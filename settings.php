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
 * Settings.
 *
 * @package    block_odsoaipmh
 * @copyright  2014 onwards Vlatka Paunovic & Sinisa Tomic (Vlatka.Paunovic@fer.hr & Sinisa.Tomic@fer.hr)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();
include("config.php");
include("moodle_pre_24.php");
global $_CFG_MODULE_OAIPMH, $CFG;

// ----------------------------------
// Server settings
$settings->add(new admin_setting_heading('odsoaipmh_server',
                                         get_string('info_server', $_CFG_MODULE_OAIPMH->block_modulename),
                                         ''));

$settings->add(new admin_setting_configtext('odsoaipmh/serverurl',
                                                get_string('label_server_url', $_CFG_MODULE_OAIPMH->block_modulename),
                                                get_string('desc_server_url', $_CFG_MODULE_OAIPMH->block_modulename),
                                                $CFG->wwwroot."/blocks/odsoaipmh/oaipmh/index.php"));

$server = parse_url($CFG->wwwroot);
$settings->add(new admin_setting_configtext('odsoaipmh/hostid',
                                                get_string('label_server_id', $_CFG_MODULE_OAIPMH->block_modulename),
                                                get_string('desc_server_id', $_CFG_MODULE_OAIPMH->block_modulename),
                                                $server['host']));

$site = get_site();
$settings->add(new admin_setting_configtext('odsoaipmh/servername',
                                                get_string('label_server_name', $_CFG_MODULE_OAIPMH->block_modulename),
                                                get_string('desc_server_name', $_CFG_MODULE_OAIPMH->block_modulename),
                                                $site->fullname));

$admin = get_admin();
$settings->add(new admin_setting_configtext('odsoaipmh/serveradmin',
                                                get_string('label_server_admin', $_CFG_MODULE_OAIPMH->block_modulename),
                                                get_string('desc_server_admin', $_CFG_MODULE_OAIPMH->block_modulename),
                                                $admin->email));


// ----------------------------------
// Sharing settings
$settings->add(new admin_setting_heading('odsoaipmh_share',
                                         get_string('info_share', $_CFG_MODULE_OAIPMH->block_modulename),
                                         ''));

$settings->add(new admin_setting_configcheckbox('odsoaipmh/default_is_shared',
                                         get_string('label_default_is_shared', $_CFG_MODULE_OAIPMH->block_modulename),
                                         get_string('desc_default_is_shared', $_CFG_MODULE_OAIPMH->block_modulename),
                                         0
                                        ));

$settings->add(new admin_setting_configcheckbox('odsoaipmh/limited_share',
                                         get_string('label_limited_share', $_CFG_MODULE_OAIPMH->block_modulename),
                                         get_string('desc_limited_share', $_CFG_MODULE_OAIPMH->block_modulename),
                                         0
                                        ));

$settings->add(new admin_setting_configmulticheckbox('odsoaipmh/shared_modules',
                                         get_string('label_shared_modules', $_CFG_MODULE_OAIPMH->block_modulename),
                                         get_string('desc_shared_modules', $_CFG_MODULE_OAIPMH->block_modulename),
                                         array(),
                                         get_module_types_names()
                                        ));

// ----------------------------------
// License settings
$settings->add(new admin_setting_heading('odsoaipmh_license',
                                         get_string('info_license', $_CFG_MODULE_OAIPMH->block_modulename),
                                         ''));

$settings->add(new admin_setting_configcheckbox('odsoaipmh/allow_force_license',
                                         get_string('label_force_license', $_CFG_MODULE_OAIPMH->block_modulename),
                                         get_string('desc_force_license', $_CFG_MODULE_OAIPMH->block_modulename),
                                         0
                                        ));

