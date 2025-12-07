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
			}
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
		}

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
		
*/

	$student_dal = new CWA_Student_DAL();
	$advisor_dal = new CWA_Advisor_DAL();
	$advisorclass_dal = new CWA_Advisorclass_DAL();
	
	
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
		$haveStudentData			= FALSE;
		$haveAdvisorData			= FALSE;
		$haveAdvisorclassData		= FALSE;
		$studentUpdateParams		= array();
		$advisorUpdateParams		= array();
		$advisorclassUpdateParams	= array();
		$updateStudent				= FALSE;
		$updateAdvisor				= FALSE;
		$updateAdvisorclass			= FALSE;
		$content					= '';
	
		foreach($inp_data as $thisKey=>$thisData) {
			$$thisKey				= $thisData;
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
		if (!$gotError) {
			if ($testMode) {
				$operatingMode = 'Testmode';
			} else {
				$operatingMode = 'Production';
			}

			// get the student record
			if ($doDebug) {
				echo "getting student record for $inp_student<br />";
			}
			$criteria = [
				'relation' => 'AND',
				'clauses' => [
					['field' => 'student_call_sign', 'value' => $inp_student, 'compare' => '=' ],
					['field' => 'student_semester', 'value' => $inp_semester, 'compare' => '=' ]
				]
			];
			$student_data = $student_dal->get_student($criteria,'student_call_sign','DESC',$operatingMode);
			if ($student_data === FALSE) {
				$errors .= "Attempting to retrieve student $inp_callsing returned FALSE";
				$gotError = TRUE;
				$doProceed = FALSE;
			} else {
				foreach($student_data as $key => $value) {
					foreach($value as $thisField => $thisValue) {
						$$thisField = $thisValue;
					}
					$haveStudentData = TRUE;
				}
			}
			if ($doProceed) {
				// get Advisor 
				$criteria = [
					'relation' => 'AND',
					'clauses' => [
						['field' => 'advisor_call_sign', 'value' => $inp_assigned_advisor, 'compare' => '=' ],
						['field' => 'advisor_semester', 'value' => $inp_semester, 'compare' => '=' ]
					]
				];
				$advisor_data = $advisor_dal->get_advisor($criteria,$operatingMode);
				if ($advisor_data === FALSE) {
					$errors .= "Attempting to retrieve advisor $inp_assigned_advisor class $inp_semester returned FALSE";
					$gotError = TRUE;
					$doProceed = FALSE;
				} else {
					foreach($advisor_data as $key => $value) {
						foreach($value as $thisField => $thisValue) {
							$$thisField = $thisValue;
						}
						$haveAdvisorData = TRUE;
					}
				}
				
			}
			if ($doProceed) {
				// get advisorclass record
				$criteria = [
					'relation' => 'AND',
					'clauses' => [
						['field' => 'advisorclass_call_sign', 'value' => $inp_assigned_advisor, 'compare' => '=' ],
						['field' => 'advisorclass_sequence', 'value' => $inp_assigned_advisor_class, 'compare' => '=' ],
						['field' => 'advisorclass_semester', 'value' => $inp_semester, 'compare' => '=' ]
					]
				];
				$advisorclass_data = $advisorclass_dal->get_advisor_classes($criteria,$operatingMode);
				if ($advisorclass_data === FALSE) {
					$errors .= "Attempting to retrieve advisorclass $inp_assigned_advisor class $inp_assigned_advisor_class returned FALSE";
					$gotError = TRUE;
					$doProceed = FALSE;
				} else {
					foreach($advisorclass_data as $key => $value) {
						foreach($value as $thisField => $thisValue) {
							$$thisField = $thisValue;
						}
						$haveAdvisorclassData = TRUE;
					}
				}
			}
			
			if ($haveStudentData && $haveAdvisorData && $haveAdvisorclassData) {
				if ($inp_method == 'add') {
					// only add if student_assigned_advisor is blank
					if ($student_assigned_advisor == '') {
						// add student to advisors class
						if ($doDebug) {
							echo "Preparing to add $inp_student at id $student_ID to $inp_assigned_advisor $inp_assigned_advisor_class class<br />";
						}
						$student_action_log = "$student_action_log / $actionDate Add_Remove $userName $jobname Student assigned to $inp_assigned_advisor $inp_assigned_advisor_class class";
						if ($inp_arbitrarily_assigned == 'Y') {
							$studentUpdateParams['student_no_catalog']	= 'Y';
						} else {
							$studentUpdateParams['student_no_catalog']	= '';
						}
						$studentUpdateParams['student_assigned_advisor'] = $inp_assigned_advisor;
						$studentUpdateParams['student_assigned_advisor_class']	= $inp_assigned_advisor_class;
						$studentUpdateParams['student_action_log'] = $student_action_log;
						$studentUpdateParams['student_advisor_select_date']	= date('Y-m-d H:i:s');
						$studentUpdateParams['student_status'] = 'S';
						$updateStudent = TRUE;
					} else {
						if ($doDebug) {
							echo "student_assigned_advisor already set to $student_assigned_advisor<br />";
						}
						$gotError			= TRUE;
						$errors				.= "student_assigned_advisor already set to $student_assigned_advisor<br />";
						$doProceed			= FALSE;
					}					
				} else {					/// remove the student
					if ($doDebug) {
						echo "Preparing to remove $inp_student at id $student_ID from $inp_assigned_advisor $inp_assigned_advisor_class class<br />";
					}
					// only remove if student_assigned_advisor is $inp_assigned_advisor
					if ($student_assigned_advisor == $inp_assigned_advisor) {
						$student_action_log						= "$student_action_log / $actionDate Add_Remove $userName $jobname Student removed from $inp_assigned_advisor $inp_assigned_advisor_class class";
						if ($student_no_catalog == 'Y') {
							$studentUpdateParams['student_no_catalog']			= '';
						}
						$studentUpdateParams['student_action_log']				= $student_action_log;
				
						if ($inp_remove_status == '') {					//// put in unassigned pool
							$studentUpdateParams['student_status'] = '';
							$studentUpdateParams['student_assigned_advisor'] = '';
							$studentUpdateParams['student_pre_assigned_advisor']	= '';
							$studentUpdateParams['student_assigned_advisor_class']	= 0;
							$studentUpdateParams['student_advisor_select_date']	= '';
							$newStudentExcludedAdvisor = updateExcludedAdvisor($student_excluded_advisor,$student_assigned_advisor,'add',$doDebug);
							if ($newStudentExcludedAdvisor === FALSE) {
								if ($doDebug) {
									echo "adding $student_assigned_advisor to student_excluded_advisors of $student_excluded_advisors failed<br />";
								}
							} else {
								$studentUpdateParams['student_excluded_advisor'] = $newStudentExcludedAdvisor;
							}
							$studentUpdateParams['student_class_priority'] = 1;
							$updateStudent = TRUE;
						} else {
							// student_remove_status is not blank
							$studentUpdateParams['student_status'] = $inp_remove_status;
							$newStudentExcludedAdvisor = updateExcludedAdvisor($student_excluded_advisor,$student_assigned_advisor,'add',$doDebug);
							if ($newStudentExcludedAdvisor === FALSE) {
								if ($doDebug) {
									echo "adding $student_assigned_advisor to student_excluded_advisors of $student_excluded_advisors failed<br />";
								}
							} else {
								$studentUpdateParams['student_excluded_advisor'] = $newStudentExcludedAdvisor;
							}
							$studentUpdateParams['student_assigned_advisor'] = '';
							$studentUpdateParams['student_assigned_advisor_class']	= 0;
							if ($student_status == 'V') {
								$studentUpdateParams['student_hold_reason_code']		= 'X';
							}
							$updateStudent = TRUE;
						}
					} else {
						if ($doDebug) {
							echo "$student_assigned_advisor is not $inp_assigned_advisor. No removal is possible<br />";
						}
						$gotError		= TRUE;
						$errors			.= "$student_assigned_advisor is not $inp_assigned_advisor. No removal is possible<br />";
						$doProceed		= FALSE;
					}
				}
				if ($doProceed) {					
					// setup advisorclass record
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
								$advisorclassUpdateParams["advisorclass_student$strSnum"] = $inp_student;
								$advisorClass_number_students++;
								$advisorclassUpdateParams['advisorclass_number_students']= $advisorClass_number_students;
								$addedStudent					= TRUE;
								if ($doDebug) {
									echo "added student to an open slot $strSnum for $inp_student<br />";
								}
								$updateAdvisorclass = TRUE;
							} else {
								if ($theInfo == $inp_student) {
									if ($doDebug) {
										echo "Student $inp_student already assigned to slot $strSnum<br />";
									}
									$doProceed = FALSE;
									$gotError = TRUE;
									$errors .= "Student $inp_student already assigned to slot $strSnum<br />";
									break;
								}
							}
						}
						if (!$addedStudent) {
							if ($doDebug) {
								echo "Could not find an open slot for student $inp_student in $advisorClass_call_sign class $advisorClass_sequence<br />";
							}
							$gotError = TRUE;
							$errors .= "Could not find an open slot for student $inp_student in $advisorClass_call_sign class $advisorClass_sequence<br />";
							$doProceed = FALSE;
						} else {
							// clear out the class_verified data in the advisor record
							if ($doDebug) {
								echo "setting advisor_class_verified to N<br />";
							}
							$advisorUpdateParams = array('advisor_class_verified' => 'N');
							$updateAdvisor = TRUE; 
						}
					} else {				/// removing a student
						if ($doDebug) {
							echo "looking for student $inp_student to remove from class record<br />";
						}
						$numberStudents = 0;
						$foundLocation = '';
						$foundTheCulprit = FALSE;
						$lastEntry = '';
						for ($snum=1;$snum<31;$snum++) {
							if ($snum < 10) {
								$strSnum = str_pad($snum,2,'0',STR_PAD_LEFT);
							} else {
								$strSnum = strval($snum);
							}
							$foundCallSign = ${'advisorClass_student' . $strSnum};
							if ($foundCallSign != '') {
								$lastEntry = $strSnum;
								$numberStudents = $snum;
								if ($inp_student == $foundCallSign) {
									$foundLocation = $strSnum;
									$foundTheCulprit = TRUE;
								}
							}
						}
						if($foundTheCulprit) {
							if ($doDebug) {
								echo "lastEntry: $lastEntry<br />foundLocation: $foundLocation<br />";
							}
							$updateAdvisorclass = TRUE;
							if ($foundLocation == $lastEntry) {
								$advisorclassUpdateParams["advisorClass_student$lastEntry"] = '';
								if ($doDebug) {
									echo "found $inp_student at lastEntry $lastEntry and deleted<br />";
								}
							} else {
								$advisorclassUpdateParams["advisorClass_student$foundLocation"]	= ${'advisorClass_student' . $lastEntry};
								$advisorclassUpdateParams["advisorClass_student$lastEntry"]		= '';
								if ($doDebug) {
									echo "moved last entry $lastEntry to found location $foundLocation, wiped out last entry $lastEntry<br />";
								}
							}
							$numberStudents--;
							if ($doDebug) {
								echo "numberStudents: $numberStudents<br />";
							}
							$advisorclassUpdateParams['advisorclass_number_students']					= $numberStudents;
						} else {
							if ($doDebug) {
								echo "$inp_student not found\n";
							}
							$gotError = TRUE;
							$errors .= "$inp_student not found in advisorClass to remove<br />";
							$doProceed = FALSE;
						}
					}
				}
				if ($doProceed) {
					// all updates staged and no errors. Update the files
					if ($updateStudent) {
						// update the student record
						$updateResult = $student_dal->update($student_id,$studentUpdateParams,$operatingMode);
						if($updateResult === FALSE) {
							if ($doDebug) {
								echo "updating student returned FALSE<br />";
							}
							$gotError = TRUE;
							$errors .= "Attempt to update $inp_callsign at $student_id returned FALSE";
							$doProceed = FALSE;
						} else {
							if ($doDebug) {
								echo "student $inp_student updated. Updating advisorClass record<br />";
							}
							if ($inp_method == 'add') {
								$content .= "<p>$inp_student student record updated to show student is assigned to $inp_assigned_advisor class $inp_assigned_advisor_class</p> ";
							} else {
								$content .= "<p>$inp_student student record updated to show student is removed from $inp_assigned_advisor class $inp_assigned_advisor_class</p> ";
							}
						}
					}
				}
				if ($doProceed) {
					if ($updateAdvisor) {
						// update the advisor record
						$updateResult = $advisor_dal->update($advisor_id,$advisorUpdateParams,$operatingMode);
						if($updateResult === FALSE) {
							if ($doDebug) {
								echo "updating advisor returned FALSE<br />";
							}
							$gotError = TRUE;
							$errors .= "Attempt to update $advisor_call_sign at $advisor_id returned FALSE";
							$doProceed = FALSE;
						} else {
							$content .= "<p>Advisor record for $advisor_call_sign updated</p>";
						}
					}
				}
				if ($doProceed) {
					if ($updateAdvisorclass) {
						// updating advisorclass
						$updateResult = $advisorclass_dal->update($advisorclass_id,$advisorclassUpdateParams,$operatingMode);
						if($updateResult === FALSE) {
							if ($doDebug) {
								echo "updating advisorclass returned FALSE<br />";
							}
							$gotError = TRUE;
							$errors .= "Attempt to update $advisorclass_call_sign at $advisorclass_id returned FALSE";
							$doProceed = FALSE;
						} else {
							$content .= "<p>Advisorclass record for $advisor_call_sign updated</p>";
						}
					}
				}
			}
		}
	}
	
	if (!$gotError) {
		$returnData	= array(TRUE,$content);
	} else {
		$returnData	= array(FALSE,$errors);
	}
	if ($doDebug) {
		echo "<b>Returning from add_remove_student</b><br /><br/>";
	}
	return $returnData;
}
add_action('add_remove_student','add_remove_student');