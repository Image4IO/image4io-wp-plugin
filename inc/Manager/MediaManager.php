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
        $this->mediaButtonHook();
        
    }

    public function mediaButtonHook(){
        add_action("init",array($this,"init_image4io_shortcode"));
        
        add_filter('manage_media_columns', array($this, 'add_image4io_media_column'));
        add_action('manage_media_custom_column', array($this, 'image4io_media_column_value'), 0, 2);
        add_action('admin_footer-upload.php', array($this, 'image4io_media_lib_upload_admin_footer'));
        add_action('load-upload.php', array($this, 'image4io_media_lib_upload_action'));
        add_filter('wp_get_attachment_url', array($this, 'fix_local_url_to_image4io'), 1, 2);
        add_filter('image_downsize', array($this, 'image4io_resize'), 1, 3);
        //add_filter('wp_calculate_image_srcset',array($this,'image4io_generate_image_srcset'),1,5);
        add_filter('the_content',array($this,'image4io_make_content_responsive'));
        
        add_action('media_buttons', array($this, 'mediaImage4io'), 11);
        add_action('admin_enqueue_scripts', array($this,'mediaButtonEnqueue'));
        add_action('wp_ajax_image4io_image_selected',array($this,'imageSelected'));
        add_action('wp_ajax_image4io_model',array($this, 'getImagesByFolder'));

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

    public function get_wp_sizes()
    {
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
                echo "<span style='line-height: 24px;'>Uploaded</span>";      
            }else{
                $action_url = wp_nonce_url('?', 'image4io-media');
                echo "<a href='$action_url&image4io_upload=$attachment_id'>Upload to Image4io</a>";
            }
        }
    }

    public function image4io_media_lib_upload_admin_footer(){

    }

    public function image4io_media_lib_upload_action(){
        $sendback = wp_get_referer();

        global $pagenow;
        if ('upload.php' == $pagenow && isset($_REQUEST['image4io_upload']) && (int) $_REQUEST['image4io_upload']) {
            check_admin_referer('image4io-media');
            //upload to image4io
            $this->upload_to_image4io($_REQUEST['image4io_upload']);
            //return to media library
            wp_redirect($sendback);
            exit();
        }
    }

    public function upload_to_image4io($attachment_id){
        $md = wp_get_attachment_metadata($attachment_id);
        if(isset($md['image4io'])){
            return 'Already uploaded to Image4io';
        }

        $attachment = get_post($attachment_id);

        $mime_type=$attachment->post_mime_type;
        if(!preg_match( '!^image/!', $mime_type )){
            return 'Unsupported file type';
        }

        $full_path = $attachment->guid;
        if (empty($full_path)) {
            return 'Unsupported attachment type';
        }

        $manager = new Image4IOManager;
        $manager->setup();
        $result = $manager->uploadToImage4ioFromUrl($full_path,"/");
        
        if(!isset($result->fetchedFile)){
            return "Cannot upload!";
        }
        $name=$result->fetchedFile->name;
        $url= $this->build_url_from_name($name);

        $old_url = wp_get_attachment_url($attachment_id);
        
        $this->register_image($name, $url,$attachment->post_parent,$attachment_id,$attachment);

        //$this->update_image_src_all($attachment_id, $result, $old_url, $url, true);

        return $id;
    }

    public function register_image($name, $url, $post_id, $attachment_id, $original_attachment)
    {
        $info = pathinfo($url);
        $public_id = $info['filename'];
        $mime_types = array('png' => 'image/png', 'jpg' => 'image/jpeg', 'bmp' => 'image/bmp');
        $type = $mime_types[$info['extension']];
        
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
            wp_update_attachment_metadata($id, $md);
        }
        
        return $id;
    }

    public function update_image_src_all($attachment_id, $attachment_metadata, $old_url, $new_url, $migrate_in)
    {
        $query = new WP_Query(
            array(
                'post_type' => 'any',
                'post_status' => 'publish,pending,draft,auto-draft,future,private',
                's' => "wp-image-{$attachment_id}",
            )
        );

        while ($query->have_posts()) {
            $query->the_post();
            $this->update_image_src($query->post, $attachment_id, $attachment_metadata, $old_url, $new_url, $migrate_in);
        }
    }

    public function update_image_src($post, $attachment_id, $attachment_metadata, $old_url, $new_url, $migrate_in)
    {
        $sizes=$this->get_wp_sizes();
        $post_content = $post->post_content;
        preg_match_all('~<img.*?>~i', $post->post_content, $images);
        foreach ($images[0] as $img) {
            if (preg_match('~class *= *["\']([^"\']+)["\']~i', $img, $class) && preg_match('~wp-image-(\d+)~i', $class[1], $id) && $id[1] == $attachment_id) {
                $wanted_size = null;
                if (preg_match('~size-([a-zA-Z0-9_\-]+)~i', $class[1], $size)) {
                    if (isset($sizes[$size[1]])) {
                        $wanted_size = $size[1];
                    } elseif ('full' == $size[1]) {
                        // default url requested
                    } else {
                        // Unknown transformation.
                        if ($migrate_in) {
                            continue; // Skip
                        } else {
                            error_log('Cannot automatically migrate image - non-standard image size detected '.$size[1]);
                            $errors[$post->ID] = true;

                            return false;
                        }
                    }
                }
                if (preg_match('~src *= *["\']([^"\']+)["\']~i', $img, $src)) {
                    if ($migrate_in) {
                        // Migrate In
                        list($new_img_src) = $this->build_resize_url($new_url, $attachment_metadata, $wanted_size);
                        if ($new_img_src) {
                            $post_content = str_replace($src[1], $new_img_src, $post_content);
                        }
                    } else {
                        // Migrate Out
                        list($old_img_src) = $this->build_resize_url($old_url, $attachment_metadata, $wanted_size);
                        if ($old_img_src) {
                            //Compare URLs ignoring secure protocol
                            if (str_replace('https://', 'http://', $old_img_src) != str_replace('https://', 'http://', $src[1])) {
                                error_log('Cannot automatically migrate image - non-standard image url detected '.$src[1]." expected $old_img_src requested size $wanted_size");
                                $errors[$post->ID] = true;

                                return false;
                            }
                            if (!isset($wanted_size)) {
                                $wanted_size = 'full';
                            }
                            list($new_img_src) = image_downsize($attachment_id, $wanted_size);
                            if (!$new_img_src) {
                                error_log('Cannot automatically migrate image - failed to downsize '.$src[1].' to '.$wanted_size);
                                $errors[$post->ID] = true;

                                return false;
                            }
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
    
    public function image4io_resize($is_downsize, $post_id, $size)
    {
        $url = wp_get_attachment_url($post_id);
        $metadata = wp_get_attachment_metadata($post_id);
        if (!isset($metadata['image4io']) || !$metadata['image4io']) {
            return false;
        }

        return $this->build_resize_url($url, $metadata, $size);
    }

    public function build_resize_url($url, $metadata, $size)
    {
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
        } elseif (is_array($size)) {
            $wanted_size = array('width' => $size[0], 'height' => $size[1]);
            $crop = false;
        } else {
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
        $parsed_url=explode('/',$url);
        $result_url="";
        foreach($parsed_url as $idx=>$part){
            if($idx==4){
                //replace with new one
                $result_url=$result_url . "f_auto,c_fit,w_" . $wanted_size['width'] . "/";
                if(preg_match("((\w)_([0-9a-zA-Z]),?)",$part)){
                    
                   continue;
                }
            }
            $result_url=$result_url . $part . '/';
        }
        return array($result_url, $wanted_size['width'], $wanted_size['height'], true);
    }

    public function image4io_generate_image_srcset($image_src, $image_meta, $attachment_id){
        /*if (!isset($image_meta['image4io']) || !$image_meta['image4io']){
            return $sources;
        }*/
        $sizes=$image_meta['image4io_sizes'];
        $results=array();
        foreach($sizes as $size){
            $result=array(
                'url'=>$size['file'],
                'descriptor'=>'w',
                'value'=>$size['width'],
            );
            $results[$size['width']]=$result;
        }
        //unset($size);

        if ( ! is_array( $results ) || count( $results ) < 2 ) {
            return false;
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

    public function image4io_make_content_responsive($content){
        if ( ! preg_match_all( '/<img [^>]+>/', $content, $matches ) ) {
            return $content;
        }
        $selected_images = array();
	    $attachment_ids  = array();
        foreach ( $matches[0] as $image ) {
            if ( false === strpos( $image, ' srcset=' ) && preg_match('#^.*?cdn\.image4.*#',$image)>=1 && preg_match( '/wp-image-([0-9]+)/i', $image, $class_id ) ) {
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
            $content    = str_replace( $image, $this->image4io_generate_image_srcset( $image, $image_meta, $attachment_id ), $content );
        }
    
        return $content;
    }

    public function image4io_timeout_extend( $time){
        return 30;
    }
 }