<div class="wrap">
    <h1>Image4io Plugin</h1>
    <?php settings_errors();?>

    <form method="post" action="options.php">
        <?php 
            settings_fields('admin_options_group');
            do_settings_sections('image4io_plugin');
            submit_button();
        ?>
    </form>
</div>