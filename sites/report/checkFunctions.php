<?php
require '../../vendor/autoload.php';

/*
This file is in charge of checking inventory and all other compiled records to determine if assignments need assigned.
All of these functions are only run within the "findAssignments" function in reportFunctions.php
*/


/*
Helper function that makes an API call to Google Cloud
Designed to only be run once per Chromebook
The first time a check function needs to use the results of a Google Cloud query, this function is called
This function stores the results of the query in a global variable that is then referred to by other check functions on the same Chromebook
The global variable is then wiped for the next Chromebook
*/
function getGAdminChromebook($serial){
	global $gResult; //where results from Google API call will be stored
	global $client; //gAdmin client
	global $service; //gAdmin client stuff

	//create array specifying api call parameters
	$optParams = array(
		'projection' => 'BASIC',
		'query' => 'id:' . $serial,
		'maxResults' => 1
	);

	//make api call with the directory object
	$results = $service->chromeosdevices->listChromeosdevices('my_customer', $optParams); 
	$devices = $results->getChromeosdevices();
	//if CB isn't found
	if (empty($devices)) {
		$gResult = 0;
		return;
	}
	//store results
	$gResult = $devices[0];
}
/*
Checks to make sure student doesn't have more than one Chromebook assigned to them in Snipe IT
Because this is only called when multiple Chromebooks are already found, this function doesn't need any logic
Priority 5

If a mysql query for a student's 99# returns multiple rows (meaning multiple Chromebooks found), create a task
*/
function checkAssignedMultiple($student, $exclusion){
	global $allAssignments;
	$allAssignments[] = array(
		"type" => "multipleAssigned",
		"id" => $student['id'],
		"firstName" => $student['firstName'],
		"lastName" => $student['lastName'],
		"grade" => $student['grade'],
		"priority" => 5
	);
	if($exclusion != []){ //add extra data if this assignment is excluded
		$allAssignments[count($allAssignments) - 1]['priority'] = -1;
		$allAssignments[count($allAssignments) - 1]['exclusionReason'] = $exclusion['reason'];
		$allAssignments[count($allAssignments) - 1]['exclusionDate'] = $exclusion['date'];
	}
}
/*
Checks to make sure student has a Chromebook assigned to them in Snipe IT
Priority 5

If a student has no Chromebook assigned to them and should (they're older than 4th grade), create a task
*/
function checkHasAssignedStudent($student, $exclusion){
	global $allAssignments;
	if($student['grade'] >= 5){
		$allAssignments[] = array(
			"type" => "noAssigned",
			"id" => $student['id'],
			"firstName" => $student['firstName'],
			"lastName" => $student['lastName'],
			"grade" => $student['grade'],
			"priority" => 5
		);
		if($exclusion != []){ //add extra data if this assignment is excluded
			$allAssignments[count($allAssignments) - 1]['priority'] = -1;
			$allAssignments[count($allAssignments) - 1]['exclusionReason'] = $exclusion['reason'];
			$allAssignments[count($allAssignments) - 1]['exclusionDate'] = $exclusion['date'];
		}
	}
}

/*
Checks if the Chromebook assigned to a student is deprovisioned in Snipe It
Then queries Google Admin to determine if CB is also deprovisioned there.
Priority 4

If the the student's Chromebook's statusName = "Deprovisioned", make a task
*/
function checkAssignedDeprovisioned($row, $student, $exclusion){
	global $allAssignments;
	global $gResult;
	global $apiConnection;
	
	if($row['statusName'] == "Deprovisioned"){//if deprovisioned in inventory
		$allAssignments[] = array(
			"type" => "assignedDeprovisioned",
			"id" => $student['id'],
			"firstName" => $student['firstName'],
			"lastName" => $student['lastName'],
			"grade" => $student['grade'],
			"serial" => $row['serial'],
			"statusName" => $row['statusName'],
			"priority" => 4
		);
		if($gResult != 0 and $apiConnection == true){ //if Chromebook has already been queried for
			if($gResult->getStatus() != "ACTIVE"){
				$status = "deprovisioned";
			} else {
				$status = "provisioned";
			}
			$allAssignments[count($allAssignments) - 1]['gAdminStatus'] = $status;
		} else if($gResult == 0 and $apiConnection == true) {//if Chromebook hasn't been queried for
			getGAdminChromebook($row['serial']);
			if($gResult->getStatus() != "ACTIVE"){
				$status = "deprovisioned";
			} else {
				$status = "provisioned";
			}
			$allAssignments[count($allAssignments) - 1]['gAdminStatus'] = $status;
		}
		if($exclusion != []){ //add extra data if this assignment is excluded
			$allAssignments[count($allAssignments) - 1]['priority'] = -1;
			$allAssignments[count($allAssignments) - 1]['exclusionReason'] = $exclusion['reason'];
			$allAssignments[count($allAssignments) - 1]['exclusionDate'] = $exclusion['date'];
		}
	}
	
}

