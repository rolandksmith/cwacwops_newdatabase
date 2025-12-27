function get_student_and_user_master($studentCallSign, $requestType, $requestInfo, $operatingMode, $doDebug) {

	/**
	Gets the student fields for a specific semester along with the associated user_master fields
	
	@param string $studentCallSign
	@param string $requestType		callsign|id|complex	
	@param string $requestInfo		a specific semester|future|student_id|criteria
		criteria needs to be an array that includes
			the criteria array
			the orderby sequence
			the order direction_
				$requestInfo = array('criteria' => $criterial,
									 'orderby' => $orderby,
									 'order' => $order);
	@param string $operatingMode
	@param bool $doDebug
	@return array|FALSE If requestType callsign or id, a single level array
						if requestType complex, an array of arrays
	
	
	*/
	
	if ($doDebug) {
		if ($requestType != 'complex') {
			echo "<br /><b>Function get_student_and_user_master</b><br />
					studentCallSign: $studentCallSign<br/>
					requestType: $requestType<br />
					requestInfo: $requestInfo<br />";
		} else {
			echo "<br /><b>Function get_student_and_user_master</b><br />
					studentCallSign: $studentCallSign<br/>
					requestType: $requestType<br />
					requestInfo:<br /><pre>";
			print_r($requestInfo);
			echo "</pre><br />";		
		}
	}
	
	if  (class_exists('CWA_User_Master_DAL')) {
		$user_dal = new CWA_User_Master_DAL();
//		echo "user_dal is defined<br />";
	} else {
		echo "CWA_User_Master_DAL doesn't exist<br />";
		return FALSE;
	}
	$student_dal = new CWA_Student_DAL();

 	$initializationArray 		= data_initialization_func();
	$currentSemester 			= $initializationArray['currentSemester'];
	$nextSemester 				= $initializationArray['nextSemester'];
	$semesterTwo 				= $initializationArray['semesterTwo'];
	$semesterThree 				= $initializationArray['semesterThree'];
	$semesterFour 				= $initializationArray['semesterFour'];
	

	
	// get the student information
	if ($requestType == 'callsign') {
		if ($requestInfo == 'future') {
			$criteria = [
				'relation' => 'AND',
				'clauses' => [
					// field1 = $value1
					['field' => 'student_call_sign', 'value' => $studentCallSign, 'compare' => '='],
					
					// (field2 = $value2 OR field2 = $value3)
					[
						'relation' => 'OR',
						'clauses' => [
							['field' => 'student_semester', 'value' => $currentSemester, 'compare' => '='],
							['field' => 'student_semester', 'value' => $nextSemester, 'compare' => '='],
							['field' => 'student_semester', 'value' => $semesterTwo, 'compare' => '='],
							['field' => 'student_semester', 'value' => $semesterThree, 'compare' => '='],
							['field' => 'student_semester', 'value' => $semesterFour, 'compare' => '=']
						]
					]
				]
			];
		} else {
			$criteria = [
				'relation' => 'AND',
				'clauses' => [
					['field' => 'student_call_sign', 'value' => $studentCallSign, 'compare' => '=' ],
					['field' => 'student_semester', 'value' => $requestInfo, 'compare' => '=' ]
				]
			];

		}
		$studentData = $student_dal->get_student_by_order($criteria,'student_call_sign','ASC',$operatingMode);
		if ($studentData === FALSE || $studentData === NULL) {
			if ($doDebug) {
				echo "get_student for $studentCallSign returned FALSE|NULL<br />";
			}
			return FALSE;
		} else {
			if (! empty($studentData)) {
				foreach($studentData as $key => $value) {
					foreach($value as $fieldName => $fieldValue) {
						$returnArray[$fieldName] = $fieldValue;
					}
				}
				// add the user_master data to the return array
				$userData =get_user_data($studentCallSign, $operatingMode);
				if ($userData === FALSE || $userData === NULL) {
					if ($doDebug) {
						echo "get_student_user_data for $studentCallSign returned FALSE|NULL<br />";
					}
					return FALSE;
				} else {
					if (! empty($userData)) {
						foreach($userData as $key => $value) {
							$returnArray[$key] = $value;
						}
					} else {
						if ($doDebug) {
							echo "no user_master data found for $studentCallSign<br />";
						}
						return FALSE;
					}
				}
			} else {
				if ($doDebug) {
					echo "no data found for $studentCallSign<br />";
				}
				return FALSE;
			}
		}
	} elseif ($requestType == 'id') {	// get student by id
		$studentData = $student_dal->get_student_by_id($requestInfo, $operatingMode);
		if ($studentData === FALSE || $studentData === NULL) {
			if ($doDebug) {
				echo "get_student for $studentCallSign returned FALSE|NULL<br />";
			}
			return FALSE;
		} else {
			if (! empty($studentData)) {
				foreach($studentData as $key => $value) {
					$returnArray[$key] = $value;
				}
				$studentCallSign = $returnArray['student_call_sign'];
				// add the user_master data to the return array
				// add the user_master data to the return array
				$userData =get_user_data($studentCallSign, $operatingMode);
				if ($userData === FALSE || $userData === NULL) {
					if ($doDebug) {
						echo "get_student_user_data for $studentCallSign returned FALSE|NULL<br />";
					}
					return FALSE;
				} else {
					if (! empty($userData)) {
						foreach($userData as $key => $value) {
							$returnArray[$key] = $value;
						}
					} else {
						if ($doDebug) {
							echo "no user_master data found for $studentCallSign<br />";
						}
						return FALSE;
					}
				}
			} else {
				if ($doDebug) {
					echo "no data found for $studentCallSign<br />";
				}
				return FALSE;
			}
		}



	} elseif ($requestType == 'complex') {
		$gotError = FALSE;
		$arrayKey = -1;
		if (! array_key_exists('criteria', $requestInfo)) {
			if ($doDebug) {
				echo "criteria is issing from requestInfo<br />";
			}
			$gotError = TRUE;
		} else {
			$criteria = $requestInfo['criteria'];
		}
		if (! array_key_exists('orderby', $requestInfo)) {
			if ($doDebug) {
				echo "orderby is issing from requestInfo<br />";
			}
			$gotError = TRUE;
		} else {
			$orderby = $requestInfo['orderby'];
		}
		if (! array_key_exists('order', $requestInfo)) {
			if ($doDebug) {
				echo "order is issing from requestInfo<br />";
			}
			$gotError = TRUE;
		} else {
			$order = $requestInfo['order'];
		}

		if (! $gotError) {
			$studentData = $student_dal->get_student_by_order( $criteria, $orderby, $order, $operatingMode );
			if ($studentData === FALSE || $studentData === NULL) {
				if ($doDebug) {
					echo "get_student by criteria returned FALSE|NULL<br />";
				}
				return FALSE;
			} else {
				if (! empty($studentData)) {
					foreach($studentData as $key => $value) {
						$arrayKey++;
						foreach($value as $thisField => $thisValue) {
							$returnArray[$arrayKey][$thisField] = $thisValue;
							if ($thisField == 'student_call_sign') {
								$studentCallSign = $thisValue;
							}
						}
						// add the user_master data to the return array
						$userData =get_user_data($studentCallSign, $operatingMode);
						if ($userData === FALSE || $userData === NULL) {
							if ($doDebug) {
								echo "get_student_user_data for $studentCallSign returned FALSE|NULL<br />";
							}
							return FALSE;
						} else {
							if (! empty($userData)) {
								foreach($userData as $key => $value) {
									$returnArray[$arrayKey][$key] = $value;
								}
							} else {
								if ($doDebug) {
									echo "no user_master data found for $studentCallSign<br />";
								}
								return FALSE;
							}
						}
					}
				} else {
					if ($doDebug) {
						echo "no data found for complex search<br />";
					}
					return FALSE;
				}
			}
		}
		
	} else {
		if ($doDebug) {
			echo "requestType $requestType is invalid<br />";
		}
		return FALSE;
	}
	return $returnArray;
}
add_action('get_student_and_user_master','get_student_and_user_master');
