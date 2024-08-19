function verify_advisor_class_func() {

/*	Verify Advisor Class
 *
 *	This function is used around the middle of the semester for advisors to verify which 
 *	students are actually in their class.
 *
 *	Input: 	Advisor call sign
 *	Process:
 *	Get all records for this advisor from the advisor table for the current semester
 *	For each of the advisor records
 *		Get all students for the current semester with a response of Y assigned to that advisor
 *			at that level, ordered by the student last name, first name
 *		List the students with a radio button 'attending' or 'not attending'
 *		Give a text box for the advisor to indicate any additional students
 *	When all records have been displayed, the advisor can submit the response
 *	Update the action log that the advisor has responded
 *	Mark each non-attending student as withdrawn (promotable -> W)
 *	If there are any extras, send that info to Bob
 *	Update a new field 'class verified' with the date the advisor responded
 *
 * 	Modified 4Mar21 by Roland to change joe fisher's email address 
 	Modified 5Oct21 by Roland to use the advisor and advisorClass pod formats
 	Modified 27Oct22 by Roland to accomodate new timezone table format
 	Modified 17Apr23 by Roland to fix action_log
 	Modified 16Jul23 by Roland to use consolidated tables
 	Modified 31Aug23 by Roland to turn off dodebug and testmode if validUser is N
*/ 

	global $wpdb;

	$doDebug						= FALSE;
	$testMode						= FALSE;
	$myDate							= date('dMy hi') . 'z';
	$initializationArray			= data_initialization_func();
	$validUser 						= $initializationArray['validUser'];
	if ($validUser == 'N') {				// turn off debug and testmode
		$doDebug					= FALSE;
		$testMode					= FALSE;
	}
	$siteURL						= $initializationArray['siteurl'];

	if ($doDebug) {
		echo "Initialization Array:<br /><pre>";
		print_r($initializationArray);
		echo "</pre><br />";
	}

//	if ($doDebug) {
		ini_set('display_errors','1');
		error_reporting(E_ALL);	
//	}
/// get the time that the process started
	$startingMicroTime			= microtime(TRUE);

	$userName					= $initializationArray['userName'];
	$currentSemester			= $initializationArray['currentSemester'];
	$strPass					= "1";
	$inp_advisor				= '';
	$extmode					= '';
	$inp_attending				= array();
	$inp_extras					= array();
	$gotStudent					= FALSE;
	$token						= '';
	$studentRecordCount			= 0;
	$studentExtraCount			= 0;
	$increment					= 0;
	$fieldTest					= array('action_log','post_status','post_title','control_code');
	$logDate					= date('Y-m-d H:i');
	$theURL						= "$siteURL/cwa-verify-advisor-class/";
	$jobname					= 'Verify Advisor Class';

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
			if ($str_key 		== "inp_advisor") {
				$inp_advisor	 = $str_value;
				$inp_advisor	 = strtoupper(filter_var($inp_advisor,FILTER_UNSAFE_RAW));
			}
			if ($str_key 		== "extmode") {
				$extmode	 = $str_value;
				$extmode	 = filter_var($extmode,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "inp_advisor_id") {
				$inp_advisor_id	 = $str_value;
				$inp_advisor_id	 = filter_var($inp_advisor_id,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "token") {
				$token			 = $str_value;
				$token			 = filter_var($token,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "inp_attending") {
				$inp_attending	 = $str_value;
			}
			if ($str_key 		== "inp_extras") {
				$inp_extras		 = $str_value;
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

table{font:'Times New Roman', sans-serif;background-image:none;border-collapse:collapse;width:80%;}

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
			echo "Function starting.<br />";
		}
		if ($validUser == "N") {
			return "YOU'RE NOT AUTHORIZED!<br />Goodby";
		} else {
			$content 		.= "<h2>Verify Advisor Class</h2>
<p>This function is normally run from an email sent the advisor around the middle of 
the semester.</p>
<p><form method='post' action='$theURL' 
name='selection_form' ENCTYPE='multipart/form-data''>
<input type='hidden' name='strpass' value='2'>
Advisor Call Sign: <input type='text' class='formInputText' name='inp_advisor' size='10' maxlength='15'><br />
<input class='formInputButton' type='submit' value='Submit' />
</form></p>";
		}

///// Pass 2 -- do the work


	} elseif ("2" == $strPass) {
		if ($doDebug) {
			echo "At pass 2<br />Looking for classes for $inp_advisor in mode $extmode<br />";
		}
		// Verify that we've got something in inp_advisor
		if ($inp_advisor != '' && $extmode != '') {
			if ($extmode == 'tm') {
				if ($doDebug) {
					echo "extmode = $extmode. Setting testMode to TRUE<br />";
				}
				$testMode	= TRUE;
			}
			
			if ($testMode) {
				$studentTableName		= 'wpw1_cwa_consolidated_student2';
				$advisorTableName		= 'wpw1_cwa_consolidated_advisor2';
				$advisorClassTableName	= 'wpw1_cwa_consolidated_advisorclass2';
				if ($doDebug) {
					echo "Function is under development.<br />";
				}
				$content .= "Function is under development.<br />";
			} else {
				$studentTableName 		= 'wpw1_cwa_consolidated_student';
				$advisorTableName		= 'wpw1_cwa_consolidated_advisor';
				$advisorClassTableName	= 'wpw1_cwa_consolidated_advisorclass';
			}
			
			$theBigHeader				= TRUE;
		

			// Get advisor record for this advisor
			$sql				= "select * from $advisorTableName 
									where call_sign='$inp_advisor' 
									and semester='$currentSemester'";
			$wpw1_cwa_advisor	= $wpdb->get_results($sql);
			if ($wpw1_cwa_advisor === FALSE) {
				if ($doDebug) {
					echo "Reading $advisorTableName table failed<br />";
					echo "wpdb->last_query: " . $wpdb->last_query . "<br />";
					echo "<b>wpdb->last_error: " . $wpdb->last_error . "</b><br />";
				}
			} else {
				$numARows			= $wpdb->num_rows;
				if ($doDebug) {
					$myStr			= $wpdb->last_query;
					echo "ran $myStr<br />and found $numARows rows in $advisorTableName table<br />";
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

						$content			.= "<h3>Mid-Semester Class Verification for $advisor_call_sign</h3>
												<p>Please mark each student as either YES (attending) or NO (not attending).<p>
												<p>Thank you for your service as an advisor!</p>
												<form method='post' action='$theURL' 
												name='selection_form' ENCTYPE='multipart/form-data''>
												<input type='hidden' name='strpass' value='3'>
												<input type='hidden' name='inp_advisor' value='$inp_advisor'>
												<input type='hidden' name='inp_advisor_id' value='$advisor_ID'>
												<input type='hidden' name='extmode' value='$extmode'>
												<input type='hidden' name='token' value='$token'>
												<table>";
			
							// get the advisorClass records for this advisor

						$sql					= "select * from $advisorClassTableName 
												   where advisor_call_sign='$inp_advisor' 
												   and semester='$currentSemester' 
												   order by sequence";
						$wpw1_cwa_advisorclass	= $wpdb->get_results($sql);
						if ($wpw1_cwa_advisorclass === FALSE) {
							if ($doDebug) {
								echo "Reading $advisorClassTableName table failed<br />";
								echo "wpdb->last_query: " . $wpdb->last_query . "<br />";
								echo "wpdb->last_error: " . $wpdb->last_error . "<br />";
							}
						} else {
							$numACRows			= $wpdb->num_rows;
							if ($doDebug) {
								$myStr			= $wpdb->last_query;
								echo "ran $myStr<br />and found $numACRows rows<br />";
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

									$firstTime				= TRUE;

									for ($snum=1;$snum<=$class_number_students;$snum++) {
										if ($snum < 10) {
											$strSnum 		= str_pad($snum,2,'0',STR_PAD_LEFT);
										} else {
											$strSnum		= strval($snum);
										}
										$studentCallSign	= ${'advisorClass_student' . $strSnum};
										if ($studentCallSign != '') {

											// Now get the student info
											$sql				= "select * from $studentTableName 
																   where semester='$currentSemester' 
																   and call_sign = '$studentCallSign' ";
											$wpw1_cwa_student	= $wpdb->get_results($sql);
											if ($wpw1_cwa_student === FALSE) {
												if ($doDebug) {
													echo "Reading $studentTableName table failed<br />";
													echo "wpdb->last_query: " . $wpdb->last_query . "<br />";
													echo "wpdb->last_error: " . $wpdb->last_error . "<br />";
												}
											} else {
												$numSRows		= $wpdb->num_rows;
												if ($doDebug) {
													$myStr		= $wpdb->last_query;
													echo "ran $myStr<br />and found $numSRows rows<br />";
												}
												if ($numSRows > 0) {
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
															echo "Have a student $student_call_sign status: $student_student_status; level: $student_level<br />";
														}

														$doProceed								= FALSE;
														if ($student_student_status == 'Y' || $student_student_status == 'S') {
															$doProceed							= TRUE;
														} else {
															$doProceed							= FALSE;
															if ($doDebug) {
																echo "Bypassing this student<br/>";
															}
														}
														if ($student_promotable == 'W') {
															$doProceed							= FALSE;
															if ($doDebug) {
																echo "Bypassing student already withdrawn<br />";
															}
														}
														if ($doProceed) {								
															if ($doDebug) {
																echo "Got a student $student_last_name, $student_first_name ($student_call_sign) $student_time_zone; $student_level<br />";
															}
															$gotStudent				= TRUE;
															if ($firstTime) {
																$firstTime			= FALSE;
																$content		.= "
																				<tr><th>$advisor_last_name, $advisor_first_name ($advisor_call_sign)</th>
																						<th>Level $advisorClass_level; Class: $advisorClass_sequence</th></tr>
																					<tr><th>Student</th>
																						<th>Attending?</th>
																					</tr>";
															}
															$content			.= "<tr><td>$student_last_name, $student_first_name ($student_call_sign)</td>
																						<td>YES <input type='radio' class='formInputButton' name='inp_attending[$studentRecordCount|$student_ID]' value='Y|$student_call_sign' checked='checked'><br />
																							NO <input type='radio' class='formInputButton' name='inp_attending[$studentRecordCount|$student_ID]' value='N|$student_call_sign'></td>
																					</tr>";
															$studentRecordCount++;
														}
													}
												}
											}
										}
									}
								}			/// end of advisorClass
								if ($gotStudent) {
									$content	.= "
													<tr><td colspan='2'>If there are any other students in your class not listed above:<br />
													<br />1. Please have them register for this class even though the registration will be in the next semester. When they are registered, 
													a system admin will move their registration to this semester and add them to your class<br />  
													<br />2. Please enter their name and call sign in the space below. <br />
													<br />3. If more than one student needs to be added, put a period or semicolon between each student. 
													<br /><br />
														<textarea class = 'formInputText' name='inp_extras[$advisorClass_sequence|$advisorClass_level|$studentExtraCount]' rows='5' cols='50'></textarea></td></tr>
													</table>
													<p><input type='submit' value='Submit' class='formInputButton'></p></form>";
									$studentExtraCount++;
								}
							} else {
								if ($doDebug) {
									echo "No advisorClass records found for advisor $advisor_call_sign<br />";
								}
								$content	.= "No advisorClass records found for advisor $advisor_call_sign<br />";
							}
						}
					}	// end of advisor foreach
				} else {
					if ($doDebug) {
						echo "No advisor records found for $inp_advisor<br />";
					}
				}
			}
		} else {
			if ($doDebug) {
				echo "Input inp_advisor is empty<br />";
			}
			$content		.= "Input information missing";
		}
		if ($doDebug) {
			echo "studentRecordCount: $studentRecordCount<br />studentExtraCount: $studentExtraCount<br />";
		}



	
