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
use core\output\html_writer;
use local_easycustmenu\form\easycustmenu_form;
use local_easycustmenu\handler\easycustmenu_handler;
use local_easycustmenu\helper;

// Get require config file.
require_once(__DIR__ . '/../../config.php');
defined('MOODLE_INTERNAL') || die();

// Get parameters.
$type = required_param('type', PARAM_ALPHANUMEXT); // navmenu, usermenu, etc.
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
$page_path = '/local/easycustmenu/edit.php';
$url_param = ['type' => $type];
if ($action) {
    $url_param['action'] = $action;
}
if ($id) {
    $url_param['id'] = $id;
}
$url = new moodle_url($page_path, $url_param);
$redirect_url = new moodle_url($page_path, ['type' => $type]);
if ($action == 'add') {
    $page_title = get_string('add_page_title', 'local_easycustmenu');
} else if ($action == 'edit') {
    $page_title = get_string('edit_page_title', 'local_easycustmenu');
} else if ($action == 'delete') {
    $page_title = get_string('delete_page_title', 'local_easycustmenu');
} else {
    $page_title = get_string('pluginname', 'local_easycustmenu');
}

// setup page information.
$PAGE->set_context($context);
$PAGE->set_url($url);
$PAGE->set_pagelayout('admin');
$PAGE->set_pagetype('easycustmenu_navmenu_setting');
$PAGE->set_title($page_title);
$PAGE->set_heading($page_title);
$PAGE->requires->css(new moodle_url('/local/easycustmenu/style/nav-menu-setting.css'));
$PAGE->add_body_class('page-easycustmenu');

// Menu form actions or content to display.
if ($action) {
    $easycustmenu_form = new easycustmenu_form($redirect_url, [
        'type' => $type,
        'action' => $action,
        'id' => $id
    ]);

    if ($easycustmenu_form->is_cancelled()) {
        redirect($redirect_url);
    } else if ($form_data = $easycustmenu_form->get_data()) {
        easycustmenu_handler::save_data($form_data, $url, $redirect_url);
    } else {
        if ($action && $id) {
            // verify sesskey
            $sesskey = required_param('sesskey', PARAM_ALPHANUM);
            if ($sesskey != sesskey()) {
                redirect($redirect_url, get_string('invalidsesskey', 'local_easycustmenu'));
            }
            // For Delete
            if ($action == 'delete') {
                easycustmenu_handler::delete_data($id, $redirect_url);
            }
            // For Edit
            if ($action == 'edit') {
                $easycustmenu_form = easycustmenu_handler::edit_form($easycustmenu_form, $id, $redirect_url);
            }
        }
    }

    $contents = '';
    $contents .= '<div class="custom-pages-setting-edit ' . $action . '">';
    $contents .= $easycustmenu_form->render();
    $contents .= '</div>';
} else {
    $contents = '';
    $contents .= easycustmenu_handler::get_ecm_menu_items_table($type, $page_path);
}

// Output Content.
echo $OUTPUT->header();
echo $OUTPUT->render_from_template(
    "local_easycustmenu/easycustmenu_setting_header",
    \local_easycustmenu\helper::get_ecm_header_templatecontext()
);
echo $contents;
echo $OUTPUT->footer();
