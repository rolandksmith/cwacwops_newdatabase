<?php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Max-Age: 1000");
header("Access-Control-Allow-Headers: X-Requested-With, Content-Type, Origin, Cache-Control, Pragma, Authorization, Accept, Accept-Encoding");
header("Access-Control-Allow-Methods: PUT, POST, GET, OPTIONS, DELETE");

if (isset( $_SERVER['REQUEST_METHOD'] )
  	&& $_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
	  // need preflight here
	  header( 'Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept' );
	  // add cache control for preflight cache
	  // @link https://httptoolkit.tech/blog/cache-your-cors/
	  header( 'Access-Control-Max-Age: 86400' );
	  header( 'Cache-Control: public, max-age=86400' );
	  header( 'Vary: origin' );
	  header("HTTP/1.1 200 OK");
	  // just exit and CORS request will be okay
	  // NOTE: We are exiting only when the OPTIONS preflight request is made
	  // because the pre-flight only checks for response header and HTTP status code.
	  exit( 0 );
}

$doDebug = FALSE;

if ($doDebug) {
	echo "assessmentapi called\n";
}


$doProceed = TRUE;
$callsign = '';
$level = '';
$cpm = 0;
$eff = 0;
$freq = 0;
$questions = 0;
$words = 0;
$characters = 0;
$answers = 0;
$infor = '';
$vocab = '';
$details = '';
$score = 0;
$token = "abcde";
$callsigns = '';
$thisdate = date("Y-m-d H:i:s");
$passwd = "7B-m)p7d2S";

//  connect to the database
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$link = mysqli_connect(NULL,"cwacwops_wp540",$passwd,"cwacwops_wp540");


if ($doDebug) {
 $thisStuff = print_r($_POST,TRUE);
  echo "contents of POST: $thisStuff\n";
}

// get the data being posted
$inpVariable =  $_POST['variable'];

// stuff what was received into wpw1_cwa_assessment_log table
$query = "insert into wpw1_cwa_assessment_log 
			(base64data,date_written,program) 
			values ('$inpVariable','$thisdate','assessmentapi')";
// echo "$query\n";			
$result = mysqli_query($link, $query);


$myVariable = base64_decode($inpVariable);
if ($doDebug) {
 echo "base64 decoded: $myVariable\n";
}

// now have a json variable
$dataArray = json_decode($myVariable,TRUE);
if ($doDebug) {
	 $thisStuff = print_r($dataArray,TRUE);
	 echo "decoded json data: $thisStuff\n";
 }

if ($doDebug) {
 	$arrayKeys = array_keys($dataArray);
 	print_r($arrayKeys);
}

if (array_key_exists('callsign',$dataArray)) {
	$callsign = $dataArray['callsign'];
	if ($doDebug) {
		echo "callsign: $callsign\n";
	}
}
if (array_key_exists('level',$dataArray)) {
	$level = $dataArray['level'];
	if ($doDebug) {
		echo "level: $level\n";
	}
}
if (array_key_exists('cpm',$dataArray)) {
	$cpm = $dataArray['cpm'];
	if ($doDebug) {
		echo "cpm: $cpm\n";
	}
}
if (array_key_exists('eff',$dataArray)) {
	$eff = $dataArray['eff'];
	if ($doDebug) {
		echo "eff: $eff\n";
	}
}
if (array_key_exists('freq',$dataArray)) {
	$freq = $dataArray['freq'];
	if ($doDebug) {
		echo "freq: $freq\n";
	}
}
if (array_key_exists('questions',$dataArray)) {
	$questions = $dataArray['questions'];
	if ($doDebug) {
		echo "questions: $questions\n";
	}
}
if (array_key_exists('words',$dataArray)) {
	$words = $dataArray['words'];
	if ($doDebug) {
		echo "words: $words\n";
	}
}
if (array_key_exists('characters',$dataArray)) {
	$characters = $dataArray['characters'];
	if ($doDebug) {
		echo "characters: $characters\n";
	}
}
if (array_key_exists('callsigns',$dataArray)) {
	$callsigns = $dataArray['callsigns'];
	if ($doDebug) {
		echo "callsigns: $callsigns\n";
	}
}
if (array_key_exists('answers',$dataArray)) {
	$answers = $dataArray['answers'];
	if ($doDebug) {
		echo "answers: $answers\n";
	}
}
if (array_key_exists('vocab',$dataArray)) {
	$vocab = $dataArray['vocab'];
	if ($doDebug) {
		echo "vocab: $vocab\n";
	}
}
if (array_key_exists('infor',$dataArray)) {
	$infor = $dataArray['infor'];
	if ($doDebug) {
		echo "infor: $infor\n";
	}
}
if (array_key_exists('score',$dataArray)) {
	$score = $dataArray['score'];
	if ($doDebug) {
		echo "score: $score\n";
	}
}
if (array_key_exists('set',$dataArray)) {
	$detailsArray = $dataArray['set'];
 	if ($doDebug) {
		echo "have set\n";
	}
}
if (array_key_exists('token',$dataArray)) {
	$token = $dataArray['token'];
	if ($doDebug) {
		echo "token: $token\n";
	}
}


if ($doProceed) {

	$callsign = strtoupper($callsign);
	$details = json_encode($detailsArray);
	
	$query = "insert into wpw1_cwa_new_assessment_data 
(callsign, level, cpm, eff, freq, questions, words, characters, answers, callsigns, vocab, score, details, token, infor, date_written) values 
('$callsign', '$level', $cpm, $eff, '$freq', $questions, $words, $characters, $answers, '$callsigns', '$vocab', $score, '$details', '$token', '$infor', '$thisdate')";
	
	if ($doDebug) {
		echo "$query\n";
	}
				
	$result = mysqli_query($link, $query);

	if ($result === FALSE) {
		if ($doDebug) {	
			echo "result was FALSE\n";
		}
		header("HTTP/1.1 400 NO");
	} else {
		if ($doDebug) {
			echo "result was TRUE\n";
		}
		header("HTTP/1.1 200 OK");
	}
	$link->close();
} else {
	if ($doDebug) {
		echo "doProc eed was FALSE\n";
	}
	header("HTTP/1.1 400 NO");
}

 exit(0);

?>