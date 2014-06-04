<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Handles all data storage, update, and retrieve requests
 *
 * @package    block_odsoaipmh
 * @copyright  2014 onwards Vlatka Paunovic & Sinisa Tomic (Vlatka.Paunovic@fer.hr & Sinisa.Tomic@fer.hr)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('_OAIPMH_DEFINED') || die();


/**
 * Checks if metadataPrefix is valid
 */
function check_metadataPrefix(){
    if($_GET['metadataPrefix'] != "oai_lom"){
        global $error;
        $error = "Invalid metadataPrefix (".$_GET['metadataPrefix']."). Supported metadataPrefixes are: oai_lom.";
        include("main.php");
        die();
    }
}

/**
 * Retrieves all data for given data type. It tries to detect name and description fields for all datatypes.
 * It may contain special handling for some data types (eg. SCORM)
 *
 * @param string $type  Type of data (eg. quiz, label, ...)
 * @return array|bool   Array with required information or false if not able to get the data
 */
function getItemDescriptionFromDatabase($type){
    global $DB;

    $id = "id";
    $name = "name";
    $description = "intro";
    $tables = $DB->get_tables();

    // Special case modules, for all others try to retrieve data using get_rectord
    switch($type){
        case "scorm":
            if(in_array("scorm", $tables) && in_array("scorm_scoes", $tables)){
                $scormElements = $DB->get_records("scorm", null, "id,name,intro");
                $scormElementsSco = $DB->get_records("scorm_scoes", null, "scorm,title");
                $preparedElements = array();
                foreach($scormElementsSco as $sco){
                    if($preparedElements[$sco->scorm] == ""){
                        $preparedElements[$sco->scorm] = trim(strip_tags($sco->title));
                    }else{
                        $preparedElements[$sco->scorm] .= ", ".trim(strip_tags($sco->title));
                    }

                }
                foreach($scormElements as &$scel){
                    $scel->intro .= ", ".$preparedElements[$scel->id];
                }

                return array( "data" => $scormElements, "desc" => true, "id"=>"id", "name"=>"name", "description"=>"intro");
            }else{
                return false;
            }
    }

    // If table exists
    if(in_array($type, $tables)){
        $columns = $DB->get_columns($type);  // Get all columns in table

        // Fields ID and name must exist, if they don't then return false (unknown module table)
        if(array_key_exists($name,  $columns) && array_key_exists($id,  $columns)){

            // If there is no description (info) field
            if(!array_key_exists($description,  $columns)){
                return array("data" => $DB->get_records($type, array(), $id.",".$name), "desc" => false, "id"=>$id, "name"=>$name, "description"=>$description);
            }else{
                return array("data" => $DB->get_records($type, array(), $id.",".$name.",".$description), "desc" => true, "id"=>$id, "name"=>$name, "description"=>$description);
            }
        }else{
            return false;
        }
    }else{
        return false;
    }
}

/**
 * Gets data for specified Moodle module. If unknown tries to retrieve {type}.name & {type}.intro using {type}.id as key element.
 *
 * @param string $type         Table data name (eg. quiz)
 * @param int    $id           Element id in table
 * @param bool   $recursion    Is this recursion (should always be called using false)
 * @return array               Returns array (name, description) or false if not found
 */
function getItemDescription($type, $id, $recursion = false){
    static $values = array();


    // Is data already cached
    if(isset($values[$type]['parsed']) && $values[$type]['parsed']==2){
        $p = $values[$type]['data'][$id];

        // Element not found (this is rather strange)
        if(!is_object($p)){
            return false;
        }else{
            if($values[$type]['desc']){
                $desc = $p->$values[$type]['description'];
            }else{
                $desc = "";
            }
            return array("name" => isset($p->$values[$type]['name'])?$p->$values[$type]['name']:"", "description" => $desc);
        }

    // Table does not exist (or not recognized), tried to get the data already
    }else if((isset($values[$type]['parsed'])?$values[$type]['parsed']:0)==1){
        return false;

    // Didn't tried to get the data, try to do it now
    }else if(!$recursion){
        $values[$type] = getItemDescriptionFromDatabase($type);     // Get the data into data
        $values[$type]['parsed'] = is_array($values[$type]) ? 2 : 1;// Check the response type
        return getItemDescription($type, $id, true);                              // Actually its only single step recursion since the data must be retrieved now
    }

   // This should not happen in normal usage (only exception is that the function is called with third parameter true, what shouldn't be done)
    return false;
}


