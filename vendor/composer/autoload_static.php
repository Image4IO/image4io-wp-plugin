<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit2681e9faeef00c72d00162cd0cd29266
{
    public static $prefixLengthsPsr4 = array (
        'T' => 
        array (
            'Template\\' => 9,
        ),
        'I' => 
        array (
            'Inc\\' => 4,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Template\\' => 
        array (
            0 => __DIR__ . '/../..' . '/templates',
        ),
        'Inc\\' => 
        array (
            0 => __DIR__ . '/../..' . '/inc',
        ),
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit2681e9faeef00c72d00162cd0cd29266::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit2681e9faeef00c72d00162cd0cd29266::$prefixDirsPsr4;

        }, null, ClassLoader::class);
    }
}
