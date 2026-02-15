function list_new_usernames_func(){

/*	List new registrations and any issues with user_logins

	modified 26Dec23 by Roland to remove user_logins that never create a sign up record
	Modified 8Jan24 by Roland to add ability to ignore errors and no longer automatically 
		some users
	Modified 20Jan24 by Roland to record tracking data in temp_data
	Modified 2Mar24 by Roland to only send missing signup email if no past or future studeht/advisor record found
	Modified 22Sep24 by Roland to use new database
	Forked from list_new_registrations_v4 on 1Nov24 by Roland
	
*/

	global $wpdb, $doDebug, $currentSemester, $nextSemester, $semesterTwo, 
			$semesterThree, $semesterFour, $userName, $jobname, $allUsersArray;

	$doDebug						= FALSE;
	$testMode						= FALSE;
	$doAdvisorNoUsername			= FALSE;
	$doStudentNoUsername			= FALSE;
	$context = CWA_Context::getInstance();
	$validUser 						= $context->validUser;
	
	$debugData						= "";


	$versionNumber				 	= "1";
		$debugData .= "Initialization Array:<br /><pre>";
		$debugData		.= print_r($context->toArray(), TRUE);
		$debugData .= "</pre><br /><br />";
	$userName			= $context->userName;
	$userEmail			= $context->userEmail;
	$userDisplayName	= $context->userDisplayName;
	$currentTimestamp	= $context->currentTimestamp;
	$validTestmode		= $context->validTestmode;
	$siteURL			= $context->siteurl;
	$currentSemester	= $context->currentSemester;
	$nextSemester		= $context->nextSemester;
	$semesterTwo		= $context->semesterTwo;
	$semesterThree		= $context->semesterThree;
	$semesterFour		= $context->semesterFour;
	
	ini_set('display_errors','1');
	error_reporting(E_ALL);	
	ini_set('max_execution_time',0);
	set_time_limit(0);

/// get the time that the process started
	$startingMicroTime			= microtime(TRUE);
	
	$theURL					= "$siteURL/cwa-list-new-usernames/";
	$studentUpdateURL		= "$siteURL/cwa-display-and-update-student-signup-information/";	
	$advisorUpdateURL		= "$siteURL/cwa-display-and-update-advisor-signup-information/";	
	$jobname				= "List New UserNames V$versionNumber";
	$advisorTableName		= "wpw1_cwa_advisor";
	$studentTableName		= "wpw1_cwa_student";
	$tempTableName			= "wpw1_cwa_temp_data";
	$nowDate				= date('Y-m-d H:i:s');
	$userLoginCount			= 0;
	$userUnverifiedCount	= 0;
	$userUnverifiedDeleted	= 0;
	$newRegistrations		= 0;
	$badUsernameCount		= 0;
	$registrationEmailCount		= 0;
	$registerEmailCount		= 0;
	$verifyEmailCount		= 0;
	$tempDataAdded			= 0;
	$tempDataDeleted		= 0;
	$usernamesDeleted		= 0;
	$newSignup				= 0;
	$advisorMissingUsername	= 0;
	$studentMissingUsername	= 0;
	$userNameArray			= array();
	$registrationArray		= array();
	$noUserMasterRecord		= array();
	$user_needs_verification	= FALSE;
	$id						= '';
	$user_login				= '';
	$display_name			= '';
	$user_registered		= '';
	$first_name				= '';
	$last_name				= '';
	$user_role				= '';
	$studentNoSignup		= 0;
	$advisorNoSignup		= 0;
	$badUsernameList		= "";
	$advisorNoUsernameArray	= array();
	$studentNoUsernameArray	= array();
	$advisorNoUsername		= 0;
	$studentNoUsername		= 0;
	$badUserNameCount		= 0;
	$userUnverifiedList		= '';
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
	$verifiedUser				= FALSE;		// whether or not the user_login record is verified
	$validFormat				= FALSE;		// whether or not the user_login is a callsign or the user's last name
	$tempRegister				= FALSE;		// whether or not there is a temp_data register record
	$tempIgnore					= FALSE;		// whether or not there is a temp_data ignore record
	$threeDayDate				= '';			// temp_data register date_written plus 3 days
	$threeDayPlus				= FALSE;		// whether or not the three day countdown date is less than today
	$tenDayDate					= '';			// temp_data register date_written plus 10 days
	$tenDayPlus					= FALSE;		// whether or not temp_data register date_written is less than today
	$setTempRegisterArray		= array();		// whether or not to write a temp_data register record
	$setTempIgnoreArray			= array();		// whether or not to write a temp_data ignore record
	$deleteTempRegisterArray	= array();		// whether or not to delete a temp_data register record
	$deleteTempIgnoreArray		= array();		// whether or not to delete a temp_data ignore record
	$sendSignupEmailArray		= array();		// whether or not to send a registration reminder email
	$emailSignup				= FALSE;		// found signup record using email address
	$validCallsign				= FALSE;		// whether or not the user_login fits a valid callsign format
	$registrationCallsign				= '';			// Callsign in signup record
	$sendRegisterEmailArray		= array();		// Whether nor not to send email requesting user create a user_login
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
	
	

	$content = "";	

	function calculateDaysBetweenDates($startDate, $endDate) {
		$startDateTime = new DateTime($startDate);
		$endDateTime = new DateTime($endDate);
		$interval = $startDateTime->diff($endDateTime);
		return $interval->days;
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
		
		function delete_temp_record($user_login,$token) {
			global $wpdb, $doDebug, $debugData;
			
			
			$result	= $wpdb->delete('wpw1_cwa_temp_data',
									array('token'=>$token,
											'callsign'=>$user_login),
									array('%s','%s'));
			if ($result === FALSE) {
				handleWPDBError("List New Registrations V2",$doDebug);
				return FALSE;
			} elseif ($result == 0) {
				$lastQuery		= $wpdb->last_query;
					$debugData .= "List New Registrations attempting to delete 
user_login $user_login with token $token deleted 0 rows. Query: $lastQuery<br /><br />";
			} else {
				return TRUE;
			}
		}




/////// real start

//		require_once( ABSPATH . 'wp-admin/includes/user.php' );

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
					$verifiedUser		= FALSE;		// whether or not the user_login record is verified
					$validFormat		= FALSE;		// whether or not the user_login is a callsign or the user's last name
					$tempRegister		= FALSE;		// whether or not there is a temp_data register record
					$tempIgnore			= FALSE;		// whether or not there is a temp_data ignore record
					$threeDayDate		= '';			// temp_data register date_written plus 3 days
					$threeDayPlus		= FALSE;		// whether or not the three day countdown date is less than today
					$tenDayDate			= '';			// temp_data register date_written plus 10 days
					$tenDayPlus			= FALSE;		// whether or not temp_data register date_written is less than today
					$validCallsign		= FALSE;		// whether or not the user_login fits a valid callsign format
					$registrationCallsign		= '';			// Callsign in signup record
					$emailSignup		= FALSE;		// whether or not there is a signup record found via email

					$user_id			= $resultRow->id;
					$user_login			= $resultRow->user_login;
					$user_email			= $resultRow->user_email;
					$display_name		= $resultRow->display_name;
					$user_registered	= $resultRow->user_registered;
					
					$user_uppercase		= strtoupper($user_login);
					
					$debugData .= "<br />Processing $user_login<br /><br />";
					
					if (in_array($user_uppercase,$bypassArray)) { 
						$doProceed 		= FALSE;
					}
					if ($doProceed) {
						$user_first_name			= '';
						$user_last_name				= 'N/A';
						$user_role					= '';
						$userLoginCount++;
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
								if ($meta_key == 'wpumuv_needs_verification') {
									$verifiedUser				= FALSE;
									$userUnverifiedCount++;
									$userUnverifiedList			.= "$user_login, "; 
								} else {
									$verifiedUser				= TRUE;
								}
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
															 'tempRegisterID'=>0,
															 'tempIgnoreID'=>0, 
															 'hasError'=>'N', 
															 'theError'=>"Username created $user_registered<br />",
															 'hasSignup'=>'Y',
															 'badUserName'=>'N');
								$debugData .= "added $user_login to allUsersArray<br /><br />";


							$tempIgnore			= FALSE;
							$gotTempRecord		= FALSE;
/*												
							// get the temp_data record, if any
							$tempSQL			= "select * from wpw1_cwa_temp_data 
													where callsign = '$user_uppercase' and 
														  (token = 'register' or 
														   token = 'ignore') 
													order by date_written";
							$tempResult			= $wpdb->get_results($tempSQL);
							if ($tempResult === FALSE) {
								handleWPDBError($jobname,$doDebug);
							} else {
								$numTempRows	= $wpdb->num_rows;
									$debugData .= "ran $tempSQL<br />and retrieved $numTempRows rows<br /><br />";
								if ($numTempRows > 0) {
									$gotTempRecord	= TRUE;
									foreach ($tempResult as $tempResultRow) {
										$tempID			= $tempResultRow->record_id;
										$tempToken		= $tempResultRow->token;
										$tempData		= $tempResultRow->temp_data;
										$date_written	= $tempResultRow->date_written;
										
										if ($tempToken == 'register') {
											$tempRegister			= TRUE;
											$debugData	.= "tempRegister record found. tempRegister is TRUE<br /><br />";
											$allUsersArray[$user_uppercase]['tempRegisterID']	= $tempID;
											$threeDayDate 			= date('Y-m-d H:i:s', strtotime($date_written . ' +3 days'));
// echo "<br />date_written: $date_written<br /><br />";
// echo "threeDayDate: $threeDayDate<br /><br />";
											if ($nowDate > $threeDayDate) {
												$threeDayPlus		= TRUE;
											}
											$tenDayDate 			= date('Y-m-d H:i:s', strtotime($date_written . ' +10 days'));
// echo "tenDatDate: $tenDayDate<br /><br />";
											if ($nowDate > $tenDayDate) {
												$tenDayPlus			= TRUE;
											}
										} elseif ($tempToken == 'ignore') {
											$tempIgnore		= TRUE;
											$debugData	.= "tempIgnore record found. tempIgnore is TRUE<br /><br />";
											$allUsersArray[$user_uppercase]['tempIgnoreID']	= $tempID;
										}
									}
								}
							}
*/						
							// if the user_role is blank, then recommend deleting the user
							if ($user_role == '') {
									$debugData .= "user_role of $user_role is invalid.<br /><br />";
								$allUsersArray[$user_uppercase]['hasError']	.= "Y";
								$allUsersArray[$user_uppercase]['theError']	.= "user_role invalid. <br /><br />";
							}
						
						
					
							if ($doProceed) {
								// do some checks on the user_login
								$badUserName			= FALSE;
/*
								$alphaResult			= preg_match('/^[A-Za-z0-9]+$/',$user_login);
								if ($alphaResult == 1) {			// have a match
										$debugData .= "$user_login passes the preg_match test<br /><br />";
									$betaResult		= preg_match('/^[A-Za-z]+$/',$user_login);
									if ($betaResult == 1) {		// it's alphabetic -- not a callsign
										// is user_login also the last name? if not, say so
										$myStr1				= strtolower($user_login);
										$myStr2				= strtolower(substr($user_last_name,1,5));
										$myStr3				= "/$myStr2/";
										// is the last name any part of the username? If not, flag it
										if (preg_match($myStr3,$myStr1)) {
											$debugData .= "<b>ERROR</b> user_login $user_login is not a callsign and not include any part of last name of $user_last_name<br /><br />";
										 	$badUserName	= TRUE;
										 	$validCallsign	= FALSE;
										}
									} else {						// has numeric -- maybe a callsign
										$testCallsign		= preg_match('/^[a-zA-Z0-9]{1,3}[0-9][a-zA-Z0-9]{0,3}[a-zA-Z]+$/',$user_login);
										if ($testCallsign == 1) {		// fits the callsign regex
											$debugData .= "user_login $user_login passed the callsign regex<br /><br />";
											$validCallsign	= TRUE;
										} else {							
											$debugData .= "<b>ERROR</b> user_login $user_login does not fit a callsign pattern<br /><br />";
											$validCallsign	= FALSE;
											$badUserName	= TRUE;
										}
									}
								} else {
									$debugData .= "<b>ERROR</b> $user_login does not pass the preg_match test<br /><br />";
									$badUserName			= TRUE;
								}
						
								if ($badUserName) {
									$badUserNameCount++;
									$badUsernameList			.= "$user_login, ";
								} else {
									$validFormat				= TRUE;
								}
								
								if ($badUserName) {
									$allUsersArray[$user_uppercase]['badUserName']	.= "Y";									
								} else {
									$allUsersArray[$user_uppercase]['badUserName']	.= "N";
								}
*/
								// does the user have a user_master record? 
								// if not, display and go onto the next login record
								$sql						= "select count(user_call_sign) 
																from wpw1_cwa_user_master 
																where user_call_sign = '$user_uppercase'";
								$masterCount				= $wpdb->get_var($sql);
								if ($masterCount == NULL || $masterCount == 0) {
									// no user_master record. So, no signup record
									$noUserMasterRecord[]	= "$user_registered|$user_login|$user_email|$user_last_name, $user_first_name|$user_role";
									$doProceed				= FALSE;		
								} 
								
								if ($doProceed) {
					
									// see if the user_login has a signup record
									$registration				= '';
									if ($user_role == 'student') {
										$student_level	= '';
										$student_semester = '';
										$studentSQL		= "select * from $studentTableName 
															where student_call_sign = '$user_uppercase'
															order by student_date_created DESC 
															limit 1";
										$studentResult	= $wpdb->get_results($studentSQL);
										if ($studentResult === FALSE) {
											handleWPDBError($jobname,$doDebug);
										} else {
											$numSRows	= $wpdb->num_rows;
											if ($numSRows > 0) {
												foreach($studentResult as $studentResultRow) {
													$student_semester	= $studentResultRow->student_semester;
													$student_level		= $studentResultRow->student_level;
												
													$registration		= "signed up for $student_level in $student_semester";
													$registrationRecord	= TRUE;
													$allUsersArray[$user_uppercase]['theError']	.= "User has a student registration for $student_level in $student_semester<br />";
													$allUsersArray[$user_uppercase]['hasSignup']	= "Y";
													$debugData .= "$user_login has a student signup record<br />";
												}
											} else {
												$allUsersArray[$user_uppercase]['hasSignup']	= "N";
											}
										}
									} elseif ($user_role == 'advisor') {
										$advisorSQL		= "select * from $advisorTableName 
														where advisor_call_sign = '$user_uppercase'
														order by advisor_date_created DESC 
														limit 1";
										$advisorResult	= $wpdb->get_results($advisorSQL);
										if ($advisorResult === FALSE) {
											handleWPDBError($jobname,$doDebug);
										} else {
											$numARows	= $wpdb->num_rows;
											if ($numARows > 0) {
												foreach($advisorResult as $advisorResultRow) {
													$advisor_semester	= $advisorResultRow->advisor_semester;
								
													$registration				= "signed up $advisor_semester";
													$registrationRecord		= TRUE;
													$allUsersArray[$user_uppercase]['theError']	.= "User has an advisor registration in $advisor_semester<br /><br />";
														$debugData .= "$user_login has an advisor signup record<br /><br />";
												}
											}
										}
									}
	
		
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
												$update			= "<a href='http://localhost:3073/cwa-display-and-update-advisor-signup-information/?request_type=callsign&request_info=$user_uppercase&inp_table=advisor&strpass=2&inp_depth=one7inp_verbose=N' target='_blank'>$user_login</a>'";
											} elseif ($user_role == 'student') {
												$update			= "<a href='http://localhost:3073/cwa-display-and-update-student-signup-information/?request_type=callsign&request_info=$user_uppercase&inp_depth=one&doDebug=$doDebug&testMode=$testMode&strpass=2' target='_blank'>$user_login</a>";
											} else {
												$update			= $user_login;
											}
										} else { 
											$update				= $user_login;
											$registration				= "Unverified User";
										}
										$content			.= "<tr><td>$user_role</td>
																	<td>$update</td>
																	<td>$user_last_name, $user_first_name</td>
																	<td><a href='mailto:$user_email' target='_blank'>$user_email</a></td>
																	<td>$registration $thisStr</td></tr>";
									}
						
										// show what we've got so far
										foreach($allUsersArray[$user_uppercase] as $thisKey=>$thisValue) {
											$debugData .= "$thisKey: $thisValue<br /><br />";
										}
										$codeStr		= '';
										if ($recentRegister) {
											$debugData .= "recentRegister: TRUE<br /><br />";
										} else {
											$debugData .= "recentRegister: FALSE<br /><br />";
										}
										if ($registrationRecord) {
											$debugData .= "registrationRecord: TRUE<br /><br />";
											$codeStr	.= 'Y';
										} else {
											$debugData .= "registrationRecord: FALSE<br /><br />";
											$codeStr	.= 'N';
										}
										if ($verifiedUser) {
											$debugData .= "verifiedUser: TRUE<br /><br />";
											$codeStr	.= 'Y';
										} else {
											$debugData .= "verifiedUser: FALSE<br /><br />";
											$codeStr	.= 'N';
										}
//										if ($validFormat) {
//											$debugData .= "validFormat: TRUE<br /><br />";
											$codeStr	.= 'Y';
//										} else {
//											$debugData .= "validFormat: FALSE<br /><br />";
//											$codeStr	.= 'N';
//										}
										if ($tempRegister) {
											$debugData .= "tempRegister: TRUE<br /><br />";
											$codeStr	.= 'Y';
										} else {
											$debugData .= "tempRegister: FALSE<br /><br />";
											$codeStr	.= 'N';
										}
										if ($tempIgnore) {
											$debugData .= "tempIgnore: TRUE<br /><br />";
											$codeStr	.= 'Y';
										} else {
											$debugData .= "tempIgnore: FALSE<br /><br />";
											$codeStr	.= 'N';
										}
										if (!$registrationRecord) {
											if ($emailSignup) {
												$debugData .= "emailSignup: TRUE<br /><br />";
												$codeStr	.= 'Y';
											} else {
												$debugData .= "emailSignup: FALSE<br /><br />";
												$codeStr	.= 'N';
											}
										} else {
											$codeStr		.= '-';
										}
										$debugData .= "Code: $codeStr<br /><br />";
										$allUsersArray[$user_uppercase]['code']	= "array code: $codeStr<br />";
	
	
										if ($codeStr != 'YYYNN-') {
											/// see if there is a tracking record if so, see if the code has changed
											/// if so, write a new record
											$writeCodeRec		= TRUE;
											$prevCode			= $wpdb->get_var("select temp_data 
																				 from wpw1_cwa_temp_data 
																				 where callsign = '$user_uppercase' 
																				 order by date_written DESC 
																				 limit 1");
											if ($prevCode != NULL) {
												if ($prevCode == $codeStr) {
													$writeCodeRec		= FALSE;
												}
											}
											if ($writeCodeRec) {
												$myStr				= date('Y-m-d H:i:s');
												$insertResult		= $wpdb->insert('wpw1_cwa_temp_data',
																				array('callsign'=>$user_uppercase,
																					   'temp_data'=>$codeStr,
																					   'token'=>'tracking',
																					   'date_written'=>$myStr),
																				array('%s','%s','%s','%s'));
								
											}
										}
									/*	Rules
										If unverified and no signup record, wait three days
										If unverified and a signup record, wait ten days
									*/
									
									if ($registrationRecord && $verifiedUser && $validFormat && $tempRegister && $tempIgnore) { 
											$debugData .= "YYYYY- has signed up, verified, valid format, tempRegister is set, tempIgnore is set<br />
											Program error. tempRegister and tempIgnore can not be set at the same time. 
											Delete tempRegister. Delete tempIgnore<br /><br /><br />";
										$allUsersArray[$user_uppercase]['hasError']	= 'Y';
										$allUsersArray[$user_uppercase]['theError']	.= 'Program error. Deleting temp_data records<br />';
										delete_temp_record($user_uppercase,'register');
										$tempDataDeleted++;
										delete_temp_record($user_uppercase,'ignore');
										$tempDataDeleted++;
									}
									if ($registrationRecord && $verifiedUser && !$validFormat && $tempRegister && $tempIgnore) { 
											$debugData .= "YYNYY- has signed up, verified, invalid format, tempRegister is set, tempIgnore is set<br />
															Program error, tempRegister, and tempIgnore can't be set at the same time. 
															Delete tempRegister. Delete tempIgnore<br /><br />";
										$allUsersArray[$user_uppercase]['hasError']	= 'Y';
										$allUsersArray[$user_uppercase]['theError']	.= 'Program error. Deleting temp_data records<br />';
										delete_temp_record($user_uppercase,'register');
										$tempDataDeleted++;
										delete_temp_record($user_uppercase,'ignore');
										$tempDataDeleted++;
									}
									if ($registrationRecord && !$verifiedUser && $validFormat && $tempRegister && $tempIgnore) { 
											$debugData .= "YNYYY- has signed up, unverified, valid format, tempRegister is set, tempIgnore is set<br />
															Program error. tempRegister and tempIgnore can not be set at the same time. 
															Delete tempRegister. Delete tempIgnore<br /><br />";
										$allUsersArray[$user_uppercase]['hasError']	= 'Y';
										$allUsersArray[$user_uppercase]['theError']	.= 'Program error. Deleting temp_data records<br />';
										delete_temp_record($user_uppercase,'register');
										$tempDataDeleted++;
										delete_temp_record($user_uppercase,'ignore');
										$tempDataDeleted++;
									}
									if ($registrationRecord && !$verifiedUser && !$validFormat && $tempRegister && $tempIgnore) { 
											$debugData .= "YNNYY- has signed up, unverified, invalid format, tempRegister is set, tempIgnore is set<br />
															Program error. tempRegister and tempIgnore can not be set at the same time. 
															Delete tempRegister. Delete tempIgnore<br /><br />";
										$allUsersArray[$user_uppercase]['hasError']	= 'Y';
										$allUsersArray[$user_uppercase]['theError']	.= 'Program error. Deleting temp_data records<br />';
										delete_temp_record($user_uppercase,'register');
										$tempDataDeleted++;
										delete_temp_record($user_uppercase,'ignore');
										$tempDataDeleted++;
									}
									
									if (!$registrationRecord && $verifiedUser && $validFormat && $tempRegister && $tempIgnore && $emailSignup) { 
											$debugData .= "NYYYYY: no signup record, verified, valid format, tempRegister is set, tempIgnore is set, emailSignup is set<br />
															Program error. tempRegister and tempIgnore can not be set at the same time. 
															delete tempRegister. Delete tempIgnore<br /><br />";
										$allUsersArray[$user_uppercase]['hasError']	= 'Y';
										$allUsersArray[$user_uppercase]['theError']	.= 'Program error. Deleting temp_data records<br />';
										delete_temp_record($user_uppercase,'register');
										$tempDataDeleted++;
										delete_temp_record($user_uppercase,'ignore');
										$tempDataDeleted++;
									}
									if (!$registrationRecord && $verifiedUser && $validFormat && $tempRegister && $tempIgnore && !$emailSignup) {
											$debugData .= "NYYYYN: no signup record, verified, valid format, tempRegister is set, tempIgnore is set, no emailSignup<br />
															Program error. tempRegister and tempIgnore can not be set at the same time. 
															delete tempRegister. Delete tempIgnore<br /><br />";
										$allUsersArray[$user_uppercase]['hasError']	= 'Y';
										$allUsersArray[$user_uppercase]['theError']	.= 'Program error. Deleting temp_data records<br />';
										delete_temp_record($user_uppercase,'register');
										$tempDataDeleted++;
										delete_temp_record($user_uppercase,'ignore');
										$tempDataDeleted++;
									}
									if (!$registrationRecord && $verifiedUser && !$validFormat && $tempRegister && $tempIgnore && $emailSignup) { 
											$debugData .= "NYNYYY: no signup record, verified, invalid format, tempRegister is set, tempIgnore is set, emailSignup is set<br />
															Program error. tempRegister and tempIgnore can not be set at the same time. 
															delete tempRegister. Delete tempIgnore<br /><br />";
										$allUsersArray[$user_uppercase]['hasError']	= 'Y';
										$allUsersArray[$user_uppercase]['theError']	.= 'Program error. Deleting temp_data records<br />';
										delete_temp_record($user_uppercase,'register');
										$tempDataDeleted++;
										delete_temp_record($user_uppercase,'ignore');
										$tempDataDeleted++;
									}
									if (!$registrationRecord && $verifiedUser && !$validFormat && $tempRegister && $tempIgnore && !$emailSignup) {
											$debugData .= "NYNYYN: no signup record, verified, invalid format, tempRegister is set, tempIgnore is set, no emailSignup<br />
															Program error. tempRegister and tempIgnore can not be set at the same time. 
															delete tempRegister. Delete tempIgnore<br /><br />";
										$allUsersArray[$user_uppercase]['hasError']	= 'Y';
										$allUsersArray[$user_uppercase]['theError']	.= 'Program error. Deleting temp_data records<br />';
										delete_temp_record($user_uppercase,'register');
										$tempDataDeleted++;
										delete_temp_record($user_uppercase,'ignore');
										$tempDataDeleted++;
									}
									if (!$registrationRecord && !$verifiedUser && $validFormat && $tempRegister && $tempIgnore && $emailSignup) { 
											$debugData .= "NNYYYY: no signup record, unverified, valid format, tempRegister is set, tempIgnore is set, emailSignup is set<br />
															Program error. tempRegister and tempIgnore can not be set at the same time. 
															delete tempRegister. Delete tempIgnore<br /><br />";
										$allUsersArray[$user_uppercase]['hasError']	= 'Y';
										$allUsersArray[$user_uppercase]['theError']	.= 'Program error. Deleting temp_data records<br />';
										delete_temp_record($user_uppercase,'register');
										$tempDataDeleted++;
										delete_temp_record($user_uppercase,'ignore');
										$tempDataDeleted++;
									}
									if (!$registrationRecord && !$verifiedUser && $validFormat && $tempRegister && $tempIgnore && !$emailSignup) {
											$debugData .= "NNYYYN: no signup record, unverified, valid format, tempRegister is set, tempIgnore is set, no emailSignup<br />
															Program error. tempRegister and tempIgnore can not be set at the same time. 
															delete tempRegister. Delete tempIgnore<br /><br />";
										$allUsersArray[$user_uppercase]['hasError']	= 'Y';
										$allUsersArray[$user_uppercase]['theError']	.= 'Program error. Deleting temp_data records<br />';
										delete_temp_record($user_uppercase,'register');
										$tempDataDeleted++;
										delete_temp_record($user_uppercase,'ignore');
										$tempDataDeleted++;
									}
									if (!$registrationRecord && !$verifiedUser && !$validFormat && $tempRegister && $tempIgnore && $emailSignup) { 
											$debugData .= "NNNYYY: no signup record, unverified, invalid format, tempRegister is set, tempIgnore is set, emailSignup is set<br />
															Program error. tempRegister and tempIgnore can not be set at the same time. 
															delete tempRegister. Delete tempIgnore<br /><br />";
										$allUsersArray[$user_uppercase]['hasError']	= 'Y';
										$allUsersArray[$user_uppercase]['theError']	.= 'Program error. Deleting temp_data records<br />';
										delete_temp_record($user_uppercase,'register');
										$tempDataDeleted++;
										delete_temp_record($user_uppercase,'ignore');
										$tempDataDeleted++;
									}
									if (!$registrationRecord && !$verifiedUser && !$validFormat && $tempRegister && $tempIgnore && !$emailSignup) {
											$debugData .= "NNNYYN: no signup record, unverified, invalid format, tempRegister is set, tempIgnore is set, no emailSignup<br />
															Program error. tempRegister and tempIgnore can not be set at the same time. 
															delete tempRegister. Delete tempIgnore<br /><br />";
										$allUsersArray[$user_uppercase]['hasError']	= 'Y';
										$allUsersArray[$user_uppercase]['theError']	.= 'Program error. Deleting temp_data records<br />';
										delete_temp_record($user_uppercase,'register');
										$tempDataDeleted++;
										delete_temp_record($user_uppercase,'ignore');
										$tempDataDeleted++;
									}
									
									
									//////////??????????/////////
									
									
									if ($registrationRecord && $verifiedUser && $validFormat && $tempRegister && !$tempIgnore) {
											$debugData .= "YYYYN- has signed up, verified, valid format, tempRegister is set, no tempIgnore<br />
															User got user_login and verified but didn't sign up. Has received registration reminder 
															email and has now signed up. Delete tempRegister<br /><br />";
										$allUsersArray[$user_uppercase]['theError']	.= 'Deleting temp_data Register record<br />';
										$deleteTempRegisterArray[]					= "$user_login&register";
									}
									if ($registrationRecord && $verifiedUser && $validFormat && !$tempRegister && $tempIgnore) { 
											$debugData .= "YYYNY- has signed up, verified, valid format, no tempRegister, tempIgnore is set<br />
											No need anymore for tempIgnore. Delete tempIgnore<br /><br /><br />";
										$allUsersArray[$user_uppercase]['theError']	.= 'Deleting unnecessary ignore record in temp_data<br />';
										$deleteTempIgnoreArray[]					= "$user_login&ignore";
									}
									if ($registrationRecord && $verifiedUser && $validFormat && !$tempRegister && !$tempIgnore) {
											$debugData .= "YYYNN- has signed up, verified, valid format, no tempRegister, no tempIgnore<br />
											No action needed<br /><br />";
									}
									if ($registrationRecord && $verifiedUser && !$validFormat && $tempRegister && !$tempIgnore) {
											$debugData .= "YYNYN- has signed up, verified, invalid format, tempRegister is set, no tempIgnore<br />
															User got a user_login which is invalid and signed up. A tempRegister record was written 
															to start a ten-day countdown. Check to see if ten days have passed. If so, 
											recommend ignoring the error\<br /><br />";
										if ($tenDayPlus) {
											$allUsersArray[$user_uppercase]['hasError']	= 'Y';
											$allUsersArray[$user_uppercase]['theError']	.= 'User has invalid user_login<br />
																							Ten days have passed<br /><br />';
										}
									}
									if ($registrationRecord && $verifiedUser && !$validFormat && !$tempRegister && $tempIgnore) { 
											$debugData .= "YYNNY- has signed up, verified, invalid format, no tempRegister, tempIgnore is set<br />
											tempIgnore has been set to ignore this error. No action needed<br /><br />";
									}
									if ($registrationRecord && $verifiedUser && !$validFormat && !$tempRegister && !$tempIgnore) {
//											$debugData .= "YYNNN- as signed up, verified, invalid format, no tempRegister, no tempIgnore<br />
//															User has obtained an invalid user_login and signed up. This is the first time we're 
//															seeing this record. Set tempRegister for a ten-day countdown. Show error.<br /><br />";
//										$allUsersArray[$user_uppercase]['hasError']	= 'Y';
//										$allUsersArray[$user_uppercase]['theError']	.= "Username is invalid<br />
//																						Signed up with invalid user_login<br />
//																						Set ten-day timer<br /><br />";
//										$setTempRegisterArray[]						= "$user_login&$user_role";
									}
									if ($registrationRecord && !$verifiedUser && $validFormat && $tempRegister && !$tempIgnore) {
											$debugData .= "YNYYN- has signed up, unverified, valid format, tempRegister is set, no tempIgnore<br />
															User signed up before user_logins. Has obtained user_login but not verified. Ten-day 
															timer has already been set. See if time is up. If so, show message and recommend 
											ignoring the error. Otherwise, show error and when the ten-days are up.<br /><br />";
										if ($tenDayPlus) {
											$allUsersArray[$user_uppercase]['hasError']	= 'Y';
											$allUsersArray[$user_uppercase]['theError']	.= "User signed up before user_logins were implemented<br />
																							User_login is not verified<br />
																							User registration and user_login have valid format<br />
																							Verify reminder email has been sent<br />
																							Ten-day timer has expired<br /><br /><br />";
										} else {
											$allUsersArray[$user_uppercase]['hasError']	= 'Y';
											$allUsersArray[$user_uppercase]['theError']	.= "User signed up before user_logins were implemented<br />
																							User_login is not verified<br />
																							User registration and user_login have valid format<br />
																							Verify reminder email was sen $date_written<br />
																							Ten-day timer set. Error will continue to be displayed until<br />
																							$tenDayDate<br /><br />";
	//										$setTempRegisterArray[]						= "$user_login&$user_role";
										}	
									}
									
									if ($registrationRecord && !$verifiedUser && $validFormat && !$tempRegister && $tempIgnore) { 
											$debugData .= "YNYNY- has signed up, unverified, valid format, no tempRegister, tempIgnore is set<br />
															User is unverified and tempIgnore is set. No action taken<br /><br />";
									}
									if ($registrationRecord && !$verifiedUser && $validFormat && !$tempRegister && !$tempIgnore) {
											$debugData .= "YNYNN- has signed up, unverified, valid format, no tempRegister, no tempIgnore<br />
															User signed up before user_logins were implemented. Has since gotten a user_login but 
															has not verified. Sending verify reminder email. Setting ten-day countdown timer";
										$allUsersArray[$user_uppercase]['hasError']	= 'Y';
										$allUsersArray[$user_uppercase]['theError']	.= "User signed up before user_logins were implemented<br />
																						Has signup record<br />
																						User_login record is unverified<br />
																						Sending verify reminder email <br />
																						Set ten-day timer<br /><br />";
										$setTempRegisterArray[]							= "$user_login&$user_role";
										$sendVerifyEmailArray[]							= "$user_email&$user_uppercase";
									}
									if ($registrationRecord && !$verifiedUser && !$validFormat && $tempRegister && !$tempIgnore) {
											$debugData .= "YNNYN- has signed up, unverified, invalid format, tempRegister is set, no tempIgnore<br />
															We've seen this record before. The ten-day timer is set.<br /><br />";
										if ($tenDayPlus) {
											$allUsersArray[$user_uppercase]['hasError']	= 'Y';
											$allUsersArray[$user_uppercase]['theError']	.= "User has a signup record<br />
																							User is unverified<br />
																							Username and signup record callsign is invalid<br />
																							Ten-day time has expired<br /><br /><br />";
										
										} else {
											$allUsersArray[$user_uppercase]['hasError']	= 'Y';
											$allUsersArray[$user_uppercase]['theError']	.= "User has a signup record<br />
																							User is unverified<br />
																							Username and signup record callsign is invalid<br />
																							Ten-day timer will expire on $tenDayDate<br /><br />";
											
										}
									}
									if ($registrationRecord && !$verifiedUser && !$validFormat && !$tempRegister && $tempIgnore) { 
											$debugData .= "YNNNY- has signed up, unverified, invalid format, no tempRegister, tempIgnore is set<br />
															tempIgnore is set. No action taken<br /><br /><br />";
									}
	
									if ($registrationRecord && !$verifiedUser && !$validFormat && !$tempRegister && !$tempIgnore) {
											$debugData .= "YNNNN- has signed up, unverified, invalid format, no tempRegister, no tempIgnore<br />
															User has a signup record from before user_logins were implemented. Has created a user_login. 
															Both the user_login and registration callsign are invalid. We're seeing this record for the 
											first time.<br /><br />";
										$allUsersArray[$user_uppercase]['hasError']	= 'Y';
										$allUsersArray[$user_uppercase]['theError']	.= "User has signed up before unsernames were imiplemented<br />
																						Username is unverified<br />
																						Username and callsign format is invalid<br />
																						Ten-day timer set<br /><br />";
										$setTempRegisterArray[]						= "$user_login&$user_role";
									}
	
									
									// when there is no signup record with the callsign = the user_login, there might be a registration 
									// record for the same user. That record is found by the user's email address
									
									if (!$registrationRecord && $verifiedUser && $validFormat && $tempRegister && !$tempIgnore && $emailSignup) {
											$debugData .= "NYYYNY: no signup record, verified, valid format, tempRegister is set, no tempIgnore, and has emailSignup<br />
														 Since tempRegister is set, we've seen this record before and the ten-day countdown was set. 
														 See if the countdown has expired.<br /><br />";
										if ($tenDayPlus) {
											$allUsersArray[$user_uppercase]['hasError']	= 'Y';
											$allUsersArray[$user_uppercase]['theError']	.= "User has signup record with callsign of $registrationCallsign<br />
																							Signup callsign has valid format<br />
																							Has a user_login record<br />
																							Ten-day timer expired on $tenDayDate<br /><br />";
										} else {
											$allUsersArray[$user_uppercase]['hasError']	= 'Y';
											$allUsersArray[$user_uppercase]['theError']	.= "User has signup record with callsign of $registrationCallsign<br />
																							Callsign valid format<br />
																							Username record exists as $user_login<br />
																							Ten-day timer expires on $tenDayDate<br /><br /><br />";
										}
									}
								
									if (!$registrationRecord && $verifiedUser && $validFormat && $tempRegister && !$tempIgnore && !$emailSignup) {
											$debugData .= "NYYYNN: no signup record, verified, valid format, tempRegister is set, no tempIgnore, and no emailSignup<br />
															Has a user_login, but no signup record by the user_login. Username has valid format. We've seen this record before. Ten-day 
															is set. If expired, recommend ignore<br /><br />";
										if ($tenDayPlus) {
											$allUsersArray[$user_uppercase]['hasError']	= 'Y';
											$allUsersArray[$user_uppercase]['theError']	.= "Has user_login record<br />
																							User does not have a signup record<br />
																							Username format is valid<br />
																							Reminder email was sent $date_written<br />
																							Ten-day timer expired on $tenDayDate<br /><br />";
										} else {
											$allUsersArray[$user_uppercase]['hasError']	= 'Y';
											$allUsersArray[$user_uppercase]['theError']	.= "User hasuser_login record<br />
																							Callsign valid format<br />
																							No signup record<br />
																							Reminder email was sent $date_written<br />
																							Ten-day timer expires on $tenDayDate<br /><br />";
										}
									}
	
									if (!$registrationRecord && $verifiedUser && $validFormat && !$tempRegister && $tempIgnore && $emailSignup) { 
											$debugData .= "NYYNYY: no signup record, verified, valid format, no tempRegister, tempIgnore is set, and has emailSignup<br />
															Have seen this record before and tempIgnore is set. No action needed<br /><br />";
									}
									if (!$registrationRecord && $verifiedUser && $validFormat && !$tempRegister && $tempIgnore && !$emailSignup) { 
											$debugData .= "NYYNYN: no signup record, verified, valid format, no tempRegister, tempIgnore is set, and no emailSignup<br />
															Since tempIgnore is set, no action taken<br /><br />";
									}
/*							
									 if (!$registrationRecord && $verifiedUser && $validFormat && $tempRegister && !$tempIgnore && $emailSignup) { 
											$debugData .= "NYYYNY: no signup record, verified, valid format, tempRegister is set, no tempIgnore, emailSignup is set<br />
															Have seen this record before. tempRegister is set, so the ten-day countdown is happening. If countdown 
															has expired, Show final reminder.<br /><br />";
										if ($tenDayPlus) {
											$allUsersArray[$user_uppercase]['hasError']	= 'Y';
											$allUsersArray[$user_uppercase]['theError']	.= "Has user_login record<br />
																							User has a signup record with callsign $registrationCallsign<br />
																							Username format is valid<br />
																							Reminder email has been sent<br />
																							Ten-day timer expired on $tenDayDate<br /><br />";
										} else {
											$allUsersArray[$user_uppercase]['hasError']	= 'Y';
											$allUsersArray[$user_uppercase]['theError']	.= "User has a signup record with callsign $registrationCallsign<br />
																							User has user_name of $user_login<br />
																							Username valid format<br />
																							Reminder email has been sent<br />
																							Ten-day timer expires on $tenDayDate<br /><br />";
										}
									}
									
									if (!$registrationRecord && $verifiedUser && $validFormat && $tempRegister && !$tempIgnore && !$emailSignup) {
											$debugData .= "NYYYNN: no signup record, verified, valid format, tempRegister is set, no tempIgnore, no emailSignup<br />
															Have seen this record before. Ten-day timer is set. If expired, recommend tempIgnore<br /><br />";
										if ($tenDayPlus) {
											$allUsersArray[$user_uppercase]['hasError']	= 'Y';
											$allUsersArray[$user_uppercase]['theError']	.= "Has user_login record<br />
																							User no signup record<br />
																							Username format is valid<br />
																							Reminder email has been sent<br />
																							Ten-day timer expired on $tenDayDate<br /><br />";
										} else {
											$allUsersArray[$user_uppercase]['hasError']	= 'Y';
											$allUsersArray[$user_uppercase]['theError']	.= "Has user_login record<br />
																							Username valid format<br />
																							Reminder email has been sent<br />
																							User has not siogned up<br />														
																							Ten-day timer expires on $tenDayDate<br /><br />";
										}
									}
*/	
									if (!$registrationRecord && $verifiedUser && $validFormat && !$tempRegister && $tempIgnore && !$emailSignup) {
											$debugData .= "NYYNYN: no signup record, verified, valid format, no tempRegister, tempIgnore is set, no emailSignup<br />
															Have seen this record before and tempIgnore is set. No further action<br /><br />";
									}
									if (!$registrationRecord && $verifiedUser && !$validFormat && $tempRegister && !$tempIgnore && !$emailSignup) {
											$debugData .= "NYNYNN: no signup record, verified, invalid format, tempRegister is set, no tempIgnore, no emailSignup<br />
															Have seen this record before.Ten-day timer is set. <br /><br />";
										if ($tenDayPlus) {
											$allUsersArray[$user_uppercase]['hasError']	= 'Y';
											$allUsersArray[$user_uppercase]['theError']	.= "Has user_login record<br />
																							No signup record<br />
																							Username format is not valid<br />
																							Ten-day timer expired on $tenDayDate<br /><br />";
										} else {
											$allUsersArray[$user_uppercase]['hasError']	= 'Y';
											$allUsersArray[$user_uppercase]['theError']	.= "Has user_login record<br />
																							User has no signup record<br />														
																							Ten-day timer expires on $tenDayDate<br /><br />";
										}
									}
									if (!$registrationRecord && $verifiedUser && !$validFormat && !$tempRegister && $tempIgnore && $emailSignup) { 
											$debugData .= "NYNNYY: no signup record, verified, invalid format, no tempRegister, tempIgnore is set, emailSignup is set<br />
															Have seen this record before. tempIgnore is set. No further action<br /><br />";
									}
									if (!$registrationRecord && $verifiedUser && !$validFormat && !$tempRegister && $tempIgnore && !$emailSignup) {
											$debugData .= "NYNNYN: no signup record, verified, invalid format, no tempRegister, tempIgnore is set, no emailSignup<br />
															Have seen this record before. tempIgnore is set. No further action<br /><br />";
									}
									if (!$registrationRecord && $verifiedUser && !$validFormat && !$tempRegister && !$tempIgnore && $emailSignup) { 
											$debugData .= "NYNNNY: no signup record, verified, invalid format, no tempRegister, no tempIgnore, emailSignup is set<br />
															We're seeing this record for the first time. Has unsername but invalid format. Has a signup record 
															with a callsign $registrationCallsign. 
											set tempRegister ten-day timer<br /><br />";
										$allUsersArray[$user_uppercase]['hasError']	= 'Y';
										$allUsersArray[$user_uppercase]['theError']	.= "Has user_login record<br />
																						User has a signup record with callsign of $registrationCallsign<br />
																						Setting ten-day timer<br /><br />";
										$setTempRegisterArray[]						= "$user_login&$user_role";
															
									}
									if (!$registrationRecord && $verifiedUser && $validFormat && !$tempRegister && !$tempIgnore && !$emailSignup) {
											$debugData .= "NYYNNN: no signup record, verified, valid format, no tempRegister, no tempIgnore, no emailSignup<br />
															We're seeing this record for the first time. Has user_login and valid format. No tempRegister, 
															no tempIgnore, and no signup record. Send registration reminder email. Set
											tempRegister ten-day countdown<br /><br />";
										$allUsersArray[$user_uppercase]['hasError']	= 'Y';
										$allUsersArray[$user_uppercase]['theError']	.= "Has user_login record<br />
																						Username is valid format<br />
																						User does not have a signup record<br />
																						Sending registration reminder email<br />
																						Setting tem-day timer<br /><br />";
										$setTempRegisterArray[]						= "$user_login&$user_role";
										$sendSignupEmailArray[]						= "$user_email&$user_uppercase";
									}
									if (!$registrationRecord && $verifiedUser && $validFormat && !$tempRegister && !$tempIgnore && $emailSignup) {
											$debugData .= "NYYNNY: no signup record, verified, valid format, no tempRegister, no tempIgnore, has emailSignup<br />
															We're seeing this record for the first time. Has user_login and valid format. No tempRegister, 
															no tempIgnore, but has a signup record with callsign $registrationCallsign. Set
															tempRegister ten-day countdown.<br /><br />";
										$allUsersArray[$user_uppercase]['hasError']	= 'Y';
										$allUsersArray[$user_uppercase]['theError']	.= "Has user_login record<br />
																						Username is valid format<br />
																						User has a signup record with callsign of $registrationCallsign<br />
																						Setting ten-day timer<br /><br />";
										$setTempRegisterArray[]					 	= "$user_login&$user_role";
									}
									if (!$registrationRecord && $verifiedUser && !$validFormat && !$tempRegister && !$tempIgnore && !$emailSignup) {
											$debugData .= "NYNNNN: no signup record, verified, invalid format, no tempRegister, no tempIgnore, no emailSignup<br />
															We're seeing this record for the first time. Has user_login but invalid format. No tempRegister, 
															no tempIgnore, and no signup record. Set
															tempRegister ten-day countdown<br /><br />";
										$allUsersArray[$user_uppercase]['hasError']	= 'Y';
										$allUsersArray[$user_uppercase]['theError']	.= "Has user_login record<br />
																						User does not have a signup record<br />
																						Setting ten-day timer<br /><br />";
										$setTempRegisterArray[]						= "$user_login&$user_role";
									}
									if (!$registrationRecord && !$verifiedUser && $validFormat && $tempRegister && !$tempIgnore && $emailSignup) { 
											$debugData .= "NNYYNY: no signup record, unverified, valid format, tempRegister is set, no tempIgnore, emailSignup is set<br />
															Have seen this record before as tempRegister is set. If time expired, recommend verifying.<br /><br />";
										if ($tenDayPlus) {
											$allUsersArray[$user_uppercase]['hasError']	= 'Y';
											$allUsersArray[$user_uppercase]['theError']	.= "User has a valid user_login<br />
																							User has a signup record with a callsign of $registrationCallsign<br />
																							User has not verified the user_login<br />
																							Ten-day countdown expired on $tenDayDate<br /><br />";
									
										} else {
											$allUsersArray[$user_uppercase]['hasError']	= 'Y';
											$allUsersArray[$user_uppercase]['theError']	.= "User has a valid user_login<br />
																							User has a signup record with a callsign of $registrationCallsign<br />
																							User has not verified the user_login<br />
																							Ten-day countdown will expire on $tenDayDate<br /><br />";
										
										}
									}
									if (!$registrationRecord && !$verifiedUser && $validFormat && $tempRegister && !$tempIgnore && !$emailSignup) {
											$debugData .= "NNYYNN: no signup record, unverified, valid format, tempRegister is set, no tempIgnore, no emailSignup<br />
															We've seen this record before. unverified valid user_login and no signup record. tempRegister is set.<br /><br />";
										if ($threeDayPlus) {
											$allUsersArray[$user_uppercase]['hasError']	= 'Y';
											$allUsersArray[$user_uppercase]['theError']	.= "User has a valid user_login<br />
																							User has not verified the user_login<br />
																							There is no signup record<br />
																							Three-day countdown expired on $threeDayDate<br >
																							<b>User has not verified.<br /><br />";
										
										} else {
											$allUsersArray[$user_uppercase]['hasError']	= 'Y';
											$allUsersArray[$user_uppercase]['theError']	.= "User has a valid user_login<br />
																							User has not verified the user_login<br />
																							There is no signup record<br />
																							Three-day countdown will expire on $threeDayDate<br >
																							If user doesn't verify by then, recommend deleting the user<br /><br />";
										
										}
									}
									if (!$registrationRecord && !$verifiedUser && $validFormat && !$tempRegister && $tempIgnore && $emailSignup) { 
											$debugData .= "NNYNYY: no signup record, unverified, valid format, no tempRegister, tempIgnore is set, emailSignup is set<br />
															Have seen this record before. tempIgnore is set. No further action<br /><br />";
									}
									if (!$registrationRecord && !$verifiedUser && $validFormat && !$tempRegister && $tempIgnore && !$emailSignup) {
											$debugData .= "NNYNYN: no signup record, unverified, valid format, no tempRegister, tempIgnore is set, no emailSignup<br />
															We've seen this record before. For some reason tempIgnore is set. No further action.<br /><br />";
									}
									if (!$registrationRecord && !$verifiedUser && $validFormat && !$tempRegister && !$tempIgnore && $emailSignup) { 
											$debugData .= "NNYNNY: no signup record, unverified, valid format, no tempRegister, no tempIgnore, emailSignup is set<br />
															First time seeing this record. Has an unverified but valid user_login and a signup record with a 
															callsign different from the user_login. Set tempRecord for three-day time for user to verify. If not 
															verified by then, will recommend verifying. If the user_login fits a valid callsign, send a verify 
															reminder email. Meanwhile, recommend getting user_login and callsign sync'd 
															up</br /><br />";
											
											if ($validCallsign) {
												$allUsersArray[$user_uppercase]['hasError']	= 'Y';
												$allUsersArray[$user_uppercase]['theError']	.= "User has an valid user_login that matches callsign format<br />
																								Username is not verified<br />
																								User has a signup record with callsign of $registrationCallsign<br />
																								Setting three-day countdown to expire on $threeDayDate<br />
																								Sending verify reminder email<br /><br />";
												$sendVerifyEmailArray[]						= "$user_email&$user_uppercase";
											} else {
												$allUsersArray[$user_uppercase]['hasError']	= 'Y';
												$allUsersArray[$user_uppercase]['theError']	.= "User has an valid user_login that does not match callsign format<br />
																								Username is not verified<br />
																								User has a signup record with callsign of $registrationCallsign<br />
																								Setting three-day countdown to expire on $threeDayDate<br /><br />";
											}
										$setTempRegisterArray[]						= "$user_login&$user_role";
									}
									if (!$registrationRecord && !$verifiedUser && $validFormat && !$tempRegister && !$tempIgnore && !$emailSignup) {
											$debugData .= "NNYNNN: no signup record, unverified, valid format, no tempRegister, no tempIgnore, no emailSignup<br />
															First time we've seen this record. Username is valid format but unverified. No signup record found. 
															if user_login fits the callsign format, send a verify reminder email.<br /><br />";
											
											if ($validCallsign) {
												$allUsersArray[$user_uppercase]['hasError']	= 'Y';
												$allUsersArray[$user_uppercase]['theError']	.= "User has an valid user_login that matches callsign format<br />
																								Username is not verified<br />
																								User has does not have a signup record<br />
																								Setting three-day countdown to expire on $threeDayDate when 
																								the recommendation will be to delete the user<br />
																								Sending verify reminder email<br /><br />";
												$sendVerifyEmailArray[]						= "$user_email&$user_uppercase";
											} else {
												$allUsersArray[$user_uppercase]['hasError']	= 'Y';
												$allUsersArray[$user_uppercase]['theError']	.= "User has an valid user_login that does not match callsign format<br />
																								Username is not verified<br />
																								User has does not have a signup record<br />
																								Setting three-day countdown to expire on $threeDayDate when 
																								the recommendation will be to delete the user<br /><br />";
											}
										$setTempRegisterArray[]						= "$user_login&$user_role";
									}
									if (!$registrationRecord && !$verifiedUser && !$validFormat && $tempRegister && !$tempIgnore && $emailSignup) { 
											$debugData .= "NNNYNY: no signup record, unverified, invalid format, tempRegister is set, no tempIgnore, emailSignup is set<br />
															Have seen this record before. User has a signup record with invalid format and has a signup record 
															under a callsign different from the user_login. tempRegister is set.	<br /><br />";
										if ($threeDayPlus) {
											$allUsersArray[$user_uppercase]['hasError']	= 'Y';
											$allUsersArray[$user_uppercase]['theError']	.= "User has an invalid user_login<br />
																							Username is not verified<br />
																							User has has a signup record with callsign of $registrationCallsign<br />
																							Three-day countdown expired on $threeDayDate<br /><br />";
										
										} else {
											$allUsersArray[$user_uppercase]['hasError']	= 'Y';
											$allUsersArray[$user_uppercase]['theError']	.= "User has an invalid user_login<br />
																							Username is not verified<br />
																							User has has a signup record with callsign of $registrationCallsign<br />
																							Three-day countdown will expire on $threeDayDate<br /><br />";
										
										}
									}
									if (!$registrationRecord && !$verifiedUser && !$validFormat && $tempRegister && !$tempIgnore && !$emailSignup) {
											$debugData .= "NNNYNN: no signup record, unverified, invalid format, tempRegister is set, no tempIgnore, no emailSignup<br />
															Have seen this record before. User has an invalid user_login. tempRegister is set waiting on three-day 
															countdown. If expired, recommend deleting the user. <br /><br />";
										if ($threeDayPlus) {
											$allUsersArray[$user_uppercase]['hasError']	= 'Y';
											$allUsersArray[$user_uppercase]['theError']	.= "User has an invalid user_login<br />
																							Username is not verified<br />
																							User has does not have a signup record<br />
																							Three-day countdown expired on $threeDayDate<br /><br />";
										
										} else {
											$allUsersArray[$user_uppercase]['hasError']	= 'Y';
											$allUsersArray[$user_uppercase]['theError']	.= "User has an invalid user_login<br />
																							Username is not verified<br />
																							User has does not have a signup record<br />
																							Three-day countdown will expire on $threeDayDate<br />
																							If not verified will recommend deleting user_login<br /><br />";
										
										}
									}
									if (!$registrationRecord && !$verifiedUser && !$validFormat && !$tempRegister && $tempIgnore && $emailSignup) { 
											$debugData .= "NNNNYY: no signup record, unverified, invalid format, no tempRegister, tempIgnore is set, emailSignup is set<br />
															Have seen this record before. temmpIgnore is set. No action taen<br /><br />";
									}
									if (!$registrationRecord && !$verifiedUser && !$validFormat && !$tempRegister && $tempIgnore && !$emailSignup) {
											$debugData .= "NNNNYN: no signup record, unverified, invalid format, no tempRegister, tempIgnore is set, no emailSignup<br />
															Have seen this record before. tempIgnore is set. No action taken<br /><br />";
									}
									if (!$registrationRecord && !$verifiedUser && !$validFormat && !$tempRegister && !$tempIgnore && $emailSignup) { 
											$debugData .= "NNNNNY: no signup record, unverified, invalid format, no tempRegister, no tempIgnore, emailSignup is set<br />
															Unverified user with invalid format. Seeing for the first time. Has signup record with callsign $registrationCallsign.<br /> 
															Set te-day timer<br /><br />";
											$allUsersArray[$user_uppercase]['hasError']	= 'Y';
											$allUsersArray[$user_uppercase]['theError']	.= "User has an invalid user_login<br />
																							User is unverified<br />
																							User has a signup record with callsign $registrationCallsign<br /><br />
																							Ten-day timer set<br /><br />";
										$setTempRegisterArray[]						= "$user_login&$user_role";
											
									}
									if (!$registrationRecord && !$verifiedUser && !$validFormat && !$tempRegister && !$tempIgnore && !$emailSignup) {
											$debugData .= "NNNNNN: no signup record, unverified, invalid format, no tempRegister, no tempIgnore, no emailSignup<br />
															First time with this record. User obtained an invalid user_login and has not validated. No signup record 
															found. Set a three-day timer.<br /><br />";
										$allUsersArray[$user_uppercase]['hasError']	= 'Y';
										$allUsersArray[$user_uppercase]['theError']	.= "User has an invalid user_login<br />
																						Username is not verified<br />
																						User has does not have a signup record<br />
																						Three-day countdown will expire on $threeDayDate<br /><br />";
										$setTempRegisterArray[]						= "$user_login&$user_role";
									}
								}
							}
						}
					}
				}
				$content			.= "</table><p>Clicking on the email address will open a new email message</p>";
			}
		}			// end of checking user_logins
		
		if ($doAdvisorNoUsername) {
			// see if there are any students or advisor records with no corresponding user record
			// start with advisors
				$debugData .= "<br /><b>Looking for advisor anomolies</b><br /><br />";
			$missingAdvisorUserName	= FALSE;
			$sql					= "select * from $advisorTableName 
									where (semester = '$currentSemester' 
											or semester = '$nextSemester' 
											or semester = '$semesterTwo' 
											or semester = '$semesterThree' 
											or semester = '$semesterFour') 
											order by call_sign";
			$advisorResult		= $wpdb->get_results($sql);
			if ($advisorResult === FALSE) {
				handleWPDBError($jobname,$doDebug);
			} else {
				$numARows		= $wpdb->num_rows;
					$debugData .= "ran $sql<br /> and retrieved $numARows rows<br /><br />";
				if ($numARows > 0) {
					foreach($advisorResult as $advisorResultRow) {
						$advisor_call_sign		= $advisorResultRow->call_sign;
						$advisor_last_name		= $advisorResultRow->last_name;
						$advisor_first_name		= $advisorResultRow->first_name;
						$advisor_email			= $advisorResultRow->email;
						$date_created			= $advisorResultRow->date_created;
						$advisor_semester		= $advisorResultRow->semester;
						$advisor_email			= $advisorResultRow->email;
						$advisorDateCreated		= $advisorResultRow->date_created;
	
						
						// see if there is a user record for this callsign
						if(!array_key_exists($advisor_call_sign,$allUsersArray)) {
							// no user_login record
							$allUsersArray[$advisor_call_sign]	= array('last_name'=>$advisor_last_name, 
																	 'first_name'=>$advisor_first_name, 
																	 'display_name'=>'', 
																	 'user_registered'=>'', 
																	 'user_email'=>$advisor_email, 
																	 'id'=>0, 
																	 'user_role'=>'advisor',
																	 'tempIgnoreID'=>0,
																	 'tempRegisterID'=>0, 
																	 'hasError'=>'N', 
																	 'theError'=>"Advisor registration created on $advisorDateCreated<br />");
	
	
							$advisorNoUsername++;
							$missingAdvisorUserName			= TRUE;
								$debugData .= "<b>$advisor_call_sign No user_login record</b><br />";
							$allUsersArray[$advisor_call_sign]['hasError']	= 'Y';	 
							$allUsersArray[$advisor_call_sign]['theError']	.= "No user_login found for $advisor_call_sign<br /><br />";
						}
						// see if there is a tempRegister or a tempIgnore record
						$gotTempRecord		= FALSE;
						$tempIgnore1		= FALSE;
						$tempRegister1		= FALSE;
						$tempSQL			= "select * from wpw1_cwa_temp_data 
												where callsign = '$advisor_call_sign' and 
													  (token = 'register' or 
													   token = 'ignore') 
												order by date_written";
						$tempResult			= $wpdb->get_results($tempSQL);
						if ($tempResult === FALSE) {
							handleWPDBError($jobname,$doDebug);
						} else {
							$numTempRows	= $wpdb->num_rows;
								$debugData .= "ran $tempSQL<br />and retrieved $numTempRows rows<br /><br />";
							if ($numTempRows > 0) {
								$gotTempRecord	= TRUE;
								foreach ($tempResult as $tempResultRow) {
									$tempID			= $tempResultRow->record_id;
									$tempData		= $tempResultRow->temp_data;
									$date_written	= $tempResultRow->date_written;
									
									if ($tempData == 'register') {
										$tempRegister1			= TRUE;
										$allUsersArray[$advisor_call_sign]['tempRegisterID']	= $tempID;	 
										$threeDayDate 			= date('Y-m-d H:i:s', strtotime($date_written . ' +3 days'));
										if ($nowDate > $threeDayDate) {
											$threeDayPlus		= TRUE;
										}
										$tenDayDate 			= date('Y-m-d H:i:s', strtotime($date_written . ' +10 days'));
										if ($nowDate > $tenDayDate) {
											$tenDayPlus			= TRUE;
										}
									} elseif ($tempData == 'ignore') {
										$tempIgnore1			= TRUE;
										$allUsersArray[$advisor_call_sign]['tempIgnoreID']	= $tempID;	 
									}
								}
							}
						}
						if ($missingAdvisorUserName) {
							if (!$tempRegister1 && !$tempIgnore1) {
									$debugData	.= "Checking Advisor Signups. No Username, no tempRegister, no tempIgnore<br />
													Seeing this record for the first time. Send register email. Set tempRegister 
													for a ten-day countdown<br /><br />";
								$allUsersArray[$advisor_call_sign]['hasError']	= 'Y';	 
								$allUsersArray[$advisor_call_sign]['theError']	.= "$advisor_call_sign does not have a user_login record<br />
																					First time with this record<br />
																					Sending register email<br />
																					Ten-day countdown timer will expire on $tenDayDat1<br /><br />";
								$sendRegisterEmailArray[]						= "$advisor_email&$advisor_call_sign";
								$setTempRegisterArray[]							= "$advisor_call_sign&$advisor";
							}
							if ($tempRegister1 && !$tempIgnore1) {
									$debugData	.= "Checking Advisor Signups. No user_login, tempRegister is set, no tempIgnore<br />
													Have seen this record before and set the ten-day timer. If timer has expired <br /><br />";
								if ($tenDayPlus) {
									$allUsersArray[$advisor_call_sign]['hasError']	= 'Y';	 
									$allUsersArray[$advisor_call_sign]['theError']	.= "$advisor_call_sign does not have a user_login record<br />
																						Register email request has been sent<br />
																						Ten-day countdown timer expired on $tenDayDay1<br />
																						User has not registerd<br /><br />";
								} else {
									$allUsersArray[$advisor_call_sign]['hasError']	= 'Y';	 
									$allUsersArray[$advisor_call_sign]['theError']	.= "$advisor_call_sign does not have a user_login record<br />
																						Register email has been sent<br />
																						Ten-day countdown timer will expire on $tenDayDay1<br /><br />";
								
								}							
							}
							if (!$tempRegister1 && $tempIgnore1) {
									$debugData	.= "Checking Advisor Signups. No user_login, no tempRegister, tempIgnore is set<br />
													tempIgnore is set. No further action<br /><br />";
							}
							if ($tempRegister1 && $tempIgnore1) {
									$debugData	.= "Checking Advisor Signups. No user_login, tempRegister is set, tempIgnore is set<br />
													Program Error. Delete tempRegister and tempIgnore<br /><br />";
								$allUsersArray[$advisor_call_sign]['hasError']	= 'Y';	 
								$allUsersArray[$advisor_call_sign]['theError']	.= "$advisor_call_sign does not have a user_login record<br />
																					Register email has been sent<br />
																					Both the ten-day countdown timer and ignore are set. Program Error!<br />
																					Deleting both temp_data Register and temp_data Ignore<br /><br />";
								$deleteTempRegisterArray[]						= "$advisor_call_sign&$advisor";
								$deleteTempIgnore								= "$advisor_call_sign&$advisor";
							}
						} else {			/// have a matching user_login record
							if ($tempIgnore1) {
								$deleteTempIgnoreArray[]				= "$advisor_call_sign&$advisor";
							}
							if ($tempRegister1) {
								$deleteTempRegisterArray[]				= "$advisor_call_sign&$advisor";
							}
						}
					}
				}
 			}
 		}


		if ($doStudentNoUsername) {
			// now do students
				$debugData .= "<br /><b>Looking for student anomolies</b><br />";
			$missingStudentUserName	= FALSE;
			$sql					= "select * from $studentTableName 
										where (semester = '$currentSemester' 
												or semester = '$nextSemester' 
												or semester = '$semesterTwo' 
												or semester = '$semesterThree' 
												or semester = '$semesterFour') 
										order by call_sign";
			$studentResult		= $wpdb->get_results($sql);
			if ($studentResult === FALSE) {
				handleWPDBError($jobname,$doDebug);
			} else {
				$numSRows		= $wpdb->num_rows;
					$debugData .= "ran $sql<br /> and retreived $numSRows rows<br /><br />";
				if ($numSRows > 0) {
					foreach($studentResult as $studentResultRow) {
						$student_call_sign		= $studentResultRow->call_sign;
						$student_last_name		= $studentResultRow->last_name;
						$student_first_name		= $studentResultRow->first_name;
						$student_email			= $studentResultRow->email;
						$student_response		= $studentResultRow->response;
						$student_semester		= $studentResultRow->semester;
						$date_created			= $studentResultRow->date_created;
						$student_email			= $studentResultRow->email;
						$studentRequestDate		= $studentResultRow->request_date;
	
						// see if there is a user_login record
						if (!array_key_exists($student_call_sign,$allUsersArray)) {
							// no user_login record
							$allUsersArray[$student_call_sign]	= array('last_name'=>$student_last_name, 
																		 'first_name'=>$student_first_name, 
																		 'display_name'=>'', 
																		 'user_registered'=>'', 
																		 'user_email'=>$student_email, 
																		 'id'=>0, 
																		 'user_role'=>'student', 
																		 'tempRegisterID'=>0,
																		 'tempIgnoreID'=>0,
																		 'hasError'=>'N', 
																		 'theError'=>"Student request date was $studentRequestDate<br />");
							$missingStudentUserName		= TRUE;
								$debugData .= "<b>$student_call_sign No user_login record</b><br />";
							$studentNoUsername++;
							$allUsersArray[$student_call_sign]['hasError']	= 'Y';	 
							$allUsersArray[$student_call_sign]['theError']	.= "No user_login found for $student_call_sign<br /><br />";
						} else {
							$debugData .= "has a allUserArray record<br /><br />";
						}	
						// see if there is a tempRegister or a tempIgnore record
						$tempRegister1	= FALSE;
						$TempIgnore1	= FALSE;
						$gotTempRecord		= FALSE;
						$tempSQL			= "select * from wpw1_cwa_temp_data 
												where callsign = '$student_call_sign' and 
													  (token = 'register' or 
													   token = 'ignore') 
												order by date_written";
						$tempResult			= $wpdb->get_results($tempSQL);
						if ($tempResult === FALSE) {
							handleWPDBError($jobname,$doDebug);
						} else {
							$numTempRows	= $wpdb->num_rows;
								$debugData .= "ran $tempSQL<br />and retrieved $numTempRows rows<br /><br />";
							if ($numTempRows > 0) {
								$gotTempRecord	= TRUE;
								foreach ($tempResult as $tempResultRow) {
									$tempID			= $tempResultRow->record_id;
									$tempData		= $tempResultRow->temp_data;
									$date_written	= $tempResultRow->date_written;
									
									if ($tempData == 'register') {
										$tempRegister1			= TRUE;
										$allUsersArray[$student_call_sign]['tempRegisterID']	= $tempID;	 
										$myIntayDate 			= date('Y-m-d H:i:s', strtotime($date_written . ' +3 days'));
										if ($nowDate > $threeDayDate) {
											$threeDayPlus		= TRUE;
										}
										$tenDayDate 			= date('Y-m-d H:i:s', strtotime($date_written . ' +10 days'));
										if ($nowDate > $tenDayDate) {
											$tenDayPlus			= TRUE;
										}
									} elseif ($tempData == 'ignore') {
										$tempIgnore1			= TRUE;
										$allUsersArray[$student_call_sign]['tempIgnoreID']	= $tempID;	 
									}
								}
							}
						}
						if ($missingStudentUserName) {
							if (!$tempRegister1 && !$tempIgnore1) {
									$debugData	.= "Checking Student Signups. No Username, no tempRegister, no tempIgnore<br />
													Seeing this record for the first time. Send register email. Set tempRegister 
													for a ten-day countdown<br /><br />";
								$allUsersArray[$student_call_sign]['hasError']	= 'Y';	 
								$allUsersArray[$student_call_sign]['theError']	.= "$student_call_sign does not have a user_login record<br />
																					First time with this record<br />
																					Sending register email<br />
																					Ten-day countdown timer will expire on $tenDayDat1<br /><br />";
								$sendRegisterEmailArray[]							= "$student_email&$student_call_sign";
								$setTempRegisterArray[]								= "$student_call_sign&advisor";
							}
							if ($tempRegister1 && !$tempIgnore1) {
									$debugData	.= "Checking Student Signups. No user_login, tempRegister is set, no tempIgnore<br />
													Have seen this record before and set the ten-day timer.<br /><br />";
								if ($tenDayPlus) {
									$allUsersArray[$student_call_sign]['hasError']	= 'Y';	 
									$allUsersArray[$student_call_sign]['theError']	.= "$student_call_sign does not have a user_login record<br />
																						Register email request has been sent<br />
																						Ten-day countdown timer expired on $tenDayDay1<br />
																						User has not registerd<br /><br /><br />";
								} else {
									$allUsersArray[$student_call_sign]['hasError']	= 'Y';	 
									$allUsersArray[$student_call_sign]['theError']	.= "$student_call_sign does not have a user_login record<br />
																						Register email has been sent<br />
																						Ten-day countdown timer will expire on $tenDayDay1<br /><br />";
								
								}							
							}
							if (!$tempRegister1 && $tempIgnore1) {
									$debugData	.= "Checking Student Signups. No user_login, no tempRegister, tempIgnore is set<br />
													tempIgnore is set. No further action<br /><br />";
							}
							if ($tempRegister1 && $tempIgnore1) {
									$debugData	.= "Checking Student Signups. No user_login, tempRegister is set, tempIgnore is set<br />
													Program Error. Delete tempRegister and tempIgnore<br /><br />";
								$allUsersArray[$student_call_sign]['hasError']	= 'Y';	 
								$allUsersArray[$student_call_sign]['theError']	.= "$student_call_sign does not have a user_login record<br />
																					Register email has been sent<br />
																					Both the ten-day countdown timer and ignore are set. Program Error!<br />
																					Deleting both temp_data Register and temp_data Ignore<br /><br />";
								$deleteTempRegisterArray[]						= "$student_call_sign&$advisor";
								$deleteTempIgnoreArray[]						= "$student_call_sign&$advisor";
							}
						} else {			/// have a matching user_login record
							if ($tempIgnore1) {
								$deleteTempIgnoreArray[]						= "$student_call_sign&$advisor";
							}
							if ($tempRegister1) {
								$deleteTempRegisterArray[]						= "$student_call_sign&$advisor";
							}
						}
					}
				}
			}
		}
		
		
		
