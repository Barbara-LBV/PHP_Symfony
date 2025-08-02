<?php 

function array2hash($array){
    $hash = array();
    foreach ($array as $item) {
        if (count($item) == 2) {
        $hash[$item[1]] = $item[0];
        }
    }
    return $hash;
}
?>