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
   - Term: term_name
   - Term: term_description
   - Theme-Mod: custom_logo
 - New Field types:
    - Post Type
    - Taxonomy
    - Image Size
    - User Role
    - Sweet Spot (use with media)
    - Plugin Tempalte Select (Selector for template file from plugin)
 - New Location Rules:
    - Post Type / Taxonomy is public / builtin / show_ui / show_in_menu / show_in_nav_menus
    - Editor is Classic / Block editor
    - WP Options page is general / Writing / Reading / Discussion / Media / Permalinks
    - Plugin Template Settings
 - Choice fields: get choices from a repeater, e.g. from an ACF options page
 - Multisite: 
   - Add a network admin page with `acf_add_options_(sub_)page()`. Add `"network" => true,` to the page args (ACF Pro only – both plugins must be network-enabled)
 - Styling:
   - More compact styles in block editor sidebar
   - Add classes `no-head` and `no-sort` to repeater


Installation
------------

### Production (using Github Updater – recommended for Multisite)
 - Install [Andy Fragen's GitHub Updater](https://github.com/afragen/github-updater) first.
 - In WP Admin go to Settings / GitHub Updater / Install Plugin. Enter `mcguffin/acf-wp-objects` as a Plugin-URI.

### Development
 - cd into your plugin directory
 - $ `git clone git@github.com:mcguffin/acf-wp-objects.git`
 - $ `cd acf-wp-objects`
 - $ `npm install`
 - $ `npm run dev`


Template Files
--------------

1. Filter template types
```php
add_filter('acf_wp_objects_template_types', function($types){
	$slug = 'foo-plugin';
	$key = 'Items Template';
	$theme_location = 'foo-plugin';
		// will point to wp-content/themes/<current-theme>/foo-plugin/
		// default: $slug
	$plugin_location = 'templates';
		// will point to wp-content/plugins/foo-plugin/templates/
		// null: use default, false: no plugin location, string: custom location inside plugin

	$types[ $slug ] = [
		'header_key' => $key,
		'theme_location' => $theme_location,
		'plugin_location' => $plugin_location,
	];
	return $types;
});
```

WP Objects will scan for template files having a header key in theme and plugin locations.

2. Create a Template select field with name `my_fabulous_template`. 
   Use it like this: `get_template_part( get_field('my_fabulous_template') );`
3. Place some template files in location
   ```php
   <?php
   /*
   Items Template: List
   */
   $settings = get_field('my_fabulous_template_settings');
   ```



ToDo:
-----
Features:
 - [x] Add Field: Select User-Role
 - [ ] Add Field: Select Plugin (active, inactive, network-activated, ...)
 - [ ] Add Field: Select Theme (all, activatable, childs, parents, ...)
 - [ ] Add Field: Select Page Template
 - [ ] Add Field Option: readonly, disabled
 - [ ] Add Connectors
   - [ ] User:
     - [ ] Email
     - [ ] Avatar
     - [ ] First name, last name
     - [ ] Role
   - [ ] Post
     - [ ] menu_order
 - [ ] Add Connector: More Options
   - [ ] Crop Thumbnails
   - [ ] Permalink structure
 - [ ] Location Rules
   - [x] Is Classic/Block Editor (depend on classic editor)
   - [ ] Content Type is Post / Taxonomy / User / Widget
   - [x] WP-Options page is ... reading, writing 
 - [ ] Add Hiding Options: term title, term description, ...
   - [ ] Term: Title, Description, Slug
   - [ ] User: Editor settings, Name, About
 - [ ] Dev: Add Tests
   - [ ] Cross-Compat with Customizer and RGBA-Color-Picker
   - [ ] Test with acf free
 - [ ] Fix: Connector: handle new post autodraft title
 - [ ] Install: submit to packagist, add composer description
 - [x] Style Fields in Block-Editor sidebar
