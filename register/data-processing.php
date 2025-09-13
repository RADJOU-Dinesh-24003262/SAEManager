<?php
require '../utilis/validator.php';
require 'fields-validator.php';

function registration(): void {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;
    
    if ($_POST['action'] !== 'registration') {
        echo "<script>alert(\"Action non prise en charge.\");</script>";
        return;
    }

    $fields = ['id', 'fname', 'lname', 'gender', 'email', 'pwd', 'pwdverif', 'tel', 'dob', 'city', 'year', 'parcours', 'td', 'tp', 'terms'];

    // Stocke les valeurs validées ici
    $validated = [];

    foreach ($fields as $field) {
        $value = $_POST[$field] ?? '';

        // Parcours est optionnel en 1ère année
        if ($field === 'parcours' && ($_POST['year'] ?? '') === '1' && empty($value)) continue;

        if (empty($value)) {
            echo "<script>alert(\"Le champ $field est requis.\");</script>";
            return;
        }

        $value = valid_donnees($value);
        $error = validateField($field, $value, $_POST);

        if ($error) {
            echo "<script>alert(\"$error\");</script>";
            return;
        }

        $validated[$field] = $value;
    }

    echo "<script>alert(\"Inscription réussie !\");</script>";
}
