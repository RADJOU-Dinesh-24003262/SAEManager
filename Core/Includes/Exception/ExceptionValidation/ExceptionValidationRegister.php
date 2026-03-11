<?php

namespace Core\Includes\Exception\ExceptionValidation;

use Exception;

/**
 * ExceptionValidationRegister
 *
 * Custom exception thrown for a single validation error during registration.
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
class ExceptionValidationRegister extends Exception
{
    /**
     * @var string The name of the field that failed validation
     */
    private string $field;

    /**
     * @var string The type of validation error
     */
    private string $type;

    /**
     * @var string Optional additional information about the validation error
     */
    private string $additionalInfo;

    /**
     * Constructor for ExceptionValidationRegister.
     *
     * Initializes the exception with information about the failed field,
     * the type of validation failure, and optional additional information.
     *
     * @param  string $field          The field name that failed validation.
     * @param  string $type           The type of validation failure.
     * @param  string $additionalInfo Optional additional error context.
     * @return void
     */
    public function __construct(string $field, string $type, string $additionalInfo = '')
    {
        $this->field = $field;
        $this->type = $type;
        $this->additionalInfo = $additionalInfo;

        parent::__construct($additionalInfo);
    }

    /**
     * Gets the name of the field that caused the validation error.
     *
     * @return string
     */
    public function getField(): string
    {
        return $this->field;
    }

    /**
     * Gets the type of the validation error.
     *
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * Gets additional information about the validation failure.
     *
     * @return string
     */
    public function getAdditionalInfo(): string
    {
        return $this->additionalInfo;
    }
}
