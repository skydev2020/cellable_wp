<?php
/*
Template Name: Cellable Process Order
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
			$phone_version_carrier = $wpdb->get_row("SELECT * FROM ".$wpdb->base_prefix . "cellable_version_carriers 
				WHERE phone_version_id=" . $phone_version_id." and carrier_id =" . $carrier_id, ARRAY_A);
									
			if (!$phone_version || !$carrier || !$capacity || !$phone_version_capacity || !$phone_version_carrier || !$payment_type_id || !$payment_username || !$defect_ids || !is_array($defect_ids)) {
			?>
			<p>There are some incorrect variables.</p>
			<a href="<?=get_home_url() ?>">Go To Homepage</a>
			<?php
				return;
			}
			
			$price = $phone_version_capacity['value'];
			$price -= $phone_version_carrier['value'];
			$original_price = $price;
			$defect_ids_str = implode(', ', $defect_ids);
			
			$total_defect_value = $wpdb->get_var("SELECT sum(cost) FROM ".$wpdb->base_prefix
				."cellable_possible_defects WHERE id in (" .$defect_ids_str.")");
				
			$price = $price-$total_defect_value;

			// Get Defect Group Name along with answer

			$sql_str = "select pd.*, dg.name dg_name FROM ".$wpdb->base_prefix . "cellable_possible_defects pd ";
			$sql_str .= "left join ".$wpdb->base_prefix."cellable_defect_groups dg on pd.defect_group_id = dg.id ";
			$sql_str .= "where pd.id in (" .$defect_ids_str.")";
	
			$defects = $wpdb->get_results($sql_str, ARRAY_A);			
			
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
			
			try {
				// begin DB transaction
				$wpdb->query('START TRANSACTION');
							
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
				
				// Update Phone Version Purchases Count
				$purchase_count = 0;
				if ($phone_version['purchases'] == null ) {
					$purchase_count = 1;
				}
				else {
					$purchase_count = 1+ $phone_version['purchases'];
				}
				
				$wpdb->update($wpdb->base_prefix ."cellable_phone_versions", array(            
					'purchases' => $purchase_count
				), array(
					'id' => $phone_version_id,
				));
				

				// Create OrderQuestion and Store it to DB
				$r = $wpdb->insert($wpdb->base_prefix ."cellable_order_qas", array(
					'order_id' => $order_id,
					'question' => "Storage Capacity",
					'answer' => $capacity['description'],
					'cost' => $phone_version_capacity['value']
				));

				foreach ($defects as $defect):
					$r = $wpdb->insert($wpdb->base_prefix ."cellable_order_qas", array(
						'order_id' => $order_id,
						'question' => $defect['dg_name'],
						'answer' => $defect['name'],
						'cost' => (0.0 - $defect['cost'])
					));
				endforeach;

				// Get/Save Shipping Label
				$shipping_mail = new CellableShipping;				
				// $shipping_mail->GetShippingLabel($user->ID, $order_id);
				$shipping_mail->GetShipStationLabel($user->ID, $order_id);
				$wpdb->query('COMMIT');

				// Send Confirmation Email(s)
				$cellable_email = new CellableEmail;
				$cellable_email->send_email($order_id, "Confirm", $user->user_email);
				// $wpdb->query('COMMIT');
				
				header("Location: ".get_home_url()."/track-orders/?new_order=true");
				exit();
			} catch (Exception $e) {
				$wpdb->query('ROLLBACK');
				echo "<p style='color:red'> Error: ".$e->getMessage()."</p>";
				error_log($e->getMessage());				
			}

			?>			

		</div><!-- #primary -->
	</div>

<?php
get_footer();