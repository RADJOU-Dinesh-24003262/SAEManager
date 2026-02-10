<?php

namespace App\GUI\Exception;

use Exception;

/**
 * Class DashboardException
 *
 * Custom exception thrown when an error occurs during the display of a user's profile.
 *
 * This exception should be used to indicate an issue while retrieving or
 * rendering user data on the dashboard or profile page.
 *
 * @category   Exception
 * @package    Core
 * @subpackage Includes/Exception
 * @author     Dinesh Radjou <dinesh.radjou@etu.univ-amu.fr>
 * @author     Alexandre Benhafessa <alexandre.benhafessa@etu.univ-amu.fr>
 * @author     François Dargentolle <francois.dargentolle@etu.univ-amu.fr>
 * @author     William Edelstein <william.edelstein@etu.univ-amu.fr>
 * @author     Nathan Griguer <nathan.griguer@etu.univ-amu.fr>
 * @license    MIT License https://opensource.org/licenses/MIT
 * @link       https://github.com/RADJOU-Dinesh-24003262/SAEManager
 */
class DashboardException extends Exception
{
    /**
     * DashboardException constructor.
     *
     * Initializes the exception with a default or custom message and optional error code.
     *
     * @param  string  $message Optional. Custom error message. Defaults to "Erreur lors de l'affichage du profil".
     * @param  integer $code    Optional. Custom error code. Defaults to 0.
     * @return void
     */
    public function __construct(
        string $message = "Erreur lors de l'affichage du profil",
        int $code = 0
        )
    {
        parent::__construct($message, $code);
    }
}