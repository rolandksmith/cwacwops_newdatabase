function select_students_for_assessment_func() {


////// requires inp_callsign, inp_email, and inp_phone

//	modified 17Apr23 by Roland to fix action_log
//  modified 13Jul23 by ROLAND to use consolidated tables


/* Select Students for Assessment 
 *
 * Input Parameters: 	Advisor's call sign (call_sign)
 						Semester (semester)
 						Mode (inp_mode tm or pd)
 *
 * Get all 'class' records for call_sign from past_advisorClass pod for the current semester, 
 * or, if the semester is not in session, the previous semester
 * For each class, get each students from the 'past_student' pod and the curent evaluation
 * 		Display a form for the advisor to specify for each student whether or not to 
 *			send the student an email to do the Morse code assessment
 *		Send the students the assessment request email
 */

	global $wpdb, $doDebug, $testMode, $advisorTableName, $advisorClassTableName, $studentTableName;

	$doDebug						= FALSE;
	$testMode						= FALSE;
	$initializationArray 			= data_initialization_func();
	$validUser 						= $initializationArray['validUser'];
	$siteURL						= $initializationArray['siteurl'];

	if ($doDebug) {
		echo "Initialization Array:<br /><pre>";
		print_r($initializationArray);
		echo "</pre><br />";
	}
						
// set some initialization values
	ini_set('display_errors','1');
	error_reporting(E_ALL);	

/// get the time that the process started
	$startingMicroTime				= microtime(TRUE);

	$userName						= $initializationArray['userName'];
	$validTestmode					= $initializationArray['validTestmode'];
	$jobname						= "Select Students for Assessment";
	$strPass						= "1";
	$inp_verbose					= "N";
	$student10						= '';
	$student11						= '';
	$student12						= '';
	$student13						= '';
	$student14						= '';
	$student15						= '';
	$student16						= '';
	$student17						= '';
	$student18						= '';
	$student19						= '';
	$student20						= '';
	$student21						= '';
	$student22						= '';
	$student23						= '';
	$student24						= '';
	$student25						= '';
	$student26						= '';
	$student27						= '';
	$student28						= '';
	$student29						= '';
	$student30						= '';
	$student31						= '';
	$student32						= '';
	$student33						= '';
	$student34						= '';
	$student35						= '';
	$student36						= '';
	$student37						= '';
	$student38						= '';
	$student39						= '';
	$student40						= '';
	$student41						= '';
	$student42						= '';
	$student43						= '';
	$student44						= '';
	$student45						= '';
	$student46						= '';
	$student47						= '';
	$student48						= '';
	$student49						= '';
	$student50						= '';
	$student51						= '';
	$student52						= '';
	$student53						= '';
	$student54						= '';
	$student55						= '';
	$student56						= '';
	$student57						= '';
	$student58						= '';
	$student59						= '';
	$student60						= '';
	$student61						= '';
	$student62						= '';
	$student63						= '';
	$student64						= '';
	$student65						= '';
	$student66						= '';
	$student67						= '';
	$student68						= '';
	$student69						= '';
	$student70						= '';
	$inp_sunday						= '';
	$inp_monday						= '';
	$inp_tuesday					= '';
	$inp_wednesday					= '';
	$inp_thursday					= '';
	$inp_friday						= '';
	$inp_saturday					= '';
	$inp_associate_advisor			= '';
	$inp_be_associate_advisor		= '';
	$inp_preferred_advisors			= '';
	$inp_level						= '';
	$inp_alt_level_1				= '';
	$inp_alt_level_2				= '';
	$inp_call_sign					= '';
	$inp_mode						= 'pd';
	$increment						= 0;
	$myDate							= date('dMy h:i');
	$theURL							= "$siteURL/cwa-select-students-for-end-of-semester-assessment/";
	$assessmentURL					= "$siteURL/cwa-end-of-semester-student-assessment/";
	$studentCounter					= 9;
	$inp_callsign					= '';
	$token							= '';
	
// get the input information
	if (isset($_REQUEST)) {
		foreach($_REQUEST as $str_key => $str_value) {
			if ($doDebug) {
				echo "Key: $str_key | Value: $str_value <br />\n";
			}
			if ($str_key 				== 'enstr') {
				$enstr					= $str_value;
				$stringToPass			= base64_decode($enstr);
				if ($doDebug) {
					echo "stringToPass: $stringToPass<br />";
				}
				$myArray				= explode("&",$stringToPass);
				foreach($myArray as $myValue) {
					$thisArray			= explode("=",$myValue);
					${$thisArray[0]}	= $thisArray[1];
					if ($doDebug) {
						echo "Setting $thisArray[0] to $thisArray[1]<br />";
					}
				}
			}
			if ($str_key 		== "strpass") {
				$strPass		 = $str_value;
			}
			if ($str_key 		== "semester") {
				$theSemester	 = strtoupper($str_value);
			}
			if ($str_key 		== "inp_mode") {
				$inp_mode	 	= $str_value;
				if ($inp_mode == 'tm' || $inp_mode == 'TESTMODE') {
					$testMode	= TRUE;
echo "testMode set to TRUE<br />";
				}
			}
			if ($str_key 		== "inp_verbose") {
				$inp_verbose	 = $str_value;
				if ($inp_verbose == 'tm' || $inp_verbose == 'Y') {
					$doDebug	= TRUE;
echo "doDebug set to TRUE<br />";
				}
			}
			if ($str_key		== "inp_call_sign") {
				$inp_call_sign	 = strtoupper($str_value);
			}
			if ($str_key		== "inp_callsign") {
				$inp_callsign	 = strtoupper($str_value);
				$inp_callsign	 = filter_var($inp_callsign,FILTER_UNSAFE_RAW);
			}
			if ($str_key		== "token") {
				$token			 = $str_value;
				$token	 		= filter_var($token,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "student10") {
				$student10		 = $str_value;
			}
			if ($str_key 		== "student11") {
				$student11		 = $str_value;
			}
			if ($str_key 		== "student12") {
				$student12		 = $str_value;
			}
			if ($str_key 		== "student13") {
				$student13		 = $str_value;
			}
			if ($str_key 		== "student14") {
				$student14		 = $str_value;
			}
			if ($str_key 		== "student15") {
				$student15		 = $str_value;
			}
			if ($str_key 		== "student16") {
				$student16		 = $str_value;
			}
			if ($str_key 		== "student17") {
				$student17		 = $str_value;
			}
			if ($str_key 		== "student18") {
				$student18		 = $str_value;
			}
			if ($str_key 		== "student19") {
				$student19		 = $str_value;
			}
			if ($str_key 		== "student20") {
				$student20		 = $str_value;
			}
			if ($str_key 		== "student21") {
				$student21		 = $str_value;
			}
			if ($str_key 		== "student22") {
				$student22		 = $str_value;
			}
			if ($str_key 		== "student23") {
				$student23		 = $str_value;
			}
			if ($str_key 		== "student24") {
				$student24		 = $str_value;
			}
			if ($str_key 		== "student25") {
				$student25		 = $str_value;
			}
			if ($str_key 		== "student26") {
				$student26		 = $str_value;
			}
			if ($str_key 		== "student27") {
				$student27		 = $str_value;
			}
			if ($str_key 		== "student28") {
				$student28		 = $str_value;
			}
			if ($str_key 		== "student29") {
				$student29		 = $str_value;
			}
			if ($str_key 		== "student30") {
				$student30		 = $str_value;
			}
			if ($str_key 		== "student31") {
				$student31		 = $str_value;
			}
			if ($str_key 		== "student32") {
				$student32		 = $str_value;
			}
			if ($str_key 		== "student33") {
				$student33		 = $str_value;
			}
			if ($str_key 		== "student34") {
				$student34		 = $str_value;
			}
			if ($str_key 		== "student35") {
				$student35		 = $str_value;
			}
			if ($str_key 		== "student36") {
				$student36		 = $str_value;
			}
			if ($str_key 		== "student37") {
				$student37		 = $str_value;
			}
			if ($str_key 		== "student38") {
				$student38		 = $str_value;
			}
			if ($str_key 		== "student39") {
				$student39		 = $str_value;
			}
			if ($str_key 		== "student40") {
				$student40		 = $str_value;
			}
			if ($str_key 		== "student41") {
				$student41		 = $str_value;
			}
			if ($str_key 		== "student42") {
				$student42		 = $str_value;
			}
			if ($str_key 		== "student43") {
				$student43		 = $str_value;
			}
			if ($str_key 		== "student44") {
				$student44		 = $str_value;
			}
			if ($str_key 		== "student45") {
				$student45		 = $str_value;
			}
			if ($str_key 		== "student46") {
				$student46		 = $str_value;
			}
			if ($str_key 		== "student47") {
				$student47		 = $str_value;
			}
			if ($str_key 		== "student48") {
				$student48		 = $str_value;
			}
			if ($str_key 		== "student49") {
				$student49		 = $str_value;
			}
			if ($str_key 		== "student50") {
				$student50		 = $str_value;
			}
			if ($str_key 		== "student51") {
				$student51		 = $str_value;
			}
			if ($str_key 		== "student52") {
				$student52		 = $str_value;
			}
			if ($str_key 		== "student53") {
				$student53		 = $str_value;
			}
			if ($str_key 		== "student54") {
				$student54		 = $str_value;
			}
			if ($str_key 		== "student55") {
				$student55		 = $str_value;
			}
			if ($str_key 		== "student56") {
				$student56		 = $str_value;
			}
			if ($str_key 		== "student57") {
				$student57		 = $str_value;
			}
			if ($str_key 		== "student58") {
				$student58		 = $str_value;
			}
			if ($str_key 		== "student59") {
				$student59		 = $str_value;
			}
			if ($str_key 		== "student60") {
				$student60		 = $str_value;
			}
			if ($str_key 		== "student61") {
				$student61		 = $str_value;
			}
			if ($str_key 		== "student62") {
				$student62		 = $str_value;
			}
			if ($str_key 		== "student63") {
				$student63		 = $str_value;
			}
			if ($str_key 		== "student64") {
				$student64		 = $str_value;
			}
			if ($str_key 		== "student65") {
				$student65		 = $str_value;
			}
			if ($str_key 		== "student66") {
				$student66		 = $str_value;
			}
			if ($str_key 		== "student67") {
				$student67		 = $str_value;
			}
			if ($str_key 		== "student68") {
				$student68		 = $str_value;
			}
			if ($str_key 		== "student69") {
				$student69		 = $str_value;
			}
			if ($str_key 		== "student70") {
				$student70		 = $str_value;
			}
			if ($str_key 		== "inp_first_name") {
				$inp_first_name	 = htmlentities($str_value);
			}
			if ($str_key 		== "inp_last_name") {
				$inp_last_name	 = htmlentities($str_value);
			}
			if ($str_key 		== "inp_email") { 
				$inp_email		 = $str_value;
			}
			if ($str_key 		== "advisor_email") { 
				$advisor_email		 = $str_value;
			}
			if ($str_key 		== "inp_phone") {
				$inp_phone		 = $str_value;
			}
				if ($str_key 		== "inp_city") {
				$inp_city		 = htmlentities($str_value);
			}
			if ($str_key 		== "inp_state") {
				$inp_state		 = htmlentities($str_value);
			}
			if ($str_key 		== "inp_country") {
				$inp_country	 = htmlentities($str_value);
			}
			if ($str_key 		== "inp_time_zone") {
				$inp_time_zone	 = $str_value;
			}
			if ($str_key 		== "inp_class_size") {
				$inp_class_size	 = $str_value;
			}
			if ($str_key 		== "inp_sunday") {
				$inp_sunday		 = $str_value;
			}
			if ($str_key 		== "inp_monday") {
				$inp_monday		 = $str_value;
			}
			if ($str_key 		== "inp_tuesday") {
				$inp_tuesday	 = $str_value;
			}
			if ($str_key 		== "inp_wednesday") {
				$inp_wednesday	 = $str_value;
			}
			if ($str_key 		== "inp_thursday") {
				$inp_thursday	 = $str_value;
			}
			if ($str_key 		== "inp_friday") {
				$inp_friday	 = $str_value;
			}
			if ($str_key 		== "inp_saturday") {
				$inp_saturday	 = $str_value;
			}
			if ($str_key 		== "inp_level") {
				$inp_level		 = $str_value;
			}
			if ($str_key 		== "inp_alt_level_1") {
				$inp_alt_level_1 = $str_value;
			}
			if ($str_key 		== "inp_alt_level_2") {
				$inp_alt_level_2 = $str_value;
			}
			if ($str_key 		== "inp_languages_spoken") {
				$inp_languages_spoken	 = htmlentities($str_value);
			}
			if ($str_key 		== "inp_associate_advisor") {
				$inp_associate_advisor	 = htmlentities($str_value);
			}
			if ($str_key 		== "inp_class_schedule_time") {
				$inp_class_schedule_time = htmlentities($str_value);
			}
			if ($str_key 		== "inp_be_associate_advisor") {
				$inp_be_associate_advisor = htmlentities($str_value);
			}
			if ($str_key 		== "inp_preferred_advisors") {
				$inp_preferred_advisors = htmlentities($str_value);
			}
			if ($str_key 		== "inp_another_class") {
				$inp_another_class = htmlentities($str_value);
			}
			if ($str_key 		== "inp_n_semester") {
				$inp_n_semester = htmlentities($str_value);
			}
			if ($str_key 		== "select_sequence") {
				$inp_select_sequence = htmlentities($str_value);
			}
			if ($str_key 		== "past_advisor_phone") {
				$past_advisor_phone = $str_value;
			}
			if ($str_key 		== "past_advisor_email") {
				$past_advisor_email = $str_value;
			}
		}
	}

	if ($doDebug) {
		$inp_verbose	= 'Y';
	}

	function getStudentInfo($theInfo,$theSemester,$studentCount) {
		global $wpdb, $doDebug, $testMode, $advisorTableName, $advisorClassTableName, $studentTableName;
		
		if ($theInfo == '') {
			if ($doDebug) {
				echo "Student call sign missing<br />";
			}
			return "ERROR";
			
			
		} else {
			$sql 				= "select * from $studentTableName 
									where call_sign='$theInfo' 
									and semester='$theSemester'";
			$wpw1_cwa_student	= $wpdb->get_results($sql);
			if ($wpw1_cwa_student === FALSE) {
				if ($doDebug) {
					echo "Reading $studentTableName table failed<br />
						  wpdb->last_query: " . $wpdb->last_query . "<br />
						  <b>wpdb->last_error: " . $wpdb->last_error . "</b><br />";
				}
			} else {
				$numPSRows									= $wpdb->num_rows;
				if ($doDebug) {
					$myStr			= $wpdb->last_query;
					echo "ran $myStr<br />and found $numPSRows rows in $studentTableName table<br />";
				}
				if ($numPSRows > 0) {
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
						$student_no_catalog		  				= $studentRow->no_catalog;
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
						$student_student_survey_completion_date = $studentRow->student_survey_completion_date;
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
						
						if ($student_promotable == 'W') {
							return "ERROR";
						}
						
						$returnData			= "
$student_last_name, $student_first_name ($student_call_sign)<br />
<div style='padding-left:15px;'><input type='radio' class='formInputButton' name='student$studentCount' value='$student_last_name,$student_first_name,$student_call_sign,$student_level,$student_email,$student_phone,$student_assigned_advisor,Y' checked>Send Assessment Email<br />
								<input type='radio' class='formInputButton' name='student$studentCount' value='$student_last_name,$student_first_name,$student_call_sign,$student_level,$student_email,$student_phone,$student_assigned_advisor,N'>No Assessment Email<br /></div>";
						return $returnData;
					}
				} else {
					if ($doDebug) {
						echo "No record found for $theInfo<br />";
					}
					return "ERROR";					
				}
			}
		}
	
	}				/// end of function
	

	
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

	if ($testMode) {
		$content	.= "<p><strong>Operating in Test Mode. Running against student2, student2, advisor2, advisor2 and class2</strong></p>";
		if ($doDebug) {
			echo "<p><strong>Operating in Test Mode. Running against student2, student2, advisor2, advisor2 and class2</strong></p>";
		}
		$advisorTableName		= "wpw1_cwa_consolidated_advisor2";
		$advisorClassTableName	= "wpw1_cwa_consolidated_advisorclass2";
		$studentTableName		= "wpw1_cwa_consolidated_student2";
		$inp_mode					= 'tm';
	} else {
		$advisorTableName		= "wpw1_cwa_consolidated_advisor";
		$advisorClassTableName	= "wpw1_cwa_consolidated_advisorclass";
		$studentTableName		= "wpw1_cwa_consolidated_student";
		$inp_mode					= 'pd';
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

		$currentSemester				= $initializationArray['currentSemester'];
		$prevSemester					= $initializationArray['prevSemester'];
		if ($currentSemester != 'Not in Session') {
			$theSemester				= $currentSemester;
		} else {
			$theSemester				= $prevSemester;
		}

///////////========================////////////////

	if ("1" == $strPass) {
		if ($doDebug) {
			echo "Function starting.<br />";
		}
		if ($validUser == "N") {
			return "YOU'RE NOT AUTHORIZED!<br />Goodby";
		} else {
			$currentSemester	= $initializationArray['currentSemester'];
			$pastSemester		= $initializationArray['prevSemester'];
			if ($currentSemester == 'Not in Session') {
				$theSemester	= $pastSemester;
			} else {
				$theSemester	= $currentSemester;
			}
		$content 		.= "<h3>Advisor Call Sign</h3>
<form method='post' action='$theURL' 
name='selection_form' ENCTYPE='multipart/form-data'>
<input type='hidden' name='strpass' value='2'>
<input type='hidden' name='semester' value='$theSemester'>
<input type='hidden' name='inp_mode' value='$inp_mode'>
<table style='border-collapse:collapse;'>
<tr><td style='width:150px;'>Advisor Call Sign</td>
<td><input class='formInputText' type='text' maxlength='30' name='inp_call_sign' size='10' autofocus></td></tr>
<tr><td><b>Email Address</b></td>
	<td><input type='text' class='formInputText' name='inp_email' size='30' maxlength='50'></td></tr>
<tr><td><b>Phone Number</b></td>
	<td><input type='text' class='formInputText' name='inp_phone' size='20' maxlength='20'></td</tr>
$testModeOption
<tr><td>&nbsp;</td><td><input class='formInputButton' type='submit' value='Submit' /></td></tr></table>
</form>";
		}


///// Pass 2


	} elseif ("2" == $strPass) {
		if ($doDebug) {
			echo "Arrived at Pass 2 with inp_call_sign of $inp_call_sign<br />";
		}
		if ($userName == '') {
			$userName			= $inp_call_sign;
		}
		$validUser						= TRUE;
		// get the advisor record
		$sql				= "select email, phone from $advisorTableName 
								where call_sign='$inp_call_sign' 
								and semester='$theSemester'";
		$cwa_advisor			= $wpdb->get_results($sql);
		if ($cwa_advisor === FALSE) {
			if ($doDebug) {
				echo "Reading $advisorTableName table<br />";
				echo "wpdb->last_query: " . $wpdb->last_query . "<br />";
				echo "<b>wpdb->last_error: " . $wpdb->last_error . "</b><br />";
			}
		} else {
			$numPARows									= $wpdb->num_rows;
			if ($doDebug) {
				echo "found $numPARows rows in $advisorTableName<br />";
			}
			if ($numPARows > 0) {
				foreach ($cwa_advisor as $advisorRow) {
					$advisor_email 					= $advisorRow->email;
					$advisor_phone					= $advisorRow->phone;
				}
				
				$advisor_last4Digits		= substr($advisor_phone,-4,4);
				$inp_last4Digits			= substr($inp_phone,-4,4);
			} else {
				if ($doDebug) {
					echo "no $advisorTableName record for $inp_call_sign<br />";
				}
				$validUser					= FALSE;
			}
		}
		if (in_array($userName,$validTestmode)) {
			$validUser						= TRUE;
		}
		if (!$validUser) {
			// if not a valid logged in user, verify the email address and phone number
			if (!in_array($userName,$validTestmode)) {
				if ($advisor_last4Digits != $inp_last4Digits) {
					$validUser				= FALSE;
				}
				if ($inp_email != $advisor_email) {
					$validUser				= FALSE;
				}
			}
		}
		
		if ($validUser) {	
			$haveClasses			= FALSE;			/// will turn true if at least one class found		
			$displayContent			= "";
			$totalIncomplete		= 0;
			$thisCount				= 9;
			// prepare to read the class table for this advisor
			$currentSemester				= $initializationArray['currentSemester'];
			$prevSemester					= $initializationArray['prevSemester'];
			if ($currentSemester != 'Not in Session') {
				$theSemester				= $currentSemester;
			} else {
				$theSemester				= $prevSemester;
			}
			$content				.= "<h3>Select Students for Morse Code Assessment</h3>
										<p><strong>Instructions</strong>:<br />
										The students for your class or classes are listed below. You 
										can select the student's who will be requested to do an end-of-semester Morse Code assessment 
										by clicking in the round circles below the 
										student's name next to the appropriate option.<br /><br />
										After you have completed your selections, click the 'Submit' button.</p>
										<form method='post' action='$theURL' 
										name='selection_form1' ENCTYPE='multipart/form-data'>
										<input type='hidden' name='strpass' value='3'>
										<input type='hidden' name='semester' value='$theSemester'>
										<input type='hidden' name='advisor_email' value='$advisor_email'>
										<input type='hidden' name='inp_mode' value='$inp_mode'>
										<input type='hidden' name='inp_verbose' value='$inp_verbose'>
										<input type='hidden' name='inp_call_sign' value='$inp_call_sign'>";
			$sql					= "select * from $advisorClassTableName 
											where semester='$theSemester' 
											and advisor_call_sign='$inp_call_sign'";
			$cwa_advisorclass			= $wpdb->get_results($sql);
			$wpw1_cwa_advisorclass	= $wpdb->get_results($sql);
			if ($wpw1_cwa_advisorclass === FALSE) {
				if ($doDebug) {
					echo "Reading $advisorClassTableName table failed<br />
						  wpdb->last_query: " . $wpdb->last_query . "<br />
						  <b>wpdb->last_error: " . $wpdb->last_error . "</b><br />";
				}
			} else {
				$numACRows				= $wpdb->num_rows;
				if ($doDebug) {
					$myStr				= $wpdb->last_query;
					echo "ran $myStr<br />and found $numACRows rows in $advisorClassTableName table<br />";
				}
				if ($numACRows > 0) {
					foreach ($wpw1_cwa_advisorclass as $advisorClassRow) {
						$advisorClass_ID				 		= $advisorClassRow->advisorclass_id;
						$advisorClass_advisor_callsign 		= $advisorClassRow->advisor_call_sign;
						$advisorClass_advisor_first_name 		= $advisorClassRow->advisor_first_name;
						$advisorClass_advisor_last_name 		= stripslashes($advisorClassRow->advisor_last_name);
						$advisorClass_advisor_id 				= $advisorClassRow->advisor_id;
						$advisorClass_sequence 				= $advisorClassRow->sequence;
						$advisorClass_semester 				= $advisorClassRow->semester;
						$advisorClass_timezone 				= $advisorClassRow->time_zone;
						$advisorClass_timezone_id				= $advisorClassRow->timezone_id;		// new
						$advisorClass_timezone_offset			= $advisorClassRow->timezone_offset;	// new
						$advisorClass_level 					= $advisorClassRow->level;
						$advisorClass_class_size 				= $advisorClassRow->class_size;
						$advisorClass_class_schedule_days 		= $advisorClassRow->class_schedule_days;
						$advisorClass_class_schedule_times 	= $advisorClassRow->class_schedule_times;
						$advisorClass_class_schedule_days_utc 	= $advisorClassRow->class_schedule_days_utc;
						$advisorClass_class_schedule_times_utc	= $advisorClassRow->class_schedule_times_utc;
						$advisorClass_action_log 				= $advisorClassRow->action_log;
						$advisorClass_class_incomplete 		= $advisorClassRow->class_incomplete;
						$advisorClass_date_created				= $advisorClassRow->date_created;
						$advisorClass_date_updated				= $advisorClassRow->date_updated;
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
						$class_evaluation_complete 					= $advisorClassRow->evaluation_complete;
						$class_comments								= $advisorClassRow->class_comments;
						$copycontrol								= $advisorClassRow->copy_control;

						$haveClasses				= TRUE;
						if ($doDebug) {
							echo "<br />Processing the $advisorClassTableName record for $advisorClass_advisor_callsign|$advisorClass_semester|$advisorClass_level|$advisorClass_sequence|$class_number_students<br />";
						}
						$content				.= "<br /><fieldset><legend>Semester: $theSemester&nbsp;&nbsp;&nbsp;$advisorClass_level&nbsp;&nbsp;&nbsp;Sequence: $advisorClass_sequence</legend>";
						for ($snum=1;$snum<=$class_number_students;$snum++) {
							if ($snum < 10) {
								$strSnum 		= str_pad($snum,2,'0',STR_PAD_LEFT);
							} else {
								$strSnum		= strval($snum);
							}
							$theInfo			= ${'advisorClass_student' . $strSnum};
							if ($doDebug) {
								echo "<br />Handling student$strSnum whose info is $theInfo<br />";
							}
							if ($theInfo != '') {
								$thisCount++;
								$studentCount		= str_pad($thisCount,2,'0',STR_PAD_LEFT);
								$studentCounter++;
								$thisStudent		= getStudentInfo($theInfo,$theSemester,$studentCounter);
								if ($thisStudent !== 'ERROR') {
									$content		.= "$thisStudent<br />";
								}									
							} else {
								if ($doDebug) {
									echo "Processing student$strSnum, within number of students, info is empty<br />";
								}
							}
						}
						$content					.= "</fieldset><br />";
					}	// end of foreach
					if ($doDebug) {
						echo "All records processed for this advisor<br />";
					}
					$content 	.= "</fieldset>
									<tr><td><input class='formInputButton' type='submit' value='Submit' /></form>";
				} else {
					$content	.= "<p>No classes found for advisor $inp_call_sign</p>";
				}
			}
		} else {
			$content		.= "Invalid Input";
		}	
		
//////// 	Pass 3: Send the emails		
		
	} elseif ("3" == $strPass) {

		if ($doDebug) {
			echo "Arrived at pass 3<br />";
		}
		if ($userName == '') {
			$userName				= $inp_call_sign;
		}
		
		$content					.= "<h3>Evaluate Students</h3>";
		// Go through each of the student records
		for ($snum=10;$snum<61;$snum++) {
			$strSnum			= strval($snum);
			$theInfo			= ${'student' . $strSnum};
			if ($doDebug) {
				echo "<br />Processing student$strSnum: $theInfo<br />";
			}
			if ($theInfo != '') {
				$studentArray			= explode(",",$theInfo);
				if ($doDebug) {
					echo "studentArray:<br /><pre>";
					print_r($studentArray);
					echo "</pre><br />";
				}
				$student_last_name		= $studentArray[0];
				$student_first_name		= $studentArray[1];
				$student_call_sign		= strtoupper($studentArray[2]);
				$student_level			= $studentArray[3];
				$student_email			= $studentArray[4];
				$student_phone			= $studentArray[5];
				$student_advisor		= $studentArray[6];
				$sendEmail				= $studentArray[7];
				
				if ($sendEmail == 'Y') {
					$token		= mt_rand();
					$myStr		= "$siteURL/cwa-select-students-for-end-of-semester-assessment/?strpass=4&inp_callsign=$student_call_sign&token=$token";
					$returnurl	= urlencode($myStr);
					
					if ($student_level == 'Beginner') {
						$thisCPM		= '25';
						$thisEff		= '6';
						$thisFreq		= '450,500,550,600,650';
						$thisQuestions	= '5';
						$thisWords		= '2';
						$thisCharacters	= '2';
						$thisAnswers	= '5';
						$thisVocab		= 'threek';
						$thisInfor		= '$student_advisor%20E-o-S%20Assessment';
					} elseif ($student_level == 'Fundamental') {
						$thisCPM		= '25';
						$thisEff		= '10';
						$thisFreq		= '450,500,550,600,650';
						$thisQuestions	= '5';
						$thisWords		= '2';
						$thisCharacters	= '3';
						$thisAnswers	= '5';
						$thisVocab		= 'threek';
						$thisInfor		= '$student_advisor%20E-o-S%20Assessment';
					} elseif ($student_level == 'Intermediate') {
						$thisCPM		= '25';
						$thisEff		= '20';
						$thisFreq		= '450,500,550,600,650';
						$thisQuestions	= '5';
						$thisWords		= '2';
						$thisCharacters	= '4';
						$thisAnswers	= '5';
						$thisVocab		= 'threek';
						$thisInfor		= '$student_advisor%20E-o-S%20Assessment';
					} else {
						$thisCPM		= '30';
						$thisEff		= '30';
						$thisFreq		= '450,500,550,600,650';
						$thisQuestions	= '5';
						$thisWords		= '2';
						$thisCharacters	= '5';
						$thisAnswers	= '5';
						$thisVocab		= 'threek';
						$thisInfor		= '$student_advisor%20E-o-S%20Assessment';
					}
					$url 		= "<a href='https://cw-assessment.vercel.app?mode=specific&callsign=$student_call_sign&cpm=$thisCPM&eff=$thisEff&freq=$thisFreq&questions=$thisQuestions&words=$thisWords&characters=$thisCharacters&answers=$thisAnswers&vocab=$thisVocab&infor=$thisInfor&level=$student_level&token=$token&returnurl=$returnurl' target='_blank'>Perform Assessment</a>";
				
					$content				.= "sending email to $student_last_name, $student_first_name ($student_call_sign) at $student_email<br />";
					$stringToPass			= "studentCallSign=$student_call_sign&studentEmail=$student_email&studentPhone=$student_phone&studentLevel=$student_level&studentSemester=$theSemester&advisor=$inp_call_sign&advisor_email=$advisor_email&inp_mode=$inp_mode&inp_verbose=$inp_verbose";
					$enstr					= base64_encode($stringToPass);
					// store te string in the temp data table
					$thisDate		= date('Y-m-d H:i:s');
					$insertResult	= $wpdb->insert('wpw1_cwa_temp_data',
													array('callsign'=>$student_call_sign,
														  'token'=>$token,
														  'temp_data'=>$enstr,
														  'date_written'=>$thisDate),
													array('%s','%s','%s','%s'));
					if ($insertResult === FALSE) {
						if ($doDebug) {
							$lastQuery	= $wpdb->last_query;
							$lastError	= $wpdb->last_error;
							echo "attempting to write to wpw1_cwa_temp_data failed. SQL: $lastQuery<br />Error: $lastError<br />";
						}
					} else {
						if ($doDebug) {
							echo "data successfully written to temp_data<br />";
						}
					}

					$theSubject				= "CW Academy Advisor Request for End of Semester Assessment";
					$emailContent			= "To: $student_last_name, $student_first_name ($student_call_sign)
												<p>Your advisor $inp_call_sign has requested that you do an end-of-semester Morse code assessment. This 
												assessment is one factor that the advisor may use to evaluate your readiness to be promoted and 
												go on to the next level. When you click on the link below, a web page will open with detailed 
												instructions.</p><p>You will have five questions in Morse code. After each question is sent in Morse code, 
												you can select the best answer from the choices given.</p>
												<p>After the five questions, the program will  display your results 
												and send them by email to your advisor.</p>
												<p>To do the assessment, click:<br />
												$url</p>
												<p>Good Luck,<br />CW Academy</p>";

					if ($testMode) {
						$theRecipient		= "kcgator@gmail.com";
						$increment++;
						$mailCode			= 2;
						$theSubject			= "TESTMODE $theSubject";
					} else {
						$theRecipient		= $student_email;
						$mailCode			= 11;
					}			
					$mailResult		= emailFromCWA_v2(array('theRecipient'=>$theRecipient,
															'theSubject'=>$theSubject,
															'theContent'=>$emailContent,
															'theCc'=>'',
															'mailCode'=>$mailCode,
															'jobname'=>$jobname,
															'increment'=>$increment,
															'testMode'=>$testMode,
															'doDebug'=>$doDebug));
					if ($mailResult === FALSE) {
						if ($doDebug) {
							echo "email to the student failed<br />";
						}
						$content			= "Sending email to $theRecipient failed<br />";
					}
					
				} else {
					$content				.= "Not sending an email to $student_last_name, $student_first_name ($student_call_sign) at $student_email<br />";
				}
			}
		}
		
		
		
	} elseif ("4" == $strPass) {
		if ($doDebug) {
			echo "<br />at pass 4<br />
			inp_callsign: $inp_callsign<br />
			token: $token<br />";
		}
		if ($inp_callsign == '' || $token == '') {
			return;
		}
		$doProceed			= TRUE;
		$increment			= 0;
		$sendAdvisorEmail	= FALSE;
		$content		.= "<h3>Morse Code Proficiency Assessment Results</h3>";
		// get the info from temp_data and decode
		$sql			= "select * from wpw1_cwa_temp_data 
							where token = '$token' and 
							callsign = '$inp_callsign'";
		$tempResult		= $wpdb->get_results($sql);
		if ($tempResult === FALSE) {
			$thisError	= $wpdb->last_error;
			if ($doDebug) {
				echo "Attempt to query wpw1_cwa_temp_data failed. Error: $thisError<br />SQL: $sql<br /";
			}
			$content	.= "A fatal program error has occured";
			$doProceed	= FALSE;
		} else {
			$numRows 	= $wpdb->num_rows;
			if ($doDebug) {
				echo "ran $sql<br />and retrieved $numRows rows<br />";
			}
			if ($numRows > 0) {
				foreach($tempResult as $tempRow) {
					$record_id		= $tempRow->record_id;
					$temp_callsign	= $tempRow->callsign;
					$temp_token		= $tempRow->token;
					$temp_data		= $tempRow->temp_data;
					$temp_date		= $tempRow->date_written;
				}
				$theseParams		= base64_decode($temp_data);
				if ($doDebug) {
					echo "temp data of $temp_data<br />decoded as $theseParams<br />";
				}
				$thisArray			= explode("&",$theseParams);
				foreach($thisArray as $thisValue) {
					if ($doDebug) {
						echo "processing $thisValue<br />";
					}

					$myArray		= explode("=",$thisValue);
					$myField		=  $myArray[0];
					$myData			= $myArray[1];
					$$myField		= $myData;
				}
				if ($inp_verbose == 'Y') {
					$doDebug		= TRUE;
				}
				if ($inp_mode == 'TESTMODE') {
					$testMode		= TRUE;
				}
				if ($doDebug) {
					echo "enstr decoded data:<br />
						  studentCallSign: $studentCallSign<br />
						  studentEmail: $studentEmail<br />
						  advisor: $advisor<br />
						  advisor_email: $advisor_email<br />
						  studentSemester: $studentSemester<br />
						  inp_mode: $inp_mode<br />
						  inp_verbose: $inp_verbose<br />
						  studentLevel: $studentLevel<br />";
				}
				// now delete the temp_data record

				$tempDelete			= $wpdb->delete('wpw1_cwa_temp_data',
													array('record_id'=>$record_id),
													array('%d'));
				if ($tempDelete === FALSE) {
					$thisQuery		= $wpdb->last_query;
					$thisError		= $wpdb->last_error;
					if ($doDebug) {
						echo "deleted record_id $record_id from wpw1_cwa_temp_data failed. SQL: $thisQuery<br />Error; $thisError<br />";
					}
					$content		.= "Fatal program error";
					$doProceed		= FALSE;
				} else {
					if ($doDebug) {
						echo "record_id $record_id deleted from wpw1_cwa_temp_data<br />";
					}
				}
				
			} else {		// no matching data found in temp data table
				$doProceed			= FALSE;
				/* 	if no record, the student is attempting to do an assessment
				 	request another time. This is a problem without a good 
				 	solution as the student did the assessment, but we can not 
				 	use the data.
				 */
				 
				 $content	.= "Unfortunately, you have clicked on a link that has 
				 				expired. Even though you did the assessment, the 
				 				information is not useable. Please contact your 
				 				advisor for further information";
				 $doProceed	= FALSE;
			}
		}
		// get the results and send the email to the advisor
		if ($doProceed) {
			// check to see what is in the new assessment table for this student
			$bestResultBeginner		= 0;
			$didBeginner			= FALSE;
			$bestResultFundamental	= 0;
			$didFundamental			= FALSE;
			$bestResultIntermediate	= 0;
			$didIntermediate		= FALSE;
			$bestResultAdvanced		= 0;
			$didAdvanced			= FALSE;
			$retVal			= displayAssessment($inp_callsign,$inp_token,$doDebug);
			if ($retVal[0] === FALSE) {
				if ($doDebug) {
					echo "displayAssessment returned FALSE. Called with $inp_callsign, $inp_token<br />";
				}
				$content	.= "No data to display.<br />Reason: $retVal[1]";
			} else {
				$content	.= $retVal[1];
				$myArray	= explode("&",$retVal[2]);
				foreach($myArray as $thisValue) {
					$myArray1	= explode("=",$thisValue);
					$thisKey	= $myArray1[0];
					$thisData	= $myArray1[1];
					$$thisKey	= $thisData;
					if ($doDebug) {
						echo "$thisKey = $thisValue<br />";
					}
				}
				$content		.= "$retVal[1]
									<p>You have completed the Morse Code Proficiency assessment.<br />";
				if ($didBeginner) {
					$content	.= "Your Beginner Level assessment score was $bestResultBeginner<br />";
				}
				if ($didFundamental) {
					$content	.= "Your Fundamental Level assessment score was $bestResultFundamental<br />";
				}
				if ($didIntermediate) {
					$content	.= "Your Intermediate Level assessment score was $bestResultIntermediate<br />";
				}
				if ($didAdvanced) {
					$content	.= "Your Advanced Level assessment score was $bestResultAdvanced<br />";
				}
				$emailContent		= "To: $advisor<br />
<p>You requested that your student $studentCallSign in your $studentLevel level class do an 
end-of-semester Morse Code Proficiency Assessment. Here are the results from that 
assessment:</p>
$retVal[1];
<p>The student could do the assessment a maximum of two times. The results of the assessment is 
one factor that you may consider when determining if a student is promotable.</p>
<br /><br />73,<br />CW Academy";
				$sendAdvisorEmail	= TRUE;
													
				$content				.= "<p>Report was sent to your advisor:<br />
											<br /><br /><p>You may close this window</p>";
			}
		} else {
			if ($doDebug) {
				echo "getting data from wpw1_cwa_temp_data for callsign: $inp_callsign and token: $token failed. No rows found<br />";
			}
			/// notify advisor that student quit the assessment
			$emailContent			= 	"To: $advisor<br />
<p>You requested that your student $studentCallSign in your $studentLevel level class do an 
end-of-semester Morse Code Proficiency Assessment. The student started the assessment but 
quit before a result could be compiled.</p>
<p>The student was instructed to contact you to discuss further options</p>
<p>One option you may take is to send the student another assessment request. The previous 
assessment request has expired.</p><br /><br />73,<br />CW Academy<br /><br />
Token: $token<br />
CS: $inp_callsign<br />
SSFA";
			$sendAdvisorEmail		= TRUE;
			$content				.= "You started the assessment but did not finish. 
										As a result, no assessment could be compiled. Please 
										contact your advisor for further instructions.";
		}
		if ($sendAdvisorEmail) {
			$emailSubject		= "CW Academy Student $studentCallSign End-of-Semester Assessment";
			$mailCode			= 13;


			if ($testMode) {
				$emailSubject	= "TESTMODE $emailSubject";
				$mailCode		= 2;
				$increment++;
			}

			$mailResult		= emailFromCWA_v2(array('theRecipient'=>$advisor_email,
								'theSubject'=>$emailSubject,
								'theContent'=>$emailContent,
								'theCc'=>'',
								'mailCode'=>$mailCode,
								'jobname'=>$jobname,
								'increment'=>$increment,
								'testMode'=>$testMode,
								'doDebug'=>$doDebug));
			if ($mailResult === FALSE) {
				if ($doDebug) {
					echo "sending email to advisor $advisorEmail failed<br />";
				}
				// add senderroremail here
			}
		}
	}
	$thisTime 					= date('Y-m-d H:i:s');
	$content .= "<br /><br /><p>Prepared at $thisTime</p>";
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
	$result			= write_joblog_func("$jobname|$nowDate|$nowTime|$userName|Time|$thisStr|$strPass: $elapsedTime");
	if ($result == 'FAIL') {
		$content	.= "<p>writing to joblog.txt failed</p>";
	}
	return $content;
}
add_shortcode ('select_students_for_assessment', 'select_students_for_assessment_func');
