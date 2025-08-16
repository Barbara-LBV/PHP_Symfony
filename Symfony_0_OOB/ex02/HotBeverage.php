<?php 

#[Attribute(\Attribute::TARGET_CLASS)]
class HotBeverage {

    public string $name;
    public float $price;
    public float $resistance;

    public function __construct(string $name, float $price, string $resistance) {
        $this->name = $name;
        $this->price = $price;
        $this->resistance = $resistance;
    }

    // getters
    public function getPrice(): string {
        return $this->price;
    }

    public function getName(): string {
        return $this->name;
    }

    public function getResistance(): string {
        return $this->resistance;
    }
}
?>