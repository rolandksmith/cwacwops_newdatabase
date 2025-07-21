function generateCatalog($semester='', $testMode=FALSE, $doDebug=FALSE) {

/*	reads the catalog file and builds the catalog array
	returns the catalog array or array(FALSE);
	
	catalogArray 	= level|language|time|days|count|advisors
	
	semester must be provided
*/

	global $wpdb;

	if ($semester == '') {
		return array(FALSE,'semester missing');
	}
	
	$classesArray			= array();
	$catalogTableName		= "wpw1_cwa_current_catalog";
	if ($testMode) {
		$catalogMode		= "TestMode";
	} else {
		$catalogMode		= "Production";
	}

	$sql 						= "select * from $catalogTableName 
									where mode='$catalogMode' 
									and semester='$semester'";
	$result						= $wpdb->get_results($sql);
	if ($result === FALSE) {
		if ($doDebug) {
			echo "Reading $catalogTableName table failed<br />";
			echo "wpdb->last_query: " . $wpdb->last_query . "<br />";
			echo "<b>wpdb->last_error: " . $wpdb->last_error . "</b><br />";
		}
		$rolandError			.= "unable to find $catalogTableName table to read the catalog<br />";
		sendErrorEmail($rolandError);
		return array(FALSE,"$catalogTableName missing");
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
			sendErrorEmail($rolandError);
			if ($doDebug) {
				echo "No catalog record found in $catalogTableName table for semester: $inp_semester, mode: $catalogMode<br />";
			}
			return array(FALSE,"No $semester $catalogMode $catalogTableName record found");
		}
	}

	if ($gotCatalog) {
		if ($doDebug) {
			echo "Have a catalog record<br />";
		}
		foreach($theCatalog as $thisLevel => $levelData) {
			foreach($levelData as $thisLanguage => $languageData) {
				foreach($languageData as $thisSched => $scheduleData) {
					$ii = 0;
					$classStr = '';
					$firstTime = TRUE;
					foreach($scheduleData as $classSeq => $thisClass) {
						$ii++;
						if ($firstTime) {
							$firstTime = FALSE;
							$classStr .= $thisClass;
						} else {
							$classStr .= ",$thisClass";
						}
					}
				$schedArray = explode(" ",$thisSched);
				$classesArray[] = "$thisLevel|$thisLanguage|$schedArray[0]|$schedArray[1]|$ii|$classStr";
				}
			}
		}
		if ($doDebug) {
			echo "classesArray:<br /><pre>";
			print_r($classesArray);
			echo "</pre><br />";
		}
		return $classesArray;
	}
}
add_action('generateCatalog','generateCatalog');