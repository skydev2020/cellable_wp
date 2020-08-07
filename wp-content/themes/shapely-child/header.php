<?php
/**
 * The header for our theme.
 *
 * This is the template that displays all of the <head> section and everything up until <div id="content">
 *
 * @link    https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package Shapely
 */

?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="profile" href="http://gmpg.org/xfn/11">
	<link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>">

	<?php wp_head(); ?>
</head>

<?php
	
 	$rating_avg = $wpdb->get_var("SELECT avg(rating) FROM " .$wpdb->base_prefix. "cellable_testimonials where published=1");
	$rating_count = $wpdb->get_var("SELECT count(rating) FROM " .$wpdb->base_prefix. "cellable_testimonials where published=1");
?>

<body <?php body_class(); ?>>
<div id="page" class="site">
	<a class="skip-link screen-reader-text" href="#content"><?php esc_html_e( 'Skip to content', 'shapely' ); ?></a>

	<header id="masthead" class="site-header" role="banner">
		<div class="nav-container">
			<nav id="site-navigation" class="main-navigation bg-dark" role="navigation">
				<div class="container nav-bar">
					<div class="row">
						<div class="module left site-title-container">
							<?php shapely_get_header_logo(); ?>
							<?php for ($i=1; $i<=$rating_avg; $i++): ?>
							<span class='fa fa-star checked'></span>
							<?php endfor; ?>
							<span class="rating-score">(ratings: <?= $rating_count ?>)</span>
						</div>
						<div class="module widget-handle mobile-toggle right visible-sm visible-xs">
							<i class="fa fa-bars"></i>
						</div>
						<div class="module-group right menu-container">
							<div class="module left" style="padding-right: 0px;">
								<?php shapely_header_menu(); // main navigation ?>
							</div>
							<div class="module left" style="padding-left: 0px;">
								<div class="collapse navbar-collapse navbar-ex1-collapse">
									<ul id="menu" class="menu">
										<?php if (get_current_user_id()==0) : ?>
										<li class="menu-item menu-item-type-post_type menu-item-object-page">
											<a title="Login" href="<?=get_home_url() ?>/wp-login.php">Login</a>
										</li>
										<?php else: ?>
										<li class="menu-item menu-item-type-post_type menu-item-object-page <?= strpos($_SERVER['REQUEST_URI'], "track-orders") !==false ? "active" : ""?>">
											<a title="Tracking Orders" href="<?=get_home_url() ?>/track-orders/">Tracking</a>
										</li>	
										<li class="menu-item menu-item-type-post_type menu-item-object-page">
											<a title="Profile" href="<?=get_edit_profile_url() ?>">Profile</a>
										</li>
										<li class="menu-item menu-item-type-post_type menu-item-object-page">
											<a title="Logout" href="<?=wp_logout_url() ?>">Logout</a>
										</li>
										<?php endif; ?>
									</ul>
								</div>
							</div>
							<!--end of menu module-->
							<div class="module widget-handle search-widget-handle left hidden-xs hidden-sm">
								<div class="search">
									<i class="fa fa-search"></i>
									<span class="title"><?php esc_html_e( "Site Search", 'shapely' ); ?></span>
								</div>
								<div class="function"><?php
									get_search_form(); ?>
								</div>
							</div>
						</div>
						<!--end of module group-->
					</div>
				</div>
			</nav><!-- #site-navigation -->
		</div>
	</header><!-- #masthead -->
	<div id="content" class="main-container">
		
		<section class="content-area <?php echo ( get_theme_mod( 'top_callout', true ) ) ? '' : ' pt0 ' ?>">
			<div id="main" class="<?php echo ( ! is_page_template( 'page-templates/template-home.php' ) ) ? 'container' : ''; ?>"
			     role="main">