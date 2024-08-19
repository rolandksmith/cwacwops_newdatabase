function get_all_classes_for_student($inp_student='', $doDebug= FALSE, $testMode=FASE) {

/*
	input: student callsign
	
	Returns: array sequence[semester][level]['advisor']
											['class']
											['status']
											['promotable']
	if no information, array will be empty
	
	example:
	
	Array
	(
		[1] => Array
			(
				[2020 Jan/Feb] => Array
					(
						[Beginnier] => Array
							(
								[advisor] => AA8TA
								[class] => 1
								[status] => Y
								[promotable] => P
							)

					)

			)

		[2] => Array
			(
				[2020 May/Jun] => Array
					(
						[Fundamental] => Array
							(
								[advisor] => AA8TA
								[class] => 1
								[status] => C
								[promotable] => 
							)

					)

			)

		[3] => Array
			(
				[2020 May/Jun] => Array
					(
						[Fundamental] => Array
							(
								[advisor] => k7ojl
								[class] => 2
								[status] => Y
								[promotable] => N
							)

					)

			)

		[4] => Array
			(
				[2020 Sep/Oct] => Array
					(
						[Fundamental] => Array
							(
								[advisor] => AD7KG
								[class] => 1
								[status] => Y
								[promotable] => P
							)

					)

			)

	)

	foreach($studentArray as $thisSequence=>$thisValue1) {
		foreach($thisValue1 as $thisSemester=>$thisValue2) {
			foreach($thisValue2 as $thisLevel=>$thisValue3) {
				$advisor = $thisValue3['advisor'];
				$class = $thisValue3['class'];
				$status = $thisValue3['status'];
				$promotable = $thisValue3['promotable'];
				echo "Semester: $thisSemester\tlevel: $thisLevel\n";
				echo "\tAdvisor: $advisor\n";
				echo "\tClass: $class\n";
				echo "\tStatus: $status\n";
				echo "\tPromotable: $promotable\n\n";
			}
		}
	}
	Semester: 2020 Jan/Feb	level: Beginnier
		Advisor: AA8TA
		Class: 1
		Status: Y
		Promotable: P

	Semester: 2020 May/Jun	level: Fundamental
		Advisor: AA8TA
		Class: 1
		Status: C
		Promotable: 

	Semester: 2020 May/Jun	level: Fundamental
		Advisor: k7ojl
		Class: 2
		Status: Y
		Promotable: N

	Semester: 2020 Sep/Oct	level: Fundamental
		Advisor: AD7KG
		Class: 1
		Status: Y
		Promotable: P
		
*/

	global $wpdb;
	
	if ($doDebug) {
		echo "<br /><b>Function: Get All Classes for Student</b><br />";
	}
	if ($inp_student == '') {
		if ($doDebug) {
			echo "inp_student is an empty. Returning<br />";
		}
		return array();
	}
	
	// get all info from past_student
	if ($doDebug) {
		echo "going after data in past_student<br />";
	}
	
	$returnArray			= array();
	$thisSequence			= 0;
	
	$pastStudentTableName		= "wpw1_cwa_consolidated_student";
	if ($testMode) {
		$pastStudentTableName	= "wpw1_cwa_consolidated_student2";
	}
	$sql					= "select * from $pastStudentTableName
								where call_sign = '$inp_student' 
								and (student_status = 'Y' or student_status = 'S' or student_status = 'C') 
								order by date_created"; 
	$wpw1_cwa_past_student	= $wpdb->get_results($sql);
	if ($wpw1_cwa_past_student === FALSE) {
		$myError			= $wpdb->last_error;
		$myQuery			= $wpdb->last_query;
		if ($doDebug) {
			echo "Reading $pastStudentTableName table failed<br />
				  wpdb->last_query: $myQuery<br />
				  wpdb->last_error: $myError<br />";
		}
		$errorMsg			= "$jobname Reading $pastStudentTableName failed. <p>SQL: $myQuery</p><p> Error: $myError</p>";
		sendErrorEmail($errorMsg);
	} else {
		$numPSRows									= $wpdb->num_rows;
		if ($doDebug) {
			$myStr			= $wpdb->last_query;
			echo "ran $myStr<br />and found $numPSRows rows in $pastStudentTableName table<br />";
		}
		if ($numPSRows > 0) {
			foreach ($wpw1_cwa_past_student as $past_studentRow) {
				$past_student_ID							= $past_studentRow->student_id;
				$past_student_call_sign						= strtoupper($past_studentRow->call_sign);
				$past_student_first_name					= $past_studentRow->first_name;
				$past_student_last_name						= stripslashes($past_studentRow->last_name);
				$past_student_email  						= strtolower(strtolower($past_studentRow->email));
				$past_student_ph_code						= $past_studentRow->ph_code;
				$past_student_phone  						= $past_studentRow->phone;
				$past_student_city  						= $past_studentRow->city;
				$past_student_state  						= $past_studentRow->state;
				$past_student_zip_code  					= $past_studentRow->zip_code;
				$past_student_country_code					= $past_studentRow->country_code;
				$past_student_country  						= $past_studentRow->country;
				$past_student_time_zone  					= $past_studentRow->time_zone;
				$past_student_timezone_id					= $past_studentRow->timezone_id;
				$past_student_timezone_offset				= $past_studentRow->timezone_offset;
				$past_student_whatsapp						= $past_studentRow->whatsapp_app;
				$past_student_signal						= $past_studentRow->signal_app;
				$past_student_telegram						= $past_studentRow->telegram_app;
				$past_student_messenger						= $past_studentRow->messenger_app;					
				$past_student_wpm 	 						= $past_studentRow->wpm;
				$past_student_youth  						= $past_studentRow->youth;
				$past_student_age  							= $past_studentRow->age;
				$past_student_student_parent 				= $past_studentRow->student_parent;
				$past_student_student_parent_email  		= strtolower($past_studentRow->student_parent_email);
				$past_student_level  						= $past_studentRow->level;
				$past_student_waiting_list 					= $past_studentRow->waiting_list;
				$past_student_request_date  				= $past_studentRow->request_date;
				$past_student_semester						= $past_studentRow->semester;
				$past_student_notes  						= $past_studentRow->notes;
				$past_student_welcome_date  				= $past_studentRow->welcome_date;
				$past_student_email_sent_date  				= $past_studentRow->email_sent_date;
				$past_student_email_number  				= $past_studentRow->email_number;
				$past_student_response  					= strtoupper($past_studentRow->response);
				$past_student_response_date  				= $past_studentRow->response_date;
				$past_student_abandoned  					= $past_studentRow->abandoned;
				$past_student_student_status  				= $past_studentRow->student_status;
				$past_student_action_log  					= $past_studentRow->action_log;
				$past_student_pre_assigned_advisor  		= $past_studentRow->pre_assigned_advisor;
				$past_student_selected_date  				= $past_studentRow->selected_date;
				$past_student_no_catalog		  			= $past_studentRow->no_catalog;
				$past_student_hold_override  				= $past_studentRow->hold_override;
				$past_student_messaging  					= $past_studentRow->messaging;
				$past_student_assigned_advisor  			= $past_studentRow->assigned_advisor;
				$past_student_advisor_select_date  			= $past_studentRow->advisor_select_date;
				$past_student_advisor_class_timezone 		= $past_studentRow->advisor_class_timezone;
				$past_student_hold_reason_code  			= $past_studentRow->hold_reason_code;
				$past_student_class_priority  				= $past_studentRow->class_priority;
				$past_student_assigned_advisor_class 		= $past_studentRow->assigned_advisor_class;
				$past_student_promotable  					= $past_studentRow->promotable;
				$past_student_excluded_advisor  			= $past_studentRow->excluded_advisor;
				$past_student_student_survey_completion_date = $past_studentRow->student_survey_completion_date;
				$past_student_available_class_days  		= $past_studentRow->available_class_days;
				$past_student_intervention_required  		= $past_studentRow->intervention_required;
				$past_student_copy_control  				= $past_studentRow->copy_control;
				$past_student_first_class_choice  			= $past_studentRow->first_class_choice;
				$past_student_second_class_choice  			= $past_studentRow->second_class_choice;
				$past_student_third_class_choice  			= $past_studentRow->third_class_choice;
				$past_student_first_class_choice_utc  		= $past_studentRow->first_class_choice_utc;
				$past_student_second_class_choice_utc  		= $past_studentRow->second_class_choice_utc;
				$past_student_third_class_choice_utc  		= $past_studentRow->third_class_choice_utc;
				$past_student_date_created 					= $past_studentRow->date_created;
				$past_student_date_updated			  		= $past_studentRow->date_updated;
				
				$thisSequence++;
				$returnArray[$thisSequence][$past_student_semester][$past_student_level]['advisor']		= $past_student_assigned_advisor;
				$returnArray[$thisSequence][$past_student_semester][$past_student_level]['class']		= $past_student_assigned_advisor_class;
				$returnArray[$thisSequence][$past_student_semester][$past_student_level]['status']		= $past_student_student_status;
				$returnArray[$thisSequence][$past_student_semester][$past_student_level]['promotable']	= $past_student_promotable;
			}
		}
	}
	
/*	
	$studentTableName		= "wpw1_cwa_consolidated_student";
	if ($testMode) {
		$studentTableName	= "wpw1_cwa_consolidated_student2";
	}
	$sql					= "select * from $studentTableName
								where call_sign = '$inp_student' 
								and (student_status = 'Y' or student_status = 'S' or student_status = 'C') 
								order by date_created"; 
	$wpw1_cwa_student		= $wpdb->get_results($sql);
	if ($wpw1_cwa_student === FALSE) {
		$myError			= $wpdb->last_error;
		$myQuery			= $wpdb->last_query;
		if ($doDebug) {
			echo "Reading $studentTableName table failed<br />
				  wpdb->last_query: $myQuery<br />
				  wpdb->last_error: $myError<br />";
		}
		$errorMsg			= "$jobname reading $studentTableName failed. <p>SQL: $myQuery</p><p> Error: $myError</p>";
		sendErrorEmail($errorMsg);
		$content		.= "Unable to obtain content from $studentTableName<br />";
	} else {
		$numSRows			= $wpdb->num_rows;
		if ($doDebug) {
			$myStr			= $wpdb->last_query;
			echo "ran $myStr<br />and found $numSRows rows<br />";
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
				$student_abandoned  					= $studentRow->abandoned;
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

				$thisSequence++;
				$returnArray[$thisSequence][$student_semester][$student_level]['advisor']		= $student_assigned_advisor;
				$returnArray[$thisSequence][$student_semester][$student_level]['class']			= $student_assigned_advisor_class;
				$returnArray[$thisSequence][$student_semester][$student_level]['status']		= $student_student_status;
				$returnArray[$thisSequence][$student_semester][$student_level]['promotable']	= $student_promotable;
			}
		}
	}
*/
	if ($doDebug) {
		echo "finished. returnArray:<br /><pre>";
		print_r($returnArray);
		echo "</pre><br />";
	}
	return $returnArray;
}
add_action('get_all_classes_for_student', 'get_all_classes_for_student');