<?php
/*

Plugin Name: Platform.ly Official
Description: Platform.ly plugin is the easiest way to setup your optins and pages that your created with Platform.ly. After building your optin or page with our interactive WYSIWYG builders, you can set them up to show on your site with just a couple of clicks.
Version: 1.14
Author: Platform.ly
Author URI: https://www.platform.ly/

 */

if(!defined('ABSPATH'))
    exit; // Exit if accessed directly

define("PLATFORMLY_PING_SLUG", "platformly_ping_for_update_page");
define("PLATFORMLY_URL", "https://pageserver.platform.ly");

define('PLATFORMLY_PLUGIN_VERSION', '1.14');

include plugin_dir_path(__FILE__) . '/inc/ply.functions.php';

add_action('init', 'ply_update_plugin', 5);
add_action('init', 'ply_register_post_types', 10);

function ply_register_post_types(){
//global $wp_taxonomies;

    register_post_type('ply_page', array(
        'labels' => array(
            'name' => 'PLY pages'
        ),
        'public'             => true,
		'publicly_queryable' => true,
		'show_ui'            => false,
		'show_in_menu'       => false,
		'query_var'          => false,
		'rewrite'            => true,
		'capability_type'    => 'page',
		'has_archive'        => false,
		'hierarchical'       => false,
		'menu_position'      => null,
		'supports'           => false,
		'taxonomies'         => array('category')
    ));
}

/**
 * Remove the slug from published post permalinks for our custom post types.
 */
add_filter('post_type_link', function($post_link, $post){
	$post_types = 'ply_page';
	if($post->post_type === $post_types && 'publish' === $post->post_status){
	    if(strpos($post_link, '/archives/'.$post->post_type.'/')){
	        $post_link = str_replace('/archives/'.$post->post_type.'/', '/', $post_link);
	    }else{
            $post_link = str_replace('/'.$post->post_type.'/', '/', $post_link);
        }
	}
	return $post_link;
}, 10, 3);

function ply_create_menus(){

    add_menu_page('Platform.ly', 'Platform.ly', 'manage_options', 'ply', 'ply_settings', plugins_url('img/logo.png', __FILE__));
    add_submenu_page('ply', 'Settings', 'Settings', 'manage_options', 'ply', 'ply_settings', null);
    add_submenu_page('ply', 'Events', 'Events', 'manage_options', 'ply_events', 'ply_events', null);
    add_submenu_page('ply', 'Pages', 'Pages', 'manage_options', 'ply_pages', 'ply_pages', null);
    add_submenu_page('ply', 'Optins', 'Optin Forms', 'manage_options', 'ply_optins', 'ply_optins_page', null);
}

add_action('admin_menu', 'ply_create_menus');

add_action('rest_api_init', 'ply_init_rest_route');

add_action('admin_init', 'platform_ly_admin_init');

// Platform.ly for WooCommerce hooks
add_action('platformly_wc_project_changed', 'platformly_wc_project_changed', 10, 2);

function ply_init_rest_route(){
    register_rest_route('platform-wp-plugin/v1', '/platformly_ping_for_update_page/(?P<page>\d+)', array(
        'methods' => 'GET',
        'callback' => 'ply_rest_update_page_html',
        'args' => array(
            'page' => array(
                'default' => null
            )
        ),
        'permission_callback' => '__return_true'
    ));
}

function ply_settings(){
    wp_enqueue_script('ply_optins_script', plugin_dir_url(__FILE__)."js/settings.js", array(),1);
    include plugin_dir_path(__FILE__) . 'inc/pages/ply.settings.php';
}

function ply_optins_page(){
    wp_enqueue_style('bootstrap_styles', plugin_dir_url(__FILE__)."css/bootstrap.min.css");
    wp_enqueue_style('bootstrap_theme_styles', plugin_dir_url(__FILE__)."css/bootstrap-theme.min.css");
    wp_enqueue_style('select2', plugin_dir_url(__FILE__)."css/select2.min.css");
    wp_enqueue_script('select2', plugin_dir_url(__FILE__)."js/select2.min.js");
    wp_enqueue_script('ply_optins_script', plugin_dir_url(__FILE__)."js/optins.js", array(),1);
    include plugin_dir_path(__FILE__) . 'inc/pages/ply.optins.page.php';
}

