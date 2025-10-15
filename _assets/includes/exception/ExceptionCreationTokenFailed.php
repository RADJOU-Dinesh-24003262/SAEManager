<?php
namespace includes\exception;

class ExceptionCreationTokenFailed extends \Exception {
    public function __construct(string $message = "Erreur lors de la création du token. Veuillez réessayer plus tard.") {
        parent::__construct($message);
    }
}