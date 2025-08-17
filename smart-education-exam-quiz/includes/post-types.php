<?php
/**
 * Custom Post Types for Smart Education Exam & Quiz Plugin.
 *
 * @package SmartEducationExamQuiz
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

/**
 * Register Custom Post Types and Taxonomies.
 */
class SE_Post_Types {

    /**
     * Initialize the class and set up the hooks.
     */
    public static function init() {
        add_action( 'init', array( __CLASS__, 'register_post_types' ), 5 );
        add_action( 'init', array( __CLASS__, 'register_taxonomies' ), 5 );
    }

    /**
     * Register core post types.
     */
    public static function register_post_types() {
        if ( ! is_blog_installed() || post_type_exists( 'se_exam' ) ) {
            return;
        }

        // Exam Post Type
        register_post_type(
            'se_exam',
            array(
                'labels'              => array(
                    'name'                  => __( 'Exams', 'smart-education-exam-quiz' ),
                    'singular_name'         => __( 'Exam', 'smart-education-exam-quiz' ),
                    'menu_name'             => _x( 'Exams', 'Admin menu name', 'smart-education-exam-quiz' ),
                    'add_new'               => __( 'Add New', 'smart-education-exam-quiz' ),
                    'add_new_item'          => __( 'Add New Exam', 'smart-education-exam-quiz' ),
                    'edit'                  => __( 'Edit', 'smart-education-exam-quiz' ),
                    'edit_item'             => __( 'Edit Exam', 'smart-education-exam-quiz' ),
                    'new_item'              => __( 'New Exam', 'smart-education-exam-quiz' ),
                    'view'                  => __( 'View Exam', 'smart-education-exam-quiz' ),
                    'view_item'             => __( 'View Exam', 'smart-education-exam-quiz' ),
                    'search_items'          => __( 'Search Exams', 'smart-education-exam-quiz' ),
                    'not_found'             => __( 'No Exams found', 'smart-education-exam-quiz' ),
                    'not_found_in_trash'    => __( 'No Exams found in trash', 'smart-education-exam-quiz' ),
                    'parent'                => __( 'Parent Exam', 'smart-education-exam-quiz' ),
                    'featured_image'        => __( 'Exam Image', 'smart-education-exam-quiz' ),
                    'set_featured_image'    => __( 'Set exam image', 'smart-education-exam-quiz' ),
                    'remove_featured_image' => __( 'Remove exam image', 'smart-education-exam-quiz' ),
                    'use_featured_image'    => __( 'Use as exam image', 'smart-education-exam-quiz' ),
                    'archives'              => __( 'Exam archives', 'smart-education-exam-quiz' ),
                    'insert_into_item'      => __( 'Insert into exam', 'smart-education-exam-quiz' ),
                    'uploaded_to_this_item' => __( 'Uploaded to this exam', 'smart-education-exam-quiz' ),
                    'filter_items_list'     => __( 'Filter exams', 'smart-education-exam-quiz' ),
                    'items_list_navigation' => __( 'Exams navigation', 'smart-education-exam-quiz' ),
                    'items_list'            => __( 'Exams list', 'smart-education-exam-quiz' ),
                ),
                'public'              => true,
                'show_ui'             => true,
                'capability_type'     => 'exam',
                'map_meta_cap'        => true,
                'publicly_queryable'  => true,
                'exclude_from_search' => false,
                'hierarchical'        => false,
                'query_var'           => true,
                'supports'            => array( 'title', 'editor', 'thumbnail' ),
                'has_archive'         => true,
                'show_in_nav_menus'   => true,
                'show_in_rest'        => true,
                'menu_icon'           => 'dashicons-welcome-learn-more',
            )
        );

        // Question Post Type
        register_post_type(
            'se_question',
            array(
                'labels'       => array(
                    'name'               => __( 'Questions', 'smart-education-exam-quiz' ),
                    'singular_name'      => __( 'Question', 'smart-education-exam-quiz' ),
                    'add_new'            => __( 'Add New', 'smart-education-exam-quiz' ),
                    'add_new_item'       => __( 'Add New Question', 'smart-education-exam-quiz' ),
                    'edit'               => __( 'Edit', 'smart-education-exam-quiz' ),
                    'edit_item'          => __( 'Edit Question', 'smart-education-exam-quiz' ),
                    'new_item'           => __( 'New Question', 'smart-education-exam-quiz' ),
                    'view'               => __( 'View Question', 'smart-education-exam-quiz' ),
                    'view_item'          => __( 'View Question', 'smart-education-exam-quiz' ),
                    'search_items'       => __( 'Search Questions', 'smart-education-exam-quiz' ),
                    'not_found'          => __( 'No Questions found', 'smart-education-exam-quiz' ),
                    'not_found_in_trash' => __( 'No Questions found in trash', 'smart-education-exam-quiz' ),
                    'parent'             => __( 'Parent Question', 'smart-education-exam-quiz' ),
                ),
                'public'              => false,
                'show_ui'             => true,
                'capability_type'     => 'question',
                'map_meta_cap'        => true,
                'publicly_queryable'  => false,
                'exclude_from_search' => true,
                'show_in_menu'        => 'edit.php?post_type=se_exam',
                'hierarchical'        => false,
                'query_var'           => true,
                'supports'            => array( 'title', 'editor' ),
                'has_archive'         => false,
                'show_in_nav_menus'   => false,
                'show_in_rest'        => true,
            )
        );
    }

