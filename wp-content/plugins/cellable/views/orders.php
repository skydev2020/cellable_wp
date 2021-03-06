<?php

/* == NOTICE ===================================================================
 * Please do not alter this file. Instead: make a copy of the entire plugin, 
 * rename it, and work inside the copy. If you modify this plugin directly and 
 * an update is released, your changes will be lost!
 * ========================================================================== */



/*************************** LOAD THE BASE CLASS *******************************
 *******************************************************************************
 * The WP_List_Table class isn't automatically available to plugins, so we need
 * to check if it's available and load it if necessary. In this tutorial, we are
 * going to use the WP_List_Table class directly from WordPress core.
 *
 * IMPORTANT:
 * Please note that the WP_List_Table class technically isn't an official API,
 * and it could change at some point in the distant future. Should that happen,
 * I will update this plugin with the most current techniques for your reference
 * immediately.
 *
 * If you are really worried about future compatibility, you can make a copy of
 * the WP_List_Table class (file path is shown just below) to use and distribute
 * with your plugins. If you do that, just remember to change the name of the
 * class to avoid conflicts with core.
 *
 * Since I will be keeping this tutorial up-to-date for the foreseeable future,
 * I am going to work with the copy of the class provided in WordPress core.
 */
if(!class_exists('WP_List_Table')){
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

/************************** CREATE A PACKAGE CLASS *****************************
 *******************************************************************************
 * Create a new list table package that extends the core WP_List_Table class.
 * WP_List_Table contains most of the framework for geneemail the table, but we
 * need to define and override some methods so that our data can be displayed
 * exactly the way we need it to be.
 * 
 * To display this example on a page, you will first need to instantiate the class,
 * then call $yourInstance->prepare_items() to handle any data manipulation, then
 * finally call $yourInstance->display() to render the table to the page.
 * 
 * Our theme for this list table is going to be movies.
 */
class Cellable_Order_List_Table extends WP_List_Table {
    
    /** ************************************************************************
     * REQUIRED. Set up a constructor that references the parent constructor. We 
     * use the parent reference to set some default configs.
     ***************************************************************************/
    function __construct(){
        global $status, $page;
                
        //Set parent defaults
        parent::__construct( array(
            'singular'  => 'item',     //singular name of the listed records
            'plural'    => 'plugins',    //plural name of the listed records
            'ajax'      => false        //does this table support ajax?
        ) );
    
    }

    function single_row( $item ) {
        echo "<tr>";
        echo $this->single_row_columns( $item );
        echo "</tr>\n";
    }

    /** ************************************************************************
     * Recommended. This method is called when the parent class can't find a method
     * specifically build for a given column. Generally, it's recommended to include
     * one method for each column you want to render, keeping your package class
     * neat and organized. For example, if the class needs to process a column
     * named 'title', it would first see if a method named $this->column_title() 
     * exists - if it does, that method will be used. If it doesn't, this one will
     * be used. Generally, you should try to use custom column methods as much as 
     * possible. 
     * 
     * Since we have defined a column_title() method later on, this method doesn't
     * need to concern itself with any column with a name of 'title'. Instead, it
     * needs to handle everything else.
     * 
     * For more detailed insight into how columns are handled, take a look at 
     * WP_List_Table::single_row_columns()
     * 
     * @param array $item A singular item (one full row's worth of data)
     * @param array $column_name The name/slug of the column to be processed
     * @return string Text or HTML to be placed inside the column <td>
     **************************************************************************/
    function column_default($item, $column_name){
        switch($column_name){
            case 'status_name':
            case 'id':
            case 'pv_name':
            case 'pm_code':
            case 'pm_name':
            case 'discount':
            case 'p_name':
            case 'p_username':
            case 'usps_tracking_id':
            case 'created_date':
            case 'action':
                return $item[$column_name];
            case 'amount':
                return "$".$item[$column_name];
            default:
                return print_r($item,true); //Show the whole array for troubleshooting purposes
        }
    }

    /** ************************************************************************
     * Recommended. This is a custom column method and is responsible for what
     * is rendered in any column with a name/slug of 'title'. Every time the class
     * needs to render a column, it first looks for a method named 
     * column_{$column_title} - if it exists, that method is run. If it doesn't
     * exist, column_default() is called instead.
     * 
     * This example also illustrates how to implement rollover actions. Actions
     * should be an associative array formatted as 'slug'=>'link html' - and you
     * will need to generate the URLs yourself. You could even ensure the links
     * 
     * 
     * @see WP_List_Table::::single_row_columns()
     * @param array $item A singular item (one full row's worth of data)
     * @return string Text to be placed inside the column <td> (movie title only)
     **************************************************************************/
    
    function column_id($item){                
        //Build row actions
        global $wpdb;
        //Build row actions
        $actions = array(
            'edit'  => sprintf('<a href="?page=%s&action=%s&item=%s">Edit</a>',$_REQUEST['page'],'edit',$item['id'])            
        );
        
        //Return the title contents
        return sprintf('%1$s %2$s',
            /*$1%s*/ $item['id'],
            /*$3%s*/ $this->row_actions($actions)
        );
    }

    function column_username($item){                
        
        $user = get_user_by('id', $item['user_id']);
        if ($user) {
            return $user->first_name. " " . $user->last_name;
        }
        
        return "";
    }
    
    function column_discount($item){                
        if ($item['discount']>0):
            return $item['discount']."%";
        elseif ($item['dis_dollar_value']>0):
            return "$".$item['dis_dollar_value'];
        endif;
    }

    function column_created_date($item){    
        if ($item['created_date']) {
            $date = new DateTime($item['created_date']);
            $created_date = $date->format('m/d/Y h:i:s A');
            return $created_date;        
        }
        return "";                
    }

    function column_action($item){       
        return sprintf(
            '<input type="button" value="eBay" />'
        );      
    }


    /** ************************************************************************
     * REQUIRED if displaying checkboxes or using bulk actions! The 'cb' column
     * is given special treatment when columns are processed. It ALWAYS needs to
     * have it's own method.
     * 
     * @see WP_List_Table::::single_row_columns()
     * @param array $item A singular item (one full row's worth of data)
     * @return string Text to be placed inside the column <td> (movie title only)
     **************************************************************************/
    function column_cb($item){
        return sprintf(
            '<input type="checkbox" name="%1$s[]" value="%2$s" />',
            /*$1%s*/ $this->_args['singular'],  //Let's simply repurpose the table's singular label ("movie")
            /*$2%s*/ $item['id']                //The value of the checkbox should be the record's id
        );
    }


    /** ************************************************************************
     * REQUIRED! This method dictates the table's columns and titles. This should
     * return an array where the key is the column slug (and class) and the value 
     * is the column's title text. If you need a checkbox for bulk actions, refer
     * to the $columns array below.
     * 
     * The 'cb' column is treated differently than the rest. If including a checkbox
     * column in your table you must create a column_cb() method. If you don't need
     * bulk actions or checkboxes, simply leave the 'cb' entry out of your array.
     * 
     * @see WP_List_Table::::single_row_columns()
     * @return array An associative array containing column information: 'slugs'=>'Visible Titles'
     **************************************************************************/
    function get_columns(){
        $columns = array(
            'cb'        => '<input type="checkbox" />', //Render a checkbox instead of text
            'id'  => 'Id',
            'username'  => 'User Name',
            'status_name'     => 'Status',
            'pv_name' => "Phone",
            'amount' => "Amount",
            'pm_code' => "Promo Code",
            'pm_name' => "Promo Name",
            'discount' => "Discount",
            'p_name' => "Payment Type",
            'p_username' => "Payment User Name",
            'usps_tracking_id' => "USPS Tracking",
            'created_date' => "Ordered Date",
            'action' => "Action",
        );
        return $columns;
    }


    /** ************************************************************************
     * Optional. If you want one or more columns to be sortable (ASC/DESC toggle), 
     * you will need to register it here. This should return an array where the 
     * key is the column that needs to be sortable, and the value is db column to 
     * sort by. Often, the key and value will be the same, but this is not always
     * the case (as the value is a column name from the database, not the list table).
     * 
     * This method merely defines which columns should be sortable and makes them
     * clickable - it does not handle the actual sorting. You still need to detect
     * the ORDERBY and ORDER querystring variables within prepare_items() and sort
     * your data accordingly (usually by modifying your query).
     * 
     * @return array An associative array containing all the columns that should be sortable: 'slugs'=>array('data_values',bool)
     **************************************************************************/
    function get_sortable_columns() {
        $sortable_columns = array(
            'id'     => array('id',false),     //true means it's already sorted
            'status_name'  => array('status_name',false),
            'pv_name'  => array('pv_name',false),
            'amount'  => array('amount',false),
            'pm_code'  => array('pm_code',false),
            'pm_name'  => array('pm_name',false),
            'discount'  => array('discount',false),
            'p_name'  => array('p_name',false),
            'p_username'  => array('p_username',false),
            'usps_tracking_id'  => array('usps_tracking_id',false),
            'created_date'  => array('created_date',false),
        );
        return $sortable_columns;
    }
    
    /** ************************************************************************
     * Optional. If you need to include bulk actions in your list table, this is
     * the place to define them. Bulk actions are an associative array in the format
     * 'slug'=>'Visible Title'
     * 
     * If this method returns an empty value, no bulk action will be rendered. If
     * you specify any bulk actions, the bulk actions box will be rendered with
     * the table automatically on display().
     * 
     * Also note that list tables are not automatically wrapped in <form> elements,
     * so you will need to create those manually in order for bulk actions to function.
     * 
     * @return array An associative array containing all the bulk actions: 'slugs'=>'Visible Titles'
     **************************************************************************/
    function get_bulk_actions() {
        $actions = array(
            // 'delete'    => 'Delete'
        );
        return $actions;
    }


    /** ************************************************************************
     * Optional. You can handle your bulk actions anywhere or anyhow you prefer.
     * For this example package, we will handle it in the class to keep things
     * clean and organized.
     * 
     * @see $this->prepare_items()
     **************************************************************************/
    function process_bulk_action() {
        //Detect when a bulk action is being triggered...
        if(isset($_GET['item'])) {
            switch($this->current_action()){
                case 'delete':
                    if(is_array($_GET['item'])) {
                        foreach ($_GET['item'] as $item){
                            // delete_spark_lead($item);
                        }                        
                    }
                    else {
                        // delete_spark_lead($_GET['item']);
                    }
                    ?><div id="message" class="updated notice is-dismissible"><p><?php _e( 'Selected User Info Deleted.' );?></p></div><?php
                    break;
                default:
                    break;
            }
        }

    }


    /** ************************************************************************
     * REQUIRED! This is where you prepare your data for display. This method will
     * usually be used to query the database, sort and filter the data, and generally
     * get it ready to be displayed. At a minimum, we should set $this->items and
     * $this->set_pagination_args(), although the following properties and methods
     * are frequently interacted with here...
     * 
     * @global WPDB $wpdb
     * @uses $this->_column_headers
     * @uses $this->items
     * @uses $this->get_columns()
     * @uses $this->get_sortable_columns()
     * @uses $this->get_pagenum()
     * @uses $this->set_pagination_args()
     **************************************************************************/
    function prepare_items($search_str='') {
        global $wpdb; //This is used only if making any database queries

        /**
         * First, lets decide how many records per page to show
         */
        $per_page = 100;        
        
        /**
         * REQUIRED. Now we need to define our column headers. This includes a complete
         * array of columns to be displayed (slugs & titles), a list of columns
         * to keep hidden, and a list of columns that are sortable. Each of these
         * can be defined in another method (as we've done here) before being
         * used to build the value for our _column_headers property.
         */
        
        $columns = $this->get_columns();
        $hidden = array();
        $sortable = $this->get_sortable_columns();
        
        
        /**
         * REQUIRED. Finally, we build an array to be used by the class for column 
         * headers. The $this->_column_headers property takes an array which contains
         * 3 other arrays. One for all columns, one for hidden columns, and one
         * for sortable columns.
         */
        $this->_column_headers = array($columns, $hidden, $sortable);
        
        
        /**
         * Optional. You can handle your bulk actions however you see fit. In this
         * case, we'll handle them within our package just to keep things clean.
         */
        $this->process_bulk_action();
        
        
        /** ************************************************************************
         * Normally we would be querying data from a database and manipulating that
         * for use in your list table. For this example, we're going to simplify it
         * slightly and create a pre-built array. Think of this as the data that might
         * be returned by $wpdb->query()
         * 
         * In a real-world scenario, you would make your own custom query inside
         * this class' prepare_items() method.
         * 
         * @var array 
         **************************************************************************/
   
        $data = array();
        $sql_str = "SELECT o.id id, o.amount amount, os.name status_name, phv.name pv_name, ";
        $sql_str .= "pm.code pm_code, pm.name pm_name, pm.discount discount, pm.dollar_value dis_dollar_value, ";
        $sql_str .= "p.name p_name, o.payment_username p_username, o.usps_tracking_id usps_tracking_id, ";
        $sql_str .= "o.created_date created_date, o.user_id user_id ";
        $sql_str .= "FROM ".$wpdb->base_prefix."cellable_orders o ";
        $sql_str .= "left join ".$wpdb->base_prefix."cellable_order_statuses os on o.order_status_id = os.id ";
        $sql_str .= "left join ".$wpdb->base_prefix."cellable_order_details od on o.order_detail_id = od.id ";
        $sql_str .= "left join ".$wpdb->base_prefix."cellable_phone_versions phv on od.phone_version_id = phv.id ";
        $sql_str .= "left join ".$wpdb->base_prefix."cellable_promos pm on o.promo_id = pm.id ";
        $sql_str .= "left join ".$wpdb->base_prefix."cellable_payment_types p on o.payment_type_id = p.id ";
        
        // $sql_str .= "SELECT o.* FROM ".$wpdb->base_prefix."cellable_orders o left join ".$wpdb->base_prefix."cellable_order_stauses os ";

        if($search_str) {
            $sql_str .= "where phv.name like %s or o.amount like %s or os.name like %s or pm.code like %s ";
            $sql_str .="or pm.name like %s or pm.discount like %s or pm.dollar_value like %s or p.name like %s ";
            $sql_str .="or o.payment_username like %s or o.usps_tracking_id like %s or o.created_date like %s ";
            
            $data = $wpdb->get_results($wpdb->prepare($sql_str, '%'.$wpdb->esc_like($search_str).'%',
                '%'.$wpdb->esc_like($search_str).'%',
                '%'.$wpdb->esc_like($search_str).'%',
                '%'.$wpdb->esc_like($search_str).'%',
                '%'.$wpdb->esc_like($search_str).'%',
                '%'.$wpdb->esc_like($search_str).'%',
                '%'.$wpdb->esc_like($search_str).'%',
                '%'.$wpdb->esc_like($search_str).'%',
                '%'.$wpdb->esc_like($search_str).'%',
                '%'.$wpdb->esc_like($search_str).'%',
                '%'.$wpdb->esc_like($search_str).'%'),ARRAY_A);
        }
        else {            
            $data = $wpdb->get_results($sql_str,ARRAY_A);
        }
        
        /**
         * This checks for sorting input and sorts the data in our array accordingly.
         * 
         * In a real-world situation involving a database, you would probably want 
         * to handle sorting by passing the 'orderby' and 'order' values directly 
         * to a custom query. The returned data will be pre-sorted, and this array
         * sorting technique would be unnecessary.
         */
        function usort_reorder($a,$b){
            $orderby = (!empty($_REQUEST['orderby'])) ? $_REQUEST['orderby'] : 'id'; //If no sort, default to title
            $order = (!empty($_REQUEST['order'])) ? $_REQUEST['order'] : 'desc'; //If no order, default to asc
            if ($orderby == "id" || $orderby == "amount" || $orderby == "discount") {
                if ($a[$orderby] > $b[$orderby]) {
                    $result = 1;
                }
                else if ($a[$orderby] < $b[$orderby]) {
                    $result = -1;
                }
                else {                
                    $result = 0;                
                }
            }
            else {
                $result = strcmp($a[$orderby], $b[$orderby]); //Determine sort order
            }
            
            return ($order==='asc') ? $result : -$result; //Send final sort direction to usort
        }
        usort($data, 'usort_reorder');
        
        
        /***********************************************************************
         * ---------------------------------------------------------------------
         * vvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvv
         * 
         * In a real-world situation, this is where you would place your query.
         *
         * For information on making queries in WordPress, see this Codex entry:
         * http://codex.wordpress.org/Class_Reference/wpdb
         * 
         * ^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^
         * ---------------------------------------------------------------------
         **********************************************************************/
        
                
        /**
         * REQUIRED for pagination. Let's figure out what page the user is currently 
         * looking at. We'll need this later, so you should always include it in 
         * your own package classes.
         */
        $current_page = $this->get_pagenum();
        
        /**
         * REQUIRED for pagination. Let's check how many items are in our data array. 
         * In real-world use, this would be the total number of items in your database, 
         * without filtering. We'll need this later, so you should always include it 
         * in your own package classes.
         */
        $total_items = count($data);
        
        
        /**
         * The WP_List_Table class does not handle pagination for us, so we need
         * to ensure that the data is trimmed to only the current page. We can use
         * array_slice() to 
         */
        $data = array_slice($data,(($current_page-1)*$per_page),$per_page);

        /**
         * REQUIRED. Now we can add our *sorted* data to the items property, where 
         * it can be used by the rest of the class.
         */
        $this->items = $data;
        
        
        /**
         * REQUIRED. We also have to register our pagination options & calculations.
         */
        $this->set_pagination_args( array(
            'total_items' => $total_items,                  //WE have to calculate the total number of items
            'per_page'    => $per_page,                     //WE have to determine how many items to show on a page
            'total_pages' => ceil($total_items/$per_page)   //WE have to calculate the total number of pages
        ) );
    }


}


/** *************************** RENDER LEAD LIST PAGE ********************************
 *******************************************************************************
 * This function renders the admin page and the example list table. Although it's
 * possible to call prepare_items() and display() from the constructor, there
 * are often times where you may need to include logic here between those steps,
 * so we've instead called those methods explicitly. It keeps things flexible, and
 * it's the way the list tables are used in the WordPress core.
 */
function render_order_list(){

    if(isset($_GET['action']) && $_GET['action'] == 'edit')
    {
        $id = $_GET['item'];        
        render_edit_order_page($id);
        return;
    }

    //Create an instance of our package class...
    $order_list_table = new Cellable_Order_List_Table();
    //Fetch, prepare, sort, and filter our data...
    $search_str = isset($_REQUEST['s']) ? $_REQUEST['s']: "";    
    $order_list_table->prepare_items($search_str);?>
    <div class="wrap">
        
        <div id="icon-users" class="icon32"><br/></div>
        <h2>Cellable Orders</h2>
        <!-- Forms are NOT created automatically, so you need to wrap the table in one to use features like bulk actions -->
        <form id="orders-filter" method="get">
            <!-- For plugins, we also need to ensure that the form posts back to our current page -->
            <input type="hidden" name="page" value="<?php echo $_REQUEST['page']?>" />
            <!-- Now we can render the completed list table -->
            <?php $order_list_table->search_box('Search','order_id');?>
            <?php $order_list_table->display()?>
            <input type="hidden" name="_wp_http_referer" value="">
        </form>
        
    </div>
    <?php
}

function render_edit_order_page($id){
    global $wpdb;

    $sql_str = "SELECT o.id id, o.amount amount, os.name status_name, phv.name pv_name, ";
    $sql_str .= "pm.code pm_code, pm.name pm_name, pm.discount discount, pm.dollar_value dis_dollar_value, ";
    $sql_str .= "p.name p_name, o.payment_username p_username, o.usps_tracking_id usps_tracking_id, ";
    $sql_str .= "o.created_date created_date, o.user_id user_id, os.id osid ";
    $sql_str .= "FROM ".$wpdb->base_prefix."cellable_orders o ";
    $sql_str .= "left join ".$wpdb->base_prefix."cellable_order_statuses os on o.order_status_id = os.id ";
    $sql_str .= "left join ".$wpdb->base_prefix."cellable_order_details od on o.order_detail_id = od.id ";
    $sql_str .= "left join ".$wpdb->base_prefix."cellable_phone_versions phv on od.phone_version_id = phv.id ";
    $sql_str .= "left join ".$wpdb->base_prefix."cellable_promos pm on o.promo_id = pm.id ";
    $sql_str .= "left join ".$wpdb->base_prefix."cellable_payment_types p on o.payment_type_id = p.id ";
    $sql_str .= "where o.id = %d ";
    
    $info = $wpdb->get_row($wpdb->prepare($sql_str, $id));
    $discount_str = "";

    if ($info->discount>0):
        $discount_str = $info->discount."%";
    elseif ($info->dis_dollar_value>0):
        $discount_str = "$".$info->dis_dollar_value;
    endif;

    $date = new DateTime($info->created_date);
    $created_date = $date->format('m/d/Y h:i:s A');    

    $ouser = get_user_by('id', $info->user_id);
    $username = "";
    $email = "";
    $phone = "";
    $address = "";

    if ($ouser) {
        $username = $ouser->first_name." ".$ouser->last_name;
        $email = $ouser->user_email; 
        $phone = get_the_author_meta('phone_number', $ouser->ID);
        $address = get_the_author_meta('address1', $ouser->ID). " ". get_the_author_meta('address2', $ouser->ID) 
        .", " .get_the_author_meta('city', $ouser->ID).", " .get_the_author_meta('state', $ouser->ID);
    }

    $order_statuses = $wpdb->get_results("select * from ".$wpdb->base_prefix."cellable_order_statuses", ARRAY_A);
    
    $sql_str = "select * FROM ".$wpdb->base_prefix . "cellable_order_qas where order_id = %d ";
    $qas = $wpdb->get_results($wpdb->prepare($sql_str, $id), ARRAY_A);
    ?>
    <div class="wrap">
        <h2>Order</h2>
                
        <form method="post" class="validate" action="<?php echo plugins_url( 'actions.php', __FILE__);?>">
            <input name="id" hidden type="text" value="<?php echo $id?>">
            <table class="form-table" role="presentation">
                <tbody>                        
                    <tr class="form-field">
                        <th scope="row">
                            <label>Id</label>
                        </th>
                        <td>
                            <?= $info->id;?>
                        </td>
                    </tr>
                    <tr class="form-field">
                        <th scope="row">
                            <label>User Name</label>
                        </th>
                        <td>
                            <?= $username;?>
                        </td>
                    </tr>
                    <tr class="form-field">
                        <th scope="row">
                            <label>Email</label>
                        </th>
                        <td>
                            <?= $email;?>
                        </td>
                    </tr>
                    <tr class="form-field">
                        <th scope="row">
                            <label>Phone</label>
                        </th>
                        <td>
                            <?= $phone?>
                        </td>
                    </tr>
                    <tr class="form-field">
                        <th scope="row">
                            <label>Address</label>
                        </th>
                        <td>
                            <?= $address ?>
                        </td>
                    </tr>
                    <tr class="form-field">
                        <th scope="row">
                            <label>Phone</label>
                        </th>
                        <td>
                            <?= $info->pv_name;?>
                        </td>
                    </tr>
                    <tr class="form-field">
                        <th scope="row">
                            <label>Amount</label>
                        </th>
                        <td>
                            $<?= $info->amount;?>
                        </td>
                    </tr>
                    <tr class="form-field">
                        <th scope="row">
                            <label>Promo Code</label>
                        </th>
                        <td>
                            <?= $info->pm_code;?>
                        </td>
                    </tr>
                    <tr class="form-field">
                        <th scope="row">
                            <label>Promo Name</label>
                        </th>
                        <td>
                            <?= $info->pm_name;?>
                        </td>
                    </tr>
                    <tr class="form-field">
                        <th scope="row">
                            <label>Discount</label>
                        </th>
                        <td>
                            <?= $discount_str;?>
                        </td>
                    </tr>
                    <tr class="form-field">
                        <th scope="row">
                            <label>Payment Type</label>
                        </th>
                        <td>
                            <?= $info->p_name;?>
                        </td>
                    </tr>
                    <tr class="form-field">
                        <th scope="row">
                            <label>Payment UserName</label>
                        </th>
                        <td>
                            <?= $info->p_username;?>
                        </td>
                    </tr>
                    <tr class="form-field">
                        <th scope="row">
                            <label>USPS Tracking</label>
                        </th>
                        <td>
                            <?= $info->usps_tracking_id;?>
                        </td>
                    </tr>
                    <tr class="form-field">
                        <th scope="row">
                            <label>Ordered Date</label>
                        </th>
                        <td>
                            <?= $created_date;?>
                        </td>
                    </tr>
                    <tr class="form-field">
                        <th scope="row" colspan="2">
                            <label><strong>Order Question and Answers</strong></label>
                        </th>
                    </tr>
                    <?php foreach($qas as $qa): ?>
                    <tr class="form-field">
                        <th scope="row">
                            <label><i><?= $qa['question'];?></i></label>
                        </th>
                        <td>
                            <?= $qa['answer'];?> : <?= $qa['cost'] >= 0 ? "$".$qa['cost'] : "-$". abs($qa['cost'])?>
                        </td>
                    </tr
                    <?php endforeach; ?>
                    <tr class="form-field form-required">
                        <th scope="row">
                            <label for="value">Status</label>
                        </th>
                        <td>
                            <select name="status_id">
                            <?php foreach ($order_statuses as $ele) :?>
                                <option value="<?= $ele['id'] ?>" <?= ($info->osid == $ele['id']) ? 
                                "selected" : "" ?>> <?= $ele['name'] ?> </option>
                            <?php endforeach; ?>
                            </select>                            
                        </td>
                    </tr>
                </tbody>
            </table>
            <p class="submit">
                <input type="submit" name="CELLABLE_ORDER_UPDATE" class="button button-primary" value="Save Changes">
            </p>
        </form>
    </div>

<?php
}