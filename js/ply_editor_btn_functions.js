(function(){
    var el = wp.element.createElement;
  /*  var iconEl = el('svg', { width: 20, height: 20 },
        el('path', { d: "M12.5,12H12v-0.5c0-0.3-0.2-0.5-0.5-0.5H11V6h1l1-2c-1,0.1-2,0.1-3,0C9.2,3.4,8.6,2.8,8,2V1.5C8,1.2,7.8,1,7.5,1 S7,1.2,7,1.5V2C6.4,2.8,5.8,3.4,5,4C4,4.1,3,4.1,2,4l1,2h1v5c0,0-0.5,0-0.5,0C3.2,11,3,11.2,3,11.5V12H2.5C2.2,12,2,12.2,2,12.5V13 h11v-0.5C13,12.2,12.8,12,12.5,12z M7,11H5V6h2V11z M10,11H8V6h2V11z" } )
      );
 */
    var iconEl = el('span',{className: 'platform_ly_link_button_new'},
        el('i',{
            className: 'platform_ly_link_button'
        })
    );
 
    wp.richText.registerFormatType('platform-ly/link', {
        title: 'Platform Link',
        tagName: 'a',
        attributes: {
                url: 'href',
                onclick: 'onclick',
                target: 'target'
        },
        className: 'platform-ly-link',
        edit: function( props ) {
            var selectedBlock = wp.data.select("core/editor").getSelectedBlock();
            if(selectedBlock.name != 'core/button'){
                return wp.element.createElement(wp.editor.RichTextToolbarButton, {
                    icon: iconEl,
                    title: 'Add Platform.ly Link',
                    onClick: function() {
                        plySelectedWord = props;
                        plyEventButton = {};
                        showPlyDialogOptions();
                        //add_platform_ly_link();
                        /*props.onChange( wp.richText.toggleFormat( props.value, {type: 'platform-ly/link', attributes: {
                            url: '#test',
                        } } ) );*/
                    },
                    isActive: props.isActive
                });
            }else{
               return []; 
            }
        }
    });
function withClientIdClassName(settings, name){
    if(name == 'core/button'){
        settings.attributes['onclick'] = {
            attribute: "onclick",
            selector: "a",
            source: "attribute",
            type: "string"
        };
    }
    return settings;
}
wp.hooks.addFilter('blocks.registerBlockType', 'platform-ly/event', withClientIdClassName);

var withInspectorControls = wp.compose.createHigherOrderComponent(function(BlockEdit){
    return function (props){
        if(props.name == 'core/button'){
            //console.log(props);
            return el(
                wp.element.Fragment,
                {},
                el(
                    BlockEdit,
                    props
                ),
                el(
                    wp.editor.BlockControls,
                    {},
                    el(wp.components.Toolbar, {}, 
                        el(
                            wp.components.ToolbarButton,
                            {
                                //type: 'button',
                                title: 'Add Platform.ly Link',
                                onClick: function() {
                                    plyEventButton = props;
                                    plySelectedWord = {};
                                    showPlyDialogOnlyEventOptions();
                                },
                                icon: iconEl
                            }
                        )
                    )
                )
            );
        }else{
            return el(
                BlockEdit,
                props
            );
        }
    };
}, 'withInspectorControls');

wp.hooks.addFilter( 'editor.BlockEdit', 'platform-ly/event', withInspectorControls );
    
    
    
function saveAttributes(element, blockType, attributes){
    if(blockType.name == 'core/button'){
        var elementAsString = wp.element.renderToString(element);
        if(typeof attributes !== 'undefined'){
            element.props.children.props['onclick'] = attributes.onclick;
        }
        return element;
    }else{
        return element;
    }
};
wp.hooks.addFilter( 'blocks.getSaveElement', 'platform-ly/event', saveAttributes );
    
})(window);
