<?php

namespace Controllers\ToDoList;

use Controllers\ControllerInterface;
use includes\exception\ExceptionValidationEmptys;
use Models\ToDoList\ToDoList;
use Utilis\SessionService;
use Utilis\Validator\ToDoListValidator;
use Views\ToDoList\ToDoListView;
use includes\exception\ExceptionValidation;

class ToDoListPost implements ControllerInterface
{


    public function control(): void
    {
        $validator = new ToDoListValidator();
    }

    public static function support(string $path, string $method): bool
    {
        // TODO: Implement support() method.
    }
}