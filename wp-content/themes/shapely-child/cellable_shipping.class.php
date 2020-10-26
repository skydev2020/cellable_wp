<?php 
require_once(ABSPATH . 'wp-content/plugins/cellable/cellable_global.php');
require_once(ABSPATH . 'wp-content/themes/shapely-child/vendor/autoload.php');

class USPSManager {
    protected $testmode;
    protected $_userid;
    protected $production_url = "http://production.shippingapis.com/ShippingAPI.dll";
    protected $testing_url = "https://secure.shippingapis.com/ShippingAPI.dll";

    public function __construct($usps_webtool_userid, $testmode) {
        $this->_userid = $usps_webtool_userid;
        $this->testmode = isset($testmode) ? $testmode : false;
    }

    public function get_url() {
        return ($this->testmode == true) ? $this->testing_url : $this->production_url;
    }

    public function get_data($url) {
        $ch = curl_init();
        $timeout = 5;
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        $data = curl_exec($ch);
        curl_close($ch);
        return $data;
    }

    public function get_tracking_info($usps_api_username, $usps_api_password, $tracking_number) {
        try
        {                     
            $service = "TrackV2";
            $xml = rawurlencode("
            <TrackRequest USERID='". $usps_api_username."'>
                <TrackID ID=\"".$tracking_number."\"></TrackID>
                </TrackRequest>");
            $url = $this->get_url() . "?API=" . $service . "&XML=" . $xml;

            $xml = file_get_contents($url);
            error_log($xml);
            if (strpos($xml, "<Error>") !== false) {
                $idx1 = strpos($xml, "<Description>") + 13;
                $idx2 = strpos($xml, "<Description>");                
                $l = strlen($xml);
                $err_desc=substr($xml, $idx1, $idx2-$idx1);
                return $err_desc;
            }

            return $xml;
        }
        catch (Exception $e)
        {            
            error_log($e->getMessage());
            return $e->getMessage();
        }
    }
}

class CellableShipping
{ 
    
    public function _USPSTrackingMessage($tracking_number) {
        global $USPS_API_USERNAME;
        global $USPS_API_PASSWORD;

        $mgr = new USPSManager($USPS_API_USERNAME, true);
        $msg = $mgr->get_tracking_info($USPS_API_USERNAME, $USPS_API_PASSWORD, $tracking_number);
        return $msg;
        // MailHelper.TrackingMessage = msg;

        // return View();
    }

    // GET: Mail
    public function ValidateAddress($address, $city, $state) {
        // bool valid = true;

        // ///Create a new instance of the USPS Manager class
        // ///The constructor takes 2 arguments, the first is
        // ///your USPS Web Tools User ID and the second is 
        // ///true if you want to use the USPS Test Servers.
        // USPSManager mgr = new USPSManager(USPSAPIUserName, true);
        // Mail.Address a = new Mail.Address();
        // a.Address2 = address;
        // a.City = city;
        // a.State = state;

        // valid = mgr.ValidateAddress(USPSAPIUserName, a);

        // return valid;
    }

    public function GetZipCode($address, $city, $state) {
        // USPSManager m = new USPSManager(USPSAPIUserName, true);
        // Mail.Address a = new Mail.Address();
        // a.Address2 = "6406 Ivy Lane";
        // a.City = "Greenbelt";
        // a.State = "MD";
        // Mail.Address addressWithZip = m.GetZipcode(a);
        // string zip = addressWithZip.Zip;
    }

