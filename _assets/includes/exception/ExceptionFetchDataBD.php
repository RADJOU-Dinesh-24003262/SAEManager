<?php

namespace includes\exception;

class ExceptionFetchDataBD extends \Exception
{
    public function __construct(string $message = 'Impossible de récuperer les données')
    {
        parent::__construct($message);
    }
}
