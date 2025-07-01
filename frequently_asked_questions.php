function frequently_asked_questions_func() {

/*

	faq table:
		faq_record_id
		faq_category
		faq_question
		faq_answer
		faq_tags
		faq_status			N: new question
		faq_submitted_by
		faq_date_created
		faq_date_updated
*/ 
	global $wpdb;

	$doDebug						= FALSE;
	$testMode						= FALSE;
	$initializationArray 			= data_initialization_func();
	$validUser 						= $initializationArray['validUser'];
	$userName			= $initializationArray['userName'];
//	CHECK THIS!								//////////////////////
	if ($userName == '') {
		return "YOU'RE NOT AUTHORIZED!<br />Goodby";
	}

	$versionNumber				 	= "1";
	if ($doDebug) {
		echo "Initialization Array:<br /><pre>";
		print_r($initializationArray);
		echo "</pre><br />";
	}
	$currentTimestamp	= $initializationArray['currentTimestamp'];
	$validTestmode		= $initializationArray['validTestmode'];
	$siteURL			= $initializationArray['siteurl'];
	$userEmail			= $initializationArray['userEmail'];
	$userDisplayName	= $initializationArray['userDisplayName'];
	$userRole			= $initializationArray['userRole'];
	
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
	$theURL						= "$siteURL/cwa-frequently-asked-questions/";
	$jobname					= "Frequently Asked Questions V$versionNumber";
	$inp_new_question			= "";		// id of new question for admin to update
	$inp_admin					= "";		// either update or delete
	$inp_tag_list				= "";		// tag to be displayed
	$inp_option					= "None Entered";		// add, listTag, search, alpha
	$inp_question				= "";		// question to be added
	$inp_search					= "";		// search term
	$inp_submit					= "";
	$token						= "";

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
			if ($str_key		== "inp_new_question") {
				$inp_new_question	= $str_value;
				$inp_new_question	= filter_var($inp_new_question,FILTER_UNSAFE_RAW);
			}
			if ($str_key		== "inp_admin") {
				$inp_admin	= $str_value;
				$inp_admin	= filter_var($inp_admin,FILTER_UNSAFE_RAW);
			}
			if ($str_key		== "inp_tag_list") {
				$inp_tag_list	= $str_value;
				$inp_tag_list	= filter_var($inp_tag_list,FILTER_UNSAFE_RAW);
			}
			if ($str_key		== "inp_option") {
				$inp_option	= $str_value;
				$inp_option	= filter_var($inp_option,FILTER_UNSAFE_RAW);
			}
			if ($str_key		== "inp_search") {
				$inp_search	= $str_value;
				$inp_search	= filter_var($inp_search,FILTER_UNSAFE_RAW);
			}
			if ($str_key		== "inp_category") {
				$inp_category	= $str_value;
				$inp_category	= filter_var($inp_category,FILTER_UNSAFE_RAW);
			}
			if ($str_key		== "inp_question") {
				$inp_question	= $str_value;
				$inp_question	= filter_var($inp_question,FILTER_UNSAFE_RAW);
			}
			if ($str_key		== "inp_answer") {
				$inp_answer	= $str_value;
				$inp_answer	= filter_var($inp_answer,FILTER_UNSAFE_RAW);
			}
			if ($str_key		== "inp_record_id") {
				$inp_record_id	= $str_value;
				$inp_record_id	= filter_var($inp_record_id,FILTER_UNSAFE_RAW);
			}
			if ($str_key		== "inp_tags") {
				$inp_tags	= $str_value;
				$inp_tags	= filter_var($inp_tags,FILTER_UNSAFE_RAW);
			}
			if ($str_key		== "inp_submit") {
				$inp_submit	= $str_value;
				$inp_submit	= filter_var($inp_submit,FILTER_UNSAFE_RAW);
			}
			if ($str_key		== "token") {
				$token	= $str_value;
				$token	= filter_var($token,FILTER_UNSAFE_RAW);
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
		$faqTableName				= "wpw1_cwa_faq2";
		$userMasterTableName		= 'wpw1_cwa_user_master2';
	} else {
		$extMode					= 'pd';
		$faqTableName				= "wpw1_cwa_faq";
		$userMasterTableName		= 'wpw1_cwa_user_master';
	}



	if ("1" == $strPass) {
		if ($doDebug) {
			echo "<br />Pass 1<br />";
		}
		
		if ($token != '') {
			$result		= resolve_reminder($userName,$token,$testMode,$doDebug);
			if ($doDebug) {
				echo "token $token removed (probably)<br />";
			}
		}
		
		$content 		.= "<h3>$jobname</h3>
							<form method='post' action='$theURL' 
							name='selection_form' ENCTYPE='multipart/form-data'>
							<input type='hidden' name='strpass' value='2'>
							<table style='border-collapse:collapse;'>";
		if ($userRole == 'administrator') {
			$newQuestionTable	= "";
			// see if there are any new questions needing an answer
			$sql		= "select * from $faqTableName 
							where faq_status = 'N' 
							order by faq_date_created";
			$faqResult	= $wpdb->get_results($sql);
			if ($faqResult === FALSE) {
				handleWPDBError($jobname,$doDebug,"attempting to get new questions");
				$content	.= "Unable to read $faqTableName table";
			} else {
				$numFRows	= $wpdb->num_rows;
				if ($doDebug) {
					echo "ran $sql<br />and retrieved $numFRows rows<br />";
				}
				if ($numFRows > 0) {
					$newQuestionTable		= "Answer a new question<br />
												<table>
											   <tr><th>ID</th>
											   		<th>Question</th>
											   		<th>Submitted by</th></tr>";
					foreach($faqResult as $faqRow) {
						$faq_record_id		= $faqRow->faq_record_id;
						$faq_category		= $faqRow->faq_category;
						$faq_question		= $faqRow->faq_question;
						$faq_answer			= $faqRow->faq_answer;
						$faq_tags			= $faqRow->faq_tags;
						$faq_status			= $faqRow->faq_status;
						$faq_submitted_by	= $faqRow->faq_submitted_by;
						$faq_date_created	= $faqRow->faq_date_created;
						$faq_date_updated	= $faqRow->faq_date_updated;
						
						$newQuestionTable	.= "<tr><td><input type='radio' class='formInputButton' name='inp_new_question' value='$faq_record_id'>$faq_record_id</td>
												<td>$faq_question</td>
												<td>$faq_submitted_by</td></tr>";
					}
					$newQuestionTable		.= "</table>";
				}
			}
			
			$content		.= "<tr><td style='vertical-align:top;'><b>Administrator Functions</b></td><td>";
			if ($newQuestionTable != '') {
				$content	.= "$newQuestionTable<br />";
			}
			$content	.= "<input type='radio' class='formInputButton' name='inp_admin' value='update'>Update or Delete a question<br />
							<input type='radio' class='formInputButton' name='inp_admin' value='add'>Add a question<br />
							<tr><td colspan='2'><hr></td></tr>";
		}
		// menu for students and advisors
//		// get a list of all tags
//		$tagsList		= array();
//		$listTags		= "";
//		$tagsListCount	= 0;
//		if ($userRole == 'administrator') {
//			$myStr	= "and (faq_category = 'administrator' or faq_category = 'advisor' or faq_category = 'student' or faq_category = 'all') ";
//		} elseif ($userRole == 'advisor') {
//			$myStr	= "and (faq_category = 'advisor' or faq_category = 'student' or faq_category = 'all') ";
//		} elseif ($userRole == 'student') {
//			$myStr	= "and (faq_category = 'student'  or faq_category = 'all')";
//		}
//		$sql			= "select faq_tags from $faqTableName 
//							where faq_tags != '' $myStr";
//		$tagsResult		= $wpdb->get_results($sql);
//		if ($tagsResult === FALSE) {
//			handleWPDBError($jobname,$doDebug,"attempting to get tags list");
//		} else {
//			$numTRows	= $wpdb->num_rows;
//			if ($doDebug) {
//				echo "ran $sql<br />and retrieved $numTRows rows<br />";
//			}
//			if ($numTRows > 0) {
//				foreach($tagsResult as $tagsRow) {
//					$thisTags		= $tagsRow->faq_tags;
//					
//					$myArray		= explode(",",$thisTags);
//					foreach($myArray as $nextTag) {
//						if (!in_array($nextTag,$tagsList)) {
//							$tagsList[]	= $nextTag;
//							$tagsListCount++;
//						}
//					}
//				}
//			}
//		}
//		if ($doDebug) {
//			echo "tagsListCount: $tagsListCount<br />";
//		}
//		if ($tagsListCount > 0) {
//			sort($tagsList);
//			foreach($tagsList as $nextTag) {
//				$listTags	.= "<input type='radio' class='formInputButton' name='inp_tag_list' value='$nextTag'>$nextTag<br />";
//			}
//		}
		$content		.= "<tr><td colspan='2'><h4>Frequently Asked Questions Options</h4></td></tr>
							<tr><td style='vertical-align:top;'><input type='radio' class='formInputButton' name='inp_option' value='add'>Submit a new question</td>
								<td>Enter your question here:<br /><textarea class='formInputText' rows='5' cols='30' name='inp_question'></textarea></td></tr>
							<tr><td style='vertical-align:top;'><input type='radio' class='formInputButton' name='inp_option' value='search'>Search questions</td>
								<td><input type='text' class='formInputText' size='30' maxlength='50' name='inp_search'></td></tr>
							<tr><td style='vertical-align:top;'><input type='radio' clas='formInputButton' name='inp_option' value='alpha'>List all questions</td>
								<td></td></tr>
							$testModeOption
							<tr><td colspan='2'><input class='formInputButton' type='submit' value='Submit' /></td></tr></table>
							</form></p>";
	

///// Pass 2 -- do the work
	} elseif ("2" == $strPass) {
		if ($doDebug) {
			echo "<br />At pass 2 with inp_option of $inp_option<br />";
		}	
		if ($token != '') {
			$result		= resolve_reminder($userName,$token,$testMode,$doDebug);
			if ($doDebug) {
				echo "token $token removed (probably)<br />";
			}
		}
		
		$allowableCategory	= "(faq_category =' adminstrator' or 
							    faq_category = 'advisor' or 
							    faq_category = 'student' or 
							    faq_category = 'all') ";
		if ($userRole == 'advisor') {
			$allowableCategory	= "(faq_category = 'advisor' or 
									faq_category = 'all') ";
		}
		if ($userRole == 'student') {
			$allowableCategory	= "(faq_category = 'student' or 
									faq_category = 'all') ";
		}
		$content		.= "<h3>$jobname</h3>";

		if ($inp_new_question != '') {
			if ($doDebug) {
				echo "inp_new_question is not blank. Answering question<br />";
			}
			$content	.= "<h4>Answer question $inp_new_question</h4>";
			$sql		= "select * from $faqTableName 
							where faq_record_id = $inp_new_question";
			$faqResult	= $wpdb->get_results($sql);
			if ($faqResult === FALSE) {
				handleWPDBError($jobname,$doDebug,"attempting to get new questions");
				$content	.= "Unable to read $faqTableName table";
			} else {
				$numFRows	= $wpdb->num_rows;
				if ($doDebug) {
					echo "ran $sql<br />and retrieved $numFRows rows<br />";
				}
				if ($numFRows > 0) {
					foreach($faqResult as $faqRow) {
						$faq_record_id		= $faqRow->faq_record_id;
						$faq_category		= $faqRow->faq_category;
						$faq_question		= $faqRow->faq_question;
						$faq_answer			= $faqRow->faq_answer;
						$faq_tags			= $faqRow->faq_tags;
						$faq_status			= $faqRow->faq_status;
						$faq_submitted_by	= $faqRow->faq_submitted_by;
						$faq_date_created	= $faqRow->faq_date_created;
						$faq_date_updated	= $faqRow->faq_date_updated;
						
						$content		.= "<form method='post' action='$theURL' 
											name='selection_form' ENCTYPE='multipart/form-data'>
											<input type='hidden' name='strpass' value='94'>
											<input type='hidden' name='inp_record_id' value='$inp_new_question'>
											<input type='hidden' name='inp_mode' value='$inp_mode'>
											<input type='hidden' name='inp_verbose' value='$inp_verbose'>
											<table style='border-collapse:collapse;'>
											<tr><td>ID</td>
												<td>$faq_record_id</td></tr>
											<tr><td style='vertical-align:top;'>Category</td>
												<td><input type='radio' class='formInputButton' name='inp_category' value='administrator'>Administrator<br />
													<input type='radio' class='formInputButton' name='inp_category' value='advisor'>Advisor<br />
													<input type='radio' class='formInputButton' name='inp_category' value='student'>Student<br />
													<input type='radio' class='formInputButton' name='inp_category' value='all'>All</td></tr>
											<tr><td style='vertical-align:top;'>Question</td>
												<td><textarea class='formInputText' rows='5' cols='50' name='inp_question'>$faq_question</textarea></td></tr>
											<tr><td style='vertical-align:top;'>Answer</td>
												<td><textarea class='formInputText' rows='5' cols='50' name='inp_answer'>$faq_answer</textarea></td></tr>
											<tr><td>Submitted by</td>
												<td>$faq_submitted_by</td></tr>
											<tr><td>Date Created</td>
												<td>$faq_date_created</td></tr>
											<tr><td>Date Updated</td>
												<td>$faq_date_updated</td></tr>
											<tr><td><input class='formInputButton' name='inp_submit' type='submit' value='Update' /></td>
												<td><input class='formInputButton' name='inp_submit' type='submit' value='Delete' /></td></table></form>";
					}
				}
			}
		}
		
		if ($inp_admin == 'add') {
			$content		.= "<form method='post' action='$theURL' 
								name='selection_form' ENCTYPE='multipart/form-data'>
								<input type='hidden' name='strpass' value='80'>
								<input type='hidden' name='inp_mode' value='$inp_mode'>
								<input type='hidden' value='inp_verbose' value='$inp_verbose'>
								<table style='border-collapse:collapse;'>
								<tr><td style='vertical-align:top;'>Category</td>
									<td><input type='radio' class='formInputButton' name='inp_category' value='administrator'>Administrator<br />
										<input type='radio' class='formInputButton' name='inp_category' value='advisor'>Advisor<br />
										<input type='radio' class='formInputButton' name='inp_category' value='student'>Student<br />
										<input type='radio' class='formInputButton' name='inp_category' value='all'>All</td></tr>
								<tr><td style='vertical-align:top;'>Question</td>
									<td><textarea class='formInputText' rows='5' cols='50' name='inp_question'></textarea></td></tr>
								<tr><td style='vertical-align:top;'>Answer</td>
									<td><textarea class='formInputText' rows='5' cols='50' name='inp_answer'></textarea></td></tr>
								<tr><td colspan='2'><input class='formInputButton' type='submit' value='Update' /></td></table></form>";
		}
		if ($inp_admin == 'update') {
			$questionList	= "<p>No questions found</p>";
			$sql		= "select * from $faqTableName 
							where $allowableCategory  
							order by faq_date_updated DESC";
			$faqResult	= $wpdb->get_results($sql);
			if ($faqResult === FALSE) {
				handleWPDBError($jobname,$doDebug,"attempting to get new questions");
				$content	.= "Unable to read $faqTableName table";
			} else {
				$numFRows	= $wpdb->num_rows;
				if ($doDebug) {
					echo "ran $sql<br />and retrieved $numFRows rows<br />";
				}
				if ($numFRows > 0) {
					$questionList			= "<form method='post' action='$theURL' 
												name='selection_form' ENCTYPE='multipart/form-data'>
												<input type='hidden' name='strpass' value='90'>
												<input type='hidden' name='inp_mode' value='$inp_mode'>
												<input type='hidden' value='inp_verbose' value='$inp_verbose'>
												<table style='border-collapse:collapse;'>
											   	<tr><th>ID</th>
											   		<th>Question</th>
											   		<th>Submitted by</th>
											   		<th>Date Updated</th></tr>";
					foreach($faqResult as $faqRow) {
						$faq_record_id		= $faqRow->faq_record_id;
						$faq_category		= $faqRow->faq_category;
						$faq_question		= $faqRow->faq_question;
						$faq_answer			= $faqRow->faq_answer;
						$faq_tags			= $faqRow->faq_tags;
						$faq_status			= $faqRow->faq_status;
						$faq_submitted_by	= $faqRow->faq_submitted_by;
						$faq_date_created	= $faqRow->faq_date_created;
						$faq_date_updated	= $faqRow->faq_date_updated;
						
						$questionList		.= "<tr><td><input type='radio' class='formInputButton' name='inp_update' value='$faq_record_id'>$faq_record_id</td>
												<td>$faq_question</td>
												<td>$faq_submitted_by</td>
												<td>$faq_date_updated</tr>";
					}
				}
			}
			$content	.= "<h4>Update or Delete a Question from the Following List</h4>
							$questionList
							<tr><td><input class='formInputButton' type='submit' value='Update' name='inp_submit'/></td>
								<td><input class='formInputButton' type='submit' value='Delete' name='inp_submit'/></td>
								<td colspan='2'></td></tr></table>
							</form></p>";
		}
		
		if ($inp_option == 'add') {
			$content	.= "<h4>Add the Following Question as a New Question</h4>
							<p>$inp_question</p>";
			if ($inp_question == '') {
				$content	.= "<p>No question was supplied -- no action taken</p>";
			} else {
				$insertResult	= $wpdb->insert($faqTableName,
											array('faq_category'=>'',
												  'faq_question'=>$inp_question,
												  'faq_answer'=>'',
												  'faq_tags'=>'',
												  'faq_status'=>'N',
												  'faq_submitted_by'=>$userName),
											array('%s','%s','%s','%s','%s','%s'));
											
				if ($insertResult === FALSE) {
					handleWPDBError($jobname,$doDebug,"attempting to insert a new question");
					$content	.= "<p>Adding the question failed</p>";
				} else {
					$content	.= "<p>The question was successfully added. A system administrator 
will answer your question in the next day or so. When the sysadmin has answered your 
question, the system will notify you by email.</p>";

					$effective_date		= date('Y-m-d 00:00:00');
					$closeStr			= strtotime("+ 2 days");
					$close_date			= date('Y-m-d 00:00:00',$closeStr);
				
					$token					= mt_rand();
					$email_text				= "<p>$userName has submitted an FAQ question.</p>";
					$reminder_text			= "<p><b>New FAQ Question:</b> $userName has submitted 
a new question: $inp_question. Click <a href='$theURL?token=$token' target='_blank'>Frequently_asked_questions</a> to 
respond to the question</p>";
					$inputParams		= array("effective_date|$effective_date|s",
												"close_date|$close_date|s",
												"resolved_date||s",
												"send_reminder|N|s",
												"send_once|Y|s",
												"call_sign|K7OJL|s",
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
					}
				}
			}
		}
		
		if ($inp_option == 'listTag') {
			$content	.= "<h4>List Questions for Tag $inp_tag_list</h4>";
		}
		
		if ($inp_option == 'search') {
			$content	.= "<h4>Search Questions for the Following Search Term</h4>
							<p>$inp_search</p>";
							
			$sql		= "select * from $faqTableName 
							where $allowableCategory 
							and faq_question like '%$inp_search%' 
							order by faq_record_id";
			
			$faqResult	= $wpdb->get_results($sql);
			if ($faqResult === FALSE) {
				handleWPDBError($jobname,$doDebug,"attempting to get new questions");
				$content	.= "Unable to read $faqTableName table";
			} else {
				$numFRows	= $wpdb->num_rows;
				if ($doDebug) {
					echo "ran $sql<br />and retrieved $numFRows rows<br />";
				}
				if ($numFRows > 0) {
					$content			.= "<table style='border-collapse:collapse;width:1000px;'>
											   	<tr><th>ID</th>
											   		<th>Question / Answer</th>
											   		<th>Category</th>
											   		<th>Date Updated</th></tr>";
					foreach($faqResult as $faqRow) {
						$faq_record_id		= $faqRow->faq_record_id;
						$faq_category		= $faqRow->faq_category;
						$faq_question		= stripslashes($faqRow->faq_question);
						$faq_answer			= stripslashes($faqRow->faq_answer);
						$faq_tags			= $faqRow->faq_tags;
						$faq_status			= $faqRow->faq_status;
						$faq_submitted_by	= $faqRow->faq_submitted_by;
						$faq_date_created	= $faqRow->faq_date_created;
						$faq_date_updated	= $faqRow->faq_date_updated;
						
						$recordIDStr		= $faq_record_id;
						if ($userRole == 'administrator') {
							$recordIDStr	= "<a href='$theURL/?strpass=90&inp_record_id=$faq_record_id&inp_verbose=$inp_verbose&inp_mode=$inp_mode&inp_submit=Update' target='_blank'>$faq_record_id</a>";
						}
						$content			.= "<tr><td style='vertical-align:top;border-bottom: 1px solid black;padding-bottom:5px;'>$recordIDStr</td>
													<td style='vertical-align:top;border-bottom: 1px solid black;padding-bottom:5px;'><b><em>$faq_question</em></b><br />
														$faq_answer</td>
													<td	 style='vertical-align:top;border-bottom: 1px solid black;padding-bottom:5px;'>$faq_category</td>
													<td style='vertical-align:top;border-bottom: 1px solid black;padding-bottom:5px;'>$faq_date_updated</td></tr>";
						
					}
					$content				.= "</table>";
				} else {
					$content				.= "<p>No records found in $faqTableName table meeting the search criteria</p>";
				}
			}
		}
		if ($inp_option == 'alpha') {
			$content	.= "<h4>List All Questions in Alphabetic Order</h4>";

			$sql		= "select * from $faqTableName 
							where $allowableCategory 
							order by faq_question";
			$faqResult	= $wpdb->get_results($sql);
			if ($faqResult === FALSE) {
				handleWPDBError($jobname,$doDebug,"attempting to get new questions");
				$content	.= "Unable to read $faqTableName table";
			} else {
				$numFRows	= $wpdb->num_rows;
				if ($doDebug) {
					echo "ran $sql<br />and retrieved $numFRows rows<br />";
				}
				if ($numFRows > 0) {
					$content			.= "<table style='border-collapse:collapse;width:1000px;'>
											   	<tr><th>ID</th>
											   		<th>Question / Answer</th>
											   		<th>Category</th>
											   		<th>Date Updated</th></tr>";
					foreach($faqResult as $faqRow) {
						$faq_record_id		= $faqRow->faq_record_id;
						$faq_category		= $faqRow->faq_category;
						$faq_question		= stripslashes($faqRow->faq_question);
						$faq_answer			= stripslashes($faqRow->faq_answer);
						$faq_tags			= $faqRow->faq_tags;
						$faq_status			= $faqRow->faq_status;
						$faq_submitted_by	= $faqRow->faq_submitted_by;
						$faq_date_created	= $faqRow->faq_date_created;
						$faq_date_updated	= $faqRow->faq_date_updated;
						
						$recordIDStr		= $faq_record_id;
						if ($userRole == 'administrator') {
							$recordIDStr	= "<a href='$theURL/?strpass=90&inp_record_id=$faq_record_id&inp_verbose=$inp_verbose&inp_mode=$inp_mode&inp_submit=Update' target='_blank'>$faq_record_id</a>";
						}
						$content			.= "<tr><td style='vertical-align:top;border-bottom: 1px solid black;padding-bottom:5px;'>$recordIDStr</td>
													<td style='vertical-align:top;border-bottom: 1px solid black;padding-bottom:5px;'><b><em>$faq_question</em></b><br />
														$faq_answer</td>
													<td	 style='vertical-align:top;border-bottom: 1px solid black;padding-bottom:5px;'>$faq_category</td>
													<td style='vertical-align:top;border-bottom: 1px solid black;padding-bottom:5px;'>$faq_date_updated</td></tr>";
						
					}
					$content				.= "</table>";
				} else {
					$content				.= "<p>No records found in $faqTableName table</p>";
				}
			}
		}

	} elseif ("80" == $strPass) {			//// add a record
		$content		.= "<h3>$jobname</h3>";
		$insertResult	= $wpdb->insert($faqTableName,
									array('faq_category'=>$inp_category,
										  'faq_question'=>$inp_question,
										  'faq_answer'=>$inp_answer,
										  'faq_tags'=>'',
										  'faq_status'=>'A',
										  'faq_submitted_by'=>$userName),
									array('%s','%s','%s','%s','%s','%s'));
									
		if ($insertResult === FALSE) {
			handleWPDBError($jobname,$doDebug,"attempting to insert a new question");
			$content	.= "<p>Adding the question failed</p>";
		} else {
			$content	.= "<p>The question was successfully added</p>";

		}
	

	} elseif ("90" == $strPass) {
		if ($doDebug) {
			echo "<br />at pass 90 -- update or delete $inp_record_id<br />";
		}
		if ($inp_submit == 'Update') {
			if ($doDebug) {
				echo "updating $inp_record_id<br />";
			}
			$sql		= "select * from $faqTableName 
							where faq_record_id = $inp_record_id";
			$faqResult	= $wpdb->get_results($sql);
			if ($faqResult === FALSE) {
				handleWPDBError($jobname,$doDebug,"attempting to get new questions");
				$content	.= "Unable to read $faqTableName table";
			} else {
				$numFRows	= $wpdb->num_rows;
				if ($doDebug) {
					echo "ran $sql<br />and retrieved $numFRows rows<br />";
				}
				if ($numFRows > 0) {
					foreach($faqResult as $faqRow) {
						$faq_record_id		= $faqRow->faq_record_id;
						$faq_category		= $faqRow->faq_category;
						$faq_question		= $faqRow->faq_question;
						$faq_answer			= $faqRow->faq_answer;
						$faq_tags			= $faqRow->faq_tags;
						$faq_status			= $faqRow->faq_status;
						$faq_submitted_by	= $faqRow->faq_submitted_by;
						$faq_date_created	= $faqRow->faq_date_created;
						$faq_date_updated	= $faqRow->faq_date_updated;
						
						$adminChecked		= "";
						$advisorChecked		= "";
						$studentChecked		= "";
						$allChecked			= "";
						if ($faq_category == 'administrator') {
							$adminChecked	= " checked ";
						}
						if ($faq_category == 'advisor') {
							$advisorChecked	= " checked ";
						}
						if ($faq_category == 'student') {
							$studentChecked	= " checked ";
						}
						if ($faq_category == 'all') {
							$allChecked	= " checked ";
						}

						$content		.= "<form method='post' action='$theURL' 
											name='selection_form' ENCTYPE='multipart/form-data'>
											<input type='hidden' name='strpass' value='94'>
											<input type='hidden' name='inp_mode' value='$inp_mode'>
											<input type='hidden' name='inp_verbose' value='$inp_verbose'>
											<input type='hidden' name='inp_record_id' value='$faq_record_id'>
											<table style='border-collapse:collapse;'>
											<tr><td style='vertical-align:top;'>Category</td>
												<td><input type='radio' class='formInputButton' name='inp_category' value='administrator' $adminChecked>Administrator<br />
													<input type='radio' class='formInputButton' name='inp_category' value='advisor' $advisorChecked>Advisor<br />
													<input type='radio' class='formInputButton' name='inp_category' value='student' $studentChecked>Student<br />
													<input type='radio' class='formInputButton' name='inp_category' value='all' $allChecked>All</td></tr>
											<tr><td style='vertical-align:top;'>Question</td>
												<td><textarea class='formInputText' rows='5' cols='50' name='inp_question'>$faq_question</textarea></td></tr>
											<tr><td style='vertical-align:top;'>Answer</td>
												<td><textarea class='formInputText' rows='5' cols='50' name='inp_answer'>$faq_answer</textarea></td></tr>
											<tr><td colspan='2'><input class='formInputButton' type='submit' value='Update' /></td></table></form>";
					}
				} else {
					$content	.= "<p>No record found by that ID</p>";
				}
			}
		} elseif ($inp_admin == 'Delete') {
			if ($doDebug) {
				echo "<br />at pass 90 with inp_record_id of $inp_record_id to be deleted<br />";
			}
			$result		= $wpdb->delete($faqTableName,
										array('faq_record_id'=>$inp_record_id),
										array('%d'));
			if ($result === FALSE) {
				handleWPDBError($jobname,$doDebug,"trying to delete $inp_record_id");
			} else {
				$content	.= "<h3>$jobname</h3><p>$inp_record_id successfully deleted</p>";
			}
		}

	} elseif ("94" == $strPass) {
		if ($doDebug) {
			echo "<br />at pass 94 doing the actual update or delete<br />";
		}
		
		if ($inp_submit == 'Delete') {
			if ($doDebug) {
				echo "deleting $inp_record_id<br />";
			}
			$content	.= "<h3>$jobname</h3>
							<h4>Deleting Record $inp_record_id</h4>";
			$deleteResult	= $wpdb->delete($faqTableName,
											array('faq_record_id'=>$inp_record_id),
											array('%d'));
			if ($deleteResult === FALSE) {
				handleWPDBError($jobname,$doDebug,"attempting to delete $inp_record_id");
				$content	.= "<p>Deleting $inp_record_id failed</p>";
			} else {
				$content	.= "<p>Record $inp_record_id successfully deleted</p>";
			}
		} else {
			$content	.= "<h3>$jobname</h3>
							<h4>Updating $inp_record_id</h4>";
			$sql		= "select * from $faqTableName 
							where faq_record_id = $inp_record_id";
			$faqResult	= $wpdb->get_results($sql);
			if ($faqResult === FALSE) {
				handleWPDBError($jobname,$doDebug,"attempting to get new questions");
				$content	.= "Unable to read $faqTableName table";
			} else {
				$numFRows	= $wpdb->num_rows;
				if ($doDebug) {
					echo "ran $sql<br />and retrieved $numFRows rows<br />";
				}
				if ($numFRows > 0) {
					foreach($faqResult as $faqRow) {
						$faq_record_id		= $faqRow->faq_record_id;
						$faq_category		= $faqRow->faq_category;
						$faq_question		= $faqRow->faq_question;
						$faq_answer			= $faqRow->faq_answer;
						$faq_tags			= $faqRow->faq_tags;
						$faq_status			= $faqRow->faq_status;
						$faq_submitted_by	= $faqRow->faq_submitted_by;
						$faq_date_created	= $faqRow->faq_date_created;
						$faq_date_updated	= $faqRow->faq_date_updated;
	
						$updateParams		= array();
						$updateFormat		= array();
						$updateData			= "";
	
						if ($inp_category != $faq_category) {
							$updateParams['faq_category']	= $inp_category;
							$updateFormat[]					= '%s';
							$updateData		.= "Category changed to $inp_category<br />";
						}
						if ($inp_question != $faq_question) {
							$updateParams['faq_question']	= $inp_question;
							$updateFormat[]					= '%s';
							$updateData		.= "Question changed to $inp_question<br />";
						}
						if ($inp_answer != $faq_answer) {
							$updateParams['faq_answer']	= $inp_answer;
							$updateFormat[]					= '%s';
							$updateData		.= "Answer changed to $inp_answer<br />";
						}
//						if ($inp_tags != $faq_tags) {
//							$updateParams['faq_tags']	= $inp_tags;
//							$updateFormat[]					= '%s';
//							$updateData		.= "Tags changed to $inp_tags<br />";
//						}
						$updateParams['faq_status']		= 'A';
						$updateFormat[]					= '%s';
						$updateResult		= $wpdb->update($faqTableName,
															$updateParams,
															array('faq_record_id'=>$inp_record_id),
															$updateFormat,
															array('%d'));
						if ($updateResult === FALSE) {
							handleWPDBError($jobname,$doDebug,"attempting to update $faqTableName");
							$content		.= "<p>Update failed</p>";
						} else {
							$content		.= $updateData;
							
							$submitterEmail	= $wpdb->get_var("select user_email 
															  from $userMasterTableName 
															  where user_call_sign like '$faq_submitted_by'");
							if ($submitterEmail === FALSE) {
								handleWPDBError($jobname,$doDebug,"trying to get submitters email address");
							} else {
								// email submitter that question has been answered
		
								$theSubject		= "CW Academy Frequently Asked Questions Response";
								$theContent		= "<p>Your question: $faq_question<br />
has been updated: $inp_answer</p>
<p>73,<br />CW Academy</p>";
	
								$mailResult		= emailFromCWA_v3(array('theRecipient'=>$submitterEmail,
																			'theContent'=>$theContent,
																			'theSubject'=>$theSubject,
																			'theCc'=>'',
																			'theBcc'=>'',
																			'theAttachment'=>'',
																			'mailCode'=>11,
																			'jobname'=>$jobname,
																			'increment'=>0,
																			'testMode'=>$testMode,
																			'doDebug'=>$doDebug));
//								if ($doDebug) {
//									echo "mailResult:<br /><pre>";
//									print_r($mailResult);
//									echo "</pre><br />";
//								}
								if ($mailResult[0] === FALSE) {
									if ($doDebug) {
										echo "mail to $faq_submitted_by failed:<br />$mailResult[1]<br />";
									}
									$content		.= "<p>Email to $faq_submitted_by failed</p>";
								} else {
									$content		.= "<p>Question updated and submitter has been notified</p>";
								}
							}
						}
					}
				} else {
					$content	.= "<p>No record found with id $inp_record_id</p>";
				}
			}
		}

	}
	if ($strPass != '1') {
		$content	.= "<a href='$siteURL/cwa-frequently-asked-questions/'>Return to Frequently Asked Questions</a>";
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
	$theTitle		= esc_html(get_the_title());
	$jobmonth		= date('F Y');
	$updateData		= array('jobname' 		=> $jobname,
							'jobdate' 		=> $nowDate,
							'jobtime'		=> $nowTime,
							'jobwho' 		=> $userName,
							'jobmode'		=> 'Time',
							'jobdatatype' 	=> $thisStr,
							'jobaddlinfo'	=> "$strPass: $elapsedTime",
							'jobip' 		=> $ipAddr,
							'jobmonth' 		=> $jobmonth,
							'jobcomments' 	=> '',
							'jobtitle' 		=> $theTitle,
							'doDebug'		=> $doDebug);
	$result			= write_joblog2_func($updateData);
	if ($result === FALSE){
		$content	.= "<p>writing to joblog failed</p>";
	}
	return $content;
}
add_shortcode ('frequently_asked_questions', 'frequently_asked_questions_func');
