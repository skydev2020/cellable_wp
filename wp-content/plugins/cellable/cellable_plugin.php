<?php
/**
* Plugin Name: Cellable
* Plugin URI: https://www.yourwebsiteurl.com/
* Description: Cellable Backend Plugin
* Version: 1.0
* Author: Sky Dev 
**/

require_once('views/settings.php');

/**
 * Add Admin Pages
 */
if ( ! function_exists( 'page_admin_spark_fields' ) ) {
    function page_admin_spark_fields(){
        render_spark_fields();
    }
}

if ( ! function_exists( 'page_admin_spark_pages' ) ) {
    function page_admin_spark_pages(){
        render_import_page_detail();
    }
}

/**
 * Add Super Admin Pages
 */

if ( ! function_exists( 'setting_pages' ) ) {
    function setting_pages(){
        render_settings_list();
    }
}



/**
 * Add SuperAdmin & SubAdmin Menus
 */
if ( ! function_exists( 'admin_add_pages' ) ) {
   
    function admin_add_pages() {
        
        if(current_user_can('administrator')){
            add_menu_page("Cellable", "Cellable", "manage_options", "cellable","setting_pages","dashicons-networking", 4);
            add_submenu_page('cellable','Settings', 'System Settings', 'manage_options', 'setting_pages','setting_pages');            
            remove_submenu_page('cellable', 'cellable');
        }
        else{
            // add_menu_page("Spark Ignite", "Spark Ignite", "edit_posts", "spark","page_super_spark_users","dashicons-networking", 4);
            // add_submenu_page('spark','Ignite Templates', 'Ignite Templates', 'edit_posts', 'admin_spark_pages','page_admin_spark_pages');
            // add_submenu_page('spark','My Landing Pages', 'My Landing Pages', 'edit_posts', 'my_landing_pages','page_my_landing_pages');
            // add_submenu_page('spark','Spark Fields', 'Spark Fields', 'edit_posts', 'spark_fields','page_admin_spark_fields');
        }

        
    }
}


/**
 * Show Spark Fields to deal with shortcode
 */
function shortcode_spark_fields($atts, $content = null) {
	global $wpdb;
	$short_code = shortcode_atts( array (
		'id' => '',
	), $atts);
    $site_id = get_current_blog_id();
    $content = $wpdb->get_var("SELECT " .  esc_attr($short_code['id'])  . " FROM wp_spark_fields WHERE status='Activated' and site_id=" . $site_id);
    
    if ($short_code['id']=="logo") {
        // return $content;
        return '<img src="'.$content.'" class="sparkLogo" />';
    }
    
	return $content;
}

 
function spark_css_js()
{
    wp_enqueue_style('spark_css', plugins_url('css/index.css',__FILE__ ),'','all');
    wp_enqueue_media();
    wp_enqueue_script( 'wp-media-uploader', plugins_url('js/wp_media_uploader.js', __FILE__), array( 'jquery' ), 1.0 );
    wp_localize_script('wp-media-uploader', 'spark_admin_url',array( 'ajax_url' => plugins_url('views/actions.php', __FILE__) ));
}

if(isset($_GET['action'])){
    // var_dump(222);
    // Removed code and add to individual bulk action
}

if ( ! function_exists( 'delete_landing_post' ) ) {
    function delete_landing_post($post_id) {
        global $wpdb;
        
        $blog_id = get_current_blog_id();
        $blog_str ="";
        if ($blog_id != 1) {
            $blog_str = "_".$blog_id;
        }
        $isExist = $wpdb->get_var("SELECT count(*) FROM wp" . $blog_str . "_posts WHERE id='" . $post_id . "'");
        if ($isExist > 0) {
            $wpdb->delete("wp_spark_pages", array(
                'post_id' => $post_id
            ));
        }
    }
}


add_action('admin_menu', 'admin_add_pages');
add_action('admin_enqueue_scripts', 'spark_css_js');
add_shortcode('spark_fields','shortcode_spark_fields');
add_action( 'before_delete_post', 'delete_landing_post' );
add_filter( 'robots_txt', 'spark_robots', 20, 2 ); // Custom Multi Site Robots.txt

if ( ! function_exists( 'myblogs_blog_callback' ) ) {
    // Multi Network My Site Section
    function myblogs_blog_callback( $string, $user_blog ) {
        global $wpdb;
        // (maybe) modify $string.
        // var_dump($user_blog);
        $info = $wpdb->get_row("SELECT *  FROM wp_spark_fields WHERE status='Activated' and  site_id=" . $user_blog->userblog_id);
        $location_str = "<h4>LocationID: ";
        if ($info) {
            $location_str .= $info->location_id;
        }
        $location_str .= "</h4>";
        return $location_str. $string;
    }
}

