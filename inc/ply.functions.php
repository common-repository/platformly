<?php

if(!defined('ABSPATH'))
    exit; // Exit if accessed directly

function ply_check_access($access = 0){
    $block = false;
    $check = wp_remote_get(PLATFORMLY_URL . "/plugin/plugin.check.key.php?plugin_key=" . ply_get_plugin_key());
    $check = wp_remote_retrieve_body($check);

    if($check){ //check if request is completed
        $check = json_decode($check, true);
        if($check['status'] == 'not_found'){ //if public key is not found block the plugin
            $block = true;
            update_option('ply_plugin_cid', '');
        }else if(!platformly_wc_check_user($check['id'])){
            $block = true;
            $check['status'] = 'cid_does_not_match_wc_cid';
            update_option('ply_plugin_cid', '');
        }else{
            $ply_plugin_cid = get_option('ply_plugin_cid');
            if($ply_plugin_cid != $check['id']){
                update_option('ply_plugin_cid', $check['id']);
            }
        }
    } else
        $block = true;

    if($access == 1)
        $block = false;
    if($block){
        //include  plugin_dir_path( __FILE__ ) . '/pages/ply.activate.php';
        //echo "<p class='ply_errorMsg'>Please go to settings and enter your API Key first.</p>";
        wp_die("<p class='ply_errorMsg'>Please go to settings and enter your API Key first.</p>");
    } else
        return $check;
}

function ply_get_plugin_key(){
    $plugin_key = get_option('ply_plugin_key');
    if($plugin_key == false)
        return '';
    else
        return $plugin_key;
}

function ply_update_plugin_key(){
    $code = sanitize_text_field($_POST['plugin_key']);
    $res = preg_match('/^[a-zA-Z0-9]{32}$/', $code);
    if($res){
        $check = wp_remote_get(PLATFORMLY_URL . "/plugin/plugin.check.key.php?plugin_key=" . $code);
        $check = wp_remote_retrieve_body($check);
        if($check){
            $check = json_decode($check, true);
            if($check['status'] != 'not_found'){
                update_option('ply_plugin_key', $code);
                return '1';
            }
        }
    }
    return 'The API key you added is not correct.';
}

