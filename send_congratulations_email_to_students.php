function send_congratulations_email_to_students_func() {

/*	Send congratulations email to students
 *	
 *	Job is run shortly after the semester ends and all advisor
 *		evaluations have been received that are going to be received
 *	
 *	Function reads the student table for students in the just-completed
 *		semester and have a student_status of 'Y', meaning they attended a class.
 *
 *	If promotable == 'W' (withdrew), no action is taken
 *	If promotable == 'N'
 *		see if there is a registration for a future class in student table
 *			If not, send email encouraging student to take the class again
 *				Student will have an elevated priority
 *				Student will have a different advisor
 *			If so, and the registration is for the same level again
 *				Thank student for continuing to pursue Morse Code
 *				Student will have elevated priority
 *				Student will have a different advisor
 *			If so, and the registration is for a higher level
 *				Don't do anything. Will be handled when preparing to assign students to advisors
 *	If promotable == 'Y' and level is Beginner, Fundamental, or Intermediate
 *		See if there is a registration for a future class in the student table
 *			If not, send congratulation email to student
 *				Encourage to take the next level
 *				Student would have elevated priority
 *			If so, and the registration is for the same level
 *				Send congratulations email
 *				Thank student for continuing to pursue Morse Code excellency
 *				Student will have elevated priority
 *				Student will have a different advisor
 *			If so, and the registration is for a higher level
 *				Send congratulations email
 *				Thank student for continuing to pursue Morse Code excellency
 *				Student will have elevated priority
 *	If promotable == 'Y' and level is Advanced
 *		See if there is a registration for a future class
 *			If not, send congratulation email to student
 *				encourage student to become an advisor
 *			If so, send congratuilation email to student
 *				Thank student for continuing to pursue Morse Code excellency
 *				Encourage student to consider becoming an advisor in the future
 *
 *	Written 11Jun2020
 *	Modified 4Mar21 by Roland to change Joe Fisher's email address
 	Modified 26Oct22 by Roland for new timezone table format
 	Modified 17Apr23 by Roland to fix action_log
 	Modified 14Jul23 by Roland to use consolidated tables
*/

	global $wpdb;

	$doDebug					= FALSE;
	$testMode					= FALSE;
	$bobTest					= FALSE;
	$initializationArray 		= data_initialization_func();
	if ($doDebug) {
		echo "Initialization Array:<br /><pre>";
		print_r($initializationArray);
		echo "</pre><br />";
	}
	$validUser = $initializationArray['validUser'];
	if ($validUser == "N") {
		return "YOU'RE NOT AUTHORIZED!<br />Goodby";
	}
	$userName				= $initializationArray['userName'];
	$validTestmode			= $initializationArray['validTestmode'];
	$siteURL				= $initializationArray['siteurl'];
	
	ini_set('memory_limit','256M');
	ini_set('max_execution_time',0);
//	if ($doDebug || $testMode) {
		ini_set('display_errors','1');
		error_reporting(E_ALL);	
//	}

/// get the time that the process started
	$startingMicroTime			= microtime(TRUE);

	$strPass					= "1";
	$theURL						= "$siteURL/cwa-send-congratulations-email-to-students/";
	$advisorURL				 	= "$siteURL/cwa-advisor-registration/";
	$increment					= 0;
	$jobname					= 'Send Congratulations Email to Students';

// get the input information
	if (isset($_REQUEST)) {
		foreach($_REQUEST as $str_key => $str_value) {
			if ($doDebug) {
				echo "Key: $str_key | Value: $str_value <br />\n";
			}
			if ($str_key 		== "strpass") {
				$strPass		 = $str_value;
				$strPass		 = filter_var($strPass,FILTER_UNSAFE_RAW);
			}
			if ($str_key 			== "testmode") {
				$myValue			 = $str_value;
				$myValue			 = filter_var($myValue,FILTER_UNSAFE_RAW);
				if ($myValue == 'testmode') {
					$testMode			 = TRUE;
				} else {
					$testMode			 = FALSE;
				}
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

table{font:'Times New Roman', sans-serif;background-image:none;}

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

	function checkClassLevel($currentLevel,$classArray,$doDebug) {
		$levelConvert		= array('Beginner'=>1,'Fundamental'=>2,'Intermediate'=>3,'Advanced'=>4);
		$semesterConvert	= array('Jan/Feb'=>1,'May/Jun'=>2,'Sep/Oct'=>3);
		$newArray			= array();
		$currentNumber		= $levelConvert[$currentLevel];
		if ($doDebug) {
			echo "checkClassLevel: Converted currentLevel $currentLevel to $currentNumber<br />";
		}
		foreach($classArray as $classValue) {
			if ($doDebug) {
				echo "checkClassLevel: Processing classArray value $classValue<br />";
			}
			$myArray		= explode(",",$classValue);
			$newLevel		= $levelConvert[$myArray[0]];
			$semesters		= explode(" ",$myArray[1]);
			$theYear		= $semesters[0];
			$theNumber		= $semesterConvert[$semesters[1]];
			$newArray[]		= "$theYear$theNumber,$newLevel";
			if ($doDebug) {
				echo "checkClassLevel: Result of process: $theYear$theNumber,$newLevel<br />";
			}
		}
		sort($newArray);
		if ($doDebug) {
			echo "checkClassLevel: sorted classArray:<br /><pre>";
			print_r($newArray);
			echo "</pre><br />";
		}
		$nextClass			= $newArray[0];
		$myArray			= explode(",",$nextClass);
		if ($doDebug) {
			echo "checkClassLevel: checking next class level of $myArray[1] in semester $myArray[0] against current level $currentNumber<br />";
		}
		if ($myArray[1] <= $currentNumber) {
			$theReturn	= array('sameorlower',$myArray[0]);
			if ($doDebug) {
				echo "The Return:<br /><pre>";
				print_r($theReturn);
				echo "</pre><br />";
			}
			return $theReturn;
		} else {
			$theReturn	= array('higher',$myArray[0]);
			if ($doDebug) {
				echo "The Return:<br /><pre>";
				print_r($theReturn);
				echo "</pre><br />";
			}
			return $theReturn;
		}
	}

	
	$promotableCount			= 0;
	$notPromotableCount			= 0;
	$withdrewCount				= 0;
	$promotableBFINoClass		= 0;
	$promotableBFISameClass		= 0;
	$promotableBFINextClass		= 0;
	$promotableADVNoClass		= 0;
	$promotableADVSameClass		= 0;
	$notPromotableBFINoClass	= 0;
	$notPromotableBFISameClass	= 0;
	$notPromotableBFINextClass	= 0;
	$notPromotableADVNoClass	= 0;
	$notPromotableADVSameClass	= 0;
	$promotableBlank			= 0;
	$emailsSent					= 0;
	$errorArray					= array();
	$myDate						= date('d/m/Y');
	$bbiArray					= array('Fundamental','Beginner','Intermediate');
	$levelConvertA				= array('Beginner'=>1,'Fundamental'=>2,'Intermediate'=>3,'Advanced'=>4);
	$levelConvertB				= array('Beginner','Fundamental','Intermediate','Advanced');
	$semesterArray				= array('Jan/Feb','May/Jun','Sep/Oct');
	
	if ($testMode) {
		$studentTableName	= 'wpw1_cwa_consolidated_student';
		$advisorTableName	= 'wpw1_cwa_consolidated_advisor';
		if ($doDebug) {
			echo "Function is under development<br />";
		}
		$content .= "<p>Function is under development.</p>";
	} else {
		$studentTableName	= 'wpw1_cwa_consolidated_student';
		$advisorTableName	= 'wpw1_cwa_consolidated_advisor';
	}

	$currentSemester	= $initializationArray['currentSemester'];
	$prevSemester		= $initializationArray['prevSemester'];
	$nextSemester		= $initializationArray['nextSemester'];
	$semesterTwo		= $initializationArray['semesterTwo'];
	$semesterThree		= $initializationArray['semesterThree'];
	if ($currentSemester == 'Not in Session') {
		$thisSemester	= $prevSemester;
	} else {
		$thisSemester	= $currentSemester;
	}



	if ("1" == $strPass) {
		if ($doDebug) {
			echo "Function starting.<br />";
		}
		$content 		.= "<h3>Send Congratulation Email to Student</h3>
<p>Click 'Submit' to start the process.</p>
<p><form method='post' action='$theURL' 
name='selection_form' ENCTYPE='multipart/form-data''>
<input type='hidden' name='strpass' value='2'>";
		if (in_array($userName,$validTestmode)) {
			$content	.= "<table><tr><td style='width:150px;'><b>Select Operation Mode</b></td></tr>
<tr><td><input type='radio' class='formInputButton' name='testmode' value='production' checked='checked'> Use Production Tables</td></tr>
<tr><td><input type='radio' class='formInputButton' name='testmode' value='testmode'> Operate in Test Mode</td></tr>
</table>";
		}
		$content	.= "<input class='formInputButton' type='submit' value='Submit' />
</form></p>";
		return $content;

///// Pass 2 -- do the work


	} elseif ("2" == $strPass) {


		$sql					= "select * from $studentTableName 
									where semester='$thisSemester' 
									and student_status='Y' 
									order by call_sign ";
		$wpw1_cwa_student		= $wpdb->get_results($sql);
		if ($wpw1_cwa_student === FALSE) {
			if ($doDebug) {
				echo "Reading $studentTableName table failed<br />";
				echo "wpdb->last_query: " . $wpdb->last_query . "<br />";
				echo "<b>wpdb->last_error: " . $wpdb->last_error . "</b><br />";
			}
		} else {
			$numPSRows				= $wpdb->num_rows;
			if ($doDebug) {
				$myStr				= $wpdb->last_query;
				echo "ran $myStr<br />and found $numPSRows rows in $studentTableName table<br />";
			}
			if ($numPSRows > 0) {
				foreach ($wpw1_cwa_student as $studentRow) {
					$student_ID							= $studentRow->student_id;
					$student_call_sign						= strtoupper($studentRow->call_sign);
					$student_first_name					= $studentRow->first_name;
					$student_last_name						= stripslashes($studentRow->last_name);
					$student_email  						= strtolower(strtolower($studentRow->email));
					$student_ph_code						= $studentRow->ph_code;
					$student_phone  						= $studentRow->phone;
					$student_city  						= $studentRow->city;
					$student_state  						= $studentRow->state;
					$student_zip_code  					= $studentRow->zip_code;
					$student_country_code					= $studentRow->country_code;
					$student_country  						= $studentRow->country;
					$student_time_zone  					= $studentRow->time_zone;
					$student_timezone_id					= $studentRow->timezone_id;
					$student_timezone_offset				= $studentRow->timezone_offset;
					$student_whatsapp						= $studentRow->whatsapp_app;
					$student_signal						= $studentRow->signal_app;
					$student_telegram						= $studentRow->telegram_app;
					$student_messenger						= $studentRow->messenger_app;					
					$student_wpm 	 						= $studentRow->wpm;
					$student_youth  						= $studentRow->youth;
					$student_age  							= $studentRow->age;
					$student_student_parent 				= $studentRow->student_parent;
					$student_student_parent_email  		= strtolower($studentRow->student_parent_email);
					$student_level  						= $studentRow->level;
					$student_waiting_list 					= $studentRow->waiting_list;
					$student_request_date  				= $studentRow->request_date;
					$student_semester						= $studentRow->semester;
					$student_notes  						= $studentRow->notes;
					$student_welcome_date  				= $studentRow->welcome_date;
					$student_email_sent_date  				= $studentRow->email_sent_date;
					$student_email_number  				= $studentRow->email_number;
					$student_response  					= strtoupper($studentRow->response);
					$student_response_date  				= $studentRow->response_date;
					$student_abandoned  				= $studentRow->abandoned;
					$student_student_status  				= strtoupper($studentRow->student_status);
					$student_action_log  					= $studentRow->action_log;
					$student_pre_assigned_advisor  		= $studentRow->pre_assigned_advisor;
					$student_selected_date  				= $studentRow->selected_date;
					$student_no_catalog		  			= $studentRow->no_catalog;
					$student_hold_override  				= $studentRow->hold_override;
					$student_messaging  					= $studentRow->messaging;
					$student_assigned_advisor  			= $studentRow->assigned_advisor;
					$student_advisor_select_date  			= $studentRow->advisor_select_date;
					$student_advisor_class_timezone 		= $studentRow->advisor_class_timezone;
					$student_hold_reason_code  			= $studentRow->hold_reason_code;
					$student_class_priority  				= $studentRow->class_priority;
					$student_assigned_advisor_class 		= $studentRow->assigned_advisor_class;
					$student_promotable  					= $studentRow->promotable;
					$student_excluded_advisor  			= $studentRow->excluded_advisor;
					$student_student_survey_completion_date = $studentRow->student_survey_completion_date;
					$student_available_class_days  		= $studentRow->available_class_days;
					$student_intervention_required  		= $studentRow->intervention_required;
					$student_copy_control  				= $studentRow->copy_control;
					$student_first_class_choice  			= $studentRow->first_class_choice;
					$student_second_class_choice  			= $studentRow->second_class_choice;
					$student_third_class_choice  			= $studentRow->third_class_choice;
					$student_first_class_choice_utc  		= $studentRow->first_class_choice_utc;
					$student_second_class_choice_utc  		= $studentRow->second_class_choice_utc;
					$student_third_class_choice_utc  		= $studentRow->third_class_choice_utc;
					$student_date_created 					= $studentRow->date_created;
					$student_date_updated			  		= $studentRow->date_updated;


					$sendBFINoClassEmail		= FALSE;
					$sendBFISameClassEmail		= FALSE;
					$sendBFINextClassEmail		= FALSE;
					$sendADVNoClassEmail		= FALSE;
					$sendADVClassEmail			= FALSE;
					$sendNPBFINoClassEmail		= FALSE;
					$sendNPBFISameClassEmail	= FALSE;
					$sendNPADVNoClassEmail		= FALSE;
					$sendNPADVSameClassEmail	= FALSE;

					if ($student_promotable != 'W') {
						if ($doDebug) {
							echo "<br />student $student_call_sign info<br />
								  &nbsp;&nbsp;&nbsp;Promotable: $student_promotable<br />
								  &nbsp;&nbsp;&nbsp;Level: $student_level<br />";
						}
						if ($student_promotable == '' || $student_promotable == ' ') {
							$promotableBlank++;
							if ($doDebug) {
								echo "&nbsp;&nbsp;&nbsp;Assigned advisor: $student_assigned_advisor<br />
									  &nbsp;&nbsp;&nbsp;level: $student_level<br />";
							}
						}
						if ($student_promotable == 'N') {
							$notPromotableCount++;
							$sql				= "select * from $studentTableName 
												   where call_sign='$student_call_sign' 
												   and (semester='$nextSemester' 
												   	    or semester='$semesterTwo' 
												   	    or semester='$semesterThree')  
												   order by date_created 
												   limit 1";
							$wpw1_cwa_student	= $wpdb->get_results($sql);
							if ($wpw1_cwa_student === FALSE) {
								if ($doDebug) {
									echo "Reading $studentTableName table failed<br />";
									echo "wpdb->last_query: " . $wpdb->last_query . "<br />";
									echo "<b>wpdb->last_error: " . $wpdb->last_error . "</b><br />";
								}
							} else {
								$numberSRecords	= $wpdb->num_rows;
								if ($doDebug) {
									$myStr		= $wpdb->last_query;
									echo "ran $myStr<br />and retrieved $numberSRecords rows from $studentTableName table<br />";
								}

								if (in_array($student_level,$bbiArray)) {			// bbi student
									if ($numberSRecords == 0) {				// not promotable, not in a class
										$notPromotableBFINoClass++;
										$sendNPBFINoClassEmail	= TRUE;
									} else {								// not promotable, in class. Get class info
										$myArray				= array();
										foreach ($wpw1_cwa_student as $studentRow) {
											$prev_student_level  						= $studentRow->level;
											$prev_student_semester						= $studentRow->semester;

											$myArray[]			= "$prev_student_level,$prev_student_semester";
										}
										$theResult				= checkClassLevel($student_level,$myArray,$doDebug);
										if ($theResult[0] == 'sameorlower') {
											$notPromotableBFISameClass++;
											$sendNPBFISameClassEmail	= TRUE;
										} else {
											$notPromotableBFINextClass++;
											if ($doDebug) {
												echo "&nbsp;&nbsp;&nbsp;No email. Problem will be handled when assigning students to advisors<br />";
											}
											$errorArray[]		= "$student_call_sign not promotable but signed up for the next level class<br />";
										}
									}
								} else {												// advanced student
									if ($numberSRecords == 0) {			// not registered for another class
										$notPromotableADVNoClass++;
										$sendNPADVNoClassEmail	= TRUE;
									} else {							// registered for one or more classes
										foreach ($wpw1_cwa_student as $studentRow) {
											$prev_student_level  	= $studentRow->level;
											$prev_student_semester	= $studentRow->semester;
											$myArray[]			= "$prev_student_level,$prev_student_semester";
										}
										$theResult				= checkClassLevel($student_level,$myArray,$doDebug);
										if ($theResult[0] == 'sameorlower') {
											$notPromotableADVSameClass++;
											$sendNPADVSameClassEmail	= TRUE;
										}								
									}
								}	
							}
						}					/// end of not promotable

						if ($student_promotable == 'P') {
							$promotableCount++;
							$sql				= "select * from $studentTableName 
													where call_sign='$student_call_sign' 
												   and (semester='$nextSemester' 
												   	    or semester='$semesterTwo' 
												   	    or semester='$semesterThree')  
												   order by date_created 
												   limit 1";
							$wpw1_cwa_student	= $wpdb->get_results($sql);
							if ($wpw1_cwa_student === FALSE) {
								if ($doDebug) {
									echo "Reading $studentTableName table failed<br />";
									echo "wpdb->last_query: " . $wpdb->last_query . "<br />";
									echo "<b>wpdb->last_error: " . $wpdb->last_error . "</b><br />";
								}
							} else {
								$numSRecords	= $wpdb->num_rows;
								if ($doDebug) {
									$myStr		= $wpdb->last_query;
									echo "ran $myStr<br />and retrieved $numSRecords rows from $studentTableName table<br />";
								}
								if (in_array($student_level,$bbiArray)) {			// bbi student
									if ($numSRecords == 0) {				// not promotable, not in a class
										$promotableBFINoClass++;
										$sendBFINoClassEmail	= TRUE;
										if ($doDebug) {
											echo "set sendBFINoClassEmail to TRUE<br />";
										}
									} else {								// not promotable, in class. Get class info
										$myArray				= array();
										foreach ($wpw1_cwa_student as $studentRow) {
											$prev_student_level  	= $studentRow->level;
											$prev_student_semester	= $studentRow->semester;
											$myArray[]			= "$prev_student_level,$prev_student_semester";
										}
										$theResult				= checkClassLevel($student_level,$myArray,$doDebug);
										if ($theResult[0] == 'sameorlower') {
											$promotableBFISameClass++;
											$sendBFISameClassEmail	= TRUE;
											if ($doDebug) {
												echo "set sendBFISameClassEmail to TRUE<br />";
											}
										} else {
											$promotableBFINextClass++;
											$sendBFINextClassEmail	= TRUE;
											if ($doDebug) {
												echo "set sendBFINextClassEmail to TRUE<br />";
											}
										}
									}
								} else {												// advanced student
									if ($numSRecords == 0) {			// not registered for another class
										$promotableADVNoClass++;
										$sendADVNoClassEmail	= TRUE;
										if ($doDebug) {
											echo "set sendADVNoClassEmail to TRUE<br />";
										}
									} else {							// registered for one or more classes
										foreach ($wpw1_cwa_student as $studentRow) {
											$prev_student_level  	= $studentRow->level;
											$prev_student_semester	= $studentRow->semester;
											$myArray[]			= "$prev_student_level,$prev_student_semester";
										}
										$theResult				= checkClassLevel($student_level,$myArray,$doDebug);
										if ($theResult[0] == 'sameorlower') {
											$promotableADVSameClass++;
											$sendADVSameClassEmail	= TRUE;
											if ($doDebug) {
												echo "set sendADVSameClassEmail to TRUE<br />";
											}
										}								
									}
								}
							}
						}					// end of promotable loop
					} else {
						if ($doDebug) {
							echo "<br />Student $student_call_sign has a promotable value of $student_promotable; assigned advisor: $student_assigned_advisor; level: $student_level<br />";
						}
						$withdrewCount++;
					}
					// student processed. Send an email?
					$sendTheEmail		= FALSE;
					if ($sendBFINoClassEmail) {
						if ($doDebug) {
							echo "&nbsp;&nbsp&nbsp;Sending promotable BFI no next class email<br />";
						}
						$currentNumber	= $levelConvertA[$student_level];
						$newLevel		= $levelConvertB[$currentNumber];
						$theSubject		= "CW Academy - Thank You for Participating";
						$theEmail		= "<p>To: $student_last_name, $student_first_name ($student_call_sign)</p>
<p>Congratulations on successfully completing the $student_level CW Academy $thisSemester class. 
Hopefully you have started using your Morse code skills on the air!</p>
<p>CW Academy encourages you to register for an $newLevel CW Academy class. You can also consider 
taking the $student_level class again. Regardless, since you now know and 
understand the CW Academy philosophy and class structure, you will be given heightened 
priority for a class.</p>
<p>Thank you and 73,<br />
CW Academy";
						$sendTheEmail	= TRUE;	
						$actionLogData	= "Sent promotable BFI no next class registration email ";
					} elseif ($sendBFISameClassEmail) {
						if ($doDebug) {
							echo "&nbsp;&nbsp&nbsp;Sending promotable BFI same class email<br />";
						}
						$theYear		= substr($theResult[1],0,4);
						$theSemester	= substr($theResult[1],4,1);
						$newSemester	= $semesterArray[$theSemester-1];
						$theSubject		= "CW Academy - Thank You for Participating";
						$theEmail		= "<p>To: $student_last_name, $student_first_name ($student_call_sign)</p>
<p>Congratulations on successfully completing the $student_level CW Academy $thisSemester class. 
Hopefully you have started using your Morse code skills on the air!</p>
<p>You have registered to take the $student_level class again in the $theYear $newSemester 
semester. Since you now know and understand the CW Academy philosophy and class structure, 
you will be given heightened priority for a class. In addition, you will be assigned to a 
different advisor for that class which will give you a different perspective.</p>
<p>Thank you and 73,<br />
CW Academy</p>";
						$sendTheEmail	= TRUE;
						$actionLogData	= "Sent promotable BFI same class registration email ";
					} elseif ($sendBFINextClassEmail) {
						if ($doDebug) {
							echo "&nbsp;&nbsp&nbsp;Sending promotable BFI next class email<br />";
						}
						$theYear		= substr($theResult[1],0,4);
						$theSemester	= substr($theResult[1],4,1);
						$newSemester	= $semesterArray[$theSemester-1];
						$currentLevel	= $levelConvertA[$student_level];
						$newLevel		= $levelConvertB[$currentLevel];
						$theSubject		= "CW Academy - Thank You for Participating";
						$theEmail		= "<p>To: $student_last_name, $student_first_name ($student_call_sign)</p>
<p>Congratulations on successfully completing the $student_level CW Academy $thisSemester class. 
Hopefully you have started using your Morse code skills on the air!</p>
<p>You have registered to take the $prev_student_level class in the $theYear $newSemester semester,
 continuing to improve your Morse code proficiency. Since you now know and understand the 
 CW Academy philosophy and class structure, you will be given heightened priority for the class.</p>
<p>Thank you and 73,<br />
CW Academy";
						$sendTheEmail	= TRUE;
						$actionLogData	= "Sent promotable BFI next class registration email ";
					} elseif ($sendADVNoClassEmail) {
						if ($doDebug) {
							echo "&nbsp;&nbsp&nbsp;Sending promotable Advanced no next class email<br />";
						}
						$theSubject		= "CW Academy - Thank You for Participating";
						$theEmail		= "<p>To: $student_last_name, $student_first_name ($student_call_sign)</p>
<p>Congratulations on successfully completing the $student_level CW Academy $thisSemester class. 
Hopefully you are using your Morse code skills on the air!<p>
<p>CW Academy is actively recruiting advisors. The number of students 
registering each semester continues to grow. Please consider using your Morse code skills 
to benefit the next class of eager students.<p>
<p>To register as an advisor, click on <a href='$advisorURL'_blank'>
CWA Advisor Sign-up</a>.</p>
<p>Thank you and 73,<br />
CW Academy</p>";
						$sendTheEmail	= TRUE;
						$actionLogData	= "Sent promotable Advanced no next class registration email ";
					} elseif ($sendADVClassEmail) {
						if ($doDebug) {
							echo "&nbsp;&nbsp&nbsp;Sending promotable Advanced same class email<br />";
						}
						$theYear		= substr($theResult[1],0,4);
						$theSemester	= substr($theResult[1],4,1);
						$newSemester	= $semesterArray[$theSemester-1];
						$theSubject		= "CW Academy - Thank You for Participating";
						$theEmail		= "<p>To: $student_last_name, $student_first_name ($student_call_sign)</p>
<p>Congratulations on successfully completing the $student_level CW Academy $thisSemester class. 
Hopefully you are using your Morse code skills on the air!</p>
<p>You have registered to take the $student_level class in the $theYear $newSemester 
semester, continuing to improve your Morse code proficiency. Since you now know and 
understand the CW Academy philosophy and class structure, you will be given heightened 
priority for the class. You will also be assigned to a different advisor.</p>
<p>Thank you and 73,<br />
CW Academy";
						$sendTheEmail	= TRUE;
						$actionLogData	= "Sent promotable Advanced same class registration email ";
					} elseif ($sendNPBFINoClassEmail) {
						if ($doDebug) {
							echo "&nbsp;&nbsp&nbsp;Sending not promotable BFI no next class email<br />";
						}
						$theSubject		= "CW Academy - Thank You for Participating";
						$theEmail		= "<p>To: $student_last_name, $student_first_name ($student_call_sign)</p>
<p>Thank you for participating in the $student_level CW Academy $thisSemester class.</p>
<p>The CW Academy encourages you to re-take the $student_level class and build on the Morse code 
skills you were able to achieve last semester.</p>
<p>Since you now know and understand the CW Academy philosophy and class structure, 
if you register to re-take the $student_level class, you will not only be given a higher 
priority, but will also be given a different advisor who will give you another perspective 
on this class.</p>
<p>Thank you and 73,<br />
CW Academy";
						$sendTheEmail	= TRUE;
						$actionLogData	= "Sent not promotable BFI no next class registration email ";
					} elseif ($sendNPBFISameClassEmail) {
						if ($doDebug) {
							echo "&nbsp;&nbsp&nbsp;Sending not promotable BFI same class email<br />";
						}
						$theYear		= substr($theResult[1],0,4);
						$theSemester	= substr($theResult[1],4,1);
						$newSemester	= $semesterArray[$theSemester-1];
						$theSubject		= "CW Academy - Thank You for Participating";
						$theEmail		= "<p>To: $student_last_name, $student_first_name ($student_call_sign)</p>
<p>Thank you for participating in the $student_level CW Academy $thisSemester class. </p>
<p>You have registered to take the $student_level class in the $theYear $newSemester 
semester. Since you now know and understand the CW Academy philosophy and class structure, 
you will be given heightened priority for the class. You will also be assigned to a 
different advisor which will give you a different perspective.</p>
<p>Thank you and 73,<br />
CW Academy";
						$sendTheEmail	= TRUE;
						$actionLogData	= "Sent not promotable BFI same class registration email ";
					} elseif ($sendNPADVNoClassEmail) {
						if ($doDebug) {
							echo "&nbsp;&nbsp&nbsp;Sending not promotable Advanced no next class email<br />";
						}
						$theYear		= substr($theResult[1],0,4);
						$theSemester	= substr($theResult[1],4,1);
						$newSemester	= $semesterArray[$theSemester-1];
						$theSubject		= "CW Academy - Thank You for Participating";
						$theEmail		= "<p>To: $student_last_name, $student_first_name ($student_call_sign)</p>
<p>Thank you for participating in the Advanced CW Academy $thisSemester class. </p>
<p>The CW Academy encourages you to re-take the Advanced class in the $theYear $newSemester 
semester and build on the Morse code skills you were able to achieve last semester. </p>
<p>Since you now know and understand the CW Academy philosophy and class structure, 
if you register to re-take the Advanced class, you will be given heightened priority 
for the class and you will be assigned to a different advisor.</p>
<p>Thank you and 73,<br />
CW Academy";
						$sendTheEmail	= TRUE;
						$actionLogData	= "Sent not promotable Advanced no next class registration email ";
					} elseif ($sendNPADVSameClassEmail) {
						if ($doDebug) {
							echo "&nbsp;&nbsp&nbsp;Sending promotable Advanced same class email<br />";
						}
						$theYear		= substr($theResult[1],0,4);
						$theSemester	= substr($theResult[1],4,1);
						$newSemester	= $semesterArray[$theSemester-1];
						$theSubject		= "CW Academy - Thank You for Participating";
						$theEmail		= "<p>To: $student_last_name, $student_first_name ($student_call_sign)</p>
<p>Thank you for participating in the Advanced CW Academy $thisSemester class. </p>
<p>You have registered to take the Advanced class in the $theYear $newSemester semester. 
Since you now know and understand the CW Academy philosophy and class structure, you 
will be given heightened priority for the class. You will also be assigned to a 
different advisor.</p>
<p>Thank you and 73,<br />
CW Academy";
						$sendTheEmail	= TRUE;
						$actionLogData	= "Sent not promotable Advanced same class registration email ";
					} else {
						if ($doDebug) {
							echo "got to the end of the prepare to send emails and no logicals were set<br />";
						}
					}
					
					if ($sendTheEmail) {
						if ($testMode) {
							$myTo		= "rolandksmith@gmail.com";
							$mailCode	= 5;
							$increment++;
							$theSubject	= "TESTMODE $theSubject";
						} elseif ($bobTest) {
							$myTo		= "kcgator@gmail.com";
							$mailCode	= 5;
							$theSubject	= "bobTest $theSubject";
						} else {
							$myTo		= $student_email;
							$mailCode	= 14;
						}
						
						if ($doDebug) {
							echo "myTo: $myTo<br />
								  mailCode: $mailCode<br />
								  theSubject: $theSubject<br />";
						}
						
						$mailResult		= emailFromCWA_v2(array('theRecipient'=>$myTo,
																	'theSubject'=>$theSubject,
																	'jobname'=>$jobname,
																	'theContent'=>$theEmail,
																	'mailCode'=>$mailCode,
																	'increment'=>$increment,
																	'testMode'=>$testMode,
																	'doDebug'=>$doDebug));

						if ($mailResult === TRUE) {
							if ($doDebug) {
								echo "$actionLogData to $myTo<br /><br />";
							}
							$content .= "$actionLogData to $myTo ($student_call_sign).<br />";
							$emailsSent++;

						} else {
							if ($doDebug) {
								echo "Send mail function failed<br />";
							}
							$content	.= "Sending an email to $myTo ($student_call_sign) failed.<br />";
						}
					}			// end of send email
				}
			} else {
				$content 	.= "<p>No records found in $studentTableName table meeting the criteria.</p>";
			}
		}
	}
	
	if (count($errorArray) > 0) {
		$content	.= "<h4>Errors</h4>";
		foreach ($errorArray as $myValue) {
			$content	.= $myValue;
		}
	}
	$content		.= "<h4>Totals</h4>
						<table>
						<tr><td>$promotableBlank</td><td>Students with an empty promotable status</td></tr>
						<tr><td>$withdrewCount</td><td>Students marked as withdrawn</td></tr>
						<tr><td colspan='2'><hr></td></tr>
						<tr><td>$notPromotableCount</td><td>Students evaluated as not promotable</td></tr>
						<tr><td>$notPromotableBFINoClass</td><td>Not promotable B,F,I student and not registered for another class</td</tr>
						<tr><td>$notPromotableBFISameClass</td><td>Not promotable B,F,I student and registered for same level</td></tr>
						<tr><td>$notPromotableBFINextClass</td><td>Not promotable B,F,I student and registered for higher level</td></tr>
						<tr><td>$notPromotableADVNoClass</td><td>Not promotable Advanced student and not registered for another class</td></tr>
						<tr><td>$notPromotableADVSameClass</td><td>Not promotable Advanced student and registered for another class</td></tr>
						<tr><td colspan='2'><hr></td></tr>
						<tr><td>$promotableCount</td><td>Students evaluated as promotable</td></tr>
						<tr><td>$promotableBFINoClass</td><td>Promotable students B,F,I level not registered for another level</td></tr>
						<tr><td>$promotableBFISameClass</td><td>Promotable students B,F,I level registered for same level</td></tr>
						<tr><td>$promotableBFINextClass</td><td>Promotable students B,F,I level registered for next level</td></tr>
						<tr><td>$promotableADVNoClass</td><td>Promotable Advanced students not registered for another class</td></tr>
						<tr><td>$promotableADVSameClass</td><td>Promotable Advanced students registered for same level</td></tr>
						<tr><td colspan='2'><hr></td></tr>
						<tr><td>$numPSRows</td><td>Total students for the $thisSemester semester</td></tr>
						<tr><td>$emailsSent</td><td>Total Emails Sent</td></tr>
						<tr><td colspan='2'<hr></td></tr>
						</table>";

	$thisTime 		= date('Y-m-d H:i:s');
	$content 		.= "<br /><br /><p>Prepared at $thisTime</p>";
	$endingMicroTime = microtime(TRUE);
	$elapsedTime	= $endingMicroTime - $startingMicroTime;
	$elapsedTime	= number_format($elapsedTime, 4, '.', ',');
	$content		.= "<p>Report pass $strPass took $elapsedTime seconds to run</p>";
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
add_shortcode ('send_congratulations_email_to_students', 'send_congratulations_email_to_students_func');
