<?php
/**
 * Logs List Table
 *
 * @package AliDrop
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

if ( ! class_exists( 'WP_List_Table' ) ) {
    require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

/**
 * AliDrop_Logs_List_Table Class.
 */
class AliDrop_Logs_List_Table extends WP_List_Table {

    /**
     * Constructor.
     */
    public function __construct() {
        parent::__construct(
            array(
                'singular' => 'log',
                'plural'   => 'logs',
                'ajax'     => false,
            )
        );
    }

    /**
     * Get columns.
     *
     * @return array
     */
    public function get_columns() {
        return array(
            'cb'         => '<input type="checkbox" />',
            'type'       => __( 'Type', 'alidrop' ),
            'component'  => __( 'Component', 'alidrop' ),
            'message'    => __( 'Message', 'alidrop' ),
            'created_at' => __( 'Date', 'alidrop' ),
        );
    }

    /**
     * Prepare items.
     */
    public function prepare_items() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'alidrop_logs';

        $per_page              = 20;
        $columns               = $this->get_columns();
        $hidden                = array();
        $sortable              = $this->get_sortable_columns();
        $this->_column_headers = array( $columns, $hidden, $sortable );

        $current_page = $this->get_pagenum();
        $total_items  = $wpdb->get_var( "SELECT COUNT(log_id) FROM $table_name" );

        $this->set_pagination_args(
            array(
                'total_items' => $total_items,
                'per_page'    => $per_page,
            )
        );

        $orderby = ( ! empty( $_REQUEST['orderby'] ) && in_array( $_REQUEST['orderby'], array_keys( $this->get_sortable_columns() ) ) ) ? $_REQUEST['orderby'] : 'created_at';
        $order   = ( ! empty( $_REQUEST['order'] ) && in_array( strtoupper( $_REQUEST['order'] ), array( 'ASC', 'DESC' ) ) ) ? $_REQUEST['order'] : 'DESC';

        $offset = ( $current_page - 1 ) * $per_page;

        $this->items = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM $table_name ORDER BY $orderby $order LIMIT %d OFFSET %d",
                $per_page,
                $offset
            ),
            ARRAY_A
        );
    }

    /**
     * Column default.
     *
     * @param object $item
     * @param string $column_name
     * @return mixed
     */
    public function column_default( $item, $column_name ) {
        switch ( $column_name ) {
            case 'type':
                return '<span class="alidrop-log-type ' . esc_attr( $item['type'] ) . '">' . esc_html( $item['type'] ) . '</span>';
            case 'component':
            case 'message':
            case 'created_at':
                return esc_html( $item[ $column_name ] );
            default:
                return print_r( $item, true ); // Should not be called.
        }
    }

    /**
     * Column checkbox.
     *
     * @param object $item
     * @return string
     */
    public function column_cb( $item ) {
        return sprintf(
            '<input type="checkbox" name="log[]" value="%s" />',
            $item['log_id']
        );
    }

    /**
     * Get sortable columns.
     *
     * @return array
     */
    protected function get_sortable_columns() {
        return array(
            'type'       => array( 'type', false ),
            'component'  => array( 'component', false ),
            'created_at' => array( 'created_at', true ),
        );
    }

    /**
     * No items found text.
     */
    public function no_items() {
        _e( 'No logs found.', 'alidrop' );
    }
}