/*
Checks if the Chromebook assigned to a student is marked "Ready to Deploy" instead of Deployed in Snipe It
Priority 2

If the the student's Chromebook's statusName = "Ready to Deploy", make a task
*/
function checkAssignedReadyToDeploy($row, $student, $exclusion){
	global $allAssignments;
	
	if($row['statusName'] == "Ready to Deploy"){//if ready to deploy in inventory
		$allAssignments[] = array(
			"type" => "assignedReadyToDeploy",
			"id" => $student['id'],
			"firstName" => $student['firstName'],
			"lastName" => $student['lastName'],
			"grade" => $student['grade'],
			"serial" => $row['serial'],
			"statusName" => $row['statusName'],
			"priority" => 2
		);
		if($exclusion != []){ //add extra data if this assignment is excluded
			$allAssignments[count($allAssignments) - 1]['priority'] = -1;
			$allAssignments[count($allAssignments) - 1]['exclusionReason'] = $exclusion['reason'];
			$allAssignments[count($allAssignments) - 1]['exclusionDate'] = $exclusion['date'];
		}
	}
	
}

/*
Checks if the Chromebook assigned to a student is marked "Broken" instead of Deployed in Snipe It
Priority 3

If the the student's Chromebook's statusName = "Broken", make a task
*/
function checkAssignedBroken($row, $student, $exclusion){
	global $allAssignments;
	
	if($row['statusName'] == "Broken"){//if Broken in inventory
		$allAssignments[] = array(
			"type" => "assignedBroken",
			"id" => $student['id'],
			"firstName" => $student['firstName'],
			"lastName" => $student['lastName'],
			"grade" => $student['grade'],
			"serial" => $row['serial'],
			"statusName" => $row['statusName'],
			"priority" => 3
		);
		if($exclusion != []){ //add extra data if this assignment is excluded
			$allAssignments[count($allAssignments) - 1]['priority'] = -1;
			$allAssignments[count($allAssignments) - 1]['exclusionReason'] = $exclusion['reason'];
			$allAssignments[count($allAssignments) - 1]['exclusionDate'] = $exclusion['date'];
		}
	}
	
}

/*
Checks if the Chromebook assigned to a student is marked "Out for Repair" instead of Deployed in Snipe It
Priority 4

If the the student's Chromebook's statusName = "Out for Repair", make a task
*/
function checkAssignedOutForRepair($row, $student, $exclusion){
	global $allAssignments;
	
	if($row['statusName'] == "Out for Repair"){//if Out for Repair in inventory
		$allAssignments[] = array(
			"type" => "assignedOutForRepair",
			"id" => $student['id'],
			"firstName" => $student['firstName'],
			"lastName" => $student['lastName'],
			"grade" => $student['grade'],
			"serial" => $row['serial'],
			"statusName" => $row['statusName'],
			"priority" => 4
		);
		if($exclusion != []){ //add extra data if this assignment is excluded
			$allAssignments[count($allAssignments) - 1]['priority'] = -1;
			$allAssignments[count($allAssignments) - 1]['exclusionReason'] = $exclusion['reason'];
			$allAssignments[count($allAssignments) - 1]['exclusionDate'] = $exclusion['date'];
		}
	}
	
}

/*
Checks if the Chromebook assigned to a student is marked under the wrong location in Snipe IT
Priority 3

If the the student's Chromebook's rtd_location_id != null and != the proper location, create a task
*/
function checkAssignedWrongLocation($row, $student, $exclusion){
	global $allAssignments;
	
	if($row['rtd_location_id'] != null and $row['rtd_location_id'] != $student['location']){//if location is set, but is set to wrong location
		$allAssignments[] = array(
			"type" => "assignedWrongLocation",
			"id" => $student['id'],
			"firstName" => $student['firstName'],
			"lastName" => $student['lastName'],
			"grade" => $student['grade'],
			"serial" => $row['serial'],
			"locationName" => $row['locationName'],
			"priority" => 3
		);
		if($exclusion != []){ //add extra data if this assignment is excluded
			$allAssignments[count($allAssignments) - 1]['priority'] = -1;
			$allAssignments[count($allAssignments) - 1]['exclusionReason'] = $exclusion['reason'];
			$allAssignments[count($allAssignments) - 1]['exclusionDate'] = $exclusion['date'];
		}
	}
	
}

