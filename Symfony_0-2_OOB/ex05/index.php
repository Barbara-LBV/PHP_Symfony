<?php 

include('./TemplateEngine.php');

try {
    $elem = new Elem('html');
    // $body = new Elem('body');
    // $body->pushElement(new Elem('p', 'Lorem ipsum', ['class'=> 'text-muted']));
    // $elem->pushElement($body);
    // echo $elem->getHTML();
    // $elem = new Elem('undefined'); // Leve une exception de type MyException$

    // $file = new TemplateEngine($elem);
    // $file->createFile('test');
    print ("************************\n");
    $head = new Elem('head');
    $meta = new Elem('meta', 'charset="UTF-8"');
    $div1 = new Elem('div');
    // $span = new Elem('span', 'This is span sentence');
    // $div2 = new Elem('div');
    // $div1->pushElement($span);
    $elem->pushElement($head);
    // print_r($head->getHtmlElements());
    $elem->pushElement($meta);
    $elem->pushElement(new Elem('title', 'Fucking PHP'));
    $elem->pushElement($div1);

    // $elem->pushElement($div2);
    //  print("\n6***********************\n");
    // $elem->pushElement(new Elem('img', '',["src"=> "image.jpg", "class" => "text-muted"]));
    // $elem->pushElement(new Elem('li', 'Ceci est une liste numerotée'));
    // $elem->pushElement(new Elem('ol'));
    // $elem->pushElement(new Elem('img', '',["src"=> "image_V2.jpg"]));
    // $elem->pushElement(new Elem('table', '', ["class" => "tabulation"]));
    // $elem->pushElement(new Elem('tr'));
    // $elem->pushElement(new Elem('table'));
    // $elem->pushElement(new Elem('th', 'cellule 1'));
    // $th = new Elem('th', 'cellule 2');
    // $elem->pushElement(new Elem('tr'));
    // $elem->pushElement($th);
    // $elem->pushElement(new Elem('td', 'cell division 1'));
    // $elem->pushElement(new Elem('tr', '', ["class" => "table-primary"]));
    // $elem->pushElement(new Elem('th', 'cellule 3'));
    // $elem->pushElement($body);
    // $elem->pushElement(new Elem('li', 'Ceci est une liste numerotée', ["class" => "text-muted"]));
    // $elem->pushElement(new Elem('img', '',["src"=> "image_V2.jpg"]));
    // $elem->pushElement(new Elem('li', 'This a html list'));
    echo $elem->getHTML();
    echo $elem->validPage() ? "This is a valid HTML page.\n" : "This is not a valid HTML page.\n";

    $file2 = new TemplateEngine($elem);
    $file2->createFile('test2');
} catch (TypeError $e) {
    echo $e->getMessage() . "\n";
}

?>