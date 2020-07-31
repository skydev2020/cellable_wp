<?php
if ( ! defined( 'ABSPATH' ) ) {
    require_once('../../../../wp-config.php');
}
// require_once('../../../../wp-config.php');
// include "../../../../wp-admin/includes/file.php";


global $wpdb;

if(isset($_POST['CELLABLE_SETTING_UPDATE']))
{   
    if(isset($_POST['id'])){       
        $value = stripslashes($_POST['value']);

        $wpdb->update($wpdb->base_prefix.'cellable_settings', array(
            'value' => $value
        ), array(
            'id' => $_POST['id']
        ));
        
        header('Location: ' . $_SERVER['HTTP_REFERER']);
    }
}
if(isset($_POST['SUPER_SPARK_FIELDS']))
{
    if(isset($_POST['id'])){
        //First check whether it has already activated Site with new-site-id, if so rejects it
        if ($_POST['status']=='Activated') {
            $info = $wpdb->get_row("SELECT * FROM wp_spark_fields WHERE status='Activated' and site_id='" . $_POST['new_site_id'] . "'");
        
            if ($info && $info->id != $_POST['id'] ) {
                // It means there are already existing activated site, so you need to choose another one.
                // printf("That site is already taken by someone else, please try another one. <a href='../../../../wp-admin/admin.php?page=users_table'>Go To Spark Admins</a>");
                printf("That site is already taken by someone else, please try another one. <a href='javascript:window.history.back();'>Go Back</a>");
                // header('Location: ' . $_SERVER['HTTP_REFERER']);
                return;
            }
        }

        $new_site_id = $_POST['new_site_id']; 
        if ($new_site_id == 1) {
            printf("You can not select Super Admin Site. <a href='javascript:window.history.back();'>Go Back</a>");            
            return;
        }

        // Get New Domain Name based on New Site Id

        $domain = $wpdb->get_row("SELECT *  FROM wp_domainer where blog_id=".$_POST['new_site_id']);
        $domain_name = "None";
        if ($domain) {
            $domain_name=$domain->name;
        }

        $site_url = "None";

        // Get New Site Url based on New Site Id
        
        if ($new_site_id != '0' && $new_site_id != '-1' ) {
            $blog_info = get_blog_details($new_site_id);
            $urls = explode("/", $blog_info->path);
            if (count($urls) > 1)    {
                $site_url = $urls[count($urls)-2];
            }
        }
        
        $location_name = stripslashes($_POST['location_name']);
        $request_site_name = stripslashes($_POST['request_site_name']);

        $wpdb->update('wp_spark_fields', array(
            'site_id' => $_POST['new_site_id'],
            'phone' => $_POST['phone'],
            'email' => $_POST['email'],
            'domain' => $domain_name,
            'api_key' => $_POST['api_key'],
            'location_id' => $_POST['location_id'],
            'location_name' => $location_name,
            'siteurl' => $site_url,
            'address' => $_POST['address'],
            'city' => $_POST['city'],
            'state' => $_POST['state'],
            'postal_code' => $_POST['postal_code'],
            'logo' => $_POST['logo'],
            'location_email' => $_POST['location_email'],
            'request_site_name' => $request_site_name,
            'status' => $_POST['status']
        ), array(
            'id' => $_POST['id']
        ));

        // Update Individual Site Status as 'Archived' or Normal according to the status
        if ($new_site_id>1) {
            if ($_POST['status'] == 'Activated') {
                $wpdb->update('wp_blogs', array(
                    'archived' => 0,
                    'deleted' => 0                
                ), array(
                    'blog_id' => $new_site_id
                ));
            }
            else {
                $wpdb->update('wp_blogs', array(
                    'archived' => 1            
                ), array(
                    'blog_id' => $new_site_id
                ));
            }
           
        }
            
        header('Location: ' . $_SERVER['HTTP_REFERER']);
    }
}

