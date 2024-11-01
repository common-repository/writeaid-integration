<?php

/**
 * Fired during plugin deactivation
 *
 * @link       https://writeaid.net
 * @since      1.0.0
 *
 * @package    Writeaid_Integration
 * @subpackage Writeaid_Integration/includes
 */

/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @since      1.0.0
 * @package    Writeaid_Integration
 * @subpackage Writeaid_Integration/includes
 * @author     WriteAid <info@writeaid.net>
 */
class Writeaid_Integration_Deactivator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public function deactivate() {
		global $wp;

		$plugin_name = 'writeaid-integration';
		$api_url     = 'https://my.writeaid.net/api/wp/deactivate';

		$options = get_option( $plugin_name );

		$domain = home_url( $wp->request );
		$domain = wp_parse_url( $domain )['host'];

		$request_params = array(
			'action'     => 'deactivate',
			'domain'     => $domain,
			'auth_token' => 'ez5Y42xmzi8ypZuCR521vngJrQLmzmVhV8cSGKkXn8meI94t8FcVHi48nJz',
			'title'      => get_bloginfo( 'name' ),
			'url'        => get_bloginfo( 'url' ),
			'options'    => $options,
		);

		$request = array(
			'timeout' => 45,
			'body'    => $request_params,
		);

		$response = wp_remote_post( $api_url, $request );

	}

}
