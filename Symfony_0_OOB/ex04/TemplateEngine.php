<?php

include './Elem.php';

class   TemplateEngine{

    public Elem $elem;

    public function __construct(Elem $elem) {
        $this->elem = $elem;
    }

    function createFile(string $fileName): bool
    {
        if (!is_string($fileName)) {
            print("Invalid parameter: must be a string.\n");
            return false;
        }

        $file = fopen($fileName . '.html', 'w');
        if (!$file) {
            print("Error: Unable to open file for writing.\n");
            return false;
        }
        
        fwrite($file, $this->elem->result);
        fclose($file);
        return true;
    }
}

?>