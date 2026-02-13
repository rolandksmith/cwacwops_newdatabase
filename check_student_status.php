function check_student_status_func() {

	global $wpdb,$doDebug, $testMode, $advisorClassTableName, $userMasterTableName;

	$doDebug						= FALSE;
	$testMode						= FALSE;
	$initializationArray 			= data_initialization_func();
	$validUser 						= $initializationArray['validUser'];
	if ($doDebug) {
		echo "Initialization Array:<br /><pre>";
		print_r($initializationArray);
		echo "</pre><br />";
	}
	$validUser 					= $initializationArray['validUser'];
	$userName  					= $initializationArray['userName'];
	$userRole					= $initializationArray['userRole'];
	$validTestmode				= $initializationArray['validTestmode'];
	$siteURL					= $initializationArray['siteurl'];
	$currentSemester			= $initializationArray['currentSemester'];
	$nextSemester				= $initializationArray['nextSemester'];
	$semesterTwo				= $initializationArray['semesterTwo'];
	$semesterThree				= $initializationArray['semesterThree'];
	$semesterFour				= $initializationArray['semesterFour'];
	$jobname					= 'Check Student Status';

	if ($userName == '') {
		$content				= "You are not authorized";
		return $content;
	}
	

/// get the time that the process started
	$startingMicroTime			= microtime(TRUE);


	if ($doDebug) {
		ini_set('display_errors','1');
		error_reporting(E_ALL);	
	}

	$strPass					= "1";
	$theURL						= "$siteURL/cwa-check-student-status/";
	$inp_semester				= '';
	$inp_level					= '';
	$inp_callsign				= '';
	$inp_email					= '';
	$inp_phone					= '';
	$inp_mode					= '';
	$inp_verbose				= '';
	$inp_verified				= FALSE;
	$studentRegistrationURL		= "$siteURL/cwa-student-registration/";
	

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
			if ($str_key 		== "encstr") {
				$encstr			 = $str_value;
				$ensctr			= filter_var($encstr,FILTER_UNSAFE_RAW);
				$encodedString	= base64_decode($encstr);
				$myArray		= explode("&",$encodedString);
				foreach($myArray as $thisValue) {
					$encArray	= explode("=",$thisValue);
					$myStr		= $encArray[0];
					${$myStr}	= $encArray[1];
					if ($doDebug) {
						echo "encstr contained $encArray[0] = $encArray[1]<br />";
					}
				}
				if ($inp_mode == 'TESTMODE') {
					$testMode	= TRUE;
				}
				if ($inp_verbose == 'verbose') {
					$doDebug	= TRUE;
				}
			}
			if ($str_key 		== "strpass") {
				$strPass		 = $str_value;
				$strPass		 = filter_var($strPass,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "inp_semester") {
				$inp_semester	 = $str_value;
				$inp_semester	 = filter_var($inp_semester,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "inp_callsign") {
				$inp_callsign	 = strtoupper($str_value);
				$inp_callsign	 = filter_var($inp_callsign,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "inp_email") {
				$inp_email	 = strtolower($str_value);
				$inp_email	 = filter_var($inp_email,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "inp_level") {
				$inp_level	 = $str_value;
				$inp_level	 = filter_var($inp_level,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "inp_phone") {
				$inp_phone	 = $str_value;
				$inp_phone	 = filter_var($inp_phone,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "inp_verified") {
				$inp_verified	 = $str_value;
				$inp_verified	 = filter_var($inp_verified,FILTER_UNSAFE_RAW);
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
		}
	}

	function getClassInfo($theSemester,$theAdvisor,$theClass,$student_time_zone) {

		global $doDebug, $testMode, $advisorClassTableName, $userMasterTableName;
	
		if ($doDebug) {
			echo "<b>FUNCTION:</b> getClassInfo: $theSemester, $theAdvisor, $theClass, $student_time_zone<br />";
		}
		$user_dal = new CWA_User_Master_DAL();
		$advisorclass_dal = new CWA_Advisorclass_DAL();
		
		$operatingMode = 'Production';
		if ($testMode) {
			$operatingMode = 'Testmode';
		}
		// get the user_master info
		$user_data = $user_dal->get_user_master_by_callsign($theCallsign,$operatingMode);
		if ($user_data === FALSE) {
			if ($doDebug) {
				echo "attempting to get user_master for $theCallsign returned FALSE<br />";
			}
			error.log("Check Student Status: ERRORINFO Function getClassInfo reading user_master for $theCallsign returned FALSE");
			return array('FALSE','','No User Master Obtained');
		} else {
			foreach($user_data as $thisField => $thisValue) {
				$$thisField = $thisValue;
			}
		}
		// now get the advisorclass info

		$criteria = [
			'relation' => 'AND',
			'clauses' => [
				['field' => 'advisorclass_call_sign', 'value' => $theCallsign, 'compare' => '=' ],
				['field' => 'advisorclass_sequence', 'value' => $theClass, 'compare' => '=' ],
				['field' => 'advisorclass_semester', 'value' => $thesemester, 'compare' => '=' ]
			]
		];
		$advisorclass_data = $advisorclass_dal->get_advisorclasses_by_order($criteria, 'advisorclass_sequence', 'ASC', $operatingMode);
		if ($advisorclass_data === FALSE) {
			if ($doDebug) {
				echo "attempting to get advisorclass for $theCallsign class $theClass returned FALSE<br />";
			}
			error.log("Check Student Status: ERRORINFO Function getClassInfo reading advisorclass for $theCallsign class $theClass returned FALSE");
			return array('FALSE','','No advisorclass Obtained');
		} else {
			foreach($advisorclass_data as $key => $value) {
				foreach($value as $thisField => $thisValue) {
					$$thisField = $thisValue;
				}
				$result						= utcConvert('tolocal',$student_time_zone,$advisorClass_class_schedule_times_utc,$advisorClass_class_schedule_days_utc);
				if ($result[0] == 'FAIL') {
					if ($doDebug) {
						echo "utcConvert failed 'tolocal',$student_time_zone,$advisorClass_class_schedule_times_utc,$advisorClass_class_schedule_days_utc<br />
							  Error: $result[3]<br />";
					}
					$displayDays			= "ERROR: UTC Convert failed";
					$displayTimes			= '';
					$returnInfo				= array(FALSE,$displayTimes,$displayDays);
				} else {
					$displayTimes			= $result[1];
					$displayDays			= $result[2];
					$returnInfo				= array(TRUE,$displayTimes,$displayDays);
					if ($doDebug) {
						echo "Returned class schedue $displayTimes on $displayDays<br />";
					}
				}
			}
		}
		return $returnInfo;
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
	
	
	$content = "";	

	if ($testMode) {
		$content	.= "<p><strong>Operating in Test Mode.</strong></p>";
		if ($doDebug) {
			echo "<p><strong>Operating in Test Mode.</strong></p>";
		}
		$operatingMode = 'Testmode';
		$studentTableName			= "wpw1_cwa_student2";
		$advisorClassTableName		= "wpw1_cwa_advisorclass2";
		$userMasterTableName		= "wpw1_cwa_user_master2";
	} else {
		$operatingMode = 'Production';
		$studentTableName			= "wpw1_cwa_student";
		$advisorClassTableName		= "wpw1_cwa_advisorclass";
		$userMasterTableName		= "wpw1_cwa_user_master";
	}

	$student_dal = new CWA_Student_DAL();
	$advisorclass_cal = new CWA_Advisorclass_DAL();
	$user_dal = new CWA_User_Master_DAL();

	$optionList						= "";
	if ($currentSemester != 'Not in Session') {
		$optionList					.= "<option value='$currentSemester'>$currentSemester</option><br />";
	}
	$optionList						.= "<option value='$nextSemester'>$nextSemester</option><br />
										<option value='$semesterTwo'>$semesterTwo</option><br />
										<option value='$semesterThree'>$semesterThree</option><br />
										<option value='$semesterFour'>$semesterFour</option><br />";

	


	if ("1" == $strPass) {
		if ($doDebug) {
			echo "<br />Function starting. userRole: $userRole<br />";
		}

		// set the userName
		if ($userRole == 'student') {
			$userName		= strtoupper($userName);
			$inp_callsign	= $userName;
			$strPass		= "2";
		} elseif ($userRole == 'administrator') {
			$content		.= "<h3>$jobname Administrator Role</h3>
								<form method='post' action='$theURL' 
								name='selection_form' ENCTYPE='multipart/form-data''>
								<input type='hidden' name='strpass' value='2'>
								Call Sign: <br />
								<table style='border-collapse:collapse;'>
								<tr><td>Student Call Sign</td>
									<td><input type='text' class='formInputText' name='inp_callsign' size='10' maxlength='10' value='$inp_callsign' autofocus></td>
								$testModeOption
								<tr><td>&nbsp;</td><td><input class='formInputButton' type='submit' value='Next' /></td></tr></table>
								</form>";


		}
	}


///// Pass 2 -- do the work


	if ("2" == $strPass) {
		if ($doDebug) {
			echo "<br />Arrived at pass $strPass with:<br />
				  Call Sign: $inp_callsign<br />";
		}
		///// based on the info entered, get the student registration
		// first, get the user_master
		$user_data = $user_dal->get_user_master_by_callsign($inp_callsign,$operatingMode);
		if ($user_data === FALSE) {
			if ($doDebug) {
				echo "attempting to get user_master for $inp_callsign returned FALSE<br />";
			}
			error.log("Check Student Status: ERRORINFO reading user_master for $inp_callsign returned FALSE");
		} else {
			foreach($user_data as $thisField => $thisValue) {
				$$thisField = $thisValue;
			}
		
			// now get a student record, if one exists
			$criteria = [
				'relation' => 'AND',
				'clauses' => [
					// field1 = $value1
					['field' => 'student_call_sign', 'value' => $inp_callsign, 'compare' => '='],
					
					// (field2 = $value2 OR field2 = $value3)
					[
						'relation' => 'OR',
						'clauses' => [
							['field' => 'student_semester', 'value' => $currentSemester, 'compare' => '='],
							['field' => 'student_semester', 'value' => $nextSemester, 'compare' => '='],
							['field' => 'student_semester', 'value' => $semesterTwo, 'compare' => '='],
							['field' => 'student_semester', 'value' => $semesterThree, 'compare' => '='],
							['field' => 'student_semester', 'value' => $semesterFour, 'compare' => '=']
						]
					]
				]
			];
			$orderby = 'student_date_created';
			$order = 'DESC';
			$student_data = $student_dal->get_student_by_order($criteria,$orderby,$order,$operatingMode);
			if ($student_data === FALSE) {
				if ($doDebug) {
					echo "attempting to get student for $inp_callsign returned FALSE<br />";
				}
				error.log("Check Student Status: ERRORINFO reading student returned FALSE");
			} else {
				foreach($student_data as $key => $value) {
					foreach($value as $thisField => $thisValue) {
						$$thisField = $thisValue;
					}
				
					if ($doDebug) {
						echo "Processing $student_call_sign<br />
								Semester: $student_semester<br />
								Student Status: $student_status<br />
								Assigned advisor: $student_assigned_advisor<br />
								Assigned Class: $student_assigned_advisor_class<br />
								Time Zone: $student_time_zone<br />";
					}
					$catalogOptions		= "";
					$daysToSemester	= days_to_semester($student_semester);
					if ($doDebug) {
						echo "daysToSemester $student_semester: $daysToSemester<br />";
					}
					$content			.= "<h3>Check Student Status for $student_call_sign<h3>
											<p>Your current registration information:<br />
											<table style='width:auto;'>
											<tr><td>Semester</td>
												<td>$student_semester</td></tr>
											<tr><td>Level</td>
												<td>$student_level</td></tr>
											<tr><td>Class Language</td>
												<td>$student_class_language</td></tr>";
												
					if ($student_response == 'R') {
						$content		.= "<tr><td>Response</td>
												<td>REFUSED -- you have indicated that you are not 
													available to take a class.</td></tr></table>
													<p>If you have further questions, concerns, or want 
													the status changed, contact the appropriate person at 
													<a href='https://cwops.org/cwa-class-resolution/' 
													target='_blank'>CW Academy Class Resolution</a>.</p>";
					} else {
						if ($daysToSemester > 48) {
							if ($student_response == 'Y') {
								$studentLink	= "<a href='$siteURL/cwa-display-and-update-student-signup-information/?strpass=2&request_type=callsign&request_info=$userName&inp_depth=one&doDebug&testMode' target='_blank'>$userName</a>";
								sendErrorEmail("$jobname -- $studentLink -- response is set to Y and more 
than 48 days before the semester. Possible error");
							}
							if ($student_no_catalog == 'Y') {
								$thisOptions				= '';
								if ($student_catalog_options != '') {
									$myArray				= explode(",",$student_catalog_options);
									foreach($myArray as $thisData) {
										$thisOptions	.= "$thisData<br >";
									}
								} else {
									if ($student_flexible == 'Y') {
										$thisOptions	= "Flexible";
									} else {
										$thisOptions		= 'None Selected';
									}
								}										
								$content	.= "<tr><td style='vertical-align:top;'>Class Preferences</td>
													<td>$thisOptions</td></tr>
												</table>
												<p>About 45 days before the semester begins 
												you will receive an email requesting you to review your class 
												preferences and update them. The NEW catalog will be available 
												by then. <span style='color:red;'>You <b>MUST</b> respond to that 
												email or you will not be considered for assignment to a class</span></p>";
							} else {
								$content	.= "<tr><td style='vertical-align:top;'>Class Preferences</td>
													<td>First: $student_first_class_choice<br />
														Second: $student_second_class_choice<br />
														Third: $student_third_class_choice</td></tr>
												</table>
												<p>About 45 days before the semester begins 
												you will receive an email requesting you to review your class 
												preferences and update them. An updated catalog will be available 
												by then. <span style='color:red;'>You <b>MUST</b> respond to that 
												email or you will not be considered for assignment to a class</span></p>";
							}
						} elseif ($daysToSemester > 20) {			/// assignments haven't happened yet
							if ($student_no_catalog == 'Y' && $student_response == '') {
								$passPhone				= substr($user_phone,-5,5);
								$stringToPass			= "inp_callsign=$student_call_sign&inp_phone=$passPhone&inp_email=$user_email&inp_mode=$inp_mode&strPass=2&inp_verbose=$inp_verbose&inp_verify=Y";
								$content	.= "</table>
												<p>You have not verified your class 
												preferences as requested in earlier emails from CW Academy. 
												If you want to be considered for assignment to a class, 
												you <span style='color:red;'><b>MUST</b></span> go to 
												<a href='$studentRegistrationURL'>Student Sign-up</a> 
												and update your preferred class schedule and alternates.";
							} else {
								$content	.= "<tr><td style='vertical-align:top;'>Class Preferences</td>
													<td>First: $student_first_class_choice<br />
														Second: $student_second_class_choice<br />
														Third: $student_third_class_choice</td></tr>
												</table>
												<p>Student assignment to advisor classes will not occur until about 
												20 days before the $student_semester starts. Until then, no additional status information is available.</p>";
								if ($doDebug) {
									echo "Student assignment has not happened<br />";
								}
							}
						} elseif ($daysToSemester > 0 && $daysToSemester < 21) {
							if ($student_response == '') {
								$content	.= "<tr><td style='vertical-align:top;'>Class Preferences</td>
													<td>First: $student_first_class_choice<br />
														Second: $student_second_class_choice<br />
														Third: $student_third_class_choice</td></tr>
												</table>
												<p>You have not responded to CW Academy verification request emails. 
											   Check your email, including the spam and promotions folders for email from 
											   CW Academy and respond appropriately</p>";
							} elseif ($student_response == 'Y') {
								if ($student_status == '') {
									$content	.= "<tr><td style='vertical-align:top;'>Class Preferences</td>
													<td>$student_first_class_choice<br />
														$student_second_class_choice<br />
														$student_third_class_choice</td></tr>
												</table>
												<p>You are on a waiting list for assignment to a class should a student drop out and a 
													vacancy arise. You will receive more information before the semester starts. If you do not get assigned 
													to a class, your sign up will be automatically moved to the next semester and you will be given heightened 
													priority for assignment to a class.</p>";
								} elseif ($student_status == 'S') {
									$content	.= "<tr><td style='vertical-align:top;'>Class Preferences</td>
													<td>$student_first_class_choice<br />
														$student_second_class_choice<br />
														$student_third_class_choice</td></tr>
												</table>
												<p>You have been assigned to a class. 
													Your advisor should contact you within the next few days to give you the actual class schedule and 
													confirm that you will be able to participate in this class.</p>";								
								} elseif ($student_status == 'R' || $student_status == 'C' || $student_status == 'N' || $student_status == 'V') {
									$content	.= "</table><p>You were assigned to a class, however the 
													advisor has removed you from the class. Either the class did not 
													meet your needs or you did not respond to the advisor.</p>";
								} elseif ($student_status == 'Y') {
									$content	.= "<tr><td style='vertical-align:top;'>Assigned Advisor</td>
														<td>$student_assigned_advisor</td></tr>
													</table>
													<p>You have been assigned to a class and your advisor has contacted you to give 
													you the actual class schedule and confirmed that you will be able to participate in this class.</p>";
								}
							} else {
								$content	.= "<p>You were registered for the $student_semester semester for a 
												$student_level class. You said that you were not available 
												to take a class and your registration has been cancelled.</p>";
							}								
						} elseif ($daysToSemester < 0) {
							if ($student_status == '') {
								$content	.= "<tr><td style='vertical-align:top;'>Class Preferences</td>
												<td>$student_first_class_choice<br />
													$student_second_class_choice<br />
													$student_third_class_choice</td></tr>
											</table>
											<p>The semester is already underway. You are on a waiting list for assignment to a class should a student drop out and a 
												vacancy arise. If you do not get assigned 
												to a class, your sign up will be automatically moved to the next semester and you will be given heightened 
												priority for assignment to a class.</p>";
							} elseif ($student_status == 'S') {
								$content	.= "<tr><td style='vertical-align:top;'>Class Preferences</td>
												<td>$student_first_class_choice<br />
													$student_second_class_choice<br />
													$student_third_class_choice</td></tr>
											</table>
											<p>You have been assigned to a class. 
												Your advisor should have already contacted you within the next few days to give you the actual class schedule and 
												confirm that you will be able to participate in this class. For further assistance, please contact the appropriate 
												person at <a href='https://cwops.org/cwa-class-resolution/' target='_blank'>CWA Class Resolution</a>.</p>";								
							} elseif ($student_status == 'R' || $student_status == 'C' || $student_status == 'C' || $student_status == 'V') {
								$content	.= "</table><p>You were assigned to a class, however the 
												advisor has removed you from the class. Either the class did not 
												meet your needs or you did not respond to the advisor. If you need further assistance, please contact 
												the appropriate person at <a href='https://cwops.org/cwa-class-resolution/' target='_blank'>CWA Class Resolution</a>.</p>";	
							} elseif ($student_status == 'Y' && $student_promotable == '') {
								$content	.= "<tr><td style='vertical-align:top;'>Assigned Advisor</td>
													<td>$student_assigned_advisor</td></tr>
												</table>
												<p>The semester is underway. You have been assigned to a class and your advisor has contacted you to give 
												you the actual class schedule and confirmed that you will be able to participate in this class.</p>";
							} elseif ($student_status == 'Y' && $student_promotable != '') {
								if ($student_promotable == 'P') {
									$content	.= "</table>
													<p>You have successfully completed the class and can sign up for the next level class</p>";
								} elseif ($student_promotable == 'W') {
									$content	.= "</table>
													<p>You withdrew from the class. You can sign up for a class</p>";
								} elseif ($student_promotable == 'N') {
									$content	.= "</table>
													<p>The class is complete, however your advisor marked you as not having met the class criteria. 
													You should sign up to take the class again</p>";
								} else {
									$content	.= "</table>
													<p>The class is complete. You may sign up for a future class</p>";
								}
								
							} else {
								$content	.= "<p>You were registered for the $student_semester semester for a 
												$student_level class. You said that you were not available 
												to take a class and your registration has been cancelled.</p>";
							}								
						}
				
						$content		.= "<p>If you have further questions or concerns, contact the appropriate person at 
											<a href='https://cwops.org/cwa-class-resolution/' target='_blank'>CW Academy Class Resolution</a>.</p>
										<p>Thanks!<br />CW Academy</p>";
					}
				}
			}
		}
	}
	$thisTime 		= date('Y-m-d H:i:s');
	$content 		.= "<br /><br /><p>Prepared at $thisTime</p>";
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
add_shortcode ('check_student_status', 'check_student_status_func');

