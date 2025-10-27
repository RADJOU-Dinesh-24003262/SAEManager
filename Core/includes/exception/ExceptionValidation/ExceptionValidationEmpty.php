<?php

namespace Core\includes\exception\ExceptionValidation;

/**
 * ExceptionValidationEmpty
 *
 * Custom exception thrown when a required field is empty during validation.
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
class ExceptionValidationEmpty extends \Exception
{
    /**
     * Constructor for the ExceptionValidationEmpty class.
     *
     * Initializes the exception with a message indicating which field is empty.
     *
     * @param  string  $fieldName The name of the field that is empty. Defaults to an empty string.
     * @param  integer $code      Optional error code. Defaults to 0.
     * @return void
     */
    public function __construct(string $fieldName = "", int $code = 0)
    {
        $message = $fieldName
            ? "Le champ '$fieldName' ne doit pas être vide."
            : "Un champ obligatoire est vide.";
        parent::__construct($message, $code);
    }
}
