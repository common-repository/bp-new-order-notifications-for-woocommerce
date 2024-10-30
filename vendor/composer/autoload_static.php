<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit930bee591d8960f47702f04c897b7511
{
    public static $files = array (
        '959f8156dee333bbdc5e31c529a12eee' => __DIR__ . '/../..' . '/include/codestar/codestar-framework.php',
    );

    public static $prefixLengthsPsr4 = array (
        'B' => 
        array (
            'Bright_New_Notification\\' => 24,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Bright_New_Notification\\' => 
        array (
            0 => __DIR__ . '/../..' . '/src',
        ),
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit930bee591d8960f47702f04c897b7511::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit930bee591d8960f47702f04c897b7511::$prefixDirsPsr4;

        }, null, ClassLoader::class);
    }
}