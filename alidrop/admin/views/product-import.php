<?php
/**
 * Product Import
 *
 * @package AliDrop
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

// The $search_results variable is provided by the calling method in class-admin-menu.php
?>
<div class="wrap">
    <h2><?php esc_html_e( 'Product Import', 'alidrop' ); ?></h2>
    <p><?php esc_html_e( 'Search for products to import from AliExpress.', 'alidrop' ); ?></p>

    <form method="get">
        <input type="hidden" name="page" value="alidrop">
        <input type="hidden" name="tab" value="product_import">
        <p>
            <input type="text" name="s" placeholder="<?php esc_attr_e( 'Search by keyword or URL', 'alidrop' ); ?>" value="<?php echo isset( $_GET['s'] ) ? esc_attr( $_GET['s'] ) : ''; ?>" style="width: 300px;">
            <input type="submit" class="button button-primary" value="<?php esc_attr_e( 'Search Products', 'alidrop' ); ?>">
        </p>
    </form>

    <hr/>

    <h3><?php esc_html_e( 'Search Results', 'alidrop' ); ?></h3>

    <table class="wp-list-table widefat fixed striped">
        <thead>
            <tr>
                <th scope="col" class="manage-column" style="width: 80px;"><?php esc_html_e( 'Image', 'alidrop' ); ?></th>
                <th scope="col" class="manage-column"><?php esc_html_e( 'Product Name', 'alidrop' ); ?></th>
                <th scope="col" class="manage-column"><?php esc_html_e( 'Price', 'alidrop' ); ?></th>
                <th scope="col" class="manage-column"><?php esc_html_e( 'Actions', 'alidrop' ); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php
            if ( ! empty( $search_results ) && ! is_wp_error( $search_results ) && ! empty( $search_results['products'] ) ) {
                foreach ( $search_results['products'] as $product ) {
                    ?>
                    <tr>
                        <td><img src="<?php echo esc_url( $product['product_main_image_url'] ); ?>" style="max-width: 60px;"></td>
                        <td>
                            <a href="<?php echo esc_url( $product['product_detail_url'] ); ?>" target="_blank">
                                <?php echo esc_html( $product['product_title'] ); ?>
                            </a>
                        </td>
                        <td><?php echo esc_html( '$' . $product['target_sale_price'] ); ?></td>
                        <td>
                            <form method="post">
                                <input type="hidden" name="import_product_id" value="<?php echo esc_attr( $product['product_id'] ); ?>">
                                <?php wp_nonce_field( 'alidrop_import_product_' . $product['product_id'] ); ?>
                                <button type="submit" class="button button-primary"><?php esc_html_e( 'Import', 'alidrop' ); ?></button>
                            </form>
                        </td>
                    </tr>
                    <?php
                }
            } elseif ( ! empty( $_GET['s'] ) ) {
                ?>
                <tr>
                    <td colspan="4"><?php esc_html_e( 'No products found for your search term.', 'alidrop' ); ?></td>
                </tr>
                <?php
            } else {
                ?>
                <tr>
                    <td colspan="4"><?php esc_html_e( 'Please enter a search term to find products.', 'alidrop' ); ?></td>
                </tr>
                <?php
            }
            ?>
        </tbody>
    </table>
</div>
