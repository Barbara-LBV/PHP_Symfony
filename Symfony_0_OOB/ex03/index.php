<?php 

include('./TemplateEngine.php');

$elem = new Elem('html');
$body = new Elem('body');
$body->pushElement(new Elem('p'));
$elem->pushElement($body);
// echo $elem->getHTML();

// $file = new TemplateEngine($elem);
// $file->createFile('test');

$meta = new Elem('meta', 'charset="UTF-8"');
$div1 = new Elem('div', 'This is a div');
$span = new Elem('span', 'This is sentence');
$div1->pushElement($span);
$elem->pushElement($meta);
$elem->pushElement($div1);
$elem->pushElement(new Elem('title', 'Fucking PHP'));
$elem->pushElement(new Elem('head'));
echo $elem->getHTML();

$file2 = new TemplateEngine($elem);
$file2->createFile('test2');

?>