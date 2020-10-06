<?php
/*
Template Name: Cellable Price Phone
Template Post Type: page
*/
require_once(ABSPATH . 'wp-content/plugins/cellable/cellable_global.php');
get_header(); 
?>

<?php $layout_class = shapely_get_layout_class(); ?>
<h1>We've Calculated Your Phone's Value</h1>
	<div class="row">
		<div id="primary" class="col-md-12 mb-xs-24">
			<?php
			while ( have_posts() ) : the_post();				
				the_content();
			endwhile; // End of the loop.

			$phone_version_id = isset($_REQUEST['phone_version_id']) ? $_REQUEST['phone_version_id'] : null;
			$capacity_id = isset($_REQUEST['capacity_id']) ? $_REQUEST['capacity_id'] : null;
			$defect_ids = isset($_REQUEST['defect_ids']) ? $_REQUEST['defect_ids'] : null;
			$carrier_id = isset($_REQUEST['carrier_id']) ? $_REQUEST['carrier_id'] : null;
			$promo_code = isset($_REQUEST['promo_code']) ? $_REQUEST['promo_code'] : null;

			$phone_version = $wpdb->get_row($wpdb->prepare("SELECT * FROM ". $wpdb->base_prefix."cellable_phone_versions WHERE id= %d", 
				$phone_version_id), ARRAY_A);
			$capacity = $wpdb->get_row($wpdb->prepare("SELECT * FROM ". $wpdb->base_prefix."cellable_storage_capacities WHERE id= %d", 
				$capacity_id), ARRAY_A);
			$phone_version_capacity = $wpdb->get_row($wpdb->prepare("SELECT * FROM ". $wpdb->base_prefix."cellable_version_capacities 
				WHERE phone_version_id= %d and storage_capacity_id =%d", $phone_version_id, $capacity_id), ARRAY_A);
			$phone_version_carrier = $wpdb->get_row($wpdb->prepare("SELECT * FROM ". $wpdb->base_prefix."cellable_version_carriers 
				WHERE phone_version_id= %d and carrier_id =%d", $phone_version_id, $carrier_id), ARRAY_A);

			if (!$phone_version || !$carrier_id || !$capacity || !$phone_version_capacity || !$phone_version_carrier || !$defect_ids || !is_array($defect_ids)) {
			?>

			<p>There are some incorrect variables.</p>
			<a href="<?=get_home_url() ?>">Go To Homepage</a>
			<?php
				return;
			}

			$price = $phone_version_capacity['value'];
			// It should include code: Carrier baseCost -= decimal.Parse(versionCarrier.Value.ToString());

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
					
			$phone_brand = $wpdb->get_row("SELECT * FROM ". $wpdb->base_prefix ."cellable_phones WHERE id=" . $phone_version['phone_id'], ARRAY_A);
			?>

			<div class="col-md-12 mb-xs-24">
				<div class="col-md-3 col-xs-12">
					<div class="text-center">
						<img src="<?= $phone_version['image_file'] ?>" style="max-width: 250px;" class="text-center" />
					</div>
					<p class="fine_print"><strong>Please Note:</strong> We do not pay for devices that have been reported lost or stolen.</p>
					<p class="fine_print"><strong><?= get_cellable_setting('DefectsFooter') ?></strong></p>
				</div>
				<div class="col-sm-2 col-xs-12"></div>
				<div class="col-sm-7 col-xs-12" id="defect_questions">
					<h3 class="big_header"><?= $phone_brand['name'] ?> <?= $phone_version['name'] ?> (<?= $capacity['description'] ?>)</h3>
					<h2 id="final_price">
						$<?= number_format((float)$price, 2, '.', '')  ?>
					</h2>

					<!--PROMO CODE -->
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
								<input name="capacity_id" type="hidden" value="<?=$capacity_id?>"/>
								<input name="carrier_id" type="hidden" value="<?=$carrier_id?>"/>
								<input id="PromoCode" name="promo_code" type="text" placeholder="Promo Code?" autofocus />

								<?php foreach ($defect_ids as $defect_id): ?>
								<input name="defect_ids[]" type="hidden" value="<?=$defect_id?>"/>
								<?php endforeach; ?>

								<button type="submit" name="submit" id="promo_code" class="PromoCode">
									<i class="fa fa-caret-right"></i>
								</button>
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
								<input type="submit" value="Sell Your Phone" class="btn sell_phone" />
							</p>
						</form>
					<?php else:?>
						<div class='text-danger'>Unfortunately, we cannot purchase your phone.</div>
					<?php endif;?>

					<!--SUMMARY -->
					<h2 id="summary_header">Summary</h2>
					<p><strong>Phone's Base Value:</strong> $<?= number_format((float)$original_price, 2, '.', '')?></p>
					<p><strong>Storage Capacity:</strong> <?= $capacity['description'] ?></p>
					
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
					<p><strong><?= $defect_group['name'] ?></strong> <?= $defect['name'] ?></p>
					<?php endforeach; ?>

					<?php if ($promo):?>		
						<h3>Promo Code Applied:</h3>								
						<?php if ($promo['discount']>0):?>
							<?= $promo['discount'] ?>%
						<?php else:?>
							$<?= $promo['dollar_value'] ?>
						<?php endif;?>
					<?php endif; ?>                            

					<!--<p><strong>Phone Value:</strong> $<?= number_format((float)$price, 2, '.', '')?></p>-->
				</div>				
			</div><!-- #content -->		
		</div><!-- #primary -->
	</div>
<?php
get_footer();