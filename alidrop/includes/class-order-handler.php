<?php
/**
 * Order Handler
 *
 * @package AliDrop
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

/**
 * AliDrop_Order_Handler Class.
 */
class AliDrop_Order_Handler {

    /**
     * Handle a new order.
     *
     * @param int $order_id
     */
    public function handle_new_order( $order_id ) {
        $order = wc_get_order( $order_id );
        if ( ! $order ) {
            return;
        }

        $aliexpress_items = array();
        foreach ( $order->get_items() as $item_id => $item ) {
            $product_id = $item->get_product_id();
            $aliexpress_product_id = get_post_meta( $product_id, '_alidrop_product_id', true );
            if ( ! empty( $aliexpress_product_id ) ) {
                $aliexpress_items[] = array(
                    'product_id' => $aliexpress_product_id,
                    'quantity'   => $item->get_quantity(),
                );
            }
        }

        if ( empty( $aliexpress_items ) ) {
            return;
        }

        AliDrop_Logger::log( 'info', 'order', "Processing order #$order_id for AliExpress push." );

        $order_data = array(
            'shipping_address' => $order->get_address(),
            'items'            => $aliexpress_items,
            'customer_note'    => $order->get_customer_note(),
        );

        $order_data = apply_filters( 'alisync_before_order_push_data', $order_data, $order );

        $api_key = get_option( 'alidrop_api_key' );
        $api_secret = get_option( 'alidrop_api_secret' );
        if ( empty( $api_key ) || empty( $api_secret ) ) {
            $error_msg = __( 'Could not push order to AliExpress. API key or secret is not set.', 'alidrop' );
            $order->add_order_note( 'AliDrop: ' . $error_msg );
            AliDrop_Logger::log( 'error', 'order', "Order push for #$order_id failed: API key or secret not set." );
            return;
        }

        $api = new AliDrop_AliExpress_API( $api_key, $api_secret );
        $result = $api->push_order( $order_data );

        if ( is_wp_error( $result ) ) {
            $error_msg = $result->get_error_message();
            $order->add_order_note( sprintf( __( 'AliDrop: Failed to push order to AliExpress. Error: %s', 'alidrop' ), $error_msg ) );
            AliDrop_Logger::log( 'error', 'order', "Order push for #$order_id failed: " . $error_msg );
        } else {
            $ali_order_id = $result['aliexpress_order_id'];
            $order->update_meta_data( '_alidrop_order_id', $ali_order_id );
            $order->add_order_note( sprintf( __( 'AliDrop: Order successfully pushed to AliExpress. AliExpress Order ID: %s', 'alidrop' ), $ali_order_id ) );
            $order->save();
            AliDrop_Logger::log( 'success', 'order', "Order #$order_id pushed successfully. AliExpress Order ID: $ali_order_id." );
            do_action( 'alisync_after_order_push', $order_id, $ali_order_id );
        }
    }
}
