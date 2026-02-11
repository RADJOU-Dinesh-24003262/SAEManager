<?php

namespace App\Tests\Helpers;

use PDO;
use PDOException;

/**
 * Schema Initializer for SAEManager test databases.
 * Initializes database schema with tables and seed data.
 *
 * @category Testing
 * @package  App\Tests\Helpers
 * @author   Dinesh Radjou <dinesh.radjou@etu.univ-amu.fr>
 * @license  MIT License https://opensource.org/licenses/MIT
 * @link     https://github.com/RADJOU-Dinesh-24003262/SAEManager
 */
class SchemaInitializer
{
    /**
     * Initializes the complete schema for a test database.
     * Automatically detects database type (SQLite vs PostgreSQL).
     *
     * @param PDO $pdo The PDO instance to initialize.
     * @return void
     * @throws PDOException If schema initialization fails.
     */
    public static function initialize(PDO $pdo): void
    {
        $driver = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);

        if ($driver === 'sqlite') {
            self::initializeSQLiteSchema($pdo);
        } elseif ($driver === 'pgsql') {
            self::initializePostgreSQLSchema($pdo);
        } else {
            throw new \Exception("Unsupported database driver: $driver");
        }
    }

    /**
     * Initializes SQLite schema with SAEManager tables and seed data.
     *
     * @param PDO $pdo The SQLite PDO instance.
     * @return void
     * @throws PDOException If schema creation fails.
     */
    private static function initializeSQLiteSchema(PDO $pdo): void
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
            $pdo->exec($stmt);
        }
    }

    /**
     * Initializes PostgreSQL schema with SAEManager tables and seed data.
     *
     * @param PDO $pdo The PostgreSQL PDO instance.
     * @return void
     * @throws PDOException If schema creation fails.
     */
    private static function initializePostgreSQLSchema(PDO $pdo): void
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

        $pdo->exec($schemaSql);
    }
}