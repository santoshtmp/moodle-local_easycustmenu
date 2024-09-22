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
 * AMD module
 *
 * @module     local_easycustmenu/easy-menu-drag
 * @copyright  2024 https://santoshmagar.com.np/
 * @author     santoshtmp7
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */
define(['jquery', 'core/sortable_list'], function ($, SortableList) {
    /**
     *
     * @param {*} menu_type
     */
    function menu_drag(menu_type) {
        new SortableList('.menu-draggable-list');
        if (menu_type == 'navmenu') {
            $('.menu-draggable-list > *').on(SortableList.EVENTS.DROP, function (evt, info) {
                let element = info.element;
                let input_itemdepth = element.find('input[name="itemdepth[]"]');
                let itemdepth = parseInt(element.attr('itemdepth'));
                let end_x = info.endX;
                let start_x = info.startX;
                if (end_x > start_x && (end_x > (start_x + 25))) {
                    let new_itemdepth = itemdepth + 1;
                    element.attr('itemdepth', new_itemdepth);
                    input_itemdepth.attr('value', new_itemdepth);
                }
                if (end_x < start_x && (end_x < (start_x - 25))) {
                    let new_itemdepth = itemdepth;
                    if (itemdepth > 1) {
                        new_itemdepth = itemdepth - 1;
                    }
                    element.attr('itemdepth', new_itemdepth);
                    input_itemdepth.attr('value', new_itemdepth);
                }

            });
        }
    }
    //
    return {
        easy_menu_drag: function (menu_type) {
            return menu_drag(menu_type);
        }
    };
});