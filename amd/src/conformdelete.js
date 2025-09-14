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
 * @copyright  2025 https://santoshmagar.com.np/
 * @author     santoshtmp7 https://santoshmagar.com.np//
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */

define(['core/notification', 'core/str'], function(notification, str) {
    return {
        init: function() {
            document.querySelectorAll('.delete-action').forEach(function(link) {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    const url = this.getAttribute('href');
                    let dataHeading = this.getAttribute('data-heading');
                    let dataTitle = this.getAttribute('data-title');
                    dataTitle = (dataTitle) ? dataTitle : '';
                    notification.confirm(
                        dataHeading,
                        str.get_string('delete_conform_text', 'local_easycustmenu', {menulabel: dataTitle}),
                        str.get_string('yes'),
                        str.get_string('cancel'),
                        function() {
                            window.location.href = url;
                        }
                    );
                });
            });
        }
    };
});

