<?php 

class MyException extends Exception {

    public function __construct() {
        parent::__construct("MyException: No valid tag found");
    }
}

class MyListException extends Exception {

    public function __construct() {
        parent::__construct("MyListException: 'li' tag must be preceeded by 'ol' or 'ul' tags");
    }
} 

?>