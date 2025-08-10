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
 * @copyright  2025 https://santoshmagar.com.np/
 * @author     santoshtmp7 https://github.com/santoshtmp/moodle-local_easycustmenu
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */

import $ from 'jquery';
import SortableList from 'core/sortable_list';
import Ajax from 'core/ajax';


export const init = (tableid) => {
    //
    let elementSelector = '#' + tableid + ' tbody[data-action="reorder"]';

    // Initialise SortableList for drag-and-drop.
    new SortableList(
        elementSelector,
        {
            targetListSelector: null,
            moveHandlerSelector: '[data-drag-type=move]',
            isHorizontal: false,
            autoScroll: true
        }
    );
    // Handle drag-and-drop depth changes.
    $(elementSelector).on(SortableList.EVENTS.DROP, function (evt, info) {
        let element = info.element;
        let end_x = info.endX;
        let start_x = info.startX;
        let positionChanged = info.positionChanged;
        let itemDepth = parseInt(element.attr('data-depth')) || 0;
        let new_itemDepth = itemDepth;
        let prevElement = element.prev();
        let prevElementDepth = parseInt(prevElement.attr('data-depth')) || 0;

        // Log sourceList contents for debugging
        // let sourceList = info.sourceList;
        // $('.sourceList').html($(info.sourceList).html());
        // $('.targetList').html($(info.targetList).html());
        // window.console.log('sourceList contents:', $(sourceList).html());
        // window.console.log('sourceList tr elements:', $(sourceList).find('tr').length);
        // window.console.log('sourceList : ' + info.sourceList.index(element));
        // window.console.log('targetList : ' + info.targetList);

        // Prevent from moving first depth 0 item to more deep

        // Only adjust depth for significant horizontal movement
        if (positionChanged) {
            let elementIndex = $(elementSelector + ' tr').index(element);
            let targetNextElementDepth = parseInt(info.targetNextElement.attr('data-depth')) || 0;
            new_itemDepth = (elementIndex == 0) ? 0 : Math.max(prevElementDepth, targetNextElementDepth);
        } else {
            if (prevElement.length) {
                if (end_x > start_x + 30) {
                    new_itemDepth = Math.min(itemDepth + 1, prevElementDepth + 1);
                } else if (end_x < start_x - 30) {
                    new_itemDepth = Math.max(0, itemDepth - 1);
                }
            }
        }

        // Update element depth and indentation only if depth changed
        if (new_itemDepth !== itemDepth) {
            element.attr('data-depth', new_itemDepth);
            let indentation = element.find('.indentation');
            indentation.css({
                'width': (30 * new_itemDepth) + 'px'
            });
        }

        // Show save button after reordering
        $('#save_menu_reorder').show();

        // ===============

    });

    // save the order of menu items
    $('#save_menu_reorder').on('click', function () {
        // Disable to prevent multiple clicks
        let saveBtn = $(this);
        saveBtn.prop('disabled', true);

        let reorder_items = {};
        let depthStack = {}; // Stores the last seen ID for each depth level

        $(elementSelector + ' tr').each(function (index) {
            let id = parseInt($(this).attr('data-id'));
            let depth = parseInt($(this).attr('data-depth'));

            // Store the last seen item at this depth
            depthStack[depth] = id;

            // Determine parent
            let parent = 0;
            if (depth > 0) {
                parent = depthStack[depth - 1] || 0;
            }

            $(this).attr('data-parent', parent);
            reorder_items[id] = {
                menu_order: index,
                id: id,
                depth: depth,
                parent: parent,
            };
        });

        // AJAX request to save the order
        let request = {
            methodname: 'local_easycustmenu_save_menu_order',
            args: {
                items: reorder_items,
            }
        };

        let ajax = Ajax.call([request])[0];
        ajax.done(function (response) {
            if (response.status) {
                window.console.log('Menu order saved successfully.');
                $('#save_menu_reorder').hide();
            } else {
                window.console.log('Error saving menu order:', response.message);
            }
        });
        ajax.fail(function () {
            window.console.log('request failed.');
        });
        ajax.always(function () {
            saveBtn.prop('disabled', false);
        });


    });

};
