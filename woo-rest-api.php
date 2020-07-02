<?php

/**
 * The file responsible for communicating with WooCommerce REST API
 * 
 * @since 0.1.0
 * @version 0.1.0
 */

require __DIR__ . '/vendor/autoload.php';

use Automattic\WooCommerce\Client;

class Woo_REST_API {

    function __construct() {

        $this->tfs_filter_product_data( $this->tfs_get_product_data() );

    }


    private function tfs_get_product_data() {

        $page = 1;
    
        $products = [];
    
        $all_products = [];

        $test = [];

        $woocommerce = new Client(

            'https://www.lightyourhome.com',
            'ck_73172a979c45a06bbf9b2e1464cfa2dc8d55e29b',
            'cs_2afa79f0cab8defd36f492a32302de10c8bb936f',
            [
                'wp_api' => true,
                'version' => 'wc/v3'
            ]
    
        );

        //$total_product_count = $woocommerce->get('reports/products/totals');

     //   do {

            //set_time_limit(0);


            try {

                $products = $woocommerce->get('products', array('per_page' => 100, 'page' => 1 ));
    
            } catch (HttpClientException $e) {
    
                die("Can't get products: $e");
    
            }
    
            $all_products = array_merge( $all_products, $products);
            //$page++;

            //print_r($products);


       // } while ( count($products) > 0 );


        return $all_products;

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

        print_r($complete_product_data);

    }


}

$init = new Woo_REST_API();