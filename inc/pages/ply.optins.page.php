<?php
if(!defined('ABSPATH'))
    exit; // Exit if accessed directly

include plugin_dir_path(__FILE__) . 'input.php';

global $wpdb;

if(isset($_POST['saveOptin'])){
    check_admin_referer('ply_options_save');
    $res = ply_update_optin_info();
    if($res == '1')
        $msg = '<div id="ply_closeNotification" class="ply_successMsg">Your changes have been succesfully saved.</div>';
    else
        $msg = '<div id="ply_closeNotification" class="ply_errorMsg">' . $res . '</div>';
};

if(isset($_POST['removeOptin'])){
    check_admin_referer('ply_options_remove');
    $res = ply_remove_optin_info();
    if($res == '1')
        $msg = '<div id="ply_closeNotification" class="ply_successMsg">Your changes have been succesfully saved.</div>';
    else
        $msg = '<div id="ply_closeNotification" class="ply_errorMsg">' . $res . '</div>';
}

$optinForms = ply_get_active_optins();
$optinIds = array();
foreach($optinForms as $optinForm){
    $optinIds[] = $optinForm->ply_optin_id;
}
if(empty($optinIds)){
    wp_add_inline_script('ply_optins_script', "var activeOptinIds = [];", 'before' );
}else{
    $jsOptionIds = "[".implode(",", $optinIds)."]";
    wp_add_inline_script('ply_optins_script', "var activeOptinIds = {$jsOptionIds};", 'before' );
}
$editOptin = false;
if(isset($_POST['editOptin'])){
    check_admin_referer('ply_options_edit');
    $editOptin = ply_get_optin_by_id((int)$_POST['editOptin']);
}

$postList = get_posts(array('post_status' => 'publish,future,private'));
$pageList = get_pages(array('post_status' => 'publish,future,private'));
?>

<?php if(isset($msg)){
    echo $msg; 
    wp_add_inline_script('ply_optins_script', "jQuery(document).ready(function () {
        setTimeout(function () {
            jQuery('#ply_closeNotification').fadeOut();
        }, 5000);
    });", 'before' );
} ?>

<div class="wrap">
    <div class="page-header">
        <img src="<?= plugins_url('', dirname(__FILE__)) ?>/../img/trackingcode.png" width="30" height="30" />
        <?php
        if($editOptin != false)
            $header = "Edit optin form: " . $editOptin->ply_optin_name;
        else
            $header = "Add new optin form from Platformly";
        ?>
        <h1><?= $header ?></h1>
    </div>
    <?php
    //get_projects
    if($editOptin == false){
        $get_projects = wp_remote_get(PLATFORMLY_URL . "/plugin/plugin.actions.php?plugin_key=" . ply_get_plugin_key() . "&action=listProjects");
        $get_projects = wp_remote_retrieve_body($get_projects);

        $projects = json_decode($get_projects, true);

        $project_select = "<select id='ply_project' onchange='loadOptins(this.value)'>";
        $project_select .= "<option value='-1'>Please choose...</option>";

        $project_select .= "<option value='0'>All projects</option>";
        foreach($projects as $pid => $pname){
            $project_select .= "<option value='$pid'>$pname</option>";
        }
        $project_select .= "</select>";
    }
    ?>
    <div class="main-container">
