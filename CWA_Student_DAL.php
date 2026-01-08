/**
 * CWA_Student_DAL (Data Access Layer)
 *
 * Manages all MySQL database interactions for the CWA Student tables.
 *
 * This class is STATELESS. The $operatingMode ('Production' or 'Testmode')
 * must be passed into every public method to determine which
 * set of tables (live or test) to use for that specific operation.
 */

if ( ! class_exists( 'CWA_Student_DAL' ) ) {

    class CWA_Student_DAL {

        /**
         * The WordPress database global object.
         * @var wpdb
         */
        private $wpdb;

        /**
         * A list of all valid column names for the student table.
         * Used to sanitize input data and prevent SQL injection.
         * @var array
         */
        private $valid_columns = [
            'student_id',
            'student_call_sign',
            'student_time_zone',
            'student_timezone_offset',
            'student_youth',
            'student_age',
            'student_parent',
            'student_parent_email',
            'student_level',
            'student_class_language',
            'student_waiting_list',
            'student_request_date',
            'student_semester',
            'student_notes',
            'student_welcome_date',
            'student_email_sent_date',
            'student_email_number',
            'student_response',
            'student_response_date',
            'student_abandoned',
            'student_status',
            'student_action_log',
            'student_pre_assigned_advisor',
            'student_selected_date',
            'student_no_catalog',
            'student_hold_override',
            'student_messaging',
            'student_assigned_advisor',
            'student_advisor_select_date',
            'student_advisor_class_timezone',
            'student_hold_reason_code',
            'student_class_priority',
            'student_assigned_advisor_class',
            'student_promotable',
            'student_excluded_advisor',
            'student_survey_completion_date',
            'student_available_class_days',
            'student_intervention_required',
            'student_copy_control',
            'student_first_class_choice',
            'student_second_class_choice',
            'student_third_class_choice',
            'student_first_class_choice_utc',
            'student_second_class_choice_utc',
            'student_third_class_choice_utc',
            'student_catalog_options',
            'student_flexible',
            'student_date_created',
            'student_date_updated',
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
         * 1. Inserts a new student record and logs the action.
         *
         * @param array  $data          Associative array of [field_name => value] to insert.
         * @param string $operatingMode 'Production' or 'Testmode'.
         * @return int|false The new student_id on success, false on error.
         */
        public function insert( $data, $operatingMode ) {
            $tables = $this->_get_table_names( $operatingMode );
            $clean_data = $this->_filter_data( $data );

            if ( empty( $clean_data ) ) {
            	error_log("CWA_Student_DAL ERROR No valid data submitted for an insert");
                return false; // No valid data provided
            }

            // $wpdb->insert handles data sanitization
            $result = $this->wpdb->insert( $tables['primary'], $clean_data );

            if ( $result ) {
                $new_id = $this->wpdb->insert_id;
                
                // Log the 'create' action
                $call_sign = isset( $clean_data['student_call_sign'] ) ? $clean_data['student_call_sign'] : 'UNKNOWN';
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
			error_log("CWA_Student_DAL ERROR attempt to insert a record returned FALSE\nSQL: $myStr");
            return false;
        }

        /**
         * 2. Gets student records based on specified criteria.
         *
         * @param array  $criteria      Criteria for the WHERE clause (supports nested AND/OR).
         * @param string $orderby		orderby colums separated by commas
         * @param string $order			ASC|DESC
         * @param string $operatingMode 'Production' or 'Testmode'.
         * @return array|NULL Array of objects on success, NULL on error.
         */
        public function get_student_by_order( $criteria, $orderby, $order, $operatingMode ) {
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
					
					$regex = '/^(ASC|DESC)(?:\s+(?:LIMIT|Limit|limit)\s+([1-9]\d{0,3}))?$/i';
                    if (preg_match($regex, trim($order))) {
                        $sql .= $order;
                    } else {
                        $sql .= 'ASC';
                    }
				}
			}
			
            if ( ! empty( $params ) ) {
                $prepared_sql = $this->wpdb->prepare( $sql, $params );
                $results = $this->wpdb->get_results( $prepared_sql, ARRAY_A);
            } else {
                $results = $this->wpdb->get_results( $sql, ARRAY_A );
            }
            
            if ($results === FALSE || $results === NULL) {
            	$myStr = $this->wpdb->last_query;
            	error_log("CWA_Student_DAL ERROR get_student returned FALSE/NULL\nSQL: $myStr");
            } else {
            	if (empty($results)) {
					$myQuery = $this->wpdb->last_query;
					error_log("CWA_Student_DAL INFORMATION No data retrieved with $myQuery");
            	}
            }
            
            return $results;
        }

        /**
         * 3. Gets a single student record by its ID.
         *
         * @param int    $student_id    The ID of the student to retrieve.
         * @param string $operatingMode 'Production' or 'Testmode'.
         * @return object|null A single row object, or null if not found.
         */
        public function get_student_by_id( $student_id, $operatingMode ) {
            $tables = $this->_get_table_names( $operatingMode );
            
            if ( ! is_numeric( $student_id ) ) {
             	error_log("CWA_Student_DAL ERROR Student_id of $student_id supplied for get_student_by_id is not numeric");
               return null;
            }

            $sql = $this->wpdb->prepare(
                "SELECT * FROM {$tables['primary']} WHERE student_id = %d",
                $student_id
            );
            
            return $this->wpdb->get_row( $sql,ARRAY_A );
        }

        /**
         * 4. Updates a student record by its ID and logs the action.
         *
         * @param int    $student_id    The ID of the record to update.
         * @param array  $data          Associative array of [field_name => value] to update.
         * @param string $operatingMode 'Production' or 'Testmode'.
         * @return int|false Number of rows updated, or false on error.
         */
        public function update( $student_id, $data, $operatingMode ) {
            $tables = $this->_get_table_names( $operatingMode );
            $clean_data = $this->_filter_data( $data );
            
            if ( empty( $clean_data ) || ! is_numeric( $student_id ) ) {
             	error_log("CWA_Student_DAL ERROR invalid data supplied for update");
               return false; // No valid data or ID
            }

            // --- Step 1: Get the call sign for logging ---
            $call_sign_for_log = $this->wpdb->get_var(
                $this->wpdb->prepare(
                    "SELECT student_call_sign FROM {$tables['primary']} WHERE student_id = %d",
                    $student_id
                )
            );

            if ( is_null( $call_sign_for_log ) ) {
             	error_log("CWA_Student_DAL ERROR getting callsign from student_id ($student_id) returned NULL");
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
                [ 'student_id' => $student_id ], // WHERE
                null,    // format for $data (auto-detected)
                [ '%d' ] // format for $where
            );
            
 			if ($result === FALSE || $result === NULL) {
 				$myStr = $this->wpdb->last_query;
 				error_log("CWA_Student_DAL ERROR update returned NULL|FALSE\nSQL: $myStr");
 			}           
            return $result;
        }

        /**
         * 5. Deletes a student record by its ID.
         * The record is copied to the deleted log table before being removed.
         *
         * @param int    $student_id    The ID of the record to delete.
         * @param string $operatingMode 'Production' or 'Testmode'.
         * @return int|false Number of rows deleted, or false on error.
         */
        public function delete( $student_id, $operatingMode ) {
            $tables = $this->_get_table_names( $operatingMode );

            if ( ! is_numeric( $student_id ) ) {
            	error_log("CWA_Student_DAL ERROR delete $student_id has non-numeric id");
                return false;
            }

            // --- Step 1: Get the full record to be deleted ---
            $record_to_delete = $this->wpdb->get_row(
                $this->wpdb->prepare(
                    "SELECT * FROM {$tables['primary']} WHERE student_id = %d",
                    $student_id
                ),
                ARRAY_A // Get as associative array
            );

            if ( ! $record_to_delete ) {
            	error_log("CWA_Student_DAL INFORMATION deleting $student_id no record found to delete");
                return false; // Record not found, nothing to delete
            }
            
            $call_sign_for_log = $record_to_delete['student_call_sign'];

            // --- Step 2: Copy the record to the deleted table ---
            $copied = $this->wpdb->insert( $tables['deleted'], $record_to_delete );

            if ( ! $copied ) {
                // Failed to copy (e.g., table doesn't exist). Abort delete.
                error_log("CWA_Student_DAL ERROR copying $student_id to deleted table failed");
                return false;
            }

            // --- Step 3: Delete the original record ---
            $result = $this->wpdb->delete(
                $tables['primary'],
                [ 'student_id' => $student_id ], // WHERE
                [ '%d' ] // Format for WHERE
            );

			if ($result === FALSE) {
				$myStr = $this->wpdb->last_query;
				error_log("CWA_Student_DAL ERROR deleting $student_id returned FALSE\nSQL: $myStr");
			} else {
				// --- Step 4: Log the change ---
				$this->_log_change(
					$call_sign_for_log,
					'delete',
					array('student_id' => $student_id),
					$tables['primary'],
					$tables['log']
				);
			}
            return $result;
        }
        
        
        /**
        * 6. Execute supplied SQL
        *
        * NOTE: This function will fill in the correct table name replacing TABLENAME 
        *	for example: select distinct(user_name) from TABLENAME where....
        *
        * @param string $SQL 	the sql to be run
        * @param string $operatingMode Production|Testmode
        * @param array $params 	the parameters for the placeholders in the SQL
        * @return array|false results of get_results
        */
		public function run_sql($SQL, $operatingMode, $params = []) {
			$tables = $this->_get_table_names($operatingMode);
			$SQL = str_replace('TABLENAME', $tables['primary'], $SQL);
			if(!empty($params)) $SQL = $this->wpdb->prepare($SQL, $params);
			
			$result = $this->wpdb->get_results($SQL, ARRAY_A);
			
			if($result === FALSE) {
				$myStr = $this->wpdb->last_query;
				error_log("CWA_Student_DAL ERROR run_sql returned FALSE\nSQL: $myStr");
			}
			
			return $result;
		}

        /**
        * 7. Get a single value
        *
        * NOTE: This function will fill in the correct table name replacing TABLENAME 
        *	for example: select distinct(user_name) from TABLENAME where....
        *
        * @param string $SQL 	the sql to be run
        * @param string $operatingMode Production|Testmode
        * @param array $params 	the parameters for the placeholders in the SQL
        * @return string|int|null	a single value
        */
        public function get_single_value($SQL, $operatingMode, $params = []) {
            $tables = $this->_get_table_names($operatingMode);
            $SQL = str_replace('TABLENAME', $tables['primary'], $SQL);
            if(!empty($params)) $SQL = $this->wpdb->prepare($SQL, $params);
			 $result = $this->wpdb->get_var($SQL);
		   if($result === FALSE || $result === NULL) {
				$myStr = $this->wpdb->last_query;
			   error_log("CWA_Student_DAL ERROR get_single_value returned FALSE|NULL\nSQL: $myStr");
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
                    'primary' => $this->wpdb->prefix . 'cwa_student',
                    'log'     => $this->wpdb->prefix . 'cwa_data_log',
                    'deleted' => $this->wpdb->prefix . 'cwa_deleted_student',
                ];
            } else {
                return [
                    'primary' => $this->wpdb->prefix . 'cwa_student2',
                    'log'     => $this->wpdb->prefix . 'cwa_data_log2',
                    'deleted' => $this->wpdb->prefix . 'cwa_deleted_student2',
                ];
            }
        }

        /**
         * Logs an action to the appropriate data log table.
         *
         * @param string $call_sign          The student's call sign.
         * @param string $action             The action (e.g., 'create', 'update').
         * @param array  $data               The data array to be logged as JSON.
         * @param string $primary_table_name The name of the table action was on.
         * @param string $log_table_name     The name of the log table to use.
         */
        private function _log_change( $call_sign, $action, $data, $primary_table_name, $log_table_name ) {

            $user = wp_get_current_user();
            if ( $user->ID > 0 ) {
            	$theUser = $user->user_login;
            } else {
            	$theUser = 'CRON';
            }
            
            $log_data = [
                'data_date_written' => current_time( 'mysql' ),
                'data_user'			=> $theUser,
                'data_call_sign'    => $call_sign,
                'data_table_name'   => $primary_table_name,
                'data_action'       => $action,
                'data_field_values' => wp_json_encode( $data )
            ];
            
            $myInsertResult = $this->wpdb->insert( $log_table_name, $log_data );
            if ($myInsertResult === FALSE || $myInsertResult === NULL) {
            	$myStr = $this->wpdb->last_error;
            	error_log("CWA_Student_DAL ERROR inserting into data_log returned FALSE|NULL. Error: $myStr");
            }
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


    } // end class CWA_Student_DAL

} // end 