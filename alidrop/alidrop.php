<?php
/**
 * Plugin Name:       AliDrop
 * Plugin URI:        https://example.com/
 * Description:       AliExpress dropshipping solution for WooCommerce.
 * Version:           1.0.0
 * Author:            Your Name
 * Author URI:        https://example.com/
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       alidrop
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

/**
 * The code that runs during plugin activation.
 */
function activate_alidrop() {
    require_once plugin_dir_path( __FILE__ ) . 'includes/class-alidrop-activator.php';
    AliDrop_Activator::activate();
}

register_activation_hook( __FILE__, 'activate_alidrop' );

define( 'ALIDROP_VERSION', '1.0.0' );
define( 'ALIDROP_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'ALIDROP_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require ALIDROP_PLUGIN_DIR . 'includes/class-alidrop.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_alidrop() {

    $plugin = new AliDrop();
    $plugin->run();

}
run_alidrop();
