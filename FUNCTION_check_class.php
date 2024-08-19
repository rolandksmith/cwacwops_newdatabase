function check_class($student_callsign='',$student_level='',$testMode=FALSE,$doDebug=FALSE) {

/*	Finds the most recent class the student has taken at the requested level

	input:	student call sign
			level
			testmode
			dodebug
			
	returns:	array
					Y,N,E			Taken the class: Y, hasn't taken the class: N, input error: E
					semester			the semester the class was taken
					promotable		whether or not the student was promotable

					if no class was taken or input error, the semester and promotable will be empty
					
	Modified 13Jul23 by Roland to use consolidated tables
*/

	global $wpdb;
	$levelArray	= array('Beginner','Fundamental','Intermediate','Advanced');
	$gotError	= FALSE;

	if ($doDebug) {
		echo "<br />Arrived at function check_class with $student_callsign, $student_level<br >";
	}
	if (!in_array($student_level,$levelArray)) {
		$gotError		= TRUE;
		if ($doDebug) {
			echo "level mismatch<br />";
		}
	}
	if ($student_callsign == '') {
		$gotError		= TRUE;
		if ($doDebug) {
			echo "call sign is empty<br />";
		}
	}
	if ($gotError) {
		return array('E','','');
	} else {
		if ($testMode) {
			$studentTableName = 'wpw1_cwa_consolidated_student2';
		} else {
			$studentTableName = 'wpw1_cwa_consolidated_student';
		}
		$sql					= "select call_sign, 
										   level, 
											semester, 
											promotable 
									from $studentTableName 
									where call_sign='$student_callsign' 
									and level='$student_level' 
									and (student_status = 'Y' or student_status = 'S') 
									order by request_date DESC 
									limit 1";
		
		$wpw1_cwa_student	= $wpdb->get_results($sql);
		if ($wpw1_cwa_student  === FALSE) {
			if ($doDebug) {
				echo "Reading $studentTableName table<br />";
				echo "wpdb->last_query: " . $wpdb->last_query . "<br />";
				if ($wpdb->last_error != '') {
					echo "<b>wpdb->last_error: " . $wpdb->last_error . "</b><br />";
				}
			}
			$myStr					= $wpdb->last_error;
			$errorMsg				= "In function check_class reading $studentTableName for $student_callsign
returned FALSE. Last error: $myStr";
			sendErrorEmail($errorMsg);
			return array('E','','');			
		} else {
			$numRows				= $wpdb->num_rows;
			if ($doDebug) {
				$myStr				= $wpdb->last_query;
				echo "read $student_callsign from $studentTableName returned $numRows rows. Query: $myStr<br />";
			}
			if ($numRows > 0) {
				foreach ($wpw1_cwa_student as $studentRow) {
					$student_call_sign						= strtoupper($studentRow->call_sign);
					$student_level  						= $studentRow->level;
					$student_semester						= $studentRow->semester;
					$student_promotable  					= $studentRow->promotable;

					if ($doDebug) {
						echo "Found student with promotable = $student_promotable <br />";
					}
					$theSemester								= $student_semester;
					$thePromotable								= $student_promotable;
				}
				return array('Y',$theSemester,$thePromotable);
			} else {
				return array('N','','');
			}
		}
	}
}
add_action ('check_class', 'check_class');