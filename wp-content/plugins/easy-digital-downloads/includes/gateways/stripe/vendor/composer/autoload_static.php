<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit52360df2f3d60779e5a0ff48f14462b7
{
    public static $prefixLengthsPsr4 = array (
        'S' => 
        array (
            'Stripe\\' => 7,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Stripe\\' => 
        array (
            0 => __DIR__ . '/..' . '/stripe/stripe-php/lib',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit52360df2f3d60779e5a0ff48f14462b7::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit52360df2f3d60779e5a0ff48f14462b7::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInit52360df2f3d60779e5a0ff48f14462b7::$classMap;

        }, null, ClassLoader::class);
    }
}
