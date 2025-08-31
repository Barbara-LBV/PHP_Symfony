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
        while (($i + 1) % 18 < $element['position'])
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
    $html = "<!DOCTYPE html>\n<html>\n<head>\n<title>Elements</title>\n</head>\n<body>\n";
    $html .= "<table border-style='double' rules='all'>\n";
    $cols = 18; // nombre de colonnes par ligne
    $count = count($elements);

    for ($i = 0; $i < $count; $i += $cols) {
        $html .= "<tr>";
        for ($j = 0; $j < $cols; $j++) {
            $index = $i + $j;
            if ($index < $count) {
                $element = $elements[$index];
                // print_r($element);
                print("index: {$index} ");
                if (strlen($element['name']) != 0){
                    print("HERE\n");
                    $html .= "<td style='border: 1px solid border; padding: 10  px'>";
                    $html .= "<h4>" . htmlspecialchars($element['name']) . "</h4>";
                    $html .= "<ul>";
                    $html .= "<li>No " . htmlspecialchars($element['number']) . "</li>";
                    $html .= "<li>" . htmlspecialchars($element['small']) . "</li>";
                    $html .= "<li>" . htmlspecialchars($element['molar']) . "</li>";
                    $html .= "<li>" . htmlspecialchars($element['electron']) . " electron</li>";
                    $html .= "</ul>";
                    $html .= "</td>";
                    $i++;
                    $j++;
                } else {
                    print("ICI\n");
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
print_r($elements);
generateHtmlFile($elements, "mendeleiev.html");
echo "done";
?>