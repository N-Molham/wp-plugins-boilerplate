<?php namespace WP_Plugins\Boilerplate;

/**
 * Class Helpers
 *
 * @since 1.0
 *
 * @package WP_Plugins\Boilerplate
 */
final class Helpers {

	/**
	 * Text Domain
	 *
	 * @var string
	 */
	public static $text_domain = WPPB_DOMAIN;

	/**
	 * Enqueue path
	 *
	 * @var string
	 */
	private static $enqueue_path;

	/**
	 * Enqueue assets version
	 *
	 * @var string
	 */
	private static $assets_version;

	/**
	 * Get Assets enqueue base path
	 *
	 * @return string
	 */
	public static function enqueue_path() {

		if ( null === self::$enqueue_path ) {

			self::$enqueue_path = sprintf( '%s/assets/%s/', untrailingslashit( WPPB_URI ), self::is_script_debugging() ? 'src' : 'dist' );

		}

		return self::$enqueue_path;
	}

	/**
	 * Get the current assets version
	 *
	 * @return string
	 */
	public static function assets_version() {

		if ( null === self::$assets_version ) {
			// assets version file
			$version_file = WPPB_DIR . 'assets/last_update';

			// read from file
			self::$assets_version = file_exists( $version_file ) && is_readable( $version_file ) ? sanitize_key( file_get_contents( $version_file ) ) : null;
			if ( empty( self::$assets_version ) ) {
				// fallback to plugin version
				self::$assets_version = wppb_version();
			}
		}

		return self::$assets_version;
	}

	/**
	 * Check if the given URL is valid
	 *
	 * @param string $url
	 *
	 * @return bool
	 */
	public static function is_valid_url( $url ) {

		if ( 0 !== strpos( $url, 'http://' ) && 0 !== strpos( $url, 'https://' ) ) {
			// Must start with http:// or https://
			return false;
		}

		if ( ! filter_var( $url, FILTER_VALIDATE_URL ) ) {
			// Must pass validation
			return false;
		}

		return true;

	}

	/**
	 * Plugin Version
	 *
	 * @return string
	 */
	public static function plugin_version() {

		return Plugin::get_instance()->version;

	}

	/**
	 * Check if target plugin is active wrapper
	 *
	 * @param string $plugin_file
	 *
	 * @return bool
	 */
	public static function is_plugin_active( $plugin_file ) {

		if ( ! function_exists( 'is_plugin_active' ) ) {
			include_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		return is_plugin_active( $plugin_file );
	}

	/**
	 * Check if target plugin is inactive wrapper
	 *
	 * @param string $plugin_file
	 *
	 * @return bool
	 */
	public static function is_plugin_inactive( $plugin_file ) {

		if ( ! function_exists( 'is_plugin_inactive' ) ) {
			include_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		return is_plugin_inactive( $plugin_file );
	}

	/**
	 * Sanitizes a hex color.
	 *
	 * Returns either '', a 3 or 6 digit hex color (with #), or null.
	 * For sanitizing values without a #, see self::sanitize_hex_color_no_hash().
	 *
	 * @since 1.0
	 *
	 * @param string $color
	 *
	 * @return string|null
	 */
	public static function sanitize_hex_color( $color ) {

		if ( '' === $color ) {
			return '';
		}

		// 3 or 6 hex digits, or the empty string.
		if ( preg_match( '|^#([A-Fa-f0-9]{3}){1,2}$|', $color ) ) {
			return $color;
		}

		return null;
	}

	/**
	 * Sanitizes a hex color without a hash. Use sanitize_hex_color() when possible.
	 *
	 * Saving hex colors without a hash puts the burden of adding the hash on the
	 * UI, which makes it difficult to use or upgrade to other color types such as
	 * rgba, hsl, rgb, and html color names.
	 *
	 * Returns either '', a 3 or 6 digit hex color (without a #), or null.
	 *
	 * @since 1.0
	 * @uses self::sanitize_hex_color()
	 *
	 * @param string $color
	 *
	 * @return string|null
	 */
	public static function sanitize_hex_color_no_hash( $color ) {

		$color = ltrim( $color, '#' );

		if ( '' === $color ) {
			return '';
		}

		return sanitize_hex_color( '#' . $color ) ? $color : null;

	}

	/**
	 * Current visitor/session IP address
	 *
	 * @since 1.0
	 * @return string
	 */
	public static function get_visitor_ip() {

		$client  = $_SERVER['HTTP_CLIENT_IP'] ?? null;
		$forward = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? null;

		if ( $client && filter_var( $client, FILTER_VALIDATE_IP ) ) {

			return $client;

		}

		if ( $client && filter_var( $forward, FILTER_VALIDATE_IP ) ) {

			return $forward;

		}

		return $_SERVER['REMOTE_ADDR'];

	}

	/**
	 * Determine scripts and styles enqueues suffix
	 *
	 * @since 1.0
	 * @return string
	 */
	public static function enqueue_suffix() {

		return self::is_script_debugging() ? '' : '.min';

	}

	/**
	 * Check whether script debugging enable or not
	 *
	 * @return bool
	 */
	public static function is_script_debugging() {

		return defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG;

	}

	/**
	 * URL Redirect
	 *
	 * @param string $target
	 * @param int    $status
	 *
	 * @return void
	 */
	public static function redirect( $target = '', $status = 302 ) {

		if ( '' === $target && isset( $_REQUEST['_wp_http_referer'] ) ) {
			$target = esc_url( $_REQUEST['_wp_http_referer'] );
		}

		wp_redirect( $target, $status );
		die();

	}

	/**
	 * Modified version of sanitize_text_field with line-breaks preserved
	 *
	 * @see sanitize_text_field
	 * @since 2.9.0
	 *
	 * @param string $str
	 *
	 * @return string
	 */
	public static function sanitize_text_field_with_linebreaks( $str ) {

		$filtered = wp_check_invalid_utf8( $str );

		if ( strpos( $filtered, '<' ) !== false ) {
			$filtered = wp_pre_kses_less_than( $filtered );

			// This will strip extra whitespace for us.
			$filtered = wp_strip_all_tags( $filtered, true );
		}

		$found = false;
		while ( preg_match( '/%[a-f0-9]{2}/i', $filtered, $match ) ) {
			$filtered = str_replace( $match[0], '', $filtered );
			$found    = true;
		}

		if ( $found ) {
			// Strip out the whitespace that may now exist after removing the octets.
			$filtered = trim( preg_replace( '/ +/', ' ', $filtered ) );
		}

		/**
		 * Filter a sanitized text field string.
		 *
		 * @since 2.9.0
		 *
		 * @param string $filtered The sanitized string.
		 * @param string $str The string prior to being sanitized.
		 */
		return apply_filters( 'sanitize_text_field_with_linebreaks', $filtered, $str );

	}

	/**
	 * Parse/Join html attributes
	 *
	 * @param array $html_attributes
	 *
	 * @return string
	 */
	public static function parse_attributes( $html_attributes ) {

		if ( empty( $html_attributes ) ) {

			return '';

		}

		$html_attributes = array_map( function ( $item, $key ) {

			return $key . '="' . esc_attr( is_array( $item ) ? implode( ' ', $item ) : $item ) . '"';

		}, array_values( $html_attributes ), array_keys( $html_attributes ) );

		return implode( ' ', $html_attributes );

	}
}