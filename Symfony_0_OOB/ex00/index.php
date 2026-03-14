<?php 

include('./TemplateEngine.php');
include('./book_description.html');

$file = new TemplateEngine();
$parameters = array(
    "nom" => "Les Misérables", 
    "auteur" => "Victor Hugo",
    "description" => "Un roman sur la vie des misérables dans la France du XIXe siècle.",
    "prix" => "9.99€"
);
$result = $file->createFile('test.html', 'book_description.html', $parameters);

?>