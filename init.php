<?php
/**
 * @noinspection PhpIncludeInspection
 */

namespace WP_Plugins\Boilerplate;

/**
 * Plugin Name: WP Plugins Boilerplate
 * Description: Plugin Description
 * Version: 1.0.0
 * Author: Nabeel Molham
 * Author URI: http://nabeel.molham.me/
 * Text Domain: wp-plugin-domain
 * Domain Path: /languages
 * License: GNU General Public License, version 3, http://www.gnu.org/licenses/gpl-3.0.en.html
 */

if ( ! defined( 'WPINC' ) ) {
	// Exit if accessed directly
	die();
}

/**
 * Constants
 */

// plugin master file
if ( ! defined( 'WPPB_MAIN_FILE' ) ) {
	define( 'WPPB_MAIN_FILE', __FILE__ );
}

// plugin DIR
if ( ! defined( 'WPPB_DIR' ) ) {
	define( 'WPPB_DIR', plugin_dir_path( WPPB_MAIN_FILE ) );
}

// plugin URI
if ( ! defined( 'WPPB_URI' ) ) {
	define( 'WPPB_URI', plugin_dir_url( WPPB_MAIN_FILE ) );
}

// plugin views DIR
if ( ! defined( 'WPPB_VIEWS_DIR' ) ) {
	define( "WPPB_VIEWS_DIR", WPPB_DIR . 'views/' );
}


// localization text Domain
if ( ! defined( 'WPPB_DOMAIN' ) ) {
	define( 'WPPB_DOMAIN', 'wp-plugin-domain' );
}

add_action( 'plugins_loaded', static function () {

	// skip if plugin's main function already exists
	if ( function_exists( 'wp_plugin_boilerplate' ) ) {
		return;
	}

	// composer autoload
	require_once __DIR__ . '/vendor/autoload.php';

	// boot up the system
	wp_plugin_boilerplate();
}, PHP_INT_MAX );
