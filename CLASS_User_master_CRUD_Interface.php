/**
 * CWA User Master CRUD Interface
 * 
 * Provides a user interface for Create, Read, Update, and Delete operations
 * on the user_master table using the CWA_User_Master_DAL.
 * 
 * Access levels:
 * - Admin users (user_is_admin = 'Y'): Full CRUD access to all records
 * - Non-admin users: Can only view/edit their own record (no delete)
 */

class CWA_User_Master_CRUD {
    
    private $dal;
    private $config;
    private $user;
    private $display;
    
    // Job name for logging
    private $jobname = 'User Master CRUD';
    
    // Constants for pass flow
    const PASS_LIST = '1';
    const PASS_VIEW = '2';
    const PASS_CREATE_FORM = '3';
    const PASS_CREATE_SAVE = '4';
    const PASS_EDIT_FORM = '5';
    const PASS_EDIT_SAVE = '6';
    const PASS_DELETE_CONFIRM = '7';
    const PASS_DELETE_EXECUTE = '8';
    const PASS_SEARCH = '9';
    const PASS_EXTERNAL_ENTRY = '10';
    const PASS_EXTERNAL_EDIT_SAVE = '11';
    
    // Field definitions for forms
    private $field_definitions = array(
        'user_call_sign' => array(
            'label' => 'Call Sign',
            'type' => 'text',
            'required' => true,
            'maxlength' => 25,
            'sanitize' => 'callsign'
        ),
        'user_first_name' => array(
            'label' => 'First Name',
            'type' => 'text',
            'required' => false,
            'maxlength' => 30,
            'sanitize' => 'text'
        ),
        'user_last_name' => array(
            'label' => 'Last Name',
            'type' => 'text',
            'required' => false,
            'maxlength' => 50,
            'sanitize' => 'text'
        ),
        'user_email' => array(
            'label' => 'Email',
            'type' => 'email',
            'required' => false,
            'maxlength' => 50,
            'sanitize' => 'email'
        ),
        'user_ph_code' => array(
            'label' => 'Phone Code',
            'type' => 'text',
            'required' => true,
            'maxlength' => 8,
            'default' => '1',
            'sanitize' => 'text'
        ),
        'user_phone' => array(
            'label' => 'Phone Number',
            'type' => 'text',
            'required' => false,
            'maxlength' => 20,
            'sanitize' => 'text'
        ),
        'user_city' => array(
            'label' => 'City',
            'type' => 'text',
            'required' => false,
            'maxlength' => 30,
            'sanitize' => 'text'
        ),
        'user_state' => array(
            'label' => 'State/Province',
            'type' => 'text',
            'required' => false,
            'maxlength' => 60,
            'sanitize' => 'text'
        ),
        'user_zip_code' => array(
            'label' => 'Zip/Postal Code',
            'type' => 'text',
            'required' => false,
            'maxlength' => 20,
            'sanitize' => 'text'
        ),
        'user_country_code' => array(
            'label' => 'Country Code',
            'type' => 'text',
            'required' => false,
            'maxlength' => 5,
            'sanitize' => 'text'
        ),
        'user_country' => array(
            'label' => 'Country',
            'type' => 'text',
            'required' => true,
            'maxlength' => 50,
            'default' => 'United States',
            'sanitize' => 'text'
        ),
        'user_whatsapp' => array(
            'label' => 'WhatsApp',
            'type' => 'text',
            'required' => false,
            'maxlength' => 20,
            'sanitize' => 'text'
        ),
        'user_telegram' => array(
            'label' => 'Telegram',
            'type' => 'text',
            'required' => false,
            'maxlength' => 20,
            'sanitize' => 'text'
        ),
        'user_signal' => array(
            'label' => 'Signal',
            'type' => 'text',
            'required' => false,
            'maxlength' => 20,
            'sanitize' => 'text'
        ),
        'user_messenger' => array(
            'label' => 'Messenger',
            'type' => 'text',
            'required' => false,
            'maxlength' => 20,
            'sanitize' => 'text'
        ),
        'user_timezone_id' => array(
            'label' => 'Timezone ID',
            'type' => 'text',
            'required' => true,
            'maxlength' => 50,
            'default' => 'XX',
            'sanitize' => 'text'
        ),
        'user_languages' => array(
            'label' => 'Languages',
            'type' => 'textarea',
            'required' => false,
            'sanitize' => 'textarea'
        ),
        'user_survey_score' => array(
            'label' => 'Survey Score',
            'type' => 'number',
            'required' => true,
            'default' => '0',
            'min' => 0,
            'max' => 127,
            'sanitize' => 'int'
        ),
        'user_is_admin' => array(
            'label' => 'Is Admin',
            'type' => 'select',
            'required' => false,
            'options' => array(
                '' => 'No',
                'Y' => 'Yes'
            ),
            'sanitize' => 'text'
        ),
        'user_role' => array(
            'label' => 'Role',
            'type' => 'select',
            'required' => false,
            'options' => array(
                '' => '-- Select Role --',
                'student' => 'Student',
                'advisor' => 'Advisor',
                'admin' => 'Administrator'
            ),
            'sanitize' => 'text'
        ),
        'user_prev_callsign' => array(
            'label' => 'Previous Callsign(s)',
            'type' => 'textarea',
            'required' => false,
            'sanitize' => 'textarea'
        ),
        'user_action_log' => array(
            'label' => 'Action Log',
            'type' => 'textarea',
            'required' => false,
            'readonly' => true,
            'sanitize' => 'textarea'
        )
    );
    