if(isset($_POST['IMPORT_PAGE']))
{
    if(isset($_POST['id'])){
        $user_id = get_current_user_id();

        $title = stripslashes($_POST['title']);
        $tag_list = stripslashes($_POST['tag_list']);
        $redirect_url = stripslashes($_POST['redirect_url']);
        $style = stripslashes($_POST['style']);
        $headline = stripslashes($_POST['headline']);
        $triallength = stripslashes($_POST['triallength']);
        $offer_value = stripslashes($_POST['offer_value']);

        generate_popups_widgets($site_id);
        $new_post_id = import_page($site_id, $_POST['id'], $title, $user_id, $tag_list, $redirect_url, 
            $style, $headline, $triallength, $offer_value);        
        
        //  Switch Back to Current Site
        //  Switch_to_blog($site_id);
        $footer_link_vars = isset($_POST["footerlink_vars"]) ? $_POST["footerlink_vars"] : [];
        if (count($footer_link_vars) > 0) {
            $link_pages = [];

            for ($j=0; $j<count($footer_link_vars); $j++) {
                // FOOTERLINK_1#Home
                $pieces = explode("#", $footer_link_vars[$j]);
                if (count($pieces)>1) {
                    $page_id = $_POST[$pieces[0]];
                    if ($page_id=='0') {
                        $link_pages[] = [
                            "key" => $footer_link_vars[$j],
                            "url" => get_permalink($new_post_id)
                        ];    
                    }
                    else if ($page_id!='-1') {
                        $link_pages[] = [
                            "key" => $footer_link_vars[$j],
                            "url" => get_permalink($page_id)
                        ];
                    }
                }    
            }

            update_page_footer_links($wpdb, $site_id, $new_post_id, $link_pages);
        }        
        wp_redirect(home_url('wp-admin/post.php?post='.$new_post_id.'&action=elementor'));
    }
}

if(isset($_POST['IMPORT_MULTI_PAGE']))
{    
    if(isset($_POST['sids'])){
        global $wpdb;

        $SUPER_ADMIN_SITE_ID = 1;
        $sids = $_POST['sids'];
        $titles = isset($_POST['titles']) ? $_POST['titles'] : [];
        $tags = isset($_POST['tags']) ? $_POST['tags'] : [];
        $redirect_urls = isset($_POST['redirect_urls']) ? $_POST['redirect_urls'] : [];
        $styles = isset($_POST['styles']) ? $_POST['styles'] : [];
        $headlines = isset($_POST['headlines']) ? $_POST['headlines'] : [];
        $triallengths = isset($_POST['triallengths']) ? $_POST['triallengths'] : [];
        $offer_values = isset($_POST['offer_values']) ? $_POST['offer_values'] : [];

        $user_id = get_current_user_id();
        $imported_page_ids = [];
        
        generate_popups_widgets($site_id);
        for ($i=0; $i<count($sids); $i++):

            $title = stripslashes($titles[$i]);
            $tag = stripslashes($tags[$i]);
            $redirect_url = stripslashes($redirect_urls[$i]);
            $style = stripslashes($styles[$i]);
            $headline = stripslashes($headlines[$i]);
            $triallength = stripslashes($triallengths[$i]);
            $offer_value = stripslashes($offer_values[$i]);
            
            $imported_page_ids[strval($sids[$i])] = import_page($site_id, $sids[$i], $title, $user_id, $tag, $redirect_url, 
                $style, $headline, $triallength, $offer_value );
        endfor;

        // Prepare the Footer Links while importing page
        switch_to_blog($site_id);
        for ($i = 0; $i < count($sids); $i++) {    
            // Footer Link Variables will have this name 39_footlink_vars[]
            $footer_link_vars = isset($_POST[$sids[$i]."_footerlink_vars"]) ? $_POST[$sids[$i]."_footerlink_vars"] : [];
            if (count($footer_link_vars) > 0) {
                $link_pages = [];
        
                for ($j=0; $j<count($footer_link_vars); $j++) {
                    // 39_FOOTERLINK_1#Home
                    $pieces = explode("#", $footer_link_vars[$j]);
                    if (count($pieces)>1) {
                        $page_id = $_POST[$sids[$i]."_".$pieces[0]];
                        if ($page_id!='-1') {
                            if (substr($page_id, 0, 1) === 's') {
                                // it means it is spark Page id
                                $page_id = substr($page_id, 1, strlen($page_id)-1);
                                $link_pages[] = [
                                    "key" => $footer_link_vars[$j],
                                    "url" => get_permalink($imported_page_ids[$page_id])
                                ];
                                
                            }
                            else if ($page_id=='0') {
                                $link_pages[] = [
                                    "key" => $footer_link_vars[$j],
                                    "url" => get_permalink($imported_page_ids[$sids[$i]])
                                ];    
                            }
                            else {
                                $link_pages[] = [
                                    "key" => $footer_link_vars[$j],
                                    "url" => get_permalink($page_id)
                                ];
                            }
                        }
                    }    
                }
                update_page_footer_links($wpdb, $site_id, $imported_page_ids[strval($sids[$i])], $link_pages);
            }
        }

        //Switch Back to Current Site and Pages List 
        wp_redirect(home_url('wp-admin/edit.php?post_type=page'));
        
    }
}

