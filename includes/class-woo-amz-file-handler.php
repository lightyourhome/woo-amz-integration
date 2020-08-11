<?php

/**
 * The class responsible for generating Inventory/Pricing files for use with Amazon MWS
 * 
 * @since 0.4.0
 * @version 0.9.0
 */

defined( 'ABSPATH' ) or die( 'You do not have sufficient permissions to access this page.' );


class Woo_Amz_File_Handler {


    public function __construct( $data = NULL ) {

        if ( $data !== NULL ) {

            self::tfs_write_to_file( $data );

        } 

    }

    /**
     * If an inventory file exists, delete it
     * 
     * @since 0.9.0
     */
    public static function tfs_delete_inv_file() {

        if ( file_exists( WOO_AMZ_INV_FILE_PATH ) ) {

            unlink( WOO_AMZ_INV_FILE_PATH );

        }

    }

    /**
     * Check if an amz inventory file currently exists
     * 
     * @since 0.4.0
     * 
     */
    public static function tfs_check_if_inv_file_exists() {

		if ( file_exists( WOO_AMZ_INV_FILE_PATH ) ) { 

			return true;
			
		} else {

			return false;

		}

	}

    /**
     * Creates a txt file and writes formatted product data to the file
     * 
     * @since 0.4.0
     * 
     * @param array $data - the product data to be written to the file
     */
    private function tfs_write_to_file( $data ) {

        if ( $data == FALSE ) {

            return;

        } else {
          
            if ( ! file_exists( WOO_AMZ_INV_FILE_PATH ) ) {
    
                $headings = "type\tsku\tprice\tminimum-seller-allowed-price\tmaximum-seller-allowed-price\tquantity\thandling-time\tfullfilment-channel";
    
                file_put_contents( WOO_AMZ_INV_FILE_PATH, $headings , FILE_APPEND );
        
            }
      
            foreach ( $data as $line ) {
    
                $formatted_line  = $line['type'] . "\t" . $line['sku'] . "\t" . $line['price'] . "\t" . $line['minimum-price'];
    
                $formatted_line .= "\t" . $line['maximum-price'] . "\t" . $line['quantity'] . "\t" . $line['handling-time'];
    
                $formatted_line .= "\t" . $line['fullfilment-channel'];
    
                file_put_contents( WOO_AMZ_INV_FILE_PATH, "\n" . $formatted_line, FILE_APPEND );    
    
            }
    
        }

    }

} 