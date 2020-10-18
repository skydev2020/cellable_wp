<?php
/*
Template Name: Cellable Search Results
Template Post Type: page
*/

require_once(ABSPATH . 'wp-content/themes/shapely-child/cellable_shipping.class.php');
require_once(ABSPATH . 'wp-content/themes/shapely-child/cellable_email.class.php');

$user = wp_get_current_user(); // ID->0: if user is not logged in
if ($user->ID==0):
	/**
	 * Push the necessary variables into Session Variable
	 * These values will be reused after user successfully logins or register
	 *  
	 * */
	$url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
	
	$obj = [];
		
	$obj["q"] = isset($_REQUEST['q']) ? $_REQUEST['q'] : null; 	
	$obj["url"] = $url;

	$_SESSION['cellable_obj'] = $obj;
endif;

get_header(); 
?>

<?php $layout_class = shapely_get_layout_class(); ?>

<div class="row">
	<div id="primary" class="col-md-12 mb-xs-24">
		<?php
		while ( have_posts() ) : the_post();				
			the_content();
		endwhile; // End of the loop.

		$s = null;

		if (isset($_REQUEST['call_back']) && $_REQUEST['call_back'] == "1") {
			$obj = $_SESSION['cellable_obj'];
			
			if (!$obj || is_array($obj) !== true) {
				// Stored session variable is expired, go to first page.
			?>
				<p>Session is expired.Please start from homepage again.</p>
				<a href="<?=get_home_url() ?>">Go To Homepage</a>
			<?php
				return;
			}
			$s = $obj['q'];
		}
		else {
			$s = isset($_GET['q']) ? $_GET['q'] : "";
		}
		
		$phone_versions = $wpdb->get_results($wpdb->prepare("SELECT * FROM ". $wpdb->base_prefix."cellable_phone_versions WHERE status=true		
			and name like %s order by phone_id, position desc, name", "%".$wpdb->esc_like($s)."%"), ARRAY_A);
		
		?>
		<div class="text-center">
			<?php foreach ($phone_versions as $ele): ?>
			<div class="col-sm-3 text-center phone-version">
				<a class="btn btn-default" href="<?=get_home_url() ?>/carriers/?phone_id=<?=$ele['phone_id']?>&phone_version_id=<?=$ele['id']?>">
					<img src="<?= $ele['image_file'] ?>" alt="<?= $ele['name'] ?>">
				</a>
				<br />
				<?php
				$phone = $wpdb->get_row($wpdb->prepare("SELECT * FROM ". $wpdb->base_prefix."cellable_phones WHERE id=%d", 
					$ele['phone_id']));
				?>
				<?= $phone->name?>
				<br />
				<?= $ele['name']?>
			</div>
			<?php endforeach; ?>
		</div>
					

	</div><!-- #primary -->
</div>
	
<script type="text/javascript">
	function CheckStar(index) {
		
		jQuery(".fa.fa-star").removeClass("checked");
		jQuery("#rating").val(index);

		for (var i=1; i<=index; i++) {
			jQuery("#star_" + i + " .fa.fa-star").addClass("checked");
		}			
	}

	jQuery(window).on('load', function () {
		
		// setTimeout(modalShow(), 0);
		var queryString = window.location.search;
		var urlParams = new URLSearchParams(queryString);
		var newOrder = urlParams.has('new_order')

		if (newOrder) {
			jQuery('#rating-modal').modal('show');
		}
	});

	function popupTrackingWindow(trackingNumber, win, w, h) {
		const y = win.top.outerHeight / 2 + win.top.screenY - (h / 2);
		const x = win.top.outerWidth / 2 + win.top.screenX - (w / 2);
		// return win.open("/Mail/_USPSTrackingMessage?trackingNumber=" + trackingNumber, "USPS Tracking", 'toolbar=no, location=no, directories=no, status=no, menubar=no, scrollbars=no, resizable=no, copyhistory=no, width=' + w + ', height=' + h + ', top=' + y + ', left=' + x);
		return win.open("<?=get_home_url() ?>/usps-tracking-message/?tracking_number=" + trackingNumber, "USPS Tracking", 'toolbar=no, location=no, directories=no, status=no, menubar=no, scrollbars=no, resizable=no, copyhistory=no, width=' + w + ', height=' + h + ', top=' + y + ', left=' + x);
	}

	function popupLabelWindow(url, win, w, h) {
		url = "<?=get_home_url() ?>/shipstation-label-data?order_id="+orderId;
		const y = win.top.outerHeight / 2 + win.top.screenY - (h / 2);
		const x = win.top.outerWidth / 2 + win.top.screenX - (w / 2);
		return win.open(url, "Print Mailing Label", 'toolbar=no, location=no, directories=no, status=no, menubar=no, scrollbars=no, resizable=no, copyhistory=no, width=' + w + ', height=' + h + ', top=' + y + ', left=' + x);		
	}
</script>
<?php
get_footer();