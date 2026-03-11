<?php

namespace Core\Includes\Exception;

use Exception;

/**
 * ExceptionCsrf
 *
 * Custom exception for CSRF validation failures.
 *
 * @category   Exception
 * @package    Core
 * @subpackage Includes/Exception
 * @author     Dinesh Radjou <dinesh.radjou@etu.univ-amu.fr>
 * @license    MIT License https://opensource.org/licenses/MIT
 * @link       https://github.com/RADJOU-Dinesh-24003262/SAEManager
 */
class ExceptionCsrf extends Exception
{
    /**
     * Constructor for the ExceptionCsrf class.
     *
     * @param  string $message Optional custom error message.
     */
    public function __construct(string $message = "Session invalide, veuillez réessayer.")
    {
        parent::__construct($message);
    }
}
