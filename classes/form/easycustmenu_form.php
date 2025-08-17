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
 * Form for editing easy custom menu items.
 *
 * @package    local_easycustmenu
 * @copyright  2025 https://santoshmagar.com.np/
 * @author     santoshtmp7 https://github.com/santoshtmp/moodle-local_easycustmenu
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */
class easycustmenu_form extends \moodleform {
    /**
     * Form definition.
     */
    public function definition() {
        global $PAGE;

        $mform = $this->_form;
        $type = $this->_customdata['type'];
        $action = $this->_customdata['action'];
        $id = $this->_customdata['id'];
        $currentmenu = easycustmenu_handler::get_ecm_menu_by_id($id);
        $menuitemtitle = ($id) ? 'edit_menu_item' : 'add_menu_item';

        // Header.
        $mform->addElement('header', 'generalsettings', get_string($menuitemtitle, 'local_easycustmenu'));

        // Menu label.
        $mform->addElement('text', 'menu_label', get_string('menu_label', 'local_easycustmenu'), ['size' => 50]);
        $mform->setType('menu_label', PARAM_TEXT);
        $mform->addRule('menu_label', null, 'required', null, 'client');

        // Menu link.
        $mform->addElement('text', 'menu_link', get_string('menu_link', 'local_easycustmenu'), ['size' => 50]);
        $mform->setType('menu_link', PARAM_URL);
        $mform->addRule('menu_link', null, 'required', null, 'client');

        // Context level.
        $contextoptions = \local_easycustmenu\helper::get_ecm_context_level();
        $mform->addElement('select', 'context_level', get_string('menu_context', 'local_easycustmenu'), $contextoptions);
        $mform->setType('context_level', PARAM_INT);

        // Condition courses Get courses list (only show when context_level == 50).
        $options = [
            'multiple' => true,
            'noselectionstring' => get_string('allcourses', 'local_easycustmenu'),
        ];
        $mform->addElement('course', 'condition_courses', get_string('course'), $options);
        $mform->hideIf('condition_courses', 'context_level', 'neq', 50);

        // Condition lang.
        $languages = get_string_manager()->get_list_of_translations();
        if (count($languages) > 1) {
            $mform->addElement(
                'autocomplete',
                'condition_lang',
                get_string('menu_condition_lang', 'local_easycustmenu'),
                $languages,
                ['multiple' => true, 'noselectionstring' => get_string('alllanguages', 'local_easycustmenu')]
            );
            $mform->setType('condition_lang', PARAM_TEXT);
        }

        // Prepare role data grouped by context level.
        $rolesbycontext = self::roleoptions_bycontextlevels($contextoptions);
        $PAGE->requires->js_call_amd('local_easycustmenu/menu_items', 'context_role_filter', [$rolesbycontext]);
        $roleoptions = [];
        foreach ($rolesbycontext[$currentmenu->context_level ?? CONTEXT_SYSTEM] as $key => $value) {
            $roleoptions[$value['value']] = $value['label'];
        }
        $mform->addElement('select', 'condition_roleid', get_string('menu_condition_role', 'local_easycustmenu'), $roleoptions);
        $mform->setType('condition_roleid', PARAM_INT);

        if ($type == 'navmenu') {
            // Tool tio title.
            $mform->addElement(
                'text',
                'label_tooltip_title',
                get_string('menu_label_tooltip_title', 'local_easycustmenu'),
                ['size' => 50]
            );
            $mform->setType('label_tooltip_title', PARAM_TEXT);

            // Open in new tab target blank.
            $radioarray = [];
            $radioarray[] = $mform->createElement('radio', 'link_target', '', get_string('yes'), 1);
            $radioarray[] = $mform->createElement('radio', 'link_target', '', get_string('no'), 0);
            $mform->addGroup(
                $radioarray,
                'link_target_group',
                get_string('open_in_a_new_browser_tab', 'local_easycustmenu'),
                [' '],
                false
            );
            $mform->setDefault('link_target', 0);
        }

        if ($type == 'navmenu') {
            $menus = easycustmenu_handler::get_ecm_menu_items($type);
            $menuparent = [0 => get_string('top')];
            foreach ($menus as $key => $menu) {
                if ($menu->id == $id) {
                    continue;
                }
                if ($currentmenu && $menu->depth > $currentmenu->depth) {
                    continue;
                }
                $depth = '';
                for ($i = 0; $i < $menu->depth; $i++) {
                    $depth .= '-';
                }
                $menuparent[$menu->id] = $depth . ' ' . $menu->menu_label;
            }
            $mform->addElement(
                'select',
                'parent',
                get_string('menu_parent', 'local_easycustmenu'),
                $menuparent
            );
            $mform->setType('parent', PARAM_INT);
            $mform->setDefault('parent', 0);
        } else {
            // Hidden parent.
            $mform->addElement('hidden', 'parent');
            $mform->setType('parent', PARAM_INT);
            $mform->setDefault('parent', 0);
        }

        // Hidden depth.
        $mform->addElement('hidden', 'depth');
        $mform->setType('depth', PARAM_INT);
        $mform->setDefault('depth', 0);

        // Hidden menu_type.
        $mform->addElement('hidden', 'menu_type');
        $mform->setType('menu_type', PARAM_TEXT);
        $mform->setDefault('menu_type', $type);
        // Hidden id.
        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);
        $mform->setDefault('id', 0);

        // Hidden menu_order.
        $mform->addElement('hidden', 'menu_order');
        $mform->setType('menu_order', PARAM_INT);
        $mform->setDefault('menu_order', 0);
        // Hidden action.
        $mform->addElement('hidden', 'action');
        $mform->setType('action', PARAM_TEXT);
        $mform->setDefault('action', $action);

        $this->add_action_buttons();
    }


    /**
     * Builds a list of role options grouped by context levels.
     *
     * @param array $contextoptions The context options array.
     * @return array Roles grouped by context level.
     */
    public static function roleoptions_bycontextlevels($contextoptions) {
        $rolesbycontext = [];
        foreach ($contextoptions as $ctxvalue => $ctxlabel) {
            $roles = \local_easycustmenu\helper::get_ecm_context_roles($ctxvalue);
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
    public function validation($data, $files) {
        global $DB;

        $errors = parent::validation($data, $files);

        // Add field validation check for duplicate menu label.
        if ($data['menu_label']) {
            $normalizedlabel = \core_text::strtolower(trim($data['menu_label']));

            $sql = "SELECT id FROM {local_easycustmenu} WHERE LOWER(menu_label) = :label AND menu_type = :menu_type";
            $params = [
                'label' => $normalizedlabel,
                'menu_type' => $data['menu_type'] ?? 'navmenu',
            ];

            $existing = $DB->get_record_sql($sql, $params);

            // If a record exists and it's not the current editing record.
            if ($existing && (empty($data['id']) || $existing->id != $data['id'])) {
                $a = new stdClass();
                $a->menu_label = trim($data['menu_label']);
                $errors['menu_label'] = get_string('label_error', 'local_easycustmenu', $a);
            }
        }

        return $errors;
    }
}