function ply_pages(){
    wp_enqueue_style('bootstrap_styles', plugin_dir_url(__FILE__)."css/bootstrap.min.css");
    wp_enqueue_style('bootstrap_theme_styles', plugin_dir_url(__FILE__)."css/bootstrap-theme.min.css");
    wp_enqueue_script('ply_pages_script', plugin_dir_url(__FILE__)."js/pages.js", array(),1);
    include plugin_dir_path(__FILE__) . 'inc/pages/ply.pages.php';
}

function ply_events(){
    wp_enqueue_style('bootstrap_styles', plugin_dir_url(__FILE__)."css/bootstrap.min.css");
    wp_enqueue_style('bootstrap_theme_styles', plugin_dir_url(__FILE__)."css/bootstrap-theme.min.css");
    wp_enqueue_script('bootstrap_script', plugin_dir_url(__FILE__)."js/bootstrap.min.js");
    include plugin_dir_path(__FILE__) . 'inc/pages/ply.events.php';
    wp_enqueue_script('ply_events_script', plugin_dir_url(__FILE__)."js/events.js", array(),1);
}

function ply_uninstall(){
    delete_option('ply_plugin_key');
    delete_option('ply_plugin_cid');
    delete_option('ply_project_code_active');
    global $wpdb;
    //remove optin_forms table
    $table_name = $wpdb->prefix . "ply_optins";
    $sql = "DROP TABLE " . $table_name;
    $wpdb->query($sql);
    $table_name = $wpdb->prefix . "ply_pages";
    $sql = "DROP TABLE " . $table_name;
    $wpdb->query($sql);
    $table_name = $wpdb->prefix."ply_project_code";
    $sql = "DROP TABLE ".$table_name;
    $wpdb->query($sql);
    //remove ply page from wp_posts table
    $plyPages = $wpdb->get_results($wpdb->prepare("SELECT id FROM {$wpdb->posts} WHERE post_type = %s", array('ply_page')));
    foreach($plyPages as $plyPage){
        wp_delete_post($plyPage->id, true);
    }
}

register_uninstall_hook(__FILE__, 'ply_uninstall');

function ply_activate(){
    global $wpdb;
    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    $charset_collate = '';
    if($wpdb->has_cap('collation')){
        if(!empty($wpdb->charset))
            $charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";
        if(!empty($wpdb->collate))
            $charset_collate .= " COLLATE $wpdb->collate";
    };
    $table_name = $wpdb->prefix . "ply_optins";
    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,       
            ply_cid int NOT NULL,
            ply_optin_name VARCHAR(255) NULL,
            ply_optin_id int NOT NULL UNIQUE,
            ply_optin_pid int NULL,
            ply_optin_options text NULL,
            ply_optin_stats text NULL,
            ply_optin_except_page int NULL,
            ply_optin_specific_page int NULL,
            PRIMARY KEY (id)
        ) {$charset_collate};";
    dbDelta($sql);
    
    $table_name = $wpdb->prefix . "ply_pages";
    $val = $wpdb->get_results("SHOW TABLES LIKE '{$table_name}'");
    if(!empty($val)){
        //IT EXISTS! Fresh update every active page html
        $sql = "SELECT * FROM " . $table_name;
        $results = $wpdb->get_results($sql);
        foreach($results as $result){
            ply_update_page_html($result->ply_page_id);
        }
    }else{
        //I can't find it... Create table
        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
		id mediumint(9) NOT NULL AUTO_INCREMENT,               
                ply_cid int NOT NULL,
		ply_page_name VARCHAR(255) NULL,
		ply_page_id int NOT NULL UNIQUE,
		ply_page_pid int NULL,
		ply_page_options text NULL,
		ply_page_html longtext NULL,
		ply_page_stats text NULL,
                ply_page_type VARCHAR(255) NULL,
                ply_page_slug VARCHAR(255) NULL,
                ply_page_status VARCHAR(20) NULL,
		PRIMARY KEY  (id)
            ) {$charset_collate};";
        dbDelta($sql);
    }
    $table_name = $wpdb->prefix."ply_project_code";
    $sql = "CREATE TABLE IF NOT EXISTS {$table_name} (
        `id` int NOT NULL AUTO_INCREMENT,
        `ply_project_id` int NOT NULL,
        `ply_cid` int NOT NULL,
        `ply_project_code` text NULL,
        PRIMARY KEY  (`id`),
        UNIQUE KEY `ply_project_id` (`ply_project_id`,`ply_cid`),
        KEY `ply_cid` (`ply_cid`)
    ) {$charset_collate};";
    dbDelta($sql);

    update_option('ply_plugin_version', PLATFORMLY_PLUGIN_VERSION);

    if(platform_ly_check_ply_wc_plugin_is_activated() && get_option('ply_plugin_cid')){
        ply_remove_project_code();
    }
}

