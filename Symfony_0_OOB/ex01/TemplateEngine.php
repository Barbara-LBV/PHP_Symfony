<?php

class   TemplateEngine{

    function createFile(string $fileName, Text $text)
    {
        if (!$text instanceof Text) {
            print("Error: Invalid parameter: must be Text object.\n");
            return false;
        }

        if (!is_string($fileName)) {
            print("Error: fileName is not a string\n");
            return false;
        }
        $renderData = $text->readData();
        if ($renderData === false) {
            print("Error: No data to render.\n");
            return false;
        }
        $file = fopen($fileName, 'w');
        $content = "<!DOCTYPE html>
        <html>
            <head>
                <title>New Text</title>
            </head>
            <body>
                <h1>New Text</h1>
                {%renderData}
            </body>
        </html>";

        fwrite($file, $content);
        fclose($file);
        return true;
    }
}

?>
<!-- 
<!DOCTYPE html>
<html>
	<head>
		<title>{nom}</title>
	</head>
	<body>
		<h1>{nom}</h1>
		<p>
			Auteur: <b>{auteur}</b><br />
			Description: {description}<br />
			Prix: <b>{prix} &euro;</b>
		</p>
	</body>
</html> -->