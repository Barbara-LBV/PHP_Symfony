<?php 

class MyException extends Exception {

    public function __construct(string $message = "MyException: No valid tag found") {
        parent::__construct($message);
    }
} 

?>