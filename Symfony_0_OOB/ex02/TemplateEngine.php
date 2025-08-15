<?php

class   TemplateEngine{

    function createFile(string $fileName, array $param): bool
    {
        if (!is_array($param)) {
            print("Error: Invalid parameter: must be an Array.\n");
            return false;
        }

        if (!is_string($fileName)) {
            print("Error: fileName is not a string\n");
            return false;
        }

        $file = fopen($fileName, 'w');
        if (!$file) {
            print("Error: Unable to open file for writing.\n");
            return false;
        }

        $template = "<!DOCTYPE html>
        <html>
            <head>
                <title>{name}</title>
            </head>
            <body>
                <h1>{name}</h1>
                <p>
                    Price: {price} &euro;<br />
                    Sleeping resistance: {resistence}/5
                </p>
                <p>Description: {description}</p>
                <p>Comment: {comment}</p>
            </body>
        </html>";

        $content = '';
        foreach ($param as $key => $value)
            $content = str_replace('{' . $key . '}', $value, $template);

        fwrite($file, $content);
        fclose($file);
        return true;
    }
}

?>
