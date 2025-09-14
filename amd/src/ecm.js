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
import Templates from 'core/templates';

/**
 *
 * @param {*} templatecontext
 */
export const adminPluginSettingInit = (templatecontext) => {
    /**
    * Adjust the setting section
    */
    let beforeDiv = "";
    Templates.render('local_easycustmenu/easycustmenu_setting_header', templatecontext)
        .then(function(html) {
            beforeDiv = document.querySelector("#menu_setting_collection .ecm-header");
            if (beforeDiv) {
                beforeDiv.outerHTML = html;
            } else {
                beforeDiv = document.querySelector("#page-admin-setting-local_easycustmenu #adminsettings .settingsform > h2");
                if (beforeDiv) {
                    beforeDiv.outerHTML = html;
                }
            }
        });
};

/**
 *
 * @param {*} link
 * @param {*} label
 * @param {*} cssClass
 * @returns
 */
function getEcmBtn(link, label, cssClass = '') {
    let btn = '<a href="' + link + '" class="btn btn-secondary btn-manage-ecm ';
    btn += cssClass + ' " style="margin:8px;">' + label + '</a>';
    btn = new DOMParser().parseFromString(btn, 'text/html');
    return btn.querySelector('a.btn-manage-ecm');

}

/**
 *
 * @param {*} stringArray
 */
export const admin_core_setting_init = (stringArray) => {

    let navmenuLink = "/local/easycustmenu/edit.php?type=navmenu";
    let usermenuLink = "/local/easycustmenu/edit.php?type=usermenu";
    /**
    * Core custommenuitems
    */
    let btnecmcustommenuitems = getEcmBtn(navmenuLink, stringArray['manage_menu_label']);
    var showmenulabel = stringArray['show_menu_label']; //'Show Default "Custom menu items" ';
    var hidemenulabel = stringArray['hide_menu_label']; //'Hide Default "Custom menu items" ';
    let btnHideShowCustommenuitems = getEcmBtn('#', showmenulabel, 'show_hide_custommenuitems show_menu');
    if (document.querySelector("#admin-custommenuitems > .form-setting ")) {
        document.querySelector("#admin-custommenuitems > .form-setting ").prepend(btnecmcustommenuitems);
        document.querySelector("#admin-custommenuitems > .form-setting ").prepend(btnHideShowCustommenuitems);
        document.querySelector("#admin-custommenuitems > .form-setting .form-textarea").style.display = "none";
        document.querySelector("#admin-custommenuitems > .form-setting .form-defaultinfo ").style.display = "none";
        document.querySelector("#admin-custommenuitems > .form-setting .form-description ").style.display = "none";

        var elementShowHide = document.querySelector("#admin-custommenuitems > .form-setting a.show_hide_custommenuitems");
        elementShowHide.addEventListener("click", function(e) {
            e.stopPropagation();
            e.preventDefault();
            if (elementShowHide.classList.contains("show_menu")) {
                elementShowHide.classList.remove("show_menu");
                elementShowHide.classList.add("hide_menu");
                elementShowHide.textContent = hidemenulabel;
                document.querySelector("#admin-custommenuitems > .form-setting .form-textarea").style.display = "block";
                document.querySelector("#admin-custommenuitems > .form-setting .form-defaultinfo ").style.display = "block";
                document.querySelector("#admin-custommenuitems > .form-setting .form-description ").style.display = "block";
            } else {
                elementShowHide.classList.remove("hide_menu");
                elementShowHide.classList.add("show_menu");
                elementShowHide.textContent = showmenulabel;
                document.querySelector("#admin-custommenuitems > .form-setting .form-textarea").style.display = "none";
                document.querySelector("#admin-custommenuitems > .form-setting .form-defaultinfo ").style.display = "none";
                document.querySelector("#admin-custommenuitems > .form-setting .form-description ").style.display = "none";
            }
        });
    }

    /** Core customusermenuitems */
    let btnecmcustomusermenuitems = getEcmBtn(usermenuLink, stringArray['manage_menu_label_2']);
    var showmenulabel2 = stringArray['show_menu_label_2'];
    var hidemenulabel2 = stringArray['hide_menu_label_2'];
    let btnhideshowcustomusermenuitems = getEcmBtn('#', showmenulabel2, 'show_hide_customusermenuitems show_menu');

    if (document.querySelector("#admin-customusermenuitems > .form-setting ")) {
        document.querySelector("#admin-customusermenuitems > .form-setting ").prepend(btnecmcustomusermenuitems);
        document.querySelector("#admin-customusermenuitems > .form-setting ").prepend(btnhideshowcustomusermenuitems);
        document.querySelector("#admin-customusermenuitems > .form-setting .form-textarea").style.display = "none";
        document.querySelector("#admin-customusermenuitems > .form-setting .form-defaultinfo ").style.display = "none";
        document.querySelector("#admin-customusermenuitems > .form-setting .form-description ").style.display = "none";

        var elementshowhide2 = document.querySelector("#admin-customusermenuitems  a.show_hide_customusermenuitems");
        elementshowhide2.addEventListener("click", function(e) {
            e.stopPropagation();
            e.preventDefault();
            if (elementshowhide2.classList.contains("show_menu")) {
                elementshowhide2.classList.remove("show_menu");
                elementshowhide2.classList.add("hide_menu");
                elementshowhide2.textContent = hidemenulabel2;
                document.querySelector("#admin-customusermenuitems > .form-setting .form-textarea").style.display = "block";
                document.querySelector("#admin-customusermenuitems > .form-setting .form-defaultinfo ").style.display = "block";
                document.querySelector("#admin-customusermenuitems > .form-setting .form-description ").style.display = "block";
            } else {
                elementshowhide2.classList.remove("hide_menu");
                elementshowhide2.classList.add("show_menu");
                elementshowhide2.textContent = showmenulabel2;
                document.querySelector("#admin-customusermenuitems > .form-setting .form-textarea").style.display = "none";
                document.querySelector("#admin-customusermenuitems > .form-setting .form-defaultinfo ").style.display = "none";
                document.querySelector("#admin-customusermenuitems > .form-setting .form-description ").style.display = "none";

            }
        });
    }
};