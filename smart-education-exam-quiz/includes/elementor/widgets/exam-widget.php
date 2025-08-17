<?php
/**
 * Elementor Exam Widget.
 *
 * @package SmartEducationExamQuiz
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

/**
 * Elementor Exam Widget.
 *
 * @since 1.0.0
 */
class SE_Exam_Widget extends \Elementor\Widget_Base {

    /**
     * Get widget name.
     *
     * @since 1.0.0
     * @access public
     * @return string Widget name.
     */
    public function get_name() {
        return 'se_exam';
    }

    /**
     * Get widget title.
     *
     * @since 1.0.0
     * @access public
     * @return string Widget title.
     */
    public function get_title() {
        return __( 'Exam', 'smart-education-exam-quiz' );
    }

    /**
     * Get widget icon.
     *
     * @since 1.0.0
     * @access public
     * @return string Widget icon.
     */
    public function get_icon() {
        return 'eicon-task';
    }

    /**
     * Get widget categories.
     *
     * @since 1.0.0
     * @access public
     * @return array Widget categories.
     */
    public function get_categories() {
        return array( 'general' );
    }

    /**
     * Get script dependencies.
     *
     * @return array
     */
    public function get_script_depends() {
        return [ 'se-frontend-script' ];
    }

    /**
     * Get style dependencies.
     *
     * @return array
     */
    public function get_style_depends() {
        return [ 'se-frontend-style' ];
    }

    /**
     * Register widget controls.
     *
     * @since 1.0.0
     * @access protected
     */
    protected function _register_controls() {
        $this->start_controls_section(
            'content_section',
            array(
                'label' => __( 'Content', 'smart-education-exam-quiz' ),
                'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
            )
        );

        $this->add_control(
            'exam_id',
            array(
                'label'   => __( 'Select Exam', 'smart-education-exam-quiz' ),
                'type'    => \Elementor\Controls_Manager::SELECT,
                'options' => $this->get_exams_list(),
                'default' => '',
            )
        );

        $this->end_controls_section();

        // Style Tab
        $this->start_controls_section(
            'style_section',
            [
                'label' => __( 'Style', 'smart-education-exam-quiz' ),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );

        // Container Styles
        $this->add_control(
            'container_heading',
            [
                'label' => __( 'Container', 'smart-education-exam-quiz' ),
                'type' => \Elementor\Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );

        $this->add_control(
            'container_background_color',
            [
                'label' => __( 'Background Color', 'smart-education-exam-quiz' ),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .se-exam-container' => 'background-color: {{VALUE}}',
                ],
            ]
        );

        $this->add_responsive_control(
            'container_padding',
            [
                'label' => __( 'Padding', 'smart-education-exam-quiz' ),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%', 'em' ],
                'selectors' => [
                    '{{WRAPPER}} .se-exam-container' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        // Button Styles
        $this->add_control(
            'button_heading',
            [
                'label' => __( 'Buttons', 'smart-education-exam-quiz' ),
                'type' => \Elementor\Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'button_typography',
                'selector' => '{{WRAPPER}} .se-exam-footer button',
            ]
        );

        $this->add_control(
            'button_background_color',
            [
                'label' => __( 'Background Color', 'smart-education-exam-quiz' ),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .se-exam-footer button' => 'background-color: {{VALUE}}',
                ],
            ]
        );

        $this->add_control(
            'button_text_color',
            [
                'label' => __( 'Text Color', 'smart-education-exam-quiz' ),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .se-exam-footer button' => 'color: {{VALUE}}',
                ],
            ]
        );

        $this->end_controls_section();
    }

    /**
     * Render widget output on the frontend.
     *
     * @since 1.0.0
     * @access protected
     */
    protected function render() {
        $settings = $this->get_settings_for_display();
        $exam_id = $settings['exam_id'];

        if ( empty( $exam_id ) ) {
            echo '<p>' . __( 'Please select an exam.', 'smart-education-exam-quiz' ) . '</p>';
            return;
        }

        $exam_post = get_post( $exam_id );
        if ( ! $exam_post ) {
            echo '<p>' . __( 'Exam not found.', 'smart-education-exam-quiz' ) . '</p>';
            return;
        }

        $question_ids_str = get_post_meta( $exam_id, '_se_exam_questions', true );
        $question_ids = ! empty( $question_ids_str ) ? array_map( 'intval', explode( ',', $question_ids_str ) ) : array();

        $questions = array();
        if ( ! empty( $question_ids ) ) {
            foreach ( $question_ids as $question_id ) {
                $question_post = get_post( $question_id );
                if ( $question_post ) {
                    $options = get_post_meta( $question_id, '_se_mcq_options', true );
                    $frontend_options = array();
                    if ( ! empty( $options ) ) {
                        foreach ( $options as $option ) {
                            $frontend_options[] = array( 'text' => $option['text'] );
                        }
                    }

                    $terms = get_the_terms( $question_id, 'se_question_type' );
                    $type = ! empty( $terms ) ? $terms[0]->slug : 'mcq';

                    $questions[] = array(
                        'id'      => $question_id,
                        'title'   => $question_post->post_title,
                        'content' => $question_post->post_content,
                        'type'    => $type,
                        'timer'   => get_post_meta( $question_id, '_se_question_timer', true ),
                        'options' => $frontend_options,
                    );
                }
            }
        }

        $exam_data = array(
            'id'        => $exam_id,
            'title'     => $exam_post->post_title,
            'duration'  => get_post_meta( $exam_id, '_se_exam_duration', true ),
            'questions' => $questions,
        );

        $this->add_render_attribute( 'container', 'class', 'se-exam-container' );
        $this->add_render_attribute( 'container', 'id', 'se-exam-container-' . esc_attr( $this->get_id() ) );

        ?>
        <div <?php echo $this->get_render_attribute_string( 'container' ); ?>>
            <div class="se-exam-intro">
                <h2><?php echo esc_html( $exam_post->post_title ); ?></h2>
                <p><?php printf( __( 'Duration: %d minutes', 'smart-education-exam-quiz' ), get_post_meta( $exam_id, '_se_exam_duration', true ) ); ?></p>
                <p><?php printf( __( 'Number of questions: %d', 'smart-education-exam-quiz' ), count( $questions ) ); ?></p>
                <button class="se-start-exam-btn"><?php _e( 'Start Exam', 'smart-education-exam-quiz' ); ?></button>
            </div>
        </div>
        <script>
            window.se_exam_data_<?php echo esc_attr( $this->get_id() ); ?> = <?php echo json_encode( $exam_data ); ?>;
        </script>
        <?php
    }

    /**
     * Get a list of exams.
     *
     * @return array
     */
    private function get_exams_list() {
        $exams = get_posts(
            array(
                'post_type'   => 'se_exam',
                'numberposts' => -1,
            )
        );

        $options = array();
        if ( ! empty( $exams ) ) {
            foreach ( $exams as $exam ) {
                $options[ $exam->ID ] = $exam->post_title;
            }
        }
        return $options;
    }
}
