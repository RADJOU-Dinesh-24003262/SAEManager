<?php
namespace Controllers;

interface ControllerInterface 
{
    /**
     * Méthode principale du contrôleur
     */
    function control(): void;
    
    /**
     * Vérifie si ce contrôleur peut traiter la requête
     */
    static function support(string $chemin, string $method): bool;
}