<?php
/*
Template Name: Cellable Carrier
Template Post Type: page
*/
require('wp-blog-header.php');
get_header(); 
?>

<?php $layout_class = shapely_get_layout_class(); ?>
	
<h1>Select Your Carrier</h1>

<div class="row" id="carrier">
		<div id="primary" class="col-md-12 mb-xs-24 phone-page">
			<?php
			while ( have_posts() ) : the_post();				
				the_content();
			endwhile; // End of the loop.
			$carriers = $wpdb->get_results("SELECT * FROM ". $wpdb->base_prefix. "cellable_carriers order by position", ARRAY_A);
			$phone_version_id = isset($_GET['phone_version_id']) ? $_GET['phone_version_id'] : "";
			$brand_id = isset($_GET['phone_id']) ? $_GET['phone_id'] : "";
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
							$phone_version_carrier = $wpdb->get_row("SELECT * FROM ".$wpdb->base_prefix . "cellable_version_carriers 
				WHERE phone_version_id=" . $phone_version_id." and carrier_id =" . $carrier['id'], ARRAY_A);
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
			</div>
		</div><!-- #primary -->
	</div>
<?php
get_footer();