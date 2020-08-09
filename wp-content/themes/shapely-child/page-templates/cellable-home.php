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
		$phones = $wpdb->get_results("SELECT * FROM ". $wpdb->base_prefix."cellable_phones", ARRAY_A);
		$testimonials = $wpdb->get_results("SELECT * FROM ". $wpdb->base_prefix."cellable_testimonials where published=1", ARRAY_A);
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
			<br/><br/><br/><?= get_cellable_setting('AboutBody') ?><br/><br/><br/>
		</div>
		<div class="text-center full-width inline-block">
			<div class="slideshow-container">
			<?php foreach ($testimonials as $testimonial): ?>
				<div class="mySlides">
				<?php for ($i=1; $i<$testimonial['rating']; $i++): ?>
					<span class='fa fa-star checked'></span>
				<?php endfor; ?>
				<i><?= $testimonial['comment'] ?></i>
				</div>
				<a class="prev" onclick="plusSlides(-1)">❮</a>
				<a class="next" onclick="plusSlides(1)">❯</a>
			<?php endforeach; ?>
			</div>
		</div>
		<div class="text-center" style="padding-top: 5px; <?= (count($testimonials) == 0) ? 'display: none;' : '' ?>">
			<div class="dot-container">
			<?php for ($i=0; $i<count($testimonials); $i++): ?>
				<span class='dot' onclick='currentSlide(<?= $i+1 ?>)'></span>
			<?php endfor; ?>
            </div>
		</div>
		<div class="text-center">
			<br/><br/><br/><?= get_cellable_setting('FrontPageFooter') ?>
		</div>
	</div><!-- #primary -->
</div>
<script>
    var slideIndex = 1;
    showSlides(slideIndex);

    function plusSlides(n) {
        showSlides(slideIndex += n);
    }

    function currentSlide(n) {
        showSlides(slideIndex = n);
    }

    function showSlides(n) {
        var i;
        var slides = document.getElementsByClassName("mySlides");
        var dots = document.getElementsByClassName("dot");
        if (n > slides.length) { slideIndex = 1 }
        if (n < 1) { slideIndex = slides.length }
        for (i = 0; i < slides.length; i++) {
            slides[i].style.display = "none";
        }
        
		for (i = 0; i < dots.length; i++) {
            dots[i].className = dots[i].className.replace(" active", "");
        }

		if (slides.length > 0) {
			slides[slideIndex - 1].style.display = "block";
        	dots[slideIndex - 1].className += " active";
		}
        
    }
</script>
<?php
get_footer();