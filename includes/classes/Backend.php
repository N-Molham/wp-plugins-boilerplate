<?php namespace WP_Plugins\Boilerplate;

/**
 * Backend logic
 *
 * @package WP_Plugins\Boilerplate
 */
class Backend extends Component {

	/**
	 * @var array
	 */
	protected $_admin_notices = [];

	/**
	 * Constructor
	 *
	 * @return void
	 */
	protected function init(): void {

		parent::init();

		add_action( 'admin_notices', [ $this, 'render_admin_notices' ], 20 );

	}

	/**
	 * @return void
	 */
	public function render_admin_notices(): void {

		$admin_notices = $this->get_admin_notices();

		if ( 0 === count( $admin_notices ) ) {

			return;

		}

		foreach ( $admin_notices as $notice_index => $notice ) {

			$message_id     = WPPB_DOMAIN . '-notice-';
			$notice_classes = [ 'notice', 'notice-' . WPPB_DOMAIN, 'notice-' . $notice['type'] ];

			if ( $notice['is_dismissible'] ) {

				$notice_classes[] = 'is-dismissible';

			}

			$message_id .= $notice['message_id'] ? : $notice_index;

			echo '<div id="', esc_attr( $message_id ), '" class="', esc_attr( implode( ' ', $notice_classes ) ), '"><p>', $notice['message'], '</p></div>';

		}

	}

	/**
	 * @param string $message
	 * @param string $type "message" || "success" || "warning" || "error"
	 * @param bool   $is_dismissible
	 * @param string $message_id
	 */
	public function add_admin_notice( $message, $type = 'message', $is_dismissible = false, $message_id = '' ): void {

		$this->_admin_notices[] = compact( 'message', 'type', 'is_dismissible', 'message_id' );

	}

	/**
	 * @return array
	 */
	public function get_admin_notices(): array {

		return (array) apply_filters( 'wppb_admin_notices', $this->_admin_notices );

	}

}
