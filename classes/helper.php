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

use moodle_url;

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
            $easycustmenu_text_output = str_replace('target_blank_on', '"target="_blank', $easycustmenu_text_output);
            $CFG->custommenuitems = $easycustmenu_text_output . $CFG->custommenuitems;
        }
    }

    /**
     * reverse to default $CFG->custommenuitems value
     */
    public static function revert_cfg_custommenuitems()
    {
        global $CFG;
        $CFG->custommenuitems = get_config('core', 'custommenuitems');
    }


    /**
     * menu_item_wrapper_script
     * @return string :: the menu_item_wrapper in the menu_item_wrapper function for browser
     */
    public static function menu_item_wrapper_script()
    {
        global $OUTPUT;
        $templatename = 'local_easycustmenu/menu/menu_item_wrapper';
        $context = [
            'sort_id' => 'null-id',
            'label' => '',
            'link' => '',
            'itemdepth' => 'null-depth',
            'condition_user_roles' => helper::get_condition_user_roles(),
            'langs' => helper::get_languages(),
            'menu_child' => (get_config('local_easycustmenu', 'menu_level') == '2') ? true : false,
            'apply_condition' => true
        ];
        $contents = $OUTPUT->render_from_template($templatename, $context);
        $contents = trim(str_replace(["\r", "\n"], '', $contents));
        ob_start(); ?>
        <script>
            //<![CDATA[
            function menu_item_wrapper() {
                return '<?php echo $contents; ?>'
            }
            //]]>
        </script>
<?php
        $contents = ob_get_contents();
        ob_end_clean();
        return  $contents;
    }


    /**
     * 
     */
    public static function before_footer_content()
    {
        global $PAGE, $CFG;
        $content = '';
        if ($PAGE->pagelayout === 'admin' && is_siteadmin()) {
            $url = $_SERVER['REQUEST_URI'];
            $defined_urls = [
                "General Setting" => "/admin/settings.php?section=local_easycustmenu",
                "Header Nav Menu Setting" => "/local/easycustmenu/pages/navmenu.php",
                "User Menu Setting" => "/local/easycustmenu/pages/usermenu.php"

            ];
            $content .= '
            <div class="easycustmenu_setting_header_top" style="display:none;">
                <div class="easycustmenu_setting_header  ">
                    <h2>' . get_string('pluginname', 'local_easycustmenu') . '</h2>
                <div class="menu_setting_tabs moremenu" style=" display: flex;flex-wrap: wrap;gap: 12px;opacity:1;">
            ';
            foreach ($defined_urls as $key => $value) {
                $active_class = (str_contains($url, $value)) ? "active" : "";
                $content .= '<a href="' . $value . '" class="nav-link ' . $active_class . '">' . $key . '</a>';
            }
            $content .= '
                    </div>
                </div>
            </div>
            ';
            $content = trim(str_replace(["\r", "\n"], '', $content));
            $PAGE->requires->js(new moodle_url($CFG->wwwroot . '/local/easycustmenu/assets/js/ecm-setting-adjust.js'));
        }
        if (get_config('local_easycustmenu', 'menu_show_on_hover') == '1') {
            $content .= '
            <style id="easycustommenu-hover-menu">
                ul.nav .nav-item:hover .dropdown-menu,
                ul.nav .nav-item .dropdown-menu:hover{
                    display: block;
                    margin-top: -2px;
                }
            </style>
            ';
        }
        return $content;
    }
}
