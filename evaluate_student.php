function evaluate_student_func() {

/* Obtain Advisor's Student Evaluations
 * This function can be run directly or can be executed through a link sent in 
 * an email to the advisor.
 *
 * Input Parameters: 	Advisor's call sign (call_sign)
 						Semester (semester)
 						Mode (inp_mode tm or pd)
 *
 * Get all 'class' records for call_sign from advisorClass table for the current semester, 
 * or, if the semester is not in session, the previous semester
 * For each class, get each students from the 'student' table and the curent evaluation
 * If there are students which have not been evaluated, then
 * 		Display a form for the advisor to specify for each student which hasn't already been
 * 		evaluated:
 *			Promotable (Y)
 *			Not promotable (N)
 *			Withdrew or dropped (W)
 *  		Not yet evaluated ()
 *
 * 		Advisor can then input the evaluation for each student. 
 *
 * If all students have been evaluated, ask if the advisor wishes to sign up for the
 * next semester
 * 		If Yes,
 *			Send them to the form for the advisor to sign up
 *
 *	Modified 4Mar21 by Roland to change Joe Fisher's email address
 *	Modified 12May21 by Roland to use past_advisor and past_advisorClass pods
 	Modified 27Oct22 by Roland to accomodate timezone table format
 	Modified 16Apr23 by Roland to fix action_log
 	Modified 14Jun23 by Roland to use consolidated tables rather than past tables
 	Modified 31Aug23 by Roland to turn off dodebug and testmode if validUser is N
 	Modified 6Dec23 by Roland to run in the Advisor Portal
*/

	global $wpdb, $doDebug, $testMode, $advisorTableName, $advisorClassTableName, $studentTableName, $jobname, $userName;

	$doDebug						= FALSE;
	$testMode						= FALSE;
	$initializationArray 			= data_initialization_func();
	$validUser 						= $initializationArray['validUser'];
	$versionNumber					= '1';
	
	if ($validUser == 'N') {				// turn off debug and testmode
		$doDebug					= FALSE;
		$testMode					= FALSE;
	}
	$validUser 						= $initializationArray['validUser'];
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
	$siteURL						= $initializationArray['siteurl'];
	$nextSemester					= $initializationArray['nextSemester'];
	$token							= '';
	$strPass						= "1";
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
	$student71						= '';
	$student72						= '';
	$student73						= '';
	$student74						= '';
	$student75						= '';
	$student76						= '';
	$student77						= '';
	$student78						= '';
	$student79						= '';
	$student80						= '';
	$student81						= '';
	$student82						= '';
	$student83						= '';
	$student84						= '';
	$student85						= '';
	$student86						= '';
	$student87						= '';
	$student88						= '';
	$student89						= '';
	$student90						= '';
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
	$inp_verbose					= '';
	$myDate							= date('dMy h:i');
	$registerURL					= "$siteURL/cwa-advisor-registration/";
	$evaluateStudentURL				= "$siteURL/cwa-evaluate-student/";
	$jobname						= "Evaluate Student V$versionNumber";
	
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
// echo "testMode set to TRUE<br />";
				}
			}
			if ($str_key 		== "inp_verbose") {
				$inp_verbose	 = $str_value;
				if ($inp_verbose == 'tm' || $inp_verbose == 'Y') {
					$doDebug	= TRUE;
// echo "doDebug set to TRUE<br />";
				}
			}
			if ($str_key		== "inp_call_sign") {
				$inp_call_sign	 = strtoupper($str_value);
			}
			if ($str_key 		== "token") {
				$token		 = strtoupper($str_value);
			}
			if ($str_key 		== "student10") {
				$student10		 = strtoupper($str_value);
			}
			if ($str_key 		== "student11") {
				$student11		 = strtoupper($str_value);
			}
			if ($str_key 		== "student12") {
				$student12		 = strtoupper($str_value);
			}
			if ($str_key 		== "student13") {
				$student13		 = strtoupper($str_value);
			}
			if ($str_key 		== "student14") {
				$student14		 = strtoupper($str_value);
			}
			if ($str_key 		== "student15") {
				$student15		 = strtoupper($str_value);
			}
			if ($str_key 		== "student16") {
				$student16		 = strtoupper($str_value);
			}
			if ($str_key 		== "student17") {
				$student17		 = strtoupper($str_value);
			}
			if ($str_key 		== "student18") {
				$student18		 = strtoupper($str_value);
			}
			if ($str_key 		== "student19") {
				$student19		 = strtoupper($str_value);
			}
			if ($str_key 		== "student20") {
				$student20		 = strtoupper($str_value);
			}
			if ($str_key 		== "student21") {
				$student21		 = strtoupper($str_value);
			}
			if ($str_key 		== "student22") {
				$student22		 = strtoupper($str_value);
			}
			if ($str_key 		== "student23") {
				$student23		 = strtoupper($str_value);
			}
			if ($str_key 		== "student24") {
				$student24		 = strtoupper($str_value);
			}
			if ($str_key 		== "student25") {
				$student25		 = strtoupper($str_value);
			}
			if ($str_key 		== "student26") {
				$student26		 = strtoupper($str_value);
			}
			if ($str_key 		== "student27") {
				$student27		 = strtoupper($str_value);
			}
			if ($str_key 		== "student28") {
				$student28		 = strtoupper($str_value);
			}
			if ($str_key 		== "student29") {
				$student29		 = strtoupper($str_value);
			}
			if ($str_key 		== "student30") {
				$student30		 = strtoupper($str_value);
			}
			if ($str_key 		== "student31") {
				$student31		 = strtoupper($str_value);
			}
			if ($str_key 		== "student32") {
				$student32		 = strtoupper($str_value);
			}
			if ($str_key 		== "student33") {
				$student33		 = strtoupper($str_value);
			}
			if ($str_key 		== "student34") {
				$student34		 = strtoupper($str_value);
			}
			if ($str_key 		== "student35") {
				$student35		 = strtoupper($str_value);
			}
			if ($str_key 		== "student36") {
				$student36		 = strtoupper($str_value);
			}
			if ($str_key 		== "student37") {
				$student37		 = strtoupper($str_value);
			}
			if ($str_key 		== "student38") {
				$student38		 = strtoupper($str_value);
			}
			if ($str_key 		== "student39") {
				$student39		 = strtoupper($str_value);
			}
			if ($str_key 		== "student40") {
				$student40		 = strtoupper($str_value);
			}
			if ($str_key 		== "student41") {
				$student41		 = strtoupper($str_value);
			}
			if ($str_key 		== "student42") {
				$student42		 = strtoupper($str_value);
			}
			if ($str_key 		== "student43") {
				$student43		 = strtoupper($str_value);
			}
			if ($str_key 		== "student44") {
				$student44		 = strtoupper($str_value);
			}
			if ($str_key 		== "student45") {
				$student45		 = strtoupper($str_value);
			}
			if ($str_key 		== "student46") {
				$student46		 = strtoupper($str_value);
			}
			if ($str_key 		== "student47") {
				$student47		 = strtoupper($str_value);
			}
			if ($str_key 		== "student48") {
				$student48		 = strtoupper($str_value);
			}
			if ($str_key 		== "student49") {
				$student49		 = strtoupper($str_value);
			}
			if ($str_key 		== "student50") {
				$student50		 = strtoupper($str_value);
			}
			if ($str_key 		== "student51") {
				$student51		 = strtoupper($str_value);
			}
			if ($str_key 		== "student52") {
				$student52		 = strtoupper($str_value);
			}
			if ($str_key 		== "student53") {
				$student53		 = strtoupper($str_value);
			}
			if ($str_key 		== "student54") {
				$student54		 = strtoupper($str_value);
			}
			if ($str_key 		== "student55") {
				$student55		 = strtoupper($str_value);
			}
			if ($str_key 		== "student56") {
				$student56		 = strtoupper($str_value);
			}
			if ($str_key 		== "student57") {
				$student57		 = strtoupper($str_value);
			}
			if ($str_key 		== "student58") {
				$student58		 = strtoupper($str_value);
			}
			if ($str_key 		== "student59") {
				$student59		 = strtoupper($str_value);
			}
			if ($str_key 		== "student60") {
				$student60		 = strtoupper($str_value);
			}
			if ($str_key 		== "student61") {
				$student61		 = strtoupper($str_value);
			}
			if ($str_key 		== "student62") {
				$student62		 = strtoupper($str_value);
			}
			if ($str_key 		== "student63") {
				$student63		 = strtoupper($str_value);
			}
			if ($str_key 		== "student64") {
				$student64		 = strtoupper($str_value);
			}
			if ($str_key 		== "student65") {
				$student65		 = strtoupper($str_value);
			}
			if ($str_key 		== "student66") {
				$student66		 = strtoupper($str_value);
			}
			if ($str_key 		== "student67") {
				$student67		 = strtoupper($str_value);
			}
			if ($str_key 		== "student68") {
				$student68		 = strtoupper($str_value);
			}
			if ($str_key 		== "student69") {
				$student69		 = strtoupper($str_value);
			}
			if ($str_key 		== "student70") {
				$student70		 = strtoupper($str_value);
			}
			if ($str_key 		== "student71") {
				$student71		 = strtoupper($str_value);
			}
			if ($str_key 		== "student72") {
				$student72		 = strtoupper($str_value);
			}
			if ($str_key 		== "student73") {
				$student73		 = strtoupper($str_value);
			}
			if ($str_key 		== "student74") {
				$student74		 = strtoupper($str_value);
			}
			if ($str_key 		== "student75") {
				$student75		 = strtoupper($str_value);
			}
			if ($str_key 		== "student76") {
				$student76		 = strtoupper($str_value);
			}
			if ($str_key 		== "student77") {
				$student77		 = strtoupper($str_value);
			}
			if ($str_key 		== "student78") {
				$student78		 = strtoupper($str_value);
			}
			if ($str_key 		== "student79") {
				$student79		 = strtoupper($str_value);
			}
			if ($str_key 		== "student80") {
				$student80		 = strtoupper($str_value);
			}
			if ($str_key 		== "student81") {
				$student81		 = strtoupper($str_value);
			}
			if ($str_key 		== "student82") {
				$student82		 = strtoupper($str_value);
			}
			if ($str_key 		== "student83") {
				$student83		 = strtoupper($str_value);
			}
			if ($str_key 		== "student84") {
				$student84		 = strtoupper($str_value);
			}
			if ($str_key 		== "student85") {
				$student85		 = strtoupper($str_value);
			}
			if ($str_key 		== "student86") {
				$student86		 = strtoupper($str_value);
			}
			if ($str_key 		== "student87") {
				$student87		 = strtoupper($str_value);
			}
			if ($str_key 		== "student88") {
				$student88		 = strtoupper($str_value);
			}
			if ($str_key 		== "student89") {
				$student89		 = strtoupper($str_value);
			}
			if ($str_key 		== "student90") {
				$student90		 = strtoupper($str_value);
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
		$content	.= "<p><strong>Operating in Test Mode.</strong></p>";
		if ($doDebug) {
			echo "<p><strong>Operating in Test Mode.</strong></p>";
		}
		$advisorTableName		= "wpw1_cwa_consolidated_advisor2";
		$advisorClassTableName	= "wpw1_cwa_consolidated_advisorclass2";
		$studentTableName		= "wpw1_cwa_consolidated_student2";
		$inp_mode				= 'tm';
	} else {
		$advisorTableName		= "wpw1_cwa_consolidated_advisor";
		$advisorClassTableName	= "wpw1_cwa_consolidated_advisorclass";
		$studentTableName		= "wpw1_cwa_consolidated_student";
		$inp_mode				= 'pd';
	}

