/**
 * CWA Advisor Registration - Refactored Version
 * Handles advisor registration, class management, and updates
 */

class CWA_Advisor_Registration {
    
    private $wpdb;
    private $config;
    private $user;
    private $logger;
    
    // Constants
    const PASS_INITIAL = '1';
    const PASS_DISPLAY_INFO = '2';
    const PASS_CREATE_ADVISOR = '3';
    const PASS_ADD_CLASS_FORM = '5';
    const PASS_ADD_CLASS = '6';
    const PASS_EDIT_CLASS_FORM = '15';
    const PASS_UPDATE_CLASS = '16';
    const PASS_DELETE_CLASS = '17';
    const PASS_DELETE_ADVISOR = '20';
    
    const MIN_DAYS_BEFORE_SEMESTER = 21;
    const DEFAULT_CLASS_SIZE = 6;
    const BAD_ACTOR_SCORE = '6';
    const VERIFICATION_REFUSED = 'R';
    
    public function __construct() {
        global $wpdb;
        $this->wpdb = $wpdb;
        $this->initializeConfig();
        $this->initializeUser();
        $this->logger = new CWA_Action_Logger();
    }
    
    /**
     * Initialize configuration settings
     */
    private function initializeConfig() {
        $initData = data_initialization_func();
        
        $this->config = array(
            'testMode' => false,
            'doDebug' => false,
            'maintenanceMode' => false,
            'validUser' => $initData['validUser'],
            'currentTimestamp' => $initData['currentTimestamp'],
            'currentSemester' => $initData['currentSemester'],
            'prevSemester' => $initData['prevSemester'],
            'nextSemester' => $initData['nextSemester'],
            'semesterTwo' => $initData['semesterTwo'],
            'semesterThree' => $initData['semesterThree'],
            'semesterFour' => $initData['semesterFour'],
            'daysToSemester' => intval($initData['daysToSemester']),
            'siteURL' => $initData['siteurl'],
            'languageArray' => $initData['languageArray'],
            'validTestmode' => $initData['validTestmode'],
            'defaultClassSize' => $initData['defaultClassSize'],
            'allowSignup' => false,
        );
        
        // Set table names based on mode
        $this->setTableNames();
    }
    
    /**
     * Set database table names based on test/production mode
     */
    private function setTableNames() {
        $prefix = $this->config['testMode'] ? '2' : '';
        
        $this->config['tables'] = array(
            'advisor' => "wpw1_cwa_advisor{$prefix}",
            'advisorClass' => "wpw1_cwa_advisorclass{$prefix}",
            'advisorDeleted' => "wpw1_cwa_advisor_deleted{$prefix}",
            'advisorClassDeleted' => "wpw1_cwa_deleted_advisorclass{$prefix}",
            'userMaster' => "wpw1_cwa_user_master{$prefix}",
        );
    }
    
    /**
     * Initialize user data
     */
    private function initializeUser() {
        $initData = data_initialization_func();
        
        $this->user = array(
            'name' => $initData['userName'],
            'role' => $initData['userRole'],
            'ipAddress' => $this->getUserIP(),
        );
    }
    
    /**
     * Main entry point - handles the registration flow
     */
    public function handle() {
        $startTime = microtime(true);
        
        // Security check
        if (!$this->isAuthorized()) {
            return $this->renderUnauthorized();
        }
        
        // Handle test mode
        $this->handleTestMode();
        
        // Get and sanitize input
        $input = $this->getInput();
        
        // Route to appropriate handler based on pass
        $content = $this->routeRequest($input);
        
        // Add execution time and logging
        $content .= $this->addFooter($startTime, $input['strpass']);
        
        return $content;
    }
    
    /**
     * Check if user is authorized
     */
    private function isAuthorized() {
        return !empty($this->user['name']);
    }
    
    /**
     * Render unauthorized message
     */
    private function renderUnauthorized() {
        return '<p>You are not authorized to access this page.</p>';
    }
    
    /**
     * Handle test mode configuration
     */
    private function handleTestMode() {
        // Check if user can access test mode
        if (isset($_REQUEST['inp_mode']) && 
            $_REQUEST['inp_mode'] === 'TESTMODE' && 
            in_array($this->user['name'], $this->config['validTestmode'])) {
            $this->config['testMode'] = true;
            $this->setTableNames(); // Update table names
        }
        
        // Handle verbose debugging
        if (isset($_REQUEST['inp_verbose']) && $_REQUEST['inp_verbose'] === 'Y') {
            $this->config['doDebug'] = true;
        }
    }
    
    /**
     * Get and sanitize all input
     */
    private function getInput() {
        $input = array(
            'strpass' => $this->sanitize(isset($_REQUEST['strpass']) ? $_REQUEST['strpass'] : self::PASS_INITIAL, 'string'),
            'callsign' => $this->sanitize(isset($_REQUEST['inp_callsign']) ? $_REQUEST['inp_callsign'] : (isset($_REQUEST['call_sign']) ? $_REQUEST['call_sign'] : ''), 'callsign'),
            'semester' => $this->sanitize(isset($_REQUEST['inp_semester']) ? $_REQUEST['inp_semester'] : '', 'string'),
            'classID' => $this->sanitize(isset($_REQUEST['classID']) ? $_REQUEST['classID'] : 0, 'int'),
            'allowSignup' => $this->sanitize(isset($_REQUEST['allowSignup']) ? $_REQUEST['allowSignup'] : false, 'boolean'),
            'bypass' => $this->sanitize(isset($_REQUEST['inp_bypass']) ? $_REQUEST['inp_bypass'] : 'N', 'string'),
            'classcount' => $this->sanitize(isset($_REQUEST['classcount']) ? $_REQUEST['classcount'] : 0, 'int'),
        );
        
        // Handle encrypted string parameter
        if (isset($_REQUEST['enstr'])) {
            $decoded = $this->decodeEncodedParams($_REQUEST['enstr']);
            $input = array_merge($input, $decoded);
        }
        
        // Get class-specific inputs if present
        $input = array_merge($input, $this->getClassInput());
        
        // Update config allowSignup if set in input
        if ($input['allowSignup']) {
            $this->config['allowSignup'] = true;
        }
        
        return $input;
    }
    
    /**
     * Sanitize input based on type
     */
    private function sanitize($value, $type = 'string') {
        switch ($type) {
            case 'callsign':
                return strtoupper(sanitize_text_field($value));
                
            case 'email':
                return sanitize_email($value);
                
            case 'int':
                return intval($value);
                
            case 'float':
                return floatval($value);
                
            case 'boolean':
                return filter_var($value, FILTER_VALIDATE_BOOLEAN);
                
            case 'url':
                return esc_url_raw($value);
                
            case 'string':
            default:
                return sanitize_text_field($value);
        }
    }
    
