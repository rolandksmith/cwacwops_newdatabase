function advisorclass_report_generator_func() {

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
	$currentSemester	= $context->currentSemester;
	$nextSemester		= $context->nextSemester;
	$semesterTwo		= $context->semesterTwo;
	$semesterThree		= $context->semesterThree;
	$semesterFour		= $context->semesterFour;
	$futureSemester		= "(semester = '$nextSemester' or semester = '$semesterTwo' or semester = '$semesterThree' or semester = '$semesterFour')";
	$proximateSemester	= $context->proximateSemester;
	if ($validUser == "N") {
		return "YOU'RE NOT AUTHORIZED!<br />Goodby";
	}
	$versionNumber = '1';
	$jobname = "AdvisorClass Report Generator V$versionNumber";
	
/// get the time that the process started
	$startingMicroTime			= microtime(TRUE);

	ini_set('display_errors','1');
	error_reporting(E_ALL);	
	ini_set('memory_limit','256M');
	ini_set('max_execution_time',360);
		
	$strPass					= "1";
	$requestType				= '';
	$where						= '';
	$orderby					= '';
	$firstTime					= TRUE;
	$inp_report					= '';
	$inp_config					= 'N';
	$inp_report_name			= '';
	$reportConfig				= array();
	$rg_config					= '';
	$theURL						= "$siteURL/cwa-advisorclass-report-generator//";

    $user_ID = '';
    $user_call_sign = '';
    $user_first_name = '';
    $user_last_name = '';
    $user_email = '';
    $user_phone = '';
    $user_ph_code = '';
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
    $advisor_id = '';
    $advisor_call_sign = '';
    $advisor_semester = '';
    $advisor_welcome_email_date = '';
    $advisor_verify_email_date = '';
    $advisor_verify_email_number = '';
    $advisor_verify_response = '';
    $advisor_action_log = '';
    $advisor_class_verified = '';
    $advisor_control_code = '';
    $advisor_date_created = '';
    $advisor_date_updated = '';
    $advisor_replacement_status = '';
    $advisorclass_id = '';
    $advisorclass_call_sign = '';
    $advisorclass_sequence = '';
    $advisorclass_semester = '';
    $advisorclass_timezone_offset = '';
    $advisorclass_level = '';
    $advisorclass_language = '';
    $advisorclass_class_size = '';
    $advisorclass_class_schedule_days = '';
    $advisorclass_class_schedule_times = '';
    $advisorclass_class_schedule_days_utc = '';
    $advisorclass_class_schedule_times_utc = '';
    $advisorclass_action_log = '';
    $advisorclass_class_incomplete = '';
    $advisorclass_date_created = '';
    $advisorclass_date_updated = '';
    $advisorclass_student01 = '';
    $advisorclass_student02 = '';
    $advisorclass_student03 = '';
    $advisorclass_student04 = '';
    $advisorclass_student05 = '';
    $advisorclass_student06 = '';
    $advisorclass_student07 = '';
    $advisorclass_student08 = '';
    $advisorclass_student09 = '';
    $advisorclass_student10 = '';
    $advisorclass_student11 = '';
    $advisorclass_student12 = '';
    $advisorclass_student13 = '';
    $advisorclass_student14 = '';
    $advisorclass_student15 = '';
    $advisorclass_student16 = '';
    $advisorclass_student17 = '';
    $advisorclass_student18 = '';
    $advisorclass_student19 = '';
    $advisorclass_student20 = '';
    $advisorclass_student21 = '';
    $advisorclass_student22 = '';
    $advisorclass_student23 = '';
    $advisorclass_student24 = '';
    $advisorclass_student25 = '';
    $advisorclass_student26 = '';
    $advisorclass_student27 = '';
    $advisorclass_student28 = '';
    $advisorclass_student29 = '';
    $advisorclass_student30 = '';
    $advisorclass_number_students = '';
    $advisorclass_evaluation_complete = '';
    $advisorclass_class_comments = '';
    $advisorclass_copy_control = '';

	// set the variables to unchecked
    $user_ID_checked = '';
    $user_call_sign_checked = '';
    $user_first_name_checked = '';
    $user_last_name_checked = '';
    $user_email_checked = '';
    $user_phone_checked = '';
    $user_ph_code_checked = '';
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
    $advisor_id_checked = '';
    $advisor_call_sign_checked = '';
    $advisor_semester_checked = '';
    $advisor_welcome_email_date_checked = '';
    $advisor_verify_email_date_checked = '';
    $advisor_verify_email_number_checked = '';
    $advisor_verify_response_checked = '';
    $advisor_action_log_checked = '';
    $advisor_class_verified_checked = '';
    $advisor_control_code_checked = '';
    $advisor_date_created_checked = '';
    $advisor_date_updated_checked = '';
    $advisor_replacement_status_checked = '';
    $advisorclass_id_checked = '';
    $advisorclass_call_sign_checked = '';
    $advisorclass_sequence_checked = '';
    $advisorclass_semester_checked = '';
    $advisorclass_timezone_offset_checked = '';
    $advisorclass_level_checked = '';
    $advisorclass_language_checked = '';
    $advisorclass_class_size_checked = '';
    $advisorclass_class_schedule_days_checked = '';
    $advisorclass_class_schedule_times_checked = '';
    $advisorclass_class_schedule_days_utc_checked = '';
    $advisorclass_class_schedule_times_utc_checked = '';
    $advisorclass_action_log_checked = '';
    $advisorclass_class_incomplete_checked = '';
    $advisorclass_date_created_checked = '';
    $advisorclass_date_updated_checked = '';
    $advisorclass_student01_checked = '';
    $advisorclass_student02_checked = '';
    $advisorclass_student03_checked = '';
    $advisorclass_student04_checked = '';
    $advisorclass_student05_checked = '';
    $advisorclass_student06_checked = '';
    $advisorclass_student07_checked = '';
    $advisorclass_student08_checked = '';
    $advisorclass_student09_checked = '';
    $advisorclass_student10_checked = '';
    $advisorclass_student11_checked = '';
    $advisorclass_student12_checked = '';
    $advisorclass_student13_checked = '';
    $advisorclass_student14_checked = '';
    $advisorclass_student15_checked = '';
    $advisorclass_student16_checked = '';
    $advisorclass_student17_checked = '';
    $advisorclass_student18_checked = '';
    $advisorclass_student19_checked = '';
    $advisorclass_student20_checked = '';
    $advisorclass_student21_checked = '';
    $advisorclass_student22_checked = '';
    $advisorclass_student23_checked = '';
    $advisorclass_student24_checked = '';
    $advisorclass_student25_checked = '';
    $advisorclass_student26_checked = '';
    $advisorclass_student27_checked = '';
    $advisorclass_student28_checked = '';
    $advisorclass_student29_checked = '';
    $advisorclass_student30_checked = '';
    $advisorclass_number_students_checked = '';
    $advisorclass_evaluation_complete_checked = '';
    $advisorclass_class_comments_checked = '';
    $advisorclass_copy_control_checked = '';
	$comma_checked						= '';	
	$thisWhere							= '';
	$thisOrderby						= 'advisorclass_call_sign';
	$table_checked						= " checked ";
	$comma_checked						= "";


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
            if ($str_key == 'user_ph_code') {
                $user_ph_code_checked = 'X';
                $reportConfig['user_ph_code_checked'] = 'X';
                if ($doDebug) {
                    echo "user_ph_code included in report<br />";
                }
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
            if ($str_key == 'advisor_id') {
                $advisor_id_checked = 'X';
                $reportConfig['advisor_id_checked'] = 'X';
                if ($doDebug) {
                    echo "advisor_id included in report<br />";
                }
            }
            if ($str_key == 'advisor_call_sign') {
                $advisor_call_sign_checked = 'X';
                $reportConfig['advisor_call_sign_checked'] = 'X';
                if ($doDebug) {
                    echo "advisor_call_sign included in report<br />";
                }
            }
            if ($str_key == 'advisor_semester') {
                $advisor_semester_checked = 'X';
                $reportConfig['advisor_semester_checked'] = 'X';
                if ($doDebug) {
                    echo "advisor_semester included in report<br />";
                }
            }
            if ($str_key == 'advisor_welcome_email_date') {
                $advisor_welcome_email_date_checked = 'X';
                $reportConfig['advisor_welcome_email_date_checked'] = 'X';
                if ($doDebug) {
                    echo "advisor_welcome_email_date included in report<br />";
                }
            }
            if ($str_key == 'advisor_verify_email_date') {
                $advisor_verify_email_date_checked = 'X';
                $reportConfig['advisor_verify_email_date_checked'] = 'X';
                if ($doDebug) {
                    echo "advisor_verify_email_date included in report<br />";
                }
            }
            if ($str_key == 'advisor_verify_email_number') {
                $advisor_verify_email_number_checked = 'X';
                $reportConfig['advisor_verify_email_number_checked'] = 'X';
                if ($doDebug) {
                    echo "advisor_verify_email_number included in report<br />";
                }
            }
            if ($str_key == 'advisor_verify_response') {
                $advisor_verify_response_checked = 'X';
                $reportConfig['advisor_verify_response_checked'] = 'X';
                if ($doDebug) {
                    echo "advisor_verify_response included in report<br />";
                }
            }
            if ($str_key == 'advisor_action_log') {
                $advisor_action_log_checked = 'X';
                $reportConfig['advisor_action_log_checked'] = 'X';
                if ($doDebug) {
                    echo "advisor_action_log included in report<br />";
                }
            }
            if ($str_key == 'advisor_class_verified') {
                $advisor_class_verified_checked = 'X';
                $reportConfig['advisor_class_verified_checked'] = 'X';
                if ($doDebug) {
                    echo "advisor_class_verified included in report<br />";
                }
            }
            if ($str_key == 'advisor_control_code') {
                $advisor_control_code_checked = 'X';
                $reportConfig['advisor_control_code_checked'] = 'X';
                if ($doDebug) {
                    echo "advisor_control_code included in report<br />";
                }
            }
            if ($str_key == 'advisor_date_created') {
                $advisor_date_created_checked = 'X';
                $reportConfig['advisor_date_created_checked'] = 'X';
                if ($doDebug) {
                    echo "advisor_date_created included in report<br />";
                }
            }
            if ($str_key == 'advisor_date_updated') {
                $advisor_date_updated_checked = 'X';
                $reportConfig['advisor_date_updated_checked'] = 'X';
                if ($doDebug) {
                    echo "advisor_date_updated included in report<br />";
                }
            }
            if ($str_key == 'advisor_replacement_status') {
                $advisor_replacement_status_checked = 'X';
                $reportConfig['advisor_replacement_status_checked'] = 'X';
                if ($doDebug) {
                    echo "advisor_replacement_status included in report<br />";
                }
            }
            if ($str_key == 'advisorclass_id') {
                $advisorclass_id_checked = 'X';
                $reportConfig['advisorclass_id_checked'] = 'X';
                if ($doDebug) {
                    echo "advisorclass_id included in report<br />";
                }
            }
            if ($str_key == 'advisorclass_call_sign') {
                $advisorclass_call_sign_checked = 'X';
                $reportConfig['advisorclass_call_sign_checked'] = 'X';
                if ($doDebug) {
                    echo "advisorclass_call_sign included in report<br />";
                }
            }
            if ($str_key == 'advisorclass_sequence') {
                $advisorclass_sequence_checked = 'X';
                $reportConfig['advisorclass_sequence_checked'] = 'X';
                if ($doDebug) {
                    echo "advisorclass_sequence included in report<br />";
                }
            }
            if ($str_key == 'advisorclass_semester') {
                $advisorclass_semester_checked = 'X';
                $reportConfig['advisorclass_semester_checked'] = 'X';
                if ($doDebug) {
                    echo "advisorclass_semester included in report<br />";
                }
            }
            if ($str_key == 'advisorclass_timezone_offset') {
                $advisorclass_timezone_offset_checked = 'X';
                $reportConfig['advisorclass_timezone_offset_checked'] = 'X';
                if ($doDebug) {
                    echo "advisorclass_timezone_offset included in report<br />";
                }
            }
            if ($str_key == 'advisorclass_level') {
                $advisorclass_level_checked = 'X';
                $reportConfig['advisorclass_level_checked'] = 'X';
                if ($doDebug) {
                    echo "advisorclass_level included in report<br />";
                }
            }
            if ($str_key == 'advisorclass_language') {
                $advisorclass_language_checked = 'X';
                $reportConfig['advisorclass_language_checked'] = 'X';
                if ($doDebug) {
                    echo "advisorclass_language included in report<br />";
                }
            }
            if ($str_key == 'advisorclass_class_size') {
                $advisorclass_class_size_checked = 'X';
                $reportConfig['advisorclass_class_size_checked'] = 'X';
                if ($doDebug) {
                    echo "advisorclass_class_size included in report<br />";
                }
            }
            if ($str_key == 'advisorclass_class_schedule_days') {
                $advisorclass_class_schedule_days_checked = 'X';
                $reportConfig['advisorclass_class_schedule_days_checked'] = 'X';
                if ($doDebug) {
                    echo "advisorclass_class_schedule_days included in report<br />";
                }
            }
            if ($str_key == 'advisorclass_class_schedule_times') {
                $advisorclass_class_schedule_times_checked = 'X';
                $reportConfig['advisorclass_class_schedule_times_checked'] = 'X';
                if ($doDebug) {
                    echo "advisorclass_class_schedule_times included in report<br />";
                }
            }
            if ($str_key == 'advisorclass_class_schedule_days_utc') {
                $advisorclass_class_schedule_days_utc_checked = 'X';
                $reportConfig['advisorclass_class_schedule_days_utc_checked'] = 'X';
                if ($doDebug) {
                    echo "advisorclass_class_schedule_days_utc included in report<br />";
                }
            }
            if ($str_key == 'advisorclass_class_schedule_times_utc') {
                $advisorclass_class_schedule_times_utc_checked = 'X';
                $reportConfig['advisorclass_class_schedule_times_utc_checked'] = 'X';
                if ($doDebug) {
                    echo "advisorclass_class_schedule_times_utc included in report<br />";
                }
            }
            if ($str_key == 'advisorclass_action_log') {
                $advisorclass_action_log_checked = 'X';
                $reportConfig['advisorclass_action_log_checked'] = 'X';
                if ($doDebug) {
                    echo "advisorclass_action_log included in report<br />";
                }
            }
            if ($str_key == 'advisorclass_class_incomplete') {
                $advisorclass_class_incomplete_checked = 'X';
                $reportConfig['advisorclass_class_incomplete_checked'] = 'X';
                if ($doDebug) {
                    echo "advisorclass_class_incomplete included in report<br />";
                }
            }
            if ($str_key == 'advisorclass_date_created') {
                $advisorclass_date_created_checked = 'X';
                $reportConfig['advisorclass_date_created_checked'] = 'X';
                if ($doDebug) {
                    echo "advisorclass_date_created included in report<br />";
                }
            }
            if ($str_key == 'advisorclass_date_updated') {
                $advisorclass_date_updated_checked = 'X';
                $reportConfig['advisorclass_date_updated_checked'] = 'X';
                if ($doDebug) {
                    echo "advisorclass_date_updated included in report<br />";
                }
            }
            if ($str_key == 'advisorclass_student01') {
                $advisorclass_student01_checked = 'X';
                $reportConfig['advisorclass_student01_checked'] = 'X';
                if ($doDebug) {
                    echo "advisorclass_student01 included in report<br />";
                }
            }
            if ($str_key == 'advisorclass_student02') {
                $advisorclass_student02_checked = 'X';
                $reportConfig['advisorclass_student02_checked'] = 'X';
                if ($doDebug) {
                    echo "advisorclass_student02 included in report<br />";
                }
            }
            if ($str_key == 'advisorclass_student03') {
                $advisorclass_student03_checked = 'X';
                $reportConfig['advisorclass_student03_checked'] = 'X';
                if ($doDebug) {
                    echo "advisorclass_student03 included in report<br />";
                }
            }
            if ($str_key == 'advisorclass_student04') {
                $advisorclass_student04_checked = 'X';
                $reportConfig['advisorclass_student04_checked'] = 'X';
                if ($doDebug) {
                    echo "advisorclass_student04 included in report<br />";
                }
            }
            if ($str_key == 'advisorclass_student05') {
                $advisorclass_student05_checked = 'X';
                $reportConfig['advisorclass_student05_checked'] = 'X';
                if ($doDebug) {
                    echo "advisorclass_student05 included in report<br />";
                }
            }
            if ($str_key == 'advisorclass_student06') {
                $advisorclass_student06_checked = 'X';
                $reportConfig['advisorclass_student06_checked'] = 'X';
                if ($doDebug) {
                    echo "advisorclass_student06 included in report<br />";
                }
            }
            if ($str_key == 'advisorclass_student07') {
                $advisorclass_student07_checked = 'X';
                $reportConfig['advisorclass_student07_checked'] = 'X';
                if ($doDebug) {
                    echo "advisorclass_student07 included in report<br />";
                }
            }
            if ($str_key == 'advisorclass_student08') {
                $advisorclass_student08_checked = 'X';
                $reportConfig['advisorclass_student08_checked'] = 'X';
                if ($doDebug) {
                    echo "advisorclass_student08 included in report<br />";
                }
            }
            if ($str_key == 'advisorclass_student09') {
                $advisorclass_student09_checked = 'X';
                $reportConfig['advisorclass_student09_checked'] = 'X';
                if ($doDebug) {
                    echo "advisorclass_student09 included in report<br />";
                }
            }
            if ($str_key == 'advisorclass_student10') {
                $advisorclass_student10_checked = 'X';
                $reportConfig['advisorclass_student10_checked'] = 'X';
                if ($doDebug) {
                    echo "advisorclass_student10 included in report<br />";
                }
            }
            if ($str_key == 'advisorclass_student11') {
                $advisorclass_student11_checked = 'X';
                $reportConfig['advisorclass_student11_checked'] = 'X';
                if ($doDebug) {
                    echo "advisorclass_student11 included in report<br />";
                }
            }
            if ($str_key == 'advisorclass_student12') {
                $advisorclass_student12_checked = 'X';
                $reportConfig['advisorclass_student12_checked'] = 'X';
                if ($doDebug) {
                    echo "advisorclass_student12 included in report<br />";
                }
            }
            if ($str_key == 'advisorclass_student13') {
                $advisorclass_student13_checked = 'X';
                $reportConfig['advisorclass_student13_checked'] = 'X';
                if ($doDebug) {
                    echo "advisorclass_student13 included in report<br />";
                }
            }
            if ($str_key == 'advisorclass_student14') {
                $advisorclass_student14_checked = 'X';
                $reportConfig['advisorclass_student14_checked'] = 'X';
                if ($doDebug) {
                    echo "advisorclass_student14 included in report<br />";
                }
            }
            if ($str_key == 'advisorclass_student15') {
                $advisorclass_student15_checked = 'X';
                $reportConfig['advisorclass_student15_checked'] = 'X';
                if ($doDebug) {
                    echo "advisorclass_student15 included in report<br />";
                }
            }
            if ($str_key == 'advisorclass_student16') {
                $advisorclass_student16_checked = 'X';
                $reportConfig['advisorclass_student16_checked'] = 'X';
                if ($doDebug) {
                    echo "advisorclass_student16 included in report<br />";
                }
            }
            if ($str_key == 'advisorclass_student17') {
                $advisorclass_student17_checked = 'X';
                $reportConfig['advisorclass_student17_checked'] = 'X';
                if ($doDebug) {
                    echo "advisorclass_student17 included in report<br />";
                }
            }
            if ($str_key == 'advisorclass_student18') {
                $advisorclass_student18_checked = 'X';
                $reportConfig['advisorclass_student18_checked'] = 'X';
                if ($doDebug) {
                    echo "advisorclass_student18 included in report<br />";
                }
            }
            if ($str_key == 'advisorclass_student19') {
                $advisorclass_student19_checked = 'X';
                $reportConfig['advisorclass_student19_checked'] = 'X';
                if ($doDebug) {
                    echo "advisorclass_student19 included in report<br />";
                }
            }
            if ($str_key == 'advisorclass_student20') {
                $advisorclass_student20_checked = 'X';
                $reportConfig['advisorclass_student20_checked'] = 'X';
                if ($doDebug) {
                    echo "advisorclass_student20 included in report<br />";
                }
            }
            if ($str_key == 'advisorclass_student21') {
                $advisorclass_student21_checked = 'X';
                $reportConfig['advisorclass_student21_checked'] = 'X';
                if ($doDebug) {
                    echo "advisorclass_student21 included in report<br />";
                }
            }
            if ($str_key == 'advisorclass_student22') {
                $advisorclass_student22_checked = 'X';
                $reportConfig['advisorclass_student22_checked'] = 'X';
                if ($doDebug) {
                    echo "advisorclass_student22 included in report<br />";
                }
            }
            if ($str_key == 'advisorclass_student23') {
                $advisorclass_student23_checked = 'X';
                $reportConfig['advisorclass_student23_checked'] = 'X';
                if ($doDebug) {
                    echo "advisorclass_student23 included in report<br />";
                }
            }
            if ($str_key == 'advisorclass_student24') {
                $advisorclass_student24_checked = 'X';
                $reportConfig['advisorclass_student24_checked'] = 'X';
                if ($doDebug) {
                    echo "advisorclass_student24 included in report<br />";
                }
            }
            if ($str_key == 'advisorclass_student25') {
                $advisorclass_student25_checked = 'X';
                $reportConfig['advisorclass_student25_checked'] = 'X';
                if ($doDebug) {
                    echo "advisorclass_student25 included in report<br />";
                }
            }
            if ($str_key == 'advisorclass_student26') {
                $advisorclass_student26_checked = 'X';
                $reportConfig['advisorclass_student26_checked'] = 'X';
                if ($doDebug) {
                    echo "advisorclass_student26 included in report<br />";
                }
            }
            if ($str_key == 'advisorclass_student27') {
                $advisorclass_student27_checked = 'X';
                $reportConfig['advisorclass_student27_checked'] = 'X';
                if ($doDebug) {
                    echo "advisorclass_student27 included in report<br />";
                }
            }
            if ($str_key == 'advisorclass_student28') {
                $advisorclass_student28_checked = 'X';
                $reportConfig['advisorclass_student28_checked'] = 'X';
                if ($doDebug) {
                    echo "advisorclass_student28 included in report<br />";
                }
            }
            if ($str_key == 'advisorclass_student29') {
                $advisorclass_student29_checked = 'X';
                $reportConfig['advisorclass_student29_checked'] = 'X';
                if ($doDebug) {
                    echo "advisorclass_student29 included in report<br />";
                }
            }
            if ($str_key == 'advisorclass_student30') {
                $advisorclass_student30_checked = 'X';
                $reportConfig['advisorclass_student30_checked'] = 'X';
                if ($doDebug) {
                    echo "advisorclass_student30 included in report<br />";
                }
            }
            if ($str_key == 'advisorclass_number_students') {
                $advisorclass_number_students_checked = 'X';
                $reportConfig['advisorclass_number_students_checked'] = 'X';
                if ($doDebug) {
                    echo "advisorclass_number_students included in report<br />";
                }
            }
            if ($str_key == 'advisorclass_evaluation_complete') {
                $advisorclass_evaluation_complete_checked = 'X';
                $reportConfig['advisorclass_evaluation_complete_checked'] = 'X';
                if ($doDebug) {
                    echo "advisorclass_evaluation_complete included in report<br />";
                }
            }
            if ($str_key == 'advisorclass_class_comments') {
                $advisorclass_class_comments_checked = 'X';
                $reportConfig['advisorclass_class_comments_checked'] = 'X';
                if ($doDebug) {
                    echo "advisorclass_class_comments included in report<br />";
                }
            }
            if ($str_key == 'advisorclass_copy_control') {
                $advisorclass_copy_control_checked = 'X';
                $reportConfig['advisorclass_copy_control_checked'] = 'X';
                if ($doDebug) {
                    echo "advisorclass_copy_control included in report<br />";
                }
            }

			if ($str_key 				== "mode_type") {
				$mode_type				 = $str_value;
				$mode_type				 = filter_var($mode_type,FILTER_UNSAFE_RAW);
				$reportConfig['mode_type']	= $mode_type;
			}
			if($str_key					== "orderby") {
				$orderby				= $str_value;
				$orderby				 = filter_var($orderby,FILTER_UNSAFE_RAW);
				$orderby				= stripslashes($orderby);
			}
			if($str_key					== "output_type") {
				$output_type				 = $str_value;
				$output_type				 = filter_var($output_type,FILTER_UNSAFE_RAW);
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
			if($str_key					== "rg_config") {
				$myStr					 = $str_value;
				$rg_config				= base64_decode($myStr);
			}
		}
	}
	
	if ($doDebug) {
		echo "<br /><b>user_call_sign_checked:</b><br /><pre>";
		var_dump($user_call_sign_checked);
		echo "</pre><br />";
	}
	
	$content = "";	

	if ("1" == $strPass) {
		if ($doDebug) {
			echo "<br />Function starting.<br />\n";
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
							<tr><td style='width:300px;'><u><b>User Master Fields</b></u></td><td>Field Name</td></tr>
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
                            <tr><td><input type='checkbox' class='formInputButton' id='advisor_id' 
                                    name='advisor_id' value='advisor_id'>
                                    <label for 'advisor_id'>advisor_id</label></td>
                                <td>advisor_id</td></tr>
				
							<tr><td><br /><u><b>Advisor Fields</b></u></td><td>Field Name</td></tr>
                            <tr><td><input type='checkbox' class='formInputButton' id='advisor_id' 
                                    name='advisor_id' value='advisor_id'>
                                    <label for 'advisor_id'>advisor_id</label></td>
                                <td>advisor_id</td></tr>
                            <tr><td><input type='checkbox' class='formInputButton' id='advisor_call_sign' 
                                    name='advisor_call_sign' value='advisor_call_sign'>
                                    <label for 'advisor_call_sign'>advisor_call_sign</label></td>
                                <td>advisor_call_sign</td></tr>
                            <tr><td><input type='checkbox' class='formInputButton' id='advisor_semester' 
                                    name='advisor_semester' value='advisor_semester'>
                                    <label for 'advisor_semester'>advisor_semester</label></td>
                                <td>advisor_semester</td></tr>
                            <tr><td><input type='checkbox' class='formInputButton' id='advisor_welcome_email_date' 
                                    name='advisor_welcome_email_date' value='advisor_welcome_email_date'>
                                    <label for 'advisor_welcome_email_date'>advisor_welcome_email_date</label></td>
                                <td>advisor_welcome_email_date</td></tr>
                            <tr><td><input type='checkbox' class='formInputButton' id='advisor_verify_email_date' 
                                    name='advisor_verify_email_date' value='advisor_verify_email_date'>
                                    <label for 'advisor_verify_email_date'>advisor_verify_email_date</label></td>
                                <td>advisor_verify_email_date</td></tr>
                            <tr><td><input type='checkbox' class='formInputButton' id='advisor_verify_email_number' 
                                    name='advisor_verify_email_number' value='advisor_verify_email_number'>
                                    <label for 'advisor_verify_email_number'>advisor_verify_email_number</label></td>
                                <td>advisor_verify_email_number</td></tr>
                            <tr><td><input type='checkbox' class='formInputButton' id='advisor_verify_response' 
                                    name='advisor_verify_response' value='advisor_verify_response'>
                                    <label for 'advisor_verify_response'>advisor_verify_response</label></td>
                                <td>advisor_verify_response</td></tr>
                            <tr><td><input type='checkbox' class='formInputButton' id='advisor_action_log' 
                                    name='advisor_action_log' value='advisor_action_log'>
                                    <label for 'advisor_action_log'>advisor_action_log</label></td>
                                <td>advisor_action_log</td></tr>
                            <tr><td><input type='checkbox' class='formInputButton' id='advisor_class_verified' 
                                    name='advisor_class_verified' value='advisor_class_verified'>
                                    <label for 'advisor_class_verified'>advisor_class_verified</label></td>
                                <td>advisor_class_verified</td></tr>
                            <tr><td><input type='checkbox' class='formInputButton' id='advisor_control_code' 
                                    name='advisor_control_code' value='advisor_control_code'>
                                    <label for 'advisor_control_code'>advisor_control_code</label></td>
                                <td>advisor_control_code</td></tr>
                            <tr><td><input type='checkbox' class='formInputButton' id='advisor_date_created' 
                                    name='advisor_date_created' value='advisor_date_created'>
                                    <label for 'advisor_date_created'>advisor_date_created</label></td>
                                <td>advisor_date_created</td></tr>
                            <tr><td><input type='checkbox' class='formInputButton' id='advisor_date_updated' 
                                    name='advisor_date_updated' value='advisor_date_updated'>
                                    <label for 'advisor_date_updated'>advisor_date_updated</label></td>
                                <td>advisor_date_updated</td></tr>
                           <tr><td><input type='checkbox' class='formInputButton' id='advisor_replacement_status' 
                                    name='advisor_replacement_status' value='advisor_replacement_status'>
                                    <label for 'advisor_replacement_status'>advisor_replacement_status</label></td>
                                <td>advisor_replacement_status</td></tr>
				
							<tr><td><u><b>AdvisorClass Fields</b></u></td><td>Field Name</td></tr>
                           <tr><td><input type='checkbox' class='formInputButton' id='advisorclass_id' 
                                    name='advisorclass_id' value='advisorclass_id'>
                                    <label for 'advisorclass_id'>advisorclass_id</label></td>
                                <td>advisorclass_id</td></tr>
                            <tr><td><input type='checkbox' class='formInputButton' id='advisorclass_call_sign' 
                                    name='advisorclass_call_sign' value='advisorclass_call_sign'>
                                    <label for 'advisorclass_call_sign'>advisorclass_call_sign</label></td>
                                <td>advisorclass_call_sign</td></tr>
                            <tr><td><input type='checkbox' class='formInputButton' id='advisorclass_sequence' 
                                    name='advisorclass_sequence' value='advisorclass_sequence'>
                                    <label for 'advisorclass_sequence'>advisorclass_sequence</label></td>
                                <td>advisorclass_sequence</td></tr>
                            <tr><td><input type='checkbox' class='formInputButton' id='advisorclass_semester' 
                                    name='advisorclass_semester' value='advisorclass_semester'>
                                    <label for 'advisorclass_semester'>advisorclass_semester</label></td>
                                <td>advisorclass_semester</td></tr>
                            <tr><td><input type='checkbox' class='formInputButton' id='advisorclass_timezone_offset' 
                                    name='advisorclass_timezone_offset' value='advisorclass_timezone_offset'>
                                    <label for 'advisorclass_timezone_offset'>advisorclass_timezone_offset</label></td>
                                <td>advisorclass_timezone_offset</td></tr>
                            <tr><td><input type='checkbox' class='formInputButton' id='advisorclass_level' 
                                    name='advisorclass_level' value='advisorclass_level'>
                                    <label for 'advisorclass_level'>advisorclass_level</label></td>
                                <td>advisorclass_level</td></tr>
                            <tr><td><input type='checkbox' class='formInputButton' id='advisorclass_language' 
                                    name='advisorclass_language' value='advisorclass_language'>
                                    <label for 'advisorclass_language'>advisorclass_language</label></td>
                                <td>advisorclass_language</td></tr>
                            <tr><td><input type='checkbox' class='formInputButton' id='advisorclass_class_size' 
                                    name='advisorclass_class_size' value='advisorclass_class_size'>
                                    <label for 'advisorclass_class_size'>advisorclass_class_size</label></td>
                                <td>advisorclass_class_size</td></tr>
                            <tr><td><input type='checkbox' class='formInputButton' id='advisorclass_class_schedule_days' 
                                    name='advisorclass_class_schedule_days' value='advisorclass_class_schedule_days'>
                                    <label for 'advisorclass_class_schedule_days'>advisorclass_class_schedule_days</label></td>
                                <td>advisorclass_class_schedule_days</td></tr>
                            <tr><td><input type='checkbox' class='formInputButton' id='advisorclass_class_schedule_times' 
                                    name='advisorclass_class_schedule_times' value='advisorclass_class_schedule_times'>
                                    <label for 'advisorclass_class_schedule_times'>advisorclass_class_schedule_times</label></td>
                                <td>advisorclass_class_schedule_times</td></tr>
                            <tr><td><input type='checkbox' class='formInputButton' id='advisorclass_class_schedule_days_utc' 
                                    name='advisorclass_class_schedule_days_utc' value='advisorclass_class_schedule_days_utc'>
                                    <label for 'advisorclass_class_schedule_days_utc'>advisorclass_class_schedule_days_utc</label></td>
                                <td>advisorclass_class_schedule_days_utc</td></tr>
                            <tr><td><input type='checkbox' class='formInputButton' id='advisorclass_class_schedule_times_utc' 
                                    name='advisorclass_class_schedule_times_utc' value='advisorclass_class_schedule_times_utc'>
                                    <label for 'advisorclass_class_schedule_times_utc'>advisorclass_class_schedule_times_utc</label></td>
                                <td>advisorclass_class_schedule_times_utc</td></tr>
                            <tr><td><input type='checkbox' class='formInputButton' id='advisorclass_action_log' 
                                    name='advisorclass_action_log' value='advisorclass_action_log'>
                                    <label for 'advisorclass_action_log'>advisorclass_action_log</label></td>
                                <td>advisorclass_action_log</td></tr>
                            <tr><td><input type='checkbox' class='formInputButton' id='advisorclass_class_incomplete' 
                                    name='advisorclass_class_incomplete' value='advisorclass_class_incomplete'>
                                    <label for 'advisorclass_class_incomplete'>advisorclass_class_incomplete</label></td>
                                <td>advisorclass_class_incomplete</td></tr>
                            <tr><td><input type='checkbox' class='formInputButton' id='advisorclass_date_created' 
                                    name='advisorclass_date_created' value='advisorclass_date_created'>
                                    <label for 'advisorclass_date_created'>advisorclass_date_created</label></td>
                                <td>advisorclass_date_created</td></tr>
                            <tr><td><input type='checkbox' class='formInputButton' id='advisorclass_date_updated' 
                                    name='advisorclass_date_updated' value='advisorclass_date_updated'>
                                    <label for 'advisorclass_date_updated'>advisorclass_date_updated</label></td>
                                <td>advisorclass_date_updated</td></tr>
                            <tr><td><input type='checkbox' class='formInputButton' id='advisorclass_student01' 
                                    name='advisorclass_student01' value='advisorclass_student01'>
                                    <label for 'advisorclass_student01'>advisorclass_student01</label></td>
                                <td>advisorclass_student01</td></tr>
                            <tr><td><input type='checkbox' class='formInputButton' id='advisorclass_student02' 
                                    name='advisorclass_student02' value='advisorclass_student02'>
                                    <label for 'advisorclass_student02'>advisorclass_student02</label></td>
                                <td>advisorclass_student02</td></tr>
                            <tr><td><input type='checkbox' class='formInputButton' id='advisorclass_student03' 
                                    name='advisorclass_student03' value='advisorclass_student03'>
                                    <label for 'advisorclass_student03'>advisorclass_student03</label></td>
                                <td>advisorclass_student03</td></tr>
                            <tr><td><input type='checkbox' class='formInputButton' id='advisorclass_student04' 
                                    name='advisorclass_student04' value='advisorclass_student04'>
                                    <label for 'advisorclass_student04'>advisorclass_student04</label></td>
                                <td>advisorclass_student04</td></tr>
                            <tr><td><input type='checkbox' class='formInputButton' id='advisorclass_student05' 
                                    name='advisorclass_student05' value='advisorclass_student05'>
                                    <label for 'advisorclass_student05'>advisorclass_student05</label></td>
                                <td>advisorclass_student05</td></tr>
                            <tr><td><input type='checkbox' class='formInputButton' id='advisorclass_student06' 
                                    name='advisorclass_student06' value='advisorclass_student06'>
                                    <label for 'advisorclass_student06'>advisorclass_student06</label></td>
                                <td>advisorclass_student06</td></tr>
                            <tr><td><input type='checkbox' class='formInputButton' id='advisorclass_student07' 
                                    name='advisorclass_student07' value='advisorclass_student07'>
                                    <label for 'advisorclass_student07'>advisorclass_student07</label></td>
                                <td>advisorclass_student07</td></tr>
                            <tr><td><input type='checkbox' class='formInputButton' id='advisorclass_student08' 
                                    name='advisorclass_student08' value='advisorclass_student08'>
                                    <label for 'advisorclass_student08'>advisorclass_student08</label></td>
                                <td>advisorclass_student08</td></tr>
                            <tr><td><input type='checkbox' class='formInputButton' id='advisorclass_student09' 
                                    name='advisorclass_student09' value='advisorclass_student09'>
                                    <label for 'advisorclass_student09'>advisorclass_student09</label></td>
                                <td>advisorclass_student09</td></tr>
                            <tr><td><input type='checkbox' class='formInputButton' id='advisorclass_student10' 
                                    name='advisorclass_student10' value='advisorclass_student10'>
                                    <label for 'advisorclass_student10'>advisorclass_student10</label></td>
                                <td>advisorclass_student10</td></tr>
                            <tr><td><input type='checkbox' class='formInputButton' id='advisorclass_student11' 
                                    name='advisorclass_student11' value='advisorclass_student11'>
                                    <label for 'advisorclass_student11'>advisorclass_student11</label></td>
                                <td>advisorclass_student11</td></tr>
                            <tr><td><input type='checkbox' class='formInputButton' id='advisorclass_student12' 
                                    name='advisorclass_student12' value='advisorclass_student12'>
                                    <label for 'advisorclass_student12'>advisorclass_student12</label></td>
                                <td>advisorclass_student12</td></tr>
                            <tr><td><input type='checkbox' class='formInputButton' id='advisorclass_student13' 
                                    name='advisorclass_student13' value='advisorclass_student13'>
                                    <label for 'advisorclass_student13'>advisorclass_student13</label></td>
                                <td>advisorclass_student13</td></tr>
                            <tr><td><input type='checkbox' class='formInputButton' id='advisorclass_student14' 
                                    name='advisorclass_student14' value='advisorclass_student14'>
                                    <label for 'advisorclass_student14'>advisorclass_student14</label></td>
                                <td>advisorclass_student14</td></tr>
                            <tr><td><input type='checkbox' class='formInputButton' id='advisorclass_student15' 
                                    name='advisorclass_student15' value='advisorclass_student15'>
                                    <label for 'advisorclass_student15'>advisorclass_student15</label></td>
                                <td>advisorclass_student15</td></tr>
                            <tr><td><input type='checkbox' class='formInputButton' id='advisorclass_student16' 
                                    name='advisorclass_student16' value='advisorclass_student16'>
                                    <label for 'advisorclass_student16'>advisorclass_student16</label></td>
                                <td>advisorclass_student16</td></tr>
                            <tr><td><input type='checkbox' class='formInputButton' id='advisorclass_student17' 
                                    name='advisorclass_student17' value='advisorclass_student17'>
                                    <label for 'advisorclass_student17'>advisorclass_student17</label></td>
                                <td>advisorclass_student17</td></tr>
                            <tr><td><input type='checkbox' class='formInputButton' id='advisorclass_student18' 
                                    name='advisorclass_student18' value='advisorclass_student18'>
                                    <label for 'advisorclass_student18'>advisorclass_student18</label></td>
                                <td>advisorclass_student18</td></tr>
                            <tr><td><input type='checkbox' class='formInputButton' id='advisorclass_student19' 
                                    name='advisorclass_student19' value='advisorclass_student19'>
                                    <label for 'advisorclass_student19'>advisorclass_student19</label></td>
                                <td>advisorclass_student19</td></tr>
                            <tr><td><input type='checkbox' class='formInputButton' id='advisorclass_student20' 
                                    name='advisorclass_student20' value='advisorclass_student20'>
                                    <label for 'advisorclass_student20'>advisorclass_student20</label></td>
                                <td>advisorclass_student20</td></tr>
                            <tr><td><input type='checkbox' class='formInputButton' id='advisorclass_student21' 
                                    name='advisorclass_student21' value='advisorclass_student21'>
                                    <label for 'advisorclass_student21'>advisorclass_student21</label></td>
                                <td>advisorclass_student21</td></tr>
                            <tr><td><input type='checkbox' class='formInputButton' id='advisorclass_student22' 
                                    name='advisorclass_student22' value='advisorclass_student22'>
                                    <label for 'advisorclass_student22'>advisorclass_student22</label></td>
                                <td>advisorclass_student22</td></tr>
                            <tr><td><input type='checkbox' class='formInputButton' id='advisorclass_student23' 
                                    name='advisorclass_student23' value='advisorclass_student23'>
                                    <label for 'advisorclass_student23'>advisorclass_student23</label></td>
                                <td>advisorclass_student23</td></tr>
                            <tr><td><input type='checkbox' class='formInputButton' id='advisorclass_student24' 
                                    name='advisorclass_student24' value='advisorclass_student24'>
                                    <label for 'advisorclass_student24'>advisorclass_student24</label></td>
                                <td>advisorclass_student24</td></tr>
                            <tr><td><input type='checkbox' class='formInputButton' id='advisorclass_student25' 
                                    name='advisorclass_student25' value='advisorclass_student25'>
                                    <label for 'advisorclass_student25'>advisorclass_student25</label></td>
                                <td>advisorclass_student25</td></tr>
                            <tr><td><input type='checkbox' class='formInputButton' id='advisorclass_student26' 
                                    name='advisorclass_student26' value='advisorclass_student26'>
                                    <label for 'advisorclass_student26'>advisorclass_student26</label></td>
                                <td>advisorclass_student26</td></tr>
                            <tr><td><input type='checkbox' class='formInputButton' id='advisorclass_student27' 
                                    name='advisorclass_student27' value='advisorclass_student27'>
                                    <label for 'advisorclass_student27'>advisorclass_student27</label></td>
                                <td>advisorclass_student27</td></tr>
                            <tr><td><input type='checkbox' class='formInputButton' id='advisorclass_student28' 
                                    name='advisorclass_student28' value='advisorclass_student28'>
                                    <label for 'advisorclass_student28'>advisorclass_student28</label></td>
                                <td>advisorclass_student28</td></tr>
                            <tr><td><input type='checkbox' class='formInputButton' id='advisorclass_student29' 
                                    name='advisorclass_student29' value='advisorclass_student29'>
                                    <label for 'advisorclass_student29'>advisorclass_student29</label></td>
                                <td>advisorclass_student29</td></tr>
                            <tr><td><input type='checkbox' class='formInputButton' id='advisorclass_student30' 
                                    name='advisorclass_student30' value='advisorclass_student30'>
                                    <label for 'advisorclass_student30'>advisorclass_student30</label></td>
                                <td>advisorclass_student30</td></tr>
                            <tr><td><input type='checkbox' class='formInputButton' id='advisorclass_number_students' 
                                    name='advisorclass_number_students' value='advisorclass_number_students'>
                                    <label for 'advisorclass_number_students'>advisorclass_number_students</label></td>
                                <td>advisorclass_number_students</td></tr>
                            <tr><td><input type='checkbox' class='formInputButton' id='advisorclass_evaluation_complete' 
                                    name='advisorclass_evaluation_complete' value='advisorclass_evaluation_complete'>
                                    <label for 'advisorclass_evaluation_complete'>advisorclass_evaluation_complete</label></td>
                                <td>advisorclass_evaluation_complete</td></tr>
                            <tr><td><input type='checkbox' class='formInputButton' id='advisorclass_class_comments' 
                                    name='advisorclass_class_comments' value='advisorclass_class_comments'>
                                    <label for 'advisorclass_class_comments'>advisorclass_class_comments</label></td>
                                <td>advisorclass_class_comments</td></tr>
                            <tr><td><input type='checkbox' class='formInputButton' id='advisorclass_copy_control' 
                                    name='advisorclass_copy_control' value='advisorclass_copy_control'>
                                    <label for 'advisorclass_copy_control'>advisorclass_copy_control</label></td>
                                <td>advisorclass_copy_control</td></tr>
							</fieldset></table></p>
							
							<p>Select Output Format<br />
							<input type='radio' id='table' name='output_type' value='table' $table_checked> Table Report<br />
							<input type='radio' id='comma' name='output_type' value='comma' $comma_checked> Tab Delimited Report<br /></p>
							
							<p>Which Table to Read<br />
							<input type='radio' id='student' name='mode_type' value='Production' checked='checked'> Production<br />
							<input type='radio' id='student2' name='mode_type' value='testMode'> TestMode<br /></p>
							
							<p>Enter the 'Where' clause:<br />
							<textarea class='formInputText' id='where' name='where' rows='5' cols='80'>$thisWhere</textarea><br /></p>
							
							<p>Enter the 'Orderby' clause:<br />
							<textarea class='formInputText' id='orderby' name='orderby' rows='5' cols='80'>$thisOrderby</textarea><br /></p>
							
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
							
							<br /><br /><p>To include all future semesters, use <em>futureSemester</em>. 
							For example: level = 'Fundamental' and futureSemester <br /><br />
							To search current or upcoming semester use <em>proximateSemester</em> <br />
							</p>";




	} elseif ("2" == $strPass) {
		if ($doDebug) {
			echo "<br />at pass 2<br />";
		}	
		
		if ($mode_type == 'testMode') {
			$advisorClassTableName		= 'wpw1_cwa_advisorclass2';
			$advisorTableName			= 'wpw1_cwa_advisor2';
			$userMasterTableName		= 'wpw1_cwa_user_master2';	
		} else {
			$advisorClassTableName		= 'wpw1_cwa_advisorclass';
			$advisorTableName			= 'wpw1_cwa_advisor';	
			$userMasterTableName		= 'wpw1_cwa_user_master';	
		}	
		if ($doDebug) {
			echo "file names defined. Setting up nameConversionArray<br />";
		}
	
		// Array to convert database name to display name
        $nameConversionArray['user_ID'] = 'user<br />ID';
        $nameConversionArray['user_call_sign'] = 'user<br />call_sign';
        $nameConversionArray['user_first_name'] = 'user<br />first_name';
        $nameConversionArray['user_last_name'] = 'user<br />last_name';
        $nameConversionArray['user_email'] = 'user<br />email';
        $nameConversionArray['user_ph_code'] = 'user<br />ph code';
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
        $nameConversionArray['advisor_id'] = 'advisor<br />id';
        $nameConversionArray['advisor_call_sign'] = 'advisor<br />call_sign';
        $nameConversionArray['advisor_semester'] = 'advisor<br />semester';
        $nameConversionArray['advisor_welcome_email_date'] = 'advisor<br />welcome_email_date';
        $nameConversionArray['advisor_verify_email_date'] = 'advisor<br />verify_email_date';
        $nameConversionArray['advisor_verify_email_number'] = 'advisor<br />verify_email_number';
        $nameConversionArray['advisor_verify_response'] = 'advisor<br />verify_response';
        $nameConversionArray['advisor_action_log'] = 'advisor<br />action_log';
        $nameConversionArray['advisor_class_verified'] = 'advisor<br />class_verified';
        $nameConversionArray['advisor_control_code'] = 'advisor<br />control_code';
        $nameConversionArray['advisor_date_created'] = 'advisor<br />date_created';
        $nameConversionArray['advisor_date_updated'] = 'advisor<br />date_updated';
        $nameConversionArray['advisor_replacement_status'] = 'advisor<br />replacement_status';
        $nameConversionArray['advisorclass_id'] = 'advisorclass<br />id';
        $nameConversionArray['advisorclass_call_sign'] = 'advisorclass<br />call_sign';
        $nameConversionArray['advisorclass_sequence'] = 'advisorclass<br />sequence';
        $nameConversionArray['advisorclass_semester'] = 'advisorclass<br />semester';
        $nameConversionArray['advisorclass_timezone_offset'] = 'advisorclass<br />timezone_offset';
        $nameConversionArray['advisorclass_level'] = 'advisorclass<br />level';
        $nameConversionArray['advisorclass_language'] = 'advisorclass<br />language';
        $nameConversionArray['advisorclass_class_size'] = 'advisorclass<br />class_size';
        $nameConversionArray['advisorclass_class_schedule_days'] = 'advisorclass<br />class_schedule_days';
        $nameConversionArray['advisorclass_class_schedule_times'] = 'advisorclass<br />class_schedule_times';
        $nameConversionArray['advisorclass_class_schedule_days_utc'] = 'advisorclass<br />class_schedule_days_utc';
        $nameConversionArray['advisorclass_class_schedule_times_utc'] = 'advisorclass<br />class_schedule_times_utc';
        $nameConversionArray['advisorclass_action_log'] = 'advisorclass<br />action_log';
        $nameConversionArray['advisorclass_class_incomplete'] = 'advisorclass<br />class_incomplete';
        $nameConversionArray['advisorclass_date_created'] = 'advisorclass<br />date_created';
        $nameConversionArray['advisorclass_date_updated'] = 'advisorclass<br />date_updated';
        $nameConversionArray['advisorclass_student01'] = 'advisorclass<br />student01';
        $nameConversionArray['advisorclass_student02'] = 'advisorclass<br />student02';
        $nameConversionArray['advisorclass_student03'] = 'advisorclass<br />student03';
        $nameConversionArray['advisorclass_student04'] = 'advisorclass<br />student04';
        $nameConversionArray['advisorclass_student05'] = 'advisorclass<br />student05';
        $nameConversionArray['advisorclass_student06'] = 'advisorclass<br />student06';
        $nameConversionArray['advisorclass_student07'] = 'advisorclass<br />student07';
        $nameConversionArray['advisorclass_student08'] = 'advisorclass<br />student08';
        $nameConversionArray['advisorclass_student09'] = 'advisorclass<br />student09';
        $nameConversionArray['advisorclass_student10'] = 'advisorclass<br />student10';
        $nameConversionArray['advisorclass_student11'] = 'advisorclass<br />student11';
        $nameConversionArray['advisorclass_student12'] = 'advisorclass<br />student12';
        $nameConversionArray['advisorclass_student13'] = 'advisorclass<br />student13';
        $nameConversionArray['advisorclass_student14'] = 'advisorclass<br />student14';
        $nameConversionArray['advisorclass_student15'] = 'advisorclass<br />student15';
        $nameConversionArray['advisorclass_student16'] = 'advisorclass<br />student16';
        $nameConversionArray['advisorclass_student17'] = 'advisorclass<br />student17';
        $nameConversionArray['advisorclass_student18'] = 'advisorclass<br />student18';
        $nameConversionArray['advisorclass_student19'] = 'advisorclass<br />student19';
        $nameConversionArray['advisorclass_student20'] = 'advisorclass<br />student20';
        $nameConversionArray['advisorclass_student21'] = 'advisorclass<br />student21';
        $nameConversionArray['advisorclass_student22'] = 'advisorclass<br />student22';
        $nameConversionArray['advisorclass_student23'] = 'advisorclass<br />student23';
        $nameConversionArray['advisorclass_student24'] = 'advisorclass<br />student24';
        $nameConversionArray['advisorclass_student25'] = 'advisorclass<br />student25';
        $nameConversionArray['advisorclass_student26'] = 'advisorclass<br />student26';
        $nameConversionArray['advisorclass_student27'] = 'advisorclass<br />student27';
        $nameConversionArray['advisorclass_student28'] = 'advisorclass<br />student28';
        $nameConversionArray['advisorclass_student29'] = 'advisorclass<br />student29';
        $nameConversionArray['advisorclass_student30'] = 'advisorclass<br />student30';
        $nameConversionArray['advisorclass_number_students'] = 'advisorclass<br />number_students';
        $nameConversionArray['advisorclass_evaluation_complete'] = 'advisorclass<br />evaluation_complete';
        $nameConversionArray['advisorclass_class_comments'] = 'advisorclass<br />class_comments';
        $nameConversionArray['advisorclass_copy_control'] = 'advisorclass<br />copy_control';
	
	
		// Begin the Report Output
	
		if ($inp_config == 'Y') {		// saving the report configuration
			if ($doDebug) {
				echo "attempting to save the report configuration<br />";
			}
			if ($inp_report_name != '') {
				$whereStr					= htmlentities($where,ENT_QUOTES);
				$reportConfig['where']		= $whereStr;
				$reportConfig['orderby']	= $orderby;
				$reportConfig['rg_table']	= $advisorTableName;
				$reportConfig['output_type']		= $output_type;
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
('$inp_report_name', 'advisor', '$rg_config', '$myStr')";
				} else {
					if ($doDebug) {
						echo "report name $inp_report_name is being updated<br />\n";
					}
					$reportQuery = "update wpw1_cwa_report_configurations 
set rg_table = 'advisor', 
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
					handleWPDBError($jobname,$doDebug);
					$content		.= "<p>Unable to store report configuration</p>\n";
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
		
		$content				.= "<h2>Generated Report from the $advisorClassTableName Table</h2>
									$myReportName
									<p>Save report: $inp_report<br />";
	
		$sql	= "SELECT * FROM $advisorClassTableName  
					left join $userMasterTableName on user_call_sign = advisorclass_call_sign 
					left join $advisorTableName on advisor_call_sign = advisorclass_call_sign 
					and advisor_semester = advisorclass_semester";
		if ($where != '') {
			$sql	= "$sql where $where ";
		}
		if ($orderby != '') {
			$sql	= "$sql order by $orderby";
		}
		if ($doDebug) {
			echo "have set up the sql: $sql <br />";
		}
		$content	.= "SQL: $sql</p>";

		if ($doDebug) {
			echo "output type: $output_type<br />";
		}
		if ($output_type == 'table') {
			if ($doDebug) {
				echo "putting out table headers<br />";
			}
			$content .= "<table><tr>";
            if ($user_ID_checked == 'X') {
                $headerName = $nameConversionArray['user_ID'];
                $content .= "<th>$headerName</th>";
           }
            if ($user_call_sign_checked == 'X') {
                $headerName = $nameConversionArray['user_call_sign'];
                $content .= "<th>$headerName</th>";
                if ($doDebug) {
                	echo "<br />user_call_sign_checked was checked.<br /><br />";
                }
           } else {
           		if ($doDebug) {
           			echo "user_call_sign_checked was not 'X'<br />";
           		}
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
            if ($advisor_id_checked == 'X') {
                $headerName = $nameConversionArray['advisor_id'];
                $content .= "<th>$headerName</th>";
           }
            if ($advisor_call_sign_checked == 'X') {
                $headerName = $nameConversionArray['advisor_call_sign'];
                $content .= "<th>$headerName</th>";
           }
            if ($advisor_semester_checked == 'X') {
                $headerName = $nameConversionArray['advisor_semester'];
                $content .= "<th>$headerName</th>";
           }
            if ($advisor_welcome_email_date_checked == 'X') {
                $headerName = $nameConversionArray['advisor_welcome_email_date'];
                $content .= "<th>$headerName</th>";
           }
            if ($advisor_verify_email_date_checked == 'X') {
                $headerName = $nameConversionArray['advisor_verify_email_date'];
                $content .= "<th>$headerName</th>";
           }
            if ($advisor_verify_email_number_checked == 'X') {
                $headerName = $nameConversionArray['advisor_verify_email_number'];
                $content .= "<th>$headerName</th>";
           }
            if ($advisor_verify_response_checked == 'X') {
                $headerName = $nameConversionArray['advisor_verify_response'];
                $content .= "<th>$headerName</th>";
           }
            if ($advisor_action_log_checked == 'X') {
                $headerName = $nameConversionArray['advisor_action_log'];
                $content .= "<th>$headerName</th>";
           }
            if ($advisor_class_verified_checked == 'X') {
                $headerName = $nameConversionArray['advisor_class_verified'];
                $content .= "<th>$headerName</th>";
           }
            if ($advisor_control_code_checked == 'X') {
                $headerName = $nameConversionArray['advisor_control_code'];
                $content .= "<th>$headerName</th>";
           }
            if ($advisor_date_created_checked == 'X') {
                $headerName = $nameConversionArray['advisor_date_created'];
                $content .= "<th>$headerName</th>";
           }
            if ($advisor_date_updated_checked == 'X') {
                $headerName = $nameConversionArray['advisor_date_updated'];
                $content .= "<th>$headerName</th>";
           }
            if ($advisor_replacement_status_checked == 'X') {
                $headerName = $nameConversionArray['advisor_replacement_status'];
                $content .= "<th>$headerName</th>";
           }
            if ($advisorclass_id_checked == 'X') {
                $headerName = $nameConversionArray['advisorclass_id'];
                $content .= "<th>$headerName</th>";
           }
            if ($advisorclass_call_sign_checked == 'X') {
                $headerName = $nameConversionArray['advisorclass_call_sign'];
                $content .= "<th>$headerName</th>";
           }
            if ($advisorclass_sequence_checked == 'X') {
                $headerName = $nameConversionArray['advisorclass_sequence'];
                $content .= "<th>$headerName</th>";
           }
            if ($advisorclass_semester_checked == 'X') {
                $headerName = $nameConversionArray['advisorclass_semester'];
                $content .= "<th>$headerName</th>";
           }
            if ($advisorclass_timezone_offset_checked == 'X') {
                $headerName = $nameConversionArray['advisorclass_timezone_offset'];
                $content .= "<th>$headerName</th>";
           }
            if ($advisorclass_level_checked == 'X') {
                $headerName = $nameConversionArray['advisorclass_level'];
                $content .= "<th>$headerName</th>";
           }
            if ($advisorclass_language_checked == 'X') {
                $headerName = $nameConversionArray['advisorclass_language'];
                $content .= "<th>$headerName</th>";
           }
            if ($advisorclass_class_size_checked == 'X') {
                $headerName = $nameConversionArray['advisorclass_class_size'];
                $content .= "<th>$headerName</th>";
           }
            if ($advisorclass_class_schedule_days_checked == 'X') {
                $headerName = $nameConversionArray['advisorclass_class_schedule_days'];
                $content .= "<th>$headerName</th>";
           }
            if ($advisorclass_class_schedule_times_checked == 'X') {
                $headerName = $nameConversionArray['advisorclass_class_schedule_times'];
                $content .= "<th>$headerName</th>";
           }
            if ($advisorclass_class_schedule_days_utc_checked == 'X') {
                $headerName = $nameConversionArray['advisorclass_class_schedule_days_utc'];
                $content .= "<th>$headerName</th>";
           }
            if ($advisorclass_class_schedule_times_utc_checked == 'X') {
                $headerName = $nameConversionArray['advisorclass_class_schedule_times_utc'];
                $content .= "<th>$headerName</th>";
           }
            if ($advisorclass_action_log_checked == 'X') {
                $headerName = $nameConversionArray['advisorclass_action_log'];
                $content .= "<th>$headerName</th>";
           }
            if ($advisorclass_class_incomplete_checked == 'X') {
                $headerName = $nameConversionArray['advisorclass_class_incomplete'];
                $content .= "<th>$headerName</th>";
           }
            if ($advisorclass_date_created_checked == 'X') {
                $headerName = $nameConversionArray['advisorclass_date_created'];
                $content .= "<th>$headerName</th>";
           }
            if ($advisorclass_date_updated_checked == 'X') {
                $headerName = $nameConversionArray['advisorclass_date_updated'];
                $content .= "<th>$headerName</th>";
           }
            if ($advisorclass_student01_checked == 'X') {
                $headerName = $nameConversionArray['advisorclass_student01'];
                $content .= "<th>$headerName</th>";
           }
            if ($advisorclass_student02_checked == 'X') {
                $headerName = $nameConversionArray['advisorclass_student02'];
                $content .= "<th>$headerName</th>";
           }
            if ($advisorclass_student03_checked == 'X') {
                $headerName = $nameConversionArray['advisorclass_student03'];
                $content .= "<th>$headerName</th>";
           }
            if ($advisorclass_student04_checked == 'X') {
                $headerName = $nameConversionArray['advisorclass_student04'];
                $content .= "<th>$headerName</th>";
           }
            if ($advisorclass_student05_checked == 'X') {
                $headerName = $nameConversionArray['advisorclass_student05'];
                $content .= "<th>$headerName</th>";
           }
            if ($advisorclass_student06_checked == 'X') {
                $headerName = $nameConversionArray['advisorclass_student06'];
                $content .= "<th>$headerName</th>";
           }
            if ($advisorclass_student07_checked == 'X') {
                $headerName = $nameConversionArray['advisorclass_student07'];
                $content .= "<th>$headerName</th>";
           }
            if ($advisorclass_student08_checked == 'X') {
                $headerName = $nameConversionArray['advisorclass_student08'];
                $content .= "<th>$headerName</th>";
           }
            if ($advisorclass_student09_checked == 'X') {
                $headerName = $nameConversionArray['advisorclass_student09'];
                $content .= "<th>$headerName</th>";
           }
            if ($advisorclass_student10_checked == 'X') {
                $headerName = $nameConversionArray['advisorclass_student10'];
                $content .= "<th>$headerName</th>";
           }
            if ($advisorclass_student11_checked == 'X') {
                $headerName = $nameConversionArray['advisorclass_student11'];
                $content .= "<th>$headerName</th>";
           }
            if ($advisorclass_student12_checked == 'X') {
                $headerName = $nameConversionArray['advisorclass_student12'];
                $content .= "<th>$headerName</th>";
           }
            if ($advisorclass_student13_checked == 'X') {
                $headerName = $nameConversionArray['advisorclass_student13'];
                $content .= "<th>$headerName</th>";
           }
            if ($advisorclass_student14_checked == 'X') {
                $headerName = $nameConversionArray['advisorclass_student14'];
                $content .= "<th>$headerName</th>";
           }
            if ($advisorclass_student15_checked == 'X') {
                $headerName = $nameConversionArray['advisorclass_student15'];
                $content .= "<th>$headerName</th>";
           }
            if ($advisorclass_student16_checked == 'X') {
                $headerName = $nameConversionArray['advisorclass_student16'];
                $content .= "<th>$headerName</th>";
           }
            if ($advisorclass_student17_checked == 'X') {
                $headerName = $nameConversionArray['advisorclass_student17'];
                $content .= "<th>$headerName</th>";
           }
            if ($advisorclass_student18_checked == 'X') {
                $headerName = $nameConversionArray['advisorclass_student18'];
                $content .= "<th>$headerName</th>";
           }
            if ($advisorclass_student19_checked == 'X') {
                $headerName = $nameConversionArray['advisorclass_student19'];
                $content .= "<th>$headerName</th>";
           }
            if ($advisorclass_student20_checked == 'X') {
                $headerName = $nameConversionArray['advisorclass_student20'];
                $content .= "<th>$headerName</th>";
           }
            if ($advisorclass_student21_checked == 'X') {
                $headerName = $nameConversionArray['advisorclass_student21'];
                $content .= "<th>$headerName</th>";
           }
            if ($advisorclass_student22_checked == 'X') {
                $headerName = $nameConversionArray['advisorclass_student22'];
                $content .= "<th>$headerName</th>";
           }
            if ($advisorclass_student23_checked == 'X') {
                $headerName = $nameConversionArray['advisorclass_student23'];
                $content .= "<th>$headerName</th>";
           }
            if ($advisorclass_student24_checked == 'X') {
                $headerName = $nameConversionArray['advisorclass_student24'];
                $content .= "<th>$headerName</th>";
           }
            if ($advisorclass_student25_checked == 'X') {
                $headerName = $nameConversionArray['advisorclass_student25'];
                $content .= "<th>$headerName</th>";
           }
            if ($advisorclass_student26_checked == 'X') {
                $headerName = $nameConversionArray['advisorclass_student26'];
                $content .= "<th>$headerName</th>";
           }
            if ($advisorclass_student27_checked == 'X') {
                $headerName = $nameConversionArray['advisorclass_student27'];
                $content .= "<th>$headerName</th>";
           }
            if ($advisorclass_student28_checked == 'X') {
                $headerName = $nameConversionArray['advisorclass_student28'];
                $content .= "<th>$headerName</th>";
           }
            if ($advisorclass_student29_checked == 'X') {
                $headerName = $nameConversionArray['advisorclass_student29'];
                $content .= "<th>$headerName</th>";
           }
            if ($advisorclass_student30_checked == 'X') {
                $headerName = $nameConversionArray['advisorclass_student30'];
                $content .= "<th>$headerName</th>";
           }
            if ($advisorclass_number_students_checked == 'X') {
                $headerName = $nameConversionArray['advisorclass_number_students'];
                $content .= "<th>$headerName</th>";
           }
            if ($advisorclass_evaluation_complete_checked == 'X') {
                $headerName = $nameConversionArray['advisorclass_evaluation_complete'];
                $content .= "<th>$headerName</th>";
           }
            if ($advisorclass_class_comments_checked == 'X') {
                $headerName = $nameConversionArray['advisorclass_class_comments'];
                $content .= "<th>$headerName</th>";
           }
            if ($advisorclass_copy_control_checked == 'X') {
                $headerName = $nameConversionArray['advisorclass_copy_control'];
                $content .= "<th>$headerName</th>";
           }
			$content	.= "</tr>";	
		} else {
			if ($doDebug) {
				echo "putting out the csv headers<br />";
			}
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
            if ($advisor_id_checked == 'X') {
                if ($needComma) {
                    $content .= '	';
                }
                $headerName = $nameConversionArray['advisor_id'];
                $headerName = str_replace('<br />','_',$headerName);
                $content .= $headerName;
                $needComma = TRUE;
            }
            if ($advisor_call_sign_checked == 'X') {
                if ($needComma) {
                    $content .= '	';
                }
                $headerName = $nameConversionArray['advisor_call_sign'];
                $headerName = str_replace('<br />','_',$headerName);
                $content .= $headerName;
                $needComma = TRUE;
            }
            if ($advisor_semester_checked == 'X') {
                if ($needComma) {
                    $content .= '	';
                }
                $headerName = $nameConversionArray['advisor_semester'];
                $headerName = str_replace('<br />','_',$headerName);
                $content .= $headerName;
                $needComma = TRUE;
            }
            if ($advisor_welcome_email_date_checked == 'X') {
                if ($needComma) {
                    $content .= '	';
                }
                $headerName = $nameConversionArray['advisor_welcome_email_date'];
                $headerName = str_replace('<br />','_',$headerName);
                $content .= $headerName;
                $needComma = TRUE;
            }
            if ($advisor_verify_email_date_checked == 'X') {
                if ($needComma) {
                    $content .= '	';
                }
                $headerName = $nameConversionArray['advisor_verify_email_date'];
                $headerName = str_replace('<br />','_',$headerName);
                $content .= $headerName;
                $needComma = TRUE;
            }
            if ($advisor_verify_email_number_checked == 'X') {
                if ($needComma) {
                    $content .= '	';
                }
                $headerName = $nameConversionArray['advisor_verify_email_number'];
                $headerName = str_replace('<br />','_',$headerName);
                $content .= $headerName;
                $needComma = TRUE;
            }
            if ($advisor_verify_response_checked == 'X') {
                if ($needComma) {
                    $content .= '	';
                }
                $headerName = $nameConversionArray['advisor_verify_response'];
                $headerName = str_replace('<br />','_',$headerName);
                $content .= $headerName;
                $needComma = TRUE;
            }
            if ($advisor_action_log_checked == 'X') {
                if ($needComma) {
                    $content .= '	';
                }
                $headerName = $nameConversionArray['advisor_action_log'];
                $headerName = str_replace('<br />','_',$headerName);
                $content .= $headerName;
                $needComma = TRUE;
            }
            if ($advisor_class_verified_checked == 'X') {
                if ($needComma) {
                    $content .= '	';
                }
                $headerName = $nameConversionArray['advisor_class_verified'];
                $headerName = str_replace('<br />','_',$headerName);
                $content .= $headerName;
                $needComma = TRUE;
            }
            if ($advisor_control_code_checked == 'X') {
                if ($needComma) {
                    $content .= '	';
                }
                $headerName = $nameConversionArray['advisor_control_code'];
                $headerName = str_replace('<br />','_',$headerName);
                $content .= $headerName;
                $needComma = TRUE;
            }
            if ($advisor_date_created_checked == 'X') {
                if ($needComma) {
                    $content .= '	';
                }
                $headerName = $nameConversionArray['advisor_date_created'];
                $headerName = str_replace('<br />','_',$headerName);
                $content .= $headerName;
                $needComma = TRUE;
            }
            if ($advisor_date_updated_checked == 'X') {
                if ($needComma) {
                    $content .= '	';
                }
                $headerName = $nameConversionArray['advisor_date_updated'];
                $headerName = str_replace('<br />','_',$headerName);
                $content .= $headerName;
                $needComma = TRUE;
            }
            if ($advisor_replacement_status_checked == 'X') {
                if ($needComma) {
                    $content .= '	';
                }
                $headerName = $nameConversionArray['advisor_replacement_status'];
                $headerName = str_replace('<br />','_',$headerName);
                $content .= $headerName;
                $needComma = TRUE;
            }
            if ($advisorclass_id_checked == 'X') {
                if ($needComma) {
                    $content .= '	';
                }
                $headerName = $nameConversionArray['advisorclass_id'];
                $headerName = str_replace('<br />','_',$headerName);
                $content .= $headerName;
                $needComma = TRUE;
            }
            if ($advisorclass_call_sign_checked == 'X') {
                if ($needComma) {
                    $content .= '	';
                }
                $headerName = $nameConversionArray['advisorclass_call_sign'];
                $headerName = str_replace('<br />','_',$headerName);
                $content .= $headerName;
                $needComma = TRUE;
            }
            if ($advisorclass_sequence_checked == 'X') {
                if ($needComma) {
                    $content .= '	';
                }
                $headerName = $nameConversionArray['advisorclass_sequence'];
                $headerName = str_replace('<br />','_',$headerName);
                $content .= $headerName;
                $needComma = TRUE;
            }
            if ($advisorclass_semester_checked == 'X') {
                if ($needComma) {
                    $content .= '	';
                }
                $headerName = $nameConversionArray['advisorclass_semester'];
                $headerName = str_replace('<br />','_',$headerName);
                $content .= $headerName;
                $needComma = TRUE;
            }
            if ($advisorclass_timezone_offset_checked == 'X') {
                if ($needComma) {
                    $content .= '	';
                }
                $headerName = $nameConversionArray['advisorclass_timezone_offset'];
                $headerName = str_replace('<br />','_',$headerName);
                $content .= $headerName;
                $needComma = TRUE;
            }
            if ($advisorclass_level_checked == 'X') {
                if ($needComma) {
                    $content .= '	';
                }
                $headerName = $nameConversionArray['advisorclass_level'];
                $headerName = str_replace('<br />','_',$headerName);
                $content .= $headerName;
                $needComma = TRUE;
            }
            if ($advisorclass_language_checked == 'X') {
                if ($needComma) {
                    $content .= '	';
                }
                $headerName = $nameConversionArray['advisorclass_language'];
                $headerName = str_replace('<br />','_',$headerName);
                $content .= $headerName;
                $needComma = TRUE;
            }
            if ($advisorclass_class_size_checked == 'X') {
                if ($needComma) {
                    $content .= '	';
                }
                $headerName = $nameConversionArray['advisorclass_class_size'];
                $headerName = str_replace('<br />','_',$headerName);
                $content .= $headerName;
                $needComma = TRUE;
            }
            if ($advisorclass_class_schedule_days_checked == 'X') {
                if ($needComma) {
                    $content .= '	';
                }
                $headerName = $nameConversionArray['advisorclass_class_schedule_days'];
                $headerName = str_replace('<br />','_',$headerName);
                $content .= $headerName;
                $needComma = TRUE;
            }
            if ($advisorclass_class_schedule_times_checked == 'X') {
                if ($needComma) {
                    $content .= '	';
                }
                $headerName = $nameConversionArray['advisorclass_class_schedule_times'];
                $headerName = str_replace('<br />','_',$headerName);
                $content .= $headerName;
                $needComma = TRUE;
            }
            if ($advisorclass_class_schedule_days_utc_checked == 'X') {
                if ($needComma) {
                    $content .= '	';
                }
                $headerName = $nameConversionArray['advisorclass_class_schedule_days_utc'];
                $headerName = str_replace('<br />','_',$headerName);
                $content .= $headerName;
                $needComma = TRUE;
            }
            if ($advisorclass_class_schedule_times_utc_checked == 'X') {
                if ($needComma) {
                    $content .= '	';
                }
                $headerName = $nameConversionArray['advisorclass_class_schedule_times_utc'];
                $headerName = str_replace('<br />','_',$headerName);
                $content .= $headerName;
                $needComma = TRUE;
            }
            if ($advisorclass_action_log_checked == 'X') {
                if ($needComma) {
                    $content .= '	';
                }
                $headerName = $nameConversionArray['advisorclass_action_log'];
                $headerName = str_replace('<br />','_',$headerName);
                $content .= $headerName;
                $needComma = TRUE;
            }
            if ($advisorclass_class_incomplete_checked == 'X') {
                if ($needComma) {
                    $content .= '	';
                }
                $headerName = $nameConversionArray['advisorclass_class_incomplete'];
                $headerName = str_replace('<br />','_',$headerName);
                $content .= $headerName;
                $needComma = TRUE;
            }
            if ($advisorclass_date_created_checked == 'X') {
                if ($needComma) {
                    $content .= '	';
                }
                $headerName = $nameConversionArray['advisorclass_date_created'];
                $headerName = str_replace('<br />','_',$headerName);
                $content .= $headerName;
                $needComma = TRUE;
            }
            if ($advisorclass_date_updated_checked == 'X') {
                if ($needComma) {
                    $content .= '	';
                }
                $headerName = $nameConversionArray['advisorclass_date_updated'];
                $headerName = str_replace('<br />','_',$headerName);
                $content .= $headerName;
                $needComma = TRUE;
            }
            if ($advisorclass_student01_checked == 'X') {
                if ($needComma) {
                    $content .= '	';
                }
                $headerName = $nameConversionArray['advisorclass_student01'];
                $headerName = str_replace('<br />','_',$headerName);
                $content .= $headerName;
                $needComma = TRUE;
            }
            if ($advisorclass_student02_checked == 'X') {
                if ($needComma) {
                    $content .= '	';
                }
                $headerName = $nameConversionArray['advisorclass_student02'];
                $headerName = str_replace('<br />','_',$headerName);
                $content .= $headerName;
                $needComma = TRUE;
            }
            if ($advisorclass_student03_checked == 'X') {
                if ($needComma) {
                    $content .= '	';
                }
                $headerName = $nameConversionArray['advisorclass_student03'];
                $headerName = str_replace('<br />','_',$headerName);
                $content .= $headerName;
                $needComma = TRUE;
            }
            if ($advisorclass_student04_checked == 'X') {
                if ($needComma) {
                    $content .= '	';
                }
                $headerName = $nameConversionArray['advisorclass_student04'];
                $headerName = str_replace('<br />','_',$headerName);
                $content .= $headerName;
                $needComma = TRUE;
            }
            if ($advisorclass_student05_checked == 'X') {
                if ($needComma) {
                    $content .= '	';
                }
                $headerName = $nameConversionArray['advisorclass_student05'];
                $headerName = str_replace('<br />','_',$headerName);
                $content .= $headerName;
                $needComma = TRUE;
            }
            if ($advisorclass_student06_checked == 'X') {
                if ($needComma) {
                    $content .= '	';
                }
                $headerName = $nameConversionArray['advisorclass_student06'];
                $headerName = str_replace('<br />','_',$headerName);
                $content .= $headerName;
                $needComma = TRUE;
            }
            if ($advisorclass_student07_checked == 'X') {
                if ($needComma) {
                    $content .= '	';
                }
                $headerName = $nameConversionArray['advisorclass_student07'];
                $headerName = str_replace('<br />','_',$headerName);
                $content .= $headerName;
                $needComma = TRUE;
            }
            if ($advisorclass_student08_checked == 'X') {
                if ($needComma) {
                    $content .= '	';
                }
                $headerName = $nameConversionArray['advisorclass_student08'];
                $headerName = str_replace('<br />','_',$headerName);
                $content .= $headerName;
                $needComma = TRUE;
            }
            if ($advisorclass_student09_checked == 'X') {
                if ($needComma) {
                    $content .= '	';
                }
                $headerName = $nameConversionArray['advisorclass_student09'];
                $headerName = str_replace('<br />','_',$headerName);
                $content .= $headerName;
                $needComma = TRUE;
            }
            if ($advisorclass_student10_checked == 'X') {
                if ($needComma) {
                    $content .= '	';
                }
                $headerName = $nameConversionArray['advisorclass_student10'];
                $headerName = str_replace('<br />','_',$headerName);
                $content .= $headerName;
                $needComma = TRUE;
            }
            if ($advisorclass_student11_checked == 'X') {
                if ($needComma) {
                    $content .= '	';
                }
                $headerName = $nameConversionArray['advisorclass_student11'];
                $headerName = str_replace('<br />','_',$headerName);
                $content .= $headerName;
                $needComma = TRUE;
            }
            if ($advisorclass_student12_checked == 'X') {
                if ($needComma) {
                    $content .= '	';
                }
                $headerName = $nameConversionArray['advisorclass_student12'];
                $headerName = str_replace('<br />','_',$headerName);
                $content .= $headerName;
                $needComma = TRUE;
            }
            if ($advisorclass_student13_checked == 'X') {
                if ($needComma) {
                    $content .= '	';
                }
                $headerName = $nameConversionArray['advisorclass_student13'];
                $headerName = str_replace('<br />','_',$headerName);
                $content .= $headerName;
                $needComma = TRUE;
            }
            if ($advisorclass_student14_checked == 'X') {
                if ($needComma) {
                    $content .= '	';
                }
                $headerName = $nameConversionArray['advisorclass_student14'];
                $headerName = str_replace('<br />','_',$headerName);
                $content .= $headerName;
                $needComma = TRUE;
            }
            if ($advisorclass_student15_checked == 'X') {
                if ($needComma) {
                    $content .= '	';
                }
                $headerName = $nameConversionArray['advisorclass_student15'];
                $headerName = str_replace('<br />','_',$headerName);
                $content .= $headerName;
                $needComma = TRUE;
            }
            if ($advisorclass_student16_checked == 'X') {
                if ($needComma) {
                    $content .= '	';
                }
                $headerName = $nameConversionArray['advisorclass_student16'];
                $headerName = str_replace('<br />','_',$headerName);
                $content .= $headerName;
                $needComma = TRUE;
            }
            if ($advisorclass_student17_checked == 'X') {
                if ($needComma) {
                    $content .= '	';
                }
                $headerName = $nameConversionArray['advisorclass_student17'];
                $headerName = str_replace('<br />','_',$headerName);
                $content .= $headerName;
                $needComma = TRUE;
            }
            if ($advisorclass_student18_checked == 'X') {
                if ($needComma) {
                    $content .= '	';
                }
                $headerName = $nameConversionArray['advisorclass_student18'];
                $headerName = str_replace('<br />','_',$headerName);
                $content .= $headerName;
                $needComma = TRUE;
            }
            if ($advisorclass_student19_checked == 'X') {
                if ($needComma) {
                    $content .= '	';
                }
                $headerName = $nameConversionArray['advisorclass_student19'];
                $headerName = str_replace('<br />','_',$headerName);
                $content .= $headerName;
                $needComma = TRUE;
            }
            if ($advisorclass_student20_checked == 'X') {
                if ($needComma) {
                    $content .= '	';
                }
                $headerName = $nameConversionArray['advisorclass_student20'];
                $headerName = str_replace('<br />','_',$headerName);
                $content .= $headerName;
                $needComma = TRUE;
            }
            if ($advisorclass_student21_checked == 'X') {
                if ($needComma) {
                    $content .= '	';
                }
                $headerName = $nameConversionArray['advisorclass_student21'];
                $headerName = str_replace('<br />','_',$headerName);
                $content .= $headerName;
                $needComma = TRUE;
            }
            if ($advisorclass_student22_checked == 'X') {
                if ($needComma) {
                    $content .= '	';
                }
                $headerName = $nameConversionArray['advisorclass_student22'];
                $headerName = str_replace('<br />','_',$headerName);
                $content .= $headerName;
                $needComma = TRUE;
            }
            if ($advisorclass_student23_checked == 'X') {
                if ($needComma) {
                    $content .= '	';
                }
                $headerName = $nameConversionArray['advisorclass_student23'];
                $headerName = str_replace('<br />','_',$headerName);
                $content .= $headerName;
                $needComma = TRUE;
            }
            if ($advisorclass_student24_checked == 'X') {
                if ($needComma) {
                    $content .= '	';
                }
                $headerName = $nameConversionArray['advisorclass_student24'];
                $headerName = str_replace('<br />','_',$headerName);
                $content .= $headerName;
                $needComma = TRUE;
            }
            if ($advisorclass_student25_checked == 'X') {
                if ($needComma) {
                    $content .= '	';
                }
                $headerName = $nameConversionArray['advisorclass_student25'];
                $headerName = str_replace('<br />','_',$headerName);
                $content .= $headerName;
                $needComma = TRUE;
            }
            if ($advisorclass_student26_checked == 'X') {
                if ($needComma) {
                    $content .= '	';
                }
                $headerName = $nameConversionArray['advisorclass_student26'];
                $headerName = str_replace('<br />','_',$headerName);
                $content .= $headerName;
                $needComma = TRUE;
            }
            if ($advisorclass_student27_checked == 'X') {
                if ($needComma) {
                    $content .= '	';
                }
                $headerName = $nameConversionArray['advisorclass_student27'];
                $headerName = str_replace('<br />','_',$headerName);
                $content .= $headerName;
                $needComma = TRUE;
            }
            if ($advisorclass_student28_checked == 'X') {
                if ($needComma) {
                    $content .= '	';
                }
                $headerName = $nameConversionArray['advisorclass_student28'];
                $headerName = str_replace('<br />','_',$headerName);
                $content .= $headerName;
                $needComma = TRUE;
            }
            if ($advisorclass_student29_checked == 'X') {
                if ($needComma) {
                    $content .= '	';
                }
                $headerName = $nameConversionArray['advisorclass_student29'];
                $headerName = str_replace('<br />','_',$headerName);
                $content .= $headerName;
                $needComma = TRUE;
            }
            if ($advisorclass_student30_checked == 'X') {
                if ($needComma) {
                    $content .= '	';
                }
                $headerName = $nameConversionArray['advisorclass_student30'];
                $headerName = str_replace('<br />','_',$headerName);
                $content .= $headerName;
                $needComma = TRUE;
            }
            if ($advisorclass_number_students_checked == 'X') {
                if ($needComma) {
                    $content .= '	';
                }
                $headerName = $nameConversionArray['advisorclass_number_students'];
                $headerName = str_replace('<br />','_',$headerName);
                $content .= $headerName;
                $needComma = TRUE;
            }
            if ($advisorclass_evaluation_complete_checked == 'X') {
                if ($needComma) {
                    $content .= '	';
                }
                $headerName = $nameConversionArray['advisorclass_evaluation_complete'];
                $headerName = str_replace('<br />','_',$headerName);
                $content .= $headerName;
                $needComma = TRUE;
            }
            if ($advisorclass_class_comments_checked == 'X') {
                if ($needComma) {
                    $content .= '	';
                }
                $headerName = $nameConversionArray['advisorclass_class_comments'];
                $headerName = str_replace('<br />','_',$headerName);
                $content .= $headerName;
                $needComma = TRUE;
            }
            if ($advisorclass_copy_control_checked == 'X') {
                if ($needComma) {
                    $content .= '	';
                }
                $headerName = $nameConversionArray['advisorclass_copy_control'];
                $headerName = str_replace('<br />','_',$headerName);
                $content .= $headerName;
                $needComma = TRUE;
            }
			$content	.= "\n";
		}
		$myCount					= 0;
		$wpw1_cwa_advisorclass		= $wpdb->get_results($sql);
		if ($wpw1_cwa_advisorclass === FALSE) {
			handleWPDBError($jobname,$doDebug);
		} else {
			$numACRows				= $wpdb->num_rows;
			if ($doDebug) {
				echo "ran $sql<br />and found $numACRows rows<br />";
			}
			if ($numACRows > 0) {
				foreach ($wpw1_cwa_advisorclass as $advisorClassRow) {
					$user_ID								= $advisorClassRow->user_ID;
					$user_call_sign							= $advisorClassRow->user_call_sign;
					$user_first_name						= $advisorClassRow->user_first_name;
					$user_last_name							= $advisorClassRow->user_last_name;
					$user_email								= $advisorClassRow->user_email;
					$user_ph_code							= $advisorClassRow->user_ph_code;
					$user_phone								= $advisorClassRow->user_phone;
					$user_city								= $advisorClassRow->user_city;
					$user_state								= $advisorClassRow->user_state;
					$user_zip_code							= $advisorClassRow->user_zip_code;
					$user_country_code						= $advisorClassRow->user_country_code;
					$user_country							= $advisorClassRow->user_country;
					$user_whatsapp							= $advisorClassRow->user_whatsapp;
					$user_telegram							= $advisorClassRow->user_telegram;
					$user_signal							= $advisorClassRow->user_signal;
					$user_messenger							= $advisorClassRow->user_messenger;
					$user_action_log						= $advisorClassRow->user_action_log;
					$user_timezone_id						= $advisorClassRow->user_timezone_id;
					$user_languages							= $advisorClassRow->user_languages;
					$user_survey_score						= $advisorClassRow->user_survey_score;
					$user_is_admin							= $advisorClassRow->user_is_admin;
					$user_role								= $advisorClassRow->user_role;
					$user_date_created						= $advisorClassRow->user_date_created;
					$user_date_updated						= $advisorClassRow->user_date_updated;
					$advisor_id								= $advisorClassRow->advisor_id;
					$advisor_call_sign						= $advisorClassRow->advisor_call_sign;
					$advisor_semester						= $advisorClassRow->advisor_semester;
					$advisor_welcome_email_date				= $advisorClassRow->advisor_welcome_email_date;
					$advisor_verify_email_date				= $advisorClassRow->advisor_verify_email_date;
					$advisor_verify_email_number			= $advisorClassRow->advisor_verify_email_number;
					$advisor_verify_response				= $advisorClassRow->advisor_verify_response;
					$advisor_action_log						= $advisorClassRow->advisor_action_log;
					$advisor_class_verified					= $advisorClassRow->advisor_class_verified;
					$advisor_control_code					= $advisorClassRow->advisor_control_code;
					$advisor_date_created					= $advisorClassRow->advisor_date_created;
					$advisor_date_updated					= $advisorClassRow->advisor_date_updated;
					$advisor_replacement_status				= $advisorClassRow->advisor_replacement_status;
					$advisorclass_id						= $advisorClassRow->advisorclass_id;
					$advisorclass_call_sign					= $advisorClassRow->advisorclass_call_sign;
					$advisorclass_sequence					= $advisorClassRow->advisorclass_sequence;
					$advisorclass_semester					= $advisorClassRow->advisorclass_semester;
					$advisorclass_timezone_offset			= $advisorClassRow->advisorclass_timezone_offset;
					$advisorclass_level						= $advisorClassRow->advisorclass_level;
					$advisorclass_language					= $advisorClassRow->advisorclass_language;
					$advisorclass_class_size				= $advisorClassRow->advisorclass_class_size;
					$advisorclass_class_schedule_days		= $advisorClassRow->advisorclass_class_schedule_days;
					$advisorclass_class_schedule_times		= $advisorClassRow->advisorclass_class_schedule_times;
					$advisorclass_class_schedule_days_utc	= $advisorClassRow->advisorclass_class_schedule_days_utc;
					$advisorclass_class_schedule_times_utc	= $advisorClassRow->advisorclass_class_schedule_times_utc;
					$advisorclass_action_log				= $advisorClassRow->advisorclass_action_log;
					$advisorclass_class_incomplete			= $advisorClassRow->advisorclass_class_incomplete;
					$advisorclass_date_created				= $advisorClassRow->advisorclass_date_created;
					$advisorclass_date_updated				= $advisorClassRow->advisorclass_date_updated;
					$advisorclass_student01					= $advisorClassRow->advisorclass_student01;
					$advisorclass_student02					= $advisorClassRow->advisorclass_student02;
					$advisorclass_student03					= $advisorClassRow->advisorclass_student03;
					$advisorclass_student04					= $advisorClassRow->advisorclass_student04;
					$advisorclass_student05					= $advisorClassRow->advisorclass_student05;
					$advisorclass_student06					= $advisorClassRow->advisorclass_student06;
					$advisorclass_student07					= $advisorClassRow->advisorclass_student07;
					$advisorclass_student08					= $advisorClassRow->advisorclass_student08;
					$advisorclass_student09					= $advisorClassRow->advisorclass_student09;
					$advisorclass_student10					= $advisorClassRow->advisorclass_student10;
					$advisorclass_student11					= $advisorClassRow->advisorclass_student11;
					$advisorclass_student12					= $advisorClassRow->advisorclass_student12;
					$advisorclass_student13					= $advisorClassRow->advisorclass_student13;
					$advisorclass_student14					= $advisorClassRow->advisorclass_student14;
					$advisorclass_student15					= $advisorClassRow->advisorclass_student15;
					$advisorclass_student16					= $advisorClassRow->advisorclass_student16;
					$advisorclass_student17					= $advisorClassRow->advisorclass_student17;
					$advisorclass_student18					= $advisorClassRow->advisorclass_student18;
					$advisorclass_student19					= $advisorClassRow->advisorclass_student19;
					$advisorclass_student20					= $advisorClassRow->advisorclass_student20;
					$advisorclass_student21					= $advisorClassRow->advisorclass_student21;
					$advisorclass_student22					= $advisorClassRow->advisorclass_student22;
					$advisorclass_student23					= $advisorClassRow->advisorclass_student23;
					$advisorclass_student24					= $advisorClassRow->advisorclass_student24;
					$advisorclass_student25					= $advisorClassRow->advisorclass_student25;
					$advisorclass_student26					= $advisorClassRow->advisorclass_student26;
					$advisorclass_student27					= $advisorClassRow->advisorclass_student27;
					$advisorclass_student28					= $advisorClassRow->advisorclass_student28;
					$advisorclass_student29					= $advisorClassRow->advisorclass_student29;
					$advisorclass_student30					= $advisorClassRow->advisorclass_student30;
					$advisorclass_number_students			= $advisorClassRow->advisorclass_number_students;
					$advisorclass_evaluation_complete		= $advisorClassRow->advisorclass_evaluation_complete;
					$advisorclass_class_comments			= $advisorClassRow->advisorclass_class_comments;
					$advisorclass_copy_control				= $advisorClassRow->advisorclass_copy_control;

					$myCount++;

					if ($doDebug) {
						echo "Processing $advisorclass_call_sign<br />\n";
					}
					if ($output_type == 'table') {
						$content	.= "<tr>";

                        if ($user_ID_checked == 'X') {
                            $content .= "<td style='vertical-align:top'>$user_ID</td>";
                         }
                        if ($user_call_sign_checked == 'X') {
                            $content .= "<td style='vertical-align:top'>$user_call_sign</td>";
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
                            $content .= "<td style='vertical-align:top'>$user_action_log</td>";
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
                        if ($advisor_id_checked == 'X') {
                            $content .= "<td style='vertical-align:top'>$advisor_id</td>";
                         }
                        if ($advisor_call_sign_checked == 'X') {
                            $content .= "<td style='vertical-align:top'>$advisor_call_sign</td>";
                         }
                        if ($advisor_semester_checked == 'X') {
                            $content .= "<td style='vertical-align:top'>$advisor_semester</td>";
                         }
                        if ($advisor_welcome_email_date_checked == 'X') {
                            $content .= "<td style='vertical-align:top'>$advisor_welcome_email_date</td>";
                         }
                        if ($advisor_verify_email_date_checked == 'X') {
                            $content .= "<td style='vertical-align:top'>$advisor_verify_email_date</td>";
                         }
                        if ($advisor_verify_email_number_checked == 'X') {
                            $content .= "<td style='vertical-align:top'>$advisor_verify_email_number</td>";
                         }
                        if ($advisor_verify_response_checked == 'X') {
                            $content .= "<td style='vertical-align:top'>$advisor_verify_response</td>";
                         }
                        if ($advisor_action_log_checked == 'X') {
                            $content .= "<td style='vertical-align:top'>$advisor_action_log</td>";
                         }
                        if ($advisor_class_verified_checked == 'X') {
                            $content .= "<td style='vertical-align:top'>$advisor_class_verified</td>";
                         }
                        if ($advisor_control_code_checked == 'X') {
                            $content .= "<td style='vertical-align:top'>$advisor_control_code</td>";
                         }
                        if ($advisor_date_created_checked == 'X') {
                            $content .= "<td style='vertical-align:top'>$advisor_date_created</td>";
                         }
                        if ($advisor_date_updated_checked == 'X') {
                            $content .= "<td style='vertical-align:top'>$advisor_date_updated</td>";
                         }
                        if ($advisor_replacement_status_checked == 'X') {
                            $content .= "<td style='vertical-align:top'>$advisor_replacement_status</td>";
                         }
                        if ($advisorclass_id_checked == 'X') {
                            $content .= "<td style='vertical-align:top'>$advisorclass_id</td>";
                         }
                        if ($advisorclass_call_sign_checked == 'X') {
                            $content .= "<td style='vertical-align:top'>$advisorclass_call_sign</td>";
                         }
                        if ($advisorclass_sequence_checked == 'X') {
                            $content .= "<td style='vertical-align:top'>$advisorclass_sequence</td>";
                         }
                        if ($advisorclass_semester_checked == 'X') {
                            $content .= "<td style='vertical-align:top'>$advisorclass_semester</td>";
                         }
                        if ($advisorclass_timezone_offset_checked == 'X') {
                            $content .= "<td style='vertical-align:top'>$advisorclass_timezone_offset</td>";
                         }
                        if ($advisorclass_level_checked == 'X') {
                            $content .= "<td style='vertical-align:top'>$advisorclass_level</td>";
                         }
                        if ($advisorclass_language_checked == 'X') {
                            $content .= "<td style='vertical-align:top'>$advisorclass_language</td>";
                         }
                        if ($advisorclass_class_size_checked == 'X') {
                            $content .= "<td style='vertical-align:top'>$advisorclass_class_size</td>";
                         }
                        if ($advisorclass_class_schedule_days_checked == 'X') {
                            $content .= "<td style='vertical-align:top'>$advisorclass_class_schedule_days</td>";
                         }
                        if ($advisorclass_class_schedule_times_checked == 'X') {
                            $content .= "<td style='vertical-align:top'>$advisorclass_class_schedule_times</td>";
                         }
                        if ($advisorclass_class_schedule_days_utc_checked == 'X') {
                            $content .= "<td style='vertical-align:top'>$advisorclass_class_schedule_days_utc</td>";
                         }
                        if ($advisorclass_class_schedule_times_utc_checked == 'X') {
                            $content .= "<td style='vertical-align:top'>$advisorclass_class_schedule_times_utc</td>";
                         }
                        if ($advisorclass_action_log_checked == 'X') {
                            $content .= "<td style='vertical-align:top'>$advisorclass_action_log</td>";
                         }
                        if ($advisorclass_class_incomplete_checked == 'X') {
                            $content .= "<td style='vertical-align:top'>$advisorclass_class_incomplete</td>";
                         }
                        if ($advisorclass_date_created_checked == 'X') {
                            $content .= "<td style='vertical-align:top'>$advisorclass_date_created</td>";
                         }
                        if ($advisorclass_date_updated_checked == 'X') {
                            $content .= "<td style='vertical-align:top'>$advisorclass_date_updated</td>";
                         }
                        if ($advisorclass_student01_checked == 'X') {
                            $content .= "<td style='vertical-align:top'>$advisorclass_student01</td>";
                         }
                        if ($advisorclass_student02_checked == 'X') {
                            $content .= "<td style='vertical-align:top'>$advisorclass_student02</td>";
                         }
                        if ($advisorclass_student03_checked == 'X') {
                            $content .= "<td style='vertical-align:top'>$advisorclass_student03</td>";
                         }
                        if ($advisorclass_student04_checked == 'X') {
                            $content .= "<td style='vertical-align:top'>$advisorclass_student04</td>";
                         }
                        if ($advisorclass_student05_checked == 'X') {
                            $content .= "<td style='vertical-align:top'>$advisorclass_student05</td>";
                         }
                        if ($advisorclass_student06_checked == 'X') {
                            $content .= "<td style='vertical-align:top'>$advisorclass_student06</td>";
                         }
                        if ($advisorclass_student07_checked == 'X') {
                            $content .= "<td style='vertical-align:top'>$advisorclass_student07</td>";
                         }
                        if ($advisorclass_student08_checked == 'X') {
                            $content .= "<td style='vertical-align:top'>$advisorclass_student08</td>";
                         }
                        if ($advisorclass_student09_checked == 'X') {
                            $content .= "<td style='vertical-align:top'>$advisorclass_student09</td>";
                         }
                        if ($advisorclass_student10_checked == 'X') {
                            $content .= "<td style='vertical-align:top'>$advisorclass_student10</td>";
                         }
                        if ($advisorclass_student11_checked == 'X') {
                            $content .= "<td style='vertical-align:top'>$advisorclass_student11</td>";
                         }
                        if ($advisorclass_student12_checked == 'X') {
                            $content .= "<td style='vertical-align:top'>$advisorclass_student12</td>";
                         }
                        if ($advisorclass_student13_checked == 'X') {
                            $content .= "<td style='vertical-align:top'>$advisorclass_student13</td>";
                         }
                        if ($advisorclass_student14_checked == 'X') {
                            $content .= "<td style='vertical-align:top'>$advisorclass_student14</td>";
                         }
                        if ($advisorclass_student15_checked == 'X') {
                            $content .= "<td style='vertical-align:top'>$advisorclass_student15</td>";
                         }
                        if ($advisorclass_student16_checked == 'X') {
                            $content .= "<td style='vertical-align:top'>$advisorclass_student16</td>";
                         }
                        if ($advisorclass_student17_checked == 'X') {
                            $content .= "<td style='vertical-align:top'>$advisorclass_student17</td>";
                         }
                        if ($advisorclass_student18_checked == 'X') {
                            $content .= "<td style='vertical-align:top'>$advisorclass_student18</td>";
                         }
                        if ($advisorclass_student19_checked == 'X') {
                            $content .= "<td style='vertical-align:top'>$advisorclass_student19</td>";
                         }
                        if ($advisorclass_student20_checked == 'X') {
                            $content .= "<td style='vertical-align:top'>$advisorclass_student20</td>";
                         }
                        if ($advisorclass_student21_checked == 'X') {
                            $content .= "<td style='vertical-align:top'>$advisorclass_student21</td>";
                         }
                        if ($advisorclass_student22_checked == 'X') {
                            $content .= "<td style='vertical-align:top'>$advisorclass_student22</td>";
                         }
                        if ($advisorclass_student23_checked == 'X') {
                            $content .= "<td style='vertical-align:top'>$advisorclass_student23</td>";
                         }
                        if ($advisorclass_student24_checked == 'X') {
                            $content .= "<td style='vertical-align:top'>$advisorclass_student24</td>";
                         }
                        if ($advisorclass_student25_checked == 'X') {
                            $content .= "<td style='vertical-align:top'>$advisorclass_student25</td>";
                         }
                        if ($advisorclass_student26_checked == 'X') {
                            $content .= "<td style='vertical-align:top'>$advisorclass_student26</td>";
                         }
                        if ($advisorclass_student27_checked == 'X') {
                            $content .= "<td style='vertical-align:top'>$advisorclass_student27</td>";
                         }
                        if ($advisorclass_student28_checked == 'X') {
                            $content .= "<td style='vertical-align:top'>$advisorclass_student28</td>";
                         }
                        if ($advisorclass_student29_checked == 'X') {
                            $content .= "<td style='vertical-align:top'>$advisorclass_student29</td>";
                         }
                        if ($advisorclass_student30_checked == 'X') {
                            $content .= "<td style='vertical-align:top'>$advisorclass_student30</td>";
                         }
                        if ($advisorclass_number_students_checked == 'X') {
                            $content .= "<td style='vertical-align:top'>$advisorclass_number_students</td>";
                         }
                        if ($advisorclass_evaluation_complete_checked == 'X') {
                            $content .= "<td style='vertical-align:top'>$advisorclass_evaluation_complete</td>";
                         }
                        if ($advisorclass_class_comments_checked == 'X') {
                            $content .= "<td style='vertical-align:top'>$advisorclass_class_comments</td>";
                         }
                        if ($advisorclass_copy_control_checked == 'X') {
                            $content .= "<td style='vertical-align:top'>$advisorclass_copy_control</td>";
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
                        if ($advisor_id_checked == 'X') {
                            if ($needComma) {
                                $content .= '	';
                            }
                            $content .= $advisor_id;
                            $needComma = TRUE;
                        }
                        if ($advisor_call_sign_checked == 'X') {
                            if ($needComma) {
                                $content .= '	';
                            }
                            $content .= $advisor_call_sign;
                            $needComma = TRUE;
                        }
                        if ($advisor_semester_checked == 'X') {
                            if ($needComma) {
                                $content .= '	';
                            }
                            $content .= $advisor_semester;
                            $needComma = TRUE;
                        }
                        if ($advisor_welcome_email_date_checked == 'X') {
                            if ($needComma) {
                                $content .= '	';
                            }
                            $content .= $advisor_welcome_email_date;
                            $needComma = TRUE;
                        }
                        if ($advisor_verify_email_date_checked == 'X') {
                            if ($needComma) {
                                $content .= '	';
                            }
                            $content .= $advisor_verify_email_date;
                            $needComma = TRUE;
                        }
                        if ($advisor_verify_email_number_checked == 'X') {
                            if ($needComma) {
                                $content .= '	';
                            }
                            $content .= $advisor_verify_email_number;
                            $needComma = TRUE;
                        }
                        if ($advisor_verify_response_checked == 'X') {
                            if ($needComma) {
                                $content .= '	';
                            }
                            $content .= $advisor_verify_response;
                            $needComma = TRUE;
                        }
                        if ($advisor_action_log_checked == 'X') {
                            if ($needComma) {
                                $content .= '	';
                            }
                            $content .= $advisor_action_log;
                            $needComma = TRUE;
                        }
                        if ($advisor_class_verified_checked == 'X') {
                            if ($needComma) {
                                $content .= '	';
                            }
                            $content .= $advisor_class_verified;
                            $needComma = TRUE;
                        }
                        if ($advisor_control_code_checked == 'X') {
                            if ($needComma) {
                                $content .= '	';
                            }
                            $content .= $advisor_control_code;
                            $needComma = TRUE;
                        }
                        if ($advisor_date_created_checked == 'X') {
                            if ($needComma) {
                                $content .= '	';
                            }
                            $content .= $advisor_date_created;
                            $needComma = TRUE;
                        }
                        if ($advisor_date_updated_checked == 'X') {
                            if ($needComma) {
                                $content .= '	';
                            }
                            $content .= $advisor_date_updated;
                            $needComma = TRUE;
                        }
                        if ($advisor_replacement_status_checked == 'X') {
                            if ($needComma) {
                                $content .= '	';
                            }
                            $content .= $advisor_replacement_status;
                            $needComma = TRUE;
                        }
                        if ($advisorclass_id_checked == 'X') {
                            if ($needComma) {
                                $content .= '	';
                            }
                            $content .= $advisorclass_id;
                            $needComma = TRUE;
                        }
                        if ($advisorclass_call_sign_checked == 'X') {
                            if ($needComma) {
                                $content .= '	';
                            }
                            $content .= $advisorclass_call_sign;
                            $needComma = TRUE;
                        }
                        if ($advisorclass_sequence_checked == 'X') {
                            if ($needComma) {
                                $content .= '	';
                            }
                            $content .= $advisorclass_sequence;
                            $needComma = TRUE;
                        }
                        if ($advisorclass_semester_checked == 'X') {
                            if ($needComma) {
                                $content .= '	';
                            }
                            $content .= $advisorclass_semester;
                            $needComma = TRUE;
                        }
                        if ($advisorclass_timezone_offset_checked == 'X') {
                            if ($needComma) {
                                $content .= '	';
                            }
                            $content .= $advisorclass_timezone_offset;
                            $needComma = TRUE;
                        }
                        if ($advisorclass_level_checked == 'X') {
                            if ($needComma) {
                                $content .= '	';
                            }
                            $content .= $advisorclass_level;
                            $needComma = TRUE;
                        }
                        if ($advisorclass_language_checked == 'X') {
                            if ($needComma) {
                                $content .= '	';
                            }
                            $content .= $advisorclass_language;
                            $needComma = TRUE;
                        }
                        if ($advisorclass_class_size_checked == 'X') {
                            if ($needComma) {
                                $content .= '	';
                            }
                            $content .= $advisorclass_class_size;
                            $needComma = TRUE;
                        }
                        if ($advisorclass_class_schedule_days_checked == 'X') {
                            if ($needComma) {
                                $content .= '	';
                            }
                            $content .= $advisorclass_class_schedule_days;
                            $needComma = TRUE;
                        }
                        if ($advisorclass_class_schedule_times_checked == 'X') {
                            if ($needComma) {
                                $content .= '	';
                            }
                            $content .= $advisorclass_class_schedule_times;
                            $needComma = TRUE;
                        }
                        if ($advisorclass_class_schedule_days_utc_checked == 'X') {
                            if ($needComma) {
                                $content .= '	';
                            }
                            $content .= $advisorclass_class_schedule_days_utc;
                            $needComma = TRUE;
                        }
                        if ($advisorclass_class_schedule_times_utc_checked == 'X') {
                            if ($needComma) {
                                $content .= '	';
                            }
                            $content .= $advisorclass_class_schedule_times_utc;
                            $needComma = TRUE;
                        }
                        if ($advisorclass_action_log_checked == 'X') {
                            if ($needComma) {
                                $content .= '	';
                            }
                            $content .= $advisorclass_action_log;
                            $needComma = TRUE;
                        }
                        if ($advisorclass_class_incomplete_checked == 'X') {
                            if ($needComma) {
                                $content .= '	';
                            }
                            $content .= $advisorclass_class_incomplete;
                            $needComma = TRUE;
                        }
                        if ($advisorclass_date_created_checked == 'X') {
                            if ($needComma) {
                                $content .= '	';
                            }
                            $content .= $advisorclass_date_created;
                            $needComma = TRUE;
                        }
                        if ($advisorclass_date_updated_checked == 'X') {
                            if ($needComma) {
                                $content .= '	';
                            }
                            $content .= $advisorclass_date_updated;
                            $needComma = TRUE;
                        }
                        if ($advisorclass_student01_checked == 'X') {
                            if ($needComma) {
                                $content .= '	';
                            }
                            $content .= $advisorclass_student01;
                            $needComma = TRUE;
                        }
                        if ($advisorclass_student02_checked == 'X') {
                            if ($needComma) {
                                $content .= '	';
                            }
                            $content .= $advisorclass_student02;
                            $needComma = TRUE;
                        }
                        if ($advisorclass_student03_checked == 'X') {
                            if ($needComma) {
                                $content .= '	';
                            }
                            $content .= $advisorclass_student03;
                            $needComma = TRUE;
                        }
                        if ($advisorclass_student04_checked == 'X') {
                            if ($needComma) {
                                $content .= '	';
                            }
                            $content .= $advisorclass_student04;
                            $needComma = TRUE;
                        }
                        if ($advisorclass_student05_checked == 'X') {
                            if ($needComma) {
                                $content .= '	';
                            }
                            $content .= $advisorclass_student05;
                            $needComma = TRUE;
                        }
                        if ($advisorclass_student06_checked == 'X') {
                            if ($needComma) {
                                $content .= '	';
                            }
                            $content .= $advisorclass_student06;
                            $needComma = TRUE;
                        }
                        if ($advisorclass_student07_checked == 'X') {
                            if ($needComma) {
                                $content .= '	';
                            }
                            $content .= $advisorclass_student07;
                            $needComma = TRUE;
                        }
                        if ($advisorclass_student08_checked == 'X') {
                            if ($needComma) {
                                $content .= '	';
                            }
                            $content .= $advisorclass_student08;
                            $needComma = TRUE;
                        }
                        if ($advisorclass_student09_checked == 'X') {
                            if ($needComma) {
                                $content .= '	';
                            }
                            $content .= $advisorclass_student09;
                            $needComma = TRUE;
                        }
                        if ($advisorclass_student10_checked == 'X') {
                            if ($needComma) {
                                $content .= '	';
                            }
                            $content .= $advisorclass_student10;
                            $needComma = TRUE;
                        }
                        if ($advisorclass_student11_checked == 'X') {
                            if ($needComma) {
                                $content .= '	';
                            }
                            $content .= $advisorclass_student11;
                            $needComma = TRUE;
                        }
                        if ($advisorclass_student12_checked == 'X') {
                            if ($needComma) {
                                $content .= '	';
                            }
                            $content .= $advisorclass_student12;
                            $needComma = TRUE;
                        }
                        if ($advisorclass_student13_checked == 'X') {
                            if ($needComma) {
                                $content .= '	';
                            }
                            $content .= $advisorclass_student13;
                            $needComma = TRUE;
                        }
                        if ($advisorclass_student14_checked == 'X') {
                            if ($needComma) {
                                $content .= '	';
                            }
                            $content .= $advisorclass_student14;
                            $needComma = TRUE;
                        }
                        if ($advisorclass_student15_checked == 'X') {
                            if ($needComma) {
                                $content .= '	';
                            }
                            $content .= $advisorclass_student15;
                            $needComma = TRUE;
                        }
                        if ($advisorclass_student16_checked == 'X') {
                            if ($needComma) {
                                $content .= '	';
                            }
                            $content .= $advisorclass_student16;
                            $needComma = TRUE;
                        }
                        if ($advisorclass_student17_checked == 'X') {
                            if ($needComma) {
                                $content .= '	';
                            }
                            $content .= $advisorclass_student17;
                            $needComma = TRUE;
                        }
                        if ($advisorclass_student18_checked == 'X') {
                            if ($needComma) {
                                $content .= '	';
                            }
                            $content .= $advisorclass_student18;
                            $needComma = TRUE;
                        }
                        if ($advisorclass_student19_checked == 'X') {
                            if ($needComma) {
                                $content .= '	';
                            }
                            $content .= $advisorclass_student19;
                            $needComma = TRUE;
                        }
                        if ($advisorclass_student20_checked == 'X') {
                            if ($needComma) {
                                $content .= '	';
                            }
                            $content .= $advisorclass_student20;
                            $needComma = TRUE;
                        }
                        if ($advisorclass_student21_checked == 'X') {
                            if ($needComma) {
                                $content .= '	';
                            }
                            $content .= $advisorclass_student21;
                            $needComma = TRUE;
                        }
                        if ($advisorclass_student22_checked == 'X') {
                            if ($needComma) {
                                $content .= '	';
                            }
                            $content .= $advisorclass_student22;
                            $needComma = TRUE;
                        }
                        if ($advisorclass_student23_checked == 'X') {
                            if ($needComma) {
                                $content .= '	';
                            }
                            $content .= $advisorclass_student23;
                            $needComma = TRUE;
                        }
                        if ($advisorclass_student24_checked == 'X') {
                            if ($needComma) {
                                $content .= '	';
                            }
                            $content .= $advisorclass_student24;
                            $needComma = TRUE;
                        }
                        if ($advisorclass_student25_checked == 'X') {
                            if ($needComma) {
                                $content .= '	';
                            }
                            $content .= $advisorclass_student25;
                            $needComma = TRUE;
                        }
                        if ($advisorclass_student26_checked == 'X') {
                            if ($needComma) {
                                $content .= '	';
                            }
                            $content .= $advisorclass_student26;
                            $needComma = TRUE;
                        }
                        if ($advisorclass_student27_checked == 'X') {
                            if ($needComma) {
                                $content .= '	';
                            }
                            $content .= $advisorclass_student27;
                            $needComma = TRUE;
                        }
                        if ($advisorclass_student28_checked == 'X') {
                            if ($needComma) {
                                $content .= '	';
                            }
                            $content .= $advisorclass_student28;
                            $needComma = TRUE;
                        }
                        if ($advisorclass_student29_checked == 'X') {
                            if ($needComma) {
                                $content .= '	';
                            }
                            $content .= $advisorclass_student29;
                            $needComma = TRUE;
                        }
                        if ($advisorclass_student30_checked == 'X') {
                            if ($needComma) {
                                $content .= '	';
                            }
                            $content .= $advisorclass_student30;
                            $needComma = TRUE;
                        }
                        if ($advisorclass_number_students_checked == 'X') {
                            if ($needComma) {
                                $content .= '	';
                            }
                            $content .= $advisorclass_number_students;
                            $needComma = TRUE;
                        }
                        if ($advisorclass_evaluation_complete_checked == 'X') {
                            if ($needComma) {
                                $content .= '	';
                            }
                            $content .= $advisorclass_evaluation_complete;
                            $needComma = TRUE;
                        }
                        if ($advisorclass_class_comments_checked == 'X') {
                            if ($needComma) {
                                $content .= '	';
                            }
                            $content .= $advisorclass_class_comments;
                            $needComma = TRUE;
                        }
                        if ($advisorclass_copy_control_checked == 'X') {
                            if ($needComma) {
                                $content .= '	';
                            }
                            $content .= $advisorclass_copy_control;
                            $needComma = TRUE;
                        }
						$content	.= "\n";
					}
				}
				if ($output_type == 'table') {
					$content	.= "</table>";
				} else {
					$content	.= "</pre>";
				}
				$content		.= "<br /><br />$myCount records printed<br />";
			} else {
				$content		.= "<br />No records found meeting the requested criteria<br />";
			}
		}
	} 
	
	return $content;
	
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
		$storeResult	= storeReportData_func("Student Report Generator",$content);
		if ($storeResult !== FALSE) {
			$content	.= "<br />Report stored in reports table as $storeResult";
		} else {
			$content	.= "<br />Storing the report in the reports table failed";
		}
	}
	return $content;
}
add_shortcode('advisorclass_report_generator','advisorclass_report_generator_func');
