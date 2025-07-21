function generateClassTimes($inp_tz=-99,$inp_level='',$inp_semester='',$inp_display='all',$doDebug,$catalogMode='Production') {

/* generateClassTimes runs in two modes
		If inp_level is 'all', the program returns a complete list in table
			from of all classes with the class schedule in UTC as well as 
			in the requested time zone for the requested semester
		If inp_level is one of 'Beginner', 'Fundamental', "Intermediate' or 'Advanced', 
			a table of available classes for that level adjusted for the inp_tz and semester
			will be returned
		If this criteria is not met for some reason, a string with the word 'FAIL' 
			is returned
		If inp_display is 'all', all catalog entries are displayed. Otherwise, 
			if inp_display is 'seats', only catalog entries with open seats are 
			displayed
			
	Returned array (if not FAIL):
		[level][sequence] = language|UTCtime|UTCdays|localtime|localdays|nmbr classes|utctime|utcdays|advisors

 	read the class catalog and generate the available classes array
	Catalog record format: level|time UTC|days|number of classes|advisors comma separated
		example: Advanced|0000|Tuesday,Friday|3|N5TOO-1,KK5NA-2,KK5NA-3
	Available classes array: printArray[level[sequence] = 'start time|days|number of classes
		example: printArray['Advanced'][0] = '0000|Monday,Thursday|3';
		
	Modified 12Jul23 by Roland to use only current tables
	Modified 17Dec24 by Roland for the inp_display option

*/

	global $wpdb;

	$doDebug					= TRUE;
//	$doDebug					= FALSE;
	if ($doDebug) {
		 echo "<br />In Function generateClassTimes with <br />
		 		inp_tz: $inp_tz<br />
		 		inp_level: $inp_level<br />
		 		inp_semester: $inp_semester<br />
		 		catalogMode: $catalogMode<br />";
	}

	$increment					= 0;	
	$initializationArray		= data_initialization_func();

	$validLevelArray			= array('Beginner',
										'Fundamental',
										'Intermediate',
										'Advanced',
										'All');
	$thisIncrement				= 0;
	$emailRoland				= FALSE;
	$rolandError				= "";
	$printArray					= array();
	$outputArray				= array();
	
	$currentSemester			= $initializationArray['currentSemester'];
	$nextSemester				= $initializationArray['nextSemester'];
	$semesterTwo				= $initializationArray['semesterTwo'];
	$semesterThree				= $initializationArray['semesterThree'];
	$semesterFour				= $initializationArray['semesterFour'];
	$validSemesters				= array($currentSemester,$nextSemester,$semesterTwo,$semesterThree,$semesterFour);
	
	if ($catalogMode == 'Production') {
		$advisorClassTableName	= 'wpw1_cwa_advisorclass';
		$catalogTableName		= 'wpw1_cwa_current_catalog';
	} else {
		$advisorClassTableName	= 'wpw1_cwa_advisorclass2';
		$catalogTableName		= 'wpw1_cwa_current_catalog2';
	}
	
	// validate the input data
	if (!in_array($inp_level,$validLevelArray)) {
		if ($doDebug) {
			echo "level of $inp_level not valid<br />";
		}
		return 'FAIL';
	}
	if (!in_array($inp_semester,$validSemesters)) {
		if ($doDebug) {
			echo "semester of $inp_semester not valid<br />";
		}
		return 'FAIL';
	}

	// get the catalog
	$gotCatalog					= FALSE;
	$sql 						= "select * from $catalogTableName 
									where mode='$catalogMode' 
									and semester='$inp_semester'";
	$result						= $wpdb->get_results($sql);
	if ($result === FALSE) {
		if ($doDebug) {
			echo "Reading $catalogTableName table failed<br />";
			echo "wpdb->last_query: " . $wpdb->last_query . "<br />";
			echo "<b>wpdb->last_error: " . $wpdb->last_error . "</b><br />";
		}
		$rolandError			.= "unable to find $catalogTableName table to read the catalog<br />";
		$emailRoland			= TRUE;
	} else {
		$numRows				= $wpdb->num_rows;
		if ($doDebug) {
			$myStr				= $wpdb->last_query;
			echo "Ran $myStr<br />and retrieved $numRows records from $catalogTableName<br />";
		}
		if ($numRows > 0) {
			foreach ($result as $catalogRow) {
				$jsonCatalog	= $catalogRow->catalog;
				$gotCatalog		= TRUE;
				
				$theCatalog		= json_decode($jsonCatalog,TRUE);
			}
		} else {
			$rolandError		.= "No catalog record found in $catalogTableName table for semester: $inp_semester, mode: $catalogMode<br />";
			$emailRoland		= TRUE;
			if ($doDebug) {
				echo "No catalog record found in $catalogTableName table for semester: $inp_semester, mode: $catalogMode<br />";
			}
		}
	}

	if ($gotCatalog) {
		if ($doDebug) {
			echo "Have a catalog record:<br /><pre>";
			print_r($theCatalog);
			echo "</pre><br />";
		}
		
		$arraySequence			= 0;	
		foreach($theCatalog as $thisLevel => $levelData) {
			if ($doDebug) {
				echo "thisLevel: $thisLevel<br />";
			}
			$doContinue			= FALSE;
			if ($inp_level == 'all') {
				$doContinue		= TRUE;
			} elseif ($inp_level == 'Beginner' && $thisLevel == 'Beginner') {
				$doContinue		= TRUE;
			} elseif ($inp_level == 'Fundamental' && $thisLevel == 'Fundamental') {
				$doContinue		= TRUE;
			} elseif ($inp_level == 'Intermediate' && $thisLevel == 'Intermediate') {
				$doContinue		= TRUE;
			} elseif ($inp_level == 'Advanced' && $thisLevel == 'Advanced') {
				$doContinue		= TRUE;
			}
			if ($doContinue) {
				foreach($levelData as $thisLanguage => $languageData) {
					foreach($languageData as $thisSched => $schedData) {
						if ($doDebug) {
							echo "processing $thisSched<br />";
						}
						$skedArray		= explode(" ",$thisSched);
						$thisUTCTime	= $skedArray[0];
						$thisUTCDays	= $skedArray[1];
						$convertResult	= utcConvert('tolocal',$inp_tz,$thisUTCTime,$thisUTCDays,$doDebug);
						if ($doDebug) {
							echo "local convertResult:<br /><pre>";
							print_r($convertResult);
							echo "</pre><br />";
						}
						$thisResult		= $convertResult[0];
						if ($thisResult != 'OK') {
							$reason		= $convertResult[3];
							sendErrorEmail('FUNCTION_generate_class_times converting $thisUTCTme $thisUTCDays to local failed');
							$localTimes	= "9999";
							$localDays	= "unknown";
						} else {
							$localTimes	= $convertResult[1];
							$localDays	= $convertResult[2];						
						}
						$classesCount	= 0;
						$advisorStr		= '';
						$firstTime		= TRUE;
						foreach($schedData as $thisSeq => $thisClass) {
							// decide if we can keep this advisor
							$keepAdvisor			= TRUE;
							if ($inp_display == 'seats') {
								$advisorArray		= explode('-',$thisClass);
								$thisAdvisor		= $advisorArray[0];
								$thisSequence		= $advisorArray[1];
								$seatsSQL			= "select * from $advisorClassTableName 
														where advisorclass_semester = '$inp_semester' 
														and advisorclass_call_sign = '$thisAdvisor' 
														and advisorclass_sequence = $thisSequence";
								$seatsResult		= $wpdb->get_results($seatsSQL);
								if ($seatsResult === FALSE) {
									handleWPDBError("FUNCTION_generate_class_times",$doDebug,'attempting to get seats information');
									$keepAdvisor	= FALSE;
								} else {
									$numSRows		= $wpdb->num_rows;
									if ($doDebug) {
										echo "ran $seatsSQL<br />and retrieved $numSRows rows<br />";
									}
									if ($numSRows > 0) {
										foreach($seatsResult as $seatsRow) {
											$advisorClass_class_size 				= $classResultRow->advisorclass_class_size;
											$advisorClass_number_students			= $classResultRow->advisorclass_number_students;
											
											$seatsAvail			= $advisorClass_class_size - $advisorClass_number_students;
											if ($doDebug) {
												echo "for advisor $thisAdvisor sequence $thisSequence<br />
														advisorClass_clss_size: $advisorClass_class_size<br >
														advisorClass_number_students: $advisorClass_number_students<br />
														seatsAvail: $seatsAvail<br />";
											}
											if ($seatsAvail < 1) {
												$keepAdvisor 	= FALSE;
												if ($doDebug) {
													echo "keepAdvisor is FALSE<br />";
												}
											}
										}
									}
								}
							}
							if ($keepAdvisor) {
								if ($doDebug) {
									echo "keeping advisor<br />";
								}
								$classesCount++;
								if ($firstTime) {
									$firstTime	= FALSE;
									$advisorStr	.= "$thisClass";
								} else {
									$advisorStr	.= ",$thisClass";
								}
							}
						}
						if ($doDebug) {
							echo "should have all the classes. classesCount: $classesCount; advisorStr: $advisorStr<br />";
						}
						if ($classesCount > 0) {
							$arraySequence++;
							$outputArray[$thisLevel][$arraySequence] = "$thisLanguage|$thisUTCTime|$thisUTCDays|$localTimes|$localDays|$classesCount|$advisorStr";
						}
					}
				}
			}
		}
	}
		
	if ($doDebug) {
		echo "<br />This is what will be returned:<br /><pre>";
		print_r($outputArray);
		echo "</pre><br />";
	}
	return $outputArray;
}
add_action('generateClassTimes','generateClassTimes');
 