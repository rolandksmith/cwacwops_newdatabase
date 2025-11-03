function generate_catalog_for_student($inp_data = array('')) {

/*	Input data comes in the form of an array
		$inp_data			= array('student_semester'=>$student_semester, 
									'student_level'=>$student_level,
									'student_class_language'=>$student_class_language, 
									'student_no_catalog'=>$student_no_catalog, 
									'student_catalog_options'=>$student_catalog_options,
									'student_flexible'=>$student_flexible,  
									'student_first_class_choice_utc'=>$student_first_class_choice_utc, 
									'student_second_class_choice_utc'=>$student_second_class_choice_utc, 
									'student_third_class_choice_utc'=>$student_third_class_choice_utc, 
									'student_timezone_offset'=>$student_timezone_offset,
									'testMode'=>$testMode,
									'deDebug'=>$doDebug);

									'verifyMode'=>TRUE or FALSE


	Returns an array
		FALSE/option/catalog/avail		if FALSE, the error will be in display/error field
		display/error
		date1							unix time the semester starts
		date2							unix time the catalog is available
		date3							unix time students will be assigned to classes
		schedAvail						list of classes to be used with catalog return

	new catalog scheme
	Each semester has three important dates:
		Date 1 is January 1st, May 1st, and September 1st, This is the date the semester starts
			The value is days_to_semester
		Date 2 is March 10th, July 10th, and November 10th. This is the date the catalog becomes available
		Date 3 is April 10th, July 10th, and December 10th. This is the date students get assigned to advisors
		
		If the current date is earlier than Date2, then there is no catalog available
			display thirteen options
		If the current date is earlier than Date3 but greater than or equal to Date2
			display the catalog along with I'm Flexible
			All of the above
			
		Field names that are used or affected:
			inp_sked_times	(arrray)
			inp_sked_avail
			inp_available
			inp_flex
			

*/		
	global $wpdb;
	
// echo "inp_data:<br /><pre>";
// print_r($inp_data);
// echo "</pre><br />";

	$initializationArray 				= data_initialization_func();
	$currentSemester					= $initializationArray['currentSemester'];
	$nextSemester						= $initializationArray['nextSemester'];
	$semesterTwo						= $initializationArray['semesterTwo'];
	$semesterThree						= $initializationArray['semesterThree'];
	$semesterFour						= $initializationArray['semesterFour'];
	$semesterArray						= array($currentSemester,
												$nextSemester,
												$semesterTwo, 
												$semesterThree, 
												$semesterFour);
											
	$doProceed							= TRUE;
	$show13Options						= FALSE;
	$showCatalog						= FALSE;
	$showAvailable						= FALSE;
	$run_date							= date("Y-m-d H:i:s");
	$student_semester					= "";
	$student_level						= "";
	$student_no_catalog					= "";
	$student_catalog_options			= "";
	$student_flexible					= "";
	$student_first_class_choice_utc		= "";
	$student_second_class_choice_utc	= "";
	$student_third_class_choice_utc		= "";
	$student_timezone_offset			= "";
	$doDebug							= FALSE;
	$testMode							= FALSE;
	$verifyMode							= FALSE;
	$errorInfo							= "";
	$returnCatalog						= "";
	
	$MTM								= '';
	$MTA								= '';
	$MTE								= '';
	$TFM								= '';
	$TFA								= '';
	$TFE								= '';
	$SWM								= '';
	$SWA								= '';
	$SWE								= '';
	$STM								= '';
	$STA								= '';
	$STE								= '';
	$flexible							= '';
	$schedAvail							= "";

	
	$timeConversion			= array('0000'=>'Midnight',
									'0030'=>'12:30 am',
									'0100'=>'1:00 am',
									'0130'=>'1:30 am',
									'0200'=>'2:00 am',
									'0230'=>'2:30 am',
									'0300'=>'3:00 am',
									'0330'=>'3:30 am',
									'0400'=>'4:00 am', 
									'0430'=>'4:30 am', 
									'0500'=>'5:00 am', 
									'0530'=>'5:30 am', 
									'0600'=>'6:00 am', 
									'0630'=>'6:30 am', 
									'0700'=>'7:00 am',
									'0730'=>'7:30 am', 
									'0800'=>'8:00 am', 
									'0830'=>'8:30 am', 
									'0900'=>'9:00 am', 
									'0930'=>'9:30 am', 
									'1000'=>'10:00 am', 
									'1030'=>'10:30 am', 
									'1100'=>'11:00 am',
									'1130'=>'11:30 am',
									'1200'=> 'Noon', 
									'1230'=> '12:30 pm', 
									'1300'=>'1:00 pm', 
									'1330'=>'1:30 pm', 
									'1400'=>'2:00 pm', 
									'1430'=>'2:30 pm', 
									'1500'=>'3:00 pm', 
									'1530'=>'3:30 pm', 
									'1600'=>'4:00 pm', 
									'1630'=>'4:30 pm', 
									'1700'=>'5:00 pm', 
									'1730'=>'5:30 pm', 
									'1800'=>'6:00 pm', 
									'1830'=>'6:30 pm', 
									'1900'=>'7:00 pm', 
									'1930'=>'7:30 pm', 
									'2000'=>'8:00 pm', 
									'2030'=>'8:30 pm', 
									'2100'=>'9:00 pm', 
									'2130'=>'9:30 pm', 
									'2200'=>'10:00 pm', 
									'2230'=>'10:30 pm', 
									'2300'=>'11:00 pm',
									'2330'=>'11:30 pm');
		
	if ($doDebug) {
		echo "<br /><b>Generate Catalog for Student</b><br />
			   inp_data:<br /><pre>";
			   print_r($inp_data);
		echo "</pre><br />";
	}
	
	$myInt							= count($inp_data);
	if ($myInt < 10) {
		$errorInfo			= "inp_data should have 10 elements. Only $myInt elements given";
		if ($doDebug) {
			echo "inp_data should have 10 elements. Only $myInt elements given<br />";
		}
		$doProceed			= FALSE;
	}

	foreach($inp_data as $thisKey=>$thisValue) {
		${$thisKey}					= $thisValue;
		if ($doDebug) {
			echo "setting $thisKey to $thisValue<br />";
		}
	}
	
	
	// student_semester must be in the semester array
	if (!in_array($student_semester,$semesterArray)) {
		$errorInfo					= "Input semester of $student_semester not a valid semester";
		if ($doDebug) {
			echo "Input semester of $student_semester not a valid semester<br />";
		}
		$doProceed					= FALSE;
	}
	
	if ($doProceed) {
		// calculate the three important dates
		$myArray				= explode(" ",$student_semester);
		$thisYear				= $myArray[0];
		$thisSemester			= $myArray[1];
		
		// figure out the previous year
		$myStr					= "$thisYear-01-01 - 1 year";
		$myInt					= strtotime($myStr);
		$prevYear				= date('Y',$myInt);
		
		
		$dateArray				= array('Jan/Feb'=>"$thisYear-01-01,$prevYear-11-10,$prevYear-12-11",
										'May/Jun'=>"$thisYear-05-01,$thisYear-03-10,$thisYear-04-11", 
										'Sep/Oct'=>"$thisYear-09-01,$thisYear-07-10,$thisYear-08-12");
		$thisDates				= $dateArray[$thisSemester];
		$myArray				= explode(',',$thisDates);
		if ($doDebug) {
			echo "calculated Dates:<br /><pre>";
			print_r($myArray);
			echo "</pre><br />";
		}
		$date1					= strtotime($myArray[0]);		// semester start
		$date2					= strtotime($myArray[1]);		// catalog available
		$date3					= strtotime($myArray[2]);		// students assigned
		if ($doDebug) {
			echo "have calculated dates:<br />
					date1: $date1<br />
					date2: $date2<br />
					date3: $date3<br />";
		}
		
		// determine which info to display
		$currentTime			= strtotime($run_date);
		
		if ($currentTime < $date2) {
			$show13Options		= TRUE;
		} elseif ($currentTime < $date3 && $currentTime >= $date2) {
			$showCatalog		= TRUE;
		} elseif ($currentTime < $date1 && $currentTime >= $date3) {
			$showAvail			= TRUE;
		} elseif ($currentTime > $date1) {		// after Semester Starts
			$show13Options		= TRUE;
		} else {
			$errorInfo			= "run_date of $currentTime doesn't compare to $date1, $date2, or $date3";
			if ($doDebug) {
				echo "$run_date of $currentTime doesn't compare to $date1, $date2, or $date3<br />";
			}
			$doProceed			= FALSE;
		}
		
		if ($verifyMode) {
			if ($student_semester == $nextSemester) {
				$show13Options	= FALSE;
				$showCatalog	= TRUE;
				$showAvail		= FALSE;
			}
		}

$showCatalog = TRUE;
$show13Options = FALSE;
$showAvail = FALSE;
		
		if ($doProceed) {
			if ($show13Options) {
				$myArray				= explode(",",$student_catalog_options);
				if (count($myArray) > 0) {
					foreach($myArray as $thisValue) {
						${$thisValue}	= 'checked';
					}
				}
				if ($student_flexible == 'Y') {
					$flexible			= 'checked';
				}
				$myStr					= date('F jS',$date2);
				$returnCatalog			= "<table style='width:500px;'>
											<tr><td colspan='3' style='vertical-align:top;'>Indicate your preferences. Multiple selections can be made.</td></tr>
											<tr><td colspan='3' style='vertical-align:top;'>Classes held on <b>Monday and Thursday</b></td></tr>
											<tr><td style='vertical-align:top;'><input type='checkbox' name='inp_sked_times[]' class='formInputButton' value='MTM' $MTM> Mornings</td>
												<td style='vertical-align:top;'><input type='checkbox' name='inp_sked_times[]' class='formInputButton' value='MTA' $MTA> Afternoons</td>
												<td style='vertical-align:top;'><input type='checkbox' name='inp_sked_times[]' class='formInputButton' value='MTE' $MTE> Evenings</td></tr>
											<tr><td colspan='3' style='vertical-align:top;'>Classes held on <b>Tuesdays and Fridays</b></td></tr>
											<tr><td style='vertical-align:top;'><input type='checkbox' name='inp_sked_times[]' class='formInputButton' value='TFM' $TFM>Mornings</td>
												<td style='vertical-align:top;'><input type='checkbox' name='inp_sked_times[]' class='formInputButton' value='TFA' $TFA> Afternoons</td>
												<td style='vertical-align:top;'><input type='checkbox' name='inp_sked_times[]' class='formInputButton' value='TFE' $TFE> Evenings</td></tr>
											<tr><td colspan='3' style='vertical-align:top;'>Classes held on <b>Sundays and Wednesdays</b></td></tr>
											<tr><td style='vertical-align:top;'><input type='checkbox' name='inp_sked_times[]' class='formInputButton' value='SWM' $SWM> Mornings</td>
												<td style='vertical-align:top;'><input type='checkbox' name='inp_sked_times[]' class='formInputButton' value='SWA' $SWA> Afternoons</td>
												<td style='vertical-align:top;'><input type='checkbox' name='inp_sked_times[]' class='formInputButton' value='SWE' $SWE> Evenings</td></tr>
											<tr><td colspan='3' style='vertical-align:top;'>Classes held on <b>Sundays and Thursdays</b></td></tr>
											<tr><td style='vertical-align:top;'><input type='checkbox' name='inp_sked_times[]' class='formInputButton' value='STM' $STM> Mornings</td>
												<td style='vertical-align:top;'><input type='checkbox' name='inp_sked_times[]' class='formInputButton' value='STA' $STA> Afternoons</td>
												<td style='vertical-align:top;'><input type='checkbox' name='inp_sked_times[]' class='formInputButton' value='STE' $STE> Evenings</td></tr>
											<tr><td colspan='3'><hr></td></tr>
											<tr><td colspan='3' style='vertical-align:top;'><input type='checkbox' name='inp_flex' class='formInputButton' value='ANY' $flexible> Any and All of the Above</td></tr>
											</table>
											<p>Legend: <i>Mornings: 7am to Noon; Afternoon: Noon to 6pm; Evenings: 6pm to 10pm</i></p> 
											<p>Your selection(s) above will help CW Academy put together class schedules that 
											best meet the students' schedule preferences and advisor classes. Approximately $myStr the catalog 
											of available classes will be available. CW Academy will send you an email at that 
											time asking you to select your specific class preferences from the classes in the 
											catalog. If you do not make your class preference selections, you will not be 
											assigned to a class.</p>";
				$returnOption			= 'option';

			} elseif ($showCatalog) {
			
			
				// get the classes
				$catalogMode			= 'Production';
				if ($testMode) {
					$catalogMode		= 'TestMode';
				}
				$result					= generateClassTimes($student_timezone_offset,$student_level,$student_semester,'all',$doDebug,$catalogMode);
				if ($result != 'FALSE') {
					if ($doDebug) {
						echo "generateClassTimes:<br /><pre>";
						print_r($result);
						echo "</pre><br />";
					}
					// [level][sequence] = language|localtime|localdays|nmbr classes|utctime|utcdays|advisors
					// parse the catalog
					$returnCatalog		= "<p><b>Available Classes (all times are your local time)</b></p>\n
											<table style='width:auto;'>
											<tr><th colspan='6'>Class Preference</th></tr>\n
											<tr><th style='width:35px;'>First<br />Preference</th>\n
												<th style='width:35px;'>Second<br />Preference</th>\n
												<th style='width:35px;'>Third<br />Preference</th>\n
												<th style='vertical-align;bottom;width:250px'>Class Time and Days</th>\n
												<th style='vertical-align;bottom;'>Classes</th>
												<th style='vertical-align;bottom;'>Language</th></tr>\n";
					$none1			= 'checked';
					$none2			= 'checked';
					$none3			= 'checked';
					foreach($result as $thisLevel=>$thisData) {
						foreach($thisData as $thisSeq=>$catalogData) {
							if ($doDebug) {
								echo "processing $catalogData<br />";
							}
							$myArray	= explode("|",$catalogData);
							$clanguage	= $myArray[0];
							$utcTime	= $myArray[1];
							$utcDays	= $myArray[2];
							$localTime	= $myArray[3];
							$localDays	= $myArray[4];
							$numClasses	= $myArray[5];
							$advisors	= $myArray[6];

							// only process if language is English or matches student_class_language
							if ($clanguage == 'English' || $clanguage == $student_class_language) {
								$myStr			= str_replace(","," and ",$localDays);
								$utcValue		= "$utcTime $utcDays";
								$sendValue		= "$localTime $localDays|$utcTime $utcDays";
								$thisChoice1	= '';
								$thisChoice2	= '';
								$thisChoice3	= '';
								if ($student_first_class_choice_utc == $utcValue) {
									$thisChoice1	= 'checked'; 
									$none1			= '';
								}
								if ($student_second_class_choice_utc == $utcValue) {
									$thisChoice2	= 'checked';
									$none2			= '';
								}
								if ($student_third_class_choice_utc == $utcValue) {
									$thisChoice3	= 'checked';
									$none3			= '';
								}
								$convertedTime	= $timeConversion[$localTime];
								$returnCatalog	.= "<tr><td><input type='radio' class='formInputText' id='chk_sked1' name='inp_sked1' value='$sendValue' $thisChoice1></td>\n
														<td><input type='radio' class='formInputText' id='chk_sked2' name='inp_sked2' value='$sendValue' $thisChoice2></td>\n
														<td><input type='radio' class='formInputText' id='chk_sked3' name='inp_sked3' value='$sendValue' $thisChoice3></td>\n
														<td>$convertedTime $myStr</td>\n
														<td style='text-align:center;'>$numClasses</td>
														<td>$clanguage</td></tr>\n";
							}
						
						}
					}
					$flexYChecked			= '';
					$flexNChecked			= '';
					if ($student_flexible == 'Y') {
						$flexYChecked		= 'checked';
					} else {
						$flexNChecked		= 'checked';
					}
					$returnCatalog			.= "<tr><td><input type='radio' class='formInputText' id='chk_sked1' name='inp_sked1' value='None|None' $none1></td>\n
													<td><input type='radio' class='formInputText' id='chk_sked2' name='inp_sked2' value='None|None' $none2></td>\n
													<td><input type='radio' class='formInputText' id='chk_sked3' name='inp_sked3' value='None|None' $none3></td>\n
													<td>None of the above</td>\n
													<td></td><td></td></tr>\n
												<tr><td colspan='6'><hr></td></tr>\n
												<tr><td colspan='6' style='vertical-align:top;'><i>Indicate if you are  
														flexible and can be assigned to any of the classes listed above</i><br />\n
														<input type='radio' class='formInputText' name='inp_flex' value='Y' $flexYChecked>Yes, I'm flexible<br />
														<input type='radio' class='formInputText' name='inp_flex' value='N' $flexNChecked>No</td></tr>\n
												</table>";
					$returnOption		= 'catalog';
				}			
			} elseif ($showAvail) {
				if ($doDebug) {
					echo "doing showAvail<br />";
				}

// keeping this code although it's not being used at present
//
//				// get list of classes with open seats
//				$result					= build_list_of_available_classes($student_semester,$testMode,$doDebug);
//				if ($doDebug) {
//					echo "got list of available classes:<br /><pre>";
//					print_r($result);
//					echo "</pre><br />";
//				}
//				if (count($result) < 1) {
//					if ($doDebug) {
//						echo "no classes with available seats found<br />";
//					}
//					$doProceed			= FALSE;
//				} else {
//					// get the classes for the student_level
//					$catalogEntries		= "";
//					$myInt				= 0;
//					foreach($result[$student_level] as $thisSched=>$thisAdvisorInfo) {
//						$myArray		= explode(" ",$thisSched);
//						$thisTimeUTC	= $myArray[0];
//						$thisDaysUTC	= $myArray[1];
//						if ($doDebug) {
//							echo "have option $thisTimeUTC $thisDaysUTC utc<br />";
//						}
//						$timeConvert	= utcConvert('tolocal',$student_timezone_offset,$thisTimeUTC,$thisDaysUTC,$doDebug);
//						if ($doDebug) {
//							echo "result of utcConvert:<br /><pre>";
//							print_r($timeConvert);
//							echo "</pre><br />";
//						}
//						if ($timeConvert[0] == 'FAIL') {
//							if ($doDebug) {
//								echo "utcConvert failed. Reason: $timeConvert[3]<br />";
//							}
//							$doProceed		= FALSE;
//							$errorInfo		= "utcConvert failed advisor . Reason: $timeConvert[3]";
//						} else {
//							$thisTimeLocal	= $timeConvert[1];
//							$thisDaysLocal	= $timeConvert[2];
//							$myStr			= $timeConversion[$thisTimeLocal];
//							$strChecked		= '';
//							if ($student_first_class_choice_utc == $thisSched) {
//								$strChecked	= 'checked';
//							}
//							$catalogEntries	.= "<tr><td> <input type='radio' class='formInputButton' name='inp_available' value='$thisTimeLocal $thisDaysLocal|$thisTimeUTC $thisDaysUTC' $strChecked required> $myStr $thisDaysLocal</td></tr>";
//							$myInt++;
//						}
//					}
//					if ($myInt > 0) {
//						$returnCatalog		= "<p><b>Available Classes (all times are your local time)</b></p>
//												<table style='width:auto;'>
//												$catalogEntries
//												<tr><td><input type='radio' class='formInputButton' name='inp_available' value='None' required> No options will work</td></tr>
//												</table>";
//					} else {
//						$returnCatalog		= "<p><b>Available Classes (all times are your local time)</b></p>
//												<table style='width:auto;'>
//												<tr><td>No available classes</td></tr>
//												</table>";
//					}
//					$returnOption		= 'avail';
//				}
//
// because there are no longer various classes with open seats after students have 
// been assigned to advisors, at this point we'll only show the 13 options

//				$myArray				= explode(",",$student_catalog_options);
//				if (count($myArray) > 0) {
//					foreach($myArray as $thisValue) {
//						${$thisValue}	= 'checked';
//					}
//				}
//				if ($student_flexible == 'Y') {
//					$flexible			= 'checked';
//				}
				$returnCatalog	.= "<table style='width:auto;'>
									<tr><th colspan='3'>Class Preference</th></tr>\n
									<tr><th>First Preference</th>\n
										<th>Second Preference</th>\n
										<th>Third Preference</th>\n";
				
				
				$switchCount = 0;
				$optionsArray 	= array('Morning<br />(1000 &plusmn; 3 hours)|1000 Monday,Thursday',
										'Afternoon<br />(1500 &plusmn; 3 hours)|1500 Monday,Thursday',
										'Evening<br />(1900 &plusmn; 3 hours)|1900 Monday,Thursday',
										'Morning<br />(1000 &plusmn; 3 hours)|1000 Tuesday,Friday',
										'Afternoon<br />(1500 &plusmn; 3 hours)|1500 Tuesday,Friday',
										'Evening<br />(1900 &plusmn; 3 hours)|1900 Tuesday,Friday',
										'Morning<br />(1000 &plusmn; 3 hours)|1000 Sunday,Wednesday',
										'Afternoon<br />(1500 &plusmn; 3 hours)|1500 Sunday,Wednesday',
										'Evening<br />(1900 &plusmn; 3 hours)|1900 Sunday,Wednesday',
										'Morning<br />(1000 &plusmn; 3 hours)|1000 Sunday,Thursday',
										'Afternoon<br />(1500 &plusmn; 3 hours)|1500 Sunday,Thursday',
										'Evening<br />(1900 &plusmn; 3 hours)|1900 Sunday,Thursday');
				
				foreach($optionsArray as $thisOption) {
					$myArray = explode('|',$thisOption);
					$localTimeDay = $myArray[0];
					$sendLocalTimeDay = $myArray[1];
					$myArray1 = explode(' ',$sendLocalTimeDay);
					$sendLocalTime = $myArray1[0];
					$sendLocalDays = $myArray1[1];
					if ($doDebug) {
						echo "<br />Have the following:<br />
							  localTimeDay: $localTimeDay<br />
							  sendLocalTimeDay: $sendLocalTimeDay<br />";
					}
					// convert to UTC
					$convertResult = utcConvert('toutc',$student_timezone_offset,$sendLocalTime,$sendLocalDays);
					if ($convertResult[0] != 'OK') {
						// convert didn't work
						$newUTCTime = '1900';
						$newUTCDays = 'Monday,Thursday';
						if ($doDebug) {
							echo "convert didn't work. Error: $convertResult[3]. Assuming 1900 Monday,Thursday<br />";
						}
					} else {
						$newUTCTime = $convertResult[1];
						$newUTCDays = $convertResult[2];
				
						$switchCount++;
						if ($switchCount > 3) {
							$switchCount = 1;
						}
						if ($doDebug) {
							echo "have newUTCTime $newUTCTime and newUTCDays $newUTCDays. SwitchCount; $switchCount<br />";
						}
						switch($switchCount) {
							case 1:
								$returnCatalog	.= "<tr><td colspan='3'>Classes held on <b>$sendLocalDays local time</b></td></tr>
													<tr><td><input type='radio' class='formInputText' id='chk_sked1' name='inp_sked1' value='$sendLocalTimeDay|$newUTCTime $newUTCDays' required>$localTimeDay</td>\n
														<td><input type='radio' class='formInputText' id='chk_sked2' name='inp_sked2' value='$sendLocalTimeDay|$newUTCTime $newUTCDays'>$localTimeDay</td>\n
														<td><input type='radio' class='formInputText' id='chk_sked2' name='inp_sked3' value='$sendLocalTimeDay|$newUTCTime $newUTCDays'>$localTimeDay</td>\n";
								break;
							case 2:
								$returnCatalog	.= "<tr><td><input type='radio' class='formInputText' id='chk_sked1' name='inp_sked1' value='$sendLocalTimeDay|$newUTCTime $newUTCDays' required>$localTimeDay</td>\n
														<td><input type='radio' class='formInputText' id='chk_sked2' name='inp_sked2' value='$sendLocalTimeDay|$newUTCTime $newUTCDays'>$localTimeDay</td>\n
														<td><input type='radio' class='formInputText' id='chk_sked3' name='inp_sked3' value='$sendLocalTimeDay|$newUTCTime $newUTCDays'>$localTimeDay</td>\n";
								break;
							case 3:
								$returnCatalog	.= "<tr><td><input type='radio' class='formInputText' id='chk_sked1' name='inp_sked1' value='$sendLocalTimeDay|$newUTCTime $newUTCDays' required>$localTimeDay</td>\n
														<td><input type='radio' class='formInputText' id='chk_sked2' name='inp_sked2' value='$sendLocalTimeDay|$newUTCTime $newUTCDays'>$localTimeDay</td>\n
														<td><input type='radio' class='formInputText' id='chk_sked3' name='inp_sked3' value='$sendLocalTimeDay|$newUTCTime $newUTCDays'>$localTimeDay</td>\n";
						}
					}
				}
				$returnCatalog			.= "<tr><td></td>\n
												<td><input type='radio' class='formInputText' id='chk_sked2' name='inp_sked2' value='None|None' checked>None</td>\n
												<td><input type='radio' class='formInputText' id='chk_sked3' name='inp_sked3' value='None|None' checked>None</td>\n
											<tr><td colspan='3'><hr></td></tr>\n
											<tr><td colspan='3' style='vertical-align:top;'><i>Indicate if you are  
													flexible and can be assigned to any of the classes listed above</i><br />\n
													<input type='radio' class='formInputText' name='inp_flex' value='Y'>Yes, I'm flexible<br />
													<input type='radio' class='formInputText' name='inp_flex' value='N' checked>No</td></tr>\n
											</table>";
				$returnOption			= 'avail';


			}
		}
	}
	if (!$doProceed) {
		$returnOption	= FALSE;
		$returnCatalog	= $errorInfo;
		$date1			= '';
		$date2			= '';
		$date3			= '';
		$schedAvail 	= '';
	}
	return array($returnOption,$returnCatalog,$date1,$date2,$date3,$schedAvail);
}
add_action('generate_catalog_for_student','generate_catalog_for_student');