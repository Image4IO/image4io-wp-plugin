var bootstrapCss = 'bootstrapCss';
if (!document.getElementById(bootstrapCss))
{
    var head = document.getElementsByTagName('head')[0];
    var bootstrapWrapper = document.createElement('link');
    bootstrapWrapper.id = bootstrapCss;
    bootstrapWrapper.rel = 'stylesheet/less';
    bootstrapWrapper.type = 'text/css';
    bootstrapWrapper.href = '../wp-content/plugins/image4io-plugin/assets/bootstrap-wrapper.less';
    bootstrapWrapper.media = 'all';
    head.appendChild(bootstrapWrapper);
	
    var lessjs = document.createElement('script');
    lessjs.type = 'text/javascript';
    lessjs.src = '../wp-content/plugins/image4io-plugin/assets/less.min.js';
    head.appendChild(lessjs);

    var bootstrapJs=document.createElement('script');
    bootstrapJs.type='text/javascript';
    bootstrapJs.src="../wp-content/plugins/image4io-plugin/assets/bootstrap/js/bootstrap.bundle.min.js";
    head.appendChild(bootstrapJs);

    //load other stylesheets that override bootstrap styles here, using the same technique from above
    
    /*var customStyles = document.createElement('link');
    customStyles.id = "customStyles";
    customStyles.rel = 'stylesheet';
    customStyles.type = 'text/css';
    customStyles.href = '../wp-content/plugins/image4io-plugin/assets/styles.css';
    customStyles.media = 'all';
    head.appendChild(customStyles);*/

    jQuery('#exampleModal').modal('show');
}