function ply_update_optin_info(){
    $ply_plugin_cid = get_option('ply_plugin_cid');
    $projectId = isset($_POST['formProjectId']) ? (int)$_POST['formProjectId'] : 0;
    $optinId = isset($_POST['formOptinId']) ? (int)$_POST['formOptinId'] : 0;
    $optinName = isset($_POST['formOptinName']) ? wp_strip_all_tags($_POST['formOptinName'], true) : '';
    if(!empty($ply_plugin_cid) && !empty($optinId) && !empty($optinName)){
        if(isset($_POST['formOptinPosition']) && in_array($_POST['formOptinPosition'], array('center', 'topleft', 'topright', 'bottomleft', 'bottomright'))){
            $optinPosition = $_POST['formOptinPosition'];
        }else{
            $optinPosition = "center";
        }
        if(isset($_POST['formOptinType']) && in_array($_POST['formOptinType'], array('pop', 'slide', 'fade'))){
            $optinType = $_POST['formOptinType'];
        }else{
            $optinType = "pop";
        }
        if(isset($_POST['formOptinWhere']) && in_array($_POST['formOptinWhere'], array('all', 'posts', 'pages', 'except', 'specific'))){
            $optinWhere = $_POST['formOptinWhere'];
        }else{
            $optinWhere = "all";
        }
        if(isset($_POST['formOptinTriggerType']) && in_array($_POST['formOptinTriggerType'], array('time', 'scroll', 'exit'))){
            $optinTriggerType = $_POST['formOptinTriggerType'];
        }else{
            $optinTriggerType = "time";
        }
        if(isset($_POST['formOptinTriggerValue'])){
            $optinTriggerValue = (int)$_POST['formOptinTriggerValue'];
            if ($optinTriggerValue < 0) {
                return "Trigger value must not be negative.";
            }
        }else{
            $optinTriggerValue = 0;
        }
        if(isset($_POST['formOptinClickAway']) && $_POST['formOptinClickAway'] == 'true'){
            $optinClickAway = "true";
        }else{
            $optinClickAway = "false";
        }
        if(isset($_POST['formOptinBlurBack']) && $_POST['formOptinBlurBack'] == 'true'){
            $optinBlurBack = "true";
        }else{
            $optinBlurBack = "false";
        }
        if(isset($_POST['formOptinWherePages']) && ($optinWhere == 'except' || $optinWhere == 'specific') && !empty($_POST['formOptinWherePages'])){
            $wpPages = explode(',', $_POST['formOptinWherePages']);
            $optinPages = array();
            foreach($wpPages as $wpPage){
                $optinPages[] = (int)$wpPage;
            }
        }else{
            $optinPages = 0;
        }
        
        $plyOptinInfo = array(
            "optinPosition" => $optinPosition,
            "optinType" => $optinType,
            "optinTriggerType" => $optinTriggerType,
            "optinTriggerValue" => $optinTriggerValue,
            "optinLoc" => $optinWhere,
            "optinClickAway" => $optinClickAway,
            "optinBlurBack" => $optinBlurBack,
            "optinPages" => $optinPages
        );
        $plyOptinInfo = json_encode($plyOptinInfo);

        global $wpdb;

        if($wpdb->query($wpdb->prepare("REPLACE INTO " . $wpdb->prefix . "ply_optins(`ply_optin_name`, `ply_optin_pid`, `ply_optin_id`, `ply_optin_options`, `ply_cid`) 
                    VALUES(%s, %d, %d, %s, %d)", array($optinName, $projectId, $optinId, $plyOptinInfo, $ply_plugin_cid)
                ))){
            return 1;
        } else{
            return "An error occured.";
        }
    }else{
        return "An error occured.";
    }
}

function ply_remove_optin_info(){
    global $wpdb;
    $ply_plugin_cid = get_option('ply_plugin_cid');
    if(isset($_POST['removeOptin']) && !empty($_POST['removeOptin'])){
        if($wpdb->query($wpdb->prepare("DELETE FROM " . $wpdb->prefix . "ply_optins WHERE ply_optin_id=%d AND ply_cid=%d LIMIT 1", array((int)$_POST['removeOptin'], $ply_plugin_cid)))){
            return 1;
        } else{
            return "An error occured.";
        }
    }else{
        return "An error occured.";
    }
}

function ply_get_optin_by_id($id){
    global $wpdb;
    $ply_plugin_cid = get_option('ply_plugin_cid');

    $sql = $wpdb->prepare("SELECT * FROM " . $wpdb->prefix . "ply_optins WHERE ply_optin_id=%d AND ply_cid=%d", array($id, $ply_plugin_cid));
    $results = $wpdb->get_results($sql);

    if(count($results)){
        return $results[0];
    } else{
        return false;
    }
}

function ply_get_optin_info(){
    $optinForm = get_option('ply_optin_info');

    if($optinForm == false){
        return '';
    } else{
        return $optinForm;
    }
}

function ply_get_active_optins($idsonly = false){
    global $wpdb;
    $ply_plugin_cid = get_option('ply_plugin_cid');
    $sql = $wpdb->prepare("SELECT * FROM " . $wpdb->prefix . "ply_optins WHERE ply_cid = %d", array($ply_plugin_cid));
    $results = $wpdb->get_results($sql);

    if($idsonly){
        $ids = array();
        foreach($results as $result)
            $ids[] = $result->ply_optin_id;
        return $ids;
    }

    return $results;
}

