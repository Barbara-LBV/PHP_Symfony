<?php 

include './MyException.php';

class Elem {

    private string  $content;
    private string  $element;
    private string  $result;
    private array   $attributes;
    private array   $htmlElements;

    private array   $autoClosing = ['meta', 'br', 'hr', 'img'];
    private array   $priorityTags = ['html', 'head', 'meta', 'title', 'body'];
    private array   $parentTags = ['div', 'table', 'tr', 'ol', 'ul'];
    private array   $closingTags = ['title','h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'li', 'span', 'th', 'td', 'p'];
    // private array   $tableTags = ['tr', 'th', 'td'];
    private array   $tags = ['html', 'head', 'meta', 'title', 'body', 'div', 'p', 'img','hr', 'br', 
    'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'span', 'table', 'tr', 'th', 'td', 'ul', 'ol', 'li'];
    
    public function __construct(string $element, string $content = '', array $attributes = []){

        if (!in_array($element, $this->tags))
            throw new MyException("Invalid HTML tag: {$element}");

        $this->element = $element;
        $this->content = $content;
        $this->attributes = $attributes;
		$this->htmlElements = [];
		$this->setResult('');

		$key = -1;

        if (array_search($this->element, $this->autoClosing) !== false) {
            $key = 0;
        } elseif (array_search($this->element, $this->closingTags) !== false) {
            $key = 1;
        }

		$value = $this->initiateAttributes($this->attributes);
		switch ($key) {
			case 0: // auto Closing Tags
				$this->htmlElements[$key] = "{$value} $this->content}/>";
				break;
			case 1: // closing tags after data
				$this->htmlElements[$key] = "{$value} {$this->content}";
				break;
			default: // other tags
				$this->htmlElements[] = "{$value} >{$this->content}</{$this->element}>";
				break;
        }
  
            // switch ($key) {
            //     case 0: // html tag
            //         $this->htmlElements[$key] = $value . ">";
            //         break;
            //     case 1: // head tag
            //         $this->htmlElements[$key] = $value . ">";
            //         break;
            //     case 2: // meta tag
            //         $this->htmlElements[$key] = "$value {$this->content}/>";
            //         break;
            //     case 3: // title tag
            //         $this->htmlElements[$key] = "{$value}>{$this->content}</{$this->element}>";
            //         break;
            //     case 4: // body tag
            //         $this->htmlElements[$key] = "{$value}>";
            //         break;
            //     case 5: // div tag
            //         if ($this->content !== '')
            //             $this->htmlElements[$key] = "{$value}>{$this->content}</{$this->element}>";
            //         else
            //             $this->htmlElements[$key] = "{$value}>";
            //         break;
            //     case 6: // p tag
            //         if ($this->content !== '')
            //             $this->htmlElements[$key] = "{$value}{$this->content}</{$this->element}>";
            //         else
            //             $this->htmlElements[$key] = "{$value}>";
            //         break;
            //     default: // other tags
                    // $this->addOtherTags($value, 7);
            //         break;
            //     }
            // ksort($this->htmlElements);
        // } catch (MyException $e) {
        //     echo $e->getMessage() . "\n";
        // } 
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

	public function setResult($result): void {
        $this->result = $result;
    }

    public function pushElement(Elem $elem): void {
        $toReplace = ['<', '>', '/  '];

        if ($elem->htmlElements) {
            foreach ($elem->htmlElements as $key => $value) {
				
                $trim_value = trim(str_replace($toReplace, ' ', $value));  
                if (in_array($value, $this->htmlElements) && in_array($trim_value, $this->priorityTags))
                    continue ;
                // elseif (!array_key_exists($key, $this->htmlElements)) {
				// 	// on push la valeur a l'index 
                //     $this->htmlElements[$key] = $value; 
                // } 
                elseif ($value == ('<div>' || '<p>') && in_array($value, $this->htmlElements)) {
                    $this->htmlElements[] = $value;
                } 
                elseif (array_key_exists($key, $this->htmlElements)
                    && in_array($trim_value, $this->priorityTags)
                    && !in_array($value, $this->htmlElements)) {
                        array_splice($this->htmlElements, $key, 0, $value);
                } 
                elseif (!in_array($trim_value, $this->priorityTags))
                    $this->htmlElements[] = $value;
            }
        } 
        else {
            print("Error: Pushed Element has no HTML content.\n");
            return ;
        }
        // $this->orderTags();
    }

    public function getHTML(): string {      
        if (empty($this->htmlElements)) {
            print("Error: No HTML elements to render.\n");
            return ('');
        }

        // Reset result and openTags at each call
        $result = '';
        $openTags = [];

        $this->insertClosingHeadTag();
        $this->insertClosingParentTags();

        $maxIndex = count($this->htmlElements);
        for ($i = -1; $i < $maxIndex; $i++) {
            // check if the index exists in htmlElements
            if (array_key_exists($i, $this->htmlElements)) {

                $element = $this->htmlElements[$i];
                // handling open tags (except closing/auto-closing ones)
                if (!preg_match('/^<\//', $element)) { // -> if it's not a closing tag
                    if (preg_match('/<([a-zA-Z0-9]+)(?:\s[^>]*)?>$/', $element, $matches)) {
                        $tagName = $matches[1];
                        if (!in_array($tagName, $this->autoClosing))
                            array_push($openTags, $tagName);
                    }
                } else {
                    // if it's a closing tag, delete it from openTags tab
                    if (preg_match('/<\/([a-zA-Z0-9]+)>$/', $element, $matches)) {
                        $tagName = $matches[1];
                        $key = array_search($tagName, $openTags);
                        if ($key !== false)
                            array_splice($openTags, $key, 1);
                    }
                } 
                $result .= $this->indentElement($element, count($openTags) - 1);
            }
        }

        // closing remaining open tags
        while (!empty($openTags)) {
            $tagToClose = array_pop($openTags);
            $result .= str_repeat('  ', count($openTags)) . "</{$tagToClose}>\n";
        }

		$this->setResult($result);
		return ($result);
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

    // private function addOtherTags($value, $key){
    //     if (in_array($this->element, $this->autoClosing)){
    //         $this->htmlElements[] = "{$value} {$this->content}/>";
    //     }   
    //     elseif(in_array($this->element, $this->closingTags)){
    //         $this->htmlElements[$key] = "{$value}>{$this->content}</{$this->element}>";
    //     }
    //     elseif(in_array($this->element, $this->parentTags)){
    //         $this->htmlElements[] = "{$value}>{$this->content}";
    //     }
    // }
    
    private function initiateAttributes(array $a) : string {
        $result = "<{$this->element}";
        // $keys = array_keys($a);

        foreach ($a as $key => $value)
            $result .= " {$key}=\"{$value}\"";
        return $result;
    }
    
    private function indentElement(string $element, int $level): string {
        $level = max(0, $level);
        $indent = str_repeat('  ', $level);
        return $indent . $element . "\n";
    }

    // private function tagName(string $tag): ?string {
    //     return preg_match('/^<\s*([a-z][a-z0-9]*)\b/i', $tag, $matches) ? strtolower($matches[1]) : null;
    // }

    
    // private function orderTags(): void {
    //     $result  = [];
    //     $usedIdx = [];

    //     // 1) placer d’abord les balises prioritaires, dans l’ordre voulu
    //     foreach ($this->priorityTags as $p) {
    //         foreach ($this->htmlElements as $i => $tag) {
    //             if (isset($usedIdx[$i])) 
    //                 continue;
    //             if ($this->tagName($tag) === $p) {
    //                 $result[] = $tag;
    //                 $usedIdx[$i] = true;   // marquer comme utilisé
    //             }
    //         }
    //     }

    //     // 2) puis toutes les autres, en gardant l’ordre d’origine, sans doublons
    //     foreach ($this->htmlElements as $i => $tag) {
    //         if (!isset($usedIdx[$i]) && !in_array($tag, $result, true)) {
    //             $result[] = $tag;
    //         }
    //     }

    //     // 3) tableau séquentiel propre
    //     $this->htmlElements = array_values($result);
    // }
}

?>
