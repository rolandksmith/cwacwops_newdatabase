function show_advisor_class_assignments_v4_func() {

/*
	
	Provides an advisor the ability  to see the advisor's classes and
	student status.
	
	Requires the advisor call sign, email address, and phone number to gain access
	
	First looks in advisor by date_created descending to see if there are any records.
	If so, show the advisor record and class information followed by students (if any)
	Then show status of all past students	
	
	
	
	modified 27Aug2021 by Roland to include a link to update a student's status
	Modified 21Feb2022 by Roland to use tables rather than pods and to consolidate two 
		programs into one
	Modified 27Oct22 by Roland for the new timezone table format
	Modified 2Mar23 by Roland to show more info before students are assigned and 
		to show previous semester student status
	Modified 17Apr23 to fix action_log
	Extensively modified 30June23 by Roland and upgraded to V3
	Modified 6July23 to use consolidated tables
	Modified 28Aug23 by Roland to set replacement_status field if advisor wants no more students
	Modified 31Aug23 by Roland to turn off debug and testmode if valid user is N
	Modified 18Nov23 by Roland for the new advisor portal
	
*/	

	global $wpdb,$userName,$validTestmode;

	$doDebug					= TRUE;
	$testMode					= FALSE;
	$initializationArray 		= data_initialization_func();
	$validUser 						= $initializationArray['validUser'];
	if ($validUser == 'N') {				// turn off debug and testmode
		$doDebug					= FALSE;
		$testMode					= FALSE;
	}
	$versionNumber				= '4';
	$jobname					= "Show Advisor Class Assignments V$versionNumber";
	if ($doDebug) {
		echo "Initialization Array:<br /><pre>";
		print_r($initializationArray);
		echo "</pre><br />";
	}
	$validUser 					= $initializationArray['validUser'];
	$userName					= $initializationArray['userName'];
	$userRole					= $initializationArray['userRole'];
	$validTestmode				= $initializationArray['validTestmode'];
	$currentSemester			= $initializationArray['currentSemester'];
	$prevSemester				= $initializationArray['prevSemester'];
	$nextSemester				= $initializationArray['nextSemester'];
	$prevSemester				= $initializationArray['prevSemester'];
	$daysToSemester				= $initializationArray['daysToSemester'];
	$siteURL					= $initializationArray['siteurl'];	
	
	$proximateSemester			= $currentSemester;
	if ($proximateSemester == 'Not in Session') {
		$proximateSemester		= $nextSemester;
	}

	ini_set('memory_limit','256M');
	ini_set('display_errors','1');
	error_reporting(E_ALL);	

/// get the time that the process started
	$startingMicroTime			= microtime(TRUE);

	$theSemester				= $initializationArray['proximateSemester'];
	$strPass					= "1";
	$studentArray				= array();
	$studentDataArray			= array();
	$totalStudents				= 0;
	$advisor_semester			= '';
	$inp_verified				= 'N';
	$inp_call_sign				= "";
	$inp_email					= "";
	$inp_phone					= "";
	$userName					= $initializationArray['userName'];
	$advisorVerificationURL		= "$siteURL/cwa-advisor-verification-of-student/";
	$promotableArray			= array('P'=>'Yes',
										'N'=>'No',
										'W'=>'Withdrew',
										''=>'');
	$statusArray				= array('Y'=>'Verified',
										'S'=>'Assigned',
										'C'=>'Dropped');
	
// get the input information
	if (isset($_REQUEST)) {
		foreach($_REQUEST as $str_key => $str_value) {
			if ($doDebug) {
				if (is_array($str_value) === FALSE) {
					echo "Key: $str_key | Value: $str_value <br />\n";
				}
			}
			if ($str_key 		== "strpass") {
				$strPass		 = $str_value;
				$strPass		 = filter_var($strPass,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "inp_mode") {
				$inp_mode		 = $str_value;
				$inp_mode		 = filter_var($inp_mode,FILTER_UNSAFE_RAW);
				if ($inp_mode == 'TESTMODE') {
					$testMode	= TRUE;
				}
			}
			if ($str_key 		== "inp_verbose") {
				$inp_verbose	 = $str_value;
				$inp_verbose	 = filter_var($inp_verbose,FILTER_UNSAFE_RAW);
				if ($inp_verbose == 'Y') {
					$doDebug	= TRUE;
				}
			}
			if ($str_key 		== "submit") {
				$submit	 = $str_value;
				$submit	 = filter_var($submit,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "inp_call_sign") {
				$inp_call_sign	 = $str_value;
				$inp_call_sign	 = filter_var($inp_call_sign,FILTER_UNSAFE_RAW);
				$inp_call_sign	= strtoupper($inp_call_sign);
			}
			if ($str_key 		== "inp_email") {
				$inp_email	 = strtolower($str_value);
				$inp_email	 = filter_var($inp_email,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "inp_phone") {
				$inp_phone	 = $str_value;
				$inp_phone	 = filter_var($inp_phone,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "advisor_semester") {
				$advisor_semester	 = $str_value;
				$advisor_semester	 = filter_var($advisor_semester,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "inp_verified") {
				$inp_verified	 = $str_value;
				$inp_verified	 = filter_var($inp_verified,FILTER_UNSAFE_RAW);
			}
		}
	}

	$theURL							= "$siteURL/cwa-show-advisor-class-assignments/";
	$errorArray						= array();

	if ($testMode) {
		$studentTableName			= 'wpw1_cwa_consolidated_student2';
		$advisorTableName			= 'wpw1_cwa_consolidated_advisor2';
		$advisorClassTableName		= 'wpw1_cwa_consolidated_advisorclass2';
		$audioAssessmentTableName	= 'wpw1_cwa_audio_assessment2';
		$thisMode					= 'TM';
		$theStatement				= "<p>Function is running in TEST MODE using test files.</p>";
	} else {
		$studentTableName			= 'wpw1_cwa_consolidated_student';
		$advisorTableName			= 'wpw1_cwa_consolidated_advisor';
		$advisorClassTableName		= 'wpw1_cwa_consolidated_advisorclass';
		$audioAssessmentTableName	= 'wpw1_cwa_audio_assessment';
		$thisMode					= 'PM';
		$theStatement				= "";
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

	
	if ("1" == $strPass) {
		if ($doDebug) {
			echo "Function starting.<br />";
		}
		// set the userName
		if ($userRole == 'advisor') {
			$userName		= strtoupper($userName);
			$inp_callsign	= $userName;
			$strPass		= "2";
		} elseif ($userRole == 'administrator') {
			$content		.= "<h3>$jobname Administrator Role</h3>
								<form method='post' action='$theURL' 
								name='selection_form' ENCTYPE='multipart/form-data''>
								<input type='hidden' name='strpass' value='2'>
								Call Sign: <br />
								<table style='border-collapse:collapse;'>
								<tr><td>Advisor Call Sign</td>
									<td><input type='text' class='formInputText' name='inp_call_sign' size='10' maxlength='10' autofocus></td>
								$testModeOption
								<tr><td>&nbsp;</td><td><input class='formInputButton' type='submit' value='Next' /></td></tr></table>
								</form>";
		}
	}

		
		
		
	if ("2" == $strPass) {
	
		if ($doDebug) {
			echo "At pass 2 with<br />
				  inp_call_sign: $inp_call_sign<br />";
		}
		
		$doProceed					= TRUE;
		$foundPrevClass				= FALSE;
		$gotAdvisor					= FALSE;
		$firstAdvisorRecord			= TRUE;
				
		$gotAdvisor						= FALSE;
		$thisCallSign					= '';
		$thisEmail						= '';
		$thisPhone						= '';
		/// get the advisor record. If not found say so and quit
		$sql 					= "select * from $advisorTableName 
									where call_sign='$inp_call_sign' 
									order by date_created DESC 
									limit 1";
		$wpw1_cwa_advisor	= $wpdb->get_results($sql);
		if ($wpw1_cwa_advisor === FALSE) {
			$myError			= $wpdb->last_error;
			$myQuery			= $wpdb->last_query;
			if ($doDebug) {
				echo "Reading $advisorTableName table failed<br />
					  wpdb->last_query: $myQuery<br />
					  wpdb->last_error: $myError<br />";
			}
			$errorMsg			= "$jobname Reading $advisorTableName table failed. <p>SQL: $myQuery</p><p> Error: $myError</p>";
			sendErrorEmail($errorMsg);
			$content		.= "Unable to obtain content from $advisorTableName<br />";
		} else {
			$numARows			= $wpdb->num_rows;
			if ($doDebug) {
				$myStr			= $wpdb->last_query;
				echo "ran $myStr<br />and found $numARows rows in $advisorTableName table<br />";
			}
			if ($numARows > 0) {
				foreach ($wpw1_cwa_advisor as $advisorRow) {
					$advisor_ID							= $advisorRow->advisor_id;
					$advisor_select_sequence 			= $advisorRow->select_sequence;
					$advisor_call_sign 					= strtoupper($advisorRow->call_sign);
					$advisor_first_name 				= $advisorRow->first_name;
					$advisor_last_name 					= stripslashes($advisorRow->last_name);
					$advisor_email 						= strtolower($advisorRow->email);
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
					$gotAdvisor							= TRUE;
					$gotAdvisorN						= TRUE;
					$thisCallSign						= $advisor_call_sign;
					$thisEmail							= $advisor_email;
					$thisPhone							= substr($advisor_phone,-4,4);
					$inp_semester						= $advisor_semester;
				}
			}
		}
		if ($gotAdvisor) {
			if ($doDebug) {
				echo "gotAdvisor is TRUE<br />\n";
			}
			// check to see if this is the right advisor
			$doProceed				= TRUE;
			if ($doProceed) {				
				if ($doDebug) {
					echo "getting advisor records<br />";
				}
				$content			.= "<h4>Show Advisor Class Assignments for $inp_call_sign</h4>\n";
				// get the advisor records combined with the advisorclass records (if any) and display
				$sql				= "select call_sign, 
											  first_name, 
											  last_name, 
											  semester, 
											  email, 
											  ph_code, 
											  phone, 
											  city, 
											  state, 
											  zip_code, 
											  country, 
											  text_message, 
											  whatsapp_app, 
											  signal_app, 
											  telegram_app, 
											  messenger_app 
									   from $advisorTableName 
									   where call_sign='$inp_call_sign' 
									   order by date_created DESC 
									   limit 2";
				$wpw1_cwa_advisor	= $wpdb->get_results($sql);
				if ($wpw1_cwa_advisor === FALSE) {
					$myError			= $wpdb->last_error;
					$myQuery			= $wpdb->last_query;
					if ($doDebug) {
						echo "Reading $advisorTableName table failed<br />
							  wpdb->last_query: $myQuery<br />
							  wpdb->last_error: $myError<br />";
					}
					$errorMsg			= "$jobname Reading $advisorTableName table failed. <p>SQL: $myQuery</p><p> Error: $myError</p>";
					sendErrorEmail($errorMsg);
					$content		.= "Unable to obtain content from $advisorTableName<br />";
				} else {
					$numARows			= $wpdb->num_rows;
					if ($doDebug) {
						$myStr			= $wpdb->last_query;
						echo "ran $myStr<br />and found $numARows rows in $advisorTableName table<br />";
					}
					if ($numARows > 0) {
						foreach ($wpw1_cwa_advisor as $advisorRow) {
							$advisor_call_sign 					= strtoupper($advisorRow->call_sign);
							$advisor_first_name 				= $advisorRow->first_name;
							$advisor_last_name 					= stripslashes($advisorRow->last_name);
							$advisor_email 						= strtolower($advisorRow->email);
							$advisor_phone						= $advisorRow->phone;
							$advisor_ph_code					= $advisorRow->ph_code;				// new
							$advisor_city 						= $advisorRow->city;
							$advisor_state 						= $advisorRow->state;
							$advisor_zip_code 					= $advisorRow->zip_code;
							$advisor_country 					= $advisorRow->country;
							$advisor_whatsapp					= $advisorRow->whatsapp_app;		// new
							$advisor_signal						= $advisorRow->signal_app;			// new
							$advisor_telegram					= $advisorRow->telegram_app;		// new
							$advisor_messenger					= $advisorRow->messenger_app;		// new
							$advisor_semester 					= $advisorRow->semester;
							$advisor_text_message 				= $advisorRow->text_message;

							$advisor_last_name 					= no_magic_quotes($advisor_last_name);
							if ($doDebug) {
								echo "have advisor record for $advisor_semester. Displaying advisor info<br />";
							}


							$content	.= "<p><h4>$advisor_semester Registration</h4> 
												<table style='width:900px;'>
												<tr><td style='vertical-align:top;width:300px;'><b>Call Sign</b><br />
														$advisor_call_sign</td>
													<td style='vertical-align:top;width:300px;'><b>Last Name</b><br />
														$advisor_last_name</td>
													<td style='vertical-align:top;'><b>First Name</b><br />
														$advisor_first_name</td></tr>
												<tr><td style='vertical-align:top;'><b>Semester</b><br />
														$advisor_semester</td>
													<td style='vertical-align:top;'><b>Email</b><br />
														$advisor_email</td>
													<td style='vertical-align:top;'><b>Phone</b>
														$advisor_ph_code $advisor_phone<br />
														Can this phone receive text messages?<br />
														$advisor_text_message</td></tr>
												<tr><td style='vertical-align:top;'><b>City</b><br />
														$advisor_city</td>
													<td style='vertical-align:top;'><b>State / Region / Province</b><br />
														$advisor_state</td>
													<td style='vertical-align:top;'><b>Zip / Postal Code</b><br />
														$advisor_zip_code<br />
														</td></tr>

												<tr><td colspan='3' style='vertical-align:top;'>
														<b>Country</b><br />
														$advisor_country ($advisor_country_code)</td>
													<td></td>
													<td></td></tr>
												<tr><td colspan='3'>Other messaging services:</td></tr>
												<tr><td colspan='3'>
													<table>
														<tr><td style='vertical-align:top;'><b>Whatsapp</b><br />
																$advisor_whatsapp</td>
															<td style='vertical-align:top;'><b>Signal</b><br />
																$advisor_signal</td>
															<td style='vertical-align:top;'><b>Telegram</b><br />
																$advisor_telegram</td>
															<td style='vertical-align:top;'><b>Messenger</b><br />
																$advisor_messenger'</td></tr>
													</table></td></tr></table>";
							
							// now get the advisorclass record, display it, and then display any students				  
							if ($doDebug) {
								echo "now getting the advisorclass records<br />";
							}
							$sql	= "select * from $advisorClassTableName 
									where advisor_call_sign = '$advisor_call_sign' 
									  and semester = '$advisor_semester' 
									order by sequence";


							$wpw1_cwa_advisorclass				= $wpdb->get_results($sql);
							if ($wpw1_cwa_advisorclass === FALSE) {
								$myError			= $wpdb->last_error;
								$myQuery			= $wpdb->last_query;
								if ($doDebug) {
									echo "Reading $advisorClassTableName table failed<br />
										  wpdb->last_query: $myQuery<br />
										  wpdb->last_error: $myError<br />";
								}
								$errorMsg			= "$jobname Reading $advisorClassTableName table failed. <p>SQL: $myQuery</p><p> Error: $myError</p>";
								sendErrorEmail($errorMsg);
							} else {
								$numACRows						= $wpdb->num_rows;
								if ($doDebug) {
									$myStr						= $wpdb->last_query;
									echo "ran $myStr<br />and found $numACRows rows<br />";
								}
								if ($numACRows > 0) {
									foreach ($wpw1_cwa_advisorclass as $advisorClassRow) {
										$advisorClass_ID				 		= $advisorClassRow->advisorclass_id;
										$advisorClass_advisor_call_sign 		= $advisorClassRow->advisor_call_sign;
										$advisorClass_advisor_first_name 		= $advisorClassRow->advisor_first_name;
										$advisorClass_advisor_last_name 		= stripslashes($advisorClassRow->advisor_last_name);
										$advisorClass_advisor_id 				= $advisorClassRow->advisor_id;
										$advisorClass_sequence 					= $advisorClassRow->sequence;
										$advisorClass_semester 					= $advisorClassRow->semester;
										$advisorClass_timezone 					= $advisorClassRow->time_zone;
										$advisorClass_timezone_id				= $advisorClassRow->timezone_id;		// new
										$advisorClass_timezone_offset			= $advisorClassRow->timezone_offset;	// new
										$advisorClass_level 					= $advisorClassRow->level;
										$advisorClass_class_size 				= $advisorClassRow->class_size;
										$advisorClass_class_schedule_days 		= $advisorClassRow->class_schedule_days;
										$advisorClass_class_schedule_times 		= $advisorClassRow->class_schedule_times;
										$advisorClass_class_schedule_days_utc 	= $advisorClassRow->class_schedule_days_utc;
										$advisorClass_class_schedule_times_utc 	= $advisorClassRow->class_schedule_times_utc;
										$advisorClass_action_log 				= $advisorClassRow->action_log;
										$advisorClass_class_incomplete 			= $advisorClassRow->class_incomplete;
										$advisorClass_date_created				= $advisorClassRow->date_created;
										$advisorClass_date_updated				= $advisorClassRow->date_updated;
										$advisorClass_student01 				= $advisorClassRow->student01;
										$advisorClass_student02 				= $advisorClassRow->student02;
										$advisorClass_student03 				= $advisorClassRow->student03;
										$advisorClass_student04 				= $advisorClassRow->student04;
										$advisorClass_student05 				= $advisorClassRow->student05;
										$advisorClass_student06 				= $advisorClassRow->student06;
										$advisorClass_student07 				= $advisorClassRow->student07;
										$advisorClass_student08 				= $advisorClassRow->student08;
										$advisorClass_student09 				= $advisorClassRow->student09;
										$advisorClass_student10 				= $advisorClassRow->student10;
										$advisorClass_student11 				= $advisorClassRow->student11;
										$advisorClass_student12 				= $advisorClassRow->student12;
										$advisorClass_student13 				= $advisorClassRow->student13;
										$advisorClass_student14 				= $advisorClassRow->student14;
										$advisorClass_student15 				= $advisorClassRow->student15;
										$advisorClass_student16 				= $advisorClassRow->student16;
										$advisorClass_student17 				= $advisorClassRow->student17;
										$advisorClass_student18 				= $advisorClassRow->student18;
										$advisorClass_student19 				= $advisorClassRow->student19;
										$advisorClass_student20 				= $advisorClassRow->student20;
										$advisorClass_student21 				= $advisorClassRow->student21;
										$advisorClass_student22 				= $advisorClassRow->student22;
										$advisorClass_student23 				= $advisorClassRow->student23;
										$advisorClass_student24 				= $advisorClassRow->student24;
										$advisorClass_student25 				= $advisorClassRow->student25;
										$advisorClass_student26 				= $advisorClassRow->student26;
										$advisorClass_student27 				= $advisorClassRow->student27;
										$advisorClass_student28 				= $advisorClassRow->student28;
										$advisorClass_student29 				= $advisorClassRow->student29;
										$advisorClass_student30 				= $advisorClassRow->student30;
										$class_number_students					= $advisorClassRow->number_students;
										$class_evaluation_complete 				= $advisorClassRow->evaluation_complete;
										$class_comments							= $advisorClassRow->class_comments;
										$copycontrol							= $advisorClassRow->copy_control;

										if ($doDebug) {
											echo "have advisorclass sequence $advisorClass_sequence record<br />";
										}
										$content	.= "<b>Class $advisorClass_sequence:</b>
														<table style='width:900px;'>
														<tr><td style='vertical-align:top;width:300px;'><b>Sequence</b><br />
																$advisorClass_sequence</td>
															<td style='vertical-align:top;width:300px;'><b>Level</b><br />
																$advisorClass_level</td>
															<td style='vertical-align:top;'><b>Class Size</b><br />
																$advisorClass_class_size</td></tr>
														<tr><td style='vertical-align:top;'><b>Class Teaching Days</b><br />
																$advisorClass_class_schedule_days</td>
															<td style='vertical-align:top;' colspan='2'><b>Local Start Time</b><br />
																$advisorClass_class_schedule_times</td></tr></table>";

										// if there are students, then show the student name list
										if ($doDebug) {
											echo "there are $class_number_students students in the class<br />";
										}
										$displayClass			= FALSE;										
										if ($class_number_students > 0) {	
											// put out the student header
											$content				.= "<table style='border-collapse:collapse;'>";
											
											$daysToGo				= days_to_semester($advisor_semester);
											if($doDebug) {
												echo "preparing to display student info. Days to $advisor_semester semester: $daysToGo<br />";
											}
											if ($daysToGo > 0 && $daysToGo < 21) {
												$content				.= "<p>Students have been assigned to classes for the $advisor_semester semester. 
																			The semester has not yet started. You have been assigned the 
																			following students:</p>\n";
												$displayClass		= TRUE;
											} elseif ($daysToGo < 0 && $daysToGo > 20) {
												$content				.= "<p>Students have not yet been assigned to advisor classes.</p>";
											} elseif ($daysToGo <0 && $daysToGo > -60) {								
												$content				.= "<p>Students have been assigned to classes for the $advisor_semester semester. 
																			The semester is underway. You have been assigned the 
																			following students:</p>\n";
												$displayClass			= TRUE;
											} else {
												$content				.= "<p>Students have been assigned to classes for the $advisor_semester semester. 
																			The semester is completed. You were assigned the 
																			following students:</p>\n";
												$displayClass			= TRUE;
											}
											if ($displayClass) {
												if ($doDebug) {
													echo "displaying class information<br />";
												}
												$studentCount			= 0;
												for ($snum=1;$snum<31;$snum++) {
													if ($snum < 10) {
														$strSnum 		= str_pad($snum,2,'0',STR_PAD_LEFT);
													} else {
														$strSnum		= strval($snum);
													}
													$theInfo			= ${'advisorClass_student' . $strSnum};
													if ($theInfo != '') {
														if ($doDebug) {
															echo "<br />processing student$strSnum $theInfo<br />";
														}
														$wpw1_cwa_student				= $wpdb->get_results("select * from $studentTableName 
																												where semester='$advisor_semester' 
																												and call_sign = '$theInfo'");
														if ($wpw1_cwa_student === FALSE) {
															$myError			= $wpdb->last_error;
															$myQuery			= $wpdb->last_query;
															if ($doDebug) {
																echo "Reading $studentTableName table failed<br />
																	  wpdb->last_query: $myQuery<br />
																	  wpdb->last_error: $myError<br />";
															}
															$errorMsg			= "$jobname reading $studentTableName failed. <p>SQL: $myQuery</p><p> Error: $myError</p>";
															sendErrorEmail($errorMsg);
														} else {
															$numSRows									= $wpdb->num_rows;
															if ($doDebug) {
																$myStr					= $wpdb->last_query;
																echo "ran $myStr<br />and found $numSRows rows<br />";
															}
															if ($numSRows > 0) {
																foreach ($wpw1_cwa_student as $studentRow) {
																	$student_ID								= $studentRow->student_id;
																	$student_call_sign						= strtoupper($studentRow->call_sign);
																	$student_first_name						= $studentRow->first_name;
																	$student_last_name						= stripslashes($studentRow->last_name);
																	$student_email  						= strtolower(strtolower($studentRow->email));
																	$student_phone  						= $studentRow->phone;
																	$student_ph_code						= $studentRow->ph_code;
																	$student_city  							= $studentRow->city;
																	$student_state  						= $studentRow->state;
																	$student_zip_code  						= $studentRow->zip_code;
																	$student_country  						= $studentRow->country;
																	$student_country_code					= $studentRow->country_code;
																	$student_time_zone  					= $studentRow->time_zone;
																	$student_timezone_id					= $studentRow->timezone_id;
																	$student_timezone_offset				= $studentRow->timezone_offset;
																	$student_whatsapp						= $studentRow->whatsapp_app;
																	$student_signal							= $studentRow->signal_app;
																	$student_telegram						= $studentRow->telegram_app;
																	$student_messenger						= $studentRow->messenger_app;					
																	$student_wpm 	 						= $studentRow->wpm;
																	$student_youth  						= $studentRow->youth;
																	$student_age  							= $studentRow->age;
																	$student_student_parent 				= $studentRow->student_parent;
																	$student_student_parent_email  			= strtolower($studentRow->student_parent_email);
																	$student_level  						= $studentRow->level;
																	$student_waiting_list 					= $studentRow->waiting_list;
																	$student_request_date  					= $studentRow->request_date;
																	$student_semester						= $studentRow->semester;
																	$student_notes  						= $studentRow->notes;
																	$student_welcome_date  					= $studentRow->welcome_date;
																	$student_email_sent_date  				= $studentRow->email_sent_date;
																	$student_email_number  					= $studentRow->email_number;
																	$student_response  						= strtoupper($studentRow->response);
																	$student_response_date  				= $studentRow->response_date;
																	$student_abandoned  					= $studentRow->abandoned;
																	$student_student_status  				= strtoupper($studentRow->student_status);
																	$student_action_log  					= $studentRow->action_log;
																	$student_pre_assigned_advisor  			= $studentRow->pre_assigned_advisor;
																	$student_selected_date  				= $studentRow->selected_date;
																	$student_no_catalog			 			= $studentRow->no_catalog;
																	$student_hold_override  				= $studentRow->hold_override;
																	$student_messaging  					= $studentRow->messaging;
																	$student_assigned_advisor  				= $studentRow->assigned_advisor;
																	$student_advisor_select_date  			= $studentRow->advisor_select_date;
																	$student_advisor_class_timezone 		= $studentRow->advisor_class_timezone;
																	$student_hold_reason_code  				= $studentRow->hold_reason_code;
																	$student_class_priority  				= $studentRow->class_priority;
																	$student_assigned_advisor_class 		= $studentRow->assigned_advisor_class;
																	$student_promotable  					= $studentRow->promotable;
																	$student_excluded_advisor  				= $studentRow->excluded_advisor;
																	$student_student_survey_completion_date	= $studentRow->student_survey_completion_date;
																	$student_available_class_days  			= $studentRow->available_class_days;
																	$student_intervention_required  		= $studentRow->intervention_required;
																	$student_copy_control  					= $studentRow->copy_control;
																	$student_first_class_choice  			= $studentRow->first_class_choice;
																	$student_second_class_choice  			= $studentRow->second_class_choice;
																	$student_third_class_choice  			= $studentRow->third_class_choice;
																	$student_first_class_choice_utc  		= $studentRow->first_class_choice_utc;
																	$student_second_class_choice_utc  		= $studentRow->second_class_choice_utc;
																	$student_third_class_choice_utc  		= $studentRow->third_class_choice_utc;
																	$student_date_created 					= $studentRow->date_created;
																	$student_date_updated			  		= $studentRow->date_updated;


																	if ($doDebug) {
																		echo "<br />Processing student $student_call_sign<br />
																				&nbsp;&nbsp;&nbsp;&nbsp;Level: $student_level<br />
																				&nbsp;&nbsp;&nbsp;&nbsp;Class: $student_assigned_advisor_class<br />
																				&nbsp;&nbsp;&nbsp;&nbsp;Status: $student_student_status<br />
																				&nbsp;&nbsp;&nbsp;&nbsp;Promotable: $student_promotable<br />";
																	}
																	$studentCount++;
																	$content			.= "<tr><td style='vertical-align:top;width:100px;'><b>Call Sign</b></td>
																								<td style='vertical-align:top;width:150px;'><b>Name</b></td>
																								<td style='vertical-align:top;width:200px;'><b>Email</b></td>
																								<td style='vertical-align:top;width:200px;'><b>Phone</b></td>
																								<td style='vertical-align:top;width:100px;'><b>Country</b></td>
																								<td style='vertical-align:top;width:100px;'><b>Verified</b></td>
																								<td style='vertical-align:top;width:100px;'><b>Promotable</b></td></tr>";
																	/// check to see if there are assessment records for this student
																	if ($doDebug) {
																		echo "looking for audio assessment records<br />";
																	}
																	$hasAssessment			= FALSE;
																	$assessment_count	= $wpdb->get_var("select count(record_id) 
																							   from $audioAssessmentTableName 
																								where call_sign='$student_call_sign'");
																	if ($assessment_count > 0) {
																		$hasAssessment	= TRUE;
																		if ($doDebug) {
																			echo "have assessment records<br />";
																		}
																	}
																	$extras							= "Additional contact options: ";
																	$haveExtras						= FALSE;
																	if ($student_whatsapp != '' ) {
																		$extras						.= "WhatsApp: $student_whatsapp ";
																		$haveExtras					= TRUE;
																	}
																	if ($student_signal != '' ) {
																		$extras						.= "Signal: $student_signal ";
																		$haveExtras					= TRUE;
																	}
																	if ($student_telegram != '' ) {
																		$extras						.= "Telegram: $student_telegram ";
																		$haveExtras					= TRUE;
																	}
																	if ($student_messenger != '' ) {
																		$extras						.= "Messenger: $student_messenger ";
																		$haveExtras					= TRUE;
																	}
									

																	$myStr							= "";
																	if ($student_student_status == 'S') {
																		$myStr						= " <b>Unverified</b>";
																	} elseif ($student_student_status == 'Y') {
																		$myStr						= "Verified";
																	}
																	if ($doDebug) {
																		echo "displaying student $student_call_sign information<br />";
																	}
																	$dispPromotable		= $promotableArray[$student_promotable];
																	$content						.= "<tr><td style='vertical-align:top;'>$student_call_sign</td>
																											<td style='vertical-align:top;'>$student_last_name, $student_first_name</td>
																											<td style='vertical-align:top;'>$student_email</td>
																											<td style='vertical-align:top;'>+$student_ph_code $student_phone ($student_messaging)</td>
																											<td style='vertical-align:top;'>$student_country</td>
																											<td style='vertical-align:top;'>$myStr</td>
																											<td style='vertical-align:top;'>$dispPromotable</td></tr>";
																	if ($haveExtras) {
																		$content					.= "<tr><td colspan='7'>$extras</td></tr>";
																	}
																	$thisParent			= '';
																	$thisParentEmail	= '';
																	if ($student_youth == 'Yes') {
																		if ($student_age < 18) { 
																			if ($student_student_parent == '') {
																				$thisParent	= 'Not Given';
																			} else {
																				$thisParent	= $student_student_parent;
																			}
																			if ($student_student_parent_email == '') {
																				$thisParentEmail = 'Not Given';
																			} else {
																				$thisParentEmail = $student_student_parent_email;
																			}
																			$content	.= "<tr><td colspan='7'>The student has registered as a youth under the age of 18. The student's 
																							parent or guardian is $thisParent at email address $thisParentEmail.</td></tr>";
																		}
																	}

																	if ($hasAssessment) {
																		$enstr		= base64_encode("advisor_call_sign=$student_assigned_advisor&inp_callsign=$student_call_sign");
																		$content	.= "<tr><td colspan='7' style='border-bottom: 1px solid #000;'>Click <a href='$siteURL/cwa-view-a-student-cw-assessment-v2/?strpass=2&enstr=$enstr' target='_blank'>HERE</a> to review $student_call_sign's self assessment</td></tr>";
																	} else {
																		$content	.= "<tr><td colspan='7' style='border-bottom: 1px solid #000;'>&nbsp;</td></tr>";
																	}
																}	/// end of the student foreach
															} else {		/// no student record found ... send error message
																sendErrorEmail("Prepare Advisor Class Display: no $studentTableName table record found for student$strSnum in advisor $advisorClass_advisor_call_sign class $advisorClass_sequence");
															}
														}	
													}
												}
												$content				.= "</table>$studentCount Students<br /><br />";
												if ($doDebug) {
													echo "have processed all students for this class<br /><br />";
												}
											}
										} else {
											$content					.= "<p>No students are currently assigned to this class.</p>";
											if ($doDebug) {
												echo "No students assigned to this class<br /><br />";
											}
										}
									}
								}
							}
						}	
					} else {
						$content				.= "<p>You have not currently registered as an advisor.</p>";
						if ($doDebug) {
							echo "not currently registered as an advisor<br /><br />";
						}
					}
				}
			

				// get all students for this advisor and show their status
				$studentArray		= array();			// call_sign => name|semester

				if ($doDebug) {
					echo "<br /><b>Getting Students for $inp_call_sign advisor</b><br />";
				}
				
				// get this advisor's students from the student table
				$sql				= "select call_sign, 
											  first_name, 
											  last_name, 
											  semester 
									   from $studentTableName 
									   where assigned_advisor = '$inp_call_sign'";
				$wpw1_cwa_student		= $wpdb->get_results($sql);
				if ($wpw1_cwa_student === FALSE) {
					$myError			= $wpdb->last_error;
					$myQuery			= $wpdb->last_query;
					if ($doDebug) {
						echo "Reading $studentTableName table failed<br />
							  wpdb->last_query: $myQuery<br />
							  wpdb->last_error: $myError<br />";
					}
					$errorMsg			= "$jobname reading $studentTableName failed. <p>SQL: $myQuery</p><p> Error: $myError</p>";
					sendErrorEmail($errorMsg);
					$content		.= "Unable to obtain content from $studentTableName<br />";
				} else {
					$numSRows			= $wpdb->num_rows;
					if ($doDebug) {
						$myStr			= $wpdb->last_query;
						echo "ran $myStr<br />and found $numSRows rows<br />";
					}
					if ($numSRows > 0) {
						foreach ($wpw1_cwa_student as $studentRow) {
							$student_call_sign						= trim(strtoupper($studentRow->call_sign));
							$student_semester						= $studentRow->semester;
							$student_first_name						= $studentRow->first_name;
							$student_last_name						= $studentRow->last_name;
							
							if ($doDebug) {
								echo "have student $student_call_sign for semester $student_semester<br />";
							}
							if (!array_key_exists($student_call_sign,$studentArray)) {
								$studentArray[$student_call_sign]	= "$student_last_name, $student_first_name";
								if ($doDebug) {
									echo "added $student_call_sign => $student_last_name, $student_first_name to studentArray<br />";
								}
							}
						}
					}
				}

				// have all the students. Sort the studentArray
				ksort($studentArray);
				if($doDebug) {
					$myInt		= count($studentArray);
					echo "<br />have the student array with $myInt records:<br /><pre>";
					print_r($studentArray);	
					echo "</pre><br />";
				}
				
				// now show all past students and their progression
				$content		.= "<h4>$inp_call_sign Past Students</h4>
									<table>
									<tr><th style='width:250px;'>Student</th>
										<th>Beginner</th>
										<th>Fundamental</th>
										<th>Intermediate</th>
										<th>Advanced</th></tr>";
				$droppedCount		= 0;
				$notPromotableCount	= 0;
				$promotableCount	= 0;
				$withdrewCount		= 0;
				$notEvaluated		= 0;

				foreach($studentArray as $thisCallSign=>$thisName) {
					$BeginnerColumn		= '';
					$IntermediateColumn	= '';
					$FundamentalColumn	= '';
					$AdvancedColumn		= '';
					
					$classInfo			= get_all_classes_for_student($thisCallSign,$doDebug,$testMode);
					if ($doDebug) {
						echo "have classInfo for $thisCallSign:<br /><pre>";
						print_r($classInfo);
						echo "</pre><br />";
					}
					foreach($classInfo as $thisSequence=>$thisValue1) {
						foreach($thisValue1 as $thisSemester=>$thisValue2) {
							foreach($thisValue2 as $thisLevel=>$thisValue3) {
								$advisor = $thisValue3['advisor'];
								$class = $thisValue3['class'];
								$status = $thisValue3['status'];
								$promotable = $thisValue3['promotable'];
																	
								$thisPromotable	= $promotableArray[$promotable];
								$thisStatus		= $statusArray[$status];

								$dispAdvisor	= $advisor;
								if ($advisor == $inp_call_sign) {
									$dispAdvisor	= "<b>$advisor</b>";
									if ($status == 'C') {
										$droppedCount++;
									}
									if ($promotable == 'N') {
										$notPromotableCount++;
									} elseif ($promotable == 'P') {
										$promotableCount++;
									} elseif ($promotable== 'W') {
										$withdrewCount++;
									} else {
										if ($status != 'C') {
											$notEvaluated++;
										}
									}
								}
								
								${$thisLevel . 'Column'}	.= "$thisSemester<br />
																$dispAdvisor $class<br />
																$thisStatus<br />
																$thisPromotable<br /><br />";
							}
						}
					}
					$content	.= "<tr><td style='vertical-align:top;border-bottom: 1px solid #000;'>$thisName ($thisCallSign)</td>
										<td style='vertical-align:top;border-bottom: 1px solid #000;'>$BeginnerColumn</td>
										<td style='vertical-align:top;border-bottom: 1px solid #000;'>$FundamentalColumn</td>
										<td style='vertical-align:top;border-bottom: 1px solid #000;'>$IntermediateColumn</td>
										<td style='vertical-align:top;border-bottom: 1px solid #000;'>$AdvancedColumn</td></tr>";
				}
				$myInt 		= count($studentArray);
				$content	.= "</table>
								<p>$myInt students for advisor $inp_call_sign</p>
								<p>$droppedCount Students Dropped<br />
								$promotableCount Promotable Students<br />
								$notPromotableCount Not promotable Students<br />
								$withdrewCount Students Who Withdrew";
				if ($notEvaluated > 0) {
					$content	.= "$notEvaluated Students Not Evaluated<br />";
				}

			} else {
				$content		.= "<p>You are not authorized</p>";
			}
		} else {
			$content			.= "<h3>Advisor Class Assignment</h3><p>No record found for advisor $inp_call_sign<br />You may close this window</p>";
		}
	}

	$thisTime 					= date('Y-m-d H:i:s');
	$content 		.= "<br /><br /><a href='$siteURL/program-list/'>Return to Student Portal</a>
						<br /><br /><p>V$versionNumber. Prepared at $thisTime</p>";
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
add_shortcode ('show_advisor_class_assignments_v4', 'show_advisor_class_assignments_v4_func');
