function list_past_advisors_registration_info_func() {

/*	Gets a list of advisors from selected previous semesters
	Looks to see if the advisor is registered in the selected upcoming semester
	Prepares a report of advisors registered and advisors not registered
	
	modified 25Oct22 by Roland for new timezone table format
	Modified 16Apr23 by Roland to fix action_code
	Modified 13Jul23 by Roland to use consolidated tables
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
	$validUser 			= $initializationArray['validUser'];
	$userName			= $initializationArray['userName'];
	$currentTimestamp	= $initializationArray['currentTimestamp'];
	$validTestmode		= $initializationArray['validTestmode'];
	$siteURL			= $initializationArray['siteurl'];
	
//	CHECK THIS!								//////////////////////
	if ($validUser == "N") {
		return "YOU'RE NOT AUTHORIZED!<br />Goodby";
	}

//	ini_set('memory_limit','256M');
//	ini_set('max_execution_time',0);
//	set_time_limit(0);

//	if ($doDebug) {
		ini_set('display_errors','1');
		error_reporting(E_ALL);	
//	}

/// get the time that the process started
	$startingMicroTime			= microtime(TRUE);

	$strPass					= "1";
	$theURL						= "$siteURL/cwa-list-past-advisors-registration-info/";
	$inp_semester				= '';
	$inp_rsave					= '';
	$jobname					= "List Past Advisors Registration Info";
	$inpSemesterArray			= array();
	$futureSemester				= "";
	$nextSemester				= $initializationArray['nextSemester'];
	$semesterTwo				= $initializationArray['semesterTwo'];
	$semesterThree				= $initializationArray['semesterThree'];
	$advisorArray				= array();			
	$ClassesArray				= array();
	$newSemesterOrder			= array();
	$advisorMissingArray		= array();
	$inp_message				= '';
	

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
			if ($str_key 		== "futureSemester") {
				$futureSemester	 = $str_value;
				$futureSemester	 = filter_var($futureSemester,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "myEncode") {
				$myEncode		 = $str_value;
			}
			if ($str_key 		== "inp_message") {
				$inp_message	 = $str_value;
				$inp_message	 = filter_var($inp_message,FILTER_UNSAFE_RAW);
			}
			if (preg_match("/semesterSelection/i",$str_key )) {
				$inpSemesterArray[]	= $str_value;
			}
		}
	}
	if ($doDebug) {
		if (count($inpSemesterArray) > 0) {
			echo "inpSemesterArray:<br /><pre>";
			print_r($inpSemesterArray);
			echo "</pre><br />";
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
		$extMode						= 'tm';
		$advisorTableName			= "wpw1_cwa_consolidated_advisor2";
		$advisorClassTableName			= "wpw1_cwa_advisorclass2";
	} else {
		$extMode						= 'pd';
		$advisorTableName			= "wpw1_cwa_consolidated_advisor";
		$advisorClassTableName			= "wpw1_cwa_consolidated_advisorclass";
	}



	if ("1" == $strPass) {
	
		// get the list of past semesters and process them one at a time
		$pastSemesters			= $initializationArray['pastSemesters'];
		$pastSemesterArray		= explode("|",$pastSemesters);
		if ($doDebug) {
			echo "past semesters:<br /><pre>";
			print_r($pastSemesterArray);
			echo "</pre><br />";
		}
		$myInt					= count($pastSemesterArray) - 1;
		$jj						= 0;
		$semesterSelection		= '';
		for ($ii=$myInt;$ii>-1;$ii--) {
			$thisSemester		= $pastSemesterArray[$ii];
			if ($doDebug) {
				echo "<br />processing past semester $thisSemester<br />";
			}
			$semesterSelection		.= "<input type='checkbox' class='formInputButton' name='semesterSelection$jj' value='$thisSemester'> $thisSemester<br />"; 
			$jj++;
		}
	
	
		$content 		.= "<h3>$jobname</h3>
<p>Lists the advisors from past semesters who are or are not registered for a future 
semester.
<ol>
<li>Select the past semesters from which to obtain the past advisors list</li>
<li>Select the future semester to compare against</li>
</ol></p>
<form method='post' action='$theURL' 
name='selection_form' ENCTYPE='multipart/form-data'>
<input type='hidden' name='strpass' value='2'>
<table style='border-collapse:collapse;'>
<tr><td style='width:300px;vertical-align:top;'>Select past semester advisors to include</td>
	<td>$semesterSelection</td></tr>
<tr><td style='vertical-align:top;'>Select future semester</td>
	<td><input type='radio' class='formInputButton' name='futureSemester' value='$nextSemester' checked='checked'> $nextSemester<br />
		<input type='radio' class='formInputButton' name='futureSemester' value='$semesterTwo'> $semesterTwo<br />
		<input type='radio' class='formInputButton' name='futureSemester' value='$semesterThree'> $semesterThree</td></tr>
$testModeOption
<tr><td colspan='2'><input class='formInputButton' type='submit' value='Submit' /></td></tr></table>
</form></p>";
	

///// Pass 2 -- do the work


	} elseif ("2" == $strPass) {
//		if ($doDebug) {
//			echo "inpSemesterArray:<br /><pre>";
//			print_r($inpSemesterArray);
//			echo "</pre><br />";
//		}
		$content				.= "<h3>$jobname</h3>";
	
		/// for each of the inpSemesterArray get the advisors and put them in the advisorArray
		$semesterCount	= count($inpSemesterArray);
		if ($semesterCount == 0) {
			$content			.= "No past semesters selected. Job ending.";
		} else {
			for ($ii=$semesterCount-1;$ii>-1;$ii--) {
				$thisSemester 	= $inpSemesterArray[$ii];
				$newSemesterOrder[]	= $thisSemester;
				if ($doDebug) {
					echo "<br />Processing $advisorTableName for semester $thisSemester<br />";
				}
				$sql			= "select 
										call_sign, 
										first_name,
										last_name,
										email,
										survey_score,
										verify_response,
										phone
									from $advisorTableName
									where semester='$thisSemester' 
									order by call_sign";
				$result			= $wpdb->get_results($sql);
				if ($result === FALSE) {
					if ($doDebug) {
						echo "Running $sql returned FALSE<br />";
					}
					echo "wpdb->last_query: " . $wpdb->last_query . "<br />";
					echo "wpdb->last_error: " . $wpdb->last_error . "<br />";
				} else {
					$numARows	= $wpdb->num_rows;
					if ($numARows == 0) {
						$myStr		= $wpdb->last_query;
						$content	.= "No records found in $advisorTableName for semester $thisSemester<br />$myStr<br />";
					} else {
						if ($doDebug) {
							echo "processing $numARows records for $thisSemester<br /><br />";
						}
						foreach($result as $advisorRow) {
							$advisorCallSign		= $advisorRow->call_sign;
							$first_name				= $advisorRow->first_name;
							$last_name				= stripslashes($advisorRow->last_name);
							$email					= $advisorRow->email;
							$phone					= $advisorRow->phone;
							$surveyScore			= $advisorRow->survey_score;
							$verifyResponse			= $advisorRow->verify_response;
							
							if ($verifyResponse == 'Y' && $surveyScore != '6') {
								if ($doDebug) {
									echo "<br />Processing $advisorCallSign in semester $thisSemester<br/>";
								}
								$advisorArray[$advisorCallSign]['name']		= "$last_name, $first_name";
								$advisorArray[$advisorCallSign]['email']	= $email;
								$advisorArray[$advisorCallSign]['phone']	= $phone;
								
								// get advisor evaluation info and put in classesArray
								$evaluationArray		= AdvisorEvaluationStatus($advisorCallSign,$thisSemester,$testMode,$doDebug);
								if ($doDebug) {
									echo "evaluationArray returned:<br /><pre>";
									print_r($evaluationArray);
									echo "</pre><br />";
								}
								if ($evaluationArray[0] == FALSE) {
									if ($doDebug) {
										echo "got FALSE back from AdvisorEvaluationStatus for $advisorCallSign, $thisSemester<br />";
									}
									$evalsDone								= 'X';
								} else {
									foreach($evaluationArray as $thisValue) {
										$myArray				= explode("|",$thisValue);
										$thisSequence			= $myArray[0];
										$thisLevel				= $myArray[1];
										$thisNmbrStudents		= $myArray[2];
										$thisNmbrEvaluated		= $myArray[3];
										$thisNmbrNotEvaluated	= $myArray[4];
										
										if ($doDebug) {
											echo "thisNmbrNotEvaluated: $thisNmbrNotEvaluated<br />";
										}
										if ($thisNmbrNotEvaluated > 0) {
											$evalsDone			= 'Eval: X';
										} else {
											$evalsDone			= 'Eval: OK';
										}
										$classesArray[$advisorCallSign][$thisSemester][$thisSequence] 	= "$thisLevel|$evalsDone";
										if ($doDebug) {
											echo "wrote $thisLevel|$evalsDone to classesArray for $advisorCallSign, $thisSemester<br />";
										}
										
									}
								}
							}
						}
					}
				}
			}
		}
		
		
		/// Now get the advisors from the future semester and add to the table
		if ($doDebug) {
			echo "<br />Going after advisors in future semester $futureSemester<br />";
		}
		$newSemesterOrder[]	= $futureSemester;
		if ($doDebug) {
			echo "Processing $advisorTableName for semester $futureSemester<br />";
		}
		$sql			= "select 
								call_sign, 
								first_name,
								last_name,
								email,
								survey_score,
								verify_response,
								phone
							from $advisorTableName
							where semester='$futureSemester' 
							order by call_sign";
		$result			= $wpdb->get_results($sql);
		if ($result === FALSE) {
			if ($doDebug) {
				handleWPDBError($jobname,$doDebug);
			}
		} else {
			$numARows	= $wpdb->num_rows;
			if ($numARows == 0) {
				$myStr		= $wpdb->last_query;
				$content	.= "No records found in $advisorTableName for semester $futureSemester<br />$myStr<br />";
			} else {
				if ($doDebug) {
					$myStr					= $wpdb->last_query;
					echo "ran $myStr<br /> and processing $numARows records for $futureSemester<br /><br />";
				}
				foreach($result as $advisorRow) {
					$advisorCallSign		= $advisorRow->call_sign;
					$first_name				= $advisorRow->first_name;
					$last_name				= stripslashes($advisorRow->last_name);
					$email					= $advisorRow->email;
					$phone					= $advisorRow->phone;
					$surveyScore			= $advisorRow->survey_score;
					$verifyResponse			= $advisorRow->verify_response;
					
					if ($surveyScore != '6') {
						if ($doDebug) {
							echo "<br />Processing $advisorCallSign in semester $thisSemester<br/>";
						}
						$advisorArray[$advisorCallSign]['name']			= "$last_name, $first_name";
						$advisorArray[$advisorCallSign]['email']		= $email;
						$advisorArray[$advisorCallSign]['phone']		= $phone;
						
						// now get the classes for the advisor in the future semester
						$wpw1_cwa_advisorclass				= $wpdb->get_results("SELECT sequence, 
																					     level 
																					FROM $advisorClassTableName 
																					WHERE advisor_call_sign='$advisorCallSign' 
																					and semester = '$futureSemester' 
																					order by sequence");
						if ($wpw1_cwa_advisorclass === FALSE) {
							handleWPDBError($jobname,$doDebug);
						} else {
							$numACRows						= $wpdb->num_rows;
							if ($doDebug) {
								$myStr						= $wpdb->last_query;
								echo "ran $myStr<br />and found $numACRows rows<br />";
							}
							if ($numACRows > 0) {
								foreach ($wpw1_cwa_advisorclass as $advisorClassRow) {
									$classSequence			= $advisorClassRow->sequence;
									$classLevel				= $advisorClassRow->level;
						
									$classesArray[$advisorCallSign][$futureSemester][$classSequence] 	= "$classLevel|";
								}
							}
						}
					}
				}
			}
		}
		ksort($advisorArray);
		ksort($classesArray);
		if ($doDebug) {
			echo "newSemesterOrder:<br /><pre>";
			print_r($newSemesterOrder);
			echo "</pre><br /><br />AdvisorArray:<br /><pre>";
			print_r($advisorArray);
			echo "<br /></pre><br />classesArray:<br /><pre>";
			print_r($classesArray);
			echo "</pre><br />";
		}



		// Now prepare the report
		$columnCount		= count($newSemesterOrder);
		$advisorCount		= count($advisorArray);
		$content			.= "<table>";
		foreach($advisorArray as $thisAdvisor=>$thisData) {
			$advisorOK		= FALSE;						// when true, advisor is in future semester
			$advisorName	= $advisorArray[$thisAdvisor]['name'];
			$advisorPhone	= $advisorArray[$thisAdvisor]['phone'];
			$advisorEmail	= $advisorArray[$thisAdvisor]['email'];
			$content		.= "<tr><td colspan='$columnCount'><b>Advisor</b>: $thisAdvisor $advisorName
								&nbsp;&nbsp;&nbsp;&nbsp;<b>Phone</b>: $advisorPhone
								&nbsp;&nbsp;&nbsp;&nbsp;<b>Email</b>: $advisorEmail</td></tr><tr>";
								
			// set up the semesters columns
			foreach($newSemesterOrder as $semester_name) {
				${'box' . $semester_name} 	= "<u>$semester_name</u><br />";
			}
			
			if (array_key_exists($thisAdvisor,$classesArray)) {
				foreach($classesArray[$thisAdvisor] as $thisSemester=>$thisData) {
					foreach($thisData as $thisSequence=>$thisInfo) {
						$myArray		= explode("|",$thisInfo);
						$thisLevel		= $myArray[0];
						$thisEval		= $myArray[1];
						if ($doDebug) {
							echo "Doing class $thisLevel with $thisEval<br />";
						}
				
						${'box' . $thisSemester}	.= "$thisLevel $thisEval<br />";
						if ($thisSemester == $futureSemester) {
							$advisorOK	= TRUE;
						}
					}
				}
				$content			.= "<tr>";

				for ($ii=0;$ii < count($newSemesterOrder);$ii++) {
					$mySemester		= $newSemesterOrder[$ii];
					if ($doDebug) {
						echo "getting the contents of box $mySemester<br />";
					}
					$content		.= "<td style='vertical-align:top;'>${'box' . $mySemester}</td>";
				}
				$content			.= "</tr>									
										<tr><td colspan='$columnCount'><hr></td></tr>";
				if (!$advisorOK) {				/// advisor not signed up for future semester
					$advisorMissingArray[]	= "$thisAdvisor|$advisorName|$advisorPhone|$advisorEmail";
				}
			} else {
				$content			.= "<tr><td colspan='$columnCount'>No Classes Found</td></tr>
										<tr><td colspan='$columnCount'><hr></td></tr>";
			}
		}
		$content				.= "</table><p>$advisorCount Advisors</p>";

		$transferArray				= array();
		$mySeq						= 0;
		$content					.= "<h4>Advisors Not Registered for the $futureSemester Semester</h4>
										<table>
									<tr><th>Call Sign</th>
										<th>Name</th>
										<th>Phone</th>
										<th>Email</th>
									</tr>";
		foreach($advisorMissingArray as $thisData) {
			$myArray				= explode("|",$thisData);
			$thisCallSign			= $myArray[0];
			$thisName				= $myArray[1];
			$thisPhone				= $myArray[2];
			$thisEmail				= $myArray[3];
			$content				.= "<tr><td>$thisCallSign</td>
											<td>$thisName</td>
											<td>$thisPhone</td>
											<td>$thisEmail</td>
										</tr>";
		}
		$content					.="</table>";
		$myInt						= count($advisorMissingArray);
		$content					.= "$myInt past advisors not registered for the $futureSemester semester<br />";
		
		// Send them an email?
		$myEncode					= base64_encode(json_encode($advisorMissingArray));
		$content					.= "<h4>Send an Email to the Unregistered Advisors?</h4>
										<p>If you would like to send an email to the advisors who have not 
										yet registered for the $futureSemester semester, compose the email 
										you would like to send and click 'Submit'. The subject line will read: 
										CW Academy Is Missing You.</p>
										<p>Otherwise, you may close this window.</p>
										<form method='post' action='$theURL' 
										name='selection_form_b' ENCTYPE='multipart/form-data'>
										<input type='hidden' name='strpass' value='3'>
										<input type='hidden' name='inp_mode' value='$inp_mode'>
										<input type='hidden' name='inp_verbose' value='$inp_verbose'>
										<input type='hidden' name='myEncode' value='$myEncode'>
										<p>Message Text to be Sent to All Unregistered Advisors:</p>
										<textarea class='formInputText' name='inp_message' rows='5' cols='50'></textarea>
										<input class='formInputButton' type='submit' value='Submit' />
										</form>";


	} elseif ("3" == $strPass) {
		if ($doDebug) {
			echo "arrived at pass 3<br />";
		}

		$thisEncode		= base64_decode($myEncode);
		$advisorMissingArray	= json_decode($thisEncode);
		
		if ($doDebug) {
			echo "advisorMissingArray:<br /><pre>";
			print_r($advisorMissingArray);
			echo "</pre><br />";
		}
		
		if ($inp_message == '') {
			$content		.= "No message entered. Program ending.";
		} else {
			$content		.= "<h3>$jobname</h3>";
			$increment		= 0;
			
			$theSubject		= "CW Academy Is Missing You";
			
			foreach($advisorMissingArray as $thisValue) {
				$myArray				= explode("|",$thisValue);
				$thisCallSign			= $myArray[0];
				$thisName				= $myArray[1];
				$thisPhone				= $myArray[2];
				$thisEmail				= $myArray[3];
				
				$theContent				= "<p>To: $thisName ($thisCallSign):</p>
											<p>$inp_message</p>
											<p>73,<br />CW Academy</p>";
				
				if ($testMode) {
					$theRecipient		= "rolandksmith@gmail.com";
					$mailCode			= 2;
					$theSubject			= "TESTMODE $theSubject";
					$increment++;
				} else {
					$theRecipient		= $thisEmail;
					$mailCode			= 13;
				}
				
				$mailResult		= emailFromCWA_v2(array('theRecipient'=>$theRecipient,
															'theSubject'=>$theSubject,
															'theContent'=>$theContent,
															'theCc'=>'',
															'mailCode'=>$mailCode,
															'jobname'=>$jobname,
															'increment'=>$increment,
															'testMode'=>$testMode,
															'doDebug'=>$doDebug));
				if ($mailResult !== FALSE) {
					if ($testMode) {
						$content			.= "Email would have been sent to $theRecipient<br />";
					} else {
						$content			.= "Email sent to $theRecipient<br />";
					}
				}
			}
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
add_shortcode ('list_past_advisors_registration_info', 'list_past_advisors_registration_info_func');
