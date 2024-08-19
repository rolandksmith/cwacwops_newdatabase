function add_remove_student($inp_data = array()) {

/*

	Add Student example:
			$inp_data			= array('inp_student'=>$inp_student_callsign,
										'inp_semester'=>$theSemester,
										'inp_assigned_advisor'=>$inp_advisor_callsign,
										'inp_assigned_advisor_class'=>$inp_advisorClass,
										'inp_remove_status'=>'',
										'inp_arbitrarily_assigned'=>'',
										'inp_method'=>'add',
										'jobname'=>$jobname,
										'userName'=>$userName,
										'testMode'=>$testMode,
										'doDebug'=>$doDebug);
						
			$addResult			= add_remove_student($inp_data);
			if ($addResult[0] === FALSE) {
				$thisReason		= $removeResult[1];
				if ($doDebug) {
					echo "attempting to add $student_call_sign to $student_assigned_advisor class failed:<br />$thisReason<br />";
				}
				sendErrorEmail("$jobname Attempting to add $student_call_sign to $student_assigned_advisor class failed:<br />$thisReason");
				$content		.= "Attempting to add $student_call_sign to $student_assigned_advisor class failed:<br />$thisReason<br />";
			} else {

	Remove Student example:
		$inp_data			= array('inp_student'=>$student_call_sign,
									'inp_semester'=>$student_semester,
									'inp_assigned_advisor'=>$student_assigned_advisor,
									'inp_assigned_advisor_class'=>$student_assigned_advisor_class,
									'inp_remove_status'=>'',
									'inp_arbitrarily_assigned'=>$student_no_catalog,
									'inp_method'=>'remove',
									'jobname'=>$jobname,
									'userName'=>$userName,
									'testMode'=>$testMode,
									'doDebug'=>$doDebug);
						
		$removeResult		= add_remove_student($inp_data);
		if ($removeResult[0] === FALSE) {
			$thisReason		= $removeResult[1];
			if ($doDebug) {
				echo "attempting to remove $student_call_sign from $student_assigned_advisor class failed:<br />$thisReason<br />";
			}
			sendErrorEmail("$jobname Attempting to remove $student_call_sign from $student_assigned_advisor class failed:<br />$thisReason");
			$content		.= "Attempting to remove $student_call_sign from $student_assigned_advisor class failed:<br />$thisReason<br />";
		} else {
			$content		.= "Student removed from class and unassigned<br />";


	need to know:
		student callsign
		semester					can be calculated
		assigned advisor			(only need on add)
		assigned advisor class		(only need on add)
		remove status				blank or R (need only on remove)
		arbitrarily assigned 		Y | N (only need on add)
		testMode
		doDebug
		inp_method 						add | remove
		jobname

	actions taken on add:
		if arbitrarily assigned: set no_catalog to Y
			otherwise set to blank
		set assigned advisor
		set assigned advisor class
		set advisor select date
		set student status to S
		update action log
		add student to the advisorclass record
		clear class_verified in the advisor record

	actions taken on remove:
		if remove status is blank (returning student to unassigned pool)
			set student status to blank
			set assigned advisor to blank
			set assigned advisor class to 0
			set advisor select date to blank
			add advisor to exluded advisor
			set class priority to 1
			
			if the student status is V
				set intervention required to H
				set hold reason code to X
		
		if the remove status is not blank (not returning student to unassigned pool)
			set student status to the remove status
			leave everything else as currently in the student record		
				
		remove the student from the advisorclass record
		
	returns array(TRUE / FALSE,"reason")
		
	Modified 16Apr23 by Roland to fix action_log
	Modified 11Jun23 by Roland to fix the process to remove a student
	Modified 12Jul23 by Roland to use only current tables
*/
	global $wpdb;
	
	$doProceed					= TRUE;
	// get the data from inp_data
	if (count($inp_data) == 0) {
		$returnData				= array(FALSE,"incomplete input");
		$doProceed				= FALSE;
	}
	if ($doProceed) {
		$initializationArray 		= data_initialization_func();
		$userName					= $initializationArray['userName'];
		$currentSemester			= $initializationArray['currentSemester'];
		$nextSemester				= $initializationArray['nextSemester'];
		$inp_student				= '';
		$inp_semester				= '';
		$inp_assigned_advisor		= '';
		$inp_assigned_advisor_class	= 0;
		$inp_remove_status			= '';
		$inp_method					= '';
		$testMode					= FALSE;
		$doDebug					= FALSE;
		$jobname					= "Add - Remove Student";
		$fieldTest					= array('action_log','control_code');
		$actionDate				 	= date('Y-m-d H:i:s');
		$returnData					= array();
	
		foreach($inp_data as $thisKey=>$thisData) {
			${$thisKey}				= $thisData;
		}
	
		if ($inp_semester == '') {
			$inp_semester				= $currentSemester;
			if ($inp_semester == 'Not in Session') {
				$inp_semester			= $nextSemester;
			}
		}


		if ($doDebug) {
			echo "<br /><b>FUNCTION</b> add_remove_student<br />
				  inp_data:<br /><pre>";
				  print_r($inp_data);
				  echo "</pre><br />";
		}
		// test inp_data
		$errors						= "";
		$gotError					= FALSE;
		if ($inp_student == '') {
			$errors					.= "inp_student missing<br />";
			$gotError				= TRUE;
		}
		if ($inp_assigned_advisor == '') {
			$errors					.= "inp_assigned_advisor missing<br />";
			$gotError				= TRUE;
		}
		if ($inp_assigned_advisor_class == '') {
			$errors					.= "inp_assigned_advisor_class missing<br />";
			$gotError				= TRUE;
		}
		if ($inp_method == '') {
			$errors					.= "inp_method missing<br />";
			$gotError				= TRUE;
		}
		if ($gotError) {
			$returnData				= array(FALSE,"$errors");
		} else {

			if ($testMode) {
				$studentTableName		= "wpw1_cwa_consolidated_student2";
				$advisorNewTableName	= "wpw1_cwa_consolidated_advisor2";
				$advisorClassTableName	= "wpw1_cwa_consolidated_advisorclass2";
			} else {
				$studentTableName		= "wpw1_cwa_consolidated_student";
				$advisorNewTableName	= "wpw1_cwa_consolidated_advisor";
				$advisorClassTableName	= "wpw1_cwa_consolidated_advisorclass";
			}

			// get the student record
			if ($doDebug) {
				echo "getting student record for $inp_student<br />";
			}
			$sql				= "select student_id, 
										  semester, 
										  student_status, 
										  assigned_advisor, 
										  assigned_advisor_class, 
										  advisor_select_date, 
										  excluded_advisor,
										  action_log, 
										  no_catalog
									from $studentTableName 
									where call_sign='$inp_student' 
									and semester='$inp_semester'";
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
				$returnData			= array(FALSE,"getting $inp_student from $studentTableName failed");
			} else {
				$numSRows			= $wpdb->num_rows;
				if ($doDebug) {
					$myStr			= $wpdb->last_query;
					echo "ran $myStr<br />and found $numSRows rows<br />";
				}
				if ($numSRows > 0) {
					foreach ($wpw1_cwa_student as $studentRow) {
						$student_ID								= $studentRow->student_id;
						$student_semester						= $studentRow->semester;
						$student_student_status  				= strtoupper($studentRow->student_status);
						$student_action_log  					= $studentRow->action_log;
						$student_assigned_advisor  				= $studentRow->assigned_advisor;
						$student_advisor_select_date  			= $studentRow->advisor_select_date;
						$student_excluded_advisor				= $studentRow->excluded_advisor;
						$student_assigned_advisor_class 		= $studentRow->assigned_advisor_class;
						$student_no_catalog						= $studentRow->no_catalog;

						if ($inp_method == 'add') {
							// add student to advisors class

							if ($doDebug) {
								echo "Preparing to add $inp_student at id $student_ID to $inp_assigned_advisor $inp_assigned_advisor_class class<br />";
							}
							$student_action_log						= "$student_action_log / $actionDate Add_Remove $userName $jobname Student assigned to $inp_assigned_advisor $inp_assigned_advisor_class class";
							if ($inp_arbitrarily_assigned == 'Y') {
								$updateParams['no_catalog']			= 'Y';
								$updateFormat[]						= '%s';
							} else {
								$updateParams['no_catalog']			= '';
								$updateFormat[]						= '%s';
							}
							$updateParams['assigned_advisor']		= $inp_assigned_advisor;
							$updateFormat[]							= '%s';
							$updateParams['assigned_advisor_class']	= $inp_assigned_advisor_class;
							$updateFormat[]							= '%d';
							$updateParams['action_log']				= $student_action_log;
							$updateFormat[]							= '%s';
							$updateParams['advisor_select_date']	= date('Y-m-d H:i:s');
							$updateFormat[]							= '%s';
							$updateParams['student_status']			= 'S';
							$updateFormat[]							= '%s';
					
						} else {					/// remove the student
							if ($doDebug) {
								echo "Preparing to remove $inp_student at id $student_ID from $inp_assigned_advisor $inp_assigned_advisor_class class<br />";
							}
							$student_action_log						= "$student_action_log / $actionDate Add_Remove $userName $jobname Student removed from $inp_assigned_advisor $inp_assigned_advisor_class class";
							if ($student_no_catalog == 'Y') {
								$updateParams['no_catalog']			= '';
								$updateFormat[]						= '%s';
							}
							$updateParams['action_log']				= $student_action_log;
							$updateFormat[]							= '%s';
					
							if ($inp_remove_status == '') {					//// put in unassigned pool
								$updateParams['student_status']			= '';
								$updateFormat[]							= '%s';
								$updateParams['assigned_advisor']		= '';
								$updateFormat[]							= '%s';
								$updateParams['assigned_advisor_class']	= 0;
								$updateFormat[]							= '%d';
								$updateParams['advisor_select_date']	= '';
								$updateFormat[]							= '%s';
								if ($student_excluded_advisor == '') {
									$student_excluded_advisor			.= "$student_assigned_advisor";
								} else {
									$student_excluded_advisor			.= "|$student_assigned_advisor";
								}
								$updateParams['excluded_advisor']		= $student_excluded_advisor;
								$updateFormat[]							= '%s';
								$updateParams['class_priority']			= 1;
								$updateFormat[]							= '%d';
							} else {
								$updateParams['student_status']			= $inp_remove_status;
								$updateFormat[]							= '%s';	
								if ($student_excluded_advisor == '') {
									$student_excluded_advisor			.= "$student_assigned_advisor";
								} else {
									$student_excluded_advisor			.= "|$student_assigned_advisor";
								}
								$updateParams['excluded_advisor']		= $student_excluded_advisor;
								$updateFormat[]							= '%s';
								$updateParams['assigned_advisor']		= '';
								$updateFormat[]							= '%s';
								$updateParams['assigned_advisor_class']	= 0;
								$updateFormat[]							= '%d';
								if ($student_student_status == 'V') {
									$updateParams['hold_reason_code']		= 'X';
									$updateFormat[]							= '%s';
								}
							}
						}
						// update the student record and the audit log
						$studentUpdateData		= array('tableName'=>$studentTableName,
														'inp_method'=>'update',
														'inp_data'=>$updateParams,
														'inp_format'=>$updateFormat,
														'jobname'=>$jobname,
														'inp_id'=>$student_ID,
														'inp_callsign'=>$inp_student,
														'inp_semester'=>$student_semester,
														'inp_who'=>$userName,
														'testMode'=>$testMode,
														'doDebug'=>$doDebug);
						$updateResult	= updateStudent($studentUpdateData);
						if ($updateResult[0] === FALSE) {
							$myError	= $wpdb->last_error;
							$mySql		= $wpdb->last_query;
							$errorMsg	= "$jobname Processing $student_call_sign in $studentTableName failed. Reason: $updateResult[1]<br />SQL: $mySql<br />Error: $myError<br />";
							if ($doDebug) {
								echo $errorMsg;
							}
							sendErrorEmail($errorMsg);
//							$content		.= "Unable to update content in $studentTableName<br />";
						} else {
							if ($doDebug) {
								echo "student $inp_student updated. Updating advisorClass record<br />";
							}
					
							// update advisorclass record
							$updateParams		= array();
							$updateFormat		= array();
							$sql				= "select * from $advisorClassTableName 
													where advisor_call_sign = '$inp_assigned_advisor' 
													and sequence = $inp_assigned_advisor_class 
													and semester = '$inp_semester'";
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
								$returnData			= array(FALSE,"reading $advisorClassTableName for $inp_assigned_advisor $inp_assigned_advisor_class in semester $inp_semester failed");
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

										$advisorClass_advisor_last_name  		= no_magic_quotes($advisorClass_advisor_last_name);


										$addedStudent							= FALSE;
										if ($inp_method == 'add') {			// find the open spot
											if ($doDebug) {
												echo "looking for a slot to add the student $inp_student<br />";
											}
											for ($snum=1;$snum<31;$snum++) {
												if ($snum < 10) {
													$strSnum = str_pad($snum,2,'0',STR_PAD_LEFT);
												} else {
													$strSnum= strval($snum);
												}
												$theInfo= ${'advisorClass_student' . $strSnum};
												if ($doDebug) {
													echo "Looking at snum $snum strSnum $strSnum with a value of $theInfo<br />";
												}
												if ($theInfo == '') {       // have an open slot
													$advisorClass_action_log		= "$advisorClass_action_log / $actionDate $userName $inp_student added to this class ";
													$updateParams["student$strSnum"] = $inp_student;
													$updateFormat[]= '%s';
													$class_number_students++;
													$updateParams['number_students']= $class_number_students;
													$updateFormat[]= '%d';
													$addedStudent					= TRUE;
													if ($doDebug) {
														echo "have an open slot and added $inp_student<br />";
													}
													break;
												} else {
													if ($theInfo == $inp_student) {
														$addedStudent				= TRUE;
														if ($doDebug) {
															echo "Student $inp_student already assigned to slot $strSnum<br />";
														}
														break;
													}
												}
											}
											if (!$addedStudent) {
												if ($doDebug) {
													echo "Could not find an open slot for student $inp_student in $advisorClass_advisor_call_sign class $advisorClass_sequence<br />";
												}
												$returnData		= array(FALSE,"could not find an open slot for student $inp_student in $advisorClass_advisor_call_sign class $advisorClass_sequence<br />");
											} else {
												// clear out the class_verified date in the advisor record
												if ($doDebug) {
													echo "getting advisor id to clear out the class_verified info<br />";
												}
												// first, get the id for the advisor's record
												$sql					= "select advisor_id from $advisorNewTableName 
																			where call_sign = '$inp_assigned_advisor' 
																			and semester='$inp_semester'";
												$wpw1_cwa_advisor	= $wpdb->get_results($sql);
												if ($wpw1_cwa_advisor === FALSE) {
													$myError			= $wpdb->last_error;
													$myQuery			= $wpdb->last_query;
													if ($doDebug) {
														echo "Reading $advisorNewTableName table failed<br />
															  wpdb->last_query: $myQuery<br />
															  wpdb->last_error: $myError<br />";
													}
													$errorMsg			= "$jobname Reading $advisorNewTableName table failed. <p>SQL: $myQuery</p><p> Error: $myError</p>";
													sendErrorEmail($errorMsg);
													$content		.= "Unable to obtain content from $advisorNewTableName<br />";
												} else {
													$numARows			= $wpdb->num_rows;
													if ($doDebug) {
														$myStr			= $wpdb->last_query;
														echo "ran $myStr<br />and found $numARows rows in $advisorNewTableName table<br />";
													}
													if ($numARows > 0) {
														foreach ($wpw1_cwa_advisor as $advisorRow) {
															$advisor_ID							= $advisorRow->advisor_id;
																									
															if ($doDebug) {
																echo "Have advisor_id of $advisor_ID. Updating the class_verified info<br />";
															}
																									
															$advisorUpdateData		= array('tableName'=>$advisorNewTableName,
																							'inp_method'=>'update',
																							'inp_data'=>array('class_verified|N|s'),
																							'jobname'=>$jobname,
																							'inp_id'=>$advisor_ID,
																							'inp_callsign'=>$inp_assigned_advisor,
																							'inp_semester'=>$inp_semester,
																							'inp_who'=>$userName,
																							'testMode'=>$testMode,
																							'doDebug'=>$doDebug);
															$updateResult	= updateAdvisor($advisorUpdateData);
															if ($updateResult[0] === FALSE) {
																$myError	= $wpdb->last_error;
																$mySql		= $wpdb->last_query;
																$errorMsg	= "$jobname Processing $advisor_call_sign in $advisorNewTableName failed. Reason: $updateResult[1]<br />SQL: $mySql<br />Error: $myError<br />";
																if ($doDebug) {
																	echo $errorMsg;
																}
																sendErrorEmail($errorMsg);
																$content		.= "Unable to update content in $advisorNewTableName<br />";
															} else {
																if ($doDebug) {
																	echo "advisor class_verified updated<br />";
																}
															}
														}
													} else {
														if ($doDebug) {
															echo "Did not find a record in $advisorNewTableName for advisor_ID of $advisor_ID<br />";
														}
														sendErrorEmail("$jobname updating class_verified Did not find a record in $advisorNewTableName for advisor_ID of $advisor_ID");
													}
												}
											}
										} else {				/// removing a student
											if ($doDebug) {
												echo "looking for student $inp_student to remove from class record<br />";
											}
											$updateParams = array();
											$updateFormat = array();
											$numberStudents = 0;
											for ($snum=1;$snum<31;$snum++) {
												if ($snum < 10) {
													$strSnum = str_pad($snum,2,'0',STR_PAD_LEFT);
												} else {
													$strSnum = strval($snum);
												}
												$advisorClass_studentCallSign = ${'advisorClass_student' . $strSnum};
												if ($inp_student == $advisorClass_studentCallSign) {
													if ($doDebug) {
														echo "found $inp_student at student$strSnum<br />";
													}
													$doneMoving = FALSE;
													$numberStudents = $snum - 1;
													$ii = $snum;
													$jj = $snum + 1;
													while(!$doneMoving) {
														if ($jj < 10) {
															$strJJ = str_pad($jj,2,'0',STR_PAD_LEFT);
														} else {
															$strJJ = strval($jj);
														}
														if ($ii < 10) {
															$strII = str_pad($ii,2,'0',STR_PAD_LEFT);
														} else {
															$strII = strval($ii);
														}
														if (${'advisorClass_student' . $strJJ} != '') {
															${'advisorClass_student' . $strII} = ${'advisorClass_student' . $strJJ};
															${'advisorClass_student' . $strJJ} = '';
															$updateParams["student$strII"] = ${'advisorClass_student' . $strII};
															$updateFormat[]					= '%s';
															$numberStudents++;
															$ii++;
															$jj++;
														} else {
															$doneMoving = TRUE;
															${'advisorClass_student' . $strII} = '';
															$updateParams["student$strII"] = ${'advisorClass_student' . $strII};
															$updateFormat[]					= '%s';
														}
													}
												}
											}
											$updateParams['number_students'] 	= $numberStudents;
											$updateFormat[]						= '%d';
											$advisorClass_action_log 			= "$advisorClass_action_log / $actionDate $userName $inp_student removed ";
											$updateParams['action_log'] 		= $advisorClass_action_log;
											$updateFormat[] 					= '%s';

										}
										// update the advisorClass record
										if ($doDebug) {
											echo "preparing to update advisorClass record at $advisorClass_ID<br />";
										}
										$classUpdateData		= array('tableName'=>$advisorClassTableName,
																		'inp_method'=>'update',
																		'inp_data'=>$updateParams,
																		'inp_format'=>$updateFormat,
																		'jobname'=>$jobname,
																		'inp_id'=>$advisorClass_ID,
																		'inp_callsign'=>$advisorClass_advisor_call_sign,
																		'inp_semester'=>$advisorClass_semester,
																		'inp_who'=>$userName,
																		'testMode'=>$testMode,
																		'doDebug'=>$doDebug);
										$updateResult	= updateClass($classUpdateData);
										if ($updateResult[0] === FALSE) {
											$myError	= $wpdb->last_error;
											$mySql		= $wpdb->last_query;
											$errorMsg	= "$jobname Processing $advisorClass_advisor_call_sign in $advisorClassTableName failed. Reason: $updateResult[1]<br />SQL: $mySql<br />Error: $myError<br />";
											if ($doDebug) {
												echo $errorMsg;
											}
											sendErrorEmail($errorMsg);
											$returnData			= array(FALSE,"Unable to update content in $advisorClassTableName");
										} else {
											if ($doDebug) {
												echo "advisorClass record updated<br />";
											}
										}
									}
								} else {
									if ($doDebug) {
										echo "no $advisorClassTableName record found for $inp_assigned_advisor $inp_assigned_advisor_class<br />";
									}
									sendErrorEmail("$jobname no $advisorClassTableName record found for $inp_assigned_advisor $inp_assigned_advisor_class");
									$returnData			= array(FALSE,"no $advisorClassTableName record found for $inp_assigned_advisor $inp_assigned_advisor_class");
								}
							}
						}
					}
				} else {
					$returnData	= array(FALSE,"$inp_student not found in $studentTableName");
				}
			}
		}
	}
	
	if (count($returnData) == 0) {
		$returnData	= array(TRUE,"");
	}
	if ($doDebug) {
		echo "<b>Returning from add_remove_student</b><br /><br/>";
	}
	return $returnData;
}
add_action('add_remove_student','add_remove_student');