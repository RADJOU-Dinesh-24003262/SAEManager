<?php
    function isValidEmail($email) {
        return preg_match('/^[a-zA-ZÀ-ÿ\-\']+\.[a-zA-ZÀ-ÿ\-\']+(\.[0-9]+)?@(etu\.)?univ-amu\.fr$/', $email);
    }

    function isOwnEmail($email, $lname, $fname) {
        $pattern = '/^' . strtolower(preg_quote($fname, '/')) . '\.' . strtolower(preg_quote($lname, '/')) . '(\.[0-9]+)?@(etu\.)?univ-amu\.fr$/';
        return preg_match($pattern, $email);
    }

    function isValidGender($gender) {
        return in_array($gender, ['male', 'female', 'other']);
    }

    function isValidPhone($phone) {
        return preg_match('/^0[4,6,7][0-9]{8}$/', $phone);
    }

    function isValidPassword($password) {
        return strlen($password) >= 8;
    }

    function isPasswordMatch($pwd, $pwdverif) {
        return $pwd === $pwdverif;
    }

    function isValidDate($date) {
        $d = DateTime::createFromFormat('Y-m-d', $date);
        return $d && $d->format('Y-m-d') === $date;
    }

    function isValidYear($year) {
        return in_array($year, ['1', '2', '3']);
    }

    function isValidParcours($parcours) {
        return in_array($parcours, ['A', 'B']);
    }

    function isValidTD($td) {
        return in_array($td, ['TD1', 'TD2', 'TD3', 'TD4']);
    }

    function isValidTP($tp) {
        return in_array($tp, ['TPA', 'TPB']);
    }

    function valid_donnees($donnees){
        $donnees = trim($donnees);
        $donnees = stripslashes($donnees);
        $donnees = htmlspecialchars($donnees, ENT_QUOTES, 'UTF-8');
        return $donnees;
    }

    
?>

