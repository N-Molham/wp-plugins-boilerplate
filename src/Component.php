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
	protected Plugin $plugin;


	/**
	 * Constructor
	 *
	 * @return void
	 */
	protected function init() : void {

		// vars
		$this->plugin = Plugin::get_instance();
	}
}
