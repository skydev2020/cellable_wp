<?php
/*
Template Name: Cellable Phone Version
Template Post Type: page
*/

require_once(ABSPATH . 'wp-content/plugins/cellable/cellable_global.php');

$user = wp_get_current_user(); // ID->0: if user is not logged in
if ($user->ID==0):
	/**
	 * Push the necessary variables into Session Variable
	 * These values will be reused after user successfully logins or register
	 *  
	 * */
	$url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
	
	$obj = [];
	
	$obj["q"] = isset($_GET['q']) ? $_GET['q'] : "";
	$obj["brand_id"] = isset($_GET['brand_id']) ? $_GET['brand_id'] : null; 
	$obj["carrier_id"] = isset($_REQUEST['carrier_id']) ? $_REQUEST['carrier_id'] : null; 
	$obj["url"] = $url;

	$_SESSION['cellable_obj'] = $obj;
	
endif;
get_header();
?>

<?php $layout_class = shapely_get_layout_class(); ?>
<h1>Which Phone Do You Have?</h1>

	<div class="row">
		<div id="primary" class="col-md-12 mb-xs-24">
			<?php
			while ( have_posts() ) : the_post();
				the_content();
			endwhile; // End of the loop.

			$search_str = null;
			$brand_id = null; 
			$carrier_id = null; 

			if (isset($_REQUEST['call_back']) && $_REQUEST['call_back'] == "1") {
				$obj = $_SESSION['cellable_obj'];
				
				if (!$obj || is_array($obj) !== true) {
					// Stored session variable is expired, go to first page.
				?>
					<p>Session is expired.Please start from homepage again.</p>
					<a href="<?=get_home_url() ?>">Go To Homepage</a>
				<?php
					return;
				}

				$carrier_id = $obj['carrier_id'];
				$brand_id = $obj['brand_id'];
				$search_str = $obj["q"];
			}
			else {
				$search_str = isset($_GET['q']) ? $_GET['q'] : "";
				$brand_id = isset($_GET['brand_id']) ? $_GET['brand_id'] : ""; 
				$carrier_id = isset($_REQUEST['carrier_id']) ? $_REQUEST['carrier_id'] : ""; 
			}

			if ($search_str) {
				// Get filtered Phone Versions list
				$phone_versions = $wpdb->get_results($wpdb->prepare(
					"SELECT pv.id id, pv.phone_id phone_id, pv.name name, pv.image_file image_file FROM ". $wpdb->base_prefix. "cellable_phone_versions pv, "
					.$wpdb->base_prefix."cellable_version_carriers vc "
					."where pv.status = true  and vc.phone_version_id=pv.id and vc.carrier_id=%d and pv.name like %s order by position desc, name desc", 
					$carrier_id, '%'.$wpdb->esc_like($search_str).'%'), ARRAY_A);
			}
			else {
				// Get entire list of Phone Versions to pass to the view
				$phone_versions = $wpdb->get_results($wpdb->prepare(
					"SELECT pv.id id, pv.phone_id phone_id, pv.name name, pv.image_file image_file FROM ". $wpdb->base_prefix. "cellable_phone_versions pv, "
					.$wpdb->base_prefix."cellable_version_carriers vc "
					."where pv.status = true and vc.phone_version_id=pv.id and vc.carrier_id=%d and pv.phone_id = %d order by pv.position desc, pv.name", 
					$carrier_id, $brand_id), ARRAY_A);
			}
			?>

			<div class="text-center">
				<?php if (count($phone_versions) == 0) : ?>
				There is no available phone versions for this carrier. <a href="<?= get_home_url() ?>">HomePage</a>
				<?php endif; ?>

				<?php foreach ($phone_versions as $ele): ?>
				<div class="col-sm-3 text-center phone-version">
					<a class="btn btn-default" href="<?=get_home_url() ?>/defect-questions/?phone_version_id=<?=$ele['id']?>&carrier_id=<?= $carrier_id?>">
						<img src="<?= $ele['image_file'] ?>" alt="<?= $ele['name'] ?>">
					</a>
					<p class="phone_type">
					<?php
					
					$phone = $wpdb->get_row($wpdb->prepare("SELECT name FROM ". $wpdb->base_prefix."cellable_phones 
						WHERE id= %d", $ele['phone_id']));
				
					// $phone = $wpdb->get_row("SELECT name FROM ". $wpdb->base_prefix. "cellable_phones WHERE id=" . $ele['phone_id']);
					?>
						<?= $phone->name?>
					<br/>
						<?= $ele['name']?>
					</p>
				</div>
				<?php endforeach; ?>
			</div>

		</div><!-- #primary -->
	</div>

<?php
get_footer();