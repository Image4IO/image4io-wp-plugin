<?php
/**
 * @package image4ioPlugin
 */

namespace Inc\Base;

class Activate{
    public static function activate(){
        flush_rewrite_rules();
    }
}