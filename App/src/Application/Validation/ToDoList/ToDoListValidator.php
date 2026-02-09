<?php

namespace App\Application\Validation\ToDoList;

use App\Application\Validation\AbstractValidator;
use App\Application\Validation\Exception\EmptyFieldException;
use Exception;
use Override;

/**
 * Validator for ToDoList creation and updates.
 * Validates description and priority.
 *
 * @category Validation
 * @package  App\Application\Validation\ToDoList
 */
class ToDoListValidator extends AbstractValidator
{
    protected array $required = ['tododesc'];

    #[Override]
    public function validate(array $data): void
    {
        $this->checkRequired($data);
        
        // Validate description length
        if (strlen($data['tododesc']) > 255) {
            throw new Exception("La description ne doit pas dépasser 255 caractères.");
        }

        // Validate priority if provided
        if (isset($data['priority'])) {
            $priority = intval($data['priority']);
            if (!in_array($priority, [1, 2, 3], true)) {
                throw new Exception("Priorité invalide (doit être 1, 2 ou 3).");
            }
        }
    }
}