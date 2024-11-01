<?php

/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       https://writeaid.net
 * @since      1.0.0
 *
 * @package    Writeaid_Integration
 * @subpackage Writeaid_Integration/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    Writeaid_Integration
 * @subpackage Writeaid_Integration/includes
 * @author     WriteAid <info@writeaid.net>
 */
class Writeaid_Integration_I18n {


	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {

		load_plugin_textdomain(
			'writeaid-integration',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);

	}

}
