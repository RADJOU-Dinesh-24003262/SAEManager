<?php
/**
 * Class Autoloader
 *
 * Handles automatic loading of PHP classes from the modules/src and _assets directories.
 */
class Autoloader
{
    /**
     * Registers the autoloader function with SPL.
     *
     * This method sets up the autoloader to look for class files in the
     * 'modules/src' and '_assets' directories based on the class namespace.
     */
    public static function register(): void
    {
        spl_autoload_register(function ($class) {
            $file = 'modules' . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . str_replace('\\', DIRECTORY_SEPARATOR, $class).'.php';
            $assetFile = '_assets' . DIRECTORY_SEPARATOR . str_replace('\\', DIRECTORY_SEPARATOR, $class).'.php';

            if (file_exists($file)) {
                require $file;
            } else if (file_exists($assetFile)) {
                require $assetFile;
            }
            //return false;
        });
    }
}
Autoloader::register();