register_activation_hook(__FILE__, 'ply_activate');

//ajax requests
add_action('wp_ajax_ply_load_optins', 'ply_load_optins_callback');
add_action('wp_ajax_ply_load_pages', 'ply_load_pages_callback');
add_action('wp_ajax_ply_get_tracking_links', 'ply_get_tracking_links_callback');
add_action('wp_ajax_ply_load_events', 'ply_load_events_callback');
add_action('wp_ajax_ply_get_tracking_links_details', 'ply_get_tracking_links_details_callback');
add_action('wp_ajax_ply_save_project_code', 'ply_save_project_code_callback');
add_action('wp_ajax_ply_check_project_code', 'ply_check_project_code_callback');
add_action('wp_ajax_ply_remove_project_code', 'ply_remove_project_code_callback');
add_action('wp_ajax_ply_project_code_include', 'ply_project_code_include_callback');
add_action('wp_ajax_ply_get_projects', 'ply_get_projects_callback');

function ply_load_optins_callback(){
    check_ajax_referer('ply_load_data');
    $projectId = intval($_POST['projectId']);
    $get_optins = wp_remote_get(PLATFORMLY_URL . "/plugin/plugin.actions.php?plugin_key=" . ply_get_plugin_key() . "&action=listOptins&projectId=" . $projectId);
    $get_optins = wp_remote_retrieve_body($get_optins);
    echo $get_optins;
    wp_die(); // this is required to terminate immediately and return a proper response
}

function ply_load_pages_callback(){
    check_ajax_referer('ply_load_data');
    $projectId = intval($_POST['projectId']);
    $get_pages = wp_remote_get(PLATFORMLY_URL . "/plugin/plugin.actions.php?plugin_key=" . ply_get_plugin_key() . "&action=listPages&projectId=" . $projectId);
    $get_pages = wp_remote_retrieve_body($get_pages);
    echo $get_pages;
    wp_die(); // this is required to terminate immediately and return a proper response
}

function ply_get_tracking_links_callback(){
    check_ajax_referer('ply_load_data');
    $result = wp_remote_get(PLATFORMLY_URL . "/plugin/plugin.actions.php?plugin_key=" . ply_get_plugin_key() . "&action=getTrackingLinks");
    $result = wp_remote_retrieve_body($result);
    echo $result;
    wp_die();
}

function ply_load_events_callback(){
    check_ajax_referer('ply_load_data');
    $projectId = intval($_POST['projectId']);
    $result = wp_remote_get(PLATFORMLY_URL . "/plugin/plugin.actions.php?plugin_key=" . ply_get_plugin_key() . "&action=getEvents&projectId=" . $projectId);
    $result = wp_remote_retrieve_body($result);
    echo $result;
    wp_die();
}

