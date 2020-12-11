ACF-WP-Objects Changelog
========================

0.5.4
-----
 - Fix: Includer field admin local field groups

0.5.3
-----
 - Page Layout: Fix repeater collapsed fields
 - Feature: ID-Option for Text fields. Ensures uniqueness the on screen. Use with repeaters!
 - Feature: introduce api functions `acf_recreate_field_keys( $fields )` and `acf_is_fieldgroup_admin()`
 - Fix: RepeaterChoices init causing weird behaviour in field group admin


0.5.2
-----
 - CSS: fix seamless style for repeaters inside accordions
 - Page Layout: Allow `save_post_content` to be callable

0.5.1
-----
 - Feature: Location Rule Everywhere
 - Fix: LocalJSON not saving under custom paths in ACF 5.9+
 - API: introduce `acf_get_page_layouts()` and `acf_get_page_layout( $page_layout )`
 - Page-Layout: skip layouts with `row_layout` not set
 
0.5.0
-----
 - Feature: Polylang compatibiity – assign ACF fields to polylang languages

0.4.18
------
 - Fix: Post content not saved
 - Styles: fix seamless, no-label

0.4.16
------
 - CSS: Fix seamless
 - CSS: Introduce no-label
 - Repeater Choice: disable field if there are no choices
 - Repeater Choice: Choose visualize field
 - Fix: Helper\Conditionals combine function

0.4.6
-----
 - CSS: Compact styles
 - Fix: includer field bug in field group admin

0.4.5
-----
 - Repeater Choices: Fix local fields not showing in field settings
 - Fix: JSON Load Paths

0.4.4
-----
 - Compatibility: Use ACF 5.9+ native save json path

0.4.1
-----
 - Feature: Sweet Spot Auto

0.4.0
-----
 - Feature: Page Layouts
 - Feature: Localize field groups through .po
 - Feature: Customize local JSON paths

0.2.12
------
 - Fix network settings

0.2.11
------
 - Security hardening

0.2.8 - 0.2.10
--------------
 - Sweet Spot field

0.2.0
------
 - Network options pages
 - ESNext
 - Upgrade plugin Boilerplate
 - add tests skeleton
 - allow conditional logic for WP-Object-Fields
 - Several bugfixes

0.1.16
------
 - Repeater-Choice: improve color select style
 - Introduce Gulp
 - Fix Issue in Field Group admin: acf noted changes but nothing changed

0.1.15
------
 - Fix function call

0.1.14
------
 - Fix messed-up fields after save
 - Fix uninstall fatal

0.1.13
------
 - Repeater Select: style improvements

0.1.12
------
 - Introduce Repeater Select

0.1.9
-----
 - Fix PHP warning

0.1.8
-----
 - Change NS dir case

0.1.6 + 0.1.7
-------------
- fix pt location rule not matching

0.1.5
-----
 - Post type location rule broken in acf/ajax/check_screen request

0.1.4
-----
 - fix php fatal in acf-customizer compat class (again)

0.1.3
-----
 - fix php fatal in acf-customizer compat class

0.1.2
-----
 - fix php fatal in plugin component class (again)

0.1.1
-----
 - fix php fatal in plugin component class

0.1.0
-----
 - Introduce changelog
 - Introduce location rules for Post Type and Taxonomy
