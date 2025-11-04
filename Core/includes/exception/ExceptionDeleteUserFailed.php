<?php

namespace Core\includes\exception;

/**
 * Class ExceptionDeleteUserFailed
 *
 * Custom exception thrown when an user is failed to delete
 *
 * This exception should be used to indicate an error occurred during
 * the process of sending a request to database to delete a user.
 *
 * @category   Exception
 * @package    Core
 * @subpackage Includes\Exception
 * @author     Dinesh Radjou <dinesh.radjou@etu.univ-amu.fr>
 * @license    MIT License https://opensource.org/licenses/MIT
 * @link       https://github.com/RADJOU-Dinesh-24003262/SAEManager
 */
class ExceptionDeleteUserFailed extends \Exception
{
    /**
     * Initializes the exception with a default or custom error message.
     *
     * @param string  $message Optional. Custom error message.
     * @param integer $code    Optional. Custom error code (default 0).
     */
    public function __construct(string $message = "Échec de la suppresion du compte", int $code = 0)
    {
        parent::__construct($message, $code);
    }
}