    // Fields that non-admin users cannot see or edit in external entry mode
    private $admin_only_fields = array(
        'user_timezone_id',
        'user_survey_score',
        'user_is_admin',
        'user_role',
        'user_prev_callsign',
        'user_action_log'
    );
    
    public function __construct() {
        $this->dal = new CWA_User_Master_DAL();
        $this->display = new CWA_User_Master_Display();
        $this->initializeConfig();
        $this->initializeUser();
    }
    
    /**
     * Initialize configuration
     */
    private function initializeConfig() {
        $initData = data_initialization_func();
        
        $this->config = array(
            'testMode' => false,
            'doDebug' => false,
            'validTestmode' => $initData['validTestmode'],
            'siteURL' => $initData['siteurl'],
            'currentTimestamp' => $initData['currentTimestamp'],
        );
    }
    
    /**
     * Initialize user including admin status from user_master
     */
    private function initializeUser() {
        $initData = data_initialization_func();
        $mode = $this->config['testMode'] ? 'Testing' : 'Production';
        
        $this->user = array(
            'name' => $initData['userName'],
            'role' => $initData['userRole'],
            'ipAddress' => $this->getUserIP(),
            'callSign' => $initData['userName'],
            'isAdmin' => false,
            'userId' => null,
        );
        
        // Look up user's admin status from user_master table
        $userRecord = $this->dal->get_user_master_by_callsign($this->user['callSign'], $mode);
        
        if ($userRecord && count($userRecord) > 0) {
            $this->user['isAdmin'] = ($userRecord[0]['user_is_admin'] === 'Y');
            $this->user['userId'] = $userRecord[0]['user_ID'];
        }
    }
    
    /**
     * Main handler
     */
    public function handle() {
        $startTime = microtime(true);
        
        // Handle test mode
        $this->handleTestMode();
        
        // Get input
        $input = $this->getInput();
        
        // Check authorization based on pass and user status
        $authResult = $this->checkAuthorization($input);
        if ($authResult !== true) {
            return $authResult;
        }
        
        // Route to appropriate handler
        $content = $this->routeRequest($input);
        
        // Add footer with joblog (skip for external entry completion messages)
        if (!in_array($input['strpass'], array(self::PASS_EXTERNAL_EDIT_SAVE))) {
            $content .= $this->addFooter($startTime, $input['strpass']);
        }
        
        return $content;
    }
    
