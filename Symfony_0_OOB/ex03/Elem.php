<?php 

class Elem {

    public string $content;
    public string $element;
    public array $htmlElements = []; // array of strings
    public string $result = '';
    private array $priorityTags = ['html', 'head', 'meta', 'title', 'body'    ];
    public array $tags = [
        'html', 'head', 'meta', 'title', 'body', 'img', 'hr', 'br', 
        'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'p', 'span', 'div'
    ];

    public function __construct(string $element, string $content = '') {
        if (!in_array($element, $this->tags)) {
            print("Error: Input is not an supported HTML tag.\n");
            return false;
        }
        $this->element = $element;
        $this->content = $content;

        $autoClosing = ['meta', 'br', 'hr', 'img'];

        if (in_array($this->element, $this->priorityTags)) {
            $key = array_search($this->element, $this->priorityTags);
            $this->htmlElements[$key] = "<{$this->element}>{$this->content}";
        } elseif (in_array($this->element, $autoClosing)) {
            $this->htmlElements[] = "<{$this->element} {$this->content}/>";
        } else
            $this->htmlElements[5] = "<{$this->element}>{$this->content}</{$this->element}>";
        ksort($this->htmlElements);
        }

    public function pushElement(Elem $elem): void {
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
        for ($i = 4 ; $i >= 0 ; $i--) {
            $key = array_key_exists($i, $this->htmlElements);
            if ($key === true)
                $this->htmlElements[] = "</{$this->priorityTags[$i]}>";
        }
        
        // formatting and indenting the HTML output
        $indent = 0;
        $result = "";

        foreach ($this->htmlElements as $tag) {
            if (preg_match('/^<\//', $tag)) {
                $indent -= 2; //closing tag, decreasing before
            }
            if ($indent < 0)
                $indent = 0; // avoid negative indentation
            $result .= str_repeat("  ", $indent) . $tag . "\n";
            if (preg_match('/^<(?!\/)(?!.*\/>)/', $tag)) {
                $indent++; // opening tag, increasing after
            }
        }
        $this->result = $result;
        return $result;
    }
}

?>