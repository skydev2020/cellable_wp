<?php
/*
Template Name: Cellable Homepage
Template Post Type: page
*/
require('wp-blog-header.php');
get_header(); 
?>

<?php $layout_class = shapely_get_layout_class(); ?>
<div class="row">
	<div id="primary" class="col-md-12 mb-xs-24 homepage">
		<?php
		while ( have_posts() ) : the_post();
			the_content();
		endwhile; // End of the loop.
		?>
		

<!-- Where CreationDepot removed phones and testimonials code -->

	</div><!-- #primary -->
</div>
<!-- removed slider script to speed up loading time -->
<?php
get_footer();