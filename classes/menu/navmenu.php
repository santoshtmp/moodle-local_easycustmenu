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

use local_easycustmenu\helper;
use moodle_url;

defined('MOODLE_INTERNAL') || die();

class navmenu
{

    /**
     * set_easycustmenu for POST Method
     */
    public function set_easycustmenu()
    {
        $url = new moodle_url('/local/easycustmenu/pages/navmenu.php');
        if ($_POST) {
            $label = optional_param_array('label', [], PARAM_RAW);
            $link = optional_param_array('link', [], PARAM_RAW);
            $target_blank = optional_param_array('target_blank', [], PARAM_RAW);
            $language = optional_param_array('language', [], PARAM_RAW);
            $user_role = optional_param_array('user_role', [], PARAM_RAW);
            $itemdepth = optional_param_array('itemdepth', [], PARAM_RAW);
            $sesskey = required_param('sesskey', PARAM_ALPHANUM);
            if ($sesskey == sesskey()) {
                $custommenuitems_text = '';
                foreach ($label as $key => $value) {
                    $prefix = $target_blank_value = '';
                    $itemdepth[$key] = (int)$itemdepth[$key];
                    if ($itemdepth[$key] > 1) {
                        for ($i = 1; $i < $itemdepth[$key]; $i++) {
                            $prefix .= '-';
                        }
                    }
                    if ($target_blank[$key] == '1') {
                        $target_blank_value = '"target="_blank';
                    }
                    $each_line = $prefix . $value . "|" . $link[$key] . $target_blank_value . "||" . $language[$key] . "|" . $user_role[$key] . "\n";
                    $custommenuitems_text = $custommenuitems_text .  $each_line;
                }
                // set custommenuitems_text
                try {
                    set_config('custommenuitems', $custommenuitems_text, 'local_easycustmenu');
                    $message = "Menu save sucessfully ";
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
     * get_easycustmenu 
     * return the custum menu setting form template
     * 
     */
    public function get_easycustmenu($json = false)
    {
        global $OUTPUT;
        $url = new moodle_url('/local/easycustmenu/pages/navmenu.php');
        $easycustmenu_values = [];
        $custommenuitems = get_config('local_easycustmenu', 'custommenuitems');
        $lines = explode("\n", $custommenuitems);
        $menu_order = $menu_order_child_1 =  $menu_order_child_2 = -1;
        $target_blank_value = '"target="_blank';
        foreach ($lines as $linenumber => $line) {
            $line = trim($line);
            if (strlen($line) == 0) {
                continue;
            }
            $settings = explode('|', $line);
            $item_text = $item_url = $item_languages = $item_user_role = '';
            $item_target_blank = false;
            foreach ($settings as $i => $setting) {
                $setting = trim($setting);
                if ($setting !== '') {
                    switch ($i) {
                        case 0: // Menu text.
                            $item_text = ltrim($setting, '-');
                            break;
                        case 1: // URL.
                            $item_target_blank = str_contains($setting, $target_blank_value);
                            $item_url = str_replace($target_blank_value, '', $setting);
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
                    }
                }
            }
            // Get depth of new item.
            preg_match('/^(\-*)/', $line, $match);
            $itemdepth = strlen($match[1]);

            // arrange the menu values
            $values = [
                'itemdepth' => $itemdepth + 1,
                'label' => $item_text,
                'link' => $item_url,
                'target_blank' => $item_target_blank,
                'languages' => $item_languages,
                'condition_user_roles' => helper::get_condition_user_roles($item_user_role),
            ];

            if ($itemdepth === 0) {
                $menu_order++;
                $menu_order_child_1 =  $menu_order_child_2 = -1;
                $values['sort_id'] = 'menu-' . $menu_order + 1;
                $easycustmenu_values[$menu_order] = $values;
            } else if ($itemdepth === 1) {
                $menu_order_child_1++;
                $menu_order_child_2 = -1;
                $values['sort_id'] = 'menu-' . $menu_order + 1 . '-' . $menu_order_child_1 + 1;
                $values['menu_child_num'] = 'menu-' . $menu_order + 1 . '-child';
                $easycustmenu_values[$menu_order]['child1'][$menu_order_child_1] = $values;
            } else if ($itemdepth === 2) {
                $menu_order_child_2++;
                $values['sort_id'] = 'menu-' . $menu_order + 1 . '-' . $menu_order_child_1 + 1 . '-' . $menu_order_child_2 + 1;
                $values['menu_child_num'] =  'menu-' . $menu_order + 1 . '-' . $menu_order_child_1 + 1 . '-child';
                $easycustmenu_values[$menu_order]['child1'][$menu_order_child_1]['child2'][$menu_order_child_2] = $values;
            }
        }
        if ($json === true) {
            echo json_encode($easycustmenu_values);
            die;
        }
        $templatename = 'local_easycustmenu/menu/menu_setting_collection';
        $context = [
            'menu_setting_form_action' => $url,
            'values' => $easycustmenu_values,
            'menu_child' => get_config('local_easycustmenu', 'menu_level')
        ];

        $contents = $OUTPUT->render_from_template($templatename, $context);
        return $contents;
    }

    /**
     * menu_item_wrapper
     * @return string :: the menu_item_wrapper template
     */
    public function menu_item_wrapper()
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
        ];
        $contents = $OUTPUT->render_from_template($templatename, $context);
        $contents = trim(str_replace(["\r", "\n"], '', $contents));
        return  $contents;
    }

    /**
     * check the permisssion  
     */
    public function menu_setting_access_check($context)
    {
        require_login();
        if (!has_capability('moodle/site:config', $context)) {
            echo "You don't have permission to access this pages";
            echo "<br>";
            echo "<a href='/'> Return Back</a>";
            die;
        }
    }

    /**
     * menu_main_script
     * @return string :: the menu_item_wrapper in the menu_item_wrapper function for browser
     */
    public function menu_main_script()
    {
        ob_start(); ?>
        <script>
            //<![CDATA[
            function menu_item_wrapper() {
                return '<?php echo $this->menu_item_wrapper(); ?>'
            }
            //]]>
        </script>
<?php
        $contents = ob_get_contents();
        ob_end_clean();
        return  $contents;
    }
}
