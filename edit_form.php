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
 * User settings
 *
 * @package    block_odsoaipmh
 * @copyright  2014 onwards Vlatka Paunovic & Sinisa Tomic (Vlatka.Paunovic@fer.hr & Sinisa.Tomic@fer.hr)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('config.php');
require_once('moodle_pre_24.php');

class block_odsoaipmh_edit_form extends block_edit_form {

    protected function specific_definition($mform) {
        global $_CFG_MODULE_OAIPMH;

        // Section header title according to language file.
        $mform->addElement('header', 'configheader', get_string('info_share', $_CFG_MODULE_OAIPMH->block_modulename));

        $sharingDefaultAllowed = (get_config($_CFG_MODULE_OAIPMH->modulename, "default_is_shared") == "1");
        if($sharingDefaultAllowed){
            $txt = get_string('share_course_allow_2', $_CFG_MODULE_OAIPMH->block_modulename);
        }else{
            $txt = get_string('share_course_allow_3', $_CFG_MODULE_OAIPMH->block_modulename);
        }
        $mform->addElement('static', 'server_course_sharing',
                                get_string('server_sharing_allow', $_CFG_MODULE_OAIPMH->block_modulename),
                                $txt);

        $mform->addElement( 'select',
                            'config_c_share_course_allow',
                            get_string('share_course_allow', $_CFG_MODULE_OAIPMH->block_modulename),
                            array(  "1"=>get_string('share_course_allow_1', $_CFG_MODULE_OAIPMH->block_modulename),
                                    "2"=>get_string('share_course_allow_2', $_CFG_MODULE_OAIPMH->block_modulename),
                                    "3"=>get_string('share_course_allow_3', $_CFG_MODULE_OAIPMH->block_modulename),
                            ));


        $modules = get_module_types_names();
        $limitedShare = (get_config($_CFG_MODULE_OAIPMH->modulename, "limited_share") == "1");
        if($limitedShare){
            $selModules = get_config($_CFG_MODULE_OAIPMH->modulename, "shared_modules");
            if($selModules != ""){
                $selModules = explode(",", $selModules);
                if(!is_array($selModules)){
                    $selModules = array($selModules);
                }
            }

            $txt = get_string('share_type_3', $_CFG_MODULE_OAIPMH->block_modulename)." (";
            foreach($selModules as $sm){
                $txt .= $modules[trim($sm)].", ";
            }
            $txt = substr($txt, 0, -2);
            $txt .= ")";
        }else{
            $txt = get_string('share_type_2', $_CFG_MODULE_OAIPMH->block_modulename);
        }
        $mform->addElement('static', 'server_sharing',
                                get_string('server_sharing', $_CFG_MODULE_OAIPMH->block_modulename),
                                $txt);

        $mform->addElement( 'select',
                            'config_c_share_type',
                            get_string('share_type', $_CFG_MODULE_OAIPMH->block_modulename),
                            array(  "1"=>get_string('share_type_1', $_CFG_MODULE_OAIPMH->block_modulename),
                                    "2"=>get_string('share_type_2', $_CFG_MODULE_OAIPMH->block_modulename),
                                    "3"=>get_string('share_type_3', $_CFG_MODULE_OAIPMH->block_modulename),
                            ));

        foreach($modules as $key=>$val){
            $mform->addElement( 'advcheckbox',
                                "config_c_mm_".$key,
                                '',
                                $val,
                                array(),
                                array(0,1)
                                );
            // Disable my control when a dropdown has value 42.
            $mform->disabledIf("config_c_mm_".$key, 'config_c_share_type', 'ne', 3);
        }


        $mform->addElement('header', 'configheader', get_string('info_license', $_CFG_MODULE_OAIPMH->block_modulename));

        $txt = "";
        $type = "lic_".get_config($_CFG_MODULE_OAIPMH->modulename, "license_type");
        if($type == "lic_4"){ // Custom license
            $txt = get_string($type, $_CFG_MODULE_OAIPMH->block_modulename)." <p>"
                    .get_string("english", $_CFG_MODULE_OAIPMH->block_modulename)
                    ." "
                    .get_config($_CFG_MODULE_OAIPMH->modulename, "license_en")
                    ."</p><p>"
                    .get_string("local", $_CFG_MODULE_OAIPMH->block_modulename)
                    ." "
                    .get_config($_CFG_MODULE_OAIPMH->modulename, "license_local")
                    ."</p>";
        }else{  // Prepared license
            $txt = get_string($type, $_CFG_MODULE_OAIPMH->block_modulename);
        }
        $mform->addElement('static', 'server_license', get_string('server_license', $_CFG_MODULE_OAIPMH->block_modulename), $txt);

        $mform->addElement( 'select',
                            'config_c_license_type',
                            get_string('license_type', $_CFG_MODULE_OAIPMH->block_modulename),
                            array(
                                    "5" => get_string('lic_5', $_CFG_MODULE_OAIPMH->block_modulename),
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
                            );

        $mform->addElement( 'text',
                            'config_c_license_en',
                            get_string('label_license_en', $_CFG_MODULE_OAIPMH->block_modulename)
                          );
        $mform->disabledIf('config_c_license_en', 'config_c_license_type', 'ne', 4);

        $mform->addElement( 'text',
                            'config_c_license_local',
                            get_string('label_license_local', $_CFG_MODULE_OAIPMH->block_modulename)
                          );
        $mform->disabledIf('config_c_license_local', 'config_c_license_type', 'ne', 4);


    }
}