function ply_update_page_info(){
    global $wpdb;
    $ply_plugin_cid = get_option('ply_plugin_cid');
    $projectId = isset($_POST['formProjectId']) ? (int)$_POST['formProjectId'] : 0;
    $pageId = isset($_POST['formPageId']) ? (int)$_POST['formPageId'] : 0;
    $pageName = isset($_POST['formPageName']) ? wp_strip_all_tags($_POST['formPageName'], true) : '';
    if(isset($_POST['formPageSlug']) && !empty($_POST['formPageSlug'])){
        if($_POST['formPageSlug'] == '[not applicable]'){
            $pageSlug = '[not-applicable]';
        }else{
            $pageSlug = sanitize_title($_POST['formPageSlug']);
        }
    }else{
        $pageSlug = '';
    }
    if(!empty($ply_plugin_cid) && !empty($pageId) && !empty($pageName) && !empty($pageSlug)){ 
        if(isset($_POST['formPageType']) && in_array($_POST['formPageType'], array('normal', 'homepage', 'welcome', '404'))){
            $pageType = $_POST['formPageType'];
        }else{
            $pageType = 'normal';
        }
        $plyPageInfo = array(
            "pageType" => $pageType,
            "pageSlug" => $pageSlug
        );
        $pingUpdateServices = 0;
        if($pageType === 'normal'){
            $plyPageInfo['ping_update_services'] = !empty($_POST['formPagePingUpdateServices']) ? 1 : 0;
            $pingUpdateServices = $plyPageInfo['ping_update_services'];
        }
        $plyPageInfo = json_encode($plyPageInfo);

        update_option("ply_page_info", $plyPageInfo);

        //if($pageType == "homepage" || $pageType == "404" || $pageType == "welcome") {
        $sql = $wpdb->prepare("SELECT * FROM " . $wpdb->prefix . "ply_pages WHERE ply_cid = %d", array($ply_plugin_cid));
        $results = $wpdb->get_results($sql);
        foreach($results as $result){
            $pageOptions = json_decode($result->ply_page_options);
            if((in_array($pageType, array("homepage", "404", "welcome")) && $pageOptions->pageType == $pageType) || ($pageType == 'normal' && $pageOptions->pageSlug == $pageSlug)){
                $resultUnsub = ply_unsubscribe_page_html($result->ply_page_id);
                if($resultUnsub){
                    $wpdb->query($wpdb->prepare("DELETE FROM " . $wpdb->prefix . "ply_pages WHERE id=%d LIMIT 1", array($result->id)));
                    platformly_delete_wp_page($result->ply_page_slug);
                }
            }
        }
        //}

        $sql = $wpdb->prepare("SELECT * FROM " . $wpdb->prefix . "ply_pages WHERE ply_page_id = %d AND ply_cid = %d", array($pageId, $ply_plugin_cid));

        $results = $wpdb->get_results($sql);
        if(count($results)){
            //if it's an update don't register the page for updates, it's already been done
            $wpdb->query($wpdb->prepare("UPDATE {$wpdb->prefix}ply_pages SET `ply_page_options` = %s, `ply_page_type` = %s, `ply_page_slug` = %s WHERE ply_page_id = %d AND ply_cid = %d", array($plyPageInfo, $pageType, $pageSlug, $pageId, $ply_plugin_cid)));
            // update page in wp_post table
            if($results[0]->ply_page_type == $pageType){
                $wpdb->query($wpdb->prepare("UPDATE {$wpdb->posts} SET `post_name` = %s, `guid` = %s WHERE post_type = %d AND post_name = %s", array($pageSlug, get_site_url().'/'.$pageSlug, 'ply_page', $results[0]->ply_page_slug)));
            }elseif($results[0]->ply_page_type === 'normal'){
                platformly_delete_wp_page($results[0]->ply_page_slug);
            }elseif($pageType === 'normal'){
                platformly_create_wp_page($pageSlug, $results[0]->ply_page_name);
            }
            if($pingUpdateServices){
                generic_ping();
            }
            return 1;
        } else{
            //if it's fresh insert register to ply that it should send updates
            $get_pages = wp_remote_get(PLATFORMLY_URL . "/plugin/plugin.actions.php?plugin_key=" . ply_get_plugin_key() . "&action=subscribePage&pageId=" . $pageId . "&pingback=" . get_platformly_pin());
            $get_pages = wp_remote_retrieve_body($get_pages);
            $get_pages = json_decode($get_pages);
            if(isset($get_pages->success) && $get_pages->success){
                $pageHTML = $get_pages->html;
                if($wpdb->query($wpdb->prepare("INSERT INTO " . $wpdb->prefix . "ply_pages(`ply_page_name`, `ply_page_pid`, `ply_page_id`, `ply_page_options`, `ply_page_html`, `ply_cid`, ply_page_type, ply_page_slug, ply_page_status) VALUES (%s, %d, %d, %s, %s, %d, %s, %s, %s)", array($pageName, $projectId, $pageId, $plyPageInfo, $pageHTML, $ply_plugin_cid, $pageType, $pageSlug, 'active')))){
                    if($pingUpdateServices){
                        generic_ping();
                    }
                    if($pageType === 'normal'){
                        platformly_create_wp_page($pageSlug, $pageName);
                    }
                    return 1;
                } else{
                    return "An error occured.";
                }
            } else{
                return "Platform.ly seems to be down at the moment. Please try again in couple of minutes.";
            }
        }
    }else{
        return "An error occured.";
    }
}

