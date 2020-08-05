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

// Phone New/Update
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

if(isset($_POST['CELLABLE_BRAND_NEW']))
{       
    $name = stripslashes($_POST['name']);
    $r = $wpdb->query(
        $wpdb->prepare(
            "INSERT ". $wpdb->base_prefix. "cellable_phones (name) VALUES (%s)",
            $name
        ) 
    );
    
    header('Location: ' . get_admin_url()."admin.php?page=brand_pages");    
}

// Carrier New/Update
if(isset($_POST['CELLABLE_CARRIER_UPDATE']))
{   
    if(isset($_POST['id'])){       
        $name = stripslashes($_POST['name']);
        $position = stripslashes($_POST['position']);
        $status = stripslashes($_POST['status']);
        $r = $wpdb->query(
            $wpdb->prepare(
                "UPDATE ". $wpdb->base_prefix. "cellable_carriers SET name = %s, position = %d, status = %d where id = %d;",
                $name, $position, $status, $_POST['id']
            ) 
        );
        header('Location: ' . $_SERVER['HTTP_REFERER']);
    }
}

if(isset($_POST['CELLABLE_CARRIER_NEW']))
{       
    $name = stripslashes($_POST['name']);
    $position = stripslashes($_POST['position']);
    $status = stripslashes($_POST['status']);

    $r = $wpdb->query(
        $wpdb->prepare(
            "INSERT ". $wpdb->base_prefix. "cellable_carriers (name, position, status) VALUES (%s, %d, %d)",
            $name, $position, $status_id
        ) 
    );
    
    header('Location: ' . get_admin_url()."admin.php?page=carrier_pages");    
}

// Phone Version Update
if(isset($_POST['CELLABLE_VERSION_UPDATE']))
{   
    if(isset($_POST['id'])){
        $id = $_POST['id'];       
        $name = stripslashes($_POST['name']);
        $phone_id = $_POST['phone_id'];
        $views = $_POST['views'];
        $purchases = $_POST['purchases'];
        $status = $_POST['status'];
        $position = $_POST['position'];
        
        // Update Phone Version Table
        $r = $wpdb->query(
            $wpdb->prepare(
                "UPDATE ". $wpdb->base_prefix. "cellable_phone_versions SET name = %s, phone_id = %d,
                    views = %d, purchases = %d, status=%d, position=%f where id = %d;",
                $name, $phone_id, $views, $purchases, $status, $position, $_POST['id']
            ) 
        );

        // Update Version Capacity
        $sql_str = "SELECT * FROM ".$wpdb->base_prefix."cellable_storage_capacities ";
        $storage_capacities = $wpdb->get_results($sql_str, ARRAY_A);

        foreach ($storage_capacities as $ele):            
            $sql_str = "select * from ". $wpdb->base_prefix."cellable_version_capacities ";
            $sql_str .= "where phone_version_id = ".$_POST['id']." and storage_capacity_id=" .$ele['id'];
            
            $vc = $wpdb->get_row($sql_str, ARRAY_A);
            $value = isset($_REQUEST["cp".$ele['id']]) ? $_REQUEST["cp".$ele['id']] : 0; 
            if ($vc) {                                
                $wpdb->query(
                    $wpdb->prepare(
                        "UPDATE ". $wpdb->base_prefix. "cellable_version_capacities SET value = %f "
                        ."where phone_version_id = %d and storage_capacity_id= %d",
                        $value, $id, $ele['id']
                    ) 
                );
            }
            else {
                
                $wpdb->query(
                    $wpdb->prepare(
                        "INSERT ". $wpdb->base_prefix. "cellable_version_capacities (phone_version_id, storage_capacity_id, value) "
                        ."VALUES (%d, %d, %f)",
                        $id, $ele['id'], $value
                    ) 
                );
            }            
        endforeach;
        
        // Update Version Carrier
        $sql_str = "SELECT * FROM ".$wpdb->base_prefix."cellable_carriers ";
        $carriers = $wpdb->get_results($sql_str, ARRAY_A);

        foreach ($carriers as $ele):            
            $sql_str = "select * from ". $wpdb->base_prefix."cellable_version_carriers ";
            $sql_str .= "where phone_version_id = ".$id." and carrier_id=" .$ele['id'];
            
            $vc = $wpdb->get_row($sql_str, ARRAY_A);
            $value = isset($_REQUEST["cr".$ele['id']]) ? $_REQUEST["cr".$ele['id']] : 0; 
            if ($vc) {                                
                $wpdb->query(
                    $wpdb->prepare(
                        "UPDATE ". $wpdb->base_prefix. "cellable_version_carriers SET value = %f "
                        ."where phone_version_id = %d and carrier_id= %d",
                        $value, $id, $ele['id']
                    ) 
                );
            }
            else {
                $wpdb->query(
                    $wpdb->prepare(
                        "INSERT ". $wpdb->base_prefix. "cellable_version_carriers (phone_version_id, carrier_id, value) "
                        ."VALUES (%d, %d, %f)",
                        $id, $ele['id'], $value
                    ) 
                );
            }            
        endforeach;

        header('Location: ' . $_SERVER['HTTP_REFERER']);
    }
}

