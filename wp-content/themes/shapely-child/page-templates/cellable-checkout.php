<?php
/*
Template Name: Cellable Checkout
Template Post Type: page
*/
require_once(ABSPATH . 'wp-content/plugins/cellable/cellable_global.php');

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

			$payment_types =  $wpdb->get_results("SELECT * FROM ". $wpdb->base_prefix."cellable_payment_types", ARRAY_A);
			
			$phone_version = $wpdb->get_row("SELECT * FROM ". $wpdb->base_prefix."cellable_phone_versions WHERE id=" . $phone_version_id, ARRAY_A);
			$capacity = $wpdb->get_row("SELECT * FROM ". $wpdb->base_prefix."cellable_storage_capacities WHERE id=" . $capacity_id, ARRAY_A);
			$phone_version_capacity = $wpdb->get_row("SELECT * FROM ". $wpdb->base_prefix."cellable_version_capacities 
				WHERE phone_version_id=" . $phone_version_id." and storage_capacity_id =" . $capacity_id, ARRAY_A);
			
			if (!$phone_version || !$carrier_id || !$capacity || !$phone_version_capacity || !$defect_ids || !is_array($defect_ids)) {
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
			$promo = null;

			if ($promo_code) {
				$promo = $wpdb->get_row($wpdb->prepare("SELECT * FROM ". $wpdb->base_prefix."cellable_promos WHERE code= %s
					and start_date <= CURDATE() and end_date >= CURDATE()", $wpdb->esc_like($promo_code)), ARRAY_A);
			}

			if ($promo && $promo['discount']>0):
				$price += $price * $promo['discount'] / 100;	
			elseif ($promo && $promo['dollar_value']>0):
				$price += $promo['dollar_value'];
			endif;
			
			$phone_brand = $wpdb->get_row("SELECT * FROM ". $wpdb->base_prefix."cellable_phones WHERE id=" . $phone_version['phone_id'], ARRAY_A);
			$capacities = $wpdb->get_results("SELECT * FROM ". $wpdb->base_prefix."cellable_storage_capacities", ARRAY_A);
			$phone_version_capacities = $wpdb->get_results($wpdb->prepare("SELECT * FROM ". $wpdb->base_prefix."cellable_version_capacities 
				where phone_version_id = %d", $phone_version['id']), ARRAY_A);

			?>			

			<table style="width:100%; margin-left:auto; margin-right:auto;">
				<tr>
					<td class="text-left v-top">
						<form action="<?=get_edit_profile_url() ?>" method="post">
							<input type="submit" value="Update"/>
						</form>
					</td>
					<td class="v-middle">
						<dl class=""> <!--dl-horizontal -->
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
					<td class="v-middle">
						<dl class=""> <!--dl-horizontal -->
                            <dt>Address</dt>
                            <dd>
								<?= get_the_author_meta( 'address1', $user->ID ) ?> <br/>
								<?= get_the_author_meta( 'address2', $user->ID ) ?> <br/>
								<?= get_the_author_meta( 'city', $user->ID ) ?>, <?= get_the_author_meta( 'state', $user->ID ) ?> <?= get_the_author_meta( 'zip', $user->ID ) ?>
                            </dd>
                            <dt>Phone Number</dt>
                            <dd>
								<?= get_the_author_meta('phone_number', $user->ID) ?>
                            </dd>                          
                        </dl>
					</td>
					<td class="v-middle">
                        <dl class=""> <!--dl-horizontal -->
                            <dt>Created On</dt>
                            <dd>
								<?= (new DateTime($user->user_registered))->format('m/d/Y h:i:s A') ?>
                            </dd>
                            <dt>Last Login</dt>
                            <dd>
								<?php
									$last_login_date = get_the_author_meta('last_login', $user->ID);
									$str = "";
									if ($last_login_date) {
										$str = (new DateTime($last_login_date))->format('m/d/Y h:i:s A'); 
									}
									echo $str;
								?>
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
                    <td class="v-top" style="border-right:solid; border-right-color:lightgrey; border-right-width:1px;">
						<form action="<?=get_home_url() ?>/user-register" method="post">
							<input name="userEmail" type="hidden" value="<?= $user->user_email ?>">
							<input name="UserExists" type="hidden" value="True">
							<input name="phone_version_id" type="hidden" value="<?= $phone_version_id ?>">
							<input name="carrier_id" type="hidden" value="<?= $carrier_id ?>">
							<input name="capacity_id" type="hidden" value="<?= $capacity_id ?>">							
							<?php foreach ($defect_ids as $defect_id): ?>
								<input name="defect_ids[]" type="hidden" value="<?=$defect_id?>"/>
							<?php endforeach; ?>
                            <table>
                                <tr>
                                    <td class="text-left" style="width:100%; padding:10px;">
                                        <i class="text-danger">*</i>&nbsp;Payment Method:
                                        <br/>
										<select class="form-control" name="payment_type_id" onchange="validate_form()" required>
											<option value="">-- How You Get Paid --</option>
											<?php foreach ($payment_types as $ele):  ?>
											<option value="<?= $ele['id'] ?>"><?= $ele['name'] ?></option>
											<?php endforeach; ?>
										</select>
                                        <div id="PaymentValidationMessage" class="text-danger"></div>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-left"  style="width:100%; padding:10px;">
                                        <div id="PayUserName"><i class="text-danger">*</i>&nbsp;User Name / Email for Payment Method:</div>
                                        <input type="text" id="PaymentUserName" name="payment_username" class="form-control" required />
                                        <div id="PaymentUserNameValidationMessage" class="text-danger"></div>
                                        <br />
                                        <input type="submit" name="submit" id="submit" value="Complete Order" class="btn" style="width: auto;" />
                                        <input type="button" name="reset" id="reset" value="Cancel" class="btn" onclick="window.location.href='<?=get_home_url() ?>';" />
                                    </td>
                                </tr>
                            </table>
                        </form>
                    </td>
                    <td colspan="3" class="v-middle">
                        <table style="width:80%; margin-left:auto; margin-right:auto; font-family:'HP Simplified'">
                            <tr>
                                <td class="text-center v-top" style="width:30%;">                                    
									<?= $phone_brand['name'] ?>
                                    <br/>
									<?= $phone_version['name'] ?> (<?= $capacity['description'] ?>)
                                    <br/>
									<img src="<?= $PHONE_IMAGE_LOCATION ?>/<?= $phone_version['image_file'] ?>" style="height:250px; width:130px;" />
                                    <br/>
                                    Please Note: We do not pay for devices that have been reported lost or stolen.
                                </td>
                                <td class="v-top" style="width:30%;">
                                    <table style="width:100%; left:auto; right:auto;">
                                        <tr>
                                            <td class="text-center" colspan="3">
                                                <div style="font-family:'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; color: green; font-size: 55px; line-height: initial;">
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