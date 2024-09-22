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
 * @author     santoshtmp7
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */
import $ from 'jquery';
import menu_drag from 'local_easycustmenu/easy-menu-drag';


export const init = (menu_item, menu_type = 'navmenu') => {
    //
    menu_drag.easy_menu_drag(menu_type);

    /**
     * Add menu
     */
    $('.btn-add-menu').on('click', function (e) {
        e.preventDefault();
        let total_menu = $('.menu .menu-item-wrapper ').length + 1;
        menu_item = menu_item.replaceAll('menu-id', 'menu-' + total_menu);
        $('.menu-wrapper .menu').append(menu_item);
    });

    /**
     * Remove menu
     */
    $(document).on('click', '.btn-remove', function (e) {
        e.stopPropagation();
        e.preventDefault();
        let sort_id = $(this).attr("data-id");
        $('#' + sort_id).remove();
    });


    // hide - show condition field
    $(document).on('click', '.btn-add-condition', function (e) {
        e.stopPropagation();
        e.preventDefault();
        let sort_id = $(this).attr("data-id");
        if ($(this).hasClass('active')) {
            $(this).removeClass('active');
        } else {
            $(this).addClass('active');
        }
        $('#' + sort_id + ' > .menu-item  .condition-fields').toggle();
    });


    /**
     * Change input checkbox target_blank value
     */
    $(document).on('change', '.target_blank_no,.target_blank_yes', function () {
        let sort_id = $(this).parent().attr("data-id");
        if ($(this).val() == "0") {
            document.getElementById('target_yes_' + sort_id).checked = false;
        }
        if ($(this).val() == "1") {
            document.getElementById('target_no_' + sort_id).checked = false;
        }
    });

};