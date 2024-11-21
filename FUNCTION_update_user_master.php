function update_user_master($inpArray) {

/*	add / update / delete user_master records

	general call process
	$userMasterData			= array('tableName'=>$userMasterTableName,
									'inp_method'=>'update',
									'inp_data'=>$updateParams,
									'inp_format'=>$updateFormat,
									'jobname'=>$jobname,
									'inp_id'=>$user_id,
									'inp_callsign'=>$user_callsign,
									'inp_who'=>$userName,
									'testMode'=>$testMode,
									'doDebug'=>$doDebug);
	$updateResult	= update_user_master($userMasterData);
	if ($updateResult[0] === FALSE) {
		handleWPDBError($jobname,$doDebug);
	} else {

	inp_method should be one of 'add', 'update', 'delete'
	
	Returns an array of two values:
		TRUE or FALSE
		Reason if FALSE


	created 4Oct24 by Roland
*/

	global $wpdb;

	$tableName					= '';
	$inp_method					= '';
	$inp_data					= array();
	$inp_format					= array('Not Specified');
	$jobname					= '';
	$inp_id						= '';
	$inp_callsign				= '';
	$inp_who					= '';
	$testMode					= FALSE;
	$doDebug					= TRUE;
	

	foreach($inpArray as $fieldName=>$fieldValue) {
		${$fieldName}			= $fieldValue;
	}

	if ($inp_who == '' && $inp_callsign != '') {
		$inp_who				= $inp_callsign;	
	}

	if ($testMode) {
		$userMasterHistoryTableName	= 'wpw1_cwa_user_master_history2';
		$userMasterTableName		= 'wpw1_cwa_user_master2';
		$userMasterDeletedTableName	= 'wpw1_cwa_user_master_deleted2';
	} else {
		$userMasterHistoryTableName	= 'wpw1_cwa_user_master_history';
		$userMasterTableName		= 'wpw1_cwa_user_master';
		$userMasterDeletedTableName	= 'wpw1_cwa_user_master_deleted';
	}

	if ($doDebug) {
		echo "<br /><b>Function: update_user_master</b><br />
			  inp_method: $inp_method<br />
			  jobname: $jobname<br />
			  inp_id: $inp_id<br />
			  inp_who: $inp_who<br />
			  testMode: $testMode<br />
			  doDebug: $doDebug<br />";
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
		} else {
			$doAdd			= TRUE;
			$doUpdate		= TRUE;
		}
	} elseif ($inp_method == 'update') {
		$doUpdate		= TRUE;
	} elseif ($inp_method == 'delete') {
		if ($inp_id == '' || $inp_id == 0 || $inp_id == '0') {
			if ($doDebug) {
				echo "inp_id is missing or invalid<br />";
			}
			return array(FALSE,'inp_id is missing or invalid');
		} else {
			$doDelete		= TRUE;
		}
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
		if ($inp_who == '') {
			if ($doDebug) {
				echo "inp_who is empty<br />";
			}
			$errorMsg					= "Function update_user_master. inp_who is empty.<br />
				  jobname: $jobname<br />
				  inp_id: $inp_id<br />
				  inp_who: $inp_who<br />";
			sendErrorEmail($errorMsg);
		}
	}
	
	$updateHistory		= FALSE;
	if ($doAdd) {
		if ($doDebug) {
			echo "<br /><b>Doing the Insert</b><br />";
		}
		$updateParams	= array('user_call_sign'=>$inp_callsign,
					  		    'user_action_log'=>"Record created by update_advisor function ");

		$addResult		= $wpdb->insert($userMasterTableName,
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
		$updateHistory	= TRUE;
		
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
		$thisData						= $wpdb->get_var("select user_call_sign from $userMasterTableName where user_id = $inp_id");
		if ($thisData == NULL) {				// no such record
			if ($doDebug) {
				echo "no record with user_id of $inp_id found in $userMasterTableName to update<br />";
			}
			return array(FALSE,"no record with user_id of $inp_id found in $userMasterTableName to update");
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
			echo "</pre><br />";
		}
		$result		= $wpdb->update($userMasterTableName, 
									$updateParams, 
									array('user_id'=>$inp_id), 
									$updateFormat,
									array('%d'));
		if ($result === FALSE) {
			if ($doDebug) {
				echo "Updating $userMasterTableName with id $inp_id failed<br />
					  wpdb->last_query: " . $wpdb->last_query . "<br />
					  <b>wpdb->last_error: " . $wpdb->last_error . "</b><br />";
				$errorMsg					= $wpdb->last_error;
				$mySQL						= $wpdb->last_query;
				$myStr						= $wpdb->last_error;
				$errorMsg					= "FUNCTION update_user_master failed attempting to update record $inp_id.<br /> 
											   Last error: $myStr.<br />Last query: $mySQL";
				sendErrorEmail($errorMsg);
				return array(FALSE,$errorMsg);
			}
		} else {
			if ($doDebug) {
				echo "Successfully updated $tableName record at $inp_id<br />";
			}
			$updateHistory		= TRUE;
		}
	}


	if ($doDelete) {
		// make sure there is a record to be deleted
		$thisData						= $wpdb->get_var("select user_call_sign from $userMasterTableName where user_id = $inp_id");
		if ($thisData == NULL) {				// no such record
			if ($doDebug) {
				echo "no record with id of $inp_id found in $tableName to delete<br />";
			}
			return array(FALSE,"no record with id of $inp_id found in $tableName to delete");
		} else {
			if ($doDebug) {
				echo "found a record for $inp_id to be deleted from $tableName<br />";
			}
		}
		// now see if there is a record by this id in the deleted table. If so, delete it
		$thisData			= $wpdb->get_var("select user_call_sign from $userMasterDeletedTableName where user_id = $inp_id");
		if ($thisData != NULL && $thisData != 0) {
			$thisDelete		= $wpdb->delete($deleteTable,
											array('user_id'=>$inp_id),
											array('%d'));
			if ($doDebug) {
				echo "deleted $inp_id record from $userMasterDeletedTableName. Result:<br /><pre>";
				var_dump($thisDelete);
				echo "</pre><br />";
			}
		} else {
			if ($doDebug) {
				echo "no record for $inp_id found in $userMasterDeletedTableName<br />";
			}
		}
		// now copy the advisor record to be deleted to the deleted table
		$myResult	= $wpdb->get_results("insert into $userMasterDeletedTableName 
											select * from $userMasterTableName 
											where user_id=$inp_id");

		if (sizeof($myResult) != 0 || $myResult === FALSE) {
			echo "adding $inp_id to $userMasterDeletedTableName table failed<br />";
			echo "wpdb->last_query: " . $wpdb->last_query . "<br />";
			$myStr				= $wpdb->last_error;
			sendErrorEmail("$jobname: attempting to move $inp_id from $userMasterTableName to $userMasterDeletedTableName failed. Last error: $myStr");
			return array(FALSE,"attempting to move $inp_id from $userMasterTableName to $userMasterDeletedTableName failed");
		} else {
			if ($doDebug) {
				echo "copied advisor record $inp_id to $deleteTable<br />";
			}
			$deleteResult			= $wpdb->delete($tableName,
													array('user_id'=>$inp_id),
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
					echo "deleted record $inp_id from $userMasterTableName<br />";
					$updateParams		= array('email'=>'record deleted');
					$updateHistory		= TRUE;
				}
			}
		}
	}
	
	if ($updateHistory) {
		if ($doDebug) {
			echo "updating the history<br />";
		}
		// write the history log
		if ($testMode) {
			$history_mode		= 'TESTMODE';
		} else {
			$history_mode		= 'PRODUCTION';
		}
		$updateParams		= json_encode($updateParams);
		$submitArray		= array('historymode'=>$history_mode,
									'historydate'=>date('Y-m-d H:i:s'),
									'historyprogram'=>$jobname,
									'historywho'=>$inp_who,
									'historycallsign'=>$inp_callsign,
									'historyid'=>$inp_id,
									'historydata'=>$updateParams);
									
									
		$updateResult		= $wpdb->insert($userMasterHistoryTableName, 
											 $submitArray,
											 array('%s','%s','%s','%s','%s','%d','%s'));
		if ($updateResult === FALSE) {
			if ($doDebug) {
				echo "inserting into $userMasterHistoryTableName failed<br />";
			}
		} else {
			if ($doDebug) { 
				echo "history record successfully processed<br />";
			}
		}
	}
	
		
	return array(TRUE,$inp_id);
}
add_action('update_user_master','update_user_master');