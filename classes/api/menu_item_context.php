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

namespace local_easycustmenu\api;

use core_external\external_api;
use core_external\external_function_parameters;
use core_external\external_multiple_structure;
use core_external\external_single_structure;
use core_external\external_value;
use local_easycustmenu\helper;

/**
 *
 * @package    local_easycustmenu
 * @copyright  2024 https://santoshmagar.com.np/
 * @author     santoshtmp7 https://github.com/santoshtmp/moodle-local_easycustmenu
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */
class menu_item_context extends external_api {
    /**
     * Get parameters.
     *
     * @return external_function_parameters
     */
    public static function menu_item_context_parameters() {
        return new external_function_parameters(
            [
                'menu_item_num' => new external_value(
                    PARAM_INT,
                    'menu_item_num.',
                ),
                'itemdepth' => new external_value(
                    PARAM_INT,
                    'itemdepth.',
                    VALUE_OPTIONAL
                ),
                'menu_type' => new external_value(
                    PARAM_TEXT,
                    'apply menu type "navmenu" or "usermenu".',
                    VALUE_OPTIONAL
                ),
            ]
        );
    }

    /**
     * Retrieves context information for a menu item.
     * @param int $menuitemnum
     * @param int $itemdepth 1 default
     * @param string $menutype 'navmenu' default
     * @return array
     */
    public static function menu_item_context($menuitemnum, $itemdepth, $menutype) {

        $params = self::validate_parameters(
            self::menu_item_context_parameters(),
            [
                'menu_item_num' => $menuitemnum,
                'itemdepth' => $itemdepth,
                'menu_type' => $menutype,
            ]
        );

        // Check Context and Capability Validation.
        $context = \context_system::instance();
        self::validate_context($context);
        require_capability('moodle/site:config', $context);

        $menutype = $params['menu_type'];
        $menuitemnum = $params['menu_item_num'];
        $itemdepth = $params['itemdepth'];
        $multilang = (count(helper::get_languages()) > 1) ? true : false;
        $pix = 24 * $itemdepth;

        $templatecontext = [
            'menu_item_num' => 'menu-' . $menuitemnum,
            'label' => '',
            'link' => '',
            'itemdepth' => $itemdepth,
            'itemdepth_left_move' => 'padding-left: ' . $pix . 'px;',
            'condition_user_roles' => helper::get_condition_user_roles(),
            'apply_condition' => true,
            'user_role_condition' => true,
            'new_tab_condition' => ($menutype == 'navmenu') ? true : false,
            'multi_lang' => ($menutype == 'navmenu') ? $multilang : false,

        ];

        $outputdata = [
            'status' => true,
            'template_name' => 'local_easycustmenu/menu_item_wrapper',
            'template_context' => $templatecontext,
        ];

        return $outputdata;
    }

    /**
     * Get returns.
     *
     * @return external_single_structure
     */
    public static function menu_item_context_returns() {
        return new external_single_structure(
            [
                'status' => new external_value(PARAM_BOOL, 'status'),
                'template_name' => new external_value(PARAM_TEXT, 'template_name'),
                'template_context' => new external_single_structure(
                    [
                        'menu_item_num' => new external_value(PARAM_TEXT, 'menu_item_num'),
                        'label' => new external_value(PARAM_TEXT, 'label'),
                        'link' => new external_value(PARAM_TEXT, 'link'),
                        'itemdepth' => new external_value(PARAM_INT, 'itemdepth'),
                        'itemdepth_left_move' => new external_value(PARAM_RAW, 'itemdepth_left_move'),
                        'condition_user_roles' => new external_multiple_structure(
                            new external_single_structure(
                                [
                                    'key' => new external_value(PARAM_TEXT, 'key'),
                                    'value' => new external_value(PARAM_TEXT, 'value'),
                                    'is_selected' => new external_value(PARAM_BOOL, 'is_selected'),
                                ]
                            )
                        ),
                        'apply_condition' => new external_value(PARAM_BOOL, 'apply_condition'),
                        'user_role_condition' => new external_value(PARAM_BOOL, 'user_role_condition'),
                        'new_tab_condition' => new external_value(PARAM_BOOL, 'new_tab_condition'),
                        'multi_lang' => new external_value(PARAM_BOOL, 'multi_lang'),

                    ],
                    'menu_item template context',
                    VALUE_OPTIONAL
                ),
                'exception' => new external_value(PARAM_RAW, 'exception message', VALUE_OPTIONAL),
            ]
        );
    }
}
