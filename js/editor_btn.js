(function() {
    tinymce.PluginManager.add('platform_ly_link', function( editor, url ) {
        console.log('tinymce.PluginManager.add');

        editor.addButton('platform_ly_link', {
            title: 'Add Platform.ly Link',
            icon: 'icon platform_ly_link_button',
            onclick: function(){
                showPlyDialogOptions()
            }
        });
    });
})();
/*(function() {
    tinymce.PluginManager.add('platform_ly_link', function( editor, url ) {
        editor.addButton('platform_ly_link', {
            title: 'Add Platform.ly Link',
            icon: 'icon platform_ly_link_button',
            onclick: function(){
                get_plyTrackingLinks();
                editor.windowManager.open({
                    title: 'Platform.ly Link',
                    autoScroll: true,
                    body: [{
                        type: 'container',
                        html: '<div>loading . . .</div>'
                    }],
                    buttons:[{
                        text: 'Add Link',
                        subtype: 'primary',
                        classes: 'ply_btn_add_link',  
                        onclick: function() {
                            (this).parent().parent().close();
                        }
                    },{
                        text: 'Cansel',
                        classes: 'ply_btn_cancel_link',
                        onclick: function() {
                            (this).parent().parent().close();
                        }
                    }]
                });
            }
        });
    });
})();

function load_trackingLinks_inDetails(id){
    jQuery.post(ajaxurl, {action: 'ply_get_tracking_links_details', id: id}, function(response){
        var data = JSON.parse(response);
        if(data.status=='success'){
            var tbody = '';
            for(var key in data.trackingLinksDetails) {
                if(data.trackingLinksDetails[key] == 'custom_tracking'){
                    tbody += '<tr>\n\
                                <td style="text-align: center"><input type="radio" data-mainlink="'+data.trackingLinksDetails[0]+'"  style="width:auto, text-align: center" class="chosenCustomLink" name="chosenCustomLink" value="custom_tracking"/></td>\n\
                                <td style="color:#007bf7; font-size:12px;">'+data.trackingLinksDetails[key]+'?c1=<input type="text" value="PlyEmail" placeholder="Source" style="width:50px" id="trc1" />&c2=<input type="text" value="Email" placeholder="Medium" style="width:50px" id="trc2" />&c3=<input type="text" placeholder="Email Title" style="width:50px" id="trc3" />&c4=<input type="text" placeholder="Identifier" style="width:50px" id="trc4" /></td>\n\
                            </tr>';
                }else{
                    tbody += '<tr>\n\
                                <td style="text-align: center"><input type="radio"  style="width:auto" class="chosenCustomLink" name="chosenCustomLink" value="'+data.trackingLinksDetails[key]+'"/></td>\n\
                                <td><a href="'+data.trackingLinksDetails[key]+'" target="_blank" style="font-size:12px"">'+data.trackingLinksDetails[key]+'</a></td>\n\
                            </tr>';
                }
            }
            tinymce.activeEditor.windowManager.close();
            var table = '<table id="links_details_table">\n\
                    <thead>\n\
                        <th style="width: 40px;">Select</th>\n\
                        <th>Traffic Link</th>\n\
                    </thead>\n\
                    <tbody>'+tbody+'</tbody>\n\
                    </table>';
            tinymce.activeEditor.windowManager.open({
                title: 'Platform.ly Link',
                body: [{
                    classes: 'ply_body_modal_link',
                    type: 'container',
                    maxHeight: 500,
                    minWidth: 600,
                    html: '<div>'+table+'</div>'
                }],
                buttons:[{
                    text: 'Add Link',
                    subtype: 'primary',
                    classes: 'ply_btn_add_link',
                    onclick: function() {
                        var enteredUrl = jQuery('.chosenCustomLink:checked').val();
                        if(enteredUrl == 'custom_tracking'){
                            enteredUrl = jQuery('.chosenCustomLink:checked').data('mainlink')+'?'+(($("#trc1").val() != '') ? '&c1='+$("#trc1").val() : '')+(($("#trc2").val() != '') ? '&c2='+$("#trc2").val() : '')+(($("#trc3").val() != '') ? '&c3='+$("#trc3").val() : '')+(($("#trc4").val() != '') ? '&c4='+$("#trc4").val() : '');
                        }
                        var selectedText = tinymce.activeEditor.selection.getContent();
                        if(selectedText.length < 1){
                            selectedText = enteredUrl;
                        }
                        tinymce.activeEditor.insertContent('<a href="'+enteredUrl+'">'+selectedText+'</a>');
                        (this).parent().parent().close();
                    }
                },{
                    text: 'Cansel',
                    classes: 'ply_btn_cancel_link',
                    onclick: function() {
                        (this).parent().parent().close();
                    }
                }]
            });
        }
    });
}

function get_plyTrackingLinks(){
    jQuery.post(ajaxurl, {action: 'ply_get_tracking_links'}, function(response){
        var data = JSON.parse(response);
        if(data.status!='success'){
            $('#plyLinks').html(data.status);
        }else{
            var tbody = '';
            for(var key in data.links) {
                if(data.links[key]['disabled'] != 'disabled'){
                    tbody += '<tr>\n\
                                <td style="font-size:12px;">'+data.links[key]['cat_name']+'</td>\n\
                                <td style="font-size:12px;"><a href="javascript:;" style="text-decoration: none; color: inherit;" onclick="load_trackingLinks_inDetails('+data.links[key]['id']+')"><strong>'+data.links[key]['name']+'</strong></a></td>\n\
                                <td style="font-size:12px; text-align:left"><a style="word-break: break-all" href="'+data.links[key]['target_link']+'" target="_blank">'+data.links[key]['target_link']+'</a></td>\n\
                            </tr>';
                }
            }
            var table = '<table id="links_table_toggle">\n\
                <thead>\n\
                    <th>Category</th>\n\
                    <th>Campaign</th>\n\
                    <th>Target URL</th>\n\
                </thead>\n\
                <tbody>'+tbody+'</tbody>\n\
                </table>';
            //$('#plyLinks').html(table);
            tinymce.activeEditor.windowManager.close();
            tinymce.activeEditor.windowManager.open({
                title: 'Platform.ly Link',
                body: [{
                    classes: 'ply_body_modal_link',
                    type: 'container',
                    maxHeight: 500,
                    minWidth: 600,
                    html: table
                }],
                buttons:[{
                    text: 'Add Link',
                    subtype: 'primary',
                    classes: 'ply_btn_add_link',  
                    onclick: function() {
                        (this).parent().parent().close();
                    }
                },{
                    text: 'Cansel',
                    classes: 'ply_btn_cancel_link',
                    onclick: function() {
                        (this).parent().parent().close();
                    }
                }]
            });
        }
    });
}
*/