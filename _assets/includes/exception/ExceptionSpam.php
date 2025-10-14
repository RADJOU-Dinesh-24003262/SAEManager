<?php 
namespace includes\exception;

class ExceptionSpam extends \Exception{
    public function __construct(string $message = "Trop de fois essayé, Veuillez réessayer plus tard") {
        parent::__construct($message);
    }
}