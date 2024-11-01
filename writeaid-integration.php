<?php
/**
 * WriteAid.net Integration
 *
 * @link              https://writeaid.net
 * @since             1.0.0
 * @package           Writeaid_Integration
 *
 * @wordpress-plugin
 * Plugin Name:       WriteAid Integration
 * Plugin URI:        https://writeaid.net/wordpress-integration
 * Description:       API integration with your WriteAid.net account. Allows remote content publishing directly from WriteAid.
 * Version:           1.0.4
 * Author:            WriteAid
 * Author URI:        https://writeaid.net
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       writeaid-integration
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'WRITEAID_INTEGRATION_VERSION', '1.0.4' );
define( 'WRITEAID__PLUGIN_DIR', plugin_dir_path( __FILE__ ) );

/**
 * Current API version.
 * Starts at version 1.0.0
 * Rename this constant if we change anything about what the api accepts
 */
define( 'WRITEAID_API_VERSION', '1.0.0' );


/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-writeaid-integration-activator.php
 */
function activate_writeaid_integration() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-writeaid-integration-activator.php';
	WriteAid_Integration_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-writeaid-integration-deactivator.php
 */
function deactivate_writeaid_integration() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-writeaid-integration-deactivator.php';
	WriteAid_Integration_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_writeaid_integration' );
register_deactivation_hook( __FILE__, 'deactivate_writeaid_integration' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-writeaid-integration.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_writeaid_integration() {

	$writeaid_integration = new WriteAid_Integration();
	$writeaid_integration->run();

}

run_writeaid_integration();
