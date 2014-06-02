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
 * Default design response
 *
 * @package    block_odsoaipmh
 * @copyright  2014 onwards Vlatka Paunovic & Sinisa Tomic (Vlatka.Paunovic@fer.hr & Sinisa.Tomic@fer.hr)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('_OAIPMH_DEFINED') || die();

global $PAGE, $OUTPUT, $SITE, $error, $_CFG_MODULE_OAIPMH;

if(function_exists("get_context_info_array")){
    if(is_object($SITE)){
        list($context, $course, $cm) = get_context_info_array($SITE->id);
    }else{
        list($context, $course, $cm) = get_context_info_array(SITEID);
    }

    $PAGE->set_course($context);
}else{
    $context = new StdClass();
    $context->id = 1;
    $PAGE->set_course($context);
}
$PAGE->set_url('/blocks/odsoaipmh/oaipmh/index.php');
$PAGE->set_heading($SITE->fullname);
$PAGE->set_pagelayout('course');
$PAGE->set_title("ODS OAI-PMH"); // get_string('searchcourse', 'block_odssse'));
$PAGE->navbar->add(get_string('pluginname', 'block_odsoaipmh'));
// $PAGE->navbar->add(get_string('plugin_title', 'block_odssse'));

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('pluginname', 'block_odsoaipmh'), 3, 'main');
if($error){
    if($_GET['verb']!=""){
        $error = "Verb: ".htmlspecialchars($_GET['verb'])."<br/>".htmlspecialchars($error);
    }
    echo $OUTPUT->notification($error);
}
echo $OUTPUT->container("
<dt>".get_string('checkaccess', 'block_odsoaipmh')."</dt>
  <dd><a href='".get_config($_CFG_MODULE_OAIPMH->modulename, "serverurl")."?verb=Identify'>Identify</a></dd>
  <dd><a href='".get_config($_CFG_MODULE_OAIPMH->modulename, "serverurl")."?verb=ListMetadataFormats'>ListMetadataFormats</a></dd>
  <dd><a href='".get_config($_CFG_MODULE_OAIPMH->modulename, "serverurl")."?verb=ListSets'>ListSets</a></dd>
  <dd><a href='".get_config($_CFG_MODULE_OAIPMH->modulename, "serverurl")."?verb=ListIdentifiers&amp;metadataPrefix=oai_lom'>ListIdentifiers</a></dd>
  <dd><a href='".get_config($_CFG_MODULE_OAIPMH->modulename, "serverurl")."?verb=ListRecords&amp;metadataPrefix=oai_lom'>ListRecords</a></dd>
</dt>
");
echo $OUTPUT->box('<hr/><em style="font-size: 0.8em">Powered by Moodle OAI-PMH & <a href="http://OpenDiscoverySpace.EU">OpenDiscoverySpace.EU</a></em>');
echo $OUTPUT->footer();
