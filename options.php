<?php

/**
 * Register and Render the WooCommerce Amazon Integration Plugin
 * Menu and Settings Pages
 * 
 * @since v0.3.0
 * @version 0.5.0
 */

defined( 'ABSPATH' ) or die( 'You do not have sufficient permissions to access this page.' );

class Woo_Amz_Integration_Settings_Page {

	/**
	 * Holds the values to be used in the fields callbacks
	 */
    private static $woo_amz_int_options;
    
	public function __construct() {

        add_action( 'admin_menu', array( $this, 'tfs_add_woo_amz_menu' ) );
        add_action( 'admin_init', array( $this, 'tfs_register_woo_amz_settings' ) );
        $this->tfs_get_form_values();

    }
    
    public static function tfs_get_form_values() {

        if ( self::$woo_amz_int_options['enable_feed'] == 1 ) {

            //Woo_REST_API::tfs_get_product_data();

        }

	}
	

	/**
	 * Get the current options for use with the plugin add on classes
	 * 
	 * @since 0.3.0
	 * @return array - options
	 */
	public static function get_inventory_tools_options() {

		return get_option('inventory_tools_settings');

    }
    
    /**
    * Adds a top level menu page for the plugin to the admin menu
    * 
    * @since 0.3.0
    */
    public function tfs_add_woo_amz_menu() {

        add_menu_page('AMZ FEED', 'AMZ FEED', 'manage_options', 'tfs-woo-amz-integration', array( $this, 'tfs_render_woo_amz_integration_settings_page' ) );

    }
	 
	/**
	 * Renders the settings page
	 * 
	 * @since 0.3.0
	 */
	public function tfs_render_woo_amz_integration_settings_page() { 

		//set the options property
		self::$woo_amz_int_options = get_option('woo_amz_int_settings');

		?>
	
		<h1>WooCommerce Amazon Integration Settings</h1>

			<?php if ( Woo_Amz_File_Handler::tfs_check_if_inv_file_exists() ) : ?>

				<div class="notice is-dismissable notice-info">
					<p><?php _e( 'An Inventory File Currently Exists. You can create a new one below.', 'lightyourhome.com' ); ?></p>
				</div>

			<?php else : ?>

				<div class="notice is-dismissable notice-error">
					<p><?php _e( 'An Inventory File Does Not Currently Exist. You can create a new one below.', 'lightyourhome.com' ); ?></p>
				</div>

			<?php endif; ?>

            <div id="feed-progress"></div>
			<div id="feed-status"><img id="ajax-loader" src="<?php echo site_url('/wp-content/plugins/woo-amz-integration/assets/images/ajax-loader.gif'); ?>"><div id="feed-status-text"></div></div>
			<div id="feed-warning"><strong>The feed is currently running. Please do not close or refresh this page.</strong></div>
			<br />
			<form id="woo-amz-admin-settings-form" action="options.php" method="post">
				<?php 
				 settings_fields( 'woo-amz-integration' );
				 do_settings_sections( 'woo-amz-integration' ); ?>
				<input name="submit" class="button button-primary" type="submit" value="<?php esc_attr_e( 'Save Settings' ); ?>" />
                <input id="feed_submit" name="submit" class="button button-primary" type="button" value="<?php if ( Woo_Amz_File_Handler::tfs_check_if_inv_file_exists() ) : esc_attr_e( 'Create New Feed' ); else : esc_attr_e( 'Create Feed' ); endif; ?>" />
				<a href="<?php echo site_url('/wp-content/uploads/amz_inventory.txt'); ?>" class="button button-primary" download>Download Inventory File</a>
				<input id="send_inventory" name="submit" class="button button-primary" type="button" value="<?php esc_attr_e( 'Send Inventory to Amazon' ); ?>" style="display: none;" />
		</form>
	
		<?php
	}
	
	/**
 	* Adds the plugin settings, sections and fields during admin_init
 	* 
 	* @since 0.3.0
 	*/
	public function tfs_register_woo_amz_settings() {

		register_setting( 'woo-amz-integration', 'woo_amz_int_settings' );

		add_settings_section(
			'woo_amz_int_settings_section',
			'',
			array( $this, 'tfs_woo_amz_int_section_description' ),
			'woo-amz-integration'
		);

		add_settings_field(
			'enable_feed',
			'Enable Amazon Feed',
			array( $this, 'tfs_enable_woo_amz_feed' ),
			'woo-amz-integration',
			'woo_amz_int_settings_section'
		);
    
	}

	
	private function tfs_on_option_save( $options ) {

		return $options;
	
	}


	/**
 	* Callback function for inventory tools settings section
 	* adds content to the start of the inventory tools settings section
 	* 
 	* @since 0.3.0
 	*/
	public function tfs_woo_amz_int_section_description() {

    	echo '<p><strong>Enable or Disable the Amazon Feed and Click Save</strong></p>';

	}


	/**
	 * Callback function for product checker setting
	 * 
	 * @since 0.3.0
	 */
	public function tfs_enable_woo_amz_feed() {

		?>
			<input id="feed_enabled" name="woo_amz_int_settings[enable_feed]" type="checkbox" value="1" <?php checked( isset( self::$woo_amz_int_options['enable_feed'] ), 1 ) ?> />
		<?php

	}

}

if ( is_admin() ) {

	$init_inventory_tools_setting_page = new Woo_Amz_Integration_Settings_Page();

}
