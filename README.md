ACF-WP-Objects
===============

Features
--------
Integrate WordPress Objects objects into ACF.
 - **Edit WP-Post properties** with ACF fields:
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
 - New **Field types**:
    - Post Type
    - Taxonomy
    - Image Size
    - User Role
    - Sweet Spot (use with media)
    - Plugin Tempalte Select (Selector for template file from plugin)
 - New **Location Rules**:
    - Post Type / Taxonomy is public / builtin / show_ui / show_in_menu / show_in_nav_menus
    - Editor is Classic / Block editor
    - WP Options page is General / Writing / Reading / Discussion / Media / Permalinks
    - Plugin Template Settings
 - **Choice fields**: get choices from a repeater, e.g. from an ACF options page
 - **Multisite**: 
   - Add a network admin page with `acf_add_options_(sub_)page()`. Add `"network" => true,` to the page args (ACF Pro only – both plugins must be network-enabled)
 - **Styling**:
   - More compact styles in block editor sidebar
   - Add classes `no-head` and `no-sort` to repeater
 - **Page Layouts**: Generic flexible content field providing a location rule for field groups. 
 - **JSON-Paths**: Save Field group JSON in custom places
 - **Localization**: Localize ACF Field labels through po-files


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


JSON-Paths
----------
Consider the follwing Scenario: You are using local json field groups in your theme. You want to override them in a child theme. Or alternatively, you have a plugin with an ACF dependency, incorporating field groups as local json.

This will load and save ACF JSON from the subdirectory `path/to/json-files` inside the theme and child theme directory but only if the field group key is `group_my_fieldgroup_key`.

```php
acf_register_local_json(
	'path/to/json-files', // e.g. 'acf-json' in a theme
	function( $field_group ) { 
		// callback which should return true, if the field group 
		// JSON should be saved at the given location
		return $field_group['key'] === 'group_my_fieldgroup_key';
	},
	[ // parent paths to check
		get_template_directory(),
		get_stylesheet_directory(),
	]
);
```

JSON I18n
---------
ACF provides support for WPML to localize Field groups. 
ACF WP Objects offers a different approach through `.po` files.

```php
acf_localize_field_groups( 
	'my-textdomain', 
	function( $field_group ) { 
		// callback which should return true, if the field group 
		// localization is available under the given textdomain
		return $field_group['key'] === 'group_my_fieldgroup_key';
	});
```

If you are using local json, here is a node script allowing you to extract the strings [`src/run/json-i18n.js`](src/run/json-i18n.js) and add them to a pot file:

Install [WP CLI](https://wp-cli.org/).

Place `src/run/json-i18n.js` and `src/run/lib/json-extract.js` in your package directory.

Extract strings from json files and add them to a PHP file:
```shell
node ./src/run/json-i18n.js 'my-texdomain' ./path/to/json ./php-output.php
```

Generate pot with WP CLI:
```shell
wp i18n make-pot . languages/my-textdomain.pot --domain=my-textdomain
```


Template Files (ACF Pro only)
-----------------------------

1. Filter template types
```php
add_filter('acf_wp_objects_template_types', function( $types ) {
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

Page layouts (ACF Pro only)
---------------------------

Generate a flexible content field and turn field groups to Layouts.
Ideal if you need an extendible Set of Layouts to choose from.

1. Add a layout section:
	```php
	acf_add_page_layout([
		'title'	=> 'My Layout',
		'name'	=> 'my-layout',
	]);
	```
2. Create field groups. Set "Page Layouts" "equals" "My Layout" as a location, and enter a row layout slug at the very bottom.
3. Create template files in your theme corresponding to the slugs chosen above. Filenames should match `acf/layout-<row_layout_slug>.php`. Don't forget to use `get_sub_field()`, you are inside a flexible content field!
4. In your page.php template file call this inside the loop:
	```php
	acf_page_layouts( 'my-layouts' );
	```
