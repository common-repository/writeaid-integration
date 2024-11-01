<?php

/**
 * Fired when the plugin is uninstalled.
 *
 * @link       https://writeaid.net
 * @since      1.0.0
 *
 * @package    WriteAid_Integration
 */

// if uninstall.php is not called by WordPress, die!
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	die;
}

$plugin_name = 'writeaid-integration';

delete_option( $plugin_name );
delete_option( $plugin_name . '-response' );

// for site options in Multisite.
delete_site_option( $plugin_name );
delete_site_option( $plugin_name . '-response' );
