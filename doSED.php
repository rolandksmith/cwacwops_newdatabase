<?php

$filesArray = array(
'advisornew_report_generator.php',
'daily_advisor_cron_v2.php',
'daily_student_cron_v2.php',
'daily_student_cron_v3.php',
'decline_student_reassignment.php',
'display_advisor_evaluation_statistics.php',
'display_evaluations_for_an_advisor.php',
'display_student_evaluation_of_advisors.php',
'list_all_students.php',
'list_students_for_a_semester.php',
'prepare_advisors_for_student_assignments.php',
'search_email_by_callsign.php',
'select_students_for_assessment.php',
'send_congratulations_email_to_students.php',
'send_email_to_student_to_evaluate_advisor.php',
'show_detailed_history_for_student.php',
'student_and_advisor_color_chart.php',
'student_evaluation_of_advisor.php'
);

foreach($filesArray as $thisFile) {
	$output = null;
	$retvalue = null;
	exec("sed -i.bak 's/adviisor/advisor/g' $thisFile", $output, $retvalue);
	echo "Ran $thisFile. Returned with a value of $retvalue\n";
//	print_r($output);
	echo "Next File\n";
}
?>