function ply_get_tracking_links_details_callback(){
    check_ajax_referer('ply_load_data');
    $linkId = intval($_POST['id']);
    $result = wp_remote_get(PLATFORMLY_URL . "/plugin/plugin.actions.php?plugin_key=" . ply_get_plugin_key() . "&action=getDetailsTrackingLinks&linkId=" . $linkId);
    $result = wp_remote_retrieve_body($result);
    echo $result;
    wp_die();
}

function ply_save_project_code_callback(){
    check_ajax_referer('ply_save_project_code');
    $projectId = intval($_POST['projectId']);
    $result = wp_remote_get(PLATFORMLY_URL."/plugin/plugin.actions.php?plugin_key=".ply_get_plugin_key()."&action=getProjectCode&projectId=".$projectId);
    $result = wp_remote_retrieve_body($result);
    $data = json_decode($result, true);
    if($data['status'] != 'success'){
        echo $result;
    }else{
        if(isset($data['projectCode']) && !empty($data['projectCode'])){
            $setProject = ply_set_project_code($projectId, $data['projectCode']);
            if($setProject === true){
                do_action('platform_ly_project_changed', $projectId);
                echo json_encode(array('status' => 'success', 'projectName' => wp_strip_all_tags($data['projectName'])));
            }else{
                echo json_encode(array('status' => 'An error occured.'));
            }
        }else{
            echo json_encode(array('status' => 'An error occured.'));
        }
    }
    wp_die();
}
function ply_check_project_code_callback(){
    check_ajax_referer('ply_load_data');
    $projectId = intval($_POST['projectId']);
    $projectCode = ply_get_project_code($projectId);
    if(empty($projectCode)){
        $response = false;
    }else{
        $response = true;
    }
    echo $response;
    wp_die();
}

function ply_remove_project_code_callback(){
    check_ajax_referer('ply_save_project_code');
    ply_remove_project_code();
    wp_die();
}

function ply_project_code_include_callback(){
    check_ajax_referer('ply_save_project_code');
    $includeCode = isset($_POST['includeCode']) && !empty($_POST['includeCode']) ? true : false;
    ply_project_code_include($includeCode);
    wp_die();
}

function ply_get_projects_callback(){
    check_ajax_referer('ply_load_data');
    $result = wp_remote_get(PLATFORMLY_URL."/plugin/plugin.actions.php?plugin_key=".ply_get_plugin_key()."&action=listProjects");
    $result = wp_remote_retrieve_body($result);
    echo $result;
    wp_die();
}

add_action('wp_footer', 'ply_load_scripts');

function ply_load_scripts(){
    global $post;
    $optins = ply_get_active_optins();
    if(count($optins) > 0){
        $optinsString = '';
        foreach($optins as $optin){
            $options = json_decode($optin->ply_optin_options);
            $optinsBody = array(
                "oid" => $optin->ply_optin_id,
                "pageType" => $post->post_type,
                "opos" => $options->optinPosition,
                "otype" => $options->optinType,
                "ott" => $options->optinTriggerType,
                "otv" => $options->optinTriggerValue,
                "oca" => $options->optinClickAway,
                "obb" => $options->optinBlurBack,
                "oloc" => $options->optinLoc,
                "pageId" => $post->ID,
                "selectedPages" => $options->optinPages,
            );
            $optinsBodyEncoded = json_encode($optinsBody);
            $optinsString .= "'{$optinsBodyEncoded}',";
        }
        wp_enqueue_script('ply_optins_load_script', PLATFORMLY_URL."/plugin/plyoptin.js?v=1.3");
        wp_add_inline_script('ply_optins_load_script', "var plyoptins = [" . rtrim($optinsString, ',') . "];var PLY = {'plugin_key':'" . ply_get_plugin_key() . "'};", 'before');
    }
    return true;
}

