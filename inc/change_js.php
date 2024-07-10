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

use local_easycustmenu\helper;

/**
 * 
 * @package    local_easycustmenu
 * @copyright  2024 https://santoshmagar.com.np/
 * @author     santoshtmp7
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * 
 */


function menu_target_blank($target_blank_menu)
{
    ob_start();
?>
    <script>
        let target_blank_on_menu = JSON.parse('<?php echo json_encode($target_blank_menu); ?>');
        Object.entries(target_blank_on_menu).forEach(([key, value]) => {
            let label = `${key}`;
            let link = `${value}`;
            if (link) {
                let menu_item = document.querySelectorAll("nav ul li a[href$='" + link + "']");
                menu_item.forEach(item => {
                    if (item.textContent.trim() == label) {
                        item.setAttribute("target", "_blank");
                    }
                });
            }
        });
    </script>
<?php
    $contents = ob_get_contents();
    ob_end_clean();
    $contents = trim(str_replace(["\r", "\n"], '', $contents));
    $contents = str_replace(['<script>', '</script>'], ' ', $contents);
    return $contents;
}


function ecm_setting_adjust($custommenuitems = [])
{
    $custommenuitems = str_replace("\n", "line_breake_backslash_n", $custommenuitems);
    ob_start();
?>
    <script>
        /** set default_custommenuitems */
        var default_custommenuitems = ('<?php echo ($custommenuitems); ?>');
        default_custommenuitems = default_custommenuitems.replaceAll("line_breake_backslash_n", "\n");
        let id_s__custommenuitems = document.querySelector("#id_s__custommenuitems");
        if (id_s__custommenuitems) {
            id_s__custommenuitems.value = default_custommenuitems;
        }
    </script>
<?php
    $contents = ob_get_contents();
    ob_end_clean();
    $contents = trim(str_replace(["\r"], '', $contents));
    $contents = str_replace(['<script>', '</script>'], ' ', $contents);
    return $contents;
}

// 
function menu_item_wrapper_scripts()
{
    ob_start();
?>
    <script>
        //<![CDATA[
        function menu_item_wrapper() {
            return '<?php echo helper::menu_item_wrapper_section(); ?>'
        }
        //]]>
    </script>
<?php
    $contents = ob_get_contents();
    ob_end_clean();
    $contents = trim(str_replace(["\r"], '', $contents));
    $contents = str_replace(['<script>', '</script>'], ' ', $contents);
    return $contents;
}
