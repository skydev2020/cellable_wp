<?php
/*
Template Name: Cellable Phone
Template Post Type: page
*/
get_header(); 
?>

<?php $layout_class = shapely_get_layout_class(); ?>
	<div class="row">
		<div id="primary" class="col-md-12 mb-xs-24 phone-page">
			<?php
			while ( have_posts() ) : the_post();				
				the_content();
			endwhile; // End of the loop.
			$carriers = $wpdb->get_results("SELECT * FROM wp_cellable_carriers order by position", ARRAY_A);
			$search = $_GET['search'];
			?>
			<div class="text-center">
				<?php foreach ($carriers as $carrier): ?>
				<div class="col-sm-3 text-center carrier">
					<?php if (!$search): ?>
					<a href="" class="btn btn-default">
						<?php if ($carrier['image_file']): ?>						
						<img src="<?= get_stylesheet_directory_uri()?>/assets/images/<?= $carrier['image_file'] ?>" alt="<?= $carrier['name'] ?>">
						<?php else: ?>							
						<div style="height:30px;"></div>
						<?=$carrier['name']?>
						<?php endif; ?>
					</a>
					<?php else: ?>					
					<a href="" class="btn btn-default">						
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