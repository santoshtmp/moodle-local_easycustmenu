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
 * Helper class for local_easycustmenu plugin.
 *
 * Provides utility methods for menu configuration, context handling,
 * and menu item management.
 *
 * @package    local_easycustmenu
 * @copyright  2024 https://santoshmagar.com.np/
 * @author     santoshtmp7 https://github.com/santoshtmp/moodle-local_easycustmenu
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_easycustmenu;

use local_easycustmenu\handler\easycustmenu_handler;
use moodle_url;

/**
 * Helper class for Easy Custom Menu plugin operations.
 *
 * This class provides methods to handle menu conditions, context levels,
 * role-based access, and menu item configuration for both navigation
 * and user menus.
 *
 * @package    local_easycustmenu
 * @copyright  2024 santoshtmp <https://santoshmagar.com.np/>
 * @author     santoshtmp
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class helper {
    /**
     * Check and configure ECM menu settings.
     *
     * Hides primary navigation items based on plugin configuration and
     * defines custom menu items if the plugin is activated.
     *
     * @return void
     */
    public function check_ecm_menu() {
        global $PAGE;

        $theme = $PAGE->theme;
        $activate = (get_config('local_easycustmenu', 'activate')) ?: "";
        if ($activate) {
            // Hide primary navigation items based on configuration.
            $theme->removedprimarynavitems = explode(',', get_config('local_easycustmenu', 'hide_primarynavigation'));
            $this->define_ecm_config_menuitems();
        }
    }

    /**
     * Get available menu types.
     *
     * @return array Associative array of menu type identifiers and their display names.
     */
    public static function get_menu_type() {
        return [
            'navmenu' => get_string('navmenu', 'local_easycustmenu'),
            'usermenu' => get_string('usermenu', 'local_easycustmenu'),
        ];
    }

    /**
     * Get available menu context levels.
     *
     * @return array Associative array of context level identifiers and their display names.
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
     * Retrieves roles that are applicable at the specified context level.
     * For system context (CONTEXT_SYSTEM), excludes frontpage archetype roles.
     *
     * @param int $contextlevel The context level (e.g., CONTEXT_SYSTEM, CONTEXT_COURSE).
     * @return array List of role records from the database.
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
     * Determines the current context, course, roles, and language for menu display.
     *
     * Context level rules:
     * - 50 (CONTEXT_COURSE): Inside a real course (course id > 1)
     * - 10 (CONTEXT_SYSTEM): System context or front page (course id = 1)
     *
     * Role IDs include:
     * - 0: Everyone (applies to all users)
     * - -1: Site administrator (if user is admin)
     * - Authenticated user role ID (if logged in and not guest)
     * - Guest role ID (if not logged in or is guest)
     * - All roles assigned to the user in the current context
     *
     * @return array {
     *     context       => \context,    // Moodle context object
     *     contextlevel  => int,         // 50 (course) or 10 (system)
     *     courseid      => int,         // 0 for front page or system
     *     roleids       => array,       // Role IDs, with 0 for everyone, -1 for admin
     *     lang          => string       // Current language code
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
            $assignedroles = get_user_roles($context, $USER->id, true);
            if (!empty($assignedroles)) {
                foreach ($assignedroles as $role) {
                    $roleids[] = $role->roleid;
                }
            }
        } catch (\Throwable $th) {
            // Exception occurred, return early.
            return [];
        }

        return [
            'context'  => $context,
            'contextlevel' => $contextlevel,
            'courseid' => $courseid,
            'roleids' => array_unique($roleids),
            'lang' => current_language(),
        ];
    }

    /**
     * Get the display name for a given role ID or comma-separated role IDs.
     *
     * Special role ID values:
     * - 0 or empty: Returns "everyone"
     * - -1: Returns "admin"
     * - Other values: Fetches role name from database
     *
     * @param int|string $conditionroleid The role ID(s) to get the name for.
     * @return string The display name(s) of the role(s), comma-separated if multiple.
     */
    public static function get_menu_role_name($conditionroleid) {
        global $DB;

        if (empty($conditionroleid) || $conditionroleid == '0') {
            return get_string('everyone', 'local_easycustmenu');
        }
        $roleids = explode(',', $conditionroleid);
        $names = [];
        foreach ($roleids as $rid) {
            $rid = trim($rid);
            if ($rid == '0') {
                $names[] = get_string('everyone', 'local_easycustmenu');
            } else if ($rid == '-1') {
                $names[] = get_string('admin');
            } else {
                $role = $DB->get_record('role', ['id' => $rid]);
                if ($role) {
                    $names[] = role_get_name($role);
                }
            }
        }
        return implode(', ', $names);
    }

    /**
     * Define and configure menu items according to Easy Custom Menu settings.
     *
     * Retrieves menu items based on the current context and configures them
     * for both navigation and user menus. Sets the global $CFG properties
     * for custom menu items.
     *
     * @return void
     */
    public static function define_ecm_config_menuitems() {
        global $CFG;

        $currentmenucondition = self::get_current_menu_condition();
        $contextlevel = $currentmenucondition['contextlevel'];
        $courseid = $currentmenucondition['courseid'];
        $roleids = $currentmenucondition['roleids'];
        $lang = $currentmenucondition['lang'];

        // Configure custom navigation menu items.
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

        // Configure custom user menu items.
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
     * Get ECM header tab template context for admin settings pages.
     *
     * Returns template context data for rendering header tabs on Easy Custom Menu
     * administration pages, including general settings, navigation menu settings,
     * and user menu settings.
     *
     * @return array Template context array containing menu tab configurations with:
     *               - menu_active_class: CSS class for active state
     *               - menu_moodle_url: moodle_url object for the menu link
     *               - menu_label: Display label for the menu tab
     */
    public static function get_ecm_header_templatecontext() {
        $pagepath = '/local/easycustmenu/edit.php';
        $type = optional_param('type', '', PARAM_ALPHANUMEXT); // Use: navmenu, usermenu.
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
     * Generate content and initialize JavaScript for the before footer hook.
     *
     * This method is called during the before_footer output hook. It injects
     * JavaScript for admin settings pages related to Easy Custom Menu configuration.
     * Only executes for site administrators on specific admin pages.
     *
     * @return string HTML content containing inline styles and scripts, or empty string if not applicable.
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
                // Initialize ECM plugin settings page.
                $pluginheadercontent = self::get_ecm_header_templatecontext();
                $PAGE->requires->js_call_amd('local_easycustmenu/ecm', 'adminPluginSettingInit', [$pluginheadercontent]);
            } else {
                // Initialize core settings with ECM links if enabled.
                $showecmcore = get_config('local_easycustmenu', 'show_ecm_core');
                if ($showecmcore) {
                    $navmenuurl = new moodle_url("/local/easycustmenu/edit.php", ["type" => "navmenu"]);
                    $usermenuurl = new moodle_url("/local/easycustmenu/edit.php", ["type" => "usermenu"]);
                    $jsdata = [
                        'managenavmenulabel' => get_string('managenavmenulabel', 'local_easycustmenu'),
                        'manageNavMenuLink' => $navmenuurl->out(false),
                        'manageusermenulabel' => get_string('manageusermenulabel', 'local_easycustmenu'),
                        'manageUserMenuLink' => $usermenuurl->out(false),
                    ];
                    $PAGE->requires->js_call_amd('local_easycustmenu/ecm', 'adminCoreSettingInit', [$jsdata]);
                }
            }
        }

        // Output inline styles and scripts if present.
        if ($stylecontent) {
            $content .= '<style>' . $stylecontent . '</style>';
        }
        if ($scriptcontent) {
            $content .= '<script>' . $scriptcontent . '</script>';
        }

        return $content;
    }
}
