function storeAuditLogData_v3($dataToStore='',$doDebug=FALSE) {

/*	Function to store log data in a flat file

	input:	array of the data to be written
			'logtype' 		=	'STUDENT' 'ADVISOR' 'TESTSTUDENT' 'TESTADVISOR' 'DEMOSTUDENT' DEMOADVISOR'
			'logmode' 		= 'PRODUCTION' 'TESTMODE'
			'logsubtype' 	= 'CLASS' 'ADVISOR' 'STUDENT' 
			'logaction' 	= action taken such as add, update, delete
			'logdate'		= date of the activity yyyy-mm-dd hh:ii
			'logprogram'	= program code of the originating info
			'logwho'		= who did the action
			'logid'			= id of the record that was affected
			data fields affected
			
		For example:	array('logtype'=>'ADVISOR',
							  'logmode'=>'TESTMODE',
							  'logaction'=>'UPDATE',
							  'logsemester'=>'2022 May/Jun',
							  'logdate'=>'2021-05-15 08:11',
							  'logprogam'=>'Cron',
							  'logwho'=>'cron',
							  'logid'=>'543122',
							  'studentcallsign'=>'K7OJL',
							  'studentlastname'=>'Smith',
							  'studentfirstname'=>'Roland',
							  'semester'=>'2021 Sep/Oct',
							  ...
							  );
		Note: Data fields can be in any order
			
	returns: 	an array of two values
				either 'FAIL' or 'SUCCESS'
				a string detailing the failure or success
				
	Can be called from any snippet
				
	Created 20Mar2021 by Roland	
	Seriously updated and revised 15May2021			
*/

	global $wpdb;

	if ($doDebug) {
		ini_set('display_errors','1');
		error_reporting(E_ALL);	
	}

	if ($doDebug) {
		echo "<br />----Arrived at the storelogData_v3 function<br />";
	}

	$initializationArray 		= data_initialization_func();
	$flatFilePath				= $initializationArray['flatFilePath'];
	$currentSemester			= $initializationArray['currentSemester'];
	$prevSemester				= $initializationArray['prevSemester'];

	$dataTypeArray				= array('STUDENT'=>'student',
										'ADVISOR'=>'advisor',
										'TESTSTUDENT'=>'teststudent',
										'TESTADVISOR'=>'testadvisor',
										'DEMOSTUDENT'=>'demostudent',
										'DEMOADVISOR'=>'demoadvisor');
									
// common data fields
	$logtype						= '';
	$logmode						= '';
	$logsubtype						= '';
	$logaction						= '';
	$logdate						= '';
	$logsemester					= '';
	$logprogram						= '';
	$logwho							= '';
	$logid							= '';
	$logsemester					= '';
	$logcallsign					= '';
	$auditLogTableName				= "wpw1_cwa_audit_log";


	// setup default semester
	$defaultSemester		= $currentSemester;
	if ($currentSemester == 'Not in Session') {
		$defaultSemester	= $prevSemester;
	}
	if ($doDebug) {
		echo "defaultSemester set to $defaultSemester<br />
dataToStore array:<br /><pre>";
print_r($dataToStore);
echo "</pre><br />";
	}
	
	// decide if defaultSemester to be used
	if  (array_key_exists('logsemester',$dataToStore)) {
		$logsemester		= $dataToStore['logsemester'];
		if ($doDebug) {
			echo "logsemester exists. Using that: $logsemester<br />";
		}
	} else {
		$logsemester		= $defaultSemester;
		if ($doDebug) {
			echo "Using defaultSemester: $logsemester<br />";
		}
	}
	
	$logsemester			= str_replace("/","-",$logsemester);
	if  (array_key_exists('logtype',$dataToStore)) {
		$logtype		= strtoupper($dataToStore['logtype']);
		if ($doDebug) {
			echo "logtype exists. Using that: $logtype<br />";
		}
	}
	if  (array_key_exists('logmode',$dataToStore)) {
		$logmode		= strtoupper($dataToStore['logmode']);
		if ($doDebug) {
			echo "logmode exists. Using that: $logmode<br />";
		}
	}
	if  (array_key_exists('logsubtype',$dataToStore)) {
		$logsubtype		= strtoupper($dataToStore['logsubtype']);
		if ($doDebug) {
			echo "logsubtype exists. Using that: $logsubtype<br />";
		}
	}
	if  (array_key_exists('logaction',$dataToStore)) {
		$logaction		= strtoupper($dataToStore['logaction']);
		if ($doDebug) {
			echo "logaction exists. Using that: $logaction<br />";
		}
	}
	if  (array_key_exists('logdate',$dataToStore)) {
		$logdate		= $dataToStore['logdate'];
		if ($doDebug) {
			echo "logdate exists. Using that: $logdate<br />";
		}
	}
	if  (array_key_exists('logprogram',$dataToStore)) {
		$logprogram		= strtoupper($dataToStore['logprogram']);
		$logprogram		= substr($logprogram,0,49);
		if ($doDebug) {
			echo "logprogram exists. Using that: $logprogram<br />";
		}
	}
	if  (array_key_exists('logwho',$dataToStore)) {
		$logwho		= strtoupper($dataToStore['logwho']);
		if ($doDebug) {
			echo "logwho exists. Using that: $logwho<br />";
		}
	}
	if  (array_key_exists('logid',$dataToStore)) {
		$logid		= $dataToStore['logid'];
		if ($doDebug) {
			echo "logid exists. Using that: $logid<br />";
		}
	}
	if  (array_key_exists('logcallsign',$dataToStore)) {
		$logcallsign		= strtoupper($dataToStore['logcallsign']);
		if ($doDebug) {
			echo "logcallsign exists. Using that: $logcallsign<br />";
		}
	}
	if ($logcallsign == '' && $logwho != '') {
		$logcallsign				= $logwho;
	}
	
	if (!array_key_exists($logtype,$dataTypeArray)) {
		return array(FALSE,'Invalid log type');
	}
	// build the data to be stored
	$jsonObject						= json_encode($dataToStore);
//	$jsonObject						= addslashes($jsonObject);
	$updateParams					= array();
	$updateParams['logtype']		= $logtype;
	$updateParams['logmode']		= $logmode;
	$updateParams['logsubtype']		= $logsubtype;
	$updateParams['logaction']		= $logaction;
	$updateParams['logdate']		= $logdate;
	$updateParams['logprogram']		= $logprogram;
	$updateParams['logwho']			= $logwho;
	$updateParams['logid']			= $logid;
	$updateParams['logsemester']	= $logsemester;
	$updateParams['logcallsign']	= $logcallsign;
	$updateParams['logdata']		= $jsonObject;
	$updateFormat					= array('%s','%s','%s','%s','%s','%s','%s','%d','%s','%s','%s');
	$result							= $wpdb->insert($auditLogTableName,
													$updateParams,
													$updateFormat);
	if ($result === FALSE) {
		if ($doDebug) {
			echo "insert to $auditLogTableName failed<br />
			      wpdb->last_query: " . $wpdb->last_query . "<br />
			      <b>wpdb->last_error: " . $wpdb->last_error . "</b><br />";
		}
		return array(FALSE,'Insert failed');
	} else {
		if ($doDebug) {
			echo "wpdb->last_query: " . $wpdb->last_query . "<br /><br />";
		}
		return array(TRUE,'data inserted');
	}
}
add_action('storeAuditLogData_v3','storeAuditLogData_v3');
