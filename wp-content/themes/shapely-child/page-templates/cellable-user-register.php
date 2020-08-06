<?php
/*
Template Name: Cellable User Register
Template Post Type: page
*/
ob_start(); // this line is for the issue: header already sent
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
			
			$phone_version_id = $_REQUEST['phone_version_id'];
			$capacity_id = $_REQUEST['capacity_id'];
			$carrier_id = $_REQUEST['carrier_id'];
			$defect_ids = $_REQUEST['defect_ids'];

			$payment_username = $_REQUEST["payment_username"];
			$payment_type_id = $_REQUEST["payment_type_id"];
			
			$phone_version = $wpdb->get_row("SELECT * FROM ". $wpdb->base_prefix ."cellable_phone_versions WHERE id=" . $phone_version_id, ARRAY_A);
			$carrier = $wpdb->get_row("SELECT * FROM ". $wpdb->base_prefix ."cellable_carriers WHERE id=" . $carrier_id, ARRAY_A);
			$capacity = $wpdb->get_row("SELECT * FROM ". $wpdb->base_prefix ."cellable_storage_capacities WHERE id=" . $capacity_id, ARRAY_A);
			$phone_version_capacity = $wpdb->get_row("SELECT * FROM ". $wpdb->base_prefix ."cellable_version_capacities 
				WHERE phone_version_id=" . $phone_version_id." and storage_capacity_id =" . $capacity_id, ARRAY_A);
									
			if (!$phone_version || !$carrier || !$capacity || !$phone_version_capacity || !$payment_type_id || !$payment_username || !$defect_ids || !is_array($defect_ids)) {
			?>
			<p>There are some incorrect variables.</p>
			<a href="<?=get_home_url() ?>">Go To Homepage</a>
			<?php
				return;
			}
			
			$price = $phone_version_capacity['value'];
			$original_price = $price;
			$defect_ids_str = implode(', ', $defect_ids);
			
			$total_defect_value = $wpdb->get_var($wpdb->prepare("SELECT sum(cost) FROM ".$wpdb->base_prefix
				."cellable_possible_defects WHERE id in (%s)", $defect_ids_str) );
			
			$price = $price-$total_defect_value;
			
			// Promotion Code
			$promo_code = isset($_REQUEST['promo_code']) ? $_REQUEST['promo_code'] : null;
			$promo_id = null;
			$promo = null;

			if ($promo_code) {
				$promo = $wpdb->get_row($wpdb->prepare("SELECT * FROM ". $wpdb->base_prefix ."cellable_promos WHERE code= %s
					and start_date <= CURDATE() and end_date >= CURDATE()", $wpdb->esc_like($promo_code)), ARRAY_A);
				$promo_id = $promo['id'];
			}
			
			if ($promo && $promo['discount']>0):
				$price += $price * $promo['discount'] / 100;	
			elseif ($promo && $promo['dollar_value']>0):
				$price += $promo['dollar_value'];
			endif;
			
			$phone_brand = $wpdb->get_row("SELECT * FROM ". $wpdb->base_prefix ."cellable_phones WHERE id=" . $phone_version['phone_id'], ARRAY_A);
						
			$capacities = $wpdb->get_results("SELECT * FROM ". $wpdb->base_prefix ."cellable_storage_capacities", ARRAY_A);
			$phone_version_capacities = $wpdb->get_results($wpdb->prepare("SELECT * FROM ". $wpdb->base_prefix ."cellable_version_capacities 
				where phone_version_id = %d", $phone_version['id']), ARRAY_A);

			try {
				// begin DB transaction
				// $wpdb->query('START TRANSACTION');
							
				// Save Order Detail
				$r = $wpdb->insert($wpdb->base_prefix ."cellable_order_details", array(
					'phone_id' => $phone_brand['id'],
					'carrier_id' => $carrier['id'],
					'phone_version_id' => $phone_version['id'],					
				));

				if ($r == false) {
					throw new Exception('User Phone Insert Failed');
				}

				$order_detail_id = $wpdb->insert_id;
				//Create Order
				$order_id = null;
				$r = $wpdb->insert($wpdb->base_prefix ."cellable_orders", array(
					'amount' => $price,
					'user_id' => $user->ID,
					'order_status_id' => 1,
					'created_date' => date_create()->format('Y-m-d H:i:s'),
					'created_by' => 'System',
					'payment_type_id' => $payment_type_id,
					'promo_id' => $promo_id,
					'order_detail_id' => $order_detail_id,
					'payment_username' => $payment_username					
				));
				
				if ($r == false) {
					throw new Exception('Order Insert Failed');
				}
				$order_id = $wpdb->insert_id;
				
				$view_count = 0;
				if ($phone_version['views'] == null ) {
					$view_count = 1;
				}
				else {
					$view_count = 1+ $phone_version['views'];
				}
				
				$wpdb->update($wpdb->base_prefix ."cellable_phone_versions", array(            
					'views' => $phone_version['views']
				), array(
					'id' => $phone_version_id,
				));
				// $wpdb->query('COMMIT');

				// Get/Save Shipping Label
				$shipping_mail = new CellableShipping;				
				$shipping_mail->GetShippingLabel($user->ID, $order_id);
				$wpdb->query('COMMIT');

				// Send Confirmation Email(s)
				$cellable_email = new CellableEmail;
				$cellable_email->send_email($order_id, "Confirm", $user->user_email);
				// $wpdb->query('COMMIT');
				
				header("Location: ".get_home_url()."/track-orders/?new_order=true");
				exit();
			} catch (Exception $e) {
				$wpdb->query('ROLLBACK');
				error_log($e->getMessage());
				
			}

			?>			

		</div><!-- #primary -->
	</div>

<?php
get_footer();