<?php 

(include './HotBeverage.php');

class Tea extends HotBeverage {

    private string $_description;
    private string $_comment;

    public function __construct() {
        $this->_description = $description;
        $this->_comment = $comment;
        parent::__construct("Tea", 4.50, "mild");
    }

    public function getDescription(): string {
        return $this->_description;
    }

    public function getComment(): string {
        return $this->_comment;
    }

    public function setDescription(string $description): void {
        $this->_description = $description;
    }
    
    public function setComment(string $comment): void {
        $this->_comment = $comment;
    }
}
?>