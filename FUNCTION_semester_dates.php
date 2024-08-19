function semester_dates($inp_semester = '',$doDebug=FALSE) {

/*	Given the semester,this function calculates and returns an array 
	of three dates in unix time format and the number of days to the semester based 
	on the current date
		semesterStart			Jan 1, May 1, Sep 1 of the year
		catalogAvailable		The unix time of the catalog available date
									11/15, 3/15, 7/15
		assignDate				The unix time of the date students will be assigned to classes
									12/10, 4/10, 8/10
		daysToSemester			The number of days from today's current date

	if the semester is not provided or is not valid, returns FALSE
									
*/

	if ($doDebug) {
		echo "<br /><b>FUNCTION semester_dates</b><br />";
	}

	if ($inp_semester == '') {
		if($doDebug) {
			echo "inp_semester is empty<br />";
		}
		return FALSE;
	}

	// calculate the three important dates
	$myArray				= explode(" ",$inp_semester);
	$thisYear				= $myArray[0];
	$thisSemester			= $myArray[1];

	if (intval($thisYear) < 2000) {
		if ($doDebug) {
			echo "the year in $inp_semester is invalid<br />";
		}
		return FALSE;
	}
	$semesterArray			= array('Jan/Feb','May/Jun','Sep/Oct');
	if (!in_array($thisSemester,$semesterArray)) {
		if ($doDebug) {
			echo "The months in $inp_semester are invalid<br />";
		}
		return FALSE;
	}
	
	// figure out the previous year
	$myStr					= "$thisYear-01-01 - 1 year";
	$myInt					= strtotime($myStr);
	$prevYear				= date('Y',$myInt);
	
	
	$dateArray				= array('Jan/Feb'=>"$thisYear-01-01,$prevYear-11-15,$prevYear-12-10",
									'May/Jun'=>"$thisYear-05-01,$thisYear-03-15,$thisYear-04-10", 
									'Sep/Oct'=>"$thisYear-09-01,$thisYear-07-15,$thisYear-08-10");
	$thisDates				= $dateArray[$thisSemester];
	$myArray				= explode(',',$thisDates);
	$date1					= strtotime($myArray[0]);
	$date2					= strtotime($myArray[1]);
	$date3					= strtotime($myArray[2]);
	
	$daysToSemester			= days_to_semester($inp_semester);
	if ($doDebug) {
		echo "returning array:<br />
			   semesterStart: $date1<br />
			   catalogAvailable: $date2<br />
			   assignDate: $date3<br />
			   daysToSemester: $daysToSemester<br /><br />";
	}
	
	return array('semesterStart'=>$date1,
				 'catalogAvailable'=>$date2,
				 'assignDate'=>$date3,
				 'daysToSemester'=>$daysToSemester);
}
add_action('semester_dates','semester_dates');
