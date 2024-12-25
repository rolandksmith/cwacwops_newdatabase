function storeAuditLogData($dataToStore='',$doDebug=FALSE) {

/*	Function to store log data

	input:	array of the data to be written
			'logtype' 		=	'STUDENT' 'ADVISOR' 'CLASS
			'logmode' 		= 'PRODUCTION' 'TESTMODE'
			'logdate'		= date of the activity yyyy-mm-dd hh:ii:ss
			'logprogram'	= program code of the originating info
			'logwho'		= who did the action
			'logsemester'	= semester of the action
			'logcallsign'	= affected student or advisor call sign
			'logsequence'	= sequence number of the class record (0 or omitted for advisor and student)
			'logid'			= id of the record that was affected
			'logdata'		= data fields affected 
			
		For example:	array('logtype'=>'ADVISOR',
							  'logmode'=>'TESTMODE',
							  'logsemester'=>'2022 May/Jun',
							  'logdate'=>'2021-05-15 08:11:04',
							  'logprogam'=>'Cron',
							  'logwho'=>'cron',
							  'logid'=>'543122',
							  'logsequence'=>0, 
							  'logcallsign'=>'K7OJL',
							  'logdata'=>array(field1=>value1,
							                   field2=>value2)
							  );
		Note: Data fields can be in any order
			
	returns: 	an array of two values
				either 'FAIL' or 'SUCCESS'
				a string detailing the failure or success
				
	Can be called from any snippet
				
	Created 20Mar2021 by Roland	
	Seriously updated and revised 15May2021			
	Major modification 3Oct24 by Roland
*/

	global $wpdb;

	if ($doDebug) {
		ini_set('display_errors','1');
		error_reporting(E_ALL);	
	}

	if ($doDebug) {
		echo "<br />----Arrived at the store_audit_log_data function<br />";
	}

//	$initializationArray 		= data_initialization_func();

	$dataTypeArray				= array('STUDENT'=>'student',
										'ADVISOR'=>'advisor',
										'CLASS'=>'class');
									
// common data fields
	$logtype						= 'unknown';
	$logmode						= 'unknown';
	$logdate						= 'unknown';
	$logsemester					= 'unknown';
	$logprogram						= 'unknown';
	$logwho							= 'unknown';
	$logid							= 0;
	$logsequence					= 0;
	$logcallsign					= 'unknown';
	$logdata						= array();
	
	$auditLogTableName				= "wpw1_cwa_audit_log";
	$errorReason					= "";

	$haveError						= FALSE;
	if ($doDebug) {
		echo "dataToStore array:<br /><pre>";
		print_r($dataToStore);
		echo "</pre><br />";
	}
		
	if  (isset($dataToStore['logtype'])) {
		$logtype		= strtoupper($dataToStore['logtype']);
		if ($doDebug) {
			echo "logtype exists. Using that: $logtype<br />";
		}
	} else {
		$errorReason	.= "logtype missing<br />";
		$haveError		= TRUE;
	}
	if  (isset($dataToStore['logmode'])) {
		$logmode		= strtoupper($dataToStore['logmode']);
		if ($doDebug) {
			echo "logmode exists. Using that: $logmode<br />";
		}
	} else {
		$errorReason	.= "logmode missing<br />";
		$haveError		= TRUE;
	}
	if  (isset($dataToStore['logdate'])) {
		$logdate		= $dataToStore['logdate'];
		if ($doDebug) {
			echo "logdate exists. Using that: $logdate<br />";
		}
	} else {
		$errorReason	.= "logdate missing<br />";
		$haveError		= TRUE;
	}
	if  (isset($dataToStore['logprogram'])) {
		$logprogram		= strtoupper($dataToStore['logprogram']);
		$logprogram		= substr($logprogram,0,99);
		if ($doDebug) {
			echo "logprogram exists. Using that: $logprogram<br />";
		}
	} else {
		$errorReason	.= "logprogram missing<br />";
		$haveError		= TRUE;
	}
	if  (isset($dataToStore['logwho'])) {
		$logwho		= strtoupper($dataToStore['logwho']);
		if ($doDebug) {
			echo "logwho exists. Using that: $logwho<br />";
		}
	} else {
		$errorReason	.= "logwho missing<br />";
		$haveError		= TRUE;
	}
	if  (isset($dataToStore['logid'])) {
		$logid		= $dataToStore['logid'];
		if ($doDebug) {
			echo "logid exists. Using that: $logid<br />";
		}
	} else {
		$errorReason	.= "logid missing<br />";
		$haveError		= TRUE;
	}
	if  (isset($dataToStore['logcallsign'])) {
		$logcallsign		= strtoupper($dataToStore['logcallsign']);
		if ($doDebug) {
			echo "logcallsign exists. Using that: $logcallsign<br />";
		}
	} else {
		$errorReason	.= "logcallsign missing<br />";
		$haveError		= TRUE;
	}
	if  (isset($dataToStore['logsequence'])) {
		$logsequence		= $dataToStore['logsequence'];
		if ($doDebug) {
			echo "logsequence exists. Using that: $logsequence<br />";
		}
	} else {
		$errorReason	.= "logsequence missing<br />";
		$haveError		= TRUE;
	}
	if  (isset($dataToStore['logsemester'])) {
		$logsemester		= $dataToStore['logsemester'];
		if ($doDebug) {
			echo "logsemester exists. Using that: $logsemester<br />";
		}
	} else {
		$errorReason	.= "logsemester missing<br />";
		$haveError		= TRUE;
	}
	
	if (isset($dataToStore['logdata'])) {
		$logdata		= $dataToStore['logdata'];
		if ($doDebug) {
			echo "logdata exists. Using that<br />";
		}
	} else {
		$errorReason	.= "logdata missing<br />";
		$haveError		= TRUE;
	}
	
	if ($haveError) {
		return array(FALSE,$errorReason);		
	} else {
		// remove the action log, if found
		if (isset($logdata['action_log'])) {
			unset ($logdata['action_log']);
		}
		// build the data to be stored
		
		$jsonObject						= json_encode($logdata);
		$updateParams					= array('logtype'=> $logtype, 
												'logmode'=> $logmode, 
												'logdate'=> $logdate, 
												'logprogram'=> $logprogram, 
												'logwho'=> $logwho, 
												'logid'=> $logid, 
												'logsemester'=> $logsemester, 
												'logsequence'=>$logsequence, 
												'logcallsign'=> $logcallsign, 
												'logdata'=> $jsonObject);
		$updateFormat					= array('%s','%s','%s','%s','%s','%d','%s','%d','%s','%s');
		$result							= $wpdb->insert($auditLogTableName,
														$updateParams,
														$updateFormat);
		if ($result === FALSE) {
			if ($doDebug) {
				echo "insert to $auditLogTableName failed<br />
					  wpdb->last_query: " . $wpdb->last_query . "<br />
					  <b>wpdb->last_error: " . $wpdb->last_error . "</b><br />";
			}
			$haveError	= TRUE;
			$errorReason	.= "Insert failed<br />";
		} else {
			if ($doDebug) {
				$myInt		= $wpdb->insert_id;
				echo "audit Log data has been stored at record $myInt<br /><br />";
			}
		}
	}
	if ($haveError) {
		return array(FALSE,$errorReason);
	} else {
		return array(TRUE,"");
	}
}
add_action('storeAuditLogData','storeAuditLogData');
