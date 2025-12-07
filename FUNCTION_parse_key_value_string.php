/**
 * Parses a specific string format of key/value pairs into an associative array.
 *
 * Example format: "'key1'=>'value1','key2'=>'value2'"
 *
 * @param string $input_string The string to parse.
 * @return array An associative array of the key/value pairs.
 */
function parse_key_value_string($input_string) {
    $result = [];
    
    // This regex looks for patterns like:
    // 'key'  (quoted key)
    // \s*=>\s* (arrow operator, with optional whitespace)
    // 'value' (quoted value)
    $pattern = "/'([^']+)'\s*=>\s*'([^']+)'/";

    // preg_match_all will find all occurrences that match the pattern.
    // PREG_SET_ORDER organizes the matches into a logical array.
    if (preg_match_all($pattern, $input_string, $matches, PREG_SET_ORDER)) {
        
        foreach ($matches as $match) {
            // $match[0] is the full string matched (e.g., "'roland'=>'name'")
            // $match[1] is the first capture group (the key, e.g., "roland")
            // $match[2] is the second capture group (the value, e.g., "name")
            
            $key = $match[1];
            $value = $match[2];
            $result["'$key'"] = "'$value'";
        }
    }

    return $result;
}

