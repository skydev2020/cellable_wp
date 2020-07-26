<?php 
require_once(ABSPATH . 'wp-content/themes/shapely-child/cellable_global.php');

class CellableEmail
{ 
    
    public function send_email($order_id, $type, $to_email) {
            
        $html_body = "";
        $subject = "";

        try
        {
            $subject = "";
            if ($type=="Confirm") {
                $html_body = $this->build_html($order_id);
                $subject = "Cellable Confirmation";
            }
            else if ($type=="Password") {
                $html_body = $this->build_password_html($to_email);
                $subject = "Reset Cellable Password";
            }

            add_filter( 'wp_mail_content_type', function( $content_type ) {return 'text/html';});
            $headers[] = "From: ".$CONFIRMATION_EMAIL."\r\n";
            $headers[] = "Bcc: ". $CONFIRMATION_EMAIL ."\r\n";;

            wp_mail( $to_email, $subject, $html_body, $headers);
            return true;
        } catch (Exception $e) {
            error_log($e->getMessage());
            return false;
        }
    }

    
    public function build_html($order_id)
    {
        $first_name = "";
        $last_name = "";
        $email = "";        
        $amount = "";
        $payment_type = "";
        $payment_username = "";
        $phone_version = "";
        $tracking_number = "";
        $mail_label = "";
        $packing_sheet = "https://cellableimages.blob.core.windows.net/systemimages/PackingSheet.pdf";

        $order = $wpdb->get_row("SELECT * FROM ". $wpdb->base_prefix ."cellable_orders WHERE id=".$order_id, ARRAY_A);
        $payment_type = $wpdb->get_row("SELECT * FROM ". $wpdb->base_prefix ."cellable_payment_types WHERE id=".$order['payment_type_id'], ARRAY_A);
        $order_detail = $wpdb->get_row("SELECT * FROM ". $wpdb->base_prefix ."cellable_order_details WHERE id=".$order['order_detail_id'], ARRAY_A);
        $phone_version = $wpdb->get_row("SELECT * FROM ". $wpdb->base_prefix ."cellable_phone_versions WHERE id=".$order_detail['phone_version_id'], ARRAY_A);

        $user =get_userdata($order['user_id']);

        $first_name = $user->first_name;
        $last_name = $user->last_name;
        $email = $user->user_email;

        $html = "<table style='margin-top: 50px;'>" +
                "<tr style='border: solid; border-width: thin; background-color: black;'>" +
                    "<th style='color: white;'>First Name" +
                    "</th>" +
                    "<td style='width: 30px;'></td>" +
                    "<th style='color: white;'>Last Name " +
                    "</th>" +
                    "<td style='width: 30px;'></td>" +
                    "<th style='color: white;'>Order Number" +
                    "</th>" +
                    "<td style='width: 30px;'></td>" +
                    "<th style='color: white;'>Phone Version" +
                    "</th>" +
                    "<td style='width: 30px;'></td>" +
                    "<th style='color: white;'>Order Amount" +
                    "</th>" +
                    "<td style='width: 30px;'></td>" +
                "</tr>" +
                "<tr>" +
                    "<td>" +
                    $user->first_name +
                    "</td>" +
                    "<td></td>" +
                    "<td>" +
                    $user->last_name +
                    "</td>" +
                    "<td></td>" +
                    "<td>" +
                    $order_id +
                    "</td>" +
                    "<td></td>" +
                    "<td>" +
                    $phone_version['name'] +
                    "</td>" +
                    "<td></td>" +
                    "<td>$" +
                    number_format((float)$order['amount'], 2, '.', '') +
                    "</td>" +
                    "<td></td>" +
                "</tr>" +
                "<tr style='border: solid; border-width: thin; background-color: black;'>" +
                    "<th style='color: white;'>Payment Type" +
                    "</th>" +
                    "<td style='width: 30px;'></td>" +
                    "<th style='color: white'>Payment ID" +
                    "</th>" +
                    "<td style='width: 30px;'></td>" +
                    "<th style='color: white;'>Email" +
                    "</th>" +
                    "<td></td>" +
                "</tr>" +
                "<tr>" +
                    "<td>" +
                        $payment_type['name'] +
                    "</td>" +
                    "<td style='width: 30px;'></td>" +
                    "<td>" +
                        $order['payment_username'] +
                    "</td>" +
                    "<td style='width: 30px;'></td>" +
                    "<td>" +
                        $user->user_email +
                    "</td>" +
                    "<td style='width: 30px;'></td>" +
                "</tr>" +
            "</table>" +
            "<table style='margin-top: 30px;'>" +
                "<tr>" +
                    "<td>" +
                        "<h3>Thank you for shopping with us!" +
                        "</h3>" +
                        "<p style='border-bottom: thin solid #000;'></p>" +
                    "</td>" +
                "</tr>" +
                "<tr>" +
                    "<td>" +
                        "<p>" +
                            "We're excited that you've decided to sell your gadgets to Cellable. To print your free shipping label and packing slip, print out the attachment on this email." +
                        "</p>" +
                        "<p>" +
                            "Please be sure to include your packing slip inside your box so we can easily identify your items." +
                        "</p>" +
                    "</td>" +
                "</tr>" +
                "<tr style='margin-top: 30px;'>" +
                    "<td>" +
                        "<h3>Shipping to Cellable</h3>" +
                    "</td>" +
                "</tr>" +
                "<tr>" +
                    "<td>1. Do a factory reset on the phones. This prevents missuse of data and information." +
                    "</td>" +
                "</tr>" +
                "<tr>" +
                    "<td>2. Find a strong box with plenty of packing materials to pack your items. Please do not add any items that are not on your packing slip." +
                    "</td>" +
                "</tr>" +
                "<tr>" +
                    "<td>3. Pack your items and packing slip in the box with plenty of packing materials. Tape your prepaid shipping label to the box. To print your prepaid shipping label click your attachment." +
                    "</td>" +
                "</tr>" +
                "<tr>" +
                    "<td>4. Drop your box off for shipping at the nearest USPS location." +
                    "</td>" +
                "</tr>" +
                "<tr>" +
                    "<td><b>Click <a href='" + $order['mailing_label'] + "'>HERE</a> to download your shipping label</b>" +
                    "</td>" +
                "</tr>" +
                "<tr>" +
                    "<td><b>Click <a href='" + $packing_sheet + "'>HERE</a> to download your packing slip</b>" +
                    "</td>" +
                "</tr>" +
            "</table>" +
            "<table>" +
                "<tr style='margin-top: 30px;'>" +
                    "<td>We'll let you know as soon as we receive your items. If you have any questions along the way, you can check the status of your items on our website or visit our help center." +
                    "</td>" +
                "</tr>" +
                "<tr>" +
                    "<td style='height: 36px'>" +
                        "<p style='margin-top: 32px;'>" +
                            "Thanks,</p>" +
                                "<p style='margin-left: 35px;'>  Cellable team" +
                        "</p>" +
                    "</td>" +
                "</tr>" +
                "<tr style='margin-top: 50px;'>" +
                    "<td style='margin-top: 30px;'>" +
                        "<p style='text-align: center; margin-top: 30px; font-size: xx-small;'>" +
                            "© Copyright 2020 - Cellable LLC, All Rights Reserved, Patents Pending." +
                        "</p>" +
                        "<p style='text-align: center;  font-size: xx-small;'>" +
                            "Designated trademarks and brands are the property of their respective owners." +
                        "</p>" +
                        "<p style='text-align: center; font-size: xx-small;'>" +
                            "Cellable is not affiliated with the manufacturers of the items available for trade-in." +
                        "</p>" +
                        "<p style='text-align: center; font-size: xx-small;'>" +
                            $POSTAL_ADDRESS +
                        "</p>" +
                    "</td>" +
                "</tr>" +
            "</table>";

        return $html;
    }

    public function build_password_html($user_email) {
    
        $html= "<html>" +
                    "<head>" +
                    "</head>" +
                    "<body>" +
                    "<table>" +
                        "<tr>" +
                            "<td>" +
                                "We received a request to reset the password associated with this email address. " +
                                "If you made this request, please follow these instructions." +
                                "<p>" +
                                "Click this link to reset your password using our secure server." +
                                "<p>" +
                                "<a href='" + get_home_url() + "/users/forgot_password/?email=" + $user_email + "'> Reset Password</a>" +
                                "<p>" +
                                "If clicking on the link doesn't work, copy and paste it into the address window of your browser or retype it there." +
                                "<p>" +
                                "If you did not make this request, please ignore this email." +
                                "<p>" +
                                "Thank you for using Cellable!" +
                            "</td>" +
                        "</tr>" +
                    "</table>" +
                    "</body>" +
                "</html>";

        return HTML;
    }
    
} 
   

?> 