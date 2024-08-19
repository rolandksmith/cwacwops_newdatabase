function MediaFileExists($filename){
    global $wpdb;
    $query = "SELECT COUNT(*) FROM {$wpdb->postmeta} WHERE meta_value LIKE '%/$filename'";
    $thisCount = $wpdb->get_var($query);
    return $thisCount;
}
add_action('MediaFileExists','MediaFileExists');
