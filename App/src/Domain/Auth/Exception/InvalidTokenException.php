<?php

namespace App\Domain\Auth\Exception;

use Exception;

/**
 * InvalidTokenException
 *
 * Custom exception thrown when a token is invalid or has expired.
 *
 * @category   Exception
 * @package    Core
 * @subpackage Includes/Exception/ExceptionToken
 * @author     Alexandre Benhafessa <alexandre.benhafessa@etu.univ-amu.fr>
 * @author     François Dargentolle <francois.dargentolle@etu.univ-amu.fr>
 * @author     William Edelstein <william.edelstein@etu.univ-amu.fr>
 * @author     Nathan Griguer <nathan.griguer@etu.univ-amu.fr>
 * @author     Dinesh Radjou <dinesh.radjou@etu.univ-amu.fr>
 * @license    MIT License https://opensource.org/licenses/MIT
 * @link       https://github.com/RADJOU-Dinesh-24003262/SAEManager
 */
class InvalidTokenException extends Exception
{
    /**
     * Constructor for the InvalidTokenException class.
     *
     * Initializes the exception with a default or custom message and optional error code.
     *
     * @param  string  $message Optional custom error message. Defaults to "Token invalide ou expiré.".
     * @param  integer $code    Optional custom error code. Defaults to 0.
     * @return void
     */
    public function __construct(string $message = "Token invalide ou expiré.", int $code = 0)
    {
        parent::__construct($message, $code);
    }
}