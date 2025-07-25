function get_user_master_data($dataArray=array()) {

/*	Input data array: 	
			getMethod			userName
			getInfo				the value of userName
			doDebug				value of doDebug
			testMode			value of testMode
			
	The function first looks in user_master for the requested getInfo. If a record 
	is found, the function errors and returns

	If no record is found it looks in wpw1_users 
	to get the user's id, then reads the data from wpw1_usermeta, and creates the 
	user_master record.
		
	If the user_master record being created from 
	wpw1_users, the program attempts to figure out the timezone_id. If 
	the country_code is US, then the zip code is used. Otherwise, only 
	the country_code. If a country has more than one timezone_id, the 
	program sets the timezone_id to '??' and it up to the calling program 
	to figure out the appropriate timezone_id.
	
	Modified 13Apr2025 by Roland for Ultimate Member
*/

	global $wpdb;	
	
	$thisDate		= date('Y-m-d H:i:s');
	$getMethod		= '';
	$getInfo		= '';
	$doDebug		= TRUE;
	$testMode		= FALSE;
	$gotError		= FALSE;
	$errors			= "";
	
	$returnArray	= array();
	

	// unpack the dataArray
	if (count($dataArray) == 0) {
		$gotError			= TRUE;
		$errors				.= "dataArray is empty<br />";
	} else {
		foreach($dataArray as $thisField=>$thisValue) {
			$$thisField		= $thisValue;
		}
		if ($doDebug) {
			echo "<br /><b>FUNCTION Get User Master Data</b><br />
				  dataArray:<br /><pre>";
			print_r($dataArray);
			echo "</pre><br />";
		}	
	
		if ($getMethod != 'callsign') {
			$gotError			= TRUE;
			$errors				.= "invalid getMethod of $getMethod<br />";
		}
	
		if ($getInfo == '') {
			$gotError			= TRUE;
			$errors				.= "getInfo is mssing<br />";
		}

	if (!$gotError) {
		// set up the tables
		if ($testMode) {
			if ($doDebug) {
				echo "<p><strong>Operating in Test Mode.</strong></p>";
			}
			$userTableName				= "wpw1_users";
			$userMetaTableName			= "wpw1_usermeta2";
			$userMasterTableName		= "wpw1_cwa_user_master2";
			$countryCodesTableName		= "wpw1_cwa_country_codes";
		} else {
			$userTableName				= "wpw1_users";
			$userMetaTableName			= "wpw1_usermeta";
			$userMasterTableName		= "wpw1_cwa_user_master";
			$countryCodesTableName		= "wpw1_cwa_country_codes";
		}
		
		// read user_master. If record found, error and return
		
		$getStr			= strtoupper($getInfo);
		$sql			= "select * from $userMasterTableName 
								where user_call_sign like '$getStr'";
		$sqlResult		= $wpdb->get_results($sql);
		if ($sqlResult === FALSE) {
			handleWPDBError("FUNCTION User Master Data",$doDebug);
			$gotError			= TRUE;
			$errors				.= "reading $userMasterTableName for $callsign failed<br />";
		} else {
			$numRows	= $wpdb->num_rows;
			if ($doDebug) {
				echo "ran $sql<br />and retrieved $numRows rows<br />";
			}
			if ($numRows > 0) {
				$gotError		= TRUE;
				$errors			.= "$getInfo already has a userMaster record<br />";
			} else {
				// No userMaster record. Create it
				$verifiedUser		= FALSE;
				$user_id			= "";
				$user_first_name	= "";
				$user_last_name		= "";
				$user_email			= "";
				$user_phone			= "";
				$user_ph_code		= "";
				$user_city			= "";
				$user_state			= "";
				$user_zip_code		= "";
				$user_country_code	= "";
				$user_country		= "";
				$user_whatsapp		= "";
				$user_telegram		= "";
				$user_signal		= "";
				$user_messenger		= "";
				$user_action_log	= "";
				$user_survey_score	= 0;
				$user_is_admin		= "";
				$user_role			= "";
				$user_timezone_id	= "";
				$user_languages		= "";

				$userSQL			= "select * from $userTableName 
										where user_login like '$getInfo'";
				$userResult			= $wpdb->get_results($userSQL);
				if ($userResult === FALSE) {
					handleWPDBError("FUNCTION Get User Master Data",$doDebug,"running $userSQL returned a result of FALSE");
					$gotError		= TRUE;
					$errors			.= "attempting to query $userTableName returned FALSE<br />";
				} else {
					$numURows	= $wpdb->num_rows;
					if ($doDebug) {
						echo "ran $userSQL<br />and retrieved $numURows rows<br />";
					}
					if ($numURows > 0) {			// have a users record. Get the ID
						foreach($userResult as $userRow) {
							$thisID			= $userRow->ID;
							$user_call_sign	= strtoupper($userRow->user_login);
							$user_email		= $userRow->user_email;
							
							if ($doDebug) {
								echo "have a ID of $thisID for $user_call_sign<br />";
							}
$verifiedUser = TRUE;
/*							
							// first determine if this is a verified user
							$verifiedData	= $wpdb->get_var("select meta_value 
												from $userMetaTableName 
												where user_id = $thisID 
												and meta_key = 'account_status'");
							if ($doDebug) {
								$lastQuery	= $wpdb->last_query;
								echo "ran $lastQuery. verifiedData:<br ><pre>";
								print_r($verifiedData);
								echo "</pre><br />";
							}
							if ($verifiedData == NULL) {
								if ($doDebug) {
									echo "verifiedData is NULL<br />";
								}
							} elseif ($verifiedData == "approved") {
								$verifiedUser	= TRUE;
								if ($doDebug) {
									echo "user is approved<br />";
								}
							}
*/
							// if user not verified, return no record found
							if (!$verifiedUser) {
								if ($doDebug) {
									echo "unverified user, so no record for this callsign<br />";
								}
								$gotError			= TRUE;
								$errors				.= "Unverified user. Treat as no record found<br />";
							} else {
								// now with this ID, get the data from userMeta
								$metaSQL		= "select * from $userMetaTableName 
													where user_id = $thisID 
													and meta_key = 'submitted'";
								$metaResult		= $wpdb->get_results($metaSQL);
								if ($metaResult === FALSE) {
									handleWPDBError("FUNCTION User Master Data",$doDebug);
									if ($doDebug) {
										echo "running $metaSQL returned a result of FALSE<br />";
									}
									$gotError		= TRUE;
									$errors			.= "attempting to read $userMetaTableName returned FALSE<br />";
								} else {
									$numMRows	= $wpdb->num_rows;
									if ($doDebug) {
										echo "ran $metaSQL<br />and retrieved $numMRows rows<br />";
									}
									if ($numMRows > 0) {
										foreach($metaResult as $metaRow) {
											$this_umeta_id		= $metaRow->umeta_id;
											$this_user_id		= $metaRow->user_id;
											$this_meta_key		= $metaRow->meta_key;
											$this_meta_value	= $metaRow->meta_value;
											
											$metaArray			= unserialize($this_meta_value);
											if ($doDebug) {
												echo "submitted data:<br /><pre>";
												print_r($metaArray);
												echo "</pre><br />";
											}
											$user_id			= $this_umeta_id;
											$user_first_name	= $metaArray['first_name'];
											$user_last_name		= $metaArray['last_name'];
											$user_email			= $metaArray['user_email'];
											$user_phone			= $metaArray['phone_number'];
											$user_city			= $metaArray['user_city'];
											$user_state			= $metaArray['user_state'];
											$user_zip_code		= $metaArray['user_zipcode'];
											$user_country		= $metaArray['country'];
											if (array_key_exists('whatsapp',$metaArray)) {
												$user_whatsapp		= $metaArray['whatsapp'];
											} else {
												$user_whatsapp		= '';
											}
											if (array_key_exists('user_telegram',$metaArray)) {
												$user_telegram		= $metaArray['user_telegram'];
											} else {
												$user_telegram		= '';
											}
											if (array_key_exists('user_signal',$metaArray)) {
												$user_signal		= $metaArray['user_signal'];
											} else {
												$user_signal		= '';
											}
											if (array_key_exists('user_messenger',$metaArray)) {
												$user_messenger		= $metaArray['user_messenger'];
											} else {
												$user_messenger		= '';
											}
											$user_action_log	= "";
											$user_survey_score	= 0;
											$user_is_admin		= "";
											$user_role			= $metaArray['user_role'][0];
											$user_timezone_id	= "";
//											$user_languages		= $metaArray['user_languages'];
											$user_languages		= "";

											// get the country and ph_code
											if ($doDebug) {
												echo "getting country code and phone code for $user_country<br />";
											}
											$countrySQL		= "select * from $countryCodesTableName 
																where country_name = '$user_country'";
											$countrySQLResult		= $wpdb->get_results($countrySQL);
											if ($countrySQLResult[0] === FALSE) {
												handleWPDBError($jobname,$doDebug);
												$user_country_code	= "??";
												$user_ph_code		= "";
											} else {
												$numCRows		= $wpdb->num_rows;
												if ($doDebug) {
													echo "ran $countrySQL<br />and retrieved $numCRows rows<br />";
												}
												if($numCRows > 0) {
													foreach($countrySQLResult as $countryRow) {
														$user_country_code	= $countryRow->country_code;
														$user_ph_code		= $countryRow->ph_code;
													}
												} else {
													$user_country_code		= "??";
													$user_ph_code			= "";
												}
											}
											// figure out the timezone_id
											if ($doDebug) {
												echo "figuring out the timezone_id. Country_code: $user_country_code<br />";
											}
											$doCheck				= TRUE;
											if ($user_country_code == '??') {
												$user_timezone_id 	= '??';
												$doCheck			= FALSE;
											} else {
												if ($user_zip_code != '') {
													// check using zip code
													if ($user_country_code == 'US') {
														if (strlen($user_zip_code) < 5) {
															if ($doDebug) {
																echo "Have invalid US zip code<br />";
															}
															$user_zip_code		= '??';
															$user_timezone_id	= '??';
															$doCheck		 	= FALSE;
														} else {
															$myInt			= strpos($user_zip_code,"-");
															if ($myInt === FALSE) {
																$checkStr			= array('zip'=>$user_zip_code,
																							'country'=>$user_country);
															} else {
																$newZip				= substr($user_zip_code,0,$myInt);
																$checkStr			= array('zip'=>$newZip,
																							'country'=>$user_country);
															}
														}
													} else {
														$checkStr			= array('zip'=>$user_zip_code,
																					'country'=>$user_country);
													}
												} else {
													if ($user_country_code == 'US') {
														$user_zip_code 		= '??';
														$user_timezone_id	= '??';
														$doCheck			= FALSE;
														if ($doDebug) {
															echo "US country code and empty zipcode. Set zipcode to ??<br />";
														}
													}
													// no zipcode. check using address info
													$checkStr			= array('city'=>$user_city,
																				'state'=>$user_state,
																				'country'=>$user_country);
												}
												if ($doCheck) {
													$checkStr['doDebug']	= $doDebug;
													$user_timezone_id		= getTimeZone($checkStr);
												}
											}
											
											// check and fix if needed the role
											// get what the metadata says
											$isStudent			= FALSE;
											$isAdvisor			= FALSE;
											$isAdministrator	= FALSE;
											$capabilitiesSQL 	= "select * from $userMetaTableName 
																	where user_id = $thisID 
																	and meta_key = 'wpw1_capabilities'";
																	
											$capabilitiesResult		= $wpdb->get_results($capabilitiesSQL);
											if ($capabilitiesResult === FALSE) {
												handleWPDBError("FUNCTION User Master Data",$doDebug);
												if ($doDebug) {
													echo "running $capabilitiesSQL returned a result of FALSE<br />";
												}
												$gotError		= TRUE;
												$errors			.= "attempting to read $userMetaTableName for wpw1_capabilities returned FALSE<br />";
											} else {
												$numMRows	= $wpdb->num_rows;
												if ($doDebug) {
													echo "ran $capabilitiesSQL<br />and retrieved $numMRows rows<br />";
												}
												if ($numMRows > 0) {
													foreach($capabilitiesResult as $capabilitiesRow) {
														$capabilities	= $capabilitiesRow->meta_value;
														
														if (preg_match('/student/',$capabilities)) {
															$isStudent		= TRUE;
														}
														if (preg_match('/advisor/',$capabilities)) {
															$isAdvisor		= TRUE;
														}
														if (preg_match('/administrator/',$capabilities)) {
															$isAdministrator	= TRUE;
														}
													}
												} 
											}
											$updateMeta			= FALSE;
											$capArray 			= unserialize($capabilities);
											if ($doDebug) {
												echo "capabilities:<br /><pre>";
												print_r($capArray);
												echo "</pre><br />
														user_role: $user_role<br />";
											}
											if ($isStudent) {
												if ($user_role != 'Student') {
													// must update usermeta capabilities
													$updateMeta	= TRUE;
													if (array_key_exists('student',$capArray)) {
														unset ($capArray['student']);
														$capArray['advisor']	= 1;
													}
												}
											}
											if ($isAdvisor) {
												if ($user_role != 'Advisor') {
													// must update usermeta capabilite\ies
													$updateMeta	= TRUE;
													if (array_key_exists('advisor',$capArray)) {
														unset ($capArray['advisor']);
														$capArray['student']	= 1;
													}
												}
											}
											if ($updateMeta) {
												if ($doDebug) {
													echo "updating metadata for wpw1_capabilities<pre><br />";
													print_r($capArray);
													echo "</pre><br />";
												}
												$capabilities		= serialize($capArray);
												$metaUpdate		= $wpdb->update($userMetaTableName,
																			array('meta_value'=>$capabilities),
																			array('user_id'=>$thisID,
																					'meta_key'=>'wpw1_capabilities'),
																			array('%s'),
																			array('%d','%s'));
												
											}
											if ($isAdministrator) {
												$user_is_admin	= 'Y';
											}
											
											// have all the data. Insert the record into user_master
											$user_action_log	= "/ $thisDate $user_call_sign Record created ";
											$user_prev_callsign	= '';
	
											try {
												$userInsert			= $wpdb->insert($userMasterTableName,
																					array('user_call_sign'=>$user_call_sign,
																						  'user_first_name'=>$user_first_name,
																						  'user_last_name'=>$user_last_name,
																						  'user_email'=>$user_email,
																						  'user_ph_code'=>$user_ph_code,
																						  'user_phone'=>$user_phone,
																						  'user_city'=>$user_city,
																						  'user_state'=>$user_state,
																						  'user_zip_code'=>$user_zip_code,
																						  'user_country_code'=>$user_country_code,
																						  'user_country'=>$user_country,
																						  'user_whatsapp'=>$user_whatsapp,
																						  'user_telegram'=>$user_telegram,
																						  'user_signal'=>$user_signal,
																						  'user_messenger'=>$user_messenger,
																						  'user_action_log'=>$user_action_log,
																						  'user_survey_score'=>0,
																						  'user_timezone_id'=>$user_timezone_id,
																						  'user_languages'=>$user_languages, 
																						  'user_is_admin'=>$user_is_admin,
																						  'user_prev_callsign'=>'', 
																						  'user_role'=>$user_role ),
																					  array('%s',
																							'%s',
																							'%s',
																							'%s',
																							'%s',
																							'%s',
																							'%s',
																							'%s',
																							'%s',
																							'%s',
																							'%s',
																							'%s',
																							'%s',
																							'%s',
																							'%s',
																							'%s',
																							'%d',
																							'%s',
																							'%s',
																							'%s',
																							'%s',
																							'%s'));
												if ($userInsert === FALSE) {
													handleWPDBError("FUNCTION_User_Master_Data",$doDebug);
													if ($doDebug) {
														echo "attempting to insert data for $user_call_sign into $userMasterTableName returned FALSE<br />";
													}
													$gotError			= TRUE;
													$errors				.= "unable to insert $user_call_sign info into $userMasterTableName table<br />";
												} else {
													$id			= $wpdb->insert_id;
													if ($doDebug) {
														echo "user_master record added for $user_call_sign at id $id<br />";
													}
			
													// now return the data
													$myDate					= date('Y-m-d H:i:s');
													$returnArray			= array('result'=>TRUE,
																					'reason'=>'',
																					'user_id'=>$id,
																					'user_call_sign'=>$user_call_sign,
																					'user_first_name'=>$user_first_name,
																					'user_last_name'=>$user_last_name,
																					'user_country'=>$user_country,
																					'user_email'=>$user_email,
																					'user_phone'=>$user_phone,
																					'user_ph_code'=>$user_ph_code,
																					'user_city'=>$user_city,
																					'user_state'=>$user_state,
																					'user_zip_code'=>$user_zip_code,
																					'user_country_code'=>$user_country_code,
																					'user_country'=>$user_country,
																					'user_whatsapp'=>$user_whatsapp,
																					'user_telegram'=>$user_telegram,
																					'user_signal'=>$user_signal,
																					'user_messenger'=>$user_messenger,
																					'user_action_log'=>$user_action_log,
																					'user_languages'=>$user_languages,
																					'user_timezone_id'=>$user_timezone_id,
																					'user_survey_score'=>$user_survey_score,
																					'user_is_admin'=>$user_is_admin,
																					'user_role'=>$user_role,
																					'user_prev_callsign'=>$user_prev_callsign,
																					'user_date_created'=>$myDate,
																					'user_date_updated'=>$myDate);
													if ($doDebug) {
														echo "have all the data. Returning<br /><br />";
													}
												}
											} catch (Exception $e) {
												$theError		= $e->getMessage();
												if ($doDebug) {
													echo "insert exception: $theError<br />";
												}
												error_log("get_user_master insert exception: $theError",1,"rolandksmith@gmail.com");
												$returnArray				= array('result'=>FALSE,
																					'reason'=>"$theError",
																					'count'=>0);
											}
										}
									} else {
										if ($doDebug) {
											echo "no userMeta records for $callsign<br />";
										}
										$gotError			= TRUE;
										$errors				.= "no records found for $callsign in $userMetaTableName table<br />";
									}
								}
							}
						}
					} else {
						if ($doDebug) {
							echo "no users record, so no record for this callsign<br />";
						}
						$gotError		= TRUE;
						$errors			.= "No record for $getInfo in $userTableName<br />";
					}
				}				
			}
		}
	}
	if ($gotError) {
		$returnArray	= array('result'=>FALSE, 
								'reason'=>$errors);
	}
	return $returnArray;
	}
}
add_action('get_user_master_data','get_user_master_data');
