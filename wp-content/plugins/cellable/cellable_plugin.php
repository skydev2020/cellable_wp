<?php
/**
* Plugin Name: Cellable
* Plugin URI: https://www.yourwebsiteurl.com/
* Description: Cellable Backend Plugin
* Version: 1.0
* Author: Sky Dev 
**/
require_once('cellable_global.php');
require_once('views/brands.php');
require_once('views/carriers.php');
require_once('views/payments.php');
require_once('views/promos.php');
require_once('views/versions.php');
require_once('views/settings.php');
require_once('views/capacities.php');
require_once('views/defect_groups.php');
require_once('views/possible_defects.php');
require_once('views/testimonials.php');
require_once('views/orders.php');

/**
 * Add Admin Pages
 */
if ( ! function_exists( 'brand_pages' ) ) {
    function brand_pages(){
        render_brand_list();
    }
}

if ( ! function_exists( 'carrier_pages' ) ) {
    function carrier_pages(){
        render_carrier_list();
    }
}

if ( ! function_exists( 'payment_pages' ) ) {
    function payment_pages(){
        render_payment_list();
    }
}

if ( ! function_exists( 'promo_pages' ) ) {
    function promo_pages(){
        render_promo_list();
    }
}

if ( ! function_exists( 'version_pages' ) ) {
    function version_pages(){
        render_version_list();
    }
}

if ( ! function_exists( 'order_pages' ) ) {
    function order_pages(){
        render_order_list();
    }
}

if ( ! function_exists( 'defect_group_pages' ) ) {
    function defect_group_pages(){
        render_defect_group_list();
    }
}

if ( !function_exists( 'possible_defect_pages' ) ) {
    function possible_defect_pages(){
        render_possible_defect_list();
    }
}

if ( ! function_exists( 'setting_pages' ) ) {
    function setting_pages(){
        render_setting_list();
    }
}

if ( ! function_exists( 'capacity_pages' ) ) {
    function capacity_pages(){
        render_capacity_list();
    }
}

if ( !function_exists( 'testimonial_pages' ) ) {
    function testimonial_pages(){
        render_testimonial_list();
    }
}

/**
 * Add SuperAdmin & SubAdmin Menus
 */
if ( ! function_exists( 'admin_add_pages' ) ) {
   
    function admin_add_pages() {
        
        if(current_user_can('administrator')){
            add_menu_page("Cellable", "Cellable", "manage_options", "cellable","orders","dashicons-networking", 4);
            add_submenu_page('cellable','Brands', 'Brands', 'manage_options', 'brand_pages','brand_pages');
            add_submenu_page('cellable','Carriers', 'Carriers', 'manage_options', 'carrier_pages','carrier_pages');
            add_submenu_page('cellable','Payment Types', 'Payment Types', 'manage_options', 'payment_pages','payment_pages');
            add_submenu_page('cellable','Promotions', 'Promotions', 'manage_options', 'promo_pages','promo_pages');
            add_submenu_page('cellable','Phone Versions', 'Phone Versions', 'manage_options', 'version_pages','version_pages');
            add_submenu_page('cellable','Defect Groups', 'Defect Groups', 'manage_options', 'defect_group_pages','defect_group_pages');
            add_submenu_page('cellable','Possible Defects', 'Possible Defects', 'manage_options', 'possible_defect_pages','possible_defect_pages');
            add_submenu_page('cellable','Storage Capacities', 'Storage Capacities', 'manage_options', 'capacity_pages','capacity_pages');
            add_submenu_page('cellable','Orders', 'Orders', 'manage_options', 'order_pages','order_pages');
            add_submenu_page('cellable','Testimonials', 'Testimonials', 'manage_options', 'testimonial_pages','testimonial_pages');
            add_submenu_page('cellable','Settings', 'System Settings', 'manage_options', 'setting_pages','setting_pages');
            remove_submenu_page('cellable', 'cellable');
        }
        else{
          
        }

        
    }
}

