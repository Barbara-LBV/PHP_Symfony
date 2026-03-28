<?php 

#[Attribute(\Attribute::TARGET_CLASS)]
class HotBeverage {

    protected string $name;
    protected float $price;
    protected float $resistance;

    function __construct(string $name, float $price, float $resistance) {
        $this->name = $name;
        $this->price = $price;
        $this->resistance = $resistance;
    }

    // getters
    function getPrice(): string {
        return $this->price;
    }

    function getName(): string {
        return $this->name;
    }

    function getResistance(): string {
        return $this->resistance;
    }
}
?>