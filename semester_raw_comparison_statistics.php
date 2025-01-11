function semester_raw_comparison_statistics_func() {

	global $wpdb;

	$doDebug						= FALSE;
	$testMode						= FALSE;
	$initializationArray 			= data_initialization_func();
	$validUser 						= $initializationArray['validUser'];

	$versionNumber				 	= "1";
	if ($doDebug) {
		echo "Initialization Array:<br /><pre>";
		print_r($initializationArray);
		echo "</pre><br />";
	}
	$userName			= $initializationArray['userName'];
	$currentTimestamp	= $initializationArray['currentTimestamp'];
	$validTestmode		= $initializationArray['validTestmode'];
	$proximateSemester	= $initializationArray['proximateSemester'];
	$currentSemester			= $initializationArray['currentSemester'];
	$nextSemester				= $initializationArray['nextSemester'];
	$semesterTwo				= $initializationArray['semesterTwo'];
	$semesterThree				= $initializationArray['semesterThree'];
	$pastSemestersArray			= $initializationArray['pastSemestersArray'];
	$inp_semesterlist			= '';
	$siteURL			= $initializationArray['siteurl'];
	$userEmail			= $initializationArray['userEmail'];
	$userDisplayName	= $initializationArray['userDisplayName'];
	$userRole			= $initializationArray['userRole'];
	
//	CHECK THIS!								//////////////////////
	if ($userName == '') {
		return "YOU'RE NOT AUTHORIZED!<br />Goodby";
	}

	if (!in_array($userName,$validTestmode) && $doDebug) {	// turn off doDebug if not a testmode user
		$doDebug			= FALSE;
		$testMode			= FALSE;
	}

//	ini_set('memory_limit','256M');
//	ini_set('max_execution_time',0);
//	set_time_limit(0);

//	if ($doDebug) {
		ini_set('display_errors','1');
		error_reporting(E_ALL);	
		$wpdb->show_errors();
//	} else {
//		$wpdb->hide_errors();
//	}

/// get the time that the process started
	$startingMicroTime			= microtime(TRUE);

	$strPass					= "1";
	$theURL						= "$siteURL/cwa-semester-raw-comparison-statistics/";
	$inp_semester				= '';
	$inp_rsave					= '';
	$jobname					= "Semester Raw Comparison Statistics V$versionNumber";
	$levelArray					= array('Beginner'=>'beg',
										'Fundamental'=>'fun',
										'Intermediate'=>'int',
										'Advanced'=>'adv');			
	$countsArray				= array();				

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
			if ($str_key 		== "inp_semesterlist") {
				$inp_semesterlist	 = $str_value;
//				$inp_semesterlist	 = filter_var($inp_semesterlist,FILTER_UNSAFE_RAW);
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

	if ($testMode) {
		$content	.= "<p><strong>Operating in Test Mode.</strong></p>";
		if ($doDebug) {
			echo "<p><strong>Operating in Test Mode.</strong></p>";
		}
		$extMode					= 'tm';
		$studentTableName			= "wpw1_cwa_student2";
		$userMasterTableName		= 'wpw1_cwa_user_master2';
	} else {
		$extMode					= 'pd';
		$studentTableName			= "wpw1_cwa_student";
		$userMasterTableName		= 'wpw1_cwa_user_master';
	}



	if ("1" == $strPass) {
		if ($doDebug) {
			echo "Function starting. Building semester option list<br />";
		}
		$thisIndex			= 0;
		$optionList			= "";
		$myInt				= count($pastSemestersArray) - 1;
		for ($ii=$myInt;$ii>-1;$ii--) {
	 		$thisSemester		= $pastSemestersArray[$ii];
	 		$thisIndex++;
			$optionList		.= "<input type='checkbox' class='formInputButton' name='inp_semesterlist[$thisIndex]' value='$thisSemester'> $thisSemester<br />";
			if ($doDebug) {
				echo "Added $thisSemester to option list<br />";
			}
		}
		$optionList		.= "----<br />";
		if ($currentSemester != 'Not in Session') {
			$thisIndex++;
			$optionList		.= "<input type='checkbox' class='formInputButton' name='inp_semesterlist[$thisIndex]' value='$currentSemester'> $currentSemester<br />";
		}
		$thisIndex++;
		$optionList		.= "<input type='checkbox' class='formInputButton' name='inp_semesterlist[$thisIndex]' value='$nextSemester' > $nextSemester<br />";		
		$thisIndex++;
		$optionList		.= "<input type='checkbox' class='formInputButton' name='inp_semesterlist[$thisIndex]' value='$semesterTwo'> $semesterTwo<br />";		
		$thisIndex++;
		$optionList		.= "<input type='checkbox' class='formInputButton' name='inp_semesterlist[$thisIndex]' value='$semesterThree'> $semesterThree<br />";
		if ($doDebug) {
			echo "optionlist complete<br />";
		}
		$content 		.= "<h3>$jobname</h3>
							<p>Select the semesters to be included and click Submit to run the program
							<form method='post' action='$theURL' 
							name='selection_form' ENCTYPE='multipart/form-data'>
							<input type='hidden' name='strpass' value='2'>
							<table style='border-collapse:collapse;'>
							<tr><td style='width:150px; vertical-align:top;'>Semester</td><td>
								$optionList
								</td></tr>
							$testModeOption
							<tr><td>Save this report to the reports achive?</td>
							<td><input type='radio' class='formInputButton' name='inp_rsave' value='N' checked='checked'> Do not save the report<br />
								<input type='radio' class='formInputButton' name='inp_rsave' value='Y'> Save a copy of the report the report</td></tr>
							<tr><td colspan='2'><input class='formInputButton' type='submit' value='Submit' /></td></tr></table>
							</form></p>";
	

///// Pass 2 -- do the work


	} elseif ("2" == $strPass) {
		if ($doDebug) {
			echo "<br />at pass 2<br />
			inp_semesterlist: <br /><pre>";
			print_r($inp_semesterlist);
			echo "</pre><br />";
		}
		
		$content	.= "<h3>$jobname</h3>";
		foreach($inp_semesterlist as $thisSeq => $theSemester) {
			// setup the array
			$countsArray[$theSemester]['students']['total']		= 0;
			$countsArray[$theSemester]['students']['beg']		= 0;
			$countsArray[$theSemester]['students']['fun']		= 0;
			$countsArray[$theSemester]['students']['int']		= 0;
			$countsArray[$theSemester]['students']['adv']		= 0;

			$countsArray[$theSemester]['validated']['total']	= 0;
			$countsArray[$theSemester]['validated']['beg']		= 0;
			$countsArray[$theSemester]['validated']['fun']		= 0;
			$countsArray[$theSemester]['validated']['int']		= 0;
			$countsArray[$theSemester]['validated']['adv']		= 0;

			$countsArray[$theSemester]['assigned']['total']		= 0;
			$countsArray[$theSemester]['assigned']['beg']		= 0;
			$countsArray[$theSemester]['assigned']['fun']		= 0;
			$countsArray[$theSemester]['assigned']['int']		= 0;
			$countsArray[$theSemester]['assigned']['adv']		= 0;

			$countsArray[$theSemester]['promoted']['total']		= 0;
			$countsArray[$theSemester]['promoted']['beg']		= 0;
			$countsArray[$theSemester]['promoted']['fun']		= 0;
			$countsArray[$theSemester]['promoted']['int']		= 0;
			$countsArray[$theSemester]['promoted']['adv']		= 0;
			// get the students
			$studentSQL		= "select * from $studentTableName 
								left join $userMasterTableName on user_call_sign = student_call_sign 
								where student_semester = '$theSemester'";
			$wpw1_cwa_student	= $wpdb->get_results($studentSQL);
			if ($wpw1_cwa_student === FALSE) {
				handleWPDBError($jobname,$doDebug);
			} else {
				$numSRows									= $wpdb->num_rows;
				if ($doDebug) {
					echo "ran $studentSQL<br />and retrieved $numSRows rows from $studentTableName table<br >";
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
						
						$prefix		= $levelArray[$student_level];
						
						$countsArray[$theSemester]['students']['total']++;
						$countsArray[$theSemester]['students'][$prefix]++;
						
						if ($student_response == 'Y') {
							$countsArray[$theSemester]['validated']['total']++;
							$countsArray[$theSemester]['validated'][$prefix]++;
						}
						if ($student_status == 'S' || $student_status == 'Y') {
							$countsArray[$theSemester]['assigned']['total']++;
							$countsArray[$theSemester]['assigned'][$prefix]++;
						}
						if ($student_promotable == 'P') {
							$countsArray[$theSemester]['promoted']['total']++;
							$countsArray[$theSemester]['promoted'][$prefix]++;
						}
					}
				} else {
					$content	.= "<p>No students found in $studentTableName table for $theSemester semester</p>";
				}
			}
		}
		// counts obtained display and export
		
		// setup csv file
		$thisStr		= "semester_comparison.csv";
		if (preg_match('/localhost/',$siteURL)) {
			$thisFileName	= "wp-content/uploads/$thisStr";
		} else {
			$thisFileName	= "/home/cwacwops/public_html/wp-content/uploads/$thisStr";
		}
		$thisFP			= fopen($thisFileName,'w');

		$csvRecord				= array();
		$displayReport			= "<h4>Raw Data</h4>
									<table>
									<tr><th>Category</th>";
		$csvRecord[]			= 'Category';
		foreach($inp_semesterlist as $thisSeq => $thisSemester) {
			$displayReport		.= "<th>$thisSemester</th>";
			$csvRecord[]		= $thisSemester;
		}
		fputcsv($thisFP,$csvRecord,"\t");
		$csvRecord				= array();
		$displayReport			.= "</tr>\n<tr><td>Total Enrolled</td>";
		$csvRecord[]			= 'Total Enrolled';
		foreach($inp_semesterlist as $thisSeq => $thisSemester) {
			$thisData			= $countsArray[$thisSemester]['students']['total'];
			$displayReport		.= "<td>$thisData</td>";
			$csvRecord[]		= $thisData;
		}
		fputcsv($thisFP,$csvRecord,"\t");
		$csvRecord				= array();
		$displayReport			.= "</tr>\n<tr><td>Beg Enrolled</td>";
		$csvRecord[]			= 'Beg Enrolled';
		foreach($inp_semesterlist as $thisSeq => $thisSemester) {
			$thisData			= $countsArray[$thisSemester]['students']['beg'];
			$displayReport		.= "<td>$thisData</td>";
			$csvRecord[]		= $thisData;
		}
		fputcsv($thisFP,$csvRecord,"\t");
		$csvRecord				= array();
		$displayReport			.= "</tr>\n<tr><td>Fun Enrolled</td>";
		$csvRecord[]			= 'Fun Enrolled';
		foreach($inp_semesterlist as $thisSeq => $thisSemester) {
			$thisData			= $countsArray[$thisSemester]['students']['fun'];
			$displayReport		.= "<td>$thisData</td>";
			$csvRecord[]		= $thisData;
		}
		fputcsv($thisFP,$csvRecord,"\t");
		$csvRecord				= array();
		$displayReport			.= "</tr>\n<tr><td>Int Enrolled</td>";
		$csvRecord[]			= 'Int Enrolled';
		foreach($inp_semesterlist as $thisSeq => $thisSemester) {
			$thisData			= $countsArray[$thisSemester]['students']['int'];
			$displayReport		.= "<td>$thisData</td>";
			$csvRecord[]		= $thisData;
		}
		fputcsv($thisFP,$csvRecord,"\t");
		$csvRecord				= array();
		$displayReport			.= "</tr>\n<tr><td style='border-bottom:1px solid black;'>Adv Enrolled</td>";
		$csvRecord[]			= 'Adv Enrolled';
		foreach($inp_semesterlist as $thisSeq => $thisSemester) {
			$thisData			= $countsArray[$thisSemester]['students']['adv'];
			$displayReport		.= "<td style='border-bottom:1px solid black;'>$thisData</td>";
			$csvRecord[]		= $thisData;
		}
		fputcsv($thisFP,$csvRecord,"\t");

		$csvRecord				= array();
		$displayReport			.= "</tr>\n<tr><td>Total Verified</td>";
		$csvRecord[]			= 'Total Verified';
		foreach($inp_semesterlist as $thisSeq => $thisSemester) {
			$thisData			= $countsArray[$thisSemester]['validated']['total'];
			$displayReport		.= "<td>$thisData</td>";
			$csvRecord[]		= $thisData;
		}
		fputcsv($thisFP,$csvRecord,"\t");
		$csvRecord				= array();
		$displayReport			.= "</tr>\n<tr><td>Beg Verified</td>";
		$csvRecord[]			= 'Beg Verified';
		foreach($inp_semesterlist as $thisSeq => $thisSemester) {
			$thisData			= $countsArray[$thisSemester]['validated']['beg'];
			$displayReport		.= "<td>$thisData</td>";
			$csvRecord[]		= $thisData;
		}
		fputcsv($thisFP,$csvRecord,"\t");
		$csvRecord				= array();
		$displayReport			.= "</tr>\n<tr><td>Fun Verified</td>";
		$csvRecord[]			= 'Fun Verified';
		foreach($inp_semesterlist as $thisSeq => $thisSemester) {
			$thisData			= $countsArray[$thisSemester]['validated']['fun'];
			$displayReport		.= "<td>$thisData</td>";
			$csvRecord[]		= $thisData;
		}
		fputcsv($thisFP,$csvRecord,"\t");
		$csvRecord				= array();
		$displayReport			.= "</tr>\n<tr><td>Int Verified</td>";
		$csvRecord[]			= 'Int Verified';
		foreach($inp_semesterlist as $thisSeq => $thisSemester) {
			$thisData			= $countsArray[$thisSemester]['validated']['int'];
			$displayReport		.= "<td>$thisData</td>";
			$csvRecord[]		= $thisData;
		}
		fputcsv($thisFP,$csvRecord,"\t");
		$csvRecord				= array();
		$displayReport			.= "</tr>\n<tr><td style='border-bottom:1px solid black;'>Adv Verified</td>";
		$csvRecord[]			= 'Adv Verified';
		foreach($inp_semesterlist as $thisSeq => $thisSemester) {
			$thisData			= $countsArray[$thisSemester]['validated']['adv'];
			$displayReport		.= "<td style='border-bottom:1px solid black;'>$thisData</td>";
			$csvRecord[]		= $thisData;
		}
		fputcsv($thisFP,$csvRecord,"\t");

		$csvRecord				= array();
		$displayReport			.= "</tr>\n<tr><td>Total Assigned</td>";
		$csvRecord[]			= 'Total Assigned';
		foreach($inp_semesterlist as $thisSeq => $thisSemester) {
			$thisData			= $countsArray[$thisSemester]['assigned']['total'];
			$displayReport		.= "<td>$thisData</td>";
			$csvRecord[]		= $thisData;
		}
		fputcsv($thisFP,$csvRecord,"\t");
		$csvRecord				= array();
		$displayReport			.= "</tr>\n<tr><td>Beg Assigned</td>";
		$csvRecord[]			= 'Beg Assigned';
		foreach($inp_semesterlist as $thisSeq => $thisSemester) {
			$thisData			= $countsArray[$thisSemester]['assigned']['beg'];
			$displayReport		.= "<td>$thisData</td>";
			$csvRecord[]		= $thisData;
		}
		fputcsv($thisFP,$csvRecord,"\t");
		$csvRecord				= array();
		$displayReport			.= "</tr>\n<tr><td>Fun Assigned</td>";
		$csvRecord[]			= 'Fun Assigned';
		foreach($inp_semesterlist as $thisSeq => $thisSemester) {
			$thisData			= $countsArray[$thisSemester]['assigned']['fun'];
			$displayReport		.= "<td>$thisData</td>";
			$csvRecord[]		= $thisData;
		}
		fputcsv($thisFP,$csvRecord,"\t");
		$csvRecord				= array();
		$displayReport			.= "</tr>\n<tr><td>Int Assigned</td>";
		$csvRecord[]			= 'Int Assigned';
		foreach($inp_semesterlist as $thisSeq => $thisSemester) {
			$thisData			= $countsArray[$thisSemester]['assigned']['int'];
			$displayReport		.= "<td>$thisData</td>";
			$csvRecord[]		= $thisData;
		}
		fputcsv($thisFP,$csvRecord,"\t");
		$csvRecord				= array();
		$displayReport			.= "</tr>\n<tr><td style='border-bottom:1px solid black;'>Adv Assigned</td>";
		$csvRecord[]			= 'Adv Assigned';
		foreach($inp_semesterlist as $thisSeq => $thisSemester) {
			$thisData			= $countsArray[$thisSemester]['assigned']['adv'];
			$displayReport		.= "<td style='border-bottom:1px solid black;'>$thisData</td>";
			$csvRecord[]		= $thisData;
		}
		fputcsv($thisFP,$csvRecord,"\t");

		$csvRecord				= array();
		$displayReport			.= "</tr>\n<tr><td>Total Promoted</td>";
		$csvRecord[]			= 'Total Promoted';
		foreach($inp_semesterlist as $thisSeq => $thisSemester) {
			$thisData			= $countsArray[$thisSemester]['promoted']['total'];
			$displayReport		.= "<td>$thisData</td>";
			$csvRecord[]		= $thisData;
		}
		fputcsv($thisFP,$csvRecord,"\t");
		$csvRecord				= array();
		$displayReport			.= "</tr>\n<tr><td>Beg Promoted</td>";
		$csvRecord[]			= 'Beg Promoted';
		foreach($inp_semesterlist as $thisSeq => $thisSemester) {
			$thisData			= $countsArray[$thisSemester]['promoted']['beg'];
			$displayReport		.= "<td>$thisData</td>";
			$csvRecord[]		= $thisData;
		}
		fputcsv($thisFP,$csvRecord,"\t");
		$csvRecord				= array();
		$displayReport			.= "</tr>\n<tr><td>Fun Promoted</td>";
		$csvRecord[]			= 'Fun Promoted';
		foreach($inp_semesterlist as $thisSeq => $thisSemester) {
			$thisData			= $countsArray[$thisSemester]['promoted']['fun'];
			$displayReport		.= "<td>$thisData</td>";
			$csvRecord[]		= $thisData;
		}
		fputcsv($thisFP,$csvRecord,"\t");
		$csvRecord				= array();
		$displayReport			.= "</tr>\n<tr><td>Int Promoted</td>";
		$csvRecord[]			= 'Int Promoted';
		foreach($inp_semesterlist as $thisSeq => $thisSemester) {
			$thisData			= $countsArray[$thisSemester]['promoted']['int'];
			$displayReport		.= "<td>$thisData</td>";
			$csvRecord[]		= $thisData;
		}
		fputcsv($thisFP,$csvRecord,"\t");
		$csvRecord				= array();
		$displayReport			.= "</tr>\n<tr><td style='border-bottom:1px solid black;'>Adv Promoted</td>";
		$csvRecord[]			= 'Adv Promoted';
		foreach($inp_semesterlist as $thisSeq => $thisSemester) {
			$thisData			= $countsArray[$thisSemester]['promoted']['adv'];
			$displayReport		.= "<td style='border-bottom:1px solid black;'>$thisData</td>";
			$csvRecord[]		= $thisData;
		}
		fputcsv($thisFP,$csvRecord,"\t");


		$displayReport			.= "</tr></table>";
		fclose($thisFP);		
		
		$content				.= "$displayReport
									<p>Click <a href='$siteURL/wp-content/uploads/$thisStr'>semester_comparison.csv</a> to 
									download semester_comparison_csv file</p>
									<p>To use this data import the downloaded semester_comparison.csv file into a spreadsheet.</p>";
		
		
	}
	$thisTime 		= date('Y-m-d H:i:s');
	$content 		.= "<br /><br /><p>Prepared at $thisTime</p>";

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
add_shortcode ('semester_raw_comparison_statistics', 'semester_raw_comparison_statistics_func');
