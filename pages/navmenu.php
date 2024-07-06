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
 * 
 * @package    local_easycustmenu
 * @copyright  2024 https://santoshmagar.com.np/
 * @author     santoshtmp7
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * 
 */

use local_easycustmenu\menu\navmenu;

// Require config.
require_once(dirname(__FILE__) . '/../../../config.php');

// Get parameters.

// Get system context.
$context = \context_system::instance();

// Prepare the page information.
$url = new moodle_url('/local/easycustmenu/pages/navmenu.php');
$page_title = 'Custom Menu Setting';
$PAGE->set_context($context);
$PAGE->set_url($url);
$PAGE->set_pagelayout('admin'); // admin , standard , ...
$PAGE->set_pagetype('custmenu_setting');
$PAGE->set_title($page_title);
$PAGE->set_heading($page_title);
// $PAGE->navbar->add($page_title);
$PAGE->requires->jquery();
$PAGE->requires->js(new moodle_url($CFG->wwwroot . '/local/easycustmenu/assets/js/nav-menu-setting.js'));
$PAGE->requires->css(new moodle_url($CFG->wwwroot . '/local/easycustmenu/assets/css/nav-menu-setting.css'));

$navmenu = new navmenu();
// Access checks.
$navmenu->menu_setting_access_check($context);
// admin_externalpage_setup('local_easycustmenu_menu');

/**
 * ========================================================
 *     For POST Method Save the custommenuitems data 
 * ========================================================
 */
$navmenu->set_easycustmenu();

/**
 * ========================================================
 *     Get the custommenuitems data and display in form
 * ========================================================
 */
$contents = $navmenu->get_easycustmenu();

/**
 * ========================================================
 * -------------------  Output Content  -------------------
 * ========================================================
 */
echo $OUTPUT->header();
// echo $OUTPUT->heading('Menu Setting');
echo $contents;
echo $navmenu->menu_main_script();
echo $OUTPUT->footer();
