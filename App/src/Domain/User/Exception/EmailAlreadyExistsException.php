<?php

namespace App\Domain\User\Exception;

use Exception;

/**
 * Class EmailAlreadyExistsException
 *
 * Custom exception thrown when registration fails due to email duplication.
 *
 * This exception should be used to indicate an error occurred during
 * the registration of the user, especially when an email is already
 * stored in the Database.
 *
 * @category   Exception
 * @package    Domain
 * @subpackage User/Exception
 * @author     Alexandre Benhafessa <alexandre.benhafessa@etu.univ-amu.fr>
 * @author     François Dargentolle <francois.dargentolle@etu.univ-amu.fr>
 * @author     William Edelstein <william.edelstein@etu.univ-amu.fr>
 * @author     Nathan Griguer <nathan.griguer@etu.univ-amu.fr>
 * @author     Dinesh Radjou <dinesh.radjou@etu.univ-amu.fr>
 * @license    MIT License https://opensource.org/licenses/MIT
 * @link       https://github.com/RADJOU-Dinesh-24003262/SAEManager
 */
class EmailAlreadyExistsException extends Exception
{
    /**
     * The email address that already exists.
     *
     * @var string
     */
    private string $email;

    /**
     * EmailAlreadyExistsException constructor.
     *
     * @param string  $email The email address that caused the conflict.
     * @param integer $code  Optional. Custom error code (default 0).
     */
    public function __construct(string $email, int $code = 0)
    {
        $this->email = $email;
        $message = "L'adresse email '{$email}' est déjà utilisée.";
        parent::__construct($message, $code);
    }

    /**
     * Gets the email address associated with the exception.
     *
     * @return string
     */
    public function getEmail(): string
    {
        return $this->email;
    }
}