<?php 

include './MyException.php';

class Elem {

    private string  $content;
    private string  $element;
    private string  $result = '';
    private array   $attributes;
    private array   $htmlElements = [];

    private array   $autoClosing = ['meta', 'br', 'hr', 'img'];
    private array   $priorityTags = ['html', 'head', 'meta', 'title', 'body'];
    private array   $parentTags = ['html','body','div', 'table', 'tr', 'ol', 'ul'];
    private array   $closingTags = ['h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'li', 'span', 'th', 'td', 'p'];
    private array   $tags = ['html', 'head', 'meta', 'title', 'body', 'div', 'p', 'img','hr', 'br', 
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
        $toReplace = ['<', '>', '/  '];

        if ($elem->htmlElements) {
            foreach ($elem->htmlElements as $key => $value) {
                $trim_value = trim(str_replace($toReplace, ' ', $value));
                if (in_array($value, $this->htmlElements)) {
                    print("1- Already added tag $value\n");
                    continue ;
                }   
                elseif (!array_key_exists($key, $this->htmlElements)){
                    print("2- Adding new tag: $value\n");
                    $this->htmlElements[] = $value; 
                } 
                elseif (($value === '<div>' || $value === '<p>') && in_array($value, $this->htmlElements)) {
                    print("3- Adding new tag: $value\n");
                    $this->htmlElements[] = $value;
                } 
                elseif (!in_array($trim_value, $this->priorityTags))
                    print("5- Adding new tag: $value\n");
                    $this->htmlElements[] = $value;
            }
        } 
        else {
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
    }

    private function insertClosingParentTags(): void {
        $stack = [];
        $newElements = [];
        foreach ($this->htmlElements as $element) {
            // get tag name from element using regex
            if (preg_match('/^<([a-zA-Z0-9]+)/', $element, $matches)) {
                $tag = $matches[1];
                if (in_array($tag, $this->parentTags)) {
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
        // Ensure </body> and </html> are at the end of the document
        // print_r($newElements); // Debugging line
        if (in_array('<body>', $newElements) && !in_array('</body>', $newElements))
            $newElements[] = "</body>";
        elseif (in_array('</body>', $newElements)) {
            // print("6- Ensuring </body> is at the end of the document.\n");
            $key = array_search('</body>', $newElements);
            print("Found </body> at index: " . $key . "\n"); // Debugging line
            print("count " .count($newElements) . "\n"); // Debugging line
            if (array_search('</html>', $newElements) && $key !== count($newElements) - 2) {
                //Remove </body> from its current position and add it to the end
                print("6 \n");
                array_splice($newElements, $key, 1);
                $newElements[count($newElements) - 2] = '</body>'; 
            }
            elseif (!array_search('</html>', $newElements) && $key !== count($newElements) - 1) {
                //Remove </body> from its current position and add it to the end
                print("7 \n");
                array_splice($newElements, $key, 1);
                $newElements[] = '</body>'; 
            }
        }

        if (in_array('<html>', $newElements) && !in_array('</html>', $newElements))
             $newElements[] = "</html>";
        elseif (in_array('</html>', $newElements)) {
            print("7- Ensuring </html> is at the end of the document.\n");
            $key = array_search('</html>', $newElements);
            print("Found </html> at index: " . $key . "\n"); // Debugging line
            if ($key !== count($newElements) - 1) {
                // Remove </html> from its current position and add it to the end
                array_splice($newElements, $key, 1); 
                $newElements[count($newElements) - 1] = '</html>';
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
		$parentTags = ['<head>', '<body>', '<div>', '<table>', '<tr>', '<ol>', '<ul>'];
        // Ensure level is not negative
        $level = max(0, $level); 
        if ($element === '<html>' || $element === '</html>')
            $level = 0;
        elseif (array_search($element, $parentTags) === false)
            $level += 1;
        $indent = str_repeat(' ', $level);
        return $indent . $element . "\n";
    }

}

?>