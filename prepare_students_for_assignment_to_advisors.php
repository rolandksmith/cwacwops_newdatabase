function prepare_students_for_assignment_to_advisors_func() {

/* Prepare for Student Assignments to Advisor
 *
 * This function should be run before running the
 * function to assign students to advisors. The function 
 * does the following:
 *		Checks each student in the upcoming semester to see if the student has taken 
 *		a previous class.
 *			Carry forward the excluded advisor(s)
 *			if so and the student is promotable, add 1 to priority
 *			if so and the student is not promotable but is taking the
 *				same level a second time, add 1 to priority, mark the
 *				student's hold_reason_code with X and put the previous advisor 
 *				in the excluded_advisor field
 *			If so and the student is not promotable, but signing up to
 *				take the next higher class, send an email to the student
 *				explaining the situation, mark student's hold_reason_code 
 *				as H (hold), and put an H in intervention_required
 *			If so and the student's promotable = Q
 *				If signing up for the same class again, add 1 to priority and allow to take the class
 * 					Mark the student's hold_reason_code with X and put the previous advisor 
 *					in the excluded_advisor field
 *				Otherwise, put a Q in intervention_required and hold_reason_code.
 *			if so and the advisor marked the student as 'W' (withdrew)
 *				If signing up for the same class again, allow to take the class
 *				Otherwise, put a 'H' in intervetion_required and a 'W' in hold_reason_code
 *			if so and the advisor hasn't evaluated the student
 *				If signing up for the same class again, allow to take the class
 *				Otherwise, put a 'E' in intervetion_required and a 'H' in hold_reason_code
 *			if so and taking the same level class again, mark the stuent's hold_reason_code 
 *				with an 'X' and put the previous advisor in th exlcuded_advisor_field
 *
 *		if a student is asking for a language other than English, check to see if there is
 *			a class in that language at that level. If not, put the student on hold
 *
 *		Check each advisor to see if the advisor has taught the previous
 *		semester. If so, and evaluations are not complete, set the score to 9.
 *		Otherwise, set the score to 1. If the advisor has not taught in the
 *		previous semester, leave the score at 0.
 *
 * The function can be run in one of five modes:
 * 		TestNoUpdate		Test mode, run against student2 and advisor2
 *							No updates attempted
 *		TestUpdate			Test mode, run against student2 and advisor2
 *							Updates will be attempted against student2 and advisor2
 *		Production			Production mode. Run against student and advisor pods
 *							data will be updated in the student and advisor pods
 *		Send Test Emails	Run against student pod.
 *							Send emails to students with registration conflicts
 *								hold_reason_code == H, Q, W, E
 *								intervention_required == H, Q, H, H
 *							Emails sent to Bob Carter / Roland Smith
 *		Send Emails			Run against student pod.
 *							Send emails to students with registration conflicts
 *								hold_reason_code == H, Q, W, E
 *								intervention_required == H, Q, H, H
 *							Emails sent to student
 *		
 
 
 */
 
 	global $wpdb;
 
	$doDebug						= FALSE;
	$initializationArray 			= data_initialization_func();
	if ($doDebug) {
		echo "Initialization Array:<br /><pre>";
		print_r($initializationArray);
		echo "</pre><br />";
	}
	$validUser 			= $initializationArray['validUser'];
	$userName			= $initializationArray['userName'];
	$currentDate		= $initializationArray['currentDate'];
	$validTestmode		= $initializationArray['validTestmode'];
	$currentTimestamp	= $initializationArray['currentTimestamp'];
	$siteURL			= $initializationArray['siteurl'];

	if ($userName == '') {
		return "YOU'RE NOT AUTHORIZED!<br />Goodby";
	}
//	if ($doDebug) {
		ini_set('display_errors','1');
		error_reporting(E_ALL);	
		ini_set('memory_limit','256M');
		ini_set('max_execution_time',0);
//	}

/// get the time that the process started
	$startingMicroTime			= microtime(TRUE);

	$testEmail			= 'rolandksmith@gmail.com';
//	$testEmail			= 'kcgator@gmail.com';
	$jobname			= 'Prepare Students for Assignment to Advisors';
	$strPass			= "1";
	$emailErrors		= 0;
	$emailCount			= 0;
	$inp_request_type	= '';
	$actionDate			= date('dMY H:i', $currentTimestamp);
	$nextSemester		= $initializationArray['nextSemester'];
	$proximateSemester	= $initializationArray['proximateSemester'];
	$anomolyCount		= 0;
	$updateCount		= 0;
	$advisorCount		= 0;
	$advisorEvalOK		= 0;
	$advisorEvalNOK		= 0;
	$promSameClass		= 0;
	$npromSameClass		= 0;
	$npromHigherClass	= 0;
	$qSameClass			= 0;
	$qHigherClass		= 0;
	$wSameClass			= 0;
	$wHigherClass		= 0;
	$neSameClass		= 0;
	$neHigherClass		= 0;
	$increment			= 0;
	$invalidYouth		= 0;
	$carryForward		= 0;
	$priorityUp			= 0;
	$noCatalogCount		= 0;
	$logDate			= date('Y-m-d H:i',$currentTimestamp);
	$fieldTest			= array('action_log','post_status','post_title','control_code');
	$semesterConversion		= array('Jan/Feb'=>1,'Apr/May'=>2,'May/Jun'=>2,'Sep/Oct'=>3,'SEP/OCT'=>3,'JAN/FEB'=>1,'APR/MAY'=>2);
	$currentSemester	= $initializationArray['currentSemester'];
	$prevSemester		= $initializationArray['prevSemester'];
	$semesterTwo		= $initializationArray['semesterTwo'];
	$semesterThree		= $initializationArray['semesterThree'];
	$semesterFour		= $initializationArray['semesterFour'];
	$theURL				= "$siteURL/cwa-prepare-students-for-assignment-to-advisors/";
	$studentManagementURL = "$siteURL/cwa-student-management/";
	$studentUpdateURL	= "$siteURL/cwa-display-and-update-student-signup-information/";
	$advisorUpdateURL	= "$siteURL/cwa-display-and-update-advisor-signup-information/";
	$errorArray			= array();
	$carryForwardExcludedAdvisor	= '';

// get the input information
	if (isset($_REQUEST)) {
		foreach($_REQUEST as $str_key => $str_value) {
			if ($doDebug) {
				echo "Key: $str_key | Value: $str_value <br />\n";
			}
			if ($str_key 		== "strpass") {
				$strPass		 = $str_value;
				$strPass		 = filter_var($strPass,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "request_type") {
				$inp_request_type	 = $str_value;
				$inp_request_type	 = filter_var($inp_request_type,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "inp_report") {
				$inp_report	 = $str_value;
				$inp_report	 = filter_var($inp_report,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "inp_show") {
				$inp_show	 = $str_value;
				$inp_show	 = filter_var($inp_show,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "inp_verbose") {
				$inp_verbose	 = $str_value;
				$inp_verbose	 = filter_var($inp_verbose,FILTER_UNSAFE_RAW);
				if ($inp_verbose == 'Y') {
					$doDebug	= TRUE;
				}
			}
		}
	}


function excludeAnAdvisor($nowExcluded='',$toBeExcluded='') {

/*	to be supplied: 	nowExcluded: current excluded_advisor field
						toBeExcluded: the advisor to be excluded
						
	returns:			array(TRUE/FALSE,Resulting excluded_advisor field)
	
	checks to see if the toBeExcluded advisor is already excluded. If not, adds the
	toBeExcluded advisor to the list. 
	
*/
	if ($toBeExcluded == '') {
		return array(FALSE,"input data missing");
	}
	if ($toBeExcluded != 'AC6AC') {
		$myInt = strpos($nowExcluded,$toBeExcluded);
		if ($myInt === FALSE) {
			if ($nowExcluded == '') {
				$nowExcluded = $toBeExcluded;
			} else {
				$nowExcluded .= "|$toBeExcluded";
			}
		}
	}
	return array(TRUE,$nowExcluded);
}
	
	
	$content = "";	
				
		$student_dal = new CWA_student_DAL();
		$advisor_dal = new CWA_Advisor_DAL();
		$user_dal = new CWA_User_Master_DAL();
		$advisorclass_dal = new CWA_Advisorclass_DAL();
				

	if ("1" == $strPass) {
		if ($doDebug) {
			echo "<br />Function starting.<br />";
		}
	if (in_array($userName,$validTestmode)) {			// give option to run in test mode 
		$testModeOption	= "<tr><td>Verbose Debugging?<br />
									<input type='radio' class='formInputButton' name='inp_verbose' value='N' checked='checked'> Standard Output<br />
									<input type='radio' class='formInputButton' name='inp_verbose' value='Y'> Turn on Debugging </td></tr>";
	} else {
		$testModeOption	= '';
	}
	$content			.= "<h3>$jobname</h3>
							<h4>Please Read the Following and Verify Before Submitting the Job</h4>
							<p>This function must be run before running the function to assign students to advisors.<p> 
							<p>If the function is being run in 'TestNoUpdate', 'TestUpdate', or 'Production' the function 
							does the following:</p>
							<table>
							<tr><td colspan='3'>If field no_catalog is Y then sets no_catalog to blank. This field will be 
												used to indicate if a student was arbitrarily assigned</td></tr>
							<tr><td colspan='3'>For each student enrolled in the upcoming semester, 
							check the past_student pod to see if the student has taken a previous class.</td></tr>
							<tr><td>&nbsp;</td><td colspan='2'>if so and the student is promotable, add 1 to priority</td></tr>
							<tr><td>&nbsp;</td><td colspan='2'>If so, the student is promotable, and taking the same class again</td></tr>
							<tr><td>&nbsp;</td><td style='width:100px;'>&nbsp;</td><td>add 1 to priority</td></tr>
							<tr><td>&nbsp;</td><td>&nbsp;</td><td>mark the student's hold_reason_code with X</td></tr>
							<tr><td>&nbsp;</td><td>&nbsp;</td><td>put the previous advisor in the excluded_advisor field</td></tr>
							<tr><td>&nbsp;</td><td colspan='2'>if so and the student is not promotable but is taking the
							same level a second time, </td></tr>
							<tr><td>&nbsp;</td><td>&nbsp;</td><td>add 1 to priority</td></tr>
							<tr><td>&nbsp;</td><td>&nbsp;</td><td>mark the student's hold_reason_code with X</td></tr>
							<tr><td>&nbsp;</td><td>&nbsp;</td><td>put the previous advisor in the excluded_advisor field</td></tr>
							<tr><td>&nbsp;</td><td colspan='2'>If so and the student is not promotable, but signing up to 
							take the next higher class</td></tr>
							<tr><td>&nbsp;</td><td>&nbsp;</td><td>mark student's hold_reason_code as H (hold)</td></tr>
							<tr><td>&nbsp;</td><td>&nbsp;</td><td>and put an H in intervention_required</td></tr>
							<tr><td>&nbsp;</td><td colspan='2'>If so and the student's promotable = Q</td></tr>
							<tr><td>&nbsp;</td><td>&nbsp;</td><td>If signing up for the same class again, add 1 to priority 
							and allow to take the class</td></tr>
							<tr><td>&nbsp;</td><td>&nbsp;</td><td>Mark the student's hold_reason_code with X and put the 
							previous advisor in the excluded_advisor field</td></tr>
							<tr><td>&nbsp;</td><td>&nbsp;</td><td>Otherwise, put a Q in intervention_required and hold_reason_code.</td></tr>
							<tr><td>&nbsp;</td><td colspan='2'>if so and the advisor marked the student as 'W' (withdrew)</td></tr>
							<tr><td>&nbsp;</td><td>&nbsp;</td><td>If signing up for the sameclass again, allow to take the class</td></tr>
							<tr><td>&nbsp;</td><td>&nbsp;</td><td>Otherwise, put a 'H' in intervetion_required and a 'W' in hold_reason_code</td></tr>
							<tr><td>&nbsp;</td><td colspan='2'>if so and the advisor hasn't evaluated the student</td></tr>
							<tr><td>&nbsp;</td><td>&nbsp;</td><td>If signing up for the sameclass again, allow to take the class</td></tr>
							<tr><td>&nbsp;</td><td>&nbsp;</td><td>Otherwise, put a 'E' in intervention_required and a 'H' in hold_reason_code</td></tr>
							<tr><td colspan='3'><hr></td></tr>
							<tr><td colspan='3'>Check each advisor to see if the advisor has taught the previous semester</td></tr>
							<tr><td>&nbsp;</td><td colspan='2'>If so, and evaluations are not complete</td></tr>
							<tr><td>&nbsp;</td><td>&nbsp;</td><td>set the score to 9</td></tr>
							<tr><td>&nbsp;</td><td>&nbsp;</td><td>Otherwise, set the score to 1.</td></tr>
							<tr><td>&nbsp;</td><td colspan='2'>If the advisor has not taught in the previous semester
							<tr><td>&nbsp;</td><td>&nbsp;</td><td>leave the score at 0.</td></tr>
							</table>
							<p>If the function is being run in 'Send Email' mode, the job goes through the verified students 
							and sends a registration conflict email to students where:<br />
							The hold_reason_code = H and intervention_required = H (not promotable, registered for higher class)<br />
							The hold_reason_code = Q and intervention_required = Q (advisor quit, registered for higher class)<br />
							The hold_reason_code = W and intervention_required = H (student withdrew, registered for higher class)<br />
							The hold_reason_code = E and intervention_required = H (student not evaluated, registered for higher class)</p>
							<p>The function can be run in one of five modes:<br />
							<dl>
							<dt>TestNoUpdate</dt>
							<dd>Test mode, run against student2 and advisor2; No updates attempted.</dd>
							<dt>TestUpdate</dt>
							<dd>Test mode, run against student2 and advisor2; Updates will be attempted 
							against student2 and advisor2.</dd>
							<dt>Send Test Emails</dt>
							<dd>Run against student2 pod. Conflict emails sent to Bob Carter / Roland Smith.</dd>
							<dt>Production</dt>
							<dd>Production mode. Run against student and advisor pods. Data will be updated in 
							the student and advisor pods.</dd>
							<dt>Send Emails</dt>
							<dd>Run against student pod. Students with registration conflicts are sent a 
							conflict email.</dd>
							</dl>
							<h3>Click Submit to Start the Process</h3>
							<p><form method='post' action='$theURL' 
							name='selection_form' ENCTYPE='multipart/form-data''>
							<input type='hidden' name='strpass' value='2'>
							<table>
							<tr><td style='width:200px;'><input type='radio' class='formInputButton' name='request_type' value='TestNoUpdate'>Test with No Update</td></tr>
							<tr><td><input type='radio' name='request_type' value='TestUpdate' checked='checked'>Test with Update</td></tr>
							<tr><td><input type='radio' name='request_type' value='SendTestEmail'>Send Test Email</td></tr>
							<tr><td><input type='radio' name='request_type' value='Production'>Production</td></tr>
							<tr><td><input type='radio' name='request_type' value='SendEmail'>Send Email</td></tr>
							<tr><td>Anomalies to display:<br />
									<input type='radio' name='inp_show' value='serious' checked='checked'> Only display significant anomalies<br />
									<input type='radio' name='inp_show' value='all'> Display all anomalies</td></tr>
							$testModeOption
							</table>
							<input class='formInputButton' type='submit' value='Submit' />
							</form>";

///// Pass 2 -- do the work

	} elseif ("2" == $strPass) {
		if ($doDebug) {
			echo "<br />at pass 2<br />Request Type: $inp_request_type<br />";
		}

		$doProceed = TRUE;
		$content 						.= "<h3>$jobname</h3>";
		if ($inp_request_type == "TestNoUpdate") {
			$updateMode					= FALSE;
			$testMode					= TRUE;
			$sendEmail					= FALSE;
			$doAdvisors					= TRUE;
			$operatingMode				= 'Testmode';
			$content					.= "<p>System is running in TestMode No Updates</p><h5>The Function Found the Following Anomalies</h5>";
		} elseif ($inp_request_type == "TestUpdate") {
			$updateMode					= TRUE;
			$testMode					= TRUE;
			$sendEmail					= FALSE;
			$doAdvisors					= TRUE;
			$operatingMode				= 'Testmode';
			$content					.= "<p>System is running in TestMode With Updates</p><h5>The Function Found the Following Anomalies</h5>";
		} elseif ($inp_request_type == "Production") {
			$testMode					= FALSE;
			$updateMode					= TRUE;
			$sendEmail					= FALSE;
			$doAdvisors					= TRUE;
			$operatingMode				= 'Production';
			$content					.= "<p>System is running in Production with Updates</p><h5>The Function Found the Following Anomalies</h5>";
		} elseif ($inp_request_type == 'SendTestEmail') {
			$testMode					= TRUE;
			$updateMode					= TRUE;
			$sendEmail					= TRUE;
			$doAdvisors					= TRUE;
			$operatingMode				= 'Testmode';
			$content					.= "<p>System is running in TestMode Sending Test Emails Only</p><h5>Sending the Following Emails</h5>";
		} elseif ($inp_request_type == 'SendEmail') {
			$testMode					= FALSE;
			$updateMode					= FALSE;
			$sendEmail					= TRUE;
			$doAdvisors					= FALSE;
			$operatingMode				= 'Production';
			$content					.= "<h5>Sending the Following Production Emails</h5>";
		} else {
			$content					.= "Invalid request type. Process aborted.";
			$doProceed					= FALSE;
		}
		if ($doProceed) {
			if ($doDebug) {
				echo "<br />Pass 2<br />
					  Logicals: testMode: $testMode<br >
					  updateMode: $updateMode<br />
					  sendEmail: $sendEmail<br />
					  doAdvisor: $doAdvisors<br />";
			}
			$content	.= "<h4>Processing Students</h4>";
			// get the student table for the next semester and process each student
			$criteria = [
				'relation' => 'AND',
				'clauses' => [
					['field' => 'student_semester', 'value' => $proximateSemester, 'compare' => '=' ],
					['field' => 'student_response', 'value' => 'Y', 'compare' => '=' ]
				]
			];
			$orderby = 'student_call_sign';
			$order = 'ASC';
			$studentData = $student_dal->get_student_by_order($criteria,$orderby,$order,$operatingMode);
			if ($studentData === FALSE || $studentData === NULL) {
				if ($doDebug) {
					echo "get_student returned FALSE|NULL<br />";
				}
				$doProceed = FALSE;
			} else {
				if (! empty ($studentData)) {
					$numSRows = count($studentData);
					foreach($studentData as $key => $value) {
						foreach($value as $thisField => $thisValue) {
							$$thisField = $thisValue;
						}
						// now get the user_master for this student
						$userData = $user_dal->get_user_master_by_callsign($student_call_sign,$operatingMode);
						if ($userData === FALSE || $userData === NULL) {
							if ($doDebug) {
								echo "get_user_by_callsign for $student_call_sign returned FALSE|NULL<br />";
							}
							$doProceed = FALSE;
						} else {
							if (! empty($userData)) {
								foreach($userData as $key => $value) {
									foreach($value as $thisField => $thisValue) {
										$$thisField = $thisValue;
									}
								}

								if ($doProceed) {
									$studentUpdateData			= "<a href='$studentUpdateURL?request_type=callsign&request_info=$student_call_sign&inp_depth=one&doDebug=$doDebug&testMode=$testMode&strpass=2' target='_blank'>$student_call_sign</a>";
									$possContent				= "<br />Processing student $user_last_name, $user_first_name ($studentUpdateData)<br />
																	&nbsp;&nbsp;&nbsp;Youth: $student_youth; age: $student_age<br />
																	&nbsp;&nbsp;&nbsp;Requesting a $student_level class<br />";
									$updateLog					= "";
									$doContent					= FALSE;
									$updateStudent				= FALSE;
									$carryForwardExclAdvisor	= '';
									$newExclAdvisor				= '';
									$studentUpdateData			= "<a href='$studentUpdateURL?request_type=callsign&request_info=$student_call_sign&inp_depth=one&doDebug=$doDebug&testMode=$testMode&strpass=2' target='_blank'>$student_call_sign</a>";
									if (!$sendEmail) {					// do the process, no emails sent
										$studentUpdateParams	= array();	
										if ($doDebug) {
											echo "<br />Processing student $user_last_name, $user_first_name ($student_call_sign)<br />
												   &nbsp;&nbsp;&nbsp;Youth: $student_youth; age: $student_age<br />
												   &nbsp;&nbsp;&nbsp;Requesting a $student_level class<br />
												   &nbsp;&nbsp;&nbsp;Requesting a class in $student_class_language<br />
												   &nbsp;&nbsp;&nbsp;Intervention Required: $student_intervention_required<br />
												   &nbsp;&nbsp;&nbsp;Hold Override: $student_hold_override<br />";
										}
										/// if student says is youth, must have an age and be 20 or less
										if ($student_youth == 'Yes' || $student_youth == 'Y') {
											if ($student_age == '') {
												$studentUpdateParams['student_youth'] = 'N';
												$updateStudent					= TRUE;
												$updateLog						.= " / student says is a youth, age not given and youth set to no";
												if ($doDebug) {
													echo "&nbsp;&nbsp;&nbsp;Youth = Yes; Student age not given, setting youth to No<br />";
												}
												$possContent		.= "&nbsp;&nbsp;&nbsp;Youth = Yes; age not given. Set youth to No<br />";
												$invalidYouth++;
											} else {
												if ($student_age > 20) {
													$studentUpdateParams['student_youth'] = 'N';
													$updateLog						.= " / student age over 20, setting youth to No";
													$updateStudent					= TRUE;
													if ($doDebug) {
														echo "&nbsp;&nsp;&nbsp;Youth = Yes; age over 20. Setting youth to No<br />";
													}
													$possContent		.= "&nbsp;&nbsp;&nbsp;Youth = Yes; age over 20. Set youth to No<br />";
													$invalidYouth++;
												}
											}
										}
										if ($student_no_catalog == 'Y') {
											if ($doDebug) {
												echo "setting no_catalog to blank<br />";
											}
											$studentUpdateParams['student_no_catalog'] = '';
											$updateLog								.= " removed no_catalog entry ";
											$possContent							.= "&nbsp;&nbsp;&nbsp;removed no_catalog entry<br >";
											$updateStudent							= TRUE;
											$noCatalogCount++;
										}						
										if ($student_hold_override == 'Y') {
											if ($doDebug) {
												echo "&nbsp;&nbsp;&nbsp;Hold Override set. Bypassing student<br />";
											}
										} else {
											if ($student_intervention_required != '') {
												if ($doDebug) {
													echo "&nbsp;&nbsp;&nbsp;Student on hold. Not being processed<br />";
												}
												$possContent	.= "&nbsp;&nbsp;&nbsp;Student on hold. Not being processed<br />";
												$doContent		= FALSE;
											} else {
												// prepare excluded advisor array to add any past excluded advisors
												$currentExcludedAdvisors	= array();
												if ($student_excluded_advisor != '') {
													$currentExcludedAdvisor	= explode('&',$student_excluded_advisor);
												}
												// See if student has past student records
												if ($doDebug) {
													echo "checking for previous semester records<br />";
												}
												$theStudentStatus			= '';
												$thePromotable				= "";
												$theLevel					= "";
												$semesterTest				= "";
												
												$criteria = [
													'relation' => 'AND',
													'clauses' => [
														['field' => 'student_call_sign', 'value' => $student_call_sign, 'compare' => '=' ],
														['field' => 'student_semester', 'value' => $currentSemester, 'compare' => '!=' ],
														['field' => 'student_semester', 'value' => $nextSemester, 'compare' => '!=' ],
														['field' => 'student_semester', 'value' => $semesterTwo, 'compare' => '!=' ],
														['field' => 'student_semester', 'value' => $semesterThree, 'compare' => '!=' ],
														['field' => 'student_semester', 'value' => $semesterFour, 'compare' => '!=' ]
													]
												];
												$orderby = 'student_date_created';
												$order = 'DESC';
												$pastStudentData = $student_dal->get_student_by_order($criteria,$orderby,$order,$operatingMode);
												if ($pastStudentData === FALSE || $pastStudentData === NULL) {
													if ($doDebug) {
														echo "get_student for past students returned FALSE|NULL<br />";
													}
												} else {
													if (! empty($pastStudentData)) {
														$numPSRows = count($pastStudentData);
														foreach($pastStudentData as $key => $value) {
															foreach($value as $thisField => $thisValue) {
																$thisField = str_replace('student','pastStudent',$thisField);
																$$thisField = $thisValue;
															}
					
															if ($doDebug) {
																echo "&nbsp;&nbsp;&nbsp;Checking $pastStudent_semester. Response: $pastStudent_response; Status: $pastStudent_status; Promotable: $pastStudent_promotable<br />";
															}
															if ($pastStudent_excluded_advisor != '') {		// carry forward the do not assign
																if ($doDebug) {
																	echo "student $student_call_sign has $pastStudent_excluded_advisor exlcuded advisors for semester $pastStudent_semester<br />";
																}
																// merge the excluded advisors
																$myArray					= explode('&',$pastStudent_excluded_advisor);
																$combined					= array_merge($currentExcludedAdvisors,$myArray);
																$currentExcludedAdvisors	= array_unique($combined);
															}
															if ($pastStudent_status == 'Y') {
																if ($pastStudent_call_sign != $student_call_sign) {
																	if ($doDebug) {
																		echo "&nbsp;&nbsp;&nbsp;Call sign mismatch: student $student_call_sign vs $pastStudent_call_sign<br />";
																	}
																} else {
																	// find the last class the student has taken			
																	// convert this semester to a number
																	$semesterArray			= explode(" ",$pastStudent_semester);
																	$thisSemesterNumber		= $semesterArray[0];
																	$thisSemesterTerm		= $semesterArray[1];
																	$thisSemesterSeq		= $semesterConversion[$thisSemesterTerm];	
																	$thisSemesterTest 		= intval($thisSemesterNumber . $thisSemesterSeq);
																	if ($thisSemesterTest > $semesterTest) {
																		$semesterTest		= $thisSemesterTest;
																		$thePromotable		= $pastStudent_promotable;
																		$theLevel			= $pastStudent_level;
																		$theStudentStatus	= $pastStudent_status;
																		$theAdvisor			= $pastStudent_assigned_advisor;
																	}
																}
															}
														}				// have checked all past student records
														// setup excluded advisors (if any)
														if (count($currentExcludedAdvisors) > 0) {
															if ($doDebug) {
																echo "have excluded advisors:<br /><pre>";
																print_r($currentExcludedAdvisors);
																echo "</pre><br />";
															}
															$carryForwardExcludedAdvisor	= implode('&',$currentExcludedAdvisors);
															if ($doDebug) {
																echo "new excluded advisor: $carryForwardExclAdvisor<br />";
															}
															$updateLog .= " / Carried forward excluded advisors $carryForwardExclAdvisor ";			 					
															$doUpdate	= TRUE;
															$carryForward++;
														}
														if ($doDebug) {
															echo "&nbsp;&nbsp;&nbsp;Finished checking all classes for the student<br />
																  &nbsp;&nbsp;&nbsp;semesterTest: $semesterTest<br />
																  &nbsp;&nbsp;&nbsp;Promotable: $thePromotable<br />
																  &nbsp;&nbsp;&nbsp;Past Level: $theLevel (proposed $student_level)<br />
																  &nbsp;&nbsp;&nbsp;Last StudentStatus: $theStudentStatus<br />";
														}
														// if theStudentStatus is Y, the student has taken a class and
														// thePromotable is the promotion status from that class and
														// theLevel is the level of the class the student took
														// theAdvisor is the advisor for the class the student took
														if ($theStudentStatus == 'Y') {
															$possContent			.= "&nbsp;&nbsp;&nbsp;Student has previously taken a $theLevel class<br />";
															// handle case where thePromotable is Y
															if ($thePromotable == 'P') {
																$thePos		= strpos($student_action_log,"ASSGNPREP Student has taken");
																if ($thePos == FALSE) {
																	$student_class_priority			= 1;
																	$studentUpdateParams['student_class_priority'] = $student_class_priority;
																	$updateLog .= " / Student has taken a $theLevel class and is promotable";
																	$updateStudent = TRUE;
																	if ($doDebug) {
																		echo "&nbsp;&nbsp;&nbsp;Student has taken a $theLevel class and is promotable<br />";
																	}
																	$possContent 	.= "&nbsp;&nbsp;&nbsp;Student was promotable<br />";
																	$doContent		= FALSE;
																	$priorityUp++;
																}
																// if student is promotable but taking the same class again, don't assign to the same advisor unless
																// that advisor is pre-assigned
																if ($theLevel == $student_level) {
																	if ($doDebug) {
																		echo "Student is promotable but taking same class again<br />";
																	}
																	if ($student_pre_assigned_advisor != $theAdvisor) { // not pre-assigned
																		$newExclAdvisor						= $theAdvisor;
																		$updateLog						.= " / student promotable but taking same class again. Excluding previous advisor. "; 
																		$possContent 	.= "&nbsp;&nbsp;&nbsp;Previous advisor was $theAdvisor<br />
																							&nbsp;&nbsp;&nbsp;Student wishes to take the same $student_level class. Class OK.<br />";
																		if ($theAdvisor != 'AC6AC') {
																			$possContent .= "&nbsp;&nbsp;&nbsp;Excluded advisor $theAdvisor<br />";
																		}
																		$updateStudent			= TRUE;
																		if ($inp_show == 'all') {
																			$doContent				= TRUE;
																		}
																		$promSameClass++;
																	}
																}
															} else {
																// promotable status is not P ... setup to process other statuses
																$classOK			= FALSE;
																if ($theLevel == 'Beginner') {
																	if ($student_level == 'Beginner') {
																		$classOK	= TRUE;
																	}
																} elseif ($theLevel == 'Fundamental') {
																	if ($student_level == 'Beginner') {
																		$classOK	= TRUE;
																	} elseif ($student_level == 'Fundamental') {
																		$classOK  	= TRUE;
																	}
																} elseif ($theLevel == "Intermediate") {
																	if ($student_level == 'Beginner') {
																		$classOK	= TRUE;
																	} elseif ($student_level == 'Fundamental') {
																		$classOK  	= TRUE;
																	} elseif ($student_level == "Intermediate") {
																		$classOK	= TRUE;
																	}
																} elseif ($theLevel == "Advanced") {
																	$classOK		= TRUE;
																}
																if ($doDebug) {
																	echo "&nbsp;&nbsp;&nbsp;Student not promotable. ";
																	if ($classOK) {
																		echo "ClassOK is true. Can take the class<br />";
																	} else {
																		echo "ClassOK is NOT true. Put on hold<br />";
																	}
																}
													
																if ($thePromotable == 'N') {
																	// handle case where thePromotable is N
																	// if classOK is TRUE, let the student take the class
																	if ($classOK) {
																		$thePos		= strpos($student_action_log,"ASSGNPREP OK-N");
																		if ($thePos == FALSE) {
																			if ($student_pre_assigned_advisor == $theAdvisor) {
																				if ($doDebug) {
																					echo "&nbsp;&nbsp;&nbsp;Student already pre-assigned to previous advisor<br />";
																				}
																				$updateLog 	.= " /  OK-N Student not promotable and is taking the same or lower level from same advisor again.";
																				$updateStudent	= TRUE;
																				$possContent 	.= "&nbsp;&nbsp;&nbsp;Student is not promotable<br />
																									&nbsp;&nbsp;&nbsp;Previous advisor was $theAdvisor<br />
																									&nbsp;&nbsp;&nbsp;Student is taking a $theLevel class pre-assigned to same advisor<br />";
																			} else {
																				$newExclAdvisor						= $theAdvisor;
																				$updateLog						.= " / OK-N Student not promotable and is taking the same or lower level again. Do not assign to $pastStudent_assigned_advisor.";
																				$updateStudent					= TRUE;
																				$possContent 	.= "&nbsp;&nbsp;&nbsp;Student is not promotable<br />
																									&nbsp;&nbsp;&nbsp;Previous advisor was $theAdvisor<br />
																									&nbsp;&nbsp;&nbsp;Student wishes to take a $student_level class. Class OK.<br />";
																				if ($theAdvisor != 'AC6AC') {
																					$possContent .= "&nbsp;&nbsp;&nbsp;Excluded advisor $theAdvisor<br />";
																				}
																			}
																			$student_class_priority	= 1;
																			$studentUpdateParams['student_class_priority'] = $student_class_priority;
																			if ($inp_show == 'all') {
																				$doContent		= TRUE;
																			}
																			$updateLog			.= " / class priority set to 1";
																			$updateStudent	= TRUE;
																			if ($doDebug) {
																				echo "&nbsp;&nbsp;&nbsp;Student is taking a $theLevel class again<br />";
																			}
																			$npromSameClass++;
																		}
																	// if classOK is FALSE, student should not take the class
																	} else {														
																		$studentUpdateParams['student_hold_reason_code'] = 'H';
																		$studentUpdateParams['student_intervention_required'] = 'H';
																		$updateLog .= " / Student took a $theLevel class, not promotable, wants to take higher next level. ";
																		$updateStudent = TRUE;
																		$possContent 	.= "&nbsp;&nbsp;&nbsp;Student is not promotable<br />
																							&nbsp;&nbsp;&nbsp;Previous advisor was $theAdvisor<br />
																							&nbsp;&nbsp;&nbsp;Wants to take next higher level.<br />
																							&nbsp;&nbsp;&nbsp;<b>Student placed on hold</b><br />";
																		$doContent		= TRUE;
																		$emailReason	= "but your advisor's evaluation at the end of the semester was not sufficient for you to take a higher level class";
																		if ($doDebug) {
																			echo "&nbsp;&nbsp;&nbsp;Student has taken a $theLevel class, is not promotable<br />
																				  &nbsp;&nbsp;&nbsp;Wants to take next higher level. <br />";
																		}
																		$npromHigherClass++;
																	}
																// handle the promotable status of Q
																} elseif ($thePromotable == 'Q') {
																	// if classOK is TRUE, let the student take the class
																	if ($classOK) {
																		$thePos		= strpos($student_action_log,"ASSGNPREP OK-Q");
																		if ($thePos === FALSE) {
																			$newExclAdvisor						= $theAdvisor;
																			$student_class_priority			= 1;
																			$studentUpdateParams['student_class_priority'] = $student_class_priority;
																			$updateLog						.= " / OK-Q Student has taken a $theLevel class, promotable is Q, taking same or lower class again";
																			$updateStudent	= TRUE;
																			$possContent 	.= "&nbsp;&nbsp;&nbsp;Student promotable is Q and the student is taking the $theLevel class again<br />
																								&nbsp;&nbsp;&nbsp;Previous advisor was $theAdvisor<br />";
																			if ($theAdvisor != 'AC6AC') {
																				$possContent .= "&nbsp;&nbsp;&nbsp;Excluded advisor $theAdvisor<br />";
																			}
																			$doContent		= TRUE;
																			if ($doDebug) {
																				echo "&nbsp;&nbsp;&nbsp;Student promotable is Q and the student is taking the $theLevel class again<br />";
																			}
																			$qSameClass++;
																		}
																	} else {		// wanting to take a different class
																		$studentUpdateParams['student_hold_reason_code'] = 'Q';
																		$studentUpdateParams['intervention_required'] = 'Q'; 
																		$updateLog						.= " / Student has previously taken $theLevel class, promotable is Q, wants to take next higher level";
																		$updateStudent			= TRUE;							
																		$possContent 	.= "&nbsp;&nbsp;&nbsp;Promotable is Q but student wants to take the next higher level<br />
																							&nbsp;&nbsp;&nbsp;Previous advisor was $theAdvisor<br />
																							&nbsp;&nbsp;&nbsp;<b>Intervention_required set to Q</b><br />";
																		$doContent		= TRUE;
																		$emailReason	= "but the advisor did not complete your evaluation for this class";
																		if ($doDebug) {
																			echo "&nbsp;&nbsp;&nbsp;Student took the $theLevel class, promotable is Q but student wants to take the next higher level<br />";
																		}
																		$qHigherClass++;
																	}
																} elseif ($thePromotable == 'W') {			// student marked by advisor as withdrew
																	// if classOK is TRUE, let the student take the class
																	if ($classOK) {
																		$thePos		= strpos($student_action_log,"ASSGNPREP OK-W");
																		if ($thePos === FALSE) {
																			$newExclAdvisor						= $theAdvisor;
																			$updateLog						.= " / OK-W Student withdrew from a $theLevel class, taking same or lower class again";
																			$updateStudent	= TRUE;
																			$possContent 	.= "&nbsp;&nbsp;&nbsp;Student withdrew from a $theLevel class.<br />
																								&nbsp;&nbsp;&nbsp;Previous advisor was $theAdvisor<br />
																								&nbsp;&nbsp;&nbsp;The student is taking the same or lower class again<br />";
																			if ($theAdvisor != 'AC6AC') {
																				$possContent .= "&nbsp;&nbsp;&nbsp;Excluded advisor $theAdvisor<br />";
																			}
																			if ($inp_show == 'all') {
																				$doContent		= TRUE;
																			}
																			if ($doDebug) {
																				echo "&nbsp;&nbsp;&nbsp;Student withdrew from a $theLevel class.<br />
																					  &nbsp;&nbsp;&nbsp;The student is taking the same or lower class again<br />";
																			}
																			$wSameClass++;
																		}
																	} else {				// taking a different class. Intervention required
																		$studentUpdateParams['student_hold_reason_code'] = 'W';
																		$studentUpdateParams['student_intervention_required'] = 'H';
																		$updateLog						.= " / Student withdrew from a $theLevel class, wants to take next higher level";
																		$updateStudent					= TRUE;
																		$possContent 	.= "&nbsp;&nbsp;&nbsp;Student withdrew from a $theLevel class.<br />
																							&nbsp;&nbsp;&nbsp;The student wants to take the next higher level.<br />
																							&nbsp;&nbsp;&nbsp;<b>Student placed on hold</b><br />";
																		$doContent		= TRUE;
																		$emailReason	= "but according to our records, you withdrew from that class without completing it or obtaining your advisor's evaluation";
																		if ($doDebug) {
																			echo "&nbsp;&nbsp;&nbsp;Student withdrew from a $theLevel class.<br />
																				  &nbsp;&nbsp;&nbsp;The student wants to take the next higher level. Is on hold.<br />";
																		}
																		$wHigherClass++;
																	}
														
																} elseif ($thePromotable == '') {
																	// if classOK is TRUE, let the student take the class
																	if ($classOK) {
																		$thePos		= strpos($student_action_log,"ASSGNPREP OK-B");
																		if ($thePos === FALSE) {
																			if ($student_assigned_advisor != ''){
																				$newExclAdvisor						= $theAdvisor;
																			}
																			$student_class_priority				= 1;
																			$studentUpdateParams['student_class_priority'] = $student_class_priority;
																			$updateLog							.= " / OK-B Student has taken a $theLevel class, promotable is unknown, taking same or lower class again";
																			$updateStudent	= TRUE;
																			$possContent 	.= "&nbsp;&nbsp;&nbsp;Student was not evaluated.
																								&nbsp;&nbsp;&nbsp;Previous advisor was $theAdvisor<br />
																								&nbsp;&nbsp;&nbsp;The student is taking the same class again<br />";
																			if ($theAdvisor != 'AC6AC') {
																				$possContent .= "&nbsp;&nbsp;&nbsp;Excluded advisor $theAdvisor<br />";
																			}
																			if ($inp_show == 'all') {
																				$doContent		= TRUE;
																			}
																			if ($doDebug) {
																				echo "&nbsp;&nbsp;&nbsp;Student was in a $theLevel class but not evaluated.<br />
																					  &nbsp;&nbsp;&nbsp;The student is taking the same class again<br />";
																			}
																			$neSameClass++;
																		}
																	} else {				// taking a different class. Intervention required
																		$studentUpdateParams['student_hold_reason_code|']= 'E';
																		$studentUpdateParams['student_intervention_required'] = 'H';
																		$updateLog						.= " / Student has taken the $theLevel class, advisor $student_assigned_advisor has not completed the evaluation, student wants to take next higher level";
																		$updateStudent	= TRUE;
																		$possContent 	.= "&nbsp;&nbsp;&nbsp;Student was not evaluated.<br />
																							&nbsp;&nbsp;&nbsp;Previous advisor was $theAdvisor<br />
																							&nbsp;&nbsp;&nbsp;The student wants to take the next higher level.<br />
																							&nbsp;&nbsp;&nbsp;<b>Student placed on hold</b><br />";
																		$doContent		= TRUE;
																		$emailReason	= "but the advisor did not complete your evaluation for this class";
																		if ($doDebug) {
																			echo "&nbsp;&nbsp;&nbsp;Student was in a $theLevel class but not evaluated.<br />
																				  &nbsp;&nbsp;&nbsp;The student wants to take the next higher level. Is on hold.<br />";
																		}
																		$neHigherClass++;
																	}
																}
															}		// finished with the not-promotable loop
														}			// finished with the student_status=Y loop
													}				// finished with the pastStudent records
												}
											}					// finished with the intervention required loop
										}
									}						// finished with the hold_override loop
									
									// if the student is asking for a class in a language other than English,
									// check to see if there is a class in that language at that level.
									// if not. put the student on hold
									$hasClass = FALSE;
									if ($student_class_language != 'English') {
										if ($student_hold_override == '') {
											if ($doDebug) {
												echo "student is asking for a class in $student_class_language. Checking to see if it is available<br />";
											}
											// get the advisorclass records for student level and language
											$criteria = [
												'relation' => 'AND',
												'clauses' => [
													['field' => 'advisorclass_semester', 'value' => $student_semester, 'compare' => '=' ],
													['field' => 'advisorclass_level', 'value' => $student_level, 'compare' => '=' ],
													['field' => 'advisorclass_language', 'value' => $student_class_language, 'compare' => '=' ],
												]
											];
											$advisorclassData = $advisorclass_dal->get_advisorclasses_by_order( $criteria, 'advisorclass_sequence', 'ASC', $operatingMode );
											if ($advisorclassData === FALSE || $advisorclassData === 'NULL') {
												if ($doDebug) {
													echo "get_advisorclass returned FALSE|NULL<br />";
												}
											} else {
												if ($doDebug) {
													echo "advisorclassData returned: <br /><pre>";
													print_r($advisorclassData);
													echo "</pre><br />";
												}
												if (! empty($advisorclassData)) {
													if (count($advisorclassData) > 0) {
														$hasClass = TRUE;		// there is a class in that language at that level
														if ($doDebug) {
															echo "there is a class available<br />";
														}
													}
												}
											}
											if (! $hasClass) {
											
												$updateStudent = TRUE;
												$studentUpdateParams['student_intervention_required'] = 'H';
												$studentUpdateParams['student_hold_reason_code'] = 'L';
												$possContent 	.= "&nbsp;&nbsp;&nbsp;Student asking for a $student_level class in $student_class_language<br />
																	&nbsp;&nbsp;&nbsp;No class is available in that language at that level<br />
																	&nbsp;&nbsp;&nbsp;<b>Student placed on hold</b><br />";
												$updateLog .= " / Student asking for a $student_level class in $student_class_language. Class is not available. ";
												$doContent		= TRUE;
											}
										}
									}
									
									
									
									// see if should send an email
									if ($sendEmail) {
										$doEmail		= FALSE;
										
										if ($student_hold_reason_code == 'H' and $student_intervention_required == 'H') {
											$doEmail	= TRUE;			// not promotable, registered for higher class
											$theReason	= "your advisor did not evaluate your proficiency sufficient to take the next level class.";
										}
										if ($student_hold_reason_code == 'Q' and $student_intervention_required == 'Q') {
											$doEmail	= TRUE;			// advisor quite, registred for higher class
											$theReason	= "your advisor quit and didn't finish the class nor provide student proficiency evaluations.";
										}
										if ($student_hold_reason_code == 'W' and $student_intervention_required == 'H') {
											$doEmail	= TRUE;			// student withdrew, registred for higher class
											$theReason	= "you withdrew and didn't finish the previous class.";
										}
										if ($student_hold_reason_code == 'E' and $student_intervention_required == 'H') {
											$doEmail	= TRUE;			// student not evaluated, registered for highr class
											$theReason	= "your advisor didn't evaluate your proficiency in your previous class.";
										}
										$anomolyCount++;
										$mySubject	= "CW Academy Class Registration Conflict";
										if ($doEmail) {					// format and send an email
											if ($testMode) {
												$myTo		= 'rolandksmith@gmail.com';
												$mySubject	= "TESTMODE $mySubject";
												$mailCode	= 2;
											} else {
												$myTo		= $student_email;
												$mailCode	= 12;
											}
											$emailContent	= "<p>To: $student_last_name, $student_first_name ($student_call_sign)</p>
																<p>You recently signed up to take the $student_level CW Academy class for the $student_semester semester.</p>
																<p>Before CW Academy can process your registration information, please take the $student_level Morse code self assessment by 
																clicking <a href='$siteURL/cwa-student-registration/' target='_blank'>HERE</a> 
																and selecting Option 4 to see if this class is a good fit for you. If not, try the other levels.</p>
																<p>When the self assessment is complete, click <a href='mailto:kcgator@gmail.com?subject=$student_call_sign Self Assessment Completed'>HERE</a> 
																to send an email to Bob Carter WR7Q with your class level decision.</p>
																<p>Your registration for the $student_level class is currently on hold because $theReason</p>
																<p><span style='color:red;font-size:medium;'><b>Do not reply to this email as the address is not monitored.</b></span><br /></p>
																<p>73,<br />Bob Carter WR7Q<br />CW Academy Administrator</p>";
											$increment++;
											$mailResult		= emailFromCWA_v2(array('theRecipient'=>$myTo,
																					'theSubject'=>$mySubject,
																					'jobname'=>$jobname,
																					'theContent'=>$emailContent,
																					'mailCode'=>$mailCode,
																					'increment'=>$increment,
																					'testMode'=>$testMode,
																					'doDebug'=>$doDebug));
											if ($mailResult !== FALSE) {
												if ($doDebug) {
													echo "&nbsp;&nbsp;&nbsp;An email was sent to $myTo<br />";
												}
												$possContent .= "&nbsp;&nbsp;&nbsp;An email was sent to $myTo<br />";
												$emailCount++;
												$updateLog				.= " / Email was sent to $myTo ";
												$updateStudent			= TRUE;
											} else {
												echo "&nbsp;&nbsp;&nbsp;The mail send function failed.<br /><pre>";
												print_r($myHeaders);
												echo "</pre><br />";
												$emailErrors++;
											}
											$content	.= $possContent;
										}
									}
									// if updateStudent, then something should be processed
									if ($updateStudent) {
										if ($carryForwardExcludedAdvisor != '') {					
											$studentUpdateParams['student_excluded_advisor'] = $carryForwardExcludedAdvisor;
										}
											
										/// fix up the action log
										$student_action_log				= "$student_action_log / $actionDate ASSGNPREP $updateLog";
										$studentUpdateParams['student_action_log'] = $student_action_log;
										$updateCount++;
										if ($testMode) {
											if ($doDebug) {
												echo "&nbsp;&nbsp;&nbsp;updateStudent is TRUE and so is testMode<br />
													  &nbsp;&nbsp;&nbsp;Update parameters:<br /><pre>";
													  print_r($studentUpdateParams);
													  echo "</pre><br />";
											}
										}
										if ($updateMode) {
											if ($doDebug) {
												echo "&nbsp;&nbsp;&nbsp;updateMode is TRUE so writing to student table<br /><pre>";
												print_r($studentUpdateParams);
												echo "</pre><br />";
											}
											$updateResult = $student_dal->update($student_id,$studentUpdateParams,$operatingMode);
											if ($updateResult === FALSE) {
												if ($doDebug) {
													echo "updating $student_id returned FALSE<br />";
												}
											} else {
												if ($doDebug) {
													echo "updating student $student_call_sign succeeded<br />";
												}
											}
										}
									}
									$updateStudent		= FALSE;
									if ($doContent) {
										$content	.= $possContent;
									}
								}					// end of the big While loop 
							} else {
			 					$content .= "<p>No users found matching callsign $student_call_sign</p>";
			 					$doProceed = FALSE;
							}
						}
					}
 				} else {
 					$content .= "<p>No students found matching the criteria of $proximateSemester semester and response of Y</p>";
 					$doProceed = FALSE;
 				}
			}
		} else {				// end of the big Nmbr Records loop
			if ($doDebug) {
				echo "No records matching the criteria were found in $studentTableName<br />";
			}
			$content	.= "No records found in the $studentTableName pod<br />";
		}
		// finished with students. Now process the advisors.
	
	
		if ($doAdvisors) {				// no need to do this if only sending emails
			$advisorCount		= 0;
			$advisorEvalOK		= 0;
			$advisorEvalNOK		= 0;
			$possContent		= '';

			if ($doDebug) {
				echo "<br /><br />Finished with students. Starting with the advisors<br />";
			}
			$content			.= "<br /><h4>Processing Advisors</h4>";
			// get advisors in the next semester
			$criteria = [
				'relation' => 'AND',
				'clauses' => [
					['field' => 'advisor_semester', 'value' => $proximateSemester, 'compare' => '=' ]
				]
			];
			$orderby = 'advisor_call_sign';
			$order = 'ASC';
			$advisorResult = $advisor_dal->get_advisor_by_order($criteria,$orderby,$order,$operatingMode);
			if ($advisorResult === FALSE) {
				if ($doDebug) {
					echo "getting advisor_by_order returned FALSE<br />";
				}
			} else {
				if (! empty($advisorResult)) {
					$advisorCount++;
					foreach($advisorResult as $key => $value) {
						$updateUser								= FALSE;
						$doContent								= FALSE;
						$evalsDone								= TRUE;
						$userUpdateParams						= array();

						foreach($value as $thisField => $thisValue) {
							$$thisField = $thisValue;
						}
						// now get the user_master info
						$userResult = $user_dal->get_user_master_by_callsign($advisor_call_sign,$operatingMode);
						if ($userResult === FALSE) {
							if ($doDebug) {
								echo "get_user_master_by_callsign for $advisor_call_sign returned FALSE<br />";
							}
						} else {
							if (! empty($userResult)) {
								foreach($userResult as $key => $value) {
									foreach($value as $thisField => $thisValue) {
										$$thisField = $thisValue;
									}
								}
								if ($doDebug) {
									echo "<br />Processing advisor $advisor_call_sign<br />";
								}
								if ($advisor_call_sign != 'K1BG') {
									// get evaluation info from last semester, if any
									$criteria = [
										'relation' => 'AND',
										'clauses' => [
											['field' => 'advisorclass_call_sign', 'value' => $advisor_call_sign, 'compare' => '=' ],
											['field' => 'advisorclass_semester', 'value' => $prevSemester, 'compare' => '=' ]
										]
									];
									$orderby = 'advisorclass_sequence';
									$order = 'ASC';
									$prevAdvisorClassData = $advisorclass_dal->get_advisorclasses_by_order($criteria,$orderby,$order,$operatingMode);
									if ($prevAdvisorClassData === FALSE) {
										if ($doDebug) {
											echo "getting prev advisorclass data returned FALSE<br />";
										}
									} else {
										if (! empty($prevAdvisorClassData)) {
											$myInt = count($prevAdvisorClassData);
											if ($doDebug) {
												echo "have $myInt prevAdvisorClassData records<br />";
											}
											foreach($prevAdvisorClassData as $key => $value) {
												foreach($value as $thisField => $thisValue) {
													$thisField = str_replace('advisorclass','prevadvisorclass',$thisField);
													$$thisField = $thisValue;
												}
		
												if ($doDebug) {
													echo "&nbsp;&nbsp;&nbsp;Class: $prevadvisorclass_call_sign sequence: $prevadvisorclass_sequence; evals: $prevadvisorclass_evaluation_complete; students: $prevadvisorclass_number_students<br />";
												}
												if ($prevadvisorclass_evaluation_complete != 'Y' && $prevadvisorclass_number_students > 0) {			// evaluations are not done
													if ($doDebug) {
														echo "&nbsp;&nbsp;&nbsp;Setting advisor score to 9<br />";
													}
													$evalsDone					= FALSE;
												} else {
													if ($doDebug) {
														echo "&nbsp;&nbsp;&nbsp;Evals are completefor this class<br />";
													}
												}
											}
											if (!$evalsDone) {
												if ($doDebug) {
													echo "&nbsp;&nbsp;&nbsp;Evals for $advisor_call_sign not complete<br />";
												}
												$userUpdateParams['user_survey_score'] = '9';
												$possContent					.= "&nbsp;&nbsp;&nbsp;$user_call_sign: Evaluations are incomplete. Setting survey score to 9<br />";
												$doContent						= TRUE;
												$updateUser						= TRUE;
												$advisorEvalNOK++;
												$actionLogData					= "Evaluations incomplete. Set survey score to 9 ";
											} else {						// evals are done
												$advisorEvalOK++;
												if ($doDebug) {
													echo "&nbsp;&nbsp;&nbsp;Evals for $advisor_call_sign done. Checking survey score<br />";
												}
												if ($user_survey_score == 9) {			// set survey score back to blank
													$userUpdateParams['user_survey_score'] = 0;
													$possContent					.= "&nbsp;&nbsp;&nbsp;$user_call_sign: Evaluations are complete. Setting survey score from 9 to 0<br />";
													$doContent						= TRUE;
													$updateUser						= TRUE;
													$actionLogData					= "Evaluations complete. Set survey score to 0 ";
												}
											}
											if ($updateUser) { 
												if ($updateMode) {
													if ($doDebug) {
														echo "&nbsp;&nbsp;&nbsp;updateMode is TRUE so updating user_master<br />";
													}
													$user_action_log			.= " / $actionDate ASSGNPREP $userName $actionLogData";
													$userUpdateParams['user_action_log'] = $user_action_log;
													$updateResult = $user_dal->update( $user_ID, $data, $operatingMode );
													if ($updateResult === FALSE) {
														if ($doDebug) {
															echo "updating user_master for id $user_ID returned FALSE<br />";
														}
													}
												} else {
													if ($doDebug) {
														echo "&nbsp;&nbsp;&nbsp;updateMode is FALSE<br />";
													}
												
												}
											}
										} else {
											if ($doDebug) {
												echo "&nbsp;&nbsp;&nbsp;No previous class records<br />";
											}
										}
									}
								}
								if ($doDebug) {
									echo "end of not K1BG advisor<br />";
								}
							} else {
								if ($doDebug) {
									echo "No user_master record found for $advisor_call_sign<br />";
								}
							}
						}
					}
					if ($doDebug) {
						echo "end of user big loop<br />";
					}
					if ($doContent) {
						$content	.= $possContent;
					}
				} else {		
					$content	.= "No advisor records were found in $proximateSemester semester<br />";	
				}
			}
		}
	}
	if ($strPass == 2) {
		$content		.= "<br /><p><b>Counts:</b><br />
							<table>
							<tr><td style='text-align:right;'>$numSRows</td>
								<td>Student records processed</td></tr>
							<tr><td style='text-align:right;'>$promSameClass</td>
								<td>Promotable students taking same class</td></tr>
							<tr><td style='text-align:right;'>$npromSameClass</td>
								<td>Not Promotable students taking same class</td></tr>
							<tr><td style='text-align:right;'>$npromHigherClass</td>
								<td>Not Promotable students wanting higher level class (on hold, getting an email)</td></tr>
							<tr><td style='text-align:right;'>$qSameClass</td>
								<td>Q status students taking same class</td></tr>
							<tr><td style='text-align:right;'>$qHigherClass</td>
								<td>Q status students wanting a higher level class (on hold, getting an email)</td></tr>
							<tr><td style='text-align:right;'>$wSameClass</td>
								<td>Withdrawn students taking same level class</td></tr>
							<tr><td style='text-align:right;'>$wHigherClass</td>
								<td>Withdrawn students wanting a higher level class (on hold, getting an email)</td></tr>
							<tr><td style='text-align:right;'>$neSameClass</td>
								<td>Not evaluated students taking same level class</td></tr>
							<tr><td style='text-align:right;'>$neHigherClass</td>
								<td>Not evaluated students wanting a higher level class (on hold, getting an email)</td></tr>
							<tr><td	 style='text-align:right;'>$invalidYouth</td>
								<td>Invalid youth designation changed</td></tr>
							<tr><td	 style='text-align:right;'>$carryForward</td>
								<td>Excluded advisors carried forward</td></tr>
							<tr><td	 style='text-align:right;'>$priorityUp</td>
								<td>Students whose priority was sent to 1<td></tr>
							<tr><td style='text-align:right;'>$emailCount</td>
								<td>Emails sent</td></tr>
							<tr><td style='text-align:right;'>$noCatalogCount</td>
								<td>Students needing no_catalog entry to be removed</td></tr>
							<tr><td style='text-align:right;'>$emailErrors</td>
								<td>Email Errors</td></tr>
							<tr><td style='text-align:right;'>$updateCount</td>
								<td>Student records needing updates</td></tr>
							<tr><td style='text-align:right;'>$advisorCount</td>
								<td>Advisor records processed</td></tr>
							<tr><td style='text-align:right;'>$advisorEvalOK</td>
								<td>Advisors had classes and completed evaluations</td></tr>
							<tr><td style='text-align:right;'>$advisorEvalNOK</td>
								<td>Advisors had classes and evaluations are incomplete</td></tr>
							</table>";
	}
	$content		.= "<br /><p>To return to the Student Management menu, click
						<a href='studentManagementURL?strpass=1'>HERE</a>.</p>";

	$thisTime 		= date('Y-m-d H:i:s',$currentTimestamp);
	$content 		.= "<br /><br /><p>Prepared at $thisTime</p>";
	$endingMicroTime = microtime(TRUE);
	$elapsedTime	= $endingMicroTime - $startingMicroTime;
	$elapsedTime	= number_format($elapsedTime, 4, '.', ',');
	$content		.= "<p>Report pass $strPass took $elapsedTime seconds to run</p>";
	$nowDate		= date('Y-m-d');
	$nowTime		= date('H:i:s');
	$thisStr			= 'Production';
	$result			= write_joblog_func("$jobname|$nowDate|$nowTime|$userName|Time|$thisStr|$strPass: $elapsedTime");
	if ($result == 'FAIL') {
		$content	.= "<p>writing to joblog.txt failed</p>";
	}
	return $content;
}
add_shortcode ('prepare_students_for_assignment_to_advisors', 'prepare_students_for_assignment_to_advisors_func');

