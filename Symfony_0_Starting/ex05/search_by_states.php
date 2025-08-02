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
    $states_reverse = array_flip($states); // ['OR' => 'Oregon', ...]

    $names = explode(',', $names);
    $names = array_map('trim', $names);

    foreach($names as $name) {
        $found = false;

        if (isset($states[$name])) {
            $abbr = $states[$name];
            if (isset($capitals[$abbr])) {
                echo "{$capitals[$abbr]} is the capital of $name.\n";
                $found = true;
            }
        }

        if (!$found && in_array($name, $capitals)) {
            $abbr = array_search($name, $capitals);
            if (isset($states_reverse[$abbr])) {
                $state = $states_reverse[$abbr];
                echo "{$capitals[$abbr]} is the capital of $state.\n";
                $found = true;
            }
        }
        
        if (!$found) {
            echo "$name is neither a capital nor a state.\n";
        }
    }
}
?>