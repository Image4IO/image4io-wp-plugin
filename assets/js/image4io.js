jQuery(function($) {
    $(document).ready(function(){
        var init=false;
        var rootContainer=$('.image4io-images-container');
        if(rootContainer.length==1||init){
          init=true;
          populateContainer(rootContainer,rootFolder.rootFolder);
          createBreadcrumb(rootFolder.rootFolder)
        }
    });
    function handleDoubleClick(e){
      clearSelection();
      if(e.currentTarget.dataset.type=="folder"){
        //add breadcrumb
        var populatingFolder=e.currentTarget.dataset.parentfolder;
        if(populatingFolder!="/"){
          populatingFolder+='/'+e.currentTarget.dataset.name;
        }else{
          populatingFolder+=e.currentTarget.dataset.name;
        }
        createBreadcrumb(populatingFolder)
        //clear container
        $('.image4io-images-container').empty();
        //populate container with folder
        var rootContainer=$('.image4io-images-container');
        if(e.currentTarget.dataset.parentfolder!="/"){
          populateContainer(rootContainer,e.currentTarget.dataset.parentfolder+'/'+e.currentTarget.dataset.name); 
        }else{
          populateContainer(rootContainer,'/'+e.currentTarget.dataset.name); 
        }
      }else if(e.currentTarget.dataset.type=="img"){
        var name=e.currentTarget.dataset.name;
        $.ajax({
          type:"POST",
          data:{
            action:"image4io_image_selected",
            url:name
          },
          url:ajaxurl,
          success:function(res){
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
      createBreadcrumb(folder)
    }

    function createBreadcrumb(folderName){
      var breadcrumbDiv=$('.image4io-folder-path');
      $('.image4io-folder-path').empty();
      var crumbs=folderName.split('/');
      //console.log(crumbs);
      breadcrumbDiv.append('<a href="#" class="image4io-breadcrumb" data-folder="/">Root</a>');
      var currentLink="";
      crumbs.forEach(currentPath=>{
        //console.log(currentPath);
        if(currentPath!=""){
          currentLink+='/'+currentPath
          breadcrumbDiv.append('<i class="fa fa-angle-right"></i><a href="#" class="image4io-breadcrumb" data-folder="'+currentLink+'">'+currentPath+'</a>');
        }
      })
      $(".image4io-breadcrumb").off("click").on("click",breadcrumbClick);
    }
    
    function populateContainer(rootContainer,parentFolder){
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
                '<div class="frame-img"><img src="'+assetPath.image4io_static_images+'folder.png" alt="'+folder.name+'"></div>'+
                '<h4><b>'+folder.name+'</b></h4>'+
              '</div>'+
            '</div>';
          });
          cardHtml+='</div><div class="image4io-container">';
          images.forEach(image => {
            cardHtml+='<div class="card-panel image4io-card" data-type="img" data-name="'+image.name+'">'+
              '<div class="card-image image-frame image-img">'+
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
