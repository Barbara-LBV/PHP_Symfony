<?php 

function array2hash_sorted($array) : array {
    $hash = array();
    foreach ($array as $item) {
        if (count($item) == 2) {
            $hash[$item[0]] = $item[1];
        }
    }
    krsort($hash); // Sort the array by reversed keys
    return $hash;
}
?>