    /**
     * Check authorization based on pass type and user status
     * 
     * @param array $input The sanitized input array
     * @return true|string Returns true if authorized, or error message HTML if not
     */
    private function checkAuthorization($input) {
        $pass = $input['strpass'];
        
        // External entry passes - any logged-in user can access their own record
        if (in_array($pass, array(self::PASS_EXTERNAL_ENTRY, self::PASS_EXTERNAL_EDIT_SAVE))) {
            if (empty($this->user['callSign'])) {
                return '<p>You must be logged in to access this page.</p>';
            }
            if ($this->user['userId'] === null) {
                return '<p>Your user record was not found. Please contact an administrator.</p>';
            }
            return true;
        }
        
        // Admin-only passes - require user_is_admin = 'Y'
        if (!$this->user['isAdmin']) {
            return '<p>You are not authorized to access this page. Admin privileges required.</p>';
        }
        
        return true;
    }
    
    /**
     * Handle test mode
     */
    private function handleTestMode() {
        if (isset($_REQUEST['inp_mode']) && 
            $_REQUEST['inp_mode'] === 'TESTMODE' && 
            in_array($this->user['name'], $this->config['validTestmode'])) {
            $this->config['testMode'] = true;
        }
        
        if (isset($_REQUEST['inp_verbose']) && $_REQUEST['inp_verbose'] === 'Y') {
            $this->config['doDebug'] = true;
        }
    }
    
    /**
     * Get and sanitize input
     */
    private function getInput() {
        return array(
            'strpass' => $this->sanitize(isset($_REQUEST['strpass']) ? $_REQUEST['strpass'] : self::PASS_LIST, 'string'),
            'user_id' => $this->sanitize(isset($_REQUEST['user_id']) ? $_REQUEST['user_id'] : 0, 'int'),
            'search_field' => $this->sanitize(isset($_REQUEST['search_field']) ? $_REQUEST['search_field'] : '', 'string'),
            'search_value' => $this->sanitize(isset($_REQUEST['search_value']) ? $_REQUEST['search_value'] : '', 'string'),
            'orderby' => $this->sanitize(isset($_REQUEST['orderby']) ? $_REQUEST['orderby'] : 'user_call_sign', 'string'),
            'order' => $this->sanitize(isset($_REQUEST['order']) ? $_REQUEST['order'] : 'ASC', 'string'),
        );
    }
    
    /**
     * Sanitize input
     */
    private function sanitize($value, $type = 'string') {
        switch ($type) {
            case 'callsign':
                return strtoupper(sanitize_text_field($value));
            case 'email':
                return sanitize_email($value);
            case 'int':
                return intval($value);
            case 'textarea':
                return sanitize_textarea_field($value);
            case 'string':
            default:
                return sanitize_text_field($value);
        }
    }
    
    /**
     * Route request to appropriate handler
     */
    private function routeRequest($input) {
        switch ($input['strpass']) {
            case self::PASS_LIST:
                return $this->handleList($input);
            
            case self::PASS_VIEW:
                return $this->handleView($input);
            
            case self::PASS_CREATE_FORM:
                return $this->handleCreateForm($input);
            
            case self::PASS_CREATE_SAVE:
                return $this->handleCreateSave($input);
            
            case self::PASS_EDIT_FORM:
                return $this->handleEditForm($input);
            
            case self::PASS_EDIT_SAVE:
                return $this->handleEditSave($input);
            
            case self::PASS_DELETE_CONFIRM:
                return $this->handleDeleteConfirm($input);
            
            case self::PASS_DELETE_EXECUTE:
                return $this->handleDeleteExecute($input);
            
            case self::PASS_SEARCH:
                return $this->handleSearch($input);
            
            case self::PASS_EXTERNAL_ENTRY:
                return $this->handleExternalEntry($input);
            
            case self::PASS_EXTERNAL_EDIT_SAVE:
                return $this->handleExternalEditSave($input);
            
            default:
                return '<p>Invalid request.</p>';
        }
    }
    
    /**
     * Handle list view
     */
    private function handleList($input) {
        $mode = $this->config['testMode'] ? 'Testing' : 'Production';
        $theURL = $this->config['siteURL'] . '/cwa-display-and-update-user-master-information/';
        
        // Get all records
        $records = $this->dal->get_user_master(
            array(),
            $input['orderby'],
            $input['order'],
            $mode
        );
        
        $content = "<h3>User Master Records</h3>";
        
        // Add search form
        $content .= $this->renderSearchForm($theURL);
        
        // Add create button
        $content .= "<p><a href='{$theURL}?strpass=" . self::PASS_CREATE_FORM . "' class='formInputButton'>Create New User</a></p>";
        
        // Render table
        if ($records && count($records) > 0) {
            $content .= $this->renderTable($records, $theURL, $input);
        } else {
            $content .= "<p>No records found.</p>";
        }
        
        return $content;
    }
    
