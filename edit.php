<?php
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
 *
 * @package    local_easycustmenu
 * @copyright  2025 https://santoshmagar.com.np/
 * @author     santoshtmp7 https://github.com/santoshtmp/moodle-local_easycustmenu
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */

use core\exception\moodle_exception;
use local_easycustmenu\form\easycustmenu_form;
use local_easycustmenu\handler\easycustmenu_handler;
use local_easycustmenu\helper;

// Get require config file.
require_once(__DIR__ . '/../../config.php');
defined('MOODLE_INTERNAL') || die();

// Get parameters.
$type = required_param('type', PARAM_ALPHANUMEXT); // ... navmenu, usermenu, etc.
$action = optional_param('action', '', PARAM_ALPHA);
$id = optional_param('id', 0, PARAM_INT);
$context = \context_system::instance();

// Access checks and validate.
require_login(null, false);
$allowedtypes = helper::get_menu_type();
if (!array_key_exists($type, $allowedtypes)) {
    throw new moodle_exception('invalidtypeparam', 'local_easycustmenu');
}
if (!has_capability('moodle/site:config', $context)) {
    throw new moodle_exception('invalideditaccess', 'local_easycustmenu');
}
if ($action && !in_array($action, ['edit', 'delete'])) {
    throw new moodle_exception('invalidactionparam', 'local_easycustmenu');
}

// Prepare the page information.
$pagepath = '/local/easycustmenu/edit.php';
$urlparam = ['type' => $type];
if ($action) {
    $urlparam['action'] = $action;
}
if ($id) {
    $urlparam['id'] = $id;
}
$url = new moodle_url($pagepath, $urlparam);
$redirecturl = new moodle_url($pagepath, ['type' => $type]);
if ($action == 'edit' && $id == 0) {
    $pagetitle = get_string('add_page_title', 'local_easycustmenu');
} else if ($action == 'edit' && $id > 0) {
    $pagetitle = get_string('edit_page_title', 'local_easycustmenu');
} else if ($action == 'delete') {
    $pagetitle = get_string('delete_page_title', 'local_easycustmenu');
} else {
    $pagetitle = get_string('pluginname', 'local_easycustmenu');
}

// ... setup page information.
$PAGE->set_context($context);
$PAGE->set_url($url);
$PAGE->set_pagelayout('admin');
$PAGE->set_pagetype('easycustmenu_navmenu_setting');
$PAGE->set_title($pagetitle);
$PAGE->set_heading($pagetitle);
$PAGE->requires->css(new moodle_url('/local/easycustmenu/style/nav-menu-setting.css'));
$PAGE->add_body_class('page-easycustmenu');

// Menu form actions or content to display.
if ($action) {
    $easycustmenuform = new easycustmenu_form($redirecturl, [
        'type' => $type,
        'action' => $action,
        'id' => $id,
    ]);

    if ($easycustmenuform->is_cancelled()) {
        redirect($redirecturl);
    } else if ($formdata = $easycustmenuform->get_data()) {
        easycustmenu_handler::save_data($formdata, $url, $redirecturl);
    } else {
        if ($action && $id) {
            // Verify sesskey.
            $sesskey = required_param('sesskey', PARAM_ALPHANUM);
            if ($sesskey != sesskey()) {
                redirect($redirecturl, get_string('invalidsesskey', 'local_easycustmenu'));
            }
            // For Delete.
            if ($action == 'delete') {
                easycustmenu_handler::delete_data($id, $redirecturl);
            }
            // For Edit.
            if ($action == 'edit') {
                $easycustmenuform = easycustmenu_handler::edit_form($easycustmenuform, $id, $redirecturl);
            }
        }
    }

    $contents = '';
    $contents .= '<div class="custom-pages-setting-edit ' . $action . '">';
    $contents .= $easycustmenuform->render();
    $contents .= '</div>';
} else {
    $contents = '';
    $contents .= easycustmenu_handler::get_ecm_menu_items_table($type, $pagepath);
}

// Output Content.
echo $OUTPUT->header();
echo $OUTPUT->render_from_template(
    "local_easycustmenu/easycustmenu_setting_header",
    \local_easycustmenu\helper::get_ecm_header_templatecontext()
);
echo $contents;
echo $OUTPUT->footer();
