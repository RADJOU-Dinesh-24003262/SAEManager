<?php

namespace Validator;

use Core\includes\exception\ExceptionValidation\ExceptionValidationToDoList;
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
     * @var string[] $required Required fields (none enforced by default escape, handled in validate).
     */
    protected $required = [];

    /**
     * This method validates the values given in $data.
     *
     * @param  array<string, mixed> $data Data to validate.
     * @return void
     * @throws ExceptionValidationToDoList If validation fails.
     */
    #[Override]
    public function validate(array $data): void
    {
        // Validate description if present.
        if (array_key_exists('description', $data)) {
            $desc = trim((string) $data['description']);
            if (empty($desc)) {
                throw new ExceptionValidationToDoList("La description ne doit pas être vide.");
            }

            if (strlen($desc) > 255) {
                throw new ExceptionValidationToDoList("La description ne doit pas dépasser 255 caractères.");
            }
        }

        // Validate priority if present.
        if (array_key_exists('priority', $data)) {
            $priority = intval($data['priority']);
            if (!in_array($priority, [1, 2, 3], true)) {
                throw new ExceptionValidationToDoList("Priorité invalide (doit être 1, 2 ou 3).");
            }
        }
    }
}
