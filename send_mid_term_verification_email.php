function send_mid_term_verification_email_func() {

/*	Send mid-term class verification email to advisors
 *	This function is run around the middle of the semester.
 *
 *	Read the advisor table for the current semester
 *	if 'class_verified' is blank, prepare an email to the advisor
 *	and send the email
 *
 *	The email will have a link to 'Verify Advisor Class' so the advisor
 *	can perform the class verification
 *
 *	Modified 1Feb2020 by Roland to highlight the action the advisor needs to take
 *  Modified 4Mar21 by Roland to change Joe Fisher's email address
 	Modified 23Jan2022 by Roland to use tables rather than pods
 	Modified 16Feb2022 by Roland to only send emails to advisors with students
 	Modified 27Oct22 by Roland for the new timezone table formats
 	Modified 17Apr23 by Roland to fix action_log
 	Modified 14Jul23 by Roland to use consolidated tables
 	Modified 31Jan24 by Roland to use reminders
 	Modified 1Oct24 by Roland for new database
*/

	global $wpdb;

	$doDebug						= FALSE;
	$testMode						= FALSE;
	$context = CWA_Context::getInstance();
	if ($doDebug) {
		echo "Initialization Array:<br /><pre>";
		print_r($context->toArray());
		echo "</pre><br />";
	}
	$validUser 						= $context->validUser;
	$userName  						= $context->userName;
	$userRole						= $context->userRole;
	$validTestmode					= $context->validTestmode;
	$siteURL						= $context->siteurl;
	$currentSemester				= $context->currentSemester;

	if ($userName == '') {
		return "YOU'RE NOT AUTHORIZED!<br />Goodby";
	}

	if ($userRole != 'administrator') {
		$doDebug			= FALSE;
		$testMode			= FALSE;
	}

//	if ($doDebug) {
		ini_set('display_errors','1');
		error_reporting(E_ALL);	
//	}

/// get the time that the process started
	$startingMicroTime			= microtime(TRUE);

	$strPass					= "1";
	$requestType				= '';
	$mailCount					= 0;
	$increment					= 0;
	$prevAdvisor				= array();
	$actionDate					= date('Y/m/d h:i');
	$verifyURL					= "$siteURL/cwa-verify-advisor-class/";
	$theURL						= "$siteURL/cwa-send-mid-term-verification-email/";
	$jobname					= 'Send Mid-term Verification Email';

// get the input information
	if (isset($_REQUEST)) {
		foreach($_REQUEST as $str_key => $str_value) {
			if ($doDebug) {
				echo "Key: $str_key | Value: $str_value <br />\n";
			}
			if ($str_key 		== "strpass") {
				$strPass		 = $str_value;
				$strPass		 = filter_var($strPass,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "inp_verbose") {
				$inp_verbose	 = $str_value;
				$inp_verbose	 = filter_var($inp_verbose,FILTER_UNSAFE_RAW);
				if ($inp_verbose == 'verbose') {
					$doDebug	= TRUE;
					echo "Turned doDebug TRUE<br />";
				}
			}
			if ($str_key 		== "inp_mode") {
				$inp_mode	 = $str_value;
				$inp_mode	 = filter_var($inp_mode,FILTER_UNSAFE_RAW);
				if ($inp_mode == 'TESTMODE') {
					$testMode = TRUE;
					echo "Turned testMode TRUE<br />";
				}
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
		$studentTableName	= 'wpw1_cwa_student2';
		$advisorTableName	= 'wpw1_cwa_advisor2';
		$userMasterTableName	= 'wpw1_cwa_user_master2';
		if ($doDebug) {
			echo "Function is under development. Using student2 and advisor2, not the production data.<br />";
		}
		$extMode					= 'tm';
		$content .= "Function is under development. Using student2 and advisor2, not the production data.<br />";
	} else {
		$studentTableName 	= 'wpw1_cwa_student';
		$advisorTableName	= 'wpw1_cwa_advisor';
		$userMasterTableName	= 'wpw1_cwa_user_master';
		$extMode					= 'pd';
	}


	if ("1" == $strPass) {
		if ($doDebug) {
			echo "Function starting.<br />";
		}
		$content 		.= "<h3>Send Mid-term Verification Email to Advisors</h3>
							<p>This function is run around the middle of the semester to send an email to the advisors 
							with a link to a web page so they can verify which students are actualy attending their 
							class(es). When the advisor does the verification, that information is recorded in the 
							advisor table so the advisor won't receive a follow-up email.<br /><br />
							Click 'Submit' to start the process.</p>
							<p><form method='post' action='$theURL' 
							name='selection_form' ENCTYPE='multipart/form-data''>
							<input type='hidden' name='strpass' value='2'>
							<table>
							$testModeOption
							<tr><td><input class='formInputButton' type='submit' value='Submit' /></td>
								<td>&nbsp;</td></tr></table>
							</form></p>";

///// Pass 2 -- do the work


	} elseif ("2" == $strPass) {
		if ($doDebug) {
			echo "<br />Arrived at pass 2<br />";
		}

		$content				.= "<h3>$jobname</h3>";
		// get all the advisor records for this semester
		$sql					= "select * from $advisorTableName 
									left join $userMasterTableName on advisor_call_sign = user_call_sign 
									where advisor_semester='$currentSemester' 
									and user_survey_score != '6'";
		$wpw1_cwa_advisor	= $wpdb->get_results($sql);
		if ($wpw1_cwa_advisor === FALSE) {
			handleWPDBError($jobname,$doDebug);
		} else {
			$numARows			= $wpdb->num_rows;
			if ($doDebug) {
				echo "ran $sql<br />and found $numARows rows in $advisorTableName table<br />";
			}
			if ($numARows > 0) {
				foreach ($wpw1_cwa_advisor as $advisorRow) {
					$advisor_master_ID 					= $advisorRow->user_ID;
					$advisor_master_call_sign			= $advisorRow->user_call_sign;
					$advisor_first_name 				= $advisorRow->user_first_name;
					$advisor_last_name 					= $advisorRow->user_last_name;
					$advisor_email 						= $advisorRow->user_email;
					$advisor_phone 						= $advisorRow->user_phone;
					$advisor_city 						= $advisorRow->user_city;
					$advisor_state 						= $advisorRow->user_state;
					$advisor_zip_code 					= $advisorRow->user_zip_code;
					$advisor_country_code 				= $advisorRow->user_country_code;
					$advisor_whatsapp 					= $advisorRow->user_whatsapp;
					$advisor_telegram 					= $advisorRow->user_telegram;
					$advisor_signal 					= $advisorRow->user_signal;
					$advisor_messenger 					= $advisorRow->user_messenger;
					$advisor_master_action_log 			= $advisorRow->user_action_log;
					$advisor_timezone_id 				= $advisorRow->user_timezone_id;
					$advisor_languages 					= $advisorRow->user_languages;
					$advisor_survey_score 				= $advisorRow->user_survey_score;
					$advisor_is_admin					= $advisorRow->user_is_admin;
					$advisor_role 						= $advisorRow->user_role;
					$advisor_master_date_created 		= $advisorRow->user_date_created;
					$advisor_master_date_updated 		= $advisorRow->user_date_updated;

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

					$doProceed							= TRUE;
					if ($doDebug) {
						echo "<br />Processing $advisor_call_sign ($advisor_last_name, $advisor_first_name)<br />";
					}
					if ($advisor_verify_response == 'R') {
						if ($doDebug) {
							echo "Advisor has verify response of R. Bypassing<br />";
						}
						$doProceed						= FALSE;
						$content						.= "$advisor_call_sign bypassed with verify response of R<br />";
					}
					if ($doProceed) {
						// see if the advisor has any students
						$sql			= "SELECT count(student_id) as student_count 
											from $studentTableName 
											where student_semester='$currentSemester' 
											and student_assigned_advisor='$advisor_call_sign' 
											and (student_status='Y' or student_status='S')";
						$student_count	= $wpdb->get_var($sql);
						if ($student_count == 0) {
							$doProceed			= FALSE;
							if ($doDebug) {
								echo "Advisor $advisor_call_sign has no students. Bypassing<br />";
							}
							$content					.= "$advisor_call_sign bypassed due to no students<br />";
						}
					}
					if ($doProceed) {
						if ($advisor_class_verified == 'Y') {
							if ($doDebug) {
								echo "$advisor_call_sign has completed evaluations. Bypassed<br />";
							}
							$content					.= "$advisor_call_sign bypassed as class already verified<br />";
							$doProceed					= FALSE;
						}
					}

					
					if ($doProceed) {
						// set up the email to the advisor
						$mySubject					= "ACTION REQUIRED: CWA Mid-term CW Academy Class Participants Verification";
						if ($testMode) {
							$email_to				= "rolandksmith@gmail.com";
							$mailCode				= 5;
							$increment++;
							$mySubject				= "TESTMODE $mySubject";
						} else {
							$email_to				= $advisor_email;
							$mailCode				= 12;
						}
						$myContent		= "<p>To: $advisor_last_name, $advisor_first_name ($advisor_call_sign)</p>
<p>About the mid-point of the semester, CW Academy asks each of the advisors to verify 
the students participating in their class(es). This is an important step in peparing to close out the 
semester.</p>
<p><table style='border:4px solid red;'><tr><td>Please Click 
<a href='$siteURL/program-list/'>Advisor Portal</a> to log into the CW Academy website. 
Then click on the action to verify your students</p>
<p>If you have questions or concerns, do not reply to this email as the address is not monitored. 
Instead reach out to <a href='https://cwops.org/cwa-class-resolution/' target='_blank'>CWA Class Resolution</a> and select the 
appropriate person.</p> 
<p>Thank you for your service as an advisor!<br />
CW Academy</p>";

						$mailResult		= emailFromCWA_v2(array('theRecipient'=>$email_to,
																	'theSubject'=>$mySubject,
																	'jobname'=>$jobname,
																	'theContent'=>$myContent,
																	'mailCode'=>$mailCode,
																	'increment'=>$increment,
																	'testMode'=>$testMode,
																	'doDebug'=>$doDebug));
						if ($mailResult === FALSE) {
							if ($doDebug) {
								echo "Sending email to $email_to failed " . $wp_error->get_error_message() . "<br />";
							}
						} else {
							$mailCount++;
							$content	.= "Email sent to $advisor_call_sign at $email_to<br />";
							$advisor_action_log		= "$advisor_action_log / $actionDate MIDVERIFY mid-term verification 
email sent to the advisor ";
							$advisor_action_log 	= addslashes($advisor_action_log);
							$sql		= "update $advisorTableName set advisor_action_log='$advisor_action_log' where advisor_id=$advisor_ID";
							$result		= $wpdb->query($sql);
							if ($result === FALSE) {
								if ($doDebug) {
										echo "Updating $advisorTableName record at $advisor_ID failed<br />
											  wpdb->last_query: " . $wpdb->last_query . "<br />
											  <b>wpdb->last_error: " . $wpdb->last_error . "</b><br />";
								}
							} else {
								if ($doDebug) {
									echo "Successfully updated $advisorTableName record at $advisor_ID<br />";
								}
							}
							// add the reminder for the advisor
							$returnArray			= wp_to_local($advisor_timezone_id,0,5);
							if ($returnArray === FALSE) {
								if ($doDebug) {
									echo "advisor timezone id of $advisor_timezone_id is bogus<br />";
								}
								$effective_date		= date('Y-m-d H:i:s');
								$closeStr			= strtotime("+5 days");
								$close_date			= date('Y-m-d H:i:s', $closeStr);
							} else {
								$effective_date		= $returnArray['effective'];
								$close_date			= $returnArray['expiration'];							
							}
							$token					= mt_rand();
							$email_text				= "<p></p>";
							$reminder_text			= "<b>Mid-term Student Verification</b> 
Please click <a href='$verifyURL?strpass=2&inp_advisor=$advisor_call_sign&extmode=$extMode&token=$token' target='_blank'>
HERE</a> to verify the current makeup of your class(es). A web page will be displayed showing each 
of your students for you to select whether or not that student is in your class as well as allow 
you to identify any additional students.";
							$inputParams		= array("effective_date|$effective_date|s",
														"close_date|$close_date|s",
														"resolved_date||s",
														"send_reminder|N|s",
														"send_once|N|s",
														"call_sign|$advisor_call_sign|s",
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
								$content		.= "Reminder successfully added<br />";
							}

						}
					}
				}
			} else {
				$content	.= "No advisor records found in $advisorTableName pod.";
			}
		}
	}
	$content		.= "<br />$mailCount emails sent.<br />";
	$thisTime 		= date('Y-m-d H:i:s');
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
	$content 		.= "<br /><br /><p>Prepared at $thisTime</p>";
	return $content;
}
add_shortcode ('send_mid_term_verification_email', 'send_mid_term_verification_email_func');

