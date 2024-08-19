function convert_options($inp_data=array()) {

/*	converts advisor schedule into catalog_options or student_catalog_options into
	first, second, and third choices
	
	To convert advisor:
		inp_data array:		'inp_type'=>'advisor',
							'inp_times'=>$advisorClass_schedule_times_utc,
							'inp_days'=>$advisorClass_schedule_days_utc'.
							'doDebug'=>$doDebug
							
		returns the advisor schedule as one of 	MTM,MTA,MTE
												TFM,TFA,TFE
												SWM,SWA,SWE
												STM,STA,SWE
												WSM,WSA,WSE
												
		If the input data doesn't compute, returns FALSE
		
	To convert student:
		$inp_convert_data 	= array('inp_type'=>'student',
									'inp_offset'=>$student_timezone_offset,
									'inp_catalog_options'=>$student_catalog_options,
									'doDebug'=>$doDebug);
		$convertResult		= convert_options($inp_convert_data);
		if ($convertResult === FALSE) {
			if ($doDebug) {
				echo "convert_options failed. inp_convert_data:<br /><pre>";
				print_r($inp_convert_data);
				echo "</pre><br />";
			}
			///// do something else??
		} else {
							
		returns the 1st, 2nd, and 3rd options as one of
				 	MTM,MTA,MTE
					TFM,TFA,TFE
					SWM,SWA,SWE
					STM,STA,SWE
					WSM,WSA,WSE
					
					along with the corresponding UTC block 
																		

		Returns an array option1 => UTC Times & 3char code
						 option2 => UTC Times & 3char code
						 option3 => UTC Times & 3 char code

		If the input data doesn't compute, returns FALSE
		
		The function converts the student's preferences into pseudo UTC by
		assuming that 	Morning is 1000,
						Afternoon is 1500,
						Evening is 2000
						
*/

	$timeConvert		= array('00'=>'M',
								'01'=>'M',
								'02'=>'M',
								'03'=>'M',
								'04'=>'M',
								'05'=>'M',
								'06'=>'M',
								'07'=>'M',
								'08'=>'M',
								'09'=>'M',
								'10'=>'M',
								'11'=>'M',
								'12'=>'A',
								'13'=>'A',
								'14'=>'A',
								'15'=>'A',
								'16'=>'A',
								'17'=>'A',
								'18'=>'E',
								'19'=>'E',
								'20'=>'E',
								'21'=>'E',
								'22'=>'E',
								'23'=>'E');
								
	$daysConvert		= array('Monday,Thursday'=>'MT',
								'Tuesday,Friday'=>'TF',
								'Sunday,Wednesday'=>'SW',
								'Sunday,Thursday'=>'ST',
								'Wednesday,Saturday'=>'WS',
								'Monday,Friday'=>'MF');
	$priorityConvert	= array('MTE'=>'01',
								'TFE'=>'02',
								'SWE'=>'03',
								'STE'=>'04',
								'WSE'=>'05',
								'MFE'=>'06',
								'MTA'=>'07',
								'TFA'=>'08',
								'SWA'=>'09',
								'STA'=>'10',
								'SWA'=>'11',
								'MFA'=>'12',
								'MTM'=>'13',
								'TFM'=>'14',
								'SWM'=>'15',
								'STM'=>'16',
								'WSM'=>'17',
								'MFM'=>'18');
								
	$studentSchedConvert	= array('MTE'=>'2000 Monday,Thursday',
									'TFE'=>'2000 Tuesday,Friday',
									'SWE'=>'2000 Sunday,Wednesday',
									'STE'=>'2000 Sunday,Thursday',
									'WSE'=>'2000 Wednesday,Saturday',
									'MTA'=>'1500 Monday,Thursday',
									'TFA'=>'1500 Tuesday,Friday',
									'SWA'=>'1500 Sunday,Wednesday',
									'STA'=>'1500 Sunday,Thursday',
									'SWA'=>'1500 Sunday,Wednesday',
									'MTM'=>'1000 Monday,Thursday',
									'TFM'=>'1000 Tuesday,Friday',
									'SWM'=>'1000 Sunday,Wednesday',
									'STM'=>'1000 Sunday,Thursday',
									'WSM'=>'1000 Wednesday,Sunday');
								
								
	$inp_type				= '';
	$inp_times				= '';
	$inp_days				= '';
	$inp_offset				=  0.0;
	$inp_catalog_options	= '';
	$doDebug				= FALSE;

	$doProceed				= TRUE;
// echo "inp_data:<br /><pre>";
// print_r($inp_data);
// echo "</pre><br />";
	foreach($inp_data as $thisKey=>$thisValue) {
		$$thisKey			= $thisValue;
		if ($doDebug) {
			echo "set $thisKey to $thisValue<br />";
		}
	}
	
	if ($doDebug) {
		echo "<br /><b>FUNCTION convert_options</b><br />";
	}

	if ($inp_type != 'advisor' && $inp_type != 'student') {
		$doProceed			= FALSE;
		if ($doDebug) {
			echo "inp_type of $inp_type not advisor nor student<br />";
		}
	}
	if ($doProceed) {
		if ($inp_type == 'advisor') {
			if ($doDebug) {
				echo "processing advisor conversion<br />";
			}
			$myStr			= substr($inp_times,0,2);
			if (array_key_exists($myStr,$timeConvert)) {
				$thisTime		= $timeConvert[$myStr];
			} else {
				$doProceed		= FALSE;
				if ($doDebug) {
					echo "inp_times of $inp_times does not compute<br />";
				}
			}
			if (array_key_exists($inp_days,$daysConvert)) {
				$thisDays		= $daysConvert[$inp_days];
			} else {
				$doProceed		= FALSE;
				if ($doDebug) {
					echo "inp_days of $inp_days does not compute<br />";
				}
			}
			if ($doProceed) {
				$returnValue	= "$thisDays$thisTime";
				if ($doDebug) {
					echo "returning $returnValue<br />";
				}
			}
		} else {			// do student convert
			if ($doDebug) {
				echo "processing student conversion<br />";
			}
			// get the student catalog_options into an array and prioritize
			$myArray			= explode(",",$inp_catalog_options);
			$priorityArray		= array();
			foreach($myArray as $thisValue) {
				if ($doDebug) {
					echo "working catalog option $thisValue<br />";
				}
				if (array_key_exists($thisValue,$studentSchedConvert)) {
					$thisStudentSchedule 	= $studentSchedConvert[$thisValue];
					$breakApart				= explode(" ",$thisStudentSchedule);
					$thisSTimes				= $breakApart[0];
					$thisSDays				= $breakApart[1];

					$utcResult				= utcConvert('toutc',$inp_offset,$thisSTimes,$thisSDays,$doDebug);

					$resultOK				= $utcResult[0];
					$resultTimes			= $utcResult[1];
					$resultDays				= $utcResult[2];
					$resultStatus			= $utcResult[3];
					
					if ($resultOK == 'FAIL') {
						if ($doDebug) {
							echo "utcConvert toutc $inp_offset, $thisSTimes, $thisSDays failed: Error: $resultStatus<br />";
						}
						$doProceed			= FALSE;
					} else {
						if ($doDebug) {
							echo "utcConvert returned $resultTimes $resultDays<br />";
						}
						if (array_key_exists($resultDays,$daysConvert)) {
							$thisScheduleDays			= $daysConvert[$resultDays];
						} else {
							if ($doDebug) {
								echo "resultDays of $resultDays not in daysConvert<br />";
							}
							$doProceed					= FALSE;
						} 
						if ($doProceed) {
							$myStr						= substr($resultTimes,0,2);
							if (array_key_exists($myStr,$timeConvert)) {
								$thisScheduleTime		= $timeConvert[$myStr];
								$completeSked			= "$thisScheduleDays$thisScheduleTime";
								if (array_key_exists($completeSked,$priorityConvert)) {
									$thisPriority		= $priorityConvert[$completeSked];
									$priorityArray[]	= "$thisPriority|$resultTimes&$resultDays&$completeSked";
								} else {
									if ($doDebug) {
										echo "completeSked of $completeSked not found in priorityConvert<br />";
									}
									$doProceed			= FALSE;
								}
							}
						}
						
					}
				}
			}
			$option1				= '';
			$option2				= '';
			$option3				= '';
			if ($doProceed) {
				// return the top three options
				sort($priorityArray);
				if ($doDebug) {
					echo "priorityArray:<br /><pre>";
					print_r($priorityArray);
					echo "</pre><br />";
				}
				if (array_key_exists(0,$priorityArray)) {
					$breakupArray		= explode("|",$priorityArray[0]);
					$option1			= $breakupArray[1];
				}
				if (array_key_exists(1,$priorityArray)) {
					$breakupArray		= explode("|",$priorityArray[1]);
					$option2			= $breakupArray[1];
				}
				
				if (array_key_exists(2,$priorityArray)) {
					$breakupArray		= explode("|",$priorityArray[2]);
					$option3			= $breakupArray[1];
				}
			}
			$returnValue				= array('option1'=>$option1,
												'option2'=>$option2,
												'option3'=>$option3);
			if ($doDebug) {
				echo "returning returnValue:<br /><pre>";
				print_r($returnValue);
				echo "</pre><br />";
			}
		}
	}
	if ($doProceed) {
		return $returnValue;
	} else {
		return FALSE;
	}

}
add_action('convert_options','convert_options');