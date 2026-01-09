<?php


namespace Core\includes\exception;

use Exception;

/**
 * Class ExceptionEmailSendingFailed
 *
 * Custom exception thrown when a registration failed due to email duplication
 *
 * This exception should be used to indicate an error occurred during
 * the registration of the user, expetially when an email is already
 * stocked in Database. It helps differentiate general
 * exceptions from issues specifically related to registration.
 *
 * @category   Exception
 * @package    Core
 * @subpackage Includes/Exception
 * @author  Alexandre Benhafessa <alexandre.benhafessa@etu.univ-amu.fr>
 * @author  François Dargentolle <francois.dargentolle@etu.univ-amu.fr>
 * @author  William Edelstein <william.edelstein@etu.univ-amu.fr>
 * @author  Nathan Griguer <nathan.griguer@etu.univ-amu.fr>
 * @author  Dinesh Radjou <dinesh.radjou@etu.univ-amu.fr>
 * @license    MIT License https://opensource.org/licenses/MIT
 * @link       https://github.com/RADJOU-Dinesh-24003262/SAEManager
 */
class ExceptionEmailAlreadyExists extends Exception
{
    /**
     * ExceptionEmailSendingFailed constructor.
     *
     * Initializes the exception with a default or custom error message.
     *
     * @param string  $message Optional. Custom error message.
     *                         Defaults to "Erreur lors de l'envoi de l'email. Veuillez réessayer plus tard.".
     * @param integer $code    Optional. Custom error code (default 0).
     */


    private string $email;

    public function __construct(string $email, int $code = 0)
    {

        $this->email = $email;
        $message = "L'adresse email '{$email}' est déjà utilisée.";
        parent::__construct($message, $code);
    }

    public function getEmail(): string
    {
        return $this->email;
    }
}