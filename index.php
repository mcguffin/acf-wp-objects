<?php

/*
Plugin Name: ACF WP-Objects
Plugin URI: https://github.com/mcguffin/acf-wp-objects.git
Description: Connecting WordPress Data with ACF Fields
Author: Jörn Lund
Version: 0.4.16
Author URI: https://github.com/mcguffin
License: GPL3
GitHub Plugin URI: mcguffin/acf-wp-objects
Requires WP: 4.8
Requires PHP: 5.6
Text Domain: acf-wp-objects
Domain Path: /languages/
*/

/*  Copyright 2018 Jörn Lund

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

/*
Plugin was generated with Jörn Lund's WP Skelton
https://github.com/mcguffin/wp-skeleton
*/


namespace ACFWPObjects;

if ( ! defined('ABSPATH') ) {
	die('FU!');
}


require_once __DIR__ . DIRECTORY_SEPARATOR . 'include/autoload.php';

Core\Core::instance( __FILE__ );
