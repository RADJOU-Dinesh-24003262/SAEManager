<?php

namespace Core\includes;

use Exception;
use PDO;
use PDOException;

/**
 * Class Database
 *
 * Handles the connection between the application and the database using PDO.
 * Implements the Singleton pattern to ensure a single instance of the database connection.
 *
 * @category Database_Connection
 * @package  Core\includes
 * @author   Dinesh Radjou <dinesh.radjou@etu.univ-amu.fr>
 * @license  MIT License https://opensource.org/licenses/MIT
 * @link     https://github.com/RADJOU-Dinesh-24003262/SAEManager
 */
class Database extends PDO
{
    /**
     * The singleton instance of the Database
     *
     * @var Database|null
     */
    private static ?Database $instance = null;

    /**
     * Database constructor.
     *
     * Reads database configuration from an INI file and initializes the PDO connection.
     *
     * @param  string $file Path to the configuration INI file.
     * @throws Exception If the INI file cannot be read or connection fails.
     */
    public function __construct(string $file = 'my_settings.ini')
    {
        // Parse the INI configuration file.
        $settings = parse_ini_file($file, true);
        if ($settings === false) {
            throw new Exception('Unable to open configuration file: ' . $file);
        }

        // Build DSN string.
        $dsn = sprintf(
            '%s:host=%s%s;dbname=%s;charset=%s',
            $settings['database']['driver'] ?? 'mysql',
            $settings['database']['host'] ?? 'localhost',
            !empty($settings['database']['port']) ? ';port=' . $settings['database']['port'] : '',
            $settings['database']['schema'] ?? '',
            $settings['database']['charset'] ?? 'utf8mb4'
        );
        // Call parent constructor.
        try {
            parent::__construct(
                $dsn,
                $settings['database']['username'] ?? '',
                $settings['database']['password'] ?? ''
            );
        } catch (PDOException $e) {
            throw new Exception('Database connection failed: ' . $e->getMessage());
        }
    }

    /**
     * Returns the singleton instance of the Database connection.
     *
     * Ensures that only one PDO connection exists throughout the application lifecycle.
     *
     * @param  string $file Path to the configuration INI file.
     * @return Database The singleton instance.
     * @throws Exception If connection fails.
     */
    public static function getInstance(string $file = 'my_settings.ini'): Database
    {
        if (self::$instance === null) {
            self::$instance = new self($file);
        }

        return self::$instance;
    }
}
