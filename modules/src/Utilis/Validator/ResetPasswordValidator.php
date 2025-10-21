<?php

namespace Utilis\Validator;

use includes\exception\ExceptionValidationResetPassword;

/**
 * Class ResetPasswordValidator
 * This class regroup function to validate the reset process of a password.

 * @category Utilis

 * @package Src

 * @subpackage Utilis\Validator

 * @author  Alexandre Benhafessa <alexandre.benhafessa@etu.univ-amu.fr>
 * @author  François Dargentolle <francois.dargentolle@etu.univ-amu.fr>
 * @author  William Edelstein <william.edelstein@etu.univ-amu.fr>
 * @author  Nathan Griguer <nathan.griguer@etu.univ-amu.fr>
 * @author  Dinesh Radjou <dinesh.radjou@etu.univ-amu.fr>
 */
class ResetPasswordValidator extends FormValidator
{
    /**
     * The list of the variables required for the password reset of a user.
     *
     * @var array
     */
    protected $required = ['pwdnew', 'pwdverif'];

    /**
     * This method validates the values given in $data to reset the password of a user with.
     *
     * @param array $data array, in adequation to the required value fields.
     *
     * @return void
     *
     * @throws ExceptionValidationResetPassword all the errors that might have been found
     */
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
