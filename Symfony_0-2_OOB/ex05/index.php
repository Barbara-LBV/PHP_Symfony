<?php 

include('./TemplateEngine.php');

try {
    $elem = new Elem('html');
    $body = new Elem('body');
    $body->pushElement(new Elem('p', 'Une balise p avec du texte', ['class'=> 'text-muted']));

    $head = new Elem('head');
    $meta = new Elem('meta', 'charset="UTF-8"'); 
 
    $elem->pushElement($head);
    $elem->pushElement($meta);

    $elem->pushElement(new Elem('title', 'My awesome PHP Piscine!'));
    $elem->pushElement($body);

    $div1 = new Elem('div');
    $span = new Elem('span', 'Voici une balise span');
    $div2 = new Elem('div', 'Voici une div avec du texte', ['class'=> 'text-muted']);
    $div1->pushElement($span);
    $div1->pushElement($div2);
    $elem->pushElement($div1);
    $elem->pushElement(new Elem('img', '',["src"=> "image.jpg", "class" => "text-muted"]));

    $elem->pushElement(new Elem('ol'));
    $elem->pushElement(new Elem('li', 'Ceci est une liste numerotée'));
    $elem->pushElement(new Elem('li', 'Ceci est une liste numerotée bis'));
    $elem->pushElement(new Elem('ul'));
    $elem->pushElement(new Elem('li', 'Ceci est une liste a puce'));
    $elem->pushElement(new Elem('li', 'Ceci est une liste a puce bis'));
    
    $elem->pushElement(new Elem('table', '', ["class" => "tabulation"]));
    $elem->pushElement(new Elem('tr'));
    $elem->pushElement(new Elem('th', 'Cellule d\'en-tete'));
    // $elem->pushElement(new Elem('table'));
    $elem->pushElement(new Elem('tr'));
    $th = new Elem('th', 'Cellule de tableau n.2');
    $elem->pushElement($th);
    $elem->pushElement(new Elem('th', 'Cellule de tableau n.3'));

    $elem->getHTML();
    $isPrintable = $elem->validPage();

    if ($isPrintable) {
        $file = new TemplateEngine($elem);
        $file->createFile('test');
    } else {
        print("Cette page HTML n'est pas valide.\n");
    }
} catch (MyException $e) {
    echo $e->getMessage() . "\n";
}

?>