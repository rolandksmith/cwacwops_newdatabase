function calculate_marketing_statistics_func() {

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
	$theURL						= "$siteURL/cwa-calculate-marketing-statistics/";
	$inp_semester				= '';
	$inp_rsave					= '';
	$jobname					= "Calculate Marketing Statistics";
	$uniqueStudentArray = array();
	$total_Beginner = 0;
	$total_Fundamental = 0;
	$total_Intermediate= 0;
	$total_Advanced = 0;
	$initial_Beginner = 0;
	$initial_Fundamental = 0;
	$initial_Intermediate = 0;
	$initial_Advanced = 0;
	$recycle_Beginner = 0;
	$recycle_Fundamental = 0;
	$recycle_Intermediate = 0;
	$recycle_Advanced = 0;
	$repeat_Beginner = 0;
	$repeat_Intermediate = 0;
	$repeat_Fundamental = 0;
	$repeat_Advanced = 0;
	$promoted_Beginner = 0;
	$promoted_Intermediate = 0;
	$promoted_Fundamental = 0;
	$promoted_Advanced = 0;
	$not_promoted_Beginner = 0;
	$not_promoted_Intermediate = 0;
	$not_promoted_Fundamental = 0;
	$not_promoted_Advanced = 0;
	$withdrew_Beginner = 0;
	$withdrew_Intermediate = 0;
	$withdrew_Fundamental = 0;
	$withdrew_Advanced = 0;
	$debugLog = "";
    $rolandData = "";
	$tookOnce_Beginner_promoted = 0;
	$tookOnce_Beginner_not_promoted = 0;
	$tookOnce_Beginner_withdrew = 0;
	$tookOnce_Fundamental_promoted = 0;
	$tookOnce_Fundamental_not_promoted = 0;
	$tookOnce_Fundamental_withdrew = 0;
	$tookOnce_Intermediate_promoted = 0;
	$tookOnce_Intermediate_not_promoted = 0;
	$tookOnce_Intermediate_withdrew = 0;
	$tookOnce_Advanced_promoted = 0;
	$tookOnce_Advanced_not_promoted = 0;
	$tookOnce_Advanced_withdrew = 0;
	$stoppedAt_Beginner_promoted = 0;
	$stoppedAt_Fundamental_promoted = 0;
	$stoppedAt_Intermediate_promoted = 0;
	$stoppedAt_Advanced_promoted = 0;
	$stoppedAt_Beginner_not_promoted = 0;
	$stoppedAt_Fundamental_not_promoted = 0;
	$stoppedAt_Intermediate_not_promoted = 0;
	$stoppedAt_Advanced_not_promoted = 0;
	$stoppedAt_Beginner_withdrew = 0;
	$stoppedAt_Fundamental_withdrew = 0;
	$stoppedAt_Intermediate_withdrew = 0;
	$stoppedAt_Advanced_withdrew = 0;
    $stoppedAt_Beginner = 0;
    $stoppedAt_Fundamental = 0;
    $stoppedAt_Intermediate = 0;
    $stoppedAt_Advanced = 0;
		
	function debugReport($message) {
		global $debugLog, $doDebug;
		$timestamp = date('Y-m-d H:i:s');
		$debugLog .= "$message ($timestamp)<br />";
		if ($doDebug) {
			echo "$message<br />";
		}
	}
	

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
		$operatingMode = 'Testmode';
	} else {
		$operatingMode = 'Production';
	
	}
	
	 
	 $semesterConvert = array(
							'2020 Apr/May' => '2020_2',
							'2020 Jan/Feb' => '2020_1',
							'2020 SEP/OCT' => '2020_3',
							'2021 Apr/May' => '2021_2',
							'2021 JAN/FEB' => '2021_1',
							'2021 Sep/Oct' => '2021_3',
							'2022 Jan/Feb' => '2022_1',
							'2022 May/Jun' => '2022_2',
							'2022 Sep/Oct' => '2022_3',
							'2023 Jan/Feb' => '2023_1',
							'2023 May/Jun' => '2023_2',
							'2023 Sep/Oct' => '2023_3',
							'2024 Jan/Feb' => '2024_1',
							'2024 May/Jun' => '2024_2',
							'2024 Sep/Oct' => '2024_3',
							'2025 Jan/Feb' => '2025_1',
							'2025 May/Jun' => '2025_2',
							'2025 Sep/Oct' => '2025_2');

	debugReport("Initialization Array:<br /><pre>");
	$myStr = print_r($context->toArray(), TRUE);
	debugReport("$myStr</pre>");
                            



	// generate initial zero counters
	foreach($semesterConvert as $thisKey => $realSemester) {
		${'semester_' . $realSemester . '_Beginner_initial'} = 0;
		${'semester_' . $realSemester . '_Beginner_recycle'} = 0;
		${'semester_' . $realSemester . '_Beginner_promoted'} = 0;
		${'semester_' . $realSemester . '_Beginner_not_promoted'} = 0;
		${'semester_' . $realSemester . '_Beginner_withdrew'} = 0;
		${'semester_' . $realSemester . '_Fundamental_initial'} = 0;
		${'semester_' . $realSemester . '_Fundamental_recycle'} = 0;
		${'semester_' . $realSemester . '_Fundamental_promoted'} = 0;
		${'semester_' . $realSemester . '_Fundamental_not_promoted'} = 0;
		${'semester_' . $realSemester . '_Fundamental_withdrew'} = 0;
		${'semester_' . $realSemester . '_Intermediate_initial'} = 0;
		${'semester_' . $realSemester . '_Intermediate_recycle'} = 0;
		${'semester_' . $realSemester . '_Intermediate_promoted'} = 0;
		${'semester_' . $realSemester . '_Intermediate_not_promoted'} = 0;
		${'semester_' . $realSemester . '_Intermediate_withdrew'} = 0;
		${'semester_' . $realSemester . '_Advanced_initial'} = 0;
		${'semester_' . $realSemester . '_Advanced_recycle'} = 0;
		${'semester_' . $realSemester . '_Advanced_promoted'} = 0;
		${'semester_' . $realSemester . '_Advanced_not_promoted'} = 0;
		${'semester_' . $realSemester . '_Advanced_withdrew'} = 0;
	}
	 
	$student_dal = new CWA_Student_DAL();



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
		debugReport("<br />At pass 2");
		
        // get array of unique student callsigns... returns single level array
        // get array of unique student callsigns... returns single level array
        $doProceed = TRUE;
        debugReport("getting unique student callsigns");
        $studentData = $student_dal->run_sql('select distinct(student_call_sign) from TABLENAME order by student_call_sign', $operatingMode);
        if ($studentData === FALSE || $studentData === NULL) {
            debugReport("getting unique student callsigns returned FALSE|NULL");
            goto Bailout;
        } else {
            if (! empty($studentData)) {
                foreach($studentData as $thisCallsign) {
                    $uniqueStudentArray[] = $thisCallsign;
                }
            } else {
                debugReport("getting unique callsigns returned an empty data set");
                goto Bailout;
            }
        }
        debugReport("done getting unique student callsigns");

        // get all records for each student
        foreach($uniqueStudentArray as $key => $value) {
            foreach($value as $thisKey => $thisValue) {
                $$thisKey = $thisValue;
    //            debugReport("set $thisKey to $thisValue");
                $criteria = [
                    'relation' => 'AND',
                    'clauses' => [
                        ['field' => 'student_call_sign', 'value' => $student_call_sign, 'compare' => '=' ],
                        ['field' => 'student_response', 'value' => 'Y', 'compare' => 'Y' ],
                        [
                            'relation' => 'OR',
                            'clauses' => [
                                ['field' => 'student_status', 'value' => 'Y', 'compare' => '='],
                                ['field' => 'student_status', 'value' => 'S', 'compare' => '=']
                            ]
                        ]
                    ]
                ];
                $orderby = 'student_date_created';
                $order = 'ASC';
                $studentData = $student_dal->get_student_by_order( $criteria, $orderby, $order, $operatingMode );
                if ($studentData === FALSE || $studentData === NULL) {
                    debugReport("getting student data for $student_call_sign returned FALSE|NULL");
                } else {
                    if (! empty($studentData)) {
                        // initialize counters for this student
                        $firstClass = TRUE;
                        $didBeginner = FALSE;
                        $didFundamental = FALSE;
                        $didIntermediate = FALSE;
                        $didAdvanced = FALSE;
                        $classesTakenCount = 0;
                        foreach($studentData as $key => $value) {
            
                            foreach($value as $thisField => $thisValue) {
                                $$thisField = $thisValue;
                            }
                            debugReport("<br />processing $student_call_sign<br />
                                        Level: |$student_level|<br />
                                        Semester: |$student_semester|<br />
                                        Promotable: |$student_promotable|<br />
                                        Response: |$student_response|<br />
                                        Status: |$student_status|<br />
                                        Classes taken:");
                            if ($didBeginner) {
                                debugReport("didBeginner");
                            }
                            if ($didFundamental) {
                                debugReport("didFundamental");
                            }
                            if ($didIntermediate) {
                                debugReport("did Intermediate");
                            }
                            if ($didAdvanced) {
                                debugReport("didAdvanced");
                            }

                            // to use the student, must have response of Y and status of S or Y
                            if ($student_response == 'Y' && ($student_status== 'S' || $student_status == 'Y')) { 
                                // and the semester must be in the semesterConvert array
                                if (array_key_exists($student_semester,$semesterConvert)) {
                                    $classesTakenCount++;
                                    $studentSemester = $semesterConvert[$student_semester];
                                    ${'total_' . $student_level}++;
                                    debugReport("counted total_$student_level");
                                    if ($firstClass) {      /// first class attended
                                        ${'initial_' . $student_level}++;
                                        debugReport("incremented initial_$student_level");
                                    } else {
                                        ${'recycle_' . $student_level}++;
                                        debugReport("incremented recycle_$student_level");
                                    }
                                    if($student_promotable == 'P') {
                                        ${'semester_' . $studentSemester . '_' . $student_level . '_' . 'promoted'}++;
                                        ${'promoted_' . $student_level}++;
                                        debugReport("incremented promoted_$student_level");
                                    } elseif ($student_promotable == 'N' || $student_promotable == '') {
                                        ${'semester_' . $studentSemester . '_' . $student_level . '_' . 'not_promoted'}++;
                                        ${'not_promoted_' . $student_level}++;
                                        debugReport("incremented not_promoted_$student_level");
                                    } elseif ($student_promotable == 'W') {
                                        ${'semester_' . $studentSemester . '_' . $student_level . '_' . 'withdrew'}++;
                                        ${'withdrew_' . $student_level}++;
                                        debugReport("incremented withdrew_$student_level");
                                    }
                                    if($student_level == 'Beginner' && $didBeginner) {
                                        ${'repeat_' . $student_level}++;
                                        debugReport("incremented repeat_$student_level");

                                    } elseif ($student_level == 'Fundamental' && $didFundamental) {
                                        ${'repeat_' . $student_level}++;
                                        debugReport("icremented repeat_$student_level");

                                    } elseif ($student_level == 'Intermediate' && $didIntermediate) {
                                        ${'repeat_' . $student_level}++;
                                        debugReport("icremented repeat_$student_level");

                                    } elseif ($student_level == 'Advanced' && $didAdvanced) {
                                        ${'repeat_' . $student_level}++;
                                        debugReport("icremented repeat_$student_level");

                                    }
                                    ${'did' . $student_level} = TRUE;
                                    debugReport("set did$student_level to TRUE");
                                    if($firstClass) {
                                        $firstClass = FALSE;
                                        debugReport("set firstClass to FALSE");
                                    }
            
            
            
            
            
            
            
                                } else {
                                    debugReport("student byassed due to semester");
                                    $doProceed = FALSE;
                                }
                            } else {
                                debugReport("student bypassed due to status");
                                $doProceed = FALSE;
                            }
                        }           // done with all classes for this student
                        if ($classesTakenCount > 0) {
                            if($classesTakenCount == 1) {       // took only one class
                                if($student_promotable == 'P') {
                                    ${'tookOnce_' . $student_level . '_promoted'}++;
                                    debugReport("incremented tookOnce $student_level promoted");
                                } elseif ($student_promotable == 'W') {
                                    ${'tookOnce_' . $student_level . '_withdrew'}++;
                                    debugReport("incremented tookOnce $student_level withdrew");
                                } else {
                                    ${'tookOnce_' . $student_level . '_not_promoted'}++;
                                    debugReport("incremented tookOnce $student_level not_promoted");
                                }
                            }
                            ${'stoppedAt_' . $student_level}++;
                            if ($student_promotable == 'P') {
                                ${'stoppedAt_' . $student_level . '_promoted'}++;
                                debugReport("incremented stoppedAt $student_level promoted");
                            } elseif ($student_promotable == 'W') {
                                ${'stoppedAt_' . $student_level . '_withdrew'}++;
                                debugReport("incremented stoppedAt $student_level withdrew");
                            } else {
                                ${'stoppedAt_' . $student_level . '_not_promoted'}++;
                                debugReport("incremented stoppedAt $student_level not_promoted");
                            }
                        }
                    } else {
//                       $content .= "No student data returned<br />";
                    }
                }
            }
	    }
        // all records processed. Put out the reports
        $content .= "<h3>$jobname</h3>";;
        
        $content .= "<h4>Class Level Statistics</h4>
                    <table>
                        <tr><th></th>
                            <th>Beginner</th>
                            <th>Fundamental</th>
                            <th>Intermediate</th>
                            <th>Advanced</th>
                            <th>Total</th></tr>";
        $myTotal = $total_Beginner + $total_Fundamental + $total_Intermediate + $total_Advanced;
        $content .= "<tr><td>Students 
                                <span class='info-asterisk' data-title='Total number of students'>*</span>
                            </td>
                        <td>$total_Beginner</td>
                        <td>$total_Fundamental</td>
                        <td>$total_Intermediate</td>
                        <td>$total_Advanced</td>
                        <td>$myTotal</td></tr>";
        $myTotal = $initial_Beginner + $initial_Fundamental + $initial_Intermediate + $initial_Advanced;
        $content .= "<tr><td>Initial Class 
                                <span class='info-asterisk' data-title='New students taking their first class'>*</span>
                            </td>
                        <td>$initial_Beginner</td>
                        <td>$initial_Fundamental</td>
                        <td>$initial_Intermediate</td>
                        <td>$initial_Advanced</td>
                        <td>$myTotal</td></tr>";
        $myTotal = $recycle_Beginner + $recycle_Fundamental + $recycle_Intermediate + $recycle_Advanced;
        $content .= "<tr><td>Subsequent Class 
                                <span class='info-asterisk' data-title='Former students taking this class for the first time'>*</span>
                            </td>
                        <td>$recycle_Beginner</td>
                        <td>$recycle_Fundamental</td>
                        <td>$recycle_Intermediate</td>
                        <td>$recycle_Advanced</td>
                        <td>$myTotal</td></tr>";
        $myTotal = $repeat_Beginner + $repeat_Fundamental + $repeat_Intermediate + $repeat_Advanced;
        $content .= "<tr><td>Repeating Students 
                                <span class='info-asterisk' data-title='Students taking this class over again'>*</span>
                            </td>
                        <td>$repeat_Beginner</td>
                        <td>$repeat_Fundamental</td>
                        <td>$repeat_Intermediate</td>
                        <td>$repeat_Advanced</td>
                        <td>$myTotal</td></tr>";
        $myTotal = $promoted_Beginner + $promoted_Fundamental + $promoted_Intermediate + $promoted_Advanced;
        $content .= "<tr><td>Promoted 
                                <span class='info-asterisk' data-title='Students marked as promotable by the advisor'>*</span>
                            </td>
                        <td>$promoted_Beginner</td>
                        <td>$promoted_Fundamental</td>
                        <td>$promoted_Intermediate</td>
                        <td>$promoted_Advanced</td>
                        <td>$myTotal</td></tr>";
        $myTotal = $not_promoted_Beginner + $not_promoted_Fundamental + $not_promoted_Intermediate + $not_promoted_Advanced;
        $content .= "<tr><td>Not Promoted 
                                <span class='info-asterisk' data-title='Students marked as not promotable by the advisor OR students not evaluated by theadvisor'>*</span>
                            </td>
                        <td>$not_promoted_Beginner</td>
                        <td>$not_promoted_Fundamental</td>
                        <td>$not_promoted_Intermediate</td>
                        <td>$not_promoted_Advanced</td>
                        <td>$myTotal</td></tr>";
        $myTotal = $withdrew_Beginner + $withdrew_Fundamental + $withdrew_Intermediate + $withdrew_Advanced;
        $content .= "<tr><td>Withdrew 
                                <span class='info-asterisk' data-title='Students marked by the advisor as having withdrawn from the class at some point'>*</span>
                            </td>
                        <td>$withdrew_Beginner</td>
                        <td>$withdrew_Fundamental</td>
                        <td>$withdrew_Intermediate</td>
                        <td>$withdrew_Advanced</td>
                        <td>$myTotal</td></tr>
                    </table>";

        // students who took one class only
        $myTotal5 = 0;
        $content .= "<br /><h4>Students Who Took One Class Only</h4>
                    <table>
                        <tr><th></th>
                            <th>Beginner</th>
                            <th>Fundaental</th>
                            <th>Intermediate</th>
                            <th>Advanced</th>
                            <th>Total</th></tr>";
        $myTotal = $tookOnce_Beginner_promoted + $tookOnce_Fundamental_promoted + $tookOnce_Intermediate_promoted + $tookOnce_Advanced_promoted;
        $myTotal5 = $myTotal5 + $myTotal;
        $content .= "<tr><td>Promoted</td>
                        <td>$tookOnce_Beginner_promoted</td>
                        <td>$tookOnce_Fundamental_promoted</td>
                        <td>$tookOnce_Intermediate_promoted</td>
                        <td>$tookOnce_Advanced_promoted</td>
                        <td>$myTotal</td></tr>";
        $myTotal = $tookOnce_Beginner_not_promoted + $tookOnce_Fundamental_not_promoted + $tookOnce_Intermediate_not_promoted + $tookOnce_Advanced_not_promoted;
        $myTotal5 = $myTotal5 + $myTotal;
        $content .= "<tr><td>Not Promoted</td>
                        <td>$tookOnce_Beginner_not_promoted</td>
                        <td>$tookOnce_Fundamental_promoted</td>
                        <td>$tookOnce_Intermediate_not_promoted</td>
                        <td>$tookOnce_Advanced_not_promoted</td>
                        <td>$myTotal</td></tr>";
        $myTotal = $tookOnce_Beginner_withdrew + $tookOnce_Fundamental_withdrew + $tookOnce_Intermediate_withdrew + $tookOnce_Advanced_withdrew;
        $myTotal5 = $myTotal5 + $myTotal;
        $content .= "<tr><td>Withdrew</td>
                        <td>$tookOnce_Beginner_withdrew</td>
                        <td>$tookOnce_Fundamental_promoted</td>
                        <td>$tookOnce_Intermediate_withdrew</td>
                        <td>$tookOnce_Advanced_withdrew</td>
                        <td>$myTotal</td></tr>";
        $myTotal1 = $tookOnce_Beginner_promoted + $tookOnce_Beginner_not_promoted + $tookOnce_Beginner_withdrew;
        $myTotal2 = $tookOnce_Fundamental_promoted + $tookOnce_Fundamental_not_promoted + $tookOnce_Fundamental_withdrew;
        $myTotal3 = $tookOnce_Intermediate_promoted + $tookOnce_Intermediate_not_promoted + $tookOnce_Intermediate_withdrew;
        $myTotal4 = $tookOnce_Advanced_promoted + $tookOnce_Advanced_not_promoted + $tookOnce_Advanced_withdrew;
        $content .= "<tr><td>Total</td>
                        <td>$myTotal1</td>
                        <td>$myTotal2</td>
                        <td>$myTotal3</td>
                        <td>$myTotal4</td>
                        <td>$myTotal5</td></tr></table>";

        // where students stopped
        $content .= "<br /><h4>Where Students Stopped
                    <span class='info-asterisk' data-title='Includes students who repeated the class'>*</span>
                    </h4>
                    <table>
                        <tr><th></th>
                            <th>Beginner</th>
                            <th>Fundaental</th>
                            <th>Intermediate</th>
                            <th>Advanced</th>
                            <th>Total</th></tr>";
        $myTotal = $stoppedAt_Beginner_promoted + $stoppedAt_Fundamental_promoted + $stoppedAt_Intermediate_promoted + $stoppedAt_Advanced_promoted;
        $content .= "<tr><td>Promoted</td>
                        <td>$stoppedAt_Beginner_promoted</td>
                        <td>$stoppedAt_Fundamental_promoted</td>
                        <td>$stoppedAt_Intermediate_promoted</td>
                        <td>$stoppedAt_Advanced_promoted</td>
                        <td>$myTotal</td></tr>";
        $myTotal = $stoppedAt_Beginner_not_promoted + $stoppedAt_Fundamental_not_promoted + $stoppedAt_Intermediate_not_promoted + $stoppedAt_Advanced_not_promoted;
        $content .= "<tr><td>Not Promoted</td>
                        <td>$stoppedAt_Beginner_not_promoted</td>
                        <td>$stoppedAt_Fundamental_promoted</td>
                        <td>$stoppedAt_Intermediate_not_promoted</td>
                        <td>$stoppedAt_Advanced_not_promoted</td>
                        <td>$myTotal</td></tr>";
        $myTotal = $stoppedAt_Beginner_withdrew + $stoppedAt_Fundamental_withdrew + $stoppedAt_Intermediate_withdrew + $stoppedAt_Advanced_withdrew;
        $content .= "<tr><td>Withdrew</td>
                        <td>$stoppedAt_Beginner_withdrew</td>
                        <td>$stoppedAt_Fundamental_promoted</td>
                        <td>$stoppedAt_Intermediate_withdrew</td>
                        <td>$stoppedAt_Advanced_withdrew</td>
                        <td>$myTotal</td></tr>";
        $myTotal = $stoppedAt_Beginner + $stoppedAt_Fundamental + $stoppedAt_Intermediate + $stoppedAt_Advanced;
        $content .= "<tr><td>Total</td>
                        <td>$stoppedAt_Beginner</td>
                        <td>$stoppedAt_Fundamental</td>
                        <td>$stoppedAt_Intermediate</td>
                        <td>$stoppedAt_Advanced</td>
                        <td>$myTotal</td></tr></table>";
		
	
    }

	Bailout:

	$thisTime 		= date('Y-m-d H:i:s');
	$content 		.= "<br /><br /><p>Prepared at $thisTime</p>";

	///// uncomment if the code to save a report is needed
	debugReport("<br '>Checking to see if the report is to be saved. inp_rsave: $inp_rsave");
	if ($inp_rsave == 'Y') {
		debugReport("Calling function to save the report as Current Student and Advisor Assignments");
		$storeResult	= storeReportData_v2($jobname,$content);
		if ($storeResult[0] !== FALSE) {
			$reportName	= $storeResult[1];
			$reportID	= $storeResult[2];
			$content	.= "<br />Report stored in reports as $reportName<br />
							Go to'Display Saved Reports' or url<br/>
							<a href='$siteURL/cwa-display-saved-report/?strpass=3&token=&inp_id=$reportID' target='_blank'>Display Report</a><br /><br />";
		} else {
			$content	.= "<br />Storing the report in the reports table failed";
		}
        if ($debugLog != '') {
            debugReport("Calling function to save the report as $jobname Debug");
            $storeResult	= storeReportData_v2("$jobname Debug", $debugLog);
            if ($storeResult[0] !== FALSE) {
                $reportName	= $storeResult[1];
                $reportID	= $storeResult[2];
                $content	.= "<br />Report stored in reports as $reportName<br />
                                Go to'Display Saved Reports' or url<br/>
                                <a href='$siteURL/cwa-display-saved-report/?strpass=3&token=&inp_id=$reportID' target='_blank'>Display Report</a><br /><br />";
            } else {
                $content	.= "<br />Storing the report in the reports table failed";
            }
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
add_shortcode ('calculate_marketing_statistics', 'calculate_marketing_statistics_func');

