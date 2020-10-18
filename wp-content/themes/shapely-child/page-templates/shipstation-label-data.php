<?php
/*
Template Name: Cellable ShipStation Label Data
Template Post Type: page
*/
global $wpdb;
$order_id = $_REQUEST['order_id'];

$order = $wpdb->get_row($wpdb->prepare("SELECT * FROM ". $wpdb->base_prefix."cellable_orders 
    WHERE id= %d", $order_id), ARRAY_A);

if (!$order) {
    echo 0;
}

$data = base64_decode($order['label_data']);

$file_name = $order_id."_label_data.pdf";
file_put_contents($file_name, $data);

header('Cache-control: private');
header('Content-Type: application/octet-stream');
header('Content-Length: '.filesize($file_name));
header('Content-Disposition: attachment; filename='.basename($file_name));

ob_clean();
flush();
readfile($file_name);
exit;


