plyPageSlug = '';
function loadPages(projectId, pageId) {
    if (projectId == -1)
        return;

    var data = {
        'action': 'ply_load_pages',
        'projectId': projectId,
        '_wpnonce': jQuery('#tdPages').data('nonce')
    };

    jQuery("#tdPages").html("Loading...");

    // since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
    jQuery.post(ajaxurl, data, function (response) {
        decoded = JSON.parse(response);
        if (decoded.status != 'success')
            jQuery("#tdPages").html(decoded.status);
        else {
            select = document.createElement("select");
            jQuery(select).attr("id", "ply_page");
            for (var key in decoded.pages) {
                var disabled = '';
                for (var k in activePageIds) {
                    if (activePageIds[k] == key)
                        disabled = " disabled ";
                }
                //if($.inArray(key, activeOptinIds)) 
                //disabled = ' disabled '; 
                jQuery(select).append("<option value='" + key + "' " + disabled + ">" + decoded.pages[key] + "</option>");
            }

            jQuery("#tdPages").html('').append(select);

            if (typeof pageId != typeof undefined)
                jQuery("#ply_page").val(pageId);
        }
    });
}
function updatePage(edit) {
    (function($){
        if (edit) {
            var projectId = $("#edit_ply_page_pid").val();
            var pageId = $("#edit_ply_page_id").val();
            var pageName = $("#edit_ply_page_name").val();
        } else {
            var projectId = $("#ply_project").val();
            var pageId = $("#ply_page").val();
            var pageName = $("#ply_page option[value='" + pageId + "']").text();
        }

        if (projectId == -1) {
            alert("Please select project first.");
            return;
        }

        var pageType = $("#plugin-type a[class='selected']").attr('id');
        var pageSlug = '[not applicable]';
        if ($("#pageSlug").length > 0) {
            pageSlug = $('#pageSlug').val();

            if (/^[a-z0-9\-_ ]+$/i.test(pageSlug) === false) {
                $("#pageSlug").next().next().css({color: 'red', 'font-size': '16px'});
                $("#pageSlug").focus();
                return;
            }
        }

        if (typeof pageId == typeof undefined)
            pageId = -1;

        $("#formProjectId").val(projectId);
        $("#formPageId").val(pageId);
        $("#formPageName").val(pageName);
        $("#formPageType").val(pageType);
        $("#formPageSlug").val(pageSlug);
        $("#formPagePingUpdateServices").val($('#plyCheckboxPingUpdateServices').prop('checked') ? '1' : '0');

        $("#pageSaveForm").submit();
    })(jQuery);
}
function editPage(id) {
    (function($){
        $("#editPage").val(id);
        $("#pageEditForm").submit();
    })(jQuery);
}
function choosePageType(elem, value, text){
    (function($){
        text = typeof text !== 'undefined' ?  text : false;
        if (typeof value != 'undefined'){
            plyPageSlug = value;
        }
        if (elem != '') {
            $('#plugin-type a').removeClass('selected');
            $(elem).addClass('selected');
        }
        var PingUpdateServices = '';
        if(typeof $('#edit_ply_ping_update_services') !== 'undefined'){
            PingUpdateServices = $('#edit_ply_ping_update_services').val() == 1 ? 'checked' : '';
        }
        
        if ($(elem).attr('id') == 'normal' || $(elem).attr('id') == 'welcome' || text != false) {
            $("#pageSlugContainer").html("<h4>Choose a slug name: <input type='text' name='pageSlug' id='pageSlug' value='" + plyPageSlug + "' /><br><small>* Only allowed: A-Z, a-z, 0-9, - and _</small> <br> <small>** Blank spaces will be automatically converted to hyphens (-)</small> </h4> ");
            if ($(elem).attr("id") == "welcome") {
                $("#pageSlugContainer").append("* If you already have a page of this type it will be overwritten as only one at a time can exist.");
            } else {
                $("#pageSlugContainer").append("* If you already have a page of this slug name it will be overwritten as only one at a time can exist.");
                $("#pageSlugContainer").append('<div style="margin-top: 8px;font-size: 13px;">\n' +
                        '                <input id="plyCheckboxPingUpdateServices" value="1" type="checkbox" ' + PingUpdateServices + ' />\n' +
                        '                <label id="plyLabelPingUpdateServices" for="plyCheckboxPingUpdateServices">Submit page to Update Services</label>\n' +
                        '            </div>');
            }
        } else {
            $("#pageSlugContainer").html("* If you already have a page of this type it will be overwritten as only one at a time can exist.");
        }
    })(jQuery);
}
function removePage(id) {
    (function($){
        if (confirm("Are you sure you want to remove this page from this site?")) {
            $("#removePage").val(id);
            $("#pageRemoveForm").submit();
        }
    })(jQuery);
}
(function($){
    $(document).on('change', '#pageSlug', function(){
        plyPageSlug = $(this).val();
    });
})(jQuery);