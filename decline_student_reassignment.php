function decline_student_reassignment_func() {

/*	function for a reassigned student to decline the assignment (incoming info only)

*/

	global $wpdb;

/// get the time that the process started
	$startingMicroTime			= microtime(TRUE);

	$myDate 			= date('dMy Hi') . 'z';
	$doDebug			= FALSE;
	$testMode			= FALSE;
	$context = CWA_Context::getInstance();
	$validUser 			= $context->validUser;
	$userName			= $context->userName;
	if ($userName == '') {				// turn off debug and testmode
		$doDebug					= FALSE;
		$testMode					= FALSE;
	}
	$siteURL			= $context->siteurl;
	$currentDate		= $context->currentDate;
	$currentSemester		= $context->currentSemester;
	$previousSemester		= $context->prevSemester;
	if ($currentSemester == 'Not in Session') {
		$currentSemester	= $previousSemester;
	}
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

	if (! isset($program_action)) {
		$program_action	= '';
	}	
	if ($program_action != 'valid') {
		return "You're not authorized. Goodbye";
	}
	
	if ($inp_mode == 'tm') {
		$testMode				= TRUE;
	}
	
	if ($testMode) {
		$studentTableName		= 'wpw1_cwa_student2';
		$advisorTableName		= 'wpw1_cwa_advisor2';
		$userMasterTableName	= 'wpw1_cwa_user_master2';
		if ($doDebug) {
			echo "Function is under development. Using student2 and advisor2, not the production data.<br />";
		}
		$content .= "<p>Function is under development. Using student2 and advisor2, not the production data.</p>";
	} else {
		$studentTableName 		= 'wpw1_cwa_student';
		$advisorTableName		= 'wpw1_cwa_advisor';
		$userMasterTableName	= 'wpw1_cwa_user_master';
	}

	if ($doDebug) {
		echo "Came in with studentid = $studentid<br />";
	}
	if ($studentid == "") {
		return "<p>Invalid or incomplete information provided. No action taken.</p>";
	}
	
	$sql					= "select * from $studentTableName 
								left join $userMasterTableName on user_call_sign = student_call_sign 
								where student_id=$studentid";
	$wpw1_cwa_student	= $wpdb->get_results($sql);
	if ($wpw1_cwa_student === FALSE) {
		handleWPDBError($jobname,$doDebug);
	} else {
		$numSRows									= $wpdb->num_rows;
		if ($doDebug) {
			echo "ran $sql<br />and retrieved $numSRows rows from $studentTableName table<br >";
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
				$student_student_parent 				= $studentRow->student_parent;
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
				$student_student_survey_completion_date	= $studentRow->student_survey_completion_date;
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

				if ($doDebug) {
					echo "Read the student table for $studentid and got a id of $student_ID and a call sign of $student_call_sign<br />";
				}
				$my_action_log 		= "$student_action_log / $myDate DECLINE student declined the move to next semester";
				$updateParams		= array('student_class_priority'=>0,
											'student_action_log'=>$my_action_log,
											'student_response'=>'R');
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
add_shortcode ('decline_student_reassignment', 'decline_student_reassignment_func');
