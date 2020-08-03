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
        $r = $wpdb->query(
            $wpdb->prepare(
                "UPDATE ". $wpdb->base_prefix. "cellable_settings SET value = %s where id = %d;",
                $value, $_POST['id']
            ) 
        );
        
        header('Location: ' . $_SERVER['HTTP_REFERER']);
    }
}

if(isset($_POST['CELLABLE_ORDER_UPDATE']))
{   
    if(isset($_POST['id'])){       
        $status_id = $_POST['status_id'];
        $r = $wpdb->query(
            $wpdb->prepare(
                "UPDATE ". $wpdb->base_prefix. "cellable_orders SET order_status_id = %d where id = %d;",
                $status_id, $_POST['id']
            ) 
        );
        header('Location: ' . $_SERVER['HTTP_REFERER']);
    }
}

if(isset($_POST['CELLABLE_BRAND_UPDATE']))
{   
    if(isset($_POST['id'])){       
        $name = stripslashes($_POST['name']);
        $r = $wpdb->query(
            $wpdb->prepare(
                "UPDATE ". $wpdb->base_prefix. "cellable_phones SET name = %s where id = %d;",
                $name, $_POST['id']
            ) 
        );
        header('Location: ' . $_SERVER['HTTP_REFERER']);
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

