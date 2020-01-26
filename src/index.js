/*const {registerBlockType} = wp.blocks;
const {InspectorControls,MediaUpload}=wp.editor;
const {PanelBody, TextControl }=wp.components;
const {registerStore,withSelect} = wp.data;

jQuery(function($) {
    function returnImage4ioUrl(src,w,h){
        $.ajax({
            type:"POST",
            data:{
              action:"return_image_url",
              url:src,
              width:w,
              height:h
            },
            url:ajaxurl,
            success:function(res){
              console.log(res);
              return res;
            },
            error:function(e){
              console.log("ERROR:")
              console.log(e)
            }
        })
    }
registerBlockType('image4io/image4io-block', {
    title:'Image4io',
    description: 'Block to generate images from image4io',
    icon: 'format-image',
    category: 'layout',

    //custom attributes
    attributes:{
        url:{
            type:"string",
            default:null
        },
        src:{
            type:"string",
            default:""
        },
        url:{
            type:"string",
            source:"attribute",
            selector:"img",
            attribute:"src",
            default:null
        }, 
        width:{
            type:"string",
            default:"500"
        },
        height:{
            type:"string",
            default:"500"
        },
        size:{
            type:"string",
            default:"large"
        }
    },

    //built-in functions
    edit({attributes,setAttributes}){
        const {
            src,url,width,height,size
        }=attributes;

        //custom functions
        function onWidthChange(newSize){
            setAttributes({width: newSize});
        }
        function onSrcChange(newUrl){
            setAttributes({src:newUrl});
        }
        function onHeightChange(newSize){
            setAttributes({height: newSize});
        }

        var result=returnImage4ioUrl(src,width,height); 
        console.log(result);
        return ([
            <InspectorControls style={{marginBottom:"40px"}}>
                <PanelBody title={ 'Image Settings' }>
                    <TextControl 
                        label="Source (required)"
                        value={url}
                        onChange={onUrlChange}
                    />
                    <TextControl 
                        label="Width"
                        value={width}
                        onChange={onWidthChange}
                    />
                    <TextControl 
                        label="Height"
                        value={height}
                        onChange={onHeightChange}
                    />
                </PanelBody>
            </InspectorControls>,
            <img src={result} width={width} height={height}></img>
        ]); 
    },

    save({attributes}){
        const {
            url,width,height
        }=attributes;

        

        return(
            <img src={url} width={width} height={height}></img>
        )
    }

})
});*/