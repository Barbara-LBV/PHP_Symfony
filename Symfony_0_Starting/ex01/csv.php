<?php 

$s = file_get_contents('ex01.txt');
$s1 = str_split($s);
$s2 = array_map('trim', $s1);
$i = 0;
while ($i < count($s2)) {
    echo $s2[$i];
    $i++;
}
// fputcsv($f, ",");
// fclose($f);

?>