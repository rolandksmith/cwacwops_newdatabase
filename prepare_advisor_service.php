function prepare_advisor_service() {
	global $wpdb;
	$filename = "wpw1_cwa_advisor_service";
	ini_set('max_execution_time',0);
	set_time_limit(0);
	$sql = "select * from $filename 
			order by advisor, semester";
	$result = $wpdb->get_results($sql);
	if ($result === FALSE) {
		echo "selecting from $filename failed<br />";
	} else {
		$numRows = $wpdb->num_rows;
		echo "ran $sql<br />and retrieved $numRows rows<br />";
		if ($numRows > 0) {
			$fixes				= 0;
			foreach($result as $resultRow) {
				$record_id		= $resultRow->record_id;
				$advisor		= $resultRow->advisor;
				$semester		= $resultRow->semester;
				$classes		= $resultRow->classes;
				$date_written	= $resultRow->date_written;


				$myArray 		= explode(" ",$semester);
				if (str_contains($myArray[0],'/')) {
					$thisYear		= $myArray[1];
					$thisMonths		= $myArray[0];
					$newSemester	= "$thisYear $thisMonths";
					$result			= $wpdb->update('wpw1_cwa_advisor_service',
													array('semester'=>$newSemester),
													array('record_id'=>$record_id),
													array('%s'),
													array('%d'));
					if ($result === FALSE) {
						$lastSQL = $wpdb->last_query;
						$lastError = $wpdb->last_error;
						echo "attempting to write $newSemester to id $record_id failed<br />
							  last SQL: $lastSQL<br />
							  last error: $lastError<br />";
					} else {
						$fixes++;
						echo "updated $advisor $semester to $newSemester<br />";
					}
				}
			}
		}
	}
	echo "$fixes fixes<br />";
	return "all done";
}
add_shortcode('prepare_advisor_service','prepare_advisor_service');