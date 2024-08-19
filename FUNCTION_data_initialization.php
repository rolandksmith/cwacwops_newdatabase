function data_initialization_func($attrib='') {

// modified 23Feb20 by Roland to add prevSemester to the return array
// modified and remodified 10Mar21 by Roland and Bob about semester Apr/May moving to May/Jun

// Initialize fields
$validUser						= "N";
$userRole						= "";
$myInterim						= 0;
$myDate							= "0000-00-00";
$nextSemester					= "";
$semesterTwo					= "";
$semesterThree					= "";
$semesterFour					= "";
$validEmailPeriod				= " ";
$daysToSemester					= 0;
$prevSemester					= "";
$defaultClassSize				= 6;
$userName						= "";

// determine if current user is a valid user
	$validUsers 				= array('wr7q','WR7Q','Roland','kcgator','n7ast','N7AST','k7ojl','K7OJL','VE2KM',); 
	$validTestmode				= array('Roland','kcgator','n7ast', 'N7AST', 'k7ojl','K7OJL','wr7q','WR7Q','VE2KM',);
	$current_user 				= wp_get_current_user();
	
//	echo "current_user:<br /><pre>";
//	print_r($current_user);
//	echo "</pre><br />";
	
	
	$user_name 					= trim($current_user->user_login);
	$user_email					= $current_user->user_email;
	$user_display_name			= $current_user->display_name;
	$userID						= get_current_user_id();
	
	
	if (in_array($user_name,$validUsers)) {
		$validUser 				= "Y";
	} else {
		$validUser 				= "N";
	}
	
// determine if current user is an administrator
	$user_role					= '';
	if (in_array('administrator', (array) $current_user->roles)) {
		$user_role				= 'administrator';
	} elseif (in_array('advisor', (array) $current_user->roles)) {
		$user_role				= 'advisor';
	} elseif (in_array('student', (array) $current_user->roles)) {
		$user_role				= 'student';
	}

// get today's date in a couple of formats
	$myInterim 					= time();
	$myDate 					= date('Y-m-d');

// based on the current date, determine the current and next three semesters
// and how many days until the next semester
	$currentYear 				= date('Y',$myInterim);
	$currentMonth 				= date('m',$myInterim);
	$newDate					= strtotime("$myDate + 1 year");
	$newYear 					= date('Y',$newDate);
	$newDate					= strtotime("$myDate + 2 years");
	$newNewYear					= date('Y',$newDate);
	$newDate					= strtotime("$myDate - 1 year");
	$prevYear					= date('Y',$newDate);
		
// set current semester
	switch ($currentMonth) {
		case "01":
			$currentSemester	= "$currentYear Jan/Feb";
			$prevSemester		= "$prevYear Sep/Oct";
			break;
		case "02":
			$currentSemester	= "$currentYear Jan/Feb";
			$prevSemester		= "$prevYear Sep/Oct";
			break;
		case "03":
			$currentSemester	= "Not in Session";
			$prevSemester		= "$currentYear Jan/Feb";
			break;
		case "04":
			$currentSemester	= "Not in Session";
			$prevSemester		= "$currentYear Jan/Feb";
			break;
		case "05":
			$currentSemester	= "$currentYear May/Jun";
			$prevSemester		= "$currentYear Jan/Feb";
			break;
		case "06":
			$currentSemester	= "$currentYear May/Jun";
			$prevSemester		= "$currentYear Jan/Feb";
			break;
		case "07":
			$currentSemester	= "Not in Session";
			$prevSemester		= "$currentYear May/Jun";
			break;
		case "08":
			$currentSemester	= "Not in Session";
			$prevSemester		= "$currentYear May/Jun";
			break;
		case "09":
			$currentSemester	= "$currentYear Sep/Oct";
			$prevSemester		= "$currentYear MayJun";
			break;
		case "10":
			$currentSemester	= "$currentYear Sep/Oct";
			$prevSemester		= "$currentYear May/Jun";
			break;
		case "11":
			$currentSemester	= "Not in Session";
			$prevSemester		= "$currentYear Sep/Oct";
			break;
		case "12":
			$currentSemester	= "Not in Session";
			$prevSemester		= "$currentYear Sep/Oct";
			break;
	}
// set next three semesters	
	$monthArray					= array('01'=>'A','02'=>'A','03'=>'A','04'=>'A','05'=>'B',
'06'=>'B','07'=>'B','08'=>'B','09'=>'C','10'=>'C','11'=>'C','12'=>'C');
	$semesterType				= $monthArray["$currentMonth"];
	switch ($semesterType) {
		case "A":
			$nextSemester 		= "$currentYear May/Jun";
			$semesterTwo 		= "$currentYear Sep/Oct";
			$semesterThree 		= "$newYear Jan/Feb";
			$semesterFour		= "$newYear May/Jun";
			$nextSemesterDate	= "$currentYear-05-01";
			$nextSemesterStamp	= strtotime($nextSemesterDate);
			$timeDiff			= $nextSemesterStamp - $myInterim;
			$daysToSemester		= intval(round($timeDiff/86400));
			break;
		case "B":
			$nextSemester 		= "$currentYear Sep/Oct";
			$semesterTwo 		= "$newYear Jan/Feb";
			$semesterThree 		= "$newYear May/Jun";
			$semesterFour		= "$newYear Sep/Oct";
			$nextSemesterDate	= "$currentYear-09-01";
			$nextSemesterStamp	= strtotime($nextSemesterDate);
			$timeDiff			= $nextSemesterStamp - $myInterim;
			$daysToSemester		= intval(round($timeDiff/86400));
			break;
		case "C":
			$nextSemester 		= "$newYear Jan/Feb";
			$semesterTwo 		= "$newYear May/Jun";
			$semesterThree 		= "$newYear Sep/Oct";
			$semesterFour		= "$newNewYear Jan/Feb";
			$nextSemesterDate	= "$newYear-01-01";
			$nextSemesterStamp	= strtotime($nextSemesterDate);
			$timeDiff			= $nextSemesterStamp - $myInterim;
			$daysToSemester		= intval(round($timeDiff/86400));
			break;
	}
	
// determine if we're in the periods to send the validate email
// the valid periods are 3/15 -> 4/10; 7/15 -> 8/10; 11/15 -> 12/10
	$validEmailPeriod			= "N";
	$currentMonthDay			= date('md',$myInterim);
	if ($currentMonthDay >= "0315" && $currentMonthDay <= "0410") {
		$validEmailPeriod		= "Y";
	} elseif ($currentMonthDay >= "0715" && $currentMonthDay <= "0810") {
		$validEmailPeriod		= "Y";
	} elseif ($currentMonthDay >= "1115" && $currentMonthDay <= "1210") {
		$validEmailPeriod		= "Y";
	} else {
		$validEmailPeriod		= "N";
	}

// determine if we're in the period to allow replacement students
// the valid periods are 4/10 thru 5/10, 8/10 thru 9/10 and 12/10 thru 1/10
	$validReplacementPeriod		= "N";
	$currentYMD					= date('Ymd');
	
	$apr10						= $currentYear . "0410";
	$may10						= $currentYear . "0510";
	$aug10						= $currentYear . "0810";
	$sep10						= $currentYear . "0910";
	$dec10						= $currentYear . "1210";
	$jan10						= $newYear . "0110";
	
	if ($currentYMD >= $apr10 && $currentYMD < $may10) {
		$validReplacementPeriod	= "Y";
	} elseif ($currentYMD >= $aug10 && $currentYMD < $sep10) {
		$validReplacementPeriod	= "Y";
	} elseif ($currentYMD >= $dec10 && $currentYMD < $jan10) {
		$validReplacementPeriod	= "Y";
	} else {
		$validReplacementPeriod	= 'N';
	}

// setup the proximate semester
	if ($currentSemester == 'Not in Session') {
		$proximateSemester		= $nextSemester;
	} else {
		$proximateSemester		= $currentSemester;
	}

// set up pastSemesters
	$pastSemesters				= '2020 Jan/Feb|2020 APR/MAY|2020 SEP/OCT|2021 Jan/Feb|2021 Apr/May|2021 Sep/Oct|2022 Jan/Feb|2022 May/Jun|2022 Sep/Oct|2023 Jan/Feb|2023 May/Jun|2023 Sep/Oct|2024 Jan/Feb|2024 May/Jun';
	$pastSemestersArray			= array('2024 May/Jun',
										'2024 Jan/Feb',
										'2023 Sep/Oct',
										'2023 May/Jun',
										'2023 Jan/Feb',
										'2022 Sep/Oct',
										'2022 May/Jun',
										'2022 Jan/Feb',
										'2021 Sep/Oct',
										'2021 Apr/May',
										'2021 Jan/Feb',
										'2020 SEP/OCT',
										'2020 APR/MAY',
										'2020 Jan/Feb');
	
// get site url
	$siteURL					= get_site_url();

	$currentDateTime				= date('Y-m-d H:i:s');
	if ($attrib == 'fake') {
		$currentDate				= "2021-10-22 13:00:00";
		$currentTimestamp			= strtotime($currentDate);
		$result 					= array('validUser'=>$validUser,
											'userRole'=>$user_role,
											'userName'=>$user_name,
											'userID'=>$userID,
											'userEmail'=>$user_email,
											'userDisplayName'=>$user_display_name,
											'currentTimestamp'=>$currentTimestamp,
											'currentDateTime'=>$currentDateTime,
											'currentDate'=>'2021-10-22',
											'prevSemester'=>'2021 Sep/Oct',
											'currentSemester'=>'Not in Session',
											'nextSemester'=>'2022 Jan/Feb',
											'semesterTwo'=>'2022 May/Jun',
											'semesterThree'=>'2022 SepOct',
											'semesterFour'=>'2023 Jan/Feb',
											'proximateSemester'=>'2022 Jan/Feb',
											'pastSemesters'=>$pastSemesters,
											'pastSemestersArray'=>$pastSemestersArray,
											'validEmailPeriod'=>'Y',
											'validReplacementPeriod'=>'N',
											'daysToSemester'=>40,
											'defaultClassSize'=>$defaultClassSize,
											'validTestmode'=>$validTestmode,
											'flatFilePath'=>'/home/cwacwops/CWAT',
											'siteurl'=>$siteURL,
											'userEmail'=>$user_email,
											'userDisplayName'=>$user_display_name
											);
	} else {
		$result 					= array('validUser'=>$validUser,
											'userRole'=>$user_role,
											'userName'=>$user_name,
											'userID'=>$userID,
											'userEmail'=>$user_email,
											'userDisplayName'=>$user_display_name,
											'currentTimestamp'=>$myInterim,
											'currentDateTime'=>$currentDateTime,
											'currentDate'=>$myDate,
											'prevSemester'=>$prevSemester,
											'currentSemester'=>$currentSemester,
											'nextSemester'=>$nextSemester,
											'semesterTwo'=>$semesterTwo,
											'semesterThree'=>$semesterThree,
											'semesterFour'=>$semesterFour,
											'proximateSemester'=>$proximateSemester,
											'pastSemesters'=>$pastSemesters,
											'pastSemestersArray'=>$pastSemestersArray,
											'validEmailPeriod'=>$validEmailPeriod,
											'validReplacementPeriod'=>$validReplacementPeriod,
											'daysToSemester'=>$daysToSemester,
											'defaultClassSize'=>$defaultClassSize,
											'validTestmode'=>$validTestmode,
											'flatFilePath'=>'/home/cwacwops/CWAT',
											'siteurl'=>$siteURL,
											'userEmail'=>$user_email,
											'userDisplayName'=>$user_display_name
											);
	}
	return $result;
}
add_action('data_initialization_func','data_initialization_func');
