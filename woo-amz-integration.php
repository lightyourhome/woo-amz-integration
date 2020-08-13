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
 * @since             0.1.0
 * @package           Woo_Amz_Integration
 *
 * @wordpress-plugin
 * Plugin Name:       WooCommerce Amazon Integration
 * Plugin URI:        https://lightyourhome.com
 * Description:       This is a short description of what the plugin does. It's displayed in the WordPress admin area.
 * Version:           0.11.0
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
define( 'WOO_AMZ_INTEGRATION_VERSION', '0.7.1' );

define('WOO_AMZ_PLUGIN_DIR', 	ABSPATH . 'wp-content/plugins/woo-amz-integration/');
define('WOO_AMZ_INV_FILE_PATH', ABSPATH . 'wp-content/uploads/amz_inventory.txt');
define('WOO_AMZ_RESPONSE_LOG',  ABSPATH . 'wp-content/uploads/amz_response_log');
define('WOO_AMZ_ERROR_LOG', 	ABSPATH . 'wp-content/uploads/amz_error_log.txt');
define('MWS_CONFIG',            plugin_dir_path( __FILE__ ) . 'includes/Config/.config.inc.php');

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

function tfs_create_custom_table() {

	global $wpdb;

	$charset_collate = $wpdb->get_charset_collate();

	$tbl_name = $wpdb->prefix . 'tfs_amz_int_data';

	$sql = "CREATE TABLE IF NOT EXISTS $tbl_name (
		id mediumint(9) NOT NULL,
		products_to_process mediumint(9) NOT NULL,
		current_page VARCHAR(100) NOT NULL,
		products_processed mediumint(9) NOT NULL,
		completed BOOLEAN,
		UNIQUE KEY id (id)
	) $charset_collate;";

	if ( ! function_exists('dbDelta') ) {

		require_once( ABSPATH . 'wp-admin/includes/upgrade,php' );

	}

	add_option( $wpdb->prefix . 'tfs_amz_int_data_version', '0.1.0' );

	dbDelta( $sql );

}

/**
 * Activation Hooks
 */
register_activation_hook( __FILE__, 'activate_woo_amz_integration' );
register_activation_hook( __FILE__, 'tfs_create_custom_table' );

/**
 * Deactivation Hooks
 */
