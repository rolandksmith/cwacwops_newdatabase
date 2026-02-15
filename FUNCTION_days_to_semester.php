function days_to_semester($inp_semester) {

/*	returns the number of days between now and the presumed start of the semester

	input: The semester 
	returns: number of days (will return -99 if the semester is not valid. Semester must be 
		one of the semesters defined in the initialization array)

*/

	$semesterConversion			= array('Jan/Feb'=>'-01-01','May/Jun'=>'-05-01','Sep/Oct'=>'-09-01');
	$context = CWA_Context::getInstance();
	$currentSemester			= $context->currentSemester;
	$nextSemester				= $context->nextSemester;
	$semesterTwo				= $context->semesterTwo;
	$semesterThree				= $context->semesterThree;
	$semesterFour				= $context->semesterFour;
	$validSemesters				= array($currentSemester,$nextSemester,$semesterTwo,$semesterThree,$semesterFour);
	
	if (!in_array($inp_semester,$validSemesters)) {
		return -99;
	}
	
	$nowDate					= new DateTime("now");
	$myArray					= explode(" ",$inp_semester);
	$thisYear					= $myArray[0];
	$thisMonths					= $myArray[1];
	$myStr						= $semesterConversion[$thisMonths];
	$futureStr					= "$thisYear$myStr";
	$futureDate					= new DateTime($futureStr);
	$diff 						= $nowDate->diff($futureDate);
	$daysToSemester				= $diff->format('%R%a');
	$daysToSemester				= $daysToSemester + 0;

	return $daysToSemester;
} 
add_action('days_to_semester', 'days_to_semester');