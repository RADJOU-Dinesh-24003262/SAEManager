<?php

namespace App\Infrastructure\Exception;

use Exception;

/**
 * TokenCreationException
 *
 * Custom exception thrown when a token creation process fails.
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
class TokenCreationException extends Exception
{
    /**
     * Constructor for the TokenCreationException class.
     *
     * Initializes the exception with a default or custom message.
     *
     * @param  string $message Optional custom error message.
     *                         Defaults to "Erreur lors de la création du token. Veuillez réessayer plus tard.".
     * @return void
     */
    public function __construct(string $message = "Erreur lors de la création du token. Veuillez réessayer plus tard.")
    {
        parent::__construct($message);
    }
}