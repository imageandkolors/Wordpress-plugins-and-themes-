<?php
/**
 * Elementor integration for Smart Education Exam & Quiz.
 *
 * @package SmartEducationExamQuiz
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

/**
 * The main class for Elementor integration.
 */
final class SE_Elementor_Integration {

    /**
     * The single instance of the class.
     *
     * @var SE_Elementor_Integration|null
     */
    private static $_instance = null;

    /**
     * Ensures only one instance of the class is loaded or can be loaded.
     */
    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    /**
     * Constructor.
     */
    public function __construct() {
        add_action( 'plugins_loaded', array( $this, 'init' ) );
    }

    /**
     * Initialize the integration.
     */
    public function init() {
        // Check if Elementor is installed and active.
        if ( ! did_action( 'elementor/loaded' ) ) {
            return;
        }

        // Register widgets.
        add_action( 'elementor/widgets/widgets_registered', array( $this, 'register_widgets' ) );
    }

    /**
     * Register the widgets.
     */
    public function register_widgets() {
        // Include the widget file.
        require_once __DIR__ . '/widgets/exam-widget.php';

        // Register the widget.
        \Elementor\Plugin::instance()->widgets_manager->register_widget_type( new \SE_Exam_Widget() );
    }
}

SE_Elementor_Integration::instance();
