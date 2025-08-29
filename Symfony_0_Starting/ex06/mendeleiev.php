<?php 

function getValues($str) {
    
    $handle = fopen($str, "r");
    $elements = [];

    if ($handle) {
        while (($line = fgets($handle)) !== false) {
            $list = explode(",", trim($line));

            // Le premier élément contient name=xxx
            $first = explode("=", array_shift($list));
            print_r($first);
            $name = trim($first[0]);
            $values = [];

            // Boucle générique sur toutes les paires clé:valeur restantes
            foreach (explode(":", $first[1]) as $i => $part) {
                if ($i === 0) continue; // ignorer la "clé" de la première paire
            }

            // on traite tous les autres éléments (clé:valeur)
            foreach ($list as $item) {
                $tmp = explode(":", $item, 2);
                if (count($tmp) === 2) {
                    $values[trim($tmp[0])] = trim($tmp[1]);
                }
            }

            // insérer aussi la valeur du premier élément
            $firstSplit = explode(":", $first[1], 2);
            if (count($firstSplit) === 2) {
                $values[trim($firstSplit[0])] = trim($firstSplit[1]);
            }

            $elements[] = array_merge(['name' => $name], $values);
        }
        fclose($handle);
    } else {
        echo "Error opening the file.";
    }
    print_r($elements);

}

getValues("ex06.txt");
echo "done";
?>