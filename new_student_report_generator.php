function new_student_report_generator_func() {

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
	Modified extensively 27Mar25 by Roland 
		added ability to sequence the fields in the output
		added ability to download the csv file (if generated)
		Significantly reduced the number of lines of code
*/

	global $wpdb;

	$doDebug						= FALSE;
	$testMode						= FALSE;
	$context = CWA_Context::getInstance();
	if ($doDebug) {
		echo "Initialization Array:<br /><pre>";
		print_r($context->toArray());
		echo "</pre><br />\n";
	}
	$validUser = $context->validUser;
	$userName  = $context->userName;
	$siteURL	= $context->siteurl;
	$versionNumber = '3';
	$currentSemester	= $context->currentSemester;
	$nextSemester		= $context->nextSemester;
	$semesterTwo		= $context->semesterTwo;
	$semesterThree		= $context->semesterThree;
	$semesterFour		= $context->semesterFour;
	$futureSemester	= "(semester = '$nextSemester' or semester = '$semesterTwo' or semester = '$semesterThree' or semester = '$semesterFour')";
	$proximateSemester	= $context->proximateSemester;
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
	$theURL					 	= "$siteURL/cwa-new-student-report-generator/";
	$sequenceArray				= array();
	$tabType					= 'tab';
	$tabTypeArray				= array('tab'=>"\t",
										'comma'=>',',
										'semicolons'=>';');
	$tabValue					= "\t";

	$fieldArray = array('user_ID',
						'user_call_sign',
						'user_last_name',
						'user_first_name',
						'user_email',
						'user_ph_code',
						'user_phone',
						'user_city',
						'user_state',
						'user_zip_code',
						'user_country_code',
						'user_country',
						'user_whatsapp',
						'user_telegram',
						'user_signal',
						'user_messenger',
						'user_action_log',
						'user_timezone_id',
						'user_languages',
						'user_survey_score',
						'user_is_admin',
						'user_role',
						'user_date_created',
						'user_date_updated',
						'student_id',
						'student_call_sign',
						'student_time_zone',
						'student_timezone_offset',
						'student_youth',
						'student_age',
						'student_parent',
						'student_parent_email',
						'student_level',
						'student_waiting_list',
						'student_request_date',
						'student_semester',
						'student_notes',
						'student_welcome_date',
						'student_email_sent_date',
						'student_email_number',
						'student_response',
						'student_response_date',
						'student_abandoned',
						'student_status',
						'student_action_log',
						'student_pre_assigned_advisor',
						'student_selected_date',
						'student_no_catalog',
						'student_hold_override',
						'student_messaging',
						'student_assigned_advisor',
						'student_advisor_select_date',
						'student_advisor_class_timezone',
						'student_hold_reason_code',
						'student_class_priority',
						'student_assigned_advisor_class',
						'student_promotable',
						'student_excluded_advisor',
						'student_survey_completion_date',
						'student_available_class_days',
						'student_intervention_required',
						'student_copy_control',
						'student_first_class_choice',
						'student_second_class_choice',
						'student_third_class_choice',
						'student_first_class_choice_utc',
						'student_second_class_choice_utc',
						'student_third_class_choice_utc',
						'student_catalog_options',
						'student_flexible',
						'student_date_created',
						'student_date_updated');


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
    $student_intervention_required = '';
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
    $student_intervention_required_checked = '';
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
	$inp_report_name				= '';
	
	

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
            if ($str_key == 'student_intervention_required') {
                $student_intervention_required_checked = 'X';
                $reportConfig['student_intervention_required_checked'] = 'X';
                if ($doDebug) {
                    echo "student_intervention_required included in report<br />";
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

           if ($str_key == 'user_ID_sequence') {
                if ($str_value != '') {
    	            $user_ID_sequence = $str_value;
					$sequenceArray[$user_ID_sequence]	= 'user_ID';
				}
            }
            if ($str_key == 'user_call_sign_sequence') {
                if ($str_value != '') {
	                $user_call_sign_sequence = $str_value;
					$sequenceArray[$user_call_sign_sequence]	= 'user_call_sign';
				}
            }
            if ($str_key == 'user_first_name_sequence') {  
                if ($str_value != '') {
			        $user_first_name_sequence = $str_value;
					$sequenceArray[$user_first_name_sequence]	= 'user_first_name';
				}
            }
            if ($str_key == 'user_last_name_sequence') {
                if ($str_value != '') {
	                $user_last_name_sequence = $str_value;
					$sequenceArray[$user_last_name_sequence]	= 'user_last_name';
				}
            }
            if ($str_key == 'user_email_sequence') {
                if ($str_value != '') {
	                $user_email_sequence = $str_value;
					$sequenceArray[$user_email_sequence]	= 'user_email';
				}
            }
            if ($str_key == 'user_ph_code_sequence') {
                if ($str_value != '') {
	                $user_ph_code_sequence = $str_value;
					$sequenceArray[$user_ph_code_sequence]	= 'user_ph_code';
				}
            }
            if ($str_key == 'user_phone_sequence') {
                if ($str_value != '') {
	                $user_phone_sequence = $str_value;
					$sequenceArray[$user_phone_sequence]	= 'user_phone';
				}
            }
            if ($str_key == 'user_city_sequence') {
                if ($str_value != '') {
	                $user_city_sequence = $str_value;
					$sequenceArray[$user_city_sequence]	= 'user_city';
				}
            }
            if ($str_key == 'user_state_sequence') {
                if ($str_value != '') {
	                $user_state_sequence = 	$str_value;
					$sequenceArray[$user_state_sequence]	= 'user_state';
				}
            }
            if ($str_key == 'user_zip_code_sequence') {
                if ($str_value != '') {
	                $user_zip_code_sequence = $str_value;
					$sequenceArray[$user_zip_code_sequence]	= 'user_zip_code';
				}
            }
            if ($str_key == 'user_country_code_sequence') {
                if ($str_value != '') {
	                $user_country_code_sequence = $str_value;
					$sequenceArray[$user_country_code_sequence]	= 'user_country_code';
				}
            }
            if ($str_key == 'user_country_sequence') {
                if ($str_value != '') {
	                $user_country_sequence = $str_value;
					$sequenceArray[$user_country_sequence]	= 'user_country';
				}
            }
            if ($str_key == 'user_whatsapp_sequence') {
                if ($str_value != '') {
	                $user_whatsapp_sequence = $str_value;
					$sequenceArray[$user_whatsapp_sequence]	= 'user_whatsapp';
				}
            }
            if ($str_key == 'user_telegram_sequence') {
                if ($str_value != '') {
	                $user_telegram_sequence = $str_value;
					$sequenceArray[$user_telegram_sequence]	= 'user_telegram';
				}
            }
            if ($str_key == 'user_signal_sequence') {
                if ($str_value != '') {
	                $user_signal_sequence = $str_value;
					$sequenceArray[$user_signal_sequence]	= 'user_signal';
				}
            }
            if ($str_key == 'user_messenger_sequence') {
                if ($str_value != '') {
	                $user_messenger_sequence = $str_value;
					$sequenceArray[$user_messenger_sequence]	= 'user_messenger';
				}
            }
            if ($str_key == 'user_action_log_sequence') {
                if ($str_value != '') {
	                $user_action_log_sequence = $str_value;
					$sequenceArray[$user_action_log_sequence]	= 'user_action_log';
				}
            }
            if ($str_key == 'user_timezone_id_sequence') {
                if ($str_value != '') {
	                $user_timezone_id_sequence = $str_value;
					$sequenceArray[$user_timezone_id_sequence]	= 'user_timezone_id';
				}
            }
            if ($str_key == 'user_languages_sequence') {
                if ($str_value != '') {
	                $user_languages_sequence = $str_value;
					$sequenceArray[$user_languages_sequence]	= 'user_languages';
				}
            }
            if ($str_key == 'user_survey_score_sequence') {
                if ($str_value != '') {
	                $user_survey_score_sequence = $str_value;
					$sequenceArray[$user_survey_score_sequence]	= 'user_survey_score';
				}
            }
            if ($str_key == 'user_is_admin_sequence') {
                if ($str_value != '') {
	                $user_is_admin_sequence = $str_value;
					$sequenceArray[$user_is_admin_sequence]	= 'user_is_admin';
				}
            }
            if ($str_key == 'user_role_sequence') {
                if ($str_value != '') {
	                $user_role_sequence = $str_value;
					$sequenceArray[$user_role_sequence]	= 'user_role';
				}
            }
            if ($str_key == 'user_date_created_sequence') {
                if ($str_value != '') {
	                $user_date_created_sequence = $str_value;
					$sequenceArray[$user_date_created_sequence]	= 'user_date_created';
				}
            }
            if ($str_key == 'user_date_updated_sequence') {
                if ($str_value != '') {
	                $user_date_updated_sequence = $str_value;
					$sequenceArray[$user_date_updated_sequence]	= 'user_role';
				}
            }
            if ($str_key == 'student_id_sequence') {
                if ($str_value != '') {
	                $student_id_sequence = $str_value;
					$sequenceArray[$student_id_sequence]	= 'student_id';
				}
            }
            if ($str_key == 'student_call_sign_sequence') {
                if ($str_value != '') {
	                $student_call_sign_sequence = $str_value;
					$sequenceArray[$student_call_sign_sequence]	= 'student_call_sign';
				}
            }
            if ($str_key == 'student_time_zone_sequence') {
                if ($str_value != '') {
	                $student_time_zone_sequence = $str_value;
					$sequenceArray[$student_time_zone_sequence]	= 'student_time_zone';
				}
            }
            if ($str_key == 'student_timezone_offset_sequence') {
                if ($str_value != '') {
	                $student_timezone_offset_sequence = $str_value;
					$sequenceArray[$student_timezone_offset_sequence]	= 'student_timezone_offset';
				}
            }
            if ($str_key == 'student_youth_sequence') {
                if ($str_value != '') {
	                $student_youth_sequence = $str_value;
					$sequenceArray[$student_youth_sequence]	= 'student_youth';
				}
            }
            if ($str_key == 'student_age_sequence') {
                if ($str_value != '') {
	                $student_age_sequence = $str_value;
					$sequenceArray[$student_age_sequence]	= 'student_age';
				}
            }
            if ($str_key == 'student_parent_sequence') {
                if ($str_value != '') {
	                $student_parent_sequence = $str_value;
					$sequenceArray[$student_parent_sequence]	= 'student_parent';
				}
            }
            if ($str_key == 'student_parent_email_sequence') {
                if ($str_value != '') {
	                $student_parent_email_sequence = $str_value;
					$sequenceArray[$student_parent_email_sequence]	= 'student_parent_email';
				}
            }
            if ($str_key == 'student_level_sequence') {
                if ($str_value != '') {
	                $student_level_sequence = $str_value;
					$sequenceArray[$student_level_sequence]	= 'student_level';
				}
            }
            if ($str_key == 'student_waiting_list_sequence') {
                if ($str_value != '') {
	                $student_waiting_list_sequence = $str_value;
					$sequenceArray[$student_waiting_list_sequence]	= 'student_waiting_list';
				}
            }
            if ($str_key == 'student_request_date_sequence') {
                if ($str_value != '') {
	                $student_request_date_sequence = $str_value;
					$sequenceArray[$student_request_date_sequence]	= 'student_request_date';
				}
            }
            if ($str_key == 'student_semester_sequence') {
                if ($str_value != '') {
	                $student_semester_sequence = $str_value;
					$sequenceArray[$student_semester_sequence]	= 'student_semester';
				}
            }
            if ($str_key == 'student_notes_sequence') {
                if ($str_value != '') {
	                $student_notes_sequence = $str_value;
					$sequenceArray[$student_notes_sequence]	= 'student_notes';
				}
            }
            if ($str_key == 'student_welcome_date_sequence') {
                if ($str_value != '') {
	                $student_welcome_date_sequence = $str_value;
					$sequenceArray[$student_welcome_date_sequence]	= 'student_welcome_date';
				}
            }
            if ($str_key == 'student_email_sent_date_sequence') {
                if ($str_value != '') {
	                $student_email_sent_date_sequence = $str_value;
					$sequenceArray[$student_email_sent_date_sequence]	= 'student_email_sent_date';
				}
            }
            if ($str_key == 'student_email_number_sequence') {
                if ($str_value != '') {
	                $student_email_number_sequence = $str_value;
					$sequenceArray[$student_email_number_sequence]	= 'student_email_number';
				}
            }
            if ($str_key == 'student_response_sequence') {
                if ($str_value != '') {
	                $student_response_sequence = $str_value;
					$sequenceArray[$student_response_sequence]	= 'student_response';
				}
            }
            if ($str_key == 'student_response_date_sequence') {
                if ($str_value != '') {
	                $student_response_date_sequence = $str_value;
					$sequenceArray[$student_response_date_sequence]	= 'student_response_date';
				}
            }
            if ($str_key == 'student_abandoned_sequence') {
                if ($str_value != '') {
	                $student_abandoned_sequence = $str_value;
					$sequenceArray[$student_abandoned_sequence]	= 'student_abandoned';
				}
            }
            if ($str_key == 'student_status_sequence') {
                if ($str_value != '') {
	                $student_status_sequence = $str_value;
					$sequenceArray[$student_status_sequence]	= 'student_status';
				}
            }
            if ($str_key == 'student_action_log_sequence') {
                if ($str_value != '') {
	                $student_action_log_sequence = $str_value;
					$sequenceArray[$student_action_log_sequence]	= 'student_action_log';
				}
            }
            if ($str_key == 'student_pre_assigned_advisor_sequence') {
                if ($str_value != '') {
	                $student_pre_assigned_advisor_sequence = $str_value;
					$sequenceArray[$student_pre_assigned_advisor_sequence]	= 'student_pre_assigned_advisor';
				}
            }
            if ($str_key == 'student_selected_date_sequence') {
                if ($str_value != '') {
	                $student_selected_date_sequence = $str_value;
					$sequenceArray[$student_selected_date_sequence]	= 'student_selected_date';
				}
            }
            if ($str_key == 'student_no_catalog_sequence') {
                if ($str_value != '') {
	                $student_no_catalog_sequence = $str_value;
					$sequenceArray[$student_no_catalog_sequence]	= 'student_no_catalog';
				}
            }
            if ($str_key == 'student_hold_override_sequence') {
                if ($str_value != '') {
	                $student_hold_override_sequence = $str_value;
					$sequenceArray[$student_hold_override_sequence]	= 'student_hold_override';
				}
            }
            if ($str_key == 'student_messaging_sequence') {
                if ($str_value != '') {
	                $student_messaging_sequence = $str_value;
					$sequenceArray[$student_messaging_sequence]	= 'student_messaging';
				}
            }
            if ($str_key == 'student_assigned_advisor_sequence') {
                if ($str_value != '') {
	                $student_assigned_advisor_sequence = $str_value;
					$sequenceArray[$student_assigned_advisor_sequence]	= 'student_assigned_advisor';
				}
            }
            if ($str_key == 'student_advisor_select_date_sequence') {
                if ($str_value != '') {
	                $student_advisor_select_date_sequence = $str_value;
					$sequenceArray[$student_advisor_select_date_sequence]	= 'student_advisor_select_date';
				}
            }
            if ($str_key == 'student_advisor_class_timezone_sequence') {
                if ($str_value != '') {
	                $student_advisor_class_timezone_sequence = $str_value;
					$sequenceArray[$student_advisor_class_timezone_sequence]	= 'student_advisor_class_timezone';
				}
            }
            if ($str_key == 'student_hold_reason_code_sequence') {
                if ($str_value != '') {
	                $student_hold_reason_code_sequence = $str_value;
					$sequenceArray[$student_hold_reason_code_sequence]	= 'student_hold_reason_code';
				}
            }
            if ($str_key == 'student_class_priority_sequence') {
                if ($str_value != '') {
	                $student_class_priority_sequence = $str_value;
					$sequenceArray[$student_class_priority_sequence]	= 'student_class_priority';
				}
            }
            if ($str_key == 'student_assigned_advisor_class_sequence') {
                if ($str_value != '') {
	                $student_assigned_advisor_class_sequence = $str_value;
					$sequenceArray[$student_assigned_advisor_class_sequence]	= 'student_assigned_advisor_class';
				}
            }
            if ($str_key == 'student_promotable_sequence') {
                if ($str_value != '') {
	                $student_promotable_sequence = $str_value;
					$sequenceArray[$student_promotable_sequence]	= 'student_promotable';
				}
            }
            if ($str_key == 'student_excluded_advisor_sequence') {
                if ($str_value != '') {
	                $student_excluded_advisor_sequence = $str_value;
					$sequenceArray[$student_excluded_advisor_sequence]	= 'student_excluded_advisor';
				}
            }
            if ($str_key == 'student_survey_completion_date_sequence') {
                if ($str_value != '') {
	                $student_survey_completion_date_sequence = $str_value;
					$sequenceArray[$student_survey_completion_date_sequence]	= 'student_survey_completion_date';
				}
            }
            if ($str_key == 'student_available_class_days_sequence') {
                if ($str_value != '') {
	                $student_available_class_days_sequence = $str_value;
					$sequenceArray[$student_available_class_days_sequence]	= 'student_available_class_days';
				}
            }
            if ($str_key == 'student_intervention_required_sequence') {
                if ($str_value != '') {
	                $student_intervention_required_sequence = $str_value;
					$sequenceArray[$student_intervention_required_sequence]	= 'student_intervention_required';
				}
            }
            if ($str_key == 'student_copy_control_sequence') {
                if ($str_value != '') {
	                $student_copy_control_sequence = $str_value;
					$sequenceArray[$student_copy_control_sequence]	= 'student_copy_control';
				}
            }
            if ($str_key == 'student_first_class_choice_sequence') {
                if ($str_value != '') {
	                $student_first_class_choice_sequence = $str_value;
					$sequenceArray[$student_first_class_choice_sequence]	= 'student_first_class_choice';
				}
            }
            if ($str_key == 'student_second_class_choice_sequence') {
                if ($str_value != '') {
	                $student_second_class_choice_sequence = $str_value;
					$sequenceArray[$student_second_class_choice_sequence]	= 'student_second_class_choice';
				}
            }
            if ($str_key == 'student_third_class_choice_sequence') {
                if ($str_value != '') {
	                $student_third_class_choice_sequence = $str_value;
					$sequenceArray[$student_third_class_choice_sequence]	= 'student_third_class_choice';
				}
			}
            if ($str_key == 'student_first_class_choice_utc_sequence') {
                if ($str_value != '') {
	                $student_first_class_choice_utc_sequence = $str_value;
					$sequenceArray[$student_first_class_choice_utc_sequence]	= 'student_first_class_choice_utc';
				}
            }
            if ($str_key == 'student_second_class_choice_utc_sequence') {
                if ($str_value != '') {
	                $student_second_class_choice_utc_sequence = $str_value;
					$sequenceArray[$student_second_class_choice_utc_sequence]	= 'student_second_class_choice_utc';
				}
            }
            if ($str_key == 'student_third_class_choice_utc_sequence') {
                if ($str_value != '') {
	                $student_third_class_choice_utc_sequence = $str_value;
					$sequenceArray[$student_third_class_choice_utc_sequence]	= 'student_third_class_choice_utc';
				}
            }
            if ($str_key == 'student_catalog_options_sequence') {
                if ($str_value != '') {
	                $student_catalog_options_sequence = $str_value;
					$sequenceArray[$student_catalog_options_sequence]	= 'student_catalog_options';
				}
            }
            if ($str_key == 'student_flexible_sequence') {
                if ($str_value != '') {
	                $student_flexible_sequence = $str_value;
					$sequenceArray[$student_flexible_sequence]	= 'student_flexible';
				}
            }
            if ($str_key == 'student_date_created_sequence') {
                if ($str_value != '') {
	                $student_date_created_sequence = $str_value;
					$sequenceArray[$student_date_created_sequence]	= 'student_date_created';
				}
            }
            if ($str_key == 'student_date_updated_sequence') {
                if ($str_value != '') {
	                $student_date_updated_sequence = $str_value;
					$sequenceArray[$student_date_updated_sequence]	= 'student_date_updated';
				}
            }




			if($str_key					== "where") {
				$where					 = $str_value;
// echo "where b4 filter: $where<br />";
//				$where					 = filter_var($where,FILTER_UNSAFE_RAW);
//				$where					= str_replace("&#39;","'",$where);
				$where					= stripslashes($where);
// echo "where after filter: $where<br />";
			}
			if ($str_key 				== "configWhere") {
				$where					= base64_decode($str_value);
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
			if($str_key					== "tabType") {
				$tabType					 = $str_value;
				$tabType				 = filter_var($tabType,FILTER_UNSAFE_RAW);
			}
		}
	}
	
	
	
	$content = "";	

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
							<tr><th style='width:250px;'>Report Field</th><th style='width:150px;'>Sequence</th><th>Table Name</th></tr>
						   <tr><td><input type='checkbox' class='formInputButton' id='user_ID' 
									name='user_ID' value='user_ID'>
									<label for 'user_ID'>user_ID</label></td>
								<td><input type='text' class='formInputText' 
									name='user_ID_sequence' size='5' maxlength='5'></td>
								<td>user_ID</td></tr>
							<tr><td><input type='checkbox' class='formInputButton' id='user_call_sign' 
									name='user_call_sign' value='user_call_sign'>
									<label for 'user_call_sign'>user_call_sign</label></td>
								<td><input type='text' class='formInputText' 
									name='user_call_sign_sequence' size='5' maxlength='5'></td>
								<td>user_call_sign</td></tr>
							<tr><td><input type='checkbox' class='formInputButton' id='user_last_name' 
									name='user_last_name' value='user_last_name'>
									<label for 'user_last_name'>user_last_name</label></td>
								<td><input type='text' class='formInputText' 
									name='user_last_name_sequence' size='5' maxlength='5'></td>
								<td>user_last_name</td></tr>
							<tr><td><input type='checkbox' class='formInputButton' id='user_first_name' 
									name='user_first_name' value='user_first_name'>
									<label for 'user_first_name'>user_first_name</label></td>
								<td><input type='text' class='formInputText' 
									name='user_first_name_sequence' size='5' maxlength='5'></td>
								<td>user_first_name</td></tr>
							<tr><td><input type='checkbox' class='formInputButton' id='user_email' 
									name='user_email' value='user_email'>
									<label for 'user_email'>user_email</label></td>
								<td><input type='text' class='formInputText' 
									name='user_email_sequence' size='5' maxlength='5'></td>
								<td>user_email</td></tr>
							<tr><td><input type='checkbox' class='formInputButton' id='user_ph_code' 
									name='user_ph_code' value='user_ph_code'>
									<label for 'user_ph_code'>user_ph_code</label></td>
								<td><input type='text' class='formInputText' 
									name='user_ph_code_sequence' size='5' maxlength='5'></td>
								<td>user_ph_code</td></tr>
							<tr><td><input type='checkbox' class='formInputButton' id='user_phone' 
									name='user_phone' value='user_phone'>
									<label for 'user_phone'>user_phone</label></td>
								<td><input type='text' class='formInputText' 
									name='user_phone_sequence' size='5' maxlength='5'></td>
								<td>user_phone</td></tr>
							<tr><td><input type='checkbox' class='formInputButton' id='user_city' 
									name='user_city' value='user_city'>
									<label for 'user_city'>user_city</label></td>
								<td><input type='text' class='formInputText' 
									name='user_city_sequence' size='5' maxlength='5'></td>
								<td>user_city</td></tr>
							<tr><td><input type='checkbox' class='formInputButton' id='user_state' 
									name='user_state' value='user_state'>
									<label for 'user_state'>user_state</label></td>
								<td><input type='text' class='formInputText' 
									name='user_state_sequence' size='5' maxlength='5'></td>
								<td>user_state</td></tr>
							<tr><td><input type='checkbox' class='formInputButton' id='user_zip_code' 
									name='user_zip_code' value='user_zip_code'>
									<label for 'user_zip_code'>user_zip_code</label></td>
								<td><input type='text' class='formInputText' 
									name='user_zip_code_sequence' size='5' maxlength='5'></td>
								<td>user_zip_code</td></tr>
							<tr><td><input type='checkbox' class='formInputButton' id='user_country_code' 
									name='user_country_code' value='user_country_code'>
									<label for 'user_country_code'>user_country_code</label></td>
								<td><input type='text' class='formInputText' 
									name='user_country_code_sequence' size='5' maxlength='5'></td>
								<td>user_country_code</td></tr>
							<tr><td><input type='checkbox' class='formInputButton' id='user_country' 
									name='user_country' value='user_country'>
									<label for 'user_country'>user_country</label></td>
								<td><input type='text' class='formInputText' 
									name='user_country_sequence' size='5' maxlength='5'></td>
								<td>user_country</td></tr>
							<tr><td><input type='checkbox' class='formInputButton' id='user_whatsapp' 
									name='user_whatsapp' value='user_whatsapp'>
									<label for 'user_whatsapp'>user_whatsapp</label></td>
								<td><input type='text' class='formInputText' 
									name='user_whatsapp_sequence' size='5' maxlength='5'></td>
								<td>user_whatsapp</td></tr>
							<tr><td><input type='checkbox' class='formInputButton' id='user_telegram' 
									name='user_telegram' value='user_telegram'>
									<label for 'user_telegram'>user_telegram</label></td>
								<td><input type='text' class='formInputText' 
									name='user_telegram_sequence' size='5' maxlength='5'></td>
								<td>user_telegram</td></tr>
							<tr><td><input type='checkbox' class='formInputButton' id='user_signal' 
									name='user_signal' value='user_signal'>
									<label for 'user_signal'>user_signal</label></td>
								<td><input type='text' class='formInputText' 
									name='user_signal_sequence' size='5' maxlength='5'></td>
								<td>user_signal</td></tr>
							<tr><td><input type='checkbox' class='formInputButton' id='user_messenger' 
									name='user_messenger' value='user_messenger'>
									<label for 'user_messenger'>user_messenger</label></td>
								<td><input type='text' class='formInputText' 
									name='user_messenger_sequence' size='5' maxlength='5'></td>
								<td>user_messenger</td></tr>
							<tr><td><input type='checkbox' class='formInputButton' id='user_action_log' 
									name='user_action_log' value='user_action_log'>
									<label for 'user_action_log'>user_action_log</label></td>
								<td><input type='text' class='formInputText' 
									name='user_action_log_sequence' size='5' maxlength='5'></td>
								<td>user_action_log</td></tr>
							<tr><td><input type='checkbox' class='formInputButton' id='user_timezone_id' 
									name='user_timezone_id' value='user_timezone_id'>
									<label for 'user_timezone_id'>user_timezone_id</label></td>
								<td><input type='text' class='formInputText' 
									name='user_timezone_id_sequence' size='5' maxlength='5'></td>
								<td>user_timezone_id</td></tr>
							<tr><td><input type='checkbox' class='formInputButton' id='user_languages' 
									name='user_languages' value='user_languages'>
									<label for 'user_languages'>user_languages</label></td>
								<td><input type='text' class='formInputText' 
									name='user_languages_sequence' size='5' maxlength='5'></td>
								<td>user_languages</td></tr>
							<tr><td><input type='checkbox' class='formInputButton' id='user_survey_score' 
									name='user_survey_score' value='user_survey_score'>
									<label for 'user_survey_score'>user_survey_score</label></td>
								<td><input type='text' class='formInputText' 
									name='user_survey_score_sequence' size='5' maxlength='5'></td>
								<td>user_survey_score</td></tr>
							<tr><td><input type='checkbox' class='formInputButton' id='user_is_admin' 
									name='user_is_admin' value='user_is_admin'>
									<label for 'user_is_admin'>user_is_admin</label></td>
								<td><input type='text' class='formInputText' 
									name='user_is_admin_sequence' size='5' maxlength='5'></td>
								<td>user_is_admin</td></tr>
							<tr><td><input type='checkbox' class='formInputButton' id='user_role' 
									name='user_role' value='user_role'>
									<label for 'user_role'>user_role</label></td>
								<td><input type='text' class='formInputText' 
									name='user_role_sequence' size='5' maxlength='5'></td>
								<td>user_role</td></tr>
							<tr><td><input type='checkbox' class='formInputButton' id='user_date_created' 
									name='user_date_created' value='user_date_created'>
									<label for 'user_date_created'>user_date_created</label></td>
								<td><input type='text' class='formInputText' 
									name='user_date_created_sequence' size='5' maxlength='5'></td>
								<td>user_date_created</td></tr>
							<tr><td><input type='checkbox' class='formInputButton' id='user_date_updated' 
									name='user_date_updated' value='user_date_updated'>
									<label for 'user_date_updated'>user_date_updated</label></td>
								<td><input type='text' class='formInputText' 
									name='user_date_updated_sequence' size='5' maxlength='5'></td>
								<td>user_date_updated</td></tr>
	
							<tr><th colspan='2'>Student Fields</th></tr>
							<tr><th>Report Field</th><th>Sequence</th><th>Table Name</th></tr>
							<tr><td><input type='checkbox' class='formInputButton' id='student_id' 
									name='student_id' value='student_id'>
									<label for 'student_id'>student_id</label></td>
								<td><input type='text' class='formInputText' 
									name='student_id_sequence' size='5' maxlength='5'></td>
								<td>student_id</td></tr>
							<tr><td><input type='checkbox' class='formInputButton' id='student_timezone_offset' 
									name='student_timezone_offset' value='student_timezone_offset'>
									<label for 'student_timezone_offset'>student_timezone_offset</label></td>
								<td><input type='text' class='formInputText' 
									name='student_timezone_offset_sequence' size='5' maxlength='5'></td>
								<td>student_timezone_offset</td></tr>
							<tr><td><input type='checkbox' class='formInputButton' id='student_youth' 
									name='student_youth' value='student_youth'>
									<label for 'student_youth'>student_youth</label></td>
 								<td><input type='text' class='formInputText' 
									name='student_youth_sequence' size='5' maxlength='5'></td>
								<td>student_youth</td></tr>
							<tr><td><input type='checkbox' class='formInputButton' id='student_age' 
									name='student_age' value='student_age'>
									<label for 'student_age'>student_age</label></td>
								<td><input type='text' class='formInputText' 
									name='student_age_sequence' size='5' maxlength='5'></td>
								<td>student_age</td></tr>
							<tr><td><input type='checkbox' class='formInputButton' id='student_parent' 
									name='student_parent' value='student_parent'>
									<label for 'student_parent'>student_parent</label></td>
								<td><input type='text' class='formInputText' 
									name='student_parent_sequence' size='5' maxlength='5'></td>
								<td>student_parent</td></tr>
							<tr><td><input type='checkbox' class='formInputButton' id='student_parent_email' 
									name='student_parent_email' value='student_parent_email'>
									<label for 'student_parent_email'>student_parent_email</label></td>
								<td><input type='text' class='formInputText' 
									name='student_parent_email_sequence' size='5' maxlength='5'></td>
								<td>student_parent_email</td></tr>
							<tr><td><input type='checkbox' class='formInputButton' id='student_level' 
									name='student_level' value='student_level'>
									<label for 'student_level'>student_level</label></td>
								<td><input type='text' class='formInputText' 
									name='student_level_sequence' size='5' maxlength='5'></td>
								<td>student_level</td></tr>
							<tr><td><input type='checkbox' class='formInputButton' id='student_waiting_list' 
									name='student_waiting_list' value='student_waiting_list'>
									<label for 'student_waiting_list'>student_waiting_list</label></td>
								<td><input type='text' class='formInputText' 
									name='student_waiting_list_sequence' size='5' maxlength='5'></td>
								<td>student_waiting_list</td></tr>
							<tr><td><input type='checkbox' class='formInputButton' id='student_request_date' 
									name='student_request_date' value='student_request_date'>
									<label for 'student_request_date'>student_request_date</label></td>
								<td><input type='text' class='formInputText' 
									name='student_request_date_sequence' size='5' maxlength='5'></td>
								<td>student_request_date</td></tr>
							<tr><td><input type='checkbox' class='formInputButton' id='student_semester' 
									name='student_semester' value='student_semester'>
									<label for 'student_semester'>student_semester</label></td>
								<td><input type='text' class='formInputText' 
									name='student_semester_sequence' size='5' maxlength='5'></td>
								<td>student_semester</td></tr>
							<tr><td><input type='checkbox' class='formInputButton' id='student_notes' 
									name='student_notes' value='student_notes'>
									<label for 'student_notes'>student_notes</label></td>
								<td><input type='text' class='formInputText' 
									name='student_notes_sequence' size='5' maxlength='5'></td>
								<td>student_notes</td></tr>
							<tr><td><input type='checkbox' class='formInputButton' id='student_welcome_date' 
									name='student_welcome_date' value='student_welcome_date'>
									<label for 'student_welcome_date'>student_welcome_date</label></td>
								<td><input type='text' class='formInputText' 
									name='student_welcome_date_sequence' size='5' maxlength='5'></td>
								<td>student_welcome_date</td></tr>
							<tr><td><input type='checkbox' class='formInputButton' id='student_email_sent_date' 
									name='student_email_sent_date' value='student_email_sent_date'>
									<label for 'student_email_sent_date'>student_email_sent_date</label></td>
								<td><input type='text' class='formInputText' 
									name='student_email_sent_date_sequence' size='5' maxlength='5'></td>
								<td>student_email_sent_date</td></tr>
							<tr><td><input type='checkbox' class='formInputButton' id='student_email_number' 
									name='student_email_number' value='student_email_number'>
									<label for 'student_email_number'>student_email_number</label></td>
								<td><input type='text' class='formInputText' 
									name='student_email_number_sequence' size='5' maxlength='5'></td>
								<td>student_email_number</td></tr>
							<tr><td><input type='checkbox' class='formInputButton' id='student_response' 
									name='student_response' value='student_response'>
									<label for 'student_response'>student_response</label></td>
								<td><input type='text' class='formInputText' 
									name='student_response_sequence' size='5' maxlength='5'></td>
								<td>student_response</td></tr>
							<tr><td><input type='checkbox' class='formInputButton' id='student_response_date' 
									name='student_response_date' value='student_response_date'>
									<label for 'student_response_date'>student_response_date</label></td>
								<td><input type='text' class='formInputText' 
									name='student_response_date_sequence' size='5' maxlength='5'></td>
								<td>student_response_date</td></tr>
							<tr><td><input type='checkbox' class='formInputButton' id='student_abandoned' 
									name='student_abandoned' value='student_abandoned'>
									<label for 'student_abandoned'>student_abandoned</label></td>
								<td><input type='text' class='formInputText' 
									name='student_abandoned_sequence' size='5' maxlength='5'></td>
								<td>student_abandoned</td></tr>
							<tr><td><input type='checkbox' class='formInputButton' id='student_status' 
									name='student_status' value='student_status'>
									<label for 'student_status'>student_status</label></td>
								<td><input type='text' class='formInputText' 
									name='student_status_sequence' size='5' maxlength='5'></td>
								<td>student_status</td></tr>
							<tr><td><input type='checkbox' class='formInputButton' id='student_action_log' 
									name='student_action_log' value='student_action_log'>
									<label for 'student_action_log'>student_action_log</label></td>
								<td><input type='text' class='formInputText' 
									name='student_action_log_sequence' size='5' maxlength='5'></td>
								<td>student_action_log</td></tr>
							<tr><td><input type='checkbox' class='formInputButton' id='student_pre_assigned_advisor' 
									name='student_pre_assigned_advisor' value='student_pre_assigned_advisor'>
									<label for 'student_pre_assigned_advisor'>student_pre_assigned_advisor</label></td>
								<td><input type='text' class='formInputText' 
									name='student_pre_assigned_advisor_sequence' size='5' maxlength='5'></td>
								<td>student_pre_assigned_advisor</td></tr>
							<tr><td><input type='checkbox' class='formInputButton' id='student_selected_date' 
									name='student_selected_date' value='student_selected_date'>
									<label for 'student_selected_date'>student_selected_date</label></td>
								<td><input type='text' class='formInputText' 
									name='student_selected_date_sequence' size='5' maxlength='5'></td>
								<td>student_selected_date</td></tr>
							<tr><td><input type='checkbox' class='formInputButton' id='student_no_catalog' 
									name='student_no_catalog' value='student_no_catalog'>
									<label for 'student_no_catalog'>student_no_catalog</label></td>
								<td><input type='text' class='formInputText' 
									name='student_no_catalog_sequence' size='5' maxlength='5'></td>
								<td>student_no_catalog</td></tr>
							<tr><td><input type='checkbox' class='formInputButton' id='student_hold_override' 
									name='student_hold_override' value='student_hold_override'>
									<label for 'student_hold_override'>student_hold_override</label></td>
								<td><input type='text' class='formInputText' 
									name='student_hold_override_sequence' size='5' maxlength='5'></td>
								<td>student_hold_override</td></tr>
							<tr><td><input type='checkbox' class='formInputButton' id='student_messaging' 
									name='student_messaging' value='student_messaging'>
									<label for 'student_messaging'>student_messaging</label></td>
								<td><input type='text' class='formInputText' 
									name='student_messaging_sequence' size='5' maxlength='5'></td>
								<td>student_messaging</td></tr>
							<tr><td><input type='checkbox' class='formInputButton' id='student_assigned_advisor' 
									name='student_assigned_advisor' value='student_assigned_advisor'>
									<label for 'student_assigned_advisor'>student_assigned_advisor</label></td>
								<td><input type='text' class='formInputText' 
									name='student_assigned_advisor_sequence' size='5' maxlength='5'></td>
								<td>student_assigned_advisor</td></tr>
							<tr><td><input type='checkbox' class='formInputButton' id='student_advisor_select_date' 
									name='student_advisor_select_date' value='student_advisor_select_date'>
									<label for 'student_advisor_select_date'>student_advisor_select_date</label></td>
								<td><input type='text' class='formInputText' 
									name='student_select_date_sequence' size='5' maxlength='5'></td>
								<td>student_advisor_select_date</td></tr>
							<tr><td><input type='checkbox' class='formInputButton' id='student_advisor_class_timezone' 
									name='student_advisor_class_timezone' value='student_advisor_class_timezone'>
									<label for 'student_advisor_class_timezone'>student_advisor_class_timezone</label></td>
								<td><input type='text' class='formInputText' 
									name='student_advisor_class_timezone_sequence' size='5' maxlength='5'></td>
								<td>student_advisor_class_timezone</td></tr>
							<tr><td><input type='checkbox' class='formInputButton' id='student_hold_reason_code' 
									name='student_hold_reason_code' value='student_hold_reason_code'>
									<label for 'student_hold_reason_code'>student_hold_reason_code</label></td>
								<td><input type='text' class='formInputText' 
									name='student_hold_reason_code_sequence' size='5' maxlength='5'></td>
								<td>student_hold_reason_code</td></tr>
							<tr><td><input type='checkbox' class='formInputButton' id='student_class_priority' 
									name='student_class_priority' value='student_class_priority'>
									<label for 'student_class_priority'>student_class_priority</label></td>
								<td><input type='text' class='formInputText' 
									name='student_class_priority_sequence' size='5' maxlength='5'></td>
								<td>student_class_priority</td></tr>
							<tr><td><input type='checkbox' class='formInputButton' id='student_assigned_advisor_class' 
									name='student_assigned_advisor_class' value='student_assigned_advisor_class'>
									<label for 'student_assigned_advisor_class'>student_assigned_advisor_class</label></td>
								<td><input type='text' class='formInputText' 
									name='student_assigned_advisor_class_sequence' size='5' maxlength='5'></td>
								<td>student_assigned_advisor_class</td></tr>
							<tr><td><input type='checkbox' class='formInputButton' id='student_promotable' 
									name='student_promotable' value='student_promotable'>
									<label for 'student_promotable'>student_promotable</label></td>
								<td><input type='text' class='formInputText' 
									name='student_promotable_sequence' size='5' maxlength='5'></td>
								<td>student_promotable</td></tr>
							<tr><td><input type='checkbox' class='formInputButton' id='student_excluded_advisor' 
									name='student_excluded_advisor' value='student_excluded_advisor'>
									<label for 'student_excluded_advisor'>student_excluded_advisor</label></td>
								<td><input type='text' class='formInputText' 
									name='student_excluded_advisor_sequence' size='5' maxlength='5'></td>
								<td>student_excluded_advisor</td></tr>
							<tr><td><input type='checkbox' class='formInputButton' id='student_survey_completion_date' 
									name='student_survey_completion_date' value='student_survey_completion_date'>
									<label for 'student_survey_completion_date'>student_survey_completion_date</label></td>
								<td><input type='text' class='formInputText' 
									name='student_survey_completion_date_sequence' size='5' maxlength='5'></td>
								<td>student_survey_completion_date</td></tr>
							<tr><td><input type='checkbox' class='formInputButton' id='student_available_class_days' 
									name='student_available_class_days' value='student_available_class_days'>
									<label for 'student_available_class_days'>student_available_class_days</label></td>
								<td><input type='text' class='formInputText' 
									name='student_available_class_days_sequence' size='5' maxlength='5'></td>
								<td>student_available_class_days</td></tr>
							<tr><td><input type='checkbox' class='formInputButton' id='student_intervention_required' 
									name='student_intervention_required' value='student_intervention_required'>
									<label for 'student_intervention_required'>student_intervention_required</label></td>
								<td><input type='text' class='formInputText' 
									name='student_intervention_required_sequence' size='5' maxlength='5'></td>
								<td>istudent_intervention_required</td></tr>
							<tr><td><input type='checkbox' class='formInputButton' id='student_copy_control' 
									name='student_copy_control' value='student_copy_control'>
									<label for 'student_copy_control'>student_copy_control</label></td>
								<td><input type='text' class='formInputText' 
									name='student_copy_control_sequence' size='5' maxlength='5'></td>
								<td>student_copy_control</td></tr>
							<tr><td><input type='checkbox' class='formInputButton' id='student_first_class_choice' 
									name='student_first_class_choice' value='student_first_class_choice'>
									<label for 'student_first_class_choice'>student_first_class_choice</label></td>
								<td><input type='text' class='formInputText' 
									name='student_first_class_choice_sequence' size='5' maxlength='5'></td>
								<td>student_first_class_choice</td></tr>
							<tr><td><input type='checkbox' class='formInputButton' id='student_second_class_choice' 
									name='student_second_class_choice' value='student_second_class_choice'>
									<label for 'student_second_class_choice'>student_second_class_choice</label></td>
								<td><input type='text' class='formInputText' 
									name='student_second_class_choice_sequence' size='5' maxlength='5'></td>
								<td>student_second_class_choice</td></tr>
							<tr><td><input type='checkbox' class='formInputButton' id='student_third_class_choice' 
									name='student_third_class_choice' value='student_third_class_choice'>
									<label for 'student_third_class_choice'>student_third_class_choice</label></td>
								<td><input type='text' class='formInputText' 
									name='student_third_class_choice_sequence' size='5' maxlength='5'></td>
								<td>student_third_class_choice</td></tr>
							<tr><td><input type='checkbox' class='formInputButton' id='student_first_class_choice_utc' 
									name='student_first_class_choice_utc' value='student_first_class_choice_utc'>
									<label for 'student_first_class_choice_utc'>student_first_class_choice_utc</label></td>
								<td><input type='text' class='formInputText' 
									name='student_first_class_choice_utc_sequence' size='5' maxlength='5'></td>
								<td>student_first_class_choice_utc</td></tr>
							<tr><td><input type='checkbox' class='formInputButton' id='student_second_class_choice_utc' 
									name='student_second_class_choice_utc' value='student_second_class_choice_utc'>
									<label for 'student_second_class_choice_utc'>student_second_class_choice_utc</label></td>
								<td><input type='text' class='formInputText' 
									name='student_second_class_choice_utc_sequence' size='5' maxlength='5'></td>
								<td>student_second_class_choice_utc</td></tr>
							<tr><td><input type='checkbox' class='formInputButton' id='student_third_class_choice_utc' 
									name='student_third_class_choice_utc' value='student_third_class_choice_utc'>
									<label for 'student_third_class_choice_utc'>student_third_class_choice_utc</label></td>
								<td><input type='text' class='formInputText' 
									name='student_third_class_choice_utc_sequence' size='5' maxlength='5'></td>
								<td>student_third_class_choice_utc</td></tr>
							<tr><td><input type='checkbox' class='formInputButton' id='student_catalog_options' 
									name='student_catalog_options' value='student_catalog_options'>
									<label for 'student_catalog_options'>student_catalog_options</label></td>
								<td><input type='text' class='formInputText' 
									name='student_catalog_options_sequence' size='5' maxlength='5'></td>
								<td>student_catalog_options</td></tr>
							<tr><td><input type='checkbox' class='formInputButton' id='student_flexible' 
									name='student_flexible' value='student_flexible'>
									<label for 'student_flexible'>student_flexible</label></td>
								<td><input type='text' class='formInputText' 
									name='student_flexible_sequence' size='5' maxlength='5'></td>
								<td>student_flexible</td></tr>
							<tr><td><input type='checkbox' class='formInputButton' id='student_date_created' 
									name='student_date_created' value='student_date_created'>
									<label for 'student_date_created'>student_date_created</label></td>
								<td><input type='text' class='formInputText' 
									name='student_date_created_sequence' size='5' maxlength='5'></td>
								<td>student_date_created</td></tr>
							<tr><td><input type='checkbox' class='formInputButton' id='student_date_updated' 
									name='student_date_updated' value='student_date_updated'>
									<label for 'student_date_updated'>student_date_updated</label></td>
								<td><input type='text' class='formInputText' 
									name='student_date_updated_sequence' size='5' maxlength='5'></td>
								<td>student_date_updated</td></tr>
							</table></p>
	
							<p>Select Output Format<br />
							<input type='radio' id='table' name='output_type' value='table' $table_checked> Table Report<br />
							<input type='radio' id='comma' name='output_type' value='comma' $comma_checked> Tab Delimited Report<br />
							<div style='margin-left:20px;'><input type='radio' class='formInputButton' name='tabType' value='tab' checked>Delimited by tabs<br />
															<input type='radio' class='formInputButton' name='tabType' value='comma'>Delimited by commas<br />
															<input type='radio' class='formInputButton' name='tabType' value='semicolons'>Delimited by semicolons</div></p>
							
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
							<br /><br /><p><b>Sequence: </b>If you want the output fields to be in a specific order, 
							enter the sequence numbers for each field to be displayed. If any fields are sequenced, all of the 
							selected fields need to have a sequence number, otherwise the order the fields will be displayed is 
							indeterminate. Recommend numbering the sequence numbers by 10's to allow for changes</p>
							<br /><p>Examples of the 'where' clause:<br />
							To include students for a particular semester: <em>student_semester='2021 Apr/May'</em><br /><br />
							To include students assigned to a specific advisor: <em>student_assigned_advisor='WR7Q'</em><br /><br />
							To include students for a particular semester but exclude students with a response of 'R': 
							<em>student_semester='2021 Apr/May' and student_response != 'R'</em><br /><br />
							Include all students with the phrase 'not promotable' in the student action log: <em>student_action_log 
							like '%not promotable%'</em><br /><br />
							To include students with a response of Y and status of S or Y or V: <em>student_response = 'Y' and (student_status = 'S' 
							or student_status = 'Y' or student_status = 'V')</em><br /><br />
							To include all future semesters, use <em>futureSemester</em>. For example: level = 'Fundamental' and futureSemester <br /><br />
							To search current or upcoming semester use <em>proximateSemester</em> <br />
							</p>";

///// Pass 2 -- do the work


	} elseif ("2" == $strPass) {
		if ($doDebug) {
			echo "<br />at pass 2<br />";
		}		

		if ($doDebug) {
			echo "reportConfig array:<br /><pre>";
			print_r($reportConfig);
			echo "</pre><br />";
			
			echo "sequenceArray:<br /><pre>";
			print_r($sequenceArray);
			echo "</pre><br />";
		}


	// set logical on how the report is to be run
	$doBySequence		= FALSE;
	if (count($sequenceArray) > 0) {
		$doBySequence	= TRUE;
		if ($doDebug) {
			echo "doing the report by sequence number<br />";
		}
	} else {
		if ($doDebug) {
			echo "doing the report by field<br />";
		}
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
        $nameConversionArray['student_pre_assigned_advisor'] = 'student<br />pre_assigned_advisor';
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
        $nameConversionArray['student_intervention_required'] = 'istudent<br />intervention_required';
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

		if ($inp_config == 'Y') {		// saving the report configuration
			if ($doDebug) {
				echo "saving the report configuration<br />";
			}
			if ($inp_report_name != '') {
//				$whereStr								= htmlentities($where,ENT_QUOTES);
				$reportConfiguration['where']			= $where;
				$reportConfiguration['orderby']			= $orderby;
				$reportConfiguration['output_type']		= $output_type;
				$reportConfiguration['mode_type']		= $mode_type;
				$reportConfiguration['tabType']			= $tabType;
				if ($doDebug) {
					$reportConfiguration['inp_debug']	= 'Y';
				} else {
					$reportConfiguration['inp_debug']	= 'N';
				}
				$reportConfiguration['inp_config']		= 'N';
				$checkedArray							= json_encode($reportConfig);
				$reportConfiguration['reportConfig']	= $checkedArray;
				$rg_sequence							= json_encode($sequenceArray);
				$reportConfiguration['sequenceArray']	= $rg_sequence;
				$myStr									= date('Y-m-d H:i:s');
				$rg_config								= addslashes(json_encode($reportConfiguration));
				
				// if the report name already exists, update else insert
				$reportNameCount		= $wpdb->get_var("select count(rg_report_name) from wpw1_cwa_report_configurations where rg_report_name = '$inp_report_name'");
				if ($reportNameCount == 0) {
					if ($doDebug) {
						echo "report name $inp_report_name is new<br />\n";
					}
					$insertResult		= $wpdb->insert('wpw1_cwa_report_configurations',
													array('rg_report_name'=>$inp_report_name,
														  'rg_table'=>'student',
														  'rg_config'=>$rg_config,
														  'date_written'=>$myStr),
													array('%s','%s','%s','%s'));
					if ($insertResult === FALSE) {
						handleWPDBError($jobname,$doDebug,'attempting to insert config record');
					} else {
						if ($doDebug) {
							echo "config successfully inserted<br />";
						}
					}

				} else {
					if ($doDebug) {
						echo "report name $inp_report_name is being updated<br />\n";
					}
					$updateResult		= $wpdb->update('wpw1_cwa_report_configurations',
													array('rg_table'=>'student',
														  'rg_config'=>$rg_config,
														  'date_written'=>$myStr),
													array('rg_report_name'=>$inp_report_name),
													array('%s','%s','%s'),
													array('%s'));
					if ($updateResult === FALSE) {
						handleWPDBError($jobname,$doDebug,"attempting to update $inp_report_name report");
					} else {
						if ($doDebug) {
							echo "report successfully updated<br />";
						}
					}
				}
			}
		} 
		
		$myInt = strpos($where,'futureSemester');
		if ($myInt !== FALSE) {
			$where = str_replace('futureSemester',$futureSemester,$where);
		}
		$myInt = strpos($where,'proximateSemester');
		if ($myInt !== FALSE) {
			$where = str_replace('proximateSemester',$proximateSemester,$where);
		}
		if ($inp_report_name != '') {
			$myReportName		= "<h4>$inp_report_name</h4>";
		} else{
			$myReportName		= '';
		}
		
		if ($doDebug) {
			echo "report setup complete<br />";
		}
		
		$content				.= "<h2>Generated Report from the $studentTableName Table</h2>
									$myReportName
									<p>Save report: $inp_report<br />";

		$sql = "select * from $studentTableName 
				left join $userMasterTableName  on student_call_sign = user_call_sign ";
		if ($where != '') {
			$sql	= "$sql where $where ";
		}
		if ($orderby != '') {
			$sql	= "$sql order by $orderby";
		}
//		$sql = "$sql limit 10";
		$content	.= "SQL: $sql</p>";
		if ($doDebug) {
			echo "output_type is $output_type<br />";
		}
		if ($doBySequence) {
			ksort($sequenceArray);
			if ($output_type == 'table') {
				$content .= "<table><tr>";
				foreach($sequenceArray as $thisSequence => $thisField) {
					$headerName	= $nameConversionArray[$thisField];
					$content .= "<th>$headerName</th>";
				}
				$content .= "</tr>";			
			} else {
				$tabValue		= $tabTypeArray[$tabType];
				$needComma = FALSE;
				$content		.= "<pre>";
				// prepare the csv file and write the headers
				$csvStr		= "$userName" . "_srg_download.csv";
				if (preg_match('/localhost/',$siteURL)) {
					$thisFileName	= "wp-content/uploads/$csvStr";
				} else {
					$thisFileName	= "/home/cwacwops/public_html/wp-content/uploads/$csvStr";
				}
				if ($doDebug) {
					echo "set up to write $thisFileName<br />";
				}
				$thisFP			= fopen($thisFileName,'w');
				$thisOutput		= array();
				foreach($sequenceArray as $thisSequence => $thisField) {
					if ($needComma) {
						$content .= $tabValue;
					}
					$headerName	= $nameConversionArray[$thisField];
					$headerName = str_replace('<br />','_',$headerName);
					$thisOutput[] = $headerName;
					$content .= $headerName;
					$needComma = TRUE;
				}
				$content .= "\n";
				fputcsv($thisFP,$thisOutput,$tabValue);
			}
		
		} else {
			if ($output_type == 'table') {
				$content .= "<table><tr>";
				
				foreach($fieldArray as $thisField) {
					if (${$thisField . '_checked'} == 'X') {
						$headerName = $nameConversionArray[$thisField];
						 $content .= "<th>$headerName</th>";
					}			
				}
				$content	.= "</tr>";	
			} else {
				$tabValue		= $tabTypeArray[$tabType];
				$needComma = FALSE;
				$content		.= "<pre>";
				// prepare the csv file and write the headers
				$csvStr		= "$userName" . "_srg_download.csv";
				if (preg_match('/localhost/',$siteURL)) {
					$thisFileName	= "wp-content/uploads/$csvStr";
				} else {
					$thisFileName	= "/home/cwacwops/public_html/wp-content/uploads/$csvStr";
				}
				if ($doDebug) {
					echo "set up to write $thisFileName<br />";
				}
				$thisFP			= fopen($thisFileName,'w');
				$thisOutput		= array();
				foreach($fieldArray as $thisField) {
					if (${$thisField . '_checked'} == 'X') {
						if ($needComma) {
							$content .= $tabValue;
						}
						$headerName = $nameConversionArray[$thisField];
						$headerName = str_replace('<br />','_',$headerName);
						$content .= $headerName;
						$thisOutput[] = $headerName;
						$needComma = TRUE;
					}			
				}
				$content	.= "\n";
				fputcsv($thisFP,$thisOutput,$tabValue);
			}
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
					if ($doBySequence) {
						if ($output_type == 'table') {
							$content	.= "<tr>";
							foreach($sequenceArray as $thisSeq => $thisField) {
								$dataToDisplay = ${$thisField};
								$myStr = $dataToDisplay;
								if ($thisField == 'user_call_sign') {
									$myStr = "<a href='$siteURL/cwa-display-and-update-student-signup-information/?strpass=2&request_type=callsign&request_info=$dataToDisplay&inp_depth=one&doDebug&testMode' 
												target='_blank'>$dataToDisplay</a>";
								}
								if ($thisField == 'student_assigned_advisor') {
									$myStr = "<a href='$siteURL/cwa-display-and-update-advisor-signup-info/?strpass=2&request_type=callsign&request_info=$dataToDisplay&inp_depth=one&doDebug&testMode' 
												target='_blank'>$dataToDisplay</a>";
								}
								if ($thisField == 'student_pre_assigned_advisor') {
									$myStr = "<a href='$siteURL/cwa-display-and-update-advisor-signup-info/?strpass=2&request_type=callsign&request_info=$dataToDisplay&inp_depth=one&doDebug&testMode' 
												target='_blank'>$dataToDisplay</a>";
								}
							   $content .= "<td style='vertical-align:top'>$myStr</td>";
							}
							$content	.= "</tr>";
						} else {
							$needComma = FALSE;
							$thisOutput = array();
							foreach($sequenceArray as $thisSeq => $thisField) {								
								if ($needComma) {
									$content .= $tabValue;
								}
								$thisOutput[] = $$thisField;
								$content .= $$thisField;
								$needComma = TRUE;
							}
							$content .= "\n";
							fputcsv($thisFP,$thisOutput,$tabValue);

						}
					} else {
						if ($output_type == 'table') {
							$content	.= "<tr>";
							foreach($fieldArray as $thisField) {
								if (${$thisField . '_checked'} == 'X') {
									$dataToDisplay = ${$thisField};
									$myStr = $dataToDisplay;
									if ($thisField == 'user_call_sign') {
										$myStr = "<a href='$siteURL/cwa-display-and-update-student-signup-information/?strpass=2&request_type=callsign&request_info=$dataToDisplay&inp_depth=one&doDebug&testMode' 
													target='_blank'>$dataToDisplay</a>";
									}
									if ($thisField == 'student_assigned_advisor') {
										$myStr = "<a href='$siteURL/cwa-display-and-update-advisor-signup-info/?strpass=2&request_type=callsign&request_info=$dataToDisplay&inp_depth=one&doDebug&testMode' 
													target='_blank'>$dataToDisplay</a>";
									}
									if ($thisField == 'student_pre_assigned_advisor') {
										$myStr = "<a href='$siteURL/cwa-display-and-update-advisor-signup-info/?strpass=2&request_type=callsign&request_info=$dataToDisplay&inp_depth=one&doDebug&testMode' 
													target='_blank'>$dataToDisplay</a>";
									}
								   $content .= "<td style='vertical-align:top'>$myStr</td>";
								}
							}
							$content	.= "</tr>\n";
						} else {			// output will be a comma separated file
							$needComma = FALSE;
							$thisOutput = array();
							foreach($fieldArray as $thisField) {
								if (${$thisField . '_checked'} == 'X') {
									if ($needComma) {
										$content .= $tabValue;
									}
									$content .= $$thisField;
									$thisOutput[] = $$thisField;
									$needComma = TRUE;
								}
							}
							$content	.= "\n";
							fputcsv($thisFP,$thisOutput,$tabValue);
						}	
					}			
				}
			}
			if ($output_type == 'table') {
				$content	.= "</table>";
			} else {
				$content	.= "</pre>";
				fclose($thisFP);
				$content	.= "<p><p>Click <a href='$siteURL/wp-content/uploads/$csvStr'>$csvStr</a> to download the csv file</p>";
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
add_shortcode ('new_student_report_generator', 'new_student_report_generator_func');