register_deactivation_hook( __FILE__, 'deactivate_woo_amz_integration' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/core/class-tfs-dbman.php';


/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-woo-amz-integration.php';

/**
 * The class responsible for communicating with the woocommerce rest api
 */
require plugin_dir_path( __FILE__ ) . 'woo-rest-api.php';

/**
 * The class responsible for interacting with the Wordpress REST API
 */
require plugin_dir_path( __FILE__ ) . 'wp-rest-api.php';

/**
 * The class responsible for interacting with the Wordpress REST API
 */
require plugin_dir_path( __FILE__ ) . 'options.php';

/**
 * The class responsible for handling file creation
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-woo-amz-file-handler.php';

/**
 * MarketplaceWebService Class responsible for exception handling
 * 
 * @since 0.6.0
 */
require plugin_dir_path( __FILE__ ) . 'includes/MarketplaceWebService/Exception.php';

/**
 * MarketplaceWebService interface
 * 
 * @since 0.6.0
 */
require plugin_dir_path( __FILE__ ) . 'includes/MarketplaceWebService/Interface.php';

/**
 * Creates mock up xml files for MarketplaceWebService responses
 * 
 * @since 0.6.0
 */
require plugin_dir_path( __FILE__ ) . 'includes/MarketplaceWebService/Mock.php';

/**
 * Model class
 * 
 * @since 0.6.0
 */
require plugin_dir_path( __FILE__ ) . 'includes/MarketplaceWebService/Model.php';

/**
 * Class that handles the type of request being sent to MarketplaceWebService
 * 
 * @since 0.6.0
 */
require plugin_dir_path( __FILE__ ) . 'includes/MarketplaceWebService/RequestType.php';

/** TODO: DOCUMENT MODEL FILES */
/**
 * Class responsible for submitting inventory feed response from MWS
 * 
 * @since 0.6.0
 */
require plugin_dir_path( __FILE__ ) . 'includes/MarketplaceWebService/Model/SubmitFeedResponse.php';

require plugin_dir_path( __FILE__ ) . 'includes/MarketplaceWebService/Model/SubmitFeedRequest.php';

require plugin_dir_path( __FILE__ ) . 'includes/MarketplaceWebService/Model/SubmitFeedResult.php';

//require plugin_dir_path( __FILE__ ) . 'includes/MarketplaceWebService/Model/ContentType.php';

require plugin_dir_path( __FILE__ ) . 'includes/MarketplaceWebService/Model/GetFeedSubmissionListRequest.php';

require plugin_dir_path( __FILE__ ) . 'includes/MarketplaceWebService/Model/GetFeedSubmissionListResponse.php';

require plugin_dir_path( __FILE__ ) . 'includes/MarketplaceWebService/Model/GetFeedSubmissionListResult.php';

require plugin_dir_path( __FILE__ ) . 'includes/MarketplaceWebService/Model/GetFeedSubmissionResultRequest.php';

require plugin_dir_path( __FILE__ ) . 'includes/MarketplaceWebService/Model/GetFeedSubmissionResultResponse.php';

require plugin_dir_path( __FILE__ ) . 'includes/MarketplaceWebService/Model/GetFeedSubmissionResultResult.php';


/**
 * MarketplaceWebService Client configuration file
 * 
 * @since 0.6.0
 */
require plugin_dir_path( __FILE__ ) . 'includes/MarketplaceWebService/Feed/.config.inc.php';

/** TODO: Remove old sample files when no longer needed */
/**
 * Class responsible for submitting inventory feed to MarketplaceWebService
 * 
 * @since 0.6.0
 */
//require plugin_dir_path( __FILE__ ) . 'includes/MarketplaceWebService/Feed/SubmitFeedphp';

/**
 * Class responsible for submitting inventory feed to MarketplaceWebService
 * 
 * @since 0.6.0
 */
//require plugin_dir_path( __FILE__ ) . 'includes/MarketplaceWebService/Feed/GetFeedSubmissionList.php';

/**
 * Class responsible for submitting inventory feed to MarketplaceWebService
 * 
 * @since 0.6.0
 */
//require plugin_dir_path( __FILE__ ) . 'includes/MarketplaceWebService/Feed/GetFeedSubmissionResult.php';

/**
 * Class responsible for submitting inventory feed to MarketplaceWebService
 * 
 * @since 0.6.0
 */
require plugin_dir_path( __FILE__ ) . 'includes/MarketplaceWebService/Feed/tfs-class-mws-feed.php';


/**
 * Enqueue admin scripts
 * 
 * @since 0.3.0
 */
function tfs_enqueue_scripts() {

	wp_register_script('tfs_woo_amz_int', site_url('/wp-content/plugins/woo-amz-integration/admin/js/woo-amz-integration-admin.js?07032020'), true);
	wp_enqueue_script( 'tfs_woo_amz_int' );

}
add_action( 'admin_enqueue_scripts', 'tfs_enqueue_scripts');


/**
 * Adds processing query string for use with CRON
 * 
 * @since 0.3.0
 * @return boolean - whether or not the query string has been hit
 */
function tfs_processing_script_query_string() {

	$query_string = $_SERVER['QUERY_STRING'];

	if ( $query_string === '0800fc577294c34e0b28ad2839435945' ) {

		return true;

	} else {

		return;
	
	}

}

/**
 * Adds trigger query string to start feed execution with CRON
 * 
 * @since 0.3.0
 * @return boolean - whether or not the query string has been hit
 */
function tfs_trigger_script_query_string() {

	$query_string = $_SERVER['QUERY_STRING'];

	if ( $query_string === '962e52217134c1f7556fefeb1bfa1e35' ) {

		return true;

	} else {

		return;
	
	}

}


/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    0.1.0
 */
function run_woo_amz_integration() {
	

	if ( tfs_trigger_script_query_string() == true ) {
		
		$plugin = new Woo_Amz_Integration();
		$plugin->run();

		$init_woo_api = new Woo_REST_API();
		
	}

	if ( tfs_processing_script_query_string() == true ) {

		Woo_REST_API::tfs_restart_product_data_feed();

	}

}
run_woo_amz_integration();
