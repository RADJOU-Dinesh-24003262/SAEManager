<?php

namespace Core\includes;

/**
 * Handles automatic loading of PHP classes from the App/src and Core directories.
 *
 * @category   Core
 * @package    Core
 * @subpackage Includes
 * @author     Dinesh Radjou <dinesh.radjou@etu.univ-amu.fr>
 * @author     Alexandre Benhafessa <alexandre.benhafessa@etu.univ-amu.fr>
 * @author     François Dargentolle <francois.dargentolle@etu.univ-amu.fr>
 * @author     William Edelstein <william.edelstein@etu.univ-amu.fr>
 * @author     Nathan Griguer <nathan.griguer@etu.univ-amu.fr>
 * @license    MIT License https://opensource.org/licenses/MIT
 * @link       https://github.com/RADJOU-Dinesh-24003262/SAEManager
 */
class Autoloader
{
    /**
     * Registers the autoloader function with SPL.
     *
     * This method sets up the autoloader to look for class files in the
     * 'App/src' and 'Core' directories based on the class namespace.
     *
     * @return void
     */
    public static function register(): void
    {
        spl_autoload_register(
            function ($class) {
                // Path in App/src directory.
                $file = '..' . DIRECTORY_SEPARATOR . 'App' . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR .
                    str_replace('\\', DIRECTORY_SEPARATOR, $class) . '.php';

                // Path in Core directory.
                $coreFile = '..' . DIRECTORY_SEPARATOR . str_replace('\\', DIRECTORY_SEPARATOR, $class) . '.php';

                if (file_exists($file)) {
                    include $file;
                } elseif (file_exists($coreFile)) {
                    include $coreFile;
                }

                // Return false.
            }
        );
    }
}
