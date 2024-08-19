function process_advisor_verification_func() {


/*	Function to process advisor's response to the advisor verification email	
 *
 *	Input comes from a link in the advisor's verification email
 *		theid: id of the advisor's registration record
 *		action: type of action to take
 *			Y: Confirm the registration
 *			R: Delete the registration
 *
 *	Modified 4Mar21 by Roland to change Joe Fisher's email address
 *	Modified 7July2021 by Roland for the new advisor and advisorClass formats
 *		Removed update ability. That has been moved to the advisor email
 	Modified 21 Jan2022 by Roland to use Tables rather than Tables
 	Modified 22Oct22 by Roland to accomodate timezone formats
 	Modified 16Apr23 by Roland to fix action_log
 	Modified 13Jul23 by Roland to use consolidated tables
 	Modified 31Aug23 by Roland to turn dodebug and testmode off if validUser is N
*/

	global $wpdb, $doDebug, $testMode;

	$doDebug						= FALSE;
	$testMode						= FALSE;
	$initializationArray 			= data_initialization_func();
	$validUser 						= $initializationArray['validUser'];
	if ($validUser == 'N') {				// turn off debug and testmode
		$doDebug					= FALSE;
		$testMode					= FALSE;
	}
	$siteURL						= $initializationArray['siteurl'];
	$jobname						= "Process Advisor Verification";
	$userName						= "Not Available";

	if ($doDebug) {
		echo "Initialization Array:<br /><pre>";
		print_r($initializationArray);
		echo "</pre><br />";
	}

//	if ($doDebug) {
		ini_set('display_errors','1');
		error_reporting(E_ALL);	
//	}

/// get the time that the process started
	$startingMicroTime			= microtime(TRUE);

	$theid						= '';
	$id							= '';
	$action						= '';
	$strPass					= '1';
	$inp_time_zone				= '';
	$inp_verify					= '';
	$updateParams				= array();
	$updateFormat				= array();
	$xmode						= '';
	$validate					= '';
	$increment					= 0;
	$logDate					= date('Y-m-d H:i');
	$fieldTest					= array('action_log','post_status','post_title','control_code');
	$nextSemester				= $initializationArray['nextSemester'];
	$advisor_call_sign			= 'unknown';
	$theURL						= "$siteURL/cwa-process-advisor-verification/";

	

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
			if ($str_key 				== 'enstr') {
				$enstr					= $str_value;
				$stringToPass			= base64_decode($enstr);
				$myArray				= explode("&",$stringToPass);
				foreach($myArray as $myValue) {
					$thisArray			= explode("=",$myValue);
					${$thisArray[0]}	= $thisArray[1];
					if ($doDebug) {
						echo "set $thisArray[0] to $thisArray[1]<br />";
					}
				}
			}
		}
	}

	if ($validUser == "N" && $validate != 'valid') {
		return "YOU'RE NOT AUTHORIZED!<br />Goodby";
	}
	

	if ($xmode == 'tm') {
		$testMode	= TRUE;
		if ($doDebug) {
			echo "xmode is 'tm' so testMode is TRUE<br />";
		}
	} else {
		$testMode	= FALSE;
	}

	$actionDate					= date('dMY H:i');
	$doUpdate					= FALSE;



