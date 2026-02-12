<?php
/**
 * CWA Config CRUD Interface
 * 
 * Provides a user interface for Create, Read, Update, and Delete operations
 * on the cwa_config table using the CWA_Config_DAL.
 * 
 * Access: Admin users only
 */

class CWA_Config_CRUD {
    
    private $dal;
    private $user;
    private $config;
    
    // Job name for logging
    private $jobname = 'Config CRUD';
    
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
    
    // Valid config types for the type dropdown
    private $valid_types = array(
        'string'  => 'String',
        'integer' => 'Integer',
        'boolean' => 'Boolean',
        'array'   => 'Array (comma-separated)',
        'json'    => 'JSON'
    );
    
    public function __construct() {
        $this->dal = new CWA_Config_DAL();
        $this->initializeConfig();
        $this->initializeUser();
    }
    
    /**
     * Initialize configuration
     */
    private function initializeConfig() {
        $ctx = CWA_Context::getInstance();
        
        $this->config = array(
            'testMode' => false,
            'doDebug' => false,
            'validTestmode' => $ctx->validTestmode,
            'siteURL' => $ctx->siteurl,
            'currentTimestamp' => $ctx->currentTimestamp,
        );
    }
    
    /**
     * Initialize user
     */
    private function initializeUser() {
        $ctx = CWA_Context::getInstance();
        
        $this->user = array(
            'name' => $ctx->userName,
            'role' => $ctx->userRole,
            'isAdmin' => $ctx->isAdmin,
            'ipAddress' => $this->getUserIP(),
        );
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
        
        // Check authorization - admin only
        if (!$this->user['isAdmin']) {
            return '<p>You are not authorized to access this page. Admin privileges required.</p>';
        }
        
        // Route to appropriate handler
        $content = $this->routeRequest($input);
        
        // Add footer
        $content .= $this->addFooter($startTime, $input['strpass']);
        
        return $content;
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
            'strpass' => sanitize_text_field(isset($_REQUEST['strpass']) ? $_REQUEST['strpass'] : self::PASS_LIST),
            'config_key' => sanitize_text_field(isset($_REQUEST['config_key']) ? $_REQUEST['config_key'] : ''),
            'search_field' => sanitize_text_field(isset($_REQUEST['search_field']) ? $_REQUEST['search_field'] : ''),
            'search_value' => sanitize_text_field(isset($_REQUEST['search_value']) ? $_REQUEST['search_value'] : ''),
            'orderby' => sanitize_text_field(isset($_REQUEST['orderby']) ? $_REQUEST['orderby'] : 'config_key'),
            'order' => sanitize_text_field(isset($_REQUEST['order']) ? $_REQUEST['order'] : 'ASC'),
        );
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
            