    /**
     * Register core taxonomies.
     */
    public static function register_taxonomies() {
        if ( ! is_blog_installed() ) {
            return;
        }

        // Exam Category Taxonomy
        register_taxonomy(
            'se_exam_category',
            'se_exam',
            array(
                'hierarchical'      => true,
                'label'             => __( 'Exam Categories', 'smart-education-exam-quiz' ),
                'labels'            => array(
                    'name'              => __( 'Exam Categories', 'smart-education-exam-quiz' ),
                    'singular_name'     => __( 'Category', 'smart-education-exam-quiz' ),
                    'menu_name'         => _x( 'Categories', 'Admin menu name', 'smart-education-exam-quiz' ),
                    'search_items'      => __( 'Search Categories', 'smart-education-exam-quiz' ),
                    'all_items'         => __( 'All Categories', 'smart-education-exam-quiz' ),
                    'parent_item'       => __( 'Parent Category', 'smart-education-exam-quiz' ),
                    'parent_item_colon' => __( 'Parent Category:', 'smart-education-exam-quiz' ),
                    'edit_item'         => __( 'Edit Category', 'smart-education-exam-quiz' ),
                    'update_item'       => __( 'Update Category', 'smart-education-exam-quiz' ),
                    'add_new_item'      => __( 'Add New Category', 'smart-education-exam-quiz' ),
                    'new_item_name'     => __( 'New Category Name', 'smart-education-exam-quiz' ),
                ),
                'show_ui'           => true,
                'query_var'         => true,
                'show_in_rest'      => true,
                'show_admin_column' => true,
            )
        );

        // Question Type Taxonomy
        register_taxonomy(
            'se_question_type',
            'se_question',
            array(
                'hierarchical'      => true,
                'label'             => __( 'Question Types', 'smart-education-exam-quiz' ),
                'labels'            => array(
                    'name'              => __( 'Question Types', 'smart-education-exam-quiz' ),
                    'singular_name'     => __( 'Type', 'smart-education-exam-quiz' ),
                    'menu_name'         => _x( 'Types', 'Admin menu name', 'smart-education-exam-quiz' ),
                    'search_items'      => __( 'Search Types', 'smart-education-exam-quiz' ),
                    'all_items'         => __( 'All Types', 'smart-education-exam-quiz' ),
                    'parent_item'       => __( 'Parent Type', 'smart-education-exam-quiz' ),
                    'parent_item_colon' => __( 'Parent Type:', 'smart-education-exam-quiz' ),
                    'edit_item'         => __( 'Edit Type', 'smart-education-exam-quiz' ),
                    'update_item'       => __( 'Update Type', 'smart-education-exam-quiz' ),
                    'add_new_item'      => __( 'Add New Type', 'smart-education-exam-quiz' ),
                    'new_item_name'     => __( 'New Type Name', 'smart-education-exam-quiz' ),
                ),
                'show_ui'           => true,
                'query_var'         => true,
                'show_in_rest'      => true,
                'show_admin_column' => true,
            )
        );

        // Section Taxonomy
        register_taxonomy(
            'se_section',
            'se_question',
            array(
                'hierarchical'      => true,
                'label'             => __( 'Sections', 'smart-education-exam-quiz' ),
                'labels'            => array(
                    'name'              => __( 'Sections', 'smart-education-exam-quiz' ),
                    'singular_name'     => __( 'Section', 'smart-education-exam-quiz' ),
                    'menu_name'         => _x( 'Sections', 'Admin menu name', 'smart-education-exam-quiz' ),
                    'search_items'      => __( 'Search Sections', 'smart-education-exam-quiz' ),
                    'all_items'         => __( 'All Sections', 'smart-education-exam-quiz' ),
                    'parent_item'       => __( 'Parent Section', 'smart-education-exam-quiz' ),
                    'parent_item_colon' => __( 'Parent Section:', 'smart-education-exam-quiz' ),
                    'edit_item'         => __( 'Edit Section', 'smart-education-exam-quiz' ),
                    'update_item'       => __( 'Update Section', 'smart-education-exam-quiz' ),
                    'add_new_item'      => __( 'Add New Section', 'smart-education-exam-quiz' ),
                    'new_item_name'     => __( 'New Section Name', 'smart-education-exam-quiz' ),
                ),
                'show_ui'           => true,
                'query_var'         => true,
                'show_in_rest'      => true,
                'show_admin_column' => true,
            )
        );
    }
}

SE_Post_Types::init();
