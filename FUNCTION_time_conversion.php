function time_conversion($timezonename,$timezonedate,$doDebug = FALSE) {

/*	Convert a time zone name into the UTC offset as of a particular date

	Input: 	Time Zone Name
			Date in the format of yyyy-mm-dd for which to calculate the offset
			Boolean whether or not to show debugging information
			
	Returns:	An integer with the UTC offset OR
				FALSE if the time zone name is invalid
			
	Created 3Aug2022 by Roland
*/

	/// function to verify that the time zone name is valid
/*	
	function isValidTimezoneId($timezoneId) {
		try{
			new DateTimeZone($timezoneId);
		}catch(Exception $e){
			return FALSE;
		}
		return TRUE;
	} 
*/	
	if ($doDebug) {
		echo "<br />Arrived at time_conversion_func with timezonename: $timezonename and timezonedate: $timezonedate<br />";
	}
	
//	if (isValidTimezoneId($timezonename) === FALSE) {
//		if ($doDebug) {
//			echo "isValidTimezoneId using $timezonename returned FALSE<br />";
//		}
//		return FALSE;
//	}
	
	

	$dateTimeZoneLocal = new DateTimeZone($timezonename);
	$dateTimeZoneUTC = new DateTimeZone("UTC");
	$dateTimeLocal = new DateTime("now",$dateTimeZoneLocal);
	$dateTimeUTC = new DateTime("now",$dateTimeZoneUTC);
	$php2 = $dateTimeZoneLocal->getOffset($dateTimeUTC);
	$offset = $php2/3600;
	$localDateTime = $dateTimeLocal->format('Y-m-d h:i A');
	
	if ($doDebug) {
		echo "Current Date Information:<br />
localDateTime: $localDateTime<br />
Current UTC Offset: $offset<br /><br />";
	}

	$dateTimeLocal = new DateTime($timezonedate,$dateTimeZoneLocal);
	$dateTimeUTC = new DateTime($timezonedate,$dateTimeZoneUTC);
	$php2 = $dateTimeZoneLocal->getOffset($dateTimeUTC);
	$offset = $php2/3600;
	$localDateTime = $dateTimeLocal->format('Y-m-d h:i A');
//	echo "Information for $timezonedate Date:<br />
// localDateTime: $localDateTime<br />
// UTC Offset on $timezonedate: $offset<br /><br />";

	return $offset;
}
add_action ('time_conversion', 'time_conversion');

