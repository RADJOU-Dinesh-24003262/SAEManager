<?php

namespace App\Validator;

use Core\includes\exception\ExceptionValidation\ExeptionValidationSAECreation;
use PDO;

/**
 * Class SAECreateValidator
 * This class regroup function to validate the SAE Creation process of a professor.

 * @category Validator

 * @package    Src
 * @subpackage Validator

 * @author Alexandre Benhafessa <alexandre.benhafessa@etu.univ-amu.fr>
 * @author François Dargentolle <francois.dargentolle@etu.univ-amu.fr>
 * @author William Edelstein <william.edelstein@etu.univ-amu.fr>
 * @author Nathan Griguer <nathan.griguer@etu.univ-amu.fr>
 * @author Dinesh Radjou <dinesh.radjou@etu.univ-amu.fr>

 * @license MIT License https://opensource.org/licenses/MIT

 * @link https://github.com/RADJOU-Dinesh-24003262/SAEManager
 */
class SAECreateValidator extends FormValidator
{
    /**
     * The list of the variables required for the SAE Creation of a professor.
     *
     * @var array
     */
    protected $required = ['name', 'desc', 'client'];


    private function validateClient(PDO $connection, int $client) : bool{
        $stmt = $connection->prepare('SELECT * FROM clients WHERE client_id = :id');
        $stmt->execute(['id' => $client]);
        return (!$stmt->rowCount() > 0);
    }

    private function validateSaeName(PDO $connection, String $name) : bool{
        return (!strlen($name) > 255);
    }

    private function validateSaeDesc(PDO $connection, String $desc) : bool{
        return (!strlen($desc) > 255);
    }

}
