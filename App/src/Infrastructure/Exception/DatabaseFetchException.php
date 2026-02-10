<?php

namespace App\Infrastructure\Exception;

use Exception;

/**
 * DatabaseFetchException
 *
 * Custom exception thrown when fetching data from the database fails.
 *
 * @category   Exception
 * @package    Core
 * @subpackage Includes/Exception/ExceptionBD
 * @author     Dinesh Radjou <dinesh.radjou@etu.univ-amu.fr>
 * @author     Alexandre Benhafessa <alexandre.benhafessa@etu.univ-amu.fr>
 * @author     François Dargentolle <francois.dargentolle@etu.univ-amu.fr>
 * @author     William Edelstein <william.edelstein@etu.univ-amu.fr>
 * @author     Nathan Griguer <nathan.griguer@etu.univ-amu.fr>
 * @license    MIT License https://opensource.org/licenses/MIT
 * @link       https://github.com/RADJOU-Dinesh-24003262/SAEManager
 */
class DatabaseFetchException extends Exception
{
    /**
     * Constructor for the DatabaseFetchException class.
     *
     * Initializes the exception with a default or custom message.
     *
     * @param  string $message Optional custom error message. Defaults to "Impossible de récuperer les données".
     * @return void
     */
    public function __construct(string $message = 'Impossible de récuperer les données')
    {
        parent::__construct($message);
    }
}