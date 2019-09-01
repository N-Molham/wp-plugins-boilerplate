<?php
/**
 * Created by Nabeel
 * Date: 2016-01-22
 * Time: 2:38 AM
 *
 * @package WP_Plugins\Boilerplate
 */

use WP_Plugins\Boilerplate\Component;
use WP_Plugins\Boilerplate\Plugin;

if ( ! function_exists( 'wp_plugin_boilerplate' ) ):
	/**
	 * Get plugin instance
	 *
	 * @return Plugin
	 */
	function wp_plugin_boilerplate() {

		return Plugin::get_instance();
		
	}
	
endif;

if ( ! function_exists( 'wppb_component' ) ):
	/**
	 * Get plugin component instance
	 *
	 * @param string $component_name
	 *
	 * @return Component|null
	 */
	function wppb_component( $component_name ) {

		if ( isset( wp_plugin_boilerplate()->$component_name ) ) {
			return wp_plugin_boilerplate()->$component_name;
		}

		return null;
	}
	
endif;

if ( ! function_exists( 'wppb_view' ) ):
	/**
	 * Load view
	 *
	 * @param string  $view_name
	 * @param array   $args
	 * @param boolean $return
	 *
	 * @return void
	 */
	function wppb_view( $view_name, $args = null, $return = false ) {

		if ( $return ) {
			// start buffer
			ob_start();
		}

		wp_plugin_boilerplate()->load_view( $view_name, $args );

		if ( $return ) {
			// get buffer flush
			return ob_get_clean();
		}
	}
	
endif;

if ( ! function_exists( 'wppb_version' ) ):
	/**
	 * Get plugin version
	 *
	 * @return string
	 */
	function wppb_version() {

		return wp_plugin_boilerplate()->version;
	}
endif;