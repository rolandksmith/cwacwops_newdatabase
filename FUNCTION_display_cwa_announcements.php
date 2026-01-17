function display_cwa_announcements() {
    global $wpdb;

    // --- THE GATEKEEPER ---
    static $already_run = false;
    if ($already_run) { return ""; } 
    $already_run = true;
    // ----------------------

    $initializationArray = data_initialization_func();
 //   if ($initializationArray['validUser'] !== "Y") { return "invalid user"; }

    $currentUserID   = $initializationArray['userID'];
    $currentUserRole = strtolower($initializationArray['userRole']);
    $currentUserEmail = $initializationArray['userEmail'];
    $tableName       = "wpw1_cwa_announcements";
    $trackTable      = "wpw1_cwa_announcements_tracking";
    $today           = current_time('Y-m-d');

    // Use LOWER() for 'All' check to fix the target role issue
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
            ?>
            <div class="announcement-item" style="border: 1px solid #d3d3d3; padding: 15px; background: #fff; margin-bottom: 20px; border-left: 5px solid #d9534f;">
                <h4 style="margin-top: 0; color: #333;"><?php echo esc_html($ann->ann_title); ?></h4>
                <div class="ann_text"><?php echo wpautop(wp_kses_post($ann->ann_text)); ?></div>
                <div class="ann_text"><a href="mailto:<?php echo $currentUserEmail; ?>"><b>Email this announcement to me</b></a></div>
                <small style="color: #999;">Posted on: <?php echo date('F j, Y', strtotime($ann->ann_date_created)); ?></small>
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