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
 * Helper class for local_easycustmenu plugin.
 *
 * Provides utility methods for managing custom navigation and user menus,
 * including menu item configuration, context-based conditions, and role-based access.
 *
 * @package    local_easycustmenu
 * @copyright  2024 santoshtmp <https://santoshmagar.com.np/>
 * @author     santoshtmp
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class helper {
    /**
     * Check and configure custom menu items based on plugin settings.
     *
     * Hides primary navigation items if configured and sets up custom menu items
     * for both navigation and user menus based on current context, roles, and language.
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
     * Get available menu types.
     *
     * @return array Associative array of menu type identifiers and their display names.
     *               Keys: 'navmenu', 'usermenu'
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
     * @return array Associative array of context level identifiers and their display names.
     *               Keys: 10 (site-wide), 50 (course-specific)
     */
    public static function get_ecm_context_level() {
        return [
            10 => get_string('show_through_site', 'local_easycustmenu'),
            50 => get_string('show_in_course', 'local_easycustmenu'),
        ];
    }

    /**
     * Get roles based on menu context level.
     *
     * @param int $contextlevel The context level (CONTEXT_SYSTEM or CONTEXT_COURSE).
     * @return array List of role records applicable to the specified context level.
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
     * Get the current menu condition based on user context.
     *
     * Determines the current context, user roles, and language to establish
     * menu visibility conditions.
     *
     * Rules for context object and its level:
     * - 50 if inside a real course (course id > 1)
     * - 10 for system context or front page (course id = 1)
     *
     * @return array|null {
     *     @type context $context       Moodle context object for the current page
     *     @type int     $contextlevel  Context level (CONTEXT_COURSE=50 or CONTEXT_SYSTEM=10)
     *     @type int     $courseid      Course ID (0 for front page or system)
     *     @type array   $roleids       Array of unique role IDs applicable to the user, with -1 for site admin
     *     @type string  $lang          Current language code
     * } Returns null if an error occurs during context/role resolution.
     */
    public static function get_current_menu_condition() {
        global $PAGE, $COURSE, $USER;

        // Initialize default values.
        $context = \context_system::instance();
        $contextlevel  = CONTEXT_SYSTEM;
        $courseid = 0;
        $roleids = [];

        try {
            // Determine context level based on current page location.
            // Set to CONTEXT_COURSE if inside a real course (course id > 1).
            if (!empty($COURSE->id) && $COURSE->id > 1) {
                if ($PAGE->context->contextlevel === CONTEXT_COURSE || $PAGE->context->contextlevel === CONTEXT_MODULE) {
                    $context  = \context_course::instance($COURSE->id);
                    $contextlevel = CONTEXT_COURSE;
                    $courseid = $COURSE->id;
                }
            }

            // Add 'everyone' role identifier (applies to all users).
            $roleids[] = 0;

            // Add site admin role identifier if user is site administrator.
            if (is_siteadmin($USER->id)) {
                $roleids[] = -1;
            }

            // Add authenticated user role (for logged-in, non-guest users).
            if (isloggedin() && !isguestuser()) {
                $authuserroles = get_archetype_roles('user');
                $authuserrole = reset($authuserroles);
                if ($authuserrole) {
                    $roleids[] = $authuserrole->id;
                }
            }

            // Add guest role for not-logged-in or guest users.
            if (!isloggedin() || isguestuser()) {
                $guestroles = get_archetype_roles('guest');
                $guestrole = reset($guestroles);
                if ($guestrole) {
                    $roleids[] = $guestrole->id;
                }
            }

            // Add all roles assigned to the user in the current context.
            $assignedroles = get_user_roles($context, $USER->id, false);
            if (!empty($assignedroles)) {
                foreach ($assignedroles as $role) {
                    $roleids[] = $role->roleid;
                }
            }
        } catch (\Throwable $th) {
            // Return early if any error occurs during context/role resolution.
            return;
        }

        // Return the collected menu condition data.
        return [
            'context'  => $context,
            'contextlevel' => $contextlevel,
            'courseid' => $courseid,
            'roleids' => array_unique($roleids),
            'lang' => current_language(),
        ];
    }

    /**
     * Get the display name for a given role ID.
     *
     * @param int|string $conditionroleid The role ID to get the name for.
     *                                    Special values: 0 = everyone, -1 = admin
     * @return string The display name of the role or special label.
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
     * Configure menu items based on Easy Custom Menu settings.
     *
     * Retrieves and processes menu items for both navigation and user menus based on
     * current context conditions (context level, course ID, roles, language).
     * Sets the global $CFG->custommenuitems and $CFG->customusermenuitems properties.
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
     * Get header tab context for Easy Custom Menu admin pages.
     *
     * Builds the template context for the tabbed navigation in the plugin's admin interface,
     * including links to general settings, navigation menu settings, and user menu settings.
     *
     * @return array Template context containing menu tab configurations with active states and URLs.
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
     * Generate content to be added before the footer.
     *
     * Injects JavaScript and CSS for Easy Custom Menu admin pages when the plugin is activated.
     * Provides enhanced UI for menu management on admin settings pages.
     *
     * @return string HTML content with inline styles and scripts, or empty string if not applicable.
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
                $PAGE->requires->js_call_amd('local_easycustmenu/ecm', 'adminPluginSettingInit', [$pluginheadercontent]);
            } else {
                $showecmcore = get_config('local_easycustmenu', 'show_ecm_core');
                if ($showecmcore) {
                    $jsdata = [
                        'managenavmenulabel' => get_string('managenavmenulabel', 'local_easycustmenu'),
                        'manageNavMenuLink' => (new moodle_url("/local/easycustmenu/edit.php", ["type" => "navmenu"]))->out(false),
                        'manageusermenulabel' => get_string('manageusermenulabel', 'local_easycustmenu'),
                        'manageUserMenuLink' => (new moodle_url("/local/easycustmenu/edit.php", ["type" => "usermenu"]))->out(false),
                    ];
                    $PAGE->requires->js_call_amd('local_easycustmenu/ecm', 'adminCoreSettingInit', [$jsdata]);
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
