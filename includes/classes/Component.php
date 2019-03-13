<?php namespace WP_Plugins\Boilerplate;

/**
 * Base Component
 *
 * @package WP_Plugins\Boilerplate
 */
class Component extends Singular {

	/**
	 * Plugin Main Component
	 *
	 * @var Plugin
	 */
	protected $plugin;

	/**
	 * Constructor
	 *
	 * @return void
	 */
	protected function init() {

		// vars
		$this->plugin = Plugin::get_instance();

	}
}
