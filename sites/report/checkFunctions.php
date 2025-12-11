<?php
/*
This file is in charge of checking inventory and all other compiled records to determine if assignments need assigned.
All of these functions are only run within the "findAssignments" function in reportFunctions.php
*/

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
Checks if the Chromebook assigned to a student is deprovisioned in Snipe Iterate
Priority 4
TODO - Add Google Admin support

If the the student's Chromebook's statusName = "Deprovisioned", make a task
*/
function checkAssignedDeprovisioned($result, $student, $exclusion){
	global $allAssignments;
	$row = $result -> fetch_assoc(); //convert MySQL output into associative array
	if($row['statusName'] == "Deprovisioned"){
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
?>