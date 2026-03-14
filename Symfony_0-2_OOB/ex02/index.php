<?php 

include('./TemplateEngine.php');
include ('./Tea.php');
include('./Coffee.php');

$file = new TemplateEngine();

$t_description = "A strong and aromatic black tea";
$t_comment = "Perfect for a morning boost";

$c_description = "Pure arabica from Ethiopia";
$c_comment = "Cocoa and mushroom notes";

$tea = new Tea("Russian Earl Grey", 4.50, 3.5, $t_description, $t_comment);
$coffee = new Coffee("Expresso", 2.50, 4.5, $c_description, $c_comment);

/*

*/
// $params = new ReflectionClass($tea);
// $text = $class->getParentClass();
$result = $file->createFile($tea);
// $params = new ReflectionClass('Coffee');
$result = $file->createFile($coffee);

?>