function program_list_func() {


	global $wpdb;

	$doDebug						= FALSE;
	$testMode						= FALSE;
	$initializationArray = data_initialization_func();
	if ($doDebug) {
		echo "Initialization Array:<br /><pre>";
		print_r($initializationArray);
		echo "</pre><br />";
	}
	$validUser 			= $initializationArray['validUser'];
	$userName			= $initializationArray['userName'];
	$userRole			= $initializationArray['userRole'];
	$currentTimestamp	= $initializationArray['currentTimestamp'];
	$siteURL			= $initializationArray['siteurl'];
	$strPass			= "0";
	$currentSemester	= $initializationArray['currentSemester'];
	$nextSemester		= $initializationArray['nextSemester'];
	$semesterTwo		= $initializationArray['semesterTwo'];
	$semesterThree		= $initializationArray['semesterThree'];
	$semesterFour		= $initializationArray['semesterFour'];
	
//	CHECK THIS!								//////////////////////
//	if ($validUser == "N") {
//		return "YOU'RE NOT AUTHORIZED!<br />Goodby";
//	}

//	ini_set('memory_limit','256M');
//	ini_set('max_execution_time',0);
//	set_time_limit(0);

//	if ($doDebug) {
		ini_set('display_errors','1');
		error_reporting(E_ALL);	
//	}

/// get the time that the process started
	$startingMicroTime			= microtime(TRUE);


// get the input information
	if (isset($_REQUEST)) {
// echo "<br />REQUEST is set<br />";
		foreach($_REQUEST as $str_key => $str_value) {
			if ($doDebug) {
				if (!is_array($str_value)) {
					echo "Key: $str_key | Value: $str_value <br />\n";
				} else {
					echo "Key: $str_key (array)<br />\n";
				}
			}
			if ($str_key 		== "validUser") {
				$validUser		 = $str_value;
				$validUser		 = filter_var($validUser,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "userName") {
				$userName		 = $str_value;
				$userName		 = filter_var($userName,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "userRole") {
				$userRole		 = $str_value;
				$userRole		 = filter_var($userRole,FILTER_UNSAFE_RAW);
			}
		}
	}


	
	$content = "<style type='text/css'>
fieldset {font:'Times New Roman', sans-serif;color:#666;background-image:none;
background:#efefef;padding:2px;border:solid 1px #d3dd3;}

legend {font:'Times New Roman', sans-serif;color:#666;font-weight:bold;
font-variant:small-caps;background:#d3d3d3;padding:2px 6px;margin-bottom:8px;}

label {font:'Times New Roman', sans-serif;font-weight:bold;line-height:normal;
text-align:right;margin-right:10px;position:relative;display:block;float:left;width:150px;}

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

table{font:'Times New Roman', sans-serif;background-image:none;border-collapse:collapse;}

th {color:#ffff;background-color:#000;padding:5px;font-size:small;}

td {padding:5px;font-size:small;}

th:first-child,
td:first-child {
 padding-left: 10px;
}

th:last-child,
td:last-child {
	padding-right: 5px;
}
</style>";	

	if ($testMode) {
		echo "<br /><b>Operating in TestMode</b><br />";
		$studentTableName		= 'wpw1_cwa_consolidated_student2';
		$advisorTableName		= 'wpw1_cwa_consolidated_advisor2';
	} else {
		$studentTableName		= 'wpw1_cwa_consolidated_student';
		$advisorTableName		= 'wpw1_cwa_consolidated_advisor';
	}


if ($userRole == 'administrator') {
	$userName		= strtoupper($userName);
	$returnInfo		= display_reminders('administrator',$userName,$doDebug);
	if ($returnInfo[0] === FALSE) {
		if ($doDebug) {
			echo "<br /><b>display_reminders</b> returned $returnInfo[1]<br />";
		}
	} else {
		if ($doDebug) {
			echo "<br /><b>display_reminders</b> returned $returnInfo[1]<br /><hr><br />";
		}
	
		$content	.= "<h3>Administrator Menu for $userName</h3>
			<div class='refresh' data-callsign='$userName' data-role='administrator' data-url='/wp-content/uploads/refreshapi.php' data-seconds='60'>
			$returnInfo[1]
			</div>
			<table style='width:1200px;'>
			<tr><td style='width:33%;vertical-align:top;'>
			<h4>Alphabetical Program List</h4>
			<ul>
			<li><a href='$siteURL/cwa-student-management/?strpass=40' target='_blank'>Add Unassigned Student to an Advisor's Class</a>
			<li><a href='$siteURL/cwa-advisor-and-advisorclass-report-generator/' target='_blank'>Advisor and AdvisorClass Report Generator</a>
			<li><a href='$siteURL/cwa-advisor-class-history/' target='_blank'>Advisor Class History</a>
			<li><a href='$siteURL/cwa-advisor-class-reminder/' target='_blank'>Advisor Class Reminder</a>
			<li><a href='$siteURL/cwa-advisor-class-report/' target='_blank'>Advisor Class Report</a>
			<li><a href='$siteURL/cwa-advisor-play-cw-audio-clips/' target='_blank'>Advisor Play CW Audio Clips</a>
			<li><a href='$siteURL/cwa-advisor-registration/' target='_blank'>Advisor Registration</a>
			<li><a href='$siteURL/cwa-advisor-request-student-assessment/' target='_blank'>Order Morse Code Proficiency Assessments</a>
			<li><a href='$siteURL/cwa-advisor-report-generator/' target='_blank'>Advisor Report Generator</a>
			<li><a href='$siteURL/cwa-advisor-statistics/' target='_blank'>Advisor Statistics</a>
			<li><a href='$siteURL/cwa-advisor-verification-of-student/' target='_blank'>Advisor Verification of Student</a>
			<li><a href='$siteURL/cwa-advisorclass-report-generator/' target='_blank'>AdvisorClass Report Generator</a>
			<li><a href='$siteURL/cwa-assign-students-to-advisors-v3/' target='_blank'>Assign Students to Advisors V3</a>
			<li><a href='$siteURL/cwa-student-management/?strpass=90' target='_blank'>Assign a Student to an Advisor regardless</a>
			<li><a href='$siteURL/cwa-automatic-student-backfill-v1/' target='_blank'>Automatic Student Backfill</a>
			<li><a href='$siteURL/bad-actors/' target='_blank'>Bad Actors</a>
			<li><a href='$siteURL/cwa-check-student-status/' target='_blank'>Check Student Status</a>
			<li><a href='$siteURL/cwa-student-management/?strpass=100' target='_blank'>Confirm One or More Students</a>
			<li><a href='$siteURL/cwa-copy-current-data-to-test-tables/' target='_blank'>Copy Current Student and Advisor Data to Test Tables</a>
			<li><a href='$siteURL/cwa-copy-data-between-current-and-backup-tables/' target='_blank'>Copy Data Between Current and Backup Tables</a>
			<li><a href='$siteURL/cwa-daily-advisor-cron-process/' target='_blank'>Daily Advisor Cron Process</a>
			<li><a href='$siteURL/cwa-daily-catalog-cron-process/' target='_blank'>Daily Catalog Cron Process</a>
			<li><a href='$siteURL/cwa-daily-cron-process/' target='_blank'>Daily Cron Process</a>
			<li><a href='$siteURL/cwa-daily-student-cron/' target='_blank'>Daily Student Cron</a>
			<li><a href='$siteURL/cwa-decline-student-reassignment/' target='_blank'>Decline Student Reassignment</a>
			<li><a href='$siteURL/cwa-student-management/?strpass=5' target='_blank'>Delete Student's Pre-assigned Advisor</a>
			<li><a href='$siteURL/cwa-delete_user_info/' target='_blank'>Delete User Info</a>
			<li><a href='$siteURL/cwa-detailed-history-for-an-advisor/' target='_blank'>Detailed History for an Advisor</a>
			<li><a href='$siteURL/cwa-display-advisor-classes/' target='_blank'>Display Advisor Classes</a>
			<li><a href='$siteURL/cwa-display-advisor-evaluation-statistics/' target='_blank'>Display Advisor Evaluation Statistics</a>
			<li><a href='$siteURL/cwa-display-all-advisors/' target='_blank'>Display All Advisors</a>
			<li><a href='$siteURL/cwa-display-all-students/' target='_blank'>Display All Students</a>
			<li><a href='$siteURL/cwa-display-catalog-for-a-timezone/' target='_blank'>Display Catalog for a Timezone</a>
			<li><a href='$siteURL/cwa-display-chronological-action-log/' target='_blank'>Display Chronological Action Log</a>
			<li><a href='$siteURL/cwa-display-evaluations-for-an-advisor/' target='_blank'>Display Evaluations for an Advisor</a>
			<li><a href='$siteURL/cwa-display-initialization-array/' target='_blank'>Display Initialization Array</a>
			<li><a href='$siteURL/cwa-display-recent-reminders/' target='_blank'>Display Recent Reminders</a>
			<li><a href='$siteURL/cwa-display-replacement-requests/' target='_blank'>Display Replacement Requests</a>
			<li><a href='$siteURL/cwa-display-saved-report/' target='_blank'>Display Saved Report</a>
			<li><a href='$siteURL/cwa-display-student-evaluation-of-advisors/' target='_blank'>Display Student Evaluation of Advisors</a>
			<li><a href='$siteURL/cwa-display-student-history/' target='_blank'>Display Student History</a>
			<li><a href='$siteURL/cwa-display-and-update-advisor-information/' target='_blank'>Display and Update Advisor Information</a>
			<li><a href='$siteURL/cwa-display-and-update-student-information/' target='_blank'>Display and Update Student Information</a>
			<li><a href='$siteURL/cwa-display-users-program-list/' target='_blank'>Display Users Program List (FAKE)<a>
			<li><a href='$siteURL/cwa-end-of-semester-student-assessment/' target='_blank'>End of Semester Student Assessment</a>
			<li><a href='$siteURL/end-of-semester-student-self-assessment/' target='_blank'>End of Semester Student Self Assessment</a>
			<li><a href='$siteURL/cwa-evaluate-student/' target='_blank'>Evaluate Student</a>
			<li><a href='$siteURL/cwa-student-management/?strpass=20' target='_blank'>Exclude an Advisor from being Assigned to a Student</a>
			<li><a href='$siteURL/cwa-student-management/?strpass=70' target='_blank'>Find Possible Classes for a Student</a>
			<li><a href='$siteURL/cwa-student-management/?strpass=80' target='_blank'>Find Possible Students for an Advisor's Class</a>
			<li><a href='$siteURL/cwa-gather-and-display-student-statistics/' target='_blank'>Gather and Display Student Statistics</a>
			<li><a href='$siteURL/cwa-generate-advisor-overall-statistics/' target='_blank'>Generate Advisor Overall Statistics</a>
			<li><a href='$siteURL/cwa-generic-updater/' target='_blank'>Generic Updater</a>
			<li><a href='$siteURL/cwa-how-students-progressed-report/' target='_blank'>How Students Progressed Report</a>
			<li><a href='$siteURL/cwa-legends-codes-and-things/' target='_blank'>Legends Codes and Things</a>
			<li><a href='$siteURL/cwa-list-advisor…h-all-s-students/' target='_blank'>List Advisors With All S Students
			<li><a href='$siteURL/cwa-list-advisors-with-s-students/' target='_blank'>List Advisors With S Students</a>
			<li><a href='$siteURL/cwa-list-advisors-with-incomplete-evaluations/' target='_blank'>List Advisors With Incomplete Evaluations</a>
			<li><a href='$siteURL/cwa-list-all-pages/' target='_blank'>List All Pages</a>
			<li><a href='$siteURL/cwa-list-all-students/' target='_blank'>List All Students</a>
			<li><a href='$siteURL/cwa-list-new-registrations/' target='_blank'>List New Registrations</a>
			<li><a href='$siteURL/cwa-list-past-advisors-registration-info/' target='_blank'>List Past Advisors Registration Info</a>
			<li><a href='$siteURL/cwa-list-student-responders/' target='_blank'>List Student Responders</a>
			<li><a href='$siteURL/cwa-list-student-self-assessments/' target='_blank'>List Student Self Assessments</a>
			<li><a href='$siteURL/cwa-student-management/?strpass=7' target='_blank'>List Students Needing Intervention</a>
			<li><a href='$siteURL/cwa-list-students-for-a-semester/' target='_blank'>List Students for a Semester</a>
			<li><a href='$siteURL/cwa-maintenance-mode/' target='_blank'>Maintenance Mode</a>
			<li><a href='$siteURL/cwa-manage-advisor-class-assignments/?strpass=20' target='_blank'>Manage Advisor Class Assignments (fake)</a>
			<li><a href='$siteURL/cwa-manage-directory/' target='_blank'>Manage Directory</a>
			<li><a href='siteURL/cwa-manage-reminders/' target='_blank'>Manage Reminders</a>
			<li><a href='siteURL/cwa-manage-temp-data/' target='_blank'>Manage Temp Data</a>
			<li><a href='$siteURL/cwa-move-student-to-different-semester/' target='_blank'>Move Student to Different Semester</a>
			<li><a href='$siteURL/cwa-student-management/?strpass=35' target='_blank'>Move Student to a Different Level and Unassign</a>
			<li><a href='$siteURL/cwa-move-unassigned-students-to-next-semester/' target='_blank'>Move Unassigned Students to Next Semester</a>
			<li><a href='$siteURL/cwa-advisor-request-student-assessment/' target='_blank'>Order Morse Code Proficiency Assessments</a>
			<li><a href='$siteURL/cwa-student-management/?strpass=30' target='_blank'>Override Excluded Advisor</a>
			<li><a href='$siteURL/cwa-perform-assessment/' target='_blank'>Perform Assessment</a>
			<li><a href='$siteURL/cwa-student-management/?strpass=2' target='_blank'>Pre-assign Student to an Advisor</a>
			<li><a href='$siteURL/cwa-practice-assessment/' target='_blank'>Practice Assessment</a>
			<li><a href='$siteURL/cwa-prepare-advisors-for-student-assignments/' target='_blank'>Prepare Advisors for Student Assignments</a>
			<li><a href='$siteURL/cwa-prepare-students-for-assignment-to-advisors/' target='_blank'>Prepare Students for Assignment to Advisors</a>
			<li><a href='$siteURL/cwa-process-advisor-verification/' target='_blank'>Process Advisor Verification</a>
			<li><a href='$siteURL/program-list/' target='_blank'>Program List</a>
			<li><a href='$siteURL/cwa-promotable-students-repeating/' target='_blank'>Promotable Students Repeating</a>
			<li><a href='$siteURL/cwa-push-advisor-class/' target='_blank'>Push Advisor Class</a>
			<li><a href='$siteURL/cwa-student-management/?strpass=55' target='_blank'>Re-assign a Student to Another Advisor</a>
			<li><a href='$siteURL/cwa-recover-deleted-record/' target='_blank'>Recover Deleted Record</a>
			<li><a href='siteURL/cwa-remove-item/' target='_blank'>Remove Item</a>
			<li><a href='$siteURL/cwa-student-management/?strpass=60' target='_blank'>Remove and/or Unassign a Student</a>
			<li><a href='$siteURL/cwa-repeating-student-statistics/' target='_blank'>Repeating Student Statistics</a>
			<li><a href='$siteURL/cwa-student-management/?strpass=25' target='_blank'>Resolve Student Hold</a>
			<li><a href='siteURL/cwa-search-audio-assessment-log/' target='_blank'>Search Audio Assessment Log</a>
			<li><a href='$siteURL/cwa-search-audit-log/' target='_blank'>Search Audit Log</a>
			<li><a href='$siteURL/cwa-search-saved-email-by-call-sign/' target='_blank'>Search Saved Email by Call Sign</a>
			<li><a href='$siteURL/cwa-search-sent-email-by-callsign-or-email/' target='_blank'>Search Sent Email by Callsign or Email</a>
			<li><a href='$siteURL/cwa-search-tracking-data/' target='_blank'>Search Tracking Data</a>
			<li><a href='$siteURL/cwa-select-students-for-end-of-semester-assessment/' target='_blank'>Select Students for End of Semester Assessment</a>
			<li><a href='$siteURL/cwa-send-advisor-email-to-view-student-evaluations/' target='_blank'>Send Advisor Email to View Student Evaluations</a>
			<li><a href='siteURL/cwa-send-an-email/' target='_blank'>Send an Email</a>
			<li><a href='$siteURL/cwa-send-congratulations-email-to-students/' target='_blank'>Send Congratulations Email to Students</a>
			<li><a href='$siteURL/cwa-send-email-to-student-to-evaluate-advisor/' target='_blank'>Send Email to Student to Evaluate Advisor</a>
			<li><a href='$siteURL/cwa-send-end-of-semester-assessment-email-to-advisors/' target='_blank'>Send End of Semester Assessment Email to Advisors</a>
			<li><a href='$siteURL/cwa-send-evaluation-email-to-advisors/' target='_blank'>Send Evaluation Email to Advisors</a>
			<li><a href='$siteURL/cwa-send-mid-term-verification-email/' target='_blank'>Send Mid-term Verification Email</a>
			<li><a href='$siteURL/cwa-show-advisor-class-assignments/' target='_blank'>Show Advisor Class Assignments</a>
			<li><a href='$siteURL/cwa-show-detailed-history-for-student/' target='_blank'>Show Detailed History for Student</a>
			<li><a href='$siteURL/cwa-show-saved-email/' target='_blank'>Show Saved Email</a>
			<li><a href='$siteURL/cwa-student-and-advisor-color-chart-v2/' target='_blank'>Student and Advisor Color Chart V2</a>
			<li><a href='$siteURL/cwa-student-evaluation-of-advisor/' target='_blank'>Student Evaluation of Advisor</a>
			<li><a href='$siteURL/cwa-student-management/' target='_blank'>Student Management</a>
			<li><a href='$siteURL/cwa-student-management/' target='_blank'>Student Management</a>
			<li><a href='$siteURL/cwa-student-progression-report/' target='_blank'>Student Progression Report</a>
			<li><a href='$siteURL/cwa-student-registration/' target='_blank'>Student Registration</a>
			<li><a href='$siteURL/cwa-student-report-generator/' target='_blank'>Student Report Generator</a>
			<li><a href='$siteURL/cwa-student-and-advisor-assignments/' target='_blank'>Student and Advisor Assignments (#1 Report)</a>
			<li><a href='$siteURL/cwa-student-and-advisor-color-chart/' target='_blank'>Student and Advisor Color Chart</a>
			<li><a href='$siteURL/cwa-thank-you-remove/' target='_blank'>Thank You Remove</a>
			<li><a href='$siteURL/cwa-thank-you-yes/' target='_blank'>Thank You Yes</a>
			<li><a href='$siteURL/cwa-update-callsign/' target='_blank'>Update Callsign</a>
			<li><a href='$siteURL/cwa-user-administration/' target='_blank'>User Administration (Take Over Acct)
			<li><a href='$siteURL/utility-show-offsets-for-a-country-or-zip-code/' target='_blank'>UTILITY Show Offsets for a Country or Zip Code</a>
			<li><a href='$siteURL/utility-calculate-utc/' target='_blank'>UTILITY: Calculate UTC</a>
			<li><a href='$siteURL/cwa-student-management/?strpass=50' target='_blank'>Unassign a Student Regardless of Status</a>
			<li><a href='$siteURL/cwa-update-unassigned-student-information/' target='_blank'>Update Unassigned Student Information</a>
			<li><a href='$siteURL/cwa-verify-advisor-class/' target='_blank'>Verify Advisor Class</a>
			<li><a href='$siteURL/cwa-student-management/?strpass=85' target='_blank'>Verify One or More Students</a>
			<li><a href='$siteURL/cwa-verify-temp-data/' target='_blank'>Verify Temp Data</a>
			<li><a href='$siteURL/cwa-view-a-student-cw-assessment-v2/' target='_blank'>View a Student CW Assessment V2</a>
			</ul>

			<td style='width:33%;vertical-align:top;'>
			<h4>Frequently Used Functions</h4>
			<ul>
			<li><a href='$siteURL/wp-admin/index.php' target='_blank'>Dashboard<br />
			<li><a href='$siteURL/wp-login.php/?action=logout'>Logout<br ><br />
			<li><a href='$siteURL/cwa-student-management/?strpass=2' target='_blank'>Pre-assign Student to an Advisor</a>
			<li><a href='$siteURL/cwa-automatic-student-backfill-v1/' target='_blank'>Automatic Student Backfill</a>
			<li><a href='siteURL/cwa-manage-reminders/' target='_blank'>Manage Reminders</a>
			<li><a href='$siteURL/cwa-student-and-advisor-color-chart-v2/' target='_blank'>Student and Advisor Color Chart V2</a><br /><br />
			<li><a href='$siteURL/cwa-display-users-program-list/' target='_blank'>Display Users Program List (FAKE)<a>
			<li><a href='$siteURL/cwa-user-administration/' target='_blank'>User Administration (Take Over Acct)
			<li><a href='$siteURL/cwa-manage-advisor-class-assignments/?strpass=20' target='_blank'>Manage Advisor Class Assignments (Fake)</a>
			<li><a href='$siteURL/cwa-student-registration/' target='_blank'>Student Registration (Fake)</a>
			<li><a href='$siteURL/cwa-advisor-class-history/' target='_blank'>Advisor Class History</a>
			<li><a href='$siteURL/cwa-show-detailed-history-for-student/' target='_blank'>Show Detailed History for Student</a>
			<li><a href='siteURL/cwa-manage-temp-data/' target='_blank'>Manage Temp Data</a>
			<li><a href='$siteURL/cwa-display-recent-reminders/' target='_blank'>Display Recent Reminders</a>
			<li><a href='$siteURL/cwa-search-sent-email-by-callsign-or-email/' target='_blank'>Search Sent Email by Callsign or Email</a>
			<li><a href='$siteURL/cwa-view-a-student-cw-assessment-v2/' target='_blank'>View a Student CW Assessment V2</a>
			<li><a href='$siteURL/cwa-list-new-registrations/' target='_blank'>List New Registrations</a>
			<li><a href='$siteURL/cwa-update-callsign/' target='_blank'>Update Callsign</a>
			<li><a href='$siteURL/cwa-delete_user_info/' target='_blank'>Delete User Info</a>
			<li><a href='$siteURL/cwa-display-saved-report/' target='_blank'>Display Saved Report</a>
			</ul>
			<h4>Advisor Reporting Programs</h4>
			<ul>
			<li><a href='$siteURL/cwa-advisor-class-report/' target='_blank'>Advisor Class Report</a>
			<li><a href='$siteURL/cwa-advisor-request-student-assessment/' target='_blank'>Order Morse Code Proficiency Assessments</a>
			<li><a href='$siteURL/cwa-advisor-play-cw-audio-clips/' target='_blank'>Advisor Play CW Audio Clips</a>
			<li><a href='$siteURL/cwa-advisor-statistics/' target='_blank'>Advisor Statistics</a>
			<li><a href='$siteURL/cwa-detailed-history-for-an-advisor/' target='_blank'>Detailed History for an Advisor</a>
			<li><a href='$siteURL/cwa-display-advisor-classes/' target='_blank'>Display Advisor Classes</a>
			<li><a href='$siteURL/cwa-display-advisor-evaluation-statistics/' target='_blank'>Display Advisor Evaluation Statistics</a>
			<li><a href='$siteURL/cwa-display-all-advisors/' target='_blank'>Display All Advisors</a>
			<li><a href='$siteURL/cwa-display-evaluations-for-an-advisor/' target='_blank'>Display Evaluations for an Advisor</a>
			<li><a href='$siteURL/cwa-display-replacement-requests/' target='_blank'>Display Replacement Requests</a>
			<li><a href='$siteURL/cwa-display-student-evaluation-of-advisors/' target='_blank'>Display Student Evaluation of Advisors</a>
			<li><a href='$siteURL/cwa-generate-advisor-overall-statistics/' target='_blank'>Generate Advisor Overall Statistics</a>
			<li><a href='$siteURL/cwa-list-advisor…h-all-s-students/' target='_blank'>List Advisors With All S Students
			<li><a href='$siteURL/cwa-list-advisors-with-s-students/' target='_blank'>List Advisors With S Students</a>
			<li><a href='$siteURL/cwa-list-advisors-with-incomplete-evaluations/' target='_blank'>List Advisors With Incomplete Evaluations</a>
			<li><a href='$siteURL/cwa-list-past-advisors-registration-info/' target='_blank'>List Past Advisors Registration Info</a>
			<li><a href='$siteURL/cwa-manage-advisor-class-assignments/' target='_blank'>Manage Advisor Class Assignments</a>
			<li><a href='$siteURL/cwa-advisor-request-student-assessment/' target='_blank'>Order Morse Code Proficiency Assessments</a>
			<li><a href='$siteURL/cwa-show-advisor-class-assignments/' target='_blank'>Show Advisor Class Assignments</a>
			</ul>
			<h4>Advisor Utilities Programs</h4>
			<ul>
			<li><a href='$siteURL/cwa-advisor-and-advisorclass-report-generator/' target='_blank'>Advisor and AdvisorClass Report Generator</a>
			<li><a href='$siteURL/cwa-advisor-report-generator/' target='_blank'>Advisor Report Generator</a>
			<li><a href='$siteURL/cwa-advisorclass-report-generator/' target='_blank'>AdvisorClass Report Generator</a>
			<li><a href='$siteURL/cwa-display-and-update-advisor-information/' target='_blank'>Display and Update Advisor Information</a>
			</ul>
			<h4>Student Reporting Programs</h4>
			<ul>
			<li><a href='$siteURL/cwa-check-student-status/' target='_blank'>Check Student Status</a>
			<li><a href='$siteURL/cwa-display-all-students/' target='_blank'>Display All Students</a>
			<li><a href='$siteURL/cwa-display-student-history/' target='_blank'>Display Student History</a>
			<li><a href='$siteURL/cwa-gather-and-display-student-statistics/' target='_blank'>Gather and Display Student Statistics</a>
			<li><a href='$siteURL/cwa-how-students-progressed-report/' target='_blank'>How Students Progressed Report</a>
			<li><a href='$siteURL/cwa-list-all-students/' target='_blank'>List All Students</a>
			<li><a href='$siteURL/cwa-list-student-responders/' target='_blank'>List Student Responders</a>
			<li><a href='$siteURL/cwa-list-student-self-assessments/' target='_blank'>List Student Self Assessments</a>
			<li><a href='$siteURL/cwa-list-students-for-a-semester/' target='_blank'>List Students for a Semester</a>
			<li><a href='$siteURL/cwa-perform-assessment/' target='_blank'>Perform Assessment</a>
			<li><a href='$siteURL/cwa-practice-assessment/' target='_blank'>Practice Assessment</a>
			<li><a href='$siteURL/cwa-promotable-students-repeating/' target='_blank'>Promotable Students Repeating</a>
			<li><a href='$siteURL/cwa-repeating-student-statistics/' target='_blank'>Repeating Student Statistics</a>
			<li><a href='$siteURL/cwa-show-detailed-history-for-student/' target='_blank'>Show Detailed History for Student</a>
			<li><a href='$siteURL/cwa-student-progression-report/' target='_blank'>Student Progression Report</a>
			<li><a href='$siteURL/cwa-view-a-student-cw-assessment-v2/' target='_blank'>View a Student CW Assessment V2</a>
			</ul>
			<h4>Student Utilities Programs</h4>
			<ul>
			<li><a href='$siteURL/cwa-display-and-update-student-information/' target='_blank'>Display and Update Student Information</a>
			<li><a href='$siteURL/cwa-student-report-generator/' target='_blank'>Student Report Generator</a>
			</ul>
			<h4>Email Link Programs</h4>
			<ul>
			<li><a href='$siteURL/cwa-advisor-verification-of-student/' target='_blank'>Advisor Verification of Student</a>
			<li><a href='$siteURL/cwa-decline-student-reassignment/' target='_blank'>Decline Student Reassignment</a>
			<li><a href='$siteURL/cwa-end-of-semester-student-assessment/' target='_blank'>End of Semester Student Assessment</a>
			<li><a href='$siteURL/cwa-evaluate-student/' target='_blank'>Evaluate Student</a>
			<li><a href='$siteURL/cwa-process-advisor-verification/' target='_blank'>Process Advisor Verification</a>
			<li><a href='$siteURL/cwa-student-evaluation-of-advisor/' target='_blank'>Student Evaluation of Advisor</a>
			<li><a href='$siteURL/cwa-thank-you-remove/' target='_blank'>Thank You Remove</a>
			<li><a href='$siteURL/cwa-thank-you-yes/' target='_blank'>Thank You Yes</a>
			<li><a href='$siteURL/end-of-semester-student-evaluation-v2/' target='_blank'>End of Semester Student Evaluation V2</a>
			<li><a href='$siteURL/cwa-verify-advisor-class/' target='_blank'>Verify Advisor Class</a>
			</ul>
			</ul>
			<h4>Primary Programs</h4>
			<ul>
			<li><a href='$siteURL/cwa-advisor-registration/' target='_blank'>Advisor Registration</a>
			<li><a href='$siteURL/cwa-check-student-status/' target='_blank'>Check Student Status</a>
			<li><a href='$siteURL/cwa-show-advisor-class-assignments/' target='_blank'>Show Advisor Class Assignments</a>
			<li><a href='$siteURL/cwa-student-registration/' target='_blank'>Student Registration</a>
			</ul>

			<td style='vertical-align:top;'>
			<h4>Common Student / Advisor Management Tasks</h4>
			<li><a href='$siteURL/cwa-student-and-advisor-assignments/' target='_blank'>Student and Advisor Assignments (#1 Report)</a><br /><br />
			<li><a href='$siteURL/cwa-display-and-update-advisor-information/' target='_blank'>Display and Update Advisor Information</a>
			<li><a href='$siteURL/cwa-display-and-update-student-information/' target='_blank'>Display and Update Student Information</a><br /><br />
			<li><a href='$siteURL/cwa-student-management/?strpass=90' target='_blank'>Assign a Student to an Advisor regardless</a>
			<li><a href='$siteURL/cwa-student-management/?strpass=70' target='_blank'>Find Possible Classes for a Student</a>
			<li><a href='$siteURL/cwa-student-management/?strpass=80' target='_blank'>Find Possible Students for an Advisor's Class</a>
			<li><a href='$siteURL/cwa-student-management/?strpass=50' target='_blank'>Unassign a Student Regardless of Status</a>
			<li><a href='$siteURL/cwa-move-student-to-different-semester/' target='_blank'>Move Student to Different Semester</a>
			<li><a href='$siteURL/cwa-student-management/?strpass=55' target='_blank'>Re-assign a Student to Another Advisor</a><br /><br />
			<li><a href='$siteURL/cwa-push-advisor-class/' target='_blank'>Push Advisor Class</a><br /><br />
			
			<h4>Scheduled Programs</h4>
			<ul>
			<li><a href='$siteURL/cwa-daily-cron-process/' target='_blank'>Daily Cron Process</a>
			<li><a href='$siteURL/cwa-daily-advisor-cron-process/' target='_blank'>Daily Advisor Cron Process</a>
			<li><a href='$siteURL/cwa-daily-catalog-cron-process/' target='_blank'>Daily Catalog Cron Process</a>
			<li><a href='$siteURL/cwa-daily-student-cron/' target='_blank'>Daily Student Cron</a><br /><br />
			<li><a href='$siteURL/cwa-assign-students-to-advisors-sequence/' target='_blank'>Assign Students to Advisors Sequence (Testmode)</a>
			<li><a href='$siteURL/cwa-prepare-students-for-assignment-to-advisors/' target='_blank'>Prepare Students for Assignment to Advisors</a>
			<li><a href='$siteURL/cwa-prepare-advisors-for-student-assignments/' target='_blank'>Prepare Advisors for Student Assignments</a>
			<li><a href='$siteURL/cwa-assign-students-to-advisors-v3/' target='_blank'>Assign Students to Advisors V3</a><br /><br />
			<li><a href='$siteURL/cwa-advisor-class-reminder/' target='_blank'>Advisor Class Reminder</a>
			<li><a href='$siteURL/cwa-move-unassigned-students-to-next-semester/' target='_blank'>Move Unassigned Students to Next Semester</a>
			<li><a href='$siteURL/cwa-send-mid-term-verification-email/' target='_blank'>Send Mid-term Verification Email</a><br /><br />
			<li><a href='$siteURL/cwa-send-end-of-semester-assessment-email-to-advisors/' target='_blank'>Send End of Semester Assessment Email to Advisors</a>
			<li><a href='$siteURL/cwa-send-evaluation-email-to-advisors/' target='_blank'>Send Evaluation Email to Advisors</a>
			<li><a href='$siteURL/cwa-select-students-for-end-of-semester-assessment/' target='_blank'>Select Students for End of Semester Assessment</a>
			<li><a href='$siteURL/cwa-send-email-to-student-to-evaluate-advisor/' target='_blank'>Send Email to Student to Evaluate Advisor</a><br /><br />
			<li><a href='$siteURL/cwa-send-advisor-email-to-view-student-evaluations/' target='_blank'>Send Advisor Email to View Student Evaluations</a>
			<li><a href='$siteURL/cwa-send-congratulations-email-to-students/' target='_blank'>Send Congratulations Email to Students</a>
			</ul>

			<h4>Student Management Functions</h4>
			<ul>
			<li><strong>Useful Functions Before Students are Assigned to Advisors</strong>
				<ol style='list-style-type: lower-alpha; padding-bottom: 0;'>
				<li><a href='$siteURL/cwa-student-management/?strpass=2' target='_blank'>Pre-assign Student to an Advisor</a>
				<li><a href='$siteURL/cwa-student-management/?strpass=5' target='_blank'>Delete Student's Pre-assigned Advisor</a>
				<li><a href='$siteURL/cwa-student-management/?strpass=7' target='_blank'>List Students Needing Intervention</a>
				<li><a href='$siteURL/cwa-student-management/?strpass=25' target='_blank'>Resolve Student Hold</a>
				<li><a href='$siteURL/cwa-student-management/?strpass=100' target='_blank'>Confirm One or More Students
				</ol>
			<li><strong>Useful Functions Any Time</strong>
				<ol style='list-style-type: lower-alpha; padding-bottom: 0;'>
				<li><a href='$siteURL/cwa-update-unassigned-student-information/' target='_blank'>Update Unassigned Student Information</a>
				<li><a href='$siteURL/cwa-student-management/?strpass=60' target='_blank'>Remove and/or Unassign a Student</a>
				<li><a href='$siteURL/cwa-student-management/?strpass=20' target='_blank'>Exclude an Advisor from being Assigned to a Student</a>
				<li><a href='$siteURL/cwa-move-student-to-different-semester/' target='_blank'>Move Student to Different Semester</a>
				<li><a href='$siteURL/cwa-student-management/?strpass=30' target='_blank'>Override Excluded Advisor</a>
				</ol>
			<li><strong>Useful Functions After Students Have Been Assigned</strong>
				<ol style='list-style-type: lower-alpha; padding-bottom: 0;'>
				<li><a href='$siteURL/cwa-student-management/?strpass=35' target='_blank'>Move Student to a Different Level and Unassign</a>
				<li><a href='$siteURL/cwa-student-management/?strpass=40' target='_blank'>Add Unassigned Student to an Advisor's Class</a>
				<li><a href='$siteURL/cwa-student-management/?strpass=50' target='_blank'>Unassign a Student Regardless of Status</a>
				<li><a href='$siteURL/cwa-student-management/?strpass=55' target='_blank'>Re-assign a Student to Another Advisor</a>
				<li><a href='$siteURL/cwa-student-management/?strpass=70' target='_blank'>Find Possible Classes for a Student</a>
				<li><a href='$siteURL/cwa-student-management/?strpass=80' target='_blank'>Find Possible Students for an Advisor's Class</a>
				<li><a href='$siteURL/cwa-student-management/?strpass=90' target='_blank'>Assign a Student to an Advisor regardless</a>
				<li><a href='$siteURL/cwa-student-management/?strpass=85' target='_blank'>Verify One or More Students</a>
				</ol>
			</ul>

			<h4>Other Utilities Programs</h4>
			<ul>
			<li><a href='$siteURL/cwa-legends-codes-and-things/' target='_blank'>Legends Codes and Things</a>
			<li><a href='$siteURL/cwa-automatic-student-backfill-v1/' target='_blank'>Automatic Student Backfill</a>
			<li><a href='$siteURL/bad-actors/' target='_blank'>Bad Actors</a>
			<li><a href='$siteURL/cwa-copy-data-between-current-and-backup-tables/' target='_blank'>Copy Data Between Current and Backup Tables</a>
			<li><a href='$siteURL/cwa-display-catalog-for-a-timezone/' target='_blank'>Display Catalog for a Timezone</a>
			<li><a href='$siteURL/cwa-display-chronological-action-log/' target='_blank'>Display Chronological Action Log</a>
			<li><a href='$siteURL/cwa-display-initialization-array/' target='_blank'>Display Initialization Array</a>
			<li><a href='$siteURL/cwa-display-saved-report/' target='_blank'>Display Saved Report</a>
			<li><a href='$siteURL/cwa-generic-updater/' target='_blank'>Generic Updater</a>
			<li><a href='$siteURL/cwa-list-all-pages/' target='_blank'>List All Pages</a>
			<li><a href='$siteURL/cwa-list-new-registrations/' target='_blank'>List New Registrations</a>
			<li><a href='$siteURL/cwa-manage-directory/' target='_blank'>Manage Directory</a>
			<li><a href='siteURL/cwa-manage-reminders/' target='_blank'>Manage Reminders</a>
			<li><a href='$siteURL/cwa-perform-assessment/' target='_blank'>Perform Assessment/a>
			<li><a href='$siteURL/cwa-recover-deleted-record/' target='_blank'>Recover Deleted Record</a>
			<li><a href='siteURL/cwa-remove-item/' target='_blank'>Remove Item</a>
			<li><a href='$siteURL/cwa-search-audit-log/' target='_blank'>Search Audit Log</a>
			<li><a href='$siteURL/cwa-search-joblog/' target='_blank'>Search Joblog</a>
			<li><a href='$siteURL/cwa-search-sent-email-by-callsign-or-email/' target='_blank'>Search Sent Email by Callsign or Email</a>
			<li><a href='$siteURL/cwa-search-tracking-data/' target='_blank'>Search Tracking Data</a>
			<li><a href='siteURL/cwa-send-an-email/' target='_blank'>Send an Email</a>
			<li><a href='$siteURL/cwa-show-saved-email/' target='_blank'>Show Saved Email</a>
			<li><a href='$siteURL/utility-show-offsets-for-a-country-or-zip-code/' target='_blank'>UTILITY Show Offsets for a Country or Zip Code</a>
			<li><a href='$siteURL/utility-calculate-utc/' target='_blank'>UTILITY: Calculate UTC</a>
			<li><a href='$siteURL/cwa-verify-temp-data/' target='_blank'>Verify Temp Data</a>
			</ul>
			</td>
			</tr>
			</table>";
	}
}
if ($userRole == 'advisor' || $userRole == 'administrator') {
	$userName		= strtoupper($userName);
	$returnInfo		= display_reminders('advisor',$userName,$doDebug);
	if ($returnInfo[0] === FALSE) {
		if ($doDebug) {
			echo "display_reminders returned $returnInfo[1]<br />";
		}
		$content	.= "<h3>Advisor Portal for $userName</h3>";
	} else {
		$content	.= "<h3>Advisor Portal for $userName</h3>
						<div class='refresh' data-callsign='$userName' data-role='advisor' data-url='/wp-content/uploads/refreshapi.php' data-seconds='60'>
						$returnInfo[1]
						</div>";
	}
	if ($userRole == 'advisor') {
		// see if the advisor has a advisor record. If not, indicate that they must sign up
		$sql	= "select count(call_sign) from $advisorTableName 
					where call_sign = '$userName' ";
		$advisorCount	= $wpdb->get_var($sql);
		// if advisorCount is NULL or zero there is no record. Advisor 
		// needs to sign up for a class
		if ($advisorCount === NULL || $advisorCount == 0) {
			$content	.= "<p><b>IMPORTANT!</b> You have obtained a username and password. 
							You must next sign up to advise one or more classes. 
							If you do not sign up, your 
							username will be deleted.</p>";
		}				
	
	}
	$content		.= "<h4>Advisor Programs</h4>
						<p><a href='$siteURL/cwa-manage-advisor-class-assignments/' target='_blank'>Manage Advisor Class Assignments</a><br />
							This program displays the current class makeup and allows the advisor to specify student's status,
							request replacement students, check Morse Code Proficency Assessments.</p>
						<p><a href='$siteURL/cwa-view-a-student-cw-assessment-v2/' target='_blank'>View a Student's Morse Code Assessments</a><br />
							This program allows the advisor to view one of his student's Morse Code Proficiency Assessments.</p>
						<p><a href='$siteURL/cwa-display-recent-reminders/' target='_blank'>Display Recent Reminders</a>
							Displays recent reminders starting with the most recent reminder. Displays 25 at a time</p>
						<p><a href='$siteURL/cwa-advisor-registration/' target='_blank'>Sign up for an Advisor Class</a><br />
							This program provides the ability to sign up for an upcoming semester and specify 
							what class(es) the advisor wishes to hold, and when those classes would be held. 
							It also provides the ability for the advisor to update that information as needed.</p>
						<p><a href='$siteURL/cwa-show-advisor-class-assignments/?strpass=2&inp_call_sign=$userName&inp_verified=Y' target='_blank'>Show Advisor Class Assignments</a><br />
							This program shows upcoming advisor registrations (if any), current class makeup, and 
							all past students</p>
						<p><a href='$siteURL/cwa-advisor-request-student-assessment/' target='_blank'>Order Morse Code Proficiency Assessments</a><br />
							This program allows the advisor to specify the parameters for the assessment and 
							which students are to take the assessment.</p>
						<p><a href='$siteURL/cwa-update-callsign/' target='_blank'>Update Callsign</a><br />
							This program will change your callsign to your new callsign in the CW Academy database.</p>
						<p><a href='https://cwops.org/cw-academy/cwa-advisor-resources/' target='_blank'>Advisor Resources</a><br />
							Use this link to access the advisor resources hosted on cwops.org</p>";
}
if ($userRole == 'student' || $userRole == 'administrator') {
	$userName		= strtoupper($userName);
	$returnInfo		= display_reminders('student',$userName,$doDebug);
	if ($returnInfo[0] === FALSE) {
		if ($doDebug) {
			echo "display_reminders returned $returnInfo[1]<br />";
		}
		$content	.= "<h3>Student Portal for $userName</h3>";
	} else {
		$content	.= "<h3>Student Portal for $userName</h3>
						<div class='refresh' data-callsign='$userName' data-role='student' data-url='/wp-content/uploads/refreshapi.php' data-seconds='60'>
						$returnInfo[1]
						</div>";
	}
	if ($userRole == 'student') {
		// see if the student has a student record. If not, indicate that they must sign up
		$sql	= "select count(call_sign) from $studentTableName 
					where call_sign = '$userName' ";
		$studentCount	= $wpdb->get_var($sql);
		// if studentCount is NULL or zero there is no record. Student 
		// needs to sign up for a class
		if ($studentCount === NULL || $studentCount == 0) {
			$content	.= "<p><b>IMPORTANT!</b> You have obtained a username and password. 
							You must next sign up for a class. If you do not sign up, your 
							username will be deleted.</p>";
		}				
	
	}
	$content		.= "<h4>Student Programs</h4>
						<p><a href='$siteURL/cwa-student-registration/' target='_blank'>Student Sign up</a><br />
							Use this program to sign up for a CW Academy Class or to modify your signup 
							details.</p>
						<p><a href='$siteURL/cwa-check-student-status/?inp_verified=1&strpass=2&inp_callsign=$userName' target='_blank'>Check Student Status</a><br />
							The program displays the current status of your CW Academy class request.</p>
						<p><a href='$siteURL/cwa-display-recent-reminders/' target='_blank'>Display Recent Reminders</a>
							Displays recent reminders starting with the most recent reminder. Displays 25 at a time</p>
						<p><a href='$siteURL/cwa-update-callsign/' target='_blank'>Update Callsign</a><br />
							This program will change your callsign to your new callsign in the CW Academy database.</p>
						<p><a href='https://cwops.org/cw-academy/cw-academy-student-resources/' target='_blank'>Student Resources</a><br />
							Use this link to access the curriculum and various practice files. This information is hosted on cwops.org.</p>";
}
	$thisTime 		= date('Y-m-d H:i:s');
	$content 		.= "<br /><br /><a href='$siteURL/wp-login.php/?action=logout'>Logout</a><br /></p>
						<p><a href='$siteURL/account' target='_blank'>Manage Account Settings</a></p>
						<p>Prepared at $thisTime</p>";
	$endingMicroTime = microtime(TRUE);
	$elapsedTime	= $endingMicroTime - $startingMicroTime;
	$elapsedTime	= number_format($elapsedTime, 4, '.', ',');
	$content		.= "<p>Report pass $strPass took $elapsedTime seconds to run</p>";
	$nowDate		= date('Y-m-d');
	$nowTime		= date('H:i:s');
	$thisStr			= 'Production';
	if ($testMode) {
		$thisStr		= 'Testmode';
	}
	$result			= write_joblog_func("Program List|$nowDate|$nowTime|$userName|Time|$thisStr|$strPass: $elapsedTime");
	if ($result == 'FAIL') {
		$content	.= "<p>writing to joblog.txt failed</p>";
	}
	return $content;
}
add_shortcode ('program_list', 'program_list_func');
