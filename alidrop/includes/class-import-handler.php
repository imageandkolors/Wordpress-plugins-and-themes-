<?php
/**
 * Product Import Handler
 *
 * @package AliDrop
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

/**
 * AliDrop_Import_Handler Class.
 */
class AliDrop_Import_Handler {

    /**
     * Main import function. Determines if a product is new, a duplicate, simple, or variable.
     *
     * @param array $product_data
     * @return int|WP_Error|array The result of the import.
     */
    public static function import_product( $product_data ) {
        $product_data = apply_filters( 'alisync_before_import_data', $product_data );
        $ali_product_id = $product_data['product_id'];

        if ( ! function_exists( 'wc_create_product' ) ) {
            $error_msg = __( 'WooCommerce is not active.', 'alidrop' );
            AliDrop_Logger::log( 'error', 'import', $error_msg );
            return new WP_Error( 'woocommerce_not_active', $error_msg );
        }

        $existing_product_id = self::get_product_by_aliexpress_id( $ali_product_id );
        if ( $existing_product_id ) {
            return self::handle_duplicate_product( $existing_product_id, $product_data );
        }

        if ( ! empty( $product_data['variations'] ) && ! empty( $product_data['sku_list'] ) ) {
            return self::create_variable_product( $product_data );
        } else {
            return self::create_simple_product( $product_data );
        }
    }

    /**
     * Handles what to do when an imported product already exists.
     */
    private static function handle_duplicate_product( $existing_product_id, $product_data ) {
        $duplicate_handling = get_option( 'alidrop_duplicate_handling', 'skip' );

        if ( 'skip' === $duplicate_handling ) {
            $error_msg = sprintf( __( 'Product with AliExpress ID %s already exists and was skipped.', 'alidrop' ), $product_data['product_id'] );
            AliDrop_Logger::log( 'info', 'import', $error_msg, $existing_product_id );
            return new WP_Error( 'product_exists_skipped', $error_msg );
        }

        if ( 'update' === $duplicate_handling ) {
            $product = wc_get_product( $existing_product_id );
            if ( ! $product ) {
                $error_msg = sprintf( __( 'Could not find existing product with ID %d to update.', 'alidrop' ), $existing_product_id );
                AliDrop_Logger::log( 'error', 'import', $error_msg, $existing_product_id );
                return new WP_Error( 'product_not_found', $error_msg );
            }

            $product->set_name( $product_data['product_title'] );
            $product->set_description( $product_data['product_description'] );
            if ( $product->is_type( 'simple' ) ) {
                $product->set_regular_price( $product_data['target_sale_price'] );
                $product->set_stock_quantity( $product_data['stock'] );
            }
            $product->save();

            AliDrop_Logger::log( 'success', 'import', "Product #$existing_product_id updated from AliExpress product " . $product_data['product_id'] . ".", $existing_product_id );
            do_action( 'alisync_after_import', $existing_product_id, $product_data, 'updated' );
            return array('status' => 'updated', 'product_id' => $existing_product_id);
        }

        return $existing_product_id;
    }

    /**
     * Creates a new simple product.
     */
    private static function create_simple_product( $product_data ) {
        $product = new WC_Product_Simple();
        $product->set_name( $product_data['product_title'] );
        $product->set_description( $product_data['product_description'] );
        $product->set_regular_price( $product_data['target_sale_price'] );
        $product->set_stock_quantity( $product_data['stock'] );
        $product->set_manage_stock( true );
        $product->set_status( 'publish' );

        if ( ! empty( $product_data['product_main_image_url'] ) ) {
            $image_id = self::upload_image_from_url( $product_data['product_main_image_url'] );
            if ( ! is_wp_error( $image_id ) ) {
                $product->set_image_id( $image_id );
            } else {
                AliDrop_Logger::log( 'warning', 'import', "Could not import image for AliExpress product " . $product_data['product_id'] . ": " . $image_id->get_error_message() );
            }
        }

        $product_id = $product->save();

        if ( $product_id ) {
            update_post_meta( $product_id, '_alidrop_product_id', $product_data['product_id'] );
            update_post_meta( $product_id, '_alidrop_product_url', $product_data['product_detail_url'] );
            AliDrop_Logger::log( 'success', 'import', "New simple product #$product_id created from AliExpress product " . $product_data['product_id'] . ".", $product_id );
            do_action( 'alisync_after_import', $product_id, $product_data, 'created' );
        } else {
            AliDrop_Logger::log( 'error', 'import', "Failed to create simple product from AliExpress product " . $product_data['product_id'] . "." );
        }

        return $product_id;
    }

