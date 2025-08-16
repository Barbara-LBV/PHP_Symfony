<?php 

#[HotBeverage]
class Coffee extends HotBeverage {

    private string $description;
    private string $comment;

    public function __construct(string $name, float $price, float $resistance, string $description, string $comment) {
        parent::__construct($name, $price, $resistance);
        $this->description = $description;
        $this->comment = $comment;
    }

    // getters
    public function getDescription(): string {
        return $this->description;
    }

    public function getComment(): string {
        return $this->comment;
    }
}

?>