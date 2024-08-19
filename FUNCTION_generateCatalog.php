function generateCatalog($semester='', $testMode=FALSE, $doDebug=FALSE) {

/*	reads the catalog file and builds the catalog array
	returns the catalog array or array(FALSE);
	
	catalogArray 	= level|time|days|count|advisors
	
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

	$sql 						= "select * from $catalogTableName where mode='$catalogMode' and semester='$semester'";
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
				$theCatalog		= $catalogRow->catalog;
				$gotCatalog		= TRUE;
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
	
				$classesArray[]		= "$thisLevel|$thisTime|$thisDays|$thisCount|$thisAdvisors";
			} else {
				$rolandError			= "Catalog has no entries<br />";
				sendErrorEmail($rolandError);
				return array(FALSE,"No catalog entries");
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