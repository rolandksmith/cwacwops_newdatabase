//// read user_master
	$sqlResult		= $wpdb->get_results($sql);
	if ($sqlResult === FALSE) {
		handleWPDBError($jobname,$doDebug);
	} else {
		$numRows	= $wpdb->num_rows;
		if ($doDebug) {
			echo "ran $sql<br />and retrieved $numRows rows<br />";
		}
		if ($numRows > 0) {
			foreach($sqlResult as $sqlRow) {
				$user_id				= $sqlRow->user_ID;
				$user_call_sign			= $sqlRow->user_call_sign;
				$user_first_name		= $sqlRow->user_first_name;
				$user_last_name			= $sqlRow->user_last_name;
				$user_email				= $sqlRow->user_email;
				$user_ph_code			= $sqlRow->user_ph_code;
				$user_phone				= $sqlRow->user_phone;
				$user_city				= $sqlRow->user_city;
				$user_state				= $sqlRow->user_state;
				$user_zip_code			= $sqlRow->user_zip_code;
				$user_country_code		= $sqlRow->user_country_code;
				$user_country			= $sqlRow->user_country;
				$user_whatsapp			= $sqlRow->user_whatsapp;
				$user_telegram			= $sqlRow->user_telegram;
				$user_signal			= $sqlRow->user_signal;
				$user_messenger			= $sqlRow->user_messenger;
				$user_action_log		= $sqlRow->user_action_log;
				$user_timezone_id		= $sqlRow->user_timezone_id;
				$user_languages			= $sqlRow->user_languages;
				$user_survey_score		= $sqlRow->user_survey_score;
				$user_is_admin			= $sqlRow->user_is_admin;
				$user_role				= $sqlRow->user_role;
				$user_prev_callsign		= $sqlRow->user_prev_callsign;
				$user_date_created		= $sqlRow->user_date_created;
				$user_date_updated		= $sqlRow->user_date_updated;

				$countrySQL				= "select * from $countryCodesTableName 
											where country_code = '$user_country_code'";
				$countrySQLResult		= $wpdb->get_results($countrySQL);
				if ($countrySQLResult === FALSE) {
					handleWPDBError($jobname,$doDebug);
					$user_country		= "UNKNOWN";
					$user_ph_code		= "";
				} else {
					$numCRows		= $wpdb->num_rows;
					if ($doDebug) {
						echo "ran $countrySQL<br />and retrieved $numCRows rows<br />";
					}
					if($numCRows > 0) {
						foreach($countrySQLResult as $countryRow) {
							$user_country		= $countryRow->country_name;
							$user_ph_code		= $countryRow->ph_code;
						}
					} else {
						$user_country			= "Unknown";
						$user_ph_code			= "";
					}
				}




////// get user_master
					$result					= FALSE;
					$reason					= "";
					$user_id 				= 0;
					$user_callsign 			= "unknown";
					$user_first_name 		= "unknown";
					$user_last_name 		= "unknown";
					$user_email 			= "unknown";
					$user_phone 			= "unknown";
					$user_ph_code 			= "";
					$user_city 				= "";
					$user_state 			= "";
					$user_zip_code 			= "";
					$user_country_code 		= "XX";
					$user_country 			= "";
					$user_whatsapp 			= "";
					$user_telegram 			= "";
					$user_signal 			= "";
					$user_messenger 		= "";
					$user_action_log		= "unknown";
					$user_timezone_id	 	= "XX";
					$user_languages 		= "";
					$user_role				= "";
					$user_prev_callsign		= "";
					$user_user_date_created = "";
					$user_user_date_updated = "";

					$dataArray					= array('getMethod'=>'callsign',
														'getInfo'=>$inp_callsign,
														'doDebug'=> $doDebug,
														'testMode'=> $testMode);
					$dataResult			= get_user_master_data($dataArray);
					// unpack the data
					$result				= $dataResult['result'];
					$reason				= $dataResult['reason'];
					$count				= $dataResult['count'];
					
					if ($result === FALSE) {
						// failure actions
						$content		.= "getting data for $inp_callsign failed.<br />Reason: $reason<br />";
					} else {
						if ($doDebug) {
							echo "call to get data for $inp_callsign returned $count rows of data<br />";
						}
						for ($ii=0;$ii<$count;$ii++) {
							$user_id 			= $dataResult[$ii]['user_id'];
							$user_callsign 		= $dataResult[$ii]['user_callsign'];
							$user_first_name 	= $dataResult[$ii]['user_first_name'];
							$user_last_name 	= $dataResult[$ii]['user_last_name'];
							$user_email 		= $dataResult[$ii]['user_email'];
							$user_phone 		= $dataResult[$ii]['user_phone'];
							$user_ph_code 		= $dataResult[$ii]['user_ph_code'];
							$user_city 			= $dataResult[$ii]['user_city'];
							$user_state 		= $dataResult[$ii]['user_state'];
							$user_zip_code 		= $dataResult[$ii]['user_zip_code'];
							$user_country_code 	= $dataResult[$ii]['user_country_code'];
							$user_country 		= $dataResult[$ii]['user_country'];
							$user_whatsapp 		= $dataResult[$ii]['user_whatsapp'];
							$user_telegram 		= $dataResult[$ii]['user_telegram'];
							$user_signal 		= $dataResult[$ii]['user_signal'];
							$user_messenger 	= $dataResult[$ii]['user_messenger'];
							$user_action_log	= $dataResult[$ii]['user_action_log'];
							$user_timezone_id	= $dataResult[$ii]['user_timezone_id'];
							$user_languages 	= $dataResult[$ii]['user_languages'];
							$user_survey_score	= $dataResult[$ii]['user_survey_score'];
							$user_is_admin		= $dataResult[$ii]['user_is_admin'];
							$user_role			= $dataResult[$ii]['user_role'];
							$user_prev_callsign	= $dataResult[$ii]['user_prev_callsign'];
							$user_date_created 	= $dataResult[$ii]['user_date_created'];
							$user_date_updated 	= $dataResult[$ii]['user_date_updated'];

						}
						
						
						
					}
					
					
//// display user_master
			$myStr			= formatActionLog($user_action_log);
			$content		.= "<h4>User Master Data</h4>
							<table style='width:900px;'>
							<tr><td><b>Callsign<br />$user_call_sign</b></td>
								<td><b>Name</b><br />$user_last_name, $user_first_name</td>
								<td><b>Phone</b><br />+$user_ph_code $user_phone</td>
								<td><b>Email</b><br />$user_email</td></tr>
							<tr><td><b>City</b><br />$user_city</td>
								<td><b>State</b><br />$user_state</td>
								<td><b>Zip Code</b><br />$user_zip_code</td>
								<td><b>Country</b><br />$user_country</td></tr>
							<tr><td><b>WhatsApp</b><br />$user_whatsapp</td>
								<td><b>Telegram</b><br />$user_telegram</td>
								<td><b>Signal</b><br />$user_signal</td>
								<td><b>Messenger</b><br />$user_messenger</td></tr>
							<tr><td><b>Timezone ID</b><br />$user_timezone_id</td>
								<td><b>Languages</b><br />$user_languages</td>
								<td><b>Date Created</b><br />user_$user_date_created</td>
								<td><b>Date Updated</b><br />user_$user_date_updated</td></tr>";
			if ($userRole == 'administrator') {
				$content .= "<tr><td><b>Survey Score</b><br />$user_survey_score</td>
								<td><b>Is Admin</b><br />$user_is_admin</td>
								<td><b>Role</b><br />$user_role</td>
								<td><b>Prev Callsign</b><br />$user_prev_callsign</td>
							<tr><td colspan='4'><b>Action Log</b><br />$myStr</td></tr>";
			}
			$content	.= "</table>
							<p>Click <a href='$siteURL/cwa-display-and-update-user-master-information/?strpass=2&request_type=callsign&request_info=$user_call_sign&inp_depth=one$doDebug=$doDebug&testMode=$testMode' 
							target='_blank'>HERE</a> to update the advisor Master Data</p>";




//// read student table
		$sql				= "select * from $studentTableName 
								left join $userMasterTableName on user_call_sign = student_call_sign ";
		$wpw1_cwa_student	= $wpdb->get_results($sql);
		if ($wpw1_cwa_student === FALSE) {
			handleWPDBError($jobname,$doDebug);
		} else {
			$numSRows									= $wpdb->num_rows;
			if ($doDebug) {
				echo "ran $sql<br />and retrieved $numSRows rows from $studentTableName table<br >";
			}
			if ($numSRows > 0) {
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
					$student_prev_callsign				= $studentRow->user_prev_callsign;
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
					





//// read advisor
		$sql				= "select * from $advisorTableName 
								left join $userMasterTableName on user_call_sign = advisor_call_sign ";
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

					// if you need the country name and phone code, include the following
					$countrySQL		= "select * from wpw1_cwa_country_codes  
										where country_code = '$advisor_country_code'";
					$countrySQLResult	= $wpdb->get_results($countrySQL);
					if ($countrySQLResult === FALSE) {
						handleWPDBError($jobname,$doDebug);
						$advisor_country		= "UNKNOWN";
						$advisor_ph_code		= "";
					} else {
						$numCRows		= $wpdb->num_rows;
						if ($doDebug) {
							echo "ran $countrySQL<br />and retrieved $numCRows rows<br />";
						}
						if($numCRows > 0) {
							foreach($countrySQLResult as $countryRow) {
								$advisor_country		= $countryRow->country_name;
								$advisor_ph_code		= $countryRow->ph_code;
							}
						} else {
							$advisor_country			= "Unknown";
							$advisor_ph_code			= "";
						}
					}




//// read advisorClass

		$sql					= "select * from $advisorClassTableName 
									left join $userMasterTableName on user_call_sign = advisorClass_call_sign ";
		$wpw1_cwa_advisorclass	= $wpdb->get_results($sql);
		if ($wpw1_cwa_advisorclass === FALSE) {
			handleWPDBError($jobname,$doDebug);
		} else {
			$numACRows			= $wpdb->num_rows;
			if ($doDebug) {
				echo "ran $sql<br />and found $numACRows rows<br />";
			}
			if ($numACRows > 0) {
				foreach ($wpw1_cwa_advisorclass as $advisorClassRow) {
					$advisorClass_master_ID 				= $advisorClassRow->user_ID;
					$advisorClass_master_call_sign			= $advisorClassRow->user_call_sign;
					$advisorClass_first_name 				= $advisorClassRow->user_first_name;
					$advisorClass_last_name 				= $advisorClassRow->user_last_name;
					$advisorClass_email 					= $advisorClassRow->user_email;
					$advisorClass_ph_code 					= $advisorClassRow->user_ph_code;
					$advisorClass_phone 					= $advisorClassRow->user_phone;
					$advisorClass_city 						= $advisorClassRow->user_city;
					$advisorClass_state 					= $advisorClassRow->user_state;
					$advisorClass_zip_code 					= $advisorClassRow->user_zip_code;
					$advisorClass_country_code 				= $advisorClassRow->user_country_code;
					$advisorClass_country 					= $advisorClassRow->user_country;
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
					$advisorClass_prev_callsign				= $advisorClassRow->user_prev_callsign;
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


					// if you need the country name and phone code, include the following
					$countrySQL		= "select * from wpw1_cwa_country_codes  
										where country_code = '$advisorClass_country_code'";
					$countrySQLResult	= $wpdb->get_results($countrySQL);
					if ($countrySQLResult === FALSE) {
						handleWPDBError($jobname,$doDebug);
						$advisorClass_country		= "UNKNOWN";
						$advisorClass_ph_code		= "";
					} else {
						$numCRows		= $wpdb->num_rows;
						if ($doDebug) {
							echo "ran $countrySQL<br />and retrieved $numCRows rows<br />";
						}
						if($numCRows > 0) {
							foreach($countrySQLResult as $countryRow) {
								$advisorClass_country		= $countryRow->country_name;
								$advisorClass_ph_code		= $countryRow->ph_code;
							}
						} else {
							$advisorClass_country			= "Unknown";
							$advisorClass_ph_code			= "";
						}
					}




///////// audio assessment table

				$assessmentResult		= $wpdb->get_results("select * from $audioAssessmentTableName 
															  where call_sign='$student_call_sign' 
															  order by assessment_date DESC");
				if ($assessmentResult === FALSE) {
					handleWPDBError($jobname,$doDebug);
				} else {
					$lastError			= $wpdb->last_error;
					if ($lastError != '') {
						handleWPDBError($jobname,$doDebug);
						$content		.= "Fatal program error. System Admin has been notified";
						return $content;
					}
					$numASRows				= $wpdb->num_rows;
					if ($doDebug) {
						$myStr				= $wpdb->last_query;
						echo "ran $myStr<br />and found $numASRows rows in $audioAssessmentTableName table<br />";
					}
					if ($numASRows > 0) {
						foreach ($assessmentResult as $audioAssessmentRow) {
							$assessment_ID			= $audioAssessmentRow->record_id;
							$assessment_call_sign	= $audioAssessmentRow->call_sign;
							$assessment_date		= $audioAssessmentRow->assessment_date;
							$assessment_level		= $audioAssessmentRow->assessment_level;
							$assessment_clip		= $audioAssessmentRow->assessment_clip;
							$assessment_score		= $audioAssessmentRow->assessment_score;
							$assessment_clip_name	= $audioAssessmentRow->assessment_clip_name;
							$assessment_notes		= $audioAssessmentRow->assessment_notes;
							$assessment_program		= $audioAssessmentRow->assessment_program;

// insert audio assessment record
		$inputParams		= array('call_sign'=>$thisCallsign, 
									'assessment_date'=>$thisDatetime, 
									'assessment_level'=>$thisLevel, 
									'assessment_clip'=>$thisClip, 
									'assessment_score'=>$thisScore, 
									'assessment_clip_name'=>$thisClipName, 
									'assessment_notes'=>$thisNotes, 
									'assessment_program'=>$thisProgram);

		$inputFormat		= array('%s','%s','%s','%d','%d','%s','%s','%s');

										
		$insertResult	= $wpdb->insert($audioAssessmentTableName ,
										$inputParams,
										$inputFormat);
		if ($insertResult === FALSE) {
			handleWPDBError($jobname,$doDebug);
		} else {
			$lastError			= $wpdb->last_error;
			if ($lastError != '') {
				handleWPDBError($jobname,$doDebug);
				$content		.= "Fatal program error. System Admin has been notified";
				return $content;
			}
		} else {
			$newID			= $wpdb->insert_id;
			if ($doDebug) {
				$myStr			= $wpdb->last_query;
				echo "ran $myStr<br />and inserted $newID into $audioAssessmentTableName <br />";
			}
		}






//////////// read the catalog

	$sql 						= "select * from $catalogTableName where mode='$catalogMode' and semester='$inp_semester'";
	$result						= $wpdb->get_results($sql);
	if ($result === FALSE) {
		handleWPDBError($jobname,$doDebug);
	} else {
		$lastError			= $wpdb->last_error;
		if ($lastError != '') {
			handleWPDBError($jobname,$doDebug);
			$content		.= "Fatal program error. System Admin has been notified";
			return $content;
		}
		$numRows				= $wpdb->num_rows;
		if ($doDebug) {
			$myStr				= $wpdb->last_query;
			echo "Ran $myStr<br />and retrieved $numRows records from $catalogTableName<br />";
		}
		if ($numRows > 0) {
			foreach ($result as $catalogRow) {
				$theCatalog		= $catalogRow->catalog;
				$gotCatalog		= TRUE;
			}
		} else {
			$rolandError		.= "No catalog record found in $catalogTableName table for semester: $inp_semester, mode: $catalogMode<br />";
			sendErrorEmail($rolandError);
			if ($doDebug) {
				echo "No catalog record found in $catalogTableName table for semester: $inp_semester, mode: $catalogMode<br />";
			}
		}
	}

	if ($gotCatalog) {
		if ($doDebug) {
			echo "Have a catalog record:<br />$theCatalog<br />";
		}
		$thisArray						= explode("&",$theCatalog);
		if ($doDebug) {
			echo "Exploded the theCatalog<br /><pre>";
			print_r($thisArray);
			echo "</pre><br />";
		}
		foreach($thisArray as $buffer) {
			if ($doDebug) {
				echo "buffer: $buffer<br />";
			}	
			$myArray				= explode("|",$buffer);
			$myInt					= count($myArray);
			if ($doDebug) {
				echo "Exploded an entry in buffer and got $myInt entries<br />";
			}
			if ($myInt > 1) {
				$thisLevel			= $myArray[0];
				$thisTime			= $myArray[1];
				$thisDays			= $myArray[2];
				$thisCount			= $myArray[3];
				$thisAdvisors		= $myArray[4];
				$skipLine			= FALSE;
	
				$printArray[$thisLevel][$thisIncrement] = "$thisTime|$thisDays|$thisCount|$thisAdvisors";
				$thisIncrement++;
			} else {
				$rolandError			= "Catalog has no entries<br />";
				sendErrorEmail($rolandError);
				$outputArray			= array('FAIL - Missing Catalog');
			}
		}
		if ($doDebug) {
			echo "printArray:<br /><pre>";
			print_r($printArray);
			echo "</pre><br />";
		}
	}




/// Bad Actors
		$wpw1_cwa_bad_actor		= $wpdb->get_results($sql);
		if ($wpw1_cwa_bad_actor === FALSE) {
			handleWPDBError($jobname,$doDebug);
		} else {
			$lastError			= $wpdb->last_error;
			if ($lastError != '') {
				handleWPDBError($jobname,$doDebug);
				$content		.= "Fatal program error. System Admin has been notified";
				return $content;
			}
			$numBARows			= $wpdb->num_rows;
			if ($doDebug) {
				$myStr			= $wpdb->last_query;
				echo "ran $myStr<br />and found $numBARows rows<br />";
			}
			if ($numBARows > 0) {
				foreach ($wpw1_cwa_bad_actor as $bad_actorRow) {
					$bad_actor_ID			= $bad_actorRow->record_id;
					$bad_actor_call_sign	= $bad_actorRow->call_sign;
					$bad_actor_info			= $bad_actorRow->information;
 
 
 
 
 /// replacement_requests
 
 		$wpw1_cwa_replacement_requests		= $wpdb->get_results($sql);
		if ($wpw1_cwa_replacement_requests === FALSE) {
			handleWPDBError($jobname,$doDebug);
		} else {
			$lastError			= $wpdb->last_error;
			if ($lastError != '') {
				handleWPDBError($jobname,$doDebug);
				$content		.= "Fatal program error. System Admin has been notified";
				return $content;
			}
		} else {
			$numRows			= $wpdb->num_rows;
			if ($doDebug) {
				$myStr			= $wpdb->last_query;
				echo "ran $myStr<br />and found $numRows rows<br />";
			}
			if ($numRows > 0) {
				foreach ($wpw1_cwa_replacement_requests as $replacement_requestsRow) {
					$replacement_id				= $replacement_requestsRow->record_id;
					$replacement_call_sign		= $replacement_requestsRow->call_sign;
					$replacement_class			= $replacement_requestsRow->class;
					$replacement_level			= $replacement_requestsRow->level;
					$replacement_semester		= $replacement_requestsRow->semester;
					$replacement_student		= $replacement_requestsRow->student;
					$replacement_date_resolved	= $replacement_requestsRow->date_resolved;
					$replacement_date_created	= $replacement_requestsRow->date_created;
					$replacement_date_updated	= $replacement_requestsRow->date_updated;
					

/// insert into replacement requests

				$inputParams 	= array('call_sign'=>$replacement_call_sign,
										'class'=>$replacement_class,
										'level'=>$replacement_level,
										'semester'=>$replacement_semester,
										'studemt'=>$replacement_student);

				$inputFormat 	= array('%s','%d','%s','%s','%s');
										
				$insertResult	= $wpdb->insert($replacementRequests,
												$inputParams,
												$inputFormat);
				if ($insertResult === FALSE) {
					handleWPDBError($jobname,$doDebug);
				} else {
					$lastError			= $wpdb->last_error;
					if ($lastError != '') {
						handleWPDBError($jobname,$doDebug);
						$content		.= "Fatal program error. System Admin has been notified";
						return $content;
					}
					$newID			= $wpdb->insert_id;
					if ($doDebug) {
						$myStr			= $wpdb->last_query;
						echo "ran $myStr<br />and inserted $newID into $replacementRequests<br />";
					}

 
 
/// reminders
					$remindersResult		= $wpdb->get_results($reminderSQL);
					if ($remindersResult === FALSE) {
						handleWPDBError($jobname,$doDebug);
					} else {
						$numRRows		= $wpdb->num_rows;
						if ($doDebug) {
							echo "ran $reminderSQL<br />and retrieved $numRRows rows<br />";
						}
						$content		.= "have $numRRows reminders records<br />";
						if ($numRRows > 0) {
							foreach($remindersResult as $remindersResultRow) {
								$reminders_record_id			= $remindersResultRow->record_id;
								$reminders_effective_date		= $remindersResultRow->effective_date;
								$reminders_close_date			= $remindersResultRow->close_date;
								$reminders_resolved_date		= $remindersResultRow->resolved_date;
								$reminders_send_reminder		= $remindersResultRow->send_reminder;
								$reminders_send_once			= $remindersResultRow->send_once;
								$reminders_call_sign			= $remindersResultRow->call_sign;
								$reminders_role					= $remindersResultRow->role;
								$reminders_email_text			= $remindersResultRow->email_text;	
								$reminders_reminder_text		= $remindersResultRow->reminder_text;		
								$reminders_resolved				= $remindersResultRow->resolved;	
								$reminders_token				= $remindersResultRow->token;
								$reminders_repeat_sent_date		= $remindersResultRow->repeate_sent_date;		
								$reminders_date_created			= $remindersResultRow->date_created;	
								$reminders_date_modified		= $remindersResultRow->date_modified;	

							}
						}
					}
 
 