/**
 * Returnes
 *
 * @return array
 */
function getGlobalSharingSettings(){
    global $_CFG_MODULE_OAIPMH;
    $retVal = array();

    // Is shared by default
    $retVal['shared'] = (get_config($_CFG_MODULE_OAIPMH->modulename, "default_is_shared") == "1");

    // What modules are shared by default
    $retVal['share_all'] = (get_config($_CFG_MODULE_OAIPMH->modulename, "limited_share") == "0");
    if(!$retVal['share_all']){
        // Some modules are shared
        $selModules = get_config($_CFG_MODULE_OAIPMH->modulename, "shared_modules");
        if($selModules != ""){
            $selModules = explode(",", $selModules);
            if(!is_array($selModules)){
                $selModules = array($selModules);
            }
            $retVal['modules'] = $selModules;
        }
    }

    // Force server license
    $retVal['force_license'] = (get_config($_CFG_MODULE_OAIPMH->modulename, "allow_force_license") == "1");

    // Get server license
    $licenseType = (get_config($_CFG_MODULE_OAIPMH->modulename, "license_type"));
    if($licenseType == 5){
        $retVal['license_en'] = get_config($_CFG_MODULE_OAIPMH->modulename, "license_en");
        $retVal['license_local'] = get_config($_CFG_MODULE_OAIPMH->modulename, "license_local");
    }else{
        $lic = get_string("lic_".$licenseType, $_CFG_MODULE_OAIPMH->block_modulename);
        if(in_array($licenseType, array("2_1_1", "2_1_2", "2_2_1", "2_2_2", "2_3_1", "2_3_2"))){
            $lic = $lic . " - " . str_replace("__LICENSE__",
                                                get_string("url_lic_".$licenseType, $_CFG_MODULE_OAIPMH->block_modulename),
                                                get_string("url_lic_2_base", $_CFG_MODULE_OAIPMH->block_modulename)
                                              );
        }
        $retVal['license_en'] = $retVal['license_local'] = $lic;
    }
    $retVal['language'] = get_config($_CFG_MODULE_OAIPMH->modulename, "default_language");

    return $retVal;
}

/**
 * Parses configuration values and decides what are default values for course
 *
 * @param $config           stdClass  Module configuration
 * @param $systemConfig     array     System Configuration
 * @return array|bool
 */
