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
        flush_rewrite_rules();
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
