function student_report_generator_func() {

/*	Build Your Own Report from the Student table
 *	
 *	Select which fields to display in the report
 *	specify the data selection criteria
 * 	specify the post-selection tests
 *	display the report
 
	Modified 1Feb2021 to change student_code to messaging
	Modified 1Aug2021 by Roland to do tab delimited correctly
	Modified 4Sep2021 by Roland to add class choice UTC
	Modified 28Dec2021 by Roland to use the student table rather than student table
	Modified 18Jul2022 by Roland to add link from student call sign to display and 
		update student information
	Modified 24Sep22 by Roland for the new database fields
	Modified 17Apr23 by Roland to fix action_log
	Modified 16Jul23 by Roland to use consolidated tables
	Modified 1Oct23 by Roland to make wpw1_cwa_old_student table available
		and to implement the static report capability
	Modified 27Aug24 by Roland to use user_master table
*/

	global $wpdb;

	$doDebug						= FALSE;
	$testMode						= FALSE;
	$initializationArray = data_initialization_func();
	if ($doDebug) {
		echo "Initialization Array:<br /><pre>";
		print_r($initializationArray);
		echo "</pre><br />\n";
	}
	$validUser = $initializationArray['validUser'];
	$userName  = $initializationArray['userName'];
	$siteURL	= $initializationArray['siteurl'];
	$versionNumber = '3';
	$currentSemester	= $initializationArray['currentSemester'];
	$nextSemester		= $initializationArray['nextSemester'];
	$semesterTwo		= $initializationArray['semesterTwo'];
	$semesterThree		= $initializationArray['semesterThree'];
	$semesterFour		= $initializationArray['semesterFour'];
	$futureSemester	= "(semester = '$nextSemester' or semester = '$semesterTwo' or semester = '$semesterThree' or semester = '$semesterFour')";
	$proximateSemester	= $initializationArray['proximateSemester'];
	if ($validUser == "N") {
		return "YOU'RE NOT AUTHORIZED!<br />Goodby";
	}
	$jobname = "Student Report Generator V$versionNumber";
/// get the time that the process started
	$startingMicroTime			= microtime(TRUE);

	ini_set('display_errors','1');
	error_reporting(E_ALL);	
	ini_set('memory_limit','256M');
	ini_set('max_execution_time',360);
		
	$strPass					= "1";
	$requestType				= '';
	$mode_type					= '';
	$rg_config					= '';
	$inp_report					= '';
	$theURL					 	= "$siteURL/cwa-student-report-generator/";

    $user_ID = '';
    $user_call_sign = '';
    $user_first_name = '';
    $user_last_name = '';
    $user_email = '';
    $user_ph_code = '';
    $user_phone = '';
    $user_city = '';
    $user_state = '';
    $user_zip_code = '';
    $user_country_code = '';
    $user_country = '';
    $user_whatsapp = '';
    $user_telegram = '';
    $user_signal = '';
    $user_messenger = '';
    $user_action_log = '';
    $user_timezone_id = '';
    $user_languages = '';
    $user_survey_score = '';
    $user_is_admin = '';
    $user_role = '';
    $user_date_created = '';
    $user_date_updated = '';
    $student_id = '';
    $student_call_sign = '';
    $student_time_zone = '';
    $student_timezone_offset = '';
    $student_youth = '';
    $student_age = '';
    $student_parent = '';
    $student_parent_email = '';
    $student_level = '';
    $student_waiting_list = '';
    $student_request_date = '';
    $student_semester = '';
    $student_notes = '';
    $student_welcome_date = '';
    $student_email_sent_date = '';
    $student_email_number = '';
    $student_response = '';
    $student_response_date = '';
    $student_abandoned = '';
    $student_status = '';
    $student_action_log = '';
    $student_pre_assigned_advisor = '';
    $student_selected_date = '';
    $student_no_catalog = '';
    $student_hold_override = '';
    $student_messaging = '';
    $student_assigned_advisor = '';
    $student_advisor_select_date = '';
    $student_advisor_class_timezone = '';
    $student_hold_reason_code = '';
    $student_class_priority = '';
    $student_assigned_advisor_class = '';
    $student_promotable = '';
    $student_excluded_advisor = '';
    $student_survey_completion_date = '';
    $student_available_class_days = '';
    $istudent_ntervention_required = '';
    $student_copy_control = '';
    $student_first_class_choice = '';
    $student_second_class_choice = '';
    $student_third_class_choice = '';
    $student_first_class_choice_utc = '';
    $student_second_class_choice_utc = '';
    $student_third_class_choice_utc = '';
    $student_catalog_options = '';
    $student_flexible = '';
    $student_date_created = '';
    $student_date_updated = '';

    $user_ID_checked = '';
    $user_call_sign_checked = '';
    $user_first_name_checked = '';
    $user_last_name_checked = '';
    $user_email_checked = '';
    $user_ph_code_checked = '';
    $user_phone_checked = '';
    $user_city_checked = '';
    $user_state_checked = '';
    $user_zip_code_checked = '';
    $user_country_code_checked = '';
    $user_country_checked = '';
    $user_whatsapp_checked = '';
    $user_telegram_checked = '';
    $user_signal_checked = '';
    $user_messenger_checked = '';
    $user_action_log_checked = '';
    $user_timezone_id_checked = '';
    $user_languages_checked = '';
    $user_survey_score_checked = '';
    $user_is_admin_checked = '';
    $user_role_checked = '';
    $user_date_created_checked = '';
    $user_date_updated_checked = '';
    $student_id_checked = '';
    $student_call_sign_checked = '';
    $student_time_zone_checked = '';
    $student_timezone_offset_checked = '';
    $student_youth_checked = '';
    $student_age_checked = '';
    $student_parent_checked = '';
    $student_parent_email_checked = '';
    $student_level_checked = '';
    $student_waiting_list_checked = '';
    $student_request_date_checked = '';
    $student_semester_checked = '';
    $student_notes_checked = '';
    $student_welcome_date_checked = '';
    $student_email_sent_date_checked = '';
    $student_email_number_checked = '';
    $student_response_checked = '';
    $student_response_date_checked = '';
    $student_abandoned_checked = '';
    $student_status_checked = '';
    $student_action_log_checked = '';
    $student_pre_assigned_advisor_checked = '';
    $student_selected_date_checked = '';
    $student_no_catalog_checked = '';
    $student_hold_override_checked = '';
    $student_messaging_checked = '';
    $student_assigned_advisor_checked = '';
    $student_advisor_select_date_checked = '';
    $student_advisor_class_timezone_checked = '';
    $student_hold_reason_code_checked = '';
    $student_class_priority_checked = '';
    $student_assigned_advisor_class_checked = '';
    $student_promotable_checked = '';
    $student_excluded_advisor_checked = '';
    $student_survey_completion_date_checked = '';
    $student_available_class_days_checked = '';
    $istudent_ntervention_required_checked = '';
    $student_copy_control_checked = '';
    $student_first_class_choice_checked = '';
    $student_second_class_choice_checked = '';
    $student_third_class_choice_checked = '';
    $student_first_class_choice_utc_checked = '';
    $student_second_class_choice_utc_checked = '';
    $student_third_class_choice_utc_checked = '';
    $student_catalog_options_checked = '';
    $student_flexible_checked = '';
    $student_date_created_checked = '';
    $student_date_updated_checked = '';

	$table_checked						= 'checked';
	$comma_checked						= '';
	$where							= '';
	$orderby						= 'student_call_sign';
	$output_type					= 'table';
	
	

// get the input information
	if (isset($_REQUEST)) {
		foreach($_REQUEST as $str_key => $str_value) {
			if ($doDebug) {
				echo "Key: $str_key | Value: $str_value <br />\n";
			}
			if($str_key					== "inp_debug") {
				$inp_debug				 = $str_value;
				$inp_debug				 = filter_var($inp_debug,FILTER_UNSAFE_RAW);
				if ($inp_debug == 'Y') {
					$doDebug			= TRUE;
				}
			}
			if ($str_key == 'enstr') {
				$enstr					= $str_value;
//				$enstr					= filter_var($enstr,FILTER_UNSAFE_RAW);
				$jsonVar				= base64_decode($enstr);
				$newVar					= html_entity_decode($jsonVar,ENT_QUOTES);
				if ($doDebug) {
					echo "newVar: newVar<br />\n";
				}
				$myArray				= json_decode($newVar,TRUE);
				if ($doDebug) {
					echo "myArray:<br />\n<pre>";
					print_r($myArray);
					echo "</pre><br />\n";
				}
				foreach ($myArray as $thisKey=>$thisValue) {
					if ($doDebug) {
						echo "setting enstr var $thisKey to $thisValue<br />\n";
					}
					$$thisKey			= $thisValue;
				}
			}
			if ($str_key 				== "strpass") {
				$strPass				 = $str_value;
				$strPass				 = filter_var($strPass,FILTER_UNSAFE_RAW);
			}
           if ($str_key == 'user_ID') {
                $user_ID_checked = 'X';
                $reportConfig['user_ID_checked'] = 'X';
                if ($doDebug) {
                    echo "user_ID included in report<br />";
                }
            }
            if ($str_key == 'user_call_sign') {
                $user_call_sign_checked = 'X';
                $reportConfig['user_call_sign_checked'] = 'X';
                if ($doDebug) {
                    echo "user_call_sign included in report<br />";
                }
            }
            if ($str_key == 'user_first_name') {
                $user_first_name_checked = 'X';
                $reportConfig['user_first_name_checked'] = 'X';
                if ($doDebug) {
                    echo "user_first_name included in report<br />";
                }
            }
            if ($str_key == 'user_last_name') {
                $user_last_name_checked = 'X';
                $reportConfig['user_last_name_checked'] = 'X';
                if ($doDebug) {
                    echo "user_last_name included in report<br />";
                }
            }
            if ($str_key == 'user_email') {
                $user_email_checked = 'X';
                $reportConfig['user_email_checked'] = 'X';
                if ($doDebug) {
                    echo "user_email included in report<br />";
                }
            }
            if ($str_key == 'user_ph_code') {
                $user_ph_code_checked = 'X';
                $reportConfig['user_ph_code_checked'] = 'X';
                if ($doDebug) {
                    echo "user_ph_code included in report<br />";
                }
            }
            if ($str_key == 'user_phone') {
                $user_phone_checked = 'X';
                $reportConfig['user_phone_checked'] = 'X';
                if ($doDebug) {
                    echo "user_phone included in report<br />";
                }
            }
            if ($str_key == 'user_city') {
                $user_city_checked = 'X';
                $reportConfig['user_city_checked'] = 'X';
                if ($doDebug) {
                    echo "user_city included in report<br />";
                }
            }
            if ($str_key == 'user_state') {
                $user_state_checked = 'X';
                $reportConfig['user_state_checked'] = 'X';
                if ($doDebug) {
                    echo "user_state included in report<br />";
                }
            }
            if ($str_key == 'user_zip_code') {
                $user_zip_code_checked = 'X';
                $reportConfig['user_zip_code_checked'] = 'X';
                if ($doDebug) {
                    echo "user_zip_code included in report<br />";
                }
            }
            if ($str_key == 'user_country_code') {
                $user_country_code_checked = 'X';
                $reportConfig['user_country_code_checked'] = 'X';
                if ($doDebug) {
                    echo "user_country_code included in report<br />";
                }
            }
            if ($str_key == 'user_country') {
                $user_country_checked = 'X';
                $reportConfig['user_country_checked'] = 'X';
                if ($doDebug) {
                    echo "user_country included in report<br />";
                }
            }
            if ($str_key == 'user_whatsapp') {
                $user_whatsapp_checked = 'X';
                $reportConfig['user_whatsapp_checked'] = 'X';
                if ($doDebug) {
                    echo "user_whatsapp included in report<br />";
                }
            }
            if ($str_key == 'user_telegram') {
                $user_telegram_checked = 'X';
                $reportConfig['user_telegram_checked'] = 'X';
                if ($doDebug) {
                    echo "user_telegram included in report<br />";
                }
            }
            if ($str_key == 'user_signal') {
                $user_signal_checked = 'X';
                $reportConfig['user_signal_checked'] = 'X';
                if ($doDebug) {
                    echo "user_signal included in report<br />";
                }
            }
            if ($str_key == 'user_messenger') {
                $user_messenger_checked = 'X';
                $reportConfig['user_messenger_checked'] = 'X';
                if ($doDebug) {
                    echo "user_messenger included in report<br />";
                }
            }
            if ($str_key == 'user_action_log') {
                $user_action_log_checked = 'X';
                $reportConfig['user_action_log_checked'] = 'X';
                if ($doDebug) {
                    echo "user_action_log included in report<br />";
                }
            }
            if ($str_key == 'user_timezone_id') {
                $user_timezone_id_checked = 'X';
                $reportConfig['user_timezone_id_checked'] = 'X';
                if ($doDebug) {
                    echo "user_timezone_id included in report<br />";
                }
            }
            if ($str_key == 'user_languages') {
                $user_languages_checked = 'X';
                $reportConfig['user_languages_checked'] = 'X';
                if ($doDebug) {
                    echo "user_languages included in report<br />";
                }
            }
            if ($str_key == 'user_survey_score') {
                $user_survey_score_checked = 'X';
                $reportConfig['user_survey_score_checked'] = 'X';
                if ($doDebug) {
                    echo "user_survey_score included in report<br />";
                }
            }
            if ($str_key == 'user_is_admin') {
                $user_is_admin_checked = 'X';
                $reportConfig['user_is_admin_checked'] = 'X';
                if ($doDebug) {
                    echo "user_is_admin included in report<br />";
                }
            }
            if ($str_key == 'user_role') {
                $user_role_checked = 'X';
                $reportConfig['user_role_checked'] = 'X';
                if ($doDebug) {
                    echo "user_role included in report<br />";
                }
            }
            if ($str_key == 'user_date_created') {
                $user_date_created_checked = 'X';
                $reportConfig['user_date_created_checked'] = 'X';
                if ($doDebug) {
                    echo "user_date_created included in report<br />";
                }
            }
            if ($str_key == 'user_date_updated') {
                $user_date_updated_checked = 'X';
                $reportConfig['user_date_updated_checked'] = 'X';
                if ($doDebug) {
                    echo "user_date_updated included in report<br />";
                }
            }
            if ($str_key == 'student_id') {
                $student_id_checked = 'X';
                $reportConfig['student_id_checked'] = 'X';
                if ($doDebug) {
                    echo "student_id included in report<br />";
                }
            }
            if ($str_key == 'student_call_sign') {
                $student_call_sign_checked = 'X';
                $reportConfig['student_call_sign_checked'] = 'X';
                if ($doDebug) {
                    echo "student_call_sign included in report<br />";
                }
            }
            if ($str_key == 'student_time_zone') {
                $student_time_zone_checked = 'X';
                $reportConfig['student_time_zone_checked'] = 'X';
                if ($doDebug) {
                    echo "student_time_zone included in report<br />";
                }
            }
            if ($str_key == 'student_timezone_offset') {
                $student_timezone_offset_checked = 'X';
                $reportConfig['student_timezone_offset_checked'] = 'X';
                if ($doDebug) {
                    echo "student_timezone_offset included in report<br />";
                }
            }
            if ($str_key == 'student_youth') {
                $student_youth_checked = 'X';
                $reportConfig['student_youth_checked'] = 'X';
                if ($doDebug) {
                    echo "student_youth included in report<br />";
                }
            }
            if ($str_key == 'student_age') {
                $student_age_checked = 'X';
                $reportConfig['student_age_checked'] = 'X';
                if ($doDebug) {
                    echo "student_age included in report<br />";
                }
            }
            if ($str_key == 'student_parent') {
                $student_parent_checked = 'X';
                $reportConfig['student_parent_checked'] = 'X';
                if ($doDebug) {
                    echo "student_parent included in report<br />";
                }
            }
            if ($str_key == 'student_parent_email') {
                $student_parent_email_checked = 'X';
                $reportConfig['student_parent_email_checked'] = 'X';
                if ($doDebug) {
                    echo "student_parent_email included in report<br />";
                }
            }
            if ($str_key == 'student_level') {
                $student_level_checked = 'X';
                $reportConfig['student_level_checked'] = 'X';
                if ($doDebug) {
                    echo "student_level included in report<br />";
                }
            }
            if ($str_key == 'student_waiting_list') {
                $student_waiting_list_checked = 'X';
                $reportConfig['student_waiting_list_checked'] = 'X';
                if ($doDebug) {
                    echo "student_waiting_list included in report<br />";
                }
            }
            if ($str_key == 'student_request_date') {
                $student_request_date_checked = 'X';
                $reportConfig['student_request_date_checked'] = 'X';
                if ($doDebug) {
                    echo "student_request_date included in report<br />";
                }
            }
            if ($str_key == 'student_semester') {
                $student_semester_checked = 'X';
                $reportConfig['student_semester_checked'] = 'X';
                if ($doDebug) {
                    echo "student_semester included in report<br />";
                }
            }
            if ($str_key == 'student_notes') {
                $student_notes_checked = 'X';
                $reportConfig['student_notes_checked'] = 'X';
                if ($doDebug) {
                    echo "student_notes included in report<br />";
                }
            }
            if ($str_key == 'student_welcome_date') {
                $student_welcome_date_checked = 'X';
                $reportConfig['student_welcome_date_checked'] = 'X';
                if ($doDebug) {
                    echo "student_welcome_date included in report<br />";
                }
            }
            if ($str_key == 'student_email_sent_date') {
                $student_email_sent_date_checked = 'X';
                $reportConfig['student_email_sent_date_checked'] = 'X';
                if ($doDebug) {
                    echo "student_email_sent_date included in report<br />";
                }
            }
            if ($str_key == 'student_email_number') {
                $student_email_number_checked = 'X';
                $reportConfig['student_email_number_checked'] = 'X';
                if ($doDebug) {
                    echo "student_email_number included in report<br />";
                }
            }
            if ($str_key == 'student_response') {
                $student_response_checked = 'X';
                $reportConfig['student_response_checked'] = 'X';
                if ($doDebug) {
                    echo "student_response included in report<br />";
                }
            }
            if ($str_key == 'student_response_date') {
                $student_response_date_checked = 'X';
                $reportConfig['student_response_date_checked'] = 'X';
                if ($doDebug) {
                    echo "student_response_date included in report<br />";
                }
            }
            if ($str_key == 'student_abandoned') {
                $student_abandoned_checked = 'X';
                $reportConfig['student_abandoned_checked'] = 'X';
                if ($doDebug) {
                    echo "student_abandoned included in report<br />";
                }
            }
            if ($str_key == 'student_status') {
                $student_status_checked = 'X';
                $reportConfig['student_status_checked'] = 'X';
                if ($doDebug) {
                    echo "student_status included in report<br />";
                }
            }
            if ($str_key == 'student_action_log') {
                $student_action_log_checked = 'X';
                $reportConfig['student_action_log_checked'] = 'X';
                if ($doDebug) {
                    echo "student_action_log included in report<br />";
                }
            }
            if ($str_key == 'student_pre_assigned_advisor') {
                $student_pre_assigned_advisor_checked = 'X';
                $reportConfig['student_pre_assigned_advisor_checked'] = 'X';
                if ($doDebug) {
                    echo "student_pre_assigned_advisor included in report<br />";
                }
            }
            if ($str_key == 'student_selected_date') {
                $student_selected_date_checked = 'X';
                $reportConfig['student_selected_date_checked'] = 'X';
                if ($doDebug) {
                    echo "student_selected_date included in report<br />";
                }
            }
            if ($str_key == 'student_no_catalog') {
                $student_no_catalog_checked = 'X';
                $reportConfig['student_no_catalog_checked'] = 'X';
                if ($doDebug) {
                    echo "student_no_catalog included in report<br />";
                }
            }
            if ($str_key == 'student_hold_override') {
                $student_hold_override_checked = 'X';
                $reportConfig['student_hold_override_checked'] = 'X';
                if ($doDebug) {
                    echo "student_hold_override included in report<br />";
                }
            }
            if ($str_key == 'student_messaging') {
                $student_messaging_checked = 'X';
                $reportConfig['student_messaging_checked'] = 'X';
                if ($doDebug) {
                    echo "student_messaging included in report<br />";
                }
            }
            if ($str_key == 'student_assigned_advisor') {
                $student_assigned_advisor_checked = 'X';
                $reportConfig['student_assigned_advisor_checked'] = 'X';
                if ($doDebug) {
                    echo "student_assigned_advisor included in report<br />";
                }
            }
            if ($str_key == 'student_advisor_select_date') {
                $student_advisor_select_date_checked = 'X';
                $reportConfig['student_advisor_select_date_checked'] = 'X';
                if ($doDebug) {
                    echo "student_advisor_select_date included in report<br />";
                }
            }
            if ($str_key == 'student_advisor_class_timezone') {
                $student_advisor_class_timezone_checked = 'X';
                $reportConfig['student_advisor_class_timezone_checked'] = 'X';
                if ($doDebug) {
                    echo "student_advisor_class_timezone included in report<br />";
                }
            }
            if ($str_key == 'student_hold_reason_code') {
                $student_hold_reason_code_checked = 'X';
                $reportConfig['student_hold_reason_code_checked'] = 'X';
                if ($doDebug) {
                    echo "student_hold_reason_code included in report<br />";
                }
            }
            if ($str_key == 'student_class_priority') {
                $student_class_priority_checked = 'X';
                $reportConfig['student_class_priority_checked'] = 'X';
                if ($doDebug) {
                    echo "student_class_priority included in report<br />";
                }
            }
            if ($str_key == 'student_assigned_advisor_class') {
                $student_assigned_advisor_class_checked = 'X';
                $reportConfig['student_assigned_advisor_class_checked'] = 'X';
                if ($doDebug) {
                    echo "student_assigned_advisor_class included in report<br />";
                }
            }
            if ($str_key == 'student_promotable') {
                $student_promotable_checked = 'X';
                $reportConfig['student_promotable_checked'] = 'X';
                if ($doDebug) {
                    echo "student_promotable included in report<br />";
                }
            }
            if ($str_key == 'student_excluded_advisor') {
                $student_excluded_advisor_checked = 'X';
                $reportConfig['student_excluded_advisor_checked'] = 'X';
                if ($doDebug) {
                    echo "student_excluded_advisor included in report<br />";
                }
            }
            if ($str_key == 'student_survey_completion_date') {
                $student_survey_completion_date_checked = 'X';
                $reportConfig['student_survey_completion_date_checked'] = 'X';
                if ($doDebug) {
                    echo "student_survey_completion_date included in report<br />";
                }
            }
            if ($str_key == 'student_available_class_days') {
                $student_available_class_days_checked = 'X';
                $reportConfig['student_available_class_days_checked'] = 'X';
                if ($doDebug) {
                    echo "student_available_class_days included in report<br />";
                }
            }
            if ($str_key == 'istudent_ntervention_required') {
                $istudent_ntervention_required_checked = 'X';
                $reportConfig['istudent_ntervention_required_checked'] = 'X';
                if ($doDebug) {
                    echo "istudent_ntervention_required included in report<br />";
                }
            }
            if ($str_key == 'student_copy_control') {
                $student_copy_control_checked = 'X';
                $reportConfig['student_copy_control_checked'] = 'X';
                if ($doDebug) {
                    echo "student_copy_control included in report<br />";
                }
            }
            if ($str_key == 'student_first_class_choice') {
                $student_first_class_choice_checked = 'X';
                $reportConfig['student_first_class_choice_checked'] = 'X';
                if ($doDebug) {
                    echo "student_first_class_choice included in report<br />";
                }
            }
            if ($str_key == 'student_second_class_choice') {
                $student_second_class_choice_checked = 'X';
                $reportConfig['student_second_class_choice_checked'] = 'X';
                if ($doDebug) {
                    echo "student_second_class_choice included in report<br />";
                }
            }
            if ($str_key == 'student_third_class_choice') {
                $student_third_class_choice_checked = 'X';
                $reportConfig['student_third_class_choice_checked'] = 'X';
                if ($doDebug) {
                    echo "student_third_class_choice included in report<br />";
                }
            }
            if ($str_key == 'student_first_class_choice_utc') {
                $student_first_class_choice_utc_checked = 'X';
                $reportConfig['student_first_class_choice_utc_checked'] = 'X';
                if ($doDebug) {
                    echo "student_first_class_choice_utc included in report<br />";
                }
            }
            if ($str_key == 'student_second_class_choice_utc') {
                $student_second_class_choice_utc_checked = 'X';
                $reportConfig['student_second_class_choice_utc_checked'] = 'X';
                if ($doDebug) {
                    echo "student_second_class_choice_utc included in report<br />";
                }
            }
            if ($str_key == 'student_third_class_choice_utc') {
                $student_third_class_choice_utc_checked = 'X';
                $reportConfig['student_third_class_choice_utc_checked'] = 'X';
                if ($doDebug) {
                    echo "student_third_class_choice_utc included in report<br />";
                }
            }
            if ($str_key == 'student_catalog_options') {
                $student_catalog_options_checked = 'X';
                $reportConfig['student_catalog_options_checked'] = 'X';
                if ($doDebug) {
                    echo "student_catalog_options included in report<br />";
                }
            }
            if ($str_key == 'student_flexible') {
                $student_flexible_checked = 'X';
                $reportConfig['student_flexible_checked'] = 'X';
                if ($doDebug) {
                    echo "student_flexible included in report<br />";
                }
            }
            if ($str_key == 'student_date_created') {
                $student_date_created_checked = 'X';
                $reportConfig['student_date_created_checked'] = 'X';
                if ($doDebug) {
                    echo "student_date_created included in report<br />";
                }
            }
            if ($str_key == 'student_date_updated') {
                $student_date_updated_checked = 'X';
                $reportConfig['student_date_updated_checked'] = 'X';
                if ($doDebug) {
                    echo "student_date_updated included in report<br />";
                }
            }
			if($str_key					== "where") {
				$where					 = $str_value;
// echo "where b4 filter: $where<br />";
//				$where					 = filter_var($where,FILTER_UNSAFE_RAW);
				$where					= str_replace("&#39;","'",$where);
				$where					= stripslashes($where);
// echo "where after filter: $where<br />";
			}
			if($str_key					== "inp_report") {
				$inp_report				 = $str_value;
				$inp_report				 = filter_var($inp_report,FILTER_UNSAFE_RAW);
			}
			if($str_key					== "inp_download") {
				$inp_download				 = $str_value;
				$inp_download				 = filter_var($inp_download,FILTER_UNSAFE_RAW);
			}
			if($str_key					== "inp_filename") {
				$inp_filename				 = $str_value;
				$inp_filename				 = filter_var($inp_filename,FILTER_UNSAFE_RAW);
			}
			if($str_key					== "inp_config") {
				$inp_config				 = $str_value;
				$inp_config				 = filter_var($inp_config,FILTER_UNSAFE_RAW);
			}
			if($str_key					== "inp_report_name") {
				$inp_report_name				 = $str_value;
				$inp_report_name				 = filter_var($inp_report_name,FILTER_UNSAFE_RAW);
			}
			if($str_key					== "inp_report") {
				$inp_report				 = $str_value;
				$inp_report				 = filter_var($inp_report,FILTER_UNSAFE_RAW);
			}
			if($str_key					== "rg_config") {
				$rg_config					 = $str_value;
				$rf_confog				 = filter_var($rg_cofig,FILTER_UNSAFE_RAW);
			}
			if($str_key					== "mode_type") {
				$mode_type					 = $str_value;
				$mode_type				 = filter_var($mode_type,FILTER_UNSAFE_RAW);
			}
			if($str_key					== "orderby") {
				$orderby					 = $str_value;
				$orderby				 = filter_var($orderby,FILTER_UNSAFE_RAW);
			}
			if($str_key					== "output_type") {
				$output_type					 = $str_value;
				$output_type				 = filter_var($output_type,FILTER_UNSAFE_RAW);
			}
		}
	}
	
	
	
	$content = "<style type='text/css'>
				fieldset {font:'Times New Roman', sans-serif;color:#666;background-image:none;
				background:#efefef;padding:2px;border:solid 1px #d3dd3;}
				
				legend {font:'Times New Roman', sans-serif;color:#666;font-weight:bold;
				font-variant:small-caps;background:#d3d3d3;padding:2px 6px;margin-bottom:8px;}
				
				label {font:'Times New Roman', sans-serif;font-weight:bold;line-height:normal;
				text-align:right;margin-right:10px;position:relative;display:block;float:left;}
				
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

	if ($mode_type == 'production') {
		$studentTableName			= 'wpw1_cwa_student';
		$userMasterTableName		= 'wpw1_cwa_user_master';
		$countryCodesTableName		= 'wpw1_cwa_country_codes';
	} else {
		$studentTableName			= 'wpw1_cwa_student2';
		$userMasterTableName		= 'wpw1_cwa_user_master2';
		$countryCodesTableName		= 'wpw1_cwa_country_codes';
	}



	if ("1" == $strPass) {
		if ($doDebug) {
			echo "Function starting.<br />\n";
		}
		
		// if the configuration is being sent in, handle it
		if ($rg_config != '') {
			$configData = json_decode($rg_config,TRUE);
			if ($doDebug) {
				echo "configData:<br /><pre>";
				print_r($configData);
				echo "</pre><br />\n";
			}
	
			foreach($configData as $thisKey => $thisValue) {
				if ($doDebug) {
					echo "thisKey: $thisKey; thisValue: $thisValue<br />\n";
				}
				$$thisKey = $thisValue;
				${$thisKey . '_checked'}	= 'checked';
			}
			$thisWhere = html_entity_decode($where);
			$table_checked					= 'checked';
			$comma_checked					= '';
			if ($type == 'table') {
				$table_checked				= 'checked';
			} elseif ($type == 'comma') {
				$comma_checked				= 'checked';
			}
			$thisOrderby					= $orderby;
		}	
			
		$content 		.= "<h3>$jobname</h3>
							<p><form method='post' action='$theURL' 
							name='selection_form' ENCTYPE='multipart/form-data'>
							<input type='hidden' name='strpass' value='2'>
							<table>
							<fieldset>
							<legend>Indicate which fields should be on the report</legend>
							<tr><th colspan='2' style='width:300px;'>User Master Fields</th></tr>
							<tr><th>Report Field</th><th>Table Name</th></tr>
						   <tr><td><input type='checkbox' class='formInputButton' id='user_ID' 
									name='user_ID' value='user_ID'>
									<label for 'user_ID'>user_ID</label></td>
								<td>user_ID</td></tr>
							<tr><td><input type='checkbox' class='formInputButton' id='user_call_sign' 
									name='user_call_sign' value='user_call_sign'>
									<label for 'user_call_sign'>user_call_sign</label></td>
								<td>user_call_sign</td></tr>
							<tr><td><input type='checkbox' class='formInputButton' id='user_first_name' 
									name='user_first_name' value='user_first_name'>
									<label for 'user_first_name'>user_first_name</label></td>
								<td>user_first_name</td></tr>
							<tr><td><input type='checkbox' class='formInputButton' id='user_last_name' 
									name='user_last_name' value='user_last_name'>
									<label for 'user_last_name'>user_last_name</label></td>
								<td>user_last_name</td></tr>
							<tr><td><input type='checkbox' class='formInputButton' id='user_email' 
									name='user_email' value='user_email'>
									<label for 'user_email'>user_email</label></td>
								<td>user_email</td></tr>
							<tr><td><input type='checkbox' class='formInputButton' id='user_ph_code' 
									name='user_ph_code' value='user_ph_code'>
									<label for 'user_ph_code'>user_ph_code</label></td>
								<td>user_ph_code</td></tr>
							<tr><td><input type='checkbox' class='formInputButton' id='user_phone' 
									name='user_phone' value='user_phone'>
									<label for 'user_phone'>user_phone</label></td>
								<td>user_phone</td></tr>
							<tr><td><input type='checkbox' class='formInputButton' id='user_city' 
									name='user_city' value='user_city'>
									<label for 'user_city'>user_city</label></td>
								<td>user_city</td></tr>
							<tr><td><input type='checkbox' class='formInputButton' id='user_state' 
									name='user_state' value='user_state'>
									<label for 'user_state'>user_state</label></td>
								<td>user_state</td></tr>
							<tr><td><input type='checkbox' class='formInputButton' id='user_zip_code' 
									name='user_zip_code' value='user_zip_code'>
									<label for 'user_zip_code'>user_zip_code</label></td>
								<td>user_zip_code</td></tr>
							<tr><td><input type='checkbox' class='formInputButton' id='user_country_code' 
									name='user_country_code' value='user_country_code'>
									<label for 'user_country_code'>user_country_code</label></td>
								<td>user_country_code</td></tr>
							<tr><td><input type='checkbox' class='formInputButton' id='user_country' 
									name='user_country' value='user_country'>
									<label for 'user_country'>user_country</label></td>
								<td>user_country</td></tr>
							<tr><td><input type='checkbox' class='formInputButton' id='user_whatsapp' 
									name='user_whatsapp' value='user_whatsapp'>
									<label for 'user_whatsapp'>user_whatsapp</label></td>
								<td>user_whatsapp</td></tr>
							<tr><td><input type='checkbox' class='formInputButton' id='user_telegram' 
									name='user_telegram' value='user_telegram'>
									<label for 'user_telegram'>user_telegram</label></td>
								<td>user_telegram</td></tr>
							<tr><td><input type='checkbox' class='formInputButton' id='user_signal' 
									name='user_signal' value='user_signal'>
									<label for 'user_signal'>user_signal</label></td>
								<td>user_signal</td></tr>
							<tr><td><input type='checkbox' class='formInputButton' id='user_messenger' 
									name='user_messenger' value='user_messenger'>
									<label for 'user_messenger'>user_messenger</label></td>
								<td>user_messenger</td></tr>
							<tr><td><input type='checkbox' class='formInputButton' id='user_action_log' 
									name='user_action_log' value='user_action_log'>
									<label for 'user_action_log'>user_action_log</label></td>
								<td>user_action_log</td></tr>
							<tr><td><input type='checkbox' class='formInputButton' id='user_timezone_id' 
									name='user_timezone_id' value='user_timezone_id'>
									<label for 'user_timezone_id'>user_timezone_id</label></td>
								<td>user_timezone_id</td></tr>
							<tr><td><input type='checkbox' class='formInputButton' id='user_languages' 
									name='user_languages' value='user_languages'>
									<label for 'user_languages'>user_languages</label></td>
								<td>user_languages</td></tr>
							<tr><td><input type='checkbox' class='formInputButton' id='user_survey_score' 
									name='user_survey_score' value='user_survey_score'>
									<label for 'user_survey_score'>user_survey_score</label></td>
								<td>user_survey_score</td></tr>
							<tr><td><input type='checkbox' class='formInputButton' id='user_is_admin' 
									name='user_is_admin' value='user_is_admin'>
									<label for 'user_is_admin'>user_is_admin</label></td>
								<td>user_is_admin</td></tr>
							<tr><td><input type='checkbox' class='formInputButton' id='user_role' 
									name='user_role' value='user_role'>
									<label for 'user_role'>user_role</label></td>
								<td>user_role</td></tr>
							<tr><td><input type='checkbox' class='formInputButton' id='user_date_created' 
									name='user_date_created' value='user_date_created'>
									<label for 'user_date_created'>user_date_created</label></td>
								<td>user_date_created</td></tr>
							<tr><td><input type='checkbox' class='formInputButton' id='user_date_updated' 
									name='user_date_updated' value='user_date_updated'>
									<label for 'user_date_updated'>user_date_updated</label></td>
								<td>user_date_updated</td></tr>
	
							<tr><th colspan='2'>Student Fields</th></tr>
							<tr><th>Report Field</th><th>Table Name</th></tr>
							<tr><td><input type='checkbox' class='formInputButton' id='student_id' 
									name='student_id' value='student_id'>
									<label for 'student_id'>student_id</label></td>
								<td>student_id</td></tr>
							<tr><td><input type='checkbox' class='formInputButton' id='student_timezone_offset' 
									name='student_timezone_offset' value='student_timezone_offset'>
									<label for 'student_timezone_offset'>student_timezone_offset</label></td>
								<td>student_timezone_offset</td></tr>
							<tr><td><input type='checkbox' class='formInputButton' id='student_youth' 
									name='student_youth' value='student_youth'>
									<label for 'student_youth'>student_youth</label></td>
								<td>student_youth</td></tr>
							<tr><td><input type='checkbox' class='formInputButton' id='student_age' 
									name='student_age' value='student_age'>
									<label for 'student_age'>student_age</label></td>
								<td>student_age</td></tr>
							<tr><td><input type='checkbox' class='formInputButton' id='student_parent' 
									name='student_parent' value='student_parent'>
									<label for 'student_parent'>student_parent</label></td>
								<td>student_parent</td></tr>
							<tr><td><input type='checkbox' class='formInputButton' id='student_parent_email' 
									name='student_parent_email' value='student_parent_email'>
									<label for 'student_parent_email'>student_parent_email</label></td>
								<td>student_parent_email</td></tr>
							<tr><td><input type='checkbox' class='formInputButton' id='student_level' 
									name='student_level' value='student_level'>
									<label for 'student_level'>student_level</label></td>
								<td>student_level</td></tr>
							<tr><td><input type='checkbox' class='formInputButton' id='student_waiting_list' 
									name='student_waiting_list' value='student_waiting_list'>
									<label for 'student_waiting_list'>student_waiting_list</label></td>
								<td>student_waiting_list</td></tr>
							<tr><td><input type='checkbox' class='formInputButton' id='student_request_date' 
									name='student_request_date' value='student_request_date'>
									<label for 'student_request_date'>student_request_date</label></td>
								<td>student_request_date</td></tr>
							<tr><td><input type='checkbox' class='formInputButton' id='student_semester' 
									name='student_semester' value='student_semester'>
									<label for 'student_semester'>student_semester</label></td>
								<td>student_semester</td></tr>
							<tr><td><input type='checkbox' class='formInputButton' id='student_notes' 
									name='student_notes' value='student_notes'>
									<label for 'student_notes'>student_notes</label></td>
								<td>student_notes</td></tr>
							<tr><td><input type='checkbox' class='formInputButton' id='student_welcome_date' 
									name='student_welcome_date' value='student_welcome_date'>
									<label for 'student_welcome_date'>student_welcome_date</label></td>
								<td>student_welcome_date</td></tr>
							<tr><td><input type='checkbox' class='formInputButton' id='student_email_sent_date' 
									name='student_email_sent_date' value='student_email_sent_date'>
									<label for 'student_email_sent_date'>student_email_sent_date</label></td>
								<td>student_email_sent_date</td></tr>
							<tr><td><input type='checkbox' class='formInputButton' id='student_email_number' 
									name='student_email_number' value='student_email_number'>
									<label for 'student_email_number'>student_email_number</label></td>
								<td>student_email_number</td></tr>
							<tr><td><input type='checkbox' class='formInputButton' id='student_response' 
									name='student_response' value='student_response'>
									<label for 'student_response'>student_response</label></td>
								<td>student_response</td></tr>
							<tr><td><input type='checkbox' class='formInputButton' id='student_response_date' 
									name='student_response_date' value='student_response_date'>
									<label for 'student_response_date'>student_response_date</label></td>
								<td>student_response_date</td></tr>
							<tr><td><input type='checkbox' class='formInputButton' id='student_abandoned' 
									name='student_abandoned' value='student_abandoned'>
									<label for 'student_abandoned'>student_abandoned</label></td>
								<td>student_abandoned</td></tr>
							<tr><td><input type='checkbox' class='formInputButton' id='student_status' 
									name='student_status' value='student_status'>
									<label for 'student_status'>student_status</label></td>
								<td>student_status</td></tr>
							<tr><td><input type='checkbox' class='formInputButton' id='student_action_log' 
									name='student_action_log' value='student_action_log'>
									<label for 'student_action_log'>student_action_log</label></td>
								<td>student_action_log</td></tr>
							<tr><td><input type='checkbox' class='formInputButton' id='student_pre_assigned_advisor' 
									name='student_pre_assigned_advisor' value='student_pre_assigned_advisor'>
									<label for 'student_pre_assigned_advisor'>student_pre_assigned_advisor</label></td>
								<td>student_pre_assigned_advisor</td></tr>
							<tr><td><input type='checkbox' class='formInputButton' id='student_selected_date' 
									name='student_selected_date' value='student_selected_date'>
									<label for 'student_selected_date'>student_selected_date</label></td>
								<td>student_selected_date</td></tr>
							<tr><td><input type='checkbox' class='formInputButton' id='student_no_catalog' 
									name='student_no_catalog' value='student_no_catalog'>
									<label for 'student_no_catalog'>student_no_catalog</label></td>
								<td>student_no_catalog</td></tr>
							<tr><td><input type='checkbox' class='formInputButton' id='student_hold_override' 
									name='student_hold_override' value='student_hold_override'>
									<label for 'student_hold_override'>student_hold_override</label></td>
								<td>student_hold_override</td></tr>
							<tr><td><input type='checkbox' class='formInputButton' id='student_messaging' 
									name='student_messaging' value='student_messaging'>
									<label for 'student_messaging'>student_messaging</label></td>
								<td>student_messaging</td></tr>
							<tr><td><input type='checkbox' class='formInputButton' id='student_assigned_advisor' 
									name='student_assigned_advisor' value='student_assigned_advisor'>
									<label for 'student_assigned_advisor'>student_assigned_advisor</label></td>
								<td>student_assigned_advisor</td></tr>
							<tr><td><input type='checkbox' class='formInputButton' id='student_advisor_select_date' 
									name='student_advisor_select_date' value='student_advisor_select_date'>
									<label for 'student_advisor_select_date'>student_advisor_select_date</label></td>
								<td>student_advisor_select_date</td></tr>
							<tr><td><input type='checkbox' class='formInputButton' id='student_advisor_class_timezone' 
									name='student_advisor_class_timezone' value='student_advisor_class_timezone'>
									<label for 'student_advisor_class_timezone'>student_advisor_class_timezone</label></td>
								<td>student_advisor_class_timezone</td></tr>
							<tr><td><input type='checkbox' class='formInputButton' id='student_hold_reason_code' 
									name='student_hold_reason_code' value='student_hold_reason_code'>
									<label for 'student_hold_reason_code'>student_hold_reason_code</label></td>
								<td>student_hold_reason_code</td></tr>
							<tr><td><input type='checkbox' class='formInputButton' id='student_class_priority' 
									name='student_class_priority' value='student_class_priority'>
									<label for 'student_class_priority'>student_class_priority</label></td>
								<td>student_class_priority</td></tr>
							<tr><td><input type='checkbox' class='formInputButton' id='student_assigned_advisor_class' 
									name='student_assigned_advisor_class' value='student_assigned_advisor_class'>
									<label for 'student_assigned_advisor_class'>student_assigned_advisor_class</label></td>
								<td>student_assigned_advisor_class</td></tr>
							<tr><td><input type='checkbox' class='formInputButton' id='student_promotable' 
									name='student_promotable' value='student_promotable'>
									<label for 'student_promotable'>student_promotable</label></td>
								<td>student_promotable</td></tr>
							<tr><td><input type='checkbox' class='formInputButton' id='student_excluded_advisor' 
									name='student_excluded_advisor' value='student_excluded_advisor'>
									<label for 'student_excluded_advisor'>student_excluded_advisor</label></td>
								<td>student_excluded_advisor</td></tr>
							<tr><td><input type='checkbox' class='formInputButton' id='student_survey_completion_date' 
									name='student_survey_completion_date' value='student_survey_completion_date'>
									<label for 'student_survey_completion_date'>student_survey_completion_date</label></td>
								<td>student_survey_completion_date</td></tr>
							<tr><td><input type='checkbox' class='formInputButton' id='student_available_class_days' 
									name='student_available_class_days' value='student_available_class_days'>
									<label for 'student_available_class_days'>student_available_class_days</label></td>
								<td>student_available_class_days</td></tr>
							<tr><td><input type='checkbox' class='formInputButton' id='istudent_ntervention_required' 
									name='istudent_ntervention_required' value='istudent_ntervention_required'>
									<label for 'istudent_ntervention_required'>istudent_ntervention_required</label></td>
								<td>istudent_ntervention_required</td></tr>
							<tr><td><input type='checkbox' class='formInputButton' id='student_copy_control' 
									name='student_copy_control' value='student_copy_control'>
									<label for 'student_copy_control'>student_copy_control</label></td>
								<td>student_copy_control</td></tr>
							<tr><td><input type='checkbox' class='formInputButton' id='student_first_class_choice' 
									name='student_first_class_choice' value='student_first_class_choice'>
									<label for 'student_first_class_choice'>student_first_class_choice</label></td>
								<td>student_first_class_choice</td></tr>
							<tr><td><input type='checkbox' class='formInputButton' id='student_second_class_choice' 
									name='student_second_class_choice' value='student_second_class_choice'>
									<label for 'student_second_class_choice'>student_second_class_choice</label></td>
								<td>student_second_class_choice</td></tr>
							<tr><td><input type='checkbox' class='formInputButton' id='student_third_class_choice' 
									name='student_third_class_choice' value='student_third_class_choice'>
									<label for 'student_third_class_choice'>student_third_class_choice</label></td>
								<td>student_third_class_choice</td></tr>
							<tr><td><input type='checkbox' class='formInputButton' id='student_first_class_choice_utc' 
									name='student_first_class_choice_utc' value='student_first_class_choice_utc'>
									<label for 'student_first_class_choice_utc'>student_first_class_choice_utc</label></td>
								<td>student_first_class_choice_utc</td></tr>
							<tr><td><input type='checkbox' class='formInputButton' id='student_second_class_choice_utc' 
									name='student_second_class_choice_utc' value='student_second_class_choice_utc'>
									<label for 'student_second_class_choice_utc'>student_second_class_choice_utc</label></td>
								<td>student_second_class_choice_utc</td></tr>
							<tr><td><input type='checkbox' class='formInputButton' id='student_third_class_choice_utc' 
									name='student_third_class_choice_utc' value='student_third_class_choice_utc'>
									<label for 'student_third_class_choice_utc'>student_third_class_choice_utc</label></td>
								<td>student_third_class_choice_utc</td></tr>
							<tr><td><input type='checkbox' class='formInputButton' id='student_catalog_options' 
									name='student_catalog_options' value='student_catalog_options'>
									<label for 'student_catalog_options'>student_catalog_options</label></td>
								<td>student_catalog_options</td></tr>
							<tr><td><input type='checkbox' class='formInputButton' id='student_flexible' 
									name='student_flexible' value='student_flexible'>
									<label for 'student_flexible'>student_flexible</label></td>
								<td>student_flexible</td></tr>
							<tr><td><input type='checkbox' class='formInputButton' id='student_date_created' 
									name='student_date_created' value='student_date_created'>
									<label for 'student_date_created'>student_date_created</label></td>
								<td>student_date_created</td></tr>
							<tr><td><input type='checkbox' class='formInputButton' id='student_date_updated' 
									name='student_date_updated' value='student_date_updated'>
									<label for 'student_date_updated'>student_date_updated</label></td>
								<td>student_date_updated</td></tr>
							</table></p>
	
							<p>Select Output Format<br />
							<input type='radio' id='table' name='output_type' value='table' $table_checked> Table Report<br />
							<input type='radio' id='comma' name='output_type' value='comma' $comma_checked> Tab Delimited Report<br /></p>
							
							<p>Which Mode<br />
							<input type='radio' id='student' name='mode_type' value='production' checked='checked'> Production<br />
							<input type='radio' id='student2' name='mode_type' value='testMode'> TestMode<br /></p>
							
							<p>Enter the 'Where' clause:<br />
							<textarea class='formInputText' id='where' name='where' rows='5' cols='80'>$where</textarea><br /></p>
							
							<p>Enter the 'Orderby' clause:<br />
							<textarea class='formInputText' id='orderby' name='orderby' rows='5' cols='80'>$orderby</textarea><br /></p>
							
							<p>Save the report configuration?<br />
							<input type='radio' id='inp_config' name='inp_config' value='N' checked='checked'> Do not save report configuration<br />
							<input type='radio' id='inp_config' name='inp_config' value='Y' > Save report configuration (enter report name:)<br />
							<input type='text' class='formInputText' size='50' maxlength='100' name='inp_report_name'></p>
							
							<p>Verbose Debugging?<br />
							<input type='radio' id='inp_debug' name='inp_debug' value='N' checked='checked'> Debugging off<br />
							<input type='radio' id='inp_debug' name='inp_debug' value='Y' > Turn Debugging on<br /></p>
							
							<p>Save Report to Reports Table? <br />
							<input type='radio' id='inp_report' name='inp_report' value='N' checked='checked'> Do not save<br />
							<input type='radio' id='inp_report' name='inp_report' value='Y' > Save the report<br /></p>
							
							<p><input class='formInputButton' type='submit' value='Submit' />
							</form></p>
	
							<br /><br /><p>Examples of the 'where' clause:<br />
							To include students for a particular semester: <em>semester='2021 Apr/May'</em><br /><br />
							To include students assigned to a specific advisor: <em>assigned_advisor='WR7Q'</em><br /><br />
							To include students for a particular semester but exclude students with a response of 'R': 
							<em>semester='2021 Apr/May' and response != 'R'</em><br /><br />
							Include all students with the phrase 'not promotable' in the student action log: <em>student_action_log 
							like '%not promotable%'</em><br /><br />
							To include all future semesters, use <em>futureSemester</em>. For example: level = 'Fundamental' and futureSemester <br /><br />
							To search current or upcoming semester use <em>proximateSemester</em> <br />
							</p>";

///// Pass 2 -- do the work


	} elseif ("2" == $strPass) {
		if ($doDebug) {
			echo "<br />at pass 2<br />";
		}		
	
// Array to convert database name to display name
        $nameConversionArray['user_ID'] = 'user<br />ID';
        $nameConversionArray['user_call_sign'] = 'user<br />call_sign';
        $nameConversionArray['user_first_name'] = 'user<br />first_name';
        $nameConversionArray['user_last_name'] = 'user<br />last_name';
        $nameConversionArray['user_email'] = 'user<br />email';
        $nameConversionArray['user_ph_code'] = 'user<br />ph_code';
        $nameConversionArray['user_phone'] = 'user<br />phone';
        $nameConversionArray['user_city'] = 'user<br />city';
        $nameConversionArray['user_state'] = 'user<br />state';
        $nameConversionArray['user_zip_code'] = 'user<br />zip_code';
        $nameConversionArray['user_country_code'] = 'user<br />country_code';
        $nameConversionArray['user_country'] = 'user<br />country';
        $nameConversionArray['user_whatsapp'] = 'user<br />whatsapp';
        $nameConversionArray['user_telegram'] = 'user<br />telegram';
        $nameConversionArray['user_signal'] = 'user<br />signal';
        $nameConversionArray['user_messenger'] = 'user<br />messenger';
        $nameConversionArray['user_action_log'] = 'user<br />action_log';
        $nameConversionArray['user_timezone_id'] = 'user<br />timezone_id';
        $nameConversionArray['user_languages'] = 'user<br />languages';
        $nameConversionArray['user_survey_score'] = 'user<br />survey_score';
        $nameConversionArray['user_is_admin'] = 'user<br />is_admin';
        $nameConversionArray['user_role'] = 'user<br />role';
        $nameConversionArray['user_date_created'] = 'user<br />date_created';
        $nameConversionArray['user_date_updated'] = 'user<br />date_updated';
        $nameConversionArray['student_id'] = 'student<br />id';
        $nameConversionArray['student_call_sign'] = 'student<br />call_sign';
        $nameConversionArray['student_time_zone'] = 'student<br />time_zone';
        $nameConversionArray['student_timezone_offset'] = 'student<br />timezone_offset';
        $nameConversionArray['student_youth'] = 'student<br />youth';
        $nameConversionArray['student_age'] = 'student<br />age';
        $nameConversionArray['student_parent'] = 'student<br />parent';
        $nameConversionArray['student_parent_email'] = 'student<br />parent_email';
        $nameConversionArray['student_level'] = 'student<br />level';
        $nameConversionArray['student_waiting_list'] = 'student<br />waiting_list';
        $nameConversionArray['student_request_date'] = 'student<br />request_date';
        $nameConversionArray['student_semester'] = 'student<br />semester';
        $nameConversionArray['student_notes'] = 'student<br />notes';
        $nameConversionArray['student_welcome_date'] = 'student<br />welcome_date';
        $nameConversionArray['student_email_sent_date'] = 'student<br />email_sent_date';
        $nameConversionArray['student_email_number'] = 'student<br />email_number';
        $nameConversionArray['student_response'] = 'student<br />response';
        $nameConversionArray['student_response_date'] = 'student<br />response_date';
        $nameConversionArray['student_abandoned'] = 'student<br />abandoned';
        $nameConversionArray['student_status'] = 'student<br />status';
        $nameConversionArray['student_action_log'] = 'student<br />action_log';
        $nameConversionArray['student_pre_assigned_advisor'] = 'student_pre_assigned_advisor';
        $nameConversionArray['student_selected_date'] = 'student<br />selected_date';
        $nameConversionArray['student_no_catalog'] = 'student<br />no_catalog';
        $nameConversionArray['student_hold_override'] = 'student<br />hold_override';
        $nameConversionArray['student_messaging'] = 'student<br />messaging';
        $nameConversionArray['student_assigned_advisor'] = 'student<br />assigned_advisor';
        $nameConversionArray['student_advisor_select_date'] = 'student<br />advisor<br />select_date';
        $nameConversionArray['student_advisor_class_timezone'] = 'student<br />advisor<br />class_timezone';
        $nameConversionArray['student_hold_reason_code'] = 'student<br />hold_reason_code';
        $nameConversionArray['student_class_priority'] = 'student<br />class_priority';
        $nameConversionArray['student_assigned_advisor_class'] = 'student<br />assigned_advisor<br />class';
        $nameConversionArray['student_promotable'] = 'student<br />promotable';
        $nameConversionArray['student_excluded_advisor'] = 'student<br />excluded_advisor';
        $nameConversionArray['student_survey_completion_date'] = 'student<br />survey_completion_date';
        $nameConversionArray['student_available_class_days'] = 'student<br />available_class_days';
        $nameConversionArray['istudent_ntervention_required'] = 'istudent<br />ntervention_required';
        $nameConversionArray['student_copy_control'] = 'student<br />copy_control';
        $nameConversionArray['student_first_class_choice'] = 'student<br />first_class_choice';
        $nameConversionArray['student_second_class_choice'] = 'student<br />second_class_choice';
        $nameConversionArray['student_third_class_choice'] = 'student<br />third_class_choice';
        $nameConversionArray['student_first_class_choice_utc'] = 'student<br />first_class_choice_utc';
        $nameConversionArray['student_second_class_choice_utc'] = 'student<br />second_class_choice_utc';
        $nameConversionArray['student_third_class_choice_utc'] = 'student<br />third_class_choice_utc';
        $nameConversionArray['student_catalog_options'] = 'student<br />catalog_options';
        $nameConversionArray['student_flexible'] = 'student<br />flexible';
        $nameConversionArray['student_date_created'] = 'student<br />date_created';
        $nameConversionArray['student_date_updated'] = 'student<br />date_updated';

		// Begin the Report Output
		
		$myInt = strpos($where,'futureSemester');
		if ($myInt !== FALSE) {
			$where = str_replace('futureSemester',$futureSemester,$where);
		}
		$myInt = strpos($where,'proximateSemester');
		if ($myInt !== FALSE) {
			$where = str_replace('proximateSemester',$proximateSemester,$where);
		}

		if ($inp_config == 'Y') {		// saving the report configuration
			if ($inp_report_name != '') {
				$whereStr					= htmlentities($where,ENT_QUOTES);
				$reportConfig['where']		= $whereStr;
				$reportConfig['orderby']	= $orderby;
				$reportConfig['rg_table']	= $studentTableName;
				$reportConfig['type']		= $output_type;
				$myStr						= date('Y-m-d H:i:s');
				$rg_config					= addslashes(json_encode($reportConfig));
				
				// if the report name alreaady exists, update else insert
				$reportNameCount			= $wpdb->get_var("select count(rg_report_name) from wpw1_cwa_report_configurations where rg_report_name = '$inp_report_name'");
				if ($reportNameCount == 0) {
					if ($doDebug) {
						echo "report name $inp_report_name is new<br />\n";
					}
					$reportQuery = "insert into wpw1_cwa_report_configurations 
(rg_report_name, rg_table, rg_config, date_written) values 
('$inp_report_name', 'student', '$rg_config', '$myStr')";
				} else {
					if ($doDebug) {
						echo "report name $inp_report_name is being updated<br />\n";
					}
					$reportQuery = "update wpw1_cwa_report_configurations 
set rg_table = 'student', 
	rg_config = '$rg_config', 
	date_written = '$myStr' 
where rg_report_name = '$inp_report_name'";
				}
				
				if ($doDebug) {
					echo "Preparing to save the report configuration. SQL: $reportQuery<br />\n";
				}
				// run the reportQuery
				$reportResult	= $wpdb->query($reportQuery);
				if ($reportResult === FALSE) {
					if ($doDebug) {
						$thisError	= $wpdb->last_error;
						$thisSQL	= $wpdb->last_query;
						echo "writing to wpw1_cwa_report_configurations failed. Error: $thisError<br />\nSQL: $thisSQL<br />\n";
					}
					$content		.= "<p>Unable to store report configuration</p>\n";
				}
			}
		}
		
		$content				.= "<h2>Generated Report from the $studentTableName Table</h2>
									<p>Save report: $inp_report<br />";

		$sql = "select * from $studentTableName 
				left join $userMasterTableName  on student_call_sign = user_call_sign ";
		if ($where != '') {
			$sql	= "$sql where $where ";
		}
		if ($orderby != '') {
			$sql	= "$sql order by $orderby";
		}
		$content	.= "SQL: $sql</p>";
		if ($doDebug) {
			echo "output_type is $output_type<br />";
		}
		if ($output_type == 'table') {
			$content .= "<table><tr>";
             if ($user_ID_checked == 'X') {
                 $headerName = $nameConversionArray['user_ID'];
                 $content .= "<th>$headerName</th>";
            }
             if ($user_call_sign_checked == 'X') {
                 $headerName = $nameConversionArray['user_call_sign'];
                 $content .= "<th>$headerName</th>";
            }
             if ($user_first_name_checked == 'X') {
                 $headerName = $nameConversionArray['user_first_name'];
                 $content .= "<th>$headerName</th>";
            }
             if ($user_last_name_checked == 'X') {
                 $headerName = $nameConversionArray['user_last_name'];
                 $content .= "<th>$headerName</th>";
            }
             if ($user_email_checked == 'X') {
                 $headerName = $nameConversionArray['user_email'];
                 $content .= "<th>$headerName</th>";
            }
             if ($user_ph_code_checked == 'X') {
                 $headerName = $nameConversionArray['user_ph_code'];
                 $content .= "<th>$headerName</th>";
            }
             if ($user_phone_checked == 'X') {
                 $headerName = $nameConversionArray['user_phone'];
                 $content .= "<th>$headerName</th>";
            }
             if ($user_city_checked == 'X') {
                 $headerName = $nameConversionArray['user_city'];
                 $content .= "<th>$headerName</th>";
            }
             if ($user_state_checked == 'X') {
                 $headerName = $nameConversionArray['user_state'];
                 $content .= "<th>$headerName</th>";
            }
             if ($user_zip_code_checked == 'X') {
                 $headerName = $nameConversionArray['user_zip_code'];
                 $content .= "<th>$headerName</th>";
            }
             if ($user_country_code_checked == 'X') {
                 $headerName = $nameConversionArray['user_country_code'];
                 $content .= "<th>$headerName</th>";
            }
             if ($user_country_checked == 'X') {
                 $headerName = $nameConversionArray['user_country'];
                 $content .= "<th>$headerName</th>";
            }
             if ($user_whatsapp_checked == 'X') {
                 $headerName = $nameConversionArray['user_whatsapp'];
                 $content .= "<th>$headerName</th>";
            }
             if ($user_telegram_checked == 'X') {
                 $headerName = $nameConversionArray['user_telegram'];
                 $content .= "<th>$headerName</th>";
            }
             if ($user_signal_checked == 'X') {
                 $headerName = $nameConversionArray['user_signal'];
                 $content .= "<th>$headerName</th>";
            }
             if ($user_messenger_checked == 'X') {
                 $headerName = $nameConversionArray['user_messenger'];
                 $content .= "<th>$headerName</th>";
            }
             if ($user_action_log_checked == 'X') {
                 $headerName = $nameConversionArray['user_action_log'];
                 $content .= "<th>$headerName</th>";
            }
             if ($user_timezone_id_checked == 'X') {
                 $headerName = $nameConversionArray['user_timezone_id'];
                 $content .= "<th>$headerName</th>";
            }
             if ($user_languages_checked == 'X') {
                 $headerName = $nameConversionArray['user_languages'];
                 $content .= "<th>$headerName</th>";
            }
             if ($user_survey_score_checked == 'X') {
                 $headerName = $nameConversionArray['user_survey_score'];
                 $content .= "<th>$headerName</th>";
            }
             if ($user_is_admin_checked == 'X') {
                 $headerName = $nameConversionArray['user_is_admin'];
                 $content .= "<th>$headerName</th>";
            }
             if ($user_role_checked == 'X') {
                 $headerName = $nameConversionArray['user_role'];
                 $content .= "<th>$headerName</th>";
            }
             if ($user_date_created_checked == 'X') {
                 $headerName = $nameConversionArray['user_date_created'];
                 $content .= "<th>$headerName</th>";
            }
             if ($user_date_updated_checked == 'X') {
                 $headerName = $nameConversionArray['user_date_updated'];
                 $content .= "<th>$headerName</th>";
            }
             if ($student_id_checked == 'X') {
                 $headerName = $nameConversionArray['student_id'];
                 $content .= "<th>$headerName</th>";
            }
             if ($student_call_sign_checked == 'X') {
                 $headerName = $nameConversionArray['student_call_sign'];
                 $content .= "<th>$headerName</th>";
            }
             if ($student_time_zone_checked == 'X') {
                 $headerName = $nameConversionArray['student_time_zone'];
                 $content .= "<th>$headerName</th>";
            }
             if ($student_timezone_offset_checked == 'X') {
                 $headerName = $nameConversionArray['student_timezone_offset'];
                 $content .= "<th>$headerName</th>";
            }
             if ($student_youth_checked == 'X') {
                 $headerName = $nameConversionArray['student_youth'];
                 $content .= "<th>$headerName</th>";
            }
             if ($student_age_checked == 'X') {
                 $headerName = $nameConversionArray['student_age'];
                 $content .= "<th>$headerName</th>";
            }
             if ($student_parent_checked == 'X') {
                 $headerName = $nameConversionArray['student_parent'];
                 $content .= "<th>$headerName</th>";
            }
             if ($student_parent_email_checked == 'X') {
                 $headerName = $nameConversionArray['student_parent_email'];
                 $content .= "<th>$headerName</th>";
            }
             if ($student_level_checked == 'X') {
                 $headerName = $nameConversionArray['student_level'];
                 $content .= "<th>$headerName</th>";
            }
             if ($student_waiting_list_checked == 'X') {
                 $headerName = $nameConversionArray['student_waiting_list'];
                 $content .= "<th>$headerName</th>";
            }
             if ($student_request_date_checked == 'X') {
                 $headerName = $nameConversionArray['student_request_date'];
                 $content .= "<th>$headerName</th>";
            }
             if ($student_semester_checked == 'X') {
                 $headerName = $nameConversionArray['student_semester'];
                 $content .= "<th>$headerName</th>";
            }
             if ($student_notes_checked == 'X') {
                 $headerName = $nameConversionArray['student_notes'];
                 $content .= "<th>$headerName</th>";
            }
             if ($student_welcome_date_checked == 'X') {
                 $headerName = $nameConversionArray['student_welcome_date'];
                 $content .= "<th>$headerName</th>";
            }
             if ($student_email_sent_date_checked == 'X') {
                 $headerName = $nameConversionArray['student_email_sent_date'];
                 $content .= "<th>$headerName</th>";
            }
             if ($student_email_number_checked == 'X') {
                 $headerName = $nameConversionArray['student_email_number'];
                 $content .= "<th>$headerName</th>";
            }
             if ($student_response_checked == 'X') {
                 $headerName = $nameConversionArray['student_response'];
                 $content .= "<th>$headerName</th>";
            }
             if ($student_response_date_checked == 'X') {
                 $headerName = $nameConversionArray['student_response_date'];
                 $content .= "<th>$headerName</th>";
            }
             if ($student_abandoned_checked == 'X') {
                 $headerName = $nameConversionArray['student_abandoned'];
                 $content .= "<th>$headerName</th>";
            }
             if ($student_status_checked == 'X') {
                 $headerName = $nameConversionArray['student_status'];
                 $content .= "<th>$headerName</th>";
            }
             if ($student_action_log_checked == 'X') {
                 $headerName = $nameConversionArray['student_action_log'];
                 $content .= "<th>$headerName</th>";
            }
             if ($student_pre_assigned_advisor_checked == 'X') {
                 $headerName = $nameConversionArray['student_pre_assigned_advisor'];
                 $content .= "<th>$headerName</th>";
            }
             if ($student_selected_date_checked == 'X') {
                 $headerName = $nameConversionArray['student_selected_date'];
                 $content .= "<th>$headerName</th>";
            }
             if ($student_no_catalog_checked == 'X') {
                 $headerName = $nameConversionArray['student_no_catalog'];
                 $content .= "<th>$headerName</th>";
            }
             if ($student_hold_override_checked == 'X') {
                 $headerName = $nameConversionArray['student_hold_override'];
                 $content .= "<th>$headerName</th>";
            }
             if ($student_messaging_checked == 'X') {
                 $headerName = $nameConversionArray['student_messaging'];
                 $content .= "<th>$headerName</th>";
            }
             if ($student_assigned_advisor_checked == 'X') {
                 $headerName = $nameConversionArray['student_assigned_advisor'];
                 $content .= "<th>$headerName</th>";
            }
             if ($student_advisor_select_date_checked == 'X') {
                 $headerName = $nameConversionArray['student_advisor_select_date'];
                 $content .= "<th>$headerName</th>";
            }
             if ($student_advisor_class_timezone_checked == 'X') {
                 $headerName = $nameConversionArray['student_advisor_class_timezone'];
                 $content .= "<th>$headerName</th>";
            }
             if ($student_hold_reason_code_checked == 'X') {
                 $headerName = $nameConversionArray['student_hold_reason_code'];
                 $content .= "<th>$headerName</th>";
            }
             if ($student_class_priority_checked == 'X') {
                 $headerName = $nameConversionArray['student_class_priority'];
                 $content .= "<th>$headerName</th>";
            }
             if ($student_assigned_advisor_class_checked == 'X') {
                 $headerName = $nameConversionArray['student_assigned_advisor_class'];
                 $content .= "<th>$headerName</th>";
            }
             if ($student_promotable_checked == 'X') {
                 $headerName = $nameConversionArray['student_promotable'];
                 $content .= "<th>$headerName</th>";
            }
             if ($student_excluded_advisor_checked == 'X') {
                 $headerName = $nameConversionArray['student_excluded_advisor'];
                 $content .= "<th>$headerName</th>";
            }
             if ($student_survey_completion_date_checked == 'X') {
                 $headerName = $nameConversionArray['student_survey_completion_date'];
                 $content .= "<th>$headerName</th>";
            }
             if ($student_available_class_days_checked == 'X') {
                 $headerName = $nameConversionArray['student_available_class_days'];
                 $content .= "<th>$headerName</th>";
            }
             if ($istudent_ntervention_required_checked == 'X') {
                 $headerName = $nameConversionArray['istudent_ntervention_required'];
                 $content .= "<th>$headerName</th>";
            }
             if ($student_copy_control_checked == 'X') {
                 $headerName = $nameConversionArray['student_copy_control'];
                 $content .= "<th>$headerName</th>";
            }
             if ($student_first_class_choice_checked == 'X') {
                 $headerName = $nameConversionArray['student_first_class_choice'];
                 $content .= "<th>$headerName</th>";
            }
             if ($student_second_class_choice_checked == 'X') {
                 $headerName = $nameConversionArray['student_second_class_choice'];
                 $content .= "<th>$headerName</th>";
            }
             if ($student_third_class_choice_checked == 'X') {
                 $headerName = $nameConversionArray['student_third_class_choice'];
                 $content .= "<th>$headerName</th>";
            }
             if ($student_first_class_choice_utc_checked == 'X') {
                 $headerName = $nameConversionArray['student_first_class_choice_utc'];
                 $content .= "<th>$headerName</th>";
            }
             if ($student_second_class_choice_utc_checked == 'X') {
                 $headerName = $nameConversionArray['student_second_class_choice_utc'];
                 $content .= "<th>$headerName</th>";
            }
             if ($student_third_class_choice_utc_checked == 'X') {
                 $headerName = $nameConversionArray['student_third_class_choice_utc'];
                 $content .= "<th>$headerName</th>";
            }
             if ($student_catalog_options_checked == 'X') {
                 $headerName = $nameConversionArray['student_catalog_options'];
                 $content .= "<th>$headerName</th>";
            }
             if ($student_flexible_checked == 'X') {
                 $headerName = $nameConversionArray['student_flexible'];
                 $content .= "<th>$headerName</th>";
            }
             if ($student_date_created_checked == 'X') {
                 $headerName = $nameConversionArray['student_date_created'];
                 $content .= "<th>$headerName</th>";
            }
             if ($student_date_updated_checked == 'X') {
                 $headerName = $nameConversionArray['student_date_updated'];
                 $content .= "<th>$headerName</th>";
            }
			$content	.= "</tr>";	
		} else {
			$needComma = FALSE;
			$content		.= "<pre>";
            if ($user_ID_checked == 'X') {
                if ($needComma) {
                    $content .= '	';
                }
                $headerName = $nameConversionArray['user_ID'];
                $headerName = str_replace('<br />','_',$headerName);
                $content .= $headerName;
                $needComma = TRUE;
            }
            if ($user_call_sign_checked == 'X') {
                if ($needComma) {
                    $content .= '	';
                }
                $headerName = $nameConversionArray['user_call_sign'];
                $headerName = str_replace('<br />','_',$headerName);
                $content .= $headerName;
                $needComma = TRUE;
            }
            if ($user_first_name_checked == 'X') {
                if ($needComma) {
                    $content .= '	';
                }
                $headerName = $nameConversionArray['user_first_name'];
                $headerName = str_replace('<br />','_',$headerName);
                $content .= $headerName;
                $needComma = TRUE;
            }
            if ($user_last_name_checked == 'X') {
                if ($needComma) {
                    $content .= '	';
                }
                $headerName = $nameConversionArray['user_last_name'];
                $headerName = str_replace('<br />','_',$headerName);
                $content .= $headerName;
                $needComma = TRUE;
            }
            if ($user_email_checked == 'X') {
                if ($needComma) {
                    $content .= '	';
                }
                $headerName = $nameConversionArray['user_email'];
                $headerName = str_replace('<br />','_',$headerName);
                $content .= $headerName;
                $needComma = TRUE;
            }
            if ($user_ph_code_checked == 'X') {
                if ($needComma) {
                    $content .= '	';
                }
                $headerName = $nameConversionArray['user_ph_code'];
                $headerName = str_replace('<br />','_',$headerName);
                $content .= $headerName;
                $needComma = TRUE;
            }
            if ($user_phone_checked == 'X') {
                if ($needComma) {
                    $content .= '	';
                }
                $headerName = $nameConversionArray['user_phone'];
                $headerName = str_replace('<br />','_',$headerName);
                $content .= $headerName;
                $needComma = TRUE;
            }
            if ($user_city_checked == 'X') {
                if ($needComma) {
                    $content .= '	';
                }
                $headerName = $nameConversionArray['user_city'];
                $headerName = str_replace('<br />','_',$headerName);
                $content .= $headerName;
                $needComma = TRUE;
            }
            if ($user_state_checked == 'X') {
                if ($needComma) {
                    $content .= '	';
                }
                $headerName = $nameConversionArray['user_state'];
                $headerName = str_replace('<br />','_',$headerName);
                $content .= $headerName;
                $needComma = TRUE;
            }
            if ($user_zip_code_checked == 'X') {
                if ($needComma) {
                    $content .= '	';
                }
                $headerName = $nameConversionArray['user_zip_code'];
                $headerName = str_replace('<br />','_',$headerName);
                $content .= $headerName;
                $needComma = TRUE;
            }
            if ($user_country_code_checked == 'X') {
                if ($needComma) {
                    $content .= '	';
                }
                $headerName = $nameConversionArray['user_country_code'];
                $headerName = str_replace('<br />','_',$headerName);
                $content .= $headerName;
                $needComma = TRUE;
            }
            if ($user_country_checked == 'X') {
                if ($needComma) {
                    $content .= '	';
                }
                $headerName = $nameConversionArray['user_country'];
                $headerName = str_replace('<br />','_',$headerName);
                $content .= $headerName;
                $needComma = TRUE;
            }
            if ($user_whatsapp_checked == 'X') {
                if ($needComma) {
                    $content .= '	';
                }
                $headerName = $nameConversionArray['user_whatsapp'];
                $headerName = str_replace('<br />','_',$headerName);
                $content .= $headerName;
                $needComma = TRUE;
            }
            if ($user_telegram_checked == 'X') {
                if ($needComma) {
                    $content .= '	';
                }
                $headerName = $nameConversionArray['user_telegram'];
                $headerName = str_replace('<br />','_',$headerName);
                $content .= $headerName;
                $needComma = TRUE;
            }
            if ($user_signal_checked == 'X') {
                if ($needComma) {
                    $content .= '	';
                }
                $headerName = $nameConversionArray['user_signal'];
                $headerName = str_replace('<br />','_',$headerName);
                $content .= $headerName;
                $needComma = TRUE;
            }
            if ($user_messenger_checked == 'X') {
                if ($needComma) {
                    $content .= '	';
                }
                $headerName = $nameConversionArray['user_messenger'];
                $headerName = str_replace('<br />','_',$headerName);
                $content .= $headerName;
                $needComma = TRUE;
            }
            if ($user_action_log_checked == 'X') {
                if ($needComma) {
                    $content .= '	';
                }
                $headerName = $nameConversionArray['user_action_log'];
                $headerName = str_replace('<br />','_',$headerName);
                $content .= $headerName;
                $needComma = TRUE;
            }
            if ($user_timezone_id_checked == 'X') {
                if ($needComma) {
                    $content .= '	';
                }
                $headerName = $nameConversionArray['user_timezone_id'];
                $headerName = str_replace('<br />','_',$headerName);
                $content .= $headerName;
                $needComma = TRUE;
            }
            if ($user_languages_checked == 'X') {
                if ($needComma) {
                    $content .= '	';
                }
                $headerName = $nameConversionArray['user_languages'];
                $headerName = str_replace('<br />','_',$headerName);
                $content .= $headerName;
                $needComma = TRUE;
            }
            if ($user_survey_score_checked == 'X') {
                if ($needComma) {
                    $content .= '	';
                }
                $headerName = $nameConversionArray['user_survey_score'];
                $headerName = str_replace('<br />','_',$headerName);
                $content .= $headerName;
                $needComma = TRUE;
            }
            if ($user_is_admin_checked == 'X') {
                if ($needComma) {
                    $content .= '	';
                }
                $headerName = $nameConversionArray['user_is_admin'];
                $headerName = str_replace('<br />','_',$headerName);
                $content .= $headerName;
                $needComma = TRUE;
            }
            if ($user_role_checked == 'X') {
                if ($needComma) {
                    $content .= '	';
                }
                $headerName = $nameConversionArray['user_role'];
                $headerName = str_replace('<br />','_',$headerName);
                $content .= $headerName;
                $needComma = TRUE;
            }
            if ($user_date_created_checked == 'X') {
                if ($needComma) {
                    $content .= '	';
                }
                $headerName = $nameConversionArray['user_date_created'];
                $headerName = str_replace('<br />','_',$headerName);
                $content .= $headerName;
                $needComma = TRUE;
            }
            if ($user_date_updated_checked == 'X') {
                if ($needComma) {
                    $content .= '	';
                }
                $headerName = $nameConversionArray['user_date_updated'];
                $headerName = str_replace('<br />','_',$headerName);
                $content .= $headerName;
                $needComma = TRUE;
            }
            if ($student_id_checked == 'X') {
                if ($needComma) {
                    $content .= '	';
                }
                $headerName = $nameConversionArray['student_id'];
                $headerName = str_replace('<br />','_',$headerName);
                $content .= $headerName;
                $needComma = TRUE;
            }
            if ($student_call_sign_checked == 'X') {
                if ($needComma) {
                    $content .= '	';
                }
                $headerName = $nameConversionArray['student_call_sign'];
                $headerName = str_replace('<br />','_',$headerName);
                $content .= $headerName;
                $needComma = TRUE;
            }
            if ($student_time_zone_checked == 'X') {
                if ($needComma) {
                    $content .= '	';
                }
                $headerName = $nameConversionArray['student_time_zone'];
                $headerName = str_replace('<br />','_',$headerName);
                $content .= $headerName;
                $needComma = TRUE;
            }
            if ($student_timezone_offset_checked == 'X') {
                if ($needComma) {
                    $content .= '	';
                }
                $headerName = $nameConversionArray['student_timezone_offset'];
                $headerName = str_replace('<br />','_',$headerName);
                $content .= $headerName;
                $needComma = TRUE;
            }
            if ($student_youth_checked == 'X') {
                if ($needComma) {
                    $content .= '	';
                }
                $headerName = $nameConversionArray['student_youth'];
                $headerName = str_replace('<br />','_',$headerName);
                $content .= $headerName;
                $needComma = TRUE;
            }
            if ($student_age_checked == 'X') {
                if ($needComma) {
                    $content .= '	';
                }
                $headerName = $nameConversionArray['student_age'];
                $headerName = str_replace('<br />','_',$headerName);
                $content .= $headerName;
                $needComma = TRUE;
            }
            if ($student_parent_checked == 'X') {
                if ($needComma) {
                    $content .= '	';
                }
                $headerName = $nameConversionArray['student_parent'];
                $headerName = str_replace('<br />','_',$headerName);
                $content .= $headerName;
                $needComma = TRUE;
            }
            if ($student_parent_email_checked == 'X') {
                if ($needComma) {
                    $content .= '	';
                }
                $headerName = $nameConversionArray['student_parent_email'];
                $headerName = str_replace('<br />','_',$headerName);
                $content .= $headerName;
                $needComma = TRUE;
            }
            if ($student_level_checked == 'X') {
                if ($needComma) {
                    $content .= '	';
                }
                $headerName = $nameConversionArray['student_level'];
                $headerName = str_replace('<br />','_',$headerName);
                $content .= $headerName;
                $needComma = TRUE;
            }
            if ($student_waiting_list_checked == 'X') {
                if ($needComma) {
                    $content .= '	';
                }
                $headerName = $nameConversionArray['student_waiting_list'];
                $headerName = str_replace('<br />','_',$headerName);
                $content .= $headerName;
                $needComma = TRUE;
            }
            if ($student_request_date_checked == 'X') {
                if ($needComma) {
                    $content .= '	';
                }
                $headerName = $nameConversionArray['student_request_date'];
                $headerName = str_replace('<br />','_',$headerName);
                $content .= $headerName;
                $needComma = TRUE;
            }
            if ($student_semester_checked == 'X') {
                if ($needComma) {
                    $content .= '	';
                }
                $headerName = $nameConversionArray['student_semester'];
                $headerName = str_replace('<br />','_',$headerName);
                $content .= $headerName;
                $needComma = TRUE;
            }
            if ($student_notes_checked == 'X') {
                if ($needComma) {
                    $content .= '	';
                }
                $headerName = $nameConversionArray['student_notes'];
                $headerName = str_replace('<br />','_',$headerName);
                $content .= $headerName;
                $needComma = TRUE;
            }
            if ($student_welcome_date_checked == 'X') {
                if ($needComma) {
                    $content .= '	';
                }
                $headerName = $nameConversionArray['student_welcome_date'];
                $headerName = str_replace('<br />','_',$headerName);
                $content .= $headerName;
                $needComma = TRUE;
            }
            if ($student_email_sent_date_checked == 'X') {
                if ($needComma) {
                    $content .= '	';
                }
                $headerName = $nameConversionArray['student_email_sent_date'];
                $headerName = str_replace('<br />','_',$headerName);
                $content .= $headerName;
                $needComma = TRUE;
            }
            if ($student_email_number_checked == 'X') {
                if ($needComma) {
                    $content .= '	';
                }
                $headerName = $nameConversionArray['student_email_number'];
                $headerName = str_replace('<br />','_',$headerName);
                $content .= $headerName;
                $needComma = TRUE;
            }
            if ($student_response_checked == 'X') {
                if ($needComma) {
                    $content .= '	';
                }
                $headerName = $nameConversionArray['student_response'];
                $headerName = str_replace('<br />','_',$headerName);
                $content .= $headerName;
                $needComma = TRUE;
            }
            if ($student_response_date_checked == 'X') {
                if ($needComma) {
                    $content .= '	';
                }
                $headerName = $nameConversionArray['student_response_date'];
                $headerName = str_replace('<br />','_',$headerName);
                $content .= $headerName;
                $needComma = TRUE;
            }
            if ($student_abandoned_checked == 'X') {
                if ($needComma) {
                    $content .= '	';
                }
                $headerName = $nameConversionArray['student_abandoned'];
                $headerName = str_replace('<br />','_',$headerName);
                $content .= $headerName;
                $needComma = TRUE;
            }
            if ($student_status_checked == 'X') {
                if ($needComma) {
                    $content .= '	';
                }
                $headerName = $nameConversionArray['student_status'];
                $headerName = str_replace('<br />','_',$headerName);
                $content .= $headerName;
                $needComma = TRUE;
            }
            if ($student_action_log_checked == 'X') {
                if ($needComma) {
                    $content .= '	';
                }
                $headerName = $nameConversionArray['student_action_log'];
                $headerName = str_replace('<br />','_',$headerName);
                $content .= $headerName;
                $needComma = TRUE;
            }
            if ($student_pre_assigned_advisor_checked == 'X') {
                if ($needComma) {
                    $content .= '	';
                }
                $headerName = $nameConversionArray['student_pre_assigned_advisor'];
                $headerName = str_replace('<br />','_',$headerName);
                $content .= $headerName;
                $needComma = TRUE;
            }
            if ($student_selected_date_checked == 'X') {
                if ($needComma) {
                    $content .= '	';
                }
                $headerName = $nameConversionArray['student_selected_date'];
                $headerName = str_replace('<br />','_',$headerName);
                $content .= $headerName;
                $needComma = TRUE;
            }
            if ($student_no_catalog_checked == 'X') {
                if ($needComma) {
                    $content .= '	';
                }
                $headerName = $nameConversionArray['student_no_catalog'];
                $headerName = str_replace('<br />','_',$headerName);
                $content .= $headerName;
                $needComma = TRUE;
            }
            if ($student_hold_override_checked == 'X') {
                if ($needComma) {
                    $content .= '	';
                }
                $headerName = $nameConversionArray['student_hold_override'];
                $headerName = str_replace('<br />','_',$headerName);
                $content .= $headerName;
                $needComma = TRUE;
            }
            if ($student_messaging_checked == 'X') {
                if ($needComma) {
                    $content .= '	';
                }
                $headerName = $nameConversionArray['student_messaging'];
                $headerName = str_replace('<br />','_',$headerName);
                $content .= $headerName;
                $needComma = TRUE;
            }
            if ($student_assigned_advisor_checked == 'X') {
                if ($needComma) {
                    $content .= '	';
                }
                $headerName = $nameConversionArray['student_assigned_advisor'];
                $headerName = str_replace('<br />','_',$headerName);
                $content .= $headerName;
                $needComma = TRUE;
            }
            if ($student_advisor_select_date_checked == 'X') {
                if ($needComma) {
                    $content .= '	';
                }
                $headerName = $nameConversionArray['student_advisor_select_date'];
                $headerName = str_replace('<br />','_',$headerName);
                $content .= $headerName;
                $needComma = TRUE;
            }
            if ($student_advisor_class_timezone_checked == 'X') {
                if ($needComma) {
                    $content .= '	';
                }
                $headerName = $nameConversionArray['student_advisor_class_timezone'];
                $headerName = str_replace('<br />','_',$headerName);
                $content .= $headerName;
                $needComma = TRUE;
            }
            if ($student_hold_reason_code_checked == 'X') {
                if ($needComma) {
                    $content .= '	';
                }
                $headerName = $nameConversionArray['student_hold_reason_code'];
                $headerName = str_replace('<br />','_',$headerName);
                $content .= $headerName;
                $needComma = TRUE;
            }
            if ($student_class_priority_checked == 'X') {
                if ($needComma) {
                    $content .= '	';
                }
                $headerName = $nameConversionArray['student_class_priority'];
                $headerName = str_replace('<br />','_',$headerName);
                $content .= $headerName;
                $needComma = TRUE;
            }
            if ($student_assigned_advisor_class_checked == 'X') {
                if ($needComma) {
                    $content .= '	';
                }
                $headerName = $nameConversionArray['student_assigned_advisor_class'];
                $headerName = str_replace('<br />','_',$headerName);
                $content .= $headerName;
                $needComma = TRUE;
            }
            if ($student_promotable_checked == 'X') {
                if ($needComma) {
                    $content .= '	';
                }
                $headerName = $nameConversionArray['student_promotable'];
                $headerName = str_replace('<br />','_',$headerName);
                $content .= $headerName;
                $needComma = TRUE;
            }
            if ($student_excluded_advisor_checked == 'X') {
                if ($needComma) {
                    $content .= '	';
                }
                $headerName = $nameConversionArray['student_excluded_advisor'];
                $headerName = str_replace('<br />','_',$headerName);
                $content .= $headerName;
                $needComma = TRUE;
            }
            if ($student_survey_completion_date_checked == 'X') {
                if ($needComma) {
                    $content .= '	';
                }
                $headerName = $nameConversionArray['student_survey_completion_date'];
                $headerName = str_replace('<br />','_',$headerName);
                $content .= $headerName;
                $needComma = TRUE;
            }
            if ($student_available_class_days_checked == 'X') {
                if ($needComma) {
                    $content .= '	';
                }
                $headerName = $nameConversionArray['student_available_class_days'];
                $headerName = str_replace('<br />','_',$headerName);
                $content .= $headerName;
                $needComma = TRUE;
            }
            if ($istudent_ntervention_required_checked == 'X') {
                if ($needComma) {
                    $content .= '	';
                }
                $headerName = $nameConversionArray['istudent_ntervention_required'];
                $headerName = str_replace('<br />','_',$headerName);
                $content .= $headerName;
                $needComma = TRUE;
            }
            if ($student_copy_control_checked == 'X') {
                if ($needComma) {
                    $content .= '	';
                }
                $headerName = $nameConversionArray['student_copy_control'];
                $headerName = str_replace('<br />','_',$headerName);
                $content .= $headerName;
                $needComma = TRUE;
            }
            if ($student_first_class_choice_checked == 'X') {
                if ($needComma) {
                    $content .= '	';
                }
                $headerName = $nameConversionArray['student_first_class_choice'];
                $headerName = str_replace('<br />','_',$headerName);
                $content .= $headerName;
                $needComma = TRUE;
            }
            if ($student_second_class_choice_checked == 'X') {
                if ($needComma) {
                    $content .= '	';
                }
                $headerName = $nameConversionArray['student_second_class_choice'];
                $headerName = str_replace('<br />','_',$headerName);
                $content .= $headerName;
                $needComma = TRUE;
            }
            if ($student_third_class_choice_checked == 'X') {
                if ($needComma) {
                    $content .= '	';
                }
                $headerName = $nameConversionArray['student_third_class_choice'];
                $headerName = str_replace('<br />','_',$headerName);
                $content .= $headerName;
                $needComma = TRUE;
            }
            if ($student_first_class_choice_utc_checked == 'X') {
                if ($needComma) {
                    $content .= '	';
                }
                $headerName = $nameConversionArray['student_first_class_choice_utc'];
                $headerName = str_replace('<br />','_',$headerName);
                $content .= $headerName;
                $needComma = TRUE;
            }
            if ($student_second_class_choice_utc_checked == 'X') {
                if ($needComma) {
                    $content .= '	';
                }
                $headerName = $nameConversionArray['student_second_class_choice_utc'];
                $headerName = str_replace('<br />','_',$headerName);
                $content .= $headerName;
                $needComma = TRUE;
            }
            if ($student_third_class_choice_utc_checked == 'X') {
                if ($needComma) {
                    $content .= '	';
                }
                $headerName = $nameConversionArray['student_third_class_choice_utc'];
                $headerName = str_replace('<br />','_',$headerName);
                $content .= $headerName;
                $needComma = TRUE;
            }
            if ($student_catalog_options_checked == 'X') {
                if ($needComma) {
                    $content .= '	';
                }
                $headerName = $nameConversionArray['student_catalog_options'];
                $headerName = str_replace('<br />','_',$headerName);
                $content .= $headerName;
                $needComma = TRUE;
            }
            if ($student_flexible_checked == 'X') {
                if ($needComma) {
                    $content .= '	';
                }
                $headerName = $nameConversionArray['student_flexible'];
                $headerName = str_replace('<br />','_',$headerName);
                $content .= $headerName;
                $needComma = TRUE;
            }
            if ($student_date_created_checked == 'X') {
                if ($needComma) {
                    $content .= '	';
                }
                $headerName = $nameConversionArray['student_date_created'];
                $headerName = str_replace('<br />','_',$headerName);
                $content .= $headerName;
                $needComma = TRUE;
            }
            if ($student_date_updated_checked == 'X') {
                if ($needComma) {
                    $content .= '	';
                }
                $headerName = $nameConversionArray['student_date_updated'];
                $headerName = str_replace('<br />','_',$headerName);
                $content .= $headerName;
                $needComma = TRUE;
            }
			$content	.= "\n";
		}
///// read the student table
		$myCount					= 0;
		$wpw1_cwa_student			= $wpdb->get_results($sql);
		if (FALSE === $wpw1_cwa_student) {		// no record found
			if ($doDebug) {
				echo "FUNCTION: No data found in $mode_type<br />\n";
			}
		} else {
			$numSRows				= $wpdb->num_rows;
			if ($doDebug) {
				echo "ran $sql<br />and retrieved $numSRows rows<br />";
			}
			if ($numSRows > 0) {
				foreach ($wpw1_cwa_student as $studentRow) {
					$user_id				= $studentRow->user_ID;
					$user_call_sign			= $studentRow->user_call_sign;
					$user_first_name		= $studentRow->user_first_name;
					$user_last_name			= $studentRow->user_last_name;
					$user_email				= $studentRow->user_email;
					$user_ph_code			= $studentRow->user_ph_code;
					$user_phone				= $studentRow->user_phone;
					$user_city				= $studentRow->user_city;
					$user_state				= $studentRow->user_state;
					$user_zip_code			= $studentRow->user_zip_code;
					$user_country_code		= $studentRow->user_country_code;
					$user_country			= $studentRow->user_country;
					$user_whatsapp			= $studentRow->user_whatsapp;
					$user_telegram			= $studentRow->user_telegram;
					$user_signal			= $studentRow->user_signal;
					$user_messenger			= $studentRow->user_messenger;
					$user_action_log		= $studentRow->user_action_log;
					$user_timezone_id		= $studentRow->user_timezone_id;
					$user_languages			= $studentRow->user_languages;
					$user_survey_score		= $studentRow->user_survey_score;
					$user_is_admin			= $studentRow->user_is_admin;
					$user_role				= $studentRow->user_role;
					$user_prev_callsign		= $studentRow->user_prev_callsign;
					$user_date_created		= $studentRow->user_date_created;
					$user_date_updated		= $studentRow->user_date_updated;

					$student_ID								= $studentRow->student_id;
					$student_call_sign						= strtoupper($studentRow->student_call_sign);
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
					$student_status 		 				= strtoupper($studentRow->student_status);
					$student_action_log  					= $studentRow->student_action_log;
					$student_pre_assigned_advisor  			= $studentRow->student_pre_assigned_advisor;
					$student_selected_date  				= $studentRow->student_selected_date;
					$student_no_catalog			 			= $studentRow->student_no_catalog;
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

					$myCount++;
	
					if ($doDebug) {
						echo "Processing $student_call_sign<br />\n";
					}
					if ($output_type == 'table') {
						$content	.= "<tr>";
                       if ($user_ID_checked == 'X') {
                           $content .= "<td style='vertical-align:top'>$user_ID</td>";
                        }
                       if ($user_call_sign_checked == 'X') {
                           $content .= "<td style='vertical-align:top'><a href='$siteURL/cwa-display-and-update-student-signup-information/?strpass=2&request_type=callsign&request_info=$user_call_sign&doDebug=$doDebug&testMode=$testMode'  
                           				target='_blank'>$user_call_sign</td>";
                        }
                       if ($user_first_name_checked == 'X') {
                           $content .= "<td style='vertical-align:top'>$user_first_name</td>";
                        }
                       if ($user_last_name_checked == 'X') {
                           $content .= "<td style='vertical-align:top'>$user_last_name</td>";
                        }
                       if ($user_email_checked == 'X') {
                           $content .= "<td style='vertical-align:top'>$user_email</td>";
                        }
                       if ($user_ph_code_checked == 'X') {
                           $content .= "<td style='vertical-align:top'>$user_ph_code</td>";
                        }
                       if ($user_phone_checked == 'X') {
                           $content .= "<td style='vertical-align:top'>$user_phone</td>";
                        }
                       if ($user_city_checked == 'X') {
                           $content .= "<td style='vertical-align:top'>$user_city</td>";
                        }
                       if ($user_state_checked == 'X') {
                           $content .= "<td style='vertical-align:top'>$user_state</td>";
                        }
                       if ($user_zip_code_checked == 'X') {
                           $content .= "<td style='vertical-align:top'>$user_zip_code</td>";
                        }
                       if ($user_country_code_checked == 'X') {
                           $content .= "<td style='vertical-align:top'>$user_country_code</td>";
                        }
                       if ($user_country_checked == 'X') {
                           $content .= "<td style='vertical-align:top'>$user_country</td>";
                        }
                       if ($user_whatsapp_checked == 'X') {
                           $content .= "<td style='vertical-align:top'>$user_whatsapp</td>";
                        }
                       if ($user_telegram_checked == 'X') {
                           $content .= "<td style='vertical-align:top'>$user_telegram</td>";
                        }
                       if ($user_signal_checked == 'X') {
                           $content .= "<td style='vertical-align:top'>$user_signal</td>";
                        }
                       if ($user_messenger_checked == 'X') {
                           $content .= "<td style='vertical-align:top'>$user_messenger</td>";
                        }
                       if ($user_action_log_checked == 'X') {
                       		$mystr = formatActionLog($user_action_log);
                           $content .= "<td style='vertical-align:top'>$mystr</td>";
                        }
                       if ($user_timezone_id_checked == 'X') {
                           $content .= "<td style='vertical-align:top'>$user_timezone_id</td>";
                        }
                       if ($user_languages_checked == 'X') {
                           $content .= "<td style='vertical-align:top'>$user_languages</td>";
                        }
                       if ($user_survey_score_checked == 'X') {
                           $content .= "<td style='vertical-align:top'>$user_survey_score</td>";
                        }
                       if ($user_is_admin_checked == 'X') {
                           $content .= "<td style='vertical-align:top'>$user_is_admin</td>";
                        }
                       if ($user_role_checked == 'X') {
                           $content .= "<td style='vertical-align:top'>$user_role</td>";
                        }
                       if ($user_date_created_checked == 'X') {
                           $content .= "<td style='vertical-align:top'>$user_date_created</td>";
                        }
                       if ($user_date_updated_checked == 'X') {
                           $content .= "<td style='vertical-align:top'>$user_date_updated</td>";
                        }
                       if ($student_id_checked == 'X') {
                           $content .= "<td style='vertical-align:top'>$student_id</td>";
                        }
                       if ($student_call_sign_checked == 'X') {
                           $content .= "<td style='vertical-align:top'>$student_call_sign</td>";
                        }
                       if ($student_time_zone_checked == 'X') {
                           $content .= "<td style='vertical-align:top'>$student_time_zone</td>";
                        }
                       if ($student_timezone_offset_checked == 'X') {
                           $content .= "<td style='vertical-align:top'>$student_timezone_offset</td>";
                        }
                       if ($student_youth_checked == 'X') {
                           $content .= "<td style='vertical-align:top'>$student_youth</td>";
                        }
                       if ($student_age_checked == 'X') {
                           $content .= "<td style='vertical-align:top'>$student_age</td>";
                        }
                       if ($student_parent_checked == 'X') {
                           $content .= "<td style='vertical-align:top'>$student_parent</td>";
                        }
                       if ($student_parent_email_checked == 'X') {
                           $content .= "<td style='vertical-align:top'>$student_parent_email</td>";
                        }
                       if ($student_level_checked == 'X') {
                           $content .= "<td style='vertical-align:top'>$student_level</td>";
                        }
                       if ($student_waiting_list_checked == 'X') {
                           $content .= "<td style='vertical-align:top'>$student_waiting_list</td>";
                        }
                       if ($student_request_date_checked == 'X') {
                           $content .= "<td style='vertical-align:top'>$student_request_date</td>";
                        }
                       if ($student_semester_checked == 'X') {
                           $content .= "<td style='vertical-align:top'>$student_semester</td>";
                        }
                       if ($student_notes_checked == 'X') {
                           $content .= "<td style='vertical-align:top'>$student_notes</td>";
                        }
                       if ($student_welcome_date_checked == 'X') {
                           $content .= "<td style='vertical-align:top'>$student_welcome_date</td>";
                        }
                       if ($student_email_sent_date_checked == 'X') {
                           $content .= "<td style='vertical-align:top'>$student_email_sent_date</td>";
                        }
                       if ($student_email_number_checked == 'X') {
                           $content .= "<td style='vertical-align:top'>$student_email_number</td>";
                        }
                       if ($student_response_checked == 'X') {
                           $content .= "<td style='vertical-align:top'>$student_response</td>";
                        }
                       if ($student_response_date_checked == 'X') {
                           $content .= "<td style='vertical-align:top'>$student_response_date</td>";
                        }
                       if ($student_abandoned_checked == 'X') {
                           $content .= "<td style='vertical-align:top'>$student_abandoned</td>";
                        }
                       if ($student_status_checked == 'X') {
                           $content .= "<td style='vertical-align:top'>$student_status</td>";
                        }
                       if ($student_action_log_checked == 'X') {
                       		$mystr = formatActionLog($student_action_log);
                           $content .= "<td style='vertical-align:top'>$mystr</td>";
        				}
                       if ($student_pre_assigned_advisor_checked == 'X') {
                           $content .= "<td style='vertical-align:top'>$student_pre_assigned_advisor</td>";
                        }
                       if ($student_selected_date_checked == 'X') {
                           $content .= "<td style='vertical-align:top'>$student_selected_date</td>";
                        }
                       if ($student_no_catalog_checked == 'X') {
                           $content .= "<td style='vertical-align:top'>$student_no_catalog</td>";
                        }
                       if ($student_hold_override_checked == 'X') {
                           $content .= "<td style='vertical-align:top'>$student_hold_override</td>";
                        }
                       if ($student_messaging_checked == 'X') {
                           $content .= "<td style='vertical-align:top'>$student_messaging</td>";
                        }
                       if ($student_assigned_advisor_checked == 'X') {
                           $content .= "<td style='vertical-align:top'>$student_assigned_advisor</td>";
                        }
                       if ($student_advisor_select_date_checked == 'X') {
                           $content .= "<td style='vertical-align:top'>$student_advisor_select_date</td>";
                        }
                       if ($student_advisor_class_timezone_checked == 'X') {
                           $content .= "<td style='vertical-align:top'>$student_advisor_class_timezone</td>";
                        }
                       if ($student_hold_reason_code_checked == 'X') {
                           $content .= "<td style='vertical-align:top'>$student_hold_reason_code</td>";
                        }
                       if ($student_class_priority_checked == 'X') {
                           $content .= "<td style='vertical-align:top'>$student_class_priority</td>";
                        }
                       if ($student_assigned_advisor_class_checked == 'X') {
                           $content .= "<td style='vertical-align:top'>$student_assigned_advisor_class</td>";
                        }
                       if ($student_promotable_checked == 'X') {
                           $content .= "<td style='vertical-align:top'>$student_promotable</td>";
                        }
                       if ($student_excluded_advisor_checked == 'X') {
                           $content .= "<td style='vertical-align:top'>$student_excluded_advisor</td>";
                        }
                       if ($student_survey_completion_date_checked == 'X') {
                           $content .= "<td style='vertical-align:top'>$student_survey_completion_date</td>";
                        }
                       if ($student_available_class_days_checked == 'X') {
                           $content .= "<td style='vertical-align:top'>$student_available_class_days</td>";
                        }
                       if ($istudent_ntervention_required_checked == 'X') {
                           $content .= "<td style='vertical-align:top'>$istudent_ntervention_required</td>";
                        }
                       if ($student_copy_control_checked == 'X') {
                           $content .= "<td style='vertical-align:top'>$student_copy_control</td>";
                        }
                       if ($student_first_class_choice_checked == 'X') {
                           $content .= "<td style='vertical-align:top'>$student_first_class_choice</td>";
                        }
                       if ($student_second_class_choice_checked == 'X') {
                           $content .= "<td style='vertical-align:top'>$student_second_class_choice</td>";
                        }
                       if ($student_third_class_choice_checked == 'X') {
                           $content .= "<td style='vertical-align:top'>$student_third_class_choice</td>";
                        }
                       if ($student_first_class_choice_utc_checked == 'X') {
                           $content .= "<td style='vertical-align:top'>$student_first_class_choice_utc</td>";
                        }
                       if ($student_second_class_choice_utc_checked == 'X') {
                           $content .= "<td style='vertical-align:top'>$student_second_class_choice_utc</td>";
                        }
                       if ($student_third_class_choice_utc_checked == 'X') {
                           $content .= "<td style='vertical-align:top'>$student_third_class_choice_utc</td>";
                        }
                       if ($student_catalog_options_checked == 'X') {
                           $content .= "<td style='vertical-align:top'>$student_catalog_options</td>";
                        }
                       if ($student_flexible_checked == 'X') {
                           $content .= "<td style='vertical-align:top'>$student_flexible</td>";
                        }
                       if ($student_date_created_checked == 'X') {
                           $content .= "<td style='vertical-align:top'>$student_date_created</td>";
                        }
                       if ($student_date_updated_checked == 'X') {
                           $content .= "<td style='vertical-align:top'>$student_date_updated</td>";
                        }
						$content	.= "</tr>\n";
					} else {			// output will be a comma separated file
						$needComma = FALSE;
                        if ($user_ID_checked == 'X') {
                            if ($needComma) {
                                $content .= '	';
                            }
                            $content .= $user_ID;
                            $needComma = TRUE;
                        }
                        if ($user_call_sign_checked == 'X') {
                            if ($needComma) {
                                $content .= '	';
                            }
                            $content .= $user_call_sign;
                            $needComma = TRUE;
                        }
                        if ($user_first_name_checked == 'X') {
                            if ($needComma) {
                                $content .= '	';
                            }
                            $content .= $user_first_name;
                            $needComma = TRUE;
                        }
                        if ($user_last_name_checked == 'X') {
                            if ($needComma) {
                                $content .= '	';
                            }
                            $content .= $user_last_name;
                            $needComma = TRUE;
                        }
                        if ($user_email_checked == 'X') {
                            if ($needComma) {
                                $content .= '	';
                            }
                            $content .= $user_email;
                            $needComma = TRUE;
                        }
                        if ($user_ph_code_checked == 'X') {
                            if ($needComma) {
                                $content .= '	';
                            }
                            $content .= $user_ph_code;
                            $needComma = TRUE;
                        }
                        if ($user_phone_checked == 'X') {
                            if ($needComma) {
                                $content .= '	';
                            }
                            $content .= $user_phone;
                            $needComma = TRUE;
                        }
                        if ($user_city_checked == 'X') {
                            if ($needComma) {
                                $content .= '	';
                            }
                            $content .= $user_city;
                            $needComma = TRUE;
                        }
                        if ($user_state_checked == 'X') {
                            if ($needComma) {
                                $content .= '	';
                            }
                            $content .= $user_state;
                            $needComma = TRUE;
                        }
                        if ($user_zip_code_checked == 'X') {
                            if ($needComma) {
                                $content .= '	';
                            }
                            $content .= $user_zip_code;
                            $needComma = TRUE;
                        }
                        if ($user_country_code_checked == 'X') {
                            if ($needComma) {
                                $content .= '	';
                            }
                            $content .= $user_country_code;
                            $needComma = TRUE;
                        }
                        if ($user_country_checked == 'X') {
                            if ($needComma) {
                                $content .= '	';
                            }
                            $content .= $user_country;
                            $needComma = TRUE;
                        }
                        if ($user_whatsapp_checked == 'X') {
                            if ($needComma) {
                                $content .= '	';
                            }
                            $content .= $user_whatsapp;
                            $needComma = TRUE;
                        }
                        if ($user_telegram_checked == 'X') {
                            if ($needComma) {
                                $content .= '	';
                            }
                            $content .= $user_telegram;
                            $needComma = TRUE;
                        }
                        if ($user_signal_checked == 'X') {
                            if ($needComma) {
                                $content .= '	';
                            }
                            $content .= $user_signal;
                            $needComma = TRUE;
                        }
                        if ($user_messenger_checked == 'X') {
                            if ($needComma) {
                                $content .= '	';
                            }
                            $content .= $user_messenger;
                            $needComma = TRUE;
                        }
                        if ($user_action_log_checked == 'X') {
                            if ($needComma) {
                                $content .= '	';
                            }
                            $content .= $user_action_log;
                            $needComma = TRUE;
                        }
                        if ($user_timezone_id_checked == 'X') {
                            if ($needComma) {
                                $content .= '	';
                            }
                            $content .= $user_timezone_id;
                            $needComma = TRUE;
                        }
                        if ($user_languages_checked == 'X') {
                            if ($needComma) {
                                $content .= '	';
                            }
                            $content .= $user_languages;
                            $needComma = TRUE;
                        }
                        if ($user_survey_score_checked == 'X') {
                            if ($needComma) {
                                $content .= '	';
                            }
                            $content .= $user_survey_score;
                            $needComma = TRUE;
                        }
                        if ($user_is_admin_checked == 'X') {
                            if ($needComma) {
                                $content .= '	';
                            }
                            $content .= $user_is_admin;
                            $needComma = TRUE;
                        }
                        if ($user_role_checked == 'X') {
                            if ($needComma) {
                                $content .= '	';
                            }
                            $content .= $user_role;
                            $needComma = TRUE;
                        }
                        if ($user_date_created_checked == 'X') {
                            if ($needComma) {
                                $content .= '	';
                            }
                            $content .= $user_date_created;
                            $needComma = TRUE;
                        }
                        if ($user_date_updated_checked == 'X') {
                            if ($needComma) {
                                $content .= '	';
                            }
                            $content .= $user_date_updated;
                            $needComma = TRUE;
                        }
                        if ($student_id_checked == 'X') {
                            if ($needComma) {
                                $content .= '	';
                            }
                            $content .= $student_id;
                            $needComma = TRUE;
                        }
                        if ($student_call_sign_checked == 'X') {
                            if ($needComma) {
                                $content .= '	';
                            }
                            $content .= $student_call_sign;
                            $needComma = TRUE;
                        }
                        if ($student_time_zone_checked == 'X') {
                            if ($needComma) {
                                $content .= '	';
                            }
                            $content .= $student_time_zone;
                            $needComma = TRUE;
                        }
                        if ($student_timezone_offset_checked == 'X') {
                            if ($needComma) {
                                $content .= '	';
                            }
                            $content .= $student_timezone_offset;
                            $needComma = TRUE;
                        }
                        if ($student_youth_checked == 'X') {
                            if ($needComma) {
                                $content .= '	';
                            }
                            $content .= $student_youth;
                            $needComma = TRUE;
                        }
                        if ($student_age_checked == 'X') {
                            if ($needComma) {
                                $content .= '	';
                            }
                            $content .= $student_age;
                            $needComma = TRUE;
                        }
                        if ($student_parent_checked == 'X') {
                            if ($needComma) {
                                $content .= '	';
                            }
                            $content .= $student_parent;
                            $needComma = TRUE;
                        }
                        if ($student_parent_email_checked == 'X') {
                            if ($needComma) {
                                $content .= '	';
                            }
                            $content .= $student_parent_email;
                            $needComma = TRUE;
                        }
                        if ($student_level_checked == 'X') {
                            if ($needComma) {
                                $content .= '	';
                            }
                            $content .= $student_level;
                            $needComma = TRUE;
                        }
                        if ($student_waiting_list_checked == 'X') {
                            if ($needComma) {
                                $content .= '	';
                            }
                            $content .= $student_waiting_list;
                            $needComma = TRUE;
                        }
                        if ($student_request_date_checked == 'X') {
                            if ($needComma) {
                                $content .= '	';
                            }
                            $content .= $student_request_date;
                            $needComma = TRUE;
                        }
                        if ($student_semester_checked == 'X') {
                            if ($needComma) {
                                $content .= '	';
                            }
                            $content .= $student_semester;
                            $needComma = TRUE;
                        }
                        if ($student_notes_checked == 'X') {
                            if ($needComma) {
                                $content .= '	';
                            }
                            $content .= $student_notes;
                            $needComma = TRUE;
                        }
                        if ($student_welcome_date_checked == 'X') {
                            if ($needComma) {
                                $content .= '	';
                            }
                            $content .= $student_welcome_date;
                            $needComma = TRUE;
                        }
                        if ($student_email_sent_date_checked == 'X') {
                            if ($needComma) {
                                $content .= '	';
                            }
                            $content .= $student_email_sent_date;
                            $needComma = TRUE;
                        }
                        if ($student_email_number_checked == 'X') {
                            if ($needComma) {
                                $content .= '	';
                            }
                            $content .= $student_email_number;
                            $needComma = TRUE;
                        }
                        if ($student_response_checked == 'X') {
                            if ($needComma) {
                                $content .= '	';
                            }
                            $content .= $student_response;
                            $needComma = TRUE;
                        }
                        if ($student_response_date_checked == 'X') {
                            if ($needComma) {
                                $content .= '	';
                            }
                            $content .= $student_response_date;
                            $needComma = TRUE;
                        }
                        if ($student_abandoned_checked == 'X') {
                            if ($needComma) {
                                $content .= '	';
                            }
                            $content .= $student_abandoned;
                            $needComma = TRUE;
                        }
                        if ($student_status_checked == 'X') {
                            if ($needComma) {
                                $content .= '	';
                            }
                            $content .= $student_status;
                            $needComma = TRUE;
                        }
                        if ($student_action_log_checked == 'X') {
                            if ($needComma) {
                                $content .= '	';
                            }
                            $content .= $student_action_log;
                            $needComma = TRUE;
                        }
                        if ($student_pre_assigned_advisor_checked == 'X') {
                            if ($needComma) {
                                $content .= '	';
                            }
                            $content .= $student_pre_assigned_advisor;
                            $needComma = TRUE;
                        }
                        if ($student_selected_date_checked == 'X') {
                            if ($needComma) {
                                $content .= '	';
                            }
                            $content .= $student_selected_date;
                            $needComma = TRUE;
                        }
                        if ($student_no_catalog_checked == 'X') {
                            if ($needComma) {
                                $content .= '	';
                            }
                            $content .= $student_no_catalog;
                            $needComma = TRUE;
                        }
                        if ($student_hold_override_checked == 'X') {
                            if ($needComma) {
                                $content .= '	';
                            }
                            $content .= $student_hold_override;
                            $needComma = TRUE;
                        }
                        if ($student_messaging_checked == 'X') {
                            if ($needComma) {
                                $content .= '	';
                            }
                            $content .= $student_messaging;
                            $needComma = TRUE;
                        }
                        if ($student_assigned_advisor_checked == 'X') {
                            if ($needComma) {
                                $content .= '	';
                            }
                            $content .= $student_assigned_advisor;
                            $needComma = TRUE;
                        }
                        if ($student_advisor_select_date_checked == 'X') {
                            if ($needComma) {
                                $content .= '	';
                            }
                            $content .= $student_advisor_select_date;
                            $needComma = TRUE;
                        }
                        if ($student_advisor_class_timezone_checked == 'X') {
                            if ($needComma) {
                                $content .= '	';
                            }
                            $content .= $student_advisor_class_timezone;
                            $needComma = TRUE;
                        }
                        if ($student_hold_reason_code_checked == 'X') {
                            if ($needComma) {
                                $content .= '	';
                            }
                            $content .= $student_hold_reason_code;
                            $needComma = TRUE;
                        }
                        if ($student_class_priority_checked == 'X') {
                            if ($needComma) {
                                $content .= '	';
                            }
                            $content .= $student_class_priority;
                            $needComma = TRUE;
                        }
                        if ($student_assigned_advisor_class_checked == 'X') {
                            if ($needComma) {
                                $content .= '	';
                            }
                            $content .= $student_assigned_advisor_class;
                            $needComma = TRUE;
                        }
                        if ($student_promotable_checked == 'X') {
                            if ($needComma) {
                                $content .= '	';
                            }
                            $content .= $student_promotable;
                            $needComma = TRUE;
                        }
                        if ($student_excluded_advisor_checked == 'X') {
                            if ($needComma) {
                                $content .= '	';
                            }
                            $content .= $student_excluded_advisor;
                            $needComma = TRUE;
                        }
                        if ($student_survey_completion_date_checked == 'X') {
                            if ($needComma) {
                                $content .= '	';
                            }
                            $content .= $student_survey_completion_date;
                            $needComma = TRUE;
                        }
                        if ($student_available_class_days_checked == 'X') {
                            if ($needComma) {
                                $content .= '	';
                            }
                            $content .= $student_available_class_days;
                            $needComma = TRUE;
                        }
                        if ($istudent_ntervention_required_checked == 'X') {
                            if ($needComma) {
                                $content .= '	';
                            }
                            $content .= $istudent_ntervention_required;
                            $needComma = TRUE;
                        }
                        if ($student_copy_control_checked == 'X') {
                            if ($needComma) {
                                $content .= '	';
                            }
                            $content .= $student_copy_control;
                            $needComma = TRUE;
                        }
                        if ($student_first_class_choice_checked == 'X') {
                            if ($needComma) {
                                $content .= '	';
                            }
                            $content .= $student_first_class_choice;
                            $needComma = TRUE;
                        }
                        if ($student_second_class_choice_checked == 'X') {
                            if ($needComma) {
                                $content .= '	';
                            }
                            $content .= $student_second_class_choice;
                            $needComma = TRUE;
                        }
                        if ($student_third_class_choice_checked == 'X') {
                            if ($needComma) {
                                $content .= '	';
                            }
                            $content .= $student_third_class_choice;
                            $needComma = TRUE;
                        }
                        if ($student_first_class_choice_utc_checked == 'X') {
                            if ($needComma) {
                                $content .= '	';
                            }
                            $content .= $student_first_class_choice_utc;
                            $needComma = TRUE;
                        }
                        if ($student_second_class_choice_utc_checked == 'X') {
                            if ($needComma) {
                                $content .= '	';
                            }
                            $content .= $student_second_class_choice_utc;
                            $needComma = TRUE;
                        }
                        if ($student_third_class_choice_utc_checked == 'X') {
                            if ($needComma) {
                                $content .= '	';
                            }
                            $content .= $student_third_class_choice_utc;
                            $needComma = TRUE;
                        }
                        if ($student_catalog_options_checked == 'X') {
                            if ($needComma) {
                                $content .= '	';
                            }
                            $content .= $student_catalog_options;
                            $needComma = TRUE;
                        }
                        if ($student_flexible_checked == 'X') {
                            if ($needComma) {
                                $content .= '	';
                            }
                            $content .= $student_flexible;
                            $needComma = TRUE;
                        }
                        if ($student_date_created_checked == 'X') {
                            if ($needComma) {
                                $content .= '	';
                            }
                            $content .= $student_date_created;
                            $needComma = TRUE;
                        }
                        if ($student_date_updated_checked == 'X') {
                            if ($needComma) {
                                $content .= '	';
                            }
                            $content .= $student_date_updated;
                            $needComma = TRUE;
                        }
						$content	.= "\n";
					}				
				}
			}
			if ($output_type == 'table') {
				$content	.= "</table>";
			} else {
				$content	.= "</pre>";
			}
			$content		.= "<br /><br />$myCount records printed<br />";
		}
	} 
	$thisTime 		= date('Y-m-d H:i:s');
	$content 		.= "<br /><br /><p>Prepared at $thisTime</p>";
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
	if ($doDebug) {
		echo "<br />Testing to save report: $inp_report<br />\n";
	}
	if ($inp_report == 'Y') {
		if ($doDebug) {
			echo "Calling function to save the report as Student Report Generator<br />\n";
		}
		$storeDate		= date('dMy H:i');
		$storeResult	= storeReportData_v2("$jobname $storeDate",$content,$testMode,$doDebug);
//		$storeResult	= storeReportData_func("Student Report Generator",$content);
		if ($storeResult !== FALSE) {
			$content	.= "<br />Report stored in reports table as $storeResult[1]";
		} else {
			$content	.= "<br />Storing the report in the reports table failed";
			if ($doDebug) {
				echo "saving the report failed. Reason: $storeResult[1]<br />";
			}
		}
	}
	return $content;
}
add_shortcode ('student_report_generator', 'student_report_generator_func');
