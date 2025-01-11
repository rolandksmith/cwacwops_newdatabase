function getTimeZone($inp_data) {

/*	call with array 
		$inp_data		= array('city'=>city,
								'state'=>state,
								'zip'=>zipcode,
								'country'=>country,
								'doDebug'=>doDebug);						

	Modified 8Jan25 by Roland to use array input
*/

	$city				= '';
	$state				= '';
	$zip				= '';
	$country			= '';
	$doDebug			= FALSE;
	$address			= '';
	$this_timezone_id	= '';
	
	foreach($inp_data as $thisKey => $thisData) {
		$$thisKey		= $thisData;
	}
	

	if ($doDebug) {
		echo "<br /><b>getTimeZone</b> with inp_data<br /><pre>";
		print_r($inp_data);
		echo "</pre><br />";
	}
	$doProceed			= TRUE;
	if ($country == '') {
		if ($doDebug) {
			echo "country is required<br />";
		}
		$this_timezone_id 	= '??';
		goto bypass;
	}
	
	if ($zip == '') {
		if ($city == '') {
			if ($doDebug) {
				echo "if no zip, then city is required<br />";
			}
			$this_timezone_id	= '??';
			goto bypass;
		}
		if ($state = '') {
			if ($doDebug) {
				echo "if no zip, then state is required<br />";
			}
			$this_timezone_id	= '??';
			goto bypass;
		}
	}
	
	if ($country == 'US' || $country == 'United States') {
		if ($zip != '') {
			if ($doDebug) {
				echo "looking up US zip code of $zip<br />";
			}
			$zipResult		= getOffsetFromZipCode($zip,'',TRUE,FALSE,$doDebug);
			if ($zipResult[0] == 'NOK') {
				$this_timezone_id		="??";
			} else {
				$this_timezone_id		= $zipResult[1];
			}
		} else {
			$this_timezone_id			= '??';
		}
 	} else {
		// user google maps to find the timezone id
		// setup the address
		if ($zip != '') {
			$address	= "$zip,$country";
		} else {
			if ($city = '' && $state != '') {
				$address	= "$state,$country";
			} elseif ($city != '' & $state = '') {
				$address	= "$city,$country";
			} else {
				$this_timezone_id	= '??';
				goto bypass;
			}
		}

		$apiKey = "AIzaSyBWoxQBzlt9ITgrhOkDe8yrxGlEYQ01wgI";
		
		// Geocoding request
		$geocodeUrl = "https://maps.googleapis.com/maps/api/geocode/json?address=" . urlencode($address) . "&key=" . $apiKey;
		$geocodeResponse = file_get_contents($geocodeUrl);
		$geocodeData = json_decode($geocodeResponse, true);
		
		$status		= $geocodeData['status'];
		if ($status != 'OK') {
			if ($doDebug) {
				echo "geocoding returned $status<br />";
				echo "geocodeData:<br /><pre>";
				print_r($geocodeData);
				echo "</pre><br />";
			}
			$this_timezone_id	= '??';
		} else {
		
			// Extract latitude and longitude
			$lat = $geocodeData['results'][0]['geometry']['location']['lat'];
			$lng = $geocodeData['results'][0]['geometry']['location']['lng'];
			
			if ($doDebug) {
				echo "extracted lat: $lat lng: $lng<br />";
			}
			
			// Timezone request
			$timezoneUrl = "https://maps.googleapis.com/maps/api/timezone/json?location=" . $lat . "," . $lng . "&timestamp=" . time() . "&key=" . $apiKey;
			$timezoneResponse = file_get_contents($timezoneUrl);
			$timezoneData = json_decode($timezoneResponse, true);
			
			$status		= $timezoneData['status'];
			if ($status != 'OK') {
				if ($doDebug) {
					echo "timezoneData returned $status<br />";
					echo "timezoneData:<br /><pre>";
					print_r($timezoneData);
					echo "</pre><br />";
				}
				$this_timezone_id	= '??';
			} else {
			
				// Extract IANA timezone ID
				$this_timezone_id = $timezoneData['timeZoneId'];
			
				if ($doDebug) {		
					echo "this_timezone_id: $this_timezone_id<br />"; 
				}
			}
		}
	}
	bypass:
	return $this_timezone_id;
}
add_action('getTimeZone','getTimeZone');
