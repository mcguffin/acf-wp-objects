ACF-WP-Objects
===============

Features
--------
Integrate WordPress Objects objects into ACF.

 - Add options to several ACF Fields allowing to edit the Post Title and Content, Site Title, Description and Logo, ...
 - Select fields for Post Types and Taxonomies
 - Location Rules for Post Type and Taxonomy properties (Like Builtin, Public, ...)



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
 - $ `gulp`




ToDo:
-----
 - [ ] Implement Term title, description
 - [ ] Select User-Role
 - [ ] Select Plugin
 - [ ] Select Theme
 - [ ] Connect more Options like media sizes, crop thumbnails, ...
 - [ ] User Properties
