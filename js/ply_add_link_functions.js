plyAddLinkDialog = '<div id="ply-add-link-dialog" class="hidden" style="max-width:800px"></div>';
plySearchWord = '';
plySelectedWord = {};
plyEventButton = {};
function get_ply_tracking_links(){
    jQuery('#ply-add-link-dialog').html('<div>Loading . . .</div>');
    jQuery.post(ajaxurl, {action: 'ply_get_tracking_links', '_wpnonce': jQuery('#ply-add-link-dialog').data('nonce')}, function(response){
        jQuery('.ui-dialog-buttonpane button:contains("Add Link")').button().hide();
        var data = JSON.parse(response);
        if(data.status!='success'){
            if(data.status == 'not_found'){
                jQuery('#ply-add-link-dialog').html('Please go to settings and enter your API Key first.');
            }else{
                jQuery('#ply-add-link-dialog').html(data.status);
            }
        }else{
            var tbody = '';
            for(var key in data.links) {
                if(data.links[key]['disabled'] != 'disabled'){
                    tbody += '<tr>\n\
                                <td class="ply_search_link" style="font-size:12px;">'+data.links[key]['project_name']+'</td>\n\
                                <td class="ply_search_link" style="font-size:12px;">'+data.links[key]['cat_name']+'</td>\n\
                                <td class="ply_search_link" style="font-size:12px;"><a href="javascript:;" style="text-decoration: none; color: inherit;" onclick="load_trackingLinks_inDetails('+data.links[key]['id']+')"><strong>'+data.links[key]['name']+'</strong></a></td>\n\
                                <td style="font-size:12px; text-align:left"><a style="word-break: break-all" href="'+data.links[key]['target_link']+'" target="_blank">'+data.links[key]['target_link']+'</a></td>\n\
                            </tr>';
                }
            }
            var search = '<div><input id="ply_links_table_search" type="text" placeholder="Enter search word"/></div>';
            var table = search+'<table id="links_table_toggle">\n\
                <thead>\n\
                    <th>Project</th>\n\
                    <th>Category</th>\n\
                    <th>Campaign</th>\n\
                    <th>Target URL</th>\n\
                </thead>\n\
                <tbody>'+tbody+'</tbody>\n\
                </table>';
            jQuery('#ply-add-link-dialog').html(table);
            if(plySearchWord){
                jQuery('#ply_links_table_search').val(plySearchWord).keyup();
            }
            jQuery('#ply-add-link-dialog').on('keyup', '#ply_links_table_search', function(){
                jQuery('#links_table_toggle tr').removeClass('ply_search_has_result');
                plySearchWord = jQuery('#ply_links_table_search').val();
                //jQuery('#links_table_toggle td.ply_search_link:not(:contains("'+plySearchWord+'"))').parents('tr').addClass('ply_serch_not_result');
                var lowerPlySearchWord = plySearchWord.toLowerCase();
                jQuery('#links_table_toggle td.ply_search_link').each(function(){
                if(jQuery(this).text().toLowerCase().indexOf(lowerPlySearchWord) < 0){
                    if(!jQuery(this).parent('tr').hasClass('ply_search_has_result')){
                            jQuery(this).parent('tr').addClass('ply_serch_not_result');
                        }
                    }else{
                        jQuery(this).parent('tr').addClass('ply_search_has_result').removeClass('ply_serch_not_result');
                    }
                });
            });
            jQuery('#ply-add-link-dialog').dialog({width: '600px'}); // .position({ my: "center", at: "center", of: window });
        }
    });
}
function load_trackingLinks_inDetails(id){
    jQuery('#ply-add-link-dialog').html('<div>Loading . . .</div>');
    jQuery.post(ajaxurl, {action: 'ply_get_tracking_links_details', id: id, '_wpnonce': jQuery('#ply-add-link-dialog').data('nonce')}, function(response){
        var data = JSON.parse(response);
        if(data.status=='success'){
            var tbody = '';
            for(var key in data.trackingLinksDetails) {
                if(data.trackingLinksDetails[key] == 'custom_tracking'){
                    tbody += '<tr>\n\
                                <td style="text-align: center"><input type="radio" data-mainlink="'+data.trackingLinksDetails[0]+'"  style="width:auto, text-align: center" class="chosenCustomLink" name="chosenCustomLink" value="custom_tracking"/></td>\n\
                                <td style="color:#007bf7; font-size:12px;">'+data.trackingLinksDetails[0]+'?c1=<input type="text" value="PlyEmail" placeholder="Source" style="width:50px" id="trc1" />&c2=<input type="text" value="Email" placeholder="Medium" style="width:50px" id="trc2" />&c3=<input type="text" placeholder="Email Title" style="width:50px" id="trc3" />&c4=<input type="text" placeholder="Identifier" style="width:50px" id="trc4" /></td>\n\
                            </tr>';
                }else{
                    tbody += '<tr>\n\
                                <td style="text-align: center"><input type="radio"  style="width:auto" class="chosenCustomLink" name="chosenCustomLink" value="'+data.trackingLinksDetails[key]+'"/></td>\n\
                                <td><a href="'+data.trackingLinksDetails[key]+'" target="_blank" style="font-size:12px"">'+data.trackingLinksDetails[key]+'</a></td>\n\
                            </tr>';
                }
            }
            var table = '<div> <a id="btnPlatformLyBackToCompany" onclick="get_ply_tracking_links()" class="button button-primary" href="javascript:;">Back to Campaigns</a></div><table id="links_details_table">\n\
                    <thead>\n\
                        <th style="width: 40px;">Select</th>\n\
                        <th>Traffic Link</th>\n\
                    </thead>\n\
                    <tbody>'+tbody+'</tbody>\n\
                    </table>';
            jQuery('#ply-add-link-dialog').html(table);
            jQuery('#ply-add-link-dialog').dialog('widget'); // .position({ my: "center", at: "center", of: window });
            jQuery('.ui-dialog-buttonpane button:contains("Add Link")').button().show();
        }else{
            if(data.status == 'not_found'){
                jQuery('#ply-add-link-dialog').html('Please go to settings and enter your API Key first.');
            }else{
                jQuery('#ply-add-link-dialog').html(data.status);
            }
        }
    });
}
function getSelected() {
    var txtarea = document.getElementById('content');
    var start = txtarea.selectionStart;
    var finish = txtarea.selectionEnd;
    return txtarea.value.substring( start, finish );
}
function add_platform_ly_link(){
    //jQuery('#ply-add-link-dialog').dialog('open');
    get_ply_tracking_links();
}
function closePlyDialog(){
    jQuery('#ply-add-link-dialog').dialog('close');
    jQuery('#ply-dialog-footer').hide();
    jQuery('#ply-add-link-dialog').html('<div>Loading . . .</div>');
}
function showPlyDialogOptions(){
    if(typeof wp.richText === 'undefined' && typeof elementor === 'undefined'){
        jQuery('#ply-add-link-dialog').dialog('open');
        get_ply_tracking_links();
    }else{
        jQuery('#ply-add-link-dialog').dialog('open');
        jQuery('#ply-add-link-dialog').dialog({width: '600px'}); // .position({ my: "center", at: "center", of: window });
        var options = '<div id="ply-block-btns"><div class="ply-btn-etitor-block" onclick="get_ply_tracking_links()">Add a tracking link</div><div class="ply-btn-etitor-block" onclick="get_ply_projects()">Add an event</div></div>';
        jQuery('#ply-add-link-dialog').html(options);
    }
}
function get_ply_projects(){
    var projectList = '<option value="-1">Please choose...</option>';
    //var plyMsgIncludeProject = '<p class="ply-project-msg ply_successMsg">'+plyProjectMsg+'</p>';
    //ply-project-name
    jQuery('#ply-add-link-dialog').html('<div>Loading . . .</div>');
    jQuery.post(ajaxurl, {'action': 'ply_get_projects', '_wpnonce': jQuery('#ply-add-link-dialog').data('nonce')}, function(response){
        var data = JSON.parse(response);
        for(var key in data) {
            projectList += '<option value="'+key+'">'+data[key]+'</option>';
        }
        if(plyProjectId > 0){
            var plyMsgIncludeProject = '<p class="ply-project-msg ply_successMsg" style="margin-top: 0;">A code from Project \'<span>'+data[plyProjectId]+'</span>\' is being used on this site.</p>';
        }else{
            var plyMsgIncludeProject = '<p class="ply-project-msg ply_errorMsg" style="margin-top: 0;">'+plyProjectMsg+'</p>';
        }
        jQuery('#ply-add-link-dialog').html("<div>"+plyMsgIncludeProject+"<select id='ply-project-select'>"+projectList+"</select></div><div id='ply-events-block'><div id='ply-default-event-msg'>Please select project</div></div>");
    });
}
function get_ply_events(projectId){
    if(projectId == -1){
        return; 
    }
    jQuery('.ui-dialog-buttonpane button:contains("Add on Page")').button().hide();
    jQuery('.ui-dialog-buttonpane button:contains("Add on Link Click")').button().hide();
    jQuery('.ui-dialog-buttonpane button:contains("Copy Code")').button().hide();
    jQuery('#ply-add-link-dialog #ply-events-block').html('<div id="ply-default-event-msg">Loading...</div></div>');
    jQuery.post(ajaxurl, {'action': 'ply_load_events','projectId': projectId, '_wpnonce': jQuery('#ply-add-link-dialog').data('nonce')}, function(response){
        var data = JSON.parse(response);
        if(data.status!='success'){
            if(data.status == 'not_found'){
                jQuery('#ply-add-link-dialog #ply-events-block').html('<div id="ply-default-event-msg">Please go to settings and enter your API Key first.</div>');
            }else{
                jQuery('#ply-add-link-dialog #ply-events-block').html('<div id="ply-default-event-msg">'+data.status+'</div>');
            }
        }else{
            if(data.events.length > 0){
                var tbody = '';
                for(var key in data.events) {
                    var listOptions = '';
                    if(jQuery.isPlainObject(data.events[key]['decode_options']) && Object.keys(data.events[key]['decode_options']).length > 1){
                        listOptions = '<div class="ply-editor-list-event-options">\n\
                            <a type="button" class="ply-link-more" data-toggle="dropdown" style="padding: 0">\n\
                                view more\n\
                            </a>\n\
                            <ul class="ply-editor-dropdown-menu" role="menu" style="padding: 5px 10px;">\n\
                                '+data.events[key]['view_options']+'\n\
                            </ul>\n\
                        </div>';
                    }
                    var custom_action = data.events[key]['custom_action'] ? "("+data.events[key]['custom_action']+")" : "";
                    if(data.events[key]['first_options']){
                        var first_options = data.events[key]['first_options'];
                    }else{
                        var first_options = '-';
                    }
                    tbody += '<tr>\n\
                                <td style="text-align: center"><input type="radio"  style="width:auto" class="chosenEvent" name="chosenEvent" value="'+data.events[key]['id']+'"/></td>\n\
                                <td style="font-size:12px;">'+data.events[key]['formatted_date']+'</td>\n\
                                <td class="existing-action" style="font-size:12px;"><span>'+data.events[key]['action']+'</span>'+custom_action+'</td>\n\
                                <td class="existing-description" style="font-size:12px;">'+data.events[key]['description']+'</td>\n\
                                <td style="font-size:12px; text-align:left">'+first_options+'<br/>'+listOptions+'</td>\n\
                            </tr>';
                }
                var table = '<table id="links_table_toggle">\n\
                    <thead>\n\
                        <th style="width: 40px;">Select</th>\n\
                        <th>Date</th>\n\
                        <th>Action</th>\n\
                        <th>Description</th>\n\
                        <th>Options</th>\n\
                    </thead>\n\
                    <tbody>'+tbody+'</tbody>\n\
                    </table>';
                var eventCode = '<div id="ply-dialog-event-close-footer"><span>&#187;</span></div><div><p style="margin-top: 0;">Place below code where you want to fire the event. The events can be fired in any HTML code, onClick,onChange events, JS functions...</p>\n\
                                <div style="margin-bottom: 10px;width: 100%;">\n\
                                    <select id="eventType" style="width: 100%;" class="form-control">\n\
                                        <option value="1" selected>Add on Page</option>\n\
                                        <option value="2">Add on Link or Button</option>\n\
                                    </select>\n\
                                </div>\n\
                                <div id="eventCodeBlock">\n\
                                    <textarea name="evCode_bottom" id="evCode_bottom" class="form-control" onclick="jQuery(this).select()" style="width: 100%;background-color:#f4f4f4;  font-size:12px;"></textarea>\n\
                                    <textarea name="evCode_bottom_click" id="evCode_bottom_click" class="form-control" onclick="jQuery(this).select()" style="width: 100%;background-color:#f4f4f4;  font-size:12px;"></textarea>\n\
                                </div></div>';
                jQuery('#ply-add-link-dialog #ply-events-block').html(table);
                jQuery('#ply-dialog-footer').html(eventCode);
                jQuery('.ui-dialog-buttonpane button:contains("Add on Page")').button().show();
                jQuery('.ui-dialog-buttonpane button:contains("Add on Link Click")').button().show();
                jQuery('.ui-dialog-buttonpane button:contains("Copy Code")').button().show();

                // Check if no text selected
                if (typeof elementor !== 'undefined' && typeof window.tinymce !== 'undefined' && !tinymce.activeEditor.isHidden()) {
                    var selectedText = tinymce.activeEditor.selection.getContent();
                    if (selectedText.length === 0) {
                        var ply_link_settings = '<div style="text-align: center;padding: 5px;">Add new link</div>\n\
                            <table><tbody>\n\
                            <tr><td style="padding: 5px;text-align: right;">Text</td><td style="width: 300px;"><input id="ply_dialog_link_text" type="text" onkeypress="jQuery(\'#ply-link-text-error\').hide();" placeholder="Click this one" required></td></tr>\n\
                            <tr id="ply-link-text-error"><td></td><td style="color: red">This field is required</td></tr>\n\
                            <tr><td style="padding: 5px;text-align: right;">URL</td><td style="width: 300px;"><input id="ply_dialog_link_url" type="text" onkeypress="jQuery(\'#ply-link-url-error\').hide();" placeholder="' + siteUrl + '" required></td></tr>\n\
                            <tr id="ply-link-url-error"><td></td><td style="color: red">This field is required</td></tr>\n\
                            </tbody></table>';
                        jQuery('#ply-dialog-link-settings').html(ply_link_settings);
                        jQuery('#ply-dialog-link-settings').show();
                    }
                }
            }else{
                jQuery('#ply-add-link-dialog #ply-events-block').html("<div id='ply-default-event-msg'>You haven't created an Event for this project yet.</div>");
            }
            jQuery('#ply-add-link-dialog').dialog({width: '600px'}); // .position({ my: "center", at: "center", of: window });
        }
    });
}
function showPlyDialogOnlyEventOptions(){
    jQuery('#ply-add-link-dialog').dialog('open');
    jQuery('#ply-add-link-dialog').dialog({width: '600px'}); // .position({ my: "center", at: "center", of: window });
    if(typeof plyEventButton.attributes.onclick !== 'undefined' && plyEventButton.attributes.onclick != ''){
        jQuery('.ui-dialog-buttonpane button:contains("Edit Event")').button().show();
        jQuery('.ui-dialog-buttonpane button:contains("Remove Event")').button().show();
        jQuery('#ply-add-link-dialog').html("There's existing event for this button. Choose one of the following action.");
        jQuery('#ply-add-link-dialog').dialog({width: '600px'}); // .position({ my: "center", at: "center", of: window });
    }else{
        get_ply_projects();
    }
}

jQuery(document).on('click', '.ply-link-more', function(){
    //jQuery('.ply-editor-list-event-options').removeClass('open');
    jQuery(this).parent().addClass('open');
});
jQuery(document).on('change', '#ply-project-select', function(){
    var projectId = jQuery(this).val();
    jQuery('#ply-dialog-footer').hide();
    get_ply_events(projectId);
});
jQuery(document).on('change', '#eventType', function(){
    if(jQuery(this).val() == 1){
        jQuery('#evCode_bottom_click').hide();
        jQuery('#evCode_bottom').show();
    }else{
        jQuery('#evCode_bottom').hide();
        jQuery('#evCode_bottom_click').show();
    }
});
jQuery(document).on('click', '#ply-dialog-event-close-footer span', function(){
    jQuery('#ply-dialog-footer').hide();
});
