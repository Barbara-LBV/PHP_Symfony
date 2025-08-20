<?php 

class Elem {

    public string $content;
    public string $element;
    public array $htmlElements = []; // array of strings
    public string $result = '';
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
            case 5 or 6: // div and p tag
                if ($this->content !== '')
                    $this->htmlElements[$key] = "<{$this->element}>{$this->content}</{$this->element}>";
                else
                    $this->htmlElements[$key] = "<{$this->element}>";
                break;
            default: // other tags
                if (in_array($this->element, $this->autoClosing)) {
                    $this->htmlElements[$key] = "<{$this->element} {$this->content}/>";
                } elseif(in_array($this->element, $this->tags)) {
                    $this->htmlElements[7] = "<{$this->element}>{$this->content}</{$this->element}>";
                }
                break;
            }
            ksort($this->htmlElements);
        }

    public function pushElement(Elem $elem): void {
        if (!$elem instanceof Elem || !$elem) {
            print("Error: Invalid parameter: must be an Elem object.\n");
            return;
        }
        if ($elem->htmlElements) {
            foreach ($elem->htmlElements as $key => $value) {
                if (!array_key_exists($key, $this->htmlElements))
                    $this->htmlElements[$key] = $value;
                elseif ($this->htmlElements[$key] && !in_array($value, $this->priorityTags))
                    $this->htmlElements[] = $value;
            }
        } else {
            print("Error: Element has no HTML content.\n");
            return;
        }
        ksort($this->htmlElements);
    }

    public function getHTML(): string {
        if (empty($this->htmlElements)) {
            print("Error: No HTML elements to render.\n");
            return '';
        }
        $this->htmlElements = array_values($this->htmlElements);
        $size = count($this->htmlElements) - 1;
        // adding closing tags for priority tags
        for ($i = $size ; $i > -1 ; $i--) {
            $key = array_key_exists($i, $this->htmlElements);
            // print_r("ele at index i : $i is : " . $this->htmlElements[$i] . "\n");
            if ($key === true){
                $value = "</{$this->tags[$i]}>";
                if($i == 1)  {
                    if (in_array('<body>', $this->htmlElements)) {
                        $k = array_search('<body>', $this->htmlElements);
                        array_splice($this->htmlElements, $k, 0, $value);
                    }
                }
                elseif (($value == ('</div>' ||'</p>' ))
                    && strstr($this->htmlElements[$i], $value)) 
                    continue ;
                elseif ($i != 2 && $i != 3)  {
                    if (!in_array($value, $this->htmlElements))
                        $this->htmlElements[] = $value;
                }
            }
        }     
        $this->result = $this->formatingHTML($this->htmlElements);
        return $this->result;
    }

    function formatingHTML(array $a): string {
        $indent = 0;

        foreach ($a as $tag) {
            if (preg_match('/^<\//', $tag)) {
                $indent -= 1; //closing tag, decreasing before
                if ($indent < 0)
                    $indent = 0; // avoid negative indentation
            }
            $this->result .= str_repeat("  ", $indent) . $tag . "\n";
            if (preg_match('/^<([a-zA-Z][a-zA-Z0-9]*)[^>]*>.*<\/\1>$/', $tag))
                continue ; // closing tag, decreasing before
            elseif (preg_match('/^<(?!\/)(?!.*\/>)/', $tag))
                $indent++; // opening tag, increasing after
            }
        return $this->result;
    }
}

?>
