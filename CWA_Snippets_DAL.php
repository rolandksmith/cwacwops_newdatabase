/**
 * CWA_Snippets_DAL (Data Access Layer)
 *
 * Manages all MySQL database interactions for the wpw1_snippets table.
 *
 * This class is STATELESS. The $operatingMode ('Production' or 'Testmode')
 * must be passed into every public method to determine which
 * set of tables (live or test) to use for that specific operation.
 */

if ( ! class_exists( 'CWA_Snippets_DAL' ) ) {

    class CWA_Snippets_DAL {

        /**
         * The WordPress database global object.
         * @var wpdb
         */
        private $wpdb;

        /**
         * A list of all valid column names for the snippets table.
         * Used to sanitize input data and prevent SQL injection.
         * @var array
         */
        private $valid_columns = [
            'id',
            'name',
            'description',
            'code',
            'tags',
            'scope',
            'priority',
            'active',
            'modified',
            'revision',
            'cloud_id',
            'condition_id',
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
         * 1. Inserts a new snippet record and logs the action.
         *
         * @param array  $data          Associative array of [field_name => value] to insert.
         * @param string $operatingMode 'Production' or 'Testmode'.
         * @return int|false The new id on success, false on error.
         */
        public function insert( $data, $operatingMode ) {
            $tables = $this->_get_table_names( $operatingMode );
            $clean_data = $this->_filter_data( $data );

            if ( empty( $clean_data ) ) {
            	error_log("CWA_Snippets_DAL ERROR No valid data submitted for an insert");
                return false;
            }

            $result = $this->wpdb->insert( $tables['primary'], $clean_data );

            if ( $result ) {
                $new_id = $this->wpdb->insert_id;

                $snippet_name = isset( $clean_data['name'] ) ? $clean_data['name'] : 'UNKNOWN';
                $this->_log_change(
                    $snippet_name,
                    'create',
                    $clean_data,
                    $tables['primary'],
                    $tables['log']
                );

                return $new_id;
            }

            $myStr = $this->wpdb->last_query;
			error_log("CWA_Snippets_DAL ERROR attempt to insert a record returned FALSE\nSQL: $myStr");
            return false;
        }

        /**
         * 2. Gets snippet records based on specified criteria.
         *
         * @param array  $criteria      Criteria for the WHERE clause (supports nested AND/OR).
         * @param string $orderby       Orderby columns separated by commas.
         * @param string $order         ASC|DESC
         * @param string $operatingMode 'Production' or 'Testmode'.
         * @return array|NULL Array of associative arrays on success, NULL on error.
         */
        public function get_snippet_by_order( $criteria, $orderby, $order, $operatingMode ) {
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
            	error_log("CWA_Snippets_DAL ERROR get_snippet_by_order returned FALSE/NULL\nSQL: $myStr");
            } else {
            	if (empty($results)) {
					$myQuery = $this->wpdb->last_query;
					error_log("CWA_Snippets_DAL INFORMATION No data retrieved with $myQuery");
            	}
            }

            return $results;
        }

        /**
         * 3. Gets a single snippet record by its ID.
         *
         * @param int    $id            The ID of the snippet to retrieve.
         * @param string $operatingMode 'Production' or 'Testmode'.
         * @return array|null A single row as associative array, or null if not found.
         */
        public function get_snippet_by_id( $id, $operatingMode ) {
            $tables = $this->_get_table_names( $operatingMode );

            if ( ! is_numeric( $id ) ) {
             	error_log("CWA_Snippets_DAL ERROR id of $id supplied for get_snippet_by_id is not numeric");
               return null;
            }

            $sql = $this->wpdb->prepare(
                "SELECT * FROM {$tables['primary']} WHERE id = %d",
                $id
            );

            return $this->wpdb->get_row( $sql, ARRAY_A );
        }

        /**
         * 4. Updates a snippet record by its ID and logs the action.
         *
         * @param int    $id            The ID of the record to update.
         * @param array  $data          Associative array of [field_name => value] to update.
         * @param string $operatingMode 'Production' or 'Testmode'.
         * @return int|false Number of rows updated, or false on error.
         */
        public function update( $id, $data, $operatingMode ) {
            $tables = $this->_get_table_names( $operatingMode );
            $clean_data = $this->_filter_data( $data );

            if ( empty( $clean_data ) || ! is_numeric( $id ) ) {
             	error_log("CWA_Snippets_DAL ERROR invalid data supplied for update");
               return false;
            }

            // --- Step 1: Get the name for logging ---
            $name_for_log = $this->wpdb->get_var(
                $this->wpdb->prepare(
                    "SELECT name FROM {$tables['primary']} WHERE id = %d",
                    $id
                )
            );

            if ( is_null( $name_for_log ) ) {
             	error_log("CWA_Snippets_DAL ERROR getting name from id ($id) returned NULL");
               $name_for_log = 'UNKNOWN';
            }

            // --- Step 2: Log the change ---
            $this->_log_change(
                $name_for_log,
                'update',
                $clean_data,
                $tables['primary'],
                $tables['log']
            );

            // --- Step 3: Perform the update ---
            $result = $this->wpdb->update(
                $tables['primary'],
                $clean_data,
                [ 'id' => $id ],
                null,
                [ '%d' ]
            );

 			if ($result === FALSE || $result === NULL) {
 				$myStr = $this->wpdb->last_query;
 				error_log("CWA_Snippets_DAL ERROR update returned NULL|FALSE\nSQL: $myStr");
 			}
            return $result;
        }

        /**
         * 5. Deletes a snippet record by its ID.
         * The record is copied to the deleted log table before being removed.
         *
         * @param int    $id            The ID of the record to delete.
         * @param string $operatingMode 'Production' or 'Testmode'.
         * @return int|false Number of rows deleted, or false on error.
         */
        public function delete( $id, $operatingMode ) {
            $tables = $this->_get_table_names( $operatingMode );

            if ( ! is_numeric( $id ) ) {
            	error_log("CWA_Snippets_DAL ERROR delete $id has non-numeric id");
                return false;
            }

            // --- Step 1: Get the full record to be deleted ---
            $record_to_delete = $this->wpdb->get_row(
                $this->wpdb->prepare(
                    "SELECT * FROM {$tables['primary']} WHERE id = %d",
                    $id
                ),
                ARRAY_A
            );

            if ( ! $record_to_delete ) {
            	error_log("CWA_Snippets_DAL INFORMATION deleting $id no record found to delete");
                return false;
            }

            $name_for_log = $record_to_delete['name'];

            // --- Step 2: Copy the record to the deleted table ---
            $copied = $this->wpdb->insert( $tables['deleted'], $record_to_delete );

            if ( ! $copied ) {
                error_log("CWA_Snippets_DAL ERROR copying $id to deleted table failed");
                return false;
            }

            // --- Step 3: Delete the original record ---
            $result = $this->wpdb->delete(
                $tables['primary'],
                [ 'id' => $id ],
                [ '%d' ]
            );

			if ($result === FALSE) {
				$myStr = $this->wpdb->last_query;
				error_log("CWA_Snippets_DAL ERROR deleting $id returned FALSE\nSQL: $myStr");
			} else {
				// --- Step 4: Log the change ---
				$this->_log_change(
					$name_for_log,
					'delete',
					array('id' => $id),
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
        *	for example: select distinct(name) from TABLENAME where....
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
				error_log("CWA_Snippets_DAL ERROR run_sql returned FALSE\nSQL: $myStr");
			}

			return $result;
		}

        /**
        * 7. Get a single value
        *
        * NOTE: This function will fill in the correct table name replacing TABLENAME
        *	for example: select count(*) from TABLENAME where....
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
			   error_log("CWA_Snippets_DAL ERROR get_single_value returned FALSE|NULL\nSQL: $myStr");
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
                    'primary' => $this->wpdb->prefix . 'snippets',
                    'log'     => $this->wpdb->prefix . 'cwa_data_log',
                    'deleted' => $this->wpdb->prefix . 'cwa_deleted_snippets',
                ];
            } else {
                return [
                    'primary' => $this->wpdb->prefix . 'snippets2',
                    'log'     => $this->wpdb->prefix . 'cwa_data_log2',
                    'deleted' => $this->wpdb->prefix . 'cwa_deleted_snippets2',
                ];
            }
        }

        /**
         * Logs an action to the appropriate data log table.
         *
         * @param string $snippet_name       The snippet name for identification.
         * @param string $action             The action (e.g., 'create', 'update').
         * @param array  $data               The data array to be logged as JSON.
         * @param string $primary_table_name The name of the table action was on.
         * @param string $log_table_name     The name of the log table to use.
         */
        private function _log_change( $snippet_name, $action, $data, $primary_table_name, $log_table_name ) {

            $user = wp_get_current_user();
            if ( $user->ID > 0 ) {
            	$theUser = $user->user_login;
            } else {
            	$theUser = 'CRON';
            }

            $log_data = [
                'data_date_written' => current_time( 'mysql' ),
                'data_user'			=> $theUser,
                'data_call_sign'    => $snippet_name,
                'data_table_name'   => $primary_table_name,
                'data_action'       => $action,
                'data_field_values' => wp_json_encode( $data )
            ];

            $myInsertResult = $this->wpdb->insert( $log_table_name, $log_data );
            if ($myInsertResult === FALSE || $myInsertResult === NULL) {
            	$myStr = $this->wpdb->last_error;
            	error_log("CWA_Snippets_DAL ERROR inserting into data_log returned FALSE|NULL. Error: $myStr");
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
                        continue;
                    }

                    $field = $clause['field'];
                    $value = isset( $clause['value'] ) ? $clause['value'] : '';
                    $compare = isset( $clause['compare'] ) ? strtoupper( $clause['compare'] ) : '=';

                    if ( ! in_array( $compare, [ '=', '!=', 'LIKE' ] ) ) {
                        $compare = '=';
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
			if ( ! function_exists( 'wp_get_current_user' ) ) {
				return 'System_Check_Error';
			}

			$current_user = wp_get_current_user();

			if ( $current_user->ID > 0 ) {
				return $current_user->user_login;
			}

			return 'Guest';
		}


    } // end class CWA_Snippets_DAL

} // end