// Functions


// Set up a function to process each of the non-blank student records
function processEachStudent($studentCallsign,$advisorCallsign,$assignedAdvisorClass,$theSemester,$studentCount) {

// returns array evaluation complete / evaluation incomplete, data to display

	global $wpdb, $doDebug, $testMode, $advisorTableName, $advisorClassTableName, $studentTableName, $jobname, $userName;
	
	if ($doDebug) {
		echo "FUNCTION: processEachStudent studentInfo: $studentCallsign; advisor: $advisorCallsign; class: $assignedAdvisorClass; semester: $theSemester; studentCount: $studentCount; studentTableName: $studentTableName<br />";
	}
	
// echo "have $studentCallSign<br />";

	$returnContent		= "";
	$returnMessage		= "";

	$sql				= "select * from $studentTableName 
						   where call_sign='$studentCallsign' 
						   and assigned_advisor='$advisorCallsign' 
						   and assigned_advisor_class=$assignedAdvisorClass 
						   and semester='$theSemester'";
	$wpw1_cwa_student	= $wpdb->get_results($sql);
	if ($wpw1_cwa_student === FALSE) {
		$myError			= $wpdb->last_error;
		$myQuery			= $wpdb->last_query;
		if ($doDebug) {
			echo "Reading $studentTableName table failed<br />
				  wpdb->last_query: $myQuery<br />
				  wpdb->last_error: $myError<br />";
		}
		$errorMsg			= "$jobname Reading $studentTableName failed. <p>SQL: $myQuery</p><p> Error: $myError</p>";
		sendErrorEmail($errorMsg);
		$returnMessage				= "PROGRAM ERROR: No student record found for $studentInfo<br />";
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

				$student_last_name 						= no_magic_quotes($student_last_name);
		
				$PChecked									= "";
				$NChecked									= "";
				$WChecked									= "";
				$EChecked									= "";
				if ($student_promotable == ' ') {
					$EChecked								= "checked ";
					$returnMessage							= "Evaluation Complete";
				} elseif ($student_promotable == 'P') {
					$PChecked								= "checked ";
					$returnMessage							= "Evaluation Complete";
				} elseif ($student_promotable == 'N') {
					$NChecked							 	= "checked ";
					$returnMessage							= "Evaluation Complete";
				} elseif ($student_promotable == 'W') {
					$WChecked							 	= "checked ";
					$returnMessage							= "Evaluation Complete";
				} else {
					$EChecked								= "checked ";
					$returnMessage							= "Evaluation Incomplete";
				}
				
				$returnContent		= "$student_last_name	, $student_first_name	 ($student_call_sign)<br />
										<input class='formInputButton' type='radio' name='student$studentCount' value='$student_ID|P' $PChecked/>Promotable<br />
										<input class='formInputButton' type='radio' name='student$studentCount' value='$student_ID|N' $NChecked/>Not Promotable<br />
										<input class='formInputButton' type='radio' name='student$studentCount' value='$student_ID|W' $WChecked/>Withdrew or Dropped<br />
										<input class='formInputButton' type='radio' name='student$studentCount' value='$student_ID|X' $EChecked />Not Evaluated<br /><br />";

			}
		} else {
			if ($doDebug) {
				echo "PROGRAM ERROR: No student record found for student = $studentCallsign<br />";
			}
			$returnMessage				= "PROGRAM ERROR: No student record found for $studentCallsign<br />";
		}
	}
	return array($returnMessage,$returnContent);
}


