<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @package    AliDrop
 * @subpackage AliDrop/admin
 * @author     Your Name <email@example.com>
 */
class AliDrop_Admin_Menu {

    private $plugin_name;
    private $version;

    public function __construct( $plugin_name, $version ) {
        $this->plugin_name = $plugin_name;
        $this->version     = $version;
    }

    /**
     * Register the stylesheets for the admin area.
     */
    public function enqueue_styles( $hook ) {
        if ( 'toplevel_page_alidrop' !== $hook && 'alidrop_page_alidrop-settings' !== $hook ) {
            return;
        }
        wp_enqueue_style(
            $this->plugin_name,
            plugin_dir_url( __FILE__ ) . '../assets/css/admin.css',
            array(),
            $this->version,
            'all'
        );
    }

    /**
     * Register the administration menu for this plugin into the WordPress Dashboard menu.
     */
    public function admin_menu() {
        add_menu_page( 'AliDrop', 'AliDrop', 'manage_options', 'alidrop', array( $this, 'dashboard_page' ), 'dashicons-cloud', 56 );
        add_submenu_page( 'alidrop', 'Settings', 'Settings', 'manage_options', 'alidrop-settings', array( $this, 'settings_page' ) );
    }

    /**
     * Render the dashboard page for the plugin.
     */
    public function dashboard_page() {
        $query = new WC_Product_Query( array( 'limit' => -1, 'meta_key' => '_alidrop_product_id', 'return' => 'ids' ) );
        $total_synced_products = count( $query->get_products() );
        $last_sync_time = get_option( 'alidrop_last_sync_time' );
        $sync_status = get_transient( 'alidrop_sync_status' ) ? 'Running' : 'Idle';
        global $wpdb;
        $logs_table = $wpdb->prefix . 'alidrop_logs';
        $recent_error_logs = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $logs_table WHERE type = %s ORDER BY created_at DESC LIMIT 5", 'error' ) );
        include_once ALIDROP_PLUGIN_DIR . 'admin/views/dashboard.php';
    }

    /**
     * Render the settings page for the plugin.
     */
    public function settings_page() {
        if ( isset( $_GET['disconnected'] ) ) { echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'API key has been disconnected.', 'alidrop' ) . '</p></div>'; }
        if ( isset( $_GET['synced'] ) ) {
            $type = $_GET['synced'] === 'true' ? 'success' : 'error';
            $message = isset( $_GET['message'] ) ? urldecode( $_GET['message'] ) : __( 'Sync process finished.', 'alidrop' );
            echo '<div class="notice notice-' . esc_attr($type) . ' is-dismissible"><p>' . esc_html( $message ) . '</p></div>';
        }

        $tabs = array( 'api_settings' => 'API Settings', 'product_import' => 'Product Import', 'sync_settings' => 'Sync Settings', 'logs' => 'Logs' );
        $current_tab = isset( $_GET['tab'] ) ? sanitize_key( $_GET['tab'] ) : 'api_settings';

        echo '<div class="wrap"><h1>' . esc_html( get_admin_page_title() ) . '</h1><nav class="nav-tab-wrapper">';
        foreach ( $tabs as $tab_id => $tab_name ) {
            $tab_url = add_query_arg( array( 'page' => 'alidrop-settings', 'tab'  => $tab_id ) );
            $active = $current_tab === $tab_id ? ' nav-tab-active' : '';
            echo '<a href="' . esc_url( $tab_url ) . '" class="nav-tab' . esc_attr( $active ) . '">' . esc_html( $tab_name ) . '</a>';
        }
        echo '</nav><div class="tab-content">';

        switch ( $current_tab ) {
            case 'api_settings': include_once ALIDROP_PLUGIN_DIR . 'admin/views/api-settings.php'; break;
            case 'product_import': $this->product_import_page(); break;
            case 'sync_settings': include_once ALIDROP_PLUGIN_DIR . 'admin/views/sync-settings.php'; break;
            case 'logs': include_once ALIDROP_PLUGIN_DIR . 'admin/views/logs.php'; break;
            default: include_once ALIDROP_PLUGIN_DIR . 'admin/views/api-settings.php'; break;
        }

