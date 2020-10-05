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
		
	
			<?php // get_sidebar( 'footer' ); ?>
	

		<div class="row">
			<div class="col-md-3 col-xs-12 text-center">
				
				<a href="https://www.cellable.net/"><img id="footer_logo" src="https://www.cellable.net/wp-content/uploads/2020/09/cellable-footer-logo.png"></a>
				<p>
					<?php echo wp_kses_post( get_theme_mod( 'shapely_footer_copyright' ) ); ?>
				
						
					&copy; <?=date("Y");?> Cellable</p>
				
			</div>
			
			<div class="col-md-3 col-xs-12 text-center">
				<p><a href="mailto:<?= get_option('admin_email')?>" class="NavLink">Support</a></p>
                
                <p><a class="NavLink" href="<?= get_home_url() ?>/about">About Us</a></p>
                
                <p><a class="NavLink" href="<?= get_home_url() ?>/contact">Contact Us</a></p>

				<p><a class="NavLink" href="<?= get_home_url() ?>/privacy-policy/">Privacy Policy</a></p>

			</div>
			
			
			<div class="col-md-3 col-xs-12 text-center">
				<p id="address">1377 Chatley Way<br>
				  Woodstock, GA 30188</p>
				<br>
				<?php shapely_social_icons(); ?>
			</div>
				
				
		</div><!-- end row-->
	</div><!-- end container-->

	<a class="btn btn-sm fade-half back-to-top inner-link" href="#top"><i class="fa fa-angle-up"></i></a>
</footer><!-- #colophon -->
</div>
</div><!-- #page -->

<?php wp_footer(); ?>

</body>
</html>