// set up a function to update the student table
function x_updateStudent($studentid,$promotable) {

	global $wpdb, $doDebug, $testMode, $advisorTableName, $advisorClassTableName, $studentTableName, $jobname, $userName;

	if ($promotable == 'X') {
		$promotable	= '';
	}

	if ($doDebug) {
		echo "FUNCTION: x_updateStudent: $studentid, promotable: $promotable, table: $studentTableName<br />";
	}
	$sql				= "select student_id, 
								  last_name, 
								  first_name, 
								  call_sign, 
								  semester, 
								  promotable, 
								  action_log, 
								  assigned_advisor 
						   from $studentTableName 
						   where student_id=$studentid";
	$wpw1_cwa_student	= $wpdb->get_results($sql);
	if ($wpw1_cwa_student === FALSE) {
		$myError			= $wpdb->last_error;
		$myQuery			= $wpdb->last_query;
		if ($doDebug) {
			echo "Reading $studentTableName table failed<br />
				  wpdb->last_query: $myQuery<br />
				  wpdb->last_error: $myError<br />";
		}
		$errorMsg			= "$jobname Reading $studentTableName failed. <p>SQL: $myQuery</p><p> Error: $myError</p>";
		sendErrorEmail($errorMsg);
	} else {
		$numPSRows									= $wpdb->num_rows;
		if ($doDebug) {
			$myStr			= $wpdb->last_query;
			echo "ran $myStr<br />and found $numPSRows rows in $studentTableName table<br />";
		}
		if ($numPSRows > 0) {
			foreach ($wpw1_cwa_student as $studentRow) {
				$student_ID							= $studentRow->student_id;
				$student_call_sign					= strtoupper($studentRow->call_sign);
				$student_first_name					= $studentRow->first_name;
				$student_last_name					= stripslashes($studentRow->last_name);
				$student_action_log					= $studentRow->action_log;
				$student_assigned_advisor  			= $studentRow->assigned_advisor;
				$student_promotable  				= $studentRow->promotable;
				$student_semester					= $studentRow->semester;

				$student_last_name 					= no_magic_quotes($student_last_name);

				if ($doDebug) {
					echo "Got a record. ID: $student_ID Name: $student_last_name, $student_first_name ($student_call_sign)<br />";
				}	
			}
			/// only update if the promotable status changes
			if ($student_promotable != $promotable) {
				$actionDate					= date('dMY H:i');
				$student_action_log 	= "$student_action_log / $actionDate EVALUATE $student_assigned_advisor updated promotable to $promotable ";
				$studentUpdateData		= array('tableName'=>$studentTableName,
												'inp_method'=>'update',
												'inp_data'=>array('promotable'=>$promotable,
																  'action_log'=>$student_action_log),
												'inp_format'=>array('%s','%s'),
												'jobname'=>$jobname,
												'inp_id'=>$student_ID,
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
//					$content		.= "Unable to update content in $studentTableName<br />";
					$returnArray	= array("Update failed for $studentid","UNKNOWN","NOK");
					return $returnArray;
				} else {
					if ($doDebug) {
						echo "Successfully updated $student_call_sign record at $student_ID<br />";
					}

					if ($promotable == 'P') {
						$valueName	= 'Promotable';
					} elseif ($promotable == 'N') {
						$valueName	= 'Not Promotable';
					} elseif ($promotable == 'W') {
						$valueName	= 'Withdrew or Dropped';
					} else {
						$valueName	= 'Not Evaluated';
					}
					$returnArray	= array("$student_last_name, $student_first_name ($student_call_sign)","$valueName","OK");
					return $returnArray;
				}
			} else {
				$returnArray	= array("$student_last_name, $student_first_name ($student_call_sign)","Unchanged","OK");
				return $returnArray;
			}
		} else {
			if ($doDebug) {
				echo "No record found for $studentid in student<br />";
			}
			$returnArray		= array("No student found with id $studentid","UNKNOWN","NOK");
			return $returnArray;
		}
	}
}


// function to read a student record and determine if it has been evaluated
// returns 0 if no student record found, 1 if not evaluated, or 2 if evaluated

