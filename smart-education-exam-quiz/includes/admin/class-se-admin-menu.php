<?php
/**
 * The admin menu functionality of the plugin.
 *
 * @package    Smart_Education_Exam_Quiz
 */

/**
 * The admin menu functionality of the plugin.
 *
 * @since      1.0.0
 * @package    Smart_Education_Exam_Quiz
 * @author     Jules
 */
class SE_Admin_Menu {

    /**
     * Initialize the class and set up the hooks.
     *
     * @since    1.0.0
     */
    public static function init() {
        add_action( 'admin_menu', array( __CLASS__, 'add_admin_menu' ) );
    }

    /**
     * Add the admin menu page.
     *
     * @since    1.0.0
     */
    public static function add_admin_menu() {
        add_submenu_page(
            'edit.php?post_type=se_exam',
            __( 'Analytics', 'smart-education-exam-quiz' ),
            __( 'Analytics', 'smart-education-exam-quiz' ),
            'manage_options',
            'se-analytics',
            array( __CLASS__, 'render_analytics_page' )
        );

        add_submenu_page(
            'edit.php?post_type=se_exam',
            __( 'Grading Queue', 'smart-education-exam-quiz' ),
            __( 'Grading Queue', 'smart-education-exam-quiz' ),
            'manage_options', // Or a custom capability for teachers
            'se-grading-queue',
            array( __CLASS__, 'render_grading_queue_page' )
        );

        add_submenu_page(
            'edit.php?post_type=se_exam',
            __( 'Site Setup', 'smart-education-exam-quiz' ),
            __( 'Site Setup', 'smart-education-exam-quiz' ),
            'manage_options',
            'se-site-setup',
            array( __CLASS__, 'render_site_setup_page' )
        );
    }

    /**
     * Render the site setup page.
     *
     * @since    1.0.0
     */
    public static function render_site_setup_page() {
        if ( isset( $_POST['se_import_demo_content'] ) ) {
            SE_Demo_Content::import();
            echo '<div class="updated"><p>Demo content imported successfully!</p></div>';
        }
        ?>
        <div class="wrap">
            <h1><?php _e( 'Site Setup', 'smart-education-exam-quiz' ); ?></h1>
            <p><?php _e( 'Use these tools to set up your site with demo content and templates.', 'smart-education-exam-quiz' ); ?></p>

            <h2><?php _e( 'Demo Content', 'smart-education-exam-quiz' ); ?></h2>
            <form method="post">
                <p><?php _e( 'Click the button below to import sample exams and questions.', 'smart-education-exam-quiz' ); ?></p>
                <?php submit_button( __( 'Import Demo Content', 'smart-education-exam-quiz' ), 'primary', 'se_import_demo_content' ); ?>
            </form>

            <hr>

            <h2><?php _e( 'Elementor Templates', 'smart-education-exam-quiz' ); ?></h2>
            <p><?php _e( 'Download the template kits below and import them manually via the Elementor > Tools > Import / Export Kit menu.', 'smart-education-exam-quiz' ); ?></p>
            <ul>
                <li><a href="<?php echo plugin_dir_url( dirname( __FILE__ ) ) . 'templates/elementor-kits/modern-academic.json'; ?>" download>Modern Academic</a></li>
                <li><a href="<?php echo plugin_dir_url( dirname( __FILE__ ) ) . 'templates/elementor-kits/creative-learning.json'; ?>" download>Creative Learning</a></li>
                <li><a href="<?php echo plugin_dir_url( dirname( __FILE__ ) ) . 'templates/elementor-kits/professional-lms.json'; ?>" download>Professional LMS</a></li>
            </ul>
        </div>
        <?php
    }

    /**
     * Render the grading queue page.
     *
     * @since    1.0.0
     */
    public static function render_grading_queue_page() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'se_exam_results';

