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
        add_action('media_buttons', array($this, 'mediaImage4io'), 11);
        add_action('admin_enqueue_scripts', array($this,'mediaButtonEnqueue'));
        add_action('wp_ajax_image4io_image_selected',array($this,'imageSelected'));
        add_action('wp_ajax_image4io_model',array($this, 'getImagesByFolder'));
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
            return;
        }

        if($width!=0&&$height!=0){
            return "https://cdn.image4.io/" . $cloudname . "/f_auto,c_fit,w_" . $width . ",h_" . $height . $src;
        }elseif($width!=0){
            return "https://cdn.image4.io/" . $cloudname . "/f_auto,c_fit,w_" . $width . $src;
        }else{
            return "https://cdn.image4.io/" . $cloudname . "/f_auto,c_fit,h_" . $height . $src;
        }
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
 }



