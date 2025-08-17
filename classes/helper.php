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

namespace local_easycustmenu;

use local_easycustmenu\handler\easycustmenu_handler;
use moodle_url;

/**
 * class to handle local_easycustmenu helper action
 *
 * @package    local_easycustmenu
 * @copyright  2024 santoshtmp <https://santoshmagar.com.np/>
 * @author     santoshtmp
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */
class helper {

    /**
     * check check_ecm_menu
     */
    public function check_ecm_menu() {
        global $PAGE;
        // ... hide primarynavigation if the data is present in hide_primarynavigation
        $theme = $PAGE->theme;
        $activate = (get_config('local_easycustmenu', 'activate')) ?: "";
        if ($activate) {
            $theme->removedprimarynavitems = explode(',', get_config('local_easycustmenu', 'hide_primarynavigation'));
            $this->define_ecm_config_menuitems();
        }
    }

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
    public static function get_ecm_context_level() {
        return [
            10 => get_string('show_through_site', 'local_easycustmenu'),
            50 => get_string('show_in_course', 'local_easycustmenu'),
        ];
    }

    /**
     * Get role base on menu context
     */
    public static function get_ecm_context_roles($contextlevel) {
        global $DB;
        if (!empty($contextlevel)) {
            if ($contextlevel == CONTEXT_SYSTEM) {

                $sql = "SELECT r.*
                    FROM {role} r
                    LEFT JOIN {role_context_levels} rcl ON r.id = rcl.roleid
                    WHERE (rcl.contextlevel = :contextlevel OR rcl.roleid IS NULL)
                    AND (r.archetype IS NULL OR r.archetype <> :frontpage_archetype)
                    ORDER BY r.sortorder ASC";

                $params = [
                    'contextlevel' => $contextlevel,
                    'frontpage_archetype' => 'frontpage',
                ];

                return $DB->get_records_sql($sql, $params);
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
        $contextlevel  = CONTEXT_SYSTEM;
        $courseid = 0;
        $roleids = [];

        try {

            if (!empty($COURSE->id) && $COURSE->id > 1) {
                if ($PAGE->context->contextlevel === CONTEXT_COURSE || $PAGE->context->contextlevel === CONTEXT_MODULE) {
                    $context  = \context_course::instance($COURSE->id);
                    $contextlevel = CONTEXT_COURSE;
                    $courseid = $COURSE->id;
                }
            }
            // Get user roles in this context.
            if (is_siteadmin($USER->id)) {
                $roleids[] = -1;
            }
            if (isloggedin() && !isguestuser()) {
                $authuserroles = get_archetype_roles('user');
                $authuserrole = reset($authuserroles);
                $roleids[] = $authuserrole->id;
            }
            $assignedroles = get_user_roles($context, $USER->id, false);
            if (!empty($assignedroles)) {
                foreach ($assignedroles as $role) {
                    $roleids[] = $role->roleid;
                }
            }
        } catch (\Throwable $th) {
            // Skipped.
            return;
        }
        // Return data.
        return [
            'context'  => $context,
            'contextlevel' => $contextlevel,
            'courseid' => $courseid,
            'roleids' => $roleids,
            'lang' => current_language(),
        ];
    }

    /**
     * Get menu condition role name
     */
    public static function get_menu_role_name($conditionroleid) {

        global $DB;
        $rolename = '';
        if ($conditionroleid == 0) {
            $rolename = get_string('everyone', 'local_easycustmenu');
        } else if ($conditionroleid == '-1') {
            $rolename = get_string('admin');
        } else {
            $role = $DB->get_record('role', ['id' => $conditionroleid]);
            if ($role) {
                $rolename = role_get_name($role);
            }
        }
        return $rolename;
    }

    /**
     * check and define menu items according to easycustmenu
     */
    public static function define_ecm_config_menuitems() {
        global $CFG;

        $currentmenucondition = self::get_current_menu_condition();
        $contextlevel = $currentmenucondition['contextlevel'];
        $courseid = $currentmenucondition['courseid'];
        $roleids = $currentmenucondition['roleids'];
        $lang = $currentmenucondition['lang'];
        // For custom nav menu.
        $custommenuitems = '';
        $navmenu = easycustmenu_handler::get_ecm_menu_items('navmenu', $contextlevel, $courseid, $roleids, $lang);
        if ($navmenu) {
            foreach ($navmenu as $key => $menu) {
                $othercondition = ($menu->other_condition) ? json_decode($menu->other_condition, true) : [];
                $itemtext = $menu->menu_label;
                $itemurl = $menu->menu_link;
                $title = isset($othercondition['label_tooltip_title']) ? $othercondition['label_tooltip_title'] : '';
                $linktarget = isset($othercondition['link_target']) ? $othercondition['link_target'] : 0;
                $itemlanguages = $menu->condition_lang;
                $depth = $menu->depth;
                $itemdepth = '';
                for ($i = 0; $i < $depth; $i++) {
                    $itemdepth .= '-';
                }
                if ($linktarget) {
                    $itemurl = $itemurl . '" target="_blank"';
                }
                $custommenuitems .= $itemdepth . $itemtext . "|" . $itemurl .  "|" . $title . "|" . $itemlanguages . "\n";
            }
            $CFG->custommenuitems = $custommenuitems;
        }

        // For custom user menu.
        $customusermenuitemsoutput = "";
        $usermenu = easycustmenu_handler::get_ecm_menu_items('usermenu', $contextlevel, $courseid, $roleids, $lang);
        if ($usermenu) {
            foreach ($usermenu as $key => $menu) {
                $itemtext = $menu->menu_label;
                $itemurl = $menu->menu_link;
                $customusermenuitemsoutput .= $itemtext . "|" . $itemurl . "\n";
            }
            $CFG->customusermenuitems = $customusermenuitemsoutput;
        }
    }

    /**
     * Get ecm header tab part
     */
    public static function get_ecm_header_templatecontext() {
        $pagepath = '/local/easycustmenu/edit.php';
        $type = optional_param('type', '', PARAM_ALPHANUMEXT); // ... navmenu, usermenu
        $section = optional_param('section', '', PARAM_ALPHANUMEXT);
        $templatecontext = [];
        $templatecontext['single_menu'] = [
            [
                'menu_active_class' => ($section == 'local_easycustmenu') ? 'active' : '',
                'menu_moodle_url' => (new moodle_url('/admin/settings.php', ['section' => 'local_easycustmenu']))->out(),
                'menu_label' => get_string('general_setting', 'local_easycustmenu'),
            ],
            [
                'menu_active_class' => ($type == 'navmenu') ? "active" : "",
                'menu_moodle_url' => (new moodle_url($pagepath, ['type' => 'navmenu']))->out(),
                'menu_label' => get_string('header_nav_menu_setting', 'local_easycustmenu'),
            ],
            [
                'menu_active_class' => ($type == 'usermenu') ? "active" : "",
                'menu_moodle_url' => (new moodle_url($pagepath, ['type' => 'usermenu']))->out(),
                'menu_label' => get_string('user_menu_setting', 'local_easycustmenu'),
            ],
        ];
        return $templatecontext;
    }

    /**
     * before_footer_content
     */
    public static function before_footer_content() {
        $activate = (get_config('local_easycustmenu', 'activate')) ?: "";
        if (!$activate) {
            return;
        }

        global $PAGE;
        $content = '';
        $scriptcontent = $stylecontent = '';
        $allowpagetype = [
            'admin-setting-themesettingsadvanced',
            'admin-setting-themesettings',
            'admin-setting-local_easycustmenu',
            'easycustmenu_navmenu_setting',
            'easycustmenu_usermenu_setting',
        ];

        if ($PAGE->pagelayout === 'admin' && in_array($PAGE->pagetype, $allowpagetype) && is_siteadmin()) {
            if ($PAGE->pagetype === 'admin-setting-local_easycustmenu') {
                $pluginheadercontent = self::get_ecm_header_templatecontext();
                $PAGE->requires->js_call_amd('local_easycustmenu/ecm', 'admin_plugin_setting_init', [$pluginheadercontent]);
            } else {
                $showecmcore = get_config('local_easycustmenu', 'show_ecm_core');
                if ($showecmcore) {
                    $stringarray = [
                        'show_menu_label' => get_string('show_menu_label', 'local_easycustmenu'),
                        'hide_menu_label' => get_string('hide_menu_label', 'local_easycustmenu'),
                        'manage_menu_label' => get_string('manage_menu_label', 'local_easycustmenu'),
                        'show_menu_label_2' => get_string('show_menu_label_2', 'local_easycustmenu'),
                        'hide_menu_label_2' => get_string('hide_menu_label_2', 'local_easycustmenu'),
                        'manage_menu_label_2' => get_string('manage_menu_label_2', 'local_easycustmenu'),
                    ];
                    $PAGE->requires->js_call_amd('local_easycustmenu/ecm', 'admin_core_setting_init', [$stringarray]);
                }
            }
        }
        // Check style and script.
        if ($stylecontent) {
            $content .= '<style>' . $stylecontent . '</style>';
        }
        if ($scriptcontent) {
            $content .= '<script>' . $scriptcontent . '</script>';
        }

        return $content;
    }
}
