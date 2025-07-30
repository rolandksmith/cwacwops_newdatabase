function prepare_students_for_assignment_to_advisors_func() {

/* Prepare for Student Assignments to Advisor
 *
 * This function should be run before running the
 * function to assign students to advisors. The function 
 * does the following:
 *		Checks each student in the upcoming semester to see if the student has taken 
 *		a previous class.
 *			Carry forward the excluded advisor(s)
 *			if so and the student is promotable, add 1 to priority
 *			if so and the student is not promotable but is taking the
 *				same level a second time, add 1 to priority, mark the
 *				student's hold_reason_code with X and put the previous advisor 
 *				in the excluded_advisor field
 *			If so and the student is not promotable, but signing up to
 *				take the next higher class, send an email to the student
 *				explaining the situation, mark student's hold_reason_code 
 *				as H (hold), and put an H in intervention_required
 *			If so and the student's promotable = Q
 *				If signing up for the same class again, add 1 to priority and allow to take the class
 * 					Mark the student's hold_reason_code with X and put the previous advisor 
 *					in the excluded_advisor field
 *				Otherwise, put a Q in intervention_required and hold_reason_code.
 *			if so and the advisor marked the student as 'W' (withdrew)
 *				If signing up for the same class again, allow to take the class
 *				Otherwise, put a 'H' in intervetion_required and a 'W' in hold_reason_code
 *			if so and the advisor hasn't evaluated the student
 *				If signing up for the same class again, allow to take the class
 *				Otherwise, put a 'E' in intervetion_required and a 'H' in hold_reason_code
 *			if so and taking the same level class again, mark the stuent's hold_reason_code 
 *				with an 'X' and put the previous advisor in th exlcuded_advisor_field
 *
 *		Check each advisor to see if the advisor has taught the previous
 *		semester. If so, and evaluations are not complete, set the score to 9.
 *		Otherwise, set the score to 1. If the advisor has not taught in the
 *		previous semester, leave the score at 0.
 *
 * The function can be run in one of five modes:
 * 		TestNoUpdate		Test mode, run against student2 and advisor2
 *							No updates attempted
 *		TestUpdate			Test mode, run against student2 and advisor2
 *							Updates will be attempted against student2 and advisor2
 *		Production			Production mode. Run against student and advisor pods
 *							data will be updated in the student and advisor pods
 *		Send Test Emails	Run against student pod.
 *							Send emails to students with registration conflicts
 *								hold_reason_code == H, Q, W, E
 *								intervention_required == H, Q, H, H
 *							Emails sent to Bob Carter / Roland Smith
 *		Send Emails			Run against student pod.
 *							Send emails to students with registration conflicts
 *								hold_reason_code == H, Q, W, E
 *								intervention_required == H, Q, H, H
 *							Emails sent to student
 *		
 

 	modified 3Mar20 by Roland to change take_previous_class to hold_reason_code
		and class_completed_date to excluded_advisor
	modified 25Oct22 by Roland to accomodate new timezone table format
	modified 16Apr23 by Roland to fix action_log
	modified 13Jul23 by Roland to use consolidated tables
	Modified 21Oct24 by Roland for new database
 
 */
 
 	global $wpdb;
 
	$doDebug						= FALSE;
	$initializationArray 			= data_initialization_func();
	if ($doDebug) {
		echo "Initialization Array:<br /><pre>";
		print_r($initializationArray);
		echo "</pre><br />";
	}
	$validUser 			= $initializationArray['validUser'];
	$userName			= $initializationArray['userName'];
	$currentDate		= $initializationArray['currentDate'];
	$validTestmode		= $initializationArray['validTestmode'];
	$currentTimestamp	= $initializationArray['currentTimestamp'];
	$siteURL			= $initializationArray['siteurl'];

	if ($userName == '') {
		return "YOU'RE NOT AUTHORIZED!<br />Goodby";
	}
//	if ($doDebug) {
		ini_set('display_errors','1');
		error_reporting(E_ALL);	
		ini_set('memory_limit','256M');
		ini_set('max_execution_time',0);
//	}

/// get the time that the process started
	$startingMicroTime			= microtime(TRUE);

	$testEmail			= 'rolandksmith@gmail.com';
//	$testEmail			= 'kcgator@gmail.com';
	$jobname			= 'Prepare Students for Assignment to Advisors';
	$strPass			= "1";
	$emailErrors		= 0;
	$emailCount			= 0;
	$inp_request_type	= '';
	$actionDate			= date('dMY H:i', $currentTimestamp);
	$nextSemester		= $initializationArray['nextSemester'];
	$anomolyCount		= 0;
	$updateCount		= 0;
	$advisorCount		= 0;
	$advisorEvalOK		= 0;
	$advisorEvalNOK		= 0;
	$promSameClass		= 0;
	$npromSameClass		= 0;
	$npromHigherClass	= 0;
	$qSameClass			= 0;
	$qHigherClass		= 0;
	$wSameClass			= 0;
	$wHigherClass		= 0;
	$neSameClass		= 0;
	$neHigherClass		= 0;
	$increment			= 0;
	$invalidYouth		= 0;
	$carryForward		= 0;
	$priorityUp			= 0;
	$noCatalogCount		= 0;
	$logDate			= date('Y-m-d H:i',$currentTimestamp);
	$fieldTest			= array('action_log','post_status','post_title','control_code');
	$semesterConversion		= array('Jan/Feb'=>1,'Apr/May'=>2,'May/Jun'=>2,'Sep/Oct'=>3,'SEP/OCT'=>3,'JAN/FEB'=>1,'APR/MAY'=>2);
	$currentSemester	= $initializationArray['currentSemester'];
	$prevSemester		= $initializationArray['prevSemester'];
	$semesterTwo		= $initializationArray['semesterTwo'];
	$semesterThree		= $initializationArray['semesterThree'];
	$semesterFour		= $initializationArray['semesterFour'];
	$theURL				= "$siteURL/cwa-prepare-students-for-assignment-to-advisors/";
	$studentManagementURL = "$siteURL/cwa-student-management/";
	$studentUpdateURL	= "$siteURL/cwa-display-and-update-student-signup-information/";
	$advisorUpdateURL	= "$siteURL/cwa-display-and-update-advisor-signup-information/";
	$errorArray			= array();

// get the input information
	if (isset($_REQUEST)) {
		foreach($_REQUEST as $str_key => $str_value) {
			if ($doDebug) {
				echo "Key: $str_key | Value: $str_value <br />\n";
			}
			if ($str_key 		== "strpass") {
				$strPass		 = $str_value;
				$strPass		 = filter_var($strPass,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "request_type") {
				$inp_request_type	 = $str_value;
				$inp_request_type	 = filter_var($inp_request_type,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "inp_report") {
				$inp_report	 = $str_value;
				$inp_report	 = filter_var($inp_report,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "inp_show") {
				$inp_show	 = $str_value;
				$inp_show	 = filter_var($inp_show,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "inp_verbose") {
				$inp_verbose	 = $str_value;
				$inp_verbose	 = filter_var($inp_verbose,FILTER_UNSAFE_RAW);
				if ($inp_verbose == 'Y') {
					$doDebug	= TRUE;
				}
			}
		}
	}


function excludeAnAdvisor($nowExcluded='',$toBeExcluded='') {

/*	to be supplied: 	nowExcluded: current excluded_advisor field
						toBeExcluded: the advisor to be excluded
						
	returns:			array(TRUE/FALSE,Resulting excluded_advisor field)
	
	checks to see if the toBeExcluded advisor is already excluded. If not, adds the
	toBeExcluded advisor to the list. 
	
*/
	if ($toBeExcluded == '') {
		return array(FALSE,"input data missing");
	}
	if ($toBeExcluded != 'AC6AC') {
		$myInt = strpos($nowExcluded,$toBeExcluded);
		if ($myInt === FALSE) {
			if ($nowExcluded == '') {
				$nowExcluded = $toBeExcluded;
			} else {
				$nowExcluded .= "|$toBeExcluded";
			}
		}
	}
	return array(TRUE,$nowExcluded);
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
				
				table{font:'Times New Roman', sans-serif;background-image:none;}
				
				th {color:#ffff;background-color:#000;padding:5px;font-size:small;}
				
				td {padding:5px;font-size:small;}
				</style>";	

	if ("1" == $strPass) {
		if ($doDebug) {
			echo "<br />Function starting.<br />";
		}
	if (in_array($userName,$validTestmode)) {			// give option to run in test mode 
		$testModeOption	= "<tr><td>Verbose Debugging?<br />
									<input type='radio' class='formInputButton' name='inp_verbose' value='N' checked='checked'> Standard Output<br />
									<input type='radio' class='formInputButton' name='inp_verbose' value='Y'> Turn on Debugging </td></tr>";
	} else {
		$testModeOption	= '';
	}
	$content			.= "<h3>$jobname</h3>
							<h4>Please Read the Following and Verify Before Submitting the Job</h4>
							<p>This function must be run before running the function to assign students to advisors.<p> 
							<p>If the function is being run in 'TestNoUpdate', 'TestUpdate', or 'Production' the function 
							does the following:</p>
							<table>
							<tr><td colspan='3'>If field no_catalog is Y then sets no_catalog to blank. This field will be 
												used to indicate if a student was arbitrarily assigned</td></tr>
							<tr><td colspan='3'>For each student enrolled in the upcoming semester, 
							check the past_student pod to see if the student has taken a previous class.</td></tr>
							<tr><td>&nbsp;</td><td colspan='2'>if so and the student is promotable, add 1 to priority</td></tr>
							<tr><td>&nbsp;</td><td colspan='2'>If so, the student is promotable, and taking the same class again</td></tr>
							<tr><td>&nbsp;</td><td style='width:100px;'>&nbsp;</td><td>add 1 to priority</td></tr>
							<tr><td>&nbsp;</td><td>&nbsp;</td><td>mark the student's hold_reason_code with X</td></tr>
							<tr><td>&nbsp;</td><td>&nbsp;</td><td>put the previous advisor in the excluded_advisor field</td></tr>
							<tr><td>&nbsp;</td><td colspan='2'>if so and the student is not promotable but is taking the
							same level a second time, </td></tr>
							<tr><td>&nbsp;</td><td>&nbsp;</td><td>add 1 to priority</td></tr>
							<tr><td>&nbsp;</td><td>&nbsp;</td><td>mark the student's hold_reason_code with X</td></tr>
							<tr><td>&nbsp;</td><td>&nbsp;</td><td>put the previous advisor in the excluded_advisor field</td></tr>
							<tr><td>&nbsp;</td><td colspan='2'>If so and the student is not promotable, but signing up to 
							take the next higher class</td></tr>
							<tr><td>&nbsp;</td><td>&nbsp;</td><td>mark student's hold_reason_code as H (hold)</td></tr>
							<tr><td>&nbsp;</td><td>&nbsp;</td><td>and put an H in intervention_required</td></tr>
							<tr><td>&nbsp;</td><td colspan='2'>If so and the student's promotable = Q</td></tr>
							<tr><td>&nbsp;</td><td>&nbsp;</td><td>If signing up for the same class again, add 1 to priority 
							and allow to take the class</td></tr>
							<tr><td>&nbsp;</td><td>&nbsp;</td><td>Mark the student's hold_reason_code with X and put the 
							previous advisor in the excluded_advisor field</td></tr>
							<tr><td>&nbsp;</td><td>&nbsp;</td><td>Otherwise, put a Q in intervention_required and hold_reason_code.</td></tr>
							<tr><td>&nbsp;</td><td colspan='2'>if so and the advisor marked the student as 'W' (withdrew)</td></tr>
							<tr><td>&nbsp;</td><td>&nbsp;</td><td>If signing up for the sameclass again, allow to take the class</td></tr>
							<tr><td>&nbsp;</td><td>&nbsp;</td><td>Otherwise, put a 'H' in intervetion_required and a 'W' in hold_reason_code</td></tr>
							<tr><td>&nbsp;</td><td colspan='2'>if so and the advisor hasn't evaluated the student</td></tr>
							<tr><td>&nbsp;</td><td>&nbsp;</td><td>If signing up for the sameclass again, allow to take the class</td></tr>
							<tr><td>&nbsp;</td><td>&nbsp;</td><td>Otherwise, put a 'E' in intervention_required and a 'H' in hold_reason_code</td></tr>
							<tr><td colspan='3'><hr></td></tr>
							<tr><td colspan='3'>Check each advisor to see if the advisor has taught the previous semester</td></tr>
							<tr><td>&nbsp;</td><td colspan='2'>If so, and evaluations are not complete</td></tr>
							<tr><td>&nbsp;</td><td>&nbsp;</td><td>set the score to 9</td></tr>
							<tr><td>&nbsp;</td><td>&nbsp;</td><td>Otherwise, set the score to 1.</td></tr>
							<tr><td>&nbsp;</td><td colspan='2'>If the advisor has not taught in the previous semester
							<tr><td>&nbsp;</td><td>&nbsp;</td><td>leave the score at 0.</td></tr>
							</table>
							<p>If the function is being run in 'Send Email' mode, the job goes through the verified students 
							and sends a registration conflict email to students where:<br />
							The hold_reason_code = H and intervention_required = H (not promotable, registered for higher class)<br />
							The hold_reason_code = Q and intervention_required = Q (advisor quit, registered for higher class)<br />
							The hold_reason_code = W and intervention_required = H (student withdrew, registered for higher class)<br />
							The hold_reason_code = E and intervention_required = H (student not evaluated, registered for higher class)</p>
							<p>The function can be run in one of five modes:<br />
							<dl>
							<dt>TestNoUpdate</dt>
							<dd>Test mode, run against student2 and advisor2; No updates attempted.</dd>
							<dt>TestUpdate</dt>
							<dd>Test mode, run against student2 and advisor2; Updates will be attempted 
							against student2 and advisor2.</dd>
							<dt>Send Test Emails</dt>
							<dd>Run against student2 pod. Conflict emails sent to Bob Carter / Roland Smith.</dd>
							<dt>Production</dt>
							<dd>Production mode. Run against student and advisor pods. Data will be updated in 
							the student and advisor pods.</dd>
							<dt>Send Emails</dt>
							<dd>Run against student pod. Students with registration conflicts are sent a 
							conflict email.</dd>
							</dl>
							<h3>Click Submit to Start the Process</h3>
							<p><form method='post' action='$theURL' 
							name='selection_form' ENCTYPE='multipart/form-data''>
							<input type='hidden' name='strpass' value='2'>
							<table>
							<tr><td style='width:200px;'><input type='radio' class='formInputButton' name='request_type' value='TestNoUpdate'>Test with No Update</td></tr>
							<tr><td><input type='radio' name='request_type' value='TestUpdate' checked='checked'>Test with Update</td></tr>
							<tr><td><input type='radio' name='request_type' value='SendTestEmail'>Send Test Email</td></tr>
							<tr><td><input type='radio' name='request_type' value='Production'>Production</td></tr>
							<tr><td><input type='radio' name='request_type' value='SendEmail'>Send Email</td></tr>
							<tr><td>Anomalies to display:<br />
									<input type='radio' name='inp_show' value='serious' checked='checked'> Only display significant anomalies<br />
									<input type='radio' name='inp_show' value='all'> Display all anomalies</td></tr>
							$testModeOption
							</table>
							<input class='formInputButton' type='submit' value='Submit' />
							</form>";

///// Pass 2 -- do the work

	} elseif ("2" == $strPass) {
		if ($doDebug) {
			echo "<br />at pass 2<br />Request Type: $inp_request_type<br />";
		}

	
		$content 						.= "<h3>$jobname</h3>";
		if ($inp_request_type == "TestNoUpdate") {
			$studentTableName			= "wpw1_cwa_student2";
			$advisorTableName			= "wpw1_cwa_advisor2";
			$advisorClassTableName		= "wpw1_cwa_advisorclass2";
			$userMasterTableName		= 'wpw1_cwa_user_master2';
			$updateMode					= FALSE;
			$testMode					= TRUE;
			$sendEmail					= FALSE;
			$doAdvisors					= TRUE;
			$content					.= "<p>System is running in TestMode No Updates</p><h5>The Function Found the Following Anomalies</h5>";
		} elseif ($inp_request_type == "TestUpdate") {
			$studentTableName			= "wpw1_cwa_student2";
			$advisorTableName			= "wpw1_cwa_advisor2";
			$advisorClassTableName		= "wpw1_cwa_advisorclass2";
			$userMasterTableName		= 'wpw1_cwa_user_master2';
			$updateMode					= TRUE;
			$testMode					= TRUE;
			$sendEmail					= FALSE;
			$doAdvisors					= TRUE;
			$content					.= "<p>System is running in TestMode With Updates</p><h5>The Function Found the Following Anomalies</h5>";
		} elseif ($inp_request_type == "Production") {
			$studentTableName			= "wpw1_cwa_student";
			$advisorTableName			= "wpw1_cwa_advisor";
			$advisorClassTableName		= "wpw1_cwa_advisorclass";
			$userMasterTableName		= 'wpw1_cwa_user_master';
			$testMode					= FALSE;
			$updateMode					= TRUE;
			$sendEmail					= FALSE;
			$doAdvisors					= TRUE;
			$content					.= "<p>System is running in Production with Updates</p><h5>The Function Found the Following Anomalies</h5>";
		} elseif ($inp_request_type == 'SendTestEmail') {
			$studentTableName			= "wpw1_cwa_student2";
			$advisorTableName			= "wpw1_cwa_advisor2";
			$advisorClassTableName		= "wpw1_cwa_advisorclass2";
			$userMasterTableName		= 'wpw1_cwa_user_master2';
			$testMode					= TRUE;
			$updateMode					= TRUE;
			$sendEmail					= TRUE;
			$doAdvisors					= TRUE;
			$content					.= "<p>System is running in TestMode Sending Test Emails Only</p><h5>Sending the Following Emails</h5>";
		} elseif ($inp_request_type == 'SendEmail') {
			$studentTableName			= "wpw1_cwa_student";
			$advisorTableName			= "wpw1_cwa_advisor";
			$advisorClassTableName		= "wpw1_cwa_advisorclass";
			$userMasterTableName		= 'wpw1_cwa_user_master';
			$testMode					= FALSE;
			$updateMode					= FALSE;
			$sendEmail					= TRUE;
			$doAdvisors					= FALSE;
			$content					.= "<h5>Sending the Following Production Emails</h5>";
		} else {
			$content					.= "Invalid request type. Process aborted.";
			return $content;
			exit;
		}
		if ($doDebug) {
			echo "StudentTableName: $studentTableName<br />
				  AdvisorTableName: $advisorTableName<br />
				  Logicals: testMode: $testMode<br >
				  updateMode: $updateMode<br />
				  sendEmail: $sendEmail<br />
				  doAdvisor: $doAdvisors<br />";
		}
		$content	.= "<h4>Processing Students</h4>";
		// get the student table for the next semester and process each student

		$sql				= "select * from $studentTableName 
								left join $userMasterTableName on user_call_sign = student_call_sign 
							   where student_semester='$nextSemester' 
							   	and (student_response='Y' or student_response='')  
							   order by student_call_sign ";
		$wpw1_cwa_student		= $wpdb->get_results($sql);
		if ($wpw1_cwa_student === FALSE) {
			handleWPDBError($jobname,$doDebug);
		} else {
			$numSRows			= $wpdb->num_rows;
			if ($doDebug) {
				echo "ran $sql<br />and found $numSRows rows<br />";
			}
			if ($numSRows > 0) {
				foreach ($wpw1_cwa_student as $studentRow) {
					$student_master_ID 					= $studentRow->user_ID;
					$student_master_call_sign 			= $studentRow->user_call_sign;
					$student_first_name 				= $studentRow->user_first_name;
					$student_last_name 					= $studentRow->user_last_name;
					$student_email 						= $studentRow->user_email;
					$student_phone 						= $studentRow->user_phone;
					$student_city 						= $studentRow->user_city;
					$student_state 						= $studentRow->user_state;
					$student_zip_code 					= $studentRow->user_zip_code;
					$student_country_code 				= $studentRow->user_country_code;
					$student_whatsapp 					= $studentRow->user_whatsapp;
					$student_telegram 					= $studentRow->user_telegram;
					$student_signal 					= $studentRow->user_signal;
					$student_messenger 					= $studentRow->user_messenger;
					$student_master_action_log 			= $studentRow->user_action_log;
					$student_timezone_id 				= $studentRow->user_timezone_id;
					$student_languages 					= $studentRow->user_languages;
					$student_survey_score 				= $studentRow->user_survey_score;
					$student_is_admin					= $studentRow->user_is_admin;
					$student_role 						= $studentRow->user_role;
					$student_master_date_created 		= $studentRow->user_date_created;
					$student_master_date_updated 		= $studentRow->user_date_updated;

					$student_ID								= $studentRow->student_id;
					$student_call_sign						= $studentRow->student_call_sign;
					$student_time_zone  					= $studentRow->student_time_zone;
					$student_timezone_offset				= $studentRow->student_timezone_offset;
					$student_youth  						= $studentRow->student_youth;
					$student_age  							= $studentRow->student_age;
					$student_parent 				= $studentRow->student_parent;
					$student_parent_email  					= strtolower($studentRow->student_parent_email);
					$student_level  						= $studentRow->student_level;
					$student_waiting_list 					= $studentRow->student_waiting_list;
					$student_request_date  					= $studentRow->student_request_date;
					$student_semester						= $studentRow->student_semester;
					$student_notes  						= $studentRow->student_notes;
					$student_welcome_date  					= $studentRow->student_welcome_date;
					$student_email_sent_date  				= $studentRow->student_email_sent_date;
					$student_email_number  					= $studentRow->student_email_number;
					$student_response  						= strtoupper($studentRow->student_response);
					$student_response_date  				= $studentRow->student_response_date;
					$student_abandoned  					= $studentRow->student_abandoned;
					$student_status  						= strtoupper($studentRow->student_status);
					$student_action_log  					= $studentRow->student_action_log;
					$student_pre_assigned_advisor  			= $studentRow->student_pre_assigned_advisor;
					$student_selected_date  				= $studentRow->student_selected_date;
					$student_no_catalog  					= $studentRow->student_no_catalog;
					$student_hold_override  				= $studentRow->student_hold_override;
					$student_assigned_advisor  				= $studentRow->student_assigned_advisor;
					$student_advisor_select_date  			= $studentRow->student_advisor_select_date;
					$student_advisor_class_timezone 		= $studentRow->student_advisor_class_timezone;
					$student_hold_reason_code  				= $studentRow->student_hold_reason_code;
					$student_class_priority  				= $studentRow->student_class_priority;
					$student_assigned_advisor_class 		= $studentRow->student_assigned_advisor_class;
					$student_promotable  					= $studentRow->student_promotable;
					$student_excluded_advisor  				= $studentRow->student_excluded_advisor;
					$student_survey_completion_date			= $studentRow->student_survey_completion_date;
					$student_available_class_days  			= $studentRow->student_available_class_days;
					$student_intervention_required  		= $studentRow->student_intervention_required;
					$student_copy_control  					= $studentRow->student_copy_control;
					$student_first_class_choice  			= $studentRow->student_first_class_choice;
					$student_second_class_choice  			= $studentRow->student_second_class_choice;
					$student_third_class_choice  			= $studentRow->student_third_class_choice;
					$student_first_class_choice_utc  		= $studentRow->student_first_class_choice_utc;
					$student_second_class_choice_utc  		= $studentRow->student_second_class_choice_utc;
					$student_third_class_choice_utc  		= $studentRow->student_third_class_choice_utc;
					$student_catalog_options				= $studentRow->student_catalog_options;
					$student_flexible						= $studentRow->student_flexible;
					$student_date_created 					= $studentRow->student_date_created;
					$student_date_updated			  		= $studentRow->student_date_updated;

					// if you need the country name and phone code, include the following
					$countrySQL		= "select * from wpw1_cwa_country_codes  
										where country_code = '$student_country_code'";
					$countrySQLResult	= $wpdb->get_results($countrySQL);
					if ($countrySQLResult === FALSE) {
						handleWPDBError($jobname,$doDebug);
						$student_country		= "UNKNOWN";
						$student_ph_code		= "";
					} else {
						$numCRows		= $wpdb->num_rows;
						if ($doDebug) {
							echo "ran $countrySQL<br />and retrieved $numCRows rows<br />";
						}
						if($numCRows > 0) {
							foreach($countrySQLResult as $countryRow) {
								$student_country		= $countryRow->country_name;
								$student_ph_code		= $countryRow->ph_code;
							}
						} else {
							$student_country			= "Unknown";
							$student_ph_code			= "";
						}
					}

				
					$studentUpdateData			= "<a href='$studentUpdateURL?request_type=callsign&request_info=$student_call_sign&inp_depth=one&doDebug=$doDebug&testMode=$testMode&strpass=2' target='_blank'>$student_call_sign</a>";
					$possContent				= "<br />Processing student $student_last_name, $student_first_name ($studentUpdateData)<br />
													&nbsp;&nbsp;&nbsp;Youth: $student_youth; age: $student_age<br />
													&nbsp;&nbsp;&nbsp;Requesting a $student_level class<br />";
					$updateLog					= "";
					$doContent					= FALSE;
					$updateStudent				= FALSE;
					$carryForwardExclAdvisor	= '';
					$newExclAdvisor				= '';
					$studentUpdateData			= "<a href='$studentUpdateURL?request_type=callsign&request_info=$student_call_sign&inp_depth=one&doDebug=$doDebug&testMode=$testMode&strpass=2' target='_blank'>$student_call_sign</a>";
					if (!$sendEmail) {					// do the process, no emails sent
						$studentUpdateParams	= array();	
						$studentUpdateFormat	= array();			
						if ($doDebug) {
							echo "<br />Processing student $student_last_name, $student_first_name ($student_call_sign)<br />
								   &nbsp;&nbsp;&nbsp;Youth: $student_youth; age: $student_age<br />
								   &nbsp;&nbsp;&nbsp;Requesting a $student_level class<br />
								   &nbsp;&nbsp;&nbsp;Intervention Required: $student_intervention_required<br />
								   &nbsp;&nbsp;&nbsp;Hold Override: $student_hold_override<br />";
						}
						/// if student says is youth, must have an age and be 20 or less
						if ($student_youth == 'Yes' || $student_youth == 'Y') {
							if ($student_age == '') {
								$studentUpdateParams[]			= "student_youth|N|s";
								$updateStudent					= TRUE;
								$updateLog						.= " / student says is a youth, age not given and youth set to no";
								if ($doDebug) {
									echo "&nbsp;&nbsp;&nbsp;Youth = Yes; Student age not given, setting youth to No<br />";
								}
								$possContent		.= "&nbsp;&nbsp;&nbsp;Youth = Yes; age not given. Set youth to No<br />";
								$invalidYouth++;
							} else {
								if ($student_age > 20) {
									$studentUpdateParams[]			= "student_youth|N|s";
									$updateLog						.= " / student age over 20, setting youth to No";
									$updateStudent					= TRUE;
									if ($doDebug) {
										echo "&nbsp;&nsp;&nbsp;Youth = Yes; age over 20. Setting youth to No<br />";
									}
									$possContent		.= "&nbsp;&nbsp;&nbsp;Youth = Yes; age over 20. Set youth to No<br />";
									$invalidYouth++;
								}
							}
						}
						if ($student_no_catalog == 'Y') {
							if ($doDebug) {
								echo "setting no_catalog to blank<br />";
							}
							$studentUpdateParams[]					= "student_no_catalog||s";
							$updateLog								.= " removed no_catalog entry ";
							$possContent							.= "&nbsp;&nbsp;&nbsp;removed no_catalog entry<br >";
							$updateStudent							= TRUE;
							$noCatalogCount++;
						}						
						if ($student_hold_override == 'Y') {
							if ($doDebug) {
								echo "&nbsp;&nbsp;&nbsp;Hold Override set. Bypassing student<br />";
							}
						} else {
							if ($student_intervention_required != '') {
								if ($doDebug) {
									echo "&nbsp;&nbsp;&nbsp;Student on hold. Not being processed<br />";
								}
								$possContent	.= "&nbsp;&nbsp;&nbsp;Student on hold. Not being processed<br />";
								$doContent		= FALSE;
							} else {

							// See if student has past student records
								if ($doDebug) {
									echo "checking for previous semester records<br />";
								}
								$theStudentStatus			= '';
								$thePromotable				= "";
								$theLevel					= "";
								$semesterTest				= "";
								$sql						= "select * from $studentTableName 
																where student_call_sign='$student_call_sign'
																and student_semester != '$currentSemester' 
																and student_semester != '$nextSemester' 
																and student_semester != '$semesterTwo' 
																and student_semester != '$semesterThree' 
																order by student_date_created";
								$wpw1_cwa_student	= $wpdb->get_results($sql);
								if ($wpw1_cwa_student === FALSE) {
									handleWPDBError($jobname,$doDebug);
								} else {
									$numPSRows									= $wpdb->num_rows;
									if ($doDebug) {
										echo "ran $sql<br />and found $numPSRows rows in $studentTableName table<br />";
									}
									if ($numPSRows > 0) {
										$myExcludedAdvisors								= array();
										foreach ($wpw1_cwa_student as $studentRow) {
											$pastStudent_ID							= $studentRow->student_id;
											$pastStudent_call_sign					= trim(strtoupper($studentRow->student_call_sign));
											$pastStudent_level  					= $studentRow->student_level;
											$pastStudent_semester					= $studentRow->student_semester;
											$pastStudent_response  					= strtoupper($studentRow->student_response);
											$pastStudent_status  					= strtoupper($studentRow->student_status);
											$pastStudent_assigned_advisor			= $studentRow->student_assigned_advisor;
											$pastStudent_promotable  				= $studentRow->student_promotable;
											$pastStudent_excluded_advisor  			= $studentRow->student_excluded_advisor;

											if ($doDebug) {
												echo "&nbsp;&nbsp;&nbsp;Checking $pastStudent_semester. Response: $pastStudent_response; Status: $pastStudent_status; Promotable: $pastStudent_promotable<br />";
											}
											if ($pastStudent_excluded_advisor != '') {		// carry forward the do not assign
												if ($doDebug) {
													echo "student $student_call_sign has $pastStudent_excluded_advisor exlcuded advisors for semester $pastStudent_semester<br />";
												}
												$myArray								= explode("|",$pastStudent_excluded_advisor);
												foreach($myArray as $thisAdvisor) {
													if (!in_array($thisAdvisor,$myExcludedAdvisors)) {
														$myExcludedAdvisors[]			= $thisAdvisor;
														if ($doDebug) {
															echo "added $thisAdvisor to myExcludedAdvisors<br />";
														}
													}
												}
											}
											if ($pastStudent_status == 'Y') {
												if ($pastStudent_call_sign != $student_call_sign) {
													if ($doDebug) {
														echo "&nbsp;&nbsp;&nbsp;Call sign mismatch: student $student_call_sign vs $pastStudent_call_sign<br />";
													}
												} else {
												// find the last class the student has taken			
												// convert this semester to a number
													$semesterArray			= explode(" ",$pastStudent_semester);
													$thisSemesterNumber		= $semesterArray[0];
													$thisSemesterTerm		= $semesterArray[1];
													$thisSemesterSeq		= $semesterConversion[$thisSemesterTerm];	
													$thisSemesterTest 		= intval($thisSemesterNumber . $thisSemesterSeq);
													if ($thisSemesterTest > $semesterTest) {
														$semesterTest		= $thisSemesterTest;
														$thePromotable		= $pastStudent_promotable;
														$theLevel			= $pastStudent_level;
														$theStudentStatus	= $pastStudent_status;
														$theAdvisor			= $pastStudent_assigned_advisor;
													}
												}
											}
										}				// have checked all past student records
										// setup excluded advisors (if any)
										if (count($myExcludedAdvisors) > 0) {
											if ($doDebug) {
												echo "have excluded advisors:<br /><pre>";
												print_r($myExcludedAdvisors);
												echo "</pre><br />";
											}
											$carryForwardExclAdvisor							= '';
											$myFirst						= TRUE;
											foreach($myExcludedAdvisors as $thisAdvisor) {
												if ($myFirst) {
													$carryForwardExclAdvisor					.= "$thisAdvisor";
													$myFirst				= FALSE;
												} else {
													$carryForwardExclAdvisor					.= "&$thisAdvisor";
												}
											}
											if ($doDebug) {
												echo "new excluded advisor: $carryForwardExclAdvisor<br />";
											}
											$updateLog									.= " / Carried forward excluded advisors $carryForwardExclAdvisor ";			 					
											$doUpdate	= TRUE;
											$carryForward++;
										}
										if ($doDebug) {
											echo "&nbsp;&nbsp;&nbsp;Finished checking all classes for the student<br />
												  &nbsp;&nbsp;&nbsp;semesterTest: $semesterTest<br />
												  &nbsp;&nbsp;&nbsp;Promotable: $thePromotable<br />
												  &nbsp;&nbsp;&nbsp;Past Level: $theLevel (proposed $student_level)<br />
												  &nbsp;&nbsp;&nbsp;Last StudentStatus: $theStudentStatus<br />";
										}
										// if theStudentStatus is Y, the student has taken a class and
										// thePromotable is the promotion status from that class and
										// theLevel is the level of the class the student took
										// theAdvisor is the advisor for the class the student took
										if ($theStudentStatus == 'Y') {
											$possContent			.= "&nbsp;&nbsp;&nbsp;Student has previously taken a $theLevel class<br />";
											// handle case where thePromotable is Y
											if ($thePromotable == 'P') {
												$thePos		= strpos($student_action_log,"ASSGNPREP Student has taken");
												if ($thePos == FALSE) {
													$student_class_priority			= 1;
													$studentUpdateParams[]			= "student_class_priority|$student_class_priority|d";
													$updateLog						.= " / Student has taken a $theLevel class and is promotable";
													$updateStudent					= TRUE;
													if ($doDebug) {
														echo "&nbsp;&nbsp;&nbsp;Student has taken a $theLevel class and is promotable<br />";
													}
													$possContent 	.= "&nbsp;&nbsp;&nbsp;Student was promotable<br />";
													$doContent		= FALSE;
													$priorityUp++;
												}
												// if student is promotable but taking the same class again, don't assign to the same advisor unless
												// that advisor is pre-assigned
												if ($theLevel == $student_level) {
													if ($doDebug) {
														echo "Student is promotable but taking same class again<br />";
													}
													if ($student_pre_assigned_advisor != $theAdvisor) { // not pre-assigned
														$newExclAdvisor						= $theAdvisor;
														$updateLog						.= " / student promotable but taking same class again. Excluding previous advisor. "; 
														$possContent 	.= "&nbsp;&nbsp;&nbsp;Previous advisor was $theAdvisor<br />
																			&nbsp;&nbsp;&nbsp;Student wishes to take the same $student_level class. Class OK.<br />";
														if ($theAdvisor != 'AC6AC') {
															$possContent .= "&nbsp;&nbsp;&nbsp;Excluded advisor $theAdvisor<br />";
														}
														$updateStudent			= TRUE;
														if ($inp_show == 'all') {
															$doContent				= TRUE;
														}
														$promSameClass++;
													}
												}
											} else {
												// promotable status is not P ... setup to process other statuses
												$classOK			= FALSE;
												if ($theLevel == 'Beginner') {
													if ($student_level == 'Beginner') {
														$classOK	= TRUE;
													}
												} elseif ($theLevel == 'Fundamental') {
													if ($student_level == 'Beginner') {
														$classOK	= TRUE;
													} elseif ($student_level == 'Fundamental') {
														$classOK  	= TRUE;
													}
												} elseif ($theLevel == "Intermediate") {
													if ($student_level == 'Beginner') {
														$classOK	= TRUE;
													} elseif ($student_level == 'Fundamental') {
														$classOK  	= TRUE;
													} elseif ($student_level == "Intermediate") {
														$classOK	= TRUE;
													}
												} elseif ($theLevel == "Advanced") {
													$classOK		= TRUE;
												}
												if ($doDebug) {
													echo "&nbsp;&nbsp;&nbsp;Student not promotable. ";
													if ($classOK) {
														echo "ClassOK is true. Can take the class<br />";
													} else {
														echo "ClassOK is NOT true. Put on hold<br />";
													}
												}
									
												if ($thePromotable == 'N') {
													// handle case where thePromotable is N
													// if classOK is TRUE, let the student take the class
													if ($classOK) {
														$thePos		= strpos($student_action_log,"ASSGNPREP OK-N");
														if ($thePos == FALSE) {
															if ($student_pre_assigned_advisor == $theAdvisor) {
																if ($doDebug) {
																	echo "&nbsp;&nbsp;&nbsp;Student already pre-assigned to previous advisor<br />";
																}
																$updateLog 	.= " /  OK-N Student not promotable and is taking the same or lower level from same advisor again.";
																$updateStudent	= TRUE;
																$possContent 	.= "&nbsp;&nbsp;&nbsp;Student is not promotable<br />
																					&nbsp;&nbsp;&nbsp;Previous advisor was $theAdvisor<br />
																					&nbsp;&nbsp;&nbsp;Student is taking a $theLevel class pre-assigned to same advisor<br />";
															} else {
																$newExclAdvisor						= $theAdvisor;
																$updateLog						.= " / OK-N Student not promotable and is taking the same or lower level again. Do not assign to $pastStudent_assigned_advisor.";
																$updateStudent					= TRUE;
																$possContent 	.= "&nbsp;&nbsp;&nbsp;Student is not promotable<br />
																					&nbsp;&nbsp;&nbsp;Previous advisor was $theAdvisor<br />
																					&nbsp;&nbsp;&nbsp;Student wishes to take a $student_level class. Class OK.<br />";
																if ($theAdvisor != 'AC6AC') {
																	$possContent .= "&nbsp;&nbsp;&nbsp;Excluded advisor $theAdvisor<br />";
																}
															}
															$student_class_priority	= 1;
															$studentUpdateParams[]			= "student_class_priority|$student_class_priority|s";
															if ($inp_show == 'all') {
																$doContent		= TRUE;
															}
															$updateLog			.= " / class priority set to 1";
															$updateStudent	= TRUE;
															if ($doDebug) {
																echo "&nbsp;&nbsp;&nbsp;Student is taking a $theLevel class again<br />";
															}
															$npromSameClass++;
														}
													// if classOK is FALSE, student should not take the class
													} else {														
														$studentUpdateParams[]			= "student_hold_reason_code|H|s";
														$studentUpdateParams[]			= "student_intervention_required|H|s";
														$updateLog						.= " / Student took a $theLevel class, not promotable, wants to take higher next level. ";
														$updateStudent			= TRUE;
														$possContent 	.= "&nbsp;&nbsp;&nbsp;Student is not promotable<br />
																			&nbsp;&nbsp;&nbsp;Previous advisor was $theAdvisor<br />
																			&nbsp;&nbsp;&nbsp;Wants to take next higher level.<br />
																			&nbsp;&nbsp;&nbsp;<b>Student placed on hold</b><br />";
														$doContent		= TRUE;
														$emailReason	= "but your advisor's evaluation at the end of the semester was not sufficient for you to take a higher level class";
														if ($doDebug) {
															echo "&nbsp;&nbsp;&nbsp;Student has taken a $theLevel class, is not promotable<br />
																  &nbsp;&nbsp;&nbsp;Wants to take next higher level. <br />";
														}
														$npromHigherClass++;
													}
												// handle the promotable status of Q
												} elseif ($thePromotable == 'Q') {
													// if classOK is TRUE, let the student take the class
													if ($classOK) {
														$thePos		= strpos($student_action_log,"ASSGNPREP OK-Q");
														if ($thePos === FALSE) {
															$newExclAdvisor						= $theAdvisor;
															$student_class_priority			= 1;
															$studentUpdateParams[]			= "student_class_priority|$student_class_priority|s";
															$updateLog						.= " / OK-Q Student has taken a $theLevel class, promotable is Q, taking same or lower class again";
															$updateStudent	= TRUE;
															$possContent 	.= "&nbsp;&nbsp;&nbsp;Student promotable is Q and the student is taking the $theLevel class again<br />
																				&nbsp;&nbsp;&nbsp;Previous advisor was $theAdvisor<br />";
															if ($theAdvisor != 'AC6AC') {
																$possContent .= "&nbsp;&nbsp;&nbsp;Excluded advisor $theAdvisor<br />";
															}
															$doContent		= TRUE;
															if ($doDebug) {
																echo "&nbsp;&nbsp;&nbsp;Student promotable is Q and the student is taking the $theLevel class again<br />";
															}
															$qSameClass++;
														}
													} else {		// wanting to take a different class
														$studentUpdateParams[]			= "student_hold_reason_code|Q|s";
														$studentUpdateParams[]			= "intervention_required|Q|s"; 
														$updateLog						.= " / Student has previously taken $theLevel class, promotable is Q, wants to take next higher level";
														$updateStudent			= TRUE;							
														$possContent 	.= "&nbsp;&nbsp;&nbsp;Promotable is Q but student wants to take the next higher level<br />
																			&nbsp;&nbsp;&nbsp;Previous advisor was $theAdvisor<br />
																			&nbsp;&nbsp;&nbsp;<b>Intervention_required set to Q</b><br />";
														$doContent		= TRUE;
														$emailReason	= "but the advisor did not complete your evaluation for this class";
														if ($doDebug) {
															echo "&nbsp;&nbsp;&nbsp;Student took the $theLevel class, promotable is Q but student wants to take the next higher level<br />";
														}
														$qHigherClass++;
													}
												} elseif ($thePromotable == 'W') {			// student marked by advisor as withdrew
													// if classOK is TRUE, let the student take the class
													if ($classOK) {
														$thePos		= strpos($student_action_log,"ASSGNPREP OK-W");
														if ($thePos === FALSE) {
															$newExclAdvisor						= $theAdvisor;
															$updateLog						.= " / OK-W Student withdrew from a $theLevel class, taking same or lower class again";
															$updateStudent	= TRUE;
															$possContent 	.= "&nbsp;&nbsp;&nbsp;Student withdrew from a $theLevel class.<br />
																				&nbsp;&nbsp;&nbsp;Previous advisor was $theAdvisor<br />
																				&nbsp;&nbsp;&nbsp;The student is taking the same or lower class again<br />";
															if ($theAdvisor != 'AC6AC') {
																$possContent .= "&nbsp;&nbsp;&nbsp;Excluded advisor $theAdvisor<br />";
															}
															if ($inp_show == 'all') {
																$doContent		= TRUE;
															}
															if ($doDebug) {
																echo "&nbsp;&nbsp;&nbsp;Student withdrew from a $theLevel class.<br />
																	  &nbsp;&nbsp;&nbsp;The student is taking the same or lower class again<br />";
															}
															$wSameClass++;
														}
													} else {				// taking a different class. Intervention required
														$studentUpdateParams[]			= "student_hold_reason_code|W|s";
														$studentUpdateParams[]			= "student_intervention_required|H|s";
														$updateLog						.= " / Student withdrew from a $theLevel class, wants to take next higher level";
														$updateStudent					= TRUE;
														$possContent 	.= "&nbsp;&nbsp;&nbsp;Student withdrew from a $theLevel class.<br />
																			&nbsp;&nbsp;&nbsp;The student wants to take the next higher level.<br />
																			&nbsp;&nbsp;&nbsp;<b>Student placed on hold</b><br />";
														$doContent		= TRUE;
														$emailReason	= "but according to our records, you withdrew from that class without completing it or obtaining your advisor's evaluation";
														if ($doDebug) {
															echo "&nbsp;&nbsp;&nbsp;Student withdrew from a $theLevel class.<br />
																  &nbsp;&nbsp;&nbsp;The student wants to take the next higher level. Is on hold.<br />";
														}
														$wHigherClass++;
													}
										
												} elseif ($thePromotable == '') {
													// if classOK is TRUE, let the student take the class
													if ($classOK) {
														$thePos		= strpos($student_action_log,"ASSGNPREP OK-B");
														if ($thePos === FALSE) {
															if ($student_assigned_advisor != ''){
																$newExclAdvisor						= $theAdvisor;
															}
															$student_class_priority				= 1;
															$studentUpdateParams[]				= "student_class_priority|$student_class_priority|d";
															$updateLog							.= " / OK-B Student has taken a $theLevel class, promotable is unknown, taking same or lower class again";
															$updateStudent	= TRUE;
															$possContent 	.= "&nbsp;&nbsp;&nbsp;Student was not evaluated.
																				&nbsp;&nbsp;&nbsp;Previous advisor was $theAdvisor<br />
																				&nbsp;&nbsp;&nbsp;The student is taking the same class again<br />";
															if ($theAdvisor != 'AC6AC') {
																$possContent .= "&nbsp;&nbsp;&nbsp;Excluded advisor $theAdvisor<br />";
															}
															if ($inp_show == 'all') {
																$doContent		= TRUE;
															}
															if ($doDebug) {
																echo "&nbsp;&nbsp;&nbsp;Student was in a $theLevel class but not evaluated.<br />
																	  &nbsp;&nbsp;&nbsp;The student is taking the same class again<br />";
															}
															$neSameClass++;
														}
													} else {				// taking a different class. Intervention required
														$studentUpdateParams[]			= "student_hold_reason_code|E|s";
														$studentUpdateParams[]			= "student_intervention_required|H|s";
														$updateLog						.= " / Student has taken the $theLevel class, advisor $student_assigned_advisor has not completed the evaluation, student wants to take next higher level";
														$updateStudent	= TRUE;
														$possContent 	.= "&nbsp;&nbsp;&nbsp;Student was not evaluated.<br />
																			&nbsp;&nbsp;&nbsp;Previous advisor was $theAdvisor<br />
																			&nbsp;&nbsp;&nbsp;The student wants to take the next higher level.<br />
																			&nbsp;&nbsp;&nbsp;<b>Student placed on hold</b><br />";
														$doContent		= TRUE;
														$emailReason	= "but the advisor did not complete your evaluation for this class";
														if ($doDebug) {
															echo "&nbsp;&nbsp;&nbsp;Student was in a $theLevel class but not evaluated.<br />
																  &nbsp;&nbsp;&nbsp;The student wants to take the next higher level. Is on hold.<br />";
														}
														$neHigherClass++;
													}
												}
											 }		// finished with the not-promotable loop
										}			// finished with the student_status=Y loop
									}				// finished with the pastStudent records
								}
							}					// finished with the intervention required loop
						}
					}						// finished with the hold_override loop
					// see if should send an email
					if ($sendEmail) {
						$doEmail		= FALSE;
						
						if ($student_hold_reason_code == 'H' and $student_intervention_required == 'H') {
							$doEmail	= TRUE;			// not promotable, registered for higher class
							$theReason	= "your advisor did not evaluate your proficiency sufficient to take the next level class.";
						}
						if ($student_hold_reason_code == 'Q' and $student_intervention_required == 'Q') {
							$doEmail	= TRUE;			// advisor quite, registred for higher class
							$theReason	= "your advisor quit and didn't finish the class nor provide student proficiency evaluations.";
						}
						if ($student_hold_reason_code == 'W' and $student_intervention_required == 'H') {
							$doEmail	= TRUE;			// student withdrew, registred for higher class
							$theReason	= "you withdrew and didn't finish the previous class.";
						}
						if ($student_hold_reason_code == 'E' and $student_intervention_required == 'H') {
							$doEmail	= TRUE;			// student not evaluated, registered for highr class
							$theReason	= "your advisor didn't evaluate your proficiency in your previous class.";
						}
						$anomolyCount++;
						$mySubject	= "CW Academy Class Registration Conflict";
						if ($doEmail) {					// format and send an email
							if ($testMode) {
								$myTo		= 'rolandksmith@gmail.com';
								$mySubject	= "TESTMODE $mySubject";
								$mailCode	= 2;
							} else {
								$myTo		= $student_email;
								$mailCode	= 12;
							}
							$emailContent	= "<p>To: $student_last_name, $student_first_name ($student_call_sign)</p>
												<p>You recently signed up to take the $student_level CW Academy class for the $student_semester semester.</p>
												<p>Before CW Academy can process your registration information, please take the $student_level Morse code self assessment by 
												clicking <a href='$siteURL/cwa-student-registration/' target='_blank'>HERE</a> 
												and selecting Option 4 to see if this class is a good fit for you. If not, try the other levels.</p>
												<p>When the self assessment is complete, click <a href='mailto:kcgator@gmail.com?subject=$student_call_sign Self Assessment Completed'>HERE</a> 
												to send an email to Bob Carter WR7Q with your class level decision.</p>
												<p>Your registration for the $student_level class is currently on hold because $theReason</p>
												<p><span style='color:red;font-size:medium;'><b>Do not reply to this email as the address is not monitored.</b></span><br /></p>
												<p>73,<br />Bob Carter WR7Q<br />CW Academy Administrator</p>";
							$increment++;
							$mailResult		= emailFromCWA_v2(array('theRecipient'=>$myTo,
																	'theSubject'=>$mySubject,
																	'jobname'=>$jobname,
																	'theContent'=>$emailContent,
																	'mailCode'=>$mailCode,
																	'increment'=>$increment,
																	'testMode'=>$testMode,
																	'doDebug'=>$doDebug));
							if ($mailResult !== FALSE) {
								if ($doDebug) {
									echo "&nbsp;&nbsp;&nbsp;An email was sent to $myTo<br />";
								}
								$possContent .= "&nbsp;&nbsp;&nbsp;An email was sent to $myTo<br />";
								$emailCount++;
								$updateLog				.= " / Email was sent to $myTo ";
								$updateStudent			= TRUE;
							} else {
								echo "&nbsp;&nbsp;&nbsp;The mail send function failed.<br /><pre>";
								print_r($myHeaders);
								echo "</pre><br />";
								$emailErrors++;
							}
							$content	.= $possContent;
						}
					}
					// if updateStudent, then something should be processed
					if ($updateStudent) {
						// fix up excluded_advisor
						$exclArray					= array();
						if ($student_excluded_advisor != '') {
							$myArray				= explode("|",$student_excluded_advisor);
							foreach($myArray as $thisAdvisor) {
								if (!in_array($thisAdvisor,$exclArray)) {
									$exclArray[]	= $thisAdvisor;
								}
							}
						}
						if ($carryForwardExclAdvisor != '') {
							$myArray				= explode("|",$carryForwardExclAdvisor);
							foreach($myArray as $thisAdvisor) {
								if (!in_array($thisAdvisor,$exclArray)) {
									$exclArray[]	= $thisAdvisor;
								}
							}
							
						}
						if ($newExclAdvisor != '') {
							if (!in_array($newExclAdvisor,$exclArray)) {
								$exclArray[]	= $newExclAdvisor;
							}
						}
						if (count($exclArray) > 0) {
							if ($doDebug) {
								echo "exclArray:<br /><pre>";
								print_r($exclArray);
								echo "</pre><br />";
							}
							$firstTime			= TRUE;
							$myStr				= "";
							foreach($exclArray as $thisAdvisor) {
								if ($firstTime) {
									$myStr		= $thisAdvisor;
									$firstTime	= FALSE;
								} else {
									$myStr		.= "&$thisAdvisor";
								}
							}
							$studentUpdateParams[]	= "student_excluded_advisor|$myStr|s";
							
						}
							
						/// fix up the action log
						$student_action_log				= "$student_action_log / $actionDate ASSGNPREP $updateLog";
						$studentUpdateParams[]			= "student_action_log|$student_action_log|s";
						$updateCount++;
						if ($testMode) {
							if ($doDebug) {
								echo "&nbsp;&nbsp;&nbsp;updateStudent is TRUE and so is testMode<br />
									  &nbsp;&nbsp;&nbsp;Update parameters:<br /><pre>";
									  print_r($studentUpdateParams);
									  echo "</pre><br />";
							}
						}
						if ($updateMode) {
							if ($doDebug) {
								echo "&nbsp;&nbsp;&nbsp;updateMode is TRUE so writing to student table<br /><pre>";
								print_r($studentUpdateParams);
								echo "</pre><br />";
							}
							$updateData	= array('tableName'=>$studentTableName,
												'inp_data'=>$studentUpdateParams,
												'inp_format'=>array(''),
												'jobname'=>'ASSGNPREP',
												'inp_id'=>$student_ID,
												'inp_method'=>'update',
												'inp_callsign'=>$student_call_sign,
												'inp_semester'=>$student_semester,
												'inp_who'=>$userName,
												'testMode'=>$testMode,
												'doDebug'=>$doDebug);
							$updateResult	= updateStudent($updateData);
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
								if ($doDebug) {
									echo "updating student $student_call_sign succeeded<br />";
								}
							}
						}
					}
					$updateStudent		= FALSE;
					if ($doContent) {
						$content	.= $possContent;
					}
				}					// end of the big While loop 
			} else {				// end of the big Nmbr Records loop
				if ($doDebug) {
					echo "No records matching the criteria were found in $studentTableName<br />";
				}
				$content	.= "No records found in the $studentTableName pod<br />";
			}
		}
		// finished with students. Now process the advisors.

// $doDebug = TRUE;

		if ($doAdvisors) {				// no need to do this if only sending emails
			$advisorCount		= 0;
			$advisorEvalOK		= 0;
			$advisorEvalNOK		= 0;
			$possContent		= '';

			if ($doDebug) {
				echo "<br /><br />Finished with students. Starting with the advisors<br />";
			}
			$content			.= "<h4>Processing Advisors</h4>";
			// get a list of advisors with classes in the next semester
			$sql				= "select distinct(advisorclass_call_sign) 
									from $advisorClassTableName 
									where advisorclass_semester = '$nextSemester' 
									order by advisorclass_call_sign";
			$wpw1_cwa_advisorCallSign	= $wpdb->get_results($sql);
			if ($wpw1_cwa_advisorCallSign === FALSE) {
				handleWPDBError($jobname,$doDebug);
			} else {
				$numACRows			= $wpdb->num_rows;
				if ($doDebug) {
					echo "ran $sql<br />and found $numACRows rows<br />";
				}
				if ($numACRows > 0) {
					$updateAdvisor							= FALSE;
					$doContent								= FALSE;
					$evalsDone								= TRUE;
					$alreadyNine							= FALSE;
					$advisorUpdateParams					= array();
					foreach ($wpw1_cwa_advisorCallSign as $advisorCallSignRow) {
						$thisAdvisorCallSign	= $advisorCallSignRow->advisorclass_call_sign;
			
						if ($thisAdvisorCallSign != 'K1BG') {
							if ($doDebug) {
								echo "<br />Processing advisor $thisAdvisorCallSign<br />";
							}
							// get evaluation info from last semester, if any
							$evalSql		= "select * 
												from $advisorClassTableName 
												left join $userMasterTableName on user_call_sign = advisorclass_call_sign 
												where advisorclass_semester = '$prevSemester'
													and advisorclass_call_sign = '$thisAdvisorCallSign'  
												order by advisorclass_sequence";
							$wpw1_cwa_advisorclass	= $wpdb->get_results($evalSql);
							if ($wpw1_cwa_advisorclass === FALSE) {
								handleWPDBError($jobname,$doDebug);
							} else {
								$numACRows			= $wpdb->num_rows;
								if ($doDebug) {
									echo "ran $evalSql<br />and found $numACRows rows<br />";
								}
								if ($numACRows > 0) {
									foreach ($wpw1_cwa_advisorclass as $advisorClassRow) {
										$advisorClass_master_ID 				= $advisorClassRow->user_ID;
										$advisorClass_master_call_sign			= $advisorClassRow->user_call_sign;
										$advisorClass_first_name 				= $advisorClassRow->user_first_name;
										$advisorClass_last_name 				= $advisorClassRow->user_last_name;
										$advisorClass_email 					= $advisorClassRow->user_email;
										$advisorClass_phone 					= $advisorClassRow->user_phone;
										$advisorClass_city 						= $advisorClassRow->user_city;
										$advisorClass_state 					= $advisorClassRow->user_state;
										$advisorClass_zip_code 					= $advisorClassRow->user_zip_code;
										$advisorClass_country_code 				= $advisorClassRow->user_country_code;
										$advisorClass_whatsapp 					= $advisorClassRow->user_whatsapp;
										$advisorClass_telegram 					= $advisorClassRow->user_telegram;
										$advisorClass_signal 					= $advisorClassRow->user_signal;
										$advisorClass_messenger 				= $advisorClassRow->user_messenger;
										$advisorClass_master_action_log 		= $advisorClassRow->user_action_log;
										$advisorClass_timezone_id 				= $advisorClassRow->user_timezone_id;
										$advisorClass_languages 				= $advisorClassRow->user_languages;
										$advisorClass_survey_score 				= $advisorClassRow->user_survey_score;
										$advisorClass_is_admin					= $advisorClassRow->user_is_admin;
										$advisorClass_role 						= $advisorClassRow->user_role;
										$advisorClass_master_date_created 		= $advisorClassRow->user_date_created;
										$advisorClass_master_date_updated 		= $advisorClassRow->user_date_updated;
					
										$advisorClass_sequence					= $advisorClassRow->advisorclass_sequence;
										$advisorClass_number_students			= $advisorClassRow->advisorclass_number_students;
										$advisorClass_class_evaluation_complete = $advisorClassRow->advisorclass_evaluation_complete;
										$advisorCount++;

										if ($doDebug) {
											echo "&nbsp;&nbsp;&nbsp;Class: $thisAdvisorCallSign sequence: $advisorClass_sequence; evals: $advisorClass_class_evaluation_complete; students: $advisorClass_number_students<br />";
										}
										if ($advisorClass_survey_score == 9) {
											if ($doDebug) {
												echo "&nbsp;&nbsp;&nbsp;Advisor $advisorClass_call_sign survey score = $advisorClass_survey_score. Bypassed<br />";
											}
											$alreadyNine			= TRUE;
										}
										if ($advisorClass_class_evaluation_complete != 'Y' && $advisorClass_number_students > 0) {			// evaluations are not done
											if ($doDebug) {
												echo "&nbsp;&nbsp;&nbsp;Setting advisor score to 9<br />";
											}
											$evalsDone					= FALSE;
										}
									}
									if (!$evalsDone) {
										$advisorUpdateParams[]			= "user_survey_score|9|d";
										$possContent					.= "&nbsp;&nbsp;&nbsp;$advisorClass_master_call_sign: Evaluations are incomplete. Setting survey score to 9<br />";
										$doContent						= TRUE;
										$updateAdvisor					= TRUE;
										$advisorEvalNOK++;
										$actionLogData					= "Evaluations incomplete. Set survey score to 9 ";
									} else {						// evals are done
										$advisorEvalOK++;
										if ($alreadyNine) {			// set survey score back to blank
											$advisorUpdateParams[]			= "user_survey_score|0|d";
											$possContent					.= "&nbsp;&nbsp;&nbsp;$advisorClass_master_call_sign: Evaluations are complete. Setting survey score from 9 to 0<br />";
											$doContent						= TRUE;
											$updateAdvisor					= TRUE;
											$actionLogData					= "Evaluations complete. Set survey score to 0 ";
										}
									}
									if ($updateAdvisor) {
										if ($testMode) {
											if ($doDebug) {
												echo "&nbsp;&nbsp;&nbsp;updateAdvisor is TRUE and so is testMode<br />
													  &nbsp;&nbsp;&nbsp;Update parameters:<br /><pre>";
													  print_r($advisorUpdateParams);
													  echo "</pre><br />";
											}
										}
										if ($updateMode) {
											if ($doDebug) {
												echo "&nbsp;&nbsp;&nbsp;updateMode is TRUE so updating advisor<br />";
											}
											$advisorClass_master_action_log			.= " / $actionDate ASSGNPREP $userName $actionLogData";
											$advisorUpdateParams[]		= "user_action_log|$advisor_action_log|s";
											$advisorUpdateData		= array('tableName'=>$userMasterTableName,
																			'inp_method'=>'update',
																			'inp_data'=>$advisorUpdateParams,
																			'jobname'=>$jobname,
																			'inp_id'=>$advisorClass_master_ID,
																			'inp_callsign'=>$thisAdvisorCallSign,
																			'inp_semester'=>$nextSemester,
																			'inp_who'=>$userName,
																			'testMode'=>$testMode,
																			'doDebug'=>$doDebug);
											$updateResult	= update_user_master($advisorUpdateData);
											if ($updateResult[0] === FALSE) {
												$myError	= $wpdb->last_error;
												$mySql		= $wpdb->last_query;
												$errorMsg	= "$jobname Processing $thisAdvisorCallSign in $userMasterTableName failed. Reason: $updateResult[1]<br />SQL: $mySql<br />Error: $myError<br />";
												if ($doDebug) {
													echo $errorMsg;
												}
												sendErrorEmail($errorMsg);
												$content		.= "Unable to update content in $advisorTableName<br />";
											}
										}
									}
								}
								if ($doContent) {
									$content	.= $possContent;
								}
							}
						}
					}				// end of big advisor while loop
				} else {			// end of numberRecords loop
					$content	.= "No records were found in $advisorTableName table<br />";	
				}
			}
		}
	}
	if ($strPass == 2) {
		$content		.= "<br /><p><b>Counts:</b><br />
							<table>
							<tr><td style='text-align:right;'>$numSRows</td>
								<td>Student records processed</td></tr>
							<tr><td style='text-align:right;'>$promSameClass</td>
								<td>Promotable students taking same class</td></tr>
							<tr><td style='text-align:right;'>$npromSameClass</td>
								<td>Not Promotable students taking same class</td></tr>
							<tr><td style='text-align:right;'>$npromHigherClass</td>
								<td>Not Promotable students wanting higher level class (on hold, getting an email)</td></tr>
							<tr><td style='text-align:right;'>$qSameClass</td>
								<td>Q status students taking same class</td></tr>
							<tr><td style='text-align:right;'>$qHigherClass</td>
								<td>Q status students wanting a higher level class (on hold, getting an email)</td></tr>
							<tr><td style='text-align:right;'>$wSameClass</td>
								<td>Withdrawn students taking same level class</td></tr>
							<tr><td style='text-align:right;'>$wHigherClass</td>
								<td>Withdrawn students wanting a higher level class (on hold, getting an email)</td></tr>
							<tr><td style='text-align:right;'>$neSameClass</td>
								<td>Not evaluated students taking same level class</td></tr>
							<tr><td style='text-align:right;'>$neHigherClass</td>
								<td>Not evaluated students wanting a higher level class (on hold, getting an email)</td></tr>
							<tr><td	 style='text-align:right;'>$invalidYouth</td>
								<td>Invalid youth designation changed</td></tr>
							<tr><td	 style='text-align:right;'>$carryForward</td>
								<td>Excluded advisors carried forward</td></tr>
							<tr><td	 style='text-align:right;'>$priorityUp</td>
								<td>Students whose priority was sent to 1<td></tr>
							<tr><td style='text-align:right;'>$emailCount</td>
								<td>Emails sent</td></tr>
							<tr><td style='text-align:right;'>$noCatalogCount</td>
								<td>Students needing no_catalog entry to be removed</td></tr>
							<tr><td style='text-align:right;'>$emailErrors</td>
								<td>Email Errors</td></tr>
							<tr><td style='text-align:right;'>$updateCount</td>
								<td>Student records needing updates</td></tr>
							<tr><td style='text-align:right;'>$advisorCount</td>
								<td>Advisor records processed</td></tr>
							<tr><td style='text-align:right;'>$advisorEvalOK</td>
								<td>Advisors had classes and completed evaluations</td></tr>
							<tr><td style='text-align:right;'>$advisorEvalNOK</td>
								<td>Advisors had classes and evaluations are incomplete</td></tr>
							</table>";
	}
	$content		.= "<br /><p>To return to the Student Management menu, click
						<a href='studentManagementURL?strpass=1'>HERE</a>.</p>";

	$thisTime 		= date('Y-m-d H:i:s',$currentTimestamp);
	$content 		.= "<br /><br /><p>Prepared at $thisTime</p>";
	$endingMicroTime = microtime(TRUE);
	$elapsedTime	= $endingMicroTime - $startingMicroTime;
	$elapsedTime	= number_format($elapsedTime, 4, '.', ',');
	$content		.= "<p>Report pass $strPass took $elapsedTime seconds to run</p>";
	$nowDate		= date('Y-m-d');
	$nowTime		= date('H:i:s');
	$thisStr			= 'Production';
	$result			= write_joblog_func("$jobname|$nowDate|$nowTime|$userName|Time|$thisStr|$strPass: $elapsedTime");
	if ($result == 'FAIL') {
		$content	.= "<p>writing to joblog.txt failed</p>";
	}
	return $content;
}
add_shortcode ('prepare_students_for_assignment_to_advisors', 'prepare_students_for_assignment_to_advisors_func');
