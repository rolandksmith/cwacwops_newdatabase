function send_evaluation_email_to_advisors_func() {

/* Send Evaluation Email to Advisors
 *
 * Read the advisorClass table for current semester (or previous semester, if a semester 
 * is not in session) and see if evaluation_complete = Y
 *	If not, check to see if the advisor is in the advisor array
 *		If not in the array, add advisor,callsign to the array
 *
 * When all dvisorClass records have been read, sort the advisor array
 * For each advisor array record
 * 		Get the advisor name and email address from the advisor table
 *		Format the email to the advisor
 *		Send the email
 *
 * Modified 8Jun20 by Roland to use previous semester if a semester is not in session
 * Modified 12May21 by Roland to use past_advisor and past_advisorClass pods
 	Modified 12Feb2022 by Roland to use the new table structure and new website
 	Modified 14Jun23 by Roland to use current tables rather than past tables
 	Modified 14Jul23 by Roland to use consolidated tables
 	Modified 6Dec23 by Roland for Advisor Portal
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
	$nextSemester		= $initializationArray['nextSemester'];
	$theURL				= "$siteURL/cwa-send-evaluation-email-to-advisors/";
	$evaluateStudentURL	= "$siteURL/cwa-evaluate-student/";
	$jobname			= 'Send Evaluation Email to Advisors';
	$gotAdditionalAdvisors	= FALSE;


// get the input information
	if (isset($_REQUEST)) {
		foreach($_REQUEST as $str_key => $str_value) {
			if ($doDebug) {
				echo "Key: $str_key | Value: $str_value <br />\n";
			}
			if ($str_key 		== "strpass") {
				$strPass		 = $str_value;
			}
			if ($str_key 		== "additionaltext") {
				$additionaltext		 = $str_value;
			}
			if ($str_key 		== "additionaladvisors") {
				$additionaladvisors		 = strtoupper($str_value);
				if ($additionaladvisors != '') {
					$gotAdditionalAdvisors	= TRUE;
				}
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
		$imp_mode						= 'pd';
	}

	$currentSemester	= $initializationArray['currentSemester'];
	$prevSemester		= $initializationArray['prevSemester'];
	if ($currentSemester == "Not in Session") {
		$theSemester	= $prevSemester;
	} else {
		$theSemester	= $currentSemester;			
	}

	if ("1" == $strPass) {
		if ($doDebug) {
			echo "Function starting.<br />";
		}
		$content 		.= "<h3>Send Evaluation Email to Advisors</h3>
<p>This function reads the advisorClass table for the current 
semester and sends an email to any advisors who have not completed evaluating 
their students requesting them to complete the evaluations.</p>
<p><form method='post' action='$theURL' 
name='selection_form' ENCTYPE='multipart/form-data'>
<input type='hidden' name='strpass' value='2'>
<table>
<tr><td>Enter any additional text to be included in the advisor email</td>
	<td><textarea class='formInputText' name='additionaltext' rows='5' cols='50'></textarea></td></tr>
<tr><td>Enter any specific advisor who should receive this email separated by commas</td>
	<td><textarea class='formInputText' name='additionaladvisors' rows='5' cols='50'></textarea></td></tr>
$testModeOption
<tr><td colspan='2'><input class='formInputButton' type='submit' value='Next' /></tr></td></table>
</form></p>";
		return $content;

///// Pass 2 -- do the work


	} elseif ("2" == $strPass) {
	
		if ($doDebug) {
			echo "Arrived at pass 2<br />";
		}

		$content .= "<h3>Send EvaluationEmail to Advisors</h3>";
		if ($gotAdditionalAdvisors && $additionaladvisors != '') {
			$additionalAdvisorsArray	= explode(",",$additionaladvisors);		
			$content	.= "<p>Processing only $additionaladvisors</p>";
		}
		// Access the advisor TABLE and cycle through the records for currentSemester
		
		$sql				= "select a.advisorclass_id, 
									  a.advisor_call_sign, 
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
		$wpw1_cwa_advisorclass			= $wpdb->get_results($sql);
		if ($doDebug) {
			echo "Reading $advisorClassTableName table<br />";
			echo "wpdb->last_query: " . $wpdb->last_query . "<br />";
			if ($wpdb->last_error != '') {
				echo "<b>wpdb->last_error: " . $wpdb->last_error . "</b><br />";
			}
		}
		if ($wpw1_cwa_advisorclass !== FALSE) {
			$numACRows									= $wpdb->num_rows;
			if ($doDebug) {
				echo "found $numACRows rows in $advisorClassTableName table<br />";
			}
			if ($numACRows > 0) {
				foreach ($wpw1_cwa_advisorclass as $advisorClassRow) {
					$advisorClass_id					= $advisorClassRow->advisorclass_id;
					$advisorClass_advisor_callsign 		= $advisorClassRow->advisor_call_sign;
					$class_number_students				= $advisorClassRow->number_students;
					$class_evaluation_complete 			= $advisorClassRow->evaluation_complete;
					$advisor_first_name 				= $advisorClassRow->first_name;
					$advisor_last_name 					= $advisorClassRow->last_name;
					$advisor_email 						= strtolower($advisorClassRow->email);
					$advisor_phone 						= $advisorClassRow->phone;
				
					if ($doDebug) {
						echo "<br />Processing $advisorClassTableName table record for advisor $advisorClass_advisor_callsign<br />";
					}
					
					$doContinue							= TRUE;
					if ($gotAdditionalAdvisors){
						$doContinue						= FALSE;
						if (in_array($advisorClass_advisor_callsign,$additionalAdvisorsArray)) {
							$doContinue					= TRUE;
							$class_evaluation_complete	= '';
							$classUpdateData		= array('tableName'=>$advisorClassTableName,
															'inp_method'=>'update',
															'inp_data'=>array('evaluation_complete'=>''),
															'inp_format'=>array('%s'),
															'jobname'=>$jobname,
															'inp_id'=>$advisorClass_id,
															'inp_callsign'=>$advisorClass_advisor_callsign,
															'inp_semester'=>$theSemester,
															'inp_who'=>$userName,
															'testMode'=>$testMode,
															'doDebug'=>$doDebug);
							$updateResult	= updateClass($classUpdateData);
							if ($updateResult[0] === FALSE) {
								handleWPDBError($jobname,$doDebug);
							}
						}
					}
					if ($doContinue) {	
						if (($class_evaluation_complete == '' || $class_evaluation_complete == 'N') && $class_number_students > 0){
							if ($doDebug) {
								echo "&nbsp;&nbsp;&nbsp;Advisor will get an email<br />";
							}					
							$advisorArrayValue		= "$advisorClass_advisor_callsign|$advisor_email|$advisor_first_name|$advisor_last_name|$advisor_phone";
							if (!in_array($advisorArrayValue,$advisorArray)) {
								$advisorArray[]		= $advisorArrayValue;
								if ($doDebug) {
									echo "&nbsp;&nbsp;&nbsp;Adding $advisorClass_advisor_callsign ,$advisor_email,$advisor_first_name,$advisor_last_name,$advisor_phone to advisorArray<br />";
								}
							}
						} else {
							if ($doDebug) {
								echo "AdvisorClass record bypassed as evaluations are complete or no students. class_evaluation_complete = $class_evaluation_complete | class_number_students = $class_number_students<br />";
							}
						}
					}
				}
			} else {
				$content	.= "$advisorClassTableName has no records<br />";
			}
		} else {
			echo "Something wrong with this $sql<br />";
		}
		
// Have the array of advisors needing to receive an email
		if ($doDebug) {
			sort($advisorArray);
			echo "<br />Advisor Array:<br />";
			foreach($advisorArray as $value);
				echo "$value<br />";
			echo "<br />";
		}
// sort the advisor array
		if (!$doDebug) {
			sort($advisorArray);
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
			$mySubject 		= "CW Academy Student Promotability Evaluation for Advisor Class(es)";
			if ($testMode) {
				$myTo			= 'kcgator@gmail.com';
				$mailCode		= 2;
				$increment++;
				$mySubject 		= "TESTMODE $mySubject";
			} else {
				$myTo			= $advisor_email;
				$mailCode		= 12;
			}
			$enstr				= "inp_call_sign=$advisor_call_sign&inp_email=$advisor_email&inp_phone=$advisor_phone";
			$encstr				= base64_encode($enstr);
			if ($additionaltext != '') {
				$additionaltext	= "<p>$additionaltext</p>";
			}
			$emailContent 		= "<p>To: $advisor_last_name, $advisor_first_name ($advisor_call_sign)</p>
$additionaltext
<p>The $theSemester semester is coming to an end and the 
$nextSemester semester will be starting soon. <b>It’s time to evaluate the promotable status of your 
current students.</b> While its not necessary to evaluate your students until the end of the class, 
you'll continue to get reminder emails until you do.</p>
<p>To enter the student promotability information, log in to the <a href='$siteURL/program-list'>CW Academy</a> 
website and follow the instructions under 'Reminders and Actions Requested'.</p>
<p><b>NOTE:</b> <i>Reminder emails will be sent to you periodically until your evaluations are complete.</i></p>
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
				echo "The mail send function failed.<br />";
			}
			// add reminder
			if ($doDebug) {
				echo "preparing to add reminder<br />";
			}
			$token			= mt_rand();
			$reminder_text	= "<b>Evaluate Student Promotability</b> Please enter the promotability information for your students, that is,  
is the Beginner, Fundamental, or Intermediate student is ready to take the next higher level class, or 
the Advanced student met the class objectives. 
Please click 
<a href='$evaluateStudentURL?semester=$theSemester&strpass=2&inp_mode=$inp_mode&inp_call_sign=$advisor_call_sign&token=$token' target='_blank'>
<b>Evaluate Students</b></a>. A CWA web page will display and allow you to enter your evaluations. 
When all your evaluations are completed, you’ll be immediately able to register as an advisor for the next semester.";

			$effective_date		= date('Y-m-d 00:00:00');
			$closeStr			= strtotime("+ 4 days");
			$close_date			= date('Y-m-d 00:00:00',$closeStr);
			$token				= mt_rand();
			$inputParams		= array("effective_date|$effective_date|s",
										"close_date|$close_date|s",
										"resolved_date||s",
										"send_reminder|3|s",
										"send_once|N|s",
										"call_sign|$advisor_call_sign|s",
										"role||s",
										"email_text|$emailContent|s",
										"reminder_text|$reminder_text|s",
										"resolved|N|s",
										"token|$token|s");
			$reminderResult	= add_reminder($inputParams,$testMode,$doDebug);
			if ($reminderResult[0] === FALSE) {
				if ($doDebug) {
					echo "adding reminder failed. $reminderResult[1]<br />";
				}
			} else {
				if ($doDebug) {
					echo "adding reminder was successful<br />";
				}
			}

		}
	}

	// send email that the job was run
	$thisDate				= date('Y-m-d H:i:s');
	$theRecipient			= 'kcgator@gmail.com';
	$theSubject				= "Program Send Evaluation Email to Advisors Was Executed by $userName";
	if ($testMode) {
		$theContent			= "Send Evaluation Email to Advisors was run on $thisDate in TESTMODE";
	} else {
		$theContent			= "Send Evaluation Email to Advisors was run on $thisDate in PRODUCTION";
	}
	$theCc					= '';
	$mailCode				= 18;
	$increment				= 0;
	$mailResult		= emailFromCWA_v2(array('theRecipient'=>$theRecipient,
										    'theSubject'=>$theSubject,
										    'theContent'=>$theContent,
										    'theCc'=>$theCc,
										    'mailCode'=>$mailCode,
										    'jobname'=>$jobname,
										    'increment'=>$increment,
										    'testMode'=>$testMode,
										    'doDebug'=>$doDebug));
	if ($mailResult === FALSE) {
		$content	.= "<p>Email at end of program failed</p>";
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
add_shortcode ('send_evaluation_email_to_advisors', 'send_evaluation_email_to_advisors_func');
