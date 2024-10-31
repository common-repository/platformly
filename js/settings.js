(function($) {
    $('#btnPlySaveProjectCode').click(function(){
        var projectId = $('#plyProjectSelect').val();
        if(projectId < 0){
            alert('Please select project');
        }else{
            var confirmMsg = 'Changing the project code will remove the current code, continue?';
            if($('#plyProjectCodeBlock').hasClass('ply-wc-project-code')){
                confirmMsg = "If you change the project it will be also changed for your 'Platform.ly for WooCommerce' plugin, continue?";
            }
            if(confirm(confirmMsg)){
                $('#msgPlyGetProgectCodeError').hide();
                $('#msgPlyGetProgectCodeActivated').hide();
                $('#plyLoadingProjectCode').show();
                $.post(ajaxurl, {'action': 'ply_save_project_code','projectId': projectId, '_wpnonce': $('#projectCodeNonce').val()}, function(response){
                    data = JSON.parse(response);
                    $('#plyLoadingProjectCode').hide();
                    if(data.status != 'success'){
                        $('#msgPlyGetProgectCodeError').html(data.status).show();
                    }else{
                        $('#msgPlyGetProgectCodeActivated span').text(data.projectName);
                        $('#msgPlyGetProgectCodeActivated').show();
                    }
                });
            }
        }
    });
    $('#plyCheckboxSetProjectCode').change(function(){
        if($(this).prop('checked')){
            $('#plyProjectCodeSettings').show();            
            var includeProjectCode = 1;
        }else{
            $('#plyProjectCodeSettings').hide();
            var includeProjectCode = 0;
        }
        if(!$('#plyProjectCodeBlock').hasClass('ply-wc-project-code')){
            $.post(ajaxurl, {action: 'ply_project_code_include', includeCode: includeProjectCode, '_wpnonce': $('#projectCodeNonce').val()}, function(response){});
        }
    });
    $('#plyRemoveProjectCode').click(function(){
        if(!$('#plyProjectCodeBlock').hasClass('ply-wc-project-code')) {
            if (confirm('Are you sure you want to remove the project code?')) {
                $.post(ajaxurl, {'action': 'ply_remove_project_code', '_wpnonce': $('#projectCodeNonce').val()}, function (response) {
                    $('#msgPlyGetProgectCodeActivated').hide();
                    alert('Project code was removed.');
                });
            }
        }
    });
})(jQuery);