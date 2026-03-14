<?php

class   TemplateEngine{

    public function createFile(string $fileName, string $templateName, array $parameters)
    {
        if (sizeof($parameters) != 4){
            print("Error: Invalid number of parameters.\n");
            return false;
        }

        if (!file_exists($templateName)) {
            print("Error: Template file does not exist.\n");
            return false;
        }
        
        $file = fopen($fileName, 'w');
        $content = file_get_contents($templateName);
        if ($content === false) {
            fclose($file);
            return false;
        }  
        foreach ($parameters as $key => $value)
            $content = str_replace('{' . $key . '}', $value, $content);
        fwrite($file, $content);
        fclose($file);
        return true;
    }
}

?>