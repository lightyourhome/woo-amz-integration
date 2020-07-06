<?php

/**
 * The class responsible for communicating with WooCommerce REST API
 * 
 * @since 0.1.0
 * @version 0.3.0
 */

require __DIR__ . '/vendor/autoload.php';

use Automattic\WooCommerce\Client;

class Woo_REST_API {

    function __construct() {

        self::tfs_get_product_data();

    }

    private static function tfs_start_http_client() {

        return new Client(

            'https://www.lightyourhome.com',
            'ck_73172a979c45a06bbf9b2e1464cfa2dc8d55e29b',
            'cs_2afa79f0cab8defd36f492a32302de10c8bb936f',
            [
                'wp_api' => true,
                'version' => 'wc/v3'
            ]
    
        );

    }

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

    private static function tfs_get_all_product_variations() {

        //use woocommerce rest api for this

        //print_r($woocommerce->get('products/<product_id>/variations'));



    }

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

    private static function tfs_update_row( $id, $current_page, $products_processed, $completed = 0 ) {

       global $wpdb;

       $wpdb->update(
           $wpdb->prefix . 'tfs_amz_int_data',
           array(
                
                'current_page'       => $current_page,
                'products_processed' => $products_processed,
                'completed'          => $completed
                
           ),
           array( 'id' => $id )
       );

    }

    private static function tfs_check_product_feed_download_status( $id ) {

        global $wpdb;

        $tbl_name = $wpdb->prefix . 'tfs_amz_int_data';

        $current_status = $wpdb->get_results(

            "
            SELECT id, products_to_process, current_page, products_processed, completed
            FROM $tbl_name
            WHERE id = $id

            "

        );

        //print_r($current_status);

        foreach( $current_status as $col ) {

            return $col;

        }

    }

    public static function tfs_restart_product_data_feed() {

        $status = self::tfs_check_product_feed_download_status( 47 );

        if ( $status->completed !== 1 ) {

            self::tfs_get_product_data();

            printf('continuing');

        }

    }

    public static function tfs_check_if_row_exists( $id ) {

        global $wpdb;

        $tbl_name = $wpdb->prefix . 'tfs_amz_int_data';

        $check = $wpdb->get_var(

            "
            SELECT id
            FROM $tbl_name
            WHERE id = $id
            "

        );

        if ( $check > 0 ) {

            return TRUE;

        } else {

            return FALSE;

        }

    }



    public static function tfs_get_product_data() {

        $total_products = self::tfs_get_product_count();

        $id = 47;

        //insert current feed id to get info about it
        $status_obj = self::tfs_check_product_feed_download_status( $id );

        $all_products = [];

        if ( empty( self::tfs_check_if_row_exists( $id ) ) || self::tfs_check_if_row_exists( $id ) !== TRUE  ) {

            self::tfs_insert_row( $id, $total_products, 1, sizeOf( $all_products) );

        }

        $page = 0;

        $current_page_count = 1;

        $products_processed = 0;

        if ( $status_obj !== NULL && $status_obj->products_processed ) {

            $products_processed = $status_obj->products_processed;

        }

        if ( $status_obj !== NULL && $status_obj->current_page ) {

            $current_page_count = $status_obj->current_page;

        }
    
        $products = [];
    
        $woocommerce = self::tfs_start_http_client();

        do {

            //when finished, mark as completed
            if ( $products_processed + count( $all_products ) > $status_obj->products_to_process ) {

                self::tfs_update_row( $id, $current_page_count, $products_processed + count( $all_products ), 1 );

                break;

            }

            try {
        
                $products = $woocommerce->get( 'products', array( 'per_page' => 100, 'page' => $current_page_count ) );
            
            } catch (HttpClientException $e) {
            
                die("Can't get products: $e");
        
            }
            
            $all_products = array_merge( $all_products, $products);
            $current_page_count++;
            $page++;
        
            self::tfs_update_row( $id, $current_page_count, $products_processed + sizeOf( $all_products ) );
    
        } while ( $page < 5 );

    }

    private function tfs_filter_product_data( $product_data_stdclass ) {

        $decoded = [];
        $complete_product_data = [];

        foreach ( $product_data_stdclass as $obj ) {

            $decoded_product_data = json_decode( json_encode( $obj ), true );

            array_push( $decoded, $decoded_product_data );

        }

        foreach( $decoded as $product ) {

            array_push( $complete_product_data, 
            
                array( $product['sku'] => 
                
                    array( 'stock' => $product['stock_quantity'], 'regular_price' => $product['regular_price'], 'sale_price' => $product['sale_price'] ) 

                )
            );

        }

        //print_r($complete_product_data);

    }


}