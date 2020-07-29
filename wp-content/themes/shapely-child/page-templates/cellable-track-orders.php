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
			<div id="myModal" class="modal fade">
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
									<div id="1" name="1" style="display:inline-block;" onclick="CheckStar(this)"><span class="fa fa-star"></span></div>
									<div id="2" name="2" style="display:inline-block;" onclick="CheckStar(this)"><span class="fa fa-star"></span></div>
									<div id="3" name="3" style="display:inline-block;" onclick="CheckStar(this)"><span class="fa fa-star"></span></div>
									<div id="4" name="4" style="display:inline-block;" onclick="CheckStar(this)"><span class="fa fa-star"></span></div>
									<div id="5" name="5" style="display:inline-block;" onclick="CheckStar(this)"><span class="fa fa-star"></span></div>
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
					
					// "SELECT * FROM ". $wpdb->base_prefix ."cellable_promos WHERE id=" . $order['promo_id'], ARRAY_A);

					$phone = null;

					if ($order_detail) {
						$phone = $wpdb->get_row("SELECT * FROM ". $wpdb->base_prefix ."cellable_phones WHERE id=" . $order_detail['phone_id'], ARRAY_A);
					}
					
					
					// $orders = $wpdb->get_results("SELECT o.id oid, o.amount amount, o.payment_username payment_username,
			// 	o.usps_tracking_id tracking_number, o.mailing_label mailing_label, o.created_date created_date,
			// 	od.id odid, od.phone_id phone_id, od.carrier_id carrier_id, od.phone_version_id phone_version_id,
			// 	pt.name payment_type_name, ph.name phone_name, os.name status_name, pr.code promo_code   
			// 	FROM `wp_cellable_orders` o, `wp_cellable_order_details` od , `wp_cellable_payment_types` pt,
			// 		`wp_cellable_phones` ph, `wp_cellable_order_statuses` os, `wp_cellable_promos` pr 
			// 	where o.user_id=1 and o.order_detail_id=od.id and o.payment_type_id=pt.id  and od.phone_id = ph.id
			// 		and o.order_status_id = os.id and o.promo_id = pr.id
			// 	order by o.id  DESC", ARRAY_A);

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
						<?= isset($promo) ? $promo['code'] : "" ?>
					</td>
					<td>
						<?= isset($promo) ? $promo['name'] : "" ?>
					</td>
					<td>
		---
					</td>
					<td>
						<?= $order['payment_type_name'] ?>
					</td>
					<td>
						<?= $order['payment_username'] ?>
					</td>
					<td>
						<?php if ($order['mailing_label']): ?>
							<div onclick="popupLabelWindow('@item.MailLabel', window, 800, 600)" style="color:blue; cursor:pointer;">Print Label</div>
						<?php endif; ?>
					</td>
					<td>
						<div onclick="popupTrackingWindow('<?= $order['tracking_number'] ?>', window, 400, 400)" style="color:blue; cursor:pointer;"><?= $order['tracking_number'] ?></div>
					</td>
					<td>
						<?= $order['created_date'] ?>
					</td>
				</tr>
				<?php endforeach; ?>
			</table>
						

		</div><!-- #primary -->
	</div>

<?php
get_footer();