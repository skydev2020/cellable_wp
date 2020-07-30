<?php
/*
Template Name: Cellable Carrier
Template Post Type: page
*/
require('wp-blog-header.php');
get_header(); 
?>

<?php $layout_class = shapely_get_layout_class(); ?>
	<div class="row">
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
			<p>Please select the Phone Brand.</p>
			<a href="<?=get_home_url() ?>">Go To Homepage</a>
			<?php
				return;
			}
			?>
			<div class="text-center">
				<?php foreach ($carriers as $carrier): ?>
				<div class="col-sm-3 text-center carrier">
					<?php if (!$phone_version_id): ?>
					<a href="<?=get_home_url() ?>/phone-versions/?brand_id=<?=$brand_id?>&carrier_id=<?=$carrier['id']?>" class="btn btn-default">
						<?php if ($carrier['image_file']): ?>						
						<img src="<?= get_stylesheet_directory_uri()?>/assets/images/<?= $carrier['image_file'] ?>" alt="<?= $carrier['name'] ?>">
						<?php else: ?>							
						<div style="height:30px;"></div>
						<?=$carrier['name']?>
						<?php endif; ?>
					</a>
					<?php else: ?>
					<a href="<?=get_home_url() ?>/defect-questions/?phone_version_id=<?=$phone_version_id?>&carrier_id=<?=$carrier['id']?>" class="btn btn-default">						
						<?php if ($carrier['image_file']): ?>
						<img src="<?= get_stylesheet_directory_uri()?>/assets/images/<?= $carrier['image_file'] ?>" alt="<?= $carrier['name'] ?>">
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