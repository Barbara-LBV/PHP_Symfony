<?php 

class Elem {
    private string $content;
    private string $element;
    private string $result = '';
    private array $htmlElements = []; // array of strings
    public array $autoClosing = ['meta', 'br', 'hr', 'img'];
    public array $openTags = ['html', 'head', 'body', 'div', 'p'];
    public array $tags = ['html', 'head', 'meta', 'title', 'body', 'div','p', 'img',
    'hr', 'br', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'span'];
    
    public function __construct(string $element, string $content = '') {
        if (!in_array($element, $this->tags)) {
            print("Error: Input is not an supported HTML tag.\n");
            return ;
        }

        $this->element = $element;
        $this->content = $content;

        $key = -1;

        if (array_search($this->element, $this->autoClosing) !== false) {
            $key = 0;
        } elseif (array_search($this->element, $this->openTags) !== false) {
            $key = 1;
        }

        switch ($key){
            case 0: // auto Closing Tags
                $this->htmlElements[$key] = "<{$this->element} {$this->content}/>";
                break;
            case 1: // tags to close after
                $this->htmlElements[$key] = "<{$this->element}>{$this->content}";
                break;
            default: // closing tags after data
                $this->htmlElements[] = "<{$this->element}>{$this->content}</{$this->element}>";
                break;
        }
    }

    public function __destruct() {}

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
        if (!$elem instanceof Elem || !$elem) {
            print("Error: Invalid parameter: must be an Elem object.\n");
            return ;
        }

        if ($elem->htmlElements) {
            foreach ($elem->htmlElements as $key => $value) {
                if (in_array($value, $this->htmlElements))
                    continue ;
                elseif (!in_array($value, $this->openTags))
                    $this->htmlElements[] = $value;
            }
        } else {
            print("Error: Element has no HTML content.\n");
            return ;
        }
        // ksort($this->htmlElements);
    }

    public function getHTML(): string { 
        if (empty($this->htmlElements)) {
            print("Error: No HTML elements to render.\n");
            return '';
        }

        // Reset result and openTags for each call
        $result = '';
        $openTags = [];

        $this->insertClosingHeadTag();
        $this->insertClosingParentTags();

        $maxIndex = count($this->htmlElements);
        for ($i = -1; $i < $maxIndex; $i++) {

            // check if the index exists in htmlElements
            if (array_key_exists($i, $this->htmlElements)) {
                $element = $this->htmlElements[$i];
                // Gestion des balises ouvrantes (sauf pour les balises fermantes et auto-fermantes)
                if (!preg_match('/^<\//', $element)) { // Si ce n'est pas une balise fermante
                    if (preg_match('/<([a-zA-Z0-9]+)(?:\s[^>]*)?>$/', $element, $matches)) {
                        $tagName = $matches[1];
                        if (!in_array($tagName, $this->autoClosing))
                            array_push($openTags, $tagName);
                    }
                } else {
                    // Si c'est une balise fermante, la retirer de openTags
                    if (preg_match('/<\/([a-zA-Z0-9]+)>$/', $element, $matches)) {
                        $tagName = $matches[1];
                        $key = array_search($tagName, $openTags);
                        if ($key !== false)
                            array_splice($openTags, $key, 1);
                    }
                }
                print("openTags stack: " . implode(', ', $openTags) . "\n"); // Debugging line
                $result .= $this->indentElement($element, count($openTags) - 1, $openTags);
                $this->setResult($result);
            }
        }
        
        // closing remaining open tags
        while (!empty($openTags)) {
            $tagToClose = array_pop($openTags);
            $result .= str_repeat(' ', count($openTags)) . "</{$tagToClose}>\n";
        }
        $this->setResult($result);
        print("Final HTML result:\n" . $result . "\n"); // Debugging line
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
        $flag = 0;
        $previousTag = '';
        $parentTags = ['div', 'table', 'tr'];

        foreach ($this->htmlElements as $i => $element) {
            $trim_element = trim(str_replace(['<', '>'], ' ', $element));
            if (in_array($trim_element, $parentTags) && $flag === 0) {
                $previousTag = "</{$trim_element}>"; 
                $flag = 1;
            } elseif (in_array($trim_element, $parentTags) && $flag === 1) {
                array_splice($this->htmlElements, $i, 0, $previousTag);
                $flag = 0;
            }
        }
    }
    
    private function indentElement(string $element, int $level, array $openTags): string {
        // print("Indenting element: " . $element . " at level: " . $level . "\n"); // Debugging line
        $level = max(0, $level); // Ensure level is not negative
        if(array_search('body', $openTags) !== false && $element !== '<body>') {
            $level += 1;
            // print("level increased for body tag. New level: " . $level . "\n"); // Debugging line
        }
            
        $indent = str_repeat(' ', $level);
        return $indent . $element . "\n";
    }
}

?> 