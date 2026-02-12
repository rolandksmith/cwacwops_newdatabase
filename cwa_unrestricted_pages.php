<?php
/**
 * Find published pages with no Ultimate Member content restriction
 * 
 * Displays pages where _um_custom_access_settings = 0
 * (meaning no custom access restriction is applied)
 */

function cwa_unrestricted_pages_func() {
    global $wpdb;
    
    $content = "<h3>Published Pages Without UM Content Restriction</h3>";
    
    // Get all published pages
    $pages = $wpdb->get_results(
        "SELECT ID, post_title 
         FROM {$wpdb->prefix}posts 
         WHERE post_type = 'page' 
         AND post_status = 'publish' 
         ORDER BY post_title ASC",
        ARRAY_A
    );
    
    if (!$pages) {
        return $content . "<p>No published pages found.</p>";
    }
    
    $unrestricted = array();
    
    foreach ($pages as $page) {
        $meta_value = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT meta_value 
                 FROM {$wpdb->prefix}postmeta 
                 WHERE post_id = %d 
                 AND meta_key = %s",
                $page['ID'],
                'um_content_restriction'
            )
        );
        
        if ($meta_value) {
            $restriction = unserialize($meta_value);
            
            if (is_array($restriction) && 
                isset($restriction['_um_custom_access_settings']) && 
                $restriction['_um_custom_access_settings'] == 0) {
                $unrestricted[] = $page;
            }
        } else {
            // No UM meta at all — also unrestricted
            $unrestricted[] = $page;
        }
    }
    
    if (empty($unrestricted)) {
        return $content . "<p>All published pages have UM content restrictions.</p>";
    }
    
    $content .= "<p>Found " . count($unrestricted) . " unrestricted page(s).</p>";
    $content .= "<table style='width:100%; border-collapse:collapse;'>
        <thead>
            <tr>
                <th style='text-align:left;'>ID</th>
                <th style='text-align:left;'>Page Title</th>
            </tr>
        </thead>
        <tbody>";
    
    foreach ($unrestricted as $page) {
        $content .= "<tr>
            <td>" . esc_html($page['ID']) . "</td>
            <td>" . esc_html($page['post_title']) . "</td>
        </tr>";
    }
    
    $content .= "</tbody></table>";
    
    return $content;
}

// Register as shortcode
add_shortcode('cwa_unrestricted_pages', 'cwa_unrestricted_pages_func');
