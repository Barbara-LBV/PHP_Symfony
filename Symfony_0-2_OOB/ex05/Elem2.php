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

        // Reindex the array
        $this->htmlElements = array_values($this->htmlElements); 
        if ($headIndex !== false && $bodyIndex !== false && $headIndex < $bodyIndex) {
            // check if </head> already exists
            $closeHeadIndex = array_search('</head>', $this->htmlElements);
            if ($closeHeadIndex === false || $closeHeadIndex > $bodyIndex) {
                // Insert </head> just before <body>
                array_splice($this->htmlElements, $bodyIndex, 0, ['</head>']);
            }
        }
        else if ($headIndex !== false && $bodyIndex !== false && $headIndex > $bodyIndex) {
            // check if </head> already exists
            $closeHeadIndex = array_search('</head>', $this->htmlElements);
            if ($closeHeadIndex === false) {
                // Insert </head> just after <head>
                array_splice($this->htmlElements, $headIndex + 1, 0, ['</head>']);
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

    private function addOtherTags(string $value): void {
        if (in_array($this->element, $this->autoClosing)){
            $this->htmlElements[] = "{$value} {$this->content}/>";
        }   
        elseif(in_array($this->element, $this->closingTags)){
            $this->htmlElements[] = "{$value}>{$this->content}</{$this->element}>";
        }
        elseif(in_array($this->element, $this->parentTags)){
            if ($this->element === 'div' && !empty($this->content)) {
                $this->htmlElements[] = "{$value}>{$this->content}</{$this->element}>";
            } elseif ($this->element === 'div' && empty($this->content)){
                $this->htmlElements[] = "{$value}>";
            } else {
                $this->htmlElements[] = "{$value}>{$this->content}";
            }
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
        if (!$this->checkTableTags()) {
            print("Error: The <table> block is not properly structured.\n");
            return false;
        }
        if (!$this->checkListTags()) {
            print("Error: The list tags (<ul>, <ol>, <li>) are not properly structured.\n");
            return false;
        }
        if (!$this->checkPTag()) {
            print("Error: The <p> tags are not properly structured.\n");
            return false;
        }
        return true;
    }

    private function checkHtmlBlock(): bool {
        // Check if <head> and <body> tags are present and properly nested
        $headOpenIndex = array_search('<head>', $this->htmlElements);
        $headcloseIndex = array_search('</head>', $this->htmlElements);
        $bodyIndex = array_search('<body>', $this->htmlElements);
        // print("headOpenIndex: {$headOpenIndex}, headcloseIndex: {$headcloseIndex}, bodyIndex: {$bodyIndex}\n"); //debug

        // check if head exists, it's directly placed after html tag
        if ($headOpenIndex !== false && $headOpenIndex !== 1) {
            echo "Error: <head> tag must be placed directly after <html> tag.\n";
            return false;
        }
        // check if head is before body
        if ($headOpenIndex !== false && $bodyIndex !== false && $headOpenIndex < $bodyIndex) {
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
        // Check if <table> tag is present and properly closed
        // Check if <tr>, <th>, and <td> tags are properly nested within <table>
        return true;
    }

    private function checkListTags(): bool  {
        // Check if <ul> or <ol> tags are present and properly closed
        // Check if <li> tags are properly nested within <ul> or <ol>
        return true;
    }

    private function checkHeadBlock(): bool {
        // Check if <head> tag is present and properly closed
        // Check if <meta> and <title> tags are present within <head>
        return true;
    }

    private function checkPTag(): bool {
        // Check if <p> tags are properly closed and not nested within each other
        return true;
    }
}

?>