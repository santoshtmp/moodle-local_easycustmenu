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

use core\output\action_menu;
use core\output\pix_icon;
use moodle_url;
use stdClass;

/**
 * class handle easycustmenu data
 *
 * @package    local_easycustmenu
 * @copyright  2025 santoshtmp <https://santoshmagar.com.np/>
 * @author     santoshtmp
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */
class easycustmenu_handler {
    /** @var string Database table name */
    protected static $menutable = 'local_easycustmenu';

    /**
     * Save or update a menu entry.
     *
     * @param stdClass $mformdata Form data object containing menu details.
     * @param string $returnurl URL to redirect after saving.
     * @param string $updatereturnurl URL to redirect after updating an existing record.
     * @return void Redirects to the specified URL after operation.
     * @throws \coding_exception
     */
    public static function save_data($mformdata, $returnurl, $updatereturnurl) {
        try {
            global $DB;
            $status = false;
            // Validate required fields.
            $menulabel = isset($mformdata->menu_label) ? $mformdata->menu_label : '';
            $menulink = isset($mformdata->menu_link) ? $mformdata->menu_link : '';
            if (!$menulabel || !$menulink) {
                $message = get_string('menu_error_submit', 'local_easycustmenu');
                redirect($returnurl, $message);
            }
            $menutype = isset($mformdata->menu_type) ? $mformdata->menu_type : 'navmenu';
            // Determine menu order.
            if (!$mformdata->id && !$mformdata->menu_order) {
                $menuorder = $DB->get_field_sql(
                    "SELECT MAX(menu_order) FROM {local_easycustmenu} WHERE menu_type = :menu_type",
                    ['menu_type' => $menutype]
                );
                if ($menuorder === false || $menuorder === null) {
                    $menuorder = 0;
                } else {
                    $menuorder++;
                }
            } else {
                $menuorder = $mformdata->menu_order ?? 0;
            }
            // Determine depth.
            $depth = 0;
            if (!empty($mformdata->parent) && $menutype == 'navmenu') {
                $parentdata = $DB->get_record(self::$menutable, ['id' => $mformdata->parent]);
                if ($parentdata) {
                    $depth = (int)$parentdata->depth + 1;
                }
            }
            // Prepare other conditions.
            $othercondition = [
                'label_tooltip_title' => isset($mformdata->label_tooltip_title) ? $mformdata->label_tooltip_title : '',
                'link_target' => isset($mformdata->link_target) ? $mformdata->link_target : 0,
            ];
            // Process the data.
            $data = new stdClass();
            $data->id = isset($mformdata->id) ? $mformdata->id : 0;
            $data->menu_type = $menutype;
            $data->context_level = isset($mformdata->context_level) ? $mformdata->context_level : CONTEXT_SYSTEM;
            $data->parent = isset($mformdata->parent) ? $mformdata->parent : 0;
            $data->depth = $depth;
            $data->menu_order = $menuorder;
            $data->menu_label = $menulabel;
            $data->menu_link = $menulink;
            $data->condition_courses = ($mformdata->context_level == 50) ? implode(',', $mformdata->condition_courses ?? []) : '';
            $data->condition_lang = isset($mformdata->condition_lang) ? implode(',', $mformdata->condition_lang ?? []) : '';
            $data->condition_roleid = isset($mformdata->condition_roleid) ? $mformdata->condition_roleid : 0;
            $data->other_condition = json_encode($othercondition);
            $data->timemodified = time();
            // Insert or update.
            if (!empty($data->id) && ($mformdata->action ?? '') === 'edit') {
                if ($DB->record_exists(self::$menutable, ['id' => $data->id])) {
                    $status = $DB->update_record(self::$menutable, $data);
                    if ($status) {
                        $a = new stdClass();
                        $a->menu_label = $data->menu_label;
                        $message = get_string('menu_updated', 'local_easycustmenu', $a);
                    }
                    $returnurl = $updatereturnurl;
                } else {
                    $message = get_string('menu_update_id_missing', 'local_easycustmenu');
                }
            } else {
                $data->timecreated = time();
                $status = $DB->insert_record(self::$menutable, $data);
                if ($status) {
                    $a = new stdClass();
                    $a->menu_label = $data->menu_label;
                    $message = get_string('menu_added', 'local_easycustmenu', $a);
                }
            }
        } catch (\Throwable $th) {
            $message = get_string('menu_error_submit', 'local_easycustmenu');
            $message .= "\n :: " . $th->getMessage();
        }

        redirect($returnurl, $message);
    }

