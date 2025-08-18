<?php
/**
 * Cron Handler
 *
 * @package AliDrop
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

/**
 * AliDrop_Cron_Handler Class.
 */
class AliDrop_Cron_Handler {

    const CRON_HOOK = 'alidrop_sync_products_cron';
    const RETRY_CRON_HOOK = 'alidrop_retry_sync_cron';

    protected static $_instance = null;

    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    public function __construct() {
        add_filter( 'cron_schedules', array( $this, 'add_custom_cron_intervals' ) );
        add_action( 'update_option_alidrop_autosync_enabled', array( $this, 'handle_schedule_change' ), 10, 0 );
        add_action( 'update_option_alidrop_sync_interval', array( $this, 'handle_schedule_change' ), 10, 0 );

        add_action( self::CRON_HOOK, array( $this, 'run_sync' ) );
        add_action( self::RETRY_CRON_HOOK, array( $this, 'run_retry_sync' ) );

        // Ensure the retry job is always scheduled.
        if ( ! wp_next_scheduled( self::RETRY_CRON_HOOK ) ) {
            wp_schedule_event( time(), 'hourly', self::RETRY_CRON_HOOK );
        }
    }

    public function add_custom_cron_intervals( $schedules ) {
        $schedules['every_5_minutes'] = array( 'interval' => 300, 'display' => __( 'Every 5 Minutes' ) );
        $schedules['every_15_minutes'] = array( 'interval' => 900, 'display' => __( 'Every 15 Minutes' ) );
        $schedules['every_30_minutes'] = array( 'interval' => 1800, 'display' => __( 'Every 30 Minutes' ) );
        return $schedules;
    }

    public function handle_schedule_change() {
        $this->schedule_sync();
    }

    public function schedule_sync() {
        wp_clear_scheduled_hook( self::CRON_HOOK );
        $is_enabled = get_option( 'alidrop_autosync_enabled' );
        if ( '1' !== $is_enabled ) {
            return;
        }
        $interval = get_option( 'alidrop_sync_interval', 'daily' );
        if ( ! wp_next_scheduled( self::CRON_HOOK ) ) {
            wp_schedule_event( time(), $interval, self::CRON_HOOK );
        }
    }

    public function run_sync() {
        require_once ALIDROP_PLUGIN_DIR . 'includes/class-sync-handler.php';
        AliDrop_Sync_Handler::sync_all_products();
    }

    public function run_retry_sync() {
        require_once ALIDROP_PLUGIN_DIR . 'includes/class-sync-handler.php';
        AliDrop_Sync_Handler::sync_retry_queue();
    }
}

function alidrop_init_cron_handler() {
    AliDrop_Cron_Handler::instance();
}
alidrop_init_cron_handler();
