ACF-WP-Objects
===============

Features
--------
Integrate WordPress Objects objects into ACF.
 - Use ACF Fields to edit several WP-Post properties:
   - Settings: blogname
   - Settings: blogdescription
   - Post: post_title
   - Post: post_excerpt
   - Post: post_content
   - Post: post_thumbnail
   - Post: attachments
   - Term: term_name (Not implemented yet)
   - Term: term_description (Not implemented yet)
   - Theme-Mod: custom_logo
 - New Field types:
    - Post Type
    - Taxonomy
    - Image Size
 - New Location Rules:
    - Post Type / Taxonomy is public / builtin / show_ui / show_in_menu / show_in_nav_menus
 - Choice fields: get choices from a repeater
 - Multisite: 
   - Add a network admin page with `acf_add_options_(sub_)page()`. Add `"network" => true,` to the page args (ACF Pro only – both plugins must be network-enabled)


Installation
------------

### Production (Stand-Alone)
 - Head over to [releases](../../releases)
 - Download 'acf-wp-objects.zip'
 - Upload and activate it like any other WordPress plugin
 - AutoUpdate will run as long as the plugin is active

### Production (using Github Updater – recommended for Multisite)
 - Install [Andy Fragen's GitHub Updater](https://github.com/afragen/github-updater) first.
 - In WP Admin go to Settings / GitHub Updater / Install Plugin. Enter `mcguffin/acf-wp-objects` as a Plugin-URI.

### Development
 - cd into your plugin directory
 - $ `git clone git@github.com:mcguffin/acf-wp-objects.git`
 - $ `cd acf-wp-objects`
 - $ `npm install`
 - $ `npm run dev`




ToDo:
-----
Features:
 - [ ] Add Field: Select User-Role
 - [ ] Add Field: Select Plugin (active, inactive, network-activated, ...)
 - [ ] Add Field: Select Theme (all, activatable, childs, parents, ...)
 - [ ] Add Field Option: readonly
 - [ ] Add Connector: Implement Term title, description
 - [ ] Add Connector: User Properties like email, nicename, avatar, ...
 - [ ] Add Connector: Connect more Options like media sizes, crop thumbnails, ...
 - [x] Add Location Rule: Network admin page
 - [ ] Add Location Rule: current_user_can ( capability )
 - [x] Improve: use PT & Tax Select for conditional logic
 - [x] Dev: ESNext
 - [x] Dev: Boilerplate 2
 - [ ] Dev: Add Tests
   - [ ] Register Test PT, Options page, ...
   - [ ] Fieldgroup with WP-Values
   - [ ] Fieldgroup with Repeater-Choices
 - [x] Fix: Repeater Choice: dont show row return foramt on Taxonomies
 - [ ] Fix: WSOD Compat\ACF\ACF::instance() after upgrade if ACF inactive
 - [x] Fix: JS Select with UI doesn't init
