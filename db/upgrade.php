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

/**
 * Execute local_easycustmenu upgrade steps from the given old version.
 *
 * This function is called automatically during the upgrade process
 * when the version number in version.php is increased.
 *
 * @param int $oldversion The version number we are upgrading from.
 * @return bool Always true on success.
 */
function xmldb_local_easycustmenu_upgrade($oldversion) {
    global $CFG, $DB;

    $dbman = $DB->get_manager();

    $newversion = 2025081800;
    if ($oldversion < $newversion) {
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
        upgrade_plugin_savepoint(true, $newversion, 'local', 'easycustmenu');
        local_easycustmenu_convert_data_into_new_format();
    }

    return true;
}


/**
 * Convert old data into new data format
 */
function local_easycustmenu_convert_data_into_new_format() {
    try {
        global $DB;
        // ... nav menu
        $custommenuitems = get_config('local_easycustmenu', 'custommenuitems');
        if ($custommenuitems) {
            $newnavmenu = [];
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
                    if ($itemuserrole == 'all') {
                        $conditionroleid = 0;
                    } else if ($itemuserrole == 'admin') {
                        $conditionroleid = -1;
                    } else if ($itemuserrole == 'auth') {
                        $shortname = 'user';
                        $roles = $DB->get_record('role', ['shortname' => $shortname]);
                        $conditionroleid = isset($roles->id) ? $roles->id : 0;
                    } else if ($itemuserrole == 'guest') {
                        $shortname = 'guest';
                        $roles = $DB->get_record('role', ['shortname' => $shortname]);
                        $conditionroleid = isset($roles->id) ? $roles->id : 0;
                    } else {
                        $shortname = $itemuserrole;
                        $roles = $DB->get_record('role', ['shortname' => $shortname]);
                        $conditionroleid = isset($roles->id) ? $roles->id : 0;
                    }
                    $othercondition = [
                        'label_tooltip_title' => $title,
                        'link_target' => $itemtargetblank,
                    ];
                    $newnavmenu[]  = [
                        'menu_type' => 'navmenu',
                        'depth' => $itemdepth,
                        'menu_order' => 0,
                        'menu_label' => ltrim($itemtext, '-'),
                        'menu_link' => $itemurl,
                        'condition_courses' => '',
                        'condition_lang' => $itemlanguages,
                        'condition_roleid' => $conditionroleid,
                        'other_condition' => $othercondition,
                    ];
                }
            }
            // ... convert into child depth content array.
            $newnavmenutree = [];
            $stack = [];
            foreach ($newnavmenu as $item) {
                // ... add children container
                $item['children'] = [];
                $depth = $item['depth'];
                if ($depth == 0) {
                    // ... top-level item
                    $newnavmenutree[] = $item;
                    $stack[0] = &$newnavmenutree[count($newnavmenutree) - 1];
                } else {
                    // ... attach to parent
                    $parent = &$stack[$depth - 1];
                    $parent['children'][] = $item;
                    $stack[$depth] = &$parent['children'][count($parent['children']) - 1];
                }
            }
            local_easycustmenu_save_menu_data($newnavmenutree, 0);
            unset_config('custommenuitems', 'local_easycustmenu');
        }
        // ... user menu
        $customusermenuitems = get_config('moodle', 'customusermenuitems');
        if ($customusermenuitems) {
            $newusermenu = [];
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
                    if ($itemuserrole == 'all') {
                        $conditionroleid = 0;
                    } else if ($itemuserrole == 'admin') {
                        $conditionroleid = -1;
                    } else if ($itemuserrole == 'auth') {
                        $shortname = 'user';
                        $roles = $DB->get_record('role', ['shortname' => $shortname]);
                        $conditionroleid = isset($roles->id) ? $roles->id : 0;
                    } else if ($itemuserrole == 'guest') {
                        $shortname = 'guest';
                        $roles = $DB->get_record('role', ['shortname' => $shortname]);
                        $conditionroleid = isset($roles->id) ? $roles->id : 0;
                    } else {
                        $shortname = $itemuserrole;
                        $roles = $DB->get_record('role', ['shortname' => $shortname]);
                        $conditionroleid = isset($roles->id) ? $roles->id : 0;
                    }

                    $newusermenu[] = [
                        'menu_type' => 'usermenu',
                        'context_level' => CONTEXT_SYSTEM,
                        'parent' => 0,
                        'depth' => 0,
                        'menu_order' => 0,
                        'menu_label' => $itemtext,
                        'menu_link' => $itemurl,
                        'condition_courses' => '',
                        'condition_lang' => '',
                        'condition_roleid' => $conditionroleid,
                        'other_condition' => [],
                    ];
                }
            }
            $newusermenutree = [];
            $stack = [];
            foreach ($newusermenu as $item) {
                // ... add children container
                $item['children'] = [];
                $depth = $item['depth'];
                if ($depth == 0) {
                    // ... top-level item
                    $newusermenutree[] = $item;
                    $stack[0] = &$newusermenutree[count($newusermenutree) - 1];
                } else {
                    // ... attach to parent
                    $parent = &$stack[$depth - 1];
                    $parent['children'][] = $item;
                    $stack[$depth] = &$parent['children'][count($parent['children']) - 1];
                }
            }
            local_easycustmenu_save_menu_data($newusermenutree, 0);
            set_config('customusermenuitems', '');
        }
    } catch (\Throwable $th) {
        // ... Skipped
        return;
    }
}

/**
 * Recursively saves menu data into the local_easycustmenu table.
 *
 * Inserts each menu item and its children into the database, maintaining parent-child relationships
 * and menu order. Used during upgrade or import to persist menu structure.
 *
 * @param array $newnavmenutree Array of menu items (with children).
 * @param int $parentid The parent menu item's ID (default: 0 for top-level).
 * @return void
 */
function local_easycustmenu_save_menu_data($newnavmenutree, $parentid = 0) {
    try {
        global $DB;
        foreach ($newnavmenutree as $key => $menu) {
            $menuorder = $DB->get_field_sql(
                "SELECT MAX(menu_order) FROM {local_easycustmenu} WHERE menu_type = :menu_type",
                ['menu_type' => $menu['menu_type']]
            );
            if ($menuorder === false || $menuorder === null) {
                $menuorder = 0;
            } else {
                $menuorder++;
            }
            // Process the data.
            $data = new stdClass();
            $data->menu_type = $menu['menu_type'];
            $data->context_level = CONTEXT_SYSTEM;
            $data->parent = $parentid;
            $data->depth = $menu['depth'];
            $data->menu_order = $menuorder;
            $data->menu_label = $menu['menu_label'];
            $data->menu_link = $menu['menu_link'];
            $data->condition_courses = '';
            $data->condition_lang = $menu['condition_lang'];
            $data->condition_roleid = $menu['condition_roleid'];
            $data->other_condition = json_encode($menu['other_condition']);
            $data->timemodified = time();
            $data->timecreated = time();
            $menuid = $DB->insert_record('local_easycustmenu', $data);
            if ($menu['children'] && count($menu['children']) > 0) {
                $childmenutree = $menu['children'];
                local_easycustmenu_save_menu_data($childmenutree, $menuid);
            }
        }
    } catch (\Throwable $th) {
        return;
    }
}