<?php if($editOptin == false){ ?>
            <div class="row">
                <div class="col-md-12">
                    <h4>Please select project</h4><hr>
                </div>
                <div class="col-md-12">
                    <?= $project_select ?>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <h4 style='display: inline-block; margin-right: 5px'>Please select optin</h4><a target="_blank" href="<?php echo $user['main_url'] ?>/?page=lead_capture.forms" class="btn btn-default">View Optins</a><hr style="margin-top: 5px !important">
                </div>
                <div class="col-md-12" id="tdOptins" data-nonce="<?php echo wp_create_nonce("ply_load_data"); ?>">
                    <label>Select project first</label>
                </div>
            </div>
<?php } ?>
        <div class="row">
            <div class="col-md-12">
                <h4>Where to show the optin</h4><hr>
            </div>
            <div class="col-md-12">
                <?php
                $loc = "all";
                $optinExceptPage = array();
                $optionSpecificPage = 0;
                if($editOptin != false){
                    $options = json_decode($editOptin->ply_optin_options, true);
                    $loc = $options['optinLoc'];
                    if(empty($options['optinPages'])){
                        $optinExceptPage = array();
                    }else{
                        $optinExceptPage = $options['optinPages'];
                    }
                }
                ?>
                <input type="radio" name="optinwhere" id="optinWhereAll" value="all" <?php echo ($loc == 'all') ? 'checked' : '' ?>>
                <label for='optinWhereAll' class='ply_label_optin_form'> Everywhere</label>
                <input type="radio" name="optinwhere" id="optinWherePages" value="pages" <?php echo ($loc == 'pages') ? 'checked' : '' ?>>
                <label for='optinWherePages' class='ply_label_optin_form'> On Pages</label>
                <input type="radio" name="optinwhere" id="optinWherePosts" value="posts" <?php echo ($loc == 'posts') ? 'checked' : '' ?>>
                <label for='optinWherePosts' class='ply_label_optin_form'> On Posts</label>
                <input type="radio" name="optinwhere" id="optinWhereExcept" value="except" <?php echo ($loc == 'except') ? 'checked' : '' ?>>
                <label for='optinWhereExcept' class='ply_label_optin_form'> Everywhere except</label>
                <input type="radio" name="optinwhere" id="optinWhereSpecific" value="specific" <?php echo ($loc == 'specific') ? 'checked' : '' ?>>
                <label for='optinWhereSpecific' class='ply_label_optin_form'> Show only on a specific page/post</label>
            </div>
        </div>
        <div id='plyBlockListWpPages' class="row" <?php echo in_array($loc, array('except', 'specific')) ? '' : 'style="display:none;"' ?>>
            <div class="col-md-12">
                <h4>Please select pages/posts</h4><hr>
            </div>
            <div class="col-md-12">
                <select id='optinWherePage' multiple name='optinWherePage[]'>
                    <?php foreach($postList as $post): ?>
                        <option value="<?php echo $post->ID ?>" <?php echo in_array($post->ID, $optinExceptPage) ? 'selected' : '' ?>><?php echo $post->post_title ?></option>
                    <?php endforeach; ?>
                    <?php foreach($pageList as $page): ?>
                        <option value="<?php echo $page->ID ?>" <?php echo in_array($page->ID, $optinExceptPage) ? 'selected' : '' ?>><?php echo $page->post_title ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <h4>Please select position</h4><hr>
            </div>
            <div class="col-md-12"><?php
                $optinPosition = '';
                if($editOptin != false){
                    $options = json_decode($editOptin->ply_optin_options, true);
                    $optinPosition = $options['optinPosition'];
                }
                ?>
                <div id="plugin-pos">
                    <a id="center" href="javascript:" onclick="jQuery('#plugin-pos a').removeClass('selected'); jQuery(this).addClass('selected');" class="<?= ($optinPosition == 'center') ? 'selected' : '' ?>">
                        <img src="<?= plugins_url('', dirname(__FILE__)) ?>/../img/center-position.png" width="100" height="100"/>
                        <p>Center</p>
                    </a>
                    <a id="topleft" href="javascript:" onclick="jQuery('#plugin-pos a').removeClass('selected'); jQuery(this).addClass('selected');" class="<?= ($optinPosition == 'topleft') ? 'selected' : '' ?>">
                        <img src="<?= plugins_url('', dirname(__FILE__)) ?>/../img/top-left-position.png" width="100" height="100"/>
                        <p>Top Left</p>
                    </a>
                    <a id="topright" href="javascript:" onclick="jQuery('#plugin-pos a').removeClass('selected'); jQuery(this).addClass('selected');" class="<?= ($optinPosition == 'topright') ? 'selected' : '' ?>">
                        <img src="<?= plugins_url('', dirname(__FILE__)) ?>/../img/top-right-position.png" width="100" height="100"/>
                        <p>Top Right</p>
                    </a>
                    <a id="bottomleft" href="javascript:" onclick="jQuery('#plugin-pos a').removeClass('selected'); jQuery(this).addClass('selected');" class="<?= ($optinPosition == 'bottomleft') ? 'selected' : '' ?>">						
                        <img src="<?= plugins_url('', dirname(__FILE__)) ?>/../img/bottom-left-position.png" width="100" height="100"/>
                        <p>Bottom Left</p>
                    </a>
                    <a id="bottomright" href="javascript:" onclick="jQuery('#plugin-pos a').removeClass('selected'); jQuery(this).addClass('selected');" class="<?= ($optinPosition == 'bottomright') ? 'selected' : '' ?>">
                        <img src="<?= plugins_url('', dirname(__FILE__)) ?>/../img/bottom-right-position.png" width="100" height="100"/>
                        <p>Bottom Right</p>
                    </a>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <h4>Please select how the optin will be showed</h4><hr>
            </div>
            <div class="col-md-12">
                <?php
                $optinType = '';
                if($editOptin != false){
                    $options = json_decode($editOptin->ply_optin_options, true);
                    $optinType = $options['optinType'];
                }
                ?>
                <div id="plugin-type">
                    <a id="pop" href="javascript:" onclick="jQuery('#plugin-type a').removeClass('selected'); jQuery(this).addClass('selected');" class="<?= ($optinType == 'pop') ? 'selected' : '' ?>">
                        <img src="<?= plugins_url('', dirname(__FILE__)) ?>/../img/pop-animation.gif" width="100" height="100"/>
                        <p>Pop</p>
                    </a>
                    <a id="slide" href="javascript:" onclick="jQuery('#plugin-type a').removeClass('selected'); jQuery(this).addClass('selected');" class="<?= ($optinType == 'slide') ? 'selected' : '' ?>">
                        <img src="<?= plugins_url('', dirname(__FILE__)) ?>/../img/slide-animation.gif" width="100" height="100"/>
                        <p>Slide</p>
                    </a>
                    <a id="fade" href="javascript:" onclick="jQuery('#plugin-type a').removeClass('selected'); jQuery(this).addClass('selected');" class="<?= ($optinType == 'fade') ? 'selected' : '' ?>">
                        <img src="<?= plugins_url('', dirname(__FILE__)) ?>/../img/fade-animation.gif" width="100" height="100"/>
                        <p>Fade</p>
                    </a>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <h4>Please select trigger</h4><hr>
            </div>
            <div class="col-md-12">
                <?php
                $triggerType = '';
                if($editOptin != false){
                    $options = json_decode($editOptin->ply_optin_options, true);
                    $triggerType = $options['optinTriggerType'];
                }
                ?>
                <select id="ply_trigger" onchange="showTriggerOptions(this.value, 0)">
                    <option value="-1">Please select...</option>
                    <option value="time" <?= ($triggerType == 'time') ? 'selected' : '' ?>>Show after X seconds</option>
                    <option value="scroll" <?= ($triggerType == 'scroll') ? 'selected' : '' ?>>Show after X% scrolled</option>
                    <option value="exit" <?= ($triggerType == 'exit') ? 'selected' : '' ?>>Show on exit intent</option>
                </select>
            </div>
            <div class="col-md-12">
                <div id="thTrigger" class="trigger"></div>
                <div id="tdTrigger" class="trigger"></div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <label for='ply_clickAway'><h4>Close optin if user clicks away?</h4></label>
                <?php
                //by default true
                $clickAway = true;
                if($editOptin != false){
                    $options = json_decode($editOptin->ply_optin_options, true);
                    $clickAway = $options['optinClickAway'];
                }
                $clickAway = filter_var($clickAway, FILTER_VALIDATE_BOOLEAN);
                ?>
                <input type="checkbox" id="ply_clickAway" <?= ($clickAway) ? "checked" : "" ?> >
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <label for='ply_blurBack'><h4>Blur the page while the optin is visible?</h4></label>
                <?php
                //by default true
                $blurBack = true;
                if($editOptin != false){
                    $options = json_decode($editOptin->ply_optin_options, true);
                    $blurBack = $options['optinBlurBack'];
                }
                $blurBack = filter_var($blurBack, FILTER_VALIDATE_BOOLEAN);
                ?>
                <input type="checkbox" id="ply_blurBack" <?= ($blurBack) ? "checked" : "" ?> >
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <?php if($editOptin != false){ ?> 
                    <input type="hidden" id="edit_ply_optin_name" value="<?= $editOptin->ply_optin_name ?>" />
                    <input type="hidden" id="edit_ply_optin_id" value="<?= $editOptin->ply_optin_id ?>" />
                    <input type="hidden" id="edit_ply_optin_pid" value="<?= $editOptin->ply_optin_pid ?>" />
                    <button onclick="updateOptin(true)" class="btn btn-primary">Update</button>
                    <button onclick="location.reload();" class="button button-secondary">Cancel</button>
                <?php } else{ ?>
                    <button onclick="updateOptin(false)" class="btn btn-primary">Save</button>
                <?php } ?>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <h4 class="table-name">Active Optins</h4>
                <?php /* <a target="_blank" href="<?php echo $user['main_url'] ?>/?page=lead_capture.forms" class="btn btn-default">View Optin</a>
                <a class="btn btn-default" title="Refresh">&#8635;</a> */ ?>
                <hr>
                <table class="table">
                    <thead> 
                        <tr> 
                            <th>#</th> 
                            <th>Optin name</th> 
                            <th>Position</th> 
                            <th>Type</th> 
                            <th>Trigger Type</th> 
                            <th>Trigger Value</th> 
                            <th>Click Away?</th> 
                            <th>Blur Background?</th>
                            <th>Actions</th> 
                        </tr> 
                    </thead>
                    <tbody>
                        <?php if(count($optinForms) < 1){ ?>
                            <tr>
                                <td colspan="8">No optins active yet.</td>
                            </tr>
                        <?php
                        } else{
                            $i = 1;
                            foreach($optinForms as $optinForm){
                                $optinOptions = json_decode($optinForm->ply_optin_options);

                                $bcolor = "";
                                if($editOptin != false){
                                    if($editOptin->ply_optin_id == $optinForm->ply_optin_id)
                                        $bcolor = " style='background-color: #fffdb6' ";
                                }
                                ?>
                                <tr <?= $bcolor ?>> 
                                    <td><?= $i ?></td>
                                    <td><?= $optinForm->ply_optin_name ?></td>
                                    <td><?= $optinOptions->optinPosition ?></td>
                                    <td><?= $optinOptions->optinType ?></td>
                                    <td><?= $optinOptions->optinTriggerType ?></td>
                                    <td><?= $optinOptions->optinTriggerValue ?></td>
                                    <td><?= ($optinOptions->optinClickAway == "false") ? "No" : "Yes" ?></td>
                                    <td><?= ($optinOptions->optinBlurBack == "false") ? "No" : "Yes" ?></td>
                                    <td>
                                        <button class="btn btn-default" style="margin-right:10px" onclick="editOptin(<?= $optinForm->ply_optin_id ?>)">Edit</button>
                                        <button class="btn btn-danger" onclick="removeOptin(<?= $optinForm->ply_optin_id ?>)">Remove</button>
                                    </td>
                                <?php $i++;
                            }
                        }?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php
        if(count($optinForms) < 1){
            echo "<p>No optins active yet.</p>";
        } else{
            $i = 1;
        } ?>
    </div>
