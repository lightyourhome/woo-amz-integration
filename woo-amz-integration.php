<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://lightyourhome.com
 * @since             1.0.0
 * @package           Woo_Amz_Integration
 *
 * @wordpress-plugin
 * Plugin Name:       WooCommerce Amazon Integration
 * Plugin URI:        https://lightyourhome.com
 * Description:       This is a short description of what the plugin does. It's displayed in the WordPress admin area.
 * Version:           1.0.0
 * Author:            Jim Merk
 * Author URI:        https://lightyourhome.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       woo-amz-integration
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
define( 'WOO_AMZ_INTEGRATION_VERSION', '1.0.0' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-woo-amz-integration-activator.php
 */
function activate_woo_amz_integration() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-woo-amz-integration-activator.php';
	Woo_Amz_Integration_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-woo-amz-integration-deactivator.php
 */
function deactivate_woo_amz_integration() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-woo-amz-integration-deactivator.php';
	Woo_Amz_Integration_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_woo_amz_integration' );
register_deactivation_hook( __FILE__, 'deactivate_woo_amz_integration' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-woo-amz-integration.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_woo_amz_integration() {

	$plugin = new Woo_Amz_Integration();
	$plugin->run();

}
run_woo_amz_integration();
