<?php

namespace Core\includes\exception\ExceptionValidation;

/**
 * ExceptionValidationRegisters
 *
 * Custom exception thrown when there are multiple validation errors during registration.
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
class ExceptionValidationRegisters extends \Exception
{
    /**
     * @var ExceptionValidationRegister[] Array of individual field validation errors
     */
    private array $errors;

    /**
     * Constructor for the ExceptionValidationRegisters class.
     *
     * Initializes the exception with a list of validation errors.
     *
     * @param  ExceptionValidationRegister[] $errors Array of validation error objects.
     * @return void
     */
    public function __construct(array $errors)
    {
        parent::__construct("Erreurs de validation lors de l'inscription.");
        $this->errors = $errors;
    }

    /**
     * Returns the array of individual validation errors.
     *
     * @return ExceptionValidationRegister[]
     */
    public function getErrors(): array
    {
        return $this->errors;
    }
}
