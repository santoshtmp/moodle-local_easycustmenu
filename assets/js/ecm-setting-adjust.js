
/**
 * adjust the setting section 
 */
const newDiv = document.querySelector(".easycustmenu_setting_header");
let beforeDiv = "";
if (beforeDiv = document.getElementById("menu_setting_collection")) {
    const parentDiv = beforeDiv.parentNode;
    parentDiv.insertBefore(newDiv, beforeDiv);
}
if (beforeDiv = document.querySelector("#adminsettings .settingsform > h2")) {
    beforeDiv.outerHTML = newDiv.outerHTML;
}
document.querySelector(".easycustmenu_setting_header_top").remove();

// custommenuitems
let btn_ecm_custommenuitems = '<a href="/local/easycustmenu/pages/navmenu.php" class="btn btn-secondary btn-manage-ecm" style="margin:8px 0;">Manage Through Easy Custom Menu</a>';
btn_ecm_custommenuitems = new DOMParser().parseFromString(btn_ecm_custommenuitems, 'text/html');
if (document.querySelector("#admin-custommenuitems > div")) {
    document.querySelector("#admin-custommenuitems > div").append(btn_ecm_custommenuitems.body);
}
// customusermenuitems
let btn_ecm_customusermenuitems = '<a href="/local/easycustmenu/pages/usermenu.php" class="btn btn-secondary btn-manage-ecm" style="margin:8px 0;">Manage Through Easy Custom Menu</a>';
btn_ecm_customusermenuitems = new DOMParser().parseFromString(btn_ecm_customusermenuitems, 'text/html');
if (document.querySelector("#admin-customusermenuitems > div")) {
    document.querySelector("#admin-customusermenuitems > div").append(btn_ecm_customusermenuitems.body);
}