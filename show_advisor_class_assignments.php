function show_advisor_class_assignments_func() {

/*
	
	Provides an advisor the ability  to see the advisor's classes and
	student status.
	
	Requires the advisor call sign, email address, and phone number to gain access
	
	First looks in advisor by date_created descending to see if there are any records.
	If so, show the advisor record and class information followed by students (if any)
	Then show status of all past students	
	
	
	
	modified 27Aug2021 by Roland to include a link to update a student's status
	Modified 21Feb2022 by Roland to use tables rather than pods and to consolidate two 
		programs into one
	Modified 27Oct22 by Roland for the new timezone table format
	Modified 2Mar23 by Roland to show more info before students are assigned and 
		to show previous semester student status
	Modified 17Apr23 to fix action_log
	Extensively modified 30June23 by Roland and upgraded to V3
	Modified 6July23 to use consolidated tables
	Modified 28Aug23 by Roland to set replacement_status field if advisor wants no more students
	Modified 31Aug23 by Roland to turn off debug and testmode if valid user is N
	Modified 18Nov23 by Roland for the new advisor portal
	Modified 25Sep24 by Roland for new database
	
*/	

	global $wpdb,$userName,$validTestmode;

	$doDebug					= FALSE;
	$testMode					= FALSE;
	$initializationArray 		= data_initialization_func();
	$validUser 						= $initializationArray['validUser'];
	$versionNumber				= '5';
	$jobname					= "Show Advisor Class Assignments V$versionNumber";
	if ($doDebug) {
		echo "Initialization Array:<br /><pre>";
		print_r($initializationArray);
		echo "</pre><br />";
	}
	$validUser 					= $initializationArray['validUser'];
	$userName					= $initializationArray['userName'];
	$userRole					= $initializationArray['userRole'];
	$validTestmode				= $initializationArray['validTestmode'];
	$currentSemester			= $initializationArray['currentSemester'];
	$prevSemester				= $initializationArray['prevSemester'];
	$nextSemester				= $initializationArray['nextSemester'];
	$proximateSemester			= $initializationArray['proximateSemester'];
	$prevSemester				= $initializationArray['prevSemester'];
	$daysToSemester				= $initializationArray['daysToSemester'];
	$siteURL					= $initializationArray['siteurl'];	

	if ($userRole != 'administrator') {				// turn off debug and testmode
		$doDebug					= FALSE;
		$testMode					= FALSE;
	}
	if ($userName == '') {
		$content				.= "You are not authorized";
		return $content;
	}
	
	ini_set('memory_limit','256M');
	ini_set('display_errors','1');
	error_reporting(E_ALL);	

/// get the time that the process started
	$startingMicroTime			= microtime(TRUE);

	$theSemester				= $initializationArray['proximateSemester'];
	$strPass					= "1";
	$studentArray				= array();
	$studentDataArray			= array();
	$totalStudents				= 0;
	$advisor_semester			= '';
	$inp_verified				= 'N';
	$inp_callsign				= "";
	$inp_email					= "";
	$inp_phone					= "";
	$userName					= $initializationArray['userName'];
	$advisorVerificationURL		= "$siteURL/cwa-advisor-verification-of-student/";
	$promotableArray			= array('P'=>'Yes',
										'N'=>'No',
										'W'=>'Withdrew',
										''=>'');
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
			if ($str_key 		== "inp_email") {
				$inp_email	 = strtolower($str_value);
				$inp_email	 = filter_var($inp_email,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "inp_phone") {
				$inp_phone	 = $str_value;
				$inp_phone	 = filter_var($inp_phone,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "advisor_semester") {
				$advisor_semester	 = $str_value;
				$advisor_semester	 = filter_var($advisor_semester,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "inp_verified") {
				$inp_verified	 = $str_value;
				$inp_verified	 = filter_var($inp_verified,FILTER_UNSAFE_RAW);
			}
		}
	}

	$theURL							= "$siteURL/cwa-show-advisor-class-assignments/";
	$errorArray						= array();

	if ($testMode) {
		$studentTableName			= 'wpw1_cwa_student2';
		$advisorTableName			= 'wpw1_cwa_advisor2';
		$advisorClassTableName		= 'wpw1_cwa_advisorclass2';
		$audioAssessmentTableName	= 'wpw1_cwa_audio_assessment2';
		$userMasterTableName		= 'wpw1_cwa_user_master2';
		$thisMode					= 'TM';
		$theStatement				= "<p>Function is running in TEST MODE using test files.</p>";
	} else {
		$studentTableName			= 'wpw1_cwa_student';
		$advisorTableName			= 'wpw1_cwa_advisor';
		$advisorClassTableName		= 'wpw1_cwa_advisorclass';
		$audioAssessmentTableName	= 'wpw1_cwa_audio_assessment';
		$userMasterTableName		= 'wpw1_cwa_user_master';
		$thisMode					= 'PM';
		$theStatement				= "";
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

	
	if ("1" == $strPass) {
		if ($doDebug) {
			echo "Function starting.<br />";
		}
		// set the userName
		if ($userRole == 'advisor') {
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
								<tr><td>Advisor Call Sign</td>
									<td><input type='text' class='formInputText' name='inp_callsign' size='10' maxlength='10' autofocus></td>
								$testModeOption
								<tr><td>&nbsp;</td><td><input class='formInputButton' type='submit' value='Next' /></td></tr></table>
								</form>";
		}
	}

		
		
		
	if ("2" == $strPass) {
	
		if ($doDebug) {
			echo "At pass 2 with<br />
				  inp_callsign: $inp_callsign<br />";
		}
		
		$content					.= "<h4>Show Advisor Class Assignments for $inp_callsign</h4>
										<p>Showing data for the past 6 semesters</p>";
		
		$doProceed					= TRUE;
		$thisCallSign					= '';
		$thisEmail						= '';
		$thisPhone						= '';

		/// get the user_master record and display it
		$sql				= "select * from $userMasterTableName 
								where user_call_sign = '$inp_callsign'";
		$sqlResult		= $wpdb->get_results($sql);
		if ($sqlResult === FALSE) {
			handleWPDBError($jobname,$doDebug);
		} else {
			$numRows	= $wpdb->num_rows;
			if ($doDebug) {
				echo "ran $sql<br />and retrieved $numRows rows<br />";
			}
			if ($numRows > 0) {
				foreach($sqlResult as $sqlRow) {
					$user_id				= $sqlRow->user_ID;
					$user_call_sign			= $sqlRow->user_call_sign;
					$user_first_name		= $sqlRow->user_first_name;
					$user_last_name			= $sqlRow->user_last_name;
					$user_email				= $sqlRow->user_email;
					$user_ph_code			= $sqlRow->user_ph_code;
					$user_phone				= $sqlRow->user_phone;
					$user_city				= $sqlRow->user_city;
					$user_state				= $sqlRow->user_state;
					$user_zip_code			= $sqlRow->user_zip_code;
					$user_country_code		= $sqlRow->user_country_code;
					$user_country			= $sqlRow->user_country;
					$user_whatsapp			= $sqlRow->user_whatsapp;
					$user_telegram			= $sqlRow->user_telegram;
					$user_signal			= $sqlRow->user_signal;
					$user_messenger			= $sqlRow->user_messenger;
					$user_action_log		= $sqlRow->user_action_log;
					$user_timezone_id		= $sqlRow->user_timezone_id;
					$user_languages			= $sqlRow->user_languages;
					$user_survey_score		= $sqlRow->user_survey_score;
					$user_is_admin			= $sqlRow->user_is_admin;
					$user_role				= $sqlRow->user_role;
					$user_prev_callsign		= $sqlRow->user_prev_callsign;
					$user_date_created		= $sqlRow->user_date_created;
					$user_date_updated		= $sqlRow->user_date_updated;
		
					$content		.= "<h4>Advisor Master Data</h4>
									<table style='width:900px;'>
									<tr><td><b>Callsign<br />$user_call_sign</b></td>
										<td><b>Name</b><br />$user_last_name, $user_first_name</td>
										<td><b>Phone</b><br />+$user_ph_code $user_phone</td>
										<td><b>Email</b><br />$user_email</td></tr>
									<tr><td><b>City</b><br />$user_city</td>
										<td><b>State</b><br />$user_state</td>
										<td><b>Zip Code</b><br />$user_zip_code</td>
										<td><b>Country</b><br />$user_country</td></tr>
									<tr><td><b>WhatsApp</b><br />$user_whatsapp</td>
										<td><b>Telegram</b><br />$user_telegram</td>
										<td><b>Signal</b><br />$user_signal</td>
										<td><b>Messenger</b><br />$user_messenger</td></tr>
									<tr><td><b>Date Created</b><br />$user_date_created</td>
										<td><b>Date Updated</b><br />$user_date_updated</td>
										<td colspan='2'></td></tr>
									</table>";
					// now get the advisor and class records
			
					$sql			= "select * from $advisorTableName 
										where advisor_call_sign = '$inp_callsign' 
										order by advisor_date_created DESC 
										limit 6";
					$wpw1_cwa_advisor	= $wpdb->get_results($sql);
					if ($wpw1_cwa_advisor === FALSE) {
						handleWPDBError($jobname,$doDebug,"Pass 2 getting advisor data for $inp_callsign. userName: $userName");
					} else {
						$numARows			= $wpdb->num_rows;
						if ($doDebug) {
							$myStr			= $wpdb->last_query;
							echo "ran $myStr<br />and found $numARows rows in $advisorTableName table<br />";
						}
						if ($numARows > 0) {
							foreach ($wpw1_cwa_advisor as $advisorRow) {
								$advisor_ID							= $advisorRow->advisor_id;
								$advisor_call_sign 					= strtoupper($advisorRow->advisor_call_sign);
								$advisor_semester 					= $advisorRow->advisor_semester;
								$advisor_welcome_email_date 		= $advisorRow->advisor_welcome_email_date;
								$advisor_verify_email_date 			= $advisorRow->advisor_verify_email_date;
								$advisor_verify_email_number 		= $advisorRow->advisor_verify_email_number;
								$advisor_verify_response 			= strtoupper($advisorRow->advisor_verify_response);
								$advisor_action_log 				= $advisorRow->advisor_action_log;
								$advisor_class_verified 			= $advisorRow->advisor_class_verified;
								$advisor_control_code 				= $advisorRow->advisor_control_code;
								$advisor_date_created 				= $advisorRow->advisor_date_created;
								$advisor_date_updated 				= $advisorRow->advisor_date_updated;
								$advisor_replacement_status 		= $advisorRow->advisor_replacement_status;
									
								$content			.= "<h4>Semester: $advisor_semester</h4>";
										
								if ($doDebug) {
									echo "now getting the advisorclass records<br />";
								}
								$sql	= "select * from $advisorClassTableName 
										where advisorclass_call_sign = '$inp_callsign' 
											and advisorclass_semester = '$advisor_semester'  
										order by advisorclass_sequence";
								$wpw1_cwa_advisorclass				= $wpdb->get_results($sql);
								if ($wpw1_cwa_advisorclass === FALSE) {
									handleWPDBError($jobname,$doDebug);
									$doProceed 			= FALSE;
								} else {
									$numACRows						= $wpdb->num_rows;
									if ($doDebug) {
										echo "ran $sql<br />and found $numACRows rows<br />";
									}
						
									if ($numACRows > 0) {
										foreach ($wpw1_cwa_advisorclass as $advisorClassRow) {
											$advisorClass_ID				 		= $advisorClassRow->advisorclass_id;
											$advisorClass_call_sign 				= $advisorClassRow->advisorclass_call_sign;
											$advisorClass_sequence 					= $advisorClassRow->advisorclass_sequence;
											$advisorClass_semester 					= $advisorClassRow->advisorclass_semester;
											$advisorClass_timezone_offset			= $advisorClassRow->advisorclass_timezone_offset;	// new
											$advisorClass_level 					= $advisorClassRow->advisorclass_level;
											$advisorClass_class_size 				= $advisorClassRow->advisorclass_class_size;
											$advisorClass_class_schedule_days 		= $advisorClassRow->advisorclass_class_schedule_days;
											$advisorClass_class_schedule_times 		= $advisorClassRow->advisorclass_class_schedule_times;
											$advisorClass_class_schedule_days_utc 	= $advisorClassRow->advisorclass_class_schedule_days_utc;
											$advisorClass_class_schedule_times_utc 	= $advisorClassRow->advisorclass_class_schedule_times_utc;
											$advisorClass_action_log 				= $advisorClassRow->advisorclass_action_log;
											$advisorClass_class_incomplete 			= $advisorClassRow->advisorclass_class_incomplete;
											$advisorClass_date_created				= $advisorClassRow->advisorclass_date_created;
											$advisorClass_date_updated				= $advisorClassRow->advisorclass_date_updated;
											$advisorClass_student01 				= $advisorClassRow->advisorclass_student01;
											$advisorClass_student02 				= $advisorClassRow->advisorclass_student02;
											$advisorClass_student03 				= $advisorClassRow->advisorclass_student03;
											$advisorClass_student04 				= $advisorClassRow->advisorclass_student04;
											$advisorClass_student05 				= $advisorClassRow->advisorclass_student05;
											$advisorClass_student06 				= $advisorClassRow->advisorclass_student06;
											$advisorClass_student07 				= $advisorClassRow->advisorclass_student07;
											$advisorClass_student08 				= $advisorClassRow->advisorclass_student08;
											$advisorClass_student09 				= $advisorClassRow->advisorclass_student09;
											$advisorClass_student10 				= $advisorClassRow->advisorclass_student10;
											$advisorClass_student11 				= $advisorClassRow->advisorclass_student11;
											$advisorClass_student12 				= $advisorClassRow->advisorclass_student12;
											$advisorClass_student13 				= $advisorClassRow->advisorclass_student13;
											$advisorClass_student14 				= $advisorClassRow->advisorclass_student14;
											$advisorClass_student15 				= $advisorClassRow->advisorclass_student15;
											$advisorClass_student16 				= $advisorClassRow->advisorclass_student16;
											$advisorClass_student17 				= $advisorClassRow->advisorclass_student17;
											$advisorClass_student18 				= $advisorClassRow->advisorclass_student18;
											$advisorClass_student19 				= $advisorClassRow->advisorclass_student19;
											$advisorClass_student20 				= $advisorClassRow->advisorclass_student20;
											$advisorClass_student21 				= $advisorClassRow->advisorclass_student21;
											$advisorClass_student22 				= $advisorClassRow->advisorclass_student22;
											$advisorClass_student23 				= $advisorClassRow->advisorclass_student23;
											$advisorClass_student24 				= $advisorClassRow->advisorclass_student24;
											$advisorClass_student25 				= $advisorClassRow->advisorclass_student25;
											$advisorClass_student26 				= $advisorClassRow->advisorclass_student26;
											$advisorClass_student27 				= $advisorClassRow->advisorclass_student27;
											$advisorClass_student28 				= $advisorClassRow->advisorclass_student28;
											$advisorClass_student29 				= $advisorClassRow->advisorclass_student29;
											$advisorClass_student30 				= $advisorClassRow->advisorclass_student30;
											$advisorClass_number_students			= $advisorClassRow->advisorclass_number_students;
											$advisorClass_class_evaluation_complete = $advisorClassRow->advisorclass_evaluation_complete;
											$advisorClass_class_comments			= $advisorClassRow->advisorclass_class_comments;
											$advisorClass_copycontrol				= $advisorClassRow->advisorclass_copy_control;
			
						
											if ($doDebug) {
												echo "have advisorclass sequence $advisorClass_sequence record for $advisorClass_semester semester<br />";
											}
											$content	.= "<b>Class $advisorClass_sequence:</b>
															<table style='width:900px;'>
															<tr><td style='vertical-align:top;width:300px;'><b>Sequence</b><br />
																	$advisorClass_sequence</td>
																<td style='vertical-align:top;width:300px;'><b>Level</b><br />
																	$advisorClass_level</td>
																<td style='vertical-align:top;'><b>Class Size</b><br />
																	$advisorClass_class_size</td></tr>
															<tr><td style='vertical-align:top;'><b>Class Teaching Days</b><br />
																	$advisorClass_class_schedule_days</td>
																<td style='vertical-align:top;' colspan='2'><b>Local Start Time</b><br />
																	$advisorClass_class_schedule_times</td></tr></table>";
						
											// if there are students, then show the student name list
											if ($doDebug) {
												echo "there are $advisorClass_number_students students in the class<br />";
											}
											$displayClass			= FALSE;										
											if ($advisorClass_number_students > 0) {	
												
												$daysToGo				= days_to_semester($advisorClass_semester);
												if($doDebug) {
													echo "preparing to display student info. Days to $advisor_semester semester: $daysToGo<br />";
												}
												if ($daysToGo > 0 && $daysToGo < 21) {
													$content				.= "<p>Students have been assigned to classes for the $advisor_semester semester. 
																				The semester has not yet started. You have been assigned the 
																				following students:</p>\n";
													$displayClass		= TRUE;
												} elseif ($daysToGo < 0 && $daysToGo > 20) {
													$content				.= "<p>Students have not yet been assigned to advisor classes.</p>";
												} elseif ($daysToGo <0 && $daysToGo > -60) {								
													$content				.= "<p>Students have been assigned to classes for the $advisor_semester semester. 
																				The semester is underway. You have been assigned the 
																				following students:</p>\n";
													$displayClass			= TRUE;
												} else {
													$content				.= "<p>Students have been assigned to classes for the $advisor_semester semester. 
																				The semester is completed. You were assigned the 
																				following students:</p>\n";
													$displayClass			= TRUE;
												}
												if ($displayClass) {
													if ($doDebug) {
														echo "displaying class information<br />";
													}
													// put out the student header
													$content				.= "<table style='border-collapse:collapse;'>";
													
													$studentCount			= 0;
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
															$wpw1_cwa_student	= $wpdb->get_results("select * from $studentTableName 
																										left join $userMasterTableName on user_call_sign = student_call_sign 
																										where student_semester='$advisorClass_semester' 
																										and student_call_sign = '$theInfo'");
															if ($wpw1_cwa_student === FALSE) {
																handleWPDBError($jobname,$doDebug,');
															} else {
																$numSRows									= $wpdb->num_rows;
																if ($doDebug) {
																	$myStr					= $wpdb->last_query;
																	echo "ran $myStr<br />and found $numSRows rows<br />";
																}
																if ($numSRows > 0) {
																	foreach ($wpw1_cwa_student as $studentRow) {
																		$student_master_ID 					= $studentRow->user_ID;
																		$student_master_call_sign 			= $studentRow->user_call_sign;
																		$student_first_name 				= $studentRow->user_first_name;
																		$student_last_name 					= $studentRow->user_last_name;
																		$student_email 						= $studentRow->user_email;
																		$student_phone 						= $studentRow->user_phone;
																		$student_city 						= $studentRow->user_city;
																		$student_state 						= $studentRow->user_state;
																		$student_zip_code 					= $studentRow->user_zip_code;
																		$student_country_code 				= $studentRow->user_country_code;
																		$student_whatsapp 					= $studentRow->user_whatsapp;
																		$student_telegram 					= $studentRow->user_telegram;
																		$student_signal 					= $studentRow->user_signal;
																		$student_messenger 					= $studentRow->user_messenger;
																		$student_master_action_log 			= $studentRow->user_action_log;
																		$student_timezone_id 				= $studentRow->user_timezone_id;
																		$student_languages 					= $studentRow->user_languages;
																		$student_survey_score 				= $studentRow->user_survey_score;
																		$student_is_admin					= $studentRow->user_is_admin;
																		$student_role 						= $studentRow->user_role;
																		$student_master_date_created 		= $studentRow->user_date_created;
																		$student_master_date_updated 		= $studentRow->user_date_updated;
													
																		$student_ID								= $studentRow->student_id;
																		$student_call_sign						= $studentRow->student_call_sign;
																		$student_time_zone  					= $studentRow->student_time_zone;
																		$student_timezone_offset				= $studentRow->student_timezone_offset;
																		$student_youth  						= $studentRow->student_youth;
																		$student_age  							= $studentRow->student_age;
																		$student_parent 				= $studentRow->student_parent;
																		$student_parent_email  					= strtolower($studentRow->student_parent_email);
																		$student_level  						= $studentRow->student_level;
																		$student_waiting_list 					= $studentRow->student_waiting_list;
																		$student_request_date  					= $studentRow->student_request_date;
																		$student_semester						= $studentRow->student_semester;
																		$student_notes  						= $studentRow->student_notes;
																		$student_welcome_date  					= $studentRow->student_welcome_date;
																		$student_email_sent_date  				= $studentRow->student_email_sent_date;
																		$student_email_number  					= $studentRow->student_email_number;
																		$student_response  						= strtoupper($studentRow->student_response);
																		$student_response_date  				= $studentRow->student_response_date;
																		$student_abandoned  					= $studentRow->student_abandoned;
																		$student_status  						= strtoupper($studentRow->student_status);
																		$student_action_log  					= $studentRow->student_action_log;
																		$student_pre_assigned_advisor  			= $studentRow->student_pre_assigned_advisor;
																		$student_selected_date  				= $studentRow->student_selected_date;
																		$student_no_catalog  					= $studentRow->student_no_catalog;
																		$student_hold_override  				= $studentRow->student_hold_override;
																		$student_assigned_advisor  				= $studentRow->student_assigned_advisor;
																		$student_advisor_select_date  			= $studentRow->student_advisor_select_date;
																		$student_advisor_class_timezone 		= $studentRow->student_advisor_class_timezone;
																		$student_hold_reason_code  				= $studentRow->student_hold_reason_code;
																		$student_class_priority  				= $studentRow->student_class_priority;
																		$student_assigned_advisor_class 		= $studentRow->student_assigned_advisor_class;
																		$student_promotable  					= $studentRow->student_promotable;
																		$student_excluded_advisor  				= $studentRow->student_excluded_advisor;
																		$student_survey_completion_date	= $studentRow->student_survey_completion_date;
																		$student_available_class_days  			= $studentRow->student_available_class_days;
																		$student_intervention_required  		= $studentRow->student_intervention_required;
																		$student_copy_control  					= $studentRow->student_copy_control;
																		$student_first_class_choice  			= $studentRow->student_first_class_choice;
																		$student_second_class_choice  			= $studentRow->student_second_class_choice;
																		$student_third_class_choice  			= $studentRow->student_third_class_choice;
																		$student_first_class_choice_utc  		= $studentRow->student_first_class_choice_utc;
																		$student_second_class_choice_utc  		= $studentRow->student_second_class_choice_utc;
																		$student_third_class_choice_utc  		= $studentRow->student_third_class_choice_utc;
																		$student_catalog_options				= $studentRow->student_catalog_options;
																		$student_flexible						= $studentRow->student_flexible;
																		$student_date_created 					= $studentRow->student_date_created;
																		$student_date_updated			  		= $studentRow->student_date_updated;
													
																		// if you need the country name and phone code, include the following
																		$countrySQL		= "select * from wpw1_cwa_country_codes  
																							where country_code = '$student_country_code'";
																		$countrySQLResult	= $wpdb->get_results($countrySQL);
																		if ($countrySQLResult === FALSE) {
																			handleWPDBError($jobname,$doDebug);
																			$student_country		= "UNKNOWN";
																			$student_ph_code		= "";
																		} else {
																			$numCRows		= $wpdb->num_rows;
																			if ($doDebug) {
																				echo "ran $countrySQL<br />and retrieved $numCRows rows<br />";
																			}
																			if($numCRows > 0) {
																				foreach($countrySQLResult as $countryRow) {
																					$student_country		= $countryRow->country_name;
																					$student_ph_code		= $countryRow->ph_code;
																				}
																			} else {
																				$student_country			= "Unknown";
																				$student_ph_code			= "";
																			}
																		}
			
						
						
																		if ($doDebug) {
																			echo "<br />Processing student $student_call_sign<br />
																					&nbsp;&nbsp;&nbsp;&nbsp;Level: $student_level<br />
																					&nbsp;&nbsp;&nbsp;&nbsp;Class: $student_assigned_advisor_class<br />
																					&nbsp;&nbsp;&nbsp;&nbsp;Status: $student_status<br />
																					&nbsp;&nbsp;&nbsp;&nbsp;Promotable: $student_promotable<br />";
																		}
					
																		$studentCount++;
																		$content			.= "<tr><td style='vertical-align:top;width:100px;'><b>Call Sign</b></td>
																									<td style='vertical-align:top;width:150px;'><b>Name</b></td>
																									<td style='vertical-align:top;width:200px;'><b>Email</b></td>
																									<td style='vertical-align:top;width:200px;'><b>Phone</b></td>
																									<td style='vertical-align:top;width:100px;'><b>Country</b></td>
																									<td style='vertical-align:top;width:100px;'><b>Verified</b></td>
																									<td style='vertical-align:top;width:100px;'><b>Promotable</b></td></tr>";
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
																		$extras							= "Additional contact options: ";
																		$haveExtras						= FALSE;
																		if ($student_whatsapp != '' ) {
																			$extras						.= "WhatsApp: $student_whatsapp ";
																			$haveExtras					= TRUE;
																		}
																		if ($student_signal != '' ) {
																			$extras						.= "Signal: $student_signal ";
																			$haveExtras					= TRUE;
																		}
																		if ($student_telegram != '' ) {
																			$extras						.= "Telegram: $student_telegram ";
																			$haveExtras					= TRUE;
																		}
																		if ($student_messenger != '' ) {
																			$extras						.= "Messenger: $student_messenger ";
																			$haveExtras					= TRUE;
																		}
										
					
																		$myStr							= "";
																		if ($student_status == 'S') {
																			$myStr						= " <b>Unverified</b>";
																		} elseif ($student_status == 'Y') {
																			$myStr						= "Verified";
																		}
																		if ($doDebug) {
																			echo "displaying student $student_call_sign information<br />";
																		}
																		$dispPromotable		= $promotableArray[$student_promotable];
																		$content						.= "<tr><td style='vertical-align:top;'>$student_call_sign</td>
																												<td style='vertical-align:top;'>$student_last_name, $student_first_name</td>
																												<td style='vertical-align:top;'>$student_email</td>
																												<td style='vertical-align:top;'>+$student_ph_code $student_phone</td>
																												<td style='vertical-align:top;'>$student_country</td>
																												<td style='vertical-align:top;'>$myStr</td>
																												<td style='vertical-align:top;'>$dispPromotable</td></tr>";
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
																								parent or guardian is $thisParent at email address $thisParentEmail.</td></tr>";
																			}
																		}
					
																		if ($hasAssessment) {
																			$enstr		= base64_encode("advisor_call_sign=$student_assigned_advisor&inp_callsign=$student_call_sign");
																			$content	.= "<tr><td colspan='7' style='border-bottom: 1px solid #000;'>Click <a href='$siteURL/cwa-view-a-student-cw-assessment-v2/?strpass=2&enstr=$enstr' target='_blank'>HERE</a> to review $student_call_sign's self assessment</td></tr>";
																		} else {
																			$content	.= "<tr><td colspan='7' style='border-bottom: 1px solid #000;'>&nbsp;</td></tr>";
																		}
																	}	/// end of the student foreach
																} else {		/// no student record found ... send error message
																	sendErrorEmail("Prepare Advisor Class Display: no $studentTableName table record found for student$strSnum in advisor $advisorClass_call_sign class $advisorClass_sequence");
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
												$content					.= "<p>No students are assigned to this class.</p>";
												if ($doDebug) {
													echo "No students assigned to this class<br /><br />";
												}
											}
										}
									} else {
										$content				.= "<p>No advisor class records found</p>";
										if ($doDebug) {
											echo "no advisor class records found<br /><br />";
										}
									}
								}
							}
						} else {
							$content		.= "<p>No advisor records found</p>";
							if ($doDebug) {
								echo "No advisor records found <br />";
							}
						}
					}
				}
			} else {
				if ($doDebug) {
					echo "no user_master record found for $inp_callsign<br />";
				}
				$content	.= "<p>No User Master record found. This is a system error. 
								The sysadmin has been notified</p>";
				sendErrorEmail("$jobname pass2 no user_master record found for $inp_callsign. userName: $userName");
			}
		}
	}

	$thisTime 					= date('Y-m-d H:i:s');
	$content 		.= "<br /><br /><a href='$siteURL/program-list/'>Return to Student Portal</a>
						<br /><br /><p>V$versionNumber. Prepared at $thisTime</p>";
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
	$result			= write_joblog_func("$jobname|$nowDate|$nowTime|$userName|Time|$thisStr|$strPass: $elapsedTime");
	if ($result == 'FAIL') {
		$content	.= "<p>writing to joblog.txt failed</p>";
	}
	return $content;
}
add_shortcode ('show_advisor_class_assignments', 'show_advisor_class_assignments_func');
