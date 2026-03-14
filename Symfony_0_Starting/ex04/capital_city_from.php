<?php

function capital_city_from($state) : string {
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

    if (array_key_exists($state, $states)) {
        $abbreviation = $states[$state];
        if (array_key_exists($abbreviation, $capitals)) {
            return $capitals[$abbreviation] . "\n";
        }
    } else {
        return "Unknown state: {$state}\n";
    }
}

?>