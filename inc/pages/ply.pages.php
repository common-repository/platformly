<?php
if(!defined('ABSPATH'))
    exit; // Exit if accessed directly

include plugin_dir_path(__FILE__) . 'input.php';

if(empty(get_option('permalink_structure'))){
    echo "<p class='ply_errorMsg'>The Plaform.ly Page feature will not be available if you choose in Permalink setting the option 'Plain'</p>";
    die();
}

global $wpdb;

if(isset($_POST['savePage'])){
    check_admin_referer('ply_pages_save');
    $res = ply_update_page_info();
    if($res == '1'){
        $msg = '<div id="ply_closeNotification" class="ply_successMsg">Your changes have been succesfully saved.</div>';
    }else{
        $msg = '<div id="ply_closeNotification" class="ply_errorMsg">' . $res . '</div>';
    }
};

if(isset($_POST['removePage'])){
    check_admin_referer('ply_pages_remove');
    $res = ply_remove_page_info();
    if($res == '1'){
        $msg = '<div id="ply_closeNotification" class="ply_successMsg">Your changes have been succesfully saved.</div>';
    }else{
        $msg = '<div id="ply_closeNotification" class="ply_errorMsg">' . $res . '</div>';
    }
}

$pages = ply_get_active_pages();
$pageIds = array();
foreach($pages as $page){
    $pageIds[] = $page->ply_page_id;
}
if(empty($pageIds)){
    wp_add_inline_script('ply_pages_script', "var activePageIds = [];", 'before' );
}else{
    $jsPagesIds = "[".implode(",", $pageIds)."]";
    wp_add_inline_script('ply_pages_script', "var activePageIds = {$jsPagesIds};", 'before' );
}
$editPage = false;
if(isset($_POST['editPage'])){
    check_admin_referer('ply_pages_edit');
    $editPage = ply_get_page_by_id((int)$_POST['editPage']);
}
?>

<?php if(isset($msg)){
    echo $msg; 
    wp_add_inline_script('ply_pages_script', "jQuery(document).ready(function () {
        setTimeout(function () {
            jQuery('#ply_closeNotification').fadeOut();
        }, 5000);
    });", 'before' ); 
} ?>

<div class="wrap">
    <div class="page-header">
        <img src="<?= plugins_url('', dirname(__FILE__)) ?>/../img/trackingcode.png" width="30" height="30" />
        <?php
        if($editPage != false)
            $header = "Edit page: " . $editPage->ply_page_name;
        else
            $header = "Add new page from Platformly";
        ?>
        <h1><?= $header ?></h1>
    </div>

    <?php
    //get_projects
    if($editPage == false){
        $get_projects = wp_remote_get(PLATFORMLY_URL . "/plugin/plugin.actions.php?plugin_key=" . ply_get_plugin_key() . "&action=listProjects");
        $get_projects = wp_remote_retrieve_body($get_projects);

        $projects = json_decode($get_projects, true);

        $project_select = "<select id='ply_project' onchange='loadPages(this.value)'>";
        $project_select .= "<option value='-1'>Please choose...</option>";

        $project_select .= "<option value='0'>All projects</option>";
        foreach($projects as $pid => $pname){
            $project_select .= "<option value='$pid'>$pname</option>";
        }
        $project_select .= "</select>";
    }
    ?>
    <div class="main-container">

