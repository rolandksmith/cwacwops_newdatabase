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
 	Modified 24Oct24 by Roland for new database
*/

	global $wpdb, $doDebug, $testMode;

	$doDebug						= FALSE;
	$testMode						= FALSE;
	$initializationArray 			= data_initialization_func();
	$validUser 						= $initializationArray['validUser'];
	$userName						= $initializationArray['userName'];

	if ($userName == '') {
		return "You are not authorized";
	}
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
	$jobname					= "Process Advisor Verification";

	

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

	
	$content = "";	

	if ("1" == $strPass) {

// verify that we have input information
		$theid							= $id;
		if ($theid == '' || $action == '') {
			$content					.= "<h3>$jobname</h3><p>Invalid information provided.</p>";
		} else {
			if ($testMode) {
				$advisorTableName		= 'wpw1_cwa_advisor2';
				$advisorClassTableName	= 'wpw1_cwa_advisorclass2';
				$userMasterTableName	= 'wpw1_cwa_user_master2';
				if ($doDebug) {
					echo "Function is in TestMode.<br />";
				}
				$content 				.= "Function is in TestMode.<br />";
			} else {
				$advisorTableName		= 'wpw1_cwa_advisor';
				$advisorClassTableName	= 'wpw1_cwa_advisorclass';
				$userMasterTableName	= 'wpw1_cwa_user_master';
			}

			$sql					= "select * from $advisorTableName 
										left join $userMasterTableName on user_call_sign = advisor_call_sign 
										where advisor_id=$theid";
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

						$userName							= $advisor_call_sign;

						// got a record, perform the requested action
						if ($action == 'Y') {
							if ($doDebug) {
								echo "Confirming the advisor's registration<br />";
							}
							$updateParams['advisor_verify_response']	= 'Y';
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

							$updateParams['advisor_verify_response']	= 'R';
							$updateFormat[]						= '%s';
							$advisor_action_log				= "$advisor_action_log / $actionDate ADVERIFY the advisor requested to be removed ";
						}
						$updateParams['advisor_action_log']			= $advisor_action_log;
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
							$deleteResult		= $wpdb->delete($advisorClassTableName, 
															array(advisorclass_call_sign=>'$advisor_call_sign', 
																  advisorclass_semeser=>'$advisor_semester').
															array('%s','%s'));
															
							if ($deleteResult === FALSE) {
								handleWPDBError($jobname,$doDebug);
							}
							if ($deleteResult > 0) {
								if ($doDebug) {
									echo "deleted $deleteResult rows<br />";
								}
								$content		.= "$deleteResult Class records deleted";
							} else {
								$myStr			= $wpdb->last_query;
								if ($doDebug) {
									echo "ran $myStr<br />and no class records found to delete.";
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

