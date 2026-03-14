<?php 

function getValues(string $str) : array {
    
    $handle = fopen($str, "r");
    $elements = [];

    if ($handle) {
        while (($line = fgets($handle)) !== false) {
            $list = explode(",", trim($line));

            // Le premier élément contient name=xxx
            $first = explode("=", array_shift($list));
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
            $values['place'] = 0;
            $elements[] = array_merge(['name' => $name], $values);
        }
        fclose($handle);
    } else {
        echo "Error opening the file.";
    }
    return $elements;
}

function pushElement(array $elements) : array {
    $i = 0;

    while ($i < count($elements)) {
        $element = $elements[$i];
        while ($i % 18 < $element['position'])
        {
            array_splice($elements, $i, 0, [[
                'name' => '',
                'number' => '',
                'small' => '',
                'molar' => '',
                'electron' => '',
                'position' => $i,
                'place' => $i,
                ]]);
            $i++;
        }
        $elements[$i]['place'] = $i;
        $i++;
    }
    return $elements;
}


function generateHtmlFile(array $elements, string $filename) : void {
    $html = "<!DOCTYPE html>\n<html>\n<head>\n<title>Elements</title>\n";
    $html .= "<style>\nbody {
    background-color: #fff;
    font-family: Trebuchet, sans-serif;
    margin: 0;
    padding: 10px;
}

h4 {
    color: #3e3e4d;
    text-align: center;
    margin: 1px 0;
    font-size: 12px;
}

ul {
    padding: 0;
    margin: 0;
    list-style: none;
    font-size: 10px;
    text-align: center;
}

table {
    border-collapse: collapse;   /* permet les doubles bordures */
    border-spacing: 0;           /* pas d’espacement supplémentaire */
    width: 100%;
    table-layout: fixed;         /* force largeur égale */
    border: 4px double #333;     /* double bordure autour du tableau */
}

td {
    width: calc(100% / 18);      /* 18 colonnes max */
    height: 100px;                /* ajuste la hauteur */
    border: 2px double #424242;  /* double bordure entre cellules */
    text-align: center;
    vertical-align: top;
    background: linear-gradient(145deg, #d6e2d9, #95a99a); /* effet léger */
    padding: 10px;
    box-sizing: border-box;
    border-collapse: separate; 
}

legend {
    text-align: center;
    padding: 50px;
    color: #2a4233ff;
}

td:hover {
    background: #c7d5cc;         /* survol plus clair */
    cursor: pointer;
}
\n</style>\n";
    $html .= "</head>\n<body>\n";
    $html .= "<legend class='legend'>Mendeleiv Elements Table</legend>";
    $html .= "<div>";
    $html .= "<table>\n";
    
    $cols = 18;
    $count = count($elements);

    for ($i = 0; $i < $count; $i += $cols) {
        $html .= "<tr>";
        for ($j = 0; $j < $cols; $j++) {
            $index = $i + $j;
            if ($index < $count) {
                $element = $elements[$index];
                if (strlen($element['name']) != 0){
                    $html .= "<td>";
                    $html .= "<h4>" . htmlspecialchars($element['name']) . "</h4>";
                    $html .= "<ul>";
                    $html .= "<li>No " . htmlspecialchars($element['number']) . "</li>";
                    $html .= "<li>" . htmlspecialchars($element['small']) . "</li>";
                    $html .= "<li>" . htmlspecialchars($element['molar']) . "</li>";
                    $html .= "<li>electrons :</li>";
                    $html .= "<li>" . htmlspecialchars($element['electron']) . "</li>";
                    $html .= "</ul>";
                    $html .= "</td>";
                } else {
                    $html .= "<td></td>";
                }
            }
        }
        $html .= "</tr>\n"; 
    }
    $html .= "</table>\n</body>\n</html>";
    file_put_contents($filename, $html);
}

$elements = getValues("ex06.txt");
$elements = pushElement($elements);
generateHtmlFile($elements, "mendeleiev.html");
echo "HTML file generated!";
?>