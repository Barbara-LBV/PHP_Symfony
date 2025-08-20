<?php 

#[Attribute(\Attribute::TARGET_CLASS)]
class HotBeverage {

    protected string $_name;
    protected float $_price;
    protected float $_resistance;

    function __construct(string $name, float $price, string $resistance) {
        $this->_name = $name;
        $this->_price = $price;
        $this->_resistance = $resistance;
    }

    // getters
    function getPrice(): string {
        return $this->_price;
    }

    function getName(): string {
        return $this->_name;
    }

    function getResistance(): string {
        return $this->_resistance;
    }
}
?>