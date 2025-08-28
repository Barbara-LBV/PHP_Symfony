<?php 

class Elem {
    private string  $content;
    private string  $element;
    private array   $htmlElements = []; // array of strings
    private array   $autoClosing = ['meta', 'br', 'hr', 'img'];
    private array   $priorityTags = ['html', 'head', 'meta', 'title', 'body'];
    private array   $tags = ['html', 'head', 'meta', 'title', 'body', 'div','p', 'img',
        'hr', 'br', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'span'];
    
    public function __construct(string $element, string $content = '') {
        if (!in_array($element, $this->tags)) {
            print("Error: Input is not an supported HTML tag.\n");
            return false;
        }
        $this->element = $element;
        $this->content = $content;

        $key = array_search($this->element, $this->tags);

        switch ($key){
            case 0: // html tag
                $this->htmlElements[$key] = "<{$this->element}>";
                break;
            case 1: // head tag
                $this->htmlElements[$key] = "<{$this->element}>";
                break;
            case 2: // meta tag
                $this->htmlElements[$key] = "<{$this->element} {$this->content}/>";
                break;
            case 3: // title tag
                $this->htmlElements[$key] = "<{$this->element}>{$this->content}</{$this->element}>";
                break;
            case 4: // body tag
                $this->htmlElements[$key] = "<{$this->element}>";
                break;
            case 5: // div tag
                if ($this->content !== '')
                    $this->htmlElements[$key] = "<{$this->element}>{$this->content}</{$this->element}>";
                else
                    $this->htmlElements[$key] = "<{$this->element}>";
                break;
            case 6: // p tag
                if ($this->content !== '')
                    $this->htmlElements[$key] = "<{$this->element}>{$this->content}</{$this->element}>";
                else
                    $this->htmlElements[$key] = "<{$this->element}>";
                break;
            default: // other tags
                if (in_array($this->element, $this->autoClosing)) 
                    $this->htmlElements[] = "<{$this->element} {$this->content}/>";
                elseif(in_array($this->element, $this->tags)) 
                    $this->htmlElements[] = "<{$this->element}>{$this->content}</{$this->element}>";
                break;
            }
            ksort($this->htmlElements);
        }

    public function pushElement(Elem $elem): void {
        if (!$elem instanceof Elem || !$elem) {
            print("Error: Invalid parameter: must be an Elem object.\n");
            return ;
        }
        if ($elem->htmlElements) {
            foreach ($elem->htmlElements as $key => $value) {
                if (!array_key_exists($key, $this->htmlElements))
                    $this->htmlElements[$key] = $value;
                elseif ((strstr($value, '<div>') || strstr($value, '<p>')) 
                    && in_array($value, $this->htmlElements)) {
                    $this->htmlElements[] = $value;
                } elseif (in_array($value, $this->htmlElements))
                    continue ;
                elseif (!in_array($value, $this->priorityTags))
                    $this->htmlElements[] = $value;
            }
        } else {
            print("Error: Element has no HTML content.\n");
            return ;
        }
        ksort($this->htmlElements);
    }

            public function getHTML(): string {      
        if (empty($this->htmlElements)) {
            print("Error: No HTML elements to render.\n");
            return '';
        }

        // Reset result and openTags for each call
        $this->result = '';
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
                $this->result .= $this->indentElement($element, count($openTags) - 1);
            }
        }
        // closing remaining open tags
        while (!empty($openTags)) {
            $tagToClose = array_pop($openTags);
            $this->result .= str_repeat('  ', count($openTags)) . "</{$tagToClose}>\n";
        }
        return $this->result;
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
    
    private function indentElement(string $element, int $level): string {
        $indent = str_repeat('  ', $level);
        return $indent . $element . "\n";
    }
}

?>