function parseCourseInstanceConfig($config, $systemConfig){
    global $_CFG_MODULE_OAIPMH;
    $retVal = array();

    if($config === false)
        return $config;

    // Is shared
    if(!isset($config->c_share_course_allow) || $config->c_share_course_allow == 1){
        // System default
        $retVal['shared'] = $systemConfig['shared'];
    }else if($config->c_share_course_allow == 2){
        // Course allows sharing
        $retVal['shared'] = true;
    }else{
        // All other cases not shared
        $retVal['shared'] = false;
    }

    // If shared what modules are shared
    if($retVal['shared']){
        if(!isset($config->c_share_type) || $config->c_share_type == 1){
            // System default
            $retVal['share_all'] = $systemConfig['share_all'];
            if(!$retVal['share_all']){
                $retVal['modules'] = $systemConfig['modules'];
            }
        }else if($config->c_share_type == 2){
            // All modules
            $retVal['share_all'] = true;
        }else{
            // Some modules
            $retVal['share_all'] = false;
            // What modules
            $selModules = get_object_vars($config);
            foreach($selModules as $sm => $val){
                if($this->startsWith($sm, "c_mm_") && $val == 1){
                    $retVal['modules'][] = substr($sm, strlen("c_mm_"));
                }
            }
        }
    }

    if(!isset($config->c_license_type) || $config->c_license_type == 5){
        $retVal['license_en'] = $systemConfig['license_en'];
        $retVal['license_local'] = $systemConfig['license_local'];
    }else if($config->c_license_type == 4){
        $retVal['license_en'] = $config->c_license_en;
        $retVal['license_local'] = $config->c_license_local;
    }else{
        $lic = get_string("lic_".$config->c_license_type, $_CFG_MODULE_OAIPMH->block_modulename);
        if(in_array($config->c_license_type, array("2_1_1", "2_1_2", "2_2_1", "2_2_2", "2_3_1", "2_3_2"))){
            $lic = $lic . " - " . str_replace("__LICENSE__",
                                                get_string("url_lic_".$config->c_license_type, $_CFG_MODULE_OAIPMH->block_modulename),
                                                get_string("url_lic_2_base", $_CFG_MODULE_OAIPMH->block_modulename)
                                              );
        }
        $retVal['license_en'] = $retVal['license_local'] = $lic;
    }

    // var_dump($retVal);
    return $retVal;
}

/**
 * Retrieves course module settings for all courses in single SQL Query
 *
 * @param $systemConfig     array   System configuration
 * @return array courseId=>ModuleSettings
 */
function getCourseInstanceConfig($systemConfig){
    global $DB, $_CFG_MODULE_OAIPMH;
    $retVal = array();

    $tables = $DB->get_tables();
    // Both tables must exist
    if(isset($tables['context']) && isset($tables['block_instances'])){

        $sql = "SELECT instanceid, configdata FROM {context} C JOIN {block_instances} I ON C.id=I.parentcontextid AND ".$DB->sql_compare_text('I.blockname', 20)." = ". $DB->sql_compare_text('?', 20)."";

        $elId = $DB->get_records_sql($sql, array($_CFG_MODULE_OAIPMH->modulename));
        foreach($elId as $el){
            $retVal[$el->instanceid] = parseCourseInstanceConfig(unserialize(base64_decode($el->configdata)), $systemConfig);
        }
    }
    return $retVal;
}

/**
 * Retrieves all currently available data from Moodle in single named array
 * Returned fields:
 *    * ID                      - Local ID of the element
 *    * Type ("course", ...)    - Type of the element (eg. "Course", "Questioneer", ...)
 *    * MoodleID                - Moodle ID of the element (eg. course ID, Quiz ID, ...)
 *    * Deleted (true/false)    - Is the element deleted
 *    * Title                   - Title of the element
 *    * Description             - Description of the element
 *    * Language                - Language of the element
 *    * URI                     - Base URI of the element
 *    * License type            - License type of the element
 *       1 - Public domain,
 *
 *       2 - CC
 *           2.1.x - Allow adaptations
 *           2.2.x - Don't allow adaptations
 *           2.3.x - Allow adaptations - share alike
 *
 *           2.x.1 - Commercial use is allowed
 *           2.x.2 - Commercial use is not allowed
 *
 *       3 - Copyrighted work
 *
 *       4 - Custom licensed work (as defined in license text_en/text_local)
 *
 *    * License text (en)       - (if license type allowes it)
 *    * License text (local)    - (if license type allowes it)
 *    * Taxonomy                - (comma separated), eg. Science, Biology
 */
