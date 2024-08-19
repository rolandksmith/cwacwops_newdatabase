function generateClassTimes($inp_tz=-99,$inp_level='',$inp_semester='',$doDebug=FALSE,$catalogMode='Production') {

/* generateClassTimes runs in two modes
		If inp_level is 'all', the program returns a complete list in table
			from of all classes with the class schedule in UTC as well as 
			in the requested time zone for the requested semester
		If inp_level is one of 'Beginner', 'Fundamental', "Intermediate' or 'Advanced', 
			a table of available classes for that level adjusted for the inp_tz and semester
			will be returned
		If this criteria is not met for some reason, a string with the word 'FAIL' 
			is returned
			
	Returned array (if not FAIL):
		[level][sequence] = localtime|localdays|nmbr classes|utctime|utcdays|advisors

 	read the class catalog and generate the available classes array
	Catalog record format: level|time UTC|days|number of classes|advisors comma separated
		example: Advanced|0000|Tuesday,Friday|3|N5TOO-1,KK5NA-2,KK5NA-3
	Available classes array: printArray[level[sequence] = 'start time|days|number of classes
		example: printArray['Advanced'][0] = '0000|Monday,Thursday|3';
		
	Modified 12Jul23 by Roland to use only current tables

*/

	global $wpdb;

//	$doDebug					= TRUE;
	if ($doDebug) {
		 echo "<br />In Function generateClassTimes with $inp_tz,$inp_level,$inp_semester,$catalogMode<br />";
	}

	$increment					= 0;	
	$initializationArray		= data_initialization_func();
	$moveForwardDays			= array('Monday,Thursday'=>'Tuesday,Friday',
										'Tuesday,Friday'=>'Wednesday,Saturday',
										'Wednesday,Friday'=>'Thursday,Saturday',
										'Sunday,Wednesday'=>'Monday,Thursday',
										'Tuesday,Thursday'=>'Wednesday,Friday',
										'Wednesday,Saturday'=>'Thursday,Sunday');

	$moveBackwardDays			= array('Monday,Thursday'=>'Sunday,Wednesday',
										'Tuesday,Friday'=>'Monday,Thursday',
										'Wednesday,Friday'=>'Tuesday,Thursday',
										'Sunday,Wednesday'=>'Saturday,Tuesday',
										'Tuesday,Thursday'=>'Monday,Wednesday',
										'Wednesday,Saturday'=>'Tuesday,Friday',
										'Monday,Friday'=>'Sunday,Thursday');

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
	$catalogTableName			= 'wpw1_cwa_current_catalog';
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
				$theCatalog		= $catalogRow->catalog;
				$gotCatalog		= TRUE;
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
			echo "Have a catalog record:<br />$theCatalog<br />";
		}
		$thisArray						= explode("&",$theCatalog);
		if ($doDebug) {
			echo "Exploded the theCatalog<br /><pre>";
			print_r($thisArray);
			echo "</pre><br />";
		}
		foreach($thisArray as $buffer) {
			if ($doDebug) {
				echo "buffer: $buffer<br />";
			}	
			$myArray				= explode("|",$buffer);
			$myInt					= count($myArray);
			if ($doDebug) {
				echo "Exploded an entry in buffer and got $myInt entries<br />";
			}
			if ($myInt > 1) {
				$thisLevel			= $myArray[0];
				$thisTime			= $myArray[1];
				$thisDays			= $myArray[2];
				$thisCount			= $myArray[3];
				$thisAdvisors		= $myArray[4];
				$skipLine			= FALSE;
	
				$printArray[$thisLevel][$thisIncrement] = "$thisTime|$thisDays|$thisCount|$thisAdvisors";
				$thisIncrement++;
			}
		}
		$noErrors					= TRUE;	
		if ($thisIncrement < 1) {
			$rolandError			= "Catalog has no entries<br />";
			$emailRoland			= TRUE;
			$outputArray			= array('FAIL - Missing Catalog');
			$noErrors				= FALSE;
		}

		if ($doDebug) {
			echo "printArray:<br /><pre>";
			print_r($printArray);
			echo "</pre><br />";
		}

		if ($noErrors) {
			// if we get here the request is either for 'all' or for a specific level	
			$tzKey			= intval($inp_tz);
			$tempArray		= array();
			foreach($validLevelArray as $thisLevel) {
				$doProceed		= TRUE;
				if ($doDebug) {
					echo "<br />Doing the validLevelArray of $thisLevel<br />";
				}
				if ($inp_level != 'All') {
					if ($thisLevel != $inp_level) {
						$doProceed	= FALSE;
						if ($doDebug) {
							echo "$thisLevel is not requested<br />";
						}				
					}
				}
				if ($doProceed) {
					foreach($printArray[$thisLevel] as $thisSeq=>$classData) {
						if ($doDebug) {
							echo "<br />Doing printArray $thisLevel, $thisSeq, $classData<br />";
						}
						$skipLine			= FALSE;
						$thisArray			= explode("|",$classData);
						$classStartUTC		= $thisArray[0];
						$classDaysUTC		= $thisArray[1];
						$classCount			= $thisArray[2];
						$classAdvisors		= $thisArray[3];
						$origDays			= $thisArray[1];
						if ($doDebug) {
							echo "Processing $thisLevel $classStartUTC | $classDaysUTC | $classCount<br />";
						}
						// prepare the local time information
						$classStart	= intval($classStartUTC) + ($tzKey * 100);
						if ($doDebug) {
							echo "classStartUTC $classStartUTC adjusted for tzKey of $tzKey is $classStart<br />";
						}
						if ($classStart >= 2400) {			// next day
							$classStart	= $classStart - 2400;
							if (array_key_exists($classDaysUTC,$moveForwardDays)) {
								$classDays	= $moveForwardDays[$classDaysUTC];
								if ($doDebug) {
									echo "$classStart $classStart adjusted for next day and days changed from $origDays to $classDays<br />";
								}
							} else {
								if ($doDebug) {
									echo "<b>$classDaysUTC not found in moveForwardDays</b><br />";
								}
								$rolandError	.= "$classDaysUTC not found in moveForwardDays<br />";
								$emailRoland	= TRUE;	
								$skipLine		= TRUE;
							}
						} elseif ($classStart < 0) {
							$classStart	= $classStart + 2400;
							if (array_key_exists($classDaysUTC,$moveBackwardDays)) {
								$classDays	= $moveBackwardDays[$classDaysUTC];
								if ($doDebug) {
									echo "$classStart $classStart adjusted for previous day and days changed from $origDays to $classDays<br />";
								}
							} else {
								if ($doDebug) {
									echo "<b>$classDaysUTC not found in moveBackwardDays</b><br />";
								}
								$rolandError	.= "$classDaysUTC not found in moveBackwardDays<br />";
								$emailRoland	= TRUE;	
								$skipLine		= TRUE;
							}
						} else {
							$classDays			= $classDaysUTC;
						}
						if (!$skipLine) {
							$classStart			= str_pad($classStart,4,'0',STR_PAD_LEFT);
							$tempArray[]		= "$classStart|$classDays|$classCount|$classStartUTC|$classDaysUTC|$classAdvisors";
							if ($doDebug) {
								echo "added $classStart|$classDays|$classCount|$classStartUTC|$classDaysUTC|$classAdvisors to tempArray<br />";
							}
						} else {
							if ($doDebug) {
								echo "skipping $thisLevel $classData as info is invalid<br />";
							}
						}
					}
					if ($doDebug) {
						echo "Got the tempArray:<br /><pre>";
						print_r($tempArray);
						echo "</pre><br />";
					}
					sort($tempArray);
					/// now build the output array
					if ($doDebug) {
						echo "<br />tempArray:<br />";
					}
					$jj			= 0;
					foreach($tempArray as $tempValue) {
						$outputArray[$thisLevel][$jj]	= $tempValue;
						if ($doDebug) {
							echo "added $tempValue to outputArray at $thisLevel and sequence $jj<br />";
						}
						$jj++;			
					}
					$tempArray			= array();
				} else {
					if ($doDebug) {
						echo "bypassed $thisLevel as doProceed was false<br />";
					}
				}
			}
		}
	}
	if ($emailRoland) {
		if ($doDebug) {
			echo "Emailing the following to Roland:<br />$rolandError<br />";
		}
		$theRecipient		= '';
		$theContent			= "Generate Class Times encountered the following errors:<br />$rolandError";
		$theSubject 		= "CWA.CWOPS Error in Generate Class Times";
		$mailCode			= 1;
		$mailResult		= emailFromCWA_v2(array('theRecipient'=>$theRecipient,
													'theSubject'=>$theSubject,
													'jobname'=>'FUNCTION Generate Class Times',
													'theContent'=>$theContent,
													'mailCode'=>$mailCode,
													'increment'=>$increment,
													'testMode'=>$catalogMode,
													'doDebug'=>$doDebug));
	}
	if ($doDebug) {
		echo "<br />This is what will be returned:<br /><pre>";
		print_r($outputArray);
		echo "</pre><br />";
	}
	return $outputArray;
}
add_action('generateClassTimes','generateClassTimes');
 