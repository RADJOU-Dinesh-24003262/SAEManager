<?php

namespace Validator;

use Core\includes\exception\ExceptionValidation\ExceptionValidationEmpty;

class SaeSujetValidator extends FormValidator {

    protected $required = ['sae_subject_id', 'subject_name', 'end_date'];


    public function validate(array $data): void
    {
        $errors = [];
        if (!is_numeric($data['sae_subject_id']) || intval($data['sae_subject_id']) <= 0) {
            $errors[] = "L'identifiant du sujet SAE doit être un entier positif.";
        }
        if (strlen($data['subject_name']) < 3 || strlen($data['subject_name']) > 255) {
            $errors[] = "Le nom du sujet SAE doit contenir entre 3 et 100 caractères.";
        }
        $endDate = strtotime($data['end_date']);
        if ($endDate === false || $endDate <= time()) {
            $errors[] = "La date de fin doit être une date valide dans le futur.";
        }

        if (!empty($errors)) {
            throw new ExceptionValidationEmpty(implode(" ", $errors));
        }
    }
}