    /**
     * Decode encrypted parameters
     */
    private function decodeEncodedParams($enstr) {
        $decoded = array();
        $stringToPass = base64_decode($enstr);
        
        if ($stringToPass === false) {
            return $decoded;
        }
        
        $params = explode("&", $stringToPass);
        
        foreach ($params as $param) {
            $parts = explode("=", $param, 2);
            if (count($parts) === 2) {
                $key = $parts[0];
                $value = $parts[1];
                
                // Apply appropriate sanitization
                if ($key === 'semester') {
                    $decoded['allowSignup'] = true;
                    $decoded['semester'] = $this->sanitize($value, 'string');
                } elseif (strpos($key, 'inp_') === 0) {
                    $decoded[substr($key, 4)] = $this->sanitize($value, 'string');
                }
            }
        }
        
        return $decoded;
    }
    
    /**
     * Get class-specific input fields
     */
    private function getClassInput() {
        return array(
            'level' => $this->sanitize(isset($_REQUEST['inp_level']) ? $_REQUEST['inp_level'] : '', 'string'),
            'teaching_days' => $this->sanitize(isset($_REQUEST['inp_teaching_days']) ? $_REQUEST['inp_teaching_days'] : (isset($_REQUEST['inp_class_schedule_days']) ? $_REQUEST['inp_class_schedule_days'] : ''), 'string'),
            'times' => $this->sanitize(isset($_REQUEST['inp_times']) ? $_REQUEST['inp_times'] : (isset($_REQUEST['inp_class_schedule_times']) ? $_REQUEST['inp_class_schedule_times'] : ''), 'string'),
            'class_size' => $this->sanitize(isset($_REQUEST['inp_class_size']) ? $_REQUEST['inp_class_size'] : self::DEFAULT_CLASS_SIZE, 'int'),
            'language' => $this->sanitize(isset($_REQUEST['inp_advisorclass_language']) ? $_REQUEST['inp_advisorclass_language'] : 'English', 'string'),
            'sequence' => $this->sanitize(isset($_REQUEST['inp_sequence']) ? $_REQUEST['inp_sequence'] : 0, 'int'),
            'id' => $this->sanitize(isset($_REQUEST['inp_id']) ? $_REQUEST['inp_id'] : 0, 'int'),
        );
    }
    
    /**
     * Route request to appropriate handler
     */
    private function routeRequest($input) {
        // Check maintenance mode
        if ($this->config['maintenanceMode'] && $input['strpass'] === self::PASS_INITIAL) {
            return $this->renderMaintenanceMode();
        }
        
        switch ($input['strpass']) {
            case self::PASS_INITIAL:
                return $this->handleInitial($input);
                
            case self::PASS_DISPLAY_INFO:
                return $this->handleDisplayInfo($input);
                
            case self::PASS_CREATE_ADVISOR:
                return $this->handleCreateAdvisor($input);
                
            case self::PASS_ADD_CLASS_FORM:
                return $this->handleAddClassForm($input);
                
            case self::PASS_ADD_CLASS:
                return $this->handleAddClass($input);
                
            case self::PASS_EDIT_CLASS_FORM:
                return $this->handleEditClassForm($input);
                
            case self::PASS_UPDATE_CLASS:
                return $this->handleUpdateClass($input);
                
            case self::PASS_DELETE_CLASS:
                return $this->handleDeleteClass($input);
                
            case self::PASS_DELETE_ADVISOR:
                return $this->handleDeleteAdvisor($input);
                
            default:
                return '<p>Invalid request.</p>';
        }
    }
    
    /**
     * Handle initial pass - determine user role and show appropriate form
     */
    private function handleInitial($input) {
        $content = '<h3>Advisor Registration</h3>';
        
        if ($this->user['role'] === 'advisor') {
            // Advisors go directly to pass 2
            $input['callsign'] = strtoupper($this->user['name']);
            return $this->handleDisplayInfo($input);
            
        } elseif ($this->user['role'] === 'administrator') {
            // Admins enter a call sign
            return $this->renderAdminForm();
            
        } else {
            return $this->renderUnauthorized();
        }
    }
    
    /**
     * Render admin form for entering call sign
     */
    private function renderAdminForm() {
        $testModeOptions = $this->renderTestModeOptions();
        $theURL = $this->config['siteURL'] . '/cwa-advisor-registration/';
        
        $html = "<h3>Advisor Registration - Administrator Access</h3>
<form method=\"post\" action=\"{$theURL}\" name=\"selection_form\">
    <input type=\"hidden\" name=\"strpass\" value=\"2\">
    <table style=\"border-collapse:collapse;\">
        <tr>
            <td>Advisor Call Sign</td>
            <td><input type=\"text\" class=\"formInputText\" name=\"inp_callsign\" size=\"10\" maxlength=\"10\" required autofocus></td>
        </tr>
        {$testModeOptions}
        <tr>
            <td>&nbsp;</td>
            <td><input class=\"formInputButton\" type=\"submit\" value=\"Next\"></td>
        </tr>
    </table>
</form>";
        
        return $html;
    }
    
    /**
     * Handle displaying advisor info (pass 2)
     */
    private function handleDisplayInfo($input) {
        $content = '<h3>Advisor Registration</h3>';
        
        // Get user master record
        $userMaster = $this->getUserMaster($input['callsign']);
        
        if (!$userMaster) {
            $this->logger->logError("No user_master record for {$input['callsign']}");
            return $content . '<p>No record found for ' . esc_html($input['callsign']) . '. The system administrator has been notified.</p>';
        }
        
        // Check if advisor already has records for current/future semesters
        $existingAdvisor = $this->getExistingAdvisorRecord($input['callsign']);
        
        if ($existingAdvisor) {
            return $content . $this->displayExistingAdvisor($existingAdvisor, $userMaster);
        } else {
            return $content . $this->displayNewAdvisorSignup($userMaster, $input);
        }
    }
    
    /**
     * Get user master record
     */
    private function getUserMaster($callsign) {
        $table = $this->config['tables']['userMaster'];
        
        $sql = $this->wpdb->prepare(
            "SELECT * FROM {$table} WHERE user_call_sign = %s",
            $callsign
        );
        
        return $this->wpdb->get_row($sql);
    }
    
