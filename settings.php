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

defined('MOODLE_INTERNAL') || die();


if ($hassiteconfig) {
    // Heading.
    $settings = new admin_settingpage('local_easycustmenu', get_string('pluginname', 'local_easycustmenu'));
    // $ADMIN->add('localplugins', $settings);
    $ADMIN->add('appearance', $settings);

    // Create primary navigation heading.
    $name = 'local_easycustmenu/setting_primarynav_heading';
    $title = get_string('setting_primarynav_heading', 'local_easycustmenu');
    $setting = new admin_setting_heading($name, $title, null);
    $settings->add($setting);

    // Setting: Hide nodes in primary navigation.
    // Prepare hide nodes options.
    $hidenodesoptions = array(
        'home' => get_string('home'),
        'myhome' => get_string('myhome'),
        'courses' => get_string('mycourses'),
        'siteadminnode' => get_string('administrationsite')
    );
    $name = 'local_easycustmenu/hide_primarynavigation';
    $title = get_string('hide_primarynavigation_title', 'local_easycustmenu');
    $description = get_string('hide_primarynavigation_description', 'local_easycustmenu');
    $setting = new admin_setting_configmulticheckbox($name, $title, $description, array(), $hidenodesoptions);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $settings->add($setting);

    // Create custom_menu_heading.
    $name = 'local_easycustmenu/custom_menu_heading';
    $title = get_string('setting_custom_menu_heading', 'local_easycustmenu');
    $description = '';
    $setting = new admin_setting_heading($name, $title, $description);
    $settings->add($setting);

    $name = 'local_easycustmenu/menu_level';
    $title = 'Menu level';
    $description = 'This will allow to set the menu depth child for custom menu items.
        <br> If "1" is selected the sub-menu can be added to main menu.
        <br> If "2" is selected the sub-menu-child can be added to sub-menu. The display of "sub-menu-child header nav" depends on the current theme template. 
        <br>
        ';
    $default = 0;
    $options = array();
    for ($i = 0; $i < 3; $i++) {
        $options[$i] = $i;
    }
    $setting = new admin_setting_configselect($name, $title, $description, $default, $options);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $settings->add($setting);

    $name = 'local_easycustmenu/menu_show_on_hover';
    $title = "Header nav menu show on hover";
    $description = 'Heaer primary menu child will be visible on hover without click on menu. By default menu need to be click to see the child menu. Ignore it, if this feature is alrady managed by current theme ';
    $setting = new admin_setting_configcheckbox($name, $title, $description, '0');
    $setting->set_updatedcallback('theme_reset_all_caches');
    $settings->add($setting);
}
