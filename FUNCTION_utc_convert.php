function utcConvert($direction='',$timezone='',$times='',$days='',$doDebug=FALSE) {

/*	Function to convert to or from UTC and Local Time

	Input:
		direction:	toutc = convert from local time to utc
					tolocal = convert from utc to local time
		timezone:	The local time zone offset
		times:		The hour to be converted
		days:		The days to be converted
		
	Returns an array:
		result:		'OK' = conversion had no errors
					'FAIL' = conversion had an error. Error is in 'status'
		newtime:	The converted time
		Newdays:	The converted days
		status:		blank if result is OK, otherwise the error message
		
	Return examples:
		array('OK','1900','Sunday,Wednesday','')
		array('FAIL','','','days not in daysConvert array')

	Error messages:
		Direction not toutc or tolocal
		days: not found in daysConvert array
		times missing
		days doesn't exist in dayBack array	
		days not found in dayForward array
		
*/



	if ($doDebug) {
		echo "<br />In utcConvert Function with direction: $direction; offset: $timezone; times: $times; days: $days<br />";
	}

	$increment				= 0;

	$dayBack				= array("Sunday,Wednesday"=>"Tuesday,Saturday",
									"Sunday,Thursday"=>"Wednesday,Saturday",
									"Monday,Wednesday"=>"Sunday,Tuesday",
									"Monday,Thursday"=>"Sunday,Wednesday",
									"Monday,Friday"=>"Sunday,Thursday",
									"Tuesday,Thursday"=>"Monday,Wednesday",
									"Tuesday,Friday"=>"Monday,Thursday",
									"Tuesday,Saturday"=>"Monday,Friday",
									"Wednesday,Friday"=>"Tuesday,Thursday",
									"Wednesday,Saturday"=>"Tuesday,Friday",
									"Saturday,Tuesday"=>"Friday,Monday");
									
	$dayForward				= array("Sunday,Wednesday"=>"Monday,Thursday",
									"Sunday,Thursday"=>"Monday,Friday",
									"Monday,Wednesday"=>"Tuesday,Thursday",
									"Monday,Thursday"=>"Tuesday,Friday",
									"Monday,Friday"=>"Tuesday,Saturday",
									"Tuesday,Thursday"=>"Wednesday,Friday",
									"Tuesday,Friday"=>"Wednesday,Saturday",
									"Tuesday,Saturday"=>"Sunday,Wednesday",
									"Wednesday,Friday"=>"Thursday,Saturday",
									"Wednesday,Saturday"=>"Sunday,Thursday",
									"Saturday,Tuesday"=>"Sunday,Wednesday");

	$timeCheck					= array('0000',
										'0030',
										'0100',
										'0130',
										'0200',
										'0230',
										'0300',
										'0330',
										'0400',
										'0430',
										'0500',
										'0530',
										'0600',
										'0630',
										'0700',
										'0730',
										'0800',
										'0830',
										'0900',
										'0930',
										'1000',
										'1030',
										'1100',
										'1130',
										'1200',
										'1230',
										'1300',
										'1330',
										'1400',
										'1430',
										'1500',
										'1530',
										'1600',
										'1630',
										'1700',
										'1730',
										'1800',
										'1830',
										'1900',
										'1930',
										'2000',
										'2030',
										'2100',
										'2130',
										'2200',
										'2230',
										'2300',
										'2330',
										'2400',
										'2430');


	$direction					= strtoupper($direction);
	if ($direction != 'TOUTC' && $direction != 'TOLOCAL') {
		if ($doDebug) {
			echo "<b>ERROR</b> direction has a value of $direction which is invalid<br />";
		}
		return array('FAIL','','',"Direction of $direction not toutc or tolocal");
	}
	if (!array_key_exists($days,$dayForward)) {
		if ($doDebug) {
			echo "<b>Error</b> invalid days of $days<br />";
		}
		return array('FAIL','','',"invalid days of $days");
	}
	if (!in_array($times, $timeCheck)) {
		if ($doDebug) {
			echo "<b>Error</b> invalid times of $times<br />";
		}
		return array('FAIL','','',"invalid times of $times");
	}
	if ($timezone == -99 || $timezone == -99.00) {
		if ($doDebug) {
			echo "<b>Error</b> invalid timezone of $timezone<br />";
		}
		return array('FAIL','','',"invalid timezone of $timezone");
	}
	
	$gotError					= FALSE;
	$errorMsg					= '';

	if ($direction == 'TOUTC') {
		if ($doDebug) {
			echo "Converting days: $days, time: $times to UTC using offset $timezone<br />";
		}
	} else {
		if ($doDebug) {
			echo "Converting days: $days, time: $times to local time using offset $timezone<br />";
		}
	}
	if (!$gotError) {
		$times					= str_pad($times,4,'0',STR_PAD_LEFT);
		$newOffset				= floatval($timezone);
		if ($direction == 'TOLOCAL') {
			$newOffset			= $newOffset * -1.0;
			if ($doDebug) {
				echo "working with $timezone converted to newOffset: $newOffset<br />";
			}
		}
		$thisHours 				= floatval(substr($times,0,2));
		$thisMins 				= floatval(substr($times,2,2));
		$thisCombined 			= $thisHours * 60 + $thisMins;
		if ($doDebug) {
			echo "Converting $times to minutes. thisHours: $thisHours, thisMins: $thisMins. Total mins: $thisCombined<br />";
		}
		$thisOffset 			= floatval($newOffset) * 60;
		$newValue 				= ($thisCombined - $thisOffset);
		if ($doDebug) {
			echo "$thisCombined minutes minus $thisOffset minutes equals $newValue minutes<br />";
		}

		if ($newValue == 0 || $newValue == 1440) {
			if ($doDebug) {
				echo "the time is zero/1440: $newValue<br />";
			}
			if ($newValue = 1440) {
				$newValue			= 0;
			}
			if ($direction == 'TOUTC') {
				if ($doDebug) {
					echo "moving $direction. Move forward a day<br />";
				}
				if (array_key_exists($days,$dayForward)) {
					$newDays		= $dayForward[$days];
					if ($doDebug) {
						echo "move yielded $newDays<br />";
					}
				} else {
					if ($doDebug) {
						echo "<b>ERROR</b> $days not found in dayForward array<br >";
					}
					$gotError	= TRUE;
					$errorMsg	= "$days not found in dayForward array<br >";
				}
			} else {
				if ($doDebug) {
					echo "moving $direction. Move backward a day<br />";
				}
				if (array_key_exists($days,$dayBack)) {
					$newDays		= $dayBack[$days];
					if ($doDebug) {
						echo "move yielded $newDays<br />";
					}
				} else {
					if ($doDebug) {
						echo "<b>ERROR</b> $days doesn't exist in dayBack array<br />";
					}
					$gotError	= TRUE;
					$errorMsg	.= "$days doesn't exist in dayBack array<br />";
				}
			}
		} elseif ($newValue < 0) {
			if ($doDebug) {
				echo "the time is negative: $newValue<br />";			
			}
			$newValue			= $newValue + 1440;
			if ($doDebug) {
				echo "Moving a day backwards<br />";
			}
			if (array_key_exists($days,$dayBack)) {
				$newDays		= $dayBack[$days];
			} else {
				if ($doDebug) {
					echo "<b>ERROR</b> $days doesn't exist in dayBack array<br />";
				}
				$gotError	= TRUE;
				$errorMsg	.= "$days doesn't exist in dayBack array<br />";
			}
		
		} elseif ($newValue > 1440) {
			if ($doDebug) {
				echo "the time is greater than 1440: $newValue<br />";			
			}
			$newValue			= $newValue - 1440;
			if ($doDebug) {
				echo "Moving a day forward<br />";
			}
			if (array_key_exists($days,$dayForward)) {
				$newDays		= $dayForward[$days];
			} else {
				if ($doDebug) {
					echo "<b>ERROR</b> $days not found in dayForward array<br >";
				}
				$gotError	= TRUE;
				$errorMsg	= "$days not found in dayForward array<br >";
			}
		} else {
			$newDays			= $days;
		}
		if (!$gotError) {
			// convert the time in minutes to the time in hours and minutes		
			$newTime			= date('Hi', mktime(0,$newValue));
			if ($doDebug) {
				echo "Conversion done. Schedule Days: $newDays; times: $newTime<br />";
			}
			return array('OK',$newTime,$newDays,'');
		} else {
			$theContent		= "utcconvert generated the following errors:<br />$errorMsg<br />
Input: direction: $direction; timezone: $timezone; times: $times; days: $days";
			$myStr			= sendErrorEmail($theContent);
			return array('FAIL','','',$errorMsg);		

		}
	} else {
		$theContent		= "utcconvert generated the following errors:<br />$errorMsg";
		$myStr			= sendErrorEmail($theContent);
		return array('FAIL','','',$errorMsg);
	}
}
add_action('utcConvert','utcConvert');
