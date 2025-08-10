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

namespace local_easycustmenu\form;

use local_easycustmenu\handler\easycustmenu_handler;
use stdClass;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');


/**
 * Form for editing menu items.
 *
 * @package    local_easycustmenu
 * @copyright  
 */
class easycustmenu_form extends \moodleform {

    // table name
    protected static $menu_table = 'local_easycustmenu';

    /**
     * Form definition.
     */
    public function definition() {
        global $DB;

        $mform = $this->_form;
        $type = $this->_customdata['type'];
        $action = $this->_customdata['action'];

        // faq header
        $mform->addElement('header', 'generalsettings', get_string('menu_item', 'local_easycustmenu'));
        // $mform->addElement('html', '<h3>Menu Add</h3>');

        // menu_label
        $mform->addElement('text', 'menu_label', get_string('label', 'local_easycustmenu'), ['size' => 50]);
        $mform->setType('menu_label', PARAM_TEXT);
        $mform->addRule('menu_label', null, 'required', null, 'client');

        // menu_link
        $mform->addElement('text', 'menu_link', get_string('link', 'local_easycustmenu'), ['size' => 50]);
        $mform->setType('menu_link', PARAM_URL);
        $mform->addRule('menu_link', null, 'required', null, 'client');

        // context_level
        $contextoptions = easycustmenu_handler::get_menu_context_level();
        $mform->addElement('select', 'context_level', get_string('context_level', 'local_easycustmenu'), $contextoptions);
        $mform->setType('context_level', PARAM_INT);
        $mform->addRule('context_level', null, 'required', null, 'client');

        // condition_courses Get courses list (only show when context_level == 50)
        $options = [
            'multiple' => true,
            'noselectionstring' => get_string('selectcourses', 'local_easycustmenu'),
        ];
        $mform->addElement('course', 'condition_courses', get_string('course'), $options);
        $mform->hideIf('condition_courses', 'context_level', 'neq', 50);


        // condition_lang.
        $languages = get_string_manager()->get_list_of_translations();
        if (count($languages) > 1) {
            $languages = array_merge(['' => get_string('alllanguages', 'local_easycustmenu')], $languages);
            $mform->addElement('select', 'condition_lang', get_string('condition_lang', 'local_easycustmenu'), $languages);
            $mform->setType('condition_lang', PARAM_TEXT);
        } else {
            $mform->addElement('hidden', 'condition_lang', '');
            $mform->setType('condition_lang', PARAM_TEXT);
        }

        // condition_roleid.
        $roles = $DB->get_records_menu('role', null, 'sortorder ASC', 'id, name');
        $roles = [
            '0' => get_string('everyone', 'local_easycustmenu'),
            '-1' => get_string('admin_user', 'local_easycustmenu'),
        ] + $roles;
        $mform->addElement('select', 'condition_roleid', get_string('condition_role', 'local_easycustmenu'), $roles);
        $mform->setType('condition_roleid', PARAM_INT);

        // Hidden fields for action and ID.
        $mform->addElement('hidden', 'menu_type');
        $mform->setType('menu_type', PARAM_TEXT);
        $mform->setDefault('menu_type', $type);
        // id
        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);
        $mform->setDefault('id', 0);
        // action
        $mform->addElement('hidden', 'action');
        $mform->setType('action', PARAM_TEXT);
        $mform->setDefault('action', $action);

        $this->add_action_buttons();
    }

    /**
     * Custom validation for the form.
     *
     * @param array $data Submitted form data.
     * @param array $files Uploaded files (not used here).
     * @return array Array of errors, empty if no errors.
     */
    function validation($data, $files) {
        global $CFG, $DB;

        $errors = parent::validation($data, $files);

        // Add field validation check for duplicate menu label.
        if ($data['menu_label']) {
            $data_menu_label = trim($data['menu_label']);
            if ($existing = $DB->get_record(self::$menu_table, array('menu_label' => $data_menu_label))) {
                if (!$data['id'] || $existing->id != $data['id']) {
                    $a = new stdClass();
                    $a->menu_label = trim($data['menu_label']);
                    $errors['menu_label'] =  get_string('label_error', 'local_easycustmenu', $a);
                }
            }
        }

        return $errors;
    }
}
