<?php

namespace Validator;

use Core\includes\exception\ExceptionValidation\ExceptionValidationRegister;
use Validator\FormValidator;

/**
 * Edit Profile Validator
 *
 * Validates user profile edit form data
 * @category Validator
 *
 * @package Src
 *
 * @subpackage Validator
 *
 * @author  Alexandre Benhafessa <alexandre.benhafessa@etu.univ-amu.fr>
 * @author  François Dargentolle <francois.dargentolle@etu.univ-amu.fr>
 * @author  William Edelstein <william.edelstein@etu.univ-amu.fr>
 * @author  Nathan Griguer <nathan.griguer@etu.univ-amu.fr>
 * @author  Dinesh Radjou <dinesh.radjou@etu.univ-amu.fr>
 *
 * @license MIT License https://opensource.org/licenses/MIT
 *
 * @link https://github.com/RADJOU-Dinesh-24003262/SAEManager
 */
class EditProfileValidator extends FormValidator
{
    /**
     * Required fields for profile edit form
     *
     * @var array
     */
    protected $required = ['phone'];

    /**
     * Validate the profile edit form data.
     *
     * @param array $data The form data to validate.
     * @return void
     * @throws ExceptionValidationRegister If validation fails.
     */
    public function validate(array $data): void
    {
        if (!($this->isValidPhone($data['phone']))) {
            throw new ExceptionValidationRegister("phone", "int", "Numéro de téléphone invalide.");
        }
    }
}
