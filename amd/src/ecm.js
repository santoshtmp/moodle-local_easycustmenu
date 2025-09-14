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
 * AdminPluginSettingInit
 * @param {*} templatecontext
 */
export const adminPluginSettingInit = (templatecontext) => {
    // Adjust the setting section.
    let beforeDiv = "";
    Templates.render('local_easycustmenu/easycustmenu_setting_header', templatecontext)
        .then(function (html) {
            beforeDiv = document.querySelector("#menu_setting_collection .ecm-header");
            if (beforeDiv) {
                beforeDiv.outerHTML = html;
            } else {
                beforeDiv = document.querySelector("#page-admin-setting-local_easycustmenu #adminsettings .settingsform > h2");
                if (beforeDiv) {
                    beforeDiv.outerHTML = html;
                }
            }
            return true;
        })
        .catch((error) => {
            window.console.log('Template rendering failed:' + error);
        });
};

/**
 * GetEcmBtn
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
 * AdminCoreSettingInit
 * @param {*} jsdata
 */
export const adminCoreSettingInit = (jsdata) => {
    // Core custommenuitems.
    if (document.querySelector("#admin-custommenuitems > .form-setting ")) {
        document.querySelector("#admin-custommenuitems > .form-setting ").prepend(
            getEcmBtn(jsdata.manageNavMenuLink, jsdata.managenavmenulabel)
        );
    }

    // Core customusermenuitems.
    if (document.querySelector("#admin-customusermenuitems > .form-setting ")) {
        document.querySelector("#admin-customusermenuitems > .form-setting ").prepend(
            getEcmBtn(jsdata.manageUserMenuLink, jsdata.manageusermenulabel)
        );
    }
};