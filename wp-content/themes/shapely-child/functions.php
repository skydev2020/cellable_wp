<?php

add_action('wp_enqueue_scripts', 'nwd_modern_jquery');
add_action('wp_enqueue_scripts', 'wpb_adding_scripts', 999);

function nwd_modern_jquery() {
    global $wp_scripts;
    if(is_admin()) return;
    $wp_scripts->registered['jquery-core']->src = get_stylesheet_directory_uri() .'/vendor/jquery-3.5.1.min.js';
    $wp_scripts->registered['jquery']->deps = ['jquery-core'];
}

function wpb_adding_scripts() {
    wp_register_script('my_amazing_script', get_stylesheet_directory_uri() . '/vendor/bootstrap/bootstrap.3.3.7.min.js');
    wp_enqueue_script('my_amazing_script');
} 