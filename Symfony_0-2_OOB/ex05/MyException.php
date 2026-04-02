<?php 

class MyException extends Exception {

    public function __construct(string $message = "MyException: No valid tag found") {
        parent::__construct($message);
    }
} 


// class MyListException extends Exception {

//     public function __construct(string $message = "MyListException: 'li' tag must be preceeded by 'ol' or 'ul' tags") {
//         parent::__construct($message);
//     }
    
// }

// class MyTableException extends Exception {

//     public function __construct(string $message = "MyTableException: 'tr', 'th' and 'td' tags must be preceeded by 'table' tag") {
//         parent::__construct($message);
//     }
// } 

?>