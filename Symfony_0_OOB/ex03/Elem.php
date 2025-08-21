<?php 

class Elem {

    public string $content;
    public string $element;
    public array $htmlElements = []; // array of strings
    public array $autoClosing = ['meta', 'br', 'hr', 'img'];
    public array $priorityTags = ['html', 'head', 'meta', 'title', 'body'];
    public array $tags = ['html', 'head', 'meta', 'title', 'body', 'div','p', 'img',
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
        // reset indexes of htmlElements to ensure no duplicates
        if (empty($this->htmlElements)) {
            print("Error: No HTML elements to render.\n");
            return '';
        }
        $this->htmlElements = array_values($this->htmlElements);
        $maxIndex = count($this->htmlElements);
        
        for ($i = 0; $i < $maxIndex ; $i++) {
            // check if the index exists in htmlElements
            if (array_key_exists($i, $this->htmlElements)) {
                $element = $this->htmlElements[$i];
                // if opening tag, add it to openTags
                if (preg_match('/<([a-zA-Z0-9]+)(?:\s[^>]*)?>$/', $element, $matches)) {
                    $tagName = $matches[1];
                    if (!in_array($tagName, $this->autoClosing)) {
                        array_push($openTags, $tagName);
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
        $indent = str_repeat('  ', $level);
        return $indent . $element . "\n";
    }

}

?>
