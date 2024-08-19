function build_list_of_available_classes($semester='',$testMode=FALSE, $doDebug=FALSE) {

/*	Reads the advisor and advisorclass tables and builds a list of 
	classes with available seats
	
	If an advisor has indicated no additional replacement students, the advisor 
	is not incuded in the list. Also, advisors with a survey score of 6 or 13 and 
	advisors with a verify_response of R are not included
	
	Returns an array
		advisorclass_level[advisorclass_class_schedule_times_utc advisorclass_class_schedule_days_utc][advisorclass_advisor_call_sign] 	= advisorclass_sequence|advisorclass_class_schedule_times advisorclass_class_schedule_days|advisorclass_class_size|class_number_students
	
	For Example:
	Array ('Fundamental' => Array ('1700 Tuesday,Friday') => Array ('K7OJL') = '1|1130 Tuesday,Friday|9|8';

*/

	global $wpdb;
// $doDebug = TRUE;
	if ($doDebug) {
		echo "<br /><b>FUNCTION: Build List of Available Classes</b><br />";
	}

	$initializationArray		= data_initialization_func();
	$currentSemester			= $initializationArray['currentSemester'];
	$nextSemester				= $initializationArray['nextSemester'];
	
	if ($semester == '') {
		$theSemester				= $currentSemester;
		if ($theSemester == 'Not in Session') {
			$theSemester			= $nextSemester;
		}
	}

	if ($testMode) {
		$studentTableName		= 'wpw1_cwa_consolidated_student2';
		$advisorTableName		= 'wpw1_cwa_consolidated_advisor2';
		$advisorClassTableName	= 'wpw1_cwa_consolidated_advisorclass2';
		$modeInfo				= "<p><b>Program Running in TestMode</b></p>";
	} else {
		$studentTableName		= 'wpw1_cwa_consolidated_student';
		$advisorTableName		= 'wpw1_cwa_consolidated_advisor';
		$advisorClassTableName	= 'wpw1_cwa_consolidated_advisorclass';
		$modeInfo				= "";
	}

	if ($doDebug) {
		echo $modeInfo;
	}
	
	$classArray					= array();
	$classArray['Beginner']		= array();
	$classArray['Fundamental']	= array();
	$classArray['Intermediate']	= array();
	$classArray['Advanced']		= array();
	
	$sql			= "select advisor_call_sign, 
							  sequence, 
							  level, 
							  class_schedule_days, 
							  class_schedule_times, 
							  class_schedule_days_utc, 
							  class_schedule_times_utc, 
							  class_size, 
							  number_students 
						from $advisorClassTableName 
						where semester='$semester' 
						order by advisor_call_sign, sequence";
	$wpw1_cwa_advisorclass				= $wpdb->get_results($sql);
	if ($wpw1_cwa_advisorclass === FALSE) {
		$myError			= $wpdb->last_error;
		$myQuery			= $wpdb->last_query;
		if ($doDebug) {
			echo "Reading $advisorClassTableName table failed<br />
				  wpdb->last_query: $myQuery<br />
				  wpdb->last_error: $myError<br />";
		}
		$errorMsg			= "$jobname Reading $advisorClassTableName table failed. <p>SQL: $myQuery</p><p> Error: $myError</p>";
		sendErrorEmail($errorMsg);
	} else {
		$numACRows						= $wpdb->num_rows;
		if ($doDebug) {
			$myStr						= $wpdb->last_query;
			echo "ran $myStr<br />and found $numACRows rows<br />";
		}
		if ($numACRows > 0) {
			foreach ($wpw1_cwa_advisorclass as $advisorClassRow) {
				$advisorClass_advisor_call_sign 		= $advisorClassRow->advisor_call_sign;
				$advisorClass_sequence 					= $advisorClassRow->sequence;
				$advisorClass_level 					= $advisorClassRow->level;
				$advisorClass_class_size 				= $advisorClassRow->class_size;
				$advisorClass_class_schedule_days 		= $advisorClassRow->class_schedule_days;
				$advisorClass_class_schedule_times 		= $advisorClassRow->class_schedule_times;
				$advisorClass_class_schedule_days_utc 	= $advisorClassRow->class_schedule_days_utc;
				$advisorClass_class_schedule_times_utc 	= $advisorClassRow->class_schedule_times_utc;
				$class_number_students					= $advisorClassRow->number_students;

				$availableSeats							= 0;

				if ($doDebug) {
					echo "<br />have class $advisorClass_sequence for advisor $advisorClass_advisor_call_sign<br />";
				}
				
				// get the info from the advisor table to see if we can use this advisors's classes
				$useAdvisor		= TRUE;
				
				$sql			= "select survey_score, 
										   verify_response, 
										   replacement_status 
									from $advisorTableName 
									where semester='$semester' 
									and call_sign='$advisorClass_advisor_call_sign'";
				$wpw1_cwa_advisor	= $wpdb->get_results($sql);
				if ($wpw1_cwa_advisor === FALSE) {
					$myError			= $wpdb->last_error;
					$myQuery			= $wpdb->last_query;
					if ($doDebug) {
						echo "Reading $advisorTableName table failed<br />
							  wpdb->last_query: $myQuery<br />
							  wpdb->last_error: $myError<br />";
					}
					$errorMsg			= "$jobname Reading $advisorTableName table failed. <p>SQL: $myQuery</p><p> Error: $myError</p>";
					sendErrorEmail($errorMsg);
					$content		.= "Unable to obtain content from $advisorTableName<br />";
				} else {
					$numARows			= $wpdb->num_rows;
					if ($doDebug) {
						$myStr			= $wpdb->last_query;
						echo "ran $myStr<br />and found $numARows rows in $advisorTableName table<br />";
					}
					if ($numARows > 0) {
						foreach ($wpw1_cwa_advisor as $advisorRow) {
							$advisor_survey_score 				= $advisorRow->survey_score;
							$advisor_verify_response 			= strtoupper($advisorRow->verify_response);
							$advisor_replacement_status 		= $advisorRow->replacement_status;
							
							if ($advisorClass_advisor_call_sign == 'K1BG') {
								$useAdvisor						= FALSE;
								if ($doDebug) {
									echo "advisor is $advisorClass_advisor_call_sign. Bypassed<br />";
								}
							}
							if ($advisor_survey_score == 6 || $advisor_survey_score == 13) {
								$useAdvisor						= FALSE;
								if($doDebug) {
									echo "survey score is $advisor_survey_score. Bypassed<br />";
								}
							}
							if ($advisor_verify_response == 'R') {
								$useAdvisor						= FALSE;
								if($doDebug) {
									echo "verify response is $advisor_verify_response. Bypassed<br />";
								}							
							}
							if ($advisor_replacement_status == 'N') {
								$useAdvisor						= FALSE;
								if($doDebug) {
									echo "replacement status is $advisor_replacement_status. Bypassed<br />";
								}							
							}
							if ($useAdvisor) {
								// see if there are any seats available
 								if ($advisorClass_class_size - $class_number_students > 0) {
 									$availableSeats				= $advisorClass_class_size - $class_number_students;
 									if ($doDebug) {
 										echo "advisor's class has $availableSeats seats available<br />";
 									}
 								}
 								if ($availableSeats > 0) {
 									if ($doDebug) {
 										echo "adding advisorclass info to the classArray<br />";
 									}
 									$myStr						= substr($advisorClass_class_schedule_times_utc,0,2);
									$advisorClass_class_schedule_times_utc	= $myStr . "00"; 									$classArray[$advisorClass_level]["$advisorClass_class_schedule_times_utc $advisorClass_class_schedule_days_utc"][$advisorClass_advisor_call_sign]['local'] 				= "$advisorClass_class_schedule_times $advisorClass_class_schedule_days";
 									$classArray[$advisorClass_level]["$advisorClass_class_schedule_times_utc $advisorClass_class_schedule_days_utc"][$advisorClass_advisor_call_sign]['class size'] 		= $advisorClass_class_size;
 									$classArray[$advisorClass_level]["$advisorClass_class_schedule_times_utc $advisorClass_class_schedule_days_utc"][$advisorClass_advisor_call_sign]['number_students'] 	= $class_number_students;
 									$classArray[$advisorClass_level]["$advisorClass_class_schedule_times_utc $advisorClass_class_schedule_days_utc"][$advisorClass_advisor_call_sign]['availableSeats'] 	= $availableSeats;
 									$classArray[$advisorClass_level]["$advisorClass_class_schedule_times_utc $advisorClass_class_schedule_days_utc"][$advisorClass_advisor_call_sign]['sequence'] 			= $advisorClass_sequence;
 									
 								} else {
 									if ($doDebug) {
 										echo "no seats available<br />";
 									}
 								}
							}
						}
					}
				}
			}
		}
	}
	if ($doDebug) {
		echo "returning classArray:<br /><pre>";
		print_r($classArray);
		echo "</pre><br />";
	}
				
	return $classArray;
}
add_action('build_list_of_available_classes', 'build_list_of_available_classes');
