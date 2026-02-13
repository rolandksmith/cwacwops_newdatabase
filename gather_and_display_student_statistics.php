function gather_and_display_student_statistics_func() {

/* Gather and Display Student Statistics
 *
 * Program reads the student table for a particular semester and calculates
 * the following statistics:
 *
 *	For each level
 *		the number of students who registered
 *		the number of students who verified
 *		the number of students who refused
 *		the number of students who didn't respond
 *		the number of students who completed the class
 *		the number of students who were promotable
 *		the number of students who were not promotable
 *		the number of students who weren't evaluated
 *		the number of students who withdrew
 *		the number of students who were replaced
 *		the number replacements the advisor declined
 *		the number of replacements the advisor didn't verify
 *
 *  The program then prepares counts of the number of advisors and the 
 *	number of classes taught by level for the semester
 *
 
 	Modified 16Apr23 by Roland to fix action_log
 	Modified 13Jul23 by Roland to use consolidated tables
 	Modified 1Mar24 by Roland to display either the statistics or a csv-compatible table
 	Modified 15Oct24 by Roland for new database
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
	$validTestmode		= $initializationArray['validTestmode'];

	if ($userName == '') {
		return "YOU'RE NOT AUTHORIZED!<br />Goodby";
	}

//	if ($doDebug) {
		ini_set('display_errors','1');
		error_reporting(E_ALL);
		ini_set('max_execution_time',0);	
//	}

/// get the time that the process started
	$startingMicroTime			= microtime(TRUE);

	$strPass					= "1";
	$displayDetail				= FALSE;
	$showDetail					= '';
	$statusArray				= array();
	$notCounted					= 0;
	$inpSemester				= '';
	$theURL						= "$siteURL/cwa-gather-and-display-student-statistics/";
	$jobname					= "Gather and Display Student Statistics";
	
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
			if ($str_key 		== "showDetail") {
				$showDetail		 = $str_value;
				$showDetail		 = filter_var($showDetail,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "inp_semester") {
				$inp_semester	 = $str_value;
				$inp_semester	 = filter_var($inp_semester,FILTER_UNSAFE_RAW);
		    }
			if ($str_key 		== "out_format") {
				$out_format	 = $str_value;
				$out_format	 = filter_var($out_format,FILTER_UNSAFE_RAW);
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


	
	$content = "";	

	if ("1" == $strPass) {
		if ($doDebug) {
			echo "Function starting.<br />";
		}
		
		$optionList		= "";
		$pastSemesters	= $initializationArray['pastSemesters'];
		$semesterArray	= explode("|",$pastSemesters);
		foreach($semesterArray as $theValue) {
			$optionList	.= "<input type='radio' class='formInputButton' name='inp_semester' value='$theValue' required>$theValue<br />";
		}
		
		$content 		.= "<h3>Gather and Display Student Statistics</h3>

							<form method='post' action='$theURL' 
							name='selection_form' ENCTYPE='multipart/form-data'>
							<input type='hidden' name='strpass' value='2'>
							<table style='border-collapse:collapse;'>
							<tr><td style='width:150px;vertical-align:top;'>Semester of Interest</td>
								<td>$optionList</td></tr>
							<tr><td style='vertical-align:top;'>Output Format</td>
								<td><input type='radio' class='formInputButton' name='out_format' value='report' required>Report<br />
									<input type='radio' class='formInputButton' name='out_format' value='csv' required>csv Formated Table<br />
									<input type='radio' class='formInputButton' name='out_format' value='both' required>Both Formats</td></tr>
							$testModeOption
							<tr><td colspan='2'><input class='formInputButton' type='submit' value='Submit' /></td></tr></table>
							</form>";
	

///// Pass 2 -- do the work


	} elseif ("2" == $strPass) {
		if ($doDebug) {
			echo "<br />at $strPass with inp_semester: $inp_semester and out_format: $out_format<br />";
		}

// data counts Array
$dataCounts['Fundamental'] = array(
'registered'=>0,
'verified'=>0,
'refused'=>0,
'noResponse'=>0,
'completed'=>0,
'decline'=>0,
'noVerify'=>0,
'promotable'=>0,
'notPromotable'=>0,
'notEvaluated'=>0,
'replaced'=>0,
'withdrew'=>0,
'advisors'=>0,
'classes'=>0);
$dataCounts['Beginner'] = array(
'registered'=>0,
'verified'=>0,
'refused'=>0,
'noResponse'=>0,
'completed'=>0,
'decline'=>0,
'noVerify'=>0,
'promotable'=>0,
'notPromotable'=>0,
'notEvaluated'=>0,
'replaced'=>0,
'withdrew'=>0,
'advisors'=>0,
'classes'=>0);
$dataCounts['Intermediate'] = array(
'registered'=>0,
'verified'=>0,
'refused'=>0,
'noResponse'=>0,
'completed'=>0,
'decline'=>0,
'noVerify'=>0,
'promotable'=>0,
'notPromotable'=>0,
'notEvaluated'=>0,
'replaced'=>0,
'withdrew'=>0,
'advisors'=>0,
'classes'=>0);
$dataCounts['Advanced'] = array(
'registered'=>0,
'verified'=>0,
'refused'=>0,
'noResponse'=>0,
'completed'=>0,
'decline'=>0,
'noVerify'=>0,
'promotable'=>0,
'notPromotable'=>0,
'notEvaluated'=>0,
'replaced'=>0,
'withdrew'=>0,
'advisors'=>0,
'classes'=>0);
$dataCounts['total'] = array(
'registered'=>0,
'verified'=>0,
'refused'=>0,
'noResponse'=>0,
'completed'=>0,
'decline'=>0,
'noVerify'=>0,
'promotable'=>0,
'notPromotable'=>0,
'notEvaluated'=>0,
'replaced'=>0,
'withdrew'=>0,
'advisors'=>0,
'classes'=>0);
$advisorArray = array();

		if ($showDetail == 'showDetail') {
			$displayDetail		= TRUE;
			$content 	.= "<h4>Data From the $inp_semester Semester</h4>";
		}
	
		$sql					= "select * from wpw1_cwa_student 
									where student_semester='$inp_semester' 
									order by student_call_sign";
		$wpw1_cwa_student			= $wpdb->get_results($sql);
			if ($wpw1_cwa_student === FALSE) {
				handleWPDBError($jobname,$doDebug);
			} else {
				$numSRows			= $wpdb->num_rows;
				if ($doDebug) {
					echo "ran $sql<br />and found $numSRows rows<br />";
				}
			if ($numSRows > 0) {
				foreach ($wpw1_cwa_student as $studentRow) {
					$student_ID								= $studentRow->student_id;
					$student_call_sign						= $studentRow->student_call_sign;
					$student_time_zone  					= $studentRow->student_time_zone;
					$student_timezone_offset				= $studentRow->student_timezone_offset;
					$student_youth  						= $studentRow->student_youth;
					$student_age  							= $studentRow->student_age;
					$student_student_parent 				= $studentRow->student_parent;
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
					$student_student_survey_completion_date	= $studentRow->student_survey_completion_date;
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

					if ($displayDetail) {
						$content .= "<br />Processing student $student_call_sign:<br />";
					}
					$dataCounts[$student_level]['registered']++;
					$dataCounts['total']['registered']++;
					if ($student_response == 'Y') {
						$dataCounts[$student_level]['verified']++;
						$dataCounts['total']['verified']++;
						if ($displayDetail) {
							$content	.=	"&nbsp;&nbsp;&nbsp;Verified (response=Y)<br />";
						}
					
						if ($student_status == 'Y') {
							$dataCounts[$student_level]['completed']++;
							$dataCounts['total']['completed']++;
							if ($displayDetail) {
								$content	.=	"&nbsp;&nbsp;&nbsp;Completed (status=Y)<br />";
							}
						
							if ($student_promotable == 'P') {
								$dataCounts[$student_level]['promotable']++;
								$dataCounts['total']['promotable']++;
								if ($displayDetail) {
									$content	.=	"&nbsp;&nbsp;&nbsp;Promotable (promotable=P)<br />";
								}
							} elseif ($student_promotable == 'Y') {
								$dataCounts[$student_level]['promotable']++;
								$dataCounts['total']['promotable']++;
								if ($displayDetail) {
									$content	.=	"&nbsp;&nbsp;&nbsp;Promotable (promotable=Y)<br />";
								}
							} elseif ($student_promotable == 'N') {
								$dataCounts[$student_level]['notPromotable']++;
								$dataCounts['total']['notPromotable']++;
								if ($displayDetail) {
									$content	.=	"&nbsp;&nbsp;&nbsp;Not Promotable (promotable=N)<br />";
								}
							} elseif ($student_promotable == 'W') {
								$dataCounts[$student_level]['withdrew']++;
								$dataCounts['total']['withdrew']++;
								if ($displayDetail) {
									$content	.=	"&nbsp;&nbsp;&nbsp;Withdrew (promotable=W)<br />";
								}
							} elseif ($student_promotable == '') {
								$dataCounts[$student_level]['notEvaluated']++;
								$dataCounts['total']['notEvaluated']++;
								if ($displayDetail) {
									$content	.=	"&nbsp;&nbsp;&nbsp;Not Evaluated (promotable='')<br />";
								}
							} else {
								echo "Check $student_call_sign. Not counted for promotable.<br />";
								$notCounted++;
							}
						} elseif ($student_status == 'R') {
							$dataCounts[$student_level]['replaced']++;
							$dataCounts['total']['replaced']++;
							if ($displayDetail) {
								$content	.=	"&nbsp;&nbsp;&nbsp;Replaced (status=R)<br />";
							}
						} elseif ($student_status == 'C') {
							$dataCounts[$student_level]['replaced']++;
							$dataCounts['total']['replaced']++;
							if ($displayDetail) {
								$content	.=	"&nbsp;&nbsp;&nbsp;Replaced (status=C)<br />";
							}
						} elseif ($student_status == 'N') {
							$dataCounts[$student_level]['decline']++;
							$dataCounts['total']['decline']++;
							if ($displayDetail) {
								$content	.=	"&nbsp;&nbsp;&nbsp;Replacement Declined (status=N)<br />";
							}
						} elseif ($student_status == 'S') {
							$dataCounts[$student_level]['noVerify']++;
							$dataCounts['total']['noVerify']++;
							if ($displayDetail) {
								$content	.=	"&nbsp;&nbsp;&nbsp;Replacement Not Verified (status=S)<br />";
							}
						}

					} elseif ($student_response == 'R') {
						$dataCounts[$student_level]['refused']++;
						$dataCounts['total']['refused']++;
						if ($displayDetail) {
							$content	.=	"&nbsp;&nbsp;&nbsp;Refused (response=R)<br />";
						}
					} elseif ($student_response == '') {
						$dataCounts[$student_level]['noResponse']++;
						$dataCounts['total']['noResponse']++;
						if ($displayDetail) {
							$content	.=	"&nbsp;&nbsp;&nbsp;No Response (response='')<br />";
						}
					}
		
				}		
			} else {
				$content	.= "<p>Past_student pod had no records</p>";
			}
		}
	
// all student information gathered. Now get the advisor information from the classtable
		$sql						= "select * from wpw1_cwa_advisorclass 
										where advisorclass_semester='$inp_semester' 
										order by advisorclass_call_sign";
		$wpw1_cwa_consolidated_advisorclass		= $wpdb->get_results($sql);
		if ($wpw1_cwa_consolidated_advisorclass === FALSE) {
			handleWPDBError($jobname,$doDebug);
		} else {	
			$numPARows				= $wpdb->num_rows;
			if ($doDebug) {
				echo "ran $sql<br />and found $numPARows rows in wpw1_cwa_consolidated_advisorclass<br />";
			}
			if ($numPARows > 0) {
				foreach ($wpw1_cwa_consolidated_advisorclass as $advisorClassRow) {
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

					$dataCounts[$advisorClass_level]['classes']++;
					$dataCounts['total']['classes']++;
					if (!in_array($advisorClass_call_sign,$advisorArray)) {
						$dataCounts[$advisorClass_level]['advisors']++;
						$dataCounts['total']['advisors']++;
						$advisorArray[] 	= $advisorClass_call_sign;
					}
			
				}
			} else {
				$content	.= "<p>advisorClass table had no records</p>";
			}
		}
		if ($doDebug) {
			echo "dataCounts array:<br /><pre>";
			print_r($dataCounts);
			echo "</pre><br />";
		}

		if ($out_format == 'report' || $out_format == 'both') {

			$content	.= "<h4>Data From the $inp_semester Semester</h4>
							<p><table style='width:900px;'>
							<tr><th>Category</th>
							<th style='width:140px;text-align:center;'>Beginner</th>
							<th style='width:140px;text-align:center;'>Fundamental</th>
							<th style='width:140px;text-align:center;'>Intermediate</th>
							<th style='width:140px;text-align:center;'>Advanced</th>
							<th style='width:140px;text-align:center;'>Total</th></tr>
							<tr><td>A: Registered Students (<i>A/Total%</i>)</td><td style='text-align:center;'>";
							$pc1		= number_format(($dataCounts['Beginner']['verified']/$dataCounts['total']['registered']*100),1);
							$pc2		= number_format(($dataCounts['Fundamental']['verified']/$dataCounts['total']['registered']*100),1);
							$pc3		= number_format(($dataCounts['Intermediate']['verified']/$dataCounts['total']['registered']*100),1);
							$pc4		= number_format(($dataCounts['Advanced']['verified']/$dataCounts['total']['registered']*100),1);
							$content	.= $dataCounts['Beginner']['registered'] . " ($pc1%)</td><td style='text-align:center;'>";
							$content	.= $dataCounts['Fundamental']['registered'] . " ($pc2%)</td><td style='text-align:center;'>";
							$content	.= $dataCounts['Intermediate']['registered'] . " ($pc3%)</td><td style='text-align:center;'>";
							$content	.= $dataCounts['Advanced']['registered'] . " ($pc4%)</td><td style='text-align:center;'>";
							$content	.= $dataCounts['total']['registered'] . " (100.0%)</td></tr>
							<tr><td>B: Verified Students (<i>B/A%</i>)</td><td style='text-align:center;'>";
							$pc1		= number_format(($dataCounts['Beginner']['verified']/$dataCounts['Beginner']['registered']*100),1);
							$pc2		= number_format(($dataCounts['Fundamental']['verified']/$dataCounts['Fundamental']['registered']*100),1);
							$pc3		= number_format(($dataCounts['Intermediate']['verified']/$dataCounts['Intermediate']['registered']*100),1);
							$pc4		= number_format(($dataCounts['Advanced']['verified']/$dataCounts['Advanced']['registered']*100),1);
							$pc5		= number_format(($dataCounts['total']['verified']/$dataCounts['total']['registered']*100),1);
							$content	.= $dataCounts['Beginner']['verified'] . " ($pc1%)</td><td style='text-align:center;'>";
							$content	.= $dataCounts['Fundamental']['verified'] . " ($pc2%)</td><td style='text-align:center;'>";
							$content	.= $dataCounts['Intermediate']['verified'] . " ($pc3%)</td><td style='text-align:center;'>";
							$content	.= $dataCounts['Advanced']['verified'] . " ($pc4%)</td><td style='text-align:center;'>";
							$content	.= $dataCounts['total']['verified'] . " ($pc5%)</td></tr>
							<tr><td>C: Students Asked to be Removed (<i>C/A%</i>)</td><td style='text-align:center;'>";
							$pc1		= number_format(($dataCounts['Beginner']['refused']/$dataCounts['Beginner']['registered']*100),1);
							$pc2		= number_format(($dataCounts['Fundamental']['refused']/$dataCounts['Fundamental']['registered']*100),1);
							$pc3		= number_format(($dataCounts['Intermediate']['refused']/$dataCounts['Intermediate']['registered']*100),1);
							$pc4		= number_format(($dataCounts['Advanced']['refused']/$dataCounts['Advanced']['registered']*100),1);
							$pc5		= number_format(($dataCounts['total']['refused']/$dataCounts['total']['registered']*100),1);
							$content	.= $dataCounts['Beginner']['refused'] . " ($pc1%)</td><td style='text-align:center;'>";
							$content	.= $dataCounts['Fundamental']['refused'] . " ($pc2%)</td><td style='text-align:center;'>";
							$content	.= $dataCounts['Intermediate']['refused'] . " ($pc3%)</td><td style='text-align:center;'>";
							$content	.= $dataCounts['Advanced']['refused'] . " ($pc4%)</td><td style='text-align:center;'>";
							$content	.= $dataCounts['total']['refused'] . " ($pc5%)</td></tr>
							<tr><td>D: Non-Responding Students (<i>D/A%</i>)</td><td style='text-align:center;'>";
							$pc1		= number_format(($dataCounts['Beginner']['noResponse']/$dataCounts['Beginner']['registered']*100),1);
							$pc2		= number_format(($dataCounts['Fundamental']['noResponse']/$dataCounts['Fundamental']['registered']*100),1);
							$pc3		= number_format(($dataCounts['Intermediate']['noResponse']/$dataCounts['Intermediate']['registered']*100),1);
							$pc4		= number_format(($dataCounts['Advanced']['noResponse']/$dataCounts['Advanced']['registered']*100),1);
							$pc5		= number_format(($dataCounts['total']['noResponse']/$dataCounts['total']['registered']*100),1);
							$content	.= $dataCounts['Beginner']['noResponse'] . " ($pc1%)</td><td style='text-align:center;'>";
							$content	.= $dataCounts['Fundamental']['noResponse'] . " ($pc2%)</td><td style='text-align:center;'>";
							$content	.= $dataCounts['Intermediate']['noResponse'] . " ($pc3%)</td><td style='text-align:center;'>";
							$content	.= $dataCounts['Advanced']['noResponse'] . " ($pc4%)</td><td style='text-align:center;'>";
							$content	.= $dataCounts['total']['noResponse'] . " ($pc5%)</td></tr>
							<tr><td colspan='6'><hr></td></tr>
							<tr><td>E: Students Completing the Class (<i>E/B%</i>)</td><td style='text-align:center;'>";
							$pc1		= number_format(($dataCounts['Beginner']['completed']/$dataCounts['Beginner']['verified']*100),1);
							$pc2		= number_format(($dataCounts['Fundamental']['completed']/$dataCounts['Fundamental']['verified']*100),1);
							$pc3		= number_format(($dataCounts['Intermediate']['completed']/$dataCounts['Intermediate']['verified']*100),1);
							$pc4		= number_format(($dataCounts['Advanced']['completed']/$dataCounts['Advanced']['verified']*100),1);
							$pc5		= number_format(($dataCounts['total']['completed']/$dataCounts['total']['verified']*100),1);
							$content	.= $dataCounts['Beginner']['completed'] . " ($pc1%)</td><td style='text-align:center;'>";
							$content	.= $dataCounts['Fundamental']['completed'] . " ($pc2%)</td><td style='text-align:center;'>";
							$content	.= $dataCounts['Intermediate']['completed'] . " ($pc3%)</td><td style='text-align:center;'>";
							$content	.= $dataCounts['Advanced']['completed'] . " ($pc4%)</td><td style='text-align:center;'>";
							$content	.= $dataCounts['total']['completed'] . " ($pc5%)</td></tr>
							<tr><td>F: Students Evaluated as Promotable (<i>F/E%</i>)</td><td style='text-align:center;'>";
							$pc1		= number_format(($dataCounts['Beginner']['promotable']/$dataCounts['Beginner']['completed']*100),1);
							$pc2		= number_format(($dataCounts['Fundamental']['promotable']/$dataCounts['Fundamental']['completed']*100),1);
							$pc3		= number_format(($dataCounts['Intermediate']['promotable']/$dataCounts['Intermediate']['completed']*100),1);
							$pc4		= number_format(($dataCounts['Advanced']['promotable']/$dataCounts['Advanced']['completed']*100),1);
							$pc5		= number_format(($dataCounts['total']['promotable']/$dataCounts['total']['completed']*100),1);
							$content	.= $dataCounts['Beginner']['promotable'] . " ($pc1%)</td><td style='text-align:center;'>";
							$content	.= $dataCounts['Fundamental']['promotable'] . " ($pc2%)</td><td style='text-align:center;'>";
							$content	.= $dataCounts['Intermediate']['promotable'] . " ($pc3%)</td><td style='text-align:center;'>";
							$content	.= $dataCounts['Advanced']['promotable'] . " ($pc4%)</td><td style='text-align:center;'>";
							$content	.= $dataCounts['total']['promotable'] . " ($pc5%)</td></tr>
							<tr><td>G: Students Evaluated as Not Promotable (<i>G/E%</i>)</td><td style='text-align:center;'>";
							$pc1		= number_format(($dataCounts['Beginner']['notPromotable']/$dataCounts['Beginner']['completed']*100),1);
							$pc2		= number_format(($dataCounts['Fundamental']['notPromotable']/$dataCounts['Fundamental']['completed']*100),1);
							$pc3		= number_format(($dataCounts['Intermediate']['notPromotable']/$dataCounts['Intermediate']['completed']*100),1);
							$pc4		= number_format(($dataCounts['Advanced']['notPromotable']/$dataCounts['Advanced']['completed']*100),1);
							$pc5		= number_format(($dataCounts['total']['notPromotable']/$dataCounts['total']['completed']*100),1);
							$content	.= $dataCounts['Beginner']['notPromotable'] . " ($pc1%)</td><td style='text-align:center;'>";
							$content	.= $dataCounts['Fundamental']['notPromotable'] . " ($pc2%)</td><td style='text-align:center;'>";
							$content	.= $dataCounts['Intermediate']['notPromotable'] . " ($pc3%)</td><td style='text-align:center;'>";
							$content	.= $dataCounts['Advanced']['notPromotable'] . " ($pc4%)</td><td style='text-align:center;'>";
							$content	.= $dataCounts['total']['notPromotable'] . " ($pc5%)</td></tr>
							<tr><td>H: Students Not Evaluated (<i>H/E%</i>)</td><td style='text-align:center;'>";
							$pc1		= number_format(($dataCounts['Beginner']['notEvaluated']/$dataCounts['Beginner']['completed']*100),1);
							$pc2		= number_format(($dataCounts['Fundamental']['notEvaluated']/$dataCounts['Fundamental']['completed']*100),1);
							$pc3		= number_format(($dataCounts['Intermediate']['notEvaluated']/$dataCounts['Intermediate']['completed']*100),1);
							$pc4		= number_format(($dataCounts['Advanced']['notEvaluated']/$dataCounts['Advanced']['completed']*100),1);
							$pc5		= number_format(($dataCounts['total']['notEvaluated']/$dataCounts['total']['completed']*100),1);
							$content	.= $dataCounts['Beginner']['notEvaluated'] . " ($pc1%)</td><td style='text-align:center;'>";
							$content	.= $dataCounts['Fundamental']['notEvaluated'] . " ($pc2%)</td><td style='text-align:center;'>";
							$content	.= $dataCounts['Intermediate']['notEvaluated'] . " ($pc3%)</td><td style='text-align:center;'>";
							$content	.= $dataCounts['Advanced']['notEvaluated'] . " ($pc4%)</td><td style='text-align:center;'>";
							$content	.= $dataCounts['total']['notEvaluated'] . " ($pc5%)</td></tr>
							<tr><td>I: Students Who Withdrew (<i>I/E%</i>)</td><td style='text-align:center;'>";
							$pc1		= number_format(($dataCounts['Beginner']['withdrew']/$dataCounts['Beginner']['completed']*100),1);
							$pc2		= number_format(($dataCounts['Fundamental']['withdrew']/$dataCounts['Fundamental']['completed']*100),1);
							$pc3		= number_format(($dataCounts['Intermediate']['withdrew']/$dataCounts['Intermediate']['completed']*100),1);
							$pc4		= number_format(($dataCounts['Advanced']['withdrew']/$dataCounts['Advanced']['completed']*100),1);
							$pc5		= number_format(($dataCounts['total']['withdrew']/$dataCounts['total']['completed']*100),1);
							$content	.= $dataCounts['Beginner']['withdrew'] . " ($pc1%)</td><td style='text-align:center;'>";
							$content	.= $dataCounts['Fundamental']['withdrew'] . " ($pc2%)</td><td style='text-align:center;'>";
							$content	.= $dataCounts['Intermediate']['withdrew'] . " ($pc3%)</td><td style='text-align:center;'>";
							$content	.= $dataCounts['Advanced']['withdrew'] . " ($pc4%)</td><td style='text-align:center;'>";
							$content	.= $dataCounts['total']['withdrew'] . " ($pc5%)</td></tr>
							<tr><td colspan='6'><hr></td></tr>
							<tr><td>J: Students Replaced (<i>J/B%</i>)</td><td style='text-align:center;'>";
							$pc1		= number_format(($dataCounts['Beginner']['replaced']/$dataCounts['Beginner']['verified']*100),1);
							$pc2		= number_format(($dataCounts['Fundamental']['replaced']/$dataCounts['Fundamental']['verified']*100),1);
							$pc3		= number_format(($dataCounts['Intermediate']['replaced']/$dataCounts['Intermediate']['verified']*100),1);
							$pc4		= number_format(($dataCounts['Advanced']['replaced']/$dataCounts['Advanced']['verified']*100),1);
							$pc5		= number_format(($dataCounts['total']['replaced']/$dataCounts['total']['verified']*100),1);
							$content	.= $dataCounts['Beginner']['replaced'] . " ($pc1%)</td><td style='text-align:center;'>";
							$content	.= $dataCounts['Fundamental']['replaced'] . " ($pc2%)</td><td style='text-align:center;'>";
							$content	.= $dataCounts['Intermediate']['replaced'] . " ($pc3%)</td><td style='text-align:center;'>";
							$content	.= $dataCounts['Advanced']['replaced'] . " ($pc4%)</td><td style='text-align:center;'>";
							$content	.= $dataCounts['total']['replaced'] . " ($pc5%)</td></tr>
							<tr><td>K: Advisor Declined Replacement Student (<i>K/B%</i>)</td><td style='text-align:center;'>";
							$pc1		= number_format(($dataCounts['Beginner']['decline']/$dataCounts['Beginner']['verified']*100),1);
							$pc2		= number_format(($dataCounts['Fundamental']['decline']/$dataCounts['Fundamental']['verified']*100),1);
							$pc3		= number_format(($dataCounts['Intermediate']['decline']/$dataCounts['Intermediate']['verified']*100),1);
							$pc4		= number_format(($dataCounts['Advanced']['decline']/$dataCounts['Advanced']['verified']*100),1);
							$pc5		= number_format(($dataCounts['total']['decline']/$dataCounts['total']['verified']*100),1);
							$content	.= $dataCounts['Beginner']['decline'] . " ($pc1%)</td><td style='text-align:center;'>";
							$content	.= $dataCounts['Fundamental']['decline'] . " ($pc1%)</td><td style='text-align:center;'>";
							$content	.= $dataCounts['Intermediate']['decline'] . " ($pc1%)</td><td style='text-align:center;'>";
							$content	.= $dataCounts['Advanced']['decline'] . " ($pc1%)</td><td style='text-align:center;'>";
							$content	.= $dataCounts['total']['decline'] . " ($pc1%)</td></tr>
							<tr><td>L: Advisor Did Not Verify Replacement Student (<i>L/B%</i>)</td><td style='text-align:center;'>";
							$pc1		= number_format(($dataCounts['Beginner']['noVerify']/$dataCounts['Beginner']['verified']*100),1);
							$pc2		= number_format(($dataCounts['Fundamental']['noVerify']/$dataCounts['Fundamental']['verified']*100),1);
							$pc3		= number_format(($dataCounts['Intermediate']['noVerify']/$dataCounts['Intermediate']['verified']*100),1);
							$pc4		= number_format(($dataCounts['Advanced']['noVerify']/$dataCounts['Advanced']['verified']*100),1);
							$pc5		= number_format(($dataCounts['total']['noVerify']/$dataCounts['total']['verified']*100),1);
							$content	.= $dataCounts['Beginner']['noVerify'] . " ($pc1%)</td><td style='text-align:center;'>";
							$content	.= $dataCounts['Fundamental']['noVerify'] . " ($pc2%)</td><td style='text-align:center;'>";
							$content	.= $dataCounts['Intermediate']['noVerify'] . " ($pc3%)</td><td style='text-align:center;'>";
							$content	.= $dataCounts['Advanced']['noVerify'] . " ($pc4%)</td><td style='text-align:center;'>";
							$content	.= $dataCounts['total']['noVerify'] . " ($pc5%)</td></tr>
							<tr><td colspan='6'><hr></td></tr>
							<tr><td>Advisors</td><td style='text-align:center;'>";
							$content	.= $dataCounts['Beginner']['advisors'] . "</td><td style='text-align:center;'>";
							$content	.= $dataCounts['Fundamental']['advisors'] . "</td><td style='text-align:center;'>";
							$content	.= $dataCounts['Intermediate']['advisors'] . "</td><td style='text-align:center;'>";
							$content	.= $dataCounts['Advanced']['advisors'] . "</td><td style='text-align:center;'>";
							$content	.= $dataCounts['total']['advisors'] . "</td></tr>
							<tr><td>Classes Held</td><td style='text-align:center;'>";
							$content	.= $dataCounts['Beginner']['classes'] . "</td><td style='text-align:center;'>";
							$content	.= $dataCounts['Fundamental']['classes'] . "</td><td style='text-align:center;'>";
							$content	.= $dataCounts['Intermediate']['classes'] . "</td><td style='text-align:center;'>";
							$content	.= $dataCounts['Advanced']['classes'] . "</td><td style='text-align:center;'>";
							$content	.= $dataCounts['total']['classes'] . "</td></tr>
							<tr><td colspan='6'><hr></td></tr>
							</table>
							<p>Percentages calculated by the formula specified in the category</p>";
	
			if ($notCounted > 0) {
				$content	.= "<p>$notCounted: Students for some reason were not counted.</p>";
			}
		}
		if($out_format == 'csv' || $out_format == 'both') {
			$levelConvArray				= array('Beginner'=>'1',
												'Fundamental'=>'2',
												'Intermediate'=>'3',
												'Advanced'=>'4',
												'total'=>'5');
			$categoryConvArray			= array('registered'=>'a',
												'verified'=>'b',
												'refused'=>'c',
												'noResponse'=>'d',
												'completed'=>'e',
												'promotable'=>'f',
												'notPromotable'=>'g',
												'notEvaluated'=>'h',
												'withdrew'=>'i',
												'replaced'=>'j',
												'decline'=>'k',
												'noVerify'=>'l',
												'advisors'=>'m',
												'classes'=>'n');
			// transform data counts array
			$csvCountsArray	= array();
			foreach($dataCounts as $thisLevel=>$thisData) {
				foreach($thisData as $thisCategory => $thisCount) {
					$convLevel			= $levelConvArray[$thisLevel];
					$convCategory		= $categoryConvArray[$thisCategory];
				
					$csvCountsArray[]	= "$convCategory|$convLevel|$thisCount";
				}

			}
			sort($csvCountsArray);
			if ($doDebug) {
				echo "csvCountsArray: <br /><pre>";
				print_r($csvCountsArray);
				echo "</pre><br />";
			}

			$categoryNameArray = array('a'=>'Registered Students',
										'b'=>'Verified Students',
										'c'=>'Students Asked to be Removed',
										'd'=>'Non-Responding Students',
										'e'=>'Students Completing the Class',
										'f'=>'Students Evaluated as Promotable',
										'g'=>'Students Evaluated as Not Promotable',
										'h'=>'Students Not Evaluated',
										'i'=>'Students Who Withdrew',
										'j'=>'Students Replaced',
										'k'=>'Advisor Declined Replacement Student',
										'l'=>'Advisor Did Not Verify Replacement Student',
										'm'=>'Advisors',
										'n'=>'Classes Held');
			$prevCategory		= '';
			$dispCategory		= '';
			$firstTime			= TRUE;
			$category			= '';
			$column1			= '';
			$column2			= '';
			$column3			= '';
			$column4			= '';
			$column5			= '';
		
			$content	.= "<h3>$jobname for the $inp_semester Semester</h3><h4>csv-formated Table</h4><pre>
Category\tBeginner\tFundamental\tIntermediate\tAdvanced\tTotal\n"; 
			foreach($csvCountsArray as $thisData) {
				$myArray 		= explode("|",$thisData);
				$theCategory	= $myArray[0];
				$theLevel		= $myArray[1];
				$theCount		= $myArray[2];
				if ($theCategory != $prevCategory) {
					if ($firstTime) {
						$firstTime			= FALSE;
						$dispCategory		= $categoryNameArray[$theCategory];
						$prevCategory 		= $theCategory;
					} else {
						$content			.= "$dispCategory\t$column1\t$column2\t$column3\t$column4\t$column5\n";
						$dispCategory		= $categoryNameArray[$theCategory];
						$column1			= '';
						$column2			= '';
						$column3			= '';
						$column4			= '';
						$column5			= '';
						$prevCategory		= $theCategory;
					}
				}
				${'column' . $theLevel} = $theCount;
			}				
			$content	.= "$dispCategory\t$column1\t$column2\t$column3\t$column4\t$column5\n</pre>";
		}
	
/*
echo "$notCounted: Not Counted<br />";
echo "statusArray:<br /><pre>";
print_r($statusArray);
echo "</pre><br />";
*/
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
	$result			= write_joblog_func("$jobname|$nowDate|$nowTime|$userName|Time|$thisStr|$strPass: $elapsedTime");
	if ($result == 'FAIL') {
		$content	.= "<p>writing to joblog.txt failed</p>";
	}
	return $content;
}
add_shortcode ('gather_and_display_student_statistics', 'gather_and_display_student_statistics_func');

