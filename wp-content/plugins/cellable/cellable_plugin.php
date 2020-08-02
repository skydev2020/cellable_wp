<?php
/**
* Plugin Name: Cellable
* Plugin URI: https://www.yourwebsiteurl.com/
* Description: Cellable Backend Plugin
* Version: 1.0
* Author: Sky Dev 
**/
require_once('cellable_global.php');
require_once('views/settings.php');
require_once('views/orders.php');

/**
 * Add Admin Pages
 */
if ( ! function_exists( 'orders_pages' ) ) {
    function orders_pages(){
        render_orders_list();
    }
}

if ( ! function_exists( 'settings_pages' ) ) {
    function settings_pages(){
        render_settings_list();
    }
}



/**
 * Add SuperAdmin & SubAdmin Menus
 */
if ( ! function_exists( 'admin_add_pages' ) ) {
   
    function admin_add_pages() {
        
        if(current_user_can('administrator')){
            add_menu_page("Cellable", "Cellable", "manage_options", "cellable","orders","dashicons-networking", 4);
            add_submenu_page('cellable','Orders', 'Orders', 'manage_options', 'orders_pages','orders_pages');
            add_submenu_page('cellable','Settings', 'System Settings', 'manage_options', 'settings_pages','settings_pages');
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
	// $short_code = shortcode_atts( array (
	// 	'id' => '',
	// ), $atts);
    // $site_id = get_current_blog_id();
    // $content = $wpdb->get_var("SELECT " .  esc_attr($short_code['id'])  . " FROM wp_spark_fields WHERE status='Activated' and site_id=" . $site_id);
    
    // if ($short_code['id']=="logo") {
    //     // return $content;
    //     return '<img src="'.$content.'" class="sparkLogo" />';
    // }
    
	return $content;
}


function get_cellable_setting($name) {
    global $wpdb;

    $content = $wpdb->get_var("SELECT value FROM ". $wpdb->base_prefix ."cellable_settings WHERE name='" . $name."'");    
	return $content;
}

 
function spark_css_js()
{
    wp_enqueue_style('spark_css', plugins_url('css/index.css',__FILE__ ),'','all');
    wp_enqueue_media();
    wp_enqueue_script( 'wp-media-uploader', plugins_url('js/wp_media_uploader.js', __FILE__), array( 'jquery' ), 1.0 );
    wp_localize_script('wp-media-uploader', 'spark_admin_url',array( 'ajax_url' => plugins_url('views/actions.php', __FILE__) ));
}


add_action('admin_menu', 'admin_add_pages');
// add_action('admin_enqueue_scripts', 'spark_css_js');
add_shortcode('spark_fields','shortcode_spark_fields');


// Registration Custom Field
add_action( 'register_form', 'crf_registration_form' );
add_filter( 'registration_errors', 'crf_registration_errors', 10, 3 );
add_action( 'user_register', 'crf_user_register' );

// Show/Edit Extra Profile Information
add_action( 'show_user_profile', 'extra_user_profile_fields' );
add_action( 'edit_user_profile', 'extra_user_profile_fields' );

add_action( 'personal_options_update', 'save_extra_user_profile_fields' );
add_action( 'edit_user_profile_update', 'save_extra_user_profile_fields' );

// Login & Logout Redirect
add_filter('login_redirect', 'admin_default_page');
add_filter('logout_redirect', 'admin_default_page');

function admin_default_page() {
    $user = wp_get_current_user();
    // Update Last Login Time
    
    if ($user->ID>0) {
        $str = date_create()->format('Y-m-d H:i:s');
        update_user_meta($user->ID, 'last_login', $str);
    }    
    return get_home_url();
}

function crf_registration_form() {
    global $STATES;
    $first_name = !empty( $_POST['first_name'] ) ? $_POST['first_name']  : '';
    $last_name = !empty( $_POST['last_name'] ) ? $_POST['last_name']  : '';
    $phone_number = !empty( $_POST['phone_number'] ) ? $_POST['phone_number']  : '';
    $address1 = !empty( $_POST['address1'] ) ? $_POST['address1']  : '';
    $address2 = !empty( $_POST['address2'] ) ? $_POST['address2']  : '';
    $city = !empty( $_POST['city'] ) ? $_POST['city']  : '';
    $state = !empty( $_POST['state'] ) ? $_POST['state']  : '';
    $zip = !empty( $_POST['zip'] ) ? $_POST['zip']  : '';
    
?>
	<p>
		<label for="first_name">First Name<br/>
			<input type="text" id="first_name" name="first_name" value="<?php echo esc_attr( $first_name ); ?>"
			       class="input" required/>
        </label>
        <label for="last_name">Last Name<br/>
			<input type="text" id="last_name" name="last_name" value="<?php echo esc_attr( $last_name ); ?>"
			       class="input"/>
        </label>
        <label for="phone_number">Phone Number<br/>
			<input type="text" id="phone_number" name="phone_number" value="<?php echo esc_attr( $phone_number ); ?>"
			       class="input"/>
        </label>
        <label for="address1">Street Address<br/>
			<input type="text" id="address1" name="address1" value="<?php echo esc_attr( $address1 ); ?>"
			       class="input"/>
        </label>
        <label for="address2">Apt/Ste<br/>
			<input type="text" id="address2" name="address2" value="<?php echo esc_attr( $address2 ); ?>"
			       class="input"/>
        </label>
        <label for="city">City<br/>
			<input type="text" id="city" name="city" value="<?php echo esc_attr( $city ); ?>"
			       class="input"/>
        </label>
        <label for="state">State<br/>
            <select class="input" id="state" name="state">
                <option value="">-- Select State --</option>
                <?php foreach ($STATES as $ele) :?>
                <option value="<?= $ele['abbr'] ?>" <?= $ele['abbr'] == $state ? "selected" : "" ?> ><?= $ele['name'] ?></option>    
                <?php endforeach; ?>
            </select>
        </label>
        <label for="zip">Zip<br/>
			<input type="text" id="zip" name="zip" value="<?php echo esc_attr( $zip ); ?>"
			       class="input"/>
		</label>
	</p>
<?php
}

function crf_registration_errors( $errors, $sanitized_user_login, $user_email ) {
    	
    if ( empty( $_POST['first_name'] ) ) {
		$errors->add( 'first_name_error', __( '<strong>Error</strong>: Please enter First Name.', 'crf' ) );
    }    
    if ( empty( $_POST['last_name'] ) ) {
		$errors->add( 'last_name_error', __( '<strong>Error</strong>: Please enter Last Name.', 'crf' ) );
    }
    if ( empty( $_POST['phone_number'] ) ) {
		$errors->add( 'phone_number_error', __( '<strong>Error</strong>: Please enter Phone Number.', 'crf' ) );
    }
    if ( empty( $_POST['address1'] ) ) {
		$errors->add( 'address1_error', __( '<strong>Error</strong>: Please enter Street Address.', 'crf' ) );
    }
    if ( empty( $_POST['city'] ) ) {
		$errors->add( 'city_error', __( '<strong>Error</strong>: Please enter City.', 'crf' ) );
    }
    if ( empty( $_POST['state'] ) ) {
		$errors->add( 'state_error', __( '<strong>Error</strong>: Please enter State.', 'crf' ) );
    }
    if ( empty( $_POST['zip'] ) ) {
		$errors->add( 'zip_error', __( '<strong>Error</strong>: Please enter Zip.', 'crf' ) );
    }

	return $errors;
}

function crf_user_register( $user_id ) {	
    if ( !empty( $_POST['first_name'] ) ) {
		update_user_meta( $user_id, 'first_name', $_POST['first_name'] ) ;
    }
    if ( !empty( $_POST['last_name'] ) ) {
		update_user_meta( $user_id, 'last_name', $_POST['last_name'] ) ;
    }
    if ( !empty( $_POST['phone_number'] ) ) {
		update_user_meta( $user_id, 'phone_number', $_POST['phone_number'] ) ;
	}
    if ( !empty( $_POST['address1'] ) ) {
		update_user_meta( $user_id, 'address1', $_POST['address1'] ) ;
    }
    if ( !empty( $_POST['city'] ) ) {
		update_user_meta( $user_id, 'city', $_POST['city'] ) ;
	}
    if ( !empty( $_POST['state'] ) ) {
		update_user_meta( $user_id, 'state', $_POST['state'] ) ;
    } 
    if ( !empty( $_POST['zip'] ) ) {
		update_user_meta( $user_id, 'zip', $_POST['zip'] ) ;
	}
}

function save_extra_user_profile_fields( $user_id ) {

    if ( !current_user_can( 'edit_user', $user_id ) ) { return false; }
    
    update_user_meta( $user_id, 'phone_number', $_POST['phone_number'] );
    update_user_meta( $user_id, 'address1', $_POST['address1'] );
    update_user_meta( $user_id, 'address2', $_POST['address2'] );
    update_user_meta( $user_id, 'city', $_POST['city'] );
    update_user_meta( $user_id, 'state', $_POST['state'] );
    update_user_meta( $user_id, 'zip', $_POST['zip'] );
}

function extra_user_profile_fields($user) { 
    global $STATES;
    $state = esc_attr( get_the_author_meta( 'state', $user->ID ) );

?>
    <h3><?php _e("Extra profile information", "blank"); ?></h3>

    <table class="form-table">
        <tr>
            <th>
                <label for="phone_number"><?php _e("Phone Number"); ?></label>
            </th>
            <td>
                <input type="text" name="phone_number" id="phone_number" value="<?php echo esc_attr( get_the_author_meta( 'phone_number', $user->ID ) ); ?>" class="regular-text" /><br />
                <span class="description"><?php _e("Please enter your Phone Number."); ?></span>
            </td>
        </tr>
        <tr>
            <th>
                <label for="address1"><?php _e("Street Address"); ?></label>
            </th>
            <td>
                <input type="text" name="address1" id="address1" value="<?php echo esc_attr( get_the_author_meta( 'address1', $user->ID ) ); ?>" class="regular-text" /><br />
                <span class="description"><?php _e("Please enter your Street."); ?></span>
            </td>
        </tr>
        <tr>
            <th>
                <label for="address2"><?php _e("Apt/Ste"); ?></label>
            </th>
            <td>
                <input type="text" name="address2" id="address2" value="<?php echo esc_attr( get_the_author_meta( 'address2', $user->ID ) ); ?>" class="regular-text" /><br />
                <span class="description"><?php _e("Please enter your Apt/Ste."); ?></span>
            </td>
        </tr>
        <tr>
            <th>
                <label for="city"><?php _e("City"); ?></label>
            </th>
            <td>
                <input type="text" name="city" id="city" value="<?php echo esc_attr( get_the_author_meta( 'city', $user->ID ) ); ?>" class="regular-text" /><br />
                <span class="description"><?php _e("Please enter your City."); ?></span>
            </td>
        </tr>
        <tr>
            <th>
                <label for="state"><?php _e("State"); ?></label>
            </th>
            <td>
                <select class="input" id="state" name="state">
                    <option value="">-- Select State --</option>
                    <?php foreach ($STATES as $ele) :?>
                    <option value="<?= $ele['abbr'] ?>" <?= $ele['abbr'] == $state ? "selected" : "" ?> ><?= $ele['name'] ?></option>    
                    <?php endforeach; ?>
                </select><br/>
                <span class="description"><?php _e("Please enter your State."); ?></span>
            </td>
        </tr>
        <tr>
            <th>
                <label for="zip"><?php _e("Zip"); ?></label>
            </th>
            <td>
                <input type="text" name="zip" id="zip" value="<?php echo esc_attr( get_the_author_meta( 'zip', $user->ID ) ); ?>" class="regular-text" /><br />
                <span class="description"><?php _e("Please enter your Zip."); ?></span>
            </td>
        </tr>
    </table>
<?php }

