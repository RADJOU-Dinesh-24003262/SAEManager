<?php

namespace Core\includes\exception\ExceptionBD;

use Exception;

/**
 * ExceptionUserNotFoundInBd
 *
 * Custom exception thrown when a user with a specific email is not found in the database.
 *
 * @category   Exception
 * @package    Core
 * @subpackage Includes/Exception/ExceptionBD
 * @author     Alexandre Benhafessa <alexandre.benhafessa@etu.univ-amu.fr>
 * @author     François Dargentolle <francois.dargentolle@etu.univ-amu.fr>
 * @author     William Edelstein <william.edelstein@etu.univ-amu.fr>
 * @author     Nathan Griguer <nathan.griguer@etu.univ-amu.fr>
 * @author     Dinesh Radjou <dinesh.radjou@etu.univ-amu.fr>
 * @license    MIT License https://opensource.org/licenses/MIT
 * @link       https://github.com/RADJOU-Dinesh-24003262/SAEManager
 */
class ExceptionUserNotFoundInDb extends Exception
{
    /**
     * Constructor for the ExceptionUserNotFoundInBd class.
     *
     * Initializes the exception with a message indicating that a user was not found.
     *
     * @param  string  $userEmail The email of the user that was not found. Defaults to an empty string.
     * @param  integer $code      Optional error code. Defaults to 0.
     * @return void
     */
    public function __construct(string $userEmail = "", int $code = 0)
    {
        $message = "L'utilisateur avec l'email '$userEmail' n'existe pas.";
        parent::__construct($message, $code);
    }
}
