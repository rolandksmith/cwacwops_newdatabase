function advisor_registration_v3_func() {

/*	Advisor Sign-up
 *
 *
 	Modified 19June21 by Roland for the new audit log process
 	Modified 9July21 by Roland to handle situation where an advisor inadvertently refused a class
 	Modified 20Aug21 by Roland to add verification process
 	Modified 4Jan2022 by Roland to move to tables from pods
 	Modified 2Oct2022 by Roland to use the new timezone process
 	Modified 3Feb2023 by Roland to fix section that checks to see if the advisor is also a student
 	Modified 22Feb23 by Roland to use the full advisorClass format
 	Modified 22Mar23 by Roland to use the updateAdvisor and updateClass functions
	Modified 15Apr23 by Roland to correct action log handling
	Modified 15June23 by Roland to allow signup for nextSemester if advisor has completed evaluations
	Modified 12Jul23 by Roland to use consolidated tables
	Modified 31Aug23 by Roland to turn off debug and testmode if the user is not signed in
	Modified 28Oct23 by Roland to allow changing the email address and phone number
	Modifed 23Nov23 by Roland for advisor portal process and to verify a deletion before deleting
*/

	global $wpdb, $advisorTableName,$advisorClassTableName,$daysToSemester,$doDebug,$testMode,$theURL, $allowSignup;


	$doDebug						= FALSE;
	$testMode						= FALSE;
	$maintenanceMode				= FALSE;
	$initializationArray 			= data_initialization_func();
	$validUser 						= $initializationArray['validUser'];
	if ($validUser == 'N') {				// turn off debug and testmode
		$doDebug					= FALSE;
		$testMode					= FALSE;
	}
	if ($doDebug) {
		echo "Initialization Array:<br /><pre>";
		print_r($initializationArray);
		echo "</pre><br />";
	}
	$defaultClassSize			= $initializationArray['defaultClassSize'];
	$currentTimestamp			= $initializationArray['currentTimestamp'];
	$validTestmode				= $initializationArray['validTestmode'];
	$userName					= $initializationArray['userName'];
	$currentSemester			= $initializationArray['currentSemester'];
	$prevSemester				= $initializationArray['prevSemester'];
	$nextSemester				= $initializationArray['nextSemester'];
	$semesterTwo				= $initializationArray['semesterTwo'];
	$semesterThree				= $initializationArray['semesterThree'];
	$semesterFour				= $initializationArray['semesterFour'];
	$daysToSemester				= intval($initializationArray['daysToSemester']);
	$userRole					= $initializationArray['userRole'];
	$siteURL					= $initializationArray['siteurl'];
	$allowSignup				= FALSE;
	


//	if ($doDebug) {
		ini_set('display_errors','1');
		error_reporting(E_ALL);	
//	}

/// get the time that the process started
	$startingMicroTime			= microtime(TRUE);

	$strPass						= "1";
	$inp_recdel						= '';
	$inp_mode						= '';
	$inp_verbose					= '';
	$fieldTest						= array('action_log','post_status','post_title','control_code');
	$jobname						= "Advisor Registration V3";
	$advisor_ID 					=   '';   // id
	$advisor_post_title 			=   '';   // post_title
	$advisor_select_sequence 		=   '';   // select_sequence
	$advisor_call_sign 				=   '';   // call_sign
	$advisor_first_name 			=   '';   // first_name
	$advisor_last_name 				=   '';   // last_name
	$advisor_email 					=   '';   // email
	$advisor_ph_code				= 	'';	  // phone code
	$advisor_phone 					=   '';   // phone
	$advisor_text_message			=	'';   // text message
	$advisor_city 					=   '';   // city
	$advisor_state 					=   '';   // state
	$advisor_zip_code 				=   '';   // zip_code
	$advisor_country 				=   '';   // country
	$advisor_country_code			= 	'';   // country code
	$advisor_time_zone 				=   '';   // time_zone
	$advisor_timezone_id			=	'';	  // timezone id
	$advisor_timezone_offset		=	'';   // timezone offset
	$advisor_whatsapp				= 	'';
	$advisor_signal					=	'';
	$advisor_telegram				=	'';
	$advisor_messenger				=	'';
	$advisor_semester 				=   '';   // semester
	$advisor_survey_score 			=   '';   // survey_score
	$advisor_languages		 		=   '';   // languages
	$advisor_fifo_date 				=   '';   // fifo_date
	$advisor_welcome_email_date 	=   '';   // welcome_email_date
	$advisor_verify_email_date 		=   '';   // verify_email_date
	$advisor_verify_email_number 	=   '';   // verify_email_number
	$advisor_verify_response 		=   '';   // verify_response
	$advisor_action_log 			=   '';   // action_log
	$advisor_class_verified 		=   '';   // class_verified
	$advisor_evaluations_complete	=   '';   // evaluations_complete
	$advisor_control_code			= 	'';
	$increment						=   0;
	$timezone						= 	"";

	
	
	$inp_ID 						=   '';   // id
	$inp_post_title 				=   '';   // post_title
	$inp_select_sequence 			=   '';   // select_sequence
	$inp_callsign 					=   '';   // call_sign
	$inp_firstname 					=   '';   // first_name
	$inp_lastname 					=   '';   // last_name
	$inp_email 						=   '';   // email
	$inp_ph_code					=	'';
	$inp_phone 						=   '';   // phone
	$inp_text_message				=	'';   // text message
	$inp_city 						=   '';   // city
	$inp_state 						=   '';   // state
	$inp_zip 						=   '';   // zip_code
	$inp_country 					=   '';   // country
	$inp_countryb					= 	'';
	$inp_country_code				=	'';
	$inp_timezone 					=   '';   // time_zone
	$inp_timezone_id				=	'';
	$inp_timezone_offset			=	'';
	$inp_whatsapp					=	'';
	$inp_signal						=	'';
	$inp_telegram					=	'';
	$inp_messenger					=	'';
	$inp_semester 					=   '';   // semester
	$inp_survey_score 				=   '';   // survey_score
	$inp_languages_spoken 			=   '';   // languages
	$inp_fifo_date 					=   '';   // fifo_date
	$inp_welcome_email_date 		=   '';   // welcome_email_date
	$inp_verify_email_date 			=   '';   // verify_email_date
	$inp_verify_email_number 		=   '';   // verify_email_number
	$inp_verify_response 			=   '';   // verify_response
	$inp_action_log 				=   '';   // action_log
	$inp_class_verified 			=   '';   // class_verified
	$inp_evaluations_complete		=   '';   // evaluations_complete
	$inp_control_code				= 	''; 
	$inp_level	 					= '';
	$inp_teaching_days	 			= '';
	$inp_times	 					= '';
	$inp_class_size 				= '';
	$inp_bypass						= 'N';
	$browser_timezone_id			= '';
	$advisorClass_class_incomplete	= '';
	$noUpdate						= FALSE;
	$newID							= 0;
	$theURL									= "$siteURL/cwa-advisor-registration/";
	$actionDate								= date('dMy H:i',$currentTimestamp);
	$log_actionDate							= date('Y-m-d H:i',$currentTimestamp);	
	$pageSource								= "";
	
	
	$inp_classdel					= '';
	$inp_nbrClasses					= 0;
	$classID						= 0;



	$timeConverter			= array(
'0600'=>'6:00am',
'0630'=>'6:30am',
'0700'=>'7:00am',
'0730'=>'7:30am',
'0800'=>'8:00am',
'0830'=>'8:30am',
'0900'=>'9:00am',
'0930'=>'9:30am',
'1000'=>'10:00am',
'1030'=>'10:30am',
'1100'=>'11:00am',
'1130'=>'11:30am',
'1200'=>'Noon',
'1230'=>'12:30pm',
'1300'=>'1:00pm',
'1330'=>'1:30pm',
'1400'=>'2:00pm',
'1430'=>'2:30pm',
'1500'=>'3:00pm',
'1530'=>'3:30pm',
'1600'=>'4:00pm ',
'1630'=>'4:30pm',
'1700'=>'5:00pm',
'1730'=>'5:30pm',
'1800'=>'6:00pm',
'1830'=>'6:30pm',
'1900'=>'7:00pm',
'1930'=>'7:30pm',
'2000'=>'8:00pm ',
'2030'=>'8:30pm',
'2100'=>'9:00pm',
'2130'=>'9"30pm',
'2200'=>'10:00pm',
'2230'=>'10:30pm');

	

// get the input information
	if (isset($_REQUEST)) {
		foreach($_REQUEST as $str_key => $str_value) {
			if ($doDebug) {
				if (!is_array($str_value)) {
					echo "Key: $str_key | Value: $str_value <br />\n";
				} else {
					echo "Key: $str_key (array)<br />\n";
				}
			}
			if ($str_key 		== "strpass") {
				$strPass		 = $str_value;
				$strPass		 = filter_var($strPass,FILTER_UNSAFE_RAW);
			}
			if ($str_key 				== 'enstr') {
				$enstr					= $str_value;
				$stringToPass			= base64_decode($enstr);
				$myArray				= explode("&",$stringToPass);
				foreach($myArray as $myValue) {
					$thisArray			= explode("=",$myValue);
					${$thisArray[0]}	= filter_var($thisArray[1],FILTER_UNSAFE_RAW);
					if ($doDebug) {
						echo "ENC Key: $thisArray[0] | Value: $thisArray[1]<br />\n";
					}
					if ($thisArray[0] == 'semester') {
						$allowSignup	= TRUE;
						$inp_semester	= $thisArray[1];
						if ($doDebug) {
							echo "allowSignup is TRUE<br />inp_semester set to $inp_semester<br />";
						}
					}
				}
			}
			if ($str_key 		== "inp_mode") {
				$inp_mode	 	= $str_value;
				$inp_mode	 	= filter_var($inp_mode,FILTER_UNSAFE_RAW);
				if ($inp_mode == 'TESTMODE') {
					$testMode	= TRUE;
//					$doDebug	= TRUE;
				}
			}
			if ($str_key 		== "inp_verbose") {
				$inp_verbose	 = $str_value;
				$inp_verbose	 = filter_var($inp_verbose,FILTER_UNSAFE_RAW);
				if ($inp_verbose == 'Y') {
					$doDebug	= TRUE;
				}
			}
			if ($str_key 		== "classID") {
				$classID	 = $str_value;
				$classID	 = filter_var($classID,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "newID") {
				$newID	 		= $str_value;
				$newID	 		= filter_var($newID,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "inp_semester") {
				$inp_semester	 = $str_value;
				$inp_semester	 = filter_var($inp_semester,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "classcount") {
				$classcount	 = $str_value;
				$classcount	 = filter_var($classcount,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "inp_recdel") {
				$inp_recdel	 = $str_value;
				$inp_recdel	 = filter_var($inp_recdel,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "inp_callsign") {
				$inp_callsign	 = $str_value;
				$inp_callsign	 = strtoupper(filter_var($inp_callsign,FILTER_UNSAFE_RAW));
			}
			if ($str_key 		== "call_sign") {
				$inp_callsign	 = $str_value;
				$inp_callsign	 = strtoupper(filter_var($inp_callsign,FILTER_UNSAFE_RAW));
			}
			if ($str_key == "inp_firstname") {
				$inp_firstname 	= $str_value;
				$inp_firstname 	= filter_var($inp_firstname,FILTER_UNSAFE_RAW);
			}
			if ($str_key == "inp_lastname") {
				$inp_lastname 		= $str_value;
				$inp_lastname		= no_magic_quotes($inp_lastname);
//				$inp_lastname 		= filter_var($inp_lastname,FILTER_UNSAFE_RAW);
			}
			if ($str_key == "inp_email") {
				$inp_email 			= $str_value;
				$inp_email 			= filter_var($inp_email,FILTER_UNSAFE_RAW);
			}
			if ($str_key == "inp_phone") {
				$inp_phone 			= $str_value;
				$inp_phone 			= filter_var($inp_phone,FILTER_UNSAFE_RAW);
			}
			if ($str_key == "inp_ph_code") {
				$inp_ph_code 			= $str_value;
				$inp_ph_code 			= filter_var($inp_ph_code,FILTER_UNSAFE_RAW);
			}
			if ($str_key == "inp_text_message") {
				$inp_text_message 			= $str_value;
				$inp_text_message 			= filter_var($inp_text_message,FILTER_UNSAFE_RAW);
			}
			if ($str_key == "inp_city") {
				$inp_city 			= $str_value;
				$inp_city 			= filter_var($inp_city,FILTER_UNSAFE_RAW);
			}
			if ($str_key == "inp_state") {
				$inp_state 			= $str_value;
				$inp_state 			= filter_var($inp_state,FILTER_UNSAFE_RAW);
			}
			if ($str_key == "inp_zip") {
				$inp_zip 			= $str_value;
				$inp_zip 			= filter_var($inp_zip,FILTER_UNSAFE_RAW);
			}
			if ($str_key == "inp_country") {
				$inp_country 		= $str_value;
				$inp_country 		= filter_var($inp_country,FILTER_UNSAFE_RAW);
			}
			if ($str_key == "inp_countryb") {
				$inp_country 		= $str_value;
				$inp_country 		= filter_var($inp_country,FILTER_UNSAFE_RAW);
			}
			if ($str_key == "inp_country_code") {
				$inp_country_code 			= $str_value;
				$inp_country_code 			= filter_var($inp_country_code,FILTER_UNSAFE_RAW);
			}
			if ($str_key == "inp_timezone") {
				$inp_timezone 		= $str_value;
				$inp_timezone 		= filter_var($inp_timezone,FILTER_UNSAFE_RAW);
			}
			if ($str_key == "inp_timezone_id") {
				$inp_timezone_id 			= $str_value;
				$inp_timezone_id 			= filter_var($inp_timezone_id,FILTER_UNSAFE_RAW);
			}
			if ($str_key == "inp_timezone_offset") {
				$inp_timezone_offset 			= $str_value;
				$inp_timezone_offset 			= filter_var($inp_timezone_offset,FILTER_UNSAFE_RAW);
			}
			if ($str_key == "inp_whatsapp") {
				$inp_whatsapp 			= $str_value;
				$inp_whatsapp 			= filter_var($inp_whatsapp,FILTER_UNSAFE_RAW);
			}
			if ($str_key == "inp_signal") {
				$inp_signal 			= $str_value;
				$inp_signal 			= filter_var($inp_signal,FILTER_UNSAFE_RAW);
			}
			if ($str_key == "inp_telegram") {
				$inp_telegram 			= $str_value;
				$inp_telegram 			= filter_var($inp_telegram,FILTER_UNSAFE_RAW);
			}
			if ($str_key == "inp_messenger") {
				$inp_messenger 			= $str_value;
				$inp_messenger 			= filter_var($inp_messenger,FILTER_UNSAFE_RAW);
			}
			if ($str_key == "inp_languages") {
				$inp_languages 		= $str_value;
				$inp_languages 		= filter_var($inp_languages,FILTER_UNSAFE_RAW);
			}
			if ($str_key == "inp_number_classes") {
				$inp_number_classes = $str_value;
				$inp_number_classes = filter_var($inp_number_classes,FILTER_UNSAFE_RAW);
			}
			if ($str_key == "inp_language") {
				$inp_language = $str_value;
				$inp_language = filter_var($inp_language,FILTER_UNSAFE_RAW);
			}
			if ($str_key == "inp_id") {
				$inp_id = $str_value;
				$inp_id = filter_var($inp_id,FILTER_UNSAFE_RAW);
			}
			if($str_key == "inp_level") {
				$inp_level = $str_value;
				$inp_level = filter_var($inp_level,FILTER_UNSAFE_RAW);
			}
			if($str_key == "inp_teaching_days") {
				$inp_teaching_days = $str_value;
				$inp_teaching_days = filter_var($inp_teaching_days,FILTER_UNSAFE_RAW);
				$inp_class_schedule_days 	= $inp_teaching_days;
			}
			if($str_key == "inp_class_schedule_days") {
				$inp_class_schedule_days = $str_value;
				$inp_class_schedule_days = filter_var($inp_class_schedule_days,FILTER_UNSAFE_RAW);
				$inp_teaching_days 	= $inp_class_schedule_days;
			}
			if($str_key == "inp_times") {
				$inp_times = $str_value;
				$inp_times = filter_var($inp_times,FILTER_UNSAFE_RAW);
				$inp_class_schedule_times	= $inp_times;
			}
			if($str_key == "inp_class_schedule_times") {
				$inp_class_schedule_times = $str_value;
				$inp_class_schedule_times = filter_var($inp_class_schedule_times,FILTER_UNSAFE_RAW);
				$inp_times	= $inp_class_schedule_times;
			}
			if($str_key == "inp_class_size") {
				$inp_class_size = $str_value;
				$inp_class_size = filter_var($inp_class_size,FILTER_UNSAFE_RAW);
			}
			if($str_key == "inp_classdel") {
				$inp_classdel = $str_value;
				$inp_classdel = filter_var($inp_classdel,FILTER_UNSAFE_RAW);
			}
			if($str_key == "inp_classrecdel") {
				$inp_classrecdel = $str_value;
				$inp_classrecdel = filter_var($inp_classrecdel,FILTER_UNSAFE_RAW);
			}
			if($str_key == "inp_advisor_id") {
				$inp_advisor_id = $str_value;
				$inp_advisor_id = filter_var($inp_advisor_id,FILTER_UNSAFE_RAW);
			}
			if($str_key == "inp_nbrClasses") {
				$inp_nbrClasses = $str_value;
				$inp_nbrClasses = filter_var($inp_nbrClasses,FILTER_UNSAFE_RAW);
			}
			if($str_key == "timezone") {
				$timezone = $str_value;
				$timezone = filter_var($timezone,FILTER_UNSAFE_RAW);
				$browser_timezone_id	= $timezone;
			}
			if($str_key == "inp_bypass") {
				$inp_bypass = $str_value;
				$inp_bypass = filter_var($inp_bypass,FILTER_UNSAFE_RAW);
			}
			if ($str_key == 'browser_timezone_id') {
				$browser_timezone_id = $str_value;
				$browser_timezone_id = filter_var($browser_timezone_id,FILTER_UNSAFE_RAW);
			}
			if ($str_key == 'pageSource') {
				$pageSource = $str_value;
				$pageSource = filter_var($pageSource,FILTER_UNSAFE_RAW);
			}
			if ($str_key == 'allowSignup') {
				$allowSignup = $str_value;
				$allowSignup = filter_var($allowSignup,FILTER_UNSAFE_RAW);
			}
		}
	}
	if ($inp_mode		== 'TESTMODE' || $inp_mode == 'tm') {
		$testMode		= TRUE;
	}
	if ($inp_verbose == 'Y') {
		$doDebug		= TRUE;
	}

$countryCheckedArray = array(
'AR',
'AU',
'AT',
'BE',
'BR',
'CA',
'CL',
'CN',
'CZ',
'DK',
'FI',
'FR',
'DE',
'GB',
'GR',
'IN',
'IE',
'IL',
'IT',
'JP',
'KR',
'MX',
'NL',
'NZ',
'NO',
'PH',
'PL',
'PT',
'PR',
'KR',
'RU',
'ZA',
'ES',
'SE',
'CH',
'US');

	
$AR_checked = '';
$AU_checked = '';
$AT_checked = '';
$BS_checked = '';
$BH_checked = '';
$BY_checked = '';
$BE_checked = '';
$BA_checked = '';
$BR_checked = '';
$BN_checked = '';
$BG_checked = '';
$CA_checked = '';
$CL_checked = '';
$CN_checked = '';
$CO_checked = '';
$CR_checked = '';
$CU_checked = '';
$CZ_checked = '';
$DK_checked = '';
$DO_checked = '';
$GB_checked = '';
$EE_checked = '';
$FI_checked = '';
$FR_checked = '';
$DE_checked = '';
$GB_checked = '';
$GR_checked = '';
$IN_checked = '';
$ID_checked = '';
$IE_checked = '';
$IL_checked = '';
$IT_checked = '';
$JP_checked = '';
$JO_checked = '';
$KE_checked = '';
$KR_checked = '';
$LV_checked = '';
$MX_checked = '';
$MD_checked = '';
$MC_checked = '';
$NL_checked = '';
$NZ_checked = '';
$GB_checked = '';
$NO_checked = '';
$PE_checked = '';
$PH_checked = '';
$PL_checked = '';
$PT_checked = '';
$PR_checked = '';
$KR_checked = '';
$MD_checked = '';
$RO_checked = '';
$RU_checked = '';
$SA_checked = '';
$GB_checked = '';
$RS_checked = '';
$SG_checked = '';
$SK_checked = '';
$SI_checked = '';
$ZA_checked = '';
$ES_checked = '';
$SE_checked = '';
$CH_checked = '';
$TH_checked = '';
$TT_checked = '';
$TR_checked = '';
$GB_checked = '';
$US_checked = '';
$GB_checked = '';
$XX_checked = '';
$AF_checked = '';
$AL_checked = '';
$DZ_checked = '';
$AX_checked = '';
$AS_checked = '';
$AD_checked = '';
$AO_checked = '';
$AI_checked = '';
$AQ_checked = '';
$AG_checked = '';
$AM_checked = '';
$AW_checked = '';
$AZ_checked = '';
$BD_checked = '';
$BB_checked = '';
$BZ_checked = '';
$BJ_checked = '';
$BM_checked = '';
$BT_checked = '';
$BO_checked = '';
$BQ_checked = '';
$BW_checked = '';
$BV_checked = '';
$IO_checked = '';
$BF_checked = '';
$BI_checked = '';
$KH_checked = '';
$CM_checked = '';
$CV_checked = '';
$KY_checked = '';
$CF_checked = '';
$TD_checked = '';
$CX_checked = '';
$CC_checked = '';
$KM_checked = '';
$CD_checked = '';
$CG_checked = '';
$CK_checked = '';
$HR_checked = '';
$CW_checked = '';
$CY_checked = '';
$KP_checked = '';
$DJ_checked = '';
$DM_checked = '';
$EC_checked = '';
$EG_checked = '';
$GQ_checked = '';
$ER_checked = '';
$ET_checked = '';
$FK_checked = '';
$FO_checked = '';
$FM_checked = '';
$FJ_checked = '';
$GF_checked = '';
$PF_checked = '';
$TF_checked = '';
$GA_checked = '';
$GM_checked = '';
$GE_checked = '';
$GH_checked = '';
$GI_checked = '';
$GL_checked = '';
$GD_checked = '';
$GP_checked = '';
$GU_checked = '';
$GT_checked = '';
$GG_checked = '';
$GW_checked = '';
$GN_checked = '';
$GY_checked = '';
$HT_checked = '';
$HM_checked = '';
$VA_checked = '';
$HN_checked = '';
$HK_checked = '';
$HU_checked = '';
$IS_checked = '';
$IR_checked = '';
$IQ_checked = '';
$IR_checked = '';
$IM_checked = '';
$CI_checked = '';
$JM_checked = '';
$JE_checked = '';
$KZ_checked = '';
$KI_checked = '';
$KW_checked = '';
$KG_checked = '';
$LA_checked = '';
$LA_checked = '';
$LB_checked = '';
$LS_checked = '';
$LR_checked = '';
$LY_checked = '';
$LI_checked = '';
$LT_checked = '';
$LU_checked = '';
$MO_checked = '';
$MK_checked = '';
$MG_checked = '';
$MW_checked = '';
$MY_checked = '';
$MV_checked = '';
$ML_checked = '';
$MT_checked = '';
$MH_checked = '';
$MQ_checked = '';
$MR_checked = '';
$MU_checked = '';
$YT_checked = '';
$FM_checked = '';
$MN_checked = '';
$ME_checked = '';
$MS_checked = '';
$MA_checked = '';
$MZ_checked = '';
$MM_checked = '';
$NA_checked = '';
$NR_checked = '';
$NP_checked = '';
$NC_checked = '';
$NI_checked = '';
$NE_checked = '';
$NG_checked = '';
$NU_checked = '';
$NF_checked = '';
$MP_checked = '';
$OM_checked = '';
$PK_checked = '';
$PW_checked = '';
$PS_checked = '';
$PA_checked = '';
$PG_checked = '';
$PY_checked = '';
$PN_checked = '';
$QA_checked = '';
$RE_checked = '';
$RW_checked = '';
$BL_checked = '';
$SH_checked = '';
$KN_checked = '';
$LC_checked = '';
$MF_checked = '';
$PM_checked = '';
$VC_checked = '';
$WS_checked = '';
$SM_checked = '';
$ST_checked = '';
$SN_checked = '';
$SC_checked = '';
$SL_checked = '';
$SX_checked = '';
$SB_checked = '';
$SO_checked = '';
$GS_checked = '';
$SS_checked = '';
$LK_checked = '';
$PS_checked = '';
$SD_checked = '';
$SR_checked = '';
$SJ_checked = '';
$SZ_checked = '';
$SY_checked = '';
$TW_checked = '';
$TJ_checked = '';
$CD_checked = '';
$TL_checked = '';
$TG_checked = '';
$TK_checked = '';
$TO_checked = '';
$TN_checked = '';
$TM_checked = '';
$TC_checked = '';
$TV_checked = '';
$UG_checked = '';
$UA_checked = '';
$AE_checked = '';
$TZ_checked = '';
$UM_checked = '';
$UY_checked = '';
$UZ_checked = '';
$VU_checked = '';
$VE_checked = '';
$VN_checked = '';
$VG_checked = '';
$VI_checked = '';
$WF_checked = '';
$EH_checked = '';
$YE_checked = '';
$ZM_checked = '';
$ZW_checked = '';




function getAdvisorInfoToDisplay($inp_callsign,$inp_semester,$noUpdate) {

	global $wpdb, $advisorTableName,$advisorClassTableName,$doDebug,$testMode,$theURL, $allowSignup;

/*	return information:
		array(result,count)

*/
	if ($doDebug) {
		echo "<br />Entered getAdvisorInfoToDispay with $inp_callsign and $inp_semester<br />";
	}

	$result					= '';
	$inp_nbrClasses			= 0;
	$classcount				= 0;
	$daysToSemester			= days_to_semester($inp_semester);
	
	$cwa_advisor			= $wpdb->get_results("select * from $advisorTableName 
													where call_sign='$inp_callsign' 
													  and semester='$inp_semester' 
													order by call_sign");
	if ($cwa_advisor === FALSE) {
			$myError			= $wpdb->last_error;
			$myQuery			= $wpdb->last_query;
			if ($doDebug) {
				echo "Reading $advisorTableName table failed<br />
					  wpdb->last_query: $myQuery<br />
					  wpdb->last_error: $myError<br />";
			}
			$errorMsg			= "$jobname Reading $advisorTableName table failed. <p>SQL: $myQuery</p><p> Error: $myError</p>";
			sendErrorEmail($errorMsg);
	} else {
		$numARows			= $wpdb->num_rows;
		if ($doDebug) {
			$myStr			= $wpdb->last_query;
			echo "ran $myStr<br />and found $numARows rows in $advisorTableName table<br />";
		}
		if ($numARows > 0) {
			foreach ($cwa_advisor as $advisorRow) {
				$advisor_ID							= $advisorRow->advisor_id;
				$advisor_select_sequence 			= $advisorRow->select_sequence;
				$advisor_call_sign 					= strtoupper($advisorRow->call_sign);
				$advisor_first_name 				= $advisorRow->first_name;
				$advisor_last_name 					= stripslashes($advisorRow->last_name);
				$advisor_email 						= strtolower($advisorRow->email);
				$advisor_phone						= $advisorRow->phone;
				$advisor_ph_code					= $advisorRow->ph_code;				// new
				$advisor_text_message 				= $advisorRow->text_message;
				$advisor_city 						= $advisorRow->city;
				$advisor_state 						= $advisorRow->state;
				$advisor_zip_code 					= $advisorRow->zip_code;
				$advisor_country 					= $advisorRow->country;
				$advisor_country_code				= $advisorRow->country_code;		// new
				$advisor_whatsapp					= $advisorRow->whatsapp_app;		// new
				$advisor_signal						= $advisorRow->signal_app;			// new
				$advisor_telegram					= $advisorRow->telegram_app;		// new
				$advisor_messenger					= $advisorRow->messenger_app;		// new
				$advisor_time_zone 					= $advisorRow->time_zone;
				$advisor_timezone_id				= $advisorRow->timezone_id;			// new
				$advisor_timezone_offset			= $advisorRow->timezone_offset;		// new
				$advisor_semester 					= $advisorRow->semester;
				$advisor_survey_score 				= $advisorRow->survey_score;
				$advisor_languages 					= $advisorRow->languages;
				$advisor_fifo_date 					= $advisorRow->fifo_date;
				$advisor_welcome_email_date 		= $advisorRow->welcome_email_date;
				$advisor_verify_email_date 			= $advisorRow->verify_email_date;
				$advisor_verify_email_number 		= $advisorRow->verify_email_number;
				$advisor_verify_response 			= strtoupper($advisorRow->verify_response);
				$advisor_action_log 				= $advisorRow->action_log;
				$advisor_class_verified 			= $advisorRow->class_verified;
				$advisor_control_code 				= $advisorRow->control_code;
				$advisor_date_created 				= $advisorRow->date_created;
				$advisor_date_updated 				= $advisorRow->date_updated;

				$advisor_last_name 					= no_magic_quotes($advisor_last_name);

				if ($doDebug) {
					echo "<br />Have an advisor record for $inp_callsign<br />";
				}
				
				$inp_mode				= '';
				if ($testMode) {
					$inp_mode			= 'TESTMODE';
				}
				$inp_verbose			= 'N';
				if ($doDebug) {
					$inp_verbose		= 'Y';
				}
				$stringToPass	= "inp_callsign=$advisor_call_sign&inp_email=$advisor_email&inp_phone=$advisor_phone&inp_mode=$inp_mode&inp_verbose=$inp_verbose&allowSignup=$allowSignup&inp_semester=$inp_semester";
				$enstr			= base64_encode($stringToPass);
				$result			.= "<p>You have signed up as an advisor. 
									Your current information is as follows. To update, delete, or add information, click 
									on the appropriate link</p>
									<p><button class='formInputButton' onClick=\"window.print()\">Print this page</button></p>
									<table style='width:900px;'>
									<tr><td style='vertical-align:top;width:300px;'><b>Call Sign</b><br />
											$advisor_call_sign</td>
										<td style='vertical-align:top;width:300px;'><b>Last Name</b><br />
											$advisor_last_name</td>
										<td style='vertical-align:top;'><b>First Name</b><br />
											$advisor_first_name</td></tr>
									<tr><td style='vertical-align:top;'><b>Semester</b><br />
											$advisor_semester</td>
										<td style='vertical-align:top;'><b>Email</b><br />
											$advisor_email</td>
										<td style='vertical-align:top;'><b>Phone</b>
											$advisor_ph_code $advisor_phone<br />
											Can this phone receive text messages?<br />
											$advisor_text_message</td></tr>
									<tr><td style='vertical-align:top;'><b>City</b><br />
											$advisor_city</td>
										<td style='vertical-align:top;'><b>State / Region / Province</b><br />
											$advisor_state</td>
										<td style='vertical-align:top;'><b>Zip / Postal Code</b> Zip/Postal Code is required for US and Canadian residents<br />
											$advisor_zip_code<br />
											</td></tr>

									<tr><td colspan='3' style='vertical-align:top;'>
											<b>Country</b><br />
											$advisor_country ($advisor_country_code)</td>
										<td></td>
										<td></td></tr>
									<tr><td style='vertical-align:top;'><b>Languages Spoken</b><br />
											$advisor_languages</td>
										<td style='vertical-align:top;'>&nbsp;</td>
										<td style='vertical-align:top;'>&nbsp;</td></tr>
									<tr><td colspan='3'>If you use any of these other messaging services, you may optionally enter your user id</td></tr>
									<tr><td colspan='3'>
										<table>
											<tr><td style='vertical-align:top;'><b>Whatsapp</b><br />
													$advisor_whatsapp</td>
												<td style='vertical-align:top;'><b>Signal</b><br />
													$advisor_signal</td>
												<td style='vertical-align:top;'><b>Telegram</b><br />
													$advisor_telegram</td>
												<td style='vertical-align:top;'><b>Messenger</b><br />
													$advisor_messenger'</td></tr>
										</table></td></tr>
									<tr>";
				if (!$noUpdate) {					/// can update the record
					$result	.= "<td><form method='post' action='$theURL' 
									name='advisor_option1_form' ENCTYPE='multipart/form-data'>
									<input type='hidden' name='enstr' value='$enstr'>
									<input type='hidden' name='strpass' value='10'>
									<input class='formInputButton' type='submit' value='Update Advisor Information'>
									</form></td>
									<td><form method='post' action='$theURL' 
									name='advisor_option2_form' ENCTYPE='multipart/form-data'>
									<input type='hidden' name='enstr' value='$enstr'>
									<input type='hidden' name='strpass' value='20'>
									<input class='formInputButton' type='submit' 
									onclick=\"return confirm('Are you sure you want to delete this the advisor and class records?');\" value='Delete Advisor and Classes'>
									</form></td>";
				} else {
					if ($doDebug) {
						echo "not allowing updates to the advisor record<br />";
					}
					$result	.= "<td colspan=2'>If you need changes to this record, please contact the appropriate 
									person at <a href='https://cwops.org/cwa-class-resolution/'>CWA Class Resolution</a>.</td>";
				}

				$sql						= "select * from $advisorClassTableName 
												where advisor_call_sign='$inp_callsign' 
												  and semester='$inp_semester' 
												order by sequence";
				$cwa_advisorclass			= $wpdb->get_results($sql);
				if ($cwa_advisorclass === FALSE) {
					handleWPDBError($jobname,$doDebug);
				} else {
					$numACRows				= $wpdb->num_rows;
					if ($doDebug) {
						$myStr				= $wpdb->last_query;
						echo "ran $myStr<br />and found $numACRows rows in $advisorClassTableName<br />";
					}
					$firstTime										= TRUE;
					if ($numACRows > 0) {
						foreach ($cwa_advisorclass as $advisorClassRow) {
							$advisorClass_ID				 		= $advisorClassRow->advisorclass_id;
							$advisorClass_advisor_call_sign 		= $advisorClassRow->advisor_call_sign;
							$advisorClass_advisor_first_name 		= $advisorClassRow->advisor_first_name;
							$advisorClass_advisor_last_name 		= stripslashes($advisorClassRow->advisor_last_name);
							$advisorClass_advisor_id 				= $advisorClassRow->advisor_id;
							$advisorClass_sequence 					= $advisorClassRow->sequence;
							$advisorClass_semester 					= $advisorClassRow->semester;
							$advisorClass_timezone 					= $advisorClassRow->time_zone;
							$advisorClass_timezone_id				= $advisorClassRow->timezone_id;		// new
							$advisorClass_timezone_offset			= $advisorClassRow->timezone_offset;	// new
							$advisorClass_level 					= $advisorClassRow->level;
							$advisorClass_class_size 				= $advisorClassRow->class_size;
							$advisorClass_class_schedule_days 		= $advisorClassRow->class_schedule_days;
							$advisorClass_class_schedule_times 		= $advisorClassRow->class_schedule_times;
							$advisorClass_class_schedule_days_utc 	= $advisorClassRow->class_schedule_days_utc;
							$advisorClass_class_schedule_times_utc 	= $advisorClassRow->class_schedule_times_utc;
							$advisorClass_action_log 				= $advisorClassRow->action_log;
							$advisorClass_class_incomplete 			= $advisorClassRow->class_incomplete;
							$advisorClass_date_created				= $advisorClassRow->date_created;
							$advisorClass_date_updated				= $advisorClassRow->date_updated;

							$advisorClass_advisor_last_name  		= no_magic_quotes($advisorClass_advisor_last_name);


							$myInt									= $advisorClass_sequence;
							if ($doDebug) {
								echo "Found a class sequence $advisorClass_sequence<br />";
		
							}
							if ($advisorClass_class_incomplete == 'Y') {
								$thisIncomplete	= "<td><b>Class Incomplete</b><br />
													The information for this class is incomplete. Please update this class record</td></tr>";
							} else {
								$thisIncomplete = "<td></td></tr>";
							}
							$result			.= "<td></td></tr></table>
												<br /><b>Class $advisorClass_sequence:</b>
												<table style='width:900px;'>
												<tr><td style='vertical-align:top;width:300px;'><b>Sequence</b><br />
														$advisorClass_sequence</td>
													<td style='vertical-align:top;width:300px;'><b>Level</b><br />
														$advisorClass_level</td>
													<td style='vertical-align:top;'><b>Class Size</b><br />
														$advisorClass_class_size</td></tr>
												<tr><td style='vertical-align:top;'><b>Class Teaching Days</b><br />
														$advisorClass_class_schedule_days</td>
													<td style='vertical-align:top;' colspan='2'><b>Local Start Time</b><br />
														$advisorClass_class_schedule_times</td>
													$thisIncomplete";
							$inp_nbrClasses++;
							$classcount		= $inp_nbrClasses + 1;
							if ($doDebug) {
								echo "Data at this point:<br />
										daysToSemester: $daysToSemester<br />
										inp_nbrClasses: $inp_nbrClasses<br/>
										classcount: $classcount<br />";
							}
							if (!$noUpdate) {
								$result	.= "<tr><td><form method='post' action='$theURL' 
											name='class_option1_form' ENCTYPE='multipart/form-data'>
											<input type='hidden' name='enstr' value='$enstr'>
											<input type='hidden' name='classID' value='$advisorClass_sequence'>
											<input type='hidden' name='inp_callsign' value='$advisor_call_sign'>
											<input type='hidden' name='strpass' value='15'>
											<input class='formInputButton' type='submit' value='Modify this Class'>
											</form></td>
											<td><form method='post' action='$theURL' 
											name='class_option2_form' ENCTYPE='multipart/form-data'>
											<input type='hidden' name='enstr' value='$enstr'>
											<input type='hidden' name='classID' value='$advisorClass_ID'>
											<input type='hidden' name='inp_callsign' value='$advisor_call_sign'>
											<input type='hidden' name='strpass' value='17'>
											<input class='formInputButton' type='submit' value='Delete this Class'>
											</form></td>";
							} else {
								if ($doDebug) {
									echo "Not allowing update to the class record<br />";
								}
							}
						}
						if (!$noUpdate) {
							$result		.= "<td><form method='post' action='$theURL' 
											name='class_option2_form' ENCTYPE='multipart/form-data'>
											<input type='hidden' name='enstr' value='$enstr'>
											<input type='hidden' name='classID' value='$advisorClass_ID'>
											<input type='hidden' name='inp_callsign' value='$advisor_call_sign'>
											<input type='hidden' name='strpass' value='5'>
											<input type='hidden' name='classcount' value='$classcount'>
											<input class='formInputButton' type='submit' value='Add Another Class'>
											</form></td></tr></table>";
						}
						$result			.= "<p>You may close this window</p>";
					} else {
						if ($doDebug) {
							echo "<b>ERROR:</b> There should be at least 1 advisorclass record for $inp_callsign<br />";
						}
						$result			.= "</table>
											<p>You have not specified any classes. Please click 'Add a Class' and enter the appropriate information.</p>
											<form method='post' action='$theURL' 
											name='class_option2_form' ENCTYPE='multipart/form-data'>
											<input type='hidden' name='enstr' value='$enstr'>
											<input type='hidden' name='inp_callsign' value='$advisor_call_sign'>
											<input type='hidden' name='strpass' value='5'>
											<input type='hidden' name='classcount' value='$classcount'>
											<input class='formInputButton' type='submit' value='Add a Class'>
											</form>";
					}
				}
			}
		} else {
			if ($doDebug) {
				echo "<b>FATAL ERROR:</b> Can not find advisor record for $inp_callsign<br />";
			}
		}
	}
	return array($result,$classcount);
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

button.formInputButton {vertical-align:middle;font-weight:bolder;
text-align:center;color:#300;background:#f99;padding:1px;border:solid 1px #f66;
cursor:pointer;position:relative;float:left;}

input.formInputButton:hover {color:#f8f400;}

input.formInputButton:active {color:#00ffff;}

td {color:#666;background:#fee;padding:2px;
margin-right:5px;margin-bottom:5px;}

tr {color:#333;background:#eee;}

table{font:'Times New Roman', sans-serif;background-image:none;border-collapse:collapse;background:#fee;}

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
		$content	.= "<p><strong>Operating in Test Mode.</strong></p>";
		if ($doDebug) {
			echo "<p><strong>Operating in Test Mode.</strong></p>";
		}
		$advisorTableName		= "wpw1_cwa_consolidated_advisor2";
		$advisorClassTableName		= "wpw1_cwa_consolidated_advisorclass2";
		$advisorDeletedTableName	= "wpw1_cwa_consolidated_advisor_deleted2";
		$advisorClassDeletedTableName = "cwa_advisorclass_deleted2";
	} else {
		$advisorTableName		= "wpw1_cwa_consolidated_advisor";
		$advisorClassTableName		= "wpw1_cwa_consolidated_advisorclass";
		$advisorDeletedTableName	= "wpw1_cwa_consolidated_advisor_deleted";
		$advisorClassDeletedTableName = "wpw1_cwa_consolidated_advisorclass_deleted";
	}
/*
	$theSemester 	= $currentSemester; 
	if ($currentSemester == 'Not in Session') {
		$theSemester	= $nextSemester;
	}
	if ($allowSignup) {
		$theSemester	= $nextSemester;
	}
	if ($doDebug) {
		echo "theSemester: $theSemester<br />";
	}
*/
	$testModeOption	= '';
	if (in_array($userName,$validTestmode)) {			// give option to run in test mode 
		$testModeOption	 = "<tr><td>Operation Mode</td>
							<td><input type='radio' class='formInputButton' name='inp_mode' value='Production' checked='checked'> Production<br />
								<input type='radio' class='formInputButton' name='inp_mode' value='TESTMODE'> TESTMODE</td></tr>
							<tr><td>Verbose Debugging?</td>
								<td><input type='radio' class='formInputButton' name='inp_verbose' value='N' checked='checked'> Standard Output<br />
									<input type='radio' class='formInputButton' name='inp_verbose' value='Y'> Turn on Debugging </td></tr>";
	}


	if ("1" == $strPass) {
		if ($maintenanceMode) {
			$content	.= "<p><b>The Advisor Sign-up process is currently undergoing 
							maintenance. That should be completed within the next hour. Please come back at that 
							time to sign up.</b></p>";
		} else {
			if ($doDebug) {
				echo "Function starting.<br />";
			}
			// set the userName
			if ($userRole == 'advisor') {
				$userName		= strtoupper($userName);
				$inp_callsign	= $userName;
				$strPass		= "2";
			} elseif ($userRole == 'administrator') {
				$content		.= "<h3>$jobname Administrator Role</h3>
									<form method='post' action='$theURL' 
									name='selection_form' ENCTYPE='multipart/form-data''>
									<input type='hidden' name='strpass' value='2'>
									Call Sign: <br />
									<table style='border-collapse:collapse;'>
									<tr><td>Advisor Call Sign</td>
										<td><input type='text' class='formInputText' name='inp_callsign' size='10' maxlength='10' value='$inp_callsign' autofocus></td>
									$testModeOption
									<tr><td>&nbsp;</td><td><input class='formInputButton' type='submit' value='Next' /></td></tr></table>
									</form>";


			}
		}
	}


///// Pass 2 -- do the work


	if ("2" == $strPass) {
		if ($doDebug) {
			echo "At pass $strPass with callsign: $inp_callsign and inp_semester: $inp_semester<br />";
		}
		$userName					= $inp_callsign;		
		
		$haveAdvisorRecord			= FALSE;
		$haveAnyAdvisorRecord		= FALSE;
		$doProceed					= TRUE;

		$content 					.= "<h3>$jobname</h3>";	
		// See if there is already a record for the requested semester
		// if so, go into modify mode. Otherwise, see if the advisor can signup
		$sql					= "select * from $advisorTableName 
									where call_sign='$inp_callsign' 
									and (semester = '$nextSemester' 
									or semester = '$semesterTwo' 
									or semester = '$semesterThree' 
									or Semester = '$semesterFour')";
		$cwa_advisor			= $wpdb->get_results($sql);
		if ($cwa_advisor === FALSE) {
			handleWPDBError($jobname,$doDebug);
		} else {
			$numARows									= $wpdb->num_rows;
			if ($doDebug) {
				$myStr			= $wpdb->last_query;
				echo "ran $myStr<br />and found $numARows rows in $advisorTableName table<br />";
			}
			if ($numARows > 0) {
				foreach ($cwa_advisor as $advisorRow) {
					$advisor_ID							= $advisorRow->advisor_id;
					$advisor_select_sequence 			= $advisorRow->select_sequence;
					$advisor_call_sign 					= strtoupper($advisorRow->call_sign);
					$advisor_first_name 				= $advisorRow->first_name;
					$advisor_last_name 					= stripslashes($advisorRow->last_name);
					$advisor_email 						= strtolower($advisorRow->email);
					$advisor_phone						= $advisorRow->phone;
					$advisor_ph_code					= $advisorRow->ph_code;				// new
					$advisor_text_message 				= $advisorRow->text_message;
					$advisor_city 						= $advisorRow->city;
					$advisor_state 						= $advisorRow->state;
					$advisor_zip_code 					= $advisorRow->zip_code;
					$advisor_country 					= $advisorRow->country;
					$advisor_country_code				= $advisorRow->country_code;		// new
					$advisor_whatsapp					= $advisorRow->whatsapp_app;		// new
					$advisor_signal						= $advisorRow->signal_app;			// new
					$advisor_telegram					= $advisorRow->telegram_app;		// new
					$advisor_messenger					= $advisorRow->messenger_app;		// new
					$advisor_time_zone 					= $advisorRow->time_zone;
					$advisor_timezone_id				= $advisorRow->timezone_id;			// new
					$advisor_timezone_offset			= $advisorRow->timezone_offset;		// new
					$advisor_semester 					= $advisorRow->semester;
					$advisor_survey_score 				= $advisorRow->survey_score;
					$advisor_languages 					= $advisorRow->languages;
					$advisor_fifo_date 					= $advisorRow->fifo_date;
					$advisor_welcome_email_date 		= $advisorRow->welcome_email_date;
					$advisor_verify_email_date 			= $advisorRow->verify_email_date;
					$advisor_verify_email_number 		= $advisorRow->verify_email_number;
					$advisor_verify_response 			= strtoupper($advisorRow->verify_response);
					$advisor_action_log 				= $advisorRow->action_log;
					$advisor_class_verified 			= $advisorRow->class_verified;
					$advisor_control_code 				= $advisorRow->control_code;
					$advisor_date_created 				= $advisorRow->date_created;
					$advisor_date_updated 				= $advisorRow->date_updated;

					$advisor_last_name 					= no_magic_quotes($advisor_last_name);
					$inp_semester						= $advisor_semester;
					
					$noUpdate							= FALSE;
					$doProceed							= TRUE;			
					if ($doDebug) {
						echo "<br />Have an advisor record for $inp_callsign. <br />";
					}

					if ($advisor_class_verified == 'R') {
						$doProceed			= FALSE;
						if ($doDebug) {
							echo "class_verified is R. Take no action<br />";
						}
						$content		.= "You need to contact 
											<a href='https://cwops.org/cwa-class-resolution/' target='_blank'>CWA 
											Class Resolution</a><br />";
					}
					if ($doProceed) {
						$haveAdvisorRecord	= TRUE;				/// go into modify mode
						if ($doDebug) {
							echo "&nbsp;&nbsp;&nbsp;&nbsp;set haveAdvisorRecord true<br />";
						}
						$daysToGo				= days_to_semester($inp_semester);
						if ($daysToGo < 0) {
							if ($doDebug) {
								echo "daysToGo is negative. No update allowed<br />";
							}
							$noUpdate			= TRUE;
						}
						if($daysToGo > 0 && $daysToGo < 21) {
							if ($doDebug) {
								echo "daysToGo to $inp_semester semester is $daysToGo and no update allowes<br />";
							}
							$noUpdate			= TRUE;
						}						
					}
				}
			} else {		// no advisor record for inp_semester. Look for any advisor record
				$sql					= "select * from $advisorTableName 
											where call_sign='$inp_callsign' 
											order by date_created DESC limit 1";
				$cwa_advisor			= $wpdb->get_results($sql);
				if ($cwa_advisor === FALSE) {
					$myError			= $wpdb->last_error;
					$myQuery			= $wpdb->last_query;
					if ($doDebug) {
						echo "Reading $advisorTableName table failed<br />
							  wpdb->last_query: $myQuery<br />
							  wpdb->last_error: $myError<br />";
					}
					$errorMsg			= "$jobname Reading $advisorTableName table failed. <p>SQL: $myQuery</p><p> Error: $myError</p>";
					sendErrorEmail($errorMsg);
				} else {
					$numARows									= $wpdb->num_rows;
					if ($doDebug) {
						$myStr			= $wpdb->last_query;
						echo "ran $myStr<br />and found $numARows rows in $advisorTableName table<br />";
					}
					if ($numARows > 0) {
						foreach ($cwa_advisor as $advisorRow) {
							$advisor_ID							= $advisorRow->advisor_id;
							$advisor_select_sequence 			= $advisorRow->select_sequence;
							$advisor_call_sign 					= strtoupper($advisorRow->call_sign);
							$advisor_first_name 				= $advisorRow->first_name;
							$advisor_last_name 					= stripslashes($advisorRow->last_name);
							$advisor_email 						= strtolower($advisorRow->email);
							$advisor_phone						= $advisorRow->phone;
							$advisor_ph_code					= $advisorRow->ph_code;				// new
							$advisor_text_message 				= $advisorRow->text_message;
							$advisor_city 						= $advisorRow->city;
							$advisor_state 						= $advisorRow->state;
							$advisor_zip_code 					= $advisorRow->zip_code;
							$advisor_country 					= $advisorRow->country;
							$advisor_country_code				= $advisorRow->country_code;		// new
							$advisor_whatsapp					= $advisorRow->whatsapp_app;		// new
							$advisor_signal						= $advisorRow->signal_app;			// new
							$advisor_telegram					= $advisorRow->telegram_app;		// new
							$advisor_messenger					= $advisorRow->messenger_app;		// new
							$advisor_time_zone 					= $advisorRow->time_zone;
							$advisor_timezone_id				= $advisorRow->timezone_id;			// new
							$advisor_timezone_offset			= $advisorRow->timezone_offset;		// new
							$advisor_semester 					= $advisorRow->semester;
							$advisor_survey_score 				= $advisorRow->survey_score;
							$advisor_languages 					= $advisorRow->languages;
							$advisor_fifo_date 					= $advisorRow->fifo_date;
							$advisor_welcome_email_date 		= $advisorRow->welcome_email_date;
							$advisor_verify_email_date 			= $advisorRow->verify_email_date;
							$advisor_verify_email_number 		= $advisorRow->verify_email_number;
							$advisor_verify_response 			= strtoupper($advisorRow->verify_response);
							$advisor_action_log 				= $advisorRow->action_log;
							$advisor_class_verified 			= $advisorRow->class_verified;
							$advisor_control_code 				= $advisorRow->control_code;
							$advisor_date_created 				= $advisorRow->date_created;
							$advisor_date_updated 				= $advisorRow->date_updated;

							$advisor_last_name 					= no_magic_quotes($advisor_last_name);
				
							if ($doDebug) {
								echo "<br />Have an advisor record for $inp_callsign for semester $advisor_semester. email $advisor_email, phone: $advisor_phone<br />";
							}

							$haveAnyAdvisorRecord				= TRUE;
							$inp_phone							= $advisor_phone;
							$inp_email							= $advisor_email;
						}
					}
				}
			}
		}
		if ($doProceed) {
			if ($doDebug) {
				echo "advisor records?<br />";
				if ($haveAdvisorRecord) {
					echo "haveAdvisorRecord: TRUE<br />";
				} else {
					echo "haveAdvisorRecord: FALSE<br />";
				}
				if ($haveAnyAdvisorRecord) {
					echo "haveAnyAdvisorRecord: TRUE<br />";
				} else {
					echo "haveAnyAdvisorRecord: FALSE<br />";
				}
			}
/*
			if there is a record for the requested semester, then do the modify and update process
			If there is not a record for the requested semester, need to determine if the advisor 
			can sign up. 
			
			If the advisor doesn't have a previous semester record, he can sign up. 
			
			Otherwise,get all the advisorclass records for the most recent semester. Since
			there was no new record the program got the last record and that has the semester.
			If all advisorclass records have evaluation complete = Y, then signup is allowed.
			
			
*/

			if (!$haveAdvisorRecord) {			/// determine if the advisor can signup
				if ($haveAnyAdvisorRecord) {		/// if no previous record, no need to check

					/// don't allow to signup if a bad actor
					if ($advisor_survey_score == '6') {		// bad actor
						$doProceed			= FALSE;
						$content		.= "You need to contact 
											<a href='https://cwops.org/cwa-class-resolution/' target='_blank'>CWA 
											Class Resolution</a> before you can sign up as an advisor<br />";
						if ($doDebug) {
							echo "Survey score of six. Take no action<br />";
						}
					}

					// get the advisorclass records
					$allComplete		= TRUE;
					$sql				= "select evaluation_complete 
											from $advisorClassTableName 
											where semester = '$advisor_semester' 
											and advisor_call_sign = '$advisor_call_sign'";
					$wpw1_cwa_advisorclass				= $wpdb->get_results($sql);
					if ($wpw1_cwa_advisorclass === FALSE) {
						$myError			= $wpdb->last_error;
						$myQuery			= $wpdb->last_query;
						if ($doDebug) {
							echo "Reading $advisorClassTableName table failed<br />
								  wpdb->last_query: $myQuery<br />
								  wpdb->last_error: $myError<br />";
						}
						$errorMsg			= "$jobname Reading $advisorClassTableName table failed. <p>SQL: $myQuery</p><p> Error: $myError</p>";
						sendErrorEmail($errorMsg);
					} else {
						$numACRows						= $wpdb->num_rows;
						if ($doDebug) {
							$myStr						= $wpdb->last_query;
							echo "ran $myStr<br />and found $numACRows rows<br />";
						}
						if ($numACRows > 0) {
							foreach ($wpw1_cwa_advisorclass as $advisorClassRow) {
								$advisorClass_evaluation_complete		= $advisorClassRow->evaluation_complete;
							
								if ($advisorClass_evaluation_complete != 'Y') {
									$allComplete		= FALSE;
									if ($doDebug) {
										echo "evaluations not complete. allComplete set to FALSE<br />";
									}
								}
							}
						}
					}
					if (!$allComplete) {
						$doProceed				= FALSE;
						$content				.= "<h3>$jobname</h3>
													<p>You need to complete the promotability evaluations 
													for your students in the $advisor_semester semester 
													before you can register for a future semester.</p>";
					}
				}
				if ($doProceed) {
					if ($advisor_country_code != '') {
						if (in_array($advisor_country_code,$countryCheckedArray)) {
							${$advisor_country_code .'_checked'}	= 'checked';
						} else {
							${$advisor_country_code .'_checked'}	= 'selected';
						}
						if ($doDebug) { 
							echo "have set advisor_country_code_checked to ${$advisor_country_code . '_checked'}<br />";
						}
					}
					if (!$haveAnyAdvisorRecord) {			/// first time signup
						$myStr			=	"<p>All CW Academy Advisors are required to sign up for each semester they 
											desire to teach. Classes are offered three times a year, in two month 
											increments called semesters:<br /><br />
											<span style='color:red;'><b>January-February,&nbsp;&nbsp;&nbsp;&nbsp; May-June,&nbsp;&nbsp;&nbsp;&nbsp; September-October</b></span><br /><br />
											Each semester is eight weeks long consisting of two one-hour online sessions per week.</p>
											<p>Note that sign-ups for multiple semesters is not allowed. Further, if an advisor is 
											currently signed up, the advisor will not be able to sign up for a future semester until the 
											student evaluations are completed.</p>";
					} else {
						$myStr			= '';
					}
					$content			.= "$myStr
											<p>If you are a first-time advisor, please fill out 
											the following form. If, however, you have been an advisor in the past, your 
											information has been pre-populated and may need correction.</p>
											<p>Then enter your class information. You <span style='color:red;'><b>MUST</b></span> sign up for a minimum of one class.</p>
											<form method='post' action='$theURL' 
											name='advisor_signup_form' ENCTYPE='multipart/form-data'>
											<input type='hidden' name='inp_callsign' value='$inp_callsign'>
											<input type='hidden' name='inp_email' value='$inp_email'>
											<input type='hidden' name='inp_phone' value='$inp_phone'>
											<input type='hidden' name='inp_ph_code' value='$advisor_ph_code'>
											<input type='hidden' name='strpass' value='3'>
											<input type='hidden' name='inp_mode' value='$inp_mode'>
											<input type='hidden' name='inp_verbose' value='$inp_verbose'>
											<input type='hidden' name='inp_sequence' value='1'>
											<input type='hidden' name='timezone' value=''>
											<input type='hidden' name='allowSignup' value=$allowSignup>
											<table style='width:1000px;'>
											<tr><td style='vertical-align:top;width:330px;'><b>Call Sign</b><br />
													$inp_callsign</td>
												<td style='vertical-align:top;width:330px;'><b>Last Name</b><br />
													<input type='text' class='formInputText' id='chk_lastname' name='inp_lastname' value=\"$advisor_last_name\"></td>
												<td style='vertical-align:top;'><b>First Name</b><br />
													<input type='text' class='formInputText' id='chk_firstname' name='inp_firstname' value='$advisor_first_name'></td></tr>
											<tr><td style='vertical-align:top;'><b>Semester</b><br />
													<input type='radio' class='formInputButton' id='chk_semester' name='inp_semester' value='$nextSemester' checked> $nextSemester<br />
													<input type='radio' class='formInputButton' id='chk_semester' name='inp_semester' value='$semesterTwo' > $semesterTwo<br />
													<input type='radio' class='formInputButton' id='chk_semester' name='inp_semester' value='$semesterThree'> $semesterThree</td>
												<td style='vertical-align:top;'><b>Email</b><br />
													<input type='text' class='formInputText' id='inp_email' name='inp_email' value='$inp_email'>'</td>
												<td style='vertical-align:top;'><b>Phone</b>
													<input type='text' class='formInputText' id='inp_phone' name='inp_phone' value='$inp_phone'><br />
													Can this phone receive text messages?<br />
													<input type='radio' class='formInputButton' name='inp_text_message' value='Y' checked > Yes<br />
													<input type='radio' class='formInputButton' name='inp_text_message' Value='N'> No<br /></td></tr>
											<tr><td style='vertical-align:top;'><b>City</b><br />
													<input type='text' class='formInputText' id='inp_city' name='inp_city' value='$advisor_city' ></td>
												<td style='vertical-align:top;'><b>State / Region / Province</b><br />
													<input type='text' class='formInputText' id='chk_state' name='inp_state' value='$advisor_state' ></td>
												<td style='vertical-align:top;'><b>Zip / Postal Code</b> Zip/Postal Code is required for US and Canadian residents<br />
													<input type='text' class='formInputText' id='chk_zip' name='inp_zip' value='$advisor_zip_code' ><br />
													</td></tr>

											<tr><td colspan='3' style='vertical-align:top;'>
													<b>Country*</b><br />
													Please select your country:
											<table style='width:100%;'>
											<tr>
											<td><input type='radio' class='formInputButton' id='chk_country' name='inp_country'  value='AR|Argentina' $AR_checked>Argentina</td>
											<td><input type='radio' class='formInputButton' id='chk_country' name='inp_country'  value='AU|Australia' $AU_checked>Australia</td>
											<td><input type='radio' class='formInputButton' id='chk_country' name='inp_country'  value='AT|Austria' $AT_checked>Austria</td>
											<td><input type='radio' class='formInputButton' id='chk_country' name='inp_country'  value='BE|Belgium' $BE_checked>Belgium</td>
											<td><input type='radio' class='formInputButton' id='chk_country' name='inp_country'  value='BR|Brazil' $BR_checked>Brazil</td>
											</tr><tr>
											<td><input type='radio' class='formInputButton' id='chk_country' name='inp_country'  value='CA|Canada' $CA_checked>Canada</td>
											<td><input type='radio' class='formInputButton' id='chk_country' name='inp_country'  value='CL|Chile' $CL_checked>Chile</td>
											<td><input type='radio' class='formInputButton' id='chk_country' name='inp_country'  value='CN|China' $CN_checked>China</td>
											<td><input type='radio' class='formInputButton' id='chk_country' name='inp_country'  value='CZ|Czech Republic' $CZ_checked>Czech Republic</td>
											<td><input type='radio' class='formInputButton' id='chk_country' name='inp_country'  value='DK|Denmark' $DK_checked>Denmark</td>
											</tr><tr>
											<td><input type='radio' class='formInputButton' id='chk_country' name='inp_country'  value='FI|Finland' $FI_checked>Finland</td>
											<td><input type='radio' class='formInputButton' id='chk_country' name='inp_country'  value='FR|France' $FR_checked>France</td>
											<td><input type='radio' class='formInputButton' id='chk_country' name='inp_country'  value='DE|Germany' $DE_checked>Germany</td>
											<td><input type='radio' class='formInputButton' id='chk_country' name='inp_country'  value='GR|Greece' $GR_checked>Greece</td>
											<td><input type='radio' class='formInputButton' id='chk_country' name='inp_country'  value='IN|India' $IN_checked>India</td>
											</tr><tr>
											<td><input type='radio' class='formInputButton' id='chk_country' name='inp_country'  value='IE|Ireland' $IE_checked>Ireland</td>
											<td><input type='radio' class='formInputButton' id='chk_country' name='inp_country'  value='IL|Israel' $IL_checked>Israel</td>
											<td><input type='radio' class='formInputButton' id='chk_country' name='inp_country'  value='IT|Italy' $IT_checked>Italy</td>
											<td><input type='radio' class='formInputButton' id='chk_country' name='inp_country'  value='JP|Japan' $JP_checked>Japan</td>
											<td><input type='radio' class='formInputButton' id='chk_country' name='inp_country'  value='KR|Korea' $KR_checked>Korea</td>
											</tr><tr>
											<td><input type='radio' class='formInputButton' id='chk_country' name='inp_country'  value='MX|Mexico' $MX_checked>Mexico</td>
											<td><input type='radio' class='formInputButton' id='chk_country' name='inp_country'  value='NL|Netherlands' $NL_checked>Netherlands</td>
											<td><input type='radio' class='formInputButton' id='chk_country' name='inp_country'  value='NZ|New Zealand' $NZ_checked>New Zealand</td>
											<td><input type='radio' class='formInputButton' id='chk_country' name='inp_country'  value='NO|Norway' $NO_checked>Norway</td>
											<td><input type='radio' class='formInputButton' id='chk_country' name='inp_country'  value='PH|Philippines' $PH_checked>Philippines</td>
											</tr><tr>
											<td><input type='radio' class='formInputButton' id='chk_country' name='inp_country'  value='PL|Poland' $PL_checked>Poland</td>
											<td><input type='radio' class='formInputButton' id='chk_country' name='inp_country'  value='PT|Portugal' $PT_checked>Portugal</td>
											<td><input type='radio' class='formInputButton' id='chk_country' name='inp_country'  value='PR|Puerto Rico' $PR_checked>Puerto Rico</td>
											<td><input type='radio' class='formInputButton' id='chk_country' name='inp_country'  value='KR|Republic of Korea' $KR_checked>Republic of Korea</td>
											<td><input type='radio' class='formInputButton' id='chk_country' name='inp_country'  value='RU|Russian Federation' $RU_checked>Russian Federation</td>
											</tr><tr>
											<td><input type='radio' class='formInputButton' id='chk_country' name='inp_country'  value='ZA|South Africa' $ZA_checked>South Africa</td>
											<td><input type='radio' class='formInputButton' id='chk_country' name='inp_country'  value='ES|Spain' $ES_checked>Spain</td>
											<td><input type='radio' class='formInputButton' id='chk_country' name='inp_country'  value='SE|Sweden' $SE_checked>Sweden</td>
											<td><input type='radio' class='formInputButton' id='chk_country' name='inp_country'  value='CH|Switzerland' $CH_checked>Switzerland</td>
											<td><input type='radio' class='formInputButton' id='chk_country' name='inp_country'  value='GB|United Kingdom' $GB_checked>United Kingdom (England, Scotland, Wales, Northern Ireland)</td>
											</tr><tr>
											<td><input type='radio' class='formInputButton' id='chk_country' name='inp_country'  value='US|United States' $US_checked>United States</td>
											<td></td>
											<td></td>
											<td></td>
											<td></td>
											</tr>
											</table>
											<p>If your country is not listed above, please select it from the list below:</p>

													<select name='inp_countryb' id='chk_countryb' class='formSelect' size='5'>
											<option value='AF|Afghanistan' $AF_checked>Afghanistan</option>
											<option value='AL|Albania' $AL_checked>Albania</option>
											<option value='DZ|Algeria' $DZ_checked>Algeria</option>
											<option value='AX|Alland Islands' $AX_checked>Alland Islands</option>
											<option value='AS|American Samoa' $AS_checked>American Samoa</option>
											<option value='AD|Andorra' $AD_checked>Andorra</option>
											<option value='AO|Angola' $AO_checked>Angola</option>
											<option value='AI|Anguilla' $AI_checked>Anguilla</option>
											<option value='AQ|Antarctica' $AQ_checked>Antarctica</option>
											<option value='AG|Antigua and Barbuda' $AG_checked>Antigua and Barbuda</option>
											<option value='AM|Armenia' $AM_checked>Armenia</option>
											<option value='AW|Aruba' $AW_checked>Aruba</option>
											<option value='AZ|Azerbaijan' $AZ_checked>Azerbaijan</option>
											<option value='BD|Bangladesh' $BD_checked>Bangladesh</option>
											<option value='BS|Bahamas' $BS_checked>Bahamas</option>
											<option value='BH|Bahrain' $BH_checked>Bahrain</option>
											<option value='BB|Barbados' $BB_checked>Barbados</option>
											<option value='BY|Belarus' $BY_checked>Belarus</option>
											<option value='BZ|Belize' $BZ_checked>Belize</option>
											<option value='BJ|Benin' $BJ_checked>Benin</option>
											<option value='BM|Bermuda' $BM_checked>Bermuda</option>
											<option value='BT|Bhutan' $BT_checked>Bhutan</option>
											<option value='BO|Bolivia - Plurinational State of' $BO_checked>Bolivia - Plurinational State of</option>
											<option value='BQ|Bonaire - Sint Eustatius and Saba' $BQ_checked>Bonaire - Sint Eustatius and Saba</option>
											<option value='BA|Bosnia and Herzegovina' $BA_checked>Bosnia and Herzegovina</option>
											<option value='BW|Botswana' $BW_checked>Botswana</option>
											<option value='BG|Bulgaria' $BG_checked>Bulgaria</option>
											<option value='BV|Bouvet Island' $BV_checked>Bouvet Island</option>
											<option value='IO|British Indian Ocean Territory' $IO_checked>British Indian Ocean Territory</option>
											<option value='BN|Brunei' $BN_checked>Brunei</option>
											<option value='BF|Burkina Faso' $BF_checked>Burkina Faso</option>
											<option value='BI|Burundi' $BI_checked>Burundi</option>
											<option value='KH|Cambodia' $KH_checked>Cambodia</option>
											<option value='CM|Cameroon' $CM_checked>Cameroon</option>
											<option value='CV|Cape Verde' $CV_checked>Cape Verde</option>
											<option value='KY|Cayman Islands' $KY_checked>Cayman Islands</option>
											<option value='CF|Central African Republic' $CF_checked>Central African Republic</option>
											<option value='TD|Chad' $TD_checked>Chad</option>
											<option value='CX|Christmas Island' $CX_checked>Christmas Island</option>
											<option value='CC|Cocos (Keeling) Islands' $CC_checked>Cocos (Keeling) Islands</option>
											<option value='CO|Colombia' $CO_checked>Colombia</option>
											<option value='KM|Comoros' $KM_checked>Comoros</option>
											<option value='CD|Congo' $CD_checked>Congo</option>
											<option value='CG|Congo' $CG_checked>Congo</option>
											<option value='CK|Cook Islands' $CK_checked>Cook Islands</option>
											<option value='CR|Costa Rica' $CR_checked>Costa Rica</option>
											<option value='HR|Croatia' $HR_checked>Croatia</option>
											<option value='CU|Cuba' $CU_checked>Cuba</option>
											<option value='CW|Curascao' $CW_checked>Curascao</option>
											<option value='CY|Cyprus' $CY_checked>Cyprus</option>
											<option value='KP|Democratic Peoples Republic of Korea $KP_checked>Democratic Peoples Republic of Korea</option>
											<option value='DJ|Djibouti' $DJ_checked>Djibouti</option>
											<option value='DM|Dominica' $DM_checked>Dominica</option>
											<option value='DO|Dominican Republic' $DO_checked>Dominican Republic</option>
											<option value='EC|Ecuador' $EC_checked>Ecuador</option>
											<option value='EG|Egypt' $EG_checked>Egypt</option>
											<option value='GQ|Equatorial Guinea' $GQ_checked>Equatorial Guinea</option>
											<option value='ER|Eritrea' $ER_checked>Eritrea</option>
											<option value='EE|Estonia' $EE_checked>Estonia</option>
											<option value='ET|Ethiopia' $ET_checked>Ethiopia</option>
											<option value='FK|Falkland Islands (Malvinas)' $FK_checked>Falkland Islands (Malvinas)</option>
											<option value='FO|Faroe Islands' $FO_checked>Faroe Islands</option>
											<option value='FM|Federated States of Micronesia' $FM_checked>Federated States of Micronesia</option>
											<option value='FJ|Fiji' $FJ_checked>Fiji</option>
											<option value='GF|French Guiana' $GF_checked>French Guiana</option>
											<option value='PF|French Polynesia' $PF_checked>French Polynesia</option>
											<option value='TF|French Southern Territories' $TF_checked>French Southern Territories</option>
											<option value='GA|Gabon' $GA_checked>Gabon</option>
											<option value='GM|Gambia' $GM_checked>Gambia</option>
											<option value='GE|Georgia' $GE_checked>Georgia</option>
											<option value='GH|Ghana' $GH_checked>Ghana</option>
											<option value='GI|Gibraltar' $GI_checked>Gibraltar</option>
											<option value='GL|Greenland' $GL_checked>Greenland</option>
											<option value='GD|Grenada' $GD_checked>Grenada</option>
											<option value='GP|Guadeloupe' $GP_checked>Guadeloupe</option>
											<option value='GU|Guam' $GU_checked>Guam</option>
											<option value='GT|Guatemala' $GT_checked>Guatemala</option>
											<option value='GG|Guernsey' $GG_checked>Guernsey</option>
											<option value='GW|Guinea-Bissau' $GW_checked>Guinea-Bissau</option>
											<option value='GN|Guinea' $GN_checked>Guinea</option>
											<option value='GY|Guyana' $GY_checked>Guyana</option>
											<option value='HT|Haiti' $HT_checked>Haiti</option>
											<option value='HM|Heard Island and McDonald Islands' $HM_checked>Heard Island and McDonald Islands</option>
											<option value='VA|Holy See (Vatican City State)' $VA_checked>Holy See (Vatican City State)</option>
											<option value='HN|Honduras' $HN_checked>Honduras</option>
											<option value='HK|Hong Kong' $HK_checked>Hong Kong</option>
											<option value='HU|Hungary' $HU_checked>Hungary</option>
											<option value='IS|Iceland' $IS_checked>Iceland</option>
											<option value='ID|Indonesia' $ID_checked>Indonesia</option>
											<option value='IR|Iran' $IR_checked>Iran</option>
											<option value='IQ|Iraq' $IQ_checked>Iraq</option>
											<option value='IR|Islamic Republic of Iran' $IR_checked>Islamic Republic of Iran</option>
											<option value='IM|Isle of Man' $IM_checked>Isle of Man</option>
											<option value='CI|Ivory Coast' $CI_checked>Ivory Coast</option>
											<option value='JM|Jamaica' $JM_checked>Jamaica</option>
											<option value='JE|Jersey' $JE_checked>Jersey</option>
											<option value='JO|Jordan' $JO_checked>Jordan</option>
											<option value='KZ|Kazakhstan' $KZ_checked>Kazakhstan</option>
											<option value='KE|Kenya' $KE_checked>Kenya</option>
											<option value='KI|Kiribati' $KI_checked>Kiribati</option>
											<option value='KW|Kuwait' $KW_checked>Kuwait</option>
											<option value='KG|Kyrgyzstan' $KG_checked>Kyrgyzstan</option>
											<option value='LA|Lao Peoples Democratic Republic' $LA_checked>Lao Peoples Democratic Republic</option>
											<option value='LA|Laos' $LA_checked>Laos</option>
											<option value='LV|Latvia' $LV_checked>Latvia</option>
											<option value='LB|Lebanon' $LB_checked>Lebanon</option>
											<option value='LS|Lesotho' $LS_checked>Lesotho</option>
											<option value='LR|Liberia' $LR_checked>Liberia</option>
											<option value='LY|Libya' $LY_checked>Libya</option>
											<option value='LI|Liechtenstein' $LI_checked>Liechtenstein</option>
											<option value='LT|Lithuania' $LT_checked>Lithuania</option>
											<option value='LU|Luxembourg' $LU_checked>Luxembourg</option>
											<option value='MO|Macao' $MO_checked>Macao</option>
											<option value='MK|Macedonia' $MK_checked>Macedonia</option>
											<option value='MG|Madagascar' $MG_checked>Madagascar</option>
											<option value='MW|Malawi' $MW_checked>Malawi</option>
											<option value='MY|Malaysia' $MY_checked>Malaysia</option>
											<option value='MV|Maldives' $MV_checked>Maldives</option>
											<option value='ML|Mali' $ML_checked>Mali</option>
											<option value='MT|Malta' $MT_checked>Malta</option>
											<option value='MH|Marshall Islands' $MH_checked>Marshall Islands</option>
											<option value='MQ|Martinique' $MQ_checked>Martinique</option>
											<option value='MR|Mauritania' $MR_checked>Mauritania</option>
											<option value='MU|Mauritius' $MU_checked>Mauritius</option>
											<option value='YT|Mayotte' $YT_checked>Mayotte</option>
											<option value='FM|Micronesia' $FM_checked>Micronesia</option>
											<option value='MD|Moldova' $MD_checked>Moldova</option>
											<option value='MC|Monaco' $MC_checked>Monaco</option>
											<option value='MN|Mongolia' $MN_checked>Mongolia</option>
											<option value='ME|Montenegro' $ME_checked>Montenegro</option>
											<option value='MS|Montserrat' $MS_checked>Montserrat</option>
											<option value='MA|Morocco' $MA_checked>Morocco</option>
											<option value='MZ|Mozambique' $MZ_checked>Mozambique</option>
											<option value='MM|Myanmar' $MM_checked>Myanmar</option>
											<option value='NA|Namibia' $NA_checked>Namibia</option>
											<option value='NR|Nauru' $NR_checked>Nauru</option>
											<option value='NP|Nepal' $NP_checked>Nepal</option>
											<option value='NC|New Caledonia' $NC_checked>New Caledonia</option>
											<option value='NI|Nicaragua' $NI_checked>Nicaragua</option>
											<option value='NE|Niger' $NE_checked>Niger</option>
											<option value='NG|Nigeria' $NG_checked>Nigeria</option>
											<option value='NU|Niue' $NU_checked>Niue</option>
											<option value='NF|Norfolk Island' $NF_checked>Norfolk Island</option>
											<option value='MP|Northern Mariana Islands' $MP_checked>Northern Mariana Islands</option>
											<option value='OM|Oman' $OM_checked>Oman</option>
											<option value='PK|Pakistan' $PK_checked>Pakistan</option>
											<option value='PW|Palau' $PW_checked>Palau</option>
											<option value='PS|Palestine' $PS_checked>Palestine</option>
											<option value='PA|Panama' $PA_checked>Panama</option>
											<option value='PG|Papua New Guinea' $PG_checked>Papua New Guinea</option>
											<option value='PY|Paraguay' $PY_checked>Paraguay</option>
											<option value='PE|Peru' $PE_checked>Peru</option>
											<option value='PN|Pitcairn' $PN_checked>Pitcairn</option>
											<option value='QA|Qatar' $QA_checked>Qatar</option>
											<option value='MD|Republic of Moldova' $MD_checked>Republic of Moldova</option>
											<option value='RE|Reunion' $RE_checked>Reunion</option>
											<option value='RO|Romania' $RO_checked>Romania</option>
											<option value='RW|Rwanda' $RW_checked>Rwanda</option>
											<option value='BL|Saint BarthÃ©lemy' $BL_checked>Saint BarthÃ©lemy</option>
											<option value='SH|Saint Helena Ascension and Tristan da Cunha' $SH_checked>Saint Helena Ascension and Tristan da Cunha</option>
											<option value='KN|Saint Kitts and Nevis' $KN_checked>Saint Kitts and Nevis</option>
											<option value='LC|Saint Lucia' $LC_checked>Saint Lucia</option>
											<option value='MF|Saint Martin (French part)' $MF_checked>Saint Martin (French part)</option>
											<option value='PM|Saint Pierre and Miquelon' $PM_checked>Saint Pierre and Miquelon</option>
											<option value='VC|Saint Vincent and the Grenadines' $VC_checked>Saint Vincent and the Grenadines</option>
											<option value='WS|Samoa' $WS_checked>Samoa</option>
											<option value='SM|San Marino' $SM_checked>San Marino</option>
											<option value='ST|Sao Tome and Principe' $ST_checked>Sao Tome and Principe</option>
											<option value='SA|Saudi Arabia' $SA_checked>Saudi Arabia</option>
											<option value='SN|Senegal' $SN_checked>Senegal</option>
											<option value='RS|Serbia' $RS_checked>Serbia</option>
											<option value='SC|Seychelles' $SC_checked>Seychelles</option>
											<option value='SL|Sierra Leone' $SL_checked>Sierra Leone</option>
											<option value='SG|Singapore' $SG_checked>Singapore</option>
											<option value='SX|Sint Maarten (Dutch part)' $SX_checked>Sint Maarten (Dutch part)</option>
											<option value='SK|Slovakia' $SK_checked>Slovakia</option>
											<option value='SI|Slovenia' $SI_checked>Slovenia</option>
											<option value='SB|Solomon Islands' $SB_checked>Solomon Islands</option>
											<option value='SO|Somalia' $SO_checked>Somalia</option>
											<option value='GS|South Georgia and the South Sandwich Islands' $GS_checked>South Georgia and the South Sandwich Islands</option>
											<option value='SS|South Sudan' $SS_checked>South Sudan</option>
											<option value='LK|Sri Lanka' $LK_checked>Sri Lanka</option>
											<option value='PS|State of Palestine' $PS_checked>State of Palestine</option>
											<option value='SD|Sudan' $SD_checked>Sudan</option>
											<option value='SR|Suriname' $SR_checked>Suriname</option>
											<option value='SJ|Svalbard and Jan Mayen' $SJ_checked>Svalbard and Jan Mayen</option>
											<option value='SZ|Swaziland' $SZ_checked>Swaziland</option>
											<option value='SY|Syrian Arab Republic' $SY_checked>Syrian Arab Republic</option>
											<option value='TW|Taiwan - Province of China' $TW_checked>Taiwan - Province of China</option>
											<option value='TJ|Tajikistan' $TJ_checked>Tajikistan</option>
											<option value='TH|Thailand' $TH_checked>Thailand</option>
											<option value='CD|The Democratic Republic of the Congo' $CD_checked>The Democratic Republic of the Congo</option>
											<option value='TL|Timor-Leste' $TL_checked>Timor-Leste</option>
											<option value='TG|Togo' $TG_checked>Togo</option>
											<option value='TK|Tokelau' $TK_checked>Tokelau</option>
											<option value='TO|Tonga' $TO_checked>Tonga</option>
											<option value='TT|Trinidad and Tobago' $TT_checked>Trinidad and Tobago</option>
											<option value='TR|Turkey' $TR_checked>Turkey</option>
											<option value='TM|Turkmenistan' $TM_checked>Turkmenistan</option>
											<option value='TC|Turks and Caicos Islands' $TC_checked>Turks and Caicos Islands</option>
											<option value='TV|Tuvalu' $TV_checked>Tuvalu</option>
											<option value='UG|Uganda' $UG_checked>Uganda</option>
											<option value='UA|Ukraine' $UA_checked>Ukraine</option>
											<option value='AE|United Arab Emirates' $AE_checked>United Arab Emirates</option>
											<option value='TZ|United Republic of Tanzania' $TZ_checked>United Republic of Tanzania</option>
											<option value='UM|United States Minor Outlying Islands' $UM_checked>United States Minor Outlying Islands</option>
											<option value='UY|Uruguay' $UY_checked>Uruguay</option>
											<option value='UZ|Uzbekistan' $UZ_checked>Uzbekistan</option>
											<option value='VU|Vanuatu' $VU_checked>Vanuatu</option>
											<option value='VE|Venezuela - Bolivarian Republic of' $VE_checked>Venezuela - Bolivarian Republic of</option>
											<option value='VN|Viet Nam' $VN_checked>Viet Nam</option>
											<option value='VG|Virgin Islands - British' $VG_checked>Virgin Islands - British</option>
											<option value='VI|Virgin Islands - U.S.' $VI_checked>Virgin Islands - U.S.</option>
											<option value='WF|Wallis and Futuna' $WF_checked>Wallis and Futuna</option>
											<option value='EH|Western Sahara' $EH_checked>Western Sahara</option>
											<option value='YE|Yemen' $YE_checked>Yemen</option>
											<option value='ZM|Zambia' $ZM_checked>Zambia</option>
											<option value='ZW|Zimbabwe' $ZW_checked>Zimbabwe</option>
											</select></td>
											</tr>

											<tr><td style='vertical-align:top;'><b>Languages Spoken</b><br />
													<input type='text' class='formInputText' id='chk_languages' name='inp_languages' value='$advisor_languages' ></td>
												<td style='vertical-align:top;'>&nbsp;</td>
												<td style='vertical-align:top;'>&nbsp;</td></tr>
											<tr><td colspan='3'>If you use any of these other messaging services, you may optionally enter your user id</td></tr>
											<tr><td colspan='3'>
												<table>
													<tr><td style='vertical-align:top;'><b>Whatsapp</b><br />
															<input type='text' class='formInputText' id='chk_whatsapp' name='inp_whatsapp' value='$advisor_whatsapp' size='20' maxlength='20'></td>
														<td style='vertical-align:top;'><b>Signal</b><br />
															<input type='text' class='formInputText' id='chk_signal' name='inp_signal' value='$advisor_signal' size='20' maxlength='20'></td>
														<td style='vertical-align:top;'><b>Telegram</b><br />
															<input type='text' class='formInputText' id='chk_telegram' name='inp_telegram' value='$advisor_telegram' size='20' maxlength='20'></td>
														<td style='vertical-align:top;'><b>Messenger</b><br />
															<input type='text' class='formInputText' id='chk_messenger' name='inp_messenger' value='$advisor_messenger' size='20' maxlength='20'></td></tr>
												</table></td></tr>
											</table>
											<p>Please check the information you entered above. Then add your class information:
											<table style='width:1000px;'>
											<tr><td style='vertical-align:top;width:330px;'><b>Sequence</b><br />
													1</td>
												<td style='vertical-align:top;width:330px;'><b>Level</b><br />
													<input type='radio' class='formInputButton' id='chk_level' name='inp_level' value='Beginner'> Beginner<br />
													<input type='radio' class='formInputButton' id='chk_level' name='inp_level' value='Fundamental'> Fundamental (formerly Basic)<br />
													<input type='radio' class='formInputButton' id='chk_level' name='inp_level' value='Intermediate'> Intermediate<br />
													<input type='radio' class='formInputButton' id='chk_level' name='inp_level' value='Advanced''> Advanced<br /></td>
												<td style='vertical-align:top;'width:330px;><b>Class Size</b><br />
													<input type='text'class='formInputText' id='chk_class_size' name='inp_class_size' size='5' maxlength='5' value='6'><br />
														(default class size is 6)</td></tr>
											<tr><td style='vertical-align:top;'><b>Class Teaching Days</b><br />Note that most advisors hold classes on Monday and Thursday<br />
													<input type='radio' class='formInputButton' id='check_days' name='inp_teaching_days' value='Sunday,Wednesday' required > Sunday and Wednesday<br />
													<input type='radio' class='formInputButton' id='check_days' name='inp_teaching_days' value='Sunday,Thursday' required > Sunday and Thursday<br />
													<input type='radio' class='formInputButton' id='check_days' name='inp_teaching_days' value='Monday,Thursday' required  checked> Monday and Thursday<br />
													<input type='radio' class='formInputButton' id='check_days' name='inp_teaching_days' value='Tuesday,Friday' required > Tuesday and Friday</td>
												<td style='vertical-align:top;' colspan='2'><b>Local Start Time</b><br />Specify start time in local time in the 
											time zone where you live. Select the time where this class will start. The program will account for standard or daylight 
											savings time or summer time as needed.<br />
												<table><tr>
												<td style='width:110px;vertical-align:top;'>
													<input type='radio' class='formInputButton' id='chk_times' name='inp_times' value='0600'  required > 6:00am<br />
													<input type='radio' class='formInputButton' id='chk_times' name='inp_times' value='0630' required  > 6:30am<br />
													<input type='radio' class='formInputButton' id='chk_times' name='inp_times' value='0700'  required > 7:00am<br />
													<input type='radio' class='formInputButton' id='chk_times' name='inp_times' value='0730'  required > 7:30am<br />
													<input type='radio' class='formInputButton' id='chk_times' name='inp_times' value='0800'  required > 8:00am<br />
													<input type='radio' class='formInputButton' id='chk_times' name='inp_times' value='0830'  required > 8:30am<br />
													<input type='radio' class='formInputButton' id='chk_times' name='inp_times' value='0900'  required > 9:00am<br />
													<input type='radio' class='formInputButton' id='chk_times' name='inp_times' value='0930'  required > 9:30am<br />
													<input type='radio' class='formInputButton' id='chk_times' name='inp_times' value='1000'  required > 10:00am</td>
												<td style='width:110px;vertical-align:top;'>
													<input type='radio' class='formInputButton' id='chk_times' name='inp_times' value='1030'  required > 10:30am<br />
													<input type='radio' class='formInputButton' id='chk_times' name='inp_times' value='1100'  required > 11:00am<br />
													<input type='radio' class='formInputButton' id='chk_times' name='inp_times' value='1130'  required > 11:30am<br />
													<input type='radio' class='formInputButton' id='chk_times' name='inp_times' value='1200'  required > Noon<br />
													<input type='radio' class='formInputButton' id='chk_times' name='inp_times' value='1230'  required > 12:30pm<br />
													<input type='radio' class='formInputButton' id='chk_times' name='inp_times' value='1300'  required > 1:00pm<br />
													<input type='radio' class='formInputButton' id='chk_times' name='inp_times' value='1330'  required > 1:30pm<br />
													<input type='radio' class='formInputButton' id='chk_times' name='inp_times' value='1400'  required > 2:00pm<br />
													<input type='radio' class='formInputButton' id='chk_times' name='inp_times' value='1430'  required > 2:30pm</td>
												<td style='width:110px;vertical-align:top;'>
													<input type='radio' class='formInputButton' id='chk_times' name='inp_times' value='1500'  required > 3:00pm<br />
													<input type='radio' class='formInputButton' id='chk_times' name='inp_times' value='1530'  required > 3:30pm<br />
													<input type='radio' class='formInputButton' id='chk_times' name='inp_times' value='1600'  required > 4:00pm<br />
													<input type='radio' class='formInputButton' id='chk_times' name='inp_times' value='1630'  required > 4:30pm<br />
													<input type='radio' class='formInputButton' id='chk_times' name='inp_times' value='1700'  required > 5:00pm<br />
													<input type='radio' class='formInputButton' id='chk_times' name='inp_times' value='1730'  required > 5:30pm<br />
													<input type='radio' class='formInputButton' id='chk_times' name='inp_times' value='1800'  required > 6:00pm<br />
													<input type='radio' class='formInputButton' id='chk_times' name='inp_times' value='1830'  required > 6:30pm<br />
													<input type='radio' class='formInputButton' id='chk_times' name='inp_times' value='1900'  required > 7:00pm</td>
												<td style='width:110px;vertical-align:top;'>
													<input type='radio' class='formInputButton' id='chk_times' name='inp_times' value='1930' required  > 7:30pm<br />
													<input type='radio' class='formInputButton' id='chk_times' name='inp_times' value='2000'  required > 8:00pm<br />
													<input type='radio' class='formInputButton' id='chk_times' name='inp_times' value='2030' required  > 8:30pm<br />
													<input type='radio' class='formInputButton' id='chk_times' name='inp_times' value='2100' required  > 9:00pm<br />
													<input type='radio' class='formInputButton' id='chk_times' name='inp_times' value='2130'  required > 9:30pm<br />
													<input type='radio' class='formInputButton' id='chk_times' name='inp_times' value='2200'  required > 10:00pm<br />
													<input type='radio' class='formInputButton' id='chk_times' name='inp_times' value='2230'  required > 10:30pm</td></tr>
												</table></td></tr>
											<tr><td>&nbsp;</td><td><input class='formInputButton' type='submit' onclick=\"return validate_form(this.form);\" value='Next' /></td></tr></table>

											</form>";		
				}
			} else {
				if ($doDebug) {
					echo "calling getAdvisorInfoToDisplay with $inp_callsign, $inp_semester, $noUpdate<br />";
				}
				$displayResult	= getAdvisorInfoToDisplay($inp_callsign,$inp_semester,$noUpdate);
				$content		.= $displayResult[0];
			}
		}	

/////////////////			Pass 3		add the advisor and class record

	} elseif ("3" == $strPass) {
		if ($doDebug) {
			echo "<br />At pass 3 with inp_callsign: $inp_callsign and inp_bypass: $inp_bypass<br />";
		}
		if ($userName = '') {
			$userName				= $inp_callsign;
		}
		$doProceed					= TRUE;

		if ($inp_bypass != 'Y') {
		
			// figure out the timezone_id and UTC offset
			$myArray				= explode("|",$inp_country);
			if (count($myArray) != 2) {
				if ($doDebug) {
					echo "inp_country from pass2 does not have the right number of elements: $inp_country<br />";
				}
				$doProceed			= FALSE;
			} else {
				$inp_country_code	= $myArray[0];
				$inp_country		= $myArray[1];
			}
			
			//////////// get the phone code from the country codes table
			$phoneResult			= getCountryData($inp_country_code,'countrycode',$doDebug);
			if ($phoneResult !== FALSE) {
				$inp_ph_code		= $phoneResult[2];
			} else {
				if ($doDebug) {
					echo "getting ph_code using country code $inp_country_code failed<br />";
				}
				$inp_ph_code		= '';
			}
			
			
			if ($inp_country_code == 'US') {
				if ($inp_zip != '') {
					$myResult			= getOffsetFromZipcode($inp_zip,$inp_semester,TRUE,$testMode,$doDebug);
					$myStatus			= $myResult[0];
					$inp_timezone_id	= $myResult[1];
					$inp_timezone_offset = $myResult[2];
					$matchMsg			= $myResult[3];
			
					if ($myStatus == 'NOK') {
						if ($doDebug) {
							echo "calling getOffsetFromZipcode using $inp_zip and $inp_semester failed<br />
Error: $matchMsg<br />";
						}
						$inp_timezone_id		= 'Unknown';
						$inp_timezone_offset	= -99;
					}
				} else {
					if ($doDebug) {
						echo "Somehow the zip code for $inp_callsign is empty<br />";
					}
					$inp_timezone_id		= 'Unknown';
					$inp_timezone_offset	= -99;
				}
				
				
				
			} else {					// foreign process needed
				$timezone_identifiers 		= DateTimeZone::listIdentifiers( DateTimeZone::PER_COUNTRY, $inp_country_code );
				$myInt						= count($timezone_identifiers);
				if ($myInt == 1) {									//  only 1 found. Use that and continue
					$thisIdentifier			= $timezone_identifiers[0];
					$inp_timezone_offset	= getOffsetFromIdentifier($thisIdentifier,$inp_semester);
					if ($inp_timezone_offset === FALSE) {
						if ($doDebug) {
							echo "getOffsetFromIdentifier returned FALSE from $thisIdentifier and $inp_semester<br />";
						}
						$inp_timezone_offset	= -99;
					}
				} elseif ($myInt > 1) {
					$timezoneSelector			= "<table>";
					$ii							= 1;
					if ($doDebug) {
						echo "have the list of identifiers for $inp_country_code<br />";
					}
					foreach ($timezone_identifiers as $thisID) {
						if ($doDebug) {
							echo "Processing $thisID<br />";
						}
						$selector			= "";
						if ($browser_timezone_id == $thisID) {
							$selector		= "checked";
						}
						$dateTimeZoneLocal 	= new DateTimeZone($thisID);
						$dateTimeLocal 		= new DateTime("now",$dateTimeZoneLocal);
						$localDateTime 		= $dateTimeLocal->format('h:i A');
						$myInt				= strpos($thisID,"/");
						$myCity				= substr($thisID,$myInt+1);
						switch($ii) {
							case 1:
								$timezoneSelector	.= "<tr><td><input type='radio' class='formInputButton' name='inp_timezone_id' value='$thisID' $selector>$myCity<br />$localDateTime</td>";
								$ii++;
								break;
							case 2:
								$timezoneSelector	.= "<td><input type='radio' class='formInputButton' name='inp_timezone_id' value='$thisID' $selector>$myCity<br />$localDateTime</td>";
								$ii++;
								break;
							case 3:
								$timezoneSelector	.= "<td><input type='radio' class='formInputButton' name='inp_timezone_id' value='$thisID' $selector>$myCity<br />$localDateTime</td></tr>";
								$ii					= 1;
								break;
						}
					}
					if ($ii == 2) {			// need two blank cells
						$timezoneSelector			.= "<td>&nbsp;</td><td>&nbsp;</td></tr>";
					} elseif ($ii == 3) {	// need one blank cell
						$timezoneSelector			.= "<td>&nbsp;</td></tr>";
					}
					if ($doDebug) {
						echo "Putting the form together<br />";
					}
					$doProceed		= FALSE;
					$content		.= "<h3>Select Time Zone City</h3>
										<p>Please select the city that best represents the timezone you will be in during the $inp_semester semester. 
										The current local time is displayed underneath the city.</p>
										<form method='post' action='$theURL' 
										name='tzselection' ENCTYPE='multipart/form-data'>
											<input type='hidden' name='strpass' value='3'>
											<input type='hidden' name='inp_mode' value='$inp_mode'>
											<input type='hidden' name='inp_verbose' value='$inp_verbose'>
											<input type='hidden' name='inp_semester' value='$inp_semester'>
											<input type='hidden' name='inp_callsign' value='$inp_callsign'>
											<input type='hidden' name='inp_firstname' value='$inp_firstname'>
											<input type='hidden' name='inp_lastname' value=\"$inp_lastname\">
											<input type='hidden' name='inp_email' value='$inp_email'>
											<input type='hidden' name='inp_phone' value='$inp_phone'>
											<input type='hidden' name='inp_ph_code' value='$inp_ph_code'>
											<input type='hidden' name='inp_text_message' value='$inp_text_message'>
											<input type='hidden' name='inp_city' value='$inp_city'>
											<input type='hidden' name='inp_state' value='$inp_state'>
											<input type='hidden' name='inp_zip' value='$inp_zip'>
											<input type='hidden' name='inp_country' value='$inp_country'>
											<input type='hidden' name='inp_country_code' value='$inp_country_code'>
											<input type='hidden' name='inp_timezone_id' value='$inp_timezone_id'>
											<input type='hidden' name='inp_timezone_offset' value='$inp_timezone_offset'>
											<input type='hidden' name='inp_whatsapp' value='$inp_whatsapp'>
											<input type='hidden' name='inp_signal' value='$inp_signal'>
											<input type='hidden' name='inp_telegram' value='$inp_telegram'>
											<input type='hidden' name='inp_messenger' value='$inp_messenger'>
											<input type='hidden' name='inp_survey_score' value='$inp_survey_score'>
											<input type='hidden' name='inp_languages' value='$inp_languages'>
											<input type='hidden' name='inp_control_code' value='$inp_control_code'>
											<input type='hidden' name='inp_level' value='$inp_level'>
											<input type='hidden' name='inp_class_size' value='$inp_class_size'>
											<input type='hidden' name='inp_class_schedule_days' value='$inp_teaching_days'>
											<input type='hidden' name='inp_class_schedule_times' value='$inp_times'>
											<input type='hidden' name='allowSignup' value='$allowSignup'>
											<input type='hidden' id='browser_timezone_id' name='browser_timezone_id' value='$browser_timezone_id' />
											<input type='hidden' name='inp_bypass' value='Y'>
										$timezoneSelector
										<tr><td colspan='3'><input class='formInputButton' type='submit' value='Submit' /></td></tr>
										</table>
										</form>";

				} else {
					if ($doDebug) {
						echo "found no timezone_identifiers for country code of $inp_country_code<br />";
					}
					$inp_timezone_id		= 'Unknown';
					$inp_timezone_offset	= -99;
				}
			}
		} else {				/// coming back here with the selected timezone identifier
			$inp_timezone_offset	= getOffsetFromIdentifier($inp_timezone_id,$inp_semester);
			if ($inp_timezone_offset === FALSE) {
				if ($doDebug) {
					echo "getOffsetFromIdentifier returned FALSE using $inp_timezone_id, $inp_semester<br />";
				}
				$inp_timezone_offset	= -99;
			}
		}

		if ($doProceed) {								// have the timezone and offset and all else
			$inp_action_log			= "$actionDate ADVREG ";
			//// check to see if the advisor is also signed up as a student
			$studentResult			= get_student_last_class($inp_callsign,$doDebug,$testMode);
			if ($doDebug) {
				echo "studentResult:<br /><pre>";
				print_r($studentResult);
				echo "</pre><br />";
			}
			//// if result['Current']['Semester'] is not empty, the person is signed up to be a student
			//// if semesters match, then set his survey_score to 13
			$myStr			= $studentResult['Current']['Semester'];
			if ($doDebug) {
				echo "Checking if advisor is also a student. Result should be blank: $myStr<br />";
			}
			if ($myStr != '' && $myStr == $inp_semester) {
				$inp_survey_score		= 13;
				$inp_action_log			= "$inp_action_log - survey score set to 13 as advisor is also signed up as a student in $myStr semester ";
				$theSubject				= "CW Academy - Advisor Survey Score Set to 13";
				$theContent				= "Advisor $inp_callsign is also registered as a student in $myStr semester";
				$theRecipient			= '';
				$mailCode				= 18;
				$mailResult				= emailFromCWA_v2(array('theRecipient'=>$theRecipient,
																'theSubject'=>$theSubject,
																'jobname'=>$jobname,
																'theContent'=>$theContent,
																'mailCode'=>$mailCode,
																'increment'=>$increment,
																'testMode'=>$testMode,
																'doDebug'=>$doDebug));
				if ($doDebug) {
					echo "set survey score to 13 and emailed Roland and Bob<br />";
				}
			}

			// Write the advisor record and set up to get the classes informations

			$inp_action_log					= "$inp_action_log - inp record added ";
			$inp_phone						= str_replace("-","",$inp_phone);
			$inp_phone						= str_replace("(","",$inp_phone);
			$inp_phone						= str_replace(")","",$inp_phone);
			$inp_fifo_date					= date('Y-m-d H:i:s',$currentTimestamp);
			$inp_select_sequence			= 0;
			if ($doDebug) {
				echo "preparing to write the $advisorTableName<br />";
			}
			$updateParams				= array("select_sequence|0|d", 
												"call_sign|$inp_callsign|s",
												"first_name|$inp_firstname|s",
												"last_name|$inp_lastname|s",
												"email|$inp_email|s",
												"phone|$inp_phone|s",
												"ph_code|$inp_ph_code|s",
												"text_message|$inp_text_message|s",
												"city|$inp_city|s",
												"state|$inp_state|s",
												"zip_code|$inp_zip|s",
												"country|$inp_country|s",
												"country_code|$inp_country_code|s",
												"timezone_id|$inp_timezone_id|s",
												"timezone_offset|$inp_timezone_offset|f",
												"whatsapp_app|$inp_whatsapp|s",
												"signal_app|$inp_signal|s",
												"telegram_app|$inp_telegram|s",
												"messenger_app|$inp_messenger|s",
												"semester|$inp_semester|s",
												"survey_score|$inp_survey_score|d",
												"languages|$inp_languages|s",
												"fifo_date|$inp_fifo_date|s",
												"action_log|$inp_action_log|s");	
			if ($doDebug) {
				echo "Would insert the following into the $advisorTableName:<br /><pre>";
				print_r($updateParams);
				echo "</pre><br />";
			}

			$advisorUpdateData		= array('tableName'=>$advisorTableName,
											'inp_method'=>'add',
											'inp_data'=>$updateParams,
											'jobname'=>$jobname,
											'inp_id'=>0,
											'inp_callsign'=>$inp_callsign,
											'inp_semester'=>$inp_semester,
											'inp_who'=>$userName,
											'testMode'=>$testMode,
											'doDebug'=>$doDebug);
			$updateResult	= updateAdvisor($advisorUpdateData);
			if ($updateResult[0] === FALSE) {
				if ($doDebug) {
					echo "ERROR<br />";
				}
				$myError	= $wpdb->last_error;
				$mySql		= $wpdb->last_query;
				$errorMsg	= "$jobname inserting $inp_callsign advisor to $advisorTableName failed. Reason: $updateResult[1]<br />SQL: $mySql<br />Error: $myError<br />";
				if ($doDebug) {
					echo $errorMsg;
				}
				sendErrorEmail($errorMsg);
				$content		.= "Unable to update content in $advisorTableName<br />";
			} else {
				$advisor_id		= $updateResult[1];				///// function returns the id
				if ($doDebug) {
					echo "advisor record add completed. ID: $advisor_id<br />";
				}

				////////// insert the class record
				$inp_class_action_log				= "$actionDate ADVREG class record added ";
				$inp_class_schedule_days			= $inp_teaching_days;
				$inp_class_schedule_times			= $inp_times;
				$convertResult		= utcConvert('toutc',$inp_timezone_offset,$inp_times,$inp_teaching_days,$doDebug);
				if ($doDebug) {
					echo "Convert Result:<br /><pre >";
					print_r($convertResult);
					echo "</pre><br />";
				}
				$thisResult			= $convertResult[0];
				if ($thisResult[0] == 'FAIL') {
					$thisReason		= $convertResult[3];
					if ($doDebug) {
						echo "converting $inp_times / $inp_teaching_days to UTC failed: $thisReason<br />";
					}
					$inp_class_schedule_days_utc 	= $inp_teaching_days;
					$inp_class_schedule_times_utc	= $inp_times;
				} else {
					$inp_class_schedule_times_utc	= $convertResult[1];
					$inp_class_schedule_days_utc	= $convertResult[2];
				}
				$updateParams		= array("advisor_call_sign|$inp_callsign|s",
											"advisor_last_name|$inp_lastname|s",
											"advisor_first_name|$inp_firstname|s",
											"advisor_id|$advisor_id|s",
											"sequence|1|d",
											"semester|$inp_semester|s",
											"timezone_id|$inp_timezone_id|s",
											"timezone_offset|$inp_timezone_offset|f",
											"level|$inp_level|s",
											"class_size|$inp_class_size|d",
											"class_schedule_days|$inp_class_schedule_days|s",
											"class_schedule_times|$inp_class_schedule_times|s",
											"class_schedule_days_utc|$inp_class_schedule_days_utc|s",
											"class_schedule_times_utc|$inp_class_schedule_times_utc|s",
											"action_log|$inp_class_action_log|s",
											"class_incomplete|N|s");

				if ($doDebug) {
					echo "Would insert the following into $advisorClassTableName:<br /><pre>";
					print_r($updateParams);
					echo "</pre><br />";
				}

				$classUpdateData		= array('tableName'=>$advisorClassTableName,
												'inp_method'=>'add',
												'inp_data'=>$updateParams,
												'jobname'=>$jobname,
												'inp_id'=>0,
												'inp_callsign'=>$inp_callsign,
												'inp_semester'=>$inp_semester,
												'inp_who'=>$userName,
												'testMode'=>$testMode,
												'doDebug'=>$doDebug);
				$updateResult	= updateClass($classUpdateData);
				if ($updateResult[0] === FALSE) {
					$myError	= $wpdb->last_error;
					$mySql		= $wpdb->last_query;
					$errorMsg	= "$jobname Inserting $advisor_call_sign into $advisorClassTableName failed. Reason: $updateResult[1]<br />SQL: $mySql<br />Error: $myError<br />";
					if ($doDebug) {
						echo $errorMsg;
					}
					sendErrorEmail($errorMsg);
					$content		.= "Unable to update content in $advisorClassTableName<br />";
				} else {
					// display the advisor signup record	
					if ($allowSignup) {
						$inp_semester 	= $nextSemester;
					}
					$displayResult		= getAdvisorInfoToDisplay($inp_callsign,$inp_semester,$noUpdate);
					$content			.= $displayResult[0];
					$content			.= "<p><b>Note:</b> The process to assign students to advisor classes will occur 
											about twenty days before the start of the semester.</p>";
				}
			}

		}




///////////	pass 5		input info for another class

	} elseif ("5" == $strPass) {
		if ($doDebug) {
			echo "at pass 5 with classcount: $classcount and callsign $inp_callsign and semester $inp_semester<br />";
		}
		
		/// get the advisor record
		$cwa_advisor			= $wpdb->get_results("select * from $advisorTableName 
													  where call_sign='$inp_callsign' 
													    and semester='$inp_semester' 
													  order by call_sign");
		if ($cwa_advisor === FALSE) {
			$myError			= $wpdb->last_error;
			$myQuery			= $wpdb->last_query;
			if ($doDebug) {
				echo "Reading $advisorTableName table failed<br />
					  wpdb->last_query: $myQuery<br />
					  wpdb->last_error: $myError<br />";
			}
			$errorMsg			= "$jobname Reading $advisorTableName table failed. <p>SQL: $myQuery</p><p> Error: $myError</p>";
			sendErrorEmail($errorMsg);
		} else {
			$numARows			= $wpdb->num_rows;
			if ($doDebug) {
				$myStr			= $wpdb->last_query;
				echo "ran $myStr<br />and found $numARows rows in $advisorTableName table<br />";
			}
			if ($numARows > 0) {
				foreach ($cwa_advisor as $advisorRow) {
					$advisor_ID							= $advisorRow->advisor_id;
					$advisor_select_sequence 			= $advisorRow->select_sequence;
					$advisor_call_sign 					= strtoupper($advisorRow->call_sign);
					$advisor_first_name 				= $advisorRow->first_name;
					$advisor_last_name 					= stripslashes($advisorRow->last_name);
					$advisor_email 						= strtolower($advisorRow->email);
					$advisor_phone						= $advisorRow->phone;
					$advisor_ph_code					= $advisorRow->ph_code;				// new
					$advisor_text_message 				= $advisorRow->text_message;
					$advisor_city 						= $advisorRow->city;
					$advisor_state 						= $advisorRow->state;
					$advisor_zip_code 					= $advisorRow->zip_code;
					$advisor_country 					= $advisorRow->country;
					$advisor_country_code				= $advisorRow->country_code;		// new
					$advisor_whatsapp					= $advisorRow->whatsapp_app;		// new
					$advisor_signal						= $advisorRow->signal_app;			// new
					$advisor_telegram					= $advisorRow->telegram_app;		// new
					$advisor_messenger					= $advisorRow->messenger_app;		// new
					$advisor_time_zone 					= $advisorRow->time_zone;
					$advisor_timezone_id				= $advisorRow->timezone_id;			// new
					$advisor_timezone_offset			= $advisorRow->timezone_offset;		// new
					$advisor_semester 					= $advisorRow->semester;
					$advisor_survey_score 				= $advisorRow->survey_score;
					$advisor_languages 					= $advisorRow->languages;
					$advisor_fifo_date 					= $advisorRow->fifo_date;
					$advisor_welcome_email_date 		= $advisorRow->welcome_email_date;
					$advisor_verify_email_date 			= $advisorRow->verify_email_date;
					$advisor_verify_email_number 		= $advisorRow->verify_email_number;
					$advisor_verify_response 			= strtoupper($advisorRow->verify_response);
					$advisor_action_log 				= $advisorRow->action_log;
					$advisor_class_verified 			= $advisorRow->class_verified;
					$advisor_control_code 				= $advisorRow->control_code;
					$advisor_date_created 				= $advisorRow->date_created;
					$advisor_date_updated 				= $advisorRow->date_updated;

					$advisor_last_name 					= no_magic_quotes($advisor_last_name);
		
					if ($classcount == 0) {
						$classcount						= 1;
					}
					$stringToPass		= "inp_callsign=$advisor_call_sign&inp_email=$advisor_email&inp_phone=$advisor_phone&inp_mode=$inp_mode&inp_verbose=$inp_verbose&allowSignup=$allowSignup";
					$enstr				= base64_encode($stringToPass);
					// display form to add a class
					$content		.= "<h3>Add a Class for Advisor $advisor_call_sign</h3>
										<p>Enter the information for the class to be added. After you click 'Add Class', the class will 
										be added to the database and you will have the opportunity to add another class if you wish. </p>
										<form method='post' action='$theURL' 
										name='class_add1_form' ENCTYPE='multipart/form-data'>
										<input type='hidden' name='enstr' value='$enstr'>
										<input type='hidden' name='strpass' value='6'>
										<input type='hidden' name='classcount' value='$classcount'>
										<input type='hidden' name='inp_timezone_id' value='$advisor_timezone_id'>
										<input type='hidden' name='inp_timezone_offset' value='$advisor_timezone_offset'>
										<input type='hidden' name='inp_semester' value='$advisor_semester'>
										<input type='hidden' name='inp_firstname' value='$advisor_first_name'>
										<input type='hidden' name='inp_lastname' value=\"$advisor_last_name\">
										<input type='hidden' name='inp_advisor_id' value='$advisor_ID'>
										<table style='width:1000px;'>
										<tr><td style='vertical-align:top;width:330px;'><b>Sequence</b><br />
												$classcount</td>
											<td style='vertical-align:top;width:330px;'><b>Level</b><br />
												<input type='radio' class='formInputButton' id='chk_level' name='inp_level' value='Beginner'> Beginner<br />
												<input type='radio' class='formInputButton' id='chk_level' name='inp_level' value='Fundamental'> Fundamental (formerly Basic)<br />
												<input type='radio' class='formInputButton' id='chk_level' name='inp_level' value='Intermediate'> Intermediate<br />
												<input type='radio' class='formInputButton' id='chk_level' name='inp_level' value='Advanced''> Advanced<br /></td>
											<td style='vertical-align:top;'width:330px;><b>Class Size</b><br />
												<input type='text'class='formInputText' id='chk_class_size' name='inp_class_size' size='5' maxlength='5' value='6'><br />
													(default class size is 6)</td></tr>
										<tr><td style='vertical-align:top;'><b>Class Teaching Days</b><br />Note that most advisors hold classes on Monday and Thursday<br />
												<input type='radio' class='formInputButton' id='check_days' name='inp_teaching_days' value='Sunday,Wednesday' required > Sunday and Wednesday<br />
												<input type='radio' class='formInputButton' id='check_days' name='inp_teaching_days' value='Sunday,Thursday' required > Sunday and Thursday<br />
												<input type='radio' class='formInputButton' id='check_days' name='inp_teaching_days' value='Monday,Thursday' required  checked> Monday and Thursday<br />
												<input type='radio' class='formInputButton' id='check_days' name='inp_teaching_days' value='Tuesday,Friday' required > Tuesday and Friday</td>
											<td style='vertical-align:top;' colspan='2'><b>Local Start Time</b><br />Specify start time in local time in the 
										time zone where you live. Select the time where this class will start. The program will account for standard or daylight 
										savings time or summer time as needed.<br />
											<table><tr>
											<td style='width:110px;vertical-align:top;'>
												<input type='radio' class='formInputButton' id='chk_times' name='inp_times' value='0600'  required > 6:00am<br />
												<input type='radio' class='formInputButton' id='chk_times' name='inp_times' value='0630' required  > 6:30am<br />
												<input type='radio' class='formInputButton' id='chk_times' name='inp_times' value='0700'  required > 7:00am<br />
												<input type='radio' class='formInputButton' id='chk_times' name='inp_times' value='0730'  required > 7:30am<br />
												<input type='radio' class='formInputButton' id='chk_times' name='inp_times' value='0800'  required > 8:00am<br />
												<input type='radio' class='formInputButton' id='chk_times' name='inp_times' value='0830'  required > 8:30am<br />
												<input type='radio' class='formInputButton' id='chk_times' name='inp_times' value='0900'  required > 9:00am<br />
												<input type='radio' class='formInputButton' id='chk_times' name='inp_times' value='0930'  required > 9:30am<br />
												<input type='radio' class='formInputButton' id='chk_times' name='inp_times' value='1000'  required > 10:00am</td>
											<td style='width:110px;vertical-align:top;'>
												<input type='radio' class='formInputButton' id='chk_times' name='inp_times' value='1030'  required > 10:30am<br />
												<input type='radio' class='formInputButton' id='chk_times' name='inp_times' value='1100'  required > 11:00am<br />
												<input type='radio' class='formInputButton' id='chk_times' name='inp_times' value='1130'  required > 11:30am<br />
												<input type='radio' class='formInputButton' id='chk_times' name='inp_times' value='1200'  required > Noon<br />
												<input type='radio' class='formInputButton' id='chk_times' name='inp_times' value='1230'  required > 12:30pm<br />
												<input type='radio' class='formInputButton' id='chk_times' name='inp_times' value='1300'  required > 1:00pm<br />
												<input type='radio' class='formInputButton' id='chk_times' name='inp_times' value='1330'  required > 1:30pm<br />
												<input type='radio' class='formInputButton' id='chk_times' name='inp_times' value='1400'  required > 2:00pm<br />
												<input type='radio' class='formInputButton' id='chk_times' name='inp_times' value='1430'  required > 2:30pm</td>
											<td style='width:110px;vertical-align:top;'>
												<input type='radio' class='formInputButton' id='chk_times' name='inp_times' value='1500'  required > 3:00pm<br />
												<input type='radio' class='formInputButton' id='chk_times' name='inp_times' value='1530'  required > 3:30pm<br />
												<input type='radio' class='formInputButton' id='chk_times' name='inp_times' value='1600'  required > 4:00pm<br />
												<input type='radio' class='formInputButton' id='chk_times' name='inp_times' value='1630'  required > 4:30pm<br />
												<input type='radio' class='formInputButton' id='chk_times' name='inp_times' value='1700'  required > 5:00pm<br />
												<input type='radio' class='formInputButton' id='chk_times' name='inp_times' value='1730'  required > 5:30pm<br />
												<input type='radio' class='formInputButton' id='chk_times' name='inp_times' value='1800'  required > 6:00pm<br />
												<input type='radio' class='formInputButton' id='chk_times' name='inp_times' value='1830'  required > 6:30pm<br />
												<input type='radio' class='formInputButton' id='chk_times' name='inp_times' value='1900'  required > 7:00pm</td>
											<td style='width:110px;vertical-align:top;'>
												<input type='radio' class='formInputButton' id='chk_times' name='inp_times' value='1930' required  > 7:30pm<br />
												<input type='radio' class='formInputButton' id='chk_times' name='inp_times' value='2000'  required > 8:00pm<br />
												<input type='radio' class='formInputButton' id='chk_times' name='inp_times' value='2030' required  > 8:30pm<br />
												<input type='radio' class='formInputButton' id='chk_times' name='inp_times' value='2100' required  > 9:00pm<br />
												<input type='radio' class='formInputButton' id='chk_times' name='inp_times' value='2130'  required > 9:30pm<br />
												<input type='radio' class='formInputButton' id='chk_times' name='inp_times' value='2200'  required > 10:00pm<br />
												<input type='radio' class='formInputButton' id='chk_times' name='inp_times' value='2230'  required > 10:30pm</td></tr>
											</table></td></tr>
										<tr><td>&nbsp;</td><td><input class='formInputButton' type='submit' onclick=\"return validate_form(this.form);\" value='Add the Class' /></td></tr></table>

										</form>";		
				}
			} else {
				if ($doDebug) {
					echo "<b>ERROR:</b> No advisor record found for $inp_callsign when trying to add a class<br />";
				}
			}
		}


///////////// Pass 6
	} elseif ("6" == $strPass) {
		if ($doDebug) {
			echo "At pass 6 with a class record to be added<br />";
		}
		if ($userName == '') {
			$userName						= $inp_callsign;
		}
		////////// insert the class record
		$inp_class_action_log				= "$actionDate ADVREG class record added ";
		$inp_class_schedule_days			= $inp_teaching_days;
		$inp_class_schedule_times			= $inp_times;
		$convertResult		= utcConvert('toutc',$inp_timezone_offset,$inp_times,$inp_teaching_days,$doDebug);
		if ($doDebug) {
			echo "Convert Result:<br /><pre >";
			print_r($convertResult);
			echo "</pre><br />";
		}
		$thisResult			= $convertResult[0];
		if ($thisResult[0] == 'FAIL') {
			$thisReason		= $convertResult[3];
			if ($doDebug) {
				echo "converting $inp_times / $inp_teaching_days to UTC failed: $thisReason<br />";
			}
			$inp_class_schedule_days_utc 	= $inp_teaching_days;
			$inp_class_schedule_times_utc	= $inp_times;
		} else {
			$inp_class_schedule_times_utc	= $convertResult[1];
			$inp_class_schedule_days_utc	= $convertResult[2];
		}
		$updateParams		= array("advisor_call_sign|$inp_callsign|s",
									"advisor_last_name|$inp_lastname|s",
									"advisor_first_name|$inp_firstname|s",
									"advisor_id|$inp_advisor_id|d",
									"sequence|$classcount|d",
									"semester|$inp_semester|s",
									"timezone_id|$inp_timezone_id|s",
									"timezone_offset|$inp_timezone_offset|f",
									"level|$inp_level|s",
									"class_size|$inp_class_size|d",
									"class_schedule_days|$inp_class_schedule_days|s",
									"class_schedule_times|$inp_class_schedule_times|s",
									"class_schedule_days_utc|$inp_class_schedule_days_utc|s",
									"class_schedule_times_utc|$inp_class_schedule_times_utc|s",
									"action_log|$inp_class_action_log|s",
									"class_incomplete|N|s");

		if ($doDebug) {
			echo "Would insert the following into $advisorClassTableName:<br /><pre>";
			print_r($updateParams);
			echo "</pre><br />";
		}
		$classUpdateData		= array('tableName'=>$advisorClassTableName,
										'inp_method'=>'add',
										'inp_data'=>$updateParams,
										'jobname'=>$jobname,
										'inp_id'=>0,
										'inp_callsign'=>$inp_callsign,
										'inp_semester'=>$inp_semester,
										'inp_who'=>$userName,
										'testMode'=>$testMode,
										'doDebug'=>$doDebug);
										
// echo "<br />ready to call updateClass:<br /><pre>";
// print_r($classUpdateData);
// echo "</pre><br /><br />";
										
		$updateResult	= updateClass($classUpdateData);
		if ($updateResult[0] === FALSE) {
			$myError	= $wpdb->last_error;
			$mySql		= $wpdb->last_query;
			$errorMsg	= "$jobname Inserting $inp_callsign into $advisorClassTableName failed. Reason: $updateResult[1]<br />SQL: $mySql<br />Error: $myError<br />";
			if ($doDebug) {
				echo $errorMsg;
			}
			sendErrorEmail($errorMsg);
			$content		.= "Unable to update content in $advisorClassTableName<br />";
		} else {
			// display the advisor signup record	
			$displayResult		= getAdvisorInfoToDisplay($inp_callsign,$inp_semester,$noUpdate);
			$content			.= $displayResult[0];
		}
		


	

///////////		pass 10 update the advisor record

	} elseif ("10" == $strPass) {
		if ($doDebug) {
			echo "At pass 10<br />";
		}

		//// get the advisor record and display the update form
		$sql				= "select * from $advisorTableName 
								where call_sign='$inp_callsign' 
								  and semester='$inp_semester'";
		$wpw1_cwa_advisor		= $wpdb->get_results($sql);
		if ($wpw1_cwa_advisor === FALSE) {
			$myError			= $wpdb->last_error;
			$myQuery			= $wpdb->last_query;
			if ($doDebug) {
				echo "Reading $advisorTableName table failed<br />
					  wpdb->last_query: $myQuery<br />
					  wpdb->last_error: $myError<br />";
			}
			$errorMsg			= "$jobname Reading $advisorTableName table failed. <p>SQL: $myQuery</p><p> Error: $myError</p>";
			sendErrorEmail($errorMsg);
		} else {
			$numARows			= $wpdb->num_rows;
			if ($doDebug) {
				$myStr			= $wpdb->last_query;
				echo "ran $myStr<br />and found $numARows rows in $advisorTableName table<br />";
			}
			if ($numARows > 0) {
				foreach ($wpw1_cwa_advisor as $advisorRow) {
					$advisor_ID							= $advisorRow->advisor_id;
					$advisor_select_sequence 			= $advisorRow->select_sequence;
					$advisor_call_sign 					= strtoupper($advisorRow->call_sign);
					$advisor_first_name 				= $advisorRow->first_name;
					$advisor_last_name 					= stripslashes($advisorRow->last_name);
					$advisor_email 						= strtolower($advisorRow->email);
					$advisor_phone						= $advisorRow->phone;
					$advisor_ph_code					= $advisorRow->ph_code;				// new
					$advisor_text_message 				= $advisorRow->text_message;
					$advisor_city 						= $advisorRow->city;
					$advisor_state 						= $advisorRow->state;
					$advisor_zip_code 					= $advisorRow->zip_code;
					$advisor_country 					= $advisorRow->country;
					$advisor_country_code				= $advisorRow->country_code;		// new
					$advisor_whatsapp					= $advisorRow->whatsapp_app;		// new
					$advisor_signal						= $advisorRow->signal_app;			// new
					$advisor_telegram					= $advisorRow->telegram_app;		// new
					$advisor_messenger					= $advisorRow->messenger_app;		// new
					$advisor_time_zone 					= $advisorRow->time_zone;
					$advisor_timezone_id				= $advisorRow->timezone_id;			// new
					$advisor_timezone_offset			= $advisorRow->timezone_offset;		// new
					$advisor_semester 					= $advisorRow->semester;
					$advisor_survey_score 				= $advisorRow->survey_score;
					$advisor_languages 					= $advisorRow->languages;
					$advisor_fifo_date 					= $advisorRow->fifo_date;
					$advisor_welcome_email_date 		= $advisorRow->welcome_email_date;
					$advisor_verify_email_date 			= $advisorRow->verify_email_date;
					$advisor_verify_email_number 		= $advisorRow->verify_email_number;
					$advisor_verify_response 			= strtoupper($advisorRow->verify_response);
					$advisor_action_log 				= $advisorRow->action_log;
					$advisor_class_verified 			= $advisorRow->class_verified;
					$advisor_control_code 				= $advisorRow->control_code;
					$advisor_date_created 				= $advisorRow->date_created;
					$advisor_date_updated 				= $advisorRow->date_updated;

					$advisor_last_name 					= no_magic_quotes($advisor_last_name);

					$txtMsgY					= '';
					$txtMsgN					= '';
					if ($advisor_text_message == 'Y') {
						$txtMsgY				= "checked='checked'";
					} elseif ($advisor_text_message == 'N') {
						$txtMsgN				= "checked='checked'";
					}

					$inp_semesterChecked			= '';
					$nextSemesterChecked		= '';
					$semesterTwoChecked			= '';
					$semesterThreeChecked		= '';

					if ($advisor_semester == $inp_semester) {
						$inp_semesterChecked	= 'checked';
					}
					$myStr 						= '';
					if ($currentSemester != 'Not in Session') {
						$myStr					= "<input type='radio' class='formInputButton' id='chk_semester' name='inp_semester' value='$inp_semester' $inp_semesterChecked> $inp_semester<br />";
					}
					if ($advisor_semester == $nextSemester) {
						$nextSemesterChecked	= 'checked';
					}
					if ($advisor_semester == $semesterTwo) {
						$semesterTwoChecked	= 'checked';
					}
					if ($advisor_semester == $semesterThree) {
						$semesterThreeChecked	= 'checked';
					}
					
					if ($advisor_country_code != '') {
						if (in_array($advisor_country_code,$countryCheckedArray)) {
							${$advisor_country_code . '_checked'} 	= 'checked';
						} else {
							${$advisor_country_code . '_checked'} 	= 'selected';
						}
						if ($doDebug) {
							echo "set advisor_country_code_checked to ${$advisor_country_code . '_checked'}<br />";
						}
					}

					$enstr				= base64_encode("inp_callsign=$advisor_call_sign&inp_mode=$inp_mode&inp_verbose=$inp_verbose&pageSource=10");
					// display form to modify an advisor
					$content		.= "<p>Following is your current sign-up information. Make 
										any corrections and click on 'Update'. If there are no changes, you can close the window.</p>
										<form method='post' action='$theURL' 
										name='advisor_signup_form' ENCTYPE='multipart/form-data'>
										<input type='hidden' name='strpass' value='11'>
										<input type='hidden' name='enstr' value='$enstr'>
										<input type='hidden' name='inp_country_code' value='$advisor_country_code'>
										<input type='hidden' name='inp_ph_code' value='$advisor_ph_code'>
										<input type='hidden' name='inp_timezone_id' value='$advisor_timezone_id'>
										<input type='hidden' name='inp_timezone_offset' value='$advisor_timezone_offset'>
										<input type='hidden' name='inp_advisor_id' value='$advisor_ID'>
										<input type='hidden' name='allowSignup' value='$allowSignup'>
										<table style='width:1000px;'>
										<tr><td style='vertical-align:top;width:330px;'><b>Call Sign</b><br />
												$inp_callsign</td>
											<td style='vertical-align:top;width:330px;'><b>Last Name</b><br />
												<input type='text' class='formInputText' id='chk_lastname' name='inp_lastname' value=\"$advisor_last_name\"></td>
											<td style='vertical-align:top;'><b>First Name</b><br />
												<input type='text' class='formInputText' id='chk_firstname' name='inp_firstname' value='$advisor_first_name'></td></tr>
										<tr><td style='vertical-align:top;'><b>Semester</b><br />
												$myStr
												<input type='radio' class='formInputButton' id='chk_semester' name='inp_semester' value='$nextSemester' $nextSemesterChecked> $nextSemester<br />
												<input type='radio' class='formInputButton' id='chk_semester' name='inp_semester' value='$semesterTwo' $semesterTwoChecked> $semesterTwo<br />
												<input type='radio' class='formInputButton' id='chk_semester' name='inp_semester' value='$semesterThree'$semesterThreeChecked> $semesterThree</td>
											<td style='vertical-align:top;'><b>Email</b><br />
												<input type='text' class='formInputText' id='chk_email' name='inp_email' value='$advisor_email' size='50' maxlength='100'></td>
											<td style='vertical-align:top;'><b>Phone</b>
												<input type='text' class='formInputText' id='chk_phone' name='inp_phone' value='$advisor_phone' size='50' maxlength='50'><br />
												Can this phone receive text messages?<br />
												<input type='radio' class='formInputButton' name='inp_text_message' value='Y' $txtMsgY > Yes<br />
												<input type='radio' class='formInputButton' name='inp_text_message' Value='N' $txtMsgN> No<br /></td></tr>
										<tr><td style='vertical-align:top;'><b>City</b><br />
												<input type='text' class='formInputText' id='inp_city' name='inp_city' value='$advisor_city' ></td>
											<td style='vertical-align:top;'><b>State / Region / Province</b><br />
												<input type='text' class='formInputText' id='chk_state' name='inp_state' value='$advisor_state' ></td>
											<td style='vertical-align:top;'><b>Zip / Postal Code</b> Zip/Postal Code is required for US residents<br />
												<input type='text' class='formInputText' name='inp_zip' value='$advisor_zip_code' ><br />
												</td></tr>

										<tr><td colspan='3' style='vertical-align:top;'>
												<b>Country</b><br />
												Your country is currently set to $advisor_country. To change the country, select from the lists below.
										<table style='width:100%;'>
										<tr>
										<td><input type='radio' class='formInputButton' id='chk_country' name='inp_country'  value='AR|Argentina' $AR_checked>Argentina</td>
										<td><input type='radio' class='formInputButton' id='chk_country' name='inp_country'  value='AU|Australia' $AU_checked>Australia</td>
										<td><input type='radio' class='formInputButton' id='chk_country' name='inp_country'  value='AT|Austria' $AT_checked>Austria</td>
										<td><input type='radio' class='formInputButton' id='chk_country' name='inp_country'  value='BE|Belgium' $BE_checked>Belgium</td>
										<td><input type='radio' class='formInputButton' id='chk_country' name='inp_country'  value='BR|Brazil' $BR_checked>Brazil</td>
										</tr><tr>
										<td><input type='radio' class='formInputButton' id='chk_country' name='inp_country'  value='CA|Canada' $CA_checked>Canada</td>
										<td><input type='radio' class='formInputButton' id='chk_country' name='inp_country'  value='CL|Chile' $CL_checked>Chile</td>
										<td><input type='radio' class='formInputButton' id='chk_country' name='inp_country'  value='CN|China' $CN_checked>China</td>
										<td><input type='radio' class='formInputButton' id='chk_country' name='inp_country'  value='CZ|Czech Republic' $CZ_checked>Czech Republic</td>
										<td><input type='radio' class='formInputButton' id='chk_country' name='inp_country'  value='DK|Denmark' $DK_checked>Denmark</td>
										</tr><tr>
										<td><input type='radio' class='formInputButton' id='chk_country' name='inp_country'  value='FI|Finland' $FI_checked>Finland</td>
										<td><input type='radio' class='formInputButton' id='chk_country' name='inp_country'  value='FR|France' $FR_checked>France</td>
										<td><input type='radio' class='formInputButton' id='chk_country' name='inp_country'  value='DE|Germany' $DE_checked>Germany</td>
										<td><input type='radio' class='formInputButton' id='chk_country' name='inp_country'  value='GR|Greece' $GR_checked>Greece</td>
										<td><input type='radio' class='formInputButton' id='chk_country' name='inp_country'  value='IN|India' $IN_checked>India</td>
										</tr><tr>
										<td><input type='radio' class='formInputButton' id='chk_country' name='inp_country'  value='IE|Ireland' $IE_checked>Ireland</td>
										<td><input type='radio' class='formInputButton' id='chk_country' name='inp_country'  value='IL|Israel' $IL_checked>Israel</td>
										<td><input type='radio' class='formInputButton' id='chk_country' name='inp_country'  value='IT|Italy' $IT_checked>Italy</td>
										<td><input type='radio' class='formInputButton' id='chk_country' name='inp_country'  value='JP|Japan' $JP_checked>Japan</td>
										<td><input type='radio' class='formInputButton' id='chk_country' name='inp_country'  value='KR|Korea' $KR_checked>Korea</td>
										</tr><tr>
										<td><input type='radio' class='formInputButton' id='chk_country' name='inp_country'  value='MX|Mexico' $MX_checked>Mexico</td>
										<td><input type='radio' class='formInputButton' id='chk_country' name='inp_country'  value='NL|Netherlands' $NL_checked>Netherlands</td>
										<td><input type='radio' class='formInputButton' id='chk_country' name='inp_country'  value='NZ|New Zealand' $NZ_checked>New Zealand</td>
										<td><input type='radio' class='formInputButton' id='chk_country' name='inp_country'  value='NO|Norway' $NO_checked>Norway</td>
										<td><input type='radio' class='formInputButton' id='chk_country' name='inp_country'  value='PH|Philippines' $PH_checked>Philippines</td>
										</tr><tr>
										<td><input type='radio' class='formInputButton' id='chk_country' name='inp_country'  value='PL|Poland' $PL_checked>Poland</td>
										<td><input type='radio' class='formInputButton' id='chk_country' name='inp_country'  value='PT|Portugal' $PT_checked>Portugal</td>
										<td><input type='radio' class='formInputButton' id='chk_country' name='inp_country'  value='PR|Puerto Rico' $PR_checked>Puerto Rico</td>
										<td><input type='radio' class='formInputButton' id='chk_country' name='inp_country'  value='KR|Republic of Korea' $KR_checked>Republic of Korea</td>
										<td><input type='radio' class='formInputButton' id='chk_country' name='inp_country'  value='RU|Russian Federation' $RU_checked>Russian Federation</td>
										</tr><tr>
										<td><input type='radio' class='formInputButton' id='chk_country' name='inp_country'  value='ZA|South Africa' $ZA_checked>South Africa</td>
										<td><input type='radio' class='formInputButton' id='chk_country' name='inp_country'  value='ES|Spain' $ES_checked>Spain</td>
										<td><input type='radio' class='formInputButton' id='chk_country' name='inp_country'  value='SE|Sweden' $SE_checked>Sweden</td>
										<td><input type='radio' class='formInputButton' id='chk_country' name='inp_country'  value='CH|Switzerland' $CH_checked>Switzerland</td>
										<td><input type='radio' class='formInputButton' id='chk_country' name='inp_country'  value='GB|United Kingdom' $GB_checked>United Kingdom (England, Scotland, Wales, Northern Ireland)</td>
										</tr><tr>
										<td><input type='radio' class='formInputButton' id='chk_country' name='inp_country'  value='US|United States' $US_checked>United States</td>
										<td></td>
										<td></td>
										<td></td>
										<td></td>
										</tr>
										</table>
										<p>If your country is not listed above, please select it from the list below:</p>

												<select name='inp_countryb' id='chk_countryb' class='formSelect' size='5'>
										<option value='AF|Afghanistan' $AF_checked>Afghanistan</option>
										<option value='AL|Albania' $AL_checked>Albania</option>
										<option value='DZ|Algeria' $DZ_checked>Algeria</option>
										<option value='AX|Alland Islands' $AX_checked>Alland Islands</option>
										<option value='AS|American Samoa' $AS_checked>American Samoa</option>
										<option value='AD|Andorra' $AD_checked>Andorra</option>
										<option value='AO|Angola' $AO_checked>Angola</option>
										<option value='AI|Anguilla' $AI_checked>Anguilla</option>
										<option value='AQ|Antarctica' $AQ_checked>Antarctica</option>
										<option value='AG|Antigua and Barbuda' $AG_checked>Antigua and Barbuda</option>
										<option value='AM|Armenia' $AM_checked>Armenia</option>
										<option value='AW|Aruba' $AW_checked>Aruba</option>
										<option value='AZ|Azerbaijan' $AZ_checked>Azerbaijan</option>
										<option value='BD|Bangladesh' $BD_checked>Bangladesh</option>
										<option value='BS|Bahamas' $BS_checked>Bahamas</option>
										<option value='BH|Bahrain' $BH_checked>Bahrain</option>
										<option value='BB|Barbados' $BB_checked>Barbados</option>
										<option value='BY|Belarus' $BY_checked>Belarus</option>
										<option value='BZ|Belize' $BZ_checked>Belize</option>
										<option value='BJ|Benin' $BJ_checked>Benin</option>
										<option value='BM|Bermuda' $BM_checked>Bermuda</option>
										<option value='BT|Bhutan' $BT_checked>Bhutan</option>
										<option value='BO|Bolivia - Plurinational State of' $BO_checked>Bolivia - Plurinational State of</option>
										<option value='BQ|Bonaire - Sint Eustatius and Saba' $BQ_checked>Bonaire - Sint Eustatius and Saba</option>
										<option value='BA|Bosnia and Herzegovina' $BA_checked>Bosnia and Herzegovina</option>
										<option value='BW|Botswana' $BW_checked>Botswana</option>
										<option value='BG|Bulgaria' $BG_checked>Bulgaria</option>
										<option value='BV|Bouvet Island' $BV_checked>Bouvet Island</option>
										<option value='IO|British Indian Ocean Territory' $IO_checked>British Indian Ocean Territory</option>
										<option value='BN|Brunei' $BN_checked>Brunei</option>
										<option value='BF|Burkina Faso' $BF_checked>Burkina Faso</option>
										<option value='BI|Burundi' $BI_checked>Burundi</option>
										<option value='KH|Cambodia' $KH_checked>Cambodia</option>
										<option value='CM|Cameroon' $CM_checked>Cameroon</option>
										<option value='CV|Cape Verde' $CV_checked>Cape Verde</option>
										<option value='KY|Cayman Islands' $KY_checked>Cayman Islands</option>
										<option value='CF|Central African Republic' $CF_checked>Central African Republic</option>
										<option value='TD|Chad' $TD_checked>Chad</option>
										<option value='CX|Christmas Island' $CX_checked>Christmas Island</option>
										<option value='CC|Cocos (Keeling) Islands' $CC_checked>Cocos (Keeling) Islands</option>
										<option value='CO|Colombia' $CO_checked>Colombia</option>
										<option value='KM|Comoros' $KM_checked>Comoros</option>
										<option value='CD|Congo' $CD_checked>Congo</option>
										<option value='CG|Congo' $CG_checked>Congo</option>
										<option value='CK|Cook Islands' $CK_checked>Cook Islands</option>
										<option value='CR|Costa Rica' $CR_checked>Costa Rica</option>
										<option value='HR|Croatia' $HR_checked>Croatia</option>
										<option value='CU|Cuba' $CU_checked>Cuba</option>
										<option value='CW|Curascao' $CW_checked>Curascao</option>
										<option value='CY|Cyprus' $CY_checked>Cyprus</option>
										<option value='KP|Democratic Peoples Republic of Korea $KP_checked>Democratic Peoples Republic of Korea</option>
										<option value='DJ|Djibouti' $DJ_checked>Djibouti</option>
										<option value='DM|Dominica' $DM_checked>Dominica</option>
										<option value='DO|Dominican Republic' $DO_checked>Dominican Republic</option>
										<option value='EC|Ecuador' $EC_checked>Ecuador</option>
										<option value='EG|Egypt' $EG_checked>Egypt</option>
										<option value='GQ|Equatorial Guinea' $GQ_checked>Equatorial Guinea</option>
										<option value='ER|Eritrea' $ER_checked>Eritrea</option>
										<option value='EE|Estonia' $EE_checked>Estonia</option>
										<option value='ET|Ethiopia' $ET_checked>Ethiopia</option>
										<option value='FK|Falkland Islands (Malvinas)' $FK_checked>Falkland Islands (Malvinas)</option>
										<option value='FO|Faroe Islands' $FO_checked>Faroe Islands</option>
										<option value='FM|Federated States of Micronesia' $FM_checked>Federated States of Micronesia</option>
										<option value='FJ|Fiji' $FJ_checked>Fiji</option>
										<option value='GF|French Guiana' $GF_checked>French Guiana</option>
										<option value='PF|French Polynesia' $PF_checked>French Polynesia</option>
										<option value='TF|French Southern Territories' $TF_checked>French Southern Territories</option>
										<option value='GA|Gabon' $GA_checked>Gabon</option>
										<option value='GM|Gambia' $GM_checked>Gambia</option>
										<option value='GE|Georgia' $GE_checked>Georgia</option>
										<option value='GH|Ghana' $GH_checked>Ghana</option>
										<option value='GI|Gibraltar' $GI_checked>Gibraltar</option>
										<option value='GL|Greenland' $GL_checked>Greenland</option>
										<option value='GD|Grenada' $GD_checked>Grenada</option>
										<option value='GP|Guadeloupe' $GP_checked>Guadeloupe</option>
										<option value='GU|Guam' $GU_checked>Guam</option>
										<option value='GT|Guatemala' $GT_checked>Guatemala</option>
										<option value='GG|Guernsey' $GG_checked>Guernsey</option>
										<option value='GW|Guinea-Bissau' $GW_checked>Guinea-Bissau</option>
										<option value='GN|Guinea' $GN_checked>Guinea</option>
										<option value='GY|Guyana' $GY_checked>Guyana</option>
										<option value='HT|Haiti' $HT_checked>Haiti</option>
										<option value='HM|Heard Island and McDonald Islands' $HM_checked>Heard Island and McDonald Islands</option>
										<option value='VA|Holy See (Vatican City State)' $VA_checked>Holy See (Vatican City State)</option>
										<option value='HN|Honduras' $HN_checked>Honduras</option>
										<option value='HK|Hong Kong' $HK_checked>Hong Kong</option>
										<option value='HU|Hungary' $HU_checked>Hungary</option>
										<option value='IS|Iceland' $IS_checked>Iceland</option>
										<option value='ID|Indonesia' $ID_checked>Indonesia</option>
										<option value='IR|Iran' $IR_checked>Iran</option>
										<option value='IQ|Iraq' $IQ_checked>Iraq</option>
										<option value='IR|Islamic Republic of Iran' $IR_checked>Islamic Republic of Iran</option>
										<option value='IM|Isle of Man' $IM_checked>Isle of Man</option>
										<option value='CI|Ivory Coast' $CI_checked>Ivory Coast</option>
										<option value='JM|Jamaica' $JM_checked>Jamaica</option>
										<option value='JE|Jersey' $JE_checked>Jersey</option>
										<option value='JO|Jordan' $JO_checked>Jordan</option>
										<option value='KZ|Kazakhstan' $KZ_checked>Kazakhstan</option>
										<option value='KE|Kenya' $KE_checked>Kenya</option>
										<option value='KI|Kiribati' $KI_checked>Kiribati</option>
										<option value='KW|Kuwait' $KW_checked>Kuwait</option>
										<option value='KG|Kyrgyzstan' $KG_checked>Kyrgyzstan</option>
										<option value='LA|Lao Peoples Democratic Republic' $LA_checked>Lao Peoples Democratic Republic</option>
										<option value='LA|Laos' $LA_checked>Laos</option>
										<option value='LV|Latvia' $LV_checked>Latvia</option>
										<option value='LB|Lebanon' $LB_checked>Lebanon</option>
										<option value='LS|Lesotho' $LS_checked>Lesotho</option>
										<option value='LR|Liberia' $LR_checked>Liberia</option>
										<option value='LY|Libya' $LY_checked>Libya</option>
										<option value='LI|Liechtenstein' $LI_checked>Liechtenstein</option>
										<option value='LT|Lithuania' $LT_checked>Lithuania</option>
										<option value='LU|Luxembourg' $LU_checked>Luxembourg</option>
										<option value='MO|Macao' $MO_checked>Macao</option>
										<option value='MK|Macedonia' $MK_checked>Macedonia</option>
										<option value='MG|Madagascar' $MG_checked>Madagascar</option>
										<option value='MW|Malawi' $MW_checked>Malawi</option>
										<option value='MY|Malaysia' $MY_checked>Malaysia</option>
										<option value='MV|Maldives' $MV_checked>Maldives</option>
										<option value='ML|Mali' $ML_checked>Mali</option>
										<option value='MT|Malta' $MT_checked>Malta</option>
										<option value='MH|Marshall Islands' $MH_checked>Marshall Islands</option>
										<option value='MQ|Martinique' $MQ_checked>Martinique</option>
										<option value='MR|Mauritania' $MR_checked>Mauritania</option>
										<option value='MU|Mauritius' $MU_checked>Mauritius</option>
										<option value='YT|Mayotte' $YT_checked>Mayotte</option>
										<option value='FM|Micronesia' $FM_checked>Micronesia</option>
										<option value='MD|Moldova' $MD_checked>Moldova</option>
										<option value='MC|Monaco' $MC_checked>Monaco</option>
										<option value='MN|Mongolia' $MN_checked>Mongolia</option>
										<option value='ME|Montenegro' $ME_checked>Montenegro</option>
										<option value='MS|Montserrat' $MS_checked>Montserrat</option>
										<option value='MA|Morocco' $MA_checked>Morocco</option>
										<option value='MZ|Mozambique' $MZ_checked>Mozambique</option>
										<option value='MM|Myanmar' $MM_checked>Myanmar</option>
										<option value='NA|Namibia' $NA_checked>Namibia</option>
										<option value='NR|Nauru' $NR_checked>Nauru</option>
										<option value='NP|Nepal' $NP_checked>Nepal</option>
										<option value='NC|New Caledonia' $NC_checked>New Caledonia</option>
										<option value='NI|Nicaragua' $NI_checked>Nicaragua</option>
										<option value='NE|Niger' $NE_checked>Niger</option>
										<option value='NG|Nigeria' $NG_checked>Nigeria</option>
										<option value='NU|Niue' $NU_checked>Niue</option>
										<option value='NF|Norfolk Island' $NF_checked>Norfolk Island</option>
										<option value='MP|Northern Mariana Islands' $MP_checked>Northern Mariana Islands</option>
										<option value='OM|Oman' $OM_checked>Oman</option>
										<option value='PK|Pakistan' $PK_checked>Pakistan</option>
										<option value='PW|Palau' $PW_checked>Palau</option>
										<option value='PS|Palestine' $PS_checked>Palestine</option>
										<option value='PA|Panama' $PA_checked>Panama</option>
										<option value='PG|Papua New Guinea' $PG_checked>Papua New Guinea</option>
										<option value='PY|Paraguay' $PY_checked>Paraguay</option>
										<option value='PE|Peru' $PE_checked>Peru</option>
										<option value='PN|Pitcairn' $PN_checked>Pitcairn</option>
										<option value='QA|Qatar' $QA_checked>Qatar</option>
										<option value='MD|Republic of Moldova' $MD_checked>Republic of Moldova</option>
										<option value='RE|Reunion' $RE_checked>Reunion</option>
										<option value='RO|Romania' $RO_checked>Romania</option>
										<option value='RW|Rwanda' $RW_checked>Rwanda</option>
										<option value='BL|Saint BarthÃ©lemy' $BL_checked>Saint BarthÃ©lemy</option>
										<option value='SH|Saint Helena Ascension and Tristan da Cunha' $SH_checked>Saint Helena Ascension and Tristan da Cunha</option>
										<option value='KN|Saint Kitts and Nevis' $KN_checked>Saint Kitts and Nevis</option>
										<option value='LC|Saint Lucia' $LC_checked>Saint Lucia</option>
										<option value='MF|Saint Martin (French part)' $MF_checked>Saint Martin (French part)</option>
										<option value='PM|Saint Pierre and Miquelon' $PM_checked>Saint Pierre and Miquelon</option>
										<option value='VC|Saint Vincent and the Grenadines' $VC_checked>Saint Vincent and the Grenadines</option>
										<option value='WS|Samoa' $WS_checked>Samoa</option>
										<option value='SM|San Marino' $SM_checked>San Marino</option>
										<option value='ST|Sao Tome and Principe' $ST_checked>Sao Tome and Principe</option>
										<option value='SA|Saudi Arabia' $SA_checked>Saudi Arabia</option>
										<option value='SN|Senegal' $SN_checked>Senegal</option>
										<option value='RS|Serbia' $RS_checked>Serbia</option>
										<option value='SC|Seychelles' $SC_checked>Seychelles</option>
										<option value='SL|Sierra Leone' $SL_checked>Sierra Leone</option>
										<option value='SG|Singapore' $SG_checked>Singapore</option>
										<option value='SX|Sint Maarten (Dutch part)' $SX_checked>Sint Maarten (Dutch part)</option>
										<option value='SK|Slovakia' $SK_checked>Slovakia</option>
										<option value='SI|Slovenia' $SI_checked>Slovenia</option>
										<option value='SB|Solomon Islands' $SB_checked>Solomon Islands</option>
										<option value='SO|Somalia' $SO_checked>Somalia</option>
										<option value='GS|South Georgia and the South Sandwich Islands' $GS_checked>South Georgia and the South Sandwich Islands</option>
										<option value='SS|South Sudan' $SS_checked>South Sudan</option>
										<option value='LK|Sri Lanka' $LK_checked>Sri Lanka</option>
										<option value='PS|State of Palestine' $PS_checked>State of Palestine</option>
										<option value='SD|Sudan' $SD_checked>Sudan</option>
										<option value='SR|Suriname' $SR_checked>Suriname</option>
										<option value='SJ|Svalbard and Jan Mayen' $SJ_checked>Svalbard and Jan Mayen</option>
										<option value='SZ|Swaziland' $SZ_checked>Swaziland</option>
										<option value='SY|Syrian Arab Republic' $SY_checked>Syrian Arab Republic</option>
										<option value='TW|Taiwan - Province of China' $TW_checked>Taiwan - Province of China</option>
										<option value='TJ|Tajikistan' $TJ_checked>Tajikistan</option>
										<option value='TH|Thailand' $TH_checked>Thailand</option>
										<option value='CD|The Democratic Republic of the Congo' $CD_checked>The Democratic Republic of the Congo</option>
										<option value='TL|Timor-Leste' $TL_checked>Timor-Leste</option>
										<option value='TG|Togo' $TG_checked>Togo</option>
										<option value='TK|Tokelau' $TK_checked>Tokelau</option>
										<option value='TO|Tonga' $TO_checked>Tonga</option>
										<option value='TT|Trinidad and Tobago' $TT_checked>Trinidad and Tobago</option>
										<option value='TR|Turkey' $TR_checked>Turkey</option>
										<option value='TM|Turkmenistan' $TM_checked>Turkmenistan</option>
										<option value='TC|Turks and Caicos Islands' $TC_checked>Turks and Caicos Islands</option>
										<option value='TV|Tuvalu' $TV_checked>Tuvalu</option>
										<option value='UG|Uganda' $UG_checked>Uganda</option>
										<option value='UA|Ukraine' $UA_checked>Ukraine</option>
										<option value='AE|United Arab Emirates' $AE_checked>United Arab Emirates</option>
										<option value='TZ|United Republic of Tanzania' $TZ_checked>United Republic of Tanzania</option>
										<option value='UM|United States Minor Outlying Islands' $UM_checked>United States Minor Outlying Islands</option>
										<option value='UY|Uruguay' $UY_checked>Uruguay</option>
										<option value='UZ|Uzbekistan' $UZ_checked>Uzbekistan</option>
										<option value='VU|Vanuatu' $VU_checked>Vanuatu</option>
										<option value='VE|Venezuela - Bolivarian Republic of' $VE_checked>Venezuela - Bolivarian Republic of</option>
										<option value='VN|Viet Nam' $VN_checked>Viet Nam</option>
										<option value='VG|Virgin Islands - British' $VG_checked>Virgin Islands - British</option>
										<option value='VI|Virgin Islands - U.S.' $VI_checked>Virgin Islands - U.S.</option>
										<option value='WF|Wallis and Futuna' $WF_checked>Wallis and Futuna</option>
										<option value='EH|Western Sahara' $EH_checked>Western Sahara</option>
										<option value='YE|Yemen' $YE_checked>Yemen</option>
										<option value='ZM|Zambia' $ZM_checked>Zambia</option>
										<option value='ZW|Zimbabwe' $ZW_checked>Zimbabwe</option>
										</select></td>
										</tr>

										<tr><td style='vertical-align:top;'><b>Languages Spoken</b><br />
												<input type='text' class='formInputText' id='chk_languages' name='inp_languages' value='$advisor_languages' ></td>
											<td style='vertical-align:top;'>&nbsp;</td>
											<td style='vertical-align:top;'>&nbsp;</td></tr>
										<tr><td colspan='3'>If you use any of these other messaging services, you may optionally enter your user id</td></tr>
										<tr><td colspan='3'>
											<table>
												<tr><td style='vertical-align:top;'><b>Whatsapp</b><br />
														<input type='text' class='formInputText' id='chk_whatsapp' name='inp_whatsapp' value='$advisor_whatsapp' size='20' maxlength='20'></td>
													<td style='vertical-align:top;'><b>Signal</b><br />
														<input type='text' class='formInputText' id='chk_signal' name='inp_signal' value='$advisor_signal' size='20' maxlength='20'></td>
													<td style='vertical-align:top;'><b>Telegram</b><br />
														<input type='text' class='formInputText' id='chk_telegram' name='inp_telegram' value='$advisor_telegram' size='20' maxlength='20'></td>
													<td style='vertical-align:top;'><b>Messenger</b><br />
														<input type='text' class='formInputText' id='chk_messenger' name='inp_messenger' value='$advisor_messenger' size='20' maxlength='20'></td></tr>
											</table></td></tr>
										<tr><td colspan='3'><input class='formInputButton' type='submit' onclick=\"return validate_form(this.form);\" value='Update' /></td></tr>
										</table></form>";
				}
			} else {
				if ($doDebug) {
					echo "Unable to obtain the advisor record. Should be there!<br />";
				}
				$content		.= "Fatal error. The appropriate people have been notified<br />";
			} 
		}

	

///////////		pass 11 update the advisor record

	} elseif ("11" == $strPass) {
		if ($doDebug) {
			echo "<br />At pass 11 with $inp_callsign<br />";
		}


		$userName			= $inp_callsign;
		$content			.= "<h3>Advisor Sign-up for $inp_callsign</h3>";
		$redoTimezone		= FALSE;					// set TRUE if CC=US and zip changes or country changes
		$finalContent		= '';
		$log_mode			= '';	// 0	TESTMODE or Production
		$log_file			= '';
		if ($testMode) {
			$log_mode		= 'testMode';
			$log_file		= 'TestAdvisor';
		} else {
			$log_mode		= 'Production';
			$log_file		= 'Advisor';
		}
		$log_actionDate				= date('Y-m-d H:i',$currentTimestamp);	
		$log_action					= '';	// 2
		$log_type					= '';	// 3
		$advisorDeleted				= FALSE;
		$nextSequence				= 0;		

		// get the requested record	and verify
		if($doDebug) {
			echo "getting the $advisorTableName record for $inp_callsign<br />";
		}
		$sql				= "select * from $advisorTableName 
								where call_sign='$inp_callsign' 
								  and semester='$inp_semester' 
								order by call_sign";
		$wpw1_cwa_advisor			= $wpdb->get_results($sql);
		if ($wpw1_cwa_advisor === FALSE) {
			$myError			= $wpdb->last_error;
			$myQuery			= $wpdb->last_query;
			if ($doDebug) {
				echo "Reading $advisorTableName table failed<br />
					  wpdb->last_query: $myQuery<br />
					  wpdb->last_error: $myError<br />";
			}
			$errorMsg			= "$jobname Reading $advisorTableName table failed. <p>SQL: $myQuery</p><p> Error: $myError</p>";
			sendErrorEmail($errorMsg);
		} else {
			$numARows			= $wpdb->num_rows;
			if ($doDebug) {
				$myStr			= $wpdb->last_query;
				echo "ran $myStr<br />and found $numARows rows in $advisorTableName table<br />";
			}
			if ($numARows > 0) {
				foreach ($wpw1_cwa_advisor as $advisorRow) {
					$advisor_ID							= $advisorRow->advisor_id;
					$advisor_select_sequence 			= $advisorRow->select_sequence;
					$advisor_call_sign 					= strtoupper($advisorRow->call_sign);
					$advisor_first_name 				= $advisorRow->first_name;
					$advisor_last_name 					= stripslashes($advisorRow->last_name);
					$advisor_email 						= strtolower($advisorRow->email);
					$advisor_phone						= $advisorRow->phone;
					$advisor_ph_code					= $advisorRow->ph_code;				// new
					$advisor_text_message 				= $advisorRow->text_message;
					$advisor_city 						= $advisorRow->city;
					$advisor_state 						= $advisorRow->state;
					$advisor_zip_code 					= $advisorRow->zip_code;
					$advisor_country 					= $advisorRow->country;
					$advisor_country_code				= $advisorRow->country_code;		// new
					$advisor_whatsapp					= $advisorRow->whatsapp_app;		// new
					$advisor_signal						= $advisorRow->signal_app;			// new
					$advisor_telegram					= $advisorRow->telegram_app;		// new
					$advisor_messenger					= $advisorRow->messenger_app;		// new
					$advisor_time_zone 					= $advisorRow->time_zone;
					$advisor_timezone_id				= $advisorRow->timezone_id;			// new
					$advisor_timezone_offset			= $advisorRow->timezone_offset;		// new
					$advisor_semester 					= $advisorRow->semester;
					$advisor_survey_score 				= $advisorRow->survey_score;
					$advisor_languages 					= $advisorRow->languages;
					$advisor_fifo_date 					= $advisorRow->fifo_date;
					$advisor_welcome_email_date 		= $advisorRow->welcome_email_date;
					$advisor_verify_email_date 			= $advisorRow->verify_email_date;
					$advisor_verify_email_number 		= $advisorRow->verify_email_number;
					$advisor_verify_response 			= strtoupper($advisorRow->verify_response);
					$advisor_action_log 				= $advisorRow->action_log;
					$advisor_class_verified 			= $advisorRow->class_verified;
					$advisor_control_code 				= $advisorRow->control_code;
					$advisor_date_created 				= $advisorRow->date_created;
					$advisor_date_updated 				= $advisorRow->date_updated;

					$advisor_last_name 					= no_magic_quotes($advisor_last_name);

				
					if ($doDebug) {
						echo "Have obtained a record for $advisor_call_sign $advisor_semester<br />";

					}
					$doUpdate							= FALSE;
					$updateParams						= array();
					$updateFormats						= array();
					$actionLogData						= '';
					$updateContent						= '';
					$zipUpdated							= FALSE;
					$countryUpdated						= FALSE;
					$firstNameUpdated					= FALSE;
					$lastNameUpdated					= FALSE;
					if ($inp_firstname != $advisor_first_name) {
						if ($doDebug) {
							echo "first_name: $inp_firstname != $advisor_first_name<br />";
						}
						$doUpdate						= TRUE;
						$updateParams['first_name']		= $inp_firstname;
						$updateFormat[]					= '%s';
						$actionLogData					.= " Updated first name from $advisor_first_name to $inp_firstname. ";
						$updateContent					.= "Updated first name from $advisor_first_name to $inp_firstname.<br />";
						$advisor_first_name				= $inp_firstname;
						$firstNameUpdated				= TRUE;
					}
					if ($inp_lastname != $advisor_last_name) {
						if ($doDebug) {
							echo "last_name: $inp_lastname != $advisor_last_name<br />";
						}
						$doUpdate						= TRUE;
						$actionLogData					.= " Updated last_name from $advisor_last_name to $inp_lastname. ";
						$updateContent					.= "Updated last_name from $advisor_last_name to $inp_lastname.<br />";
						$advisor_last_name				= $inp_lastname;
						$updateParams['last_name']		= addslashes($inp_lastname);
						$updateFormat[]					= '%s';
						$lastNameUpdated				= TRUE;
					}
					if ($inp_email != $advisor_email) {
						if ($doDebug) {
							echo "email: $inp_email != $advisor_email<br />";
						}
						$doUpdate						= TRUE;
						$updateParams['email']			= $inp_email;
						$updateFormat[]					= '%s';
						$actionLogData					.= " Updated email from $advisor_email to $inp_email. ";
						$updateContent					.= "Updated email from $advisor_email to $inp_email.<br />";
						$advisor_email					= $inp_email;
					}
					if (strcmp($inp_phone,$advisor_phone) != 0) {
						if ($doDebug) {
							echo "phone: $inp_phone != $advisor_phone<br />";
						}
						$doUpdate						= TRUE;
						$inp_phone						= str_replace("-","",$inp_phone);
						$inp_phone						= str_replace("(","",$inp_phone);
						$inp_phone						= str_replace(")","",$inp_phone);
						$updateParams['phone']			= $inp_phone;
						$updateFormat[]					= '%s';
						$actionLogData					.= " Updated phone from $advisor_phone to $inp_phone. ";
						$updateContent					.= "Updated phone from $advisor_phone to $inp_phone.<br />";
						$advisor_phone					= $inp_phone;
					}
					if ($inp_text_message != $advisor_text_message) {
						if ($doDebug) {
							echo "text_message: $inp_text_message != $advisor_text_message<br />";
						}
						$doUpdate						= TRUE;
						$updateParams['text_message']	= $inp_text_message;
						$updateFormat[]					= '%s';
						$actionLogData					.= " Updated text_message from $advisor_text_message to $inp_text_message. ";
						$updateContent					.= "Updated text_message from $advisor_text_message to $inp_text_message.<br />";
						$advisor_text_message			= $inp_text_message;
					}
					if ($inp_city != $advisor_city) {
						if ($doDebug) {
							echo "city: $inp_city != $advisor_city<br />";
						}
						$doUpdate						= TRUE;
						$updateParams['city']			= $inp_city;
						$updateFormat[]					= '%s';
						$actionLogData					.= " Updated city from $advisor_city to $inp_city. ";
						$updateContent					.= "Updated city from $advisor_city to $inp_city.<br />";
						$advisor_city					= $inp_city;
					}
					if ($inp_state != $advisor_state) {
						if ($doDebug) {
							echo "state: $inp_state != $advisor_state<br />";
						}
						$doUpdate						= TRUE;
						$updateParams['state']			= $inp_state;
						$updateFormat[]					= '%s';
						$actionLogData					.= " Updated state from $advisor_state to $inp_state. ";
						$updateContent					.= "Updated state from $advisor_state to $inp_state.<br />";
						$advisor_state					= $inp_state;
					}
					if ($inp_zip != $advisor_zip_code) {
						if ($doDebug) {
							echo "zip_code: $inp_zip != $advisor_zip_code<br />";
						}
						$doUpdate						= TRUE;
						$updateParams['zip_code']		= $inp_zip;
						$updateFormat[]					= '%s';
						$actionLogData					.= " Updated zip_code from $advisor_zip_code to $inp_zip. ";
						$updateContent					.= "Updated zip_code from $advisor_zip_code to $inp_zip.<br />";
						$advisor_zip_code				= $inp_zip;
						$zipUpdated						= TRUE;
					}
					if ($inp_countryb == '') {
						if ($doDebug) {
							echo "country data is in inp_country<br />";
						}
						$myArray							= explode("|",$inp_country);
						$thisCountryCode					= $myArray[0];
						$thisCountry						= $myArray[1];
					} else {
						if ($doDebug) {
							echo "country data is in inp_countryb<br />";
						}
						$myArray							= explode("|",$inp_countryb);
						$thisCountryCode					= $myArray[0];
						$thisCountry						= $myArray[1];
					}
					
					if ($thisCountry != $advisor_country) {
						if ($doDebug) {
							echo "country: $thisCountry != $advisor_country<br />";
						}
						$doUpdate						= TRUE;
						$updateParams['country']		= $thisCountry;
						$updateFormat[]					= '%s';
						$actionLogData					.= " Updated country from $advisor_country to $thisCountry. ";
						$updateContent					.= "Updated country from $advisor_country to $thisCountry.<br />";
						$advisor_country				= $inp_country;
						$updateParams['country_code']	= $thisCountryCode;
						$updateFormat[]					= '%s';
						$actionLogData					.= " Updated country code from $advisor_country_code to $thisCountryCode. ";
						$updateContent					.= "Updated country from $advisor_country_code to $thisCountryCode.<br />";
						$countryUpdated					= TRUE;
						$advisor_country_code			= $thisCountryCode;
					}
					if ($inp_languages != $advisor_languages) {
						if ($doDebug) {
							echo "languages: $inp_languages != $advisor_languages<br />";
						}
						$doUpdate						= TRUE;
						$updateParams['languages']		= $inp_languages;
						$updateFormat[]					= '%s';
						$actionLogData					.= " Updated languages spoken from $advisor_languages to $inp_languages. ";
						$updateContent					.= "Updated languages spoken from $advisor_languages to $inp_languages.<br />";
						$advisor_languages		 		= $inp_languages;
					}
					if ($inp_whatsapp != $advisor_whatsapp) {
						if ($doDebug) {
							echo "whatsapp: $inp_whatsapp != $advisor_whatsapp<br />";
						}
						$doUpdate						= TRUE;
						$updateParams['whatsapp_app']	= $inp_whatsapp;
						$updateFormat[]					= '%s';
						$actionLogData					.= " Updated whatsapp from $advisor_whatsapp to $inp_whatsapp. ";
						$updateContent					.= "Updated whatsapp from $advisor_whatsapp to $inp_whatsapp.<br />";
						$advisor_whatsapp		 		= $inp_whatsapp;
					}
					if ($inp_signal != $advisor_signal) {
						if ($doDebug) {
							echo "signal: $inp_signal != $advisor_signal<br />";
						}
						$doUpdate						= TRUE;
						$updateParams['signal_app']	= $inp_signal;
						$updateFormat[]					= '%s';
						$actionLogData					.= " Updated signal from $advisor_signal to $inp_signal. ";
						$updateContent					.= "Updated signal from $advisor_signal to $inp_signal.<br />";
						$advisor_signal		 		= $inp_signal;
					}
					if ($inp_telegram != $advisor_telegram) {
						if ($doDebug) {
							echo "telegram: $inp_telegram != $advisor_telegram<br />";
						}
						$doUpdate						= TRUE;
						$updateParams['telegram_app']	= $inp_telegram;
						$updateFormat[]					= '%s';
						$actionLogData					.= " Updated telegram from $advisor_telegram to $inp_telegram. ";
						$updateContent					.= "Updated telegram from $advisor_telegram to $inp_telegram.<br />";
						$advisor_telegram		 		= $inp_telegram;
					}
					if ($inp_messenger != $advisor_messenger) {
						if ($doDebug) {
							echo "messenger: $inp_messenger != $advisor_messenger<br />";
						}
						$doUpdate						= TRUE;
						$updateParams['messenger_app']	= $inp_messenger;
						$updateFormat[]					= '%s';
						$actionLogData					.= " Updated messenger from $advisor_messenger to $inp_messenger. ";
						$updateContent					.= "Updated messenger from $advisor_messenger to $inp_messenger.<br />";
						$advisor_messenger		 		= $inp_messenger;
					}
					
					// if country changed then ph_code has to be updated
					if ($countryUpdated) {
						if ($doDebug) {
							echo "country code changed. Looking up new ph_code<br />";
						}
						//////////// get the phone code from the country codes table
						$phoneResult					= getCountryData($advisor_country_code,'countrycode',$doDebug);
						if ($phoneResult !== FALSE) {
							$advisor_ph_code			= $phoneResult[2];
							$updateParams['ph_code']	= $advisor_ph_code;
							$updateFormat[]				= '%s';
							$actionLogData				.= " Updated phone code to $advisor_ph_code. ";
							$updateContent				.= "Updated phone code to $advisor_ph_code.<br />";
							$doUpdate					= TRUE;
						} else {
							if ($doDebug) {
								echo "getting ph_code using country code $inp_country_code failed<br />";
							}
							$advisor_ph_code			= '';
							$updateParams['ph_code']	= '';
							$updateFormat[]				= '%s';
							$actionLogData				.= " Unknown phone code. ";
							$updateContent				.= "Unknown phone code.<br />";
							$doUpdate					= TRUE;
						}
					}
					
					if ($advisor_country_code == 'US' && $zipUpdated) {
						if ($doDebug) {
							echo "country code is US and zip has been updated. Getting timezone info for new zipcode<br />";
						}
						// get the timezone info for the new zipcode
						$zipCodeData					= getOffsetFromZipcode($advisor_zip_code,$advisor_semester,TRUE,$testMode,$doDebug);
						if ($zipCodeData[0] == 'OK') {
							$this_timezone_id			= $zipCodeData[1];
							$this_timezone_offset		= $zipCodeData[2];
							if ($this_timezone_offset != $advisor_timezone_offset) {
								$advisor_timezone_id		= $zipCodeData[1];
								$advisor_timezone_offset	= $zipCodeData[2];
								$updateParams['timezone_id']	= $advisor_timezone_id;
								$updateFormat[]				= '%s';
								$actionLogData				.= " Set timezone_id to $advisor_timezone_id. ";
								$updateContent				.= "Set timezone_id to $advisor_timezone_id<br />";
								$updateParams['timezone_offset']	= $advisor_timezone_offset;
								$updateFormat[]				= '%f';
								$actionLogData				.= " Set timezone_offset to $advisor_timezone_offset. ";
								$updateContent				.= "Set timezone_offset to $advisor_timezone_offset<br />";
								$doUpdate					= TRUE;
								$redoTimezone				= TRUE;
							}
						} else {
							if ($doDebug) {
								echo "Did not get timezone_id nor timezone_offset based on US zip code of $advisor_zip_code<br />";
							}
							$advisor_timezone_id		= 'Unknown';
							$advisor_timezone_offset	= -99;
							$updateParams['timezone_id']	= $advisor_timezone_id;
							$updateFormat[]				= '%s';
							$actionLogData				.= " Set timezone_id to $advisor_timezone_id. ";
							$updateContent				.= "Set timezone_id to $advisor_timezone_id<br />";
							$updateParams['timezone_offset']	= $advisor_timezone_offset;
							$updateFormat[]				= '%f';
							$actionLogData				.= " Set timezone_offset to $advisor_timezone_offset. ";
							$updateContent				.= "Set timezone_offset to $advisor_timezone_offset<br />";
							$doUpdate					= TRUE;
						}										
					}
					
					// set up to handle Canadian postal code changes
					if ($advisor_country_code == 'CA' and $zipUpdated) {
						if ($doDebug) {
							echo "country code is CA and postal code updated. Setting up to select the identifier<br />";
						}
						$advisor_timezone_id		= 'Unknown';
						$advisor_timezone_offset	= -99;
						$updateParams['timezone_id']	= $advisor_timezone_id;
						$updateFormat[]				= '%s';
						$updateParams['timezone_offset']	= $advisor_timezone_offset;
						$updateFormat[]				= '%f';
						$doUpdate					= TRUE;
					}
					
					// set up to handle foreign country changes
					if ($countryUpdated && $advisor_country_code != 'US') {
						if ($doDebug) {
							echo "country code updated and is not US. Setting up to select the identifier<br />";
						}
						$advisor_timezone_id		= 'Unknown';
						$advisor_timezone_offset	= -99;
						$updateParams['timezone_id']	= $advisor_timezone_id;
						$updateFormat[]				= '%s';
						$updateParams['timezone_offset']	= $advisor_timezone_offset;
						$updateFormat[]				= '%f';
						$doUpdate					= TRUE;
					}
					
					if ($doUpdate) {
						if ($doDebug) {
							echo "Preparing to update $advisor_call_sign record<br />";
						}
						if ($actionLogData != '') {
							$advisor_action_log				= "$advisor_action_log / $actionDate $actionLogData";
							$updateParams['action_log']		= $advisor_action_log;
							$updateFormat[]					= '%s';
						}

						if ($doDebug) {
							echo "updateParams:<br /><pre>";
							print_r($updateParams);
							echo "</pre><br />updateFormat:<br /><pre>";
							print_r($updateFormat);
							echo "</pre><br />";
						}

						$advisorUpdateData		= array('tableName'=>$advisorTableName,
														'inp_method'=>'update',
														'inp_data'=>$updateParams,
														'inp_format'=>$updateFormat,
														'jobname'=>$jobname,
														'inp_id'=>$advisor_ID,
														'inp_callsign'=>$advisor_call_sign,
														'inp_semester'=>$advisor_semester,
														'inp_who'=>$userName,
														'testMode'=>$testMode,
														'doDebug'=>$doDebug);
						$updateResult	= updateAdvisor($advisorUpdateData);
						if ($updateResult[0] === FALSE) {
							$myError	= $wpdb->last_error;
							$mySql		= $wpdb->last_query;
							$errorMsg	= "$jobname Processing $advisor_call_sign in $advisorTableName failed. Reason: $updateResult[1]<br />SQL: $mySql<br />Error: $myError<br />";
							if ($doDebug) {
								echo $errorMsg;
							}
							sendErrorEmail($errorMsg);
							$content		.= "Unable to update content in $advisorTableName<br />";
						} else {
							if ($doDebug) {
								echo "Successfully updated $advisor_call_sign record at $advisor_ID<br />";

							}
				
							if ($doDebug) {
								echo "record updated:";
								foreach($updateParams as $key=>$value) {
									echo "&nbsp;&nbsp;&nbsp;&nbsp;$key = $value<br />";
								}
							}
						}
					} else {
						if ($doDebug) {
							echo "No updates to the advisor record<br />";
						}
						$content			.= "<p>No updates to the advisor record were requested.</p>";
					}
					$doProceed				= TRUE;
					
					// handle country changes for other than US
					if (($countryUpdated && $advisor_country_code != 'US') || ($advisor_country_code == 'CA' && $zipUpdated)) {

						if ($doDebug) {
							echo "country changed and not US or Canadian postal code changed. Setting up to select timezone id for $advisor_country_code<br />";
						}
						$timezone_identifiers 		= DateTimeZone::listIdentifiers( DateTimeZone::PER_COUNTRY, $advisor_country_code );
						$myInt						= count($timezone_identifiers);
						if ($myInt == 1) {									//  only 1 found. Use that and continue
							$inp_timezone_id		= $timezone_identifiers[0];
							$advisor_timezone_offset	= getOffsetFromIdentifier($inp_timezone_id,$advisor_semester);
							if ($inp_timezone_offset === FALSE) {
								if ($doDebug) {
									echo "getOffsetFromIdentifier returned FALSE using $inp_timezone_id, $inp_semester<br />";
								}
								$inp_timezone_offset	= -99;
							}
						} elseif ($myInt > 1) {
							$timezoneSelector			= "<table>";
							$ii							= 1;
							if ($doDebug) {
								echo "have the list of identifiers for $advisor_country_code<br />";
							}
							foreach ($timezone_identifiers as $thisID) {
								if ($doDebug) {
									echo "Processing $thisID<br />";
								}
								$selector			= "";
								if ($browser_timezone_id == $thisID) {
									$selector		= "checked";
								}
								$dateTimeZoneLocal 	= new DateTimeZone($thisID);
								$dateTimeLocal 		= new DateTime("now",$dateTimeZoneLocal);
								$localDateTime 		= $dateTimeLocal->format('h:i A');
								$myInt				= strpos($thisID,"/");
								$myCity				= substr($thisID,$myInt+1);
								switch($ii) {
									case 1:
										$timezoneSelector	.= "<tr><td><input type='radio' class='formInputButton' name='inp_timezone_id' value='$thisID' $selector>$myCity<br />$localDateTime</td>";
										$ii++;
										break;
									case 2:
										$timezoneSelector	.= "<td><input type='radio' class='formInputButton' name='inp_timezone_id' value='$thisID' $selector>$myCity<br />$localDateTime</td>";
										$ii++;
										break;
									case 3:
										$timezoneSelector	.= "<td><input type='radio' class='formInputButton' name='inp_timezone_id' value='$thisID' $selector>$myCity<br />$localDateTime</td></tr>";
										$ii					= 1;
										break;
								}
							}
							if ($ii == 2) {			// need two blank cells
								$timezoneSelector			.= "<td>&nbsp;</td><td>&nbsp;</td></tr>";
							} elseif ($ii == 3) {	// need one blank cell
								$timezoneSelector			.= "<td>&nbsp;</td></tr>";
							}
							if ($doDebug) {
								echo "displaying form to select the indentifier<br />";
							}
							$doProceed		= FALSE;
							$content		.= "<h3>Select Time Zone City</h3>
												<p>Please select the city that best represents the timezone you will be in during the $inp_semester semester. 
												The current local time is displayed underneath the city.</p>
												<form method='post' action='$theURL' 
												name='tzselection' ENCTYPE='multipart/form-data'>
													<input type='hidden' name='strpass' value='25'>
													<input type='hidden' name='inp_mode' value='$inp_mode'>
													<input type='hidden' name='inp_verbose' value='$inp_verbose'>
													<input type='hidden' name='inp_semester' value='$advisor_semester'>
													<input type='hidden' name='inp_callsign' value='$advisor_call_sign'>
													<input type='hidden' name='inp_advisor_id' value='$advisor_ID'>
													<input type='hidden' name='allowSignup' value='$allowSignup'>
												$timezoneSelector
												<tr><td colspan='3'><input class='formInputButton' type='submit' value='Submit' /></td></tr>
												</table>
												</form>";

						} else {
							if ($doDebug) {
								echo "found no timezone_identifiers for country code of $inp_country_code<br />";
							}
							$inp_timezone_id		= 'Unknown';
							$inp_timezone_offset	= -99.0;
							$advisor_timezone_id	= $inp_timezone_id;
							$advisor_timezone_offset = $inp_timezone_offset;
						}
						if ($doProceed) {
							/// write this info to the database
							if ($doDebug) {
								echo "have an identifier, or no identifier. Updating database<br />";
							}
							$redoTimezone		= TRUE;
							$advisorUpdateData		= array('tableName'=>$advisorTableName,
															'inp_method'=>'update',
															'inp_data'=>array('timezone_id'=>$inp_timezone_id,
																	  'timezone_offset'=>$inp_timezone_offset),
															'inp_format'=>array('%s','%f'),
															'jobname'=>$jobname,
															'inp_id'=>$advisor_ID,
															'inp_callsign'=>$advisor_call_sign,
															'inp_semester'=>$advisor_semester,
															'inp_who'=>$userName,
															'testMode'=>$testMode,
															'doDebug'=>$doDebug);
							$updateResult	= updateAdvisor($advisorUpdateData);
							if ($updateResult[0] === FALSE) {
								$myError	= $wpdb->last_error;
								$mySql		= $wpdb->last_query;
								$errorMsg	= "$jobname Processing $advisor_call_sign in $advisorTableName failed. Reason: $updateResult[1]<br />SQL: $mySql<br />Error: $myError<br />";
								if ($doDebug) {
									echo $errorMsg;
								}
								sendErrorEmail($errorMsg);
								$content		.= "Unable to update content in $advisorTableName<br />";
							}
						}
					}
			
					if ($doProceed) {
						if ($doDebug) {
							echo "<br /><b>checking to see if the class records have to be updated</b><br />";
						}
						/// see if the class records have to be updated
						if ($redoTimezone || $firstNameUpdated || $lastNameUpdated) {
							if ($doDebug) {
								echo "redoing class info based on changes<br />";
							}
							
							$wpw1_cwa_advisorclass	= $wpdb->get_results("select * from $advisorClassTableName 
																			where advisor_call_sign = '$advisor_call_sign' 
																			  and semester='$inp_semester' 
																			order by sequence");
							if ($wpw1_cwa_advisorclass === FALSE) {
								$myError			= $wpdb->last_error;
								$myQuery			= $wpdb->last_query;
								if ($doDebug) {
									echo "Reading $advisorClassTableName table failed<br />
										  wpdb->last_query: $myQuery<br />
										  wpdb->last_error: $myError<br />";
								}
								$errorMsg			= "$jobname Reading $advisorClassTableName table failed. <p>SQL: $myQuery</p><p> Error: $myError</p>";
								sendErrorEmail($errorMsg);
							} else {
								$numACRows						= $wpdb->num_rows;
								if ($doDebug) {
									$myStr						= $wpdb->last_query;
									echo "ran $myStr<br />and found $numACRows rows<br />";
								}
								if ($numACRows > 0) {
									foreach ($wpw1_cwa_advisorclass as $advisorClassRow) {
										$advisorClass_ID				 		= $advisorClassRow->advisorclass_id;
										$advisorClass_advisor_call_sign 		= $advisorClassRow->advisor_call_sign;
										$advisorClass_advisor_first_name 		= $advisorClassRow->advisor_first_name;
										$advisorClass_advisor_last_name 		= stripslashes($advisorClassRow->advisor_last_name);
										$advisorClass_advisor_id 				= $advisorClassRow->advisor_id;
										$advisorClass_sequence 					= $advisorClassRow->sequence;
										$advisorClass_semester 					= $advisorClassRow->semester;
										$advisorClass_timezone 					= $advisorClassRow->time_zone;
										$advisorClass_timezone_id				= $advisorClassRow->timezone_id;		// new
										$advisorClass_timezone_offset			= $advisorClassRow->timezone_offset;	// new
										$advisorClass_level 					= $advisorClassRow->level;
										$advisorClass_class_size 				= $advisorClassRow->class_size;
										$advisorClass_class_schedule_days 		= $advisorClassRow->class_schedule_days;
										$advisorClass_class_schedule_times 		= $advisorClassRow->class_schedule_times;
										$advisorClass_class_schedule_days_utc 	= $advisorClassRow->class_schedule_days_utc;
										$advisorClass_class_schedule_times_utc 	= $advisorClassRow->class_schedule_times_utc;
										$advisorClass_action_log 				= $advisorClassRow->action_log;
										$advisorClass_class_incomplete 			= $advisorClassRow->class_incomplete;
										$advisorClass_date_created				= $advisorClassRow->date_created;
										$advisorClass_date_updated				= $advisorClassRow->date_updated;

										$advisorClass_advisor_last_name  		= no_magic_quotes($advisorClass_advisor_last_name);
										$updateParams			= array();
										$updateFormat			= array();
										
										if ($redoTimezone) {
											if ($doDebug) {
												echo "redoing timezone for $advisorClass_advisor_call_sign sequence $advisorClass_sequence<br />";
											}
											$advisorClass_timezone_id				= $advisor_timezone_id;
											$advisorClass_timezone_offset			= $advisor_timezone_offset;
										
											if ($advisorClass_timezone_id != 'Unknown') {
												$utcResult								= utcConvert('toutc',$advisorClass_timezone_offset,$advisorClass_class_schedule_times,$advisorClass_class_schedule_days,$doDebug);
												if ($utcResult[0] == 'OK') {
													$advisorClass_class_schedule_times_utc	= $utcResult[1];
													$advisorClass_class_schedule_days_utc	= $utcResult[2];
												} else {
													$advisorClass_class_schedule_times_utc	= 'None';
													$advisorClass_class_schedule_days_utc	= 'None';
													if ($doDebug) {
														echo "Getting UTC times failed: $utcResult[3]<br />";
													}
												}
											} else {
												$advisorClass_class_schedule_times_utc	= 'None';
												$advisorClass_class_schedule_days_utc	= 'None';
											}
											
											$updateParams['timezone_id']				= $advisor_timezone_id;
											$updateFormat[]								= '%s';
											$updateParams['timezone_offset']			= $advisor_timezone_offset;
											$updateFormat[]								= '%f';
											$updateParams['class_schedule_days_utc']	= $advisorClass_class_schedule_days_utc;
											$updateFormat[]								= '%s';
											$updateParams['class_schedule_times_utc']	= $advisorClass_class_schedule_times_utc;
											$updateFormat[]								= '%s';
										}
										if ($firstNameUpdated) {
											$updateParams['advisor_first_name']			= $advisor_first_name;
											$updateFormat[]								= '%s';
										}
										if ($lastNameUpdated) {
											$UpdateParams['advisor_last_name']			= addslashes($advisor_last_name);
											$updateFormat[]								= '%s';
										}
										
										if ($doDebug) {
											echo "updating advisor $advisor_call_sign class $advisorClass_sequence<br /><pre>";
											print_r($updateParams);
											print_r($updateFormat);
											echo "</pre><br />";
										}
										$classUpdateData		= array('tableName'=>$advisorClassTableName,
																		'inp_method'=>'update',
																		'inp_data'=>$updateParams,
																		'inp_format'=>$updateFormat,
																		'jobname'=>$jobname,
																		'inp_id'=>$advisorClass_ID,
																		'inp_callsign'=>$advisorClass_advisor_call_sign,
																		'inp_semester'=>$advisorClass_semester,
																		'inp_who'=>$userName,
																		'testMode'=>$testMode,
																		'doDebug'=>$doDebug);
										$updateResult	= updateClass($classUpdateData);
										if ($updateResult[0] === FALSE) {
											$myError	= $wpdb->last_error;
											$mySql		= $wpdb->last_query;
											$errorMsg	= "A$jobname Processing $advisorClass_advisor_call_sign in $advisorClassTableName failed. Reason: $updateResult[1]<br />SQL: $mySql<br />Error: $myError<br />";
											if ($doDebug) {
												echo $errorMsg;
											}
											sendErrorEmail($errorMsg);
											$content		.= "Unable to update content in $advisorClassTableName<br />";
										}
									}
								}
							}
						}
						if ($doDebug) {
							echo "<br /><b>All changes done. Displaying the advisor and class records</b><br />";
						}
						// Now display the advisor and class records
						$displayResult		= getAdvisorInfoToDisplay($inp_callsign,$inp_semester,$noUpdate);
						$content			.= $displayResult[0];
					}
				}
			} else {
				if ($doDebug) {
					echo "No record found to update for $inp_callsign at id $inp_id<br />";
				}
				$content		.= "<p>ERROR: Couldn't get the record for $inp_callsign at id $inp_id to update</p>";
			}
		}

/////////////////		pass 15				update a class record

	} elseif ("15" == $strPass) {
	
		if ($doDebug) {
			echo "At pass 15 ... update a class record<br />";
		}
		
		
		//// get the record
		$sql					= "select * from $advisorClassTableName 
									where advisor_call_sign='$inp_callsign' 
									  and sequence=$classID
									  and semester='$inp_semester'";
		$wpw1_cwa_advisorclass	= $wpdb->get_results($sql);
		if ($wpw1_cwa_advisorclass === FALSE) {
			$myError			= $wpdb->last_error;
			$myQuery			= $wpdb->last_query;
			if ($doDebug) {
				echo "Reading $advisorClassTableName table failed<br />
					  wpdb->last_query: $myQuery<br />
					  wpdb->last_error: $myError<br />";
			}
			$errorMsg			= "$jobname Reading $advisorClassTableName table failed. <p>SQL: $myQuery</p><p> Error: $myError</p>";
			sendErrorEmail($errorMsg);
		} else {
			$numACRows						= $wpdb->num_rows;
			if ($doDebug) {
				$myStr						= $wpdb->last_query;
				echo "ran $myStr<br />and found $numACRows rows<br />";
			}
			if ($numACRows > 0) {
				foreach ($wpw1_cwa_advisorclass as $advisorClassRow) {
					$advisorClass_ID				 		= $advisorClassRow->advisorclass_id;
					$advisorClass_advisor_call_sign 		= $advisorClassRow->advisor_call_sign;
					$advisorClass_advisor_first_name 		= $advisorClassRow->advisor_first_name;
					$advisorClass_advisor_last_name 		= stripslashes($advisorClassRow->advisor_last_name);
					$advisorClass_advisor_id 				= $advisorClassRow->advisor_id;
					$advisorClass_sequence 					= $advisorClassRow->sequence;
					$advisorClass_semester 					= $advisorClassRow->semester;
					$advisorClass_timezone 					= $advisorClassRow->time_zone;
					$advisorClass_timezone_id				= $advisorClassRow->timezone_id;		// new
					$advisorClass_timezone_offset			= $advisorClassRow->timezone_offset;	// new
					$advisorClass_level 					= $advisorClassRow->level;
					$advisorClass_class_size 				= $advisorClassRow->class_size;
					$advisorClass_class_schedule_days 		= $advisorClassRow->class_schedule_days;
					$advisorClass_class_schedule_times 		= $advisorClassRow->class_schedule_times;
					$advisorClass_class_schedule_days_utc 	= $advisorClassRow->class_schedule_days_utc;
					$advisorClass_class_schedule_times_utc 	= $advisorClassRow->class_schedule_times_utc;
					$advisorClass_action_log 				= $advisorClassRow->action_log;
					$advisorClass_class_incomplete 			= $advisorClassRow->class_incomplete;
					$advisorClass_date_created				= $advisorClassRow->date_created;
					$advisorClass_date_updated				= $advisorClassRow->date_updated;

					$advisorClass_advisor_last_name  		= no_magic_quotes($advisorClass_advisor_last_name);

	
					$sunWed				= '';
					$sunThu				= '';
					$monWed				= '';
					$monThu				= '';
					$tueThu				= '';
					$tueFri				= '';
					$wedFri				= '';
					$wedSat				= '';
					
					if ($advisorClass_class_schedule_days == 'Sunday,Wednesday') {
						$sunWed			= "checked='checked'";
					} elseif ($advisorClass_class_schedule_days == 'Sunday,Thursday') {
						$sunThu			= "checked='checked'";
					} elseif ($advisorClass_class_schedule_days == 'Monday,Thursday') {
						$monThu			= "checked='checked'";
					} elseif ($advisorClass_class_schedule_days == 'Tuesday,Friday') {
						$tueFri			= "checked='checked'";
					} else {
						if ($doDebug) {
							echo "<b>Error</b> advisorClass_class_schedule_days of $advisorClass_class_schedule_days do not compute<br />";
						}
					}
					$time0600			= '';
					$time0630			= '';
					$time0700			= '';
					$time0730			= '';
					$time0800			= '';
					$time0830			= '';
					$time0900			= '';
					$time0930			= '';
					$time1000			= '';
					$time1030			= '';
					$time1100			= '';
					$time1130			= '';
					$time1200			= '';
					$time1230			= '';
					$time1300			= '';
					$time1330			= '';
					$time1400			= '';
					$time1430			= '';
					$time1500			= '';
					$time1530			= '';
					$time1600			= '';
					$time1630			= '';
					$time1700			= '';
					$time1730			= '';
					$time1800			= '';
					$time1830			= '';
					$time1900			= '';
					$time1930			= '';
					$time2000			= '';
					$time2030			= '';
					$time2100			= '';
					$time2130			= '';
					$time2200			= '';
					$time2230			= '';
					
					if ($advisorClass_class_schedule_times == '0600') {
						$time0600		= "checked='checked'";
					}
					if ($advisorClass_class_schedule_times == '0630') {
						$time0630		= "checked='checked'";
					}
					if ($advisorClass_class_schedule_times == '0700') {
						$time0700		= "checked='checked'";
					}
					if ($advisorClass_class_schedule_times == '0730') {
						$time0730		= "checked='checked'";
					}
					if ($advisorClass_class_schedule_times == '0800') {
						$time0800		= "checked='checked'";
					}
					if ($advisorClass_class_schedule_times == '0830') {
						$time0830		= "checked='checked'";
					}
					if ($advisorClass_class_schedule_times == '0900') {
						$time0900		= "checked='checked'";
					}
					if ($advisorClass_class_schedule_times == '0930') {
						$time0930		= "checked='checked'";
					}
					if ($advisorClass_class_schedule_times == '1000') {
						$time1000		= "checked='checked'";
					}
					if ($advisorClass_class_schedule_times == '1030') {
						$time1030		= "checked='checked'";
					}
					if ($advisorClass_class_schedule_times == '1100') {
						$time1100		= "checked='checked'";
					}
					if ($advisorClass_class_schedule_times == '1130') {
						$time1130		= "checked='checked'";
					}
					if ($advisorClass_class_schedule_times == '1200') {
						$time1200		= "checked='checked'";
					}
					if ($advisorClass_class_schedule_times == '1230') {
						$time1230		= "checked='checked'";
					}
					if ($advisorClass_class_schedule_times == '1300') {
						$time1300		= "checked='checked'";
					}
					if ($advisorClass_class_schedule_times == '1330') {
						$time1330		= "checked='checked'";
					}
					if ($advisorClass_class_schedule_times == '1400') {
						$time1400		= "checked='checked'";
					}
					if ($advisorClass_class_schedule_times == '1430') {
						$time1430		= "checked='checked'";
					}
					if ($advisorClass_class_schedule_times == '1500') {
						$time1500		= "checked='checked'";
					}
					if ($advisorClass_class_schedule_times == '1530') {
						$time1530		= "checked='checked'";
					}
					if ($advisorClass_class_schedule_times == '1600') {
						$time1600		= "checked='checked'";
					}
					if ($advisorClass_class_schedule_times == '1630') {
						$time1630		= "checked='checked'";
					}
					if ($advisorClass_class_schedule_times == '1700') {
						$time1700		= "checked='checked'";
					}
					if ($advisorClass_class_schedule_times == '1730') {
						$time1730		= "checked='checked'";
					}
					if ($advisorClass_class_schedule_times == '1800') {
						$time1800		= "checked='checked'";
					}
					if ($advisorClass_class_schedule_times == '1830') {
						$time1830		= "checked='checked'";
					}
					if ($advisorClass_class_schedule_times == '1900') {
						$time1900		= "checked='checked'";
					}
					if ($advisorClass_class_schedule_times == '1930') {
						$time1930		= "checked='checked'";
					}
					if ($advisorClass_class_schedule_times == '2000') {
						$time2000		= "checked='checked'";
					}
					if ($advisorClass_class_schedule_times == '2030') {
						$time2030		= "checked='checked'";
					}
					if ($advisorClass_class_schedule_times == '2100') {
						$time2100		= "checked='checked'";
					}
					if ($advisorClass_class_schedule_times == '2130') {
						$time2130		= "checked='checked'";
					}
					if ($advisorClass_class_schedule_times == '2200') {
						$time2200		= "checked='checked'";
					}
					if ($advisorClass_class_schedule_times == '2230') {
						$time2230		= "checked='checked'";
					}
					$begChecked			= '';
					$funChecked			= '';
					$intChecked			= '';
					$advChecked			= '';
					if ($advisorClass_level == 'Beginner') {
						$begChecked		= "checked='checked' ";
					}
					if ($advisorClass_level == 'Fundamental') {
						$funChecked		= "checked='checked' ";
					}
					if ($advisorClass_level == 'Intermediate') {
						$intChecked		= "checked='checked' ";
					}
					if ($advisorClass_level == 'Advanced') {
						$advChecked		= "checked='checked' ";
					}
				
	
	
					$content			.= "<b>Class $advisorClass_sequence:</b>
											<p>Please update the class record and then submit the changes to be updated in the database:</p>
											<form method='post' action='$theURL' 
											name='advisorclass_update_form' ENCTYPE='multipart/form-data'>
											<input type='hidden' name='strpass' value='16'>
											<input type='hidden' name='inp_mode' value='$inp_mode'>
											<input type='hidden' name='inp_callsign' value='$advisorClass_advisor_call_sign'>
											<input type='hidden' name='inp_id' value='$advisorClass_ID'>
											<input type='hidden' name='inp_sequence' value='$advisorClass_sequence'>
											<input type='hidden' name='allowSignup' value='$allowSignup'>

											<table style='width:1000px;'>
											<tr><td style='vertical-align:top;width:330px;'><b>Sequence</b><br />
													$advisorClass_sequence</td>
												<td style='vertical-align:top;width:330px;'><b>Level</b><br />
													<input type='radio' class='formInputButton' name='inp_level' value='Beginner' $begChecked /> Beginner<br />
													<input type='radio' class='formInputButton' name='inp_level' value='Fundamental' $funChecked /> Fundamental<br />
													<input type='radio' class='formInputButton' name='inp_level' value='Intermediate' $intChecked /> Intermediate<br />
													<input type='radio' class='formInputButton' name='inp_level' value='Advanced' $advChecked /> Advanced</td>	
												<td style='vertical-align:top;'width:330px;><b>Class Size</b><br />
													<input type='text'class='formInputText' id='chk_class_size' name='inp_class_size' size='5' maxlength='5' value='$advisorClass_class_size'><br />
														(default class size is 6)</td></tr>
											<tr><td style='vertical-align:top;'><b>Class Teaching Days</b><br />Note that most advisors hold classes on Monday and Thursday<br />
													<input type='radio' class='formInputButton' name='inp_teaching_days' value='Sunday,Wednesday' $sunWed> Sunday and Wednesday<br />
													<input type='radio' class='formInputButton' name='inp_teaching_days' value='Sunday,Thursday' $sunThu> Sunday and Thursday<br />
													<input type='radio' class='formInputButton' name='inp_teaching_days' value='Monday,Thursday' $monThu> Monday and Thursday<br />
													<input type='radio' class='formInputButton' name='inp_teaching_days' value='Tuesday,Friday' $tueFri> Tuesday and Friday<br /></td>
												<td style='vertical-align:top;' colspan='2'><b>Local Start Time</b><br />Specify start time in local time in the 
											time zone where you live. Select the time where this class will start. The program will account for standard or daylight 
											savings time or summer time as needed.<br />
											<table><tr>
												<td style='width:110px;vertical-align:top;'>
													<input type='radio' class='formInputButton' name='inp_times' value='0600' $time0600> 6:00am<br />
													<input type='radio' class='formInputButton' name='inp_times' value='0630' $time0630> 6:30am<br />
													<input type='radio' class='formInputButton' name='inp_times' value='0700' $time0700> 7:00am<br />
													<input type='radio' class='formInputButton' name='inp_times' value='0730' $time0730> 7:30am<br />
													<input type='radio' class='formInputButton' name='inp_times' value='0800' $time0800> 8:00am<br />
													<input type='radio' class='formInputButton' name='inp_times' value='0830' $time0830> 8:30am<br />
													<input type='radio' class='formInputButton' name='inp_times' value='0900' $time0900> 9:00am<br />
													<input type='radio' class='formInputButton' name='inp_times' value='0930' $time0930> 9:30am<br />
													<input type='radio' class='formInputButton' name='inp_times' value='1000' $time1000> 10:00am</td>
												<td style='width:110px;vertical-align:top;'>
													<input type='radio' class='formInputButton' name='inp_times' value='1030' $time1030> 10:30am<br />
													<input type='radio' class='formInputButton' name='inp_times' value='1100' $time1100> 11:00am<br />
													<input type='radio' class='formInputButton' name='inp_times' value='1130' $time1130> 11:30am<br />
													<input type='radio' class='formInputButton' name='inp_times' value='1200' $time1200> Noon<br />
													<input type='radio' class='formInputButton' name='inp_times' value='1230' $time1230> 12:30pm<br />
													<input type='radio' class='formInputButton' name='inp_times' value='1300' $time1300> 1:00pm<br />
													<input type='radio' class='formInputButton' name='inp_times' value='1330' $time1330> 1:30pm<br />
													<input type='radio' class='formInputButton' name='inp_times' value='1400' $time1400> 2:00pm<br />
													<input type='radio' class='formInputButton' name='inp_times' value='1430' $time1430> 2:30pm</td>
												<td style='width:110px;vertical-align:top;'>
													<input type='radio' class='formInputButton' name='inp_times' value='1500' $time1500> 3:00pm<br />
													<input type='radio' class='formInputButton' name='inp_times' value='1530' $time1530> 3:30pm<br />
													<input type='radio' class='formInputButton' name='inp_times' value='1600' $time1600> 4:00pm<br />
													<input type='radio' class='formInputButton' name='inp_times' value='1630' $time1630> 4:30pm<br />
													<input type='radio' class='formInputButton' name='inp_times' value='1700' $time1700> 5:00pm<br />
													<input type='radio' class='formInputButton' name='inp_times' value='1730' $time1730> 5:30pm<br />
													<input type='radio' class='formInputButton' name='inp_times' value='1800' $time1800> 6:00pm<br />
													<input type='radio' class='formInputButton' name='inp_times' value='1830' $time1830> 6:30pm<br />
													<input type='radio' class='formInputButton' name='inp_times' value='1900' $time1900> 7:00pm</td>
												<td style='width:110px;vertical-align:top;'>
													<input type='radio' class='formInputButton' name='inp_times' value='1930' $time1930> 7:30pm<br />
													<input type='radio' class='formInputButton' name='inp_times' value='2000' $time2000> 8:00pm<br />
													<input type='radio' class='formInputButton' name='inp_times' value='2030' $time2030> 8:30pm<br />
													<input type='radio' class='formInputButton' name='inp_times' value='2100' $time2100> 9:00pm<br />
													<input type='radio' class='formInputButton' name='inp_times' value='2130' $time2130> 9:30pm<br />
													<input type='radio' class='formInputButton' name='inp_times' value='2200' $time2100> 10:00pm<br />
													<input type='radio' class='formInputButton' name='inp_times' value='2230' $time2130> 10:30pm</td>
												<td>&nbsp;</td>
												</tr></table></td></tr>
											<tr><td>&nbsp;</td><td><input class='formInputButton' type='submit' onclick=\"return validate_form(this.form);\" value='Update Info' /></td></tr>
											</table></form>";


				}
			} else {
				if ($doDebug) {
					echo "No advisorclass record found for advisor_call_sign=$inp_callsign and sequence=$classID<br />";
				}
				$content			.= "System Error. The appropriate people have been notified<br />";
			}
		}


///////////////////		pass 16				do the actual update to the class record

	} elseif ("16" == $strPass) {
		if ($doDebug) {
			echo "At pass 16 to do the actual update to the class record<br />";
		}

		$wpw1_cwa_advisorclass	= $wpdb->get_results("select * from $advisorClassTableName 
															where advisorclass_id=$inp_id");
		if ($wpw1_cwa_advisorclass === FALSE) {
			$myError			= $wpdb->last_error;
			$myQuery			= $wpdb->last_query;
			if ($doDebug) {
				echo "Reading $advisorClassTableName table failed<br />
					  wpdb->last_query: $myQuery<br />
					  wpdb->last_error: $myError<br />";
			}
			$errorMsg			= "$jobname Reading $advisorClassTableName table failed. <p>SQL: $myQuery</p><p> Error: $myError</p>";
			sendErrorEmail($errorMsg);
		} else {
			$numACRows						= $wpdb->num_rows;
			if ($doDebug) {
				$myStr						= $wpdb->last_query;
				echo "ran $myStr<br />and found $numACRows rows<br />";
			}
			if ($numACRows > 0) {
				foreach ($wpw1_cwa_advisorclass as $advisorClassRow) {
					$advisorClass_ID				 		= $advisorClassRow->advisorclass_id;
					$advisorClass_advisor_call_sign 		= $advisorClassRow->advisor_call_sign;
					$advisorClass_advisor_first_name 		= $advisorClassRow->advisor_first_name;
					$advisorClass_advisor_last_name 		= stripslashes($advisorClassRow->advisor_last_name);
					$advisorClass_advisor_id 				= $advisorClassRow->advisor_id;
					$advisorClass_sequence 					= $advisorClassRow->sequence;
					$advisorClass_semester 					= $advisorClassRow->semester;
					$advisorClass_timezone 					= $advisorClassRow->time_zone;
					$advisorClass_timezone_id				= $advisorClassRow->timezone_id;		// new
					$advisorClass_timezone_offset			= $advisorClassRow->timezone_offset;	// new
					$advisorClass_level 					= $advisorClassRow->level;
					$advisorClass_class_size 				= $advisorClassRow->class_size;
					$advisorClass_class_schedule_days 		= $advisorClassRow->class_schedule_days;
					$advisorClass_class_schedule_times 		= $advisorClassRow->class_schedule_times;
					$advisorClass_class_schedule_days_utc 	= $advisorClassRow->class_schedule_days_utc;
					$advisorClass_class_schedule_times_utc 	= $advisorClassRow->class_schedule_times_utc;
					$advisorClass_action_log 				= $advisorClassRow->action_log;
					$advisorClass_class_incomplete 			= $advisorClassRow->class_incomplete;
					$advisorClass_date_created				= $advisorClassRow->date_created;
					$advisorClass_date_updated				= $advisorClassRow->date_updated;

					$advisorClass_advisor_last_name  		= no_magic_quotes($advisorClass_advisor_last_name);

					$thisErrors									= '';
					if ($doDebug) {
						echo "&nbsp;&nbsp;&nbsp;&nbsp;Processing sequence $advisorClass_sequence<br />";
						
					}
					$doUpdate					= FALSE;
					$newUTC						= FALSE;
					$updateParams				= array();
					$updateFormats				= array();
					$actionLogData				= '';
					$updateContent				= '';
					if ($userName == '') {
						$userName				= $advisorClass_advisor_call_sign;
					}



					if ($inp_level != $advisorClass_level) {
						if ($doDebug) {
							echo "level: $inp_level != $advisorClass_level<br />";
						}
						$updateParams['level']	= $inp_level;
						$updateFormats[]		= '%s';
						$doUpdate				= TRUE;
						$actionLogData			.= " Updated advisorClass level from $advisorClass_level to $inp_level ";
						$updateContent			.= "Updated advisorClass level from $advisorClass_level to $inp_level<br />";
						$advisorClass_level		= $inp_level;
					}
					if ($inp_class_size != $advisorClass_class_size) {
						if ($inp_class_size == 0 || $inp_class_size == '') {
							$inp_class_size = $defaultClassSize;
						}
						if ($doDebug) {
							echo "class_size: $inp_class_size != $advisorClass_class_size<br />";
						}
						$updateParams['class_size']	= $inp_class_size;
						$updateFormats[]		= '%d';
						$doUpdate				= TRUE;
						$actionLogData			.= " Updated class_size from $advisorClass_class_size to $inp_class_size ";
						$updateContent			.= "Updated class_size from $advisorClass_class_size to $inp_class_size<br />";
						$advisorClass_class_size		= $inp_class_size;
					}
					if ($inp_teaching_days != $advisorClass_class_schedule_days) {
						if ($doDebug) {
							echo "teaching_days: $inp_teaching_days != $advisorClass_class_schedule_days<br />";
						}
						$updateParams['class_schedule_days']	= $inp_teaching_days;
						$updateFormats[]		= '%s';
						$doUpdate				= TRUE;
						$actionLogData			.= " Updated  class_schedule_days from $advisorClass_class_schedule_days to $inp_teaching_days ";
						$updateContent			.= "Updated class_schedule_days from $advisorClass_class_schedule_days to $inp_teaching_days<br />";
						$advisorClass_class_schedule_days		= $inp_teaching_days;
						$myStr					= str_replace(",","/",$advisorClass_class_schedule_days);
						$newUTC					= TRUE;
					}
					if ($inp_times != $advisorClass_class_schedule_times) {
						if ($doDebug) {
							echo "teaching_times: $inp_times != $advisorClass_class_schedule_times<br />";
						}
						$updateParams['class_schedule_times']	= $inp_times;
						$updateFormats[]		= '%s';
						$doUpdate				= TRUE;
						$actionLogData			.= " Updated class_schedule_times from $advisorClass_class_schedule_times to $inp_times ";
						$updateContent			.= "Updated class_schedule_times from $advisorClass_class_schedule_times to $inp_times<br />";
						$advisorClass_class_schedule_times		= $inp_times;
						$newUTC					= TRUE;
					}
					if ($advisorClass_class_schedule_times_utc == '' && $advisorClass_class_schedule_times != '') {
						$newUTC				= TRUE;
						if ($doDebug) {
							echo "missing teaching times in UTC<br />";
						}
					}
					if ($advisorClass_class_schedule_days_utc == '' && $advisorClass_class_schedule_days != '') {
						$newUTC				= TRUE;
						if ($doDebug) {
							echo "missing teaching days in UTC<br />";
						}
					}
					// see if class incomplete.if so, see if that's been fixed
					if ($advisorClass_class_incomplete != '') {
						$incompleteCleared		= TRUE;
						if ($advisorClass_level == '') {
							$incompleteCleared	= FALSE;
						}
						if ($advisorClass_class_schedule_days == '') {
							$incompleteCleared	= FALSE;
							$newUTC				= FALSE;
						}
						if ($advisorClass_class_schedule_times == '') {
							$incompleteCleared	= FALSE;
							$newUTC				= FALSE;
						}
						if ($incompleteCleared) {
							$advisorClass_class_incomplete	= '';
							$actionLogData		.= " Cleared class_incomplete flag ";
							$updateParams['class_incomplete'] = '';
							$updateFormats[]		= '%s';
							$updateContent		.= "Class Incomplete flag cleared<br />";
						}
					}
					if ($newUTC) {
						if ($doDebug) {
							echo  "Calculating UTC for $advisorClass_timezone_offset,$advisorClass_class_schedule_times,$advisorClass_class_schedule_days<br />";
						}
						$result						= utcConvert('toutc',$advisorClass_timezone_offset,$advisorClass_class_schedule_times,$advisorClass_class_schedule_days);
						if ($result[0] == 'FAIL') {
							if ($doDebug) {
								echo "utcConvert failed 'toutc',$advisorClass_timezone,$advisorClass_class_schedule_times,$advisorClass_class_schedule_days<br />
										Error: $result[3]<br />";
							}
							$updateParams['class_schedule_times_utc']			= '';
							$updateParams['class_schedule_days_utc']			= "ERROR";
							$updateParams['class_incomplete']					= 'Y';
							$updateFormats[]		= '%s';
							$updateFormats[]		= '%s';
							$updateFormats[]		= '%s';
							$doUpdate				= TRUE;
						} else {
							$updateParams['class_schedule_times_utc']			= $result[1];
							$updateParams['class_schedule_days_utc']			= $result[2];
							$updateFormats[]		= '%s';
							$updateFormats[]		= '%s';
							$doUpdate				= TRUE;
						}
					}
					if ($doUpdate) {
						$content				.= "<p><b>Class $advisorClass_sequence Updates</b><br />
$updateContent</p>";
						$advisorClass_action_log	= "$advisorClass_action_log / $actionDate ADVREG $actionLogData ";
						$updateParams['action_log']	= $advisorClass_action_log;
						$updateFormats[]			= '%s';
						$classUpdateData		= array('tableName'=>$advisorClassTableName,
														'inp_method'=>'update',
														'inp_data'=>$updateParams,
														'inp_format'=>$updateFormats,
														'jobname'=>$jobname,
														'inp_id'=>$advisorClass_ID,
														'inp_callsign'=>$advisorClass_advisor_call_sign,
														'inp_semester'=>$advisorClass_semester,
														'inp_who'=>$userName,
														'testMode'=>$testMode,
														'doDebug'=>$doDebug);
						$updateResult	= updateClass($classUpdateData);
						if ($updateResult[0] === FALSE) {
							$myError	= $wpdb->last_error;
							$mySql		= $wpdb->last_query;
							$errorMsg	= "A$jobname Processing $advisorClass_advisor_call_sign in $advisorClassTableName failed. Reason: $updateResult[1]<br />SQL: $mySql<br />Error: $myError<br />";
							if ($doDebug) {
								echo $errorMsg;
							}
							sendErrorEmail($errorMsg);
							$content		.= "Unable to update content in $advisorClassTableName<br />";
						}				
					} else {
						if ($doDebug) {
							echo "No updates to class record<br /><br >";
						}
						$content				.= "<p><b>Class $advisorClass_sequence Updates</b>
													<br />No updates requested. ";
						if ($advisorClass_class_incomplete != '') {
							$content			.= "HOWEVER, errors need correction: $advisorClass_class_incomplete.</p>";
						}
						$content				.= "</p>";
					}
					////// now display advisor and class records and provide options
					$result				= getAdvisorInfoToDisplay($inp_callsign,$advisorClass_semester,$noUpdate);
					$content			.= $result[0];
				}
			} else {
				if ($doDebug) {
					echo "No $advisorClassTableName table records for $advisor_call_sign<br/>";
				}
			}
		}

//////////////////		pass 17				Delete a class record

	} elseif ("17" == $strPass) {
		if ($doDebug) {
			echo "At pass 17 to delete a class record: $classID for advisor $inp_callsign<br />";
		}
		if ($userName == '') {
			$userName		= $inp_callsign;
		}
		/// find out how many class records are available for this advisor
		$nbr_classes		= $wpdb->get_var("SELECT count(advisorClass_ID) as nbr_classes 
												from $advisorClassTableName 
												where advisor_call_sign ='$inp_callsign' 
												  and semester='$inp_semester'");

		if ($nbr_classes < 2) {
			if ($doDebug) {
				echo "&nbsp;&nbsp;&nbsp;&nbsp;Number of classes is $nbr_classes. Unable to trash<br />";
			}
			$content		.= "<p>You have requested to delete your only class. In order to do that, 
								you need to delete your entire sign-up record. Go to <a href='$theURL'>CWA Advisor Sign-up</a> 
								and select 'Delete this record'.</p>";
		} else {
			//// delete the record
			$classUpdateData		= array('tableName'=>$advisorClassTableName,
											'inp_method'=>'delete',
											'jobname'=>$jobname,
											'inp_id'=>$advisorClass_ID,
											'inp_callsign'=>$inp_callsign,
											'inp_semester'=>$inp_semester,
											'inp_who'=>$userName,
											'testMode'=>$testMode,
											'doDebug'=>$doDebug);
			$updateResult	= updateClass($classUpdateData);
			if ($updateResult[0] === FALSE) {
				$myError	= $wpdb->last_error;
				$mySql		= $wpdb->last_query;
				$errorMsg	= "A$jobname Processing $advisorClass_advisor_call_sign in $advisorClassTableName failed. Reason: $updateResult[1]<br />SQL: $mySql<br />Error: $myError<br />";
				if ($doDebug) {
					echo $errorMsg;
				}
				sendErrorEmail($errorMsg);
				$content		.= "Unable to update content in $advisorClassTableName<br />";
			} else {
				$content				.= "<p><b>Class Updates</b><br />Class record was deleted.</p>";
/*
				//// see if the classes need to be resequenced
				if ($doDebug) {
					echo "Checking to see if need to resequence classes<br />";
				}
				$sql		= "select advisorclass_id, sequence 
								from $advisorClassTableName 
								where advisor_call_sign='$inp_callsign' 
								and semester='$inp_semester'  
								order by sequence";
				$cwa_advisorclass			= $wpdb->get_results($sql);
				if ($cwa_advisorclass === FALSE) {
					$myError			= $wpdb->last_error;
					$myQuery			= $wpdb->last_query;
					if ($doDebug) {
						echo "Reading $advisorClassTableName table failed<br />
							  wpdb->last_query: $myQuery<br />
							  wpdb->last_error: $myError<br />";
					}
					$errorMsg			= "$jobname Reading $advisorClassTableName table failed. <p>SQL: $myQuery</p><p> Error: $myError</p>";
					sendErrorEmail($errorMsg);
				} else {
					$myInt											= 0;
					$numACRows										= $wpdb->num_rows;
					if ($doDebug) {
						echo "found $numACRows records in $advisorClassTableName<br />";
					}
					if ($numACRows > 0) {
						foreach ($cwa_advisorclass as $advisorClassRow) {
							$advisorClass_ID				 		= $advisorClassRow->advisorclass_id;
							$advisorClass_sequence 					= $advisorClassRow->sequence;
		
							$myInt++;
							if ($myInt != $advisorClass_sequence) {			/// out of sequence, renumber
								$classUpdateData		= array('tableName'=>$advisorClassTableName,
																'inp_method'=>'update',
																'inp_data'=>array('sequence'=>$myInt),
																'inp_format'=>array('%d'),
																'jobname'=>$jobname,
																'inp_id'=>$advisorClass_ID,
																'inp_callsign'=>$advisorClass_advisor_call_sign,
																'inp_semester'=>$advisorClass_semester,
																'inp_who'=>$userName,
																'testMode'=>$testMode,
																'doDebug'=>$doDebug);
								$updateResult	= updateClass($classUpdateData);
								if ($updateResult[0] === FALSE) {
									$myError	= $wpdb->last_error;
									$mySql		= $wpdb->last_query;
									$errorMsg	= "A$jobname Processing $advisorClass_advisor_call_sign in $advisorClassTableName failed. Reason: $updateResult[1]<br />SQL: $mySql<br />Error: $myError<br />";
									if ($doDebug) {
										echo $errorMsg;
									}
									sendErrorEmail($errorMsg);
									$content		.= "Unable to update content in $advisorClassTableName<br />";
								}
							}
						}
					} else {
						if ($doDebug) {
							echo "System Error: Should have been at least one advisorClass record<br />";
						}
					}
				}
*/
			}
			////// now display advisor and class records and provide options
			$result				= getAdvisorInfoToDisplay($inp_callsign,$advisorClass_semester,$noUpdate);
			$content			.= $result[0];
		}

//////////////////		pass 20	 			Delete the advisor and class records
		
		
	} elseif ("20" == $strPass) {			/// delete advisor and advisorclass records

		if ($doDebug) {
			echo "entire advisor and advisorClass records for $inp_callsign are to be deleted<br />";
		}
		if ($userName == '') {
			$userName 			= $inp_callsign;
		}

		// get the advisor record and delete it
		$cwa_advisor			= $wpdb->get_results("select * from $advisorTableName 
													where call_sign='$inp_callsign' 
													  and semester='$inp_semester' 
													order by call_sign");
		if ($cwa_advisor === FALSE) {
			$myError			= $wpdb->last_error;
			$myQuery			= $wpdb->last_query;
			if ($doDebug) {
				echo "Reading $advisorTableName table failed<br />
					  wpdb->last_query: $myQuery<br />
					  wpdb->last_error: $myError<br />";
			}
			$errorMsg			= "$jobname Reading $advisorTableName table failed. <p>SQL: $myQuery</p><p> Error: $myError</p>";
			sendErrorEmail($errorMsg);
		} else {
			$numARows									= $wpdb->num_rows;
			if ($doDebug) {
				echo "found $numARows rows in $advisorTableName table<br />";
			}
			if ($numARows > 0) {
				foreach ($cwa_advisor as $advisorRow) {
					$advisor_ID							= $advisorRow->advisor_id;
					$advisor_select_sequence 			= $advisorRow->select_sequence;
					$advisor_call_sign 					= strtoupper($advisorRow->call_sign);
					$advisor_first_name 				= $advisorRow->first_name;
					$advisor_last_name 					= stripslashes($advisorRow->last_name);
					$advisor_email 						= strtolower($advisorRow->email);
					$advisor_phone						= $advisorRow->phone;
					$advisor_ph_code					= $advisorRow->ph_code;				// new
					$advisor_text_message 				= $advisorRow->text_message;
					$advisor_city 						= $advisorRow->city;
					$advisor_state 						= $advisorRow->state;
					$advisor_zip_code 					= $advisorRow->zip_code;
					$advisor_country 					= $advisorRow->country;
					$advisor_country_code				= $advisorRow->country_code;		// new
					$advisor_whatsapp					= $advisorRow->whatsapp_app;		// new
					$advisor_signal						= $advisorRow->signal_app;			// new
					$advisor_telegram					= $advisorRow->telegram_app;		// new
					$advisor_messenger					= $advisorRow->messenger_app;		// new
					$advisor_time_zone 					= $advisorRow->time_zone;
					$advisor_timezone_id				= $advisorRow->timezone_id;			// new
					$advisor_timezone_offset			= $advisorRow->timezone_offset;		// new
					$advisor_semester 					= $advisorRow->semester;
					$advisor_survey_score 				= $advisorRow->survey_score;
					$advisor_languages 					= $advisorRow->languages;
					$advisor_fifo_date 					= $advisorRow->fifo_date;
					$advisor_welcome_email_date 		= $advisorRow->welcome_email_date;
					$advisor_verify_email_date 			= $advisorRow->verify_email_date;
					$advisor_verify_email_number 		= $advisorRow->verify_email_number;
					$advisor_verify_response 			= strtoupper($advisorRow->verify_response);
					$advisor_action_log 				= $advisorRow->action_log;
					$advisor_class_verified 			= $advisorRow->class_verified;
					$advisor_control_code 				= $advisorRow->control_code;
					$advisor_date_created 				= $advisorRow->date_created;
					$advisor_date_updated 				= $advisorRow->date_updated;

					$advisor_last_name 					= no_magic_quotes($advisor_last_name);
					


					//// delete the record
					$advisorUpdateData		= array('tableName'=>$advisorTableName,
													'inp_method'=>'delete',
													'jobname'=>$jobname,
													'inp_id'=>$advisor_ID,
													'inp_callsign'=>$advisor_call_sign,
													'inp_semester'=>$advisor_semester,
													'inp_who'=>$userName,
													'testMode'=>$testMode,
													'doDebug'=>$doDebug);
					$updateResult	= updateAdvisor($advisorUpdateData);
					if ($updateResult[0] === FALSE) {
						$myError	= $wpdb->last_error;
						$mySql		= $wpdb->last_query;
						$errorMsg	= "$jobname Processing $advisor_call_sign in $advisorTableName failed. Reason: $updateResult[1]<br />SQL: $mySql<br />Error: $myError<br />";
						if ($doDebug) {
							echo $errorMsg;
						}
						sendErrorEmail($errorMsg);
						$content		.= "Unable to update content in $advisorTableName<br />";
					} else {
						if ($doDebug) {
							echo "now delete the class records<br /";
						}
						/// delete the class records
						$cwa_advisorclass	= $wpdb->get_results("select * from $advisorClassTableName 
																	where advisor_call_sign = '$inp_callsign' 
																	  and semester='$inp_semester' 
																	order by sequence");
						if ($cwa_advisorclass === FALSE) {
							$myError			= $wpdb->last_error;
							$myQuery			= $wpdb->last_query;
							if ($doDebug) {
								echo "Reading $advisorClassTableName table failed<br />
									  wpdb->last_query: $myQuery<br />
									  wpdb->last_error: $myError<br />";
							}
							$errorMsg			= "$jobname Reading $advisorClassTableName table failed. <p>SQL: $myQuery</p><p> Error: $myError</p>";
							sendErrorEmail($errorMsg);
						} else {
							$numACRows									= $wpdb->num_rows;
							if ($doDebug) {
								echo "obtained $numACRows rows from $advisorClassTableName table<br />";
							}
							if ($numACRows > 0) {
								foreach ($cwa_advisorclass as $advisorClassRow) {
									$advisorClass_ID				 		= $advisorClassRow->advisorclass_id;
									$advisorClass_advisor_call_sign 		= $advisorClassRow->advisor_call_sign;
									$advisorClass_advisor_first_name 		= $advisorClassRow->advisor_first_name;
									$advisorClass_advisor_last_name 		= stripslashes($advisorClassRow->advisor_last_name);
									$advisorClass_advisor_id 				= $advisorClassRow->advisor_id;
									$advisorClass_sequence 					= $advisorClassRow->sequence;
									$advisorClass_semester 					= $advisorClassRow->semester;
									$advisorClass_timezone 					= $advisorClassRow->time_zone;
									$advisorClass_timezone_id				= $advisorClassRow->timezone_id;		// new
									$advisorClass_timezone_offset			= $advisorClassRow->timezone_offset;	// new
									$advisorClass_level 					= $advisorClassRow->level;
									$advisorClass_class_size 				= $advisorClassRow->class_size;
									$advisorClass_class_schedule_days 		= $advisorClassRow->class_schedule_days;
									$advisorClass_class_schedule_times 		= $advisorClassRow->class_schedule_times;
									$advisorClass_class_schedule_days_utc 	= $advisorClassRow->class_schedule_days_utc;
									$advisorClass_class_schedule_times_utc 	= $advisorClassRow->class_schedule_times_utc;
									$advisorClass_action_log 				= $advisorClassRow->action_log;
									$advisorClass_class_incomplete 			= $advisorClassRow->class_incomplete;
									$advisorClass_date_created				= $advisorClassRow->date_created;
									$advisorClass_date_updated				= $advisorClassRow->date_updated;

									$advisorClass_advisor_last_name  		= no_magic_quotes($advisorClass_advisor_last_name);


									//// delete the class record
									$classUpdateData		= array('tableName'=>$advisorClassTableName,
																	'inp_method'=>'delete',
																	'jobname'=>$jobname,
																	'inp_id'=>$advisorClass_ID,
																	'inp_callsign'=>$advisorClass_advisor_call_sign,
																	'inp_semester'=>$advisorClass_semester,
																	'inp_who'=>$userName,
																	'testMode'=>$testMode,
																	'doDebug'=>$doDebug);
									$updateResult	= updateClass($classUpdateData);
									if ($updateResult[0] === FALSE) {
										$myError	= $wpdb->last_error;
										$mySql		= $wpdb->last_query;
										$errorMsg	= "A$jobname Processing $advisorClass_advisor_call_sign in $advisorClassTableName failed. Reason: $updateResult[1]<br />SQL: $mySql<br />Error: $myError<br />";
										if ($doDebug) {
											echo $errorMsg;
										}
										sendErrorEmail($errorMsg);
										$content		.= "Unable to update content in $advisorClassTableName<br />";
									}
								}
								$content					.= "<h3>Advisor Registration</h3>
																<p>The advisor and class records have been deleted.</p>
																<p>To return to the initial registration page, click 
																<a href='$theURL'>HERE</a></p>
																<p>Otherwise, you can close this window</p>";
							}
						}
					}
				}
			} else {
				if ($doDebug) {
					echo "No advisor record found for $inp_callsign to delete<br />";
				}
			}
		}					
		
		
		
		
///////////////////// pass 25		
		
		
	} elseif ("25" == $strPass) {			/// handle new identifier for non US country
	
		if ($doDebug) {
			echo "at Pass 25<br />";
		}
	
		/// get the advisor information
		$sql					= "select * from $advisorTableName 
									where advisor_id=$inp_advisor_id";
		$wpw1_cwa_advisor	= $wpdb->get_results($sql);
		if ($wpw1_cwa_advisor === FALSE) {
			$myError			= $wpdb->last_error;
			$myQuery			= $wpdb->last_query;
			if ($doDebug) {
				echo "Reading $advisorTableName table failed<br />
					  wpdb->last_query: $myQuery<br />
					  wpdb->last_error: $myError<br />";
			}
			$errorMsg			= "$jobname Reading $advisorTableName table failed. <p>SQL: $myQuery</p><p> Error: $myError</p>";
			sendErrorEmail($errorMsg);
		} else {
			$numARows			= $wpdb->num_rows;
			if ($doDebug) {
				$myStr			= $wpdb->last_query;
				echo "ran $myStr<br />and found $numARows rows in $advisorTableName table<br />";
			}
			if ($numARows > 0) {
				foreach ($wpw1_cwa_advisor as $advisorRow) {
					$advisor_ID							= $advisorRow->advisor_id;
					$advisor_select_sequence 			= $advisorRow->select_sequence;
					$advisor_call_sign 					= strtoupper($advisorRow->call_sign);
					$advisor_first_name 				= $advisorRow->first_name;
					$advisor_last_name 					= stripslashes($advisorRow->last_name);
					$advisor_email 						= strtolower($advisorRow->email);
					$advisor_phone						= $advisorRow->phone;
					$advisor_ph_code					= $advisorRow->ph_code;				// new
					$advisor_text_message 				= $advisorRow->text_message;
					$advisor_city 						= $advisorRow->city;
					$advisor_state 						= $advisorRow->state;
					$advisor_zip_code 					= $advisorRow->zip_code;
					$advisor_country 					= $advisorRow->country;
					$advisor_country_code				= $advisorRow->country_code;		// new
					$advisor_whatsapp					= $advisorRow->whatsapp_app;		// new
					$advisor_signal						= $advisorRow->signal_app;			// new
					$advisor_telegram					= $advisorRow->telegram_app;		// new
					$advisor_messenger					= $advisorRow->messenger_app;		// new
					$advisor_time_zone 					= $advisorRow->time_zone;
					$advisor_timezone_id				= $advisorRow->timezone_id;			// new
					$advisor_timezone_offset			= $advisorRow->timezone_offset;		// new
					$advisor_semester 					= $advisorRow->semester;
					$advisor_survey_score 				= $advisorRow->survey_score;
					$advisor_languages 					= $advisorRow->languages;
					$advisor_fifo_date 					= $advisorRow->fifo_date;
					$advisor_welcome_email_date 		= $advisorRow->welcome_email_date;
					$advisor_verify_email_date 			= $advisorRow->verify_email_date;
					$advisor_verify_email_number 		= $advisorRow->verify_email_number;
					$advisor_verify_response 			= strtoupper($advisorRow->verify_response);
					$advisor_action_log 				= $advisorRow->action_log;
					$advisor_class_verified 			= $advisorRow->class_verified;
					$advisor_control_code 				= $advisorRow->control_code;
					$advisor_date_created 				= $advisorRow->date_created;
					$advisor_date_updated 				= $advisorRow->date_updated;

					$advisor_last_name 					= no_magic_quotes($advisor_last_name);
					
					$advisor_timezone_id				= $inp_timezone_id;
					
					if ($userName == '') {
						$userName						= $advisor_call_sign;
					}
					
					// get the UTC offset
					$myArray				= explode(" ",$advisor_semester);
					$thisYear				= $myArray[0];
					$thisMonDay				= $myArray[1];
					$myConvertArray			= array('Jan/Feb'=>'-01-01','May/Jun'=>'-05-01','Sep/Oct'=>'-09-01');
					$myMonDay				= $myConvertArray[$thisMonDay];
					$thisNewDate			= "$thisYear$myMonDay 00:00:00";
					if ($doDebug) {
						echo "converted $inp_semester to $thisNewDate<br />";
					}
					$timeResult				= time_conversion($inp_timezone_id,$thisNewDate,$doDebug);
					if ($timeResult != FALSE) {
						$advisor_timezone_offset	= $timeResult;
					} else {
						if ($doDebug) {
							echo "doing time_conversion for Rinp_timezone_id, $thisNewDate failed<br />";
						}
						$advisor_timezone_offset	= -99;
					}
					
					$advisor_log			= "$advisor_action_log / $actionDate timezone_id updated to $inp_timezone_id and timezone_offset to $advisor_timezone_offset ";
					$advisorUpdateData		= array('tableName'=>$advisorTableName,
													'inp_method'=>'update',
													'inp_data'=>array('timezone_id'=>$advisor_timezone_id,
																  'timezone_offset'=>$advisor_timezone_offset,
																  'action_log'=>$advisor_log),
													'inp_format'=>array('%s','%f','%s'),
													'jobname'=>$jobname,
													'inp_id'=>$advisor_ID,
													'inp_callsign'=>$advisor_call_sign,
													'inp_semester'=>$advisor_semester,
													'inp_who'=>$userName,
													'testMode'=>$testMode,
													'doDebug'=>$doDebug);
					$updateResult	= updateAdvisor($advisorUpdateData);
					if ($updateResult[0] === FALSE) {
						$myError	= $wpdb->last_error;
						$mySql		= $wpdb->last_query;
						$errorMsg	= "$jobname Processing $advisor_call_sign in $advisorTableName failed. Reason: $updateResult[1]<br />SQL: $mySql<br />Error: $myError<br />";
						if ($doDebug) {
							echo $errorMsg;
						}
						sendErrorEmail($errorMsg);
						$content		.= "Unable to update content in $advisorTableName<br />";
					} else {
						if ($doDebug) {
							echo "redoing class info based on new timezone offset<br />";
						}
						$classResult			= $wpdb->get_results("select * from $advisorClassTableName 
																		where advisor_call_sign = '$advisor_call_sign' 
																		  and semester='$inp_semester' 
																		order by sequence");
						if ($classResult === FALSE) {
							$myError			= $wpdb->last_error;
							$myQuery			= $wpdb->last_query;
							if ($doDebug) {
								echo "Reading $advisorClassTableName table failed<br />
									  wpdb->last_query: $myQuery<br />
									  wpdb->last_error: $myError<br />";
							}
							$errorMsg			= "$jobname Reading $advisorClassTableName table failed. <p>SQL: $myQuery</p><p> Error: $myError</p>";
							sendErrorEmail($errorMsg);
						} else {
							$numACRows			= $wpdb->num_rows;
							if ($doDebug) {
								echo "Obtained $numACRows rows from $advisorClassTableName for advisor $advisor_call_sign<br />";
							}
							if ($numACRows > 0) {
								foreach($classResult as $advisorClassRow) {
									$advisorClass_ID				 		= $advisorClassRow->advisorclass_id;
									$advisorClass_advisor_call_sign 		= $advisorClassRow->advisor_call_sign;
									$advisorClass_advisor_first_name 		= $advisorClassRow->advisor_first_name;
									$advisorClass_advisor_last_name 		= stripslashes($advisorClassRow->advisor_last_name);
									$advisorClass_advisor_id 				= $advisorClassRow->advisor_id;
									$advisorClass_sequence 					= $advisorClassRow->sequence;
									$advisorClass_semester 					= $advisorClassRow->semester;
									$advisorClass_timezone 					= $advisorClassRow->time_zone;
									$advisorClass_timezone_id				= $advisorClassRow->timezone_id;		// new
									$advisorClass_timezone_offset			= $advisorClassRow->timezone_offset;	// new
									$advisorClass_level 					= $advisorClassRow->level;
									$advisorClass_class_size 				= $advisorClassRow->class_size;
									$advisorClass_class_schedule_days 		= $advisorClassRow->class_schedule_days;
									$advisorClass_class_schedule_times 		= $advisorClassRow->class_schedule_times;
									$advisorClass_class_schedule_days_utc 	= $advisorClassRow->class_schedule_days_utc;
									$advisorClass_class_schedule_times_utc 	= $advisorClassRow->class_schedule_times_utc;
									$advisorClass_action_log 				= $advisorClassRow->action_log;
									$advisorClass_class_incomplete 			= $advisorClassRow->class_incomplete;
									$advisorClass_date_created				= $advisorClassRow->date_created;
									$advisorClass_date_updated				= $advisorClassRow->date_updated;

									$advisorClass_advisor_last_name  		= no_magic_quotes($advisorClass_advisor_last_name);
									
									$advisorClass_timezone_id				= $advisor_timezone_id;
									$advisorClass_timezone_offset			= $advisor_timezone_offset;
									
									if ($advisorClass_timezone_id != 'Unknown') {
										$utcResult								= utcConvert('toutc',$advisorClass_timezone_offset,$advisorClass_class_schedule_times,$advisorClass_class_schedule_days,$doDebug);
										if ($utcResult[0] == 'OK') {
											$advisorClass_class_schedule_times_utc	= $utcResult[1];
											$advisorClass_class_schedule_days_utc	= $utcResult[2];
										} else {
											$advisorClass_class_schedule_times_utc	= 'None';
											$advisorClass_class_schedule_days_utc	= 'None';
											if ($doDebug) {
												echo "Getting UTC times failed: $utcResult[3]<br />";
											}
										}
									} else {
										$advisorClass_class_schedule_times_utc	= 'None';
										$advisorClass_class_schedule_days_utc	= 'None';
									}
									if ($doDebug) {
										echo "updating advisor $advisor_call_sign class $advisorClass_sequence<br />";
									}
									$classUpdateData		= array('tableName'=>$advisorClassTableName,
																	'inp_method'=>'update',
																	'inp_data'=>array('timezone_id'=>$advisorClass_timezone_id,
																				  'timezone_offset'=>$advisorClass_timezone_offset,
																				  'class_schedule_days_utc'=>$advisorClass_class_schedule_days_utc,
																				  'class_schedule_times_utc'=>$advisorClass_class_schedule_times_utc),
																	'inp_format'=>array('%s','%f','%s','%s'),
																	'jobname'=>$jobname,
																	'inp_id'=>$advisorClass_ID,
																	'inp_callsign'=>$advisorClass_advisor_call_sign,
																	'inp_semester'=>$advisorClass_semester,
																	'inp_who'=>$userName,
																	'testMode'=>$testMode,
																	'doDebug'=>$doDebug);
									$updateResult	= updateClass($classUpdateData);
									if ($updateResult[0] === FALSE) {
										$myError	= $wpdb->last_error;
										$mySql		= $wpdb->last_query;
										$errorMsg	= "A$jobname Processing $advisorClass_advisor_call_sign in $advisorClassTableName failed. Reason: $updateResult[1]<br />SQL: $mySql<br />Error: $myError<br />";
										if ($doDebug) {
											echo $errorMsg;
										}
										sendErrorEmail($errorMsg);
										$content		.= "Unable to update content in $advisorClassTableName<br />";
									}
								}
								//// display the advisor / class record
								$result				= getAdvisorInfoToDisplay($inp_callsign,$advisorClass_semester,$noUpdate);
								$content			.= $result[0];
							}
						}
					}	
				}
			} else {
				if ($doDebug) {
					echo "no record found in $advisorTableName for id $inp_advisor_id $inp_callsign<br />";
				}
			}
		}	
	}

	$endingMicroTime = microtime(TRUE);
	$elapsedTime	= $endingMicroTime - $startingMicroTime;
	$elapsedTime	= number_format($elapsedTime, 4, '.', ',');
	$content		.= "<br /><br /><p>Report pass $strPass took $elapsedTime seconds to run</p>";
	$thisTime 		= date('Y-m-d H:i:s',$currentTimestamp);
	$nowDate		= date('Y-m-d');
	$nowTime		= date('H:i:s');
	$thisStr			= 'Production';
	if ($testMode) {
		$thisStr		= 'Testmode';
	}
	$ipAddr			= get_the_user_ip();
	$result			= write_joblog_func("Advisor Registration|$nowDate|$nowTime|$userName|Time|$thisStr|$strPass: $elapsedTime|$ipAddr");
	if ($result == 'FAIL') {
		$content	.= "<p>writing to joblog.txt failed</p>";
	}
	$content 		.= "<br /><br /><p>Prepared at $thisTime</p>";
	return $content;
}
add_shortcode ('advisor_registration_v3', 'advisor_registration_v3_func');
