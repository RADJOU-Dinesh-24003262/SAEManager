<?php
namespace Utilis\Validator;

use includes\exception\ExceptionValidationForgotPassword;

/**
 * Class ForgotPasswordValidator
 *  
 * @package     src

 * @subpackage  Utilis\Validator

 * @author      Benhafessa Alexandre, Dargentolle Francois, Edelstein William, Griguer Nathan, Radjou Dinesh
 * 
 * Provides method to validate the inputed form infos on the forgot password page.
 */
class ForgotPasswordValidator extends FormValidator
{
    /**
     * The list of the variables required for the password reset of a user.
     * @var array
     */
    protected $required = ['email'];

    /**
     * 
     * 
     * This this method validated the values given in $data to let a user try to reset their password with.
     *
     * @param array $data array, in adequation to the required value fields. 
     * 
     * @return void
     * 
     * @throws ExceptionValidationForgotPassword all the errors that might have been found
     */
    public function validate(array $data): void
    {
        $errors = [];
        if (!$this->isValidEmail($data['email'])) {
            throw new ExceptionValidationForgotPassword('email', 'string', "L'adresse email n'est pas valide.");
        }
    }
}