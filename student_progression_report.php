function student_progression_report_func() {

// Function to see how many students are repeat students
//
//	modified 2Oct22 by Roland for new timezone table format
//	modified 17Apr23 by Roland to fix action_log
// 	modified 16Jul23 by Roland to use consolidated tables

	global $wpdb;

	$doDebug					= TRUE;
	$testMode					= FALSE;
	$initializationArray 		= data_initialization_func();
	if ($doDebug) {
		echo "Initialization Array:<br /><pre>";
		print_r($initializationArray);
		echo "</pre><br />";
	}
	$validUser 					= $initializationArray['validUser'];
	$userName					= $initializationArray['userName'];
	$currentTimestamp			= $initializationArray['currentTimestamp'];
	$validTestmode				= $initializationArray['validTestmode'];
	$siteURL					= $initializationArray['siteurl'];
	$currentSemester			= $initializationArray['currentSemester'];
	
//	CHECK THIS!								//////////////////////
	if ($validUser == "N") {
		return "YOU'RE NOT AUTHORIZED!<br />Goodby";
	}


//	if ($doDebug) {
		ini_set('display_errors','1');
		error_reporting(E_ALL);	
//	}

/// get the time that the process started
	$startingMicroTime			= microtime(TRUE);

	$theURL						= "$siteURL/cwa-student-progression-report/";
	$strPass					= "1";
	$inp_semester				= '';
	ini_set('max_execution_time',360);
	ini_set('memory_limit',-1);
	$outputRecords				= 0;
	
	$registeredClass			= array(0=>0,1=>0,2=>0,3=>0,4=>0);
	$initialClass				= array(0=>0,1=>0,2=>0,3=>0,4=>0);
	$respVer					= array(0=>0,1=>0,2=>0,3=>0,4=>0);
	$withdw						= array(0=>0,1=>0,2=>0,3=>0,4=>0);
	$compl						= array(0=>0,1=>0,2=>0,3=>0,4=>0);
	$nextLevel					= array(0=>0,1=>0,2=>0,3=>0,4=>0);
	$responseY					= array(0=>0,1=>0,2=>0,3=>0,4=>0);
	$responseR					= array(0=>0,1=>0,2=>0,3=>0,4=>0);
	$statusY					= array(0=>0,1=>0,2=>0,3=>0,4=>0);
	$statusN					= array(0=>0,1=>0,2=>0,3=>0,4=>0);
	$statusS					= array(0=>0,1=>0,2=>0,3=>0,4=>0);
	$statusC					= array(0=>0,1=>0,2=>0,3=>0,4=>0);
	$statusR					= array(0=>0,1=>0,2=>0,3=>0,4=>0);
	$statusBlank				= array(0=>0,1=>0,2=>0,3=>0,4=>0);
	$responseBlank				= array(0=>0,1=>0,2=>0,3=>0,4=>0);
	$notProm					= array(0=>0,1=>0,2=>0,3=>0,4=>0);
	
	$tookBegOnly				= 0;
	$tookFunOnly				= 0;
	$tookIntOnly				= 0;
	$tookAdvOnly				= 0;
	$tookAll					= 0;


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
			if ($str_key 		== "inp_semester") {
				$inp_semester	 = $str_value;
//				$inp_semester	 = filter_var($inp_semester,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "inp_details") {
				$inp_details	 = $str_value;
				$inp_details	 = filter_var($inp_details,FILTER_UNSAFE_RAW);
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
		$advisorTableName		= "wpw1_cwa_consolidated_advisor2";
		$classTableName			= "wpw1_cwa_consolidated_advisorclass2";
		$studentTableName		= 'wpw1_cwa_consolidated_student2';
	} else {
		$advisorTableName		= "wpw1_cwa_consolidated_advisor";
		$classTableName			= "wpw1_cwa_consolidated_advisorclass";
		$studentTableName		= 'wpw1_cwa_consolidated_student';
	}



	if ("1" == $strPass) {
		if ($doDebug) {
			echo "<br />At pass $strPass<br />";
		}
		
		$pastSemesters			= $initializationArray['pastSemesters'];
		$pastSemesterArray		= explode("|",$pastSemesters);
		$selectList				= "<select name='inp_semester[]' multiple>\n";
		foreach($pastSemesterArray as $thisValue) {
			$selectList			.= "<option value='$thisValue'>$thisValue</option>\n";
		}
		$selectList				.= "</select>\n";
		
		
		$content 		.= "<h3>Student Progression Report</h3>
							<p>Function reads the student table, including only selected semesters, 
							optionally displays a list of each 
							student, each semester they attended, the level they took, and their promotability. 
							The statistics from this information are then presented.</p>
							<form method='post' action='$theURL' 
							name='selection_form' ENCTYPE='multipart/form-data''>
							<input type='hidden' name='strpass' value='2'>
							<p>Select which semesters are to be included:<br />
							$selectList</p>
							<p>Should the student details be displayed<br />
							<input type='radio' class='formInputButton' name='inp_details' value='Y' checked>Yes<br />
							<input type='radio' class='formInputButton' name='inp_details' value='N'>No</p>
							<p><input class='formInputButton' type='submit' value='Next' /></p>
							</form>";

///// Pass 2 -- do the work


	} elseif ("2" == $strPass) {
	
		if ($doDebug) {
			echo "<br />at pass $strPass<br />
			inp_semester Array:<br /><pre>";
			print_r($inp_semester);
			echo "</pre><br />";
		}
		
		$doProceed					= TRUE;
		$myInt						= count($inp_semester);
		if ($doDebug) {
			echo "have $myInt entries in inp_semester<br />";
		}
		if ($myInt > 1) {
			$thisSemesters			= "(";
			$firstTime				= TRUE;
			foreach($inp_semester as $thisValue) {
				if ($firstTime) {
					$thisSemesters	.= "semester = '$thisValue' ";
					$firstTime		= FALSE;
				} else {
					$thisSemesters	.= "or semester = '$thisValue' ";
				}
			}
			$thisSemesters			.= ")";
		} elseif ($myInt == 1) {
			$thisSemesters			= "semester = '$thisValue[0]' ";
		} else {
			$content				.= "No semesters selected. Aborting";
			$doProceed				= FALSE;
		}


		if ($doProceed) {
			$studentArray			= array();				// callsign|semester code|level Code|response|student_status|promotable
															// semester code: year,(1,2,3)
															// level code: (1,2,3,4)
			$semesterConversion		= array('Jan/Feb'=>1,'Apr/May'=>2,'May/Jun'=>2,'Sep/Oct'=>3,'SEP/OCT'=>3,'JAN/FEB'=>1);
			$semesterBack			= array(1=>'Jan/Feb',2=>'Apr/May',3=>'Sep/Oct');
			$levelConversion		= array('fundamental'=>1,'beginner'=>2,'intermediate'=>3,'advanced'=>4);
			$levelBack				= array(1=>'Fun',2=>'Beg',3=>'Int',4=>'Adv');
			// get all the students
		
			$sql				= "select * from $studentTableName 
									where $thisSemesters 
									order by call_sign";
			$wpw1_cwa_student				= $wpdb->get_results($sql);
			if ($wpw1_cwa_student === FALSE) {
				if ($doDebug) {
					echo "Reading $studentTableName table<br />";
					echo "wpdb->last_query: " . $wpdb->last_query . "<br />";
					echo "<b>wpdb->last_error: " . $wpdb->last_error . "</b><br />";
				}
			} else {
				$numSRows									= $wpdb->num_rows;
				if ($doDebug) {
					echo "ran $sql<br />and retrieved $numSRows rows from $studentTableName table<br />";
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
						$student_abandoned  				= $studentRow->abandoned;
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
				
						if ($student_student_status == 'S') {
							$student_student_status = 'Y';
						}
						if ($doDebug) {
							echo "have student $student_call_sign in semester $student_semester with level $student_level<br />";
						}
						$myArray			= explode(" ",$student_semester);
						$myCode				= $semesterConversion[$myArray[1]];
						$semesterCode		= "$myArray[0],$myCode";
						$myStr				= strtolower($student_level);
						$levelCode			= $levelConversion[$myStr];
						$arrayInfo		 	= "$student_call_sign|$semesterCode|$levelCode|$student_response|$student_student_status|$student_promotable|";
						$studentArray[]		= $arrayInfo;
					}
				}
			}
			sort ($studentArray);
			$prevCallsign		= "";
			$prevSemester		= "";
			$tookBas			= "";
			$tookBeg			= "";
			$tookInt			= "";
			$tookAdv			= "";
			$firstTime			= TRUE;
			$prevCallsign		= "";
			$prevSemester		= "";
			$tookClass			= array(1=>FALSE,2=>FALSE,3=>FALSE,4=>FALSE);
			$inClass			= array(1=>FALSE,2=>FALSE,3=>FALSE,4=>FALSE);
			$passedClass		= array(1=>FALSE,2=>FALSE,3=>FALSE,4=>FALSE);
			$withdrewClass		= array(1=>FALSE,2=>FALSE,3=>FALSE,4=>FALSE);
			$arrayLevels		= array(1=>'',2=>'',3=>'',4=>'');

			if ($doDebug) {
				echo "<br />studentarray:<br />";
			}
			if ($inp_details == 'Y') {
				$content			.= "<h3>Student Information and Statistics</h3>
										<table style='border-collapse:collapse;'>
										<tr>
										<th>Student</th>
										<th>Beginner</th>
										<th>Fundamental</th>
										<th>Intermediate</th>
										<th>Advanced</th>
										</tr>";
			}
			foreach($studentArray as $myValue) {
				$myArray		= explode("|",$myValue);
				$thisCallsign	= $myArray[0];
				$thisPromotable	= substr($myArray[5],0,1);
				if ($thisCallsign != $prevCallsign) {
					if ($firstTime) {
						$firstTime 		= FALSE;
						$prevCallsign	= $thisCallsign;
					} else {			// Write out the record and calculate the statistics
						// calculate the statistics here	
						if ($doDebug) {
							echo "<span style='margin-left:15px;'>Finishing $thisCallSign. Checking first class</span><br /><pre>";
							print_r($tookClass);
							echo "</pre><br />";
						}
						// display registered for next class info
						if ($tookClass[1] && $tookClass[2]) {
							$arrayLevels[1]		.= "Registered next level";
							$nextLevel[1]++;
						} elseif ($tookClass[1] && $tookClass[3]) {
							$arrayLevels[1]		.= "Registered higher level";
							$nextLevel[1]++;
						} elseif ($tookClass[1] && $tookClass[4]) {
							$arrayLevels[1]		.= "Registered higher level";
							$nextLevel[1]++;
						}
						if ($tookClass[2] && $tookClass[3]) {
							$arrayLevels[2]		.= "Registered next level";
							$nextLevel[2]++;
						} elseif ($tookClass[2] && $tookClass[4]) {
							$arrayLevels[2]		.= "Registered higher level";
							$nextLevel[2]++;
						}
						if ($tookClass[3] && $tookClass[4]) {
							$arrayLevels[3]		.= "Registered next level";
							$nextLevel[3]++;
						}
						// count how many levels the student passed
						if ($passedClass[1] && !$passedClass[2] && !$passedClass[3] && !$passedClass[4]) {
							$tookBegOnly++;
						}
						if (!$passedClass[1] && $passedClass[2] && !$passedClass[3] && !$passedClass[4]) {
							$tookFunOnly++;
						}
						if (!$passedClass[1] && !$passedClass[2] && $passedClass[3] && !$passedClass[4]) {
							$tookIntOnly++;
						}
						if (!$passedClass[1] && !$passedClass[2] && !$passedClass[3] && $passedClass[4]) {
							$tookAdvOnly++;
						}
						// count how many students took and passed all four levels
						if ($passedClass[1] && $passedClass[2] && $passedClass[3] && $passedClass[4]) {
							$tookAll++;
						}
						// find the first class
						$firstClass				= 0;
						$jj						= 1;
						$noFirstClass			= TRUE;
						while($noFirstClass) {
							if ($tookClass[$jj]) {		// found the first class
								$respVer[$jj]++;
								$initialClass[$jj]++;
								$noFirstClass	= FALSE;
							}
							$jj++;
							if ($jj > 4) {
								$noFirstClass	= FALSE;
							}
						}

						if ($inp_details == 'Y') {
							// Write the output record
							$content	.= "<tr><td style='vertical-align:top;'>$prevCallsign</td>
												<td style='vertical-align:top;'>$arrayLevels[1]</td>
												<td style='vertical-align:top;'>$arrayLevels[2]</td>
												<td style='vertical-align:top;'>$arrayLevels[3]</td>
												<td style='vertical-align:top;'>$arrayLevels[4]</td>
											</tr><tr>
												<td colspan='5'><hr></td></tr>";
						}

						// clear logicals and get ready for next record set
						$prevCallsign		= $thisCallsign;
						$prevSemester		= $thisSemester;
						$tookBas			= "";
						$tookBeg			= "";
						$tookInt			= "";
						$tookAdv			= "";
						$tookClass			= array(1=>FALSE,2=>FALSE,3=>FALSE,4=>FALSE);
						$inClass			= array(1=>FALSE,2=>FALSE,3=>FALSE,4=>FALSE);
						$passedClass		= array(1=>FALSE,2=>FALSE,3=>FALSE,4=>FALSE);
						$withdrewClass		= array(1=>FALSE,2=>FALSE,3=>FALSE,4=>FALSE);
						$arrayLevels		= array(1=>'',2=>'',3=>'',4=>'');
					}
				}
				if ($doDebug) {
					echo "$myValue<br />";
				}
				$thisSemester	= $myArray[1];
				$thisLevel		= $myArray[2];
				$thisResponse	= $myArray[3];
				$thisStatus		= $myArray[4];
				$semesterArray			= explode(",",$thisSemester);
				$whichSemester			= $semesterBack[$semesterArray[1]];
				$outputSemester			= "$semesterArray[0] $whichSemester";
	//			$arrayLevels[$thisLevel] .= "$outputSemester,$thisResponse,$thisStatus,$thisPromotable";
				$arrayLevels[$thisLevel] .= "$outputSemester<br />";
				$whichLevel				= $levelBack[$thisLevel];
				$tookClass[$thisLevel]	= TRUE;
				$registeredClass[$thisLevel]++;
				$arrayLevels[$thisLevel] .= "Registered for class<br />";
				if ($thisResponse == 'Y') {
					if ($doDebug) {
						echo "<span style='margin-left:15px;'>response is Y</span><br />";
					}
					$responseY[$thisLevel]++;
					$arrayLevels[$thisLevel]		.= "Responded Y<br />";
				} elseif ($thisResponse == 'R') {
					if ($doDebug) {
						echo "<span style='margin-left:15px;'>response is R</span><br />";
					}
					$responseR[$thisLevel]++;
					$arrayLevels[$thisLevel]		.= "Refused<br />";
				} elseif ($thisResponse == '') {
					if ($doDebug) {
						echo "<span style='margin-left:15px;'>response is empty</span><br />";
					}
					$responseBlank[$thisLevel]++;
					$arrayLevels[$thisLevel]		.= "No response<br />";
				}
				if ($thisResponse == 'Y') {
					switch($thisStatus) {
						case "Y":
							if ($doDebug) {
								echo "<span style='margin-left:15px;'>Took a $whichLevel class. inClass is TRUE</span><br />";
							}
							$inClass[$thisLevel]		= TRUE;
							$arrayLevels[$thisLevel]	.= "Advisor assigned<br />";
							$statusY[$thisLevel]++;
							break;
						case "N":
							if ($doDebug) {
								echo "<span style='margin-left:15px;'>status=N for a $whichLevel class. inClass is FALSE</span><br />";
							}
							$arrayLevels[$thisLevel]	.= "Advisor dropped<br />";
							$statusN[$thisLevel]++;
							break;
					
						case "C":
							if ($doDebug) {
								echo "<span style='margin-left:15px;'>status=C for a $whichLevel class. inClass is FALSE</span><br />";
							}
							$arrayLevels[$thisLevel]	.= "Replaced<br />";
							$statusC[$thisLevel]++;
							break;
					
						case "R":
							if ($doDebug) {
								echo "<span style='margin-left:15px;'>status=R for a $whichLevel class. inClass is FALSE</span><br />";
							}
							$arrayLevels[$thisLevel]	.= "Replacement requested<br />";
							$statusR[$thisLevel]++;
							break;
					
						case "":
							if ($doDebug) {
								echo "<span style='margin-left:15px;'>status=blank for a $whichLevel class. inClass is FALSE</span><br />";
							}
							$arrayLevels[$thisLevel]	.= "Unassigned<br />";
							$statusBlank[$thisLevel]++;
							break;
					
						default:
	//						if ($doDebug) {
								echo "<span style='margin-left:15px;'>Have an unknown status of $thisStatus</span><br />";
	//						}
					}
				}
				if ($thisResponse == 'Y' && $thisStatus == 'Y') {				// took the class
	//				if ($thisPromotable == '') {
	//					$thisPromotable = 'P';
	//				}
					if ($doDebug) {
						echo "<span style='margin-left:15px;'>thisPromotable: $thisPromotable</span><br />";
					}
					switch($thisPromotable) {
						case "":
							$noPassClass[$thisLevel]	= TRUE;
							$arrayLevels[$thisLevel]	.= "Not Reported<br /><br />";
							$compl[$thisLevel]++;
							if ($doDebug) {
								echo "<span style='margin-left:15px;'>Passed the $whichLevel class. passedClass is TRUE</span><br />";
							}
							break;
						case "P": 
							$passedClass[$thisLevel]	= TRUE;
							$arrayLevels[$thisLevel]	.= "Promoted<br /><br />";
							$compl[$thisLevel]++;
							if ($doDebug) {
								echo "<span style='margin-left:15px;'>Passed the $whichLevel class. passedClass is TRUE</span><br />";
							}
							break;
						case "W":
							$withdrewClass[$thisLevel]	= TRUE;
							$arrayLevels[$thisLevel]	.= "Withdrew<br /><br />";
							$withdw[$thisLevel]++;
							if ($doDebug) {
								echo "<span style='margin-left:15px;'>Withdrew from the $whichLevel class. withdrewClass is TRUE</span><br />";
							}
							break;
						case "N":
							$noPassClass[$thisLevel]	= TRUE;
							$arrayLevels[$thisLevel]	.= "Did not pass<br /><br />";
							$notProm[$thisLevel]++;
							if ($doDebug) {
								echo "<span style='margin-left:15px;'>promotable is N. noPassClass is TRUE</span><br />";
							}
							break;
						default:
	//						if ($doDebug) {
								echo "<span style='margin-left:15px;'>$myValue Failed every promotable case</span><br />";
	//						}
					}
	//				$arrayLevels[$thisLevel]			.= "<br /><br />";

				}
			}	
	// calculate the percentages
			$pc01			= array(0=>0,1=>0,2=>0,3=>0,4=>0);
			$pc02			= array(0=>0,1=>0,2=>0,3=>0,4=>0);
			$pc03			= array(0=>0,1=>0,2=>0,3=>0,4=>0);
			$pc04			= array(0=>0,1=>0,2=>0,3=>0,4=>0);
			$pc05			= array(0=>0,1=>0,2=>0,3=>0,4=>0);
			$pc06			= array(0=>0,1=>0,2=>0,3=>0,4=>0);
			$pc07			= array(0=>0,1=>0,2=>0,3=>0,4=>0);
			$pc08			= array(0=>0,1=>0,2=>0,3=>0,4=>0);
			$pc09			= array(0=>0,1=>0,2=>0,3=>0,4=>0);
			$pc10			= array(0=>0,1=>0,2=>0,3=>0,4=>0);
			$pc11			= array(0=>0,1=>0,2=>0,3=>0,4=>0);
			$pc12			= array(0=>0,1=>0,2=>0,3=>0,4=>0);
			$pc13			= array(0=>0,1=>0,2=>0,3=>0,4=>0);
			for ($ii=1;$ii<=4;$ii++) {
				$pc01[$ii] = round(($responseBlank[$ii] / $registeredClass[$ii] * 100),1);
				$pc02[$ii] = round(($responseR[$ii] / $registeredClass[$ii] * 100),1);
				$pc03[$ii] = round(($responseY[$ii] / $registeredClass[$ii] * 100),1);
				$pc04[$ii] = round(($statusBlank[$ii] / $registeredClass[$ii] * 100),1);
				$pc05[$ii] = round(($statusN[$ii] / $registeredClass[$ii] * 100),1);
				$pc06[$ii] = round(($statusC[$ii] / $registeredClass[$ii] * 100),1);
				$pc07[$ii] = round(($statusR[$ii] / $registeredClass[$ii] * 100),1);
				$pc08[$ii] = round(($statusY[$ii] / $registeredClass[$ii] * 100),1);
				$pc09[$ii] = round(($withdw[$ii] / $registeredClass[$ii] * 100),1);
				$pc10[$ii] = round(($notProm[$ii] / $registeredClass[$ii] * 100),1);
				$pc11[$ii] = round(($compl[$ii] / $registeredClass[$ii] * 100),1);
			}
			$pc13[1] = round(($tookBegOnly / $registeredClass[1] * 100),1);
			$pc13[2] = round(($tookFunOnly / $registeredClass[2] * 100),1);
			$pc13[3] = round(($tookIntOnly / $registeredClass[3] * 100),1);
			$pc13[4] = round(($tookAdvOnly / $registeredClass[4] * 100),1);
			for ($ii=1;$ii<=3;$ii++) {
				$pc12[$ii] = round(($nextLevel[$ii] / $registeredClass[$ii] * 100),1);
			}
			if ($inp_details == 'Y') {
				$content	.= "</table><br />";
			}
			$content	.= "<h4>Statistics</h4>
							<table style='width:auto;'>
							<tr>
								<th style='width:300px;'>Statistic</th>
								<th style='width:110px;'><b>Beginner</th>
								<th style='width:110px;'><b>Fundamental</th>
								<th style='width:110px;'><b>Intermediate</th>
								<th style='width:110px;'>Advanced</th>
							</tr><tr>
								<td>First Registrations*</td>
								<td style='text-align:center;'>$respVer[1]</td>
								<td style='text-align:center;'>$respVer[2]</td>
								<td style='text-align:center;'>$respVer[3]</td>
								<td style='text-align:center;'>$respVer[4]</td>
							</tr><tr>
								<td>Student registrations</td>
								<td style='text-align:center;'>$registeredClass[1] (100%)</td>
								<td style='text-align:center;'>$registeredClass[2] (100%)</td>
								<td style='text-align:center;'>$registeredClass[3] (100%)</td>
								<td style='text-align:center;'>$registeredClass[4] (100%)</td>
							</tr><tr>
								<td>Students didn't respond to verification request</td>
								<td style='text-align:center;'>$responseBlank[1] ($pc01[1]%)</td>
								<td style='text-align:center;'>$responseBlank[2] ($pc01[2]%)</td>
								<td style='text-align:center;'>$responseBlank[3] ($pc01[3]%)</td>
								<td style='text-align:center;'>$responseBlank[4] ($pc01[4]%)</td>
							</tr><tr>
								<td>Students refusing the verification request</td>
								<td style='text-align:center;'>$responseR[1] ($pc02[1]%)</td>
								<td style='text-align:center;'>$responseR[2] ($pc02[2]%)</td>
								<td style='text-align:center;'>$responseR[3] ($pc02[3]%)</td>
								<td style='text-align:center;'>$responseR[4] ($pc02[4]%)</td>
							</tr><tr>
								<td>Students verified</td>
								<td style='text-align:center;'>$responseY[1] ($pc03[1]%)</td>
								<td style='text-align:center;'>$responseY[2] ($pc03[2]%)</td>
								<td style='text-align:center;'>$responseY[3] ($pc03[3]%)</td>
								<td style='text-align:center;'>$responseY[4] ($pc03[4]%)</td>
							</tr><tr>
								<td colspan='5'>&nbsp;</td></tr>
								<td>Students unassigned</td>
								<td style='text-align:center;'>$statusBlank[1] ($pc04[1]%)</td>
								<td style='text-align:center;'>$statusBlank[2] ($pc04[2]%)</td>
								<td style='text-align:center;'>$statusBlank[3] ($pc04[3]%)</td>
								<td style='text-align:center;'>$statusBlank[4] ($pc04[4]%)</td>
							</tr><tr>
								<td>Students dropped by advisor</td>
								<td style='text-align:center;'>$statusN[1] ($pc05[1]%)</td>
								<td style='text-align:center;'>$statusN[2] ($pc05[2]%)</td>
								<td style='text-align:center;'>$statusN[3] ($pc05[3]%)</td>
								<td style='text-align:center;'>$statusN[4] ($pc05[4]%)</td>
							</tr><tr>
								<td>Students replaced</td>
								<td style='text-align:center;'>$statusC[1] ($pc06[1]%)</td>
								<td style='text-align:center;'>$statusC[2] ($pc06[2]%)</td>
								<td style='text-align:center;'>$statusC[3] ($pc06[3]%)</td>
								<td style='text-align:center;'>$statusC[4] ($pc06[4]%)</td>
							</tr><tr>
								<td>Students replacement requested</td>
								<td style='text-align:center;'>$statusR[1] ($pc07[1]%)</td>
								<td style='text-align:center;'>$statusR[2] ($pc07[2]%)</td>
								<td style='text-align:center;'>$statusR[3] ($pc07[3]%)</td>
								<td style='text-align:center;'>$statusR[4] ($pc07[4]%)</td>
							</tr><tr>
								<td>Students assigned advisors</td>
								<td style='text-align:center;'>$statusY[1] ($pc08[1]%)</td>
								<td style='text-align:center;'>$statusY[2] ($pc08[2]%)</td>
								<td style='text-align:center;'>$statusY[3] ($pc08[3]%)</td>
								<td style='text-align:center;'>$statusY[4] ($pc08[4]%)</td>
							</tr><tr>
								<tr><td colspan='5'>&nbsp;</td></tr>
								<td>Students who withdrew</td>
								<td style='text-align:center;'>$withdw[1] ($pc09[1]%)</td>
								<td style='text-align:center;'>$withdw[2] ($pc09[2]%)</td>
								<td style='text-align:center;'>$withdw[3] ($pc09[3]%)</td>
								<td style='text-align:center;'>$withdw[4] ($pc09[4]%)</td>
							</tr><tr>
								<td>Students not Promoted</td>
								<td style='text-align:center;'>$notProm[1] ($pc10[1]%)</td>
								<td style='text-align:center;'>$notProm[2] ($pc10[2]%)</td>
								<td style='text-align:center;'>$notProm[3] ($pc10[3]%)</td>
								<td style='text-align:center;'>$notProm[4] ($pc10[4]%)</td>
							</tr><tr>
								<td>Students Promoted</td>
								<td style='text-align:center;'>$compl[1] ($pc11[1]%)</td>
								<td style='text-align:center;'>$compl[2] ($pc11[2]%)</td>
								<td style='text-align:center;'>$compl[3] ($pc11[3]%)</td>
								<td style='text-align:center;'>$compl[4] ($pc11[4]%)</td>
							</tr><tr>
								<td colspan='5'>&nbsp;</td>
							</tr><tr>
								<td>Students registered for next level</td>
								<td style='text-align:center;'>$nextLevel[1] ($pc12[1]%)</td>
								<td style='text-align:center;'>$nextLevel[2] ($pc12[2]%)</td>
								<td style='text-align:center;'>$nextLevel[3] ($pc12[3]%)</td>
								<td style='text-align:center;'>&nbsp;</td>
							</tr><tr>
								<td colspan='5'>&nbsp;</td>
							</tr><tr>
								<td>Students promoted from one level only</td>
								<td style='text-align:center;'>$tookBegOnly ($pc13[1]%)</td>
								<td style='text-align:center;'>$tookFunOnly ($pc13[2]%)</td>
								<td style='text-align:center;'>$tookIntOnly ($pc13[3]%)</td>
								<td style='text-align:center;'>$tookAdvOnly ($pc13[4]%)</td>
							</tr><tr>
								<td colspan='5'>Students who took all levels and were promoted from all levels: $tookAll</td>
							</tr>
							</table><p>* These are students whose first registration was for this level 
							of a class. Student registrations count all registrations for this level of 
							class, including first registrations and re-registrations.</p>";
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
	$result			= write_joblog_func("Student Progression Report|$nowDate|$nowTime|$userName|Time|$thisStr|$strPass: $elapsedTime");
	if ($result == 'FAIL') {
		$content	.= "<p>writing to joblog.txt failed</p>";
	}
	return $content;
}
add_shortcode ('student_progression_report', 'student_progression_report_func');
