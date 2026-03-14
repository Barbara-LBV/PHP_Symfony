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
?>