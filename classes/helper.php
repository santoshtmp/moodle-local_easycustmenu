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

/**
 * class to handle local_easycustmenu helper action
 *
 * @package    local_easycustmenu
 * @copyright  2024 santoshtmp <https://santoshmagar.com.np/>
 * @author     santoshtmp
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class helper {

    /**
     * check check_custum_header_menu
     */
    public function check_custum_header_menu() {
        global $PAGE;
        // ... hide primarynavigation if the data is present in hide_primarynavigation
        $theme = $PAGE->theme;
        $activate = (get_config('local_easycustmenu', 'activate')) ?: "";
        if ($activate) {
            $theme->removedprimarynavitems = explode(',', get_config('local_easycustmenu', 'hide_primarynavigation'));
            $this->define_new_cfg_custommenuitems();
        }
    }


    /**
     * Checks if the current user has a specific role.
     * @param string $conditionuserrole
     * @return bool
     */
    public function check_menu_line_role($conditionuserrole) {
        if ($conditionuserrole == 'all') {
            return true;
        } else if ($conditionuserrole == 'guest') {
            if (!isloggedin() || isguestuser()) {
                return true;
            }
        } else if ($conditionuserrole == 'auth') {
            if (isloggedin() && !isguestuser()) {
                return true;
            }
        } else if ($conditionuserrole == 'admin') {
            if (is_siteadmin()) {
                return true;
            }
        }
        return false;
    }

    /**
     * get languages
     */
    public static function get_languages() {
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
     * get condition user roles
     */
    public static function get_condition_user_roles($role = '') {

        $roles = [
            [
                'key' => 'all',
                'value' => get_string('all_users_role', 'local_easycustmenu'),
                'is_selected' => ($role == 'all') ? true : false,
            ],
            [
                'key' => 'guest',
                'value' => get_string('guest_user_role', 'local_easycustmenu'),
                'is_selected' => ($role == 'guest') ? true : false,
            ],
            [
                'key' => 'auth',
                'value' => get_string('auth_login_user', 'local_easycustmenu'),
                'is_selected' => ($role == 'auth') ? true : false,
            ],
            [
                'key' => 'admin',
                'value' => get_string('admin_user', 'local_easycustmenu'),
                'is_selected' => ($role == 'admin') ? true : false,
            ],
        ];
        return $roles;
    }

    /**
     * check and define new custommenuitems according to custommenuitems
     */
    public function define_new_cfg_custommenuitems() {
        global $CFG;
        global $targetblankonmenu;
        $targetblankonmenu = [];
        // Check and update custom menu.
        $custommenuitems = get_config('local_easycustmenu', 'custommenuitems');
        if ($custommenuitems) {
            $easycustmenutextoutput = '';
            $menudepth0value = $menudepth1value = $menudepth2value = 0;
            $lines = explode("\n", $custommenuitems);
            foreach ($lines as $linenumber => $line) {
                $line = trim($line);
                if (strlen($line) == 0) {
                    continue;
                }
                $settings = explode('|', $line);
                $itemtext = $itemurl = $title = $itemlanguages = $itemuserrole = $itemtargetblank = '';
                foreach ($settings as $i => $setting) {
                    $setting = trim($setting);
                    if ($setting !== '') {
                        switch ($i) {
                            case 0: // Prefix and Menu text.
                                $itemtext = $setting;
                                break;
                            case 1: // URL.
                                $itemurl = ($setting) ?: '#';
                                break;
                            case 2: // Title.
                                $title = $setting;
                                break;
                            case 3: // Language.
                                $itemlanguages = $setting;
                                break;
                            case 4: // User role.
                                $itemuserrole = $setting;
                                break;
                            case 5: // Item_target_blank.
                                $itemtargetblank = (int)$setting;
                                break;
                        }
                    }
                }
                if ($itemtext) {
                    // Check menu condition to open in new window for this line menu and then remove it from line menu data .
                    if ($itemtargetblank) {
                        $targetblankonmenu[ltrim($itemtext, '-')] = $itemurl;
                    }
                    // Get depth of new item.
                    preg_match('/^(\-*)/', $line, $match);
                    $itemdepth = strlen($match[1]);
                    // New menu line.
                    $newline = $itemtext . "|" . $itemurl .  "|" . $title . "|" . $itemlanguages . "\n";
                    // Add menu line according to user role condition.
                    if ($itemdepth === 0) {
                        if ($this->check_menu_line_role($itemuserrole)) {
                            $easycustmenutextoutput .= $newline;
                            $menudepth0value = 1;
                        } else {
                            $menudepth0value = 0;
                        }
                    } else if ($itemdepth === 1 &&  $menudepth0value) {
                        if ($this->check_menu_line_role($itemuserrole)) {
                            $easycustmenutextoutput .= $newline;
                            $menudepth1value = 1;
                        } else {
                            $menudepth1value = 0;
                        }
                    } else if ($itemdepth === 2 && $menudepth1value) {
                        if ($this->check_menu_line_role($itemuserrole)) {
                            $easycustmenutextoutput .= $newline;
                            $menudepth2value = 1;
                        } else {
                            $menudepth2value = 0;
                        }
                    } else if ($itemdepth === 3 && $menudepth2value) {
                        if ($this->check_menu_line_role($itemuserrole)) {
                            $easycustmenutextoutput .= $newline;
                        }
                    }
                }
            }
            $CFG->custommenuitems = $easycustmenutextoutput;
        }

        $customusermenuitems = get_config('moodle', 'customusermenuitems');
        if ($customusermenuitems) {
            $customusermenuitemsoutput = "";
            $lines = explode("\n", $customusermenuitems);
            foreach ($lines as $linenumber => $line) {
                $line = trim($line);
                if (strlen($line) == 0) {
                    continue;
                }
                $settings = explode('|', $line);
                $itemtext = $itemurl = $itemuserrole = '';
                foreach ($settings as $i => $setting) {
                    $setting = trim($setting);
                    if ($setting !== '') {
                        switch ($i) {
                            case 0: // Prefix and Menu text.
                                $itemtext = $setting;
                                break;
                            case 1: // URL.
                                $itemurl = ($setting) ?: '#';
                                break;
                            case 2: // Role.
                                $itemuserrole = $setting;
                                break;
                        }
                    }
                }
                if ($itemtext) {
                    // New menu line.
                    $newline = $itemtext . "|" . $itemurl . "\n";
                    // Add menu line according to user role condition.
                    if ($this->check_menu_line_role($itemuserrole)) {
                        $customusermenuitemsoutput .= $newline;
                    }
                }
            }
            $CFG->customusermenuitems = $customusermenuitemsoutput;
        }
    }

    /**
     * menu_item_wrapper_section
     * @return string :: the menu_item_wrapper in the menu_item_wrapper function for browser
     */
    public static function menu_item_wrapper_section($applycondition = true) {
        global $OUTPUT;
        $templatename = 'local_easycustmenu/menu_item_wrapper';
        $context = [
            'menu_item_num' => 'menu-id',
            'label' => '',
            'link' => '',
            'itemdepth' => '1',
            'condition_user_roles' => self::get_condition_user_roles(),
            'apply_condition' => $applycondition,
            'multi_lang' => (count(self::get_languages()) > 1) ? true : false,
        ];
        $contents = $OUTPUT->render_from_template($templatename, $context);
        $contents = trim(str_replace(["\r", "\n"], '', $contents));
        return  $contents;
    }


    /**
     * before_footer_content
     */
    public static function before_footer_content() {
        $activate = (get_config('local_easycustmenu', 'activate')) ?: "";
        if (!$activate) {
            return;
        }

        global $PAGE, $targetblankonmenu, $OUTPUT;
        $content = '';
        $scriptcontent = $stylecontent = '';
        $allowpagetype = [
            'admin-setting-themesettingsadvanced',
            'admin-setting-themesettings',
            'admin-setting-local_easycustmenu',
            'easycustmenu_navmenu_setting',
            'easycustmenu_usermenu_setting',
        ];

        if ($PAGE->pagelayout === 'admin' && in_array($PAGE->pagetype, $allowpagetype) && is_siteadmin()) {
            if (
                $PAGE->pagetype === 'admin-setting-local_easycustmenu' ||
                $PAGE->pagetype == 'easycustmenu_navmenu_setting' ||
                $PAGE->pagetype == 'easycustmenu_usermenu_setting'
            ) {
                $url = $_SERVER['REQUEST_URI'];
                $definedurls = [
                    get_string('general_setting', 'local_easycustmenu') => "/admin/settings.php?section=local_easycustmenu",
                    get_string('header_nav_menu_setting', 'local_easycustmenu') => "/local/easycustmenu/pages/navmenu.php",
                    get_string('user_menu_setting', 'local_easycustmenu') => "/local/easycustmenu/pages/usermenu.php",
                ];
                $templatename = 'local_easycustmenu/easycustmenu_setting_header';
                $templatecontext = [];
                foreach ($definedurls as $label => $valueurl) {
                    $singlemenu = [
                        'menu_active_class' => (str_contains($url, $valueurl)) ? "active" : "",
                        'menu_moodle_url' => new moodle_url($valueurl),
                        'menu_label' => $label,
                    ];
                    $templatecontext['single_menu'][] = $singlemenu;
                }
                $pluginheadercontent = $OUTPUT->render_from_template($templatename, $templatecontext);
                $pluginheadercontent = trim(str_replace(["\r", "\n"], '', $pluginheadercontent));
                $PAGE->requires->js_call_amd('local_easycustmenu/ecm', 'admin_plugin_setting_init', [$pluginheadercontent]);
            } else {
                $showecmcore = get_config('local_easycustmenu', 'show_ecm_core');
                if ($showecmcore) {
                    $stringarray = [
                        'show_menu_label' => get_string('show_menu_label', 'local_easycustmenu'),
                        'hide_menu_label' => get_string('hide_menu_label', 'local_easycustmenu'),
                        'manage_menu_label' => get_string('manage_menu_label', 'local_easycustmenu'),
                        'show_menu_label_2' => get_string('show_menu_label_2', 'local_easycustmenu'),
                        'hide_menu_label_2' => get_string('hide_menu_label_2', 'local_easycustmenu'),
                        'manage_menu_label_2' => get_string('manage_menu_label_2', 'local_easycustmenu'),
                    ];
                    $PAGE->requires->js_call_amd('local_easycustmenu/ecm', 'admin_core_setting_init', [$stringarray]);
                }
            }
        }
        // Check style and script.
        if ($stylecontent) {
            $content .= '<style>' . $stylecontent . '</style>';
        }
        if ($scriptcontent) {
            $content .= '<script>' . $scriptcontent . '</script>';
        }
        if ($targetblankonmenu) {
            $PAGE->requires->js_call_amd('local_easycustmenu/ecm', 'target_blank_menu', [json_encode($targetblankonmenu)]);
        }

        return $content;
    }
}