    /**
     * Render search form
     */
    private function renderSearchForm($theURL) {
        $searchFields = array(
            'user_call_sign' => 'Call Sign',
            'user_email' => 'Email',
            'user_last_name' => 'Last Name',
            'user_city' => 'City',
            'user_state' => 'State'
        );
        
        $options = '';
        foreach ($searchFields as $field => $label) {
            $options .= "<option value='{$field}'>{$label}</option>";
        }
        
        return "<form method='get' action='{$theURL}'>
            <input type='hidden' name='strpass' value='" . self::PASS_SEARCH . "'>
            <table style='border-collapse:collapse;'>
                <tr>
                    <td>Search Field:</td>
                    <td>
                        <select name='search_field' class='formSelect'>
                            {$options}
                        </select>
                    </td>
                </tr>
                <tr>
                    <td>Search Value:</td>
                    <td><input type='text' name='search_value' class='formInputText' required></td>
                </tr>
                <tr>
                    <td>&nbsp;</td>
                    <td><input type='submit' value='Search' class='formInputButton'></td>
                </tr>
            </table>
        </form>";
    }
    
    /**
     * Render records table
     */
    private function renderTable($records, $theURL, $input) {
        // Create sort links
        $orderToggle = $input['order'] === 'ASC' ? 'DESC' : 'ASC';
        
        $content = "<table style='width:100%; border-collapse:collapse;'>
            <thead>
                <tr>
                    <th><a href='{$theURL}?strpass=" . self::PASS_LIST . "&orderby=user_ID&order={$orderToggle}'>ID</a></th>
                    <th><a href='{$theURL}?strpass=" . self::PASS_LIST . "&orderby=user_call_sign&order={$orderToggle}'>Call Sign</a></th>
                    <th><a href='{$theURL}?strpass=" . self::PASS_LIST . "&orderby=user_last_name&order={$orderToggle}'>Name</a></th>
                    <th><a href='{$theURL}?strpass=" . self::PASS_LIST . "&orderby=user_email&order={$orderToggle}'>Email</a></th>
                    <th><a href='{$theURL}?strpass=" . self::PASS_LIST . "&orderby=user_city&order={$orderToggle}'>City</a></th>
                    <th><a href='{$theURL}?strpass=" . self::PASS_LIST . "&orderby=user_state&order={$orderToggle}'>State</a></th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>";
        
        foreach ($records as $record) {
            $id = $record['user_ID'];
            $callsign = esc_html($record['user_call_sign']);
            $name = esc_html($record['user_last_name'] . ', ' . $record['user_first_name']);
            $email = esc_html($record['user_email']);
            $city = esc_html($record['user_city']);
            $state = esc_html($record['user_state']);
            
            $content .= "<tr>
                <td>{$id}</td>
                <td>{$callsign}</td>
                <td>{$name}</td>
                <td>{$email}</td>
                <td>{$city}</td>
                <td>{$state}</td>
                <td>
                    <a href='{$theURL}?strpass=" . self::PASS_VIEW . "&user_id={$id}'>View</a> | 
                    <a href='{$theURL}?strpass=" . self::PASS_EDIT_FORM . "&user_id={$id}'>Edit</a> | 
                    <a href='{$theURL}?strpass=" . self::PASS_DELETE_CONFIRM . "&user_id={$id}'>Delete</a>
                </td>
            </tr>";
        }
        
        $content .= "</tbody></table>";
        
        return $content;
    }
    
    /**
     * Handle view single record - uses CWA_User_Master_Display
     */
    private function handleView($input) {
        $theURL = $this->config['siteURL'] . '/cwa-display-and-update-user-master-information/';
        
        $content = "<p><a href='{$theURL}?strpass=" . self::PASS_LIST . "'>Back to List</a> | ";
        $content .= "<a href='{$theURL}?strpass=" . self::PASS_EDIT_FORM . "&user_id={$input['user_id']}'>Edit</a></p>";
        
        // Use the standard display class
        $content .= $this->display->render($input['user_id']);
        
        return $content;
    }
    
