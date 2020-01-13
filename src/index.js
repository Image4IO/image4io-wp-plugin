const {registerBlockType} = wp.blocks;
const {InspectorControls,MediaUpload}=wp.editor;
const {PanelBody, TextControl }=wp.components;

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
        /*url:{
            type:"string",
            source:"attribute",
            selector:"img",
            attribute:"src",
            default:null
        }, */
        width:{
            type:"string",
            default:"500"
        },
        height:{
            type:"string",
            default:null
        },
        size:{
            type:"string",
            default:"large"
        }
    },

    //built-in functions
    edit({attributes,setAttributes}){
        const {
            url,width,height,size
        }=attributes;

        //custom functions
        function onWidthChange(newSize){
            setAttributes({size: newSize});
        }
        function onUrlChange(newUrl){
            setAttributes({url:newUrl});
        }

        return ([
            <InspectorControls style={{marginBottom:"40px"}}>
                <PanelBody title={ 'Image Settings' }>
                    <TextControl 
                        label="Url (required)"
                        value={url}
                        onChange={onUrlChange}
                    />
                    
                    <TextControl 
                        label="Width"
                        value={width}
                        onChange={onWidthChange}
                    />
                </PanelBody>
            </InspectorControls>,
            <img src="url"></img>
        ]); 
    },

    save({attributes}){
        const {
            url,width,height,size
        }=attributes;

        return(
            <img src={url} width={width}></img>
        )
    }

})