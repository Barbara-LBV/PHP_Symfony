<?php

class Elem
{
    private string $content;
    private string $element;
    private string $result;
    private array $htmlElements = []; // array of strings

    public array $autoClosing = ['meta', 'br', 'hr', 'img'];
    private array $parentTags = ['html', 'head', 'body', 'div'];
    private array $closingTags = ['title', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'span', 'p'];
    public array $tags = [
        'html',
        'head',
        'meta',
        'title',
        'body',
        'div',
        'p',
        'img',
        'hr',
        'br',
        'h1',
        'h2',
        'h3',
        'h4',
        'h5',
        'h6',
        'span'
    ];

    public function __construct(string $element, string $content = '')
    {
        if (!in_array($element, $this->tags)) {
            print("Error: Input is not an supported HTML tag.\n");
            return;
        }

        $this->element = $element;
        $this->content = $content;
        $this->result = '';
        $this->addOtherTags();
    }

    public function __destruct() {}

    public function getElement(): string
    {
        return $this->element;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function getHtmlElements(): array
    {
        return $this->htmlElements;
    }

    public function getResult(): string
    {
        return $this->result;
    }

    public function setResult(string $result): void
    {
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

    public function getHTML(): string {
        if (empty($this->htmlElements)) {
            print("Error: No HTML elements to render.\n");
            return '';
        }

        // Reset result and openTags at each call
        $result = '';
        $openTags = [];

        $this->insertClosingHeadTag();
        $this->insertClosingParentTags();
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
            $result .= $this->indentElement($element, count($openTags)-1);
        }
        $this->setResult($result);
        return $result;
    }

    private function insertClosingHeadTag(): void
    {
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
        $parentTags = ['div'];

        foreach ($this->htmlElements as $element) {
            // get tag name from element using regex
            if (preg_match('/^<(?!\/)(?![^>]*\/>)[a-zA-Z][a-zA-Z0-9]*\b[^>]*>\s*\S/s', $element)) {
                // if there is content after the tag, add it to the new elements and continue
                $newElements[] = $element;
                continue;
            }
            if (preg_match('/^<([a-zA-Z0-9]+)/', $element, $matches)) {
                $tag = $matches[1];
                if (in_array($tag, $parentTags)) {
                    // if a parent tag is encountered, check if the previous one is the same and close it if necessary
                    while (!empty($stack) && $stack[count($stack) - 1] === $tag) {
                        $newElements[] = "</$tag>";
                        array_pop($stack);
                    }
                    $stack[] = $tag;
                }
            }
            $newElements[] = $element;
        }
        // close any remaining open parent tags
        while (!empty($stack)) {
            $tag = array_pop($stack);
            $newElements[] = "</$tag>";
        }
        $newElements = $this->insertClosingHtmlTag($newElements);
        $this->htmlElements = $newElements;
    }

    private function insertClosingHtmlTag(array $newElements): array
    {
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

    private function indentElement(string $element, int $level): string
    {
        // Ensure level is not negative
        $level = max(0, $level);
        if ($element === '<html>' || $element === '</html>')
            $level = 0;
        elseif (array_search($element, $this->parentTags) === false)
            $level += 1;
        $indent = str_repeat(' ', $level);
        return $indent . $element . "\n";
    }

    private function addOtherTags(): void
    {
        if (in_array($this->element, $this->autoClosing)) {
            $this->htmlElements[] = "<{$this->element} {$this->content}/>";
        } elseif (in_array($this->element, $this->closingTags))
            $this->htmlElements[] = "<{$this->element}>{$this->content}</{$this->element}>";
        elseif (in_array($this->element, $this->parentTags)) {
            if ($this->element === 'div' && !empty($this->content))
                $this->htmlElements[] = "<{$this->element}>{$this->content}</{$this->element}>";
            elseif ($this->element === 'div' && empty($this->content))
                $this->htmlElements[] = "<{$this->element}>";
            else
                $this->htmlElements[] = "<{$this->element}>{$this->content}";
        }
    }

    private function findFirstElementStartingWith(string $prefix): int|false {
        foreach ($this->htmlElements as $index => $element) {
            if (str_starts_with($element, $prefix)) {
                return $index;
            }
        }
        return false;
    }

}
