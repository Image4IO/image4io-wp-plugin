<?php
/**
 * @package image4ioPlugin
 */

 namespace Image4io\Manager;

 use Image4io\Base\BaseController;
 use Image4io\Api\Image4IOManager;

 class MediaManager extends BaseController{
     
    public function register(){
        $this->hookUI();
    }

    public function hookUI(){
        $this->mediaHook();
        //$this->mceButtonHook();
    }

    public function mceButtonHook(){
        add_action('admin_enqueue_scripts', array($this,'mceButtonEnqueue'));
    }

    public function mceButtonEnqueue(){
        wp_enqueue_script( 'image4io_mce_button_script', $this->plugin_url . 'assets/js/image4io-mce-button.js');
        $args=array('plugin_url'=> $this->plugin_url);
        wp_localize_script( 'image4io_mce_button_script', 'args', $args );
    }

    public function mediaHook(){
        //add_action("init",array($this,"init_image4io_shortcode"));
        
        add_filter('manage_media_columns', array($this, 'add_image4io_media_column'));
        add_action('manage_media_custom_column', array($this, 'image4io_media_column_value'), 0, 2);
        add_action('admin_footer-upload.php', array($this, 'image4io_media_lib_upload_admin_footer'));
        add_action('load-upload.php', array($this, 'image4io_media_lib_upload_action'));
        //add_action('delete_attachment',array($this,'image4io_media_delete'),1,1);
        add_filter('wp_generate_attachment_metadata',array($this,'image4io_upload_new_media_action'),1,3);
        add_filter('wp_get_attachment_url', array($this, 'fix_local_url_to_image4io'), 1, 2);
        add_filter('image_downsize', array($this, 'image4io_resize'), 1, 3);
        add_filter('the_content',array($this,'image4io_make_content_responsive'));
        add_action('admin_notices',array($this,'image4io_upload_notices'));
        add_action('admin_enqueue_scripts', array($this,'mediaButtonEnqueue'));

        add_action('pre_update_option_image4io_settings',array($this,'check_image4io_options'),1,3);
        
        //add_action('media_buttons', array($this, 'mediaImage4io'), 11);
        //add_action('wp_ajax_image4io_image_selected',array($this,'imageSelected'));
        //add_action('wp_ajax_image4io_model',array($this, 'getImagesByFolder'));
        add_action('wp_ajax_image4io_migrate_from',array($this,'migrate_from_image4io'));
        add_action('wp_ajax_image4io_migrate_to',array($this,'migrate_to_image4io'));

        add_filter( 'http_request_timeout', array($this, 'image4io_timeout_extend'));
    }

    public static function init_image4io_shortcode(){
        add_shortcode( "image4io", array($this,"image4io_shortcode"));
    }

    public static function image4io_shortcode($atts){
        $defaultSizes=$this->get_wp_sizes();
        $a=shortcode_atts(array(
            'src'=>"",
            'alt'=>"",
            'width'=>0,
            'height'=>0,
            'size'=>"large"
        ), $atts);
        if($a['src']==""){
            return;
        }

        if($a['width']!=0&&$a['height']!=0){
            $url=$this->createImage4ioUrl($a['src'],$a['width'],$a['height']);
            return "<div class='image4io-parent'><img class='shortcode-image' src='$url' style='width: ". $a['width'] ."px;height: ". $a['height'] ."'></img></div>";
        }elseif($a['width']!=0){
            $url=$this->createImage4ioUrl($a['src'],$a['width'],0);
            return "<div class='image4io-parent'><img class='shortcode-image' src='$url' style='width: ". $a['width'] ."px;height:auto;'></img></div>";
        }elseif($a['height']!=0){
            $url=$this->createImage4ioUrl($a['src'],0,$a['height']);
            return "<div class='image4io-parent'><img class='shortcode-image' src='$url' style='height: ". $a['height'] ."px;width:auto;'></img></div>";
        }else{
            if(!isset($defaultSizes[$a['size']])){
                return;
            }
            $size=$defaultSizes[$a['size']];
            $url=$this->createImage4ioUrl($a['src'],$size['width'],$size['height']);
            return "<div class='image4io-parent'><img class='shortcode-image' src='$url' style='width: ". $size['width'] ."px;height:auto;'></img></div>";
        }
    }

    public function createImage4ioUrl($src,$width,$height){
        $options=get_option("image4io_settings");
        $cloudname=$options['cloudname'];
        //return $cloudname;
        if(!isset($cloudname)||$cloudname==""){
            return false;
        }

        if($width!=0&&$height!=0){
            return "https://cdn.image4.io/" . $cloudname . "/f_auto,c_fit,w_" . $width . ",h_" . $height . $src;
        }elseif($width!=0){
            return "https://cdn.image4.io/" . $cloudname . "/f_auto,c_fit,w_" . $width . $src;
        }else{
            return "https://cdn.image4.io/" . $cloudname . "/f_auto,c_fit,h_" . $height . $src;
        }
    }

    public function createImage4ioUrlWithTransformation($src,$transformation){
        $options=get_option("image4io_settings");
        $cloudname=$options['cloudname'];
        //return $cloudname;
        if(!isset($cloudname)||$cloudname==""){
            return false;
        }
        return "https://cdn.image4.io/" . $cloudname . "/" .$transformation . $src;
    }

    public function mediaImage4io(){

        echo '<a href="#TB_inline?height=800&width=753&inlineId=image4ioModal&modal=false" class="thickbox button">Add Image4IO</a>';
        add_action( 'admin_footer', array($this,'prepare_thickbox_modal'));
        return null;
    }

    public function prepare_thickbox_modal(){
        require_once("$this->plugin_path/templates/media.php");
    }

    public function mediaButtonEnqueue(){
        wp_enqueue_script( 'image4io_script', $this->plugin_url . 'assets/js/image4io.js' );
        $options=get_option( "image4io_settings");
        $rootFolder=array('rootFolder'=> isset($options['folder'])?$options['folder']:"/" );
        wp_localize_script( 'image4io_script', 'rootFolder', $rootFolder );
    }

    public function getImagesByFolder(){
        if(isset( $_POST['image4IOFolder'] )) {
            $folder=esc_url($_POST['image4IOFolder']);
            $manager = new Image4IOManager;
            $manager->setup();
            $error=$manager->validateCredentialsWithOptions();
            if(isset($error)){
                return $error;
            }
            $result = $manager->getImagesByFolder($folder);
            try {
                $result = _wp_json_sanity_check( $result, 512 );
            } catch ( Exception $e ) {
                wp_die();
            }
            echo $result;
            wp_die();
       }
       wp_die();
    }

    public function imageSelected(){
        if(isset($_POST['url'])){
            $name=esc_url( $_POST['url'] );
            $buildedHtml="<!--wp:shortcode -->\n[image4io src='$name']\n<!--/wp:shortcode-->\n";
            $buildedHtml=wp_check_invalid_utf8($buildedHtml);
            echo $buildedHtml;
        }
        wp_die();
    }

    public function get_wp_sizes(){
        if (isset($this->sizes)) {
            return $this->sizes;
        }
        // make thumbnails and other intermediate sizes
        global $_wp_additional_image_sizes;
        $sizes = array();

        foreach (get_intermediate_image_sizes() as $s) {
            $sizes[$s] = array('width' => '', 'height' => '', 'crop' => false);
            if (isset($_wp_additional_image_sizes[$s]['width'])) {
                $sizes[$s]['width'] = intval($_wp_additional_image_sizes[$s]['width']);
            } // For theme-added sizes
            else {
                $sizes[$s]['width'] = get_option("{$s}_size_w");
            } // For default sizes set in options
            if (isset($_wp_additional_image_sizes[$s]['height'])) {
                $sizes[$s]['height'] = intval($_wp_additional_image_sizes[$s]['height']);
            } // For theme-added sizes
            else {
                $sizes[$s]['height'] = get_option("{$s}_size_h");
            } // For default sizes set in options
            if (isset($_wp_additional_image_sizes[$s]['crop'])) {
                $sizes[$s]['crop'] = intval($_wp_additional_image_sizes[$s]['crop']);
            } // For theme-added sizes
            else {
                $sizes[$s]['crop'] = get_option("{$s}_crop");
            } // For default sizes set in options
        }

        $this->sizes = apply_filters('intermediate_image_sizes_advanced', $sizes);

        return $this->sizes;
    }

    public function add_image4io_media_column($cols){
        $cols['image4io_column'] = 'Image4io';
        return $cols;
    }

    public function image4io_media_column_value($column_name, $attachment_id){
        if ('image4io_column' == $column_name) {
            $metadata = wp_get_attachment_metadata($attachment_id);
            $isImage4io=isset($metadata['image4io'])?$metadata['image4io']:null;
            if(is_array($metadata)&&$isImage4io){ //<img src='$src' style='vertical-align: middle;' width='24' height='24'/>
                echo "<div style='line-height: 24px;'>Uploaded</div>";
                $action_url = wp_nonce_url('?', 'image4io-media');
                echo "<a href='$action_url&image4io_upload=$attachment_id&image4io_undo=1'>Undo</a>";
            }else{
                $action_url = wp_nonce_url('?', 'image4io-media');
                echo "<a href='$action_url&image4io_upload=$attachment_id'>Upload to Image4io</a>";
            }
        }
    }

    public function image4io_media_lib_upload_admin_footer(){
        $loader=$this->plugin_url . "assets/img/ajax-loader.gif";
        require_once("$this->plugin_path/templates/loading.php");
        ?>
        <script type="text/javascript">
            jQuery(function($) {
                $(document).ready(function(){
                    add_bulk_option();
                });

                function add_bulk_option(){
                    var buttons=$("select[name='action'],select[name='action2']");
                    if(buttons.length>0){
                        buttons.each(function() {
                            $('<option>').val('image4io_upload').text('Upload to Image4io').appendTo(this);
                            $('<option>').val('image4io_undo').text('Migrate from Image4io').appendTo(this);
                        });
                    }else{
                        setTimeout(add_bulk_option, 10);
                    }
                }

                function show_loader(){
                    tb_show('','#TB_inline?height=100&width=150&inlineId=loadingModal&modal=true');
                }
            });
        </script>
        <?php
        
    }
    
    public function image4io_upload_animation_show(){
        ?>
        <script type="text/javascript">
            jQuery(document).ready(function() {
                tb_show('','#TB_inline?height=100&width=150&inlineId=loadingModal&modal=true');
            });
        </script>
        <?php
    }

    public function image4io_upload_animation_remove(){
        ?>
        <script type="text/javascript">
            jQuery(document).ready(function() {
                console.log('remove')
                tb_remove();
            });
        </script>
        <?php
    }
    public function migrate_from_image4io(){
        $args = array('post_type'=>'attachment','numberposts'=>null,'post_status'=>null);
        $attachments = get_posts($args);
        if($attachments){
            foreach($attachments as $attachment){
                $this->undo_image4io($attachment->ID);
            }
        }
        wp_die();
    }

    public function migrate_to_image4io(){
        $args = array('post_type'=>'attachment','numberposts'=>null,'post_status'=>null);
        $attachments = get_posts($args);
        if($attachments){
            foreach($attachments as $attachment){
                $this->upload_to_image4io($attachment->ID);
            }
        }
        wp_die();
    }

    public function image4io_media_lib_upload_action(){
        $sendback = wp_get_referer();

        global $pagenow;
        if ('upload.php' == $pagenow && isset($_REQUEST['image4io_upload']) && (int) $_REQUEST['image4io_upload']) {
            
            check_admin_referer('image4io-media');
            $result=null;
            if(isset($_REQUEST['image4io_undo'])&&$_REQUEST['image4io_undo']==1){
                $result=$this->undo_image4io($_REQUEST['image4io_upload']);
                if($result){
                    $message=$result;
                    $sendback=add_query_arg(array('image4io_message'=>urlencode($message),'image4io_error'=>true),$sendback);
                }else{
                    $message="Media is loaded to Wordpress.";
                    $sendback=add_query_arg(array('image4io_message'=>urlencode($message)),$sendback);
                }
                //return to media library
                wp_redirect($sendback);
                exit();
            }

            //upload to image4io
            $result=$this->upload_to_image4io($_REQUEST['image4io_upload']);
            //setup message
            if($result){
                $message=$result;
                $sendback=add_query_arg(array('image4io_message'=>urlencode($message),'image4io_error'=>true),$sendback);
            }else{
                $message="Media is uploaded to image4io.";
                $sendback=add_query_arg(array('image4io_message'=>urlencode($message)),$sendback);
            }
            //return to media library
            wp_redirect($sendback);
            exit();
        }
        
        //bulk action
        $wp_list_table = _get_list_table('WP_Media_List_Table');
        $action = $wp_list_table->current_action();
        if($action=='image4io_upload'){

            check_admin_referer('bulk-media');

            $post_ids = array();
            if (isset($_REQUEST['media'])) {
                $post_ids = $_REQUEST['media'];
            } elseif (isset($_REQUEST['ids'])) {
                $post_ids = explode(',', $_REQUEST['ids']);
            }
            $results=array();
            foreach($post_ids as $k=>$post_id){
                $res=$this->upload_to_image4io($post_id);
                if($res){
                    $results[$post_id]=$res;
                }
            }
            $message="";
            if(count($results)>0){
                foreach($results as $id=>$result){
                    $message .= " Filename: " . $id . "; Error: " . $result . "\n";
                }
                $message=rtrim($message,'\n');
                $message= "There are some errors while uploading to image4io.\n" . $message;
                $sendback=add_query_arg( array('image4io_message'=>urlencode($message),'image4io_error'=>true),$sendback );
            }else{
                $message="All selected images have uploaded to image4io successfully.";
                $sendback=add_query_arg(array('image4io_message'=>urlencode($message)),$sendback);
            }
            wp_redirect($sendback);
            exit();
        }else if($action=='image4io_undo'){
            check_admin_referer('bulk-media');
            $post_ids = array();
            if (isset($_REQUEST['media'])) {
                $post_ids = $_REQUEST['media'];
            } elseif (isset($_REQUEST['ids'])) {
                $post_ids = explode(',', $_REQUEST['ids']);
            }
            $results=array();
            foreach($post_ids as $k=>$post_id){
                $res=$this->undo_image4io($post_id);
                if($res){
                    $results[$post_id]=$res;
                }
            }
            $message="";
            if(count($results)>0){
                foreach($results as $id=>$result){
                    $message .= " Filename: " . $id . "; Error: " . $result . "\n";
                }
                $message=rtrim($message,'\n');
                $message= "There are some errors while migrating from image4io.\n" . $message;
                $sendback=add_query_arg( array('image4io_message'=>urlencode($message),'image4io_error'=>true),$sendback );
            }else{
                $message="All selected images have migrated from image4io successfully.";
                $sendback=add_query_arg(array('image4io_message'=>urlencode($message)),$sendback);
            }
            wp_redirect($sendback);
            exit();
        }
    }

    public function undo_image4io($attachment_id){
        $md = wp_get_attachment_metadata($attachment_id);
        if(!$md['image4io']){
            return 'Already loaded to Wordpress';
        }

        $attachment = get_post($attachment_id);
        $old_url = wp_get_attachment_url($attachment_id);

        $error=$this->unregister_image($attachment_id,$attachment,$old_url,$md['original_url']);
        if($error){
            return $error;
        }

        $name=$md['image4io_name'];
        $manager = new Image4IOManager;
        $manager->setup();
        $error=$manager->validateCredentialsWithOptions();
        if(isset($error)){
            return $error;
        }
        $result = $manager->deleteImage($name);

        $this->update_image_src_all($attachment_id, $old_url, $md['original_url']);
    }

    public function upload_to_image4io($attachment_id){
        $md = wp_get_attachment_metadata($attachment_id);
        if(isset($md['image4io'])&&$md['image4io']){
            return 'Already uploaded to Image4io';
        }

        $attachment = get_post($attachment_id);

        $mime_type=$attachment->post_mime_type;
        if(!preg_match( '!^image/!', $mime_type )){
            return 'Unsupported file type: ' . $mime_type ;
        }

        //$full_path = $attachment->guid;
        //$full_path = wp_get_original_image_url($attachment_id);
        $full_path=wp_get_attachment_image_src($attachment_id,"attached-image")[0];

        if(preg_match('#^.*?/(https?://.*)#', $full_path, $matches)){
            $full_path=$matches[1];
        }
        
        if (empty($full_path)) {
            return 'Unsupported attachment type!';
        }

        $full_path = $this->update_urls_for_ssl($full_path);

        $target_path = "/";
        $values=get_option( "image4io_settings" );
        if(isset($values["target_folder"])){
            $target_path=$values["target_folder"];
        }

        $manager = new Image4IOManager;
        $manager->setup();
        $error=$manager->validateCredentialsWithOptions();
        if(isset($error)){
            return $error;
        }
        $result = $manager->uploadToImage4ioFromUrl($full_path,$target_path);
        
        if(!isset($result->fetchedImage)){
            return "Cannot upload to image4io server! File: " . $attachment->$post_title;
        }
        $name=$result->fetchedImage->name;
        $url= $this->build_url_from_name($name);

        $old_url = wp_get_attachment_url($attachment_id);
        
        $res= $this->register_image($name, $url, $attachment->post_parent, $attachment_id, $attachment);
        if(!$res){
            return "Cannot register image! File: " . $attachment->$post_title;
        }
        $this->update_image_src_all($attachment_id, $old_url, $url);
        return null;
    }

    public function register_image($name, $url, $post_id, $attachment_id, $original_attachment){
        $info = pathinfo($url);
        $public_id = $info['filename'];
        $mime_types = array('png' => 'image/png','jpeg' => 'image/jpeg', 'jpg' => 'image/jpeg', 'bmp' => 'image/bmp');
        $type = $mime_types[strtolower($info['extension'])];
        
        $md = wp_get_attachment_metadata($attachment_id);
        
        $meta = $md['image_meta'];
        $title = $original_attachment->post_title;
        $caption = $original_attachment->post_content;
        
        $sizes=$md['sizes'];
        $image4io_sizes=array();
        foreach($sizes as $key=>$size){
            $i_size=array(
                'file'=>$this->createImage4ioUrl($name,$size['width'],0),
                'width'=>(int)$size['width'],
                'height'=>(int)$size['height'],
                'mime-type'=>$size['mime-type']
            );
            $image4io_sizes[$key]=$i_size;
        }

        if (!$title) {
            $title = $public_id;
        }
        if (!$caption) {
            $caption = '';
        }
        if (!$meta) {
            $meta = array(
                  'aperture' => 0,
                  'credit' => '',
                  'camera' => '',
                  'caption' => $caption,
                  'created_timestamp' => 0,
                  'copyright' => '',
                  'focal_length' => 0,
                  'iso' => 0,
                  'shutter_speed' => 0,
                  'title' => $title, );
        }

        $attachment = array(
            'post_mime_type' => $type,
            'guid' => $url,
            'post_parent' => $post_id,
            'post_title' => $title,
            'post_content' => $caption, );
        if ($attachment_id && is_numeric($attachment_id)) {
            $attachment['ID'] = intval($attachment_id);
        }
        // Save the data
        $id = wp_insert_attachment($attachment, $url, $post_id);
        if (!is_wp_error($id)) {
            $md['image4io']=true;
            $md['image4io_name']=$name;
            $md['image4io_sizes']=$image4io_sizes;
            $md['original_url']=$original_attachment->guid;
            wp_update_attachment_metadata($id, $md);
        }
        
        return $id;
    }

    public function unregister_image($attachment_id, $attachment, $old_url, $new_url){
        $new_attachment = array(
            'ID'=>$attachment_id,
            'post_mime_type' => $attachment->post_mime_type,
            'guid' => $new_url,
            'post_parent' => $attachment->post_parent,
            'post_title' => $attachment->post_title,
            'post_content' => $attachment->post_content, 
        );
        $id = wp_insert_attachment($new_attachment, $new_url, $attachment->post_parent);
        if (!is_wp_error($id)) {
            $md = wp_get_attachment_metadata($attachment_id);
            $md['image4io']=false;
            $md['image4io_name']="";
            $md['image4io_sizes']=array();
            $md['original_url']="";
            wp_update_attachment_metadata($id, $md);
            return null;
        }else{
            return "Cannot register image";
        }
    }

    public function update_image_src_all($attachment_id,  $old_url, $new_url){
        $query = new \WP_Query(
            array(
                'post_type' => 'any',
                'post_status' => 'publish,pending,draft,auto-draft,future,private',
                's' => "wp-image-{$attachment_id}",
            )
        );

        while ($query->have_posts()) {
            $query->the_post();
            $this->update_image_src($query->post, $attachment_id, $old_url, $new_url);
        }
    }

    public function update_image_src($post, $attachment_id, $old_url, $new_url){
        $sizes=$this->get_wp_sizes();
        $post_content = $post->post_content;
        $metadata = wp_get_attachment_metadata($attachment_id);
        preg_match_all('/<img [^>]+>/', $post->post_content, $images);
        foreach ($images[0] as $img) {
            if (preg_match('/class *= *["\']([^"\']+)["\']/i', $img, $class) && preg_match('/wp-image-([0-9]+)/i', $class[1], $id) && $id[1] == $attachment_id) {
                $wanted_size = null;
                if (preg_match('/size-([a-zA-Z0-9_\-]+)/i', $class[1], $size)) {
                    if (isset($sizes[$size[1]])) {
                        $wanted_size = $size[1];
                    } elseif ('full' == $size[1]) {
                        // default url requested
                    } else {
                        // Unknown transformation.
                        // TODO build url from width and height values
                        continue;
                    }
                }
                if (preg_match('/src *= *["\']([^"\']+)["\']/i', $img, $src)) {
                    
                    // Migrate In
                    if(preg_match('/^.*?cdn\.image4.*/i',$new_url)>=1){
                        list($new_img_src) = $this->build_resize_url($new_url, $metadata , $wanted_size);
                        if ($new_img_src) {
                            $post_content = str_replace($src[1], $new_img_src, $post_content);
                        }
                    //Migrate Out
                    }elseif(preg_match('/^.*?cdn\.image4.*/i',$old_url)>=1){
                        if(!$wanted_size){
                            $post_content=str_replace($src[1], $new_url, $post_content);
                        }
                        list($new_img_src)=image_downsize( $attachment_id, $wanted_size );
                        if ($new_img_src) {
                            $post_content = str_replace($src[1], $new_img_src, $post_content);
                        }
                    }
                }
            }
            // Also replace original link with new link, for hrefs
            $post_content = str_replace($old_url, $new_url, $post_content);
        }
        if ($post_content != $post->post_content) {
            return wp_update_post(array('post_content' => $post_content, 'ID' => $post->ID));
        }

        return false;
    }

    public function build_url_from_name($name){
        $values=get_option( "image4io_settings" );
        if(!isset($values)||!isset($values["cloudname"])){
            return null;
        }
        return "https://cdn.image4.io/" . $values["cloudname"] . $name;
    }

    public function fix_local_url_to_image4io($url,$post_id){
        $metadata = wp_get_attachment_metadata($post_id);
        if (isset($metadata['image4io']) && $metadata['image4io'] && preg_match('#^.*?/(https?://.*)#', $url, $matches)) {
            return $matches[1];
        }
        return $url;
    }
    
    public function image4io_resize($is_downsize, $post_id, $size){
        $url = wp_get_attachment_url($post_id);
        $metadata = wp_get_attachment_metadata($post_id);
        if (!isset($metadata['image4io']) || !$metadata['image4io']) {
            return;
        }

        return $this->build_resize_url($url, $metadata, $size);
    }

    public function build_resize_url($url, $metadata, $size){
        if (!$size) {
            return array($url, $metadata['width'], $metadata['height'], false);
        }
        
        if (is_string($size)) {
            $available_sizes = $this->get_wp_sizes();
            
            if (!array_key_exists($size, $available_sizes)) {
                return array($url, $metadata['width'], $metadata['height'], false);
            }

            $wanted_size = $available_sizes[$size];
            $crop = $wanted_size['crop'];
        } else if (is_array($size)) {
            $wanted_size = array('width' => $size[0], 'height' => $size[1]);
            $crop = false;
        } else if(is_int($size)){
            $wanted_size = array('width'=> $size,'height'=>$size);
            //return false;
        }else{
            // Unsupported argument
            return false;
        }
        //TODO
        /*
        $transformation = '';
        $src_w = $dst_w = $metadata['width'];
        $src_h = $dst_h = $metadata['height'];
        if ($crop) {
            $resized = image_resize_dimensions($metadata['width'], $metadata['height'], $wanted_size['width'], $wanted_size['height'], true);
            if ($resized) {
                list($dst_x, $dst_y, $src_x, $src_y, $dst_w, $dst_h, $src_w, $src_h) = $resized;
                $transformation = "f_auto,c_crop,h_$src_h,w_$src_w,x_$src_x,y_$src_y/";



                return $this->createImage4ioUrlWithTransformation()
            }
            
        }*/

        //TODO After chain manuplation
        /*list($width, $height) = image_constrain_size_for_editor($dst_w, $dst_h, $size);
        if ($width != $src_w || $height != $src_h) {
            $transformation = $transformation."h_$height,w_$width/";
        }*/
        //preg_match('/^.+?[^\/:](?=[?\/]|$)/', $input_line, $output_array);
        //$url="https://cdn.image4.io/i4io/f_auto,h_450,w_900/692df81d-227f-46d5-aed3-5ec2ff76543a.png";
        $parsed_url=parse_url($url);
        $parsed_url=explode('/',$parsed_url["path"]);
        
        $result_url="";
        if(count($parsed_url)==4){
            $result_url="https://cdn.image4.io/" . $parsed_url[1] . "/f_auto,c_fit,w_" . $wanted_size['width'] . "/" . $parsed_url[3];
        }else if(count($parsed_url)==3){
            $result_url="https://cdn.image4.io/" . $parsed_url[1] . "/f_auto,c_fit,w_". $wanted_size['width'] . "/" . $parsed_url[2];
        }else{
            return false;
        }
        /*
        foreach($parsed_url as $idx=>$part){
            if($idx==4){
                //replace with new one
                $result_url=$result_url . "f_auto,c_fit,w_" . $wanted_size['width'] . "/";
                if(preg_match("((\w)_([0-9a-zA-Z]),?)",$part)){
                    
                   continue;
                }
            }
            $result_url=$result_url . $part . '/';
        }*/
        return array($result_url, $wanted_size['width'], $wanted_size['height'], true);
    }

    public function image4io_make_content_responsive($content){
        
        if ( ! preg_match_all( '/<img [^>]+>/', $content, $matches ) ) {
            return $content;
        }
        $selected_images = array();
	    $attachment_ids  = array();
        foreach ( $matches[0] as $image ) {
            if ( false === strpos( $image, ' srcset=' ) && preg_match('/^.*?cdn\.image4.*/i',$image)>=1 && preg_match( '/wp-image-([0-9]+)/i', $image, $class_id ) ) {
                $attachment_id = absint( $class_id[1] );
    
                if ( $attachment_id ) {
                    /*
                     * If exactly the same image tag is used more than once, overwrite it.
                     * All identical tags will be replaced later with 'str_replace()'.
                     */
                    $selected_images[ $image ] = $attachment_id;
                    // Overwrite the ID when the same image is included more than once.
                    $attachment_ids[ $attachment_id ] = true;

                }
            }
        }
        
        if ( count( $attachment_ids ) > 1 ) {
            /*
             * Warm the object cache with post and meta information for all found
             * images to avoid making individual database calls.
             */
            _prime_post_caches( array_keys( $attachment_ids ), false, true );
        }
        foreach ( $selected_images as $image => $attachment_id ) {
            $image_meta = wp_get_attachment_metadata( $attachment_id );
            $updatedSrc=$this->image4io_generate_image_srcset( $image, $image_meta, $attachment_id );
            $content    = str_replace( $image, $updatedSrc , $content );
        }
    
        return $content;
    }
    
    public function image4io_generate_image_srcset($image_src, $image_meta, $attachment_id){
        $sizes=$image_meta['image4io_sizes'];
        
        if(! is_array( $sizes ) || count( $sizes ) < 1){
            return $image_src;
        }
        $results=array();
        
        foreach($sizes as $size){
            $result=array(
                'url'=>$size['file'],
                'descriptor'=>'w',
                'value'=>$size['width'],
            );
            $results[$size['width']]=$result;
        }

        $srcset = '';
        
        foreach ( $results as $result ) {
            $srcset .= str_replace( ' ', '%20', $result['url'] ) . ' ' . $result['value'] . $result['descriptor'] . ', ';
        }
        $srcset = rtrim($srcset, ', ');
        $srcset_sizes= wp_calculate_image_sizes( array($image_meta["width"],$image_meta["height"]), $image_src, $image_meta, $attachment_id );
        
        preg_match('/(^.*?src=\"[^"]+")(.*)/i',$image_src,$res);

        return $res[1] . ' srcset="' . $srcset . '" sizes="' . $srcset_sizes . '"' . $res[2];
    }

    public function image4io_timeout_extend( $time){
        return 30;
    }

    public function image4io_upload_notices(){
        global $post_type, $pagenow;
        
        if ('upload.php' == $pagenow && isset($_REQUEST['image4io_message'])) {
            if(isset($_REQUEST['image4io_error'])&&$_REQUEST['image4io_error']){
                $message = htmlentities($_REQUEST['image4io_message'], ENT_NOQUOTES);
                echo "<div class='error notice is-dismissible'><p>{$message}</p></div>";
                return;
            }else{
                $message = htmlentities($_REQUEST['image4io_message'], ENT_NOQUOTES);
                echo "<div class='updated notice is-dismissible'><p>{$message}</p></div>";
                return;
            }
        }
    }

    public function image4io_upload_new_media_action($metadata,$attachment_id,$context){
        $attachment = get_post($attachment_id);

        if($context=="create"){

            $values=get_option( "image4io_settings" );
            if(!isset($values)||!isset($values["auto_upload"])||!$values["auto_upload"]){
                return $metadata;
            }
            
            $mime_type=$attachment->post_mime_type;
            if(!preg_match( '!^image/!', $mime_type )){
                return $metadata;
            }
            $full_path = $attachment->guid;
            if (empty($full_path)) {
                return $metadata;
            }

            $full_path = $this->update_urls_for_ssl($full_path);

            $manager = new Image4IOManager;
            $manager->setup();
            $error=$manager->validateCredentialsWithOptions();
            if(isset($error)){
                return $error;
            }
            $result = $manager->uploadToImage4ioFromUrl($full_path,"/");
            
            if(!isset($result->fetchedImage)){
                return $metadata;
            }
            $name=$result->fetchedImage->name;
            $url= $this->build_url_from_name($name);

            $res= $this->register_image($name, $url, $attachment->post_parent, $attachment_id, $attachment);
            if($res){
                return wp_get_attachment_metadata($res);
            }else{
                return $metadata;
            }
        }else{
            return $metadata;
        }
    }

    /*public function image4io_media_delete($postid){
        $md = wp_get_attachment_metadata($attachment_id);
        if(isset($md['image4io'])&&$md['image4io']){
            $manager = new Image4IOManager;
            $manager->setup();
            $result = $manager->uploadToImage4ioFromUrl($full_path,"/");
            var_dump($result);
            die;
        }
    }*/

    public function update_urls_for_ssl($url) {
        //Correct protocol for https connections
        list($protocol, $uri) = explode('://', $url, 2);
        if(is_ssl()) {
            if('http' == $protocol) {
            $protocol = 'https';
            }
        } else {
            if('https' == $protocol) {
            $protocol = 'http';
            }
        }
        
        return $protocol.'://'.$uri;
    }

    public function check_image4io_options($value,$old_value,$option){
        
        $manager = new Image4IOManager();
        $error=$manager->validateCredentials($value['api_key'],$value['api_secret'],$value['cloudname']);
        if(isset($error)){
            add_settings_error( $option, 1, $error );
            return $old_value;
        }else{
            return $value;
        }
    }
 }