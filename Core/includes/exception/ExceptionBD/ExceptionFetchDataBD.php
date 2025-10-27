<?php

namespace Core\includes\exception\ExceptionBD;

class ExceptionFetchDataBD extends \Exception
{
    public function __construct(string $message = 'Impossible de récuperer les données')
    {
        parent::__construct($message);
    }
}
