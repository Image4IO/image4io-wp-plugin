<?php
/**
 * @package image4ioPlugin
 */

namespace Inc\Base;

class Image4ioWidget extends \WP_Widget
{

  public function __construct()
  {
    parent::__construct(
      'image4io-media-widget',
      'Image4io Media Widget',
      array(
        'description' => 'Image4io Media Widget'
      )
    );
  }

  public function widget( $args, $instance )
  {
    // basic output just for this example
    echo '<a href="#">
      <img src="'.esc_url($instance['image_uri']).'" />
      <h4>'.esc_html($instance['image_uri']).'</h4>
    </a>';
  }

  public function form( $instance )
  {
    // removed the for loop, you can create new instances of the widget instead
    ?>
    <p>
      <label for="<?php echo $this->get_field_id('text'); ?>">Text</label><br />
      <input type="text" name="<?php echo $this->get_field_name('text'); ?>" id="<?php echo $this->get_field_id('text'); ?>" value="<?php echo $instance['text']; ?>" class="widefat" />
    </p>
    <p>
      <label for="<?php echo $this->get_field_id('image_uri'); ?>">Image</label><br />
      <input type="text" class="img" name="<?php echo $this->get_field_name('image_uri'); ?>" id="<?php echo $this->get_field_id('image_uri'); ?>" value="<?php echo $instance['image_uri']; ?>" />
      <input type="button" class="select-img" value="Select Image" />
    </p>
    <?php
  }


}