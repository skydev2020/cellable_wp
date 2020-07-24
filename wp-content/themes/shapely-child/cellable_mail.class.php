<?php 
require_once(ABSPATH . 'wp-content/themes/shapely-child/cellable_global.php');
require_once(ABSPATH . 'wp-content/themes/shapely-child/vendor/autoload.php');
class CellableMail
{ 
    // Constructor 
    public function __construct(){ 
        echo 'The class "' . __CLASS__ . '" was initiated!<br>'; 
    } 

    public function _USPSTrackingMessage($trackingNumber) {
        // USPSManager mgr = new USPSManager(USPSAPIUserName, true);
        // string msg = mgr.GetTrackingInfo(USPSAPIUserName, USPSAPIPassword, trackingNumber);

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

        
    public function GetShipStationLabel($orderId, $userId) {
        // var client = new RestClient("https://ssapi.shipstation.com/orders/createlabelfororder");
        // client.Timeout = -1;
        // var request = new RestRequest(Method.POST);
        // request.AddHeader("Host", "ssapi.shipstation.com");
        // request.AddHeader("Authorization", "__YOUR_AUTH_HERE__");
        // request.AddHeader("Content-Type", "application/json");
        // request.AddParameter("application/json", "{\n  \"orderId\": " + orderId + ",\n  \"carrierCode\": \"fedex\",\n  \"serviceCode\": \"fedex_2day\",\n  \"packageCode\": \"package\",\n  \"confirmation\": null,\n  \"shipDate\": \"2014-04-03\",\n  \"weight\": {\n    \"value\": 2,\n    \"units\": \"pounds\"\n  },\n  \"dimensions\": null,\n  \"insuranceOptions\": null,\n  \"internationalOptions\": null,\n  \"advancedOptions\": null,\n  \"testLabel\": false\n}", ParameterType.RequestBody);
        // IRestResponse response = client.Execute(request);
        // Console.WriteLine(response.Content);
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

        public function GetShippingLabel($userId, $orderId) {
            // Generate Mailing Label
            APIResource resource = new APIResource(ShippoLiveAPIToken);

            // To Address
            //Get Cellable Mail Info
            SystemSetting address = db.SystemSettings.Find(9);
            SystemSetting city = db.SystemSettings.Find(11);
            SystemSetting state = db.SystemSettings.Find(12);
            SystemSetting zip = db.SystemSettings.Find(13);
            SystemSetting phone = db.SystemSettings.Find(14);

            Hashtable toAddressTable = new Hashtable();
            toAddressTable.Add("name", "Cellable Receiving");
            toAddressTable.Add("company", "Cellable");
            toAddressTable.Add("street1", address.Value);
            toAddressTable.Add("city", city.Value);
            toAddressTable.Add("state", state.Value);
            toAddressTable.Add("zip", zip.Value);
            toAddressTable.Add("country", "US");
            toAddressTable.Add("phone", "+1 " + ContactUsPhone);
            toAddressTable.Add("email", ContactEmail);

            // from address
            // Get User Mail Info
            User user = db.Users.Find(userId);
            Hashtable fromAddressTable = new Hashtable();
            fromAddressTable.Add("name", user.FirstName + " " + user.LastName);
            fromAddressTable.Add("street1", user.Address);
            fromAddressTable.Add("city", user.City);
            fromAddressTable.Add("state", user.State);
            fromAddressTable.Add("zip", user.Zip);
            fromAddressTable.Add("country", "US");
            fromAddressTable.Add("email", user.Email);
            fromAddressTable.Add("phone", "+1 " + user.PhoneNumber);
            fromAddressTable.Add("metadata", "Order ID " + orderId);

            // parcel
            Hashtable parcelTable = new Hashtable();
            parcelTable.Add("length", "6");
            parcelTable.Add("width", "4");
            parcelTable.Add("height", "2");
            parcelTable.Add("distance_unit", "in");
            parcelTable.Add("weight", "7");
            parcelTable.Add("mass_unit", "oz");
            List<Hashtable> parcels = new List<Hashtable>();
            parcels.Add(parcelTable);


            // shipment
            Hashtable shipmentTable = new Hashtable();
            shipmentTable.Add("address_to", toAddressTable);
            shipmentTable.Add("address_from", fromAddressTable);
            shipmentTable.Add("parcels", parcels);
            shipmentTable.Add("object_purpose", "PURCHASE");
            shipmentTable.Add("async", false);

            // create Shipment object
            Shipment shipment = resource.CreateShipment(shipmentTable);

            // select desired shipping rate according to your business logic
            // we simply select the first rate in this example
            Rate rate = shipment.Rates[3];

            Hashtable transactionParameters = new Hashtable();
            transactionParameters.Add("rate", rate.ObjectId);
            transactionParameters.Add("async", false);
            Transaction transaction = resource.CreateTransaction(transactionParameters);

            if (((String)transaction.Status).Equals("SUCCESS", StringComparison.OrdinalIgnoreCase))
            {
                var order = new Order() 
                { 
                    OrderID = orderId,
                    MailingLabel = transaction.LabelURL.ToString(),
                    USPSTrackingId = transaction.TrackingNumber.ToString()
                };
                using (var db = new CellableEntities())
                {
                    db.Orders.Attach(order);
                    db.Entry(order).Property(x => x.MailingLabel).IsModified = true;
                    db.Entry(order).Property(x => x.USPSTrackingId).IsModified = true;
                    db.Configuration.ValidateOnSaveEnabled = false;
                    db.SaveChanges();
                }
            }
            else
            {
                Console.WriteLine("An Error has occured while generating your label. Messages : " + transaction.Messages);
            }
        }
      
} 
   

?> 