function getOffsetFromZipcode($inp_zip='',$inp_semester='',$fuzzy=FALSE,$testMode=FALSE,$doDebug=FALSE) {

/*	returns the timezone id and the utc offset based on a US zipcode
	
	Input:	zipcode
			semester
			
	returns	array(status,timezone_id,UTC offset,matchMsg)
	
		status: OK or NOK
		if NOK matchMsg will have the error
*/

		global $wpdb;
		
		$thisDate			= date('Y-m-d');
		
		if ($doDebug) {
			echo "<br />At function getOffsetFromZipcode with inp_zip: $inp_zip and inp_semester=$inp_semester<br />";
		}
		if ($inp_zip == '' || $inp_semester == '') {
			return array('NOK','','','One or more inputs empty');
		}


		// get the timezone info based on the zipcode
		$gotTheCode			= FALSE;
		$myZip				= substr($inp_zip,0,5);
		$zipcodeData		= $wpdb->get_results("select timezone from wpw1_cwa_timezonebyzipcode where zip='$myZip'");
		if ($zipcodeData === FALSE) {
			return array('NOK','','','Database Error');
		} else {
			$numRows		= $wpdb->num_rows;
			if ($numRows > 0) {
				foreach($zipcodeData as $thisRow) {
					$zipTimeZone	= $thisRow->timezone;
					if ($doDebug) {
						echo "Retrieved timezone of $zipTimeZone for inp_zip $myZip<br />";
					}
					$gotTheCode		= TRUE;
				}			
			} else {
				if ($fuzzy) {			/// do a fuzzy search and return whatever comes up first
					$fuzzyZip	= substr($myZip,0,4);
					$fuzzyZip	= "$fuzzyZip%";
					if ($doDebug) {
						echo "Attempting a fuzzy search with fuzzyZip: $fuzzyZip<br />";
					}
					$fuzzycodeData		= $wpdb->get_results("select * from wpw1_cwa_timezonebyzipcode where zip like '$fuzzyZip' limit 1");
					if ($fuzzycodeData !== FALSE) {
						$numRows		= $wpdb->num_rows;
						if ($numRows > 0) {
							foreach($fuzzycodeData as $thisRow) {
								$thiszip		= $thisRow->zip;
								$thisCity		= $thisRow->city;
								$thisCounty		= $thisRow->county;
								$thisState		= $thisRow->state;
								$thisCountry	= $thisRow->country;
								$zipTimeZone	= $thisRow->timezone;
								$thisQuality	= $thisRow->addressquality;
								$thisSource		= $thisRow->source;
								$thisSourceDate	= $thisRow->sourcedate;
								if ($doDebug) {
									echo "Retrieved timezone of $zipTimeZone based on fuzzy search for $fuzzyZip<br />";
								}
								$gotTheCode 	= TRUE;
								$addResult		= $wpdb->insert('wpw1_cwa_timezonebyzipcode',
																array('zip'=>$myZip,
																	  'city'=>'',
																	  'county'=>$thisCounty,
																	  'state'=>$thisState,
																	  'country'=>$thisCountry,									
																	  'timezone'=>$zipTimeZone,
																	  'addressquality'=>'',
																	  'source'=>'CW Academy',
																	  'sourcedate'=>$thisDate),
																array('%s','%s','%s','%s','%s','%s','%s','%s','%s'));
								if ($addResult === FALSE) {
									$myStr		= $wpdb->last_error;
									$errorMsg	= "Attempting to add $myZip to wpw1_cwa_timezonebyzipcode failed: $myStr";
									sendErrorEmail($errorMsg);
								} else {
									if ($doDebug) {
										echo "Added $myZip to database<br />";
									}
								}
							}
						}
					}
				}
			}
		}
		if (!$gotTheCode) {
			if ($doDebug) {
				echo "Attempting to get timezone for zipcode $myZip failed<br />";
			}
			$matchMsg		= "Getting timezone_id based on zipcode $myZip failed. ";
			return array('NOK','','',$matchMsg);
		}
		// have the timezone_id. Now figure out the UTC offset for the student's semester
		$myArray			= explode(" ",$inp_semester);
		$thisYear			= $myArray[0];
		$thisMonDay			= $myArray[1];
		$myConvertArray		= array('Jan/Feb'=>'-01-01','May/Jun'=>'-05-01','Sep/Oct'=>'-09-01','JAN/FEB'=>'-01-01','APR/MAY'=>'-04-01','MAY/JUN'=>'-05-01','SEP/OCT'=>'-09-01','Apr/May'=>'-04-01');
		$myMonDay			= $myConvertArray[$thisMonDay];
		$thisNewDate		= "$thisYear$myMonDay 00:00:00";
		if ($doDebug) {
			echo "converted $inp_semester to $thisNewDate<br />";
		}
		$dateTimeZoneLocal 	= new DateTimeZone($zipTimeZone);
		$dateTimeZoneUTC 	= new DateTimeZone("UTC");
		$dateTimeLocal 		= new DateTime($thisNewDate,$dateTimeZoneLocal);
		$dateTimeUTC		= new DateTime($thisNewDate,$dateTimeZoneUTC);
		$php2 				= $dateTimeZoneLocal->getOffset($dateTimeUTC);
		$offset 			= $php2/3600;
		if ($doDebug) {
			echo "UTC offset for $zipTimeZone is $offset hours<br />";
		}
		$matchMsg			= "Using $zipTimeZone the UTC offset for $inp_semester is $offset hours. ";

		return array('OK',$zipTimeZone,$offset,$matchMsg);	
	}
add_action ('getOffsetFromZipcode', 'getOffsetFromZipcode');