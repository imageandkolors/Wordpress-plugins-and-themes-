<?php
/**
 * REST API Controller
 *
 * @package AliDrop
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

/**
 * AliDrop_REST_API Class.
 */
class AliDrop_REST_API {

    /**
     * Namespace for the REST API.
     * @var string
     */
    protected $namespace = 'alidrop/v1';

    /**
     * Constructor.
     */
    public function __construct() {
        add_action( 'rest_api_init', array( $this, 'register_routes' ) );
    }

    /**
     * Register the routes for the objects of the controller.
     */
    public function register_routes() {
        register_rest_route( $this->namespace, '/status', array(
            'methods'  => WP_REST_Server::READABLE,
            'callback' => array( $this, 'get_status' ),
            'permission_callback' => array( $this, 'get_permissions_check' ),
        ) );

        register_rest_route( $this->namespace, '/sync', array(
            'methods'  => WP_REST_Server::CREATABLE,
            'callback' => array( $this, 'trigger_sync' ),
            'permission_callback' => array( $this, 'get_permissions_check' ),
        ) );
    }

    /**
     * Get plugin status.
     *
     * @param WP_REST_Request $request Full data about the request.
     * @return WP_Error|WP_REST_Response
     */
    public function get_status( $request ) {
        $query = new WC_Product_Query( array( 'limit' => -1, 'meta_key' => '_alidrop_product_id', 'return' => 'ids' ) );
        $total_synced_products = count( $query->get_products() );
        $last_sync_time = get_option( 'alidrop_last_sync_time' );
        $sync_status = get_transient( 'alidrop_sync_status' ) ? 'Running' : 'Idle';

        $data = array(
            'total_synced_products' => $total_synced_products,
            'last_sync_time'        => $last_sync_time,
            'sync_status'           => $sync_status,
        );

        return new WP_REST_Response( $data, 200 );
    }

    /**
     * Trigger a manual sync.
     *
     * @param WP_REST_Request $request Full data about the request.
     * @return WP_Error|WP_REST_Response
     */
    public function trigger_sync( $request ) {
        $result = AliDrop_Sync_Handler::sync_all_products();
        return new WP_REST_Response( $result, 200 );
    }

    /**
     * Check if a given request has access to get the status.
     *
     * @param WP_REST_Request $request Full data about the request.
     * @return WP_Error|bool
     */
    public function get_permissions_check( $request ) {
        return current_user_can( 'manage_woocommerce' );
    }
}
