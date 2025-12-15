<?php

include 'getData.php';
include 'checkFunctions.php';
include 'displayFunctions.php';

/*
This switch statement determines which list of students, classrooms, and exclusions should be iterated through based on variables set in get request
Designed to minimize unnecessary work
$students, $classrooms, and $exclusions are the ultimate arrays that are utilized
*/
switch ($_GET['school']) { //get locations of students from roster and match the id of snipe database
    case "DE": //Dayton
		$students = $Daytonstudents;
		$classrooms = $Daytonclassrooms;
		$exclusions = $Daytonexclusions;
	break 1;
	case "EE": //Elderton
		$students = $Eldertonstudents;
		$classrooms = $Eldertonclassrooms;
		$exclusions = $Eldertonexclusions;
	break 1;
	case "SV": //Shannock
		$students = $Shannockstudents;
		$classrooms = $Shannockclassrooms;
		$exclusions = $Shannockexclusions;
	break 1;
	case "LE": //Lenape
		$students = $Lenapestudents;
		$classrooms = $Lenapeclassrooms;
		$exclusions = $Lenapeexclusions;
	break 1;
	case "WP": //Primary
		$students = $Primarystudents;
		$classrooms = $Primaryclassrooms;
		$exclusions = $Primaryexclusions;
	break 1;
	case "WI": //Intermediate
		$students = $Intermediatestudents;
		$classrooms = $Intermediateclassrooms;
		$exclusions = $Intermediateexclusions;
	break 1;
	case "AS": //Armstrong
		$students = $Armstrongstudents;
		$classrooms = $Armstrongclassrooms;
		$exclusions = $Armstrongexclusions;
	break 1;
	case "WS": //West Shamokin
		$students = $WSstudents;
		$classrooms = $WSclassrooms;
		$exclusions = $WSexclusions;
	break 1;
	case "CY": //Cyber, likely never used
		$students = $Cyberstudents;
		$classrooms = [];
	break 1;
	default: //defaults to all students
		$students = $Allstudents;
		$classrooms = $Allclassrooms;
		$exclusions = $Allexclusions;
	break 1;		

}

/*
This array is a cummulative list of all tasks that need to be completed
Each task also get assigned a priority level. This priority level is designed so that more important tasks can be prioritized during display
*/
$allAssignments = [];


/*
This array is to determine whether or not the Chromebook/student being iterated over is part of the exclusions.txt list or not and to track it if so
*/
$exclusion = [];


