<?php 

include('./TemplateEngine.php');

try {
    $elem = new Elem('html');
    $body = new Elem('body');
    $body->pushElement(new Elem('elementary', 'truc'));
    print ("************************\n");
    $elem->pushElement($body);
    echo $elem->getHTML();

    $file = new TemplateEngine($elem);
    $file->createFile('test');
    print ("************************\n");
    $meta = new Elem('meta', 'charset="UTF-8"');
    $div1 = new Elem('div', 'Machin et bidule');
    $span = new Elem('span', 'This is sentence');
    $div2 = new Elem('div');
    $div1->pushElement($span);
    $elem->pushElement($meta);
    $elem->pushElement($div1);
    $elem->pushElement(new Elem('title', 'Fucking PHP'));
    $elem->pushElement(new Elem('head'));
    $elem->pushElement($div2);
    $elem->pushElement(new Elem('img', '',["src"=> "image.jpg", "class" => "text-muted"]));
    $elem->pushElement(new Elem('li', 'Ceci est une liste numerotée'));
    $elem->pushElement(new Elem('ol'));
    $elem->pushElement(new Elem('table'));
    $elem->pushElement(new Elem('tr'));
    $elem->pushElement($body);
    $elem->pushElement(new Elem('li', 'Ceci est une liste numerotée', ["class" => "text-muted"]));
    echo $elem->getHTML();

    $file2 = new TemplateEngine($elem);
    $file2->createFile('test2');
} catch (TypeError $e) {
    echo $e->getMessage() . "\n";
} 
?>