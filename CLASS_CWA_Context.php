/** CLASS CWA_Context

	Replacement for the dataInitializationFunction
	
*/

class CWA_Context {
    
    private static $instance = null;
    
    // Flags for lazy loading
    private $userComputed = false;
    private $semesterComputed = false;
    private $configLoaded = false;
    
    // User properties
    protected $userName;
    protected $userRole;
    protected $userID;
    protected $userEmail;
    protected $userDisplayName;
    protected $validUser;
    protected $isAdmin;
    
    // Semester properties
    protected $currentSemester;
    protected $nextSemester;
    protected $prevSemester;
    protected $semesterTwo;
    protected $semesterThree;
    protected $semesterFour;
    protected $proximateSemester;
    protected $daysToSemester;
    protected $validEmailPeriod;
    protected $validReplacementPeriod;
    
    // Date properties
    protected $currentTimestamp;
    protected $currentDate;
    protected $currentDateTime;
    
    // Config properties (from database)
    protected $validUsers;
    protected $validTestmode;
    protected $pastSemestersArray;
    protected $defaultClassSize;
    protected $languageArray;
    protected $languageConversion;
    
    // Site properties
    protected $siteurl;
    
    // DAL reference
    private $config_dal;
    
    /**
     * Get singleton instance
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Reset instance (useful for testing)
     */
    public static function resetInstance() {
        self::$instance = null;
    }
    
    /**
     * Private constructor
     */
    private function __construct() {
        $this->config_dal = new CWA_Config_DAL();
        $this->computeDateData();
        $this->computeSiteData();
    }
    
    /**
     * Magic getter for lazy loading
     */
    public function __get($name) {
        // User properties
        $userProps = array(
            'userName', 'userRole', 'userID', 'userEmail', 
            'userDisplayName', 'validUser', 'isAdmin'
        );
        
        // Semester properties
        $semesterProps = array(
            'currentSemester', 'nextSemester', 'prevSemester',
            'semesterTwo', 'semesterThree', 'semesterFour',
            'proximateSemester', 'daysToSemester',
            'validEmailPeriod', 'validReplacementPeriod'
        );
        
        // Config properties (from database)
        $configProps = array(
            'validUsers', 'validTestmode', 'pastSemestersArray',
            'defaultClassSize', 'languageArray', 'languageConversion'
        );
        
        if (in_array($name, $userProps) && !$this->userComputed) {
            $this->computeUserData();
        }
        
        if (in_array($name, $semesterProps) && !$this->semesterComputed) {
            $this->computeSemesterData();
        }
        
        if (in_array($name, $configProps) && !$this->configLoaded) {
            $this->loadConfigData();
        }
        
        return $this->$name ?? null;
    }
    
    /**
     * Compute date data (always runs - cheap)
     */
    private function computeDateData() {
        $this->currentDateTime = current_time('mysql', 1);
        $this->currentTimestamp = strtotime($this->currentDateTime);
        $this->currentDate = date('Y-m-d', $this->currentTimestamp);
    }
    
    /**
     * Compute site data (always runs - cheap)
     */
    private function computeSiteData() {
        $this->siteurl = get_site_url();
    }
    
    /**
     * Load config data from database
     */
    private function loadConfigData() {
        $this->validUsers = $this->config_dal->get('valid_users', array());
        $this->validTestmode = $this->config_dal->get('valid_testmode', array());
        $this->pastSemestersArray = $this->config_dal->get('past_semesters_array', array());
        $this->defaultClassSize = $this->config_dal->get('default_class_size', 6);
        $this->languageArray = $this->config_dal->get('language_array', array('English'));
        $this->languageConversion = $this->config_dal->get('language_conversion', array());
        
        $this->configLoaded = true;
    }
    
    /**
     * Compute user data from WordPress
     */
    private function computeUserData() {
        $current_user = wp_get_current_user();
        
        $this->userName = strtoupper(trim($current_user->user_login));
        $this->userEmail = $current_user->user_email;
        $this->userDisplayName = $current_user->display_name;
        $this->userID = get_current_user_id();
        
        // Determine role
        if (in_array('administrator', (array)$current_user->roles)) {
            $this->userRole = 'administrator';
        } elseif (in_array('advisor', (array)$current_user->roles)) {
            $this->userRole = 'advisor';
        } elseif (in_array('student', (array)$current_user->roles)) {
            $this->userRole = 'student';
        } else {
            $this->userRole = '';
        }
        
        $this->isAdmin = ($this->userRole === 'administrator');
        
        // Check if valid user (ensure config is loaded)
        if (!$this->configLoaded) {
            $this->loadConfigData();
        }
        $this->validUser = in_array($this->userName, $this->validUsers) ? 'Y' : 'N';
        
        $this->userComputed = true;
    }
    
