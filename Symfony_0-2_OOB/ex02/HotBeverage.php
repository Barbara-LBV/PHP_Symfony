<?php

#[Attribute(\Attribute::TARGET_CLASS)]
class HotBeverage
{

    protected string $name;
    protected float $price;
    protected float $resistance;

    function __construct(string $name, float $price, float $resistance)
    {
        $this->name = $name;
        $this->price = $price;
        $this->resistance = $resistance;
    }

    public function __destruct() {}

    // getters
    function getPrice(): float
    {
        return $this->price;
    }

    function getName(): string
    {
        return $this->name;
    }

    function getResistance(): float
    {
        return $this->resistance;
    }
}
