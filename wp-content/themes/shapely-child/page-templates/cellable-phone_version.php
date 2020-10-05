<?php
/*
Template Name: Cellable Phone Version
Template Post Type: page
*/
require_once(ABSPATH . 'wp-content/plugins/cellable/cellable_global.php');
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
		
		$search_str = isset($_GET['q']) ? $_GET['q'] : "";
		$brand_id = isset($_GET['brand_id']) ? $_GET['brand_id'] : ""; 
		$carrier_id = isset($_REQUEST['carrier_id']) ? $_REQUEST['carrier_id'] : ""; 

		if ($search_str) {
			// Get filtered Phone Versions list
			$phone_versions = $wpdb->get_results($wpdb->prepare(
				"SELECT pv.id id, pv.phone_id phone_id, pv.name name, pv.image_file image_file FROM ". $wpdb->base_prefix. "cellable_phone_versions pv, "
				.$wpdb->base_prefix."cellable_version_carriers vc "
				."where pv.status = true  and vc.phone_version_id=pv.id and vc.carrier_id=". $carrier_id." and pv.name like %s order by position desc, name desc", 
			'%'.$wpdb->esc_like($search_str).'%'), ARRAY_A);
		}
		else {
			// Get entire list of Phone Versions to pass to the view
			$phone_versions = $wpdb->get_results($wpdb->prepare(
				"SELECT pv.id id, pv.phone_id phone_id, pv.name name, pv.image_file image_file FROM ". $wpdb->base_prefix. "cellable_phone_versions pv, "
				.$wpdb->base_prefix."cellable_version_carriers vc "
				."where pv.status = true and vc.phone_version_id=pv.id and vc.carrier_id=". $carrier_id." and pv.phone_id = %d order by pv.position desc, pv.name", 
			$wpdb->esc_like($brand_id)), ARRAY_A);
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
				$phone = $wpdb->get_row("SELECT name FROM ". $wpdb->base_prefix. "cellable_phones WHERE id=" . $ele['phone_id']);					
				?>
				<?= $phone->name?>
				<br />
					<?= $ele['name']?></p>
			</div>
			<?php endforeach; ?>
		</div>
	</div><!-- #primary -->
</div>
<?php
get_footer();