function ply_remove_page_info(){
    global $wpdb;
    $ply_plugin_cid = get_option('ply_plugin_cid');
    
    if(isset($_POST['removePage']) && !empty($_POST['removePage'])){
        $sql = $wpdb->prepare("SELECT * FROM " . $wpdb->prefix . "ply_pages WHERE ply_page_id=%d AND ply_cid=%d LIMIT 1", array((int)$_POST['removePage'], $ply_plugin_cid));
        $ply_pages = $wpdb->get_results($sql);

        if(count($ply_pages)){
            $ply_page = $ply_pages[0];
            $pageId = $ply_page->ply_page_id;

            $result = ply_unsubscribe_page_html($pageId);

            if($result){
                if($wpdb->query($wpdb->prepare("DELETE FROM " . $wpdb->prefix . "ply_pages WHERE ply_page_id=%d AND ply_cid=%d LIMIT 1", array((int)$_POST['removePage'], $ply_plugin_cid)))){
                    platformly_delete_wp_page($ply_page->ply_page_slug);
                    return 1;
                } else{
                    return "An error occured.";
                }
            }

            return "An error occured.";
        }
    }
}

function ply_get_page_by_id($id){
    global $wpdb;
    $ply_plugin_cid = get_option('ply_plugin_cid');

    $sql = $wpdb->prepare("SELECT * FROM " . $wpdb->prefix . "ply_pages WHERE ply_page_id=%d AND ply_cid=%d", array($id, $ply_plugin_cid));
    $results = $wpdb->get_results($sql);

    if(count($results)){
        return $results[0];
    } else{
        return false;
    }
}

function ply_get_page_info(){
    $page = get_option('ply_page_info');

    if($page == false)
        return '';
    else
        return $page;
}

function ply_get_active_pages($idsonly = false){
    global $wpdb;
    $ply_plugin_cid = get_option('ply_plugin_cid');

    $sql = $wpdb->prepare("SELECT * FROM " . $wpdb->prefix . "ply_pages WHERE ply_cid = %d", array($ply_plugin_cid));
    $results = $wpdb->get_results($sql);

    if($idsonly){
        $ids = array();
        foreach($results as $result)
            $ids[] = $result->ply_page_id;
        return $ids;
    }

    return $results;
}

function ply_rest_update_page_html(WP_REST_Request $request){
    if(isset($request['page']) && !empty($request['page'])){
        return ply_update_page_html($request['page']);
    } else{
        return array("success" => false, "msg" => "An error occurred.");
    }
}

