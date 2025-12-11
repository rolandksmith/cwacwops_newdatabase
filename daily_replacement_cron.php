		if ($validReplacementPeriod == 'Y') {
			if ($doDebug) {
				echo "<br /><b>REPLACE Array</b> In the replacement period<br />Building the array of 
					  potential replacement students<br />";
			}
		 	////// build the array of potential replacement students
			////// replaceArray[level][priority-reqdate] = student call sign|first choice UTC|second choice UTC|thirdChoice UTC|excluded advisor
		
			$criteria = [
				'relation' => 'AND',
				'clauses' => [
					['field' => 'student_semester', 'value' => $theSemester, 'compare' => '=' ],
					['field' => 'student_response', 'value' => 'Y', 'compare' => '=' ],
					['field' => 'student_status', 'value' => '', 'compare' => '=' ],
					['field' => 'student_intervention_required', 'value' => 'H', 'compare' => '!=' ],
					['field' => 'user_email_number', 'value' => 4, 'compare' => '!=' ],
				]
			];
			$studentData = $student_dal->get_student( $criteria, 'student_level, student_call_sign', 'ASC', $operatingMode );
			if ($studentData === FALSE) {
				if ($doDebug) {
					echo "get_student_and_user_master for $studentToProcess returned FALSE<br />";
				}
			} else {
				if (! empty($studentData)) {
					foreach($studentData as $key => $value) {
						$$key == $value;
					}
								
					$user_last_name 						= no_magic_quotes($user_last_name);

					if ($student_first_class_choice == '') {
						$student_first_class_choice			= 'None';
						$student_first_class_choice_utc		= 'None';
					}
					if ($student_second_class_choice == '') {
						$student_second_class_choice		= 'None';
						$student_second_class_choice_utc	= 'None';
					}
					if ($student_third_class_choice == '') {
						$student_third_class_choice			= 'None';
						$student_third_class_choice_utc		= 'None';
					}


					if ($doDebug) {
						echo "<br />Processing $student_call_sign. Data read:<br />
							  &nbsp;&nbsp;&nbsp;&nbsp;First class choice: $student_first_class_choice<br />
							  &nbsp;&nbsp;&nbsp;&nbsp;Second class choice: $student_second_class_choice<br />
							  &nbsp;&nbsp;&nbsp;&nbsp;Third class choice: $student_third_class_choice<br />
							  &nbsp;&nbsp;&nbsp;&nbsp;First class choice UTC: $student_first_class_choice_utc<br />
							  &nbsp;&nbsp;&nbsp;&nbsp;Second class choice UTC: $student_second_class_choice_utc<br />
							  &nbsp;&nbsp;&nbsp;&nbsp;Third class choice UTC: $student_third_class_choice_utc<br />
							  &nbsp;&nbsp;&nbsp;&nbsp;Student email sent date: $user_email_sent_date<br />
							  &nbsp;&nbsp;&nbsp;&nbsp;Student Email Number; $student_email_number<br />";
					  
					}
					$utc1Times					= '';
					$utc2Times					= '';
					$utc3Times					= '';
					$utc1Days					= '';
					$utc2Days					= '';
					$utc3Days					= '';
					$noGo						= FALSE;
					if ($student_intervention_required == 'H') {
						if (student_hold_reason_code != 'N') {
							if ($doDebug) {
								echo "&nbsp;&nbsp;&nbsp;&nbsp;student is on hold<br />";
							}
						}
						$noGo					= TRUE;
					}
					if ($student_first_class_choice == '' || $student_first_class_choice == 'None') {
						if ($doDebug) {
							echo "&nbsp;&nbsp;&nbsp;&nbsp;student has no first class choice<br />";
						}
						$noGo				= TRUE;
					} else {
						$myInt				= strtotime($student_request_date);
						if ($student_class_priority == '') {
							$student_class_priority	= 0;
						}
						$student_class_priority	= 4 - intval($student_class_priority);
						$thisSequence		= $student_class_priority . $myInt;

						/// if UTC field is set, use it. Otherwise calculate it
						if ($student_first_class_choice_utc != '' || $student_first_class_choice != 'None') {
							if ($doDebug) {
								echo "Using $student_first_class_choice_utc of $student_first_class_choice_utc<br />";
							}
							$myArray			 	= explode(" ",$student_first_class_choice_utc);
							if (count($myArray) == 2) {
								$utc1Times				= $myArray[0];
								$utc1Days			 	= $myArray[1];
							} else {
								echo "<br /><b>ERROR</b> $student_call_sign has an invalid first_class_choice_utc of $student_first_class_choice_utc<br /><br />";
							}
						} else {				/// first choice utc is empty. is there a first choice local?
							if ($student_first_class_choice != '' && $student_first_class_choice != 'None') {
								if ($doDebug) {
									echo "&nbsp;&nbsp;&nbsp;&nbsp;converting first class choice of $student_first_class_choice to UTC<br />";
								}
								$myArray			= explode(" ",$student_first_class_choice);
								$thisTime			= $myArray[0];
								$thisDay			= $myArray[1];
								$result				= utcConvert('toutc',$student_timezone_offset,$thisTime,$thisDay);
								if ($result[0] == 'FAIL') {
									if ($doDebug) {
										echo "utcConvert failed 'toutc',$student_time_zone,$thisTime,$thisDay<br />
														  Error: $result[3]<br />";
									}
									$noGo			= TRUE;
								} else {
									$utc1Times			= $result[1];
									$utc1Days			= $result[2];
								}
							}
						}
						/// if UTC field is set, use it. Otherwise calculate it
						if ($student_second_class_choice_utc != '' && $student_second_class_choice_utc != 'None') {
							$myArray			 	= explode(" ",$student_second_class_choice_utc);
							$utc2Times				= $myArray[0];
							$utc2Days			 	= $myArray[1];
							if ($doDebug) {
								echo "Using $student_second_class_choice_utc of $utc2Times $utc2Days<br />";
							}
						} else {				/// second choice utc is empty. is there a second choice local?
							if ($student_second_class_choice != '' && $student_second_class_choice != 'None') {
								if ($doDebug) {
									echo "&nbsp;&nbsp;&nbsp;&nbsp;converting second class choice of $student_second_class_choice to UTC<br />";
								}
								$myArray			= explode(" ",$student_second_class_choice);
								$thisTime			= $myArray[0];
								$thisDay			= $myArray[1];
								$result				= utcConvert('toutc',$student_timezone_offset,$thisTime,$thisDay);
								if ($result[0] == 'FAIL') {
									if ($doDebug) {
										echo "utcConvert failed 'toutc',$student_time_zone,$thisTime,$thisDay<br />
														  Error: $result[3]<br />";
									}
									$utc2Times			= '';
									$utc2Days			= '';
								} else {
									$utc2Times			= $result[1];
									$utc2Days			= $result[2];
								}
							}
						}
						/// if UTC field is set, use it. Otherwise calculate it
						if ($student_third_class_choice_utc != '' && $student_third_class_choice_utc != 'None') {
							$myArray			 	= explode(" ",$student_third_class_choice_utc);
							$utc3Times				= $myArray[0];
							$utc3Days			 	= $myArray[1];
							if ($doDebug) {
								echo "Using $student_third_class_choice_utc of $utc1Times $utc1Days<br />";
							}
						} else {				/// third choice utc is empty. is there a third choice local?
							if ($student_third_class_choice != '' && $student_third_class_choice != 'None') {
								if ($doDebug) {
									echo "&nbsp;&nbsp;&nbsp;&nbsp;converting third class choice of $student_third_class_choice to UTC<br />";
								}
								$myArray			= explode(" ",$student_third_class_choice);
								$thisTime			= $myArray[0];
								$thisDay			= $myArray[1];
								$result				= utcConvert('toutc',$student_timezone_offset,$thisTime,$thisDay);
								if ($result[0] == 'FAIL') {
									if ($doDebug) {
										echo "utcConvert failed 'toutc',$student_time_zone,$thisTime,$thisDay<br />
														  Error: $result[3]<br />";
									}
									$utc3Times			= '';
									$utc3Days			= '';
								} else {
									$utc3Times			= $result[1];
									$utc3Days			= $result[2];
								}
							}
						}
						$myStr			= $student_excluded_advisor;
						if (!$noGo) {
							/// add to the array
							${'replaceArray' . $student_level}[] = "$thisSequence|$student_call_sign|$utc1Times|$utc1Days|$utc2Times|$utc2Days|$utc3Times|$utc3Days|$myStr|$student_class_language|$student_id";
							//										0             1                  2          3         4          5         6          7         8
							if ($doDebug) {
								echo "data added to replaceArray$student_level: $thisSequence|$student_call_sign|$utc1Times|$utc1Days|$utc2Times|$utc2Days|$utc3Times|$utc3Days|$myStr|$student_class_language|$student_id<br />";
							}
						} else {
							if ($doDebug) {
								echo "student not added to the replaceArray<br />";
							}
						}
					}
					sort($replaceArrayBeginner);
					sort($replaceArrayFundamental);
					sort($replaceArrayIntermediate);
					sort($replaceArrayAdvanced);
					/// dump the replace arrays if in debug mode
					if ($doDebug) {
						$myInt 		= count($replaceArrayBeginner);
						echo "<br />replace array beginner ($myInt students):<br /><pre>";
						print_r($replaceArrayBeginner);
						echo "</pre><br />";
						$myInt		= count($replaceArrayFundamental);
						echo "<br />replace array fundamental ($myInt students):<br /><pre>";
						print_r($replaceArrayFundamental);
						echo "</pre><br />";
						$myInt		= count($replaceArrayIntermediate);
						echo "<br />replace array intermediate ($myInt students):<br /><pre>";
						print_r($replaceArrayIntermediate);
						echo "</pre><br />";
						echo$myInt		= count($replaceArrayAdvanced);
						echo "<br />replace array advanced ($myInt students):<br /><pre>";
						print_r($replaceArrayAdvanced);
						echo "</pre><br />";
					}
				} else {
					$content	.= "<p>In the replacement period. Reading $studentTableName table to build 
									the replacement array. No records found.</p>";
					if ($doDebug) {
						echo "In the replacement period. Reading $studentTableName table to build 
							 the replacement array. No records found. Turning validReplacementPeriod off<br />";

						$validReplacementPeriod 	= 'N';
					}
				}
			}
			if ($doDebug) {
				echo "REPLACE Array: array built<br />";
			}
		}		///////// end of build the replacement array




