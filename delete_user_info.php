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
							<p>Delete the user from the system either by user_name or 
							by ID number</p>
							<form method='post' action='$theURL' 
							name='selection_form' ENCTYPE='multipart/form-data'>
							<input type='hidden' name='strpass' value='2'>
							<table style='border-collapse:collapse;width:auto;'>
							<tr><td style = 'width:150px;'>Delete using</td>
								<td><input type='radio' class='formInputButton' name='inp_type' value='name'>User Name<br />
									<input type='radio' class='formInputButton' name='inp_type' value='id'>ID Number</td></tr>
							<tr><td>Name / ID</td>
								<td><input type='text' class='formInputText' size='5' maxlength='5' name='inp_value'></td></tr>
							$testModeOption
							<tr><td colspan='2'><input class='formInputButton' type='submit' value='Submit' /></td></tr></table>
							</form></p>";
	

///// Pass 2 -- do the work


	} elseif ("2" == $strPass) {
		if ($doDebug) {
			echo "<br />at pass $strPass with inp_type: $inp_type and inp_value: $inp_value<br />";
		}	
	
		$doProceed			= TRUE;
		if ($inp_type == 'id') {
			$where			= "ID = $inp_value";
		} elseif ($inp_type == 'name') {
			$where			= "user_login = $inp_value";
		} else {
			$content		.= "Requested inp_type of $inp_type and inp_value of $inp_value. 
								No such information found.<br />";
			$doProceed		= FALSE;
		}
	
		if ($doProceed) {
			$sql				= "SELECT id, 
										   user_login, 
										   user_email, 
										   user_registered 
									FROM `wpw1_users` 
									WHERE $where";
			$result				= $wpdb->get_results($sql);
			if ($result === FALSE) {
				$lastError		= $wpdb->last_error;
				if ($doDebug) {
					echo "running $sql returned false. $last_error<br />";
				}
			} else {
				$numRows		= $wpdb->num_rows;
				if ($doDebug) {
					echo "ran $sql<br />and retrieved $numRows rows<br />";
				}
				if ($numRows > 0) {
					foreach($result as $resultRow) {
						$id					= $resultRow->id;
						$user_login			= $resultRow->user_login;
						$user_email			= $resultRow->user_email;
						$user_registered	= $resultRow->user_registered;

						$user_needs_verification	= FALSE;
						
						$metaSQL		= "select meta_key, meta_value 
											from `wpw1_usermeta` 
											where user_id = $id 
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
							
							// see if the user_login has a signup record
							$signupRecord		= FALSE;
							$myStr				= strtoupper($user_login);
							if ($user_role == 'student') {
								$studentSQL		= "select * from $studentTableName 
													where call_sign = '$myStr'
													order by date_created DESC 
													limit 1";
								$studentResult	= $wpdb->get_results($studentSQL);
								if ($studentResult === FALSE) {
									$lastError	= $wpdb->last_error;
									if ($doDebug) {
										echo "running $studentSQL failed. Error: $lastError<br />";
									}
								} else {
									$numSRows	= $wpdb->num_rows;
									if ($numSRows > 0) {
										foreach($studentResult as $studentResultRow) {
											$student_id			= $studentResultRow->student_id;
											$signupRecord		= TRUE;
											if ($doDebug) {
												echo "$user_login has a student signup record<br />";
											}
										}
									} else {
										// no signup record
										if ($doDebug) {
											echo "$user_login DOES NOT have a student signup record<br />";
										}
									}
								}
							} elseif ($user_role == 'advisor') {
								$advisorSQL		= "select * from $advisorTableName 
												where call_sign = '$myStr'
												order by date_created DESC 
												limit 1";
								$advisorResult	= $wpdb->get_results($advisorSQL);
								if ($advisorResult === FALSE) {
									$lastError	= $wpdb->last_error;
									if ($doDebug) {
										echo "running $advisorSQL failed. Error: $lastError<br />";
									}
								} else {
									$numARows	= $wpdb->num_rows;
									if ($numARows > 0) {
										foreach($advisorResult as $advisorResultRow) {
											$advisor_id			= $advisorResultRow->advisor_id;
							
											$signupRecord		= TRUE;
											if ($doDebug) {
												echo "$user_login has an advisor signup record<br />";
											}
										}
									} else {		// no signup record
										if ($doDebug) {
											echo "$user_login DOES NOT have an advisor signup record<br />";
										}
									}
								}
							}
							// have all needed information. Display and verify the deletion
							$content		.= "<h3>$jobname</h3>
												<p>User_login: $user_login<br />
												User_role: $user_role<br />
												User_first_name: $user_first_name<br />
												User_last_name: $user_last_name<br />
												User_registered: $user_registered<br />";
							if ($user_needs_verification) {
								$content	.= "User has not verified<br />";
							} else {
								$content	.= "User record is verified<br />";
							}
							if ($signupRecord) {
								$content	.= "User has a signup record which will be deleted<br />";
								$deleteSignup = 'Y';
							} else {
								$content	.= "User does not have a signup record<br />";
								$deleteSignup = 'N';
							}
							$content		.= "<form method='post' action='$theURL' 
												name='dodelete_form' ENCTYPE='multipart/form-data'>
												<input type='hidden' name='strpass' value='3'>
												<input type='hidden' name='inp_type' value='$user_role'>
												<input type='hidden' name='inp_value' value='$id'>
												<input type='hidden' name='user_login' value='$user_login'>
												<input type='hidden' name='deleteSignup' value='$deleteSignup'>
												<input class='formInputButton' type='submit' value='Delete?' />
												</form></p>";
						}
					}
				} else {
					$content		.= "<h3>$jobname</h3>
										<p>No information found for $inp_type and $inp_value</p>";
				}
			}
		}
		
		
		
		
	} elseif ("3" == $strPass) {
		if ($doDebug) {
			echo "<br />at pass $strPass<br />
					user_role: $inp_type<br />
					ID: $inp_value<br />
					deleteSignup: $deleteSignup<br />
					user_login: $user_login<br /><br />";
		}
		$content		.= "<h3>$jobname</h3>";
		
		if ($deleteSignup == 'Y') {
			if ($inp_type == 'student') {
				$sql			= "select * from $studentTableName 
									where call_sign = '$user_login' and
										(semester = '$currentSemester' 
											or semester = '$nextSemester' 
											or semester = '$semesterTwo' 
											or semester = '$semesterThree' 
											or semester = '$semesterFour')";
				$wpw1_cwa_student		= $wpdb->get_results($sql);
				if ($wpw1_cwa_student === FALSE) {
					$myError			= $wpdb->last_error;
					$myQuery			= $wpdb->last_query;
					if ($doDebug) {
						echo "Reading $studentTableName table failed<br />
							  wpdb->last_query: $myQuery<br />
							  wpdb->last_error: $myError<br />";
					}
					$errorMsg			= "$jobname reading $studentTableName failed. <p>SQL: $myQuery</p><p> Error: $myError</p>";
					sendErrorEmail($errorMsg);
					$content		.= "Unable to obtain content from $studentTableName<br />";
				} else {
					$numSRows			= $wpdb->num_rows;
					if ($doDebug) {
						$myStr			= $wpdb->last_query;
						echo "ran $myStr<br />and found $numSRows rows<br />";
					}
					if ($numSRows > 0) {
						foreach ($wpw1_cwa_student as $studentRow) {
							$student_ID								= $studentRow->student_id;
							$student_call_sign						= strtoupper($studentRow->call_sign);
							$student_first_name						= $studentRow->first_name;
							$student_last_name						= stripslashes($studentRow->last_name);
							$student_email  						= strtolower(strtolower($studentRow->email));
							$student_phone  						= $studentRow->phone;
							$student_ph_code						= $studentRow->ph_code;
							$student_city  							= $studentRow->city;
							$student_state  						= $studentRow->state;
							$student_zip_code  						= $studentRow->zip_code;
							$student_country  						= $studentRow->country;
							$student_country_code					= $studentRow->country_code;
							$student_time_zone  					= $studentRow->time_zone;
							$student_timezone_id					= $studentRow->timezone_id;
							$student_timezone_offset				= $studentRow->timezone_offset;
							$student_whatsapp						= $studentRow->whatsapp_app;
							$student_signal							= $studentRow->signal_app;
							$student_telegram						= $studentRow->telegram_app;
							$student_messenger						= $studentRow->messenger_app;					
							$student_wpm 	 						= $studentRow->wpm;
							$student_youth  						= $studentRow->youth;
							$student_age  							= $studentRow->age;
							$student_student_parent 				= $studentRow->student_parent;
							$student_student_parent_email  			= strtolower($studentRow->student_parent_email);
							$student_level  						= $studentRow->level;
							$student_waiting_list 					= $studentRow->waiting_list;
							$student_request_date  					= $studentRow->request_date;
							$student_semester						= $studentRow->semester;
							$student_notes  						= $studentRow->notes;
							$student_welcome_date  					= $studentRow->welcome_date;
							$student_email_sent_date  				= $studentRow->email_sent_date;
							$student_email_number  					= $studentRow->email_number;
							$student_response  						= strtoupper($studentRow->response);
							$student_response_date  				= $studentRow->response_date;
							$student_abandoned  					= $studentRow->abandoned;
							$student_student_status  				= strtoupper($studentRow->student_status);
							$student_action_log  					= $studentRow->action_log;
							$student_pre_assigned_advisor  			= $studentRow->pre_assigned_advisor;
							$student_selected_date  				= $studentRow->selected_date;
							$student_no_catalog			 			= $studentRow->no_catalog;
							$student_hold_override  				= $studentRow->hold_override;
							$student_messaging  					= $studentRow->messaging;
							$student_assigned_advisor  				= $studentRow->assigned_advisor;
							$student_advisor_select_date  			= $studentRow->advisor_select_date;
							$student_advisor_class_timezone 		= $studentRow->advisor_class_timezone;
							$student_hold_reason_code  				= $studentRow->hold_reason_code;
							$student_class_priority  				= $studentRow->class_priority;
							$student_assigned_advisor_class 		= $studentRow->assigned_advisor_class;
							$student_promotable  					= $studentRow->promotable;
							$student_excluded_advisor  				= $studentRow->excluded_advisor;
							$student_student_survey_completion_date	= $studentRow->student_survey_completion_date;
							$student_available_class_days  			= $studentRow->available_class_days;
							$student_intervention_required  		= $studentRow->intervention_required;
							$student_copy_control  					= $studentRow->copy_control;
							$student_first_class_choice  			= $studentRow->first_class_choice;
							$student_second_class_choice  			= $studentRow->second_class_choice;
							$student_third_class_choice  			= $studentRow->third_class_choice;
							$student_first_class_choice_utc  		= $studentRow->first_class_choice_utc;
							$student_second_class_choice_utc  		= $studentRow->second_class_choice_utc;
							$student_third_class_choice_utc  		= $studentRow->third_class_choice_utc;
							$student_catalog_options				= $studentRow->catalog_options;
							$student_flexible						= $studentRow->flexible;
							$student_date_created 					= $studentRow->date_created;
							$student_date_updated			  		= $studentRow->date_updated;

							if ($student_student_status == 'S' || $student_student_status == 'Y') {
								// student is assigned. First remove the student
								$inp_data			= array('inp_student'=>$student_call_sign,
															'inp_semester'=>$student_semester,
															'inp_assigned_advisor'=>$student_assigned_advisor,
															'inp_assigned_advisor_class'=>$student_assigned_advisor_class,
															'inp_remove_status'=>'',
															'inp_arbitrarily_assigned'=>$student_no_catalog,
															'inp_method'=>'remove',
															'jobname'=>$jobname,
															'userName'=>$userName,
															'testMode'=>$testMode,
															'doDebug'=>$doDebug);
						
								$removeResult		= add_remove_student($inp_data);
								if ($removeResult[0] === FALSE) {
									$thisReason		= $removeResult[1];
									if ($doDebug) {
										echo "attempting to remove $student_call_sign from $student_assigned_advisor class failed:<br />$thisReason<br />";
									}
									sendErrorEmail("$jobname Attempting to remove $student_call_sign from $student_assigned_advisor class failed:<br />$thisReason");
									$content		.= "Attempting to remove $student_call_sign from $student_assigned_advisor class failed:<br />$thisReason<br />";
								} else {
									$content		.= "Student removed from class and unassigned<br />
														You need to push $student_assigned_advisor class<br />";
								}
							}
							// now delete the student record
							$studentUpdateData		= array('tableName'=>$studentTableName,
															'inp_method'=>'delete',
															'inp_data'=>array(''),
															'inp_format'=>array(''),
															'jobname'=>$jobname,
															'inp_id'=>$student_ID,
															'inp_callsign'=>$student_call_sign,
															'inp_semester'=>$student_semester,
															'inp_who'=>$userName,
															'testMode'=>$testMode,
															'doDebug'=>$doDebug);
							$updateResult	= updateStudent($studentUpdateData);
							if ($updateResult[0] === FALSE) {
								$myError	= $wpdb->last_error;
								$mySql		= $wpdb->last_query;
								$errorMsg	= "$jobname Processing $student_call_sign in $studentTableName failed. Reason: $updateResult[1]<br />SQL: $mySql<br />Error: $myError<br />";
								if ($doDebug) {
									echo $errorMsg;
								}
								sendErrorEmail($errorMsg);
								$content		.= "Unable to update content in $studentTableName<br />";
							} else {
								$content		.= "Student $student_call_sign has been deleted<br />";
							}
						}
					} else {
						$content		.= "Unable to delete student $user_login as there is no record to delete<br />";
					}
				}				
											
			} elseif ($inp_type == 'advisor') {
				$sql			= "select * from $advisorTableName 
									where advisor_call_sign = '$user_login' 
									and (semester = '$currentSemester' 
											or semester = '$nextSemester' 
											or semester = '$semesterTwo' 
											or semester = '$semesterThree' 
											or semester = '$semesterFour')";
				$wpw1_cwa_advisor	= $wpdb->get_results($sql);
				if ($wpw1_cwa_advisor === FALSE) {
					$myError			= $wpdb->last_error;
					$myQuery			= $wpdb->last_query;
					if ($doDebug) {
						echo "Reading $advisorTableName table failed<br />
							  wpdb->last_query: $myQuery<br />
							  wpdb->last_error: $myError<br />";
					}
					$errorMsg			= "$jobname Reading $advisorTableName table failed. <p>SQL: $myQuery</p><p> Error: $myError</p>";
					sendErrorEmail($errorMsg);
					$content		.= "Unable to obtain content from $advisorTableName<br />";
				} else {
					$numARows			= $wpdb->num_rows;
					if ($doDebug) {
						$myStr			= $wpdb->last_query;
						echo "ran $myStr<br />and found $numARows rows in $advisorTableName table<br />";
					}
					if ($numARows > 0) {
						foreach ($wpw1_cwa_advisor as $advisorRow) {
							$advisor_ID							= $advisorRow->advisor_id;
							$advisor_select_sequence 			= $advisorRow->select_sequence;
							$advisor_call_sign 					= strtoupper($advisorRow->call_sign);
							$advisor_first_name 				= $advisorRow->first_name;
							$advisor_last_name 					= stripslashes($advisorRow->last_name);
							$advisor_email 						= strtolower($advisorRow->email);
							$advisor_phone						= $advisorRow->phone;
							$advisor_ph_code					= $advisorRow->ph_code;				// new
							$advisor_text_message 				= $advisorRow->text_message;
							$advisor_city 						= $advisorRow->city;
							$advisor_state 						= $advisorRow->state;
							$advisor_zip_code 					= $advisorRow->zip_code;
							$advisor_country 					= $advisorRow->country;
							$advisor_country_code				= $advisorRow->country_code;		// new
							$advisor_whatsapp					= $advisorRow->whatsapp_app;		// new
							$advisor_signal						= $advisorRow->signal_app;			// new
							$advisor_telegram					= $advisorRow->telegram_app;		// new
							$advisor_messenger					= $advisorRow->messenger_app;		// new
							$advisor_time_zone 					= $advisorRow->time_zone;
							$advisor_timezone_id				= $advisorRow->timezone_id;			// new
							$advisor_timezone_offset			= $advisorRow->timezone_offset;		// new
							$advisor_semester 					= $advisorRow->semester;
							$advisor_survey_score 				= $advisorRow->survey_score;
							$advisor_languages 					= $advisorRow->languages;
							$advisor_fifo_date 					= $advisorRow->fifo_date;
							$advisor_welcome_email_date 		= $advisorRow->welcome_email_date;
							$advisor_verify_email_date 			= $advisorRow->verify_email_date;
							$advisor_verify_email_number 		= $advisorRow->verify_email_number;
							$advisor_verify_response 			= strtoupper($advisorRow->verify_response);
							$advisor_action_log 				= $advisorRow->action_log;
							$advisor_class_verified 			= $advisorRow->class_verified;
							$advisor_control_code 				= $advisorRow->control_code;
							$advisor_date_created 				= $advisorRow->date_created;
							$advisor_date_updated 				= $advisorRow->date_updated;
							$advisor_replacement_status 		= $advisorRow->replacement_status;

							// have an advisor record. Get and delete advisor class records
							
							$classSQL		= "select * from $advisorClassTableName 
												where advisor_call_sign = '$advisor_call_sign' 
												and (semester = '$currentSemester' 
													or semester = '$nextSemester' 
													or semester = '$semesterTwo' 
													or semester = '$semesterThree' 
													or semester = '$semesterFour')";
							$wpw1_cwa_advisorclass				= $wpdb->get_results($sql);
							if ($wpw1_cwa_advisorclass === FALSE) {
								$myError			= $wpdb->last_error;
								$myQuery			= $wpdb->last_query;
								if ($doDebug) {
									echo "Reading $advisorClassTableName table failed<br />
										  wpdb->last_query: $myQuery<br />
										  wpdb->last_error: $myError<br />";
								}
								$errorMsg			= "$jobname Reading $advisorClassTableName table failed. <p>SQL: $myQuery</p><p> Error: $myError</p>";
								sendErrorEmail($errorMsg);
							} else {
								$numACRows						= $wpdb->num_rows;
								if ($doDebug) {
									$myStr						= $wpdb->last_query;
									echo "ran $myStr<br />and found $numACRows rows<br />";
								}
								if ($numACRows > 0) {
									foreach ($wpw1_cwa_advisorclass as $advisorClassRow) {
										$advisorClass_ID				 		= $advisorClassRow->advisorclass_id;
										$advisorClass_advisor_call_sign 		= $advisorClassRow->advisor_call_sign;
										$advisorClass_advisor_first_name 		= $advisorClassRow->advisor_first_name;
										$advisorClass_advisor_last_name 		= stripslashes($advisorClassRow->advisor_last_name);
										$advisorClass_advisor_id 				= $advisorClassRow->advisor_id;
										$advisorClass_sequence 					= $advisorClassRow->sequence;
										$advisorClass_semester 					= $advisorClassRow->semester;
										$advisorClass_timezone 					= $advisorClassRow->time_zone;
										$advisorClass_timezone_id				= $advisorClassRow->timezone_id;		// new
										$advisorClass_timezone_offset			= $advisorClassRow->timezone_offset;	// new
										$advisorClass_level 					= $advisorClassRow->level;
										$advisorClass_class_size 				= $advisorClassRow->class_size;
										$advisorClass_class_schedule_days 		= $advisorClassRow->class_schedule_days;
										$advisorClass_class_schedule_times 		= $advisorClassRow->class_schedule_times;
										$advisorClass_class_schedule_days_utc 	= $advisorClassRow->class_schedule_days_utc;
										$advisorClass_class_schedule_times_utc 	= $advisorClassRow->class_schedule_times_utc;
										$advisorClass_action_log 				= $advisorClassRow->action_log;
										$advisorClass_class_incomplete 			= $advisorClassRow->class_incomplete;
										$advisorClass_date_created				= $advisorClassRow->date_created;
										$advisorClass_date_updated				= $advisorClassRow->date_updated;
										$advisorClass_student01 				= $advisorClassRow->student01;
										$advisorClass_student02 				= $advisorClassRow->student02;
										$advisorClass_student03 				= $advisorClassRow->student03;
										$advisorClass_student04 				= $advisorClassRow->student04;
										$advisorClass_student05 				= $advisorClassRow->student05;
										$advisorClass_student06 				= $advisorClassRow->student06;
										$advisorClass_student07 				= $advisorClassRow->student07;
										$advisorClass_student08 				= $advisorClassRow->student08;
										$advisorClass_student09 				= $advisorClassRow->student09;
										$advisorClass_student10 				= $advisorClassRow->student10;
										$advisorClass_student11 				= $advisorClassRow->student11;
										$advisorClass_student12 				= $advisorClassRow->student12;
										$advisorClass_student13 				= $advisorClassRow->student13;
										$advisorClass_student14 				= $advisorClassRow->student14;
										$advisorClass_student15 				= $advisorClassRow->student15;
										$advisorClass_student16 				= $advisorClassRow->student16;
										$advisorClass_student17 				= $advisorClassRow->student17;
										$advisorClass_student18 				= $advisorClassRow->student18;
										$advisorClass_student19 				= $advisorClassRow->student19;
										$advisorClass_student20 				= $advisorClassRow->student20;
										$advisorClass_student21 				= $advisorClassRow->student21;
										$advisorClass_student22 				= $advisorClassRow->student22;
										$advisorClass_student23 				= $advisorClassRow->student23;
										$advisorClass_student24 				= $advisorClassRow->student24;
										$advisorClass_student25 				= $advisorClassRow->student25;
										$advisorClass_student26 				= $advisorClassRow->student26;
										$advisorClass_student27 				= $advisorClassRow->student27;
										$advisorClass_student28 				= $advisorClassRow->student28;
										$advisorClass_student29 				= $advisorClassRow->student29;
										$advisorClass_student30 				= $advisorClassRow->student30;
										$class_number_students					= $advisorClassRow->number_students;
										$class_evaluation_complete 				= $advisorClassRow->evaluation_complete;
										$class_comments							= $advisorClassRow->class_comments;
										$copycontrol							= $advisorClassRow->copy_control;
									
										// if there are any students, remove them from the class
										if ($class_number_students > 0) {
											for ($snum=1;$snum<31;$snum++) {
												if ($snum < 10) {
													$strSnum 		= str_pad($snum,2,'0',STR_PAD_LEFT);
												} else {
													$strSnum		= strval($snum);
												}
												$theInfo			= ${'advisorClass_student' . $strSnum};
												if ($theInfo != '') {
													if ($doDebug) {
														echo "<br />processing student$strSnum $theInfo<br />";
													}
													$inp_data			= array('inp_student'=>$theInfo,
																				'inp_semester'=>$advisorClass_semester,
																				'inp_assigned_advisor'=>$advisorClass_advisor_call_sign,
																				'inp_assigned_advisor_class'=>$advisorClass_sequence,
																				'inp_remove_status'=>'',
																				'inp_arbitrarily_assigned'=>'',
																				'inp_method'=>'remove',
																				'jobname'=>$jobname,
																				'userName'=>$userName,
																				'testMode'=>$testMode,
																				'doDebug'=>$doDebug);
						
													$removeResult		= add_remove_student($inp_data);
													if ($removeResult[0] === FALSE) {
														$thisReason		= $removeResult[1];
														if ($doDebug) {
															echo "attempting to remove $student_call_sign from $student_assigned_advisor class failed:<br />$thisReason<br />";
														}
														sendErrorEmail("$jobname Attempting to remove $student_call_sign from $student_assigned_advisor class failed:<br />$thisReason");
														$content		.= "Attempting to remove $student_call_sign from $student_assigned_advisor class failed:<br />$thisReason<br />";
													} else {
														$content		.= "Student removed from class and unassigned<br />";
													}
												}
											}
										}
										// Now delete the advisorClass record
										$classUpdateData		= array('tableName'=>$advisorClassTableName,
																		'inp_method'=>'delete',
																		'inp_data'=>array(''),
																		'inp_format'=>array(''),
																		'jobname'=>$jobname,
																		'inp_id'=>$advisorClass_ID,
																		'inp_callsign'=>$advisorClass_advisor_call_sign,
																		'inp_semester'=>$advisorClass_semester,
																		'inp_who'=>$userName,
																		'testMode'=>$testMode,
																		'doDebug'=>$doDebug);
										$updateResult	= updateClass($classUpdateData);
										if ($updateResult[0] === FALSE) {
											$myError	= $wpdb->last_error;
											$mySql		= $wpdb->last_query;
											$errorMsg	= "A$jobname Processing $advisor_call_sign in $advisorClassTableName failed. Reason: $updateResult[1]<br />SQL: $mySql<br />Error: $myError<br />";
											if ($doDebug) {
												echo $errorMsg;
											}
											sendErrorEmail($errorMsg);
											$content		.= "Unable to update content in $advisorClassTableName<br />";
										} else {
											$content		.= "$advisorClass_advisor_call_sign class $advisorClass_sequence deleted<br >";
										}
									}
									// advisorClass records deleted. Delete the advisor record
									$advisorUpdateData		= array('tableName'=>$advisorTableName,
																	'inp_method'=>'delete',
																	'inp_data'=>array(''),
																	'inp_format'=>array(''),
																	'jobname'=>$jobname,
																	'inp_id'=>$advisor_ID,
																	'inp_callsign'=>$advisor_call_sign,
																	'inp_semester'=>$advisor_semester,
																	'inp_who'=>$userName,
																	'testMode'=>$testMode,
																	'doDebug'=>$doDebug);
									$updateResult	= updateAdvisor($advisorUpdateData);
									if ($updateResult[0] === FALSE) {
										$myError	= $wpdb->last_error;
										$mySql		= $wpdb->last_query;
										$errorMsg	= "$jobname Processing $advisor_call_sign in $advisorNewTableName failed. Reason: $updateResult[1]<br />SQL: $mySql<br />Error: $myError<br />";
										if ($doDebug) {
											echo $errorMsg;
										}
										sendErrorEmail($errorMsg);
										$content		.= "Unable to update content in $advisorNewTableName<br />";
									} else {
										$content		.= "Advisor $advisor_call_sign has been deleted<br />";
									
									}
									
								} else {
									$content		.= "No advisorClass records for $user_login found<br />";
								}
							}

						}
					} else {
						$content			.= "Unable to delete advisor $user_login as no advisor record found<br />";
					}
				}
			} else {
				$content		.= "Requested to delete signup inforation for $inp_type 
									which doesn't compute<br />";
			}
		}
		// now delete the user
		$userDelete		= delete_user($inp_value);
//		$userDelete		= TRUE;
		if (!$userDelete) {
			$content	.= "Deleting $user_login id $inp_value failed<br />";
		} else {
			$content	.= "Successfully deleted $user_login id $inp_value<br />";
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
	$result			= write_joblog_func("$jobname|$nowDate|$nowTime|$userName|Time|$thisStr|$strPass: $elapsedTime|$ipAddr");
	if ($result == 'FAIL') {
		$content	.= "<p>writing to joblog.txt failed</p>";
	}
	return $content;
}
add_shortcode ('delete_user_info', 'delete_user_info_func');
