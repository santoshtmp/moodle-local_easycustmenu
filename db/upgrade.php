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

use local_easycustmenu\handler\easycustmenu_handler;

defined('MOODLE_INTERNAL') || die;

function xmldb_local_easycustmenu_upgrade($oldversion) {
    global $CFG, $DB;

    $dbman = $DB->get_manager();

    $new_version = 2025081700;
    if ($oldversion < $new_version) {

        // Define table local_easycustmenu to be created.
        $table = new xmldb_table('local_easycustmenu');

        // Adding fields to table local_easycustmenu.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('menu_type', XMLDB_TYPE_CHAR, '50', null, XMLDB_NOTNULL, null, null);
        $table->add_field('menu_label', XMLDB_TYPE_CHAR, '225', null, XMLDB_NOTNULL, null, null);
        $table->add_field('menu_link', XMLDB_TYPE_CHAR, '225', null, XMLDB_NOTNULL, null, null);
        $table->add_field('context_level', XMLDB_TYPE_INTEGER, '4', null, XMLDB_NOTNULL, null, null);
        $table->add_field('parent', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('depth', XMLDB_TYPE_INTEGER, '4', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('menu_order', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('condition_courses', XMLDB_TYPE_CHAR, '150', null, XMLDB_NOTNULL, null, null);
        $table->add_field('condition_lang', XMLDB_TYPE_CHAR, '50', null, XMLDB_NOTNULL, null, null);
        $table->add_field('condition_roleid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('other_condition', XMLDB_TYPE_TEXT, null, null, null, null, null);
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');

        // Adding keys to table local_easycustmenu.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);

        // Conditionally launch create table for local_easycustmenu.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Easycustmenu savepoint reached.
        upgrade_plugin_savepoint(true, $new_version, 'local', 'easycustmenu');

        // 
        local_easycustmenu_convert_data_into_new_format();
    }

    return true;
}


/**
 * CConvert old data into new data format
 */
function local_easycustmenu_convert_data_into_new_format() {
    global $DB;
    // nav menu.
    $custommenuitems = get_config('local_easycustmenu', 'custommenuitems');
    if ($custommenuitems) {
        $new_navmenu = [];
        $lines = explode("\n", $custommenuitems);
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
                        case 0: // Prefix and Menu text.
                            $itemtext = $setting;
                            break;
                        case 1: // URL.
                            $itemurl = ($setting) ?: '#';
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
            if ($itemtext) {
                // Get depth of new item.
                preg_match('/^(\-*)/', $line, $match);
                $itemdepth = strlen($match[1]);
                // 
                if ($itemuserrole == 'all') {
                    $condition_roleid = 0;
                } else if ($itemuserrole == 'admin') {
                    $condition_roleid = -1;
                } else if ($itemuserrole == 'auth') {
                    $shortname = 'user';
                    $roles = $DB->get_record('role', ['shortname' => $shortname]);
                    $condition_roleid = isset($roles->id) ? $roles->id : 0;
                } else if ($itemuserrole == 'guest') {
                    $shortname = 'guest';
                    $roles = $DB->get_record('role', ['shortname' => $shortname]);
                    $condition_roleid = isset($roles->id) ? $roles->id : 0;
                } else {
                    $shortname = $itemuserrole;
                    $roles = $DB->get_record('role', ['shortname' => $shortname]);
                    $condition_roleid = isset($roles->id) ? $roles->id : 0;
                }
                // $condition_roleid =  $itemuserrole;
                $other_condition = [
                    'label_tooltip_title' => $title,
                    'link_target' => $itemtargetblank
                ];
                $new_navmenu[]  = [
                    'menu_type' => 'navmenu',
                    'depth' => $itemdepth,
                    'menu_order' => 0,
                    'menu_label' => ltrim($itemtext, '-'),
                    'menu_link' => $itemurl,
                    'condition_courses' => '',
                    'condition_lang' => $itemlanguages,
                    'condition_roleid' => $condition_roleid,
                    'other_condition' => $other_condition,
                ];
            }
        }
        // convert into child depth content array.
        $new_navmenu_tree = [];
        $stack = [];
        foreach ($new_navmenu as $item) {
            // add children container
            $item['children'] = [];
            $depth = $item['depth'];
            if ($depth == 0) {
                // top-level item
                $new_navmenu_tree[] = $item;
                $stack[0] = &$new_navmenu_tree[count($new_navmenu_tree) - 1];
            } else {
                // attach to parent
                $parent = &$stack[$depth - 1];
                $parent['children'][] = $item;
                $stack[$depth] = &$parent['children'][count($parent['children']) - 1];
            }
        }
        local_easycustmenu_save_menu_data($new_navmenu_tree, 0);
        unset_config('custommenuitems', 'local_easycustmenu');
    }
    // user menu
    $customusermenuitems = get_config('moodle', 'customusermenuitems');
    if ($customusermenuitems) {
        $new_usermenu = [];
        $lines = explode("\n", $customusermenuitems);
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
                        case 0: // Prefix and Menu text.
                            $itemtext = $setting;
                            break;
                        case 1: // URL.
                            $itemurl = ($setting) ?: '#';
                            break;
                        case 2: // Role.
                            $itemuserrole = $setting;
                            break;
                    }
                }
            }
            if ($itemtext) {
                // 
                if ($itemuserrole == 'all') {
                    $condition_roleid = 0;
                } else if ($itemuserrole == 'admin') {
                    $condition_roleid = -1;
                } else if ($itemuserrole == 'auth') {
                    $shortname = 'user';
                    $roles = $DB->get_record('role', ['shortname' => $shortname]);
                    $condition_roleid = isset($roles->id) ? $roles->id : 0;
                } else if ($itemuserrole == 'guest') {
                    $shortname = 'guest';
                    $roles = $DB->get_record('role', ['shortname' => $shortname]);
                    $condition_roleid = isset($roles->id) ? $roles->id : 0;
                } else {
                    $shortname = $itemuserrole;
                    $roles = $DB->get_record('role', ['shortname' => $shortname]);
                    $condition_roleid = isset($roles->id) ? $roles->id : 0;
                }

                $new_usermenu[] = [
                    'menu_type' => 'usermenu',
                    'context_level' => CONTEXT_SYSTEM,
                    'parent' => 0,
                    'depth' => 0,
                    'menu_order' => 0,
                    'menu_label' => $itemtext,
                    'menu_link' => $itemurl,
                    'condition_courses' => '',
                    'condition_lang' => '',
                    'condition_roleid' => $condition_roleid,
                    'other_condition' => [],
                ];
            }
        }
        $new_usermenu_tree = [];
        $stack = [];
        foreach ($new_usermenu as $item) {
            // add children container
            $item['children'] = [];
            $depth = $item['depth'];
            if ($depth == 0) {
                // top-level item
                $new_usermenu_tree[] = $item;
                $stack[0] = &$new_usermenu_tree[count($new_usermenu_tree) - 1];
            } else {
                // attach to parent
                $parent = &$stack[$depth - 1];
                $parent['children'][] = $item;
                $stack[$depth] = &$parent['children'][count($parent['children']) - 1];
            }
        }
        local_easycustmenu_save_menu_data($new_usermenu_tree, 0);
    }
}

