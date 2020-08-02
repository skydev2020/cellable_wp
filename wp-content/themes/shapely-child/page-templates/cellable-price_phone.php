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
			$carrier_id = $_REQUEST['carrier_id'];

			$phone_version = $wpdb->get_row("SELECT * FROM ".$wpdb->base_prefix. "cellable_phone_versions WHERE id=" . $phone_version_id, ARRAY_A);
			$capacity = $wpdb->get_row("SELECT * FROM ". $wpdb->base_prefix. "cellable_storage_capacities WHERE id=" . $capacity_id, ARRAY_A);
			$phone_version_capacity = $wpdb->get_row("SELECT * FROM ".$wpdb->base_prefix . "cellable_version_capacities 
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
						
			$phone_brand = $wpdb->get_row("SELECT * FROM ". $wpdb->base_prefix ."cellable_phones WHERE id=" . $phone_version['phone_id'], ARRAY_A);
			?>
			
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
								<?php else: ?>
									+$<?= $promo['dollar_value'] ?>
								<?php endif;?>
							<?php else:?>
								<form action="<?=get_home_url() ?>/price-phone/?phone_version_id=<?= $phone_version_id ?>" method="post">
									Do you have a Promo Code?<br/>
									<input name="capacity_id" type="hidden" value="<?=$capacity_id?>"/>
									<input name="carrier_id" type="hidden" value="<?=$carrier_id?>"/>
									<input id="PromoCode" name="promo_code" type="text" placeholder="Enter Promo Code" style="width:80%;" autofocus />

									<?php foreach ($defect_ids as $defect_id): ?>
									<input name="defect_ids[]" type="hidden" value="<?=$defect_id?>"/>
									<?php endforeach; ?>
									<button type="submit" name="submit" id="PromoCode" class="PromoCode" style="width: 30px;">
										<i class="fa fa-plus-square"></i>
									</button>
									<p></p>
								</form>
							<?php endif;?>
							<form action="<?=get_home_url() ?>/checkout/?phone_version_id=<?= $phone_version_id ?>" method="post">
								<input name="capacity_id" type="hidden" value="<?=$capacity_id?>"/>
								<input name="promo_code" type="hidden" value="<?=$promo_code?>" />
								<input name="carrier_id" type="hidden" value="<?=$carrier_id?>"/>

								<?php foreach ($defect_ids as $defect_id): ?>
								<input name="defect_ids[]" type="hidden" value="<?=$defect_id?>"/>
								<?php endforeach; ?>
								<p>
									<input type="submit" value="Sell My Phone" class="btn" />
									<input type="button" value="Cancel" class="btn" onclick="location.href='<?=get_home_url() ?>';" />
								</p>
							</form>
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
								$defect = $wpdb->get_row("SELECT * FROM ".$wpdb->base_prefix. "cellable_possible_defects WHERE id=" . $defect_id, ARRAY_A);
								if (!$defect) {
									continue;
								}	
								$defect_group = $wpdb->get_row("SELECT * FROM ". $wpdb->base_prefix. "cellable_defect_groups WHERE id=" . $defect['defect_group_id'], ARRAY_A);
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
		</div><!-- #primary -->
	</div>
<?php
get_footer();