///// All processing done. Do the requested actions

			$debugData	.= "<br /><b>All Processing Done</b><br />
							sendSignupEmailArray:<br /><pre>";
			$debugData	.= print_r($sendSignupEmailArray,TRUE);
			$debugData	.= "</pre><br />";

			$debugData	.= "<br />sendRegisterEmailArray:<br /><pre>";
			$debugData	.= print_r($sendRegisterEmailArray,TRUE);
			$debugData	.= "</pre><br />";

			$debugData	.= "<br />sendVerifyEmailArray:<br /><pre>";
			$debugData	.= print_r($sendVerifyEmailArray,TRUE);
			$debugData	.= "</pre><br />";

			$debugData	.= "<br />setTempRegisterArray:<br /><pre>";
			$debugData	.= print_r($setTempRegisterArray,TRUE);
			$debugData	.= "</pre><br />";

			$debugData	.= "<br />deleteTempRegisterArray:<br /><pre>";
			$debugData	.= print_r($deleteTempRegisterArray,TRUE);
			$debugData	.= "</pre><br />";

			$debugData	.= "<br />deleteTempIgnoreArray:<br /><pre>";
			$debugData	.= print_r($deleteTempIgnoreArray,TRUE);
			$debugData	.= "</pre><br />";

			$debugData	.= "<br />setTempIgnoreArray:<br /><pre>";
			$debugData	.= print_r($setTempIgnoreArray,TRUE);
			$debugData	.= "</pre><br />";

		if (count($sendSignupEmailArray) > 0) {
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
Academy</a> enter your usename and password, and sign up by clicking on the 'Sign up' button.<br />73,<br />CW Academy";
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
				$registrationEmailCount++;
			}
		}
 
 		if (count($sendRegisterEmailArray) > 0) {
			foreach($sendRegisterEmailArray as $thisValue) {
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
				$theSubject	 	= "CW Academy -- Please Set Up your Username and Password for CW Academy";
				$theContent		= "<p>To: $thisLastName, $thisFirstName:</p>
<p>Since you signed up to $textStr, CW Academy has implemented a new 
user management system which will further isolate your personal information from the Internet. In order 
to have access to the CW Academy website, you will need to obtain a username and a password.</p>
<p>Please go to the <a href='http://localhost:3073/program-list/'>CW Academy</a> and set up 
your username and password. <b>NOTE!</b> Your username MUST be your amateur radio callsign, or, 
if you don't have a callsign, it must be some form of your last name.</p><br />73,<br />CW Academy";

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
				$registerEmailCount++;
			}
		}
		
		if (count($sendVerifyEmailArray) > 0) {
			foreach($sendVerifyEmailArray as $thisValue) {
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
				$theSubject	 	= "CW Academy -- Please Verify your Username and Password for CW Academy";
				$theContent		= "<p>To: $thisLastName, $thisFirstName:</p>
<p>You have obtained a username and password for the CW Academy 
website, however, you have not verified that information. After
creating yourusername and password, CW Academy has sent you 
an email with a link to verify that you were the person that 
created the username and password.</p>
<p>Please find that email and click on the link. If you can't 
find the email, go to < href='http://localhost:3073/program-list/'>CW Academy</a> 
and enter your username and password. The program will send 
you another email with a link to verify your username and password.</p>
<p><b>NOTE: </b>Setting up your username and password DOES NOT automatically 
sign you up for a class. After verifying your username, you will need to log in 
to the CW Academy website and sign up for a class.</p>
<br />73,<br />CW Academy";
	
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
				$verifyEmailCount++;
			}
		}		
		
		
		if (count($setTempRegisterArray) > 0) {
			foreach ($setTempRegisterArray as $thisData) {
					$debugData .= "adding temp_data record<br >";
				$myArray			= explode("&",$thisData);
				$thisCallSign		= $myArray[0];
				$thisRole			= $myArray[1];
				$tempResult			= $wpdb->insert('wpw1_cwa_temp_data', 
											array('callsign'=>$thisCallSign, 
													'token'=>'register', 
													'temp_data'=>$thisRole, 
													'date_written'=>$nowDate),
											array('%s','%s','%s','%s'));
				if ($tempResult === FALSE) {
					handleWPDBError($jobname,$doDebug);
				} else {
						$debugData .= "added $user_login Register to temp_data<br />";
					$tempDataAdded++;
				}
			}
		}
		
		if (count($deleteTempRegisterArray) > 0) {
			foreach($deleteTempRegisterArray as $thisData) {
					$debugData .= "Delete the temp_data";
				$myArray			= explode("&",$thisData);
				$thisCallSign		= $myArray[0];
				$thisRole			= $myArray[1];
				delete_temp_record($thisCallSign, 'register');
				$tempDataDeleted++;
			}
		}

		if (count($deleteTempIgnoreArray) > 0) {
			foreach($deleteTempIgnoreArray as $thisData) {
				$debugData .= "Delete the temp_data";
				$myArray			= explode("&",$thisData);
				$thisCallSign		= $myArray[0];
				$thisRole			= $myArray[1];
				delete_temp_record($thisCallSign, 'ignore');
				$tempDataDeleted++;
			}
		}

		if (count($setTempIgnoreArray) > 0) {
			foreach($setTempIgnoreArray as $thisData) {
					$debugData .= "adding temp_data Ignore record<br >";
				$myArray			= explode("&",$thisData);
				$thisCallSign		= $myArray[0];
				$thisRole			= $myArray[1];		
				$tempResult			= $wpdb->insert('wpw1_cwa_temp_data', 
											array('callsign'=>$thisCallSign, 
													'token'=>'ignore', 
													'temp_data'=>$thisRole, 
													'date_written'=>$nowDate),
											array('%s','%s','%s','%s'));
				if ($tempResult === FALSE) {
					handleWPDBError($jobname,$doDebug);
				} else {
						$debugData .= "added $thisCallSign Ignore to temp_data Ignore<br />";
					$tempDataAdded++;
				}
			}
		}

		// display the error arrays
		if (count($allUsersArray) > 0) {
				$debugData .= "<br />have allUsersArray data to display<br /><br />";
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
					$thisCode		= $allUsersArray[$thisUser]['code'];
					$thisHasSignup	= $allUsersArray[$thisUser]['hasSignup'];
					$thisBadName	= $allUsersArray[$thisUser]['badUserName'];
					
					if ($thisRole == 'Advisor') {
						$thisLink		= "<a href='$advisorUpdateURL?request_type=callsign&request_info=$thisUser&inp_depth=one&doDebug=$doDebug&testMode=$testMode&strpass=2' target='_blank'>$thisUser</a>";
						$emailLink		= "<a href='mailto:$thisEmail' target='_blank'>$thisEmail</a>";
					} elseif ($thisRole == 'student') {
						$thisLink		= "<a href='$studentUpdateURL?request_type=callsign&request_info=$thisUser&inp_depth=one&strpass=2&doDebug=$doDebug&testMode=$testMode' target='_blank'>$thisUser</a>";
						$emailLink		= "<a href='mailto:$thisEmail' target='_blank'>$thisEmail</a>";
					} else {
						$thisLink		= $thisUser;
						$emailLink		= $thisEmail;
					}
					$errorString		= $thisCode . $theErrors;

					$editLink		= "<a href='$siteURL/wp-admin/users.php?s=$thisUser' target='_blank'>$thisUser</a>";				
					$ignoreLink		= "<a href='$siteURL/cwa-manage-temp-data/?inp_callsign=$thisUser&inp_role=$thisRole&inp_action=add&token=ignore&strpass=2' target='_blank'>Ignore Error</a>";
					$deleteIDLink	= "<a href='$siteURL/cwa-delete-user-info/?inp_value=$thisUser&strpass=2' target='_blank'>Delete User</a>";

					// per Bob Carter
					if ($thisHasSignup == 'Y') {
						$deleteIDLink	= '';
					}
					if ($thisBadName == 'Y') {
						$ignoreLink		= '';
					}
					
					$content		.= "<tr><td style='vertical-align:top;'>$thisRole</td>
											<td style='vertical-align:top;'>$thisLink</td>
											<td style='vertical-align:top;'>$thisLastName, $thisFirstName</td>
											<td style='vertical-align:top;'>$emailLink</td>
											<td style='vertical-align:top;'>$errorString</td>
											<td style='vertical-align:top;'>$editLink</td>
											<td style='vertical-align:top;'>$deleteIDLink</td>";
					$myCount++;
				}
			}
			$content			.= "</table>$myCount Errors Displayed<br />Clicking on the email address will start a new email<br /><br /><br />";
			$endDate			= date('Y-m-d H:i:s');
			$myInt				= count($noUserMasterRecord);
			if ($myInt > 0) {
				sort($noUserMasterRecord);
				$content		.= "<h4>User Login Records with No User Master Record</h4>
									<table style='width:800px;'>
									<tr><th>User Login</th>
										<th>User Name</th>
										<th>Role</th>
										<th>User Email</th>
										<th>Registration Date</th>
										<th>Registration Age</th></tr>";
				foreach ($noUserMasterRecord as $thisData) {
					$myArray		= explode("|",$thisData);
					$thisRegister	= $myArray[0];
					$thisLogin		= $myArray[1];
					$thisEmail		= $myArray[2];
					$thisName		= $myArray[3];
					$thisRole		= $myArray[4];
					$thisAge		= calculateDaysBetweenDates($thisRegister, $endDate);
					$emailLink		= "<a href='mailto:$thisEmail' target='_blank'>$thisEmail</a>";
					$editLink		= "<a href='$siteURL/wp-admin/users.php?s=$thisLogin' target='_blank'>$thisLogin</a>";				
					$content		.= "<tr><td>$editLink</td>
											<td>$thisName</td>
											<td>$thisRole</td>
											<td>$emailLink</td>
											<td>$thisRegister</td>
											<td style='text-align:center;'>$thisAge days</td></tr>";
				}
				$content			.= "</table>
										<p>$myInt errors displayed</p>
										<p>User Login records displayed above have obtained 
										a user name and a password, have verified by clicking 
										on the link in the verification email, but have not 
										signed up as either a student or an advisor. Clicking 
										on the User Login will open the User Information where 
										you can update or delete the user name record. Clicking 
										on the email address will open up a new email message 
										to the user.</p>";
			}
		}

