function generate_advisor_overall_statistics_v1_func() {

	global $wpdb;

	$doDebug						= FALSE;
	$testMode						= FALSE;
	$initializationArray 			= data_initialization_func();
	$versionNumber				 	= "1";
	if ($doDebug) {
		echo "Initialization Array:<br /><pre>";
		print_r($initializationArray);
		echo "</pre><br />";
	}
	$validUser 			= $initializationArray['validUser'];
	$userName			= $initializationArray['userName'];
	$currentTimestamp	= $initializationArray['currentTimestamp'];
	$validTestmode		= $initializationArray['validTestmode'];
	$siteURL			= $initializationArray['siteurl'];
	
//	CHECK THIS!								//////////////////////
	if ($validUser == "N") {
		return "YOU'RE NOT AUTHORIZED!<br />Goodby";
	}

	ini_set('memory_limit','256M');
	ini_set('max_execution_time',0);
	set_time_limit(0);

//	if ($doDebug) {
		ini_set('display_errors','1');
		error_reporting(E_ALL);	
//	}

/// get the time that the process started
	$startingMicroTime			= microtime(TRUE);

	$strPass					= "1";
	$theURL						= "$siteURL/cwa-generate-advisor-overall-statistics/";
	$inp_semester				= '';
	$inp_rsave					= '';
	$jobname					= "Generate Advisor Overall Statistics V$versionNumber";

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
			if ($str_key 		== "inp_verbose") {
				$inp_verbose	 = $str_value;
				$inp_verbose	 = filter_var($inp_verbose,FILTER_UNSAFE_RAW);
				if ($inp_verbose == 'Y') {
					$doDebug	= TRUE;
				}
			}
			if ($str_key 		== "inp_mode") {
				$inp_mode	 = $str_value;
				$inp_mode	 = filter_var($inp_mode,FILTER_UNSAFE_RAW);
				if ($inp_mode == 'TESTMODE') {
					$testMode = TRUE;
				}
			}
		}
	}
	
	
	if (in_array($userName,$validTestmode)) {			// give option to run in test mode 
		$testModeOption	= "<tr><td>Operation Mode</td>
							<td><input type='radio' class='formInputButton' name='inp_mode' value='Production' checked='checked'> Production<br />
								<input type='radio' class='formInputButton' name='inp_mode' value='TESTMODE'> TESTMODE</td></tr>
							<tr><td>Verbose Debugging?</td>
								<td><input type='radio' class='formInputButton' name='inp_verbose' value='N' checked='checked'> Standard Output<br />
									<input type='radio' class='formInputButton' name='inp_verbose' value='Y'> Turn on Debugging </td></tr>";
	} else {
		$testModeOption	= '';
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

	function Stand_Deviation($arr) {

		$num_of_elements = count($arr);

		$variance = 0.0;

		// calculating mean using array_sum() method
		$average = array_sum($arr)/$num_of_elements;

		foreach($arr as $i) {
			// sum of squares of differences between
			// all numbers and means.
			$variance += pow(($i - $average), 2);
		}
		$stdev = (float)sqrt($variance/$num_of_elements);
 
		return array($average,$stdev);
	}



	if ($testMode) {
		$content	.= "<p><strong>Operating in Test Mode.</strong></p>";
		if ($doDebug) {
			echo "<p><strong>Operating in Test Mode.</strong></p>";
		}
		$extMode					= 'tm';
		$TableName					= "wpw1_cwa_";
	} else {
		$extMode					= 'pd';
		$TableName					= "wpw1_cwa_";
	}



	if ("1" == $strPass) {
		$content 		.= "<h3>$jobname</h3>
<p>
<form method='post' action='$theURL' 
name='selection_form' ENCTYPE='multipart/form-data'>
<input type='hidden' name='strpass' value='2'>
<table style='border-collapse:collapse;'>
$testModeOption
<tr><td>Save this report to the reports achive?</td>
<td><input type='radio' class='formInputButton' name='inp_rsave' value='N' checked='checked'> Do not save the report<br />
	<input type='radio' class='formInputButton' name='inp_rsave' value='Y'> Save a copy of the report the report</td></tr>
<tr><td colspan='2'><input class='formInputButton' type='submit' value='Submit' /></td></tr></table>
</form></p>";
	

///// Pass 2 -- do the work


	} elseif ("2" == $strPass) {
	
		if ($doDebug) {
			echo "<br />arrived at pass 2<br />";
		}
		$prevAdvisor				= '';
		$prevLevel					= '';
		$firstTime					= TRUE;
		
		$levelUp					= array('Beginner'=>'Fundamental',
											'Fundamental'=>'Intermediate',
											'Intermediate'=>'Advanced');
/* arrays
	advisorArray:
		[level][advisor][studentcount]
		              [promotable]
		              [notpromotable]
		              [dropped]
		              [withdrew]
		              [noteval]
		              [nextlevel]
		              [nextdropped]
		              [nextpromotable]
		              [nextnotpromotable]
		              [nextwithdrew]
		              [nextnoteval]
		              
	overallArray
		[level][studentcount]
			   [promotable]
			   [notpromotable]
			   [dropped]
			   [withdrew]
			   [noteval]
			   [nextlevel]
			   [nextdropped]
			   [nextpromotable]
			   [nextnotpromotable]
			   [nextwithdrew]
			   [nextnoteval]
*/

		$advisorArray				= array();
		$overallArray				= array();
		$overallArray[1]['studentcount']			= 0;
		$overallArray[1]['promotable']				= 0;
		$overallArray[1]['notpromotable']			= 0;
		$overallArray[1]['dropped']					= 0;
		$overallArray[1]['withdrew']				= 0;
		$overallArray[1]['noteval']					= 0;
		$overallArray[1]['nextlevel']				= 0;
		$overallArray[1]['nextdropped']				= 0;
		$overallArray[1]['nextpromotable']			= 0;
		$overallArray[1]['nextnotpromotable']		= 0;
		$overallArray[1]['nextwithdrew']			= 0;
		$overallArray[1]['nextnoteval']				= 0;

		$overallArray[2]['studentcount']		= 0;
		$overallArray[2]['promotable']			= 0;
		$overallArray[2]['notpromotable']		= 0;
		$overallArray[2]['dropped']				= 0;
		$overallArray[2]['withdrew']			= 0;
		$overallArray[2]['noteval']				= 0;
		$overallArray[2]['nextlevel']			= 0;
		$overallArray[2]['nextdropped']			= 0;
		$overallArray[2]['nextpromotable']		= 0;
		$overallArray[2]['nextnotpromotable']	= 0;
		$overallArray[2]['nextwithdrew']		= 0;
		$overallArray[2]['nextnoteval']			= 0;

		$overallArray[3]['studentcount']		= 0;
		$overallArray[3]['promotable']			= 0;
		$overallArray[3]['notpromotable']		= 0;
		$overallArray[3]['dropped']				= 0;
		$overallArray[3]['withdrew']			= 0;
		$overallArray[3]['noteval']				= 0;
		$overallArray[3]['nextlevel']			= 0;
		$overallArray[3]['nextdropped']			= 0;
		$overallArray[3]['nextpromotable']		= 0;
		$overallArray[3]['nextnotpromotable']	= 0;
		$overallArray[3]['nextwithdrew']		= 0;
		$overallArray[3]['nextnoteval']			= 0;

		$overallArray[4]['studentcount']		= 0;
		$overallArray[4]['promotable']			= 0;
		$overallArray[4]['notpromotable']		= 0;
		$overallArray[4]['dropped']				= 0;
		$overallArray[4]['withdrew']			= 0;
		$overallArray[4]['noteval']				= 0;
		$overallArray[4]['nextlevel']			= 0;
		$overallArray[4]['nextdropped']			= 0;
		$overallArray[4]['nextpromotable']		= 0;
		$overallArray[4]['nextnotpromotable']	= 0;
		$overallArray[4]['nextwithdrew']		= 0;
		$overallArray[4]['nextnoteval']			= 0;
		
		$sortLevel			= array('Beginner'=>1,
									'Fundamental'=>2,
									'Intermediate'=>3,
									'Advanced'=>4);
		$printLevel			= array(1=>'Beginner',
									2=>'Fundamental',
									3=>'Intermediate',
									4=>'Advanced');
	
		// get the student records sorted by assigned advisor and level
		$sql		= "select * from wpw1_cwa_consolidated_student 
						where assigned_advisor != '' 
						and student_status != 'N' 
						order by assigned_advisor, level ";
						
		$wpw1_cwa_student		= $wpdb->get_results($sql);
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
			$content		.= "Unable to obtain content from $studentTableName<br />";
		} else {
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
					$student_date_created 					= $studentRow->date_created;
					$student_date_updated			  		= $studentRow->date_updated;
					
					if ($doDebug) {
						echo "<br />Have student $student_call_sign<br />
							  semester: $student_semester<br />
							  assigned_advisor: $student_assigned_advisor<br />
							  level: $student_level<br />
							  student_status: $student_student_status<br />
							  promotable: $student_promotable<br />";
					}
					$studentLevel				= $sortLevel[$student_level];
					// see if a new advisor/level
					if ($student_assigned_advisor != $prevAdvisor || $student_level != $prevLevel) {
						if ($doDebug) {
							echo "have a new advisor or level<br />";
						}
						$prevAdvisor		= $student_assigned_advisor;
						$prevLevel			= $student_level;

						if ($doDebug) {
							echo "building advisorArray for level $studentLevel and advisor $student_assigned_advisor<br />";
						}
						$advisorArray[$studentLevel][$student_assigned_advisor]['studentcount']			= 0;
						$advisorArray[$studentLevel][$student_assigned_advisor]['promotable']			= 0;
						$advisorArray[$studentLevel][$student_assigned_advisor]['notpromotable']		= 0;
						$advisorArray[$studentLevel][$student_assigned_advisor]['dropped']				= 0;
						$advisorArray[$studentLevel][$student_assigned_advisor]['withdrew']				= 0;
						$advisorArray[$studentLevel][$student_assigned_advisor]['noteval']				= 0;
						$advisorArray[$studentLevel][$student_assigned_advisor]['nextlevel']			= 0;
						$advisorArray[$studentLevel][$student_assigned_advisor]['nextdropped']			= 0;
						$advisorArray[$studentLevel][$student_assigned_advisor]['nextpromotable']		= 0;
						$advisorArray[$studentLevel][$student_assigned_advisor]['nextnotpromotable']	= 0;
						$advisorArray[$studentLevel][$student_assigned_advisor]['nextwithdrew']			= 0;
						$advisorArray[$studentLevel][$student_assigned_advisor]['nextnoteval']			= 0;
						
					}
					// do the counts
					if ($doDebug) {
						echo "counting for advisorArray level: $studentLevel advisor: $student_assigned_advisor<br />";
					}
					if ($student_student_status == 'C') {
						$advisorArray[$studentLevel][$student_assigned_advisor]['studentcount']++;
						$overallArray[$studentLevel]['studentcount']++;
						$advisorArray[$studentLevel][$student_assigned_advisor]['dropped']++;
						$overallArray[$studentLevel]['dropped']++;
						if ($doDebug) {
							echo "counted dropped<br />";
						}
					} elseif ($student_student_status == 'Y' || $student_student_status == 'S') {
						$advisorArray[$studentLevel][$student_assigned_advisor]['studentcount']++;
						$overallArray[$studentLevel]['studentcount']++;
						if ($student_promotable == 'P') {					
							$advisorArray[$studentLevel][$student_assigned_advisor]['promotable']++;
							$overallArray[$studentLevel]['promotable']++;
							if ($doDebug) {
								echo "counted promotable<br />";
							}
						} elseif ($student_promotable == 'N') {					
							$advisorArray[$studentLevel][$student_assigned_advisor]['notpromotable']++;
							$overallArray[$studentLevel]['notpromotable']++;
							if ($doDebug) {
								echo "counted notpromotable<br />";
							}
						} elseif ($student_promotable == 'W') {
							$advisorArray[$studentLevel][$student_assigned_advisor]['withdrew']++;
							$overallArray[$studentLevel]['withdrew']++;
							if ($doDebug) {
								echo "counted withdrew<br />";
							}
						} else {
							$advisorArray[$studentLevel][$student_assigned_advisor]['noteval']++;
							$overallArray[$studentLevel]['noteval']++;
							if ($doDebug) {
								echo "counted noteval<br />";
							}
						}
						// find if student has taken next level
						if ($student_level != 'Advanced') {
							$newLevel				= $levelUp[$student_level];
							$newSQL					= "select * from wpw1_cwa_consolidated_student 
													   where call_sign = '$student_call_sign' 
													   and level = '$newLevel' 
													   and response = 'Y' 
													   order by date_created 
													   limit 1";
							$levelUp_student		= $wpdb->get_results($newSQL);
							if ($levelUp_student === FALSE) {
								$myError			= $wpdb->last_error;
								$myQuery			= $wpdb->last_query;
								if ($doDebug) {
									echo "Reading $studentTableName table failed<br />
										  wpdb->last_query: $myQuery<br />
										  wpdb->last_error: $myError<br />";
								}
								$errorMsg			= "$jobname reading $studentTableName failed. <p>SQL: $myQuery</p><p> Error: $myError</p>";
								sendErrorEmail($errorMsg);
								$content		.= "Unable to obtain content from $studentTableName<br />";
							} else {
								$numSRows			= $wpdb->num_rows;
								if ($doDebug) {
									$myStr			= $wpdb->last_query;
									echo "ran $myStr<br />and found $numSRows rows<br />";
								}
								if ($numSRows > 0) {
									foreach ($levelUp_student as $levelUpRow) {
										$levelUp_level  						= $levelUpRow->level;
										$levelUp_semester						= $levelUpRow->semester;
										$levelUp_response  						= strtoupper($levelUpRow->response);
										$levelUp_status  						= strtoupper($levelUpRow->student_status);
										$levelUp_promotable  					= $levelUpRow->promotable;
									}
						
									$advisorArray[$studentLevel][$student_assigned_advisor]['nextlevel']++;
									$overallArray[$studentLevel]['nextlevel']++;
									if ($levelUp_status == 'C') { 									
										$advisorArray[$studentLevel][$student_assigned_advisor]['nextdropped']++;
										$overallArray[$studentLevel]['nextdropped']++;
										if ($doDebug) {
											echo "counted nextdropped<br />";
										}
									} elseif ($levelUp_status == 'Y' || $levelUp_status == 'S') {
										if ($levelUp_promotable == 'P') {										 
											$advisorArray[$studentLevel][$student_assigned_advisor]['nextpromotable']++;
											$overallArray[$studentLevel]['nextpromotable']++;
											if ($doDebug) {
												echo "counted nextpromotable<br />";
											}
										} elseif ($levelUp_promotable == 'N') {
											$advisorArray[$studentLevel][$student_assigned_advisor]['nextnotpromotable']++;
											$overallArray[$studentLevel]['nextnotpromotable']++;
											if ($doDebug) {
												echo "counted nextnotpromotable<br />";
											}
										} elseif ($levelUp_promotable == 'W') {
											$advisorArray[$studentLevel][$student_assigned_advisor]['nextwithdrew']++;
											$overallArray[$studentLevel]['nextwithdrew']++;
											if ($doDebug) {
												echo "counted nextwithdrew<br />";
											}
										} else {
											$advisorArray[$studentLevel][$student_assigned_advisor]['nextnoteval']++;
											$overallArray[$studentLevel]['nextnoteval']++;
											if ($doDebug) {
												echo "counted nextnoteval<br />";
											}
										}
									}
								}
							}
						}
						
					}
					if ($doDebug) {
						echo "<br />advisorArray:<br /><pre>";
						print_r($advisorArray[$studentLevel][$student_assigned_advisor]);
						echo "</pre><br />";
					}

				}					/// end of foreach
				
				if ($doDebug) {
					echo "<br />advisorArray:<br /><pre>";
					print_r($advisorArray);
					echo "</pre><br />";
				}

				if ($doDebug) {
					echo "<br /><br />overallArray:<br /><pre>";
					print_r($overallArray);
					echo "</pre><br />";
				}
				
				// calculate the averages and standard deviations from the advisorArray
				$droppedArray					= array();
				$promotableArray				= array();
				$notPromotableArray				= array();
				$withdrewArray					= array();
				$notevalArray					= array();
				$nextLevelArray					= array();
				$nextdroppedArray				= array();
				$nextpromotableArray			= array();
				$nextnotpromotableArray			= array();
				$nextwithdrewArray				= array();
				$nextnotevalArray				= array();
				
				$BeginnerDroppedAvg				= 0.0;
				$BeginnerDroppedStdDev			= 0.0;
				$BeginnerDroppedLow				= 0.0;
				$BeginnerDroppedHigh			= 0.0;
				
				$BeginnerPromotableAvg			= 0.0;
				$BeginnerPromotableStdDev		= 0.0;
				$BeginnerPromotableLow			= 0.0;
				$BeginnerPromotableHigh			= 0.0;
				
				$BeginnerNotPromotableAvg			= 0.0;
				$BeginnerNotPromotableStdDev		= 0.0;
				$BeginnerNotPromotableLow			= 0.0;
				$BeginnerNotPromotableHigh			= 0.0;

				$BeginnerWithdrewAvg			= 0.0;
				$BeginnerWithdrewStdDev			= 0.0;
				$BeginnerWithdrewLow			= 0.0;
				$BeginnerWithdrewHigh			= 0.0;

				$BeginnerNotEvalAvg			= 0.0;
				$BeginnerNotEvalStdDev		= 0.0;
				$BeginnerNotEvalLow			= 0.0;
				$BeginnerNotEvalHigh		= 0.0;

				$BeginnerNextLevelAvg			= 0.0;
				$BeginnerNextLevelStdDev		= 0.0;
				$BeginnerNextLevelLow			= 0.0;
				$BeginnerNextLevelHigh			= 0.0;

				$BeginnerNextDroppedAvg			= 0.0;
				$BeginnerNextDroppedStdDev		= 0.0;
				$BeginnerNextDroppedLow			= 0.0;
				$BeginnerNextDroppedHigh		= 0.0;

				$BeginnerNextPromotableAvg			= 0.0;
				$BeginnerNextPromotableStdDev		= 0.0;
				$BeginnerNextPromotableLow			= 0.0;
				$BeginnerNextPromotableHigh			= 0.0;

				$BeginnerNextNotPromotableAvg			= 0.0;
				$BeginnerNextNotPromotableStdDev		= 0.0;
				$BeginnerNextNotPromotableLow			= 0.0;
				$BeginnerNextNotPromotableHigh			= 0.0;

				$BeginnerNextWithdrewAvg			= 0.0;
				$BeginnerNextWithdrewStdDev			= 0.0;
				$BeginnerNextWithdrewLow			= 0.0;
				$BeginnerNextWithdrewHigh			= 0.0;

				$BeginnerNextNotEvalAvg			= 0.0;
				$BeginnerNextNotEvalStdDev		= 0.0;
				$BeginnerNextNotEvalLow			= 0.0;
				$BeginnerNextNotEvalHigh		= 0.0;

				$FundamentalDroppedAvg				= 0.0;
				$FundamentalDroppedStdDev			= 0.0;
				$FundamentalDroppedLow				= 0.0;
				$FundamentalDroppedHigh			= 0.0;
				
				$FundamentalPromotableAvg			= 0.0;
				$FundamentalPromotableStdDev		= 0.0;
				$FundamentalPromotableLow			= 0.0;
				$FundamentalPromotableHigh			= 0.0;
				
				$FundamentalNotPromotableAvg			= 0.0;
				$FundamentalNotPromotableStdDev		= 0.0;
				$FundamentalNotPromotableLow			= 0.0;
				$FundamentalNotPromotableHigh			= 0.0;

				$FundamentalWithdrewAvg			= 0.0;
				$FundamentalWithdrewStdDev			= 0.0;
				$FundamentalWithdrewLow			= 0.0;
				$FundamentalWithdrewHigh			= 0.0;

				$FundamentalNotEvalAvg			= 0.0;
				$FundamentalNotEvalStdDev		= 0.0;
				$FundamentalNotEvalLow			= 0.0;
				$FundamentalNotEvalHigh		= 0.0;

				$FundamentalNextLevelAvg			= 0.0;
				$FundamentalNextLevelStdDev		= 0.0;
				$FundamentalNextLevelLow			= 0.0;
				$FundamentalNextLevelHigh			= 0.0;

				$FundamentalNextDroppedAvg			= 0.0;
				$FundamentalNextDroppedStdDev		= 0.0;
				$FundamentalNextDroppedLow			= 0.0;
				$FundamentalNextDroppedHigh		= 0.0;

				$FundamentalNextPromotableAvg			= 0.0;
				$FundamentalNextPromotableStdDev		= 0.0;
				$FundamentalNextPromotableLow			= 0.0;
				$FundamentalNextPromotableHigh			= 0.0;

				$FundamentalNextNotPromotableAvg			= 0.0;
				$FundamentalNextNotPromotableStdDev		= 0.0;
				$FundamentalNextNotPromotableLow			= 0.0;
				$FundamentalNextNotPromotableHigh			= 0.0;

				$FundamentalNextWithdrewAvg			= 0.0;
				$FundamentalNextWithdrewStdDev			= 0.0;
				$FundamentalNextWithdrewLow			= 0.0;
				$FundamentalNextWithdrewHigh			= 0.0;

				$FundamentalNextNotEvalAvg			= 0.0;
				$FundamentalNextNotEvalStdDev		= 0.0;
				$FundamentalNextNotEvalLow			= 0.0;
				$FundamentalNextNotEvalHigh		= 0.0;


				$IntermediateDroppedAvg				= 0.0;
				$IntermediateDroppedStdDev			= 0.0;
				$IntermediateDroppedLow				= 0.0;
				$IntermediateDroppedHigh			= 0.0;
				
				$IntermediatePromotableAvg			= 0.0;
				$IntermediatePromotableStdDev		= 0.0;
				$IntermediatePromotableLow			= 0.0;
				$IntermediatePromotableHigh			= 0.0;
				
				$IntermediateNotPromotableAvg			= 0.0;
				$IntermediateNotPromotableStdDev		= 0.0;
				$IntermediateNotPromotableLow			= 0.0;
				$IntermediateNotPromotableHigh			= 0.0;

				$IntermediateWithdrewAvg			= 0.0;
				$IntermediateWithdrewStdDev			= 0.0;
				$IntermediateWithdrewLow			= 0.0;
				$IntermediateWithdrewHigh			= 0.0;

				$IntermediateNotEvalAvg			= 0.0;
				$IntermediateNotEvalStdDev		= 0.0;
				$IntermediateNotEvalLow			= 0.0;
				$IntermediateNotEvalHigh		= 0.0;

				$IntermediateNextLevelAvg			= 0.0;
				$IntermediateNextLevelStdDev		= 0.0;
				$IntermediateNextLevelLow			= 0.0;
				$IntermediateNextLevelHigh			= 0.0;

				$IntermediateNextDroppedAvg			= 0.0;
				$IntermediateNextDroppedStdDev		= 0.0;
				$IntermediateNextDroppedLow			= 0.0;
				$IntermediateNextDroppedHigh		= 0.0;

				$IntermediateNextPromotableAvg			= 0.0;
				$IntermediateNextPromotableStdDev		= 0.0;
				$IntermediateNextPromotableLow			= 0.0;
				$IntermediateNextPromotableHigh			= 0.0;

				$IntermediateNextNotPromotableAvg			= 0.0;
				$IntermediateNextNotPromotableStdDev		= 0.0;
				$IntermediateNextNotPromotableLow			= 0.0;
				$IntermediateNextNotPromotableHigh			= 0.0;

				$IntermediateNextWithdrewAvg			= 0.0;
				$IntermediateNextWithdrewStdDev			= 0.0;
				$IntermediateNextWithdrewLow			= 0.0;
				$IntermediateNextWithdrewHigh			= 0.0;

				$IntermediateNextNotEvalAvg			= 0.0;
				$IntermediateNextNotEvalStdDev		= 0.0;
				$IntermediateNextNotEvalLow			= 0.0;
				$IntermediateNextNotEvalHigh		= 0.0;

				$AdvancedDroppedAvg				= 0.0;
				$AdvancedDroppedStdDev			= 0.0;
				$AdvancedDroppedLow				= 0.0;
				$AdvancedDroppedHigh			= 0.0;
				
				$AdvancedPromotableAvg			= 0.0;
				$AdvancedPromotableStdDev		= 0.0;
				$AdvancedPromotableLow			= 0.0;
				$AdvancedPromotableHigh			= 0.0;
				
				$AdvancedNotPromotableAvg			= 0.0;
				$AdvancedNotPromotableStdDev		= 0.0;
				$AdvancedNotPromotableLow			= 0.0;
				$AdvancedNotPromotableHigh			= 0.0;

				$AdvancedWithdrewAvg			= 0.0;
				$AdvancedWithdrewStdDev			= 0.0;
				$AdvancedWithdrewLow			= 0.0;
				$AdvancedWithdrewHigh			= 0.0;

				$AdvancedNotEvalAvg			= 0.0;
				$AdvancedNotEvalStdDev		= 0.0;
				$AdvancedNotEvalLow			= 0.0;
				$AdvancedNotEvalHigh		= 0.0;

				$AdvancedNextLevelAvg			= 0.0;
				$AdvancedNextLevelStdDev		= 0.0;
				$AdvancedNextLevelLow			= 0.0;
				$AdvancedNextLevelHigh			= 0.0;

				$AdvancedNextDroppedAvg			= 0.0;
				$AdvancedNextDroppedStdDev		= 0.0;
				$AdvancedNextDroppedLow			= 0.0;
				$AdvancedNextDroppedHigh		= 0.0;

				$AdvancedNextPromotableAvg			= 0.0;
				$AdvancedNextPromotableStdDev		= 0.0;
				$AdvancedNextPromotableLow			= 0.0;
				$AdvancedNextPromotableHigh			= 0.0;

				$AdvancedNextNotPromotableAvg			= 0.0;
				$AdvancedNextNotPromotableStdDev		= 0.0;
				$AdvancedNextNotPromotableLow			= 0.0;
				$AdvancedNextNotPromotableHigh			= 0.0;

				$AdvancedNextWithdrewAvg			= 0.0;
				$AdvancedNextWithdrewStdDev			= 0.0;
				$AdvancedNextWithdrewLow			= 0.0;
				$AdvancedNextWithdrewHigh			= 0.0;

				$AdvancedNextNotEvalAvg			= 0.0;
				$AdvancedNextNotEvalStdDev		= 0.0;
				$AdvancedNextNotEvalLow			= 0.0;
				$AdvancedNextNotEvalHigh		= 0.0;


				foreach($advisorArray as $thisLevel=>$thisData) {
					if ($doDebug) {
						echo "<br />Calculating statistics for level $thisLevel<br />";
					}
					foreach($thisData as $thisAdvisor=>$thisValue) {
						$thisStudents		= $thisValue['studentcount'];
						$thisDropped		= $thisValue['dropped'];
						$thisDroppedPC		= $thisDropped / $thisStudents;
						$droppedArray[]		= $thisDroppedPC;
						
						$thisPromotable		= $thisValue['promotable'];
						$thisPromotablePC	= $thisPromotable / $thisStudents;
						$promotableArray[]	= $thisPromotablePC;

						$thisNotPromotable		= $thisValue['notpromotable'];
						$thisNotPromotablePC	= $thisNotPromotable / $thisStudents;
						$notpromotableArray[]	= $thisNotPromotablePC;

						$thisWithdrew		= $thisValue['withdrew'];
						$thisWithdrewPC		= $thisWithdrew / $thisStudents;
						$withdrewArray[]	= $thisWithdrewPC;

						$thisNotEval		= $thisValue['noteval'];
						$thisNotEvalPC		= $thisNotEval / $thisStudents;
						$notevalArray[]		= $thisNotEvalPC;

						if ($thisLevel != 4) {
							$thisNextLevel		= $thisValue['nextlevel'];
							$thisNextLevelPC	= $thisNextLevel / $thisStudents;
							$nextlevelArray[]	= $thisNextLevelPC;

							$thisNextDropped		= $thisValue['nextdropped'];
							$thisNextDroppedPC		= $thisNextDropped / $thisStudents;
							$nextdroppedArray[]		= $thisNextDroppedPC;

							$thisNextPromotable		= $thisValue['nextpromotable'];
							$thisNextPromotablePC	= $thisNextPromotable / $thisStudents;
							$nextpromotableArray[]	= $thisNextPromotablePC;

							$thisNextNotPromotable		= $thisValue['nextnotpromotable'];
							$thisNextNotPromotablePC	= $thisNextNotPromotable / $thisStudents;
							$nextnotpromotableArray[]	= $thisNextNotPromotablePC;

							$thisNextWithdrew		= $thisValue['nextwithdrew'];
							$thisNextWithdrewPC		= $thisNextWithdrew / $thisStudents;
							$nextwithdrewArray[]	= $thisNextWithdrewPC;

							$thisNextNotEval		= $thisValue['nextnoteval'];
							$thisNextNotEvalPC		= $thisNextNotEval / $thisStudents;
							$nextnotevalArray[]		= $thisNextNotEvalPC;
						}
					}	//// end of foreach for all advisors for this level
					
					
					if ($thisLevel == 1) {
						$useLevel				= "Beginner";
					} elseif ($thisLevel == 2) {
						$useLevel				= "Fundamental";
					} elseif ($thisLevel == 3) {
						$useLevel				= "Intermediate";
					} else {
						$useLevel				= "Advanced";
					}
					
					$result								= Stand_Deviation($droppedArray);
					$resultAvg							= $result[0];
					$resultStdDev						= $result[1];
					${$useLevel . 'DroppedAvg'}			= $resultAvg;
					${$useLevel . 'DroppedStdDev'}		= $resultStdDev;
					${$useLevel . 'DroppedLow'}		= round(($resultAvg - $resultStdDev) * 100,1); 
					if (${$useLevel . 'DroppedLow'} < 0.0) {
						${$useLevel . 'DroppedLow'}	= 0.0;
					}					
					${$useLevel . 'DroppedHigh'}		= round(($resultAvg + $resultStdDev) * 100,1); 
					
					if ($doDebug) {
						echo "calculated $useLevel Dropped information:<br />
							  DroppedAvg: ${$useLevel . 'DroppedAvg'}<br />
							  DroppedStdDev: ${$useLevel . 'DroppedStdDev'}<br />
							  DroppedLow: ${$useLevel . 'DroppedLow'}<br />
							  DroppedHigh: ${$useLevel . 'DroppedHigh'}<br />";
					}

					$result								= Stand_Deviation($promotableArray);
					$resultAvg							= $result[0];
					$resultStdDev						= $result[1];
					${$useLevel . 'PromotableAvg'}			= $resultAvg;
					${$useLevel . 'PromotableStdDev'}		= $resultStdDev;
					${$useLevel . 'PromotableLow'}		= round(($resultAvg - $resultStdDev) * 100,1); 
					if (${$useLevel . 'PromotableLow'} < 0.0) {
						${$useLevel . 'PromotableLow'}	= 0.0;
					}					
					${$useLevel . 'PromotableHigh'}		= round(($resultAvg + $resultStdDev) * 100,1); 
					
					if ($doDebug) {
						echo "calculated $useLevel Promotable information:<br />
							  PromotableAvg: ${$useLevel . 'PromotableAvg'}<br />
							  PromotableStdDev: ${$useLevel . 'PromotableStdDev'}<br />
							  PromotableLow: ${$useLevel . 'PromotableLow'}<br />
							  PromotableHigh: ${$useLevel . 'PromotableHigh'}<br />";
					}
					
					$result								= Stand_Deviation($notpromotableArray);
					$resultAvg							= $result[0];
					$resultStdDev						= $result[1];
					${$useLevel . 'NotPromotableAvg'}			= $resultAvg;
					${$useLevel . 'NotPromotableStdDev'}		= $resultStdDev;
					${$useLevel . 'NotPromotableLow'}		= round(($resultAvg - $resultStdDev) * 100,1); 
					if (${$useLevel . 'NotPromotableLow'} < 0.0) {
						${$useLevel . 'NotPromotableLow'}	= 0.0;
					}					
					${$useLevel . 'NotPromotableHigh'}		= round(($resultAvg + $resultStdDev) * 100,1); 
					
					if ($doDebug) {
						echo "calculated $useLevel NotPromotable information:<br />
							  NotPromotableAvg: ${$useLevel . 'NotPromotableAvg'}<br />
							  NotPromotableStdDev: ${$useLevel . 'NotPromotableStdDev'}<br />
							  NotPromotableLow: ${$useLevel . 'NotPromotableLow'}<br />
							  NotPromotableHigh: ${$useLevel . 'NotPromotableHigh'}<br />";
					}

					$result								= Stand_Deviation($withdrewArray);
					$resultAvg							= $result[0];
					$resultStdDev						= $result[1];
					${$useLevel . 'WithdrewAvg'}			= $resultAvg;
					${$useLevel . 'WithdrewStdDev'}		= $resultStdDev;
					${$useLevel . 'WithdrewLow'}		= round(($resultAvg - $resultStdDev) * 100,1); 
					if (${$useLevel . 'WithdrewLow'} < 0.0) {
						${$useLevel . 'WithdrewLow'}	= 0.0;
					}					
					${$useLevel . 'WithdrewHigh'}		= round(($resultAvg + $resultStdDev) * 100,1); 
					
					if ($doDebug) {
						echo "calculated $useLevel Withdrew information:<br />
							  WithdrewAvg: ${$useLevel . 'WithdrewAvg'}<br />
							  WithdrewStdDev: ${$useLevel . 'WithdrewStdDev'}<br />
							  WithdrewLow: ${$useLevel . 'WithdrewLow'}<br />
							  WithdrewHigh: ${$useLevel . 'WithdrewHigh'}<br />";
					}

					$result								= Stand_Deviation($notevalArray);
					$resultAvg							= $result[0];
					$resultStdDev						= $result[1];
					${$useLevel . 'NotEvalAvg'}			= $resultAvg;
					${$useLevel . 'NotEvalStdDev'}		= $resultStdDev;
					${$useLevel . 'NotEvalLow'}		= round(($resultAvg - $resultStdDev) * 100,1); 
					if (${$useLevel . 'NotEvalLow'} < 0.0) {
						${$useLevel . 'NotEvalLow'}	= 0.0;
					}					
					${$useLevel . 'NotEvalHigh'}		= round(($resultAvg + $resultStdDev) * 100,1); 
					
					if ($doDebug) {
						echo "calculated $useLevel NotEval information:<br />
							  NotEvalAvg: ${$useLevel . 'NotEvalAvg'}<br />
							  NotEvalStdDev: ${$useLevel . 'NotEvalStdDev'}<br />
							  NotEvalLow: ${$useLevel . 'NotEvalLow'}<br />
							  NotEvalHigh: ${$useLevel . 'NotEvalHigh'}<br />";
					}

					$result								= Stand_Deviation($nextlevelArray);
					$resultAvg							= $result[0];
					$resultStdDev						= $result[1];
					${$useLevel . 'NextLevelAvg'}			= $resultAvg;
					${$useLevel . 'NextLevelStdDev'}		= $resultStdDev;
					${$useLevel . 'NextLevelLow'}		= round(($resultAvg - $resultStdDev) * 100,1); 
					if (${$useLevel . 'NextLevelLow'} < 0.0) {
						${$useLevel . 'NextLevelLow'}	= 0.0;
					}					
					${$useLevel . 'NextLevelHigh'}		= round(($resultAvg + $resultStdDev) * 100,1); 
					
					if ($doDebug) {
						echo "calculated $useLevel NextLevel information:<br />
							  NextLevelAvg: ${$useLevel . 'NextLevelAvg'}<br />
							  NextLevelStdDev: ${$useLevel . 'NextLevelStdDev'}<br />
							  NextLevelLow: ${$useLevel . 'NextLevelLow'}<br />
							  NextLevelHigh: ${$useLevel . 'NextLevelHigh'}<br />";
					}
					
					$result								= Stand_Deviation($nextdroppedArray);
					$resultAvg							= $result[0];
					$resultStdDev						= $result[1];
					${$useLevel . 'NextDroppedAvg'}			= $resultAvg;
					${$useLevel . 'NextDroppedStdDev'}		= $resultStdDev;
					${$useLevel . 'NextDroppedLow'}		= round(($resultAvg - $resultStdDev) * 100,1); 
					if (${$useLevel . 'NextDroppedLow'} < 0.0) {
						${$useLevel . 'NextDroppedLow'}	= 0.0;
					}					
					${$useLevel . 'NextDroppedHigh'}		= round(($resultAvg + $resultStdDev) * 100,1); 
					
					if ($doDebug) {
						echo "calculated $useLevel NextDropped information:<br />
							  NextDroppedAvg: ${$useLevel . 'NextDroppedAvg'}<br />
							  NextDroppedStdDev: ${$useLevel . 'NextDroppedStdDev'}<br />
							  NextDroppedLow: ${$useLevel . 'NextDroppedLow'}<br />
							  NextDroppedHigh: ${$useLevel . 'NextDroppedHigh'}<br />";
					}

					$result								= Stand_Deviation($nextpromotableArray);
					$resultAvg							= $result[0];
					$resultStdDev						= $result[1];
					${$useLevel . 'NextPromotableAvg'}			= $resultAvg;
					${$useLevel . 'NextPromotableStdDev'}		= $resultStdDev;
					${$useLevel . 'NextPromotableLow'}		= round(($resultAvg - $resultStdDev) * 100,1); 
					if (${$useLevel . 'NextPromotableLow'} < 0.0) {
						${$useLevel . 'NextPromotableLow'}	= 0.0;
					}					
					${$useLevel . 'NextPromotableHigh'}		= round(($resultAvg + $resultStdDev) * 100,1); 
					
					if ($doDebug) {
						echo "calculated $useLevel NextPromotable information:<br />
							  NextPromotableAvg: ${$useLevel . 'NextPromotableAvg'}<br />
							  NextPromotableStdDev: ${$useLevel . 'NextPromotableStdDev'}<br />
							  NextPromotableLow: ${$useLevel . 'NextPromotableLow'}<br />
							  NextPromotableHigh: ${$useLevel . 'NextPromotableHigh'}<br />";
					}

					$result								= Stand_Deviation($nextnotpromotableArray);
					$resultAvg							= $result[0];
					$resultStdDev						= $result[1];
					${$useLevel . 'NextNotPromotableAvg'}			= $resultAvg;
					${$useLevel . 'NextNotPromotableStdDev'}		= $resultStdDev;
					${$useLevel . 'NextNotPromotableLow'}		= round(($resultAvg - $resultStdDev) * 100,1); 
					if (${$useLevel . 'NextNotPromotableLow'} < 0.0) {
						${$useLevel . 'NextNotPromotableLow'}	= 0.0;
					}					
					${$useLevel . 'NextNotPromotableHigh'}		= round(($resultAvg + $resultStdDev) * 100,1); 
					
					if ($doDebug) {
						echo "calculated $useLevel NextNotPromotable information:<br />
							  NextNotPromotableAvg: ${$useLevel . 'NextNotPromotableAvg'}<br />
							  NextNotPromotableStdDev: ${$useLevel . 'NextNotPromotableStdDev'}<br />
							  NextNotPromotableLow: ${$useLevel . 'NextNotPromotableLow'}<br />
							  NextNotPromotableHigh: ${$useLevel . 'NextNotPromotableHigh'}<br />";
					}

					$result								= Stand_Deviation($nextwithdrewArray);
					$resultAvg							= $result[0];
					$resultStdDev						= $result[1];
					${$useLevel . 'NextWithdrewAvg'}			= $resultAvg;
					${$useLevel . 'NextWithdrewStdDev'}		= $resultStdDev;
					${$useLevel . 'NextWithdrewLow'}		= round(($resultAvg - $resultStdDev) * 100,1); 
					if (${$useLevel . 'NextWithdrewLow'} < 0.0) {
						${$useLevel . 'NextWithdrewLow'}	= 0.0;
					}					
					${$useLevel . 'NextWithdrewHigh'}		= round(($resultAvg + $resultStdDev) * 100,1); 
					
					if ($doDebug) {
						echo "calculated $useLevel NextWithdrew information:<br />
							  NextWithdrewAvg: ${$useLevel . 'NextWithdrewAvg'}<br />
							  NextWithdrewStdDev: ${$useLevel . 'NextWithdrewStdDev'}<br />
							  NextWithdrewLow: ${$useLevel . 'NextWithdrewLow'}<br />
							  NextWithdrewHigh: ${$useLevel . 'NextWithdrewHigh'}<br />";
					}

					$result								= Stand_Deviation($nextnotevalArray);
					$resultAvg							= $result[0];
					$resultStdDev						= $result[1];
					${$useLevel . 'NextNotEvalAvg'}			= $resultAvg;
					${$useLevel . 'NextNotEvalStdDev'}		= $resultStdDev;
					${$useLevel . 'NextNotEvalLow'}		= round(($resultAvg - $resultStdDev) * 100,1); 
					if (${$useLevel . 'NextNotEvalLow'} < 0.0) {
						${$useLevel . 'NextNotEvalLow'}	= 0.0;
					}					
					${$useLevel . 'NextNotEvalHigh'}		= round(($resultAvg + $resultStdDev) * 100,1); 
					
					if ($doDebug) {
						echo "calculated $useLevel NextNotEval information:<br />
							  NextNotEvalAvg: ${$useLevel . 'NextNotEvalAvg'}<br />
							  NextNotEvalStdDev: ${$useLevel . 'NextNotEvalStdDev'}<br />
							  NextNotEvalLow: ${$useLevel . 'NextNotEvalLow'}<br />
							  NextNotEvalHigh: ${$useLevel . 'NextNotEvalHigh'}<br />";
					}
					if ($doDebug) {
						echo "<br />";
					}

				}		/// end of the array

				// calculate the averages and spread and have them ready to display
				$myArray				= array('Beginner',
												'Fundamental',
												'Intermediate',
												'Advanced');
				foreach($myArray as $thisLevel) {
					if(${$thisLevel . 'DroppedAvg'} > 0) {
						$num1			= round(${$thisLevel . 'DroppedAvg'} * 100,1); 
						$cell1			= "<td style='text-align:center;'>$num1%<br />${$thisLevel . 'DroppedLow'}-${$thisLevel . 'DroppedHigh'}%</td>";
					} else {
						$cell1			= "<td style='text-align:center;'>--</td>";
					}
					if(${$thisLevel . 'PromotableAvg'} > 0) {
						$num2			= round(${$thisLevel . 'PromotableAvg'} * 100,1);
						$cell2			= "<td style='text-align:center;'>$num2%<br />${$thisLevel . 'PromotableLow'}-${$thisLevel . 'PromotableHigh'}%</td>";
					} else {
						$cell2			= "<td style='text-align:center;'>--</td>";
					}
					if(${$thisLevel . 'NotPromotableAvg'} > 0) {
						$num3			= round(${$thisLevel . 'NotPromotableAvg'} * 100,1);
						$cell3			= "<td style='text-align:center;'>$num3%<br />${$thisLevel . 'NotPromotableLow'}-${$thisLevel . 'NotPromotableHigh'}%</td>";
					} else {
						$cell3			= "<td style='text-align:center;'>--</td>";
					}
					if(${$thisLevel . 'WithdrewAvg'} > 0) {
						$num4			= round(${$thisLevel . 'WithdrewAvg'} * 100,1);
						$cell4			= "<td style='text-align:center;'>$num4%<br />${$thisLevel . 'WithdrewLow'}-${$thisLevel . 'WithdrewHigh'}%</td>";
					} else {
						$cell4			= "<td style='text-align:center;'>--</td>";
					}
					if(${$thisLevel . 'NotEvalAvg'} > 0) {
						$num5			= round(${$thisLevel . 'NotEvalAvg'} * 100,1);
						$cell5			= "<td style='text-align:center;'>$num5%<br />${$thisLevel . 'NotEvalLow'}-${$thisLevel . 'NotEvalHigh'}%</td>";
					} else {
						$cell5			= "<td style='text-align:center;'>--</td>";
					}
					if ($thisLevel != 'Advanced') {
						if(${$thisLevel . 'NextLevelAvg'} > 0) {
							$num6			= round(${$thisLevel . 'NextLevelAvg'} * 100,1);
							$cell6			= "<td style='text-align:center;'>$num6%<br />${$thisLevel . 'NextLevelLow'}-${$thisLevel . 'NextLevelHigh'}%</td>";
						} else {
							$cell6			= "<td style='text-align:center;'>--</td>";
						}
						if(${$thisLevel . 'NextDroppedAvg'} > 0) {
							$num7			= round(${$thisLevel . 'NextDroppedAvg'} * 100,1);
							$cell7			= "<td style='text-align:center;'>$num7%<br />${$thisLevel . 'NextDroppedLow'}-${$thisLevel . 'NextDroppedHigh'}%</td>";
						} else {
							$cell7			= "<td style='text-align:center;'>--</td>";
						}
						if(${$thisLevel . 'NextPromotableAvg'} > 0) {
							$num8			= round(${$thisLevel . 'NextPromotableAvg'} * 100,1);
							$cell8			= "<td style='text-align:center;'>$num8%<br />${$thisLevel . 'NextPromotableLow'}-${$thisLevel . 'NextPromotableHigh'}%</td>";
						} else {
							$cell8			= "<td style='text-align:center;'>--</td>";
						}
						if(${$thisLevel . 'NextNotPromotableAvg'} > 0) {
							$num9			= round(${$thisLevel . 'NextNotPromotableAvg'} * 100,1);
							$cell9			= "<td style='text-align:center;'>$num9%<br />${$thisLevel . 'NextNotPromotableLow'}-${$thisLevel . 'NextNotPromotableHigh'}%</td>";
						} else {
							$cell9			= "<td style='text-align:center;'>--</td>";
						}
						if(${$thisLevel . 'NextWithdrewAvg'} > 0) {
							$num10			= round(${$thisLevel . 'NextWithdrewAvg'} * 100,1);
							$cell10			= "<td style='text-align:center;'>$num10%<br />${$thisLevel . 'NextWithdrewLow'}-${$thisLevel . 'NextWithdrewHigh'}%</td>";
						} else {
							$cell10			= "<td style='text-align:center;'>--</td>";
						}
						if(${$thisLevel . 'NextNotEvalAvg'} > 0) {
							$num11			= round(${$thisLevel . 'NextNotEvalAvg'} * 100,1);
							$cell11			= "<td style='text-align:center;'>$num11%<br />${$thisLevel . 'NextNotEvalLow'}-${$thisLevel . 'NextNotEvalHigh'}%</td>";
						} else {
							$cell11			= "<td style='text-align:center;'>--</td>";
						}
					} else {
							$cell6			= "<td style='text-align:center;'>--</td>";
							$cell7			= "<td style='text-align:center;'>--</td>";
							$cell8			= "<td style='text-align:center;'>--</td>";
							$cell9			= "<td style='text-align:center;'>--</td>";
							$cell10			= "<td style='text-align:center;'>--</td>";
							$cell11			= "<td style='text-align:center;'>--</td>";
					}
					${$thisLevel . 'Avg'}	= "<tr><td>$thisLevel</td>
												<td colspan='2'>Average</td>
												$cell1
												$cell2
												$cell3
												$cell4
												$cell5
												$cell6
												$cell7
												$cell8
												$cell9
												$cell10
												$cell11</tr>";
				
				}
				
				// put out the results table
				$content				.= "<h3>$jobname</h3>
											<table style='width:1300px;'>";
				$newAdvisor				= TRUE;
				$newLevel				= TRUE;
				ksort($advisorArray);
				foreach($advisorArray as $thisLevel=>$thisValue) {
					$content			.= "<tr><th>Level</th>
											<th>Advisor</th>
											<th>Students</th>
											<th>Dropped</th>
											<th>Promotable</th>
											<th>Not Promotable</th>
											<th>Withdrew</th>
											<th>Not Eval</th>
											<th>Next Level</th>
											<th>Dropped</th>
											<th>Promotable</th>
											<th>Not Promotable</th>
											<th>Withdrew</th>
											<th>Not Eval</th></tr>";

					$displayLevel		= $printLevel[$thisLevel];
					$newLevel			= TRUE;
					$firstTime			= TRUE;
					ksort($thisValue);
					foreach($thisValue as $thisAdvisor=>$thisData) {
						if ($newLevel) {
							$levelCell		= $displayLevel;
							$newLevel		= FALSE;
							$content		.= ${$displayLevel . 'Avg'};
						} else {
							$levelCell		= '';
						}
						$thisStudents		= $thisData['studentcount'];
						if ($thisStudents > 9) {
							$thisDropped		= $thisData['dropped'];
							$thisDroppedPC		= round($thisDropped / $thisStudents * 100,1);
							if ($thisDroppedPC < ${$displayLevel . 'DroppedLow'}) {
								$cell1			= "<td style='text-align:center;background-color:Yellow;'>";
							} elseif ($thisDroppedPC > ${$displayLevel . 'DroppedHigh'}) {
								$cell1			= "<td style='text-align:center;background-color:LightPink;'>";
							} else {
								$cell1			= "<td style='text-align:center;background-color:LightGreen;'>";
							}
							$cell1				.= "$thisDropped<br />$thisDroppedPC%</td>";
						
							$thisPromotable		= $thisData['promotable'];
							$thisPromotablePC	= round($thisPromotable / $thisStudents * 100,1);
							if ($thisPromotablePC < ${$displayLevel . 'PromotableLow'}) {
								$cell2			= "<td style='text-align:center;background-color:Yellow;'>";
							} elseif ($thisPromotablePC > ${$displayLevel . 'PromotableHigh'}) {
								$cell2			= "<td style='text-align:center;background-color:LightPink;'>";
							} else {
								$cell2			= "<td style='text-align:center;background-color:LightGreen;'>";
							}
							$cell2				.= "$thisPromotable<br />$thisPromotablePC%</td>";

							$thisNotPromotable		= $thisData['notpromotable'];
							$thisNotPromotablePC	= round($thisNotPromotable / $thisStudents * 100,1);
							if ($thisNotPromotablePC < ${$displayLevel . 'NotPromotableLow'}) {
								$cell3			= "<td style='text-align:center;background-color:Yellow;'>";
							} elseif ($thisNotPromotablePC > ${$displayLevel . 'NotPromotableHigh'}) {
								$cell3			= "<td style='text-align:center;background-color:LightPink;'>";
							} else {
								$cell3			= "<td style='text-align:center;background-color:LightGreen;'>";
							}
							$cell3				.= "$thisNotPromotable<br />$thisNotPromotablePC%</td>";

							$thisWithdrew		= $thisData['withdrew'];
							$thisWithdrewPC		= round($thisWithdrew / $thisStudents * 100,1);
							if ($thisWithdrewPC < ${$displayLevel . 'WithdrewLow'}) {
								$cell4			= "<td style='text-align:center;background-color:Yellow;'>";
							} elseif ($thisWithdrewPC > ${$displayLevel . 'WithdrewHigh'}) {
								$cell4			= "<td style='text-align:center;background-color:LightPink;'>";
							} else {
								$cell4			= "<td style='text-align:center;background-color:LightGreen;'>";
							}
							$cell4				.= "$thisWithdrew<br />$thisWithdrewPC%</td>";

							$thisNotEval		= $thisData['noteval'];
							$thisNotEvalPC		= round($thisNotEval / $thisStudents * 100,1);
							if ($thisNotEvalPC < ${$displayLevel . 'NotEvalLow'}) {
								$cell5			= "<td style='text-align:center;background-color:Yellow;'>";
							} elseif ($thisNotEvalPC > ${$displayLevel . 'NotEvalHigh'}) {
								$cell5			= "<td style='text-align:center;background-color:LightPink;'>";
							} else {
								$cell5			= "<td style='text-align:center;background-color:LightGreen;'>";
							}
							$cell5				.= "$thisNotEval<br />$thisNotEvalPC%</td>";

							$thisNextLevel		= $thisData['nextlevel'];
							$thisNextLevelPC	= round($thisNextLevel / $thisStudents * 100,1);
							if ($thisNextLevelPC < ${$displayLevel . 'NextLevelLow'}) {
								$cell6			= "<td style='text-align:center;background-color:Yellow;'>";
							} elseif ($thisNextLevelPC > ${$displayLevel . 'NextLevelHigh'}) {
								$cell6			= "<td style='text-align:center;background-color:LightPink;'>";
							} else {
								$cell6			= "<td style='text-align:center;background-color:LightGreen;'>";
							}
							$cell6				.= "$thisNextLevel<br />$thisNextLevelPC%</td>";

							$thisNextDropped		= $thisData['nextdropped'];
							$thisNextDroppedPC		= round($thisNextDropped / $thisStudents * 100,1);
							if ($thisNextDroppedPC < ${$displayLevel . 'NextDroppedLow'}) {
								$cell7			= "<td style='text-align:center;background-color:Yellow;'>";
							} elseif ($thisNextDroppedPC > ${$displayLevel . 'NextDroppedHigh'}) {
								$cell7			= "<td style='text-align:center;background-color:LightPink;'>";
							} else {
								$cell7			= "<td style='text-align:center;background-color:LightGreen;'>";
							}
							$cell7				.= "$thisNextDropped<br />$thisNextDroppedPC%</td>";

							$thisNextPromotable		= $thisData['nextpromotable'];
							$thisNextPromotablePC	= round($thisNextPromotable / $thisStudents * 100,1);
							if ($thisNextPromotablePC < ${$displayLevel . 'NextPromotableLow'}) {
								$cell8			= "<td style='text-align:center;background-color:Yellow;'>";
							} elseif ($thisNextPromotablePC > ${$displayLevel . 'NextPromotableHigh'}) {
								$cell8			= "<td style='text-align:center;background-color:LightPink;'>";
							} else {
								$cell8			= "<td style='text-align:center;background-color:LightGreen;'>";
							}
							$cell8				.= "$thisNextPromotable<br />$thisNextPromotablePC%</td>";

							$thisNextNotPromotable		= $thisData['nextnotpromotable'];
							$thisNextNotPromotablePC	= round($thisNextNotPromotable / $thisStudents * 100,1);
							if ($thisNextNotPromotablePC < ${$displayLevel . 'NextNotPromotableLow'}) {
								$cell9			= "<td style='text-align:center;background-color:Yellow;'>";
							} elseif ($thisNextNotPromotablePC > ${$displayLevel . 'NextNotPromotableHigh'}) {
								$cell9			= "<td style='text-align:center;background-color:LightPink;'>";
							} else {
								$cell9			= "<td style='text-align:center;background-color:LightGreen;'>";
							}
							$cell9				.= "$thisNextNotPromotable<br />$thisNextNotPromotablePC%</td>";

							$thisNextWithdrew		= $thisData['nextwithdrew'];
							$thisNextWithdrewPC		= round($thisNextWithdrew / $thisStudents * 100,1);
							if ($thisNextWithdrewPC < ${$displayLevel . 'NextWithdrewLow'}) {
								$cell10			= "<td style='text-align:center;background-color:Yellow;'>";
							} elseif ($thisNextWithdrewPC > ${$displayLevel . 'NextWithdrewHigh'}) {
								$cell10			= "<td style='text-align:center;background-color:LightPink;'>";
							} else {
								$cell10			= "<td style='text-align:center;background-color:LightGreen;'>";
							}
							$cell10				.= "$thisNextWithdrew<br />$thisNextWithdrewPC%</td>";

							$thisNextNotEval		= $thisData['nextnoteval'];
							$thisNextNotEvalPC		= round($thisNextNotEval / $thisStudents * 100,1);
							if ($thisNextNotEvalPC < ${$displayLevel . 'NextNotEvalLow'}) {
								$cell11			= "<td style='text-align:center;background-color:Yellow;'>";
							} elseif ($thisNextNotEvalPC > ${$displayLevel . 'NextNotEvalHigh'}) {
								$cell11			= "<td style='text-align:center;background-color:LightPink;'>";
							} else {
								$cell11			= "<td style='text-align:center;background-color:LightGreen;'>";
							}
							$cell11				.= "$thisNextNotEval<br />$thisNextNotEvalPC%</td>";

							$content		.= "<tr><td>$levelCell</td>
													<td>$thisAdvisor</td>
													<td style='text-align:center;'>$thisStudents</td>
													$cell1
													$cell2
													$cell3
													$cell4
													$cell5
													$cell6
													$cell7
													$cell8
													$cell9
													$cell10
													$cell11</tr>";
						}
					}
					// Put out the totals
					$thisStudents		= $overallArray[$thisLevel]['studentcount'];
					$thisDropped		= $overallArray[$thisLevel]['dropped'];
					$thisDroppedPC		= round($thisDropped / $thisStudents * 100,1);
					if ($thisDroppedPC < ${$displayLevel . 'DroppedLow'}) {
						$cell1			= "<td style='text-align:center;background-color:Yellow;'>";
					} elseif ($thisDroppedPC > ${$displayLevel . 'DroppedHigh'}) {
						$cell1			= "<td style='text-align:center;background-color:LightPink;'>";
					} else {
						$cell1			= "<td style='text-align:center;background-color:LightGreen;'>";
					}
					$cell1				.= "$thisDropped<br />$thisDroppedPC%</td>";
				
					$thisPromotable		= $overallArray[$thisLevel]['promotable'];
					$thisPromotablePC	= round($thisPromotable / $thisStudents * 100,1);
					if ($thisPromotablePC < ${$displayLevel . 'PromotableLow'}) {
						$cell2			= "<td style='text-align:center;background-color:Yellow;'>";
					} elseif ($thisPromotablePC > ${$displayLevel . 'PromotableHigh'}) {
						$cell2			= "<td style='text-align:center;background-color:LightPink;'>";
					} else {
						$cell2			= "<td style='text-align:center;background-color:LightGreen;'>";
					}
					$cell2				.= "$thisPromotable<br />$thisPromotablePC%</td>";

					$thisNotPromotable		= $overallArray[$thisLevel]['notpromotable'];
					$thisNotPromotablePC	= round($thisNotPromotable / $thisStudents * 100,1);
					if ($thisNotPromotablePC < ${$displayLevel . 'NotPromotableLow'}) {
						$cell3			= "<td style='text-align:center;background-color:Yellow;'>";
					} elseif ($thisNotPromotablePC > ${$displayLevel . 'NotPromotableHigh'}) {
						$cell3			= "<td style='text-align:center;background-color:LightPink;'>";
					} else {
						$cell3			= "<td style='text-align:center;background-color:LightGreen;'>";
					}
					$cell3				.= "$thisNotPromotable<br />$thisNotPromotablePC%</td>";

					$thisWithdrew		= $overallArray[$thisLevel]['withdrew'];
					$thisWithdrewPC		= round($thisWithdrew / $thisStudents * 100,1);
					if ($thisWithdrewPC < ${$displayLevel . 'WithdrewLow'}) {
						$cell4			= "<td style='text-align:center;background-color:Yellow;'>";
					} elseif ($thisWithdrewPC > ${$displayLevel . 'WithdrewHigh'}) {
						$cell4			= "<td style='text-align:center;background-color:LightPink;'>";
					} else {
						$cell4			= "<td style='text-align:center;background-color:LightGreen;'>";
					}
					$cell4				.= "$thisWithdrew<br />$thisWithdrewPC%</td>";

					$thisNotEval		= $overallArray[$thisLevel]['noteval'];
					$thisNotEvalPC		= round($thisNotEval / $thisStudents * 100,1);
					if ($thisNotEvalPC < ${$displayLevel . 'NotEvalLow'}) {
						$cell5			= "<td style='text-align:center;background-color:Yellow;'>";
					} elseif ($thisNotEvalPC > ${$displayLevel . 'NotEvalHigh'}) {
						$cell5			= "<td style='text-align:center;background-color:LightPink;'>";
					} else {
						$cell5			= "<td style='text-align:center;background-color:LightGreen;'>";
					}
					$cell5				.= "$thisNotEval<br />$thisNotEvalPC%</td>";

					$thisNextLevel		= $overallArray[$thisLevel]['nextlevel'];
					$thisNextLevelPC	= round($thisNextLevel / $thisStudents * 100,1);
					if ($thisNextLevelPC < ${$displayLevel . 'NextLevelLow'}) {
						$cell6			= "<td style='text-align:center;background-color:Yellow;'>";
					} elseif ($thisNextLevelPC > ${$displayLevel . 'NextLevelHigh'}) {
						$cell6			= "<td style='text-align:center;background-color:LightPink;'>";
					} else {
						$cell6			= "<td style='text-align:center;background-color:LightGreen;'>";
					}
					$cell6				.= "$thisNextLevel<br />$thisNextLevelPC%</td>";

					$thisNextDropped		= $overallArray[$thisLevel]['nextdropped'];
					$thisNextDroppedPC		= round($thisNextDropped / $thisStudents * 100,1);
					if ($thisNextDroppedPC < ${$displayLevel . 'NextDroppedLow'}) {
						$cell7			= "<td style='text-align:center;background-color:Yellow;'>";
					} elseif ($thisNextDroppedPC > ${$displayLevel . 'NextDroppedHigh'}) {
						$cell7			= "<td style='text-align:center;background-color:LightPink;'>";
					} else {
						$cell7			= "<td style='text-align:center;background-color:LightGreen;'>";
					}
					$cell7				.= "$thisNextDropped<br />$thisNextDroppedPC%</td>";

					$thisNextPromotable		= $overallArray[$thisLevel]['nextpromotable'];
					$thisNextPromotablePC	= round($thisNextPromotable / $thisStudents * 100,1);
					if ($thisNextPromotablePC < ${$displayLevel . 'NextPromotableLow'}) {
						$cell8			= "<td style='text-align:center;background-color:Yellow;'>";
					} elseif ($thisNextPromotablePC > ${$displayLevel . 'NextPromotableHigh'}) {
						$cell8			= "<td style='text-align:center;background-color:LightPink;'>";
					} else {
						$cell8			= "<td style='text-align:center;background-color:LightGreen;'>";
					}
					$cell8				.= "$thisNextPromotable<br />$thisNextPromotablePC%</td>";

					$thisNextNotPromotable		= $overallArray[$thisLevel]['nextnotpromotable'];
					$thisNextNotPromotablePC	= round($thisNextNotPromotable / $thisStudents * 100,1);
					if ($thisNextNotPromotablePC < ${$displayLevel . 'NextNotPromotableLow'}) {
						$cell9			= "<td style='text-align:center;background-color:Yellow;'>";
					} elseif ($thisNextNotPromotablePC > ${$displayLevel . 'NextNotPromotableHigh'}) {
						$cell9			= "<td style='text-align:center;background-color:LightPink;'>";
					} else {
						$cell9			= "<td style='text-align:center;background-color:LightGreen;'>";
					}
					$cell9				.= "$thisNextNotPromotable<br />$thisNextNotPromotablePC%</td>";

					$thisNextWithdrew		= $overallArray[$thisLevel]['nextwithdrew'];
					$thisNextWithdrewPC		= round($thisNextWithdrew / $thisStudents * 100,1);
					if ($thisNextWithdrewPC < ${$displayLevel . 'NextWithdrewLow'}) {
						$cell10			= "<td style='text-align:center;background-color:Yellow;'>";
					} elseif ($thisNextWithdrewPC > ${$displayLevel . 'NextWithdrewHigh'}) {
						$cell10			= "<td style='text-align:center;background-color:LightPink;'>";
					} else {
						$cell10			= "<td style='text-align:center;background-color:LightGreen;'>";
					}
					$cell10				.= "$thisNextWithdrew<br />$thisNextWithdrewPC%</td>";

					$thisNextNotEval		= $overallArray[$thisLevel]['nextnoteval'];
					$thisNextNotEvalPC		= round($thisNextNotEval / $thisStudents * 100,1);
					if ($thisNextNotEvalPC < ${$displayLevel . 'NextNotEvalLow'}) {
						$cell11			= "<td style='text-align:center;background-color:Yellow;'>";
					} elseif ($thisNextNotEvalPC > ${$displayLevel . 'NextNotEvalHigh'}) {
						$cell11			= "<td style='text-align:center;background-color:LightPink;'>";
					} else {
						$cell11			= "<td style='text-align:center;background-color:LightGreen;'>";
					}
					$cell11				.= "$thisNextNotEval<br />$thisNextNotEvalPC%</td>";

					$content		.= "<tr><td><b>$displayLevel</b></td>
											<td><b>Total</b></td>
											<td style='text-align:center;'>$thisStudents</td>
											$cell1
											$cell2
											$cell3
											$cell4
											$cell5
											$cell6
											$cell7
											$cell8
											$cell9
											$cell10
											$cell11</tr>";
				
				}
				$content			.= "</table>";
				
				
			} else {
				if ($doDebug) {
					echo "No student records found for $sql<br />";
				}
				$content			.= "No student records found";
			}
		}

	}
	$thisTime 		= date('Y-m-d H:i:s');
	$content 		.= "<br /><br /><p>Prepared at $thisTime</p>";
