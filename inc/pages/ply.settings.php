<?php
if(!defined('ABSPATH'))
    exit; // Exit if accessed directly

if(isset($_POST['plugin_key'])){
    check_admin_referer('ply_settings');
    $res = ply_update_plugin_key();
    if($res == '1')
        $msg = '<div id="ply_closeNotification" class="ply_successMsg">Your changes have been succesfully saved.</div>';
    else
        $msg = '<div id="ply_closeNotification" class="ply_errorMsg">' . $res . '</div>';
};
$pkey = ply_get_plugin_key();
$access = 1;
$projectCodeBlockClass = '';
include plugin_dir_path(__FILE__) . 'input.php';
$projectCode = array();
$projectCodeInclude = 0;
$projectCodeSetInPlyWoocommerce = platfrom_ly_get_ply_wc_project_code_active();
$plyWcPluginIsActive = platform_ly_check_ply_wc_plugin_is_activated();
$disabled = '';
if(!empty($user) && $user['status'] == 'active'){
    $get_projects = wp_remote_get( PLATFORMLY_URL . "/plugin/plugin.actions.php?plugin_key=".ply_get_plugin_key()."&action=listProjects" );
    $get_projects = wp_remote_retrieve_body($get_projects);
    $projects = json_decode($get_projects, true);
    $projectCodeInclude = get_option('ply_project_code_active');
    $projectCode = ply_get_project_code();
    if($plyWcPluginIsActive){
        $projectCodeBlockClass = 'ply-wc-project-code';
        $disabled = 'disabled';
    }
}
?>
<table width="100%" border="0" cellspacing="0" cellpadding="3">
    <tr>
        <td width="30"><img src="<?= plugins_url('', dirname(__FILE__)) ?>/../img/settings.png" width="30" height="30" /></td>
        <td><h1>Settings</h1></td>
    </tr>
</table>
<?php if(isset($msg)){
    echo $msg;
    wp_add_inline_script('ply_settings_script', "jQuery(document).ready(function () {
        setTimeout(function () {
            jQuery('#ply_closeNotification').fadeOut();
        }, 5000);
    });", 'before' );
} ?>
<div id="ply_content">
<?php if(!empty($user) && $user['status'] == 'active'){ ?>
    <p>Your Plugin Key:</p>
<?php } else{ ?>
    <p>Please Enter Your Plugin Key:</p>
<?php } ?>
    <form id="form1" name="form1" method="post" action="">
        <label for="plugin_key"></label>
        <input type="text" name="plugin_key" id="plugin_key" style="width:500px;" value="<?php echo $pkey ?>" />
        <?php wp_nonce_field( 'ply_settings' ); ?>
        <input type="submit" name="button" id="button" class="button" value="Save" /><br />
        <span style="font-size:12px;"> You will need to add your API Key from your Platform.ly account. <br>You can find the API section if you click on your name in the upper right corner on Platform.ly and then on 'Api Keys'.</span>
        <?php
        if(!empty($pkey) && (empty($user) || $user['status'] == 'not_found')){
            echo "<div class='ply_errorMsg' style='margin-top:1%'>The plugin key you added is not correct.</div>";
            wp_die();
        }else if(!empty($pkey) && (empty($user) || $user['status'] == 'cid_does_not_match_wc_cid')){
            echo "<div class='ply_errorMsg' style='margin-top:1%'>Only one user can be used in  Platform.ly applications.</div>";
            wp_die();
        }else if(empty($pkey)){
            wp_die();
        }
        ?>
    </form>
<?php if($user['status'] == 'active'){ ?>
        <br />
        <table width="100%" border="0" cellspacing="0" cellpadding="3">
            <tr>
                <td width="90" valign="top">
                    <?php
                    $default = plugins_url('', dirname(__FILE__)) . "/../img/profile_img.png";
                    if(isset($user['profile_image']) && strlen($user['profile_image']) > 0){
                        if(!empty($user['use_gravatar'])){
                            $img_url = $user['profile_image']."?d=".urlencode( $default )."&s=90";
                        }else{
                            $img_url = $user['profile_image'];
                        }
                    } else{
                        $size = 80;
                        $img_url = $default;
                    }//var_dump($user);die;
                    ?>
                    <img src="<?= $img_url ?>" width="80" height="80" class="round" style="border:3px #f4f4f4 solid;" title="<?= $user['first_name'] ?>" />
                </td>
                <td valign="top">
                    <h2 style="margin-bottom:0; display: inline-block;margin-top: 5px;">Welcome <?= $user['first_name'] . ' ' . $user['last_name'] ?></h2>
                    <div><strong>Email: </strong><?= $user['email'] ?></div>
                    <form id="btnVisitAccount" method="get" target="_blank" action="<?php echo $user['main_url'] ?>">
                        <input type='hidden' name='page' value='settings.personal_information'/>
                        <input type="submit" class="button" value="Visit your Account" />
                    </form>
                </td>
            </tr>
        </table>
        <br/>
        <div id="plyProjectCodeBlock" class="<?php echo $projectCodeBlockClass ?>">
            <h2 style="margin-bottom: 5px;">Add a Project Code</h2>
            <div style="margin-bottom: 8px;">
                <input id="plyCheckboxSetProjectCode" value='1' <?php echo $disabled ?> type="checkbox" <?php echo !empty($projectCodeInclude) && !empty($projectCode) ? 'checked' : '' ?>/>
                <label for="plyCheckboxSetProjectCode">Click here to include a Platform.ly project code within your blog</label>
            </div>
            <div id='plyProjectCodeSettings' <?php echo !empty($projectCodeInclude) && !empty($projectCode) ? 'style="display:block"' : '' ?>>
                <select id="plyProjectSelect">
                    <option value='-1'>Please choose...</option>
                    <?php foreach($projects as $pid => $pname): ?>
                        <option value="<?php echo $pid ?>" <?php echo isset($projectCode['ply_project_id']) && $projectCode['ply_project_id'] == $pid ? 'selected' : ''  ?>><?php echo $pname ?></option>
                    <?php endforeach; ?>
                </select>
                <input type="hidden" value="<?php echo wp_create_nonce("ply_save_project_code"); ?>" id="projectCodeNonce">
                <input id="btnPlySaveProjectCode" class="button" type="button" value="Save"/>
                <div id="msgPlyGetProgectCodeError"></div>
                <div id='plyLoadingProjectCode'>Loading...</div>
                <div id="msgPlyGetProgectCodeActivated" class='ply_successMsg' <?php echo !isset($projectCode['ply_project_id']) ? 'style="display:none"' : '' ?> >
                    <div>A code from Project '<span><?php echo isset($projectCode['ply_project_id']) ? $projects[$projectCode['ply_project_id']] : '' ?></span>' is being used on this site.  <?php echo $projectCodeSetInPlyWoocommerce ? "<span>This Code is installed from 'Platform.ly for WooCommerce' plugin</span>" : ($plyWcPluginIsActive ? '' : '<a id="plyRemoveProjectCode" href="javascript:;">Remove Project code.</a>') ?></div>
                </div>
            </div>
        </div>
<?php } ?>
</div>