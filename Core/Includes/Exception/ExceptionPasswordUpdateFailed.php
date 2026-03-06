<?php

namespace Core\Includes\Exception;

use Exception;

/**
 * Class ExceptionPasswordUpdateFailed
 *
 * Custom exception thrown when a password update operation fails.
 *
 * This exception should be used to indicate that the process of updating
 * a user's password did not complete successfully. Common causes may include
 * database errors, invalid input, or permission issues.
 *
 * @category   Core
 * @package    Core
 * @subpackage Includes/Exception
 * @author     Alexandre Benhafessa <alexandre.benhafessa@etu.univ-amu.fr>
 * @author     François Dargentolle <francois.dargentolle@etu.univ-amu.fr>
 * @author     William Edelstein <william.edelstein@etu.univ-amu.fr>
 * @author     Nathan Griguer <nathan.griguer@etu.univ-amu.fr>
 * @author     Dinesh Radjou <dinesh.radjou@etu.univ-amu.fr>
 * @license    MIT License https://opensource.org/licenses/MIT
 * @link       https://github.com/RADJOU-Dinesh-24003262/SAEManager
 */
class ExceptionPasswordUpdateFailed extends Exception
{
    /**
     * ExceptionPasswordUpdateFailed constructor.
     *
     * Initializes the exception with a default or custom message and optional error code.
     *
     * @param string  $message Optional. Custom error message. Defaults to "Échec de la mise à jour du mot de passe.".
     * @param integer $code    Optional. Custom error code. Defaults to 0.
     *
     * @return void
     */
    public function __construct(string $message = "Échec de la mise à jour du mot de passe.", int $code = 0)
    {
        parent::__construct($message, $code);
    }
}
