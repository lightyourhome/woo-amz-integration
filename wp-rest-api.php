<?php 

/**
 * The class responsible for interacting with the Wordpress REST API
 * 
 * @since 0.3.0
 * @version 0.9.0
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

                $dbman = new TFS_DB_MAN();
               
                $feed_status = $dbman->tfs_check_product_feed_download_status();

                $completed = 0;

                if ( $feed_status !== NULL && $feed_status->completed ) {

                    $completed = $feed_status->completed;

                }

                if ( isset( $params['enabled'] ) && $params['enabled'] == TRUE ) {

                    if ( Woo_REST_API::$feed_running !== TRUE ) {

                        /**
                        * reset the feed and return continue_after_reset as true
                        * so the feed will reset from the next REST API call
                        */
                        if ( $params['restart'] == "true" ) {
    
                            $dbman->tfs_delete_row();
    
                            $dbman->tfs_insert_row( 0, 0, 0, 0 );                
        
                            if ( $feed_status !== NULL ) {

                                $status = array(

                                    'status'               => $feed_status,
                                    'continue_after_reset' => TRUE

                                );
    
                                return wp_json_encode( $status ); //wp_json_encode( $params );
    
                            }
                            
                        /**
                         * Run the feed or continue its execution
                         */
                        } elseif ( $params['run'] == "true" ) {

                            $init_woo_rest_api = new Woo_REST_API();
    
                            if ( $feed_status !== NULL ) {
    
                                $status = array(

                                    'status'               => $feed_status,
                                    'continue_after_reset' => FALSE

                                );

                                return wp_json_encode( $status );
    
                            }                    
    
                        } else {
    
                            if ( $feed_status !== NULL ) {

                                $status = array(

                                    'status'               => $feed_status,
                                    'continue_after_reset' => FALSE

                                );
    
                                return wp_json_encode( $status );
    
                            }
    
                        }

                    }

                    //continue the feed
                    

                } 

            } //end callback

        ) ); //end array()

    }

    public function tfs_endpoint_callback() {





    }

} // End class Tfs_WP_REST_API