function ply_update_page_html($pageId){
    $get_pages = wp_remote_get(PLATFORMLY_URL . "/plugin/plugin.actions.php?plugin_key=" . ply_get_plugin_key() . "&action=getPageHTML&pageId=" . $pageId);
    $get_pages = wp_remote_retrieve_body($get_pages);
    $get_pages = json_decode($get_pages);
    $ply_plugin_cid = get_option('ply_plugin_cid');
    if(isset($get_pages->success) && $get_pages->success){
        //everything ok, proceed with adding the page to database
        $pageHTML = $get_pages->html;
        $pageStatus = $get_pages->status;
    } else{
        return json_encode(array("success" => false, "msg" => "Platform.ly seems to be down at the moment. Please try again in couple of minutes."));
    }
    global $wpdb;

    if($wpdb->query($wpdb->prepare("UPDATE " . $wpdb->prefix . "ply_pages SET `ply_page_html` = %s, ply_page_status = %s  WHERE ply_page_id = %d AND ply_cid = %d LIMIT 1", array($pageHTML, $pageStatus, $pageId, $ply_plugin_cid)))){

        wp_remote_get(PLATFORMLY_URL . "/plugin/plugin.actions.php?plugin_key=" . ply_get_plugin_key() . "&action=confirmPageHTML&pageId=" . $pageId);
        return json_encode(array("success" => true));
    } else
        return json_encode(array("success" => false, "msg" => "An error occured."));
}

function ply_unsubscribe_page_html($pageId){
    $result = wp_remote_get(PLATFORMLY_URL . "/plugin/plugin.actions.php?plugin_key=" . ply_get_plugin_key() . "&action=unsubscribePage&pageId=" . $pageId . "&pingback=" . get_platformly_pin());
    $result = wp_remote_retrieve_body($result);
    $result = json_decode($result);

    if(isset($result->success) && $result->success)
        return true;

    return false;
}

function ply_curPageURL(){
    $pageURL = 'http';
    if($_SERVER["HTTPS"] == "on"){
        $pageURL .= "s";
    }
    $pageURL .= "://";
    $pageURL .= $_SERVER["SERVER_NAME"] . $_SERVER["REQUEST_URI"];

    return $pageURL;
}

function ply_get_page_by_options($type, $slug = null){
    global $wpdb;
    $ply_plugin_cid = get_option('ply_plugin_cid');
    if(!empty($ply_plugin_cid)){
        if($slug !== null){
            $slug = rtrim($slug, '/');
            $sql = $wpdb->prepare("SELECT * FROM " . $wpdb->prefix . "ply_pages WHERE ply_cid = %d AND ply_page_type = %s AND ply_page_slug = %s AND ply_page_status = 'active'", array($ply_plugin_cid, $type, $slug));
        } else{
            $sql = $wpdb->prepare("SELECT * FROM " . $wpdb->prefix . "ply_pages WHERE ply_cid = %d AND ply_page_type = %s AND  ply_page_status = 'active'", array($ply_plugin_cid, $type));
        }
        return $wpdb->get_row($sql, ARRAY_A);
    } else{
        return false;
    }
}

function get_platformly_pin(){
    return get_rest_url(null, 'platform-wp-plugin/v1/platformly_ping_for_update_page', 'rest');
}

function ply_set_project_code($projectId, $projectCode){
    global $wpdb;
    $ply_plugin_cid = get_option('ply_plugin_cid');
    if($ply_plugin_cid){
        $wpdb->query($wpdb->prepare("DELETE FROM {$wpdb->prefix}ply_project_code WHERE ply_cid = %d", array($ply_plugin_cid)));
        $query = $wpdb->query($wpdb->prepare("INSERT INTO {$wpdb->prefix}ply_project_code(ply_project_id, ply_cid, ply_project_code) VALUES (%d, %d, %s)", array(absint($projectId), absint($ply_plugin_cid), esc_js($projectCode))));
        /*$sql = $wpdb->prepare("SELECT id FROM {$wpdb->prefix}ply_project_code WHERE ply_project_id = %d AND ply_cid = %d", array($projectId, $ply_plugin_cid));
        $existsProjectCode = $wpdb->get_row($sql, ARRAY_A);
        if(!empty($existsProjectCode)){
            $query = $wpdb->query($wpdb->prepare("UPDATE {$wpdb->prefix}ply_project_code SET ply_project_code = %s WHERE ply_project_id = %d AND ply_cid = %d", array($projectCode, $projectId, $ply_plugin_cid)));
        }else{
            $query = $wpdb->query($wpdb->prepare("INSERT INTO {$wpdb->prefix}ply_project_code(ply_project_id, ply_cid, ply_project_code) VALUES (%d, %d, %s)", array($projectId, $ply_plugin_cid, $projectCode)));
        }*/
        if($query){
            return true;
        }else{
            return false;
        }
    }
    return false;
}

