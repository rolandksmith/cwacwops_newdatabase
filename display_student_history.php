function display_student_history_func() {

/*	Display Student History
 *
 *	Reads the current student and past_student pods and builds an array for 
 *	each student
 *
 *	studentArray: callSign
 *					semester
 *						level|time zone|student response|student status|assigned_advisor|promotable|name
 *
 *	Sort the array by call sign
 *	build the report
 *
 *	Modified 1Feb2021 by Roland to change student_code to messaging
 	Modified 29Sep22 by Roland for new student table structure
 	Modified 16Apr23 by Roland to fix action_log
 	Modified 13Jul23 by Roland to use consolidated tables
 	Modified 19Oct24 by Roland to use new database
*/

	global $wpdb;

	$doDebug						= FALSE;
	$testMode						= FALSE;
	$initializationArray = data_initialization_func();
	if ($doDebug) {
		echo "Initialization Array:<br /><pre>";
		print_r($initializationArray);
		echo "</pre><br />";
	}
	$validUser = $initializationArray['validUser'];
	$userName  = $initializationArray['userName'];
	$siteURL	= $initializationArray['siteurl'];
	$jobname	= "Display Student History";
	
//	CHECK THIS!								//////////////////////
	if ($userName = '') {
		return "YOU'RE NOT AUTHORIZED!<br />Goodby";
	}

	ini_set('memory_limit','256M');

//	if ($doDebug) {
		ini_set('display_errors','1');
		error_reporting(E_ALL);	
//	}

/// get the time that the process started
	$startingMicroTime			= microtime(TRUE);

	$strPass					= "1";
	$studentArray				= array();
	$newStudentCount			= 0;
	$pastStudentCount			= 0;
	$semesterConversion			= array("JAN/FEB"=>1,"APR/MAY"=>2,"MAY/JUN"=>3,"SEP/OCT"=>4);
	$semesterBack				= array(1=>"Jan/Feb",2=>"Apr/May",3=>"May/Jun",4=>"Sep/Oct");
	$theURL						= "$siteURL/cwa-display-student-history/";

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

	if ($testMode) {
		$content	.= "<p><strong>Operating in Test Mode.</strong></p>";
		if ($doDebug) {
			echo "<p><strong>Operating in Test Mode.</strong></p>";
		}
		$studentTableName				= "wpw1_cwa_student2";
		$advisorTableName				= "wpw1_cwa_advisor2";
		$userMasterTableName			= 'wpw1_cwa_user_master2';
	} else {
		$studentTableName				= "wpw1_cwa_student";
		$advisorTableName				= "wpw1_cwa_advisor";
		$userMasterTableName			= 'wpw1_cwa_user_master';
	}



	if ("1" == $strPass) {
		if ($doDebug) {
			echo "Function starting.<br />";
		}
		$content 		.= "<h3>$jobname</h3<
							<p>Click Submit to Start the Process</p>
							<p><form method='post' action='$theURL' 
							name='selection_form' ENCTYPE='multipart/form-data''>
							<input type='hidden' name='strpass' value='2'>
							<input class='formInputButton' type='submit' value='Submit' />
							</form></p>";

///// Pass 2 -- do the work


	} elseif ("2" == $strPass) {
		if ($doDebug) {
			echo "at pass2<br />";
		}
		$sql			 	= "select * from $studentTableName 
								left join $userMasterTableName on user_call_sign = student_call_sign 
								order by student_date_created";
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
					$student_phone 						= $studentRow->user_phone;
					$student_city 						= $studentRow->user_city;
					$student_state 						= $studentRow->user_state;
					$student_zip_code 					= $studentRow->user_zip_code;
					$student_country_code 				= $studentRow->user_country_code;
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
					$student_parent 				= $studentRow->student_parent;
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
				
					$myArray	= explode(" ",$student_semester);
					$myLookup	= strtoupper($myArray[1]);
					$myInt		= $semesterConversion[$myLookup];
					$myYear		= $myArray[0];
					$newSemester = "$myYear$myInt";
					$studentArray[]	= "$student_call_sign|$newSemester|$student_level|$student_timezone_id $student_timezone_offset|$student_response|$student_status|$student_assigned_advisor|$student_promotable|$student_last_name, $student_first_name";
					$newStudentCount++;
					if ($doDebug) {
						echo "Added $student_call_sign | $newSemester | etc to the studentArray<br />";
					}
				}
			} else {
				if ($doDebug) {
					echo "No records found in $studentTableName<br />";
				}
			}
		}

		sort($studentArray);

		$content	.= "<h3>Display Student History</h3>
						<table>
						<tr>
							<th style='width:100px;'>Call Sign</th>
							<th>Name</th>
							<th>Semester</th>
							<th>Level</th>
							<th>TZ</th>
							<th>Response</th>
							<th>Status</th>
							<th>Advisor</th>
							<th>Promotable</th>
						</tr>";

		$prevCallSign			= '';
		$firstTime				= TRUE;
		//	call_sign|semester|level|time zone|student response|student status|assigned_advisor|promotable|name
		foreach($studentArray as $value) {
			$myArray		= explode("|",$value);
			$theCallSign	= $myArray[0];
			$theSemester	= $myArray[1];
			$theLevel		= $myArray[2];
			$theTZ			= $myArray[3];
			$theResponse	= $myArray[4];
			$theStatus		= $myArray[5];
			$theAdvisor		= $myArray[6];
			$thePromotable	= $myArray[7];
			$theName		= $myArray[8];
			
			if ($doDebug) {
				echo "<br />Processing $theCallSign<br />";
			}

			if($theCallSign != $prevCallSign) {
				if ($firstTime) {
					$firstTime	= FALSE;
				} else {
					// see if there are any advisor records
					$sql				= "select * from $advisorTableName 
											where advisor_call_sign='$prevCallSign' 
											order by advisor_date_created ";
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

								$content	.= "<tr><td>&nbsp;</td>
													<td style='vertical-align:top;'>Advisor</td>
													<td style='vertical-align:top;'>$advisor_semester</td>
													<td style='vertical-align:top;'>&nbsp;</td>
													<td style='vertical-align:top;text-align:center;'>&nbsp;</td>
													<td style='vertical-align:top;text-align:center;'>&nbsp;</td>
													<td>&nbsp;</td>
													<td>&nbsp;</td>
													<td>&nbsp;</td>
												</tr>";
								if ($doDebug) {
									echo "put out the $advisor_semester advisor record<br /";
								}
							}
						} else {
							if ($doDebug) {
								echo "No $advisorTableName table records found<br />";
							}
						}
						$content	.= "<tr><td colspan='9'><hr></td></tr>";
					}
				}
				$prevCallSign	= $theCallSign;
				$content		.= "<tr><td style='vertical-align:top;'>$theCallSign</td><td style='vertical-align:top;'>$theName</td>";
			} else {
				$content		.= "<tr><td>&nbsp;</td><td>&nbsp;</td>";
			}
			$theYear		= substr($theSemester,0,4);
			$theInt			= substr($theSemester,4,1);
			$backSemester	= $semesterBack[$theInt];
			$content		.= "<td style='vertical-align:top;'>$theYear $backSemester</td>
								<td style='vertical-align:top;'>$theLevel</td>
								<td style='vertical-align:top;text-align:center;'>$theTZ</td>
								<td style='vertical-align:top;text-align:center;'>$theResponse</td>
								<td style='vertical-align:top;text-align:center;'>$theStatus</td>
								<td style='vertical-align:top;'>$theAdvisor</td>
								<td style='vertical-align:top;'text-align:center;>$thePromotable</td>
							</tr>";
			if ($doDebug) {
				echo "put out $theYear $backSemester record<br />";
			}
		}		/////// end of the report
		$content	.= "</table>
						<p>Students: $newStudentCount<br />";
		

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
	$result			= write_joblog_func("Display Student History|$nowDate|$nowTime|$userName|Time|$thisStr|$strPass: $elapsedTime");
	if ($result == 'FAIL') {
		$content	.= "<p>writing to joblog.txt failed</p>";
	}
	return $content;
}
add_shortcode ('display_student_history', 'display_student_history_func');
