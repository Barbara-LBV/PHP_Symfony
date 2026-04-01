<?php 

include './MyException.php';

class Elem {

    private string  $content;
    private string  $element;
    private string  $result = '';
    private array   $attributes;
    private array   $htmlElements = [];

    private array   $autoClosing = ['meta', 'br', 'hr', 'img'];
    private array   $parentTags = ['html','head','body','div', 'table', 'tr', 'ol', 'ul'];
    private array   $closingTags = ['title', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'li', 'span', 'th', 'td', 'p'];
    private array   $tags = ['html', 'head', 'meta', 'title', 'body', 'div', 'p', 'img', 'hr', 'br', 
    'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'span', 'table', 'tr', 'th', 'td', 'ul', 'ol', 'li'];
    
    public function __construct(string $element, string $content = '', array $attributes = []){
        if (!in_array($element, $this->tags))
            throw new MyException("Invalid HTML tag: {$element}");

        $this->element = $element;
        $this->content = $content;
        $this->attributes = $attributes;
		$this->setResult('');

        $value = $this->initiateAttributes($this->attributes);
        $this->addOtherTags($value);
    }

    public function __destruct(){}

    public function getElement(): string {
        return $this->element;
    }

    public function getContent(): string {
        return $this->content;
    }

    public function getHtmlElements(): array {
        return $this->htmlElements;
    }

    public function getAttributes(): array {
        return $this->attributes;
    }

    public function getResult(): string {
        return $this->result;
    }

	public function setResult(string $result): void {
        $this->result = $result;
    }

    public function pushElement(Elem $elem): void {
        if ($elem->htmlElements) {
            foreach ($elem->htmlElements as $key => $value) {
                if (in_array($value, $this->htmlElements))
                    continue ;
                else
                    $this->htmlElements[] = $value; 
            }
        } else {
            print("Error: Pushed Element has no HTML content.\n");
            return ;
        }
    }

    public function getHTML(): string {      
        if (empty($this->htmlElements)) {
            print("Error: No HTML elements to render.\n");
            return '';
        }

        // Reset result and openTags at each call
        $result = '';
        $openTags = [];

        $this->insertClosingHeadTag();
        $this->insertClosingParentTags();
        foreach ($this->htmlElements as $element) {
            // Track open tags for indentation (already closed by insertClosingParentTags)
            if (!preg_match('/^<\//', $element)) { // open tag
                if (preg_match('/<([a-zA-Z0-9]+)(?:\s[^>]*)?>$/', $element, $matches)) {
                    $tagName = $matches[1];
                    if (!in_array($tagName, $this->autoClosing)) {
                        array_push($openTags, $tagName);
                    }
                }
            } else { // closing tag
                if (preg_match('/<\/([a-zA-Z0-9]+)>$/', $element, $matches)) {
                    $tagName = $matches[1];
                    $key = array_search($tagName, $openTags);
                    if ($key !== false)
                        array_splice($openTags, $key, 1);
                }
            }
            $result .= $this->indentElement($element, count($openTags) - 1);
        }
        $this->setResult($result);
        return $result;
    }

    private function insertClosingHeadTag(): void {
        // Insert </head> tag before <body> if necessary
        $headIndex = array_search('<head>', $this->htmlElements);
        $bodyIndex = array_search('<body>', $this->htmlElements);
        $closeHeadIndex = array_search('</head>', $this->htmlElements);

        // Reindex the array
        $this->htmlElements = array_values($this->htmlElements); 
        if ($headIndex !== false && $bodyIndex !== false && $headIndex < $bodyIndex) {
            if ($closeHeadIndex === false || $closeHeadIndex > $bodyIndex) {
                // Insert </head> just before <body>
                array_splice($this->htmlElements, $bodyIndex, 0, ['</head>']);
            }
        } else if ($headIndex !== false && $bodyIndex !== false && $headIndex > $bodyIndex) {
            if ($closeHeadIndex === false) {
                // Insert </head> just after <head>
                array_splice($this->htmlElements, $headIndex + 1, 0, ['</head>']);
            }
        } else if ($headIndex !== false && $bodyIndex === false) {
            $metaIndex = $this->findFirstElementStartingWith('<meta');          
            $titleIndex = $this->findFirstElementStartingWith('<title');
            if ($closeHeadIndex === false) {
                if ($metaIndex !== false || $titleIndex !== false) {
                    $insertIndex = max($metaIndex, $titleIndex) + 1;
                    array_splice($this->htmlElements, $insertIndex, 0, ['</head>']);
                } else {
                    // Insert </head> just after <head>
                    array_splice($this->htmlElements, $headIndex + 1, 0, ['</head>']);
                }
            }
        }
    }

    private function insertClosingParentTags(): void {
        $stack = [];
        $newElements = [];
        $parentTags = ['div', 'table', 'tr', 'ol', 'ul'];
 
        foreach ($this->htmlElements as $element) {
            // get tag name from element using regex
            if (preg_match('/^<(?!\/)(?![^>]*\/>)[a-zA-Z][a-zA-Z0-9]*\b[^>]*>\s*\S/s', $element)) {
                // if there is content after the tag, add it to the new elements and continue
                $newElements[] = $element;
                continue;
            }
            if (preg_match('/^<([a-zA-Z0-9]+)/', $element, $matches)) {
                $tag = $matches[1];
                if (in_array($tag, $parentTags)) {
                    // if a parent tag is encountered, check if the previous one is the same and close it if necessary
                    while (!empty($stack) && $stack[count($stack)-1] === $tag) {
                        $newElements[] = "</$tag>";
                        array_pop($stack);
                    }
                    $stack[] = $tag;
                }
            }
            $newElements[] = $element;
        }
        // close any remaining open parent tags
        while (!empty($stack)) {
            $tag = array_pop($stack);
            $newElements[] = "</$tag>";
        }
        $newElements = $this->insertClosingHtmlTag($newElements);
        $this->htmlElements = $newElements;
    }

    private function insertClosingHtmlTag(array $newElements): array {
        $lastIndex = count($newElements) - 1;

        if (in_array('<html>', $newElements) && !in_array('</html>', $newElements))
            $newElements[] = "</html>";
        elseif (in_array('</html>', $newElements)) {
            $htmlKey = array_search('</html>', $newElements);
            if ($htmlKey !== $lastIndex) {
                // Remove </html> from its current position and add it to the end
                array_splice($newElements, $htmlKey, 1); 
                $newElements[] = '</html>';
            }
        }

        $lastIndex = count($newElements) - 1;

        // If body is present without a closing tag, add it before </html> or at the end if </html> is not present
        if (in_array('<body>', $newElements) && !in_array('</body>', $newElements) && in_array('</html>', $newElements))
            array_splice($newElements, $lastIndex, 0, ["</body>"]);
        elseif (in_array('<body>', $newElements) && !in_array('</body>', $newElements) && !in_array('</html>', $newElements))
            $newElements[] = '</body>';
        
        // If </body> is present but not at the end, move it to the end before </html> or at the end if </html> is not present
        elseif (in_array('</body>', $newElements)) {
            $bodyKey = array_search('</body>', $newElements);
            $htmlKey = array_search('</html>', $newElements);
            if ($htmlKey === false && $bodyKey !== $lastIndex) {
                // Remove </body> from its current position and add it to the end
                array_splice($newElements, $bodyKey, 1);
                array_splice($newElements, $lastIndex, 0, ['</body>']);
            } elseif ($htmlKey === $lastIndex && $bodyKey !== $lastIndex - 1) {
                // Remove </body> from its current position and add it just before </html>
                array_splice($newElements, $bodyKey, 1);
                array_splice($newElements, $lastIndex - 1, 0, ['</body>']);
            }
        }
        return $newElements;
    }

    private function findFirstElementStartingWith(string $prefix): int|false {
        foreach ($this->htmlElements as $index => $element) {
            if (str_starts_with($element, $prefix)) {
                return $index;
            }
        }
        return false;
    }

    private function addOtherTags(string $value): void {
        if (in_array($this->element, $this->autoClosing)){
            $this->htmlElements[] = "{$value} {$this->content}/>";
        }   
        elseif(in_array($this->element, $this->closingTags))
            $this->htmlElements[] = "{$value}>{$this->content}</{$this->element}>";
        elseif(in_array($this->element, $this->parentTags)){
            if ($this->element === 'div' && !empty($this->content)) 
                $this->htmlElements[] = "{$value}>{$this->content}</{$this->element}>";
            elseif ($this->element === 'div' && empty($this->content))
                $this->htmlElements[] = "{$value}>";
            else 
                $this->htmlElements[] = "{$value}>{$this->content}"; 
        } 

    }
    
    private function initiateAttributes(array $a) : string {
        $result = "<{$this->element}";
        foreach ($a as $key => $value)
             $result .= " {$key}=\"{$value}\"";
        return $result;
    }

	private function indentElement(string $element, int $level): string {
        // Ensure level is not negative
        $level = max(0, $level); 
        if ($element === '<html>' || $element === '</html>')
            $level = 0;
        elseif (array_search($element, $this->parentTags) === false)
            $level += 1;
        $indent = str_repeat(' ', $level);
        return $indent . $element . "\n";
    }

    public function validPage(): bool {
        $hasHtml = in_array('<html>', $this->htmlElements);

        if (!$hasHtml || array_search('<html>', $this->htmlElements) !== 0) {
            print("Error: A valid HTML page must contain both <html> at index 0.\n");
            return false;
        }

        if (!$this->checkHtmlBlock()) {
            print("Error: The <html> block is not properly structured.\n");
            return false;
        }
        if (!$this->checkHeadBlock()) {
            print("Error: The <head> block is not properly structured.\n");
            return false;
        }
        if (!$this->checkBodyblock()) {
            print("Error: The <body> block is not properly structured.\n");
            return false;
        }
        if (!$this->checkTableTags()) {
            print("Error: The <table> block is not properly structured.\n");
            return false;
        }
        if (!$this->checkListTags()) {
            print("Error: The list tags (<ul>, <ol>, <li>) are not properly structured.\n");
            return false;
        }
        return true;
    }

    private function checkHtmlBlock(): bool {
        // Check if <head> and <body> tags are present and properly nested
        $headOpenIndex = $this->findFirstElementStartingWith('<head');
        $headcloseIndex = $this->findFirstElementStartingWith('</head>');
        $bodyIndex = $this->findFirstElementStartingWith('<body');

        // check if head exists, it's directly placed after html tag
        if ($headOpenIndex !== false && $headOpenIndex !== 1) {
            echo "Error: <head> tag must be placed directly after <html> tag.\n";
            return false;
        }
        // check if head is before body
        if ($headOpenIndex !== false && $bodyIndex !== false && $headOpenIndex > $bodyIndex) {
            echo "Error: <head> tag must be placed before <body> tag.\n";
            return false;
        }
        // check if /head is before body
        if ($headcloseIndex !== false && $bodyIndex !== false && $headcloseIndex > $bodyIndex) {
            echo "Error: </head> tag must be placed before <body> tag.\n";
            return false;  
        }
        return true;
    }

    private function checkTableTags(): bool {
        // Check if <tr>, <th>, and <td> tags are properly nested within <table>
        $trIndex = $this->findFirstElementStartingWith('<tr');
        $thIndex = $this->findFirstElementStartingWith('<th');
        $tdIndex = $this->findFirstElementStartingWith('<td');
        $tableIndex = $this->findFirstElementStartingWith('<table');

        // check if <tr>, <th>, and <td> tags are present within a <table> tag
        if (($trIndex !== false || $thIndex !== false || $tdIndex !== false) && $tableIndex === false) {
            print("Error: <table> tag absent whereas <tr>, <th>, and <td> tags must be nested within a <table> block.\n");
            return false;
        } 
        // check if <tr> and <th> tags are present without <td> tag
        else if (($tdIndex !== false || $thIndex !== false) && $trIndex === false){
            print("Error: <tr> and <th> tags must be nested within a <td> block.\n");
            return false;
        } 
        // check if <tr> tags are properly nested within <table>, <td> and <th> tags are properly nested within <tr>
        else if ($tableIndex !== false) {
            if ($trIndex !== false && $trIndex !== $tableIndex + 1) {
                print("Error: <tr> tag must be directly nested after <table> block.\n");
                return false;
            } else if ($trIndex !== false && $tdIndex !== false && $tdIndex !== $trIndex + 1) {
                print("Error: <td> tag must be nested within a <tr> block.\n");
                return false;
            } else if ($trIndex !== false && $thIndex !== false && $thIndex !== $trIndex + 1) {
                print("Error: <th> tag must be nested within a <tr> block.\n");
                return false;
            } 
        }
        return true;
    }

    private function checkListTags(): bool  {
        // Check if <li> tags are properly nested within <ul> or <ol>
        $liIndex = $this->findFirstElementStartingWith('<li');
        $ulIndex = $this->findFirstElementStartingWith('<ul');
        $olIndex = $this->findFirstElementStartingWith('<ol');

        if ($liIndex !== false && $ulIndex === false && $olIndex === false) {
            print("Error: <li> tags must be nested within <ul> or <ol> block.\n");
            return false;
        } else if ($liIndex !== false && $ulIndex !== false && $liIndex !== $ulIndex + 1) {
            print("Error: <li> tags must be nested within a <ul> block.\n");
            return false;
        } else if ($liIndex !== false && $olIndex !== false && $liIndex !== $olIndex + 1) {
            print("Error: <li> tags must be nested within an <ol> block.\n");
            return false;
        }
        return true;
    }

    private function checkHeadBlock(): bool {
        // Check if <meta> and <title> tags are present within <head>
        $metaIndex= $this->findFirstElementStartingWith('<meta');
        $titleIndex = $this->findFirstElementStartingWith('<title');
        $headIndex = array_search('<head>', $this->htmlElements);

        if ($headIndex === false && ($metaIndex !== false || $titleIndex !== false)) {
            print("Error: <head> tag is missing.\n");
            return false;
        } elseif ($headIndex !== false)  {
            if (($metaIndex !== false && $metaIndex < $headIndex) || ($titleIndex !== false && $titleIndex < $headIndex)) {
                print("Error: <meta> or <title> tag is not nested in <head> block.\n");
                return false;
            }
        }
        return true;
    }
    
    private function checkBodyBlock(): bool {
        // Check if <head> and <body> tags are present and properly nested
        $headcloseIndex = $this->findFirstElementStartingWith('</head>');
        $bodyOpenIndex = $this->findFirstElementStartingWith('<body');
        $htmlCloseIndex = $this->findFirstElementStartingWith('</html>');

        // check if body exists, it's directly placed after html tag
        if ($bodyOpenIndex === false && $headcloseIndex !== false && $htmlCloseIndex !== false) {
            if ($htmlCloseIndex !== $headcloseIndex - 1) {
                echo "Error: if no <body> tag is found, </head> must be placed directly before </html>.\n";
                return false;
            }
        }
        return true;
    }
}

?>