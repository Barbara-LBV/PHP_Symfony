<?php 

include('./TemplateEngine.php');

$elem = new Elem('html');
$body = new Elem('body');
$body->pushElement(new Elem('p', 'Lorem ipsum'));
$elem->pushElement($body);
echo $elem->getHTML();

$file = new TemplateEngine($elem);
$file->createFile('test');
$meta = new Elem('meta', 'charset="UTF-8"');
$elem->pushElement($meta);
echo $elem->getHTML();
$file2 = new TemplateEngine($elem);
$file2->createFile('test2');
?>