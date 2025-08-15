<?php 

class HotBeverage {

    public string $_name;
    public float $_price;
    public string $_resistence;

    public function __construct(string $name, float $price, string $resistence = "hot") {
        $this->_name = $name;
        $this->_price = $price;
        $this->_resistence = $resistence;
    }
}
?>