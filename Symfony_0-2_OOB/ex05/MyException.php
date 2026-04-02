<?php 

class MyException extends Exception {

    public function __construct(string $message = "MyException: No valid tag found") {
        parent::__construct($message);
    }
} 


class MyListException extends Exception {

    public function __construct(string $message = "MyListException: 'li' tag must be preceeded by 'ol' or 'ul' tags") {
        parent::__construct($message);
    }
    
}

class MyTableException extends Exception {

    public function __construct(string $message = "MyTableException: 'tr', 'th' and 'td' tags must be preceeded by 'table' tag") {
        parent::__construct($message);
    }
} 


class MyPException extends Exception {

    public function __construct(string $message = "MyPException: 'p' tag must contain only text and inline elements") {
        parent::__construct($message);
    }
} 


class MyHTMLtException extends Exception {

    public function __construct(string $message = "MyHTMLException: 'html' tag must be first and last element of a html file") {
        parent::__construct($message);
    }
    
}

class MyHeadBlockException extends Exception {

    public function __construct(string $message = "MyHeadBlockException: 'head' block is not properly structured") {
        parent::__construct($message);
    }
} 

class MyBodyBlockException extends Exception {

    public function __construct(string $message = "MyBodyBlockException: 'body' block is not properly structured") {
        parent::__construct($message);
    }
} 

?>