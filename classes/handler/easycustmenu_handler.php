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
use core\output\html_writer;
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
     * Get menu types.
     *
     * @return array
     */
    public static function get_menu_type() {
        return [
            'navmenu' => get_string('navmenu', 'local_easycustmenu'),
            'usermenu' => get_string('usermenu', 'local_easycustmenu'),
        ];
    }

    /**
     * Get menu context levels.
     *
     * @return array
     */
    public static function get_menu_context_level() {
        return [
            10 => get_string('site'),
            50 => get_string('course')
        ];
    }

    /**
     * Save Data
     * @param object $data
     * @param string $return_url
     */
    public static function save_data($mform_data, $return_url, $update_return_url) {
        try {
            global $DB;
            $status = false;
            // 
            $maxorder = $DB->get_field_sql(
                "SELECT MAX(menu_order) FROM {local_easycustmenu} WHERE menu_type = :menu_type",
                ['menu_type' => $mform_data->menu_type]
            );
            if ($maxorder === false || $maxorder === null) {
                $maxorder = 0;
            } else {
                $maxorder++;
            }
            // Process the data
            $data = new stdClass();
            $data->id = isset($mform_data->id) ? $mform_data->id : 0;
            $data->menu_type = $mform_data->menu_type;
            $data->context_level = $mform_data->context_level;
            $data->parent = 0;
            $data->depth = 0;
            $data->menu_order = $maxorder;
            $data->menu_label = $mform_data->menu_label;
            $data->menu_link = $mform_data->menu_link;
            $data->condition_courses = ($mform_data->context_level == 50) ? implode(',', $mform_data->courses ?? []) : '';
            $data->condition_lang = $mform_data->condition_lang;
            $data->condition_roleid = $mform_data->condition_roleid;
            $data->other_condition = '';
            $data->timemodified = time();

            // 
            if ($data->id && ($mform_data->action == 'edit')) {
                $data_exists = $DB->record_exists(self::$menu_table, ['id' =>  $data->id]);
                if ($data_exists) {
                    $status =  $DB->update_record(self::$menu_table, $data);
                    if ($status) {
                        $a = new stdClass();
                        $a->menu_label = '"' . $data->menu_label . '" ';
                        $message = get_string('menu_updated', 'local_easycustmenu', $a);
                    }
                }
                $return_url = $update_return_url;
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
            global $DB, $CFG;
            // var_dump('returning empty form');
            // die;

            if (!$id) {
                return $mform;
            }
            $data = $DB->get_record(self::$menu_table, ['id' => $id]);
            if ($data) {
                $entry = new stdClass();
                $entry->id = $id;
                $entry->action = 'edit';
                $entry->menu_type = $data->menu_type;
                $entry->context_level = $data->context_level;
                $entry->menu_label = $data->menu_label;
                $entry->menu_link = $data->menu_link;
                $entry->condition_courses = ($data->condition_courses) ? explode(',', $data->condition_courses) : [];
                $entry->condition_lang = $data->condition_lang;
                $entry->condition_roleid = $data->condition_roleid;
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
     * Get menu items by type.
     *
     * @param string $type
     * @return array
     */
    public static function get_menu_items(
        $type = 'navmenu',
        $context_level = 10,
        $courseid = ''
    ) {

        global $DB, $CFG;

        // sql parameters and where condition 
        $where_condition_apply = '';

        $sql_params = [
            'menu_type' => $type,
        ];

        $where_condition = [
            'ecm.menu_type = :menu_type',
        ];


        // $sql_params['context_level'] = $context_level;
        // $where_condition[] = 'ecm.context_level = :context_level';

        // $context = context_system::instance(); // or context_course::instance($courseid), etc.
        // $currentcontextlevel = $context->contextlevel;


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
        $data_records = $DB->get_records_sql($sql_query, $sql_params);

        return $data_records;
    }

    /**
     * Get menu items table.
     *
     * @param string $type
     * @return string
     */
    public static function get_menu_items_table($type) {
        global $PAGE;

        $menus = easycustmenu_handler::get_menu_items($type, 10);
        $tableid = $type . '-table';
        $PAGE->requires->js_call_amd('local_easycustmenu/menu_items', 'menu_item_reorder', [$tableid]);
        $PAGE->requires->js_call_amd('local_easycustmenu/conformdelete', 'init');

        $contents = '';
        $contents .= html_writer::start_tag('table', ['id' => $tableid, 'class' => 'generaltable']);
        $contents .= html_writer::start_tag('thead');
        $contents .= html_writer::tag(
            'tr',
            html_writer::tag('th', 'Label') .
                html_writer::tag('th', 'Context') .
                html_writer::tag('th', 'Action')
        );
        $contents .= html_writer::end_tag('thead');

        $contents .= html_writer::start_tag('tbody', ['data-type' => $type, 'data-action' => 'reorder']);
        $contextoptions = easycustmenu_handler::get_menu_context_level();

        foreach ($menus as $menu) {
            // action menu
            $core_renderer = $PAGE->get_renderer('core');
            $action_menu = new action_menu();
            $action_menu->set_kebab_trigger('Action', $core_renderer);
            $action_menu->set_additional_classes('fields-actions');
            $action_url_param = [
                'type' => $type,
                'id' => $menu->id,
                'sesskey' => sesskey()
            ];
            $action_menu->add(new \action_menu_link(
                new moodle_url('', ['action' => 'edit'] + $action_url_param),
                new pix_icon('i/edit', 'edit'),
                get_string('edit', 'local_easycustmenu'),
                false,
                [
                    'data-id' => $menu->id,
                ]
            ));
            $action_menu->add(new \action_menu_link(
                new moodle_url('', ['action' => 'delete'] +  $action_url_param),
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

            // output the menu item row
            $contents .= html_writer::start_tag(
                'tr',
                [
                    'data-id' => $menu->id,
                    'data-depth' => (int)$menu->depth,
                    'data-parent' => (int)$menu->parent,
                    'data-menu_order' => (int)$menu->menu_order,
                    'data-menu_label' => $menu->menu_label
                ]
            );
            $contents .= html_writer::tag(
                'td',
                html_writer::tag(
                    'span',
                    html_writer::tag('span', '', ['class' => 'indentation', 'style' => "display: inline-block; width:" . (30 * (int)$menu->depth) . "px "]) .
                        html_writer::tag('i', '', ['class' => 'icon fa fa-arrows-up-down-left-right fa-fw', 'role' => "img"]),
                    ['class' => 'float-start drag-handle', "data-drag-type" => "move"]
                ) .
                    html_writer::tag(
                        'span',
                        format_string($menu->menu_label),
                        ['class' => 'menu-label']
                    )
            );
            $contents .= html_writer::tag(
                'td',
                html_writer::tag(
                    'span',
                    format_string($contextoptions[$menu->context_level]),
                    ['class' => 'menu-context']
                )
            );
            $contents .= html_writer::tag('td', $core_renderer->render($action_menu));
            $contents .= html_writer::end_tag('tr');
        }

        $contents .= html_writer::end_tag('tbody');
        $contents .= html_writer::end_tag('table');
        $contents .= html_writer::tag(
            'button',
            get_string('save_order', 'local_easycustmenu'),
            [
                'id' => 'save_menu_reorder',
                'class' => 'btn btn-primary mt-3',
                'type' => 'button',
                'style' => 'display: none;'
            ]
        );
        
        return $contents;
    }


    /**
     * ==== END =====
     */
}
