<?php

namespace Core\includes\exception;

/**
 * ExceptionSpam
 *
 * Custom exception for spam or abuse scenarios (e.g., too many password reset attempts).
 *
 * @package includes\exception
 * @author  Dinesh <dinesh.radjou@etu.univ-amu.fr>
 */
class ExceptionSpam extends \Exception
{
    /**
     * Constructor for the ExceptionSpam class.
     *
     * @param string $message Optional custom error message.
     */
    public function __construct(string $message = "Too many attempts. Please try again later.")
    {
        parent::__construct($message);
    }
}
