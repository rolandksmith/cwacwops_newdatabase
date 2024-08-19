function send_end_of_semester_assessment_email_to_advisors_func() {

/* Send End of Semester Assessment Email to Advisors
 *
 * Run about a week before the end of the semester
 * Sends an email to each advisor who has one or more classes explaining 
 	the ability to order a Morse code assessment
 	
 * created 10Jun2022 by Roland from send_evaluation_email_to_advisors
 	Modified 13Jul23 by Roland to use consolidated tables
 	Modified 18Jun24 by Roland to just send the email

*/

	global $wpdb;

	$doDebug						= FALSE;
	$testMode						= FALSE;
	$initializationArray 			= data_initialization_func();
	if ($doDebug) {
		echo "Initialization Array:<br /><pre>";
		print_r($initializationArray);
		echo "</pre><br />";
	}
	$validUser = $initializationArray['validUser'];
	$siteURL			= $initializationArray['siteurl'];

	if ($validUser == "N") {
		return "YOU'RE NOT AUTHORIZED!<br />Goodby";
	}

// set some initialization values
//	if ($doDebug) {
		ini_set('display_errors','1');
		error_reporting(E_ALL);	
//	}
	
/// get the time that the process started
	$startingMicroTime			= microtime(TRUE);

	$strPass			= "1";
	$advisorArray		= array();
	$myCount			= 0;
	$additionaltext		= "";
	$increment			= 0;
	$userName			= $initializationArray['userName'];
	$validTestmode		= $initializationArray['validTestmode'];
	$siteURL			= $initializationArray['siteurl'];
	$theURL				= "$siteURL/cwa-send-end-of-semester-assessment-email-to-advisors/";
	$jobname			= 'Send End of Semester Eval Email to Advisors';


// get the input information
	if (isset($_REQUEST)) {
		foreach($_REQUEST as $str_key => $str_value) {
			if ($doDebug) {
				echo "Key: $str_key | Value: $str_value <br />\n";
			}
			if ($str_key 		== "strpass") {
				$strPass		 = $str_value;
				$strPass		= filter_var($strPass,FILTER_UNSAFE_RAW);
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
			if ($str_key 		== "inp_advisors") {
				$inp_advisors	 = $str_value;
				$inp_advisors	 = strtoupper(filter_var($inp_advisors,FILTER_UNSAFE_RAW));
			}
			if ($str_key 		== "addl_comments") {
				$addl_comments	 = $str_value;
				$addl_comments	 = filter_var($addl_comments,FILTER_UNSAFE_RAW);
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

table{font:'Times New Roman', sans-serif;background-image:none;}

th {color:#ffff;background-color:#000;padding:5px;font-size:small;}

td {padding:5px;font-size:small;}
</style>";	

	if ($testMode) {
		$content	.= "<p><strong>Operating in Test Mode.</strong></p>";
		if ($doDebug) {
			echo "<p><strong>Operating in Test Mode.</strong></p>";
		}
		$advisorTableName			= "wpw1_cwa_consolidated_advisor2";
		$advisorClassTableName		= "wpw1_cwa_consolidated_advisorclass2";
		$inp_mode						= 'tm';
	} else {
		$advisorTableName			= "wpw1_cwa_consolidated_advisor";
		$advisorClassTableName		= "wpw1_cwa_consolidated_advisorclass";
		$inp_mode						= 'pd';
	}


	if ("1" == $strPass) {
		if ($doDebug) {
			echo "Function starting.<br />";
		}
		$content 		.= "<h3>Send Advisors an Email for Student to Do an End of Semester Assessment</h3>
<p>This function reads the advisorClass table for the current (or just completed) 
semester and sends an email to the advisors with instructions on how to order an end-of-semester 
Morse code evaluation for any or all of their students.
<p><form method='post' action='$theURL' 
name='selection_form' ENCTYPE='multipart/form-data'>
<input type='hidden' name='strpass' value='2'>
<table>
<tr><td style='vertical-align:top;'>If selected advisors are to receive this email enter their call signs:</td>
	<td><textarea class='formInputText' name='inp_advisors' rows='5' cols='50'></textarea></td></tr>
<tr><td style='vertical-align:top;'>Enter any additional comments (if any) to add to the email to the advisors:</td>
	<td><textarea class='formInputText' name='addl_comments' rows='5' cols='50'></textarea></td></tr>
$testModeOption
<tr><td colspan='2'><input class='formInputButton' type='submit' value='Submit' /></tr></td></table>
</form></p>";

///// Pass 2 -- do the work


	} elseif ("2" == $strPass) {
	
		if ($doDebug) {
			echo "Arrived at pass 2<br />";
		}

		$content .= "<h3>Send Advisors an Email on how to order an End of Semester Assessment</h3>";
		
		$selectedAdvisors			= FALSE;
		//// see if a list of advisors was entered
		if ($inp_advisors != '') {
			$inp_advisors			= str_replace(" ","",$inp_advisors);
			// build an array of selected advisors
			$selectedAdvisorsArray	= explode(",",$inp_advisors);
			$selectedAdvisors		= TRUE;
		}

		$currentSemester	= $initializationArray['currentSemester'];
		$nextSemester		= $initializationArray['nextSemester'];
		$prevSemester		= $initializationArray['prevSemester'];
		if ($currentSemester == "Not in Session") {
			$theSemester	= $prevSemester;
		} else {
			$theSemester	= $currentSemester;			
		}

		if ($selectedAdvisors) {
			foreach($selectedAdvisorsArray as $thisAdvisor) {	
				$sql				= "select a.advisor_call_sign, 
											  a.evaluation_complete, 
										      a.number_students, 
											  b.last_name, 
											  b.first_name, 
											  b.email, 
											  b.phone 
										from $advisorClassTableName as a, 
											 $advisorTableName as b 
										where a.advisor_call_sign = '$thisAdvisor'  
											  and a.semester='$theSemester' 
											  and a.advisor_call_sign=b.call_sign 
											  and b.semester='$theSemester' 
										order by a.advisor_call_sign, a.sequence";
				$cwa_advisorclass			= $wpdb->get_results($sql);
				if ($doDebug) {
					echo "Reading $advisorClassTableName table<br />";
					echo "wpdb->last_query: " . $wpdb->last_query . "<br />";
					if ($wpdb->last_error != '') {
						echo "<b>wpdb->last_error: " . $wpdb->last_error . "</b><br />";
					}
				}
				if ($cwa_advisorclass !== FALSE) {
					$numACRows									= $wpdb->num_rows;
					if ($doDebug) {
						echo "found $numACRows rows in $advisorClassTableName table<br />";
					}
					if ($numACRows > 0) {
						foreach ($cwa_advisorclass as $advisorClassRow) {
							$advisorClass_advisor_callsign 		= $advisorClassRow->advisor_call_sign;
							$class_number_students						= $advisorClassRow->number_students;
							$class_evaluation_complete 					= $advisorClassRow->evaluation_complete;
							$advisor_first_name 					= $advisorClassRow->first_name;
							$advisor_last_name 					= $advisorClassRow->last_name;
							$advisor_email 						= strtolower($advisorClassRow->email);
							$advisor_phone 						= $advisorClassRow->phone;
				
							if ($doDebug) {
								echo "<br />Processing $advisorClassTableName pod record for advisor $advisorClass_advisor_callsign<br />";
							}
				
							$advisorArrayValue		= "$advisorClass_advisor_callsign|$advisor_email|$advisor_first_name|$advisor_last_name|$advisor_phone";
							if (!in_array($advisorArrayValue,$advisorArray)) {
								$advisorArray[]		= $advisorArrayValue;
								if ($doDebug) {
									echo "&nbsp;&nbsp;&nbsp;Adding $advisorClass_advisor_callsign ,$advisor_email,$advisor_first_name,$advisor_last_name,$advisor_phone to advisorArray<br /><br />";
								}
							}
						}
					} else {
						$content	.= "$advisorClassTableName has no records<br />";
					}
				} else {
					echo "Something wrong with this $sql<br />";
				}
			}
		} else {
			$sql				= "select a.advisor_call_sign, 
										  a.evaluation_complete, 
										  a.number_students, 
										  b.last_name, 
										  b.first_name, 
										  b.email, 
										  b.phone 
									from $advisorClassTableName as a, 
										 $advisorTableName as b 
									where a.semester='$theSemester' 
										  and a.advisor_call_sign=b.call_sign 
										  and b.semester='$theSemester' 
									order by a.advisor_call_sign, a.sequence";
			$cwa_advisorclass			= $wpdb->get_results($sql);
			if ($doDebug) {
				echo "Reading $advisorClassTableName table<br />";
				echo "wpdb->last_query: " . $wpdb->last_query . "<br />";
				if ($wpdb->last_error != '') {
					echo "<b>wpdb->last_error: " . $wpdb->last_error . "</b><br />";
				}
			}
			if ($cwa_advisorclass !== FALSE) {
				$numACRows									= $wpdb->num_rows;
				if ($doDebug) {
					echo "found $numACRows rows in $advisorClassTableName table<br />";
				}
				if ($numACRows > 0) {
					foreach ($cwa_advisorclass as $advisorClassRow) {
						$advisorClass_advisor_callsign 		= $advisorClassRow->advisor_call_sign;
						$class_number_students						= $advisorClassRow->number_students;
						$class_evaluation_complete 					= $advisorClassRow->evaluation_complete;
						$advisor_first_name 					= $advisorClassRow->first_name;
						$advisor_last_name 					= $advisorClassRow->last_name;
						$advisor_email 						= strtolower($advisorClassRow->email);
						$advisor_phone 						= $advisorClassRow->phone;
			
						if ($doDebug) {
							echo "<br />Processing $advisorClassTableName pod record for advisor $advisorClass_advisor_callsign<br />";
						}
			
						$advisorArrayValue		= "$advisorClass_advisor_callsign|$advisor_email|$advisor_first_name|$advisor_last_name|$advisor_phone";
						if (!in_array($advisorArrayValue,$advisorArray)) {
							$advisorArray[]		= $advisorArrayValue;
							if ($doDebug) {
								echo "&nbsp;&nbsp;&nbsp;Adding $advisorClass_advisor_callsign ,$advisor_email,$advisor_first_name,$advisor_last_name,$advisor_phone to advisorArray<br />";
							}
						}
					}
				} else {
					$content	.= "$advisorClassTableName has no records<br />";
				}
			} else {
				echo "Something wrong with this $sql<br />";
			}
		}
		
		// Have the array of advisors needing to receive an email
		sort($advisorArray);
		
		if ($doDebug) {
			echo "<br />Advisor Array:<br />";
			foreach($advisorArray as $value) {
				echo "$value<br />";
			}
			echo "<br />";
		}

		foreach($advisorArray as $advisorArrayValue) {
			$advisorData		= explode("|",$advisorArrayValue);
			$advisor_call_sign	= $advisorData[0];
			$advisor_email		= $advisorData[1];
			$advisor_first_name	= $advisorData[2];
			$advisor_last_name	= $advisorData[3];
			$advisor_phone		= $advisorData[4];
			
			if ($doDebug) {
				echo "Getting email ready to send to $advisor_email ($advisor_call_sign)<br />";
			}
			$mySubject 		= "CW Academy Student Promotability Assessment for Advisor Class(es)";
			if ($testMode) {
				$myTo			= 'kcgator@gmail.com';
				$mailCode		= 2;
				$increment++;
				$mySubject 		= "TESTMODE $mySubject";
			} else {
				$myTo			= $advisor_email;
				$mailCode		= 12;
			}
			if ($addl_comments != '') {
				$addl_comments	= "<p>$addl_comments</p>";
			}
			$enstr				= "inp_call_sign=$advisor_call_sign&inp_email=$advisor_email&inp_phone=$advisor_phone&inp_mode=$inp_mode";
			$encstr				= base64_encode($enstr);
			$emailContent 		= "<p>To: $advisor_last_name, $advisor_first_name ($advisor_call_sign)</p>
$addl_comments
<p>The $theSemester semester is coming to an end. In a few days you will receive an email from CW Academy 
asking you to evaluate the promotability of your students.</p>
<p>To assist you in your evaluation process, you may want to have some or all of your students 
do a Morse code assessment and consider that as part of your evaluation. Advisors who wish to use 
this assessment tool generally select the students and send the assessment request about a 
week before the end of the semester.</p>
<p>To order assessments, go to your <a href='$siteURL/program-list/'>Advisor Portal</a> and select the 'Order Morse Code Proficiency Assessments' 
function. The program will explain how to order the assessments.</p>
<p>Thank you very much for your service as an Advisor!<br />
CW Academy</p>
<table style='border:4px solid red;'><tr><td>
<p><span style='color:red;font-size:14pt;'><b>Do not reply to this email as the address is not monitored.</b> 
<br />Please refer to the appropriate person at <a href='https://cwops.org/cwa-class-resolution/'>CWA Class 
Resolution</a> for assistance.</span></p></td></tr></table>";
			$mailResult		= emailFromCWA_v2(array('theRecipient'=>$myTo,
													    'theSubject'=>$mySubject,
													    'theContent'=>$emailContent,
													    'jobname'=>$jobname,
													    'mailCode'=>$mailCode,
													    'increment'=>$increment,
													    'testMode'=>$testMode,
													    'doDebug'=>$doDebug));
			if ($mailResult === TRUE) {
				$content .= "An email was sent to $advisor_call_sign ($myTo)<br />";
				$myCount++;
			} else {
				echo "The mail send function failed.<br /><pre>";
			}
		}
	}
	
	
	$thisTime 		= date('Y-m-d H:i:s');
	$content 		.= "<br /><br />$myCount emails sent to advisors<br /><br /><p>Prepared at $thisTime</p>";
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
add_shortcode ('send_end_of_semester_assessment_email_to_advisors', 'send_end_of_semester_assessment_email_to_advisors_func');
