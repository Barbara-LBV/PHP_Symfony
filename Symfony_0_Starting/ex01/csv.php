<?php

$filename = "ex01.txt";

if (file_exists($filename)) {
    $content = file_get_contents($filename);
    $valeurs = explode(',', $content);
    foreach ($valeurs as $valeur) {
        print(trim($valeur) . "\n");
    }
} else {
    echo "No such file or directory: $filename\n";
}
/* file_get_contents() reads the entire file into a string
explode() splits the string into an array using the specified delimiter (comma in this case)
trim() removes whitespace from the beginning and end of a string
foreach() iterates over each element in the array and prints it*/
?>