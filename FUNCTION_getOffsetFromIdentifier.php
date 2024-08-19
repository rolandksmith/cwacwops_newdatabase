function getOffsetFromIdentifier($identifier='',$semester='',$doDebug=FALSE) {

/*	returns the offset to UTC for a specified timezone identifier and semester

	input		the timezone identifier
				the semester
				
	returns		the offset OR
				FALSE if the input data is missing or incorrect

			$inp_timezone_offset	= getOffsetFromIdentifier($inp_timezone_id,$inp_semester);
			if ($inp_timezone_offset === FALSE) {
				if ($doDebug) {
					echo "getOffsetFromIdentifier returned FALSE using $inp_timezone_id, $inp_semester<br />";
				}
				$inp_timezone_offset	= -99;
			}

*/

	if ($doDebug) {
		echo "<br />In getOffsetFromIdentifier with identifier: $identifier and semester: $semester<br />";
	}
	
	if ($identifier == '' || $semester == '') {
		if ($doDebug) {
			echo "input data missing<br />";
		}
		return FALSE;
	} else {
		$myArray				= explode(" ",$semester);
		$thisYear				= $myArray[0];
		$thisMonDay				= $myArray[1];
		$myConvertArray			= array('Jan/Feb'=>'-01-01','May/Jun'=>'-05-01','Sep/Oct'=>'-09-01','JAN/FEB'=>'-01-01','May/JUN'=>'-05-01','SEP/OCT'=>'-09-01','Apr/May'=>'-04-01','APR/MAY'=>'-04-01');
		$myMonDay				= $myConvertArray[$thisMonDay];
		$thisNewDate			= "$thisYear$myMonDay 00:00:00";
		
		try {
			$dateTimeZoneLocal 		= new DateTimeZone($identifier);
		}
		catch (Exception $e) {
			if ($doDebug) {
				echo "<br /><b>getOffsetFromIdentifier: time zone indentifier of $inp_timezone_id is invalid</b><br />";
			}
			return FALSE;
		}
		$dateTimeZoneUTC	 	= new DateTimeZone("UTC");
		$dateTimeLocal 			= new DateTime($thisNewDate,$dateTimeZoneLocal);
		$dateTimeUTC 			= new DateTime($thisNewDate,$dateTimeZoneUTC);
		$php2 					= $dateTimeZoneLocal->getOffset($dateTimeUTC);
		$timezone_offset 		= $php2/3600;
	
		return $timezone_offset;
	}
}
add_action('getOffsetFromIdentifier','getOffsetFromIdentifier');