    /**
     * Delete a menu entry.
     *
     * @param int $id Menu entry ID to delete.
     * @param string $returnurl URL to redirect after deletion.
     * @return void Redirects to the specified URL after operation.
     */
    public static function delete_data($id, $returnurl) {
        try {
            global $DB;
            if (!$id) {
                $message = get_string('menu_delete_missing', 'local_easycustmenu');
                redirect($returnurl, $message);
            }
            $data = $DB->get_record(self::$menutable, ['id' => $id]);
            if ($data) {
                $delete = $DB->delete_records(self::$menutable, ['id' => $data->id]);
                if ($delete) {
                    $a = new stdClass();
                    $a->menu_label = $data->menu_label;
                    $message = get_string('menu_delete', 'local_easycustmenu', $a);
                } else {
                    $message = get_string('menu_error_delete', 'local_easycustmenu');
                }
            } else {
                $message = get_string('menu_delete_missing', 'local_easycustmenu');
            }
        } catch (\Throwable $th) {
            $message = get_string('menu_error_delete', 'local_easycustmenu');
            $message .= "\n" . $th->getMessage();
        }

        redirect($returnurl, $message);
    }

    /**
     * Load menu data into a form for editing.
     *
     * @param \moodleform $mform Moodle form instance.
     * @param int $id Menu entry ID to edit.
     * @param string $returnurl URL to redirect in case of error.
     * @return \moodleform The form instance with data prefilled.
     */
    public static function edit_form($mform, $id, $returnurl) {
        try {
            global $DB;
            if (!$id) {
                return $mform;
            }
            $data = $DB->get_record(self::$menutable, ['id' => $id]);
            if ($data) {
                $othercondition = ($data->other_condition) ? json_decode($data->other_condition, true) : [];
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
                $entry->label_tooltip_title = $othercondition['label_tooltip_title'] ?? '';
                $entry->link_target = isset($othercondition['link_target']) ? $othercondition['link_target'] : 0;
                $mform->set_data($entry);
                return $mform;
            } else {
                $message = get_string('data_missing', 'local_easycustmenu');
            }
        } catch (\Throwable $th) {
            $message = $th->getMessage();
        }
        redirect($returnurl, $message);
    }

    /**
     * Recursively sort menu items into a tree structure.
     *
     * @param array|stdClass[] $menuitems Array of menu items.
     * @param int $parent Parent ID to start sorting from.
     * @return array Sorted array of menu items.
     */
    protected static function sort_menu_tree($menuitems, $parent = 0) {
        $sorteditems = [];
        foreach ($menuitems as $item) {
            if ($item->parent == $parent) {
                $sorteditems[] = $item;
                $sorteditems = array_merge($sorteditems, self::sort_menu_tree($menuitems, $item->id));
            }
        }
        return $sorteditems;
    }

    /**
     * Retrieve Easy Custom Menu items based on type, context, course, role, and language.
     *
     * @param string $type Menu type: 'navmenu' or 'usermenu'.
     * @param int $contextlevel Context level, e.g., 10 (system) or 50 (course).
     * @param int $courseid Course ID (required if context level is 50).
     * @param int[] $roleids Array of role IDs to filter menu items.
     * @param string $lang Language code to filter menu items.
     * @return array Array of menu item objects.
     */
    public static function get_ecm_menu_items($type = 'navmenu', $contextlevel = 0, $courseid = 0, $roleids = [], $lang = '') {
        global $DB;
        // Check if table exist.
        if (!$DB->get_manager()->table_exists('local_easycustmenu')) {
            return;
        }
        // SQL parameters and where condition.
        $whereconditionapply = '';
        $sqlparams = [
            'menu_type' => $type,
        ];
        $wherecondition = [
            'ecm.menu_type = :menu_type',
        ];
        // ... apply context level condition
        if ($contextlevel) {
            $sqlparams['context_level'] = $contextlevel;
            if ($contextlevel == CONTEXT_COURSE) {
                $sqlparams['context_level_system'] = CONTEXT_SYSTEM;
                $wherecondition[] = '(ecm.context_level = :context_level OR ecm.context_level = :context_level_system)';
            } else {
                $wherecondition[] = 'ecm.context_level = :context_level';
            }
        }
        // ... apply roleids condition
        if ($roleids) {
            [$insql, $inparams] = $DB->get_in_or_equal($roleids, SQL_PARAMS_NAMED, 'roleids');
            $wherecondition[] = "(ecm.condition_roleid = :everyone OR ecm.condition_roleid $insql)";
            $sqlparams = array_merge($sqlparams, $inparams);
            $sqlparams['everyone'] = 0;
        }
        // Where condition.
        if (count($wherecondition) > 0) {
            $whereconditionapply = "WHERE " . implode(" AND ", $wherecondition);
        }
        // SQL query.
        $sqlquery = 'SELECT * FROM {local_easycustmenu} ecm ' . $whereconditionapply . ' ORDER BY ecm.menu_order ASC';
        // Execute sql query.
        $menurecords = $DB->get_records_sql($sqlquery, $sqlparams);
        // ... check each row item
        foreach ($menurecords as $key => $item) {
            // ... apply courses condition on each row item
            if ($courseid && $contextlevel == CONTEXT_COURSE) {
                if ($item->context_level == CONTEXT_COURSE && $item->condition_courses) {
                    if (!in_array($courseid, explode(',', $item->condition_courses))) {
                        unset($menurecords[$key]);
                    }
                }
            }
            // ... apply lang condition.
            if ($lang && $item->condition_lang) {
                if (!in_array($lang, explode(',', $item->condition_lang))) {
                    unset($menurecords[$key]);
                }
            }
        }
        // ... rearrange the menu tree
        $menuitems = self::sort_menu_tree($menurecords);

        return $menuitems;
    }

