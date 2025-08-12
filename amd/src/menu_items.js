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
 * AMD ES6 module
 *
 * @copyright  2025 https://santoshmagar.com.np/
 * @author     santoshtmp7 https://github.com/santoshtmp/moodle-local_easycustmenu
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */

import $ from 'jquery';
import SortableList from 'core/sortable_list';
import Ajax from 'core/ajax';
import { getString } from 'core/str';

/**
 * variables define
 */
let elementSelector = '';
let moveHandlerSelector = '[data-drag-type=move]';


/**
 * To check menu items siglings tr depths
 */
async function check_invalid_depth() {
    let invalidRows = [];
    $(elementSelector + ' tr').removeClass('invalid-depth').css('background-color', '');
    $('#menu_depth_error').remove();

    // check on each tr
    $(elementSelector + ' tr').each(function () {
        let depth = parseInt($(this).attr('data-depth')) || 0;
        let parent_id = parseInt($(this).attr('data-parent'));
        let parent_depth = $(elementSelector + ' tr[data-id="' + parent_id + '"]').attr('data-depth') || 0;
        if (Math.abs(depth - parseInt(parent_depth)) > 1) {
            invalidRows.push($(this));
        }
    });

    // check invalid length
    if (invalidRows.length > 0) {
        invalidRows.forEach(function (row) {
            row.addClass('invalid-depth').css('background-color', '#ffcccc'); // light red
        });
        $('#save_menu_reorder').prop('disabled', true);
        $('<div id="menu_depth_error" style="color:red;margin-top:10px;">' +
            await getString('invalidmenudepth', 'local_easycustmenu') + '</div>')
            .insertAfter($('#save_menu_reorder'));
    } else {
        $('#save_menu_reorder').prop('disabled', false);
    }

    return invalidRows.length;
}

/**
 * To save the menu items
 * @param {*} reorder_items
 */
async function ajax_save_menu_items(reorder_items) {
    // AJAX request to save the order
    const request = {
        methodname: 'local_easycustmenu_save_menu_order',
        args: {
            items: reorder_items,
        }
    };
    return await Ajax.call([request])[0]
        .done(function (response) {
            if (response.status) {
                // window.console.log('Menu order saved successfully.');
                $('#save_menu_reorder').hide();
            } else {
                window.console.log('Error saving menu order:', response.message);
            }
        }).fail(function () {
            window.console.log('request failed.');
        }).always(function () {
            $('#save_menu_reorder').prop('disabled', false);
        }
        );
}

/**
 * get_reorder_items
 */
function get_reorder_items() {
    let reorder_items = {};
    let depthStack = {}; // Stores the last seen ID for each depth level
    $(elementSelector + ' tr').each(function (index) {
        let id = parseInt($(this).attr('data-id'));
        let depth = parseInt($(this).attr('data-depth')) || 0;

        // Store the last seen item at this depth
        depthStack[depth] = id;

        // Determine parent
        let parent = 0;
        if (depth > 0) {
            parent = depthStack[depth - 1] || 0;
        } else {
            depthStack = {};
        }

        $(this).attr('data-parent', parent);
        reorder_items[id] = {
            menu_order: index,
            id: id,
            depth: depth,
            parent: parent,
        };

    });
    return reorder_items;
}

/**
 * Menu Item Re-order
 *
 * @param {*} tableid
 */
export const menu_item_reorder = (tableid) => {
    //
    elementSelector = '#' + tableid + ' tbody[data-action="reorder"]';
    let child_arrow = document.querySelector('#depth-reusable-icon #child_arrow').innerHTML;
    let child_indentation = document.querySelector('#depth-reusable-icon #child_indentation').innerHTML;

    // Initialise SortableList for drag-and-drop.
    new SortableList(
        elementSelector,
        {
            targetListSelector: null,
            moveHandlerSelector: moveHandlerSelector,
            isHorizontal: false,
            autoScroll: true
        }
    );

    // Disable click on drag handle to prevent unintended clicks
    $(elementSelector).on('click', moveHandlerSelector, function (e) {
        e.preventDefault();
        e.stopPropagation();
        return false;
    });

    // Prevent dragging start for first depth 0
    $(elementSelector).on('mousedown', moveHandlerSelector, function (e) {
        let tr = $(this).closest('tr');

        // Find all depth 0 rows
        let depth0Rows = $(elementSelector + ' tr[data-depth="0"]');

        // Check if this is the first depth 0 row
        let isFirstDepth0 = tr.is(depth0Rows.first());

        if (isFirstDepth0) {
            let hasOtherDepth0 = depth0Rows.length > 1;
            let nextRowDepth = parseInt(tr.next().attr('data-depth')) || 0;

            // Condition: no other depth 0 row OR next sibling has depth > 0
            if (!hasOtherDepth0 || nextRowDepth > 0) {
                e.preventDefault();
                e.stopImmediatePropagation();
                return false;
            }
        }
    });

    // Handle drag-and-drop depth changes.
    $(elementSelector).on(SortableList.EVENTS.DROP, async function (evt, info) {
        let element = info.element;
        let end_x = info.endX;
        let start_x = info.startX;
        let positionChanged = info.positionChanged;
        let itemDepth = parseInt(element.attr('data-depth')) || 0;
        let new_itemDepth = itemDepth;
        let prevElement = element.prev();
        let prevElementDepth = parseInt(prevElement.attr('data-depth')) || 0;

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

        // Update element depth and child-icon only if depth changed
        if (new_itemDepth !== itemDepth) {
            element.attr('data-depth', new_itemDepth);
            let child_indentation_icon = '';
            if (new_itemDepth) {
                for (let index = 0; index < new_itemDepth - 1; index++) {
                    child_indentation_icon += child_indentation;
                }
                child_indentation_icon += child_arrow;
            }
            let indentation = element.find('.child-icon-wrapper');
            if (indentation) {
                indentation.html(child_indentation_icon);
            }

        }

        // Show save button after reordering
        $('#save_menu_reorder').show();

    });

    // save the order of menu items
    $('#save_menu_reorder').on('click', async function () {
        // Disable to prevent multiple clicks
        $(this).prop('disabled', true);
        //
        let reorder_items = get_reorder_items();
        Object.values(reorder_items).forEach(function (item) {
            let tr = $(elementSelector + ' tr[data-id="' + item.id + '"]');
            if (tr) {
                tr.attr('data-depth', item.depth);
                tr.attr('data-parent', item.parent);
                tr.attr('data-menu_order', item.menu_order);
            }
        });
        // check invalid depth
        let invalid_depth = await check_invalid_depth();
        if (invalid_depth) {
            $(this).prop('disabled', false);
            return;
        }
        // save the menu items
        await ajax_save_menu_items(reorder_items);

    });

};