        echo '</div></div>';
    }

    /**
     * Render the product import page.
     */
    public function product_import_page() {
        $search_results = array();
        $api_key = get_option( 'alidrop_api_key' );
        if ( ! empty( $_GET['s'] ) ) {
            if ( empty( $api_key ) ) {
                echo '<div class="notice notice-error is-dismissible"><p>' . esc_html__( 'Please enter your AliExpress API key in the API Settings tab to search for products.', 'alidrop' ) . '</p></div>';
            } else {
                $api = new AliDrop_AliExpress_API( $api_key );
                $search_results = $api->search_products( sanitize_text_field( $_GET['s'] ) );
            }
        }
        if ( isset( $_POST['import_product_id'] ) && check_admin_referer( 'alidrop_import_product_' . $_POST['import_product_id'] ) ) {
            $product_id_to_import = sanitize_text_field( $_POST['import_product_id'] );
            if ( empty( $api_key ) ) {
                echo '<div class="notice notice-error is-dismissible"><p>' . esc_html__( 'API key is missing.', 'alidrop' ) . '</p></div>';
            } else {
                $api = new AliDrop_AliExpress_API( $api_key );
                $product_data = $api->get_product_details( $product_id_to_import );
                if ( is_wp_error( $product_data ) ) {
                    echo '<div class="notice notice-error is-dismissible"><p>' . esc_html( $product_data->get_error_message() ) . '</p></div>';
                } else {
                    $result = AliDrop_Import_Handler::import_product( $product_data );
                    if ( is_wp_error( $result ) ) {
                        echo '<div class="notice notice-warning is-dismissible"><p>' . esc_html( $result->get_error_message() ) . '</p></div>';
                    } elseif ( is_array( $result ) && isset( $result['status'] ) && 'updated' === $result['status'] ) {
                        echo '<div class="notice notice-success is-dismissible"><p>' . sprintf( esc_html__( 'Product updated successfully. You can view it %shere%s.', 'alidrop' ), '<a href="' . esc_url( get_edit_post_link( $result['product_id'] ) ) . '" target="_blank">', '</a>' ) . '</p></div>';
                    } elseif ( is_int( $result ) ) {
                        echo '<div class="notice notice-success is-dismissible"><p>' . sprintf( esc_html__( 'Product imported successfully. You can view it %shere%s.', 'alidrop' ), '<a href="' . esc_url( get_edit_post_link( $result ) ) . '" target="_blank">', '</a>' ) . '</p></div>';
                    }
                }
            }
        }
        include_once ALIDROP_PLUGIN_DIR . 'admin/views/product-import.php';
    }

    /**
     * Register settings, sections, and fields.
     */
    public function register_settings() {
        register_setting( 'alidrop_api_settings', 'alidrop_api_key', array( 'sanitize_callback' => 'sanitize_text_field' ) );
        add_settings_section( 'alidrop_api_section', 'API Credentials', null, 'alidrop_api_settings' );
        add_settings_field( 'alidrop_api_key', 'API Key', array( $this, 'render_api_key_field' ), 'alidrop_api_settings', 'alidrop_api_section' );

        add_settings_section( 'alidrop_sync_section', 'Synchronization Settings', null, 'alidrop_sync_settings' );
        register_setting( 'alidrop_sync_settings', 'alidrop_autosync_enabled', array( 'sanitize_callback' => 'sanitize_text_field' ) );
        add_settings_field( 'alidrop_autosync_enabled', 'Enable Auto-Sync', array( $this, 'render_autosync_enabled_field' ), 'alidrop_sync_settings', 'alidrop_sync_section' );
        register_setting( 'alidrop_sync_settings', 'alidrop_sync_interval', array( 'sanitize_callback' => 'sanitize_text_field' ) );
        add_settings_field( 'alidrop_sync_interval', 'Sync Interval', array( $this, 'render_sync_interval_field' ), 'alidrop_sync_settings', 'alidrop_sync_section' );
        register_setting( 'alidrop_sync_settings', 'alidrop_selective_sync_options', array( 'sanitize_callback' => array( $this, 'sanitize_selective_sync_options' ) ) );
        add_settings_field( 'alidrop_selective_sync', 'Selective Sync', array( $this, 'render_selective_sync_field' ), 'alidrop_sync_settings', 'alidrop_sync_section' );
        register_setting( 'alidrop_sync_settings', 'alidrop_duplicate_handling', array( 'sanitize_callback' => 'sanitize_key' ) );
        add_settings_field( 'alidrop_duplicate_handling', 'Duplicate Product Handling', array( $this, 'render_duplicate_handling_field' ), 'alidrop_sync_settings', 'alidrop_sync_section' );
        register_setting( 'alidrop_sync_settings', 'alidrop_email_notifications_enabled', array( 'sanitize_callback' => 'sanitize_text_field' ) );
        add_settings_field( 'alidrop_email_notifications', 'Email Notifications', array( $this, 'render_email_notifications_field' ), 'alidrop_sync_settings', 'alidrop_sync_section' );
    }

    /* RENDER METHODS FOR SETTINGS FIELDS */
    public function render_api_key_field() {
        $api_key = get_option( 'alidrop_api_key' );
        echo '<input type="text" name="alidrop_api_key" value="' . esc_attr( $api_key ) . '" class="regular-text">';
        echo $this->get_tooltip_html(__( 'Enter your API key from your AliExpress developer account.', 'alidrop' ));
        if ( ! empty( $api_key ) ) {
            $masked_key = '****' . substr( $api_key, -4 );
            $disconnect_url = wp_nonce_url( admin_url( 'admin.php?page=alidrop-settings&tab=api_settings&action=alidrop_disconnect_api' ), 'alidrop_disconnect_api_nonce', 'alidrop_nonce' );
            echo '<p class="description">' . sprintf( esc_html__( 'Your API key is %s.', 'alidrop' ), '<code>' . esc_html( $masked_key ) . '</code>' ) . '</p>';
            echo '<p><a href="' . esc_url( $disconnect_url ) . '" class="button button-secondary">' . esc_html__( 'Disconnect', 'alidrop' ) . '</a></p>';
        }
    }
    public function render_autosync_enabled_field() {
        $option = get_option( 'alidrop_autosync_enabled' );
        echo '<label><input type="checkbox" name="alidrop_autosync_enabled" value="1" ' . checked( 1, $option, false ) . '> ' . __( 'Enable automatic background synchronization.', 'alidrop' ) . '</label>';
    }
    public function render_sync_interval_field() {
        $option = get_option( 'alidrop_sync_interval', 'daily' );
        $intervals = array( 'every_5_minutes'  => 'Every 5 Minutes', 'every_15_minutes' => 'Every 15 Minutes', 'every_30_minutes' => 'Every 30 Minutes', 'hourly' => 'Hourly', 'twicedaily' => 'Twice Daily', 'daily' => 'Daily' );
        echo '<select name="alidrop_sync_interval">';
        foreach ( $intervals as $value => $label ) { echo '<option value="' . esc_attr( $value ) . '" ' . selected( $option, $value, false ) . '>' . esc_html( $label ) . '</option>'; }
        echo '</select>';
        echo $this->get_tooltip_html(__( 'How often the plugin should automatically sync with AliExpress.', 'alidrop' ));
    }
    public function render_selective_sync_field() {
        $options = get_option( 'alidrop_selective_sync_options', array( 'price' => '1', 'stock' => '1' ) );
        $fields = array( 'price' => 'Sync Price', 'stock' => 'Sync Stock', 'description' => 'Sync Description & Name', 'images' => 'Sync Images' );
        foreach ( $fields as $key => $label ) {
            $checked = isset( $options[ $key ] ) ? 'checked="checked"' : '';
            echo '<label><input type="checkbox" name="alidrop_selective_sync_options[' . esc_attr( $key ) . ']" value="1" ' . $checked . '> ' . esc_html( $label ) . '</label><br>';
        }
        echo '<p class="description">' . __( 'Choose which product fields to update during synchronization.', 'alidrop' ) . '</p>';
    }
    public function render_duplicate_handling_field() {
        $option = get_option( 'alidrop_duplicate_handling', 'skip' );
        $options = array( 'skip' => 'Skip Import (Default)', 'update' => 'Update Existing Product' );
        echo '<select name="alidrop_duplicate_handling">';
        foreach ( $options as $value => $label ) { echo '<option value="' . esc_attr( $value ) . '" ' . selected( $option, $value, false ) . '>' . esc_html( $label ) . '</option>'; }
        echo '</select>';
        echo $this->get_tooltip_html(__( 'Choose what to do when importing a product that already exists in your store.', 'alidrop' ));
    }
    public function render_email_notifications_field() {
        $option = get_option( 'alidrop_email_notifications_enabled' );
        echo '<label><input type="checkbox" name="alidrop_email_notifications_enabled" value="1" ' . checked( 1, $option, false ) . '> ' . __( 'Enable email notifications for critical errors.', 'alidrop' ) . '</label>';
    }

    /* SANITIZE METHODS */
    public function sanitize_selective_sync_options( $input ) {
        $sanitized_input = array();
        if ( ! empty( $input ) && is_array( $input ) ) {
            $allowed_keys = array( 'price', 'stock', 'description', 'images' );
            foreach ( $input as $key => $value ) { if ( in_array( $key, $allowed_keys ) ) { $sanitized_input[ sanitize_key( $key ) ] = '1'; } }
        }
        return $sanitized_input;
    }

    /* ACTION HANDLERS */
    public function disconnect_api() {
        if ( isset( $_GET['action'] ) && 'alidrop_disconnect_api' === $_GET['action'] ) {
            if ( ! isset( $_GET['alidrop_nonce'] ) || ! wp_verify_nonce( $_GET['alidrop_nonce'], 'alidrop_disconnect_api_nonce' ) ) { wp_die( 'Invalid nonce.' ); }
            delete_option( 'alidrop_api_key' );
            wp_safe_redirect( admin_url( 'admin.php?page=alidrop-settings&tab=api_settings&disconnected=true' ) );
            exit;
        }
    }
    public function handle_manual_sync() {
        if ( isset( $_GET['action'] ) && 'alidrop_manual_sync' === $_GET['action'] ) {
            if ( ! isset( $_GET['alidrop_nonce'] ) || ! wp_verify_nonce( $_GET['alidrop_nonce'], 'alidrop_manual_sync_nonce' ) ) { wp_die( 'Invalid nonce.' ); }
            $result = AliDrop_Sync_Handler::sync_all_products();
            $redirect_url = add_query_arg( array( 'page' => 'alidrop', 'synced' => $result['success'] ? 'true' : 'false', 'message' => urlencode($result['message']) ), admin_url('admin.php') );
            wp_safe_redirect( $redirect_url );
            exit;
        }
    }

    /* HELPER METHODS */
    private function get_tooltip_html( $tip_text ) {
        return '<span class="alidrop-tooltip">?
                    <span class="tooltip-text">' . esc_html( $tip_text ) . '</span>
                </span>';
    }
}
