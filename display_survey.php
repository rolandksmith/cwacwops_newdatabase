function display_survey_func() {

	global $wpdb;

	$doDebug						= FALSE;
	$testMode						= FALSE;
	$context = CWA_Context::getInstance();
	$validUser 						= $context->validUser;

	$versionNumber				 	= "1";
	if ($doDebug) {
		echo "Initialization Array:<br /><pre>";
		print_r($context->toArray());
		echo "</pre><br />";
	}
	$userName			= $context->userName;
	$currentTimestamp	= $context->currentTimestamp;
	$validTestmode		= $context->validTestmode;
	$siteURL			= $context->siteurl;
	$userEmail			= $context->userEmail;
	$userDisplayName	= $context->userDisplayName;
	$userRole			= $context->userRole;
	$inp_survey_id		= '';
	$token				= '';
	
//	CHECK THIS!								//////////////////////
	if ($username='') {
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
	$theURL						= "$siteURL/cwa-display-survey/";
	$jobname					= "Display Survey V$versionNumber";
	$token						= '';

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
			if ($str_key 		== "token") {
				$token	 = $str_value;
				$token	 = filter_var($token,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "questionList") {
				$questionList	 = $str_value;
				$questionList	 = filter_var($questionList,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "inp_survey_id") {
				$inp_survey_id	 = $str_value;
				$inp_survey_id	 = filter_var($inp_survey_id,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "inp_callsign") {
				$inp_callsign	 = $str_value;
				$inp_callsign	 = filter_var($inp_callsign,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "inp_selected_survey") {
				$inp_selected_survey	 = $str_value;
				$inp_selected_survey	 = filter_var($inp_selected_survey,FILTER_UNSAFE_RAW);
				$thisArray				= explode("|",$inp_selected_survey);
				$inp_survey_id			= $thisArray[0];
				$inp_survey_name		= $thisArray[1];
			}
			if (preg_match('/_answer$/',$str_key)) {
				$thisValue	= $str_value;
				${$str_key}	= filter_var($thisValue,FILTER_UNSAFE_RAW);
				echo "set $str_key to ${$str_key}<br />";
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

	if ($testMode) {
		$content	.= "<p><strong>Operating in Test Mode.</strong></p>";
		if ($doDebug) {
			echo "<p><strong>Operating in Test Mode.</strong></p>";
		}
		$extMode					= 'tm';
		$surveysTableName			= "wpw1_cwa_survey_surveys2";
		$surveyContentTableName		= "wpw1_cwa_survey_content2";
		$surveyResponseTableName	= 'wpw1_cwa_survey_response2';
		$userMasterTableName		= 'wpw1_cwa_user_master2';
	} else {
		$extMode					= 'pd';
		$surveysTableName			= "wpw1_cwa_survey_surveys";
		$surveyContentTableName		= "wpw1_cwa_survey_content";
		$surveyResponseTableName	= 'wpw1_cwa_survey_response';
		$userMasterTableName		= 'wpw1_cwa_user_master';
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
					
					$surveyRadioList		.= "<input type='radio' class='formInputButton' name='inp_selected_survey' value='$survey_record_id|$survey_name' required>$survey_name<br />";
				}
				
				$content		.= "<h3>$jobname</h3>
									<p>Select from the list of surveys below</p>
									<form method='post' action='$theURL' 
									name='selection_form' ENCTYPE='multipart/form-data'>
									<input type='hidden' name='strpass' value='2'>
									<input type='hidden' name='inp_callsign' value='$userName'>
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
			echo "<br />at pass2 with id $inp_survey_id<br />";
		}
		
		$content		.= "<h3>$jobname</h3>";

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
		$content		.= "<h4>$survey_name</h4>";
		
		// get the contents of the survey and display
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
				$questionList	= "";
				$content	.= "<h5>Please Complete the Following Questionnaire</h5>
									<form method='post' action='$theURL' 
									name='selection_form' ENCTYPE='multipart/form-data'>
									<input type='hidden' name='strpass' value='3'>
									<input type='hidden' name='inp_survey_id' value='$inp_survey_id'>
									<input type='hidden' name='inp_callsign' value='$inp_callsign'>
									<input type='hidden' name='token' value='$token'>
									<table style='border-collapse:collapse;'>";
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
					
					switch($content_answer_format) {
						case "Block":
							$content		.= "<tr><td>$content_text</td></tr>";
							break;
						case "YN":
							$myStr			= "inp_" . $content_seq . "_answer";
							$questionList	.= "$myStr,";
							$thisRequired	= '';
							if ($content_answer_required == 'Y') {
								$thisRequired	= 'required';
							}
							$content		.= "<tr><td>$content_text<br />
														<input type='radio' class='formInputButton' name='$myStr' value='Yes' $thisRequired >Yes<br />
														<input type='radio' class='formInputButton' name='$myStr' value='No' $thisRequired >No<br />
														<hr></td></tr>";
							break;
						case "shortText":
							$myStr			= "inp_" . $content_seq . "_answer";
							$questionList	.= "$myStr,";
							$thisRequired	= '';
							if ($content_answer_required == 'Y') {
								$thisRequired	= 'required';
							}
							$content		.= "<tr><td>$content_text<br />
														<input type='text' class='formInputText' name='$myStr' size='50' maxlength='100' $thisRequired><br />
														<hr></td></tr>";						
							break;
						case "longText":
							$myStr			= "inp_" . $content_seq . "_answer";
							$questionList	.= "$myStr,";
							$thisRequired	= '';
							if ($content_answer_required == 'Y') {
								$thisRequired	= 'required';
							}
							$content		.= "<tr><td>$content_text<br />
														<textarea class='formInputText' name='$myStr' cols='50' rows='5' $thisRequired></textarea><br />
														<hr></td></tr>";						
							break;
						case "scale":
							$myStr			= "inp_" . $content_seq . "_answer";
							$questionList	.= "$myStr,";
							$thisRequired	= '';
							if ($content_answer_required == 'Y') {
								$thisRequired	= 'required';
							}
							$myArray		= explode(",",$content_answer_params);
							$myInt			= count($myArray);
							$content		.= "<tr><td>$content_text<br />";
							$content		.= "<table><tr>";
							foreach($myArray as $thisStr) {
								$content	.= "<td><input type='radio' class='formInputButton' name='$myStr' value='$thisStr' $thisRequired >$thisStr</td>";
							}
							$content		.= "</tr></table><br>
												<hr></td></tr>";
							break;
						case "multimulti":
							$myStr			= "inp_" . $content_seq . "_answer";
							$questionList	.= "$myStr,";
							$content		.= "<tr><td>$content_text<br />
														This is a mulitmulti answer<br />
														<hr></td></tr>";
							break;
						case "multisingle":
							$myStr			= "inp_" . $content_seq . "_answer";
							$questionList	.= "$myStr,";
							$content		.= "<tr><td>$content_text<br />
														This is a multisingle answer<br />
														<hr></td></tr>";
							break;
						default:
							if ($doDebug) {
								echo "invalid format $content_answer_format<br />";
							}
						
					}
				}
				$content	.= "<tr><td><table>
								$testModeOption
								</table></td></tr>
								<tr><td colspan='2'><input type='hidden' name='questionList' value='$questionList'>
													<input class='formInputButton' type='submit' value='Submit' /></td></tr></table>
								</form></p>";
			} else {
				$content	.= "<p>No survey content information found</p>";
			}
		}
	} elseif ("3" == $strPass) {
		if ($doDebug) {
			echo "<br />at pass3<br />";
		}
		
		$content		.= "<h3>$jobname</h3>";
		
		// get the survey info based on inp_survey_id
		if ($doDebug) {
			echo "getting the survey info from $surveysTableName<br />";
		}
		$surveySQL		= "select * from $surveysTableName 
							where survey_record_id = $inp_survey_id";
		$surveyResult	= $wpdb->get_results($surveySQL);
		if ($surveyResult === FALSE) {
			handleWPDBError($jobname,$doDebug,"pass 3 getting survey data");
		} else {
			$numSRows	= $wpdb->num_rows;
			if ($doDebug) {
				echo "Ran $surveySQL<br />and retrieved $numSRows rows<br />";
			}
			if ($numSRows > 0) {
				$surveyRadioList				= "";
				foreach($surveyResult as $surveyResultRow) {
					$survey_record_id			= $surveyResultRow->survey_record_id;
					$survey_owner				= $surveyResultRow->survey_owner;
					$survey_name				= $surveyResultRow->survey_name;
					$survey_action_log			= $surveyResultRow->survey_action_log;
					$survey_date_created		= $surveyResultRow->survey_date_created;
					$survey_date_updated		= $surveyResultRow->survey_date_updated;
					
					$response_survey_owner		= $survey_owner;
					$response_survey_id			= $survey_record_id;
					$response_survey_name		= $survey_name;
					$response_survey_date		= date('Y-m-d H:i:s');
					$response_survey_who		= $inp_callsign;

					$dataArray					= array();
					$updateParams				= array();
					$updateformat				= array();
					
					if ($questionList != '') {
						$myArray				= explode(",",$questionList);
						foreach($myArray as $thisQuestion) {
							if ($thisQuestion != '') {
								if ($doDebug) {
									echo "processing $thisQuestion<br />
										  which has a value of ${$thisQuestion}<br />";
								}
								if (isset(${$thisQuestion})) {
									$dataArray[$thisQuestion]	= ${$thisQuestion};
								}
							}
						}
					}				
					$response_survey_data		= json_encode($dataArray);
					if ($doDebug) {
						echo "have json encoded the result. Ready to store the results<br />";
					}
					
					$content		.= "<h4>$survey_name</h4>
										Response Survey Owner: $response_survey_owner<br />
										Response Survey Record ID: $response_survey_id<br />
										Response Survey Name: $response_survey_name<br />
										Response Survey Data: $response_survey_data<br />
										Response Survey Date: $response_survey_date<br />
										Response Survey Who: $inp_callsign<br />";
										
					$updateParams	= array('response_survey_owner'=>$response_survey_owner, 
										    'response_survey_id'=>$response_survey_id, 
										    'response_survey_name'=>$response_survey_name, 
										    'response_survey_data'=>$response_survey_data, 
										    'response_survey_who'=>$inp_callsign, 
										    'response_survey_date'=>$response_survey_date);
					$updateFormat	= array('%s','%d','%s','%s','%s','%s');
					$insertResult	= $wpdb->insert($surveyResponseTableName,
													$updateParams, 
													$updateFormat);
					if ($insertResult === FALSE) {
						handleWPDBError($jobname,$doDebug,"pass3 inserting response");
						if ($doDebug) {
							echo "inserting the survey results failed<br />";
						}
					} else {
						$content	.= "<p>Response to the questionnair has been recorded</p>";
						if ($doDebug) {
							echo "response recorded<br />";
						}
						if ($token != '') {
							$resolveResult	= resolve_reminder($inp_callsign,$token,$testMode,$doDebug);
							if ($resolveResult === FALSE) {
								$last_error	= $wpdb->last_error;
								sendErrorEmail("$jobname pass3 attempt to resolve reminder failed<br />Error: $last_error");
								if (doDebug) {
									echo "resolving the reminder failed<br />";
								}
							} else {
								if ($doDebug) {
									echo "token resolved<br />";
								}
								// send email to owner
								$ownerSQL		= "select * from $userMasterTableName 
													where user_call_sign = $survey_owner";
								$sqlResult		= $wpdb->get_results($ownerSQL);
								if ($sqlResult === FALSE) {
									handleWPDBError($jobname,$doDebug."getting survey owner email");
								} else {
									$numRows	= $wpdb->num_rows;
									if ($doDebug) {
										echo "ran $sql<br />and retrieved $numRows rows<br />";
									}
									if ($numRows > 0) {
										foreach($sqlResult as $sqlRow) {
											$user_id				= $sqlRow->user_ID;
											$user_call_sign			= $sqlRow->user_call_sign;
											$user_first_name		= $sqlRow->user_first_name;
											$user_last_name			= $sqlRow->user_last_name;
											$user_email				= $sqlRow->user_email;
											$user_ph_code			= $sqlRow->user_ph_code;
											$user_phone				= $sqlRow->user_phone;
											$user_city				= $sqlRow->user_city;
											$user_state				= $sqlRow->user_state;
											$user_zip_code			= $sqlRow->user_zip_code;
											$user_country_code		= $sqlRow->user_country_code;
											$user_country			= $sqlRow->user_country;
											$user_whatsapp			= $sqlRow->user_whatsapp;
											$user_telegram			= $sqlRow->user_telegram;
											$user_signal			= $sqlRow->user_signal;
											$user_messenger			= $sqlRow->user_messenger;
											$user_action_log		= $sqlRow->user_action_log;
											$user_timezone_id		= $sqlRow->user_timezone_id;
											$user_languages			= $sqlRow->user_languages;
											$user_survey_score		= $sqlRow->user_survey_score;
											$user_is_admin			= $sqlRow->user_is_admin;
											$user_role				= $sqlRow->user_role;
											$user_prev_callsign		= $sqlRow->user_prev_callsign;
											$user_date_created		= $sqlRow->user_date_created;
											$user_date_updated		= $sqlRow->user_date_updated;
											
											// format and send email to survey owner
											$theSubject			= "CW Academy -- Questionnaire Completed";
											$theContent			= "To: $user_last_name, $user_first_name ($user_call_sign):<br />
$inp_callsign has completed the $response_survey_name questionnaire.";											

											if ($testMode) {
												$theSubject	= "TESTMODE $theSubject";
												$myTo		= "rolandksmith@gmail.com";
												$mailCode	= '2';
											} else {
												$myTo		= $user_email;
												$mailCode	= '10';
											}
											$mailResult		= emailFromCWA_v2(array('theRecipient'=>$myTo,
																						'theSubject'=>$theSubject,
																						'theContent'=>$theContent,
																						'theCc'=>'',
																						'theAttachment'=>'',
																						'mailCode'=>$mailCode,
																						'jobname'=>$jobname,
																						'increment'=>0,
																						'testMode'=>$testMode,
																						'doDebug'=>$doDebug));
											if ($mailResult === FALSE) {
												if ($doDebug) {
													echo "sending the email to $myTo failed<br />";
													sendErrorEmail("$jobname sending email to $myTo failed");
												}
											} else {
												if ($doDebug) {
													echo "email sent<br />";
												}
											}
										}
									}
								}
							}
						}
					}
				}		
				
			} else {
				$content			.= "<h3>$jobname</h3>
										<p>No responses returned</p>";
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
add_shortcode ('display_survey', 'display_survey_func');

