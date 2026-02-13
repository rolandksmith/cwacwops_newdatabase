function user_master_report_generator_func() {

/*	Build Your Own Report from the user_ aster table
 *	
 *	Select which fields to display in the report
 *	specify the data selection criteria
 * 	specify the post-selection tests
 *	display the report

	forked from student_report_generator on 15Nov24 by Roland 
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
	$jobname = "User Master Report Generator V$versionNumber";
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
	$theURL					 	= "$siteURL/cwa-user-master-report-generator/";

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
    $user_prev_callsign = '';
    $user_date_created = '';
    $user_date_updated = '';

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
    $user_prev_callsign_checked = '';
    $user_date_created_checked = '';
    $user_date_updated_checked = '';

	$table_checked						= 'checked';
	$comma_checked						= '';
	$where							= '';
	$orderby						= 'user_call_sign';
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
            if ($str_key == 'user_prev_callsign') {
                $user_prev_callsign_checked = 'X';
                $reportConfig['user_prev_callsign_checked'] = 'X';
                if ($doDebug) {
                    echo "user_prev_callsign included in report<br />";
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
	
	
	
	$content = "";	

	if ($mode_type == 'production') {
		$userMasterTableName		= 'wpw1_cwa_user_master';
		$countryCodesTableName		= 'wpw1_cwa_country_codes';
	} else {
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
							<tr><td><input type='checkbox' class='formInputButton' id='user_prev_callsign' 
									name='user_prev_callsign' value='user_prev_callsign'>
									<label for 'user_prev_callsign'>user_prev_callsign</label></td>
								<td>user_prev_callsign</td></tr>
							<tr><td><input type='checkbox' class='formInputButton' id='user_date_created' 
									name='user_date_created' value='user_date_created'>
									<label for 'user_date_created'>user_date_created</label></td>
								<td>user_date_created</td></tr>
							<tr><td><input type='checkbox' class='formInputButton' id='user_date_updated' 
									name='user_date_updated' value='user_date_updated'>
									<label for 'user_date_updated'>user_date_updated</label></td>
								<td>user_date_updated</td></tr>
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
        $nameConversionArray['user_ID'] = 'User<br />ID';
        $nameConversionArray['user_call_sign'] = 'User<br />Callsign';
        $nameConversionArray['user_first_name'] = 'User<br />First Name';
        $nameConversionArray['user_last_name'] = 'User<br />Last Name';
        $nameConversionArray['user_email'] = 'User<br />Email';
        $nameConversionArray['user_ph_code'] = 'User<br />ph_code';
        $nameConversionArray['user_phone'] = 'User<br />Phone';
        $nameConversionArray['user_city'] = 'User<br />City';
        $nameConversionArray['user_state'] = 'User<br />State';
        $nameConversionArray['user_zip_code'] = 'User<br />Zip Code';
        $nameConversionArray['user_country_code'] = 'User<br />Country Code';
        $nameConversionArray['user_country'] = 'User<br />Country';
        $nameConversionArray['user_whatsapp'] = 'User<br />Whatsapp';
        $nameConversionArray['user_telegram'] = 'User<br />Telegram';
        $nameConversionArray['user_signal'] = 'User<br />Signal';
        $nameConversionArray['user_messenger'] = 'User<br />Messenger';
        $nameConversionArray['user_action_log'] = 'User<br />Action Log';
        $nameConversionArray['user_timezone_id'] = 'User<br />Timezone ID';
        $nameConversionArray['user_languages'] = 'User<br />Languages';
        $nameConversionArray['user_survey_score'] = 'User<br />Survey Score';
        $nameConversionArray['user_is_admin'] = 'User<br />is_admin';
        $nameConversionArray['user_role'] = 'User<br />Role';
        $nameConversionArray['user_prev_callsign'] = 'User<br />Prev Callsign';
        $nameConversionArray['user_date_created'] = 'User<br />Date Created';
        $nameConversionArray['user_date_updated'] = 'User<br />Date Updated';

		// Begin the Report Output

		if ($inp_config == 'Y') {		// saving the report configuration
			if ($inp_report_name != '') {
				$whereStr					= htmlentities($where,ENT_QUOTES);
				$reportConfig['where']		= $whereStr;
				$reportConfig['orderby']	= $orderby;
				$reportConfig['rg_table']	= $userMasterTableName;
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
		
		$content				.= "<h2>Generated Report from the $userMasterTableName Table</h2>
									$myReportName
									<p>Save report: $inp_report<br />";

		$sql = "select * from $userMasterTableName ";
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
             if ($user_prev_callsign_checked == 'X') {
                 $headerName = $nameConversionArray['user_prev_callsign'];
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
            if ($user_prev_callsign_checked == 'X') {
                if ($needComma) {
                    $content .= '	';
                }
                $headerName = $nameConversionArray['user_prev_callsign'];
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
			$content	.= "\n";
		}
///// read the user_master table
		$myCount					= 0;
		$wpw1_cwa_user_master			= $wpdb->get_results($sql);
		if (FALSE === $wpw1_cwa_user_master) {		// no record found
			if ($doDebug) {
				echo "FUNCTION: No data found in $mode_type<br />\n";
			}
		} else {
			$numSRows				= $wpdb->num_rows;
			if ($doDebug) {
				echo "ran $sql<br />and retrieved $numSRows rows<br />";
			}
			if ($numSRows > 0) {
				foreach ($wpw1_cwa_user_master as $userRow) {
					$user_ID								= $userRow->user_ID;
					$user_call_sign							= $userRow->user_call_sign;
					$user_first_name						= $userRow->user_first_name;
					$user_last_name							= $userRow->user_last_name;
					$user_email								= $userRow->user_email;
					$user_ph_code							= $userRow->user_ph_code;
					$user_phone								= $userRow->user_phone;
					$user_city								= $userRow->user_city;
					$user_state								= $userRow->user_state;
					$user_zip_code							= $userRow->user_zip_code;
					$user_country_code						= $userRow->user_country_code;
					$user_country							= $userRow->user_country;
					$user_whatsapp							= $userRow->user_whatsapp;
					$user_telegram							= $userRow->user_telegram;
					$user_signal							= $userRow->user_signal;
					$user_messenger							= $userRow->user_messenger;
					$user_action_log						= $userRow->user_action_log;
					$user_timezone_id						= $userRow->user_timezone_id;
					$user_languages							= $userRow->user_languages;
					$user_survey_score						= $userRow->user_survey_score;
					$user_is_admin							= $userRow->user_is_admin;
					$user_role								= $userRow->user_role;
					$user_prev_callsign						= $userRow->user_prev_callsign;
					$user_date_created						= $userRow->user_date_created;
					$user_date_updated						= $userRow->user_date_updated;


					

					$myCount++;
	
					if ($doDebug) {
						echo "Processing $user_call_sign<br />\n";
					}
					if ($output_type == 'table') {
						$content	.= "<tr>";
                       if ($user_ID_checked == 'X') {
                           $content .= "<td style='vertical-align:top'>$user_ID</td>";
                        }
                       if ($user_call_sign_checked == 'X') {
                           $content .= "<td style='vertical-align:top'><a href='$siteURL/cwa-display-and-update-user-master/?strpass=10' 
                           target='_blank'>$user_call_sign<a/></td>";
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
                       if ($user_prev_callsign_checked == 'X') {
                           $content .= "<td style='vertical-align:top'>$user_prev_callsign</td>";
                        }
                       if ($user_date_created_checked == 'X') {
                           $content .= "<td style='vertical-align:top'>$user_date_created</td>";
                        }
                       if ($user_date_updated_checked == 'X') {
                           $content .= "<td style='vertical-align:top'>$user_date_updated</td>";
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
                        if ($user_prev_callsign_checked == 'X') {
                            if ($needComma) {
                                $content .= '	';
                            }
                            $content .= $user_prev_callsign;
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
add_shortcode ('user_master_report_generator', 'user_master_report_generator_func');

