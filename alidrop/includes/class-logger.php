<?php
/**
 * Logger
 *
 * @package AliDrop
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

/**
 * AliDrop_Logger Class.
 */
class AliDrop_Logger {

    /**
     * Log a message.
     *
     * @param string $type 'success', 'info', 'warning', 'error'
     * @param string $component 'import', 'sync', 'order', 'api'
     * @param string $message The log message.
     * @param int|null $product_id Optional. The ID of the product related to the log.
     */
    public static function log( $type, $component, $message, $product_id = null ) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'alidrop_logs';

        $wpdb->insert(
            $table_name,
            array(
                'type'       => $type,
                'component'  => $component,
                'message'    => $message,
                'created_at' => current_time( 'mysql' ),
            )
        );

        // Send email notification for errors if enabled
        if ( 'error' === $type && '1' === get_option( 'alidrop_email_notifications_enabled' ) ) {
            $admin_email = get_option( 'admin_email' );
            $subject = __( '[AliDrop] Plugin Error Notification', 'alidrop' );
            $body = sprintf(
                __( "An error occurred in the AliDrop plugin:\n\nComponent: %s\nMessage: %s\nTime: %s", 'alidrop' ),
                strtoupper( $component ),
                $message,
                current_time( 'mysql' )
            );
            wp_mail( $admin_email, $subject, $body );
        }
    }
}
