<?php
/**
 * Fired when the plugin is uninstalled.
 *
 * @package    Smart_Education_Exam_Quiz
 */

// If uninstall not called from WordPress, then exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit;
}

global $wpdb;

// Delete custom database table.
$table_name = $wpdb->prefix . 'se_exam_results';
$wpdb->query( "DROP TABLE IF EXISTS $table_name" );

// Delete options.
delete_option( 'se_demo_content_imported' );
// Note: The installer job options are transient and will expire on their own.
// If there were other persistent options, they would be deleted here.

// Note: Roles are removed on deactivation, so no need to remove them here again.
// If the user just deletes the plugin without deactivating, the roles will remain.
// This is a trade-off. For a more robust solution, we could remove them here as well.
// But for now, we will stick to the deactivation hook for role removal.
