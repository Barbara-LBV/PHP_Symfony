<?php 

class Text{

    public array $_data;

    public function __construct(array $array){
        if (!is_array($array)) {
            print("Error: Input is not an array.\n");
            return false;
        }
        foreach ($array as $value) {
            if (!is_string($value)) {
                print("Error: All elements must be strings.\n");
                return false;
            }
        }
        $this->_data = $array;
    }

    public function append(string $new_data)
    {
        if (!is_string($new_data)) {
            print("Error: Element must be string.\n");
            return false;
        }
        
        if (empty($this->_data)) {
            $this->_data = array($new_data);
        } else {
            $this->_data[] = $new_data;
        }
    }

    public function readData(): array
    {
        if (empty($this->_data)) {
            print("Error: No data to read.\n");
            return array();
        }

        $content[] = '';

        foreach ($this->_data as $str)
            $content[] .= "<p>" . htmlspecialchars($str) . "</p>\n";

        $size = count($content) - 1;

        // Remove the last empty element if it exists
        if ($size >= 0)
            $content[$size] = trim($content[$size]);

        return $content;
    }
}

?>