<?php

namespace WP_Plugins\Boilerplate;

use RuntimeException;

/**
 * Plugin main component
 *
 * @package WP_Plugins\Boilerplate
 */
class Plugin extends Singular {

	/**
	 * Plugin version
	 *
	 * @var string
	 */
	public const VERSION = '1.0.0';

	/**
	 * @var string[]
	 */
	protected array $components_classes = [
		Ajax_Handler::class,
		Frontend::class,
		Backend::class,
	];

	/** @var Component[] */
	protected array $loaded_components = [];


	/**
	 * Initialization
	 *
	 * @return void
	 */
	protected function init() : void {

		// load language files
		add_action( 'plugins_loaded', [ $this, 'load_language' ] );

		$this->load_components();

		// plugin loaded hook
		do_action_ref_array( 'mbyes_loaded', [ $this ] );
	}


	/**
	 * Load plugin components
	 *
	 * @return void
	 */
	protected function load_components() : void {

		/** @var Component $componentClass */
		foreach ( $this->components_classes as $componentClass ) {
			if ( ! method_exists( $componentClass, 'get_instance' ) ) {
				throw new RuntimeException( "$componentClass does not have instance method." );
			}

			$this->loaded_components[ $componentClass ] = $componentClass::get_instance();
		}
	}


	/**
	 * Load view template
	 *
	 * @param string $view_name
	 * @param array|null $args ( optional )
	 *
	 * @return void
	 */
	public function load_view( string $view_name, array $args = null ) : void {

		// build view file path
		$__view_name     = $view_name;
		$__template_path = WPPB_DIR . 'views/' . $__view_name . '.php';
		if ( ! file_exists( $__template_path ) ) {

			// file not found!
			wp_die( sprintf( __( 'Template <code>%s</code> File not found, calculated path: <code>%s</code>', WPPB_DOMAIN ), $__view_name, $__template_path ) );

		}

		if ( ! empty( $args ) ) {

			// extract passed args into variables
			extract( $args, EXTR_OVERWRITE );

		}

		/**
		 * Before loading template hook
		 *
		 * @param string $__template_path
		 * @param string $__view_name
		 */
		do_action_ref_array( 'wppb_load_template_before', [ &$__template_path, $__view_name, $args ] );

		/**
		 * Loading template file path filter
		 *
		 * @param string $__template_path
		 * @param string $__view_name
		 *
		 * @return string
		 */
		require apply_filters( 'wppb_load_template_path', $__template_path, $__view_name, $args );

		/**
		 * After loading template hook
		 *
		 * @param string $__template_path
		 * @param string $__view_name
		 */
		do_action( 'wppb_load_template_after', $__template_path, $__view_name, $args );

	}


	/**
	 * Language file loading
	 *
	 * @return void
	 */
	public function load_language() : void {

		load_plugin_textdomain( WPPB_DOMAIN, false, dirname( plugin_basename( WPPB_MAIN_FILE ) ) . '/languages' );

	}


}