            default:
                return '<p>Invalid request.</p>';
        }
    }
    
    /**
     * Handle list view
     */
    private function handleList($input) {
        $theURL = $this->getPageURL();
        
        $records = $this->dal->getAllRows($input['orderby'], $input['order']);
        
        $content = "<h3>CWA Configuration Records</h3>";
        
        // Search form
        $content .= $this->renderSearchForm($theURL);
        
        // Create button
        $content .= "<p><a href='{$theURL}?strpass=" . self::PASS_CREATE_FORM . "' class='formInputButton'>Create New Config Entry</a></p>";
        
        // Table
        if ($records && count($records) > 0) {
            $content .= $this->renderTable($records, $theURL, $input);
        } else {
            $content .= "<p>No configuration records found.</p>";
        }
        
        return $content;
    }
    
    /**
     * Render search form
     */
    private function renderSearchForm($theURL) {
        $searchFields = array(
            'config_key' => 'Config Key',
            'config_value' => 'Config Value',
            'config_type' => 'Config Type',
            'updated_by' => 'Updated By'
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
        $orderToggle = $input['order'] === 'ASC' ? 'DESC' : 'ASC';
        
        $content = "<table style='width:100%; border-collapse:collapse;'>
            <thead>
                <tr>
                    <th><a href='{$theURL}?strpass=" . self::PASS_LIST . "&orderby=config_key&order={$orderToggle}'>Config Key</a></th>
                    <th><a href='{$theURL}?strpass=" . self::PASS_LIST . "&orderby=config_type&order={$orderToggle}'>Type</a></th>
                    <th>Value</th>
                    <th><a href='{$theURL}?strpass=" . self::PASS_LIST . "&orderby=updated_at&order={$orderToggle}'>Updated At</a></th>
                    <th><a href='{$theURL}?strpass=" . self::PASS_LIST . "&orderby=updated_by&order={$orderToggle}'>Updated By</a></th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>";
        
        foreach ($records as $record) {
            $key = esc_html($record['config_key']);
            $keyEncoded = urlencode($record['config_key']);
            $type = esc_html($record['config_type']);
            $value = esc_html($this->truncateValue($record['config_value'], 80));
            $updatedAt = esc_html($record['updated_at']);
            $updatedBy = esc_html($record['updated_by']);
            
            $content .= "<tr>
                <td>{$key}</td>
                <td>{$type}</td>
                <td>{$value}</td>
                <td>{$updatedAt}</td>
                <td>{$updatedBy}</td>
                <td>
                    <a href='{$theURL}?strpass=" . self::PASS_VIEW . "&config_key={$keyEncoded}'>View</a> | 
                    <a href='{$theURL}?strpass=" . self::PASS_EDIT_FORM . "&config_key={$keyEncoded}'>Edit</a> | 
                    <a href='{$theURL}?strpass=" . self::PASS_DELETE_CONFIRM . "&config_key={$keyEncoded}'>Delete</a>
                </td>
            </tr>";
        }
        
        $content .= "</tbody></table>";
        
        return $content;
    }
    
    /**
     * Handle view single record
     */
    private function handleView($input) {
        $theURL = $this->getPageURL();
        
        $record = $this->dal->getRow($input['config_key']);
        
        if (!$record) {
            return "<h3>View Configuration</h3><p>Record not found.</p>";
        }
        
        $keyEncoded = urlencode($record['config_key']);
        
        $content = "<h3>View Configuration - " . esc_html($record['config_key']) . "</h3>";
        $content .= "<p><a href='{$theURL}?strpass=" . self::PASS_LIST . "'>Back to List</a> | ";
        $content .= "<a href='{$theURL}?strpass=" . self::PASS_EDIT_FORM . "&config_key={$keyEncoded}'>Edit</a></p>";
        
        $content .= "<table style='width:900px;'>
            <tr><td><b>Config Key:</b></td><td>" . esc_html($record['config_key']) . "</td></tr>
            <tr><td><b>Config Type:</b></td><td>" . esc_html($record['config_type']) . "</td></tr>
            <tr><td><b>Config Value:</b></td><td><pre style='white-space:pre-wrap;'>" . esc_html($record['config_value']) . "</pre></td></tr>
            <tr><td><b>Updated At:</b></td><td>" . esc_html($record['updated_at']) . "</td></tr>
            <tr><td><b>Updated By:</b></td><td>" . esc_html($record['updated_by']) . "</td></tr>
        </table>";
        
        return $content;
    }
    
    /**
     * Handle create form
     */
    private function handleCreateForm($input) {
        $theURL = $this->getPageURL();
        
        $content = "<h3>Create New Configuration Entry</h3>";
        $content .= "<p><a href='{$theURL}?strpass=" . self::PASS_LIST . "'>Back to List</a></p>";
        
        $content .= "<form method='post' action='{$theURL}'>
            <input type='hidden' name='strpass' value='" . self::PASS_CREATE_SAVE . "'>
            <table style='width:900px;'>";
        
        // Config Key (editable on create only)
        $content .= "<tr><td><b>Config Key:</b></td><td>
            <input type='text' name='config_key' value='' class='formInputText' maxlength='50' required>
        </td></tr>";
        
        // Config Type
        $content .= "<tr><td><b>Config Type:</b></td><td>" . $this->renderTypeSelect('') . "</td></tr>";
        
        // Config Value
        $content .= "<tr><td><b>Config Value:</b></td><td>
            <textarea name='config_value' class='formInputText' rows='6' cols='60'></textarea>
        </td></tr>";
        
        $content .= "<tr><td>&nbsp;</td><td><input type='submit' value='Create Entry' class='formInputButton'></td></tr>";
        $content .= "</table></form>";
        
        return $content;
    }
    
    /**
     * Handle create save
     */
    private function handleCreateSave($input) {
        $theURL = $this->getPageURL();
        
        // Collect form data
        $configKey = sanitize_text_field(isset($_POST['config_key']) ? $_POST['config_key'] : '');
        $configValue = isset($_POST['config_value']) ? sanitize_textarea_field($_POST['config_value']) : '';
        $configType = sanitize_text_field(isset($_POST['config_type']) ? $_POST['config_type'] : 'string');
        
        // Validate
        if (empty($configKey)) {
            $content = "<h3>Error</h3>";
            $content .= "<p>Config Key is required.</p>";
            $content .= "<p><a href='{$theURL}?strpass=" . self::PASS_CREATE_FORM . "'>Try Again</a> | ";
            $content .= "<a href='{$theURL}?strpass=" . self::PASS_LIST . "'>Back to List</a></p>";
            return $content;
        }
        
        // Check for duplicate key
        if ($this->dal->keyExists($configKey)) {
            $content = "<h3>Error</h3>";
            $content .= "<p>Config Key '" . esc_html($configKey) . "' already exists.</p>";
            $content .= "<p><a href='{$theURL}?strpass=" . self::PASS_CREATE_FORM . "'>Try Again</a> | ";
            $content .= "<a href='{$theURL}?strpass=" . self::PASS_LIST . "'>Back to List</a></p>";
            return $content;
        }
        
        // Validate type
        if (!array_key_exists($configType, $this->valid_types)) {
            $configType = 'string';
        }
        
        // Insert
        $data = array(
            'config_key'   => $configKey,
            'config_value' => $configValue,
            'config_type'  => $configType,
            'updated_by'   => $this->user['name']
        );
        
        $result = $this->dal->insertRow($data);
        
        if ($result) {
            $keyEncoded = urlencode($configKey);
            $content = "<h3>Success</h3>";
            $content .= "<p>Configuration entry created successfully.</p>";
            $content .= "<p><a href='{$theURL}?strpass=" . self::PASS_VIEW . "&config_key={$keyEncoded}'>View Record</a> | ";
            $content .= "<a href='{$theURL}?strpass=" . self::PASS_LIST . "'>Back to List</a></p>";
        } else {
            $content = "<h3>Error</h3>";
            $content .= "<p>Failed to create configuration entry.</p>";
            $content .= "<p><a href='{$theURL}?strpass=" . self::PASS_CREATE_FORM . "'>Try Again</a> | ";
            $content .= "<a href='{$theURL}?strpass=" . self::PASS_LIST . "'>Back to List</a></p>";
        }
        
        return $content;
    }
    
    /**
     * Handle edit form
     */
    private function handleEditForm($input) {
        $theURL = $this->getPageURL();
        
        $record = $this->dal->getRow($input['config_key']);
        
        if (!$record) {
            return "<h3>Edit Configuration</h3><p>Record not found.</p>";
        }
        
        $content = "<h3>Edit Configuration - " . esc_html($record['config_key']) . "</h3>";
        $content .= "<p><a href='{$theURL}?strpass=" . self::PASS_LIST . "'>Back to List</a></p>";
        
        $content .= "<form method='post' action='{$theURL}'>
            <input type='hidden' name='strpass' value='" . self::PASS_EDIT_SAVE . "'>
            <input type='hidden' name='config_key' value='" . esc_attr($record['config_key']) . "'>
            <table style='width:900px;'>";
        
        // Config Key (read-only on edit)
        $content .= "<tr><td><b>Config Key:</b></td><td>" . esc_html($record['config_key']) . "</td></tr>";
        
        // Config Type
        $content .= "<tr><td><b>Config Type:</b></td><td>" . $this->renderTypeSelect($record['config_type']) . "</td></tr>";
        
        // Config Value
        $content .= "<tr><td><b>Config Value:</b></td><td>
            <textarea name='config_value' class='formInputText' rows='6' cols='60'>" . esc_textarea($record['config_value']) . "</textarea>
        </td></tr>";
        
        // Updated At (display only)
        $content .= "<tr><td><b>Updated At:</b></td><td>" . esc_html($record['updated_at']) . "</td></tr>";
        
        // Updated By (display only)
        $content .= "<tr><td><b>Updated By:</b></td><td>" . esc_html($record['updated_by']) . "</td></tr>";
        
        $content .= "<tr><td>&nbsp;</td><td><input type='submit' value='Update Entry' class='formInputButton'></td></tr>";
        $content .= "</table></form>";
        
        return $content;
    }
    
    /**
     * Handle edit save
     */
    private function handleEditSave($input) {
        $theURL = $this->getPageURL();
        
        // Collect form data
        $configKey = sanitize_text_field(isset($_POST['config_key']) ? $_POST['config_key'] : '');
        $configValue = isset($_POST['config_value']) ? sanitize_textarea_field($_POST['config_value']) : '';
        $configType = sanitize_text_field(isset($_POST['config_type']) ? $_POST['config_type'] : 'string');
        
        if (empty($configKey)) {
            return "<h3>Error</h3><p>Config Key is missing.</p>";
        }
        
        // Validate type
        if (!array_key_exists($configType, $this->valid_types)) {
            $configType = 'string';
        }
        
        // Update
        $data = array(
            'config_value' => $configValue,
            'config_type'  => $configType,
            'updated_by'   => $this->user['name']
        );
        
        $result = $this->dal->updateRow($configKey, $data);
        
        if ($result !== false) {
            $keyEncoded = urlencode($configKey);
            $content = "<h3>Success</h3>";
            $content .= "<p>Configuration entry updated successfully.</p>";
            $content .= "<p><a href='{$theURL}?strpass=" . self::PASS_VIEW . "&config_key={$keyEncoded}'>View Record</a> | ";
            $content .= "<a href='{$theURL}?strpass=" . self::PASS_LIST . "'>Back to List</a></p>";
        } else {
            $keyEncoded = urlencode($configKey);
            $content = "<h3>Error</h3>";
            $content .= "<p>Failed to update configuration entry.</p>";
            $content .= "<p><a href='{$theURL}?strpass=" . self::PASS_EDIT_FORM . "&config_key={$keyEncoded}'>Try Again</a> | ";
            $content .= "<a href='{$theURL}?strpass=" . self::PASS_LIST . "'>Back to List</a></p>";
        }
        
        return $content;
    }
    
    /**
     * Handle delete confirmation
     */
    private function handleDeleteConfirm($input) {
        $theURL = $this->getPageURL();
        
        $record = $this->dal->getRow($input['config_key']);
        
        if (!$record) {
            return "<h3>Delete Configuration</h3><p>Record not found.</p>";
        }
        
        $content = "<h3>Delete Configuration Entry</h3>";
        $content .= "<p><strong>Are you sure you want to delete this configuration entry?</strong></p>";
        $content .= "<table style='width:600px;'>
            <tr><td><b>Config Key:</b></td><td>" . esc_html($record['config_key']) . "</td></tr>
            <tr><td><b>Config Type:</b></td><td>" . esc_html($record['config_type']) . "</td></tr>
            <tr><td><b>Config Value:</b></td><td>" . esc_html($this->truncateValue($record['config_value'], 120)) . "</td></tr>
        </table>";
        
        $content .= "<form method='post' action='{$theURL}'>
            <input type='hidden' name='strpass' value='" . self::PASS_DELETE_EXECUTE . "'>
            <input type='hidden' name='config_key' value='" . esc_attr($record['config_key']) . "'>
            <input type='submit' value='Yes, Delete' class='formInputButton' style='background:#f99;'>
            <a href='{$theURL}?strpass=" . self::PASS_LIST . "' class='formInputButton'>Cancel</a>
        </form>";
        
        return $content;
    }
    
    /**
     * Handle delete execution
     */
    private function handleDeleteExecute($input) {
        $theURL = $this->getPageURL();
        
        $configKey = sanitize_text_field(isset($_POST['config_key']) ? $_POST['config_key'] : '');
        
        if (empty($configKey)) {
            return "<h3>Error</h3><p>Config Key is missing.</p>";
        }
        
        $result = $this->dal->delete($configKey);
        
        if ($result) {
            $content = "<h3>Success</h3>";
            $content .= "<p>Configuration entry '" . esc_html($configKey) . "' deleted successfully.</p>";
            $content .= "<p><a href='{$theURL}?strpass=" . self::PASS_LIST . "'>Back to List</a></p>";
        } else {
            $content = "<h3>Error</h3>";
            $content .= "<p>Failed to delete configuration entry.</p>";
            $content .= "<p><a href='{$theURL}?strpass=" . self::PASS_LIST . "'>Back to List</a></p>";
        }
        
        return $content;
    }
    
    /**
     * Handle search
     */
    private function handleSearch($input) {
        $theURL = $this->getPageURL();
        
        $records = $this->dal->searchRows($input['search_field'], $input['search_value']);
        
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
     * Render config_type select dropdown
     */
    private function renderTypeSelect($selectedType) {
        $html = "<select name='config_type' class='formSelect'>";
        foreach ($this->valid_types as $value => $label) {
            $selected = ($selectedType === $value) ? 'selected' : '';
            $html .= "<option value='{$value}' {$selected}>{$label}</option>";
        }
        $html .= "</select>";
        return $html;
    }
    
    /**
     * Truncate long values for display in table
     */
    private function truncateValue($value, $maxLength = 80) {
        if (strlen($value) > $maxLength) {
            return substr($value, 0, $maxLength) . '...';
        }
        return $value;
    }
    
    /**
     * Get the page URL for this CRUD
     */
    private function getPageURL() {
        return $this->config['siteURL'] . '/cwa-config-crud/';
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
        
        // Write to joblog
        $this->writeToJoblog($pass, $elapsed);
        
        $timestamp = date('Y-m-d H:i:s', $this->config['currentTimestamp']);
        
        return "<br><br>
<p>Report pass {$pass} took {$elapsed} seconds to run</p>
<p>Prepared at {$timestamp}</p>";
    }
    
    /**
     * Write execution to joblog
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
        );
        
        $result = write_joblog2_func($updateData);
        
        if ($result === false && $this->config['doDebug']) {
            error_log("{$this->jobname}: Failed to write to joblog");
        }
    }
}
