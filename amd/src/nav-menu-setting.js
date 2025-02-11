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
 * @copyright  2024 https://santoshmagar.com.np/
 * @author     santoshtmp7 https://github.com/santoshtmp/moodle-local_easycustmenu
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */
import $ from 'jquery';
import Ajax from 'core/ajax';
import Templates from 'core/templates';
import menu_drag from 'local_easycustmenu/easy-menu-drag';

/**
 *
 * @param {*} menu_item_num
 * @param {*} itemdepth
 * @param {*} menu_type
 * @param {*} callback
 */
function ajax_get_menu_item_context(menu_item_num, itemdepth, menu_type, callback) {

    let request = {
        methodname: 'get_menu_item_context',
        args: {
            menu_item_num: parseInt(menu_item_num),
            itemdepth: parseInt(itemdepth),
            menu_type: menu_type,
        }
    };

    let ajax = Ajax.call([request])[0];
    ajax.done(function (response) {
        if (!response.status) {
            window.console.log('something is wrong...');
        }
        if (callback) {
            callback(response);
        }
    });
    ajax.fail(function () {
        window.console.log('request failed.');
    });
    // ajax.always(function (response) {
    //     window.console.log(response);
    // });
}


export const init = (menu_type = 'navmenu') => {
    //
    menu_drag.easy_menu_drag(menu_type);
    /**
     * Add menu
     */
    $('.btn-add-menu').on('click', function (e) {
        e.preventDefault();
        var menu_id = $('.menu .menu-item-wrapper:last-child ').attr('id');
        if (menu_id) {
            menu_id = parseInt(menu_id.split('-')[1]) + 1;
        } else {
            menu_id = 1;
        }
        ajax_get_menu_item_context(menu_id, 1, menu_type, function (response) {
            if (response.status) {
                Templates.render(response.template_name, response.template_context)
                    .then(function (html) {
                        $('.menu-wrapper .menu').append(html);
                        menu_drag.easy_menu_drag(menu_type);
                    });
            }
        });


    });

    /**
     * Remove menu
     */
    $(document).on('click', '.btn-remove', function (e) {
        e.stopPropagation();
        e.preventDefault();
        let menu_id = $(this).attr("data-id");
        $('#' + menu_id).remove();
    });


    // hide - show condition field
    $(document).on('click', '.btn-add-condition', function (e) {
        e.stopPropagation();
        e.preventDefault();
        let menu_id = $(this).attr("data-id");
        if ($(this).hasClass('active')) {
            $(this).removeClass('active');
        } else {
            $(this).addClass('active');
        }
        $('#' + menu_id + '  .condition-fields').toggle();
    });


    /**
     * Change input checkbox target_blank value
     */
    $(document).on('change', '.target_blank_no,.target_blank_yes', function () {
        let menu_id = $(this).parent().attr("data-id");
        if ($(this).val() == "0") {
            document.getElementById('target_yes_' + menu_id).checked = false;
        }
        if ($(this).val() == "1") {
            document.getElementById('target_no_' + menu_id).checked = false;
        }
    });

};