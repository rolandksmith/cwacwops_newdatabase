function get_student_last_class($inp_callsign = '',$doDebug=FALSE,$testMode=FALSE) {

/*	Obtains from the student table the last class information
	
	Input: call sign
	
	Returns		array['Beginner']['Semester']			Semester the class was taken or empty if no beginner class taken
					 ['Beginner']['advisor']			Assigned advisor
					 ['Beginner']['advisor class']		assigned advisor class
					 ['Beginner']['Promotable']			promotable information
					 ['Beginner']['status']				student status
					 ['Fundamental']['Semester']		Semester the class was taken or empty if no basic class taken
					 ['Fundamental']['advisor']			Assigned advisor
					 ['Fundamental']['advisor class']	assigned advisor class
					 ['Fundamental']['Promotable']		promotable information
					 ['Fundamental']['status']			student status
					 ['Intermediate']['Semester']		Semester the class was taken or empty if no intermediate class taken
					 ['Intermediate']['advisor']		Assigned advisor
					 ['Intermediate']['advisor class']	assigned advisor class
					 ['Intermediate']['Promotable']		promotable information
					 ['Intermediate']['status']			student status
					 ['Advanced']['Semester']			Semester the class was taken or empty if no advanced class taken
					 ['Advanced']['advisor']			Assigned advisor
					 ['Advanced']['advisor class']		assigned advisor class
					 ['Advanced']['Promotable']			promotable information
					 ['Advanced']['status']				student status
					 ['Current']['Semester']			Semester the class was taken
					 ['Current']['advisor']				Assigned advisor
					 ['Current']['advisor class']		assigned advisor class
					 ['Current']['Promotable']			promotable information
					 ['Current']['status']				student status
					 ['Current']['level']				student level
	)
	
	Modified 29Oct22 by Roland for new timezone table format
	Modified 3Feb2023 by Roland to display the returned array if running in debug mode
	Modified 26June23 by Roland to handle last class still being in the student table
	Modified 12Jul23 by Roland to use current tables only
	Modified 10Oct24 by Roland for new database
*/

	global $wpdb;

	if ($doDebug) {
		echo "<br /><b>Function get_student_last_class</b> with parameter $inp_callsign<br />";
	}
	if ($doDebug) {
		ini_set('display_errors','1');
		error_reporting(E_ALL);	
	}

//	$doDebug										= TRUE;
//	$testMode										= FALSE;
	$initializationArray 							= data_initialization_func();
	$currentSemester								= $initializationArray['currentSemester'];
	$nextSemester									= $initializationArray['nextSemester'];
	$semesterTwo									= $initializationArray['semesterTwo'];
	$semesterThree									= $initializationArray['semesterThree'];
	$semesterFour									= $initializationArray['semesterFour'];
	$returnArray['Beginner']['Semester']		 	= '';
	$returnArray['Beginner']['Advisor']				= '';
	$returnArray['Beginner']['Advisor class']		= '';
	$returnArray['Beginner']['Promotable']			= '';
	$returnArray['Beginner']['Status']				= '';
	$returnArray['Fundamental']['Semester']			= '';
	$returnArray['Fundamental']['Advisor']			= '';
	$returnArray['Fundamental']['Advisor class']	= '';
	$returnArray['Fundamental']['Promotable']		= '';
	$returnArray['Fundamental']['Status']			= '';
	$returnArray['Intermediate']['Semester']		= '';
	$returnArray['Intermediate']['Advisor']			= '';
	$returnArray['Intermediate']['Advisor class']	= '';
	$returnArray['Intermediate']['Promotable']		= '';
	$returnArray['Intermediate']['Status']			= '';
	$returnArray['Advanced']['Semester']			= '';
	$returnArray['Advanced']['Advisor']				= '';
	$returnArray['Advanced']['Advisor class']		= '';
	$returnArray['Advanced']['Promotable']			= '';
	$returnArray['Advanced']['Status']				= '';
	$returnArray['Current']['Semester']				= '';
	$returnArray['Current']['Advisor']				= '';
	$returnArray['Current']['Advisor class']		= '';
	$returnArray['Current']['Promotable']			= '';
	$returnArray['Current']['Status']				= '';
	$returnArray['Current']['Level']				= '';
	$levelArray										= array('Beginner','Intermediate','Fundamental','Advanced');

	if ($testMode) {
		$studentTableName			= "wpw1_cwa_student2";
		if ($doDebug) {
			echo "Operating in testMode<br />";
		}
	} else {
		$studentTableName			= "wpw1_cwa_student";
		if ($doDebug) {
			echo "Operating in Production mode<br />";
		}
	}

	// get the student info from student table for each level
	
	foreach($levelArray as $thisLevel) {
		$sql				= "select student_call_sign, 
									  student_level, 
									  student_assigned_advisor, 
									  student_assigned_advisor_class, 
									  student_promotable, 
									  student_semester, 
									  student_status 
								from $studentTableName 
								where student_call_sign='$inp_callsign' 
								and (student_status = 'Y' or student_status = 'S') 
								and student_level = '$thisLevel'
								order by student_request_date DESC 
								limit 1";
		$wpw1_cwa_student	= $wpdb->get_results($sql);
		if ($wpw1_cwa_student === FALSE) {
			handWPDBError("FUNCTION: Get Student Last Class", $doDebug);
		} else {
			$numPSRows									= $wpdb->num_rows;
			if ($doDebug) {
				echo "ran $sql<br />and found $numPSRows rows in $studentTableName table<br />";
			}
			if ($numPSRows > 0) {
				foreach ($wpw1_cwa_student as $studentRow) {
					$student_call_sign						= strtoupper($studentRow->student_call_sign);
					$student_level  						= $studentRow->student_level;
					$student_semester						= $studentRow->student_semester;
					$student_assigned_advisor  				= $studentRow->student_assigned_advisor;
					$student_assigned_advisor_class 		= $studentRow->student_assigned_advisor_class;
					$student_promotable  					= $studentRow->student_promotable;
					$student_status	  						= $studentRow->student_status;

					$returnArray[$thisLevel]['Semester']		 	= $student_semester;
					$returnArray[$thisLevel]['Advisor']				= $student_assigned_advisor;
					$returnArray[$thisLevel]['Advisor class']		= $student_assigned_advisor_class;
					$returnArray[$thisLevel]['Promotable']			= $student_promotable;
					$returnArray[$thisLevel]['Status']				= $student_status;
					if ($doDebug) {
						echo "loaded up data for $student_level<br />";
					}
				}
			}
		}
	}
	
	/// now get the most current record in the student table regardless of level
	$sql				= "select student_call_sign, 
								  student_level, 
								  student_assigned_advisor, 
								  student_assigned_advisor_class, 
								  student_promotable, 
								  student_semester, 
								  student_status 
						  from $studentTableName 
						  where student_call_sign='$inp_callsign' 
						   and (student_status = 'Y' or student_status = 'S') 
						   and (student_semester = '$semesterTwo' 
						   or student_semester = '$semesterThree' 
						   or student_semester='$semesterFour') 
						   order by student_date_created DESC 
						   limit 1";
	$wpw1_cwa_student		= $wpdb->get_results($sql);
	if ($wpw1_cwa_student === FALSE) {
		if ($doDebug) {
			echo "Reading $studentTableName table failed<br />";
			echo "wpdb->last_query: " . $wpdb->last_query . "<br />";
			echo "wpdb->last_error: " . $wpdb->last_error . "<br />";
		}
	} else {
		$numSRows			= $wpdb->num_rows;
		if ($doDebug) {
			echo "ran $sql<br />and found $numSRows rows<br />";
		}
		if ($numSRows > 0) {
			foreach ($wpw1_cwa_student as $studentRow) {
				$student_call_sign						= strtoupper($studentRow->student_call_sign);
				$student_level  						= $studentRow->student_level;
				$student_semester  						= $studentRow->student_semester;
				$student_assigned_advisor  				= $studentRow-student_>assigned_advisor;
				$student_assigned_advisor_class 		= $studentRow->student_assigned_advisor_class;
				$student_promotable  					= $studentRow->student_promotable;
				$student_status  						= $studentRow->student_status;

				$returnArray['Current']['Semester']		 = $student_semester;
				$returnArray['Current']['Advisor']		 = $student_assigned_advisor;
				$returnArray['Current']['Advisor class'] = $student_assigned_advisor_class;
				$returnArray['Current']['Promotable']	 = $student_promotable;
				$returnArray['Current']['Status']		 = $student_status;
				$returnArray['Current']['Level']		 = $student_level;
				
				if ($doDebug) {
					echo "Loading up current data for $inp_callsign<br />";
				}
			}
		}
	}

	if ($doDebug) {
		echo "Exiting get_student_last_class function<br /><pre>";
		print_r($returnArray);
		echo "</pre><br />";
	}
	
	return $returnArray;
}
add_action('get_student_last_class','get_student_last_class');
