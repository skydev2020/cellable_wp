<?php
/*
Template Name: Cellable Phone Version
Template Post Type: page
*/
require_once(ABSPATH . 'wp-content/themes/shapely-child/cellable_global.php');
get_header(); 
?>

<?php $layout_class = shapely_get_layout_class(); ?>
	<div class="row">
		<div id="primary" class="col-md-12 mb-xs-24">
			<?php
			while ( have_posts() ) : the_post();				
				the_content();
			endwhile; // End of the loop.
			
			$search_str = $_GET['q'];
			$brand_id = $_GET['brand_id'];
			$carrier_id = $_REQUEST['carrier_id'];

			if ($search_str) {
				// Get filtered Phone Versions list
				$phone_versions = $wpdb->get_results($wpdb->prepare("SELECT * FROM ". $wpdb->base_prefix. "cellable_phone_versions where name like %s order by position desc", 
				'%'.$wpdb->esc_like($search_str).'%'), ARRAY_A);
			}
			else {
				// Get entire list of Phone Versions to pass to the view
				$phone_versions = $wpdb->get_results($wpdb->prepare("SELECT * FROM ". $wpdb->base_prefix. "cellable_phone_versions where active = true and phone_id = %d order by position desc", 
				$wpdb->esc_like($brand_id)), ARRAY_A);
			}
			?>
			<div class="text-center">
				<?php foreach ($phone_versions as $ele): ?>
				<div class="col-sm-3 text-center phone-version">
					<a class="btn btn-default" href="<?=get_home_url() ?>/defect-questions/?phone_version_id=<?=$ele['id']?>&carrier_id=<?= $carrier_id?>">
						<img src="<?= $PHONE_IMAGE_LOCATION ?>/<?= $ele['image_file'] ?>" alt="<?= $ele['name'] ?>">
					</a>
					<br />
					<?php
					$phone = $wpdb->get_row("SELECT brand FROM ". $wpdb->base_prefix. "cellable_phones WHERE id=" . $ele['phone_id']);					
					?>
					<?= $phone->brand?>
					<br />
					<?= $ele['name']?>
				</div>
				<?php endforeach; ?>
			</div>
		</div><!-- #primary -->
	</div>
<?php
get_footer();