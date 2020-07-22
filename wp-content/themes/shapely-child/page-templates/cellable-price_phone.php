<?php
/*
Template Name: Cellable Price Phone
Template Post Type: page
*/
require_once(ABSPATH . 'wp-content/themes/shapely-child/cellable_global.php');

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
			$defect_ids = $_REQUEST['defect_ids'];

			$phone_version = $wpdb->get_row("SELECT * FROM wp_cellable_phone_versions WHERE id=" . $phone_version_id, ARRAY_A);
			$capacity = $wpdb->get_row("SELECT * FROM wp_cellable_storage_capacities WHERE id=" . $capacity_id, ARRAY_A);
			$phone_version_capacity = $wpdb->get_row("SELECT * FROM wp_cellable_version_capacities 
				WHERE phone_version_id=" . $phone_version_id." and storage_capacity_id =" . $capacity_id, ARRAY_A);
			
			if (!$phone_version || !$capacity || !$phone_version_capacity || !$defect_ids || !is_array($defect_ids)) {
			?>
			<p>There are some incorrect variables.</p>
			<a href="<?=get_home_url() ?>">Go To Homepage</a>
			<?php
				return;
			}

			$price = $phone_version_capacity['value'];
			$original_price = $price;
			$defect_ids_str = implode(', ', $defect_ids);
			$total_defect_value = $wpdb->get_var($wpdb->prepare("SELECT sum(cost) FROM wp_cellable_possible_defects WHERE id in ($defect_ids_str)") );
			
			$price = $price-$total_defect_value;
			// Promotion Code
			$promo_code = $_REQUEST['promo_code'];
			$promo = null;

			if (isset($promo_code)) {
				$promo = $wpdb->get_row("SELECT * FROM wp_cellable_promoes WHERE code=" . $promo_code, ARRAY_A);
			}
			
			$possible_defect_groups = $wpdb->get_results($wpdb->prepare("SELECT distinct(defect_group_id) id FROM wp_cellable_possible_defects 
				where phone_version_id = %d order by defect_group_id asc", 
				$wpdb->esc_like($phone_version_id)), ARRAY_A);
			
			$phone_brand = $wpdb->get_row("SELECT * FROM wp_cellable_phones WHERE id=" . $phone_version['phone_id'], ARRAY_A);
						

			$capacities = $wpdb->get_results($wpdb->prepare("SELECT * FROM wp_cellable_storage_capacities"), ARRAY_A);
			$phone_version_capacities = $wpdb->get_results($wpdb->prepare("SELECT * FROM wp_cellable_version_capacities 
				where phone_version_id = %d", $phone_version['id']), ARRAY_A);


			?>
			<form action="<?=get_home_url() ?>/pricephone" method="post">
			    <input id="id" name="id" type="hidden" value="<?= $phone_version_id?>" />
				<table style="width:80%; margin-left:auto; margin-right:auto; font-family:'HP Simplified'">
					<tr>
						<td class="text-center" style="vertical-align:top; width:30%;">
							<div style="height:100px;"></div>
							<?= $phone_brand['name'] ?>
							<br/> 
							<?= $phone_version['name'] ?> (<?= $capacity['description'] ?>)
                        	<br/>
							<img src="<?= $PHONE_IMAGE_LOCATION ?>/<?= $phone_version['image_file'] ?>" style="height:250px; width:130px;" />
							<br/>
							Please Note: We do not pay for devices that have been reported lost or stolen.
						</td>
						<td class="text-center" style="width:40%;">
							<div style="font-family:'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; color: green; font-size: 55px;">
								$<?= number_format((float)$price, 2, '.', '')  ?>
							</div>
							<p>&nbsp;</p>

							<?php if ($price>0):?>
								<?php if (isset($promo)):?>
									Promo Code Applied<br/>
									Promo Code: <?= $promo['code'] ?><br/>
									<?php if ($promo['discount']>0):?>
										+<?= $promo['discount'] ?>%
									<?php else:?>
										+$<?= $promo['dollar_value'] ?>
									<?php endif;?>
								<?php else:?>
									<form action="<?=get_home_url() ?>/apply-promo/?phone_version_id=<?= $phone_version_id ?>" method="post">
										Do you have a Promo Code?<br/>
										<input id="id" name="id" type="hidden" value="<?=$phone_version['id']?>"/>
										<input id="PromoCode" name="PromoCode" type="text" placeholder="Enter Promo Code" autofocus />
										<button type="submit" name="submit" id="PromoCode" class="PromoCode" value="reset">
											<i class="fa fa-plus-square"></i>
										</button>
										<p></p>
									</form>
									<form action="<?=get_home_url() ?>/complete-user-phone-registration" method="post">
										<p>
											<input type="submit" value="Sell My Phone" class="button" onclick="return valid_form()" />
											<input type="button" value="Cancel" class="button" onclick="location.href='<?=get_home_url() ?>/complete-user-phone-registration';return false;" />
										</p>
									</form>
								<?php endif;?>
							<?php else:?>
								<div class='text-danger'>Unfortunately, we cannot purchase your phone.</div>
							<?php endif;?>
						</td>
						<td style="width: 30%;">
							<table style="width:100%; margin-left:auto; margin-right:auto;">
								<tr>
									<td class="text-center" style="background-color:lightgrey" colspan="3">
										<b>Your Phone Details</b><br />
									</td>
								</tr>
								<tr>
									<td class = "text-right" style="padding:3px;">
										<strong>Phone's Base Value:</strong>
									</td>
									<td class="text-right" style="width:25px; color:forestgreen;">
										<strong>$</strong>
									</td>
									<td class="text-right" style="padding:3px; color:forestgreen;">
										<strong><?= number_format((float)$original_price, 2, '.', '')?></strong>
									</td>
								</tr>
								<tr>
									<td colspan="2" style="padding:3px;">Storage Capacity</td>
									<td class="text-right">
										<?= $capacity['description'] ?>
									</td>
								</tr>
								<tr>
									
								</tr>
								<?php 
								foreach ($defect_ids as $defect_id): 
									$defect = $wpdb->get_row("SELECT * FROM wp_cellable_possible_defects WHERE id=" . $defect_id, ARRAY_A);
									if (!$defect) {
										continue;
									}	
									$defect_group = $wpdb->get_row("SELECT * FROM wp_cellable_defect_groups WHERE id=" . $defect['defect_group_id'], ARRAY_A);
									if (!$defect_group) {
										continue;
									}
								?>
								
								<tr>
									<td colspan='2' style='padding:3px;'>
										<?= $defect_group['name'] ?>
									</td>
									<td class='text-right'>
										<?= $defect['name'] ?>
									</td>
								</tr>
								<?php endforeach; ?>
							
								<?php if ($promo):?>
								<tr>
									<td class="text-right" style='padding:3px;'>Promo Code Applied:</td>
									<td class="text-right" style='width:25px; color:forestgreen;'>+</td>
									<td class="text-right" style='width:25px; color:forestgreen;'>
									<?php if ($promo['discount']>0):?>
										<?= $promo['discount'] ?>%
									<?php else:?>
										$<?= $promo['dollar_value'] ?>
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
										<strong><?= number_format((float)$price, 2, '.', '')?></strong>
									</td>
								</tr>
							</table>
						</td>
					</tr>
				</table>
				<table style="width:70%; margin-left:auto; margin-right:auto;">
					<tr>
						<td style="text-align:center; vertical-align:middle; height:150px;">							
							Your phone will be professionally inspected at our facilities.      
						</td>
					</tr>
				</table>
			</form>
		</div><!-- #primary -->
	</div>
<?php
get_footer();