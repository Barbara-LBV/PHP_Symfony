<?php 

include './TemplateEngine.php';
include './Text.php';

$tab = array(
    "Nom du livre = Les Misérables", 
    "auteur = Victor Hugo",
    "description = Un roman sur la vie des misérables dans la France du XIXe siècle.",
    "prix = 9.99 euros"
);

$file = new TemplateEngine();
$parameters = new Text($tab);

$b = "This is an additionnal text.";
$parameters->append($b);

$c = "another text to append";
$parameters->append($c);

$result = $file->createFile('test.html', $parameters);

?>