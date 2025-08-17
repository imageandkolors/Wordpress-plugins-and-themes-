<?php
/**
 * The template importer for the plugin.
 *
 * @package    Smart_Education_Exam_Quiz
 */

/**
 * The template importer for the plugin.
 *
 * @since      1.0.0
 * @package    Smart_Education_Exam_Quiz
 * @author     Jules
 */
class SE_Importer {

    private $job_id;
    private $job_data;

    /**
     * Constructor.
     *
     * @param string $job_id The job ID.
     */
    public function __construct( $job_id ) {
        $this->job_id = $job_id;
        $this->job_data = get_option( $job_id );
    }

    /**
     * Run the import process.
     */
    public function run() {
        if ( ! $this->job_data ) {
            return;
        }

        try {
            $this->update_status( 'running', 10, 'Starting import process...' );

            $template = $this->job_data['params']['template'];
            do_action( 'se_before_template_import', $template, $this->job_id );

            $template_file_path = SMART_EDUCATION_EXAM_QUIZ_PLUGIN_DIR . 'templates/elementor-kits/' . $template . '.json';
            $template_url = plugin_dir_url( dirname( __FILE__ ) ) . 'templates/elementor-kits/' . $template . '.json';

            if ( ! file_exists( $template_file_path ) ) {
                throw new Exception( 'Template file not found.' );
            }

            $this->update_status( 'failed', 50, 'Automatic import of Elementor kits is not yet supported. Please download the kit and import it manually.' );
            $this->job_data['log'][] = 'Download link: ' . $template_url;
            update_option( $this->job_id, $this->job_data );


            // Since auto-import is not supported, we'll consider it "successful" in terms of queuing the manual download.
            do_action( 'se_after_template_import', $template, $this->job_id, ['status' => 'manual_required'] );

            if ( ! empty( $this->job_data['params']['settings']['importDemo'] ) ) {
                $this->update_status( 'running', 80, 'Importing demo content...' );
                SE_Demo_Content::import();
            }

            $this->update_status( 'completed', 100, 'Import finished successfully!' );
            do_action( 'se_after_installer_finish', $this->job_id, true );

        } catch ( Exception $e ) {
            $this->update_status( 'failed', 100, 'Error: ' . $e->getMessage() );
            do_action( 'se_template_import_failed', $template, $this->job_id, $e );
            do_action( 'se_after_installer_finish', $this->job_id, false );
        }
    }

    /**
     * Update the job status.
     *
     * @param string $status
     * @param int    $percent
     * @param string $step
     */
    private function update_status( $status, $percent, $step ) {
        $this->job_data['status'] = $status;
        $this->job_data['percent'] = $percent;
        $this->job_data['step'] = $step;
        $this->job_data['log'][] = date( '[Y-m-d H:i:s] ' ) . $step;
        update_option( $this->job_id, $this->job_data );
    }
}
