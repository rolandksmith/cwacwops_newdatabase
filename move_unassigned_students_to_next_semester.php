function move_unassigned_students_to_next_semester_func() {

/* Function to move unassigned students for the current semester to the
  next semester and bump up their student_priority
 
 


 modified 22Feb2020 by Roland to fix appending to the action log
 modified 10May2020 by Roland to set up testmode
 modified bc 30Dec20 15:41  / chgd acadamy to academy
 modified 4Jan2021 by Roland to add ability to save the report
 modified 1Feb2021 by Roland to remove student_code
 modified 18Mar2021 by Roland to split the program functions into
	two processes. One to notify the student that they are on the waiting list
	and a second to do the move to the next semester and notify them of that
 Modified 18Jan2022 by Roland to use tables rather than tables
 Modified 25Oct22 by Roland for the new timezone table format
 Modified 16Apr23 by Roland to fix action_log
 Modified 13Jul23 by Roland to use conslidated tables
 Modified 11Sep23 by Roland to include student's name and callsign in the email
 Modified 12Jan24 by Roland to update timezone offset
*/

	global $wpdb;


	$initializationArray 	= data_initialization_func();
	$validUser 				= $initializationArray['validUser'];
	$userName  				= $initializationArray['userName'];
	$validTestmode			= $initializationArray['validTestmode'];
	$siteURL				= $initializationArray['siteurl'];

	if ($validUser == "N") {
		return "YOU'RE NOT AUTHORIZED!<br />Goodby";
	}
	ini_set('max_execution_time',0);

		ini_set('display_errors','1');
		error_reporting(E_ALL);	

/// get the time that the process started
	$startingMicroTime			= microtime(TRUE);

// initial values	
	$doDebug 					= FALSE;
	$testMode					= FALSE;
	$requestType				= "";
	$strPass					= "1";
	$firstTime					= TRUE;
	$studentCount				= 0;
	$notify						= 0;
	$notifyAndMove				= 0;
	$notifyAndReregister		= 0;
	$emailsSent					= 0;
	$studentsMoved				= 0;
	$studentsBypassed0			= 0;
	$studentsBypassedH			= 0;
	$myDate 					= date('dMy Hi') . 'z';
	$logDate					= date('Y-m-d H:i:s');
	$inp_report					= '';
	$inp_type					= '';
	$promoString				= '';
	$increment					= 0;
	$jobname					= "Move Unassigned Students to Next Semester";
	$currentDate				= $initializationArray['currentDate'];
	$fieldTest					= array('action_log','post_status','post_title','control_code');
	$theURL						= "$siteURL/cwa-move-unassigned-students-to-next-semester/";
	$studentRegistrationURL		= "$siteURL/cwa-student-registration/";
	$declineMoveURL				= "$siteURL/cwa-decline-student-reassignment/";

	
// get the input information
	if (isset($_REQUEST)) {
		foreach($_REQUEST as $str_key => $str_value) {
			if ($doDebug) {
				echo "Key: $str_key | Value: $str_value <br />\n";
			}
			if ($str_key 		== "strpass") {
				$strPass		 = $str_value;
				$strPass		= filter_var($strPass,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "request_type") {
				$requestType	 = $str_value;
				$requestType	= filter_var($requestType,FILTER_UNSAFE_RAW);
			}
			if($str_key			== "inp_report") {
				$inp_report		= $str_value;
				$inp_report		= filter_var($inp_report,FILTER_UNSAFE_RAW);
			}
			if($str_key			== "inp_type") {
				$inp_type		= $str_value;
				$inp_type		= filter_var($inp_type,FILTER_UNSAFE_RAW);
			}
			if($str_key			== "inp_mode") {
				$inp_mode		= $str_value;
				$inp_mode		= filter_var($inp_mode,FILTER_UNSAFE_RAW);
				if ($inp_mode == 'TESTMODE') {
					$testMode	= TRUE;
				}
			}
			if ($str_key 		== "inp_verbose") {
				$inp_verbose	 = $str_value;
				$inp_verbose	 = filter_var($inp_verbose,FILTER_UNSAFE_RAW);
				if ($inp_verbose == 'Y') {
					$doDebug	= TRUE;
					echo "have set doDebug to TRUE<br />";
				}
			}
		}
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

td {padding:5px;font-size:small;vertical-align:top;}

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
		$studentTableName	= "wpw1_cwa_consolidated_student2";
		$run_mode		= 'tm';
		$content 		.= "<p><strong>Function under development.</strong></p>";
		if ($doDebug) {
			echo "Function under development.<br />";
		}
	} else {
		$studentTableName	= "wpw1_cwa_consolidated_student";
		$run_mode		= 'pm';
	}
		


	

	
/*
 * When strPass is equal to 1 then get the run type
 *
*/
	if ("1" == $strPass) {
	if (in_array($userName,$validTestmode)) {			// give option to run in test mode 
		if ($doDebug) {
			echo "offering testModeOption<br />";
		}
		$testModeOption	= "Operation Mode<br />
							<input type='radio' class='formInputButton' name='inp_mode' value='Production' checked='checked'> Production<br />
							<input type='radio' class='formInputButton' name='inp_mode' value='TESTMODE'> TESTMODE<br />
							<br />Verbosity<br />
							<input type='radio' class='formInputButton' name='inp_verbose' value='N' checked='checked'> Normal Output<br />
							<input type='radio' class='formInputButton' name='inp_verbose' value='Y'> Verbose<br />";
	} else {
		if ($doDebug) {
			echo "No testMode offered<br />";
		}
		$testModeOption	= '';
	}

	
		$content .= "<h3>$jobname</h3>
					<p>This function has two options:
					<dl>
					<dt>NOTIFY</dt>
					<dd>Sends an email to the unassigned students that they are on the waiting list. This 
					is to be run about 5 days before the semester starts.</dd>
					<dt>MOVE</dt>
					<dd>Sends an mail to the unassigned students that they weren't able to be assigned 
					to a class and have been moved to the next semester with a higher priority. This 
					function is to be run about 10 days after the semester starts.</dd>
					</dl>
					<p>Please select the type of process, whether or not to save the report, and then 
					click 'Next' to execute.</p>
					<form method='post' action='$theURL' 
					name='selection_form' ENCTYPE='multipart/form-data'>
						<input type='hidden' name='strpass' value='2'>
					<p>Select the type of run:<br />
						<input type='radio' class='formInputButton' name='inp_type' value='NOTIFY' checked='checked'> NOTIFY<br />
						<input type='radio' class='formInputButton' name='inp_type' value='MOVE'> MOVE</p>
					<p>Save Report to Reports Table?<br />
						<input type='radio' id='inp_report' name='inp_report' value='N' checked='checked'> Do not save<br />
						<input type='radio' id='inp_report' name='inp_report' value='Y' > Save the report<br /></p>
					<p>$testModeOption</p>
					<p><input class='formInputButton' type='submit' value='Next' /></p>
					</form>";
		
//// pass 2 display student information		
		
	} elseif ("2" == $strPass) {
	
		$content .= "<h3>Move Unassigned Students to Next Semester</h3>";
		
		if ($inp_type != 'NOTIFY' && $inp_type != 'MOVE') {
			$content		.= "Type of run is invalid. Function aborted.";
			return $content;
		}
		
		$currentSemester			= $initializationArray['currentSemester'];
		$nextSemester				= $initializationArray['nextSemester'];
		$semesterTwo				= $initializationArray['semesterTwo'];
		if ($currentSemester == "Not in Session") {
			if ($inp_type == 'NOTIFY') {
				$currentSemester			= $nextSemester;
				$nextSemester				= $semesterTwo;
			} else {
				$content			.= "There isn't a semester in session so MOVE is not a valid option.";
				return $content;
			}
		} else {
			if ($inp_type != 'MOVE') {
				$content			.= "The semester is in session. MOVE is the appropriate option.";
				return $content;
			}
		}
		if ($doDebug) {
			echo "At Pass 2. Current semester: $currentSemester.<br />
			Next Semester: $nextSemester<br />
			Type of run: $inp_type<br />";
		}
		
		// figure out when the verify email will be sent
		$whenArray			= array('Jan/Feb'=>'November 15th',
									'May/Jun'=>'March 15th',
									'Sep/Oct'=>'July 15th');
		$thisWhen			= "";
		$myArray			= explode(" ",$nextSemester);
		if (count($myArray) > 1) {
			$thisStr		= $myArray[1];
			$thisWhen		= $whenArray[$thisStr];
		} else {
			$content		.= "Unable to calculate when the verify email will be sent from $nextSemester<br />";
		}
		
		
		$sql				= "select * from $studentTableName 
								where semester='$currentSemester' 
									and response='Y' 
									and student_status='' 
								order by call_sign";
		$wpw1_cwa_student	= $wpdb->get_results($sql);
		if ($wpw1_cwa_student === FALSE) {
			if ($doDebug) {
				echo "Reading $studentTableName table failed<br />";
				echo "wpdb->last_query: " . $wpdb->last_query . "<br />";
				echo "<b>wpdb->last_error: " . $wpdb->last_error . "</b><br />";
			}
		} else {
			$numSRows		= $wpdb->num_rows;
			if ($doDebug) {
				$myStr		= $wpdb->last_query;
				echo "ran $myStr<br />and retrieved $numSRows rows from $studentTableName table<br />";
			}
			if ($numSRows > 0) {
				if ($inp_type == 'NOTIFY') {
					$content	.= "<table style='width:1000px;'><tr>
									<th>Call Sign</th>
									<th>Name</th>
									<th>City</th>
									<th>State</th>
									<th>Country</th>
									<th>Phone</th>
									<th>Email</th>
									<th>TZ</th>
									<th>Level</th>
									<th>Class Priority</th>
									</tr>";
			
				} else {
					$content	.= "<table style='width:1000px;'>
									<tr>
										<th>Call Sign</th>
										<th>Name</th>
										<th>City</th>
										<th>State</th>
										<th>Country</th>
										<th>Phone</th>
										<th>Email</th>
									</tr><tr>
										<th>Level</th>
										<th>Current Semester</th>
										<th>Next Semester</th>
										<th>Current Priority</th>
										<th>New Priority</th>
										<th>Intervention<br />Required</td>
										<th>&nbsp;</th>
									</tr>";
					foreach ($wpw1_cwa_student as $studentRow) {
						$student_ID								= $studentRow->student_id;
						$student_call_sign						= strtoupper($studentRow->call_sign);
						$student_first_name						= $studentRow->first_name;
						$student_last_name						= stripslashes($studentRow->last_name);
						$student_email  						= strtolower(strtolower($studentRow->email));
						$student_phone  						= $studentRow->phone;
						$student_ph_code						= $studentRow->ph_code;
						$student_city  							= $studentRow->city;
						$student_state  						= $studentRow->state;
						$student_zip_code  						= $studentRow->zip_code;
						$student_country  						= $studentRow->country;
						$student_country_code					= $studentRow->country_code;
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
						$student_date_created 					= $studentRow->date_created;
						$student_date_updated			  		= $studentRow->date_updated;

						$student_last_name 						= no_magic_quotes($student_last_name);
				
						$sendEmail								= FALSE;
						$promoString							= '';
						if ($doDebug) {
							echo  "<br />Processing $student_call_sign<br />
									&nbsp;&nbsp;&nbsp;&nbsp;Response: $student_response<br />
									&nbsp;&nbsp;&nbsp;&nbsp;Status: $student_student_status<br />
									&nbsp;&nbsp;&nbsp;&nbsp;Class Priority: $student_class_priority<br />";
						}				
						$updateStr								= '';
						$the_level		= substr($student_level,0,3);

						$studentCount++;
						if ($doDebug) {
							echo "inp_type is $inp_type<br />";
						}
				
						if ($inp_type == 'MOVE') {
							if ($doDebug) {
								echo "class_priority is 0. Setting class_priority to 1. Student will be moved<br />";
							}	
							$new_priority = '1';
							$student_action_log 	= "$student_action_log / $myDate REASSIGN student moved to the next semester $nextSemester";

							$newActionLog			= formatActionLog($student_action_log);
							$content	.= "<tr>
												<td>$student_call_sign</td>
												<td>$student_last_name, $student_first_name</td>
												<td>$student_city</td>
												<td>$student_state</td>
												<td>$student_country</td>
												<td>$student_phone</td>
												<td>$student_email</td>
											</tr><tr>
												<td>$student_level</td>
												<td>$student_semester</td>
												<td>$nextSemester</td>
												<td>$student_class_priority</td>
												<td>$new_priority</td>
												<td>$student_intervention_required</td>
												<td></td>
											</tr><tr>
												<td colspan='7'>$newActionLog</td></tr>";
												
							$thisOffset		= getOffsetFromIdentifier($student_timezone_id,$nextSemester,$doDebug);
												
							$updateParams	= array();
							$updateFormat	= array();
							$updateParams['semester']				= $nextSemester;
							$updateFormat[]							= '%s';
							$updateParams['timezone_offset']		= $thisOffset;
							$updateFormat[]							= '%f';
							$updateParams['class_priority']			= 1;
							$updateFormat[]							= '%d';
							$updateParams['action_log']				= $student_action_log;
							$updateFormat[]							= '%s';
							$updateParams['response']				= '';
							$updateFormat[]							= '%s';
							$updateParams['response_date']			= '';
							$updateFormat[]							= '%s';
							$updateParams['student_status']			= '';
							$updateFormat[]							= '%s';
							$updateParams['hold_override']			= '';
							$updateFormat[]							= '%s';
							$updateParams['intervention_required']	= '';
							$updateFormat[]							= '%s';
							$updateParams['pre_assigned_advisor']	= '';
							$updateFormat[]							= '%s';
							$updateParams['assigned_advisor']		= '';
							$updateFormat[]							= '%s';
							$updateParams['advisor_class_timezone']	= -99;
							$updateFormat[]							= '%d';
							$updateParams['advisor_select_date']	= '';
							$updateFormat[]							= '%s';
							$updateParams['welcome_date']			= '';
							$updateFormat[]							= '%s';
							$updateParams['email_sent_date']		= '';
							$updateFormat[]							= '%s';
							$updateParams['email_number']			= 0;
							$updateFormat[]							= '%d';
							$updateParams['assigned_advisor_class'] = 0;
							$updateFormat[]							= '%d';
							$updateParams['first_class_choice']		= 'None';
							$updateFormat[]							= '%s';
							$updateParams['second_class_choice']	= 'None';
							$updateFormat[]							= '%s';
							$updateParams['third_class_choice']		= 'None';
							$updateFormat[]							= '%s';
							$updateParams['first_class_choice_utc']	= '';
							$updateFormat[]							= '%s';
							$updateParams['second_class_choice_utc'] = '';
							$updateFormat[]							= '%s';
							$updateParams['third_class_choice_utc']	= '';
							$updateFormat[]							= '%s';
							$updateParams['no_catalog']				= 'Y';
							$updateFormat[]							= '%s';
							$updateParams['waiting_list']			= '';
							$updateFormat[]							= '%s';
							$updateParams['abandoned']				= 'N';
							$updateFormat[]							= '%s';

							$studentUpdateData		= array('tableName'=>$studentTableName,
															'inp_data'=>$updateParams,
															'inp_format'=>$updateFormat,
															'inp_method'=>'update',
															'jobname'=>$jobname,
															'inp_id'=>$student_ID,
															'inp_callsign'=>$student_call_sign,
															'inp_semester'=>$student_semester,
															'inp_who'=>$userName,
															'testMode'=>$testMode,
															'doDebug'=>$doDebug);
							$updateResult	= updateStudent($studentUpdateData);
							if ($updateResult[0] === FALSE) {
								handleWPDBError($jobname,$doDebug);
								$content		.= "Unable to update content in $studentTableName<br />";
							} else {
								if ($doDebug) {
									echo "current student record updated. Preparing to send email<br />";
								}
								
								$studentsMoved++;
								$stringToPass	= "studentid=$student_ID&inp_mode=$run_mode&program_action=valid";
								$enstr			= base64_encode($stringToPass);

								$my_subject	= "CW Academy Update to your Registration Request";	
								$my_message = "To: $student_last_name, $student_first_name ($student_call_sign):
<p>Thank you for your interest in attending the $student_level CW Academy class 
for the $currentSemester semester. Unfortunately, either the class was full for this semester or there 
was no advisor available for your requested class choices. You have been 
automatically signed up for the $nextSemester $student_level class and will have priority for that class.</p>
<table style='border:4px solid red;'><tr><td>
<p>You will receive an email around $thisWhen asking you to review and update your class preferences. 
You MUST respond to that email in order to be considered for assignment to a class.<br /><br /> 
If this re-assignment doesn't work for you, please click <a href='$declineMoveURL?enstr=$enstr' 
target='_blank'><b>decline</b></a>. Then you can re-register at your convenience in the future.</p>
</td></tr></table>
<p>We look forward to seeing you in the $nextSemester semester!</p>
<p>73,<br />
CW Academy</p>
<br /><p><span style='color:red;font-size:medium;'><b>Do not reply to this email as the address is not monitored. 
<br />Please refer to the appropriate person at <a href='https://cwops.org/cwa-class-resolution/'>CWA Class 
Resolution</a> for assistance.</b></span><br /></p>";

								$sendEmail			= TRUE;
							}

//////////////////////						
						
						} elseif ($inp_type == 'NOTIFY') {				//// type run is NOTIFY					
							$studentPromoInfo		= 'Notified and will Possibly be moved to the next semester';
							$promoString			= "Otherwise CW Academy will update you again early in the semester and 
automatically move your registration to $nextSemester semester.";
							if ($student_class_priority > 0) {
								$studentPromoInfo	= 'Notified Only. Will not be moved to the next semester';
								$promoString		= "Otherwise CW Academy will update you again with another email early in the semester.";
							}
							$content	.= "<tr><td>$student_call_sign</td>
												<td>$student_last_name, $student_first_name</td>
												<td>$student_city</td>
												<td>$student_state</td>
												<td>$student_country</td>
												<td>$student_phone</td>
												<td>$student_email</td>
												<td>$the_level</td>
												<td>$student_class_priority</td></tr>
												<tr><td colspan='9'>$student_action_log
												<br /><br />$studentPromoInfo</td></tr>";
							$my_subject	= "CW Academy Update to your Registration Request";	
							$my_message = "<p>To: $student_last_name, $student_first_name ($student_call_sign):</p>
<p>Thank you for your interest in attending the $student_level CW Academy class 
for the $currentSemester semester. Unfortunately, there was no class available that meets your time preferences 
or the number of students for the semester exceeds the available seats 
in the class you requested. You have been placed on the waiting list. Some students may drop out before the semester starts or during the first 
week of classes and CW Academy draws from the waiting list to back-fill. If you are selected to fill one of the 
open seats, you'll be notified by the advisor. $promoString</p>
<p>73,<br />
CW Academy</p>
<br /><p><span style='color:red;font-size:medium;'><b>Do not reply to this email as the address is not monitored. 
<br />Please refer to the appropriate person at <a href='https://cwops.org/cwa-class-resolution/'>CWA Class 
Resolution</a> for assistance.</b></span><br /></p>";
							$sendEmail 	= TRUE;
					
						} else {
							if ($doDebug) {
								echo "inp_type of $inp_type was invalid<br />";
							}
						}
						if ($sendEmail) {
							// send the email
							if ($testMode) {
								$theRecipient	= "rolandksmith@gmail.com";
								$my_subject		= "TESTMODE $my_subject";
								$my_message 	= "<p>Email would have been sent to $student_email ($student_call_sign)</p>$my_message";
								$mailCode		= 2;
								$increment++;
							} else {
								$theRecipient	= $student_email;
								$mailCode		= 13;
							}
						
							$mailResult		= emailFromCWA_v2(array('theRecipient'=>$theRecipient,
																		'theSubject'=>$my_subject,
																		'jobname'=>$jobname,
																		'theContent'=>$my_message,
																		'mailCode'=>$mailCode,
																		'increment'=>$increment,
																		'testMode'=>$testMode,
																		'doDebug'=>$doDebug));
							if ($mailResult === TRUE) {
								if ($doDebug) {
									echo "An email was sent to $theRecipient<br />";
								}
								if ($inp_type == 'NOTIFY') {
									$content .= "<tr><td colspan='10'>An email was sent to $theRecipient</td></tr>
									<tr><td colspan='10'><hr></td></tr>";
								} else {
									$content .= "<tr><td colspan='8'>An email was sent to $theRecipient $updateStr</td></tr>
									<tr><td colspan='8'><hr></td></tr>";
								}
								$emailsSent++;
							} else {
								if ($doDebug) {
									echo "The email function failed for $theRecipient ($student_call_sign)" . $wp_error->get_error_message() . "<br />";
								}
								if ($inp_type == 'NOTIFY') {
									$content .= "<tr><td colspan='8'>The mail send function to $theRecipient failed.</td></tr>
									<tr><td colspan='8'><hr></td></tr>";
								} else {
									$content .= "<tr><td colspan='8'>The mail send function to $theRecipient failed. The table was updated.</td></tr>
									<tr><td colspan='10'><hr></td></tr>";
								}
							}
							$sendEmail			= FALSE;
						}
					}			/// end of student while
				}				
			} else {
				if ($doDebug) {
					echo "No $studentTableName students. Aborting.";
				}
			}
		}
		$content		.= "</table><p>$numSRows Total Records Processed<br />
							$emailsSent: Emails were sent</p>";
		if ($inp_type == 'NOTIFY') {
			$content	.= "$notify: students notified, but won't be moved<br />
							$notifyAndMove: students notified that they will possibly be moved<br />";
		} else {
			$content	.= "$studentsMoved: Students moved to the $nextSemester semester<br />
							$studentsBypassed0: Students bypassed due to class priority greater than 0<br />
							$studentsBypassedH: Students bypassed due to being on hold<br />";
		}
	}
	$thisTime 		= date('Y-m-d H:i:s');
	$content		.= "<br /><br /><p>Report displayed at $thisTime.</p>";
	if ($doDebug) {
		echo "<br />Testing to save report: $inp_report<br />";
	}
	if ($inp_report == 'Y') {
		if ($doDebug) {
			echo "Calling function to save the report as Move Unassigned Students to Next Semester<br />";
		}
		$storeResult	= storeReportData_func("Move Unassigned Students to Next Semester",$content);
		if ($storeResult !== FALSE) {
			$content	.= "<br />Report stored in reports table as $storeResult";
		} else {
			$content	.= "<br />Storing the report in the reports table failed";
		}
	}	
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
add_shortcode ('move_unassigned_students_to_next_semester', 'move_unassigned_students_to_next_semester_func');
