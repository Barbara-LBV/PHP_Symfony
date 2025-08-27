<?php 

// declare(strict_types=1);
include './MyException.php';

class Elem {

    private string $content;
    private string $element;
    private string $result = '';
    private array $attributes;
    private array $htmlElements = [];

    private array $autoClosing = ['meta', 'br', 'hr', 'img'];
    private array $priorityTags = ['html', 'head', 'meta', 'title', 'body'];
    private array $parentTags = ['div', 'table', 'tr', 'ol', 'ul'];
    private array $closingTags = ['h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'li', 'span', 'th', 'td', 'p'];
    private array $tableTags = ['tr', 'th', 'td'];
    private array $tags = ['html', 'head', 'meta', 'title', 'body', 'div','p', 'img','hr', 'br', 
    'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'span', 'table', 'tr', 'th', 'td', 'ul', 'ol', 'li'];
    
    public function __construct(string $element, string $content = '', array $attributes = []){
        try {
            if (!in_array($element, $this->tags))
                throw new MyException("Invalid HTML tag: {$element}");

            $this->element = $element;
            $this->content = $content;
            $this->attributes = $attributes;

            $key = array_search($this->element, $this->tags);
            $value = $this->initiateAttributes($this->attributes);
  
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

    public function addOtherTags($value, $key){
        if (in_array($this->element, $this->autoClosing)){
            $this->htmlElements[] = "{$value} {$this->content}/>";
        }   
        elseif(in_array($this->element, $this->closingTags)){
            $this->htmlElements[$key] = "{$value}>{$this->content}</{$this->element}>";
        }
        elseif(in_array($this->element, $this->parentTags)){
            $this->htmlElements[] = "{$value}>{$this->content}";
        }
    }
    
    public function initiateAttributes(array $a) : string {
        $result = "<{$this->element}";
        $keys = array_keys($a);

        foreach ($a as $key => $value)
            $result .= " {$key}={$value}";
        return $result;
    }

    public function pushElement(Elem $elem): void {
        $toReplace = ['<', '>', '/  '];
        if ($elem->htmlElements) {
            foreach ($elem->htmlElements as $key => $value) {
                $trim_value = trim(str_replace($toReplace, ' ', $value));
                // $trim_value = trim($trim_value);
                if (in_array($value, $this->htmlElements))
                    continue ;
                elseif (!array_key_exists($key, $this->htmlElements)){
                    $this->htmlElements[$key] = $value; 
                } elseif ($value == ('<div>' || '<p>') && in_array($value, $this->htmlElements)) {
                    $this->htmlElements[] = $value;
                } elseif(array_key_exists($key, $this->htmlElements)
                    && in_array($trim_value, $this->priorityTags)) {
                        array_splice($this->htmlElements, $key, 0, $value);
                 } elseif (in_array($trim_value, $this->priorityTags)
                    && !in_array($value, $this->htmlElements)) {
                    $k = array_search($value, $this->tags);
                    if (array_search($value, $this->htmlElements) === false)
                        $this->htmlElements[$k] = $value;
                    else {
                        array_splice($this->htmlElements, $k, 0, $value);
                    }
                } elseif (!in_array($trim_value, $this->priorityTags)){
                    $this->htmlElements[] = $value;
                }
            }
        } else {
            print("Error: Pushed Element has no HTML content.\n");
            return ;
        }
        // $this->orderTags();
        ksort($this->htmlElements);
    }

    public function orderTags(): void {
        $tmp_array = [];
        $k = 0;
        foreach ($this->tags as $key => $value) {
            if (!empty($this->htmlElements[$key]) && (strstr($this->htmlElements[$key], $value) != false)){
               print("0- Reordering tag: {$value}\n");
                $tmp_array[$key] = $this->htmlElements[$key];
            }
            else {
                $matches = array_filter($this->htmlElements, function($item) use ($value) {
                    return strpos($item, $value) !== false;
                });

                if (!empty($matches)) {
                    $firstKey = array_key_first($matches);
                    $tmp_array[$key] = $this->htmlElements[$firstKey];
                }
            }
        }
        ksort($tmp_array);
        $this->htmlElements = $tmp_array;
    }

    public function getHTML(): string {      
        if (empty($this->htmlElements)) {
            print("Error: No HTML elements to render.\n");
            return '';
        }

        // Reset result and openTags for each call
        $this->result = '';
        $openTags = [];

        // Insert </head> tag before <body> if necessary
        $headIndex = array_search('<head>', $this->htmlElements);
        $bodyIndex = array_search('<body>', $this->htmlElements);

        // Reindex the array
        $this->htmlElements = array_values($this->htmlElements); 
        // print_r($this->htmlElements);
        if ($headIndex !== false && $bodyIndex !== false && $headIndex < $bodyIndex) {
            // check if </head> already exists
            $closeHeadIndex = array_search('</head>', $this->htmlElements);
            if ($closeHeadIndex === false || $closeHeadIndex > $bodyIndex) {
                // Insert </head> just before <body>
                array_splice($this->htmlElements, $bodyIndex, 0, ['</head>']);
            }
        }
        
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
    
    public function indentElement(string $element, int $level): string {
        $level = max(0, $level);
        $indent = str_repeat('  ', $level);
        return $indent . $element . "\n";
    }
}

?>