function isStudentEvaluated($studentCallSign,$assignedAdvisor,$assignedAdvisorClass,$theSemester) {

	global $wpdb, $doDebug, $testMode, $advisorTableName, $advisorClassTableName, $studentTableName, $userName, $jobname;

	if ($doDebug) {
		echo "FUNCTION: isStudentEvaluated($studentCallSign,$assignedAdvisor,$assignedAdvisorClass,$theSemester)<br />";
	}
	$sql				= "select student_id, 
								  promotable 
						   from $studentTableName 
						   where call_sign='$studentCallSign' 
						   and assigned_advisor='$assignedAdvisor' 
						   and assigned_advisor_class=$assignedAdvisorClass 
						   and semester='$theSemester'";
	$wpw1_cwa_student	= $wpdb->get_results($sql);
	if ($wpw1_cwa_student === FALSE) {
		$myError			= $wpdb->last_error;
		$myQuery			= $wpdb->last_query;
		if ($doDebug) {
			echo "Reading $studentTableName table failed<br />
				  wpdb->last_query: $myQuery<br />
				  wpdb->last_error: $myError<br />";
		}
		$errorMsg			= "$jobname Reading $studentTableName failed. <p>SQL: $myQuery</p><p> Error: $myError</p>";
		sendErrorEmail($errorMsg);
		return array('0',$past_student_ID);
	} else {
		$numPSRows									= $wpdb->num_rows;
		if ($doDebug) {
			$myStr			= $wpdb->last_query;
			echo "ran $myStr<br />and found $numPSRows rows in $studentTableName table<br />";
		}
		if ($numPSRows > 0) {
			foreach ($wpw1_cwa_student as $studentRow) {
				$student_ID					= $studentRow->student_id;
				$student_promotable  		= $studentRow->promotable;
			}
			if ($doDebug) {
				echo "promotable for $student_ID is $student_promotable<br />";
			}
			if ($student_promotable != '') {
				return array('2',$student_ID);
			} else {
				return array('1',$student_ID);
			}
		}
	}
}		
		
// Function to handle setting class and student records either to 'Q' or to
// remove the 'Q'
// Input is the advisor's class id, whether to add the Q or remove the Q
// Returns an array of the result (OK, NOK) and any result message

