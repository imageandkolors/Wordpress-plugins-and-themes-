<?php
/**
 * Logs
 *
 * @package AliDrop
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

require_once ALIDROP_PLUGIN_DIR . 'admin/class-logs-list-table.php';

$logs_table = new AliDrop_Logs_List_Table();
$logs_table->prepare_items();
?>
<div class="wrap">
    <h1 class="wp-heading-inline"><?php esc_html_e( 'Logs', 'alidrop' ); ?></h1>
    <p><?php esc_html_e( 'This page displays a log of all synchronization activities and errors.', 'alidrop' ); ?></p>
    <form method="post">
        <input type="hidden" name="page" value="alidrop_logs">
        <?php
        $logs_table->display();
        ?>
    </form>
</div>
<style>
    .alidrop-log-type {
        display: inline-block;
        padding: 2px 6px;
        border-radius: 4px;
        color: #fff;
        font-weight: bold;
        text-transform: capitalize;
    }
    .alidrop-log-type.success { background-color: #4CAF50; }
    .alidrop-log-type.error { background-color: #F44336; }
    .alidrop-log-type.info { background-color: #2196F3; }
</style>
