<?php
// load all the composer dependencies
 require_once __DIR__ . '/vendor/autoload.php';

/**
 * Enqueue styles for the child theme
 */
function twentyseventeen_child_enqueue_styles() {
    // 1. Load the Parent Theme styles
    wp_enqueue_style( 'parent-style', get_template_directory_uri() . '/style.css' );

    // 2. Load the Child Theme styles with "Cache Busting"
    wp_enqueue_style( 
        'child-style', 
        get_stylesheet_directory_uri() . '/style.css', 
        array( 'parent-style' ), 
        filemtime( get_stylesheet_directory() . '/style.css' ) 
    );
}
add_action( 'wp_enqueue_scripts', 'twentyseventeen_child_enqueue_styles' );

