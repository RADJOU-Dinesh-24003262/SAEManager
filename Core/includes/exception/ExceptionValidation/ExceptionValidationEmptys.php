<?php

namespace Core\includes\exception\ExceptionValidation;

use Exception;

/**
 * ExceptionValidationEmptys
 *
 * Custom exception thrown when one or more required fields are empty during validation.
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
class ExceptionValidationEmptys extends Exception
{
    /**
     * @var array<int,ExceptionValidationEmpty> Array of individual field validation errors
     */
    private array $errors;

    /**
     * Constructor for ExceptionValidationEmptys.
     *
     * @param  array<int,ExceptionValidationEmpty> $errors Array of validation errors.
     * @return void
     */
    public function __construct(array $errors)
    {
        parent::__construct("Erreurs de validation : champs vides");
        $this->errors = $errors;
    }

    /**
     * Returns the array of validation errors.
     *
     * @return array<int,ExceptionValidationEmpty>
     */
    public function getErrors(): array
    {
        return $this->errors;
    }
}
