<?php
/*
Template Name: Cellable Save Testimonial
Template Post Type: page
*/
ob_start(); // this line is for the issue: header already sent
require_once(ABSPATH . 'wp-content/themes/shapely-child/cellable_shipping.class.php');
require_once(ABSPATH . 'wp-content/themes/shapely-child/cellable_email.class.php');

$user = wp_get_current_user(); // ID->0: if user is not logged in
if ($user->ID==0):
	header("Location: ".get_home_url()."/wp-login.php?action=login");
	exit();
endif;
$rating = isset($_REQUEST['rating']) ? $_REQUEST['rating'] : null;
									
if (!$rating) {
	header("Location: ".get_home_url());
	exit();
}

$comment = isset($_REQUEST['comment']) ? $_REQUEST['comment'] : "";

$sql_query = "INSERT INTO ". $wpdb->base_prefix . "cellable_testimonials (comment, rating, user_id, created_date) VALUES (%s, %d, %d, %s)";

$wpdb->query( $wpdb->prepare($sql_query, 
	$comment,
	$rating, 
	$user->ID, 
	date_create()->format('Y-m-d H:i:s')
));
header("Location: ".get_home_url()."/track-orders");