// Phone Version New
if(isset($_POST['CELLABLE_VERSION_NEW']))
{   
    $name = stripslashes($_POST['name']);
    $phone_id = $_POST['phone_id'];
    $views = $_POST['views'];
    $purchases = $_POST['purchases'];
    $status = $_POST['status'];
    $position = $_POST['position'];
    
    // Update Into Phone Version Table
    $r = $wpdb->query(
        $wpdb->prepare(
            "INSERT ". $wpdb->base_prefix. "cellable_phone_versions (name, phone_id, views, purchases, status, position) "
            ."VALUES (%s, %d, %d, %d, %d, %f)",
            $name, $phone_id, $views, $purchases, $status, $position
        ) 
    );
    if ($r != true) {
        echo("<a href='javascript:window.history.back();'>DB Insert Error</a>");
        return;
    }

    $id = $wpdb->insert_id;
    
    // Insert Version Capacity
    $sql_str = "SELECT * FROM ".$wpdb->base_prefix."cellable_storage_capacities ";
    $storage_capacities = $wpdb->get_results($sql_str, ARRAY_A);

    foreach ($storage_capacities as $ele):
        $value = isset($_REQUEST["cp".$ele['id']]) ? $_REQUEST["cp".$ele['id']] : 0;             
        $wpdb->query(
            $wpdb->prepare(
                "INSERT ". $wpdb->base_prefix. "cellable_version_capacities (phone_version_id, storage_capacity_id, value) "
                ."VALUES (%d, %d, %f)",
                $id, $ele['id'], $value
            ) 
        );
    endforeach;
    
    // Insert Version Carrier
    $sql_str = "SELECT * FROM ".$wpdb->base_prefix."cellable_carriers ";
    $carriers = $wpdb->get_results($sql_str, ARRAY_A);

    foreach ($carriers as $ele):            
        $wpdb->query(
            $wpdb->prepare(
                "INSERT ". $wpdb->base_prefix. "cellable_version_carriers (phone_version_id, carrier_id, value) "
                ."VALUES (%d, %d, %f)",
                $id, $ele['id'], $value
            ) 
        );
    endforeach;
    
    header('Location: ' . get_admin_url()."admin.php?page=version_pages");    
    
}

// Defect Group
if(isset($_POST['CELLABLE_DEFECT_GROUP_NEW']))
{       
    $name = stripslashes($_POST['name']);
    $info = stripslashes($_POST['info']);
    $position = $_POST['position'];

    $r = $wpdb->query(
        $wpdb->prepare(
            "INSERT ". $wpdb->base_prefix. "cellable_defect_groups (name, info, position) VALUES (%s, %s, %d)",
            $name, $info, $position
        ) 
    );
    
    header('Location: ' . get_admin_url()."admin.php?page=defect_group_pages");    
}

if(isset($_POST['CELLABLE_DEFECT_GROUP_UPDATE']))
{   
    if(isset($_POST['id'])){       
        $name = stripslashes($_POST['name']);
        $info = stripslashes($_POST['info']);
        $position = $_POST['position'];
        $r = $wpdb->query(
            $wpdb->prepare(
                "UPDATE ". $wpdb->base_prefix. "cellable_defect_groups SET name = %s, info = %s, position = %d where id = %d;",
                $name, $info, $position, $_POST['id']
            ) 
        );
        header('Location: ' . $_SERVER['HTTP_REFERER']);
    }
}

// Possible Defect
if(isset($_POST['CELLABLE_POSSIBLE_DEFECT_NEW']))
{       
    $name = stripslashes($_POST['name']);
    $cost = $_POST['cost'];
    $phone_version_id = $_POST['phone_version_id'];
    $defect_group_id = $_POST['defect_group_id'];

    $r = $wpdb->query(
        $wpdb->prepare(
            "INSERT ". $wpdb->base_prefix. "cellable_possible_defects (name, cost, phone_version_id, defect_group_id) "
            ."VALUES (%s, %f, %d, %d)",
            $name, $cost, $phone_version_id, $defect_group_id
        ) 
    );
    
    header('Location: ' . get_admin_url()."admin.php?page=possible_defect_pages");    
}

