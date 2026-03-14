<?php
include('./array2hash_sorted.php');

$array = array(array("Mary","28"), array("Pierre","30"), array("Nelly", "22"));
print_r ( array2hash_sorted($array) );
?>