function send_evaluation_email_to_advisors_func() {

/* Send Evaluation Email to Advisors
 *
 * Read the advisorClass table for current semester (or previous semester, if a semester 
 * is not in session) and see if evaluation_complete = Y
 *	If not, check to see if the advisor is in the advisor array
 *		If not in the array, add advisor,callsign to the array
 *
 * When all dvisorClass records have been read, sort the advisor array
 * For each advisor array record
 * 		Get the advisor name and email address from the advisor table
 *		Format the email to the advisor
 *		Send the email
 *
 * Modified 8Jun20 by Roland to use previous semester if a semester is not in session
 * Modified 12May21 by Roland to use past_advisor and past_advisorClass pods
 	Modified 12Feb2022 by Roland to use the new table structure and new website
 	Modified 14Jun23 by Roland to use current tables rather than past tables
 	Modified 14Jul23 by Roland to use consolidated tables
 	Modified 6Dec23 by Roland for Advisor Portal
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
	$validUser			= $initializationArray['validUser'];
	$siteURL			= $initializationArray['siteurl'];
	$userName			= $initializationArray['userName'];
	$validTestmode		= $initializationArray['validTestmode'];
	$nextSemester		= $initializationArray['nextSemester'];
	$currentSemester	= $initializationArray['currentSemester'];
	$prevSemester		= $initializationArray['prevSemester'];
	if ($currentSemester == "Not in Session") {
		$theSemester	= $prevSemester;
	} else {
		$theSemester	= $currentSemester;			
	}
	$doTheEmails		= TRUE;
	if (preg_match('/localhost/',$siteURL) == 1) {
		$doTheEmails	= FALSE;
 		if ($doDebug) {
 			echo "doTheEmails set to FALSE<br />";
 		}
	}

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
	$theURL				= "$siteURL/cwa-send-evaluation-email-to-advisors/";
	$evaluateStudentURL	= "$siteURL/cwa-evaluate-student/";
	$jobname			= 'Send Evaluation Email to Advisors';
	$gotAdditionalAdvisors	= FALSE;


// get the input information
	if (isset($_REQUEST)) {
		foreach($_REQUEST as $str_key => $str_value) {
			if ($doDebug) {
				echo "Key: $str_key | Value: $str_value <br />\n";
			}
			if ($str_key 		== "strpass") {
				$strPass		 = $str_value;
			}
			if ($str_key 		== "additionaltext") {
				$additionaltext		 = $str_value;
			}
			if ($str_key 		== "additionaladvisors") {
				$additionaladvisors		 = strtoupper($str_value);
				if ($additionaladvisors != '') {
					$gotAdditionalAdvisors	= TRUE;
				}
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
				
				table{font:'Times New Roman', sans-serif;background-image:none;}
				
				th {color:#ffff;background-color:#000;padding:5px;font-size:small;}
				
				td {padding:5px;font-size:small;}
				</style>";	

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
		$content 		.= "<h3>Send Evaluation Email to Advisors</h3>
							<p>This function reads the advisorClass table for the current 
							semester and sends an email to any advisors who have not completed evaluating 
							their students requesting them to complete the evaluations.</p>
							<p><form method='post' action='$theURL' 
							name='selection_form' ENCTYPE='multipart/form-data'>
							<input type='hidden' name='strpass' value='2'>
							<table>
							<tr><td>Enter any additional text to be included in the advisor email</td>
								<td><textarea class='formInputText' name='additionaltext' rows='5' cols='50'></textarea></td></tr>
							<tr><td>Enter any specific advisor who should receive this email separated by commas</td>
								<td><textarea class='formInputText' name='additionaladvisors' rows='5' cols='50'></textarea></td></tr>
							$testModeOption
							<tr><td colspan='2'><input class='formInputButton' type='submit' value='Next' /></tr></td></table>
							</form></p>";

///// Pass 2 -- do the work


	} elseif ("2" == $strPass) {
	
		if ($doDebug) {
			echo "Arrived at pass 2<br />";
		}

		$content .= "<h3>Send EvaluationEmail to Advisors</h3>";
		if ($gotAdditionalAdvisors && $additionaladvisors != '') {
			$additionalAdvisorsArray	= explode(",",$additionaladvisors);		
			$content	.= "<p>Processing only $additionaladvisors</p>";
		}
		// Access the advisor TABLE and cycle through the records for currentSemester
		$sql			= "select * from $advisorTableName 
							left join $userMasterTableName on user_call_sign = advisor_call_sign 
							where advisor_semester = '$theSemester' 
							order by advisor_call_sign";
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
					$advisor_ph_code					= $advisorRow->user_ph_code;
					$advisor_phone 						= $advisorRow->user_phone;
					$advisor_city 						= $advisorRow->user_city;
					$advisor_state 						= $advisorRow->user_state;
					$advisor_zip_code 					= $advisorRow->user_zip_code;
					$advisor_country_code 				= $advisorRow->user_country_code;
					$advisor_country 					= $advisorRow->user_country;
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
						
					if ($doDebug) {
						echo "<br />Processing $advisorTableName table record for advisor $advisor_call_sign<br />";
					}
					
					$doContinue							= TRUE;
					$haveAdditionalAdvisor				= FALSE;
					if ($gotAdditionalAdvisors){
						$doContinue						= FALSE;
						if (in_array($advisor_call_sign,$additionalAdvisorsArray)) {
							$doContinue					= TRUE;
							$haveAdditionalAdvisor		= TRUE;
						}
					}
					if ($doContinue){
						$evaluationsComplete			= TRUE;
						// get the class records and see if evaluations are complete
						// if additional advisor, turn evaluations complete off
						$sql			= "select * from $advisorClassTableName 
											left join $userMasterTableName on user_call_sign = advisorclass_call_sign 
											where advisorclass_call_sign = '$advisor_call_sign' 
											and advisorclass_semester = '$advisor_semester' 
											order by advisorclass_sequence";
						$wpw1_cwa_advisorclass				= $wpdb->get_results($sql);
						if ($wpw1_cwa_advisorclass === FALSE) {
							handleWPDBError($jobname,$doDebug);
						} else {
							$numACRows						= $wpdb->num_rows;
							if ($doDebug) {
								echo "ran $sql<br />and found $numACRows rows<br />";
							}
							if ($numACRows > 0) {
								foreach ($wpw1_cwa_advisorclass as $advisorClassRow) {
									$advisorClass_master_ID 				= $advisorClassRow->user_ID;
									$advisorClass_master_call_sign			= $advisorClassRow->user_call_sign;
									$advisorClass_first_name 				= $advisorClassRow->user_first_name;
									$advisorClass_last_name 				= $advisorClassRow->user_last_name;
									$advisorClass_email 					= $advisorClassRow->user_email;
									$advisorClass_ph_code					= $advisorClassRow->user_ph_code;
									$advisorClass_phone 					= $advisorClassRow->user_phone;
									$advisorClass_city 						= $advisorClassRow->user_city;
									$advisorClass_state 					= $advisorClassRow->user_state;
									$advisorClass_zip_code 					= $advisorClassRow->user_zip_code;
									$advisorClass_country_code 				= $advisorClassRow->user_country_code;
									$advisorClass_country	 				= $advisorClassRow->user_country;
									$advisorClass_whatsapp 					= $advisorClassRow->user_whatsapp;
									$advisorClass_telegram 					= $advisorClassRow->user_telegram;
									$advisorClass_signal 					= $advisorClassRow->user_signal;
									$advisorClass_messenger 				= $advisorClassRow->user_messenger;
									$advisorClass_master_action_log 		= $advisorClassRow->user_action_log;
									$advisorClass_timezone_id 				= $advisorClassRow->user_timezone_id;
									$advisorClass_languages 				= $advisorClassRow->user_languages;
									$advisorClass_survey_score 				= $advisorClassRow->user_survey_score;
									$advisorClass_is_admin					= $advisorClassRow->user_is_admin;
									$advisorClass_role 						= $advisorClassRow->user_role;
									$advisorClass_master_date_created 		= $advisorClassRow->user_date_created;
									$advisorClass_master_date_updated 		= $advisorClassRow->user_date_updated;
				
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
				
				
									if ($haveAdditionalAdvisor) {
										$class_evaluation_complete	= '';
										$classUpdateData		= array('tableName'=>$advisorClassTableName,
																		'inp_method'=>'update',
																		'inp_data'=>array('advisorclass_evaluation_complete'=>''),
																		'inp_format'=>array('%s'),
																		'jobname'=>$jobname,
																		'inp_id'=>$advisorClass_ID,
																		'inp_callsign'=>$advisorClass_call_sign,
																		'inp_semester'=>$theSemester,
																		'inp_sequence'=>$advisorClass_sequence,
																		'inp_who'=>$userName,
																		'testMode'=>$testMode,
																		'doDebug'=>$doDebug);
										$updateResult	= updateClass($classUpdateData);
										if ($updateResult[0] === FALSE) {
											handleWPDBError($jobname,$doDebug);
											$doContinue					= FALSE;
										} else {
											$evaluationsComplete		= FALSE;
											$class_evaluation_complete	= 'N';
										}
									}
									if ($doContinue) {	
										if (($advisorClass_class_evaluation_complete == '' || $advisorClass_class_evaluation_complete == 'N') && $advisorClass_number_students > 0){
											if ($doDebug) {
												echo "&nbsp;&nbsp;&nbsp;Advisor will get an email<br />";
											}					
											$advisorArrayValue		= "$advisorClass_call_sign|$advisor_email|$advisor_first_name|$advisor_last_name|$advisor_phone|$advisor_timezone_id";
											if (!in_array($advisorArrayValue,$advisorArray)) {
												$advisorArray[]		= $advisorArrayValue;
												if ($doDebug) {
													echo "&nbsp;&nbsp;&nbsp;Adding $advisorClass_call_sign ,$advisor_email,$advisor_first_name,$advisor_last_name,$advisor_phone,$advisor_timezone_id to advisorArray<br />";
												}
											}
										} else {
											if ($doDebug) {
												echo "AdvisorClass record bypassed as evaluations are complete or no students. class_evaluation_complete = $advisorClass_class_evaluation_complete | class_number_students = $advisorClass_number_students<br />";
											}
										}
									}
								}
							}
						}
					}
				}
			} else {
				$content	.= "$advisorClassTableName has no records<br />";
			}
		}
		
// Have the array of advisors needing to receive an email
		if ($doDebug) {
			sort($advisorArray);
			echo "<br />Advisor Array:<br /><pre>";
			print_r($advisorArray);
			echo "</pre><br />";
		}
// sort the advisor array
		if (!$doDebug) {
			sort($advisorArray);
		}
		foreach($advisorArray as $advisorArrayValue) {
			$advisorData		= explode("|",$advisorArrayValue);
			$advisor_call_sign	= $advisorData[0];
			$advisor_email		= $advisorData[1];
			$advisor_first_name	= $advisorData[2];
			$advisor_last_name	= $advisorData[3];
			$advisor_phone		= $advisorData[4];
			$advisor_tz_id		= $advisorData[5];
			
			if ($doDebug) {
				echo "Getting email ready to send to $advisor_email ($advisor_call_sign)<br />";
			}
			$mySubject 		= "CW Academy Student Promotability Evaluation for Advisor Class(es)";
			if ($testMode) {
				$myTo			= 'kcgator@gmail.com';
				$mailCode		= 2;
				$increment++;
				$mySubject 		= "TESTMODE $mySubject";
			} else {
				$myTo			= $advisor_email;
				$mailCode		= 12;
			}
			if ($additionaltext != '') {
				$additionaltext	= "<p>$additionaltext</p>";
			}
			$emailContent 		= "<p>To: $advisor_last_name, $advisor_first_name ($advisor_call_sign)</p>
$additionaltext
<p>The $theSemester semester is coming to an end and the 
$nextSemester semester will be starting soon. <b>It’s time to evaluate the promotable status of your 
current students.</b> While its not necessary to evaluate your students until the end of the class, 
you'll continue to get reminder emails until you do.</p>
<p>To enter the student promotability information, log in to the <a href='$siteURL/program-list'>CW Academy</a> 
website and follow the instructions under 'Reminders and Actions Requested'.</p>
<p><b>NOTE:</b> <i>Reminder emails will be sent to you periodically until your evaluations are complete.</i></p>
<p>Thank you very much for your service as an Advisor!<br />
CW Academy</p>
<table style='border:4px solid red;'><tr><td>
<p><span style='color:red;font-size:14pt;'><b>Do not reply to this email as the address is not monitored.</b> 
<br />Please refer to the appropriate person at <a href='https://cwops.org/cwa-class-resolution/'>CWA Class 
Resolution</a> for assistance.</span></p></td></tr></table>";

			if ($doTheEmails) {
				$mailResult		= emailFromCWA_v2(array('theRecipient'=>$myTo,
															'theSubject'=>$mySubject,
															'theContent'=>$emailContent,
															'jobname'=>$jobname,
															'mailCode'=>$mailCode,
															'increment'=>$increment,
															'testMode'=>$testMode,
															'doDebug'=>$doDebug));
			} else {
				if ($doDebug) {
					echo "email would have been sent to $myTo<br />";
				}
				$mailResult		= TRUE;
			}
			if ($mailResult === TRUE) {
				$content .= "An email was sent to $advisor_call_sign ($myTo)<br />";
				$myCount++;
			} else {
				echo "The mail send function failed.<br />";
			}
			// add reminder
			if ($doDebug) {
				echo "preparing to add reminder<br />";
			}
			$token			= mt_rand();
			$reminder_text	= "<b>Evaluate Student Promotability</b> Please enter the promotability information for your students, that is,  
is the Beginner, Fundamental, or Intermediate student is ready to take the next higher level class, or 
the Advanced student met the class objectives. 
Please click 
<a href='$evaluateStudentURL?semester=$theSemester&strpass=2&inp_mode=$inp_mode&inp_callsign=$advisor_call_sign&token=$token' target='_blank'>
<b>Evaluate Students</b></a>. A CWA web page will display and allow you to enter your evaluations. 
When all your evaluations are completed, you’ll be immediately able to register as an advisor for the next semester.";


			$returnArray		= wp_to_local($advisor_tz_id, 0, 5);
			if ($returnArray === FALSE) {
				if ($doDebug) {
					echo "called wp_to_local with $advisor_tz_id, 0, 5 which returned FALSE<br />";
				} else {
					sendErrorEmail("$jobname calling wp_to_local with $advisor_tz_id, 0, 5 returned FALSE");
				}
				$effective_date		= date('Y-m-d 00:00:00');
				$closeStr			= strtotime("+ 5 days");
				$close_date			= date('Y-m-d 00:00:00',$closeStr);
			} else {
				$effective_date		= $returnArray['effective'];
				$close_date			= $returnArray['expiration'];
			}
			$token				= mt_rand();
			$inputParams		= array("effective_date|$effective_date|s",
										"close_date|$close_date|s",
										"resolved_date||s",
										"send_reminder|3|s",
										"send_once|N|s",
										"call_sign|$advisor_call_sign|s",
										"role||s",
										"email_text|$emailContent|s",
										"reminder_text|$reminder_text|s",
										"resolved|N|s",
										"token|$token|s");
			$reminderResult	= add_reminder($inputParams,$testMode,$doDebug);
			if ($reminderResult[0] === FALSE) {
				if ($doDebug) {
					echo "adding reminder failed. $reminderResult[1]<br />";
				}
			} else {
				if ($doDebug) {
					echo "adding reminder was successful<br />";
				}
			}

		}
	}
	if ($strPass == '2') {
		// send email that the job was run
		$thisDate				= date('Y-m-d H:i:s');
		$theRecipient			= 'kcgator@gmail.com';
		$theSubject				= "Program Send Evaluation Email to Advisors Was Executed by $userName";
		if ($testMode) {
			$theContent			= "Send Evaluation Email to Advisors was run on $thisDate in TESTMODE";
		} else {
			$theContent			= "Send Evaluation Email to Advisors was run on $thisDate in PRODUCTION";
		}
		$theCc					= '';
		$mailCode				= 18;
		$increment				= 0;
		if ($doTheEmails) {
			$mailResult		= emailFromCWA_v2(array('theRecipient'=>$theRecipient,
													'theSubject'=>$theSubject,
													'theContent'=>$theContent,
													'theCc'=>$theCc,
													'mailCode'=>$mailCode,
													'jobname'=>$jobname,
													'increment'=>$increment,
													'testMode'=>$testMode,
													'doDebug'=>$doDebug));
		} else {
			if ($doDebug) {
				echo "final email would have been sent<br />";
			}
			$mailResult		= TRUE;
		}
		if ($mailResult === FALSE) {
			$content	.= "<p>Email at end of program failed</p>";
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
	$thisStr		= 'Production';
	if ($testMode) {
		$thisStr	= 'Testmode';
	}
	$result			= write_joblog_func("$jobname|$nowDate|$nowTime|$userName|Time|$thisStr|$strPass: $elapsedTime");
	if ($result == 'FAIL') {
		$content	.= "<p>writing to joblog.txt failed</p>";
	}
	return $content;
}
add_shortcode ('send_evaluation_email_to_advisors', 'send_evaluation_email_to_advisors_func');