function getAllDataFromMoodle(){
    global $_CFG_MODULE_OAIPMH, $CFG, $DB;
    $minTime = 0;
    $retVal = array();

    $default_lang  = get_config($_CFG_MODULE_OAIPMH->modulename, "default_language");   // Default language

    $systemConfig = getGlobalSharingSettings();
    $force_license = $systemConfig['force_license'];

    $moduleConfig = getCourseInstanceConfig($systemConfig);
    // var_dump($moduleConfig);

    // Courses
    // For some unknown reason old version of Moodle returns invalid ID for course what causes a lot of problems later
    //$courses = get_courses("all",false,"*"); //c.id,c.shortname,c.fullname,c.summary,c.summaryformat,c.lang,c.timemodified");
    $courses = $DB->get_records("course", array()); //c.id,c.shortname,c.fullname,c.summary,c.summaryformat,c.lang,c.timemodified");

    foreach($courses as $c){
        if($c->id == "1") // Skip main page course
            continue;


        // Check what should be harvested
        if(isset($moduleConfig[$c->id])){
            $moduleCfg = $moduleConfig[$c->id];
        }else{
            $moduleCfg = false;
        }
        if(!$moduleCfg){ // Course does not have any specific settings, use global settings
            if(!$systemConfig['shared']){
                continue;
            }
            $harvestAll = $systemConfig['share_all'];
            $sharedModules = $systemConfig['modules'];
        }else{
            // Use course specific settings, if not shared, then skip
            if(!$moduleCfg['shared']){
                continue;
            }
            $harvestAll = $moduleCfg['share_all'];
            if(isset($moduleCfg['modules'])){
                $sharedModules = $moduleCfg['modules'];
            }else{
                $sharedModules = array();
            }
        }


        $data = array();
        $data['uniq_id'] = "course_".$c->id."_".$c->shortname;
        $data['type'] = "course";
        $data['moodle_id'] = $c->id;
        $data['deleted'] = "0";
        $data['title'] = str_replace("&", "&amp;", strip_tags($c->fullname));
        $data['description'] = str_replace("&", "&amp;",  strip_tags($c->summary));
        $data['language'] = ($c->lang==""?$default_lang:$c->lang);
        $courseLanguage = $data['language'];
        $data['uri'] = $CFG->wwwroot."/course/view.php?id=".$c->id;
        $data['datestamp'] = date("Y-m-d H:i:s", $c->timemodified);
        if($c->timemodified < $minTime || $minTime == 0){ // Find oldest item
            $minTime = $c->timemodified;
        }
        $data['taxonomy'] = "";
        if($force_license){
            // Force server license
            $courseLicense_En = $data['license_en'] = $systemConfig['license_en'];
            $courseLicense_Local = $data['license_local'] = $systemConfig['license_local'];
        }else{
            // Get local license
            if($moduleCfg){
                // Has local settings
                $courseLicense_En = $data['license_en'] = $moduleCfg['license_en'];
                $courseLicense_Local = $data['license_local'] = $moduleCfg['license_local'];
            }else{
                // Use server license
                $courseLicense_En = $data['license_en'] = $systemConfig['license_en'];
                $courseLicense_Local = $data['license_local'] = $systemConfig['license_local'];
            }
        }

        // Items in courses (select is done course by course)
        $retVal[] = $data;
        $p = get_fast_modinfo($c);
        $l = $p->get_instances();
        // var_dump($p->get_instances_of("block_odsoaipmh"));

        foreach($l as $type=>$instance){
            foreach($instance as $cm_instance){
                // var_dump($cm_instance);
                // var_dump(unserialize($cm_instance->modinfo->course-modinfo));

                // @var \cm_info $cm_instance
                $data = array();
                $data['uniq_id'] = $type."_".$c->id."_".$cm_instance->instance."_".$c->shortname;
                $data['moodle_id'] = $cm_instance->id;

                // If not all modules are shared, share only allowed ones
                if(!$harvestAll && !in_array($type, $sharedModules)){
                    continue;
                }
                $data['type'] = $type;
                $data['deleted'] = "0";

                $vals = getItemDescription($type, $cm_instance->instance);
                // $vals = array();

                $data['title'] = str_replace("&", "&amp;", strip_tags($vals['name']));
                $data['description'] = str_replace("&", "&amp;", strip_tags($vals['description']));
                // $data['title'] = str_replace("&", "&amp;", strip_tags($cm_instance->name));
                // $data['description'] = "";
                $data['language'] = $courseLanguage;
                $data['uri'] = (string) $cm_instance->get_url();
                $data['license_en'] = $courseLicense_En;
                $data['license_local'] = $courseLicense_Local;
                // $data['time'] = gmdate("Y-m-d\TH:i:s\Z", $c->added);
                $data['datestamp'] = date("Y-m-d H:i:s", $cm_instance->added);

                if($cm_instance->added < $minTime || $minTime == 0){ // Find oldest item
                    $minTime = (isset($cm_instance) && isset($cm_instance->added))?$cm_instance->added:0;
                }
                $data['taxonomy'] = "";
                $retVal[] = $data;
            }
        }
    }

    // Special value, just for storing some information like Update time, oldest date, ...
    $data = array();
    $data['uniq_id'] = "__GENERATED_ID";
    $data['moodle_id'] = 0;
    $data['type'] = "_INTERNAL";
    $data['deleted'] = "0";
    $data['title'] = time();                                    // Update time of database
    $data['description'] = $minTime;                            // Oldest timestamp in database
    $data['datestamp'] = date("Y-m-d H:i:s", $data['title']);   // Update time in human readable format
    $data['language'] = "";
    $data['uri'] = "";
    $data['license_en'] = "";
    $data['license_local'] = "";
    $data['taxonomy'] = "";
    $retVal[] = $data;

    return $retVal;
}

