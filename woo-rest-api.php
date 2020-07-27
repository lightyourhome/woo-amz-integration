<?php

/**
 * The class responsible for communicating with WooCommerce REST API
 * 
 * @since 0.1.0
 * @version 0.5.0
 */

defined( 'ABSPATH' ) or die( 'You do not have sufficient permissions to access this page.' );

require __DIR__ . '/vendor/autoload.php';

use Automattic\WooCommerce\Client;

class Woo_REST_API {

    /**
     * Product data for use with class Woo_Amz_File_Handler
     * 
     * @since 0.4.0
     */
    public static $tfs_product_data = NULL;

    public static $feed_running = FALSE;


    function __construct() {

        self::tfs_filter_product_data( self::tfs_get_product_data() );
    }

    /**
     * Get instance of class Client
     * 
     * @since 0.2.0
     * @return new Client - HTTP Client to communicate with WooCommerce REST API
     */
    private static function tfs_start_http_client() {

        return new Client(

            'https://www.lightyourhome.com',
            'ck_73172a979c45a06bbf9b2e1464cfa2dc8d55e29b',
            'cs_2afa79f0cab8defd36f492a32302de10c8bb936f',
            [
                'wp_api' => true,
                'version' => 'wc/v3',
                'query_string_auth' => true
            ]
    
        );

    }

    /**
     * Retrieve the total product count via the WooCommerce REST API
     * 
     * @since 0.2.0
     * @return int - the total number of products
     */
    private static function tfs_get_product_count() {

        $decoded = [];

        $woocommerce = self::tfs_start_http_client();

        $product_totals = $woocommerce->get('reports/products/totals');

        foreach ( $product_totals as $obj ) {

            $decoded_product_data = json_decode( json_encode( $obj ), true );

            array_push( $decoded, $decoded_product_data );

        }

        $total_simple_products = $decoded[2]['total'];
        $total_variable_products = $decoded[3]['total'];

        //all simple products
        return $total_simple_products + $total_variable_products;

    }

    /**
     * Retrieve all product variations
     * 
     * @since 0.2.0
     */
    private static function tfs_get_all_product_variations() {

        //use woocommerce rest api for this

        //print_r($woocommerce->get('products/<product_id>/variations'));



    }


    /**
     * Inserts a row into the plugin custom table
     * 
     * @since 0.3.0
     * 
     * @param int $id - the export id
     * @param int $products_to_process - the total of amount of products that need to be processed
     * @param int $current_page - the current page in WooCommerce REST API pagination
     * @param int $products_processed - the current number of products processed
     * @param bool $completed - whether or not the current export is completed
     */
    private static function tfs_insert_row( $id, $products_to_process, $current_page, $products_processed, $completed = 0 ) {

        global $wpdb;

        $wpdb->insert( 
            $wpdb->prefix . 'tfs_amz_int_data',
            array(

                'id'                  => $id,
                'products_to_process' => $products_to_process,
                'current_page'        => $current_page,
                'products_processed'  => 0,
                'completed'           => $completed

        )  );

    }

    private static function tfs_delete_row() {

        global $wpdb;

        $wpdb->delete( 
            $wpdb->prefix . 'tfs_amz_int_data',
            array(

                'id' => 0,
                
        )  );

    }

    /**
     * Reset the plugin inventory progress row
     * 
     * @since 0.5.0
     * 
     */
    private static function tfs_reset_row_data() {

        global $wpdb;

        $wpdb->update(
            $wpdb->prefix . 'tfs_amz_int_data',
            array(

                'current_page' => 1,
                'products_to_process' => 0,
                'products_processed' => 0,
                'completed' => 0

            ),
            array( 'id' => 0 )

        );


    }


    /**
     * Update a row in the custom plugin table
     * 
     * @since 0.3.0
     * 
     * @param int $id - the export id
     * @param int $current_page - the current page in WooCommerce REST API pagination
     * @param int $products_processed - the current number of products processed
     * @param bool $completed - whether or not the current export is completed
     */
    private static function tfs_update_row( $id, $current_page, $total_products, $products_processed, $completed = 0 ) {

       global $wpdb;

       $wpdb->update(
           $wpdb->prefix . 'tfs_amz_int_data',
           array(
                
                'current_page'        => $current_page,
                'products_to_process' => $total_products,
                'products_processed'  => $products_processed,
                'completed'           => $completed
                
           ),
           array( 'id' => $id )
       );

    }

    /**
     * Checks the status of the current inventory export
     * 
     * @since 0.3.0
     * 
     * @return stdClass $col - object containing info about current inventory export
     */
    public static function tfs_check_product_feed_download_status() {

        global $wpdb;

        $tbl_name = $wpdb->prefix . 'tfs_amz_int_data';

        $current_status = $wpdb->get_results(

            "
            SELECT id, products_to_process, current_page, products_processed, completed
            FROM $tbl_name
            WHERE id = 0

            "

        );

        foreach( $current_status as $col ) {

            return $col;

        }

    }

