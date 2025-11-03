<?php

namespace Core\includes\exception;

class ExceptionDeleteUserFailed extends \Exception
{
    public function __construct(string $message = "Échec de la suppresion du compte", int $code = 0)
    {
        parent::__construct($message, $code);
    }
}