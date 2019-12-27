<?php
/**
 * @package image4ioPlugin
 */

 namespace Inc\Manager;

 use Inc\Base\BaseController;
 use Inc\Base\Image4ioWidget;
 use Inc\Api\Image4IOManager;

 class MediaManager extends BaseController{

    public function register(){
        $this->hookUI();
    }

    public function hookUI(){
        //$this->hookAttachmentDetails();
        $this->hookMediaList();
        //$this->hookMediaGrid();
        //$this->hookStorageInfoMetabox();
        //$this->mediaFrameHook();
        $this->mediaButtonHook();
        
    }

    private function hookAttachmentDetails(){
        add_action( 'wp_enqueue_media', function () {
            add_action( 'admin_footer', function () {
                ?>
                <script>
                    jQuery(document).ready(function () {
                        var attachTemplate = jQuery('#tmpl-attachment-details-two-column');
                        if (attachTemplate) {
                            var txt = attachTemplate.text();
                            var idx = txt.indexOf('<div class="compat-meta">');
                            txt = txt.slice(0, idx) + 'TEST TEST \n' + txt.slice(idx);
                            attachTemplate.text(txt);
                        }
                    });
                </script>
				<?php 
            } );
        });
    }

    private function hookMediaList(){
        add_action('admin_init', function(){
            add_filter('manage_media_columns' , function ($cols){
                $cols["cloud"] = 'Cloud';
                return $cols;
            });

            add_action(
                'manage_media_custom_column',
                function ( $column_name, $id ) {
                    $metadata = wp_get_attachment_metadata($id);
                    /*if (is_array($metadata) && Cloudinary::option_get($metadata, 'cloudinary')) {
                        $src = plugins_url('/images/edit_icon.png', __FILE__);
                        echo "<span style='line-height: 24px;'><img src='$src' style='vertical-align: middle;' width='24' height='24'/> Uploaded</span>";
                    } elseif (Cloudinary::config_get('api_secret')) {
                        $action_url = wp_nonce_url('?', 'bulk-media');
                        echo "<a href='$action_url&cloud_upload=$attachment_id'>Upload to Cloudinary</a>";
                    }*/
                    echo "<a href='_blank'>Upload to Image4IO</a>";
                    
            
                },
                10,
                2
            );
        });
        add_action( 'wp_enqueue_media', function () {
            add_action( 'admin_head', function () {
                if ( get_current_screen()->base == 'upload' ) {
                    ?>
                    <style>
                        th.column-cloud, td.column-cloud {
                            width: 60px !important;
                            max-width: 60px !important;
                            text-align: center;
                        }
                    </style>
					<?php 
                }
            } );
        } );
    }

    private function hookMediaGrid(){
        if ( !$this->displayBadges ) {
            return;
        }
    }

    private function hookStorageInfoMetabox(){

    }

    private function mediaFrameHook(){
        add_action( 'widgets_init', array($this,'registerImage4ioWidget'));
        add_action('admin_enqueue_scripts', array($this,'image4iowidget_enqueue'));
    }
    function registerImage4ioWidget(){
        return register_widget(Image4ioWidget::class);
    }
    function image4iowidget_enqueue($hook){
        if($hook!= 'widgets.php' ) 
            return;
        wp_enqueue_style('thickbox');
        wp_enqueue_script('media-upload');
        wp_enqueue_script('thickbox');
        wp_enqueue_script( 'image4io_script', $this->plugin_url . 'assets/js/image4io.js' );
    }





    private function mediaButtonHook(){
        add_action('media_buttons', array($this, 'mediaImage4io'), 11);
        add_action('wp_ajax_image4io_model',array($this, 'getImagesByFolder'));
        add_action('admin_enqueue_scripts', array($this,'mediaButtonEnqueue'));
        add_action('wp_ajax_image4io_image_selected',array($this,'imageSelected'));
        //add_action('wp_ajax_image4io_update_options', array($this,'ajax_update_options'));
        //add_filter('wp_get_attachment_url',array($this, 'fix_url'), 1, 2);
        //add_filter('image_downsize', array($this, 'remote_resize'), 1, 3);
        //add_action('wp_ajax_image4io_register_image', array($this, 'ajax_register_image'));
    }

    public function mediaImage4io(){
        //wp_enqueue_script('jquery');

        echo '<a href="#TB_inline?height=800&width=753&inlineId=examplePopup1&modal=false" class="thickbox button">Add Image4IO</a>';
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
            $manager = new Image4IOManager;
            $manager->setup();
            $result = $manager->getImagesByFolder($_POST['image4IOFolder']);
            echo $result;
            wp_die();
       }
       wp_die();
    }

    public function imageSelected(){
        var_dump($_POST);
        wp_die();
    }

    public function fix_url($url, $post_id)
    {
        $metadata = wp_get_attachment_metadata($post_id);
        if (Cloudinary::option_get($metadata, 'cloudinary') && preg_match('#^.*?/(https?://.*)#', $url, $matches)) {
            return $matches[1];
        }

        return $url;
    }

    public function remote_resize($dummy, $post_id, $size)
    {
        $url = wp_get_attachment_url($post_id);
        $metadata = wp_get_attachment_metadata($post_id);
        if (!Cloudinary::option_get($metadata, 'cloudinary')) {
            return false;
        }

        return $this->build_resize_url($url, $metadata, $size);
    }

    public function build_resize_url($url, $metadata, $size)
    {
        // Check if this is a Cloudinary URL
        if (!preg_match('#(.*?)/(v[0-9]+/.*)$#', $url, $matches)) {
            return false;
        }

        if (!$size) {
            return array($url, $metadata['width'], $metadata['height'], false);
        }

        if (is_string($size)) {
            $available_sizes = $this->get_wp_sizes();
            // Unsupported custom size or 'full' image return as is, indicating that it was not changed
            if (!array_key_exists($size, $available_sizes)) {
                return array($url, $metadata['width'], $metadata['height'], false);
            }

            $wanted = $available_sizes[$size];
            $crop = $wanted['crop'];
        } elseif (is_array($size)) {
            $wanted = array('width' => $size[0], 'height' => $size[1]);
            $crop = false;
        } else {
            // Unsupported argument
            return false;
        }

        $transformation = '';
        $src_w = $dst_w = $metadata['width'];
        $src_h = $dst_h = $metadata['height'];
        if ($crop) {
            $resized = image_resize_dimensions($metadata['width'], $metadata['height'], $wanted['width'], $wanted['height'], true);
            if ($resized) {
                list($dst_x, $dst_y, $src_x, $src_y, $dst_w, $dst_h, $src_w, $src_h) = $resized;
                $transformation = "c_crop,h_$src_h,w_$src_w,x_$src_x,y_$src_y/";
            }
        }

        list($width, $height) = image_constrain_size_for_editor($dst_w, $dst_h, $size);
        if ($width != $src_w || $height != $src_h) {
            $transformation = $transformation."h_$height,w_$width/";
        }

        $url = "$matches[1]/$transformation$matches[2]";

        return array($url, $width, $height, true);
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

    public function ajax_register_image(){
        $post_id = $_POST['post_id'];
        $attachment_id = &$_POST['attachment_id'];
        $url = $_POST['url'];

        if (!empty($post_id) && !current_user_can('edit_post', $post_id)) {
            wp_send_json(array('message' => 'Permission denied.', 'error' => true));
        }
        if (!empty($attachment_id) && !current_user_can('edit_post', $attachment_id)) {
            wp_send_json(array('message' => 'Permission denied.', 'error' => true));
        }
        if (empty($url)) {
            wp_send_json(array('message' => 'Missing URL.', 'error' => true));
        }

        $id = $this->register_image($url, $post_id, $attachment_id, null, $_POST['width'], $_POST['height']);
        wp_send_json(array('success' => true, 'attachment_id' => $id));
    }

    public function register_image($url, $post_id, $attachment_id, $original_attachment, $width, $height)
    {
        $info = pathinfo($url);
        $public_id = $info['filename'];
        $mime_types = array('png' => 'image/png', 'jpg' => 'image/jpeg');
        $type = $mime_types[$info['extension']];
        $meta = null;
        if ($original_attachment) {
            $md = wp_get_attachment_metadata($attachment_id);
            $meta = $md['image_meta'];
            $title = $original_attachment->post_title;
            $caption = $original_attachment->post_content;
        } else {
            $title = null;
            $caption = null;
            $meta = null;
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
            $metadata = array('image_meta' => $meta, 'width' => $width, 'height' => $height, 'cloudinary' => true);
            wp_update_attachment_metadata($id, $metadata);
        }

        return $id;
    }

    

    

 }