/*
Checks if a Chromebook assigned to a student has no location in Snipe IT
Priority 3

If the the student's Chromebook's rtd_location_id is null, create a task
*/
function checkAssignedNoLocation($row, $student, $exclusion){
	global $allAssignments;
	
	if($row['rtd_location_id'] == false or $row['rtd_location_id'] == '' or $row['rtd_location_id'] == 0){//if location is not set
		$allAssignments[] = array(
			"type" => "assignedNoLocation",
			"id" => $student['id'],
			"firstName" => $student['firstName'],
			"lastName" => $student['lastName'],
			"grade" => $student['grade'],
			"serial" => $row['serial'],
			"priority" => 3
		);
		if($exclusion != []){ //add extra data if this assignment is excluded
			$allAssignments[count($allAssignments) - 1]['priority'] = -1;
			$allAssignments[count($allAssignments) - 1]['exclusionReason'] = $exclusion['reason'];
			$allAssignments[count($allAssignments) - 1]['exclusionDate'] = $exclusion['date'];
		}
	}
	
}

/*
Checks to make sure that a Chromebook assigned to a room exists in Snipe it
Priority 5

Because this function is only called when no rows are returned via a database query, it doesn't require any logic
*/
function checkIfAssignedChromebook($assetTag, $exclusion){
	global $allAssignments;
	$allAssignments[] = array(
		"type" => "noClassroomCB",
		"assetTag" => $assetTag,
		"priority" => 5
	);
	if($exclusion != []){ //add extra data if this assignment is excluded
		$allAssignments[count($allAssignments) - 1]['priority'] = -1;
		$allAssignments[count($allAssignments) - 1]['exclusionReason'] = $exclusion['reason'];
		$allAssignments[count($allAssignments) - 1]['exclusionDate'] = $exclusion['date'];
	}
}

/*
Checks if a Chromebook assigned to a classroom is marked as Deprovisioned on Snipe IT
Then, checks Google Admin to determine if device is deprovisioned there
Priority 4

If the the student's Chromebook's statusName = "Deprovisioned", make a task
*/
function checkClassroomDeprovisioned($row, $exclusion){
	global $allAssignments;
	global $gResult;
	global $apiConnection;
	
	if($row['statusName'] == "Deprovisioned"){//if deprovisioned in inventory
		$allAssignments[] = array(
			"type" => "classroomDeprovisioned",
			"assetTag" => $row['asset_tag'],
			"serial" => $row['serial'],
			"locationName" => $row['locationName'],
			"statusName" => $row['statusName'],
			"priority" => 4
		);
		if($gResult != 0 and $apiConnection == true){ //if Chromebook has already been queried for
			if($gResult->getStatus() != "ACTIVE"){
				$status = "deprovisioned";
			} else {
				$status = "provisioned";
			}
			$allAssignments[count($allAssignments) - 1]['gAdminStatus'] = $status;
		} else if($gResult == 0 and $apiConnection == true) {//if Chromebook hasn't been queried for
			getGAdminChromebook($row['serial']);
			if($gResult->getStatus() != "ACTIVE"){
				$status = "deprovisioned";
			} else {
				$status = "provisioned";
			}
			$allAssignments[count($allAssignments) - 1]['gAdminStatus'] = $status;
		}
		if($exclusion != []){ //add extra data if this assignment is excluded
			$allAssignments[count($allAssignments) - 1]['priority'] = -1;
			$allAssignments[count($allAssignments) - 1]['exclusionReason'] = $exclusion['reason'];
			$allAssignments[count($allAssignments) - 1]['exclusionDate'] = $exclusion['date'];
		}
	}
}

/*
Checks if a Chromebook assigned to a classroom is marked as Ready to Deploy on Snipe IT
Priority 2

If the the student's Chromebook's statusName = "Ready to Deploy", make a task
*/
function checkClassroomReadyToDeploy($row, $exclusion){
	global $allAssignments;
	
	if($row['statusName'] == "Ready to Deploy"){//if ready to deploy in inventory
		$allAssignments[] = array(
			"type" => "classroomReadyToDeploy",
			"assetTag" => $row['asset_tag'],
			"serial" => $row['serial'],
			"locationName" => $row['locationName'],
			"statusName" => $row['statusName'],
			"priority" => 2
		);
		if($exclusion != []){ //add extra data if this assignment is excluded
			$allAssignments[count($allAssignments) - 1]['priority'] = -1;
			$allAssignments[count($allAssignments) - 1]['exclusionReason'] = $exclusion['reason'];
			$allAssignments[count($allAssignments) - 1]['exclusionDate'] = $exclusion['date'];
		}
	}
}

