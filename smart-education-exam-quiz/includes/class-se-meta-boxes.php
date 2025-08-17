<?php
/**
 * The file that defines the custom meta boxes for the plugin.
 *
 * @link       https://example.com
 * @since      1.0.0
 *
 * @package    Smart_Education_Exam_Quiz
 * @subpackage Smart_Education_Exam_Quiz/includes
 */

/**
 * The custom meta boxes for the plugin.
 *
 * @package    Smart_Education_Exam_Quiz
 * @subpackage Smart_Education_Exam_Quiz/includes
 * @author     Jules
 */
class SE_Meta_Boxes {

    /**
     * Initialize the class and set up the hooks.
     *
     * @since    1.0.0
     */
    public static function init() {
        add_action( 'add_meta_boxes', array( __CLASS__, 'add_meta_boxes' ) );
        add_action( 'save_post', array( __CLASS__, 'save_meta_boxes' ) );
        add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_scripts' ) );
    }

    /**
     * Enqueue scripts and styles for the admin area.
     *
     * @since    1.0.0
     */
    public static function enqueue_scripts( $hook ) {
        global $post;

        if ( $hook == 'post-new.php' || $hook == 'post.php' ) {
            if ( isset( $post->post_type ) && 'se_question' === $post->post_type ) {
                wp_enqueue_script( 'se-admin-script', plugin_dir_url( dirname( __FILE__ ) ) . 'assets/js/admin.js', array( 'jquery' ), SMART_EDUCATION_EXAM_QUIZ_VERSION, true );
            }
        }
    }

    /**
     * Add the meta boxes.
     *
     * @since    1.0.0
     */
    public static function add_meta_boxes() {
        add_meta_box(
            'se_exam_settings',
            __( 'Exam Settings', 'smart-education-exam-quiz' ),
            array( __CLASS__, 'render_exam_settings_meta_box' ),
            'se_exam',
            'normal',
            'high'
        );

        add_meta_box(
            'se_question_settings',
            __( 'Question Settings', 'smart-education-exam-quiz' ),
            array( __CLASS__, 'render_question_settings_meta_box' ),
            'se_question',
            'normal',
            'high'
        );
    }

    /**
     * Render the exam settings meta box.
     *
     * @since    1.0.0
     * @param    WP_Post    $post    The post object.
     */
    public static function render_exam_settings_meta_box( $post ) {
        // Add a nonce field so we can check for it later.
        wp_nonce_field( 'se_exam_settings_meta_box', 'se_exam_settings_meta_box_nonce' );

        // Use get_post_meta to retrieve an existing value from the database.
        $duration = get_post_meta( $post->ID, '_se_exam_duration', true );
        $passing_score = get_post_meta( $post->ID, '_se_passing_score', true );
        $question_ids = get_post_meta( $post->ID, '_se_exam_questions', true );

        // Display the form, using the current values.
        ?>
        <p>
            <label for="se_exam_duration">
                <?php _e( 'Duration (in minutes)', 'smart-education-exam-quiz' ); ?>
            </label>
            <input type="number" id="se_exam_duration" name="se_exam_duration" value="<?php echo esc_attr( $duration ); ?>" size="25" />
        </p>
        <p>
            <label for="se_passing_score">
                <?php _e( 'Passing Score (%)', 'smart-education-exam-quiz' ); ?>
            </label>
            <input type="number" id="se_passing_score" name="se_passing_score" value="<?php echo esc_attr( $passing_score ); ?>" size="25" />
        </p>
        <p>
            <label for="se_exam_questions">
                <?php _e( 'Question IDs (comma-separated)', 'smart-education-exam-quiz' ); ?>
            </label>
            <textarea id="se_exam_questions" name="se_exam_questions" rows="5" cols="50"><?php echo esc_textarea( $question_ids ); ?></textarea>
        </p>
        <?php
    }

