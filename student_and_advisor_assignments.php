function student_and_advisor_assignments_func() {

	global $wpdb, $advisorClassArray, $doDebug;

	$initializationArray = data_initialization_func();
	$validUser = $initializationArray['validUser'];
	if ($validUser == "N") {
		return "YOU'RE NOT AUTHORIZED!<br />Goodby";
	}
/// get the time that the process started
	$startingMicroTime			= microtime(TRUE);

	$doDebug 					= FALSE;
	$testMode					= FALSE;
	$thisVersion				= '4';
	$inp_semester				= $initializationArray['nextSemester'];
	$userName					= $initializationArray['userName'];
	$siteURL					= $initializationArray['siteurl'];
	$strPass					= "1";
	$errorCount					= 0;
	$xStudentCount				= 0;
	$defaultClassSize			= $initializationArray['defaultClassSize'];
	$inp_rsave				 	= '';
	$inp_verified				= '';
	$inp_verbose			 	= 'N';
	$theURL						= "$siteURL/cwa-student-and-advisor-assignments/";
	$studentUpdateURL			= "$siteURL/cwa-display-and-update-student-signup-information/";	
	$studentHistoryURL		 	= "$siteURL/cwa-show-detailed-history-for-student/";	
	$advisorUpdateURL			= "$siteURL/cwa-display-and-update-advisor-information/";	
	$studentManagementURL		= "$siteURL/cwa-student-management/";
	$pastSemesters				= $initializationArray['pastSemesters'];
	$currentSemester			= $initializationArray['currentSemester'];
	$nextSemester				= $initializationArray['nextSemester'];
	$semesterTwo				= $initializationArray['semesterTwo'];
	$semesterThree				= $initializationArray['semesterThree'];
	$pastSemesterArray			= explode("|",$pastSemesters);
	$inp_semesterlist			= '';
	$validTestmode				= $initializationArray['validTestmode'];
	$jobname					= "Student and Advisor Assignments V$thisVersion";
	
	$levelConvert				= array('Beginner'=>1,'Fundamental'=>2,'Intermediate'=>3,'Advanced'=>4);
	$levelBack					= array(1=>'Beginner',2=>'Fundamental',3=>'Intermediate',4=>'Advanced');

	ini_set('display_errors','1');
	error_reporting(E_ALL);	
	ini_set('memory_limit','256M');
	ini_set('max_execution_time',0);

	
// get the input information
	if (isset($_REQUEST)) {
		foreach($_REQUEST as $str_key => $str_value) {
			if ($doDebug) {
				echo "Key: $str_key | Value: $str_value <br />\n";
			}
			if ($str_key 		== "inp_debug") {
				$inp_debug		 = $str_value;
				$inp_debug		 = filter_var($inp_debug,FILTER_UNSAFE_RAW);
				if ($inp_debug == 'Y') {
					$doDebug	= TRUE;
				}
			}
			if ($str_key 		== "strpass") {
				$strPass		 = $str_value;
				$strPass		 = filter_var($strPass,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "inp_semester") {
				$inp_semester		 = $str_value;
				$inp_semester		 = filter_var($inp_semester,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "inp_semesterlist") {
				$inp_semesterlist		 = $str_value;
				$inp_semesterlist		 = filter_var($inp_semesterlist,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "inp_type") {
				$inp_type		 = $str_value;
				$inp_type		 = filter_var($inp_type,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "level") {
				$inp_level		 = $str_value;
				$inp_level		 = filter_var($inp_level,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "timezone") {
				$inp_timezone		 = $str_value;
				$inp_timezone		 = filter_var($inp_timezone,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "inp_rsave") {
				$inp_rsave		 = $str_value;
				$inp_rsave		 = filter_var($inp_rsave,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "inp_verified") {
				$inp_verified		 = $str_value;
				$inp_verified		 = filter_var($inp_verified,FILTER_UNSAFE_RAW);
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
	if ($testMode) {
		$extMode				= 'tm';
		if ($doDebug) {
			echo "Function is under development. Using student2 and advisor2, not the production data.<br />";
		}
		$theStatement			= "<p>Running in TESTMODE using test data.</p>";
		$operatingMode = 'Testmode';
	} else {
		$extMode				= 'pd';
		$theStatement			= "";
		$operatingMode = 'Production';
	}

	$student_dal = new CWA_Student_DAL();
	$advisor_dal = new CWA_Advisor_DAL();
	$advisorclass_dal = new CWA_Advisorclass_DAL();
	$user_dal = new CWA_User_Master_DAL();

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
	if ($strReasonCode	== 'X') {
		return "(X) Student is being recycled for schedule issues";
	}
	if ($strReasonCode == 'N') {
		return "(N) Student is unassigned and has been moved to the next semester";
	}
	return "($strReasonCode) unknown";
}	



	
// The content to be returned initially includes the special style information.
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
				
				table{font:'Times New Roman', sans-serif;background-image:none;}
				
				th {color:#ffff;background-color:#000;padding:5px;font-size:small;}
				
				td {padding:5px;font-size:small;}
				
				table {table-layout:auto;padding:5px;}
				
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
			echo "Function starting. Building semester option list<br />";
		}
		$optionList			= "";
		$thisChecked		= "";
		if ($currentSemester != 'Not in Session') {
			$optionList		.= "<input type='radio' class='formInputButton' name='inp_semesterlist' value='$currentSemester' checked='checked'> $currentSemester<br />";
		} else {
			$thisChecked	= "checked";
		}
		$optionList		.= "<input type='radio' class='formInputButton' name='inp_semesterlist' value='$nextSemester' $thisChecked > $nextSemester<br />";		
		$optionList		.= "<input type='radio' class='formInputButton' name='inp_semesterlist' value='$semesterTwo'> $semesterTwo<br />";		
		$optionList		.= "<input type='radio' class='formInputButton' name='inp_semesterlist' value='$semesterThree'> $semesterThree<br />";
		$optionList		.= "----<br />";
		$myInt				= count($pastSemesterArray) - 1;
		for ($ii=$myInt;$ii>-1;$ii--) {
	 		$thisSemester		= $pastSemesterArray[$ii];
			$optionList		.= "<input type='radio' class='formInputButton' name='inp_semesterlist' value='$thisSemester'> $thisSemester<br />";
			if ($doDebug) {
				echo "Added $thisSemester to option list<br />";
			}
		}
		if ($doDebug) {
			echo "optionlist complete<br />";
		}
		
	if (in_array($userName,$validTestmode)) {			// give option to run in test mode 
		$testModeOption	= "<tr><td>Operation Mode</td>
								<td><input type='radio' class='formInputButton' name='inp_mode' value='Production' checked='checked'> Production<br />
									<input type='radio' class='formInputButton' name='inp_mode' value='TESTMODE'> TESTMODE</td></tr>
							<tr><td>Verbose Debugging?</td>
								<td><input type='radio' class='formInputButton' name='inp_debug' value='N' checked='checked'> Standard Output<br />
									<input type='radio' class='formInputButton' name='inp_debug' value='Y'> Turn on Debugging </td></tr>";
		} else {
			$testModeOption	= '';
		}
		$content 			.= "<h3>$jobname</h3>
								$theStatement
								<p>Select the semester of interest from the list below:</p>
								<form method='post' action='$theURL' 
								name='selection_form' ENCTYPE='multipart/form-data''>
								<input type='hidden' name='strpass' value='2'>
								<table style='border-collapse:collapse;'>
								<tr><td style='width:150px; vertical-align:top;'>Semester</td><td>
								$optionList
								</td></tr>
								<tr><td>Advisor Records to Include</td>
								<td><input type='radio' class='formInputButton' name='inp_type' value='assigned' checked='checked'>Assigned Advisors (use after students have been assigned)<br />
									<input type='radio' class='formInputButton' name='inp_type' value='pre-assigned'>Pre-assigned Advisors and Assigned Advisors (use before students have been assigned)</td></tr>
								<tr><td>Student Records to Include</td>
								<td><input type='radio' class='formInputButton' name='inp_verified' value='verified' checked='checked'>Verified students (use after students verified attendance)<br />
									<input type='radio' class='formInputButton' name='inp_verified' value='all'>All students except R</td></tr>
								$testModeOption
								<tr><td>Save this report to the reports achive?</td>
								<td><input type='radio' class='formInputButton' name='inp_rsave' value='N' checked='checked'> Do not save the report<br />
									<input type='radio' class='formInputButton' name='inp_rsave' value='Y'> Save a copy of the report the report</td></tr>
								<tr><td>&nbsp;</td><td><input class='formInputButton' type='submit' value='Submit' /></td></tr></table>
								</form>";

	} elseif ("2" == $strPass) {
		if ($doDebug) {
			echo "At pass 2 in version $thisVersion<br />";
		}
 		
 		$inp_semester				= $inp_semesterlist;
 		

		$doPreAssigned				= FALSE;
		$preAssignedStr				= 'FALSE';
 		if ($inp_type == 'pre-assigned') {
 			$doPreAssigned			= TRUE;
 			$preAssignedStr			= 'TRUE';
 		}
 		if ($doDebug) {
 			echo "Using inp_semester: $inp_semester<br />
					doPreAssigned: $preAssignedStr<br />";
 		}
 
		$content .= "<h2>$jobname</h2>\n
					$theStatement
					<p><a href='#report1'>Go to the Advisor Assignment Information Report</a><br />\n
					<a href='#report2'>Go to the Students Assignment Information</a><br />\n
					<a href='#reportW'>Go to the Students Who Withdrew</a><br />\n
					<a href='#reportX'>Go to the Students Who Were Requested to be Replaced Report</a><br />\n
					<a href='#reportBB'>Go to All Advisors and Class Slots</a><br />\n
					<a href='#reportS'>Go to the Advisors with Small Classes Report</a><br />\n
					<a href='#reportY'>Go to the Advisor Class Slots Available Report</a><br />\n
					<a href='#report3'>Go to the Unassigned Student Information Report</a><br />\n
					<a href='#reportH'>Go to the Students on Hold Report</a><br />\n
					</p>\n";

		if ($doDebug) {
			echo "<br /><b>Starting the Advisor Assignment Information Report</b><br />\n";
		}
		
		if($doDebug) {
			echo "<br />Start of current advisor information report<br />";
		}
		$totalAdvisorClasses		= 0;
		$totalStudents				= 0;
		$badAdvisors				= array();
		$content					.= "<a name='report1'><h3>Current Advisor Assignment Information for $inp_semester</h3>\n";

 		// get each advisor
		$criteria = [
			'relation' => 'AND',
			'clauses' => [
				['field' => 'advisor_semester', 'value' => $inp_semester, 'compare' => '=' ]
			]
		];
		$orderby = 'advisor_call_sign';
		$order = 'ASC';
		$requestInfo = array('criteria' => $criteria,
							 'orderby' => $orderby,
							 'order' => $order);
		$advisorData = get_advisor_and_user_master('', 'complex', $requestInfo, $operatingMode, $doDebug);
		if ($advisorData === FALSE) {
			echo "get_advisor_and_user_master returned FALSE<br />";
		} else {
			if (! empty($advisorData)) {
				$numARows = count($advisorData);
				if ($doDebug) {
					echo "have $numARows of advisors:<br /><pre>";
					print_r($advisorData);
					echo "</pre><br />";
				}
				foreach($advisorData as $key => $value) {
					foreach($value as $thisField => $thisValue) {
						$$thisField = $thisValue;
					}
							
					// determine if we can use this advisor
					$doProceed							= TRUE;
					if ($user_survey_score == 6 || $user_survey_score == 13) {
						if ($doDebug) {
							echo "Can not use $advisor_call_sign due to survey score of $user_survey_score<br />";
						}
						$doProceed						= FALSE;
						$badAdvisors[]					= $advisor_call_sign;
					}
					if ($doProceed) {					
						if ($doDebug) {
							echo "Preparing advisor class display for $advisor_call_sign<br />";
						}
		
						$result				= prepare_preassigned_class_display($advisor_call_sign,
																				$inp_semester,
																				'Full', 			// full or sonly
																				FALSE,				// show verified
																				TRUE,				// include header
																				$doPreAssigned,		// do preassigned and assigned or assigned only
																				TRUE,				// doFind: whether or not to show link for finding students
																				$testMode,
																				$doDebug); 
						if ($result[0] === FALSE) {
							if ($doDebug) {
								echo "prepare_advisor for $advisor_call_sign failed<br />
									  reason: $result[1]<br />";
							}
						} else {
							$content				.= $result[1];
							$thisClasses			= $result[2];
							$thisStudentCount		= $result[3];
							$totalAdvisorClasses	= $totalAdvisorClasses + $thisClasses;
							$totalStudents			= $totalStudents + $thisStudentCount;
							if ($doDebug) {
								echo "displayed content for $advisor_call_sign<br />";
							}
						}
					}
 				}
 				$content			.= "<p>Total Advisors: $numARows<br />
 										Total Advisor Classes: $totalAdvisorClasses<br />
 										Total Assigned Students: $totalStudents</p>";
 			} else {
 				$content			.= "No advisor records found<br />";
 				if ($doDebug) {
 					echo "no advisor records found<br />";
 				}
 			}
 		}
		if($doDebug) {
			echo "<br />end of current advisor information report<br /><br />";
		}
// goto Bypass;

		
//////// end of Current Advisor Assignment Information report			
	
	
//////// Start of Student Assignment Information Report		
// $doDebug = TRUE;
			if ($doDebug) {
				echo "<br />Preparing Student Assignment Information Report<br />";
			}
			

			$thisBeginner			= 0;
			$thisFundamental		= 0;
			$thisIntermediate		= 0;
			$thisAdvanced			= 0;
			
			$content		.= "<a name='report2'><h3>Student Assignment Information for $inp_semester</h3></a>";

			$BeginnerArray		= array();
			$FundamentalArray	= array();
			$IntermediateArray	= array();
			$AdvancedArray		= array();	
			$studentCount		= 0;
			$verifiedCount		= 0;								

			$criteria = [
				'relation' => 'AND',
				'clauses' => [
					['field' => 'student_semester', 'value' => $inp_semester, 'compare' => '='],
					['field' => 'student_response', 'value' => 'R', 'compare' => '!='],
					['field' => 'student_promotable', 'value' => 'W', 'compare' => '!='],
					
					[
						'relation' => 'OR',
						'clauses' => [
							['field' => 'student_status', 'value' => 'S', 'compare' => '='],
							['field' => 'student_status', 'value' => 'Y', 'compare' => '=']
						]
					]
				]
			];
					
			$orderby = 'student_level,student_call_sign';
			$order = 'ASC';
			$requestInfo = array('criteria' => $criteria,
								 'orderby' => $orderby,
								 'order' => $order);
			$studentData = get_student_and_user_master('', 'complex', $requestInfo, $operatingMode, $doDebug);
			if ($studentData == FALSE) {
				if ($doDebug) {
					echo "getting student and user master returned FALSE<br />";
				}
			} else {
				if (! empty($studentData)) {
					foreach($studentData as $key => $value) {
						foreach($value as $thisField => $thisValue) {
							$$thisField = $thisValue;
						}
						$studentCount++;
						if ($student_status == 'Y') {
							$verifiedCount++;
						}

						${$student_level . 'Array'}[]		 	= "<tr><td style='vertical-align:top;'>$student_level</td>
																		<td style='vertical-align:top;'>$student_assigned_advisor</td>
																		<td style='text-align:center;vertical-align:top;'>$student_assigned_advisor_class</td>
																		<td style='text-align:center;vertical-align:top;'>$student_class_language</td>
																		<td style='text-align:center;vertical-align:top;'>$user_last_name, $user_first_name, ($student_call_sign)</td>
																		<td style='text-align:center;vertical-align:top;'>$student_timezone_offset</td>
																		<td style='text-align:center;vertical-align:top;'>$student_status</td>
																		<td style='vertical-align:top;'>$user_email</td>
																		<td style='vertical-align:top;'>+$user_ph_code $user_phone</td>
																		<td style='vertical-align:top;'>$user_state</td>
																		<td style='vertical-align:top;'>$user_country</td>
																		<td style='text-align:center;vertical-align:top;'>$student_promotable</td></tr>";
					}


					if (count($BeginnerArray) > 0) {
						$myCount			= count($BeginnerArray);
						$content			.= "<table style='width:1000px;'>
												<tr><th>Level</th>
													<th>Advisor</th>
													<th>Class</th>
													<th>Language</th>
													<th>Student</th>
													<th>TZ</th>
													<th>Status</th>
													<th>Email</th>
													<th>Phone</th>
													<th>State</th>
													<th>Country</th>
													<th>Prom</th></tr>";
						foreach($BeginnerArray as $thisValue) {
							$content		.= $thisValue;
						}
						$content			.= "</table>
												<p>Total Assigned Beginner Students: $myCount</p>";
					}
					if (count($FundamentalArray) > 0) {
						$myCount			= count($FundamentalArray);
						$content			.= "<table style='width:1000px;'>
												<tr><th>Level</th>
													<th>Advisor</th>
													<th>Class</th>
													<th>Language</th>
													<th>Student</th>
													<th>TZ</th>
													<th>Status</th>
													<th>Email</th>
													<th>Phone</th>
													<th>State</th>
													<th>Country</th>
													<th>Prom</th></tr>";
						foreach($FundamentalArray as $thisValue) {
							$content		.= $thisValue;
						}
						$content			.= "</table>
												<p>Total Assigned Fundamental Students: $myCount</p>";
					}
					if (count($IntermediateArray) > 0) {
						$myCount			= count($IntermediateArray);
						$content			.= "<table style='width:1000px;'>
												<tr><th>Level</th>
													<th>Advisor</th>
													<th>Class</th>
													<th>Language</th>
													<th>Student</th>
													<th>TZ</th>
													<th>Status</th>
													<th>Email</th>
													<th>Phone</th>
													<th>State</th>
													<th>Country</th>
													<th>Prom</th></tr>";
						foreach($IntermediateArray as $thisValue) {
							$content		.= $thisValue;
						}
						$content			.= "</table>
												<p>Total Assigned Intermediate Students: $myCount</p>";
					}
					if (count($AdvancedArray) > 0) {
						$myCount			= count($AdvancedArray);
						$content			.= "<table style='width:1000px;'>
												<tr><th>Level</th>
													<th>Advisor</th>
													<th>Class</th>
													<th>Language</th>
													<th>Student</th>
													<th>TZ</th>
													<th>Status</th>
													<th>Email</th>
													<th>Phone</th>
													<th>State</th>
													<th>Country</th>
													<th>Prom</th></tr>";
						foreach($AdvancedArray as $thisValue) {
							$content		.= $thisValue;
						}
						$content			.= "</table>
												<p>Total Assigned Advanced Students: $myCount</p>
												<p>Total Assigned Students: $studentCount<br />
												Confirmed Students: $verifiedCount</p>";
					}
					unset($BeginnerArray);
					unset($FundamentalArray);
					unset($IntermediateArray);
					unset($advancedArray);
							
				} else {
					$content	.= "No assigned students found in $studentTableName table";
				}
			}	
			if ($doDebug) {
				echo "end of the student assignment information report<br />";
			}
// $doDebug = FAlSE;
// goto Bypass;
		
//////// End of Student Assignment Information Report	


//////// Start of the withdrawn Report
// $doDebug = TRUE;

			if ($doDebug) {
				echo "<br >Start of the withdrawn Report<br />";
			}
			// Prepare report of withdrawn Students
			$content		.= "<a name='reportW'><h3>Students Who Have Withdrawn (promotable = W)</h3></a>";
			$cCount			= 0;
			$rCouht			= 0;

			$criteria = [
				'relation' => 'AND',
				'clauses' => [
					['field' => 'student_semester', 'value' => $inp_semester, 'compare' => '='],
					['field' => 'student_promotable', 'value' => 'W', 'compare' => '='],
					
					[
						'relation' => 'OR',
						'clauses' => [
							['field' => 'student_status', 'value' => 'S', 'compare' => '='],
							['field' => 'student_status', 'value' => 'Y', 'compare' => '=']
						]
					]
				]
			];
					
			$orderby = 'student_level,student_call_sign';
			$order = 'ASC';
			$requestInfo = array('criteria' => $criteria,
								 'orderby' => $orderby,
								 'order' => $order);
			$studentData = get_student_and_user_master('', 'complex', $requestInfo, $operatingMode, $doDebug);
			if ($studentData == FALSE) {
				if ($doDebug) {
					echo "getting student and user master returned FALSE<br />";
				}
			} else {
				if (! empty($studentData)) {
					$content		.= "<table style='width:900px;'><tr>
										<th>Name</th>
										<th>Email</th>
										<th>Phone</th>
										<th>State</th>
										<th>TZ</th>
										<th>Level</th>
										<th>Language</th>
										<th>Promotable</th>
										<th>Former<br />Advisor</th></tr>";
					foreach($studentData as $key => $value) {
						foreach($value as $thisField => $thisValue) {
							$$thisField = $thisValue;
						}
						$studentCount++;
						
						if ($doDebug) {
							echo "processing student $student_call_sign with status of $student_status<br />";
						}

						$theLink			= "$user_last_name, $user_first_name <a href='$siteURL/cwa-display-and-update-student-information/?request_type=callsign&request_info=$student_call_sign&request_table=$studentTableName&strpass=2' target='_blank'>($student_call_sign)";
						$content			.= "<tr><td>$theLink</td>
													<td>$user_email</td>
													<td>$user_ph_code $user_phone</td>
													<td>$user_state</td>
													<td>$student_timezone_offset</td>
													<td>$student_level</td>
													<td>$student_class_language</td>
													<td>$student_promotable</td>
													<td>$student_assigned_advisor ($student_assigned_advisor_class)</td></tr>";
					}
					$content				.= "</table>
													<p>$numSRows students</p>";
				} else {
					$content				.= "<p>No students matching this criteria</p>";
				}
				if ($doDebug) {
					echo "end of the Withdrawn report<br /><br />";
				}
			}
// $doDebug = FALSE;			
//////// End of the Withdrawn Report




	
//////// Start of the C&R Report
// $doDebug = TRUE;

			if ($doDebug) {
				echo "<br >Start of the C&R Report<br />";
			}
			// Prepare report of C and R Students
			$content		.= "<a name='reportX'><h3>Students with a Status of C, R, or V</h3></a>";
			$cCount			= 0;
			$rCouht			= 0;
								
			$criteria = [
				'relation' => 'AND',
				'clauses' => [
					['field' => 'student_semester', 'value' => $inp_semester, 'compare' => '='],					
					[
						'relation' => 'OR',
						'clauses' => [
							['field' => 'student_status', 'value' => 'C', 'compare' => '='],
							['field' => 'student_status', 'value' => 'R', 'compare' => '='],
							['field' => 'student_status', 'value' => 'V', 'compare' => '=']
						]
					]
				]
			];
					
			$orderby = 'student_level,student_call_sign';
			$order = 'ASC';
			$requestInfo = array('criteria' => $criteria,
								 'orderby' => $orderby,
								 'order' => $order);
			$studentData = get_student_and_user_master('', 'complex', $requestInfo, $operatingMode, $doDebug);
			if ($studentData == FALSE) {
				if ($doDebug) {
					echo "getting student and user master returned FALSE<br />";
				}
			} else {
				if (! empty($studentData)) {
					$content		.= "<table style='width:900px;'><tr>
										<th>Name</th>
										<th>Email</th>
										<th>Phone</th>
										<th>State</th>
										<th>TZ</th>
										<th>Level</th>
										<th>Language</th>
										<th>Status</th>
										<th>Former<br />Advisor</th></tr>";
					foreach($studentData as $key => $value) {
						foreach($value as $thisField => $thisValue) {
							$$thisField = $thisValue;
						}
						$studentCount++;
						
						if ($doDebug) {
							echo "processing student $student_call_sign with status of $student_status<br />";
						}

						$theLink			= "$user_last_name, $user_first_name <a href='$studentUpdateURL?request_type=callsign&request_info=$student_call_sign&inp_depth=one&doDebug=$doDebug&testMode=$testMode&strpass=2' target='_blank'>($student_call_sign)";
						$content			.= "<tr><td>$theLink</td>
													<td>$user_email</td>
													<td>$user_ph_code $user_phone</td>
													<td>$user_state</td>
													<td>$student_timezone_offset</td>
													<td>$student_level</td>
													<td>$student_class_language</td>
													<td style='text-align:center;'>$student_status</td>
													<td>$student_assigned_advisor ($student_assigned_advisor_class)</td></tr>";
					}
					$content				.= "</table>
												<p>$numSRows students</p>";
				} else {
					$content				.= "<p>No students matching this criteria</p>";
				}
			}
			if ($doDebug) {
				echo "end of the CRV report<br /><br />";
			}

// $doDebug = FALSE;			
//////// End of the C&R Report

// $doDebug = TRUE;
			// get the advisorclass information for the next few reports
			
			if ($doDebug) {
				echo "getting the advisorclass info for the next few reports<br />";
			}
			
			$advisorArray				= array();
			$criteria = [
				'relation' => 'AND',
				'clauses' => [
					['field' => 'advisorclass_semester', 'value' => $inp_semester, 'compare' => '=' ]
				]
			];
			$orderby = 'advisorclass_call_sign, $advisorclass_sequence';
			$order = 'ASC';
			$advisorclassData = $advisorclass_dal->get_advisorclasses_by_order( $criteria, $orderby, $order, $operatingMode );
			if ($advisorclassData === FALSE || $advisorclassData === NULL) {
				if ($doDebug) {
					echo "get_advisorclasses_by_order returned FALSE|NULL<br />";
				}
			} else {
				if (! empty($advisorclassData)) {
					foreach($advisorclassData as $key => $value) {
						foreach($value as $thisField => $thisValue) {
							$$thisField = $thisValue;
						}
	
						// see if we can use this advisor
						$doProceed				= TRUE;
						if (in_array($advisorclass_call_sign,$badAdvisors)) {
							if ($doDebug) {
								echo "can not use $advisorclass_call_sign due to bad survey score<br />";
							}
							$doProceed			= FALSE;
						}
						if ($doProceed) {						
							if ($doDebug) {
								echo "processing advisorclass $advisorclass_call_sign; class $advisorclass_sequence<br />";
							}
							$theLink			= "<a href='$siteURL/cwa-display-and-update-advisor-signup-info/?strpass=2&request_type=callsign&request_info=$advisorclass_call_sign&inp_depth=one&doDebug&testMode' target='_blank'>($advisorclass_call_sign)</a>";
							$advisorArray[]		= "$advisorclass_level|$advisorclass_call_sign|$user_last_name, $user_first_name|$theLink|$advisorclass_sequence|$advisorclass_timezone_offset|$advisorclass_class_size|$advisorclass_class_schedule_days|$advisorclass_class_schedule_times|$advisorclass_class_schedule_days_utc|$advisorclass_class_schedule_times_utc|$advisorclass_number_students|$advisorclass_language";
						}
					}
					if ($doDebug) {
						echo "<br />AdvisorArray:<br /><pre>";
						print_r($advisorArray);
						echo "</pre><br />";
					}
				} else {
					$content				.= "No advisor class records found";
					if ($doDebug) {
						echo "no advisorclass records found<br />";
					}
				}
			}
			if ($doDebug) {
				echo "end of getting the advisorclass information<br /><br />";
			}
				
// $doDebug = FALSE;

///////// 	Start of display all advisor slots
// $doDebug = TRUE;
			if ($doDebug) {
				echo "<br />Start of display all advisor slots report<br />";
			}
			sort($advisorArray);
			$content			.= "<a name='reportBB'><h3>All Advisors and Class Slots</h3></a>
									<table style='width:1000px;'>
									<tr><th>Name</th>
										<th>Sequence</th>
										<th>Level</th>
										<th>Language</th>
										<th>TZ</th>
										<th>Class Schedule</th>
										<th style='text-align:center;'>Class Size</th>
										<th style='text-align:center;'>Students</th>
										<th style='text-align:center;'>Slots<br />Avail</th>
									</tr>";

			$totalSlots				= 0;
			$totalAdvisors			= 0;

			foreach($advisorArray as $thisValue) {
				$myArray								= explode("|",$thisValue);
				$advisorClass_level						= $myArray[0];
				$advisor_call_sign						= $myArray[1];
				$advisor_name 							= $myArray[2];
				$theLink								= $myArray[3];
				$advisorClass_sequence					= $myArray[4];
				$advisorClass_timezone_offset			= $myArray[5];
				$advisorClass_class_size				= $myArray[6];
				$advisorClass_class_schedule_days		= $myArray[7];
				$advisorClass_class_schedule_times		= $myArray[8];
				$advisorClass_class_schedule_days_utc	= $myArray[9];
				$advisorClass_class_schedule_times_utc	= $myArray[10];
				$advisorClass_number_students			= $myArray[11];
				$advisorClass_language					= $myArray[12];
				
				if ($doDebug) {
					echo "<br />Processing $advisor_call_sign<br />
							advisorName: $advisor_name<br />
							theLink: $theLink<br />
							advisorClass_sequence: $advisorClass_sequence<br />
							advisorClass_tiezone_offset: $advisorClass_timezone_offset<br />
							advisorClass_level: $advisorClass_level<br />
							advisorClass_language: $advisorClass_language<br />
							advisorClass_class_size: $advisorClass_class_size<br />
							advisorClass_class_schedule_days: $advisorClass_class_schedule_days<br />
							advisorClass_class_schedule_times: $advisorClass_class_schedule_times <br />
							advisor$advisorClass_class_schedule_days_utc<br />
							advisorCLass_class_schedule_times_utc: $advisorClass_class_schedule_times_utc<br />
							class_number_students: $advisorClass_number_students<br />";
				}
				

				$slotsAvailable			= 0;
				if ($advisorClass_class_size > 0) {
					$slotsAvailable		= $advisorClass_class_size - $advisorClass_number_students;
					if ($slotsAvailable > 0) {
						$totalSlots			= $totalSlots + $slotsAvailable;
					}
				} else {
					$slotsAvailable			= 0;
				}
				
				$content	.= "<tr><td style='vertical-align:top;'>$advisor_name $theLink</td>
									<td style='vertical-align:top;'>$advisorClass_sequence</td>
									<td style='vertical-align:top;'>$advisorClass_level</td> 
									<td style='vertical-align:top;'>$advisorClass_language</td>
									<td style='vertical-align:top;'>$advisorClass_timezone_offset</td>
									<td style='vertical-align:top;'>$advisorClass_class_schedule_times_utc $advisorClass_class_schedule_days_utc<br />$advisorClass_class_schedule_times $advisorClass_class_schedule_days Local</td>
									<td style='text-align:center;vertical-align:top;'>$advisorClass_class_size</td>
									<td style='text-align:center;vertical-align:top;'>$advisorClass_number_students</td>
									<td style='text-align:center;vertical-align:top;'>$slotsAvailable</td></tr>";
				$totalAdvisors++;
				
			}
			$content		.= "</table>
								<p>Total Advisors: $totalAdvisors<br />
								Total slots Available: $totalSlots</p>";
// $doDebug = FALSE;

///////// 	End of display available advisor slots



/////////		Display advisors with small class sizes

// $doDebug = TRUE;
			if ($doDebug) {
				echo "<br />Start of advisors with small class sizes report<br />";
			}	
			$advisorCount			= 0;
			$totalAvailable			= 0;
			$content			.= "<a name='reportS'><h3>Advisor with Small Classes</h3></a>
									<p><table style='width:1000px;'>
									<tr><th>Name</th>
										<th>Sequence</th>
										<th>Level</th>
										<th>Language</th>
										<th>TZ</th>
										<th>Class Schedule</th>
										<th style='text-align:center;'>Class Size</th>
										<th style='text-align:center;'>Students</th>
										<th style='text-align:center;'>Slots<br />Avail</th>
									</tr>";
			$totalSlots				= 0;

			foreach($advisorArray as $thisValue) {
				$myArray								= explode("|",$thisValue);
				$advisorClass_level						= $myArray[0];
				$advisor_call_sign						= $myArray[1];
				$advisor_name 							= $myArray[2];
				$theLink								= $myArray[3];
				$advisorClass_sequence					= $myArray[4];
				$advisorClass_timezone_offset			= $myArray[5];
				$advisorClass_class_size				= $myArray[6];
				$advisorClass_class_schedule_days		= $myArray[7];
				$advisorClass_class_schedule_times		= $myArray[8];
				$advisorClass_class_schedule_days_utc	= $myArray[9];
				$advisorClass_class_schedule_times_utc	= $myArray[10];
				$advisorClass_number_students			= $myArray[11];
				$advisorClass_language					= $myArray[12];
				

				$slotsAvailable			= 0;
				if ($advisorClass_class_size > 0) {
					if ($advisorClass_number_students <= $advisorClass_class_size) {
						$slotsAvailable		= $advisorClass_class_size - $advisorClass_number_students;
						$totalSlots			= $totalSlots + $slotsAvailable;
					} else {
						$slotsAvailable		= 0;
					}
				} else {
					$slotsAvailable			= 0;
				}
						
				if ($advisorClass_number_students < 4) {
					$advisorCount++;
					$slotsAvailable		= $advisorClass_class_size - $advisorClass_number_students;
					$totalAvailable		= $totalAvailable + $slotsAvailable;
					$theLink	= "$advisor_name <a href='$studentManagementURL?strpass=81&inp_advisor_callsign=$advisor_call_sign&inp_advisorClass=$advisorClass_sequence&inp_search=standard&inp_mode=$inp_mode' target='_blank'>($advisor_call_sign)</a>";
					$content	.= "<tr><td style='vertical-align:top;'>$advisor_name $theLink</td>
										<td style='vertical-align:top;'>$advisorClass_sequence</td>
										<td style='vertical-align:top;'>$advisorClass_level</td>
										 <td style='vertical-align:top;'>$advisorClass_language</td>
										<td style='vertical-align:top;'>$advisorClass_timezone_offset</td>
										<td style='vertical-align:top;'>$advisorClass_class_schedule_times_utc $advisorClass_class_schedule_days_utc<br />$advisorClass_class_schedule_times $advisorClass_class_schedule_days Local</td>
										<td style='text-align:center;vertical-align:top;'>$advisorClass_class_size</td>
										<td style='text-align:center;vertical-align:top;'>$advisorClass_number_students</td>
										<td style='text-align:center;vertical-align:top;'>$slotsAvailable</td></tr>";
								
				}
			}
			$content		.= "</table>
								<p>$advisorCount: Small Classes<br />
								$totalAvailable: Slots available in small classes</p>";
									
									
// $doDebug = FALSE;
/////////		End of Display advisors with small class sizes


///////// 	Start of display available advisor slots
// $doDebug = TRUE;
			if ($doDebug) {
				echo "<br />Start of Advisor Class Slots Available report<br />";
			}	
			$advisorCount			= 0;
			$totalAvailable			= 0;
			$advisorSortArray		= array();
			foreach($advisorArray as $thisValue) {
				$myArray								= explode("|",$thisValue);
				$advisorClass_level						= $myArray[0];
				$advisor_call_sign						= $myArray[1];
				$advisor_name 							= $myArray[2];
				$theLink								= $myArray[3];
				$advisorClass_sequence					= $myArray[4];
				$advisorClass_timezone_offset			= $myArray[5];
				$advisorClass_class_size				= $myArray[6];
				$advisorClass_class_schedule_days		= $myArray[7];
				$advisorClass_class_schedule_times		= $myArray[8];
				$advisorClass_class_schedule_days_utc	= $myArray[9];
				$advisorClass_class_schedule_times_utc	= $myArray[10];
				$advisorClass_number_students			= $myArray[11];
				$advisorClass_language					= $myArray[12];

				$slotsAvailable		= $advisorClass_class_size - $advisorClass_number_students;
				if ($slotsAvailable > 0) {
					$advisorSortArray[] 	= "$advisorClass_level|$advisorClass_class_schedule_times_utc|$advisorClass_class_schedule_days_utc|$advisor_call_sign|$advisor_name|$theLink|$advisorClass_sequence|$advisorClass_timezone_offset|$advisorClass_class_size|$advisorClass_class_schedule_days|$advisorClass_class_schedule_times|$advisorClass_number_students|$advisorClass_language";
 		 			//                          0                  1                                      2                                      3                  4              5        6                     7                             8                        9                                 10                                  11
				}
			}
// echo "advisorSortArray<br /><pre>";
// print_r($advisorSortArray);
// echo "</pre><br />";			
			
			sort($advisorSortArray);


			$content			.= "<a name='reportY'><h3>Advisor Class Slots Available</h3></a>
									<p><table style='width:1000px;'>
									<tr><th>Name</th>
										<th>Sequence</th>
										<th>Level</th>
										<th>Language</th>
										<th>TZ</th>
										<th>Class Schedule</th>
										<th style='text-align:center;'>Class Size</th>
										<th style='text-align:center;'>Students</th>
										<th style='text-align:center;'>Slots<br />Avail</th>
									</tr>";
// echo "<br />AdvisorSortArray values:<br />";
			foreach($advisorSortArray as $thisValue) {
				$myArray								= explode("|",$thisValue);
				$advisor_call_sign						= $myArray[3];
				$advisor_name 							= $myArray[4];
				$theLink								= $myArray[5];
				$advisorClass_sequence					= $myArray[6];
				$advisorClass_timezone_offset			= $myArray[7];
				$advisorClass_level						= $myArray[0];
				$advisorClass_class_size				= $myArray[8];
				$advisorClass_class_schedule_days		= $myArray[9];
				$advisorClass_class_schedule_times		= $myArray[10];
				$advisorClass_class_schedule_days_utc	= $myArray[2];
				$advisorClass_class_schedule_times_utc	= $myArray[1];
				$advisorClass_number_students			= $myArray[11];
				$advisorClass_language					= $myArray[12];
				
// echo "advisorClassclass_schedule_days_utc: $advisorClass_class_schedule_days_utc<br />";
				$slotsAvailable			= 0;
				if ($advisorClass_class_size > 0) {
					if ($advisorClass_number_students <= $advisorClass_class_size) {
						$slotsAvailable		= $advisorClass_class_size - $advisorClass_number_students;
						$totalSlots			= $totalSlots + $slotsAvailable;
					} else {
						$slotsAvailable		= 0;
					}
				} else {
					$slotsAvailable			= 0;
				}
					
				if ($slotsAvailable > 0) {
					$advisorCount++;
					$totalAvailable		= $totalAvailable + $slotsAvailable;
					$theLink	= "$advisor_name <a href='$studentManagementURL?strpass=81&inp_advisor_callsign=$advisor_call_sign&inp_advisorClass=$advisorClass_sequence&inp_search=standard&inp_mode=$inp_mode' target='_blank'>($advisor_call_sign)</a>";
					$content	.= "<tr><td style='vertical-align:top;'>$theLink</td>
										<td style='vertical-align:top;'>$advisorClass_sequence</td>
										<td style='vertical-align:top;'>$advisorClass_level</td> 
										<td style='vertical-align:top;'>$advisorClass_language</td> 
										<td style='vertical-align:top;'>$advisorClass_timezone_offset</td>
										<td style='vertical-align:top;'>$advisorClass_class_schedule_times_utc $advisorClass_class_schedule_days_utc<br />$advisorClass_class_schedule_times $advisorClass_class_schedule_days Local</td>
										<td style='text-align:center;vertical-align:top;'>$advisorClass_class_size</td>
										<td style='text-align:center;vertical-align:top;'>$advisorClass_number_students</td>
										<td style='text-align:center;vertical-align:top;'>$slotsAvailable</td></tr>";
								
				}
			}
			$content		.= "</table>
								<p>$advisorCount: Classes with slots available<br />
								$totalAvailable: Slots available</p>";
									
									
// $doDebug = FALSE;
			
///////// 	End of display available advisor slots



			
//////// Start of Unassigned Student Information Report				


// $doDebug = TRUE;
			if ($doDebug) {
				echo "<br />Start of Unassigned Student Information Report <br />";
			}

			$content		.= "<a name='report3'><h3>Unassigned Student Information for $inp_semester</h3></a>";

			$BeginnerArray		= array();
			$FundamentalArray	= array();
			$IntermediateArray	= array();
			$AdvancedArray		= array();
			$totalUnassigned	= 0;									

			$criteria = [
				'relation' => 'AND',
				'clauses' => [
					['field' => 'student_semester', 'value' => $inp_semester, 'compare' => '='],
					['field' => 'student_response', 'value' => 'Y', 'compare' => '='],
					['field' => 'student_status', 'value' => '', 'compare' => '='],
					['field' => 'student_intervention_requred', 'value' => 'H', 'compare' => '!='],
				]
			];

					
			$orderby = 'student_level,student_call_sign';
			$order = 'ASC';
			$requestInfo = array('criteria' => $criteria,
								 'orderby' => $orderby,
								 'order' => $order);
			$studentData = get_student_and_user_master('', 'complex', $requestInfo, $operatingMode, $doDebug);
			if ($studentData == FALSE) {
				if ($doDebug) {
					echo "getting student and user master returned FALSE<br />";
				}
			} else {
				if (! empty($studentData)) {
					foreach($studentData as $key => $value) {
						foreach($value as $thisField => $thisValue) {
							$$thisField = $thisValue;
						}
						$totalUnassigned++;
						
						$theLink		= "<a href='$siteURL/cwa-student-management/?strpass=70&inp_student_callsign=$student_call_sign&inp_mode=$inp_mode' target='_blank'>$student_call_sign</a> (<a href='$studentUpdateURL?request_type=callsign&request_info=$student_call_sign&strpass=2&doDebug=$doDebug&testMode=0' target='_Blank'>UPD</a>)";
						$reqDate		= substr($student_request_date,0,10);
						
						${$student_level . 'Array'}[]		 	= "<tr><td style='vertical-align:top;'>$theLink</td>
																		<td style='vertical-align:top;'>$user_last_name, $user_first_name</td>
																		<td style='vertical-align:top;'>$student_class_language</td>
																		<td style='text-align:center;vertical-align:top;'>$student_timezone_offset<br />UTC:</td>
																		<td style='vertical-align:top;'>$user_email<br />$student_first_class_choice_utc</td>
																		<td style='vertical-align:top;'>+$user_ph_code $user_phone<br />$student_second_class_choice_utc</td>
																		<td style='vertical-align:top;'>$user_zip_code<br />$student_third_class_choice_utc</td>
																		<td style='vertical-align:top;'>$user_country</td>
																		<td style='text-align:center;vertical-align:top;'>$student_promotable</td></tr>";
					}

					if (count($BeginnerArray) > 0) {
						$myCount			= count($BeginnerArray);
						$content			.= "<h4>Beginner Unassigned</h4>
												<table style='width:1200px;'>
												<tr><th>Call Sign</th>
													<th>Student</th>
													<th>Language</th>
													<th>TZ</th>
													<th>Email</th>
													<th>Phone</th>
													<th>ZipCode</th>
													<th>Country</th>
													<th>Prom</th></tr>";
						foreach($BeginnerArray as $thisValue) {
							$content		.= $thisValue;
						}
						$content			.= "</table>
												<p>Total Unassigned Beginner Students: $myCount<br />
												<i>Clicking on the call sign link will open 'Find Possible Classes for a Student' in a new tab</i></p>";
					}
					if (count($FundamentalArray) > 0) {
						$myCount			= count($FundamentalArray);
						$content			.= "<h4>Fundamental Unassigned</h4>
												<table style='width:1200px;'>
												<tr><th>Call Sign</th>
													<th>Student</th>
													<th>Language</th>
													<th>TZ</th>
													<th>Email</th>
													<th>Phone</th>
													<th>ZipCode</th>
													<th>Country</th>
													<th>Prom</th></tr>";
						foreach($FundamentalArray as $thisValue) {
							$content		.= $thisValue;
						}
						$content			.= "</table>
												<p>Total Unassigned Fundamental Students: $myCount<br />
												<i>Clicking on the call sign link will open 'Find Possible Classes for a Student' in a new tab</i></p>";
					}
					if (count($IntermediateArray) > 0) {
						$myCount			= count($IntermediateArray);
						$content			.= "<h4>Intermediate Unassigned</h4>
												<table style='width:1200px;'>
												<tr><th>Call Sign</th>
													<th>Student</th>
													<th>Language</th>
													<th>TZ</th>
													<th>Email</th>
													<th>Phone</th>
													<th>ZipCode</th>
													<th>Country</th>
													<th>Prom</th></tr>";
						foreach($IntermediateArray as $thisValue) {
							$content		.= $thisValue;
						}
						$content			.= "</table>
												<p>Total Unassigned Intermediate Students: $myCount<br />
												<i>Clicking on the call sign link will open 'Find Possible Classes for a Student' in a new tab</i></p>";
					}
					if (count($AdvancedArray) > 0) {
						$myCount			= count($AdvancedArray);
						$content			.= "<h4>Advanced Unassigned</h4>
												<table style='width:1200px;'>
												<tr><th>Call Sign</th>
													<th>Student</th>
													<th>Language</th>
													<th>TZ</th>
													<th>Email</th>
													<th>Phone</th>
													<th>ZipCode</th>
													<th>Country</th>
													<th>Prom</th></tr>";
						foreach($AdvancedArray as $thisValue) {
							$content		.= $thisValue;
						}
						$content			.= "</table>
												<p>Total Unassigned Advanced Students: $myCount<br />
												<i>Clicking on the call sign link will open 'Find Possible Classes for a Student' in a new tab</i></p>
												<p>Total Overall Unassigned Students: $totalUnassigned";
					}
					unset($BeginnerArray);
					unset($FundamentalArray);
					unset($IntermediateArray);
					unset($advancedArray);
					
				} else {
					$content	.= "No assigned students found in $studentTableName table";
				}
			}	
// $doDebug = FAlSE;									
		


//////// End of Unassigned Student Information Report				
	
	


////////////	 students on hold report
			if ($doDebug) {
				echo "<br />Doing Students On Hold Report<br />";
			}
				
		
			$content		.= "<a name='reportH'><h3>Students On Hold Report</h3></a>
								<table style='width:1000px;'>
								<tr><th>Student</th>
									<th>Level</th>
									<th>Language</th>
									<th>Response</th>
									<th>Status</th>
									<th>Hold Reason</th>
									<th>Action Log</th></tr>";
									
			$criteria = [
				'relation' => 'AND',
				'clauses' => [
					['field' => 'student_intervention_required', 'value' => 'H', 'compare' => '=' ],
					['field' => 'student_semester', 'value' => $inp_semester, 'compare' => '=' ]
				]
			];
			$orderby = 'student_call_sign';
			$order = 'ASC';
			$requestInfo = array('criteria' => $criteria,
								 'orderby' => $orderby,
								 'order' => $order);
			$studentData = get_student_and_user_master('', 'complex', $requestInfo, $operatingMode, $doDebug);
			if ($studentData == FALSE) {
				if ($doDebug) {
					echo "getting student and user master returned FALSE<br />";
				}
			} else {
				if (! empty($studentData)) {
					foreach($studentData as $key => $value) {
						foreach($value as $thisField => $thisValue) {
							$$thisField = $thisValue;
						}

						$myStr				= getTheReason($student_hold_reason_code);
						$theLink			= "$user_last_name, $user_first_name <a href='$studentUpdateURL?request_type=callsign&request_info=$student_call_sign&strpass=2&doDebug=$doDebug&testMode=$testMode' target='_blank'>($student_call_sign)</a>";
						$holdLink			= "<a href='$studentManagementURL?strpass=26&inp_student_callsign=$student_call_sign&inp_mode=$inp_mode&inp_verbose=$inp_verbose' target='_blank'>Remove Hold</a>";
						$findLink			= "<a href='$studentManagementURL?strpass=71&inp_student_callsign=$student_call_sign&inp_choice=option1&inp_mode=$inp_mode&inp_verbose=$inp_verbose' target='_blank'>Find Classes</a>";
						$thisLog			= formatActionLog($student_action_log);
						$content			.= "<tr><td style='vertical-align:top;'>$theLink<br />
														$holdLink<br />
														$findLink</td>
													<td style='vertical-align:top;'>$student_level</td>
													<td style='vertical-align:top;'>$student_class_language</td>
													<td style='vertical-align:top;'>$student_response</td>
													<td style='vertical-align:top;'>$student_status</td>
													<td style='vertical-align:top;'>$myStr</td>
													<td style='vertical-align:top;'>$thisLog</td></tr>";
					}
					$content				.= "</table>
												<p><em>Clicking on the student call sign will open the Display and Update Student function</em></p>";
				} else {
					$content				.= "<tr><td colspan='6'>No students on hold</td></tr></table>";
				}
			}
			if ($doDebug) {
				echo "end of the students on hold report<br />";
			}
//////////////	end of students on hold report

			$content .= "<p><a href='#report1'>Go to the Advisor Assignment Information Report</a><br />\n
						<a href='#report2'>Go to the Students Assignment Information</a><br />\n
						<a href='#reportW'>Go to the Students Who Withdrew</a><br />\n
						<a href='#reportX'>Go to the Students Who Were Requested to be Replaced Report</a><br />\n
						<a href='#reportBB'>Go to All Advisors and Class Slots</a><br />\n
						<a href='#reportS'>Go to the Advisors with Small Classes Report</a><br />\n
						<a href='#reportY'>Go to the Advisor Class Slots Available Report</a><br />\n
						<a href='#report3'>Go to the Unassigned Student Information Report</a><br />\n
						<a href='#reportH'>Go to the Students on Hold Report</a><br />\n
						</p>\n";


	}
Bypass:
	$thisTime 		= date('Y-m-d H:i:s');
	$content 		.= "<br /><br /><p>V$thisVersion Prepared at $thisTime</p>";
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
	$result			= write_joblog_func("$jobname|$nowDate|$nowTime|$userName|$thisStr|Time|$strPass: $elapsedTime");
	if ($result == 'FAIL') {
		$content	.= "<p>writing to joblog.txt failed</p>";
	}
	if ($doDebug) {
		echo "<br '>Checking to see if the report is to be saved. inp_rsave: $inp_rsave<br />";
	}
	if ($inp_rsave == 'Y') {
		if ($doDebug) {
			echo "Calling function to save the report as $jobname<br />";
		}
		$storeResult	= storeReportData_func($jobname,$content);
		if ($storeResult !== FALSE) {
			$content	.= "<br />Report stored in reports pod as $storeResult";
		} else {
			$content	.= "<br />Storing the report in the reports pod failed";
		}
	}
	return $content;

}
add_shortcode ('student_and_advisor_assignments', 'student_and_advisor_assignments_func');