/**
 * Compares current data with the one in database and updates if elements are deleted
 */
function compareDataInDatabase(){
    global $DB;

    // Get all current Moodle data (all new data will have deleted flag set to false)
    $currentVal = getAllDataFromMoodle();

    // $p1 = microtime(true);
    // Assume all old data is obsolete
    $DB->set_field("block_odsoaipmh", "deleted", "1");
    // Use transactions since they are much faster
    $transact = $DB->start_delegated_transaction();
    $cnt = 0;
    foreach($currentVal as $c){
        $cnt++;
        if($cnt == 500){ // Commit transaction every 1000 records (much faster then holding really large transaction)
            $cnt = 0;
            $DB->commit_delegated_transaction($transact);
            $transact = $DB->start_delegated_transaction();
        }
        $sql = "SELECT id FROM {block_odsoaipmh} WHERE moodle_id=? AND ".$DB->sql_compare_text('type', 20) ." = ". $DB->sql_compare_text('?', 20)."";
        // var_dump($sql);

        // Check if record exists - if exists, then update otherwise insert new one
        $elId = $DB->get_records_sql($sql,
                                    array(isset($c['moodle_id'])?$c['moodle_id']:0, isset($c['type'])?$c['type']:""));

        if(count($elId) == 0){
            $DB->insert_record("block_odsoaipmh", $c, true);
        }else{
            $elId = reset($elId); // Get first if multiple found (actuall should not happen, but...)
            $elId = $elId->id;
            $c['id'] = $elId;
            $DB->update_record("block_odsoaipmh", $c, true);
        }
    }
    $DB->commit_delegated_transaction($transact);
    // $p2 = microtime(true);
    // echo $p2-$p1;
}

/**
 * Gets the data. This function handles all _GET parameters and resumptionTokens
 *
 * @return array    Data array
 */
