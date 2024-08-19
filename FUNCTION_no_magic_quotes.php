function no_magic_quotes($theString,$doDebug=FALSE) {

// used to remove slashes from a string


        $data = explode("\\",$theString);
        $cleaned = implode("",$data);
        if ($doDebug) {
        	echo "<br />FUNCTION no_magic_quotes. theString: $theString. cleaned: $cleaned<br />";
        }
        
        return $cleaned;
}
add_action('no_magic_quotes','no_magic_quotes');