<?php
/*
Template Name: Cellable Phone
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
	$obj["url"] = $url;

	$_SESSION['cellable_obj'] = $obj;	
endif;
get_header(); 
?>

<?php $layout_class = shapely_get_layout_class(); ?>
	<div class="row">
		<div id="primary" class="col-md-12 mb-xs-24 homepage">
			<?php
			while ( have_posts() ) : the_post();
				the_content();
			endwhile; // End of the loop.

			$phones = $wpdb->get_results("SELECT * FROM ". $wpdb->base_prefix."cellable_phones", ARRAY_A);
			?>

			<div class="text-center full-width inline-block">
				<?php foreach ($phones as $phone): ?>
				<div class="col-sm-4 text-center phone">
					<a class="btn btn-default" href="<?=get_home_url() ?>/carriers/?phone_id=<?=$phone['id']?>">
						<img class="phone-image" src="<?= $phone['image_file'] ?>">
						<p class="text-center title"><?= $phone['name'] ?></p>
					</a>
				</div>
				<?php endforeach; ?>
			</div>

			<div class="text-center">
			</div>
		</div><!-- #primary -->
	</div>
<?php
get_footer();