$settings->add(new admin_setting_configselect('odsoaipmh/license_type',
                                                get_string('label_license_type', $_CFG_MODULE_OAIPMH->block_modulename),
                                                get_string('desc_license_type', $_CFG_MODULE_OAIPMH->block_modulename),
                                                "2_1_1",
                                                array(
                                                        "2_1_1" => get_string('lic_2_1_1', $_CFG_MODULE_OAIPMH->block_modulename),
                                                        "2_1_2" => get_string('lic_2_1_2', $_CFG_MODULE_OAIPMH->block_modulename),
                                                        "2_2_1" => get_string('lic_2_2_1', $_CFG_MODULE_OAIPMH->block_modulename),
                                                        "2_2_2" => get_string('lic_2_2_2', $_CFG_MODULE_OAIPMH->block_modulename),
                                                        "2_3_1" => get_string('lic_2_3_1', $_CFG_MODULE_OAIPMH->block_modulename),
                                                        "2_3_2" => get_string('lic_2_3_2', $_CFG_MODULE_OAIPMH->block_modulename),
                                                        "1"     => get_string('lic_1', $_CFG_MODULE_OAIPMH->block_modulename),
                                                        "3"     => get_string('lic_3', $_CFG_MODULE_OAIPMH->block_modulename),
                                                        "4"     => get_string('lic_4', $_CFG_MODULE_OAIPMH->block_modulename),
                                                    )
                                                ));

$settings->add(new admin_setting_configtext('odsoaipmh/license_en',
                                                get_string('label_license_en', $_CFG_MODULE_OAIPMH->block_modulename),
                                                get_string('desc_license_en', $_CFG_MODULE_OAIPMH->block_modulename),
                                                ""));


$settings->add(new admin_setting_configtext('odsoaipmh/license_local',
                                                get_string('label_license_local', $_CFG_MODULE_OAIPMH->block_modulename),
                                                get_string('desc_license_local', $_CFG_MODULE_OAIPMH->block_modulename),
                                                ""));

$settings->add(new admin_setting_configtext('odsoaipmh/default_language',
                                                get_string('label_default_language', $_CFG_MODULE_OAIPMH->block_modulename),
                                                get_string('desc_default_language', $_CFG_MODULE_OAIPMH->block_modulename),
                                                "en"));

require_once('moodle_pre_24.php');


// ----------------------------------
// Access settings
$settings->add(new admin_setting_heading('odsoaipmh_access',
                                         get_string('info_access', $_CFG_MODULE_OAIPMH->block_modulename),
                                         ''));

$settings->add(new admin_setting_configtextarea('odsoaipmh/allowed_ip',
                                                get_string('label_allowed_ip', $_CFG_MODULE_OAIPMH->block_modulename),
                                                get_string('desc_allowed_ip', $_CFG_MODULE_OAIPMH->block_modulename),
                                                ""));

// Try to use setting duration if exists
if(class_exists("admin_setting_configduration")){
    $settings->add(new admin_setting_configduration('odsoaipmh/max_seconds_refresh',
                                                    get_string('label_max_seconds_refresh', $_CFG_MODULE_OAIPMH->block_modulename),
                                                    get_string('desc_max_seconds_refresh', $_CFG_MODULE_OAIPMH->block_modulename),
                                                    "0"));
}else{
    $settings->add(new admin_setting_configtext('odsoaipmh/max_seconds_refresh',
                                                    get_string('label_max_seconds_refresh', $_CFG_MODULE_OAIPMH->block_modulename),
                                                    get_string('desc_max_seconds_refresh', $_CFG_MODULE_OAIPMH->block_modulename),
                                                    "0",
                                                    PARAM_INT));
}
$settings->add(new admin_setting_configtextarea('odsoaipmh/allowed_ip_refresh',
                                                get_string('label_allowed_ip_refresh', $_CFG_MODULE_OAIPMH->block_modulename),
                                                get_string('desc_allowed_ip_refresh', $_CFG_MODULE_OAIPMH->block_modulename),
                                                ""));
$settings->add(new admin_setting_configtext('odsoaipmh/limit_records',
                                                get_string('label_limit_records', $_CFG_MODULE_OAIPMH->block_modulename),
                                                get_string('desc_limit_records', $_CFG_MODULE_OAIPMH->block_modulename),
                                                "500"));
