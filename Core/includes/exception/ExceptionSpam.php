<?php

namespace Core\includes\exception;

use Exception;

/**
 * ExceptionSpam
 *
 * Custom exception for spam or abuse scenarios (e.g., too many password reset attempts).
 *
 * @category   Exception
 * @package    Core
 * @subpackage Includes/Exception
 * @author     Dinesh Radjou <dinesh.radjou@etu.univ-amu.fr>
 * @license    MIT License https://opensource.org/licenses/MIT
 * @link       https://github.com/RADJOU-Dinesh-24003262/SAEManager
 */
class ExceptionSpam extends Exception
{
    /**
     * Constructor for the ExceptionSpam class.
     *
     * Initializes the exception with a default or custom message.
     *
     * @param  string $message Optional custom error message. Defaults to "Too many attempts. Please try again later.".
     * @return void
     */
    public function __construct(string $message = "Too many attempts. Please try again later.")
    {
        parent::__construct($message);
    }
}