/*
	///// uncomment if the code to save a report is needed
	if ($doDebug) {
		echo "<br '>Checking to see if the report is to be saved. inp_rsave: $inp_rsave<br />";
	}
	if ($inp_rsave == 'Y') {
		if ($doDebug) {
			echo "Calling function to save the report as Current Student and Advisor Assignments<br />";
		}
		$storeResult	= storeReportData_func("Current Student and Advisor Assignments",$content);
		if ($storeResult !== FALSE) {
			$content	.= "<br />Report stored in reports pod as $storeResult";
		} else {
			$content	.= "<br />Storing the report in the reports pod failed";
		}
	}
*/
	$endingMicroTime = microtime(TRUE);
	$elapsedTime	= $endingMicroTime - $startingMicroTime;
	$elapsedTime	= number_format($elapsedTime, 4, '.', ',');
	$content		.= "<p>Report V$versionNumber pass $strPass took $elapsedTime seconds to run</p>";
	$nowDate		= date('Y-m-d');
	$nowTime		= date('H:i:s');
	$thisStr		= 'Production';
	if ($testMode) {
		$thisStr	= 'Testmode';
	}
	$ipAddr			= get_the_user_ip();
	$result			= write_joblog_func("$jobname|$nowDate|$nowTime|$userName|Time|$thisStr|$strPass: $elapsedTime|$ipAddr");
	if ($result == 'FAIL') {
		$content	.= "<p>writing to joblog.txt failed</p>";
	}
	return $content;
}
add_shortcode ('generate_advisor_overall_statistics_v1', 'generate_advisor_overall_statistics_v1_func');
