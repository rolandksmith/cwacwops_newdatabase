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
		$advisordvisorClassTableName	= 'wpw1_cwa_consolidated_advisorclass2';
		$studentTableName				= 'wpw1_cwa_consolidated_student2';
	} else {
		$advisordvisorClassTableName	= 'wpw1_cwa_consolidated_advisorclass';
		$studentTableName				= 'wpw1_cwa_consolidated_student';
	}

	$returnArray					= array();

	// get the classes and counts
	$wpw1_cwa_consolidated_advisorclass	= $wpdb->get_results("select * from $advisordvisorClassTableName 
													  where advisor_call_sign = '$advisorCallSign' 
														and semester='$advisorSemester'
													  order by sequence");
	if ($wpw1_cwa_consolidated_advisorclass === FALSE) {
		if ($doDebug) {
			echo "Reading $advisordvisorClassTableName table failed<br />";
			echo "wpdb->last_query: " . $wpdb->last_query . "<br />";
			echo "<b>wpdb->last_error: " . $wpdb->last_error . "</b><br />";
		}
		$returnArray[]			= FALSE;
	} else {
		$numACRows				= $wpdb->num_rows;
		if ($doDebug) {
			$myStr				= $wpdb->last_query;
			echo "ran $myStr<br />and found $numACRows rows in $advisordvisorClassTableName table<br />";
		}
		if ($numACRows > 0) {
			foreach ($wpw1_cwa_consolidated_advisorclass as $advisorClassRow) {
				$advisorClass_ID				 		= $advisorClassRow->advisorclass_id;
				$advisorClass_level						= $advisorClassRow->level;
				$advisorClass_sequence 					= $advisorClassRow->sequence;
				$advisorClass_semester 					= $advisorClassRow->semester;
				$advisorClass_student01 				= $advisorClassRow->student01;
				$advisorClass_student02 				= $advisorClassRow->student02;
				$advisorClass_student03 				= $advisorClassRow->student03;
				$advisorClass_student04 				= $advisorClassRow->student04;
				$advisorClass_student05 				= $advisorClassRow->student05;
				$advisorClass_student06 				= $advisorClassRow->student06;
				$advisorClass_student07 				= $advisorClassRow->student07;
				$advisorClass_student08 				= $advisorClassRow->student08;
				$advisorClass_student09 				= $advisorClassRow->student09;
				$advisorClass_student10 				= $advisorClassRow->student10;
				$advisorClass_student11 				= $advisorClassRow->student11;
				$advisorClass_student12 				= $advisorClassRow->student12;
				$advisorClass_student13 				= $advisorClassRow->student13;
				$advisorClass_student14 				= $advisorClassRow->student14;
				$advisorClass_student15 				= $advisorClassRow->student15;
				$advisorClass_student16 				= $advisorClassRow->student16;
				$advisorClass_student17 				= $advisorClassRow->student17;
				$advisorClass_student18 				= $advisorClassRow->student18;
				$advisorClass_student19 				= $advisorClassRow->student19;
				$advisorClass_student20 				= $advisorClassRow->student20;
				$advisorClass_student21 				= $advisorClassRow->student21;
				$advisorClass_student22 				= $advisorClassRow->student22;
				$advisorClass_student23 				= $advisorClassRow->student23;
				$advisorClass_student24 				= $advisorClassRow->student24;
				$advisorClass_student25 				= $advisorClassRow->student25;
				$advisorClass_student26 				= $advisorClassRow->student26;
				$advisorClass_student27 				= $advisorClassRow->student27;
				$advisorClass_student28 				= $advisorClassRow->student28;
				$advisorClass_student29 				= $advisorClassRow->student29;
				$advisorClass_student30 				= $advisorClassRow->student30;
				$class_number_students						= $advisorClassRow->number_students;

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
				for ($snum=1;$snum<=$class_number_students;$snum++) {
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
						$wpw1_cwa_consolidated_student	= $wpdb->get_results("select promotable 
																	  from $studentTableName 
																	  where call_sign='$studentCallSign' 
																		and semester='$advisorSemester'");
						if ($wpw1_cwa_consolidated_student === FALSE) {
							if ($doDebug) {
								echo "Reading $studentTableName table failed<br />";
								echo "wpdb->last_query: " . $wpdb->last_query . "<br />";
								echo "<b>wpdb->last_error: " . $wpdb->last_error . "</b><br />";
							}
						} else {
							$numPSRows									= $wpdb->num_rows;
							if ($doDebug) {
								$myStr			= $wpdb->last_query;
								echo "ran $myStr<br />and found $numPSRows rows in $studentTableName table<br />";
							}
							if ($numPSRows > 0) {
								foreach ($wpw1_cwa_consolidated_student as $studentRow) {
									$student_promotable  					= $studentRow->promotable;
									if ($doDebug) {
										echo "got a promotable of $student_promotable for $studentCallSign<br />";
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