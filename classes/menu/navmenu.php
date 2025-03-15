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
 * class to handle navmenu admin action
 *
 * @package    local_easycustmenu
 * @copyright  2024 santoshtmp <https://santoshmagar.com.np/>
 * @author     santoshtmp
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class navmenu {

    /**
     * Set easycustmenu for POST Method.
     */
    public function set_easycustmenu() {
        global $CFG;
        $url = $CFG->wwwroot . '/local/easycustmenu/pages/navmenu.php';
        if ($_POST) {
            // Get the parameters.
            $label = optional_param_array('label', [], PARAM_TEXT);
            $link = optional_param_array('link', [], PARAM_URL);
            $targetblank = optional_param_array('target_blank', [], PARAM_INT);
            $language = optional_param_array('language', [], PARAM_TEXT);
            $userrole = optional_param_array('user_role', [], PARAM_TEXT);
            $itemdepth = optional_param_array('itemdepth', [], PARAM_INT);
            $sesskey = required_param('sesskey', PARAM_ALPHANUM);

            // Check sesskey.
            if ($sesskey == sesskey()) {
                $custommenuitemstext = '';
                foreach ($label as $key => $value) {
                    if (empty($value) || $value == '') {
                        continue;
                    }
                    $prefix = '';
                    $itemdepth[$key] = (int)$itemdepth[$key];
                    if ($itemdepth[$key] > 1) {
                        for ($i = 1; $i < $itemdepth[$key]; $i++) {
                            $prefix .= '-';
                        }
                    }
                    // Validate.
                    $link[$key] = isset($link[$key]) ? $link[$key] : '';
                    $language[$key] = isset($language[$key]) ? $language[$key] : '';
                    $userrole[$key] = isset($userrole[$key]) ? $userrole[$key] : '';
                    $targetblank[$key] = isset($targetblank[$key]) ? $targetblank[$key] : '';
                    if (str_contains($value, '|') || str_contains($link[$key], '|') || str_contains($language[$key], '|')) {
                        $message = get_string('something_wrong_input', 'local_easycustmenu');
                        $messagetype = \core\output\notification::NOTIFY_WARNING;
                        redirect($url, $message, null, $messagetype);
                    }
                    // Prepare each line.
                    $eachline = [
                        $prefix . $value,
                        $link[$key],
                        "",
                        $language[$key],
                        $userrole[$key],
                        $targetblank[$key],
                    ];
                    $custommenuitemstext = $custommenuitemstext .  implode("|", $eachline) . "\n";
                }
                // Set custommenuitems_text.
                try {
                    set_config('custommenuitems', $custommenuitemstext, 'local_easycustmenu');
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
     * get_easycustmenu
     * return the custum menu setting form template
     *
     */
    public function get_easycustmenu_setting_section($json = false) {
        global $OUTPUT,  $CFG;
        $url = $CFG->wwwroot . '/local/easycustmenu/pages/navmenu.php';
        $easycustmenuvalues = [];
        $corecustommenuitems = get_config('core', 'custommenuitems');
        $custommenuitems = get_config('local_easycustmenu', 'custommenuitems');
        $lines = explode("\n", $custommenuitems);
        $menuitemnum = 1;
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
                        case 0: // Menu text.
                            $itemtext = ltrim($setting, '-');
                            break;
                        case 1: // URL.
                            $itemurl = $setting;
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
            // Get depth of new item.
            preg_match('/^(\-*)/', $line, $match);
            $itemdepth = strlen($match[1]);

            // Arrange the menu values.
            $pix = 24 * ($itemdepth + 1);
            $values = [
                'menu_item_num' => 'menu-' . $menuitemnum,
                'itemdepth' => $itemdepth + 1,
                'itemdepth_left_move' => 'padding-left: ' . $pix . 'px;',
                'label' => $itemtext,
                'link' => $itemurl,
                'target_blank' => $itemtargetblank,
                'languages' => $itemlanguages,
                'condition_user_roles' => helper::get_condition_user_roles($itemuserrole),
            ];
            $easycustmenuvalues[] = $values;
            $menuitemnum++;
        }
        if ($json === true) {
            echo json_encode($easycustmenuvalues);
            die;
        }

        $templatename = 'local_easycustmenu/menu_setting_collection';
        $context = [
            'menu_setting_form_action' => $url,
            'values' => $easycustmenuvalues,
            'apply_condition' => true,
            'user_role_condition' => true,
            'new_tab_condition' => true,
            'multi_lang' => (count(helper::get_languages()) > 1) ? true : false,
            'core_custommenuitems' => $corecustommenuitems,
        ];

        $contents = $OUTPUT->render_from_template($templatename, $context);
        return $contents;
    }
}
