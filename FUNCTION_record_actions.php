function record_actions($inpArray=array()) {

/*	Provides a chronological record of all changes made to the 
	student, advisor, and class tables
	
	Input is an array: (items can be in any order)
		tablename			Name of table being updated
		method				Type of update: add / update / delete
		updateData			array of fields being updated and the update value 
								(stored as a json object)
		tableID				record id of the record affected
		callsign			call sign being affected
		semester			semester to which the record belongs
		who					name or callsign of person making the change
		jobname				name of the job actually making the change
		otherInfo			anything else the update function can add
		
	Table wpw1_cwa_cron_record has these fields
		date_created		Date and time the record was written
		tablename	(VC50)
		method		(VC10)
		update_data	(text)	json object
		table_id	(tinyint)
		callsign	(VC20)
		semester	(VC20)
		who			(VC50)
		jobname		(VC100)
		other_info	(text)
		
		Example:
			$actionData		= array('tablename'=>$studentTableName,
									'method'=>$inp_method,
									'update_data'=>$updateParams,
									'table_id'=>$record_ID,
									'callsign'=>$student_call_sign,
									'semester'=>$student_semester,
									'who'=>$inp_who,
									'jobname'=>$jobname,
									'other_info'=>"record will be deleted this evening");
			$actionResult	= record_actions($actionData);
			if (!$actionResult) {
				if ($doDebug) {
					echo "record_actions failed<br />";
				}
			} else {
			
								 
	Returns TRUE or FALSE	
		Any errors are sent to send_error_email
		
*/

	global $wpdb;
	
	
	$tablename				= '';
	$method					= '';
	$update_data			= array();
	$table_id				= 0;
	$callsign				= '';
	$semester				= '';
	$who					= '';
	$jobname				= '';
	$other_info				= '';
	
	foreach($inpArray as $thisKey => $thisValue) {
		$$thisKey			= $thisValue;
	}
	
//	echo "<br /><b>record_actions</b> table_id: $table_id<br />";
	
	$cronData				= json_encode($update_data);
// echo "update_data:<br /><pre>";
// var_dump($update_data);
// echo "</pre><br />$cronData<br />";
	$myDate					= date('Y-m-d H:i:s');
	$actionResult			= $wpdb->insert('wpw1_cwa_cron_record',
											array('tablename'=>$tablename,
													'date_created'=>$myDate,
													'method'=>$method,
													'update_data'=>$cronData,
													'table_id'=>$table_id,
													'callsign'=>$callsign,
													'semester'=>$semester,
													'who'=>$who,
													'jobname'=>$jobname,
													'other_info'=>$other_info),
											array('%s','%s','%s','%s','%d','%s',
												  '%s','%s','%s','%s'));
	if ($actionResult === FALSE) {
		$myError			= $wpdb->last_error;
		$mySQL				= $wpdb->last_query;
		$result				= sendErrorEmail("RecordActions $jobname insert failed. SQL: $mySQL. Error: $myError");
		return FALSE;
	}
	return TRUE;
}
add_action('record_actions','record_actions');