<?php if($editPage == false){ ?>
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
                    <h4 style='display: inline-block; margin-right: 5px'>Please select page</h4><a target="_blank" href="<?php echo $user['main_url'] ?>/?page=lead_capture.pages" class="btn btn-default">View Pages</a><hr style="margin-top: 5px !important">
                </div>
                <div class="col-md-12" id="tdPages" data-nonce="<?php echo wp_create_nonce("ply_load_data"); ?>">
                    <label>Select project first</label>
                </div>
            </div>
<?php } ?>

        <div class="row">
            <div class="col-md-12">
                <h4>Please select type</h4><hr>
            </div>
            <div class="col-md-12"><?php
                $pageType = 'normal';
                if($editPage != false){
                    $options = json_decode($editPage->ply_page_options, true);
                    $pageType = $options['pageType'];
                }
                ?>
                <div id="plugin-type">
                    <a id="normal" href="javascript:" onclick="choosePageType(jQuery(this));" class="<?= ($pageType == 'normal') ? 'selected' : '' ?>">
                        <img src="<?= plugins_url('', dirname(__FILE__)) ?>/../img/normal-page-type.png" width="100" height="100"/>
                        <p>Normal</p>
                    </a>
                    <a id="homepage" href="javascript:" onclick="choosePageType(jQuery(this));" class="<?= ($pageType == 'homepage') ? 'selected' : '' ?>">
                        <img src="<?= plugins_url('', dirname(__FILE__)) ?>/../img/home-page-type.png" width="100" height="100"/>
                        <p>Homepage</p>
                    </a>
                    <?php /* <a id="welcome" href="javascript:" onclick="choosePageType(jQuery(this));" class="<?= ($pageType == 'welcome') ? 'selected' : '' ?>">
                        <img src="<?= plugins_url('', dirname(__FILE__)) ?>/../img/welcome-page-type.png" width="100" height="100"/>
                        <p>Welcome</p>
                    </a> */ ?>
                    <a id="404" href="javascript:" onclick="choosePageType(jQuery(this));" class="<?= ($pageType == '404') ? 'selected' : '' ?>">
                        <img src="<?= plugins_url('', dirname(__FILE__)) ?>/../img/error-page-type.png" width="100" height="100"/>
                        <p>404</p>
                    </a>
                </div>
            </div>
            <div class="col-md-12">
                <div id="pageSlugContainer" class="page-text"></div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
            <?php if($editPage != false){ ?> 
                    <input type="hidden" id="edit_ply_page_name" value="<?= $editPage->ply_page_name ?>" />
                    <input type="hidden" id="edit_ply_page_id" value="<?= $editPage->ply_page_id ?>" />
                    <input type="hidden" id="edit_ply_page_pid" value="<?= $editPage->ply_page_pid ?>" />
                    <input type="hidden" id="edit_ply_ping_update_services" value="<?= !empty($options['ping_update_services']) ? 1 : 0 ?>" />
                    <button onclick="updatePage(true)" class="btn btn-primary">Update</button>
                    <button onclick="location.reload();" class="button button-secondary">Cancel</button>
            <?php } else{ ?>
                    <button onclick="updatePage(false)" class="btn btn-primary">Save</button>
            <?php } ?>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <h4 class="table-name">Active Pages</h4>
                
                <hr>
                <table class="table">
                    <thead> 
                        <tr> 
                            <th>#</th> 
                            <th>Page name</th>
                            <th>Type</th>
                            <th>Slug</th>
                            <th>Status</th>
                            <th>Actions</th> 
                        </tr> 
                    </thead>
                    <tbody>
                        <?php if(count($pages) < 1){ ?>
                            <tr>
                                <td colspan="8">No pages active yet.</td>
                            </tr>
                        <?php
                        } else{
                            $i = 1;
                            foreach($pages as $page){
                                $pageOptions = json_decode($page->ply_page_options);

                                $bcolor = "";
                                if($editPage != false){
                                    if($editPage->ply_page_id == $page->ply_page_id)
                                        $bcolor = " style='background-color: #fffdb6' ";
                                }
                                ?>
                                <tr <?= $bcolor ?>> 
                                    <td><?= $i ?></td>
                                    <td><?= $page->ply_page_name ?></td>
                                    <td><?= $pageOptions->pageType ?></td>
                                    <td><?= $pageOptions->pageSlug ?>
                                    <?php if($pageOptions->pageSlug !== '[not-applicable]'){ ?>
                                        <a class="btn btn-info pull-right" target="_blank" href="<?= get_site_url() ?>/<?= $pageOptions->pageSlug ?>" title="Opens in new tab">Visit</a>
                                        <input style='display:none' value="<?= get_site_url() ?>/<?= $pageOptions->pageSlug ?>"/>
                                        <button class="btn pull-right" style="margin-right: 5px" onclick="jQuery(this).prev('input').show(); jQuery(this).prev('input').select(); document.execCommand('copy'); jQuery(this).prev('input').hide(); jQuery(this).replaceWith('<label class=\'pull-right\' style=\'margin-right:30px\'>Copied</label>')">Copy link</button>
                                <?php } ?>
                                    </td>
                                    <td>
                                    <?php echo $page->ply_page_status ?>
                                    </td>
                                    <td>
                                        <button class="btn btn-default" style="margin-right:10px" onclick="editPage(<?= $page->ply_page_id ?>)">Edit</button>
                                        <button class="btn btn-danger" onclick="removePage(<?= $page->ply_page_id ?>)">Remove</button>
                                    </td>
                                <?php $i++;
                            }
                        }?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php
if($editPage == false){
    wp_add_inline_script('ply_pages_script', "jQuery(document).ready(function() { loadPages(-1, -1); });");
}

if($editPage != false){
    $options = json_decode($editPage->ply_page_options, true);
    $pageType = $options['pageType'];
    $pageSlug = $options['pageSlug'];
    if($pageType == 'normal' || $pageType == 'welcome'){
        wp_add_inline_script('ply_pages_script', "jQuery(document).ready(function() { choosePageType('', '" . $pageSlug . "', true); });");
    }else{
        wp_add_inline_script('ply_pages_script', "jQuery(document).ready(function() { choosePageType('', '" . $pageSlug . "', false); });");
    }
}else{
    wp_add_inline_script('ply_pages_script', "jQuery(document).ready(function() { loadPages(-1, -1); choosePageType(jQuery(\"#plugin-type a#" . $pageType . "\").first()); });");
}
?>

<form id="pageSaveForm" style="display:none" method="post" action="">
    <input type="text" name="formProjectId" id="formProjectId" value=""/>
    <input type="text" name="formPageId" id="formPageId" value=""/>
    <input type="text" name="formPageName" id="formPageName" value=""/>
    <input type="text" name="formPageType" id="formPageType" value=""/>
    <input type="text" name="formPageSlug" id="formPageSlug" value=""/>
    <input type="text" name="formPagePingUpdateServices" id="formPagePingUpdateServices" value=""/>
    <?php wp_nonce_field('ply_pages_save'); ?>
    <input type="hidden" name="savePage" value="1" />
</form>

<form id="pageRemoveForm" style="display:none" method="post" action="">
    <?php wp_nonce_field('ply_pages_remove'); ?>
    <input type="hidden" name="removePage" id="removePage" value="" />
</form>

<form id="pageEditForm" style="display:none" method="post" action="">
    <?php wp_nonce_field('ply_pages_edit'); ?>
    <input type="hidden" name="editPage" id="editPage" value="" />
</form>