<?php
/**
 * The shortcodes of the plugin.
 *
 * @package    Smart_Education_Exam_Quiz
 */

/**
 * The shortcodes of the plugin.
 *
 * @since      1.0.0
 * @package    Smart_Education_Exam_Quiz
 * @author     Jules
 */
class SE_Shortcodes {

    /**
     * Initialize the class and set up the hooks.
     *
     * @since    1.0.0
     */
    public static function init() {
        add_shortcode( 'se_student_report', array( __CLASS__, 'student_report_shortcode' ) );
    }

    /**
     * The callback for the [se_student_report] shortcode.
     *
     * @since    1.0.0
     * @param    array    $atts    The shortcode attributes.
     * @return   string           The shortcode output.
     */
    public static function student_report_shortcode( $atts ) {
        if ( ! is_user_logged_in() ) {
            return '<p>' . __( 'You must be logged in to view your results.', 'smart-education-exam-quiz' ) . '</p>';
        }

        global $wpdb;
        $user_id = get_current_user_id();
        $table_name = $wpdb->prefix . 'se_exam_results';

        $results = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM $table_name WHERE user_id = %d ORDER BY date_taken DESC",
                $user_id
            )
        );

        ob_start();
        ?>
        <div class="se-student-report">
            <h3><?php _e( 'My Exam Results', 'smart-education-exam-quiz' ); ?></h3>
            <?php if ( ! empty( $results ) ) : ?>
                <table>
                    <thead>
                        <tr>
                            <th><?php _e( 'Exam', 'smart-education-exam-quiz' ); ?></th>
                            <th><?php _e( 'Date Taken', 'smart-education-exam-quiz' ); ?></th>
                            <th><?php _e( 'Score', 'smart-education-exam-quiz' ); ?></th>
                            <th><?php _e( 'Percentage', 'smart-education-exam-quiz' ); ?></th>
                            <th><?php _e( 'Status', 'smart-education-exam-quiz' ); ?></th>
                            <th><?php _e( 'Certificate', 'smart-education-exam-quiz' ); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ( $results as $result ) : ?>
                            <tr>
                                <td><?php echo get_the_title( $result->exam_id ); ?></td>
                                <td><?php echo date_format( date_create( $result->date_taken ), 'F j, Y, g:i a' ); ?></td>
                                <td><?php echo $result->score; ?> / <?php echo $result->max_score; ?></td>
                                <td><?php echo round( $result->percentage, 2 ); ?>%</td>
                                <td><?php echo ucfirst( $result->status ); ?></td>
                                <td>
                                    <?php if ( $result->status === 'pass' ) : ?>
                                        <a href="<?php echo esc_url( add_query_arg( array( 'se_action' => 'download_certificate', 'result_id' => $result->result_id ), home_url() ) ); ?>">
                                            <?php _e( 'Download', 'smart-education-exam-quiz' ); ?>
                                        </a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else : ?>
                <p><?php _e( 'You have not taken any exams yet.', 'smart-education-exam-quiz' ); ?></p>
            <?php endif; ?>
        </div>
        <?php
        return ob_get_clean();
    }
}

SE_Shortcodes::init();
