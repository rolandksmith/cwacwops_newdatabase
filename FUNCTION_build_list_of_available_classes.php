function build_list_of_available_classes($semester='',$level='all',$tzoffset=0.00,$testMode=FALSE, $doDebug=FALSE) {

/*	Reads the advisor and advisorclass tables and builds a list of 
	classes with available seats
	
	If an advisor has indicated no additional replacement students, the advisor 
	is not incuded in the list. Also, advisors with a survey score of 6 or 13 and 
	advisors with a verify_response of R are not included
	
	Call build_list_of_available_clases($proximateSemester,'all',-3.0,$testMode,$doDebug);
	
	Returns an array
		level	=> callsign	=> utc
							=> local
							=> class size
							=> number students
							=> available seats
							=> sequence
	
	
	Modified 12Oct24 by Roland for new database
	Modified 5May25 by Roland to actually work

*/

	global $wpdb;
// $doDebug = TRUE;
	if ($doDebug) {
		echo "<br /><b>FUNCTION: Build List of Available Classes</b><br />";
	}

	$initializationArray		= data_initialization_func();
	$proximateSemester			= $initializationArray['proximateSemester'];
	
	if ($semester == '') {
		$semester				= $proximateSemester;
	} else {
		$theSemester	= $semester;
	}

	if ($tzoffset = 0.00) {
		$tzID = 0.00;
	}

	if ($testMode) {
		$studentTableName		= 'wpw1_cwa_student2';
		$advisorTableName		= 'wpw1_cwa_advisor2';
		$advisorClassTableName	= 'wpw1_cwa_advisorclass2';
		$userMasterTableName	= 'wpw1_cwa_user_master2';
		$modeInfo				= "<p><b>Program Running in TestMode</b></p>";
	} else {
		$studentTableName		= 'wpw1_cwa_student';
		$advisorTableName		= 'wpw1_cwa_advisor';
		$advisorClassTableName	= 'wpw1_cwa_advisorclass';
		$userMasterTableName	= 'wpw1_cwa_user_master';
		$modeInfo				= "";
	}

	if ($doDebug) {
		echo $modeInfo;
	}
	
	$classArray					= array();
	
	$sql			= "select * from $advisorTableName 
						join $userMasterTableName on user_call_sign = advisor_call_sign 
						where advisor_semester = '$theSemester' 
						order by advisor_call_sign";
	$wpw1_cwa_advisor	= $wpdb->get_results($sql);
	if ($wpw1_cwa_advisor === FALSE) {
		handleWPDBError($jobname,$doDebug);
	} else {
		$numARows			= $wpdb->num_rows;
		if ($doDebug) {
			echo "ran $sql<br />and found $numARows rows in $advisorTableName table<br />";
		}
		if ($numARows > 0) {
			foreach ($wpw1_cwa_advisor as $advisorRow) {
echo "Advisor foreach iteration<br />";
				$advisor_master_ID 					= $advisorRow->user_ID;
				$advisor_master_call_sign			= $advisorRow->user_call_sign;
				$advisor_first_name 				= $advisorRow->user_first_name;
				$advisor_last_name 					= $advisorRow->user_last_name;
				$advisor_email 						= $advisorRow->user_email;
				$advisor_phone 						= $advisorRow->user_phone;
				$advisor_city 						= $advisorRow->user_city;
				$advisor_state 						= $advisorRow->user_state;
				$advisor_zip_code 					= $advisorRow->user_zip_code;
				$advisor_country_code 				= $advisorRow->user_country_code;
				$advisor_whatsapp 					= $advisorRow->user_whatsapp;
				$advisor_telegram 					= $advisorRow->user_telegram;
				$advisor_signal 					= $advisorRow->user_signal;
				$advisor_messenger 					= $advisorRow->user_messenger;
				$advisor_master_action_log 			= $advisorRow->user_action_log;
				$advisor_timezone_id 				= $advisorRow->user_timezone_id;
				$advisor_languages 					= $advisorRow->user_languages;
				$advisor_survey_score 				= $advisorRow->user_survey_score;
				$advisor_is_admin					= $advisorRow->user_is_admin;
				$advisor_role 						= $advisorRow->user_role;
				$advisor_master_date_created 		= $advisorRow->user_date_created;
				$advisor_master_date_updated 		= $advisorRow->user_date_updated;

				$advisor_ID							= $advisorRow->advisor_id;
				$advisor_call_sign 					= strtoupper($advisorRow->advisor_call_sign);
				$advisor_semester 					= $advisorRow->advisor_semester;
				$advisor_welcome_email_date 		= $advisorRow->advisor_welcome_email_date;
				$advisor_verify_email_date 			= $advisorRow->advisor_verify_email_date;
				$advisor_verify_email_number 		= $advisorRow->advisor_verify_email_number;
				$advisor_verify_response 			= strtoupper($advisorRow->advisor_verify_response);
				$advisor_action_log 				= $advisorRow->advisor_action_log;
				$advisor_class_verified 			= $advisorRow->advisor_class_verified;
				$advisor_control_code 				= $advisorRow->advisor_control_code;
				$advisor_date_created 				= $advisorRow->advisor_date_created;
				$advisor_date_updated 				= $advisorRow->advisor_date_updated;
				$advisor_replacement_status 		= $advisorRow->advisor_replacement_status;


				$useAdvisor							= TRUE;
				if ($doDebug) {
					echo "<br />Processing advisor: $advisor_master_call_sign<br />";
				}
				
				if ($advisor_call_sign == 'K1BG') {
					$useAdvisor						= FALSE;
					if ($doDebug) {
						echo "advisor is $advisor_call_sign. Bypassed<br />";
					}
				}
				if ($advisor_survey_score == 6 || $advisor_survey_score == 13) {
					$useAdvisor						= FALSE;
					if($doDebug) {
						echo "survey score is $advisor_survey_score. Bypassed<br />";
					}
				}
				if ($advisor_verify_response == 'R') {
					$useAdvisor						= FALSE;
					if($doDebug) {
						echo "verify response is $advisor_verify_response. Bypassed<br />";
					}							
				}
//				if ($advisor_replacement_status == 'N') {
//					$useAdvisor						= FALSE;
//					if($doDebug) {
//						echo "replacement status is $advisor_replacement_status. Bypassed<br />";
//					}							
//				}
				if ($useAdvisor) {
					// get the advisorclass records
					$advisorClassSQL		= "select * from $advisorClassTableName 
												where advisorclass_call_sign = '$advisor_call_sign' 
												and advisorclass_semester = '$proximateSemester' 
												order by advisorclass_sequence";
					$wpw1_cwa_advisorclass	= $wpdb->get_results($advisorClassSQL);
					if ($wpw1_cwa_advisorclass === FALSE) {
						handleWPDBError($jobname,$doDebug);
					} else {
						$numACRows			= $wpdb->num_rows;
						if ($doDebug) {
							echo "ran $advisorClassSQL<br />and found $numACRows rows<br />";
						}
						if ($numACRows > 0) {
							foreach ($wpw1_cwa_advisorclass as $advisorClassRow) {
echo "advisorClass foreach iteration<br />";
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

				
								$availableSeats						= 0;
								$useClass							= TRUE;
								if ($doDebug) {
									echo "<br />Processing advisorClass: $advisorClass_sequence<br />";
								}
								
								if ($advisorClass_level != 'all') {
									if ($advisorClass_level != $level) {
										$useClass					= FALSE;
										if ($doDebug) {
											echo "advisor class level is not $level<br />";
										}
									}
								}
				
								if ( $useClass) {				

									// see if there are any seats available
									if ($advisorClass_class_size - $advisorClass_number_students > 0) {
										$availableSeats				= $advisorClass_class_size - $advisorClass_number_students;
										if ($doDebug) {
											echo "advisor's class has $availableSeats seats available<br />";
										}
									}
									if ($availableSeats > 0) {
										if ($doDebug) {
											echo "adding advisorclass info to the classArray<br />";
										}
				
										$localTimes			= utcConvert('toLocal',$advisorClass_timezone_offset,$advisorClass_class_schedule_times_utc,$advisorClass_class_schedule_days_utc,$doDebug);
										if ($doDebug) {
											echo "return from utcConvert:<br /><pre>";
											print_r($localTimes);
											echo "</pre><br />";
										}
										if ($localTimes[0] == 'FAIL') {
											$thisReason		= $localTimes[3];
											if ($doDebug) {
												echo "convert to local gave error $thisReason<br />";
											}
											sendErrorEmail("FUNCTION_build_list_of_available_classes: attempting to convert $acvisorClass_clas_schedule_times_utc $advisorClass_class_schedule_days_utc in offset $advisorClass_timezone_offset failed. Reason: $thisReason");
										} else {
											$advisorClass_class_schedule_times_local	= $localTimes[1];
											$advisorClass_class_schedule_days_local		= $localTimes[2];
				
											$classArray[$advisorClass_level][$advisor_call_sign]['UTC']				= "$advisorClass_class_schedule_times_utc $advisorClass_class_schedule_days_utc";
											$classArray[$advisorClass_level][$advisor_call_sign]['LOCAL']				= "$advisorClass_class_schedule_times_local $advisorClass_class_schedule_days_local";
											$classArray[$advisorClass_level][$advisor_call_sign]['class size'] 		= $advisorClass_class_size;
											$classArray[$advisorClass_level][$advisor_call_sign]['number_students'] 	= $advisorClass_number_students;
											$classArray[$advisorClass_level][$advisor_call_sign]['availableSeats'] 	= $availableSeats;
											$classArray[$advisorClass_level][$advisor_call_sign]['sequence'] 			= $advisorClass_sequence;
											if ($doDebug) {
												echo "added class to classArray<br />";
											}
										}
										
									} else {
										if ($doDebug) {
											echo "no seats available. Not using this class<br />";
										}
									}
								} else {
									if ($doDebug) {
										echo "not using this class because of level<br />";
									}
								}
							}
							if ($doDebug) {
								echo "end of foreach for advisorclass<br />";
							}
						} else {
							if ($doDebug) {
								echo "no advisorclass records found<br />";
							}
						}
					}
					if ($doDebug) {
						echo "at advisorclass else<br />";
					}
				} else {
					echo "useAdvisor is FALSE<br />";
				}
			}
			if ($doDebug) {
				echo "end of advisor foreach<br />";
			}
		} else {
			if ($doDebug) {
				echo "no advisor records found<br />";
			}
		}
	}
	if ($doDebug) {
		echo "end of advisor foreach<br />";
	}
	if ($doDebug) {
		echo "returning classArray:<br /><pre>";
		print_r($classArray);
		echo "</pre><br />";
	}
				
	return $classArray;
}
add_action('build_list_of_available_classes', 'build_list_of_available_classes');
