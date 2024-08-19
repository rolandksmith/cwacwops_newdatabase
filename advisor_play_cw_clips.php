function advisor_play_cw_clips_func() {

/*

	Modified 15Apr23 by Roland to correct action log handling
	Modified 12Jul23 by Roland to use consolidated tables

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
//	if ($validUser == "N") {
//		return "YOU'RE NOT AUTHORIZED!<br />Goodby";
//	}

//	ini_set('memory_limit','256M');
//	ini_set('max_execution_time',0);
//	set_time_limit(0);

	if ($doDebug) {
		ini_set('display_errors','1');
		error_reporting(E_ALL);	
	}

/// get the time that the process started
	$startingMicroTime			= microtime(TRUE);

	$strPass					= "1";
	$theURL						= "$siteURL/cwa-advisor-play-cw-audio-clips/";
	$inp_semester				= '';
	$inp_rsave					= '';
	$jobname					= "Advisor Play CW Audio Clips";
	$convertArray				= array('BegBas'=>'Beginner into Fundamental',
										'BasInt'=>'Fundamental into Intermediate',
										'IntAdv'=>'Intermediate into Advanced',
										'Adv'=>'Out of Advanced');

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
				$inp_callsign	 = strtoupper(filter_var($inp_callsign,FILTER_UNSAFE_RAW));
//				echo "inp_callsign: $inp_callsign<br />";
			}
			if ($str_key 		== "inp_email") {
				$inp_email	 = trim($str_value);
//				$inp_email	 = filter_var($inp_email,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "inp_phone") {
				$inp_phone	 = $str_value;
				$inp_phone	 = filter_var($inp_phone,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "inp_level") {
				$inp_level	 = $str_value;
				$inp_level	 = filter_var($inp_level,FILTER_UNSAFE_RAW);
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
		$advisorTableName		= "wpw1_cwa_consolidated_advisor2";
	} else {
		$extMode					= 'pd';
		$advisorTableName		= "wpw1_cwa_consolidated_advisor";
	}



	if ("1" == $strPass) {
		$content 		.= "<h3>$jobname</h3>
							<p>Enter your call sign, email address, phone number, and level. The email address and 
							phone number are used for validation purposes.</p>
							<p>The program will present you all the audio CW clips so that you can play them 
							along with the text for that clip.</p>
							<form method='post' action='$theURL' 
							name='selection_form' ENCTYPE='multipart/form-data'>
							<input type='hidden' name='strpass' value='2'>
							<table style='border-collapse:collapse;'>
							<tr><td>Advisor Call Sign</td>
								<td><input type='text' class='formInputText' name='inp_callsign' size='10' maxlength='10' autofocus></td>
							<tr><td>Advisor Email Address</td>
								<td><input class='formInputText' type='text' maxlength='50' name='inp_email' size='50'></td></tr>
							<tr><td>Advisor Phone Number:</td>
								<td><input class='formInputText' type='text' maxlength='20' name='inp_phone' size='20'></td></tr>
							<tr><td>Level</td>
								<td><input type='radio' class='formInputButton' name='inp_level' value='BegBas'>Beginner into Fundamental<br />
									<input type='radio' class='formInputButton' name='inp_level' value='BasInt'>Fundamental into Intermediate<br />
									<input type='radio' class='formInputButton' name='inp_level' value='IntAdv'>Intermediate into Advanced<br />
									<input type='radio' class='formInputButton' name='inp_level' value='Adv'>Out of Advanced<br />
							$testModeOption
							<tr><td>&nbsp;</td><td><input class='formInputButton' type='submit' value='Next' /></td></tr></table>
							</form></p>
							<p><b>Explanation:</b>
							<br /><b>During student registration for a class:</b>
							<dl><dt>Beginner into Fundamental</dt>
								<dd>Students registering for a Beginner Level class are asked to listen to an audio clip 
									in this category to determine if the student is better suited for a Fundamental 
									level class. Students registering for a Fundamental Level class are asked to 
									listen to an audio clip in this category to determine if the student is ready to 
									take the Fundamental Level class.</dd>
								<dt>Fundamental into Intermediate</dt>
								<dd>Students registering for an Intermediate Level class are asked to listen to an audio 
									clip in this category to determine if the student is ready to take the Fundamental Level 
									class. Depending on the outcome, the student may be encouraged to take the Beginner Level 
									class or to take the Intermediate evaluation.</dd>
								<dt>Intermediate to Advanced</dt>
								<dd>Students registering for an Advanced Level class are asked to listen to an audio 
									clip in this category to determine if the student is ready to take the Advanced Level 
									class. Depending on the outcome, the student may be encouraged to take the Intermediate Level 
									class.</dd>
							</dl></p>
							<p><b>During End-of-Semester Student Evaluation:</b>
							<dl><dt>Beginner into Fundamental</dt>
								<dd>Students completing the Beginner Level class are asked to listen to audio
									clips in this category, enter what the student heard, and the score is presented 
									to the student and emailed to the advisor</dd>
								<dt>Fundamental into Intermediate</dt>
								<dd>Students completing the Fundamental Level class are asked to listen to audio
									clips in this category, enter what the student heard, and the score is presented 
									to the student and emailed to the advisor</dd>
								<dt>Intermediate into Advanced</dt>
								<dd>Students completing the Intermediate Level class are asked to listen to audio
									clips in this category, enter what the student heard, and the score is presented 
									to the student and emailed to the advisor</dd>
								<dt>Out of Advanced</dt>
								<dd>Students completing the Advanced Level class are asked to listen to audio
									clips in this category, enter what the student heard, and the score is presented 
									to the student and emailed to the advisor</dd></dl></p>";
								

///// Pass 2 -- do the work


	} elseif ("2" == $strPass) {
		if ($doDebug) {
			echo "<br />$jobname arrived at pass 2<br />
				  inp_callsign: $inp_callsign<br />
				  inp_email: $inp_email<br />
				  inp_phone: $inp_phone<br />
				  inp_level: $inp_level<br />";
		}
		
		if ($inp_callsign == '' || $inp_email == '' || $inp_phone == '') {
			$content			.= "You are not authorized";
			return $content;
		}
		
		$gotAdvisor				= FALSE;
		
		// get the advisor record, if there is one
		$sql					= "select * from $advisorTableName 
									where call_sign='$inp_callsign' 
									order by date_created DESC, 
										     call_sign";
		$cwa_advisor			= $wpdb->get_results($sql);
		if ($cwa_advisor === FALSE) {
			$myError			= $wpdb->last_error;
			$myQuery			= $wpdb->last_query;
			if ($doDebug) {
				echo "Reading $advisorTableName table failed<br />
					  wpdb->last_query: $myQuery<br />
					  wpdb->last_error: $myError<br />";
			}
			$errorMsg			= "$jobname Reading $advisorTableName table failed. <p>SQL: $myQuery</p><p> Error: $myError</p>";
			sendErrorEmail($errorMsg);
		} else {
			$numARows									= $wpdb->num_rows;
			if ($doDebug) {
				$myStr			= $wpdb->last_query;
				echo "ran $myStr<br />and found $numARows rows in $advisorTableName table<br />";
			}
			if ($numARows > 0) {
				foreach ($cwa_advisor as $advisorRow) {
					$advisor_ID							= $advisorRow->advisor_id;
					$advisor_select_sequence 			= $advisorRow->select_sequence;
					$advisor_call_sign 					= strtoupper($advisorRow->call_sign);
					$advisor_first_name 				= $advisorRow->first_name;
					$advisor_last_name 					= stripslashes($advisorRow->last_name);
					$advisor_email 						= trim(strtolower($advisorRow->email));
					$advisor_phone						= $advisorRow->phone;
					$advisor_ph_code					= $advisorRow->ph_code;				// new
					$advisor_text_message 				= $advisorRow->text_message;
					$advisor_city 						= $advisorRow->city;
					$advisor_state 						= $advisorRow->state;
					$advisor_zip_code 					= $advisorRow->zip_code;
					$advisor_country 					= $advisorRow->country;
					$advisor_country_code				= $advisorRow->country_code;		// new
					$advisor_whatsapp					= $advisorRow->whatsapp_app;		// new
					$advisor_signal						= $advisorRow->signal_app;			// new
					$advisor_telegram					= $advisorRow->telegram_app;		// new
					$advisor_messenger					= $advisorRow->messenger_app;		// new
					$advisor_time_zone 					= $advisorRow->time_zone;
					$advisor_timezone_id				= $advisorRow->timezone_id;			// new
					$advisor_timezone_offset			= $advisorRow->timezone_offset;		// new
					$advisor_semester 					= $advisorRow->semester;
					$advisor_survey_score 				= $advisorRow->survey_score;
					$advisor_languages 					= $advisorRow->languages;
					$advisor_fifo_date 					= $advisorRow->fifo_date;
					$advisor_welcome_email_date 		= $advisorRow->welcome_email_date;
					$advisor_verify_email_date 			= $advisorRow->verify_email_date;
					$advisor_verify_email_number 		= $advisorRow->verify_email_number;
					$advisor_verify_response 			= strtoupper($advisorRow->verify_response);
					$advisor_action_log 				= $advisorRow->action_log;
					$advisor_class_verified 			= $advisorRow->class_verified;
					$advisor_control_code 				= $advisorRow->control_code;
					$advisor_date_created 				= $advisorRow->date_created;
					$advisor_date_updated 				= $advisorRow->date_updated;

					$advisor_last_name 					= no_magic_quotes($advisor_last_name);

					if ($advisor_survey_score != 6) {
						$gotAdvisor							= TRUE;
					}
				}
			}
		}
		if (!$gotAdvisor) {
			 $content			.= "You Are Not Authorized";
			 return $content;
		}
		$doProceed				= TRUE;
		if ($inp_email != $advisor_email) {
			$doProceed	= FALSE;
			if ($doDebug) {
				echo "email address of $inp_email does not match $advisor_email<br >";
			}
		}
		if ($inp_phone == '') {
			$doProceed	= FALSE;
			if ($doDebug) {
				echo "input phone is missing<br />";
			}
		} else {
			$advisor_last4Digits					= substr($advisor_phone,-4,4);
			$inp_last4Digits						= substr($inp_phone,-4,4);
			if ($advisor_last4Digits != $inp_last4Digits) {
				$doProceed	= FALSE;
				if ($doDebug) {
					echo "inp_phone $inp_phone last 4 digits does not match $advisor_phone<br />";
				}
			}
		}
		if (!$doProceed) {
			$content			.= "You're Not Authorized";
			return $content;
		}
		if ($doDebug) {
			echo "Everything matches. Can proceed<br />";
		}
		$thisStr			= $convertArray[$inp_level];
		$content			.= "<h3>$jobname</h3>
								<p>Here are the CW audio clips for $thisStr</p><table>";
/*
	If the media file exists:  	0: html to play the file
								1: full path clip name
								2: clip number
								3: clip contents
	
	If the media file does not exist:	0: FALSE
										1: Error message
										2: full path clip name
										3: (empty)
*/

		for ($ii=1;$ii<=20;$ii++) {
			$result			= playAudioFile($inp_level,$ii,$ii,$doDebug,'N');
			if ($result === FALSE) {
				$content	.= "<tr><td>No clip for ii=$ii</td></tr>";
			} else {
				$audioContent		= $result[0];
				$audioFileName		= $result[1];
				$audioFileNumber	= $result[2];
				$audioFileText		= $result[3];
				if ($doDebug) {
					echo "Got some content:<br /><pre>";
					print_r($result);
					echo "</pre><br />";
				}
				/// have a clip. Put it in the form
				$content				.= "<tr><td><b>Audio Clip $audioFileNumber:</b><br />
												<div><p>$audioContent</p></div>
												<button type='button' class='formInputButton' onclick=\"alert('Text: $audioFileText');\" >Show Text</button></td></tr>
												<tr><td><hr></td></tr>";
			}
		}
		$content						.= "</table>";
	
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
add_shortcode ('advisor_play_cw_clips', 'advisor_play_cw_clips_func');
