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
use stdClass;

/**
 * class to handle usermenu admin action
 *
 * @package    local_easycustmenu
 * @copyright  2024 santoshtmp <https://santoshmagar.com.np/>
 * @author     santoshtmp
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class usermenu {

    /**
     * set_usertmenu for POST Method
     */
    public function set_usertmenu() {
        $url = new moodle_url('/local/easycustmenu/pages/usermenu.php');
        if ($_POST) {
            $label = optional_param_array('label', [], PARAM_TEXT);
            $link = optional_param_array('link', [], PARAM_URL);
            $userrole = optional_param_array('user_role', [], PARAM_TEXT);
            $sesskey = required_param('sesskey', PARAM_ALPHANUM);
            if ($sesskey == sesskey()) {
                $custommenuitemstext = '';
                foreach ($label as $key => $value) {
                    if (empty($value) || $value == '') {
                        continue;
                    }
                    // Validate.
                    if (str_contains($value, '|') || str_contains($link[$key], '|')) {
                        $message = get_string('something_wrong_input', 'local_easycustmenu');
                        $messagetype = \core\output\notification::NOTIFY_WARNING;
                        redirect($url, $message, null, $messagetype);
                    }
                    $eachline = $value . "|" . $link[$key] . "|" . $userrole[$key] . "\n";
                    $custommenuitemstext = $custommenuitemstext .  $eachline;
                }

                // Set custommenuitems_text .
                try {
                    set_config('customusermenuitems', $custommenuitemstext);
                    $message = get_string('save_successfully', 'local_easycustmenu');
                    $messagetype = \core\output\notification::NOTIFY_INFO;
                } catch (\Throwable $th) {
                    $message = get_string('something_wrong', 'local_easycustmenu');
                    $messagetype = \core\output\notification::NOTIFY_WARNING;
                }
                redirect($url, $message, null, $messagetype);
            } else {
                $a = new stdClass();
                $a->name = '"' . $url . '" ';
                echo get_string('sesskey_incorrect', 'local_easycustmenu', $a);
                die;
            }
        }
    }

    /**
     * get_usermenu_setting_section
     * return the user menu setting form template
     *
     */
    public function get_usermenu_setting_section($json = false) {
        global $OUTPUT;
        $url = new moodle_url('/local/easycustmenu/pages/usermenu.php');
        $easycustmenuvalues = [];
        $custommenuitems = get_config('core', 'customusermenuitems');
        $lines = explode("\n", $custommenuitems);
        $menuorder = 1;
        $targetblankvalue = 'target_blank_on';
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
                        case 0: // Menu text.
                            $itemtext = ltrim($setting, '-');
                            break;
                        case 1: // URL.
                            $itemurl = str_replace($targetblankvalue, '', $setting);
                            break;
                        case 2: // User role.
                            $itemuserrole = $setting;
                            break;
                    }
                }
            }

            // Arrange the menu values.
            $values = [
                'itemdepth' => 1,
                'label' => $itemtext,
                'link' => $itemurl,
                'menu_item_num' => 'menu-' . $menuorder,
                'condition_user_roles' => helper::get_condition_user_roles($itemuserrole),

            ];
            $easycustmenuvalues[] = $values;
            $menuorder++;
        }
        if ($json === true) {
            echo json_encode($easycustmenuvalues);
            die;
        }
        $templatename = 'local_easycustmenu/menu_setting_collection';
        $context = [
            'menu_setting_form_action' => $url,
            'values' => $easycustmenuvalues,
            'menu_child' => false,
            'apply_condition' => true,
            'user_role_condition' => true,
            'multi_lang' => false,
        ];

        $contents = $OUTPUT->render_from_template($templatename, $context);
        return $contents;
    }
}