function sendBobEmail($advisor_last_name,$advisor_first_name,$advisor_call_sign,$advisor_time_zone,$action) {

	global $doDebug, $testMode, $increment;

	$content		= "";
	$mySubject		= "CW Academy - Advisor Verification Reply";
	if ($testMode) {
		$theRecipient	= 'rolandksmith@gmail.com';
		$mailCode	= 2;
		$mySubject	= "TESTMODE $mySubject";
		$increment++;
	} else {
		$theRecipient	= 'rolandksmith@gmail.com';
		$mailCode		= 17;
	}
	if ($action == 'Y') {
		$myContent		= "Advisor $advisor_last_name, $advisor_first_name, ($advisor_call_sign) has 
submitted his verification for his class(es) in the $advisor_time_zone time zone.<br /><br />Thanks!";
	} elseif ($action == 'R') {
		$myContent		= "Advisor $advisor_last_name, $advisor_first_name, ($advisor_call_sign) has 
requested to be removed.<br /><br />Thanks!";
		
	}
	$mailResult		= emailFromCWA_v2(array('theRecipient'=>$theRecipient,
												'theSubject'=>$mySubject,
												'jobname'=>"Process Advisor Verification",
												'theContent'=>$myContent,
												'mailCode'=>$mailCode,
												'increment'=>$increment,
												'testMode'=>$testMode,
												'doDebug'=>$doDebug));

	if ($mailResult === TRUE) {
		return "Process completed";
	} else {
		$content .= "<br />The final mail send function failed.</p>";
		return $content;
	}	
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

th {color:#ffff;background-color:#000;padding:5px;font-size:small;border-bottom:1px solid black;}

td {padding:5px;font-size:small;border-bottom:1px solid black;}

th:first-child,
td:first-child {
 padding-left: 10px;
}

th:last-child,
td:last-child {
	padding-right: 5px;
}
</style>";	

	if ("1" == $strPass) {

// verify that we have input information
		$theid							= $id;
		if ($theid == '' || $action == '') {
			$content					.= "<h3>Process Advisor Verification</h3><p>Invalid information provided.</p>";
		} else {
			if ($testMode) {
				$advisorTableName		= 'wpw1_cwa_consolidated_advisor2';
				$advisorClassTableName	= 'wpw1_cwa_consolidated_advisorclass2';
				if ($doDebug) {
					echo "Function is in TestMode.<br />";
				}
				$content 				.= "Function is in TestMode.<br />";
			} else {
				$advisorTableName		= 'wpw1_cwa_consolidated_advisor';
				$advisorClassTableName	= 'wpw1_cwa_consolidated_advisorclass';
			}

			$sql					= "select * from $advisorTableName 
										where advisor_id=$theid";
			$wpw1_cwa_advisor	= $wpdb->get_results($sql);
			if ($wpw1_cwa_advisor === FALSE) {
				if ($doDebug) {
					echo "Reading $advisorTableName table failed<br />";
					echo "wpdb->last_query: " . $wpdb->last_query . "<br />";
					echo "<b>wpdb->last_error: " . $wpdb->last_error . "</b><br />";
				}
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

						$userName							= $advisor_call_sign;

						// got a record, perform the requested action
						if ($action == 'Y') {
							if ($doDebug) {
								echo "Confirming the advisor's registration<br />";
							}
							$updateParams['verify_response']	= 'Y';
							$updateFormat[]						= '%s';
							$advisor_action_log				= "$advisor_action_log / $actionDate ADVERIFY Advisor confirmed the registration ";
							$myInt			=  strpos($nextSemester,"Jan/Feb");
							if ($myInt !== FALSE) {
								$myStr		= "December";
							}
							$myInt			=  strpos($nextSemester,"May/Jun");
							if ($myInt !== FALSE) {
								$myStr		= "April";
							}
							$myInt			=  strpos($nextSemester,"Sep/Oct");
							if ($myInt !== FALSE) {
								$myStr		= "August";
							}

							$content							.= "<h3>Confirming Advisor $advisor_call_sign Participation</h3>
<p>Your class registration has been confirmed. 
Student assignment to advisor classes will occur about the 10th of $myStr. You will receive an email 
with your student assignment information shortly after that.</p>
<p>Thank you for your willingness to serve as an advisor! You may close this window.</p>
<p>73,<br />CW Academy</p>";
							$theResult			= sendBobEmail($advisor_last_name,$advisor_first_name,$advisor_call_sign,$advisor_time_zone,$action);


						} elseif ($action == 'R') {
							/// Process R request
							if ($doDebug) {
								echo "Removing the advisor's registration<br />";
							}
							$content			.= "<h3>Confirming Advisor $advisor_call_sign Decision Not to Participate in the $nextSemester Semester</h3>
<p>Your class(es) have been removed.</p>
<p>If you did this inadvertently, please contact the appropriate person 
at <a href='https://cwops.org/cwa-class-resolution/' target='_blank'>Class Resolution</a>.</p>
<p>If you have any questions or concerns, please reach out 
to <a href='https://cwops.org/cwa-class-resolution/' target='_blank'>Bob Carter</a>.</p><p>You may close this window.</p>";

							$updateParams['verify_response']	= 'R';
							$updateFormat[]						= '%s';
							$advisor_action_log				= "$advisor_action_log / $actionDate ADVERIFY the advisor requested to be removed ";
						}
						$updateParams['action_log']			= $advisor_action_log;
						$updateFormat[]						= '%s';
						$advisorUpdateData		= array('tableName'=>$advisorTableName,
														'inp_method'=>'update',
														'inp_data'=>$updateParams,
														'inp_format'=>$updateFormat,
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
							$errorMsg	= "$jobname Processing $advisor_call_sign in $advisorTableName failed. Reason: $updateResult[1]<br />SQL: $mySql<br />Error: $myError<br />";
							if ($doDebug) {
								echo $errorMsg;
							}
							sendErrorEmail($errorMsg);
							$content		.= "Unable to update content in $advisorTableName<br />";
						} else {

							if ($doDebug) {
								echo "Successfully updated $advisor_call_sign record at $advisor_ID<br />";
							}
							if ($doDebug) {
								echo "Advisor record updated:<br /><pre>";
								print_r($updateParams);
								echo "</pre><br />";
							}
							$myInt			=  strpos($nextSemester,"Jan/Feb");
							if ($myInt !== FALSE) {
								$myStr		= "December";
							}
							$myInt			=  strpos($nextSemester,"May/Jun");
							if ($myInt !== FALSE) {
								$myStr		= "April";
							}
							$myInt			=  strpos($nextSemester,"Sep/Oct");
							if ($myInt !== FALSE) {
								$myStr		= "August";
							}
						}

						//// if the request was to remove, need to delete the class records
						if ($action == 'R') {
							$sql					= "select * from $advisorClassTableName 
														where advisor_call_sign='$advisor_call_sign' 
														and semester = '$advisor_semester'";
							$wpw1_cwa_advisorclass	= $wpdb->get_results($sql);
							if ($wpw1_cwa_advisorclass === FALSE) {
								if ($doDebug) {
									echo "Reading $advisorClassTableName table failed<br />";
									echo "wpdb->last_query: " . $wpdb->last_query . "<br />";
									echo "<b>wpdb->last_error: " . $wpdb->last_error . "</b><br />";
								}
							} else {
								$numACRows			= $wpdb->num_rows;
								if ($doDebug) {
									$myStr			= $wpdb->last_query;
									echo "ran $myStr<br />and obtained $numACRows from $advisorClassTableName table<br />";
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

										$classUpdateData		= array('tableName'=>$advisorClassTableName,
																		'inp_method'=>'delete',
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
											if ($doDebug) {
												echo "Successfully deleted $advisorClass_ID from $advisorClassTableName<br />";
											}
										}
									}
								} else {
									if ($doDebug) {
										echo "No $advisorClassTableName records for advisor $advisor_call_sign<br />";
									}
								}
							}
						}
					}
				} else {				// No record with that id found
					if ($doDebug) {
						echo "No record found in $advisorTableName for id $theID<br />";
					}
					$content			.= "Unknown record requested.";
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
	$result			= write_joblog_func("$jobname|$nowDate|$nowTime|$advisor_call_sign|Time|$thisStr|$strPass: $elapsedTime");
	if ($result == 'FAIL') {
		$content	.= "<p>writing to joblog.txt failed</p>";
	}
	return $content;
}
add_shortcode ('process_advisor_verification', 'process_advisor_verification_func');
