<?php
/**
 * Dashboard View
 *
 * @package AliDrop
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

// The variables used here are provided by the calling method in class-admin-menu.php
// $total_synced_products, $last_sync_time, $sync_status, $recent_error_logs
?>
<div class="wrap alidrop-dashboard">
    <h1><?php esc_html_e( 'AliDrop Dashboard', 'alidrop' ); ?></h1>

    <div id="dashboard-widgets-wrap">
        <div id="dashboard-widgets" class="metabox-holder">
            <div class="postbox-container">
                <div class="postbox">
                    <h2 class="hndle"><span><?php esc_html_e( 'Sync Status', 'alidrop' ); ?></span></h2>
                    <div class="inside">
                        <p><strong><?php esc_html_e( 'Total Synced Products:', 'alidrop' ); ?></strong> <?php echo esc_html( $total_synced_products ); ?></p>
                        <p><strong><?php esc_html_e( 'Last Sync:', 'alidrop' ); ?></strong> <?php echo esc_html( $last_sync_time ? human_time_diff( strtotime( $last_sync_time ) ) . ' ago' : 'Never' ); ?></p>
                        <p><strong><?php esc_html_e( 'Status:', 'alidrop' ); ?></strong> <span class="alidrop-status-<?php echo esc_attr( strtolower( $sync_status ) ); ?>"><?php echo esc_html( $sync_status ); ?></span></p>
                        <hr>
                        <?php
                        // We need to build the correct URL for the sync action
                        $sync_url = wp_nonce_url(
                            admin_url('admin.php?page=alidrop&action=alidrop_manual_sync'),
                            'alidrop_manual_sync_nonce',
                            'alidrop_nonce'
                        );
                        ?>
                        <p><a href="<?php echo esc_url($sync_url); ?>" class="button button-primary"><?php esc_html_e( 'Run Manual Sync', 'alidrop' ); ?></a></p>
                    </div>
                </div>
            </div>
            <div class="postbox-container">
                <div class="postbox">
                    <h2 class="hndle"><span><i class="dashicons dashicons-warning"></i> <?php esc_html_e( 'Recent Error Logs', 'alidrop' ); ?></span></h2>
                    <div class="inside">
                        <?php if ( ! empty( $recent_error_logs ) ) : ?>
                            <ul class="alidrop-error-logs">
                                <?php foreach ( $recent_error_logs as $log ) : ?>
                                    <li>
                                        <span class="log-time"><?php echo esc_html( date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( $log->created_at ) ) ); ?></span>
                                        <span class="log-message"><?php echo esc_html( $log->message ); ?></span>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php else : ?>
                            <p><?php esc_html_e( 'No recent errors. Everything looks good!', 'alidrop' ); ?></p>
                        <?php endif; ?>
                        <p><a href="<?php echo esc_url( admin_url( 'admin.php?page=alidrop-settings&tab=logs' ) ); ?>"><?php esc_html_e( 'View All Logs', 'alidrop' ); ?></a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<style>
.alidrop-dashboard .metabox-holder {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    grid-gap: 20px;
}
.alidrop-status-running {
    color: #ffb900; /* WordPress yellow */
    font-weight: bold;
}
.alidrop-status-idle {
    color: #444;
}
.alidrop-error-logs li {
    border-bottom: 1px solid #eee;
    padding: 8px 0;
}
.alidrop-error-logs li:last-child {
    border-bottom: none;
}
.alidrop-error-logs .log-time {
    font-weight: bold;
    display: block;
    color: #555;
}
</style>
