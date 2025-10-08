<?php
namespace Utilis\Validator;

use includes\exception\ExceptionValidationRegister;
use includes\exception\ExceptionValidationRegisters;

class LoginValidator extends FormValidator
{
    protected $required = ['username', 'password'];

    public function validate(array $data): void
    {
        //Void because no validation rules for login form
        //The required fields are already handled in the escape() method of the parent class
    }
}