add_filter( 'myblogs_blog_actions', 'myblogs_blog_callback', 10, 3);

// A send custom WebHook
// add_action( 'elementor_pro/forms/webhooks/response', function( $response, $record ) {
//     //make sure its our form
   
//     $form_name = $record->get_form_settings( 'form_name' );

//     // Replace MY_FORM_NAME with the name you gave your form
//     // if ( 'MY_FORM_NAME' !== $form_name ) {
//     //     return;
//     // }
  
//     $raw_fields = $record->get( 'fields' );
//     $fields = [];
//     foreach ( $raw_fields as $id => $field ) {
//         $fields[ $id ] = $field['value'];
//     }
   
//     // Replace HTTP://YOUR_WEBHOOK_URL with the actuall URL you want to post the form to
    
//     // header("Location: http://127.0.0.1/sparkignitepro/");
//     // print('<script>window.location.href="https://ronsell.com/thank-you?c=RDHq&upID=3981011"</script>');
//     // die();
// }, 10, 2 );


add_action( 'elementor_pro/forms/new_record', function( $record, $handler ) {
    global $wpdb;
    //make sure its our form
    $form_name = $record->get_form_settings( 'form_name' );
    $site_id = get_current_blog_id();
    $site_name = get_bloginfo('name');

    // Replace MY_FORM_NAME with the name you gave your form
    if ( 'IgniteForm1' !== $form_name && 'IgniteForm2' !== $form_name && 'IgniteForm3' !== $form_name ) {
        return;
    }

    $raw_fields = $record->get( 'fields' );
    
    $fields = [];
    foreach ( $raw_fields as $id => $field ) {
        $fields[ $id ] = $field['value'];
    }

    // error_log(print_r($fields, true) );
    // die();
    $res = wp_remote_post( 'https://api.sparkmembership.com/SparkIgnitePro.ashx', [
        'body' => $fields,
    ]);
        
    // error_log(print_r($res['body'], true) );
    $obj = json_decode($res['body']);

    $redirectURL = "";
    $resContactId = "";
    $status = 'Failed';

    if ($obj != null) {
        $redirectURL = $obj->redirectURL;
        $resContactId = $obj->contactID;
        $status = 'Success';
    }

    // $uri = $_SERVER['REQUEST_URI'];    
    // $protocol = ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";    
    // $url = $protocol . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];    
    $url = $_SERVER['HTTP_REFERER'];
    
    $date = new DateTime("now", new DateTimeZone('America/New_York') );
    $dateStr = $date->format('Y-m-d H:i:s');

    // error_log(print_r($_SERVER, true) );

    $lead_res = $wpdb->insert("wp_spark_leads", array(
        'ab_first_name' => $fields['ab_firstname'],
        'ab_last_name' => $fields['ab_lastname'],
        'ab_email_address' => $fields['ab_emailaddress'],
        'ab_mobile' => $fields['ab_mobile'],
        'name' => $fields['name'],
        'ab_location_id' => $fields['ab_locationID'],
        'redirect_url' => $fields['redirectURL'],
        'api_key' => $fields['apiKey'],
        'ab_tags' => $fields['ab_tags'],
        'ab_ignite' => $fields['ab_ignite'],
        'ab_utm_source' => $fields['ab_utm_source'],
        'site_id' => $site_id,
        'site_name' => $site_name,
        'page_url' => $url,
        'status' => $status,
        'response' => $res['body'],
        'res_redirect_url' => $redirectURL,
        'res_contact_id' => $resContactId,
        'created_date' => $dateStr
    ));

    $handler->data['output'] = $redirectURL;
}, 10, 2 );

add_action( 'wp_footer', function() {
    global $wpdb;
    $blog_id = get_current_blog_id();
    // (maybe) modify $string.
    
    $info = $wpdb->get_row("SELECT *  FROM wp_spark_fields WHERE status='Activated' and  site_id=" . $blog_id);
    
    if ($info) {
?>
    <script src="//app.sparkmembership.com/wf/abundance.js.ashx?lid=<?= $info->location_id?>"></script>
<?php
    }
    if ( !defined( 'ELEMENTOR_VERSION' ) ) {
        return;
    }
?>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        jQuery( function( $ ) {
            var frm = jQuery(".elementor-form");

            // form_destruct: This trigger is defined in Elementor Page on Success Action
            $( ".elementor-form" ).on( "form_destruct", function(e, res) {
                // console.log(res.data.output);
                if (res.data && res.data.output) {
                    window.location.href = res.data.output;
                }
                
            });
        });
    });
   
</script>
<?php
} );