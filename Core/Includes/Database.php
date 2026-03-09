<?php

namespace Core\Includes;

use Core\Utils\Config;
use Exception;
use PDO;
use PDOException;

/**
 * Class Database
 *
 * Handles the connection between the application and the database using PDO.
 * Implements the Singleton pattern to ensure a single instance of the database connection.
 *
 * Provides support for dynamic test databases:
 * - SQLite in-memory for local testing
 * - PostgreSQL for CI/CD environments
 *
 * @category Database_Connection
 * @package  Core\Includes
 * @author   Dinesh Radjou <dinesh.radjou@etu.univ-amu.fr>
 * @license  MIT License https://opensource.org/licenses/MIT
 * @link     https://github.com/RADJOU-Dinesh-24003262/SAEManager
 */
class Database extends PDO
{
    /**
     * The singleton instance of the Database.
     *
     * @var Database|null
     */
    private static ?Database $instance = null;


    /**
     * Database constructor.
     *
     * Reads database configuration from an INI file and initializes the PDO connection.
     * In testing mode, uses SQLite in-memory by default or PostgreSQL if configured.
     *
     * @throws Exception If the configuration file cannot be read or connection fails.
     */
    public function __construct()
    {
        $dsn = sprintf(
            '%s:host=%s%s;dbname=%s',
            Config::get('database', 'driver', 'mysql'),
            Config::get('database', 'host', 'localhost'),
            Config::get('database', 'port') ? ';port=' . Config::get('database', 'port') : '',
            Config::get('database', 'schema', '')
        );

        try {
            parent::__construct(
                $dsn,
                Config::get('database', 'username', ''),
                Config::get('database', 'password', '')
            );
            $this->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            throw new Exception('Database connection failed: ' . $e->getMessage());
        }
    }

    /**
     * Returns the singleton instance of the Database connection.
     *
     * Ensures that only one PDO connection exists throughout the application lifecycle.
     *
     * @return Database The singleton instance.
     * @throws Exception If connection fails.
     */
    public static function getInstance(): Database
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }


    /**
     * Can inject a custom Database instance (for testing purposes).
     *
     * @param Database $instance The custom Database instance to set.
     * @return void
     */
    public static function setInstance(Database $instance): void
    {
        self::$instance = $instance;
    }
}
