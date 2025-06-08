function wp_to_local($timeZoneId, $effectiveOffset, $expirationOffset) {

/* Using the system time calculates
		effective as the local time plus the effectiveOffset
		expiration as the effective date plus the expirationOffset
		
		Offsets are in days
	
	input: 	the local timezone ID
			the number of days to offset the effective date as an integer
			the number of days from effective to expiration as an integer
			
	Example: $returnArray = wp_to_local('America/Chicago',0,5);
			
	returns:	array('effective'=> local date and time for the effective date,
					  'expiration'=> local date and time for the expiration date)
					  
				dates are in Y-m-d H:i:s format
	returns FALSE if there is an error
*/

	$doDebug		= TRUE;
	if ($doDebug) {
		echo "<br /><b>In wp_to_local</b> with timeZoneId = $timeZoneId, effectiveOffset = $effectiveOffset, and expirationOffset = $expirationOffset<br />";
	}
	try {
		$localTimeZone 	= new DateTimeZone($timeZoneId);
	} catch (Exception $e) {
		return FALSE;
	}
	$dateTimeLocal 	= new DateTime('now', $localTimeZone);
	$nowDateTime 	= $dateTimeLocal->format('Y-m-d H:i:s');
	$effectiveDateTime	= strtotime("$nowDateTime + $effectiveOffset days");
	$effectiveDate	= date('Y-m-d H:i:s',$effectiveDateTime);
	$expiration 	= strtotime("$effectiveDate + $expirationOffset days");
	$expireDate 	= date('Y-m-d H:i:s',$expiration);

	$returnArray	= array('effective'=>$effectiveDate,'expiration'=>$expireDate);

	if ($doDebug) {
		echo "returnArray:<br /><pre>";
		print_r($returnArray);
		echo "</pre><br />";
	}
	return $returnArray;

}
add_action('wp_to_local','wp_to_local');
