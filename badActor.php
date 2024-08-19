function badActor_func() {

/*
	Modified 15Apr23 by Roland to fix action_log
	Modified 12Jul23 by Roland to consolidated tables
	Modified 25June23 by Roland to add active / inactive status
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
	$theURL						= "$siteURL/bad-actors/";
	$jobname					= "Bad Actors";
	$inp_status					= '';
	$inp_reason					= '';
	$inp_id						= 0;
	$bad_actorTableName			= 'wpw1_cwa_bad_actor';
	$studentTableName			= 'wpw1_cwa_consolidated_student';
	$advisorTableName			= 'wpw1_cwa_consolidated_advisor';
	$searchArray				= array($studentTableName,
										$advisorTableName);
	$categoryArray				= array('wpw1_cwa_consolidated_student'=>'Student',
										'wpw1_cwa_consolidated_advisor'=>'Advisor');
	

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
			if ($str_key 		== "inp_call_sign") {
				$inp_call_sign	 = strtoupper(trim($str_value));
				$inp_call_sign	 = filter_var($inp_call_sign,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "inp_reason") {
				$inp_reason		 = $str_value;
			}
			if ($str_key 		== "inp_status") {
				$inp_status		 = $str_value;
			}
			if ($str_key 		== "inp_status") {
				$inp_status		 = $str_value;
			}
			if ($str_key 		== "inp_id") {
				$inp_id		 = $str_value;
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





	if ("1" == $strPass) {
		$content 		.= "<h3>$jobname</h3>
<p>Select the appropriate option</p>
<table style='border-collapse:collapse;'>
<tr><td style='vertical-align:top;'><h3>List Bad Actors</h3>
		<form method='post' action='$theURL' 
		name='option_form' ENCTYPE='multipart/form-data'>
		<input type='hidden' name='strpass' value='2'>
		<input class='formInputButton' type='submit' value='List Bad Actors' /></form></td>
	<td style='vertical-align:top;'><h3>Add a Bad Actor</h3>
		<form method='post' action='$theURL' 
		name='option_form' ENCTYPE='multipart/form-data'>
		<input type='hidden' name='strpass' value='5'>
		Call Sign:<br />
		<input type='text' class='formInputText' name='inp_call_sign' size='25' maxlength='25'><br />
		Reason:<br />
		<textarea class='formInputText' name='inp_reason' rows='5' cols='35'></textarea><br />
		<input class='formInputButton' type='submit' value='Add a Bad Actor' /></form></td>
	<td style='vertical-align:top;'><h3>Change Bad Actor Status</h3>
		<form method='post' action='$theURL' 
		name='option_form' ENCTYPE='multipart/form-data'>
		<input type='hidden' name='strpass' value='10'>
		Call Sign:<br />
		<input type='text' class='formInputText' name='inp_call_sign' size='25' maxlength='25'><br />
		<input class='formInputButton' type='submit' value='Change Bad Actor Status' /></form></td></tr>
</table>";
	

///// Pass 2 -- do the work


	} elseif ("2" == $strPass) {

		if ($doDebug) {
			echo "<br />arrived at pass 2 List Bad Actors<br />";
		}
		$content				.= "<h3>List Bad Actors</h3>";
		$sql					= "select * from $bad_actorTableName order by call_sign"; 
		$wpw1_cwa_bad_actor		= $wpdb->get_results($sql);
		if ($wpw1_cwa_bad_actor === FALSE) {
			$myError			= $wpdb->last_error;
			$myQuery			= $wpdb->last_query;
			if ($doDebug) {
				echo "Reading $bad_actorTableName table failed<br />
					  wpdb->last_query: $myQuery<br />
					  wpdb->last_error: $myError<br />";
			}
			$errorMsg			= "$jobname reading $bad_actorTableName failed.\nSQL: $myQuery\nError: $myError";
			sendErrorEmail($errorMsg);
			$content		.= "Unable to obtain content from $bad_actorTableName<br />";
		} else {
			$numBARows			= $wpdb->num_rows;
			if ($doDebug) {
				$myStr			= $wpdb->last_query;
				echo "ran $myStr<br />and found $numBARows rows<br />";
			}
			if ($numBARows > 0) {
				$content		.= "<table>
									<tr><th>Call Sign</th>
										<th>Name</th>
										<th>Category</th>
										<th>Reason</th>
										<th>Status</th>
										<th>Created</th>
										<th>Modified</th></tr>";
				foreach ($wpw1_cwa_bad_actor as $bad_actorRow) {
					$bad_actor_ID			= $bad_actorRow->record_id;
					$bad_actor_call_sign	= $bad_actorRow->call_sign;
					$bad_actor_last_name	= $bad_actorRow->last_name;
					$bad_actor_first_name	= $bad_actorRow->first_name;
					$bad_actor_category		= $bad_actorRow->category;
					$bad_actor_status		= $bad_actorRow->status;
					$bad_actor_date_created	= $bad_actorRow->date_created;
					$bad_actor_date_modified = $bad_actorRow->date_modified;
					
					$bad_actor_info			= stripslashes($bad_actorRow->information);
					$thisStatus				= "<a href='$theURL?strpass=10&inp_call_sign=$bad_actor_call_sign'>Active</a>";
					if ($bad_actor_status == 'I') {
						$thisStatus			= "<a href='$theURL?strpass=10&inp_call_sign=$bad_actor_call_sign'>Inactive</a>";
					}
					
					$content	.= "<tr><td style='vertical-align:top;'>$bad_actor_call_sign</td>
										<td style='vertical-align:top;'>$bad_actor_last_name, $bad_actor_first_name</td>
										<td style='vertical-align:top;'>$bad_actor_category</td>
										<td style='vertical-align:top;'>$bad_actor_info</td>
										<td style='vertical-align:top;'>$thisStatus</td>
										<td style='vertical-align:top;'>$bad_actor_date_created</td>
										<td style='vertical-align:top;'>$bad_actor_date_modified</tr>";
				}
				$content		.= "</table><p>$numBARows bad actor records</p>
									<p>Click on 'Status' to change the status</p>";
			} else {
				$content		.= "No data found in $bad_actorTableName";
			}

		}
	
	
	
	} elseif ("5" == $strPass) {
		if ($doDebug) {
			echo "<br />at pass 3 Add Bad Actor<br />";
		}
		
		// find the bad actor by searching student, past_student, advisor, past_advisor
		$haveRecord				= FALSE;
		$first_name				= '';
		$last_name				= '';
		$category				= 'Unknown';
		foreach($searchArray as $thisTable) {
			if (!$haveRecord) {
				if ($doDebug) {
					echo "searching $thisTable<br />";
				}
				$sql			= "select first_name, last_name from $thisTable where call_sign='$inp_call_sign' limit 1";
				$thisResult		= $wpdb->get_results($sql);
				if ($thisResult === FALSE) {
					$myError	= $wpdb->last_error;
					$myQuery	= $wpdb->last_query;
					echo "attempting to read $thisTable failed:<br />
						  Error: $thisError<br />
						  Query: $thisQuery<br />";					
				} else {
					$numRows	= $wpdb->num_rows;
					if ($doDebug) {
						$myStr	= $wpdb->last_query;
						echo "ran $myStr<br />and retrieved $numRows rows<br />";
					}
					if ($numRows > 0) {
						foreach($thisResult as $thisRow) {
							$first_name		= $thisRow->first_name;
							$last_name		= $thisRow->last_name;
							$haveRecord	= TRUE;
							$category		= $categoryArray[$thisTable];
						}
					}
				}
			}
		}
		if (!$haveRecord) {
			$content			.= "<p>No record found in any student or advisor table. Adding record with default info.</p>";
		}
		
		
		$addResult				= $wpdb->insert($bad_actorTableName,
												array('call_sign'=>$inp_call_sign,
													  'first_name'=>$first_name,
													  'last_name'=>$last_name,
													  'category'=>$category,
													  'information'=>$inp_reason),
												array('%s','%s','%s','%s','%s'));
		if ($addResult === FALSE) {
			$myError			= $wpdb->last_error;
			$myQuery			= $wpdb->last_query;
			if ($doDebug) {
				echo "Inserting a record into $bad_actorTableName failed<br />
					  Last query: $myQuery<br />
					  Last error: $myError<br />";
			}
			$content			.= "inserting the bad actor failed";
			sendErrorEmail("$jobname inserting bad actor failed\nQuery: $myQuery\nError: $myError");
		} else {
			$content			.= "<h3>Add Bad Actor</h3><p>Successfully added $inp_call_sign<p>";
		}
	
	} elseif ("10" == $strPass) {
		if ($doDebug) {
			echo "<br />at pass 10 Change Bad Actor Status<br />";
		}
		$sql					= "select * from $bad_actorTableName where call_sign = '$inp_call_sign'"; 
		$wpw1_cwa_bad_actor		= $wpdb->get_results($sql);
		if ($wpw1_cwa_bad_actor === FALSE) {
			handleWPDBError($jobname,$doDebug);
			$content		.= "Unable to obtain content from $bad_actorTableName<br />";
		} else {
			$numBARows			= $wpdb->num_rows;
			if ($doDebug) {
				$myStr			= $wpdb->last_query;
				echo "ran $myStr<br />and found $numBARows rows<br />";
			}
			if ($numBARows > 0) {
				foreach ($wpw1_cwa_bad_actor as $bad_actorRow) {
					$bad_actor_ID			= $bad_actorRow->record_id;
					$bad_actor_call_sign	= $bad_actorRow->call_sign;
					$bad_actor_info			= stripslashes($bad_actorRow->information);
					$bad_actor_status		= $bad_actorRow->status;
					$bad_actor_information	= $bad_actorRow->information;
					
					$thisStatus				= "Active";
					$changeStatus			= "Clicking 'Change Status' will set the status to Inactive";
					$inp_status				= 'I';
					if ($bad_actor_status == 'I') {
						$thisStatus			= "Inactive";
						$changeStatus		= "Clicking 'Change Status' will set the status to Active";
						$inp_status			= 'A';
					}
					$content				.= "$bad_actor_call_sign status is currently $thisStatus. $changeStatus</p>
														<form method='post' action='$theURL' 
														name='status_form' ENCTYPE='multipart/form-data'>
														<input type='hidden' name='strpass' value='15'>
														<input type='hidden' name='inp_id' value='$bad_actor_ID'>
														<input type='hidden' name='inp_call_sign' value='$bad_actor_call_sign'>
														<input type='hidden' name='inp_status' value='$inp_status'>
														Reason for change: <textarea class='formInputText' name='inp_reason' cols='50' rows='5'></textarea><br />
														<input class='formInputButton' type='submit' value='Change Status' /></form>";
 

				}
			} else {
				$content					.= "No record for $inp_call_sign found in $bad_actorTableName";
			}
		}
	} elseif ("15" == $strPass) {
		if ($doDebug) {
			echo "arrived at $strPass<br />";
		}
		$sql					= "select * from $bad_actorTableName where record_id = $inp_id"; 
		$wpw1_cwa_bad_actor		= $wpdb->get_results($sql);
		if ($wpw1_cwa_bad_actor === FALSE) {
			handleWPDBError($jobname,$doDebug);
			$content		.= "Unable to obtain content from $bad_actorTableName<br />";
		} else {
			$numBARows			= $wpdb->num_rows;
			if ($doDebug) {
				$myStr			= $wpdb->last_query;
				echo "ran $myStr<br />and found $numBARows rows<br />";
			}
			if ($numBARows > 0) {
				foreach ($wpw1_cwa_bad_actor as $bad_actorRow) {
					$bad_actor_ID			= $bad_actorRow->record_id;
					$bad_actor_call_sign	= $bad_actorRow->call_sign;
					$bad_actor_info			= stripslashes($bad_actorRow->information);
					$bad_actor_status		= $bad_actorRow->status;
					$bad_actor_information	= $bad_actorRow->information;
					
					$thisDate				= date('Y-m-d H:i');
					$bad_actor_information	.= "/ $thisDate $userName Change status to $inp_status. Reason: $inp_reason";
		
					$updateResult			= $wpdb->update($bad_actorTableName,
															array('status'=>$inp_status, 
																	'information'=>$bad_actor_information),
															array('call_sign'=>$inp_call_sign),
															array('%s','%s'),
															array('%s'));
					if ($updateResult === FALSE) {
						handleWPDBError($jobname,$doDebug);
					} else {
						$thisStatus			= "Active";
						if ($inp_status == 'I') {
							$thisStatus		= "Inactive";
						}
						$content			.= "<h3>$jobname</h3>
												<p>$inp_call_sign status has been changed to $thisStatus</p>";
					}
				}
			}
		}
	}	
	
	if ($strPass != "1") {
		$content		.= "<br /><br /><a href='$theURL'>Do It Again</a><br/>";
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
add_shortcode ('badActor', 'badActor_func');
