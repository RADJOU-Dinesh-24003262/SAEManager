<?php

namespace Validator;

use Core\includes\exception\ExceptionValidation\ExceptionValidationEmpty;
use Exception;
use Override;

/**
 * Class ToDoListValidator
 * This class regroups function to validate the ToDo list tasks.

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
     * @param  array<string, mixed> $data Represent the data in the database.
     * @return void
     * @throws ExceptionValidationEmpty All the errors that might have been found.
     * @throws Exception If other validation rules fail.
     */
    #[Override]
    public function validate(array $data): void
    {
        if (empty($data['tododesc'])) {
            throw new ExceptionValidationEmpty();
        }

        if (strlen($data['tododesc']) > 255) {
            throw new Exception("La description ne doit pas dépasser 255 caractères.");
        }

        if (isset($data['priority'])) {
            $priority = intval($data['priority']);
            if (!in_array($priority, [1, 2, 3], true)) {
                throw new Exception("Priorité invalide.");
            }
        }
    }
}
