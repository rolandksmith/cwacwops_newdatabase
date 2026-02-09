/** CWA Config DAL
	Provides the methods to manage the data initialization static values
	
*/

class CWA_Config_DAL {
    
    private $table_name;
    private $wpdb;
    private $cache = array();
    
    public function __construct() {
        global $wpdb;
        $this->wpdb = $wpdb;
        $this->table_name = $wpdb->prefix . 'cwa_config';
    }
    
    /**
     * Get a config value by key
     * 
     * @param string $key The config key
     * @param mixed $default Default value if key not found
     * @return mixed The typed config value
     */
    public function get($key, $default = null) {
        // Check cache first
        if (isset($this->cache[$key])) {
            return $this->cache[$key];
        }
        
        $row = $this->wpdb->get_row(
            $this->wpdb->prepare(
                "SELECT config_value, config_type FROM {$this->table_name} WHERE config_key = %s",
                $key
            ),
            ARRAY_A
        );
        
        if (!$row) {
            return $default;
        }
        
        $value = $this->castValue($row['config_value'], $row['config_type']);
        $this->cache[$key] = $value;
        
        return $value;
    }
    
    /**
     * Set a config value
     * 
     * @param string $key The config key
     * @param mixed $value The value to store
     * @param string $updated_by Who is making the update
     * @return bool Success/failure
     */
    public function set($key, $value, $updated_by = '') {
        $config_type = $this->detectType($value);
        $stored_value = $this->serializeValue($value, $config_type);
        
        // Check if key exists
        $exists = $this->wpdb->get_var(
            $this->wpdb->prepare(
                "SELECT COUNT(*) FROM {$this->table_name} WHERE config_key = %s",
                $key
            )
        );
        
        if ($exists) {
            $result = $this->wpdb->update(
                $this->table_name,
                array(
                    'config_value' => $stored_value,
                    'config_type' => $config_type,
                    'updated_by' => $updated_by
                ),
                array('config_key' => $key),
                array('%s', '%s', '%s'),
                array('%s')
            );
        } else {
            $result = $this->wpdb->insert(
                $this->table_name,
                array(
                    'config_key' => $key,
                    'config_value' => $stored_value,
                    'config_type' => $config_type,
                    'updated_by' => $updated_by
                ),
                array('%s', '%s', '%s', '%s')
            );
        }
        
        // Update cache
        if ($result !== false) {
            $this->cache[$key] = $value;
        }
        
        return $result !== false;
    }
    
    /**
     * Get all config values
     * 
     * @return array Associative array of all config values
     */
    public function getAll() {
        $rows = $this->wpdb->get_results(
            "SELECT config_key, config_value, config_type FROM {$this->table_name}",
            ARRAY_A
        );
        
        $result = array();
        foreach ($rows as $row) {
            $value = $this->castValue($row['config_value'], $row['config_type']);
            $result[$row['config_key']] = $value;
            $this->cache[$row['config_key']] = $value;
        }
        
        return $result;
    }
    
    /**
     * Delete a config key
     * 
     * @param string $key The config key to delete
     * @return bool Success/failure
     */
    public function delete($key) {
        $result = $this->wpdb->delete(
            $this->table_name,
            array('config_key' => $key),
            array('%s')
        );
        
        unset($this->cache[$key]);
        
        return $result !== false;
    }
    
    /**
     * Cast stored string value to appropriate type
     */
    private function castValue($value, $type) {
        switch ($type) {
            case 'array':
                return explode(',', $value);
            case 'integer':
                return (int)$value;
            case 'boolean':
                return ($value === '1' || strtolower($value) === 'true');
            case 'json':
                return json_decode($value, true);
            case 'string':
            default:
                return $value;
        }
    }
    
    /**
     * Serialize value for storage
     */
    private function serializeValue($value, $type) {
        switch ($type) {
            case 'array':
                return implode(',', $value);
            case 'integer':
                return (string)$value;
            case 'boolean':
                return $value ? '1' : '0';
            case 'json':
                return json_encode($value);
            case 'string':
            default:
                return $value;
        }
    }
    
    /**
     * Detect the type of a value
     */
    private function detectType($value) {
        if (is_array($value)) {
            // Check if it's an associative array (needs JSON)
            if (array_keys($value) !== range(0, count($value) - 1)) {
                return 'json';
            }
            return 'array';
        }
        if (is_bool($value)) {
            return 'boolean';
        }
        if (is_int($value)) {
            return 'integer';
        }
        return 'string';
    }
    
    /**
     * Clear the cache
     */
    public function clearCache() {
        $this->cache = array();
    }
}
