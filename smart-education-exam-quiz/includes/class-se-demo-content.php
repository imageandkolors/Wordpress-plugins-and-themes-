<?php
/**
 * The demo content importer for the plugin.
 *
 * @package    Smart_Education_Exam_Quiz
 */

/**
 * The demo content importer for the plugin.
 *
 * @since      1.0.0
 * @package    Smart_Education_Exam_Quiz
 * @author     Jules
 */
class SE_Demo_Content {

    /**
     * Import the demo content.
     *
     * @since    1.0.0
     */
    public static function import() {
        if ( get_option( 'se_demo_content_imported' ) ) {
            return;
        }

        $questions = self::create_questions();
        self::create_exams( $questions );

        update_option( 'se_demo_content_imported', true );
    }

    /**
     * Create demo questions.
     *
     * @return array
     */
    private static function create_questions() {
        $question_ids = [];
        $questions_data = [
            [
                'title' => 'What is the capital of France?',
                'type' => 'mcq',
                'options' => [
                    ['text' => 'London', 'correct' => false],
                    ['text' => 'Paris', 'correct' => true],
                    ['text' => 'Berlin', 'correct' => false],
                ],
            ],
            [
                'title' => 'Explain the theory of relativity.',
                'type' => 'theory',
            ],
            [
                'title' => 'What is 2 + 2?',
                'type' => 'mcq',
                'options' => [
                    ['text' => '3', 'correct' => false],
                    ['text' => '4', 'correct' => true],
                    ['text' => '5', 'correct' => false],
                ],
            ],
        ];

        foreach ( $questions_data as $data ) {
            $question_id = wp_insert_post([
                'post_title' => $data['title'],
                'post_content' => '',
                'post_status' => 'publish',
                'post_type' => 'se_question',
            ]);

            if ( $question_id ) {
                wp_set_object_terms( $question_id, $data['type'], 'se_question_type' );
                if ( isset( $data['options'] ) ) {
                    update_post_meta( $question_id, '_se_mcq_options', $data['options'] );
                }
                $question_ids[] = $question_id;
            }
        }
        return $question_ids;
    }

    /**
     * Create demo exams.
     *
     * @param array $question_ids
     */
    private static function create_exams( $question_ids ) {
        $exams_data = [
            [
                'title' => 'General Knowledge Quiz',
                'duration' => 10,
                'passing_score' => 50,
                'questions' => $question_ids,
            ],
        ];

        foreach ( $exams_data as $data ) {
            $exam_id = wp_insert_post([
                'post_title' => $data['title'],
                'post_content' => 'This is a sample exam.',
                'post_status' => 'publish',
                'post_type' => 'se_exam',
            ]);

            if ( $exam_id ) {
                update_post_meta( $exam_id, '_se_exam_duration', $data['duration'] );
                update_post_meta( $exam_id, '_se_passing_score', $data['passing_score'] );
                update_post_meta( $exam_id, '_se_exam_questions', implode( ',', $data['questions'] ) );
            }
        }
    }
}