    /**
     * Render the question settings meta box.
     *
     * @since    1.0.0
     * @param    WP_Post    $post    The post object.
     */
    public static function render_question_settings_meta_box( $post ) {
        // Add a nonce field so we can check for it later.
        wp_nonce_field( 'se_question_settings_meta_box', 'se_question_settings_meta_box_nonce' );

        // Use get_post_meta to retrieve an existing value from the database.
        $timer = get_post_meta( $post->ID, '_se_question_timer', true );
        $options = get_post_meta( $post->ID, '_se_mcq_options', true );

        // Display the form, using the current values.
        ?>
        <p>
            <label for="se_question_timer">
                <?php _e( 'Timer (in seconds)', 'smart-education-exam-quiz' ); ?>
            </label>
            <input type="number" id="se_question_timer" name="se_question_timer" value="<?php echo esc_attr( $timer ); ?>" size="25" />
        </p>

        <hr>

        <h4><?php _e( 'MCQ Options', 'smart-education-exam-quiz' ); ?></h4>

        <div id="se-mcq-options-wrapper">
            <?php
            if ( ! empty( $options ) && is_array( $options ) ) {
                foreach ( $options as $index => $option ) {
                    ?>
                    <div class="se-mcq-option">
                        <input type="text" name="se_mcq_options[<?php echo $index; ?>][text]" value="<?php echo esc_attr( $option['text'] ); ?>" size="50" />
                        <label>
                            <input type="radio" name="se_mcq_correct_option" value="<?php echo $index; ?>" <?php checked( $option['correct'] ); ?> />
                            <?php _e( 'Correct Answer', 'smart-education-exam-quiz' ); ?>
                        </label>
                        <a href="#" class="se-remove-option button"><?php _e( 'Remove', 'smart-education-exam-quiz' ); ?></a>
                    </div>
                    <?php
                }
            }
            ?>
        </div>

        <a href="#" id="se-add-option" class="button"><?php _e( 'Add Option', 'smart-education-exam-quiz' ); ?></a>

        <script type="text/template" id="se-mcq-option-template">
            <div class="se-mcq-option">
                <input type="text" name="se_mcq_options[{index}][text]" value="" size="50" />
                <label>
                    <input type="radio" name="se_mcq_correct_option" value="{index}" />
                    <?php _e( 'Correct Answer', 'smart-education-exam-quiz' ); ?>
                </label>
                <a href="#" class="se-remove-option button"><?php _e( 'Remove', 'smart-education-exam-quiz' ); ?></a>
            </div>
        </script>
        <?php
    }


    /**
     * Save the meta box data.
     *
     * @since    1.0.0
     * @param    int    $post_id    The post ID.
     */
    public static function save_meta_boxes( $post_id ) {
        // Check if our nonce is set.
        if ( ! isset( $_POST['se_exam_settings_meta_box_nonce'] ) && ! isset( $_POST['se_question_settings_meta_box_nonce'] ) ) {
            return;
        }

        // Verify that the nonce is valid.
        if ( isset( $_POST['se_exam_settings_meta_box_nonce'] ) && ! wp_verify_nonce( $_POST['se_exam_settings_meta_box_nonce'], 'se_exam_settings_meta_box' ) ) {
            return;
        }
        if ( isset( $_POST['se_question_settings_meta_box_nonce'] ) && ! wp_verify_nonce( $_POST['se_question_settings_meta_box_nonce'], 'se_question_settings_meta_box' ) ) {
            return;
        }

        // If this is an autosave, our form has not been submitted, so we don't want to do anything.
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return;
        }

        // Check the user's permissions.
        if ( isset( $_POST['post_type'] ) && 'se_exam' == $_POST['post_type'] ) {
            if ( ! current_user_can( 'edit_exam', $post_id ) ) {
                return;
            }
        }

        if ( isset( $_POST['post_type'] ) && 'se_question' == $_POST['post_type'] ) {
            if ( ! current_user_can( 'edit_question', $post_id ) ) {
                return;
            }
        }

        /* OK, it's safe for us to save the data now. */

        // Make sure that fields are set.
        if ( isset( $_POST['se_exam_duration'] ) ) {
            update_post_meta( $post_id, '_se_exam_duration', sanitize_text_field( $_POST['se_exam_duration'] ) );
        }

        if ( isset( $_POST['se_passing_score'] ) ) {
            update_post_meta( $post_id, '_se_passing_score', sanitize_text_field( $_POST['se_passing_score'] ) );
        }

        if ( isset( $_POST['se_exam_questions'] ) ) {
            update_post_meta( $post_id, '_se_exam_questions', sanitize_text_field( $_POST['se_exam_questions'] ) );
        }

        if ( isset( $_POST['se_question_timer'] ) ) {
            update_post_meta( $post_id, '_se_question_timer', sanitize_text_field( $_POST['se_question_timer'] ) );
        }

        // Save MCQ options
        if ( isset( $_POST['se_mcq_options'] ) ) {
            $mcq_options = array();
            $correct_option_index = isset( $_POST['se_mcq_correct_option'] ) ? intval( $_POST['se_mcq_correct_option'] ) : -1;

            foreach ( $_POST['se_mcq_options'] as $index => $option_data ) {
                if ( ! empty( $option_data['text'] ) ) {
                    $mcq_options[] = array(
                        'text'    => sanitize_text_field( $option_data['text'] ),
                        'correct' => ( $index === $correct_option_index ),
                    );
                }
            }
            update_post_meta( $post_id, '_se_mcq_options', $mcq_options );
        } else if ( isset( $_POST['post_type'] ) && $_POST['post_type'] === 'se_question' ) {
            // If no options are submitted for a question post, save an empty array.
            update_post_meta( $post_id, '_se_mcq_options', array() );
        }
    }
}

SE_Meta_Boxes::init();
