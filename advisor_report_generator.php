function advisor_report_generator_func() {

/*

	created 1Dec24 by Roland

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
	$currentSemester	= $initializationArray['currentSemester'];
	$nextSemester		= $initializationArray['nextSemester'];
	$semesterTwo		= $initializationArray['semesterTwo'];
	$semesterThree		= $initializationArray['semesterThree'];
	$semesterFour		= $initializationArray['semesterFour'];
	$futureSemester		= "(semester = '$nextSemester' or semester = '$semesterTwo' or semester = '$semesterThree' or semester = '$semesterFour')";
	$proximateSemester	= $initializationArray['proximateSemester'];
	if ($validUser == "N") {
		return "YOU'RE NOT AUTHORIZED!<br />Goodby";
	}
	$versionNumber = '1';
	$jobname = "Advisor Report Generator V$versionNumber";
	
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
	$theURL						= "$siteURL/cwa-advisor-report-generator/";

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
	$comma_checked						= '';	
	$thisWhere							= '';
	$thisOrderby						= 'advisor_call_sign';
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
			$advisorTableName			= 'wpw1_cwa_advisor2';	
			$userMasterTableName		= 'wpw1_cwa_user_master2';
		} else {
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
		
		$content				.= "<h2>Generated Report from the $advisorTableName Table</h2>
									$myReportName
									<p>Save report: $inp_report<br />";
	
		$sql	= "SELECT * FROM $advisorTableName 
					left join $userMasterTableName on user_call_sign = advisor_call_sign ";
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
			$content	.= "\n";
		}
		$myCount					= 0;
		$wpw1_cwa_advisor			= $wpdb->get_results($sql);
		if ($wpw1_cwa_advisor === FALSE) {
			handleWPDBError($jobname,$doDebug);
		} else {
			$numACRows				= $wpdb->num_rows;
			if ($doDebug) {
				echo "ran $sql<br />and found $numACRows rows<br />";
			}
			if ($numACRows > 0) {
				foreach ($wpw1_cwa_advisor as $advisorRow) {
					$user_ID								= $advisorRow->user_ID;
					$user_call_sign							= $advisorRow->user_call_sign;
					$user_first_name						= $advisorRow->user_first_name;
					$user_last_name							= $advisorRow->user_last_name;
					$user_email								= $advisorRow->user_email;
					$user_ph_code							= $advisorRow->user_ph_code;
					$user_phone								= $advisorRow->user_phone;
					$user_city								= $advisorRow->user_city;
					$user_state								= $advisorRow->user_state;
					$user_zip_code							= $advisorRow->user_zip_code;
					$user_country_code						= $advisorRow->user_country_code;
					$user_country							= $advisorRow->user_country;
					$user_whatsapp							= $advisorRow->user_whatsapp;
					$user_telegram							= $advisorRow->user_telegram;
					$user_signal							= $advisorRow->user_signal;
					$user_messenger							= $advisorRow->user_messenger;
					$user_action_log						= $advisorRow->user_action_log;
					$user_timezone_id						= $advisorRow->user_timezone_id;
					$user_languages							= $advisorRow->user_languages;
					$user_survey_score						= $advisorRow->user_survey_score;
					$user_is_admin							= $advisorRow->user_is_admin;
					$user_role								= $advisorRow->user_role;
					$user_date_created						= $advisorRow->user_date_created;
					$user_date_updated						= $advisorRow->user_date_updated;
					$advisor_id								= $advisorRow->advisor_id;
					$advisor_call_sign						= $advisorRow->advisor_call_sign;
					$advisor_semester						= $advisorRow->advisor_semester;
					$advisor_welcome_email_date				= $advisorRow->advisor_welcome_email_date;
					$advisor_verify_email_date				= $advisorRow->advisor_verify_email_date;
					$advisor_verify_email_number			= $advisorRow->advisor_verify_email_number;
					$advisor_verify_response				= $advisorRow->advisor_verify_response;
					$advisor_action_log						= $advisorRow->advisor_action_log;
					$advisor_class_verified					= $advisorRow->advisor_class_verified;
					$advisor_control_code					= $advisorRow->advisor_control_code;
					$advisor_date_created					= $advisorRow->advisor_date_created;
					$advisor_date_updated					= $advisorRow->advisor_date_updated;
					$advisor_replacement_status				= $advisorRow->advisor_replacement_status;

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
		$storeResult	= storeReportData_func("Student Report Generator",$content);
		if ($storeResult !== FALSE) {
			$content	.= "<br />Report stored in reports table as $storeResult";
		} else {
			$content	.= "<br />Storing the report in the reports table failed";
		}
	}
	return $content;
}
add_shortcode('advisor_report_generator','advisor_report_generator_func');