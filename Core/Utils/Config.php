<?php

namespace Core\Utils;

use Exception;

/**
 * Class Config
 *
 * Utility class to provide centralized access to configuration settings
 * stored in the App/config/my_settings.ini file.
 *
 * @category   Utility
 * @package    Core
 * @subpackage Utils
 *
 * @author     Dinesh Radjou <dinesh.radjou@etu.univ-amu.fr>
 * @license    MIT License https://opensource.org/licenses/MIT
 * @link       https://github.com/RADJOU-Dinesh-24003262/SAEManager
 */
class Config
{
    /**
     * @var array<string, array<string, string>>|null Cached configuration settings.
     */
    private static ?array $settings = null;

    /**
     * @var string Path to the configuration file relative to this script.
     */
    private const CONFIG_FILE = '../../App/config/my_settings.ini';

    /**
     * Retrieves a configuration setting.
     *
     * @param string $section The section name in the INI file.
     * @param string $key     The key within the section.
     * @param mixed  $default The default value to return if the key is not found.
     *
     * @return mixed The configuration value or the default.
     */
    public static function get(string $section, string $key, mixed $default = null): mixed
    {
        if (self::$settings === null) {
            self::load();
        }

        return self::$settings[$section][$key] ?? $default;
    }

    /**
     * Loads the configuration from the INI file.
     *
     * @return void
     * @throws Exception If the configuration file cannot be read.
     */
    private static function load(): void
    {
        $filePath = __DIR__ . '/' . self::CONFIG_FILE;

        if (!file_exists($filePath)) {
            // Fallback for different execution contexts if needed,
            // but usually __DIR__ is reliable for Core/Utils/Config.php.
            self::$settings = [];
            return;
        }

        $parsed = parse_ini_file($filePath, true);

        if ($parsed === false) {
            throw new Exception("Impossible de lire le fichier de configuration : " . $filePath);
        }

        self::$settings = $parsed;
    }
}
