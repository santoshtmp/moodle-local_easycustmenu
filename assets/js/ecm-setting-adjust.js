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
}

let easycustmenu_setting_header_top = document.querySelector(".easycustmenu_setting_header_top")
if (easycustmenu_setting_header_top) {
    easycustmenu_setting_header_top.remove();
}


/**
 *
 * @param {*} link
 * @param {*} label
 * @param {*} css_class
 * @returns
 */
function get_ecm_btn(link, label, css_class = '') {
    let btn = '<a href="' + link + '" class="btn btn-secondary btn-manage-ecm ' + css_class + '" style="margin:8px;">' + label + '</a>';
    btn = new DOMParser().parseFromString(btn, 'text/html');
    return btn.querySelector('a.btn-manage-ecm');

}
/**
 * custommenuitems
 */
let btn_ecm_custommenuitems = get_ecm_btn("/local/easycustmenu/pages/navmenu.php", "Manage Custom Menu Through Easy Custom Menu");
var show_menu_label = 'Show Default "Custom menu items" Input Area';
var hide_menu_label = 'Hide Default "Custom menu items" Input Area';
if (document.querySelector("#admin-custommenuitems > .form-setting ")) {
    document.querySelector("#admin-custommenuitems > .form-setting ").prepend(btn_ecm_custommenuitems);
    document.querySelector("#admin-custommenuitems > .form-setting ").prepend(get_ecm_btn('#', show_menu_label, 'show_hide_customusermenuitems show_menu'));
    document.querySelector("#admin-custommenuitems > .form-setting .form-textarea").style.display = "none";
    document.querySelector("#admin-custommenuitems > .form-setting .form-defaultinfo ").style.display = "none";
    document.querySelector("#admin-custommenuitems > .form-setting .form-description ").style.display = "none";

    var element_show_hide = document.querySelector("#admin-custommenuitems > .form-setting a.show_hide_customusermenuitems");
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
        return "";
    });
}


/**customusermenuitems */
let btn_ecm_customusermenuitems = get_ecm_btn("/local/easycustmenu/pages/usermenu.php", "Manage User Menu Through Easy Custom Menu");
if (document.querySelector("#admin-customusermenuitems > .form-setting")) {
    document.querySelector("#admin-customusermenuitems > .form-setting").prepend(btn_ecm_customusermenuitems);
}