    /**
     * Handle create form
     */
    private function handleCreateForm($input) {
        $theURL = $this->config['siteURL'] . '/cwa-display-and-update-user-master-information/';
        
        $content = "<h3>Create New User Master Record</h3>";
        $content .= "<p><a href='{$theURL}?strpass=" . self::PASS_LIST . "'>Back to List</a></p>";
        
        $content .= "<form method='post' action='{$theURL}'>
            <input type='hidden' name='strpass' value='" . self::PASS_CREATE_SAVE . "'>
            <table style='width:900px;'>";
        
        foreach ($this->field_definitions as $field => $def) {
            // Skip auto-generated and readonly fields
            if ($field === 'user_action_log') {
                continue;
            }
            
            $content .= $this->renderFormField($field, $def, array());
        }
        
        $content .= "<tr><td>&nbsp;</td><td><input type='submit' value='Create User' class='formInputButton'></td></tr>";
        $content .= "</table></form>";
        
        return $content;
    }
    
    /**
     * Handle create save
     */
    private function handleCreateSave($input) {
        $mode = $this->config['testMode'] ? 'Testing' : 'Production';
        $theURL = $this->config['siteURL'] . '/cwa-display-and-update-user-master-information/';
        
        // Collect and sanitize data
        $data = $this->collectFormData();
        
        // Add action log
        $actionDate = date('dMy H:i', $this->config['currentTimestamp']);
        $data['user_action_log'] = "{$actionDate} CRUD Record created by {$this->user['name']}";
        
        // Insert record
        $newId = $this->dal->insert($data, $mode);
        
        if ($newId) {
            $content = "<h3>Success</h3>";
            $content .= "<p>User record created successfully with ID: {$newId}</p>";
            $content .= "<p><a href='{$theURL}?strpass=" . self::PASS_VIEW . "&user_id={$newId}'>View Record</a> | ";
            $content .= "<a href='{$theURL}?strpass=" . self::PASS_LIST . "'>Back to List</a></p>";
        } else {
            $content = "<h3>Error</h3>";
            $content .= "<p>Failed to create user record.</p>";
            $content .= "<p><a href='{$theURL}?strpass=" . self::PASS_CREATE_FORM . "'>Try Again</a> | ";
            $content .= "<a href='{$theURL}?strpass=" . self::PASS_LIST . "'>Back to List</a></p>";
        }
        
        return $content;
    }
    
    /**
     * Handle edit form
     */
    private function handleEditForm($input) {
        $mode = $this->config['testMode'] ? 'Testing' : 'Production';
        $theURL = $this->config['siteURL'] . '/cwa-display-and-update-user-master-information/';
        
        $records = $this->dal->get_user_master_by_id($input['user_id'], $mode);
        
        if (!$records || count($records) === 0) {
            return "<h3>Edit User Master Record</h3><p>Record not found.</p>";
        }
        
        $record = $records[0];
        
        $content = "<h3>Edit User Master Record - {$record['user_call_sign']}</h3>";
        $content .= "<p><a href='{$theURL}?strpass=" . self::PASS_LIST . "'>Back to List</a></p>";
        
        $content .= "<form method='post' action='{$theURL}'>
            <input type='hidden' name='strpass' value='" . self::PASS_EDIT_SAVE . "'>
            <input type='hidden' name='user_id' value='{$input['user_id']}'>
            <table style='width:900px;'>";
        
        foreach ($this->field_definitions as $field => $def) {
            $content .= $this->renderFormField($field, $def, $record);
        }
        
        $content .= "<tr><td>&nbsp;</td><td><input type='submit' value='Update User' class='formInputButton'></td></tr>";
        $content .= "</table></form>";
        
        return $content;
    }
    
