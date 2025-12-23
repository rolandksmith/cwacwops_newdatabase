function manage_advisor_class_assignments_func() {

/*
	
	Provides an advisor the ability  to get the advisor's class(es) and confirm
	the students
	
	
*/	

	global $wpdb,$userName,$validTestmode;

	$doDebug					= FALSE;
	$testMode					= FALSE;
	$initializationArray 		= data_initialization_func();
	$validUser 					= $initializationArray['validUser'];
	$userName					= $initializationArray['userName'];

	$versionNumber				= '1';
	$jobname					= "Manage Advisor Class Assignments V$versionNumber";
	if ($doDebug) {
		echo "Initialization Array:<br /><pre>";
		print_r($initializationArray);
		echo "</pre><br />";
	}
	$userRole					= $initializationArray['userRole'];
	$validTestmode				= $initializationArray['validTestmode'];
	$currentSemester			= $initializationArray['currentSemester'];
	$nextSemester				= $initializationArray['nextSemester'];
	$userName					= $initializationArray['userName'];
	$siteURL					= $initializationArray['siteurl'];	
	$validReplacementPeriod		= $initializationArray['validReplacementPeriod'];	

// must be a logged-in user
	if ($userName == '') {
		return "YOU'RE NOT AUTHORIZED!<br />Goodby";		
	}

	
	$proximateSemester			= $currentSemester;
	if ($proximateSemester == 'Not in Session') {
		$proximateSemester		= $nextSemester;
	}
	if ($userRole != 'administrator') {
		$doDebug		= FALSE;
		$testMode		= FALSE;
	}
	if ($doDebug) {
		ini_set('display_errors','1');
		error_reporting(E_ALL);	
		$wpdb->show_errors();
	} else {
		$wpdb->hide_errors();
	}

	ini_set('memory_limit','256M');
	ini_set('display_errors','1');
	error_reporting(E_ALL);	

/// get the time that the process started
	$startingMicroTime			= microtime(TRUE);

	$strPass					= "5";
	$studentArray				= array();
	$studentDataArray			= array();
	$totalStudents				= 0;
	$inp_callsign				= "";
	$confirmationMsg			= '';
	$inp_attend					= '';
	$inp_comment_attend			= '';
	$inp_comment				= '';
	$inp_replacement			= '';
	$token						= '';
	$submit						= '';
	$inp_mode					= '';
	$inp_verbose				= '';
	$actionDate					= date('Y-m-d H:i:s');
	$updateStudentInfoURL		= "$siteURL/cwa-display-and-update-student-signup-information/";
	$statusArray				= array('Y'=>'Verified',
										'S'=>'Assigned',
										'C'=>'Dropped');
	
// get the input information
	if (isset($_REQUEST)) {
		foreach($_REQUEST as $str_key => $str_value) {
			if ($doDebug) {
				if (is_array($str_value) === FALSE) {
					echo "Key: $str_key | Value: $str_value <br />\n";
				}
			}
			if ($str_key 		== "strpass") {
				$strPass		 = $str_value;
				$strPass		 = filter_var($strPass,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "inp_mode") {
				$inp_mode		 = $str_value;
				$inp_mode		 = filter_var($inp_mode,FILTER_UNSAFE_RAW);
				if ($inp_mode == 'TESTMODE') {
					$testMode	= TRUE;
				}
			}
			if ($str_key 		== "inp_verbose") {
				$inp_verbose	 = $str_value;
				$inp_verbose	 = filter_var($inp_verbose,FILTER_UNSAFE_RAW);
				if ($inp_verbose == 'Y') {
					$doDebug	= TRUE;
				}
			}
			if ($str_key 		== "submit") {
				$submit	 = $str_value;
				$submit	 = filter_var($submit,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "inp_callsign") {
				$inp_callsign	 = $str_value;
				$inp_callsign	 = filter_var($inp_callsign,FILTER_UNSAFE_RAW);
				$inp_callsign	= strtoupper($inp_callsign);
			}
			if ($str_key 		== "student_call_sign") {
				$student_call_sign	 = $str_value;
				$student_call_sign	 = filter_var($student_call_sign,FILTER_UNSAFE_RAW);
				$student_call_sign	= strtoupper($student_call_sign);
			}
			if ($str_key 		== "token") {
				$token			 = $str_value;
				$token			 = filter_var($token,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "unconfirm") {
				$unconfirm			 = $str_value;
				$unconfirm			 = filter_var($unconfirm,FILTER_UNSAFE_RAW);
				$unconfirm			= str_replace('Change ','',$unconfirm);
				$studentToProcess	= str_replace(' Confirmation','',$unconfirm);
				if ($doDebug) {
					echo "set studentToProcess to $studentToProcess<br />";
				}
			}
			if ($str_key 		== "confirm") {
				$confirm			 = $str_value;
				$confirm			 = filter_var($confirm,FILTER_UNSAFE_RAW);
				$studentToProcess	= str_replace('Confirm ','',$confirm);
				if ($doDebug) {
					echo "set studentToProcess to $studentToProcess<br />";
				}
			}
			if ($str_key 		== "inp_attend") {
				$inp_attend			 = $str_value;
				$inp_attend			 = filter_var($inp_attend,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "inp_comment_attend") {
				$inp_comment_attend			 = $str_value;
				$inp_comment_attend			= str_replace("'","&apos;",$inp_comment_attend);
				$inp_comment_attend			 = filter_var($inp_comment_attend,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "inp_comment1") {
				$inp_comment1			 = $str_value;
				$inp_comment1			= str_replace("'","&apos;",$inp_comment1);
				$inp_comment1			 = filter_var($inp_comment1,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "inp_comment2") {
				$inp_comment2			 = $str_value;
				$inp_comment2			= str_replace("'","&apos;",$inp_comment2);
				$inp_comment2			 = filter_var($inp_comment2,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "inp_comment3") {
				$inp_comment3			 = $str_value;
				$inp_comment3			= str_replace("'","&apos;",$inp_comment3);
				$inp_comment3			 = filter_var($inp_comment3,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "inp_replace") {
				$inp_replace			 = $str_value;
				$inp_replace			 = filter_var($inp_replace,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "studentid") {
				$studentid			 = $str_value;
				$studentid			 = filter_var($studentid,FILTER_UNSAFE_RAW);
			}
		}
	}
	

	$firstTime						= TRUE;
	$advisor_call_sign				= '';
	$user_email					= '';
	$user_phone					= '';
	$theURL							= "$siteURL/cwa-manage-advisor-class-assignments/";
	$errorArray						= array();

	if ($testMode) {
		$studentTableName			= 'wpw1_cwa_student2';
		$advisorTableName			= 'wpw1_cwa_advisor2';
		$advisorClassTableName		= 'wpw1_cwa_advisorclass2';
		$audioAssessmentTableName	= 'wpw1_cwa_audio_assessment2';
		$newAssessmentData			= 'wpw1_cwa_new_assessment_data';
		$userMasterTableName		= 'wpw1_cwa_user_master2';
		$thisMode					= 'TM';
		$operatingMode				= 'Testmode';
		$theStatement				= "<p>Function is running in TEST MODE using test files.</p>";
	} else {
		$studentTableName			= 'wpw1_cwa_student';
		$advisorTableName			= 'wpw1_cwa_advisor';
		$advisorClassTableName		= 'wpw1_cwa_advisorclass';
		$audioAssessmentTableName	= 'wpw1_cwa_audio_assessment';
		$newAssessmentData			= 'wpw1_cwa_new_assessment_data';
		$userMasterTableName		= 'wpw1_cwa_user_master';
		$thisMode					= 'PM';
		$operatingMode				= 'Production';
		$theStatement				= "";
	}

	$advisor_dal = new CWA_Advisor_DAL();
	$advisorclass_dal = new CWA_Advisorclass_DAL();
	$student_dal = new CWA_Student_DAL();
	$user_dal = new CWA_User_Master_DAL();

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

	if ($submit == 'Prepare CSV') {
		$strPass	= '10';
	}

	if ("1" == $strPass) {
		if ($doDebug) {
			echo "<br />arrived at $strPass with <br />
				inp_callsign: $inp_callsign<br />
				studentToProcess: $studentToProcess<br />
				token: $token<br />";
		}

		$studentData = get_student_and_user_master($studentToProcess, 'callsign', $proximateSemester, $operatingMode, $doDebug);
		if ($studentData === FALSE) {
			if ($doDebug) {
				echo "get_student_and_user_master for $studentToProcess returned FALSE<br />";
			}
		} else {
			if (! empty($studentData)) {
				if ($doDebug) {
					echo "studentData: <br /><pre>";
					print_r($studentData);
					echo "</pre><br />";
				}
				foreach($studentData as $key => $value) {
					$$key = $value;
				}

				/////// see if student is actually assigned to the advisor
				if ($student_assigned_advisor != $inp_callsign) {
					$content	.= "Incompatible Information Entered.<br />";
					if ($doDebug) {
						echo "$student_call_sign assigned advisor of $student_assigned_advisor is not $inp_callsign<br />";
					}
					error_log("ERROR manage_advisor_class_assignments_func(): Advisor $inp_callsign assigned student $student_call_sign does not have $userName as assigned advisor");
				} else {
					/////////// 	if student status is already C, R, or V, no sense in going further
					if ($student_status == 'C' || $student_status == 'R' || $student_status == 'V') {
						if ($doDebug) {
							echo "Student status is $student_status. No further action available<br />";
						}
						$strPass		= '5';		// redisplay the class
					} else {
				
						// display the form for the advisor to fill out and process the form in pass 3

						$content		.= "<h2>Advisor Confirmation of Student $student_call_sign</h2>
											<p><form method='post' action='$theURL' 
											name='verification_form' ENCTYPE='multipart/form-data'>
											<input type='hidden' name='strpass' value='3'>
											<input type='hidden' name='inp_callsign' value='$inp_callsign'>
											<input type='hidden' name='student_call_sign' value='$student_call_sign'>
											<input type='hidden' name='studentid' value='$student_id'>
											<input type='hidden' name='inp_vebose' value='$inp_verbose'>
											<input type='hidden' name='token' value='$token'>
											<table>
											<tr><td>Student $student_call_sign has been assigned to your class. After contacting 
													the student, please select any of the following that apply:</td></tr>
											<table style='border:4px solid green;'>
											<tr><td style='vertical-align:top;'><input type='radio' class='formInputButton' id='chk_attend' name='inp_attend' value='Yes' checked='checked'><b> 
													The student has responded and will attend my $student_semester Class</b></td></tr></table></td></tr>
											<table style='border:4px solid red;'>
											<tr><td style='vertical-align:top;'><b>STUDENT WILL NOT BE ATTENDING my $student_semester class for the following reason:</td></tr>
											<tr><td style='vertical-align:top;'><input type='radio' class='formInputButton' id='chk_attend' name='inp_attend' value='schedule'> 
													<b>Will Not Attend Due To Schedule Issues:</b><br />
													Student can not attend your class because of the scheduled time, but could take a class at a 
													different day or time.   
													Please find out when the student is available to take a class and enter that information below. <b>ONLY SELECT THIS ANSWER 
													if the student can indeed take a class at another time.</b> IF NOT, select 'Student Unable to Take a Class'.
													CW Academy will unassign 
													the student from your class and try to assign the student to a different class, based on your comments</td></tr>
											<tr><td><b>Advisor Comments (<em>Required</em>):</b><br />
													<textarea class=formInputText' name='inp_comment_attend' id='inp_comment_attend' rows='2' cols='50'></textarea></td></tr>
											<tr><td style='vertical-align:top;'><input type='radio' class='formInputButton' id='chk_attend' name='inp_attend' value='class'> 
													<b>Student Unable to Take the Class:</b><br />
													If the student is unable to take the class this semester due to circumstances like health issues or changes in schedules, 
													select this option. CW Academy will unassign the student from your class and the student will be unavailable 
													for reassignment. Comments would be helpful but not required.</td></tr>
											<tr><td><b>Advisor Comments (<em>optional</em>):</b><br />
													<textarea class=formInputText' name='inp_comment1' id='inp_comment1' rows='2' cols='50'></textarea></td></tr>
											<tr><td style='vertical-align:top;'><input type='radio' class='formInputButton' name='inp_attend' value='advisor'> 
													<b>Advisor Doesn't Want the Student:</b><br />
													If you don't want the student in your class for whatever reason, select this option. The student will be unassigned and 
													returned to the unassigned pool for possible assignment to another advisor's class. Comments would be helpful but 
													not required.</td></tr>
											<tr><td><b>Advisor Comments (<em>optional</em>):</b><br />
													<textarea class=formInputText' name='inp_comment2' id='inp_comment2' rows='2' cols='50'></textarea></td></tr>
											<tr><td style='vertical-align:top;'><input type='radio' class='formInputButton' id='chk_attend' name='inp_attend' value='other'> 
													<b>Some Other Reason</b> (such as no responding)<br />
													Please enter as much information as you have about why. 
													The student will be marked as unavailable for reassignment.</td></tr>
											<tr><td><b>Advisor Comments (<em>Required</em>):</b><br />
													<textarea class=formInputText' name='inp_comment3' id='inp_comment3' rows='2' cols='50'></textarea></td></tr></table>";
						if ($validReplacementPeriod == 'Y') {
							$content		.= "<table style='border:4px solid blue;'>
												<tr><td style='vertical-align:top;width:500px;'><b>If the student will <em>NOT</em> be attending your class, do you want the 
												system to attempt to replace the student?</b><br /><br /><em>Note that there may not be any replacement students 
												available.</em></td>
													<td style='vertical-align:top;text-align:left;width:150px;'>
													<input type='radio' class='formInputButton' name='inp_replace' value='replace'> Yes<br />
													<input type='radio' class='formInputButton' name='inp_replace' value='no' checked> No</td></tr>
												</table>";
						} else {
							$content		.= "<table style='border:4px solid blue;'>
												<tr><td style='vertical-align:top;width:650px;'><b>No Replacement Students are available</b></td></tr>
												</table>
												<input type='hidden' name='inp_replace' value='no'>";
					
						}
						$content			.= "</td></tr>
												<tr><td colspan='2'><input class='formInputButton' type='submit' value='Submit' /></td></tr>
												</table></form></p>";
					}
				}
			} else {
				if ($doDebug) {
					echo "No record found for $studentToProcess<br />";
				}
				$strPass		= '5';			// redisplay the class
			}
		}

	} elseif ("3" == $strPass) {
		if ($doDebug) {
			echo "<br />At pass $strPass<br />";
		}

//		$strPass 		= '5';

/*	if the advisor says the student is attending (inp_attend = Yes)
		ignore the other responses
		set student status to Y
		set up the student action log
		set up the advisorclass action log
		Update the student record
		Update the advisorclass record
	if the advisor says the student is not attending (inp_attend != Yes)
		if the advisor says to replace the student
		 	if the reason is schedule (student to be put back in unassigned pool)
		 		set student remove_status to V
		 		setup the student action log
		 		setup the advisorclass action log
		 	if the reason is class (student not to be put back in unassigned pool)
		 		set the student remove_status to R
		 		Setup the student action log
		 		Setup the advisor action log
		 	if the reason is advisor (advisor does not want the student)
		 		Set the student remove status to R
		 		Setup the student action log
		 		Set up the advisor action log
		 	if the reason is other
		 		set the student revmoe_status to R
		 		setup the student action log
		 		setup the advisorclass action log
		If the advisor says not to replace the student
			if the reason is schedule
				set the student remove_status to blank
				set intervention required to H
				set hold reason code to X
				set excluded advisor to the advisor requesting the student
				update the student
				setup the advisorclass action log
				send an email to Bob
			if the reason is class
				set the student remove_status to C
				setup the student action log
				setup the advisorclass action log
			If the reason is advisor
				set the student remove_status to blank (unassigned)
				Set excljuded advisor 
				Setup the advisorClass action log
				Update the student
			If reason is other
				set the student remove_status to C
				setup the student action log
				setup the advisorclass action log
	If the advisor doesn't want the student, exclude the advisor from the student and 
		unassign the student
*/
		$goOn				= TRUE;

		// first, see if the advisor has written required comments
		if ($inp_attend == 'schedule' && $inp_comment_attend == '') {
			$goOn			= FALSE;
			$content		.= "<h3>$jobname</h3>
								<h4>Advisor Schedule Comments Missing</h4>
								<p>You have marked the student as not attending because 
								of schedule issues. You were requested to indicate when 
								the student might be able to take a class. 
								Please find out when the student is available to take a class and enter that information below. 
								CW Academy will  try to assign the student to a different class based on your comments:</td></tr>
								<form method='post' action='$theURL' 
								name='comments_form' ENCTYPE='multipart/form-data'>
								<input type='hidden' name='strpass' value='3'>
								<input type='hidden' name='inp_attend' value='$inp_attend'>
								<input type='hidden' name='inp_callsign' value='$inp_callsign'>
								<input type='hidden' name='student_call_sign' value='$student_call_sign'>
								<input type='hidden' name='studentid' value='$studentid'>
								<input type='hidden' name='inp_replace' value='$inp_replace'>
								<input type='hidden' name='inp_vebose' value='$inp_verbose'>
								<input type='hidden' name='token' value='$token'>
								<table>
								<tr><td><b>Advisor Comments (<em>Required</em>):</b><br />
										<textarea class=formInputText' name='inp_comment_attend' id='inp_comment_attend' rows='2' cols='50'></textarea></td></tr>
								<tr><td colspan='2'><input class='formInputButton' type='submit' value='Submit' /></td></tr>
								</table></form>";
		} else {
			if ($goOn) {
				//////////	get the advisor record
				$advisorData = get_advisor_and_user_master($inp_callsign, 'callsign', $proximateSemester, $operatingMode, $doDebug);
				if ($advisorData === FALSE) {
					if ($doDebug) {
						echo "get_advisor_and_user_master for $advisorToProcess returned FALSE<br />";
					}
				} else {
					if (! empty($advisorData)) {
						foreach($advisorData as $key => $value) {
							$$key = $value;
						}
		
						if ($userName == '') {
							$userName 		= $advisor_call_sign;
						}
						if ($doDebug) {
							echo "Got $advisor_call_sign's records from $advisorTableName table<br />";
						}
					
						$doProceed		= TRUE;
						
						///// now get the student information
						$studentData = get_student_and_user_master('', 'id', $studentid, $operatingMode, $doDebug);
						if ($studentData === FALSE) {
							if ($doDebug) {
								echo "get_student_and_user_master for $studentToProcess returned FALSE<br />";
							}
						} else {
							if (! empty($studentData)) {
								foreach($studentData as $key => $value) {
									if (str_contains($key,'user_')) {
										$key = str_replace('user_','user_fixed_',$key);
									}
									$$key = $value;
								}
								if ($doProceed) {
									$student_remove_status			= '';
									$myStr							= '';
									$updateFiles					= FALSE;
									$removeStudent					= FALSE;
									if ($inp_attend == 'Yes') {
										if ($doDebug) {
											echo "<br />attend is Yes. Updating student and advisor<br />";
										}
										$student_action_log			= "$student_action_log / $actionDate CONFIRM $advisor_call_sign advisor confirmed student participation ";
										$updateParams				= array('student_status' => 'Y', 
																			'student_action_log' => $student_action_log);
										$advisorReply				= "<p>You have confirmed that $student_call_sign will attend your  class.</p>";
										if ($doDebug) {
											echo "inp_attend is $inp_attend. Set status to Y and updated action log<br />";
										}
										$updateResult = $student_dal->update( $student_id, $updateParams, $operatingMode );
										if ($updateResult === FALSE || $updateResult === NULL) {
											if ($doDebug) {
												echo "updating $student_call_sign (ID $student_id) returned FALSE|NULL<br />";
											}
										} else {
											if ($doDebug) {
												echo "student record updated. Now updating advisor record<br />";
											}
											$advisor_action_log		= "$advisor_action_log / $actionDate CONFIRM advisor confirmed $student_call_sign attendance ";
											$updateParams			= array('advisor_action_log' => $advisor_action_log);
											$updateResult = $advisor_dal->update( $advisor_id, $updateParams, $operatingMode );
											if ($updateResult === FALSE || $updateResult === NULL) {
												if ($doDebug) {
													echo "updating $advisor_call_sign (ID $advisor_id) returned FALSE|NULL<br />";
												}
											} else {
												if ($doDebug) {
													echo "advisor record successfully updated<br />";
												}
											}
											$confirmationMsg	= "Student $student_call_sign has been confirmed as attending<br />";
											if ($doDebug) {
												echo "strPass: $strPass<br />";
											}
										}
									} else {			// student not attending
										if ($doDebug) {
											echo "Student not attending<br />";
										}
										$updateFiles					= TRUE;
										$studentUpdateParams			= array();
										$advisorUpdateParams			= array();
										if ($inp_replace == 'replace') {			// asking for a replacement
											if ($inp_attend == 'schedule') {
												if ($doDebug) {
													echo "Doing Schedule; Replacement Yes<br />";
												}
												$student_action_log		= "$student_action_log / $actionDate CONFIRM $advisor_call_sign student will not attend due to schedule. Advisor comments: $inp_comment_attend. Replacement requested ";
												$advisor_action_log		= "$advisor_action_log / $actionDate CONFIRM $advisor_call_sign $student_call_sign will not attend due to schedule. Advisor comments: $inp_comment_attend. Replacement requested ";
												$studentUpdateParams['student_action_log'] = $student_action_log;
												$advisorUpdateParams['advisor_action_log'] = $advisor_action_log;
												$studentUpdateParams['student_status'] = 'V';
												$confirmationMsg		= "Student $student_call_sign confirmed as not attending and a replacement has been requested.<br />";
												$removeStudent			= FALSE;
											} elseif ($inp_attend == 'class') {
												if ($doDebug) {
													echo "Doing Class; Replacement Yes<br />";
												}
												if ($inp_comment1 != '') {
													$myStr				= "Advisor comments: $inp_comment1 ";
												}
												$student_action_log		= "$student_action_log / $actionDate CONFIRM $advisor_call_sign student does not want the class. $myStr Replacement requested ";
												$advisor_action_log		= "$advisor_action_log / $actionDate CONFIRM $student_call_sign does not want the class. $myStr Replacement requested ";
												$studentUpdateParams['student_action_log'] = $student_action_log;
												$advisorUpdateParams['advisor_action_log'] = $advisor_action_log;
												$studentUpdateParams['student_status'] = 'R';
												$confirmationMsg		= "Student $student_call_sign confirmed as not attending and a replacement has been requested.<br />";
												$removeStudent			= FALSE;
											} elseif ($inp_attend == 'advisor') {
												if ($doDebug) {
													echo "Doing advisor; Replacement Yes<br />";
												}
												$newStudentExcludedAdvisor	= updateExcludedAdvisor($student_excluded_advisor,$advisor_call_sign,'add',$doDebug);
												if ($inp_comment2 != '') {
													$myStr				= "Advisor comments: $inp_comment2 ";
												}
												$student_action_log		= "$student_action_log / $actionDate CONFIRM $advisor_call_sign advisor does not want the student. $myStr Replacement requested ";
												$advisor_action_log		= "$advisor_action_log / $actionDate CONFIRM advisor does not want $student_call_sign. $myStr Replacement requested ";
												$studentUpdateParams['student_action_log'] = $student_action_log;
//												$studentUpdateParams['student_excluded_advisor'] = $newStudentExcludedAdvisor;
//												$studentUpdateParams['student_hold_reason_code'] = 'X';
												$advisorUpdateParams['advisor_action_log'] = $advisor_action_log;
												$studentUpdateParams['student_status'] = 'R';
												$confirmationMsg		= "Student $student_call_sign confirmed as not attending and no replacement was requested.<br />";
												$removeStudent				= FALSE;
											} else {
												if ($doDebug) {
													echo "Doing Other; Replacement Yes<br />";
												}
												if ($inp_comment3 != '') {
													$myStr				= "Advisor comments: $inp_comment3 ";
												}
												$student_action_log		= "$student_action_log / $actionDate CONFIRM $advisor_call_sign student will not attend. $myStr Replacement requested ";
												$advisor_action_log		= "$advisor_action_log / $actionDate CONFIRM $student_call_sign will not attend. Advisor comments: $inp_comment. Replacement requested ";
												$studentUpdateParams['student_action_log'] = $student_action_log;
												$advisorUpdateParams['advisor_action_log'] = $advisor_action_log;
												$studentUpdateParams['student_status'] = 'R';
												$confirmationMsg		= "Student $student_call_sign confirmed as not attending and a replacement has been requested.<br />";
												$removeStudent			= FALSE;
											}
										} else {
											if ($inp_attend == 'schedule') {
												if ($doDebug) {
													echo "Doing Schedule; Replacement No<br />";
												}
												$student_action_log		= "$student_action_log / $actionDate CONFIRM $advisor_call_sign 
student will not attend due to schedule. Advisor comments: $inp_comment_attend. Unassigned from $student_assigned_advisor class $student_assigned_advisor_class. 
No replacement requested.  ";
//												$newStudentExcludedAdvisor	= updateExcludedAdvisor($student_excluded_advisor,$advisor_call_sign,'add',$doDebug);
												$studentUpdateParams['student_action_log'] = $student_action_log;
												$studentUpdateParams['student_hold_reason_code'] = 'X';
												$studentUpdateParams['student_class_priority'] = 2;
//												$studentUpdateParams['student_excluded_advisor'] = $newStudentExcludedAdvisor;
												$studentUpdateParams['student_intervention_required'] = 'H';
												$student_remove_status		= '';
												$removeStudent				= TRUE;
												$advisor_action_log		= "$advisor_action_log / $actionDate CONFIRM 
$student_call_sign will not attend due to schedule. Advisor comments: $inp_comment_attend. Unassigned from $student_assigned_advisor class $student_assigned_advisor_class. 
No replacement requested. ";
												$advisorUpdateParams	= array('advisor_action_log' => $advisor_action_log);
			
											} elseif ($inp_attend == 'class') {
												if ($doDebug) {
													echo "Doing Class; Replacement No<br />";
												}
												$myStr					= " No advisor comments. ";
												if ($inp_comment1 != '') {
													$myStr				= "Advisor comments: $inp_comment1 ";
												}
												$student_action_log		= "$student_action_log / $actionDate CONFIRM $advisor_call_sign student does not want the class. $myStr No replacement requested ";
												$advisor_action_log		= "$advisor_action_log / $actionDate CONFIRM $student_call_sign does not want the class. $myStr No replacement requested ";
												$studentUpdateParams['student_action_log'] = $student_action_log;
												$advisorUpdateParams['advisor_action_log'] = $advisor_action_log;
												$student_remove_status	= 'C';
												$removeStudent			= TRUE;
												$confirmationMsg		= "Student $student_call_sign confirmed as not attending and no replacement was requested.<br />";
											} elseif ($inp_attend == 'advisor') {
												if ($doDebug) {
													echo "Doing advisor; Replacement No<br />";
												}
												$newStudentExcludedAdvisor		= updateExcludedAdvisor($student_excluded_advisor,$advisor_call_sign,'add',$doDebug);
												$myStr					= " No advisor comments. ";
												if ($inp_comment2 != '') {
													$myStr				= "Advisor comments: $inp_comment2 ";
												}
												$student_action_log		= "$student_action_log / $actionDate CONFIRM $advisor_call_sign advisor does not want the student. $myStr No replacement requested. Student set to unassigned ";
												$advisor_action_log		= "$advisor_action_log / $actionDate CONFIRM advisor does not want $student_call_sign. $myStr No replacement requested ";
												$studentUpdateParams['student_action_log'] = $student_action_log;
//												$studentUpdateParams['student_excluded_advisor'] = $newStudentExcludedAdvisor;
//												$studentUpdateParams['student_hold_reason_code'] = 'X';
												$advisorUpdateParams['advisor_action_log'] = $advisor_action_log;
												$student_remove_status	= '';
												$removeStudent			= TRUE;
												$confirmationMsg		= "Student $student_call_sign confirmed as not attending and no replacement was requested.<br />";
											} else {
												if ($doDebug) {
													echo "Doing Other; Replacement No<br />";
												}
												$myStr					= " No advisor coments. ";
												if ($inp_comment3 != '') {
													$myStr				= "Advisor comments: $inp_comment3 ";
												}
												$student_action_log		= "$student_action_log / $actionDate CONFIRM $advisor_call_sign student will not attend. $myStr No replacement requested ";
												$advisor_action_log		= "$advisor_action_log / $actionDate CONFIRM $student_call_sign will not attend. $myStr No replacement requested ";
												$newStudentExcludedAdvisor		= updateExcludedAdvisor($student_excluded_advisor,$advisor_call_sign,'add',$doDebug);
												$studentUpdateParams['student_hold_reason_code'] = 'X';
												$studentUpdateParams['student_class_priority'] = 2;
												$studentUpdateParams['student_excluded_advisor'] = $newStudentExcludedAdvisor;
												$studentUpdateParams['student_action_log'] = $student_action_log;
												$advisorUpdateParams['advisor_action_log'] = $advisor_action_log;
												$student_remove_status	= 'C';
												$removeStudent			= TRUE;
												$confirmationMsg		= "Student $student_call_sign confirmed as not attending and no replacement was requested.<br />";
											}
										}
									}
									if ($updateFiles) {
										//// now update the student 
										$updateResult = $student_dal->update($student_id, $studentUpdateParams, $operatingMode);
										if ($updateResult === FALSE || $updateResult === NULL) {
											if ($doDebug) {
												echo "updating student $student_call_sign (ID $student_id) returned FALSE|NULL<br />";
											}
										} else {
											if ($doDebug) {
												echo "student record successfully updated<br />";
											}
										}
										// update the advisor
										$updateResult = $advisor_dal->update($advisor_id, $advisorUpdateParams, $operatingMode);
										if ($updateResult === FALSE || $updateResult === NULL) {
											if ($doDebug) {
												echo "updating advisor $advisor_call_sign (ID $advisor_id) returned FALSE|NULL<br />";
											}
										} else {
											if ($doDebug) {
												echo "updating $advisor_call_sign in $advisorTableName succeeded<br />";
											}
										}
									}
									if ($removeStudent) {
									// remove the student
									
										if (!isset($student_assigned_advisor)) {
											if ($doDebug) {
												echo "student_assigned_advisor is MISSING!<br />";
											}
											$nowInfo		= date('Y-m-d H:i:s');
											sendErrorEmail("$jobname $userName $nowInfo attempting to remove student $student_call_sign. student_assigned_advisor is missing");
											$student_assigned_advisor	= $userName;
										}
									
										$inp_data			= array('inp_student'=>$student_call_sign,
																	'inp_semester'=>$student_semester,
																	'inp_assigned_advisor'=>$student_assigned_advisor,
																	'inp_assigned_advisor_class'=>$student_assigned_advisor_class,
																	'inp_remove_status'=>$student_remove_status,
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
										} else {
											if ($doDebug) {
												echo "student was successfully removed from the advisor's class<br />";
											}
										}
									}
								}
							}
						}
					}
				}
				$strPass		= '5';
			}
		}
	}
	if ("5" == $strPass) {
	
		if ($doDebug) {
			echo "<br />At pass $strPass with<br />
				  inp_callsign: $inp_callsign<br />
				  token: $token<br />";
		}
		
		if ($inp_callsign == '') {
			$inp_callsign	= strtoupper($userName);
			$token			= '';
		}
		$unconfirmedCount	= 0;
		$jjCount			= 0;
		$content			.= "$confirmationMsg 
								<h4>$inp_callsign Classes and Students for $proximateSemester Semester</h4>
								<div><p><table style='width:900px;'>
								<tr><td>If you would like a .csv dump of the students assigned 
										to your $proximateSemester semester classes, please click</td>
									<td><form method='post' action='$theURL' 
										name='dump_form' ENCTYPE='multipart/form-data'>
										<input type='hidden' name='strpass' value='10'>
										<input type='hidden' name='inp_callsign' value='$inp_callsign'>
										<input class='formInputButton' type='submit' name='submit' value='Prepare CSV' /></td></tr></table></p></div>";
		
		$criteria = [
			'relation' => 'AND',
			'clauses' => [
				['field' => 'advisorclass_call_sign', 'value' => $inp_callsign, 'compare' => '=' ],
				['field' => 'advisorclass_semester', 'value' => $proximateSemester, 'compare' => '=' ],
			]
		];
		
		$advisorclassData = $advisorclass_dal->get_advisorclasses_by_order( $criteria, 'advisorclass_sequence', 'ASC', $operatingMode );
		if ($advisorclassData === FALSE || $advisorclassData === NULL) {
			if ($doDebug) {
				echo "getting advisorclassData returned FALSE|NULL<br />";
			}
		} else {
			if (! empty($advisorclassData)) {
				foreach($advisorclassData as $key => $value) {
					foreach($value as $thisField => $thisValue) {
						$$thisField = $thisValue;
					}

					if ($doDebug) {
						echo "have advisorclass sequence $advisorclass_sequence record<br />";
					}
					$content	.= "<b>Class $advisorclass_sequence:</b>
									<table style='width:900px;'>
									<tr><td style='vertical-align:top;width:300px;'><b>Sequence</b><br />
											$advisorclass_sequence</td>
										<td style='vertical-align:top;width:300px;'><b>Level</b><br />
											$advisorclass_level</td>
										<td style='vertical-align:top;'><b>Class Size</b><br />
											$advisorclass_class_size</td></tr>
									<tr><td style='vertical-align:top;'><b>Class Teaching Days</b><br />
											$advisorclass_class_schedule_days</td>
										<td style='vertical-align:top;' colspan='2'><b>Local Start Time</b><br />
											$advisorclass_class_schedule_times</td></tr></table>";

					// if there are students, then show the student name list
					if ($doDebug) {
						echo "there are $advisorclass_number_students students in the class<br />";
					}
					$displayClass			= FALSE;										
					if ($advisorclass_number_students > 0) {	
						// put out the student header
						$content				.= "<table style='border-collapse:collapse;'>";
						
						$daysToGo				= days_to_semester($proximateSemester);
						if($doDebug) {
							echo "preparing to display student info. Days to $advisorclass_semester semester: $daysToGo<br />";
						}
						if ($daysToGo > 0 && $daysToGo < 21) {
							$content				.= "<p>Students have been assigned to classes for the $advisorclass_semester semester. 
														The semester has not yet started. You have been assigned the 
														following students:</p>\n";
							$displayClass		= TRUE;
						} elseif ($daysToGo < 0 && $daysToGo > 20) {
							$content				.= "<p>Students have not yet been assigned to advisor classes.</p>";
						} elseif ($daysToGo <0 && $daysToGo > -60) {								
							$content				.= "<p>Students have been assigned to classes for the $advisorclass_semester semester. 
														The semester is underway. You have been assigned the 
														following students:</p>\n";
							$displayClass			= TRUE;
						} else {
							$content				.= "<p>Students have been assigned to classes for the $proximateSemester semester. 
														The semester is completed. You were assigned the 
														following students:</p>\n";
							$displayClass			= TRUE;
						}
						if ($displayClass) {
							if ($doDebug) {
								echo "displaying class information<br />";
							}
							$studentCount			= 0;
							for ($snum=1;$snum<31;$snum++) {
								if ($snum < 10) {
									$strSnum 		= str_pad($snum,2,'0',STR_PAD_LEFT);
								} else {
									$strSnum		= strval($snum);
								}
								$theInfo			= ${'advisorclass_student' . $strSnum};
								if ($theInfo != '') {
									if ($doDebug) {
										echo "<br />processing student$strSnum $theInfo<br />";
									}
									$studentData = get_student_and_user_master($theInfo, 'callsign', $proximateSemester, $operatingMode, $doDebug);
									if ($studentData === FALSE) {
										if ($doDebug) {
											echo "get_student_and_user_master for $theInfo returned FALSE<br />";
										}
									} else {
										if (! empty($studentData)) {
											foreach($studentData as $key => $value) {
												$$key = $value;
											}
											if ($doDebug) {
												echo "&nbsp;&nbsp;&nbsp;&nbsp;Level: $student_level<br />
													  &nbsp;&nbsp;&nbsp;&nbsp;Class: $student_assigned_advisor_class<br />
													  &nbsp;&nbsp;&nbsp;&nbsp;Status: $student_status<br />
													  &nbsp;&nbsp;&nbsp;&nbsp;Promotable: $student_promotable<br />";
											}
											
											if ($student_status == 'S' || $student_status == 'Y') {
												$studentCount++;
												$content			.= "<tr><td style='vertical-align:top;width:100px;'><b>Call Sign</b></td>\n
																			<td style='vertical-align:top;width:150px;'><b>Name</b></td>\n
																			<td style='vertical-align:top;width:200px;'><b>Email</b></td>\n
																			<td style='vertical-align:top;width:200px;'><b>Phone</b></td>\n
																			<td style='vertical-align:top;width:100px;'><b>Country</b></td>\n
																			<td style='vertical-align:top;width:100px;'><b>Action</b></td></tr>\n";
												/// check to see if there are assessment records for this student
												if ($doDebug) {
													echo "looking for audio assessment records<br />";
												}
												$hasAssessment			= FALSE;
												$assessment_count	= $wpdb->get_var("select count(record_id) 
																		   from $audioAssessmentTableName 
																			where call_sign='$student_call_sign'");
												if ($assessment_count > 0) {
													$hasAssessment	= TRUE;
													if ($doDebug) {
														echo "have assessment records<br />";
													}
												}
												$newAssessmentCount		= $wpdb->get_var("select count(record_id) 
																		   from $newAssessmentData 
																			where callsign='$student_call_sign'");
																			
												if ($newAssessmentCount > 0) {
													$hasAssessment	= TRUE;
													if ($doDebug) {
														echo "have assessment records<br />";
													}
												}
																			
												$extras							= "Additional contact options: ";
												$haveExtras						= FALSE;
												if ($user_whatsapp != '' ) {
													$extras						.= "WhatsApp: $user_whatsapp ";
													$haveExtras					= TRUE;
												}
												if ($user_signal != '' ) {
													$extras						.= "Signal: $user_signal ";
													$haveExtras					= TRUE;
												}
												if ($user_telegram != '' ) {
													$extras						.= "Telegram: $user_telegram ";
													$haveExtras					= TRUE;
												}
												if ($user_messenger != '' ) {
													$extras						.= "Messenger: $user_messenger ";
													$haveExtras					= TRUE;
												}
			

												$myStr							= "";
												if ($student_status == 'S') {
													$unconfirmedCount++;
													$myStr					= "<table style='border:4px solid green;'>
																				<tr><td><form method='post' action='$theURL' 
																						name='confirm_form' ENCTYPE='multipart/form-data'>
																						<input type='hidden' name='strpass' value='1'>
																						<input type='hidden' name='inp_callsign' value='$advisorclass_call_sign'>
																						<input type='hidden' name='token' value='$token'>
																						<input type='hidden' name='inp_mode' value='$inp_mode'>
																						<input type='hidden' name='inp_verbose' value='$inp_verbose'>
																						<input class='formInputButton' type='submit' name='confirm' value='Confirm $student_call_sign' />
																						</td></tr></table>\n";
												} elseif ($student_status == 'Y') {
													$myStr					= "Confirmed<br />
																				<form method='post' action='$theURL' 
																				name='unconfirm_form' ENCTYPE='multipart/form-data'>
																				<input type='hidden' name='strpass' value='1'>
																				<input type='hidden' name='token' value='$token'>
																				<input type='hidden' name='inp_mode' value='$inp_mode'>
																				<input type='hidden' name='inp_verbose' value='$inp_verbose'>
																				<input class='formInputButton' type='submit' name='unconfirm' value='Change $student_call_sign Confirmation' />";
												}
												if ($doDebug) {
													echo "displaying student $student_call_sign information<br />";
												}
												$content						.= "<tr><td style='vertical-align:top;'>$student_call_sign</td>\n
																						<td style='vertical-align:top;'>$user_last_name, $user_first_name</td>\n
																						<td style='vertical-align:top;'>$user_email</td>\n
																						<td style='vertical-align:top;'>+$user_ph_code $user_phone</td>\n
																						<td style='vertical-align:top;'>$user_country</td>\n
																						<td style='vertical-align:top;'>$myStr</td></tr>\n";
												if ($haveExtras) {
													$content					.= "<tr><td colspan='7'>$extras</td></tr>";
												}
												$thisParent			= '';
												$thisParentEmail	= '';
												if ($student_youth == 'Yes') {
													if ($student_age < 18) { 
														if ($student_parent == '') {
															$thisParent	= 'Not Given';
														} else {
															$thisParent	= $student_parent;
														}
														if ($student_parent_email == '') {
															$thisParentEmail = 'Not Given';
														} else {
															$thisParentEmail = $student_parent_email;
														}
														$content	.= "<tr><td colspan='7'>The student has registered as a youth under the age of 18. The student's 
																		parent or guardian is $thisParent at email address $thisParentEmail.</td></tr>\n";
													}
												}

												if ($hasAssessment) {
													$enstr		= base64_encode("advisor_call_sign=$student_assigned_advisor&inp_callsign=$student_call_sign");
													$content	.= "<tr><td colspan='7' style='border-bottom: 1px solid #000;'>Click <a href='$siteURL/cwa-view-a-student-assessment/?strpass=2&enstr=$enstr' target='_blank'>HERE</a> to review $student_call_sign's self assessment</td></tr>\n";
												} else {
													$content	.= "<tr><td colspan='7' style='border-bottom: 1px solid #000;'>&nbsp;</td></tr>";
												}
											}	/// end of the student foreach
										} else {		/// no student record found ... send error message
											sendErrorEmail("Prepare Advisor Class Display: no student record found for student$strSnum in advisor $advisorclass_call_sign class $advisorclass_sequence");
										}
									}	
								}
							}
							$content				.= "</table>$studentCount Students<br /><br />";
							if ($doDebug) {
								echo "have processed all students for this class<br /><br />";
							}
						}
					} else {
						$content					.= "<p>No students are currently assigned to this class.</p>";
						if ($doDebug) {
							echo "No students assigned to this class<br /><br />";
						}
					}
				}			/// have processed all advisor classes. See if reminder to be resolved
				if ($unconfirmedCount == 0 && $token != '') {
					if ($doDebug) {
						echo "DELETE THE TOKEN HERE<br />";
					}
					$resolveResult				= resolve_reminder($inp_callsign,'studentConfirmation',$testMode,$doDebug);
					if ($resolveResult === FALSE) {
						if ($doDebug) {
							echo "resolve_reminder for $inp_callsign and $token failed<br />";
						}
					}
					
				} else {
					echo "Still have $unconfirmedCount unconfirmed students<br />";
				}
			}
		}




	} elseif ("10" == $strPass) {

		if ($doDebug) {
			echo "<br />arrived at pass $strPass<br />
			advisor: $inp_callsign<br />
			semester: $proximateSemester<br />
			submit: $submit<br />";
			
		}
		if ($submit == 'Prepare CSV') {		
			// get all students assigned to this advisor for the specified semester
			$criteria = [
				'relation' => 'AND',
				'clauses' => [
					// field1 = $value1
					['field' => 'student_assigned_advisor', 'value' => $inp_callsign, 'compare' => '='],
					['field' => 'student_semester', 'value' => $proximateSemester, 'compare' => '='],
					['field' => 'student_response', 'value' => 'Y', 'compare' => '='],
					
					// (field2 = $value2 OR field2 = $value3)
					[
						'relation' => 'OR',
						'clauses' => [
							['field' => 'student_status', 'value' => 'S', 'compare' => '='],
							['field' => 'student_status', 'value' => 'Y', 'compare' => '=']
						]
					]
				]
			];
			$requestInfo = array('criteria' => $criteria,
								 'orderby' => 'student_assigned_advisor_class, student_call_sign',
								 'order' => 'ASC');
			$studentData = get_student_and_user_master('', 'complex', $requestInfo, $operatingMode, $doDebug);
			if ($studentData === FALSE) {
				if ($doDebug) {
					echo "get_student_and_user_master  returned FALSE<br />";
				}
			} else {
				if (! empty($studentData)) {
					$content		.= "<h3>$jobname</h3>
										<h4>CSV Dump of Students in $inp_callsign $proximateSemester Semester Class(es)</h4>
										<p>The table below shows what the csv file contains for your students by class number in student call sign 
										order. The fields are separated by tabs. The first row are the field names.</p>
										<p>The link to download the csv file along with instructions are below the table.</p>
										<pre>class\tcall_sign\tfirst_name\tlast_name\temail\tphone\tstate\tcountry\twhatsapp\tsignal\ttelegram\tmessenger\n";
										
					// prepare the csv file and write the headers
					$thisStr		= "$inp_callsign" . "_class_download.csv";
					if (preg_match('/localhost/',$siteURL)) {
						$thisFileName	= "wp-content/uploads/$thisStr";
					} else {
						$thisFileName	= "/home/cwacwops/public_html/wp-content/uploads/$thisStr";
					}
					$thisFP			= fopen($thisFileName,'w');
					$thisList		= ['class','call_sign','first_name','last_name','email','phone','state','country','whatsapp','signal','telegram','messenger'];
					fputcsv($thisFP,$thisList,"\t");										
			
					foreach($studentData as $key => $value) {
						foreach($value as $thisField => $thisValue) {
							$$thisField = $thisValue;
						}
	
						$content			.= "$student_assigned_advisor_class\t$student_call_sign\t$user_first_name\t$user_last_name\t$user_email\t+$user_ph_code $user_phone\t$user_state\t$user_country\t$user_whatsapp\t$user_signal\t$user_telegram\t$user_messenger\n";	
						$thisPhone			= "+$user_ph_code $user_phone";
						$thisList			= [$student_assigned_advisor_class,$student_call_sign,$user_first_name,$user_last_name,$user_email,$thisPhone,$user_state,$user_country,$user_whatsapp,$user_signal,$user_telegram,$user_messenger];
						fputcsv($thisFP,$thisList,"\t");
					}
					fclose($thisFP);
					if ($doDebug) {
						echo "table is written and the file is ready to download<br />";
					}
					$content				.= "</pre><br />
												<p>Click <a href='$siteURL/wp-content/uploads/$thisStr'>$thisStr</a> to download the csv file</p>
												<p>To use this data on a Windows computer do the following:<br />
												Open Excel (or your preferred spreadsheet program) and import the newly 
												downloaded document. You may need to specify that Whatsapp, Signal, 
												Telegram, and Messenger are 'text' fields.</p>";
				} else {
					$content				.= "no students found<br />";
				}
			}	
		}	
		
	} elseif ("20" == $strPass) {
		if ($doDebug) {
			echo "<br />Arrived at $strPass pass<br />";
		}
		// get the advisor callsign and go to pass 5 to do the work
		$content		.= "<h3>$jobname</h3>
							<p>Enter the advisor's callsign and click 'Submit' to 
							see what the advisor sees.</p>
							<form method='post' action='$theURL' 
							name='fake_form' ENCTYPE='multipart/form-data'>
							<input type='hidden' name='strpass' value='5'>
							<input type='hidden' name='token' value=''>
							<table style='width:auto;'>
							<tr><td>Advisor Callsign</td>
								<td><input type='text' class='formInputText' size='15' maxlength = '15' name='inp_callsign'></td></tr>
							$testMode
							<tr><td colspan='2'><input class='formInputButton' type='submit' name='submit' value='Submit' /></td></tr></table></form>";
		
	}
	$thisTime 					= date('Y-m-d H:i:s');
	$content 		.= "<br /><br /><p>V$versionNumber. Prepared at $thisTime</p>";
	$endingMicroTime = microtime(TRUE);
	$elapsedTime	= $endingMicroTime - $startingMicroTime;
	$elapsedTime	= number_format($elapsedTime, 4, '.', ',');
	$content		.= "<p>Report pass $strPass took $elapsedTime seconds to run</p>";
	$nowDate		= date('Y-m-d');
	$nowTime		= date('H:i:s');
	$thisStr			= 'Production';
	if ($testMode) {
		$thisStr		= 'Testmode';
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
add_shortcode ('manage_advisor_class_assignments', 'manage_advisor_class_assignments_func');