function ply_get_project_code($projectId = null){
    global $wpdb;
    $ply_plugin_cid = get_option('ply_plugin_cid');
    if($projectId){
        $sql = $wpdb->prepare("SELECT id, ply_project_code, ply_project_id FROM {$wpdb->prefix}ply_project_code WHERE ply_project_id = %d AND ply_cid = %d", array($projectId, $ply_plugin_cid));
        return $wpdb->get_row($sql, ARRAY_A);
    }else{
        $sql = $wpdb->prepare("SELECT id, ply_project_code, ply_project_id  FROM {$wpdb->prefix}ply_project_code WHERE ply_cid = %d", array($ply_plugin_cid));
        //return $wpdb->get_results($sql, ARRAY_A);
        return $wpdb->get_row($sql, ARRAY_A);
    }
}

function ply_remove_project_code(){
    global $wpdb;
    $ply_plugin_cid = get_option('ply_plugin_cid');
    $wpdb->query($wpdb->prepare("DELETE FROM {$wpdb->prefix}ply_project_code WHERE ply_cid = %d", array($ply_plugin_cid)));
}

function ply_project_code_include($v){
    if($v === true){
        update_option('ply_project_code_active', 1);
    }else{
        update_option('ply_project_code_active', 0);
    }
}

function platform_ly_check_ply_wc_plugin_is_activated(){
    return is_plugin_active('platformly-for-woocommerce/platformly-for-woocommerce.php');
}

function platform_ly_get_ply_wc_project_id(){
    $platformly_wc_options = get_option('platformly-woocommerce');
    $platformly_wc_project_id = isset($platformly_wc_options['platformly-wc-project-id']) && !empty($platformly_wc_options['platformly-wc-project-id']) ? $platformly_wc_options['platformly-wc-project-id'] : null;
    return $platformly_wc_project_id;
}

function platform_ly_get_ply_wc_project_code(){
    $platformly_wc_options = get_option('platformly-woocommerce');
    $platformly_wc_project_code = isset($platformly_wc_options['platformly-wc-project-code']) && !empty($platformly_wc_options['platformly-wc-project-code']) ? $platformly_wc_options['platformly-wc-project-code'] : null;
    return $platformly_wc_project_code;
}

function platfrom_ly_project_code_include_from_ply_wc($v){
    if($v === true){
        update_option('ply_wc_project_code_active', 1);
    }else{
        update_option('ply_wc_project_code_active', 0);
    }
}

function platfrom_ly_get_ply_wc_project_code_active(){
    $wc_project_code_is_active = get_option('ply_wc_project_code_active');
    return empty($wc_project_code_is_active) ? false : true;
    
}

function platformly_wc_check_user($userId){
    $platformlyWcUserId = get_option('platformly_wc_cid');
    if($platformlyWcUserId && $platformlyWcUserId != $userId){
        return false;
    }
    return true;
}

function platformly_create_wp_page($pageSlug, $pageName){
    wp_insert_post(array(
        'post_status'    => 'publish',
        'post_type'      => 'ply_page',
        'post_author'    => 1,
        'post_name'      => $pageSlug,
        'post_title'     => $pageName,
        'post_content'   => '',
        'comment_status' => 'closed',
        'guid' => get_site_url().'/'.$pageSlug
    ));
}

function platformly_delete_wp_page($pageSlug){
    global $wpdb;
    $plyPage = $wpdb->get_row($wpdb->prepare("SELECT id FROM {$wpdb->posts} WHERE post_type = %s AND post_name = %s", array('ply_page', $pageSlug)));
    if($plyPage){
        wp_delete_post($plyPage->id, true);
    }
}