jQuery(function($) {
    $(document).ready(function(){
        
        register_image4io_edit_button();
    });

    function register_image4io_edit_button(){
        var buttons = $('.mce-toolbar-grp.mce-inline-toolbar-grp.mce-container.mce-panel');
        
        var inlineButton=$('<img />').attr({src:args.plugin_url+"assets/img/edit-button.png", width: "24", height: "24", title: "Image4io Edit Image"});
        buttons.append(inlineButton);
        console.log(buttons);
        inlineButton.click(function(e){
            console.log(e);
            console.log(tinyMCE.activeEditor.selection.getContent({format : 'html'}));
        });
    }
    
});