function setup_survey_func() {

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
	
//	CHECK THIS!								//////////////////////
	if ($userName  == '') {
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
	$theURL						= "$siteURL/cwa-setup-survey/";
	$jobname					= "Setup survey V$versionNumber";
	$updateSurvey				= FALSE;
	$createSurvey				= FALSE;
	$cloneSurvey				= FALSE;
	$newDate					= date('dMY H:i:s');
	$submit						= '';
	$inp_recurring				= 'N';
	$surveyRadioList			= '';
	$inp_required				= 'N';

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
			if ($str_key 			== "inp_survey_name") {
				$inp_survey_name	 = $str_value;
				$inp_survey_name	 = filter_var($inp_survey_name,FILTER_UNSAFE_RAW);
			}
			if ($str_key 			== "inp_sequence") {
				$inp_sequence	 = $str_value;
				$inp_sequence	 = filter_var($inp_sequence,FILTER_UNSAFE_RAW);
			}
			if ($str_key 			== "old_sequence") {
				$old_sequence	 = $str_value;
				$old_sequence	 = filter_var($old_sequence,FILTER_UNSAFE_RAW);
			}
			if ($str_key 			== "inp_survey_id") {
				$inp_survey_id	 = $str_value;
				$inp_survey_id	 = filter_var($inp_survey_id,FILTER_UNSAFE_RAW);
			}
			if ($str_key 			== "inpSeq") {
				$inpSeq	 = $str_value;
				$inpSeq	 = filter_var($inpSeq,FILTER_UNSAFE_RAW);
			}
			if ($str_key 			== "inpFormat") {
				$inpFormat	 = $str_value;
				$inpFormat	 = filter_var($inpFormat,FILTER_UNSAFE_RAW);
			}
			if ($str_key 			== "inpText") {
				$inpText	 = $str_value;
				$inpText	 = filter_var($inpText,FILTER_UNSAFE_RAW);
			}
			if ($str_key 			== "inpParams") {
				$inpParams	 = $str_value;
				$inpParams	 = filter_var($inpParams,FILTER_UNSAFE_RAW);
			}
			if ($str_key 			== "inp_recurring") {
				$inp_recurring	 = $str_value;
				$inp_recurring	 = filter_var($inp_recurring,FILTER_UNSAFE_RAW);
			}
			if ($str_key 			== "inp_required") {
				$inp_required	 = $str_value;
				$inp_required	 = filter_var($inp_required,FILTER_UNSAFE_RAW);
			}
			if ($str_key 				== "inp_selected_survey") {
				$inp_selected_survey	 = $str_value;
				$inp_selected_survey	 = filter_var($inp_selected_survey,FILTER_UNSAFE_RAW);
				$thisArray				= explode("|",$inp_selected_survey);
				$inp_survey_id			= intval($thisArray[0]);
				$inp_survey_name		= $thisArray[1];
//				$updateSurvey			= TRUE;
				if ($doDebug) {
					echo "set inp_survey_id = $inp_survey_id<br />
						  set inp_survey_name = $inp_survey_name<br />";
				}
			}
			if ($str_key 				== "submit") {
				$submit	 				= $str_value;
				$submit	 				= filter_var($submit,FILTER_UNSAFE_RAW);
echo "set submit to $submit<br />";
				if ($submit == 'Update Survey') {
					$updateSurvey		= TRUE;
echo "set updateSurvey to TRUE<br />";
				} elseif ($submit == 'Clone and Update') {
					$cloneSurvey		= TRUE;
echo "set cloneSurvey to TRUE<br />";
				} elseif ($submit == 'Create Survey') {
					$createSurvey		= TRUE; 
echo "set createSurvey to TRUE<br />";
				}
			}
		}
	}
	
	
	if (in_array($userName,$validTestmode)) {			// give option to run in test mode 
		$testModeOption	= "<tr><td colspan='4'><hr></td></tr>
							<tr><td>Operation Mode</td>
							<td colspan='3'><input type='radio' class='formInputButton' name='inp_mode' value='Production' checked='checked'> Production<br />
								<input type='radio' class='formInputButton' name='inp_mode' value='TESTMODE'> TESTMODE</td></tr>
							<tr><td>Verbose Debugging?</td>
								<td colspan='3'><input type='radio' class='formInputButton' name='inp_verbose' value='N' checked='checked'> Standard Output<br />
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
	} else {
		$extMode					= 'pd';
		$surveysTableName			= "wpw1_cwa_survey_surveys";
		$surveyContentTableName		= "wpw1_cwa_survey_content";
		$surveyResponseTableName	= 'wpw1_cwa_survey_response';
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
			}
			$content		.= "<h3>$jobname</h3>
								<table>
								<tr><td style='width:300px;vertical-align:top;'><b>Review and Modify an Existing Survey</b><br />
										<p>Select survey to modify from the list of surveys below
										<form method='post' action='$theURL' 
										name='modify_form' ENCTYPE='multipart/form-data'>
										<input type='hidden' name='strpass' value='2'>
										<br /><u>Available Surveys</u><br />
										$surveyRadioList<br />
										<input class='formInputButton' name='submit' type='submit' value='Update Survey' />
										</form></td>
									<td style='width:300px;vertical-align:top;'><b>Clone and Modify an Existing Survey</b><br />
										<p>Select survey to copy and modify from the list below
										and specify the new survey name
										<form method='post' action='$theURL' 
										name='modify_form' ENCTYPE='multipart/form-data'>
										<input type='hidden' name='strpass' value='2'>
										<br /><u>Available Surveys</u><br />
										$surveyRadioList<br />
										<b>New Survey Name</b><br />
										<input type='text' class='formInputText' size='50' maxlength='100' name='inp_survey_name'><br />
										<b>Is this a recurring survey?</b><br />
										<input type='radio' class='formInputText' name='inp_recurring' value='N' checked>No<br />
										<input type='radio' class='formInputText' name='inp_recurring' value='Y'>Yes<br /><br />
										<input class='formInputButton' name='submit' type='submit' value='Clone and Update' /><br />
										</form></td>
									<td style='width:300px;vertical-align:top;'><b>Create a New Survey</b><br />
										<p>Enter the new survey name
										<form method='post' action='$theURL' 
										name='modify_form' ENCTYPE='multipart/form-data'>
										<input type='hidden' name='strpass' value='2'>
										<b>New Survey Name</b><br />
										<input type='text' class='formInputText' size='50' maxlength='100' name='inp_survey_name'><br />
										<b>Is this a recurring survey?</b><br />
										<input type='radio' class='formInputText' name='inp_recurring' value='N' checked>No<br />
										<input type='radio' class='formInputText' name='inp_recurring' value='Y'>Yes<br /><br />
										<input class='formInputButton' type='submit' name='submit' value='Create Survey' />
										</form></td>
									<td style='width:300px;vertical-align:top;'><b>Delete a Survey</b><br />
										<p>Select survey to delete from the list of surveys below
										<form method='post' action='$theURL' 
										name='delete_form' ENCTYPE='multipart/form-data'>
										<input type='hidden' name='strpass' value='30'>
										<br /><u>Available Surveys</u><br />
										$surveyRadioList<br />
										<input class='formInputButton' name='submit' type='submit' value='Delete Survey' />
										</form></td></tr>
										$testModeOption
								<tr><td colspan='2'></td></tr></table>
								</form></p>";
			$strPass	= '1';
		}

	} elseif ("4" == $strPass) {
		if ($doDebug) {
			echo "<br />at pass4 ready to either insert or update a sequence<br />";
		}
		
		$strPass			= "2";
		
		if ($submit == 'Update') {
			// get the record to be updated
			$contentSQL		= "select * from $surveyContentTableName 
								where content_id = $inp_survey_id 
								and content_seq = $old_sequence ";
			$contentResult	= $wpdb->get_results($contentSQL);
			if ($contentResult === FALSE) {
				handleWPDBError($jobname,$doDebug,"pass 4 attempting to get survey question $inp_sequence for update");
				$content	.= "Trying to get the survey question for update failed<br />";
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
						
						$inp_survey_id				= $content_id;
						$updateSurvey				= TRUE;
			
						$updateParams				= array();
						$updateFormat				= array();
						$doUpdate					= FALSE;
						$logInfo					= "";
						if ($inpSeq != $content_seq) {
							$updateParams['content_seq']		= $inpSeq;
							$updateFormat[]						= '%d';
							$doUpdate							= TRUE;
							$logInfo							.= "Sequence changed from $content_seq to $inpSeq. ";
						}
						if ($inpFormat != $content_answer_format) {
							$updateParams['content_answer_format']	= $inpFormat;
							$updateFormat[]						= '%s';
							$doUpdate							= TRUE;
							$logInfo							.= "Answer format changed from $content_answer_format to $inpFormat. ";
						}
						if ($inpText != $content_text) {
							$updateParams['content_text']	= $inpText;
							$updateFormat[]						= '%s';
							$doUpdate							= TRUE;
							$logInfo							.= "Answer text changed from $content_text to $inpText. ";
						}
						if ($inpFormat == 'scale' || $inpFormat == 'multimulti' || $inpFormat == 'multisingle') {
							if ($inpParams != $content_answer_params) {
								$updateParams['content_answer_params']	= $inpParams;
								$updateFormat[]						= '%s';
								$doUpdate							= TRUE;
								$logInfo							.= "Answer params changed from $content_answer_params to $inpParams. ";
							}
						}
						if ($inp_required != $content_answer_required) {
							$updateParams['content_answer_required']	= $inp_required;
							$updateFormat[]						= '%s';
							$doUpdate							= TRUE;
							$logInfo							.= "Answer required changed from $content_answer_required to $inp_required. ";
						}
						
						if ($doUpdate) {
							if ($doDebug) {
								echo "Making these modifications: $logInfo<br />";
							}
						
							$nowDate							= date('dMy H:i:s');
							$content_action_log					.= " / $nowDate $userName $logInfo ";
							$updateParams['content_action_log']	= $content_action_log;
							$updateFormat[]						= '%s';
							
							$updateResult		= $wpdb->update($surveyContentTableName, 
																$updateParams, 
																array('content_record_id'=>$content_record_id), 
																$updateFormat, 
																array('%d'));
							if ($updateResult === FALSE) {
								handleWPDBError($jobname,$doDebug,"updating a sequence record failed");
								$content		.= "<p>Update failed</p>";
							} else {
								$content		.= "<p>Record successfully updated</p>";
							}
						} else {
							$content			.= "<p>No updates were requested</p>";
						}
					}
				} else {
					$content		.= "<p>No record found in $surveyContentTableName for id $content_record_id</p>";
				}
			}
		} elseif ($submit == "Add") {
			if ($doDebug) {
				echo "<br />at pass4 adding sequence $inp_sequence to ID $inp_survey_id<br />";
			}
			$strPass			= '';
			
			// see if the sequence is already in the form
			$recordCount		= $wpdb->get_var("select count(content_seq) from $surveyContentTableName 
												where content_id = $inp_survey_id 
												and content_seq = $inp_sequence");
			if ($recordCount == NULL || $recordCount == 0) {
				if ($doDebug) {
					echo "Sequence $inp_sequence is available to add a question<br />";
				}
				$content		.= "<h4>Enter the survey question information</h4>
									<form method='post' action='$theURL' 
									name='modify_form' ENCTYPE='multipart/form-data'>
									<input type='hidden' name='strpass' value='4B'>
									<input type='hidden' name='inp_survey_id' value='$inp_survey_id'>
									<input type='hidden' name='inp_sequence' value='$inp_sequence'>
									<table>
									<tr><td style='vertical-align:top;'><b>Question Sequence:</b> <i>Sequence in which the question will be shown</i><br />
										$inp_sequence<br />
										<b>Question Format:</b><br /><input type='radio' class='formInputButton' name='inpFormat' value='Block'>Block <i>Text displayed. No response given</i><br />
																<input type='radio' class='formInputButton' name='inpFormat' value='YN'>Yes or No </i>Can select either Yes or No for the response</i><br />
																<input type='radio' class='formInputButton' name='inpFormat' value='shortText'>Text response up to 100 characters</i><br />
																<input type='radio' class='formInputButton' name='inpFormat' value='longText'>Free form response </i>An unlimited character textual response</i><br />
																<input type='radio' class='formInputButton' name='inpFormat' value='scale'>Scale <i>Response selects one of the three to five options such as Poor,Fair,Good</i><br />
										<br /><b>Question Text:</b> <textarea class='formInputText' rows='5' cols='50' name='inpText'></textarea><br />
										<br /><b>Question Parameters</b> <i>indicate up to five parameters, one of which will be selected, separated by commas. For example: Good,Fair,Poor</i><br />
										<input type='text' class='formInputText' name='inpParams' size='20' maxlength='20'><br />
										<br /><b>Is a response required?</b><br />
										<input type='radio' class='formInputButton' name='inp_required' value='Y'>Yes<br />
										<input type='radio' class='formInputButton' name='inp_required' value='N'>No<br />
										<hr style='border: 2px solid black;'><br /><br />
										<input class='formInputButton' name='submit' type='submit' value='Add Sequence' />
										</form></td></tr></table>";
			} else {
				$content	.= "<p>Sequence $inp_sequence is NOT available to add a question</p>";
			}
		}
	}
	
	if ("4B" == $strPass) {
		if ($doDebug) {
			echo "<br />at pass4B <br />";
		}
		$nowDate			= date('dMY H:i:s');
		$updateParams		= array('content_seq'=>$inp_sequence,
									'content_id'=>$inp_survey_id,
									'content_text'=>$inpText,
									'content_answer_format'=>$inpFormat,
									'content_answer_params'=>$inpParams, 
									'content_answer_required'=>$inp_required, 
									'content_action_log'=>"$nowDate $userName sequence added to survey ");
		$updateFormat		= array('%d','%d','%s','%s','%s','%s','%s','%s');
		$insertResult		= $wpdb->insert($surveyContentTableName, 
											$updateParams,
											$updateFormat);
		if ($insertResult === FALSE) {
			handleWPDBError($jobname,$doDebug,"attempting to insert $inp_sequence to ID $inp_survey_id");
			$content		.= "<p>Adding $inp_sequence failed</p>";
		} else {
			if ($doDebug) {
				echo "insert was successful<br />";
			}
			$content		.= "<p>Sequence $inp_sequence added to survey</p>";
			$strPass		= '2';
			$updateSurvey	= TRUE;
		}
	}

