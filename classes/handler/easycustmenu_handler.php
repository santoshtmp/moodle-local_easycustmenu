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
 * easycustmenu handler
 *
 * @package    local_easycustmenu
 * @copyright  2025 https://santoshmagar.com.np/
 * @author     santoshtmp7 https://github.com/santoshtmp/moodle-local_easycustmenu
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */



namespace local_easycustmenu\handler;

if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');    //  It must be included from a Moodle page.
}

use core\output\action_menu;
use core\output\pix_icon;
use moodle_url;
use stdClass;

/**
 *
 * @package    local_easycustmenu
 * @copyright  2025 santoshtmp <https://santoshmagar.com.np/>
 * @author     santoshtmp
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class easycustmenu_handler {
    // table name
    protected static $menu_table = 'local_easycustmenu';

    /**
     * Save Data
     * @param object $data
     * @param string $return_url
     */
    public static function save_data($mform_data, $return_url, $update_return_url) {
        try {
            global $DB;
            $status = false;
            // Validate required fields
            $menu_label = isset($mform_data->menu_label) ? $mform_data->menu_label : '';
            $menu_link = isset($mform_data->menu_link) ? $mform_data->menu_link : '';
            if (!$menu_label || !$menu_link) {
                $message = get_string('menu_error_submit', 'local_easycustmenu');
                redirect($return_url, $message);
            }
            $menu_type = isset($mform_data->menu_type) ? $mform_data->menu_type : 'navmenu';
            // Determine menu order
            if (!$mform_data->id && !$mform_data->menu_order) {
                $menu_order = $DB->get_field_sql(
                    "SELECT MAX(menu_order) FROM {local_easycustmenu} WHERE menu_type = :menu_type",
                    ['menu_type' => $menu_type]
                );
                if ($menu_order === false || $menu_order === null) {
                    $menu_order = 0;
                } else {
                    $menu_order++;
                }
            } else {
                $menu_order = $mform_data->menu_order;
            }
            // Determine depth
            $depth = 0;
            // if ($mform_data->depth == 0 && $mform_data->parent) {
            if (($mform_data->depth ?? 0) == 0 && !empty($mform_data->parent)) {
                $parent_data = $DB->get_record(self::$menu_table, ['id' => $mform_data->parent]);
                if ($parent_data) {
                    $depth = (int)$parent_data->depth + 1;
                }
            }
            // Prepare other conditions
            $other_condition = [
                'label_tooltip_title' => isset($mform_data->label_tooltip_title) ? $mform_data->label_tooltip_title : '',
                'link_target' => isset($mform_data->link_target) ? $mform_data->link_target : 0,
            ];
            // Process the data
            $data = new stdClass();
            $data->id = isset($mform_data->id) ? $mform_data->id : 0;
            $data->menu_type = $menu_type;
            $data->context_level = isset($mform_data->context_level) ? $mform_data->context_level : CONTEXT_SYSTEM;
            $data->parent = isset($mform_data->parent) ? $mform_data->parent : 0;
            $data->depth = $depth;
            $data->menu_order = $menu_order;
            $data->menu_label = $menu_label;
            $data->menu_link = $menu_link;
            $data->condition_courses = ($mform_data->context_level == 50) ? implode(',', $mform_data->condition_courses ?? []) : '';
            $data->condition_lang = isset($mform_data->condition_lang) ? implode(',', $mform_data->condition_lang ?? []) : '';
            $data->condition_roleid = isset($mform_data->condition_roleid) ? $mform_data->condition_roleid : 0;
            $data->other_condition = json_encode($other_condition);
            $data->timemodified = time();

            // Insert or update
            if (!empty($data->id) && ($mform_data->action ?? '') === 'edit') {
                if ($DB->record_exists(self::$menu_table, ['id' => $data->id])) {
                    $status =  $DB->update_record(self::$menu_table, $data);
                    if ($status) {
                        $a = new stdClass();
                        $a->menu_label = '"' . $data->menu_label . '" ';
                        $message = get_string('menu_updated', 'local_easycustmenu', $a);
                    }
                    $return_url = $update_return_url;
                } else {
                    $message = get_string('menu_update_id_missing', 'local_easycustmenu');
                }
            } else {
                $data->timecreated = time();
                $status = $DB->insert_record(self::$menu_table, $data);
                if ($status) {
                    $a = new stdClass();
                    $a->menu_label = $data->menu_label;
                    $message =  get_string('menu_added', 'local_easycustmenu', $a);
                }
            }
        } catch (\Throwable $th) {
            $message = get_string('menu_error_submit', 'local_easycustmenu');
            $message .= "\n :: " . $th->getMessage();
        }

        redirect($return_url, $message);
    }

    /**
     * Delete Data
     * @param int $id
     */
    public static function delete_data($id, $return_url) {
        try {
            global $DB;
            if (!$id) {
                $message =  get_string('menu_delete_missing', 'local_easycustmenu');
                redirect($return_url, $message);
            }
            $data = $DB->get_record(self::$menu_table, ['id' => $id]);
            if ($data) {
                $delete =  $DB->delete_records(self::$menu_table, ['id' => $data->id]);
                if ($delete) {
                    $a = new stdClass();
                    $a->menu_label = $data->menu_label;
                    $message =  get_string('menu_delete', 'local_easycustmenu', $a);
                } else {
                    $message =  get_string('menu_error_delete', 'local_easycustmenu');
                }
            } else {
                $message =  get_string('menu_delete_missing', 'local_easycustmenu');
            }
        } catch (\Throwable $th) {
            $message = get_string('menu_error_delete', 'local_easycustmenu');
            $message .= "\n" . $th->getMessage();
        }

        redirect($return_url, $message);
    }

    /**
     * edit form data
     * @param object $mform
     * @param int $id
     */
    public static function edit_form($mform, $id, $return_url) {

        try {
            global $DB;
            if (!$id) {
                return $mform;
            }
            $data = $DB->get_record(self::$menu_table, ['id' => $id]);
            if ($data) {
                $other_condition = ($data->other_condition) ? json_decode($data->other_condition, true) : [];
                $entry = new stdClass();
                $entry->id = $id;
                $entry->action = 'edit';
                $entry->parent = $data->parent;
                $entry->depth = $data->depth;
                $entry->menu_order = $data->menu_order;
                $entry->menu_type = $data->menu_type;
                $entry->context_level = $data->context_level;
                $entry->menu_label = $data->menu_label;
                $entry->menu_link = $data->menu_link;
                $entry->condition_courses = ($data->condition_courses) ? explode(',', $data->condition_courses) : [];
                $entry->condition_lang = $data->condition_lang;
                $entry->condition_roleid = $data->condition_roleid;
                $entry->label_tooltip_title = isset($other_condition['label_tooltip_title']) ? $other_condition['label_tooltip_title'] : '';
                $entry->link_target = isset($other_condition['link_target']) ? $other_condition['link_target'] : 0;
                $mform->set_data($entry);
                return $mform;
            } else {
                $message = get_string('data_missing', 'local_easycustmenu');
            }
        } catch (\Throwable $th) {
            //throw $th;
            $message = $th->getMessage();
        }
        redirect($return_url, $message);
    }

    /**
     * sort_menu_tree
     * @param array|object $menu_items
     * @param int $parent
     */
    protected static function sort_menu_tree($menu_items, $parent = 0) {
        $sorted_items = [];
        foreach ($menu_items as $item) {
            if ($item->parent == $parent) {
                $sorted_items[] = $item;
                $sorted_items = array_merge($sorted_items, self::sort_menu_tree($menu_items, $item->id));
            }
        }
        return $sorted_items;
    }

    /**
     * Get menu items by type.
     *
     * @param string $type navmenu or usermenu
     * @param int $context_level 10 or 50
     * @param int $courseid
     * @param array $roleids
     * @param string $lang
     * @return array
     */
    public static function get_ecm_menu_items($type = 'navmenu', $context_level = 0, $courseid = 0, $roleids = [], $lang = '') {

        global $DB;

        // sql parameters and where condition 
        $where_condition_apply = '';
        $sql_params = [
            'menu_type' => $type,
        ];
        $where_condition = [
            'ecm.menu_type = :menu_type',
        ];
        // 
        if ($context_level) {
            $sql_params['context_level'] = $context_level;
            $where_condition[] = ($context_level == '50') ? '(ecm.context_level = :context_level OR ecm.context_level = 10)' : 'ecm.context_level = :context_level';
        }
        if ($courseid && $context_level == '50') {
            $sql_params['courseid'] = $courseid;
            $where_condition[] = "(ecm.condition_courses = '' OR FIND_IN_SET(:courseid, ecm.condition_courses) > 0 )";
        }
        if ($roleids) {
            $where_condition[] = "(ecm.condition_roleid = '' OR ecm.condition_roleid IN ( " . implode(',', $roleids) . "))";
        }
        if ($lang) {
            $sql_params['lang'] = $lang;
            $where_condition[] = "(ecm.condition_lang = '' OR FIND_IN_SET(:lang, ecm.condition_lang) > 0 )";
        }
        // 
        if (count($where_condition) > 0) {
            $where_condition_apply = "WHERE " . implode(" AND ", $where_condition);
        }
        // sql query
        $sql_query = 'SELECT *        
            FROM {local_easycustmenu} AS ecm ' .
            $where_condition_apply . '
            ORDER BY ecm.menu_order ASC
        ';
        // execute sql query
        $menu_records = $DB->get_records_sql($sql_query, $sql_params);
        $menu_items = self::sort_menu_tree($menu_records);

        return $menu_items;
    }

    /**
     * Get menu item by id
     * @param int $id
     */
    public static function get_ecm_menu_by_id($id) {
        global $DB;
        if (!$id) {
            return null;
        }
        return $DB->get_record(self::$menu_table, ['id' => $id]);
    }

    /**
     * Get menu items table.
     *
     * @param string $type
     * @return string
     */
    public static function get_ecm_menu_items_table($type, $page_path) {
        global $PAGE, $OUTPUT;

        $contextoptions = \local_easycustmenu\helper::get_ecm_context_level();
        $menus = self::get_ecm_menu_items($type);

        // Load JS
        $PAGE->requires->js_call_amd('local_easycustmenu/menu_items', 'menu_item_reorder', [$type . '-table']);
        $PAGE->requires->js_call_amd('local_easycustmenu/conformdelete', 'init');

        $child_indentation = $OUTPUT->pix_icon('child_indentation', 'child-indentation', 'local_easycustmenu', ['class' => 'child-icon indentation']);
        $child_arrow = $OUTPUT->pix_icon('child_arrow', 'child-arrow-icon', 'local_easycustmenu', ['class' => 'child-icon child-arrow']);
        $core_renderer = $PAGE->get_renderer('core');

        // Prepare menus for template
        $$menu_items = [];
        foreach ($menus as $menu) {
            // Action menu
            $action_menu = new action_menu();
            $action_menu->set_kebab_trigger('Action', $core_renderer);
            $action_menu->set_additional_classes('fields-actions');
            $action_url_param = ['type' => $type, 'id' => $menu->id, 'sesskey' => sesskey()];

            $action_menu->add(new \action_menu_link(
                new moodle_url($page_path, ['action' => 'edit'] + $action_url_param),
                new pix_icon('i/edit', 'edit'),
                get_string('edit', 'local_easycustmenu'),
                false,
                ['data-id' => $menu->id]
            ));

            $action_menu->add(new \action_menu_link(
                new moodle_url($page_path, ['action' => 'delete'] + $action_url_param),
                new pix_icon('i/delete', 'delete'),
                get_string('delete', 'local_easycustmenu'),
                false,
                [
                    'class' => 'text-danger delete-action',
                    'data-id' => $menu->id,
                    'data-title' => format_string($menu->menu_label),
                    'data-heading' => get_string('delete_conform_heading', 'local_easycustmenu')
                ]
            ));

            // Child indentation
            $child_indentation_icon = '';
            if ($menu->depth) {
                for ($i = 0; $i < (int)$menu->depth - 1; $i++) {
                    $child_indentation_icon .= $child_indentation;
                }
                $child_indentation_icon .= $child_arrow;
            }

            // menu item row
            $menu_items[] = [
                'id' => $menu->id,
                'depth' => $menu->depth,
                'parent' => $menu->parent,
                'menu_order' => $menu->menu_order,
                'menu_label' => format_string($menu->menu_label),
                'context' => format_string($contextoptions[$menu->context_level]),
                'role_name' => \local_easycustmenu\helper::get_menu_role_name($menu->condition_roleid),
                'action_menu' => $core_renderer->render($action_menu),
                'child_indentation_icon' => $child_indentation_icon,
            ];
        }

        $templatecontext = [
            'type' => $type,
            'menu_items' => $menu_items,
            'add_menu_url' => new \moodle_url($page_path, ['type' => $type, 'action' => 'edit', 'id' => 0, 'sesskey' => sesskey()]),
            'child_indentation' => $child_indentation,
            'child_arrow' => $child_arrow
        ];

        return $OUTPUT->render_from_template('local_easycustmenu/menu_items_table', $templatecontext);
    }

    /**
     * ==== END =====
     */
}
