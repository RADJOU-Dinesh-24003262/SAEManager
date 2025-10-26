<?php
namespace includes\exception\ExceptionBD;

class ExeptionuserNotFoundInBd extends \Exception
{
    public function __construct(string $userEmail = "", int $code = 0)
    {
        $message = "L'utilisateur avec l'email '$userEmail' n'existe pas";
        parent::__construct($message, $code );
    }
}