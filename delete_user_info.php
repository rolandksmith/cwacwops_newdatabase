function delete_user_info_func() {

	global $wpdb;

	$doDebug						= FALSE;
	$testMode						= FALSE;
	$initializationArray 			= data_initialization_func();
	$validUser 						= $initializationArray['validUser'];
/*
	if ($validUser == 'N') {				// turn off debug and testmode
		$doDebug					= FALSE;
		$testMode					= FALSE;
	}
*/
	$versionNumber				 	= "1";
	if ($doDebug) {
		echo "Initialization Array:<br /><pre>";
		print_r($initializationArray);
		echo "</pre><br />";
	}
	$userName			= $initializationArray['userName'];
	$currentTimestamp	= $initializationArray['currentTimestamp'];
	$validTestmode		= $initializationArray['validTestmode'];
	$siteURL			= $initializationArray['siteurl'];
	$userRole			= $initializationArray['userRole'];
	$userName			= $initializationArray['userName'];
	$userEmail			= $initializationArray['userEmail'];
	$userDisplayName	= $initializationArray['userDisplayName'];
	$currentTimestamp	= $initializationArray['currentTimestamp'];
	$validTestmode		= $initializationArray['validTestmode'];
	$siteURL			= $initializationArray['siteurl'];
	$currentSemester	= $initializationArray['currentSemester'];
	$nextSemester		= $initializationArray['nextSemester'];
	$semesterTwo		= $initializationArray['semesterTwo'];
	$semesterThree		= $initializationArray['semesterThree'];
	$semesterFour		= $initializationArray['semesterFour'];
	$advisorTableName	= "wpw1_cwa_consolidated_advisor";
	$studentTableName	= "wpw1_cwa_consolidated_student";
	$tempTableName		= "wpw1_cwa_temp_data";
	
//	CHECK THIS!								//////////////////////
	if ($userRole != 'administrator') {
		return "YOU'RE NOT AUTHORIZED!<br />Goodby";
	}

//	ini_set('memory_limit','256M');
//	ini_set('max_execution_time',0);
//	set_time_limit(0);

//	if ($doDebug) {
		ini_set('display_errors','1');
		error_reporting(E_ALL);	
//	}

/// get the time that the process started
	$startingMicroTime			= microtime(TRUE);

	$strPass					= "1";
	$theURL						= "$siteURL/cwa-delete-user-info/";
	$inp_semester				= '';
	$inp_rsave					= '';
	$jobname					= "Delete User Info V$versionNumber";

// get the input information
	if (isset($_REQUEST)) {
		foreach($_REQUEST as $str_key => $str_value) {
			if ($doDebug) {
				if (!is_array($str_value)) {
					echo "Key: $str_key | Value: $str_value <br />\n";
				} else {
					echo "Key: $str_key (array)<br />\n";
				}
			}
			if ($str_key 		== "strpass") {
				$strPass		 = $str_value;
				$strPass		 = filter_var($strPass,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "inp_verbose") {
				$inp_verbose	 = $str_value;
				$inp_verbose	 = filter_var($inp_verbose,FILTER_UNSAFE_RAW);
				if ($inp_verbose == 'Y') {
					$doDebug	= TRUE;
				}
			}
			if ($str_key 		== "inp_mode") {
				$inp_mode	 = $str_value;
				$inp_mode	 = filter_var($inp_mode,FILTER_UNSAFE_RAW);
				if ($inp_mode == 'TESTMODE') {
					$testMode = TRUE;
				}
			}
			if ($str_key 		=== "inp_type") {
				$inp_type		= $str_value;
				$inp_type		= filter_var($inp_type,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		=== "inp_value") {
				$inp_value		= $str_value;
				$inp_value		= filter_var($inp_value,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		=== "deleteSignup") {
				$deleteSignup		= $str_value;
				$deleteSignup		= filter_var($deleteSignup,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		=== "user_login") {
				$user_login		= strtoupper($str_value);
				$user_login		= filter_var($user_login,FILTER_UNSAFE_RAW);
			}
			if ($str_key == 'user_id') {
				$user_id		= $str_value;
				$user_id		= filter_var($user_id,FILTER_UNSAFE_RAW);
			}
			if ($str_key == 'userMaster_id') {
				$userMaster_id		= $str_value;
				$userMaster_id		= filter_var($userMaster_id,FILTER_UNSAFE_RAW);
			}
			if ($str_key == 'userMaster_call_sign') {
				$userMaster_call_sign		= $str_value;
				$userMaster_call_sign		= filter_var($userMaster_call_sign,FILTER_UNSAFE_RAW);
			}
			if ($str_key == 'student_call_sign') {
				$student_call_sign		= $str_value;
				$student_call_sign		= filter_var($student_call_sign,FILTER_UNSAFE_RAW);
			}
			if ($str_key == 'advisor_call_sign') {
				$advisor_call_sign		= $str_value;
				$advisor_call_sign		= filter_var($advisor_call_sign,FILTER_UNSAFE_RAW);
			}
			if ($str_key == 'advisorClass_call_sign') {
				$advisorClass_call_sign		= $str_value;
				$advisorClass_call_sign		= filter_var($advisorClass_call_sign,FILTER_UNSAFE_RAW);
			}
			if ($str_key == 'hasUserRecord') {
				$hasUserRecord		= $str_value;
				$hasUserRecord		= filter_var($hasUserRecord,FILTER_UNSAFE_RAW);
			}
			if ($str_key == 'hasUserMasterRecord') {
				$hasUserMasterRecord		= $str_value;
				$hasUserMasterRecord	= filter_var($hasUserMasterRecord,FILTER_UNSAFE_RAW);
			}
			if ($str_key == 'hasStudentRecord') {
				$hasStudentRecord		= $str_value;
				$hasStudentRecord		= filter_var($hasStudentRecord,FILTER_UNSAFE_RAW);
			}
			if ($str_key == 'hasAdvisorRecord') {
				$hasAdvsorRecord		= $str_value;
				$hasAdvsorRecord		= filter_var($hasAdvsorRecord,FILTER_UNSAFE_RAW);
			}
			if ($str_key == 'hasAdvisorClassRecord') {
				$hasAdvisorClassRecord		= $str_value;
				$hasAdvisorClassRecord		= filter_var($hasAdvisorClassRecord,FILTER_UNSAFE_RAW);
			}
							
		}
	}
	
	
	if (in_array($userName,$validTestmode)) {			// give option to run in test mode 
		$testModeOption	= "<tr><td>Operation Mode</td>
							<td><input type='radio' class='formInputButton' name='inp_mode' value='Production' checked='checked'> Production<br />
								<input type='radio' class='formInputButton' name='inp_mode' value='TESTMODE'> TESTMODE</td></tr>
							<tr><td>Verbose Debugging?</td>
								<td><input type='radio' class='formInputButton' name='inp_verbose' value='N' checked='checked'> Standard Output<br />
									<input type='radio' class='formInputButton' name='inp_verbose' value='Y'> Turn on Debugging </td></tr>";
	} else {
		$testModeOption	= '';
	}
	
	
	$content = "<style type='text/css'>
		fieldset {font:'Times New Roman', sans-serif;color:#666;background-image:none;
		background:#efefef;padding:2px;border:solid 1px #d3dd3;}

		legend {font:'Times New Roman', sans-serif;color:#666;font-weight:bold;
		font-variant:small-caps;background:#d3d3d3;padding:2px 6px;margin-bottom:8px;}

		label {font:'Times New Roman', sans-serif;font-weight:bold;line-height:normal;
		text-align:right;margin-right:10px;position:relative;display:block;float:left;width:150px;}

		textarea.formInputText {font:'Times New Roman', sans-serif;color:#666;
		background:#fee;padding:2px;border:solid 1px #f66;margin-right:5px;margin-bottom:5px;}

		textarea.formInputText:focus {color:#000;background:#ffffff;border:solid 1px #006600;}

		textarea.formInputText:hover {color:#000;background:#ffffff;border:solid 1px #006600;}

		input.formInputText {color:#666;background:#fee;padding:2px;
		border:solid 1px #f66;margin-right:5px;margin-bottom:5px;}

		input.formInputText:focus {color:#000;background:#ffffff;border:solid 1px #006600;}

		input.formInputText:hover {color:#000;background:#ffffff;border:solid 1px #006600;}

		input.formInputFile {color:#666;background:#fee;padding:2px;border:
		solid 1px #f66;margin-right:5px;margin-bottom:5px;height:20px;}

		input.formInputFile:focus {color:#000;background:#ffffff;border:solid 1px #006600;}

		select.formSelect {color:#666;background:#fee;padding:2px;
		border:solid 1px #f66;margin-right:5px;margin-bottom:5px;cursor:pointer;}

		select.formSelect:hover {color:#333;background:#ccffff;border:solid 1px #006600;}

		input.formInputButton {vertical-align:middle;font-weight:bolder;
		text-align:center;color:#300;background:#f99;padding:1px;border:solid 1px #f66;
		cursor:pointer;position:relative;float:left;}

		input.formInputButton:hover {color:#f8f400;}

		input.formInputButton:active {color:#00ffff;}

		tr {color:#333;background:#eee;}

		table{font:'Times New Roman', sans-serif;background-image:none;border-collapse:collapse;}

		th {color:#ffff;background-color:#000;padding:5px;font-size:small;}

		td {padding:5px;font-size:small;}

		th:first-child,
		td:first-child {
		 padding-left: 10px;
		}

		th:last-child,
		td:last-child {
			padding-right: 5px;
		}
		</style>";	

	if ($testMode) {
		$content	.= "<p><strong>Operating in Test Mode.</strong></p>";
		if ($doDebug) {
			echo "<p><strong>Operating in Test Mode.</strong></p>";
		}
		$userMasterTableName		= 'wpw1_cwa_user_master2';
		$userMetaTableName			= 'wpw1_usermeta2';
		$usersTableName				= 'wpw1_users2';
		$advisorTableName			= 'wpw1_cwa_advisor2';
		$advisorClassTableName		= 'wpw1_cwa_advisorclass2';
		$studentTableName			= 'wpw1_cwa_student2';
		$tempDataTableName			= 'wpw1_cwa_temp_data2';
	} else {
		$userMasterTableName		= 'wpw1_cwa_user_master';
		$usersTableName				= 'wpw1_users';
		$userMetaTableName			= 'wpw1_usermeta';
		$advisorTableName			= 'wpw1_cwa_advisor';
		$advisorClassTableName		= 'wpw1_cwa_advisorclass';
		$studentTableName			= 'wpw1_cwa_student';
		$tempDataTableName			= 'wpw1_cwa_temp_data';
	}

	function delete_user( $user_id ) {

		// Include the user file with the user administration API
		require_once( ABSPATH . 'wp-admin/includes/user.php' );

		// Delete a WordPress user by specifying its user ID. 
		// Here the user with an ID equal to $user_id is deleted.
		return wp_delete_user( $user_id );

	}



	if ("1" == $strPass) {
		$content 		.= "<h3>$jobname</h3>
							<p>Delete the user from the system by user_name</p>
							<form method='post' action='$theURL' 
							name='selection_form' ENCTYPE='multipart/form-data'>
							<input type='hidden' name='strpass' value='2'>
							<table style='border-collapse:collapse;width:auto;'>
							<tr><td>User Name</td>
								<td><input type='text' class='formInputText' size='25' maxlength='25' name='inp_value' required></td></tr>
							$testModeOption
							<tr><td colspan='2'><input class='formInputButton' type='submit' value='Submit' /></td></tr></table>
							</form></p>";
	

///// Pass 2 -- do the work


	} elseif ("2" == $strPass) {
		if ($doDebug) {
			echo "<br />at pass $strPass with inp_value: $inp_value<br />";
		}	
	
		$doProceed				= TRUE;
		$hasUserRecord			= FALSE;
		$hasUserMasterRecord	= FALSE;
		$hasAdvisorRecord		= FALSE;
		$hasAdvisorClassRecord	= FALSE;
		$hasStudentRecord		= FALSE;
		$userMaster_id			= 0;
	
		if ($doProceed) {
			$sql				= "SELECT id, 
										   user_login, 
										   user_email, 
										   user_registered 
									FROM $usersTableName 
									WHERE user_login like '$inp_value'";
			$result				= $wpdb->get_results($sql);
			if ($result === FALSE) {
				$lastError		= $wpdb->last_error;
				if ($doDebug) {
					echo "running $sql returned false. $last_error<br />";
				}
				$hasUserRecord	= FALSE;
				$user_id		= 0;
			} else {
				$numRows		= $wpdb->num_rows;
				if ($doDebug) {
					echo "ran $sql<br />and retrieved $numRows rows<br />";
				}
				if ($numRows > 0) {
					$hasUserRecord			= TRUE;
					if ($doDebug) {
						echo "set hasUserRecord to TRUE<br />";
					}
					foreach($result as $resultRow) {
						$user_id			= $resultRow->id;
						$user_login			= $resultRow->user_login;
						$user_email			= $resultRow->user_email;
						$user_registered	= $resultRow->user_registered;

						$user_needs_verification	= FALSE;
						
						$metaSQL		= "select meta_key, meta_value 
											from $userMetaTableName 
											where user_id = $user_id 
											and (meta_key = 'first_name' 
												or meta_key = 'last_name' 
												or meta_key = 'wpw1_capabilities' 
												or meta_key = 'wpumuv_needs_verification')";
						$metaResult		= $wpdb->get_results($metaSQL);
						if ($metaResult === FALSE) {
							$lastError	= $wpdb->last_error;
							if ($doDebug) {
								echo "running $metaSQL failed. $lastError<br />";
							}
						} else {
							$numMRows	= $wpdb->num_rows;
							if ($doDebug) {
								echo "ran $metaSQL<br />and retrieved $numMRows rows<br />";
							}
							foreach($metaResult as $metaResultRow) {
								$meta_key		= $metaResultRow->meta_key;
								$meta_value		= $metaResultRow->meta_value;
						
								if ($meta_key == 'last_name') {
									$user_last_name	= $meta_value;
								}
								if ($meta_key == 'first_name') {
									$user_first_name = $meta_value;
								}
								if ($meta_key == 'wpw1_capabilities') {
						
									$myInt			= strpos($meta_value,'administrator');
									if ($myInt !== FALSE) {
										$user_role	= 'aministrator';
									}
									$myInt			= strpos($meta_value,'student');
									if ($myInt !== FALSE) {
										$user_role	= 'student';
									}
									$myInt			= strpos($meta_value,'advisor');
									if ($myInt !== FALSE) {
										$user_role	= 'advisor';
									}
								}
								if ($meta_key == 'wpumuv_needs_verification') {
									$user_needs_verification	= TRUE;
								}
							}
			
							// see if there is a user_master record
							$sql			= "select * from $userMasterTableName 
												where user_call_sign like '$user_login'";
							$sqlResult		= $wpdb->get_results($sql);
							if ($sqlResult === FALSE) {
								handleWPDBError($jobname,$doDebug);
								$hasUserMasterRecord			= FALSE;
							} else {
								$numRows	= $wpdb->num_rows;
								if ($doDebug) {
									echo "ran $sql<br />and retrieved $numRows rows<br />";
								}
								if ($numRows > 0) {
									$hasUserMasterRecord		= TRUE;
									foreach($sqlResult as $sqlRow) {
										$userMaster_id				= $sqlRow->user_ID;
										$userMaster_call_sign		= $sqlRow->user_call_sign;
										$userMaster_first_name		= $sqlRow->user_first_name;
										$userMaster_last_name		= $sqlRow->user_last_name;
									}
								} else {
									if ($doDebug) {
										echo "no user_master record found<br />";
									}
									$hasUserMaster				= FALSE;
									$userMaster_call_sign		= '';
								}
							}
						}
					}
				} else {
					if ($doDebug) {
						echo "hasUserRecord being set to FALSE<br />";
					}
					$hasUserRecord		= FALSE;
				}
			}
	
			// see if the inp_value has a student record
			$studentSQL		= "select * from $studentTableName 
								where student_call_sign like '$inp_value' 
								order by student_date_created DESC 
								limit 1";
			$wpw1_cwa_student	= $wpdb->get_results($studentSQL);
			if ($wpw1_cwa_student === FALSE) {
				handleWPDBError($jobname,$doDebug);
				$student_call_sign			= '';
			} else {
				$numSRows					= $wpdb->num_rows;
				if ($doDebug) {
					echo "ran $studentSQL<br />and retrieved $numSRows rows from $studentTableName table<br >";
				}
				if ($numSRows > 0) {
					$hasStudentRecord			= TRUE;
					if ($doDebug) {
						echo "set hasStudentRecord to TRUE<br />";
					}
				} else {
					if ($doDebug) {
						echo "no student record found<br />";
					}
					$hasStudentRecord			= FALSE;
					$student_call_sign			= '';
				}
			}

			// Is there an advisor record?
			$advisorSQL		= "select * from $advisorTableName 
							where advisor_call_sign like '$inp_value'
							order by advisor_date_created DESC 
							limit 1";
			$wpw1_cwa_advisor	= $wpdb->get_results($advisorSQL);
			if ($wpw1_cwa_advisor === FALSE) {
				handleWPDBError($jobname,$doDebug);
				$hasAdvisorClassRecord	= FALSE;
				$advisor_call_sign	= '';
			} else {
				$numARows			= $wpdb->num_rows;
				if ($doDebug) {
					echo "ran $advisorSQL<br />and found $numARows rows in $advisorTableName table<br />";
				}
				if ($numARows > 0) {
					$hasAdvisorRecord			= TRUE;
					if ($doDebug) {
						echo "set hasAdvisorRecord to TRUE<br />";
					}

					// are there any advisorclass records?
					$sql				= "select * from $advisorClassTableName 
											where advisorclass_call_sign like '$inp_value' 
											order by advisorclass_date_created DESC
											limit 1";
					$wpw1_cwa_advisorclass	= $wpdb->get_results($sql);
					if ($wpw1_cwa_advisorclass === FALSE) {
						handleWPDBError($jobname,$doDebug);
						$hasAdvisorClassRecord	= FALSE;
						$advisorClass_call_sign	= '';
					} else {
						$numACRows			= $wpdb->num_rows;
						if ($doDebug) {
							echo "ran $sql<br />and found $numACRows rows<br />";
						}
						if ($numACRows > 0) {
							$hasAdvisorClassRecord	= TRUE;
						if ($doDebug) {
							echo "set hasAdvisorClassRecord to TRUE<br />";
						}
						} else {
							if ($doDebug) {
								echo "no advisorclass record found<br />";
							}
							$hasAdvisorClassRecord		= FALSE;
							$advisorClass_call_sign		= '';
						}
					}
				} else {
					if ($doDebug) {
						echo "no advisor record found<br />";
					}
					$hasAdvisorRecord			= FALSE;
					$advisor_call_sign			= '';
				}
			}

			// have all needed information. Display and verify the deletion
			$content		.= "<h3>$jobname for $inp_value</h3>";
			
			$canDelete		= TRUE;
			if ($hasUserRecord) {
				$content	.= "<p>There is a User record<br />
								User_login: $user_login<br />
								User_role: $user_role<br />
								User_first_name: $user_first_name<br />
								User_last_name: $user_last_name<br />
								User_registered: $user_registered<br />";
			}				
			if ($user_needs_verification) {
				$content	.= "User has not verified</p>";
			} else {
				$content	.= "User record is verified</p>";
			}
			if ($hasUserMasterRecord) {
				$content	.= "<p>User has a User Master record, meaning the user has signed 
								in at least once.</p>";
			}
			if ($hasStudentRecord) {
				$content	.= "<p>User has student signup record(s). The user can not be 
								deleted by this program</p>";
				$canDelete	= FALSE;
			} else {
				$content	.= "<p>User does not have a student signup record</p>";
			}
			if ($hasAdvisorRecord) {
				$content	.= "<p>User has advisor signup record(s). The user can not 
								be deleted by this program</p>";
				$canDelete	= FALSE;
			} else {
				$content	.= "<p>User does not have an advisor signup record</p>";
			}
			if ($hasAdvisorClassRecord) {
				$content	.= "<p>User has advisorClass record(s). The user can not be 
								deleted by this program.</p>";
				$canDelete	= FALSE;
			} else {
				$content	.= "<p>User does not have any advisorClass record(s)</p>";
			}
			
			if ($canDelete) {			
				$content		.= "<form method='post' action='$theURL' 
									name='dodelete_form' ENCTYPE='multipart/form-data'>
									<input type='hidden' name='strpass' value='3'>
									<input type='hidden' name='user_id' value='$user_id'>
									<input type='hidden' name='userMaster_call_sign' value='$userMaster_call_sign'>
									<input type='hidden' name='userMaster_id' value='$userMaster_id'>
									<input type='hidden' name='hasUserRecord' value='$hasUserRecord'>
									<input type='hidden' name='hasUserMasterRecord' value='$hasUserMasterRecord'>
									<input type='hidden' name='hasStudentRecord' value='$hasStudentRecord'>
									<input type='hidden' name='hasAdvisorRecord' value='$hasAdvisorRecord'>
									<input type='hidden' name='hasAdvisorClassRecord' value='$hasAdvisorClassRecord'>
									<input class='formInputButton' type='submit' value='Delete?' />
	
									</form></p>";
			} else {
				$content		.= "<p>This program is designed to remove user and user_master records for users 
									that aren't legitimate. Since the user has one or more signup record, the 
									user is considered legit and deletions must be made through the 
									display and update routines. Once that is done, the user record can 
									be deleted by this program</p>";
			}
		}
		
	} elseif ("3" == $strPass) {
		if ($doDebug) {
			echo "<br />at pass $strPass<br />
					user_id: $user_id<br />
					userMaster_call_sign: $userMaster_call_sign<br />
					userMaster_id: $userMaster_id<br /><br />";
		}
		$content		.= "<h3>$jobname for $userMaster_call_sign</h3>";

		// delete the user_master record, if there is one
		if ($hasUserMasterRecord) {
			$userMasterData			= array('tableName'=>$userMasterTableName,
											'inp_method'=>'delete',
											'inp_data'=>array(),
											'inp_format'=>array(),
											'jobname'=>$jobname,
											'inp_id'=>$userMaster_id,
											'inp_callsign'=>$userMaster_call_sign,
											'inp_who'=>$userName,
											'testMode'=>$testMode,
											'doDebug'=>$doDebug);
			$updateResult	= update_user_master($userMasterData);
			if ($updateResult[0] === FALSE) {
				$content			.= "The attempt to delete user master record for $inp_call_sign at $inp_id idfailed<br />";
				handleWPDBError($jobname,$doDebug);
			} else {
				$content		.= "The user master record id $userMaster_id for $userMaster_call_sign has been deleted<br />";
			}
			
			// get rid of any temp_data records
			$tempResult			= $wpdb->delete($tempDataTableName,
										array('callsign'=>$userMaster_call_sign),
										array('%s'));
			if ($tempResult === FALSE) {
				handleWPDBError($jobname,$doDebug,'trying to clear out temp_data table');
				$content	.= "unable to clear out $tempDataTableName for $userMaster_call_sign<br />";
			} else {
				$content	.= "The $tempDataTableName table was cleared of all $userMaster_call_sign records<br />";
			}

		}
		
		
		// now delete the user
		$userDelete		= delete_user($user_id);
//		$userDelete		= TRUE;
		if (!$userDelete) {
			$content	.= "Deleting $user_login id $inp_value failed<br />";
		} else {
			$content	.= "Successfully deleted $userMaster_call_sign at ID $user_id from $usersTableName<br />";
		}
		
	
	}
	$thisTime 		= date('Y-m-d H:i:s');
	$content 		.= "<br /><br /><p>Prepared at $thisTime</p>";
	$endingMicroTime = microtime(TRUE);
	$elapsedTime	= $endingMicroTime - $startingMicroTime;
	$elapsedTime	= number_format($elapsedTime, 4, '.', ',');
	$content		.= "<p>Report V$versionNumber pass $strPass took $elapsedTime seconds to run</p>";
	$nowDate		= date('Y-m-d');
	$nowTime		= date('H:i:s');
	$thisStr		= 'Production';
	if ($testMode) {
		$thisStr	= 'Testmode';
	}
	$ipAddr			= get_the_user_ip();
	$theTitle		= esc_html(get_the_title());
	$jobmonth		= date('F Y');
	$updateData		= array('jobname' 		=> $jobname,
							'jobdate' 		=> $nowDate,
							'jobtime'		=> $nowTime,
							'jobwho' 		=> $userName,
							'jobmode'		=> 'Time',
							'jobdatatype' 	=> $thisStr,
							'jobaddlinfo'	=> "$strPass: $elapsedTime",
							'jobip' 		=> $ipAddr,
							'jobmonth' 		=> $jobmonth,
							'jobcomments' 	=> '',
							'jobtitle' 		=> $theTitle,
							'doDebug'		=> $doDebug);
	$result			= write_joblog2_func($updateData);
	if ($result === FALSE){
		$content	.= "<p>writing to joblog failed</p>";
	}

	return $content;
}
add_shortcode ('delete_user_info', 'delete_user_info_func');
