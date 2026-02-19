<?php

namespace Core\includes\exception\ExceptionValidation;

use Exception;

/**
 * ExceptionValidationToDoList
 *
 * Custom exception thrown when todo list validation fails.
 *
 * @category   Exception
 * @package    Core
 * @subpackage Includes/Exception/ExceptionValidation
 * @author     Dinesh Radjou <dinesh.radjou@etu.univ-amu.fr>
 * @license    MIT License https://opensource.org/licenses/MIT
 * @link       https://github.com/RADJOU-Dinesh-24003262/SAEManager
 */
class ExceptionValidationToDoList extends Exception
{
    /**
     * Constructor.
     *
     * @param string $message error message.
     * @param int $code error code.
     */
    public function __construct(string $message = "Erreur de validation ToDoList.", int $code = 400)
    {
        parent::__construct($message, $code);
    }
}