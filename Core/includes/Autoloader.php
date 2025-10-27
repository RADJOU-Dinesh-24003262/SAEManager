<?php

namespace Core\includes;

/**
 * Class Autoloader
 *
 * Handles automatic loading of PHP classes from the App/src and Core directories.
 */
class Autoloader
{
    /**
     * Registers the autoloader function with SPL.
     *
     * This method sets up the autoloader to look for class files in the
     * 'App/src' and 'Core' directories based on the class namespace.
     */
    public static function register(): void
    {
        spl_autoload_register(function ($class) {
            $file = '..' . DIRECTORY_SEPARATOR . 'App' . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR .
            str_replace('\\', DIRECTORY_SEPARATOR, $class) . '.php';
            $coreFile = '..' . DIRECTORY_SEPARATOR . str_replace('\\', DIRECTORY_SEPARATOR, $class) . '.php';

            if (file_exists($file)) {
                require $file;
            } elseif (file_exists($coreFile)) {
                require $coreFile;
            }
            //return false;
        });
    }
}