function processQ($advisorClassID,$requestType) {

	global $wpdb, $doDebug, $testMode, $advisorTableName, $advisorClassTableName, $studentTableName, $jobname, $userName;

	if ($doDebug) {
		echo "FUNCTION: processQ($advisorClassID,$requestType)<br />";
	}


	$allOK					= TRUE;
	$resultMessage			= "";
	$myDate					= date('dMy h:i');
	
	$sql					= "select * from $sdvisorClassTableName 
							   where advisorclass_id=$advisorClassID";
	$wpw1__advisorclass	= $wpdb->get_results($sql);
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
		$allOK					= FALSE;
	} else {
		$numACRows				= $wpdb->num_rows;
		if ($doDebug) {
			$myStr				= $wpdb->last_query;
			echo "ran $myStr<br />and found $numACRows rows in $advisorClassTableName table<br />";
		}
		if ($numACRows > 0) {
			foreach ($wpw1_cwa_advisorclass as $advisorClassRow) {
				$advisorClass_ID				 		= $advisorClassRow->advisorclass_id;
				$advisorClass_advisor_callsign 			= $advisorClassRow->advisor_call_sign;
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
				$advisorClass_class_schedule_times_utc	= $advisorClassRow->class_schedule_times_utc;
				$advisorClass_action_log 				= $advisorClassRow->action_log;
				$advisorClass_class_incomplete 			= $advisorClassRow->class_incomplete;
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
				$class_number_students					= $advisorClassRow->number_students;
				$class_evaluation_complete 				= $advisorClassRow->evaluation_complete;
				$class_comments							= $advisorClassRow->class_comments;
				$copycontrol							= $advisorClassRow->copy_control;

				$advisorClass_advisor_last_name  		= no_magic_quotes($advisorClass_advisor_last_name);

				if ($doDebug) {
					echo "Processing class record $past_advisorClass_advisor_callsign<br />";
				}

// if setting Q, if evaluation_complete = Y, do nothing and go on to the next record
// otherwise set evaluation_complete to Q and process the students
// if removing Q, if evaluation_complete = Q, then set it to N and process the students
// otherwise do nothing with the students and go on to the next record
				$doStudents					= TRUE;
				if ($requestType == 'SetQ') {
					if ($class_evaluation_complete != 'Y' && $class_evaluation_complete != 'Q') {
						$classUpdateData		= array('tableName'=>$advisorClassTableName,
														'inp_method'=>'update',
														'inp_data'=>array('evaluation_complete'=>'Q'),
														'inp_format'=>array('%s'),
														'jobname'=>$jobname,
														'inp_id'=>$advisorClass_ID,
														'inp_callsign'=>$advisorClass_advisor_callsign,
														'inp_semester'=>$advisorClass_semester,
														'inp_who'=>$userName,
														'testMode'=>$testMode,
														'doDebug'=>$doDebug);
						$updateResult	= updateClass($classUpdateData);
						if ($updateResult[0] === FALSE) {
							$myError	= $wpdb->last_error;
							$mySql		= $wpdb->last_query;
							$errorMsg	= "A$jobname Processing $advisor_call_sign in $advisorClassTableName failed. Reason: $updateResult[1]<br />SQL: $mySql<br />Error: $myError<br />";
							if ($doDebug) {
								echo $errorMsg;
							}
							sendErrorEmail($errorMsg);
							$content		.= "Unable to update content in $advisorClassTableName<br />";
						} else {
							if ($doDebug) {
								echo "Successfully updated $advisorClassTableName record at $advisorClass_ID<br />";
							}							
						}
					} else {
						$doStudents			= FALSE;
						if ($doDebug) {
							echo "Setting Q but evaluate_advisor for class $advisorClass_post_title, sequence $advisorClass_sequence set to Y. Not doing students<br />";
						}
					}
					
					///// now update the advisor record's action log and the survey score to 6 (do not assign to teach)
					
					$sql					= "select advisor_id,
													  survey_score,
													  action_log,
													  semester 
											   from $advisorTableName 
											   where call_sign='$advisorClass_advisor_callsign' 
											   and semester='$advisorClass_semester'";
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
						$allOK			= FALSE;
					} else {
						$numACRows				= $wpdb->num_rows;
						if ($doDebug) {
							$myStr				= $wpdb->last_query;
							echo "ran $myStr<br />and found $numACRows rows in $advisorTableName table<br />";
						}
						if ($numACRows > 0) {
							foreach ($wpw1_cwa_advisor as $advisorRow) {
								$advisor_ID			 			= $advisorRow->advisor_id;
								$advisor_survey_score 			= $advisorRow->survey_score;
								$advisor_action_log 			= $advisorRow->action_log;
								$advisor_semester				= $advisorRow->semester;
					
					
								$advisor_action_log		= "$advisor_action_log / $myDate EVALUTE Advisor quit during the student evaluation process ";
								$advisorUpdateData		= array('tableName'=>$advisorTableName,
																'inp_method'=>'update',
																'inp_data'=>array('survey_score'=>6,'action_log'=>$advisor_action_log),
																'inp_format'=>array('%s','%s'),
																'jobname'=>$jobname,
																'inp_id'=>$advisor_ID,
																'inp_callsign'=>$advisorClass_advisor_callsign,
																'inp_semester'=>$advisorClass_semester,
																'inp_who'=>$userName,
																'testMode'=>$testMode,
																'doDebug'=>$doDebug);
								$updateResult	= updateAdvisor($advisorUpdateData);
								if ($updateResult[0] === FALSE) {
									$myError	= $wpdb->last_error;
									$mySql		= $wpdb->last_query;
									$errorMsg	= "$jobname Processing $advisor_call_sign in $advisorTableName failed. Reason: $updateResult[1]<br />SQL: $mySql<br />Error: $myError<br />";
									if ($doDebug) {
										echo $errorMsg;
									}
									sendErrorEmail($errorMsg);
									$content		.= "Unable to update content in $advisorTableName<br />";
								} else {
									if ($doDebug) {
										echo "Successfully updated $advisorClassTableName record at $past_advisorClass_ID<br />";
									}
								}
							}
						} else {
							if ($doDebug) {
								echo "MAJOR error: No $advisorTableName record found for class $advisorClass_post_title<br />";
							}
							$allOK				= FALSE;
							$resultMessage		= "No $advisorTableName record returned for $advisorClass_post_title";
						}
					}
				}
				
				
				
				//////// removeQ
				if ($requestType == 'RemoveQ') {
					if ($advisorClass_evaluation_complete == 'Q') {
						$advisorClass_action_log	= "$advisorClass_action_log / $myDate EVALUATE Removed the Q from the advisorClass record ";
						$classUpdateData		= array('tableName'=>$advisorClassTableName,
														'inp_method'=>'update',
														'inp_data'=>array('evaluation_complete'=>'N','action_log'=>$advisorClass_action_log),
														'inp_format'=>array('%s','%s'),
														'jobname'=>$jobname,
														'inp_id'=>$advisorClass_ID,
														'inp_callsign'=>$advisorClass_advisor_callsign ,
														'inp_semester'=>$advisorClass_semester,
														'inp_who'=>$userName,
														'testMode'=>$testMode,
														'doDebug'=>$doDebug);
						$updateResult	= updateClass($classUpdateData);
						if ($updateResult[0] === FALSE) {
							$myError	= $wpdb->last_error;
							$mySql		= $wpdb->last_query;
							$errorMsg	= "A$jobname Processing $advisor_call_sign in $advisorClassTableName failed. Reason: $updateResult[1]<br />SQL: $mySql<br />Error: $myError<br />";
							if ($doDebug) {
								echo $errorMsg;
							}
							sendErrorEmail($errorMsg);
							$content		.= "Unable to update content in $advisorClassTableName<br />";
						} else {
							if ($doDebug) {
								echo "Successfully updated $advisorClassTableName record at $advisorClass_ID<br />";
							}
						}
					}
				}
				if ($allOK && $doStudents) {

					// go through each of the students for the advisor's class
					// If setting Q and student's promotable is blank, set it to Q
					// If removing Q and student's promotable is Q, set it blank
	
					for ($snum=1;$snum<=$advisorClass_student;$snum++) {
						if ($snum < 10) {
							$strSnum 		= str_pad($snum,2,'0',STR_PAD_LEFT);
						} else {
							$strSnum		= strval($snum);
						}
						$theInfo			= ${'advisorClass_student' . $strSnum};
						if ($doDebug) {
							echo "The info for student$strSnum is $theInfo<br />";
						}
						if ($theInfo != '') {
							$studentCallSign	= $theInfo;
							$sql			=	"select student_id,
													    promotable,
													    action_log 
												  from $studentTableName 
												  where call_sign='$studentCallSign' 
														and assigned_advisor='$advisorClass_advisor_callsign' 
														and assigned_advisor_class=$advisorClass_advisor_sequence 
														and semester=$advisorClass_semester";
							$wpw1_cwa_student	= $wpdb->get_results($sql);
							if ($wpw1_cwa_student === FALSE) {
								$myError			= $wpdb->last_error;
								$myQuery			= $wpdb->last_query;
								if ($doDebug) {
									echo "Reading $studentTableName table failed<br />
										  wpdb->last_query: $myQuery<br />
										  wpdb->last_error: $myError<br />";
								}
								$errorMsg			= "$jobname Reading $studentTableName failed. <p>SQL: $myQuery</p><p> Error: $myError</p>";
								sendErrorEmail($errorMsg);
								$allOK			= FALSE;
							} else {
								$numPSRows									= $wpdb->num_rows;
								if ($doDebug) {
									$myStr			= $wpdb->last_query;
									echo "ran $myStr<br />and found $numPSRows rows in $studentTableName table<br />";
								}
								if ($numPSRows > 0) {
									foreach ($wpw1_cwa_student as $studentRow) {
										$student_ID							= $studentRow->student_id;
										$student_action_log  					= $studentRow->action_log;
										$student_promotable  					= $studentRow->promotable;
										if ($doDebug) {
											echo "Retrieved student record for id $studentID ($studentCallSign) and promotable=$studentPromotable<br />";
										}
										if ($requestType == "SetQ") {
											if ($studentPromotable == '') {
												$student_action_log 	= "$student_action_log / $myDate EVALUATE $callSign updated promotable to Q\n";
												$student_promotable	= 'Q';
											}
										}				// end set Q
										if ($requestType == "RemoveQ") {
											if ($studentPromotable == 'Q') {
												$student_action_log 	= "$student_action_log / $actionDate EVALUATE $callSign removed Q from promotable\n";
												$student_promotable	= '';
											}
										}
										$studentUpdateData		= array('tableName'=>$studentTableName,
																		'inp_method'=>'update',
																		'inp_data'=>array('promotable'=>$student_promotable,
																			  				'action_log'=>$student_action_log),
																		'inp_format'=>array('%s','%s'),
																		'jobname'=>$jobname,
																		'inp_id'=>$student_ID,
																		'inp_callsign'=>$studentCallSign,
																		'inp_semester'=>$advisorClass_semester,
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
											$resultMessage	= "Updated student id $student_ID ($studentCallSign) to remove Q failed";
											$allOK			= FALSE;
										} else {
											if ($doDebug) {
												echo "Successfully updated $studentTableName record at student_ID<br />";
											}
										}
									}					// end while fetch
								} else {
									$allOK			= FALSE;
									$resultMessage	.= "Getting record for studentid $studentID ($studentCallSign) returned $numberRecords records. Should have been at least 1.";
									if ($doDebug) {
										echo "No record returned for studentid $studentID<br />";
									}
								}
							}
						}						// end of theInfo loop
					}							// end for student loop
				}								// end allOK and processStudents
			}									// end while fetch class pod
		} else {								// no records from class pod
			if ($doDebug) {
				echo "Didn't get any class records<br />";
			}
			$allOK				= FALSE;
			$resultMessage		= "No class record returned";
		}
	}
	if ($allOK) {
		$returnArray		= array('OK','All OK');
	} else {
		$returnArray		= array('NOK',$resultMessage);
	}
	return $returnArray;
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
							<form method='post' action='$evaluateStudentURL' 
							name='selection_form' ENCTYPE='multipart/form-data'>
							<input type='hidden' name='strpass' value='2'>
							<input type='hidden' name='semester' value='$theSemester'>
							<input type='hidden' name='inp_mode' value='$inp_mode'>
							<table style='border-collapse:collapse;'>
							<tr><td style='width:150px;'>Advisor Call Sign</td>
							<td><input class='formInputText' type='text' maxlength='30' name='inp_call_sign' size='10' value='$inp_call_sign' autofocus></td></tr>
							$testModeOption
							<tr><td>&nbsp;</td><td><input class='formInputButton' type='submit' value='Submit' /></td></tr></table>
							</form>";
		}


