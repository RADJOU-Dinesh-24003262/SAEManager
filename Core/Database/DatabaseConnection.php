<?php

namespace Core\Database;

use Exception;
use PDO;
use PDOException;

/**
 * Generic PDO Database Connection Singleton.
 * Manages a single database connection using configuration from INI file.
 * 
 * This is a reusable component suitable for any PHP project.
 *
 * @category Database
 * @package  Core\Database
 * @author   Dinesh Radjou <dinesh.radjou@etu.univ-amu.fr>
 * @license  MIT License https://opensource.org/licenses/MIT
 * @link     https://github.com/RADJOU-Dinesh-24003262/SAEManager
 */
class DatabaseConnection
{
    /**
     * The singleton PDO instance.
     *
     * @var PDO|null
     */
    private static ?PDO $instance = null;

    /**
     * Path to the configuration INI file.
     *
     * @var string
     */
    private static string $configFile = 'my_settings.ini';

    /**
     * Private constructor to prevent direct instantiation.
     */
    private function __construct()
    {
    }

    /**
     * Gets the singleton PDO instance.
     * Creates connection on first call using configuration from INI file.
     *
     * @param string|null $configFile Optional path to INI configuration file.
     * @return PDO The singleton PDO instance.
     * @throws Exception If configuration file cannot be read or connection fails.
     */
    public static function getInstance(?string $configFile = null): PDO
    {
        if ($configFile !== null) {
            self::$configFile = $configFile;
        }

        if (self::$instance === null) {
            self::$instance = self::createConnection(self::$configFile);
        }

        return self::$instance;
    }

    /**
     * Creates a new PDO connection from INI configuration.
     *
     * Expected INI format:
     * ```ini
     * [database]
     * driver = "mysql"        ; or "pgsql"
     * host = "localhost"
     * port = 3306             ; optional
     * schema = "my_database"
     * username = "user"
     * password = "pass"
     * ```
     *
     * @param string $configFile Path to the INI configuration file.
     * @return PDO A configured PDO instance.
     * @throws Exception If configuration file cannot be parsed or connection fails.
     */
    private static function createConnection(string $configFile): PDO
    {
        $settings = parse_ini_file($configFile, true);

        if ($settings === false) {
            throw new Exception('Unable to open configuration file: ' . $configFile);
        }

        if (!isset($settings['database'])) {
            throw new Exception('Configuration file missing [database] section');
        }

        $config = $settings['database'];

        // Build DSN
        $dsn = sprintf(
            '%s:host=%s%s;dbname=%s',
            $config['driver'] ?? 'mysql',
            $config['host'] ?? 'localhost',
            !empty($config['port']) ? ';port=' . $config['port'] : '',
            $config['schema'] ?? ''
        );

        try {
            $pdo = new PDO(
                $dsn,
                $config['username'] ?? '',
                $config['password'] ?? ''
                );

            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            return $pdo;
        }
        catch (PDOException $e) {
            throw new Exception('Database connection failed: ' . $e->getMessage());
        }
    }

    /**
     * Sets a custom PDO instance (useful for testing).
     *
     * @param PDO $pdo The PDO instance to use.
     * @return void
     */
    public static function setInstance(PDO $pdo): void
    {
        self::$instance = $pdo;
    }

    /**
     * Resets the singleton instance (useful for testing).
     *
     * @return void
     */
    public static function reset(): void
    {
        self::$instance = null;
    }
}