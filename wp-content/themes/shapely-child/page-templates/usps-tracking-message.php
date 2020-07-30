<?php
/*
Template Name: Cellable USPS Tracking Message
Template Post Type: page
*/
ob_start(); // this line is for the issue: header already sent
require_once(ABSPATH . 'wp-content/themes/shapely-child/cellable_shipping.class.php');
require_once(ABSPATH . 'wp-content/themes/shapely-child/cellable_email.class.php');

// $user = wp_get_current_user(); // ID->0: if user is not logged in
// if ($user->ID==0):
// 	header("Location: ".get_home_url()."/wp-login.php?action=login");
// 	exit();
// endif;

$tracking_number = $_REQUEST['tracking_number'];
// Get/Save Shipping Label
$shipping_mail = new CellableShipping;				
$msg = $shipping_mail->_USPSTrackingMessage($tracking_number);
$replace = "<SUP>&reg;</SUP>";
$msg = str_replace("&lt;SUP>&amp;reg;&lt;/SUP>", $replace, $msg);

?>
<table style="width:100%; margin-left:auto; margin-right:auto;">
    <tr>
        <td style="width:100%; text-align:center;">
            <img src="<?= $IMAGE_LOCATION ?>/usps-logo.jpg" style="width:380px; text-align:center;" />
        </td>
    </tr>
</table>
<table style="width:80%; margin-left:auto; margin-right:auto;">
    <tr>
        <td>
			<?= $msg ?>
        </td>
    </tr>
</table>
