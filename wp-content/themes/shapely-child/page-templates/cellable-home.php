<?php
/*
Template Name: Cellable Homepage
Template Post Type: page
*/
get_header(); 
?>

<?php $layout_class = shapely_get_layout_class(); ?>
	<div class="row">
		<div id="primary" class="col-md-12 mb-xs-24 homepage">
			
			<?php
			while ( have_posts() ) : the_post();

				// get_template_part( 'template-parts/content' );
				the_content();
	
			endwhile; // End of the loop.
			$phones = $wpdb->get_results("SELECT * FROM wp_cellable_phones", ARRAY_A);
			?>
			<div class="text-center">
				<?php foreach ($phones as $phone): ?>
				<div class="col-sm-4 text-center phone">
					<a class="btn btn-default" href="/Phones/Carriers/1">
						<img src="<?= get_stylesheet_directory_uri()?>/assets/images/<?= $phone['image_file'] ?>" style="width: 185px; height: 224px; margin-top: 27px; padding-top: 10px;">
						<p class="text-center" style="font-family: 'HP Simplified'; padding-top: 50px; font-size: 25px; width: 249px; height: 70px; color: #000; margin-right: 50px;"><?= $phone['brand'] ?></p>
					</a>
				</div>
				<?php endforeach; ?>
			</div>
		</div><!-- #primary -->
	</div>
<?php
get_footer();