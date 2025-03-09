<?php

function child_enqueue_files() {
    // Parent and child theme styles
    wp_enqueue_style('parent-style', get_template_directory_uri() . '/style.css');
    wp_enqueue_style('child-style', get_stylesheet_uri(), array('parent-style','elementor-frontend'));

    wp_enqueue_script('script', get_stylesheet_directory_uri() . '/assets/js/scripts.js', array('jquery'), '', true);
}

add_action('wp_enqueue_scripts', 'child_enqueue_files', 20);

// require files
require_once get_stylesheet_directory() . '/inc/meal-prep.php';
require_once get_stylesheet_directory() . '/inc/cleaning.php';

