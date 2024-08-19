
//// read student table

			$wpw1_cwa_student		= $wpdb->get_results($sql);
			if ($wpw1_cwa_student === FALSE) {
				handleWPDBError($jobname,$doDebug);
			} else {
				$lastError			= $wpdb->last_error;
				if ($lastError != '') {
					handleWPDBError($jobname,$doDebug);
					$content		.= "Fatal program error. System Admin has been notified";
					return $content;
				}
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

						$student_last_name 						= no_magic_quotes($student_last_name);
						$student_excluded_advisor_array			= explode("|",$student_excluded_advisor);


//// insert into student table
				$inputParams 	= array('call_sign'=>$student_call_sign,
										'first_name'=>$student_first_name,
										'last_name'=>$student_last_name,
										'email'=>$student_email,
										'phone'=>$student_phone,
										'ph_code'=>$student_ph_code,
										'city'=>$student_city,
										'state'=>$student_state,
										'zip_code'=>$student_zip_code,
										'country'=>$student_country,
										'country_code'=>$student_country_code,
										'time_zone'=>$student_time_zone,
										'timezone_id'=>$student_timezone_id,
										'timezone_offset'=>$student_timezone_offset,
										'whatsapp_app'=>$student_whatsapp,
										'signal_app'=>$student_signal,
										'telegram_app'=>$student_telegram,
										'messenger_app'=>$student_messenger,
										'wpm'=>$student_wpm,
										'youth'=>$student_youth,
										'age'=>$student_age,
										'student_parent'=>$student_student_parent,
										'student_parent_email'=>$student_student_parent_email,
										'level'=>$student_level,
										'waiting_list'=>$student_waiting_list,
										'request_date'=>$student_request_date,
										'semester'=>$student_semester,
										'notes'=>$student_notes,
										'welcome_date'=>$student_welcome_date,
										'email_sent_date'=>$student_email_sent_date,
										'email_number'=>$student_email_number,
										'response'=>$student_response,
										'response_date'=>$student_response_date,
										'abandoned'=>$student_abandoned,
										'student_status'=>$student_student_status,
										'action_log'=>$student_action_log,
										'pre_assigned_advisor'=>$student_pre_assigned_advisor,
										'selected_date'=>$student_selected_date,
										'no_catalog'=>$student_no_catalog,
										'hold_override'=>$student_hold_override,
										'messaging'=>$student_messaging,
										'assigned_advisor'=>$student_assigned_advisor,
										'advisor_select_date'=>$student_advisor_select_date,
										'advisor_class_timezone'=>$student_advisor_class_timezone,
										'hold_reason_code'=>$student_hold_reason_code,
										'class_priority'=>$student_class_priority,
										'assigned_advisor_class'=>$student_assigned_advisor_class,
										'promotable'=>$student_promotable,
										'excluded_advisor'=>$student_excluded_advisor,
										'student_survey_completion_date'=>$student_student_survey_completion_date,
										'available_class_days'=>$student_available_class_days,
										'intervention_required'=>$student_intervention_required,
										'copy_control'=>$student_copy_control,
										'first_class_choice'=>$student_first_class_choice,
										'second_class_choice'=>$student_second_class_choice,
										'third_class_choice'=>$student_third_class_choice,
										'first_class_choice_utc'=>$student_first_class_choice_utc,
										'second_class_choice_utc'=>$student_second_class_choice_utc,
										'third_class_choice_utc'=>$student_third_class_choice_utc,
										'date_created'=>$student_date_created,
										'date_updated'=>$student_date_updated);

				$inputFormat 	= array('%s','%s','%s','%s','%s','%s','%s','%s','%s','%s',
										'%s','%d','%s','%f','%s','%s','%s','%s','%s','%s',
										'%s','%s','%s','%s','%s','%s','%s','%s','%s','%s',
										'%d','%s','%s','%s','%s','%s','%s','%s','%s','%s',
										'%s','%s','%s','%d','%s','%d','%s','%s','%s','%s',
										'%s','%s','%s','%s','%s','%s','%s','%s','%s','%s',
										'%s');
										
				$insertResult	= $wpdb->insert($studentTableName,
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
						echo "ran $myStr<br />and inserted $newID into $studentTableName<br />";
					}
				}





