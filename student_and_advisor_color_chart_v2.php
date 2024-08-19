function student_and_advisor_color_chart_v2_func() {

/*	New Student and Advisor Color Chart V2

	Arrays
		combinedArray: 
			[0000|Monday,Thursday] => Array
				[Beginner Advisors] => 1
 	           	[Beginner Seats] => 6
 	           	[Fundamental Advisors] => 0
 	           	[Fundamental Seats] => 0
 	           	[Intermediate Advisors] => 0
 	           	[Intermediate Seats] => 0
 	           	[Advanced Advisors] => 0
 	           	[Advanced Seats] => 0
 	           	[Beginner Students] => 27
 	           	[Fundamental Students] => 14
 	           	[Intermediate Students] => 19
 	           	[Advanced Students] => 12
		studentArray: time | class days | level | student array-class choice separated by commas
			example: 0000|Monday,Thursday|Beginner|A1ABC-1,A1BCD-2,A1CDE-3,...
		advisorArray: time | class days | level | advisors separated by commas
			exemple: 0000|Monday,Thursday|beginner|AA8TA-1,K7OJL-2,WR7Q-1...
			
	All of these arrays are built and maintained in the addCombinedArray function
	
	Modified 19Oct21 by Roland to select the semester
	Modified 29Sep22 by Roland to use new timezone process
	Modified 17Apr23 by Roland to fix action_log
	Modified 15Jul23 by Roland to use consolidated tables
		
*/

	global $wpdb, $combinedArray, $studentArray, $advisorArray, $includedArray, $doDebug;

	$doDebug						= FALSE;
	$testMode						= FALSE;
	$initializationArray 			= data_initialization_func();
	if ($doDebug) {
		echo "Initialization Array:<br /><pre>";
		print_r($initializationArray);
		echo "</pre><br />";
	}
	$validUser 			= $initializationArray['validUser'];
	$userName  			= $initializationArray['userName'];
	$validTestmode		= $initializationArray['validTestmode'];
	$siteURL			= $initializationArray['siteurl'];
	$versionNumber		= '2';
	$jobname			= "Student and Advisor Color Chart V$versionNumber";
	
//	CHECK THIS!								//////////////////////
	if ($validUser == "N") {
		return "YOU'RE NOT AUTHORIZED!<br />Goodby";
	}

/// get the time that the process started
	$startingMicroTime			= microtime(TRUE);

//	if ($doDebug) {
		ini_set('display_errors','1');
		error_reporting(E_ALL);	
		ini_set('max_execution_time',0);
//	}


	$strPass					= "1";
	$theURL						= "$siteURL/cwa-student-and-advisor-color-chart-v2/";
	$updateAdvisorURL			= "$siteURL/cwa-display-and-update-advisor-information/";
	$updateStudentURL			= "$siteURL/cwa-display-and-update-student-information/";
	$currentSemester			= $initializationArray['currentSemester'];
	$nextSemester				= $initializationArray['nextSemester'];
	$proximateSemeter			= $initializationArray['proximateSemester'];
	$semesterTwo				= $initializationArray['semesterTwo'];
	$semesterThree			 	= $initializationArray['semesterThree'];
	$defaultClassSize			= $initializationArray['defaultClassSize'];
	$totalAdvisors				= 0;
	$advisorCount				= 0;
	$totalClasses				= 0;
	$beginnerAdvisors			= 0;
	$fundamentalAdvisors		= 0;
	$intermediateAdvisors		= 0;
	$advancedAdvisors			= 0;
	$totalStudents				= 0;
	$studentsNotCounted			= 0;
	$beginnerStudents			= 0;
	$fundamentalStudents		= 0;
	$intermediateStudents		= 0;
	$advancedStudents			= 0;
	$combinedArray				= array();	
	$studentArray				= array();
	$advisorArray				= array();
	$errorArray					= array();
	$advisorClassOptions		= array();
	$arbitraryArray				= array();
	$firstClassChoiceFilled		= 0;
	$secondClassChoiceFilled	= 0;
	$thirdClassChoiceFilled		= 0;
	$noClassChoiceFilled		= 0;
	$studentsRead				= 0;
	$studentsNotVerified		= 0;
	$studentsVerified			= 0;
	$studentsDropped			= 0;
	$studentsRefused			= 0;
	$studentsArbAssigned		= 0;
	$inp_verbose				= "";
	$inp_mode					= "";
	$studentUpdateURL			= "$siteURL/cwa-student-and-advisor-color-chart-v2/";	
	$notFilled					= array();
	$notIncluded			 	= array();
	$includedArray				= array();
	$timeChunker				= array('0000'=>'0000',
										'0030'=>'0000',
										'0100'=>'0100',
										'0130'=>'0100',
										'0200'=>'0200',
										'0230'=>'0200',
										'0300'=>'0300',
										'0330'=>'0300',
										'0400'=>'0400',
										'0430'=>'0400',
										'0500'=>'0500',
										'0530'=>'0500',
										'0600'=>'0600',
										'0630'=>'0600',
										'0700'=>'0700',
										'0730'=>'0700',
										'0800'=>'0800',
										'0830'=>'0800',
										'0900'=>'0900',
										'0930'=>'0900',
										'1000'=>'1000',
										'1030'=>'1000',
										'1100'=>'1100',
										'1130'=>'1100',
										'1200'=>'1200',
										'1230'=>'1200',
										'1300'=>'1300',
										'1330'=>'1300',
										'1400'=>'1400',
										'1430'=>'1400',
										'1500'=>'1500',
										'1530'=>'1500',
										'1600'=>'1600',
										'1630'=>'1600',
										'1700'=>'1700',
										'1730'=>'1700',
										'1800'=>'1800',
										'1830'=>'1800',
										'1900'=>'1900',
										'1930'=>'1900',
										'2000'=>'2000',
										'2030'=>'2000',
										'2100'=>'2100',
										'2130'=>'2100',
										'2200'=>'2200',
										'2230'=>'2200',
										'2300'=>'2300',
										'2330'=>'2300');

	$b4TimeGrouper				= array('0000'=>'0000',
										'0030'=>'0000',
										'0100'=>'0000',
										'0130'=>'0000',
										'0200'=>'0000',
										'0230'=>'0000',
										'0300'=>'0300',
										'0330'=>'0300',
										'0400'=>'0300',
										'0430'=>'0300',
										'0500'=>'0300',
										'0530'=>'0300',
										'0600'=>'0600',
										'0630'=>'0600',
										'0700'=>'0600',
										'0730'=>'0600',
										'0800'=>'0600',
										'0830'=>'0600',
										'0900'=>'0900',
										'0930'=>'0900',
										'1000'=>'0900',
										'1030'=>'0900',
										'1100'=>'0900',
										'1130'=>'0900',
										'1200'=>'1200',
										'1230'=>'1200',
										'1300'=>'1200',
										'1330'=>'1200',
										'1400'=>'1200',
										'1430'=>'1200',
										'1500'=>'1500',
										'1530'=>'1500',
										'1600'=>'1500',
										'1630'=>'1500',
										'1700'=>'1500',
										'1730'=>'1500',
										'1800'=>'1800',
										'1830'=>'1800',
										'1900'=>'1800',
										'1930'=>'1800',
										'2000'=>'1800',
										'2030'=>'1800',
										'2100'=>'2100',
										'2130'=>'2100',
										'2200'=>'2100',
										'2230'=>'2100',
										'2300'=>'2100',
										'2330'=>'2100');



	$timeGrouper				= array('0000'=>'Night',
										'0030'=>'Night',
										'0100'=>'Night',
										'0130'=>'Night',
										'0200'=>'Night',
										'0230'=>'Night',
										'0300'=>'Night',
										'0330'=>'Night',
										'0400'=>'Night',
										'0430'=>'Night',
										'0500'=>'Night',
										'0530'=>'Night',
										'0600'=>'Morning',
										'0630'=>'Morning',
										'0700'=>'Morning',
										'0730'=>'Morning',
										'0800'=>'Morning',
										'0830'=>'Morning',
										'0900'=>'Morning',
										'0930'=>'Morning',
										'1000'=>'Morning',
										'1030'=>'Morning',
										'1100'=>'Morning',
										'1130'=>'Morning',
										'1200'=>'Afternoon',
										'1230'=>'Afternoon',
										'1300'=>'Afternoon',
										'1330'=>'Afternoon',
										'1400'=>'Afternoon',
										'1430'=>'Afternoon',
										'1500'=>'Afternoon',
										'1530'=>'Afternoon',
										'1600'=>'Afternoon',
										'1630'=>'Afternoon',
										'1700'=>'Afternoon',
										'1730'=>'Afternoon',
										'1800'=>'Evening',
										'1830'=>'Evening',
										'1900'=>'Evening',
										'1930'=>'Evening',
										'2000'=>'Evening',
										'2030'=>'Evening',
										'2100'=>'Evening',
										'2130'=>'Evening',
										'2200'=>'Evening',
										'2230'=>'Evening',
										'2300'=>'Evening',
										'2330'=>'Evening');



// get the input information
	if (isset($_REQUEST)) {
		foreach($_REQUEST as $str_key => $str_value) {
			if ($doDebug) {
				if (!is_array($str_value)) {
					echo "Key: $str_key | Value: $str_value <br />\n";
				} else {
					echo "Key: $str_key (array)<br />\n";
				}
			}
			if ($str_key 		== "strpass") {
				$strPass		 = $str_value;
				$strPass		 = filter_var($strPass,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "inp_verbose") {
				$inp_verbose	 = $str_value;
				$inp_verbose	 = filter_var($inp_verbose,FILTER_UNSAFE_RAW);
				if ($inp_verbose == 'Y') {
					$doDebug	= TRUE;
				}
			}
			if ($str_key 		== "inp_semester") {
				$inp_semester	 = $str_value;
				$inp_semester	 = filter_var($inp_semester,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "inp_mode") {
				$inp_mode	 = $str_value;
				$inp_mode	 = filter_var($inp_mode,FILTER_UNSAFE_RAW);
				if ($inp_mode == 'TESTMODE') {
					$testMode = TRUE;
				}
			}
			if ($str_key 		== "inp_level") {
				$inp_level	 	= $str_value;
				$inp_level	 	= filter_var($inp_level,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "inp_time") {
				$inp_time	 	= $str_value;
				$inp_time	 	= filter_var($inp_time,FILTER_UNSAFE_RAW);
			}
			if ($str_key 			== "inp_teaching_days") {
				$inp_teaching_days	 = $str_value;
				$inp_teaching_days	 = filter_var($inp_teaching_days,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "inp_list") {
				$inp_list	 	= $str_value;
				$inp_list	 	= filter_var($inp_list,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "inp_students") {
				$inp_students	 	= $str_value;
				$inp_students	 	= filter_var($inp_students,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "inp_advisors") {
				$inp_advisors	 	= $str_value;
				$inp_advisors	 	= filter_var($inp_advisors,FILTER_UNSAFE_RAW);
			}
		}
	}


	function addCombinedArray($teachingTimeChunk,$teachingDays,$level,$countType,$theCount,$callsign,$class) {

		global $combinedArray, $advisorArray, $studentArray, $includedArray, $doDebug;

/*	where 
		teachingTimeChunk is the 1-hour block (e.g., 0300)
		teachingDays is the day combination (e.g., Monday,Thursday)
		level is the class level (e.g., Beginner)
		countType is either Students or Advisors
		theCount is the number of seats or students (????)
*/
		if ($doDebug) {
			echo "In function addCombinedArray with variables:<br />
					&nbsp;&nbsp;&nbsp;&nbsp;teachingTimeChunk: $teachingTimeChunk<br />
					&nbsp;&nbsp;&nbsp;&nbsp;teachingDays: $teachingDays<br />
					&nbsp;&nbsp;&nbsp;&nbsp;level: $level<br />
					&nbsp;&nbsp;&nbsp;&nbsp;countType: $countType<br />
					&nbsp;&nbsp;&nbsp;&nbsp;theCount: $theCount<br />
					&nbsp;&nbsp;&nbsp;&nbsp;callsign: $callsign<br >";
		}
		$thisKey	= "$teachingTimeChunk|$teachingDays";
		if (!array_key_exists($thisKey,$combinedArray)) {
			$combinedArray[$thisKey]['Beginner Advisors'] 		= 0;
			$combinedArray[$thisKey]['Beginner Seats'] 			= 0;
			$combinedArray[$thisKey]['Fundamental Advisors'] 	= 0;
			$combinedArray[$thisKey]['Fundamental Seats'] 		= 0;
			$combinedArray[$thisKey]['Intermediate Advisors'] 	= 0;
			$combinedArray[$thisKey]['Intermediate Seats'] 		= 0;
			$combinedArray[$thisKey]['Advanced Advisors'] 		= 0;
			$combinedArray[$thisKey]['Advanced Seats'] 			= 0;
			$combinedArray[$thisKey]['Beginner Students'] 		= 0;
			$combinedArray[$thisKey]['Fundamental Students'] 	= 0;
			$combinedArray[$thisKey]['Intermediate Students'] 	= 0;
			$combinedArray[$thisKey]['Advanced Students'] 		= 0;
		}

		if ($countType == 'Advisors') {
			$specificKey	= "$level $countType";
			$combinedArray[$thisKey][$specificKey]++;
			$specificKey	= "$level Seats";
			$combinedArray[$thisKey][$specificKey]	= $combinedArray[$thisKey][$specificKey] + $theCount;
			$advisorKey		= "$teachingTimeChunk|$teachingDays|$level";
			if (array_key_exists($advisorKey,$advisorArray)) {
				$advisorArray[$advisorKey]			.= ",$callsign-$class";
				if ($doDebug) {
					echo "array_key_exists $advisorKey. concatenating ,$callsign<br />";
				}
			} else {
				$advisorArray[$advisorKey]			= "$callsign-$class";
				if ($doDebug) {
					echo "no array_key_exists for $advisorKey. setting to $callsign<br />";
				}
			}
		} else {
			$specificKey	= "$level $countType";
			$combinedArray[$thisKey][$specificKey]++;
			$studentKey		= "$teachingTimeChunk|$teachingDays|$level";
			if (array_key_exists($studentKey,$studentArray)) {
				$studentArray[$studentKey]			.= ",$callsign-$theCount";
			} else {
				$studentArray[$studentKey]			= "$callsign-$theCount";
			}
			$includedArray[]						= $callsign;
		}
		
		return "Success";	
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
table,th,td{border:1px solid black;}
td,th{text-align:center;}
</style>";	

	if ($testMode) {
		$content	.= "<p><strong>Operating in Test Mode.</strong></p>";
		if ($doDebug) {
			echo "<p><strong>Operating in Test Mode.</strong></p>";
		}
		$extMode					= 'Test';
		$advisorTableName			= 'wpw1_cwa_consolidated_advisor2';
		$advisorClassTableName		= 'wpw1_cwa_consolidated_advisorclass2';
		$studentTableName			= 'wpw1_cwa_consolidated_student2';
	} else {
		$extMode					= 'Production';
		$advisorTableName			= 'wpw1_cwa_consolidated_advisor';
		$advisorClassTableName		= 'wpw1_cwa_consolidated_advisorclass';
		$studentTableName			= 'wpw1_cwa_consolidated_student';
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


	if ("1" == $strPass) {
		if ($doDebug) {
			echo "Function starting.<br />";
		}
		if ($currentSemester != 'Not in Session') {
			$optionList		= "<input type='radio' class='formInputButton' name='inp_semester' value='$currentSemester' checked> $currentSemester<br />
							<input  type='radio' class='formInputButton' name='inp_semester' value='$nextSemester'> $nextSemester<br />";
		} else {
			$optionList	 	= "<input  type='radio' class='formInputButton' name='inp_semester' value='$nextSemester' checked> $nextSemester<br />";
		}
		$optionList			.= "<input  type='radio' class='formInputButton' name='inp_semester' value='$semesterTwo'> $semesterTwo<br />
								<input  type='radio' class='formInputButton' name='inp_semester' value='$semesterThree'> $semesterThree";

		$content 		.= "<h3>$jobname</h3>
							<p>Select the advisors and students to be included in the chart and click 'Submit' 
							to run the chart. The program will display the information for the current semester, 
							or if the current semester is not in session, for the upcoming semester.</p>
							<p><form method='post' action='$theURL' 
							name='selection_form' ENCTYPE='multipart/form-data'>
							<input type='hidden' name='strpass' value='2'>
							<table>
							<tr><td style='text-align:top;'>Students to be included:</td>
								<td><input type='radio' class='formInputButton' name='inp_students' value='verified' checked='checked'> Verified students only<br />
									<input type='radio' class='formInputButton' name='inp_students' value='all'> All students</td></tr>
							<tr><td style='text-align:top;'>Advisors to be included:</td>
								<td><input type='radio' class='formInputButton' name='inp_advisors' value='verified' checked='checked'> Verified advisors only<br />
									<input type='radio' class='formInputButton' name='inp_advisors' value='all'> All advisors</td></tr>
							<tr><td style='text-align:top;'>Semester:</td>
								<td>$optionList</td></tr>
							$testModeOption
							<tr><td colspan='2'><input class='formInputButton' type='submit' value='Submit' /></td></tr></table>
							</form></p>";

///// Pass 2 -- do the work


	} elseif ("2" == $strPass) {
		if ($doDebug) {
			echo "<br />At pass 2 with students: $inp_students, advisors: $inp_advisors, and semester: $inp_semester<br />";
		}
		
		$doNoCatalog		= TRUE;
		$semesterDates		= semester_dates($inp_semester,$doDebug);
		$semesterStart		= $semesterDates['semesterStart'];
		$catalogAvailable	= $semesterDates['catalogAvailable'];
		$assignDate			= $semesterDates['assignDate'];
		$daysToSemester		= $semesterDates['daysToSemester'];

		if ($doDebug) {
			$thisSemesterStart 		= date("Y-m-d", $semesterStart);
			$thisCatalogAvailable 	= date("Y-m-d", $catalogAvailable);
			$thisAssignDate		 	= date("Y-m-d", $assignDate);
			echo "called semesterDates:<br />
					thisSemester Start: $thisSemesterStart<br />
					thisCatalogAvailable: $thisCatalogAvailable<br />
					thisAssignDate: $thisAssignDate<br />
					daysToSemester: $daysToSemester<br />";
		}
		
		$todaysDate	= strtotime(date("Y-m-d"));
		
		// if today is before the catalog, prepare the report based on
		// morning, afternoon, evening time (beforecatalog = TRUE). Otherwise 
		// prepare the report based on student first class choice and advisor 
		// actual class time
		$beforeCatalog		= FAlSE;
		if ($todaysDate < $catalogAvailable) {
			$beforeCatalog	= TRUE;
		}
		
		// get the advisor classes and build the advisorArray
		$advisorSequence	= 0;
		$doProceed			= TRUE;
		if ($inp_advisors == 'all') {
			$sql				= "select * from $advisorTableName 
									where semester='$inp_semester' 
									order by fifo_date, 
											  select_sequence DESC, 
											  call_sign";
		} else {
			$sql				= "select * from $advisorTableName 
									where semester='$inp_semester' 
									and verify_response = 'Y' 
									order by fifo_date, 
											 select_sequence DESC, 
											 call_sign";
		}
		$wpw1_cwa_advisor		= $wpdb->get_results($sql);
		if ($wpw1_cwa_advisor === FALSE) {
			if ($doDebug) {
				echo "Reading wpw1_cwa_advisor table<br />";
				echo "wpdb->last_query: " . $wpdb->last_query . "<br />";
				echo "wpdb->last_error: " . $wpdb->last_error . "<br />";
				$doProceed			- FALSE;
			}
		} else {
			$numARows									= $wpdb->num_rows;
			if ($doDebug) {
				$myStr				= $wpdb->last_query;
				echo "ran $myStr<br />and got $numARows rows from $advisorTableName table<br />";
			}
			if ($numARows > 0) {
				foreach ($wpw1_cwa_advisor as $advisorRow) {
					$advisor_ID							= $advisorRow->advisor_id;
					$advisor_select_sequence 			= $advisorRow->select_sequence;
					$advisor_call_sign 					= strtoupper($advisorRow->call_sign);
					$advisor_first_name 				= $advisorRow->first_name;
					$advisor_last_name 					= stripslashes($advisorRow->last_name);
					$advisor_email 						= strtolower($advisorRow->email);
					$advisor_phone						= $advisorRow->phone;
					$advisor_ph_code					= $advisorRow->ph_code;				// new
					$advisor_text_message 				= $advisorRow->text_message;
					$advisor_city 						= $advisorRow->city;
					$advisor_state 						= $advisorRow->state;
					$advisor_zip_code 					= $advisorRow->zip_code;
					$advisor_country 					= $advisorRow->country;
					$advisor_country_code				= $advisorRow->country_code;		// new
					$advisor_whatsapp					= $advisorRow->whatsapp_app;		// new
					$advisor_signal						= $advisorRow->signal_app;			// new
					$advisor_telegram					= $advisorRow->telegram_app;		// new
					$advisor_messenger					= $advisorRow->messenger_app;		// new
					$advisor_time_zone 					= $advisorRow->time_zone;
					$advisor_timezone_id				= $advisorRow->timezone_id;			// new
					$advisor_timezone_offset			= $advisorRow->timezone_offset;		// new
					$advisor_semester 					= $advisorRow->semester;
					$advisor_survey_score 				= $advisorRow->survey_score;
					$advisor_languages 					= $advisorRow->languages;
					$advisor_fifo_date 					= $advisorRow->fifo_date;
					$advisor_welcome_email_date 		= $advisorRow->welcome_email_date;
					$advisor_verify_email_date 			= $advisorRow->verify_email_date;
					$advisor_verify_email_number 		= $advisorRow->verify_email_number;
					$advisor_verify_response 			= strtoupper($advisorRow->verify_response);
					$advisor_action_log 				= $advisorRow->action_log;
					$advisor_class_verified 			= $advisorRow->class_verified;
					$advisor_control_code 				= $advisorRow->control_code;
					$advisor_date_created 				= $advisorRow->date_created;
					$advisor_date_updated 				= $advisorRow->date_updated;


					if ($doDebug) {
						echo "<br />Processing $advisor_call_sign<br />";
					}
					// if the survey score is 6 or class verified is R bypass this advisor
					if ($advisor_survey_score == '6' || $advisor_verify_response == 'R') {
						if ($doDebug) {
							echo "&nbsp;&nbsp;&nbsp;&nbsp;bypassing: survey_score: $advisor_survey_score; advisor response: $advisor_verify_response<br />";
						}
					} else {
						$advisorCount++;
						// get the class records
						$sql					= "select * from $advisorClassTableName 
													where advisor_call_sign='$advisor_call_sign' 
													and semester='$inp_semester'";
						$wpw1_cwa_advisorclass		= $wpdb->get_results($sql);
						if ($wpw1_cwa_advisorclass === FALSE) {
							if ($doDebug) {
								echo "Reading $advisorClassTableName:<br />";
								echo "wpdb->last_query: " . $wpdb->last_query . "<br />";
								echo "wpdb->last_error: " . $wpdb->last_error . "<br />";
							}
						} else {
							$numACRows									= $wpdb->num_rows;
							if ($doDebug) {
								$myStr				= $wpdb->last_query;
								echo "ran $myStr<br />and got $numACRows rows from $advisorClassTableName<br />";
							}
							if ($numACRows > 0) {
								foreach ($wpw1_cwa_advisorclass as $advisorClassRow) {
									$advisorClass_ID				 		= $advisorClassRow->advisorclass_id;
									$advisorClass_advisor_call_sign 		= $advisorClassRow->advisor_call_sign;
									$advisorClass_advisor_first_name 		= $advisorClassRow->advisor_first_name;
									$advisorClass_advisor_last_name 		= stripslashes($advisorClassRow->advisor_last_name);
									$advisorClass_advisor_id 				= $advisorClassRow->advisor_id;
									$advisorClass_sequence 					= $advisorClassRow->sequence;
									$advisorClass_semester 					= $advisorClassRow->semester;
									$advisorClass_timezone 					= $advisorClassRow->time_zone;
									$advisorClass_timezone_id				= $advisorClassRow->timezone_id;		// new
									$advisorClass_timezone_offset			= $advisorClassRow->timezone_offset;	// new
									$advisorClass_level 					= $advisorClassRow->level;
									$advisorClass_class_size 				= $advisorClassRow->class_size;
									$advisorClass_class_schedule_days 		= $advisorClassRow->class_schedule_days;
									$advisorClass_class_schedule_times 		= $advisorClassRow->class_schedule_times;
									$advisorClass_class_schedule_days_utc 	= $advisorClassRow->class_schedule_days_utc;
									$advisorClass_class_schedule_times_utc 	= $advisorClassRow->class_schedule_times_utc;
									$advisorClass_action_log 				= $advisorClassRow->action_log;
									$advisorClass_class_incomplete 			= $advisorClassRow->class_incomplete;
									$advisorClass_date_created				= $advisorClassRow->date_created;
									$advisorClass_date_updated				= $advisorClassRow->date_updated;

									if ($advisorClass_class_schedule_days == '' || $advisorClass_class_schedule_times == '') {
										$advisorClass_class_incomplete			= 'Y'; 
										if ($doDebug) {
						  					echo "issue with $advisorClass_class_schedule_days or $advisorClass_class_schedule_times<br />";
										}
									}
									if ($advisorClass_class_incomplete == 'Y') {
										$errorArray[]		= "$advisorClass_advisor_call_sign class sequence $advisorClass_sequence class incomplete: $advisorClass_class_incomplete<br />";
									} else {
										$totalClasses++;
										if ($advisorClass_class_size == '') {
											$advisorClass_class_size	= $defaultClassSize;
										}
										if ($advisorClass_advisor_callsign = '') {
											$errorArray[]		= "advisorClass_advisor_callsign missing for $advisorClass_advisor_callsign seq $advisorClass_sequence<br />";
										}
									
										if ($doDebug) {
											echo "&nbsp;&nbsp;&nbsp;&nbsp;Class advisor; $advisorClass_advisor_callsign ($advisorClass_advisor_callsign)<br />
												  &nbsp;&nbsp;&nbsp;&nbsp;level: $advisorClass_level<br />
												  &nbsp;&nbsp;&nbsp;&nbsp;Sequence: $advisorClass_sequence<br />
												  &nbsp;&nbsp;&nbsp;&nbsp;TZ Offset: $advisorClass_timezone_offset<br />
												  &nbsp;&nbsp;&nbsp;&nbsp;Teaching days: $advisorClass_class_schedule_days<br />
												  &nbsp;&nbsp;&nbsp;&nbsp;Teaching days UTC: $advisorClass_class_schedule_days_utc<br />
												  &nbsp;&nbsp;&nbsp;&nbsp;Teaching times: $advisorClass_class_schedule_times<br />
												  &nbsp;&nbsp;&nbsp;&nbsp;Teaching times UTC: $advisorClass_class_schedule_times_utc<br />";
										}
										$goOn										= TRUE;
										if ($advisorClass_class_schedule_days_utc != '') {
											$advisorClass_class_schedule_days_utc	= str_replace(" ","",$advisorClass_class_schedule_days_utc);
											
											if ($beforeCatalog) {		// chunk the time in 3-hour increments
												if (array_key_exists($advisorClass_class_schedule_times_utc,$b4TimeGrouper)) {
													$thisTimes							= $b4TimeGrouper[$advisorClass_class_schedule_times_utc];
												} else {
													$errorArray[]						= "$advisorClass_advisor_callsign class $advisorClass_sequence has an invalid UTC time: $advisorClass_class_schedule_times_utc. Class bypassed.<br />";
													$goOn								= FALSE;
												}
											} else {					// chunk the time in 1-hour imcrements
											
												if (array_key_exists($advisorClass_class_schedule_times_utc,$timeChunker)) {
													$thisTimes							= $timeChunker[$advisorClass_class_schedule_times_utc];
												} else {
													$errorArray[]						= "$advisorClass_advisor_callsign class $advisorClass_sequence has an invalid UTC time: $advisorClass_class_schedule_times_utc. Class bypassed.<br />";
													$goOn								= FALSE;
												}
											}
										} else {
											$errorArray[]							= "$advisorClass_advisor_callsign class $advisorClass_sequence has an empty UTC time. Class bypassed.<br />";
											$goOn									= FALSE;
										}
										if ($goOn) {
											if ($doDebug) {
												echo "Using advisor schedule of $thisTimes $advisorClass_class_schedule_days_utc<br />";
											}
											if ($advisorClass_level == 'Beginner') {
												$beginnerAdvisors++;
											} elseif ($advisorClass_level == 'Fundamental') {
												$fundamentalAdvisors++;
											} elseif ($advisorClass_level == 'Intermediate') {
												$intermediateAdvisors++;
											} elseif ($advisorClass_level == 'Advanced') {
												$advancedAdvisors++;
											}
											// add class to the combinedArray
											$myResult		= addCombinedArray($thisTimes,$advisorClass_class_schedule_days_utc,$advisorClass_level,'Advisors',$advisorClass_class_size,$advisor_call_sign,$advisorClass_sequence);
											if ($doDebug) {
												echo "&nbsp;&nbsp;&nbsp;&nbsp;did AddCombinedArray with $thisTimes,$advisorClass_class_schedule_days_utc,$advisorClass_level,Advisors,$advisorClass_class_size,$advisor_call_sign,$advisorClass_sequence<br />";
											}
										}
									}
								}
							} else {
								if ($doDebug) {
									echo "&nbsp;&nbsp;&nbsp;&nbsp;No $advisorClassTableName records found for $advisor_call_sign. Bypassing advisor<br />";
								}
								$errorArray[]		= "No $advisorClassTableName records found for $advisor_call_sign. Bypassing advisor<br />";
							}							
						}
					}
				}
			} else {
				if ($doDebug) {
					echo "No $advisorTableName table records found<br />";
				}
				$errorArray[]		= "No $advisorTableName table records found<br />";
				$doProceed			= FALSE;
				$content			.= "No $advisorTableName table records found";
			}
		}	
		if ($doProceed) {
			if ($doDebug) {
				echo "<br />combinedArray for Advisors built:<br /><pre>";
				ksort($combinedArray);
				print_r($combinedArray);
				echo "</pre><br />";
			}

			// Build the report of advisor class options 
			$classOptionsArray				= array();
			foreach($combinedArray as $thisKey=>$thisValue) {
				$myArray					= explode("|",$thisKey);
	 			$thisTime					= $myArray[0];
				$thisTeachingDays			= $myArray[1];
				$myInt						= $thisValue['Beginner Advisors'];
				if ($myInt > 0) {
					$classOptionsArray[]	= "Beginner|$thisTime|$thisTeachingDays|$myInt";
				}
				$myInt						= $thisValue['Fundamental Advisors'];
				if ($myInt > 0) {
					$classOptionsArray[]	= "Fundamental|$thisTime|$thisTeachingDays|$myInt";
				}
				$myInt						= $thisValue['Intermediate Advisors'];
				if ($myInt > 0) {
					$classOptionsArray[]	= "Intermediate|$thisTime|$thisTeachingDays|$myInt";
				}
				$myInt						= $thisValue['Advanced Advisors'];
				if ($myInt > 0) {
					$classOptionsArray[]	= "Advanced|$thisTime|$thisTeachingDays|$myInt";
				}
			}
			sort ($classOptionsArray);
			if ($doDebug) {
				$myInt				= count($classOptionsArray);
				echo "<br /><b>Built the classOptionsArray with a total of $myInt records</b><br />";
			}

			// get the students and fill the student portion of the combinedArray	
	
			$sql						= "select * from $studentTableName 
											where semester='$inp_semester' 
											order by call_sign";
			$wpw1_cwa_student				= $wpdb->get_results($sql);
			if ($wpw1_cwa_student === FALSE) {
				if ($doDebug) {
					echo "Reading wpw1_cwa_student table failed<br />";
					echo "wpdb->last_query: " . $wpdb->last_query . "<br />";
					echo "wpdb->last_error: " . $wpdb->last_error . "<br />";
				}
			} else {
				$numSRows									= $wpdb->num_rows;
				if ($doDebug) {
					$myStr				= $wpdb->last_query;
					echo "ran $myStr<br />and retrieved $numSRows rows<br />";
				}
				if ($numSRows > 0) {
					foreach ($wpw1_cwa_student as $studentRow) {
						$student_ID								= $studentRow->student_id;
						$student_call_sign						= strtoupper($studentRow->call_sign);
						$student_first_name						= $studentRow->first_name;
						$student_last_name						= stripslashes($studentRow->last_name);
						$student_email  						= strtolower(strtolower($studentRow->email));
						$student_ph_code						= $studentRow->ph_code;
						$student_phone  						= $studentRow->phone;
						$student_city  							= $studentRow->city;
						$student_state  						= $studentRow->state;
						$student_zip_code  						= $studentRow->zip_code;
						$student_country_code					= $studentRow->country_code;
						$student_country  						= $studentRow->country;
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
						$student_catalog_options				= $studentRow->catalog_options;
						$student_flexible						= $studentRow->flexible;
						$student_date_created 					= $studentRow->date_created;
						$student_date_updated			  		= $studentRow->date_updated;


						if ($doDebug) {
							echo "<br />Procesing $student_call_sign<br />
									student_email_number: $student_email_number<br />
									student_response: $student_response<br />
									student_abandoned: $student_abandoned<br />
									student_catalog_options: $student_catalog_options<br />
									student_flexible: $student_flexible<br />
									student_first_class_choice_utc: $student_first_class_choice_utc<br />
									student_second_class_choice_utc: $student_second_class_choice_utc<br />
									student_third_class_choice_utc: $student_third_class_choice_utc<br />";
						}
						$studentsRead++;
						if ($student_email_number == '4' && $student_response == '') {
							$studentsDropped++;
//							$notIncluded[]						= "<a href='$studentUpdateURL?request_type=callsign&request_info=$student_call_sign&request_table=$studentTableName&strpass=2' target='_blank'>$student_call_sign</a> was dropped<br />";
						}
						if ($student_response == '') {
							$studentsNotVerified++;
						} elseif ($student_response == 'Y') {
							$studentsVerified++;
						}
						if ($student_response == 'R') {
							$studentsRefused++;
//							$notIncluded[]						= "$student_call_sign refused<br />";
						}
						if ($student_abandoned == 'Y') {
							$studentsDropped++;
//							$notIncluded[]						= "<a href='$studentUpdateURL?request_type=callsign&request_info=$student_call_sign&request_table=$studentTableName&strpass=2' target='_blank'>$student_call_sign</a> abandoned the registration<br />";
							$studentsNotCounted++;
						}
						
						
						if ($doDebug) {
							echo "<br />Processing student $student_call_sign; TZ Offset: $student_timezone_offset; Level: $student_level<br />";
						}
						$processStudent							= FALSE;
						if ($inp_students == 'all') {
							if ($student_response == 'R') {
								$processStudent					= FALSE;
							} else {
								$processStudent					= TRUE;
							}
						} else {
							if ($student_response == 'Y') {
								$processStudent					= TRUE;
							} else {
								$processStudent					= FALSE;
							}
						}
						if ($student_email_number == '4' && $student_response == '') {
							$processStudent						= FALSE;
						}
						if ($student_abandoned == 'Y') {
							$processStudent						= FALSE;
						}
						
						if ($processStudent) {
							if ($beforeCatalog) {			// use catalog_options to chunk into 3-hour bocks
								if ($student_catalog_options == '' || $student_catalog_options == 'None') {
									if ($doDebug) {
										echo "student_catalog_options empty or none. Checking flexible<br />";
									}
									if ($student_flexible == 'Y') {
										$student_catalog_options	= 'MTE';	// if flexible, assume 1900 MT
									} else {
										$errorArray[]			= "Student $student_call_sign has no catatlog_options and is not flexible<br />";
									}
 								} else {
 									// convert the catalog options and add to combined array
									$inp_convert_data 	= array('inp_type'=>'student',
																'inp_offset'=>$student_timezone_offset,
																'inp_catalog_options'=>$student_catalog_options,
																'doDebug'=>$doDebug);
									$convertResult		= convert_options($inp_convert_data);
									if ($convertResult === FALSE) {
										if ($doDebug) {
											echo "convert_options failed. inp_convert_data:<br /><pre>";
											print_r($inp_convert_data);
											echo "</pre><br />";
										}
										$errorArray[]	= "Student $student_call_sign catalog_options of $student_catalog_options does not compute<br />";
									} else {
										$thisTimes			= '';
										$thisDays			= '';
										if ($convertResult['option1'] != '') {
											$thisSchedule		= $convertResult['option1'];
											if ($doDebug) {
												echo "option 1 has $thisSchedule<br />";
											}
											$myArray1 		= explode("&",$thisSchedule);
											if (count($myArray1) > 1) {
												$thisTimes	= $myArray1[0];
												$thisDays	= $myArray1[1];
											}
										} elseif ($convertResult['option2'] != '') {
											$thisSchedule		= $convertResult['option2'];
											if ($doDebug) {
												echo "option 2 has $thisSchedule<br />";
											}
											$myArray1 		= explode("&",$thisSchedule);
											if (count($myArray1) > 1) {
												$thisTimes	= $myArray1[0];
												$thisDays	= $myArray1[1];
											}
										} elseif ($convertResult['option3'] != '') {
											$thisSchedule		= $convertResult['option3'];
											if ($doDebug) {
												echo "option 3 has $thisSchedule<br />";
											}
											$myArray1 		= explode("&",$thisSchedule);
											if (count($myArray1) > 1) {
												$thisTimes	= $myArray1[0];
												$thisDays	= $myArray1[1];
											}
										}
										if ($thisTimes == '') {			// no option returned
											$errorArray[]		= "Student 4student_call_sign catalog_options of $catalog_options did not return a result from convert_options<br />";
										} else {
											$totalStudents++;
											if ($student_level == 'Beginner') {
												$beginnerStudents++;
											} elseif ($student_level == 'Fundamental') {
												$fundamentalStudents++;
											} elseif ($student_level == 'Intermediate') {
												$intermediateStudents++;
											} elseif ($student_level == 'Advanced') {
												$advancedStudents++;
											}
											// group the time
											if (array_key_exists($thisTimes,$b4TimeGrouper)) {
												$displayTimes	= $b4TimeGrouper[$thisTimes];
												// add info to the student portion of the combinedArray
												$myResult		= addCombinedArray($displayTimes,$thisDays,$student_level,'Students',1,$student_call_sign,0);
												if ($doDebug) {
													echo "&nbsp;&nbsp;&nbsp;&nbsp;did AddCombinedArray with $displayTimes,$thisDays,$student_level,Students,1,$student_call_sign<br />";
												}
											} else {
												if ($doDebug) {
													echo "time of $thisTimes is not in b4TimeGrouper<br />";
												}
												$errorArray[]	= "Student $student_call_sign cnverted time of $thisTimes can not be grouped<br />";
											}
										}
									}

 								}
							} else {
								if ($student_first_class_choice == '' || $student_first_class_choice == 'None') {
									$student_first_class_choice		= 'None';
									$student_first_class_choice_utc	= 'None';
								}
								if ($student_second_class_choice == '' || $student_second_class_choice == 'None') {
									$student_second_class_choice		= 'None';
									$student_second_class_choice_utc	= 'None';
								}
								if ($student_third_class_choice == '' || $student_third_class_choice == 'None') {
									$student_third_class_choice		= 'None';
									$student_third_class_choice_utc	= 'None';
								}
					
								if ($student_first_class_choice == 'None' && ($student_second_class_choice == 'None') && ($student_third_class_choice == 'None')) {
									if ($doDebug) {
										echo "&nbsp;&nbsp;&nbsp;&nbsp;No class choices specified. Attempting Arb Placement<br />";
									}
							
									//// arbitrarily choosing 1900 Monday,Thursday local time (or Evening Monday,Thursday)
									$thisResult			= utcConvert('toutc',$student_timezone_offset,'1900','Monday,Thursday');
									if ($thisResult[0] == 'OK') {
										$thisTime		= $thisResult[1];
										$thisDays		= $thisResult[2];
										// fix up student first class choice UTC time to be a whole hour
										$myStr					= substr($thisTime,0,2);
										$thisTime				= $myStr . '00';
										$student_first_class_choice_utc	= "$thisTime $thisDays";
										if ($doDebug) {
											echo "No class choices. Setting to $thisTime $thisDays UTC<br />";
										}
										$myResult		= addCombinedArray($thisTime,$thisDays,$student_level,'Students',1,$student_call_sign,0);
										if ($doDebug) {
											echo "&nbsp;&nbsp;&nbsp;&nbsp;did AddCombinedArray with $thisTime,$thisDays,$student_level,'Students',1,$student_call_sign,0<br />";
										}
										$arbitraryArray[]				= "Student $student_call_sign specified no class choices. Set to $student_level $thisTime $thisDays UTC<br />";
										$studentsArbAssigned++;
									} else {
										$errorArray[]					= "Student $student_call_sign specified no class choices. Arb assignment failed<br />";
										$notIncluded[]					= "$student_call_sign specified no class choices. Arbitrary assignment failed<br />";
										$studentsNotCounted++;
									}
								} 
								if ($student_first_class_choice == 'None') {
	//								$errorArray[]	= "Student $student_call_sign missing first_class_choice UTC<br />";
	//								if ($doDebug) {
	//									echo "&nbsp;&nbsp;&nbsp;&nbsp;No student first class choice<br />";
	//								}
								} else {
									$totalStudents++;
									if ($student_level == 'Beginner') {
										$beginnerStudents++;
									} elseif ($student_level == 'Fundamental') {
										$fundamentalStudents++;
									} elseif ($student_level == 'Intermediate') {
										$intermediateStudents++;
									} elseif ($student_level == 'Advanced') {
										$advancedStudents++;
									}
								
									if ($doDebug) {
										echo "&nbsp;&nbsp;&nbsp;&nbsp;Processing 1st Class Choice: $student_first_class_choice ($student_first_class_choice)<br />";
									}
									$gotClass					= FALSE;
									$myArray					= explode(" ",$student_first_class_choice_utc);
									if (count($myArray) == 2) {
										$displayDays			= $myArray[1];
										$displayTimes			= $myArray[0];
										$thisFirstDays			= $displayDays;
										$myStr					= substr($displayTimes,0,2);
										$thisFirstTimes			= $myStr . '00';
										$thisFirstLevel			= $student_level;
										// fix up student first class choice UTC time to be a whole hour
										$myStr					= substr($displayTimes,0,2);
										$displayTimes			= $myStr . '00';
										// see if there is a matching combinedArray
										$myStr					= "$displayTimes|$displayDays";
										if ($doDebug) {
											echo "&nbsp;&nbsp;&nbsp;&nbsp;Looking for $myStr in combinedArray<br />";
										}
										if (array_key_exists($myStr,$combinedArray)) {
											// array exists. Is there an advisor?
											$numberAdvisors		= $combinedArray[$myStr]["$student_level Advisors"];
											if ($doDebug) {
												echo "&nbsp;&nbsp;&nbsp;&nbsp;Found $numberAdvisors advisors in combinedArray [$myStr][$student_level Advisors]<br />";
											}
											if ($numberAdvisors > 0) {						
												// add info to the student portion of the combinedArray
												$myResult		= addCombinedArray($displayTimes,$displayDays,$student_level,'Students',1,$student_call_sign,0);
												if ($doDebug) {
													echo "&nbsp;&nbsp;&nbsp;&nbsp;did AddCombinedArray with $displayTimes,$displayDays,$student_level,Students,1,$student_call_sign<br />";
												}
												$gotClass				= TRUE;
												$firstClassChoiceFilled++;
												if ($doDebug) {
													echo "first class choice fulfilled<br />";
												}
											} else {
												if ($doDebug) {
													echo " &nbsp;&nbsp;&nbsp;&nbsp;first class choice not fulfilled<br />";
												}
											}
										} else {
											if ($doDebug) {
												echo "&nbsp;&nbsp;&nbsp;&nbsp;No combinedArray for $myStr<br />";
											}
										}
									} else {
										$errorArray[] 		= "$student_call_sign first class choice UTC of $student_first_class_choice_utc is invalid<br />";
									}
									if (!$gotClass) {
										if ($doDebug) {
											echo "&nbsp;&nbsp;&nbsp;&nbsp;Processing 2nd Class Choice: $student_second_class_choice_utc <br />";
										}
										if ($student_second_class_choice != 'None') {
											$myArray			= explode(" ",$student_second_class_choice_utc);
											if (count($myArray) == 2) {
												$displayDays			= $myArray[1];
												$displayTimes			= $myArray[0];
												// fix up student second class choice UTC time to be a whole hour
												$myStr					= substr($displayTimes,0,2);
												$displayTimes			= $myStr . '00';
												// see if there is a matching combinedArray
												$myStr					= "$displayTimes|$displayDays";
												if ($doDebug) {
													echo "&nbsp;&nbsp;&nbsp;&nbsp;Looking for $myStr in combinedArray<br />";
												}
												if (array_key_exists($myStr,$combinedArray)) {
													// array exists. Is there an advisor?
													$numberAdvisors		= $combinedArray[$myStr]["$student_level Advisors"];
													if ($doDebug) {
														echo "&nbsp;&nbsp;&nbsp;&nbsp;Found $numberAdvisors in combinedArray [$myStr][$student_level Advisors]<br />";
													}
													if ($numberAdvisors > 0) {						
														// add info to the student portion of the combinedArray
														$myResult		= addCombinedArray($displayTimes,$displayDays,$student_level,'Students',1,$student_call_sign,0);
														if ($doDebug) {
															echo "&nbsp;&nbsp;&nbsp;&nbsp;did AddCombinedArray with $displayTimes,$displayDays,$student_level,Students,1,$student_call_sign<br />";
														}
														$gotClass				= TRUE;
														$secondClassChoiceFilled++;
														if ($doDebug) {
															echo "second class choice fulfilled<br />";
														}
													} else {
														if ($doDebug) {
															echo " &nbsp;&nbsp;&nbsp;&nbsp;second class choice not fulfilled<br />";
														}
													}
												} else {
													if ($doDebug) {
														echo "&nbsp;&nbsp;&nbsp;&nbsp;No combinedArray for $myStr<br />";
													}
												}
											} else {
												$errorArray[]		= "$student_call_sign second class choice UTC of $student_second_class_choice_utc is invalid<br />";
											}
										}
									}
									if (!$gotClass) {
										if ($doDebug) {
											echo "&nbsp;&nbsp;&nbsp;&nbsp;Processing 3rd Class Choice: $student_third_class_choice_utc<br />";
										}
										if ($student_third_class_choice != 'None') {
											$myArray			= explode(" ",$student_third_class_choice_utc);
											if (count($myArray) == 2) {
												$displayDays			= $myArray[1];
												$displayTimes			= $myArray[0];
												// fix up student third class choice UTC time to be a whole hour
												$myStr					= substr($displayTimes,0,2);
												$displayTimes			= $myStr . '00';
												// see if there is a matching combinedArray
												$myStr					= "$displayTimes|$displayDays";
												if ($doDebug) {
													echo "&nbsp;&nbsp;&nbsp;&nbsp;Looking for $myStr in combinedArray<br />";
												}
												if (array_key_exists($myStr,$combinedArray)) {
													// array exists. Is there an advisor?
													$numberAdvisors		= $combinedArray[$myStr]["$student_level Advisors"];
													if ($doDebug) {
														echo "&nbsp;&nbsp;&nbsp;&nbsp;Found $numberAdvisors in combinedArray [$myStr][$student_level Advisors]<br />";
													}
													if ($numberAdvisors > 0) {						
														// add info to the student portion of the combinedArray
														$myResult		= addCombinedArray($displayTimes,$displayDays,$student_level,'Students',1,$student_call_sign,0);
														if ($doDebug) {
															echo "&nbsp;&nbsp;&nbsp;&nbsp;did AddCombinedArray with $displayTimes,$displayDays,$student_level,Students,1,$student_call_sign<br />";
														}
														$gotClass				= TRUE;
														$thirdClassChoiceFilled++;
														if ($doDebug) {
															echo "third class choice fulfilled<br />";
														}
													} else {
														if ($doDebug) {
															echo " &nbsp;&nbsp;&nbsp;&nbsp;third class choice not fulfilled<br />";
														}
													}
												} else {
													if ($doDebug) {
														echo "&nbsp;&nbsp;&nbsp;&nbsp;No combinedArray for $myStr<br />";
													}
												}
											} else {
												$errorArray[]		= "$student_call_sign third class choice UTC of $third_class_choice_utc is invalid<br />";
											}
										}
									}
									if (!$gotClass) {
										if ($doDebug) {
											echo "&nbsp;&nbsp;&nbsp;&nbsp;student $student_call_sign had no matching classes<br />";
										}
										$myResult		= addCombinedArray($thisFirstTimes,$thisFirstDays,$student_level,'Students',1,$student_call_sign,0);
										if ($doDebug) {
											echo "&nbsp;&nbsp;&nbsp;&nbsp;did AddCombinedArray with $thisFirstTimes,$thisFirstDays,$student_level,Students,1,$student_call_sign<br />";
										}
								
										$noClassChoiceFilled++;
//										$notFilled[]	= "$student_call_sign $student_level had no matching class choices. Added first choice to matrix<br />";
									}
								}
							}
						}							// end of process student loop
//						if (!in_array($student_call_sign,$includedArray)) {
//							$notIncluded[]			= "$student_call_sign not included for unknown reason<br />";
//						}
					}
				} else {
					if ($doDebug) {
						echo "No students found in $studentTableName<br />";
					}
					$errorArray[]		= "No students found in $studentTableName<br />";
					$doProceed			= FALSE;
				}
			}
		}
		if ($doProceed) {
			ksort($combinedArray);
			if ($doDebug) {
				echo "<br /><b>Combined Array:</b><br /><pre>";
				print_r($combinedArray);
				echo "</pre><br />";
			}
			ksort ($advisorArray);
			if ($doDebug) {
				echo "<br /><b>Advisor Array:</b><br />";
				foreach ($advisorArray as $myKey=>$myValue) {
					echo "&nbsp;&nbsp;&nbsp;&nbsp;$myKey =$myValue<br />";
				}
			}
			ksort ($studentArray);
			if ($doDebug) {
				echo "<br /><b>Student Array:</b><br />";
				foreach ($studentArray as $myKey=>$myValue) {
					echo "&nbsp;&nbsp;&nbsp;&nbsp;$myKey = $myValue<br />";
				}
			}


 
// display the chart

			if ($doDebug) {
				echo "<br /><b>Preparing and Displaying the Chart</b><br />";
			}
			if ($beforeCatalog) {
				$myStr			= "<p>All times are in UTC and are in three-hour chunks. For example, the UTC time 
									of 0300 includes the time between 0300 and 0559.</p>";
			} else {
				$myStr			= "<p>All times are in UTC and are in one-hour chunks. For example, the UTC time 
									of 0300 includes the time between 0300 and 0359.</p>";
			}
			$content			.= "<h3>NEW Student and Advisor Color Chart</h3>
									<p>How to read this chart:</p>
									$myStr
									<table style='width:250px;'><tr><td style='text-align:center;'>Nmbr Advisors|Total Seats<br />Nmbr students|Open Seats</td></tr></table>
									<p>The top row in each of the levels has the number of advisors | total seats for all advisors.<br />
									The bottom row in each of the levels has the number of students | number of open seats which is 
									calculated by the number of students minus the total seats for all advisors. A positive number 
									indicates that there are more seats available than students. A negative number indicates that 
									there are more students than available seats.</p>
									<p>The colors indicate:
									<table>
									<tr><td style='background-color:lightPink;'>No Advisors</td>
										<td style='background-color:Yellow;'>Too Few Advisors</td>
										<td style='background-color:PaleGreen'>Just About Right</td>
										<td  style='background-color:#00ffff;'>Too Many Advisors</td></tr></table>
									<table>
									<tr><th>UTC Time</th>
										<th>Teaching Days</th>
										<th>Beginner</th>
										<th>Fundamental</th>
										<th>Intermediate</th>
										<th>Advanced</th>
										<th>Total</th></tr>";
				
			$prevTime					= '';
			$prevTeachingDays			= '';
			$prevlevel					= '';
			$advisorCountBeg			= 0;
			$classSizeCountBeg			= 0;
			$advisorCountFun			= 0;
			$classSizeCountFun			= 0;
			$advisorCountInt			= 0;
			$classSizeCountInt			= 0;
			$advisorCountAdv			= 0;
			$classSizeCountAdv			= 0;
			$advisorCountTot			= 0;
			$classSizeCountTot			= 0;
			$studentCountFun			= 0;
			$studentCountBeg			= 0;
			$studentCountInt			= 0;
			$studentCountAdv			= 0;
			$studentCountTot			= 0;
			$studentExcessTot			= 0;
			$firstTime					= TRUE;
			$totalAdvisorsBeg			= 0;
			$totalSeatsBeg				= 0;
			$totalStudentsBeg			= 0;
			$totalExcessBeg				= 0;
			$totalAdvisorsFun			= 0;
			$totalSeatsFun				= 0;
			$totalStudentsFun			= 0;
			$totalExcessFun				= 0;
			$totalAdvisorsInt			= 0;
			$totalSeatsInt				= 0;
			$totalStudentsInt			= 0;
			$totalExcessInt				= 0;
			$totalAdvisorsAdv			= 0;
			$totalSeatsAdv				= 0;
			$totalStudentsAdv			= 0;
			$totalExcessAdv				= 0;

			if ($doDebug) {
				echo "<br /><b>Doing the Chart</b><br />";
			}

			foreach($combinedArray as $thisKey=>$thisValue) {
				if ($doDebug) {
					echo "Processing $thisKey<br /><pre>";
					print_r($thisValue);
					echo "</pre><br />";
				}
				$myArray				= explode("|",$thisKey);
				$thisTime				= $myArray[0];
				$thisTeachingDays		= $myArray[1];
				if ($doDebug) {
					echo "<br />Processing combinedArray $thisTime | $thisTeachingDays<br />";
				}
				if ($doDebug) {
					echo "Writing a row for $thisTime and $thisTeachingDays<br />";
				}
				$advisorCountBeg				= $thisValue['Beginner Advisors'];
				$classSizeCountBeg				= $thisValue['Beginner Seats'];
				$advisorCountFun				= $thisValue['Fundamental Advisors'];
				$classSizeCountFun				= $thisValue['Fundamental Seats'];
				$advisorCountInt				= $thisValue['Intermediate Advisors'];
				$classSizeCountInt				= $thisValue['Intermediate Seats'];
				$advisorCountAdv				= $thisValue['Advanced Advisors'];
				$classSizeCountAdv				= $thisValue['Advanced Seats'];
				$studentCountBeg				= $thisValue['Beginner Students'];
				$studentCountFun				= $thisValue['Fundamental Students'];
				$studentCountInt				= $thisValue['Intermediate Students'];
				$studentCountAdv				= $thisValue['Advanced Students'];
				$advisorCountTot				= 0;
				$classSizeCountTot				= 0;
				$studentCountTot				= 0;
				$studentExcessTot				= 0;
				$colorBeg						= "#fff";
				$colorFun						= "#fff";
				$colorInt						= "#fff";
				$colorAdv						= "#fff";
				
				if ($advisorCountBeg == 0) {
					$begColumnTop		= '&nbsp;';
				} else {
					$begListKey			= "$thisTime|$thisTeachingDays|Beginner";
					$begListAdv			= $advisorArray[$begListKey];
					$begColumnTop		= "<a href='$theURL?inp_time=$thisTime&inp_level=Beginner&inp_teaching_days=$thisTeachingDays&strpass=3&inp_semester=$inp_semester&inp_verbose=$inp_verbose&inp_mode=$inp_mode&inp_list=$begListAdv' target='_blank'>$advisorCountBeg</a> | $classSizeCountBeg";
					$totalAdvisorsBeg	= $totalAdvisorsBeg + $advisorCountBeg;
					$totalSeatsBeg		= $totalSeatsBeg + $classSizeCountBeg;
					$advisorCountTot	= $advisorCountTot + $advisorCountBeg;
					$classSizeCountTot	= $classSizeCountTot + $classSizeCountBeg;
				}
				if ($advisorCountFun == 0) {
					$funColumnTop		= '&nbsp;';
				} else {
					$funListKey			= "$thisTime|$thisTeachingDays|Fundamental";
					$funListAdv			= $advisorArray[$funListKey];
					$funColumnTop		= "<a href='$theURL?inp_time=$thisTime&inp_level=Fundamental&inp_teaching_days=$thisTeachingDays&strpass=3&inp_semester=$inp_semester&inp_verbose=$inp_verbose&inp_mode=$inp_mode&inp_list=$funListAdv' target='_blank'>$advisorCountFun</a> | $classSizeCountFun";
					$totalAdvisorsFun	= $totalAdvisorsFun + $advisorCountFun;
					$totalSeatsFun		= $totalSeatsFun + $classSizeCountFun;
					$advisorCountTot	= $advisorCountTot + $advisorCountFun;
					$classSizeCountTot	= $classSizeCountTot + $classSizeCountFun;
				}
				if ($advisorCountInt == 0) {
					$intColumnTop		= '&nbsp;';
				} else {
					$intListKey			= "$thisTime|$thisTeachingDays|Intermediate";
					$intListAdv			= $advisorArray[$intListKey];
					$intColumnTop		= "<a href='$theURL?inp_time=$thisTime&inp_level=Intermediate&inp_teaching_days=$thisTeachingDays&strpass=3&inp_semester=$inp_semester&inp_verbose=$inp_verbose&inp_mode=$inp_mode&inp_list=$intListAdv' target='_blank'>$advisorCountInt</a> | $classSizeCountInt";
					$totalAdvisorsInt	= $totalAdvisorsInt + $advisorCountInt;
					$totalSeatsInt		= $totalSeatsInt + $classSizeCountInt;
					$advisorCountTot	= $advisorCountTot + $advisorCountInt;
					$classSizeCountTot	= $classSizeCountTot + $classSizeCountInt;
				}
				if ($advisorCountAdv == 0) {
					$advColumnTop		= '&nbsp;';
				} else {
					$advListKey			= "$thisTime|$thisTeachingDays|Advanced";
					$advListAdv			= $advisorArray[$advListKey];
					$advColumnTop		= "<a href='$theURL?inp_time=$thisTime&inp_level=Advanced&inp_teaching_days=$thisTeachingDays&strpass=3&inp_semester=$inp_semester&inp_verbose=$inp_verbose&inp_mode=$inp_mode&inp_list=$advListAdv' target='_blank'>$advisorCountAdv</a> | $classSizeCountAdv";
					$totalAdvisorsAdv	= $totalAdvisorsAdv + $advisorCountAdv;
					$totalSeatsAdv		= $totalSeatsAdv + $classSizeCountAdv;
					$advisorCountTot	= $advisorCountTot + $advisorCountAdv;
					$classSizeCountTot	= $classSizeCountTot + $classSizeCountAdv;
				}
				if ($studentCountBeg == 0 && $advisorCountBeg == 0) {
					$begColumnBottom	= '&nbsp;';
				} else {
					$excessBeg			= $classSizeCountBeg - $studentCountBeg;
					if ($studentCountBeg != 0) {
						$begListKey			= "$thisTime|$thisTeachingDays|Beginner";
						$begListStu			= $studentArray[$begListKey];
						$begColumnBottom	= "<a href='$theURL?inp_time=$thisTime&inp_level=Beginner&inp_teaching_days=$thisTeachingDays&strpass=4&inp_semester=$inp_semester&inp_verbose=$inp_verbose&inp_mode=$inp_mode&inp_list=$begListStu' target='_blank'>$studentCountBeg</a> | $excessBeg";
					} else {
						$begColumnBottom	= "$studentCountBeg | $excessBeg";
					}
					$studentCountTot	= $studentCountTot + $studentCountBeg;
					$studentExcessTot	= $studentExcessTot + $excessBeg;
					$totalStudentsBeg	= $totalStudentsBeg + $studentCountBeg;
					$totalExcessBeg		= $totalExcessBeg + $excessBeg;
					if ($advisorCountBeg == 0 && $studentCountBeg > 0) {
						$colorBeg		= "lightPink";
					} else {
						if ($excessBeg > 3) {
							$colorBeg		= "#00ffff";
						} elseif ($excessBeg < -3) {
							$colorBeg		= "Yellow";
						} else {
							$colorBeg		= "PaleGreen";
						}
					}
				}
				if ($studentCountFun == 0 && $advisorCountFun == 0) {
					$funColumnBottom	= '&nbsp;';
				} else {
					$excessFun			= $classSizeCountFun - $studentCountFun;
					if ($studentCountFun != 0) {
						$funListKey			= "$thisTime|$thisTeachingDays|Fundamental";
						$funListStu			= $studentArray[$funListKey];
						$funColumnBottom	= "<a href='$theURL?inp_time=$thisTime&inp_level=Fundamental&inp_teaching_days=$thisTeachingDays&strpass=4&inp_semester=$inp_semester&inp_verbose=$inp_verbose&inp_mode=$inp_mode&inp_list=$funListStu' target='_blank'>$studentCountFun</a> | $excessFun";
					} else {
						$funColumnBottom	= "$studentCountFun | $excessFun";
					}
					$studentCountTot	= $studentCountTot + $studentCountFun;
					$studentExcessTot	= $studentExcessTot + $excessFun;
					$totalStudentsFun	= $totalStudentsFun + $studentCountFun;
					$totalExcessFun		= $totalExcessFun + $excessFun;
					if ($advisorCountFun == 0 && $studentCountFun > 0) {
						$colorFun		= "lightPink";
					} else {
						if ($excessFun > 3) {
							$colorFun		= "#00ffff";
						} elseif ($excessFun < -3) {
							$colorFun		= "Yellow";
						} else {
							$colorFun		= "PaleGreen";
						}
					}
				}
				if ($studentCountInt == 0 && $advisorCountInt == 0) {
					$intColumnBottom	= '&nbsp;';
				} else {
					$excessInt			= $classSizeCountInt - $studentCountInt;
					if ($studentCountInt != 0) {
						$intListKey			= "$thisTime|$thisTeachingDays|Intermediate";
						$intListStu			= $studentArray[$intListKey];
						$intColumnBottom	= "<a href='$theURL?inp_time=$thisTime&inp_level=Intermediate&inp_teaching_days=$thisTeachingDays&strpass=4&inp_semester=$inp_semester&inp_verbose=$inp_verbose&inp_mode=$inp_mode&inp_list=$intListStu' target='_blank'>$studentCountInt</a> | $excessInt";
					} else {
						$intColumnBottom	= "$studentCountInt | $excessInt";
					}
					$studentCountTot	= $studentCountTot + $studentCountInt;
					$studentExcessTot	= $studentExcessTot + $excessInt;
					$totalStudentsInt	= $totalStudentsInt + $studentCountInt;
					$totalExcessInt		= $totalExcessInt + $excessInt;
					if ($advisorCountInt == 0 && $studentCountInt > 0) {
						$colorInt		= "lightPink";
					} else {
						if ($excessInt > 3) {
							$colorInt		= "#00ffff";
						} elseif ($excessInt < -3) {
							$colorInt		= "Yellow";
						} else {
							$colorInt		= "PaleGreen";
						}
					}
				}
				if ($studentCountAdv == 0 && $advisorCountAdv == 0) {
					$advColumnBottom	= '&nbsp;';
				} else {
					$excessAdv			= $classSizeCountAdv - $studentCountAdv;
					if ($studentCountAdv != 0) {
						$advListKey			= "$thisTime|$thisTeachingDays|Advanced";
						$advListStu			= $studentArray[$advListKey];
						$advColumnBottom	= "<a href='$theURL?inp_time=$thisTime&inp_level=Advanced&inp_teaching_days=$thisTeachingDays&strpass=4&inp_semester=$inp_semester&inp_verbose=$inp_verbose&inp_mode=$inp_mode&inp_list=$advListStu' target='_blank'>$studentCountAdv</a> | $excessAdv";
					} else {
						$advColumnBottom	= "$studentCountAdv | $excessAdv";
					}
					$studentCountTot	= $studentCountTot + $studentCountAdv;
					$studentExcessTot	= $studentExcessTot + $excessAdv;
					$totalStudentsAdv	= $totalStudentsAdv + $studentCountAdv;
					$totalExcessAdv		= $totalExcessAdv + $excessAdv;
					if ($advisorCountAdv == 0 && $studentCountAdv > 0) {
						$colorAdv		= "lightPink";
					} else {
						if ($excessAdv > 3) {
							$colorAdv		= "#00ffff";
						} elseif ($excessAdv < -3) {
							$colorAdv		= "Yellow";
						} else {
							$colorAdv		= "PaleGreen";
						}
					}
				}

				if ($advisorCountTot == 0 && $studentCountTot > 0) {
					$colorTot		= "lightPink";
				} else {
					if ($studentExcessTot > 3) {
						$colorTot		= "#00ffff";
					} elseif ($studentExcessTot < -3) {
						$colorTot		= "Yellow";
					} else {
						$colorTot		= "PaleGreen";
					}
				}
				$content	.= "<tr><td>$thisTime</td>
									<td>$thisTeachingDays</td>
									<td style='background-color:$colorBeg;'>$begColumnTop<br />$begColumnBottom</td>
									<td style='background-color:$colorFun;'>$funColumnTop<br />$funColumnBottom</td>
									<td style='background-color:$colorInt;'>$intColumnTop<br />$intColumnBottom</td>
									<td style='background-color:$colorAdv;'>$advColumnTop<br />$advColumnBottom</td>
									<td style='background-color:$colorTot;'>$advisorCountTot | $classSizeCountTot<br />$studentCountTot | $studentExcessTot</td></tr>";
			}
			
			// now put out the totals
					
				$totalAdvisors		= 0;
				$totalSeats			= 0;
				
				$begColumnTop		= "$totalAdvisorsBeg | $totalSeatsBeg";
				$totalAdvisors		= $totalAdvisors + $totalAdvisorsBeg;
				$totalSeats			= $totalSeats + $totalSeatsBeg;

				$funColumnTop		= "$totalAdvisorsFun | $totalSeatsFun";
				$totalAdvisors		= $totalAdvisors + $totalAdvisorsFun;
				$totalSeats			= $totalSeats + $totalSeatsFun;

				$intColumnTop		= "$totalAdvisorsInt | $totalSeatsInt";
				$totalAdvisors		= $totalAdvisors + $totalAdvisorsInt;
				$totalSeats			= $totalSeats + $totalSeatsInt;

				$advColumnTop		= "$totalAdvisorsAdv | $totalSeatsAdv";
				$totalAdvisors		= $totalAdvisors + $totalAdvisorsAdv;
				$totalSeats			= $totalSeats + $totalSeatsAdv;

				$totalStudents		= 0;
				$totalExcess		= 0;
				
				$begColumnBottom	= "$totalStudentsBeg | $totalExcessBeg";
				$totalStudents		= $totalStudents + $totalStudentsBeg;
				$totalExcess		= $totalExcess + $totalExcessBeg;

				$funColumnBottom	= "$totalStudentsFun | $totalExcessFun";
				$totalStudents		= $totalStudents + $totalStudentsFun;
				$totalExcess		= $totalExcess + $totalExcessFun;

				$intColumnBottom	= "$totalStudentsInt | $totalExcessInt";
				$totalStudents		= $totalStudents + $totalStudentsInt;
				$totalExcess		= $totalExcess + $totalExcessInt;

				$advColumnBottom	= "$totalStudentsAdv | $totalExcessAdv";
				$totalStudents		= $totalStudents + $totalStudentsAdv;
				$totalExcess		= $totalExcess + $totalExcessAdv;
				
				if ($totalAdvisorsBeg == 0) {
					$colorBeg		= "lightPink";
				} else {
					if ($totalExcessBeg > 3) {
						$colorBeg		= "#00ffff";
					} elseif ($totalExcessBeg < -3) {
						$colorBeg		= "Yellow";
					} else {
						$colorBeg		= "PaleGreen";
					}
				}
				if ($totalAdvisorsFun == 0) {
					$colorFun		= "lightPink";
				} else {
					if ($totalExcessFun > 3) {
						$colorFun		= "#00ffff";
					} elseif ($totalExcessFun < -3) {
						$colorFun		= "Yellow";
					} else {
						$colorFun		= "PaleGreen";
					}
				}
				if ($totalAdvisorsInt == 0) {
					$colorInt		= "lightPink";
				} else {
					if ($totalExcessInt > 3) {
						$colorInt		= "#00ffff";
					} elseif ($totalExcessInt < -3) {
						$colorInt		= "Yellow";
					} else {
						$colorInt		= "PaleGreen";
					}
				}
				if ($totalAdvisorsAdv == 0) {
					$colorAdv		= "lightPink";
				} else {
					if ($totalExcessAdv > 3) {
						$colorAdv		= "#00ffff";
					} elseif ($totalExcessAdv < -3) {
						$colorAdv		= "Yellow";
					} else {
						$colorAdv		= "PaleGreen";
					}
				}
				if ($totalAdvisors == 0) {
					$colorTot		= "lightPink";
				} else {
					if ($totalExcess > 3) {
						$colorTot		= "#00ffff";
					} elseif ($totalExcess < -3) {
						$colorTot		= "Yellow";
					} else {
						$colorTot		= "PaleGreen";
					}
				}


				

			$content	.= "<tr><th>Total</th>
								<th>&nbsp;</th>
								<th>Beginner</th>
								<th>Fundamental</th>
								<th>Intermediate</th>
								<th>Advanced</th>
								<th>Total</th></tr>
							<tr><td>Total</td>
								<td>&nbsp;</td>
								<td style='background-color:$colorBeg;'>$begColumnTop<br />$begColumnBottom</td>
								<td style='background-color:$colorFun;'>$funColumnTop<br />$funColumnBottom</td>
								<td style='background-color:$colorInt;'>$intColumnTop<br />$intColumnBottom</td>
								<td style='background-color:$colorAdv;'>$advColumnTop<br />$advColumnBottom</td>
								<td style='background-color:$colorTot;'>$totalAdvisors | $totalSeats<br />$totalStudents | $totalExcess</td></tr>
							</table>";

			$myStr				= "<b>Input Parameters:</b><br />";
			if ($inp_advisors == "all") {
				$myStr			.= "All Advisors<br />";
			} else {
				$myStr			.= "Verified Advisors<br />";
			}
			if ($inp_students == "all") {
				$myStr			.= "All Students<br />";
			} else {
				$myStr			.= "Verified Students<br />";
			}

			$content			.= "<p>$myStr<br />
									<b>Counts:</b><br />
									<b>Raw Data:</b><br />
									$studentsRead: Total student registrations for $inp_semester<br />
									$studentsVerified: Total verified students<br />
									$studentsNotVerified: Students who haven't verified<br />
									$studentsDropped: Total dropped students<br />
									$studentsRefused: Total refused students<br /><br />
									$advisorCount: Total Advisors<br />
									$beginnerAdvisors: Beginner Classes<br />
									$fundamentalAdvisors: Fundamental Classes<br />
									$intermediateAdvisors: Intermediate Classes<br />
									$advancedAdvisors: Advanced Classes<br />
									$totalClasses: Total Classes<br /><br />
									$beginnerStudents: Beginner Students<br />
									$fundamentalStudents: Fundamental Students<br />
									$intermediateStudents: Intermediate Students<br />
									$advancedStudents: Advanced Students<br />
									$totalStudents: Students in the chart<br /><br />
									$firstClassChoiceFilled: Students matching first class choice to available classes<br />
									$secondClassChoiceFilled: Students matching second class choice to available classes<br />
									$thirdClassChoiceFilled: Students matching third class choice to available classes<br />
									$noClassChoiceFilled: Students where no class choices match to available classes<br />
									$studentsArbAssigned: Students arbitrarily given 1900 Monday,Thursday as no class choices made<br />
									$studentsNotCounted: Students not included in chart due to errors<br /><br />";
			
			
//			if (count($arbitraryArray) > 0) {
//				$content		.= "<h4>Students Arbitrarily Placed:</b></h4>";
//				foreach($arbitraryArray as $thisValue) {
//					$content	.=	$thisValue;
//				}
//				$myInt			= count($arbitraryArray);
//				$content		.= "$myInt students<br />";
//			}
			
			if (count($errorArray) > 0) {
				$content		.= "<h4>Errors Encountered:</b></h4>";
				foreach($errorArray as $thisValue) {
					$content	.=	$thisValue;
				}
				$myInt			= count($errorArray);
				$content		.= "$myInt errors<br />";
			}
			
			if (count($notFilled) > 0) {
				sort($notFilled);
				$content		.= "<h4>Students Where No Class Choices Matched</h4>";
				foreach($notFilled as $thisValue) {
					$content	.= $thisValue;
				}
				$myInt			= count($notFilled);
				$content		.= "$myInt no matching class choices<br />";
			}

			if (count($notIncluded) > 0) {
				sort($notIncluded);
				$content		.= "<h4>Students Not Included in the Matrix</h4>";
				foreach($notIncluded as $thisValue) {
					$content	.= $thisValue;
				}
				$myInt			= count($notIncluded);
				$content		.= "$myInt Not Included<br />";
			}

			// put out the planned classes table
			if ($doDebug) {
				echo "<br />classOptionsArray:<br />";
			}
			$content			.= "<br /><b>Current Advisor Planned classes</b>
									<br />All dates and times in UTC
									<table style='width:auto;'>
									<tr><th style='width:110px;'>Level</th>
											<th>Teaching Schedule</th>
											<th>Advisors</th>
											<th style='width:70px;'>Classes</th></tr>";
			$prevLevel			= '';
			foreach($classOptionsArray as $myValue) {
				if ($doDebug) {
					echo "&nbsp;&nbsp;&nbsp;&nbsp;$myValue<br />";
				}
				$myArray		= explode("|",$myValue);
				$thisLevel		= $myArray[0];
				$thisTime		= $myArray[1];
				$thisDays		= $myArray[2];
				$thisCount		= $myArray[3];
				if ($thisLevel != $prevLevel) {
					$myStr		= $thisLevel;
					$prevLevel	= $thisLevel;
				} else {
					$myStr		= "&nbsp;";
				}
				$advisorList	= '';
				$advisorArrayKey = "$thisTime|$thisDays|$thisLevel";
				if (array_key_exists($advisorArrayKey,$advisorArray)) {
					$advisorList	= $advisorArray[$advisorArrayKey];
					$advisorList	= str_replace(",",", ",$advisorList);
					if ($doDebug) {
						echo "&nbsp;&nbsp;&nbsp;found $advisorList in advisorArray<br />";
					}
				} else {
					$advisorList	= "No Advisors";
				}
				$content		.= "<tr><td>$thisLevel</td>
										<td>$thisTime $thisDays</td>
										<td>$advisorList</td>
										<td style='text-align:center;'>$thisCount</td></tr></tr>";
			}
			$content			.= "</table>";
		}

///////////	Pass 3		display selected advisor classes
		
	} elseif ("3" == $strPass) {
		if ($doDebug) {
			echo "at Pass 3<br />";
		}
// $doDebug = TRUE;		
		
		// get days to the semester and determine if there is a catalog. More than 47 days, no catalog
		$daysToSemester			= days_to_semester($inp_semester);
		
		$content				.= "<h3>Advisors in Level $inp_level Teaching on UTC $inp_teaching_days at $inp_time</h3>
									<table>
									<tr><th>Advisor</th>
										<th>Timezone Offset</th>
										<th>Local Teaching Time</th>
										<th>Local Teaching Days</th>
										<th>UTC Teaching Time</th>
										<th>UTC Teaching Days</th></tr>";
		
		// get array of advisors
		$myArray				= explode(",",$inp_list);
		sort($myArray);
		// get each advisor and display
		foreach ($myArray as $myValue) {
			$tempArray				= explode("-",$myValue);
			$tempAdvisor			= $tempArray[0];
			$tempClass			 	= $tempArray[1];
			$sql					= "select * from $advisorClassTableName where advisor_call_sign='$tempAdvisor' and semester='$inp_semester' and sequence=$tempClass";
			$wpw1_cwa_advisorclass				= $wpdb->get_results($sql);
			if ($wpw1_cwa_advisorclass !== FALSE) {
				$numACRows									= $wpdb->num_rows;
				if ($numACRows > 0) {
					foreach ($wpw1_cwa_advisorclass as $advisorClassRow) {
						$advisorClass_ID				 		= $advisorClassRow->advisorclass_id;
						$advisorClass_advisor_call_sign 		= $advisorClassRow->advisor_call_sign;
						$advisorClass_advisor_first_name 		= $advisorClassRow->advisor_first_name;
						$advisorClass_advisor_last_name 		= stripslashes($advisorClassRow->advisor_last_name);
						$advisorClass_advisor_id 				= $advisorClassRow->advisor_id;
						$advisorClass_sequence 					= $advisorClassRow->sequence;
						$advisorClass_semester 					= $advisorClassRow->semester;
						$advisorClass_timezone 					= $advisorClassRow->time_zone;
						$advisorClass_timezone_id				= $advisorClassRow->timezone_id;		// new
						$advisorClass_timezone_offset			= $advisorClassRow->timezone_offset;	// new
						$advisorClass_level 					= $advisorClassRow->level;
						$advisorClass_class_size 				= $advisorClassRow->class_size;
						$advisorClass_class_schedule_days 		= $advisorClassRow->class_schedule_days;
						$advisorClass_class_schedule_times 		= $advisorClassRow->class_schedule_times;
						$advisorClass_class_schedule_days_utc 	= $advisorClassRow->class_schedule_days_utc;
						$advisorClass_class_schedule_times_utc 	= $advisorClassRow->class_schedule_times_utc;
						$advisorClass_action_log 				= $advisorClassRow->action_log;
						$advisorClass_class_incomplete 			= $advisorClassRow->class_incomplete;
						$advisorClass_date_created				= $advisorClassRow->date_created;
						$advisorClass_date_updated				= $advisorClassRow->date_updated;

					
						if ($doDebug) {
							echo "<br />Got advisor $advisorClass_advisor_call_sign<br />
								  &nbsp;&nbsp;&nbsp;&nbsp;ID: $advisorClass_ID<br />
								  &nbsp;&nbsp;&nbsp;&nbsp;Teaching Days: $advisorClass_class_schedule_days<br />
								  &nbsp;&nbsp;&nbsp;&nbsp;Teaching Time: $advisorClass_class_schedule_times<br />";
						}
						$content		.= "<tr><td><a href='$siteURL/cwa-display-and-update-advisor-information/?strpass=2&request_type=callsign&request_info=$advisorClass_advisor_call_sign&inp_pod=$advisorTableName' target='_blank'>$advisorClass_advisor_call_sign</a></td>
												<td style='text-align:center;'>$advisorClass_timezone_offset</td>
												<td>$advisorClass_class_schedule_times</td>
												<td>$advisorClass_class_schedule_days</td>
												<td>$advisorClass_class_schedule_times_utc</td>
												<td>$advisorClass_class_schedule_days_utc</td></tr>";	
					}
				} else {
					if ($doDebug) {
						echo "No records found in $advisorClassTableName<br />";
					}
					$content	.= "No records found in $advisorClassTableName<br />";
				}
			} else {
				if ($doDebug) {
					echo "Either $advisorClassTableName not found or bad $sql<br />";
				}
				$content		.= "Either $advisorClassTableName not found or bad $sql<br />";
			}
		}
		$content				.= "</table>";



//////////		Pass 4	show students
		
	} elseif ("4" == $strPass) {
		if ($doDebug) {
			echo "at pass 4";
		}
		$content				.= "<h3>Students in Level $inp_level Requesting a Class on UTC $inp_teaching_days at $inp_time</h3>
									<table style='width:900px;'>
									<tr><th>Student</th>
										<th>Timezone Offset</th>
										<th style='width:120px;'>Local First Choice Days and Time</th>
										<th style='width:120px;'>Local Second Choice Days and Time</th>
										<th style='width:120px;'>Local Third Choice Days and Time</th>
										<th style='width:120px;'>UTC First Choice Days and Time</th>
										<th style='width:120px;'>UTC Second Choice Days and Time</th>
										<th style='width:120px;'>UTC Third Choice Days and Time</th>";
		// get array of students
		$myArray			= explode(",",$inp_list);
		sort($myArray);
		// now get the student info for each student on the list
		foreach($myArray as $myValue) {
			$studentArray	= explode("-",$myValue);
			$studentCallSign	= $studentArray[0];
			$studentClassChoice	= $studentArray[1];		
			$sql				= "select * from $studentTableName where call_sign='$studentCallSign' and semester='$inp_semester' and level='$inp_level'";
			$wpw1_cwa_student				= $wpdb->get_results($sql);
			if ($doDebug) {
				echo "Reading wpw1_cwa_student table<br />";
				echo "wpdb->last_query: " . $wpdb->last_query . "<br />";
				echo "wpdb->last_error: " . $wpdb->last_error . "<br />";
			}
			if ($wpw1_cwa_student !== FALSE) {
				$numSRows									= $wpdb->num_rows;
				if ($numSRows > 0) {
					foreach ($wpw1_cwa_student as $studentRow) {
						$student_ID								= $studentRow->student_id;
						$student_call_sign						= strtoupper($studentRow->call_sign);
						$student_first_name						= $studentRow->first_name;
						$student_last_name						= stripslashes($studentRow->last_name);
						$student_email  						= strtolower(strtolower($studentRow->email));
						$student_ph_code						= $studentRow->ph_code;
						$student_phone  						= $studentRow->phone;
						$student_city  							= $studentRow->city;
						$student_state  						= $studentRow->state;
						$student_zip_code  						= $studentRow->zip_code;
						$student_country_code					= $studentRow->country_code;
						$student_country  						= $studentRow->country;
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
						$student_abandoned  				= $studentRow->abandoned;
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

						if ($doDebug) {
							echo "<br />Processing student $student_call_sign<br />
								  &nbsp;&nbsp;&nbsp;&nbsp;Level: $student_level<br />
								  &nbsp;&nbsp;&nbsp;&nbsp;TZ: $student_time_zone<br />
								  &nbsp;&nbsp;&nbsp;&nbsp;First Class Choice: $student_first_class_choice<br />
								  &nbsp;&nbsp;&nbsp;&nbsp;Second Class Choice: $student_second_class_choice<br />
								  &nbsp;&nbsp;&nbsp;&nbsp;Third Class Choice: $student_third_class_choice<br />";
						}
						// have all the pieces. Show the result
						$content	.= "<tr><td><a href='$updateStudentURL?strpass=2&request_type=callsign&request_info=$studentCallSign&request_table=$studentTableName' target='_blank'>$studentCallSign</a></td>
											<td style='text-align:center;'>$student_timezone_offset</td>
											<td>$student_first_class_choice</td>
											<td>$student_second_class_choice</td>
											<td>$student_third_class_choice</td>
											<td>$student_first_class_choice_utc</td>
											<td>$student_second_class_choice_utc</td>
											<td>$student_third_class_choice_utc</th></tr>";
					}
				} else {
					if ($doDebug) {
						echo "No student record found for $studentCallSign<br />";
					}
					$content		.= "No student record found for $studentCallSign<br />";
				}
			} else {
				if ($doDebug) {
					echo "Either $studentTableName not found or bad $sql<br />";
				}
				$content			.= "Either $studentTableName not found or bad $sql<br />";
			}
		}
		$content	.= "</table>";
	}

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
	$result			= write_joblog_func("Student and Advisor Color Chart|$nowDate|$nowTime|$userName|Time|$thisStr|$strPass: $elapsedTime");
	if ($result == 'FAIL') {
		$content	.= "<p>writing to joblog.txt failed</p>";
	}
	return $content;
}
add_shortcode ('student_and_advisor_color_chart_v2', 'student_and_advisor_color_chart_v2_func');
