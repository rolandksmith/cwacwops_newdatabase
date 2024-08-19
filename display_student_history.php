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
	$siteURL			= $initializationArray['siteurl'];
	$jobname	= "Display Student History";
	
//	CHECK THIS!								//////////////////////
	if ($validUser == "N") {
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
	$semesterBack				= array(1=>"JAN/FEB",2=>"APR/MAY",3=>"MAY/JUN",4=>"SEP/OCT");
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
		$studentTableName				= "wpw1_cwa_consolidated_student2";
		$advisorTableName				= "wpw1_cwa_consolidated_advisor2";
	} else {
		$studentTableName				= "wpw1_cwa_consolidated_student";
		$advisorTableName				= "wpw1_cwa_consolidated_advisor";
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
								order by date_created";
		$wpw1_cwa_student	= $wpdb->get_results($sql);
		if ($wpw1_cwa_student === FALSE) {
				$myError			= $wpdb->last_error;
				$myQuery			= $wpdb->last_query;
				if ($doDebug) {
					echo "Reading $studentTableName table failed<br />
						  wpdb->last_query: $myQuery<br />
						  wpdb->last_error: $myError<br />";
				}
				$errorMsg			= "$jobname reading $studentTableName failed. <p>SQL: $myQuery</p><p> Error: $myError</p>";
				sendErrorEmail($errorMsg);
		} else {
			$numSRows									= $wpdb->num_rows;
			if ($doDebug) {
				echo "retrieved $numSRows rows from $studentTableName table<br />";
			}
			if ($numSRows > 0) {
				foreach ($wpw1_cwa_student as $studentRow) {
					$student_ID								= $studentRow->student_id;
					$student_call_sign						= strtoupper($studentRow->call_sign);
					$student_first_name						= $studentRow->first_name;
					$student_last_name						= stripslashes($studentRow->last_name);
					$student_email  						= strtolower(strtolower($studentRow->email));
					$student_ph_code						= $studentRow->ph_code;
					$student_phone  						= $studentRow->phone;
					$student_city  							= $studentRow->city;
					$student_state  						= $studentRow->state;
					$student_zip_code  						= $studentRow->zip_code;
					$student_country_code					= $studentRow->country_code;
					$student_country  						= $studentRow->country;
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
					$student_date_created 					= $studentRow->date_created;
					$student_date_updated			  		= $studentRow->date_updated;

					$student_last_name 						= no_magic_quotes($student_last_name);

					if ($student_timezone_offset == '-99') {
						$student_timezone_offset = $student_time_zone;
					}
				
					$myArray	= explode(" ",$student_semester);
					$myLookup	= strtoupper($myArray[1]);
					$myInt		= $semesterConversion[$myLookup];
					$myYear		= $myArray[0];
					$newSemester = "$myYear$myInt";
					$studentArray[]	= "$student_call_sign|$newSemester|$student_level|$student_timezone_id $student_timezone_offset|$student_response|$student_student_status|$student_assigned_advisor|$student_promotable|$student_last_name, $student_first_name";
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

			if($theCallSign != $prevCallSign) {
				if ($firstTime) {
					$firstTime	= FALSE;
				} else {
					// see if there are any advisor records
					$sql				= "select * from $advisorTableName 
											where call_sign='$theCallSign' 
											order by date_created ";
					$wpw1_cwa_advisor		= $wpdb->get_results($sql);
					if ($wpw1_cwa_advisor === FALSE) {
						$myError			= $wpdb->last_error;
						$myQuery			= $wpdb->last_query;
						if ($doDebug) {
							echo "Reading $advisorTableName table failed<br />
								  wpdb->last_query: $myQuery<br />
								  wpdb->last_error: $myError<br />";
						}
						$errorMsg			= "$jobname Reading $advisorTableName table failed. <p>SQL: $myQuery</p><p> Error: $myError</p>";
						sendErrorEmail($errorMsg);
					} else {
						$numARows									= $wpdb->num_rows;
						if ($doDebug) {
							echo "found $numARows rows in $advisorTableName table<br />";
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

								$advisor_last_name 					= no_magic_quotes($advisor_last_name);

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
							}
						} else {
							if ($doDebug) {
								echo "No $advisorTableName table records found<br />";
							}
						}
					}
					$content	.= "<tr><td colspan='9'><hr></td></tr>";
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
