<?php

namespace Core\includes\exception\ExceptionValidation;

/**
 * ExceptionValidationEmpty
 *
 * Custom exception thrown when the SAE creation process fails.
 *
 * @category   Exception
 * @package    Core
 * @subpackage Includes\Exception\ExceptionValidation
 * @author     Alexandre Benhafessa <alexandre.benhafessa@etu.univ-amu.fr>
 * @author     François Dargentolle <francois.dargentolle@etu.univ-amu.fr>
 * @author     William Edelstein <william.edelstein@etu.univ-amu.fr>
 * @author     Nathan Griguer <nathan.griguer@etu.univ-amu.fr>
 * @author     Dinesh Radjou <dinesh.radjou@etu.univ-amu.fr>
 * @license    MIT License https://opensource.org/licenses/MIT
 * @link       https://github.com/RADJOU-Dinesh-24003262/SAEManager
 */
class ExeptionValidationSAECreation extends \Exception
{
    /**
     * @var string Additional information about the SAE creation validation failure
     */
    private string $additionalInfo;

    /**
     * ExeptionValidationSAECreation constructor.
     *
     * @param  string $additionalInfo Optional. Additional details about the error.
     *                                Defaults to "Invalid fields".
     * @return void
     */
    public function __construct(string $additionalInfo = 'Invalid fields.')
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