function getData(){
    global $DB, $_CFG_MODULE_OAIPMH;
    $retVal = array();

    // Check if request is valid for data retrieval
    if(!isset($_GET['resumptionToken'])){
        check_metadataPrefix();
    }

    // Check if the cache needs to be updated. It is done if: IP matches refresh IP and if time since last cached data has expired
    // $lastUpdated = $DB->get_record_select("block_odsoaipmh", " array("uniq_id"=>"__GENERATED_ID"), "title");

    $seconds = get_config($_CFG_MODULE_OAIPMH->modulename, "max_seconds_refresh");
    $lastUpdated = $DB->get_records_sql("SELECT title FROM {block_odsoaipmh} WHERE ".$DB->sql_compare_text('uniq_id', 20) ." = ". $DB->sql_compare_text(':textid', 20)."", array("textid"=> "__GENERATED_ID"));
    $lastUpdated = reset($lastUpdated);
    // var_dump($lastUpdated);

    if(checkIfAllowed(false) && (!$lastUpdated ||  (time() - $lastUpdated->title > $seconds))){
        compareDataInDatabase();
    }

    $server = get_config($_CFG_MODULE_OAIPMH->modulename, "hostid");
    $retVal['server'] = $server;
    $retVal['verb'] = $_GET['verb'];

    // Single record is requested (getRecord)
    if($_GET['verb'] == "GetRecord"){
        $retVal['verb'] = "GetRecord";

        $item = explode(":", $_GET['identifier'], 3);
        if($item[0] != "oai" || $item[1] != $server || $item[2] == "__GENERATED_ID"){
            $retVal['ok'] = false;
            $retVal['error'] = "Invalid record identifier";
        }

        $result = $DB->get_records_sql('SELECT *
                                            --, extract(epoch from datestamp) as unixts
                                        FROM {block_odsoaipmh} WHERE uniq_id=?', array( $item[2] ));

        if(count($result) == 1){
            $retVal['ok'] = true;
            foreach($result as &$r){
                $r->unixts = strtotime($r->datestamp);
            }
            $retVal['data'] = $result;
        }else{
            $retVal['ok'] = false;
            $retVal['error'] = "Invalid record identifier";
        }
    }else{ // Multiple records are requested (ResumptionToken is used, ListRecords or ListIdentifiers)

        // resumptionToken format: 2014-05-02_2014-06-02_5
        // From: 2014-05-02
        // To:   2014-06-02
        // Page: 5
        if(isset($_GET['resumptionToken']) && $_GET['resumptionToken'] != ""){
            $el = explode("_", $_GET['resumptionToken']);
            if(count($el) != 3){
                return array('ok' => false, "error" => "Invalid resumption token.");
            }
            $from = date_parse($el[0]);
            $to = date_parse($el[1]);
            $offset = intval($el[2]);
            if(!is_array($from)  || $from['error_count']!=0
                || !is_array($to)  || $to['error_count']!=0
                || $offset == 0
              ){
                return array('ok' => false, "error" => "Invalid resumption token.");
            }
        }else{
            // From parameter
            if(isset($_GET['from']) && $_GET['from'] != ""){
                $from = date_parse($_GET['from']);
                if(!is_array($from) || $from['error_count']!=0){
                    return array('ok' => false, "error" => "Invalid from.");
                }
            }else{
                $from = array('year'=>1900, 'month' => 1, 'day'=> 1);
            }

            // To parameter
            if(isset($_GET['to']) && $_GET['to'] != ""){
                $to = date_parse($_GET['to']);
                if(!is_array($to) || $to['error_count']!=0){
                    return array('ok' => false, "error" => "Invalid from.");
                }
            }else{
                $to = array('year'=>2999, 'month' => 12, 'day'=> 31);
            }
            $offset = 0;
        }

        $where['from'] = sprintf("%04d-%02d-%02d", $from['year'], $from['month'], $from['day']);
        $where['to'] = sprintf("%04d-%02d-%02d", $to['year'], $to['month'], $to['day']);
        $limit = intval(get_config($_CFG_MODULE_OAIPMH->modulename, "limit_records"));
        if($limit == 0){
            $limit = 500;
        }

        $sql = 'SELECT count(*) FROM {block_odsoaipmh} WHERE datestamp BETWEEN ? AND ? AND '.$DB->sql_compare_text('uniq_id', 20) ." <> ". $DB->sql_compare_text('?', 20).'';
        $result_cnt = $DB->get_records_sql($sql,
                                        array( $where['from'], $where['to'], "__GENERATED_ID" ));
        if(count($result_cnt) == 1){
            $ak = array_keys($result_cnt);
            $retVal['cnt'] = reset($ak);
            $sql = 'SELECT * FROM {block_odsoaipmh} WHERE datestamp BETWEEN ? AND ? AND '.$DB->sql_compare_text('uniq_id', 20) ." <> ". $DB->sql_compare_text('?', 20).'  ORDER BY id LIMIT '.$limit.' OFFSET '.($offset*$limit);
            $result = $DB->get_records_sql($sql,
                                            array( $where['from'], $where['to'], "__GENERATED_ID", $limit, intval($offset*$limit) ));

            foreach($result as &$r){
                $r->unixts = strtotime($r->datestamp);
            }

            $retVal['ok'] = true;
            $retVal['data'] = $result;
            $retVal['data_cnt'] = count($result);

            if($offset*$limit+count($result) < $retVal['cnt']){
                $retVal['resumptionToken'] = $where['from']."_".$where['to']."_".($offset+1);
                $retVal['offset'] = $offset*$limit+1;
                $retVal['size'] = $retVal['cnt'];
            }
        }else{
            $retVal['ok'] = false;
            $retVal['error'] = "Invalid number of records";
        }
    }

    return $retVal;
}


/**
 * Checks if the remote IP is allowed to access this environment. If not displays error message.
 *
 * @param string $allowedClientsTxt     List of allowed clients
 * @internal param bool $throwException Default: true
 * @return bool
 */
function checkIfAllowed($allowedClientsTxt){
    $remoteIp = $_SERVER['REMOTE_ADDR'];
    if(trim($allowedClientsTxt) == "")
        return true;
    $allowedClientsList = explode("\n", $allowedClientsTxt);
    foreach($allowedClientsList as $allowedMask){
        if(trim($allowedMask) == "")
            continue;
        if(netMatch(trim($allowedMask), $remoteIp)){
            return true;
        }
    }
    return false;
}


/**
 * Checks if the IP address of remote host matches netmask
 * Note: taken from: http://stackoverflow.com/questions/10421613/match-ipv4-address-given-ip-range-mask
 *
 * @param $network  string  netmask/network
 * @param $ip       string  Remote IP
 * @return bool
 */
function netMatch($network, $ip) {
    $network=trim($network);
    // $orig_network = $network;
    $ip = trim($ip);
    if ($ip == $network) {
        // echo "used network ($network) for ($ip)\n";
        return TRUE;
    }
    $network = str_replace(' ', '', $network);
    if (strpos($network, '*') !== FALSE) {
        if (strpos($network, '/') !== FALSE) {
            $asParts = explode('/', $network);
            $network = @ $asParts[0];
        }
        $nCount = substr_count($network, '*');
        $network = str_replace('*', '0', $network);
        if ($nCount == 1) {
            $network .= '/24';
        } else if ($nCount == 2) {
            $network .= '/16';
        } else if ($nCount == 3) {
            $network .= '/8';
        } else if ($nCount > 3) {
            return TRUE; // if *.*.*.*, then all, so matched
        }
    }

    // echo "from original network($orig_network), used network ($network) for ($ip)\n";

    $d = strpos($network, '-');
    if ($d === FALSE) {
        $ip_arr = explode('/', $network);
        if (!preg_match("@\d*\.\d*\.\d*\.\d*@", $ip_arr[0], $matches)){
            $ip_arr[0].=".0";    // Alternate form 194.1.4/24
        }
        $network_long = ip2long($ip_arr[0]);
        $x = ip2long($ip_arr[1]);
        $mask = long2ip($x) == $ip_arr[1] ? $x : (0xffffffff << (32 - $ip_arr[1]));
        $ip_long = ip2long($ip);
        return ($ip_long & $mask) == ($network_long & $mask);
    } else {
        $from = trim(ip2long(substr($network, 0, $d)));
        $to = trim(ip2long(substr($network, $d+1)));
        $ip = ip2long($ip);
        return ($ip>=$from and $ip<=$to);
    }
}
