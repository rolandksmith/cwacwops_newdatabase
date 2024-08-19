function playAudioFile($inp_level='',$inp_mode='',$id_number=1,$doDebug=FALSE,$playOnce='Y') {

/* 	inp_level: 	BegBas, BasInt, IntAdv, Adv
	inp_mode: 	random, number (specific number)
	id_number: 	the number to be attached to the 'audioclip' name to make the
				clip id unique
	
	example: 	playAudioFile("IntAdv","random");
				playAudioFile("IntAdv","4",$doDebug);
				
				
	Returns an array
	If the media file exists:  	0: html to play the file
								1: full path clip name
								2: clip number
								3: clip contents
	
	If the media file does not exist:	0: FALSE
										1: Error message
										2: full path clip name
										3: (empty)
										
*/


	if ($doDebug) {
		echo "<br />Arrived at playAudioFile with data: $inp_level|$inp_mode|$id_number|$playOnce<br />";
	}

	$initializationArray			= data_initialization_func();
	$siteURL						= $initializationArray['siteurl'];

	$levelArray						= array("BegBas","BasInt","IntAdv","Adv");

	$mediaFiles						= array();
	$mediaFiles['BegBas'][1]		= "$siteURL/wp-content/uploads/begbas01.mp3";	
	$mediaFiles['BegBas'][2]		= "$siteURL/wp-content/uploads/begbas02.mp3";	
	$mediaFiles['BegBas'][3]		= "$siteURL/wp-content/uploads/begbas03.mp3";	
	$mediaFiles['BegBas'][4]		= "$siteURL/wp-content/uploads/begbas04.mp3";	
	$mediaFiles['BegBas'][5]		= "$siteURL/wp-content/uploads/begbas05.mp3";	
	$mediaFiles['BegBas'][6]		= "$siteURL/wp-content/uploads/begbas06.mp3";	
	$mediaFiles['BegBas'][7]		= "$siteURL/wp-content/uploads/begbas07.mp3";	
	$mediaFiles['BegBas'][8]		= "$siteURL/wp-content/uploads/begbas08.mp3";	
	$mediaFiles['BegBas'][9]		= "$siteURL/wp-content/uploads/begbas09.mp3";	
	$mediaFiles['BegBas'][10]		= "$siteURL/wp-content/uploads/begbas10.mp3";	
	$mediaFiles['BegBas'][11]		= "$siteURL/wp-content/uploads/begbas11.mp3";	
	$mediaFiles['BegBas'][12]		= "$siteURL/wp-content/uploads/begbas12.mp3";	
	$mediaFiles['BegBas'][13]		= "$siteURL/wp-content/uploads/begbas13.mp3";	
	$mediaFiles['BegBas'][14]		= "$siteURL/wp-content/uploads/begbas14.mp3";	
	$mediaFiles['BegBas'][15]		= "$siteURL/wp-content/uploads/begbas15.mp3";	
	$mediaFiles['BegBas'][16]		= "$siteURL/wp-content/uploads/begbas16.mp3";	
	$mediaFiles['BegBas'][17]		= "$siteURL/wp-content/uploads/begbas17.mp3";	
	$mediaFiles['BegBas'][18]		= "$siteURL/wp-content/uploads/begbas18.mp3";	
	$mediaFiles['BegBas'][19]		= "$siteURL/wp-content/uploads/begbas19.mp3";	
	$mediaFiles['BegBas'][20]		= "$siteURL/wp-content/uploads/begbas20.mp3";	
	$mediaFiles['BasInt'][1]		= "$siteURL/wp-content/uploads/basint31.mp3";	
	$mediaFiles['BasInt'][2]		= "$siteURL/wp-content/uploads/basint32.mp3";	
	$mediaFiles['BasInt'][3]		= "$siteURL/wp-content/uploads/basint33.mp3";	
	$mediaFiles['BasInt'][4]		= "$siteURL/wp-content/uploads/basint34.mp3";	
	$mediaFiles['BasInt'][5]		= "$siteURL/wp-content/uploads/basint35.mp3";	
	$mediaFiles['BasInt'][6]		= "$siteURL/wp-content/uploads/basint36.mp3";	
	$mediaFiles['BasInt'][7]		= "$siteURL/wp-content/uploads/basint37.mp3";	
	$mediaFiles['BasInt'][8]		= "$siteURL/wp-content/uploads/basint38.mp3";	
	$mediaFiles['BasInt'][9]		= "$siteURL/wp-content/uploads/basint39.mp3";	
	$mediaFiles['BasInt'][10]		= "$siteURL/wp-content/uploads/basint40.mp3";	
	$mediaFiles['BasInt'][11]		= "$siteURL/wp-content/uploads/basint41.mp3";	
	$mediaFiles['BasInt'][12]		= "$siteURL/wp-content/uploads/basint42.mp3";	
	$mediaFiles['BasInt'][13]		= "$siteURL/wp-content/uploads/basint43.mp3";	
	$mediaFiles['BasInt'][14]		= "$siteURL/wp-content/uploads/basint44.mp3";	
	$mediaFiles['BasInt'][15]		= "$siteURL/wp-content/uploads/basint45.mp3";	
	$mediaFiles['BasInt'][16]		= "$siteURL/wp-content/uploads/basint46.mp3";	
	$mediaFiles['BasInt'][17]		= "$siteURL/wp-content/uploads/basint47.mp3";	
	$mediaFiles['BasInt'][18]		= "$siteURL/wp-content/uploads/basint48.mp3";	
	$mediaFiles['BasInt'][19]		= "$siteURL/wp-content/uploads/basint49.mp3";	
	$mediaFiles['BasInt'][20]		= "$siteURL/wp-content/uploads/basint50.mp3";	
	$mediaFiles['IntAdv'][1]		= "$siteURL/wp-content/uploads/intadv51.mp3";	
	$mediaFiles['IntAdv'][2]		= "$siteURL/wp-content/uploads/intadv52.mp3";	
	$mediaFiles['IntAdv'][3]		= "$siteURL/wp-content/uploads/intadv53.mp3";	
	$mediaFiles['IntAdv'][4]		= "$siteURL/wp-content/uploads/intadv54.mp3";	
	$mediaFiles['IntAdv'][5]		= "$siteURL/wp-content/uploads/intadv55.mp3";	
	$mediaFiles['IntAdv'][6]		= "$siteURL/wp-content/uploads/intadv56.mp3";	
	$mediaFiles['IntAdv'][7]		= "$siteURL/wp-content/uploads/intadv57.mp3";	
	$mediaFiles['IntAdv'][8]		= "$siteURL/wp-content/uploads/intadv58.mp3";	
	$mediaFiles['IntAdv'][9]		= "$siteURL/wp-content/uploads/intadv59.mp3";	
	$mediaFiles['IntAdv'][10]		= "$siteURL/wp-content/uploads/intadv60.mp3";	
	$mediaFiles['IntAdv'][11]		= "$siteURL/wp-content/uploads/intadv61.mp3";	
	$mediaFiles['IntAdv'][12]		= "$siteURL/wp-content/uploads/intadv62.mp3";	
	$mediaFiles['IntAdv'][13]		= "$siteURL/wp-content/uploads/intadv63.mp3";	
	$mediaFiles['IntAdv'][14]		= "$siteURL/wp-content/uploads/intadv64.mp3";	
	$mediaFiles['IntAdv'][15]		= "$siteURL/wp-content/uploads/intadv65.mp3";	
	$mediaFiles['IntAdv'][16]		= "$siteURL/wp-content/uploads/intadv66.mp3";	
	$mediaFiles['IntAdv'][17]		= "$siteURL/wp-content/uploads/intadv67.mp3";	
	$mediaFiles['IntAdv'][18]		= "$siteURL/wp-content/uploads/intadv68.mp3";	
	$mediaFiles['IntAdv'][19]		= "$siteURL/wp-content/uploads/intadv69.mp3";	
	$mediaFiles['IntAdv'][20]		= "$siteURL/wp-content/uploads/intadv70.mp3";	
	$mediaFiles['Adv'][1]			= "$siteURL/wp-content/uploads/adv71.mp3";	
	$mediaFiles['Adv'][2]			= "$siteURL/wp-content/uploads/adv72.mp3";	
	$mediaFiles['Adv'][3]			= "$siteURL/wp-content/uploads/adv73.mp3";	
	$mediaFiles['Adv'][4]			= "$siteURL/wp-content/uploads/adv74.mp3";	
	$mediaFiles['Adv'][5]			= "$siteURL/wp-content/uploads/adv75.mp3";	
	$mediaFiles['Adv'][6]			= "$siteURL/wp-content/uploads/adv76.mp3";	
	$mediaFiles['Adv'][7]			= "$siteURL/wp-content/uploads/adv77.mp3";	
	$mediaFiles['Adv'][8]			= "$siteURL/wp-content/uploads/adv78.mp3";	
	$mediaFiles['Adv'][9]			= "$siteURL/wp-content/uploads/adv79.mp3";	
	$mediaFiles['Adv'][10]			= "$siteURL/wp-content/uploads/adv80.mp3";	
	$mediaFiles['Adv'][11]			= "$siteURL/wp-content/uploads/adv81.mp3";	
	$mediaFiles['Adv'][12]			= "$siteURL/wp-content/uploads/adv82.mp3";	
	$mediaFiles['Adv'][13]			= "$siteURL/wp-content/uploads/adv83.mp3";	
	$mediaFiles['Adv'][14]			= "$siteURL/wp-content/uploads/adv84.mp3";	
	$mediaFiles['Adv'][15]			= "$siteURL/wp-content/uploads/adv85.mp3";	
	$mediaFiles['Adv'][16]			= "$siteURL/wp-content/uploads/adv86.mp3";	
	$mediaFiles['Adv'][17]			= "$siteURL/wp-content/uploads/adv87.mp3";	
	$mediaFiles['Adv'][18]			= "$siteURL/wp-content/uploads/adv88.mp3";	
	$mediaFiles['Adv'][19]			= "$siteURL/wp-content/uploads/adv89.mp3";	
	$mediaFiles['Adv'][20]			= "$siteURL/wp-content/uploads/adv90.mp3";	
	
	$textFiles						= array();
	$textFiles['BegBas'][1]			= "let it go now";	
	$textFiles['BegBas'][2]			= "go for it son";	
	$textFiles['BegBas'][3]			= "we do not lie";	
	$textFiles['BegBas'][4]			= "be the one now";	
	$textFiles['BegBas'][5]			= "the big fat cat";	
	$textFiles['BegBas'][6]			= "my dog is wet";	
	$textFiles['BegBas'][7]			= "do not go cry";	
	$textFiles['BegBas'][8]			= "the old tin man";	
	$textFiles['BegBas'][9]			= "my old hot pot";	
	$textFiles['BegBas'][10]		= "my own wet cap";	
	$textFiles['BegBas'][11]		= "a tip top job";	
	$textFiles['BegBas'][12]		= "how are you now";	
	$textFiles['BegBas'][13]		= "go hit the gap";
	$textFiles['BegBas'][14]		= "she can sit up";	
	$textFiles['BegBas'][15]		= "go to the zoo";	
	$textFiles['BegBas'][16]		= "get the red bus";	
	$textFiles['BegBas'][17]		= "now pat his arm";	
	$textFiles['BegBas'][18]		= "the red fox den";	
	$textFiles['BegBas'][19]		= "why let him hop";	
	$textFiles['BegBas'][20]		= "get his own pay";	
	$textFiles['BasInt'][1]			= "tnx for the call";
	$textFiles['BasInt'][2]			= "go up and down";	
	$textFiles['BasInt'][3]			= "i kiss and tell";	
	$textFiles['BasInt'][4]			= "hope to cu agn";	
	$textFiles['BasInt'][5]			= "have a fun time";	
	$textFiles['BasInt'][6]			= "be safe and well";	
	$textFiles['BasInt'][7]			= "it is as you wish";	
	$textFiles['BasInt'][8]			= "why are they late";	
	$textFiles['BasInt'][9]			= "i can sing my song";	
	$textFiles['BasInt'][10]		= "my life goes on";	
	$textFiles['BasInt'][11]		= "it is cold here";	
	$textFiles['BasInt'][12]		= "give it a shot";	
	$textFiles['BasInt'][13]		= "i have an idea";	
	$textFiles['BasInt'][14]		= "you mark my word";	
	$textFiles['BasInt'][15]		= "take it from me";	
	$textFiles['BasInt'][16]		= "best of my love";	
	$textFiles['BasInt'][17]		= "it is all you need";	
	$textFiles['BasInt'][18]		= "they were just here";	
	$textFiles['BasInt'][19]		= "it is warm now";	
	$textFiles['BasInt'][20]		= "i work from home";	
	$textFiles['IntAdv'][1]			= "de ab4xm ok george, saying 73 for now";	
	$textFiles['IntAdv'][2]			= "de ad3pk wx is cloudy and cold temp 28 f";	
	$textFiles['IntAdv'][3]			= "de dl8y xyl is calling qrt for now";	
	$textFiles['IntAdv'][4]			= "de es6q during the day i am a nurse";	
	$textFiles['IntAdv'][5]			= "de g2nmp i am using a long wire";	
	$textFiles['IntAdv'][6]			= "de g4ke getting real serious about dxing";	
	$textFiles['IntAdv'][7]			= "de ja8tu ant hr is a hexbeam up 66 feet";	
	$textFiles['IntAdv'][8]			= "de k9wd wx foggy, temp 67 degrees f";	
	$textFiles['IntAdv'][9]			= "de kg9sd qth is az rig is ft1000 mp";	
	$textFiles['IntAdv'][10]		= "de kk3p using an omni 10x at 100 watts";	
	$textFiles['IntAdv'][11]		= "de aa8rt tnx for call alex name george";	
	$textFiles['IntAdv'][12]		= "de n0hr been a ham since my late teens";	
	$textFiles['IntAdv'][13]		= "de n8fsw ham since 1988 i was 34 years old";	
	$textFiles['IntAdv'][14]		= "de nw3t ant is g5rv es wx hr is very cold";	
	$textFiles['IntAdv'][15]		= "de py4c have a yagi up 40 feet";	
	$textFiles['IntAdv'][16]		= "de w9sfo name is thelma your rst is 569";	
	$textFiles['IntAdv'][17]		= "de wa3x weather here is cold and snowy";	
	$textFiles['IntAdv'][18]		= "de ws5x jim es tnx fer great qso";	
	$textFiles['IntAdv'][19]		= "de ww3pm what is your primary ant";	
	$textFiles['IntAdv'][20]		= "de xe2hg location hr is chicago, il";	
	$textFiles['Adv'][1]			= "de xe2hg location hr is chicago, il";	
	$textFiles['Adv'][2]			= "de ww3pm what is your primary ant";	
	$textFiles['Adv'][3]			= "de ws5x jim es tnx fer great qso";	
	$textFiles['Adv'][4]			= "de wa3x weather here is cold and snowy";	
	$textFiles['Adv'][5]			= "de w9sfo name is thelma your rst is 569";	
	$textFiles['Adv'][6]			= "de py4c have a yagi up 40 feet";	
	$textFiles['Adv'][7]			= "de nw3t ant is g5rv es wx hr is very cold";	
	$textFiles['Adv'][8]			= "de n8fsw ham since 1988 i was 34 years old";	
	$textFiles['Adv'][9]			= "de n0hr been a ham since my late teens";	
	$textFiles['Adv'][10]			= "de aa8rt tnx for call alex name george";	
	$textFiles['Adv'][11]			= "de kk3p using an omni 10x at 100 watts";	
	$textFiles['Adv'][12]			= "de kg9sd qth is az rig is ft1000 mp";	
	$textFiles['Adv'][13]			= "de k9wd wx foggy, temp 67 degrees f";	
	$textFiles['Adv'][14]			= "de ja8tu ant hr is a hexbeam up 66 feet";	
	$textFiles['Adv'][15]			= "de g4ke getting real serious about dxing";	
	$textFiles['Adv'][16]			= "de g2nmp i am using a long wire";	
	$textFiles['Adv'][17]			= "de es6q during the day i am a nurse";	
	$textFiles['Adv'][18]			= "de dl8y xyl is calling qrt for now";	
	$textFiles['Adv'][19]			= "de ad3pk wx is cloudy and cold temp 28 f";	
	$textFiles['Adv'][20]			= "de ab4xm ok george, saying 73 for now";	

	if (!in_array($inp_level,$levelArray)) {
		return FALSE;
	}
	if ($inp_mode == 'random' || $inp_mode == 'Random') {
		$playNumber					= mt_rand(1,20);
	} else {
		$playNumber					= intval($inp_mode);
	}
	
	if ($doDebug) {
		echo "playNumber: $playNumber<br />";
	}
	$playFile						= $mediaFiles[$inp_level][$playNumber];	
	$playText						= $textFiles[$inp_level][$playNumber];

//	$myArray						= explode("/",$playFile);
//	$myInt							= count($myArray) -1;
//	$thisFile						= $myArray[$myInt];
//	$fileCount						= MediaFileExists($thisFile);

//	if ($fileCount > 0) {
		if ($playOnce == 'Y') {
			$result		= array("<figure class=\"wp-block-audio audio-play-once-true\">
<audio class=\"autofocus\" data-focus=\"audioClip$id_number\" controls src=\"$playFile\"></audio></figure>",
							$playFile,
							$playNumber,
							$playText);
		} else {
			$result		= array("<figure class=\"wp-block-audio\">
<audio class=\"autofocus\" data-focus=\"audioClip$id_number\" controls src=\"$playFile\"></audio></figure>",
							$playFile,
							$playNumber,
							$playText);
		}
							
		if ($doDebug) {
			echo "Returning result:<br /><pre>";
			print_r($result);
			echo "</pre><br />";
		}
//	} else {
//		$result		= array(FALSE,"Media file does not exist",$playFile,"");
//	}

	return $result;

}
add_action ('playAudioFile', 'playAudioFile');