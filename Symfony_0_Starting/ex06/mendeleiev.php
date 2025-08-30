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

function addData(array $elements, string $key, string $value) : array {
    foreach ($elements as &$element) {
        $element[$key] = $value;
    }
}

function pushElement(array $elements) : array {
    $i = 0;

    while ($i < count($elements)) {
        $element = $elements[$i];
        while ($i + 1 < $element['position'])
        {
            array_splice($elements, $i + 1, 0, [[
                'name' => '',
                'number' => '',
                'small' => '',
                'molar' => '',
                'electron' => '',
                'position' => $i + 1,
                'place' => $i + 1,
                ]]);
            $i++;
        }
        $elements[$i]['place'] = $i + 1;
        $i++;

    if ($element['number'] < 120)
            $element['place'] = $i + 1;
    }
    return $elements;
}


function generateHtmlFile(array $elements, string $filename) : void {
    $html = "<!DOCTYPE html>\n<html>\n<head>\n<title>Elements</title>\n</head>\n<body>\n";
    $html .= "<table border-style='double' rules='all'>\n";

    foreach ($elements as $element) {
        $html .= "<tr>";
        $html .= "<td style='border: 1px solid border; padding: 10  px'>";
        $html .= "<h4>" . htmlspecialchars($element['name']) . "</h4>";
        $html .= "<ul>";
        $html .= "<li>No " . htmlspecialchars($element['number']) . "</li>";
        $html .= "<li>" . htmlspecialchars($element['small']) . "</li>";
        $html .= "<li>" . htmlspecialchars($element['molar']) . "</li>";
        $html .= "<li>" . htmlspecialchars($element['electron']) . "electron</li>";
        $html .= "</ul>";
        $html .= "<td>";
        $html .= "</tr>\n";
    }

    $html .= "</table>\n</body>\n</html>";
    // $file = fopen($fileName, 'w');
    //     if (!$file) {
    //         print("Error: Unable to open file for writing.\n");
    //         return ;
    //     }
    file_put_contents($filename, $html);
    // fwrite($file, $html);
    // fclose($file);
}

$elements = getValues("ex06.txt");
$elements = pushElement($elements);
print_r($elements);
generateHtmlFile($elements, "mendeleiev.html");
echo "done";
?>