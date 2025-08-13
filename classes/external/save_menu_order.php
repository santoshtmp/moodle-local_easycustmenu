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

namespace local_easycustmenu\external;

use core_external\external_api;
use core_external\external_function_parameters;
use core_external\external_multiple_structure;
use core_external\external_single_structure;
use core_external\external_value;
use dml_exception;

/**
 *
 * @package    local_easycustmenu
 * @copyright  2024 https://santoshmagar.com.np/
 * @author     santoshtmp7 https://github.com/santoshtmp/moodle-local_easycustmenu
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */
class save_menu_order extends external_api {

    /**
     * Get parameters.
     *
     * @return external_function_parameters
     */
    public static function execute_parameters() {
        return new external_function_parameters([
            'items' => new external_multiple_structure(
                new external_single_structure([
                    'id' => new external_value(PARAM_INT, 'Menu item ID'),
                    'menu_order' => new external_value(PARAM_INT, 'Display order index'),
                    'depth' => new external_value(PARAM_INT, 'Menu depth level'),
                    'parent' => new external_value(PARAM_INT, 'Parent menu item ID'),
                ])
            )
        ]);
    }

    /**
     * Execute the function to save menu order.
     *
     * @param array $items
     * @return array
     */
    public static function execute($items) {
        global $DB;

        $params = self::validate_parameters(self::execute_parameters(), [
            'items' => $items
        ]);

        $context = \context_system::instance();
        self::validate_context($context);

        try {
            $transaction = $DB->start_delegated_transaction();

            foreach ($params['items'] as $item) {
                if (!$item['id']) {
                    continue;
                }
                $DB->update_record('local_easycustmenu', (object)[
                    'id' => $item['id'],
                    'menu_order' => $item['menu_order'],
                    'depth' => $item['depth'],
                    'parent' => $item['parent'],
                ]);
            }

            $transaction->allow_commit();

            return [
                'status' => true,
                'message' => 'Menu order saved successfully'
            ];
        } catch (\Throwable $th) {
            return [
                'status' => false,
                'message' => 'Database error: ' . $th->getMessage()
            ];
        }
    }

    /**
     * Returns description of method result value.
     *
     * @return external_single_structure
     */
    public static function execute_returns() {
        return new external_single_structure([
            'status' => new external_value(PARAM_BOOL, 'Save status'),
            'message' => new external_value(PARAM_TEXT, 'Status message'),
        ]);
    }
}
