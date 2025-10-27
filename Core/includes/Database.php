<?php

namespace Core\includes;

use Exception;
use PDO;

/**
 * Class database
 *
 * Handles the connection between the application and the database using PDO.
 * Implements the Singleton pattern to ensure a single instance of the database connection.
 */
class Database extends PDO
{
    private static ?Database $instance = null;
    public function __construct(string $file = 'my_settings.ini')
    {
        if (!$settings = parse_ini_file($file, true)) {
            throw new exception('Unable to open ' . $file . '.');
        }


        $dns = $settings['database']['driver'] .
            ':host=' . $settings['database']['host'] .
            ((!empty($settings['database']['port'])) ? (';port=' . $settings['database']['port']) : '') .
            ';dbname=' . $settings['database']['schema'];



        parent::__construct($dns, $settings['database']['username'], $settings['database']['password']);
    }

    /**
     * Returns the singleton instance of the database connection.
     *
     * This method checks if an instance of the database connection already exists.
     * If not, it creates a new instance using the provided configuration file.
     * Subsequent calls to this method will return the existing instance.
     * @param string $file The path to the configuration file (default is 'my_settings.ini').
     * @return Database The singleton instance of the database connection.
     */
    public static function getInstance($file = 'my_settings.ini'): Database
    {
        if (self::$instance === null) {
            self::$instance = new self($file);
        }
        return self::$instance;
    }
}
