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

function cellable_search_form( $form ) {
	$form = '<form role="search" method="get" id="searchform" class="search-form" action="' . esc_url( home_url( '/' ) ) . '/search-results" >
    <label class="screen-reader-text" for="s">Search for:</label>
    <input type="text" placeholder="Search" value="' . esc_attr( get_search_query() ) . '" name="q" id="q" />
    <button type="submit" class="searchsubmit"><i class="fa fa-search" aria-hidden="true"></i><span class="screen-reader-text">' . esc_attr__( 'Search', 'shapely' ) . '</span></button>
    </form>';

	return $form;
}

add_filter( 'get_search_form', 'cellable_search_form', 101 ); // Higher Priority means redefine the form



