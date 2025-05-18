function list_available_shortcodes() {

 global $shortcode_tags;
        echo '<pre>'; 
        print_r($shortcode_tags); 
        echo '</pre><br />'


}
add_shortcode('list_avaialble_shortcoes','list_available_shortcodes');