## Release notes

### Version 1.0.0 (2025031501)

* initial release

### Version 1.1.0 (2025063000)

* Invalid character form validation
* Added system user roles to menu condition roles

### Version 2.0.0 (2025081800)

* Add more condition roles and the context level as site or course 
* Creadte seperate table to store easycustmenu settings
* change the menu control list and menu item form.

### Version 2.0.1 (2025082800)
* Fix db issue

### Version 2.1.1 (2025091400)
* Add support from moodle 4.5+ and 5.0+

### Version 2.1.2 (2026031200)
* Fix guest access item and menu link validation

### Version 2.1.3 (2026031300)
* Multi-role support: Menu items can now be assigned to multiple roles (e.g., Teacher + Student) instead of only one role at a time
* Changed condition_roleid column from INT to CHAR(150) to store comma-separated role IDs
* Added DB upgrade step to migrate existing single role values to the new format
* Role selector changed from single-select dropdown to multi-select autocomplete in the menu item form
