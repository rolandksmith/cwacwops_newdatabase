function decline_student_reassignment_func() {

/*	function for a reassigned student to decline the assignment (incoming info only)

	modified 4Mar21 by Roland to change Joe Fisher's email address
	Modified 22Oct22 by Roland for new timezone table format
	Modified 15Apr23 by Roland for action_log
	Modified 12Jul23 by Roland to use consolidated tables
	Modified 31Aug23 by Roland to turn off dodebug and testmode if validUser is N
*/

	global $wpdb;

/// get the time that the process started
	$startingMicroTime			= microtime(TRUE);

	$myDate 			= date('dMy Hi') . 'z';
	$doDebug			= FALSE;
	$testMode			= FALSE;
	$initializationArray = data_initialization_func();
	$validUser 						= $initializationArray['validUser'];
	if ($validUser == 'N') {				// turn off debug and testmode
		$doDebug					= FALSE;
		$testMode					= FALSE;
	}
	$siteURL			= $initializationArray['siteurl'];
	$currentDate		= $initializationArray['currentDate'];
	$my_id				= "";
	$my_message			= "";
	$studentid			= "";
	$programAction		= '';
	$content			= "";
	$increment  		= 0;
	$jobname			= 'Decline Student Reassignment';
	$myCount			= 0;
	$fieldTest			= array('action_log','control_code');
	$inp_mode			= '';
	
// get the input information
	if (isset($_REQUEST)) {
		foreach($_REQUEST as $str_key => $str_value) {
			if ($doDebug) {
				echo "Key: $str_key | Value: $str_value <br />\n";
			}
			if ($str_key 				== 'enstr') {
				$enstr					= $str_value;
				$stringToPass			= base64_decode($enstr);
				$myArray				= explode("&",$stringToPass);
				foreach($myArray as $myValue) {
					$thisArray			= explode("=",$myValue);
					${$thisArray[0]}	= $thisArray[1];
					if ($doDebug) {
						echo "$thisArray[0] | $thisArray[1]<br />";
					}
				}
			}
			if ($str_key 		== "inp_mode") {
				$inp_mode		 = $str_value;
				$inp_mode		= filter_var($inp_mode,FILTER_UNSAFE_RAW);
				if ($inp_mode == 'tm') {
					$testMode	= TRUE;
				}
			}
		}
	}

	if ($program_action != 'valid') {
		return "You're not authorized. Goodbye";
	}
	
	if ($inp_mode == 'tm') {
		$testMode				= TRUE;
	}
	
	if ($testMode) {
		$studentTableName	= 'wpw1_cwa_consolidated_student2';
		$advisorTableName	= 'wpw1_cwa_consolidated_advisor2';
		if ($doDebug) {
			echo "Function is under development. Using student2 and advisor2, not the production data.<br />";
		}
		$content .= "<p>Function is under development. Using student2 and advisor2, not the production data.</p>";
	} else {
		$studentTableName 	= 'wpw1_cwa_consolidated_student';
		$advisorTableName	= 'wpw1_cwa_consolidated_advisor';
	}

	if ($doDebug) {
		echo "Came in with studentid = $studentid<br />";
	}
	if ($studentid == "") {
		return "<p>Invalid or incomplete information provided. No action taken.</p>";
	}
	$currentSemester		= $initializationArray['currentSemester'];
	$previousSemester		= $initializationArray['prevSemester'];
	if ($currentSemester == 'Not in Session') {
		$currentSemester	= $previousSemester;
	}
	
	$sql					= "select * from $studentTableName 
								where student_id=$studentid";
	$wpw1_cwa_student		= $wpdb->get_results($sql);
	if ($wpw1_cwa_student === FALSE) {
		if ($doDebug) {
			echo "Reading $studentTableName table failed<br />";
			echo "wpdb->last_query: " . $wpdb->last_query . "<br />";
			echo "<b>wpdb->last_error: " . $wpdb->last_error . "</b><br />";
		}
	} else {
		$numSRows			= $wpdb->num_rows;
		if ($doDebug) {
			$myStr			= $wpdb->last_query;
			echo "ran $myStr<br />and retrieved $numSRows rows from $studentTableName table<br />";
		}
		if ($numSRows > 0) {
			foreach ($wpw1_cwa_student as $studentRow) {
				$student_ID								= $studentRow->student_id;
				$student_call_sign						= strtoupper($studentRow->call_sign);
				$student_first_name						= $studentRow->first_name;
				$student_last_name						= stripslashes($studentRow->last_name);
				$student_email  						= strtolower(strtolower($studentRow->email));
				$student_phone  						= $studentRow->phone;
				$student_ph_code						= $studentRow->ph_code;
				$student_city  							= $studentRow->city;
				$student_state  						= $studentRow->state;
				$student_zip_code  						= $studentRow->zip_code;
				$student_country  						= $studentRow->country;
				$student_country_code					= $studentRow->country_code;
				$student_time_zone  					= $studentRow->time_zone;
				$student_timezone_id					= $studentRow->timezone_id;
				$student_timezone_offset				= $studentRow->timezone_offset;
				$student_whatsapp						= $studentRow->whatsapp_app;
				$student_signal							= $studentRow->signal_app;
				$student_telegram						= $studentRow->telegram_app;
				$student_messenger						= $studentRow->messenger_app;					
				$student_wpm 	 						= $studentRow->wpm;
				$student_youth  						= $studentRow->youth;
				$student_age  							= $studentRow->age;
				$student_student_parent 				= $studentRow->student_parent;
				$student_student_parent_email  			= strtolower($studentRow->student_parent_email);
				$student_level  						= $studentRow->level;
				$student_waiting_list 					= $studentRow->waiting_list;
				$student_request_date  					= $studentRow->request_date;
				$student_semester						= $studentRow->semester;
				$student_notes  						= $studentRow->notes;
				$student_welcome_date  					= $studentRow->welcome_date;
				$student_email_sent_date  				= $studentRow->email_sent_date;
				$student_email_number  					= $studentRow->email_number;
				$student_response  						= strtoupper($studentRow->response);
				$student_response_date  				= $studentRow->response_date;
				$student_abandoned  				= $studentRow->abandoned;
				$student_student_status  				= strtoupper($studentRow->student_status);
				$student_action_log  					= $studentRow->action_log;
				$student_pre_assigned_advisor  			= $studentRow->pre_assigned_advisor;
				$student_selected_date  				= $studentRow->selected_date;
				$student_no_catalog			 			= $studentRow->no_catalog;
				$student_hold_override  				= $studentRow->hold_override;
				$student_messaging  					= $studentRow->messaging;
				$student_assigned_advisor  				= $studentRow->assigned_advisor;
				$student_advisor_select_date  			= $studentRow->advisor_select_date;
				$student_advisor_class_timezone 		= $studentRow->advisor_class_timezone;
				$student_hold_reason_code  				= $studentRow->hold_reason_code;
				$student_class_priority  				= $studentRow->class_priority;
				$student_assigned_advisor_class 		= $studentRow->assigned_advisor_class;
				$student_promotable  					= $studentRow->promotable;
				$student_excluded_advisor  				= $studentRow->excluded_advisor;
				$student_student_survey_completion_date	= $studentRow->student_survey_completion_date;
				$student_available_class_days  			= $studentRow->available_class_days;
				$student_intervention_required  		= $studentRow->intervention_required;
				$student_copy_control  					= $studentRow->copy_control;
				$student_first_class_choice  			= $studentRow->first_class_choice;
				$student_second_class_choice  			= $studentRow->second_class_choice;
				$student_third_class_choice  			= $studentRow->third_class_choice;
				$student_first_class_choice_utc  		= $studentRow->first_class_choice_utc;
				$student_second_class_choice_utc  		= $studentRow->second_class_choice_utc;
				$student_third_class_choice_utc  		= $studentRow->third_class_choice_utc;
				$student_date_created 					= $studentRow->date_created;
				$student_date_updated			  		= $studentRow->date_updated;

				$student_last_name 						= no_magic_quotes($student_last_name);

				if ($doDebug) {
					echo "Read the student table for $studentid and got a id of $student_ID and a call sign of $student_call_sign<br />";
				}
				$my_action_log 		= "$student_action_log / $myDate DECLINE student declined the move to next semester";
				$updateParams		= array('class_priority'=>0,
											'action_log'=>$my_action_log,
											'response'=>'R');
				$updateFormat		= array('%d',
											'%s',
											'%s');
				if ($doDebug) {
					echo "updateParams:<br /><pre>";
					print_r($updateParams);
					echo "</pre><br />";
				}
				$studentUpdateData		= array('tableName'=>$studentTableName,
												'inp_data'=>$updateParams,
												'inp_format'=>$updateFormat,
												'inp_method'=>'update',
												'jobname'=>$jobname,
												'inp_id'=>$student_ID,
												'inp_callsign'=>$student_call_sign,
												'inp_semester'=>$student_semester,
												'inp_who'=>$student_call_sign,
												'testMode'=>$testMode,
												'doDebug'=>$doDebug);
				$updateResult	= updateStudent($studentUpdateData);
				if ($updateResult[0] === FALSE) {
					$myError	= $wpdb->last_error;
					$mySql		= $wpdb->last_query;
					$errorMsg	= "$jobname Processing $student_call_sign in $studentTableName failed. Reason: $updateResult[1]<br />SQL: $mySql<br />Error: $myError<br />";
					if ($doDebug) {
						echo $errorMsg;
					}
					sendErrorEmail($errorMsg);
					$content		.= "Unable to update content in $studentTableName<br />";
				} else {
					$content .= "<h3>Decline Semester Reassignment</h3>
<p>Thank you for your interest in the CW Academy. Your registration has been 
removed. We hope to see you in a future class! To register for a future class, please go 
to <a href='$siteURL/cwa-student-registration/' 
target='_blank' rel='noopener noreferrer'>CW Academy Student Signup</a> to register.</p>
<p>73,<br />CW Academy</p>";

				}
			}
		} else {
			if ($doDebug) {
				echo "Got $numSRows records ... should be one only<br />";
			}
			$theRecipient	= 'rolandksmith@gmail.com';
			$mailCode		= 1;
			$errorMessage	= "<p>An attempt to decline reassignment for $studentid inputid yielded $numSRows from the $studentPodName pod.</p>";
			sendErrorEmail($errorMessage);
		}
	}
	$thisTime 		= date('Y-m-d H:i:s');
	$content		.= "<p>Report displayed at $thisTime.</p>";
	$endingMicroTime = microtime(TRUE);
	$elapsedTime	= $endingMicroTime - $startingMicroTime;
	$elapsedTime	= number_format($elapsedTime, 4, '.', ',');
	$content		.= "<p>Report pass 1 took $elapsedTime seconds to run</p>";
	$nowDate		= date('Y-m-d');
	$nowTime		= date('H:i:s');
	$thisStr			= 'Production';
	if ($testMode) {
		$thisStr		= 'Testmode';
	}
	$result			= write_joblog_func("Decline Student Reassignment|$nowDate|$nowTime|$student_call_sign|Time|$thisStr|0: $elapsedTime");
	if ($result == 'FAIL') {
		$content	.= "<p>writing to joblog.txt failed</p>";
	}
	return $content;	
}	
add_shortcode ('decline_student_reassignment', 'decline_student_reassignment_func');
