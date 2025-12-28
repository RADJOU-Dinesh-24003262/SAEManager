<?php

namespace Validator;

use Core\includes\exception\ExceptionValidation\ExceptionValidationResetPassword;

/**
 * Class ResetPasswordValidator
 * This class regroup function to validate the reset process of a password.

 * @category Validator

 * @package Src

 * @subpackage Validator

 * @author  Alexandre Benhafessa <alexandre.benhafessa@etu.univ-amu.fr>
 * @author  François Dargentolle <francois.dargentolle@etu.univ-amu.fr>
 * @author  William Edelstein <william.edelstein@etu.univ-amu.fr>
 * @author  Nathan Griguer <nathan.griguer@etu.univ-amu.fr>
 * @author  Dinesh Radjou <dinesh.radjou@etu.univ-amu.fr>
 * @license MIT License https://opensource.org/licenses/MIT

 * @link https://github.com/RADJOU-Dinesh-24003262/SAEManager
 */
class ResetPasswordValidator extends FormValidator
{
    /**
     * The list of the variables required for the password reset of a user.
     *
     * @var array<string>
     */
    protected $required = ['pwdnew', 'pwdverif'];

    /**
     * This method validates the values given in $data to reset the password of a user with.
     *
     * @param array<string, mixed> $data Array, in adequation to the required value fields.
     *
     * @return void
     *
     * @throws ExceptionValidationResetPassword All the errors that might have been found.
     */
    #[\Override]
    public function validate(array $data): void
    {
        if (!$this->isValidPassword($data['pwdnew'])) {
            throw new ExceptionValidationResetPassword(
                'pwdnew',
                'Not Valid',
                "Le mot de passe doit contenir au moins 8 caractères."
            );
        }
        if (($data['pwdnew'] ?? '') !== ($data['pwdverif'] ?? '')) {
            throw new ExceptionValidationResetPassword(
                'pwdverif',
                'Mismatch',
                "Les mots de passe ne correspondent pas."
            );
        }
    }
}