    public function GetCityStateFromZip($zip) {
            // USPSManager m = new USPSManager(USPSAPIUserName, true);
            // Mail.Address a = m.GetCityState(zip.ToString());
            // string outCity = a.City;
            // string outState = a.State;
    }

        
    public function GetShipStationLabel($user_id, $order_id) {
        global $SHIPSTATION_API_KEY;
        global $SHIPSTATION_API_SECRET;
        global $CONTACT_US_PHONE;
        global $wpdb;

        $curl = curl_init();
        $post_fields = "";
        $auth = "Basic ". base64_encode($SHIPSTATION_API_KEY.":". $SHIPSTATION_API_SECRET);

        $user =get_userdata($user_id);
        $address_setting = $wpdb->get_row("SELECT * FROM ".$wpdb->base_prefix."cellable_settings WHERE name='LocationAddress'", ARRAY_A);
        $city_setting = $wpdb->get_row("SELECT * FROM ".$wpdb->base_prefix."cellable_settings WHERE name='LocationCity'", ARRAY_A);
        $state_setting = $wpdb->get_row("SELECT * FROM ".$wpdb->base_prefix."cellable_settings WHERE name='LocationState'", ARRAY_A);
        $zip_setting = $wpdb->get_row("SELECT * FROM ".$wpdb->base_prefix."cellable_settings WHERE name='LocationZip'", ARRAY_A);
        $phone_setting = $wpdb->get_row("SELECT * FROM ".$wpdb->base_prefix."cellable_settings WHERE name='Phone'", ARRAY_A);
        
        $post_data = array(
            "carrierCode" => "stamps_com",
            "serviceCode" => "usps_first_class_mail",
            "packageCode"=> "package",
            "confirmation"=> "delivery",
            "shipDate"=> date('Y-m-d'),
            "weight"=> array(
              "value"=> 2,
              "units"=> "ounces"
            ),
            "dimensions"=> array(
              "units"=> "inches",
              "length"=> 6,
              "width"=> 4,
              "height"=> 2
            ),
            "shipFrom"=> array(
              "name"=> $user->first_name . " " . $user->last_name,
              "company"=> "Individual",
              "street1"=> get_the_author_meta('address1', $user_id),
              "street2"=> get_the_author_meta('address2', $user_id),
              "street3"=> null,
              "city"=> get_the_author_meta('city', $user_id),
              "state"=> get_the_author_meta('state', $user_id),
              "postalCode"=> get_the_author_meta('zip', $user_id),
              "country"=> "US",
              "phone"=> get_the_author_meta('phone_number', $user_id),
              "residential"=> false
            ),
            "shipTo"=> array(
              "name"=> "Cellable Receiving",
              "company"=> "Cellable",
              "street1"=> $address_setting['value'],
              "street2"=> null,
              "street3"=> null,
              "city"=> $city_setting['value'],
              "state"=> $state_setting['value'],
              "postalCode"=> $zip_setting['value'],
              "country"=> "US",
              "phone"=> '+1 '.$CONTACT_US_PHONE
            ),
            "insuranceOptions"=> null,
            "internationalOptions"=> null,
            "advancedOptions"=> null,
            "testLabel"=> true
        );
        error_log(json_encode($post_data));
        
        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://ssapi.shipstation.com/shipments/createlabel",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => json_encode($post_data),
            CURLOPT_HTTPHEADER => array(
                "Host: ssapi.shipstation.com",
                "Authorization: ".$auth,
                "Content-Type: application/json"
            ),
        ));

        $response = curl_exec($curl);
        $res = json_decode($response, true);
        curl_close($curl);
        error_log($response);

