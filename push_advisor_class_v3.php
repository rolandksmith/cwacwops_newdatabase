function push_advisor_class_v3_func() {

/*
	edited by Roland on 2020-01-03 10:30 to select the semester for the display
	edited by Roland on 2019-12-15 20:47 to fix the data passed to pass 3
	Original version significantly revised on 17Aug2020. Simplified the logic,
		made the look and feel like other programs that display advisor classes,
		and got the "all" function to work correctly

	modified 10Dec2020 by Roland to properly handle student assigned with a 
		different time zone.
	Modified 26Dec2020 by Roland to add student state and advisor class times to the report
	Modified 4Mar21 by Roland to change Joe Fisher's email address
	Modified 13Mar21 by Roland to add a message to the advisor
	Modified 3Aug21 by Roland to use new advisor and advisorClass pods
	Modified 20Dec21 by Roland to select only students assigned to the advisor if only one advisor is being requested
	Modified 31Dec21 by Roland to use the mysql tables instead of pods
	Modified 17Apr23 by Roland to fix action_log
	Modified 20Jul23 by Roland to use consolidated tables
	Modified 22Nov23 by Roland for the advisor portal. This was a significant upgrade. 
		version number changd to 3.
	
	studentArray[] = student_assigned_advisor|student_assigned_advisor_class|student_level|student_id|student_call_sign
	
	studentDataArray[student_call_sign]['name']
									   ['city']
									   ['state']
									   ['country']
									   ['email']
									   ['phone']
									   ['messaging']
									   ['youth']
									   ['age']
									   ['parent']
									   ['parent email']
	
	
	
	
*/	

	global $wpdb;

	$doDebug					= FALSE;
	$testMode					= FALSE;
	$initializationArray 	= data_initialization_func();
	if ($doDebug) {
		echo "Initialization Array:<br /><pre>";
		print_r($initializationArray);
		echo "</pre><br />";
	}
	$validUser 				= $initializationArray['validUser'];
	$userName				= $initializationArray['userName'];
	$validTestmode			= $initializationArray['validTestmode'];
	$siteURL				= $initializationArray['siteurl'];
	$versionNumber			= "3";

	if ($validUser == "N") {
		return "YOU'RE NOT AUTHORIZED!<br />Goodby";
	}
	/// get the time that the process started
	$startingMicroTime			= microtime(TRUE);

	ini_set('memory_limit','256M');
	ini_set('display_errors','1');
	error_reporting(E_ALL);	
	ini_set('max_execution_time',360);


	$theURL						= "$siteURL/cwa-push-advisor-class/";
	$currentSemester			= $initializationArray['currentSemester'];
	$nextSemester				= $initializationArray['nextSemester'];
	$semesterTwo				= $initializationArray['semesterTwo'];
	$defaultClassSize			= $initializationArray['defaultClassSize'];
	$jobname					= "Push Advisor Class V$versionNumber";
	$strPass					= "1";
	$requestString				= "";
	$request_type				= "Full";
	$inp_semester				= "";
	$gotRecord					= FALSE;
	$sendEmail					= array();
	$inp_msg					= "";
	$inp_cc						= '';
	$inp_bcc					= '';
	$studentArray				= array();
	$studentDataArray			= array();
	$totalStudents				= 0;
	$studentCount				= 0;
	$inpMode					= "";
	$inp_verbose				= '';
	$increment					= 0;
	
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
			if ($str_key 		== "inp_semester") {
				$inp_semester	 = $str_value;
				$inp_semester	 = filter_var($inp_semester,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "request_info") {
				$request_info	 = $str_value;
				$request_info	 = filter_var($request_info,FILTER_UNSAFE_RAW);
				$request_info	 = strtoupper($request_info);
			}
			if ($str_key 		== "email_content") {
				$email_content	 = $str_value;
				$email_content	 = filter_var($email_content,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "theSubject") {
				$theSubject	 = $str_value;
				$theSubject	 = filter_var($theSubject,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "theEmail") {
				$theEmail	 = $str_value;
				$theEmail	 = filter_var($theEmail,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "request_type") {
				$request_type	 = $str_value;
				$request_type	 = filter_var($request_type,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "inp_cc") {
				$inp_cc	 = $str_value;
				$inp_cc	 = filter_var($inp_cc,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "inp_bcc") {
				$inp_bcc	 = $str_value;
				$inp_bcc	 = filter_var($inp_bcc,FILTER_UNSAFE_RAW);
			}
			if ($str_key		== "sendEmail") {
				$sendEmail		 = $str_value;
			}
			if ($str_key		== "inp_msg") {
				$inp_msg		 = $str_value;
				$inp_msg		 = filter_var($inp_msg,FILTER_UNSAFE_RAW);
				$inp_msg		= stripslashes($inp_msg);
			}
		}
	}

	$firstTime						= TRUE;
	$prevAdvisor					= "";
	$prevClass						= "";
	$prevTZ							= 99;
	$emailCount						= 0;
	$emailArray						= array();

	if ($testMode) {
		$studentTableName			= 'wpw1_cwa_consolidated_student2';
		$advisorTableName			= 'wpw1_cwa_consolidated_advisor2';
		$advisorClassTableName		= 'wpw1_cwa_consolidated_advisorclass2';
		$audioAssessmentTableName	= 'wpw1_cwa_audio_assessment2';
		$thisMode					= 'TM';
		$theStatement				= "<p>Function is running in TEST MODE using test files.</p>";
	} else {
		$studentTableName			= 'wpw1_cwa_consolidated_student';
		$advisorTableName			= 'wpw1_cwa_consolidated_advisor';
		$advisorClassTableName		= 'wpw1_cwa_consolidated_advisorclass';
		$audioAssessmentTableName	= 'wpw1_cwa_audio_assessment2';
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


	
/*
 * When strPass is equal to 1 then get the information needed to build the display.
 * The information entered can be a comma-separated list of advisor call signs
 * or the word 'all'
*/
	if ("1" == $strPass) {
		$content .= "<h3>$jobname</h3>
					$theStatement
					<p>Please enter (a) the advisor call sign to be processed, or (b) 
					a comma-separated list of advisor call signs and click 'Next'. The next 
					step will display the classes for each advisor specified and provide the option to 
					send the advisor an email with the current class makeup.</p>
					<form method='post' action='$theURL' 
					name='selection_form' ENCTYPE='multipart/form-data''>
					<input type='hidden' name='strpass' value='2'>
					<input type='hidden' name='inp_semester' value='$inp_semester'>
					<table style='border-collapse:collapse;'><tr><td style='width:150px;'>Requested Advisor(s):</td><td>
					<input class='formInputText' type='text' maxlength='150' name='request_info' size='10' autofocus></td></tr>
					$testModeOption
					<tr><td>&nbsp;</td><td><input class='formInputButton' type='submit' value='Next' /></td></tr></table>
					</form>";
		
		
		
		
	} elseif ("2" == $strPass) {
	
		$doProceed					= TRUE;
		$emailsToSend				= 0;
	
		$currentSemester	= $initializationArray['currentSemester'];
		$nextSemester		= $initializationArray['nextSemester'];
		$inp_semester		= $currentSemester;
		if ($currentSemester == "Not in Session") {
			$inp_semester	= $nextSemester;
		}
		if ($doDebug) {
			echo "<br />Got to pass $strPass with request_info: $request_info and semester: $inp_semester <br />";
		}
		$content .= "<h3>$jobname</h3>$theStatement";
		
		
		$numberAdvisors				= 0;		
		if ($request_info == "") {
			if ($doDebug) {
				echo "No advisors requested<br />";
			}
			$content					.= "<p>Nothing requested.</p>";
			$doProceed					= FALSE;
		} else {
			if ($doProceed) {
				$requestArray 			= explode(",",$request_info);
				$numberAdvisors			= count($requestArray);
				if ($numberAdvisors < 1) {
					$content		= "<p>Must specify at least one advisor call sign. End of process.</p>";
					$doProceed		= FALSE;
				}
			}
		}
		if ($doProceed) {
			sort($requestArray);
			// Now display each requested advisor and their class

			if ($doDebug) {
				echo "<br /><b>Starting Display of Requested Advisor's Classes</b><br />";
			}
			$content					.= "<form method='post' action='$theURL' 
											name='selection_form' ENCTYPE='multipart/form-data''>
											<input type='hidden' name='strpass' value='3'>
											<input type='hidden' name='inp_semester' value='$inp_semester'>
											<input type='hidden' name='inp_mode' value='$inp_mode'>
											<input type='hidden' name='inp_verbose' value='$inp_verbose'>
											<input type='hidden' name='request_type' value='$request_type'>
											<input type='hidden' name='request_info' value='$request_info'>";
			foreach($requestArray as $advisorCallSign) {
				$prepareResult			= prepare_preassigned_class_display($advisorCallSign,
																		$inp_semester,
																		$request_type,
																		FALSE,
																		FALSE,
																		FALSE,
																		$testMode,
																		$doDebug);
				if ($prepareResult[0] == FALSE) {
					$content			.= "Getting data to displayfailed. $prepareResult[1]<br/>";
					$myStr				= "Production";
					if($testMode) {
						$myStr			= "TestMode";
					}
					$errorMsg			= "prepare_preassigned_class_display failed in the Push. $myStr. $userName. $prepareResult[1]";
					sendErrorEmail($errorMsg);
				} else {
					$content			.=	$prepareResult[1]; 
					if ($prepareResult[3] != 0) {
						$content			.= "<p><input type='checkbox' class='formInputButton' name='sendEmail[]' value='$advisorCallSign' 
													checked='checked'> Send advisor an email</p><br />";
						$emailsToSend++;
					} else {
						$content			.= "<p>No Students. No email can be sent</p><br />";
					}
				}
			}
			if ($emailsToSend > 0) {
				$content					.= "<p>Enter any additional information for the advisor(s) below:<br />
												<textarea class='formInputText' name='inp_msg' rows='5' cols='50'></textarea><p>
												<input class='formInputButton' type='submit' value='Submit' /></form>";
			} else {
				$content					.= "<p>No advisors are getting an email.</p>";
			}
		}
///////////

	} elseif ("3" == $strPass) {
// $doDebug	= TRUE;
		if ($doDebug) {
			echo "Arrived at pass 3<br /><br />Input values:<br />
				  inp_semester: $inp_semester<br />
				  inp_mode: $inp_mode<br />
				  request_info: $request_info<br />
				  inp_msg: $inp_msg<br />
				  sendEmail array:<br />";
	 		foreach($sendEmail as $value) {
				echo "Value: $value<br />";
			}
		}
		$content .= "<h3>$jobname</h3>$theStatement";

		$myCount					= 0;
		if ($inp_msg != '') {
			 $inp_msg				= "<p>$inp_msg</p>";
		}
		$advisor_subject			= "CW Academy Action Required: Class Makeup Has Changed";
		foreach($sendEmail as $advisorCallSign) {

			$sql						 	= "select email,
													first_name,
													last_name  
												from $advisorTableName 
												where call_sign = '$advisorCallSign' 
												order by date_created DESC 
												limit 1 ";
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
			} else {
				$numARows			= $wpdb->num_rows;
				if ($doDebug) {
					$myStr			= $wpdb->last_query;
					echo "ran $myStr<br />and found $numARows rows in $advisorTableName table<br />";
				}
				if ($numARows > 0) {
					foreach ($wpw1_cwa_advisor as $advisorRow) {
						$advisor_email		= $advisorRow->email;
						$advisor_first_name	= $advisorRow->first_name;
						$advisor_last_name	= $advisorRow->last_name;
						
						$email_to_advisor 	= "To: $advisor_last_name, $advisor_first_name ($advisorCallSign):
$inp_msg
<p>The makeup of your class has changed. Please log into 
<a href='$siteURL/login'>CW Academy</p> and follow the instructions 
under Actions Required.</p>
<p>Thanks for your service as an advisor!<br />
CW Academy</p>
<p><span style='color:red;font-size:14pt;'><b>Do not reply to this email as the address is not monitored. 
<br />Please refer to the appropriate person at <a href='https://cwops.org/cwa-class-resolution/'>CWA Class 
Resolution</a> for assistance.</b></span><br /></p>";
						if ($testMode) {
							$mailCode				= 5;
							$email_to				= 'rolandksmith@gmail.com';
							$advisor_subject		= "TESTMODE $advisor_subject";
							$increment++;
						} else {
							$email_to				= $advisor_email;
							$mailCode				= 21;
						}
						$mailResult		= emailFromCWA_v2(array('theRecipient'=>$email_to,
																'theSubject'=>$advisor_subject,
																'jobname'=>$jobname,
																'theContent'=>$email_to_advisor,
																'theCc'=>$inp_cc,
																'mailCode'=>$mailCode,
																'increment'=>$increment,
																'testMode'=>$testMode,
																'doDebug'=>$doDebug));
						if ($mailResult === TRUE) {
							if ($doDebug) {
								echo "An email for $advisorCallSign was sent to $email_to<br />";
							}
							$content .= "An email for $advisorCallSign was sent to $advisor_last_name, $advisor_first_name ($advisorCallSign) at $email_to<br /><br />";
							$myCount++;
						} else {
							$content .= "The mail send function to $email_to failed.<br /><br />";
						}
						
						// reminder --- put in a reminder unless one already exists
						// first, see if there is an existing open reminder
						$reminderSQL			= "select * from wpw1_cwa_reminders 
													where call_sign = '$advisorCallSign' 
													and token = 'studentConfirmation'  
													and resolved != 'Y'";
						$reminderCount			= $wpdb->get_results($reminderSQL);
						if ($reminderCount === FALSE) {
							handleWPDBError($jobname,$doDebug);
						} else {
							$numRRows			= $wpdb->num_rows;
							if ($doDebug) {
								echo "ran $reminderSQL<br />and retrieved $numRRows rows<br />";
							} 
							if ($numRRows == 0) {
								$effective_date		 	= date('Y-m-d H:i:s');
								$closeStr				= strtotime("+5 days");
								$close_date				= date('Y-m-d H:i:s', $closeStr);
								$token					= 'studentConfirmation';
								$email_text				= "<p></p>";
								$reminder_text		= "<b>Student Participation Confirmation:</b> The makeup of your 
your class has changed. You should now contact each unconfirmed student, verify if that student will be attending, and then update the 
student status. Click on <a href='cwa-manage-advisor-class-assignments/?strpass=5&inp_call_sign=$advisorCallSign&token=$token' target='_blank'>Manage Advisor Class</a> 
to perform this task."; 
								$inputParams		= array("effective_date|$effective_date|s",
															"close_date|$close_date|s",
															"resolved_date||s",
															"send_reminder||s",
															"send_once||s",
															"call_sign|$advisorCallSign|s",
															"role||s",
															"email_text||s",
															"reminder_text|$reminder_text|s",
															"resolved||s",
															"token|$token|s");
								$insertResult		= add_reminder($inputParams,$testMode,$doDebug);
								if ($insertResult[0] === FALSE) {
									if ($doDebug) {
										echo "inserting reminder failed: $insertResult[1]<br />";
									}
									$content		.= "Inserting reminder failed: $insertResult[1]<br />";
								} else {
									$content		.= "Task Reminder successfully added<br />";
								}
							} else {
								// update close date in the existing reminder
								if ($doDebug) {
									echo "updating existing reminders<br />";
								}
								foreach($reminderCount as $reminderRow) {
									$reminderID		= $reminderRow->record_id;
									$close_date		= $reminderRow->close_date;
									
									$closeStr		= strtotime("+5 days");
									$close_date		= date('Y-m-d H:i:s', $closeStr);
									
									$reminderUpdate	= $wpdb->update('wpw1_cwa_reminders', 
																	array('close_date'=>$close_date),
																	array('record_id'=>$reminderID),
																	array('%s'),
																	array('%d'));
									if ($reminderUpdate === FALSE) {
										handleWPDBError($jobname,$doDebug);
										$content	.= "Reminder failed to update<br />";
									} else {
										$content	.= "Reminder close date updated<br />";
									}
								}
							}
						}
					}
				} else {
					$content	.= "No $advisorTableName record found for $advisorCallSign<br />";
				}
			}
		}
		$content				.= "$myCount Emails sent<br />";
	}
	$thisTime 					= date('Y-m-d H:i:s');
	$content					.= "<br /><br /><br /><p>Report displayed at $thisTime.</p>";
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
	$result			= write_joblog_func("$jobname|$nowDate|$nowTime|$userName|$thisStr|Time|$strPass: $elapsedTime");
	if ($result == 'FAIL') {
		$content	.= "<p>writing to joblog.txt failed</p>";
	}
	return $content;
}
add_shortcode ('push_advisor_class_v3', 'push_advisor_class_v3_func');
