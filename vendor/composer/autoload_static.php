<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit0aab77ca8a895b1d9197a1080310d5a0
{
    public static $prefixLengthsPsr4 = array (
        'b' => 
        array (
            'bootstrap\\' => 10,
        ),
        'P' => 
        array (
            'Psr\\Log\\' => 8,
        ),
        'K' => 
        array (
            'Katzgrau\\KLogger\\' => 17,
        ),
        'A' => 
        array (
            'App\\' => 4,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'bootstrap\\' => 
        array (
            0 => __DIR__ . '/../..' . '/bootstrap',
        ),
        'Psr\\Log\\' => 
        array (
            0 => __DIR__ . '/..' . '/psr/log/Psr/Log',
        ),
        'Katzgrau\\KLogger\\' => 
        array (
            0 => __DIR__ . '/..' . '/katzgrau/klogger/src',
        ),
        'App\\' => 
        array (
            0 => __DIR__ . '/../..' . '/app',
        ),
    );

    public static $classMap = array (
        'Katzgrau\\KLogger\\Logger' => __DIR__ . '/..' . '/katzgrau/klogger/src/Logger.php',
        'sdk\\libs\\ConfigHelper' => __DIR__ . '/../..' . '/sdk/libs/ConfigHelper.php',
        'sdk\\libs\\HttpHelper' => __DIR__ . '/../..' . '/sdk/libs/HttpHelper.php',
        'sdk\\libs\\MysqlHelper' => __DIR__ . '/../..' . '/sdk/libs/MysqlHelper.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit0aab77ca8a895b1d9197a1080310d5a0::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit0aab77ca8a895b1d9197a1080310d5a0::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInit0aab77ca8a895b1d9197a1080310d5a0::$classMap;

        }, null, ClassLoader::class);
    }
}