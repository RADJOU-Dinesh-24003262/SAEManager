<?php

namespace Validator;

use Core\includes\exception\ExceptionValidation\ExceptionValidationRegister;
use Validator\FormValidator;

class EditProfileValidator extends FormValidator
{
    protected $required = ['phone'];

    /**
     * @param array $data
     * @return void
     */
    public function validate(array $data): void
    {
        if (!($this->isValidPhone($data['phone']))) {
            throw new ExceptionValidationRegister("phone", "int", "Numéro de téléphone invalide.");
        }
    }
}
