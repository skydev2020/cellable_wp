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
			$total_defect_value = $wpdb->get_var($wpdb->prepare("SELECT sum(cost) FROM wp_cellable_possible_defects WHERE id in %s",
				"(".implode(', ', $defect_ids).")"), ARRAY_A );
			
			$price = $price-$total_defect_value;
			
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
								<?= number_format((float)$price, 2, '.', '')  ?>								
							</div>
							<p></p>
							@if (decimal.Parse(Session["Phone Value"].ToString()) > 0)
							{
								if (Session["PromoCode"] == null)
								{
									using (Html.BeginForm("CalcPromo", "Phones", FormMethod.Post, new { enctype = "multipart/form-data" }))
									{
										Html.AntiForgeryToken();

										Html.Raw("Do you have a Promo Code?");
										<br />
										<input id="id" name="id" type="hidden" value="@Session["VersionId"]" />
										<input id="PromoCode" name="PromoCode" type="text" placeholder="Enter Promo Code" autofocus />
										<button type="submit" name="submit" id="PromoCode" class="PromoCode" value="reset">
											<i class="fa fa-plus-square"></i>
										</button>
										<p></p>
									}
								}
								else
								{
									//string discount = Session["PromoDiscount"].ToString();
									//discount.Split(char["."])[0];

									Html.Raw("Promo Code Applied");
									<br />
									Html.Raw("Promo Code: " + Session["PromoCode"]);
									<br />
									if (Session["PromoType"].ToString() == "%")
									{
										Html.Raw("+" + Session["PromoValue"] + Session["PromoType"]);
									}
									else
									{
										Html.Raw("+" + Session["PromoType"] + decimal.Round(decimal.Parse(Session["PromoValue"].ToString()), 2).ToString());
									}
								}
								using (Html.BeginForm("CompleteUserPhoneRegistration", "Users"))
								{
									@Html.AntiForgeryToken()
									<p>
										<input type="submit" value="Sell My Phone" class="button" onclick="return valid_form()" />
										<input type="button" value="Cancel" class="button" onclick="location.href='@Url.Action("Cancel", "Users")';return false;" />
									</p>
								}
							}
							else
							{
								@Html.Raw("<div class='text-danger'>Unfortunately, we cannot purchase your phone.</div>")
							}
						</td>
						
						<td style="text-align:left;">
							<table style="width:80%; margin-left:auto; margin-right:auto;">
								<tr>
									<td>
										<div style=" margin-right: 50px; height: 125px; border: medium solid #ccc; width: 600px; border-radius: 15px; margin-left: 24px;">
											<div style="text-align:left; background-color:lightgray;">
												&nbsp;&nbsp;&nbsp;
												<p style="text-align:left; font-size:large; background-color:lightgray; margin-top: -10px; width: 573px; margin-left: 17px; border-bottom: thin solid #ccc; ">
													Storage Capacity
												</p>
											</div>
											<div style="display:inline-block; width:30px;"></div>
											<?php foreach ($capacities as $capacity): ?>
												<?php foreach ($phone_version_capacities as $phone_version_capacity): ?>
													<?php if ($capacity['id'] == $phone_version_capacity['storage_capacity_id']): ?>
												
												<label>
													<input  type="radio" name="capacity" value="<?= $phone_version_capacity['value'] ?>" onchange="SetField('capacity', <?= $capacity['value'] ?>, '<?= $phone_version_capacity['description'] ?>')" autocomplete="off" />&nbsp;
													<?= $capacity['description'] ?>
												</label>&nbsp;&nbsp;
												
													<?php endif; ?>
												<?php endforeach; ?>
											<?php endforeach; ?>
											
											
											<br />
											<div style="display:inline-block; width:30px;"></div><div id="CapacityValidationMessage" name="CapacityValidationMessage" style="display:inline-block" class="text-danger"></div>
											<input type="hidden" name="hdnCapacity" id="hdnCapacity" />
											<input type="hidden" name="hdnCapacityDesc" id="hdnCapacityDesc" />
										</div>
									</td>
								</tr>
								
								<?php 

								foreach ($possible_defect_groups as $possible_defect_group):
									
									$defect_group = $wpdb->get_row("SELECT * FROM wp_cellable_defect_groups WHERE id=" . $possible_defect_group['id'], ARRAY_A);
									$possible_defects = $wpdb->get_results($wpdb->prepare("SELECT * FROM wp_cellable_possible_defects 
										where phone_version_id = %d and defect_group_id = %d", 
										$wpdb->esc_like($phone_version_id), $wpdb->esc_like($possible_defect_group['id'])), ARRAY_A);
								?>
								<tr>
									<td>
										<div style="margin-right: 50px; height: 125px; border: medium solid #ccc; width: 600px; border-radius: 15px; margin-left: 24px;">
											&nbsp;&nbsp;&nbsp;
											<p style="text-align:left; margin-top: -10px; font-size:large; width: 573px; margin-left: 17px; border-bottom: thin solid #ccc; ">
												<?= $defect_group['name'] ?>
												<?php if ($defect_group['info']!=null): ?>
													<abbr title="<?= $defect_group['info'] ?>"><i class="fa fa-info-circle" style="cursor:pointer;"></i></abbr>	
												<?php endif; ?>
											</p>
											<div style="width:30px; display:inline-block"></div>
											
											<?php foreach ($possible_defects as $ele): ?>
											<label>
												<input id="<?= $ele['defect_group_id'] ?>" type="radio" name="<?= $ele['defect_group_id'] ?>" 
													value="<?= $defect_group['id'] ?>_<?= $ele['id'] ?>_<?= $ele['cost'] ?>" 
													onchange="SetField('<?= $defect_group['id'] ?>', null, null)" autocomplete="off"/> 
													&nbsp; <?= $ele['name'] ?>
											</label>&nbsp;&nbsp;&nbsp;
											<?php endforeach; ?>
											
											<div id="<?= $defect_group['id'] ?>" name="<?= $defect_group['id'] ?>" class="text-danger" style="margin-left:40px; display:none;">* Required</div>
											<input type="hidden" id="hdn_<?= $defect_group['id'] ?>" name="hdn_<?= $defect_group['id'] ?>" />
										</div>
									</td>
								</tr>
								<?php endforeach; ?>
								
									
								
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