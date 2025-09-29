<?php
class BdSaeManager extends PDO{
    public function __construct($file = 'my_settings.ini'){
        if (!$settings = parse_ini_file($file, TRUE))
            throw new exception('Unable to open ' . $file . '.');


        $dns = $settings['database']['driver'] .
            ':host=' . $settings['database']['host'] .
            ((!empty($settings['database']['port'])) ? (';port=' . $settings['database']['port']) : '') .
            ';dbname=' . $settings['database']['schema'];


        parent::__construct($dns, $settings['database']['username'], $settings['database']['password']);


    }
}

$test = new BdSaeManager();
if ($test){
    echo "Connected to the database successfully!";
}

$result = $test->query("SELECT nom, email FROM users");
while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
    echo "Name: " . $row['nom'] . "\n" .
        "Email: " . $row['email'] . "\n";
}


?>