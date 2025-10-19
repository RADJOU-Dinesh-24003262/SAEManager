<?php
namespace Controllers;

/**
 * Class User
 
 * @package     src

 * @subpackage  Controllers

 * @author      Benhafessa Alexandre, Dargentolle Francois, Edelstein William, Griguer Nathan, Radjou Dinesh

 * This class is the interface to be implemented for all the controllers.
 */
interface ControllerInterface 
{
    /**
     * Principal manager of the controller
     * 
     * @return void
     */
    function control(): void;
    
    /**
     * Check if this controller can handle the request
     * 
     * @return boolean Is the method supported?
     */
    static function support(string $chemin, string $method): bool;
}
