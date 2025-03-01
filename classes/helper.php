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

namespace local_easycustmenu;

use moodle_url;

defined('MOODLE_INTERNAL') || die();

/**
 * class to handle local_easycustmenu helper action
 *
 * @package    local_easycustmenu
 * @copyright  2024 santoshtmp <https://santoshmagar.com.np/>
 * @author     santoshtmp
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class helper
{

    /**
     * check check_custum_header_menu
     */
    public function check_custum_header_menu()
    {
        global $PAGE;
        // ... hide primarynavigation if the data is present in hide_primarynavigation
        $theme = $PAGE->theme;
        $activate = (get_config('local_easycustmenu', 'activate')) ?: "";
        if ($activate) {
            $theme->removedprimarynavitems = explode(',', get_config('local_easycustmenu', 'hide_primarynavigation'));
            $this->define_new_cfg_custommenuitems(); // define new custom menus
        }
    }


    /**
     * 
     */
    public function check_menu_line_role($condition_user_role)
    {
        if ($condition_user_role == 'all') {
            return true;
        } else if ($condition_user_role == 'guest') {
            if (!isloggedin() or isguestuser()) {
                return true;
            }
        } else if ($condition_user_role == 'auth') {
            if (isloggedin() && !isguestuser()) {
                return true;
            }
        } else if ($condition_user_role == 'admin') {
            if (is_siteadmin()) {
                return true;
            }
        }
        return false;
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
     * check and define new custommenuitems according to custommenuitems
     */
    public function define_new_cfg_custommenuitems()
    {
        global $CFG;
        global $target_blank_on_menu;
        $target_blank_on_menu = [];
        // check and update custom menu
        $custommenuitems = get_config('local_easycustmenu', 'custommenuitems');
        if ($custommenuitems) {
            $easycustmenu_text_output = '';
            $menu_depth_0_value = $menu_depth_1_value = $menu_depth_2_value = 0;
            $lines = explode("\n", $custommenuitems);
            foreach ($lines as $linenumber => $line) {
                $line = trim($line);
                if (strlen($line) == 0) {
                    continue;
                }
                $settings = explode('|', $line);
                $item_text = $item_url = $title = $item_languages = $item_user_role =  $item_target_blank = '';
                foreach ($settings as $i => $setting) {
                    $setting = trim($setting);
                    if ($setting !== '') {
                        switch ($i) {
                            case 0: // prefix and Menu text.
                                $item_text = $setting;
                                break;
                            case 1: // URL.
                                $item_url = ($setting) ?: '#';
                                break;
                            case 2: // title.
                                $title = $setting;
                                break;
                            case 3: // Language.
                                $item_languages = $setting;
                                break;
                            case 4: // user_role.
                                $item_user_role = $setting;
                                break;
                            case 5: // item_target_blank.
                                $item_target_blank = (int)$setting;
                                break;
                        }
                    }
                }
                if ($item_text) {
                    // check menu condition to open in new window for this line menu and then remove it from line menu data 
                    if ($item_target_blank) {
                        $target_blank_on_menu[ltrim($item_text, '-')] = $item_url;
                    }
                    // Get depth of new item.
                    preg_match('/^(\-*)/', $line, $match);
                    $itemdepth = strlen($match[1]);
                    // new menu line
                    $new_line = $item_text . "|" . $item_url .  "|" . $title . "|" . $item_languages . "\n";
                    // add menu line according to user role condition
                    if ($itemdepth === 0) {
                        if ($this->check_menu_line_role($item_user_role)) {
                            $easycustmenu_text_output .= $new_line;
                            $menu_depth_0_value = 1;
                        } else {
                            $menu_depth_0_value = 0;
                        }
                    } else if ($itemdepth === 1 &&  $menu_depth_0_value) {
                        if ($this->check_menu_line_role($item_user_role)) {
                            $easycustmenu_text_output .= $new_line;
                            $menu_depth_1_value = 1;
                        } else {
                            $menu_depth_1_value = 0;
                        }
                    } else if ($itemdepth === 2 && $menu_depth_1_value) {
                        if ($this->check_menu_line_role($item_user_role)) {
                            $easycustmenu_text_output .= $new_line;
                            $menu_depth_2_value = 1;
                        } else {
                            $menu_depth_2_value = 0;
                        }
                    } else if ($itemdepth === 3 && $menu_depth_2_value) {
                        if ($this->check_menu_line_role($item_user_role)) {
                            $easycustmenu_text_output .= $new_line;
                        }
                    }
                }
            }
            $CFG->custommenuitems = $easycustmenu_text_output; // . $CFG->custommenuitems;
        }

        // 
        $customusermenuitems = get_config('moodle', 'customusermenuitems');
        if ($customusermenuitems) {
            $customusermenuitems_output = "";
            $lines = explode("\n", $customusermenuitems);
            foreach ($lines as $linenumber => $line) {
                $line = trim($line);
                if (strlen($line) == 0) {
                    continue;
                }
                $settings = explode('|', $line);
                $item_text = $item_url = $item_user_role =  '';
                foreach ($settings as $i => $setting) {
                    $setting = trim($setting);
                    if ($setting !== '') {
                        switch ($i) {
                            case 0: // prefix and Menu text.
                                $item_text = $setting;
                                break;
                            case 1: // URL.
                                $item_url = ($setting) ?: '#';
                                break;
                            case 2: // role.
                                $item_user_role = $setting;
                                break;
                        }
                    }
                }
                if ($item_text) {
                    // new menu line
                    $new_line = $item_text . "|" . $item_url . "\n";
                    // add menu line according to user role condition
                    if ($this->check_menu_line_role($item_user_role)) {
                        $customusermenuitems_output .= $new_line;
                    }
                }
            }
            $CFG->customusermenuitems = $customusermenuitems_output;
        }
    }

    /**
     * menu_item_wrapper_section
     * @return string :: the menu_item_wrapper in the menu_item_wrapper function for browser
     */
    public static function menu_item_wrapper_section($apply_condition = true)
    {
        global $OUTPUT;
        $templatename = 'local_easycustmenu/menu_item_wrapper';
        $context = [
            'menu_item_num' => 'menu-id',
            'label' => '',
            'link' => '',
            'itemdepth' => '1',
            'condition_user_roles' => helper::get_condition_user_roles(),
            'apply_condition' => $apply_condition,
            'multi_lang' => (count(helper::get_languages()) > 1) ? true : false

        ];
        $contents = $OUTPUT->render_from_template($templatename, $context);
        $contents = trim(str_replace(["\r", "\n"], '', $contents));
        return  $contents;
    }


    /**
     * 
     */
    public static function before_footer_content()
    {
        $activate = (get_config('local_easycustmenu', 'activate')) ?: "";
        if (!$activate) {
            return;
        }

        global $PAGE, $target_blank_on_menu, $OUTPUT;
        $content = '';
        $script_content = $style_content = '';
        $allow_page_type = [
            'admin-setting-themesettingsadvanced',
            'admin-setting-themesettings',
            'admin-setting-local_easycustmenu',
            'easycustmenu_navmenu_setting',
            'easycustmenu_usermenu_setting'
        ];
        //
        if ($PAGE->pagelayout === 'admin' && in_array($PAGE->pagetype, $allow_page_type) && is_siteadmin()) {
            if (
                $PAGE->pagetype === 'admin-setting-local_easycustmenu' ||
                $PAGE->pagetype == 'easycustmenu_navmenu_setting' ||
                $PAGE->pagetype == 'easycustmenu_usermenu_setting'
            ) {
                $url = $_SERVER['REQUEST_URI'];
                $defined_urls = [
                    get_string('general_setting', 'local_easycustmenu') => "/admin/settings.php?section=local_easycustmenu",
                    get_string('header_nav_menu_setting', 'local_easycustmenu') => "/local/easycustmenu/pages/navmenu.php",
                    get_string('user_menu_settinig', 'local_easycustmenu') => "/local/easycustmenu/pages/usermenu.php"
                ];
                $templatename = 'local_easycustmenu/easycustmenu_setting_header';
                $template_context = [];
                foreach ($defined_urls as $label => $value_url) {
                    $single_menu = [
                        'menu_active_class' => (str_contains($url, $value_url)) ? "active" : "",
                        'menu_moodle_url' => new moodle_url($value_url),
                        'menu_label' => $label
                    ];
                    $template_context['single_menu'][] = $single_menu;
                }
                $plugin_header_content = $OUTPUT->render_from_template($templatename, $template_context);
                $plugin_header_content = trim(str_replace(["\r", "\n"], '', $plugin_header_content));
                $PAGE->requires->js_call_amd('local_easycustmenu/ecm', 'admin_plugin_setting_init', [$plugin_header_content]);
            } else {
                $show_ecm_core = get_config('local_easycustmenu', 'show_ecm_core');
                if ($show_ecm_core) {
                    $string_array = [
                        'show_menu_label' => get_string('show_menu_label', 'local_easycustmenu'),
                        'hide_menu_label' => get_string('hide_menu_label', 'local_easycustmenu'),
                        'manage_menu_label' => get_string('manage_menu_label', 'local_easycustmenu'),
                        'show_menu_label_2' => get_string('show_menu_label_2', 'local_easycustmenu'),
                        'hide_menu_label_2' => get_string('hide_menu_label_2', 'local_easycustmenu'),
                        'manage_menu_label_2' => get_string('manage_menu_label_2', 'local_easycustmenu'),
                    ];
                    $PAGE->requires->js_call_amd('local_easycustmenu/ecm', 'admin_core_setting_init', [$string_array]);
                }
            }
        }
        //
        // if (get_config('local_easycustmenu', 'menu_show_on_hover') == '1') {
        //     $style_content .= '
        //         ul.nav .nav-item:hover .dropdown-menu,
        //         ul.nav .nav-item .dropdown-menu:hover{
        //             display: block;
        //             margin-top: -2px;
        //         }
        //     ';
        // }

        // 
        if ($style_content) {
            $content .= '<style>' . $style_content . '</style>';
        }
        if ($script_content) {
            $content .= '<script>' . $script_content . '</script>';
        }
        if ($target_blank_on_menu) {
            $PAGE->requires->js_call_amd('local_easycustmenu/ecm', 'target_blank_menu', [json_encode($target_blank_on_menu)]);
        }

        return $content;
    }
}
