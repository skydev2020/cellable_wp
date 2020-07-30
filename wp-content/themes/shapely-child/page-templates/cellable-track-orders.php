<?php
/*
Template Name: Cellable Track Orders
Template Post Type: page
*/

require_once(ABSPATH . 'wp-content/themes/shapely-child/cellable_shipping.class.php');
require_once(ABSPATH . 'wp-content/themes/shapely-child/cellable_email.class.php');

$user = wp_get_current_user(); // ID->0: if user is not logged in
if ($user->ID==0):
	header("Location: ".get_home_url()."/wp-login.php?action=login");
	exit();
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
			
			$orders = $wpdb->get_results("SELECT * FROM ". $wpdb->base_prefix ."cellable_orders o WHERE user_id=" . $user->ID ." order by id desc", ARRAY_A);
			
			?>
			<div id="rating-modal" class="modal">
				<div class="modal-dialog">
					<div class="modal-content">
						<div class="modal-header">
							<h4 class="modal-title">How did we do?</h4>
							<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
						</div>
						<div class="modal-body">
							<p>Please take a moment to rate our service and leave a comment.</p>
							<form action="<?=get_home_url() ?>/save-testimonial" method="post">
								<div class="form-group">
									<div id="star_1" name="star_1" class="inline-block" onclick="CheckStar(this)"><span class="fa fa-star pointer"></span></div>
									<div id="star_2" name="star_2" class="inline-block" onclick="CheckStar(this)"><span class="fa fa-star pointer"></span></div>
									<div id="star_3" name="star_3" class="inline-block" onclick="CheckStar(this)"><span class="fa fa-star pointer"></span></div>
									<div id="star_4" name="star_4" class="inline-block" onclick="CheckStar(this)"><span class="fa fa-star pointer"></span></div>
									<div id="star_5" name="star_5" class="inline-block" onclick="CheckStar(this)"><span class="fa fa-star pointer"></span></div>
									<input id="stars" name="stars" type="hidden">
								</div>
								<div class="form-group">
									<input type="text" id="comment" name="comment" class="form-control" placeholder="Leave a comment">
								</div>
								<button type="submit" class="btn btn-primary">Submit</button>
							</form>
						</div>
					</div>
				</div>
			</div>

			<table class="table">
				<tr style="background-color:black; color:lawngreen">
					<th>Phone</th>
					<th>Amount</th>
					<th>Status Type</th>
					<th>Promo Code</th>
					<th>Promo Name</th>
					<th>Discount</th>
					<th>Payment Type</th>
					<th>Payment User Name</th>
					<th>Mailing Label</th>
					<th>Tracking Number</th>
					<th>Created Date</th>
				</tr>
				<?php 
				foreach ($orders as $order): 
					$order_detail = $wpdb->get_row("SELECT * FROM ". $wpdb->base_prefix ."cellable_order_details WHERE id=" . $order['id'], ARRAY_A);
					$order_status = $wpdb->get_row("SELECT * FROM ". $wpdb->base_prefix ."cellable_order_statuses WHERE id=" . $order['order_status_id'], ARRAY_A);
					$promo = $wpdb->get_row($wpdb->prepare("SELECT * FROM ". $wpdb->base_prefix ."cellable_promos WHERE id=%d", $order['promo_id']),  ARRAY_A);
					$payment_type = $wpdb->get_row($wpdb->prepare("SELECT * FROM ". $wpdb->base_prefix ."cellable_payment_types 
						WHERE id=%d", $order['payment_type_id']),  ARRAY_A);
					
					// "SELECT * FROM ". $wpdb->base_prefix ."cellable_promos WHERE id=" . $order['promo_id'], ARRAY_A);

					$phone = null;

					if ($order_detail) {
						$phone = $wpdb->get_row("SELECT * FROM ". $wpdb->base_prefix ."cellable_phones WHERE id=" . $order_detail['phone_id'], ARRAY_A);
					}
					

				?>
				<tr style="background-color:lightgrey">
					<td>
						<?= isset($phone) ? $phone['name'] : "" ?>
					</td>
					<td>
						$<?= $order['amount'] ?>
					</td>
					<td>
						<?= isset($order_status) ? $order_status['name'] : "" ?>
					</td>
					<td>
						<?= isset($promo) ? $promo['code'] : "---" ?>
					</td>
					<td>
						<?= isset($promo) ? $promo['name'] : "---" ?>
					</td>
					<td>
						<?= isset($promo) ? $promo['discount'] ."%" : "---" ?>
					</td>
					<td>
						<?= isset($payment_type) ? $payment_type['name'] : "" ?>
					</td>
					<td>
						<?= $order['payment_username'] ?>
					</td>
					<td>
						<?php if ($order['mailing_label']): ?>
							<div onclick="popupLabelWindow('<?= $order['mailing_label'] ?>', window, 800, 600)" style="color:blue; cursor:pointer;">Print Label</div>
						<?php endif; ?>
					</td>
					<td>
						<div onclick="popupTrackingWindow('<?= $order['usps_tracking_id'] ?>', window, 400, 400)" style="color:blue; cursor:pointer;">
							<?= $order['usps_tracking_id'] ?>
						</div>
					</td>
					<td>
						<?= $order['created_date'] ?>
					</td>
				</tr>
				<?php endforeach; ?>
			</table>
						

		</div><!-- #primary -->
	</div>
	
	<script type="text/javascript">
		function CheckStar(control) {
			var one = document.getElementById("star_1");
			var two = document.getElementById("star_2");
			var three = document.getElementById("star_3");
			var four = document.getElementById("star_4");
			var five = document.getElementById("star_5");
			var stars = document.getElementById("stars");

			switch (control.id) {
				case "star_1":
					one.innerHTML = "<span class='fa fa-star checked pointer'></span>";
					two.innerHTML = "<span class='fa fa-star pointer'></span>";
					three.innerHTML = "<span class='fa fa-star pointer'></span>";
					four.innerHTML = "<span class='fa fa-star pointer'></span>";
					five.innerHTML = "<span class='fa fa-star pointer'></span>";
					stars.value = "1";
					break;
				case "star_2":
					one.innerHTML = "<span class='fa fa-star checked pointer'></span>";
					two.innerHTML = "<span class='fa fa-star checked pointer'></span>";
					three.innerHTML = "<span class='fa fa-star pointer'></span>";
					four.innerHTML = "<span class='fa fa-star pointer'></span>";
					five.innerHTML = "<span class='fa fa-star pointer'></span>";
					stars.value = "2";
					break;
				case "star_3":
					one.innerHTML = "<span class='fa fa-star checked pointer'></span>";
					two.innerHTML = "<span class='fa fa-star checked pointer'></span>";
					three.innerHTML = "<span class='fa fa-star checked pointer'></span>";
					four.innerHTML = "<span class='fa fa-star pointer'></span>";
					five.innerHTML = "<span class='fa fa-star pointer'></span>";
					stars.value = "3";
					break;
				case "star_4":
					one.innerHTML = "<span class='fa fa-star checked pointer'></span>";
					two.innerHTML = "<span class='fa fa-star checked pointer'></span>";
					three.innerHTML = "<span class='fa fa-star checked pointer'></span>";
					four.innerHTML = "<span class='fa fa-star checked pointer'></span>";
					five.innerHTML = "<span class='fa fa-star pointer'></span>";
					stars.value = "4";
					break;
				case "star_5":
					one.innerHTML = "<span class='fa fa-star checked pointer'></span>";
					two.innerHTML = "<span class='fa fa-star checked pointer'></span>";
					three.innerHTML = "<span class='fa fa-star checked pointer'></span>";
					four.innerHTML = "<span class='fa fa-star checked pointer'></span>";
					five.innerHTML = "<span class='fa fa-star checked pointer'></span>";
					stars.value = "5";
					break;
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
			return win.open("/Mail/_USPSTrackingMessage?trackingNumber=" + trackingNumber, "USPS Tracking", 'toolbar=no, location=no, directories=no, status=no, menubar=no, scrollbars=no, resizable=no, copyhistory=no, width=' + w + ', height=' + h + ', top=' + y + ', left=' + x);
		}

		function popupLabelWindow(url, win, w, h) {
			const y = win.top.outerHeight / 2 + win.top.screenY - (h / 2);
			const x = win.top.outerWidth / 2 + win.top.screenX - (w / 2);
			return win.open(url, "Print Mailing Label", 'toolbar=no, location=no, directories=no, status=no, menubar=no, scrollbars=no, resizable=no, copyhistory=no, width=' + w + ', height=' + h + ', top=' + y + ', left=' + x);
		}
	</script>
<?php
get_footer();