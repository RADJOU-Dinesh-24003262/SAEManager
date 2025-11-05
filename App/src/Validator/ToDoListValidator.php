<?php

namespace Validator;

use Core\includes\exception\ExceptionValidation\ExceptionValidationEmpty;

/**
 * Class LoginValidator
 * This class regroup function to validate the login process of a user.

 * @category Validator

 * @package Src

 * @author Alexandre Benhafessa <alexandre.benhafessa@etu.univ-amu.fr>
 * @author François Dargentolle <francois.dargentolle@etu.univ-amu.fr>
 * @author William Edelstein <william.edelstein@etu.univ-amu.fr>
 * @author Nathan Griguer <nathan.griguer@etu.univ-amu.fr>
 * @author Dinesh Radjou <dinesh.radjou@etu.univ-amu.fr>

 * @license MIT License https://opensource.org/licenses/MIT

 * @link https://github.com/RADJOU-Dinesh-24003262/SAEManager
 */
class ToDoListValidator extends FormValidator
{
    /**
     * @var string[] $required Represent the description of the todolist.
     */
    protected $required = ['tododesc'];

    /**
     * This method validates the values given in $data to validate the todolist with their description.
     *
     * @param  array $data Represent the data in the database.
     * @return void
     * @throws ExceptionValidationEmpty All the errors that might have been found.
     */
    public function validate(array $data): void
    {
        if ($data['tododesc'] === '') {
            throw new ExceptionValidationEmpty("is empty");
        }
    }
}
