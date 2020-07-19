<?php
/*
Template Name: Cellable Homepage
Template Post Type: page
*/
get_header(); 
?>

<?php $layout_class = shapely_get_layout_class(); ?>
	<div class="row">
		<div id="primary" class="col-md-12 mb-xs-24 <?php echo esc_attr( $layout_class ); ?>">
			
			<?php
			while ( have_posts() ) : the_post();

				// get_template_part( 'template-parts/content' );
				the_content();
	
			endwhile; // End of the loop.
			$phones = $wpdb->get_results("SELECT * FROM wp_cellable_phones", ARRAY_A);
			?>
			<div class="text-center">
				<?php foreach ($phones as $phone): ?>
				<div class="col-sm-4 text-center">
					<a class="btn btn-default" href="/Phones/Carriers/1" style="font-size: 50px; height: 350px; left: 58px; width: 250px; padding: 10px; border-radius: 5px; box-shadow: 0 4px 8px 0 rgba(0, 0, 0, 0.2), 0 6px 20px 0 rgba(0, 0, 0, 0.19); margin-top: 50px; ">
						<img src="<?= get_stylesheet_directory_uri()?>/assets/images/<?= $phone['image_file'] ?>" style="width: 185px; height: 224px; left: 200px; margin-top: 27px; padding-top: 10px;">
						<p style="font-family: 'HP Simplified'; padding-top: 50px; font-size: 25px; width: 249px; height: 70px; color: #000; text-align: center; margin-right: 50px;"><?= $phone['brand'] ?></p>
					</a>
				</div>
				<?php endforeach; ?>
			</div>
		</div><!-- #primary -->
	</div>
<?php
get_footer();