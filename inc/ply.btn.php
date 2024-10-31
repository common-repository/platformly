<?php
    if(!defined('ABSPATH'))
        exit; // Exit if accessed directly

    $projectCodeInclude = get_option('ply_project_code_active'); 
    $projectCode = ply_get_project_code();
?>
<div id="ply-add-link-dialog" class="hidden" style="max-width:600px;max-height: 500px;" data-nonce="<?php echo wp_create_nonce("ply_load_data"); ?>">
    <div>Loading . . .</div>
</div>
<script type="text/javascript">
<?php if(!empty($projectCodeInclude) && !empty($projectCode)): ?>
    var plyProjectMsg = "A code from Project '<span class='ply-project-name'></span>' is being used on this site.";
    var plyProjectId = '<?php echo $projectCode['ply_project_id'] ?>';
<?php else: ?>
    var plyProjectMsg = 'You must include a Platform.ly project code within your site, please <a href="<?php echo get_admin_url(null, 'admin.php?page=ply') ?>">"click here"</a> to do it now.';
    var plyProjectId = 0;
<?php endif; ?>
    var editInElementor = <?php echo platformLyisEditInElementor() ? 'true' : 'false'; ?>;
    var siteUrl = '<?php echo home_url(); ?>';

    jQuery(function ($) {
        // initalise the dialog
        $('#ply-add-link-dialog').dialog({
            title: 'Platform.ly Link',
            //dialogClass: 'wp-dialog',
            autoOpen: false,
            draggable: false,
            width: 'auto',
            modal: true,
            resizable: false,
            closeOnEscape: true,
            maxHeight: 550,
            dialogClass: "ply-dialog-editor",
            position: {
                my: "center",
                at: "center",
                of: window
            },
            close: function () {
                $('#ply-dialog-link-settings').hide();
            },
            open: function () {
                $('.ui-widget-overlay').bind('click', function () {
                    closePlyDialog();
                });
                $('.ui-widget-content').bind('click', function () {
                    $('.ply-editor-list-event-options').removeClass('open');
                });
                $('.ui-dialog-buttonpane button:contains("Add Link")').button().hide();
                $('.ui-dialog-buttonpane button:contains("Add on Page")').button().hide();
                $('.ui-dialog-buttonpane button:contains("Add on Link Click")').button().hide();
                $('.ui-dialog-buttonpane button:contains("Copy Code")').button().hide();
                $('.ui-dialog-buttonpane button:contains("Edit Event")').button().hide();
                $('.ui-dialog-buttonpane button:contains("Remove Event")').button().hide();
            },
            create: function () {
                $('.ui-dialog-titlebar-close').addClass('ui-button');
                $('.ply-dialog-editor .ui-dialog-buttonpane').before('<div id="ply-dialog-footer"></div>');
                $('#ply-dialog-footer').after('<div id="ply-dialog-link-settings"></div>');
            },
            buttons: [{
                    text: "Add Link",
                    click: function() {
                        var enteredUrl = jQuery('.chosenCustomLink:checked').val();
                        if(typeof wp.richText !== 'undefined'){
                            if(typeof enteredUrl !== 'undefined' && enteredUrl.length > 0){
                                if(enteredUrl == 'custom_tracking') {
                                    enteredUrl = jQuery('.chosenCustomLink:checked').data('mainlink') + '?' + (($("#trc1").val() != '') ? '&c1=' + $("#trc1").val() : '') + (($("#trc2").val() != '') ? '&c2=' + $("#trc2").val() : '') + (($("#trc3").val() != '') ? '&c3=' + $("#trc3").val() : '') + (($("#trc4").val() != '') ? '&c4=' + $("#trc4").val() : '');
                                }

                                if(plySelectedWord.value.start == plySelectedWord.value.end){
                                    var newElement = wp.richText.insert(plySelectedWord.value, enteredUrl);
                                    plySelectedWord.onChange(newElement);
                                    newElement.start = plySelectedWord.value.start;
                                    plySelectedWord.onChange(wp.richText.applyFormat(newElement, {
                                        type: 'core/link',
                                        attributes: {
                                            url: enteredUrl
                                        }
                                    }));
                                }else{
                                    plySelectedWord.onChange(wp.richText.applyFormat(plySelectedWord.value, {
                                        type: 'core/link',
                                        attributes: {
                                            url: enteredUrl
                                        }
                                    }));
                                }
                                closePlyDialog();
                            }else{
                                alert("Your Link can't be empty.");
                            }
                        }else{
                            if(typeof enteredUrl !== 'undefined' && enteredUrl.length > 0){
                                if (enteredUrl == 'custom_tracking') {
                                    enteredUrl = jQuery('.chosenCustomLink:checked').data('mainlink') + '?' + (($("#trc1").val() != '') ? '&c1=' + $("#trc1").val() : '') + (($("#trc2").val() != '') ? '&c2=' + $("#trc2").val() : '') + (($("#trc3").val() != '') ? '&c3=' + $("#trc3").val() : '') + (($("#trc4").val() != '') ? '&c4=' + $("#trc4").val() : '');
                                }
                                if (typeof window.tinymce !== 'undefined' && !tinymce.activeEditor.isHidden()) {
                                    var selectedText = tinymce.activeEditor.selection.getContent();
                                    if (selectedText.length < 1) {
                                        selectedText = enteredUrl;
                                    }
                                    tinymce.activeEditor.insertContent('<a href="' + enteredUrl + '">' + selectedText + '</a>');
                                } else {
                                    var selectedText = getSelected();
                                    if (selectedText.length < 1) {
                                        selectedText = enteredUrl;
                                    }
                                    QTags.insertContent('<a href="' + enteredUrl + '">' + selectedText + '</a>');
                                }
                                closePlyDialog();
                            }else{
                                alert("Your Link can't be empty.");
                            }
                        }
                    },
                    "class": 'button button-primary'
                }, 
                {
                    text: "Add on Page",
                    click: function() {
                        var id = jQuery('.chosenEvent:checked').val();
                        if(typeof id !== 'undefined' && id.length > 0){
                            var selectedEvent = jQuery('.chosenEvent:checked').parents('tr');
                            var description = selectedEvent.find('.existing-description').text();
                            var action = selectedEvent.find('.existing-action span').text();
                            var eventCode = "<script type='text/javascript'>plyt('"+action.replace(/[^a-zA-Z0-9_ ]/gi, "")+"', '"+description.replace(/[^a-zA-Z0-9_ ]/gi, "")+"','"+encodeURIComponent(id)+"');<\/script>";

                            if(typeof wp.richText !== 'undefined'){
                                // Using WP blocks editor
                                insertedBlock = wp.blocks.createBlock('core/html', {
                                    content: eventCode
                                });
                                wp.data.dispatch('core/editor').insertBlocks(insertedBlock, 0);
                            } else if(typeof window.tinymce !== 'undefined' && !tinymce.activeEditor.isHidden()) {
                                // Using TinyMCE editor
                                tinymce.activeEditor.insertContent(eventCode);
                            }
                            closePlyDialog();
                        }else{
                            alert("Please select event");
                        }
                    },
                    "class": 'button button-primary'
                },
                {
                    text: "Add on Link Click",
                    click: function() {
                        var link_text, link_url = siteUrl;
                        // Validate link settings
                        if (jQuery('#ply-dialog-link-settings').css('display') !== 'none') {
                            if (jQuery('#ply_dialog_link_text').val() === '') {
                                jQuery('#ply-link-text-error').show();
                                return;
                            }
                            if (jQuery('#ply_dialog_link_url').val() === '') {
                                jQuery('#ply-link-url-error').show();
                                return;
                            }
                            link_text = jQuery('#ply_dialog_link_text').val();
                            link_url  = jQuery('#ply_dialog_link_url').val();
                        }

                        var id = jQuery('.chosenEvent:checked').val();
                        if(typeof id !== 'undefined' && id.length > 0){
                            var selectedEvent = jQuery('.chosenEvent:checked').parents('tr');
                            var description = selectedEvent.find('.existing-description').text();
                            var action = selectedEvent.find('.existing-action span').text();
                            var eventCodeClick = "plyt('"+action.replace(/[^a-zA-Z0-9_ ]/gi, "")+"', '"+description.replace(/[^a-zA-Z0-9_ ]/gi, "")+"','"+encodeURIComponent(id)+"');";

                            if(typeof wp.richText !== 'undefined'){
                                if(Object.keys(plySelectedWord).length > 0){
                                    var activeLink = wp.richText.getActiveFormat(plySelectedWord.value, 'core/link');
                                    var attributes = {
                                        url: link_url,
                                        onclick: eventCodeClick
                                    };

                                    if(typeof activeLink !== 'undefined'){
                                        attributes.url = activeLink.attributes.url;
                                        if(typeof activeLink.attributes.target !== 'undefined'){
                                            attributes.target = activeLink.attributes.target;
                                        }
                                    }

                                    plySelectedWord.onChange(wp.richText.applyFormat(plySelectedWord.value, {
                                        type: 'core/link',
                                        attributes: attributes
                                    }));
                                } else if(Object.keys(plyEventButton).length > 0){
                                    plyEventButton.setAttributes({onclick: eventCodeClick});
                                }
                            } else {
                                if (typeof window.tinymce !== 'undefined' && !tinymce.activeEditor.isHidden()) {
                                    var selectedText = tinymce.activeEditor.selection.getContent();
                                    if (selectedText.length < 1) {
                                        selectedText = link_text;
                                    }

                                    // Check if editing not in Elementor
                                    if (elementor === 'undefined') {
                                        tinymce.activeEditor.insertContent('<a href="javascript:;" onclick="eventCodeClick">' + selectedText + '</a>');
                                    } else {
                                        var a_id = 'a_plyt_'+id,
                                            linkTag = '<a href="'+link_url+'" target="_blank" id="' + a_id + '">' + selectedText + '</a>',
                                            scriptTag = '<script type="text/javascript">jQuery("#' + a_id + '").on("click", function() {' + eventCodeClick + '})<\/script>';
                                        tinymce.activeEditor.insertContent(linkTag + scriptTag);
                                    }
                                } else {
                                    var selectedText = getSelected();
                                    if (selectedText.length < 1) {
                                        selectedText = link_text;
                                    }
                                    QTags.insertContent('<a href="' + link_url + '" onclick="' + eventCodeClick + '">' + selectedText + '</a>');
                                }
                            }
                            closePlyDialog();
                        }else{
                            alert("Please select event");
                        }
                    },
                    "class": 'button button-primary'
                },
                {
                    text: "Copy Code",
                    click: function(){
                        var id = jQuery('.chosenEvent:checked').val();
                        if(typeof id !== 'undefined' && id.length > 0){
                            var selectedEvent = jQuery('.chosenEvent:checked').parents('tr');
                            var description = selectedEvent.find('.existing-description').text();
                            var action = selectedEvent.find('.existing-action span').text();
                            var eventCodeClick = "plyt('"+action.replace(/[^a-zA-Z0-9_ ]/gi, "")+"', '"+description.replace(/[^a-zA-Z0-9_ ]/gi, "")+"','"+encodeURIComponent(id)+"');";
                            var eventCode = "<script type='text/javascript'>plyt('"+action.replace(/[^a-zA-Z0-9_ ]/gi, "")+"', '"+description.replace(/[^a-zA-Z0-9_ ]/gi, "")+"','"+encodeURIComponent(id)+"');<\/script>";
                            $("#eventType").val(1).trigger('change');
                            $("#evCode_bottom_click").val(eventCodeClick);
                            $("#evCode_bottom").val(eventCode);
                            $('#ply-dialog-footer').toggle();
                        }else{
                            alert("Please select event");
                        }
                    },
                    "class": 'button button-primary'
                },
                {
                    text: "Edit Event",
                    click: function(){
                        $('.ui-dialog-buttonpane button:contains("Edit Event")').button().hide();
                        $('.ui-dialog-buttonpane button:contains("Remove Event")').button().hide();
                        get_ply_projects();
                    },
                    "class": 'button button-primary'
                },
                {
                    text: "Remove Event",
                    click: function(){
                        delete plyEventButton.attributes.onclick;
                        closePlyDialog();
                    },
                    "class": 'button button-primary'
                },
                {
                    text: "Cancel",
                    click: function () {
                        closePlyDialog();
                    }
                }]
        });
    });

