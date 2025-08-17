<?php
/**
 * Fired during plugin activation.
 *
 * @package    Smart_Education_Exam_Quiz
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Smart_Education_Exam_Quiz
 * @author     Jules
 */
class SE_Activator {

    /**
     * Short Description. (use period)
     *
     * Long Description.
     *
     * @since    1.0.0
     */
    public static function activate() {
        self::add_roles();
        self::create_database_tables();
        self::setup_pages();
        SE_Demo_Content::import();

        flush_rewrite_rules();
    }

    /**
     * Create the necessary pages for the plugin.
     */
    public static function setup_pages() {
        $pages = [
            'exams' => [
                'title' => 'Exams',
                'content' => '<!-- This page will display a list of exams. The functionality is not yet implemented. -->',
            ],
            'my-results' => [
                'title' => 'My Results',
                'content' => '[se_student_report]',
            ],
        ];

        foreach ( $pages as $slug => $page ) {
            if ( ! get_page_by_path( $slug ) ) {
                wp_insert_post([
                    'post_name' => $slug,
                    'post_title' => $page['title'],
                    'post_content' => $page['content'],
                    'post_status' => 'publish',
                    'post_type' => 'page',
                ]);
            }
        }
    }

    /**
     * Create custom database tables.
     */
    public static function create_database_tables() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'se_exam_results';
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE $table_name (
            result_id bigint(20) NOT NULL AUTO_INCREMENT,
            user_id bigint(20) NOT NULL,
            exam_id bigint(20) NOT NULL,
            score int(11) NOT NULL,
            max_score int(11) NOT NULL,
            percentage float NOT NULL,
            status varchar(20) NOT NULL,
            date_taken datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
            answers longtext NOT NULL,
            requires_manual_grading tinyint(1) NOT NULL DEFAULT 0,
            time_spent text NOT NULL,
            PRIMARY KEY  (result_id)
        ) $charset_collate;";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta( $sql );
    }

    /**
     * Add custom user roles.
     */
    public static function add_roles() {
        // Teacher role
        add_role(
            'se_teacher',
            __( 'Teacher', 'smart-education-exam-quiz' ),
            array(
                'read'                   => true,
                // Exam capabilities
                'edit_exam'              => true,
                'read_exam'              => true,
                'delete_exam'            => true,
                'edit_exams'             => true,
                'edit_others_exams'      => true,
                'publish_exams'          => true,
                'read_private_exams'     => true,
                'delete_exams'           => true,
                'delete_private_exams'   => true,
                'delete_published_exams' => true,
                'delete_others_exams'    => true,
                'edit_private_exams'     => true,
                'edit_published_exams'   => true,
                // Question capabilities
                'edit_question'          => true,
                'read_question'          => true,
                'delete_question'        => true,
                'edit_questions'         => true,
                'edit_others_questions'  => true,
                'publish_questions'      => true,
                'read_private_questions' => true,
                'delete_questions'       => true,
                'delete_private_questions'   => true,
                'delete_published_questions' => true,
                'delete_others_questions'    => true,
                'edit_private_questions'     => true,
                'edit_published_questions'   => true,
                // Other capabilities
                'upload_files'           => true,
            )
        );

        // Student role
        add_role(
            'se_student',
            __( 'Student', 'smart-education-exam-quiz' ),
            array(
                'read' => true,
                'read_exam' => true,
            )
        );
    }
}
