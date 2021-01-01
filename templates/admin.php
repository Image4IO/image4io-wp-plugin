<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>

<div class="wrap">
  <h1>Image4io Plugin</h1>
  <?php settings_errors(); ?>

  <form method="POST" action="options.php">
    <?php
    settings_fields('dashboard_options_group');
    do_settings_sections('image4io_plugin');
    submit_button();
    ?>
  </form>

  <hr />
  <h2>Migration</h2>
  <p>image4io plugin provides migrate actions to quickly integrate your site to image4io. <br> <strong>NOTE: These actions will take some time according to your images in the Media Library.</strong> <br> <strong>NOTE: These actions will require correct API settings.</strong></p>
  <table class="form-table" role="presentation">
    <tbody>
      <tr>
        <th scope="row">
          <p>Migrating to image4io will upload all the images from WP Media Library to image4io storage</p>
        </th>
        <td><button class="button button-primary" onclick="migrateTo()">Migrate to image4io</button></td>
      </tr>
      <tr>
        <th scope="row">
          <p>Migrating from image4io will download all the images that are uploaded from the WP Media Library</p>
        </th>
        <td><button class="button button-primary" onclick="migrateFrom()">Migrate from image4io</button></td>
      </tr>
    </tbody>
  </table>
</div>
<div id="loadingModal" style="display:none;">
  <img src="<?php echo plugin_dir_url(dirname(__FILE__, 2)) ?>image4io-wp-plugin/assets/img/ajax-loader.gif" class="center-img">
</div>
<style>
  [data-tooltip] {
    position: relative;
    z-index: 10;
  }

  /* Positioning and visibility settings of the tooltip */
  [data-tooltip]:before,
  [data-tooltip]:after {
    position: absolute;
    visibility: hidden;
    opacity: 0;
    left: 50%;
    bottom: calc(100% + 5px);
    pointer-events: none;
    transition: 0.2s;
    will-change: transform;
  }

  /* The actual tooltip with a dynamic width */
  [data-tooltip]:before {
    content: attr(data-tooltip);
    padding: 10px 18px;
    min-width: 50px;
    max-width: 300px;
    width: max-content;
    width: -moz-max-content;
    border-radius: 6px;
    font-size: 14px;
    /*   font-size: 0.73rem; */
    background-color: rgba(59, 72, 80, 0.9);
    background-image: linear-gradient(30deg,
        rgba(59, 72, 80, 0.44),
        rgba(59, 68, 75, 0.44),
        rgba(60, 82, 88, 0.44));
    box-shadow: 0px 0px 24px rgba(0, 0, 0, 0.2);
    color: #fff;
    text-align: center;
    white-space: pre-wrap;
    transform: translate(-50%, -5px) scale(0.5);
  }

  /* Tooltip arrow */
  [data-tooltip]:after {
    content: '';
    border-style: solid;
    border-width: 5px 5px 0px 5px;
    border-color: rgba(55, 64, 70, 0.9) transparent transparent transparent;
    transition-duration: 0s;
    /* If the mouse leaves the element, 
                                the transition effects for the 
                                tooltip arrow are "turned off" */
    transform-origin: top;
    /* Orientation setting for the
                                slide-down effect */
    transform: translateX(-50%) scaleY(0);
  }

  /* Tooltip becomes visible at hover */
  [data-tooltip]:hover:before,
  [data-tooltip]:hover:after {
    visibility: visible;
    opacity: 1;
  }

  /* Scales from 0.5 to 1 -> grow effect */
  [data-tooltip]:hover:before {
    transition-delay: 0.3s;
    transform: translate(-50%, -5px) scale(1);
  }

  /* Slide down effect only on mouseenter (NOT on mouseleave) */
  [data-tooltip]:hover:after {
    transition-delay: 0.5s;
    /* Starting after the grow effect */
    transition-duration: 0.2s;
    transform: translateX(-50%) scaleY(1);
  }

  /* RIGHT */
  [data-tooltip-location="right"]:before,
  [data-tooltip-location="right"]:after {
    left: calc(100% + 5px);
    bottom: 50%;
  }

  [data-tooltip-location="right"]:before {
    transform: translate(5px, 50%) scale(0.5);
  }

  [data-tooltip-location="right"]:hover:before {
    transform: translate(5px, 50%) scale(1);
  }

  [data-tooltip-location="right"]:after {
    border-width: 5px 5px 5px 0px;
    border-color: transparent rgba(55, 64, 70, 0.9) transparent transparent;
    transform-origin: right;
    transform: translateY(50%) scaleX(0);
  }

  [data-tooltip-location="right"]:hover:after {
    transform: translateY(50%) scaleX(1);
  }

  .center-img {
    display: block;
    margin-left: auto;
    margin-right: auto;
    width: 50%;
  }
</style>
<script>
  function migrateTo() {
    console.log("migrateTo")
    show_loader();
    $.ajax({
      type: "POST",
      data: {
        action: "image4io_migrate_to",
        url: name
      },
      url: ajaxurl,
      success: function(res) {
        console.log(res)
        remove_loader()
      },
      error: function(e) {
        console.log("ERROR:")
        console.log(e)
        remove_loader()
      }
    })
  }

  function migrateFrom(e) {
    console.log("migrateFrom")
    show_loader();
    $.ajax({
      type: "POST",
      data: {
        action: "image4io_migrate_from",
        url: name
      },
      url: ajaxurl,
      success: function(res) {
        console.log(res)
        remove_loader()
      },
      error: function(e) {
        console.log("ERROR:")
        console.log(e)
        remove_loader()
      }
    })
  }

  function show_loader() {
    tb_show('', '#TB_inline?height=48&width=48&inlineId=loadingModal&modal=true');
  }

  function remove_loader() {
    tb_remove();
  }
</script>