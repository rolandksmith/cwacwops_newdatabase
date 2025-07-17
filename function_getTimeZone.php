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
	// fill in the timezone ID for specific countries
	$specificCountryArray	= array('Afghanistan'=>'Asia/Kabul',
									'Albania'=>'America/Los_Angeles',
									'Andorra'=>'America/Chicago',
									'Andorra'=>'Europe/Andorra',
									'Argentina'=>'America/Argentina/Buenos_Aires',
									'Austria'=>'Europe/Vienna',
									'Bahamas'=>'America/Nassau',
									'Bahrain'=>'Asia/Bahrain',
									'Belarus'=>'Europe/Minsk',
									'Belgium'=>'Europe/Brussels',
									'Bosnia and Herzegovina'=>'Europe/Sarajevo',
									'Brunei'=>'Asia/Brunei',
									'Bulgaria'=>'Europe/Sofia',
									'Cayman Islands'=>'America/Cayman',
									'Colombia'=>'America/Bogota',
									'Costa Rica'=>'America/Costa_Rica',
									'Croatia'=>'Europe/Zagreb',
									'Cyprus'=>'Asia/Nicosia',
									'Czech Republic'=>'Europe/Prague',
									'Denmark'=>'Europe/Copenhagen',
									'El Salvador'=>'America/El_Salvador',
									'Estonia'=>'Europe/Tallinn',
									'Fiji'=>'Pacific/Fiji',
									'Finland'=>'Europe/Helsinki',
									'France'=>'Europe/Paris',
									'Germany'=>'Europe/Berlin',
									'Greece'=>'Europe/Athens',
									'Guam'=>'Pacific/Guam',
									'Honduras'=>'America/Tegucigalpa',
									'Hong Kong'=>'Asia/Hong_Kong',
									'India'=>'Asia/Kolkata',
									'Iran'=>'Asia/Tehran',
									'Ireland'=>'Europe/Dublin',
									'Isle of Man'=>'Europe/Isle_of_Man',
									'Israel'=>'Asia/Jerusalem',
									'Italy'=>'Europe/Rome',
									'Japan'=>'Asia/Tokyo',
									'Jordan'=>'Asia/Amman',
									'Kazakhstan'=>'Asia/Almaty',
									'Kenya'=>'Africa/Nairobi',
									'Latvia'=>'Europe/Riga',
									'Lithuania'=>'Europe/Vilnius',
									'Macedonia'=>'Europe/Skopje',
									'Malawi'=>'Africa/Blantyre',
									'Malaysia'=>'Asia/Kuala_Lumpur',
									'Mauritius'=>'Indian/Mauritius',
									'Moldova'=>'Asia/Macau',
									'Morocco'=>'Africa/Casablanca',
									'Nepal'=>'Asia/Kathmandu',
									'Netherlands'=>'Europe/Amsterdam',
									'Nigeria'=>'Africa/Lagos',
									'Norway'=>'Europe/Oslo',
									'Pakistan'=>'Asia/Karachi',
									'Peru'=>'America/Lima',
									'Philippines'=>'Asia/Manila',
									'Poland'=>'Europe/Warsaw',
									'Puerto Rico'=>'America/Puerto_Rico',
									'Romania'=>'Europe/Bucharest',
									'Saudi Arabia'=>'Asia/Riyadh',
									'Serbia'=>'Europe/Belgrade',
									'Singapore'=>'Asia/Singapore',
									'Slovakia'=>'Europe/Bratislava',
									'Slovenia'=>'Europe/Ljubljana',
									'South Africa'=>'Africa/Johannesburg',
									'South Korea'=>'Asia/Seoul',
									'Sweden'=>'Europe/Stockholm',
									'Switzerland'=>'Europe/Zurich',
									'Taiwan'=>'Asia/Taipei',
									'Thailand'=>'Asia/Bangkok',
									'Trinidad and Tobago'=>'America/Port_of_Spain',
									'Turkey'=>'Europe/Istanbul',
									'U.S. Virgin Islands'=>'America/St_Thomas',
									'Ukraine'=>'Europe/Kiev',
									'United Kingdom'=>'Europe/London',
									'Vietnam'=>'Asia/Ho_Chi_Minh',
									'Zambia'=>'Africa/Harare');	
	if (array_key_exists($country,$specificCountryArray)) {
		$this_timezone_id	= $specificCountryArray[$country];
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
		if ($state == '') {
			if ($doDebug) {
				echo "if no zip, then state is required<br />";
			}
			$this_timezone_id	= '??';
			goto bypass;
		}
	}
	
	if ($zip != '' && ($country == '' || $country == 'US' || $country == 'United States')) {
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
 		if ($doDebug) {
 			echo "using google maps to find the timezone ID<br />";
 		}
		// use google maps to find the timezone id
		// setup the address
		if ($zip != '') {
			$address	= "$zip,$country";
		} else {
			$address	= "$city,$state,$country";
			if ($doDebug) {
				echo "getting geocoordinates for $address <br />";
			}
		}
		$apiKey = "AIzaSyCGzRL0ROuiIxTaN8oOEZlP6yLsgtRYh-4";
		
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
			if ($status === 'REQUEST_DENIED') {
				$thisErrorStuff	= print_r($geocodeData,TRUE);
				$errorMessage	= "function_getTimeZone returned $status<br /><pre>$thisErrorStuff</pre><br />
getting geocorrdinates for $address";
				sendErrorEmail($errorMessage);
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
				if ($status === 'REQUEST_DENIED') {
					$thisErrorStuff	= print_r($timezoneData,TRUE);
					$errorMessage	= "function_getTimeZone returned $status<br /><pre>$thisErrorStuff</pre><br />
getting geocorrdinates for $address";
					sendErrorEmail($errorMessage);
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