/*
		// read the temp_data table and see if any of those records should be deleted
		// if the user_login has a signup record, delete the temp_data record
		$tempDataDeleted = 0;
		$tempSql		= "select * from wpw1_cwa_temp_data 
							where token = 'register' 
							order by callsign";
		$tempResult		= $wpdb->get_results($tempSql);
		if ($tempResult === FALSE) {
			handleWPDBError($jobname,$doDebug);
		} else {
			$numRows	= $wpdb->num_rows;
			if ($numRows > 0) {
				foreach($tempResult as $tempResultRow) {
					$temp_id			= $tempResultRow->record_id;
					$temp_callsign		= strtoupper($tempResultRow->callsign);
					$temp_token			= $tempResultRow->token;
					$temp_data			= $tempResultRow->temp_data;
					$temp_date_written	= $tempResultRow->date_written;
					
					$doContinue			= TRUE;
					// see if there is a signup record
					if ($temp_data == 'student') {
						$tempStr		= 'wpw1_cwa_student';
					} elseif ($temp_data == 'advisor') {
						$tempStr		= 'wpw1_cwa_advisor';
					} else {
						$doContinue		= FALSE;
					}
					if ($doContinue) {
						$thisSQL		= "select count(call_sign) 
											from $tempStr 
											where call_sign = '$temp_callsign' 
											and (semester = '$currentSemester' 
													or semester = '$nextSemester' 
													or semester = '$semesterTwo' 
													or semester = '$semesterThree' 
													or semester = '$semesterFour')";
						$thisCount			= $wpdb->get_var($thisSQL);
						if ($thisCount == NULL || $thisCount == 0) {		// no record
								$debugData .= "no signup record found for temp_data $temp_callsign<br /><br />";
						} else {					/// registration found. Delete the temp_data record
							delete_temp_record($temp_callsign,$temp_token);
							$tempDataDeleted++;
							$newSignup++;
						}
					}
				}
			}
		}
*/		
		

		$content	.= "<h4>Counts</h4>
						$userLoginCount: User Login Records<br />
						$newRegistrations: New User Registrations in Past 36 Hours<br /><br />
						$userUnverifiedCount: User Records that are Unverified ($userUnverifiedList)<br />";
