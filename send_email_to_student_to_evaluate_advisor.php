function send_email_to_student_to_evaluate_advisor_func() {

/* Send Email to Student to Evaluate Advisor
 * 
 * Reads Class pod for the recent semester and formulates an email
 * to each student in the class asking the student to do an evaluation
 * of the class advisor.
 *
 * The email contains a link to the web page 
 * which has the evaluation form
 *
 * The function will send an email to each student in the semester which can be anywhere 
 * from 300 - 800 emails. The function will time out trying to send that many emails, so 
 * the function writes a date/time to the past_student.student_survey_completion_date for 
 * each email sent and will send up to 100 emails per execution. It will skip any students 
 * that have a student_survey_completion_date. Run the function over again until no emails
 * are sent
 *
 * Created 16 May 2020 by Roland
 	Modified 2Nov2021 by Roland to accomodate testMode
 	Modified 26Oct22 by Roland to accomodate new timezone table formats
 	Modified 17Apr23 by Roland to fix action_log
 	Modified 16June23 by Roland to use current tables rather than past tables
 	Modified 14Jul23 by Roland to use consolidated tables
 	Modified 2Mar24 by Roland to use send reminder email to the students
 	Modified 6Oct24 by Roland for the new database
 *
*/

	global $wpdb;

	$doDebug				= FALSE;
	$testMode				= FALSE;

	$initializationArray 	= data_initialization_func();
	if ($doDebug) {
		echo "Initialization Array:<br /><pre>";
		print_r($initializationArray);
		echo "</pre><br />";
	}
	$validUser = $initializationArray['validUser'];
	$userName			= $initializationArray['userName'];
	$validTestmode		= $initializationArray['validTestmode'];
	$siteURL			= $initializationArray['siteurl'];
	$currentSemester	= $initializationArray['currentSemester'];
	$prevSemester		= $initializationArray['prevSemester'];

	if ($userName == '') {
		return "You are not authorized";
	}

	if ($currentSemester != 'Not in Session') {
		$theSemester	= $currentSemester;
	} else {
		$theSemester	= $prevSemester;
	}

	ini_set('memory_limit','256M');
	ini_set('max_execution_time',0);
//	if ($doDebug) {
		ini_set('display_errors','1');
		error_reporting(E_ALL);	
//	}
	$myInt					= 100;
	$myDate					= date('dMy H:i');
	$totalStudents			= 0;
	$increment				= 0;
	$theURL					= "$siteURL/cwa-send-email-to-student-to-evaluate-advisor/";
	$evaluateAdvisorURL		= "$siteURL/cwa-student-evaluation-of-advisor/";
	$jobname				= 'Send Email to Student to Evaluate Advisor';

/// get the time that the process started
	$startingMicroTime			= microtime(TRUE);

	$strPass					= "0";
	$requestType				= '';
	$advisorsProcessed			= 0;
	$emailsSent					= 0;

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
			if ($str_key 		== "inp_verbose") {
				$inp_verbose	 = $str_value;
				$inp_verbose	 = filter_var($inp_verbose,FILTER_UNSAFE_RAW);
				if ($inp_verbose == 'Y') {
					$doDebug	= TRUE;
				}
			}
			if ($str_key 		== "inp_mode") {
				$inp_mode	 = $str_value;
				$inp_mode	 = filter_var($inp_mode,FILTER_UNSAFE_RAW);
				if ($inp_mode == 'TESTMODE') {
					$testMode = TRUE;
				}
			}
			if ($str_key 		== "inp_selected") {
				$inp_selected	 = $str_value;
				$inp_selected	 = filter_var($inp_selected,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "strpass") {
				$strPass	 = $str_value;
				$strPass	 = filter_var($strPass,FILTER_UNSAFE_RAW);
			}
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
		$extMode					= 'tm';
		$advisorClassTableName	= "wpw1_cwa_advisorclass2";
		$studentTableName		= "wpw1_cwa_student2";
		$advisorTableName		= "wpw1_cwa_advisor2";
		$userMasterTableName	= 'wpw1_cwa_user_master2';
		if ($doDebug) {
			echo "<strong>Operating in Test Mode against class2, student2, and advisor2</strong><br />";
		}
		$content	.= 	"<p><strong>Operating in Test Mode against class2, student2, and advisor2</strong></p>";
	} else {
		$extMode					= 'pd';
		$advisorClassTableName	= "wpw1_cwa_advisorclass";
		$studentTableName		= "wpw1_cwa_student";
		$advisorTableName		= "wpw1_cwa_advisor";
		$userMasterTableName	= 'wpw1_cwa_user_master';
	}

	if ("0" == $strPass) {
		if ($doDebug) {
			echo "<br />at pass 0<br />";
		}
		$content		.= "<h3>$jobname</h3>
							<p>Options:
							<ol>
							<li>To prepare the student table to send the evaluation request 
								click <a href='$theURL/?strpass=200'>HERE</a>
							<ul><li>This will clear out the student_survey_completion_date 
									so that all qualified students will receive the evaluation 
									request when option 2 is run
								<li><em>NOTE:</em> Run this option only when you intend to send 
									evaluation messages to all qualified students
							</ul>
							<br />
							<li>To send the evaluation request emails to qualified students, click 
							<a href='$theURL/?strpass=1'>HERE</a>
							<ul><li>This will send the evaluation request to each qualified student 
									with a blank student_survey_completion_date
							</ul>
							<br />
							<li>To select specific students to get the evaluation email, click 
							<a href='$theURL/?strpass=100'>HERE</a>
							<ul><li>This will clear out the student_survey_completion_date in the 
									specified student records and then will allow you to send the 
									evaluation request emails
								<li><em>NOTE</em> Emails will be sent to any qualified student with 
									a blank student_survey_completion_date
							</ul>
							</ul>
							<br />
							<p>Qualified students are those assigned to a class, have 
							a student_status of 'Y' and a student_promotable of either 'P' 
							or 'W'</p>";
	}

	if ("1" == $strPass) {
		if ($doDebug) {
			echo "<br />Function starting.<br />";
		}

		$content 		.= "<h3>$jobname Setup</h3>
							<p>Reads advisorClass table for the recent semester and formulates an email 
							to each student in the class asking the student to do an evaluation 
							of the class advisor and the curriculum.</p>
							<p>The email contains a link to the CW Academy web page Student Portal which will have 
							the reminder giving the student the link to do the actual evaluation form.</p>
							<p>The function will send an email to each student in the semester which can be anywhere 
							from 300 - 800 emails. The function will time out trying to send that many emails, so 
							the function writes a date/time to the student.student_survey_completion_date for 
							each email sent and will send up to 100 emails per execution. It will skip any students 
							that have a student_survey_completion_date.</p>
							<p>If after sending up to 100 emails there are still students that have not been 
							sent the evaluation request, there will be a link to run the process again 
							and send up to another 100 emails.</p>
							<p><form method='post' action='$theURL' 
							name='selection_form' ENCTYPE='multipart/form-data''>
							<input type='hidden' name='strpass' value='2'>
							<table style='border-collapse:collapse;'>
							$testModeOption
							<tr><td colspan='2'><input class='formInputButton' type='submit' value='Submit' /></td></tr></table>
							</form></p>";

///// Pass 2 -- do the work


	} elseif ("2" == $strPass) {
		if ($doDebug) {
			echo "<br />Arrived at pass 2<br />";
		}
		
		$content			.= "<h3>$jobname</h3>";
		$thisErrors			= 0;

// Read the advisorClass table and process each student in the pod

		$sql						= "select * from $advisorClassTableName 
									left join $userMasterTableName on user_call_sign = advisorclass_call_sign 
										where advisorclass_semester='$theSemester' 
										order by advisorclass_call_sign";
		$wpw1_cwa_advisorclass	= $wpdb->get_results($sql);
		if ($wpw1_cwa_advisorclass === FALSE) {
			handleWPDBError($jobname,$doDebug);
		} else {
			$numACRows				= $wpdb->num_rows;
			if ($doDebug) {
				echo "ran $sql<br />and found $numACRows rows in $advisorClassTableName table<br />";
			}
			if ($numACRows > 0) {
				foreach ($wpw1_cwa_advisorclass as $advisorClassRow) {
					$advisorClass_master_ID 				= $advisorClassRow->user_ID;
					$advisorClass_master_call_sign			= $advisorClassRow->user_call_sign;
					$advisorClass_first_name 				= $advisorClassRow->user_first_name;
					$advisorClass_last_name 				= $advisorClassRow->user_last_name;
					$advisorClass_email 					= $advisorClassRow->user_email;
					$advisorClass_ph_code					= $advisorClassRow->user_ph_code;
					$advisorClass_phone 					= $advisorClassRow->user_phone;
					$advisorClass_city 						= $advisorClassRow->user_city;
					$advisorClass_state 					= $advisorClassRow->user_state;
					$advisorClass_zip_code 					= $advisorClassRow->user_zip_code;
					$advisorClass_country_code 				= $advisorClassRow->user_country_code;
					$advisorClass_country					= $advisorClassRow->user_country;
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

					$advisorClass_ID				 		= $advisorClassRow->advisorclass_id;
					$advisorClass_call_sign 				= $advisorClassRow->advisorclass_call_sign;
					$advisorClass_sequence 					= $advisorClassRow->advisorclass_sequence;
					$advisorClass_semester 					= $advisorClassRow->advisorclass_semester;
					$advisorClass_timezone_offset			= $advisorClassRow->advisorclass_timezone_offset;	// new
					$advisorClass_level 					= $advisorClassRow->advisorclass_level;
					$advisorClass_class_size 				= $advisorClassRow->advisorclass_class_size;
					$advisorClass_class_schedule_days 		= $advisorClassRow->advisorclass_class_schedule_days;
					$advisorClass_class_schedule_times 		= $advisorClassRow->advisorclass_class_schedule_times;
					$advisorClass_class_schedule_days_utc 	= $advisorClassRow->advisorclass_class_schedule_days_utc;
					$advisorClass_class_schedule_times_utc 	= $advisorClassRow->advisorclass_class_schedule_times_utc;
					$advisorClass_action_log 				= $advisorClassRow->advisorclass_action_log;
					$advisorClass_class_incomplete 			= $advisorClassRow->advisorclass_class_incomplete;
					$advisorClass_date_created				= $advisorClassRow->advisorclass_date_created;
					$advisorClass_date_updated				= $advisorClassRow->advisorclass_date_updated;
					$advisorClass_student01 				= $advisorClassRow->advisorclass_student01;
					$advisorClass_student02 				= $advisorClassRow->advisorclass_student02;
					$advisorClass_student03 				= $advisorClassRow->advisorclass_student03;
					$advisorClass_student04 				= $advisorClassRow->advisorclass_student04;
					$advisorClass_student05 				= $advisorClassRow->advisorclass_student05;
					$advisorClass_student06 				= $advisorClassRow->advisorclass_student06;
					$advisorClass_student07 				= $advisorClassRow->advisorclass_student07;
					$advisorClass_student08 				= $advisorClassRow->advisorclass_student08;
					$advisorClass_student09 				= $advisorClassRow->advisorclass_student09;
					$advisorClass_student10 				= $advisorClassRow->advisorclass_student10;
					$advisorClass_student11 				= $advisorClassRow->advisorclass_student11;
					$advisorClass_student12 				= $advisorClassRow->advisorclass_student12;
					$advisorClass_student13 				= $advisorClassRow->advisorclass_student13;
					$advisorClass_student14 				= $advisorClassRow->advisorclass_student14;
					$advisorClass_student15 				= $advisorClassRow->advisorclass_student15;
					$advisorClass_student16 				= $advisorClassRow->advisorclass_student16;
					$advisorClass_student17 				= $advisorClassRow->advisorclass_student17;
					$advisorClass_student18 				= $advisorClassRow->advisorclass_student18;
					$advisorClass_student19 				= $advisorClassRow->advisorclass_student19;
					$advisorClass_student20 				= $advisorClassRow->advisorclass_student20;
					$advisorClass_student21 				= $advisorClassRow->advisorclass_student21;
					$advisorClass_student22 				= $advisorClassRow->advisorclass_student22;
					$advisorClass_student23 				= $advisorClassRow->advisorclass_student23;
					$advisorClass_student24 				= $advisorClassRow->advisorclass_student24;
					$advisorClass_student25 				= $advisorClassRow->advisorclass_student25;
					$advisorClass_student26 				= $advisorClassRow->advisorclass_student26;
					$advisorClass_student27 				= $advisorClassRow->advisorclass_student27;
					$advisorClass_student28 				= $advisorClassRow->advisorclass_student28;
					$advisorClass_student29 				= $advisorClassRow->advisorclass_student29;
					$advisorClass_student30 				= $advisorClassRow->advisorclass_student30;
					$advisorClass_number_students			= $advisorClassRow->advisorclass_number_students;
					$advisorClass_class_evaluation_complete = $advisorClassRow->advisorclass_evaluation_complete;
					$advisorClass_class_comments			= $advisorClassRow->advisorclass_class_comments;
					$advisorClass_copycontrol				= $advisorClassRow->advisorclass_copy_control;

					if ($doDebug) {
						echo "<br />Processing $advisorClassTableName $advisorClass_call_sign<br />";
					}
					$advisorName		= "$advisorClass_first_name  $advisorClass_last_name";
					// cycle through thru all students in the class and output the information
					for ($snum=1;$snum<=$advisorClass_number_students;$snum++) {
						if ($snum < 10) {
							$strSnum 		= str_pad($snum,2,'0',STR_PAD_LEFT);
						} else {
							$strSnum		= strval($snum);
						}
						$theInfo			= ${'advisorClass_student' . $strSnum};
						if ($doDebug) {
							echo "processing student $strSnum whose info is $theInfo<br />";
						}
						if ($theInfo != '') {
							$totalStudents++;
							$studentCallSign = trim($theInfo);
							// Get the student info from student table
							$sql			= "select * from $studentTableName 
												left join $userMasterTableName on user_call_sign = student_call_sign 
												where student_semester='$theSemester' 
												and student_call_sign='$studentCallSign' 
												and student_assigned_advisor='$advisorClass_call_sign' 
												and student_assigned_advisor_class='$advisorClass_sequence'";
							$wpw1_cwa_student	= $wpdb->get_results($sql);
							if ($wpw1_cwa_student === FALSE) {
								handelWPDBError($jobname,$doDebug);
							} else {
								$numPSRows									= $wpdb->num_rows;
								if ($doDebug) {
									echo "ran $sql<br />and found $numPSRows rows in $studentTableName table<br />";
								}
								if ($numPSRows > 0) {
									foreach ($wpw1_cwa_student as $studentRow) {
										$student_master_ID 					= $studentRow->user_ID;
										$student_master_call_sign 			= $studentRow->user_call_sign;
										$student_first_name 				= $studentRow->user_first_name;
										$student_last_name 					= $studentRow->user_last_name;
										$student_email 						= $studentRow->user_email;
										$student_ph_code					= $studentRow->user_ph_code;
										$student_phone 						= $studentRow->user_phone;
										$student_city 						= $studentRow->user_city;
										$student_state 						= $studentRow->user_state;
										$student_zip_code 					= $studentRow->user_zip_code;
										$student_country_code 				= $studentRow->user_country_code;
										$student_country					= $studentRow->user_country;
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
										$student_survey_completion_date	= $studentRow->student_survey_completion_date;
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

										$studentQualifies			= FALSE;
										if ($student_status == 'Y') {			// status must be y
											if ($student_promotable == 'P' || $student_promotable == 'N') {
												if ($student_survey_completion_date == "") {		// do not send if already sent
													if ($myInt > 0) {
														$studentQualifies	= TRUE;
														// formulate and send the email to the student
//														$theURL	= "click <a href='$evaluateAdvisorURL?inp_student=$student_call_sign&strpass=2&extMode=$extMode' target='_blank'>Student Evaluation Survey</a>";
														if ($doDebug) {
															echo "Have all the data to formulate and send an email to<br />
																  Student: $student_last_name, $student_first_name ($student_call_sign)<br />
																  Email: $student_email<br />
																  Status: $student_status<br />
																  Level: $advisorClass_level<br />
																  Survey: $student_survey_completion_date<br />
																		  Advisor: $advisorName ($advisorClass_call_sign)<br /><br />";
														}
														$my_message		= '';
														$my_subject 	= "CW Academy -- Request to Evaluate Your Recent Class, Curriculum, and Advisor";
														if ($testMode) {		// no emails to students!
															$my_to		= "kcgator@gmail.com";
															$mailCode	= 2;
															$increment++;
															$my_message .= "<p>Email would have been sent to $student_email ($student_call_sign)</p>";
															$my_subject	= "TESTMODE $my_subject";
														} else {
															$my_to		= $student_email;
															$mailCode	= 15;
														}
														$returnArray		= wp_to_local($student_timezone_id , 0, 14);
														if ($returnArray === FALSE) {
															if ($doDebug) {
																echo "called wp_to_local with $student_timezone_id , 0, 14 which returned FALSE<br />";
															} else {
																sendErrorEmail("$jobname calling wp_to_local with $advisor_tz_id, 0, 5 returned FALSE");
															}
															$effective_date		= date('Y-m-d 00:00:00');
															$closeStr			= strtotime("+ 14 days");
															$close_date			= date('Y-m-d 00:00:00',$closeStr);
														} else {
															$effective_date		= $returnArray['effective'];
															$close_date			= $returnArray['expiration'];
														}

														
														
														$currentDate	= date('Y-m-d H:i:s');
														$expireDate		= $close_date;
														$my_message 	.= "<p>To: $student_last_name, $student_first_name ($student_call_sign):</p>
<p>Thank you for your participating in the $advisorClass_level CW Acadamy class with advisor $advisorName ($advisorClass_call_sign). As the semester is concluding, 
the CW Academy would like your opinion on the class, curriculum, and your advisor.</p>
<p>The survey will take just a few minutes and your input will help CW Academy continue to innovate and improve.</p>
<table style='border:4px solid red;'><tr><td><p>Please go to <a href='$siteURL/program-list/'>CW Academy</a> and fill out the survey linked in the Reminder 
at the top of your Student Portal. <b>NOTE: </b>The link to the survey will expire in two weeks.</p></td></tr></table>
<p>Do not reply to this email as the mailbox is not monitored.</p>
<p>Thanks and 73,<br />
CW Academy</p>";
				
														$mailResult		= emailFromCWA_v2(array('theRecipient'=>$my_to,
																									'theSubject'=>$my_subject,
																									'jobname'=>$jobname,
																									'theContent'=>$my_message,
																									'mailCode'=>$mailCode,
																									'increment'=>$increment,
																									'testMode'=>$testMode,
																									'doDebug'=>$doDebug));
														if ($mailResult === TRUE) {
															if ($doDebug) {
																echo "A email was sent to $my_to<br /><br />";
															}
															$content .= "A survey request email was sent to $my_to ($student_call_sign).<br />";
															$emailsSent++;
															$myInt--;
															
															// set up the reminder
															$token					= mt_rand();
															$email_text				= "<p></p>";
															$reminder_text			= "<b>Evaluate Class, Curriculum, and Advisor:</b> CW Academy is asking you to 
																						fill out a survey form evaluating your class, the curriculum, and the 
																						advisor. To fill out the survey, please click 
																						<a href='$evaluateAdvisorURL?inp_student=$student_call_sign&strpass=2&extMode=$extMode&token=$token' target='_blank'>Student Evaluation of Advisor</a>.
																						The link to the survey will expire on $expireDate.";
															$inputParams		= array("effective_date|$effective_date|s",
																						"close_date|$close_date|s",
																						"resolved_date||s",
																						"send_reminder||s",
																						"send_once|Y|s",
																						"call_sign|$student_call_sign|s",
																						"role||s",
																						"email_text|$email_text|s",
																						"reminder_text|$reminder_text|s",
																						"resolved||s",
																						"token|$token|s");
															$insertResult		= add_reminder($inputParams,$testMode,$doDebug);
															if ($insertResult[0] === FALSE) {
																handleWPDBError($jobname,$doDebug);
															} else {
																$content		.= "Reminder successfully added<br />";
															}
				
															$student_action_log	= "$student_action_log / $myDate ADVISOREVAL Email sent to student requesting advisor evaluation ";
															$studentUpdateData		= array('tableName'=>$studentTableName,
																							'inp_method'=>'update',
																							'inp_data'=>array('student_survey_completion_date'=>$myDate,
																											  'student_action_log'=>$student_action_log),
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
																$content		.= "Unable to update content in $studentTableName<br />";
															} else {
																if ($doDebug) {
																	echo "Successfully updated $studentTableName record at $student_ID<br />";
																}
															}
														} else {
															if ($doDebug) {
																echo "The email function failed for $my_to ($student_call_sign)<br />";
															}
															$content .= "The email function failed for $my_to ($student_call_sign)<br />";
														}
													}
												}
											}
										}
										if (!$studentQualifies) {
											if ($doDebug) {
												echo "Student does not meet the criteria:<br />
													  Student: $student_last_name, $student_first_name ($student_call_sign)<br />
													  Email: $student_email<br />
													  Status: $student_status<br />
													  Level: $advisorClass_level<br />
													  Survey: $student_survey_completion_date<br />";
											}
										}
									}
								} else {
								
									$content	.= "<p>No record for student $studentCallSign in $studentTableName. Advisor: $advisorClass_call_sign $advisorClass_sequence</p>";
									$thisErrors++;
								}
							}
						}
					}
				}		// end of foreach loop
				$content		.= "<p>$emailsSent Emails sent<br />
									$thisErrors Errors found</p><br />";
				// see if there are any students left
				$studentsLeft	= $wpdb->get_var("select count(*) 
												from $studentTableName 
												where student_semester = '$theSemester' 
												and (student_promotable = 'P' 
												or student_promotable = 'N')  
												and student_survey_completion_date = '' ");
				if ($studentsLeft === FALSE) {
					handleWPDBError($joblog,$doDebug,"trying to get count of students still to get the email");
				} else {
					$studentsLeft		= $studentsLeft - $thisErrors;
					if ($studentsLeft > 0) {
						$content	.= "There are $studentsLeft emails to send. 
										Click <a href='$theURL/?strpass=2&inp_verbose=$inp_verbose&inp_mode=$inp_mode'>HERE</a> to 
										send the next batch of emails";
					} else {
						$content	.= "All emails have been sent";
					}
				}
			} else {
				$content	.= "<p>No records found in $advisorClassTableName table</p>";
			}			// end of numberRecords section
		}
	} elseif ("100" == $strPass) {
		if ($doDebug) {
			echo "<br />at pass 100<br />";
		}
		$content		.= "<h3>$jobname</h3>
							<form method='post' action='$theURL' 
							name='selection_form' ENCTYPE='multipart/form-data''>
							<input type='hidden' name='strpass' value='110'>
							<table style='border-collapse:collapse;'>
							<tr><td style='vertical-align:top;width:200px;'>Enter a comma-separated list
							of student call signs to be sent the class evaluation email (<em>no 
							spaces and no trailing comma</em>)</td>
							<td><textarea class='formInputText' name='inp_selected' rows='5' cols = '50'></textarea></td></tr>
							$testModeOption
							<tr><td colspan='2'><input class='formInputButton' type='submit' value='Submit' /></td></tr></table>
							</form></p>";
		
	} elseif ("110" == $strPass) {
		if ($doDebug) {
			echo "<br />at pass 110 with inp_selected of $inp_selected<br />";
		}
		$content		.= "<h3>$jobname</h3>";
		// figure out which requested students are qualified
		$myArray		= explode(",",$inp_selected);
		foreach($myArray as $thisCallsign) {
			$sql		= "select * from $studentTableName 
							where student_call_sign like '$thisCallsign' 
							and student_semester = '$theSemester'";
			$studentResult	= $wpdb->get_results($sql);
			if ($studentResult === FALSE) {
				handleWPDBError($jobname,$doDebug,"attempting to qualify $thisCallsign");
				$content	.= "Unable to retrieve data for student $thisCallsign<br />";
			} else {
				$numSRows	= $wpdb->num_rows;
				if ($doDebug) {
					echo "ran $sql<br />and retrieved $numSRows rows<br />";
				}
				if ($numSRows > 0) {
					foreach($studentResult as $studentRow) {
						$student_ID			= $studentRow->student_id;
						$student_status		= $studentRow->student_status;
						$student_promotable	= $studentRow->student_promotable;
						
						if ($doDebug) {
							echo "<br />Student: $thisCallsign<br />
									student_status: $student_status<br />
									promotable: $student_promotable<br />";
						}
						if ($student_status == 'Y') {
							if ($student_promotable == 'P' || $student_promotable == 'N') {
								$content	.= "Student $thisCallsign qualifies to receive an email<br />";
								
								$updateParams	= array('student_survey_completion_date'=>'');
								$updateFormat	= array('%s');
								
								$studentUpdateData		= array('tableName'=>$studentTableName,
																'inp_method'=>'update',
																'inp_data'=>$updateParams,
																'inp_format'=>$updateFormat,
																'jobname'=>$jobname,
																'inp_id'=>$student_ID,
																'inp_callsign'=>$thisCallsign,
																'inp_semester'=>$theSemester,
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
									$content		.= "Student $thisCallsign is staged<br />";
								}
							} else {
								$content			.= "Student $thisCallsign does not qualify for an email<br />";
							}
						} else {
							$content			.= "Student $thisCallsign does not qualify for an email<br />";
						}
					}
				} else {
					$content	.= "No record found in $studentTableName for $thisCallsign<br />";
				}
			}
		}
		$content	.= "<p>All requested students processed</p>
						<p>Click <a href='$theURL/?strpass=2&inp_verbose=$inp_verbose&inp_mode=$inp_mode'>HERE</a> to send the emails</p>";	

	} elseif ("200" == $strPass) {
		if ($doDebug) {
			echo "<br />at pass 200 ready to clear out all student_survey_completion_date fields<br />";
		}
		$content	.= "<h3>$jobname</h3>";
		$sql		= "update $studentTableName 
						set student_survey_completion_date = '' 
						where student_semester = '$theSemester' 
						and student_status = 'Y' 
						and (student_promotable = 'P' or student_promotable = 'N')";
		$result		= $wpdb->get_results($sql);
		if ($result === FALSE) {
			handleWPDBError($jobname,$doDebug,"attempting to clear out student_survey_completion_date");
			$content	.= "Attempt to clear student-survey_completion_date failed";
		} else {
			$content	.= "All qualified student records cleared. Click 
							<a href='$theURL/?strpass=0'>HERE</a> to continue<br />";
		}
	}
		
	

	$thisTime 		= date('Y-m-d H:i:s');
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
	$content 		.= "<br /><br /><p>Prepared at $thisTime</p>";
	return $content;
}
add_shortcode ('send_email_to_student_to_evaluate_advisor', 'send_email_to_student_to_evaluate_advisor_func');
