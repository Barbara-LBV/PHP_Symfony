<?php

include './HotBeverage.php';

class   TemplateEngine{

    public function __construct() {}

    public function __destruct() {}

    /**
     * Creates an HTML file by replacing the parameters in the template.
     *
     * @param HotBeverage $text : HotBeverage object containing the data to render.
     * @return bool : true if the file was created successfully, false otherwise.
     */

    function createFile(HotBeverage $text): bool
    {
        if (!$text instanceof HotBeverage) {
            print("Error: Invalid parameter: must be an HotBeverage object.\n");
            return false;
        }

        $template = file_get_contents('template.html');
        if ($template === false) {
            return false;
        }

        $reflectedClass = new ReflectionClass($text);
        $attributes = $reflectedClass->getProperties();

        if ($attributes === false) {
            print("Error: Unable to get parameters from the object.\n");
            return false;
        }

        $file = fopen($reflectedClass->getName() . '.html', 'w');
        if (!$file) {
            print("Error: Unable to open file for writing.\n");
            return false;
        }

        $content = '';
        $ordreredAttributes = ['name', 'price', 'resistance', 'description', 'comment'];
        $names = [];
        $values = [];
        $propertyNames = [];

        foreach ($attributes as $attribute) {
            $propertyNames[] = $attribute->getName();
        }

        foreach ($ordreredAttributes as $key => $attributeName) {
            if (!in_array($attributeName, $propertyNames, true)) {
                continue;
            }

            $getterName = 'get' . ucfirst($attributeName);
            if (!method_exists($text, $getterName)) {
                print("Warning: Getter '{$getterName}' does not exist and will be ignored.\n");
                continue;
            }

            $names[$key] = '{' . $attributeName . '}';
            $values[$key] = $text->{$getterName}();
        }

        $content = str_replace($names, $values, $template);
        
        fwrite($file, $content);
        fclose($file);
        return true;
    }
}

?>