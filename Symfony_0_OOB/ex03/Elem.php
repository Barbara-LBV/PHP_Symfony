<?php 

class Elem {

    public string $content;
    public string $element;
    public array $htmlElements = []; // array of strings
    public string $result = '';
    public array $tags = [
        'html', 'head', 'meta', 'title', 'body', 'p', 'div', 'img', 'hr', 'br', 
        'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'span'];
    public array $autoClosing = ['meta', 'br', 'hr', 'img'];
    public array $priorityTags = ['html', 'head', 'meta', 'title', 'body', 'div', 'p'];
    public array $secondaryTags = ['div', 'p'];

    public function __construct(string $element, string $content = '') {
        if (!in_array($element, $this->tags)) {
            print("Error: Input is not an supported HTML tag.\n");
            return false;
        }
        $this->element = $element;
        $this->content = $content;

        $key = array_search($this->element, $this->priorityTags);

        if (in_array($this->element, $this->priorityTags) 
            && in_array($this->element, $this->autoClosing)) {
            $this->htmlElements[$key] = "<{$this->element} {$this->content}/>";
        }

        elseif($this->element == 'title')
            $this->htmlElements[$key] = "<{$this->element}> {$this->content} </{$this->element}>";
        
        elseif (in_array($this->element, $this->priorityTags)){
            if (!$this->content !== '')
                $this->htmlElements[$key] = "<{$this->element}>{$this->content}";
            else 
                $this->htmlElements[$key] = "<{$this->element}>";
        }

        elseif (in_array($this->element, $this->autoClosing))
            $this->htmlElements[6] = "<{$this->element} {$this->content} />";
        
        else
            $this->htmlElements[6] = "<{$this->element}>{$this->content} </{$this->element}>";
        
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
                elseif ($this->htmlElements[$key] && $key > 4)
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

        // adding closing tags for priority tags
        for ($i = 6 ; $i > -1 ; $i--) {
            $key = array_key_exists($i, $this->htmlElements);
            $value = "</{$this->priorityTags[$i]}>";
            if ($key === true && $i == 1)  {
                if (in_array('<body>', $this->htmlElements)){
                    $k = array_search('<body>', $this->htmlElements);
                    array_splice($this->htmlElements, $k, 0, $value);
                }
            }
            if ($key === true && $i != 2 && $i != 3)  {
                if (!in_array($value, $this->htmlElements))
                    $this->htmlElements[] = $value;
            }
        }
        $this->result = $this->formatingHTML($this->htmlElements);
        return $this->result;
    }

    function formatingHTML(array $a): string {
        $indent = 0;

        foreach ($a as $tag) {
            if (preg_match('/^<\//', $tag))
                $indent -= 2; //closing tag, decreasing before
            if ($indent < 0)
                $indent = 0; // avoid negative indentation
            $this->result .= str_repeat("  ", $indent) . $tag . "\n";
            if (preg_match('/^<(?!\/)(?!.*\/>)/', $tag))
                $indent++; // opening tag, increasing after
        }
        return $this->result;
    }
}

?>