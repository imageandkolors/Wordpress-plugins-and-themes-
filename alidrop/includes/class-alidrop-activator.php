<?php
/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    AliDrop
 * @subpackage AliDrop/includes
 * @author     Your Name <email@example.com>
 */
class AliDrop_Activator {

    /**
     * Create the logs table.
     *
     * @since    1.0.0
     */
    public static function activate() {
        global $wpdb;

        $table_name      = $wpdb->prefix . 'alidrop_logs';
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE $table_name (
            log_id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            type VARCHAR(20) NOT NULL,
            component VARCHAR(50) NOT NULL,
            message LONGTEXT NOT NULL,
            created_at DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
            PRIMARY KEY  (log_id)
        ) $charset_collate;";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta( $sql );
    }

}