        $results_to_grade = $wpdb->get_results(
            "SELECT * FROM $table_name WHERE requires_manual_grading = 1 ORDER BY date_taken DESC"
        );
        ?>
        <div class="wrap">
            <h1><?php _e( 'Grading Queue', 'smart-education-exam-quiz' ); ?></h1>
            <p><?php _e( 'These exams contain theory questions and require manual grading.', 'smart-education-exam-quiz' ); ?></p>
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th><?php _e( 'Student', 'smart-education-exam-quiz' ); ?></th>
                        <th><?php _e( 'Exam', 'smart-education-exam-quiz' ); ?></th>
                        <th><?php _e( 'Date Taken', 'smart-education-exam-quiz' ); ?></th>
                        <th><?php _e( 'Status', 'smart-education-exam-quiz' ); ?></th>
                        <th><?php _e( 'Action', 'smart-education-exam-quiz' ); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ( ! empty( $results_to_grade ) ) : ?>
                        <?php foreach ( $results_to_grade as $result ) : ?>
                            <tr>
                                <td><?php echo get_user_by( 'id', $result->user_id )->display_name; ?></td>
                                <td><?php echo get_the_title( $result->exam_id ); ?></td>
                                <td><?php echo date_format( date_create( $result->date_taken ), 'F j, Y, g:i a' ); ?></td>
                                <td><?php echo ucfirst( $result->status ); ?></td>
                                <td><a href="#" class="button button-primary"><?php _e( 'Grade Now', 'smart-education-exam-quiz' ); ?></a></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <tr>
                            <td colspan="5"><?php _e( 'No exams to grade.', 'smart-education-exam-quiz' ); ?></td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php
    }

    /**
     * Render the analytics page.
     *
     * @since    1.0.0
     */
    public static function render_analytics_page() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'se_exam_results';

        // Get total exams taken
        $total_taken = $wpdb->get_var( "SELECT COUNT(*) FROM $table_name" );

        // Get overall average percentage
        $avg_percentage = $wpdb->get_var( "SELECT AVG(percentage) FROM $table_name" );

        // Get pass/fail rate
        $pass_count = $wpdb->get_var( "SELECT COUNT(*) FROM $table_name WHERE status = 'pass'" );
        $fail_count = $total_taken - $pass_count;
        $pass_rate = ( $total_taken > 0 ) ? ( $pass_count / $total_taken ) * 100 : 0;

        // Get recent results
        $recent_results = $wpdb->get_results( "SELECT * FROM $table_name ORDER BY date_taken DESC LIMIT 10" );
        ?>
        <div class="wrap">
            <h1><?php _e( 'Exam Analytics', 'smart-education-exam-quiz' ); ?></h1>

            <div id="dashboard-widgets-wrap">
                <div id="dashboard-widgets" class="metabox-holder">
                    <div class="postbox-container">
                        <div class="meta-box-sortables">
                            <div class="postbox">
                                <h2><span><?php _e( 'Overall Stats', 'smart-education-exam-quiz' ); ?></span></h2>
                                <div class="inside">
                                    <p><?php printf( __( 'Total Exams Taken: %d', 'smart-education-exam-quiz' ), $total_taken ); ?></p>
                                    <p><?php printf( __( 'Average Score: %s%%', 'smart-education-exam-quiz' ), round( $avg_percentage, 2 ) ); ?></p>
                                    <p><?php printf( __( 'Pass Rate: %s%%', 'smart-education-exam-quiz' ), round( $pass_rate, 2 ) ); ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <h2><?php _e( 'Recent Results', 'smart-education-exam-quiz' ); ?></h2>
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th><?php _e( 'Student', 'smart-education-exam-quiz' ); ?></th>
                        <th><?php _e( 'Exam', 'smart-education-exam-quiz' ); ?></th>
                        <th><?php _e( 'Date Taken', 'smart-education-exam-quiz' ); ?></th>
                        <th><?php _e( 'Score', 'smart-education-exam-quiz' ); ?></th>
                        <th><?php _e( 'Percentage', 'smart-education-exam-quiz' ); ?></th>
                        <th><?php _e( 'Status', 'smart-education-exam-quiz' ); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ( ! empty( $recent_results ) ) : ?>
                        <?php foreach ( $recent_results as $result ) : ?>
                            <tr>
                                <td><?php echo get_user_by( 'id', $result->user_id )->display_name; ?></td>
                                <td><?php echo get_the_title( $result->exam_id ); ?></td>
                                <td><?php echo date_format( date_create( $result->date_taken ), 'F j, Y, g:i a' ); ?></td>
                                <td><?php echo $result->score; ?> / <?php echo $result->max_score; ?></td>
                                <td><?php echo round( $result->percentage, 2 ); ?>%</td>
                                <td><?php echo ucfirst( $result->status ); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <tr>
                            <td colspan="6"><?php _e( 'No results found.', 'smart-education-exam-quiz' ); ?></td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php
    }
}

SE_Admin_Menu::init();
