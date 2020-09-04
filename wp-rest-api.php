<?php 

/**
 * The class responsible for interacting with the Wordpress REST API
 * 
 * @since 0.3.0
 * @version 0.5.0
 */

defined( 'ABSPATH' ) or die( 'You do not have sufficient permissions to access this page.' );

add_action('plugins_loaded', array('Tfs_WP_REST_API', 'init') );

class Tfs_WP_REST_API {

    public static function init() {

        $tfs_rest_api = __CLASS__;
        new $tfs_rest_api; 

        add_action('admin_enqueue_scripts', array( $tfs_rest_api, 'tfs_localize_wp_rest_api_object' ), 999 );
        add_action('rest_api_init', array( $tfs_rest_api, 'tfs_wp_rest_api_validate_endpoint' ) ); 
        
    }    

    public static function tfs_localize_wp_rest_api_object() {

       //DEFINE THE NONCE AND THE API URL
        wp_localize_script( 'tfs_woo_amz_int', 'tfs_woo_amz_int_object',
            array(
                'api_nonce' => wp_create_nonce( 'wp_rest' ),
                'api_url'   => site_url('/wp-json/rest/v3/')
            )
        );
    }

    public static function tfs_wp_rest_api_validate_endpoint() {

        //declare a $namespace for the quick view endpoint
        $namespace = 'rest/v3';

        //register the rest api route
        register_rest_route( $namespace, '/woo-amz-feed/', array(
            'methods' => 'GET',
            'callback' => function($request) {

                $params = $request->get_params();
               
                $feed_status = Woo_REST_API::tfs_check_product_feed_download_status();

                $completed = 0;

                if ( $feed_status !== NULL && $feed_status->completed ) {

                    $completed = $feed_status->completed;

                }

                if ( isset( $params['enabled'] ) && $params['enabled'] == true ) {

                    if ( Woo_REST_API::$feed_running !== TRUE ) {

                        $init_woo_rest_api = new Woo_REST_API();

                        $init_file_handler = new Woo_Amz_File_Handler();

                        if ( $feed_status !== NULL ) {

                            return wp_json_encode( $feed_status );

                        }

                    } else {

                        if ( $feed_status !== NULL ) {

                            return wp_json_encode( $feed_status );

                        }

                    }

                } 

            } //end callback
        ) ); //end array()

    }

    public function tfs_endpoint_callback() {





    }

} // End class Tfs_WP_REST_API