/**
 * Save the menu data in order.
 */
function local_easycustmenu_save_menu_data($new_navmenu_tree, $parent_id = 0) {
    try {
        global $DB;
        foreach ($new_navmenu_tree as $key => $menu) {
            $menu_order = $DB->get_field_sql(
                "SELECT MAX(menu_order) FROM {local_easycustmenu} WHERE menu_type = :menu_type",
                ['menu_type' => $menu['menu_type']]
            );
            if ($menu_order === false || $menu_order === null) {
                $menu_order = 0;
            } else {
                $menu_order++;
            }
            // Process the data
            $data = new stdClass();
            $data->menu_type = $menu['menu_type'];
            $data->context_level = CONTEXT_SYSTEM;
            $data->parent = $parent_id;
            $data->depth = $menu['depth'];
            $data->menu_order = $menu_order;
            $data->menu_label = $menu['menu_label'];
            $data->menu_link = $menu['menu_link'];
            $data->condition_courses = '';
            $data->condition_lang = $menu['condition_lang'];
            $data->condition_roleid = $menu['condition_roleid'];
            $data->other_condition = json_encode($menu['other_condition']);
            $data->timemodified = time();
            $data->timecreated = time();
            $menu_id = $DB->insert_record(easycustmenu_handler::$menu_table, $data);
            if ($menu['children'] && count($menu['children']) > 0) {
                $child_menu_tree = $menu['children'];
                local_easycustmenu_save_menu_data($child_menu_tree, $menu_id);
            }
        }
    } catch (\Throwable $th) {
    }
}