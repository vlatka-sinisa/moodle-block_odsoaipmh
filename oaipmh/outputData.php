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
 * getData verb
 *
 * @package    block_odsoaipmh
 * @copyright  2014 onwards Vlatka Paunovic & Sinisa Tomic (Vlatka.Paunovic@fer.hr & Sinisa.Tomic@fer.hr)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('_OAIPMH_DEFINED') || die();
include_once('../config.php');


/**
 * Verb: identify
 * Sends OAI-PMH server identification
 */
function identify(){
    global $_CFG_MODULE_OAIPMH, $DB;

    // $retVal = array();
    // $oldestRecord = $DB->get_record("block_odsoaipmh", array("uniq_id"=>"__GENERATED_ID"), "description");
    $oldestRecord = new StdClass();
    $oldestRecord->description = "2015-02-02";

    header('Content-type: text/xml');
    if(is_numeric($oldestRecord->description)){
        $date = date("Y-m-d", $oldestRecord->description);
    }else{
        $date = "1900-01-01";
    }

    echo "<?xml-stylesheet type='text/xsl' href='oai2.xsl' ?>\n";
    echo "<OAI-PMH xmlns='http://www.openarchives.org/OAI/2.0/' xmlns:xsi='http://www.w3.org/2001/XMLSchema-instance' xsi:schemaLocation='http://www.openarchives.org/OAI/2.0/ http://www.openarchives.org/OAI/2.0/OAI-PMH.xsd'>
    <responseDate>".gmdate("Y-m-d\TH:i:s\Z")."</responseDate>
    <request verb='Identify'>".get_config($_CFG_MODULE_OAIPMH->modulename, "serverurl")."</request>
    <Identify>
        <repositoryName>".get_config($_CFG_MODULE_OAIPMH->modulename, "servername")."</repositoryName>
        <baseURL>".get_config($_CFG_MODULE_OAIPMH->modulename, "serverurl")."</baseURL>
        <protocolVersion>2.0</protocolVersion>
        <adminEmail>".get_config($_CFG_MODULE_OAIPMH->modulename, "serveradmin")."</adminEmail>
        <earliestDatestamp>".$date."</earliestDatestamp>
        <deletedRecord>transient</deletedRecord>
        <granularity>YYYY-MM-DD</granularity>
    </Identify>
    </OAI-PMH>\n";

    // return;
}


/**
 * Verb: ListMetadataFormats
 * Sends OAI-PMH server supported list of metadata
 */
function listMetadataFormats(){
    global $_CFG_MODULE_OAIPMH;

    header('Content-type: text/xml');

    echo "<?xml-stylesheet type='text/xsl' href='oai2.xsl' ?>\n";
    echo "<OAI-PMH xmlns='http://www.openarchives.org/OAI/2.0/' xmlns:xsi='http://www.w3.org/2001/XMLSchema-instance' xsi:schemaLocation='http://www.openarchives.org/OAI/2.0/ http://www.openarchives.org/OAI/2.0/OAI-PMH.xsd'>
    <responseDate>".gmdate("Y-m-d\TH:i:s\Z")."</responseDate>
    <request verb='ListSets'>".get_config($_CFG_MODULE_OAIPMH->modulename, "serverurl")."</request>
    <ListMetadataFormats>
        <metadataFormat>
            <metadataPrefix>oai_lom</metadataPrefix>
            <schema>http://ltsc.ieee.org/xsd/LOM lomODS.xsd</schema>
            <metadataNamespace>http://ltsc.ieee.org/xsd/LOM</metadataNamespace>
        </metadataFormat>
    </ListMetadataFormats>
    </OAI-PMH>\n";
}


/**
 * Verb: ListSets
 * Sends OAI-PMH server supported list of sets (only Moodle set is supported for now)
 */
function listSets(){
    global $_CFG_MODULE_OAIPMH;
    header('Content-type: text/xml');

    echo "<?xml-stylesheet type='text/xsl' href='oai2.xsl' ?>\n";
    echo "<OAI-PMH xmlns='http://www.openarchives.org/OAI/2.0/' xmlns:xsi='http://www.w3.org/2001/XMLSchema-instance' xsi:schemaLocation='http://www.openarchives.org/OAI/2.0/ http://www.openarchives.org/OAI/2.0/OAI-PMH.xsd'>
    <responseDate>".gmdate("Y-m-d\TH:i:s\Z")."</responseDate>
    <request verb='ListSets'>".get_config($_CFG_MODULE_OAIPMH->modulename, "serverurl")."</request>
    <ListSets>
        <set>
            <setSpec>moodle</setSpec>
            <setName>Moodle</setName>
        </set>
    </ListSets>
    </OAI-PMH>\n";
}

/**
 * Verbs: ListRecords, GetRecord and ListIdentifiers
 * Sends data from OAI-PMH server in requested format
 *
 * @ param array     $dataToDisplay  Array of data to display
 * @param string    $format         Format to use
 * @param bool      $identifieronly Should only identifiers be sent
 */
