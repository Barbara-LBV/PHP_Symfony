<?php

include './HotBeverage.php';

class   TemplateEngine {

    function createFile(HotBeverage $beverage): bool
    {
        if (!$beverage instanceof HotBeverage) {
            print("Error: Invalid parameter: must be an HotBeverage object.\n");
            return false;
        }

        $template = file_get_contents('template.html');
        if ($template === false) {
            fclose($template);
            return false;
        }

        $reflectedClass = new ReflectionClass($beverage);
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
        $names = [];
        $values = [];

        foreach ($attributes as $attribute) {
            $name = $attribute->getName();
            $names[] = '{' . $name . '}';
            $values[] = $attribute->getValue($beverage);
        }
 
        $content = str_replace($names, $values, $template);
        fwrite($file, $content);
        fclose($file);
        return true;
    }
}

?>