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
 * Main routing engine
 *
 * @package    block_odsoaipmh
 * @copyright  2014 onwards Vlatka Paunovic & Sinisa Tomic (Vlatka.Paunovic@fer.hr & Sinisa.Tomic@fer.hr)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require (dirname(dirname(dirname(dirname(__FILE__)))).'/config.php');
define("_OAIPMH_DEFINED", 1);
error_reporting(E_ALL);

require_once('../config.php');
global $_CFG_MODULE_OAIPMH;
include("getData.php");
include("outputData.php");

if(!checkIfAllowed(get_config($_CFG_MODULE_OAIPMH->modulename, "allowed_ip"))){
    global $error;
    $error = "Your IP (".$_SERVER['REMOTE_ADDR'].") is not allowed to access this functionality.";
    include("main.php");
    die();
}

// compareDataInDatabase();
// var_dump(getAllDataFromMoodle());
// $data = getData();

// die("OK");

if(count($_GET) == 0){
    include("main.php");
}else if ($_GET['verb'] != ""){
    switch($_GET['verb']){
        case "Identify":
            identify();
            break;
        case "ListMetadataFormats":
            listMetadataFormats();
            break;
        case "ListSets":
            listSets();
            break;
        case "GetRecord":
            listRecords();
            // check_metadataPrefix();
            // include("listRecords.php");
            break;
        case "ListIdentifiers":
            listRecords(true);
            // check_metadataPrefix();
            // include("listIdentifiers.php");
            break;
        case "ListRecords":
            listRecords();
            // check_metadataPrefix();
            // include("listRecords.php");
            break;
        default:
            global $error;
            $error = "Invalid verb (".$_GET['verb']."). Allowed Verbs: Identify, ListMetadataFormats, ListSets, GetRecord, ListIdentifiers, ListRecords.";
            include("main.php");
            break;
    }
}



