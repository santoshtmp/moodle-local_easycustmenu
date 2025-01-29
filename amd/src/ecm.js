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
 * @param {*} plugin_header_content
 */
export const admin_plugin_setting_init = (plugin_header_content) => {
    /**
    * adjust the setting section
    */
    let beforeDiv = "";
    beforeDiv = document.querySelector("#menu_setting_collection .ecm-header");
    if (beforeDiv) {
        beforeDiv.outerHTML = plugin_header_content;
    } else {
        beforeDiv = document.querySelector("#page-admin-setting-local_easycustmenu #adminsettings .settingsform > h2");
        if (beforeDiv) {
            beforeDiv.outerHTML = plugin_header_content;
        }
    }

};


/**
 *
 * @param {*} string_array
 */
export const admin_core_setting_init = (string_array) => {

    window.console.log(string_array);
    /**
     *
     * @param {*} link
     * @param {*} label
     * @param {*} css_class
     * @returns
     */
    function get_ecm_btn(link, label, css_class = '') {
        let btn = '<a href="' + link + '" class="btn btn-secondary btn-manage-ecm ';
        btn += css_class + ' " style="margin:8px;">' + label + '</a>';
        btn = new DOMParser().parseFromString(btn, 'text/html');
        return btn.querySelector('a.btn-manage-ecm');

    }

    /**
    * core custommenuitems
    */
    let btn_ecm_custommenuitems = get_ecm_btn("/local/easycustmenu/pages/navmenu.php", string_array['manage_menu_label']);
    var show_menu_label = string_array['show_menu_label']; //'Show Default "Custom menu items" ';
    var hide_menu_label = string_array['hide_menu_label']; //'Hide Default "Custom menu items" ';
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
    let btn_ecm_customusermenuitems = get_ecm_btn("/local/easycustmenu/pages/usermenu.php", string_array['manage_menu_label_2']);
    var show_menu_label_2 = string_array['show_menu_label_2'];
    var hide_menu_label_2 = string_array['hide_menu_label_2'];
    let btn_hide_show_customusermenuitems = get_ecm_btn('#', show_menu_label_2, 'show_hide_customusermenuitems show_menu');

    if (document.querySelector("#admin-customusermenuitems > .form-setting ")) {
        document.querySelector("#admin-customusermenuitems > .form-setting ").prepend(btn_ecm_customusermenuitems);
        document.querySelector("#admin-customusermenuitems > .form-setting ").prepend(btn_hide_show_customusermenuitems);
        document.querySelector("#admin-customusermenuitems > .form-setting .form-textarea").style.display = "none";
        document.querySelector("#admin-customusermenuitems > .form-setting .form-defaultinfo ").style.display = "none";
        document.querySelector("#admin-customusermenuitems > .form-setting .form-description ").style.display = "none";

        var element_show_hide_2 = document.querySelector("#admin-customusermenuitems  a.show_hide_customusermenuitems");
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