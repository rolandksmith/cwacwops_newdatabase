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
 Modified 20Oct24 by Roland for new database
*/

	global $wpdb;


	$doDebug 				= TRUE;
	$testMode				= FALSE;
	$initializationArray 	= data_initialization_func();
	$validUser 				= $initializationArray['validUser'];
	$userName  				= $initializationArray['userName'];
	$validTestmode			= $initializationArray['validTestmode'];
	$siteURL				= $initializationArray['siteurl'];
	$currentSemester		= $initializationArray['currentSemester'];
	$nextSemester			= $initializationArray['nextSemester'];

	if ($userName == '') {
		return "YOU'RE NOT AUTHORIZED!<br />Goodby";
	}
	if ($doDebug) {
		echo "Initialization Array:<br /><pre>";
		print_r($initializationArray);
		echo "</pre><br />";
	}
	ini_set('max_execution_time',0);

		ini_set('display_errors','1');
		error_reporting(E_ALL);	

/// get the time that the process started
	$startingMicroTime			= microtime(TRUE);

// initial values	
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

	$content = "";


	if ($testMode) {
		$studentTableName	= "wpw1_cwa_student2";
		$userMasterTableName	= 'wpw1_cwa_user_master2';
		$run_mode		= 'tm';
		$content 		.= "<p><strong>Function under development.</strong></p>";
		if ($doDebug) {
			echo "Function under development.<br />";
		}
	} else {
		$studentTableName	= "wpw1_cwa_student";
		$userMasterTableName	= 'wpw1_cwa_user_master';
		$run_mode		= 'pm';
	}
		


	if ("1" == $strPass) {
		if (in_array($userName,$validTestmode)) {			// give option to run in test mode 
			if ($doDebug) {
				echo "<br />offering testModeOption<br />";
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
		if ($doDebug) {
			echo "<br />At Pass 2. Current semester: $currentSemester.<br />
			Next Semester: $nextSemester<br />
			Type of run: $inp_type<br />";
		}
		$content .= "<h3>$jobname</h3>";
		$doProceed					= TRUE;
		if ($inp_type != 'NOTIFY' && $inp_type != 'MOVE') {
			$content		.= "Type of run is invalid. Function aborted.";
			$doProceed		= FALSE;
		}
		if ($doProceed) {		
			$currentSemester			= $initializationArray['currentSemester'];
			$nextSemester				= $initializationArray['nextSemester'];
			$semesterTwo				= $initializationArray['semesterTwo'];
			if ($currentSemester == "Not in Session") {
				if ($inp_type == 'NOTIFY') {
					$currentSemester			= $nextSemester;
					$nextSemester				= $semesterTwo;
				} else {
					$content			.= "There isn't a semester in session so MOVE is not a valid option.";
					$doProceed			= FALSE;
				}
			} else {
				if ($inp_type != 'MOVE') {
					$content			.= "The semester is in session. MOVE is the appropriate option.";
					$doProceed			= FALSE;
				}
			}
		}
		if ($doProceed) {
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
									left join $userMasterTableName on user_call_sign = student_call_sign 
									where student_semester='$currentSemester' 
										and student_response='Y' 
										and (student_status='' 
											 or student_status='U') 
									order by student_call_sign";
			$wpw1_cwa_student	= $wpdb->get_results($sql);
			if ($wpw1_cwa_student === FALSE) {
				handleWPDBError($jobname,$doDebug);
			} else {
				$numSRows		= $wpdb->num_rows;
				if ($doDebug) {
					echo "ran $sql<br />and retrieved $numSRows rows from $studentTableName table<br />";
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
							$student_master_ID 					= $studentRow->user_ID;
							$student_master_call_sign 			= $studentRow->user_call_sign;
							$student_first_name 				= $studentRow->user_first_name;
							$student_last_name 					= $studentRow->user_last_name;
							$student_email 						= $studentRow->user_email;
							$student_ph_code 					= $studentRow->user_ph_code;
							$student_phone 						= $studentRow->user_phone;
							$student_city 						= $studentRow->user_city;
							$student_state 						= $studentRow->user_state;
							$student_zip_code 					= $studentRow->user_zip_code;
							$student_country_code 				= $studentRow->user_country_code;
							$student_country 					= $studentRow->user_country;
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
							$student_parent 						= $studentRow->student_parent;
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
							$student_student_survey_completion_date	= $studentRow->student_survey_completion_date;
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
					
							$sendEmail								= FALSE;
							$promoString							= '';
							if ($doDebug) {
								echo  "<br />Processing $student_call_sign<br />
										&nbsp;&nbsp;&nbsp;&nbsp;Response: $student_response<br />
										&nbsp;&nbsp;&nbsp;&nbsp;Status: $student_status<br />
										&nbsp;&nbsp;&nbsp;&nbsp;Class Priority: $student_class_priority<br />";
							}				
							$updateStr		= '';
							$the_level		= substr($student_level,0,3);
	
							$studentCount++;
							if ($doDebug) {
								echo "inp_type is $inp_type<br />";
							}
					
							if ($inp_type == 'MOVE') {
								if ( $doDebug) {
									echo "Updating current semester student record<br />";
								}
								$myStr					= "$student_action_log / $myDate REASSIGN $userName student moved to $nextSemester semester ";
								$updateParams			= array('student_action_log'=>$myStr, 
																'student_status'=>'U');
								$updateFormat			= array('%s','%s');

								$studentUpdateData		= array('tableName'=>$studentTableName,
																'inp_method'=>'update',
																'inp_data'=>$updateParams,
																'inp_format'=>$updateFormat,
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
									$content		.= "Unable to update content in $studentTableName<br />";
								} else {
									if ($doDebug) {
										echo "current semester action log updated. Setting up next semester record<br />";
									}
									$thisOffset		= getOffsetFromIdentifier($student_timezone_id,$nextSemester,$doDebug);
									$student_action_log		.= " / $myDate REASSIGN $userName Student record moved from $currentSemester to $nextSemester ";
									$updateParams			= array('student_call_sign'=>$student_call_sign,
																	'student_time_zone'=>$student_time_zone,
																	'student_timezone_offset'=>$thisOffset,
																	'student_youth'=>$student_youth,
																	'student_age'=>$student_age,
																	'student_parent'=>$student_parent,
																	'student_parent_email'=>$student_parent_email,
																	'student_level'=>$student_level,
																	'student_waiting_list'=>'',
																	'student_request_date'=>'',
																	'student_semester'=>$nextSemester,
																	'student_notes'=>$student_notes,
																	'student_welcome_date'=>'',
																	'student_email_sent_date'=>'',
																	'student_email_number'=>0,
																	'student_response'=>'',
																	'student_response_date'=>'',
																	'student_abandoned'=>'N',
																	'student_status'=>'',
																	'student_action_log'=>$student_action_log,
																	'student_pre_assigned_advisor'=>'',
																	'student_selected_date'=>'',
																	'student_no_catalog'=>'Y',
																	'student_hold_override'=>'',
																	'student_assigned_advisor'=>'',
																	'student_advisor_select_date'=>'',
																	'student_advisor_class_timezone'=>'',
																	'student_hold_reason_code'=>$student_hold_reason_code,
																	'student_class_priority'=>1,
																	'student_assigned_advisor_class'=>0,
																	'student_promotable'=>'',
																	'student_excluded_advisor'=>$student_excluded_advisor,
																	'student_survey_completion_date'=>'',
																	'student_available_class_days'=>'',
																	'student_intervention_required'=>$student_intervention_required,
																	'student_copy_control'=>'',
																	'student_first_class_choice'=>'None',
																	'student_second_class_choice'=>'None',
																	'student_third_class_choice'=>'None',
																	'student_first_class_choice_utc'=>'None',
																	'student_second_class_choice_utc'=>'None',
																	'student_third_class_choice_utc'=>'None',
																	'student_catalog_options'=>'',
																	'student_flexible'=>'');
							
									$updateFormat			= array('%s',
																	'%s',
																	'%f',
																	'%s',
																	'%s',
																	'%s',
																	'%s',
																	'%s',
																	'%s',
																	'%s',
																	'%s',
																	'%s',
																	'%s',
																	'%s',
																	'%d',
																	'%s',
																	'%s',
																	'%s',
																	'%s',
																	'%s',
																	'%s',
																	'%s',
																	'%s',
																	'%s',
																	'%s',
																	'%s',
																	'%s',
																	'%s',
																	'%d',
																	'%d',
																	'%s',
																	'%s',
																	'%s',
																	'%s',
																	'%s',
																	'%s',
																	'%s',
																	'%s',
																	'%s',
																	'%s',
																	'%s',
																	'%s',
																	'%s',
																	'%s');
							
									$studentUpdateData		= array('tableName'=>$studentTableName,
																	'inp_method'=>'add',
																	'inp_data'=>$updateParams,
																	'inp_format'=>$updateFormat,
																	'jobname'=>$jobname,
																	'inp_id'=>0,
																	'inp_callsign'=>$student_call_sign,
																	'inp_semester'=>$nextSemester,
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
										$new_student_ID	= $updateResult[1];					

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
															<td>$currentSemester</td>
															<td>$nextSemester</td>
															<td>$student_class_priority</td>
															<td>1</td>
															<td>$student_intervention_required</td>
															<td></td>
														</tr><tr>
															<td colspan='7'>$newActionLog</td></tr>";
																
										if ($doDebug) {
											echo "current student record updated. Preparing to send email<br />";
										}
										
										$studentsMoved++;
										$stringToPass	= "studentid=$new_student_ID&inp_mode=$run_mode&program_action=valid";
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
