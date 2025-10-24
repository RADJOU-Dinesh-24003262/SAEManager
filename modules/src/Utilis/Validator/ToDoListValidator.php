<?php

namespace Utilis\Validator;

use includes\exception\ExceptionValidation;

/**
 * Class LoginValidator
 * This class regroup function to validate the login process of a user.

 * @category Utilis

 * @package Src

 * @subpackage Utilis\Validator

 * @author  Alexandre Benhafessa <alexandre.benhafessa@etu.univ-amu.fr>
 * @author  François Dargentolle <francois.dargentolle@etu.univ-amu.fr>
 * @author  William Edelstein <william.edelstein@etu.univ-amu.fr>
 * @author  Nathan Griguer <nathan.griguer@etu.univ-amu.fr>
 * @author  Dinesh Radjou <dinesh.radjou@etu.univ-amu.fr>

 * @license MIT License https://opensource.org/licenses/MIT

 * @link https://github.com/RADJOU-Dinesh-24003262/SAEManager
 */
class ToDoListValidator extends FormValidator
{
    protected $required = ['tododesc'];
    public function validate(array $data): void
    {
        if (!$this->is_string($data['tododesc'])) {
            throw new ExceptionValidation('To do list needs to be a string');
        }
    }
}
