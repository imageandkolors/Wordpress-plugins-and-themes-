<?php
/**
 * Fired during plugin deactivation.
 *
 * @package    Smart_Education_Exam_Quiz
 */

/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @since      1.0.0
 * @package    Smart_Education_Exam_Quiz
 * @author     Jules
 */
class SE_Deactivator {

    /**
     * Short Description. (use period)
     *
     * Long Description.
     *
     * @since    1.0.0
     */
    public static function deactivate() {
        self::remove_roles();
        flush_rewrite_rules();
    }

    /**
     * Remove custom user roles.
     */
    public static function remove_roles() {
        remove_role( 'se_teacher' );
        remove_role( 'se_student' );
    }
}
