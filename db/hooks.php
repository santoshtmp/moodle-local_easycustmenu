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
 * Hook callbacks
 * https://moodledev.io/docs/4.5/apis/core/hooks
 *
 * @package    local_easycustmenu
 * @copyright  2024 https://santoshmagar.com.np/
 * @author     santoshtmp7 https://github.com/santoshtmp/moodle-local_easycustmenu
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */


defined('MOODLE_INTERNAL') || die();

$callbacks = [
    [
        'hook' => core\hook\navigation\primary_extend::class,
        'callback' => 'local_easycustmenu\hooks\hook_callbacks::primary_navigation_extend',
        'priority' => 0,
    ],
    [
        'hook' => core\hook\output\before_http_headers::class,
        'callback' => [local_easycustmenu\hooks\hook_callbacks::class, 'before_http_headers'],
        'priority' => 0,
    ],
    [
        'hook' => core\hook\output\before_standard_head_html_generation::class,
        'callback' => [local_easycustmenu\hooks\hook_callbacks::class, 'before_standard_head_html_generation'],
        'priority' => 0,
    ],
    [
        'hook' => core\hook\output\before_footer_html_generation::class,
        'callback' => 'local_easycustmenu\hooks\hook_callbacks::before_footer_html_generation',
        'priority' => 0,
    ],
];
