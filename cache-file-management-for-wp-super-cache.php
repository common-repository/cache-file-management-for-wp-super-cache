<?php
/*
 * Plugin Name: Cache file management for WP Super Cache
 * Version: 1.3
 * Plugin URI: https://wordpress.org/plugins/cache-file-management-for-wp-super-cache/
 * Description: This plugin helps Webmaster to manage cache files of WP Super Cache. Delete a cache file or a cache folder.
 * Author: For Games
 * Author URI: https://www.sockscap64.com/wordpress-plguin-cache-file-management-for-wp-super-cache/
 * Requires at least: 4.0
 * Tested up to: 5.0.3
 *
 * Text Domain: cache-file-management-for-wp-super-cache
 * Domain Path: /lang/
 *
 * @package WordPress
 * @author 4Games
 * @since 1.0.0
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */
 
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Detect plugin. 
 */
include_once( ABSPATH . 'wp-admin/includes/plugin.php' );

// Load plugin class files
require_once( 'includes/cfm-lib-tfm.php' );
require_once( 'includes/cache-file-management-for-wp-super-cache.php' );

/**
 * Returns the main instance of CahceFileManagement_for_WPSuperCache to prevent the need to use globals.
 *
 * @since  1.0.0
 * @return object CahceFileManagement_for_WPSuperCache
 */
function CahceFileManagement_for_WPSuperCache () {
	$instance = CahceFileManagement_for_WPSuperCache::instance( __FILE__, '1.0.0' );

	return $instance;
}

CahceFileManagement_for_WPSuperCache();