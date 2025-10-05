<?php
namespace includes\exception;

class ExceptionInvalidToken extends \Exception {
    public function __construct(string $message = "Token invalide ou expiré.", int $code = 0, \Throwable $previous = null) {
        parent::__construct($message, $code, $previous);
    }
}