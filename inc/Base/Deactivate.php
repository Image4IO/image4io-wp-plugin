<?php
/**
 * @package image4ioPlugin
 */

namespace Image4io\Base;

class Deactivate{
    public static function deactivate(){
        flush_rewrite_rules();
    }
}