if(isset($_POST['CELLABLE_POSSIBLE_DEFECT_UPDATE']))
{   
    if(isset($_POST['id'])){       
        $name = stripslashes($_POST['name']);
        $cost = $_POST['cost'];
        $phone_version_id = $_POST['phone_version_id'];
        $defect_group_id = $_POST['defect_group_id'];

        $r = $wpdb->query(
            $wpdb->prepare(
                "UPDATE ". $wpdb->base_prefix. "cellable_possible_defects SET phone_version_id=%d, defect_group_id=%d, 
                name = %s, cost = %f where id = %d;",
                $phone_version_id, $defect_group_id, $name, $cost, $_POST['id']
            ) 
        );
        header('Location: ' . $_SERVER['HTTP_REFERER']);
    }
}

// Storage Capacity
if(isset($_POST['CELLABLE_CAPACITY_NEW']))
{   
    $capacity = $_POST['capacity'];    
    $description = stripslashes($_POST['description']);
    
    $r = $wpdb->query(
        $wpdb->prepare(
            "INSERT ". $wpdb->base_prefix. "cellable_storage_capacities (capacity, description) "
            ."VALUES (%d, %s)",
            $capacity, $description
        ) 
    );
    
    header('Location: ' . get_admin_url()."admin.php?page=capacity_pages");    
}

if(isset($_POST['CELLABLE_CAPACITY_UPDATE']))
{   
    if(isset($_POST['id'])){       
        $capacity = $_POST['capacity'];    
        $description = stripslashes($_POST['description']);

        $r = $wpdb->query(
            $wpdb->prepare(
                "UPDATE ". $wpdb->base_prefix. "cellable_storage_capacities SET capacity=%d, description=%s where id = %d;",
                $capacity, $description, $_POST['id']
            ) 
        );
        header('Location: ' . $_SERVER['HTTP_REFERER']);
    }
}

// Payment Types
if(isset($_POST['CELLABLE_PAYMENT_NEW']))
{   
    $name = stripslashes($_POST['name']);
    
    $r = $wpdb->query(
        $wpdb->prepare(
            "INSERT ". $wpdb->base_prefix. "cellable_payment_types (name) VALUES (%s)",
            $name
        ) 
    );
    
    header('Location: ' . get_admin_url()."admin.php?page=payment_pages");    
}

if(isset($_POST['CELLABLE_PAYMENT_UPDATE']))
{   
    if(isset($_POST['id'])){               
        $name = stripslashes($_POST['name']);

        $r = $wpdb->query(
            $wpdb->prepare(
                "UPDATE ". $wpdb->base_prefix. "cellable_payment_types SET name=%s where id = %d;",
                $name, $_POST['id']
            ) 
        );
        header('Location: ' . $_SERVER['HTTP_REFERER']);
    }
}

/**
 * Image Uploading: Version
 */

if(isset($_POST['post_id']) && isset($_POST['version_id'])){
    global $wpdb;
    $image = $wpdb->get_var("SELECT guid FROM wp_posts WHERE id='" . $_POST['post_id'] . "'");
    if($wpdb->get_var("SELECT id FROM ". $wpdb->base_prefix. "cellable_phone_versions WHERE id=" . $_POST['version_id'] )) {
        $r = $wpdb->query(
            $wpdb->prepare(
                "UPDATE ". $wpdb->base_prefix. "cellable_phone_versions SET image_file = %s where id = %d;",
                $image, $_POST['version_id']
            ) 
        );
    }
    else {
        // $wpdb->insert("wp_spark_admin_pages", array('page_id' => $_POST['spark_page_id'], 'image' => $image,'title' => $title));
    }        
}

/**
 * Image Uploading: Carrier
 */

if(isset($_POST['post_id']) && isset($_POST['carrier_id'])){
    global $wpdb;
    $image = $wpdb->get_var("SELECT guid FROM wp_posts WHERE id='" . $_POST['post_id'] . "'");
    if($wpdb->get_var("SELECT id FROM ". $wpdb->base_prefix. "cellable_carriers WHERE id=" . $_POST['carrier_id'] )) {
        $r = $wpdb->query(
            $wpdb->prepare(
                "UPDATE ". $wpdb->base_prefix. "cellable_carriers SET image_file = %s where id = %d;",
                $image, $_POST['carrier_id']
            ) 
        );
    }
    else {
        // $wpdb->insert("wp_spark_admin_pages", array('page_id' => $_POST['spark_page_id'], 'image' => $image,'title' => $title));
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
