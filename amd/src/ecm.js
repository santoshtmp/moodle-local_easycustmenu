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
// import $ from 'jquery';


/**
 *
 * @param {*} target_blank_menu
 */
export const target_blank_menu = (target_blank_menu) => {
    if (target_blank_menu) {
        let target_blank_on_menu = JSON.parse(target_blank_menu);
        Object.entries(target_blank_on_menu).forEach(([key, value]) => {
            let label = `${key}`;
            let link = `${value}`;
            if (link) {
                let menu_item = document.querySelectorAll("nav ul li a[href$='" + link + "']");
                menu_item.forEach(item => {
                    if (item.textContent.trim() == label) {
                        item.setAttribute("target", "_blank");
                    }
                });
            }
        });
    }
};

/**
 *
 */
export const admin_setting_init = () => {
    /**
    * adjust the setting section
    */
    const newDiv = document.querySelector(".easycustmenu_setting_header");
    if (newDiv) {
        let beforeDiv = "";
        beforeDiv = document.getElementById("menu_setting_collection");
        if (beforeDiv) {
            const parentDiv = beforeDiv.parentNode;
            parentDiv.insertBefore(newDiv, beforeDiv);
        }
        beforeDiv = document.querySelector("#page-admin-setting-local_easycustmenu #adminsettings .settingsform > h2");
        if (beforeDiv) {
            beforeDiv.outerHTML = newDiv.outerHTML;
        }
        let easycustmenu_setting_header_top = document.querySelector(".easycustmenu_setting_header_top");
        if (easycustmenu_setting_header_top) {
            easycustmenu_setting_header_top.remove();
        }
    }


    /**
   *
   * @param {*} link
   * @param {*} label
   * @param {*} css_class
   * @returns
   */
    function get_ecm_btn(link, label, css_class = '') {
        let btn = '<a href="' + link + '" class="btn btn-secondary btn-manage-ecm ' + css_class + ' " style="margin:8px;">' + label + '</a>';
        btn = new DOMParser().parseFromString(btn, 'text/html');
        return btn.querySelector('a.btn-manage-ecm');

    }

    /**
    * core custommenuitems
    */
    let btn_ecm_custommenuitems = get_ecm_btn("/local/easycustmenu/pages/navmenu.php", "Manage Custom Menu Through Easy Custom Menu");
    var show_menu_label = 'Show Default "Custom menu items" ';
    var hide_menu_label = 'Hide Default "Custom menu items" ';
    let btn_hide_show_custommenuitems = get_ecm_btn('#', show_menu_label, 'show_hide_custommenuitems show_menu');
    if (document.querySelector("#admin-custommenuitems > .form-setting ")) {
        document.querySelector("#admin-custommenuitems > .form-setting ").prepend(btn_ecm_custommenuitems);
        document.querySelector("#admin-custommenuitems > .form-setting ").prepend(btn_hide_show_custommenuitems);
        document.querySelector("#admin-custommenuitems > .form-setting .form-textarea").style.display = "none";
        document.querySelector("#admin-custommenuitems > .form-setting .form-defaultinfo ").style.display = "none";
        document.querySelector("#admin-custommenuitems > .form-setting .form-description ").style.display = "none";

        var element_show_hide = document.querySelector("#admin-custommenuitems > .form-setting a.show_hide_custommenuitems");
        element_show_hide.addEventListener("click", function (e) {
            e.stopPropagation();
            e.preventDefault();
            if (element_show_hide.classList.contains("show_menu")) {
                element_show_hide.classList.remove("show_menu");
                element_show_hide.classList.add("hide_menu");
                element_show_hide.textContent = hide_menu_label;
                document.querySelector("#admin-custommenuitems > .form-setting .form-textarea").style.display = "block";
                document.querySelector("#admin-custommenuitems > .form-setting .form-defaultinfo ").style.display = "block";
                document.querySelector("#admin-custommenuitems > .form-setting .form-description ").style.display = "block";
            } else {
                element_show_hide.classList.remove("hide_menu");
                element_show_hide.classList.add("show_menu");
                element_show_hide.textContent = show_menu_label;
                document.querySelector("#admin-custommenuitems > .form-setting .form-textarea").style.display = "none";
                document.querySelector("#admin-custommenuitems > .form-setting .form-defaultinfo ").style.display = "none";
                document.querySelector("#admin-custommenuitems > .form-setting .form-description ").style.display = "none";
            }
        });
    }


    /** core customusermenuitems */
    let btn_ecm_customusermenuitems = get_ecm_btn("/local/easycustmenu/pages/usermenu.php", "Manage User Menu Through Easy Custom Menu");
    var show_menu_label_2 = 'Show Default "User menu items" ';
    var hide_menu_label_2 = 'Hide Default "User menu items" ';
    let btn_hide_show_customusermenuitems = get_ecm_btn('#', show_menu_label_2, 'show_hide_customusermenuitems show_menu');

    if (document.querySelector("#admin-customusermenuitems > .form-setting ")) {
        document.querySelector("#admin-customusermenuitems > .form-setting ").prepend(btn_ecm_customusermenuitems);
        document.querySelector("#admin-customusermenuitems > .form-setting ").prepend(btn_hide_show_customusermenuitems);
        document.querySelector("#admin-customusermenuitems > .form-setting .form-textarea").style.display = "none";
        document.querySelector("#admin-customusermenuitems > .form-setting .form-defaultinfo ").style.display = "none";
        document.querySelector("#admin-customusermenuitems > .form-setting .form-description ").style.display = "none";

        var element_show_hide_2 = document.querySelector("#admin-customusermenuitems > .form-setting a.show_hide_customusermenuitems");
        element_show_hide_2.addEventListener("click", function (e) {
            e.stopPropagation();
            e.preventDefault();
            if (element_show_hide_2.classList.contains("show_menu")) {
                element_show_hide_2.classList.remove("show_menu");
                element_show_hide_2.classList.add("hide_menu");
                element_show_hide_2.textContent = hide_menu_label_2;
                document.querySelector("#admin-customusermenuitems > .form-setting .form-textarea").style.display = "block";
                document.querySelector("#admin-customusermenuitems > .form-setting .form-defaultinfo ").style.display = "block";
                document.querySelector("#admin-customusermenuitems > .form-setting .form-description ").style.display = "block";
            } else {
                element_show_hide_2.classList.remove("hide_menu");
                element_show_hide_2.classList.add("show_menu");
                element_show_hide_2.textContent = show_menu_label_2;
                document.querySelector("#admin-customusermenuitems > .form-setting .form-textarea").style.display = "none";
                document.querySelector("#admin-customusermenuitems > .form-setting .form-defaultinfo ").style.display = "none";
                document.querySelector("#admin-customusermenuitems > .form-setting .form-description ").style.display = "none";

            }
        });
    }
};