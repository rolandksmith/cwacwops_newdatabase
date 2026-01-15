function student_management_func() {

/* CW Academy Student Management
  	
*/

	global $wpdb, $studentTableName, $advisorTableName, $advisorClassTableName, $theSemester, $doDebug;

	$doDebug					= FALSE;
	$testMode					= FALSE;
	$initializationArray 		= data_initialization_func();
	$userName 					= $initializationArray['userName'];
	$currentDate 				= $initializationArray['currentDate'];
	$daysToSemester				= $initializationArray['daysToSemester'];
	$validTestmode				= $initializationArray['validTestmode'];
	$siteURL					= $initializationArray['siteurl'];
	$userName 					= $initializationArray['userName'];
	$currentDate 				= $initializationArray['currentDate'];
	$daysToSemester				= $initializationArray['daysToSemester'];
	$validTestmode				= $initializationArray['validTestmode'];
	$siteURL					= $initializationArray['siteurl'];
	$proximateSemester			= $initializationArray['proximateSemester'];
	$currentSemester			= $initializationArray['currentSemester'];
	$nextSemester				= $initializationArray['nextSemester'];
	$semesterTwo				= $initializationArray['semesterTwo'];
	$semesterThree				= $initializationArray['semesterThree'];
	$semesterFour				= $initializationArray['semesterFour'];
	$proximateSemester			= $initializationArray['proximateSemester'];
	$languageConversion			= $initializationArray['languageConversion'];
	if ($doDebug) {
		echo "Initialization Array:<br /><pre>";
		print_r($initializationArray);
		echo "</pre><br />";
	}

	if ($userName == '') {
		return "YOU'RE NOT AUTHORIZED!<br />Goodby";
	}

/// get the time that the process started
	$startingMicroTime			= microtime(TRUE);

//	if ($doDebug) {
		ini_set('display_errors','1');
		error_reporting(E_ALL);	
//	}

	$strPass					= "1";
	$requestType				= '';
	$inp_student_callsign		= '';
	$inp_advisor_callsign		= '';
	$level						= '';
	$actionDate					= date('dMY H:i');
	$logDate					= date('Y-m-d H:i');
	$inp_level					= '';
	$inp_additional				= '';
	$inp_advisorClass			= '';
	$inp_callsign				= '';
	$inp_search					= '';
	$fieldTest					= array('action_log','post_status','post_title','control_code');
	$inp_mode					= '';
	$inp_choice					= '';
	$search_days				= '';
	$search_time				= '';
	$jobname					= 'Student Mgmt: Not Specified';
	$inp_prev_advisor			= '';
	$inp_prev_advisor_class		= '';
	$theURL						= "$siteURL/cwa-student-management/";
	$prodURL					= "https://cwa.cwops.org/cwa-student-management/";
	$colorChartURL				= "$siteURL/cwa-student-and-advisor-color-chart/";
	$studentHistoryURL			= "$siteURL/cwa-show-detailed-history-for-student/";
	$changeSemesterURL			= "$siteURL/cwa-move-student-to-different-semester/";
	$updateUnassignedInfoURL	= "$siteURL/cwa-update-unassigned-student-information/";
	$updateStudentInfoURL		= "$siteURL/cwa-display-and-update-student-signup-information/";
	$pushURL					= "$siteURL/cwa-push-advisor-class/";
	
	$searchRange				= array(0=>'0|400',
										100=>'0|500',
										200=>'0|600',
										300=>'0|700',
										400=>'100|800',
										500=>'200|900',
										600=>'300|1000',
										700=>'400|1100',
										800=>'500|1200',
										900=>'600|1300',
										1000=>'700|1400',
										1100=>'800|1500',
										1200=>'900|1600',
										1300=>'1000|1700',
										1400=>'1100|1800',
										1500=>'1200|1900',
										1600=>'1300|22000',
										1700=>'1400|2100',
										1800=>'1500|2200',
										1900=>'1600|2300',
										2000=>'1700|2400',
										2100=>'1800|2400',
										2200=>'1900|2400',
										2300=>'2000|2400');
	
	if ($testMode) {
		$studentTableName			= 'wpw1_cwa_student2';
		$advisorTableName			= 'wpw1_cwa_advisor2';
		$advisorClassTableName		= 'wpw1_cwa_advisorclass2';
		$newAssessmentTableName		= 'wpw1_cwa_new_assessment_data2';
		$audioAssessmentTableName	= 'wpw1_cwa_audio_assessment2';
		$tempDataTableName			= 'wpw1_cwa_temp_data2';
		$userMasterTableName		= 'wpw1_cwa_user_master2';
		$operatingMode				= 'Testmode';
		if ($testMode) {
			echo "Function is under development.<br />";
		}
		$content .= "<p><strong>Function is under development.</strong></p>";
	} else {
		$studentTableName			= 'wpw1_cwa_student';
		$advisorTableName			= 'wpw1_cwa_advisor';
		$advisorClassTableName		= 'wpw1_cwa_advisorclass';
		$newAssessmentTableName		= 'wpw1_cwa_new_assessment_data';
		$audioAssessmentTableName	= 'wpw1_cwa_audio_assessment';
		$tempDataTableName			= 'wpw1_cwa_temp_data';
		$userMasterTableName		= 'wpw1_cwa_user_master';
		$operatingMode				= 'Production';
	}

	$student_dal = new CWA_Student_DAL();
	$advisor_dal = new CWA_Advisor_DAL();
	$advisorclass_dal = new CWA_Advisorclass_DAL();
	$user_dal = new CWA_User_Master_DAL();


// get the input information
	// set some logicals
	$getStudent					= FALSE;
	$getAdvisor					= FALSE;
	$getClass					= FALSE;
	$getStudentByCallsign		= FALSE;
	$getStudentById				= FALSE;
	$getAdvisorByCallsign		= FALSE;
	$getAdvisorById				= FALSE;
	$getClassByCallsign			= FALSE;
	$getClassById				= FALSE;
	$haveSemester				= FALSE;
	$haveStudent				= FALSE;
	$haveAdvisor				= FALSE;
	$haveClass					= FALSE;
	$preNumSRows				= 0;
	$preNumARows				= 0;
	$preNumCRows				= 0;
	$inp_use_language			= 'Y';

	if (isset($_REQUEST)) {
		foreach($_REQUEST as $str_key => $str_value) {
			if ($doDebug) {
				echo "Key: $str_key | Value: $str_value <br />\n";
			}
			if ($str_key 		== "strpass") {
				$strPass		 = $str_value;
				$strPass		 = filter_var($strPass,FILTER_UNSAFE_RAW);
			}
			if ($str_key 				== "inp_student_callsign") {
				$inp_student_callsign 	= $str_value;
				$inp_student_callsign 	= strtoupper(filter_var($inp_student_callsign,FILTER_UNSAFE_RAW));
				$getStudent				= TRUE;
				$getStudentByCallsign	= TRUE;
			}
			if ($str_key 			== "inp_new_callsign") {
				$inp_new_callsign = $str_value;
				$inp_new_callsign = strtoupper(filter_var($inp_new_callsign,FILTER_UNSAFE_RAW));
			}
			if ($str_key 			== "inp_use_language") {
				$inp_use_language = $str_value;
				$inp_use_language = strtoupper(filter_var($inp_use_language,FILTER_UNSAFE_RAW));
			}
			if ($str_key 				== "inp_advisor_callsign") {
				$inp_advisor_callsign 	= $str_value;
				$inp_advisor_callsign 	= strtoupper(filter_var($inp_advisor_callsign,FILTER_UNSAFE_RAW));
				$getAdvisor				= TRUE;
				$getAdvisorByCallsign	= TRUE;
			}
			if ($str_key 			== "inp_prev_advisor_class") {
				$inp_prev_advisor_class = $str_value;
				$inp_prev_advisor_class = filter_var($inp_prev_advisor_class,FILTER_UNSAFE_RAW);
			}
			if ($str_key 			== "inp_excluded_advisor") {
				$inp_excluded_advisor = $str_value;
				$inp_excluded_advisor = filter_var($inp_excluded_advisor,FILTER_UNSAFE_RAW);
			}
			if ($str_key 			== "inp_prev_advisor") {
				$inp_prev_advisor = $str_value;
				$inp_prev_advisor = strtoupper(filter_var($inp_prev_advisor,FILTER_UNSAFE_RAW));
			}
			if ($str_key 			== "inp_semester") {
				$inp_semester		= $str_value;
				$inp_semester 		= filter_var($inp_semester,FILTER_UNSAFE_RAW);
				$haveSemester		= TRUE;
			}
			if ($str_key 			== "inp_advisorclass") {
				$inp_advisorclass		= $str_value;
				$inp_advisorclass 		= filter_var($inp_advisorclass,FILTER_UNSAFE_RAW);
				$haveSemester		= TRUE;
			}
			if ($str_key 			== "inp_choice") {
				$inp_choice		 = $str_value;
				$inp_choice 		= filter_var($inp_choice,FILTER_UNSAFE_RAW);
			}
			if ($str_key 			== "search_days") {
				$search_days		 = $str_value;
				$search_days 		= filter_var($search_days,FILTER_UNSAFE_RAW);
			}
			if ($str_key 			== "search_time") {
				$search_time		 = $str_value;
				$search_time 		= filter_var($search_time,FILTER_UNSAFE_RAW);
			}
			if ($str_key 			== "inp_callsign") {
				$inp_callsign		 = strtoupper($str_value);
				$inp_callsign 		= filter_var($inp_callsign,FILTER_UNSAFE_RAW);
			}
			if ($str_key 			== "timezone") {
				$inp_timezone		 = $str_value;
				$inp_timezone		 = filter_var($inp_timezone,FILTER_UNSAFE_RAW);
			}
			if ($str_key 			== "level") {
				$inp_level			 = $str_value;
				$inp_level			 = filter_var($inp_level,FILTER_UNSAFE_RAW);
			}
			if ($str_key 			== "inp_level") {
				$inp_level			 = $str_value;
				$inp_level			 = filter_var($inp_level,FILTER_UNSAFE_RAW);
			}
			if ($str_key 			== "inp_search") {
				$inp_search			 = $str_value;
				$inp_search			 = filter_var($inp_search,FILTER_UNSAFE_RAW);
			}
			if ($str_key 			== "reqtype") {
				$inp_reqtype		 = $str_value;
				$inp_reqtype		 = filter_var($inp_reqtype,FILTER_UNSAFE_RAW);
			}
			if ($str_key 			== "studentid") {
				$inp_studentid		 = $str_value;
				$inp_studentid		 = filter_var($inp_studentid,FILTER_UNSAFE_RAW);
				$getStudent			= TRUE;
				$getStudentById		= TRUE;
			}
			if ($str_key 			== "inp_studentid") {
				$inp_studentid		 = $str_value;
				$inp_studentid		 = filter_var($inp_studentid,FILTER_UNSAFE_RAW);
				$getStudent			= TRUE;
				$getStudentById		= TRUE;
			}
			if ($str_key 			== "inp_additional") {
				$inp_additional		 = $str_value;
				$inp_additional		 = filter_var($inp_additional,FILTER_UNSAFE_RAW);
			}
			if ($str_key 			== "inp_advisorClass") {
				$inp_advisorClass		 = $str_value;
				$inp_advisorClass		 = filter_var($inp_advisorClass,FILTER_UNSAFE_RAW);
			}
			if ($str_key 			== "submit") {
				$submit		 = $str_value;
				$submit		 = filter_var($submit,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "inp_mode") {
				$inp_mode		 = $str_value;
				$inp_mode		 = filter_var($inp_mode,FILTER_UNSAFE_RAW);
				if ($inp_mode == 'TESTMODE') {
					$testMode	= TRUE;
				}
			}
			if ($str_key 		== "inp_verbose") {
				$inp_verbose		 = $str_value;
				$inp_verbose		 = filter_var($inp_verbose,FILTER_UNSAFE_RAW);
				if ($inp_verbose == 'Y') {
					$doDebug	= TRUE;
				}
			}
		}
	}

//	get the data based on above requests
	if ($doDebug) {
		echo "<br />Get Logicals:<br />";
		if ($getStudent) {
			echo "getStudent is TRUE<br />";
			if ($getStudentByCallsign) {
				echo "getStudentByCallsign is TRUE<br />";
			} else {
				echo "getStudentByCallsign is FALSE<br />";
			}
			if ($getStudentById) {
				echo "getStudentById is TRUE<br />";
			} else {
				echo "getStudentById is FALSE<br />";
			}
		} else {
			echo "getStudent is FALSE<br />";
		}
		if ($getAdvisor) {
			echo "getAdvisor is TRUE<br />";
			if ($getAdvisorByCallsign) {
				echo "getAdvisorByCallsign is TRUE<br />";
			} else {
				echo "getAdvisorByCallsign is FALSE<br />";
			}
			if ($getAdvisorById) {
				echo "getAdvisorById is TRUE<br />";
			} else {
				echo "getAdvisorById is FALSE<br />";
			}
		} else {
			echo "getAdvisor is FALSE<br />";
		}
	}

	// set some result logicals
	$haveStudent = FALSE;
	$haveAdvisor = FALSE;
	$haveStudentUser = FALSE;
	$haveAdvisorUser = FALSE;
	
	$getUserCallsign = '';

	// student information
	if ($getStudent) {
		if ($doDebug) {
			echo "<br/>Getting the student information<br />";
		}
		if ($getStudentByCallsign && !$getStudentById) {
			if ($haveSemester) {
				$student = get_student_and_user_master($inp_student_callsign,'callsign',$inp_semester,$operatingMode,$doDebug);
				if ($student !== FALSE) {
					$haveStudent = TRUE;
				}
			} else {			/// no semester. Get the record in the current or future semester
				$student = get_student_and_user_master($inp_student_callsign,'callsign','future',$operatingMode,$doDebug);
				if ($student !== FALSE) {
					$haveStudent = TRUE;
					$haveStudentUser = TRUE;
				}
			}			
		} elseif ($getStudentById) {
			if ($doDebug) {
				echo "attempting to get student data by id $inp_studentid<br />";
			}
			$student = get_student_and_user_master($inp_student_callsign,'id',$inp_studentid,$operatingMode, $doDebug); 
				if ($student !== FALSE && $student !== NULL) {
					$haveStudent = TRUE;
					$haveStudentUser = TRUE;
				} else {
					if ($doDebug) {
						echo "getting student id $inp_studentid returned FALSE|NUll<br />";
					}
				}
		} else {
			if ($doDebug) {
				echo "getStudent is TRUE but neither getStudentByCallsign nor getStudentById is set<br />";
			}
			$getStudent			= FALSE;
		}
	}
	
	// get the advisor information
	if ($getAdvisor) {
		if ($doDebug) {
			echo "<br/>Getting the advisor information<br />";
		}
		if ($getAdvisorByCallsign && !$getAdvisorById) {
			if ($haveSemester) {
				$advisor = get_advisor_and_user_master($inp_advisor_callsign,'callsign',$inp_semester,$operatingMode,$doDebug);
				if ($advisor !== FALSE) {
					$haveAdvisor = TRUE;
					$haveAdvisorUser = TRUE;
				}
			} else {			/// no semester. Get the record in the current or future semester
				$advisor = get_advisor_and_user_master($inp_advisor_callsign,'callsign','future',$operatingMode,$doDebug);
				if ($advisor !== FALSE) {
					$haveAdvisor = TRUE;
					$haveAdvisorUser = TRUE;
				}
			}			
		} elseif ($getAdvisorById) {
			if ($doDebug) {
				echo "attempting to get advisor data by id $inp_advisorid<br />";
			}
			$advisor = get_advisor_and_user_master($inp_advisor_callsign,'id',$inp_advisorid,$operatingMode, $doDebug);
			if ($advisor !== FALSE) {
				$haveAdvisor = TRUE;
			}
		} else {
			if ($doDebug) {
				echo "getAdvisor is TRUE but neither getAdvisorByCallsign nor getAdvisorById is set<br />";
			}
			$getAdvisor			= FALSE;
		}
	}


	if (in_array($userName,$validTestmode)) {			// give option to run in test mode 
		$testModeOption	= "<tr><td>Operation Mode</td>
								<td><input type='radio' class='formInputButton' name='inp_mode' value='Production' checked='checked'> Production<br />
									<input type='radio' class='formInputButton' name='inp_mode' value='TESTMODE'> TESTMODE</td></tr>
							<tr><td>Verbose Debugging?</td>
							<td><input type='radio' class='formInputButton' name='inp_verbose' value='N' checked='checked'> Standard Output<br />
								<input type='radio' class='formInputButton' name='inp_verbose' value='Y'> Turn on Debugging </td></tr>";
		} else {
			$testModeOption	= '';
		}

	
function getTheReason($strReasonCode) {
	if ($strReasonCode == 'H') {
		return "(H) Student not promotable but signed up for next class level";
	}
	if ($strReasonCode == 'Q') {
		return "(Q) Student not evaluated but signed up for next class level";
	}
	if ($strReasonCode == 'W') {
		return "(W) Student withdrew but signed up for next class level";
	}
	if ($strReasonCode == 'E') {
		return "(E) Advisor has not evaluated the student who has signed up for next class level";
	}
	if ($strReasonCode == 'A') {
		return "(A) Student hard-assigned to AC6AC";
	}
	if ($strReasonCode == 'B') {
		return "(B) Student is a Bad Actor";
	}
	if ($strReasonCode === 'L') {
		return "(L) Student wants a class in a language that is not available";
	}
	return "($strReasonCode) unknown";
}	


	function getStudentCount($advisor_callsign,$advisor_sequence) {

		global $wpdb, $studentTableName, $advisorTableName, $advisorClassTableName, $proximateSemester, $doDebug;
		
		$numSRows			= 0;

		$sql				= "select count($student_call_sign) from $studentTableName 
								where student_semester='$proximateSemester' 
									and student_assigned_advisor='$advisor_callsign' 
									and student_assigned_advisor_class='$advisor_sequence'
									and (student_status='Y' or student_status='S')";
		$studentCount = $student_dal->run_sql($sql, $operatingMode);
		if ($studentCount === FALSE || $studentCount === NULL) {
			if ($doDebug) {
				echo "run sql for student returned FALSE|NULL<br />";
			}
		} else {
			if (! empty($studentCount)) {
				foreach($studentCount as $key => $value) {
					foreach($value as $thisField => $theCount) {
						$$thisField = $theCount;
					}
					return $theCount;
				}
			}
		}						
		return 0;
	}
	
	$content = "<style type='text/css'>
				fieldset {font:'Times New Roman', sans-serif;color:#666;background-image:none;
				background:#efefef;padding:2px;border:solid 1px #d3dd3;}
				
				legend {font:'Times New Roman', sans-serif;color:#666;font-weight:bold;
				font-variant:small-caps;background:#d3d3d3;padding:2px 6px;margin-bottom:8px;}
				
				label {font:'Times New Roman', sans-serif;font-weight:bold;line-height:normal;
				text-align:right;margin-right:10px;position:relative;display:block;float:left;width:150px;}
				
				textarea.formInputText {font:'Times New Roman', sans-serif;color:#666;
				background:#fee;padding:2px;border:solid 1px #f66;margin-right:5px;margin-bottom:5px;}
				
				textarea.formInputText:focus {color:#000;background:#ffffff;border:solid 1px #006600;}
				
				textarea.formInputText:hover {color:#000;background:#ffffff;border:solid 1px #006600;}
				
				input.formInputText {color:#666;background:#fee;padding:2px;
				border:solid 1px #f66;margin-right:5px;margin-bottom:5px;}
				
				input.formInputText:focus {color:#000;background:#ffffff;border:solid 1px #006600;}
				
				input.formInputText:hover {color:#000;background:#ffffff;border:solid 1px #006600;}
				
				input.formInputFile {color:#666;background:#fee;padding:2px;border:
				solid 1px #f66;margin-right:5px;margin-bottom:5px;height:20px;}
				
				input.formInputFile:focus {color:#000;background:#ffffff;border:solid 1px #006600;}
				
				select.formSelect {color:#666;background:#fee;padding:2px;
				border:solid 1px #f66;margin-right:5px;margin-bottom:5px;cursor:pointer;}
				
				select.formSelect:hover {color:#333;background:#ccffff;border:solid 1px #006600;}
				
				input.formInputButton {vertical-align:middle;font-weight:bolder;
				text-align:center;color:#300;background:#f99;padding:1px;border:solid 1px #f66;
				cursor:pointer;position:relative;float:left;}
				
				input.formInputButton:hover {color:#f8f400;}
				
				input.formInputButton:active {color:#00ffff;}
				
				tr {color:#333;background:#eee;}
				
				table{font:'Times New Roman', sans-serif;background-image:none;border-collapse:collapse;}
				
				th {color:#ffff;background-color:#000;padding:5px;font-size:small;}
				
				td {padding:5px;font-size:small;}
				
				th:first-child,
				td:first-child {
				 padding-left: 10px;
				}
				
				th:last-child,
				td:last-child {
					padding-right: 5px;
				}
				</style>";	
	
	if ("1" == $strPass) {
		if ($doDebug) {
			echo "<br />Function starting. daysToSemester: $daysToSemester<br />";
		}
		$jobname		= "Student Mgmt: ";
		$content 		.= "<h3>$jobname Pass 1</h3>
							<p>Click on the links below to perform the indicated action:</p>
							<ol>
							<li><strong>Useful Functions Before Students are Assigned to Advisors</strong>
								<ol style='list-style-type: lower-alpha; padding-bottom: 0;'>

							<div>
							<div style='float:left;'>
								<li style='margin-left:2em;'><a href='$theURL?strpass=2' target='_blank'>Pre-assign 
							Student to an Advisor</a></div>
							<div style='float:right;'><a href=\"javascript:window.alert('If a 
							student has not been already pre-assigned to an advisor, stores the requested advisor and advisor class 
							in the student record. If the advisor has a class when assigning students to classes, the student will be 
							assigned to that advisor\'s class. Function is only effective before student assignment to advisors is performed.');\">
							<span style='color:orange;'><em>Note</em></span></div></div>

							<div style='clear:both;'>
							<div style='float:left;'>
								<li style='margin-left:2em;'><a href='$theURL?strpass=5' target='_blank'>Delete Student's 
							Pre-assigned Advisor</a></div>
							<div style='float:right;'><a href=\"javascript:window.alert('Removes the pre-assigned 
							advisor from a student record. Function is only effective before student assignment to advisors is performed.');\">
							<span style='color:orange;'><em>Note</em></span></div></div>

							<div style='clear:both;'>
							<div style='float:left;'>
								<li style='margin-left:2em;'><a href='$colorChartURL' target='_blank'><b>Color Chart</b> - Display 
							Student and Advisor Statistics</a></div>
							<div style='float:right;'><a href=\"javascript:window.alert('Displays 
							a chart of the number of students requesting classes at a certain time and the number of advisors offering 
							classes at that time. The list of students or advisors can be displayed and individual student or advisor 
							records updated. Function is only effective before student assignment to advisors is performed. Once the 
							student assignment to advisors is completed, use the Number One report.');\">
							<span style='color:orange;'><em>Note</em></span></span></div></div>

							<div style='clear:both;'>
							<div style='float:left;'>
								<li style='margin-left:2em;'><a href='$theURL?strpass=7' target='_blank'>List Students Needing Intervention</a></div>
							<div style='float:right;'><a href=\"javascript:window.alert('Displays any students in the upcoming semester that have some type if intervention required. 
							The program Resolve Student Hold is used to resolve the situation.');\">
							<span style='color:orange;'><em>Note</em></span></span></div></div>	

							<div style='clear:both;'>
							<div style='float:left;'>
								<li style='margin-left:2em;'><a href='$theURL?strpass=25' target='_blank'>Resolve Student 
							Hold<a/></div>
							<div style='float:right;'><a href=\"javascript:window.alert('Removes the intervention_required hold code 
							and sets the hold_override code so this hold will not be applied again to this student record. Function will have 
							no effect after students are assigned to advisors.');\">
							<span style='color:orange;'><em>Note</em></span></span></div></div>

							<div style='clear:both;'>
							<div style='float:left;'>
								<li style='margin-left:2em;'><a href='$theURL?strpass=100' target='_blank'>Verify One or More Students</a></div>
							<div style='float:right;'><a href=\"javascript:window.alert('The function will check that the each student is unassigned and not 
							on hold. Then the response is set to Y');\">
							<span style='color:orange;'><em>Note</em></span></span></div></div>
							
							<div style='clear:both;'>
							<div style='float:left;'>
								<li style='margin-left:2em;'><a href='$theURL?strpass=110' target='_blank'>Add Excluded Advisor to a Student</a></div>
							<div style='float:right;'><a href=\"javascript:window.alert('If the specified advisor is not excluded from the 
							specified student already, then the student record is updated with the specified excluded advisor');\">
							<span style='color:orange;'><em>Note</em></span></span></div></div>
							
							<div style='clear:both;'>
							<div style='float:left;'>
								<li style='margin-left:2em;'><a href='$theURL?strpass=120' target='_blank'>Remove Excluded Advisor from a Student</a></div>
							<div style='float:right;'><a href=\"javascript:window.alert('The specified advisor will be removed from all specified student 
							records');\">
							<span style='color:orange;'><em>Note</em></span></span></div></div>

							<div style='clear:both;'></div>
								</ol>
	
								<br />
							<li><strong>Useful Functions Any Time</strong>
								<ol style='list-style-type: lower-alpha; padding-bottom: 0;'>

							<div style='clear:both;'>
							<div style='float:left;'>
								<li style='margin-left:2em;'><a href='$studentHistoryURL' target='_blank'>Show Detailed History for a Student</a></div>
							<div style='float:right;'><a href=\"javascript:window.alert('For a specific student call sign, reads the student table and past_student table 
							and displays an entry for each record for that student.');\">
							<span style='color:orange;'><em>Note</em></span></span></div></div>	

							<div style='clear:both;'>
							<div style='float:left;'>
								<li style='margin-left:2em;'><a href='$changeSemesterURL' target='_blank'>Change unassigned student's semester</a></div>
							<div style='float:right;'><a href=\"javascript:window.alert('The program allows the student semester as well as class choices 
							to be updated. The student must be unassigned.');\">
							<span style='color:orange;'><em>Note</em></span></span></div></div>	

							<div style='clear:both;'>
							<div style='float:left;'>
								<li style='margin-left:2em;'><a href='$updateUnassignedInfoURL' target='_blank'>Update Unassigned Student Info</a> (class priority and/or class choices)</div>
							<div style='float:right;'><a href=\"javascript:window.alert('Allows a student to be selected by student ID, student name, 
							or student call sign (most common use). The information about the student is display and can be updated.');\">
							<span style='color:orange;'><em>Note</em></span></span></div></div>	

							<div style='clear:both;'>
							<div style='float:left;'>
								<li style='margin-left:2em;'><a href='$theURL?strpass=60' target='_blank'>Unassign and Remove a Student</a></div>
							<div style='float:right;'><a href=\"javascript:window.alert('This function removes ths student from an advisor class and sets the 
								student_status to C. Otherwise, sets the student response to R');\">
							<span style='color:orange;'><em>Note</em></span></span></div></div>	

							<div style='clear:both;'>
							<div style='float:left;'>
								<li style='margin-left:2em;'><a href='$theURL?strpass=20' target='_blank'>Exclude an Advisor from being Assigned to a Specific Student</a></div>
							<div style='float:right;'><a href=\"javascript:window.alert('This is the opposite of overriding an excluded advisor. The program places an X 
							in the Intervention Required field and and the advisor to be excluded in the excluded_advisor part of the student record.');\">
							<span style='color:orange;'><em>Note</em></span></span></div></div>	

								</ol><br /><br />
							<li><strong>Useful Functions After Students Have Been Assigned to Advisors</strong>
								<ol style='list-style-type: lower-alpha; padding-bottom: 0;'>

							<div style='clear:both;'>
							<div style='float:left;'>
								<li style='margin-left:2em;'><a href='$theURL?strpass=35' target='_blank'>Move Student to a Different Level and Unassign</a></div>
							<div style='float:right;'><a href=\"javascript:window.alert('The student must have a status of Y or S, otherwise no action will be taken 
							The student is unassigned from the current class, if already assigned. 
							The student level is changed to the desired level,and the student is returned to the unassigned pool.');\">
							<span style='color:orange;'><em>Note</em></span></span></div></div>	

							<div style='clear:both;'>
							<div style='float:left;'>
								<li style='margin-left:2em;'><a href='$theURL?strpass=40' target='_blank'>Add Unassigned Student to an Advisor's Class</a></div>
							<div style='float:right;'><a href=\"javascript:window.alert('The student call sign, advisor call sign, and the advisor and class where the student will be 
							assigned should be known. If the student is not unassigned, the function will fail.');\">
							<span style='color:orange;'><em>Note</em></span></span></div></div>	

							<div style='clear:both;'>
							<div style='float:left;'>
								<li style='margin-left:2em;'><a href='$theURL?strpass=50' target='_blank'>Unassign a Student Regardless of Status</a></div>
							<div style='float:right;'><a href=\"javascript:window.alert('This is a Brute Force function that will unassign a student 
							regardless of current situations.');\">
							<span style='color:orange;'><em>Note</em></span></span></div></div>	

							<div style='clear:both;'>
							<div style='float:left;'>
								<li style='margin-left:2em'><a href='$theURL?strpass=55' target='_blank'>Re-assign a Student to Another Advisor</a></div>
							<div style='float:right;'><a href=\"javascript:window.alert('The program removes the student from an advisor and reassigns 
							the student to a different advisor. The advisors must have classes at the students requested time.');\">
							<span style='color:orange;'><em>Note</em></span></span></div></div>	

							<div style='clear:both;'>
							<div style='float:left;'>
								<li style='margin-left:2em;'><a href='$theURL?strpass=70' target='_blank'>Find Possible Classes for a Student</a></div>
							<div style='float:right;'><a href=\"javascript:window.alert('The program takes a student current class choices and looks for any class that might match the 
							student class selection criteria. If a possibility is found, the matching information is displayed and the student can be 
							added to the requested class.');\">
							<span style='color:orange;'><em>Note</em></span></span></div></div>	

							<div style='clear:both;'>
							<div style='float:left;'>
								<li style='margin-left:2em;'><a href='$theURL?strpass=80' target='_blank'>Find Possible Students for an Advisor's Class</a></div>
							<div style='float:right;'><a href=\"javascript:window.alert('The program takes the class schedule from the requested advisor and 
							looks at all unassigned students to determine of one of them can be assigned to the advisor. The program considers student shoices 
							an hour earlier and an hour later.');\">
							<span style='color:orange;'><em>Note</em></span></span></div></div>	

							<div style='clear:both;'>
							<div style='float:left;'>
								<li style='margin-left:2em;'><a href='$theURL?strpass=90' target='_blank'>Assign a Student to an Advisor</a> regardless of status or semester</div>
							<div style='float:right;'><a href=\"javascript:window.alert('This function is used to pull a student from a future semester registration 
							and assigning the student to an advisor. ');\">
							<span style='color:orange;'><em>Note</em></span></span></div></div>	

							<div style='clear:both;'>
							<div style='float:left;'>
								<li style='margin-left:2em;'><a href='$theURL?strpass=85' target='_blank'>Confirm Attendance for One or More Students</a></div>
							<div style='float:right;'><a href=\"javascript:window.alert('The function will check that each 
							student is assigned to a class and has a student_status of S. If so, the student_status will be set to Y.');\">
							<span style='color:orange;'><em>Note</em></span></span></div></div>

							<div style='clear:both;'>
							</div>
								</ol>
							</ol>";

	} elseif ("2" == $strPass) {
		if ($doDebug) {
			echo "<br />at pass 2<br />";
		}
		$jobname		= "Student Management Pre-assign Student to an Advisor";
		$content .= "<h3>Pre-assign Student to an Advisor</h3>
					<p><form method='post' action='$theURL' 
					name='selection_form' ENCTYPE='multipart/form-data''>
					<input type='hidden' name='strpass' value='3'>
					<input type='hidden' name='inp_mode' value='$inp_mode'>
					<table style='border-collapse:collapse;'>
					<tr><th colspan='2'>Pre-Assign Student to an Advisor</th></tr>
					<tr><td style='width:150px;'>Student Call Sign</td>
						<td><input class='formInputText' type='text' name='inp_student_callsign' size='10' maxlenth='10' value='$inp_student_callsign' autofocus /></td></tr>
					<tr><td style='width:150px;'>Advisor Call Sign</td>
						<td><input class='formInputText' type='text' name='inp_advisor_callsign' size='10' maxlenth='10' value='$inp_advisor_callsign' /></td></tr>
					<tr><td style='vertical-alogn:top;'>Respect Student&apos;s language Preference?</td>
						<td><input type='radio' class='formInputButton' name='inp_use_language' value='Y' checked >Yes<br />
							<input type='radio' class='formInputButton' name='inp_use_language' value='N' checked >No</td></tr>
					<tr><td style='vertical-align:top;'>Additional Comments</td>
						<td><textarea class='formInputText' name='inp_additional' rows='5' cols='50'></textarea></td></tr>
					$testModeOption
					<tr><td>&nbsp;</td><td><input class='formInputButton' type='submit' value='Next' /></td></tr></table>
					</form>
					<br />
					<p>Enter the student call sign that is to be pre-assigned (must be 
					registered in the upcoming semester) and then 
					the advisor call sign to whom the student is to be assigned. If there are any add'l 
					comments to be added to the action_log, enter those as well. If the student is registered 
					and the advisor is registered, a list of the classes the advisor is teaching will be displayed. 
					Select the appropriate class for the student. After that the pre-assignment will be made.</p>
					<p>This function updates the \"pre_assigned_advisor\" field in the student's record. When the program is 
					run to do the student assignments to advisors, this student will be assigned to the 
					advisor first before any other unassigned students are put in the advisor's class.</p>";

	} elseif ("3" == $strPass) {
// $doDebug = TRUE;
		$jobname		= "Student Management Pre-assign Student to an Advisor";
		$content .= "<h3>$jobname</h3>";
		if ($doDebug) {
			echo "<br />At pass 3 with student $inp_student_callsign<br />
					advisor $inp_advisor_callsign<br />";
		}
					
		$doProceed = TRUE;
		if ($haveStudent) {
			// actualize the student information
			foreach($student as $thisField => $thisValue) {
				$$thisField = $thisValue;
			}
			if ($student_semester == $proximateSemester) {
				if ($doDebug) {
					echo "have the student in the proper semester<br />";
				}
			} else {
				if ($doDebug) {
					echo "have the student in the wrong semester $student_semester<br />";
				}
				$content .= "No student record for $inp_student_callsign in the $proximateSemester semester<br />";
				$doProceed = FALSE;
			}
		}
		if ($doProceed) {
			if ($haveAdvisor) {
				$advisor_semester = $advisor['advisor_semester'];
				if ($advisor_semester == $proximateSemester) {
					if ($doDebug) {
						echo "haveAdvisor is TRUE.<br />";
					}	
				} else {
					if ($doDebug) {
						echo "advisor is in the wrong semester: $advisor_semester<br />";
					}
					$content .= "No advisor record for $inp_advisor_callsign in $proximateSemester<br />";
				}
			} else {
				$content .= "<p>No advisor record obtained for $inp_student_callsign</p>";
			}
		}
		if ($haveStudent) {
			if ($student_response == '') {
				$responseSTR	= 'blank';
			} else {
				 $responseSTR	= $student_response;
			}
			if ($student_response != 'Y') {
				$content 		.= "<p><b>NOTE:</b> Student is not validated (response = $responseSTR)</p>";
			}
		} else {
			$content		.= "<p>No student record found for $inp_student_callsign</p>";
		}
		// log that the job was run
		$thisDate			= date('Y-m-d');
		$thisTime			= date('H:i:s');

		// do we have student and advisor records?
		
		if ($haveStudent && $haveAdvisor && $student_semester == $advisor_semester) {
			if ($doDebug) {
				echo "have most of the data. Continuing verification checks<br />";
			}

			$gotError				= FALSE;
			if ($student_pre_assigned_advisor != '') {
				if ($doDebug) {
					echo "Student has a pre-assigned advisor already<br />";
				}
				$content			.= "<p>Student $inp_student_callsign already has a pre-assigned advisor of $student_pre_assigned_advisor. 
										Please unassign this advisor before making a new pre-assignment.<p>";
				$gotError			= TRUE;
			}
			if ($student_email_number == 4 && $student_response != 'Y') {
				if ($doDebug) {
					echo "Student has been dropped<br />";
				}
				$content			.= "<p>Student $inp_student_callsign failed to respond to verification requests and has been dropped. Pre-assignment refused.</p>";
				$gotError			= TRUE;
			}
			if ($student_response == 'R') {
				if ($doDebug) {
					echo "Student has refused the verification<br />";
				}
				$content			.= "<p>Student $inp_student_callsign responded 'refused' to the verification request. Pre-assignement refused.</p>";
				$gotError			= TRUE;
			}
			if ($str_contains($student_excluded_advisor,$inp_advisor_callsign)) {
				if ($doDebug) {
					echo "$inp_advisor_callsign is excluded<br />";
				}
				$content .= "<p>$inp_advisor_callsign is an excluded advisor</p>";
				$gotError = TRUE;
			}
			if (!$gotError) {
				if ($doDebug) {
					echo "no errors. Checking advisor<br />";
				}
				// get the classes for the advisor
				$optionList				= '';
				$optionCount			= 0;
				$listOptionClass		= 0;
				$student_class_language = $student['student_class_language'];
				$languageCheck = $languageConversion[$student_class_language];
				
				if ($inp_use_language == 'Y') {
					$criteria = [
						'relation' => 'AND',
						'clauses' => [
							['field' => 'advisorclass_call_sign', 'value' => $inp_advisor_callsign, 'compare' => '=' ],
							['field' => 'advisorclass_semester', 'value' => $proximateSemester, 'compare' => '=' ],
							['field' => 'advisorclass_language', 'value' => "%$languageCheck%", 'compare' => 'like' ],
							['field' => 'advisorclass_level', 'value' => $student['student_level'], 'compare' => '=' ]
						]
					];
				} else {
					$criteria = [
						'relation' => 'AND',
						'clauses' => [
							['field' => 'advisorclass_call_sign', 'value' => $inp_advisor_callsign, 'compare' => '=' ],
							['field' => 'advisorclass_semester', 'value' => $proximateSemester, 'compare' => '=' ],
							['field' => 'advisorclass_level', 'value' => $student['student_level'], 'compare' => '=' ]
						]
					];
				}
				$advisorClassData = $advisorclass_dal->get_advisorclasses_by_order( $criteria, 'advisorclass_sequence', 'ASC', $operatingMode );
				if ($advisorClassData === FALSE || $advisorClassData == NULL) {
					if ($doDebug) {
						echo "get_advisorclasses_by_order returned FALSE|NULL<br />";
					}
					$content	.= "Advisor $inp_advisor_callsign does not have a $student_level class in the $proximateSemester semester in the $student_class_language language.";
					$gotError = TRUE;
				} else {
					if (! empty($advisorClassData)) {
						$numACRows = count($advisorClassData);
						foreach($advisorClassData as $key => $value) {
							foreach($value as $thisField => $thisValue) {
								$$thisField = $thisValue;
							}

							$thisChecked	= '';
							if ($numACRows == 1) {
								$thisChecked	= ' checked ';
							}
							$optionList		.= "<input type='radio' class='formInputButton' name='inp_advisorclass' value='$advisorclass_sequence' $thisChecked required> Class nbr $advisorclass_sequence $advisorclass_level at $advisorclass_class_schedule_times local on $advisorclass_class_schedule_days<br />";
							$optionCount++;
							$lastOptionClass	= $advisorclass_sequence;
						}
						$myStudentid = $student_id;
						$myAdvisorid = $advisor['advisor_id'];
						$content	.= "<p>Select the class to which the student $inp_student_callsign is to  be assigned and click 'Next'</p>
										<p><form method='post' action='$theURL' 
										name='selection_form' ENCTYPE='multipart/form-data''>
										<input type='hidden' name='strpass' value='4'>
										<input type='hidden' name='inp_student_callsign' value='$inp_student_callsign'>
										<input type='hidden' name='inp_studentid' value='$myStudentid']>
										<input type='hidden' name='inp_advisor_callsign' value='$inp_advisor_callsign'>
										<input type='hidden' name='inp_advisorid' value='$myAdvisorid'>
										<input type='hidden' name='inp_semester' value='$proximateSemester'>
										<input type='hidden' name='inp_additional' value='$inp_additional'>
										<input type='hidden' name='inp_mode' value='$inp_mode'>
										<input type='hidden' name='inp_verbose' value='$inp_verbose'>
										<table style='border-collapse:collapse;'>
										<tr><th colspan='2'>Pre-Assign Student to Advisor $inp_advisor_callsign's Class</th></tr>
										<tr><td style='width:150px;'>Advisor Class(es)</td><td>
										$optionList
										</td></tr>
										<tr><td>&nbsp;</td><td><input class='formInputButton' type='submit' value='Next' /></td></tr></table>
										</form></p>";
					} else {
						if ($doDebug) {
							echo "No advisorclass recprds found with the call sign $inp_advisor_callsign in the $proximateSemester semester in level $student_level in the $student_class_language language<br />";
						}
						$content	.= "Advisor $inp_advisor_callsign does not have a $student_level class in the $proximateSemester semester.";
					}
				}
			}
		}


////////////// pass 4

	
	} elseif ("4" == $strPass) {
		if ($doDebug) {
			echo "<br />at pass 4 with inp_student_callsign of $inp_student_callsign (id: $inp_studentid)<br />
					inp_advisor_callsign of $inp_advisor_callsign<br />";
			if ($haveStudent) {
				echo "haveStudent is TRUE<br />";
			} else {
				echo "haveStudent is FALSE<br />";
			}
			if ($haveAdvisor) {
				echo "haveAdvisor is TRUE<br />";
			} else {
				echo "haveAdvisor is FALSE<br />";
			}
		}
		// do the actual pre-assignment

// $doDebug	= TRUE;

		$jobname			= "Student Management Pre-assign Student to an Advisor";
		$content			.= "<h3>$jobname</h3>";

		if ($haveStudent && $haveAdvisor) {			
			// set up and do the pre-assignment
			$updateParams	= array();
			$student_action_log = $student['student_action_log'];
			$student_action_log .= " / $actionDate MGMT1 $userName pre-assigned advisor $inp_advisor_callsign ";
			$updateParams['student_pre_assigned_advisor'] 	= $inp_advisor_callsign;
			$updateParams['student_assigned_advisor_class']	= $inp_advisorclass;
			if ($inp_additional != '') {
				$student_action_log					.= " $inp_additional ";
			}
			$updateParams['student_action_log'] = $student_action_log;
			$updateResult = $student_dal->update( $inp_studentid, $updateParams, $operatingMode );
			if ($updateResult === 'FALSE' || $updateResult === NULL) {
				if ($doDebug) {
					echo "updating $inp_student_callsign ($inp_studentid) returned FALSE|NULL<br />";
				}
			} else {
				$content	.= "<p>Student $inp_student_callsign now has advisor $inp_advisor_callsign pre-assigned and advisor
								has a class at the student's level.</p>";
			}
		}
		$content	.= "<p>Click <a href='$theURL?strpass=2'>here</a> to do another pre-assignment.</p>";





////  pass 5   Delete a pre-assigned advisor

	} elseif ("5" == $strPass) {
		if ($doDebug) {
			echo "<br />At pass 5<br />";
		}
	
		$jobname	= "Student Management Delete a Pre-assigned Advisor";
		$content 	.= "<h3>$jobname</h3>
						<p>Enter the student call sign that has the pre-assigned advisor.<p>
						<p>This process works until the program is run to assign students to 
						advisors and make up classes. Once that has happened, using this function 
						will have no effect.</p>
						<p><form method='post' action='$theURL' 
						name='selection_form' ENCTYPE='multipart/form-data''>
						<input type='hidden' name='strpass' value='6'>
						<input type='hidden' name='inp_mode' value='$inp_mode'>
						<input type='hidden' name='inp_semester' value='$proximateSemester'>
						<table style='border-collapse:collapse;'>
						<tr><th colspan='2'>Delete Student's Pre-assigned Advisor</th></tr>
						<tr><td style='width:150px;'>Student Call Sign</td><td>
						<input class='formInputText' type='text' name='inp_student_callsign' size='10' maxlenth='10' /></td></tr>
						<tr><td>&nbsp;</td><td><input class='formInputButton' type='submit' value='Make the Deletion' /></td></tr></table>
						</form>
						<br />
						<p>This function updates the 'pre_assigned_advisor' field in the student's record. When the program is 
						run to do the student assignments to advisors, this student will be handled as a normal 
						unassigned student.</p>";


/////// Pass 6			Delete the pre-assigned advisor


	} elseif ("6" == $strPass) {
		if ($doDebug) {
			echo "<br />at pass 6 with inp_student_callsign of $inp_student_callsign<br />";
			if ($haveStudent) {
				echo "haveStudent is TRUE<br />";
			} else {
				echo "haveStudent is FALSE<br />";
			}
		}
	
		// do the actual deletion
		$jobname				= "Student Management Delete a Pre-assigned Advisor";
		$content .= "<h3>$jobname</h3>";	
		if ($inp_student_callsign == '') {
			$content 			.= "No student call sign entered. Process aborted.";
		} else {
			// log that the job was run
			$thisDate			= date('Y-m-d');
			$thisTime			= date('H:i:s');

			if ($haveStudent) {	
				// actualize the student data
				foreach($student as $key => $value) {
					$$key = $value;
				}
				if ($doDebug) {
					echo "Retrieved $inp_student_callsign with pre-assigned advisor of $myPreAssignedAdvisor<br />";
				}
				
				// don't do anything if there is an assigned_advisor
				if ($student_assigned_advisor != '') {
					$student_action_log .= " / $actionDate MGMT2 $userName deleted pre-assigned advisor $myPreAssignedAdvisor ";
					$updateParams							= array();
					$updateParams['student_intervention_required']	= '';
					$updateParams['student_pre_assigned_advisor']	= '';
					$updateParams['student_assigned_advisor_class']	= '0';
					$updateParams['student_action_log']	= $student_action_log;
					$updateResult = $student_dal->update( $myStudentid, $updateParams, $operatingMode );		
					if ($updateResult === FALSE || $updateResult === NULL) {
						if ($doDebug) {
							echo "attempting to update $inp_student_callsign ($my_studentid) returned FALSE|NULL<br />";
						}
					} else {
						$content	.= "<p>Pre-assigned advisor removed from student $inp_student_callsign</p>";
					}
				} else {
					$content .= "<p>Student $inp_student_callsign has an assigned advisor of 
								$student_assigned_advisor. Pre-assigning an advisor is no longer
								useful and was not done</p>";
				}
			} else {
				$content	.= "No student with call sign $inp_student_callsign found<br />";
			}
		}


////  	pass 7	List student needing intervention		
	} elseif ("7" == $strPass) {

		$jobname			= "Student Management List Students Needing Intervention";
		$content			.= "<h3>$jobname in the $proximateSemester Semester</h3>
								<p>Click 'List Students Needing Intervention' to run the report</p><table>
								<form method='post' action='$theURL' 
								name='selection_form' ENCTYPE='multipart/form-data''>
								<input type='hidden' name='strpass' value='8'>
								<input type='hidden' name='inp_mode' value='$inp_mode'>
								$testModeOption
								<tr><td><input class='formInputButton' type='submit' value='List Students Needing Intervention' /></td></tr></table>
								</form>";
		
	} elseif ("8" == $strPass) {
		
	///// list students needing intervention assistance
		$jobname			= "Student Management List Students Needing Intervention";
		$myInt = 0;

		//	get the student records
		$criteria = [
			'relation' => 'AND',
			'clauses' => [
				['field' => 'student_intervention_required', 'value' => '', 'compare' => '!=' ],
				['field' => 'student_semester', 'value' => $proximateSemester, 'compare' => '=' ],
				['field' => 'student_hold_reason_code', 'value' => 'X', 'compare' => '!=' ]
			]
		];
		$studentResult = $student_dal->get_student_by_order( $criteria, 'student_call_sign', 'ASC', $operatingMode );
		if ($studentResult === FALSE || $studentResult === NULL) {
			if ($doDebug) {
				echo "getting students needing intervention returned FALSD|NULL<br />";
			}
			$content .= "<p>Function failed</p>";
		} else {
			if (! empty($studentResult)) {
				$content	.= "<h3>Students Needing Intervention</h3>
								<table><tr>
									<th>ID</th>
									<th>Name</th>
									<th>Email</th>
									<th>Phone</th>
									<th>City</th>
									<th>State</th>
								</tr><tr>
									<th>Time Zone</th>
									<th>Level</th>
									<th>Semester</th>
									<th>Response</th>
									<th>Status</th>
									<th>Advisor</th>
								</tr><tr>
									<th>Type Intervention</th>
									<th colspan='5'>Reason</th>
								</tr>";
				foreach($studentResult as $key => $value) {
					foreach($value as $thisField => $thisValue) {
						$$thisField = $thisValue;
					}
					// get the user_master
					$userData = $user_dal->get_user_master_by_callsign( $student_call_sign, $operatingMode );
					if ($userData === FALSE || $userData === NULL) {
						if ($doDebug) {
							echo "getting the user master for $student_call_sign returned FALSE|NULL<br />";
						}
						$content .= "<p>Fatal error</p>";
					} else {
						if (! empty($userData)) {
							foreach($userData as $key => $value) {
								foreach($value as $thisField => $thisValue) {
									$$thisField = $thisValue;
								}
								if ($doDebug) {
									echo "Processing call sign $student_call_sign. Intervention Required: $student_intervention_required; hold_reason_code: $student_hold_reason_code<br />";
								}
								// don't worry about hold_reason_code = A
								if ($student_hold_reason_code != 'A') {
									if ($student_intervention_required == 'H') {   	// only interested in those requiring intervention
										$IRType				= '';
										$IRReason			= '';
										if ($student_hold_reason_code == 'M') {
											$IRType			= "M: Moved";
											$IRReason		= "unknown";
										}
										if ($student_hold_reason_code == 'Q') {
											$IRType			= "Q: Advisor Quit";
											$IRReason		= getTheReason($student_hold_reason_code);
										}
										if ($student_hold_reason_code == 'H') {
											$IRType			= "H: Assignment on Hold";
											$IRReason		= getTheReason($student_hold_reason_code);
										}
										if ($student_hold_reason_code == 'E') {
											$IRType			= "H: Student on Hold";
											$IRReason		= "(E) Student not evaluated but asking for next class level";
										}
										if ($student_hold_reason_code == 'W') {
											$IRType			= "H: Student on Hold";
											$IRReason		= "(W) Student withdrew but asking for next class level";
										}
										if ($student_hold_reason_code == 'L') {
											$IRType			= "H: Student on Hold";
											$IRReason		= "(L) Student wants a class in a language that is not available";
										}
										$thisAdvisor		= '';
										if ($student_assigned_advisor != '') {
											$thisAdvisor	= $student_assigned_advisor;
										} else {
											$thisAdvisor		= $student_pre_assigned_advisor;
										}
										$newActionLog		= formatActionLog($student_action_log);
										$studentLink	= "<a href='$siteURL/cwa-display-and-update-student-signup-information/?strpass=2&request_type=callsign&request_info=$student_call_sign&inp_depth=one&doDebug&testMode' target='_blank'>$student_id</a>";
										$content	.= "<tr><td>$studentLink</td>
															<td>$user_last_name, $user_first_name, ($student_call_sign)</td>
															<td>$user_email</td>
															<td>$user_phone</td>
															<td>$user_city</td>
															<td>$user_state</td>
														</tr><tr>
															<td>$user_timezone_id $student_timezone_offset</td>
															<td>$student_level</td>
															<td>$student_semester</td>
															<td>$student_response</td>
															<td>$student_status</td>
															<td>$thisAdvisor</td>
														</tr><tr>
															<td colspan='2'>$IRType</td>
															<td colspan='4'>$IRReason</td>
														</tr><tr>
															<td colspan='6'>$newActionLog</td>
														</tr><tr>
															<td colspan='6'><hr /></td>
														</tr>";
										$myInt++;
									}
								}
							}
						} else {
							$content .= "<p>No user_master record found</p>";
						}
					}
				}
				$content	.= "</table>$myInt records displayed<br />";
			} else {			// no records in the table
				$content 	.= "No students needing intervention records found in $studentTableName table<br />";
			}
		}
		

		
//// 	pass 20		Exclude an advisor from being assigned to a specific student		
		
	} elseif ("20" == $strPass) {
		if ($doDebug) {
			echo "<br />At pass 20<br />";
		}

		$jobname		= "Student Management Exclude an Advisor";
		$content 		.= "<h3>$jobname from being Assigned to a Specific Student</h3>
							<p>Enter the student call sign of the record to be updated and the advisor 
							call sign to be excluded from assignment to the student.</p>
							<p><form method='post' action='$theURL' 
							name='selection_form' ENCTYPE='multipart/form-data''>
							<input type='hidden' name='strpass' value='21'>
							<input type='hidden' name='inp_semester' value='$proximateSemester'>
							<table style='border-collapse:collapse;'>
							<tr><th colspan='2'>Exclude an Advisor</th></tr>
							<tr><td style='width:150px;'>Student Call Sign</td><td>
							<input class='formInputText' type='text' name='inp_student_callsign' size='10' maxlenth='10' /></td></tr>
							<tr><td style='width:150px;'>Advisor Call Sign to be Excluded</td><td>
							<input class='formInputText' type='text' name='inp_advisor_callsign' size='10' maxlenth='10' /></td></tr>
							$testModeOption
							<tr><td>&nbsp;</td><td><input class='formInputButton' type='submit' value='Make the Exclusion' /></td></tr></table>
							</form>";


////////	pass 21 	Do the actual advisor exclusion

	} elseif ("21" == $strPass) {
		if ($doDebug) {
			echo "<br />at pass 21 with inp_student_callsign of $inp_student_callsign<br />
					inp_advisor_callsign of $inp_advisor_callsign<br />";
			if ($haveStudent) {
				echo "haveStudent is TRUE<br />";
			} else {
				echo "haveStudent is FALSE<br />";
			}
			if ($haveAdvisor) {
				echo "haveAdvisor is TRUE<br />";
			} else {
				echo "haveAdvisor is FALSE<br />";
			}
		}
	
		$jobname			= "Student Management Exclude an Advisor";

		if ($haveStudent) {				
			if ($doDebug) {
				echo "Processing student $student_call_sign<br />
					  &nbsp;&nbsp;&nbsp;Advisor: $student_advisor<br />
					  &nbsp;&nbsp;&nbsp;Excluded Advisor: $student_assigned_advisor<br />
					  &nbsp;&nbsp;&nbsp;Hold Reason Code: $student_hold_reason_code<br />
					  &nbsp;&nbsp;&nbsp;Intervention Required: $student_intervention_required<br />";
			}
			$daysToSemester	= days_to_semester($student_semester);
			if ($daysToSemester < 19) { 				// student assignment to advisor has probably been run
				$content .= "<p>Since the process to assign students to an advisor has been run
							for this semester, excluding an advisor will have no affect. 
							This process only works 
							until the program is run to assign students to 
							advisors and make up classes. Once that has happened, using this function 
							to attempt to exclude an advisor will have no effect. The appropriate action may be to click on 
							<a href='$theURL?strpass=45'>Remove Student from an Advisor's Class</a></p>";
			} else {
				$student_excluded_advisor = $student['student_excluded_advisor'];
				$student_action_log = $student['student_action_log'];
				$isExcluded		= FALSE;
				if ($student_excluded_advisor != '') {
					/// see if the advisor is already excluded
					if (str_contains($student_excluded_advisor,$inp_advisor_callsign)) {
						$content .= "<p>Student already has $inp_advisor_callsign  excluded advisor. Process aborted.</p>";
						$isExcluded	= TRUE;
					}
				}
				if (!$isExcluded) {	
					$student_action_log	.= " / actionDate MGMT3 $userName Excluded advisor $inp_advisor_callsign from being assigned to student ";
					$student_excluded_advisor = updateExcludedAdvisor($student_excluded_advisor, $inp_advisor_callsign,'add', $doDebug);
					if ($student_excluded_advisor === FALSE) {
						if ($doDebug) {
							echo "updateExcludedAdvisor returned FALSE<br />";
						}
						$content .= "Adding $inp_advisor_callsign to $student_excluded_advisor failed<br />";
					} else {
						$student_id = $student['student_id'];
						$updateParams	= array('student_excluded_advisor'=>"$student_excluded_advisor",
												'student_action_log'=>"$student_action_log");
						$updateresult = $student_dal->update($student_id,$updateParams,$operatingMode);
						if ($updateResult === FALSE || $updateResult === NULL) {
							if ($doDebug) {
								echo "update $inp_student_callsign exluded advisor failed<br />";
							}
							$content .= "<p>Fatal Error</p>";
						} else {
							$content .= "<p>Student record updated to exclude advisor $inp_advisor_callsign.</p>";
						}
					}
				}
			}
		} else {
			if ($doDebug) {
				echo "No record found in $studentTableName table for $inp_student_callsign<br />";
			}
			$content			.= "No record found in $studentTableName table for $inp_student_callsign";
		}



		
///// 	pass 25		Resolve Student Hold		
		
		
	} elseif ("25" == $strPass) {
		if ($doDebug) {
			echo "<br />at pass 25<br />";
		}
	
// $testMode	= TRUE;
		$jobname		= "Student Management Resolve Student Hold";
		$content 		.= "<h3>$jobname</h3>
							<p>Enter the student call sign below. The hold information will be displayed after which 
							you can decide to proceed to remove the hold information.</p>
							<form method='post' action='$theURL' 
							name='selection_form' ENCTYPE='multipart/form-data'>
							<input type='hidden' name='strpass' value='26'>
							<table style='border-collapse:collapse;'><tr><td style='width:150px;'>Student Call Sign</td><td>
							<input class='formInputText' type='text' maxlength='30' name='inp_student_callsign' size='10' autofocus></td></tr>
							$testModeOption
							<tr><td>&nbsp;</td><td><input class='formInputButton' type='submit' value='Submit' /></td></tr></table>
							</form>";

/////	pass 26		display student hold information

	} elseif ("26" == $strPass) {
		if ($doDebug) {
			echo "<br />at pass 26 with inp_student_callsign of $inp_student_callsign<br />";
			if ($haveStudent) {
				echo "haveStudent is TRUE<br />";
			} else {
				echo "haveStudent is FALSE<br />";
			}
		}

// $testMode	= TRUE;
		$jobname				= "Student Management Resolve Student Hold";
		if ($haveStudent) {
			// actualize the student data
			foreach($student as $thisField => $thisValue) {
				$$thisField = $thisValue;
			}
		
		
			if ($doDebug) {
				echo "found intevention_required of $student_intervention_required<br />";
			}
			if ($student_intervention_required != '') {   	// only interested in those requiring intervention
				$content	.= "<h3>$jobname on $inp_student_callsign</h3>
								<table><tr>
									<th>ID</th>
									<th>Name</th>
									<th>Email</th>
									<th>Phone</th>
									<th>City</th>
									<th>State</th>
								</tr><tr>
									<th>Time Zone</th>
									<th>Level</th>
									<th>Semester</th>
									<th>Response</th>
									<th>Status</th>
									<th>Pre-assigned<br />Advisor</th>
								</tr><tr>
									<th>Type Intervention</th>
									<th colspan='5'>Reason</th>
								</tr>";

				$IRType				= '';
				$IRReason			= '';
				if ($student_intervention_required == 'M') {
					$IRType			= "Moved";
					$IRReason		= "unknown";
				}
				if ($student_intervention_required == 'Q') {
					$IRType			= "Advisor Quit";
					$IRReason		= getTheReason($student_hold_reason_code);
				}
				if ($student_intervention_required == 'H') {
					$IRType			= "Assignment on Hold";
					$IRReason		= getTheReason($student_hold_reason_code);
				}
				$newActionLog		= formatActionLog($student_action_log);
				$content	.= "<p>Click on 'Delete this Hold' to remove the hold.
								<form method='post' action='$theURL' 
								name='selection_form' ENCTYPE='multipart/form-data'>
								<input type='hidden' name='strpass' value='27'>
								<input type='hidden' name='studentid' value='$student_id'>
								<input type='hidden' name='inp_mode' value='$inp_mode'>
								<input type='hidden' name='inp_verbose' value='$inp_verbose'>
								<tr><td>$student_id</td>
									<td>$user_last_name, $user_first_name, ($student_call_sign)</td>
									<td>$user_email</td>
									<td>$user_phone</td>
									<td>$user_city</td>
									<td>$user_state</td>
								</tr><tr>
									<td>$user_timezone_id $student_timezone_offset</td>
									<td>$student_level</td>
									<td>$student_semester</td>
									<td>$student_response</td>
									<td>$student_status</td>
									<td>$student_pre_assigned_advisor</td>
								</tr><tr>
									<td>$IRType</td>
									<td colspan='5'>$IRReason</td>
								</tr><tr>
									<td colspan='6'>$newActionLog</td>
								</tr><tr>
									<td colspan='6'><hr /></td>
								</tr></table>
								<input type='submit' class='formInputButton' name='Delete this Hold' value='Delete this Hold'
								onclick=\"return confirm('Are you sure you want to delete this hold information?');\">
								</form></p>";
			} else {
				$content	.= "<p>The student $student_call_sign is not on hold. No action taken.</p>";
			}
		} else {
			if ($doDebug) {
				echo "no record found in $studentTableName table for $inp_student_callsign<br />";
			}
			$content		.= "No record found in $studentTableName table for $inp_student_callsign";
		}
		
		
		
////////	pass 27		Do the hold removal
	} elseif ("27" == $strPass) {
		if ($doDebug) {
			echo "<br />at pass 27 with studentid of $inp_studentid<br />";
			if ($haveStudent) {
				echo "haveStudent is TRUE<br />";
			} else {
				echo "haveStudent is FALSE<br />";
			}
		}
	
// $testMode	= TRUE;
//   $doDebug		= TRUE;
		$jobname			= "Student Management Resolve Student Hold";
		$content .= "<h3>$jobname</h3>";
		if ($haveStudent) {
			// actualize the student data
			foreach($student as $thisField => $thisValue) {
				$$thisField = $thisValue;
			}
			
			$updateParams							= array();
			$updateParams['student_intervention_required']	= '';
			$updateParams['student_hold_override']			= 'Y';
			$doUpdate								= TRUE;
			$student_action_log 					.= " / $actionDate MGMT27 $userName removed hold_reason_code of $student_hold_reason_code ";
			$updateParams['student_action_log']				= $student_action_log;

			$updateResult = $student_dal->update($student_id, $updateParams, $operatingMode);
			if ($updateResult === FALSE || $updateResult === NULL) {
				if ($doDebug) {
					echo "updating $student_id for $student_call_sign returned FALSE|NULL<br />";
				}
				$content .= "<p>Update failed</p>";
			} else {
				$content			.= "Hold is removed from $student_call_sign.<br />"; 
			}
		} else {
			$content	.= "Did not retrieve any records with student ID of $inp_studentid";
		}


		
//////		Pass 35		Change a student level and put in unassigned pool
	} elseif ("35" == $strPass) {
	
		if ($doDebug) {
			echo "<br />at pass 35<br />";
		}
		$jobname		= "Student Management Move Student to a Different Level";
		$content 		.= "<h3>$jobname</h3>
							<p>Enter the student call sign below and select the new level for the student. 
							If the student is currently assigned to an advisor's class, the student will be 
							removed from the class. The student status will be set to blank, meaning the 
							student is unassigned and available for assignment.</p>
							<form method='post' action='$theURL' 
							name='selection_form' ENCTYPE='multipart/form-data'>
							<input type='hidden' name='strpass' value='36'>
							<table style='border-collapse:collapse;'><tr><td style='width:150px;'>Student Call Sign:</td><td>
							<input class='formInputText' type='text' maxlength='30' name='inp_student_callsign' size='10' autofocus></td></tr>
							<tr><td style='width:150px;'>New Level:</td><td>
							<input type='radio' class='formInputButton' name='level' value='Beginner' required>Beginner<br />
							<input type='radio' class='formInputButton' name='level' value='Fundamental' required>Fundamental<br />
							<input type='radio' class='formInputButton' name='level' value='Intermediate' required>Intermediate<br />
							<input type='radio' class='formInputButton' name='level' value='Advanced' required>Advanced</td></tr>
							$testModeOption
							<tr><td>&nbsp;</td><td><input class='formInputButton' type='submit' value='Submit' /></td></tr></table>
							</form>";



	} elseif ("36" == $strPass) {				// do the actual level move
		if ($doDebug) {
			echo "<br />at pass 36 with inp_student_callsign of $inp_student_callsign<br />";
			if ($haveStudent) {
				echo "haveStudent is TRUE<br />";
			} else {
				echo "haveStudent is FALSE<br />";
			}
		}
// $testMode	= TRUE;
// $doDebug		= TRUE;

		$jobname		= "Student Management Move Student to a Different Level";
		$content		.= "<h3>$jobname</h3>";
		if ($inp_level == '') {
			$content	.= "The level to which the student is to be moved was not specified.";
		} else {
			if ($haveStudent) {
				$doUnassign			= FALSE;
				$changeLevel		= FALSE;
				
				// actualize the student data
				foreach($student as $thisField => $thisValue) {
					$$thisField = $thisValue;
				}

				if ($doDebug) {
					echo "Student call sign: $student_call_sign<br />
						  &nbsp;&nbsp;&nbsp;Response: $student_response<br />
						  &nbsp;&nbsp;&nbsp;Status: $student_status<br />
						  &nbsp;&nbsp;&nbsp;Assigned Advisor: $student_assigned_advisor<br />";
				}

				if ($inp_level == $student_level) {
					if ($student_status == 'Y') {
						$content	.= "<p>Student is currently at the requested level of $inp_level, is
										assigned to $student_assigned_advisor's class. The level
										will remain unchanged but $student_call_sign will be removed 
										from the class and returned to the unassigned pool.</p>";
						$doUnassign	= TRUE;
					}
					if ($student_status == 'S') {
						$content	.= "<p>Student is currently at the requested level of $inp_level, and
										while assigned to $student_assigned_advisor, the advisor has not
										verified the student. Student will be moved to the unassigned pool.</p>";
						$doUnassign	= TRUE;
					}
					if ($student_status == 'C') {
						$content	.= "<p>Student is currently at the requested level of $inp_level. The student
										was already removed from $student_assigned_advisor's class and replaced. 
										No action taken.</p>";
						if ($doDebug) {
							echo "&nbsp;&nbsp;&nbsp;No action<br />";
						}
					}
					if ($student_status == 'N') {
						$content	.= "<p>Student is currently at the requested level of $inp_level. The student
										was already removed from $student_assigned_advisor's class. 
										No action taken.</p>";
						if ($doDebug) {
							echo "&nbsp;&nbsp;&nbsp;No action<br />";
						}
					}
					if ($student_status == 'R') {
						$content	.= "<p>Student is currently at the requested level of $inp_level. The student
										was already removed from $student_assigned_advisor's class and a replacement
										requested. No action taken.</p>";
						if ($doDebug) {
							echo "&nbsp;&nbsp;&nbsp;No action<br />";
						}
					}
					if ($student_status == '') {			// unassigned
						$content	.= "<p>Student is currently at the requested level of $inp_level. The student 
										is also unassigned. No action taken.</p>";
					}
						if ($doDebug) {
							echo "&nbsp;&nbsp;&nbsp;No action<br />";
						}

				} else {				// student level is not same as requested level

					if ($student_status == 'Y') {
						$content	.= "<p>Student is assigned to $student_assigned_advisor's class. The level
										will be changed from $student_level to $inp_level, $student_call_sign will be removed 
										from the class, and returned to the unassigned pool.</p>";
						$doUnassign		= TRUE;
						$changeLevel	= TRUE;
					}
					if ($student_status == 'S') {
						$content	.= "<p>Student is assigned to $student_assigned_advisor, but the advisor has not
										verified the student. Student will be moved to the unassigned pool 
										and the level changed from $student_level to $inp_level.</p>";
						$doUnassign		= TRUE;
						$changeLevel	= TRUE;
					}
					if ($student_status == 'C') {
						$content	.= "<p>The student was already removed from 
										$student_assigned_advisor's class and replaced. 
										No action needs to be taken.</p>";
						if ($doDebug) {
							echo "&nbsp;&nbsp;&nbsp;No action<br />";
						}
					}
					if ($student_status == 'N') {
						$content	.= "<p>The student was already removed from 
										$student_assigned_advisor's class and replaced. 
										No action needs to be taken.</p>";
						if ($doDebug) {
							echo "&nbsp;&nbsp;&nbsp;No action<br />";
						}
					}
					if ($student_status == 'R') {
						$content	.= "<p>The student was already removed from 
										$student_assigned_advisor's class and replaced. 
										No action needs to be taken.</p>";
						if ($doDebug) {
							echo "&nbsp;&nbsp;&nbsp;No action<br />";
						}
					}
					if ($student_status == '') {
						$content	.= "<p>The student is unassigned. Level will be changed from
										$student_level to $inp_level.</p>";
						$doUnassign		= TRUE;
						$changeLevel	= TRUE;
						if ($doDebug) {
							echo "&nbsp;&nbsp;&nbsp;Level changed.<br />";
						}	
					}
				}
				if ($changeLevel) {
					$student_action_log .= " / $actionDate $jobname $userName Level changed to $inp_level ";
					$updateParams		= array('student_level'=>$inp_level,
												'student_action_log'=>$student_action_log);
					$updateResult = $student_dal->update($student_id, $updateParams, $operatingMode);
					if ($updateResult === FALSE || $updateResult === NULL) {
						if ($doDebug) {
							echo "attempting toudpate student $student_id responded with FALSE|NULL<br />";
						}
					} else {
						$content		.= "Student level updated to $inp_level<br />";
					}
				}

				if ($doUnassign) {
					if ($student_assigned_advisor != '') {
						$inp_data			= array('inp_student'=>$student_call_sign,
													'inp_semester'=>$student_semester,
													'inp_assigned_advisor'=>$student_assigned_advisor,
													'inp_assigned_advisor_class'=>$student_assigned_advisor_class,
													'inp_remove_status'=>'',
													'inp_arbitrarily_assigned'=>$student_no_catalog,
													'inp_method'=>'remove',
													'jobname'=>$jobname,
													'userName'=>$userName,
													'testMode'=>$testMode,
													'doDebug'=>$doDebug);
							
						$removeResult		= add_remove_student($inp_data);
						if ($removeResult[0] === FALSE) {
							$thisReason		= $removeResult[1];
							if ($doDebug) {
								echo "attempting to remove $student_call_sign from $student_assigned_advisor class failed:<br />$thisReason<br />";
							}
							sendErrorEmail("$jobname Attempting to remove $student_call_sign from $student_assigned_advisor class failed:<br />$thisReason");
							$content		.= "Attempting to remove $student_call_sign from $student_assigned_advisor class failed:<br />$thisReason<br />";
						} else {
							$content		.= "Student removed from class and unassigned<br />
												<p>Click 'Push' to push the information to the advisor:<br />
												<form method='post' action='$pushURL' 
												name='selection_form_41' ENCTYPE='multipart/form-data'>
												<input type='hidden' name='strpass' value='2'>
												<input type='hidden' name='inp_mode' value='$inp_mode'>
												<input type='hidden' name='inp_semester' value='$student_semester'>
												<input type='hidden' name='request_info' value='$student_assigned_advisor'>
												<input type='hidden' name='request_type' value= 'Full'>
												<input type='submit' class='formInputButton' value='Push'></form></p><br /><br />";
						}
					}
				}
			} else {
				$content	.= "No student record found for $inp_student_callsign in the $proximateSemester semester.";
			}
		}



//////		Pass 40		Add a student to an advisor's class

	} elseif ("40" == $strPass) {
		if ($doDebug) {
			echo "<br />Arrived at pass 40 with inp_advisor_callsign: $inp_advisor_callsign, 
					inp_student_callsign: $inp_student_callsign, and level: $inp_level<br />";
		}
		$jobname						= "Student Management Add Student to Advisor Class";
		$beginnerChecked				= "";
		$fundamentalChecked				= "";
		$intermediateChecked			= "";
		$advancedChecked				= "";
		if ($inp_level != '') {
			if ($inp_level == 'Beginner') {
				$beginnerChecked		= "checked";
			} elseif ($inp_level == 'Fundamental') {
				$fundamentalChecked		= "checked";
			} elseif ($inp_level == 'Intermediate') {
				$intermediateChecked	= "checked";
			} elseif ($inp_level == 'Advanced') {
				$advancedChecked		= "checked";
			}
		}
		$content .= "<h3>$jobname</h3>
					<p>Enter the student call sign to be added, the advisor's call sign, and select the class 
					level that the advisor is teaching. The advisor must have a class in the indicated level. 
					The student must be unassigned or no action will be taken.</p>
					<p><b>Note:</b> If this action is taken before students are assigned to advisor classes, 
					 the better option is to pre-assign 
					the student to the advisor's class.</p>
					<form method='post' action='$theURL' 
					name='selection_form' ENCTYPE='multipart/form-data'>
					<input type='hidden' name='strpass' value='41'>
					<table style='border-collapse:collapse;'>
					<tr><td style='width:150px;'>Student Call Sign:</td>
						<td><input class='formInputText' type='text' size= '30' maxlength='30' name='inp_student_callsign' value='$inp_student_callsign' autofocus></td></tr>
					<tr><td style='width:150px;'>Advisor Call Sign:</td>
						<td><input class='formInputText' type='text' size = '30' maxlength='30' name='inp_advisor_callsign' value='$inp_advisor_callsign'></td></tr>
					<tr><td style='width:150px;'>Advisor's Teaching Level:</td>
						<td><input type='radio' class='formInputButton' name='inp_level' value='Beginner' $beginnerChecked>Beginner<br />
							<input type='radio' class='formInputButton' name='inp_level' value='Fundamental' $fundamentalChecked>Fundamental<br />
							<input type='radio' class='formInputButton' name='inp_level' value='Intermediate' $intermediateChecked>Intermediate<br />
							<input type='radio' class='formInputButton' name='inp_level' value='Advanced' $advancedChecked>Advanced</td></tr>
					$testModeOption
					<tr><td>&nbsp;</td><td><input class='formInputButton' type='submit' value='Submit' /></td></tr></table>
					</form>";

	} elseif ("41" == $strPass) {				// get the class 
// $doDebug = TRUE;
		if ($doDebug) {
			echo "<br />at pass 41 with inp_student_callsign of $inp_student_callsign<br />
					inp_advisor_callsign of $inp_advisor_callsign<br />
					level of $level<br />";
			if ($haveStudent) {
				echo "haveStudent is TRUE<br />";
			} else {
				echo "haveStudent is FALSE<br />";
			}
			if ($haveAdvisor) {
				echo "haveAdvisor is TRUE<br />";
			} else {
				echo "haveAdvisor is FALSE<br />";
			}
		}
		$jobname = "Student Management Add Student to Advisor Class";
		if ($haveStudent) {
			// actualize the student data
			foreach($student as $thisField => $thisValue) {
				$$thisField = $thisValue;
			}
			$gotError				= FALSE;
			if ($student_assigned_advisor === '') {
				if ($doDebug) {
					echo "Student does not have an assigned advisor<br />";
				}
		} else {
				if ($doDebug) {
					echo "Student already has an assigned advisor of $student_assigned_advisor<br />";
				}	
				$content			.= "<p>Student $inp_student_callsign already has an assigned advisor of $student_assigned_advisor. 
										Please unassign this advisor before making a new assignment.<p>";
				$gotError			= TRUE;
			}
			if ($student_email_number == 4 && $student_response != 'Y') {
				if ($doDebug) {
					echo "Student has been dropped<br />";
				}
				$content			.= "<p>Student $inp_student_callsign failed to respond to verification requests and has been dropped. Pre-assignment refused.</p>";
				$gotError			= TRUE;
			}
			if ($student_response == 'R') {
				if ($doDebug) {
					echo "Student has refused the verification<br />";
				}
				$content			.= "<p>Student $inp_student_callsign responded 'refused' to the verification request. Assignement refused.</p>";
				$gotError			= TRUE;
			}
			if (str_contains($student_excluded_advisor,$inp_advisor_callsign)) {
				if ($doDebug) {
					echo "$inp_advisor_callsign is an excluded advisor<br />";
				}
				$content			.= "<p>$inp_advisor_callsign is student's excluded advisor. Assignement refused.</p>";
				$gotError			= TRUE;
			}
			if (!$gotError) {
				// see if there is an advisor in the next semester. If so, get the classes at that level for the advisor
				$optionList				= '';
				if ($haveAdvisor) {		
					$advisor_semester = $advisor['advisor_semester'];
					if ($student_semester == $advisor_semester) {
						// get the classes
						$criteria = [
							'relation' => 'AND',
							'clauses' => [
								['field' => 'advisorclass_call_sign', 'value' => $inp_advisor_callsign, 'compare' => '=' ],
								['field' => 'advisorclass_semester', 'value' => $student_semester, 'compare' => '=' ],
								['field' => 'advisorclass_level', 'value' => $student_level, 'compare' => '=' ]
							]
						];
						$advisorclassResult = $advisorclass_dal->get_advisorclasses_by_order( $criteria, 'advisorclass_sequence', 'ASC', $operatingMode );
						if ($advisorclassResult === FALSE || $advisorclassResult === NULL) {
							if ($doDebug) {
								echo "get_advisorclass_by_order returned FALSE|NULL<br />";
							}
							$content .= "<p>Fatal Error</p>";
						} else {
							if (! empty($advisorclassResult)) {
								foreach($advisorclassResult as $key => $value) {
									foreach($value as $thisField => $thisValue) {
										$$thisField = $thisValue;
									}
									$optionList	.= "<input type='radio' class='formInputButton' name='inp_advisorclass' value='$advisorclass_sequence' required> $advisorclass_call_sign $advisorclass_level Class nbr $advisorclass_sequence at $advisorclass_class_schedule_times on $advisorclass_class_schedule_days<br />";
									if ($doDebug) {
										echo "making class $advisorclass_sequence available<br />";
									}
								}
								if ($optionList != '') {
									$content	.= "<h3>Assign Student to an Advisor</h3>
													<p>Select the class to which the student $inp_student_callsign is to  be assigned and click 'Next'</p>
													<p><form method='post' action='$theURL' 
													name='selection_form' ENCTYPE='multipart/form-data''>
													<input type='hidden' name='strpass' value='42'>
													<input type='hidden' name='inp_mode' value='$inp_mode'>
													<input type='hidden' name='inp_verbose' value='$inp_verbose'>
													<input type='hidden' name='inp_student_callsign' value='$inp_student_callsign'>
													<input type='hidden' name='inp_advisor_callsign' value='$inp_advisor_callsign'>
													<input type='hidden' name='inp_level' value='$student_level'>
													<input type='hidden' name='studentid' value='$student_id'>
													<input type='hidden' name='inp_semester' value='$student_semester'>
													<table style='border-collapse:collapse;'>
													<tr><th colspan='2'>The Advisor $inp_advisor_callsign's Class</th></tr>
													<tr><td style='width:150px;'>Advisor Class(es)</td><td>
													$optionList
													</td></tr>
													<tr><td>&nbsp;</td><td><input class='formInputButton' type='submit' value='Next' /></td></tr></table>
													</form></p>";
								} else {
									$content	.= "<h3>$jobname</h3>
													<p>The requested advisor $inp_advisor_callsign does not have a class at student $inp_student_callsign 
													$student_level level. No action taken.</p>";
								}
							} else {
								$content	.= "<h3>$jobname</h3>
												<p>The requested advisor $inp_advisor_callsign does not have a class at student $inp_student_callsign 
												$student_level level. No action taken.</p>";
							}
						}
					} else {
						if ($doDebug) {
							echo "no advisorclass records for $inp_advisor_call_sign in the $student_semester semester<br />";
						}
						$content .= "<p>No advisorclass record for the student to be assigned to<br />";
					}
				} else {
					$content			.= "<h3>$jobname</h3>
											<p>No matching $inp_advisor_callsign record found</p>";
				}
			}
		} else {		// no student found
			if ($doDebug) {
				echo "No student found with call sign $inp_student_callsign in the $studentTableName table<br />";
			}
			$content		.= "Student $inp_student_callsign not found in the $studentTableName table.";
		}


	} elseif ("42" == $strPass) {				// do the actual assignment
		if ($doDebug) {
			echo "<br />at pass 42 with inp_student_callsign of $inp_student_callsign<br />
					inp_advisor_callsign of $inp_advisor_callsign<br />
					studentid of $inp_studentid<br />";
			if ($haveStudent) {
				echo "haveStudent is TRUE<br />";
			} else {
				echo "haveStudent is FALSE<br />";
			}
			if ($haveAdvisor) {
				echo "haveAdvisor is TRUE<br />";
			} else {
				echo "haveAdvisor is FALSE<br />";
			}
		}
// $testMode	= TRUE;
// $doDebug	= TRUE;
		$jobname						= "Student Management Add Student to Advisor Class";
		
		if ($doDebug) {
			echo "inp_student_callsign: $inp_student_callsign<br />
			      inp_advisor_callsign: $inp_advisor_callsign<br />
			      inp_advisorClass: $inp_advisorclass<br />
			      inp_semester: $inp_semester<br />
			      inp_level: $inp_level<br />";
		}
		$content .= "<h3>$jobname</h3>";

		if ($haveStudent) {
			// actualize student info
			foreach($student as $key => $value) {
				$$key = $value;
			}
			if ($doDebug) {
				echo "Processing $student_call_sign:<br />
					  &nbsp&nbsp;&nbsp;with a level of $student_level<br />
					  &nbsp&nbsp;&nbsp;and a status of $student_status<br />";
			}
			$student_action_log	= "$student_action_log / $actionDate $jobname $userName Student assigned to $inp_advisor_callsign";
			$inp_data			= array('inp_student'=>$student_call_sign,
										'inp_semester'=>$inp_semester,
										'inp_assigned_advisor'=>$inp_advisor_callsign,
										'inp_assigned_advisor_class'=>$inp_advisorclass,
										'inp_remove_status'=>'',
										'inp_arbitrarily_assigned'=>'',
										'inp_method'=>'add',
										'jobname'=>$jobname,
										'userName'=>$userName,
										'testMode'=>$testMode,
										'doDebug'=>$doDebug);
					
			$addResult			= add_remove_student($inp_data);
			if ($addResult[0] === FALSE) {
				$thisReason		= $addResult[1];
				if ($doDebug) {
					echo "attempting to add $student_call_sign to $inp_advisor_callsign class failed:<br />$thisReason<br />";
				}
				sendErrorEmail("$jobname Attempting to add $student_call_sign to $inp_advisor_callsign class failed:<br />$thisReason");
				$content		.= "Attempting to add $student_call_sign to $inp_advisor_callsign class failed:<br />$thisReason<br />";
			} else {
				$content		.= "Student added to $inp_advisor_callsign class $inp_advisorclass<br />
									<p>Click 'Push' to push the information to the advisor:<br />
									<form method='post' action='$pushURL' 
									name='selection_form_41' ENCTYPE='multipart/form-data'>
									<input type='hidden' name='strpass' value='2'>
									<input type='hidden' name='inp_mode' value='$inp_mode'>
									<input type='hidden' name='inp_semester' value='$student_semester'>
									<input type='hidden' name='request_info' value='$inp_advisor_callsign'>
									<input type='hidden' name='request_type' value= 'Full'>
									<input type='submit' class='formInputButton' value='Push'></form></p><br /><br />";

				if ($doDebug) {
					echo "&nbsp;&nbsp;&nbsp;Student assignment made:<br />";
				}
			}
				
		} else {					
			if ($doDebug) {
				echo "Student call sign of $inp_student_callsign not found in the $studentTableName table. This process was aborted.<br />";
			}
			$content	.= "Student call sign of $inp_student_callsign not found in the $studentTableName table. This request was aborted.<br />";
		}



/////		Pass 50		unassign a  student

	} elseif ("50" == $strPass) {
		$jobname		= "Student Management Unassign a Student";
		$content .= "<h3>$jobname</h3>
					<p>Enter the student call sign to be unassigned. This will result in the student being 
					removed from a class, if assigned, student_status set to blank, assigned_advisor set 
					to blank, and the class number removed.</p>
					<form method='post' action='$theURL' 
					name='selection_form' ENCTYPE='multipart/form-data'>
						<input type='hidden' name='strpass' value='51'>
					<table style='border-collapse:collapse;'>
					<tr><td style='width:150px;'>Student Call Sign:</td><td>
						<input class='formInputText' type='text' size= '30' maxlength='30' name='inp_student_callsign' autofocus></td></tr>
					$testModeOption
					<tr><td>&nbsp;</td><td><input class='formInputButton' type='submit' value='Submit' /></td></tr></table>
					</form>";



	} elseif ("51" == $strPass) {				// do the actual unassignment
// $testMode	= TRUE;
// $doDebug	= TRUE;
		if ($doDebug) {
			echo "<br />at pass 51 with inp_student_callsign of $inp_student_callsign<br />";
			if ($haveStudent) {
				echo "haveStudent is TRUE<br />";
			} else {
				echo "haveStudent is FALSE<br />";
			}
		}

		$jobname		= "Student Management Unassign a Student";
		$isSuccessful	= TRUE;
		$content .= "<h3>$jobname $inp_student_callsign</h3>";
		
		if ($haveStudent) {
			// actualize the student data fields
			foreach($student as $key => $value) {
				$$key = $value;
			}
		}
		

		// get the student info and process
		if ($haveStudent) {
			if ($doDebug) {
				echo "<br />Processing student $student_call_sign:<br />
				&nbsp;&nbsp;&nbsp;Student Status: $student_status<br />
				&nbsp;&nbsp;&nbsp;Level: $student_level<br />
				&nbsp;&nbsp;&nbsp;Assigned Advisor: $student_assigned_advisor<br />
				&nbsp;&nbsp;&nbsp;Assigned Advisor's Class: $student_assigned_advisor_class<br />";
			}
			
			if ($student_assigned_advisor != '') {
				$inp_data			= array('inp_student'=>$student_call_sign,
											'inp_semester'=>$student_semester,
											'inp_assigned_advisor'=>$student_assigned_advisor,
											'inp_assigned_advisor_class'=>$student_assigned_advisor_class,
											'inp_remove_status'=>'',
											'inp_arbitrarily_assigned'=>$student_no_catalog,
											'inp_method'=>'remove',
											'jobname'=>$jobname,
											'userName'=>$userName,
											'testMode'=>$testMode,
											'doDebug'=>$doDebug);
				
				$removeResult		= add_remove_student($inp_data);
				if ($removeResult[0] === FALSE) {
					$thisReason		= $removeResult[1];
					if ($doDebug) {
						echo "attempting to remove $student_call_sign from $student_assigned_advisor class failed:<br />$thisReason<br />";
					}
					sendErrorEmail("$jobname Attempting to remove $student_call_sign from $student_assigned_advisor class failed:<br />$thisReason");
					$content		.= "Attempting to remove $student_call_sign from $student_assigned_advisor class failed:<br />$thisReason<br />";
				} else {
					$content		.= "<p>Student $student_call_sign has been unassigned from $student_assigned_advisor class</p>
										<p>Click 'Push' to push the information to the advisor:<br />
										<form method='post' action='$pushURL' 
										name='selection_form_51' ENCTYPE='multipart/form-data'>
										<input type='hidden' name='strpass' value='2'>
										<input type='hidden' name='inp_mode' value='$inp_mode'>
										<input type='hidden' name='inp_semester' value='$student_semester'>
										<input type='hidden' name='request_info' value='$student_assigned_advisor'>
										<input type='hidden' name='request_type' value= 'Full'>
										<input type='submit' class='formInputButton' value='Push'></form></p><br /><br />";
				}
			} else {
				$content			.= "<p>Student is not assigned to an advisor</p>";
			} 
		} else {
			$content 	.= "<p>No student with the call sign of $inp_student_callsign found in the $studentTableName table. No action taken.</p>";
		}


//////		Pass 55		Re-assign a student to a different advisor

	} elseif ("55" == $strPass) {
		if ($doDebug) {
			echo "<br />at pass 55<br />";
		}
		$jobname		= "Student Management Reassign Student to Another Advisor";
		$content	.= "<h3>$jobname</h3>
						<p>Enter the student call sign that is to be re-assigned (must be 
						registered in the upcoming semester) and then 
						the advisor call sign to whom the student is to be assigned. If there are any add'l 
						comments to be added to the action_log, enter those as well. If the student is registered 
						and the advisor is registered, a list of the classes the advisor is teaching will be displayed. 
						Select the appropriate class for the student. After that the reassignment will be made.</p>
						<p><form method='post' action='$theURL' 
						name='selection_form' ENCTYPE='multipart/form-data''>
						<input type='hidden' name='strpass' value='56'>
						<table style='border-collapse:collapse;'>
						<tr><td style='width:150px;'>Student Call Sign</td>
							<td><input class='formInputText' type='text' name='inp_student_callsign' size='10' maxlenth='10' autofocus /></td></tr>
						<tr><td style='vertical-align:top;'>Advisor Call Sign<br /><i>This is the advisor the student will be reassigned to</i></td>
							<td><input class='formInputText' type='text' name='inp_advisor_callsign' size='10' maxlenth='10' /></td></tr>
						<tr><td style='vertical-align:top;'>Additional Comments</td>
							<td><textarea class='formInputText' name='inp_additional' rows='5' cols='50'></textarea></td></tr>
						$testModeOption
						<tr><td>&nbsp;</td><td><input class='formInputButton' type='submit' value='Next' /></td></tr></table>
						</form></p>";

	} elseif ("56" == $strPass) {
// $doDebug = TRUE;
		if ($doDebug) {
			echo "<br />at pass 56 with inp_student_callsign of $inp_student_callsign<br />
					inp_advisor_callsign of $inp_advisor_callsign<br />";
			if ($haveStudent) {
				echo "haveStudent is TRUE<br />";
			} else {
				echo "haveStudent is FALSE<br />";
			}
			if ($haveAdvisor) {
				echo "haveAdvisor is TRUE<br />";
			} else {
				echo "haveAdvisor is FALSE<br />";
			}
		}

		$jobname			= "Student Management Reassign Student to Another Advisor";
		$content			.= "<h3>$jobname</h3>";
		
		if ($haveStudent) {
			// actualize the student data
			foreach($student as $key => $value) {
				$$key = $value;
			}
		}
		if ($haveAdvisor) {
			$advisor_semester = $advisor['advisor_semester'];
//			echo "have advisor_semester of $advisor_semester<br />";
		
			// actualize the receiving advisor information
			foreach($advisor as $key => $value) {
				$$key = $value;
//				echo "set $key to $value<br />";
			}
		}
		if ($haveStudent && $student_semester == $proximateSemester) {
			$gotError				= FALSE;
			if ($student_response == 'R') {
				if ($doDebug) {
					echo "Student has refused the verification<br />";
				}
				$content			.= "<p>Student $inp_student_callsign responded 'refused' to the verification request.</p>";
				$gotError			= TRUE;
			}
			if ($student_assigned_advisor == '') {
				if ($doDebug) {
					echo "Student has no assigned advisor<br />";
				}
				$content			.= "<p>Student $inp_student_callsign has no assigned advisor.</p>";
			}
			if ($student_status == 'C' || $student_status == 'R' || $student_status == 'V') {
				if ($doDebug) {
					echo "Student status is ($student_status) C, R, or V<br />";
				}
				$content			.= "<p>Student $inp_student_callsign has a status of $student_status (replaced).</p>";
				$gotError			= TRUE;
			}
			if (strPos($student_excluded_advisor,$inp_advisor_callsign) !== FALSE) {
				if ($doDebug) {
					echo "$inp_advisor_callsign is an excluded advisor<br />";
				}
				$content			.= "<p>$inp_advisor_callsign is an excluded advisor.</p>";
				$gotError			= TRUE;
			} 
			if ($gotError) {
				$content			.= "<p>If the student should not be reassigned, close this window without 
										taking any further action.<br />";
			} else {
				if ($haveAdvisor && $advisor_semester == $proximateSemester) {
					if ($doDebug) {
						echo "looking for matching class records<br />";
					}
					$optionList = '';
					$criteria = [
						'relation' => 'AND',
						'clauses' => [
							['field' => 'advisorclass_call_sign', 'value' => $inp_advisor_callsign, 'compare' => '=' ],
							['field' => 'advisorclass_semester', 'value' => $proximateSemester, 'compare' => '=' ],
							['field' => 'advisorclass_level', 'value' => $student_level, 'compare' => '=' ]
						]
					];
					$advisorclassData = $advisorclass_dal->get_advisorclasses_by_order( $criteria, 'advisorclass_sequence', 'ASC', $operatingMode );						
					if ($advisorclassData === FALSE) {
						if ($doDebug) {
							echo "get_advisorclasses_by_order for $inp_advisor_callsign returned FALSE<br />";
						}
						$content .= "<p>No advisorclass records found for advisor $inp_advisor_callsign</p>";
					} else {
						if (! empty($advisorclassData)) {
							$myInt = count($advisorclassData);
							foreach($advisorclassData as $key => $value) {
								foreach($value as $thisField => $thisValue) {
									$$thisField = $thisValue;	
								}
								if ($myInt == 1) {
									$optionList	.= "<input type='radio' class='formInputButton' name='inp_advisorClass' value='$advisorclass_sequence' checked> Class nbr $advisorclass_sequence $advisorclass_level at $advisorclass_class_schedule_times on $advisorclass_class_schedule_days<br />";
								} else {
										$optionList	.= "<input type='radio' class='formInputButton' name='inp_advisorClass' value='$advisorclass_sequence' > Class nbr $advisorclass_sequence $advisorclass_level at $advisorclass_class_schedule_times on $advisorclass_class_schedule_days<br />";
								}
							}
							if ($optionList != '') {
								if ($doDebug) {
									echo "options set up<br />";
								}
								$content	.= "<p>Select the class to which the student $inp_student_callsign is to  be assigned and click 'Next'</p>
												<p><form method='post' action='$theURL' 
												name='selection_form' ENCTYPE='multipart/form-data''>
												<input type='hidden' name='strpass' value='57'>
												<input type='hidden' name='theSemester' value='$proximateSemester'>
												<input type='hidden' name='inp_mode' value='$inp_mode'>
												<input type='hidden' name='inp_verbose' value='$inp_verbose'>
												<input type='hidden' name='inp_student_callsign' value='$inp_student_callsign'>
												<input type='hidden' name='inp_advisor_callsign' value='$inp_advisor_callsign'>
												<input type='hidden' name='inp_prev_advisor' value='$student_assigned_advisor'>
												<input type='hidden' name='inp_prev_advisor_class' value='$student_assigned_advisor_class'>
												<input type='hidden' name='studentid' value='$student_id'>
												<input type='hidden' name='inp_additional' value='$inp_additional'>
												<table style='border-collapse:collapse;'>
												<tr><th colspan='2'>Re-assign Student to Advisor $inp_advisor_callsign's Class</th></tr>
												<tr><td style='width:150px;'>Advisor Class(es)</td><td>
												$optionList
												</td></tr>
												<tr><td>&nbsp;</td><td><input class='formInputButton' type='submit' value='Next' /></td></tr></table>
												</form></p>";
							} else {
								if ($doDebug) {
									echo "optionList is empty<br />";
								}
								$content .= "<p>No matching classes found</p>";
							}
						} else {
							if ($doDebug) {
								echo "No advisorClass records found with the call sign $inp_advisor_callsign at level $student_level in the $proximateSemester semester<br />";
							}
							$content	.= "<p>No advisorClass records found with the call sign $inp_advisor_callsign at level $student_level in the $proximateSemester semester</p>";
						}
					}
				} else {
					$content .= "<p>No advisor record found for $inp_advisor_callsign in semester $proximateSemester</p>";
				}
			}
		} else {		// no student found
			if ($doDebug) {
				echo "No student found with call sign $inp_student_callsign in the $proximateSemester semester<br />";
			}
			$content		.= "Student $inp_student_callsign is not registered in the $proximateSemester semester.";
		}

	
	} elseif ("57" == $strPass) {
// do the actual re-assignment

// $doDebug	= TRUE;
		if ($doDebug) {
			echo "<br />at pass 57 with inp_student_callsign of $inp_student_callsign<br />
					inp_advisor_callsign of $inp_advisor_callsign<br />
					inp_prev_advisor_class of $inp_prev_advisor_class<br />";
			if ($haveStudent) {
				echo "haveStudent is TRUE<br />";
			} else {
				echo "haveStudent is FALSE<br />";
			}
			if ($haveAdvisor) {
				echo "haveAdvisor is TRUE<br />";
			} else {
				echo "haveAdvisor is FALSE<br />";
			}
		}

		$jobname			= "Student Management Reassign Student to Another Advisor";
		$content			.= "<h3>$jobname</h3>";

		if ($doDebug) {
			echo "inp_student_callsign: $inp_student_callsign<br />
				  inp_studentid: $inp_studentid<br />
				  inp_advisor_callsign: $inp_advisor_callsign<br />
				  inp_advisorClass: $inp_advisorClass<br />
				  inp_prev_advisor: $inp_prev_advisor<br />
				  inp_prev_advisor_class: $inp_prev_advisor_class<br />";
		}

		if ($haveStudent) {
			// actualize the student info
			foreach($student as $key => $value) {
				$$key = $value;
			}
		}
		if ($haveAdvisor) {			/// this is the new advisor
			// actualize the advisor info
			$advisor_semester = $advisor['advisor_semester'];
			
		}

		//	get the student record in the next semester along with the new advisor
		// 	if either doesn't exist, don't do anything
		if ($haveStudent && $student_semester == $proximateSemester) {
			if ($haveAdvisor && $advisor_semester == $proximateSemester) {
				if ($doDebug) {
					echo "have everything needed to reassign the student<br />";
				}			
				// if there was a previous advisor, remove the student from that class
				if ($inp_prev_advisor != '') {
					if ($doDebug) {
						echo "removing student from prev advisor: $inp_prev_advisor<br />";
					}
					$inp_data			= array('inp_student'=>$inp_student_callsign,
												'inp_semester'=>$proximateSemester,
												'inp_assigned_advisor'=>$inp_prev_advisor,
												'inp_assigned_advisor_class'=>$inp_prev_advisor_class,
												'inp_remove_status'=>'',
												'inp_arbitrarily_assigned'=>'',
												'inp_method'=>'remove',
												'jobname'=>$jobname,
												'userName'=>$userName,
												'testMode'=>$testMode,
												'doDebug'=>$doDebug);
							
					$removeResult			= add_remove_student($inp_data);
					if ($removeResult[0] === FALSE) {
						handleWPDBError("$jobname MGMT 57",$doDebug);
					} else {
						$content		.= "Student $inp_student_callsign successfully removed from $inp_prev_advisor class $inp_prev_advisor_class<br />";
						// add the student to the advisor's class
						$inp_data			= array('inp_student'=>$inp_student_callsign,
													'inp_semester'=>$theSemester,
													'inp_assigned_advisor'=>$inp_advisor_callsign,
													'inp_assigned_advisor_class'=>$inp_advisorClass,
													'inp_remove_status'=>'',
													'inp_arbitrarily_assigned'=>'',
													'inp_method'=>'add',
													'jobname'=>$jobname,
													'userName'=>$userName,
													'testMode'=>$testMode,
													'doDebug'=>$doDebug);
									
						$addResult			= add_remove_student($inp_data);
						if ($addResult[0] === FALSE) {
							$thisReason		= $addResult[1];
							if ($doDebug) {
								echo "attempting to add $inp_student_callsign to $inp_advisor_callsign class $inp_advisorClass failed:<br />$thisReason<br />";
							}
							sendErrorEmail("$jobname Attempting to add $inp_student_callsign to $inp_advisor_callsign class $inp_advisorClass failed:<br />$thisReason");
							$content		.= "Attempting to add $inp_student_callsign to $inp_advisor_callsign class $inp_advisorClass failed:<br />$thisReason<br />";
						} else {
							if ($doDebug) {
								echo "deciding how many pushes are needed<br />inp_prev_advisor: $inp_prev_advisor<br />inp_advisor_callsign: $inp_advisor_callsign<br />";
							}
							if ($inp_prev_advisor == '') {
								if ($doDebug) {
									echo "No previous advisor. Pushing new advisor<br />";
								}
								$content		.= "<p>Student $inp_student_callsign assigned to advisor $inp_advisor_callsign.</p>
													<p>Click 'Push' to push the information to the advisor:<br />
													<form method='post' action='$pushURL' 
													name='selection_form_41' ENCTYPE='multipart/form-data'>
													<input type='hidden' name='strpass' value='2'>
													<input type='hidden' name='inp_semester' value='$proximateSemester'>
													<input type='hidden' name='request_info' value='$inp_advisor_callsign'>
													<input type='hidden' name='inp_verbose' value='$inp_verbose'>
													<input type='hidden' name='request_type' value= 'Full'>
													<input type='hidden' name='inp_mode' value='$inp_mode'>
													<input type='submit' class='formInputButton' value='Push'></form>";
							} else {
								if ($doDebug) {
									echo "need to push previous and new advisor classes<br />"; 
								}
								$content		.= "<p>Student $inp_student_callsign re-assigned from advisor $inp_prev_advisor class $inp_prev_advisor_class 
													to advisor $inp_advisor_callsign class $inp_advisorClass.</p>
													<p>Both advisors need to be informed of the class change. Click 'Push $inp_prev_advisor' to push the 
													information to the previous advisor. The 'push' will open in a new tab. 
													When that is done, return here and click 'Push $inp_advisor_callsign'.</p>
													<table style='width:auto;'>
													<tr><td><form method='post' action='$pushURL' target='_blank' 
															name='selection_form_41' ENCTYPE='multipart/form-data'> 
															<input type='hidden' name='strpass' value='2'>
															<input type='hidden' name='inp_semester' value='$proximateSemester'>
															<input type='hidden' name='request_info' value='$inp_prev_advisor'>
															<input type='hidden' name='inp_verbose' value='$inp_verbose'>
															<input type='hidden' name='request_type' value= 'Full'>
															<input type='hidden' name='inp_mode' value='$inp_mode'>
															<input type='submit' class='formInputButton' value='Push $inp_prev_advisor'></form></td>
														<td style='width:50px;'></td>
														<td><form method='post' action='$pushURL' target='_blank' 
															name='selection_form_41' ENCTYPE='multipart/form-data'> 
															<input type='hidden' name='strpass' value='2'>
															<input type='hidden' name='inp_semester' value='$proximateSemester'>
															<input type='hidden' name='request_info' value='$inp_advisor_callsign'>
															<input type='hidden' name='inp_verbose' value='$inp_verbose'>
															<input type='hidden' name='request_type' value= 'Full'>
															<input type='hidden' name='inp_mode' value='$inp_mode'>
															<input type='submit' class='formInputButton' value='Push $inp_advisor_callsign'></form></td></tr>
													</table>";
							}
						}
					}
				} else {
					if ($doDebug) {
						echo "no previous advisor. No action taken<br />";
					}
					$content			.= "<p>Student was not assigned to a class. No action taken</p>";
				}
			} else {
				if ($doDebug) {
					echo "no advisor record for the new advisor. No action taken<br />";
				}
				$content				.= "<p>No advisor record found for the new advisor. No action taken</p>";
			}
		} else {
			if ($doDebug) {
				echo "No student record found<br />";
			}
			$content					.= "<p>No student record found for $inp_student_callsign. No action taken</p>";
		}
		


/////		Pass 60		remove a  student

	} elseif ("60" == $strPass) {
		if ($doDebug) {
			echo "<br />at pass 60<br />";
		}
	
		$jobname			= "Student Management Unassign and Remove a Student";
		$content .= "<h3>$jobname</h3>
					<p>Enter the student call sign to be removed. If the student is assigned to an advisor, 
					this will result in the student status being set to C, the student  
					removed from a class, assigned_advisor set to blank, and the class number set to blank.</p>
					<p>If the student is not assigned, then the student response will be set to R (refused).</p>
					<form method='post' action='$theURL' 
					name='selection_form' ENCTYPE='multipart/form-data'>
					<input type='hidden' name='strpass' value='61'>
					<table style='border-collapse:collapse;'><tr><td style='width:150px;'>Student Call Sign:</td><td>
					<input class='formInputText' type='text' size= '30' maxlength='30' name='inp_student_callsign' autofocus></td></tr>
					$testModeOption
					<tr><td>&nbsp;</td><td><input class='formInputButton' type='submit' value='Submit' /></td></tr></table>
					</form>";



	} elseif ("61" == $strPass) {				// do the actual unassignment
// $testMode	= TRUE;
// $doDebug	= TRUE;

		if ($doDebug) {
			echo "<br />at pass 61 with inp_student_callsign of $inp_student_callsign<br />";
			if ($haveStudent) {
				echo "haveStudent is TRUE<br />";
			} else {
				echo "haveStudent is FALSE<br />";
			}
		}

		$jobname			= "Student Management Unassign and Remove a Student";
		if ($haveStudent) {
			// actualize the student data
			foreach($student as $key => $value) {
				$$key = $value;
			}
		}
		
		$content 			.= "<h3>$jobname</h3>";
		if ($haveStudent && $student_semester == $proximateSemester) {
			if ($doDebug) {
				echo "<br />Processing student $student_call_sign:<br />
				&nbsp;&nbsp;&nbsp;Student Status: $student_status<br />
				&nbsp;&nbsp;&nbsp;Level: $student_level<br />
				&nbsp;&nbsp;&nbsp;Semester: $student_semester<br />
				&nbsp;&nbsp;&nbsp;Pre-assigned Advisor: $student_pre_assigned_advisor<br />
				&nbsp;&nbsp;&nbsp;Assigned Advisor: $student_assigned_advisor<br />
				&nbsp;&nbsp;&nbsp;Assigned Advisor's Class: $student_assigned_advisor_class<br />";
			}
			if ($student_assigned_advisor != '') {
				// remove the student
				$inp_data			= array('inp_student'=>$student_call_sign,
											'inp_semester'=>$student_semester,
											'inp_assigned_advisor'=>$student_assigned_advisor,
											'inp_assigned_advisor_class'=>$student_assigned_advisor_class,
											'inp_remove_status'=>'C',
											'inp_arbitrarily_assigned'=>'',
											'inp_method'=>'remove',
											'jobname'=>$jobname,
											'userName'=>$userName,
											'testMode'=>$testMode,
											'doDebug'=>$doDebug);
				
				$removeResult		= add_remove_student($inp_data);
				if ($removeResult[0] === FALSE) {
					$thisReason		= $removeResult[1];
					if ($doDebug) {
						echo "attempting to remove $student_call_sign from $student_assigned_advisor class failed:<br />$thisReason<br />";
					}
					sendErrorEmail("$jobname Attempting to remove $student_call_sign from $student_assigned_advisor class failed:<br />$thisReason");
					$content		.= "Attempting to remove $student_call_sign from $student_assigned_advisor class failed:<br />$thisReason<br />";
				} else {
					if ($doDebug) {
						echo "<br />&nbsp;&nbsp;&nbsp;Student has been removed:<br />";
					}
					$content	.= "<p>Student $student_call_sign has been removed and unassigned</p>
									<p>Click 'Push' to push the information to the advisor:<br />
									<form method='post' action='$pushURL' 
									name='selection_form_41' ENCTYPE='multipart/form-data'>
									<input type='hidden' name='strpass' value='2'>
									<input type='hidden' name='inp_semester' value='$student_semester'>
									<input type='hidden' name='request_info' value='$student_assigned_advisor'>
									<input type='hidden' name='inp_mode' value='$inp_mode'>
									<input type='hidden' name='request_type' value= 'Full'>
									<input type='submit' class='formInputButton' value='Push'></form>";
				}
			} else {					// student not assigned ... set the response to R
				$updateParams			= array('student_response|R|s');
				$updateFormat			= array('');
				$studentUpdateData		= array('tableName'=>$studentTableName,
												'inp_method'=>'update',
												'inp_data'=>$updateParams,
												'inp_format'=>$updateFormat,
												'jobname'=>$jobname,
												'inp_id'=>$student_id,
												'inp_callsign'=>$student_call_sign,
												'inp_semester'=>$student_semester,
												'inp_who'=>$userName,
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
					$content		.= "Student response set to R<br />";
				}
			}
		} else {
			$content 	.= "<p>No student with the call sign of $inp_student_callsign in semester $proximateSemester found in the $studentTableName table. No action taken.</p>";
		}


///////		pass 70		Find Possible Classes for a Student

	} elseif ("70" == $strPass) {
		if ($doDebug) {
			echo "arrived at pass 70<br />";
		}
		$jobname			= "Student Management Find Possible Classes for a Student";
		$content			.= "<h3>$jobname</h3>
								<p>There are two options:
								<dl>
								<dt>1. Search Using Student's Choices</dt>
								<dd>The option will search through the advisor classes being offered 
								in the student's requested language to find any classes that 
								may meet the student's first, second  and thirdclass choices</dd>
								<dt>2. Search Using A New Time and Class Days</dt>
								<dd>Enter the approximate time (the system will search two hours before and two hours after) 
								and select the class days. The function will search through the advisor classes to find 
								any classes that may meet the search criteria</dd>
								</dl></p>
								<p>If the student is on hold or if the student is already assigned to an advisor, 
								it will be necessary to use the function 'Assign a Student to an Advisor Regardless of Status or Semester' 
								to make the assignment</p>
								<p><form method='post' action='$theURL' 
								name='selection_form' ENCTYPE='multipart/form-data'>
								<input type='hidden' name='strpass' value='71'>
								<input type='hidden' name='inp_semester' value='$proximateSemester'>
								<table style='width:auto;'>
								<tr><td style='vertical-align:top;'>Student Call Sign:</td>
									<td><input class='formInputText' type='text' size= '30' maxlength='30' value='$inp_student_callsign' name='inp_student_callsign'></td></tr>
								<tr><td colspan='2'>Select Option</td></tr>
								<tr><td colspan='2'><input type='radio' class='formInputButton' name='inp_choice' value='option1' checked='checked'> Search using student&apos;s preferences</td></tr>
								<tr><td colspan='2'><input type='radio' class='formInputButton' name='inp_choice' value='option2'>Search using new search terms</td></tr>
								<tr><td></td><td>Search time in student's local time<br /><input type='text' class='formInputText' name='search_time' size='5 maxlenth='5'></td></tr>
								<tr><td></td><td>Specify search days<br />
										<input type='radio' class='formInputButton' name='search_days' value='Saturday,Wednesday'> Saturday,Wednesday<br />
										<input type='radio' class='formInputButton' name='search_days' value='Sunday,Wednesday'> Sunday,Wednesday<br />
										<input type='radio' class='formInputButton' name='search_days' value='Monday,Thursday' checked='checked'> Monday,Thursday<br />
										<input type='radio' class='formInputButton' name='search_days' value='Tuesday,Friday'> Tuesday,Friday</td></tr>
								$testModeOption
								<tr><td>&nbsp;</td><td><input class='formInputButton' type='submit' value='Submit' /></td></tr></table>
								</form>";

	} elseif ("71" == $strPass) {
// $doDebug = TRUE;
		if ($doDebug) {
			echo "<br />at pass 71 with inp_student_callsign of $inp_student_callsign<br />";
			if ($haveStudent) {
				echo "haveStudent is TRUE<br />";
			} else {
				echo "haveStudent is FALSE<br />";
			}
			if ($inp_choice == 'choice2') {
				echo "requested time: $search_time and requested days: $search_days<br />";
			}
		}
		$jobname				= "Student Management Find Possible Classes for a Student";
		$classAvailableArray	= array();
		
		$content				.= "<h3>$jobname $inp_student_callsign</h3>";
		$doActions				= TRUE;
		
		// actualize some variables
		foreach($student as $key => $value) {
			$$key = $value;
		}
		
		if ($haveStudent && $student_semester == $proximateSemester) {						
			if ($doDebug) {
				echo "<br />Processing $student_call_sign<br />
					  &nbsp;&nbsp;&nbsp;&nbsp;response: $student_response<br />
					  &nbsp;&nbsp;&nbsp;&nbsp;status: $student_status<br />
					  &nbsp;&nbsp;&nbsp;&nbsp;language: $student_class_language<br />";
			}
			$statusComment							= '';
			$doProceed								= TRUE;
			/// check to see if the student meetinp the criteria
			if ($student_response != 'Y') {
				if ($student_response == '') {
					$myStr		= 'blank';
				} else {
					$myStr		= $student_response;
				}
				$content							.= "<b>Student is not verified (Response is $myStr)</b><br />";
				$doActions							= FALSE;
			}						
				/// inform if on hold
			if ($student_intervention_required == 'H') {
				$content							.= "Student is on hold<br />";
//				$doActions							= FALSE;
			}
			if ($student_status == 'Y') {
				$content							.= "Student already assigned to $student_assigned_advisor - $student_assigned_advisor_class. Student status: $student_status<br />";
				$doActions							= FALSE;
			} elseif ($student_status != '') {
				$content							.= "Student has a status of $student_status and is currently not available to be assigned<br />";
				$doActions							= FALSE;
			}
			if ($doActions) {			
				if ($inp_choice == 'option1') {
					$testFirst					= TRUE;
					$searchFirstBegin			= "";
					$searchFirstEnd				= "";
					$testSecond					= TRUE;
					$searchSecondBegin			= "";
					$searchSecondEnd				= "";
					$testThird					= TRUE;
					$searchThirdBegin			= "";
					$searchThirdEnd				= "";
					////// get the student's search values
					if ($student_first_class_choice_utc == '' || $student_first_class_choice_utc == 'None') {
						$testFirst				= FALSE;
						$searchFirstTime		= '';
						$searchFirstDays		= '';
						if ($doDebug) {
							echo "No first choice. Not searching first choice<br />";
						}
					} else {
						$myArray				= explode(" ",$student_first_class_choice_utc);
						$myTime					= substr($myArray[0],0,2);
						$myInt					= intval($myTime) * 100;
						$searchValues			= $searchRange[$myInt];
						$thisArray				= explode("|",$searchValues);
						$searchFirstBegin		= intval($thisArray[0]);
						$searchFirstEnd			= intval($thisArray[1]);
						$searchFirstDays		= $myArray[1];
						if ($doDebug) {
							echo "searchFirst: $searchFirstBegin - $searchFirstEnd $searchFirstDays<br />";
						}
					}
					if ($student_second_class_choice_utc == '' || $student_second_class_choice_utc == 'None') {
						$searchSecondTime		= "";
						$searchSecondDays		= "";
						$testSecond				= FALSE;
						if ($doDebug) {
							echo "No second choice. Not searching second choice<br />";
						}
					} else {
						$myArray				= explode(" ",$student_second_class_choice_utc);
						$myTime					= substr($myArray[0],0,2);
						$myInt					= intval($myTime) * 100;
						$searchValues			= $searchRange[$myInt];
						$thisArray				= explode("|",$searchValues);
						$searchSecondBegin		= intval($thisArray[0]);
						$searchSecondEnd		= intval($thisArray[1]);
						$searchSecondDays		= $myArray[1];
						if ($doDebug) {
							echo "searchSecond: $searchSecondBegin - $searchSecondEnd $searchSecondDays<br />";
						}
					}
					if ($student_third_class_choice_utc == '' || $student_third_class_choice_utc == 'None') {
						$searchThirdTime		= "";
						$searchThirdDays		= "";
						$testThird				= FALSE;
						if ($doDebug) {
							echo "No third choice. Not searching third choice<br />";
						}
					} else {
						$myArray				= explode(" ",$student_third_class_choice_utc);
						$myTime					= substr($myArray[0],0,2);
						$myInt					= intval($myTime) * 100;
						$searchValues			= $searchRange[$myInt];
						$thisArray				= explode("|",$searchValues);
						$searchThirdBegin		= intval($thisArray[0]);
						$searchThirdEnd			= intval($thisArray[1]);
						$searchThirdDays		= $myArray[1];
						if ($doDebug) {
							echo "searchThird: $searchThirdBegin - $searchThirdEnd $searchThirdDays<br />";
						}
					}
				} else {					//// doing a option2 search rather than the student choices
					/// convert input times and days to UTC
					$result						= utcConvert('toutc',$student_timezone_offset,$search_time,$search_days);
					if ($result[0] == 'FAIL') {
						if ($doDebug) {
							echo "utcConvert failed 'toutc',$student_timezone_offset,$search_time,$search_days<br />
								  Error: $result[3]<br />";
						}
						$displayDays			= "<b>ERROR</b>";
						$displayTimes			= '';
					} else {
						$displayTimes			= $result[1];
						$displayDays			= $result[2];
					}
					$myTime					= substr($displayTimes[0],0,2);
					$myInt					= intval($myTime) * 100;
					$searchValues			= $searchRange[$myInt];
					$thisArray				= explode("|",$searchValues);
					$searchFirstBegin		= intval($thisArray[0]);
					$searchFirstEnd			= intval($thisArray[1]);
					$searchFirstDays		= $displayDays;
					if ($doDebug) {
						echo "searchFirst: $searchFirstBegin - $searchFirstEnd $searchFirstDays<br />";
					}
					$testFirst					= TRUE;
					$testSecond					= FALSE;
					$testThird					= FALSE;
				}
				// check to see if should test student choices
				$doTesting			= FALSE;
				if ($testFirst || $testSecond || $testThird) {
					$doTesting		= TRUE;
				} else {
					$content		.= "<p><b>Student has not selected any class choices</b></p>";
				}
				if ($doTesting) {
					//// get all the advisorClass records for the student's level and language

					$criteria = [
						'relation' => 'AND',
						'clauses' => [
							['field' => 'advisorclass_semester', 'value' => $proximateSemester, 'compare' => '=' ],
							['field' => 'advisorclass_level', 'value' => $student_level, 'compare' => '=' ],
							['field' => 'advisorclass_language', 'value' => $student_class_language, 'compare' => '=' ]
						]
					];
					$orderby = 'advisorclass_call_sign,advisorclass_sequence';
					$order = 'ASC';
					$advisorclassData = $advisorclass_dal->get_advisorclasses_by_order( $criteria, $orderby, $order, $operatingMode );
					if ($advisorclassData === FALSE || $advisorclassData === NULL) {
						if ($doDebug) {
							echo "get advisorclass by order returned FALSE|NULL<br />";
						}
					} else {
						if (! empty($advisorclassData)) {
							foreach($advisorclassData as $key => $value) {
								foreach($value as $thisField => $thisValue) {
									$$thisField = $thisValue;
								}
								if ($doDebug) {
									echo "<br />Have $advisorclass_level advisorclass for $advisorclass_call_sign held on $advisorclass_class_schedule_times_utc $advisorclass_class_schedule_days_utc<br />
										language: $advisorclass_language<br />
										advisorclass_class_size: $advisorclass_class_size<br />
										class_number_students: $advisorclass_number_students<br />";
								}
								$doProceed					= TRUE;
								//// see if this advisor is excluded
								if (str_contains($student_excluded_advisor,$advisorclass_call_sign)) {
									if ($doDebug) {
										echo "This advisor is excluded .... bypassed<br />";
									}
									$doProceed				= FALSE;
								}
								if ($doProceed) {
									////// get the advisor and user_master records and see if we can use this advisor
									$advisorData = get_advisor_and_user_master($advisorclass_call_sign, 'callsign', $advisorclass_semester, $operatingMode, $doDebug);
									if ($advisorData === FALSE) {
										if ($doDebug) {
											echo "getting advisor_and_user_master retured FALSE<br />";
										}
										$doProceed = FALSE;
									} else {
										$thisAdvisorCallSign = $advisorData['advisor_call_sign'];
										$thisUserSurveyScore = $advisorData['user_survey_score'];
										$thisAdvisorVerifyResponse = $advisorData['advisor_verify_response'];
										$thisAdvisorEmailNumber = $advisorData['advisor_verify_email_number'];
										$thisAdvisorLastName = $advisorData['user_last_name'];
										$thisAdvisorFirstName = $advisorData['user_first_name'];
										$thisAdvisorState = $advisorData['user_state'];
										$thisAdvisorCountry = $advisorData['user_country'];

										if ($thisAdvisorVerifyResponse == 'R') {
											if ($doDebug) {
												echo "Advisor has a response of R. Bypassing<br />";
											}
											$doProceed		= FALSE;
										}
										if ($thisUserSurveyScore == '6' || $thisUserSurveyScore == 9 || $thisUserSurveyScore == 13) {
											if ($doDebug) {
												echo "Advisor has a survey score of $thisUserSurveyScore. Bypassing<br />";
											}
											$doProceed		= FALSE;
										}
										if ($thisAdvisorEmailNumber == '4') {
											if ($doDebug) {
												echo "Advisor was dropped. Bypassing<br />";
											}
											$doProceed		= FALSE;
										}
									}
									if ($doProceed) {
										if ($doDebug) {
											echo "testing advisor $thisAdvisorCallSign<br />";
										}
										$gotAMatch				= FALSE;
										$advisorclass_class_schedule_times_utc		= intval($advisorclass_class_schedule_times_utc);
										//// does the advisor's class meet the student's requirements?
										if ($testFirst) {
											if ($doDebug) {
												echo "checking $thisAdvisorCallSign schedule against student first class choice<br />";
											}
											if ($searchFirstDays == $advisorclass_class_schedule_days_utc) {		/// half way there
												if ($advisorclass_class_schedule_times_utc >= $searchFirstBegin && $advisorclass_class_schedule_times_utc < $searchFirstEnd) {		/// have a match
													$result						= utcConvert('tolocal',$student_timezone_offset,$advisorclass_class_schedule_times_utc,$advisorclass_class_schedule_days_utc,$doDebug);
													if ($result[0] == 'FAIL') {
														if ($doDebug) {
															echo "utcConvert failed 'tolocal',$student_timezone_offset,$advisorclass_class_schedule_times_utc,$advisorclass_class_schedule_days_utc<br />
																  Error: $result[3]<br />";
														}
														$displayDays			= "<b>ERROR</b>";
														$displayTimes			= "";
													} else {
														$displayTimes			= $result[1];
														$displayDays			= $result[2];
														$classAvailableArray[]	= "1|$advisorclass_call_sign|$thisAdvisorFirstName, $thisAdvisorLastName<br />$thisAdvisorState, $thisAdvisorCountry|$advisorclass_sequence|$advisorclass_language|$advisorclass_class_schedule_times_utc|$advisorclass_class_schedule_days_utc|First|$advisorclass_class_size|$advisorclass_number_students|$advisorclass_class_schedule_times $advisorclass_class_schedule_days|$advisorclass_timezone_offset|$displayTimes $displayDays|$student_level";
														if ($doDebug) {
															echo "Got a match. Added $advisorclass_call_sign|$advisorclass_sequence|$advisorclass_language|$advisorclass_class_schedule_times_utc|$advisorclass_class_schedule_days_utc|First|$advisorclass_class_size|$advisorclass_number_students|$advisorclass_class_schedule_times $advisorclass_class_schedule_days|$advisorclass_timezone_offset|$displayTimes $displayDays|$student_level<br />";
														}
														$gotAMatch	= TRUE;
													}
												} else {
													if ($doDebug) {
														echo "no match<br />";
													}
												}
											}
										}
										if ($testSecond) {
											if ($doDebug) {
												echo "checking $thisAdvisorCallSign chedule against student second class choice<br />";
											}
											if ($searchSecondDays == $advisorclass_class_schedule_days_utc) {		/// half way there
												if ($advisorclass_class_schedule_times_utc >= $searchSecondBegin && $advisorclass_class_schedule_times_utc < $searchSecondEnd) {		/// have a match
													$result						= utcConvert('tolocal',$student_timezone_offset,$advisorclass_class_schedule_times_utc,$advisorclass_class_schedule_days_utc,$doDebug);
													if ($result[0] == 'FAIL') {
														if ($doDebug) {
															echo "utcConvert failed 'tolocal',$student_timezone_offset,$advisorclass_class_schedule_times_utc,$advisorclass_class_schedule_days_utc<br />
																  Error: $result[3]<br />";
														}
														$displayDays			= "<b>ERROR</b>";
														$displayTimes			= "";
													} else {
														$displayTimes			= $result[1];
														$displayDays			= $result[2];
//														$studentCount			= getStudentCount($advisorclass_call_sign,$advisorclass_sequence);
														$classAvailableArray[]	= "2|$advisorclass_call_sign|$thisAdvisorFirstName, $thisAdvisorLastName<br />$thisAdvisorState, $thisAdvisorCountry|$advisorclass_sequence|$advisorclass_language|$advisorclass_class_schedule_times_utc|$advisorclass_class_schedule_days_utc|First|$advisorclass_class_size|$advisorclass_number_students|$advisorclass_class_schedule_times $advisorclass_class_schedule_days|$advisorclass_timezone_offset|$displayTimes $displayDays|$student_level";
														if ($doDebug) {
															echo "Got a match. Added 2|$advisorclass_call_sign|$user_last_name, $user_first_name<br />$user_state, $user_country|$advisorclass_sequence|$advisorclass_language|$advisorclass_class_schedule_times_utc|$advisorclass_class_schedule_days_utc|Second|$advisorclass_class_size|$advisorclass_number_students|$advisorclass_class_schedule_times $advisorclass_class_schedule_days|$advisorclass_timezone_offset|$displayTimes $displayDays|$student_level<br />";
														}
														$gotAMatch		= TRUE;
													}
												} else {
													if ($doDebug) {
														echo "no match<br />";
													}
												}
											}
										}
										if ($testThird) {
											if ($doDebug) {
												echo "checking $thisAdvisorCallSign schedule against student third class choice<br />";
											}
											if ($searchThirdDays == $advisorclass_class_schedule_days_utc) {		/// half way there
												if ($advisorclass_class_schedule_times_utc >= $searchThirdBegin && $advisorclass_class_schedule_times_utc < $searchThirdEnd) {		/// have a match
													$result						= utcConvert('tolocal',$student_timezone_offset,$advisorclass_class_schedule_times_utc,$advisorclass_class_schedule_days_utc,$doDebug);
													if ($result[0] == 'FAIL') {
														if ($doDebug) {
															echo "utcConvert failed 'tolocal',$student_timezone_offset,$advisorclass_class_schedule_times_utc,$advisorclass_class_schedule_days_utc<br />
																  Error: $result[3]<br />";
														}
														$displayDays			= "<b>ERROR</b>";
														$displayTimes			= "";
													} else {
														$displayTimes			= $result[1];
														$displayDays			= $result[2];
														$classAvailableArray[]	= "3|$advisorclass_call_sign|$thisAdvisorFirstName, $thisAdvisorLastName<br />$thisAdvisorState, $thisAdvisorCountry|$advisorclass_sequence|$advisorclass_language|$advisorclass_class_schedule_times_utc|$advisorclass_class_schedule_days_utc|First|$advisorclass_class_size|$advisorclass_number_students|$advisorclass_class_schedule_times $advisorclass_class_schedule_days|$advisorclass_timezone_offset|$displayTimes $displayDays|$student_level";
														if ($doDebug) {
															echo "Got a match. Added $advisorclass_call_sign|$advisorclass_sequence|$advisorclass_language|$advisorclass_class_schedule_times_utc|$advisorclass_class_schedule_days_utc|Third|$advisorclass_class_size|$advisorclass_number_students|$advisorclass_class_schedule_times $advisorclass_class_schedule_days|$advisorclass_timezone_offset|$displayTimes $displayDays|$student_level<br />";
														}
														$gotAMatch				= TRUE;
													}
												} else {
													if ($doDebug) {
														echo "no match<br />";
													}
												}
											}
										}
									}
								}
							}			//// end of advisorClass while
							if (count($classAvailableArray) > 0) {
								/// output the data
								sort($classAvailableArray);
								if ($doDebug) {
									echo "<br />class available array:<br /><pre>";
									print_r($classAvailableArray);
									echo "</pre><br />";
									if ($gotAMatch) {
										echo "gotAMatch is TRUE<br />";
									} else {
										echo "gotAMatch is FALSE<br />";
									}
								}
								if ($testFirst) {
									$searchFirstBegin		= str_pad($searchFirstBegin,4,'0',STR_PAD_LEFT);
									$searchFirstEnd			= str_pad($searchFirstEnd,4,'0',STR_PAD_LEFT);
								} else {
									$searchFirstBegin		= '';
									$searchFirstEnd			= '';
								}
								if ($testSecond) {
									$searchSecondBegin		= str_pad($searchSecondBegin,4,'0',STR_PAD_LEFT);
									$searchSecondEnd		= str_pad($searchSecondEnd,4,'0',STR_PAD_LEFT);
								} else {
									$searchSecondBegin		= '';
									$searchSecondEnd		= '';
								}
								if ($testThird) {
									$searchThirdBegin		= str_pad($searchThirdBegin,4,'0',STR_PAD_LEFT);
									$searchThirdEnd			= str_pad($searchThirdEnd,4,'0',STR_PAD_LEFT);
								} else {
									$searchThirdBegin		= '';
									$searchThirdEnd			= '';
								}
								if ($inp_choice == 'option1') {
									$thisType			 	= "Search Using Student's Choices";
									$thisSearch				= "<table style='width:1000px;'>
																<tr><th>Student<br />
																		$user_last_name, $user_first_name</th>
																	<th>Call Sign<br />
																		$student_call_sign</th>
																	<th>Level<br />
																		$student_level</th>
																	<th>Language<br />
																		$student_class_language</th>
																	<th>Location<br />
																		$user_state, $user_country</th></tr>
																<tr><td>&nbsp;</td>
																	<td><b>Local Time</b></td>
																	<td><b>UTC Time</b></td>
																	<td></td>
																	<td></td></tr>
																<tr><td style='vertical-align:top;'>First Choice</td>
																	<td style='vertical-align:top;'>$student_first_class_choice</td>
																	<td style='vertical-align:top;'>$student_first_class_choice_utc<br />
																		Search GE $searchFirstBegin LT $searchFirstEnd</td>
																	<td></td>
																	<td></td></tr>
																<tr><td style='vertical-align:top;'>Second Choice</td>
																	<td style='vertical-align:top;'>$student_second_class_choice</td>
																	<td style='vertical-align:top;'>$student_second_class_choice_utc<br />
																		Search GE $searchSecondBegin LT $searchSecondEnd</td>
																	<td></td>
																	<td></td></tr>
																<tr><td style='vertical-align:top;'>Third Choice</td>
																	<td style='vertical-align:top;'>$student_third_class_choice</td>
																	<td style='vertical-align:top;'>$student_third_class_choice_utc<br />
																		Search GE $searchThirdBegin LT $searchThirdEnd</td>
																	<td></td>
																	<td></td></tr>
																<tr><td colspan='5'>Search Using Student Choices</td></tr>
																</table>";
								} else {
									$thisType			 	= "Search Using a New Time and Class Days";
									$thisSearch				= "New Search Criteria: $search_time $search_days Local ($displayTimes $displayDays UTC)<br />";
								}
								$showPreAssign				= TRUE;
								if ($daysToSemester > 0 && $daysToSemester <= 19) {		/// show pre-assign or not??
									$showPreAssign			= FALSE;
									if ($doDebug) {
										echo "showPreAssign set to FALSE as $daysToSemester is gt 0 and le 19<br />";
									}
								}
							
								$content		.= "$thisSearch
													$statusComment</p>
													<table style='width:1000px;'>
													<tr><th>Advisor</th>
														<th>Name</th>
														<th>Class</th>
														<th>Language</th>
														<th>Advisor Class Schedule</th>
														<th>Choice Match</th>
														<th>Class Size</th>
														<th>Seats<br />Taken</th>
														<th>Seats<br />Available</th>
														<th>Student Class Time Local</th>
														<th>Pre-Assign</th>
														<th>Assign</th></tr>";
		
/*
		"3|$advisorclass_call_sign|$user_last_name, $user_first_name<br />$user_state, $advisor_country|$advisorclass_sequence|$advisorclass_language|$advisorclass_class_schedule_times_utc|$advisorclass_class_schedule_days_utc|Third|$advisorclass_class_size|$advisorclass_number_students|$advisorclass_class_schedule_times $advisorclass_class_schedule_days|$advisorclass_timezone_offset|$displayTimes $displayDays|$student_level";

		classAvailableArray = 
		0	1, 2, or 3 depending on which class choice matched
		1	advisorclass_call_sign 
		2	advisorclass_user_last_name, advisorclass_user_first_name
		3	advisorclass_sequence
		4	advisorclass_language
		5	advisorclass_class_schedule_times_utc
		6	advisorclass_class_schedule_days_utc
		7	Third
		8	advisorclass_class_size
		9	studentCount
		10	advisorclass_class_schedule_times advisorclass_class_schedule_days
		11	advisorclass_timezone
		12  Class time in student's timezone
		13	level

	
	
		Truth table
		doActions 			FALSE	FALSE	FALSE	TRUE	TRUE	TRUE	TRUE	
		classFull			FALSE	TRUE	TRUE	FALSE	FALSE	TRUE	TRUE
		showPreAssign		TRUE	FALSE	TRUE	FALSE	TRUE	FALSE	TRUE
	
		PreAssign											xxx				
		Assign										xxx		xxx		xxx		xxx
		Neither				xxx		xxx		xxx		
			
*/
								if (count($classAvailableArray) > 0) {
									$gotAMatch			= TRUE;
								}
								foreach ($classAvailableArray as $myValue) {
									$myArray			= explode("|",$myValue);
									$thisChoiceMatch	= $myArray[0];
									$thisCallSign		= $myArray[1];
									$thisName			= $myArray[2];
									$thisClass			= $myArray[3];
									$thisLanguage		= $myArray[4];
									$thisClassSkedTime	= $myArray[5];
									$thisClassSkedDays	= $myArray[6];
									$thisMatch			= $myArray[7];
									$thisSize			= $myArray[8];
									$thisCount			= $myArray[9];
									$thisLocal			= $myArray[10];
									$thisTimeZone		= $myArray[11];
									$thisClassTime		= $myArray[12];
									$thisLevel			= $myArray[13];
								
									$thisClassSkedTime	= str_pad($thisClassSkedTime,4,0,STR_PAD_LEFT);
									$classFull			= TRUE;
									if ($thisCount < $thisSize) {
										$classFull		= FALSE;
									}
									if ($doDebug) {
										echo "Logicals: doActions: $doActions; classFull: $classFull; showPreAssign: $showPreAssign<br />";
									}
									$myInt				= $thisSize - $thisCount;
									$thisAvail			= 0;
									if ($myInt < 0) {
										$thisAvail	= 0;
									}
									if ($myInt > 0) {
										$thisAvail		= "<b>$myInt</b>";
									}
									
									// convert 1,2,3 into First, Second, Third
									$countConvert = array('1' => 'First',
														  '2' => 'Second',
														  '3' => 'Third');
									$countStr = $countConvert[$thisChoiceMatch];					  
									$content			.= "<tr><td style='vertical-align:top;'>$thisCallSign</td>
																<td style='vertical-align:top;'>$thisName</td>
																<td style='vertical-align:top;text-align:center;'>$thisClass</td>
																<td style='vertical-align:top;'>$thisLanguage</td>																<td style='vertical-align:top;'>$thisClassSkedTime $thisClassSkedDays UTC<br />$thisLocal Local</td>
																<td style='vertical-align:top;'>$countStr</td>
																<td style='vertical-align:top;text-align:center;'>$thisSize</td>
																<td style='vertical-align:top;text-align:center;'>$thisCount</td>
																<td style='vertical-align:top;text-align:center;'>$thisAvail</td>
																<td style='vertical-align:top;'>$thisClassTime</td>";
									if ($doActions && !$classFull && $showPreAssign) {
										$content		.= "	<td style='vertical-align:top;'><a href='$theURL?strpass=2&inp_advisor_callsign=$thisCallSign&inp_student_callsign=$inp_student_callsign' target='_blank'>Pre-Assign</a></td>
																<td style='vertical-align:top;'><a href='$theURL?strpass=40&inp_advisor_callsign=$thisCallSign&inp_student_callsign=$inp_student_callsign&level=$thisLevel' target='_blank'>Assign</a></td>
															</tr>";
									} elseif ($doActions) {
										$content		.= "	<td style='vertical-align:top;'></td>
																<td style='vertical-align:top;'><a href='$theURL?strpass=40&inp_advisor_callsign=$thisCallSign&inp_student_callsign=$inp_student_callsign&level=$thisLevel' target='_blank'>Assign</a></td>
															</tr>";
									} else {
										$content		.="<td style='vertical-align:top;'></td>
															<td style='vertical-align:top;'></td></tr>";						
									}
								}
								$content				.= "</table>
															<p><u>The Pre-Assign Link</u>: Clicking on this link will open the 'Pre-assign Student to an Advisor' function. Use this capability before 
															students have been actually assigned to an advisor.</p>
															<p><u>The Assign Link</u>: Clicking on this link will open the 'Add Unassigned Student to an Advisors Class' function. Use this capability 
															after students have been assigned to an advisor, otherwise the assignment will be ignored when assigning students to an advisor.</p>";
							} else {	/// no matching records found
								$content	.= "<h3>Find Possible Classes for Unassigned $inp_student_callsign<h3>
												<p>No advisor classes found that match the criteria";
							}
						} else {		//// no advisorClass records 
							$content		.= "<h3>Find Possible Classes for Unassigned $inp_student_callsign<h3>
												<p>No advisorClass records found for the student level of $student_level";
						}
					} 
				}
			} else {
				$content	.= "<p>No further action taken</p>";
			}
		} else {			/// no student record found
			$content			.= "<p>No student record found in $proximateSemester semester</p>";
		}



///////		pass 80			Find Possible Unassigned Students for an Advisor's Class
	} elseif ("80" == $strPass) {
		if ($doDebug) {
			echo "Arrived at pass 80<br />";
		}
		$standardChecked	= "";
		$expandedChecked	= "";
		if ($inp_search == 'expanded') {
			$expandedChecked	= "checked='checked'";
		} else {
			$standardChecked	= "checked='checked'";
		}
		$jobname			= "Student Management Find Possible Unassigned Students for an Advisor";
		$content			.= "<h3>$jobname</h3>
								<p>Enter the advisor's call sign and class number. The function will search through the 
								unassigned students to see if there are any students that may meet the class time. The 
								function will look at unassigned student's first, second, and third class choices.</p>
								<p>If 'Expanded Search' is selected, the program will also look students in the next 
								semester.</p>
								<p><form method='post' action='$theURL' 
								name='selection_form' ENCTYPE='multipart/form-data'>
								<input type='hidden' name='strpass' value='81'>
								<table style='border-collapse:collapse;'>
								<tr><td style='width:150px;'>Advisor Call Sign:</td>
									<td><input class='formInputText' type='text' size= '30' maxlength='30' name='inp_advisor_callsign' value='$inp_advisor_callsign' autofocus></td></tr>
								<tr><td style='width:150px;'>Advisor Class Number:</td>
									<td><input class='formInputText' type='text' size= '5' maxlength='5' name='inp_advisorClass' value='$inp_advisorClass'></td></tr>
								<tr><td>Search Parameter</td>
									<td><input type='radio' class='formInputButton' name='inp_search' value='standard' $standardChecked> Current Semester<br />
										<input type='radio' class='formInputButton' name='inp_search' value='expanded' $expandedChecked> Expanded Search</td></tr>
								$testModeOption
								<tr><td>&nbsp;</td><td><input class='formInputButton' type='submit' value='Submit' /></td></tr></table>
								</form>";



	} elseif ("81" == $strPass) {
// $doDebug = TRUE;
		if ($doDebug) {
			echo "Arrived at pass 81 with inp_advisor_callsign: $inp_advisor_callsign, inp_advisorClass: $inp_advisorClass, and search: $inp_search<br />";
		}
		$jobname			= "Student Management Find Possible Unassigned Students for an Advisor";
		if ($inp_advisor_callsign != '' && $inp_advisorClass != '') {
			$studentsFirstChoiceMatch					= array();
			$studentsSecondChoiceMatch					= array();
			$studentsThirdChoiceMatch					= array();



			//// get the advisor class level and class times
			$criteria = [
				'relation' => 'AND',
				'clauses' => [
					['field' => 'advisorclass_call_sign', 'value' => $inp_advisor_callsign, 'compare' => '=' ],
					['field' => 'advisorclass_semester', 'value' => $proximateSemester, 'compare' => '=' ],
					['field' => 'advisorclass_sequence', 'value' => $inp_advisorClass, 'compare' => '=' ]
				]
			];
			$advisorclassData = $advisorclass_dal->get_advisorclasses_by_order( $criteria, 'advisorclass_sequence', 'ASC', $operatingMode );
			if ($advisorclassData === FALSE || $advisorclassData === NULL) {
				if ($doDebug) {
					echo "get_advisorclasses for $advisor_call_sign returned FALSE|NULL<br />";
				}
				$content .= "<p>Unable to retrieve the advisorclass record for $advisor_call_sign class $inp_advisor_class</p>";
			} else {
				if (! empty($advisorclassData)) {
					foreach($advisorclassData as $key => $value) {
						foreach($value as $thisField => $thisValue) {
							$$thisField = $thisValue;
						}
				
						if ($doDebug) {
							echo "Have an advisor class at level $advisorclass_level for $inp_advisor_callsign and class $inp_advisorClass<br />";
						}
						$searchTime 					= substr($advisorclass_class_schedule_times_utc,0,2);
						$searchTime						= intval($searchTime) * 100;
						$searchDays 					= $advisorclass_class_schedule_days_utc;
						$searchValues					= $searchRange[$searchTime];
						$thisArray						= explode("|",$searchValues);
						$searchBegin					= intval($thisArray[0]);
						$searchEnd						= intval($thisArray[1]);

						if ($doDebug) {
							echo "&nbsp;&nbsp;&nbsp;&nbsp;class schedule: $advisorclass_class_schedule_times_utc $advisorclass_class_schedule_days_utc<br />
								  &nbsp;&nbsp;&nbsp;&nbsp;Looking for a replacement between $searchBegin and $searchEnd on $searchDays<br />";
						}

						$expandedSemester	= $nextSemester;
						if ($currentSemester == 'Not in Session') {
							$expandedSemester	= $semesterTwo;
						}					
						
						///// 		now get all unassigned students and see if any can fill the class criteria
						if ($inp_search == 'standard') {
							$criteria = [
								'relation' => 'AND',
								'clauses' => [
									['field' => 'student_semester', 'value' => $proximateSemester, 'compare' => '=' ],
									['field' => 'student_level', 'value' => $advisorclass_level, 'compare' => '=' ],
									['field' => 'student_response', 'value' => 'Y', 'compare' => '=' ],
									['field' => 'student_status', 'value' => '', 'compare' => '=' ],
									['field' => 'student_class_language', 'value' => $advisorclass_language, 'compare' => '=' ]
								]
							];
							$orderby = 'student_class_priority,$student_request_date';
							$order = 'DESC';
						} else {
							$criteria = [
								'relation' => 'AND',
								'clauses' => [
									// field1 = $value1
									['field' => 'student_call_sign', 'value' => 'K1ABC', 'compare' => '='],
									
									[
										'relation' => 'OR',
										'clauses' => [
											['field' => 'student_level', 'value' => $advisorclass_level, 'compare' => '='],
											['field' => 'student_response', 'value' => 'Y', 'compare' => '='],
											['field' => 'student_status', 'value' => '', 'compare' => '='],
											['field' => 'student_class_language', 'value' => $advisorclass_language, 'compare' => '=']
										]
									]
								]
							];
							$orderby = 'student_class_priority, student_request_date';
							$order = 'DESC';
						}
						$requestInfo = array('criteria' => $criteria,
											 'orderby' => $orderby,
											 'order' => $order);
						$studentResult = get_student_and_user_master('', 'complex', $requestInfo, $operatingMode, $doDebug);
						if ($studentResult === FALSE) {
							if ($doDebug) {
								echo "get_student_and_user_master returned FALSE<br />";
							}
						} else {
							if (! empty($studentResult)) {
								$myInt = count($studentResult);
								if ($doDebug) {
									echo "retrieved $myInt unassigned student records<br />";
								}
								foreach ($studentResult as $key => $value) {
									foreach($value as $thisField => $thisValue) {
										$$thisField = $thisValue;
									}
									if ($doDebug) {
										echo "<br />Found $student_call_sign<br />
											  &nbsp;&nbsp;&nbsp;&nbsp;semester: $student_semester<br />
											  &nbsp;&nbsp;&nbsp;&nbsp;response: $student_response<br />
											  &nbsp;&nbsp;&nbsp;&nbsp;first choice UTC: $student_first_class_choice_utc<br />
											  &nbsp;&nbsp;&nbsp;&nbsp;second choice UTC: $student_second_class_choice_utc<br />
											  &nbsp;&nbsp;&nbsp;&nbsp;third choice UTC: $student_third_class_choice_utc<br />
											  &nbsp;&nbsp;&nbsp;&nbsp;Catalog Options: $student_catalog_options<br />
											  &nbsp;&nbsp;&nbsp;&nbsp;Flexible: $student_flexible<br />";
									}
									$doProceed								= TRUE;
									//////	make sure this isn't an excluded advisor
									if (str_contains($student_excluded_advisor,$advisorclass_call_sign)) {
										$doProceed							= FALSE;
										if ($doDebug) {
											echo "&nbsp;&nbsp;&nbsp;&nbsp;Advisor is excluded. Bypassing<br />";
										}
									} else {
										if ($doDebug) {
											echo "&nbsp;&nbsp;&nbsp;&nbsp;Advisor not excluded. Continue<br />";
										}
									}
									if ($student_email_number == '4' and $student_response != 'Y') {
										$doProceed							= FALSE;
										if ($doDebug) {
											echo "&nbsp;&nbsp;&nbsp;&nbsp;student was dropped. Bypassing<br />";
										}
									}
									if ($student_semester == $proximateSemester && $student_response != 'Y') {
										$doProceed							= FALSE;
										if ($doDebug) {
											echo "&nbsp;&nbsp;&nbsp;&nbsp;Semester is $theSemester and student_response of $student_response != 'Y'. Bypassing<br />";
										}
									}
									if ($student_assigned_advisor != '') {			/// already assigned
										$doProceed							= FALSE;
										if ($doDebug) {
											echo "&nbsp;&nbsp;&nbsp;&nbsp;already assigned to $student_assigned_advisor. Bypassing<br />";
										}
									}
									if ($doProceed) {
										if ($doDebug) {
											echo "&nbsp;&nbsp;&nbsp;&nbsp;processing this student<br />";
										}
										$gotMatch				= FALSE;
										if ($student_semester == $proximateSemester) {
											////// check first class choice for a match
											if ($student_first_class_choice_utc != '' && $student_first_class_choice_utc != 'None') {
												$myArray			= explode(" ",$student_first_class_choice_utc);
												$needleTime			= intval($myArray[0]);
												$origtime			= $myArray[0];
												$needleDays			= $myArray[1];
												if ($doDebug) {
													echo "first needleTime: $needleTime, needleDays: $needleDays<br />
														  Checking against $searchBegin - $searchEnd $searchDays<br />";
												}
										
												if ($needleDays == $searchDays) {			/// halfway there
													if ($doDebug) {
														echo "first choice days match<br />";
													}
													if ($needleTime >= $searchBegin && $needleTime < $searchEnd) {		/// got the rest of the match
														/// convert the advisors class time to student local time
														$result						= utcConvert('tolocal',$student_timezone_offset,$advisorclass_class_schedule_times_utc,$advisorclass_class_schedule_days_utc,$doDebug);
														if ($result[0] == 'FAIL') {
															if ($doDebug) {
																echo "utcConvert failed 'tolocal',$student_timezone_offset,$advisorclass_class_schedule_times_utc,$advisorclass_class_schedule_days_utc<br />
																	  Error: $result[3]<br />";
															}
															$displayDays			= "<b>ERROR</b>";
															$displayTimes			= '';
														} else {
															$displayTimes			= $result[1];
															$displayDays			= $result[2];
														}
	
													
														/// add the student to the studentsFirstChoiceMatch array
														$studentsFirstChoiceMatch[]		= "$student_semester|$student_first_class_choice_utc|$student_request_date|$student_call_sign|$user_last_name, $user_first_name|$student_timezone_offset|$student_first_class_choice|$user_email|$user_phone|$student_class_priority|$displayTimes|$displayDays|$student_class_language";
														if ($doDebug) {
															echo "Found a match. Added $student_semester|$student_first_class_choice_utc|$student_request_date|$student_call_sign|$user_last_name, $user_first_name|$student_timezone_offset|$student_first_class_choice|$user_email|$user_phone|$student_class_priority|$displayTimes|$displayDays to studentsFirstChoiceMatch array<br />";
														}
														$gotMatch 					= TRUE;
													} else {
														if ($doDebug) {
															echo "No match on first class choice<br />";
														}
													}
												} else {
													if ($doDebug) {
														echo "No match on first class choice<br />";
													}
												}
											}
											if ($student_second_class_choice_utc != '' && $student_second_class_choice_utc != 'None' && $gotMatch == FALSE) {
												////// check second class choice for a match
												$myArray			= explode(" ",$student_second_class_choice_utc);
												$needleTime			= intval($myArray[0]);
												$origTime			= $myArray[0];
												$needleDays			= $myArray[1];
												if ($doDebug) {
													echo "second needleTime: $needleTime, needleDays: $needleDays<br />";
												}
											
												if ($needleDays == $searchDays) {			/// halfway there
													if ($doDebug) {
														echo "second choice days match<br />";
													}
													if ($needleTime >= $searchBegin && $needleTime < $searchEnd) {		/// got the rest of the match
														/// convert the advisors class time to student local time
														$result						= utcConvert('tolocal',$student_timezone_offset,$advisorclass_class_schedule_times_utc,$advisorclass_class_schedule_days_utc,$doDebug);
														if ($result[0] == 'FAIL') {
															if ($doDebug) {
																echo "utcConvert failed 'tolocal',$student_timezone_offset,$advisorclass_class_schedule_times_utc,$advisorclass_class_schedule_days_utc<br />
																	  Error: $result[3]<br />";
															}
															$displayDays			= "<b>ERROR</b>";
															$displayTimes			= '';
														} else {
															$displayTimes			= $result[1];
															$displayDays			= $result[2];
														}
														/// add the student to the studentsSecondChoiceMatch array
														$studentsSecondChoiceMatch[]		= "$student_semester|$student_second_class_choice_utc|$student_request_date|$student_call_sign|$user_last_name, $user_first_name|$student_timezone_offset|$student_second_class_choice|$user_email|$user_phone|$student_class_priority|$displayTimes|$displayDays|$student_class_language";
														if ($doDebug) {
															echo "Found a match. Added $student_semester|$student_second_class_choice_utc|$student_request_date|$student_call_sign|$user_last_name, $user_first_name|$student_timezone_offset|$student_second_class_choice|$user_email|$user_phone|$student_class_priority|$displayTimes|$displayDays|$student_class_language to studentsSecondChoiceMatch array<br />";
														}
														$gotMatch					= TRUE;
													} else {
														if ($doDebug) {
															echo "No match on second class choice<br />";
														}
													}
												} else {
													if ($doDebug) {
														echo "No match on second class choice<br />";
													}
												}
											}
											if ($student_third_class_choice_utc != '' && $student_third_class_choice_utc != 'None' && $gotMatch == FALSE ) {
												////// check third class choice for a match
												$myArray			= explode(" ",$student_third_class_choice_utc);
												$needleTime			= intval($myArray[0]);
												$origTime			= $myArray[0];
												$needleDays			= $myArray[1];
												if ($doDebug) {
													echo "third needleTime: $needleTime, needleDays: $needleDays<br />";
												}
											
												if ($needleDays == $searchDays) {			/// halfway there
													if ($doDebug) {
														echo "third choice days match<br />";
													}
													if ($needleTime >= $searchBegin && $needleTime < $searchEnd) {		/// got the rest of the match
														/// convert the advisors class time to student local time
														$result						= utcConvert('tolocal',$student_timezone_offset,$advisorclass_class_schedule_times_utc,$advisorclass_class_schedule_days_utc,$doDebug);
														if ($result[0] == 'FAIL') {
															if ($doDebug) {
																echo "utcConvert failed 'tolocal',$student_timezone_offset,$advisorclass_class_schedule_times_utc,$advisorclass_class_schedule_days_utc<br />
																	  Error: $result[3]<br />";
															}
															$displayDays			= "<b>ERROR</b>";
															$displayTimes			= '';
														} else {
															$displayTimes			= $result[1];
															$displayDays			= $result[2];
														}
														/// add the student to the studentsThirdChoiceMatch array
														$studentsThirdChoiceMatch[]		= "$student_semester|$student_third_class_choice_utc|$student_request_date|$student_call_sign|$user_last_name, $user_first_name|$student_timezone_offset|$student_third_class_choice|$user_email|$user_phone|$student_class_priority|$displayTimes|$displayDays|$student_class_language";
														if ($doDebug) {
															echo "Found a match. Added $student_semester|$student_third_class_choice_utc|$student_request_date|$student_call_sign|$user_last_name, $user_first_name|$student_timezone_offset|$student_third_class_choice|$user_email|$user_phone|$student_class_priority|$displayTimes|$displayDays|$student_class_language to studentsThirdChoiceMatch array<br />";
														}
													} else {
														if ($doDebug) {
															echo "No match on third class choice<br />";
														}
													}
												} else {
													if ($doDebug) {
														echo "No match on third class choice<br />";
													}
												}
											}
										} else {		/// student is in the next semester
										
										}
									}				/// end of the student while
									///// display the output
									$myInt			= count($studentsFirstChoiceMatch);
									if ($myInt > 0) {
										if ($doDebug) {
											echo "<br /><b>Finished with students</b><br />
												  studentsFirstChoiceMatch array has $myInt entries<br /><pre>";
											print_r($studentsFirstChoiceMatch);
											echo "</pre><br />";
										}
									} else {
										if ($doDebug) {
	  										echo "studentsFirstChoiceMatch array is empty<br />";
	  									}
									}
									$myInt			= count($studentsSecondChoiceMatch);
									if ($myInt > 0) {
										if ($doDebug) {
											echo "studentsSecondChoiceMatch array has $myInt entries<br /><pre>";
											print_r($studentsSecondChoiceMatch);
											echo "</pre><br />";
										}
									} else {
										if ($doDebug) {
	 										echo "studentsSecondChoiceMatch array is empty<br />";
	 									}
									}
									$myInt			= count($studentsThirdChoiceMatch);
									if ($myInt > 0) {
										if ($doDebug) {
											echo "studentsThirdChoiceMatch array has $myInt entries<br /><pre>";
											print_r($studentsThirdChoiceMatch);
											echo "</pre><br />";
										}
									} else {
										if ($doDebug) {
											echo "studentsThirdChoiceMatch array is empty<br />";
										}
									}
								}
							}
						}		//// display the result
									
//						studentsFirstChoiceMatch student_semester|student_first_class_choice_utc|student_request_date|student_call_sign|user_last_name, user_first_name|student_time_zone|student_first_class_choice|user_email|user_phone|student_class_priority|$displayTimes|$displayDays|$student_class_language
//												 0                1                              2                    3                 4                               5                 6                          7          8		   9                      10             11          12
									
						sort($studentsFirstChoiceMatch);
						sort($studentsSecondChoiceMatch);
						sort($studentsThirdChoiceMatch);
						$noPreAssign			= FALSE;
						if ($daysToSemester > 0 && $daysToSemester <= 19) {
							$noPreAssign		= TRUE;
						}
						$content		.= "<h3>Find Possible Students for $inp_advisor_callsign's $advisorclass_language  $advisorclass_level Class Number $advisorclass_sequence</h3>
											<p>Search type: $inp_search<br />
											Search Range: $searchBegin - $searchEnd $searchDays<br />
											Advisor Time Zone: $advisorclass_timezone_offset<br />
											Class is held at about $advisorclass_class_schedule_times_utc UTC on $advisorclass_class_schedule_days_utc 
											($advisorclass_class_schedule_times on $advisorclass_class_schedule_days Local)</p>
											<p><b>Any Pre-Assignments Occur in Production</b></p>
											<table>
											<tr><th>Call Sign</th>
												<th>Name</th>
												<th>Time Zone</th>
												<th>Semester</th>
												<th>Email</th>
												<th>Phone</th>
												<th>Language</th>
												<th>Priority</th>
												<th>Student Choice UTC</th>
												<th>Student Choice Local</th>
												<th>Pre-Assign</th>
												<th>Assign</th></tr>";
						$myInt			= count($studentsFirstChoiceMatch);
						if ($myInt > 0) {
							$content				.= "<tr><td colspan='12'>Students whose First Class Choice matches</td></tr>";
							foreach($studentsFirstChoiceMatch as $myValue) {
								$myArray	     = explode("|",$myValue);
								$thisCallSign	 = $myArray[3];
								$thisName		 = $myArray[4];
								$thisTimeZone	 = $myArray[5];
								$thisClassChoice	= $myArray[1];
								$thisClassLocal		= $myArray[6];
								$thisEmail			= $myArray[7];
								$thisPhone			= $myArray[8];
								$thisSemester		= $myArray[0];
								$thisRequestDate	= $myArray[2];
								$thisClassPriority	= $myArray[9];
								$thisLanguage		= $myArray[12];
								$content			.= "<tr><td><a href='$updateStudentInfoURL?request_type=callsign&request_info=$thisCallSign&inp_depth=one&doDebug=$doDebug&testMode=$testMode&strpass=2' target='_blank'>$thisCallSign</a></td>
															<td>$thisName</td>
															<td>$thisTimeZone</td>
															<td>$thisSemester</td>
															<td>$thisEmail</td>
															<td>$thisPhone</td>
															<td>$thisLanguage</td>
															<td>$thisClassPriority</td>
															<td>$thisClassChoice</td>
															<td>$thisClassLocal</td>";
								if ($noPreAssign) {
									$content		.= "<td>&nbsp;</td>
														<td><a href='$theURL?strpass=40&inp_advisor_callsign=$inp_advisor_callsign&inp_student_callsign=$thisCallSign&level=$advisorclass_level' target='_blank'>Assign</a></td></tr>";
								} else {
									$content		.= "<td><a href='$prodURL?strpass=2&inp_advisor_callsign=$inp_advisor_callsign&inp_student_callsign=$thisCallSign' target='_blank'>Pre-Assign</a></td>
														<td><a href='$theURL?strpass=40&inp_advisor_callsign=$inp_advisor_callsign&inp_student_callsign=$thisCallSign&level=$advisorclass_level' target='_blank'>Assign</a></td></tr>";
								}
							}
							$content				.= "<tr><td colspan='12'>&nbsp;</td></tr>";

						} else {
							$content				.= "<tr><td colspan='12'>No students matched on first class choice</td></tr>";
						}
						$myInt			= count($studentsSecondChoiceMatch);
						if ($myInt > 0) {
							$content				.= "<tr><td colspan='13'>Students whose Second Class Choice matches</td></tr>";
							foreach($studentsSecondChoiceMatch as $myValue) {
								$myArray	= explode("|",$myValue);
								$thisCallSign	= $myArray[3];
								$thisName		= $myArray[4];
								$thisTimeZone	= $myArray[5];
								$thisClassChoice	= $myArray[1];
								$thisClassLocal		= $myArray[6];
								$thisEmail			= $myArray[7];
								$thisPhone			= $myArray[8];
								$thisSemester		= $myArray[0];
								$thisRequestDate	= $myArray[2];
								$thisClassPriority	= $myArray[9];
								$thisAdvisorTime	= $myArray[10];
								$thisAdvisorDays	= $myArray[11];
								$content			.= "<tr><td><a href='$updateStudentInfoURL?request_type=callsign&request_info=$thisCallSign&inp_depth=one&doDebug=$doDebug&testMode=$testMode&strpass=2' target='_blank'>$thisCallSign</a></td>
															<td>$thisName</td>
															<td>$thisTimeZone</td>
															<td>$thisSemester</td>
															<td>$thisEmail</td>
															<td>$thisPhone</td>
															<td>$thisRequestDate</td>
															<td>$thisClassPriority</td>
															<td>$thisClassChoice</td>
															<td>$thisClassLocal</td>";
								if ($noPreAssign) {
									$content		.= "<td>&nbsp;</td>
														<td><a href='$theURL?strpass=40&inp_advisor_callsign=$inp_advisor_callsign&inp_student_callsign=$thisCallSign&level=$advisorclass_level' target='_blank'>Assign</a></td></tr>";
								} else {
									$content		.= "<td><a href='$theURL?strpass=2&inp_advisor_callsign=$inp_advisor_callsign&inp_student_callsign=$thisCallSign' target='_blank'>Pre-Assign</a></td>
														<td><a href='$theURL?strpass=40&inp_advisor_callsign=$inp_advisor_callsign&inp_student_callsign=$thisCallSign&level=$advisorclass_level' target='_blank'>Assign</a></td></tr>";
								}
							}
							$content				.= "<tr><td colspan='12'>&nbsp;</td></tr>";
						} else {
							$content				.= "<tr><td colspan='12'>No students matched on Second Class Choice</td></tr>";
						}
						$myInt			= count($studentsThirdChoiceMatch);
						if ($myInt > 0) {
							$content				.= "<tr><td colspan='13'>Students whose Third Class Choice matches</td></tr>";
							foreach($studentsThirdChoiceMatch as $myValue) {
								$myArray	= explode("|",$myValue);
								$thisCallSign	= $myArray[3];
								$thisName		= $myArray[4];
								$thisTimeZone	= $myArray[5];
								$thisClassChoice	= $myArray[1];
								$thisClassLocal		= $myArray[6];
								$thisEmail			= $myArray[7];
								$thisPhone			= $myArray[8];
								$thisSemester		= $myArray[0];
								$thisRequestDate	= $myArray[2];
								$thisClassPriority	= $myArray[9];
								$thisAdvisorTime	= $myArray[10];
								$thisAdvisorDays	= $myArray[11];
								$content			.= "<tr><td><a href='$updateStudentInfoURL?request_type=callsign&request_info=$thisCallSign&inp_depth=one&doDebug=$doDebug&testMode=$testMode&strpass=2' target='_blank'>$thisCallSign</a></td>
															<td>$thisName</td>
															<td>$thisTimeZone</td>
															<td>$thisSemester</td>
															<td>$thisEmail</td>
															<td>$thisPhone</td>
															<td>$thisRequestDate</td>
															<td>$thisClassPriority</td>
															<td>$thisClassChoice</td>
															<td>$thisClassLocal</td>";
								if ($noPreAssign) {
									$content		.= "<td>&nbsp;</td>
														<td><a href='$theURL?strpass=40&inp_advisor_callsign=$inp_advisor_callsign&inp_student_callsign=$thisCallSign&level=$advisorclass_level' target='_blank'>Assign</a></td></tr>";
								} else {
									$content		.= "<td><a href='$theURL?strpass=2&inp_advisor_callsign=$inp_advisor_callsign&inp_student_callsign=$thisCallSign' target='_blank'>Pre-Assign</a></td>
														<td><a href='$theURL?strpass=40&inp_advisor_callsign=$inp_advisor_callsign&inp_student_callsign=$thisCallSign&level=$advisorclass_level' target='_blank'>Assign</a></td></tr>";
								}
							}
							$content				.= "<tr><td colspan='12'>&nbsp;</td></tr>";
						} else {
							$content				.= "<tr><td colspan='12'>No students matched on Third Class Choice</td></tr>";
						}
						$content					.= "</table>
														<p>Clicking on the 'Pre-Assign' link will open the'Pre-assign a Student' page in a new tab with the
														form partially filled out<br />
														Clicking on the 'Assign' link will open the 'Adding a Student to 
														an Advisor's Class' page in a new tab with the form filled out</p>";
					}
				} else {
					if ($doDebug) {
						echo "no students found matching the criteria<br />";
					}
					$content .= "No unassigned students found that match the criteria<br />";
				}
			}
		}



///////		pass 85			Confirm One or More Students
	} elseif ("85" == $strPass) {
		if ($doDebug) {
			echo "Arrived at pass 85<br />";
		}
		$jobname			= "Student Management Confirm Attendance for One or More Students";
		$content			.= "<h3>$jobname</h3>
								<p>Enter the student's call sign or a list of student call signs separated by 
								commas. The function will confirm that each student is assigned to a class and 
								has a student_status of S. If so, the student_status will be set to Y.</p>
								<p><form method='post' action='$theURL' 
								name='selection_form' ENCTYPE='multipart/form-data'>
								<input type='hidden' name='strpass' value='86'>
								<table style='border-collapse:collapse;'>
								<tr><td style='width:150px;'>Student Call Sign:<br /><em>Enter one or more call signs 
								separated by commas</em></td>
									<td><input class='formInputText' type='text' size= '50' maxlength='100' name='inp_callsign' autofocus></td></tr>
								$testModeOption
								<tr><td>&nbsp;</td><td><input class='formInputButton' type='submit' value='Submit' /></td></tr></table>
								</form>";

	} elseif ("86" == $strPass) {
		if ($doDebug) {
			echo "<br />Arrived at pass 86 with inp_callsign of $inp_callsign<br />";
		}
		$jobname			= "Student Management Confirm Attendance for One or More Students";
		$content			.= "<h3>$jobname</h3>";
		if ($inp_callsign == '') {
			$content		.= "Invalid or Incomplete information provided";
		} else {
			$inp_callsign	= str_replace(" ","",$inp_callsign);
			$callsignArray	= explode(",",$inp_callsign);
			foreach($callsignArray as $thisCallSign) {
				if ($doDebug) {
					echo "<br />Processing $thisCallSign<br />";
				}
				$content			.= "<br />Processing $thisCallSign<br />";

				$studentData = get_student_and_user_master($thisCallSign, 'callsign', $proximateSemester, $operatingMode, $doDebug);
				if ($studentData === FALSE) {
					if ($doDebug) {
						echo "get_student_and_user_master for $thisCallSign returned FALSE<br />";
					}
					$content .= "Failed to get student info for $thisCallSign<br />";
				} else {
					if (! empty($studentData)) {
						foreach($studentData as $key => $value) {
							$$key = $value;
						}
						if ($doDebug) {
							echo "have student record for $student_call_sign:<br />
									Response: $student_response<br />
									Status: $student_status<br />
									Intervention Required: $student_intervention_required<br />
									Assigned Advisor: $student_assigned_advisor<br />
									Assigned Advisor Class: $student_assigned_advisor_class<br />";
						}	
						$content				.= 	"&nbsp;&nbsp;&nbsp;&nbsp;$user_last_name, $user_first_name Advisor: $student_assigned_advisor<br />";	
						$doProcess				= TRUE;
						$updateParams			= array();
						if ($student_intervention_required == 'H') {
							$content			.= "&nbsp;&nbsp;&nbsp;&nbsp;Student is on hold. Bypassed<br />";
							$doProcess			= FALSE;
						}
						if ($student_response != 'Y') {
							if ($student_response == '') {
								$student_response	= 'blank';
							}
							$content			.= "&nbsp;&nbsp;&nbsp;&nbsp;Student response is $student_response. Did not verify. Bypassed<br />";
							$doProcess			= FALSE;
						}
						if ($student_status != 'S') {
							if ($student_status == '') {
								$student_status	= 'blank';
							}
							$content			.= "&nbsp;&nbsp;&nbsp;&nbsp;Student status is $student_status. Must be S. Bypassed<br />";
							$doProcess			= FALSE;
						}
						if ($student_assigned_advisor == '') {
							$content			.= "&nbsp;&nbsp;&nbsp;&nbsp;Student does not have an assigned advisor. Bypassed<br />";
							$doProcess			= FALSE;
						}
						if ($student_assigned_advisor_class == '') {
							$content			.= "&nbsp;&nbsp;&nbsp;&nbsp;Student does not have an assigned advisor class. Bypassed<br />";
							$doProcess			= FALSE;
						}
						if ($doProcess) {
							$student_action_log			= "$student_action_log / $actionDate MGMT85 $userName confirmed student participation ";
							$updateParams				= array('student_status'=>'Y',
																'student_action_log'=>$student_action_log);
							$updateResult = $student_dal->update($student_id, $updateParams, $operatingMode);
							if ($updateResult === FALSE || $updateResult === NULL) {
								echo "update student $student_call_sign (id: $student_id) returned FALSE|NULL<br />";
							} else {
								$content		.= "Student $student_call_sign confirmed<br />";
							}
						}
					} else {
						$content		.= "&nbsp;&nbsp&nbsp;&nbsp;No student record for $thisCallSign. Bypassed<br />";
					}
				}
			}
			$content				.= "<p>Processing completed</p>";
		}



	} elseif ("90" == $strPass) {
		if ($doDebug) {
			echo "<br />Arrived at pass 90<br />";
		}
		$jobname	= "Student Management Add a Student to an Advisor Class Regardless";
		$content .= "<h3>$jobname of Semester or Status</h3>
					<p>Enter the student call sign to be added, the advisor's call sign, and the class 
					number that the advisor is teaching. If the class number isn't known, click the 
					'Show Classes' button and the program 
					will show a list of classes matching the student's level (if only one class, the function 
					will proceed with the assignment). Otherwise, click the 'Assign' 
					button.</p>
					<p>The student will be pulled into the current semester, if necessary<br />
					The student level will be changed, if necessary<br />
					The student response will be set to Y<br />
					The student status will be set to S (awaiting verification)<br />
					The select date will be set to the current date<br />
					If student has been dropped, email number will be set to 3<br />
					Any holds will be released</p>
					<p><b>Note:</b> If this action is taken before students are assigned to advisor classes, 
					that process will override this action. In that case the better option is to pre-assign 
					the student to the advisor's class.</p>
					<form method='post' action='$theURL' 
					name='selection_form' ENCTYPE='multipart/form-data'>
					<input type='hidden' name='strpass' value='91'>
					<table style='border-collapse:collapse;'>
					<tr><td style='width:150px;'>Student Call Sign:</td>
						<td><input class='formInputText' type='text' size= '30' maxlength='30' name='inp_student_callsign' autofocus></td></tr>
					<tr><td style='width:150px;'>Class Level:</td>
						<td><input class='formInputButton' type='radio' name='inp_level' value='Beginner'> Beginner<br />
							<input class='formInputButton' type='radio' name='inp_level' value='Fundamental'> Fundamental<br />
							<input class='formInputButton' type='radio' name='inp_level' value='Intermediate'> Intermediate<br />
							<input class='formInputButton' type='radio' name='inp_level' value='Advanced'> Advanced</td></tr>
					<tr><td style='width:150px;'>Advisor Call Sign:</td>
						<td><input class='formInputText' type='text' size = '30' maxlength='30' name='inp_advisor_callsign'></td></tr>
					<tr><td style='width:150px;'>Advisor Class:</td>
						<td><input class='formInputText' type='text' size = '5' maxlength='5' name='inp_advisorClass'></td></tr>
					$testModeOption
					<tr><td>&nbsp;</td>
						<td><input class='formInputButton' name='submit' type='submit' value='Assign' /></td></tr>
					</table>
					</form>";

	} elseif ("91" == $strPass) {				// get the class 
// $doDebug = TRUE;
		if ($doDebug) {
			echo "<br />At pass 91 with student $inp_student_callsign; level: $inp_level; advisor: $inp_advisor_callsign; class: $inp_advisorClass; submit: $submit<br />";
		}
		$jobname	= "Student Management Add a Student to an Advisor Class Regardless";
		
		$theSemester		= $initializationArray['currentSemester'];
		$nextSemester		= $initializationArray['nextSemester'];
		if ($theSemester == 'Not in Session') {
			$theSemester	= $nextSemester;
		}
		$assignMatch		= FALSE;
				
		$class_sequence		= 0;
		$updateParams = array();                            

		// See if the student is in the student table
		if ($haveStudent) {
			// actualize the student info
			foreach($student as $key => $value) {
				$$key = $value;
			}
			$reqDate = substr($student_request_date,0,10);
			$doProceed = TRUE;
			if ($doDebug) {
				echo "Student $student_call_sign student status is $student_status<br />
					  assigned advisor is $student_assigned_advisor<br />";
			}
		} else {		// no student found
			if ($doDebug) {
				echo "No student found with call sign $inp_student_callsign<br />";
			}
			$content		.= "Student $inp_student_callsign is not registered.";
			$doProceed = FALSE;
		}
		if ($doProceed) {
			// see if there is an advisor in the proximate semester. If so, get the classes at that level for the advisor
			if ($haveAdvisor) {
				// actualize the advisor info
				if ($doDebug) {
					echo "have the advisor record<br />";
				}
				foreach($advisor as $key => $value) {
					$key = str_replace('user_','advisor_user_',$key);
					$$key = $value;
				}
				if($advisor_semester == $theSemester) {
					$optionList				= '';
					$criteria = [
						'relation' => 'AND',
						'clauses' => [
							['field' => 'advisorclass_call_sign', 'value' => $inp_advisor_callsign, 'compare' => '=' ],
							['field' => 'advisorclass_semester', 'value' => $theSemester, 'compare' => '=' ],
							['field' => 'advisorclass_level', 'value' => $inp_level, 'compare' => '=' ]
						]
					];
					$advisorclassData = $advisorclass_dal->get_advisorclasses_by_order( $criteria, 'advisorclass_sequence', 'ASC', $operatingMode );
					if ($advisorclassData === FALSE || $advisorclassData === NULL) {
						if ($doDebug) {
							echo "get_advisor_classes_by_order returned FALSE|NULL<br />";
						}
					} else {
						if (! empty($advisorclassData)) {
							foreach($advisorclassData as $key => $value) {
								foreach($value as $thisField => $thisValue) {
									$$thisField = $thisValue;
								}
	
								if ($inp_advisorClass == $advisorclass_sequence) {
									$class_ID							= $advisorclass_id;	
									$class_sequence					 	= $advisorclass_sequence;
									$assignMatch						= TRUE;
									if ($doDebug) {
										echo "Have a class match. Saving id: $class_ID; sequence: $class_sequence<br />";
									}
								}
							}
							if ($assignMatch) {
								$content			.= "<h3>Add Student $inp_student_callsign to Advisor $inp_advisor_callsign Class Regardless of Semester or Status</h3>";
								if ($doDebug) {
									echo "<br />Doing the assignment<br />
											advisor callsign: $inp_advisor_callsign<br />
											advisor class: $inp_advisorClass<br />
											level: $inp_level<br />
											student: $student_call_sign<br />
											current semester: $student_semester<br />
											current level: $student_level<br />
											current response: $student_response<br />
											current status: $student_status<br />
											current assigned advisor: $student_assigned_advisor<br />
											current assigned advisor class: $student_assigned_advisor_class<br />
											current intervention required: $student_intervention_required<br />
											current email number: $student_email_number<br />";
								}
								// if the student is assigned elsewhere, first remove the student
								if ($student_assigned_advisor != '' && ($student_status == 'S' || $student_status == 'Y')) {
									if ($doDebug) {
										echo "student has to be removed from $student_assigned_advisor $advisor_class_squence class<br />";
									}
									$inp_data			= array('inp_student'=>$student_call_sign,
																'inp_semester'=>$student_semester,
																'inp_assigned_advisor'=>$student_assigned_advisor,
																'inp_assigned_advisor_class'=>$student_assigned_advisor_class,
																'inp_remove_status'=>'',
																'inp_arbitrarily_assigned'=>$student_no_catalog,
																'inp_method'=>'remove',
																'jobname'=>$jobname,
																'userName'=>$userName,
																'testMode'=>$testMode,
																'doDebug'=>$doDebug);
					
									$removeResult		= add_remove_student($inp_data);
									if ($removeResult[0] === FALSE) {
										$thisReason		= $removeResult[1];
										if ($doDebug) {
											echo "attempting to remove $student_call_sign from $student_assigned_advisor class failed:<br />$thisReason<br />";
										}
										sendErrorEmail("$jobname Attempting to remove $student_call_sign from $student_assigned_advisor class failed:<br />$thisReason");
										$content		.= "Attempting to remove $student_call_sign from $student_assigned_advisor class failed:<br />$thisReason<br />";
									} else {
										$content		.= "Student removed from $student_assigned_advisor $student_assigned_advisor_class class and unassigned<br />";
									}
								} elseif ($student_assigned_advisor != '') {			// have to clean up the student record
									$updateParams = array('student_status' => '',
														  'student_assigned_advisor' => '', 
														  'student_assigned_advisor_class' => 0);
									$updateResult = $student_dal->update($student_id, $updateParams, $operatingMode);
									if ($updateResult === FALSE || $updateResult === NULL) {
										if ($doDebug) {
											echo "attempting to update $student_call_sign at id $student_id returned FALSE|NULL<br />";
										}
										$content .= "<p>unable to update student $student_call_sign record atid $student_id</p>";
									}
								}
								// if the student_semester is not the proximate semester, change the semester
								if ($student_semester != $theSemester) {
									if ($doDebug) {
										echo "Student semester $student_semester needs to be changed to $theSemester<br />";
									}
									$updateParams['student_semester'] = $theSemester;
									$updateParams['student_response'] = 'Y';
									$studentUpdateResult = $student_dal->update( $student_id, $updateParams, $operatingMode );
									if ($studentUpdateResult === FALSE || $studentUpdateResult === NULL) {
										if ($doDebug) {
											echo "updating $student_id returned FALSE|NULL<br />";
										}
										$content .= "<p>Unable to update the student semester to $proximateSemester<br />";
									} else {
										$content		.= "Updated student semester to $theSemester<br />";
									}
								}								
								// now assign the student
								if ($doDebug) {
									echo "doing the assignment to $inp_advisor_callsign $class_sequence class<br />";
								}
								$inp_data			= array('inp_student'=>$student_call_sign,
															'inp_semester'=>$theSemester,
															'inp_assigned_advisor'=>$inp_advisor_callsign,
															'inp_assigned_advisor_class'=>$class_sequence,
															'inp_remove_status'=>'',
															'inp_arbitrarily_assigned'=>'',
															'inp_method'=>'add',
															'jobname'=>$jobname,
															'userName'=>$userName,
															'testMode'=>$testMode,
															'doDebug'=>$doDebug);
					
								$addResult			= add_remove_student($inp_data);
								if ($addResult[0] === FALSE) {
									$thisReason		= $addResult[1];
									if ($doDebug) {
										echo "attempting to add $student_call_sign to $student_assigned_advisor class failed:<br />$thisReason<br />";
									}
									sendErrorEmail("$jobname Attempting to add $student_call_sign to $student_assigned_advisor class failed:<br />$thisReason");
									$content		.= "Attempting to add $student_call_sign to $student_assigned_advisor class failed:<br />$thisReason<br />";
								} else {
									$content		.= "Student has been added to $inp_advisor_callsign class $class_sequence<br />
									<p>Click 'Push' to push the information to the advisor:<br />
									<form method='post' action='$pushURL' 
									name='selection_form_41' ENCTYPE='multipart/form-data'>
									<input type='hidden' name='strpass' value='2'>
									<input type='hidden' name='inp_mode' value='$inp_mode'>
									<input type='hidden' name='inp_semester' value='$student_semester'>
									<input type='hidden' name='request_info' value='$inp_advisor_callsign'>
									<input type='hidden' name='request_type' value= 'Full'>
									<input type='submit' class='formInputButton' value='Push'></form></p><br /><br />";
								}								
							} else {
								$content	.= "<p>No advisorClass record found for $inp_advisor_callsign class $inp_advisorClass</p>";
							}
						} else {
							if ($doDebug) {
								echo "No advisorClass entry for $inp_advisor_callsign<br />";
							}
							$content		.= "No advisorClass class entry found for $inp_advisor_callsign";
						}
					}
				} else {
					if ($doDebug) {
						echo "no advisor $inp_advisor found in $theSemester semester<br />";
					}
					$content		.= "No advisor $inp_advisor_callsign found in $advisorTableName for the $theSemester semester.";
				}
			} else {
				if ($doDebug) {
					echo "no advisor $inp_advisor found<br />";
				}
				$content		.= "No advisor $inp_advisor_callsign found.";
			}
		}


///////		pass 100			Verify one or more students
	} elseif ("100" == $strPass) {
		if ($doDebug) {
			echo "<br />Arrived at pass 100<br />";
		}
		$jobname			= "Student Management Verify One or More Students";
		$content			.= "<h3>$jobname</h3>
								<p>Enter the student's call sign or a list of student call signs separated by 
								commas. The function will check that each student is unassigned and not 
								on hold. If so, the response is set to Y.</p>
								<p><form method='post' action='$theURL' 
								name='selection_form' ENCTYPE='multipart/form-data'>
								<input type='hidden' name='strpass' value='101'>
								<table style='border-collapse:collapse;'>
								<tr><td style='width:150px;'>Student Call Sign:<br /><em>Enter one or more call signs 
								separated by commas</em></td>
									<td><input class='formInputText' type='text' size= '50' maxlength='100' name='inp_callsign' autofocus></td></tr>
								$testModeOption
								<tr><td>&nbsp;</td><td><input class='formInputButton' type='submit' value='Submit' /></td></tr></table>
								</form>";

	} elseif ("101" == $strPass) {
		if ($doDebug) {
			echo "<br />Arrived at pass 101 with inp_callsign of $inp_callsign<br />";
		}
		$jobname			= "Student Management Confirm One or More Students";
		$content			.= "<h3>$jobname</h3>";
		if ($inp_callsign == '') {
			$content		.= "Invalid or Incomplete information provided";
		} else {
			$inp_callsign	= str_replace(" ","",$inp_callsign);
			$callsignArray	= explode(",",$inp_callsign);
			foreach($callsignArray as $thisCallSign) {
				if ($doDebug) {
					echo "<br />Processing $thisCallSign<br />";
				}
				$content			.= "<br />Processing $thisCallSign<br />"; 
				$studentData = get_student_and_user_master($thisCallSign, 'callsign', $proximateSemester, $operatingMode, $doDebug);
				if ($studentData === FALSE) {
					if ($doDebug) {
						echo "get_student_and_user_master for $thisCallSign returned FALSE<Br /";
					}
				} else {
					if (! empty($studentData)) {
						foreach($studentData as $key => $value) {
							$$key = $value;
						}
						$updateParams = array();
						if ($doDebug) {
							echo "have student record for $student_call_sign:<br />
									Response: $student_response<br />
									Status: $student_status<br />
									Intervention Required: $student_intervention_required<br />";
						}	
						$content				.= 	"&nbsp;&nbsp;&nbsp;&nbsp;$user_last_name, $user_first_name ($student_call_sign)<br />";	
						$doProcess				= TRUE;
						$updateParams			= array();
						if ($student_intervention_required == 'H') {
							$content			.= "&nbsp;&nbsp;&nbsp;&nbsp;Student is on hold. Bypassed<br />";
							$doProcess			= FALSE;
						}
						if ($student_response != '') {
							$content			.= "&nbsp;&nbsp;&nbsp;&nbsp;Student response is $student_response. Did not confirm. Bypassed<br />";
							$doProcess			= FALSE;
						}
						if ($student_status != '') {
							$content			.= "&nbsp;&nbsp;&nbsp;&nbsp;Student status is $student_status. Must be blank. Bypassed<br />";
							$doProcess			= FALSE;
						}
						if ($doProcess) {
							$student_action_log			= "$student_action_log / $actionDate MGMT100 $userName confirmed student interest ";
							$updateParams				= array('student_response'=>'Y',
																'student_response_date'=>$currentDate,
																'student_action_log'=>$student_action_log);
							$updateResult = $student_dal->update($student_id, $updateParams, $operatingMode); 
							if ($updateResult === 'FALSE' || $updateResult === NULL) {
								if ($doDebug) {
									echo "updating $student_call_sign (id: $student_id) returned FALSE|Null<br />";
								}
							} else {
								$content		.= "Student $student_call_sign Verified<br />";
							}
						}
					} else {
						$content		.= "&nbsp;&nbsp&nbsp;&nbsp;No student record. Bypassed<br />";
					}
				}
			}
			$content				.= "<p>Processing completed</p>";
		}
		
		
/////// pass 110 -- add excluded advisor		
		
		
	} elseif ("110" == $strPass) {
		if ($doDebug) {
			echo "<br />At pass 110 -- add excluded advisor<br />";
		}
		$jobname			= "Student Management Add Excluded Advisor";
		$content			.= "<h3>$jobname</h3>
								<p>Enter the student's call sign to which the specified advisor should be 
								excluded</p>
								<p><form method='post' action='$theURL' 
								name='selection_form' ENCTYPE='multipart/form-data'>
								<input type='hidden' name='strpass' value='112'>
								<table style='border-collapse:collapse;'>
								<tr><td style='width:150px;'>Student Call Sign:<br /></td>
									<td><input class='formInputText' type='text' size= '50' maxlength='100' name='inp_student_callsign' autofocus></td></tr>
								<tr><td style='width:150px;'>Excluded advisor callsign:<br /></td>
									<td><input class='formInputText' type='text' size= '50' maxlength='100' name='inp_excluded_advisor'></td></tr>
								$testModeOption
								<tr><td>&nbsp;</td><td><input class='formInputButton' type='submit' value='Submit' /></td></tr></table>
								</form>";
	} elseif ("112" == $strPass) {

// $doDebug	= TRUE;

		if ($doDebug) {
			echo "<br />At pass 112 with inp_student_callsign $inp_student_callsign and advisor callsign: $inp_excluded_advisor<br />";
		}
		$jobname			= "Student Management Add Excluded Advisor";
		$content			.= "<h3>$jobname</h3>";

		$doProceed			= TRUE;		
		if ($haveStudent) {
			// actualize student info
			foreach($student as $key => $value) {
				$$key = $value;
			}
			if ($doDebug) {
				echo "Have student record. Preparing to update<br />
				student_excluded_advisor: $student_excluded_advisor<br />";
			}
		} else {
			if ($doDebug) {
				echo "haveStudent is FALSE<br />";
				$content		.= "<p>No student record found for student $inp_student_callsign<br />";
				$doProceed 		= FALSE;
			}
		}
		if ($doProceed) {
			$newExcludedAdvisorList	= updateExcludedAdvisor($student_excluded_advisor,$inp_excluded_advisor,'add',$doDebug);
			if ($newExcludedAdvisorList === FALSE) {
				$content		.= "<p>Attempt to add $inp_callsign as an excluded advisor for $student_call_sign failed</p>";
			} else {
				// update if excluded advisors has changed
				if ($student_excluded_advisor != $newExcludedAdvisorList) {
					$student_action_log .= " / MGMT110 $userName $actionDate updated excluded advisors to $newExcludedAdvisorList ";
					$updateParams		= array('student_assigned_advisor' => $newExcludedAdvisorList,
												'student_action_log' => $student_adtion_log);
					$updateResult = $student_dal->update($student_id,$updateParams,$operatingMode);
					if ($updateResult === FALSE || $updateResult === NULL) {
						if ($doDebug) {
							echo "update $student_call_sign (id: $student_id) returned FALSE|NULL<br />";
						}
					} else {
						$content		.= "<p>Advisor $inp_excluded_advisor has been excluded for student $inp_student_callsign</p>";
					}
				} else {
					$content	.= "<p>No change to student_excluded_advisor was needed</p>";
				}
			}
		}

/////// pass 120 -- remove excluded advisor		
		
		
	} elseif ("120" == $strPass) {
		if ($doDebug) {
			echo "<br />At pass 120 -- remove excluded advisor<br />";
		}
		$jobname			= "Student Management Remove Excluded Advisor";
		$content			.= "<h3>$jobname</h3>
								<p>Enter the student's call sign from which the specified advisor should be 
								no longer be excluded</p>
								<p><form method='post' action='$theURL' 
								name='selection_form' ENCTYPE='multipart/form-data'>
								<input type='hidden' name='strpass' value='122'>
								<table style='border-collapse:collapse;'>
								<tr><td style='width:150px;'>Student Call Sign:<br /></td>
									<td><input class='formInputText' type='text' size= '50' maxlength='100' name='inp_callsign' autofocus></td></tr>
								<tr><td style='width:150px;'>Excluded advisor callsign:<br /></td>
									<td><input class='formInputText' type='text' size= '50' maxlength='100' name='inp_excluded_advisor'></td></tr>
								$testModeOption
								<tr><td>&nbsp;</td><td><input class='formInputButton' type='submit' value='Submit' /></td></tr></table>
								</form>";
	} elseif ("122" == $strPass) {

// $doDebug	= TRUE;

		if ($doDebug) {
			echo "<br />At pass 122 with inp_student_callsign $inp_student_callsign and advisor callsign: $inp_callsign<br />";
		}
		$jobname			= "Student Management Remove Excluded Advisor";
		$content			.= "<h3>$jobname</h3>";

		$doProceed			= TRUE;		
		// get all the student records
		// remove the advisor to be removed from each one, if present
		$criteria = [
			'relation' => 'AND',
			'clauses' => [
				['field' => 'student_call_sign', 'value' => $inp_callsign, 'compare' => '=' ]
			]
		];
		$studentData = $student_dal->get_student_by_order( $criteria, 'student_date_created', 'DESC', $operatingMode );
		if ($studentData === FALSE || $studentData === NULL) {
			if ($doDebug) {
				echo "get_student for $student_call_sign returned FALSE|NULL<br />";
			}
		} else {
			if (! empty($studentData)) {
				foreach($studentData as $key => $value) {
					foreach($value as $thisField => $thisValue) {
						$$thisField = $thisValue;
					}

					if ($doDebug) {
						echo "<br />processing id $student_id ($student_call_sign)<br />";
					}
					$newExcludedAdvisorList		= updateExcludedAdvisor($student_excluded_advisor,$inp_callsign,'delete',$doDebug);
					if ($newExcludedAdvisorList === FALSE) {
						$content				.= "<p>Attempt to delete $inp_excluded_advisor from excluded advisors $student_excluded_advisor failed for id $student_id</p>";
					} else {
						if ($student_excluded_advisor != $newExcludedAdvisorList) {
							$student_action_log .= " / MGMT120 $actionDate $userName removed $inp_excluded_advisor from excluded advisors ";
							$updateParams = array('student_action_log' => $student_action_log,
												  'student_excluded_advisor' => $newExcludedAdvisorList);
							$updateResult = $student_dal->update($student_id, $updateParams, $operatingMode);
							if ($updateResult=== FALSE || $updateResult === NULL) {
								if ($doDebug) {
									echo "update $student_call_sign (id: $student_id) returned FALSE|NULL<br />";
								}
							} else {
								$content		.= "<p>Advisor $inp_excluded_advisor has been removed as an excluded advisor in student record for $student_semester semester</p>";
							}							
						} else {
							$content			.= "<p>No changes needed for id $student_id ($student_call_sign) for semester $student_semester</p>";
						}
					}
				}
			} else {
				$content		.= "<p>No student records found for $student_call_sign</p>";
			}
		} 
	}

	
		
//	$content		.= "<br /><p>To return to the Student Management menu, click
//						<a href='$theURL?strpass=1'>HERE</a>.</p>";
	$thisTime 		= date('Y-m-d H:i:s');
	$content 		.= "<br /><br /><p>Prepared at $thisTime</p>";
	$endingMicroTime = microtime(TRUE);
	$elapsedTime	= $endingMicroTime - $startingMicroTime;
	$elapsedTime	= number_format($elapsedTime, 4, '.', ',');
	$content		.= "<p>Report pass $strPass took $elapsedTime seconds to run</p>";
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
add_shortcode ('student_management', 'student_management_func');
