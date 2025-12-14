/**
 * CWA_Advisorclass_DAL (Data Access Layer)
 *
 * Manages all MySQL database interactions for the CWA Advisor Class tables.
 *
 * This class is STATELESS. The $operatingMode ('Production' or 'Testmode')
 * must be passed into every public method to determine which
 * set of tables (live or test) to use for that specific operation.
 */

if ( ! class_exists( 'CWA_Advisorclass_DAL' ) ) {

    class CWA_Advisorclass_DAL {

        /**
         * The WordPress database global object.
         * @var wpdb
         */
        private $wpdb;

        /**
         * A list of all valid column names for the advisorclass table.
         * Used to sanitize input data and prevent SQL injection.
         * @var array
         */
        private $valid_columns = [
            'advisorclass_id',
            'advisorclass_call_sign',
            'advisorclass_sequence',
            'advisorclass_timezone_offset',
            'advisorclass_semester',
            'advisorclass_level',
            'advisorclass_language',
            'advisorclass_class_size',
            'advisorclass_class_schedule_days',
            'advisorclass_class_schedule_times',
            'advisorclass_class_schedule_days_utc',
            'advisorclass_class_schedule_times_utc',
            'advisorclass_action_log',
            'advisorclass_class_incomplete',
            'advisorclass_date_created',
            'advisorclass_date_updated',
            'advisorclass_student01',
            'advisorclass_student02',
            'advisorclass_student03',
            'advisorclass_student04',
            'advisorclass_student05',
            'advisorclass_student06',
            'advisorclass_student07',
            'advisorclass_student08',
            'advisorclass_student09',
            'advisorclass_student10',
            'advisorclass_student11',
            'advisorclass_student12',
            'advisorclass_student13',
            'advisorclass_student14',
            'advisorclass_student15',
            'advisorclass_student16',
            'advisorclass_student17',
            'advisorclass_student18',
            'advisorclass_student19',
            'advisorclass_student20',
            'advisorclass_student21',
            'advisorclass_student22',
            'advisorclass_student23',
            'advisorclass_student24',
            'advisorclass_student25',
            'advisorclass_student26',
            'advisorclass_student27',
            'advisorclass_student28',
            'advisorclass_student29',
            'advisorclass_student30',
            'advisorclass_number_students',
            'advisorclass_evaluation_complete',
            'advisorclass_class_comments',
            'advisorclass_copy_control',
        ];

        /**
         * Constructor.
         * Only initializes the $wpdb object.
         */
        public function __construct() {
            global $wpdb;
            $this->wpdb = $wpdb;
        }

        // ---------------------------------------------------------------------
        // Public Methods
        // ---------------------------------------------------------------------

        /**
         * 1. Inserts a new advisorclass record and logs the action.
         *
         * @param array  $data          Associative array of [field_name => value] to insert.
         * @param string $operatingMode 'Production' or 'Testmode'.
         * @return int|false The new advisorclass_id on success, false on error.
         */
        public function insert( $data, $operatingMode ) {
            $tables = $this->_get_table_names( $operatingMode );
            $clean_data = $this->_filter_data( $data );

            if ( empty( $clean_data ) ) {
            	error_log("CWA_Advisorclass_DAL ERROR No valid data submitted for an insert");
                return false; // No valid data provided
            }

            // $wpdb->insert handles data sanitization
            $result = $this->wpdb->insert( $tables['primary'], $clean_data );

            if ( $result ) {
                $new_id = $this->wpdb->insert_id;
                
                // Log the 'create' action
                $call_sign = isset( $clean_data['advisorclass_call_sign'] ) ? $clean_data['advisorclass_call_sign'] : 'UNKNOWN';
                $this->_log_change(
                    $call_sign,
                    'create',
                    $clean_data,
                    $tables['primary'],
                    $tables['log']
                );
                
                return $new_id;
            }

            $myStr = $this->wpdb->last_query;
			error_log("CWA_Advisorclass_DAL ERROR attempt to insert a record returned FALSE\nSQL: $myStr");
            return false;
        }

        /**
         * 2. Gets advisorclass records based on specified criteria.
         *
         * @param array  $criteria      Criteria for the WHERE clause (supports nested AND/OR).
         * @param string $operatingMode 'Production' or 'Testmode'.
         * @return array|false Array of objects on success, false on error.
         */
        public function get_advisorclasses( $criteria, $operatingMode ) {
            $tables = $this->_get_table_names( $operatingMode );
            $sql = "SELECT * FROM {$tables['primary']}";
            $params = [];
            
            if ( ! empty( $criteria ) ) {
                $where_parts = $this->_build_where_clause_recursive( $criteria );
                
                if ( ! empty( $where_parts['sql'] ) ) {
                    $sql .= " WHERE " . $where_parts['sql'];
                    $params = $where_parts['params'];
                }
            }

            if ( ! empty( $params ) ) {
                $prepared_sql = $this->wpdb->prepare( $sql, $params );
                $results = $this->wpdb->get_results( $prepared_sql, ARRAY_A );
            } else {
                $results = $this->wpdb->get_results( $sql, ARRAY_A );
            }
            
            if ($results === FALSE || $results === NULL) {
            	$myStr = $this->wpdb->last_query;
            	error_log("CWA_Advisorclass_DAL ERROR get_advisorclasses returned FALSE/NULL\nSQL: $myStr");
            } else {
            	if (empty($results)) {
					$myQuery = $this->wpdb->last_query;
					error_log("CWA_Advisorclass_DAL INFORMATION No data retrieved with $myQuery");
            	}
            }
            return $results;
        }


        /**
         * 3. Gets advisorclass records based on specified criteria, orderby and order.
         *
         * @param array  $criteria      Criteria for the WHERE clause (supports nested AND/OR).
         * @param strint $orderby		fields to sequence the result
         * @param string ASC|DESC		order of the sequence
         * @param string $operatingMode 'Production' or 'Testmode'.
         * @return array|false Array of objects on success, false on error.
       */
        public function get_advisorclasses_by_order( $criteria, $orderby, $order, $operatingMode ) {
            $tables = $this->_get_table_names( $operatingMode );
            $sql = "SELECT * FROM {$tables['primary']}";
            $params = [];
            
            if ( ! empty( $criteria ) ) {
                $where_parts = $this->_build_where_clause_recursive( $criteria );
                
                if ( ! empty( $where_parts['sql'] ) ) {
                    $sql .= " WHERE " . $where_parts['sql'];
                    $params = $where_parts['params'];
                }
            }

 			// check orderby 
			if ($orderby != '') {
				$orderby = filter_var($orderby,FILTER_UNSAFE_RAW);
				$orderbyArray = explode(",",$orderby);
				$myFirst = true;
				$newOrderBy = '';
				foreach($orderbyArray as $columnName) {
					if ( $this->_is_valid_column($columnName)) {
						if ($myFirst) {
							$myFirst = false;
							$newOrderBy = $columnName;
						} else {
							$newOrderBy .= ",$columnName";
						}
					}
				}
				if ($newOrderBy != '') {
					$sql .= " ORDER BY $newOrderBy ";
					
					if ($order == 'ASC') {
						$sql .= 'ASC ';
					} else {
						$sql .= 'DESC ';
					}
				}
			}
			
            if ( ! empty( $params ) ) {
                $prepared_sql = $this->wpdb->prepare( $sql, $params );
                $results = $this->wpdb->get_results( $prepared_sql, ARRAY_A );
            } else {
                $results = $this->wpdb->get_results( $sql, ARRAY_A );
            }
            
            if ($results === FALSE || $results === NULL) {
            	$myStr = $this->wpdb->last_query;
            	error_log("CWA_Advisorclass_DAL ERROR get_advisorclasses_by_order returned FALSE/NULL\nSQL: $myStr");
            } else {
            	if (empty($results)) {
					$myQuery = $this->wpdb->last_query;
					error_log("CWA_Advisorclass_DAL INFORMATION No data retrieved with $myQuery");
            	}
            }
            return $results;
        }



        /**
         * 4. Gets a single advisorclass record by its ID.
         *
         * @param int    $advisorclass_id The ID of the class to retrieve.
         * @param string $operatingMode   'Production' or 'Testmode'.
         * @return object|null A single row object, or null if not found.
         */
        public function get_advisorclasses_by_id( $advisorclass_id, $operatingMode ) {
            $tables = $this->_get_table_names( $operatingMode );
            
            if ( ! is_numeric( $advisorclass_id ) ) {
              	error_log("CWA_Advisorclass_DAL ERROR Advisorclass_id of $advisorclass_id supplied for get_advisor_by_id is not numeric");
                return null;
            }

            $sql = $this->wpdb->prepare(
                "SELECT * FROM {$tables['primary']} WHERE advisorclass_id = %d",
                $advisorclass_id
            );
            
            return $this->wpdb->get_row( $sql, ARRAY_A );
        }

        /**
         * 5. Updates an advisorclass record by its ID and logs the action.
         *
         * @param int    $advisorclass_id The ID of the record to update.
         * @param array  $data            Associative array of [field_name => value] to update.
         * @param string $operatingMode   'Production' or 'Testmode'.
         * @return int|false Number of rows updated, or false on error.
         */
        public function update( $advisorclass_id, $data, $operatingMode ) {
            $tables = $this->_get_table_names( $operatingMode );
            $clean_data = $this->_filter_data( $data );
            
            if ( empty( $clean_data ) || ! is_numeric( $advisorclass_id ) ) {
            	error_log("CWA_Advisorclass_DAL update FATAL ERROR either clean data is empty or the advisorclass_id ($advisorclass_id) is not numeric");
                return false; // No valid data or ID
            }

            // --- Step 1: Get the call sign for logging ---
            $call_sign_for_log = $this->wpdb->get_var(
                $this->wpdb->prepare(
                    "SELECT advisorclass_call_sign FROM {$tables['primary']} WHERE advisorclass_id = %d",
                    $advisorclass_id
                )
            );

            if ( is_null( $call_sign_for_log ) ) {
            	error_log("CWA_AdvisorClass_DAL update ERROR unable to get call sign from advisorclass_id of $advisorclass_id");
                $call_sign_for_log = 'UNKNOWN'; // Record not found, but we still log the attempt
            }

            // --- Step 2: Log the change ---
            $this->_log_change(
                $call_sign_for_log,
                'update',
                $clean_data,
                $tables['primary'],
                $tables['log']
            );
            
            // --- Step 3: Perform the update ---
            $result = $this->wpdb->update(
                $tables['primary'],
                $clean_data,
                [ 'advisorclass_id' => $advisorclass_id ], // WHERE
                null,    // format for $data (auto-detected)
                [ '%d' ] // format for $where
            );
            
            if ($result === FALSE) {
            	$mySQL = $this->wpdb->last_query;
            	error_log("CWA_Advisorclass_DAL update ERROR updating advisorclass_id of $advisorclass_id ($advisorclass_id) failed\nSQL: $mySQL");
            }
            return $result;
        }

        /**
         * 6. Deletes an advisorclass record by its ID.
         * The record is copied to the deleted log table before being removed.
         *
         * @param int    $advisorclass_id The ID of the record to delete.
         * @param string $operatingMode   'Production' or 'Testmode'.
         * @return int|false Number of rows deleted, or false on error.
         */
        public function delete( $advisorclass_id, $operatingMode ) {
            $tables = $this->_get_table_names( $operatingMode );

            if ( ! is_numeric( $advisorclass_id ) ) {
            	error_log("CWA_Advisorclass_DAL ERROR delete $advisorclass_id has non-numeric id");
                return false;
            }

            // --- Step 1: Get the full record to be deleted ---
            $record_to_delete = $this->wpdb->get_row(
                $this->wpdb->prepare(
                    "SELECT * FROM {$tables['primary']} WHERE advisorclass_id = %d",
                    $advisorclass_id
                ),
                ARRAY_A // Get as associative array
            );

            if ( ! $record_to_delete ) {
            	error_log("CWA_Advisorclass_DAL INFORMATION deleting $advisorclass_id no record found to delete");
                return false; // Record not found, nothing to delete
            }

            // --- Step 2: Copy the record to the deleted table ---
            $copied = $this->wpdb->insert( $tables['deleted'], $record_to_delete );

            if ( ! $copied ) {
                // Failed to copy (e.g., table doesn't exist). Abort delete.
                error_log("CWA_Advisorclass_DAL ERROR copying $advisorclass_id to deleted table failed");
                return false;
            }

            // --- Step 3: Delete the original record ---
            $result = $this->wpdb->delete(
                $tables['primary'],
                [ 'advisorclass_id' => $advisorclass_id ], // WHERE
                [ '%d' ] // Format for WHERE
            );

   			if ($result === FALSE) {
				$myStr = $this->wpdb->last_query;
				error_log("CWA_Advisorclass_DAL ERROR deleting $advisorclass_id returned FALSE\nSQL: $myStr");
			}
          return $result;
        }

        /**
        * 7. Execute supplied SQL
        *
        * NOTE: This function will fill in the correct table name replacing TABLENAME 
        *	for example: select distinct(user_name) from TABLENAME where....
        *
        * @param string $SQL 	the sql to be run
        * @param string $operatingMode Production|Testmode
        * @return array|false results of get_results
        */
        
        public function run_sql($SQL, $operatingMode) {
            $tables = $this->_get_table_names( $operatingMode );
			$SQL = str_replace('TABLENAME',$tables['primary'],$SQL);
			
			$result = $this->wpdb->get_results($SQL, ARRAY_A); 
			
			if($result === FALSE) {
				$myStr = $this->wpdb->last_query;
				error_log("CWA_User_Master_DAL ERROR run_sql returned FALSE\nSQL: $myStr");
			}       
        
        	return $result;
        }

        // ---------------------------------------------------------------------
        // Private Helper Functions
        // ---------------------------------------------------------------------

        /**
         * Gets the correct set of table names based on the operating mode.
         *
         * @param string $mode 'Production' or 'Testmode'.
         * @return array Associative array of table names.
         */
        private function _get_table_names( $mode ) {
            if ( 'Production' === $mode ) {
                return [
                    'primary' => $this->wpdb->prefix . 'cwa_advisorclass',
                    'log'     => $this->wpdb->prefix . 'cwa_data_log',
                    'deleted' => $this->wpdb->prefix . 'cwa_deleted_advisorclass',
                ];
            } else {
                return [
                    'primary' => $this->wpdb->prefix . 'cwa_advisorclass2',
                    'log'     => $this->wpdb->prefix . 'cwa_data_log2',
                    'deleted' => $this->wpdb->prefix . 'cwa_deleted_advisorclass2',
                ];
            }
        }

        /**
         * Logs an action to the appropriate data log table.
         *
         * @param string $call_sign          The advisor's call sign.
         * @param string $action             The action (e.g., 'create', 'update').
         * @param array  $data               The data array to be logged as JSON.
         * @param string $primary_table_name The name of the table action was on.
         * @param string $log_table_name     The name of the log table to use.
         */
        private function _log_change( $call_sign, $action, $data, $primary_table_name, $log_table_name ) {
            
			$currentUser = $this->get_current_user_login();
            
            $log_data = [
                'data_date_written' => current_time( 'mysql' ),
                'data_user'			=> $currentUser,
                'data_call_sign'    => $call_sign,
                'data_table_name'   => $primary_table_name,
                'data_action'       => $action,
                'data_field_values' => wp_json_encode( $data )
            ];
            
            $this->wpdb->insert( $log_table_name, $log_data );
        }
        
        /**
         * Recursively builds a WHERE clause from a criteria array.
         */
        private function _build_where_clause_recursive( $criteria ) {
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
                    $nested_parts = $this->_build_where_clause_recursive( $clause );
                    
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
         * Filters an associative array to only include valid, known columns.
         */
        private function _filter_data( $data ) {
            return array_intersect_key( $data, array_flip( $this->valid_columns ) );
        }

        /**
         * Checks if a given string is a valid, known column name.
         */
        private function _is_valid_column( $column_name ) {
            return in_array( $column_name, $this->valid_columns, true );
        }
        
		/**
		 * Retrieves the username of the currently logged-in WordPress user.
		 * @return string The user_login string or 'Guest'.
		 */
		private function get_current_user_login(): string {
			// Ensure this runs after WordPress has loaded user data
			if ( ! function_exists( 'wp_get_current_user' ) ) {
				return 'System_Check_Error';
			}
			
			$current_user = wp_get_current_user();
	
			// Check if a user is logged in (ID > 0)
			if ( $current_user->ID > 0 ) {
				return $current_user->user_login;
			}
	
			// Return a default value if the user is not logged in
			return 'Guest'; 
		}

    } // end class CWA_Advisorclass_DAL

} // end if ! class_exists