<?php

include 'getData.php';


/*
This switch statement determines which list of students and classrooms should be iterated through based on variables set in get request
Designed to minimize unnecessary work
$students and $classrooms are the ultimate arrays that are utilized
*/
switch ($_GET['school']) { //get locations of students from roster and match the id of snipe database
    case "DE": //Dayton
		$students = $Daytonstudents;
		$classrooms = $Daytonclassrooms;
	break 1;
	case "EE": //Elderton
		$students = $Eldertonstudents;
		$classrooms = $Eldertonclassrooms;
	break 1;
	case "SV": //Shannock
		$students = $Shannockstudents;
		$classrooms = $Shannockclassrooms;
	break 1;
	case "LE": //Lenape
		$students = $Lenapestudents;
		$classrooms = $Lenapeclassrooms;
	break 1;
	case "WP": //Primary
		$students = $Primarystudents;
		$classrooms = $Primaryclassrooms;
	break 1;
	case "WI": //Intermediate
		$students = $Intermediatestudents;
		$classrooms = $Intermediateclassrooms;
	break 1;
	case "AS": //Armstrong
		$students = $Armstrongstudents;
		$classrooms = $Armstrongclassrooms;
	break 1;
	case "WS": //West Shamokin
		$students = $WSstudents;
		$classrooms = $WSclassrooms;
	break 1;
	case "CY": //Cyber, likely never used
		$students = $Cyberstudents;
		$classrooms = [];
	break 1;
	default: //defaults to all students
		$students = $Allstudents;
		$classrooms = $Allclassrooms;
	break 1;		

}

