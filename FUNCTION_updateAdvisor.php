function updateAdvisor($inpArray) {

/*	add, update, or delete advisor information 

	The inpArray contains these elements:
		
		tableName		name of the table to be updated
							must be one of 	wpw1_cwa_advisor
											wpw1_cwa_advisor2
		inp_method		add / update / delete
		inp_data:		an array of table fields to be updated
							either in the format of
								'fieldname'=>fieldvalue or 
								'fieldname|fieldvalue|s/d/f'
							Not needed if method is delete
		inp_format		an array of format placeholders
							set as an empty array (or leave out) if inp_data is in the
							format of 'fieldname|fieldvalue|s/d/f'
							Not needed if method is delete
		jobname			the name of the job calling the update
							max length 30 characters
		inp_id			the id of the record to be updated or deleted
							required if the method is delete 
		inp_callsign	the advisor callsign
							must always be included
		inp_semester	the semester for the data
							also a required field
		inp_who			the logged in user
		testMode		the boolean value for testMode (optional)
		doDebug			the booelan value for doDebug (optional)

		if the method is 'add', the function will first add a generic record with only 
			the call sign filled in. It will then get the record id of the new record
			and do an update based on the provided input parameters
			
		If the method is 'update' and the record identified by inp_id exists, the 
			fields supplied in the update parameters will be updated
			
		If the method is 'delete' the record specified in inp_id first be moved to 
			the deleted table and will then be deleted regardless of content
		
		Example Template:
				$advisorUpdateData		= array('tableName'=>$advisorNewTableName,
												'inp_method'=>'update',
												'inp_data'=>$updateParams,
												'inp_format'=>$updateFormat,
												'jobname'=>'',
												'inp_id'=>$advisor_ID,
												'inp_callsign'=>$advisor_call_sign,
												'inp_semester'=>$advisor_semester,
												'inp_who'=>$userName,
												'testMode'=>$testMode,
												'doDebug'=>$doDebug);
				$updateResult	= updateAdvisor($advisorUpdateData);
				if ($updateResult[0] === FALSE) {
					handleWPDBError($jobname,$doDebug);
				} else {
		
	returns array(TRUE,advisor_id) if update was successful
			array(FALSE,'reason') if not successful


*/


	global $wpdb;

	$tableName					= '';
	$inp_method					= '';
	$inp_data					= array();
	$inp_format					= array('Not Specified');
	$jobname					= '';
	$inp_id						= '';
	$inp_semester				= '';
	$inp_callsign				= '';
	$inp_who					= '';
	$testMode					= FALSE;
	$doDebug					= TRUE;

	foreach($inpArray as $fieldName=>$fieldValue) {
		$$fieldName			= $fieldValue;
	}

	if ($inp_who == '' && $inp_callsign != '') {
		$inp_who				= $inp_callsign;	
	}

	$tableNameArray			= array('wpw1_cwa_advisor'=>'wpw1_cwa_deleted_advisor',
									'wpw1_cwa_advisor2'=>'wpw1_cwa_deleted_advisor');		
	$fieldTest				= array('action_log','control_code');
		
	if ($doDebug) {
		echo "<br /><b>Function: updateAdvisor</b><br />
			  inp_method: $inp_method<br />
			  jobname: $jobname<br />
			  inp_id: $inp_id<br />
			  inp_semester: $inp_semester<br />
			  inp_who: $inp_who<br />
			  testMode: $testMode<br />
			  doDebug: $doDebug<br />";
	}
	if (!array_key_exists($tableName,$tableNameArray)) {
		if ($doDebug) {
			echo "tableName of $tableName invalid<br />";
		}
		return array(FALSE,"tableName of $tableName invalid. No update done");
	}
	
	$doAdd				= FALSE;
	$doUpdate			= FALSE;
	$doDelete			= FALSE;
	if ($inp_method == 'add') {
		if ($inp_callsign == '') {
			if ($doDebug) {
				echo "callsign is missing<br />";
			}
			return array(FALSE,"callsign is missing");
		}
		$doAdd			= TRUE;
		$doUpdate		= TRUE;
	} elseif ($inp_method == 'update') {
		$doUpdate		= TRUE;
	} elseif ($inp_method == 'delete') {
		if ($inp_id == '' || $inp_id == 0 || $inp_id == '0') {
			if ($doDebug) {
				echo "inp_id is missing or invalid<br />";
			}
			return array(FALSE,'inp_id is missing or invalid');
		}
		$doDelete		= TRUE;
	} else {
		if ($doDebug) {
			echo "inp_method of $inp_method invalid<br />";
		}
		return array(FALSE,"inp_method of $inp_method is invalid");
	}
	
	if ($doUpdate) {
		if (count($inp_data) == 0) {
			if ($doDebug) {
				echo "inp_data is invalid<br />";
			}
			return array(FALSE,'invalid input data array. No update done');
		}
		if ($jobname == '') {
			if ($doDebug) {
				echo "jobname is empty<br />";
			}
			return array(FALSE,'invalid jobname. No update done');
		}
		if ($inp_semester == '') {
			if ($doDebug) {
				echo "inp_semester is empty<br />";
			}
			return array(FALSE,'invalid input semester. No update done');
		}
		if ($inp_who == '') {
			if ($doDebug) {
				echo "inp_who is empty<br />";
			}
	//		return array(FALSE,'invalid input who. No update done');
			$errorMsg					= "Function updateAdvisor. inp_who is empty.<br />
				  jobname: $jobname<br />
				  inp_id: $inp_id<br />
				  inp_semester: $inp_semester<br />
				  inp_who: $inp_who<br />";
			sendErrorEmail($errorMsg);
		}
	}
	
	if ($doAdd) {
		if ($doDebug) {
			echo "<br /><b>Doing the Insert</b><br />";
		}
		$updateParams	= array('advisor_call_sign'=>$inp_callsign,
					  		    'advisor_action_log'=>"Record created by update_advisor function ");

		$addResult		= $wpdb->insert($tableName,
										$updateParams, 
										array('%s','%s'));
		if ($addResult === FALSE) {
			$thisSQL	= $wpdb->last_query;
			$thisError	= $wpdb->last_error;
			if ($doDebug) {
				echo "Adding record for $inp_callsign failed.<br />SQL: $thisSQL<br />Error: $thisError<br />";
			}
			return array(FALSE,"Adding record for $inp_callsign failed. $thisError");
		}
		$inp_id			= $wpdb->insert_id;
		if ($doDebug) {
			echo "add was successful. Got inp_id of $inp_id<br />";
		}
		// write the audit log
		if ($testMode) {
			$log_mode		= 'TESTMODE';
		} else {
			$log_mode		= 'PRODUCTION';
		}
		$submitArray		= array('logtype'=>'ADVISOR',
									'logmode'=>$log_mode,
									'logdate'=>date('Y-m-d H:i:s'),
									'logprogram'=>$jobname,
									'logwho'=>$inp_who,
									'logcallsign'=>$inp_callsign,
									'logid'=>$inp_id,
									'logsemester'=>$inp_semester,
									'logsequence'=>0, 
									'logdata'=>$updateParams);
		$result		= storeAuditLogData($submitArray,$doDebug);
		if ($result[0] === FALSE) {
			if ($doDebug) {
				echo "storeAuditLogData failed: $result[1]<br />";
			}
		} else {
			if ($doDebug) {
				echo "audit log record successfully processed<br />";
			}
		}
	}
	
	if ($doUpdate) {
		if ($doDebug) {
			echo "<br /><b>Doing the Update</b><br />";
		}
		if ($inp_id == '') {
			if ($doDebug) {
				echo "inp_id is empty<br />";
			}
			return array(FALSE,'invalid input id. No update done');
		}
		// make sure there is a record to be updated
		$thisData						= $wpdb->get_var("select advisor_call_sign from $tableName where advisor_id = $inp_id");
		if ($thisData == NULL) {				// no such record
			if ($doDebug) {
				echo "no record with advisor_id of $inp_id found in $tableName to update<br />";
			}
			return array(FALSE,"no record with advisor_id of $inp_id found in $tableName to update");
		} else {
			if ($doDebug) {
				echo "There is a record to be updated<br />";
			}
		}
	
		// convert inp_data to updateParams and updateFormat
	
		$updateParams					= array();
		$updateFormat					= array();
		if ($inp_format[0] == 'Not Specified' || $inp_format[0] == '') {
			if ($doDebug) {
				echo "creating the updateFormat array from: <br /><pre>";
				print_r($updateParams);
				echo "</pre><br />";
			}
			foreach($inp_data as $myValue) {
				$myArray				= explode("|",$myValue);
				$field					= $myArray[0];
				$fieldValue				= $myArray[1];
			
				$fieldFormat			= $myArray[2];
				$updateParams[$field]	= $fieldValue;
				$updateFormat[]			= "%$fieldFormat";
			}
		} else {
			$updateParams				= $inp_data;
			$updateFormat				= $inp_format;
		}
	
		if ($doDebug) {
			echo "Ready to do the update.<br />updateParams:<br /><pre>";
			print_r($updateParams);
			echo "</pre><br />
				  updateFormat:<br /><pre>";
			print_r($updateFormat);
			echo "</pre><br />";
		}
		$result		= $wpdb->update($tableName, 
									$updateParams, 
									array('advisor_id'=>$inp_id), 
									$updateFormat,
									array('%d'));
		if ($result === FALSE) {
			if ($doDebug) {
				echo "Updating $tableName with id $inp_id failed<br />
					  wpdb->last_query: " . $wpdb->last_query . "<br />
					  <b>wpdb->last_error: " . $wpdb->last_error . "</b><br />";
				$errorMsg					= $wpdb->last_error;
				$mySQL						= $wpdb->last_query;
				$myStr						= $wpdb->last_error;
				$errorMsg					= "FUNCTION updateAdvisor failed attempting to update record $inp_id.<br /> 
											   Last error: $myStr.<br />Last query: $mySQL";
				sendErrorEmail($errorMsg);
				return array(FALSE,$errorMsg);
			}
		} else {
			if ($doDebug) {
				echo "Successfully updated $tableName record at $inp_id<br />";
			}

			// write the advisor audit log record
			if ($testMode) {
				$log_mode		= 'TESTMODE';
			} else {
				$log_mode		= 'PRODUCTION';
			}
			$submitArray		= array('logtype'=>'ADVISOR',
										'logmode'=>$log_mode,
										'logdate'=>date('Y-m-d H:i:s'),
										'logprogram'=>$jobname,
										'logwho'=>$inp_who,
										'logcallsign'=>$inp_callsign,
										'logid'=>$inp_id,
										'logsemester'=>$inp_semester,
										'logsequence'=>0, 
										'logdata'=>$updateParams);
			$result		= storeAuditLogData($submitArray,$doDebug);
			if ($result[0] === FALSE) {
				if ($doDebug) {
					echo "storeAuditLogData failed: $result[1]<br />";
				}
			} else {
				if ($doDebug) {
					echo "audit log record successfully processed<br />";
				}
			}
		}
	}


	if ($doDelete) {
		// make sure there is a record to be deleted
		$thisData						= $wpdb->get_var("select advisor_action_log from $tableName where advisor_id = $inp_id");
		if ($thisData == NULL) {				// no such record
			if ($doDebug) {
				echo "no record with advisor_id of $inp_id found in $tableName to delete<br />";
			}
			return array(FALSE,"no record with advisor_id of $inp_id found in $tableName to delete");
		} else {
			if ($doDebug) {
				echo "found a record for $inp_id to be deleted from $tableName<br />";
			}
		}
		// check to see if there is a deleted table
		$deleteTable			= $tableNameArray[$tableName];
		if ($deleteTable != 'No Deleted') {			/// no deleted table for past_advisor
			// now see if there is a record by this id in the deleted table. If so, delete it
			$thisData			= $wpdb->get_var("select advisor_call_sign from $deleteTable where advisor_id = $inp_id");
			if ($thisData != NULL) {
				$thisDelete		= $wpdb->delete($deleteTable,
												array('advisor_id'=>$inp_id),
												array('%d'));
				if ($doDebug) {
					echo "deleted $inp_id record from $deleteTable. Result:<br /><pre>";
					var_dump($thisDelete);
					echo "</pre><br />";
				}
			} else {
				if ($doDebug) {
					echo "no record for $inp_id found in $deleteTable<br />";
				}
			}
			// now copy the advisor record to be deleted to the deleted table
			$myResult	= $wpdb->get_results("insert into $deleteTable 
												select * from $tableName 
												where advisor_id=$inp_id");
	
			if (sizeof($myResult) != 0 || $myResult === FALSE) {
				echo "adding $inp_id to $deleteTable table failed<br />";
				echo "wpdb->last_query: " . $wpdb->last_query . "<br />";
				$myStr				= $wpdb->last_error;
				sendErrorEmail("$jobname: attempting to move $inp_id from $tableName to $deleteName failed. Last error: $myStr");
				return array(FALSE,"attempting to move $inp_id from $tableName to $deleteName failed");
			} else {
				if ($doDebug) {
					echo "copied advisor record $inp_id to $deleteTable<br />";
				}
				$deleteResult			= $wpdb->delete($tableName,
														array('advisor_id'=>$inp_id),
														array('%d'));
				if ($deleteResult === FALSE) {
					$thisSQL	= $wpdb->last_query;
					$thisError	= $wpdb->last_error;
					if ($doDebug) {
						echo "Deleting record $inp_id failed.<br />SQL: $thisSQL<br />Error: $thisError<br />";
					}
					return array(FALSE,"Deleting record $inp_idfailed. $thisError");
				} else {
					if ($doDebug) {
						echo "deleted record $inp_id from $tableName<br />";
					}
					// write the advisor audit log record
					if ($testMode) {
						$log_mode		= 'TESTMODE';
					} else {
						$log_mode		= 'PRODUCTION';
					}
					$actionDate			= date('Y-m-d H:i:s');
					$thisData			.= " / $actionDate UdateAdvisor $inp_who record deleted ";
					$updateParams		= array('advisor_action_log'=>$thisData);
					$submitArray		= array('logtype'=>'ADVISOR',
												'logmode'=>$log_mode,
												'logdate'=>date('Y-m-d H:i:s'),
												'logprogram'=>$jobname,
												'logwho'=>$inp_who,
												'logcallsign'=>$inp_callsign,
												'logid'=>$inp_id,
												'logsemester'=>$inp_semester,
												'logsequence'=>0, 
												'logdata'=>$updateParams);
					$result		= storeAuditLogData($submitArray,$doDebug);
					if ($result[0] === FALSE) {
						if ($doDebug) {
							echo "storeAuditLogData failed: $result[1]<br />";
						}
					}
				}
			}
		}
	}
		
	return array(TRUE,$inp_id);
}
add_action('updateAdvisor','updateAdvisor');