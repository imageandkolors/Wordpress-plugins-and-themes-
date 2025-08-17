<?php
/**
 * The PDF certificate generator of the plugin.
 *
 * @package    Smart_Education_Exam_Quiz
 */

/**
 * The PDF certificate generator of the plugin.
 *
 * @since      1.0.0
 * @package    Smart_Education_Exam_Quiz
 * @author     Jules
 */
class SE_Certificate_Generator {

    /**
     * Generate the PDF certificate.
     *
     * @since    1.0.0
     * @param    int    $result_id    The result ID.
     */
    public static function generate( $result_id ) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'se_exam_results';

        $result = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM $table_name WHERE result_id = %d",
                $result_id
            )
        );

        if ( ! $result || $result->status !== 'pass' ) {
            wp_die( __( 'Certificate not available.', 'smart-education-exam-quiz' ) );
        }

        $user = get_user_by( 'id', $result->user_id );
        $exam_title = get_the_title( $result->exam_id );

        require_once SMART_EDUCATION_EXAM_QUIZ_PLUGIN_DIR . 'vendor/fpdf/fpdf.php';

        $pdf = new FPDF( 'L', 'mm', 'A4' );
        $pdf->AddPage();
        $pdf->SetFont( 'Arial', 'B', 16 );

        // Title
        $pdf->Cell( 0, 20, 'Certificate of Achievement', 0, 1, 'C' );

        // Main content
        $pdf->SetFont( 'Arial', '', 12 );
        $pdf->MultiCell( 0, 10, "This is to certify that", 0, 'C' );

        $pdf->SetFont( 'Arial', 'B', 20 );
        $pdf->MultiCell( 0, 20, $user->display_name, 0, 'C' );

        $pdf->SetFont( 'Arial', '', 12 );
        $pdf->MultiCell( 0, 10, "has successfully completed the exam:", 0, 'C' );

        $pdf->SetFont( 'Arial', 'I', 16 );
        $pdf->MultiCell( 0, 20, $exam_title, 0, 'C' );

        $pdf->SetFont( 'Arial', '', 12 );
        $pdf->MultiCell( 0, 10, "on " . date_format( date_create( $result->date_taken ), 'F j, Y' ), 0, 'C' );

        $pdf->Output( 'D', 'certificate.pdf' );
        exit;
    }
}
