<?php 

include('./TemplateEngine.php');
include ('./Tea.php');
include('.Coffe.php');

$file = new TemplateEngine();

$tea = new Tea();
$coffe = new Coffee();

/*

*/
// $params = new ReflectionClass('Tea');
$result = $file->createFile('test_tea.html', $params);
// $params = new ReflectionClass('Coffee');
$result = $file->createFile('test_coffee.html', $params);

?>