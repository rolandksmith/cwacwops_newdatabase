/**
 * CWA Advisor Data Access Layer
 * 
 * Handles all database operations for advisor records with proper
 * security, validation, and error handling.
 */
if ( ! class_exists( 'CWA_Advisor_DAL' ) ) {
    class CWA_Advisor_DAL {
        private $wpdb;
        
        /**
         * Valid database columns for advisor table
         */
        private $valid_columns = [
            'advisor_id',
            'advisor_call_sign',
            'advisor_semester',
            'advisor_welcome_email_date',
            'advisor_verify_email_date',
            'advisor_verify_email_number',
            'advisor_verify_response',
            'advisor_action_log',
            'advisor_class_verified',
            'advisor_control_code',
            'advisor_date_created',
            'advisor_date_updated',
            'advisor_replacement_status'
        ];
        
        /**
         * Valid comparison operators for WHERE clauses
         */
        private $valid_operators = [
            '=',
            '!=',
            '>',
            '<',
            '>=',
            '<=',
            'LIKE',
            'NOT LIKE',
            'IN',
            'NOT IN'
        ];
        
        /**
         * Valid modes for table selection
         */
        private $valid_modes = ['Production', 'Testing'];

        /**
         * Constructor
         */
        public function __construct() {
            global $wpdb;
            $this->wpdb = $wpdb;
        }

        /**
         * Insert a new advisor record
         * 
         * @param array $data Advisor data to insert
         * @param string $mode Database mode (Production or Testing)
         * @return int|false Insert ID on success, false on failure
         */
        public function insert( $data, $mode ) {
            if ( ! $this->_validate_mode( $mode ) ) {
                return false;
            }
            
            $tables = $this->_get_table_names( $mode );
            $clean = $this->_sanitize_data( $data );
            
            if ( empty( $clean ) ) {
                return false;
            }
            
            $result = $this->wpdb->insert( $tables['primary'], $clean );
            
            if ( $result ) {
                $new_id = $this->wpdb->insert_id;
                $this->_log(
                    $clean['advisor_call_sign'] ?? 'UNKNOWN',
                    'create',
                    $clean,
                    $tables
                );
                return $new_id;
            }
            
            return false;
        }

        /**
         * Update an existing advisor record
         * 
         * @param int $id Advisor ID
         * @param array $data Data to update
         * @param string $mode Database mode
         * @return int|false Number of rows updated, or false on failure
         */
        public function update( $id, $data, $mode ) {
            if ( ! $this->_validate_mode( $mode ) || ! $this->_validate_id( $id ) ) {
                return false;
            }
            
            $tables = $this->_get_table_names( $mode );
            $clean = $this->_sanitize_data( $data );
            
            if ( empty( $clean ) ) {
                return false;
            }
            
            $result = $this->wpdb->update(
                $tables['primary'],
                $clean,
                ['advisor_id' => $id],
                null,
                ['%d']
            );
            
            if ( $result !== false ) {
                $call_sign = $this->wpdb->get_var(
                    $this->wpdb->prepare(
                        "SELECT advisor_call_sign FROM {$tables['primary']} WHERE advisor_id = %d",
                        $id
                    )
                );
                
                $this->_log(
                    $call_sign ?? 'UNKNOWN',
                    'update',
                    $clean,
                    $tables
                );
            }
            
            return $result;
        }


        /**
         * Get advisor record by id
         *
         * @param int $id The record_id of the advisor to retrieve
         * @param string $operating_mode Database mode (Production, Testing, or Testmode)
         * @return array|null Array of matching records or null on error
         */
        public function get_advisor_by_id( $id, $operating_mode ) {
            if ( ! $this->_validate_mode( $operating_mode ) ) {
                return null;
            }
            
            // Validate id is numeric
            if ( ! $this->_validate_id( $id )  ) {
                return null;
            }
            
            $tables = $this->_get_table_names( $operating_mode );
            
            $sql = $this->wpdb->prepare(
                "SELECT * FROM {$tables['primary']} WHERE user_ID = %d",
                $id 
            );
            
            return $this->wpdb->get_results( $sql, ARRAY_A );
        }

        /**
         * Delete an advisor record (moves to deleted table)
         * 
         * @param int $id Advisor ID
         * @param string $mode Database mode
         * @return int|false Number of rows deleted, or false on failure
         */
        public function delete( $id, $mode ) {
            if ( ! $this->_validate_mode( $mode ) || ! $this->_validate_id( $id ) ) {
                return false;
            }
            
            $tables = $this->_get_table_names( $mode );
            
            $record = $this->wpdb->get_row(
                $this->wpdb->prepare(
                    "SELECT * FROM {$tables['primary']} WHERE advisor_id = %d",
                    $id
                ),
                ARRAY_A
            );
            
            if ( ! $record ) {
                return false;
            }
            
            // Archive to deleted table
            $this->wpdb->insert( $tables['deleted'], $record );
            
            // Log the deletion
            $this->_log(
                $record['advisor_call_sign'] ?? 'UNKNOWN',
                'delete',
                ['id' => $id],
                $tables
            );
            
            // Delete from primary table
            return $this->wpdb->delete(
                $tables['primary'],
                ['advisor_id' => $id],
                ['%d']
            );
        }

        /**
         * Get advisors with optional filtering and ordering
         * 
         * @param array $criteria Search criteria
         * @param string $orderby Column to order by
         * @param string $order ASC or DESC
         * @param string $mode Database mode
         * @return array|null Results array or null on error
         */
        public function get_advisor_by_order( $criteria, $orderby, $order, $mode ) {
            if ( ! $this->_validate_mode( $mode ) ) {
                return null;
            }
            
            $tables = $this->_get_table_names( $mode );
            $sql = "SELECT * FROM {$tables['primary']}";
            $params = [];
            
            // Build WHERE clause
            if ( ! empty( $criteria ) ) {
                $where = $this->_build_where( $criteria );
                if ( ! empty( $where['sql'] ) && $where['sql'] !== '1=1' ) {
                    $sql .= " WHERE " . $where['sql'];
                    $params = $where['params'];
                }
            }
            
            // Add ORDER BY if valid
            if ( ! empty( $orderby ) && in_array( $orderby, $this->valid_columns ) ) {
                $order = strtoupper( $order ) === 'DESC' ? 'DESC' : 'ASC';
                $sql .= " ORDER BY {$orderby} {$order}";
            }
            
            // Execute query
            if ( ! empty( $params ) ) {
                $sql = $this->wpdb->prepare( $sql, $params );
            }
            return $this->wpdb->get_results( $sql, ARRAY_A );
        }

        /**
         * Run a custom SQL query
         * 
         * @param string $sql SQL query with TABLENAME placeholder
         * @param string $mode Database mode
         * @param array $params Parameters for prepared statement
         * @return array|null Query results or null on error
         */
        public function run_sql( $sql, $mode, $params = [] ) {
            if ( ! $this->_validate_mode( $mode ) ) {
                return null;
            }
            
            $tables = $this->_get_table_names( $mode );
            $sql = str_replace( 'TABLENAME', $tables['primary'], $sql );
            
            if ( ! empty( $params ) ) {
                $sql = $this->wpdb->prepare( $sql, $params );
            }
            
            return $this->wpdb->get_results( $sql, ARRAY_A );
        }

        /**
         * Get a single value from a custom SQL query
         * 
         * @param string $sql SQL query with TABLENAME placeholder
         * @param string $mode Database mode
         * @param array $params Parameters for prepared statement
         * @return string|null Single value or null
         */
        public function get_single_value( $sql, $mode, $params = [] ) {
            if ( ! $this->_validate_mode( $mode ) ) {
                return null;
            }
            
            $tables = $this->_get_table_names( $mode );
            $sql = str_replace( 'TABLENAME', $tables['primary'], $sql );
            
            if ( ! empty( $params ) ) {
                $sql = $this->wpdb->prepare( $sql, $params );
            }
            
            return $this->wpdb->get_var( $sql );
        }

        /**
         * Validate database mode
         * 
         * @param string $mode Mode to validate
         * @return bool True if valid
         */
        private function _validate_mode( $mode ) {
            return in_array( $mode, $this->valid_modes, true );
        }

        /**
         * Checks if a given string is a valid, known column name.
         */
        private function _is_valid_column( $column_name ) {
            return in_array( $column_name, $this->valid_columns, true );
        }
        /**
         * Validate advisor ID
         * 
         * @param mixed $id ID to validate
         * @return bool True if valid
         */
        private function _validate_id( $id ) {
            return is_numeric( $id ) && $id > 0;
        }

        /**
         * Sanitize input data against valid columns
         * 
         * @param array $data Data to sanitize
         * @return array Sanitized data
         */
        private function _sanitize_data( $data ) {
            if ( ! is_array( $data ) ) {
                return [];
            }
            
            return array_intersect_key(
                $data,
                array_flip( $this->valid_columns )
            );
        }

         /**
         * Gets the correct $wpdb->prepare placeholder for a value.
         */
        private function _get_placeholder_for_value( $value ) {
            if ( is_int( $value ) ) {
                return '%d';
            }
            if ( is_float( $value ) ) {
                return '%f';
            }
            return '%s';
        }


       /**
         * Get table names based on mode
         * 
         * @param string $mode Database mode
         * @return array Table names
         */
        private function _get_table_names( $mode ) {
            $suffix = ( $mode === 'Production' ) ? '' : '2';
            
            return [
                'primary' => $this->wpdb->prefix . 'cwa_advisor' . $suffix,
                'log'     => $this->wpdb->prefix . 'cwa_data_log' . $suffix,
                'deleted' => $this->wpdb->prefix . 'cwa_deleted_advisor' . $suffix
            ];
        }

        /**
         * Log database action
         * 
         * @param string $call_sign Advisor call sign
         * @param string $action Action performed
         * @param array $data Data involved in action
         * @param array $tables Table names
         */
        private function _log( $call_sign, $action, $data, $tables ) {
            $user = wp_get_current_user();
             if ( $user->ID > 0 ) {
            	$theUser = $user->user_login;
            } else {
            	$theUser = 'CRON';
            }
           
            $log_data = [
                'data_date_written'  => current_time( 'mysql' ),
                'data_user'          => $thisUser,
                'data_call_sign'     => $call_sign,
                'data_table_name'    => $tables['primary'],
                'data_action'        => $action,
                'data_field_values'  => wp_json_encode( $data )
            ];
            
            $this->wpdb->insert( $tables['log'], $log_data );
        }

        /**
         * Build WHERE clause from criteria
         * 
         * @param array $criteria Criteria array
         * @return array SQL string and parameters
         */
        private function _build_where( $criteria ) {
            $sql_chunks = [];
            $params = [];
            
            $relation = ( isset( $criteria['relation'] ) && 'OR' === strtoupper( $criteria['relation'] ) ) ? ' OR ' : ' AND ';

            if ( empty( $criteria['clauses'] ) ) {
                return [ 'sql' => '', 'params' => [] ];
            }

            foreach ( $criteria['clauses'] as $clause ) {
                if ( ! empty( $clause['field'] ) ) {
                    if ( ! $this->_is_valid_column( $clause['field'] ) ) {
                        continue; // Skip invalid column
                    }

                    $field = $clause['field'];
                    $value = isset( $clause['value'] ) ? $clause['value'] : '';
                    $compare = isset( $clause['compare'] ) ? strtoupper( $clause['compare'] ) : '=';

                    if ( ! in_array( $compare, [ '=', '!=', 'LIKE' ] ) ) {
                        $compare = '='; // Default to safe operator
                    }
                    
                    $placeholder = $this->_get_placeholder_for_value( $value );
                    
                    $sql_chunks[] = "{$field} {$compare} {$placeholder}";
                    $params[] = $value;
                
                } 
                else if ( ! empty( $clause['relation'] ) ) {
                    $nested_parts = $this->_build_where( $clause );
                    
                    if ( ! empty( $nested_parts['sql'] ) ) {
                        $sql_chunks[] = "( " . $nested_parts['sql'] . " )";
                        $params = array_merge( $params, $nested_parts['params'] );
                    }
                }
            }

            return [
                'sql'    => implode( $relation, $sql_chunks ),
                'params' => $params
            ];
        }
    }
}
