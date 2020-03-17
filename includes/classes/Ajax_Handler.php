<?php namespace WP_Plugins\Boilerplate;

/**
 * AJAX handler
 *
 * @package WP_Plugins\Boilerplate
 */
class Ajax_Handler extends Component {

	/**
	 * @var array
	 */
	protected $_nopriv_actions;

	/**
	 * Constructor
	 *
	 * @return void
	 */
	protected function init(): void {

		parent::init();

		if ( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX ) {

			return;

		}

		$ajax_action = filter_var( $_REQUEST['action'] ?? '', FILTER_SANITIZE_STRING );

		if ( 0 !== strpos( $ajax_action, 'wppb_' ) ) {

			return;

		}

		$this->_nopriv_actions = [];

		$action_callback = str_replace( 'wppb_', '', $ajax_action );

		if ( method_exists( $this, $action_callback ) ) {

			// hook into action if it's method exists
			add_action( 'wp_ajax_' . $ajax_action, [ $this, $action_callback ] );

			if ( in_array( $action_callback, $this->_nopriv_actions, true ) ) {

				add_action( 'wp_ajax_nopriv_' . $ajax_action, [ $this, $action_callback ] );

			}

		}

	}

	/**
	 * AJAX Debug response
	 *
	 * @param mixed $data
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function debug( $data ): void {

		// return dump
		$this->error( $data );
	}

	/**
	 * AJAX Debug response ( dump )
	 *
	 * @param mixed $args
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function dump( $args ): void {

		// return dump
		$this->error( print_r( func_num_args() === 1 ? $args : func_get_args(), true ) );

	}

	/**
	 * AJAX Error response
	 *
	 * @param mixed $data
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function error( $data ): void {

		wp_send_json_error( $data );

	}

	/**
	 * AJAX success response
	 *
	 * @param mixed $data
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function success( $data ): void {

		wp_send_json_success( $data );

	}

	/**
	 * AJAX JSON Response
	 *
	 * @param mixed $response
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function response( $response ): void {

		// send response
		wp_send_json( $response );

	}
}