function get_cellable_setting($name) {
    global $wpdb;

    $content = $wpdb->get_var("SELECT value FROM ". $wpdb->base_prefix ."cellable_settings WHERE name='" . $name."'");    
	return $content;
}

 
function cellable_css_js()
{
    wp_enqueue_style('fontawesome_css', plugins_url('css/font-awesome.css',__FILE__ ),'','all');
    wp_enqueue_style('cellable_css', plugins_url('css/admin.css',__FILE__ ),'','all');
    wp_enqueue_media();
    wp_enqueue_script( 'wp-media-uploader', plugins_url('js/wp_media_uploader.js', __FILE__), array( 'jquery' ), 1.0 );
    wp_enqueue_script( 'cellable-admin', plugins_url('js/admin.js', __FILE__));
    wp_localize_script('wp-media-uploader', 'cellable_admin_url',array( 'ajax_url' => plugins_url('views/actions.php', __FILE__) ));
}


add_action('admin_menu', 'admin_add_pages');
add_action('admin_enqueue_scripts', 'cellable_css_js');
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
add_filter('login_redirect', 'user_default_redirect_page' ,10, 3);
add_filter('logout_redirect', 'home_page',10, 3);

function user_default_redirect_page($url, $request, $user) {       
    if (isset($user->ID) && ($user->ID>0)) {
        // When login success
        $str = date_create()->format('Y-m-d H:i:s');
        update_user_meta($user->ID, 'last_login', $str);
                
        $obj = $_SESSION['cellable_obj'];
				
		if (!$obj || is_array($obj) !== true) {
            return get_home_url();
        }
    
        $rtr_url = $obj['url']."&call_back=1";
        return $rtr_url;
    }
}

