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
     * The base directory of the project.
     * @var string
     */
    private static string $projectRoot;

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
        // Calculate project root dynamically.
        // Assumes Autoloader.php is in Core/includes/
        self::$projectRoot = dirname(dirname(__DIR__));

        spl_autoload_register(
            function ($class) {
                // Convert namespace to file path.
                $classPath = str_replace('\\', DIRECTORY_SEPARATOR, $class) . '.php';

                $file = null;

                // Handle 'App' namespace (e.g., App\Models\User\User -> App/src/Models/User/User.php)
                if (str_starts_with($class, 'App\\')) {
                    $file = self::$projectRoot . DIRECTORY_SEPARATOR . 'App' . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR .
                            substr($classPath, 4); // Remove 'App\' part
                }
                // Handle 'Models', 'Services' namespaces which are under App/src
                elseif (str_starts_with($class, 'Models\\') || str_starts_with($class, 'Services\\')) {
                    $file = self::$projectRoot . DIRECTORY_SEPARATOR . 'App' . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . $classPath;
                }
                // Handle 'Core' namespace (e.g., Core\Utilis\Logger -> Core/Utilis/Logger.php)
                elseif (str_starts_with($class, 'Core\\')) {
                    $file = self::$projectRoot . DIRECTORY_SEPARATOR . $classPath;
                }


                if ($file !== null && file_exists($file)) {
                    include $file;
                }
            }
        );
    }
}
