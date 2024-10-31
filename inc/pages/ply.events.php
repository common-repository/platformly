<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
include plugin_dir_path( __FILE__ )  . 'input.php';
$get_projects = wp_remote_get( PLATFORMLY_URL . "/plugin/plugin.actions.php?plugin_key=".ply_get_plugin_key()."&action=listProjects" );
$get_projects = wp_remote_retrieve_body($get_projects);
$projects = json_decode($get_projects, true);
$projectCodeInclude = get_option('ply_project_code_active');
$projectCode = ply_get_project_code();
?>
<?php if(isset($msg)){ 
    echo $msg; 
    wp_add_inline_script('ply_events_script', "jQuery(document).ready(function() {setTimeout(function(){jQuery('#ply_closeNotification').fadeOut();}, 5000);});");
} ?>

<div class="wrap">
    <div class="page-header">
        <img src="<?=plugins_url('', dirname(__FILE__))?>/../img/trackingcode.png" width="30" height="30" />
        <h1>Events</h1>
    </div>
    <div class="main-container">
        <div class="row">
            <div class="col-md-12">
                <h4>Please select project</h4><hr>
            </div>
            <div class="col-md-12">
                <select id='plyProjectSelect'>
                    <option value='-1'>Please choose...</option>
                    <?php foreach($projects as $pid => $pname): ?>
                        <option value="<?php echo $pid ?>"><?php echo $pname ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <h4 class="table-name">Active Events</h4>
                <a target="_blank" href="<?php echo $user['main_url'] ?>/?page=setup.events" class="btn btn-default">View Events</a>
                <a href="javascript:;" id="btnPlyRefreshEvents" class="btn btn-default" title="Refresh">&#8635;</a>
                <hr>
                <table id="plyEventsTable" class="table" data-nonce="<?php echo wp_create_nonce("ply_load_data"); ?>">
                    <thead> 
                        <tr> 
                            <th>Date</th>
                            <th>Action</th>
                            <th>Description</th>
                            <th>Options</th>
                            <th>Last Fired</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr><td colspan="6">Please select project</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="modal" id="plyEventsModal" tabindex="-1" role="dialog" aria-labelledby="plyEventsModalLabel">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title" id="plyEventsModalLabel">Event Code</h4>
                    </div>
                    <div class="modal-body">
                        <div class="created-code" id="evCode">
                            <?php /* <div style="margin-bottom: 15px;">
                                <a href="#" id="eventTabJs" class="active event-tabs">Javascript Code</a>&nbsp;|&nbsp;<a  id="eventTabPixel" class="event-tabs" href="#">Pixel Code</a>
                            </div> */ ?>
                            <div id="eventTabContentJs">
                                <?php /* <p>Place below code within your &lt;head&gt; section.</p>
                                <textarea name="evCode_top" id="evCode_top" class="form-control" onclick="jQuery(this).select()" style="background-color:#f4f4f4;  font-size:12px; height:100px;"></textarea>
                                 */ ?>
                                <?php if(!empty($projectCodeInclude) && !empty($projectCode)): ?>
                                    <p class='ply_successMsg'>A code from Project '<span><?php echo $projects[$projectCode['ply_project_id']] ?></span>' is being used on this site.</p>
                                <?php else: ?>
                                    <p class='ply_errorMsg'>
                                        You must include a Platform.ly project code within your site, please <a href="<?php echo get_admin_url(null, 'admin.php?page=ply') ?>">"click here"</a> to do it now.
                                    </p>
                                <?php endif; ?>
                                <p style="margin-top: 15px;">Place below code where you want to fire the event. The events can be fired in any HTML code, onClick,onChange events, JS functions...</p>
                                <div style="margin-bottom: 10px;">
                                    <select id="eventType" class="form-control">
                                        <option value="1" selected>Add on Page</option>
                                        <option value="2">Add on Link or Button</option>
                                    </select>
                                </div>
                                <div id="eventCodeBlock">
                                    <textarea name="evCode_bottom" id="evCode_bottom" class="form-control" onclick="jQuery(this).select()" style="background-color:#f4f4f4;  font-size:12px;"></textarea>
                                    <textarea name="evCode_bottom_click" id="evCode_bottom_click" class="form-control" onclick="jQuery(this).select()" style="background-color:#f4f4f4;  font-size:12px;"></textarea>
                                </div>
                            </div>
                            <?php /*
                            <div id="eventTabContentPixel" style="display:none;">
                                <p>Place below code where you want to fire the event.</p>
                                <textarea name="evCode_bottom_pixel" id="evCode_bottom_pixel" class="form-control" onclick="jQuery(this).select()" style="background-color:#f4f4f4;  font-size:12px;"></textarea>
                            </div> */ ?>
                            </div>
                        </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Done</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>