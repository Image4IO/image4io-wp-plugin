<?php
/**
 * @package image4ioPlugin
 */

namespace Image4io;

use Image4io\Api\SettingsApi;

final class Init{
    public static function get_services(){
        return [
            Base\Enqueue::class,
            Base\SettingsLink::class,
            Pages\Admin::class,
            Manager\MediaManager::class
        ];
    }

    public static function register_services(){
        foreach(self::get_services() as $class){
            $service=self::instantiate($class);
            if(method_exists($service,'register')){
                $service->register();
            }
        }
        SettingsApi::instance()->register();
    }

    private static function instantiate($class){
        return new $class();
    }
}