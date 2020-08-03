<?php
if ( ! defined( 'ABSPATH' ) ) {
    require_once('../../../../wp-config.php');
}
// require_once('../../../../wp-config.php');
// include "../../../../wp-admin/includes/file.php";


global $wpdb;

if(isset($_POST['CELLABLE_SETTING_UPDATE']))
{   
    if(isset($_POST['id'])){       
        $value = stripslashes($_POST['value']);
        $r = $wpdb->query(
            $wpdb->prepare(
                "UPDATE ". $wpdb->base_prefix. "cellable_settings SET value = %s where id = %d;",
                $value, $_POST['id']
            ) 
        );
        
        header('Location: ' . $_SERVER['HTTP_REFERER']);
    }
}

if(isset($_POST['CELLABLE_ORDER_UPDATE']))
{   
    if(isset($_POST['id'])){       
        $status_id = $_POST['status_id'];
        $r = $wpdb->query(
            $wpdb->prepare(
                "UPDATE ". $wpdb->base_prefix. "cellable_orders SET order_status_id = %d where id = %d;",
                $status_id, $_POST['id']
            ) 
        );
        header('Location: ' . $_SERVER['HTTP_REFERER']);
    }
}

if(isset($_POST['CELLABLE_BRAND_UPDATE']))
{   
    if(isset($_POST['id'])){       
        $name = stripslashes($_POST['name']);
        $r = $wpdb->query(
            $wpdb->prepare(
                "UPDATE ". $wpdb->base_prefix. "cellable_phones SET name = %s where id = %d;",
                $name, $_POST['id']
            ) 
        );
        header('Location: ' . $_SERVER['HTTP_REFERER']);
    }
}

/**
 * Image Uploading: Phone Brand
 */

if(isset($_POST['post_id']) && isset($_POST['phone_id'])){
    global $wpdb;
    $image = $wpdb->get_var("SELECT guid FROM wp_posts WHERE id='" . $_POST['post_id'] . "'");
    if($wpdb->get_var("SELECT id FROM ". $wpdb->base_prefix. "cellable_phones WHERE id=" . $_POST['phone_id'] )) {
        $r = $wpdb->query(
            $wpdb->prepare(
                "UPDATE ". $wpdb->base_prefix. "cellable_phones SET image_file = %s where id = %d;",
                $image, $_POST['phone_id']
            ) 
        );
    }
    else {
        // $wpdb->insert("wp_spark_admin_pages", array('page_id' => $_POST['spark_page_id'], 'image' => $image,'title' => $title));
    }
        
}