/*
This function is the main loop of the program. It is in charge of iterating through every student
from the Skyward csv export and then any other students and/or Chromebooks
As each student is iterated over, many functions will be ran to check for specific issues and then assign them to be fixed

Some functions are placed inside if-else blocks. This is because some of the checks would be redundant if they were all run
As an example, devices with no location set do not also need to be checked if their location is set to the proper school, as this is obviously false

If we ever update Snipe IT and it has a much different database structure, the long MySQL query may need to be changed
*/
function findAssignments(){
	global $mysqli; //mysql object, comes from getData.php
	global $students; //list of students compiled from Skyward csv, assigned at top of this file
	global $classrooms; //list of all classrooms that have Chromebooks assigned, assigned at top of this file
	global $exclusions; //list of all exclusions
	global $exclusion; //determines whether or not the current Chromebook/student is excluded
	global $gResult; //stores queried Chromebooks/Users to reduce API calls
	

	//Iterate through Skyward csv to find errors in Chromebook Assignments
	foreach($students as $student){
		//queries the inventory database
		$result = $mysqli -> query("select assets.serial, assets.rtd_location_id, assets.status_id, models.name as modelName, locations.name as locationName, status_labels.name as statusName from assets inner join users on assets.assigned_to = users.id inner join models on assets.model_id = models.id inner join status_labels on assets.status_id = status_labels.id inner join locations on assets.rtd_location_id = locations.id where users.username = '". $student['id'] ."' and assets.deleted_at is null;");
		
		//determine how many rows were in the response
		$num_rows = $result -> num_rows;
		
		//convert MySQL output into associative array
		$row = $result -> fetch_assoc();
		
		//checks to see if the queried student is excluded
		foreach($exclusions as $e) {
			if(($student['id'] == $e['exclusion']) or (isset($row['serial']) and $row['serial'] == $e['exclusion'])){
				$exclusion = $e;
			}
		}
		
		if($num_rows > 1){ //assignments for more than 1 CB per student
			
		} else if($num_rows == 0){ //assignments for no CB found
			checkHasAssignedStudent($student, $exclusion);
			
		} else { //assignments for 1 CB found
			checkAssignedDeprovisioned($row, $student, $exclusion);
			checkAssignedReadyToDeploy($row, $student, $exclusion);
			checkAssignedWrongLocation($row, $student, $exclusion);
		}
		//free up variables
		$result -> free_result();
		$exclusion = [];
		$gResult = 0;
	}
	
	//Iterate through all classroom-assigned Chromebooks
	foreach($classrooms as $classroom){ //through each classroom in the list
		for($x = 0; $x < $classroom["numOfCBs"]; $x++){ //through each Chromebook in the classroom
			$tempNum = (($x+1 < 10)?("0".$x+1):($x+1)); //if x < 10, append leading 0 to digit
			$result = $mysqli -> query("select assets.serial, assets.asset_tag, assets.rtd_location_id, assets.status_id, models.name as modelName, locations.name as locationName, status_labels.name as statusName from assets inner join models on assets.model_id = models.id inner join status_labels on assets.status_id = status_labels.id inner join locations on assets.rtd_location_id = locations.id where assets.asset_tag = '". $classroom["room"]."-".$tempNum ."' and assets.deleted_at is null;");
			
			//determine how many rows were in the response
			$num_rows = $result -> num_rows;
		
			//convert MySQL output into associative array
			$row = $result -> fetch_assoc();
			
			//checks to see if the queried student is excluded
			foreach($exclusions as $e){
				if((($classroom["room"]."-".$tempNum) == $e['exclusion']) or (isset($row['serial']) and $row['serial'] == $e['exclusion'])){
					$exclusion = $e;
				}
			}

			if($num_rows > 1){ //assignments for more than 1 CB with asset tag
				//likely should not occur, as asset tags must be unique
			} else if($num_rows == 0){ //assignments for no CB found
				checkIfAssignedChromebook($classroom["room"]."-".$tempNum, $exclusion);
			
			} else { //assignments for 1 CB found
				checkClassroomDeprovisioned($row, $exclusion);
				checkClassroomReadyToDeploy($row, $exclusion);
				checkClassroomWrongLocation($row, $exclusion);
				checkClassroomNoLocation($row, $exclusion);
			}
			//clear up variables
			$result -> free_result();
			$exclusion = [];
			$gResult = 0;
		}
	}
	
	//finally, call displayAssignments to continue logic
	displayAssignments();
}

/*
Iterates through the $allAssignments array and displays the content with drawChromebookTable
Core logic is a switch statement depending on every check function
For every check function, there must be a case in this function for the assignment's "type"
*/
function displayAssignments(){
	global $allAssignments;
	
	//sorts assignments by priority
	$priorityCol = array_column($allAssignments, "priority");
	array_multisort($priorityCol, SORT_DESC, $allAssignments);

	//prints out amount of assignments
	echo"<h3>".count(array_filter($allAssignments, fn($item) => $item['priority'] != -1))." Tasks Remaining</h3>";
	
	foreach($allAssignments as $assignment){
		echo "<details open>";
		echo "<summary>Task #".(array_search($assignment, $allAssignments)+1)."</summary>";
		echo "<div class='assignment'>";
		switch ($assignment['type']) {
			case 'noAssigned':
				displayHasAssignedStudent($assignment);
			break 1;
			case 'noClassroomCB':
				displayIfAssignedChromebook($assignment);
			break 1;
			case 'assignedDeprovisioned':
				displayAssignedDeprovisioned($assignment);
			break 1;
			case 'assignedReadyToDeploy':
				displayAssignedReadyToDeploy($assignment);
			break 1;
			case 'assignedWrongLocation':
				displayAssignedWrongLocation($assignment);
			break 1;
			case 'classroomDeprovisioned':
				displayClassroomDeprovisioned($assignment);
			break 1;
			case 'classroomReadyToDeploy':
				displayClassroomReadyToDeploy($assignment);
			break 1;
			case 'classroomWrongLocation':
				displayClassroomWrongLocation($assignment);
			break 1;
			case 'classroomNoLocation':
				displayClassroomNoLocation($assignment);
			break 1;
		}

		echo "</div>";
		echo"</details>";
	}
	

}

?>