    /**
     * Get existing advisor record for current or future semesters
     */
    private function getExistingAdvisorRecord($callsign) {
        if ($this->config['allowSignup']) {
            return null; // Skip check if coming from evaluate_student
        }
        
        $table = $this->config['tables']['advisor'];
        $semesters = array(
            $this->config['currentSemester'],
            $this->config['nextSemester'],
            $this->config['semesterTwo'],
            $this->config['semesterThree'],
            $this->config['semesterFour'],
        );
        
        $placeholders = implode(',', array_fill(0, count($semesters), '%s'));
        
        $sql = $this->wpdb->prepare(
            "SELECT * FROM {$table} 
             WHERE advisor_call_sign = %s 
             AND advisor_semester IN ({$placeholders})",
            array_merge(array($callsign), $semesters)
        );
        
        return $this->wpdb->get_row($sql);
    }
    
    /**
     * Display existing advisor information
     */
    private function displayExistingAdvisor($advisor, $userMaster) {
        // Check if advisor can update
        $canUpdate = $this->canUpdateAdvisor($advisor);
        
        // Check for verification issues
        if ($advisor->advisor_class_verified === self::VERIFICATION_REFUSED) {
            return $this->renderVerificationRefused();
        }
        
        // Display advisor and class information
        return $this->renderAdvisorInfo($advisor->advisor_call_sign, $advisor->advisor_semester, !$canUpdate);
    }
    
