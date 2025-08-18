<?php
/**
 * Sync Settings
 *
 * @package AliDrop
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

$sync_url = wp_nonce_url(
    admin_url('admin.php?page=alidrop&tab=sync_settings&action=alidrop_manual_sync'),
    'alidrop_manual_sync_nonce',
    'alidrop_nonce'
);
?>
<div class="wrap">
    <form method="post" action="options.php">
        <?php
        settings_fields( 'alidrop_sync_settings' );
        do_settings_sections( 'alidrop_sync_settings' );
        submit_button();
        ?>
    </form>
    <hr>
    <h3><?php esc_html_e( 'Manual Sync', 'alidrop' ); ?></h3>
    <p><?php esc_html_e( 'You can also trigger a manual sync for all products.', 'alidrop' ); ?></p>
    <p><a href="<?php echo esc_url($sync_url); ?>" class="button button-secondary"><?php esc_html_e( 'Sync Now', 'alidrop' ); ?></a></p>
</div>
