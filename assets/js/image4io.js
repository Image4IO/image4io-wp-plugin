jQuery(function($) {
    $(document).ready(function(){
        var init=false;
        var rootContainer=$('.image4io-images-container');
        
        if(rootContainer||!init){
          //console.log(rootFolder);
          init=true;
          populateContainer(rootContainer,rootFolder.rootFolder);
        }
    });
    function handleDoubleClick(e){
      clearSelection();
      console.log(e);
      if(e.currentTarget.dataset.type=="folder"){
        //add breadcrumb
        var breadcrumbDiv=$('.image4io-folder-path');
        var parent=e.currentTarget.dataset.parentfolder;
        var pattern=/[^/]+(?=\/$|$)/i
        var parentId;
        if(parent=="/"){
          parentId="root";
        }else{
          parentId=parent.match(pattern);
        }
        breadcrumbDiv.append('<a href="#" class="image4io-breadcrumb" id="'+parentId+'" data-folder="'+parent+'">'+parent+'</a>');
        $(".image4io-breadcrumb").off("click").on("click",breadcrumbClick);
        //clear container
        $('.image4io-images-container').empty();
        //populate container with folder
        var rootContainer=$('.image4io-images-container');
        populateContainer(rootContainer,e.currentTarget.dataset.name); 
      }else if(e.currentTarget.dataset.type=="img"){
        var name=e.currentTarget.dataset.name;
        //console.log(wp.media.editor);
        $.ajax({
          type:"POST",
          data:{
            action:"image4io_image_selected",
            url:name
          },
          url:ajaxurl,
          success:function(res){
            console.log(res);
            wp.media.editor.insert(res);
          },
          error:function(e){
            console.log("ERROR:")
            console.log(e)
          }
        })
        tb_remove('','#TB_inline?height=800&width=753&inlineId=image4ioModal&modal=false');

      }
      
      return false;
    }
    function breadcrumbClick(e){
      var folder=e.currentTarget.dataset.folder;
      $('.image4io-images-container').empty();
      var rootContainer=$('.image4io-images-container');
      populateContainer(rootContainer,folder);
      $('#'+e.currentTarget.id).nextAll().remove();
      e.currentTarget.remove();
    }
    
    function populateContainer(rootContainer,parentFolder){
      //console.log(parentFolder);

      $.ajax({
        type:"POST",
        data:{
          action:"image4io_model",
          image4IOFolder: parentFolder
        },
        url:ajaxurl,
        success:function(response){
          //console.log(response);
          var data=JSON.parse(response);
          var folders=data.folders;
          var images=data.files;
          var cardHtml="<div class='image4io-container'>";
          folders.forEach(folder=>{
            cardHtml+='<div class="card-panel image4io-card" data-type="folder" data-parentFolder="'+parentFolder+'" data-name="'+folder.name+'">'+
              '<div class="card-image image-frame image-folder">'+
                '<div class="frame-img"><img src="'+assetPath.staticImages+'folder.png" alt="'+folder.name+'"></div>'+
                '<h4><b>'+folder.name+'</b></h4>'+
              '</div>'+
            '</div>';
          });
          cardHtml+='</div><div class="image4io-container">';
          images.forEach(image => {
            cardHtml+='<div class="card-panel image4io-card" data-type="img" data-name="'+image.name+'">'+
              '<div class="card-image image-frame">'+
                '<div class="frame-img"><img src="https://cdn.image4.io/i4io/w_64,f_auto'+image.name+'" alt="'+image.name.substring(1)+'"></div>'+
                '<h4><b>'+image.orginal_name+'</b></h4>'+
              '</div>'+
            '</div>';
          });
          cardHtml+="</div>"
          rootContainer.append(cardHtml);
          var imgFrames=$('.image4io-card');
          imgFrames.off("dblclick").on("dblclick", handleDoubleClick);
        },
        error:function(e){
          console.log("ERROR:")
          console.log(e)
        }
      });
    }
    
    function clearSelection() {
      if(document.selection && document.selection.empty) {
          document.selection.empty();
      } else if(window.getSelection) {
          var sel = window.getSelection();
          sel.removeAllRanges();
      }
  }
});
