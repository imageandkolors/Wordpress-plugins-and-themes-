<?php
/**
 * The installation wizard functionality of the plugin.
 *
 * @package    Smart_Education_Exam_Quiz
 */

/**
 * The installation wizard functionality of the plugin.
 *
 * @since      1.0.0
 * @package    Smart_Education_Exam_Quiz
 * @author     Jules
 */
class SE_Installation_Wizard {

    /**
     * Initialize the class and set up the hooks.
     *
     * @since    1.0.0
     */
    public static function init() {
        add_action( 'admin_menu', array( __CLASS__, 'add_installer_page' ) );
        add_action( 'admin_init', array( __CLASS__, 'redirect_to_installer' ) );
        add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_scripts' ) );
        add_action( 'se_run_installer_job', array( __CLASS__, 'run_installer_job' ) );
    }

    /**
     * Run the installer job.
     *
     * @since    1.0.0
     * @param    string    $job_id    The job ID.
     */
    public static function run_installer_job( $job_id ) {
        require_once SMART_EDUCATION_EXAM_QUIZ_PLUGIN_DIR . 'includes/templates/importer.php';
        $importer = new SE_Importer( $job_id );
        $importer->run();
    }

    /**
     * Add the installer page.
     *
     * @since    1.0.0
     */
    public static function add_installer_page() {
        add_dashboard_page(
            '', // No menu title
            __( 'Smart Education Installer', 'smart-education-exam-quiz' ),
            'manage_options',
            'se-installer',
            array( __CLASS__, 'render_installer_page' )
        );
    }

    /**
     * Redirect to the installer page on activation.
     *
     * @since    1.0.0
     */
    public static function redirect_to_installer() {
        if ( get_option( 'se_redirect_to_installer' ) ) {
            delete_option( 'se_redirect_to_installer' );
            wp_safe_redirect( admin_url( 'index.php?page=se-installer' ) );
            exit;
        }
    }

    /**
     * Enqueue scripts and styles for the installer.
     *
     * @since    1.0.0
     */
    public static function enqueue_scripts( $hook ) {
        if ( 'dashboard_page_se-installer' !== $hook ) {
            return;
        }

        wp_enqueue_style(
            'se-installer-style',
            plugin_dir_url( dirname( __FILE__ ) ) . 'assets/css/installer.css',
            array(),
            SMART_EDUCATION_EXAM_QUIZ_VERSION
        );

        wp_enqueue_script(
            'se-installer-script',
            plugin_dir_url( dirname( __FILE__ ) ) . 'assets/js/installer.js',
            array( 'wp-element', 'wp-i18n', 'wp-api-fetch', 'wp-hooks' ),
            SMART_EDUCATION_EXAM_QUIZ_VERSION,
            true
        );

        wp_add_inline_script(
            'se-installer-script',
            'document.addEventListener("DOMContentLoaded", function() { if(window.seInstaller) { window.seInstaller.init(); } });'
        );
    }

    /**
     * Render the installer page.
     *
     * @since    1.0.0
     */
    public static function render_installer_page() {
        ?>
        <div class="wrap">
            <div id="se-installer-react-app">
                <noscript>
                    <p><?php _e( 'This installer requires JavaScript to run.', 'smart-education-exam-quiz' ); ?></p>
                </noscript>
                <p style="text-align: center; padding: 40px;"><?php _e( 'Loading installer...', 'smart-education-exam-quiz' ); ?></p>
            </div>
        </div>
        <?php
    }
}

SE_Installation_Wizard::init();
