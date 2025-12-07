function updateExcludedAdvisor($excludedAdvisors,$newExcludedAdvisor,$direction,$doDebug) {

/*	Either adds a new excluded advisor or deletes an excluded advisor

	@param string $excludedAdvisor 	current list of excluded advisors
	@param string $newExcludedAdvisor	the advisor to be added or removed
	@param string $direction	add|delete	(action to be taken)
	@param Bool    $doDebug				
	@return string|FALSE	new list of excluded advisors
							or
							FALSE if something didn't work

*/

	if ($doDebug) {
		echo "<br /><b>updateExcludedAdvisor function invoked</b><br />
				current list: $excludedAdvisors<br />
				advisor to be added or deleted: $newExcludedAdvisor<br />
				action: $direction<br />";
	}
	
	$excludedAdvisors = str_replace('|','&',$excludedAdvisors);
	
	if ($direction != 'add' && $direction != 'delete') {
		if ($doDebug) {
			echo "direction of $direction invalid<br />";
		}
	} else {
		if ($direction == 'add') {
			$newExcludedAdvisorList	= '';
			if ($excludedAdvisors == '') {
				$newExcludedAdvisorList		= $newExcludedAdvisor;
				if ($doDebug) {
					echo "no previous excluded advisors. sending back $newExcludedAdvisor<br />";
				}
				if ($doDebug) {
					echo "returning<br />";
				}
				return $newExcludedAdvisorList;
			} else {
				if (str_contains($excludedAdvisors,$newExcludedAdvisor)) {  // already there
					// compress and return
					if ($doDebug) {
						echo "new advisor already in the current list<br />";
					}
					$myArray 				= explode('&',$excludedAdvisors);
					$newExcluded			= array_unique($myArray);
					foreach($newExcluded as $thisKey => $thisValue) {
						if ($thisValue == '') {
							unset($newExcluded[$thisKey]);
						}
					}
					$newExcludedAdvisorList	= implode('&',$newExcluded);
					if ($doDebug) {
						echo "returning<br />";
					}
					return $newExcludedAdvisorList;
				} else {
					if ($doDebug) {
						echo "new advisor not in the current list. Adding.<br />";
					}
					$myArray 				= explode('&',$excludedAdvisors);
					$newExcluded			= array_unique($myArray);
					foreach($newExcluded as $thisKey => $thisValue) {
						if ($thisValue == '') {
							unset($newExcluded[$thisKey]);
						}
					}
					$newExcluded[]			= $newExcludedAdvisor;
					$newExcludedAdvisorList	= implode('&',$newExcluded);
					if ($doDebug) {
						echo "returning<br />";
					}
					return $newExcludedAdvisorList;
					
					}
//				}
			}
		} elseif ($direction == 'delete') {
			// if the current list is empty, return an empty list
			if ($excludedAdvisors == '') {
				if ($doDebug) {
					echo "current list is empty. can't delete $newExcludedAdvisor<br />";
				}
				if ($doDebug) {
					echo "returning<br />";
				}
				return $excludedAdvisors;
			} else {
				// if the advisor to be deleted isn't in the list, compress and return
				if (!str_contains($excludedAdvisors,$newExcludedAdvisor)) {
					$myArray 				= explode('&',$excludedAdvisor);
					$newExcluded			= array_unique($myArray);
					$newExcludedAdvisorList	= implode('&',$newExcluded);
					return $newExcludedAdvisorList;				
				} else {			// advisor to be deleted is in the list
					if ($excludedAdvisors == $newExcludedAdvisor) {
						// empty the list and return it
						if ($doDebug) {
							echo "$newExcludedAdvisor is the only advisor in the list<br />";
						}
						$newExcludedAdvisorList 	= '';
						if ($doDebug) {
							echo "returning<br />";
						}
						return $newExcludedAdvisorList;
					} else {
						// delete the advisor, and return a compressed list
						if ($doDebug) {
							echo "deleting $newExcludedAdvisor from current list<br />";
						}
						$myArray 				= explode('&',$excludedAdvisors);
						$newExcluded			= array_unique($myArray);
						foreach($newExcluded as $thisKey => $thisValue) {
							if ($thisValue == $newExcludedAdvisor) {
								unset($newExcluded[$thisKey]);
							}
						}
						$newExcludedAdvisorList	= implode('&',$newExcluded);
						if ($doDebug) {
							echo "returning<br />";
						}
						return $newExcludedAdvisorList;	
					}
				}
			}
		}
	}
}
add_action('updateExcludedAdvisor','updateExcludedAdvisor');
