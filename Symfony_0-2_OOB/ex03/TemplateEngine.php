<?php

include './Elem.php';

class   TemplateEngine{

    private Elem $elem;

    public function __construct(Elem $elem) {
        $this->elem = $elem;
    }

    public function __destruct() {}

    function createFile(string $fileName): bool
    {
        if (!is_string($fileName)) {
            print("Invalid parameter: must be a string.\n");
            return false;
        }

        if (empty($this->elem->getHtmlElements())) {
            print("Error: No HTML content to render.\n");
            return false;
        }

        // Always rebuild the HTML before writing to avoid stale output.
        $this->elem->getHTML();

        $file = fopen($fileName . '.html', 'w');
        if (!$file) {
            print("Error: Unable to open file for writing.\n");
            return false;
        }
        $content =  $this->elem->getResult();
        fwrite($file, $content);
        fclose($file);
        return true;
    }
}

?>