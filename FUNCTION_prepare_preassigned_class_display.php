function prepare_preassigned_class_display($inp_advisor='', $inp_semester='', $all='Full', $showVerified=TRUE, $header=FALSE, $doPreAssigned=FALSE, $doFind=FALSE, $testMode=FALSE, $doDebug=FALSE) {

/*	Parameters:

	Input:	inp_advisor: Advisor call sign 
			inp_semester: Semester for the information
			all: either 'Full' (show full class) or 'sonly' (show only student status S students)
			showVerified: 'Y' (show the verified field) or 'N' (don't show verified field)
			header: TRUE: show advisor header or FALSE: no advisor header
			doPreAssigned: TRUE: do pre_assigned as well as assigned or FALSE: do assigned only
			doFind: not used but required
			
	Returns an array(TRUE/FALSE,Display Data or Error,classes count,student count)

*/

	global $wpdb;
	
	if ($doDebug) {
		echo "<br /><b>FUNCTION Prepare Advisor Class Display</b><br />";
	}
	
	$context = CWA_Context::getInstance();
	$siteURL				= $context->siteurl;
	
	$advisorVerificationURL		= "$siteURL/cwa-advisor-verification-of-student/";

	if ($testMode) {
		$audioAssessmentTableName	= 'wpw1_cwa_audio_assessment2';
		$newAssessmentData			= 'wpw1_cwa_new_assessment_data';
		$thisMode					= 'TM';
		$operatingMode = 'Tesstmode';
		if ($doDebug) {
			echo "Operating in testMode<br />";
		}
	} else {
		$audioAssessmentTableName	= 'wpw1_cwa_audio_assessment';
		$newAssessmentData			= 'wpw1_cwa_new_assessment_data';
		$thisMode					= 'PM';
		$operatingMode = 'Production';
	}
	
	$student_dal = new CWA_Student_DAL();
	$advisor_dal = new CWA_Advisor_DAL();
	$advisorclass_dal = new CWA_Advisorclass_DAL();
	$user_dal = new CWA_User_Master_DAL();	
	
	$content						= '';
	$myCount						= 0;
	$classCount						= 0;
	$studentCount					= 0;
	$totalClasses					= 0;
	$countArray						= array();

	$prevClass						= "0";

	// get appropriate advisor record
	$advisorData = get_advisor_and_user_master($inp_advisor, 'callsign', $inp_semester, $operatingMode, $doDebug);
	if ($advisorData === FALSE) {
		if ($doDebug) {
			echo "get_advisor_and_user_aster for $inp_advisor returned FALSE<br />";
		}
	} else {
		if (! empty($advisorData)) {
			foreach($advisorData as $key => $value) {
				$$key = $value;
			}
				
			$verifyStr		= "";
			if ($advisor_verify_response == '') {
				$verifyStr	= "<br /><b>Unverified</b>";
			}
			
			if ($header) {
				$content	.= "<table style='width:1000px;'>
								<tr><th style='text-align:left;'>Advisor</th>\n
									<th style='text-align:left;'>Email</th>\n
									<th style='text-align:left;'>Phone</th>\n
									<th style='text-align:left;'>State</th>\n
									<th colspan='2' style='text-align:left;'>Country</th>
									<th></th></tr>\n
								<tr><td style='vertical-align:top;'><b>$user_last_name, $user_first_name ($advisor_call_sign)</b></td>\n
									<td style='vertical-align:top;'>$user_email</td>\n
									<td style='vertical-align:top;'>$user_ph_code $user_phone</td>\n
									<td style='vertical-align:top;'>$user_state</td>\n
									<td colspan='2' style='vertical-align:top;'>$user_country</td>
									<td></td></tr>\n";
			} else {
				$content	 = "<h3>Advisor $user_last_name, $user_first_name ($inp_advisor) Class Participants for $inp_semester Semester</h3>
								<p>Following are your current student assignments  for the $inp_semester Semester.</p>
								<table>";
			}
			$firstAdvisorClass	= TRUE;

			//// get the advisor's classes
			$classesArray		= array();
			$criteria = [
				'relation' => 'AND',
				'clauses' => [
					['field' => 'advisorclass_call_sign', 'value' => $advisor_call_sign, 'compare' => '=' ],
					['field' => 'advisorclass_semester', 'value' => $advisor_semester, 'compare' => '=' ]
				]
			];

			$advisorclassData = $advisorclass_dal->get_advisorclasses_by_order( $criteria, 'advisorclass_sequence', 'ASC', $operatingMode );
			if ($advisorclassData === FALSE || $advisorclassData === NULL) {
				if ($doDebug) {
					echo "get_advisor_classes_by_order for $advisor_call_sign returned FALSE|NUL<br />";
				}
			} else {
				if(! empty($advisorclassData)) {
					$jj = 0;
					$totalClasses = count($advisorclassData);
					foreach($advisorclassData as $key => $value) {
						foreach($value as $thisField => $thisValue) {
							$$thisField = $thisValue;
						}
						if ($doDebug) {
							echo "<br />starting advisor class $advisorclass_sequence<br />";
						}
						$jj++;
						if ($jj > 1) {
							$content .= "<tr><td colspan='7'><hr></td></tr>";
						}
						$content .= "<tr><td colspan='7'><b>Advisor Class #$jj</b><br />
											<b>Class Size:</b> $advisorclass_class_size &nbsp;&nbsp;&nbsp;&nbsp;
											<b>Level: </b>$advisorclass_level &nbsp;&nbsp;&nbsp;&nbsp;
											<b>anguage:</b> $advisorclass_language &nbsp;&nbsp;&nbsp;&nbsp;
											<b>Local:</b> $advisorclass_class_schedule_times $advisorclass_class_schedule_days &nbsp;&nbsp;&nbsp;&nbsp;
											<b>UTC:</b> $advisorclass_class_schedule_times_utc $advisorclass_class_schedule_days_utc</td></tr>";

						// if doing pre_assigned rather than assigned, fill in the student list
						if ($doPreAssigned) {
							if ($doDebug) {
								echo "<br />Looking for pre-assigned not yet assigned<br />";
							}
							$criteria = [
								'relation' => 'AND',
								'clauses' => [
									['field' => 'student_semester', 'value' => $inp_semester, 'compare' => '='],
									['field' => 'student_pre_assigned_advisor', 'value' => $advisor_call_sign, 'compare' => '='],
									['field' => 'student_assigned_advisor_class', 'value' => $advisorclass_sequence, 'compare' => '='],
									['field' => 'student_assigned_advisor', 'value' => '', 'compare' => '='],
									['field' => 'student_response', 'value' => 'Y', 'compare' => '=']
								]
							];
							$requestInfo = array('criteria' => $criteria,
												 'orderby' => 'student_call_sign',
												 'order' => 'ASC');
							$preResult = get_student_and_user_master('', 'complex', $requestInfo, $operatingMode, $doDebug) ;
							if ($preResult === FALSE || $preResult === NULL) {
								if ($doDebug) {
									echo "getting preResult returned FALSE|NULL<br/>";
								}
							} else {
								if (! empty($preResult)) {
									$ii = 0;
									foreach($preResult as $key => $value) {
										foreach($value as $thisField => $thisValue) {
											$$thisField = $thisValue;
										}
										$useThisStudent			= TRUE;

										// see if this advisor is excluded
										if (str_contains($student_excluded_advisor,$advisor_call_sign)) {
											if ($doDebug) {
												echo "advisor is excluded<br />";
											}
											$useThisStudent		= FALSE;
										}
										
										// see if student is on hold
										if ($student_intervention_required == 'H') {
											if ($student_hold_reasonCode != 'X') {
												if ($doDebug) {
													echo "student is on hold<br />";
												}
												$useThisStudent	= FALSE;
											}
										}											
										
										if ($useThisStudent) {
											$studentCount++;
											$ii++;
											$classCount++;
											$studentLink	= "<a href='$siteURL/cwa-display-and-update-student-signup-information/?strpass=2&request_type=callsign&request_info=$student_call_sign&inp_depth=one&doDebug&testMode' target='_blank'>$student_call_sign</a>";
											$myStr = "";
											$content	.= "<tr><td style='vertical-align:top;'><b>Student $ii</b><br />$user_last_name, $user_first_name ($studentLink)</td>
																<td style='vertical-align:top;'><b>Email</b><br />$user_email<br />$student_first_class_choice_utc</td>
																<td style='vertical-align:top;'><b>Phone</b><br />$user_ph_code $user_phone<br />$student_second_class_choice_utc</td>
																<td style='vertical-align:top;'><b>State</b><br />$user_state<br />$student_third_class_choice_utc</td>
																<td style='vertical-align:top;'><b>Country</b><br/>$user_country</td>
																<td style='vertical-align:top;'><b>Language</b><br />$student_class_language</td>
																<td style='vertical-align:top;'><b>Status<br />$myStr<td></tr>";
// 										
											/// check to see if there are assessment records for this student
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
											$newAssessmentCount		= $wpdb->get_var("select count(record_id) 
																	   from $newAssessmentData 
																		where callsign='$student_call_sign'");
																		
											if ($newAssessmentCount > 0) {
												$hasAssessment	= TRUE;
												if ($doDebug) {
													echo "have assessment records<br />";
												}
											}
											$extras							= "Additional contact options: ";
											$haveExtras						= FALSE;
											if ($user_whatsapp != '' ) {
												$extras						.= "WhatsApp: $user_whatsapp ";
												$haveExtras					= TRUE;
											}
											if ($user_signal != '' ) {
												$extras						.= "Signal: $user_signal ";
												$haveExtras					= TRUE;
											}
											if ($user_telegram != '' ) {
												$extras						.= "Telegram: $user_telegram ";
												$haveExtras					= TRUE;
											}
											if ($user_messenger != '' ) {
												$extras						.= "Messenger: $user_messenger ";
												$haveExtras					= TRUE;
											}
									

											if ($haveExtras) {
												$content					.= "<tr><td colspan='7'>$extras</td></tr>";
											}
											$thisParent			= '';
											$thisParentEmail	= '';
											if ($student_youth == 'Yes') {
												if ($student_age < 18) { 
													if ($student_parent == '') {
														$thisParent	= 'Not Given';
													} else {
														$thisParent	= $student_parent;
													}
													if ($student_parent_email == '') {
														$thisParentEmail = 'Not Given';
													} else {
														$thisParentEmail = $student_parent_email;
													}
													$content	.= "<tr><td colspan='7'>The student has registered as a youth under the age of 18. The student's 
																	parent or guardian is $thisParent at email address $thisParentEmail.</td></tr>";
												}
											}

											if ($hasAssessment) {
												$enstr		= base64_encode("advisor_call_sign=$student_assigned_advisor&inp_callsign=$student_call_sign");
												$content	.= "<tr><td colspan='7' style='border-bottom-style:solid;'>Click <a href='$siteURL/cwa-view-a-student-assessment/?strpass=2&enstr=$enstr' target='_blank'>HERE</a> to review $student_call_sign's self assessment</td></tr>";
											} else {
												$content	.= "<tr><td colspan='7' style='border-bottom-style:solid;'></td></tr>";
											}
											if ($doDebug) {
												echo "student added to display<br />";
											}
										} else {
											if ($doDebug) {
												echo "student $student_call_sign bypased<br />";
											}
										}
									}
								} else {
									if ($doDebug) {
										echo "No pre-assigned found<br />";
									}
								}
							}
						} else {	
							 if ($showVerified) {
								$myStr		= "<br />Students needing verification will have an 'unverified' link in the column titled '<em>Verify Link</em>'. 
												Click the 'unverified' link to confirm their status<br />
												If needed, update verified student information by clicking on the 'verified' link";
							} else {
								$myStr		= '';
							}
							$doOnce			= TRUE;
							$classCount		= 0;
							$studentList	= array();
							// get students assigned to this advisor class
							for ($snum=1;$snum<31;$snum++) {
								if ($snum < 10) {
									$strSnum 		= str_pad($snum,2,'0',STR_PAD_LEFT);
								} else {
									$strSnum		= strval($snum);
								}
								$theInfo			= ${'advisorclass_student' . $strSnum};
								if ($theInfo != '') {
									if ($doDebug) {
										echo "<br />adding student$strSnum $theInfo to studentList array<br />";
									}
									$studentList[]	= $theInfo;
								}
							}		/// have the student list. Sort and process
							if (count($studentList) > 0) {
								sort($studentList);
								foreach($studentList as $thisStudent) {
									$studentResult = get_student_and_user_master($thisStudent,'callsign', $advisor_semester, $operatingMode, $doDebug);
									if ($studentResult === FALSE || $studentResult === NULL) {
										if ($doDebug) {
											echo "get_student for $thisStudent returned FALSE|NULL<br />";
										}
									} else {
										if (! empty($studentResult)) {
											foreach($studentResult as $key => $thisValue) {
												$$key = $thisValue;
											}

											if ($doDebug) {
												echo "&nbsp;&nbsp;&nbsp;&nbsp;Level: $student_level<br />
														&nbsp;&nbsp;&nbsp;&nbsp;Class: $student_assigned_advisor_class<br />
														&nbsp;&nbsp;&nbsp;&nbsp;Status: $student_status<br />
														&nbsp;&nbsp;&nbsp;&nbsp;Promotable: $student_promotable<br />";
											}
											$processStudent			= TRUE;
											if ($student_status != 'S' && $student_status != 'Y') {
												$processStudent		= FALSE;
												$studentLink	= "<a href='$siteURL/cwa-display-and-update-student-signup-information/?strpass=2&request_type=callsign&request_info=$thisStudent&inp_depth=one&doDebug&testMode' target='_blank'>$thisStudent</a>";
												sendErrorEmail("FUNCTION Prepare Preassinged Class Display: student $studentLink needs to be removed from $advisorclass_call_sign class $advisorclass_sequence");
												if ($doDebug) {
													echo "<b>ERROR</b>student status is $student_status so not processing<br />";
												}
											}	
											if ($student_promotable == 'W') {
												$processStudent		= FALSE;
											}
											if ($processStudent) {
												if ($all == 'Full') {
													$processStudent		= TRUE;
												} else {
													if ($student_status == 'S') {
														$processStudent	= TRUE;
													} else {
														$processStudent	= FALSE;
													}
												}
											}
								
											if ($processStudent) {
												if ($showVerified) {
													$verifyTitle	= "Verify Link";
												} else {
													$verifyTitle	= "Status";
												}
												$studentCount++;
												$classCount++;
												$studentLink	= "<a href='$siteURL/cwa-display-and-update-student-signup-information/?strpass=2&request_type=callsign&request_info=$student_call_sign&inp_depth=one&doDebug&testMode' target='_blank'>$student_call_sign</a>";
												$myStr = "";
												if ($showVerified) {
													if ($student_status == 'S') {
														$myStr						= " <a href='$advisorVerificationURL?advisorCallSign=$advisor_call_sign&studentCallSign=$student_call_sign&mode=$thisMode&strpass=2'><b>unverified</b></a>";
													} elseif ($student_status == 'Y') {
														$myStr						= "<a href='$advisorVerificationURL?advisorCallSign=$advisor_call_sign&studentCallSign=$student_call_sign&mode=$thisMode&strpass=2' style='color:green;'>verified</a>";
													}
												} else {
													$myStr						= $student_status;
												}
												$content	.= "<tr><td style='vertical-align:top;'><b>Student</b><br />$user_last_name, $user_first_name ($studentLink)</td>
																	<td style='vertical-align:top;'><b>Email</b><br />$user_email<br />$student_first_class_choice_utc</td>
																	<td style='vertical-align:top;'><b>Phone</b><br />$user_ph_code $user_phone<br />$student_second_class_choice_utc</td>
																	<td style='vertical-align:top;'><b>State</b><br />$user_state<br />$student_third_class_choice_utc</td>
																	<td style='vertical-align:top;'><b>Country</b><br/>$user_country</td>
																	<td style='vertical-align:top;'><b>Language</b><br />$student_class_language</td>
																	<td style='vertical-align:top;'><b>$verifyTitle<br />$myStr<td></tr>";
// 										
												/// check to see if there are assessment records for this student
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
												$newAssessmentCount		= $wpdb->get_var("select count(record_id) 
																		   from $newAssessmentData 
																			where callsign='$student_call_sign'");
																			
												if ($newAssessmentCount > 0) {
													$hasAssessment	= TRUE;
													if ($doDebug) {
														echo "have assessment records<br />";
													}
												}
												$extras							= "Additional contact options: ";
												$haveExtras						= FALSE;
												if ($user_whatsapp != '' ) {
													$extras						.= "WhatsApp: $user_whatsapp ";
													$haveExtras					= TRUE;
												}
												if ($user_signal != '' ) {
													$extras						.= "Signal: $user_signal ";
													$haveExtras					= TRUE;
												}
												if ($user_telegram != '' ) {
													$extras						.= "Telegram: $user_telegram ";
													$haveExtras					= TRUE;
												}
												if ($user_messenger != '' ) {
													$extras						.= "Messenger: $user_messenger ";
													$haveExtras					= TRUE;
												}
										

												if ($haveExtras) {
													$content					.= "<tr><td colspan='8'>$extras</td></tr>";
												}
												$thisParent			= '';
												$thisParentEmail	= '';
												if ($student_youth == 'Yes') {
													if ($student_age < 18) { 
														if ($student_parent == '') {
															$thisParent	= 'Not Given';
														} else {
															$thisParent	= $student_parent;
														}
														if ($student_parent_email == '') {
															$thisParentEmail = 'Not Given';
														} else {
															$thisParentEmail = $student_parent_email;
														}
														$content	.= "<tr><td colspan='7'>The student has registered as a youth under the age of 18. The student's 
																		parent or guardian is $thisParent at email address $thisParentEmail.</td></tr>";
													}
												}

												if ($hasAssessment) {
													$enstr		= base64_encode("advisor_call_sign=$student_assigned_advisor&inp_callsign=$student_call_sign");
													$content	.= "<tr><td colspan='8' style='border-bottom-style:solid;'>Click <a href='$siteURL/cwa-view-a-student-assessment/?strpass=2&enstr=$enstr' target='_blank'>HERE</a> to review $student_call_sign's self assessment</td></tr>";
												} else {
													$content	.= "<tr><td colspan='8' style='border-bottom-style:solid;'></td></tr>";
												}
												if ($doDebug) {
													echo "student added to display<br />";
												}
											}			/// end of the student foreach
										}
									}
								}
								$content .= "<tr><td colspan='7' style='border-bottom-style:solid;'>$classCount Students. Class size: $advisorclass_class_size</td></tr>";
							}
						}
					}
					// Got all students. Now complete the display
					$content .= "<tr><td colspan='7' style='border-bottom-style:solid;'>$studentCount Total Students in all Classes</td></tr>
								</table>";
				} else {
					if ($doDebug) {	
						echo "no advisorClass record found $advisorClassTableName for $inp_advisor<br />";
					}
					$content			.= "<b>ERROR</b> No advisorClass record foound for $inp_advisor<br />";
				}
			}
		} else {
			if ($doDebug) {
				echo "no records found in $advisorTableName table for $inp_advisor<br />";
			}
			return array(FALSE,"<b>ERROR</b> No advisor record found for $inp_advisor in $inp_semester semester X<br />",0,0);
		}
	}
	$returnInfo		= array(TRUE,$content,$totalClasses,$studentCount,$countArray);
//	if ($doDebug) {
//		echo "returning:<br /><pre>";
//		print_r($returnInfo);
//		echo "</pre><br />";
//	}
	return $returnInfo;
}
add_action('prepare_preassigned_class_display', 'prepare_oreassigned_class_display');