    /**
     * Creates a new variable product.
     */
    private static function create_variable_product( $product_data ) {
        $product = new WC_Product_Variable();
        $product->set_name( $product_data['product_title'] );
        $product->set_description( $product_data['product_description'] );
        $product->set_status( 'publish' );

        $attributes = self::prepare_attributes( $product_data['variations'] );
        $product->set_attributes( $attributes );
        $product_id = $product->save();

        if ( $product_id ) {
            self::create_variations( $product_id, $product_data['sku_list'] );

            if ( ! empty( $product_data['product_main_image_url'] ) ) {
                $image_id = self::upload_image_from_url( $product_data['product_main_image_url'] );
                if ( ! is_wp_error( $image_id ) ) {
                    $product->set_image_id( $image_id );
                    $product->save();
                }
            }

            update_post_meta( $product_id, '_alidrop_product_id', $product_data['product_id'] );
            update_post_meta( $product_id, '_alidrop_product_url', $product_data['product_detail_url'] );
            AliDrop_Logger::log( 'success', 'import', "New variable product #$product_id created from AliExpress product " . $product_data['product_id'] . ".", $product_id );
            do_action( 'alisync_after_import', $product_id, $product_data, 'created' );
        } else {
            AliDrop_Logger::log( 'error', 'import', "Failed to create variable product from AliExpress product " . $product_data['product_id'] . "." );
        }

        return $product_id;
    }

    /**
     * Prepares an array of WC_Product_Attribute objects from API data.
     */
    private static function prepare_attributes( $variation_data ) {
        $attributes = array();
        $position = 0;
        foreach ( $variation_data as $variation_item ) {
            $attribute = new WC_Product_Attribute();
            $attribute->set_name( $variation_item['name'] );
            $options = array_map(function($option) { return $option['name']; }, $variation_item['options']);
            $attribute->set_options( $options );
            $attribute->set_position( $position++ );
            $attribute->set_visible( true );
            $attribute->set_variation( true );
            $attributes[] = $attribute;
        }
        return $attributes;
    }

    /**
     * Creates and saves the individual variations for a variable product.
     */
    private static function create_variations( $product_id, $sku_list ) {
        foreach ( $sku_list as $sku_item ) {
            $variation = new WC_Product_Variation();
            $variation->set_parent_id( $product_id );

            $attributes = array();
            $attribute_pairs = explode( ',', $sku_item['attributes'] );
            foreach ( $attribute_pairs as $pair ) {
                list( $name, $value ) = explode( ':', $pair );
                $attributes[ sanitize_title( $name ) ] = $value;
            }
            $variation->set_attributes( $attributes );

            $variation->set_regular_price( $sku_item['price'] );
            $variation->set_stock_quantity( $sku_item['stock'] );
            $variation->set_manage_stock( true );
            $variation->save();
        }
    }

    public static function get_product_by_aliexpress_id( $aliexpress_id ) {
        $query = new WC_Product_Query( array( 'limit' => 1, 'meta_key' => '_alidrop_product_id', 'meta_value' => $aliexpress_id, 'return' => 'ids' ) );
        $products = $query->get_products();
        return ! empty( $products ) ? $products[0] : 0;
    }

    public static function upload_image_from_url( $image_url ) {
        require_once( ABSPATH . 'wp-admin/includes/image.php' );
        require_once( ABSPATH . 'wp-admin/includes/file.php' );
        require_once( ABSPATH . 'wp-admin/includes/media.php' );
        $tmp = download_url( $image_url );
        if ( is_wp_error( $tmp ) ) return $tmp;
        $file_array = array( 'name' => basename( strtok($image_url, '?') ), 'tmp_name' => $tmp );
        if ( is_wp_error( $tmp ) ) { @unlink($file_array['tmp_name']); return $tmp; }
        $id = media_handle_sideload( $file_array, 0 );
        if ( is_wp_error( $id ) ) { @unlink( $file_array['tmp_name'] ); return $id; }
        return $id;
    }
}
