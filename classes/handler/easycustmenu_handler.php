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
    public static $menu_table = 'local_easycustmenu';

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
     * Get the current menu condition.
     *
     * Rules for context object and its level:
     * - 50 if inside a real course (course id > 1)
     * - 10 for system context or front page (course id = 1)
     *
     * @return array {
     *     context       => context, // Moodle context object
     *     contextlevel  => int,     // 50 or 10
     *     courseid      => int,     // 0 for front page or system
     *     roleids       => array,   // role IDs, with -1 for site admin
     *     lang          => string   // current language code
     * }
     */
    public static function get_current_menu_condition() {
        global $PAGE, $COURSE, $USER;
        // Defaults.
        $context = \context_system::instance();
        $contextlevel   = CONTEXT_SYSTEM;
        $courseid = 0;
        $roleids = [];
        try {
            if (!empty($COURSE->id) && $COURSE->id > 1) {
                if ($PAGE->context->contextlevel === CONTEXT_COURSE or $PAGE->context->contextlevel === CONTEXT_MODULE) {
                    $context  = \context_course::instance($COURSE->id);
                    $contextlevel    = CONTEXT_COURSE;
                    $courseid = $COURSE->id;
                }
            }
            // Get user roles in this context.
            $roleids = [];
            if (is_siteadmin($USER->id)) {
                $roleids[] = '-1';
            }
            $assignedroles = get_user_roles($context, $USER->id, false);
            foreach ($assignedroles as $role) {
                $roleids[] = $role->roleid;
            }
        } catch (\Throwable $th) {
            //throw $th;
        }
        // 
        return [
            'context'  => $context,
            'contextlevel' => $contextlevel,
            'courseid' => $courseid,
            'roleids' => $roleids,
            'lang' => current_language()
        ];
    }

    /**
     * Get menu condition role name
     */
    public static function get_menu_role_name($condition_roleid) {

        global $DB;
        $role_name = '';
        if ($condition_roleid == 0) {
            $role_name = get_string('everyone', 'local_easycustmenu');
        } else if ($condition_roleid == '-1') {
            $role_name = get_string('admin');
        } else {
            $role = $DB->get_record('role', ['id' => $condition_roleid]);
            if ($role) {
                $role_name = role_get_name($role);
            }
        }
        return $role_name;
    }

    /**
     * Get role base on menu context
     */
    public static function get_context_roles($contextlevel) {
        global $DB;
        if (!empty($contextlevel)) {
            if ($contextlevel == CONTEXT_SYSTEM) {
               $sql = "SELECT r.*
                        FROM {role} r
                        LEFT JOIN {role_context_levels} rcl ON r.id = rcl.roleid
                        WHERE rcl.contextlevel = :contextlevel OR rcl.roleid IS NULL
                        ORDER BY r.sortorder ASC";

                return $DB->get_records_sql($sql, ['contextlevel' => $contextlevel]);

            } else {
                $sql = "SELECT r.*
                  FROM {role} r
                  JOIN {role_context_levels} rcl ON r.id = rcl.roleid
                 WHERE rcl.contextlevel = :contextlevel";
                return $DB->get_records_sql($sql, ['contextlevel' => $contextlevel]);
            }
        }

        return $DB->get_records('role');
    }

    /**
     * check and define menu items according to easycustmenu
     */
    public static function define_config_menuitems() {
        global $CFG;
        //
        $current_menu_condition = self::get_current_menu_condition();
        $contextlevel = $current_menu_condition['contextlevel'];
        $courseid = $current_menu_condition['courseid'];
        $roleids = $current_menu_condition['roleids'];
        $lang = $current_menu_condition['lang'];

        // 
        $custommenuitems = '';
        $navmenu = self::get_menu_items('navmenu', $contextlevel, $courseid, $roleids, $lang);
        if ($navmenu) {
            foreach ($navmenu as $key => $menu) {
                $other_condition = ($menu->other_condition) ? json_decode($menu->other_condition, true) : [];
                $itemtext = $menu->menu_label;
                $itemurl = $menu->menu_link;
                $title = isset($other_condition['label_tooltip_title']) ? $other_condition['label_tooltip_title'] : '';
                $itemlanguages = $menu->condition_lang;
                $depth = $menu->depth;
                $itemdepth = '';
                for ($i = 0; $i < $depth; $i++) {
                    $itemdepth .= '-';
                }
                $custommenuitems .= $itemdepth . $itemtext . "|" . $itemurl .  "|" . $title . "|" . $itemlanguages . "\n";
            }
            $CFG->custommenuitems = $custommenuitems;
        }

        // 
        $customusermenuitemsoutput = "";
        $usermenu = self::get_menu_items('usermenu', $contextlevel, $courseid, $roleids, $lang);
        if ($usermenu) {
            foreach ($usermenu as $key => $menu) {
                $itemtext = $menu->menu_label;
                $itemurl = $menu->menu_link;
                $customusermenuitemsoutput .= $itemtext . "|" . $itemurl . "\n";
            }
            $CFG->customusermenuitems = $customusermenuitemsoutput;
        }
    }

    // ------------------------------------------------------------------------------------------

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
            if (!$mform_data->id && !$mform_data->menu_order) {
                $menu_order = $DB->get_field_sql(
                    "SELECT MAX(menu_order) FROM {local_easycustmenu} WHERE menu_type = :menu_type",
                    ['menu_type' => $mform_data->menu_type]
                );
                if ($menu_order === false || $menu_order === null) {
                    $menu_order = 0;
                } else {
                    $menu_order++;
                }
            } else {
                $menu_order = $mform_data->menu_order;
            }
            //
            if ($mform_data->parent) {
                $parent_data = $DB->get_record(self::$menu_table, ['id' => $mform_data->parent]);
                if ($parent_data) {
                    $mform_data->depth = (int)$parent_data->depth + 1;
                }
            } else {
                $mform_data->depth = 0;
            }
            // 
            $other_condition = [
                'label_tooltip_title' => isset($mform_data->label_tooltip_title) ? $mform_data->label_tooltip_title : '',
                'link_target' => isset($mform_data->link_target) ? $mform_data->link_target : 0,
            ];
            // Process the data
            $data = new stdClass();
            $data->id = isset($mform_data->id) ? $mform_data->id : 0;
            $data->menu_type = $mform_data->menu_type;
            $data->context_level = $mform_data->context_level;
            $data->parent = $mform_data->parent;
            $data->depth = $mform_data->depth;
            $data->menu_order = $menu_order;
            $data->menu_label = $mform_data->menu_label;
            $data->menu_link = $mform_data->menu_link;
            $data->condition_courses = ($mform_data->context_level == 50) ? implode(',', $mform_data->condition_courses ?? []) : '';
            $data->condition_lang = ($mform_data->condition_lang) ? implode(',', $mform_data->condition_lang ?? []) : '';
            $data->condition_roleid = $mform_data->condition_roleid;
            $data->other_condition = json_encode($other_condition);
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
    public static function get_menu_items($type = 'navmenu', $context_level = 0, $courseid = 0, $roleids = [], $lang = '') {

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
     * Get menu items table.
     *
     * @param string $type
     * @return string
     */
    public static function get_menu_items_table($type, $page_path) {
        global $PAGE, $OUTPUT;
        $contextoptions = easycustmenu_handler::get_menu_context_level();
        $menus = self::get_menu_items($type);
        $PAGE->requires->js_call_amd('local_easycustmenu/menu_items', 'menu_item_reorder', [$type . '-table']);
        $PAGE->requires->js_call_amd('local_easycustmenu/conformdelete', 'init');
        $child_indentation = $OUTPUT->pix_icon('child_indentation', 'child-indentation', 'local_easycustmenu', ['class' => 'child-icon indentation']);
        $child_arrow = $OUTPUT->pix_icon('child_arrow', 'child-arrow-icon', 'local_easycustmenu', ['class' => 'child-icon child-arrow']);
        // content prepare
        $contents = '';
        $contents .= html_writer::start_tag('div', ['class' => $type . '-type-wrapper mt-4 mb-4']);
        $contents .= html_writer::link(
            new moodle_url($page_path, [
                'type' => $type,
                'action' => 'edit',
                'id' => 0,
                'sesskey' => sesskey()
            ]),
            get_string('add_menu_item', 'local_easycustmenu'),
            ['class' => 'btn btn-primary add-menu']
        );
        $contents .= html_writer::start_tag('table', ['id' => $type . '-table', 'class' => 'generaltable']);
        $contents .= html_writer::start_tag('thead');
        $contents .= html_writer::tag(
            'tr',
            html_writer::tag('th', get_string('label', 'local_easycustmenu')) .
                html_writer::tag('th', get_string('context')) .
                html_writer::tag('th', get_string('role')) .
                html_writer::tag('th', get_string('action'))
        );
        $contents .= html_writer::end_tag('thead');
        $contents .= html_writer::start_tag('tbody', ['data-type' => $type, 'data-action' => 'reorder']);
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
            $child_indentation_icon = '';
            if ($menu->depth) {
                for ($i = 0; $i < (int)$menu->depth - 1; $i++) {
                    $child_indentation_icon .= $child_indentation;
                }
                $child_indentation_icon .= $child_arrow;
            }
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
                    html_writer::tag('span', $child_indentation_icon, ['class' => 'child-icon-wrapper']) .
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
            $contents .= html_writer::tag('td', self::get_menu_role_name($menu->condition_roleid));
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
        $contents .= html_writer::tag(
            'div',
            html_writer::tag('div', $child_indentation, ['id' => 'child_indentation', 'style' => 'display: none;']) .
                html_writer::tag('div', $child_arrow, ['id' => 'child_arrow', 'style' => 'display: none;']),
            [
                'id' => 'depth-reusable-icon',
                'style' => 'display: none;'
            ]
        );
        $contents .= html_writer::end_tag('div');

        return $contents;
    }

    /**
     * 
     */
    public static function get_header_tab_part($page_path) {
        global $OUTPUT;
        $type = required_param('type', PARAM_ALPHANUMEXT); // navmenu, usermenu, etc.
        $section = optional_param('section', '', PARAM_ALPHANUMEXT);
        $templatename = 'local_easycustmenu/easycustmenu_setting_header';
        $templatecontext = [];
        $templatecontext['single_menu'] = [
            [
                'menu_active_class' => ($section == 'local_easycustmenu') ? 'active' : '',
                'menu_moodle_url' => new moodle_url('/admin/settings.php', ['section' => 'local_easycustmenu']),
                'menu_label' => get_string('general_setting', 'local_easycustmenu'),
            ],
            [
                'menu_active_class' => ($type == 'navmenu') ? "active" : "",
                'menu_moodle_url' => new moodle_url($page_path, ['type' => 'navmenu']),
                'menu_label' => get_string('header_nav_menu_setting', 'local_easycustmenu'),
            ],
            [
                'menu_active_class' => ($type == 'usermenu') ? "active" : "",
                'menu_moodle_url' => new moodle_url($page_path, ['type' => 'usermenu']),
                'menu_label' => get_string('user_menu_setting', 'local_easycustmenu'),
            ],
        ];
        return $OUTPUT->render_from_template($templatename, $templatecontext);
    }


    /**
     * ==== END =====
     */
}
