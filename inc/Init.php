<?php
/**
 * @package image4ioPlugin
 */

namespace Inc;

use Inc\Api\SettingsApi;

final class Init{
    public static function get_services(){
        return [
            Pages\Admin::class,
            Base\Enqueue::class,
            Base\SettingsLink::class,
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