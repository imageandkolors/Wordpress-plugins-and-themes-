<?php
/**
 * Sync Handler
 *
 * @package AliDrop
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

/**
 * AliDrop_Sync_Handler Class.
 */
class AliDrop_Sync_Handler {

    const RETRY_QUEUE_OPTION = 'alidrop_sync_retry_queue';

    /**
     * Sync all imported products.
     */
    public static function sync_all_products() {
        AliDrop_Logger::log( 'info', 'sync', 'Sync process started.' );
        set_transient( 'alidrop_sync_status', 'running', 300 );

        $api_key = get_option( 'alidrop_api_key' );
        $api_secret = get_option( 'alidrop_api_secret' );
        if ( empty( $api_key ) || empty( $api_secret ) ) {
            delete_transient( 'alidrop_sync_status' );
            AliDrop_Logger::log( 'error', 'sync', 'Sync failed: API key or secret is not set.' );
            return array( 'success' => false, 'message' => __( 'API key or secret is not set.', 'alidrop' ) );
        }

        $api = new AliDrop_AliExpress_API( $api_key, $api_secret );
        $stats = array( 'synced' => 0, 'failed' => 0, 'skipped' => 0 );

        $query = new WC_Product_Query( array( 'limit' => -1, 'meta_key' => '_alidrop_product_id', 'return' => 'ids' ) );
        $product_ids = $query->get_products();

        if ( empty( $product_ids ) ) {
            delete_transient( 'alidrop_sync_status' );
            update_option( 'alidrop_last_sync_time', current_time( 'mysql' ) );
            AliDrop_Logger::log( 'info', 'sync', 'No imported products to sync.' );
            return array( 'success' => true, 'message' => __( 'No imported products to sync.', 'alidrop' ) );
        }

        foreach ( $product_ids as $product_id ) {
            self::sync_single_product( $product_id, $api, $stats );
        }

        $message = sprintf( __( 'Sync complete. Synced: %d, Skipped: %d, Failed: %d.', 'alidrop' ), $stats['synced'], $stats['skipped'], $stats['failed'] );
        AliDrop_Logger::log( 'success', 'sync', $message );
        delete_transient( 'alidrop_sync_status' );
        update_option( 'alidrop_last_sync_time', current_time( 'mysql' ) );

        return array( 'success' => true, 'message' => $message );
    }

    /**
     * Sync a single product.
     */
    private static function sync_single_product( $product_id, $api, &$stats ) {
        $product = wc_get_product( $product_id );
        if ( ! $product ) {
            $stats['failed']++;
            AliDrop_Logger::log( 'warning', 'sync', "Could not retrieve product with ID: $product_id." );
            self::remove_from_retry_queue( $product_id );
            return;
        }

        $aliexpress_id = $product->get_meta( '_alidrop_product_id' );
        if ( empty( $aliexpress_id ) ) {
            $stats['skipped']++;
            return;
        }

        $details = $api->get_product_details( $aliexpress_id );
        if ( is_wp_error( $details ) ) {
            $stats['failed']++;
            AliDrop_Logger::log( 'error', 'sync', "API error for product #$product_id (Ali ID: $aliexpress_id): " . $details->get_error_message() );
            self::add_to_retry_queue( $product_id );
            return;
        }

        $sync_options = get_option( 'alidrop_selective_sync_options', array( 'price' => '1', 'stock' => '1' ) );
        $product_updated = false;

        if ( isset( $sync_options['price'] ) ) { $product->set_regular_price( $details['target_sale_price'] ); $product_updated = true; }
        if ( isset( $sync_options['stock'] ) ) { $product->set_stock_quantity( $details['stock'] ); $product_updated = true; }
        if ( isset( $sync_options['description'] ) ) { $product->set_name( $details['product_title'] ); $product->set_description( $details['product_description'] ); $product_updated = true; }

        if ( $product_updated ) {
            if ( $product->save() ) {
                $stats['synced']++;
                self::remove_from_retry_queue( $product_id );
            } else {
                $stats['failed']++;
                AliDrop_Logger::log( 'error', 'sync', "Failed to save product #$product_id." );
                self::add_to_retry_queue( $product_id );
            }
        } else {
            $stats['skipped']++;
        }
    }

    /**
     * Process the retry queue.
     */
    public static function sync_retry_queue() {
        $queue = get_option( self::RETRY_QUEUE_OPTION, array() );
        if ( empty( $queue ) ) return;

        AliDrop_Logger::log( 'info', 'sync', 'Retry sync process started for ' . count( $queue ) . ' products.' );
        $api_key = get_option( 'alidrop_api_key' );
        $api_secret = get_option( 'alidrop_api_secret' );
        if ( empty( $api_key ) || empty( $api_secret ) ) {
            AliDrop_Logger::log( 'error', 'sync', 'Retry sync failed: API key or secret is not set.' );
            return;
        }
        $api = new AliDrop_AliExpress_API( $api_key, $api_secret );
        $stats = array( 'synced' => 0, 'failed' => 0, 'skipped' => 0 );

        $products_to_process = array_slice( $queue, 0, 5 ); // Process 5 at a time
        foreach ( $products_to_process as $product_id ) {
            self::sync_single_product( $product_id, $api, $stats );
        }
        AliDrop_Logger::log( 'info', 'sync', 'Retry sync process finished.' );
    }

    private static function add_to_retry_queue( $product_id ) {
        $queue = get_option( self::RETRY_QUEUE_OPTION, array() );
        if ( ! in_array( $product_id, $queue ) ) {
            $queue[] = $product_id;
            update_option( self::RETRY_QUEUE_OPTION, array_unique($queue) );
        }
    }

    private static function remove_from_retry_queue( $product_id ) {
        $queue = get_option( self::RETRY_QUEUE_OPTION, array() );
        if ( ( $key = array_search( $product_id, $queue ) ) !== false ) {
            unset( $queue[ $key ] );
            update_option( self::RETRY_QUEUE_OPTION, $queue );
        }
    }
}
