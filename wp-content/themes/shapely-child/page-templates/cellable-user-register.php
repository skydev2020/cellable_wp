<?php
/*
Template Name: Cellable User Register
Template Post Type: page
*/
require_once(ABSPATH . 'wp-content/themes/shapely-child/cellable_shipping.class.php');

$user = wp_get_current_user(); // ID->0: if user is not logged in
if ($user->ID==0):
	header("Location: ".get_home_url()."/wp-login.php?action=register");
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
			
			$phone_version_id = $_GET['phone_version_id'];
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
			$total_defect_value = $wpdb->get_var($wpdb->prepare("SELECT sum(cost) FROM ". $wpdb->base_prefix ."cellable_possible_defects WHERE id in ($defect_ids_str)") );
			
			$price = $price-$total_defect_value;
			
			// Promotion Code
			$promo_code = $_REQUEST['promo_code'];
			$promo_id = null;
			$promo = null;

			if (isset($promo_code)) {
				$promo = $wpdb->get_row($wpdb->prepare("SELECT * FROM ". $wpdb->base_prefix ."cellable_promos WHERE code= %s
					and start_date <= CURDATE() and end_date >= CURDATE()", $wpdb->esc_like($promo_code)), ARRAY_A);
				$promo_id = $promo['id'];
			}

			if ($promo['discount']>0):
				$price += $price * $promo['discount'] / 100;	
			else:
				$price += $promo['dollar_value'];
			endif;

			
			$possible_defect_groups = $wpdb->get_results($wpdb->prepare("SELECT distinct(defect_group_id) id FROM ". $wpdb->base_prefix ."cellable_possible_defects 
				where phone_version_id = %d order by defect_group_id asc", 
				$wpdb->esc_like($phone_version_id)), ARRAY_A);
			
			$phone_brand = $wpdb->get_row("SELECT * FROM ". $wpdb->base_prefix ."cellable_phones WHERE id=" . $phone_version['phone_id'], ARRAY_A);
						

			$capacities = $wpdb->get_results($wpdb->prepare("SELECT * FROM ". $wpdb->base_prefix ."cellable_storage_capacities"), ARRAY_A);
			$phone_version_capacities = $wpdb->get_results($wpdb->prepare("SELECT * FROM ". $wpdb->base_prefix ."cellable_version_capacities 
				where phone_version_id = %d", $phone_version['id']), ARRAY_A);

			try {
				// begin DB transaction
				$wpdb->query('START TRANSACTION');
							
				// Save User Phone
				$r = $wpdb->insert($wpdb->base_prefix ."cellable_order_details", array(
					'user_id' => $user->ID,
					'phone_id' => $phone_brand['id'],
					'carrier_id' => $carrier['id'],
					'phone_version_id' => $phone_version['id'],
					'created_date' => date()
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
					'created_date' => date(),
					'created_by' => 'System',
					'payment_type_id' => $payment_type_id,
					'promo_id' => $promo_id,
					'order_detail_id' => $order_detail_id,
					'payment_user_name' => $payment_username					
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
				$wpdb->query('COMMIT');

				// Get/Save Shipping Label
				$shipping_mail = new CellableShipping;				
				$shipping_mail->GetShippingLabel($user->ID, $order_id);

				// Send Confirmation Email(s)
				$cellable_email = new CellableEmail;
				$cellable_email->send_email($order_id, "Confirm", $user->user_email);
			} catch (Exception $e) {
				error_log($e->getMessage());
			}

			?>			

			<table style="width:100%; margin-left:auto; margin-right:auto;">
				<tr>
					<td class="text-center" style="vertical-align:top;">
						<form action="<?=get_home_url() ?>/update-returning-user" method="post">
							<input type="submit" value="Update" class="PromoCode" />
						</form>
					</td>
					<td style="vertical-align:top;">
						<dl class="dl-horizontal">
                            <dt>Name</dt>
                            <dd>
								<?= $user->first_name ?> <?= $user->last_name ?>
                            </dd>
                            <dt>User Name</dt>
                            <dd>
								<?= $user->user_login ?>
                            </dd>
                            <dd>
								<a href="mailto:<?= $user->user_email ?>"><?= $user->user_email ?></a>
                            </dd>
                        </dl>
					</td>
					<td style="vertical-align:top;">
						<dl class="dl-horizontal">
                            <dt>Address</dt>
                            <dd>
								4796 township farm trail
								marietta, State 30066
								<!-- @Html.DisplayFor(model => model.Address)
                                @if (Model.Address2 != null)
                                {
                                    @Html.Raw("<br />")@Html.DisplayFor(model => model.Address2)
                                }
                                <br />
                                @Html.DisplayFor(model => model.City), @Html.DisplayNameFor(model => model.State) @Html.DisplayFor(model => model.Zip) -->
                            </dd>
                            <dt>Phone Number</dt>
                            <dd>
								404-405-1210<!-- @Html.DisplayFor(model => model.PhoneNumber) -->
                            </dd>                          
                        </dl>
					</td>
					<td style="vertical-align:middle;">
                        <dl class="dl-horizontal">
                            <dt>Created On</dt>
                            <dd>
								<?= $user->user_registered ?>
                            </dd>
                            <dt>Last Login</dt>
                            <dd>
								Last Login Date
                            </dd>
                        </dl>
                    </td>
				</tr>
				<tr>
                    <td colspan="4">
                        <h4>Order Details</h4>
                        <hr />
                    </td>
                </tr>
				<tr>
                    <td style="vertical-align:top; border-right:solid; border-right-color:lightgrey; border-right-width:1px;">
						<form action="<?=get_home_url() ?>/user-register" method="post">
							<input name="userEmail" type="hidden" value="<?= $user->user_email ?>">
							<input name="UserExists" type="hidden" value="True">
                            <table>
                                <tr>
                                    <td class="text-left" style="width:100%; padding:10px;">
                                        <i class="text-danger">*</i>&nbsp;Payment Method:
                                        <br/>
										<select class="form-control" name="payment_types" onchange="validate_form()">
											<option value="">-- How You Get Paid --</option>
											<?php foreach ($payment_types as $ele):  ?>
											<option value="<?= $ele['id'] ?>"><?= $ele['type'] ?></option>
											<?php endforeach; ?>
										</select>
                                        <div id="PaymentValidationMessage" class="text-danger"></div>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-left"  style="width:100%; padding:10px;">
                                        <div id="PayUserName" name="PayUserName"><i class="text-danger">*</i>&nbsp;User Name / Email for Payment Method:</div>
                                        <input type="text" id="PaymentUserName" name="PaymentUserName" class="form-control" onchange="validate_form()" />
                                        <div id="PaymentUserNameValidationMessage" class="text-danger"></div>
                                        <br />
                                        <input type="submit" name="submit" id="submit" value="Complete Order" class="PromoCode" onclick="return validate_form()" />
                                        <input type="button" name="reset" id="reset" value="Cancel" class="PromoCode" onclick="window.location.href='<?=get_home_url() ?>/user_delete';" />
                                    </td>
                                </tr>
                            </table>
                        </form>
                    </td>
                    <td colspan="3" style="vertical-align:top;">
                        <table style="width:80%; margin-left:auto; margin-right:auto; font-family:'HP Simplified'">
                            <tr>
                                <td class="text-center" style="width:30%; vertical-align:top;">                                    
									<?= $phone_brand['name'] ?>
                                    <br/>
									<?= $phone_version['name'] ?> (<?= $capacity['description'] ?>)
                                    <br/>
									<img src="<?= $PHONE_IMAGE_LOCATION ?>/<?= $phone_version['image_file'] ?>" style="height:250px; width:130px;" />
                                    <br/>
                                    Please Note: We do not pay for devices that have been reported lost or stolen.
                                </td>
                                <td style="width:30%; vertical-align:top;">
                                    <table style="width:100%; left:auto; right:auto;">
                                        <tr>
                                            <td class="text-center" colspan="3">
                                                <div style="font-family:'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; color: green; font-size: 55px;">                                                    
													$<?= number_format((float)$price, 2, '.', '')  ?>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="text-center" style="background-color:lightgrey" colspan="3">
												<strong>Your Phone Details</strong><br />
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="text-right" style="padding:3px; text-align:right; font-weight:bold">
                                                <strong>Phone's Base Value:</strong>
                                            </td>
                                            <td class="text-right" style="width:25px; color:forestgreen;"><strong>$</strong></td>
                                            <td class="text-right" style="padding:3px; color:forestgreen;">
												<strong><?= number_format((float)$original_price, 2, '.', '')?></strong>
                                            </td>
                                        </tr>
										<?php if (isset($promo)):?>
										<tr>
											<td class="text-right" style="padding:3px;">
												Promo Code Applied:
											</td>
											<td class="text-right" style="width:25px; color:forestgreen;">
												+
											</td>
											<td class="text-right" style="width:25px; color:forestgreen;">
												<?php if ($promo['discount']>0):?>
													+<?= $promo['discount'] ?>%
												<?php else: ?>
													+$<?= $promo['dollar_value'] ?>
												<?php endif;?>
											</td>
										</tr>
										<?php endif; ?>

                                        <tr>
                                            <td class="text-right" style="padding:3px; border-top:solid; border-top-color:black; border-top-width:1px">
                                                <strong>Phone Value:</strong>
                                            </td>
                                            <td class="text-right" style="width:25px; color:forestgreen; border-top:solid; border-top-color:black; border-top-width:1px">
												<strong>$</strong>
											</td>
                                            <td class="text-right" style="padding:3px; color:forestgreen; border-top:solid; border-top-color:black; border-top-width:1px">
                                                <strong><?= number_format((float)$price, 2, '.', '')  ?></strong>
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
			</table>
		</div><!-- #primary -->
	</div>
	<script>
		function validate_form() {
			var paymentType = document.getElementById("PaymentTypes").value;
			var paymentUserName = document.getElementById("PaymentUserName").value;
			var userNameDisplay = document.getElementById("PayUserName");

			if (paymentType != "1") {
				userNameDisplay.innerHTML = "<i class='text-danger'>*</i>&nbsp;Email Address for Payment Method:";
			}
			else {
				userNameDisplay.innerHTML = "<i class='text-danger'>*</i>&nbsp;User Name / Email for Payment Method:";
			}

			// Custom "Forced" Validation For Password
			if (paymentType == "") {
				document.getElementById("PaymentValidationMessage").innerHTML = "Payment Type is required";
				valid = false;
			}
			else {
				document.getElementById("PaymentValidationMessage").innerHTML = "";
				valid = true;
			}

			// Custom "Forced" Validation For Confirm Password
			if (paymentUserName == "") {
				document.getElementById("PaymentUserNameValidationMessage").innerHTML = "Payment User Name is required";
				valid = false;
			}
			else {
				document.getElementById("PaymentUserNameValidationMessage").innerHTML = "";
				valid = true;
			}

			return valid;
		}
	</script>
<?php
get_footer();