    /**
     * Handle edit save
     */
    private function handleEditSave($input) {
        $mode = $this->config['testMode'] ? 'Testing' : 'Production';
        $theURL = $this->config['siteURL'] . '/cwa-display-and-update-user-master-information/';
        
        // Collect and sanitize data
        $data = $this->collectFormData();
        
        // Add to action log
        $actionDate = date('dMy H:i', $this->config['currentTimestamp']);
        $existingLog = isset($_POST['user_action_log']) ? $_POST['user_action_log'] : '';
        $data['user_action_log'] = $existingLog . " / {$actionDate} CRUD Record updated by {$this->user['name']}";
        
        // Update record
        $result = $this->dal->update($input['user_id'], $data, $mode);
        
        if ($result !== false) {
            $content = "<h3>Success</h3>";
            $content .= "<p>User record updated successfully.</p>";
            $content .= "<p><a href='{$theURL}?strpass=" . self::PASS_VIEW . "&user_id={$input['user_id']}'>View Record</a> | ";
            $content .= "<a href='{$theURL}?strpass=" . self::PASS_LIST . "'>Back to List</a></p>";
        } else {
            $content = "<h3>Error</h3>";
            $content .= "<p>Failed to update user record.</p>";
            $content .= "<p><a href='{$theURL}?strpass=" . self::PASS_EDIT_FORM . "&user_id={$input['user_id']}'>Try Again</a> | ";
            $content .= "<a href='{$theURL}?strpass=" . self::PASS_LIST . "'>Back to List</a></p>";
        }
        
        return $content;
    }
    
    /**
     * Handle delete confirmation
     */
    private function handleDeleteConfirm($input) {
        $mode = $this->config['testMode'] ? 'Testing' : 'Production';
        $theURL = $this->config['siteURL'] . '/cwa-display-and-update-user-master-information/';
        
        $records = $this->dal->get_user_master_by_id($input['user_id'], $mode);
        
        if (!$records || count($records) === 0) {
            return "<h3>Delete User Master Record</h3><p>Record not found.</p>";
        }
        
        $record = $records[0];
        
        $content = "<h3>Delete User Master Record</h3>";
        $content .= "<p><strong>Are you sure you want to delete this record?</strong></p>";
        $content .= "<table style='width:600px;'>
            <tr><td><b>Call Sign:</b></td><td>" . esc_html($record['user_call_sign']) . "</td></tr>
            <tr><td><b>Name:</b></td><td>" . esc_html($record['user_first_name'] . ' ' . $record['user_last_name']) . "</td></tr>
            <tr><td><b>Email:</b></td><td>" . esc_html($record['user_email']) . "</td></tr>
        </table>";
        
        $content .= "<form method='post' action='{$theURL}'>
            <input type='hidden' name='strpass' value='" . self::PASS_DELETE_EXECUTE . "'>
            <input type='hidden' name='user_id' value='{$input['user_id']}'>
            <input type='submit' value='Yes, Delete' class='formInputButton' style='background:#f99;'>
            <a href='{$theURL}?strpass=" . self::PASS_LIST . "' class='formInputButton'>Cancel</a>
        </form>";
        
        return $content;
    }
    
    /**
     * Handle delete execution
     */
    private function handleDeleteExecute($input) {
        $mode = $this->config['testMode'] ? 'Testing' : 'Production';
        $theURL = $this->config['siteURL'] . '/cwa-display-and-update-user-master-information/';
        
        $result = $this->dal->delete($input['user_id'], $mode);
        
        if ($result) {
            $content = "<h3>Success</h3>";
            $content .= "<p>User record deleted successfully and archived.</p>";
            $content .= "<p><a href='{$theURL}?strpass=" . self::PASS_LIST . "'>Back to List</a></p>";
        } else {
            $content = "<h3>Error</h3>";
            $content .= "<p>Failed to delete user record.</p>";
            $content .= "<p><a href='{$theURL}?strpass=" . self::PASS_LIST . "'>Back to List</a></p>";
        }
        
        return $content;
    }
    
