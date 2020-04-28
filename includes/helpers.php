<?php namespace WP_Plugins\Boilerplate;

use WP_Error;

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
	 * Check if password is pwned by the API
	 *
	 * @see https://haveibeenpwned.com/API/v2#PwnedPasswords
	 *
	 * @param string $password_hash the password hashed using SHA-1
	 *
	 * @return false|int|WP_Error int if a match found, WP_Error on error, otherwise false
	 */
	public static function is_password_pwned( $password_hash ) {

		// extract matching parts
		$password_hash = strtoupper( $password_hash );
		$hash_prefix   = substr( $password_hash, 0, 5 );
		$hash_suffix   = substr( $password_hash, 5 );

		// make the HTTPS request
		$response      = wp_remote_get( 'https://api.pwnedpasswords.com/range/' . $hash_prefix );
		$response_code = wp_remote_retrieve_response_code( $response );

		// check if results came back successfully
		if ( 200 !== $response_code ) {

			return new WP_Error( 'wppb_pwned_check_error', $response_code . ': ' . wp_remote_retrieve_body( $response ) );

		}

		// find a match
		$results   = wp_remote_retrieve_body( $response );
		$match_pos = strpos( $results, $hash_suffix );

		// good news, nothing found
		if ( false === $match_pos ) {

			return false;

		}

		// get recurrence position
		$suffix_end_pos = $match_pos + 36;
		$line_break_pos = strpos( $results, "\n", $match_pos );

		return (int) trim( substr( $results, $suffix_end_pos, $line_break_pos - $suffix_end_pos ) );

	}

	/**
	 * Get Assets enqueue base path
	 *
	 * @return string
	 */
	public static function enqueue_path(): string {

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
	public static function assets_version(): ?string {

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
	public static function is_valid_url( $url ): bool {

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
	public static function plugin_version(): string {

		return wppb_version();

	}

	/**
	 * Check if target plugin is active wrapper
	 *
	 * @param string $plugin_file
	 *
	 * @return bool
	 */
	public static function is_plugin_active( $plugin_file ): bool {

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
	public static function is_plugin_inactive( $plugin_file ): bool {

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
	 * @param string $color
	 *
	 * @since 1.0
	 *
	 * @return string|null
	 */
	public static function sanitize_hex_color( $color ): ?string {

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
	 * @param string $color
	 *
	 * @since 1.0
	 * @return string|null
	 * @uses self::sanitize_hex_color()
	 *
	 */
	public static function sanitize_hex_color_no_hash( $color ): ?string {

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
	public static function get_visitor_ip(): ?string {

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
	public static function enqueue_suffix(): string {

		return self::is_script_debugging() ? '' : '.min';

	}

	/**
	 * Check whether script debugging enable or not
	 *
	 * @return bool
	 */
	public static function is_script_debugging(): bool {

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
	public static function redirect( $target = '', $status = 302 ): void {

		if ( '' === $target && isset( $_REQUEST['_wp_http_referer'] ) ) {
			
			$target = esc_url( $_REQUEST['_wp_http_referer'] );
			
		}

		wp_redirect( $target, $status );
		exit();

	}

	/**
	 * Modified version of sanitize_text_field with line-breaks preserved
	 *
	 * @param string $str
	 *
	 * @deprecated
	 *
	 * @return string
	 * @see sanitize_text_field
	 */
	public static function sanitize_text_field_with_linebreaks( $str ): string {

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
		 * @param string $filtered The sanitized string.
		 * @param string $str The string prior to being sanitized.
		 *
		 * @since 2.9.0
		 *
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
	public static function parse_attributes( $html_attributes ): string {

		if ( empty( $html_attributes ) ) {

			return '';

		}

		$html_attributes = array_map( static function ( $item, $key ) {

			return $key . '="' . esc_attr( is_array( $item ) ? implode( ' ', $item ) : $item ) . '"';

		}, array_values( $html_attributes ), array_keys( $html_attributes ) );

		return implode( ' ', $html_attributes );

	}
}