///// Pass 2


	} elseif ("2" == $strPass) {
		if ($doDebug) {
			echo "Arrived at Pass 2 with inp_call_sign of $inp_call_sign<br />";
		}
		$validUser						= TRUE;
		// get the advisor record
		$sql				= "select email, 
									  phone 
							   from $advisorTableName 
							   where call_sign='$inp_call_sign' 
							   and semester='$theSemester'";
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
			$validUser						= FALSE;
		} else {	
			$numPARows				= $wpdb->num_rows;
			if ($doDebug) {
				$myStr				= $wpdb->last_query;
				echo "ran $myStr<br />and found $numPARows rows in $advisorTableName<br />";
			}
			if ($numPARows > 0) {
				foreach ($wpw1_cwa_advisor as $advisorRow) {
					$advisor_email 					= $advisorRow->email;
					$advisor_phone						= $advisorRow->phone;
				}
			}
		}
		if ($validUser) {	
			$haveClasses			= FALSE;			/// will turn true if at least one class found		
			$displayContent			= "";
			$totalIncomplete		= 0;
			$thisCount				= 9;
			$content				.= "<h3>Evaluate Students</h3>";
			// prepare to read the class pod for this advisor
			$currentSemester				= $initializationArray['currentSemester'];
			$prevSemester					= $initializationArray['prevSemester'];
			if ($currentSemester != 'Not in Session') {
				$theSemester				= $currentSemester;
			} else {
				$theSemester				= $prevSemester;
			}
			$sql							= "select * from $advisorClassTableName 
											   where semester='$theSemester' 
											   and advisor_call_sign='$inp_call_sign'";
			$wpw1_cwa_advisorclass	= $wpdb->get_results($sql);
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
				$content	.= "<p>No $advisorClassTableName table records for this advisor.</p>";
			} else {
				$numACRows				= $wpdb->num_rows;
				if ($doDebug) {
					$myStr				= $wpdb->last_query;
					echo "ran $myStr<br />and found $numACRows rows in $advisorClassTableName table<br />";
				}
				if ($numACRows > 0) {
					foreach ($wpw1_cwa_advisorclass as $advisorClassRow) {
						$advisorClass_ID				 		= $advisorClassRow->advisorclass_id;
						$advisorClass_advisor_callsign 			= $advisorClassRow->advisor_call_sign;
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
						$advisorClass_class_schedule_times_utc	= $advisorClassRow->class_schedule_times_utc;
						$advisorClass_action_log 				= $advisorClassRow->action_log;
						$advisorClass_class_incomplete 			= $advisorClassRow->class_incomplete;
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
						$class_number_students					= $advisorClassRow->number_students;
						$class_evaluation_complete 				= $advisorClassRow->evaluation_complete;
						$class_comments							= $advisorClassRow->class_comments;
						$copycontrol							= $advisorClassRow->copy_control;

						$advisorClass_advisor_last_name  		= no_magic_quotes($advisorClass_advisor_last_name);

						$evaluationIncompleteCount	= 0;
						$haveClasses				= TRUE;
						if ($doDebug) {
							echo "<br />Processing the $advisorClassTableName record for $advisorClass_advisor_callsign|$advisorClass_semester|$advisorClass_level|$advisorClass_sequence<br />
evaluationComplete: $class_evaluation_complete<br />";
						}
						if ($class_evaluation_complete == 'Q') {
							// first, before processing, set everything back to not Q
							$theResult	= processQ($advisorClass_ID,'RemoveQ');
							$isOK		= $theResult[0];
							$isMessage	= $theResult[1];
							if ($doDebug) {
								echo "Got results from SetQ. OK: $isOK, Message: $isMessage<br />";
							}
							if ($isOK == "OK") {
								$class_evaluation_complete	= 'N';
							} else {
								echo "ERROR Removing Q. Reason: $isMessage";
								exit;
							}
						}
						$displayContent			.= "<br /><fieldset><legend>Semester: $theSemester&nbsp;&nbsp;&nbsp;$advisorClass_level&nbsp;&nbsp;&nbsp;Sequence: $advisorClass_sequence</legend>";
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
								$returnContent		= processEachStudent($theInfo,$advisorClass_advisor_callsign, $advisorClass_sequence, $theSemester, $studentCount);
								if ($returnContent[1] != '') {
									$displayContent	.= $returnContent[1];
								}
								if ($returnContent[0] == "Evaluation Incomplete") {
									$evaluationIncompleteCount++;
								}
							} else {
								if ($doDebug) {
									echo "Processing student$strSnum, within number of students, info is empty<br />";
								}
							}
						}
						$displayContent					.= "</fieldset><br />";
					
					}	// end of foreach
					if ($doDebug) {
						echo "All records processed for this advisor. There are $totalIncomplete incomplete evaluations<br />";
					}
					if ($haveClasses) {
						$content 	.= "<p><strong>Instructions</strong>:<br />
										The students for your class or classes are listed below, including those previously evaluated (if any). You 
										can select the student status by clicking in the round circles below the 
										student's name next to the appropriate option. If you don't wish to enter an evaluation for a particular 
										student at this time, leave the student with the 'Not Evaluated' option selected.<br /><br />
										After you have completed your evaluations, click the 'Submit' button.<br /><br />
										You will continue to receive emails requesting student evaluations until all the students in 
										your class(es) have been evaluated.</p>
										<form method='post' action='$evaluateStudentURL'
										name='selection_form_a' ENCTYPE='multipart/form-data'>
										<input type='hidden' name='strpass' value='3'>
										<input type='hidden' name='inp_call_sign' value='$inp_call_sign'>
										<input type='hidden' name='semester' value='$theSemester'>
										<input type='hidden' name='inp_mode' value='$inp_mode'>
										<input type='hidden' name='inp_verbose' value='$inp_verbose'>
										<input type='hidden' name='advisor_email' value='$advisor_email'>
										<input type='hidden' name='advisor_phone' value='$advisor_phone'>
										<input type='hidden' name='token' value='$token'>
										$displayContent
										<div style='clear:both;'>
										<input class='formInputButton' type='submit' value='Next' />
										</div>";

					} else {
						$content	.= "<p>No classes found for advisor $inp-call_sign</p>";
					}
				} else {
					$content	.= "<p>No classes found for advisor $inp_call_sign</p>";
				}
			}
		} else {
			$content		.= "Validation Failed";
		}	
		