<?php if (platformLyisEditInElementor()): ?>
    elementor.hooks.addAction( 'panel/open_editor/widget/text-editor', function( panel, model, view ) {
        setTimeout(function() {
            elementorEditorAddButton();
        }, 500);
    } );
<?php else: ?>
    QTags.addButton('platform_ly_link', 'Platform.ly', showPlyDialogOptions);
<?php endif; ?>

    function getBlockDOMNode(clientId){
        document.querySelector('[data-block="' + clientId + '"]');
    }

    function elementorEditorAddButton() {
        // get an instance of the editor
        var editor = tinymce.activeEditor; //or tinymce.editors[0], or loop, whatever
        var plyButtonName = 'platform_ly_link';

        if (editor.buttons.hasOwnProperty(plyButtonName)) {
            return;
        }

        //add a button to the editor buttons
        editor.addButton(plyButtonName, {
          title: 'Add Platform.ly Link',
          icon: 'icon platform_ly_link_button',
          onclick: function () {
            showPlyDialogOptions();
          }
        });
        //the button now becomes
        var button=editor.buttons[plyButtonName];
        //find the buttongroup in the toolbar found in the panel of the theme
        var bg=editor.theme.panel.find('toolbar buttongroup')[0];
        //without this, the buttons look weird after that
        bg._lastRepaintRect=bg._layoutRect;
        //append the button to the group
        bg.append(button);
    }
</script>