//						$badUserNameCount: Usernames with invalid format ($badUsernameList)<br />";
		if ($advisorNoUsername) {
			$content	.= "$advisorNoUsername: Advisor Records with no Corresponding Username<br /><br />";
		}
		if ($studentNoUsername) {
			$content	.= "$studentNoUsername: Student Records with no Corresponding Username<br /><br />";
		}
		$content	.= "$registrationEmailCount: Signup Reminder Emails sent<br />
						$registerEmailCount: Register Reminder Emails sent<br />
						$verifyEmailCount: Verify Reminder Emails set<br />
						$tempDataAdded: TempData records added<br />
						$tempDataDeleted: TempData records deleted<br /><br />";
						
		$content	.= "<br />
<pre>Explanation of the Array Code<br />
Y Y Y Y Y Y 
| | | | | |
| | | | | - No user_login but signup record
| | | | - - Has a temp_data ignore record
| | | _ _ _ Has a temp_data register record
| | _ _ _ _ User_login has a valid format
| - - - - - User has a verified user_login format
- - - - - - User has a user_login and a signup record
</pre><br /><br />";
		

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
		$result			= write_joblog_func("$jobname|$nowDate|$nowTime|$userName|Time|$thisStr|1: $elapsedTime|$ipAddr");
		if ($result == 'FAIL') {
			$content	.= "<p>writing to joblog.txt failed</p>";
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
			$closeStr			= strtotime("+ 2 days");
			$close_date			= date('Y-m-d 00:00:00',$closeStr);
			$token			= mt_rand();
			$reminder_text	= "<b>New Registrations</b> To view the New Registrations report for $nowDate $nowTime, click <a href='cwa-display-saved-report/?strpass=3&inp_callsign=XXXXX&inp_id=$reportid_1&token=$token' target='_blank'>Display Report</a>";
			$inputParams		= array("effective_date|$nowDate $nowTime|s",
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
add_shortcode ('list_new_usernames','list_new_usernames_func');

