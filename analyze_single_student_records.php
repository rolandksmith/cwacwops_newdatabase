function analyze_single_student_records_func() {

//	prepares counts of students who took one class only

	global $wpdb, $doDebug, $debugLog;

	$doDebug						= FALSE;
	$testMode						= FALSE;
	$context = CWA_Context::getInstance();
	$validUser 						= $context->validUser;

	$versionNumber				 	= "1";
	$userName			= $context->userName;
	$currentTimestamp	= $context->currentTimestamp;
	$validTestmode		= $context->validTestmode;
	$siteURL			= $context->siteurl;
	$userEmail			= $context->userEmail;
	$userDisplayName	= $context->userDisplayName;
	$userRole			= $context->userRole;
	
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
	$theURL						= "$siteURL/cwa-analyze-single-student-records/";
	$inp_semester				= '';
	$inp_rsave					= '';
	$jobname					= "Analyze Single Student Records";
	$debugLog					= "";
	
	$noReason = "";
	$student_total = 0;
	
	$r_Beginner = 0;
	$r_Fundamental = 0;
	$r_Intermediate = 0;
	$r_Advanced = 0;
	$r_total = 0;
	$r_Beginner_students = "";
	$r_Fundamental_students = "";
	$r_Intermediate_students = "";
	$r_Advanced_students = "";
	
	$dropped_Beginner = 0;
	$dropped_Fundamental = 0;
	$dropped_Intermediate = 0;
	$dropped_Advanced = 0;
	$dropped_total = 0;
	$dropped_Beginner_students = "";
	$dropped_Fundamental_students = "";
	$dropped_Intermediate_students = "";
	$dropped_Advanced_students = "";
	
	$C_Beginner = 0;
	$C_Fundamental = 0;
	$C_Intermediate = 0;
	$C_Advanced = 0;
	$C_total = 0;
	$C_Beginner_students = "";
	$C_Fundamental_students = "";
	$C_Intermediate_students = "";
	$C_Advanced_students = "";
	
	$Y_Beginner = 0;
	$Y_Fundamental = 0;
	$Y_Intermediate = 0;
	$Y_Advanced = 0;
	$Y_total = 0;
	$Y_Beginner_students = "";
	$Y_Fundamental_students = "";
	$Y_Intermediate_students = "";
	$Y_Advanced_students = "";

	$Y_P_Beginner = 0;
	$Y_P_Fundamental = 0;
	$Y_P_Intermediate = 0;
	$Y_P_Advanced = 0;
	$Y_P_total = 0;
	$Y_P_Beginner_students = "";
	$Y_P_Fundamental_students = "";
	$Y_P_Intermediate_students = "";
	$Y_P_Advanced_students = "";

	$Y_W_Beginner = 0;
	$Y_W_Fundamental = 0;
	$Y_W_Intermediate = 0;
	$Y_W_Advanced = 0;
	$Y_W_total = 0;
	$Y_W_Beginner_students = "";
	$Y_W_Fundamental_students = "";
	$Y_W_Intermediate_students = "";
	$Y_W_Advanced_students = "";

	
	$N_Beginner = 0;
	$N_Fundamental = 0;
	$N_Intermediate = 0;
	$N_Advanced = 0;
	$N_total = 0;
	$N_Beginner_students = "";
	$N_Fundamental_students = "";
	$N_Intermediate_students = "";
	$N_Advanced_students = "";

	
	$S_Beginner = 0;
	$S_Fundamental = 0;
	$S_Intermediate = 0;
	$S_Advanced = 0;
	$S_total = 0;
	$S_Beginner_students = "";
	$S_Fundamental_students = "";
	$S_Intermediate_students = "";
	$S_Advanced_students = "";

	
	$R_Beginner = 0;
	$R_Fundamental = 0;
	$R_Intermediate = 0;
	$R_Advanced = 0;
	$R_total = 0;
	$R_Beginner_students = "";
	$R_Fundamental_students = "";
	$R_Intermediate_students = "";
	$R_Advanced_students = "";

	
	$U_Beginner = 0;
	$U_Fundamental = 0;
	$U_Intermediate = 0;
	$U_Advanced = 0;
	$U_total = 0;
	$U_Beginner_students = "";
	$U_Fundamental_students = "";
	$U_Intermediate_students = "";
	$U_Advanced_students = "";

	
	$V_Beginner = 0;
	$V_Fundamental = 0;
	$V_Intermediate = 0;
	$V_Advanced = 0;
	$V_total = 0;
	$V_Beginner_students = "";
	$V_Fundamental_students = "";
	$V_Intermediate_students = "";
	$V_Advanced_students = "";

	
	$blank_status_Beginner = 0;
	$blank_status_Fundamental = 0;
	$blank_status_Intermediate = 0;
	$blank_status_Advanced = 0;
	$blank_status_total = 0;
	$blank_status_Beginner_students = "";
	$blank_status_Fundamental_students = "";
	$blank_status_Intermediate_students = "";
	$blank_status_Advanced_students = "";

	
	$Beginner_count = 0;
	$Fundamental_count = 0;
	$Intermediate_count = 0;
	$Advanced_count = 0;

	function debugReport($message) {
		global $debugLog, $doDebug;
		$timestamp = date('Y-m-d H:i:s');
		$debugLog .= "$message ($timestamp)<br />";
		if ($doDebug) {
			echo "$message<br />";
		}
	}
	
	debugReport("Initialization Array:<br /><pre>");
	$myStr = print_r($context->toArray(), TRUE);
	debugReport("$myStr</pre>");
	

// get the input information
	if (isset($_REQUEST)) {
		foreach($_REQUEST as $str_key => $str_value) {
			if (!is_array($str_value)) {
				debugReport("Key: $str_key | Value: $str_value");
			} else {
				debugReport("Key: $str_key (array)");
			}
			if ($str_key 		== "strpass") {
				$strPass		 = $str_value;
				$strPass		 = filter_var($strPass,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "inp_rsave") {
				$inp_rsave		 = $str_value;
				$inp_rsave		 = filter_var($inp_rsave,FILTER_UNSAFE_RAW);
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
			debugReport("<p><strong>Operating in Test Mode.</strong></p>");
		}
		$extMode					= 'tm';
		$TableName					= "wpw1_cwa_";
		$operatingMode				= 'Testmode';
	} else {
		$extMode					= 'pd';
		$TableName					= "wpw1_cwa_";
		$operatingMode				= 'Production';
	}

	$student_dal = new CWA_Student_DAL();
	$advisor_dal = new CWA_Advisor_DAL();
	$advisorclass_dal = new CWA_Advisorclass_DAL();
	$user_dal = new CWA_User_Master_DAL();



	if ("1" == $strPass) {
		$content 		.= "<h3>$jobname</h3>
							<p>
							<form method='post' action='$theURL' 
							name='selection_form' ENCTYPE='multipart/form-data'>
							<input type='hidden' name='strpass' value='2'>
							<table style='border-collapse:collapse;'>
							$testModeOption
							<tr><td>Save this report to the reports achive?</td>
							<td><input type='radio' class='formInputButton' name='inp_rsave' value='N' checked='checked'> Do not save the report<br />
								<input type='radio' class='formInputButton' name='inp_rsave' value='Y'> Save a copy of the report the report</td></tr>
							<tr><td colspan='2'><input class='formInputButton' type='submit' value='Submit' /></td></tr></table>
							</form></p>";
	

///// Pass 2 -- do the work


	} elseif ("2" == $strPass) {
		debugReport("<br />pass 2");
		
		$content .= "<h3>$jobname</h3>
					<h4>Report Index</h4>
					<a href='#summary'>Summary Report</a><br />\n
					<a href='#response_r'>Response R</a><br />\n
					<a href='#dropped'>Dropped Report</a><br />\n
					<a href='#status_C'>Status C Report</a><br />\n
					<a href='#status_Y'>Status Y Report</a><br />\n
					<a href='#status_Y_P'>Status Y_P Report</a><br />\n
					<a href='#status_Y_W'>Status Y_W Report</a><br />\n
					<a href='#status_N'>Status N Report</a><br />\n
					<a href='#status_S'>Status S Report</a><br />\n
					<a href='#status_C'>Status R Report</a><br />\n
					<a href='#status_C'>Status U Report</a><br />\n
					<a href='#blank'>Status Blank Report</a><br />\n
					<a href='#noreason'>No Reason Report</a><br />\n";

		
		// get all singleton student records
		$sql = "SELECT * FROM wpw1_cwa_student 
				WHERE student_call_sign IN ( 
				    SELECT student_call_sign FROM wpw1_cwa_student 
				    GROUP BY student_call_sign 
				    HAVING COUNT(student_call_sign) = 1)";
		$studentData = $student_dal->run_sql($sql, $operatingMode);
		if ($studentData === FALSE || $studentData === NULL) {
			debugReport("getting singleton student records returned FALSE|NULL");
		} else {
			if (! empty($studentData)) {
				foreach($studentData as $key => $value) {
					foreach($value as $thisField => $thisValue) {
						$$thisField = $thisValue;
					}
					debugReport("<br />Processing |$student_call_sign|<br />
								Response: |$student_response|<br />
								Status: |$student_status|<br />
								Email Number: |$student_email_number|<br />
								Assigned Advisor; |$student_assigned_advisor|<br />
								Assigned AdvisorClass: |$student_assigned_advisor_class|<br />
								Intervention Required: |$student_intervention_required|<br />
								Hold Reason Code: |$student_hold_reason_code|");
					$studentLink	= "<a href='$siteURL/cwa-display-and-update-student-signup-information/?strpass=2&request_type=callsign&request_info=$student_call_sign&inp_depth=all&doDebug&testMode' target='_blank'>$student_call_sign</a><br />";
					${$student_level . '_count'}++;
					$student_total++;			
					if ($student_response == 'R') {
						${'r_' . $student_level}++;
						$r_total++;
						${'r_' . $student_level . '_students'} .= $studentLink;
						debugReport("incremented r_ $student_level");
						$haveReason = TRUE;
					}
					if ($student_status == '' && $student_email_number == 4) {
						${'dropped_' . $student_level}++;
						${'dropped_' . $student_level . '_students'} .= $studentLink;
						$dropped_total++;
						debugReport("incremented dropped_ $student_level");
						$haveReason = TRUE;
					}
					if ($student_status != '') {
						${$student_status . '_' . $student_level}++;
						${$student_status . '_' . $student_level . '_students'} .= $studentLink;
						${$student_status . '_total'}++;
						debugReport("incremented $student_status _ $student_level");
						$haveReason = TRUE;
						if ($student_status == 'Y' && $student_promotable == 'P') {
							${'Y_P_' . $student_level}++;
							${'Y_P_' . $student_level . '_students'} .= $studentLink;
							$Y_P_total++;
						} elseif ($student_status == 'Y' && $student_promotable = 'W') {
							${'Y_W_' . $student_level}++;
							${'Y_W_' . $student_level . '_students'} .= $studentLink;
							$Y_W_total++;
						}
						
					} else {
						if (! $haveReason) {
							${'blank_status_' . $student_level}++;
							$blank_status_total++;
							${'blank_status_' . $student_level . '_students'} .= $studentLink;
							debugReport("incremented blank_status_ $student_level");
							$haveReason = TRUE;
						}
					}
					if (! $haveReason) {
						$noReason .= "$studentLink<br />";
						$noReason_total++;
						debugReport("logged no reason");
					}
				}
				$content .= "<br /><a name='summary'><h4>Analysis Summary</h4></a>
							<table>
							<tr><th></th>
								<th>Beginner</th>
								<th>Fundamental</th>
								<th>Intermediate</th>
								<th>Advance</th>
								<th>Total</th></tr>
							<tr><td>Response R 
									<span class='info-asterisk' data-title='Students who refused the class when verification was requested'>*</span>
									</td>
								<td>$r_Beginner</td>
								<td>$r_Fundamental</td>
								<td>$r_Intermediate</td>
								<td>$r_Advanced</td>
								<td>$r_total</td></tr>
							<tr><td>Dropped 
									<span class='info-asterisk' data-title='students dropped after 4 emails and no response'>*</span>
									</td>
								<td>$dropped_Beginner</td>
								<td>$dropped_Fundamental</td>
								<td>$dropped_Intermediate</td>
								<td>$dropped_Advanced</td>
								<td>$dropped_total</td></tr>
							<tr><td>Status C 
									<span class='info-asterisk' data-title='students which were assigned to a class and dropped by the advisor'>*</span>
									</td>
								<td>$C_Beginner</td>
								<td>$C_Fundamental</td>
								<td>$C_Intermediate</td>
								<td>$C_Advanced</td>
								<td>$C_total</td></tr>
							<tr><td>Status Y 
									<span class='info-asterisk' data-title='students assigned to a class and confirmed by the advisor'>*</span>
									</td>
								<td>$Y_Beginner</td>
								<td>$Y_Fundamental</td>
								<td>$Y_Intermediate</td>
								<td>$Y_Advanced</td>
								<td>$Y_total</td></tr>
							<tr><td>Status Y Promoted 
									<span class='info-asterisk' data-title='students assigned to a class and evaluated as promotable'>*</span>
									</td>
								<td>$Y_P_Beginner</td>
								<td>$Y_P_Fundamental</td>
								<td>$Y_P_Intermediate</td>
								<td>$Y_P_Advanced</td>
								<td>$Y_P_total</td></tr>
							<tr><td>Status Y Withdrew 
									<span class='info-asterisk' data-title='students assigned to a class and then withdrew'>*</span>
									</td>
								<td>$Y_W_Beginner</td>
								<td>$Y_W_Fundamental</td>
								<td>$Y_W_Intermediate</td>
								<td>$Y_W_Advanced</td>
								<td>$Y_W_total</td></tr>
							<tr><td>Status N 
									<span class='info-asterisk' data-title='students turned down by the advisor'>*</span>
									</td>
								<td>$N_Beginner</td>
								<td>$N_Fundamental</td>
								<td>$N_Intermediate</td>
								<td>$N_Advanced</td>
								<td>$N_total</td></tr>
							<tr><td>Status S 
									<span class='info-asterisk' data-title='student assigned to an advisor but not confirmed'>*</span>
									</td>
								<td>$S_Beginner</td>
								<td>$S_Fundamental</td>
								<td>$S_Intermediate</td>
								<td>$S_Advanced</td>
								<td>$S_total</td></tr>
							<tr><td>Status R 
									<span class='info-asterisk' data-title='Student was assigned to a class and dropped by the advisor who requested a replacement'>*</span>
									</td>
								<td>$R_Beginner</td>
								<td>$R_Fundamental</td>
								<td>$R_Intermediate</td>
								<td>$R_Advanced</td>
								<td>$R_total</td></tr>
							<tr><td>Status U 
									<span class='info-asterisk' data-title='student available for assingment but no class available'>*</span>
									</td>
								<td>$U_Beginner</td>
								<td>$U_Fundamental</td>
								<td>$U_Intermediate</td>
								<td>$U_Advanced</td>
								<td>$U_total</td></tr>
							<tr><td>Status V 
									<span class='info-asterisk' data-title='student assigned to a class but unable to take the class due toschedule issues. Student waiting to be replaced'>*</span>
									</td>
								<td>$V_Beginner</td>
								<td>$V_Fundamental</td>
								<td>$V_Intermediate</td>
								<td>$V_Advanced</td>
								<td>$V_total</td></tr>
							<tr><td>Blank Status 
									<span class='info-asterisk' data-title='unknown reason'>*</span>
									</td>
								<td>$blank_status_Beginner</td>
								<td>$blank_status_Fundamental</td>
								<td>$blank_status_Intermediate</td>
								<td>$blank_status_Advanced</td>
								<td>$blank_status_total</td></tr>
							<tr><td>Total</td>
								<td>$Beginner_count</td>
								<td>$Fundamental_count</td>
								<td>$Intermediate_count</td>
								<td>$Advanced_count</td>
								<td>$student_total</td></tr>
							</table>";
				$content .= "<a name='noreason'><h4>Students With No Reason</h4></a>
							$noReason";
				$content .= "<a name='response_r'><h4>Response R Students</h4></a>
							<table>
							<tr><th>Beginner</th>
								<th>Fundamental</th>
								<th>Intermediate</th>
								<th>Advanced</th></tr>
							<tr><td style='vertical-align:top;'>$r_Beginner_students</td>
								<td style='vertical-align:top;'>$r_Fundamental_students</td>
								<td style='vertical-align:top;'>$r_Intermediate_students</td>
								<td style='vertical-align:top;'>$r_Advanced_students</td></tr>
							</table>";

				$content .= "<a name='dropped'><h4>Dropped Students</h4></a>
							<table>
							<tr><th>Beginner</th>
								<th>Fundamental</th>
								<th>Intermediate</th>
								<th>Advanced</th></tr>
							<tr><td style='vertical-align:top;'>$dropped_Beginner_students</td>
								<td style='vertical-align:top;'>$dropped_Fundamental_students</td>
								<td style='vertical-align:top;'>$dropped_Intermediate_students</td>
								<td style='vertical-align:top;'>$dropped_Advanced_students</td></tr>
							</table>";
				$content .= "<a name='status_Y'><h4>Status Y Students</h4></a>
							<table>
							<tr><th>Beginner</th>
								<th>Fundamental</th>
								<th>Intermediate</th>
								<th>Advanced</th></tr>
							<tr><td style='vertical-align:top;'>$Y_Beginner_students</td>
								<td style='vertical-align:top;'>$Y_Fundamental_students</td>
								<td style='vertical-align:top;'>$Y_Intermediate_students</td>
								<td style='vertical-align:top;'>$Y_Advanced_students</td></tr>
							</table>";

				$content .= "<a name='status_Y_P'><h4>Status Y_P Students</h4></a>
							<table>
							<tr><th>Beginner</th>
								<th>Fundamental</th>
								<th>Intermediate</th>
								<th>Advanced</th></tr>
							<tr><td style='vertical-align:top;'>$Y_P_Beginner_students</td>
								<td style='vertical-align:top;'>$Y_P_Fundamental_students</td>
								<td style='vertical-align:top;'>$Y_P_Intermediate_students</td>
								<td style='vertical-align:top;'>$Y_P_Advanced_students</td></tr>
							</table>";

				$content .= "<a name='status_Y_W'><h4>Status Y_W Students</h4></a>
							<table>
							<tr><th>Beginner</th>
								<th>Fundamental</th>
								<th>Intermediate</th>
								<th>Advanced</th></tr>
							<tr><td style='vertical-align:top;'>$Y_W_Beginner_students</td>
								<td style='vertical-align:top;'>$Y_W_Fundamental_students</td>
								<td style='vertical-align:top;'>$Y_W_Intermediate_students</td>
								<td style='vertical-align:top;'>$Y_W_Advanced_students</td></tr>
							</table>";

				$content .= "<a name='status_N'><h4>Status N Students</h4></a>
							<table>
							<tr><th>Beginner</th>
								<th>Fundamental</th>
								<th>Intermediate</th>
								<th>Advanced</th></tr>
							<tr><td style='vertical-align:top;'>$N_Beginner_students</td>
								<td style='vertical-align:top;'>$N_Fundamental_students</td>
								<td style='vertical-align:top;'>$N_Intermediate_students</td>
								<td style='vertical-align:top;'>$N_Advanced_students</td></tr>
							</table>";

				$content .= "<a name='status_S'><h4>Status S Students</h4></a>
							<table>
							<tr><th>Beginner</th>
								<th>Fundamental</th>
								<th>Intermediate</th>
								<th>Advanced</th></tr>
							<tr><td style='vertical-align:top;'>$S_Beginner_students</td>
								<td style='vertical-align:top;'>$S_Fundamental_students</td>
								<td style='vertical-align:top;'>$S_Intermediate_students</td>
								<td style='vertical-align:top;'>$S_Advanced_students</td></tr>
							</table>";

				$content .= "<a name='status_R'><h4>Status R Students</h4></a>
							<table>
							<tr><th>Beginner</th>
								<th>Fundamental</th>
								<th>Intermediate</th>
								<th>Advanced</th></tr>
							<tr><td style='vertical-align:top;'>$R_Beginner_students</td>
								<td style='vertical-align:top;'>$R_Fundamental_students</td>
								<td style='vertical-align:top;'>$R_Intermediate_students</td>
								<td style='vertical-align:top;'>$R_Advanced_students</td></tr>
							</table>";

				$content .= "<a name='status_U'><h4>Status U Students</h4></a>
							<table>
							<tr><th>Beginner</th>
								<th>Fundamental</th>
								<th>Intermediate</th>
								<th>Advanced</th></tr>
							<tr><td style='vertical-align:top;'>$U_Beginner_students</td>
								<td style='vertical-align:top;'>$U_Fundamental_students</td>
								<td style='vertical-align:top;'>$U_Intermediate_students</td>
								<td style='vertical-align:top;'>$U_Advanced_students</td></tr>
							</table>";

				$content .= "<a name='status_V'><h4>Status V Students</h4></a>
							<table>
							<tr><th>Beginner</th>
								<th>Fundamental</th>
								<th>Intermediate</th>
								<th>Advanced</th></tr>
							<tr><td style='vertical-align:top;'>$V_Beginner_students</td>
								<td style='vertical-align:top;'>$V_Fundamental_students</td>
								<td style='vertical-align:top;'>$V_Intermediate_students</td>
								<td style='vertical-align:top;'>$V_Advanced_students</td></tr>
							</table>";

				$content .= "<a name='blank'><h4>Blank Status Students</h4></a>
							<table>
							<tr><th>Beginner</th>
								<th>Fundamental</th>
								<th>Intermediate</th>
								<th>Advanced</th></tr>
							<tr><td style='vertical-align:top;'>$blank_status_Beginner_students</td>
								<td style='vertical-align:top;'>$blank_status_Fundamental_students</td>
								<td style='vertical-align:top;'>$blank_status_Intermediate_students</td>
								<td style='vertical-align:top;'>$blank_status_Advanced_students</td></tr>
							</table>";


				$content .= "<a name='status_C'><h4>Status C Students</h4></a>
							<table>
							<tr><th>Beginner</th>
								<th>Fundamental</th>
								<th>Intermediate</th>
								<th>Advanced</th></tr>
							<tr><td style='vertical-align:top;'>$C_Beginner_students</td>
								<td style='vertical-align:top;'>$C_Fundamental_students</td>
								<td style='vertical-align:top;'>$C_Intermediate_students</td>
								<td style='vertical-align:top;'>$C_Advanced_students</td></tr>
							</table>";

				$content .= "<p>End of Analysis</p>";

			} else {
				debugReport("getting singleton student records returned empty dataset");
				$content .= "No singleton student records found<br />";
			}
		}
	}
	$thisTime 		= date('Y-m-d H:i:s');
	$content 		.= "<br /><br /><p>Prepared at $thisTime</p>";

	///// uncomment if the code to save a report is needed
	debugReport("<br />Checking to see if the report is to be saved. inp_rsave: $inp_rsave");
	if ($inp_rsave == 'Y') {
		if ($doDebug) {
			echo "Calling function to save the report as Current Student and Advisor Assignments<br />";
		}
		$storeResult	= storeReportData_v2($jobname,$content);
		if ($storeResult[0] !== FALSE) {
			$reportName	= $storeResult[1];
			$reportID	= $storeResult[2];
			$content	.= "<br />Report stored in reports as $reportName<br />
							Go to'Display Saved Reports' or url<br/>
							<a href='$siteURL/cwa-display-saved-report/?strpass=3&token=&inp_id=$reportID' 'target='_blank'>Display Report</a>";
							
			// store the debug report
			$storeResult	= storeReportData_v2("$jobname Debug",$content);
			if ($storeResult[0] !== FALSE) {
				$reportName	= $storeResult[1];
				$reportID	= $storeResult[2];
				$content	.= "<br />Report stored in reports as $reportName<br />
								Go to'Display Saved Reports' or url<br/>
								<a href='$siteURL/cwa-display-saved-report/?strpass=3&token=&inp_id=$reportID' 'target='_blank'>Display Report</a>";
							
			}					
		} else {
			$content	.= "<br />Storing the report in the reports pod failed";
		}
	}

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
add_shortcode ('analyze_single_student_records', 'analyze_single_student_records_func');