        if (isset($res['shipmentId'])){
            $order = $wpdb->get_row("SELECT * FROM ".$wpdb->base_prefix."cellable_orders WHERE id=" . $order_id, ARRAY_A);
            $r = $wpdb->query(
                $wpdb->prepare(
                    "UPDATE ". $wpdb->base_prefix. "cellable_orders SET label_data = %s, usps_tracking_id = %s where id = %d;",
                    $res['labelData'], $res['trackingNumber'], $order_id
                ) // $wpdb->prepare
            ); // $wpdb->query
          
        } 
        else {
            error_log("Creating Shipping Label Failed:");    
            error_log($response);  
            throw new Exception('Creating Shipping Label Failed:');      
        }

        
    }

        //public ShipStationCreateOrderRequest GetShipStationCreateOrderRequestByExternalRef(long external_ref)
        //{
        //    ShipStationCreateOrderRequest o = new ShipStationCreateOrderRequest();
        //    billTo b = new billTo();
        //    shipTo s = new shipTo();
        //    List<customsItems> Custom = new List<customsItems>();
        //    oShipInternational intl = new oShipInternational();
        //    using (var dc = new stylusDataContext())
        //    {
        //        ISingleResult<sp_get_order_by_external_refResult> res = dc.sp_get_order_by_external_ref(external_ref);
        //        foreach (sp_get_order_by_external_refResult ret in res)
        //        {
        //            o.orderNumber = external_ref.ToString();
        //            o.orderDate = ret.sale_datetime.ToString();

        //            o.orderStatus = "awaiting_shipment";

        //            b.name = "Your department.";
        //            b.company = "Your company";
        //            b.street1 = "Your address";
        //            b.city = "city";
        //            b.state = "ST";
        //            b.postalCode = "06902";
        //            b.country = "US";
        //            b.phone = "888-888-5555";
        //            b.residential = false;

        //            o.billTo = b;

        //            shipTo st = new shipTo();
        //            st.name = ret.customer_name;
        //            st.company = null;
        //            st.street1 = ret.shipping_address_1;
        //            st.street2 = ret.shipping_address_2;
        //            //st.street3 = ret.shipping_address_3;
        //            if (ret.shipping_country_code != "US")
        //            {
        //                intl.contents = "merchandise";
        //                intl.nonDelivery = "return_to_sender";
        //                customsItems c = new customsItems();
        //                c.description = ret.description;
        //                c.quantity = ret.quantity;
        //                c.value = 14 * ret.quantity;
        //                c.harmonizedTariffCode = "";
        //                c.countryOfOrigin = "US";
        //                Custom.Add(c);
        //                intl.customsItems = Custom;
        //                o.internationalOptions = intl;
        //            }

        //            st.city = ret.shipping_address_3;
        //            st.state = ret.shipping_address_4;
        //            st.postalCode = ret.shipping_postcode;
        //            st.country = ret.shipping_country_code;
        //            st.phone = ret.phone;
        //            st.residential = true;

        //            o.shipTo = st;
        //        }
        //        return o;
        //    }
        //    return o;
        //}

        public function GetShippingLabel($user_id, $order_id) {

            global $SHIPPO_API_LIVE_API_TOKEN;
            global $wpdb;
            global $CONTACT_US_PHONE;
            global $CONTACT_EMAIL;

            // Generate Mailing Label
            Shippo::setApiKey($SHIPPO_API_LIVE_API_TOKEN);
             
            // To Address
            //Get Cellable Mail Info
            $address_setting = $wpdb->get_row("SELECT * FROM ".$wpdb->base_prefix."cellable_settings WHERE name='LocationAddress'", ARRAY_A);
            $city_setting = $wpdb->get_row("SELECT * FROM ".$wpdb->base_prefix."cellable_settings WHERE name='LocationCity'", ARRAY_A);
            $state_setting = $wpdb->get_row("SELECT * FROM ".$wpdb->base_prefix."cellable_settings WHERE name='LocationState'", ARRAY_A);
            $zip_setting = $wpdb->get_row("SELECT * FROM ".$wpdb->base_prefix."cellable_settings WHERE name='LocationZip'", ARRAY_A);
            $phone_setting = $wpdb->get_row("SELECT * FROM ".$wpdb->base_prefix."cellable_settings WHERE name='Phone'", ARRAY_A);
            
            $user =get_userdata($user_id);

            $to_address = array(
                'name' => "Cellable Receiving",
                'company' => "Cellable",
                'street1' => $address_setting['value'],
                'city' => $city_setting['value'],
                'state' => $state_setting['value'],
                'zip' => $zip_setting['value'],
                'country' => 'US',
                'phone' => '+1 '.$CONTACT_US_PHONE,
                'email' => $CONTACT_EMAIL
            );

            // from address
            // Get User Mail Info
            $from_address = array(
                'name' => $user->first_name . " " . $user->last_name,
                'street1' => '76 may apple lane', //$user->address
                'city' => 'Franklin', //$user->city
                'state' => 'NC', //$user->state
                'zip' => '28734', //$user->zip
                'country' => 'US',
                'phone' => '+1 '.get_the_author_meta('phone_number', $user_id),
                'email' => $user->user_email,
                'metadata' => "Order ID " . $order_id
            );
            
            // parcel
            $parcel = array(
                'length'=> '6',
                'width'=> '4',
                'height'=> '2',
                'distance_unit'=> 'in',
                'weight'=> '2',
                'mass_unit'=> 'oz',
            );

            // shipment
            $shipment = Shippo_Shipment::create(
                array(
                    'address_from'=> $from_address,
                    'address_to'=> $to_address,
                    'parcels'=> array($parcel),
                    'object_purpose'=> 'PURCHASE',
                    'async'=> false,
                )
            );

            // select desired shipping rate according to your business logic
            // we simply select the first rate in this example
            $rate = $shipment['rates'][0];
            
            $transaction = Shippo_Transaction::create(array(
                'rate'=> $rate['object_id'],
                'async'=> false,
            ));
                            
            if ($transaction['status'] == 'SUCCESS'){
                $r = $wpdb->query(
                    $wpdb->prepare(
                        "UPDATE ". $wpdb->base_prefix. "cellable_orders SET mailing_label = %s, usps_tracking_id = %s where id = %d;",
                        $transaction['label_url'], $transaction['tracking_number'], $order_id
                    ) // $wpdb->prepare
                ); // $wpdb->query              
            } 
            else {
                error_log("Transaction failed with messages:");
                $err_msg = "";
                foreach ($transaction['messages'] as $message) {
                    $err_msg .= $message;
                    error_log("--> " . $message);
                }

                $r = $wpdb->query(
                    $wpdb->prepare(
                        "UPDATE ". $wpdb->base_prefix. "cellable_orders SET error_msg = %s where id = %d;",
                        $err_msg, $order_id
                    ) // $wpdb->prepare
                ); // $wpdb->query

            }

        }
      
} 
   

?> 