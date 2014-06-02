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
 * Mail package file
 *
 * @package    block_odsoaipmh
 * @copyright  2014 onwards Vlatka Paunovic & Sinisa Tomic (Vlatka.Paunovic@fer.hr & Sinisa.Tomic@fer.hr)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Relevant URLs:
//  http://www.openarchives.org/OAI/2.0/openarchivesprotocol.htm#dublincore
//  http://www.openarchives.org/OAI/2.0/guidelines-harvester.htm#resumptionToken
//  http://dabih.carnet.hr/oai/?verb=ListRecords&dbg=1&metadataPrefix=oai_lom

defined('MOODLE_INTERNAL') || die();
require_once('config.php');
require_once('moodle_pre_24.php');


class block_odsoaipmh extends block_base {

    function init() {
        $this->title = get_string('pluginname', 'block_odsoaipmh');
    }

    function startsWith($haystack, $needle)
    {
        return $needle === "" || strpos($haystack, $needle) === 0;
    }

    function get_content() {

        // global $CFG, $OUTPUT,
        global $_CFG_MODULE_OAIPMH;

        if ($this->content !== null) {
            return $this->content;
        }

        if (empty($this->instance)) {
            $this->content = '';
            return $this->content;
        }

        // var_dump($this->config);

        $this->content = new stdClass();
        $this->content->items = array();
        $this->content->icons = array();
        $this->content->footer = '';
        $this->content->text = "";

        // user/index.php expect course context, so get one if page has module context.
        // var_dump($this->config);
        if(method_exists($this->page->context, "get_course_context")){ // Moodle 2.0
            $currentcontext = $this->page->context->get_course_context(false);
        }else{
            $currentcontext = null;
        }

        //var_dump($this->config);


        $shared = false;
        $system = false;
        $sharing = (empty($this->config->c_share_course_allow)) ? 1 : $this->config->c_share_course_allow;
        if($sharing == 1){ // System default
            $system = true;
            $shared = (get_config($_CFG_MODULE_OAIPMH->modulename, "default_is_shared") == "1");
        }else if ($sharing == 2){
            $shared = true;     // Course shared
        }

        $this->content->text = "";
        if($shared){
            $txt = "";
            // Sharing
            $this->content->text .= get_string("share_course_allow", $_CFG_MODULE_OAIPMH->block_modulename)
                                 . ": ";

            if($system){
                $this->content->text   .= get_string("server_sharing_allow", $_CFG_MODULE_OAIPMH->block_modulename)
                                        . " ("
                                        . get_string("share_course_allow_2", $_CFG_MODULE_OAIPMH->block_modulename)
                                        . ")";
            }else{
                $this->content->text   .= get_string("share_course_allow_2", $_CFG_MODULE_OAIPMH->block_modulename);
            }

            $this->content->text .= "<br/><a style='cursor: pointer' onclick=\"document.getElementById('odsoaipmh').style.display='block'; this.style.display='none';\">".get_string("details", $_CFG_MODULE_OAIPMH->block_modulename)."</a>";
            $this->content->text .= "<div style='display: none' id='odsoaipmh'><br/>";


            // License
            if($this->config->c_license_type == 5){ // Using System license
                $type = "lic_".get_config($_CFG_MODULE_OAIPMH->modulename, "license_type");
                if($type == "lic_4"){ // Custom license
                    $txt = get_string($type, $_CFG_MODULE_OAIPMH->block_modulename)
                            ." <p>"
                            .get_string("english", $_CFG_MODULE_OAIPMH->block_modulename)
                            ." "
                            .get_config($_CFG_MODULE_OAIPMH->modulename, "license_en")
                            ."</p>"
                            ."<p>"
                            .get_string("local", $_CFG_MODULE_OAIPMH->block_modulename)
                            ." "
                            .get_config($_CFG_MODULE_OAIPMH->modulename, "license_local")
                            ."</p>";
                }else{  // Prepared license
                    $txt = get_string($type, $_CFG_MODULE_OAIPMH->block_modulename);
                }
                $txt = " (".$txt.")";
            }else if($this->config->c_license_type == 4){ // Using custom license
                $txt =  "<br/>"
                        .get_string("english", $_CFG_MODULE_OAIPMH->block_modulename)
                        ." "
                        .$this->config->c_license_en
                        ."<br/>"
                        .get_string("local", $_CFG_MODULE_OAIPMH->block_modulename)
                        ." "
                        .$this->config->c_license_local
                        ."";
            }
            $this->content->text .= get_string("license_type", $_CFG_MODULE_OAIPMH->block_modulename)
                                 . ": "
                                 . get_string("lic_".$this->config->c_license_type, $_CFG_MODULE_OAIPMH->block_modulename)
                                 . $txt
                                 ;

            // Harvested Moodle modules
            $modules = get_module_types_names();
            $shareType = $this->config->c_share_type;
            switch($shareType){
                case 1: // System defaults
                    $limitedShare = (get_config($_CFG_MODULE_OAIPMH->modulename, "limited_share") == "1");
                    if($limitedShare){
                        $selModules = get_config($_CFG_MODULE_OAIPMH->modulename, "shared_modules");
                        if($selModules != ""){
                            $selModules = explode(",", $selModules);
                            if(!is_array($selModules)){
                                $selModules = array($selModules);
                            }
                        }

                        $txt = get_string('share_type_1', $_CFG_MODULE_OAIPMH->block_modulename). " (";

                        $txt .= get_string('share_type_3', $_CFG_MODULE_OAIPMH->block_modulename)." [";
                        foreach($selModules as $sm){
                            $txt .= $modules[trim($sm)].", ";
                        }
                        $txt = substr($txt, 0, -2);
                        $txt .= "])";
                    }else{
                        $txt = get_string('share_type_2', $_CFG_MODULE_OAIPMH->block_modulename);
                    }
                    break;
                case 2: // All modules
                    $txt = get_string('share_type_2', $_CFG_MODULE_OAIPMH->block_modulename);
                    break;
                case 3: // Some modules
                    $txt = get_string('share_type_3', $_CFG_MODULE_OAIPMH->block_modulename)
                         . "  (";
                    $selModules = get_object_vars($this->config);
                    $found = false;
                    foreach($selModules as $sm => $val){
                        if($this->startsWith($sm, "c_mm_") && $val == 1){
                            $sm = substr($sm, strlen("c_mm_"));
                            $txt .= $modules[trim($sm)].", ";
                            $found = true;
                        }
                    }
                    if($found){
                        $txt = substr($txt, 0, -2);
                    }
                    $txt .= ")";
                    break;
            }
            $this->content->text .= "<br/><br/>";
            $this->content->text .= get_string("share_type", $_CFG_MODULE_OAIPMH->block_modulename)
                                  . " "
                                  . $txt;

            $this->content->text .= "</div>";

        }else{
            // Not shared
            $this->content->text = get_string("share_course_allow", $_CFG_MODULE_OAIPMH->block_modulename)
                                 . ": ";

            if($system){
                $this->content->text   .= get_string("server_sharing_allow", $_CFG_MODULE_OAIPMH->block_modulename)
                                        . " ("
                                        . get_string("share_course_allow_3", $_CFG_MODULE_OAIPMH->block_modulename)
                                        . ")";
            }else{
                $this->content->text   .= get_string("share_course_allow_3", $_CFG_MODULE_OAIPMH->block_modulename);
            }
        }



        // $this->content = '';
        if (empty($currentcontext)) {
            return $this->content;
        }

        return $this->content;
    }

    // my moodle can only have SITEID and it's redundant here, so take it away
    public function applicable_formats() {
        return array('site' => false,
                     'site-index' => false,
                     'course-view' => true, 
                     'course-view-social' => false,
                     'mod' => false,
                     'mod-quiz' => false);
    }

    public function instance_allow_multiple() { return false; }

    function has_config() { return true; }

    // public function cron() { return true; }
}
