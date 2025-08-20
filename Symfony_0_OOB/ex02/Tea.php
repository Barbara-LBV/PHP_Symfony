<?php 

#[HotBeverage]
class Tea extends HotBeverage {

    private string $_description;
    private string $_comment;

    public function __construct(string $name, float $price, float $resistance, string $description, string $comment) {
        parent::__construct($name, $price, $resistance);
        $this->_description = $description;
        $this->_comment = $comment;
    }

    // getters
    public function getDescription(): string {
        return $this->_description;
    }

    public function getComment(): string {
        return $this->_comment;
    }
}
?>