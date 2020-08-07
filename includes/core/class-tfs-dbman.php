<?php

/**
* Handles Database operations for the plugin
* 
* @since 0.6.0
* @version 0.9.0
*/

class TFS_DB_MAN {

    public function __construct() {


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
    public static function tfs_insert_row( $id, $products_to_process, $current_page, $products_processed, $completed = 0 ) {

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

    /**
     * Deletes the plugin database table row
     * 
     * @since 0.9.0
     */
    public static function tfs_delete_row() {

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
    public static function tfs_reset_row_data() {

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
    public static function tfs_update_row( $id, $current_page, $total_products, $products_processed, $completed = 0 ) {

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




}