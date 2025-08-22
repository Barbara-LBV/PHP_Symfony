<?php 

include './MyException.php';

class Elem {

    public string $content;
    public string $element;
    public array $attributes;
    public array $htmlElements = []; // array of strings

    public array $autoClosing = ['meta', 'br', 'hr', 'img'];
    public array $priorityTags = ['html', 'head', 'meta', 'title', 'body'];
    public array $parentTags = ['div','p', 'table', 'tr', 'ol', 'ul'];
    public array $closingTags = ['h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'li', 'span', 'th', 'td'];
    public array $tableTags = ['tr', 'th', 'td'];
    public array $tags = ['html', 'head', 'meta', 'title', 'body', 'div','p', 'img','hr', 'br', 
    'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'span', 'table', 'tr', 'th', 'td', 'ul', 'ol', 'li'];
    
    public function __construct(string $element, string $content = '', array $attributes = []) {
        try {
            if (!is_string($element) || !is_string($content) || !is_array($attributes)) 
                throw new MyException();
            
            if (!in_array($element, $this->tags)) 
                throw new MyException();
            
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
                        $this->htmlElements[$key] = "{$value}>{$this->content}{$this->content}</{$this->element}>";
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
                    $this->pushOtherTags($value, $key);
                    break;
                }
            ksort($this->htmlElements);
        } catch (Exception $e) {
            echo $e->getMessage() . "\n";
        }
    }

    private function pushOtherTags($value, $key){
        if ($this->element == 'li')
            $this->isElementList($value);
        elseif (in_array($this->element, $this->tableTags)){
            $this->isTableElement($value);
        }
        elseif (in_array($this->element, $this->autoClosing)){
            if (!in_array($this->element, $this->htmlElements))
                $this->htmlElements[$key] = "{$value}>{$this->content}";
            else
                $this->htmlElements[] = "{$value} {$this->content}/>";
        }   
        elseif(in_array($this->element, $this->closingTags)){
            if (!in_array($this->element, $this->htmlElements))
                $this->htmlElements[$key] = "{$value}>{$this->content}</{$this->element}>";
            else
                $this->htmlElements[] = "{$value}>{$this->content}</{$this->element}>";
        }
        elseif(in_array($this->element, $this->parentTags)){
            if (!in_array($this->element, $this->htmlElements))
                $this->htmlElements[$key] = "{$value}>{$this->content}";
            else
                $this->htmlElements[] = "{$value}>{$this->content}";
        }
    }
    
    private function initiateAttributes(array $a) : string {
        $result = "<{$this->element}";
        $keys = array_keys($a);

        foreach ($a as $key => $value){
            $result .= " {$key}={$value}";
        }
        return $result;
    }

    private function isElementList(string $value){
        try {
            if ($this->element == 'li'){
                if (!in_array('<ol>', $this->htmlElements) && 
                !in_array('<ul>', $this->htmlElements) ){
                    throw new MyException();
                }
            }
            $li_keys = array_keys($this->htmlElements, '<li');
            $ul_keys = array_keys($this->htmlElements, '<ul');
            $ol_keys = array_keys($this->htmlElements, '<ol');

            if (!empty($li_keys)){
                $key = array_key_last($li_keys);
                array_splice($this->htmlElements, $key + 1, 0, "{$value}>{$this->content}</{$this->element}>");
                return ;
            }
            elseif (!empty($ol_keys) || !empty($ul_keys)) {
                $new_a = array_merge($ol_keys, $ul_keys);
                $key = array_key_last($new_a);
                array_splice($this->htmlElements, $key + 1, 0, "{$value}>{$this->content}</{$this->element}>");
                return ;
            }
                
        } catch (Exception $e) {
            echo $e->getMessage() . "\n";
        }
    }

    private function isTableElement(string $value){
        try {
            if ($this->element == ('th' || 'tr' || 'td')){
                if (!in_array('table', $this->htmlElements)){
                    throw new MyException("Must be preceded by 'table' tag");
                }
            }
            $tr_keys = array_keys($this->htmlElements, '<tr');
            $th_keys = array_keys($this->htmlElements, '<th');
            $td_keys = array_keys($this->htmlElements, '<td');
            $new_a = array_merge($tr_keys, $th_keys, $td_keys);

            if (!empty($tr_keys)){
                $key = array_key_last($tr_keys);
                if ($this->element == ('th' || 'td'))
                    array_splice($this->htmlElements, $key + 1, 0, "{$value}>{$this->content}</{$this->element}>");
                elseif ($this->element == 'tr')
                    array_splice($this->htmlElements, $key + 1, 0, "{$value}>");
            }    
        } catch (Exception $e) {
            echo $e->getMessage() . "\n";
        }
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
                elseif ($value == ('<div>' || '<p>') && in_array($value, $this->htmlElements)) {
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
        $this->result = '';
        $openTags = [];
        
        if (empty($this->htmlElements)) {
            print("Error: No HTML elements to render.\n");
            return '';
        }
        
        // Insert </head> tag before <body> if necessary
        $headIndex = array_search('<head>', $this->htmlElements);
        $bodyIndex = array_search('<body>', $this->htmlElements);
        
        if ($headIndex !== false && $bodyIndex !== false && $headIndex < $bodyIndex) {
            // check if </head> already exists
            $closeHeadIndex = array_search('</head>', $this->htmlElements);
            if ($closeHeadIndex === false || $closeHeadIndex > $bodyIndex) {
                // Insert </head> just before <body>
                array_splice($this->htmlElements, $bodyIndex, 0, ['</head>']);
            }
        }
        
        $maxIndex = count($this->htmlElements);
        
        for ($i = 0; $i < $maxIndex; $i++) {
            // check if the index exists in htmlElements
            if (array_key_exists($i, $this->htmlElements)) {
                $element = $this->htmlElements[$i];
                
                // Gestion des balises ouvrantes (sauf pour les balises fermantes et auto-fermantes)
                if (!preg_match('/^<\//', $element)) { // Si ce n'est pas une balise fermante
                    if (preg_match('/<([a-zA-Z0-9]+)(?:\s[^>]*)?>$/', $element, $matches)) {
                        $tagName = $matches[1];
                        if (!in_array($tagName, $this->autoClosing)) {
                            array_push($openTags, $tagName);
                        }
                    }
                } else {
                    // Si c'est une balise fermante, la retirer de openTags
                    if (preg_match('/<\/([a-zA-Z0-9]+)>$/', $element, $matches)) {
                        $tagName = $matches[1];
                        $key = array_search($tagName, $openTags);
                        if ($key !== false) {
                            array_splice($openTags, $key, 1);
                        }
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
    
    private function indentElement(string $element, int $level): string {
        $level = max(0, $level);
        $indent = str_repeat('  ', $level);
        return $indent . $element . "\n";
    }
}

?>
