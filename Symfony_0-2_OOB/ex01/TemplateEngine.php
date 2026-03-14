<?php

class   TemplateEngine {


    function createFile(string $fileName, Text $text): bool
    {
        if (!$text instanceof Text) {
            print("Error: Invalid parameter: must be Text object.\n");
            return false;
        }

        if (!is_string($fileName)) {
            print("Error: fileName is not a string\n");
            return false;
        }

        $renderedData = $text->readData();

        if ($renderedData === false) {
            print("Error: No data to render.\n");
            return false;
        }
    
        if (is_array($renderedData))
            $renderedData = implode('', $renderedData);

        $file = fopen($fileName, 'w');
        if (!$file) {
            print("Error: Unable to open file for writing.\n");
            return false;
        }

        $template = "<!DOCTYPE html>
        <html>
            <head>
                <title>New Text</title>
            </head>
            <body>
                <h1>New Text</h1>
                    {%renderedData}
            </body>
        </html>";

        $content = str_replace("{%renderedData}", $renderedData, $template);

        fwrite($file, $content);
        fclose($file);
        return true;
    }
}

?>