    /**
     * If the current inventory export is not yet completed, restart it
     * 
     * @since 0.3.0
     */
    public static function tfs_restart_product_data_feed() {

        $status = self::tfs_check_product_feed_download_status();

        if ( $status->completed !== 1 ) {

            self::tfs_filter_product_data( self::tfs_get_product_data() );

            printf('continuing');

        }

    }

    /**
     * Checks whether or not a row with the supplied id exists in the plugin custom table
     * 
     * @since 0.3.0
     * 
     * @param int $id - the export/row id
     * @return bool
     */
    public static function tfs_check_if_row_exists() {

        global $wpdb;

        $tbl_name = $wpdb->prefix . 'tfs_amz_int_data';

        $check = $wpdb->get_var(

            "
            SELECT id
            FROM $tbl_name
            WHERE id = 0
            "

        );

        if ( $check > 0 ) {

            return TRUE;

        } else {

            return FALSE;

        }

    }


    /**
     * Retrieves product data from the WooCommerce REST API
     * 
     * @since 0.2.0
     * 
     * @return array $all_products - an array of products from the current WooCommerce REST API call
     */
    public static function tfs_get_product_data() {

        self::$feed_running = TRUE;

        $total_products = self::tfs_get_product_count();

        $all_products = [];

        $status_obj = self::tfs_check_product_feed_download_status();

        $page = 0;

        $current_page_count = 1;

        $products_processed = 0;

        if ( $status_obj !== NULL && $status_obj->products_processed ) {

            $products_processed = $status_obj->products_processed;

        }

        if ( $status_obj !== NULL && $status_obj->current_page ) {

            $current_page_count = $status_obj->current_page;

        }

        if ( $status_obj !== NULL && $status_obj->completed !== FALSE ) {

            //populate the row
            self::tfs_update_row( 0, $current_page_count, $total_products, $products_processed );
    
        }    
    
        $products = [];
    
        $woocommerce = self::tfs_start_http_client();

        do {

            //when finished, mark as completed
            if ( $status_obj !== NULL ) {

                if ( $products_processed + count( $all_products ) > $status_obj->products_to_process ) {

                    self::tfs_update_row( 0, $current_page_count, $total_products, $products_processed + count( $all_products ), 1 );
                        
                    break;
    
                }

            }

            try {
        
                $products = $woocommerce->get( 'products', array( 'per_page' => 100, 'page' => $current_page_count ) );
            
            } catch (HttpClientException $e) {
            
                die("Can't get products: $e");
        
            }
            
            $all_products = array_merge( $all_products, $products);
            $current_page_count++;
            $page++;
        
            self::tfs_update_row( 0, $current_page_count, $total_products, $products_processed + sizeOf( $all_products ) );
    
        } while ( $page < 5 );


        self::$feed_running = FALSE;

        if ( $status_obj !== NULL && $status_obj->completed == 1 ) {

            self::tfs_delete_row();

            self::tfs_insert_row( 0, 0, 0, 0 );
            
            return FALSE;

        } else {

            return $all_products;

        }

    }

    /**
     * Creates an array containing only the necessary product data for Amazon MWS inventory file
     * 
     * @since 0.2.0
     * 
     * @param stdClass - object containing product data
     */
    private static function tfs_filter_product_data( $product_data_stdclass ) {

        if ( $product_data_stdclass == FALSE ) {

            self::$tfs_product_data == FALSE;

        } else {

            $decoded = [];
            $complete_product_data = [];
    
            foreach ( $product_data_stdclass as $obj ) {
    
                $decoded_product_data = json_decode( json_encode( $obj ), true );
    
                array_push( $decoded, $decoded_product_data );
    
            }
    
            foreach( $decoded as $product ) {
    
                $price = null;
    
                if ( ! empty( $product['sale_price'] ) && $product['sale_price'] > 0 ) {
    
                    $price = $product['sale_price'];
    
                } else {
    
                    $price = $product['regular_price'];
    
                }
    
                array_push( $complete_product_data, 
                
                    array( 
    
                        'sku'                 => $product['sku'], 
                        'price'               => $price, 
                        'minimum-price'       => $price, 
                        'maximum-price'       => '', 
                        'quantity'            => $product['stock_quantity'], 
                        'handling-time'       => '', 
                        'fullfilment-channel' => ''
    
                    )
    
                );
    
            }
    
            self::$tfs_product_data = $complete_product_data;

        }

    }


} //end class Woo_REST_API
//$init = new Woo_REST_API();