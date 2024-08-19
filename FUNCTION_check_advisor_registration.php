function check_advisor_registration($inp_call_sign = '',$doDebug=FALSE,$testMode=FALSE) {

/*	Looks in the current advisor table and returns information about an advisor's registration
	
	Input: call sign
	
	Returns		array['advisor']['Semester']						Semester the class is being offered
					 ['advisor']['first_name']						Advisor first name
					 ['advisor']['last_name']						Advisor last name
					 ['advisor']['email']							Advisor Email
					 ['advisor']['phone']							Advisor Phone
					 ['advisor']['survey_score']					Advisor Survey Schore
					 ['advisor']['classes']							Number of classes the advisor has registered for
					 ['class'][*sequence*]['level']					Class level
					 ['class'][*sequence*]['size']					Class size
					 ['class'][*sequence*]['students']				Number of students assigned to the class
					 ['class'][*sequence*]['schedule_days']			Scheduled days local
					 ['class'][*sequence*]['schedule_times']		Scheduled times local
					 ['class'][*sequence*]['schedule_days_utc']		Schedule days UtC
					 ['class'][*sequence*]['schedule_times_utc']	Schedule times UTC
					 ['class'][*sequence*]['complete']				Class structure ok (Y or N)
					 
					 
	Modified 13Jul23 by Roland to use consolidated tables

*/

	global $wpdb;

	if ($doDebug) {
		echo "<br />Entering Function check_advisor_registration with parameter $inp_call_sign<br />";
	}
	if ($doDebug) {
		ini_set('display_errors','1');
		error_reporting(E_ALL);	
	}

//	$doDebug										= TRUE;
//	$testMode										= FALSE;
	$initializationArray 							= data_initialization_func();
	$currentSemester								= $initializationArray['currentSemester'];
	$prevSemester									= $initializationArray['nextSemester'];
	$proximateSemester								= $initializationArray['proximateSemester'];
	$returnArray['advisor']['Semester']						= '';
	$returnArray['advisor']['first_name']					= '';
	$returnArray['advisor']['last_name']					= '';
	$returnArray['advisor']['email']						= '';
	$returnArray['advisor']['phone']						= '';
	$returnArray['advisor']['survey_score']					= '';
	$returnArray['advisor']['classes']						= '';
//	$returnArray['class'][*sequence*]['level']				Class level
//	$returnArray['class'][*sequence*]['size']				Class size
//	$returnArray['class'][*sequence*]['students']			Number of students assigned to the class
//	$returnArray['class'][*sequence*]['schedule_days']		Scheduled days local
//	$returnArray['class'][*sequence*]['schedule_times']		Scheduled times local
//	$returnArray['class'][*sequence*]['schedule_days_utc']	Schedule days UtC
//	$returnArray['class'][*sequence*]['schedule_times_utc']	= '';
//	$returnArray['class'][*sequence*]['complete']			Class structure ok (Y or N)


	if ($testMode) {
		$advisorTableName			= "wpw1_cwa_consolidated_adviisor2";
		$advisorClassTableName		= "wpw1_cwa_consolidated_advisorclass2";
		$studentTableName			= "wpw1_cwa_consolidated_student2";
		if ($doDebug) {
			echo "Operating in testMode<br />";
		}
	} else {
		$advisorTableName			= "wpw1_cwa_consolidated_adviisor";
		$advisorClassTableName		= "wpw1_cwa_consolidated_advisorclass";
		$studentTableName			= "wpw1_cwa_consolidated_student";
		if ($doDebug) {
			echo "Operating in Production mode<br />";
		}
	}

	// get the advisor info
	
	$sql				= "select call_sign, 
								  semester, 
								  first_name, 
								  last_name, 
								  email, 
								  phone, 
								  survey_score 
							from $advisorTableName 
							where call_sign='$inp_call_sign' 
							order by date_created DESC 
							limit 1";
	$wpw1_cwa_adviisor	= $wpdb->get_results($sql);
	if ($wpw1_cwa_adviisor === FALSE) {
		if ($doDebug) {
			echo "Reading $advisorTableName table failed<br />";
			echo "wpdb->last_query: " . $wpdb->last_query . "<br />";
			echo "<b>wpdb->last_error: " . $wpdb->last_error . "</b><br />";
		}
		$returnArray['advisor']['Semester']		= "No Record";
	} else {
		$numARows			= $wpdb->num_rows;
		if ($doDebug) {
			$myStr			= $wpdb->last_query;
			echo "ran $myStr<br />and found $numARows rows in $advisorTableName table<br />";
		}
		if ($numARows > 0) {
			foreach ($wpw1_cwa_adviisor as $advisorRow) {
				$advisor_call_sign 					= strtoupper($advisorRow->call_sign);
				$advisor_first_name 				= $advisorRow->first_name;
				$advisor_last_name 					= stripslashes($advisorRow->last_name);
				$advisor_email 						= $advisorRow->email;
				$advisor_phone						= $advisorRow->phone;
				$advisor_semester 					= $advisorRow->semester;
				$advisor_survey_score 				= $advisorRow->survey_score;

				$returnArray['advisor']['Semester']		= $advisor_semester;
				$returnArray['advisor']['first_name']	= $advisor_first_name;
				$returnArray['advisor']['last_name']	= $advisor_last_name;
				$returnArray['advisor']['email']		= $advisor_email;
				$returnArray['advisor']['phone']		= $advisor_phone;
				$returnArray['advisor']['survey_score']	= $advisor_survey_score;

				if ($doDebug) {
					echo "loaded up advisor data for $inp_call_sign. Getting class info<br />";
				}
				
				// get the classes for the advisor
				$sql				= "select advisor_call_sign, 
										      sequence, 
										      semester, 
										      level, 
											  class_size, 
											  class_schedule_days, 
											  class_schedule_times, 
											  class_schedule_days_utc, 
											  class_schedule_times_utc, 
											  class_incomplete 
										from $advisorClassTableName 
										where advisor_call_sign = '$inp_call_sign' 
										and semester = '$advisor_semester' 
										order by sequence";
				$wpw1_cwa_advisorclass				= $wpdb->get_results($sql);
				if ($wpw1_cwa_advisorclass === FALSE) {
					if ($doDebug) {
						echo "Reading $advisorClassTableName table failed<br />";
						echo "wpdb->last_query: " . $wpdb->last_query . "<br />";
						echo "wpdb->last_error: " . $wpdb->last_error . "<br />";
					}
				} else {
					$numACRows						= $wpdb->num_rows;
					if ($doDebug) {
						$myStr						= $wpdb->last_query;
						echo "ran $myStr<br />and found $numACRows rows<br />";
					}
					if ($numACRows > 0) {
						foreach ($wpw1_cwa_advisorclass as $advisorClassRow) {
							$advisorClass_advisor_callsign 								= $advisorClassRow->advisor_call_sign;
							$advisorClass_sequence 										= $advisorClassRow->sequence;
							$advisorClass_semester 										= $advisorClassRow->semester;
							$advisorClass_level 										= $advisorClassRow->level;
							$advisorClass_class_size 									= $advisorClassRow->class_size;
							$advisorClass_class_schedule_days 							= $advisorClassRow->class_schedule_days;
							$advisorClass_class_schedule_times 							= $advisorClassRow->class_schedule_times;
							$advisorClass_class_schedule_days_utc 						= $advisorClassRow->class_schedule_days_utc;
							$advisorClass_class_schedule_times_utc 						= $advisorClassRow->class_schedule_times_utc;

							$returnArray['class'][$advisorClass_sequence]['level']					= $advisorClass_level;
							$returnArray['class'][$advisorClass_sequence]['size']					= $advisorClass_class_size;
//							$returnArray['class'][$advisorClass_sequence]['students']				= ;
							$returnArray['class'][$advisorClass_sequence]['schedule_days']			= $advisorClass_class_schedule_times;
							$returnArray['class'][$advisorClass_sequence]['schedule_times']			= $advisorClass_class_schedule_times;
							$returnArray['class'][$advisorClass_sequence]['schedule_days_utc']		= $advisorClass_class_schedule_days_utc;
							$returnArray['class'][$advisorClass_sequence]['schedule_times_utc']		= $advisorClass_class_schedule_times_utc;

							//// have a class. How many students assigned?
							$sql			= "select count(student_id) as student_count from $studentTableName 
												where semester='$advisorClass_semester' 
												and assigned_advisor='$advisorClass_advisor_callsign' 
												and assigned_advisor_class=$advisorClass_sequence 
												and (student_status = 'Y' or student_status = 'S'";
							$returnArray['class'][$advisorClass_sequence]['students']	= $wpdb->get_var($sql);
						}
					}
				
				}
			}
		} else {
			$returnArray['advisor']['Semester']		= "No Record";
		}
	}

	
	return $returnArray;
}
add_action('check_advisor_registration','check_advisor_registration');
