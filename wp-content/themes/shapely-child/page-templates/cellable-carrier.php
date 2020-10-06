<?php

/*
Template Name: Cellable Carrier
Template Post Type: page
*/

require('wp-blog-header.php');
$user = wp_get_current_user(); // ID->0: if user is not logged in

if ($user->ID==0):
	/**
	 * Push the necessary variables into Session Variable
	 * These values will be reused after user successfully logins or register
	 *  
	 * */
	$url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
	
	$obj = [];
	
	$obj["phone_version_id"] = isset($_REQUEST['phone_version_id']) ? $_REQUEST['phone_version_id'] : null;
	$obj["phone_id"] = isset($_REQUEST['phone_id']) ? $_REQUEST['phone_id'] : null; 
	$obj["url"] = $url;

	$_SESSION['cellable_obj'] = $obj;
	
endif;
get_header();
?>

<?php $layout_class = shapely_get_layout_class(); ?>
<h1>Select Your Carrier</h1>

	<div class="row" id="carrier">
		<div id="primary" class="col-md-12 mb-xs-24 phone-page">
			<?php
			while (have_posts()) : the_post();
				the_content();
			endwhile; // End of the loop.

			$phone_version_id = null;
			$brand_id = null;

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

				$phone_version_id = $obj['phone_version_id'];
				$brand_id = $obj['phone_id'];
			}
			else {
				$phone_version_id = isset($_GET['phone_version_id']) ? $_GET['phone_version_id'] : null;
				$brand_id = isset($_GET['phone_id']) ? $_GET['phone_id'] : null;
			}

			$carriers = $wpdb->get_results("SELECT * FROM ". $wpdb->base_prefix. "cellable_carriers order by position", ARRAY_A);
			
			if (!$brand_id) {
			?>
				<p>Please select your carrier.</p>
				<a href="<?=get_home_url() ?>">Go To Homepage</a>
			<?php
				return;
			}
			?>

			<div class="text-center">

				<?php foreach ($carriers as $carrier): ?>
				<div class="col-sm-4 text-center carrier">
					
					<?php if (!$phone_version_id): ?>
					<a href="<?=get_home_url() ?>/phone-versions/?brand_id=<?=$brand_id?>&carrier_id=<?=$carrier['id']?>" class="btn btn-default">
						<?php if ($carrier['image_file']): ?>						
						<img src="<?= $carrier['image_file'] ?>" alt="<?= $carrier['name'] ?>">
						<?php else: ?>							
						<div style="height:30px;"></div>
						<?=$carrier['name']?>
						<?php endif; ?>
					</a>
					<?php 
					else: 
						$phone_version_carrier = $wpdb->get_row($wpdb->prepare("SELECT * FROM ". $wpdb->base_prefix."cellable_version_carriers 
							WHERE phone_version_id= %d and carrier_id = %d", $phone_version_id, $carrier['id']), ARRAY_A);
						
						
						// $wpdb->get_row("SELECT * FROM ".$wpdb->base_prefix . "cellable_version_carriers 
						// 	WHERE phone_version_id=" . $phone_version_id." and carrier_id =" . $carrier['id'], ARRAY_A);

						if (!$phone_version_carrier) {
							continue;
						}
					?>
					<a href="<?=get_home_url() ?>/defect-questions/?phone_version_id=<?=$phone_version_id?>&carrier_id=<?=$carrier['id']?>" class="btn btn-default">
						<?php if ($carrier['image_file']): ?>
						<img src="<?= $carrier['image_file'] ?>" alt="<?= $carrier['name'] ?>">
						<?php else: ?>
						<div style="height:30px;"></div>
						<?=$carrier['name']?>
						<?php endif; ?>
					</a>
					<?php endif; ?>
				</div>
				<?php endforeach; ?>
			</div><!-- #primary -->
		</div>
	</div>

<?php
get_footer();