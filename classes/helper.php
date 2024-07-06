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

namespace local_easycustmenu;

defined('MOODLE_INTERNAL') || die();

class helper
{

    /**
     * check check_custum_header_menu
     */
    public function check_custum_header_menu()
    {
            global $PAGE;
            // hide primarynavigation if the data is present in hide_primarynavigation
            $theme = $PAGE->theme;
            $theme->removedprimarynavitems = explode(',', get_config('local_easycustmenu', 'hide_primarynavigation'));
            // define new custom menus
            $this->define_new_cfg_custommenuitems();
        
    }


    public function check_menu_line_role($condition_user_role,  $menu_line)
    {
        if ($condition_user_role == 'all') {
            return $menu_line  . "\n";
        } else if ($condition_user_role == 'guest') {
            if (!isloggedin() or isguestuser()) {
                return $menu_line  . "\n";
            }
        } else if ($condition_user_role == 'auth') {
            // if ($USER->id > 1) {
            if (isloggedin() && !isguestuser()) {
                return $menu_line  . "\n";
            }
        } else if ($condition_user_role == 'admin') {
            if (is_siteadmin()) {
                return $menu_line  . "\n";
            }
        }
        return '';
    }

    /**
     * 
     */
    public static function get_languages()
    {
        $langs = get_string_manager()->get_list_of_translations();
        $i = 0;
        $languages = [];
        foreach ($langs as $key => $value) {
            $languages[$i]['key'] = $key;
            $languages[$i]['value'] = $value;
            $i++;
        }
        return $languages;
    }

    /**
     * 
     */
    public static function get_condition_user_roles($role = '')
    {

        $roles = [
            [
                'key' => 'all',
                'value' => 'All users role',
                'is_selected' => ($role == 'all') ? true : false
            ],
            [
                'key' => 'guest',
                'value' => 'Guest user role',
                'is_selected' => ($role == 'guest') ? true : false
            ],
            [
                'key' => 'auth',
                'value' => 'Login user role',
                'is_selected' => ($role == 'auth') ? true : false
            ],
            [
                'key' => 'admin',
                'value' => 'Admin user role',
                'is_selected' => ($role == 'admin') ? true : false
            ],
        ];
        return $roles;
    }

    /**
     * chck and define new custommenuitems accoeding to custommenuitems
     */
    public function define_new_cfg_custommenuitems()
    {
        global $CFG;
        // check and update custom menu
        $custommenuitems = get_config('local_easycustmenu', 'custommenuitems');
        if ($custommenuitems) {
            $easycustmenu_text_output = '';
            $menu_depth_0_value = $menu_depth_1_value = 0;
            $lines = explode("\n", $custommenuitems);
            foreach ($lines as $linenumber => $line) {
                $line = trim($line);
                if (strlen($line) == 0) {
                    continue;
                }
                $settings = explode('|', $line);
                $item_user_role = isset($settings[4]) ? $settings[4] : '';
                // Get depth of new item.
                preg_match('/^(\-*)/', $line, $match);
                $itemdepth = strlen($match[1]);

                if ($itemdepth === 0) {
                    if ($menu_line = $this->check_menu_line_role($item_user_role, $line)) {
                        $easycustmenu_text_output .= $menu_line;
                        $menu_depth_0_value = 1;
                    } else {
                        $menu_depth_0_value = 0;
                    }
                } else if ($itemdepth === 1 &&  $menu_depth_0_value) {
                    if ($menu_line = $this->check_menu_line_role($item_user_role, $line)) {
                        $easycustmenu_text_output .= $menu_line;
                        $menu_depth_1_value = 1;
                    } else {
                        $menu_depth_1_value = 0;
                    }
                } else if ($itemdepth === 2 && $menu_depth_1_value) {
                    if ($menu_line = $this->check_menu_line_role($item_user_role, $line)) {
                        $easycustmenu_text_output .= $menu_line;
                    }
                }
            }
            $CFG->custommenuitems = $easycustmenu_text_output . $CFG->custommenuitems;
        }
    }

    /**
     * reverse to default $CFG->custommenuitems value
     */
    public static function revert_cfg_custommenuitems()
    {
        global $CFG;
        $CFG->custommenuitems = get_config('core','custommenuitems');
    }
}
