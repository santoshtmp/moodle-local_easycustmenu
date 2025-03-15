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

defined('MOODLE_INTERNAL') || die();


if ($hassiteconfig) {
    // Heading.
    $settings = new admin_settingpage('local_easycustmenu', get_string('pluginname', 'local_easycustmenu'));
    $ADMIN->add('appearance', $settings);

    // Create primary navigation heading.
    $name = 'local_easycustmenu/setting_ecm_general';
    $title = get_string('setting_general', 'local_easycustmenu');
    $setting = new admin_setting_heading($name, $title, null);
    $settings->add($setting);

    $default = '';
    $name = 'local_easycustmenu/activate';
    $title = get_string('setting_activate', 'local_easycustmenu');
    $description = get_string('setting_activate_desc', 'local_easycustmenu');
    $setting = new admin_setting_configcheckbox($name, $title, $description, $default, PARAM_RAW);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $settings->add($setting);

    $name = 'local_easycustmenu/show_ecm_core';
    $title = get_string('setting_show_ecm_core', 'local_easycustmenu');
    $description = get_string('setting_show_ecm_core_desc', 'local_easycustmenu');
    $default = 0;
    $choices = [
        0 => get_string('hide', 'local_easycustmenu'),
        1 => get_string('show', 'local_easycustmenu'),
    ];
    $setting = new admin_setting_configselect($name, $title, $description, $default, $choices);
    $settings->add($setting);

    // Create primary navigation heading.
    $name = 'local_easycustmenu/setting_primarynav_heading';
    $title = get_string('setting_primarynav_heading', 'local_easycustmenu');
    $setting = new admin_setting_heading($name, $title, null);
    $settings->add($setting);

    // Setting: Hide nodes in primary navigation.
    // Prepare hide nodes options.
    $hidenodesoptions = [
        'home' => get_string('home'),
        'myhome' => get_string('myhome'),
        'courses' => get_string('mycourses'),
        'siteadminnode' => get_string('administrationsite'),
    ];
    $name = 'local_easycustmenu/hide_primarynavigation';
    $title = get_string('hide_primarynavigation_title', 'local_easycustmenu');
    $description = get_string('hide_primarynavigation_description', 'local_easycustmenu');
    $setting = new admin_setting_configmulticheckbox($name, $title, $description, [], $hidenodesoptions);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $settings->add($setting);
}
