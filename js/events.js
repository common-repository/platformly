function loadEvents(projectId){
    if(projectId == -1){
        return; 
    }
    jQuery('#plyEventsTable tbody').html('<tr><td colspan="6">Loading...</td></tr>');
    jQuery.post(ajaxurl, {'action': 'ply_load_events','projectId': projectId, '_wpnonce': jQuery('#plyEventsTable').data('nonce')}, function(response){
        data = JSON.parse(response);
        if(data.status!='success'){
            jQuery('#plyEventsTable tbody').html('<tr><td colspan="6">'+data.status+'</td></tr>');
        }else{
            //jQuery('#evCode_top').text(data.eventCode);
            if(data.events.length > 0){
                var tbody = '';
                for(var key in data.events){
                    var listOptions = '';
                    if(jQuery.isPlainObject(data.events[key]['decode_options']) && Object.keys(data.events[key]['decode_options']).length > 1){
                        listOptions = '<div class="btn-group list-event-options">\n\
                            <a type="button" class="btn option-button" data-toggle="dropdown" style="padding: 0">\n\
                                view more\n\
                            </a>\n\
                            <ul class="dropdown-menu" role="menu" style="padding: 5px 10px;">\n\
                                '+data.events[key]['view_options']+'\n\
                            </ul>\n\
                        </div>';
                    }
                    var custom_action = data.events[key]['custom_action'] ? "("+data.events[key]['custom_action']+")" : "";
                    tbody += '<tr data-event="'+data.events[key]['id']+'"><td>'+data.events[key]['formatted_date']+'</td>\n\
                        <td class="existing-action"><span>'+data.events[key]['action']+'</span>'+custom_action+'</td>\n\
                        <td class="existing-description">'+data.events[key]['description']+'</td>\n\
                        <td>'+data.events[key]['first_options']+'<br/>'+listOptions+'</td>\n\
                        <td>'+data.events[key]['formatted_last_visited']+'</td>\n\
                        <td><button id="btnCopyCodeEvent" class="btn btn-default" style="margin-right:10px">Copy code</button></td></tr>';
                }
                jQuery('#plyEventsTable tbody').html(tbody);
            }else{
                jQuery('#plyEventsTable tbody').html("<tr><td colspan='6'>You haven't created an Event for this project yet.</td></tr>");
            }
        }
    });
}
(function($) {
    $(document).on('click', '#btnCopyCodeEvent', function(){
        $('#eventTabJs').click();
        var id = $(this).parents('tr').data('event');
        var description = $(this).parents('tr').find('.existing-description').text();
        var action = $(this).parents('tr').find('.existing-action span').text();
        var eventCode = "<script type='text/javascript'>plyt('"+action.replace(/[^a-zA-Z0-9_ ]/gi, "")+"', '"+description.replace(/[^a-zA-Z0-9_ ]/gi, "")+"','"+encodeURIComponent(id)+"');<\/script>";
        var eventCodeClick = "onclick=\"plyt('"+action.replace(/[^a-zA-Z0-9_ ]/gi, "")+"', '"+description.replace(/[^a-zA-Z0-9_ ]/gi, "")+"','"+encodeURIComponent(id)+"');\"";
        //var eventPixelCode = '<img height="1" width="1" style="display:none"	src="https://www.platform.ly/platformly.php?a='+encodeURIComponent(action.replace(/[^a-zA-Z0-9_ ]/gi, ""))+'&d='+encodeURIComponent(description.replace(/[^a-zA-Z0-9_ ]/gi, ""))+'&e='+encodeURIComponent(id)+'"/>';

        $("#eventType").val(1).trigger('change');
        $("#evCode_bottom_click").val(eventCodeClick);
        $("#evCode_bottom").val(eventCode);
        //$("#evCode_bottom_pixel").val(eventPixelCode);
        $('#plyEventsModal').modal('show');
    });

    $('#eventTabJs').click(function(){
        $('#eventTabContentJs').show();
        $('#eventTabContentPixel').hide();
        $(this).addClass('active');
        $('#eventTabPixel').removeClass('active');
    });
    $('#eventTabPixel').click(function(){
        $('#eventTabContentPixel').show();
        $('#eventTabContentJs').hide();
        $(this).addClass('active');
        $('#eventTabJs').removeClass('active');
    });
    $('#eventType').change(function(){
        if($(this).val() == 1){
            $('#evCode_bottom_click').hide();
            $('#evCode_bottom').show();
        }else{
            $('#evCode_bottom').hide();
            $('#evCode_bottom_click').show();
        }
    });
    $('#btnPlyRefreshEvents').click(function(e){
        e.preventDefault();
        var projectId = $('#plyProjectSelect').val();
        loadEvents(projectId);
    });
    $('#plyProjectSelect').click(function(){
        var projectId = $(this).val();
        loadEvents(projectId);
    });
})(jQuery);