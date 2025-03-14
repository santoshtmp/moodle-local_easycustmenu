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

namespace local_easycustmenu\hooks;

use core\hook\navigation\primary_extend;
use core\hook\output\before_http_headers;
use core\hook\output\before_standard_head_html_generation;
use local_easycustmenu\helper;

/**
 * Hook callbacks for local_easycustmenu
 *
 * @package    local_easycustmenu
 * @copyright  santoshtmp7 https://github.com/santoshtmp/moodle-local_easycustmenu
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class hook_callbacks {

    /**
     * Callback allowing to add primary node
     *
     * @param \core\hook\navigation\primary_extend $hook
     */
    public static function primary_navigation_extend(primary_extend $hook): void {
        // Test.
    }


    /**
     * Callback allowing to before_http_headers
     *
     * @param \core\hook\output\before_http_headers $hook
     */
    public static function before_http_headers(before_http_headers $hook): void {
        global $CFG;
        if (during_initial_install() || isset($CFG->upgraderunning)) {
            // Do nothing during installation or upgrade.
            return;
        }
        $easycustmenu = new helper();
        $easycustmenu->check_custum_header_menu();
    }

    /**
     * Callback allowing to add to <head> of the page
     *
     * @param \core\hook\output\before_standard_head_html_generation $hook
     */
    public static function before_standard_head_html_generation(before_standard_head_html_generation $hook): void {
        global $CFG;
        if (during_initial_install() || isset($CFG->upgraderunning)) {
            // Do nothing during installation or upgrade.
            return;
        }
    }

    /**
     * Callback allowing to add contetnt inside the region-main, in the very end
     *
     * @param \core\hook\output\before_footer_html_generation $hook
     */
    public static function before_footer_html_generation(\core\hook\output\before_footer_html_generation $hook): void {
        global $CFG;
        if (during_initial_install() || isset($CFG->upgraderunning)) {
            // Do nothing during installation or upgrade.
            return;
        }
        $output = helper::before_footer_content();
        $hook->add_html($output);
    }
}
