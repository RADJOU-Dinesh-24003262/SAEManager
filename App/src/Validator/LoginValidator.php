<?php

namespace Validator;

use Core\includes\exception\ExceptionValidation\ExceptionValidationLogin;

/**
 * Class LoginValidator
 * This class regroup function to validate the login process of a user.

 * @category Validator

 * @package    Src
 * @subpackage Validator

 * @author Alexandre Benhafessa <alexandre.benhafessa@etu.univ-amu.fr>
 * @author François Dargentolle <francois.dargentolle@etu.univ-amu.fr>
 * @author William Edelstein <william.edelstein@etu.univ-amu.fr>
 * @author Nathan Griguer <nathan.griguer@etu.univ-amu.fr>
 * @author Dinesh Radjou <dinesh.radjou@etu.univ-amu.fr>

 * @license MIT License https://opensource.org/licenses/MIT

 * @link https://github.com/RADJOU-Dinesh-24003262/SAEManager
 */
class LoginValidator extends FormValidator
{
    /**
     * The list of the variables required for the loggin of a user.
     *
     * @var array
     */
    protected $required = ['email', 'password'];

    /**
     * This method validated the values given in $data to log a user with.
     *
     * @param array $data Array, in adequation to the required value fields.
     *
     * @return void
     *
     * @throws ExceptionValidationLogin All the errors that might have been found.
     */
    public function validate(array $data): void
    {
        if (!$this->isValidEmail($data['email'])) {
            throw new ExceptionValidationLogin("L'adresse email n'est pas valide.");
        }
    }
}