    /**
     * Handle search
     */
    private function handleSearch($input) {
        $mode = $this->config['testMode'] ? 'Testing' : 'Production';
        $theURL = $this->config['siteURL'] . '/cwa-display-and-update-user-master-information/';
        
        // Build search criteria
        $criteria = array(
            'relation' => 'AND',
            'clauses' => array(
                array(
                    'field' => $input['search_field'],
                    'value' => $input['search_value'],
                    'compare' => 'LIKE'
                )
            )
        );
        
        // Search records
        $records = $this->dal->get_user_master(
            $criteria,
            'user_call_sign',
            'ASC',
            $mode
        );
        
        $content = "<h3>Search Results</h3>";
        $content .= "<p><a href='{$theURL}?strpass=" . self::PASS_LIST . "'>Back to List</a></p>";
        
        if ($records && count($records) > 0) {
            $content .= "<p>Found " . count($records) . " record(s).</p>";
            $content .= $this->renderTable($records, $theURL, $input);
        } else {
            $content .= "<p>No records found matching your search criteria.</p>";
        }
        
        return $content;
    }
    
    /**
     * Handle external entry - display edit form for current user's own record
     * Called from other programs, opens in new tab
     */
    private function handleExternalEntry($input) {
        $mode = $this->config['testMode'] ? 'Testing' : 'Production';
        $theURL = $this->config['siteURL'] . '/cwa-display-and-update-user-master-information/';
        
        // Get current user's record by their user_id (already looked up in initializeUser)
        $records = $this->dal->get_user_master_by_id($this->user['userId'], $mode);
        
        if (!$records || count($records) === 0) {
            return "<h3>Edit Your User Record</h3><p>Your user record was not found.</p>";
        }
        
        $record = $records[0];
        
        $content = "<h3>Edit Your User Record - {$record['user_call_sign']}</h3>";
        
        $content .= "<form method='post' action='{$theURL}'>
            <input type='hidden' name='strpass' value='" . self::PASS_EXTERNAL_EDIT_SAVE . "'>
            <input type='hidden' name='user_id' value='{$this->user['userId']}'>
            <table style='width:900px;'>";
        
        foreach ($this->field_definitions as $field => $def) {
            // Skip admin-only fields for non-admin users
            if (!$this->user['isAdmin'] && in_array($field, $this->admin_only_fields)) {
                continue;
            }
            
            $content .= $this->renderFormField($field, $def, $record);
        }
        
        $content .= "<tr><td>&nbsp;</td><td><input type='submit' value='Update My Record' class='formInputButton'></td></tr>";
        $content .= "</table></form>";
        
        return $content;
    }
    
    /**
     * Handle external entry save - save current user's own record
     */
    private function handleExternalEditSave($input) {
        $mode = $this->config['testMode'] ? 'Testing' : 'Production';
        $startTime = microtime(true);
        
        // Security check: ensure they're updating their own record
        $submittedUserId = $this->sanitize(isset($_POST['user_id']) ? $_POST['user_id'] : 0, 'int');
        
        if ((int)$submittedUserId !== (int)$this->user['userId']) {
            return "<h3>Error</h3><p>You can only update your own record.</p>
                    <p><strong>You may close this tab.</strong></p>";
        }
        
        // Collect and sanitize data
        $data = $this->collectFormData();
        
        // For non-admin users, remove admin-only fields they shouldn't be able to change
        if (!$this->user['isAdmin']) {
            foreach ($this->admin_only_fields as $field) {
                unset($data[$field]);
            }
        }
        
        // Add to action log (handled separately since it's in admin_only_fields)
        $actionDate = date('dMy H:i', $this->config['currentTimestamp']);
        
        // Get existing action log from database
        $existingRecord = $this->dal->get_user_master_by_id($this->user['userId'], $mode);
        $existingLog = ($existingRecord && count($existingRecord) > 0) 
            ? $existingRecord[0]['user_action_log'] 
            : '';
        
        $data['user_action_log'] = $existingLog . " / {$actionDate} Self-service record updated by {$this->user['name']}";
        
        // Update record
        $result = $this->dal->update($this->user['userId'], $data, $mode);
        
        if ($result !== false) {
            $content = "<h3>Success</h3>";
            $content .= "<p>Your user record has been updated successfully.</p>";
            $content .= "<p><strong>You may close this tab.</strong></p>";
        } else {
            $content = "<h3>Error</h3>";
            $content .= "<p>Failed to update your user record. Please try again or contact an administrator.</p>";
            $content .= "<p><strong>You may close this tab.</strong></p>";
        }
        
        // Add footer for external save
        $content .= $this->addFooter($startTime, self::PASS_EXTERNAL_EDIT_SAVE);
        
        return $content;
    }
    
