<?php
/**
 * The template for displaying the footer.
 *
 * Contains the closing of the #content div and all content after.
 *
 * @link    https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package Shapely
 */

?>

</div><!-- #main -->
</section><!-- section -->

<div class="footer-callout">
	<?php shapely_footer_callout(); ?>
</div>

<footer id="colophon" class="site-footer footer bg-dark" role="contentinfo">
	<div class="container footer-inner">
		<div class="row">
			<?php get_sidebar( 'footer' ); ?>
		</div>

		<div class="row">
			<div class="site-info col-sm-4">
				<div class="copyright-text">
					<?php echo wp_kses_post( get_theme_mod( 'shapely_footer_copyright' ) ); ?>
				</div>
				<div class="footer-credits">
					&copy; <?=date("Y");?> - Cellable
				</div>
			</div><!-- .site-info -->
			<div class="col-sm-4 text-center">
				<a href="mailto:<?= get_option('admin_email')?>" class="NavLink">Support</a>
                <div style="width:30px; display:inline-block"></div>
                <a class="NavLink" href="<?= get_home_url() ?>/about">About Us</a>
                <div style="width:30px; display:inline-block"></div>
                <a class="NavLink" href="<?= get_home_url() ?>/contact">Contact Us</a>
			</div>
			<div class="col-sm-4 text-right">
				<?php shapely_social_icons(); ?>
			</div>
		</div>
	</div>

	<a class="btn btn-sm fade-half back-to-top inner-link" href="#top"><i class="fa fa-angle-up"></i></a>
</footer><!-- #colophon -->
</div>
</div><!-- #page -->

<?php wp_footer(); ?>

</body>
</html>
