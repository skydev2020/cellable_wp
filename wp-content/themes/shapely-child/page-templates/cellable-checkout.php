<?php
/*
Template Name: Cellable Checkout
Template Post Type: page
*/

require_once(ABSPATH . 'wp-content/plugins/cellable/cellable_global.php');
$user = wp_get_current_user(); // ID->0: if user is not logged in

if ($user->ID==0):
	/**
	 * Push the necessary variables into Session Variable
	 * These values will be reused after user successfully logins or register
	 *  
	 * */
	$url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
	
	$obj = [];
	$obj["phone_version_id"] = $_REQUEST['phone_version_id'];
	$obj["capacity_id"] = $_REQUEST['capacity_id']; 
	$obj["carrier_id"] = $_REQUEST['carrier_id'];
	$obj["defect_ids"] = $_REQUEST['defect_ids'];
	$obj["promo_code"] = isset($_REQUEST['promo_code']) ? $_REQUEST['promo_code'] : null;
	$obj["url"] = $url;

	$_SESSION['cellable_obj'] = $obj;
	
	header("Location: ".get_home_url()."/wp-login.php?action=login");
	exit();
endif;
get_header();
?>

<?php $layout_class = shapely_get_layout_class(); ?>

<h1 style="margin: 2% 0 4%;">Where Should We Send the Shipping Box?</h1>
<div class="container">
	<div class="row">
		<div id="primary" class="col-md-12 mb-xs-24">

			<?php
			while ( have_posts() ) : the_post();					
				the_content();
			endwhile; // End of the loop.

			$phone_version_id = null;
			$capacity_id = null;
			$carrier_id = null;
			$defect_ids = null;
			$promo_code = null;

			// if this variable is called back from Login or Register, pull the variable from session variable
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

				$phone_version_id = $obj['phone_version_id'];
				$capacity_id = $obj['capacity_id'];
				$carrier_id = $obj['carrier_id'];
				$defect_ids = $obj['defect_ids'];
				$promo_code = $obj['promo_code'];
			}
			else {
				$phone_version_id = isset($_REQUEST['phone_version_id']) ? $_REQUEST['phone_version_id'] : null;
				$capacity_id = isset($_REQUEST['capacity_id']) ? $_REQUEST['capacity_id'] : null;
				$carrier_id = isset($_REQUEST['carrier_id']) ? $_REQUEST['carrier_id'] : null; 
				$defect_ids = isset($_REQUEST['defect_ids']) ? $_REQUEST['defect_ids'] : null; 
				$promo_code = isset($_REQUEST['promo_code']) ? $_REQUEST['promo_code'] : null;
			}
			
			$payment_types =  $wpdb->get_results("SELECT * FROM ". $wpdb->base_prefix."cellable_payment_types", ARRAY_A);			
			$phone_version = $wpdb->get_row("SELECT * FROM ". $wpdb->base_prefix."cellable_phone_versions WHERE id=" . $phone_version_id, ARRAY_A);
			$capacity = $wpdb->get_row("SELECT * FROM ". $wpdb->base_prefix."cellable_storage_capacities WHERE id=" . $capacity_id, ARRAY_A);
			$phone_version_capacity = $wpdb->get_row("SELECT * FROM ". $wpdb->base_prefix."cellable_version_capacities 
				WHERE phone_version_id=" . $phone_version_id." and storage_capacity_id =" . $capacity_id, ARRAY_A);
			$phone_version_carrier = $wpdb->get_row("SELECT * FROM ".$wpdb->base_prefix . "cellable_version_carriers 
				WHERE phone_version_id=" . $phone_version_id." and carrier_id =" . $carrier_id, ARRAY_A);

			if (!$phone_version || !$carrier_id || !$capacity || !$phone_version_capacity || !$phone_version_carrier || !$defect_ids || !is_array($defect_ids)) {
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

			// Promotion Code			
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
			?>			

			
			<div class="container" class="col-md-12 mb-xs-24"><!--content container-->
				<div class="row">
					<div class="col-md-3 col-xs-12"> <!--Left Column-->
						<img src="<?= $phone_version['image_file'] ?>" style="max-width: 250px;" class="text-center" />
						<p class="fine_print"><strong>Please Note:</strong> We do not pay for devices that have been reported lost or stolen.</p>
						<p class="fine_print"><strong><?= get_cellable_setting('DefectsFooter') ?></strong></p>
					</div><!--End Left Column-->
					<div class="col-sm-2 col-xs-12"></div><!--gutter-->
					<div class="col-sm-7 col-xs-12" id="defect_questions"><!--Right Column-->

						
						<h3 class="big_header"><?= $phone_brand['name'] ?> <?= $phone_version['name'] ?> (<?= $capacity['description'] ?>)</h3>
					<h2 id="final_price">
						$<?= number_format((float)$price, 2, '.', '')  ?>
					</h2>
						
					<h2>Summary</h2>
						
					<div class="col-sm-6 col-xs-12">
					<p><strong><?= $user->first_name ?> <?= $user->last_name ?></strong><br>
                    User Name: <?= $user->user_login ?><br>
					Email: <a href="mailto:<?= $user->user_email ?>" style="font-weight:normal;"><?= $user->user_email ?></a></p>

					</div><div class="col-sm-6 col-xs-12">
						<p>
						<?= get_the_author_meta( 'address1', $user->ID ) ?>
						<?= get_the_author_meta( 'address2', $user->ID ) ?> <br>
						<?= get_the_author_meta( 'city', $user->ID ) ?> <?= get_the_author_meta( 'state', $user->ID ) ?> <?= get_the_author_meta( 'zip', $user->ID ) ?><br>
                        <?= get_the_author_meta('phone_number', $user->ID) ?></p>
					</div>
                      <!--  Created On
						<? // = (new DateTime($user->user_registered))->format('m/d/Y h:i:s A') ?>
                            
						Last Login
						
								<?php
									/* $last_login_date = get_the_author_meta('last_login', $user->ID);
									$str = "";
									if ($last_login_date) {
										$str = (new DateTime($last_login_date))->format('m/d/Y h:i:s A'); 
									}
									echo $str; */
								?>
                     
						##Commenting out login date information -->
						
						<!--<form action="<?=get_edit_profile_url() ?>" method="post">
							<input type="submit" value="Update"/>
						</form>
###REMOVED because it links back into the WordPress Dashboard and not to a user profile editing page
-->
						
						
											                                         
										<?php if (isset($promo)):?>

										

												Promo Code Applied:

											
												+

											

												<?php if ($promo['discount']>0):?>

													<?= $promo['discount'] ?>%

												<?php else: ?>

													$<?= $promo['dollar_value'] ?>

												<?php endif;?>

											

										<?php endif; ?>


						
						
						<h2 style="margin: 2% 0;">Choose Your Payment Method</h2>
						
						
						<form action="<?=get_home_url() ?>/process-order" method="post">

							<input name="userEmail" type="hidden" value="<?= $user->user_email ?>">
							<input name="UserExists" type="hidden" value="True">
							<input name="phone_version_id" type="hidden" value="<?= $phone_version_id ?>">
							<input name="carrier_id" type="hidden" value="<?= $carrier_id ?>">
							<input name="capacity_id" type="hidden" value="<?= $capacity_id ?>">
							<input name="promo_code" type="hidden" value="<?=$promo_code?>" />
							<?php foreach ($defect_ids as $defect_id): ?>
								<input name="defect_ids[]" type="hidden" value="<?=$defect_id?>"/>
							<?php endforeach; ?>

                          	<p><i class="text-danger">*</i>&nbsp;Payment Method:<br/>
							<select class="form-control" name="payment_type_id" required>
							<option value="">-- How You Get Paid --</option>
							<?php foreach ($payment_types as $ele):  ?>
							<option value="<?= $ele['id'] ?>"><?= $ele['name'] ?></option>
							<?php endforeach; ?>
							</select></p>

                             <p id="PaymentValidationMessage" class="text-danger"></p>

<p id="PayUserName"><i class="text-danger">*</i>&nbsp;User Name / Email for Payment Method:<br>
	<input type="text" id="PaymentUserName" name="payment_username" class="form-control" required /></p>
							
<p><div id="PaymentUserNameValidationMessage" class="text-danger"></div><br />
<input type="submit" name="submit" id="submit" value="Complete Your Order" class="btn" style="width: auto;" /></p>
</form>
					
						
				</div><!--End Right Column-->
				
			</div><!--end row-->
						
						

		</div><!-- end content container -->
			
		</div><!--end primary-->

	</div>	<!--end row-->

</div><!--end outside container-->

<?php

get_footer();