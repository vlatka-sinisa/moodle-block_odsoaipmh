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
// This function does not exist in Moodle 2.0 try to simulate functionality for it
// get_module_types_names
if(!function_exists("get_module_types_names")){
    function get_module_types_names($plural = false){
        static $modnames = null;
        global $DB, $CFG;
        if ($modnames === null) {
            $modnames = array(0 => array(), 1 => array());
            if ($allmods = $DB->get_records("modules")) {
                foreach ($allmods as $mod) {
                    if (file_exists("$CFG->dirroot/mod/$mod->name/lib.php") && $mod->visible) {
                        $modnames[0][$mod->name] = get_string("modulename", "$mod->name");
                        $modnames[1][$mod->name] = get_string("modulenameplural", "$mod->name");
                    }
                }
                asort($modnames[0]);
                asort($modnames[1]);
            }
        }
        return $modnames[(int)$plural];
    }
}