</div>
<?php
if($editOptin != false){
    $options = json_decode($editOptin->ply_optin_options, true);
    $optinTriggerType = $options['optinTriggerType'];
    $optinTriggerValue = $options['optinTriggerValue'];
    wp_add_inline_script('ply_optins_script', "jQuery(document).ready(function() { showTriggerOptions('" . $optinTriggerType . "', " . $optinTriggerValue . "); });", 'before' );    
} else{
    wp_add_inline_script('ply_optins_script', "jQuery(document).ready(function() { loadOptins(-1, -1); });", 'before');
}
?>

<form id="optinSaveForm" style="display:none" method="post" action="">
    <input type="text" name="formProjectId" id="formProjectId" value=""/>
    <input type="text" name="formOptinId" id="formOptinId" value=""/>
    <input type="text" name="formOptinName" id="formOptinName" value=""/>
    <input type="text" name="formOptinPosition" id="formOptinPosition" value=""/>
    <input type="text" name="formOptinType" id="formOptinType" value=""/>
    <input type="text" name="formOptinWhere" id="formOptinWhere" value=""/>
    <input type="text" name="formOptinTriggerType" id="formOptinTriggerType" value=""/>
    <input type="text" name="formOptinTriggerValue" id="formOptinTriggerValue" value=""/>
    <input type="text" name="formOptinClickAway" id="formOptinClickAway" value="" />
    <input type="text" name="formOptinBlurBack" id="formOptinBlurBack" value="" />
    <input type="hidden" name='formOptinWherePages' id='formOptinWherePages' value=''/>
    <?php wp_nonce_field('ply_options_save'); ?>
    <input type="hidden" name="saveOptin" value="1" />
</form>

<form id="optinRemoveForm" style="display:none" method="post" action="">
    <?php wp_nonce_field('ply_options_remove'); ?>
    <input type="hidden" name="removeOptin" id="removeOptin" value="" />
</form>

<form id="optinEditForm" style="display:none" method="post" action="">
    <?php wp_nonce_field('ply_options_edit'); ?>
    <input type="hidden" name="editOptin" id="editOptin" value="" />
</form>