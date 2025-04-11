function find_black_holes_func() {

/*	Find Black Holes --- Advisors where students go in and don't come out

	Starting with the 2021 Jan/Feb semester and then continuing up to the previous semester
		Get all advisor classes for that semester for Beginner, Fundamental, and Intermediate
		for each advisor, count the number of students 
			count the number of promotable
			count the number withdrawn
			count the number not evaluated
			
		For each promotable student, did the student
									 take the next level?
									 take the same level again
									 disappear?
		For each not-promotable student, did the student
									 take the next level?
									 take the same level again
									 disappear?
		For each withdrawn student, did the student
									 take the next level?
									 take the same level again
									 disappear?
		For each unevaluated student, did the student
									 take the next level?
									 take the same level again
									 disappear?
									 
		create blackHoleArray[advisor][level]	total classes
												total students
												total promoted
												promoted took next level
												promoted took same level
												promoted took lower level
												promoted disappeared
												total not-promoted
												not-promoted took next level
												not-promoted took same level
												not-promoted took  lower level
												not-promoted disappeared
												total withdrew
												withdrew took next level
												withdrew took same level
												withdrew took lower level
												withdrew disappeared
												
	created 4Mar2025 by Roland
*/

	global $wpdb;

	$doDebug						= FALSE;
	$testMode						= FALSE;
	$initializationArray 			= data_initialization_func();
	$validUser 						= $initializationArray['validUser'];
	$userName						= $initializationArray['userName'];
	$proximateSemester				= $initializationArray['proximateSemester'];
	$pastSemestersArray				= $initializationArray['pastSemestersArray'];

	$versionNumber				 	= "1";
	if ($doDebug) {
		echo "Initialization Array:<br /><pre>";
		print_r($initializationArray);
		echo "</pre><br />";
	}
	$userName			= $initializationArray['userName'];
	$currentTimestamp	= $initializationArray['currentTimestamp'];
	$validTestmode		= $initializationArray['validTestmode'];
	$siteURL			= $initializationArray['siteurl'];
	$userEmail			= $initializationArray['userEmail'];
	$userDisplayName	= $initializationArray['userDisplayName'];
	$userRole			= $initializationArray['userRole'];
	
//	CHECK THIS!								//////////////////////
	if ($userName == '') {
		return "YOU'RE NOT AUTHORIZED!<br />Goodby";
	}

//	ini_set('memory_limit','256M');
//	ini_set('max_execution_time',0);
//	set_time_limit(0);

		ini_set('display_errors','1');
		error_reporting(E_ALL);	
		$wpdb->show_errors();

/// get the time that the process started
	$startingMicroTime			= microtime(TRUE);

	$strPass					= "1";
	$theURL						= "$siteURL/cwa-find-black-holes/";
	$inp_rsave					= '';
	$jobname					= "Find Black Holes V$versionNumber";
	$blackHoleArray				= array();
	$convertLevel				= array('Beginner'=>1,
										'Fundamental'=>2,
										'Intermediate'=>3,
										'Advanced'=>4);

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
			if ($str_key 		== "inp_rsave") {
				$inp_rsave		 = $str_value;
				$inp_rsave		 = filter_var($inp_rsave,FILTER_UNSAFE_RAW);
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

	if ($testMode) {
		$content	.= "<p><strong>Operating in Test Mode.</strong></p>";
		if ($doDebug) {
			echo "<p><strong>Operating in Test Mode.</strong></p>";
		}
		$extMode					= 'tm';
		$advisorClassTableName		= "wpw1_cwa_advisorclass2";
		$userMasterTableName		= "wpw1_cwa_user_master2";
		$studentTableName			= "wpw1_cwa_student2";
	} else {
		$extMode					= 'pd';
		$advisorClassTableName		= "wpw1_cwa_advisorclass";
		$userMasterTableName		= "wpw1_cwa_user_master";
		$studentTableName			= "wpw1_cwa_student";
	}

	function semesterConvert($inpSemester) {
		$nameConversion		= array('Jan/Feb'=>1,
									'Apr/May'=>2,
									'May/Jun'=>3,
									'Sep/Oct'=>4,
									'SEP/OCT'=>4,
									'JAN/FEB'=>1);
		$semesterArray		= explode(" ",$inpSemester);
		$semesterYear		= $semesterArray[0];
		$semesterName		= $semesterArray[1];
		if (array_key_exists($semesterName,$nameConversion)) {
			$semesterNumber	= $nameConversion[$semesterName];
		} else {
			echo "<b>ERROR:</b> Can't convert $inpSemester<br />";
			$semesterNumber	= 0;
		}
		return "$semesterYear$semesterNumber";
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
			echo "<br />at pass 2<br />";
		}
		
		$content		.= "<h3>$jobname</h3>";
		
		// process each past semester starting with 2021 JanFeb
		for ($ii=12;$ii>0;$ii--) {
			$processSemester		= $pastSemestersArray[$ii];
			$processSemesterSeq		= semesterConvert($processSemester);
			if ($doDebug) {
				echo "processing $processSemester semester<br />";
			}
			$sql			= "select * from $advisorClassTableName 
								left join $userMasterTableName on advisorclass_call_sign = user_call_sign 
								where advisorclass_semester = '$processSemester' 
								and advisorclass_level != 'Advanced' 
								order by advisorclass_sequence";
			$wpw1_cwa_advisorclass	= $wpdb->get_results($sql);
			if ($wpw1_cwa_advisorclass === FALSE) {
				handleWPDBError($jobname,$doDebug);
			} else {
				$numACRows			= $wpdb->num_rows;
				if ($doDebug) {
					echo "ran $sql<br />and found $numACRows rows<br />";
				}
				if ($numACRows > 0) {
					foreach ($wpw1_cwa_advisorclass as $advisorClassRow) {
						$advisorClass_master_ID 				= $advisorClassRow->user_ID;
						$advisorClass_master_call_sign			= $advisorClassRow->user_call_sign;
						$advisorClass_first_name 				= $advisorClassRow->user_first_name;
						$advisorClass_last_name 				= $advisorClassRow->user_last_name;
						$advisorClass_email 					= $advisorClassRow->user_email;
						$advisorClass_ph_code 					= $advisorClassRow->user_ph_code;
						$advisorClass_phone 					= $advisorClassRow->user_phone;
						$advisorClass_city 						= $advisorClassRow->user_city;
						$advisorClass_state 					= $advisorClassRow->user_state;
						$advisorClass_zip_code 					= $advisorClassRow->user_zip_code;
						$advisorClass_country_code 				= $advisorClassRow->user_country_code;
						$advisorClass_country 					= $advisorClassRow->user_country;
						$advisorClass_whatsapp 					= $advisorClassRow->user_whatsapp;
						$advisorClass_telegram 					= $advisorClassRow->user_telegram;
						$advisorClass_signal 					= $advisorClassRow->user_signal;
						$advisorClass_messenger 				= $advisorClassRow->user_messenger;
						$advisorClass_master_action_log 		= $advisorClassRow->user_action_log;
						$advisorClass_timezone_id 				= $advisorClassRow->user_timezone_id;
						$advisorClass_languages 				= $advisorClassRow->user_languages;
						$advisorClass_survey_score 				= $advisorClassRow->user_survey_score;
						$advisorClass_is_admin					= $advisorClassRow->user_is_admin;
						$advisorClass_role 						= $advisorClassRow->user_role;
						$advisorClass_prev_callsign				= $advisorClassRow->user_prev_callsign;
						$advisorClass_master_date_created 		= $advisorClassRow->user_date_created;
						$advisorClass_master_date_updated 		= $advisorClassRow->user_date_updated;
	
						$advisorClass_ID				 		= $advisorClassRow->advisorclass_id;
						$advisorClass_call_sign 				= $advisorClassRow->advisorclass_call_sign;
						$advisorClass_sequence 					= $advisorClassRow->advisorclass_sequence;
						$advisorClass_semester 					= $advisorClassRow->advisorclass_semester;
						$advisorClass_timezone_offset			= $advisorClassRow->advisorclass_timezone_offset;	// new
						$advisorClass_level 					= $advisorClassRow->advisorclass_level;
						$advisorClass_class_size 				= $advisorClassRow->advisorclass_class_size;
						$advisorClass_class_schedule_days 		= $advisorClassRow->advisorclass_class_schedule_days;
						$advisorClass_class_schedule_times 		= $advisorClassRow->advisorclass_class_schedule_times;
						$advisorClass_class_schedule_days_utc 	= $advisorClassRow->advisorclass_class_schedule_days_utc;
						$advisorClass_class_schedule_times_utc 	= $advisorClassRow->advisorclass_class_schedule_times_utc;
						$advisorClass_action_log 				= $advisorClassRow->advisorclass_action_log;
						$advisorClass_class_incomplete 			= $advisorClassRow->advisorclass_class_incomplete;
						$advisorClass_date_created				= $advisorClassRow->advisorclass_date_created;
						$advisorClass_date_updated				= $advisorClassRow->advisorclass_date_updated;
						$advisorClass_student01 				= $advisorClassRow->advisorclass_student01;
						$advisorClass_student02 				= $advisorClassRow->advisorclass_student02;
						$advisorClass_student03 				= $advisorClassRow->advisorclass_student03;
						$advisorClass_student04 				= $advisorClassRow->advisorclass_student04;
						$advisorClass_student05 				= $advisorClassRow->advisorclass_student05;
						$advisorClass_student06 				= $advisorClassRow->advisorclass_student06;
						$advisorClass_student07 				= $advisorClassRow->advisorclass_student07;
						$advisorClass_student08 				= $advisorClassRow->advisorclass_student08;
						$advisorClass_student09 				= $advisorClassRow->advisorclass_student09;
						$advisorClass_student10 				= $advisorClassRow->advisorclass_student10;
						$advisorClass_student11 				= $advisorClassRow->advisorclass_student11;
						$advisorClass_student12 				= $advisorClassRow->advisorclass_student12;
						$advisorClass_student13 				= $advisorClassRow->advisorclass_student13;
						$advisorClass_student14 				= $advisorClassRow->advisorclass_student14;
						$advisorClass_student15 				= $advisorClassRow->advisorclass_student15;
						$advisorClass_student16 				= $advisorClassRow->advisorclass_student16;
						$advisorClass_student17 				= $advisorClassRow->advisorclass_student17;
						$advisorClass_student18 				= $advisorClassRow->advisorclass_student18;
						$advisorClass_student19 				= $advisorClassRow->advisorclass_student19;
						$advisorClass_student20 				= $advisorClassRow->advisorclass_student20;
						$advisorClass_student21 				= $advisorClassRow->advisorclass_student21;
						$advisorClass_student22 				= $advisorClassRow->advisorclass_student22;
						$advisorClass_student23 				= $advisorClassRow->advisorclass_student23;
						$advisorClass_student24 				= $advisorClassRow->advisorclass_student24;
						$advisorClass_student25 				= $advisorClassRow->advisorclass_student25;
						$advisorClass_student26 				= $advisorClassRow->advisorclass_student26;
						$advisorClass_student27 				= $advisorClassRow->advisorclass_student27;
						$advisorClass_student28 				= $advisorClassRow->advisorclass_student28;
						$advisorClass_student29 				= $advisorClassRow->advisorclass_student29;
						$advisorClass_student30 				= $advisorClassRow->advisorclass_student30;
						$advisorClass_number_students			= $advisorClassRow->advisorclass_number_students;
						$advisorClass_class_evaluation_complete = $advisorClassRow->advisorclass_evaluation_complete;
						$advisorClass_class_comments			= $advisorClassRow->advisorclass_class_comments;
						$advisorClass_copycontrol				= $advisorClassRow->advisorclass_copy_control;

//						if ($advisorClass_call_sign == 'K5GQ') {
//							$doDebug = TRUE;
//						} else {
//							$doDebug = FALSE;
//						}
						if ($doDebug) {
							echo "<br />Processing $advisorClass_last_name, $advisorClass_first_name ($advisorClass_call_sign) $advisorClass_semester semester class $advisorClass_sequence $advisorClass_level level<br />";
						}
						// see if the advisor/level are in the black hole array
						if (!isset($blackHoleArray[$advisorClass_call_sign][$advisorClass_level])) {
							if ($doDebug) {
								echo "$advisorClass_call_sign / $advisorClass_level not in blackHoleArray. Adding<br />";
							}
							$blackHoleArray[$advisorClass_call_sign][$advisorClass_level]['total_classes']					= 0;
							$blackHoleArray[$advisorClass_call_sign][$advisorClass_level]['total_students']					= 0;
							$blackHoleArray[$advisorClass_call_sign][$advisorClass_level]['total_promoted']					= 0;
							$blackHoleArray[$advisorClass_call_sign][$advisorClass_level]['promoted_took_next_level']		= 0;
							$blackHoleArray[$advisorClass_call_sign][$advisorClass_level]['promoted_took_same_level']		= 0;
							$blackHoleArray[$advisorClass_call_sign][$advisorClass_level]['promoted_took_lower_level']		= 0;
							$blackHoleArray[$advisorClass_call_sign][$advisorClass_level]['promoted_disappeared']			= 0;
							$blackHoleArray[$advisorClass_call_sign][$advisorClass_level]['total_not_promoted']				= 0;
							$blackHoleArray[$advisorClass_call_sign][$advisorClass_level]['not_promoted_took_next_level']	= 0;
							$blackHoleArray[$advisorClass_call_sign][$advisorClass_level]['not_promoted_took_same_level']	= 0;
							$blackHoleArray[$advisorClass_call_sign][$advisorClass_level]['not_promoted_took_lower_level']	= 0;
							$blackHoleArray[$advisorClass_call_sign][$advisorClass_level]['not_promoted_disappeared']		= 0;
							$blackHoleArray[$advisorClass_call_sign][$advisorClass_level]['total_withdrew']					= 0;
							$blackHoleArray[$advisorClass_call_sign][$advisorClass_level]['withdrew_took_next_level']		= 0;
							$blackHoleArray[$advisorClass_call_sign][$advisorClass_level]['withdrew_took_same_level']		= 0;
							$blackHoleArray[$advisorClass_call_sign][$advisorClass_level]['withdrew_took_lower_level']		= 0;
							$blackHoleArray[$advisorClass_call_sign][$advisorClass_level]['withdrew_disappeared']			= 0;
							$blackHoleArray[$advisorClass_call_sign][$advisorClass_level]['total_unevaluated']				= 0;
							$blackHoleArray[$advisorClass_call_sign][$advisorClass_level]['unevaluated_took_next_level']	= 0;
							$blackHoleArray[$advisorClass_call_sign][$advisorClass_level]['unevaluated_took_same_level']	= 0;
							$blackHoleArray[$advisorClass_call_sign][$advisorClass_level]['unevaluated_took_lower_level']	= 0;
							$blackHoleArray[$advisorClass_call_sign][$advisorClass_level]['unevaluated_disappeared']		= 0;
						}
						
						$totalStudents			= $advisorClass_number_students;
						$totalPromotable		= 0;
						$isPromotable			= FALSE;
						$totalNotPromotable		= 0;
						$isNotPromotable		= FALSE;
						$totalWithdrew			= 0;
						$isWithdrew				= FALSE;
						$totalUnevaluated		= 0;
						$isUnevaluated			= FALSE;
						$promotedNextLevel		= 0;
						$promotedSameLevel		= 0;
						$promotedLowerLevel		= 0;
						$promotedDisappeared	= 0;
						$notPromotedNextLevel	= 0;
						$notPromotedSameLevel	= 0;
						$notPromotedLowerLevel	= 0;
						$notPromotedDisappeared	= 0;
						$withdrewNextLevel		= 0;
						$withdrewSameLevel		= 0;
						$withdrewLowerLevel		= 0;
						$withdrewDisappeared	= 0;
						$unevaluatedNextLevel	= 0;
						$unevaluatedSameLevel	= 0;
						$unevaluatedLowerLevel	= 0;
						$unevaluatedDisappeared	= 0;
						$doProceed				= TRUE;

						for ($snum=1;$snum<31;$snum++) {
							if ($snum < 10) {
								$strSnum 		= str_pad($snum,2,'0',STR_PAD_LEFT);
							} else {
								$strSnum		= strval($snum);
							}
							$studentCallSign	= ${'advisorClass_student' . $strSnum};
							if ($studentCallSign != '') {

								$isPromotable		= FALSE;
								$isNotPromotable	= FALSE;
								$isWithdrew			= FALSE;
								$isUnevaluated		= FALSE;
								$isDisappeared		= FALSE;
							
								// get student promotable
								$studentSQL			= "select student_promotable from $studentTableName 
														where student_call_sign = '$studentCallSign' 
															and student_semester = '$advisorClass_semester'";
								$sqlResult		= $wpdb->get_results($studentSQL);
								if ($sqlResult === FALSE) {
									handleWPDBError($jobname,$doDebug);
								} else {
									$numRows	= $wpdb->num_rows;
									if ($doDebug) {
										echo "ran $studentSQL<br />and retrieved $numRows rows<br />";
									}
									if ($numRows > 0) {
										foreach($sqlResult as $sqlRow) {
											$student_promotable	= $sqlRow->student_promotable;
											if ($doDebug) {
												echo "have a student_promotable of $student_promotable<br />";
											}
											
											if ($student_promotable == 'P') {
												$totalPromotable++;
												if ($doDebug) {
													echo "incremented totalPromotable<br />";
												}
												$isPromotable		= TRUE;
											} elseif ($student_promotable == 'N') {
												$totalNotPromotable++;
												$isNotPromotable	= TRUE;
												if ($doDebug) {
													echo "incremented totalNotPromotable<br />";
												}
											} elseif ($student_promotable == 'W') {
												$totalWithdrew++;
												$isWithdrew			= TRUE;
												if ($doDebug) {
													echo "incremented totalWithdrew<br />";
												}
											} elseif ($student_promotable == '') {
												$totalUnevaluated++;
												$isUnevaluated		= TRUE;
												if ($doDebug) {
													echo "incremented totalUnevaluated<br />";
												}
											} else {
												if ($doDebug) {
													echo "<b>ERROR</b> invalid student_promotable<br />";
												}
												$doProceed			= FALSE;
											}
											if ($doProceed) {
												// figure out what the student did next, if anything
												$nextArray			= array();
												$checkLevel			= $convertLevel[$advisorClass_level];
												$nextSQL			= "select student_level, student_semester from $studentTableName 
																		where student_call_sign = '$studentCallSign'";
												$sqlResult		= $wpdb->get_results($nextSQL);
												if ($sqlResult === FALSE) {
													handleWPDBError($jobname,$doDebug);
												} else {
													$numRows	= $wpdb->num_rows;
													if ($doDebug) {
														echo "ran $nextSQL<br />and retrieved $numRows rows<br />";
													}
													if ($numRows > 0) {
														foreach($sqlResult as $sqlRow) {
															$thisLevel		= $sqlRow->student_level;
															$thisSemester	= $sqlRow->student_semester;
															$testLevel		= $convertLevel[$thisLevel];
															
															$thisNewSemester	= semesterConvert($thisSemester);
															$nextArray[$thisNewSemester]	= $testLevel;
														}
													}
	
													ksort($nextArray);
													if ($doDebug) {
														echo "Calculating what the student did next using nextArray:<br /><pre>";
														print_r($nextArray);
														echo "</pre><br />";
													}
													$hisNextSemester			= '';
													$hisNextLevel				= '';
													foreach($nextArray as $mySemester => $myLevel) {
														if ($doDebug) {
															echo "comparing $mySemester to $processSemesterSeq<br />";
														}
														if ($mySemester > $processSemesterSeq) {
															$hisNextSemester	= $mySemester;
															$hisNextLevel		= $myLevel;
															if ($doDebug) {
																echo "hisNextSemester: $hisNextSemester<br />
																	  hisNextLevel: $hisNextLevel<br />
																	  Comparing level to $checkLevel<br />";
															}
															break;
														} else {
															if ($doDebug) {
																echo "student disappeared<br />";
															}
														}
													}
													// if hisNextLevel is the same as student_level, he repeated the same level
													//	if hisNextLevel is higher than student_level, he took next level
													// if hisNextLevel is less than student_level, he took a lower level
													// if hisNextLevel is empty, the student disappeared
													
													if ($hisNextLevel == '') {
														if ($isPromotable) {
															$promotedDisappeared++;
															if ($doDebug) {
																echo "incremented promotedDisappeared<br />";
															}
														} elseif ($isNotPromotable) {
															$notPromotedDisappeared++;
															if ($doDebug) {
																echo "incremented notPromotedDisappeared<br />";
															}
														} elseif ($isWithdrew) {
															$withdrewDisappeared++;
															if ($doDebug) {
																echo "incremented withdrewDisappeared<br />";
															}
														}
													} elseif ($hisNextLevel > $checkLevel) {		// took a higher level
														if ($isPromotable) {
															$promotedNextLevel++;
															if ($doDebug) {
																echo "incremented promotedNextLevel<br />";
															}
														} elseif ($isNotPromotable) {
															$notPromotedNextLevel++;
															if ($doDebug) {
																echo "incremented notPromotedNextLevel<br />";
															}
														} elseif ($isWithdrew) {
															$withdrewNextLevel++;
															if ($doDebug) {
																echo "incremented withdrewNextLevel<br />";
															}
														}
													} elseif ($hisNextLevel == $checkLevel) {
														if ($isPromotable) {
															$promotedSameLevel++;
															if ($doDebug) {
																echo "incremented promotedSameLevel<br />";
															}
														} elseif ($isNotPromotable) {
															$notPromotedSameLevel++;
															if ($doDebug) {
																echo "incremented notPromotedSameLevel<br />";
															}
														} elseif ($isWithdrew) {
															$withdrewSameLevel++;
															if ($doDebug) {
																echo "incremented withdrewSameLevel<br />";
															}
														}
													} elseif ($hisNextLevel < $checkLevel) {
														if ($isPromotable) {
															$promotedLowerLevel++;
															if ($doDebug) {
																echo "incremented promotedLowerLevel<br />";
															}
														} elseif ($isNotPromotable) {
															$notPromotedLowerLevel++;
															if ($doDebug) {
																echo "incremented notPromotedLowerLevel<br />";
															}
														} elseif ($isWithdrew) {
															$withdrewLowerLevel++;
															if ($doDebug) {
																echo "incremented withdrewLowerLevel<br />";
															}
														}
													}
												}
	
											}
										}
									} else {		// student in advisorclass but no student record found
										if ($doDebug) {
											echo "student in advisorclass but no student record found<br />";
										}
									}
								}
							}
						}	// all students processed
						if ($doDebug) {
							echo "Here's the totals for this class:<br />
								totalStudents: $totalStudents <br />
								totalPromotable: $totalPromotable <br />
								totalNotPromotable: $totalNotPromotable <br />
								totalWithdrew: $totalWithdrew <br />
								totalUnevaluated: $totalUnevaluated <br />
								promotedNextLevel: $promotedNextLevel <br />
								promotedSameLevel: $promotedSameLevel <br />
								promotedLowerLevel: $promotedLowerLevel <br />
								promotedDisappeared: $promotedDisappeared <br />
								notPromotedNextLevel: $notPromotedNextLevel <br />
								notPromotedSameLevel: $notPromotedSameLevel <br />
								notPromotedLowerLevel: $notPromotedLowerLevel <br />
								notPromotedDisappeared: $notPromotedDisappeared <br />
								withdrewNextLevel: $withdrewNextLevel <br />
								withdrewSameLevel: $withdrewSameLevel <br />
								withdrewLowerLevel: $withdrewLowerLevel <br />
								withdrewDisappeared: $withdrewDisappeared <br />
								unevaluatedNextLevel: $unevaluatedNextLevel <br />
								unevaluatedSameLevel: $unevaluatedSameLevel <br />
								unevaluatedLowerLevel: $unevaluatedLowerLevel <br />
								unevaluatedDisappeared: $unevaluatedDisappeared <br />";
							}
						// update blackHoleArray with the totals for this class			
						$blackHoleArray[$advisorClass_call_sign][$advisorClass_level]['total_classes']++;
						$blackHoleArray[$advisorClass_call_sign][$advisorClass_level]['total_students']					+= $totalStudents;
						$blackHoleArray[$advisorClass_call_sign][$advisorClass_level]['total_promoted']					+= $totalPromotable;
						$blackHoleArray[$advisorClass_call_sign][$advisorClass_level]['promoted_took_next_level']		+= $promotedNextLevel;
						$blackHoleArray[$advisorClass_call_sign][$advisorClass_level]['promoted_took_same_level']		+= $promotedSameLevel;
						$blackHoleArray[$advisorClass_call_sign][$advisorClass_level]['promoted_took_lower_level']		+= $promotedLowerLevel;
						$blackHoleArray[$advisorClass_call_sign][$advisorClass_level]['promoted_disappeared']			+= $promotedDisappeared;
						$blackHoleArray[$advisorClass_call_sign][$advisorClass_level]['total_not_promoted']				+= $totalNotPromotable;
						$blackHoleArray[$advisorClass_call_sign][$advisorClass_level]['not_promoted_took_next_level']	+= $notPromotedNextLevel;
						$blackHoleArray[$advisorClass_call_sign][$advisorClass_level]['not_promoted_took_same_level']	+= $notPromotedSameLevel;
						$blackHoleArray[$advisorClass_call_sign][$advisorClass_level]['not_promoted_took_lower_level']	+= $notPromotedLowerLevel;
						$blackHoleArray[$advisorClass_call_sign][$advisorClass_level]['not_promoted_disappeared']		+= $notPromotedDisappeared;
						$blackHoleArray[$advisorClass_call_sign][$advisorClass_level]['total_withdrew']					+= $totalWithdrew;
						$blackHoleArray[$advisorClass_call_sign][$advisorClass_level]['withdrew_took_next_level']		+= $withdrewNextLevel;
						$blackHoleArray[$advisorClass_call_sign][$advisorClass_level]['withdrew_took_same_level']		+= $withdrewSameLevel;
						$blackHoleArray[$advisorClass_call_sign][$advisorClass_level]['withdrew_took_lower_level']		+= $withdrewLowerLevel;
						$blackHoleArray[$advisorClass_call_sign][$advisorClass_level]['withdrew_disappeared']			+= $withdrewDisappeared;
						$blackHoleArray[$advisorClass_call_sign][$advisorClass_level]['total_unevaluated']				+= $totalUnevaluated;
						$blackHoleArray[$advisorClass_call_sign][$advisorClass_level]['unevaluated_took_next_level']	+= $unevaluatedNextLevel;
						$blackHoleArray[$advisorClass_call_sign][$advisorClass_level]['unevaluated_took_same_level']	+= $unevaluatedSameLevel;
						$blackHoleArray[$advisorClass_call_sign][$advisorClass_level]['unevaluated_took_lower_level']	+= $unevaluatedLowerLevel;
						$blackHoleArray[$advisorClass_call_sign][$advisorClass_level]['unevaluated_disappeared']		+= $unevaluatedDisappeared;
					}
				} else {
					if ($doDebug) {
						echo "no advisorclass records found in $processSemester semester<br />";
					}
				}
			}
		} // all records processed. Do something with the final array

		ksort($blackHoleArray);
		if ($doDebug) {
			echo "The blackHoleArray:<br /><pre>";
			print_r($blackHoleArray);
			echo "</pre><br />";
		}
		$content		.= "<h4>Advisors with Disappearing Promoted Students</h4>
							<p>Advisors below have advised 3 or more classes and 
							have more than 30% disappearing students</p><table>";
		foreach($blackHoleArray as $thisAdvisor => $thisData) {
			foreach($thisData as $thisLevel => $thisCategory) {
				$total_classes 					= $thisCategory['total_classes']++;
				$total_students 				= $thisCategory['total_students'];
				$total_promoted 				= $thisCategory['total_promoted'];
				$promoted_took_next_level 		= $thisCategory['promoted_took_next_level'];
				$promoted_took_same_level 		= $thisCategory['promoted_took_same_level'];
				$promoted_took_lower_level 		= $thisCategory['promoted_took_lower_level'];
				$promoted_disappeared 			= $thisCategory['promoted_disappeared'];
				$total_not_promoted 			= $thisCategory['total_not_promoted'];
				$not_promoted_took_next_level 	= $thisCategory['not_promoted_took_next_level'];
				$not_promoted_took_same_level 	= $thisCategory['not_promoted_took_same_level'];
				$not_promoted_took_lower_level 	= $thisCategory['not_promoted_took_lower_level'];
				$not_promoted_disappeared 		= $thisCategory['not_promoted_disappeared'];
				$total_withdrew 				= $thisCategory['total_withdrew'];
				$withdrew_took_next_level 		= $thisCategory['withdrew_took_next_level'];
				$withdrew_took_same_level 		= $thisCategory['withdrew_took_same_level'];
				$withdrew_took_lower_level 		= $thisCategory['withdrew_took_lower_level'];
				$withdrew_disappeared 			= $thisCategory['withdrew_disappeared'];
				$total_unevaluated 				= $thisCategory['total_unevaluated'];
				$unevaluated_took_next_level 	= $thisCategory['unevaluated_took_next_level'];
				$unevaluated_took_same_level 	= $thisCategory['unevaluated_took_same_level'];
				$unevaluated_took_lower_level 	= $thisCategory['unevaluated_took_lower_level'];
				$unevaluated_disappeared 		= $thisCategory['unevaluated_disappeared'];

				if ($promoted_disappeared > 0 && $total_classes > 3) {
					$disappearedPC	= $promoted_disappeared/$total_promoted * 100;
					$disapearedStr = number_format($disappearedPC,1,'.',',');
					if ($disappearedPC > 30.0) {
					
						// is the advisor signed up for the proximate semester?
						$proxStr	= "";
						$proxSQL	= "select count(advisorclass_call_sign) 
													from $advisorClassTableName 
													where advisorclass_call_sign = '$thisAdvisor' 
														and advisorclass_semester = '$proximateSemester' 
														and advisorclass_level = '$thisLevel'";
						$proxCount	= $wpdb->get_var($proxSQL);
						if ($proxCount === FALSE) {
							handleWPDBError($jobname,$doDebug,"checking if advisor is signed up for $proximateSemester semester");
						} else {
							if ($doDebug) {
								echo "ran $proxSQL<br />and got $proxCount in return<br />";
							}
							if ($proxCount > 0) {
								$prxStr 	= "Signed up for $proximateSemester";
							}
						}
						
						$content	.= "<tr><td><b>Advisor</b><br />$thisAdvisor</td>
											<td><b>Level</b><br />$thisLevel</td>
											<td><b>Classes</b><br />$total_classes</td>
											<td><b>Students</b><br />$total_students</td>
											<td>$proxStr</td></tr>
										<tr><td><b>Total Promoted</b><br />$total_promoted</td>
											<td><b>Took Next Level</b><br />$promoted_took_next_level</td>
											<td><b>Took Same Level</b><br />$promoted_took_same_level</td>
											<td><b>Took Lower Level</b><br />$promoted_took_lower_level</td>
											<td><b>Disappeared</b><br />$promoted_disappeared ($disapearedStr%)</td></tr>
										<tr><td colspan='5'><hr></td></tr>";
					}
				}
			}
		}
		$content	.= "</table>";

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
		$storeResult	= storeReportData_v2($jobname,$content);
		if ($storeResult[0] !== FALSE) {
			$reportName	= $storeResult[1];
			$reportID	= $storeResult[2];
			$content	.= "<br />Report stored in reports as $reportName<br />
							Go to'Display Saved Reports' or url<br/>
							$siteURL/cwa-display-saved-report/?strpass=3&token=&inp_id=$reportID<br /><br />";
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
	return $content;
}
add_shortcode ('find_black_holes', 'find_black_holes_func');
