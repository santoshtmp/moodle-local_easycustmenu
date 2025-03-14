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

use local_easycustmenu\helper;

/**
 * From moodle 4.4 callback are managed through callback hook => local_easycustmenu\hooks\hook_callbacks
 * https://moodledev.io/docs/4.5/apis/core/hooks
 * https://docs.moodle.org/dev/Output_callbacks#before_http_headers
 */
function local_easycustmenu_before_http_headers() {
    $easycustmenu = new helper();
    $easycustmenu->check_custum_header_menu();
}

/**
 * Callback allowing to add contetnt inside the region-main, in the very end
 *
 * @return string
 */
function local_easycustmenu_before_footer() {
    return helper::before_footer_content();
}
