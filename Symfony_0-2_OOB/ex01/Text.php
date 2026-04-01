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

      public function __destruct() {}

    /**
     * Appends a new string to the data array (Text attribute).
     *
     * @param string $newData : new string to append to the data array (Text attribute).
     * @return bool : true if the string was appended successfully, false otherwise.
     */

    public function append(string $newData): bool {
        if (!is_string($newData)) {
            print("Error: Element must be string.\n");
            return false;
        }
        
        if (empty($this->_data)) {
            $this->_data = array($newData);
        } else {
            $this->_data[] = $newData;
        }
        return true;
    }

    
    /**
     * Reads the data from the data array (Text attribute).
     *
     * @return array : array of strings containing the data.
     */

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