///// Pass 2 -- do the work


	if ("2" == $strPass) {
		if ($doDebug) {
			echo "<br />at pass2<br />";
			echo "with submit of $submit<br />";
		}
		if ($doDebug) {
			if ($updateSurvey) {
				echo "updateSurvey is TRUE<br />";
			} else {
				echo "updateSurvey is FALSE<br />";
			}
			if ($cloneSurvey) {
				echo "cloneSurvey is TRUE<br />";
			} else {
				echo "cloneSurvey is FALSE<br />";
			}
			if ($createSurvey) {
				echo "createSurvey is TRUE<br />";
			} else {
				echo "createSurvey is FALSE<br />";
			}
		}
		$content		.= "<h3>$jobname</h3>";
		
		$haveID			= FALSE;


		if ($updateSurvey) {
			if ($doDebug) {
				echo "doing updateSurvey";
			}
			$sql			= "select * from $surveysTableName 
								where survey_record_id = $inp_survey_id";
			$surveyResult	= $wpdb->get_results($sql);
			if ($surveyResult === FALSE) {
				handleWPDBError($jobname,$doDebug,"attempting to get survey id $inp_survey_id");
				$content	.= "<p>Attempting get data for survey $inp_survey_name failed<br />";
			} else {
				$numSRows	= $wpdb->num_rows;
				if ($doDebug) {
					echo "ran $sql<br />and retrieved $numSRows rows<br />";
				}
				if ($numSRows > 0) {
					foreach($surveyResult as $surveyResultRow) {
						$survey_record_id		= $surveyResultRow->survey_record_id;
						$survey_action_log		= $surveyResultRow->survey_action_log;
						
						$haveID					= TRUE;
						if ($doDebug) {
							echo "haveID is now TRUE<br />";
						}
					}
				}
									
			}
		}
		if ($createSurvey) {
			if ($doDebug) {
				echo "<br />Creating a new survey: $inp_survey_name<br />";
			}
			if ($inp_survey_name == '') {
				$content			.= "<p>A survey name is required. Go back and enter the name</p>";
				$strPass			= '0';
			} else {
				// Insert the surveys record
				if ($inp_recurring == 'N') {
					$nowDate			= date('dMY');
					$inp_survey_name	= "$inp_survey_name $nowDate";
				}
				$insertResult		= $wpdb->insert($surveysTableName, 
													array('survey_owner'=>$userName, 
														  'survey_name'=>$inp_survey_name, 
														  'survey_recurring'=>$inp_recurring, 
														  'survey_action_log'=>"$newDate $userName survey created "), 
													array('%s','%s','%s'));
				if ($insertResult === FALSE) {
					handleWPDBError($jobname,$doDebug,"pass 15 attempting to insert new survey failed");
					$content		.= "<p>Attempting to insert survey name into $surveysTableName failed</p>";
				} else {
					$survey_record_id	= $wpdb->insert_id;
					if ($doDebug) {
						echo "inserting survey name $inp_survey_name into $surveysTableName was succesful. Insert_id: $survey_record_id<br />";
					}
					$content		.= "<p>Survey $inp_survey_name is set up.</p>";
					$haveID			= TRUE;
					$updateSurvey	= TRUE;
				}
			}
		}
		if ($cloneSurvey) {
			if ($inp_survey_name == '') {
				$content			.= "<p>A survey name is required. Go back and enter the name</p>";
				$strPass			= '0';
			} else {
				if ($doDebug) {
					echo "<br />Cloning surveyid $inp_survey_id into $inp_survey_name<br />";
				}
				// get the survey information and write new survey
				$sql			= "select * from $surveysTableName 
									where survey_record_id = $inp_survey_id";
				$surveyResult	= $wpdb->get_results($sql);
				if ($surveyResult === FALSE) {
					handleWPDBError($jobname,$doDebug,"attempting to get survey id $inp_survey_id for cloning");
					$content	.= "<p>Attempting get data for survey $inp_survey_name failed<br />";
				} else {
					$numSRows	= $wpdb->num_rows;
					if ($doDebug) {
						echo "ran $sql<br />and retrieved $numSRows rows<br />";
					}
					if ($numSRows > 0) {
						foreach($surveyResult as $surveyResultRow) {
							$survey_record_id		= $surveyResultRow->survey_record_id;
							$survey_owner			= $surveyResultRow->survey_owner;
							$survey_name			= $surveyResultRow->survey_name;
							$survey_recurring		= $surveyResultRow->survey_recurring;
							
							// Insert the surveys record
							if ($inp_recurring == 'N') {
								$nowDate			= date('dMY');
								$inp_survey_name	= "$inp_survey_name $nowDate";
							}
							$insertResult		= $wpdb->insert($surveysTableName, 
																array('survey_owner'=>$userName, 
																	  'survey_name'=>$inp_survey_name, 
																	  'survey_recurring'=>$inp_recurring, 
																	  'survey_action_log'=>"$newDate $userName survey cloned "), 
																array('%s','%s','%s','%s'));
							if ($insertResult === FALSE) {
								handleWPDBError($jobname,$doDebug,"pass 15 attempting to insert new survey failed");
								$content		.= "<p>Attempting to insert cloned survey name into $surveysTableName failed</p>";
							} else {
								$survey_record_id	= $wpdb->insert_id;
								if ($doDebug) {
									echo "inserting survey name $inp_survey_name into $surveysTableName was succesful. Insert_id: $survey_record_id<br />";
								}
								
								// Now copy the survey content
								$cloneSQL		= "select * from $surveyContentTableName 
													where content_id = $inp_survey_id 
													order by content_seq";
								$cloneResult	= $wpdb->get_results($cloneSQL);
								if ($cloneResult === FALSE) {
									handleWPDBError($jobname,$doDebug,"pass2 attempting to read $surveyContentTableName to clone it");
									$content	.= "<p>Cloning Failed</p>";
								} else {
									$numSCRows	= $wpdb->num_rows;
									if ($doDebug) {
										echo "ran $cloneSQL<br />and retrieved $numSCRows rows<br />";
									}
									if ($numSCRows > 0) {
										foreach ($cloneResult as $cloneResultRow) {
											$content_id					= $cloneResultRow->content_id;
											$content_seq				= $cloneResultRow->content_seq;
											$content_text				= $cloneResultRow->content_text;
											$content_answer_format		= $cloneResultRow->content_answer_format;
											$content_answer_params		= $cloneResultRow->content_answer_params;
											$content_answer_required	= $cloneResultRow->content_answer_required;
											
											$cloneInsert		= $wpdb->insert($surveyContentTableName, 
																	array('content_id'=>$survey_record_id, 
																		  'content_seq'=>$content_seq, 
																		  'content_text'=>$content_text, 
																		  'content_answer_format'=>$content_answer_format, 
																		  'content_answer_params'=>$content_answer_params, 
																		  'content_answer_required'=>$content_answer_required, 
																		  'content_action_log'=>" $newDate $userName record created "), 
																	array('%d','%d','%s','%s','%s','%s','%s'));
											if ($cloneInsert === FALSE) {
												handleWPDBError($jobname,$doDebug,"pass2 attempting to insert $content_seq into $surveyContentTableName");
												$content	.= "<p>Cloning Failed</p>";
											} else {
												if ($doDebug) {
													echo "$content_seq cloned<br />";
												}
											
											}
										}
										$content		.= "<p>Survey $inp_survey_name is set up.</p>";
										$haveID			= TRUE;
										$updateSurvey	= TRUE;
									} else {
										$content	.= "<p>No questions found to be cloned</p>";
									}
								}
							}
						}
					} else {
						$content		.= "<p>No survey found with ID $inp_survey_id to clone</p>";
					}
				}
			}
		}
		if ($haveID) {
			$inp_survey_id	= $survey_record_id;
			if ($updateSurvey) {
				$content		.= "<p>Current Survey Questions</p>
									<table>";
				// get the current survey data and display for update
				$contentSQL		= "select * from $surveyContentTableName 
									where content_id = $survey_record_id 
									order by content_seq";
				$contentResult	= $wpdb->get_results($contentSQL);
				if ($contentResult === FALSE) {
					handleWPDBError($jobname,$doDebug,"pass 2 attempting to get survey questions");
					$content	.= "Trying to get the survey questions for update failed<br />";
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
							
							switch ($content_answer_format) {
								case "Block":
									$formatStr		= "Block <i>Text displayed. No response given</i>";
									break;
								case "shortText":
									$formatStr		= "shortText: Text response up to 100 characters</i>";
									break;
								case "longText":
									$formatStr		= "longText: Free form response </i>An unlimited character textual response</i>";
									break;
								case "YN":
									$formatStr		= "Yes or No </i>Can select either Yes or No for the response</i>";
									break;
								case "scale":
									$formatStr		= "Scale <i>Response selects one of the three to five options such as Poor,Fair,Good</i>";
									break;
								case "multimulti":
									$formatStr		= "";
									break;
								case "multisingle":
									$formatStr		= "";
							}
							
							$content		.= "<tr><td style='vertical-align:top;'><b>Question Sequence:</b> <i>Sequence in which the question will be shown</i><br />
													$content_seq<br /><br />
													<b>Question Format:</b><br />
													$formatStr<br />
													<br /><b>Question Text:</b> $content_text<br />";
							if ($content_answer_format == 'scale') {
								$content	.= "<br /><b>Question Parameters</b> <i>indicate up to five parameters, one of which will be selected, separated by commas. For example: Good,Fair,Poor</i><br />
												$content_answer_params<br />";
							}
							if ($content_answer_format != 'Block') {
								$content		.= "<br /><b>Is a response required?</b><br />
													$content_answer_required<br />";
							}
							$content		.= "<hr style='border: 2px solid black;'></td></tr>";
						}
						$content			.= "</table>";
					} else {
						$content			.= "<tr><td>No questions have been added</td></tr>
												</table>";
					}
					$content			.= "<p><b>What would you like to do?</b></p>
											<table>
											<tr><td style='width:300px;vertical-align:top;'><b>Modify a Question</b><br />
																		 Sequence Number to Modify:<br />
																		<form method='post' action='$theURL' 
																		name='modify_form' ENCTYPE='multipart/form-data'>
																		<input type='hidden' name='strpass' value='3'>
																		<input type='hidden' name='inp_survey_id' value='$inp_survey_id'>
																		 <input type='text' class='formInputText' size='5' maxlength='5' name='inp_sequence' ><br />
																		<input class='formInputButton' type='submit' name='submit' value='Modify' />
																		</form></td>
												<td style='width:300px;vertical-align:top;'><b>Add a Question</b><br />
																		Sequence Number to Add:<br />
																		<form method='post' action='$theURL' 
																		name='add_form' ENCTYPE='multipart/form-data'>
																		<input type='hidden' name='strpass' value='4'>
																		<input type='hidden' name='inp_survey_id' value='$inp_survey_id'>
																		 <input type='text' class='formInputText' size='5' maxlength='5' name='inp_sequence' ><br />
																		<input class='formInputButton' type='submit' name='submit' value='Add' />
																		</form></td>
												<td style='width:300px;vertical-align:top;'><b>Delete a Question</b><br />
																		 Sequence Number to Delete:<br />
																		<form method='post' action='$theURL' 
																		name='clone_form' ENCTYPE='multipart/form-data'>
																		<input type='hidden' name='strpass' value='3'>
																		<input type='hidden' name='inp_survey_id' value='$inp_survey_id'>
																		 <input type='text' class='formInputText' size='5' maxlength='5' name='inp_sequence' ><br />
																		<input class='formInputButton' type='submit' name='submit' value='Delete' />
																		</form></td>
												<td style='width:300px;vertical-align:top;'><b>Finish</b><br />
																		<form method='post' action='$theURL' 
																		name='finish_form' ENCTYPE='multipart/form-data'>
																		<input type='hidden' name='strpass' value='99'>
																		<input class='formInputButton' type='submit' name='submit' value='Finish' />
																		</form></td></tr></table>";
					$strPass	= '0';
				}
			}
		} else {
			if ($doDebug) {
				echo "shouldn't get here. Should have a record_id<br />";
			}
			$content	.= "<b>Fatal Error</b>";
			$strPass	= '0';
		}
	} elseif ("3" == $strPass) {
		if ($doDebug) {
			echo "<br />at pass3 to modify $inp_sequence in survey ID $inp_survey_id<br />";
		}	
		$content 		.= "<h3>$jobname</h3>";
		$contentSQL		= "select * from $surveyContentTableName 
							where content_id = $inp_survey_id 
							and content_seq = $inp_sequence ";
		$contentResult	= $wpdb->get_results($contentSQL);
		if ($contentResult === FALSE) {
			handleWPDBError($jobname,$doDebug,"pass 3 attempting to get survey question $inp_sequence for modification");
			$content	.= "Trying to get the survey question for update failed<br />";
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

					$blockChecked		= '';
					$YNChecked			= '';
					$shortTextChecked		= '';
					$longTextChecked		= '';
					$scaleChecked		= '';
					$multimultiChecked	= '';
					$mintisingleChecked	= '';
					switch ($content_answer_format) {
						case "Block":
							$blockChecked	= 'checked';
							break;
						case "shortText":
							$shortTextChecked	= 'checked';
							break;
						case "longText":
							$longTextChecked	= 'checked';
							break;
						case "YN":
							$YNChecked		= 'checked';
							break;
						case "scale":
							$scaleChecked	= 'checked';
							break;
						case "multimulti":
							$multimultiChecked	= 'checked';
							break;
						case "multisingle": 
							$multiSingleChecked	= 'checked';
					}
					$requiredYesChecked		= '';
					$requiredNoChecked		= '';
					if ($content_answer_required == 'Y') {
						$requiredYesChecked	= 'checked';
						$requiredNoChecked	= '';
					} elseif ($content_answer_required == 'N') {
						$requiredYesChecked	= '';
						$requiredNoChecked	= 'checked';
					}
					
					$content		.= "<h4>Record to be Modified</h4>
										<form method='post' action='$theURL' 
										name='update_form' ENCTYPE='multipart/form-data'>
										<input type='hidden' name='strpass' value='4'>
										<input type='hidden' name='inp_survey_id' value='$inp_survey_id'>
										<input type='hidden' name='old_sequence' value='$content_seq'>
										<tr><td style='vertical-align:top;'><b>Question Sequence:</b> <i>Sequence in which the question will be shown</i><br />
											<input type='text' 
											class='formInputText' size='5' maxlength='5' value='$content_seq' name='inpSeq'><br /><br />
											<b>Question Format:</b><br /><input type='radio' class='formInputButton' name='inpFormat' value='Block' $blockChecked> Block <i>Text displayed. No response given</i><br />
																	<input type='radio' class='formInputButton' name='inpFormat' value='YN' $YNChecked>Yes or No </i>Can select either Yes or No for the response</i><br />
																	<input type='radio' class='formInputButton' name='inpFormat' value='shortText' $shortTextChecked>Text response up to 100 characters</i><br />
																	<input type='radio' class='formInputButton' name='inpFormat' value='longText' $longTextChecked>Free form response </i>An unlimited character textual response</i><br />
																	<input type='radio' class='formInputButton' name='inpFormat' value='scale' $scaleChecked>Scale <i>Response selects one of the three to five options such as Poor,Fair,Good</i><br />
											<br /><b>Question Text:</b> <textarea class='formInputText' rows='5' cols='50' name='inpText'>$content_text</textarea><br />";
					if ($content_answer_format == 'scale') {
						$content	.= "<br /><b>Question Parameters</b> <i>indicate up to five parameters, one of which will be selected, separated by commas. For example: Good,Fair,Poor</i><br />
										<input type='text' class='formInputText' name='inpParams' size='20' maxlength='20' value='$content_answer_params'><br />";
					}
					if ($content_answer_format != 'Block') {
						$content		.= "<br /><b>Is a response required?</b><br />
											<input type='radio' class='formInputButton' name='inp_required' value='Y' $requiredYesChecked >Yes<br />
											<input type='radio' class='formInputButton' name='inp_required' value='N' $requiredNoChecked >No<br />";
					}
					$content		.= "<hr style='border: 2px solid black;'></td></tr>
										<br /><input class='formInputButton' name='submit' type='submit' name='submit' value='Update' />
										</form></td></tr></table>";
				}
			} else {
				$content		.= "<p>No record found to update</p>";
			}
		}
	}
	if ("99" == $strPass) {
		if ($doDebug) {
			echo "<br />at pass99<br />";
		}
		$content		.= "<h3>$jobname</h3>
							<p>The questionnaire is ready to be used. You may close this window</p>";
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
	$result			= write_joblog_func("$jobname|$nowDate|$nowTime|$userName|Time|$thisStr|$strPass: $elapsedTime|$ipAddr",$doDebug);
	if ($result == 'FAIL') {
		$content	.= "<p>writing to joblog.txt failed</p>";
	}
	return $content;
}
add_shortcode ('setup_survey', 'setup_survey_func');

