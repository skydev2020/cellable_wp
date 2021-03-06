<?php

/*
Template Name: Cellable Defect Question
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
		
	$obj["phone_version_id"] = isset($_REQUEST['phone_version_id']) ? $_REQUEST['phone_version_id'] : null; 
	$obj["carrier_id"] = isset($_REQUEST['carrier_id']) ? $_REQUEST['carrier_id'] : null; 
	$obj["url"] = $url;

	$_SESSION['cellable_obj'] = $obj;
endif;

get_header(); 
?>

<?php $layout_class = shapely_get_layout_class(); ?>
<h1>What Is The Condition Of The Phone?</h1>

	<div class="row">
		<div id="primary" class="col-md-12 mb-xs-24">
			<?php
			while ( have_posts() ) : the_post();				
				the_content();
			endwhile; // End of the loop.

			$phone_version_id = null;
			$carrier_id = null;

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
				$carrier_id = $obj['carrier_id'];
				$phone_version_id = $obj['phone_version_id'];				
			}
			else {
				$phone_version_id = isset($_REQUEST['phone_version_id']) ? $_REQUEST['phone_version_id'] : null; 
				$carrier_id = isset($_REQUEST['carrier_id']) ? $_REQUEST['carrier_id'] : null; 
			}

			// Get filtered Phone Versions list			
			$phone_version = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".$wpdb->base_prefix . "cellable_phone_versions 
				where id = %d", $phone_version_id), ARRAY_A);

			if (!$phone_version || !$carrier_id) {
			?>
			<p>Proper Phone Version can't be found.</p>
			<a href="<?=get_home_url() ?>">Go To Homepage</a>
			<?php
				return;
			}

			$possible_defect_groups = $wpdb->get_results($wpdb->prepare("SELECT distinct(defect_group_id) id FROM ". $wpdb->base_prefix. "cellable_possible_defects 
				where phone_version_id = %d order by defect_group_id asc", $phone_version_id), ARRAY_A);

			// Update Phone Version View Count to DB		
			if ($phone_version['views'] !=null) {
				$phone_version['views'] +=1;
			}
			else{
				$phone_version['views'] =0;
			}

			$wpdb->update($wpdb->base_prefix.'cellable_phone_versions', array(
				'views' => $phone_version['views']
			), array(
				'id' => $phone_version_id,
			));
			
			$phone_brand = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".$wpdb->base_prefix . "cellable_phones 
				where id = %d", $phone_version['phone_id']), ARRAY_A);

			$phone_version_capacities = $wpdb->get_results($wpdb->prepare("SELECT * FROM ".$wpdb->base_prefix."cellable_version_capacities 
				where value>0 and phone_version_id = %d order by storage_capacity_id", $phone_version['id']), ARRAY_A);
			?>

			<form action="<?=get_home_url() ?>/price-phone/?phone_version_id=<?= $phone_version_id ?>" method="post">
				<input id="id" name="id" type="hidden" value="<?= $phone_version_id?>" />
				<input name="carrier_id" type="hidden" value="<?= $carrier_id?>" />
				<div class="row">
					<div class="col-md-3 col-xs-12">
						<div class="text-center">
							<img class="text-center" src="<?= $phone_version['image_file'] ?>" style="max-width:250px;" />
						</div>
						<br/>
						<p class="fine_print"><strong>Please Note:</strong> We do not pay for devices that have been reported lost or stolen.</p>
						<p class="fine_print"><strong><?= get_cellable_setting('DefectsFooter') ?></strong></p>
					</div>
					
					<div class="col-md-2 col-xs-12"></div>
					<div class="col-md-7 col-xs-12" id="defect_questions">
						<h2 id="phone_name"><?= $phone_brand['name'] ?> <?= $phone_version['name'] ?></h2>
						<p>Storage Capacity</p>
						<p>
							<?php 
							foreach ($phone_version_capacities as $phone_version_capacity): 								
								$capacity = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".$wpdb->base_prefix . "cellable_storage_capacities 
									where id = %d", $phone_version_capacity['storage_capacity_id']), ARRAY_A);
							?>
							<label>
								<input type="radio" name="capacity_id" value="<?= $capacity['id'] ?>" autocomplete="off" required/>&nbsp;
								<?= $capacity['description'] ?>
							</label>&nbsp;&nbsp;
							<?php endforeach; ?>
							<div id="CapacityValidationMessage" name="CapacityValidationMessage" style="display:inline-block" class="text-danger"></div>
							<input type="hidden" name="hdnCapacity" id="hdnCapacity" />
							<input type="hidden" name="hdnCapacityDesc" id="hdnCapacityDesc" />
						</p>

						<?php
						foreach ($possible_defect_groups as $possible_defect_group):
							$defect_group = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".$wpdb->base_prefix . "cellable_defect_groups 
									where id = %d", $possible_defect_group['id']), ARRAY_A);

							$possible_defects = $wpdb->get_results($wpdb->prepare("SELECT * FROM ".$wpdb->base_prefix . "cellable_possible_defects 
								where phone_version_id = %d and defect_group_id = %d", 
								$phone_version_id, $possible_defect_group['id']), ARRAY_A);
						?>
						<p>
							<?= $defect_group['name'] ?>
							<?php if ($defect_group['info']!=null): ?>
								<abbr title="<?= $defect_group['info'] ?>"><i class="fa fa-info-circle" style="cursor:pointer;"></i></abbr>	
							<?php endif; ?>
						</p>
						<p>
							<?php foreach ($possible_defects as $ele): ?>
							<label>
								<input name="defect_group_<?= $ele['defect_group_id'] ?>" id="<?= $ele['defect_group_id'] ?>" type="radio"	value="<?= $ele['id'] ?>" 
									autocomplete="off" onclick="setDefectId('<?=$ele['defect_group_id'] ?>','<?= $ele['id'] ?>')" required/> 
									&nbsp; <?= $ele['name'] ?>
							</label>&nbsp;&nbsp;&nbsp;
							<?php endforeach; ?>
							<input type="hidden" name="defect_ids[]" id="defect_group_id_<?= $ele['defect_group_id'] ?>"/>
						</p>
						<?php endforeach; ?>

						<input type="submit" class="cellable_button" value="Price My Phone" />
					</div>
				</div>
			</form>
		</div><!-- #primary -->
	</div>

<script>
	function setDefectId(defect_group_id, defect_id) {
		document.getElementById("defect_group_id_"+defect_group_id).value=defect_id;
	}
</script>

<?php
get_footer();