function home_page($url, $request, $user) {
    // clear the session variable
    $_SESSION['cellable_obj'] = null;
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
    $password = !empty( $_POST['password'] ) ? $_POST['password']  : '';
    $cpassword = !empty( $_POST['cpassword'] ) ? $_POST['cpassword']  : '';
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
        <label for="password">Password<br/>
			<input type="password" id="password" name="password" value="<?php echo esc_attr( $password ); ?>"
			       class="input"/>
		</label>
        <label for="cpassword">Confirm Password<br/>
			<input type="password" id="cpassword" name="cpassword" value="<?php echo esc_attr( $cpassword ); ?>"
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
    if ( empty( $_POST['password'] ) ) {
		$errors->add( 'password_error', __( '<strong>Error</strong>: Please enter Password.', 'crf' ) );
    }
    if ($_POST['password'] !== $_POST['cpassword']) {
		$errors->add( 'cpassword_error', __( '<strong>Error</strong>: Please confirm Password.', 'crf' ) );
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
    
    // setup the password
    $password = $_POST['password'];

    wp_set_password($password, $user_id);
    wp_set_current_user($user_id);
    wp_set_auth_cookie($user_id);
    wp_redirect( home_url() ); // You can change home_url() to the specific URL,such as "wp_redirect( 'http://www.wpcoke.com' )";
    exit();

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

// Custom Dashboard
add_action('wp_dashboard_setup', 'cellable_new_orders_dashboard_widgets');
add_action('wp_dashboard_setup', 'cellable_most_viewed_dashboard_widgets');
add_action('wp_dashboard_setup', 'cellable_most_purchased_dashboard_widgets');
add_action('wp_dashboard_setup', 'cellable_promotions_dashboard_widgets');

function cellable_new_orders_dashboard_widgets() {
    global $wp_meta_boxes;
    
    wp_add_dashboard_widget('cellable_new_orders_widget', 'New Orders', 'new_orders_widget_section');
}

function cellable_most_viewed_dashboard_widgets() {
    wp_add_dashboard_widget('cellable_viewed_versions_widget', 'Top 10 Viewed Versions', 'viewed_versions_widget_section');
}

function cellable_most_purchased_dashboard_widgets() {
    wp_add_dashboard_widget('cellable_purchased_versions_widget', 'Top 10 Purchased Versions', 'purchased_versions_widget_section');
}

function cellable_promotions_dashboard_widgets() {
    wp_add_dashboard_widget('cellable_promotions_widget', 'Promotions', 'promotions_widget_section');
}
 


function new_orders_widget_section() {
    global $wpdb;
    
    $sql_str = "select o.*, pt.name pay_name FROM ".$wpdb->base_prefix . "cellable_orders o ";
    $sql_str .= "left join ".$wpdb->base_prefix."cellable_payment_types pt on o.payment_type_id = pt.id ";
    $sql_str .= "where o.order_status_id=1 ";

    $data = $wpdb->get_results($sql_str,ARRAY_A);
    
    $str ="<table style='width:100%;' class='striped cellable-widget'><thead>";
    $str .="<tr><td class='text-center' style='width: 30%;'>Amount</td>";
    $str .="<td class='text-center' style='width: 45%;'>Created Date</td>";
    $str .="<td class='text-center'>Payment Method</td></tr>";
    $str .="</thead><tbody>";
    
    foreach ($data as $ele):
        $created_date="";
        if ($ele['created_date']) {
            $date = new DateTime($ele['created_date']);
            $created_date = $date->format('m/d/Y h:i:s A');            
        }
        
        $str .= "<tr>";
        $str .= "<td class='text-center'>$". $ele["amount"]. "</td>";
        $str .= "<td class='text-center'>". $created_date. "</td>";
        $str .= "<td class='text-center'>". $ele["pay_name"]. "</td>";
        $str .= "</tr>";
    endforeach;
    $str .="</tbody></table>";

    echo $str;
}

function viewed_versions_widget_section() {
    global $wpdb;
    
    $sql_str = "select * FROM ".$wpdb->base_prefix . "cellable_phone_versions ";    
    $sql_str .= "where status=true order by views desc limit 10";

    $data = $wpdb->get_results($sql_str,ARRAY_A);
    
    $str ="<table style='width:100%;' class='striped cellable-widget'><thead>";
    $str .="<tr><td class='text-center'>Version</td><td class='text-center'>Views</td></tr>";
    $str .="</thead><tbody>";
    foreach ($data as $ele):
        $str .= "<tr>";
        $str .= "<td class='text-center'>". $ele["name"]. "</td>";
        $str .= "<td class='text-center'>". $ele["views"]. "</td>";
        $str .= "</tr>";
    endforeach;
    $str .="</tbody></table>";

    echo $str;
}

function purchased_versions_widget_section() {
    global $wpdb;
    
    $sql_str = "select * FROM ".$wpdb->base_prefix . "cellable_phone_versions ";    
    $sql_str .= "where status=true order by purchases desc limit 10";

    $data = $wpdb->get_results($sql_str,ARRAY_A);
    
    $str ="<table style='width:100%;' class='striped cellable-widget'><thead>";
    $str .="<tr><td class='text-center'>Version</td><td class='text-center'>Sales</td></tr>";
    $str .="</thead><tbody>";
    foreach ($data as $ele):
        $str .= "<tr>";
        $str .= "<td class='text-center'>". $ele["name"]. "</td>";
        $str .= "<td class='text-center'>". $ele["purchases"]. "</td>";
        $str .= "</tr>";
    endforeach;
    $str .="</tbody></table>";

    echo $str;
}

function promotions_widget_section() {
    global $wpdb;
    
    $sql_str = "select * FROM ".$wpdb->base_prefix . "cellable_promos ";

    $data = $wpdb->get_results($sql_str,ARRAY_A);
    
    $str ="<table style='width:100%;' class='striped cellable-widget'><thead>";
    $str .="<tr>";
    $str .="<td class='text-center'>Code</td><td class='text-center'>Name</td>";
    $str .="<td class='text-center'>Start Date</td><td class='text-center'>End Date</td>";
    $str .="<td class='text-center'>Discount</td><td class='text-center'>Dollar Value</td>";
    $str .="</tr>";
    $str .="</thead><tbody>";

    foreach ($data as $ele):
        $str .= "<tr>";
        $str .= "<td class='text-center'>". $ele["code"]. "</td>";
        $str .= "<td class='text-center'>". $ele["name"]. "</td>";
        $str .= "<td class='text-center'>". $ele["start_date"]. "</td>";
        $str .= "<td class='text-center'>". $ele["end_date"]. "</td>";
        $str .= "<td class='text-center'>". $ele["discount"]. "</td>";
        $str .= "<td class='text-center'>". $ele["dollar_value"]. "</td>";
        $str .= "</tr>";
    endforeach;
    $str .="</tbody></table>";

    echo $str;
}

