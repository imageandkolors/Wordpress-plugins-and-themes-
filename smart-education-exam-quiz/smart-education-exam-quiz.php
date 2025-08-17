<?php
/**
 * Plugin Name:       Smart Education Exam & Quiz
 * Plugin URI:        https://example.com/
 * Description:       A comprehensive exam and quiz plugin for WordPress, compatible with Elementor and any LMS.
 * Version:           1.0.0
 * Author:            Jules
 * Author URI:        https://example.com/
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       smart-education-exam-quiz
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

/**
 * Currently plugin version.
 */
define( 'SMART_EDUCATION_EXAM_QUIZ_VERSION', '1.0.0' );
define( 'SMART_EDUCATION_EXAM_QUIZ_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );


/**
 * The code that runs during plugin activation.
 */
function activate_smart_education_exam_quiz() {
    require_once SMART_EDUCATION_EXAM_QUIZ_PLUGIN_DIR . 'includes/class-se-activator.php';
    SE_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 */
function deactivate_smart_education_exam_quiz() {
    require_once SMART_EDUCATION_EXAM_QUIZ_PLUGIN_DIR . 'includes/class-se-deactivator.php';
    SE_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_smart_education_exam_quiz' );
register_deactivation_hook( __FILE__, 'deactivate_smart_education_exam_quiz' );


/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require_once SMART_EDUCATION_EXAM_QUIZ_PLUGIN_DIR . 'includes/post-types.php';
require_once SMART_EDUCATION_EXAM_QUIZ_PLUGIN_DIR . 'includes/class-se-meta-boxes.php';
require_once SMART_EDUCATION_EXAM_QUIZ_PLUGIN_DIR . 'includes/elementor/elementor.php';
require_once SMART_EDUCATION_EXAM_QUIZ_PLUGIN_DIR . 'includes/class-se-rest-api.php';
require_once SMART_EDUCATION_EXAM_QUIZ_PLUGIN_DIR . 'includes/shortcodes.php';
require_once SMART_EDUCATION_EXAM_QUIZ_PLUGIN_DIR . 'includes/admin/class-se-admin-menu.php';
require_once SMART_EDUCATION_EXAM_QUIZ_PLUGIN_DIR . 'includes/class-se-download-handler.php';
require_once SMART_EDUCATION_EXAM_QUIZ_PLUGIN_DIR . 'includes/class-se-demo-content.php';


/**
 * Enqueue frontend scripts and styles.
 */
function se_enqueue_frontend_assets() {
    wp_register_style(
        'se-frontend-style',
        plugin_dir_url( __FILE__ ) . 'assets/css/frontend.css',
        array(),
        SMART_EDUCATION_EXAM_QUIZ_VERSION
    );

    wp_register_script(
        'se-frontend-script',
        plugin_dir_url( __FILE__ ) . 'assets/js/frontend.js',
        array( 'wp-api-fetch' ), // Add wp-api-fetch as a dependency
        SMART_EDUCATION_EXAM_QUIZ_VERSION,
        true
    );

    // Pass the REST API URL and nonce to the script
    wp_localize_script(
        'se-frontend-script',
        'se_exam_ajax',
        array(
            'root'  => esc_url_raw( rest_url() ),
            'nonce' => wp_create_nonce( 'wp_rest' ),
        )
    );
}
add_action( 'wp_enqueue_scripts', 'se_enqueue_frontend_assets' );
