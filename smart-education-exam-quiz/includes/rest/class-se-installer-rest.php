<?php
/**
 * The REST API functionality for the installer.
 *
 * @package    Smart_Education_Exam_Quiz
 */

/**
 * The REST API functionality for the installer.
 *
 * @since      1.0.0
 * @package    Smart_Education_Exam_Quiz
 * @author     Jules
 */
class SE_Installer_REST_API {

    /**
     * Initialize the class and set up the hooks.
     *
     * @since    1.0.0
     */
    public static function init() {
        add_action( 'rest_api_init', array( __CLASS__, 'register_routes' ) );
    }

    /**
     * Register the REST API routes.
     *
     * @since    1.0.0
     */
    public static function register_routes() {
        register_rest_route( 'se/v1', '/installer/start', [
            'methods' => 'POST',
            'callback' => [ __CLASS__, 'start_import' ],
            'permission_callback' => [ __CLASS__, 'check_permissions' ],
        ]);
        register_rest_route( 'se/v1', '/installer/status', [
            'methods' => 'GET',
            'callback' => [ __CLASS__, 'get_status' ],
            'permission_callback' => [ __CLASS__, 'check_permissions' ],
        ]);
        register_rest_route( 'se/v1', '/installer/retry', [
            'methods' => 'POST',
            'callback' => [ __CLASS__, 'retry_import' ],
            'permission_callback' => [ __CLASS__, 'check_permissions' ],
        ]);
        register_rest_route( 'se/v1', '/installer/abort', [
            'methods' => 'POST',
            'callback' => [ __CLASS__, 'abort_import' ],
            'permission_callback' => [ __CLASS__, 'check_permissions' ],
        ]);

        register_rest_route( 'se/v1', '/installer/templates', [
            'methods' => 'GET',
            'callback' => [ __CLASS__, 'get_templates' ],
            'permission_callback' => [ __CLASS__, 'check_permissions' ],
        ]);
    }

    /**
     * Get the list of templates.
     *
     * @param WP_REST_Request $request
     * @return WP_REST_Response
     */
    public function get_templates( $request ) {
        $templates = [
            [ 'id' => 'modern_academic', 'name' => 'Modern Academic', 'features' => 'Clean, university-style UI.' ],
            [ 'id' => 'creative_learning', 'name' => 'Creative Learning', 'features' => 'Bright, playful, gamified UI.' ],
            [ 'id' => 'professional_lms', 'name' => 'Professional LMS', 'features' => 'Corporate feel with dark/light mode.' ],
        ];

        return new WP_REST_Response( apply_filters( 'se_installer_template_list', $templates ), 200 );
    }

    /**
     * Check if the user has permission to perform the action.
     *
     * @return bool
     */
    public static function check_permissions() {
        return current_user_can( 'manage_options' );
    }

    /**
     * Start the import process.
     *
     * @param WP_REST_Request $request
     * @return WP_REST_Response
     */
    public function start_import( $request ) {
        $job_id = 'se_installer_job_' . time();
        do_action( 'se_before_installer_start', $request->get_json_params() );

        $job_data = [
            'id' => $job_id,
            'status' => 'queued',
            'percent' => 0,
            'step' => 'Queued for processing...',
            'log' => [],
            'params' => $request->get_json_params(),
        ];

        update_option( $job_id, $job_data );

        // Schedule a one-off cron event.
        wp_schedule_single_event( time(), 'se_run_installer_job', [ $job_id ] );

        return new WP_REST_Response( [ 'job_id' => $job_id, 'status' => 'queued' ], 200 );
    }

    /**
     * Get the status of an import job.
     *
     * @param WP_REST_Request $request
     * @return WP_REST_Response
     */
    public function get_status( $request ) {
        $job_id = $request->get_param('job_id');
        $job_data = get_option( $job_id );

        if ( ! $job_data ) {
            return new WP_REST_Response( [ 'status' => 'not_found' ], 404 );
        }

        return new WP_REST_Response( $job_data, 200 );
    }

    /**
     * Retry an import job.
     *
     * @param WP_REST_Request $request
     * @return WP_REST_Response
     */
    public function retry_import( $request ) {
        // Placeholder
        return new WP_REST_Response( [ 'job_id' => '123', 'status' => 'queued' ], 200 );
    }

    /**
     * Abort an import job.
     *
     * @param WP_REST_Request $request
     * @return WP_REST_Response
     */
    public function abort_import( $request ) {
        // Placeholder
        return new WP_REST_Response( [ 'status' => 'aborted' ], 200 );
    }
}

SE_Installer_REST_API::init();