/*
This array is a cummulative list of all tasks that need to be completed
Each task also get assigned a priority level. This priority level is designed so that more important tasks can be prioritized during display
*/
$allAssignments = [];
/*
Draws the table that appears in each task.
$results - a very, very specifically formated 2D array that gives the instructions necessary to represent the table
Designed to be a ones-size-fits-all function

$result[0] = an indexed array that contains header information for the table. Is popped from array after use
Every other element besides the last one in $results is an indexed array with 2 values. $results[x][0] is the value to be printed and $results[x][1] tells whether the value is correct or incorrect (should be green or red)

Finally, $results[-1] is always going to be used to create a link to Snipe IT
$results[-1][0] is the string that will be put into the Link
$results[-1][1] tells whether a Chromebook or a Snipe IT User is being searched for (either "users" or "hardware")
*/
function drawChromebookTable($results){
	global $snipe_url;
	
	echo "<table border='1'>";
	$counter = 0;
	foreach($results as $row){
		if($counter == 0){//draw header row
			echo "<tr>";
			foreach($row as $header){
				echo "<td>".$header."</td>";
			}
			echo "</tr>";
			echo "<tr>";
		} else if($counter != count($results) - 1) {//draw link to Snipe IT
			echo "<td class = '". $row[1] ."'>";
			echo $row[0];
			echo "</td>";
		} else if ($counter == count($results) - 1) {//everything else
			echo "<td><a href='" . $snipe_url . "/".$row[1]."?page=1&size=20&search=" . $row[0] . "' target = '_blank'>Link</a></td>";
			echo "</tr>";
		}
		$counter++;
	}
	echo"</table>";
}

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
	
	//Iterate through Skyward csv to find errors in Chromebook Assignments
	foreach($students as $student){
		$result = $mysqli -> query("select assets.serial, assets.rtd_location_id, assets.status_id, models.name as modelName, locations.name as locationName, status_labels.name as statusName from assets inner join users on assets.assigned_to = users.id inner join models on assets.model_id = models.id inner join status_labels on assets.status_id = status_labels.id inner join locations on assets.rtd_location_id = locations.id where users.username = '". $student['id'] ."' and assets.deleted_at is null;");
		if($result -> num_rows > 1){ //assignments for more than 1 CB per student
			
		} else if($result -> num_rows == 0){ //assignments for no CB found
			checkHasAssignedStudent($student);
			
		} else { //assignments for 1 CB found
			checkAssignedDeprovisioned($result, $student);
		}
		$result -> free_result();
	}
	
	//Iterate through all classroom-assigned Chromebooks
	foreach($classrooms as $classroom){ //through each classroom in the list
		for($x = 0; $x < $classroom["numOfCBs"]; $x++){ //through each Chromebook in the classroom
			$tempNum = (($x+1 < 10)?("0".$x+1):($x+1)); //if x < 10, append leading 0 to digit
			$result = $mysqli -> query("select assets.serial, assets.rtd_location_id, assets.status_id, models.name as modelName, locations.name as locationName, status_labels.name as statusName from assets inner join models on assets.model_id = models.id inner join status_labels on assets.status_id = status_labels.id inner join locations on assets.rtd_location_id = locations.id where assets.asset_tag = '". $classroom["room"]."-".$tempNum ."' and assets.deleted_at is null;");
			if($result -> num_rows > 1){ //assignments for more than 1 CB with asset tag
				//likely should not occur, as asset tags must be unique
			} else if($result -> num_rows == 0){ //assignments for no CB found
				checkIfAssignedChromebook($classroom["room"]."-".$tempNum);
			
			} else { //assignments for 1 CB found
			
			}
		$result -> free_result();
		}
	}
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

	
	foreach($allAssignments as $assignment){
		echo "<div class='assignment'>";
		switch ($assignment['type']) {
			case 'noAssigned':
				$display = array(
					array('Student ID', 'First Name', 'Last Name', 'Grade', 'Chromebook', 'Link'),
					array($assignment['id'], "good"),
					array($assignment['firstName'], "good"),
					array($assignment['lastName'], "good"),
					array($assignment['grade'], "good"),
					array("Not Found", "bad"),
					array($assignment['id'], "users")
				);
				echo "<h2>Student has No Chromebook Assigned</h2>";
				drawChromebookTable($display);
				echo "<p>This student is listed on Skyward as attending your specific school and does not have a Chromebook assigned to them on Snipe IT. This is likely because they are a new/moving student, there was an issue with filing an insurance claim/sending an invoice, or tech had an issue over the summer.</p>";
				echo "<p>Please check your records, locate the student (if necessary), and contact tech (if necessary) to determine the status of this student's Chromebook. Then, update Snipe IT so the student's Chromebook is recorded correctly. <strong>Please also make sure that this student's location is set correctly on Snipe IT.</strong></p>";
				echo "<p>If you believe this student is recorded on Skyward in error (has left the district, set to wrong school, etc.), please contact tech.</p>";
			break 1;
			case 'noClassroomCB':
				$display = array(
					array('Asset Tag', 'Serial Number', 'Link'),
					array($assignment['assetTag'], "bad"),
					array("Not Found", "bad"),
					array($assignment['assetTag'], "hardware")
				);
				echo "<h2>Classroom Chromebook is Missing</h2>";
				drawchromebookTable($display);
				echo "<p>This Chromebook was not found in Snipe IT when searching by Asset Tag. This could be because the Chromebook was removed from the classroom and not replaced, or a record keeping error on Snipe IT.</p>";
				echo "<p>If this Chromebook is not in the classroom, please make sure it is accounted for. TEMP REPLACE ME</p>";
				echo "<p>If this Chromebook is present in the classroom, check its asset tag (by searching via serial number) to ensure that there are no typos in the asset tag.</p>";
			break 1;
			case 'assignedDeprovisioned':
				$display = array(
					array('Student ID', 'First Name', 'Last Name', 'Grade', 'Serial Number', 'Status', 'Link'),
					array($assignment['id'], "good"),
					array($assignment['firstName'], "good"),
					array($assignment['lastName'], "good"),
					array($assignment['grade'], "good"),
					array($assignment['serial'], "good"),
					array($assignment['statusName'], "bad"),
					array($assignment['serial'], "hardware")
				);
				echo "<h2>Student Assigned Deprovisioned Chromebook (ADD GOOGLE SUPPORT)</h2>";
				drawchromebookTable($display);
				echo "<p>This student's Chromebook is marked as Deprovisioned in Snipe IT. <strong>ADD GADMIN CHECK HERE</strong></p>";
				echo "<p>If this Chromebook is deprovisioned because it is broken, it should be unassigned from them in Snipe IT and proper replacement protocol should be followed.</p>";
				echo "<p>If this Chromebook is not broken and it deprovisioned on accident, reenroll it and update it on Snipe IT.</p>";
				echo "<p>If this Chromebook is not broken and not deprovisioned via Google Admin, mark it as Deployed on Snipe IT.</p>";
			break 1;
		}

		echo "</div>";
	}
	

}
/*
Checks to make sure student has a Chromebook assigned to them in Snipe IT
Priority 5

If a student has no Chromebook assigned to them and should (they're older than 4th grade), create a task
*/
function checkHasAssignedStudent($student){
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
	}
}

/*
Checks if the Chromebook assigned to a student is deprovisioned in Snipe Iterate
Priority 4
TODO - Add Google Admin support

If the the student's Chromebook's statusName = "Deprovisioned", make a task
*/
function checkAssignedDeprovisioned($result, $student){
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
	}
}
/*
Checks to make sure that a Chromebook assigned to a room exists in Snipe it
Priority 5

Because this function is only called when no rows are returned via a database query, it doesn't require any logic
*/
function checkIfAssignedChromebook($assetTag){
	global $allAssignments;
	$allAssignments[] = array(
		"type" => "noClassroomCB",
		"assetTag" => $assetTag,
		"priority" => 5
	);
}
?>