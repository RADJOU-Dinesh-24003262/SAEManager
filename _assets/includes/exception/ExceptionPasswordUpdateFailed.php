<?php
namespace includes\exception;

class ExceptionPasswordUpdateFailed extends \Exception {
    public function __construct(string $message = "Échec de la mise à jour du mot de passe.", int $code = 0, \Throwable $previous = null) {
        parent::__construct($message, $code, $previous);
    }
}