function loadOptins(projectId, optinId) {
    if (projectId == -1)
        return;

    var data = {
        'action': 'ply_load_optins',
        'projectId': projectId,
        '_wpnonce': jQuery('#tdOptins').data('nonce')
    };

    jQuery("#tdOptins").html("Loading...");

    jQuery.post(ajaxurl, data, function (response) {
        decoded = JSON.parse(response);
        if (decoded.status != 'success')
            jQuery("#tdOptins").html(decoded.status);
        else {
            select = document.createElement("select");
            jQuery(select).attr("id", "ply_optin");
            for (var key in decoded.optins) {
                var disabled = '';
                for (var k in activeOptinIds) {
                    if (activeOptinIds[k] == key)
                        disabled = " disabled ";
                }
                //if($.inArray(key, activeOptinIds)) 
                //disabled = ' disabled '; 
                jQuery(select).append("<option value='" + key + "' " + disabled + ">" + decoded.optins[key] + "</option>");
            }

            jQuery("#tdOptins").html('').append(select);

            if (typeof optinId != typeof undefined)
                jQuery("#ply_optin").val(optinId);
        }
    });
}
(function($) {
    function checkLoc(loc) {
        if (loc == 'all')
            $("#optinWhereAll").attr("checked", "checked");
        else if (loc == 'posts')
            $("#optinWherePosts").attr("checked", "checked");
    }
})(jQuery);
function updateOptin(edit) {
    (function($) {
        var optinWhere = $('input[name="optinwhere"]:checked').val();
        var optinWherePage = $('#optinWherePage').val();
        
        if ($("#ply_project").val() == -1) {
            alert("Please select optin form first!");
            return;
        }
        if ($('#plugin-pos a.selected').length == 0) {
            alert("Please select optin screen position first!");
            return;
        }
        if ($('#plugin-type a.selected').length == 0) {
            alert("Please select how the optin will be shown first!");
            return;
        }
        if ($("#ply_trigger").val() == -1) {
            alert("Please select trigger first!");
            return;
        }
        if((optinWhere == 'except' || optinWhere == 'specific') && optinWherePage === null){
            alert("Please choose a page/post!");
            return;
        }
        
        if (edit) {
            var projectId = $("#edit_ply_optin_pid").val();
            var optinId = $("#edit_ply_optin_id").val();
            var optinName = $("#edit_ply_optin_name").val();
        } else {
            var projectId = $("#ply_project").val();
            var optinId = $("#ply_optin").val();
            var optinName = $("#ply_optin option[value='" + optinId + "']").text();
        }
        var optinPosition = $("#plugin-pos a[class='selected']").attr('id');
        var optinType = $("#plugin-type a[class='selected']").attr('id');

        if (typeof optinId == typeof undefined)
            optinId = -1;

        /*if ($("#optinWhereAll").is(":checked"))
            optinWhere = 'all';
        else if ($("#optinWherePosts").is(":checked"))
            optinWhere = 'posts';
        */
        var clickAway = false;
        if ($("#ply_clickAway").is(":checked"))
            clickAway = true;

        var blurBack = false;
        if ($("#ply_blurBack").is(":checked"))
            blurBack = true;
        if(optinWherePage !== null){
            optinWherePage = optinWherePage.join(",");
        }else{
            optinWherePage = 0;
        }
        $("#formProjectId").val(projectId);
        $("#formOptinId").val(optinId);
        $("#formOptinName").val(optinName);
        $("#formOptinWhere").val(optinWhere);
        $("#formOptinPosition").val(optinPosition);
        $("#formOptinType").val(optinType);
        $("#formOptinTriggerType").val($("#ply_trigger").val());
        $("#formOptinTriggerValue").val($("#triggerValue").val());
        $("#formOptinClickAway").val(clickAway);
        $("#formOptinBlurBack").val(blurBack);
        $("#formOptinWherePages").val(optinWherePage);
        
        $("#optinSaveForm").submit();
    })(jQuery);
}

function showTriggerOptions(type, value) {
    (function($){
        if (type == 'time') {
            $("#thTrigger").html("Please select the delay (in seconds) before this optins is shown:");

            $("#tdTrigger").html("<input type='text' name='triggerValue' id='triggerValue' style='width: 50px' value='" + value + "' /> seconds");
        } else if (type == 'scroll') {
            $("#thTrigger").html("Please select the scroll percentage before this optins is shown");

            var options = '';
            var selected = '';
            for (var i = 0; i <= 100; i += 10) {
                selected = '';
                if (value == i)
                    selected = ' selected ';
                options += "<option value='" + i + "' " + selected + ">" + i + "</option>";
            }

            $("#tdTrigger").html("<select name='triggerValue' id='triggerValue'> " + options + " </select>%");
        } else if (type == 'exit') {
            $("#thTrigger").html("If this is selected optin will be shown when the user navigates to close tab or window.");

            $("#tdTrigger").html("All is set! <input type='hidden' value='0' id='triggerValue' name='triggerValue'/>");
        } else {
            $("#thTrigger").html("");
            $("#tdTrigger").html("");
        }
    })(jQuery);
}

function removeOptin(id) {
    (function($){
        if (confirm("Are you sure you want to remove this optin from this site?")) {
            $("#removeOptin").val(id);
            $("#optinRemoveForm").submit();
        }
    })(jQuery);
}

function editOptin(id) {
    (function($){
        $("#editOptin").val(id);
        $("#optinEditForm").submit();
    })(jQuery);
}

(function($){
    $(document).ready(function(){
        $('#optinWherePage').select2({
            placeholder: "Please choose...",
            width: '20%'
        });
    });
    $('input[name="optinwhere"]').change(function(){
        if($(this).val() == 'except' || $(this).val() == 'specific'){
            $('#plyBlockListWpPages').show();
        }else{
            $('#plyBlockListWpPages').hide();
        }
    });
})(jQuery);