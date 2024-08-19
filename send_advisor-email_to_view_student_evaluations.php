function send_advisor_email_to_view_student_evaluations_func() {

/*	The function reads the class pod and gathers a list of all advisors 
 *	and sends an email to each of the advisors with a link to a web page 
 *	where they can see all of their student's evaluations and comments.
 *
 *	modified 4Mar21 by Roland to change Joe Fisher's email address
 *  modified 6Jun21 by Roland to update for new advisor pod structure
 *  modified 7Jun21 by Roland to send emails to a list of advisors or to all advisors
 	modified 25Oct22 by Roland to accomodate timezone table format
 	Modified 10Jul23 by Roland to run from consolicated tables
 	Modified 7Mar24 by Roland to use reminders
*/

	global $wpdb;

	$doDebug					= FALSE;
	$testMode					= FALSE;
	$bobTest					= FALSE;
	$initializationArray 		= data_initialization_func();
	$versionNumber				= '1';
	if ($doDebug) {
		echo "Initialization Array:<br /><pre>";
		print_r($initializationArray);
		echo "</pre><br />";
	}
	$validUser 					= $initializationArray['validUser'];
	$userName					= $initializationArray['userName'];
	$validTestmode				= $initializationArray['validTestmode'];
	$siteURL					= $initializationArray['siteurl'];
	
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

	$strPass					= "1";
	$inp_semester				= '';
	$theURL						= "$siteURL/cwa-send-advisor-email-to-view-student-evaluations/";
	$viewURL					= "$siteURL/cwa-display-evaluations-for-an-advisor/";
	$recordsRead				= 0;
	$emailsSent					= 0;
	$advisorArray				= array();
	$increment					= 0;
	$jobname					= 'Send Advisor Email to View Student Evaluations V$versionNumber';
	$inp_list					= '';
	
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
			if ($str_key 		== "inp_list") {
				$inp_list	 	= strtoupper($str_value);
				$inp_list	 	= filter_var($inp_list,FILTER_UNSAFE_RAW);
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
				if ($inp_mode == 'bobest') {
					$bobTest	= TRUE;
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
		$content						.= "<p><strong>Operating in Test Mode.</strong></p>";
		$advisorClassTableName			= 'wpw1_cwa_consolidated_advisorclass';
		$advisorTableName				= 'wpw1_cwa_consolidated_advisor';
		if ($doDebug) {
			echo "<p><strong>Operating in Test Mode.</strong></p>";
		}
	} else {
		$advisorClassTableName			= 'wpw1_cwa_consolidated_advisorclass';
		$advisorTableName				= 'wpw1_cwa_consolidated_advisor';
	}
	
	if ($bobTest) {
		if ($doDebug) {
			echo "<strong>Running in bobTest mode</strong><br />";
		}
		$content						.= "<p><b>Operating in bobTest mode</b></p>";
	}

	if (in_array($userName,$validTestmode)) {			// give option to run in test mode 
		$testModeOption	= "<tr><td>Operation Mode</td>
								<td><input type='radio' class='formInputButton' name='inp_mode' value='Production' checked='checked'> Production<br />
									<input type='radio' class='formInputButton' name='inp_mode' value='bobtest' > BobTest<br />
									<input type='radio' class='formInputButton' name='inp_mode' value='TESTMODE'> TESTMODE</td></tr>
								<tr><td>Verbose Debugging?</td>
									<td><input type='radio' class='formInputButton' name='inp_verbose' value='N' checked='checked'> Standard Output<br />
										<input type='radio' class='formInputButton' name='inp_verbose' value='Y'> Turn on Debugging </td></tr>";
	} else {
		$testModeOption	= '';
	}

	if ($testMode || $bobTest) {
		$myInt					= 9;
	} else {
		$myInt					= 900;
	}


	if ("1" == $strPass) {
		if ($doDebug) {
			echo "Function starting.<br />";
		}
		$content 		.= "<h3>Send Advisor Email to View Student Evaluations</h3>
							<p><form method='post' action='$theURL' 
							name='selection_form' ENCTYPE='multipart/form-data''>
							<input type='hidden' name='strpass' value='2'>
							<table>
							<tr><td>Send Email List</td>
								<td><input type='test' class='formInputText' name='inp_list' size='50' maxlength='100' value='all'> <br />To send the email 
							to all advisors, enter the word 'all'.<br /> To send to one or more specific advisors, enter the list separated 
							by commas</td></tr>
							$testModeOption
							<tr><td colspan='2'><input class='formInputButton' type='submit' value='Next' /></td></tr></table>
							</form></p>";

///// Pass 2 -- do the work


	} elseif ("2" == $strPass) {
		if ($inp_list == '') {
			$content		.= "No advisors requested<br />";
		}
		$currentSemester	= $initializationArray['currentSemester'];
		$prevSemester		= $initializationArray['prevSemester'];
		if ($currentSemester == 'Not in Session') {
			$theSemester	= $prevSemester;
		} else {
			$theSemester	= $currentSemester;
		}
		if ($inp_list != 'ALL') {
			$thisArray		= explode(",",$inp_list);
			if (count($thisArray) < 1) {
				$content	.= "Invalid input: $inp_list<br />";
			}
		}
		
		
		//// get the advisor and advisorclass records
		
		$sql 		= "select a.advisor_call_sign, 
							  a.evaluation_complete, 
							  b.advisor_id, 
							  b.first_name, 
							  b.last_name, 
							  b.email 
						from $advisorClassTableName as a 
						join $advisorTableName as b 
						where a.semester='$theSemester' 
						and a.advisor_call_sign=b.call_sign 
						and a.semester=b.semester 
						order by a.advisor_call_sign,a.sequence";
		$wpw1_cwa_advisorclass			= $wpdb->get_results($sql);
		if ($wpw1_cwa_advisorclass === FALSE) {
			handleWPDBError($jobname,$doDebug);
		} else {
			$numACRows									= $wpdb->num_rows;
			if ($doDebug) {
				echo "ran $sql<br />and found $numACRows rows in $advisorClassTableName and $advisorTableName tables<br />";
			}
			if ($numACRows > 0) {
				foreach ($wpw1_cwa_advisorclass as $advisorClassRow) {
					$advisor_ID					 		= $advisorClassRow->advisor_id;
					$advisorClass_advisor_callsign 		= $advisorClassRow->advisor_call_sign;
					$advisor_first_name 				= $advisorClassRow->first_name;
					$advisor_last_name 					= $advisorClassRow->last_name;
					$advisor_email 						= strtolower($advisorClassRow->email);
					$advisorClass_evaluation_complete 	= $advisorClassRow->evaluation_complete;

					if ($doDebug) {
						echo "<br />Processing $advisorClass_advisor_callsign<br />";
					}
					$doProcess							= FALSE;
					if ($inp_list != 'ALL') {
						if (in_array($advisorClass_advisor_callsign,$thisArray)) {
							$doProcess					= TRUE;
						} 
					} else {
						$doProcess						= TRUE;
					}
					if ($doProcess) {
						if ($advisorClass_evaluation_complete == 'Y') {
							if (!array_key_exists($advisorClass_advisor_callsign,$advisorArray)) {
								if ($doDebug) {
									echo "adding $advisorClass_advisor_callsign to advisor Array<br />";
								}
								$advisorInfo			= "$advisor_ID|$advisor_first_name|$advisor_last_name|$advisor_email";
								if ($doDebug) {
									echo "Adding $advisorInfo for $advisorClass_advisor_callsign to advisorArray<br />";
								}
								$advisorArray[$advisorClass_advisor_callsign]			= $advisorInfo;
							}
						} else {
							if ($doDebug) {
								echo "advisorClass evaluations are not complete. Advisor bypassed<br />";
							}
						}
					}
				}
			} else {
				if ($doDebug) {
					echo "No records found in $advisorClassTableName / $advisorTableName<br />";
				}
				$content	.= "No records found in $advisorClassTableName / $advisorTableName<br />";
			}
		}
		// should now have an advisor array
		ksort($advisorArray);
		if ($doDebug) {
			echo "<br />Advisor Array:<br />";
			foreach($advisorArray as $key=>$value) {
				echo "$key = $value<br />";
			}
		}



		// send the email here
		foreach($advisorArray as $key=>$value) {
			$recordsRead++;
			$myArray		= explode("|",$value);
			$theID			= $myArray[0];
			$theFirstName 	= $myArray[1];
			$theLastName	= $myArray[2];
			$theEmail		= $myArray[3];
			$my_subject		= "CW Academy - Displaying Your Student Evaluations";		
			if ($testMode) {
				$my_to		= "rolandksmith@gmail.com";
				$my_subject	= "TESTMODE $my_subject";
				$mailCode	= 5;
				$increment++;
			} elseif ($bobTest) {
				$my_to		= "kcgator@gmail.com";
				$mailCode	= 18;
				$my_subject	= "bobTest $my_subject";
			} else {
				$my_to		= $theEmail;
				$mailCode	= 12;
//				$my_to		= "kcgator@gmail.com";												
			}
			$my_message 	= "<p>To: $theLastName, $theFirstName ($key):</p>
<p>Around the end of the semester the students were sent an email requesting their 
evaluation of the class, tools, curriculum, and advisor. Many students have submitted 
their evaluation. If any of your students submitted an evaluation, you can view the evaluations your students submitted by 
logging into <a href='$siteURL/program-list/'>CW Academy</a> and following the link under 
Reminders and Actions Requested.
<table style='border:4px solid red;'><tr><td>
<p><span style='color:red;font-size:14pt;'><b>Do not reply to this email as the address is not monitored.</b> 
<br />Please refer to the appropriate person at <a href='https://cwops.org/cwa-class-resolution/'>CWA Class 
Resolution</a> for assistance.</span></p></td></tr></table><p>Thanks and 73,<br />
CW Academy</p>";
			if ($myInt > 0) {
				
				$mailResult		= emailFromCWA_v2(array('theRecipient'=>$my_to,
															'theSubject'=>$my_subject,
															'theContent'=>$my_message,
															'theCc'=>'',
															'theAttachment'=>'',
															'mailCode'=>$mailCode,
															'jobname'=>$jobname,
															'increment'=>$increment,
															'testMode'=>$testMode,
															'doDebug'=>$doDebug));
				if ($mailResult === TRUE) {
					if ($doDebug) {
						echo "A email was sent to $my_to<br /><br />";
					}
					$emailsSent++;

					// add the reminder
					$effective_date		 	= date('Y-m-d H:i:s');
					$closeStr				= strtotime("+5 days");
					$close_date				= date('Y-m-d H:i:s', $closeStr);
					$token					= mt_rand();
					$email_text				= "<p></p>";
					$reminder_text			= "<b>View Student Evaluations:</b> To see your student 
evaluations, click 
<a href='$viewURL?strpass=2&inp_advisor=$key&inp_id=$theID&mode=1&token=$token' target='_blank'>Display Student Evaluations</a>. 
Note that some students may have entered more that one evaluation. Also, you might want to check back 
after a few days as more students may have responded by then.";
					$inputParams		= array("effective_date|$effective_date|s",
												"close_date|$close_date|s",
												"resolved_date||s",
												"send_reminder|N|s",
												"send_once|Y|s",
												"call_sign|$key|s",
												"role||s",
												"email_text|$email_text|s",
												"reminder_text|$reminder_text|s",
												"resolved||s",
												"token|$token|s");
					$insertResult		= add_reminder($inputParams,$testMode,$doDebug);
					if ($insertResult[0] === FALSE) {
						if ($doDebug) {
							echo "inserting reminder failed: $insertResult[1]<br />";
						}
						$content		.= "Inserting reminder failed: $insertResult[1]<br />";
					} else {
						$content		.= "Reminder successfully added<br />";
					}
				}
				$myInt--;
			}
		}
		$content	.= "<p>Advisor Records Read: $recordsRead<br />Emails sent: $emailsSent</p>";
	
	
	}
	$thisTime 		= date('Y-m-d H:i:s');
	$content 		.= "<br /><br /><p>V$versionNumber Prepared at $thisTime</p>";
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
add_shortcode ('send_advisor_email_to_view_student_evaluations', 'send_advisor_email_to_view_student_evaluations_func');
