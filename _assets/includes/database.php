<?php

namespace includes;

use Exception;
use PDO;

class database extends PDO{
    private static ?database $instance = null;
    public function __construct(string $file = 'my_settings.ini'){
        if (!$settings = parse_ini_file($file, TRUE))
            throw new exception('Unable to open ' . $file . '.');


        $dns = $settings['database']['driver'] .
            ':host=' . $settings['database']['host'] .
            ((!empty($settings['database']['port'])) ? (';port=' . $settings['database']['port']) : '') .
            ';dbname=' . $settings['database']['schema'];


        parent::__construct($dns, $settings['database']['username'], $settings['database']['password']);


    }

    public static function getInstance($file = 'my_settings.ini'): database
    {
        if (self::$instance === null) {
            self::$instance = new self($file);
        }
        return self::$instance;
    }
}

?>