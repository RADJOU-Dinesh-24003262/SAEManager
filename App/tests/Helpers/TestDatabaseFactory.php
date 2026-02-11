<?php

namespace App\Tests\Helpers;

use Exception;
use PDO;
use PDOException;

/**
 * Test Database Factory.
 * Creates test databases for unit and integration testing.
 * Supports both SQLite (in-memory for local) and PostgreSQL (for CI/CD).
 *
 * @category Testing
 * @package  App\Tests\Helpers
 * @author   Dinesh Radjou <dinesh.radjou@etu.univ-amu.fr>
 * @license  MIT License https://opensource.org/licenses/MIT
 * @link     https://github.com/RADJOU-Dinesh-24003262/SAEManager
 */
class TestDatabaseFactory
{
    /**
     * Name of the dynamically created PostgreSQL test database.
     *
     * @var string|null
     */
    private static ?string $testDbName = null;

    /**
     * Creates a test database.
     * Automatically chooses between SQLite (local) and PostgreSQL (CI).
     *
     * @return PDO A PDO instance connected to the test database.
     * @throws Exception If database creation fails.
     */
    public static function create(): PDO
    {
        if (self::isPostgreSQLAvailable()) {
            return self::createPostgreSQLTestDatabase();
        }

        return self::createSQLiteTestDatabase();
    }

    /**
     * Creates an SQLite in-memory test database.
     *
     * @return PDO A PDO instance with SQLite in-memory database.
     * @throws Exception If creation fails.
     */
    public static function createSQLiteTestDatabase(): PDO
    {
        try {
            $pdo = new PDO('sqlite::memory:');
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            return $pdo;
        }
        catch (PDOException $e) {
            throw new Exception('SQLite test database creation failed: ' . $e->getMessage());
        }
    }

    /**
     * Creates a PostgreSQL test database with unique name.
     *
     * @return PDO A PDO instance connected to the new test database.
     * @throws Exception If creation fails.
     */
    public static function createPostgreSQLTestDatabase(): PDO
    {
        $rootUser = getenv('DB_USER') ?: 'postgres';
        $rootPass = getenv('DB_PASS') ?: 'postgres';
        $rootHost = getenv('DB_HOST') ?: 'localhost';
        $rootPort = getenv('DB_PORT') ?: 5432;

        $rootDsn = "pgsql:host=$rootHost;port=$rootPort;dbname=postgres";

        try {
            // Connect to PostgreSQL as superuser
            $root = new PDO($rootDsn, $rootUser, $rootPass);
            $root->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // Create unique test database
            self::$testDbName = 'saemanager_test_' . substr(md5(uniqid()), 0, 6);
            $root->exec("CREATE DATABASE " . self::$testDbName);

            // Connect to the new database
            $dsn = "pgsql:host=$rootHost;port=$rootPort;dbname=" . self::$testDbName;
            $pdo = new PDO($dsn, $rootUser, $rootPass);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            return $pdo;
        }
        catch (PDOException $e) {
            throw new Exception('PostgreSQL test database creation failed: ' . $e->getMessage());
        }
    }

    /**
     * Checks if PostgreSQL is available for testing.
     *
     * @return bool True if PostgreSQL is available, false otherwise.
     */
    public static function isPostgreSQLAvailable(): bool
    {
        $rootUser = getenv('DB_USER') ?: 'postgres';
        $rootPass = getenv('DB_PASS') ?: 'postgres';
        $rootHost = getenv('DB_HOST') ?: 'localhost';
        $rootPort = getenv('DB_PORT') ?: 5432;

        try {
            $rootDsn = "pgsql:host=$rootHost;port=$rootPort;dbname=postgres";
            $pdo = new PDO($rootDsn, $rootUser, $rootPass);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            return true;
        }
        catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Drops the test database (cleanup after tests).
     *
     * @return void
     */
    public static function dropTestDatabase(): void
    {
        if (self::$testDbName === null) {
            return;
        }

        try {
            $rootUser = getenv('DB_USER') ?: 'postgres';
            $rootPass = getenv('DB_PASS') ?: 'postgres';
            $rootHost = getenv('DB_HOST') ?: 'localhost';
            $rootPort = getenv('DB_PORT') ?: 5432;

            $rootDsn = "pgsql:host=$rootHost;port=$rootPort;dbname=postgres";
            $root = new PDO($rootDsn, $rootUser, $rootPass);
            $root->exec("DROP DATABASE IF EXISTS " . self::$testDbName);
        }
        catch (PDOException $e) {
        // Silently ignore cleanup errors
        }

        self::$testDbName = null;
    }

    /**
     * Gets the name of the current test database.
     *
     * @return string|null The test database name, or null if none created.
     */
    public static function getTestDatabaseName(): ?string
    {
        return self::$testDbName;
    }
}