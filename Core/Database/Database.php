<?php

namespace Core\Database;

use App\Tests\Helpers\TestDatabaseFactory;
use App\Tests\Helpers\SchemaInitializer;
use Exception;
use PDO;
use PDOException;

/**
 * Legacy Database class for backward compatibility.
 * 
 * @deprecated Use DatabaseConnection::getInstance() for production,
 *             and TestDatabaseFactory + SchemaInitializer for testing.
 * 
 * This class is kept temporarily for backward compatibility.
 * It delegates to the new DatabaseConnection and test helpers.
 *
 * @category Database_Connection
 * @package  Core\Database
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
     * Name of the dynamically created test database.
     *
     * @var string|null
     */
    private static ?string $testDbName = null;

    /**
     * Database constructor.
     * Creates either a test database or production connection.
     *
     * @param string $file Path to the configuration INI file.
     * @throws Exception If the configuration file cannot be read or connection fails.
     */
    public function __construct(string $file = 'my_settings.ini')
    {
        // Test mode - use new test helpers
        if (getenv('APP_ENV') === 'testing') {
            $pdo = TestDatabaseFactory::create();
            SchemaInitializer::initialize($pdo);

            // Store test DB name if PostgreSQL
            self::$testDbName = TestDatabaseFactory::getTestDatabaseName();

            // Initialize parent with test database connection details
            parent::__construct($pdo->getAttribute(PDO::ATTR_DRIVER_NAME) . ':memory:');
            return;
        }

        // Normal mode - use new DatabaseConnection
        $pdo = DatabaseConnection::getInstance($file);

        // Extract connection details to initialize PDO parent
        $dsn = $this->extractDsnFromPdo($pdo, $file);
        $settings = parse_ini_file($file, true);

        parent::__construct(
            $dsn,
            $settings['database']['username'] ?? '',
            $settings['database']['password'] ?? ''
        );
    }

    /**
     * Helper to extract DSN from configuration file.
     *
     * @param PDO    $pdo  The PDO instance.
     * @param string $file The config file path.
     * @return string The DSN string.
     */
    private function extractDsnFromPdo(PDO $pdo, string $file): string
    {
        $settings = parse_ini_file($file, true);

        return sprintf(
            '%s:host=%s%s;dbname=%s',
            $settings['database']['driver'] ?? 'mysql',
            $settings['database']['host'] ?? 'localhost',
            !empty($settings['database']['port']) ? ';port=' . $settings['database']['port'] : '',
            $settings['database']['schema'] ?? ''
        );
    }

    /**
     * Returns the singleton instance.
     *
     * @param string $file Path to the configuration INI file.
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

    /**
     * Drops the test database (for cleanup).
     *
     * @return void
     */
    public static function dropTestDatabase(): void
    {
        TestDatabaseFactory::dropTestDatabase();
        self::$testDbName = null;
    }

    /**
     * Sets a custom Database instance (for testing).
     *
     * @param Database $instance The custom Database instance to set.
     * @return void
     */
    public static function setInstance(Database $instance): void
    {
        self::$instance = $instance;
    }
}