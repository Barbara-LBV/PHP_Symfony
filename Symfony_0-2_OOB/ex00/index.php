<?php 

include('./TemplateEngine.php');

$file = new TemplateEngine();

$templateName = 'book_description.html';
$fileName = 'test.html';
$parameters = array(
    "nom" => "Les Misérables", 
    "auteur" => "Victor Hugo",
    "description" => "Un roman sur la vie des misérables dans la France du XIXe siècle.",
    "prix" => "9.99"
);
$result = $file->createFile($fileName, $templateName, $parameters);

?>