function get_all_classes_for_student($inp_student='', $doDebug= FALSE, $testMode=FASE) {

/*
	input: student callsign
	
	Returns: array sequence[semester][level]['advisor']
											['class']
											['status']
											['promotable']
	if no information, array will be empty
	
	example:

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
	
	$studentTableName		= "wpw1_cwa_student";
	if ($testMode) {
		$studentTableName	= "wpw1_cwa_student2";
	}
	$sql					= "select * from $studentTableName
								where call_sign = '$inp_student' 
								and (student_status = 'Y' or student_status = 'S' or student_status = 'C') 
								order by date_created"; 
	$wpw1_cwa_student		= $wpdb->get_results($sql);
	if ($wpw1_cwa_student === FALSE) {
		handleWPDBError($jobname,$doDebug);
		$content		.= "Unable to obtain content from $studentTableName<br />";
	} else {
		$numSRows			= $wpdb->num_rows;
		if ($doDebug) {
			echo "ran $sql<br />and found $numSRows rows<br />";
		}
		if ($numSRows > 0) {
			foreach ($wpw1_cwa_student as $studentRow) {
				$student_ID								= $studentRow->student_id;
				$student_call_sign						= $studentRow->call_sign;
				$student_time_zone  					= $studentRow->time_zone;
				$student_timezone_offset				= $studentRow->timezone_offset;
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
				$student_status  				= strtoupper($studentRow->student_status);
				$student_action_log  					= $studentRow->action_log;
				$student_pre_assigned_advisor  			= $studentRow->pre_assigned_advisor;
				$student_selected_date  				= $studentRow->selected_date;
				$student_no_catalog  					= $studentRow->no_catalog;
				$student_hold_override  				= $studentRow->hold_override;
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
				$student_catalog_options				= $studentRow->catalog_options;
				$student_flexible						= $studentRow->flexible;
				$student_date_created 					= $studentRow->date_created;
				$student_date_updated			  		= $studentRow->date_updated;


				$thisSequence++;
				$returnArray[$thisSequence][$student_semester][$student_level]['advisor']		= $student_assigned_advisor;
				$returnArray[$thisSequence][$student_semester][$student_level]['class']			= $student_assigned_advisor_class;
				$returnArray[$thisSequence][$student_semester][$student_level]['status']		= $student_status;
				$returnArray[$thisSequence][$student_semester][$student_level]['promotable']	= $student_promotable;
			}
		}
	}

	if ($doDebug) {
		echo "finished. returnArray:<br /><pre>";
		print_r($returnArray);
		echo "</pre><br />";
	}
	return $returnArray;
}
add_action('get_all_classes_for_student', 'get_all_classes_for_student');