//////// 	Pass 3: Do student evaluations		
		
	} elseif ("3" == $strPass) {

		if ($doDebug) {
			echo "Arrived at pass 3<br />";
		}
		if ($userName == '') {
			$userName				= $inp_call_sign;
		}
		$content					.= "<h3>Evaluate Students</h3>";
// Go through each of the student records
		$prelimUnevaluatedCount		= 0;
		for ($snum=10;$snum<91;$snum++) {
			$strSnum			= strval($snum);
			$theInfo			= ${'student' . $strSnum};
			if ($doDebug) {
				echo "<br />Processing student$strSnum: $theInfo<br />";
			}
			if ($theInfo != '') {
				$studentArray		= explode("|",$theInfo);
				$studentID			= $studentArray[0];
				$studentValue		= $studentArray[1];
				$returnResult		= x_updateStudent($studentID,$studentValue);
				if ($doDebug) {
					echo "returnResult:<br /><pre>";
					print_r($returnResult);
					echo "</pre><br />";
				}
				$studentName		= $returnResult[0];
				$studentValue		= $returnResult[1];
				$updateStatus		= $returnResult[2];
				if ($updateStatus == "OK") {
					$content	.= "$studentName was set to $studentValue<br />";
				} else {
					$content	.= "Updating the student failed: $studentName<br />";
				}
			}
		}



// Now go look to see if the advisor has any unevaluated students.
// If so, say thank you and quit.
// If all have been evaluated, then give the advisor the opportunity to sign up for the next semester
		$sql					= "select email, 
										  phone 
									from $advisorTableName 
									where call_sign = '$inp_call_sign' 
										and semester='$theSemester'";
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
					$advisor_email 						= strtolower($advisorRow->email);
					$advisor_phone						= $advisorRow->phone;
				}
			} else {
				if ($doDebug) {
					echo "$inp_call_sign not found in $advisorTableName<br />";
				}
			}
		}

		if ($doDebug) {
			echo "Evaluations updated. Reviewing class records to see if advisor evaluation is complete<br />";
		}
		$totalUnevaluated		= 0;
		$content				.= "<h3>Evaluate Students</h3>";
		$sql					= "select * from $advisorClassTableName 
								   where advisor_call_sign='$inp_call_sign' 
								   and semester='$theSemester'";
		$wpw1_cwa_advisorclass	= $wpdb->get_results($sql);
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

					$advisorClass_advisor_last_name  		= no_magic_quotes($advisorClass_advisor_last_name);

					$evaluationIncompleteCount	= 0;

					if ($doDebug) {
						echo "Processing the advisorclass table record for $advisorClass_advisor_callsign|$advisorClass_semester|$advisorClass_level<br />";
					}
					for ($snum=1;$snum<=$class_number_students;$snum++) {
						if ($snum < 10) {
							$strSnum 		= str_pad($snum,2,'0',STR_PAD_LEFT);
						} else {
							$strSnum		= strval($snum);
						}
						$theInfo			= ${'advisorClass_student' . $strSnum};
						if ($theInfo != '') {
							$studentCallSign = $theInfo;
							$returnResult 	= isStudentEvaluated($studentCallSign,$advisorClass_advisor_callsign,$advisorClass_sequence,$theSemester);
							$theResult		= $returnResult[0];
							$studentid		= $returnResult[1];
							switch ($theResult) {
								case '0':
									if ($doDebug) {
										echo "isStudentEvaluated returned a 0: No student record found<br />";
									}
									$content .= "<br />ERROR: $student $studentCallSign with id $studentid not found in student records<br />";
									$evaluationIncompleteCount++;
									break;
								case '1':
									$content .= "Student $studentCallSign still needs evaluation.<br />";
									$evaluationIncompleteCount++;
									break;
							}
						}
					}

					// Have all evaluations for this class been completed? if so, mark it complete in the class record
					if ($evaluationIncompleteCount == 0) {
						$advisorClass_action_log	= "$advisorClass_action_log / $myDate EVALUATE Advisor evaluations complete for this class ";
						$advisorUpdateData		= array('tableName'=>$advisorClassTableName,
														'inp_method'=>'update',
														'inp_data'=>array('evaluation_complete'=>'Y',
															  				'action_log'=>$advisorClass_action_log),
														'inp_format'=>array('%s','%s'),
														'jobname'=>$jobname,
														'inp_id'=>$advisorClass_ID,
														'inp_callsign'=>$advisorClass_advisor_callsign,
														'inp_semester'=>$advisorClass_semester,
														'inp_who'=>$userName,
														'testMode'=>$testMode,
														'doDebug'=>$doDebug);
						$updateResult	= updateClass($advisorUpdateData);
						if ($updateResult[0] === FALSE) {
							$myError	= $wpdb->last_error;
							$mySql		= $wpdb->last_query;
							$errorMsg	= "$jobname Processing $advisorClass_advisor_call_sign in $advisorClassTableName failed. Reason: $updateResult[1]<br />SQL: $mySql<br />Error: $myError<br />";
							if ($doDebug) {
								echo $errorMsg;
							}
							sendErrorEmail($errorMsg);
							$content		.= "Unable to update content in $advisorTableName<br />";
						} else {
							if ($doDebug) {
								echo "Successfully updated $advisorClassTableName record at $advisorClass_ID<br />";
							}
						}
					} else {				// make sure that evaluations complete is set to N
						if ($class_evaluation_complete == 'Y') {
							$advisorClass_action_log	= "$advisorClass_action_log / $myDate EVALUATE Advisor evaluations are incomplete for this class ";
							$advisorUpdateData		= array('tableName'=>$advisorClassTableName,
															'inp_method'=>'update',
															'inp_data'=>array('evaluation_complete'=>'N',
																				'action_log'=>$advisorClass_action_log),
															'inp_format'=>array('%s','%s'),
															'jobname'=>$jobname,
															'inp_id'=>$advisorClass_ID,
															'inp_callsign'=>$advisorClass_advisor_callsign,
															'inp_semester'=>$advisorClass_semester,
															'inp_who'=>$userName,
															'testMode'=>$testMode,
															'doDebug'=>$doDebug);
							$updateResult	= updateAdvisor($advisorUpdateData);
							if ($updateResult[0] === FALSE) {
								$myError	= $wpdb->last_error;
								$mySql		= $wpdb->last_query;
								$errorMsg	= "$jobname Processing $advisor_call_sign in $advisorTableName failed. Reason: $updateResult[1]<br />SQL: $mySql<br />Error: $myError<br />";
								if ($doDebug) {
									echo $errorMsg;
								}
								sendErrorEmail($errorMsg);
								$content		.= "Unable to update content in $advisorTableName<br />";
							} else {
								if ($doDebug) {
									echo "Successfully updated $advisorClassTableName record at $advisorClass_ID<br />";
								}
							}
						}
					}
					$totalUnevaluated = $totalUnevaluated + $evaluationIncompleteCount;	
				} // end of the while statement
			
				// If all evaluations are complete, allow the advisor to sign up for the next semester
				if ($totalUnevaluated == 0) {
					// see if the advisor has already signed up for next semester. If not, allow the signup
					$sql			= "SELECT count(advisor_id) as advisor_count 
										from $advisorTableName 
										where semester='$nextSemester' 
										  and call_sign='$inp_call_sign'";
					$student_count	= $wpdb->get_var($sql);
					if ($student_count == 0) {
						$controlcode		= mt_rand();
						$stringToPass		= "inp_callsign=$inp_call_sign&inp_email=$advisor_email&inp_phone=$advisor_phone&controlcode=$controlcode&inp_mode=$inp_mode&semester=$nextSemester";
						$enstr				= base64_encode($stringToPass);
						$content	.= "<p>Thank you for completing the evaluations on all your students!</p>
										<p>Please click the 'Register' button below 
										to <strong>complete the advisor sign-up</strong> for the upcoming semester. </p>
										<p>If you choose <b>not</b> to teach in the upcoming semester, do not click on the 'Register' button, 
										just close this page. If you would like to teach again sometime in the future, please go to the CW Academy web page 
										and sign up again as an Advisor.<p>
										<p>You can update student promotability any time until one week after the end of the semester by 
										clicking again the link in the email you received or by clicking 
										<a href='$evaluateStudentURL'>Enter Student Evaluation</a>.</p>
										<p>Thank you very much for your service!<br />
										CW Academy</p> 
										<form method='post' action='$registerURL' 
										name='selection_form' ENCTYPE='multipart/form-data'>
										<input type='hidden' name='enstr' value='$enstr'>
										<input type='hidden' name='strpass' value='2'>
										<div style='clear:both;'>
										<input class='formInputButton' type='submit' value='Register' />
										</div></form>";
										
						// resolve the reminder
						if ($doDebug) {
							echo "now resolving the reminder for $inp_call_sign token $token<br />";
						}
						if ($token != '') {
							$resolveResult				= resolve_reminder($inp_call_sign,$token,$testMode,$doDebug);
							if ($resolveResult === FALSE) {
								if ($doDebug) {
									echo "resolve_reminder for $inp_call_sign and $token failed<br />";
								}
							}
						}

					} else {
						$content	.= "<p>Thank you for completing the evaluations on all your students.</p>
										<p>You are already registered for the $nextSemester semester. If you want to 
										update your registration information, please click 
										<a href='$registerURL'>Advisor Registration</a>
										<br /><br />
										Thank you very much for your service!<br />
										CW Academy </p><br /><br />
										You may close this window.";					
					}
				} else {
					$content 	.= "<p>There are still $totalUnevaluated student(s) to be evaluated 
									for your classes. In a few days you'll receive another email inviting you to complete your 
									evaluations.</p>
									<p>At any time you can evaluate your remaining students clicking  
									on the Action Request to evaluate your students on the Advisor Portal.</p>
									<p>If you are NOT planning on teaching the next semester, please do your students a favor and complete their evaluation. 
									We will remind you to complete your evaluations. After they are completed, don't register for the next semester.</p>
									<p>Thank you for your participation as an advisor!</p>";
				}		
		
			} else {			// no advisor records found. This is an error and shouldn't happen
				if ($doDebug) {
					echo "ERROR! No class records found for $inp_call_sign|$theSemester<br />";
				}
				$content	.= "<p>ERROR: No class records found. Process aborted.</p>";
			}			
		}


////// Pass 10 ... advisor has opted out
	
	} elseif ("10" == $strPass) {
		if ($doDebug) {
			echo "Arrived at Pass 10<br />";
		}



		$theResult	= processQ($inp_call_sign,'SetQ');
		
		$isOK		= $theResult[0];
		$isMessage	= $theResult[1];
		if ($doDebug) {
			echo "Got results from SetQ. OK: $isOK, Message: $isMessage<br />";
		}
		if ($isOK == "OK") {
			$content	.= "Thanks for your service and you will not get any more reminders.";
		} else {
			$content	.= "ERROR. Reason: $isMessage";
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
add_shortcode ('evaluate_student', 'evaluate_student_func');
