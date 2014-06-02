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
 * Configuration
 *
 * @package    block_odsoaipmh
 * @copyright  2014 onwards Vlatka Paunovic & Sinisa Tomic (Vlatka.Paunovic@fer.hr & Sinisa.Tomic@fer.hr)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

if(!defined('_OAIPMH_CONFIGURED')){
    define('_OAIPMH_CONFIGURED', 1);
    global $_CFG_MODULE_OAIPMH;
    $_CFG_MODULE_OAIPMH = new stdClass();

    $_CFG_MODULE_OAIPMH->modulename = "odsoaipmh";
    $_CFG_MODULE_OAIPMH->block_modulename = "block_".$_CFG_MODULE_OAIPMH->modulename;
}

