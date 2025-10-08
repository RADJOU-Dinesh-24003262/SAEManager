<?php
namespace Controllers;

interface ControllerInterface 
{
    /**
     * Principal manager of the controller
     */
    function control(): void;
    
    /**
     * Check if this controller can handle the request
     */
    static function support(string $chemin, string $method): bool;
}