//////////////		Pass 3	
	
	} elseif ("3" == $strPass) {
		if ($extmode == 'tm') {
			if ($doDebug) {
				echo "extmode = $extmode. Setting testMode to TRUE<br />";
			}
			$testMode	= TRUE;
		}
		
		if ($testMode) {
			$studentTableName		= 'wpw1_cwa_consolidated_student2';
			$advisorTableName		= 'wpw1_cwa_consolidated_advisor2';
			$advisorClassTableName	= 'wpw1_cwa_consolidated_advisorclass2';
			if ($doDebug) {
				echo "Function is under development.<br />";
			}
			$content .= "Function is under development.<br />";
		} else {
			$studentTableName 		= 'wpw1_cwa_consolidated_student';
			$advisorTableName		= 'wpw1_cwa_consolidated_advisor';
			$advisorClassTableName	= 'wpw1_cwa_consolidated_advisorclass';
		}
		if ($doDebug) {
			echo "Arrived at pass 3<br />";
		}
		$nextSemester	= $initializationArray['nextSemester'];
		$content		.= "<h3>Results of the Class Verification for $inp_advisor</h3>";
// Handling students who are attending or have dropped
		if ($doDebug) {
			echo "<br />inp_attending:<br ><pre>";
			print_r($inp_attending);
			echo "</pre><br />";
		}
		foreach($inp_attending as $key=>$value) {
			if ($doDebug) {
				echo "$key=>$value<br />";
			}
			$thisArray 				= explode("|",$value);
			$theAttending			= $thisArray[0];
			$theAttendingCallSign	= $thisArray[1];
			$myArray				= explode("|",$key);
			$theRecordCount			= $myArray[0];
			$theRecordID			= $myArray[1];

			/// get the student record
			$sql					= "select * from $studentTableName where student_id='$theRecordID'";
			$wpw1_cwa_student		= $wpdb->get_results($sql);
			if ($wpw1_cwa_student === FALSE) {
				if ($doDebug) {
					echo "Reading $studentTableName table failed<br />";
					echo "wpdb->last_query: " . $wpdb->last_query . "<br />";
					echo "wpdb->last_error: " . $wpdb->last_error . "<br />";
				}
			} else {
				$numSRows			= $wpdb->num_rows;
				if ($doDebug) {
					$myStr			= $wpdb->last_query;
					echo "ran $myStr<br />and found $numSRows rows<br />";
				}
				if ($numSRows > 0) {
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
						
						$updateStudent				= FALSE;
						if ($theAttending == 'N') {
							if ($doDebug) {
								echo "Handling non-attending student at id $theRecordID<br />";
							}
							$student_action_log		.= " / $myDate CLASSVERIFY $inp_advisor marked student as not attending. Promotable set to W ";
							$updateData				= array('promotable'=>'W',
															'action_log'=>$student_action_log);
							$updateFormat			= array('%s',
															'%s');
							$content				.= "Marking $theAttendingCallSign as not attending<br />";
							if ($student_student_status == 'S') {			// make status a Y
								$updateData['student_status']	= 'Y';
								$updateFormat[]					= '%s';
							}
							$updateStudent			= TRUE;

						} else {			// student attending. Is the student status an 'S'?
						
							if ($student_student_status == 'S') {			// make this a 'Y'
								$updateData		= array('student_status'=>'Y');
								$updateFormat	= array('%s');
								$updateStudent	= TRUE;
								if ($doDebug) {
									echo "Student status of 'Y' has been saved for $student_call_sign<br />";
								}
							}
						}
						if ($updateStudent) {
							$studentUpdateData		= array('tableName'=>$studentTableName,
															'inp_method'=>'update',
															'inp_data'=>$updateData,
															'inp_format'=>$updateFormat,
															'jobname'=>$jobname,
															'inp_id'=>$student_ID,
															'inp_callsign'=>$student_call_sign,
															'inp_semester'=>$student_semester,
															'inp_who'=>$student_assigned_advisor,
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
									echo "Successfully updated $student_call_sign record at $student_ID<br />";
								}
							}
						}
					}
				} else {
					if ($doDebug) {
						echo "No student record found for $theAtteningCallSign at id $theRecordID<br />";
					}
				}				
			}
		}
		$content						.= "<br />";