    /**
     * Compute semester data
     */
    private function computeSemesterData() {
        $myInterim = $this->currentTimestamp;
        $myDate = $this->currentDate;
        
        $currentYear = date('Y', $myInterim);
        $currentMonth = date('m', $myInterim);
        $newYear = date('Y', strtotime("$myDate + 1 year"));
        $newNewYear = date('Y', strtotime("$myDate + 2 years"));
        $prevYear = date('Y', strtotime("$myDate - 1 year"));
        
        // Set current semester and previous semester
        switch ($currentMonth) {
            case '01':
            case '02':
                $this->currentSemester = "$currentYear Jan/Feb";
                $this->prevSemester = "$prevYear Sep/Oct";
                break;
            case '03':
            case '04':
                $this->currentSemester = "Not in Session";
                $this->prevSemester = "$currentYear Jan/Feb";
                break;
            case '05':
            case '06':
                $this->currentSemester = "$currentYear May/Jun";
                $this->prevSemester = "$currentYear Jan/Feb";
                break;
            case '07':
            case '08':
                $this->currentSemester = "Not in Session";
                $this->prevSemester = "$currentYear May/Jun";
                break;
            case '09':
            case '10':
                $this->currentSemester = "$currentYear Sep/Oct";
                $this->prevSemester = "$currentYear May/Jun";
                break;
            case '11':
            case '12':
                $this->currentSemester = "Not in Session";
                $this->prevSemester = "$currentYear Sep/Oct";
                break;
        }
        
        // Set next semesters
        $monthArray = array(
            '01' => 'A', '02' => 'A', '03' => 'A', '04' => 'A',
            '05' => 'B', '06' => 'B', '07' => 'B', '08' => 'B',
            '09' => 'C', '10' => 'C', '11' => 'C', '12' => 'C'
        );
        $semesterType = $monthArray[$currentMonth];
        
        switch ($semesterType) {
            case 'A':
                $this->nextSemester = "$currentYear May/Jun";
                $this->semesterTwo = "$currentYear Sep/Oct";
                $this->semesterThree = "$newYear Jan/Feb";
                $this->semesterFour = "$newYear May/Jun";
                $nextSemesterDate = "$currentYear-05-01";
                break;
            case 'B':
                $this->nextSemester = "$currentYear Sep/Oct";
                $this->semesterTwo = "$newYear Jan/Feb";
                $this->semesterThree = "$newYear May/Jun";
                $this->semesterFour = "$newYear Sep/Oct";
                $nextSemesterDate = "$currentYear-09-01";
                break;
            case 'C':
                $this->nextSemester = "$newYear Jan/Feb";
                $this->semesterTwo = "$newYear May/Jun";
                $this->semesterThree = "$newYear Sep/Oct";
                $this->semesterFour = "$newNewYear Jan/Feb";
                $nextSemesterDate = "$newYear-01-01";
                break;
        }
        
        // Days to semester
        $nextSemesterStamp = strtotime($nextSemesterDate);
        $timeDiff = $nextSemesterStamp - $myInterim;
        $this->daysToSemester = intval(round($timeDiff / 86400));
        
        // Proximate semester
        $this->proximateSemester = ($this->currentSemester === 'Not in Session') 
            ? $this->nextSemester 
            : $this->currentSemester;
        
        // Valid email period (3/15-4/10, 7/15-8/10, 11/15-12/10)
        $currentMonthDay = date('md', $myInterim);
        $this->validEmailPeriod = 'N';
        if (($currentMonthDay >= '0315' && $currentMonthDay <= '0410') ||
            ($currentMonthDay >= '0715' && $currentMonthDay <= '0810') ||
            ($currentMonthDay >= '1115' && $currentMonthDay <= '1210')) {
            $this->validEmailPeriod = 'Y';
        }
        
        // Valid replacement period (4/10-5/10, 8/10-9/10, 12/10-1/10)
        $currentYMD = date('Ymd', $myInterim);
        $this->validReplacementPeriod = 'N';
        
        $apr10 = $currentYear . '0410';
        $may10 = $currentYear . '0510';
        $aug10 = $currentYear . '0810';
        $sep10 = $currentYear . '0910';
		$dec10 = $prevYear . '1210';
        $jan10 = $currentYear . '0110';
        
        if (($currentYMD >= $apr10 && $currentYMD < $may10) ||
            ($currentYMD >= $aug10 && $currentYMD < $sep10) ||
            ($currentYMD >= $dec10 && $currentYMD < $jan10)) {
            $this->validReplacementPeriod = 'Y';
        }
        
        $this->semesterComputed = true;
    }
    
    /**
     * Get all data as array (for backward compatibility)
     */
    public function toArray() {
        // Force all computations
        if (!$this->userComputed) {
            $this->computeUserData();
        }
        if (!$this->semesterComputed) {
            $this->computeSemesterData();
        }
        if (!$this->configLoaded) {
            $this->loadConfigData();
        }
        
        // Build pastSemesters string from array
        $pastSemesters = implode('|', $this->pastSemestersArray);
        
        return array(
            'validUser' => $this->validUser,
            'userRole' => $this->userRole,
            'userName' => $this->userName,
            'userID' => $this->userID,
            'userEmail' => $this->userEmail,
            'userDisplayName' => $this->userDisplayName,
            'currentTimestamp' => $this->currentTimestamp,
            'currentDateTime' => $this->currentDateTime,
            'currentDate' => $this->currentDate,
            'prevSemester' => $this->prevSemester,
            'currentSemester' => $this->currentSemester,
            'nextSemester' => $this->nextSemester,
            'semesterTwo' => $this->semesterTwo,
            'semesterThree' => $this->semesterThree,
            'semesterFour' => $this->semesterFour,
            'proximateSemester' => $this->proximateSemester,
            'pastSemesters' => $pastSemesters,
            'pastSemestersArray' => $this->pastSemestersArray,
            'validEmailPeriod' => $this->validEmailPeriod,
            'validReplacementPeriod' => $this->validReplacementPeriod,
            'daysToSemester' => $this->daysToSemester,
            'defaultClassSize' => $this->defaultClassSize,
            'validTestmode' => $this->validTestmode,
            'siteurl' => $this->siteurl,
            'languageArray' => $this->languageArray,
            'languageConversion' => $this->languageConversion,
        );
    }
}
