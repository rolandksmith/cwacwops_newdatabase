function performAssessment_func() {

	global $wpdb;

	$doDebug						= FALSE;
	$testMode						= FALSE;
	$initializationArray 			= data_initialization_func();
	$validUser 						= $initializationArray['validUser'];
/*
	if ($validUser == 'N') {				// turn off debug and testmode
		$doDebug					= FALSE;
		$testMode					= FALSE;
	}
*/
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
	$validTestmode		= $initializationArray['validTestmode'];


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
	$theURL						= "$siteURL/cwa-perform-assessment/";
	$jobname					= "Perform Assessment V$versionNumber";
	$parameterArray				= array('Beginner'=>'15&4&5&2&2&5',		// cpm & wpm & questions & word & characters
										'Fundamental'=>'25&6&5&2&2&5',
										'Intermediate'=>'25&10&5&2&3&5',
										'Advanced'=>'25&20&5&2&4&5');
	$inp_mode1					= '';
	$inp_callsign				= '';
	$inp_level					= '';
	$inp_cpm					= '';
	$inp_eff					= '';
	$inp_freq					= '';
	$inp_questions				= '';
	$inp_words					= '';
	$inp_characters				= '';
	$inp_answers				= '';
	$inp_vocab					= '';
	$inp_infor					= '';
	$inp_callsign_count			= 0;
	$inp_makeup					= "";
	
	$vocabConvert				= array('threek'=>'3k Words',
										'original'=>'900 Words');

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
			if ($str_key 		== "inp_callsign") {
				$inp_callsign	 = $str_value;
				$inp_callsign	 = filter_var($inp_callsign,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "inp_level") {
				$inp_level	 = $str_value;
				$inp_level	 = filter_var($inp_level,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "inp_mode1") {
				$inp_mode1	 = $str_value;
				$inp_mode1	 = filter_var($inp_mode1,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "inp_wpm") {
				$inp_wpm	 = $str_value;
				$inp_wpm	 = filter_var($inp_wpm,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "inp_eff") {
				$inp_eff	 = $str_value;
				$inp_eff	 = filter_var($inp_eff,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "inp_freq") {
				$inp_freq	 = $str_value;
//				$inp_freq	 = filter_var($inp_freq,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "inp_questions") {
				$inp_questions	 = $str_value;
				$inp_questions	 = filter_var($inp_questions,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "inp_words") {
				$inp_words	 = $str_value;
				$inp_words	 = filter_var($inp_words,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "inp_characters") {
				$inp_characters	 = $str_value;
				$inp_characters	 = filter_var($inp_characters,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "inp_answers") {
				$inp_answers	 = $str_value;
				$inp_answers	 = filter_var($inp_answers,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "inp_vocab") {
				$inp_vocab	 = $str_value;
				$inp_vocab	 = filter_var($inp_vocab,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "inp_infor") {
				$inp_infor	 = $str_value;
				$inp_infor	 = filter_var($inp_infor,FILTER_UNSAFE_RAW);
				$inp_infor	 = str_replace(" ","%20",$inp_infor);
			}
			if ($str_key 		== "inp_doemail") {
				$inp_doemail	 = $str_value;
				$inp_doemail	 = filter_var($inp_doemail,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "token") {
				$token	 = $str_value;
				$token	 = filter_var($token,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "inp_callsign_count") {
				$inp_callsign_count	 = $str_value;
				$inp_callsign_count	 = filter_var($inp_callsign_count,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "inp_email_text") {
				$inp_email_text	 = $str_value;
				$inp_email_text	 = filter_var($inp_email_text,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "inp_makeup") {
				$inp_makeup	 = $str_value;
				$inp_makeup	 = filter_var($inp_makeup,FILTER_UNSAFE_RAW);
			}
		}
	}

//	CHECK THIS!								//////////////////////
	if ($strPass != 3 && $validUser == "N") {
		return "YOU'RE NOT AUTHORIZED!<br />Goodby";
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

label {font:'Times New Roman', sans-serif;line-height:normal;
text-align:left;margin-right:10px;position:relative;display:block;float:left;}

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
		$TableName					= "wpw1_cwa_";
	} else {
		$extMode					= 'pd';
		$TableName					= "wpw1_cwa_";
	}

// callsign=wr7q&wpm=25&eff=6&freq=600&questions=5&words=1&characters=3	


	if ("1" == $strPass) {
		$content 		.= "<h3>$jobname</h3>
<form method='post' action='$theURL' 
name='selection_form' ENCTYPE='multipart/form-data'>
<input type='hidden' name='strpass' value='2'>
<table style='border-collapse:collapse;width:auto'>
<tr style='border-bottom: 1pt solid black;'><td style='vertical-align:top;width:250px;'>Call Sign</td>
	<td><input type='text' class= 'formInputText' name='inp_callsign' size='20' maxlength='20' required></td></tr>

<tr style='border-bottom: 1pt solid black;'>
	<td style='vertical-align:top;'>Character Speed</td>
	<td><table>
		<tr><td><input type='radio' class='formInputButton' name='inp_wpm' value='15' > 15 wpm</td>
			<td><input type='radio' class='formInputButton' name='inp_wpm' value='20' > 20 wpm</td>
			<td><input type='radio' class='formInputButton' name='inp_wpm' value='25' checked> 25 wpm</td>
			<td><input type='radio' class='formInputButton' name='inp_wpm' value='30' > 30 wpm</td></tr></table>
	</td></tr>

<tr style='border-bottom: 1pt solid black;'><td style='vertical-align:top;'>Effective Speed</td>
	<td><table>
		<tr><td><input type='radio' class='formInputButton' name='inp_eff' value='6' > 6 wpm</td>
			<td><input type='radio' class='formInputButton' name='inp_eff' value='8' > 8 wpm</td>
			<td><input type='radio' class='formInputButton' name='inp_eff' value='10' > 10 wpm</td>
			<td><input type='radio' class='formInputButton' name='inp_eff' value='12' > 12 wpm</td></tr>
		<tr><td><input type='radio' class='formInputButton' name='inp_eff' value='15' > 15 wpm</td>
			<td><input type='radio' class='formInputButton' name='inp_eff' value='18' > 18 wpm</td>
			<td><input type='radio' class='formInputButton' name='inp_eff' value='20' > 20 wpm</td>
			<td><input type='radio' class='formInputButton' name='inp_eff' value='22' > 22 wpm</td></tr>
		<tr><td><input type='radio' class='formInputButton' name='inp_eff' value='25' > 25 wpm</td>
			<td><input type='radio' class='formInputButton' name='inp_eff' value='27' > 27 wpm</td>
			<td><input type='radio' class='formInputButton' name='inp_eff' value='30'>  30 wpm</td>
			<td><input type='radio' class='formInputButton' name='inp_eff' value='35'>  35 wpm</td></tr>
		<tr><td><input type='radio' class='formInputButton' name='inp_eff' value='40'>  40 wpm</td>
			<td></td>
			<td></td>
			<td></td></tr></table>
	</td></tr>

<tr style='border-bottom: 1pt solid black;'>
	<td style='vertical-align:top;'>Frequency</td>
	<td><table>
		<tr><td><input type='checkbox' class='formInputButton' name='inp_freq[]' value='450' > 450 Hz</td>
			<td><input type='checkbox' class='formInputButton' name='inp_freq[]' value='500' > 500 Hz</td>
			<td><input type='checkbox' class='formInputButton' name='inp_freq[]' value='550' > 550 Hz</td>
			<td><input type='checkbox' class='formInputButton' name='inp_freq[]' value='600' checked> 600 Hz</td></tr>
		<tr><td><input type='checkbox' class='formInputButton' name='inp_freq[]' value='650' > 650 Hz</td>
			<td><input type='checkbox' class='formInputButton' name='inp_freq[]' value='700' > 700 Hz</td>
			<td></td>
			<td></td></tr></table>
	</td></tr>

<tr style='border-bottom: 1pt solid black;'>
	<td style='vertical-align:top;'>Questions</td>
	<td><table>
		<tr><td><input type='radio' class='formInputButton' name='inp_questions' value='3' > 3 questions</td>
			<td><input type='radio' class='formInputButton' name='inp_questions' value='5' checked> 5 questions</td>
			<td><input type='radio' class='formInputButton' name='inp_questions' value='7' > 7 questions</td>
			<td><input type='radio' class='formInputButton' name='inp_questions' value='8' > 8 questions</td></tr>
		</table>
	</td></tr>

<tr style='border-bottom: 1pt solid black;'>
	<td style='vertical-align:top;'>Words per question</td>
	<td><table>
		<tr><td><input type='radio' class='formInputButton' name='inp_words' value='1' > 1 word</td>
			<td><input type='radio' class='formInputButton' name='inp_words' value='2' checked> 2 words</td>
			<td><input type='radio' class='formInputButton' name='inp_words' value='3' > 3 words</td>
			<td><input type='radio' class='formInputButton' name='inp_words' value='4' > 4 words</td></tr>
			</table>
	</td></tr>

<tr style='border-bottom: 1pt solid black;'>
	<td style='vertical-align:top;'>Max characters per word</td>
	<td><table>
		<tr><td><input type='radio' class='formInputButton' name='inp_characters' value='3' checked> up to 3 characters</td>
			<td><input type='radio' class='formInputButton' name='inp_characters' value='4' > up to 4 characters</td>
			<td><input type='radio' class='formInputButton' name='inp_characters' value='5' > up to 5 characters</td>
			<td></td></tr></table>
	</td></tr>

<tr style='border-bottom: 1pt solid black;'>
	<td style='vertical-align:top;'>Callsigns to be included</td>
	<td><table>
		<tr><td>How Many<br />
				<input type='radio' class='formInputButton' name='inp_callsign_count' value='1'> One<br />
				<input type='radio' class='formInputButton' name='inp_callsign_count' value='2'> Two<br />
				<input type='radio' class='formInputButton' name='inp_callsign_count' value='3'> Three<br />
				<input type='radio' class='formInputButton' name='inp_callsign_count' value='4'> Four<br />
				<input type='radio' class='formInputButton' name='inp_callsign_count' value='5'> Five<br />
				<input type='radio' class='formInputButton' name='inp_callsign_count' value='6'> Six<br /></td>
			<td>Callsign Makeup<br />
				<input type='radio' class='formInputButton' name='inp_makeup' value='(3-4)'> 3-4 characters<br />
				<input type='radio' class='formInputButton' name='inp_makeup' value='(3-5)'> 3-5 characters<br />
				<input type='radio' class='formInputButton' name='inp_makeup' value='(3-6)'> 3-6 characters</td>
			</table></td></tr>

<tr style='border-bottom: 1pt solid black;'>
	<td style='vertical-align:top;'>How many multiple choice answers</td>
	<td><table>
		<tr><td><input type='radio' class='formInputButton' name='inp_answers' value='4' > 4 answers</td>
			<td><input type='radio' class='formInputButton' name='inp_answers' value='5' checked> 5 answers</td>
			<td><input type='radio' class='formInputButton' name='inp_answers' value='7' > 7 answers</td>
			<td><input type='radio' class='formInputButton' name='inp_answers' value='8' > 8 answers</td></tr>
		<tr><td></table>
	</td></tr>

<tr style='border-bottom: 1pt solid black;'>
	<td style='vertical-align:top;'>Which Vocabulary</td>
	<td><table>
		<tr><td><input type='radio' class='formInputButton' name='inp_vocab' value='threek' checked> 3000 Words</td>
			<td><input type='radio' class='formInputButton' name='inp_vocab' value='original' > 900 Words</td>
			<td></td>
			<td></td></tr></table>
	</td></tr>

<tr style='border-bottom: 1pt solid black;'>
	<td style='vertical-align:top;'>Identification Info</td>
	<td><input type='text' class='formInputText' name='inp_infor' size='50' maxlength='50' value='Requested Assessment'>
	</td></tr>

<tr style='border-bottom: 1pt solid black;'>
	<td style='vertical-align:top;'>Email text to be sent to the person performing the assessment (modify as needed)</td>
	<td><textarea class='formInputText' cols='50' rows='5' name='inp_email_text'>Because you previously withdrew from a ??? Level 
class before completing the class and have requested to be enrolled in a ??? level class in the upcoming semester, CW Academy would 
like you to take a Morse Code Proficiency Assessment. The results will help to indicate whether or not you meet the minimum 
proficiency requirements to be assigned to an Advanced level class.  
To take the assessment, please sign into your Student 
Portal (https://cwa.cwops.org/program-list/) and follow the instructions there.</textarea>
	</td></tr>

$testModeOption

<tr><td colspan='2'><input class='formInputButton' type='submit' value='Submit' /></td></tr></table>
</form></p>";

///// Pass 2 -- do the work


	} elseif ("2" == $strPass) {
		if ($doDebug) {
			echo "<br />at pass $strPass with inp_mode of $inp_mode1<br />";
		}
		
		
		$emailAddr		= '';
		$emailCallsign	= '';
		$gotEmail		= FALSE;
		
		$content		.= "<h3>$jobname</h3>";
		
		// first see if it's an advisor
		$sql		= "select call_sign, email 
						from wpw1_cwa_consolidated_advisor 
						where call_sign = '$inp_callsign' 
						order by date_created DESC 
						limit 1";
		$advisorResult	= $wpdb->get_results($sql);
		if ($advisorResult === FALSE) {
			$content	.= "Reading wpw1_cwa_consolidated_advisor failed<br />";
		} else {
			$numRows	= $wpdb->num_rows;
			if ($doDebug) {
				echo "ran $sql<br />and retrieved $numRows rows<br />";
			}
			foreach ($advisorResult as $advisorRow) {
				$emailCallsign		= $advisorRow->call_sign;
				$emailAddr			= $advisorRow->email;
				
				$gotEmail				= TRUE;
			}
		}
		if (!$gotEmail) {			// see if it is a student to get the email
			$sql			= "select call_sign, email 
								from wpw1_cwa_consolidated_student 
								where call_sign = '$inp_callsign' 
								order by date_created DESC 
								limit 1";
			$studentResult	= $wpdb->get_results($sql);
			if ($studentResult === FALSE) {
				$content	.= "Reading wpw1_cwa_consolidated_student failed<br />";
			} else {
				$numRows	=$wpdb->num_rows;
				if ($doDebug) {
					echo "ran $sql<br />and retrieved $numRows rows<br />";
				}
				if ($numRows > 0) {
					foreach($studentResult as $studentRow) {
						$emailCallsign		= $studentRow->call_sign;
						$emailAddr			= $studentRow->email;
						
						$gotEmail				= TRUE;
					}
				}
			}
		}
		
		if ($gotEmail) {
		
		
			// handle the freq array
			$freq		= "";
			$firstTime	= TRUE;
			foreach($inp_freq as $thisValue) {
				if ($firstTime) {
					$firstTime = FALSE;
					$freq 	= $thisValue;
				} else {
					$freq	= "$freq,$thisValue";
				}
			}
	
			// put together the callsigns parameter
			if ($inp_callsign_count > 0) {
				$csparam	= $inp_callsign_count . $inp_makeup;
			} else {
				$csparam	= '';
			}

							
			if ($doDebug) {
				echo "Parameters:<br />
						Call sign: $inp_callsign<br />
						WPM: $inp_wpm<br />
						Effective: $inp_eff<br />
						Frequency: $freq<br />
						Questions: $inp_questions<br />
						Words: $inp_words<br />
						Characters: $inp_characters<br />
						Callsigns: $csparam<br />
						Answers: $inp_answers<br />
						Vocabulary: $inp_vocab<br />
						Identification Info: $inp_infor</p>";
				
			}
			$emailContent	= "To: $emailCallsign
							   <p>$inp_email_text</p>";
	
			$theSubject		= "CW Academy Morse Code Proficiency Assessment Request";
			$mailResult		= emailFromCWA_v2(array('theRecipient'=>$emailAddr,
														'theSubject'=>$theSubject,
														'theContent'=>$emailContent,
														'theCc'=>'',
														'mailCode'=>11,
														'jobname'=>$jobname,
														'increment'=>0,
														'testMode'=>$testMode,
														'doDebug'=>$doDebug));
			if ($mailResult === FALSE) {
				$content	.= "Email failed to send to $emailAddr<br />";
			} else {
				$content	.= "Email sent to $emailAddr<br />";

				$token		= mt_rand();
				$myStr		= "$theURL?strpass=3&inp_callsign=$inp_callsign&token=$token";
				$returnurl	= urlencode($myStr);
				$url 		= "<a href='https://cw-assessment.vercel.app?mode=specific&callsign=$inp_callsign&cpm=$inp_wpm&eff=$inp_eff&freq=$freq&questions=$inp_questions&words=$inp_words&characters=$inp_characters&callsigns=$csparam&answers=$inp_answers&token=$token&vocab=$inp_vocab&infor=$inp_infor&returnurl=$returnurl' target='_blank'>Perform Assessment</a>";

				
				$effective_date		 	= date('Y-m-d H:i:s');
				$closeStr				= strtotime("+5 days");
				$close_date				= date('Y-m-d H:i:s', $closeStr);
				$email_text				= "<p></p>";
				$reminder_text			= "<p><b>Morse Code Proficiency Assessment Request:</b> CW Academy 
requests that you do a Morse code proficiency assessment. When you click on the following link, 
the proficiency assessment will start. The assessment will consist of $inp_questions questions. The Morse 
code will be sent at a speed of $inp_wpm words per minute with Farnsworth spacing of $inp_eff wpm effective 
speed. Upon completion the results of the assessment will be displayed and made available to CW Academy. 
To start the assessment, please click $url. Thanks!<br />CW Academy</p>";
				$inputParams		= array("effective_date|$effective_date|s",
											"close_date|$close_date|s",
											"resolved_date||s",
											"send_reminder|N|s",
											"send_once|Y|s",
											"call_sign|$inp_callsign|s",
											"role||s",
											"email_text|$email_text|s",
											"reminder_text|$reminder_text|s",
											"resolved|N|s",
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
				
				$content			.= "<p>Click <a href='$theURL'>Do It Again</a> to send another request</p>";
			}
		} else {
			if ($doDebug) {
				echo "no email address found for callsign $inp_callsign<br />";
			}
			$content		.= "No email address found for callsign $inp_callsign<br />";
		}


	} elseif ("3" == $strPass) {
		if ($doDebug) {
			echo "<br />arrived at pass3 with <br />
					inp_callsign: $inp_callsign<br />
					token: $token<br />";
		}
		
		$doProceed	= TRUE;
		
		$content	.= "<h3>$jobname</h3>
						<h4>Database Info</h4>";
		$bestResultBeginner		= 0;
		$didBeginner			= FALSE;
		$bestResultFundamental	= 0;
		$didFundamental			= FALSE;
		$bestResultIntermediate	= 0;
		$didIntermediate		= FALSE;
		$bestResultAdvanced		= 0;
		$didAdvanced			= FALSE;
		$retVal			= displayAssessment('',$token,$doDebug);
		if ($retVal[0] === FALSE) {
			if ($doDebug) {
				echo "displayAssessment returned FALSE. Called with $inp_callsign, $token<br />";
			}
			$content	.= "No data to display.<br />Reason: $retVal[1]";
		} else {
			$content	.= $retVal[1];
			$myArray	= explode("&",$retVal[2]);
			foreach($myArray as $thisValue) {
				$myArray1	= explode("=",$thisValue);
				$thisKey	= $myArray1[0];
				$thisData	= $myArray1[1];
				$$thisKey	= $thisData;
				if ($doDebug) {
					echo "$thisKey = $thisValue<br />";
				}
			}
			resolve_reminder($inp_callsign,$token,$testMode,$doDebug);	

			// let admin know the assessment is completerd			
			$effective_date		 	= date('Y-m-d H:i:s');
			$closeStr				= strtotime("+5 days");
			$close_date				= date('Y-m-d H:i:s', $closeStr);
			$token					= mt_rand();
			$email_text				= "<p></p>";
			$reminder_text			= "<p><b>Requested Morse Code Assessment Completed:</b> Student 
$inp_callsign has completed the requested Morse code proficiency assessment. The results 
are available <a href='$siteURL/cwa-view-a-student-cw-assessment-v2/?inp_advisor$userName&inp_callsign=$inp_callsign&token=$token&strpass=2' target='_blank'>HERE</a></p>";
			$inputParams		= array("effective_date|$effective_date|s",
										"close_date|$close_date|s",
										"resolved_date||s",
										"send_reminder|N|s",
										"send_once|Y|s",
										"call_sign||s",
										"role|administrator|s",
										"email_text|$email_text|s",
										"reminder_text|$reminder_text|s",
										"resolved|N|s",
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
			
			$content		.= "<p>You have completed the Morse Code Proficiency 
								assessment.<br />";
		}
	}
	$thisTime 		= date('Y-m-d H:i:s');
	$content 		.= "<br /><br /><p>Prepared at $thisTime</p>";
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
add_shortcode ('performAssessment', 'performAssessment_func');