function ply_get_current_url_path($withGetParams = false){
    $uri = trim($_SERVER['REQUEST_URI'], "/");
    $site_url = get_site_url();
    $site_url = str_replace(array("http://", "https://", "www."), "", $site_url);
    $site_url = trim($site_url, "/");
    $site_url_parts = explode("/", $site_url);
    foreach($site_url_parts as $part){
        $uri = preg_replace("@^$part/@", "", $uri);
    }
    if(!$withGetParams && strpos($uri, "?") > -1){
        $uri = substr($uri, 0, strpos($uri, "?"));
    }
    return $uri;
}

function ply_manage_pages(){
	$path = \sanitize_text_field( \wp_unslash( $_SERVER['REQUEST_URI'] ) );
	// If it's not a wp-sitemap request, nothing to do.
	if ( \substr( $path, 0, 11 ) === '/wp-sitemap' ) {
		return;
	}

    $welcomePage = ply_get_page_by_options('welcome');
    if(!empty($welcomePage) && !isset($_COOKIE[$welcomePage['ply_page_slug']])){
        $DAYS_IN_SECONDS = 45 * 24 * 60 * 60;
        setcookie($welcomePage['ply_page_slug'], 1, (time() + $DAYS_IN_SECONDS));
        $ply_page = ply_get_page_by_id($welcomePage['ply_page_id']);
        echo $ply_page->ply_page_html;
        die();
    }else{
        $slug = ply_get_current_url_path();
        //if slug is from PLY, update the page html
        /* if(strpos($slug, PLATFORMLY_PING_SLUG) !== FALSE) {
          $pageId = str_replace(PLATFORMLY_PING_SLUG, "", $slug);
          $pageId = intval($pageId);
          $res = ply_update_page_html($pageId);
          die($res);
          } */
        /*if(!empty($welcomePage) && $slug == $welcomePage['ply_page_slug']){
            $page['ply_page_html'] = $welcomePage['ply_page_html'];
        } else
        */if(is_front_page()){
            $page = ply_get_page_by_options('homepage');
        } else{
            $page = ply_get_page_by_options('normal', $slug);
        }
        if((!isset($page) || (isset($page) && empty($page))) && is_404()){
            $page = ply_get_page_by_options('404');
        }
        if(isset($page) && !empty($page)){
            status_header(200);
            echo $page['ply_page_html'];
            die();
        }
    }
}

add_action('template_redirect', 'ply_manage_pages');
add_action('admin_print_footer_scripts', 'add_platform_ly_link');

function add_platform_ly_link(){
	global $typenow;
	if(!in_array($typenow, array('post', 'page'))){
		return;
	}

	if(!wp_script_is('quicktags')){
        return;
    }

    include plugin_dir_path(__FILE__) . 'inc/ply.btn.php';
}

function add_platform_ly_link_plugin($plugin_array){
    $plugin_array['platform_ly_link'] = plugin_dir_url(__FILE__) . "js/editor_btn.js";
    return $plugin_array;
}

function register_platform_ly_link_button($buttons){
    array_push($buttons, "platform_ly_link");
    return $buttons;
}

function add_mce_platform_ly_link(){
    if(!current_user_can('edit_posts') && !current_user_can('edit_pages')){
        return;
    }

	global $typenow;
    if(!in_array($typenow, array('post', 'page'))){
        return;
    }

    wp_enqueue_script('jquery-ui-dialog');
    wp_enqueue_style('wp-jquery-ui-dialog');
    wp_enqueue_script('ply-add-link', plugin_dir_url(__FILE__) . 'js/ply_add_link_functions.js', array(),1);
    if(get_user_option('rich_editing') == 'true'){
        add_filter("mce_external_plugins", "add_platform_ly_link_plugin");
        add_filter('mce_buttons', 'register_platform_ly_link_button');
    }
}

function platform_ly_editor_css(){
    wp_enqueue_style('ply_styles', plugin_dir_url(__FILE__) . "css/ply_styles.css");
    wp_enqueue_style('ex_first', plugin_dir_url(__FILE__) . "css/editor_btn.css");
}

function add_mce_platform_ly_link_to_elementor_editor(){
    platform_ly_editor_css();
    add_mce_platform_ly_link();
}

