function display_survey_results_func() {

	global $wpdb, $jobname, $doDebug, $testMode;

	$doDebug						= TRUE;
	$testMode						= FALSE;
	$initializationArray 			= data_initialization_func();
	$validUser 						= $initializationArray['validUser'];

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

	if (!in_array($userName,$validTestmode) && $doDebug) {	// turn off doDebug if not a testmode user
		$doDebug			= FALSE;
		$testMode			= FALSE;
	}

//	ini_set('memory_limit','256M');
//	ini_set('max_execution_time',0);
//	set_time_limit(0);

//	if ($doDebug) {
		ini_set('display_errors','1');
		error_reporting(E_ALL);	
		$wpdb->show_errors();
//	} else {
//		$wpdb->hide_errors();
//	}

/// get the time that the process started
	$startingMicroTime			= microtime(TRUE);

	$strPass					= "1";
	$theURL						= "$siteURL/cwa-display-survey-results/";
	$jobname					= "Display Survey Results V$versionNumber";

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
			if ($str_key 		== "inp_survey_id") {
				$inp_survey_id	 = $str_value;
				$inp_survey_id	 = filter_var($inp_survey_id,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "inp_selected_survey") {
				$inp_selected_survey	 = $str_value;
				$inp_selected_survey	 = filter_var($inp_selected_survey,FILTER_UNSAFE_RAW);
				$thisArray				= explode("|",$inp_selected_survey);
				$inp_survey_id			= $thisArray[0];
				$inp_survey_name		= $thisArray[1];
			}
			if ($str_key 		== "inp_format") {
				$inp_format	 = $str_value;
				$inp_format	 = filter_var($inp_format,FILTER_UNSAFE_RAW);
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
		$surveysTableName			= "wpw1_cwa_survey_surveys2";
		$surveyContentTableName		= "wpw1_cwa_survey_content2";
		$surveyResponseTableName	= 'wpw1_cwa_survey_response2';
	} else {
		$extMode					= 'pd';
		$surveysTableName			= "wpw1_cwa_survey_surveys";
		$surveyContentTableName		= "wpw1_cwa_survey_content";
		$surveyResponseTableName	= 'wpw1_cwa_survey_response';
	}


	function getResponseName($userName) {
		global $wpdb, $jobname, $doDebug, $testMode;
		
		$userMasterSQL		= "select * from wpw1_cwa_user_master 
								where user_call_sign = '$userName'";
		$userMasterResult	= $wpdb->get_results($userMasterSQL);
		if ($userMasterResult === FALSE) {
			handleWPDBError($jobname,$doDebug,"in function getResponseName");
		} else {
			$numURows		= $wpdb->num_rows;
			if ($doDebug) {
				echo "ran $userMasterSQL<br />and retrieved $numURows rows<br />";
				$thisName	= "Unknown";
			}
			if ($numURows > 0) {
				foreach($userMasterResult as $userMasterResultRow) {
					$user_first_name		= $userMasterResultRow->user_first_name;
					$user_last_name			= $userMasterResultRow->user_last_name;
					
					$thisName				= "$user_last_name, $user_first_name";
				}
				
			}
		}
		return $thisName;
	}


	if ("1" == $strPass) {
		if ($doDebug) {
			echo "<br />at pass1<br />";
		}
		
		// get the list of surveys
		$surveySQL		= "select * from $surveysTableName 
							where survey_owner='$userName'";
		$surveyResult	= $wpdb->get_results($surveySQL);
		if ($surveyResult === FALSE) {
			handleWPDBError($jobname,$doDebug,"pass 1 getting list of surveys");
		} else {
			$numSRows	= $wpdb->num_rows;
			if ($doDebug) {
				echo "Ran $surveySQL<br />and retrieved $numSRows rows<br />";
			}
			if ($numSRows > 0) {
				$surveyRadioList			= "";
				foreach($surveyResult as $surveyResultRow) {
					$survey_record_id		= $surveyResultRow->survey_record_id;
					$survey_owner			= $surveyResultRow->survey_owner;
					$survey_name			= $surveyResultRow->survey_name;
					$survey_action_log		= $surveyResultRow->survey_action_log;
					$survey_date_created	= $surveyResultRow->survey_date_created;
					$survey_date_updated	= $surveyResultRow->survey_date_updated;
					
					$surveyRadioList		.= "<input type='radio' class='formInputButton' name='inp_survey_id' value='$survey_record_id' required>$survey_name<br />";
				}
				
				$content		.= "<h3>$jobname</h3>
									<p>Select from the list of surveys below</p>
									<form method='post' action='$theURL' 
									name='selection_form' ENCTYPE='multipart/form-data'>
									<input type='hidden' name='strpass' value='2'>
									<input type='hidden' name='inp_format' value='student'>
									<table style='border-collapse:collapse;'>
									<tr><td style='vertical-align:top;'>Available Surveys</td>
										<td>$surveyRadioList</td></tr>
									$testModeOption
									<tr><td colspan='2'><input class='formInputButton' type='submit' value='Submit' /></td></tr></table>
									</form></p>";
			}
		}

///// Pass 2 -- do the work


	} elseif ("2" == $strPass) {
		if ($doDebug) {
			echo "<br />At pass 2 with survey $inp_survey_id<br />";
		}	
		$content	.= "<h3>$jobname</h3>";
		
		// get the survey name
		$surveySQL		= "select * from $surveysTableName 
							where survey_record_id = $inp_survey_id";
		$surveyResult	= $wpdb->get_results($surveySQL);
		if ($surveyResult === FALSE) {
			handleWPDBError($jobname,$doDebug,"pass 2 getting list of surveys");
			$survey_name		= "Unknown";
		} else {
			$numSRows	= $wpdb->num_rows;
			if ($doDebug) {
				echo "Ran $surveySQL<br />and retrieved $numSRows rows<br />";
			}
			if ($numSRows > 0) {
				$surveyRadioList			= "";
				foreach($surveyResult as $surveyResultRow) {
					$survey_record_id		= $surveyResultRow->survey_record_id;
					$survey_owner			= $surveyResultRow->survey_owner;
					$survey_name			= $surveyResultRow->survey_name;
					$survey_action_log		= $surveyResultRow->survey_action_log;
					$survey_date_created	= $surveyResultRow->survey_date_created;
					$survey_date_updated	= $surveyResultRow->survey_date_updated;
				}
			} else {
				if ($doDebug) {
					echo "no records found attempting to get survey name for id $inp_survey_id<br />";
				}
				$surveyName		= "Unknown";
			}		
		
		}		
		
		$content	.= "<h4>$survey_name</h4>
						<table>\n";
	
		// Get the survey questions and put in an array
		$surveyContent		= array();
		$contentSQL		= "select * from $surveyContentTableName 
							where content_id = $inp_survey_id 
							order by content_seq";
		$contentResult	= $wpdb->get_results($contentSQL);
		if ($contentResult === FALSE) {
			handleWPDBError($jobname,$doDebug,"pass 2 getting data from $surveyContentTableName");
		} else {
			$numCRows	= $wpdb->num_rows;
			if ($doDebug) {
				echo "ran $contentSQL<br />and retrieved $numCRows rows<br />";
			}
			if ($numCRows > 0) {
				foreach($contentResult as $contentResultRow) {
					$content_record_id			= $contentResultRow->content_record_id;
					$content_id					= $contentResultRow->content_id;
					$content_seq				= $contentResultRow->content_seq;
					$content_text				= $contentResultRow->content_text;
					$content_answer_format		= $contentResultRow->content_answer_format;
					$content_answer_params		= $contentResultRow->content_answer_params;
					$content_answer_required	= $contentResultRow->content_answer_required;
					$content_action_log			= $contentResultRow->content_action_log;
					$content_date_created		= $contentResultRow->content_date_created;
					$content_date_updated		= $contentResultRow->content_date_updated;

					$surveyContent[$content_seq]['text']			= $content_text;
					$surveyContent[$content_seq]['answer_format']	= $content_answer_format;
					$surveyContent[$content_seq]['answer_params']	= $content_answer_params;
					$surveyContent[$content_seq]['answer_required']	= $content_answer_required;
				}
				if ($doDebug) {
					echo "surveyContent Array:<br /><pre>";
					print_r($surveyContent);
					echo "</pre><br />";
				}
				
				if ($inp_format == 'student') {			// display each student's response
					// get the responses
					$responseSQL		= "select * from $surveyResponseTableName 
											where response_survey_id = $content_id 
											and response_survey_name = '$survey_name' 
											order by response_survey_who";
					$responseResult		= $wpdb->get_results($responseSQL);
					if ($responseResult === FALSE) {
						handleWPDBError($jobname,$doDebug,"pass 2 getting student responses");
					} else {
						$numRRows		= $wpdb->num_rows;
						if ($doDebug) {
							echo "ran $responseSQL<br />and retrieved $numRRows rows<br />";
						}
						if ($numRRows > 0) {
							$slot						= 1;
							foreach($responseResult as $responseResultRow) {
								$response_record_id		= $responseResultRow->response_record_id;
								$response_survey_owner	= $responseResultRow->response_survey_owner;
								$response_survey_id		= $responseResultRow->response_survey_id;
								$response_survey_name	= $responseResultRow->response_survey_name;
								$response_survey_data	= $responseResultRow->response_survey_data;
								$response_survey_who	= $responseResultRow->response_survey_who;
								$response_survey_date	= $responseResultRow->response_survey_date;
								
								$nameInfo				= getResponseName($response_survey_who);
								$responseDisplay		= "<td style='width:350px;'><b>$nameInfo ($response_survey_who)</b>";
								$responseAnswers		= json_decode($response_survey_data,TRUE);
								
								foreach($surveyContent as $thisSeq => $thisAnswerSet) {
									$thisText			= $thisAnswerSet['text'];
									$thisFormat			= $thisAnswerSet['answer_format'];
									$thisParams			= $thisAnswerSet['answer_params'];
									$thisRequired		= $thisAnswerSet['answer_required'];
									
									switch ($thisFormat) {
										case "Block":
											$responseDisplay	.= "<b>($thisSeq) $thisText</b><br />\n";
											break;
										case "longText":
											$myStr				= "inp_" . $thisSeq . "_answer";
											if (isset($responseAnswers[$myStr])) {
												$thisResponse		= $responseAnswers[$myStr];
											} else {
												$thisResponse		= "no answer provided";
											}
											$responseDisplay	.= "<b>($thisSeq) $thisText</b><br />
																	$thisResponse<br /><hr><br />\n";
											break;
										case "shortText":
											$myStr				= "inp_" . $thisSeq . "_answer";
											if (isset($responseAnswers[$myStr])) {
												$thisResponse		= $responseAnswers[$myStr];
											} else {
												$thisResponse		= "no answer provided";
											}
											$responseDisplay	.= "<b>($thisSeq) $thisText</b><br />
																	$thisResponse<br /><hr><br />\n";
											break;
										case "YN":
											$myStr				= "inp_" . $thisSeq . "_answer";
											if (isset($responseAnswers[$myStr])) {
												$thisResponse		= $responseAnswers[$myStr];
											} else {
												$thisResponse		= "no answer provided";											
											}
											$responseDisplay	.= "<b>($thisSeq) $thisText</b><br />
																	$thisResponse<br />
																	<hr><br />\n";
											break;
										case "scale";
											$myStr				= "inp_" . $thisSeq . "_answer";
											if (isset($responseAnswers[$myStr])) {
												$thisResponse		= $responseAnswers[$myStr];
											} else {
												$thisResponse		= "no answer provided";											
											}
											$responseDisplay	.= "<b>($thisSeq) $thisText</b><br />
																	$thisResponse<br />
																	<hr><br />\n";
										case "multimulti":
											break;
										case "multisingle":
											break;
									}
								}
								$responseDisplay		.= "</td>\n";
								switch ($slot) {
									case 1:
										$content		.= "<tr>$responseDisplay\n";
										$slot			= 2;
										break;
									case 2:
										$content		.= "$responseDisplay\n";
										$slot			= 3;
										break;
									case 3:
										$content		.= "$responseDisplay</tr>\n
															<tr><td colspan='3'><hr></td></tr>\n";
										$slot			= 1;
										break;
								}
							}
							// finish up the display
							switch ($slot) {
								case 1:
									break;
								case 2:
									$content		.= "<td></td><td></td></tr>\n
														<tr><td colspan='3'><hr></td></tr>\n";
									break;
								case 3:
									$content		.= "<td></td></tr>\n
														<tr><td colspan='3'><hr></td></tr>\n";
									break;
							}
							$content	.= "</table>\n";
						}
					}
				} else {
					$content		.= "<p>Figuring out how to make this option work</p>";
				}
			} else {
				$content	.= "<p>No questions found for this survey</p>";
			}
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
add_shortcode ('display_survey_results', 'display_survey_results_func');
