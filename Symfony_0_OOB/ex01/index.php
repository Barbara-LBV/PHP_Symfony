<?php 

include('./TemplateEngine.php');
include ('./Text.php');

$file = new TemplateEngine();

$a = array(
    "Nom du livre = Les Trois Mousquetaires", 
    "auteur = Victor Hugo",
    "description = Un roman sur la vie des misérables dans la France du XIXe siècle.",
    "prix = 9.99€"
);
$parameters = new Text($a);
$b = "This is an additionnal text.";
$parameters->append($b);
$c = "another text to append";

$result = $file->createFile('test.html', $parameters);

?>