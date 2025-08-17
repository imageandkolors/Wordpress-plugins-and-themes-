<?php
/**
 * The download handler of the plugin.
 *
 * @package    Smart_Education_Exam_Quiz
 */

/**
 * The download handler of the plugin.
 *
 * @since      1.0.0
 * @package    Smart_Education_Exam_Quiz
 * @author     Jules
 */
class SE_Download_Handler {

    /**
     * Initialize the class and set up the hooks.
     *
     * @since    1.0.0
     */
    public static function init() {
        add_action( 'init', array( __CLASS__, 'handle_download' ) );
    }

    /**
     * Handle the download request.
     *
     * @since    1.0.0
     */
    public static function handle_download() {
        if ( isset( $_GET['se_action'] ) && $_GET['se_action'] === 'download_certificate' && isset( $_GET['result_id'] ) ) {
            $result_id = (int) $_GET['result_id'];

            if ( ! is_user_logged_in() ) {
                wp_die( __( 'You must be logged in to download this certificate.', 'smart-education-exam-quiz' ) );
            }

            global $wpdb;
            $table_name = $wpdb->prefix . 'se_exam_results';

            $result = $wpdb->get_row(
                $wpdb->prepare(
                    "SELECT * FROM $table_name WHERE result_id = %d",
                    $result_id
                )
            );

            if ( ! $result ) {
                wp_die( __( 'Invalid result ID.', 'smart-education-exam-quiz' ) );
            }

            // Check if the current user is the one who took the exam or an admin.
            if ( (int) $result->user_id !== get_current_user_id() && ! current_user_can( 'manage_options' ) ) {
                wp_die( __( 'You do not have permission to download this certificate.', 'smart-education-exam-quiz' ) );
            }

            require_once SMART_EDUCATION_EXAM_QUIZ_PLUGIN_DIR . 'includes/class-se-certificate-generator.php';
            SE_Certificate_Generator::generate( $result_id );
        }
    }
}

SE_Download_Handler::init();