    /**
     * Check if advisor can update their record
     */
    private function canUpdateAdvisor($advisor) {
        $daysToSemester = days_to_semester($advisor->advisor_semester);
        
        // Can't update if semester has passed
        if ($daysToSemester < 0) {
            return false;
        }
        
        // Can't update within 21 days of semester start
        if ($daysToSemester > 0 && $daysToSemester < self::MIN_DAYS_BEFORE_SEMESTER) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Display new advisor signup form
     */
    private function displayNewAdvisorSignup($userMaster, $input) {
        // Check eligibility
        $eligibility = $this->checkAdvisorEligibility($userMaster, $input['callsign']);
        
        if (!$eligibility['eligible']) {
            return $eligibility['message'];
        }
        
        // Check timezone
        if ($userMaster->user_timezone_id === 'XX') {
            return $this->renderTimezoneWarning($userMaster->user_call_sign);
        }
        
        // Render signup form
        return $this->renderSignupForm($userMaster);
    }
    
    /**
     * Check if advisor is eligible to sign up
     */
    private function checkAdvisorEligibility($userMaster, $callsign) {
        // Check for bad actor
        if ($userMaster->user_survey_score === self::BAD_ACTOR_SCORE) {
            return array(
                'eligible' => false,
                'message' => $this->renderContactResolution('bad actor status'),
            );
        }
        
        // Check for incomplete evaluations from previous semester
        $evaluationsComplete = $this->checkEvaluationsComplete($callsign);
        
        if (!$evaluationsComplete['complete']) {
            return array(
                'eligible' => false,
                'message' => $this->renderEvaluationsIncomplete($evaluationsComplete['semester']),
            );
        }
        
        return array('eligible' => true);
    }
    
    /**
     * Check if advisor completed evaluations from previous semester
     */
    private function checkEvaluationsComplete($callsign) {
        $checkSemester = $this->config['currentSemester'] === 'Not in Session' 
            ? $this->config['prevSemester'] 
            : $this->config['currentSemester'];
        
        $table = $this->config['tables']['advisorClass'];
        
        $sql = $this->wpdb->prepare(
            "SELECT advisorclass_evaluation_complete 
             FROM {$table} 
             WHERE advisorclass_call_sign = %s 
             AND advisorclass_semester = %s",
            $callsign,
            $checkSemester
        );
        
        $results = $this->wpdb->get_results($sql);
        
        if (!$results) {
            return array('complete' => true); // No previous classes
        }
        
        foreach ($results as $row) {
            if ($row->advisorclass_evaluation_complete !== 'Y') {
                return array(
                    'complete' => false,
                    'semester' => $checkSemester,
                );
            }
        }
        
        return array('complete' => true);
    }
    
    /**
     * Render signup form for new advisor
     */
    private function renderSignupForm($userMaster) {
        $theURL = $this->config['siteURL'] . '/cwa-advisor-registration/';
        $doDebug = $this->config['doDebug'] ? 'Y' : 'N';
        $testMode = $this->config['testMode'] ? 'TESTMODE' : 'Production';
        $siteURL = $this->config['siteURL'];
        $allowSignup = $this->config['allowSignup'] ? 'true' : 'false';
        
        // Display user master info
        $content = $this->renderUserMasterInfo($userMaster, true);
        
        // Build language options
        $languageOptions = $this->buildLanguageRadioButtons('English');
        
        $content .= "<p>If any of the above information needs to be updated, 
please click <a href='{$siteURL}/cwa-display-and-update-user-master-information/?strpass=2&request_type=callsign&request_info={$userMaster->user_call_sign}&inp_depth=one&doDebug={$doDebug}&testMode={$testMode}' target='_blank'>HERE</a> 
to update the advisor Master Data before proceeding with the sign up process</p>

<p>Please fill out the following form and submit it. You must sign up for 
one class. You will be able to sign up for as many classes as you wish.</p> 

<form method='post' action='{$theURL}' name='advisor_signup_form'>
    <input type='hidden' name='inp_callsign' value='{$userMaster->user_call_sign}'>
    <input type='hidden' name='strpass' value='3'>
    <input type='hidden' name='inp_mode' value='{$testMode}'>
    <input type='hidden' name='inp_verbose' value='{$doDebug}'>
    <input type='hidden' name='allowSignup' value='{$allowSignup}'>
    <table style='width:1000px;'>
        <tr>
            <td style='vertical-align:top;'><b>Call Sign</b><br />{$userMaster->user_call_sign}</td>
            <td style='vertical-align:top;'><b>Sequence</b><br />1</td>
            <td style='vertical-align:top;'><b>Semester</b><br />
                <input type='radio' class='formInputButton' name='inp_semester' value='{$this->config['nextSemester']}' checked> {$this->config['nextSemester']}<br />
                <input type='radio' class='formInputButton' name='inp_semester' value='{$this->config['semesterTwo']}'> {$this->config['semesterTwo']}<br />
                <input type='radio' class='formInputButton' name='inp_semester' value='{$this->config['semesterThree']}'> {$this->config['semesterThree']}
            </td>
        </tr>
        <tr>
            <td style='vertical-align:top;width:330px;'><b>Level</b><br />
                <input type='radio' class='formInputButton' name='inp_level' value='Beginner' required> Beginner<br />
                <input type='radio' class='formInputButton' name='inp_level' value='Fundamental' required> Fundamental (formerly Basic)<br />
                <input type='radio' class='formInputButton' name='inp_level' value='Intermediate' required> Intermediate<br />
                <input type='radio' class='formInputButton' name='inp_level' value='Advanced' required> Advanced<br />
            </td>
            <td style='vertical-align:top;'><b>Language</b><br />{$languageOptions}</td>
            <td style='vertical-align:top;'><b>Class Size</b><br />
                <input type='text' class='formInputText' name='inp_class_size' size='5' maxlength='5' value='6'><br />
                (default class size is 6)
            </td>
        </tr>
        <tr>
            <td style='vertical-align:top;'><b>Class Teaching Days</b><br />Note that most advisors hold classes on Monday and Thursday<br />
                <input type='radio' class='formInputButton' name='inp_teaching_days' value='Sunday,Wednesday' required> Sunday and Wednesday<br />
                <input type='radio' class='formInputButton' name='inp_teaching_days' value='Sunday,Thursday' required> Sunday and Thursday<br />
                <input type='radio' class='formInputButton' name='inp_teaching_days' value='Monday,Thursday' required checked> Monday and Thursday<br />
                <input type='radio' class='formInputButton' name='inp_teaching_days' value='Tuesday,Friday' required> Tuesday and Friday
            </td>
            <td style='vertical-align:top;' colspan='2'><b>Local Start Time</b><br />
                Specify start time in local time where you live. Select the time where this class will start. 
                The program will account for standard or daylight savings time or summer time as needed.<br />
                {$this->renderTimeSlots()}
            </td>
        </tr>
        <tr>
            <td>&nbsp;</td>
            <td><input class='formInputButton' type='submit' value='Submit' /></td>
        </tr>
    </table>
</form>";
        
        return $content;
    }
    
    /**
     * Render user master information table
     */
    private function renderUserMasterInfo($userMaster, $showUpdateLink = false) {
        $siteURL = $this->config['siteURL'];
        $doDebug = $this->config['doDebug'] ? 'Y' : 'N';
        $testMode = $this->config['testMode'] ? 'TESTMODE' : 'Production';
        
        $content = "<h4>User Master Data</h4>
<table style='width:900px;'>
    <tr>
        <td><b>Callsign</b><br />{$userMaster->user_call_sign}</td>
        <td><b>Name</b><br />{$userMaster->user_last_name}, {$userMaster->user_first_name}</td>
        <td><b>Phone</b><br />+{$userMaster->user_ph_code} {$userMaster->user_phone}</td>
        <td><b>Email</b><br />{$userMaster->user_email}</td>
    </tr>
    <tr>
        <td><b>City</b><br />{$userMaster->user_city}</td>
        <td><b>State</b><br />{$userMaster->user_state}</td>
        <td><b>Zip Code</b><br />{$userMaster->user_zip_code}</td>
        <td><b>Country</b><br />{$userMaster->user_country}</td>
    </tr>
    <tr>
        <td><b>WhatsApp</b><br />{$userMaster->user_whatsapp}</td>
        <td><b>Telegram</b><br />{$userMaster->user_telegram}</td>
        <td><b>Signal</b><br />{$userMaster->user_signal}</td>
        <td><b>Messenger</b><br />{$userMaster->user_messenger}</td>
    </tr>
    <tr>
        <td><b>Timezone ID</b><br />{$userMaster->user_timezone_id}</td>
        <td><b>Date Created</b><br />{$userMaster->user_date_created}</td>
        <td><b>Date Updated</b><br />{$userMaster->user_date_updated}</td>
        <td></td>
    </tr>
</table>";
        
        return $content;
    }
    
    /**
     * Build language radio buttons
     */
    private function buildLanguageRadioButtons($selectedLanguage = 'English') {
        $options = '';
        $first = true;
        
        foreach ($this->config['languageArray'] as $language) {
            $checked = ($language === $selectedLanguage) ? 'checked' : '';
            $br = $first ? '' : '<br />';
            $options .= "{$br}<input type='radio' class='formInputButton' name='inp_advisorclass_language' value='{$language}' {$checked} required>{$language}";
            $first = false;
        }
        
        return $options;
    }
    
    /**
     * Render time slot options
     */
    private function renderTimeSlots($selectedTime = '') {
        $times = array(
            '0600' => '6:00am', '0630' => '6:30am', '0700' => '7:00am', '0730' => '7:30am',
            '0800' => '8:00am', '0830' => '8:30am', '0900' => '9:00am', '0930' => '9:30am',
            '1000' => '10:00am', '1030' => '10:30am', '1100' => '11:00am', '1130' => '11:30am',
            '1200' => 'Noon', '1230' => '12:30pm', '1300' => '1:00pm', '1330' => '1:30pm',
            '1400' => '2:00pm', '1430' => '2:30pm', '1500' => '3:00pm', '1530' => '3:30pm',
            '1600' => '4:00pm', '1630' => '4:30pm', '1700' => '5:00pm', '1730' => '5:30pm',
            '1800' => '6:00pm', '1830' => '6:30pm', '1900' => '7:00pm', '1930' => '7:30pm',
            '2000' => '8:00pm', '2030' => '8:30pm', '2100' => '9:00pm', '2130' => '9:30pm',
            '2200' => '10:00pm', '2230' => '10:30pm'
        );
        
        $columns = array_chunk($times, 9, true);
        $output = '<table><tr>';
        
        foreach ($columns as $column) {
            $output .= "<td style='width:110px;vertical-align:top;'>";
            foreach ($column as $value => $label) {
                $checked = ($value === $selectedTime) ? 'checked' : '';
                $output .= "<input type='radio' class='formInputButton' name='inp_times' value='{$value}' {$checked} required> {$label}<br />";
            }
            $output .= "</td>";
        }
        
        $output .= '</tr></table>';
        return $output;
    }
    
    /**
     * Handle creating new advisor and first class (pass 3)
     */
    private function handleCreateAdvisor($input) {
        $content = '<h3>Advisor Registration</h3>';
        
        // Get user master record
        $userMaster = $this->getUserMaster($input['callsign']);
        
        if (!$userMaster) {
            $this->logger->logError("No user_master record for {$input['callsign']} in pass 3");
            return $content . '<p>An error occurred. The system administrator has been notified.</p>';
        }
        
        // Check if advisor is also a student
        $this->checkAdvisorStudentConflict($input['callsign'], $input['semester']);
        
        // Create advisor record
        $advisorID = $this->createAdvisorRecord($input);
        
        if (!$advisorID) {
            return $content . '<p>An error occurred creating the advisor record.</p>';
        }
        
        // Create first class record
        $classCreated = $this->createClassRecord($input, $userMaster, 1);
        
        if (!$classCreated) {
            return $content . '<p>An error occurred creating the class record.</p>';
        }
        
        // Display created advisor info
        $content .= $this->renderAdvisorInfo($input['callsign'], $input['semester'], false);
        $content .= '<p><b>Note:</b> Student assignment will occur approximately 20 days before the semester starts.</p>';
        
        return $content;
    }
    
    /**
     * Create advisor record
     */
    private function createAdvisorRecord($input) {
        $table = $this->config['tables']['advisor'];
        $actionLog = $this->logger->formatAction('advisor record added');
        
        $data = array(
            'advisor_call_sign' => $input['callsign'],
            'advisor_semester' => $input['semester'],
            'advisor_welcome_email_date' => '',
            'advisor_verify_email_date' => '',
            'advisor_verify_response' => '',
            'advisor_verify_email_number' => 0,
            'advisor_class_verified' => 'N',
            'advisor_replacement_status' => 'N',
            'advisor_action_log' => $actionLog,
        );
        
        $formats = array('%s', '%s', '%s', '%s', '%s', '%d', '%s', '%s', '%s');
        
        $result = $this->wpdb->insert($table, $data, $formats);
        
        if ($result === false) {
            $this->logger->logDatabaseError('Creating advisor record', $input['callsign']);
            return false;
        }
        
        return $this->wpdb->insert_id;
    }
    
    /**
     * Create class record
     */
    private function createClassRecord($input, $userMaster, $sequence) {
        $table = $this->config['tables']['advisorClass'];
        
        // Get timezone offset
        $timezoneOffset = getOffsetFromIdentifier(
            $userMaster->user_timezone_id, 
            $input['semester'], 
            $this->config['doDebug']
        );
        
        if ($timezoneOffset === false) {
            $this->logger->logError("Failed to get timezone offset for {$userMaster->user_timezone_id}");
            $timezoneOffset = 0.00;
        }
        
        // Convert to UTC
        $utcConversion = $this->convertToUTC(
            $timezoneOffset,
            $input['times'],
            $input['teaching_days']
        );
        
        $actionLog = $this->logger->formatAction('class record added');
        
        $data = array(
            'advisorclass_call_sign' => $input['callsign'],
            'advisorclass_sequence' => $sequence,
            'advisorclass_semester' => $input['semester'],
            'advisorclass_timezone_offset' => $timezoneOffset,
            'advisorclass_level' => $input['level'],
            'advisorclass_class_size' => $input['class_size'] ? $input['class_size'] : self::DEFAULT_CLASS_SIZE,
            'advisorclass_language' => $input['language'],
            'advisorclass_class_schedule_days' => $input['teaching_days'],
            'advisorclass_class_schedule_times' => $input['times'],
            'advisorclass_class_schedule_days_utc' => $utcConversion['days'],
            'advisorclass_class_schedule_times_utc' => $utcConversion['times'],
            'advisorclass_action_log' => $actionLog,
            'advisorclass_class_incomplete' => 'N',
        );
        
        $formats = array('%s', '%d', '%s', '%f', '%s', '%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s');
        
        $result = $this->wpdb->insert($table, $data, $formats);
        
        if ($result === false) {
            $this->logger->logDatabaseError('Creating class record', $input['callsign']);
            return false;
        }
        
        return true;
    }
    
    /**
     * Convert schedule to UTC
     */
    private function convertToUTC($offset, $times, $days) {
        $result = utcConvert('toutc', $offset, $times, $days, $this->config['doDebug']);
        
        if ($result[0] === 'FAIL') {
            $this->logger->logError("UTC conversion failed: {$result[3]}");
            return array('times' => $times, 'days' => $days);
        }
        
        return array('times' => $result[1], 'days' => $result[2]);
    }
    
    /**
     * Check if advisor is also signed up as student
     */
    private function checkAdvisorStudentConflict($callsign, $semester) {
        $studentResult = get_student_last_class($callsign, $this->config['doDebug'], $this->config['testMode']);
        
        $studentSemester = isset($studentResult['Current']['Semester']) ? $studentResult['Current']['Semester'] : '';
        
        if ($studentSemester && $studentSemester === $semester) {
            // Send notification email
            $this->sendAdvisorStudentConflictEmail($callsign, $semester);
        }
    }
    
    /**
     * Handle add class form (pass 5)
     */
    private function handleAddClassForm($input) {
        $content = '<h3>Add a Class</h3>';
        
        // Get advisor and user master info
        $advisor = $this->getAdvisorWithUserMaster($input['callsign'], $input['semester']);
        
        if (!$advisor) {
            $this->logger->logError("No advisor found for {$input['callsign']} in {$input['semester']}");
            return $content . '<p>Advisor record not found.</p>';
        }
        
        // Render add class form
        $content .= $this->renderAddClassForm($advisor, $input['classcount']);
        
        return $content;
    }
    
    /**
     * Handle adding a class (pass 6)
     */
    private function handleAddClass($input) {
        $content = '<h3>Add Class</h3>';
        
        // Get user master for timezone
        $userMaster = $this->getUserMaster($input['callsign']);
        
        if (!$userMaster) {
            return $content . '<p>Error: User master record not found.</p>';
        }
        
        // Create the class
        $classCreated = $this->createClassRecord($input, $userMaster, $input['classcount']);
        
        if (!$classCreated) {
            return $content . '<p>An error occurred creating the class record.</p>';
        }
        
        $content .= '<p>Class added successfully.</p>';
        $content .= $this->renderAdvisorInfo($input['callsign'], $input['semester'], false);
        
        return $content;
    }
    
    /**
     * Handle edit class form (pass 15)
     */
    private function handleEditClassForm($input) {
        $content = '<h3>Edit Class</h3>';
        
        // Get the class
        $class = $this->getClassByID($input['classID']);
        
        if (!$class) {
            return $content . '<p>Class not found.</p>';
        }
        
        // Render edit form
        $content .= $this->renderEditClassForm($class);
        
        return $content;
    }
    
    /**
     * Handle updating a class (pass 16)
     */
    private function handleUpdateClass($input) {
        $content = '<h3>Update Class</h3>';
        
        // Get the class
        $class = $this->getClassByID($input['id']);
        
        if (!$class) {
            return $content . '<p>Class not found.</p>';
        }
        
        // Update the class
        $updated = $this->updateClassRecord($class, $input);
        
        if ($updated) {
            $content .= '<p>Class updated successfully.</p>';
        } else {
            $content .= '<p>No changes were made.</p>';
        }
        
        $content .= $this->renderAdvisorInfo($input['callsign'], $class->advisorclass_semester, false);
        
        return $content;
    }
    
    /**
     * Update class record
     */
    private function updateClassRecord($class, $input) {
        $table = $this->config['tables']['advisorClass'];
        $updates = array();
        $formats = array();
        $needsUTCUpdate = false;
        
        // Check each field for changes
        if ($input['level'] && $input['level'] !== $class->advisorclass_level) {
            $updates['advisorclass_level'] = $input['level'];
            $formats[] = '%s';
        }
        
        if ($input['language'] && $input['language'] !== $class->advisorclass_language) {
            $updates['advisorclass_language'] = $input['language'];
            $formats[] = '%s';
        }
        
        if ($input['class_size'] && $input['class_size'] != $class->advisorclass_class_size) {
            $updates['advisorclass_class_size'] = $input['class_size'];
            $formats[] = '%d';
        }
        
        if ($input['teaching_days'] && $input['teaching_days'] !== $class->advisorclass_class_schedule_days) {
            $updates['advisorclass_class_schedule_days'] = $input['teaching_days'];
            $formats[] = '%s';
            $needsUTCUpdate = true;
        }
        
        if ($input['times'] && $input['times'] !== $class->advisorclass_class_schedule_times) {
            $updates['advisorclass_class_schedule_times'] = $input['times'];
            $formats[] = '%s';
            $needsUTCUpdate = true;
        }
        
        // Update UTC times if needed
        if ($needsUTCUpdate) {
            $utc = $this->convertToUTC(
                $class->advisorclass_timezone_offset,
                $input['times'] ? $input['times'] : $class->advisorclass_class_schedule_times,
                $input['teaching_days'] ? $input['teaching_days'] : $class->advisorclass_class_schedule_days
            );
            $updates['advisorclass_class_schedule_times_utc'] = $utc['times'];
            $updates['advisorclass_class_schedule_days_utc'] = $utc['days'];
            $formats[] = '%s';
            $formats[] = '%s';
        }
        
        if (empty($updates)) {
            return false;
        }
        
        // Add action log
        $updates['advisorclass_action_log'] = $class->advisorclass_action_log . ' / ' . 
            $this->logger->formatAction('class updated');
        $formats[] = '%s';
        
        $result = $this->wpdb->update(
            $table,
            $updates,
            array('advisorclass_id' => $class->advisorclass_id),
            $formats,
            array('%d')
        );
        
        return $result !== false;
    }
    
    /**
     * Handle deleting advisor (pass 20)
     */
    private function handleDeleteAdvisor($input) {
        $content = '<h3>Delete Advisor</h3>';
        
        // Get advisor
        $advisor = $this->getAdvisor($input['callsign'], $input['semester']);
        
        if (!$advisor) {
            return $content . '<p>Advisor not found.</p>';
        }
        
        // Get all classes
        $classes = $this->getAdvisorClasses($input['callsign'], $input['semester']);
        
        // Unassign students from all classes
        foreach ($classes as $class) {
            if ($class->advisorclass_number_students > 0) {
                $this->unassignStudentsFromClass($class);
            }
            // Delete the class
            $this->deleteClass($class->advisorclass_id);
        }
        
        // Delete advisor
        $deleted = $this->deleteAdvisor($advisor->advisor_id);
        
        if ($deleted) {
            $content .= '<p>Advisor and all classes have been deleted successfully.</p>';
        } else {
            $content .= '<p>An error occurred during deletion.</p>';
        }
        
        return $content;
    }
    
    /**
     * Get advisor record
     */
    private function getAdvisor($callsign, $semester) {
        $table = $this->config['tables']['advisor'];
        
        $sql = $this->wpdb->prepare(
            "SELECT * FROM {$table} 
             WHERE advisor_call_sign = %s 
             AND advisor_semester = %s",
            $callsign,
            $semester
        );
        
        return $this->wpdb->get_row($sql);
    }
    
    /**
     * Get advisor with user master data
     */
    private function getAdvisorWithUserMaster($callsign, $semester) {
        $advisorTable = $this->config['tables']['advisor'];
        $userTable = $this->config['tables']['userMaster'];
        
        $sql = $this->wpdb->prepare(
            "SELECT * FROM {$advisorTable} 
             LEFT JOIN {$userTable} ON user_call_sign = advisor_call_sign 
             WHERE advisor_call_sign = %s 
             AND advisor_semester = %s",
            $callsign,
            $semester
        );
        
        return $this->wpdb->get_row($sql);
    }
    
    /**
     * Get all classes for an advisor
     */
    private function getAdvisorClasses($callsign, $semester) {
        $table = $this->config['tables']['advisorClass'];
        
        $sql = $this->wpdb->prepare(
            "SELECT * FROM {$table} 
             WHERE advisorclass_call_sign = %s 
             AND advisorclass_semester = %s 
             ORDER BY advisorclass_sequence",
            $callsign,
            $semester
        );
        
        return $this->wpdb->get_results($sql);
    }
    
    /**
     * Delete advisor
     */
    private function deleteAdvisor($advisorID) {
        $table = $this->config['tables']['advisor'];
        
        $result = $this->wpdb->delete(
            $table,
            array('advisor_id' => $advisorID),
            array('%d')
        );
        
        return $result !== false;
    }
    
    /**
     * Handle deleting a class (pass 17)
     */
    private function handleDeleteClass($input) {
        $content = '<h3>Delete Class</h3>';
        
        // Check if this is the only class
        $classCount = $this->getClassCount($input['callsign'], $input['semester']);
        
        if ($classCount === 1) {
            return $content . '<p>This is the only class for this advisor. You must delete the entire advisor record instead.</p>';
        }
        
        // Get the class record
        $class = $this->getClassByID($input['classID']);
        
        if (!$class) {
            $this->logger->logError("No class found with ID {$input['classID']}");
            return $content . '<p>Class not found. The system administrator has been notified.</p>';
        }
        
        // Unassign any students
        if ($class->advisorclass_number_students > 0) {
            $content .= '<p>Unassigning ' . $class->advisorclass_number_students . ' students...</p>';
            $this->unassignStudentsFromClass($class);
        }
        
        // Delete the class
        $deleted = $this->deleteClass($class->advisorclass_id);
        
        if (!$deleted) {
            return $content . '<p>An error occurred deleting the class.</p>';
        }
        
        $content .= '<p>Class deleted successfully.</p>';
        
        // Resequence remaining classes
        $this->resequenceClasses($input['callsign'], $input['semester']);
        
        // Display updated advisor info
        $content .= $this->renderAdvisorInfo($input['callsign'], $input['semester'], false);
        
        return $content;
    }
    
    /**
     * Get count of classes for advisor/semester
     */
    private function getClassCount($callsign, $semester) {
        $table = $this->config['tables']['advisorClass'];
        
        $sql = $this->wpdb->prepare(
            "SELECT COUNT(*) FROM {$table} 
             WHERE advisorclass_call_sign = %s 
             AND advisorclass_semester = %s",
            $callsign,
            $semester
        );
        
        return (int) $this->wpdb->get_var($sql);
    }
    
    /**
     * Get class by ID
     */
    private function getClassByID($classID) {
        $table = $this->config['tables']['advisorClass'];
        
        $sql = $this->wpdb->prepare(
            "SELECT * FROM {$table} WHERE advisorclass_id = %d",
            $classID
        );
        
        return $this->wpdb->get_row($sql);
    }
    
    /**
     * Unassign all students from a class
     */
    private function unassignStudentsFromClass($class) {
        for ($i = 1; $i <= 30; $i++) {
            $studentField = 'advisorclass_student' . str_pad($i, 2, '0', STR_PAD_LEFT);
            $studentCallsign = $class->$studentField;
            
            if ($studentCallsign) {
                $this->unassignStudent(
                    $studentCallsign,
                    $class->advisorclass_semester,
                    $class->advisorclass_call_sign,
                    $class->advisorclass_sequence
                );
            }
        }
    }
    
    /**
     * Unassign a student
     */
    private function unassignStudent($studentCallsign, $semester, $advisor, $sequence) {
        $data = array(
            'inp_student' => $studentCallsign,
            'inp_semester' => $semester,
            'inp_assigned_advisor' => $advisor,
            'inp_assigned_advisor_class' => $sequence,
            'inp_remove_status' => '',
            'inp_arbitrarily_assigned' => '',
            'inp_method' => 'remove',
            'jobname' => 'Advisor Registration',
            'userName' => $this->user['name'],
            'testMode' => $this->config['testMode'],
            'doDebug' => $this->config['doDebug'],
        );
        
        $result = add_remove_student($data);
        
        if ($result[0] === false) {
            $this->logger->logError("Failed to unassign student {$studentCallsign}: {$result[1]}");
        }
    }
    
    /**
     * Delete a class
     */
    private function deleteClass($classID) {
        $table = $this->config['tables']['advisorClass'];
        
        $result = $this->wpdb->delete(
            $table,
            array('advisorclass_id' => $classID),
            array('%d')
        );
        
        if ($result === false) {
            $this->logger->logDatabaseError('Deleting class', $classID);
            return false;
        }
        
        return true;
    }
    
    /**
     * Resequence classes after deletion
     */
    private function resequenceClasses($callsign, $semester) {
        $table = $this->config['tables']['advisorClass'];
        
        $sql = $this->wpdb->prepare(
            "SELECT advisorclass_id, advisorclass_sequence 
             FROM {$table} 
             WHERE advisorclass_call_sign = %s 
             AND advisorclass_semester = %s 
             ORDER BY advisorclass_sequence",
            $callsign,
            $semester
        );
        
        $classes = $this->wpdb->get_results($sql);
        
        $newSequence = 1;
        foreach ($classes as $class) {
            if ($class->advisorclass_sequence != $newSequence) {
                $this->wpdb->update(
                    $table,
                    array('advisorclass_sequence' => $newSequence),
                    array('advisorclass_id' => $class->advisorclass_id),
                    array('%d'),
                    array('%d')
                );
            }
            $newSequence++;
        }
    }
    
    /**
     * Render advisor information display
     */
    private function renderAdvisorInfo($callsign, $semester, $noUpdate = false) {
        $displayer = new CWA_Advisor_Display($this->config, $this->wpdb);
        return $displayer->render($callsign, $semester, $noUpdate);
    }
    
    /**
     * Render add class form
     */
    private function renderAddClassForm($advisor, $classcount) {
        $theURL = $this->config['siteURL'] . '/cwa-advisor-registration/';
        $languageOptions = $this->buildLanguageRadioButtons('English');
        
        return "<p>Enter the information for the class to be added.</p>
<form method='post' action='{$theURL}' name='class_add_form'>
    <input type='hidden' name='strpass' value='6'>
    <input type='hidden' name='inp_callsign' value='{$advisor->user_call_sign}'>
    <input type='hidden' name='classcount' value='{$classcount}'>
    <input type='hidden' name='inp_semester' value='{$advisor->advisor_semester}'>
    <table style='width:1000px;'>
        <tr>
            <td style='vertical-align:top;width:330px;'><b>Level</b><br />
                <input type='radio' class='formInputButton' name='inp_level' value='Beginner' required> Beginner<br />
                <input type='radio' class='formInputButton' name='inp_level' value='Fundamental' required> Fundamental<br />
                <input type='radio' class='formInputButton' name='inp_level' value='Intermediate' required> Intermediate<br />
                <input type='radio' class='formInputButton' name='inp_level' value='Advanced' required> Advanced
            </td>
            <td style='vertical-align:top;'><b>Language</b><br />{$languageOptions}</td>
            <td style='vertical-align:top;'><b>Class Size</b><br />
                <input type='text' class='formInputText' name='inp_class_size' size='5' maxlength='5' value='6'>
            </td>
        </tr>
        <tr>
            <td style='vertical-align:top;'><b>Class Teaching Days</b><br />
                <input type='radio' class='formInputButton' name='inp_teaching_days' value='Sunday,Wednesday' required> Sunday and Wednesday<br />
                <input type='radio' class='formInputButton' name='inp_teaching_days' value='Sunday,Thursday' required> Sunday and Thursday<br />
                <input type='radio' class='formInputButton' name='inp_teaching_days' value='Monday,Thursday' required checked> Monday and Thursday<br />
                <input type='radio' class='formInputButton' name='inp_teaching_days' value='Tuesday,Friday' required> Tuesday and Friday
            </td>
            <td style='vertical-align:top;' colspan='2'><b>Local Start Time</b><br />
                {$this->renderTimeSlots()}
            </td>
        </tr>
        <tr>
            <td>&nbsp;</td>
            <td><input class='formInputButton' type='submit' value='Add Class' /></td>
        </tr>
    </table>
</form>";
    }
    
    /**
     * Render edit class form
     */
    private function renderEditClassForm($class) {
        $theURL = $this->config['siteURL'] . '/cwa-advisor-registration/';
        $languageOptions = $this->buildLanguageRadioButtons($class->advisorclass_language);
        
        // Build checked values for level
        $levelChecked = array(
            'Beginner' => $class->advisorclass_level === 'Beginner' ? 'checked' : '',
            'Fundamental' => $class->advisorclass_level === 'Fundamental' ? 'checked' : '',
            'Intermediate' => $class->advisorclass_level === 'Intermediate' ? 'checked' : '',
            'Advanced' => $class->advisorclass_level === 'Advanced' ? 'checked' : '',
        );
        
        // Build checked values for days
        $daysChecked = array(
            'Sunday,Wednesday' => $class->advisorclass_class_schedule_days === 'Sunday,Wednesday' ? 'checked' : '',
            'Sunday,Thursday' => $class->advisorclass_class_schedule_days === 'Sunday,Thursday' ? 'checked' : '',
            'Monday,Thursday' => $class->advisorclass_class_schedule_days === 'Monday,Thursday' ? 'checked' : '',
            'Tuesday,Friday' => $class->advisorclass_class_schedule_days === 'Tuesday,Friday' ? 'checked' : '',
        );
        
        return "<p>Update the class information and submit.</p>
<form method='post' action='{$theURL}' name='class_edit_form'>
    <input type='hidden' name='strpass' value='16'>
    <input type='hidden' name='inp_callsign' value='{$class->advisorclass_call_sign}'>
    <input type='hidden' name='inp_id' value='{$class->advisorclass_id}'>
    <input type='hidden' name='inp_sequence' value='{$class->advisorclass_sequence}'>
    <table style='width:1000px;'>
        <tr>
            <td style='vertical-align:top;width:330px;'><b>Level</b><br />
                <input type='radio' class='formInputButton' name='inp_level' value='Beginner' {$levelChecked['Beginner']}> Beginner<br />
                <input type='radio' class='formInputButton' name='inp_level' value='Fundamental' {$levelChecked['Fundamental']}> Fundamental<br />
                <input type='radio' class='formInputButton' name='inp_level' value='Intermediate' {$levelChecked['Intermediate']}> Intermediate<br />
                <input type='radio' class='formInputButton' name='inp_level' value='Advanced' {$levelChecked['Advanced']}> Advanced
            </td>
            <td style='vertical-align:top;'><b>Language</b><br />{$languageOptions}</td>
            <td style='vertical-align:top;'><b>Class Size</b><br />
                <input type='text' class='formInputText' name='inp_class_size' size='5' maxlength='5' value='{$class->advisorclass_class_size}'>
            </td>
        </tr>
        <tr>
            <td style='vertical-align:top;'><b>Class Teaching Days</b><br />
                <input type='radio' class='formInputButton' name='inp_teaching_days' value='Sunday,Wednesday' {$daysChecked['Sunday,Wednesday']}> Sunday and Wednesday<br />
                <input type='radio' class='formInputButton' name='inp_teaching_days' value='Sunday,Thursday' {$daysChecked['Sunday,Thursday']}> Sunday and Thursday<br />
                <input type='radio' class='formInputButton' name='inp_teaching_days' value='Monday,Thursday' {$daysChecked['Monday,Thursday']}> Monday and Thursday<br />
                <input type='radio' class='formInputButton' name='inp_teaching_days' value='Tuesday,Friday' {$daysChecked['Tuesday,Friday']}> Tuesday and Friday
            </td>
            <td style='vertical-align:top;' colspan='2'><b>Local Start Time</b><br />
                {$this->renderTimeSlots($class->advisorclass_class_schedule_times)}
            </td>
        </tr>
        <tr>
            <td>&nbsp;</td>
            <td><input class='formInputButton' type='submit' value='Update Class' /></td>
        </tr>
    </table>
</form>";
    }
    
    /**
     * Render maintenance mode message
     */
    private function renderMaintenanceMode() {
        return '<p><b>The Advisor Sign-up process is currently undergoing maintenance. Please try again in about an hour.</b></p>';
    }
    
    /**
     * Render verification refused message
     */
    private function renderVerificationRefused() {
        return '<p>You need to contact the appropriate person at <a href="https://cwops.org/cwa-class-resolution/" target="_blank">CWA Class Resolution</a></p>';
    }
    
    /**
     * Render contact resolution message
     */
    private function renderContactResolution($reason) {
        return "<p>Due to {$reason}, you need to contact <a href='https://cwops.org/cwa-class-resolution/' target='_blank'>CWA Class Resolution</a> before signing up as an advisor.</p>";
    }
    
    /**
     * Render evaluations incomplete message
     */
    private function renderEvaluationsIncomplete($semester) {
        return "<p>You need to complete the promotability evaluations for your students in the {$semester} semester before you can register for a future semester.</p>";
    }
    
    /**
     * Render timezone warning
     */
    private function renderTimezoneWarning($callsign) {
        $updateURL = $this->config['siteURL'] . "/cwa-display-and-update-user-master-information/?strpass=2&request_type=callsign&request_info={$callsign}";
        
        return "<p><b>URGENT:</b> Please go to <a href=\"{$updateURL}\" target=\"_blank\">Update User Master Data</a> 
to determine the correct timezone identifier. Until this is done, you cannot complete the signup process.</p>
<p>After updating your timezone, please restart the sign-up process.</p>";
    }
    
    /**
     * Render test mode options for admins
     */
    private function renderTestModeOptions() {
        if (!in_array($this->user['name'], $this->config['validTestmode'])) {
            return '';
        }
        
        return "<tr>
    <td>Operation Mode</td>
    <td>
        <input type=\"radio\" name=\"inp_mode\" value=\"Production\" checked> Production<br>
        <input type=\"radio\" name=\"inp_mode\" value=\"TESTMODE\"> Test Mode
    </td>
</tr>
<tr>
    <td>Verbose Debugging?</td>
    <td>
        <input type=\"radio\" name=\"inp_verbose\" value=\"N\" checked> Standard Output<br>
        <input type=\"radio\" name=\"inp_verbose\" value=\"Y\"> Turn on Debugging
    </td>
</tr>";
    }
    
    /**
     * Add footer with execution time and logging
     */
    private function addFooter($startTime, $pass) {
        $endTime = microtime(true);
        $elapsed = number_format($endTime - $startTime, 4);
        
        // Log to joblog
        $this->logger->logJobExecution('Advisor Registration', $pass, $elapsed, $this->user['name']);
        
        $timestamp = date('Y-m-d H:i:s', $this->config['currentTimestamp']);
        
        return "<br><br>
<p>Report pass {$pass} took {$elapsed} seconds to run</p>
<p>Prepared at {$timestamp}</p>";
    }
    
    /**
     * Get user IP address
     */
    private function getUserIP() {
        return get_the_user_ip();
    }
    
    /**
     * Send email notification for advisor/student conflict
     */
    private function sendAdvisorStudentConflictEmail($callsign, $semester) {
        emailFromCWA_v2(array(
            'theRecipient' => '',
            'theSubject' => 'CW Academy - Advisor Is Also a Student',
            'jobname' => 'Advisor Registration',
            'theContent' => "Advisor {$callsign} is also registered as a student in {$semester} semester",
            'mailCode' => 18,
            'increment' => 0,
            'testMode' => $this->config['testMode'],
            'doDebug' => $this->config['doDebug'],
        ));
    }
}
