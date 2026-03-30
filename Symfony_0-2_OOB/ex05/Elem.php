<?php 

// declare(strict_types=1);
include './MyException.php';

class Elem {

    private string  $content;
    private string  $element;
    private string  $result;
    private array   $attributes;
    private array   $htmlElements = [];
    private int     $tableFlag = 0;

    private array   $autoClosing = ['meta', 'br', 'hr', 'img'];
    private array   $priorityTags = ['html', 'head', 'meta', 'title', 'body'];
    private array   $parentTags = ['div', 'table', 'tr', 'ol', 'ul'];
    private array   $closingTags = ['h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'li', 'span', 'th', 'td', 'p'];
    private array   $tableTags = ['tr', 'th', 'td'];
    private array   $tags = ['html', 'head', 'meta', 'title', 'body', 'div','p', 'img','hr', 'br', 
    'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'span', 'table', 'tr', 'th', 'td', 'ul', 'ol', 'li'];
    
    public function __construct(string $element, string $content = '', array $attributes = []){
        try {
            if (!in_array($element, $this->tags))
                throw new MyException("Invalid HTML tag: {$element}");

            $this->element = $element;
            $this->content = $content;
            $this->attributes = $attributes;
            $this->result = '';

            $key = array_search($this->element, $this->tags);
            $value = $this->initiateAttributes($this->attributes);
            $this->htmlElements[] = $element;
            
            switch ($key){
                case 0: // html tag
                    $this->htmlElements[$key] = $value . ">";
                    break;
                case 1: // head tag
                    $this->htmlElements[$key] = $value . ">";
                    break;
                case 2: // meta tag
                    $this->htmlElements[$key] = "$value {$this->content}/>";
                    break;
                case 3: // title tag
                    $this->htmlElements[$key] = "{$value}>{$this->content}</{$this->element}>";
                    break;
                case 4: // body tag
                    $this->htmlElements[$key] = "{$value}>";
                    break;
                case 5: // div tag
                    if ($this->content !== '')
                        $this->htmlElements[$key] = "{$value}>{$this->content}</{$this->element}>";
                    else
                        $this->htmlElements[$key] = "{$value}>";
                    break;
                case 6: // p tag
                    if ($this->content !== '')
                        $this->htmlElements[$key] = "{$value}{$this->content}</{$this->element}>";
                    else
                        $this->htmlElements[$key] = "{$value}>";
                    break;
                default: // other tags
                    $this->addOtherTags($value, 7);
                    break;
                }
            ksort($this->htmlElements);
        } catch (MyException $e) {
            echo $e->getMessage() . "\n";
        } 
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
        try {
            if ($elem->htmlElements) {
                foreach ($elem->htmlElements as $key => $value) {
                    if (in_array($value, $this->htmlElements) 
                        && !in_array($elem->element, $this->parentTags))
                        continue;
                    elseif ($this->checkListTags($value) === false
                        || $this->checkTableTags($value) === false){
                        // print("Skipping invalid tag: $value\n");
                        continue;
                        }
                    elseif (strstr($value, '<li') !== false)
                        $this->pushListTag($value);
                    elseif (in_array($elem->element, $this->tableTags)){
                        // print("PUSHING TABLE TAG: $value\n");
                        $this->pushTableTag($value, $elem->element);
                    }
                        
                    elseif (!array_key_exists($key, $this->htmlElements))
                        $this->htmlElements[$key] = $value; 
                    elseif ($value == ('<div>' || '<p>') && in_array($value, $this->htmlElements)){
                        $this->htmlElements[] = $value;
                        // print("ICIIIII {$value}\n");
                    } 
                    elseif (array_key_exists($key, $this->htmlElements)
                        && in_array($elem->element, $this->priorityTags)
                        && !in_array($value, $this->htmlElements))
                            array_splice($this->htmlElements, $key, 0, $value);
                    elseif (!in_array($elem->element, $this->priorityTags)){
                        $this->htmlElements[] = $value;
                        if ($elem->element == 'table')
                             $this->tableFlag++;
                        // print("LAAA {$value}\n");
                    }
                }
            } else {
                print("Error: Pushed Element has no HTML content.\n");
                return ;
            }
        } catch (MyListException $e) {
            echo $e->getMessage() . "\n";
        } catch (MyTableException $e) {
            echo $e->getMessage() . "\n";
        }
        $this->orderTags();
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

    public function validPage(): bool {
        $hasHtml = in_array('<html>', $this->htmlElements);
        $hasBody = in_array('<body>', $this->htmlElements);
        if (!$hasHtml || !$hasBody) {
            print("Error: A valid HTML page must contain both <html> and <body> tags.\n");
            return false;
        }
        return true;
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
        if (!in_array('</body>', $newElements))
             $newElements[] = "</body>";
        elseif (in_array('</body>', $newElements)) {
            $key = array_search('</body>', $newElements);
            if ($key !== count($newElements) - 1) {
                //Remove </body> from its current position and add it to the end
                array_splice($newElements, $key, 1);
                $newElements[] = '</body>'; 
            }
        }

        if (!in_array('</html>', $newElements))
             $newElements[] = "</html>";
        elseif (in_array('</html>', $newElements)) {
            $key = array_search('</html>', $newElements);
            if ($key !== count($newElements) - 1) {
                // Remove </html> from its current position and add it to the end
                array_splice($newElements, $key, 1); 
                $newElements[] = '</html>';
            }
        }
        return $newElements;
    }

    private function addOtherTags($value, $key){
        if (in_array($this->element, $this->autoClosing)){
            $this->htmlElements[] = "{$value} {$this->content}/>";
        }   
        elseif(in_array($this->element, $this->closingTags)){
            $this->htmlElements[$key] = "{$value}>{$this->content}</{$this->element}>";
        }
        elseif(in_array($this->element, $this->parentTags)){
            $this->htmlElements[] = "{$value}>{$this->content}";
        }
        if (strstr($value, '<table') !== false)
            $this->tableFlag++;
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

    private function tagName(string $tag): ?string {
        return preg_match('/^<\s*([a-z][a-z0-9]*)\b/i', $tag, $matches) ? strtolower($matches[1]) : null;
    }

    private function checkTableTags(string $value): bool {
        foreach ($this->tableTags as $tag) {
            if (!empty(strstr($value, $tag)) && !in_array('<table>', $this->htmlElements)) {
                throw new MyTableException();
                return false;
            }
            if (($tag == 'th' || $tag == 'td') && !empty(strstr($value, $tag)) 
                && in_array('<table>', $this->htmlElements)
                && !in_array('<tr>', $this->htmlElements)) {
                throw new MyTableException();
                return false;
            }
        }
        return true;
    }

    private function checkListTags(string $value): bool {
        if (strstr($value, "<li") !== false
        && !(in_array('<ol>', $this->htmlElements))
        && !(in_array('<ul>', $this->htmlElements))){
            throw new MyListException();
            return false;
        }
        return true;
    }

    private function orderTags(): void {
        $result  = [];
        $usedIdx = [];
        // 1) put priority tags, in proper order
        foreach ($this->priorityTags as $p) {
            foreach ($this->htmlElements as $i => $tag) {
                if (isset($usedIdx[$i])) 
                    continue;
                if ($this->tagName($tag) === $p) {
                    $result[] = $tag;
                    $usedIdx[$i] = true; // marcked as used
                }
            }
        }
        // 2) add others tags, no doubles except for parent tags
        foreach ($this->htmlElements as $i => $tag) {
            $trim_tag = trim(str_replace(['<', '>'], ' ', $tag));
            if (!isset($usedIdx[$i]) && in_array($trim_tag, $this->parentTags, true))
                $result[] = $tag; // allow duplicates for parent tags
            elseif (!isset($usedIdx[$i]) && !in_array($tag, $result, true))
                $result[] = $tag;
        }
        // 3) clean tab
        $this->htmlElements = array_values($result);
    }

    private function pushListTag(string $list) : void {
        $result  = [];
        $parentTags = ['ol', 'ul'];
        
        // get the last parent tag index
        foreach ($parentTags as $p) {
            foreach ($this->htmlElements as $i => $tag) {
                if ($this->tagName($tag) === $p)
                    $result[] = $i;
            }
        }

        $key = array_key_last($result);
        $key_value = $result[$key] ?? null;
        $maxIndex = count($this->htmlElements);
        if ($key !== null) {
            for ($i = $key_value; $i < $maxIndex; $i++) {
                if (strstr($this->htmlElements[$i + 1], '<li') === false){
                    array_splice($this->htmlElements, $i + 1, 0, $list);
                    array_splice($this->htmlElements, $i + 2, 0, ['</' . $this->tagName($this->htmlElements[$key_value]) . '>']);
                    break ;
                }
            }
        }
    }

    private function pushTableTag(string $value, string $element) : void {
        $parentTags = ['table', 'tr'];
        $result  = [];
        $index = [];

        if (!in_array($element, $this->tableTags)){
            print("Error: Invalid table tag: $element\n");
            return ;
        }
        try {
            if ($element === 'tr' && $this->tableFlag === 0) {
                    throw new MyTableException("Must be preceded by 'table' tag");
                    return ;    
            }
            foreach ($parentTags as $p) {
                foreach ($this->htmlElements as $i => $tag) {
                    if ($this->tagName($tag) === $p){
                        $result[] = $i;
                        $index[$i] = $tag;
                    }
                }
            }

            $key = array_key_last($result);
            $key_value = $result[$key] ?? null;
            if ($this->tagName($this->htmlElements[$key_value]) === 'tr' && $element == 'tr') {
                    print("Error: Cannot add 'tr' inside another 'tr'\n");
                    return ;
            }

            $maxIndex = count($this->htmlElements);
            if ($key !== null) {
            for ($i = $key_value; $i < $maxIndex; $i++) {
                if ($i + 1 === $maxIndex) {
                    $this->htmlElements[] = $value;
                    break ;
                }
                elseif (strstr($this->htmlElements[$i + 1], '<th') === false
                && strstr($this->htmlElements[$i + 1], '<td') === false) {
                    array_splice($this->htmlElements, $i + 1, 0, $value);
                    break ;
                }
            }
        }
        } catch (Exception $e) {
            echo $e->getMessage() . "\n";
        }
    }
}

?>
