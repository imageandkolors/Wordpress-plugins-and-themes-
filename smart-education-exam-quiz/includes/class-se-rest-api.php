<?php
/**
 * The REST API functionality of the plugin.
 *
 * @package    Smart_Education_Exam_Quiz
 */

/**
 * The REST API functionality of the plugin.
 *
 * @since      1.0.0
 * @package    Smart_Education_Exam_Quiz
 * @author     Jules
 */
class SE_REST_API {

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
        register_rest_route(
            'se/v1',
            '/exams/(?P<id>\d+)/submit',
            array(
                'methods'             => 'POST',
                'callback'            => array( __CLASS__, 'submit_exam_callback' ),
                'permission_callback' => function () {
                    return is_user_logged_in();
                },
            )
        );
    }

    /**
     * The callback for the exam submission route.
     *
     * @since    1.0.0
     * @param    WP_REST_Request    $request    The request object.
     * @return   WP_REST_Response               The response object.
     */
    public static function submit_exam_callback( $request ) {
        global $wpdb;

        $exam_id = (int) $request['id'];
        $submitted_answers = $request->get_param( 'answers' );
        $time_spent_raw = $request->get_param( 'time_spent' );
        $time_spent = array();
        if ( is_array( $time_spent_raw ) ) {
            foreach ( $time_spent_raw as $q_id => $time ) {
                $time_spent[ intval( $q_id ) ] = intval( $time );
            }
        }
        $user_id = get_current_user_id();

        if ( empty( $submitted_answers ) ) {
            return new WP_REST_Response( array( 'message' => 'No answers submitted.' ), 400 );
        }

        $question_ids_str = get_post_meta( $exam_id, '_se_exam_questions', true );
        $question_ids = ! empty( $question_ids_str ) ? array_map( 'intval', explode( ',', $question_ids_str ) ) : array();

        if ( empty( $question_ids ) ) {
            return new WP_REST_Response( array( 'message' => 'No questions found for this exam.' ), 400 );
        }

        $score = 0;
        $max_score = 0;
        $requires_manual_grading = false;

        foreach ( $question_ids as $question_id ) {
            $terms = get_the_terms( $question_id, 'se_question_type' );
            $type = ! empty( $terms ) ? $terms[0]->slug : 'mcq';

            if ( $type === 'theory' ) {
                $requires_manual_grading = true;
            } else {
                // It's an MCQ, grade it.
                $max_score++;
                $options = get_post_meta( $question_id, '_se_mcq_options', true );
                $correct_answer_index = -1;

                if ( ! empty( $options ) ) {
                    foreach ( $options as $index => $option ) {
                        if ( $option['correct'] ) {
                            $correct_answer_index = $index;
                            break;
                        }
                    }
                }

                if ( isset( $submitted_answers[ $question_id ] ) && (int) $submitted_answers[ $question_id ] === $correct_answer_index ) {
                    $score++;
                }
            }
        }

        if ( $requires_manual_grading ) {
            $percentage = 0;
            $status = 'pending_grading';
        } else {
            $percentage = ( $max_score > 0 ) ? ( $score / $max_score ) * 100 : 0;
            $passing_score = (int) get_post_meta( $exam_id, '_se_passing_score', true );
            $status = $percentage >= $passing_score ? 'pass' : 'fail';
        }

        $table_name = $wpdb->prefix . 'se_exam_results';
        $wpdb->insert(
            $table_name,
            array(
                'user_id'                   => $user_id,
                'exam_id'                   => $exam_id,
                'score'                     => $score,
                'max_score'                 => $max_score,
                'percentage'                => $percentage,
                'status'                    => $status,
                'date_taken'                => current_time( 'mysql' ),
                'answers'                   => wp_json_encode( $submitted_answers ),
                'requires_manual_grading'   => $requires_manual_grading,
                'time_spent'                => wp_json_encode( $time_spent ),
            )
        );

        $result = array(
            'message'    => 'Exam graded successfully.',
            'score'      => $score,
            'max_score'  => $max_score,
            'percentage' => round( $percentage, 2 ),
            'status'     => $status,
        );

        return new WP_REST_Response( $result, 200 );
    }
}

SE_REST_API::init();
