<?php

namespace includes\exception;

class ExceptionDashboard extends \Exception
{
    public function __construct(string $message = 'Erreur lors de l\'affichage du profil')
    {
        parent::__construct($message);
    }
}