    /**
     * Render form field
     */
    private function renderFormField($field, $def, $record) {
        $value = isset($record[$field]) ? esc_attr($record[$field]) : (isset($def['default']) ? $def['default'] : '');
        $required = isset($def['required']) && $def['required'] ? 'required' : '';
        $readonly = isset($def['readonly']) && $def['readonly'] ? 'readonly' : '';
        $disabled = isset($def['readonly']) && $def['readonly'] ? 'disabled' : '';
        $label = $def['label'];
        
        $html = "<tr><td><b>{$label}:</b></td><td>";
        
        switch ($def['type']) {
            case 'text':
            case 'email':
                $maxlength = isset($def['maxlength']) ? "maxlength='{$def['maxlength']}'" : '';
                $html .= "<input type='{$def['type']}' name='{$field}' value='{$value}' class='formInputText' {$maxlength} {$required} {$readonly}>";
                break;
            
            case 'number':
                $min = isset($def['min']) ? "min='{$def['min']}'" : '';
                $max = isset($def['max']) ? "max='{$def['max']}'" : '';
                $html .= "<input type='number' name='{$field}' value='{$value}' class='formInputText' {$min} {$max} {$required} {$readonly}>";
                break;
            
            case 'textarea':
                $html .= "<textarea name='{$field}' class='formInputText' rows='4' cols='50' {$readonly}>{$value}</textarea>";
                break;
            
            case 'select':
                $html .= "<select name='{$field}' class='formSelect' {$required} {$disabled}>";
                foreach ($def['options'] as $optValue => $optLabel) {
                    $selected = ($value == $optValue) ? 'selected' : '';
                    $html .= "<option value='{$optValue}' {$selected}>{$optLabel}</option>";
                }
                $html .= "</select>";
                // Add hidden field if disabled to preserve value on submit
                if ($disabled) {
                    $html .= "<input type='hidden' name='{$field}' value='{$value}'>";
                }
                break;
        }
        
        $html .= "</td></tr>";
        
        return $html;
    }
    
    /**
     * Collect form data from POST
     */
    private function collectFormData() {
        $data = array();
        
        foreach ($this->field_definitions as $field => $def) {
            if (isset($_POST[$field])) {
                $value = $_POST[$field];
                
                // Sanitize based on type
                if (isset($def['sanitize'])) {
                    $value = $this->sanitize($value, $def['sanitize']);
                }
                
                $data[$field] = $value;
            }
        }
        
        return $data;
    }
    
    /**
     * Get user IP address
     */
    private function getUserIP() {
        if (function_exists('get_the_user_ip')) {
            return get_the_user_ip();
        }
        return '0.0.0.0';
    }
    
    /**
     * Add footer with execution time and joblog
     */
    private function addFooter($startTime, $pass) {
        $endTime = microtime(true);
        $elapsed = number_format($endTime - $startTime, 4);
        
        // Write to joblog (simplified - auto-populates most fields)
        $this->writeToJoblog($pass, $elapsed);
        
        $timestamp = date('Y-m-d H:i:s', $this->config['currentTimestamp']);
        
        return "<br><br>
<p>Report pass {$pass} took {$elapsed} seconds to run</p>
<p>Prepared at {$timestamp}</p>";
    }
    
    /**
     * Write execution to joblog (simplified version)
     */
    private function writeToJoblog($pass, $elapsed) {
        $updateData = array(
            'jobname' => $this->jobname,
            'jobwho' => $this->user['name'],
            'jobmode' => 'Time',
            'jobdatatype' => $this->config['testMode'] ? 'Testmode' : 'Production',
            'jobaddlinfo' => "{$pass}: {$elapsed}",
            'jobip' => $this->user['ipAddress'],
            'doDebug' => $this->config['doDebug']
            // Note: jobdate, jobtime, jobmonth, jobcomments, jobtitle 
            // are auto-populated by the refactored write_joblog2_func
        );
        
        $result = write_joblog2_func($updateData);
        
        if ($result === false && $this->config['doDebug']) {
            error_log("{$this->jobname}: Failed to write to joblog");
        }
    }
}