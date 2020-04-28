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
	function wp_plugin_boilerplate(): Plugin {

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
	function wppb_component( $component_name ): ?Component {

		return wp_plugin_boilerplate()->$component_name ?? null;

	}

endif;

if ( ! function_exists( 'wppb_view' ) ):

	/**
	 * Render view with option to return it instead
	 *
	 * @param string $view_name
	 * @param null   $args
	 * @param bool   $return
	 *
	 * @return string|null
	 */
	function wppb_view( $view_name, $args = null, $return = false ): ?string {

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
	function wppb_version(): string {

		return wp_plugin_boilerplate()->version;

	}
endif;