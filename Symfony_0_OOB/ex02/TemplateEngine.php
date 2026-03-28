<?php

include './HotBeverage.php';

class   TemplateEngine{

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

        foreach ($attributes as $attribute) {
            $attribute->setAccessible(true);
            $name = $attribute->getName();
            $key = array_search($name, $ordreredAttributes);
            
            if ($key !== false) {
                $names[$key] = '{' . $name . '}';
                $values[$key] = $attribute->getValue($text);
            } else {
                print("Warning: Attribute '{$name}' is not in the ordered attributes list and will be ignored.\n");
            }
        }

        $content = str_replace($names, $values, $template);
        
        fwrite($file, $content);
        fclose($file);
        return true;
    }
}

?>