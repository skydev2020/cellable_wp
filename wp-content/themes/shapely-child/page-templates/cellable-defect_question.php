<?php
/*
Template Name: Cellable Defect Question
Template Post Type: page
*/
require_once(ABSPATH . 'wp-content/plugins/cellable/cellable_global.php');

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
		$carrier_id = $_REQUEST['carrier_id'];
		// Get filtered Phone Versions list
		$phone_version = $wpdb->get_row("SELECT * FROM " . $wpdb->base_prefix ."cellable_phone_versions WHERE id=" . $phone_version_id, ARRAY_A);

		if (!$phone_version || !$carrier_id) {
		?>
		<p>Proper Phone Version can't be found.</p>
		<a href="<?=get_home_url() ?>">Go To Homepage</a>
		
		<?php
			return;
		}			

		$possible_defect_groups = $wpdb->get_results($wpdb->prepare("SELECT distinct(defect_group_id) id FROM ". $wpdb->base_prefix. "cellable_possible_defects 
			where phone_version_id = %d order by defect_group_id asc", 
			$wpdb->esc_like($phone_version_id)), ARRAY_A);
		
		// Update Phone Version View Count to DB		
		if ($phone_version['views'] !=null) {
			$phone_version['views'] +=1;
		}
		else {
			$phone_version['views'] =0;
		}

		$wpdb->update($wpdb->base_prefix.'cellable_phone_versions', array(            
			'views' => $phone_version['views']
		), array(
			'id' => $phone_version_id,
		));
		
		$phone_brand = $wpdb->get_row("SELECT * FROM ". $wpdb->base_prefix ."cellable_phones WHERE id=" . $phone_version['phone_id'], ARRAY_A);
		$phone_version_capacities = $wpdb->get_results($wpdb->prepare("SELECT * FROM ".$wpdb->base_prefix."cellable_version_capacities 
			where phone_version_id = %d", $phone_version['id']), ARRAY_A);

		?>
		<form action="<?=get_home_url() ?>/price-phone/?phone_version_id=<?= $phone_version_id ?>" method="post">
			<input id="id" name="id" type="hidden" value="<?= $phone_version_id?>" />
			<input name="carrier_id" type="hidden" value="<?= $carrier_id?>" />
			<table class="margin-left-auto margin-right-auto" style="width:70%; font-family:'HP Simplified'">
				<tr>
					<td class="text-center v-top" style="width:30%;">
						<div style="height:50px;"></div>
						<div style=" width:300px">
							<?= $phone_brand['name'] ?>
							<br />
							<?= $phone_version['name'] ?>
							<br />
							<img src="<?= $phone_version['image_file'] ?>" style="height:250px; width:130px;" />
							<br />
							Please Note: We do not pay for devices that have been reported lost or stolen.
							<p>
								<input type="submit" class="btn" value="Price Phone" />
								<input type="reset" class="btn" value="Reset Form" />
							</p>
						</div>
					</td>
					<td class="text-left">
						<table class="margin-left-auto margin-right-auto" style="width:80%;">
							<tr>
								<td>
									<div style=" margin-right: 50px; height: 125px; border: medium solid #ccc; width: 600px; border-radius: 15px; margin-left: 24px;">
										<div class="text-left" style="background-color:lightgray;">
											&nbsp;&nbsp;&nbsp;
											<p class="text-left" style="font-size:large; background-color:lightgray; margin-top: -10px; width: 573px; margin-left: 17px; border-bottom: thin solid #ccc; ">
												Storage Capacity
											</p>
										</div>
										<div class="inline-block" style="width:30px;"></div>
										<?php 
										foreach ($phone_version_capacities as $phone_version_capacity): 
											$capacity = $wpdb->get_row("SELECT * FROM " .$wpdb->base_prefix ."cellable_storage_capacities WHERE id=" . $phone_version_capacity['storage_capacity_id'], ARRAY_A);
										?>
											<label>
												<input type="radio" name="capacity_id" value="<?= $capacity['id'] ?>" autocomplete="off" required/>&nbsp;
												<?= $capacity['description'] ?>
											</label>&nbsp;&nbsp;
										<?php endforeach; ?>
										
										<br />
										<div class="inline-block" style="width:30px;"></div><div id="CapacityValidationMessage" name="CapacityValidationMessage" style="display:inline-block" class="text-danger"></div>
										<input type="hidden" name="hdnCapacity" id="hdnCapacity" />
										<input type="hidden" name="hdnCapacityDesc" id="hdnCapacityDesc" />
									</div>
								</td>
							</tr>
							
							<?php 

							foreach ($possible_defect_groups as $possible_defect_group):
								
								$defect_group = $wpdb->get_row("SELECT * FROM ".$wpdb->base_prefix. "cellable_defect_groups WHERE id=" . $possible_defect_group['id'], ARRAY_A);
								$possible_defects = $wpdb->get_results($wpdb->prepare("SELECT * FROM ".$wpdb->base_prefix . "cellable_possible_defects 
									where phone_version_id = %d and defect_group_id = %d", 
									$wpdb->esc_like($phone_version_id), $wpdb->esc_like($possible_defect_group['id'])), ARRAY_A);
							?>
							<tr>
								<td>
									<div style="margin-right: 50px; height: 125px; border: medium solid #ccc; width: 600px; border-radius: 15px; margin-left: 24px;">
										&nbsp;&nbsp;&nbsp;
										<p class="text-left" style="margin-top: -10px; font-size:large; width: 573px; margin-left: 17px; border-bottom: thin solid #ccc; ">
											<?= $defect_group['name'] ?>
											<?php if ($defect_group['info']!=null): ?>
												<abbr title="<?= $defect_group['info'] ?>"><i class="fa fa-info-circle" style="cursor:pointer;"></i></abbr>	
											<?php endif; ?>
										</p>
										<div style="width:30px; display:inline-block"></div>
										
										<?php foreach ($possible_defects as $ele): ?>
										<label>
											<input name="defect_group_<?= $ele['defect_group_id'] ?>" id="<?= $ele['defect_group_id'] ?>" type="radio"	value="<?= $ele['id'] ?>" 
												autocomplete="off" onclick="setDefectId('<?=$ele['defect_group_id'] ?>','<?= $ele['id'] ?>')" required/> 
												&nbsp; <?= $ele['name'] ?>
										</label>&nbsp;&nbsp;&nbsp;
										<?php endforeach; ?>
										<input type="hidden" name="defect_ids[]" id="defect_group_id_<?= $ele['defect_group_id'] ?>"/>
									</div>
								</td>
							</tr>
							<?php endforeach; ?>
						</table>
					</td>
				</tr>
			</table>
			<table class="margin-left-auto margin-right-auto" style="width:70%;">
				<tr>
					<td class="text-center">
						<?= get_cellable_setting('DefectsFooter') ?>    
					</td>
				</tr>
			</table>
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