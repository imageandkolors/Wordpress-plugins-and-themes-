<?php
/**
 * AliExpress API Client
 *
 * @package AliDrop
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

/**
 * AliDrop_AliExpress_API Class.
 */
class AliDrop_AliExpress_API {

    private $api_key;
    private $api_secret;
    private $api_endpoint = 'https://api.aliexpress.com/rest';

    public function __construct( $api_key, $api_secret ) {
        $this->api_key = $api_key;
        $this->api_secret = $api_secret;
    }

    private function are_credentials_valid() {
        return ! empty( $this->api_key ) && ! empty( $this->api_secret );
    }

    public function search_products( $keyword, $args = array() ) {
        if ( ! $this->are_credentials_valid() ) {
            return new WP_Error( 'api_credentials_missing', __( 'AliExpress API key or secret is not set.', 'alidrop' ) );
        }
        sleep(1);
        $products = array();
        for ( $i = 1; $i <= 10; $i++ ) {
            $products[] = array(
                'product_id'   => '100500' . rand(100000000, 999999999),
                'product_title' => 'Sample Product ' . $i . ' for "' . esc_html($keyword) . '"',
                'product_main_image_url' => 'https://via.placeholder.com/150x150.png?text=Product+' . $i,
                'product_detail_url' => 'https://www.aliexpress.com/item/12345.html',
                'product_description' => 'This is a sample product description for product ' . $i . '.',
                'target_original_price' => '25.99',
                'target_sale_price' => '19.99',
                'evaluate_rate' => '4.' . rand(5,9),
                'product_video_url' => '',
                'stock' => rand(10, 100),
                'variations' => array( array( 'name' => 'Color', 'options' => array('Red', 'Green', 'Blue') ), array( 'name' => 'Size', 'options' => array('S', 'M', 'L') ) ),
            );
        }
        return array( 'products' => $products, 'total_results' => 100, 'current_page' => isset($args['page']) ? $args['page'] : 1, 'total_pages' => 10 );
    }

    public function get_product_details( $product_id ) {
        if ( ! $this->are_credentials_valid() ) {
            return new WP_Error( 'api_credentials_missing', __( 'AliExpress API key or secret is not set.', 'alidrop' ) );
        }
        if ( rand( 1, 5 ) === 1 ) {
            return new WP_Error( 'api_error', __( 'Failed to connect to AliExpress API (simulated error).', 'alidrop' ) );
        }
        sleep(1);
        return array(
            'product_id'   => $product_id,
            'product_title' => 'Detailed Sample Product',
            'product_main_image_url' => 'https://via.placeholder.com/300x300.png?text=Product+' . $product_id,
            'product_images' => array( array('url' => 'https://via.placeholder.com/300x300.png?text=Image+1'), array('url' => 'https://via.placeholder.com/300x300.png?text=Image+2'), array('url' => 'https://via.placeholder.com/300x300.png?text=Image+3') ),
            'product_detail_url' => 'https://www.aliexpress.com/item/12345.html',
            'product_description' => 'This is a detailed sample product description. It has lots of information about the product features, specifications, and more.',
            'target_original_price' => '25.99',
            'target_sale_price' => '19.99',
            'evaluate_rate' => '4.8',
            'stock' => 50,
            'variations' => array(
                array( 'name' => 'Color', 'options' => array( array('name' => 'Red', 'image' => 'https://via.placeholder.com/50x50.png?text=Red'), array('name' => 'Green', 'image' => 'https://via.placeholder.com/50x50.png?text=Green'), array('name' => 'Blue', 'image' => 'https://via.placeholder.com/50x50.png?text=Blue') ) ),
                array( 'name' => 'Size', 'options' => array( array('name' => 'S'), array('name' => 'M'), array('name' => 'L') ) ),
            ),
            'sku_list' => array(
                array( 'sku_code' => 'RED-S', 'price' => '19.99', 'stock' => 10, 'attributes' => 'Color:Red,Size:S' ),
                array( 'sku_code' => 'RED-M', 'price' => '19.99', 'stock' => 15, 'attributes' => 'Color:Red,Size:M' ),
            )
        );
    }

    public function push_order( $order_data ) {
        if ( ! $this->are_credentials_valid() ) {
            return new WP_Error( 'api_credentials_missing', __( 'AliExpress API key or secret is not set.', 'alidrop' ) );
        }
        sleep(1);
        return array(
            'success'             => true,
            'aliexpress_order_id' => '814' . rand(1000000000, 9999999999),
        );
    }
}
