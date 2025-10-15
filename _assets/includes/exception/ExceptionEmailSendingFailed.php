<?php

namespace includes\exception;

class ExceptionEmailSendingFailed extends \Exception {
    public function __construct(string $message = "Erreur lors de l'envoi de l'email. Veuillez réessayer plus tard.") {
        parent::__construct($message);
    }
}