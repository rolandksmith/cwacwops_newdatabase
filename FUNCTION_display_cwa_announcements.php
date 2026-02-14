function display_cwa_announcements() {
    global $wpdb;

    // --- THE GATEKEEPER ---
    static $already_run = false;
    if ($already_run) { return ""; } 
    $already_run = true;
    // ----------------------

    $context = CWA_Context::getInstance();
    
    // 1. GET THE USER'S EMAIL
    // We try to grab it from your init array, or fall back to the WP current user
    $currentUserEmail = isset($context->userEmail) ? $context->userEmail : wp_get_current_user()->user_email;
    
    $currentUserID   = $context->userID;
    $currentUserRole = strtolower($context->userRole);
    $tableName       = "wpw1_cwa_announcements";
    $trackTable      = "wpw1_cwa_announcements_tracking";
    $today           = current_time('mysql', 1);

    $query = $wpdb->prepare("
        SELECT a.* FROM $tableName a
        LEFT JOIN $trackTable t ON a.ann_record_id = t.ann_id AND t.user_id = %d
        WHERE (LOWER(a.ann_target_role) = 'all' OR LOWER(a.ann_target_role) = %s)
        AND (
            (a.ann_occurances = 'Once' AND t.track_id IS NULL)
            OR (a.ann_occurances REGEXP '^[0-9]{4}-[0-9]{2}-[0-9]{2}$' AND a.ann_occurances >= %s)
        )
        ORDER BY a.ann_date_created DESC
    ", $currentUserID, $currentUserRole, $today);

    $announcements = $wpdb->get_results($query);

    if ($announcements) {
        ob_start();
        echo "<div class='announcement-display-box'>";
        foreach ($announcements as $ann) {
            
            // 2. PREPARE EMAIL CONTENT
            // We strip HTML tags (like <b> or <p>) so the email body is readable plain text
            // We use rawurlencode to handle spaces and special characters safely in the link
            $mailTo      = $currentUserEmail;
            $mailSubject = rawurlencode("Announcement: " . $ann->ann_title);
            $cleanText   = strip_tags(str_replace(['<br>', '<br/>', '<br />'], "\n", $ann->ann_text));
            $mailBody    = rawurlencode($cleanText . "\n\n--\nSent from CW Academy Dashboard");
            
            // Construct the Link
            $mailtoLink  = "mailto:$mailTo?subject=$mailSubject&body=$mailBody";
            ?>
            <div class="announcement-item" style="border: 1px solid #d3d3d3; padding: 15px; background: #fff; margin-bottom: 20px; border-left: 5px solid #d9534f;">
                <h4 style="margin-top: 0; color: #333;"><?php echo esc_html($ann->ann_title); ?></h4>
                <div class="ann_text"><?php echo wpautop(wp_kses_post($ann->ann_text)); ?></div>
                
                <div style="margin-top: 10px; border-top: 1px solid #eee; padding-top: 10px; overflow:hidden;">
                    <small style="color: #999; float:left; line-height: 28px;">Posted on: <?php echo date('F j, Y', strtotime($ann->ann_date_created)); ?></small>
                    
                    <a href="<?php echo $mailtoLink; ?>" 
                       style="float:right; background:#f0f0f1; color:#2271b1; text-decoration:none; padding:4px 10px; border:1px solid #2271b1; border-radius:3px; font-size:12px;">
                       Email to Me
                    </a>
                </div>
            </div>
            <?php
            
            if ($ann->ann_occurances === 'Once') {
                $wpdb->insert($trackTable, array(
                    'ann_id'  => $ann->ann_record_id,
                    'user_id' => $currentUserID
                ));
            }
        }
        echo "</div>";
        return ob_get_clean();
    }
    return "";
}
add_action('display_cwa_announcements', 'display_cwa_announcements');