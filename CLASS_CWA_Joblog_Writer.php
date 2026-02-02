/**
 * CWA Joblog Writer
 * 
 * Handles writing job execution logs to the database with proper
 * validation, security, and error handling.
 */

class CWA_Joblog_Writer {
    
    private $wpdb;
    private $table_name;
    private $required_fields = array('jobname');
    private $default_values = array(
        'jobname' => 'Not Given',
        'jobdate' => '',
        'jobtime' => 'Not Given',
        'jobwho' => 'Not Given',
        'jobmode' => 'Not Given',
        'jobdatatype' => 'Not Given',
        'jobaddlinfo' => 'Not Given',
        'jobip' => 'Not Given',
        'jobmonth' => 'Not Given',
        'jobcomments' => '',
        'jobtitle' => 'Not Given',
        'doDebug' => false
    );
    
    public function __construct() {
        global $wpdb;
        $this->wpdb = $wpdb;
        $this->table_name = $this->wpdb->prefix . 'cwa_joblog';
    }
    
    /**
     * Write a job log entry
     * 
     * @param array $data Job log data
     * @return bool True on success, false on failure
     */
    public function write($data) {
        // Merge with defaults
        $log_data = array_merge($this->default_values, $data);
        
        $doDebug = $log_data['doDebug'];
        
        if ($doDebug) {
            error_log("CWA Joblog Writer called with data: " . print_r($data, true));
        }
        
        // Validate jobname
        if (!$this->validateJobname($log_data['jobname'])) {
            $this->sendErrorNotification('Invalid jobname', $log_data);
            return false;
        }
        
        // Auto-populate missing fields
        $log_data = $this->populateDefaults($log_data);
        
        // Get browser/device information
        $browser_data = $this->getBrowserData();
        
        // Get current timestamp
        $current_time = current_time('mysql', 1);
        $timestamp = strtotime($current_time);
        
        // Prepare insert data
        $insert_data = array(
            'job_name' => sanitize_text_field($log_data['jobname']),
            'job_date' => date('Y-m-d', $timestamp),
            'job_time' => date('H:i:s', $timestamp),
            'job_who' => sanitize_text_field($log_data['jobwho']),
            'job_mode' => sanitize_text_field($log_data['jobmode']),
            'job_data_type' => sanitize_text_field($log_data['jobdatatype']),
            'job_addl_info' => sanitize_text_field($log_data['jobaddlinfo']),
            'job_ip_addr' => sanitize_text_field($log_data['jobip']),
            'job_month' => sanitize_text_field($log_data['jobmonth']),
            'job_comments' => sanitize_textarea_field($log_data['jobcomments']),
            'job_title' => sanitize_text_field($log_data['jobtitle']),
            'job_browser' => sanitize_text_field($browser_data['browser']),
            'job_version' => sanitize_text_field($browser_data['version']),
            'job_OS' => sanitize_text_field($browser_data['OS']),
            'job_Mfgr' => sanitize_text_field($browser_data['Mfgr']),
            'job_device' => sanitize_text_field($browser_data['device'])
        );
        
        $insert_format = array(
            '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s',
            '%s', '%s', '%s', '%s', '%s'
        );
        
        // Perform insert
        $result = $this->wpdb->insert(
            $this->table_name,
            $insert_data,
            $insert_format
        );
        
        if ($result === false) {
            $error = $this->wpdb->last_error;
            $query = $this->wpdb->last_query;
            
            if ($doDebug) {
                error_log("Joblog insert failed. Error: {$error}. Query: {$query}");
            }
            
            $this->sendErrorNotification("Database insert failed: {$error}", $log_data);
            return false;
        }
        
        if ($doDebug) {
            $insert_id = $this->wpdb->insert_id;
            error_log("Joblog insert successful. ID: {$insert_id}");
        }
        
        return true;
    }
    
    /**
     * Validate jobname
     * 
     * @param string $jobname Job name to validate
     * @return bool True if valid, false otherwise
     */
    private function validateJobname($jobname) {
        // Check for empty or default values
        if (empty($jobname) || $jobname === 'Not Given') {
            return false;
        }
        
        // Check for placeholder text
        if (stripos($jobname, 'jobname') !== false) {
            return false;
        }
        
        // Check for FIX placeholder
        if (stripos($jobname, 'FIX') !== false) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Populate default values for missing fields
     * 
     * @param array $data Input data
     * @return array Data with defaults populated
     */
    private function populateDefaults($data) {
        // Auto-populate jobtitle if not given
        if ($data['jobtitle'] === 'Not Given') {
            $data['jobtitle'] = esc_html(get_the_title());
            if (empty($data['jobtitle'])) {
                $data['jobtitle'] = 'Unknown Page';
            }
        }
        
        // Auto-populate jobip if not given
        if ($data['jobip'] === 'Not Given') {
            $data['jobip'] = $this->getUserIP();
        }
        
        // Auto-populate jobmonth if not given
        if ($data['jobmonth'] === 'Not Given') {
            $data['jobmonth'] = date('F Y');
        }
        
        // Check if this is a CRON job
        if ($this->isCronJob()) {
            $data['jobwho'] = 'CRON';
        }
        
        return $data;
    }
    
    /**
     * Get browser and device data
     * 
     * @return array Browser data
     */
    private function getBrowserData() {
        // If this is a CRON job, return empty data
        if ($this->isCronJob()) {
            return array(
                'browser' => '',
                'version' => '',
                'OS' => '',
                'Mfgr' => '',
                'device' => ''
            );
        }
        
        // Get browser data from existing function
        $ip_data = get_the_user_ip_data();
        
        return array(
            'browser' => isset($ip_data['browser']) ? $ip_data['browser'] : '',
            'version' => isset($ip_data['version']) ? $ip_data['version'] : '',
            'OS' => isset($ip_data['OS']) ? $ip_data['OS'] : '',
            'Mfgr' => isset($ip_data['Mfgr']) ? $ip_data['Mfgr'] : '',
            'device' => isset($ip_data['device']) ? $ip_data['device'] : ''
        );
    }
    
    /**
     * Check if current request is from CRON
     * 
     * @return bool True if CRON job
     */
    private function isCronJob() {
        // Check if already defined
        if (defined('IS_CRON') && IS_CRON) {
            return true;
        }
        
        // Check user agent for curl (common CRON indicator)
        if (isset($_SERVER['HTTP_USER_AGENT']) && 
            stripos($_SERVER['HTTP_USER_AGENT'], 'curl') === 0) {
            if (!defined('IS_CRON')) {
                define('IS_CRON', true);
            }
            return true;
        }
        
        return false;
    }
    
    /**
     * Get user IP address
     * 
     * @return string IP address
     */
    private function getUserIP() {
        if (function_exists('get_the_user_ip')) {
            return get_the_user_ip();
        }
        
        // Fallback IP detection
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            return $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            return $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            return isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '0.0.0.0';
        }
    }
    
    /**
     * Send error notification
     * 
     * @param string $message Error message
     * @param array $data Related data
     */
    private function sendErrorNotification($message, $data) {
        $jobname = isset($data['jobname']) ? $data['jobname'] : 'Unknown Job';
        $data_dump = print_r($data, true);
        
        $error_message = "CWA Joblog Writer Error: {$message}\n";
        $error_message .= "Job: {$jobname}\n";
        $error_message .= "Data: {$data_dump}";
        
        if (function_exists('sendErrorEmail')) {
            sendErrorEmail($error_message);
        } else {
            error_log($error_message);
        }
    }
}
