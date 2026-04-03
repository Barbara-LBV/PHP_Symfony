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
        if (in_array($element, ['html','head','table', 'tr', 'ol', 'ul']) && !empty($content))
            throw new MyException("Parent tags cannot have content: {$element}");

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
        if (empty($elem->htmlElements)) {
            print("Error: Pushed Element has no HTML content.\n");
            return;
        }

        // Remove only this parent closing to avoid deleting valid child closings.
        $generatedClosings = ["</{$this->element}>"];
        $this->htmlElements = array_values(array_filter(
            $this->htmlElements,
            function (string $value) use ($generatedClosings): bool {
                return !in_array($value, $generatedClosings, true);
            }
        ));

        // If no closing tag is found, append new elements (closing tag will be added on the next render if necessary)
        array_push($this->htmlElements, ...$elem->htmlElements);
    }

    public function getHTML(): string
    {
        if (empty($this->htmlElements)) {
            print("Error: No HTML elements to render.\n");
            return '';
        }

        // Reset result and openTags at each call
        $result = '';
        $openTags = [];

        $this->insertClosingParentTags();
        $this->insertClosingHeadTag();
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

        foreach ($this->htmlElements as $element) {
            // ignore les fermetures déjà présentes
            if (preg_match('/^<\//', $element)) {
                continue;
            }

            $tag = null;
            // check if the element is an opening tag and extract the tag name
            if (preg_match('/^<([a-zA-Z0-9]+)/', $element, $matches)) {
                $tag = $matches[1];
            }

            if ($tag !== null) {
                // Close parent tags if the current tag is not allowed as a child
                while (!empty($stack) && !$this->isAllowedChild($stack[count($stack) - 1], $tag)) {
                    $newElements[] = '</' . array_pop($stack) . '>';
                }

                // Stack the current tag if it's a parent tag
                $isInlineClosedTag = preg_match('/<\/' . preg_quote($tag, '/') . '>\s*$/', $element) === 1;
                if (in_array($tag, ['div', 'table', 'tr', 'ol', 'ul'], true) && !$isInlineClosedTag) {
                    $stack[] = $tag;
                }
            }
            $newElements[] = $element;
        }

        // Close any remaining open parent tags
        while (!empty($stack)) {
            $newElements[] = '</' . array_pop($stack) . '>';
        }
        $this->htmlElements = $this->insertClosingHtmlTag($newElements);
    }

    private function isAllowedChild(string $parent, string $child): bool {
        if ($parent === 'ol' || $parent === 'ul') {
            return $child === 'li';
        }
        if ($parent === 'table') {
            return $child === 'tr';
        }
        if ($parent === 'tr') {
            return $child === 'th' || $child === 'td';
        }
        return true; // div
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

    private function findFirstElementStartingWith(string $prefix, array $elements = []): int|false {
        // if $elements array is provided, otherwise search in the htmlElements array
        $searchArray = empty($elements) ? $this->htmlElements : $elements;
        foreach ($searchArray as $index => $element) {
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
        if (!$this->checkPBlock()) {
            print("Error: The <p> block is not properly structured.\n");
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

        // check if /head is before body
        if ($headcloseIndex !== false && $bodyIndex !== false && $headcloseIndex > $bodyIndex) {
            echo "Error: </head> tag must be placed before <body> tag.\n";
            return false;  
        }

        $htmlElements = [];
        $flagBody = 0;
        $flagHead = 0;
        for ($i = 0; $i < count($this->htmlElements); $i++) {
            if (str_starts_with($this->htmlElements[$i], '<body')) {
                $flagBody += 1;
            } elseif (str_starts_with($this->htmlElements[$i], '<head')) {
                $flagHead += 1;
            }
            $htmlElements[] = $this->htmlElements[$i];
        }

        if ($flagBody !== 1 || $flagHead !== 1) {
            print("Error: <html> block has to contain exactly one <head> and one <body> tags.\n");
            return false;
        }
        return true;
    }

    private function checkTableTags(): bool {
        // Check if <tr>, <th>, and <td> tags are properly nested within <table>
        for ($i = 0; $i < count($this->htmlElements); $i++) {
            if (str_starts_with($this->htmlElements[$i], '<table')) {
                if (!$this->checkParentTable($i+1, 'table')) {
                    print("Error: <table> tags must be followed by a <tr> block.\n");
                    return false;
                }
            }
            if (str_starts_with($this->htmlElements[$i], '<tr')) {
                if (!$this->checkParentTable($i+1, 'tr')) {
                    print("Error: <tr> tags must be followed by a <th> or <td> tags.\n");
                    return false;
                }
            }
            if (str_starts_with($this->htmlElements[$i], '<th') || str_starts_with($this->htmlElements[$i], '<td')) {
                for ($j = $i; $j > 0 ; $j--) {
                    if (str_starts_with($this->htmlElements[$j],"<tr"))
                        break ;
                }
                if (!$this->checkParentTable($j+1, 'tr')) {
                    print("Error: <th> and <td> tags must be nested within a <tr> block.\n");
                    return false;
                }
            }
        }
        return true;
    }

    private function checkParentTable(int $i, string $tag): bool {
        // Check if <tr>, <th>, and <td> tags are properly nested within <table>
        $size = count($this->htmlElements);
        $Elem = [];

        if ($i < 0 || $i >= $size)
            return false;

        while($i < $size) {
            if (str_starts_with($this->htmlElements[$i], '</' . $tag . '>'))
                break;
            $Elem[] = $this->htmlElements[$i];
            $i++;
        }

        if (empty($Elem))
            return false;

        if ($tag === 'table') {
            if (!str_starts_with($Elem[0], '<tr')) {
                return false;
            }
        } elseif ($tag === 'tr') {
            foreach($Elem as $elem) {
                if (!str_starts_with($elem, '<th') && !str_starts_with($elem, '<td')) {
                    return false;
                }
            }
        }
        return true;
    }

    private function checkListTags(): bool  {
        // Check if <li> tags are properly nested within <ul> or <ol>
        for ($i = 0; $i < count($this->htmlElements); $i++) {
            if (str_starts_with($this->htmlElements[$i], '<ul')) {
                if (!$this->checkParentList($i+1, 'ul')) {
                    print("Error: <ul> tags must be followed by a <li> block.\n");
                    return false;
                }
                else
                    continue;
            } elseif (str_starts_with($this->htmlElements[$i], '<ol')) {
                if (!$this->checkParentList($i+1, 'ol')) {
                    print("Error: <ol> tags must be followed by a <li> block.\n");
                    return false;
                }
                else
                    continue;
            }
        }
        return true;
    }

    private function checkParentList(int $i, string $tag): bool {
        // Check if <li> tags are properly nested within <ul>
        $Elem = [];
        $size = count($this->htmlElements);
        
        if ($i < 0 || $i >= $size)
            return false;

        while($i < $size) {
            if (str_starts_with($this->htmlElements[$i], '</' . $tag . '>'))
                break;
            $Elem[] = $this->htmlElements[$i];
            $i++;
        }
        if (empty($Elem))
            return false;

        foreach($Elem as $elem) {
            if (!str_starts_with($elem, '<li')) {
                return false;
            }
        }
        return true;
    }

    private function checkHeadBlock(): bool {
        // Check if <meta> and <title> tags are present within <head>
        $headIndex = array_search('<head>', $this->htmlElements);

        if ($headIndex === false) {
            print("Error: <head> tag is missing.\n");
            return false;
        }

        $headElements = [];
        $flagMeta = 0;
        $flagTitle = 0;
        for ($i = $headIndex; $i < count($this->htmlElements); $i++) {
            if ($this->htmlElements[$i] === '</head>') {
                break;
            }
            if (str_starts_with($this->htmlElements[$i], '<meta charset')) {
                $flagMeta += 1;
            } elseif (str_starts_with($this->htmlElements[$i], '<title')) {
                $flagTitle += 1;
            }
            $headElements[] = $this->htmlElements[$i];
        }

        if ($flagMeta !== 1 || $flagTitle !== 1) {
            print("Error: <head> block cannot contain more than one <meta> / <title> tags.\n");
            return false;
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

    private function checkPBlock(): bool {
        // Check if <p> tags contain no other tags

        // Extract content of <p> tags... 
        foreach ($this->htmlElements as $element) {
            if (preg_match('/<p\b[^>]*>(.*?)<\/p>/s', $element, $matches)) {
                // ... and check for potential nested tags
                if (preg_match('/<[^>]+>/', $matches[1])) {
                    print("Error: <p> tags cannot nest other HTML tags.\n");
                    return false;
                }
            }
        }
        return true;    
    }

}

?>