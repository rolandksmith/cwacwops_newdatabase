Function AdvisorEvaluationStatus($advisorCallSign,$advisorSemester,$testMode,$doDebug){

/* Returns the advisor classes and student evaluation status

	Input:		Advisor call sign
				Semester
				testMode
				doDebug
				
	Returns		array(class sequence|Level|Nmbr of Students|Nmbr Evaluated|Nmbr Not Evaluated|Nmbr Promotable|Nmbr Not Promotable|Nmbr Withdrawn)
				if no data or bad input returns array(FALSE);
				
*/

	global $wpdb;
	
	if ($doDebug) {
		echo "<br />FUNCTION AdvisorEvaluationStatus with data $advisorCallSign, $advisorSemester>br />";
	}
	
	// Check the input
	if ($advisorCallSign == '' || $advisorSemester == '') {
		return array(FALSE);
	}
	
	if ($testMode) {
		$advisorClassTableName			= 'wpw1_cwa_advisorclass2';
		$studentTableName				= 'wpw1_cwa_student2';
	} else {
		$advisorClassTableName			= 'wpw1_cwa_advisorclass';
		$studentTableName				= 'wpw1_cwa_student';
	}

	$dataOK							= TRUE;
	$returnArray					= array();

	// get the classes and counts
	$sql							= "select * from $advisorClassTableName 
									  where advisorclass_call_sign = '$advisorCallSign' 
										and advisorclass_semester='$advisorSemester'
													  order by advisorclass_sequence";
	$wpw1_cwa_advisorclass	= $wpdb->get_results($sql);
	if ($wpw1_cwa_advisorclass === FALSE) {
		handleWPDBError($jobname,$doDebug);
	} else {
		$numACRows			= $wpdb->num_rows;
		$dataOK				= FALSE;
		if ($doDebug) {
			echo "ran $sql<br />and found $numACRows rows<br />";
		}
		if ($numACRows > 0) {
			foreach ($wpw1_cwa_advisorclass as $advisorClassRow) {
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

				if ($doDebug) {
					echo "have class $advisorClass_sequence for $advisorCallSign<br />";
				}

				$nmbrStudents					= 0;
				$nmbrEvaluated					= 0;
				$nmbrNotEvaluated				= 0;
				$nmbrPromoted					= 0;
				$nmbrNotPromoted				= 0;
				$nmbrWithdrawn					= 0;

				// cycle through thru all students in the class
				for ($snum=1;$snum<=$advisorClass_number_students;$snum++) {
					if ($snum < 10) {
						$strSnum 		= str_pad($snum,2,'0',STR_PAD_LEFT);
					} else {
						$strSnum		= strval($snum);
					}
					$theInfo			= ${'advisorClass_student' . $strSnum};
					if ($doDebug) {
						echo "processing student $strSnum whose info is $theInfo<br />";
					}
					if ($theInfo != '') {
						$studentCallSign = $theInfo;
						
						// get the student info
						$student_promotable			= $wpdb->get_var("select student_promotable 
																	  from $studentTableName 
																	  where student_call_sign = '$studentCallSign' 
																	  and student_semester = '$advisorSemester'");
						if ($student_promotable == NULL || $student_promotable == 0) {
							$student_promotable		= '';
						}			
						$nmbrStudents++;
						if ($student_promotable == '') {
							$nmbrNotEvaluated++;
						} else {
							$nmbrEvaluated++;
						}
						if ($student_promotable == 'P') {
							$nmbrPromoted++;
						}
						if ($student_promotable == 'N') {
							$nmbrNotPromoted++;
						}
						if ($student_promotable == 'W') {
							$nmbrWithdrawn++;
						}
					}
				}
				$returnArray[]			= "$advisorClass_sequence|$advisorClass_level|$nmbrStudents|$nmbrEvaluated|$nmbrNotEvaluated|$nmbrPromoted|$nmbrNotPromoted|$nmbrWithdrawn";
				if ($doDebug) {
					echo "wrote $advisorClass_sequence|$advisorClass_level|$nmbrStudents|$nmbrEvaluated|$nmbrNotEvaluated|$nmbrPromoted|$nmbrNotPromoted|$nmbrWithdrawn to returnArray<br />";
				}
			}
		} else {
			$returnArray[]			= FALSE;
		}
	}
	if ($doDebug) {
		echo "returnArray:<br /><pre>";
		print_r($returnArray);
		echo "</pre><br /><br />";
	}
	return $returnArray;

}