/*
Checks if a Chromebook assigned to a classroom is marked as Broken on Snipe IT
Priority 3

If the the student's Chromebook's statusName = "Broken", make a task
*/
function checkClassroomBroken($row, $exclusion){
	global $allAssignments;
	
	if($row['statusName'] == "Broken"){//if ready to deploy in inventory
		$allAssignments[] = array(
			"type" => "classroomBroken",
			"assetTag" => $row['asset_tag'],
			"serial" => $row['serial'],
			"locationName" => $row['locationName'],
			"statusName" => $row['statusName'],
			"priority" => 3
		);
		if($exclusion != []){ //add extra data if this assignment is excluded
			$allAssignments[count($allAssignments) - 1]['priority'] = -1;
			$allAssignments[count($allAssignments) - 1]['exclusionReason'] = $exclusion['reason'];
			$allAssignments[count($allAssignments) - 1]['exclusionDate'] = $exclusion['date'];
		}
	}
}

/*
Checks if a Chromebook assigned to a classroom is marked as Out for Repair on Snipe IT
Priority 4

If the the student's Chromebook's statusName = "Out for Repair", make a task
*/
function checkClassroomOutForRepair($row, $exclusion){
	global $allAssignments;
	
	if($row['statusName'] == "Out for Repair"){//if ready to deploy in inventory
		$allAssignments[] = array(
			"type" => "classroomOutForRepair",
			"assetTag" => $row['asset_tag'],
			"serial" => $row['serial'],
			"locationName" => $row['locationName'],
			"statusName" => $row['statusName'],
			"priority" => 2
		);
		if($exclusion != []){ //add extra data if this assignment is excluded
			$allAssignments[count($allAssignments) - 1]['priority'] = -1;
			$allAssignments[count($allAssignments) - 1]['exclusionReason'] = $exclusion['reason'];
			$allAssignments[count($allAssignments) - 1]['exclusionDate'] = $exclusion['date'];
		}
	}
}

/*
Checks if a Chromebook assigned to a classroom has the correct location in Snipe IT
Is done stupidly here because I need to translate Snipe IT's rtd_location_id into the school codes
Priority 3

If the the student's Chromebook's rtd_location_id is not null and does not equal the proper value, create a task
*/
function checkClassroomWrongLocation($row, $exclusion){
	global $allAssignments;
	
	//look at first characters of asset tag to determine what proper location should be
	switch(substr($row['asset_tag'], 0, 3)){ //looks at the first 3 characters of the asset tag to determine location. Set to 3 instead of 2 because Primary is WHP instead of WP for some reason
		case "DE ":
			$correctLocation = 5;
			break 1;
		case "EE ":
			$correctLocation = 7;
			break 1;
		case "SV ":
			$correctLocation = 9;
			break 1;
		case "LE ":
			$correctLocation = 8;
			break 1;
		case "WHP":
			$correctLocation = 2;
			break 1;
		case "WI ":
			$correctLocation = 4;
			break 1;
		case "AS ": //should likely never occur
			$correctLocation = 3;
			break 1;
		case "WS ": //should likely never occur
			$correctLocation = 6;
			break 1;
		default: //should likely never occur
			$correctLocation = -1;
			break 1;
		}
		
	if($row['rtd_location_id'] != null and $row['rtd_location_id'] != $correctLocation){//if location is set, but is set to wrong location
		$allAssignments[] = array(
			"type" => "classroomWrongLocation",
			"assetTag" => $row['asset_tag'],
			"serial" => $row['serial'],
			"statusName" => $row['statusName'],
			"locationName" => $row['locationName'],
			"priority" => 3
		);
		if($exclusion != []){ //add extra data if this assignment is excluded
			$allAssignments[count($allAssignments) - 1]['priority'] = -1;
			$allAssignments[count($allAssignments) - 1]['exclusionReason'] = $exclusion['reason'];
			$allAssignments[count($allAssignments) - 1]['exclusionDate'] = $exclusion['date'];
		}
	}
}

/*
Checks if a Chromebook assigned to a classroom has no location in Snipe IT
Priority 3

If the the Chromebook's rtd_location_id is null, create a task
*/
function checkClassroomNoLocation($row, $exclusion){
	global $allAssignments;
	
	if(isset($row['rtd_location_id']) == false or $row['rtd_location_id'] == '' or $row['rtd_location_id'] == 0){//if location is not set
		$allAssignments[] = array(
			"type" => "classroomNoLocation",
			"assetTag" => $row['asset_tag'],
			"serial" => $row['serial'],
			"statusName" => $row['statusName'],
			"priority" => 3
		);
		if($exclusion != []){ //add extra data if this assignment is excluded
			$allAssignments[count($allAssignments) - 1]['priority'] = -1;
			$allAssignments[count($allAssignments) - 1]['exclusionReason'] = $exclusion['reason'];
			$allAssignments[count($allAssignments) - 1]['exclusionDate'] = $exclusion['date'];
		}
	}
}
?>