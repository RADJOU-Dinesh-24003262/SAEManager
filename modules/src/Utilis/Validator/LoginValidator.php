<?php

namespace Utilis\Validator;

use includes\exception\ExceptionValidationLogin;

/**
 * Class LoginValidator

 * @package     src

 * @subpackage  Utilis\Validator

 * @author      Benhafessa Alexandre, Dargentolle Francois, Edelstein William, Griguer Nathan, Radjou Dinesh

 * This class regroup function to validate the login process of a user.
 */
class LoginValidator extends FormValidator
{
    /**
     * The list of the variables required for the loggin of a user.
     * @var array
     */
    protected $required = ['email', 'password'];

    /**
     *
     *
     * This this method validated the values given in $data to log a user with.
     *
     * @param array $data array, in adequation to the required value fields.
     *
     * @return void
     *
     * @throws ExceptionValidationLogin all the errors that might have been found
     */
    public function validate(array $data): void
    {
        if (!$this->isValidEmail($data['email'])) {
            throw new ExceptionValidationLogin("L'adresse email n'est pas valide.");
        }
    }
}