// Now handle the extras
		if ($doDebug) {
			echo "inp_extras:<br />";
		}
		$gotExtras				= FALSE;
		$emailData				= '';
		foreach($inp_extras as $key=>$value) {
			if ($doDebug) {
				echo "$key=>$value<br />";
			}
			if ($value != '') {
				$myArray		= explode("|",$key);
				$theSequence	= $myArray[0];
				$theLevel		= $myArray[1];
				$theII			= $myArray[2];
				$content		.= "$value will be added to $inp_advisor's $theLevel class number $theSequence<br />";
				$emailData		.= "$inp_advisor has indicated the following students need to be added to the advisor's $theLevel class number $theSequence:<br />$value<br />";
				$gotExtras		= TRUE;
			}
// If have an Extras, send an email with the information
			if ($gotExtras) {
				$mySubject				= "CW Academy Additional Students to be Added to Advisor Class";
				if ($testMode) {
					$email_to				= "kcgator@gmail.com";
					$increment++;
					$mailCode				= 5;
					$mySubject				= "TESTMODE $mySubject";
				} else {
					$email_to				= "kcgator@gmail.com";
					$mailCode				= 19;
				}
				$myContent					= "<p>Advisor $inp_advisor has verified his students. 
												<p>$emailData<br /><br />
												73,<br/>
												CW Academy</p>";
				$mailResult		= emailFromCWA_v2(array('theRecipient'=>$email_to,
															'theSubject'=>$mySubject,
															'jobname'=>$jobname,
															'theContent'=>$myContent,
															'mailCode'=>$mailCode,
															'increment'=>$increment,
															'testMode'=>$testMode,
															'doDebug'=>$doDebug));
				if ($mailResult === FALSE) {
					if ($doDebug) {
						echo "The mail function failed<br />";
					}
				}
			}
		
// Finally, update the advisor's class_verified so we don't ask again.
			$currentDate		= $initializationArray['currentDate'];

			$sql				= "select call_sign, action_log from $advisorTableName where advisor_id=$inp_advisor_id";
			$wpw1_cwa_advisor			= $wpdb->get_results($sql);
			if ($doDebug) {
				echo "Reading $advisorTableName table<br />";
				echo "wpdb->last_query: " . $wpdb->last_query . "<br />";
				if ($wpdb->last_error != '') {
					echo "<b>wpdb->last_error: " . $wpdb->last_error . "</b><br />";
				}
			}
			if ($wpw1_cwa_advisor !== FALSE) {
				$numARows									= $wpdb->num_rows;
				if ($doDebug) {
					echo "found $numARows rows in $advisorTableName table<br />";
				}
				if ($numARows > 0) {
					foreach ($wpw1_cwa_advisor as $advisorRow) {
						$advisor_call_sign					= $advisorRow->call_sign;
						$advisor_action_log					= $advisorRow->action_log;
						if ($gotExtras) {
							$advisor_action_log	.= " / $myDate CLASSVERIFY $inp_advisor has verified the students in his class and has 
additional students: $emailData ";
						} else {
							$advisor_action_log		.= " / $myDate CLASSVERIFY $inp_advisor has verified the students in his class ";
						}
						$updateData				= array('class_verified'=>'Y',
														'action_log'=>$advisor_action_log);
						$updateFormat			= array('%s','%s');
						$advisorUpdateData		= array('tableName'=>$advisorTableName,
														'inp_method'=>'update',
														'inp_data'=>$updateData,
														'inp_format'=>$updateFormat,
														'jobname'=>$jobname,
														'inp_id'=>$inp_advisor_id,
														'inp_callsign'=>$advisor_call_sign,
														'inp_semester'=>$student_semester,
														'inp_who'=>$advisor_call_sign,
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
								echo "Successfully updated $advisor_call_sign record at $inp_advisor_id<br />";
							}
							if ($doDebug) {
								echo "Advisor $inp_advisor (ID: $inp_advisor_id) Updated<br />";
							}
							// now resolve the reminder
							resolve_reminder($advisor_call_sign,$token,$testMode,$doDebug);						
						}
					}
				} else {
					if ($doDebug) {
						echo "Advisor record for $inp_advisor not found to update.<br />";
					}
				}
			} else {
				if ($doDebug) {
					echo "Either $advisorTableName table not found or bad $sql 04<br />";
				}
				$content	.= "Either $advisorTableName table not found or bad $sql 04<br />";
			}
		}
		$content	.= "<p>Verification Completed. Thank you. You can close this window.</p>";		
	
	}
	$thisTime 		= date('Y-m-d H:i:s');
	$endingMicroTime = microtime(TRUE);
	$elapsedTime	= $endingMicroTime - $startingMicroTime;
	$elapsedTime	= number_format($elapsedTime, 4, '.', ',');
	$content		.= "<br /><br /><p>Report pass $strPass took $elapsedTime seconds to run</p>";
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
	$content 		.= "<br /><br /><p>Prepared at $thisTime</p>";
	return $content;
}
add_shortcode ('verify_advisor_class', 'verify_advisor_class_func');