function listRecords(/*$dataToDisplay, */ $identifieronly=false, $format="oai_lom"){

    global $_CFG_MODULE_OAIPMH;

    $data = getData();
    if(!$data["ok"]){
        global $error;
        $error = $data["error"];
        include("main.php");
        die();
    }

    header('Content-type: text/xml');


    $request = "";
    foreach($_GET as $name=>$value){
        $request .= " {$name}='".urlencode($value)."'";
    }


    echo "<?xml-stylesheet type='text/xsl' href='oai2.xsl' ?>\n";

    echo "<OAI-PMH xmlns='http://www.openarchives.org/OAI/2.0/' xmlns:xsi='http://www.w3.org/2001/XMLSchema-instance' xsi:schemaLocation='http://www.openarchives.org/OAI/2.0/ http://www.openarchives.org/OAI/2.0/OAI-PMH.xsd'>\n";
    echo "<responseDate>".date("Y-m-d\TH:i:s\Z")."</responseDate>\n";
    echo "<request $request>".get_config($_CFG_MODULE_OAIPMH->modulename, "serverurl")."</request>\n";
    echo "<{$_GET['verb']}>\n";

    $server = $data['server'];

    foreach($data['data'] as $r){

        if($identifieronly){ // Only identifiers
            echo "<header";
            if($r->deleted == "1"){
                echo " status='deleted'";
            }
            echo ">
            <identifier>oai:{$server}:{$r->uniq_id}</identifier>
            <datestamp>".gmdate("Y-m-d\TH:i:s\Z", $r->unixts)."</datestamp>
            <setSpec>moodle</setSpec>
            </header>";
        }else if($r->deleted == "1"){ // Deleted item
            echo "<record>
                        <header status='deleted'>
                        <identifier>oai:{$server}:{$r->uniq_id}</identifier>
                        <datestamp>".gmdate("Y-m-d\TH:i:s\Z", $r->unixts)."</datestamp>
                        <setSpec>moodle</setSpec>
                        </header>
                  </record>";
        }else{ // Detail data, but only if not deleted
            $license = "";
            if($r->license_local != ""){
                $license = "<description>\n";
                if($r->license_en != ""){
                    $license .="<string language='en'>{$r->license_en}</string>";
                }
                if($r->license_local != ""){
                    $license .= "<string language='{$r->language}'>{$r->license_local}</string>";
                }
                $license .= "</description>\n";
            }

            $taxonomy = "";
            if($r->taxonomy!=""){
                $el = explode(",", $r->taxonomy);
                if(!is_array($el)){
                    $el = array($r->taxonomy);
                }
                $taxonomy .= "<taxonPath>
                                <source>
                                    <string language='en'>ODS Science Vocabulary</string>
                                </source>";
                foreach($el as $e){
                    $taxonomy .= "<taxon>
                                    <id/>
                                    <entry>
                                    <string language='en'>{$e}</string>
                                    </entry>
                                    </taxon>";
                }
                $taxonomy .= "</taxonPath>";
            }

            echo "<record>
            <header>
            <identifier>oai:{$server}:{$r->uniq_id}</identifier>
            <datestamp>".gmdate("Y-m-d\TH:i:s\Z", $r->unixts)."</datestamp>
            <setSpec>moodle</setSpec>
            </header>

            <metadata>
            <lom xmlns='http://ltsc.ieee.org/xsd/LOM' xmlns:xsi='http://www.w3.org/2001/XMLSchema-instance' xsi:schemaLocation='http://ltsc.ieee.org/xsd/LOM lomODS.xsd'>
                <general>
                    <identifier>
                    <catalog>moodle</catalog>
                    <entry>{$r->uniq_id}</entry>
                    </identifier>

                    <title>
                        <string language='{$r->language}'>{$r->title}</string>
                    </title>
                    <language>{$r->language}</language>
                    <description>
                        <string language='{$r->language}'>{$r->description}</string>
                    </description>
                </general>

                <lifeCycle></lifeCycle>
                <metaMetadata>
                    <identifier>
                        <catalog>moodle</catalog>
                        <entry>{$r->uniq_id}</entry>
                    </identifier>
                    <metadataSchema>LOMv1.0</metadataSchema>
                </metaMetadata>
                <technical>
                    <location>{$r->uri}</location>
                </technical>
                <educational/>
                <rights>
                    <copyrightAndOtherRestrictions>
                        <source>LOMODSv1.0</source>
                        <value>yes</value>
                    </copyrightAndOtherRestrictions>
                    {$license}
                </rights>
                <relation/>
                <annotation/>

                <classification>

                <purpose>
                    <source>LOMODSv1.0</source>
                    <value>discipline</value>
                </purpose>

                {$taxonomy}

                </classification>

            </lom>
            </metadata>

            </record>\n";
        }
    }
    // var_dump($data['resumptionToken']);
    if(isset($data['resumptionToken']) && $data['resumptionToken'] != ""){
        echo "<resumptionToken completeListSize='{$data['cnt']}' cursor='{$data['offset']}'>{$data['resumptionToken']}</resumptionToken>";
    }
    echo "</{$_GET['verb']}>
            </OAI-PMH>";
    die();
}
