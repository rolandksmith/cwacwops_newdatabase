/**
 * Helper class for logging
 */
class CWA_Action_Logger {
    
    public function formatAction($message, $jobname) {
        $date = date('dMy H:i');
        return "{$date} {$jobname} {$message}";
    }
    
    public function logError($message) {
        error_log("CWA Advisor Registration Error: {$message}");
        sendErrorEmail("Advisor Registration: {$message}");
    }
    
    public function logDatabaseError($operation, $identifier) {
        global $wpdb;
        $error = $wpdb->last_error;
        $query = $wpdb->last_query;
        
        $message = "{$operation} failed for {$identifier}. Error: {$error}. Query: {$query}";
        $this->logError($message);
    }
    
    public function logJobExecution($jobname, $pass, $elapsed, $user) {
        $data = [
            'jobname' => $jobname,
            'jobdate' => date('Y-m-d'),
            'jobtime' => date('H:i:s'),
            'jobwho' => $user,
            'jobmode' => 'Time',
            'jobdatatype' => 'Production',
            'jobaddlinfo' => "{$pass}: {$elapsed}",
            'jobip' => get_the_user_ip(),
            'jobmonth' => date('F Y'),
            'jobcomments' => '',
            'jobtitle' => esc_html(get_the_title()),
            'doDebug' => false,
        ];
        
        write_joblog2_func($data);
    }
}