    /**
     * Get a single ECM (Easy Custom Menu) item by ID.
     *
     * @param int $id Menu item ID.
     * @return stdClass|null Returns menu item object if found, null otherwise.
     */
    public static function get_ecm_menu_by_id($id) {
        global $DB;
        if (!$id) {
            return null;
        }
        return $DB->get_record(self::$menutable, ['id' => $id]);
    }

    /**
     * Get the Easy Custom Menu items table name.
     *
     * @param string $type Menu type ('navmenu' or 'usermenu').
     * @param string $pagepath
     * @return string Returns the database table name for the menu items.
     */
    public static function get_ecm_menu_items_table($type, $pagepath) {
        global $PAGE, $OUTPUT;

        $contextoptions = \local_easycustmenu\helper::get_ecm_context_level();
        $menus = self::get_ecm_menu_items($type);

        // Load JS.
        $PAGE->requires->js_call_amd('local_easycustmenu/menu_items', 'menuItemReorder', [$type . '-table']);
        $PAGE->requires->js_call_amd('local_easycustmenu/conformdelete', 'init');

        $childindentation = $OUTPUT->pix_icon(
            'child_indentation',
            'child-indentation',
            'local_easycustmenu',
            ['class' => 'child-icon indentation']
        );
        $childarrow = $OUTPUT->pix_icon(
            'child_arrow',
            'child-arrow-icon',
            'local_easycustmenu',
            ['class' => 'child-icon child-arrow']
        );
        $corerenderer = $PAGE->get_renderer('core');

        // Prepare menus for template.
        $menuitems = [];
        foreach ($menus as $menu) {
            // Action menu.
            $actionmenu = new action_menu();
            $actionmenu->set_kebab_trigger('Action', $corerenderer);
            $actionmenu->set_additional_classes('fields-actions');
            $actionurlparam = ['type' => $type, 'id' => $menu->id, 'sesskey' => sesskey()];

            $actionmenu->add(new \action_menu_link(
                new moodle_url($pagepath, ['action' => 'edit'] + $actionurlparam),
                new pix_icon('i/edit', 'edit'),
                get_string('edit', 'local_easycustmenu'),
                false,
                ['data-id' => $menu->id]
            ));

            $actionmenu->add(new \action_menu_link(
                new moodle_url($pagepath, ['action' => 'delete'] + $actionurlparam),
                new pix_icon('i/delete', 'delete'),
                get_string('delete', 'local_easycustmenu'),
                false,
                [
                    'class' => 'text-danger delete-action',
                    'data-id' => $menu->id,
                    'data-title' => format_string($menu->menu_label),
                    'data-heading' => get_string('delete_conform_heading', 'local_easycustmenu'),
                ]
            ));

            // Child indentation.
            $childindentationicon = '';
            if ($menu->depth) {
                for ($i = 0; $i < (int)$menu->depth - 1; $i++) {
                    $childindentationicon .= $childindentation;
                }
                $childindentationicon .= $childarrow;
            }

            // Menu item row.
            $menuitems[] = [
                'id' => $menu->id,
                'depth' => $menu->depth,
                'parent' => $menu->parent,
                'menu_order' => $menu->menu_order,
                'menu_label' => format_string($menu->menu_label),
                'menu_link' => $menu->menu_link,
                'context' => format_string($contextoptions[$menu->context_level]),
                'role_name' => \local_easycustmenu\helper::get_menu_role_name($menu->condition_roleid),
                'action_menu' => $corerenderer->render($actionmenu),
                'child_indentation_icon' => $childindentationicon,
            ];
        }

        $templatecontext = [
            'type' => $type,
            'menu_items' => $menuitems,
            'add_menu_url' => new \moodle_url($pagepath, ['type' => $type, 'action' => 'edit', 'id' => 0, 'sesskey' => sesskey()]),
            'child_indentation' => $childindentation,
            'child_arrow' => $childarrow,
        ];

        return $OUTPUT->render_from_template('local_easycustmenu/menu_items_table', $templatecontext);
    }

    // END.
}
