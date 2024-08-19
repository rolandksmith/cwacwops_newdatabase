function copy_data_between_current_and_backup_tables_func($atts) {
/*	
	Copy data to backup tables or from backup tables to current tables

	Created 25Jan2022 by Roland
	Modified 20Jul23 by Roland to use consolidated tables
*/
	
	$doDebug						= FALSE;

	$runTheJob						= TRUE;
	
	$initializationArray = data_initialization_func();
	if ($doDebug) {
		echo "Initialization Array:<br /><pre>";
		print_r($initializationArray);
		echo "</pre><br />";
	}
	$validUser = $initializationArray['validUser'];
	$userName  = $initializationArray['userName'];
	$siteURL   = $initializationArray['siteurl'];
	$versionNumber = '1';
	
	// get any attributes
	$attrib		= '';
	$attributes = shortcode_atts(
			array(
			   'attrib' => '',
			 ), 
			$atts
		);
		
	$attrib		= $attributes['attrib'];
	if ($doDebug) {
		if ($attrib == '') {
			echo "attrib is blank<br />";
		} else {
			echo "Retrieved $attrib from attrib<br />";
		}
	}
	
	
/// get the time that the process started
	$startingMicroTime			= microtime(TRUE);

	ini_set('memory_limit','256M');
	ini_set('max_execution_time',0);
	set_time_limit(0);
//	if ($doDebug) {
		ini_set('display_errors','1');
		error_reporting(E_ALL);	
//	}

	$strPass					= "1";
	$inp_request				= '';
	$theURL						= "$siteURL/cwa-copy-data-between-current-and-backup-tables/";
	$inp_resetpod				= '';
	$studentCount				= 0;
	$advisorCount				= 0;
	$classCount					= 0;
	$evaluateAdvisorCount		= 0;
	$advisorDeletedCount		= 0;
	$classDeletedCount			= 0;
	$studentDeletedCount		= 0;
	$assessmentCount			= 0;
	$auditCount					= 0;
	$catalogCount				= 0;
	$reportsCount				= 0;
	$eventsCount				= 0;
	$increment					= 0;
	$jobname					= "Copy Data Between Curent and Backup Tables V$versionNumber";
	$copyCount					= 1000;
	$copyStudent_to_Student2							= FALSE;
	$copyStudent2_to_Student							= FALSE;
	$copyAdvisor_to_Advisor2							= FALSE;
	$copyAdvisor2_to_Advisor							= FALSE;
	$copyAdvisorClass_to_AdvisorClass2					= FALSE;
	$copyAdvisorClass2_to_AdvisorClass					= FALSE;
	$copyEvaluate_to_Evaluate2							= FALSE;
	$copyEvaluate2_to_Evaluate							= FALSE;
	$copyAdvisorDeleted_to_AdvisorDeleted2				= FALSE;
	$copyAdvisorDeleted2_to_AdvisorDeleted				= FALSE;
	$copyAdvisorClassDeleted_to_AdvisorClassDeleted2	= FALSE;	
	$copyTempData_to_TempData2							= FALSE;
	$copyAdvisorClassDeleted2_to_AdvisorClassDeleted	= FALSE;
	$copyStudentDeleted_to_StudentDeleted2				= FALSE;
	$copyStudentDeleted2_to_StudentDeleted				= FALSE;
	$copyAssessment_to_Assessment2						= FALSE;
	$copyAssessment2_to_Assessment						= FALSE;
	$copyAuditLog_to_AuditLog2							= FALSE;
	$copyAuditLog2_to_AuditLog							= FALSE;
	$copyCatalog_to_Catalog2							= FALSE;
	$copyCatalog2_to_Catalog							= FALSE;
	$copyReports_to_Reports2							= FALSE;
	$copyReports2_to_Reports							= FALSE;
	$copyReplacementRequests_to_ReplacementRequests2	= FALSE;
	$copyReplacementRequests2_to_ReplacementRequests	= FALSE;
	$copyReminders_to_reminders2						= FALSE;
	$copyReminders2_to_reminders						= FALSE;
	$copyEmails_to_emails2								= FALSE;
	$copyEmails2_to_emails								= FALSE;
	$copyTempData2_to_TempData							= FALSE;

// get the input information
	if (isset($_REQUEST)) {
		foreach($_REQUEST as $str_key => $str_value) {
			if ($doDebug) {
				echo "Key: $str_key | Value: $str_value <br />\n";
			}
			if ($str_key 		== "strpass") {
				$strPass		 = $str_value;
				$strPass		 = filter_var($strPass,FILTER_UNSAFE_RAW);
			}
			if ($str_key		== "inp_request") {
				$inp_request	 = $str_value;
			}
			if ($str_key		== "inp_verbose") {
				$inp_verbose	 = $str_value;
				if ($inp_verbose == 'Yes') {
					$doDebug	= TRUE;
				}
			}
		}
	}
	
	if ($attrib == "allcurrent") {
		$inp_request			= 'allcurrent';
		$strPass				= "2";
	} else {
		if ($validUser == "N") {
			return "YOU'RE NOT AUTHORIZED!<br />Goodby";
		}
	}
	
	
	$content = "<style type='text/css'>
fieldset {font:'Times New Roman', sans-serif;color:#666;background-image:none;
background:#efefef;padding:2px;border:solid 1px #d3dd3;}

legend {font:'Times New Roman', sans-serif;color:#666;font-weight:bold;
font-variant:small-caps;background:#d3d3d3;padding:2px 6px;margin-bottom:8px;}

label {font:'Times New Roman', sans-serif;font-weight:bold;line-height:normal;
text-align:left;margin-right:10px;position:relative;display:block;float:left;width:400px;}

textarea.formInputText {font:'Times New Roman', sans-serif;color:#666;
background:#fee;padding:2px;border:solid 1px #f66;margin-right:5px;margin-bottom:5px;}

textarea.formInputText:focus {color:#000;background:#ffffff;border:solid 1px #006600;}

textarea.formInputText:hover {color:#000;background:#ffffff;border:solid 1px #006600;}

input.formInputText {color:#666;background:#fee;padding:2px;
border:solid 1px #f66;margin-right:5px;margin-bottom:5px;}

input.formInputText:focus {color:#000;background:#ffffff;border:solid 1px #006600;}

input.formInputText:hover {color:#000;background:#ffffff;border:solid 1px #006600;}

input.formInputFile {color:#666;background:#fee;padding:2px;border:
solid 1px #f66;margin-right:5px;margin-bottom:5px;height:20px;}

input.formInputFile:focus {color:#000;background:#ffffff;border:solid 1px #006600;}

select.formSelect {color:#666;background:#fee;padding:2px;
border:solid 1px #f66;margin-right:5px;margin-bottom:5px;cursor:pointer;}

select.formSelect:hover {color:#333;background:#ccffff;border:solid 1px #006600;}

input.formInputButton {vertical-align:middle;font-weight:bolder;
text-align:center;color:#300;background:#f99;padding:1px;border:solid 1px #f66;
cursor:pointer;position:relative;float:left;}

input.formInputButton:hover {color:#f8f400;}

input.formInputButton:active {color:#00ffff;}

tr {color:#333;background:#eee;}

table{font:'Times New Roman', sans-serif;background-image:none;}

th {color:#ffff;background-color:#000;padding:5px;font-size:small;}

td {padding:5px;font-size:small;}
</style>";	

	if ("1" == $strPass) {
		if ($doDebug) {
			echo "Function starting.<br />";
		}
		$content 		.= "<h3>Select Desired Copy and Submit</h3>
<p><form method='post' action='$theURL' 
name='selection_form' ENCTYPE='multipart/form-data'>
<fieldset>
<legend>Indicate which copy should be made</legend>
<input type='hidden' name='strpass' value='2'>
<table>
<tr><td><b>Batch Backup and Restore</b></td></tr>
<tr><td>
	<input type='radio' class='formInputButton' id='primary' name='inp_request' value='primary' checked>
	<label for='primary'>Advisor, AdvisorClass, Student, TempData to Backup Tables</label></td></tr>
<tr><td>
	<input type='radio' class='formInputButton' id='allcurrent' name='inp_request' value='allcurrent'>
	<label for='allcurrent'>All Current Tables to Backup Tables</label></td></tr>
<tr><td><hr></td></tr>	
<tr><td>
	<input type='radio' class='formInputButton' id='backup' name='inp_request' value='backup'>
	<label for='backup'>Backup Advisor, AdvisorClass, Student and TempData to Current Tables</label></td></tr>
<tr><td>
	<input type='radio' class='formInputButton' id='allbackup' name='inp_request' value='allbackup'>
	<label for='allbackup'>All Backup Tables to Current Tables</label></td></tr>
<tr><td><hr></td></tr>	
<tr><td><b>Current Table Backups</b></td></tr>
<tr><td>
	<input type='radio' class='formInputButton' id='advisor' name='inp_request' value='advisor'>
	<label for='advisor'>Consolidated Advisor table to backup table</label></td></tr>
<tr><td>
	<input type='radio' class='formInputButton' id='advisorclass' name='inp_request' value='advisorclass'>
	<label for='advisorclass'>Consolidated AdvisorClass table to backup table</label></td></tr>
<tr><td>
	<input type='radio' class='formInputButton' id='student' name='inp_request' value='student'>
	<label for='student'>Consolidated Student table to backup table</label></td></tr>
<tr><td>
	<input type='radio' class='formInputButton' id='tempdata' name='inp_request' value='tempdata'>
	<label for='tempdata'>TempData table to backup table</label></td></tr>
<tr><td>
	<input type='radio' class='formInputButton' id='advisordeleted' name='inp_request' value='advisordeleted'>
	<label for='advisordeleted'>Advisor Deleted table to backupo table</label><br /></td></tr>
<tr><td>
	<input type='radio' class='formInputButton' id='advisorclassdeleted' name='inp_request' value='advisorclassdeleted'>
	<label for='advisorclassdeleted'>AdvisorClass Deleted table to backup table</label><br /></td></tr>
<tr><td>
	<input type='radio' class='formInputButton' id='studentdeleted' name='inp_request' value='studentdeleted'>
	<label for='studentdeleted'>Student Deleted table to backup table</label><br /></td></tr>
<tr><td>
	<input type='radio' class='formInputButton' id='audioAssessment' name='inp_request' value='audioAssessment'>
	<label for='audioAssessment'>Audio Assessment to backup table</label><br /></td></tr>
<tr><td>
	<input type='radio' class='formInputButton' id='ReplacementRequests' name='inp_request' value='ReplacementRequests'>
	<label for='audioAssessment'>Replacement Requests to backup table</label><br /></td></tr>
<tr><td>
	<input type='radio' class='formInputButton' id='evaluate' name='inp_request' value='evaaluate'>
	<label for='evaluate'>Evaluate Advisor table to backup table</label></td></tr>
<tr><td>
	<input type='radio' class='formInputButton' id='auditlog' name='inp_request' value='auditlog'>
	<label for='auditlog'>Audit Log table to backup table</label><br /></td></tr>
<tr><td>
	<input type='radio' class='formInputButton' id='reports' name='inp_request' value='reports'>
	<label for='reports'>Reports table to backup table</label><br /></td></tr>
<tr><td>
	<input type='radio' class='formInputButton' id='catalog' name='inp_request' value='catalog'>
	<label for='catalog'>Catalog table to backup table</label><br /></td></tr>
<tr><td>
	<input type='radio' class='formInputButton' id='email' name='inp_request' value='email'>
	<label for='catalog'>Email table to backup table</label><br /></td></tr>
<tr><td>
	<input type='radio' class='formInputButton' id='reminders' name='inp_request' value='reminders'>
	<label for='reminders'>Reminders table to backup table</label><br /></td></tr>
<tr><td><hr></td></tr>
<tr><td><b>Backup Tables to Current Tables</b></td></tr>
<tr><td>
	<input type='radio' class='formInputButton' id='advisor2' name='inp_request' value='advisor2'>
	<label for='advisor2'>Backup Advisor table to Current table</label></td></tr>
<tr><td>
	<input type='radio' class='formInputButton' id='advisorclass2' name='inp_request' value='advisorclass2'>
	<label for='advisorclass2'>Backup AdvisorClass table to Current table</label></td></tr>
<tr><td>
	<input type='radio' class='formInputButton' id='student2' name='inp_request' value='student2'>
	<label for='student2'>Backup Student table to Current table</label></td></tr>
<tr><td>
	<input type='radio' class='formInputButton' id='tempdata2' name='inp_request' value='tempdata2'>
	<label for='tempdata'>TempData2 table to Current table</label></td></tr>
<tr><td>
	<input type='radio' class='formInputButton' id='advisordeleted2' name='inp_request' value='advisordeleted2'>
	<label for='advisordeleted2'>Backup Advisor Deleted table to Currento table</label><br /></td></tr>
<tr><td>
	<input type='radio' class='formInputButton' id='advisorclassdeleted2' name='inp_request' value='advisorclassdeleted2'>
	<label for='advisorclassdeleted2'>Backup AdvisorClass Deleted table to Current table</label><br /></td></tr>
<tr><td>
	<input type='radio' class='formInputButton' id='studentdeleted2' name='inp_request' value='studentdeleted2'>
	<label for='studentdeleted2'>Backup Student Deleted table to Current table</label><br /></td></tr>
<tr><td>
	<input type='radio' class='formInputButton' id='audioAssessment2' name='inp_request' value='audioAssessment2'>
	<label for='audioAssessment2'>Backup Audio Assessment to Current table</label><br /></td></tr>
<tr><td>
	<input type='radio' class='formInputButton' id='ReplacementRequests2' name='inp_request' value='ReplacementRequests2'>
	<label for='audioAssessment'>Replacement Requests2 to Current table</label><br /></td></tr>
<tr><td>
	<input type='radio' class='formInputButton' id='evaluate2' name='inp_request' value='evaaluate2'>
	<label for='evaluate2'>Backup Evaluate Advisor table to Current table</label></td></tr>
<tr><td>
	<input type='radio' class='formInputButton' id='auditlog2' name='inp_request' value='auditlog2'>
	<label for='auditlog2'>Backup Audit Log table to Current table</label><br /></td></tr>
<tr><td>
	<input type='radio' class='formInputButton' id='reports2' name='inp_request' value='reports2'>
	<label for='reports2'>Backup Reports table to Current table</label><br /></td></tr>
<tr><td>
	<input type='radio' class='formInputButton' id='catalog2' name='inp_request' value='catalog2'>
	<label for='catalog2'>Backup Catalog table to Current table</label><br /></td></tr>
<tr><td>
	<input type='radio' class='formInputButton' id='email2' name='inp_request' value='email2'>
	<label for='catalog'>Email2 table to current table</label><br /></td></tr>
<tr><td>
	<input type='radio' class='formInputButton' id='reminders' name='inp_request' value='reminders2'>
	<label for='reminders'>Reminders2 table to current table</label><br /></td></tr>
<tr><td><hr></tr></td>
<tr><td>
	Verbose Output?<br />
	<input type='radio' class='formInputButton' id='inp_verbose' name='inp_verbose' value='No' checked='checked'> No<br />
	<input type='radio' class='formInputButton' id='inp_verbose' name='inp_verbose' value='Yes'> Yes</td></tr></table>
<input class='formInputButton' type='submit' value='Submit' />
</fieldset>
</form>";

///// Pass 2 -- do the work

	} elseif ("2" == $strPass) {
	
		global $wpdb;
	
//		$runTheJob				= TRUE;
		if ($userName == '') {
			$checkBegin = strtotime('12:50:00');
			$checkEnd = strtotime('13:30:00');
			$thisTime = date('H:i:s');
			$nowTime = strtotime($thisTime);
			if ($nowTime >= $checkBegin && $nowTime <= $checkEnd) {
				$runTheJob = TRUE;
			} else {
				$runTheJob = FALSE;
				if ($doDebug) {
					echo "runTheJob is FALSE<br />";
				}
				$theRecipient	= 'rolandksmith@gmail.com';
				$theSubject		= 'CW Academy - Cron Triggered';
				$theContent		= "The Daily Copy Cron was triggered at $thisTime. It did not run.";
				$mailCode		= 16;
				$mailResult		= emailFromCWA_v2(array('theRecipient'=>$theRecipient,
															'theSubject'=>$theSubject,
															'jobname'=>$jobname,
															'theContent'=>$theContent,
															'mailCode'=>$mailCode,
															'increment'=>$increment,
															'testMode'=>FALSE,
															'doDebug'=>$doDebug));
			}
		}
		if ($runTheJob) {

			$doContinue				= FALSE;
		
			if ($doDebug) {
				echo "<br />at pass 2<br />
						inp_request: $inp_request<br />
						Setting Logicals<br />";
			}

			$copyStudent_to_Student2							= FALSE;
			$copyStudent2_to_Student							= FALSE;
			$copyAdvisor_to_Advisor2							= FALSE;
			$copyAdvisor2_to_Advisor							= FALSE;
			$copyAdvisorClass_to_AdvisorClass2					= FALSE;
			$copyAdvisorClass2_to_AdvisorClass					= FALSE;
			$copyTempData_to_TempData2							= FALSE;
			$copyTempData2_to_TempData							= FALSE;
			$copyEvaluate_to_Evaluate2							= FALSE;
			$copyEvaluate2_to_Evaluate							= FALSE;
			$copyAdvisorDeleted_to_AdvisorDeleted2				= FALSE;
			$copyAdvisorDeleted2_to_AdvisorDeleted				= FALSE;
			$copyAdvisorClassDeleted_to_AdvisorClassDeleted2	= FALSE;	
			$copyAdvisorClassDeleted2_to_AdvisorClassDeleted	= FALSE;
			$copyStudentDeleted_to_StudentDeleted2				= FALSE;
			$copyStudentDeleted2_to_StudentDeleted				= FALSE;
			$copyAssessment_to_Assessment2						= FALSE;
			$copyAssessment2_to_Assessment						= FALSE;
			$copyAuditLog_to_AuditLog2							= FALSE;
			$copyAuditLog2_to_AuditLog							= FALSE;
			$copyCatalog_to_Catalog2							= FALSE;
			$copyCatalog2_to_Catalog							= FALSE;
			$copyReports_to_Reports2							= FALSE;
			$copyReports2_to_Reports							= FALSE;
			$copyReplacementRequests_to_ReplacementRequests2	= FALSE;
			$copyReplacementRequests2_to_ReplacementRequests	= FALSE;
			$copyReminders_to_Reminders2						= FALSE;
			$copyReminders2_to_Reminders						= FALSE;
			$copyEmail_to_Email2								= FALSE;
			$copyEmail2_to_Email								= FALSE;
		
			if ($doDebug) {
				echo "initial logicals set. Setting group logicals<br />";
			}

			if ($inp_request == "allcurrent") {
				$copyStudent_to_Student2							= TRUE;
				$copyAdvisor_to_Advisor2							= TRUE;
				$copyAdvisorClass_to_AdvisorClass2					= TRUE;
				$copyTempData_to_TempData2							= TRUE;
				$copyEvaluate_to_Evaluate2							= TRUE;
				$copyAdvisorDeleted_to_AdvisorDeleted2				= TRUE;
				$copyAdvisorClassDeleted_to_AdvisorClassDeleted2	= TRUE;	
				$copyStudentDeleted_to_StudentDeleted2				= TRUE;
				$copyAssessment_to_Assessment2						= TRUE;
				$copyReplacementRequests_to_ReplacementRequests2	= TRUE;
				$copyAuditLog_to_AuditLog2							= TRUE;
				$copyCatalog_to_Catalog2							= TRUE;
				$copyReports_to_Reports2							= TRUE;
				$copyReminders_to_reminders2						= FALSE;
				$copyEmails_to_emails2								= FALSE;
				$doContinue											= TRUE;
			} elseif ($inp_request == "primary") {
				$copyStudent_to_Student2							= TRUE;
				$copyAdvisor_to_Advisor2							= TRUE;
				$copyAdvisorClass_to_AdvisorClass2					= TRUE;
				$copyTempData_to_TempData2							= TRUE;
				$doContinue											= TRUE;
			} elseif ($inp_request == "backup") {
				$copyStudent2_to_Student							= TRUE;
				$copyAdvisor2_to_Advisor							= TRUE;
				$copyAdvisorClass2_to_AdvisorClass					= TRUE;
				$copyTempData2_to_TempData							= TRUE;
				$doContinue											= TRUE;
			} elseif ($inp_request 		== "allbackup") {
				$copyStudent2_to_Student							= TRUE;
				$copyAdvisor2_to_Advisor							= TRUE;
				$copyAdvisorClass2_to_AdvisorClass					= TRUE;
				$copyTempData2_to_TempData							= TRUE;
				$copyEvaluate2_to_Evaluate							= TRUE;
				$copyAdvisorDeleted2_to_AdvisorDeleted				= TRUE;
				$copyAdvisorClassDeleted2_to_AdvisorClassDeleted	= TRUE;
				$copyStudentDeleted2_to_StudentDeleted				= TRUE;
				$copyAssessment2_to_Assessment						= TRUE;
				$copyReplacementRequests2_to_ReplacementRequests	= TRUE;
				$copyAuditLog2_to_AuditLog							= TRUE;
				$copyCatalog2_to_Catalog							= TRUE;
				$copyReports2_to_Reports							= TRUE;
				$copyReminders2_to_reminders						= FALSE;
				$copyEmails2_to_emails								= FALSE;
				$doContinue											= TRUE;
			} elseif ($inp_request 		== "advisor") {
				$copyAdvisor_to_Advisor2							= TRUE;
				$doContinue											= TRUE;
			} elseif ($inp_request		== "advisorclass") {
				$copyAdvisorClass_to_AdvisorClass2					= TRUE;
				$doContinue											= TRUE;
			} elseif ($inp_request		== "student") {
				$copyStudent_to_Student2							= TRUE;
				$doContinue											= TRUE;
			} elseif ($inp_request		== "tempdata") {
				$copyTempData_to_TempData2							= TRUE;
				$doContinue											= TRUE;
			} elseif ($inp_request		== "advisordeleted") {
				$copyAdvisorDeleted_to_AdvisorDeleted2				= TRUE;
				$doContinue											= TRUE;
			} elseif ($inp_request		== "advisorclassdeleted") {
				$copyAdvisorClassDeleted_to_AdvisorClassDeleted2	= TRUE;
				$doContinue											= TRUE;
			} elseif ($inp_request		== "studentdeleted") {
				$copyStudentDeleted_to_StudentDeleted2				= TRUE;
				$doContinue											= TRUE;
			} elseif ($inp_request		== "audioassessment") {
				$copyAssessment_to_Assessment2						= TRUE;
				$doContinue											= TRUE;
			} elseif ($inp_request		== "ReplacementRequests") {
				$copyReplacementRequests_to_ReplacementRequests2		= TRUE;
				$doContinue											= TRUE;
			} elseif ($inp_request		== "evaluate") {
				$copyEvaluate_to_Evaluate2							= TRUE;
				$doContinue											= TRUE;
			} elseif ($inp_request		== "auditlog") {
				$copyAuditLog_to_AuditLog2							= TRUE;
				$doContinue											= TRUE;
			} elseif ($inp_request		== "reports") {
				$copyReports_to_Reports2							= TRUE;
				$doContinue											= TRUE;
			} elseif ($inp_request		== "catalog") {
				$copyCatalog_to_Catalog2							= TRUE;
				$doContinue											= TRUE;
			} elseif ($inp_request		== "email") {
				$copyEmail_to_Email2								= TRUE;
				$doContinue											= TRUE;
			} elseif ($inp_request		== "reminders") {
				$copyReminders_to_Reminders2						= TRUE;
				$doContinue											= TRUE;

			} elseif ($inp_request 		== "advisor2") {
				$copyAdvisor_to_Advisor2							= TRUE;
				$doContinue											= TRUE;
			} elseif ($inp_request		== "advisorclass2") {
				$copyAdvisorClass2_to_AdvisorClass					= TRUE;
				$doContinue											= TRUE;
			} elseif ($inp_request		== "student2") {
				$copyStudent2_to_Student							= TRUE;
				$doContinue											= TRUE;
			} elseif ($inp_request		== "tempdata2") {
				$copyTempData2_to_TempData							= TRUE;
				$doContinue											= TRUE;
			} elseif ($inp_request		== "advisordeleted2") {
				$copyAdvisorDeleted2_to_AdvisorDeleted				= TRUE;
				$doContinue											= TRUE;
			} elseif ($inp_request		== "advisorclassdeleted2") {
				$copyAdvisorClassDeleted2_to_AdvisorClassDeleted	= TRUE;
				$doContinue											= TRUE;
			} elseif ($inp_request		== "studentdeleted2") {
				$copyStudentDeleted2_to_StudentDeleted				= TRUE;
				$doContinue											= TRUE;
			} elseif ($inp_request		== "audioassessment2") {
				$copyAssessment2_to_Assessment						= TRUE;
				$doContinue											= TRUE;
			} elseif ($inp_request		== "ReplacementRequests2") {
				$copyReplacementRequests2_to_ReplacementRequests		= TRUE;
				$doContinue											= TRUE;
			} elseif ($inp_request		== "evaluate2") {
				$copyEvaluate2_to_Evaluate							= TRUE;
				$doContinue											= TRUE;
			} elseif ($inp_request		== "auditlog2") {
				$copyAuditLog2_to_AuditLog							= TRUE;
				$doContinue											= TRUE;
			} elseif ($inp_request		== "reports2") {
				$copyReports2_to_Reports							= TRUE;
				$doContinue											= TRUE;
			} elseif ($inp_request		== "catalog2") {
				$copyCatalog2_to_Catalog							= TRUE;
				$doContinue											= TRUE;
			} elseif ($inp_request		== "email2") {
				$copyEmail2_to_Email								= TRUE;
				$doContinue											= TRUE;
			} elseif ($inp_request		== "reminders2") {
				$copyReminders2_to_Reminders						= TRUE;
				$doContinue											= TRUE;
			}				
			
			$copyArray					= array();
			if ($copyAdvisor_to_Advisor2) {
				$copyArray[]			= "wpw1_cwa_consolidated_advisor|wpw1_cwa_consolidated_advisor2";
				if ($doDebug) {
					echo "copyAdvisor_to_Advisor2 true<br />";
				}
			}	
			if ($copyAdvisorClass_to_AdvisorClass2) {
				$copyArray[]			= "wpw1_cwa_consolidated_advisorclass|wpw1_cwa_consolidated_advisorclass2";
				if ($doDebug) {
					echo "copyAdvisorClass_to_AdvisorClass2 true<br />";
				}
			}	
			if ($copyStudent_to_Student2) {
				$copyArray[]			= "wpw1_cwa_consolidated_student|wpw1_cwa_consolidated_student2";
				if ($doDebug) {
					echo "copyStudent_to_Student2 true<br />";
				}
			}
			if ($copyTempData_to_TempData2) {
				$copyArray[]			= "wpw1_cwa_temp_data|wpw1_cwa_temp_data2";
				if ($doDebug) {
					echo "copyTempData_to_TempData2 true<br />";
				}
			}
			if ($copyAdvisorDeleted_to_AdvisorDeleted2) {
				$copyArray[]			= "wpw1_cwa_advisor_deleted|wpw1_cwa_advisor_deleted2";
				if ($doDebug) {
					echo "copyAdvisorDeleted_to_AdvisorDeleted2 true<br />";
				}
			}
			if ($copyAdvisorClassDeleted_to_AdvisorClassDeleted2) {
				$copyArray[]			= "wpw1_cwa_advisorclass_deleted|wpw1_cwa_advisorclass_deleted2";
				if ($doDebug) {
					echo "copyAdvisorClassDeleted_to_AdvisorClassDeleted2 is true<br />";
				}
			}
			if ($copyStudentDeleted_to_StudentDeleted2) {
				$copyArray[]			= "wpw1_cwa_student_deleted|wpw1_cwa_student_deleted2";
				if ($doDebug) {
					echo "copyStudentDeleted_to_StudentDeleted2 is true<br />";
				}
			}
			if ($copyAssessment_to_Assessment2) {
				$copyArray[]			= "wpw1_cwa_audio_assessment|wpw1_cwa_audio_assessment2";
				if ($doDebug) {
					echo "copyAssessment_to_Assessment2 is true<br />";
				}
			}
			if ($copyReplacementRequests_to_ReplacementRequests2) {
				$copyArray[]			= "wpw1_cwa_replacement_requests|wpw1_cwa_replacement_requests2";
				if ($doDebug) {
					echo "copyReplacementRequests_to_ReplacementRequests2 is true<br />";
				}
			}
			if ($copyEvaluate_to_Evaluate2) {
				$copyArray[]			= "wpw1_cwa_evaluate_advisor|wpw1_cwa_evaluate_advisor2";
				if ($doDebug) {
					echo "copyEvaluate_to_Evaluate2 is true<br />";
				}
			}
			if ($copyAuditLog_to_AuditLog2) {
				$copyArray[]			= "wpw1_cwa_audit_log|wpw1_cwa_audit_log2";
				if ($doDebug) {
					echo "copyAuditLog_to_AuditLog2 is true<br />";
				}
			}
			if ($copyReports_to_Reports2) {
				$copyArray[]			= "wpw1_cwa_reports|wpw1_cwa_reports2";
				if ($doDebug) {
					echo "copyReports_to_Reports2 is true<br />";
				}
			}
			if ($copyCatalog_to_Catalog2) {
				$copyArray[]			= "wpw1_cwa_current_catalog|wpw1_cwa_current_catalog2";
				if ($doDebug) {
					echo "copyCatalog_to_Catalog2 is true<br />";
				}
			}
			if ($copyEmail_to_Email2) {
				$copyArray[]			= "wpw1_cwa_production_email|wpw1_cwa_production_email2";
				if ($doDebug) {
					echo "copyEmail_to_Email2 is true<br />";
				}
			}
			if ($copyReminders_to_Reminders2) {
				$copyArray[]			= "wpw1_cwa_reminders|wpw1_cwa_reminders2";
				if ($doDebug) {
					echo "copyReminders_to_Reminders2 is true<br />";
				}
			}
			if ($copyAdvisor2_to_Advisor) {
				$copyArray[]			= "wpw1_cwa_consolidated_advisor2|wpw1_cwa_consolidated_advisor";
				if ($doDebug) {
					echo "copyAdvisor2_to_Advisor true<br />";
				}
			}
			if ($copyAdvisorClass2_to_AdvisorClass) {
				$copyArray[]			= "wpw1_cwa_consolidated_advisorclass2|wpw1_cwa_consolidated_advisorclass";
				if ($doDebug) {
					echo "copyAdvisorClass2_to_AdvisorClass is true<br />";
				}
			}
			if ($copyStudent2_to_Student) {
				$copyArray[]			= "wpw1_cwa_consolidated_student2|wpw1_cwa_consolidated_student";
				if ($doDebug) {
					echo "copyStudent2_to_Student true<br />";
				}
			}
			if ($copyTempData2_to_TempData) {
				$copyArray[]			= "wpw1_cwa_temp_data2|wpw1_cwa_temp_data";
				if ($doDebug) {
					echo "copyTempData2_to_TempData true<br />";
				}
			}
			if ($copyAdvisorDeleted2_to_AdvisorDeleted) {
				$copyArray[]			= "wpw1_cwa_advisor_deleted2|wpw1_cwa_advisor_deleted";
				if ($doDebug) {
					echo "copyAdvisorDeleted2_to_AdvisorDeleted true<br />";
				}
			}
			if ($copyAdvisorClassDeleted2_to_AdvisorClassDeleted) {
				$copyArray[]			= "wpw1_cwa_advisorclass_deleted2|wpw1_cwa_advisorclass_deleted";
				if ($doDebug) {
					echo "copyAdvisorClassDeleted2_to_AdvisorClassDeleted is true<br />";
				}
			}
			if ($copyStudentDeleted2_to_StudentDeleted) {
				$copyArray[]			= "wpw1_cwa_student_deleted2|wpw1_cwa_student_deleted";
				if ($doDebug) {
					echo "copyStudentDeleted2_to_StudentDeleted is true<br />";
				}
			}
			if ($copyAssessment2_to_Assessment) {
				$copyArray[]			= "wpw1_cwa_audio_assessment2|wpw1_cwa_audio_assessment";
				if ($doDebug) {
					echo "copyAssessment2_to_Assessment is true<br />";
				}
			}
			if ($copyReplacementRequests2_to_ReplacementRequests) {
				$copyArray[]			= "wpw1_cwa_replacement_requests2|wpw1_cwa_replacement_requests";
				if ($doDebug) {
					echo "copyReplacementRequests2_to_ReplacementRequests is true<br />";
				}
			}
			if ($copyEvaluate2_to_Evaluate) {
				$copyArray[]			= "wpw1_cwa_evaluate_advisor2|wpw1_cwa_evaluate_advisor";
				if ($doDebug) {
					echo "copyEvaluate2_to_Evaluate is true<br />";
				}
			}
			if ($copyAuditLog2_to_AuditLog) {
				$copyArray[]			= "wpw1_cwa_audit_log2|wpw1_cwa_audit_log";
				if ($doDebug) {
					echo "copyAuditLog2_to_AuditLog is true<br />";
				}
			}
			if ($copyReports2_to_Reports) {
				$copyArray[]			= "wpw1_cwa_reports2|wpw1_cwa_reports";
				if ($doDebug) {
					echo "copyReports2_to_Reports is true<br />";
				}
			}
			if ($copyCatalog2_to_Catalog) {
				$copyArray[]			= "wpw1_cwa_current_catalog2|wpw1_cwa_current_catalog";
				if ($doDebug) {
					echo "copyCatalog2_to_Catalog is true<br />";
				}
			}
			if ($copyEmail2_to_Email) {
				$copyArray[]			= "wpw1_cwa_production_email2|wpw1_cwa_production_email";
				if ($doDebug) {
					echo "copyEmail2_to_Email is true<br />";
				}
			}
			if ($copyReminders2_to_Reminders) {
				$copyArray[]			= "wpw1_cwa_reminders2|wpw1_cwa_reminders";
				if ($doDebug) {
					echo "copyReminders2_to_Reminders is true<br />";
				}
			}
			
			if ($doDebug) {
				echo "<br />copyArray:<br /><pre>";
				print_r($copyArray);
				echo "</pre><br />";
			}

		
			if ($doContinue) {
				echo "<h3>$jobname</h3>";
				foreach ($copyArray as $myValue) {
					$myArray			= explode("|",$myValue);
					$sourceTable		= $myArray[0];
					$destinationTable	= $myArray[1];
						echo "<p>Copying $sourceTable to $destinationTable<br />";

					// truncate the destination table
					$result				= $wpdb->query("TRUNCATE $destinationTable");
					if ($result === FALSE) {
						echo "Truncating $destinationTable failed<br />
								Result: $result<br />
								wpdb->last_query: " . $wpdb->last_query . "<br />";
							if ($wpdb->last_error != '') {
								echo "<b>wpdb->last_error: " . $wpdb->last_error . "</b><br />";
							}
					} else {
						echo "Successfully truncated $destinationTable<br />";
			
						//// can proceed with the copy
						$result			= $wpdb->query("insert into $destinationTable select * from $sourceTable");
						if ($result === FALSE) {
							echo "Copying from $sourceTable to $destinationTable failed<br />
									Result: $result<br />
									wpdb->last_query: " . $wpdb->last_query . "<br />";
								if ($wpdb->last_error != '') {
									echo "<b>wpdb->last_error: " . $wpdb->last_error . "</b><br />";
								}
						} else {
							$sql		= "select count(*) from $destinationTable";
							$myInt		= $wpdb->get_var($sql);
							echo "Successfully copied from $sourceTable to $destinationTable<br />
								  $destinationTable has $myInt records<br />";
						}
					}
				}
			}			
		}
	}
	if ($runTheJob) {
		$content		.= "<br /><a href='$theURL'>Do it Again</a>";
		$thisTime 		= date('Y-m-d H:i:s');
		$content 		.= "<br /><br /><a href='$siteURL/program-list/'>Return to Student Portal</a>
							<br /><br /><p>V$versionNumber. Prepared at $thisTime</p>";
		$endingMicroTime = microtime(TRUE);
		$elapsedTime	= $endingMicroTime - $startingMicroTime;
		$elapsedTime	= number_format($elapsedTime, 4, '.', ',');
		$content		.= "<p>Report pass $strPass took $elapsedTime seconds to run</p>";
		$nowDate		= date('Y-m-d');
		$nowTime		= date('H:i:s');
		$thisStr			= 'Production';
		$ipAddr			= get_the_user_ip();
		$result			= write_joblog_func("$jobname|$nowDate|$nowTime|$userName|Time|$thisStr|$strPass: $elapsedTime|$ipAddr");
		if ($result == 'FAIL') {
			$content	.= "<p>writing to joblog.txt failed</p>";
		}
		return $content;
	}
}
add_shortcode ('copy_data_between_current_and_backup_tables', 'copy_data_between_current_and_backup_tables_func');