//////// Begin process to handle outstanding replacement requests
		if ($validReplacementPeriod == 'Y' && $currentSemester == 'Not in Session') {
			if ($doDebug) {
				echo "<br /><b>OUTSTANDING</b> Outstanding Replacement Requests Process<br />";
			}
			$content	.= "<h4>Proccessing Outstanding Replacement Requests</h4>";
			// get the outstanding replacement requests and run any that 
			// haven't been fulfilled
			
			$sql		= "select * from $replacementRequests 
							where semester = '$theSemester' 
							and date_resolved = '' 
							order by date_created ";
			$wpw1_cwa_replacement_requests		= $wpdb->get_results($sql);
			if ($wpw1_cwa_replacement_requests === FALSE) {
				handleWPDBError($jobname,$doDebug);
			} else {
				$numBARows			= $wpdb->num_rows;
				if ($doDebug) {
					$myStr			= $wpdb->last_query;
					echo "ran $myStr<br />and found $numBARows rows<br />";
				}
				if ($numBARows > 0) {
					$outstandingRequests			= $numBARows;
					$content		.= "There are $outstandingRequests outstanding replacement requests<br />";
					foreach ($wpw1_cwa_replacement_requests as $replacement_requestsRow) {
						$replacement_id				= $replacement_requestsRow->record_id;
						$replacement_call_sign		= $replacement_requestsRow->call_sign;
						$replacement_class			= $replacement_requestsRow->class;
						$replacement_level			= $replacement_requestsRow->level;
						$replacement_semester		= $replacement_requestsRow->semester;
						$replacement_student		= $replacement_requestsRow->student;
						$replacement_date_resolved	= $replacement_requestsRow->date_resolved;
						$replacement_date_created	= $replacement_requestsRow->date_created;
						$replacement_date_updated	= $replacement_requestsRow->date_updated;
						
						if ($doDebug) {
							echo "<br />Processing replacement request for advisor: $replacement_call_sign<br />
									student: $replacement_student<br />
									class: $replacement_class<br />";
						}

						// get the advisor class schedule and language
						$criteria = [
							'relation' => 'AND',
							'clauses' => [
								['field' => 'advisorclass_call_sign', 'value' => $replacement_call_sign, 'compare' => '=' ],
								['field' => 'advisorclass_sequence', 'value' => $replacement_class, 'compare' => '=' ],
								['field' => 'advisorclass_semester', 'value' => $theSemester, 'compare' => '=' ]
							]
						];
						$advisorclassData = $advisorclass_dal->get_advisorclasses( $criteria, $operatingMode );
						if ($advisorclassData === FALSE) {
							if ($doDebug) {
								echo "get_advisorclasses for $replacement_call_sign returned FALSE<br />";
							}
						} else {
							if (! empty($advisorclassData)) {
								foreach($advisorclassData as $key => $value) {
									$$key = $value;
								}

								// now look for a potential replacement student
								$searchTime = intval($advisorclass_class_schedule_times_utc);
								$searchDays = $advisorclass_class_schedule_days_utc;
								if ($doDebug) {
									echo "&nbsp;&nbsp;&nbsp;&nbsp;Looking for a replacement level $replacement_level $searchTime $searchDays<br />";
								}
								$callSign1	= '';
								$sequence1	= '';
								$callSign2	= '';
								$sequence2	= '';
								$callSign3	= '';
								$sequence3	= '';

								foreach(${'replaceArray' . $replacement_level} as $myKey=>$myValue) {
									if ($doDebug) {
										echo "<br />Checking possible replacement: $myValue<br />";
									}
									$myArray = explode("|",$myValue);
									$thisSequence		= $myArray[0];
									$thisCallSign 		= $myArray[1];
									$firstTime 			= $myArray[2];
									$firstDays 			= $myArray[3];
									$secondTime 		= $myArray[4];
									$secondDays 		= $myArray[5];
									$thirdTime 			= $myArray[6];
									$thirdDays 			= $myArray[7];
									$excludedAdvisor	= $myArray[8];
									$thisLanguage		= $myArray[9];
									$thisStudentid		= $myArray[10];
									
									if ($doDebug) {
										echo "checking possible replacement $thisCallSign<br />";
									}
		
									if (! str_contains($excludedAdvisor,$advisorclass_call_sign)) {
										if ($thisLanguage == $advisorclass_language) {
											if ($searchDays == $firstDays) {
												if ($doDebug) {
													echo "first choice match on $searchDays. Checking poss replacement first choice $firstTime<br />";
												}
												if ($callSign1 == '') {		// don't continue if have a match
													$searchBegin = intval($firstTime) - 300;
													$searchEnd = intval($firstTime) + 300;
													if ($searchBegin < 0) {
														$searchBegin	= 0;
													}
													if ($searchEnd > 2400) {
														$searchEnd		= 2400;
													}
													if ($doDebug) {
														echo "testing firstTime: $firstTime. Looking for student between $searchBegin and $searchEnd<br />";
													}
													if ($searchTime >= $searchBegin && $searchTime < $searchEnd) {
														$callSign1 = $thisCallSign;
														$sequence1 = $myKey;
														if ($doDebug) {
															echo "1: Found $searchDays in $firstDays and $searchTime between $searchBegin and $searchEnd<br />
																			  Set callSign1 to $thisCallSign and sequence1 to $myKey<br />";
														}
													} else {
														if ($doDebug) {
															echo "1: No go $searchDays in $firstDays and $searchTime between $searchBegin and $searchEnd<br />";
														}
													}
												}
											} else {
												if ($doDebug) {
													echo "Searching first choice days did not match<br />";
												}
				
											}
											if ($searchDays == $secondDays) {
												if ($doDebug) {
													echo "second choice match on searchDays. Doing second choice $secondTime<br />";
												}
												if ($callSign2 == '') {
													$searchBegin 	= intval($secondTime) - 300;
													$searchEnd 		= intval($secondTime) + 300;
													if ($searchBegin < 0) {
														$searchBegin	= 0;
													}
													if ($searchEnd > 2400) {
														$searchEnd		= 2400;
													}
													if ($doDebug) {
														echo "testing secondTime: $secondTime. Looking for student between $searchBegin and $searchEnd<br />";
													}
													if ($searchTime >= $searchBegin && $searchTime < $searchEnd) {
														$callSign2 = $thisCallSign;
														$sequence2 = $myKey;
														if ($doDebug) {
															echo "2: Found $searchDays in $secondDays and $searchTime between $searchBegin and $searchEnd<br />
															Set callSign2 to $thisCallSign and sequence2 to $myKey<br />";
														}
													} else {
														if ($doDebug) {
															echo "2: No go $searchDays in $secondDays and $searchTime between $searchBegin and $searchEnd<br />";
														}
													}
												} else {
													if ($doDebug) {
														echo "searching second choice days did not match<br />";
													}
												}
											} else {
												if ($doDebug) {
													echo "searching second choice days didn't match<br />";
												}
											}
											if ($searchDays == $thirdDays) {
												if ($doDebug) {
													echo "third choice match on searchDays. Doing third choice $thirdTime<br />";
												}
												if ($callSign3 == '') {
													$searchBegin 	= intval($thirdTime) - 300;
													$searchEnd 		= intval($thirdTime) + 300;
													$searchEnd = $searchBegin + 300;
													if ($searchBegin < 0) {
														$searchBegin	= 0;
													}
													if ($searchEnd > 2400) {
														$searchEnd		= 2400;
													}
													if ($doDebug) {
														echo "testing thirdTime: $thirdTime. Looking for student between $searchBegin and $searchEnd<br />";
													}
													if ($searchTime >= $searchBegin && $searchTime < $searchEnd) {
														$callSign3 = $thisCallSign;
														$sequence3 = $myKey;
														if ($doDebug) {
															echo "3: Found $searchDays in $thirdDays and $searchTime between $searchBegin and $searchEnd<br />
																			  Set callSign3 to $thisCallSign and sequence3 to $myKey<br />";
														}
													} else {
														if ($doDebug) {
															echo "3: No go $searchDays in $thirdDays and $searchTime between $searchBegin and $searchEnd<br />";
														}
													}
												}	
											} else {
												if ($doDebug) {
													echo "searching third choice days did not match<br />";
												}
											}
										} else {
											if ($doDebug) {
												echo "$replacement_call_sign&apos;s language of $thisLanguage does not match advisorclass $advisorclass_language language<br />";
											}
										}
									} else {
										if ($doDebug) {
											echo "possible advisor is excluded: $excludedAdvisor<br />";
										}
									} 
								}
								if ($doDebug) {
									echo "CallSign1: $callSign1 | $sequence1<br />
										  CallSign2: $callSign2 | $sequence2<br />
										  CallSign3: $callSign3 | $sequence3<br />";
								}
								$gotAReplacement			= FALSE;
								if ($callSign1 != '') {
									$replacingCallSign		= $callSign1;
									$replacingSequence		= $sequence1;
									$gotAReplacement		= TRUE;
									if ($doDebug) {
										echo "$replacingCallSign (1) works as a replacement<br />";
									}
								} elseif ($callSign2 != '' && !$gotAReplacement) {
									$replacingCallSign		= $callSign2;
									$replacingSequence		= $sequence2;
									$gotAReplacement		= TRUE;
									if ($doDebug) {
										echo "$replacingCallSign (2) works as a replacement<br />";
									}
								} elseif ($callSign3 != '' && !$gotAReplacement) {
									$replacingCallSign		= $callSign3;
									$replacingSequence		= $sequence2;
									$gotAReplacement		= TRUE;
									if ($doDebug) {
										echo "$replacingCallSign (3) works as a replacement<br />";
									}
								}
	
								if ($gotAReplacement) {
									if ($doDebug) {
										echo "Have a replacement $replacingCallSign. Getting student record<br />";
									}
									///// get the replacement student record
									$replacementRecord = ${'replaceArray' . $replacement_level}[$replacingSequence];
									$replacementRecordID = $replacementRecord[10];
									$replacementStudentData = get_student_and_user_master('', 'id', $replacementRecordID, $operatingMode, $doDebug);
									if ($replacementStudentData === FALSE) {
										if ($doDebug) {
											echo "get_student_and_user_master for ID $replacementRecordID returned FALSE<br />";
										}
									} else {
										if (! empty($replacementStudentData)) {
											foreach($replacementStudentData as $key => $value) {
												$$key = $value;
											}
												
											if ($student_assigned_advisor == '') {
												//// add student to the advisor's class
												$inp_data			= array('inp_student'=>$student_call_sign, 
																			'inp_semester'=>$theSemester, 
																			'inp_assigned_advisor'=>$replacement_call_sign, 
																			'inp_assigned_advisor_class'=>$replacement_class, 
																			'inp_remove_status'=>'',
																			'inp_arbitrarily_assigned'=>'',
																			'inp_method'=>'add',
																			'jobname'=>$jobname,
																			'userName'=>$userName,
																			'testMode'=>$testMode,
																			'doDebug'=>$doDebug);
			
												$addResult			= add_remove_student($inp_data);
												if ($addResult[0] === FALSE) {
													$thisReason		= $addResult[1];
													if ($doDebug) {
														echo "attempting to add $replace_call_sign to $replacement_call_sign class failed:<br />$thisReason<br />";
													}
													sendErrorEmail("$jobname Attempting to add $replace_call_sign to $replacement_call_sign class failed:<br />$thisReason");
													$content		.= "Attempting to add $replace_call_sign to $replacement_call_sign class failed:<br />$thisReason<br />";
												} else {
													$content	.= "&nbsp;&nbsp;&nbsp;REPLACE student <a href='$studentUpdateURL?request_type=callsign&request_info=$replacement_call_sign&inp_depth=one&doDebug=$doDebug&testMode=$testMode&strpass=2' 
																	target='_blank'>$replacement_student</a> was replaced by <a href='$studentUpdateURL?request_type=callsign&request_info=$student_call_sign&inp_depth=one&inp_depth=one&doDebug=$doDebug&testMode=$testMode&strpass=2' 
																	target='_blank'>$student_call_sign</a> in $advisorclass_call_sign $advisorclass_sequence class.<br />";
													$studentReplaced	= TRUE;
													if ($doDebug) {
														echo "&nbsp;&nbsp;&nbsp; student $replacement_student was replaced by $student_call_sign<br />";
													}
													//// format replacement email to advisor
																			
													///// send the email to the advisor
													// get the advisor's user_master record
													$userData = $user_dal->get_user_master_by_callsign( $advisorclass_call_sign, $operatingMode );
													if ($userData === FALSE) {
														if ($doDebug) {
															echo "get_user_master_by_callsign for $advisorclass_call_sign returned FALSE<br />";
														}
													} else {
														if (! empty($userData)) {
															foreach($userData as $key => $value) {
																$$key == $value;
															}
															$theSubject			= "CW Academy Replacement Student Information for your Class";
															if ($testMode) {
																$theRecipient			= 'rolandksmith@gmail.com';
																$theSubject				= "TESTMODE $theSubject";
																$mailCode				= 2;
															} else {
																$theRecipient			= $user_email;
																$mailCode				= 14;
															}
																
															$thisContent			= "<p>To: $user_last_name, $user_first_name ($advisorclass_call_sign):</p>
																						<p>You have requested a replacement student for 
																						$replacement_student in your $replacement_level class number $advisorclass_sequence.</p>
																						<p>A replacement student has been added to your class. Please login to 
																						<a href='$siteURL/login'>CW Academy</a> and follow the directions there.
																						<p>If you have questions or concerns, do not reply to this email as the address is not monitored. 
																						Instead reach out to <a href='classResolutionURL' target='_blank'>CWA Class Resolution</a> and select the appropriate person.</p> 
																						<p>Thanks for your service as an advisor!<br />
																						CW Academy</p>";
															$mailResult				= emailFromCWA_v3(array('theRecipient'=>$theRecipient,
																											'theSubject'=>$theSubject,
																											'theContent'=>$thisContent,
																											'jobname'=>$jobname,
																											'mailCode'=>$mailCode,
																											'testMode'=>$testMode,
																											'doDebug'=>FALSE));
															// $mailResult = TRUE;
															if ($mailResult[0] === TRUE) {
																$content .= "&nbsp;&nbsp;&nbsp;&nbsp;An email was sent to <a href='$siteURL/cwa-search-sent-email-by-callsign-or-email/?inp_callsign=$advisorclass_advisor_call_sign&strpass=2' target='_blank'>$theRecipient</a>.<br />";
																if ($doDebug) {
																	echo $mailResult[1];
																	echo "Replacement email sent to the advisor at $theRecipient<br />";
																}
																$studentEmailCount--;
															} else {
																$content .= "&nbsp;&nbsp;&nbsp;&nbsp;The replacement email send function to advisor $advisorclass_call_sign email: $theRecipient failed.</p><br />";
																if ($doDebug) {
																	echo $mailResult[1];
																	echo "Replacement email FAILED to advisor at $theRecipient<br />";
																}
															}
															// add the reminder to the reminders table
															if ($doDebug) {
																echo "Adding reminder to reminders table<br />";
															}
															$myStr				= date('Y-m-d 00:00:00');
															$closeStr			= strtotime("+10 days");
															$close_date			= date('Y-m-d H:i:s',$closeStr);
															$token				= mt_rand();
															$reminder_text		= "<b>Replacement Student:</b> Your class makeup has been revised and students need to be contacted 
and confirmed. Click on <a href='$advisorVerifyURL/?&token=$token' target='_blank'>Manage Advisor Class Assignments</a> to complete that task.";
															$inputParams		= array("effective_date|$myStr|s",
																						"close_date|$close_date|s",
																						"resolved_date||s",
																						"send_reminder|N|s",
																						"send_once|Y|s",
																						"call_sign|$advisorclass_call_sign|s",
																						"role||s",
																						"email_text||s",
																						"reminder_text|$reminder_text|s",
																						"resolved|N|s",
																						"token|$token|s");
															$insertResult		= add_reminder($inputParams,$testMode,$doDebug);
															if ($insertResult[0] === FALSE) {
																if ($doDebug) {
																	echo "inserting reminder failed: $insertResult[1]<br />";
																}
																$content		.= "Inserting reminder failed: $insertResult[1]<br />";
															} else {
																/// mark the replacement request as fulfilled
																$myStr 		= date("Y-m-d H:i:s");
																$replResult		= $wpdb->update($replacementRequests,
																								array('date_resolved'=>$myStr), 
																								array('record_id'=>$replacementRecordID), 
																								array('%s'), 
																								array('%d'));
																if ($replResult === FALSE) {
																	$myError			= $wpdb->last_error;
																	$myQuery			= $wpdb->last_query;
																	if ($doDebug) {
																		echo "updating $replacementRequests table failed<br />
																			  wpdb->last_query: $myQuery<br />
																			  wpdb->last_error: $myError<br />";
																	}
																	$errorMsg			= "$jobname updating $replacementRequests failed.\nSQL: $myQuery\nError: $myError";
																	sendErrorEmail($errorMsg);
																	$content		.= "Unable to update $replacementRequests<br />";
																} else {
																	$outstandingFulfilled++;
																						
																	$replacedCount++;
																	if ($doDebug) {
																		$debugLog	.= "replacement request marked as filled<br />";
																	}
																}
															}
		
															///// unset the replacement student in replaceArray
															if (isset(${'replaceArray' . $replacement_level}[$replacingSequence])) {
																unset(${'replaceArray' . $replacement_level}[$replacingSequence]);
																if ($doDebug) {
																	echo "did unset replaceArray $replacement_level $replacingSequence<br />";
																}
															} else {
																if ($doDebug) {
																	echo "<b>ERROR</b> replaceArray $replacement_level $replacingSequence not available to unset<br />";
																}
															}
														} else {
															$content .= "No user_master record found for $advisorclass_call_sign<br />";
															if ($doDebug) {
																echo "No user_master record found for $advisorclass_call_sign<br />";
															}
														}
													}
												}
											} else {
												if ($doDebug) {
													echo "student already assigned to $student_assigned_advisor<br />";
												}
												sendErrorEmail("$jobname doing replacements student $student_call_sign was in the available replacement students but had $student_assigned_advisor assigned");
											}
										} else {
											if ($doDebug) {
												echo "Unable to retrieve the replacement student info ($replaceCallSign) from $studentTableName table<br />";
											}
											$content			.= "Unable to retrieve the replacement student info ($replaceCallSign) from $studentTableName table<br />";
										}
									}
								} else {			/// no replacement found
									if ($doDebug) {
										echo "No replacement found<br />";
									}
									$outstandingNotFulfilled++;
									$notReplacedCount++;
									$content	.= "No replacement found for $replacement_student in $replacement_call_sign $replacement_level class $replacement_class<br />";
								}
							} else {
								$content			.= "Attempting to do a replacement for $replacement_call_sign sequence $replacement_class. 
														advisorclass returned no records<br />";
							}
						}
					}
				} else {
					$content .= "No outstanding replacement requests<br />";
				}
			}
			if ($doDebug) {
				echo "OUTSTANDING: end of outstanding replacement process<br />";
			}
		}
		
