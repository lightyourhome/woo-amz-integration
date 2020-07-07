<?php

/**
 * The class responsible for generating Inventory/Pricing files for use with Amazon MWS
 * 
 * @since 0.4.0
 * @version 0.5.0
 */

defined( 'ABSPATH' ) or die( 'You do not have sufficient permissions to access this page.' );


class Woo_Amz_File_Handler {

    public function __construct() {

        self::tfs_write_to_file( Woo_REST_API::$tfs_product_data );

    }

    /**
     * Creates a txt file and writes formatted product data to the file
     * 
     * @since 0.4.0
     * 
     * @param array $data - the product data to be written to the file
     */
    private function tfs_write_to_file( $data ) {

        $file = 'c:\\xampp\\htdocs\\lightyourhome.com_april\\wp-content\\uploads\\amz_inventory.txt';
          
        if ( ! file_exists($file) ) {

            $headings = "sku\t price\tminimum-seller-allowed-price\tmaximum-seller-allowed-price\tquantity\thandling-time\tfullfilment-channel";

            file_put_contents( $file, $headings , FILE_APPEND );
    
        }
  
        foreach ( $data as $line ) {

            $formatted_line  = $line['sku'] . "\t" . $line['price'] . "\t" . $line['minimum-price'];

            $formatted_line .= "\t" . $line['maximum-price'] . "\t" . $line['quantity'] . "\t" . $line['handling-time'];

            $formatted_line .= "\t" . $line['fullfilment-channel'];

            file_put_contents( $file, "\n" . $formatted_line, FILE_APPEND );    

        }

    }

} 