function prepare_advisor_class_display($inp_advisor='', $inp_semester='', $all='Full', $showVerified=TRUE, $header=FALSE, $testMode=FALSE, $doDebug=FALSE) {

/*	Parameters:

	Input:	inp_advisor: Advisor call sign 
			inp_semester: Semester for the information
			all: either 'Full' (show full class) or 'sonly' (show only student status S students)
			showVerified: 'Y' (show the verified field) or 'N' (don't show verified field)
			header: TRUE: show advisor header or FALSE: no advisor header
			
	Returns an array(TRUE/FALSE,Display Data or Error,classes count,student count)
	
	Modified 16Apr23 by Roland to fix action_log
	Modified 13Jul23 by Roland to use consolidated tables
	Modified 11Oct24 by Roland to use new database
*/

	global $wpdb;
	
	if ($doDebug) {
		echo "<br /><b>FUNCTION Prepare Advisor Class Display</b><br />";
	}
	
	$context = CWA_Context::getInstance();
	$siteURL				= $context->siteurl;
	
	$advisorVerificationURL		= "$siteURL/cwa-advisor-verification-of-student/";

	if ($testMode) {
		$advisorTableName			= 'wpw1_cwa_advisor2';
		$advisorClassTableName		= 'wpw1_cwa_advisorclass2';
		$studentTableName			= 'wpw1_cwa_student2';
		$audioAssessmentTableName	= 'wpw1_cwa_audio_assessment2';
		$newAssessmentData			= 'wpw1_cwa_new_assessment_data';
		$thisMode					= 'TM';
		if ($doDebug) {
			echo "Operating in testMode<br />";
		}
	} else {
		$advisorTableName			= 'wpw1_cwa_advisor';
		$advisorClassTableName		= 'wpw1_cwa_advisorclass';
		$studentTableName			= 'wpw1_cwa_student';
		$audioAssessmentTableName	= 'wpw1_cwa_audio_assessment';
		$newAssessmentData			= 'wpw1_cwa_new_assessment_data';
		$thisMode					= 'PM';
	}
	
	$content						= '';
	$myCount						= 0;
	$classCount						= 0;
	$studentCount					= 0;
	$totalClasses					= 0;
	$countArray						= array();

	$prevClass						= "0";

	// get appropriate advisor record
	$sql				= "select * from $advisorTableName 
							left join $userMasterTableName on user_call_sign = advisor_call_sign 
						    where advisor_call_sign='$inp_advisor' 
						    and advisor_semester='$inp_semester'";
	$wpw1_cwa_advisor	= $wpdb->get_results($sql);
	if ($wpw1_cwa_advisor === FALSE) {
		handleWPDBError($jobname,$doDebug);
	} else {
		$numARows			= $wpdb->num_rows;
		if ($doDebug) {
			echo "ran $sql<br />and found $numARows rows in $advisorTableName table<br />";
		}
		if ($numARows > 0) {
			foreach ($wpw1_cwa_advisor as $advisorRow) {
				$user_ID 							= $advisorRow->user_ID;
				$user_call_sign						= $advisorRow->user_call_sign;
				$user_first_name 					= $advisorRow->user_first_name;
				$user_last_name 					= $advisorRow->user_last_name;
				$user_email 						= $advisorRow->user_email;
				$user_ph_code 						= $advisorRow->user_ph_code;
				$user_phone 						= $advisorRow->user_phone;
				$user_city 							= $advisorRow->user_city;
				$user_state 						= $advisorRow->user_state;
				$user_zip_code 						= $advisorRow->user_zip_code;
				$user_country_code 					= $advisorRow->user_country_code;
				$user_country 						= $advisorRow->user_country;
				$user_whatsapp 						= $advisorRow->user_whatsapp;
				$user_telegram 						= $advisorRow->user_telegram;
				$user_signal 						= $advisorRow->user_signal;
				$user_messenger 					= $advisorRow->user_messenger;
				$user_action_log 					= $advisorRow->user_action_log;
				$user_timezone_id 					= $advisorRow->user_timezone_id;
				$user_languages 					= $advisorRow->user_languages;
				$user_survey_score 					= $advisorRow->user_survey_score;
				$user_is_admin						= $advisorRow->user_is_admin;
				$user_role 							= $advisorRow->user_role;
				$user_date_created 					= $advisorRow->user_date_created;
				$user_date_updated 					= $advisorRow->user_date_updated;

				$advisor_ID							= $advisorRow->advisor_id;
				$advisor_call_sign 					= strtoupper($advisorRow->advisor_call_sign);
				$advisor_semester 					= $advisorRow->advisor_advisor_semester;
				$advisor_welcome_email_date 		= $advisorRow->advisor_welcome_email_date;
				$advisor_verify_email_date 			= $advisorRow->advisor_verify_email_date;
				$advisor_verify_email_number 		= $advisorRow->advisor_verify_email_number;
				$advisor_verify_response 			= strtoupper($advisorRow->advisor_verify_response);
				$advisor_action_log 				= $advisorRow->advisor_action_log;
				$advisor_class_verified 			= $advisorRow->advisor_class_verified;
				$advisor_control_code 				= $advisorRow->advisor_control_code;
				$advisor_date_created 				= $advisorRow->advisor_date_created;
				$advisor_date_updated 				= $advisorRow->advisor_date_updated;
				$advisor_replacement_status 		= $advisorRow->advisor_replacement_status;


				$verifyStr		= "";
				if ($advisor_verify_response == '') {
					$verifyStr	= "<br /><b>Unverified</b>";
				}
				
				if ($header) {
					$content	.= "<table style='width:900px;'>
									<tr><td><table style='width:900px;'>
									<tr><th style='text-align:left;'>Advisor</th>\n
										<th style='text-align:left;'>Email</th>\n
										<th style='text-align:left;'>Phone</th>\n
										<th style='text-align:left;'>State</th>\n
										<th colspan='2' style='text-align:left;'>Country</th></tr>\n
									<tr><td style='vertical-align:top;'>$user_last_name, $user_first_name ($user_call_sign)$verifyStr</td>\n
										<td style='vertical-align:top;'>$user_email</td>\n
										<td style='vertical-align:top;'>$user_ph_code $user_phone</td>\n
										<td style='vertical-align:top;'>$user_state</td>\n
										<td colspan='2' style='vertical-align:top;'>$user_country</td></tr>
									</table></td></tr>";
				} else {
					$content	 = "<h3>Advisor $user_last_name, $user_first_name ($inp_advisor) Class Participants for $inp_semester Semester</h3>
									<p>Following are your current student assignments  for the $inp_semester Semester.</p>
									<table>";
				}
				$firstAdvisorClass	= TRUE;

				//// get the advisor's classes
				$classesArray		= array();
				$wpw1_cwa_advisorclass				= $wpdb->get_results("select * from $advisorClassTableName 
																		  where advisor_call_sign = '$advisor_call_sign'
																		  and semester = '$advisor_semester'
																		  order by sequence");
				$sql					= "select * from $advisorClassTableName 
											  where advisorclass_call_sign = '$advisor_call_sign'
											  and advisorclass_semester = '$advisor_semester'
											  order by advisorclass_sequence";
				$wpw1_cwa_advisorclass	= $wpdb->get_results($sql);
				if ($wpw1_cwa_advisorclass === FALSE) {
					handleWPDBError($jobname,$doDebug);
				} else {
					$numACRows			= $wpdb->num_rows;
					if ($doDebug) {
						echo "ran $sql<br />and found $numACRows rows<br />";
					}
					if ($numACRows > 0) {
						foreach ($wpw1_cwa_advisorclass as $advisorClassRow) {
							$advisorClass_ID				 		= $advisorClassRow->advisorclass_id;
							$advisorClass_call_sign 				= $advisorClassRow->advisorclass_call_sign;
							$advisorClass_sequence 					= $advisorClassRow->advisorclass_sequence;
							$advisorClass_semester 					= $advisorClassRow->advisorclass_semester;
							$advisorClass_timezone_offset			= $advisorClassRow->advisorclass_timezone_offset;	// new
							$advisorClass_level 					= $advisorClassRow->advisorclass_level;
							$advisorClass_class_size 				= $advisorClassRow->advisorclass_class_size;
							$advisorClass_class_schedule_days 		= $advisorClassRow->advisorclass_class_schedule_days;
							$advisorClass_class_schedule_times 		= $advisorClassRow->advisorclass_class_schedule_times;
							$advisorClass_class_schedule_days_utc 	= $advisorClassRow->advisorclass_class_schedule_days_utc;
							$advisorClass_class_schedule_times_utc 	= $advisorClassRow->advisorclass_class_schedule_times_utc;
							$advisorClass_action_log 				= $advisorClassRow->advisorclass_action_log;
							$advisorClass_class_incomplete 			= $advisorClassRow->advisorclass_class_incomplete;
							$advisorClass_date_created				= $advisorClassRow->advisorclass_date_created;
							$advisorClass_date_updated				= $advisorClassRow->advisorclass_date_updated;
							$advisorClass_student01 				= $advisorClassRow->advisorclass_student01;
							$advisorClass_student02 				= $advisorClassRow->advisorclass_student02;
							$advisorClass_student03 				= $advisorClassRow->advisorclass_student03;
							$advisorClass_student04 				= $advisorClassRow->advisorclass_student04;
							$advisorClass_student05 				= $advisorClassRow->advisorclass_student05;
							$advisorClass_student06 				= $advisorClassRow->advisorclass_student06;
							$advisorClass_student07 				= $advisorClassRow->advisorclass_student07;
							$advisorClass_student08 				= $advisorClassRow->advisorclass_student08;
							$advisorClass_student09 				= $advisorClassRow->advisorclass_student09;
							$advisorClass_student10 				= $advisorClassRow->advisorclass_student10;
							$advisorClass_student11 				= $advisorClassRow->advisorclass_student11;
							$advisorClass_student12 				= $advisorClassRow->advisorclass_student12;
							$advisorClass_student13 				= $advisorClassRow->advisorclass_student13;
							$advisorClass_student14 				= $advisorClassRow->advisorclass_student14;
							$advisorClass_student15 				= $advisorClassRow->advisorclass_student15;
							$advisorClass_student16 				= $advisorClassRow->advisorclass_student16;
							$advisorClass_student17 				= $advisorClassRow->advisorclass_student17;
							$advisorClass_student18 				= $advisorClassRow->advisorclass_student18;
							$advisorClass_student19 				= $advisorClassRow->advisorclass_student19;
							$advisorClass_student20 				= $advisorClassRow->advisorclass_student20;
							$advisorClass_student21 				= $advisorClassRow->advisorclass_student21;
							$advisorClass_student22 				= $advisorClassRow->advisorclass_student22;
							$advisorClass_student23 				= $advisorClassRow->advisorclass_student23;
							$advisorClass_student24 				= $advisorClassRow->advisorclass_student24;
							$advisorClass_student25 				= $advisorClassRow->advisorclass_student25;
							$advisorClass_student26 				= $advisorClassRow->advisorclass_student26;
							$advisorClass_student27 				= $advisorClassRow->advisorclass_student27;
							$advisorClass_student28 				= $advisorClassRow->advisorclass_student28;
							$advisorClass_student29 				= $advisorClassRow->advisorclass_student29;
							$advisorClass_student30 				= $advisorClassRow->advisorclass_student30;
							$advisorClass_number_students			= $advisorClassRow->advisorclass_number_students;
							$advisorClass_class_evaluation_complete = $advisorClassRow->advisorclass_evaluation_complete;
							$advisorClass_class_comments			= $advisorClassRow->advisorclass_class_comments;
							$advisorClass_copycontrol				= $advisorClassRow->advisorclass_copy_control;



							if ($doDebug) {
								echo "starting advisor class $advisorClass_sequence<br />";
							}
							if ($showVerified) {
								$myStr		= "<br />Students needing verification will have an 'unverified' link in the column titled '<em>Verify Link</em>'. 
												Click the 'unverified' link to confirm their status<br />
												If needed, update verified student information by clicking on the 'verified' link";
							} else {
								$myStr		= '';
							}
							if ($firstAdvisorClass) {
								$firstAdvisorClass 	= FALSE;
							} else {
								$content	.= "<tr><td><hr></td></tr>";
							}
							$content		.= "<tr><td><b>Students for $advisor_call_sign $advisorClass_level class Number $advisorClass_sequence</b> 
												&nbsp;&nbsp;&nbsp;($advisorClass_class_schedule_times $advisorClass_class_schedule_days Local -- $advisorClass_class_schedule_times_utc $advisorClass_class_schedule_days_utc UTC)
												$myStr</td></tr>";
							$doOnce			= TRUE;
							$classCount		= 0;
							// get students assigned to this advisor class
							for ($snum=1;$snum<31;$snum++) {
								if ($snum < 10) {
									$strSnum 		= str_pad($snum,2,'0',STR_PAD_LEFT);
								} else {
									$strSnum		= strval($snum);
								}
								$theInfo			= ${'advisorClass_student' . $strSnum};
								if ($theInfo != '') {
									if ($doDebug) {
										echo "<br />process student$strSnum $theInfo<br />";
									}
									$sql				= "select * from $studentTablename 
															left join $userMasterTableName on user_call_sign = student_call_sign 
															where student_semester='$inp_semester' 
															and student_call_sign = '$theInfo'";
									$wpw1_cwa_student	= $wpdb->get_results($sql);
									if ($wpw1_cwa_student === FALSE) {
										handleWPDBError($jobname,$doDebug);
									} else {
										$numSRows									= $wpdb->num_rows;
										if ($doDebug) {
											echo "ran $sql<br />and retrieved $numSRows rows from $studentTableName table<br >";
										}
										if ($numSRows > 0) {
											foreach ($wpw1_cwa_student as $studentRow) {
												$user_ID 								= $studentRow->user_ID;
												$user_call_sign 						= $studentRow->user_call_sign;
												$user_first_name 						= $studentRow->user_first_name;
												$user_last_name 						= $studentRow->user_last_name;
												$user_email 							= $studentRow->user_email;
												$user_ph_code 							= $studentRow->user_ph_code;
												$user_phone 							= $studentRow->user_phone;
												$user_city 								= $studentRow->user_city;
												$user_state 							= $studentRow->user_state;
												$user_zip_code 							= $studentRow->user_zip_code;
												$user_country_code 						= $studentRow->user_country_code;
												$user_country 							= $studentRow->user_country;
												$user_whatsapp 							= $studentRow->user_whatsapp;
												$user_telegram 							= $studentRow->user_telegram;
												$user_signal 							= $studentRow->user_signal;
												$user_messenger 						= $studentRow->user_messenger;
												$user_action_log 						= $studentRow->user_action_log;
												$user_timezone_id 						= $studentRow->user_timezone_id;
												$user_languages 						= $studentRow->user_languages;
												$user_survey_score 						= $studentRow->user_survey_score;
												$user_is_admin							= $studentRow->user_is_admin;
												$user_role 								= $studentRow->user_role;
												$user_date_created 						= $studentRow->user_date_created;
												$user_date_updated 						= $studentRow->user_date_updated;
							
												$student_ID								= $studentRow->student_id;
												$student_call_sign						= $studentRow->call_sign;
												$student_time_zone  					= $studentRow->time_zone;
												$student_timezone_offset				= $studentRow->timezone_offset;
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
												$student_no_catalog  					= $studentRow->no_catalog;
												$student_hold_override  				= $studentRow->hold_override;
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
												$student_catalog_options				= $studentRow->catalog_options;
												$student_flexible						= $studentRow->flexible;
												$student_date_created 					= $studentRow->date_created;
												$student_date_updated			  		= $studentRow->date_updated;
							

												if ($doDebug) {
													echo "&nbsp;&nbsp;&nbsp;&nbsp;Level: $student_level<br />
															&nbsp;&nbsp;&nbsp;&nbsp;Class: $student_assigned_advisor_class<br />
															&nbsp;&nbsp;&nbsp;&nbsp;Status: $student_student_status<br />
															&nbsp;&nbsp;&nbsp;&nbsp;Promotable: $student_promotable<br />";
												}
												$processStudent			= TRUE;
												if ($student_promotable == 'W') {
													$processStudent		= FALSE;
												}
												if ($processStudent) {
													if ($all == 'Full') {
														$processStudent		= TRUE;
													} else {
														if ($student_student_status == 'S') {
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
													$content			.= "<tr><td><table style='border-bottom-style:solid;width:900px;'>
																			<tr><td style='vertical-align:top;width:100px;'><b>Call Sign</b></td>
																				<td style='vertical-align:top;width:150px;'><b>Name</b></td>
																				<td style='vertical-align:top;width:200px;'><b>Email</b></td>
																				<td style='vertical-align:top;width:200px;'><b>Phone</b></td>
																				<td style='vertical-align:top;width:100px;'><b>State</b></td>
																				<td style='vertical-align:top;width:100px;'><b>Country</t></td>
																				<td style='vertical-align:top;width:100px;'><b>$verifyTitle<td></tr>";
										
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
													if ($showVerified) {
														if ($student_student_status == 'S') {
															$myStr						= " <a href='$advisorVerificationURL?advisorCallSign=$advisor_call_sign&studentCallSign=$student_call_sign&mode=$thisMode&strpass=2'><b>unverified</b></a>";
														} elseif ($student_student_status == 'Y') {
															$myStr						= "<a href='$advisorVerificationURL?advisorCallSign=$advisor_call_sign&studentCallSign=$student_call_sign&mode=$thisMode&strpass=2' style='color:green;'>verified</a>";
														}
													} else {
														$myStr						= $student_student_status;
													}
													$content						.= "<tr><td style='vertical-align:top;'>$student_call_sign</td>
																							<td style='vertical-align:top;'>$user_last_name, $user_first_name</td>
																							<td style='vertical-align:top;'>$user_email</td>
																							<td style='vertical-align:top;'>+$user_ph_code $user_phone</td>
																							<td style='vertical-align:top;'>$user_state</td>
																							<td style='vertical-align:top;'>$user_country</td>
																							<td style='vertical-align:top;'>$myStr</td></tr>";
													if ($haveExtras) {
														$content					.= "<tr><td colspan='8'>$extras</td></tr>";
													}
													$student_parent			= '';
													$student_parent_email	= '';
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
															$content	.= "<tr><td colspan='8'>The student has registered as a youth under the age of 18. The student's 
																			parent or guardian is $thisParent at email address $thisParentEmail.</td></tr>";
														}
													}

													if ($hasAssessment) {
														$enstr		= base64_encode("advisor_call_sign=$student_assigned_advisor&inp_callsign=$student_call_sign");
														$content	.= "<tr><td colspan='8'>Click <a href='$siteURL/cwa-view-a-student-assessment/?strpass=2&enstr=$enstr' target='_blank'>HERE</a> to review $student_call_sign's self assessment</td></tr>";
													}
													$content		.= "</table>";
													if ($doDebug) {
														echo "student added to display<br />";
													}
												}
											}			/// end of the student foreach
										} else {		/// no student record found ... send error message
											$myStr		= $wpdb->last_query;
											sendErrorEmail("Prepare Advisor Class Display: ran $sql. No $studentTableName table record found for student$strSnum $theInfo in advisor $advisorClass_advisor_call_sign class $advisorClass_sequence<br />SQL: $myStr");
											if ($doDebug) {
												echo "no $studentTableName table record found for student$strSnum $theInfo in advisor $advisorClass_advisor_call_sign class $advisorClass_sequence<br />SQL: $myStr";
											}
											$content	.= "<tr><td>$theInfo</td>
																<td colspan='6'>No matching student table record</td></tr>";
										}
									}
								}
							}
							$content				.= "<tr><td>$classCount Students. Class size: $advisorClass_class_size</td></tr>";
							$countArray[$advisorClass_sequence]['students']	= $classCount;
							$countArray[$advisorClass_sequence]['size']		= $advisorClass_class_size;
						}
						// Got all students. Now complete the display
						$content .= "<tr><td>$studentCount Total Students in all Classes</td></tr>
									</table>";
					} else {
						if ($doDebug) {	
							echo "no advisorClass record found $advisorClassTableName for $inp_advisor<br />";
						}
						$content			.= "<b>ERROR</b> No advisorClass record foound for $inp_advisor<br />";
					}
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
add_action('prepare_advisor_class_display', 'prepare_advisor_class_display');
