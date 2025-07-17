function new_usernames_report_func(){

/*	List new registrations and any issues with user_logins

 	Issues:
 	If the user does not have a user_master record
 	
	If the user does not have a signup record
		If three days have passed
			send a signup reminder
		If six or more days and ten or less days have passed
			note 6 Day Rule violation on the error report
		If ten days have passed
			send a signup reminder
		If fifteen days or more and twenty-five days or less have passed
			Note 15 Day Rule violation on the error report
		if more than 90 days have passed, delete the user
	Forked from list_new_registrations on 13Mar25 by Roland
	
*/

	global $wpdb, $doDebug, $currentSemester, $nextSemester, $semesterTwo, 
			$semesterThree, $semesterFour, $userName, $jobname, $allUsersArray;

	$doDebug						= FALSE;
	$testMode						= FALSE;
	$doAdvisorNoUsername			= FALSE;
	$doStudentNoUsername			= FALSE;
	$initializationArray 			= data_initialization_func();
	$validUser 						= $initializationArray['validUser'];
	
	$debugData						= "";


	$versionNumber				 	= "1";
		$debugData .= "Initialization Array:<br /><pre>";
		$debugData		.= print_r($initializationArray,TRUE);
		$debugData .= "</pre><br /><br />";
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
	
	ini_set('display_errors','1');
	error_reporting(E_ALL);	
	ini_set('max_execution_time',0);
	set_time_limit(0);

/// get the time that the process started
	$startingMicroTime			= microtime(TRUE);
	
	$theURL					= "$siteURL/cwa-new-usernames-report/";
	$studentUpdateURL		= "$siteURL/cwa-display-and-update-student-signup-information/";	
	$advisorUpdateURL		= "$siteURL/cwa-display-and-update-advisor-signup-information/";
	$userMasterUpdateURL	= "$siteURL/cwa-display-and-update-user-master-information/";	
	$jobname				= "New UserNames Report V$versionNumber";
	$advisorTableName		= "wpw1_cwa_advisor";
	$studentTableName		= "wpw1_cwa_student";
	$tempTableName			= "wpw1_cwa_temp_data";
	$nowDate				= date('Y-m-d H:i:s');

	$userLoginCount			= 0;
	$userUnverifiedCount	= 0;
	$newRegistrations		= 0;
	$registrationEmailCount	= 0;
	$registerEmailCount		= 0;
	$verifyEmailCount		= 0;
	$noUserMasterCount		= 0;
	$noSignupCount			= 0;
	$signupEmailCount		= 0;
	$newSignup				= 0;
	$userRecordsDeleted		= 0;
	$userNameArray			= array();
	$registrationArray		= array();
	$recordsToBeDeleted		= "";
	$user_needs_verification	= FALSE;
	$id						= '';
	$user_login				= '';
	$display_name			= '';
	$user_registered		= '';
	$first_name				= '';
	$last_name				= '';
	$user_role				= '';
	$userUnverifiedList		= '';
	$fifteenDayList			= array();
	$bypassArray			= array('ROLAND',
									'KCGATOR', 
									'N7AST', 
									'F8ABC', 
									'BOBC',
									'AH7RF',
									'ah7rf', 
									'VE2KM',
									've2km',
									'K7OJL');
	$registrationRecord				= FALSE;		// whether or not there is a signup record with callsign = user_login
	$sendSignupEmailArray		= array();		// whether or not to send a registration reminder email
	$sendVerifyEmailArray		= array();
	
	if ($doDebug) {
		$thisVerbose			= 'Y';
	} else {
		$thisVerbose			= 'N';
	}
	if ($testMode) {
		$thisMode				= 'TESTMODE';
	} else {
		$thisMode				= 'PRODUCTION';
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

	function calculateDaysBetweenDates($startDate, $endDate) {
		$startDateTime = new DateTime($startDate);
		$endDateTime = new DateTime($endDate);
		$interval = $startDateTime->diff($endDateTime);
		return $interval->days;
	}

	function updateActionLog($username,$whichRule) {
	
		global $wpdb,$doDebug;
	
		if ($doDebug) {
			echo "<br /><b>updateActionLog</b><br />
					Username: $username<br />
					Rule: $whichRule<br />";
		}
		$actionDate		= date('Y-m-d H:i:s');
		$thisActionLog	= $wpdb->get_var("select user_action_log 
										from wpw1_cwa_user_master 
										where user_call_sign like '$username'");
		if ($thisActionLog === FALSE) {
			handleWPDBError("New Usernames Report",$doDebug,"in function $updateActionLog with username $username and rule $rule");
		} else {
			if ($doDebug) {
				echo "got the action log for $username<br />";
			}
			$thisActionLog	.= " / $actionDate New Usernames Report $whichRule day signup email sent ";
			$updateResult	= $wpdb->update('wpw1_cwa_user_master',
											array('user_action_log'=>$thisActionLog),
											array('user_call_sign'=>$username),
											array('%s'),
											array('%s'));
			$lastError		= $wpdb->last_error;
			$lastQuery		= $wpdb->last_query;
			if ($lastError != '') {
				if ($doDebug) {
					echo "attempted to run $wpdb_last_query<br />Error: $lastError<br />";
				}
			} else {
				if ($doDebug) {
					echo "action log for $username updated<br />";
				}
			}
		}
		if ($doDebug) {
			echo "Returning from updateActionLog<br /><br />";
		}
	}


	$runTheJob				= TRUE;
	$runByCron				= FALSE;
////// see if this is the time to actually run
		$debugData .= "<br />starting<br /><br />";
		
	if ($userName != '') {
		$content 			.= "<h3>$jobname Executed by $userName</h3>";
	} else {
		$content			.= "<h3>$jobname Automatically Executed</h3>";
		$runByCron			= TRUE;
		$userName			= "CRON";
		$runTheJob				= allow_job_to_run($doDebug);
	}
	if ($runTheJob) {
/////// real start

		require_once( ABSPATH . 'wp-admin/includes/user.php' );

		// get all registrations
		$sql				= "SELECT id, 
									   user_login, 
									   user_email, 
									   display_name, 
									   user_registered 
								FROM `wpw1_users` 
								order by user_login";
		$result				= $wpdb->get_results($sql);
		if ($result === FALSE) {
			handleWPDBError($jobname,$doDebug);
			$content		.= "Unable to read wpw1_users table";
			$doProceed		= FALSE;
		} else {
			$numRows		= $wpdb->num_rows;
			$debugData .= "ran $sql<br />and retrieved $numRows rows<br /><br />";
			if ($numRows > 0) {
				$myInt				= strtotime("$nowDate -36 hours");
				$recents			= date('Y-m-d H:i:s',$myInt);
				$content			.= "<h4>New UserNames Since $recents</h4>
										<table style='width:auto;'>
										<tr><th>Role</th>
											<th>Call Sign</th>
											<th>Name</th>
											<th>Email</th>
											<th>Signup</th></tr>";
				foreach($result as $resultRow) {
					$doProceed			= TRUE;

					$registrationRecord		= FALSE;		// whether or not there is a signup record with callsign = user_login
					$user_id			= $resultRow->id;
					$user_login			= $resultRow->user_login;
					$user_email			= $resultRow->user_email;
					$display_name		= $resultRow->display_name;
					$user_registered	= $resultRow->user_registered;

					$userLoginCount++;					
					$user_uppercase		= strtoupper($user_login);
					
					$debugData .= "<br />Processing $user_login<br /><br />";
					
					if (in_array($user_uppercase,$bypassArray)) { 
						$doProceed 		= FALSE;
					}
					if ($doProceed) {
						$user_first_name			= '';
						$user_last_name				= 'N/A';
						$user_role					= '';
						$doProceed					= TRUE;
					
					
						$metaSQL		= "select meta_key, meta_value 
											from `wpw1_usermeta` 
											where user_id = $user_id 
											and (meta_key = 'first_name' 
												or meta_key = 'last_name' 
												or meta_key = 'wpw1_capabilities' 
												or meta_key = 'wpumuv_needs_verification')";
						$metaResult		= $wpdb->get_results($metaSQL);
						if ($metaResult === FALSE) {
							handleWPDBError($jobname,$doDebug);
							$content	.= "unable to obtain usermeta data for $user_id";
							$doProceed	= FALSE;
						} else {
							$numMRows	= $wpdb->num_rows;
								$debugData .= "ran $metaSQL<br />and retrieved $numMRows rows<br /><br />";
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
										$user_role	= 'administrator';
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
//								if ($meta_key == 'wpumuv_needs_verification') {
//									$verifiedUser				= FALSE;
//									$userUnverifiedCount++;
//									$userUnverifiedList			.= "$user_login&$user_email"; 
//								} else {
									$verifiedUser				= TRUE;
//								}
							}
						}
						if ($doProceed) {
						
							$allUsersArray[$user_uppercase]	= array('last_name'=>$user_last_name, 
															 'first_name'=>$user_first_name, 
															 'display_name'=>$display_name, 
															 'user_registered'=>$user_registered, 
															 'user_email'=>$user_email, 
															 'id'=>$user_id, 
															 'user_role'=>$user_role,
															 'hasError'=>'N', 
															 'theError'=>"Username created on $user_registered<br />",
															 'hasUserMaster'=>'N',
															 'hasSignup'=>'N');
							$debugData .= "added $user_login to allUsersArray<br /><br />";

							// does the user have a user_master record? 
							$hasUserMaster				= FALSE;
							// if not, display and go onto the next login record
							$sql						= "select count(user_call_sign) 
															from wpw1_cwa_user_master 
															where user_call_sign like '$user_uppercase'";
							$masterCount				= $wpdb->get_var($sql);
							$debugData					.= "running $sql returned $masterCount records<br />";
							if ($masterCount == NULL || $masterCount == 0) {
								// no user_master record. So, no signup record
								$allUsersArray[$user_uppercase]['hasError']	= 'Y';
								$allUsersArray[$user_uppercase]['theError']	.= 'No user_master record<br />';
								$debugData				.= "running $sql returned either NULL or no record<br />";
								$noUserMasterCount++;
							} else {
								$allUsersArray[$user_uppercase]['hasUserMaster']	= 'Y';
								$allUsersArray[$user_uppercase]['theError']	.= 'Has a user_master record<br />';
								$hasUserMaster			= TRUE;
							}
								
							if ($doProceed) {
								// see if the user_login has a signup record
								$registration				= '';
								if ($user_role == 'student') {
									$student_level	= '';
									$student_semester = '';
									$studentSQL		= "select * from $studentTableName 
														where student_call_sign like '$user_uppercase'
														order by student_date_created DESC 
														limit 1";
									$studentResult	= $wpdb->get_results($studentSQL);
									if ($studentResult === FALSE) {
										handleWPDBError($jobname,$doDebug);
										$debugData	.= "ran $studentSQL and the result was FALSE<br />";
										$doProceed	= FALSE;
									} else {
										$numSRows	= $wpdb->num_rows;
										$debugData	.= "ran $studentSQL and retrieved $numSRows rows of data<br />";
										if ($numSRows > 0) {
											foreach($studentResult as $studentResultRow) {
												$student_semester	= $studentResultRow->student_semester;
												$student_level		= $studentResultRow->student_level;
											
												$registration		= "signed up for $student_level in $student_semester";
												$registrationRecord	= TRUE;
												$allUsersArray[$user_uppercase]['hasSignup']	= 'Y';
												$debugData .= "$user_login has a student signup record<br />";
												$allUsersArray[$user_uppercase]['theError']	.= 'Has a student signup record<br />';
											}
										} else {
												$allUsersArray[$user_uppercase]['theError']	.= "No student signup record<br />";
												$allUsersArray[$user_uppercase]['hasError']	= "Y";
												$noSignupCount++;
										}
									}
								} elseif ($user_role == 'advisor') {
									$advisorSQL		= "select * from $advisorTableName 
													where advisor_call_sign like '$user_uppercase'
													order by advisor_date_created DESC 
													limit 1";
									$advisorResult	= $wpdb->get_results($advisorSQL);
									if ($advisorResult === FALSE) {
										handleWPDBError($jobname,$doDebug);
										$debugData	.= "ran 4advisorSQL which returned FALSE<br />";
										$doProceed	= FALSE;
										
									} else {
										$numARows	= $wpdb->num_rows;
										$debugData	.= "ran $advisorSQL which yielded $numARows rows of data<br />";
										if ($numARows > 0) {
											foreach($advisorResult as $advisorResultRow) {
												$advisor_semester	= $advisorResultRow->advisor_semester;
							
												$registration				= "signed up $advisor_semester";
												$registrationRecord		= TRUE;
												$allUsersArray[$user_uppercase]['hasSignup']	= 'Y';
												$debugData .= "$user_login has an advisor signup record<br /><br />";
												$allUsersArray[$user_uppercase]['theError']	.= 'Has an advisor signup record<br />';
											}
										} else {
											$allUsersArray[$user_uppercase]['theError']	.= "No advisor signup<br />";
											$allUsersArray[$user_uppercase]['hasError']	= "Y";
											$noSignupCount++;
										}
									}
								}
							}
							if ($doProceed) {
								// if a recent registration, display
								$recentRegister			= FALSE;
								if ($user_registered >= $recents) {
									$recentRegister		= TRUE;
									$newRegistrations++;
										$debugData .= "$user_login is a recent registration<br /><br />";
									$user_uppercase				= strtoupper($user_login);
									$thisStr			= '';
									if ($verifiedUser) {
										if ($user_role == 'advisor') {
											$update			= "<a href='https://cwa.cwops.org/cwa-display-and-update-advisor-signup-information/?request_type=callsign&request_info=$user_uppercase&inp_table=advisor&strpass=2&inp_depth=one7inp_verbose=N' target='_blank'>$user_login</a>'";
										} elseif ($user_role == 'student') {
											$update			= "<a href='https://cwa.cwops.org/cwa-display-and-update-student-signup-information/?request_type=callsign&request_info=$user_uppercase&inp_depth=one&doDebug=$doDebug&testMode=$testMode&strpass=2' target='_blank'>$user_login</a>";
										} else {
											$update			= $user_login;
										}
									} else { 
										$update				= $user_login;
										$registration		= "Unverified User";
									}
									$content			.= "<tr><td>$user_role</td>
																<td>$update</td>
																<td>$user_last_name, $user_first_name</td>
																<td><a href='mailto:$user_email' target='_blank'>$user_email</a></td>
																<td>$registration $thisStr</td></tr>";
								}

								// check the dates
								$threeDays				= FALSE;
								$sixDays			 	= FALSE;
								$tenDays				= FALSE;
								$fifteenDays			= FALSE;
								$fifteenDaysPlus		= FALSE;
								$thirtyDays				= FALSE;
								$ninetyDaysPlus			= FALSE;
								$sendVerifyEmail		= FALSE;
								$sendSignupEmail		= FALSE;
								$deleteUser				= FALSE;
								$daysElapsed			= calculateDaysBetweenDates($user_registered,$nowDate);
								if ($daysElapsed == 3) {
									$threeDays			= TRUE;
								}
								if ($daysElapsed >= 6 && $daysElapsed <= 10) {
									$sixDays			= TRUE;
								}
								if ($daysElapsed == 10) {
									$tenDays			= TRUE;
								}
								if ($daysElapsed == 15) {
									$fifteenDays		= TRUE;
								} elseif ($daysElapsed >= 15 && $daysElapsed <= 25) {
									$fifteenDaysPlus	= TRUE;
								}
								if ($daysElapsed == 30) {
									$thirtyDays			= TRUE;
								}
								if ($daysElapsed > 90) {
									$ninetyDaysPlus		= TRUE;
								}
								
								if (!$registrationRecord) {
									if ($ninetyDaysPlus){
										$deleteUser			= TRUE;
										$allUsersArray[$user_uppercase]['theError']	.= "<b>90 Day Rule</b> No signup record for $daysElapsed days. <b>User Deleted<br />";
										$allUsersArray[$user_uppercase]['hasError']	= "Y";
									} elseif ($tenDays) {			// send signup email
										$sendSignupEmail	= TRUE;
										$allUsersArray[$user_uppercase]['theError']	.= "<b>10 Day Rule</b> Sending 10-day Signup Email<br />";
										$allUsersArray[$user_uppercase]['hasError']	= "Y";	
									} elseif ($sixDays) {
										$allUsersArray[$user_uppercase]['theError']	.= "<b>6 Day Rule</b> No signup record for $daysElapsed days<br />";
										$allUsersArray[$user_uppercase]['hasError']	= "Y";	
									} elseif ($threeDays) {			// send signup email
										$sendSignupEmail	= TRUE;
										$allUsersArray[$user_uppercase]['theError']	.= "<b>3 Day Rule</b> Sending 3-day Signup Email<br />";
										$allUsersArray[$user_uppercase]['hasError']	= "Y";	
									}
									if ($fifteenDaysPlus) {
										$allUsersArray[$user_uppercase]['theError']	.= "<b>15 Day Rule</b> No signup record for $daysElapsed days<br />";
										$allUsersArray[$user_uppercase]['hasError']	= "Y";	
//										$fifteenDayList[]	= $user_login;
									}
								}
								
								// send emails
								if ($sendSignupEmail) {
									$sendSignupEmailArray[]	= "$user_email&$user_uppercase&$user_last_name, $user_first_name&$user_role";
									if ($hasUserMaster) {
										if ($threeDays) {
											updateActionLog($user_uppercase,3);
										}
										if ($tenDays) {
											updateActionLog($user_uppercase,10);
										}
									}
								}
								if ($deleteUser) {
									wp_delete_user( $user_id, null );
									$userRecordsDeleted++;
									$allUsersArray[$user_uppercase]['theError']	.= "Users and user_master records were deleted<br />";
									$allUsersArray[$user_uppercase]['hasUserMaster']	= 'N';
								}
							}
						}
					}
				}
				$content		.= "</table><p>$newRegistrations New Registrations</p>";
			} else {
				$content		.= "No records found in wpw1_users";
			}
		}	
		
		
		
		if ($doProceed) {								
			// show what we've got so far
			$debugData		.= "<br />This is what we have so far:<br ><pre>";
			$debugData		.= print_r($allUsersArray,TRUE);
			$debugData		.= "</pre><br />";

			// display the error arrays
			$thisInt		= count($allUsersArray);
			if ($thisInt > 0) {
				$debugData .= "<br />have $thisInt allUsersArray data to display<br /><br />";
				ksort($allUsersArray);
				$myCount	= 0;
				$content	.= "<h4>Errors Encountered</h4>
								<table style=width:1200px;'>
								<tr><th>Role</th>
									<th>Callsign</th>
									<th>Name</th>
									<th>Email</th>
									<th>Errors</th>
									<th>Edit User</th>
									<th>Delete ID</th>";
				foreach($allUsersArray as $thisUser => $userData) {
					if ($allUsersArray[$thisUser]['hasError'] == 'Y') {
						$thisRole		= $allUsersArray[$thisUser]['user_role'];
						$thisLastName	= $allUsersArray[$thisUser]['last_name'];
						$thisFirstName	= $allUsersArray[$thisUser]['first_name'];
						$thisEmail		= $allUsersArray[$thisUser]['user_email'];
						$theErrors		= $allUsersArray[$thisUser]['theError'];
						$userID			= $allUsersArray[$thisUser]['id'];
						$hasUserMaster	= $allUsersArray[$thisUser]['hasUserMaster'];
						$hasSignup		= $allUsersArray[$thisUser]['hasSignup'];
						
						if ($thisRole == 'Advisor') {
							if ($hasSignup == 'Y') {
								$thisLink		= "<a href='$advisorUpdateURL?request_type=callsign&request_info=$thisUser&inp_depth=one&doDebug=$doDebug&testMode=$testMode&strpass=2' target='_blank'>$thisUser</a>";
							} elseif ($hasUserMaster == 'Y') {
								$thisLink		= "<a href='$userMasterUpdateURL?request_type=callsign&request_info=$thisUser&strpass=2&doDebug=$doDebug&testMode=$testMode' target='_blank'>$thisUser</a>";
							} else {
								$thisLink		= $thisUser;
							}
							$emailLink		= "<a href='mailto:$thisEmail' target='_blank'>$thisEmail</a>";
						} elseif ($thisRole == 'student') {
							if ($hasSignup == 'Y') {
								$thisLink		= "<a href='$studentUpdateURL?request_type=callsign&request_info=$thisUser&inp_depth=one&strpass=2&doDebug=$doDebug&testMode=$testMode' target='_blank'>$thisUser</a>";
							} elseif ($hasUserMaster == 'Y') {
								$thisLink		= "<a href='$userMasterUpdateURL?request_type=callsign&request_info=$thisUser&strpass=2&doDebug=$doDebug&testMode=$testMode' target='_blank'>$thisUser</a>";
							} else {
								$thisLink		= $thisUser;
							}
							$emailLink		= "<a href='mailto:$thisEmail' target='_blank'>$thisEmail</a>";
						} else {
							$thisLink		= $thisUser;
							$emailLink		= $thisEmail;
						}
						$editLink		= "<a href='$siteURL/wp-admin/users.php?s=$thisUser' target='_blank'>$thisUser</a>";
						if ($hasSignup == 'N') {
							$deleteIDLink	= "<a href='$siteURL/cwa-delete-user-info/?inp_value=$thisUser&strpass=2' target='_blank'>Delete User</a>";
						} else {
							$deleteIDLink	= '';
						}
	
						$content		.= "<tr><td style='vertical-align:top;'>$thisRole</td>
												<td style='vertical-align:top;'>$thisLink</td>
												<td style='vertical-align:top;'>$thisLastName, $thisFirstName</td>
												<td style='vertical-align:top;'>$emailLink</td>
												<td style='vertical-align:top;'>$theErrors</td>
												<td style='vertical-align:top;'>$editLink</td>
												<td style='vertical-align:top;'>$deleteIDLink</td>";
						$myCount++;
					}
				}
				$content			.= "</table>$myCount Errors Displayed<br />
										<b>Action Items Listed Above:</b><br>
										3 Day Rule: a signup reminder was sent by the program<br />
										6 Day Rule: 6-9 days have passed. Manually send a signup request<br />
										10 Day Rule: Another signup reminder was sent by the program<br />
										15 Day Rule: 15-25 days have passed. Manually send a signup request<br />
										90 Day Rule: the user is automatically deleted<br /><br />
										Clicking on the Username will open the user_master record in a new tab<br />
										Clicking on the email address will start a new email<br />
										Clicking on the Delete User link will delete the user and the user_master records<br />
										<p>User Login records displayed above have obtained 
										a user name and a password. Whether or not they have a user_master 
										record or a signup record is indicated above</p>
										</p>";
			}
		
			// send signup and verify emails
			if (count($sendSignupEmailArray) > 0) {
				$content			.= "<h4>Signup Reminder Emails Sent</h4>";
				foreach($sendSignupEmailArray as $thisValue) {							
					$myArray		= explode("&",$thisValue);
					$thisEmail		= $myArray[0];
					$thisUser		= $myArray[1];
					$thisLastName	= $allUsersArray[$thisUser]['last_name'];
					$thisFirstName	= $allUsersArray[$thisUser]['first_name'];
					$thisRole		= $allUsersArray[$thisUser]['user_role'];
					
					$debugData .= "Sending email to $thisEmail<br />";
					$thisRole		= ucfirst($thisRole);
					if ($thisRole == 'Student') {
						$article	= 'a';
						$textStr	= "take a class";
					} else {
						$article	= 'an';
						$textStr	= "be an advisor";
					}	
					$theSubject		= "CW Academy -- Missing $thisRole Sign up Information";
					$theContent		= "<p>To: $thisLastName, $thisFirstName:</p>
<p>You recently obtained a username and password for the 
CW Academy website, but did not sign for a class. Obtaining a CW Academy username and password does 
not automatically sign you up for $article $user_role class. Please go to <a href='$siteURL/program-list/'>CW 
Academy</a>, enter your usename and password, and sign up by clicking on the 'Sign up' button.<br />73,<br />CW Academy";
					$mailResult		= emailFromCWA_v2(array('theRecipient'=>$thisEmail,
																'theSubject'=>$theSubject,
																'theContent'=>$theContent,
																'theCc'=>'',
																'theAttachment'=>'',
																'mailCode'=>13,
																'jobname'=>$jobname,
																'increment'=>0,
																'testMode'=>$testMode,
																'doDebug'=>$doDebug));
					$signupEmailCount++;
					$content		.= "Signup Reminder sent to $thisLastName, $thisFirstName ($thisUser) at $thisEmail<br />";
				}
			}
				
			$content	.= "<br /><h4>Counts</h4>
							$userLoginCount User Login Records<br />
							$newRegistrations New User Registrations in Past 36 Hours<br /><br />
							$userUnverifiedCount User Records that are Unverified<br />
							$noUserMasterCount Users with no user_master record (e.g., have never logged in)<br />
							$noSignupCount Users with no signup record<br /><br />
							$userRecordsDeleted Users with no signup record older than 90 days deleted<br />
							$verifyEmailCount Verify reminder emails sent during this job<br />
							$signupEmailCount Signup reminder emails sent during this job<br />";
							
			$debugData	.= "<br />Records to be Deleted:<br /><pre>";
			$debugData	.=	$recordsToBeDeleted;
			$debugData	.= "</pre><br />";
		}						

		$endingMicroTime = microtime(TRUE);
		$elapsedTime	= $endingMicroTime - $startingMicroTime;
		$elapsedTime	= number_format($elapsedTime, 4, '.', ',');
		$content		.= "<br /><p>Report V$versionNumber pass 1 took $elapsedTime seconds to run</p>";
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
								'jobaddlinfo'	=> "0: $elapsedTime",
								'jobip' 		=> $ipAddr,
								'jobmonth' 		=> $jobmonth,
								'jobcomments' 	=> '',
								'jobtitle' 		=> $theTitle,
								'doDebug'		=> $doDebug);
		$result			= write_joblog2_func($updateData);
		if ($result === FALSE){
			$content	.= "<p>writing to joblog failed</p>";
		}
		
		// store the report in the reports table
		$storeResult	= storeReportData_v2($jobname,$content,$testMode,$doDebug);
		if ($storeResult[0] === FALSE) {
				$debugData .= "storing report failed. $storeResult[1]<br />";
			$content	.= "Storing report failed. $storeResult[1]<br />";
		} else {
			$reportid_1	= $storeResult[2];
		}
		// store the debugData
		$storeResult	= storeReportData_v2("$jobname Debug",$debugData,$testMode,$doDebug);
		if ($storeResult[0] === FALSE) {
				$debugData .= "storing report failed. $storeResult[1]<br />";
			$content	.= "Storing report failed. $storeResult[1]<br />";
		} else {
			$reportid_2	= $storeResult[2];
		}
		
		/// if run thru cron, set up reminders otherwise display the report
		if ($runByCron) {
			// store the reminder
			$effective_date		= date('Y-m-d 00:00:00');
			$closeStr			= strtotime("+ 14 days");
			$close_date			= date('Y-m-d 00:00:00',$closeStr);
			$token			= mt_rand();
			$reminder_text	= "<b>New Registrations</b> To view the New Registrations report for $nowDate $nowTime, click <a href='cwa-display-saved-report/?strpass=3&inp_callsign=XXXXX&inp_id=$reportid_1&token=$token' target='_blank'>Display Report</a>";
			$inputParams		= array("effective_date|$effective_date|s",
										"close_date|$close_date|s",
										"resolved_date||s",
										"send_reminder|N|s",
										"send_once|N|s",
										"call_sign||s",
										"role|administrator|s",
										"email_text||s",
										"reminder_text|$reminder_text|s",
										"resolved|N|s",
										"token|$token|s");
			$reminderResult	= add_reminder($inputParams,$testMode,$doDebug);
			if ($reminderResult[0] === FALSE) {
				$debugData .= "adding reminder failed. $reminderResult[1]<br />";
			}
		} else {
			return $content;
		}
	}
}
add_shortcode ('new_usernames_report','new_usernames_report_func');
