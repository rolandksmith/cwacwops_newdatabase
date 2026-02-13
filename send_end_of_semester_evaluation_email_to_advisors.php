function send_end_of_semester_assessment_email_to_advisors_func() {

/* Send End of Semester Assessment Email to Advisors
 *
 * Run about a week before the end of the semester
 * Sends an email to each advisor who has one or more classes explaining 
 	the ability to order a Morse code assessment
 	
 * created 10Jun2022 by Roland from send_evaluation_email_to_advisors
 	Modified 13Jul23 by Roland to use consolidated tables
 	Modified 18Jun24 by Roland to just send the email
 	Modified 5Oct24 by Roland for new database

*/

	global $wpdb;

	$doDebug						= FALSE;
	$testMode						= FALSE;
	$initializationArray 			= data_initialization_func();
	if ($doDebug) {
		echo "Initialization Array:<br /><pre>";
		print_r($initializationArray);
		echo "</pre><br />";
	}
	$validUser = $initializationArray['validUser'];
	$siteURL			= $initializationArray['siteurl'];
	$userName			= $initializationArray['userName'];

	if ($userName == '') {
		return "YOU'RE NOT AUTHORIZED!<br />Goodby";
	}

// set some initialization values
//	if ($doDebug) {
		ini_set('display_errors','1');
		error_reporting(E_ALL);	
//	}
	
/// get the time that the process started
	$startingMicroTime			= microtime(TRUE);

	$strPass			= "1";
	$advisorArray		= array();
	$myCount			= 0;
	$additionaltext		= "";
	$increment			= 0;
	$validTestmode		= $initializationArray['validTestmode'];
	$siteURL			= $initializationArray['siteurl'];
	$theURL				= "$siteURL/cwa-send-end-of-semester-assessment-email-to-advisors/";
	$jobname			= 'Send End of Semester Eval Email to Advisors';
	$currentSemester	= $initializationArray['currentSemester'];
	$nextSemester		= $initializationArray['nextSemester'];
	$prevSemester		= $initializationArray['prevSemester'];
	if ($currentSemester == "Not in Session") {
		$theSemester	= $prevSemester;
	} else {
		$theSemester	= $currentSemester;			
	}


// get the input information
	if (isset($_REQUEST)) {
		foreach($_REQUEST as $str_key => $str_value) {
			if ($doDebug) {
				echo "Key: $str_key | Value: $str_value <br />\n";
			}
			if ($str_key 		== "strpass") {
				$strPass		 = $str_value;
				$strPass		= filter_var($strPass,FILTER_UNSAFE_RAW);
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
			if ($str_key 		== "inp_advisors") {
				$inp_advisors	 = $str_value;
				$inp_advisors	 = strtoupper(filter_var($inp_advisors,FILTER_UNSAFE_RAW));
			}
			if ($str_key 		== "addl_comments") {
				$addl_comments	 = $str_value;
				$addl_comments	 = filter_var($addl_comments,FILTER_UNSAFE_RAW);
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



	$content = "";	

	if ($testMode) {
		$content	.= "<p><strong>Operating in Test Mode.</strong></p>";
		if ($doDebug) {
			echo "<p><strong>Operating in Test Mode.</strong></p>";
		}
		$advisorTableName			= "wpw1_cwa_advisor2";
		$advisorClassTableName		= "wpw1_cwa_advisorclass2";
		$userMasterTableName		= 'wpw1_cwa_user_master2';
		$inp_mode						= 'tm';
	} else {
		$advisorTableName			= "wpw1_cwa_advisor";
		$advisorClassTableName		= "wpw1_cwa_advisorclass";
		$userMasterTableName		= 'wpw1_cwa_user_master';
		$inp_mode						= 'pd';
	}


	if ("1" == $strPass) {
		if ($doDebug) {
			echo "Function starting.<br />";
		}
		$content 		.= "<h3>Send Advisors an Email for Student to Do an End of Semester Assessment</h3>
							<p>This function reads the advisorClass table for the current (or just completed) 
							semester and sends an email to the advisors with instructions on how to order an end-of-semester 
							Morse code evaluation for any or all of their students.
							<p><form method='post' action='$theURL' 
							name='selection_form' ENCTYPE='multipart/form-data'>
							<input type='hidden' name='strpass' value='2'>
							<table>
							<tr><td style='vertical-align:top;'>If selected advisors are to receive this email enter their call signs:</td>
								<td><textarea class='formInputText' name='inp_advisors' rows='5' cols='50'></textarea></td></tr>
							<tr><td style='vertical-align:top;'>Enter any additional comments (if any) to add to the email to the advisors:</td>
								<td><textarea class='formInputText' name='addl_comments' rows='5' cols='50'></textarea></td></tr>
							$testModeOption
							<tr><td colspan='2'><input class='formInputButton' type='submit' value='Submit' /></tr></td></table>
							</form></p>";

///// Pass 2 -- do the work


	} elseif ("2" == $strPass) {
	
		if ($doDebug) {
			echo "Arrived at pass 2<br />";
		}

		$content .= "<h3>Send Advisors an Email on how to order an End of Semester Assessment</h3>";
		
		$selectedAdvisorsArray			= array();
		//// see if a list of advisors was entered
		if ($inp_advisors != '') {
			$inp_advisors			= str_replace(" ","",$inp_advisors);
			// build an array of selected advisors
			$myArray	= explode(",",$inp_advisors);
			foreach($myArray as $thisAdvisor) {			
				$sql				= "select * from $advisorTableName 
										left join $userMasterTableName on advisor_call_sign = user_call_sign 
										where call_sign = '$thisAdvisor' 
										and semester = '$theSemester' 
										and response != 'R'";
				$wpw1_cwa_advisor	= $wpdb->get_results($sql);
				if ($wpw1_cwa_advisor === FALSE) {
					handleWPDBError($jobname,$doDebug);
				} else {
					$lastError			= $wpdb->last_error;
					if ($lastError != '') {
						handleWPDBError($jobname,$doDebug);
						$content		.= "Fatal program error. System Admin has been notified";
						return $content;
					}
					$numARows			= $wpdb->num_rows;
					if ($doDebug) {
						$myStr			= $wpdb->last_query;
						echo "ran $myStr<br />and found $numARows rows in $advisorTableName table<br />";
					}
					if ($numARows > 0) {
						foreach ($wpw1_cwa_advisor as $advisorRow) {
							$user_id				= $advisorRow->user_ID;
							$user_call_sign			= $advisorRow->user_call_sign;
							$user_first_name		= $advisorRow->user_first_name;
							$user_last_name			= $advisorRow->user_last_name;
							$user_email				= $advisorRow->user_email;
							$user_ph_code			= $advisorRow->user_ph_code;
							$user_phone				= $advisorRow->user_phone;
							$user_city				= $advisorRow->user_city;
							$user_state				= $advisorRow->user_state;
							$user_zip_code			= $advisorRow->user_zip_code;
							$user_country_code		= $advisorRow->user_country_code;
							$user_country			= $advisorRow->user_country;
							$user_whatsapp			= $advisorRow->user_whatsapp;
							$user_telegram			= $advisorRow->user_telegram;
							$user_signal			= $advisorRow->user_signal;
							$user_messenger			= $advisorRow->user_messenger;
							$user_action_log		= $advisorRow->user_action_log;
							$user_timezone_id		= $advisorRow->user_timezone_id;
							$user_languages			= $advisorRow->user_languages;
							$user_survey_score		= $advisorRow->user_survey_score;
							$user_is_admin			= $advisorRow->user_is_admin;
							$user_role				= $advisorRow->user_role;
							$user_prev_callsign		= $advisorRow->user_prev_callsign;
							$user_date_created		= $advisorRow->user_date_created;
							$user_date_updated		= $advisorRow->user_date_updated;
			
							$advisor_ID							= $advisorRow->advisor_id;
							$advisor_call_sign 					= strtoupper($advisorRow->call_sign);
							$advisor_semester 					= $advisorRow->semester;
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
							
							if ($doDebug) {
								echo "<br />Processing advisor $advisor_call_sign<br />";
							}
							// see if the advisor has any classes
							$classCount			= $wpdb->get_var("select count(advisor_call_sign) 
																from $advisorClassTableName where 
																advisor_call_sign = '$advisor_call_sign' 
																and semester = '$advisor_semester'");
							if ($classCount == Null || $classCount == 0) {
								if ($doDebug) {
									echo "advisor has no classes. Bypassing<br />";
								}
							} else {
								if ($doDebug) {
									echo "adding advisor to the selectedAdvisors array<br />";
									
								}
								$selectedAdvisorsArray[]	= "$advisor_call_sign|$advisor_email|$advisor_last_name, $advisor_first_name";
							}
						}
					} else {
						$content			.= "<p>No advisor records found</p>";
					}
				}
			}
		} else {
			$sql				= "select * from $advisorTableName 
									left join $userMasterTableName on advisor_call_sign = user_call_sign 
									where semester = '$theSemester' 
									and response != 'R' 
									order by call_sign";
			$wpw1_cwa_advisor	= $wpdb->get_results($sql);
			if ($wpw1_cwa_advisor === FALSE) {
				handleWPDBError($jobname,$doDebug);
			} else {
				$lastError			= $wpdb->last_error;
				if ($lastError != '') {
					handleWPDBError($jobname,$doDebug);
					$content		.= "Fatal program error. System Admin has been notified";
					return $content;
				}
				$numARows			= $wpdb->num_rows;
				if ($doDebug) {
					$myStr			= $wpdb->last_query;
					echo "ran $myStr<br />and found $numARows rows in $advisorTableName table<br />";
				}
				if ($numARows > 0) {
					foreach ($wpw1_cwa_advisor as $advisorRow) {
						$user_id				= $advisorRow->user_ID;
						$user_call_sign			= $advisorRow->user_call_sign;
						$user_first_name		= $advisorRow->user_first_name;
						$user_last_name			= $advisorRow->user_last_name;
						$user_email				= $advisorRow->user_email;
						$user_ph_code			= $advisorRow->user_ph_code;
						$user_phone				= $advisorRow->user_phone;
						$user_city				= $advisorRow->user_city;
						$user_state				= $advisorRow->user_state;
						$user_zip_code			= $advisorRow->user_zip_code;
						$user_country_code		= $advisorRow->user_country_code;
						$user_country			= $advisorRow->user_country;
						$user_whatsapp			= $advisorRow->user_whatsapp;
						$user_telegram			= $advisorRow->user_telegram;
						$user_signal			= $advisorRow->user_signal;
						$user_messenger			= $advisorRow->user_messenger;
						$user_action_log		= $advisorRow->user_action_log;
						$user_timezone_id		= $advisorRow->user_timezone_id;
						$user_languages			= $advisorRow->user_languages;
						$user_survey_score		= $advisorRow->user_survey_score;
						$user_is_admin			= $advisorRow->user_is_admin;
						$user_role				= $advisorRow->user_role;
						$user_prev_callsign		= $advisorRow->user_prev_callsign;
						$user_date_created		= $advisorRow->user_date_created;
						$user_date_updated		= $advisorRow->user_date_updated;
		
						$advisor_ID							= $advisorRow->advisor_id;
						$advisor_call_sign 					= strtoupper($advisorRow->call_sign);
						$advisor_semester 					= $advisorRow->semester;
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
						
						if ($doDebug) {
							echo "<br />Processing advisor $advisor_call_sign<br />";
						}
						// see if the advisor has any classes
						$classCount			= $wpdb->get_var("select count(advisor_call_sign) 
															from $advisorClassTableName where 
															advisor_call_sign = '$advisor_call_sign' 
															and semester = '$advisor_semester'");
						if ($classCount == Null || $classCount == 0) {
							if ($doDebug) {
								echo "advisor has no classes. Bypassing<br />";
							}
						} else {
							if ($doDebug) {
								echo "adding advisor to the selectedAdvisors array<br />";
								
							}
							$selectedAdvisorsArray[]	= "$advisor_call_sign|$advisor_email|$advisor_last_name, $advisor_first_name";
						}
					}
				} else {
					$content			.= "<p>No advisor records found</p>";
				}
			}					
		}		
		
		// Have the array of advisors needing to receive an email
		sort($advisorArray);
		
		if ($doDebug) {
			echo "<br />Advisor Array:<br />";
			foreach($advisorArray as $value) {
				echo "$value<br />";
			}
			echo "<br />";
		}

		foreach($advisorArray as $advisorArrayValue) {
			$advisorData		= explode("|",$advisorArrayValue);
			$advisor_call_sign	= $advisorData[0];
			$advisor_email		= $advisorData[1];
			$advisor_first_name	= $advisorData[2];
			$advisor_last_name	= $advisorData[3];
			
			if ($doDebug) {
				echo "Getting email ready to send to $advisor_email ($advisor_call_sign)<br />";
			}
			$mySubject 		= "CW Academy Student Promotability Assessment for Advisor Class(es)";
			if ($testMode) {
				$myTo			= 'kcgator@gmail.com';
				$mailCode		= 2;
				$increment++;
				$mySubject 		= "TESTMODE $mySubject";
			} else {
				$myTo			= $advisor_email;
				$mailCode		= 12;
			}
			if ($addl_comments != '') {
				$addl_comments	= "<p>$addl_comments</p>";
			}
			$emailContent 		= "<p>To: $advisor_last_name, $advisor_first_name ($advisor_call_sign)</p>
$addl_comments
<p>The $theSemester semester is coming to an end. In a few days you will receive an email from CW Academy 
asking you to evaluate the promotability of your students.</p>
<p>To assist you in your evaluation process, you may want to have some or all of your students 
do a Morse code assessment and consider that as part of your evaluation. Advisors who wish to use 
this assessment tool generally select the students and send the assessment request about a 
week before the end of the semester.</p>
<p>To order assessments, go to your <a href='$siteURL/program-list/'>Advisor Portal</a> and select the 'Order Morse Code Proficiency Assessments' 
function. The program will explain how to order the assessments.</p>
<p>Thank you very much for your service as an Advisor!<br />
CW Academy</p>
<table style='border:4px solid red;'><tr><td>
<p><span style='color:red;font-size:14pt;'><b>Do not reply to this email as the address is not monitored.</b> 
<br />Please refer to the appropriate person at <a href='https://cwops.org/cwa-class-resolution/'>CWA Class 
Resolution</a> for assistance.</span></p></td></tr></table>";
			$mailResult		= emailFromCWA_v2(array('theRecipient'=>$myTo,
													    'theSubject'=>$mySubject,
													    'theContent'=>$emailContent,
													    'jobname'=>$jobname,
													    'mailCode'=>$mailCode,
													    'increment'=>$increment,
													    'testMode'=>$testMode,
													    'doDebug'=>$doDebug));
			if ($mailResult === TRUE) {
				$content .= "An email was sent to $advisor_call_sign ($myTo)<br />";
				$myCount++;
			} else {
				echo "The mail send function failed.<br /><pre>";
			}
		}
	}
	
	
	$thisTime 		= date('Y-m-d H:i:s');
	$content 		.= "<br /><br />$myCount emails sent to advisors<br /><br /><p>Prepared at $thisTime</p>";
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
add_shortcode ('send_end_of_semester_assessment_email_to_advisors', 'send_end_of_semester_assessment_email_to_advisors_func');

