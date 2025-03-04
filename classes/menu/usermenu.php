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

namespace local_easycustmenu\menu;

use local_easycustmenu\helper;
use moodle_url;

defined('MOODLE_INTERNAL') || die();

/**
 * class to handle usermenu admin action
 *
 * @package    local_easycustmenu
 * @copyright  2024 santoshtmp <https://santoshmagar.com.np/>
 * @author     santoshtmp
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class usermenu
{

    /**
     * set_usertmenu for POST Method
     */
    public function set_usertmenu()
    {
        $url = new moodle_url('/local/easycustmenu/pages/usermenu.php');
        if ($_POST) {
            $label = optional_param_array('label', [], PARAM_TEXT);
            $link = optional_param_array('link', [], PARAM_URL);
            $user_role = optional_param_array('user_role', [], PARAM_TEXT);

            // $user_role = optional_param_array('user_role', [], PARAM_TEXT);
            $sesskey = required_param('sesskey', PARAM_ALPHANUM);
            if ($sesskey == sesskey()) {
                $custommenuitems_text = '';
                foreach ($label as $key => $value) {
                    if (empty($value) || $value == '') {
                        continue;
                    }
                    // validate
                    if (str_contains($value, '|') || str_contains($link[$key], '|')) {
                        $message = "Something went wromg, <br> input menu value contain '|' specific character. Which is not allowed.";
                        $messagetype = \core\output\notification::NOTIFY_WARNING;
                        redirect($url, $message, null, $messagetype);
                    }
                    $each_line = $value . "|" . $link[$key] . "|" . $user_role[$key] . "\n";
                    $custommenuitems_text = $custommenuitems_text .  $each_line;
                }

                // set custommenuitems_text
                try {
                    set_config('customusermenuitems', $custommenuitems_text);
                    $message = "User Menu save sucessfully ";
                    $messagetype = \core\output\notification::NOTIFY_INFO;
                } catch (\Throwable $th) {
                    $message = "Something went wromg";
                    $messagetype = \core\output\notification::NOTIFY_WARNING;
                }
                redirect($url, $message, null, $messagetype);
            } else {
                echo "Your key is incorrect";
                echo "<br>";
                echo "<a href='" . $url . "'> Return Back</a>";
                die;
            }
        }
    }

    /**
     * get_usermenu_setting_section 
     * return the user menu setting form template
     * 
     */
    public function get_usermenu_setting_section($json = false)
    {
        global $OUTPUT;
        $url = new moodle_url('/local/easycustmenu/pages/usermenu.php');
        $easycustmenu_values = [];
        $custommenuitems = get_config('core', 'customusermenuitems');
        $lines = explode("\n", $custommenuitems);
        $menu_order = 1;
        $target_blank_value = 'target_blank_on';
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
                        case 0: // Menu text.
                            $item_text = ltrim($setting, '-');
                            break;
                        case 1: // URL.
                            $item_url = str_replace($target_blank_value, '', $setting);
                            break;
                        case 2: // user_role.
                            $item_user_role = $setting;
                            break;
                    }
                }
            }

            // arrange the menu values
            $values = [
                'itemdepth' => 1,
                'label' => $item_text,
                'link' => $item_url,
                'menu_item_num' => 'menu-' . $menu_order,
                'condition_user_roles' => helper::get_condition_user_roles($item_user_role),

            ];
            $easycustmenu_values[] = $values;
            $menu_order++;
        }
        if ($json === true) {
            echo json_encode($easycustmenu_values);
            die;
        }
        $templatename = 'local_easycustmenu/menu_setting_collection';
        $context = [
            'menu_setting_form_action' => $url,
            'values' => $easycustmenu_values,
            'menu_child' => false,
            'apply_condition' => true,
            'user_role_condition' => true,
            'multi_lang' => false  //(count(helper::get_languages()) > 1) ? true : false
        ];

        $contents = $OUTPUT->render_from_template($templatename, $context);
        return $contents;
    }
}