///////// end of outstanding replacement requests process
		








// Beginning of the handle replacement students process

/*	The Replacement Process

Student status of R (replace the student) or V (replace the student and return to 
	unassigned pool) triggers the replacement process
Do remove student
	if the student status is R, set remove status to C
	If the student status is V, set the remove status to blank
	
Find a possible replacement student
	The student level, the assigned advisor, and the assigned advisor class define the
		class. Get the class record and the class time and days in UTC
			Cycle through the replaceArray records using the level
				if the class day matches first class choice days and the class 
					times is within the students first class times, there is a match
				if no match, check the 2nd class choice
				if still no match, check the 3rd class choice
				if still no match, go on to the next replaceArray record
	If there is a match
		Get the replacement student record
			assign the student to the advisor class
			delete the replaceArray record so the student does not get double assigned
			send the email to the advisor
	If there is no match
		send the no replacement available to the advisor
		add the replacement request to the replacementRequsts table
	
*/

								if ($doDebug) {
									echo "<br />Replacement Student Process<br />";
								}
								if ($validReplacementPeriod == 'Y' && $currentSemester == 'Not in Session') {
									if ($student_status == 'R' || $student_status == 'V') {
										// make sure the student is assigned and has a class number
										if ($student_assigned_advisor == '' || $student_assigned_advisor_class == 0) {
											if ($doDebug) {
												echo "student assigned advisor is empty or assigned_advisor_class is 0<br />";
											}
										} else {
									
											// save off the assigned advisor and class so we can remove the student
											$str_assigned_advisor		= $student_assigned_advisor;
											$str_assigned_advisor_class	= $student_assigned_advisor_class;
											
											$content					.= "REPLACE Student <a href='$studentUpdateURL?request_type=callsign&request_info=$student_call_sign&inp_depth=one&inp_depth=one&doDebug=$doDebug&testMode=$testMode&strpass=2' target='_blank'>$student_call_sign</a> at level $student_level in advisor $str_assigned_advisor class $str_assigned_advisor_class<br />";
											if ($doDebug) {
												echo "REPLACE Student $student_call_sign at level $student_level in advisor $str_assigned_advisor class $str_assigned_advisor_class<br />";
											}
											// Now remove the student from the advisor's class
											if ($student_status == 'V') {
												$removeStatus	= '';
												$updateData[]		= 'student_intervention_required|H|s';
												$doUpdateStudent	= TRUE;
	
												// add the reminder
												if ($doDebug) {
													echo "adding reminder to reminders table<br />";
												}
												$effective_date		= date('Y-m-d H:i:s');
												$closeStr			= strtotime("+10 days");
												$close_date			= date('Y-m-d H:i:s', $closeStr);
												$token				= mt_rand();
												$reminder_text		= "<b>Signup on Hold:</b> Student <a href='$studentUpdateURL?request_type=callsign&request_info=$student_call_sign&inp_depth=one&inp_depth=one&doDebug=$doDebug&testMode=$testMode&strpass=2' target='_blank'>$student_call_sign</a> 
had a student status of V and is on hold waiting for a possible reassignment. When the issue is resolved, click 'Remove Item'.";
												$inputParams		= array("effective_date|$effective_date|s",
																			"close_date|$close_date|s",
																			"resolved_date||s",
																			"send_reminder||s",
																			"send_once||s",
																			"call_sign||s",
																			"role|administrator|s",
																			"email_text||s",
																			"reminder_text|$reminder_text|s",
																			"resolved|N|s",
																			"token|$token|s");
												$insertResult		= add_reminder($inputParams,$testMode,$doDebug);
												if ($insertResult[0] === FALSE) {
													if ($doDebug) {
														echo "inserting reminder failed: $insertResult[1]<br />";
													}
													$content		.= "Inserting reminder failed: $insertResult[1]<br />";
												} else {
													if ($doDebug) {
														$debugLog	.= "On hold reminder set<br />";
													}
													// put the student on hold so the student does not get processed again
													$updateData[] 	= 'student_intervention_required|H|s';
													$updateData[]	= 'student_status||s';
													$update_action_log	.= "placed student on hold ";
												}
	
											} else {
												$removeStatus	= 'C';
											}
											$inp_data			= array('inp_student'=>$student_call_sign,
																		'inp_semester'=>$student_semester,
																		'inp_assigned_advisor'=>$student_assigned_advisor,
																		'inp_assigned_advisor_class'=>$student_assigned_advisor_class,
																		'inp_remove_status'=>$removeStatus,
																		'inp_arbitrarily_assigned'=>'',
																		'inp_method'=>'remove',
																		'jobname'=>$jobname,
																		'userName'=>$userName,
																		'testMode'=>$testMode,
																		'doDebug'=>$doDebug);
					
											$removeResult		= add_remove_student($inp_data);
											if ($removeResult[0] === FALSE) {
												$thisReason		= $removeResult[1];
												if ($doDebug) {
													echo "attempting to remove $student_call_sign from $advisorclass_advisor_call_sign class failed:<br />$thisReason<br />";
												}
												sendErrorEmail("$jobname Attempting to remove $student_call_sign from $advisorclass_advisor_call_sign class failed:<br />$thisReason");
												$content		.= "Attempting to remove $student_call_sign from $advisorclass_advisor_call_sign class failed:<br />$thisReason<br />";
											} else {
												if ($doDebug) {
													$debugLog	.= "Student removed from advisor class<br />";
												}
											}
											
											/// figure out if there is a replacement student
											//// get the advisor and advisorClass records
					
											$sql			= "select * from $advisorClassTableName  
																left join $userMasterTableName on user_call_sign = advisorclass_call_sign 
																where advisorclass_call_sign = '$str_assigned_advisor' 
																and advisorclass_sequence = $str_assigned_advisor_class 
																and advisorclass_semester = '$theSemester'";
					
											$wpw1_cwa_advisorclass				= $wpdb->get_results($sql);
											if ($wpw1_cwa_advisorclass === FALSE) {
												handleWPDBError($jobname,$doDebug);
											} else {
												$numACRows						= $wpdb->num_rows;
												if ($doDebug) {
													$myStr						= $wpdb->last_query;
													echo "ran $myStr<br />and found $numACRows rows<br />";
												}
												if ($numACRows > 0) {
													foreach ($wpw1_cwa_advisorclass as $advisorClassRow) {
														$advisorclass_ID				 		= $advisorClassRow->advisorclass_id;
														$advisorclass_advisor_call_sign 		= $advisorClassRow->advisorclass_call_sign;
														$advisorclass_user_first_name 		= $advisorClassRow->user_first_name;
														$advisorclass_user_last_name 		= $advisorClassRow->user_last_name;
														$advisorEmail							= $advisorClassRow->user_email;
														$advisorclass_sequence					= $advisorClassRow->advisorclass_sequence;
														$advisorclass_level						= $advisorClassRow->advisorclass_level;
														$advisorclass_language					= $advisorClassRow->advisorclass_language;
														$advisorclass_action_log				= stripslashes($advisorClassRow->advisorclass_action_log);
														$advisorclass_class_schedule_days 		= $advisorClassRow->advisorclass_class_schedule_days;
														$advisorclass_class_schedule_times 		= $advisorClassRow->advisorclass_class_schedule_times;
														$advisorclass_class_schedule_days_utc 	= $advisorClassRow->advisorclass_class_schedule_days_utc;
														$advisorclass_class_schedule_times_utc 	= $advisorClassRow->advisorclass_class_schedule_times_utc;
	
														///// find a replacement student
														$searchTime = intval($advisorclass_class_schedule_times_utc);
														$searchDays = $advisorclass_class_schedule_days_utc;
														if ($doDebug) {
															echo "&nbsp;&nbsp;&nbsp;&nbsp;Looking for a replacement level $student_level $searchTime $searchDays<br />";
														}
														$callSign1	= '';
														$sequence1	= '';
														$callSign2	= '';
														$sequence2	= '';
														$callSign3	= '';
														$sequence3	= '';
	
														foreach(${'replaceArray' . $advisorclass_level} as $myKey=>$myValue) {
															if ($doDebug) {
																echo "<br />Checking possible replacement: $myKey - $myValue<br />";
															}
															$myArray = explode("|",$myValue);
															$student_sequence		= $myArray[0];
															$thisCallSign 		= $myArray[1];
															$firstTime 			= $myArray[2];
															$firstDays 			= $myArray[3];
															$secondTime 		= $myArray[4];
															$secondDays 		= $myArray[5];
															$thirdTime 			= $myArray[6];
															$thirdDays 			= $myArray[7];
															$excludedAdvisor	= $myArray[8];
								
															$myInt				= strpos($excludedAdvisor,$advisorclass_advisor_call_sign);
															if ($myInt === FALSE) {
																if ($searchDays == $firstDays) {
																	if ($doDebug) {
																		echo "first choice match on $searchDays. Checking poss replacement first choice $firstTime<br />";
																	}
																	if ($callSign1 == '') {		// don't continue if have a match
																		$searchBegin = intval($firstTime) - 300;
																		$searchEnd = intval($firstTime) + 300;
																		if ($searchBegin < 0) {
																			$searchBegin	= 0;
																		}
																		if ($searchEnd > 2400) {
																			$searchEnd		= 2400;
																		}
																		if ($doDebug) {
																			echo "testing firstTime: $firstTime. Looking for student between $searchBegin and $searchEnd<br />";
																		}
																		if ($searchTime >= $searchBegin && $searchTime < $searchEnd) {
																			$callSign1 = $thisCallSign;
																			$sequence1 = $myKey;
																			if ($doDebug) {
																				echo "1: Found $searchDays in $firstDays and $searchTime between $searchBegin and $searchEnd<br />
																								  Set callSign1 to $thisCallSign and sequence1 to $myKey<br />";
																			}
																		} else {
																			if ($doDebug) {
																				echo "1: No go $searchDays in $firstDays and $searchTime between $searchBegin and $searchEnd<br />";
																			}
																		}
																	}
																} else {
																	if ($doDebug) {
																		echo "Searching first choice days did not match<br />";
																	}
									
																}
																if ($searchDays == $secondDays) {
																	if ($doDebug) {
																		echo "second choice match on searchDays. Doing second choice $secondTime<br />";
																	}
																	if ($callSign2 == '') {
																		$searchBegin 	= intval($secondTime) - 300;
																		$searchEnd 		= intval($secondTime) + 300;
																		if ($searchBegin < 0) {
																			$searchBegin	= 0;
																		}
																		if ($searchEnd > 2400) {
																			$searchEnd		= 2400;
																		}
																		if ($doDebug) {
																			echo "testing secondTime: $secondTime. Looking for student between $searchBegin and $searchEnd<br />";
																		}
																		if ($searchTime >= $searchBegin && $searchTime < $searchEnd) {
																			$callSign2 = $thisCallSign;
																			$sequence2 = $myKey;
																			if ($doDebug) {
																				echo "2: Found $searchDays in $secondDays and $searchTime between $searchBegin and $searchEnd<br />
																				Set callSign2 to $thisCallSign and sequence2 to $myKey<br />";
																			}
																		} else {
																			if ($doDebug) {
																				echo "2: No go $searchDays in $secondDays and $searchTime between $searchBegin and $searchEnd<br />";
																			}
																		}
																	} else {
																		if ($doDebug) {
																			echo "searching second choice days did not match<br />";
																		}
																	}
																} else {
																	if ($doDebug) {
																		echo "searching second choice days didn't match<br />";
																	}
																}
																if ($searchDays == $thirdDays) {
																	if ($doDebug) {
																		echo "third choice match on searchDays. Doing third choice $thirdTime<br />";
																	}
																	if ($callSign3 == '') {
																		$searchBegin 	= intval($thirdTime) - 300;
																		$searchEnd 		= intval($thirdTime) + 300;
																		$searchEnd = $searchBegin + 300;
																		if ($searchBegin < 0) {
																			$searchBegin	= 0;
																		}
																		if ($searchEnd > 2400) {
																			$searchEnd		= 2400;
																		}
																		if ($doDebug) {
																			echo "testing thirdTime: $thirdTime. Looking for student between $searchBegin and $searchEnd<br />";
																		}
																		if ($searchTime >= $searchBegin && $searchTime < $searchEnd) {
																			$callSign3 = $thisCallSign;
																			$sequence3 = $myKey;
																			if ($doDebug) {
																				echo "3: Found $searchDays in $thirdDays and $searchTime between $searchBegin and $searchEnd<br />
																								  Set callSign3 to $thisCallSign and sequence3 to $myKey<br />";
																			}
																		} else {
																			if ($doDebug) {
																				echo "3: No go $searchDays in $thirdDays and $searchTime between $searchBegin and $searchEnd<br />";
																			}
																		}
																	}	
																} else {
																	if ($doDebug) {
																		echo "searching third choice days did not match<br />";
																	}
																}
															} else {
																if ($doDebug) {
																	echo "possible advisor $advisor_call_sign is excluded: $excludedAdvisor<br />";
																}
															} 
														}
														if ($doDebug) {
															echo "CallSign1: $callSign1 | $sequence1<br />
																  CallSign2: $callSign2 | $sequence2<br />
																  CallSign3: $callSign3 | $sequence3<br />";
														}
	
														$gotAReplacement			= FALSE;
														if ($callSign1 != '') {
															$replacingCallSign		= $callSign1;
															$replacingSequence		= $sequence1;
															$gotAReplacement		= TRUE;
														} elseif ($callSign2 != '') {
															$replacingCallSign		= $callSign2;
															$replacingSequence		= $sequence2;
															$gotAReplacement		= TRUE;
														} elseif ($callSign3 != '') {
															$replacingCallSign		= $callSign3;
															$replacingSequence		= $sequence3;
															$gotAReplacement		= TRUE;
														}
							
														if ($gotAReplacement) {
															if ($doDebug) {
																echo "Have a replacement $replacingCallSign. Getting student record<br />";
															}
															///// get the replacement student record
															$sql						= "select * from $studentTableName 
																							left join $userMasterTableName on user_call_sign = student_call_sign 
																							where student_semester='$theSemester' 
																							and student_call_sign='$replacingCallSign'";
															$wpw1_cwa_replace				= $wpdb->get_results($sql);
															if ($wpw1_cwa_replace === FALSE) {
																handleWPDBError($jobname,$doDebug);
															} else {
																$numRRows				= $wpdb->num_rows;
																if ($doDebug) {
																	echo "ran $sql<br />and retrieved $numRRows rows from $studentTableName table<br />";
																}
																if ($numRRows > 0) {
																	foreach ($wpw1_cwa_replace as $replaceRow) {
																		$replace_master_ID 					= $replaceRow->user_ID;
																		$replace_master_call_sign 			= $replaceRow->user_call_sign;
																		$replace_first_name 				= $replaceRow->user_first_name;
																		$replace_last_name 					= $replaceRow->user_last_name;
																		$replace_email 						= $replaceRow->user_email;
																		$replace_ph_code					= $replaceRow->user_ph_code;
																		$replace_phone 						= $replaceRow->user_phone;
																		$replace_city 						= $replaceRow->user_city;
																		$replace_state 						= $replaceRow->user_state;
																		$replace_zip_code 					= $replaceRow->user_zip_code;
																		$replace_country_code 				= $replaceRow->user_country_code;
																		$replace_country	 				= $replaceRow->user_country;
																		$replace_whatsapp 					= $replaceRow->user_whatsapp;
																		$replace_telegram 					= $replaceRow->user_telegram;
																		$replace_signal 					= $replaceRow->user_signal;
																		$replace_messenger 					= $replaceRow->user_messenger;
																		$replace_master_action_log 			= stripslashes($replaceRow->user_action_log);
																		$replace_timezone_id 				= $replaceRow->user_timezone_id;
																		$replace_languages 					= $replaceRow->user_languages;
																		$replace_survey_score 				= $replaceRow->user_survey_score;
																		$replace_is_admin					= $replaceRow->user_is_admin;
																		$replace_role 						= $replaceRow->user_role;
																		$replace_master_date_created 		= $replaceRow->user_date_created;
																		$replace_master_date_updated 		= $replaceRow->user_date_updated;
					
																		$replace_ID								= $replaceRow->student_id;
																		$replace_call_sign						= $replaceRow->student_call_sign;
																		$replace_time_zone  					= $replaceRow->student_time_zone;
																		$replace_timezone_offset				= $replaceRow->student_timezone_offset;
																		$replace_youth  						= $replaceRow->student_youth;
																		$replace_age  							= $replaceRow->student_age;
																		$replace_replace_parent 				= $replaceRow->student_parent;
																		$replace_replace_parent_email  			= strtolower($replaceRow->student_parent_email);
																		$replace_level  						= $replaceRow->student_level;
																		$replace_class_language					= $replaceRow->student_class_language;
																		$replace_waiting_list 					= $replaceRow->student_waiting_list;
																		$replace_request_date  					= $replaceRow->student_request_date;
																		$replace_semester						= $replaceRow->student_semester;
																		$replace_notes  						= $replaceRow->student_notes;
																		$replace_welcome_date  					= $replaceRow->student_welcome_date;
																		$replace_email_sent_date  				= $replaceRow->user_email_sent_date;
																		$replace_email_number  					= $replaceRow->user_email_number;
																		$replace_response  						= strtoupper($replaceRow->student_response);
																		$replace_response_date  				= $replaceRow->student_response_date;
																		$replace_abandoned  					= $replaceRow->student_abandoned;
																		$replace_replace_status  				= strtoupper($replaceRow->student_status);
																		$replace_action_log  					= stripslashes($replaceRow->student_action_log);
																		$replace_pre_assigned_advisor  			= $replaceRow->student_pre_assigned_advisor;
																		$replace_selected_date  				= $replaceRow->student_selected_date;
																		$replace_no_catalog  					= $replaceRow->student_no_catalog;
																		$replace_hold_override  				= $replaceRow->student_hold_override;
																		$replace_assigned_advisor  				= $replaceRow->student_assigned_advisor;
																		$replace_advisor_select_date  			= $replaceRow->student_advisor_select_date;
																		$replace_advisor_class_timezone 		= $replaceRow->student_advisor_class_timezone;
																		$replace_hold_reason_code  				= $replaceRow->student_hold_reason_code;
																		$replace_class_priority  				= $replaceRow->student_class_priority;
																		$replace_assigned_advisor_class 		= $replaceRow->student_assigned_advisor_class;
																		$replace_promotable  					= $replaceRow->student_promotable;
																		$replace_excluded_advisor  				= $replaceRow->student_excluded_advisor;
																		$replace_replace_survey_completion_date	= $replaceRow->student_survey_completion_date;
																		$replace_available_class_days  			= $replaceRow->student_available_class_days;
																		$replace_intervention_required  		= $replaceRow->student_intervention_required;
																		$replace_copy_control  					= $replaceRow->student_copy_control;
																		$replace_first_class_choice  			= $replaceRow->student_first_class_choice;
																		$replace_second_class_choice  			= $replaceRow->student_second_class_choice;
																		$replace_third_class_choice  			= $replaceRow->student_third_class_choice;
																		$replace_first_class_choice_utc  		= $replaceRow->student_first_class_choice_utc;
																		$replace_second_class_choice_utc  		= $replaceRow->student_second_class_choice_utc;
																		$replace_third_class_choice_utc  		= $replaceRow->student_third_class_choice_utc;
																		$replace_catalog_options				= $replaceRow->student_catalog_options;
																		$replace_flexible						= $replaceRow->student_flexible;
																		$replace_date_created 					= $replaceRow->student_date_created;
																		$replace_date_updated			  		= $replaceRow->student_date_updated;
	
																		// make sure the student really unassigned
																		if ($replace_assigned_advisor == '') {
																			//// add student to the advisor's class
																			$inp_data			= array('inp_student'=>$replace_call_sign,
																										'inp_semester'=>$replace_semester,
																										'inp_assigned_advisor'=>$advisorclass_advisor_call_sign,
																										'inp_assigned_advisor_class'=>$advisorclass_sequence ,
																										'inp_remove_status'=>'', 
																										'inp_arbitrarily_assigned'=>'',
																										'inp_method'=>'add',
																										'jobname'=>$jobname,
																										'userName'=>$userName,
																										'testMode'=>$testMode,
																										'doDebug'=>$doDebug);
																			if ($doDebug) {
																				echo "Attempting to add $replace_call_sign to $advisorclass_advisor_call_sign<br /><pre>";
																				print_r($inp_data);
																				echo "</pre><br />";
																			}
										
																			$addResult			= add_remove_student($inp_data);
																			if ($addResult[0] === FALSE) {
																				$thisReason		= $addResult[1];
																				if ($doDebug) {
																					echo "attempting to add $student_call_sign to $advisorclass_advisor_call_sign class failed:<br />$thisReason<br />";
																				}
																				sendErrorEmail("$jobname Attempting to add $student_call_sign to $advisorclass_advisor_call_sign class failed:<br />$thisReason");
																				$content		.= "Attempting to add $student_call_sign to $advisorclass_advisor_call_sign class failed:<br />$thisReason<br />";
																			} else {
																				$content	.= "&nbsp;&nbsp;&nbsp;REPLACE student <a href='$studentUpdateURL?request_type=callsign&request_info=$student_call_sign&inp_depth=one&inp_depth=one&doDebug=$doDebug&testMode=$testMode&strpass=2' target='_blank'>$student_call_sign</a> was replaced by <a href='$studentUpdateURL?request_type=callsign&request_info=$replace_call_sign&inp_depth=one&inp_depth=one&doDebug=$doDebug&testMode=$testMode&strpass=2' target='_blank'>$replace_call_sign</a> in $str_assigned_advisor $str_assigned_advisor_class class.<br />";
																				$studentReplaced	= TRUE;
																				if ($doDebug) {
																					echo "student $student_call_sign was replaced by $replace_call_sign<br />";
																				}
																				///// unset the replacement student in replaceArray
																				if (isset(${'replaceArray' . $advisorclass_level}[$replacingSequence])) {
																					unset(${'replaceArray' . $advisorclass_level}[$replacingSequence]);
																					if ($doDebug) {
																						echo "did unset replaceArray$advisorclass_level $replacingSequence<br />";
																					}
																				}
																				//// format replacement email to advisor
																				$email_content			= "<p>You requested a replacement student. A replacement has been found and 
																											added to your class. Please go to <a href='$siteURL/program-list/'>CW Academy</a> 
																											and follow the instructions there.";
																				if ($doDebug) {
																					echo "adding reminder to reminders table<br />";
																				}
																				$closeStr				= strtotime("+5 days");
																				$close_date				= date('Y-m-d H:i:s',$closeStr);
																				$token					= mt_rand();
																				$effective_date			= date('Y-m-d H:i:s');
																				$reminder_text			= "<b>Manage Students:</b> To see your current class makeup and and verify the student(s) click on 
<a href='$siteURL/cwa-manage-advisor-class?strpass=2&callsign=$advisorclass_advisor_call_sign&token=$token'>Manage Advisor Class</a> to complete that task.";
																				$inputParams		= array("effective_date|$effective_date|s",
																											"close_date|$close_date|s",
																											"resolved_date||s",
																											"send_reminder|n|s",
																											"send_once|Y|s",
																											"call_sign|$str_assigned_advisor|s",
																											"role||s",
																											"email_text||s",
																											"reminder_text|$reminder_text|s",
																											"resolved|N|s",
																											"token|$token|s");
																				$insertResult		= add_reminder($inputParams,$testMode,$doDebug);
																				if ($insertResult[0] === FALSE) {
																					if ($doDebug) {
																						echo "inserting reminder failed: $insertResult[1]<br />";
																					}
																					$content		.= "Inserting reminder failed: $insertResult[1]<br />";
																				} else {
																					$replacedCount++;
																				}
																			}
																		} else {
																			if ($doDebug) {
																				echo "$replace_call_sign is assigned to $replace_assigned_advisor class $replace_assigned_advisor_class<br />";
																			}
																		}
																	}
																} else {
																	if ($doDebug) {
																		echo "Unable to retrieve the replacement student info ($replaceCallSign) from $studentTableName table (no record found)<br />";
																	}
																	$content			.= "Unable to retrieve the replacement student info ($replaceCallSign) from $studentTableName table (no record found)<br />";
																}
															}	
														} else {			/// no replacement found
															if ($doDebug) {
																echo "No replacement found<br />";
															}
															//// format email to the advisor
															$email_content			= "<p>Unfortunately, no students are available for 
assignment that meet the criteria for your class.<p>";
															$notReplacedCount++;
															// update advisorClass record with the replacement information
															if ($doDebug) {
																echo "Updating advisorClass record with the relplacement information<br />";
															}
															$advisorclass_action_log	= "$advisorclass_action_log / $actionDate CRON Advisor requested replacement for $student_call_sign. No replacement found ";
															$classUpdateData		= array('tableName'=>$advisorClassTableName,
																							'inp_method'=>'update',
																							'inp_data'=>array('advisorclass_action_log'=>$advisorclass_action_log),
																							'inp_format'=>array('%s'),
																							'jobname'=>$jobname,
																							'inp_id'=>$advisorclass_ID,
																							'inp_callsign'=>$str_assigned_advisor,
																							'inp_semester'=>$theSemester,
																							'inp_sequence'=>$advisorclass_sequence,
																							'inp_who'=>$userName,
																							'testMode'=>$testMode,
																							'doDebug'=>$doDebug);
															$updateResult	= updateClass($classUpdateData);
															if ($updateResult[0] === FALSE) {
																$myError	= $wpdb->last_error;
																$mySql		= $wpdb->last_query;
																$errorMsg	= "$jobname Processing $str_assigned_advisor in $advisorClassTableName failed. Reason: $updateResult[1]<br />SQL: $mySql<br />Error: $myError<br />";
																if ($doDebug) {
																	echo $errorMsg;
																}
																sendErrorEmail($errorMsg);
																$content		.= "Unable to update content in $advisorClassTableName<br />";
															} else {		// if update is successful, add the record to replacementRequests table
																$replParams 	= array('call_sign'=>$advisorclass_advisor_call_sign,
																						'class'=>$advisorclass_sequence,
																						'level'=>$advisorclass_level,
																						'semester'=>$student_semester,
																						'student'=>$student_call_sign);
	
																$replFormat 	= array('%s','%d','%s','%s','%s');
										
																$insertResult	= $wpdb->insert($replacementRequests,
																								$replParams,
																								$replFormat);
																if ($insertResult === FALSE) {
																	$myError			= $wpdb->last_error;
																	$myQuery			= $wpdb->last_query;
																	if ($doDebug) {
																		echo "Inserting into $replacementRequests table failed<br />
																			  wpdb->last_query: $myQuery<br />
																			  wpdb->last_error: $myError<br />";
																	}
																	$errorMsg			= "$jobname inserting $replacementRequests failed while attempting to move to past_student. <p>SQL: $myQuery</p><p> Error: $myError</p>";
																	sendErrorEmail($errorMsg);
																	$content		.= "Unable to insert into $replacementRequests<br />";
																} else {
																	$newID			= $wpdb->insert_id;
																	if ($doDebug) {
																		$myStr			= $wpdb->last_query;
																		echo "ran $myStr<br />and inserted $newID into $replacementRequests<br />";
																	}
																	$outstandingRequests++;
																}
	
															}
														}		
														///// send the email to the advisor
														$theSubject			= "CW Academy Replacement Student Information for your Class";
														if ($testMode) {
															$theRecipient			= 'rolandksmith@gmail.com';
															$theSubject				= "TESTMODE $theSubject";
															$mailCode				= 2;
														} else {
															$theRecipient			= $advisorEmail;
															$mailCode				= 14;
														}
														$strSemester				= $currentSemester;
														if ($currentSemester == 'Not in Session') {
															$strSemester 			= $nextSemester;
														}
														$thisContent			= "<p>To: $advisorclass_user_last_name, $advisorclass_user_first_name ($advisorclass_advisor_call_sign):</p>
																					<p>You have requested a replacement student for $user_last_name, $user_first_name 
																					($student_call_sign) in your $student_level class number $advisorclass_sequence.</p>
																					$email_content
																					<p>If you have questions or concerns, do not reply to this email as the address is not monitored. 
																					Instead reach out to <a href='classResolutionURL' target='_blank'>CWA Class Resolution</a> and select the appropriate person.</p> 
																					<p>Thanks for your service as an advisor!<br />
																					CW Academy</p>";
														$mailResult				= emailFromCWA_v3(array('theRecipient'=>$theRecipient,
																										'theSubject'=>$theSubject,
																										'theContent'=>$thisContent,
																										'jobname'=>$jobname,
																										'mailCode'=>$mailCode,
																										'testMode'=>$testMode,
																										'doDebug'=>FALSE));
														// $mailResult = TRUE;
														if ($mailResult[0] === TRUE) {
															$content .= "&nbsp;&nbsp;&nbsp;&nbsp;An email was sent to <a href='$siteURL/cwa-search-sent-email-by-callsign-or-email/?inp_email=$theRecipient&strpass=2' 
																 target='_blank'>$theRecipient</a>.<br />";
															if ($doDebug) {
																echo $mailResult[1];
																echo "Replacement email sent to the advisor at $theRecipient<br />";
															}
															$studentEmailCount--;
														} else {
															$content .= "&nbsp;&nbsp;&nbsp;&nbsp;The replacement email send function to advisor $advisor_call_sign; email: $theRecipient failed.<br />";
															if ($doDebug) {
																echo $mailResult[1];
																echo "Replacement email FAILED to advisor at $theRecipient<br />";
															}
														}
													}
												} else {		//// no records found in advisorClass tabke
													if ($doDebug) {
														echo "No matching advisorClass record<br />";
													}
													$content	.= "<p>Student $student_call_sign replacement was requested by 
																	advisor $str_assigned_advisor in the advisor's $str_assigned_advisor_class class. No 
																	$advisorClassTableName pod record found for that class. No replacement made.</p>";
												}
											}
										}
									}
								} else {
									if ($doDebug) {
										echo "End of replacement process<br />";
									}
								}			///// end of replacement process
