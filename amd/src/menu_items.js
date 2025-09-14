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
 * Variables define
 */
var elementSelector = '';
const moveHandlerSelector = '[data-drag-type=move]';


/**
 * To check menu items siglings tr depths
 */
async function checkInvalidDepth() {
    let invalidRows = [];
    $(elementSelector + ' tr').removeClass('invalid-depth').css('background-color', '');
    $('#menu_depth_error').remove();

    // Check on each tr
    $(elementSelector + ' tr').each(function () {
        let depth = parseInt($(this).attr('data-depth')) || 0;
        let parentId = parseInt($(this).attr('data-parent'));
        let parentDepth = $(elementSelector + ' tr[data-id="' + parentId + '"]').attr('data-depth') || 0;
        if (Math.abs(depth - parseInt(parentDepth)) > 1) {
            invalidRows.push($(this));
        }
    });

    // Check invalid length
    if (invalidRows.length > 0) {
        invalidRows.forEach(function (row) {
            row.addClass('invalid-depth').css('background-color', '#ffcccc');
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
 * @param {*} reorderItems
 */
async function ajaxSaveMenuItems(reorderItems) {
    // AJAX request to save the order
    const request = {
        methodname: 'local_easycustmenu_save_menu_order',
        args: {
            items: reorderItems,
        }
    };
    return await Ajax.call([request])[0]
        .done(function (response) {
            if (response.status) {
                window.location.reload();
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
 * Get reorder items
 */
function getReorderItems() {
    let reorderItems = {};
    let depthStack = {}; // Stores the last seen ID for each depth level
    let parent = 0;
    $(elementSelector + ' tr').each(function (index) {
        let id = parseInt($(this).attr('data-id'));
        let depth = parseInt($(this).attr('data-depth')) || 0;
        // Determine parent
        if (depth > 0) {
            parent = depthStack[depth - 1] || 0;
        } else {
            depthStack = {};
            parent = 0;
        }
        // Store the last seen item at this depth
        depthStack[depth] = id;
        //
        $(this).attr('data-parent', parent);
        reorderItems[id] = {
            menuorder: index,
            id: id,
            depth: depth,
            parent: parent,
        };

    });
    return reorderItems;
}

/**
 * Menu Item Re-order
 *
 * @param {*} tableid
 */
export const menuItemReorder = (tableid) => {
    elementSelector = '#' + tableid + ' tbody[data-action="reorder"]';
    const childArrow = document.querySelector('.page-easycustmenu #depth-reusable-icon #child_arrow').innerHTML;
    const childIndentation = document.querySelector('.page-easycustmenu #depth-reusable-icon #child_indentation').innerHTML;

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
        return true;
    });

    // Handle drag-and-drop depth changes.
    $(elementSelector).on(SortableList.EVENTS.DROP, async function (evt, info) {
        if (tableid == 'navmenu-table') {
            let element = info.element;
            let endX = info.endX;
            let startX = info.startX;
            let positionChanged = info.positionChanged;
            let itemDepth = parseInt(element.attr('data-depth')) || 0;
            let newItemDepth = itemDepth;
            let prevElement = element.prev();
            let prevElementDepth = parseInt(prevElement.attr('data-depth')) || 0;

            // Only adjust depth for significant horizontal movement
            if (positionChanged) {
                let elementIndex = $(elementSelector + ' tr').index(element);
                let targetNextElementDepth = parseInt(info.targetNextElement.attr('data-depth')) || 0;
                newItemDepth = (elementIndex == 0) ? 0 : Math.max(prevElementDepth, targetNextElementDepth);
            } else {
                if (prevElement.length) {
                    if (endX > startX + 30) {
                        newItemDepth = Math.min(itemDepth + 1, prevElementDepth + 1);
                    } else if (endX < startX - 30) {
                        newItemDepth = Math.max(0, itemDepth - 1);
                    }
                }
            }

            // Update element depth and child-icon only if depth changed
            if (newItemDepth !== itemDepth) {
                element.attr('data-depth', newItemDepth);
                let childIndentationIcon = '';
                if (newItemDepth) {
                    for (let index = 0; index < newItemDepth - 1; index++) {
                        childIndentationIcon += childIndentation;
                    }
                    childIndentationIcon += childArrow;
                }
                let indentation = element.find('.child-icon-wrapper');
                if (indentation) {
                    indentation.html(childIndentationIcon);
                }

            }
        }
        // Show save button after reordering
        $('#save_menu_reorder').show();

    });

    // Save the order of menu items
    $('#save_menu_reorder').on('click', async function () {
        // Disable to prevent multiple clicks
        $(this).prop('disabled', true);
        let reorderItems = getReorderItems();
        Object.values(reorderItems).forEach(function (item) {
            let tr = $(elementSelector + ' tr[data-id="' + item.id + '"]');
            if (tr) {
                tr.attr('data-depth', item.depth);
                tr.attr('data-parent', item.parent);
                tr.attr('data-menuorder', item.menuorder);
            }
        });
        // Check invalid depth
        let invalidDepth = await checkInvalidDepth();
        if (invalidDepth) {
            $(this).prop('disabled', false);
            return;
        }
        // Save the menu items
        await ajaxSaveMenuItems(reorderItems);

    });

};


/**
 *
 * @param {*} rolesByContext
 * @returns
 */
export const contextRoleFilter = (rolesByContext) => {
    const contextSelect = document.querySelector('select[name="context_level"]');
    const roleSelect = document.querySelector('select[name="condition_roleid"]');

    if (!contextSelect || !roleSelect) {
        return;
    }

    contextSelect.addEventListener('change', () => {
        const ctxValue = contextSelect.value;
        const roleList = rolesByContext[ctxValue] || [];
        const previouslySelected = roleSelect.value;
        roleSelect.innerHTML = '';
        roleList.forEach(({ value, label }) => {
            const opt = document.createElement('option');
            opt.value = value;
            opt.textContent = label;
            if (value === previouslySelected) {
                opt.selected = true;
            }
            roleSelect.appendChild(opt);
        });
    });
};
