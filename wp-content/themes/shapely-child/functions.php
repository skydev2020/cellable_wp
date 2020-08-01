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




function admin_default_page() {
    return "http://127.0.0.1/cellable/";
  }
  
add_filter('login_redirect', 'admin_default_page');

add_filter( 'get_search_form', 'cellable_search_form', 101 ); // Higher Priority means redefine the form

add_action( 'show_user_profile', 'extra_user_profile_fields' );
add_action( 'edit_user_profile', 'extra_user_profile_fields' );

function extra_user_profile_fields( $user ) { ?>
    <h3><?php _e("Extra profile information", "blank"); ?></h3>

    <table class="form-table">
    <tr>
    <th><label for="address"><?php _e("Address"); ?></label></th>
    <td>
    <input type="text" name="address" id="address" value="<?php echo esc_attr( get_the_author_meta( 'address', $user->ID ) ); ?>" class="regular-text" /><br />
    <span class="description"><?php _e("Please enter your address."); ?></span>
    </td>
    </tr>
    <tr>
    <th><label for="city"><?php _e("City"); ?></label></th>
    <td>
    <input type="text" name="city" id="city" value="<?php echo esc_attr( get_the_author_meta( 'city', $user->ID ) ); ?>" class="regular-text" /><br />
    <span class="description"><?php _e("Please enter your city."); ?></span>
    </td>
    </tr>
    <tr>
    <th><label for="province"><?php _e("Province"); ?></label></th>
    <td>
    <input type="text" name="province" id="province" value="<?php echo esc_attr( get_the_author_meta( 'province', $user->ID ) ); ?>" class="regular-text" /><br />
    <span class="description"><?php _e("Please enter your province."); ?></span>
    </td>
    </tr>
    <tr>
    <th><label for="postalcode"><?php _e("Postal Code"); ?></label></th>
    <td>
    <input type="text" name="postalcode" id="postalcode" value="<?php echo esc_attr( get_the_author_meta( 'postalcode', $user->ID ) ); ?>" class="regular-text" /><br />
    <span class="description"><?php _e("Please enter your postal code."); ?></span>
    </td>
    </tr>
    </table>
<?php }

add_action( 'personal_options_update', 'save_extra_user_profile_fields' );
add_action( 'edit_user_profile_update', 'save_extra_user_profile_fields' );

function save_extra_user_profile_fields( $user_id ) {

if ( !current_user_can( 'edit_user', $user_id ) ) { return false; }

update_user_meta( $user_id, 'address', $_POST['address'] );
update_user_meta( $user_id, 'city', $_POST['city'] );
update_user_meta( $user_id, 'province', $_POST['province'] );
update_user_meta( $user_id, 'postalcode', $_POST['postalcode'] );


}

