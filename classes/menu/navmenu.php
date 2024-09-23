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

namespace local_easycustmenu\menu;

use cache;
use local_easycustmenu\helper;
use moodle_url;

defined('MOODLE_INTERNAL') || die();

/**
 * class to handle navmenu admin action
 *
 * @package    local_easycustmenu
 * @copyright  2024 santoshtmp <https://santoshmagar.com.np/>
 * @author     santoshtmp
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class navmenu
{

    /**
     * set_easycustmenu for POST Method
     */
    public function set_easycustmenu()
    {
        $url = new moodle_url('/local/easycustmenu/pages/navmenu.php');
        if ($_POST) {
            // Get the parameters
            $label = optional_param_array('label', [], PARAM_RAW);
            $link = optional_param_array('link', [], PARAM_RAW);
            $target_blank = optional_param_array('target_blank', [], PARAM_RAW);
            $language = optional_param_array('language', [], PARAM_RAW);
            $user_role = optional_param_array('user_role', [], PARAM_RAW);
            $itemdepth = optional_param_array('itemdepth', [], PARAM_RAW);
            $sesskey = required_param('sesskey', PARAM_ALPHANUM);
            $remove_core_custommenuitems = optional_param('core_custommenuitems', 0, PARAM_INT);

            // check sesskey
            if ($sesskey == sesskey()) {
                $custommenuitems_text = '';
                foreach ($label as $key => $value) {
                    $prefix = '';
                    $itemdepth[$key] = (int)$itemdepth[$key];
                    if ($itemdepth[$key] > 1) {
                        for ($i = 1; $i < $itemdepth[$key]; $i++) {
                            $prefix .= '-';
                        }
                    }
                    // validate
                    if (str_contains($value, '|') || str_contains($link[$key], '|') || str_contains($language[$key], '|')) {
                        $message = "Something went wromg, <br> Input value contain '|' specific character. Which is not allowed.";
                        $messagetype = \core\output\notification::NOTIFY_WARNING;
                        redirect($url, $message, null, $messagetype);
                    }
                    // prepare each line
                    $each_line = $prefix . $value . "|" . $link[$key] .  "|" . "|" . $language[$key] . "|" . $user_role[$key] . "|" . $target_blank[$key] . "\n";
                    $custommenuitems_text = $custommenuitems_text .  $each_line;
                }
                // set custommenuitems_text
                try {
                    set_config('custommenuitems', $custommenuitems_text, 'local_easycustmenu');
                    if ($remove_core_custommenuitems == '1') {
                        set_config('custommenuitems', '');
                    }
                    $message = "Menu save sucessfully ";
                    $messagetype = \core\output\notification::NOTIFY_INFO;
                    // purge_all_caches();
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
     * get_easycustmenu 
     * return the custum menu setting form template
     * 
     */
    public function get_easycustmenu_setting_section($json = false)
    {
        global $OUTPUT;
        $url = new moodle_url('/local/easycustmenu/pages/navmenu.php');
        $easycustmenu_values = [];
        $core_custommenuitems = get_config('core', 'custommenuitems');
        $custommenuitems = get_config('local_easycustmenu', 'custommenuitems');
        $load_core_custommenuitems = optional_param('load_core_custommenuitems', 0, PARAM_INT);
        if ($load_core_custommenuitems) {
            $custommenuitems = $custommenuitems . "\n" . $core_custommenuitems;
        }
        $lines = explode("\n", $custommenuitems);
        // $menu_order = $menu_order_child_1 =  $menu_order_child_2 = -1;
        $menu_item_num = 1;
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
                        case 0: // Menu text.
                            $item_text = ltrim($setting, '-');
                            break;
                        case 1: // URL.
                            $item_url = $setting;
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
            // Get depth of new item.
            preg_match('/^(\-*)/', $line, $match);
            $itemdepth = strlen($match[1]);

            // arrange the menu values
            $pix = 24 * ($itemdepth + 1);
            $values = [
                'menu_item_num' => 'menu-' . $menu_item_num,
                'itemdepth' => $itemdepth + 1,
                'itemdepth_left_move' => 'padding-left: ' . $pix . 'px;',
                'label' => $item_text,
                'link' => $item_url,
                'target_blank' => $item_target_blank,
                'languages' => $item_languages,
                'condition_user_roles' => helper::get_condition_user_roles($item_user_role),
            ];
            $easycustmenu_values[] = $values;
            $menu_item_num++;
        }
        if ($json === true) {
            echo json_encode($easycustmenu_values);
            die;
        }


        $templatename = 'local_easycustmenu/menu_setting_collection';
        $context = [
            'menu_setting_form_action' => $url,
            'values' => $easycustmenu_values,
            'apply_condition' => true,
            'multi_lang' => (count(helper::get_languages()) > 1) ? true : false,
            'core_custommenuitems' => $core_custommenuitems,
            'load_core_custommenuitems' => $load_core_custommenuitems
        ];

        $contents = $OUTPUT->render_from_template($templatename, $context);
        return $contents;
    }
}
