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
        global $DB, $PAGE;

        $mform = $this->_form;
        $type = $this->_customdata['type'];
        $action = $this->_customdata['action'];
        $id = optional_param('id', 0, PARAM_INT);
        $current_menu = $DB->get_record(easycustmenu_handler::$menu_table, ['id' => $id]);

        // header
        $mform->addElement('header', 'generalsettings', get_string('menu_item', 'local_easycustmenu'));

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
            $mform->addElement(
                'autocomplete',
                'condition_lang',
                get_string('condition_lang', 'local_easycustmenu'),
                $languages,       // choices
                ['multiple' => true, 'noselectionstring' => get_string('alllanguages', 'local_easycustmenu')]
            );
            $mform->setType('condition_lang', PARAM_TEXT);
        } else {
            $mform->addElement('hidden', 'condition_lang', '');
            $mform->setType('condition_lang', PARAM_TEXT);
        }


        // Prepare role data grouped by context level.
        $rolesbycontext = self::roleoptions_bycontextlevels($contextoptions);
        $PAGE->requires->js_call_amd('local_easycustmenu/menu_items', 'context_role_filter', [$rolesbycontext]);
        $role_options = [];
        foreach ($rolesbycontext[$current_menu->context_level ?? CONTEXT_SYSTEM] as $key => $value) {
            $role_options[$value['value']] = $value['label'];
        }
        $mform->addElement('select', 'condition_roleid', get_string('condition_role', 'local_easycustmenu'), $role_options);
        $mform->setType('condition_roleid', PARAM_INT);

        if ($type == 'navmenu') {
            // tool tio title
            $mform->addElement('text', 'label_tooltip_title', get_string('label_tooltip_title', 'local_easycustmenu'), ['size' => 50]);
            $mform->setType('label_tooltip_title', PARAM_TEXT);

            // Open in new tab target blank
            $radioarray = [];
            $radioarray[] = $mform->createElement('radio', 'link_target', '', get_string('yes'), 1);
            $radioarray[] = $mform->createElement('radio', 'link_target', '', get_string('no'), 0);
            $mform->addGroup($radioarray, 'link_target_group', get_string('link_target_option', 'local_easycustmenu'), [' '], false);
            $mform->setDefault('link_target', 0);
        }
        // 
        if ($type == 'navmenu') {
            $menus = easycustmenu_handler::get_menu_items($type);
            $menu_parent = [0 => get_string('top')];
            foreach ($menus as $key => $menu) {
                if ($menu->id == $id) {
                    continue;
                }
                if ($menu->depth > $current_menu->depth) {
                    continue;
                }
                $depth = '';
                for ($i = 0; $i < $menu->depth; $i++) {
                    $depth .= '-';
                }
                $menu_parent[$menu->id] = $depth . ' ' . $menu->menu_label;
            }
            $mform->addElement(
                'select',
                'parent',
                get_string('menu_parent', 'local_easycustmenu'),
                $menu_parent
            );
            $mform->setType('parent', PARAM_INT);
            $mform->setDefault('parent', 0);
        } else {
            // parent
            $mform->addElement('hidden', 'parent');
            $mform->setType('parent', PARAM_INT);
            $mform->setDefault('parent', 0);
        }

        // depth
        $mform->addElement('hidden', 'depth');
        $mform->setType('depth', PARAM_INT);
        $mform->setDefault('depth', 0);

        // menu_type.
        $mform->addElement('hidden', 'menu_type');
        $mform->setType('menu_type', PARAM_TEXT);
        $mform->setDefault('menu_type', $type);
        // id
        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);
        $mform->setDefault('id', 0);

        // menu_order
        $mform->addElement('hidden', 'menu_order');
        $mform->setType('menu_order', PARAM_INT);
        $mform->setDefault('menu_order', 0);
        // action
        $mform->addElement('hidden', 'action');
        $mform->setType('action', PARAM_TEXT);
        $mform->setDefault('action', $action);

        $this->add_action_buttons();
    }


    /**
     * @param array $contextoptions
     */
    public static function roleoptions_bycontextlevels($contextoptions) {
        $rolesbycontext = [];
        foreach ($contextoptions as $ctxvalue => $ctxlabel) {
            $roles = easycustmenu_handler::get_context_roles($ctxvalue);
            $roleopts = [
                [
                    'value' => 0,
                    'label'  => get_string('everyone', 'local_easycustmenu'),
                ],
                [
                    'value' => '-1',
                    'label'  => get_string('admin'),
                ],
            ];
            foreach ($roles as $role) {
                $roleopts[] = [
                    'value' => $role->id,
                    'label' => role_get_name($role),
                ];
            }
            $rolesbycontext[$ctxvalue] = $roleopts;
        }
        return $rolesbycontext;
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