//// read advisor

		$wpw1_cwa_advisor	= $wpdb->get_results($sql);
		if ($wpw1_cwa_advisor === FALSE) {
			handleWPDBError($jobname,$doDebug);
		} else {
			$lastError			= $wpdb->last_error;
			if ($lastError != '') {
				handleWPDBError($jobname,$doDebug);
				$content		.= "Fatal program error. System Admin has been notified";
				return $content;
			}
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
					$advisor_replacement_status 		= $advisorRow->replacement_status;

					$advisor_last_name 					= no_magic_quotes($advisor_last_name);


//// Insert advisor record
		$updateParams		= array('select_sequence'=>$advisor_select_sequence,
									'call_sign'=>$advisor_call_sign,
									'first_name'=>$advisor_first_name,
									'last_name'=>$advisor_last_name,
									'email'=>$advisor_email,
									'phone'=>$advisor_phone,
									'ph_code'=>$advisor_ph_code,
									'text_message'=>$advisor_text_message,
									'city'=>$advisor_city,
									'state'=>$advisor_state,
									'zip_code'=>$advisor_zip_code,
									'country'=>$advisor_country,
									'country_code'=>$advisor_country_code,
									'time_zone'=>$advisor_time_zone,
									'timezone_id'=>$advisor_timezone_id,
									'timezone_offset'=>$advisor_timezone_offset,
									'whatsapp_app'=>$advisor_whatsapp,
									'signal_app'=>$advisor_signal,
									'telegram_app'=>$advisor_telegram,
									'messenger_app'=>$advisor_messenger,
									'semester'=>$advisor_semester,
									'survey_score'=>$advisor_survey_score,
									'languages'=>$advisor_languages,
									'fifo_date'=>$advisor_fifo_date,
									'welcome_email_date'=>$advisor_welcome_email_date,
									'verify_email_date'=>$advisor_verify_email_date,
									'verify_email_number'=>$advisor_verify_email_number,
									'verify_response'=>$advisor_verify_response,
									'action_log'=>$advisor_action_log,
									'class_verified'=>$advisor_class_verified,
									'control_code'=>$advisor_control_code,
									'replacement_status'=>$advisor_replacement_status);
		$updateFormat		= array('%d',				// select_sequence
									'%s',				// call_sign
									'%s',				// first_name
									'%s',				// last_name
									'%s',				// email
									'%s',				// phone
									'%s',				// ph_code
									'%s',				// text_message
									'%s',				// city
									'%s',				// state
									'%s',				// zip_code
									'%s',				// country
									'%s',				// country_code
									'%d',				// time_zone
									'%s',				// timezone_id
									'%f',				// timezone_offset
									'%s',				// whatsapp_app
									'%s',				// signal_app
									'%s',				// telegram_app
									'%s',				// messenger_app
									'%s',				// semester
									'%d',				// survey_score
									'%s',				// languages
									'%s',				// fifo_date
									'%s',				// welcome_email_date
									'%s',				// verify_email_date
									'%d',				// verify_email_number
									'%s',				// verify_response
									'%s',				// action_log
									'%s',				// class_verified
									'%s',				// control_code
									'%s');				// replacement_status
		$result				= $wpdb->insert($pastAdvisorTableName,
											$updateParams,
											$updateFormat);
		if ($result === FALSE) {
			handleWPDBError($jobname,$doDebug);
		} else {
			$lastError			= $wpdb->last_error;
			if ($lastError != '') {
				handleWPDBError($jobname,$doDebug);
				$content		.= "Fatal program error. System Admin has been notified";
				return $content;
			}
			$nextID				= $wpdb->insert_id;
			if ($doDebug) {
				$myStr			 	= $wpdb->last_query;
				echo "ran $myStr<br/>and successfully inserted $advisor_call_sign record at $nextID<br />";
			}




//// read advisorClass

		$wpw1_cwa_advisorclass				= $wpdb->get_results($sql);
		if ($wpw1_cwa_advisorclass === FALSE) {
			handleWPDBError($jobname,$doDebug);
		} else {
			$lastError			= $wpdb->last_error;
			if ($lastError != '') {
				handleWPDBError($jobname,$doDebug);
				$content		.= "Fatal program error. System Admin has been notified";
				return $content;
			}
			$numACRows						= $wpdb->num_rows;
			if ($doDebug) {
				$myStr						= $wpdb->last_query;
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

					$advisorClass_advisor_last_name  		= no_magic_quotes($advisorClass_advisor_last_name);


//// Insert into advisorClass
		$inputParams		= array('advisor_call_sign'=>$advisorClass_advisor_call_sign,
									'advisor_first_name'=>$advisorClass_advisor_first_name,
									'advisor_last_name'=>$advisorClass_advisor_last_name,
									'advisor_id'=>$advisorClass_advisor_id,
									'sequence'=>$advisorClass_sequence,
									'semester'=>$advisorClass_semester,
									'time_zone'=>$advisorClass_timezone,
									'timezone_id'=>$advisorClass_timezone_id,
									'timezone_offset'=>$advisorClass_timezone_offset,
									'level'=>$advisorClass_level,
									'class_size'=>$advisorClass_class_size,
									'class_schedule_days'=>$advisorClass_class_schedule_days,
									'class_schedule_times'=>$advisorClass_class_schedule_times,
									'class_schedule_days_utc'=>$advisorClass_class_schedule_days_utc,
									'class_schedule_times_utc'=>$advisorClass_class_schedule_times_utc,
									'action_log'=>$advisorClass_action_log,
									'class_incomplete'=>$advisorClass_class_incomplete,
									'date_created'=>$advisorClass_date_created,
									'date_updated'=>$advisorClass_date_updated,
									'student01'=>$advisorClass_student01,
									'student02'=>$advisorClass_student02,
									'student03'=>$advisorClass_student03,
									'student04'=>$advisorClass_student04,
									'student05'=>$advisorClass_student05,
									'student06'=>$advisorClass_student06,
									'student07'=>$advisorClass_student07,
									'student08'=>$advisorClass_student08,
									'student09'=>$advisorClass_student09,
									'student10'=>$advisorClass_student10,
									'student11'=>$advisorClass_student11,
									'student12'=>$advisorClass_student12,
									'student13'=>$advisorClass_student13,
									'student14'=>$advisorClass_student14,
									'student15'=>$advisorClass_student15,
									'student16'=>$advisorClass_student16,
									'student17'=>$advisorClass_student17,
									'student18'=>$advisorClass_student18,
									'student19'=>$advisorClass_student19,
									'student20'=>$advisorClass_student20,
									'student21'=>$advisorClass_student21,
									'student22'=>$advisorClass_student22,
									'student23'=>$advisorClass_student23,
									'student24'=>$advisorClass_student24,
									'student25'=>$advisorClass_student25,
									'student26'=>$advisorClass_student26,
									'student27'=>$advisorClass_student27,
									'student28'=>$advisorClass_student28,
									'student29'=>$advisorClass_student29,
									'student30'=>$advisorClass_student30,
									'number_students'=>$class_number_students,
									'evaluation_complete'=>$class_evaluation_complete,
									'class_comments'=>$class_comments,
									'copy_control'=>$copycontrol);

		$inputFormat		= array('%s','%s','%s','%d','%d','%s','%s','%f','%s','%s',
									'%d','%s','%s','%s','%s','%s','%s','%s','%s','%s',
									'%s','%s','%s','%s','%s','%s','%s','%s','%s','%s',
									'%s','%s','%s','%s','%s','%s','%s','%s','%s','%s',
									'%s','%s','%s','%s','%s','%s','%s','%s','%s','%d',
									'%s','%s','%s');

										
		$insertResult	= $wpdb->insert($advisorClassTableName,
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
				echo "ran $myStr<br />and inserted $newID into $advisorClassTableName<br />";
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
 
 


