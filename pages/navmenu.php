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
 * @author     santoshtmp7 https://github.com/santoshtmp/moodle-local_easycustmenu
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
$pagetitle = 'Header Nav Menu Setting';
$PAGE->set_context($context);
$PAGE->set_url($url);
$PAGE->set_pagelayout('admin');
$PAGE->set_pagetype('easycustmenu_navmenu_setting');
$PAGE->set_title($pagetitle);
$PAGE->set_heading($pagetitle);
$PAGE->requires->js_call_amd('local_easycustmenu/nav-menu-setting', 'init', ['navmenu']);
$PAGE->requires->css(new moodle_url($CFG->wwwroot . '/local/easycustmenu/style/nav-menu-setting.css'));

$navmenu = new navmenu();
// Access checks.
require_login(null, false);
if (!has_capability('moodle/site:config', $context)) {
    $contents = "You don't have permission to access this pages";
    $contents .= "<br>";
    $contents .= "<a href='/'> Return Back</a>";
} else {
    // For POST Method Save the data.
    $navmenu->set_easycustmenu();

    // Get the data and display in form.
    $contents = $navmenu->get_easycustmenu_setting_section();
}
// Output Content.
echo $OUTPUT->header();
echo $contents;
echo $OUTPUT->footer();
