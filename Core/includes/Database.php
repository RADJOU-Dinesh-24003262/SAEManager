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
 * Provides support for dynamic test databases:
 * - SQLite in-memory for local testing
 * - PostgreSQL for CI/CD environments
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
     * The singleton instance of the Database.
     *
     * @var Database|null
     */
    private static ?Database $instance = null;

    /**
     * Name of the dynamically created test database (used in testing mode).
     *
     * @var string|null
     */
    private static ?string $testDbName = null;

    /**
     * Database constructor.
     *
     * Reads database configuration from an INI file and initializes the PDO connection.
     * In testing mode, uses SQLite in-memory by default or PostgreSQL if configured.
     *
     * @param string $file Path to the configuration INI file.
     * @throws Exception If the configuration file cannot be read or connection fails.
     */
    public function __construct(string $file = 'my_settings.ini')
    {
        // --- Test mode ---
        if (getenv('APP_ENV') === 'testing') {
            // Check if PostgreSQL is available (CI environment).
            if ($this->isPostgreSQLAvailable()) {
                /* error_log('Database: Using dynamic PostgreSQL for testing.'); */
                $this->createDynamicTestDatabase();
            } else {
                /* error_log('Database: Using in-memory SQLite for testing.'); */
                // Use SQLite for local testing.
                $this->createSQLiteTestDatabase();
            }
            return;
        }

        // --- Normal mode ---
        $settings = parse_ini_file($file, true);
        if ($settings === false) {
            throw new Exception('Unable to open configuration file: ' . $file);
        }

        // Build DSN string.
        $dsn = sprintf(
            '%s:host=%s%s;dbname=%s',
            $settings['database']['driver'] ?? 'mysql',
            $settings['database']['host'] ?? 'localhost',
            !empty($settings['database']['port']) ? ';port=' . $settings['database']['port'] : '',
            $settings['database']['schema'] ?? ''
        );

        // Initialize PDO connection.
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
     * Check if PostgreSQL is available and accepting connections.
     *
     * @return boolean True if PostgreSQL is available, false otherwise.
     */
    private function isPostgreSQLAvailable(): bool
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
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Creates an SQLite in-memory database for local testing.
     *
     * @return void
     * @throws Exception If the database creation or schema initialization fails.
     */
    private function createSQLiteTestDatabase(): void
    {
        try {
            // Use in-memory SQLite database.
            parent::__construct('sqlite::memory:');
            $this->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // Initialize the database schema for SQLite.
            $this->initializeSQLiteSchema();
        } catch (PDOException $e) {
            throw new Exception('SQLite test database creation failed: ' . $e->getMessage());
        }
    }

    /**
     * Dynamically creates a PostgreSQL test database with full schema and seed data.
     *
     * @return void
     * @throws Exception If the database creation or schema initialization fails.
     */
    private function createDynamicTestDatabase(): void
    {
        $rootUser = getenv('DB_USER') ?: 'postgres';
        $rootPass = getenv('DB_PASS') ?: 'postgres';
        $rootHost = getenv('DB_HOST') ?: 'localhost';
        $rootPort = getenv('DB_PORT') ?: 5432;

        $rootDsn = "pgsql:host=$rootHost;port=$rootPort;dbname=postgres";

        try {
            $root = new PDO($rootDsn, $rootUser, $rootPass);
            $root->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // Create a unique test database.
            self::$testDbName = 'saemanager_test_' . substr(md5(uniqid()), 0, 6);
            $root->exec("CREATE DATABASE " . self::$testDbName);

            // Connect to the new test database.
            $dsn = "pgsql:host=$rootHost;port=$rootPort;dbname=" . self::$testDbName;
            parent::__construct($dsn, $rootUser, $rootPass);
            $this->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // Initialize the database schema dynamically.
            $this->initializePostgreSQLSchema();
        } catch (PDOException $e) {
            throw new Exception('Dynamic test database creation failed: ' . $e->getMessage());
        }
    }

    /**
     * Initializes the SQLite database schema (adapted from PostgreSQL schema).
     *
     * @return void
     * @throws PDOException If schema creation fails.
     */
    private function initializeSQLiteSchema(): void
    {
        $schemaSql = <<<SQL
        CREATE TABLE users (
            user_id INTEGER PRIMARY KEY AUTOINCREMENT,
            first_name TEXT NOT NULL,
            last_name TEXT NOT NULL,
            email TEXT NOT NULL UNIQUE,
            phone TEXT NOT NULL,
            hashed_password TEXT NOT NULL,
            user_type TEXT NOT NULL CHECK(user_type IN ('0', '1', '2'))
        );

        CREATE TABLE clients (
            client_id INTEGER PRIMARY KEY,
            organisation TEXT NOT NULL,
            FOREIGN KEY (client_id) REFERENCES users(user_id) ON DELETE CASCADE
        );

        CREATE TABLE professors (
            professor_id INTEGER PRIMARY KEY,
            amu_id TEXT NOT NULL,
            FOREIGN KEY (professor_id) REFERENCES users(user_id) ON DELETE CASCADE
        );

        CREATE TABLE sae_subjects (
            sae_subject_id INTEGER PRIMARY KEY AUTOINCREMENT,
            responsible_prof_id INTEGER NOT NULL REFERENCES professors(professor_id) ON DELETE CASCADE,
            client_id INTEGER REFERENCES clients(client_id) ON DELETE CASCADE,
            subject_name TEXT NOT NULL,
            begin_date DATE NOT NULL,
            end_date DATE NOT NULL,
            file_path TEXT
        );

        CREATE TABLE sae_groups (
            sae_group_id INTEGER PRIMARY KEY AUTOINCREMENT,
            sae_subject_id INTEGER NOT NULL REFERENCES sae_subjects(sae_subject_id) ON DELETE CASCADE,
            professor_id INTEGER REFERENCES professors(professor_id) ON DELETE SET NULL
        );

        CREATE TABLE students (
            student_id INTEGER PRIMARY KEY REFERENCES users(user_id) ON DELETE CASCADE,
            amu_id TEXT NOT NULL,
            major TEXT,
            course TEXT,
            year INTEGER NOT NULL,
            td TEXT NOT NULL,
            tp TEXT NOT NULL,
            sae_group_id INTEGER REFERENCES sae_groups(sae_group_id)
        );

        CREATE TABLE participated_in (
            student_id INTEGER NOT NULL REFERENCES students(student_id) ON DELETE CASCADE,
            sae_group_id INTEGER NOT NULL REFERENCES sae_groups(sae_group_id) ON DELETE CASCADE,
            sae_subject_id INTEGER REFERENCES sae_subjects(sae_subject_id) ON DELETE CASCADE,
            PRIMARY KEY (student_id, sae_group_id)
        );

        CREATE TABLE competences (
            competence_name TEXT NOT NULL,
            sae_subject_id INTEGER NOT NULL REFERENCES sae_subjects(sae_subject_id) ON DELETE CASCADE,
            PRIMARY KEY (competence_name, sae_subject_id)
        );


        CREATE TABLE sae_todolists (
            todoid INTEGER PRIMARY KEY AUTOINCREMENT,
            sae_group_id INTEGER REFERENCES sae_groups(sae_group_id),
            tododesc TEXT,
            checked INTEGER NOT NULL DEFAULT 0
        );

        CREATE TABLE password_resets (
            email TEXT NOT NULL REFERENCES users(email),
            token TEXT PRIMARY KEY,
            created_at TEXT NOT NULL,
            expires_at TEXT NOT NULL,
            used INTEGER NOT NULL
        );

        INSERT INTO users (first_name, last_name, email, phone, hashed_password, user_type)
        VALUES ('azerty', 'azerty', 'azerty.azerty@etu.univ-amu.fr', '0689879878', 'fake_hash', '0');

        INSERT INTO students (student_id, amu_id, major, course, year, td, tp)
        VALUES (1, 'azerty', NULL, NULL, 1, 'TD1', 'TPA');
        SQL;

        $statements = array_filter(
            array_map('trim', explode(';', $schemaSql)),
            fn ($stmt) => !empty($stmt)
        );

        foreach ($statements as $stmt) {
            $this->exec($stmt);
        }
    }

    /**
     * Initializes the PostgreSQL database schema dynamically.
     *
     * @return void
     * @throws PDOException If schema creation fails.
     */
    private function initializePostgreSQLSchema(): void
    {
        $schemaSql = <<<SQL
        CREATE TYPE user_types AS ENUM ('0', '1', '2');

        CREATE TABLE users (
            user_id SERIAL PRIMARY KEY,
            first_name VARCHAR(100) NOT NULL,
            last_name VARCHAR(100) NOT NULL,
            email VARCHAR(320) NOT NULL UNIQUE,
            phone VARCHAR(13) NOT NULL,
            hashed_password VARCHAR(255) NOT NULL,
            user_type user_types NOT NULL
        );

        CREATE TABLE clients (
            client_id INT PRIMARY KEY,
            organisation VARCHAR(255) NOT NULL,
            FOREIGN KEY (client_id) REFERENCES users(user_id) ON DELETE CASCADE
        );

        CREATE TABLE professors (
            professor_id INT PRIMARY KEY,
            amu_id VARCHAR(9) NOT NULL,
            FOREIGN KEY (professor_id) REFERENCES users(user_id) ON DELETE CASCADE
        );

        CREATE TABLE sae_subjects (
            sae_subject_id SERIAL PRIMARY KEY,
            responsible_prof_id INT NOT NULL REFERENCES professors(professor_id) ON DELETE CASCADE,
            client_id INT REFERENCES clients(client_id) ON DELETE CASCADE,
            subject_name VARCHAR(255) NOT NULL,
            begin_date DATE NOT NULL,
            end_date DATE NOT NULL,
            file_path TEXT
        );

        CREATE TABLE sae_groups (
            sae_group_id SERIAL PRIMARY KEY,
            sae_subject_id INT NOT NULL REFERENCES sae_subjects(sae_subject_id) ON DELETE CASCADE,
            professor_id INT REFERENCES professors(professor_id) ON DELETE SET NULL
        );

        CREATE TABLE students (
            student_id INT PRIMARY KEY REFERENCES users(user_id) ON DELETE CASCADE,
            amu_id VARCHAR(9) NOT NULL,
            major VARCHAR(100),
            course VARCHAR(10),
            year SMALLINT NOT NULL,
            td VARCHAR(10) NOT NULL,
            tp VARCHAR(10) NOT NULL,
            sae_group_id INT REFERENCES sae_groups(sae_group_id)
        );

        CREATE TABLE competences (
            competence_name VARCHAR(255) NOT NULL,
            sae_subject_id INT NOT NULL REFERENCES sae_subjects(sae_subject_id) ON DELETE CASCADE,
            PRIMARY KEY (competence_name, sae_subject_id)
        );

        CREATE TABLE sae_professor_groups (
            sae_subject_id INT NOT NULL REFERENCES sae_subjects(sae_subject_id) ON DELETE CASCADE,
            professor_id INT NOT NULL REFERENCES professors(professor_id) ON DELETE CASCADE,
            PRIMARY KEY (sae_subject_id, professor_id)
        );

        CREATE TABLE sae_todolists (
            todoid SERIAL PRIMARY KEY,
            sae_group_id INT REFERENCES sae_groups(sae_group_id),
            tododesc TEXT,
            checked BOOLEAN NOT NULL DEFAULT FALSE
        );

        CREATE TABLE password_resets (
            email VARCHAR(320) NOT NULL REFERENCES users(email),
            token VARCHAR(64) PRIMARY KEY,
            created_at TIMESTAMPTZ NOT NULL,
            expires_at TIMESTAMPTZ NOT NULL,
            used BOOLEAN NOT NULL
        );

        -- Example seed data
        INSERT INTO users (first_name, last_name, email, phone, hashed_password, user_type)
        VALUES ('azerty', 'azerty', 'azerty.azerty@etu.univ-amu.fr', '0689879878', 'fake_hash', '0');

        INSERT INTO students (student_id, amu_id, major, course, year, td, tp)
        VALUES (1, 'azerty', NULL, NULL, 1, 'TD1', 'TPA');
        SQL;

        $this->exec($schemaSql);
    }

    /**
     * Drops the dynamically created test database if it exists.
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
        } catch (PDOException $e) {
            // Ignore errors.
        }

        self::$testDbName = null;
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
