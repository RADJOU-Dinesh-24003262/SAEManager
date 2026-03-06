<?php

namespace Core\Includes\Exception\ExceptionValidation;

use Exception;

/**
 * Class ExceptionValidationLogin
 *
 * Custom exception thrown when login credentials validation fails.
 *
 * This exception is intended to be used when user authentication fails due to
 * invalid credentials. It includes an optional additional information string
 * to provide more context about the failure.
 *
 * @category   Exception
 * @package    Core
 * @subpackage Includes/Exception/ExceptionValidation
 * @author     Alexandre Benhafessa <alexandre.benhafessa@etu.univ-amu.fr>
 * @author     François Dargentolle <francois.dargentolle@etu.univ-amu.fr>
 * @author     William Edelstein <william.edelstein@etu.univ-amu.fr>
 * @author     Nathan Griguer <nathan.griguer@etu.univ-amu.fr>
 * @author     Dinesh Radjou <dinesh.radjou@etu.univ-amu.fr>
 * @license    MIT License https://opensource.org/licenses/MIT
 * @link       https://github.com/RADJOU-Dinesh-24003262/SAEManager
 */
class ExceptionValidationLogin extends Exception
{
    /**
     * @var string Additional information about the login validation failure
     */
    private string $additionalInfo;

    /**
     * ExceptionValidationLogin constructor.
     *
     * @param  string $additionalInfo Optional. Additional details about the error.
     *                                Defaults to "Credentials not valid.".
     * @return void
     */
    public function __construct(string $additionalInfo = 'Credentials not valid.')
    {
        $this->additionalInfo = $additionalInfo;
        parent::__construct($additionalInfo);
    }

    /**
     * Returns additional information about the validation failure.
     *
     * @return string The additional information provided at exception creation
     */
    public function getAdditionalInfo(): string
    {
        return $this->additionalInfo;
    }
}
