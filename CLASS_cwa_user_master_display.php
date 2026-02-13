<?php
/**
 * CWA User Master Display
 * 
 * Provides a standardized display of user_master information in a 4-column table.
 * Can be called from anywhere user master data needs to be displayed.
 * 
 * Usage:
 *   $display = new CWA_User_Master_Display();
 *   echo $display->render(123);           // By user ID
 *   echo $display->render('K7OJL');       // By call sign
 */

class CWA_User_Master_Display {
    
    private $dal;
    private $config;
    private $isAdmin;
    
    public function __construct() {
        $this->dal = new CWA_User_Master_DAL();
        $this->initializeConfig();
    }
    
    /**
     * Initialize configuration
     */
    private function initializeConfig() {
        $initData = data_initialization_func();
        
        $this->config = array(
            'testMode' => false,
            'userName' => $initData['userName'],
        );
        
        // Check for test mode
        if (isset($_REQUEST['inp_mode']) && 
            $_REQUEST['inp_mode'] === 'TESTMODE' && 
            in_array($initData['userName'], $initData['validTestmode'])) {
            $this->config['testMode'] = true;
        }
        
        // Determine admin status from userRole
        $this->isAdmin = ($initData['userRole'] === 'administrator');
    }
    
    /**
     * Render the user master display table
     * 
     * @param int|string $identifier User ID (int) or Call Sign (string)
     * @return string HTML table displaying user master data
     */
    public function render($identifier) {
        $mode = $this->config['testMode'] ? 'Testing' : 'Production';
        
        // Fetch the record by ID or call sign
        if (is_numeric($identifier)) {
            $records = $this->dal->get_user_master_by_id((int)$identifier, $mode);
        } else {
            $records = $this->dal->get_user_master_by_callsign(strtoupper($identifier), $mode);
        }
        
        if (!$records || count($records) === 0) {
            return "<p>User record not found.</p>";
        }
        
        $record = $records[0];
        
        return $this->buildTable($record);
    }
    
    /**
     * Build the HTML table
     * 
     * @param array $record The user_master record
     * @return string HTML table
     */
    private function buildTable($record) {
        $html = "<table style='width:100%; border-collapse:collapse;'>";
        
        // Table title
        $html .= "<tr><th colspan='4' style='text-align:center; padding:8px;'><b>User Master Data</b></th></tr>";
        
        // Row 1: Call Sign | Name | Phone | Email
        $html .= $this->buildHeaderRow(array('Call Sign', 'Name', 'Phone', 'Email'));
        $html .= $this->buildDataRow(array(
            '<b>' . esc_html($record['user_call_sign']) . '</b> (' . esc_html($record['user_ID']) . ')',
            esc_html($record['user_last_name'] . ', ' . $record['user_first_name']),
            esc_html('+' . $record['user_ph_code'] . ' ' . $record['user_phone']),
            esc_html($record['user_email'])
        ));
        
        // Row 2: City | State | Zip Code | Country
        $html .= $this->buildHeaderRow(array('City', 'State', 'Zip Code', 'Country'));
        $html .= $this->buildDataRow(array(
            esc_html($record['user_city']),
            esc_html($record['user_state']),
            esc_html($record['user_zip_code']),
            esc_html($record['user_country'])
        ));
        
        // Row 3: WhatsApp | Telegram | Signal | Messenger
        $html .= $this->buildHeaderRow(array('WhatsApp', 'Telegram', 'Signal', 'Messenger'));
        $html .= $this->buildDataRow(array(
            esc_html($record['user_whatsapp']),
            esc_html($record['user_telegram']),
            esc_html($record['user_signal']),
            esc_html($record['user_messenger'])
        ));
        
        // Row 4: Timezone ID | Languages | Date Created | Date Updated
        $html .= $this->buildHeaderRow(array('Timezone ID', 'Languages', 'Date Created', 'Date Updated'));
        $html .= $this->buildDataRow(array(
            esc_html($record['user_timezone_id']),
            esc_html($record['user_languages']),
            esc_html($record['user_date_created']),
            esc_html($record['user_date_updated'])
        ));
        
        // Admin-only rows
        if ($this->isAdmin) {
            // Row 5: Survey Score | Is Admin | Role | Prev Call Sign
            $html .= $this->buildHeaderRow(array('Survey Score', 'Is Admin', 'Role', 'Prev Call Sign'));
            $html .= $this->buildDataRow(array(
                esc_html($record['user_survey_score']),
                esc_html($record['user_is_admin']),
                esc_html($record['user_role']),
                esc_html($record['user_prev_callsign'])
            ));
            
            // Row 6: Action Log (spans all 4 columns)
            $html .= "<tr><td colspan='4' style='padding:8px; border:1px solid #ccc; background:#f5f5f5;'><b>Action Log</b></td></tr>";
            $reformattedLog = formatActionLog($record['user_action_log']);
            $html .= "<tr><td colspan='4' style='padding:8px; border:1px solid #ccc;'>{$reformattedLog}</td></tr>";
        }
        
        $html .= "</table>";
        
        return $html;
    }
    
    /**
     * Build a header row
     * 
     * @param array $headers Array of 4 header strings
     * @return string HTML table row
     */
    private function buildHeaderRow($headers) {
        $html = "<tr>";
        foreach ($headers as $header) {
            $html .= "<td style='padding:8px; border:1px solid #ccc; background:#f5f5f5;'><b>{$header}</b></td>";
        }
        $html .= "</tr>";
        return $html;
    }
    
    /**
     * Build a data row
     * 
     * @param array $data Array of 4 data values (already escaped/formatted)
     * @return string HTML table row
     */
    private function buildDataRow($data) {
        $html = "<tr>";
        foreach ($data as $value) {
            $html .= "<td style='padding:8px; border:1px solid #ccc;'>{$value}</td>";
        }
        $html .= "</tr>";
        return $html;
    }
    
    /**
     * Check if current user is admin (useful for external checks)
     * 
     * @return bool
     */
    public function isCurrentUserAdmin() {
        return $this->isAdmin;
    }
}