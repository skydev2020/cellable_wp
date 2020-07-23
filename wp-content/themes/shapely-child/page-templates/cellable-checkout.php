<?php
/*
Template Name: Cellable Checkout
Template Post Type: page
*/
require_once(ABSPATH . 'wp-content/themes/shapely-child/cellable_global.php');

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
			$defect_ids = $_REQUEST['defect_ids'];

			$payment_types =  $wpdb->get_results("SELECT * FROM wp_cellable_payment_types", ARRAY_A);
			
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
				$promo = $wpdb->get_row($wpdb->prepare("SELECT * FROM wp_cellable_promos WHERE code= %s
					and start_date <= CURDATE() and end_date >= CURDATE()", $wpdb->esc_like($promo_code)), ARRAY_A);
			}

			if ($promo['discount']>0):
				$price += $price * $promo['discount'] / 100;	
			else:
				$price += $promo['dollar_value'];
			endif;

			
			$possible_defect_groups = $wpdb->get_results($wpdb->prepare("SELECT distinct(defect_group_id) id FROM wp_cellable_possible_defects 
				where phone_version_id = %d order by defect_group_id asc", 
				$wpdb->esc_like($phone_version_id)), ARRAY_A);
			
			$phone_brand = $wpdb->get_row("SELECT * FROM wp_cellable_phones WHERE id=" . $phone_version['phone_id'], ARRAY_A);
						

			$capacities = $wpdb->get_results($wpdb->prepare("SELECT * FROM wp_cellable_storage_capacities"), ARRAY_A);
			$phone_version_capacities = $wpdb->get_results($wpdb->prepare("SELECT * FROM wp_cellable_version_capacities 
				where phone_version_id = %d", $phone_version['id']), ARRAY_A);


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
						<form action="/Users/Register?user=System.Security.Principal.GenericPrincipal" method="post">
							<input name="userEmail" type="hidden" value="<?= $user->user_email ?>">
							<input name="UserExists" type="hidden" value="True">
                            <table>
                                <tr>
                                    <td class="text-left" style="width:100%; padding:10px;">
                                        <i class="text-danger">*</i>&nbsp;Payment Method:
                                        <br/>
										<select class="form-control" name="payment_types" onchange="valid_form()">
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
                                        <input type="text" id="PaymentUserName" name="PaymentUserName" class="form-control" onchange="valid_form()" />
                                        <div id="PaymentUserNameValidationMessage" class="text-danger"></div>
                                        <br />
                                        <input type="submit" name="submit" id="submit" value="Complete Order" class="PromoCode" onclick="return valid_form()" />
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
                                            <td style="text-align:center" colspan="3">
                                                <div style="font-family:'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; color: green; font-size: 55px;">
                                                    $@decimal.Round(decimal.Parse(@Session["Phone Value"].ToString()), 2)
                                                </div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td style="text-align:center; background-color:lightgrey" colspan="3">
                                                <b>Your Phone Details</b><br />
                                            </td>
                                        </tr>
                                        <tr>
                                            <td style="padding:3px; text-align:right; font-weight:bold">
                                                Phone's Base Value:
                                            </td>
                                            <td style="width:25px; text-align:right; color:forestgreen; font-weight:bold">$</td>
                                            <td style="padding:3px; text-align:right; color:forestgreen; font-weight:bold">
                                                @decimal.Truncate(decimal.Parse(Session["BaseValue"].ToString())).00
                                            </td>
                                        </tr>
                                        @if (Session["PromoCode"] != null)
                                        {
                                            @Html.Raw("<tr>")
                                            @Html.Raw("<td  style='padding:3px; text-align:right;'>Promo Code Applied:</td>")
                                            @Html.Raw("<td style='width:25px; text-align:right; color:forestgreen;'>+</td>")
                                            @Html.Raw("<td style='width:25px; text-align:right; color:forestgreen;'>")
                                            if (Session["PromoType"].ToString() == "%")
                                            {
                                                @Html.Raw(Session["PromoValue"].ToString() + Session["PromoType"].ToString());
                                            }
                                            else
                                            {
                                                @Html.Raw(Session["PromoType"] + decimal.Round(decimal.Parse(Session["PromoValue"].ToString()), 2).ToString());
                                            }
                                            @Html.Raw("</td>")
                                            @Html.Raw("</tr>")
                                        }
                                        <tr>
                                            <td style="padding:3px; text-align:right; font-weight:bold; border-top:solid; border-top-color:black; border-top-width:1px">
                                                Phone Value:
                                            </td>
                                            <td style="width:25px; text-align:right; color:forestgreen; font-weight:bold; border-top:solid; border-top-color:black; border-top-width:1px">$</td>
                                            <td style="padding:3px; text-align:right; color:forestgreen; font-weight:bold; border-top:solid; border-top-color:black; border-top-width:1px">
                                                @decimal.Round(decimal.Parse(@Session["Phone Value"].ToString()), 2)
                                            </td>
                                        </tr>
                                    </table>
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