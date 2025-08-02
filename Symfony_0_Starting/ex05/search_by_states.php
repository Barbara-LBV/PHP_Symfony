<?php 

function search_by_states($names) {
    $states = [
        'Oregon' => 'OR',
        'Alabama' => 'AL',
        'New Jersey' => 'NJ',
        'Colorado' => 'CO',
    ];
    $capitals = [
        'OR' => 'Salem',
        'AL' => 'Montgomery',
        'NJ' => 'trenton',
        'KS' => 'Topeka',
    ];
    $names = explode(',', $names);
    $names = array_map('trim', $names);
    
    foreach ($names as $name){
        if (array_key_exists($name, $states)) {
            $abbreviation = $states[$name];
            if (array_key_exists($abbreviation, $capitals)) {
                echo "$capitals[$abbreviation] is the capital of $name.\n";
            } else {
            echo "$name is neither a capital nor a state.\n";
        }
        }
        else if (in_array($name, $capitals)) {
            $abbreviation = array_search($name, $capitals);
            if ($abbreviation !== false) {
                $state = array_search($abbreviation, $states);
                if ($state !== false) {
                    echo "$name is the capital of $state.\n";
                } else {
                    echo "$name is neither a capital nor a state.\n";
                }
            }
        } else {
            echo "$name is neither a capital nor a state.\n";
        }
    }
}
?>