/**
 * Check if the user is currently editing using Elementor
 * @return boolean
 */
function platformLyIsEditInElementor() {
    $path = parse_url($_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'], PHP_URL_PATH);
    if (strpos($path, 'post.php') !== false && isset($_GET['action']) && $_GET['action'] === 'elementor') {
        return true;
    } else {
        return false;
    }
}

if (platformLyisEditInElementor()) {
    add_action('elementor/editor/before_enqueue_scripts', 'add_mce_platform_ly_link_to_elementor_editor');
} else {
    add_action('admin_enqueue_scripts', 'platform_ly_editor_css');
    add_action('admin_head', 'add_mce_platform_ly_link');
}

function platform_ly_set_project_code(){
    $projectCodeInclude = get_option('ply_project_code_active');
    if(!empty($projectCodeInclude)){
        $projectCode = ply_get_project_code();
        if(isset($projectCode['ply_project_code'])){
            echo wp_specialchars_decode($projectCode['ply_project_code'], ENT_QUOTES);
        }
    }
    /*foreach($projectCode as $code){
        echo $code['ply_project_code'];
    }*/
}
add_action('wp_head', 'platform_ly_set_project_code');

function platform_ly_test_new_editor(){
    wp_enqueue_script('ply-add-link-new', plugin_dir_url(__FILE__).'js/ply_editor_btn_functions.js', array('wp-blocks', 'wp-editor', 'wp-components', 'wp-i18n', 'wp-element', 'wp-rich-text', 'wp-format-library'));
}

add_action('enqueue_block_editor_assets', 'platform_ly_test_new_editor');

function platform_ly_admin_init(){
    $plyProjectCode = ply_get_project_code();
    if(platform_ly_check_ply_wc_plugin_is_activated() && (empty($plyProjectCode) || empty(get_option('ply_project_code_active')))){
        $projectId = platform_ly_get_ply_wc_project_id();
        $projectCode = platform_ly_get_ply_wc_project_code();
        if($projectId && $projectCode){
            ply_set_project_code($projectId, $projectCode);
            ply_project_code_include(true);
            platfrom_ly_project_code_include_from_ply_wc(true);
        }else{
            platfrom_ly_project_code_include_from_ply_wc(false);
        }
    }else if(!platform_ly_check_ply_wc_plugin_is_activated() && platfrom_ly_get_ply_wc_project_code_active()){
        platfrom_ly_project_code_include_from_ply_wc(false);
    }
}

function platformly_wc_project_changed($projectId, $projectCode){
    if(get_option('ply_plugin_cid')){
        ply_set_project_code($projectId, $projectCode);
    }
}

function ply_update_plugin(){
    global $wpdb;
    $version = get_option('ply_plugin_version');
    if(empty($version)){
        $sql = 'ALTER TABLE `wp_ply_pages` CHANGE `ply_page_html` `ply_page_html` LONGTEXT';
        $wpdb->query($sql);
        update_option('ply_plugin_version', PLATFORMLY_PLUGIN_VERSION);
    }
    $versionData = explode('.',$version);
    $major = empty($versionData[0]) ? 0 : $versionData[0];
    $minor = empty($versionData[1]) ? 0 : $versionData[1];
    // update for 1.10 version
    if($major < 1 || ($major == 1 && $minor < 10)){
        $pages = $wpdb->get_results($wpdb->prepare('SELECT ply_page_slug, ply_page_name FROM '.$wpdb->prefix.'ply_pages WHERE ply_page_type = %s', array('normal')));
        foreach($pages as $page){
            $plyPage = $wpdb->get_row($wpdb->prepare("SELECT id FROM {$wpdb->posts} WHERE post_type = %s AND post_name = %s", array('ply_page', $page->ply_page_slug)));
            if(!$plyPage){
                platformly_create_wp_page($page->ply_page_slug, $page->ply_page_name);
            }
        }
        update_option('ply_plugin_version', PLATFORMLY_PLUGIN_VERSION);
    }
}