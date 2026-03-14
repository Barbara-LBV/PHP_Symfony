<?php 

class MyException extends Exception {

    public function __construct() {
        parent::__construct("MyException: No valid tag found");
    }
} 

?>