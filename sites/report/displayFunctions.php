<?php
/*
This file is in charge of displaying all assignments found by reportFunctions and checkFunctions
All of the functiosn within this file are only ran within the "displayAssignments" functino in reportFunctions.php
*/


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
Displays the task that was created in checkHasAssignedStudent
type of task is noAssigned, so this runs in reportFunctions.php's displayAssignments function when the switch statement hits 'noAssigned'
*/
function displayHasAssignedStudent($assignment){
	$display = array(
		array('Student ID', 'First Name', 'Last Name', 'Grade', 'Chromebook', 'Link'),
		array($assignment['id'], "good"),
		array($assignment['firstName'], "good"),
		array($assignment['lastName'], "good"),
		array($assignment['grade'], "good"),
		array("Not Found", "bad"),
		array($assignment['id'], "users")
	);
	if($assignment['priority'] == -1){ //if excluded assignment, display extra info
		echo "<h3 class='exclusion'>This Assignment is Excluded</h3>";
		echo "<p class='exclusion'>Reason: ".$assignment['exclusionReason']."</p>";
		echo "<p class='exclusion'>Date Excluded: ".$assignment['exclusionDate']."</p>";
	}
	echo "<h2>Student has No Chromebook Assigned</h2>";
	drawChromebookTable($display);
	echo "<p>This student is listed on Skyward as attending your specific school and does not have a Chromebook assigned to them on Snipe IT. This is likely because they are a new/moving student, there was an issue with filing an insurance claim/sending an invoice, or tech had an issue over the summer.</p>";
	echo "<p>Please check your records, locate the student (if necessary), and contact tech (if necessary) to determine the status of this student's Chromebook. Then, update Snipe IT so the student's Chromebook is recorded correctly. <strong>Please also make sure that this student's location is set correctly on Snipe IT.</strong></p>";
	echo "<p>If you believe this student is recorded on Skyward in error (has left the district, set to wrong school, etc.), please contact tech.</p>";
}

/*
Displays the task that was created in checkAssignedDeprovisioned
type of task is assignedDeprovisioned, so this runs in reportFunctions.php's displayAssignments function when the switch statement hits 'assignedDeprovisioned'
*/
function displayAssignedDeprovisioned($assignment){
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
	if($assignment['priority'] == -1){ //if excluded assignment, display extra info
		echo "<h3 class='exclusion'>This Assignment is Excluded</h3>";
		echo "<p class='exclusion'>Reason: ".$assignment['exclusionReason']."</p>";
		echo "<p class='exclusion'>Date Excluded: ".$assignment['exclusionDate']."</p>";
	}
	echo "<h2>Student Assigned Deprovisioned Chromebook</h2>";
	drawchromebookTable($display);
	echo "<p>This student's Chromebook is marked as Deprovisioned in Snipe IT.</p>";
	if(isset($assignment['gAdminStatus'])){
		echo "<p>This student's Chromebook is ".$assignment['gAdminStatus']." on Google Admin.</p>";
	}
	echo "<p>If this Chromebook is deprovisioned because it is broken, it should be unassigned from them in Snipe IT and proper replacement protocol should be followed <strong>REVIEW THIS</strong>.</p>";
	echo "<p>If this Chromebook is not broken and it deprovisioned on accident, reenroll it and update it on Snipe IT.</p>";
	echo "<p>If this Chromebook is not broken and not deprovisioned via Google Admin, mark it as Deployed on Snipe IT.</p>";
}

/*
Displays the task that was created in checkAssignedReadyToDeploy
type of task is assignedReadyToDeploy, so this runs in reportFunctions.php's displayAssignments function when the switch statement hits 'assignedReadyToDeploy'
*/
function displayAssignedReadyToDeploy($assignment){
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
	if($assignment['priority'] == -1){ //if excluded assignment, display extra info
		echo "<h3 class='exclusion'>This Assignment is Excluded</h3>";
		echo "<p class='exclusion'>Reason: ".$assignment['exclusionReason']."</p>";
		echo "<p class='exclusion'>Date Excluded: ".$assignment['exclusionDate']."</p>";
	}
	echo '<h2>Student Assigned "Ready to Deploy" Chromebook</h2>';
	drawchromebookTable($display);
	echo "<p>This student's Chromebook is marked as Ready to Deploy in Snipe IT.</p>";
	echo "<p>This is likely because the student's Chromebook was never marked as Deployed whenever it was assigned to them.</p>";
	echo "<p>Ensure that this student possesses the Chromebook in question, then mark the Chromebook as Deployed in Snipe IT.</p>";
}

/*
Displays the task that was created in checkAssignedWrongLocation
type of task is assignedWrongLocation, so this runs in reportFunctions.php's displayAssignments function when the switch statement hits 'assignedWrongLocation'
*/
function displayAssignedWrongLocation($assignment){
	$display = array(
		array('Student ID', 'First Name', 'Last Name', 'Grade', 'Serial Number', 'Location', 'Link'),
		array($assignment['id'], "good"),
		array($assignment['firstName'], "good"),
		array($assignment['lastName'], "good"),
		array($assignment['grade'], "good"),
		array($assignment['serial'], "good"),
		array($assignment['locationName'], "bad"),
		array($assignment['serial'], "hardware")
	);
	if($assignment['priority'] == -1){ //if excluded assignment, display extra info
		echo "<h3 class='exclusion'>This Assignment is Excluded</h3>";
		echo "<p class='exclusion'>Reason: ".$assignment['exclusionReason']."</p>";
		echo "<p class='exclusion'>Date Excluded: ".$assignment['exclusionDate']."</p>";
	}
	echo '<h2>Student Chromebook Marked with Incorrect Location</h2>';
	drawchromebookTable($display);
	echo "<p>This student's Chromebook is not set to the proper school in Snipe IT.</p>";
	echo "<p>This is likely because the student's Chromebook's location was not updated when they transitioned from 6th-7th grade, or because it wasn't updated when the student's Chromebook was assigned.</p>";
	echo "<p>Ensure that this student possesses the Chromebook in question, then update the Chromebook's location in Snipe IT.</p>";
	echo "<p><strong>If the location of the Chromebook doesn't look like it updated in Snipe IT, do not worry. Check again tommorow and it should've synced.</strong></p>";
}
/*
Displays the task that was created in checkIfAssignedChromebook
type of task is noClassroomCB, so this runs in reportFunctions.php's displayAssignments function when the switch statement hits 'noClassroomCB'
*/
function displayIfAssignedChromebook($assignment){
	$display = array(
		array('Asset Tag', 'Serial Number', 'Link'),
		array($assignment['assetTag'], "bad"),
		array("Not Found", "bad"),
		array($assignment['assetTag'], "hardware")
	);
	if($assignment['priority'] == -1){ //if excluded assignment, display extra info
		echo "<h3 class='exclusion'>This Assignment is Excluded</h3>";
		echo "<p class='exclusion'>Reason: ".$assignment['exclusionReason']."</p>";
		echo "<p class='exclusion'>Date Excluded: ".$assignment['exclusionDate']."</p>";
	}
	echo "<h2>Classroom Chromebook is Missing</h2>";
	drawchromebookTable($display);
	echo "<p>This Chromebook was not found in Snipe IT when searching by Asset Tag. This could be because the Chromebook was removed from the classroom and not replaced, or a record keeping error on Snipe IT.</p>";
	echo "<p>If this Chromebook is not in the classroom, please make sure it is accounted for. TEMP REPLACE ME</p>";
	echo "<p>If this Chromebook is present in the classroom, check its asset tag (by searching via serial number) to ensure that there are no typos in the asset tag.</p>";
}

/*
Displays the task that was created in checkClassroomDeprovisioned()
type of task is classroomDeprovisioned, so this runs in reportFunctions.php's displayAssignments function when the switch statement hits 'classroomDeprovisioned'
*/
function displayClassroomDeprovisioned($assignment){
	$display = array(
		array('Asset Tag', 'Serial Number', 'Location', 'Status', 'Link'),
		array($assignment['assetTag'], "good"),
		array($assignment['serial'], "good"),
		array($assignment['locationName'], "good"),
		array($assignment['statusName'], "bad"),
		array($assignment['assetTag'], "hardware")
	);
	if($assignment['priority'] == -1){ //if excluded assignment, display extra info
		echo "<h3 class='exclusion'>This Assignment is Excluded</h3>";
		echo "<p class='exclusion'>Reason: ".$assignment['exclusionReason']."</p>";
		echo "<p class='exclusion'>Date Excluded: ".$assignment['exclusionDate']."</p>";
	}
	echo "<h2>Classroom Chromebook is Marked as Deprovisioned</h2>";
	drawchromebookTable($display);
	echo "<p>This Chromebook is marked as Deprovisioned on Snipe IT.</p>";
	if(isset($assignment['gAdminStatus'])){
		echo "<p>This student's Chromebook is ".$assignment['gAdminStatus']." on Google Admin.</p>";
	}
	echo "<p>If this Chromebook is deprovisioned because it is broken, it follow proper replacement protocol <strong>REVIEW THIS</strong>.</p>";
	echo "<p>If this Chromebook is not broken and it deprovisioned on accident, reenroll it and update it on Snipe IT.</p>";
	echo "<p>If this Chromebook is not broken and not deprovisioned via Google Admin, mark it as Deployed on Snipe IT.</p>";
}

/*
Displays the task that was created in checkClassroomReadyToDeploy()
type of task is classroomReadyToDeploy, so this runs in reportFunctions.php's displayAssignments function when the switch statement hits 'classroomReadyToDeploy'
*/
function displayClassroomReadyToDeploy($assignment){
	$display = array(
		array('Asset Tag', 'Serial Number', 'Location', 'Status', 'Link'),
		array($assignment['assetTag'], "good"),
		array($assignment['serial'], "good"),
		array($assignment['locationName'], "good"),
		array($assignment['statusName'], "bad"),
		array($assignment['assetTag'], "hardware")
	);
	if($assignment['priority'] == -1){ //if excluded assignment, display extra info
		echo "<h3 class='exclusion'>This Assignment is Excluded</h3>";
		echo "<p class='exclusion'>Reason: ".$assignment['exclusionReason']."</p>";
		echo "<p class='exclusion'>Date Excluded: ".$assignment['exclusionDate']."</p>";
	}
	echo "<h2>Classroom Chromebook is Marked as Ready to Deploy</h2>";
	drawchromebookTable($display);
	echo "<p>This Chromebook is marked as Ready to Deploy on Snipe IT.</p>";
	echo "<p>This is likely because the Chromebook was never marked as Deployed whenever it was placed in the classroom.</p>";
	echo "<p>Ensure the Chromebook is present and undamaged in the classroom, then mark the Chromebook as Deployed in Snipe IT.</p>";
}

/*
Displays the task that was created in checkClassroomWrongLocation()
type of task is classroomWrongLocation, so this runs in reportFunctions.php's displayAssignments function when the switch statement hits 'classroomWrongLocation'
*/
function displayClassroomWrongLocation($assignment){
	$display = array(
		array('Asset Tag', 'Serial Number', 'Status', 'Location', 'Link'),
		array($assignment['assetTag'], "good"),
		array($assignment['serial'], "good"),
		array($assignment['statusName'], "good"),
		array($assignment['locationName'], "bad"),
		array($assignment['assetTag'], "hardware")
	);
	if($assignment['priority'] == -1){ //if excluded assignment, display extra info
		echo "<h3 class='exclusion'>This Assignment is Excluded</h3>";
		echo "<p class='exclusion'>Reason: ".$assignment['exclusionReason']."</p>";
		echo "<p class='exclusion'>Date Excluded: ".$assignment['exclusionDate']."</p>";
	}
	echo "<h2>Classroom Chromebook is Marked with Wrong Location</h2>";
	drawchromebookTable($display);
	echo "<p>This Chromebook is marked with the wrong location in Snipe IT.</p>";
	echo "<p>This is likely because the Chromebook's location was never updated when it was moved.</p>";
	echo "<p>Ensure the Chromebook is present and undamaged in the classroom, then update the location in Snipe IT.</p>";
	echo "<p><strong>If the location of the Chromebook doesn't look like it updated in Snipe IT, do not worry. Check again tommorow and it should've synced.</strong></p>";
}

/*
Displays the task that was created in checkClassroomNoLocation()
type of task is classroomNoLocation, so this runs in reportFunctions.php's displayAssignments function when the switch statement hits 'classroomNoLocation'
*/
function displayClassroomNoLocation($assignment){
	$display = array(
		array('Asset Tag', 'Serial Number', 'Status', 'Location', 'Link'),
		array($assignment['assetTag'], "good"),
		array($assignment['serial'], "good"),
		array($assignment['statusName'], "good"),
		array("No Location Set", "bad"),
		array($assignment['assetTag'], "hardware")
	);
	if($assignment['priority'] == -1){ //if excluded assignment, display extra info
		echo "<h3 class='exclusion'>This Assignment is Excluded</h3>";
		echo "<p class='exclusion'>Reason: ".$assignment['exclusionReason']."</p>";
		echo "<p class='exclusion'>Date Excluded: ".$assignment['exclusionDate']."</p>";
	}
	echo "<h2>Classroom Chromebook has No Location Set</h2>";
	drawchromebookTable($display);
	echo "<p>This Chromebook has no location in Snipe IT.</p>";
	echo "<p>This is likely because the Chromebook's location was not properly updated when it was moved.</p>";
	echo "<p>Ensure the Chromebook is present and undamaged in the classroom, then update the location in Snipe IT.</p>";
	echo "<p><strong>If the location of the Chromebook doesn't look like it updated in Snipe IT, do not worry. Check again tommorow and it should've synced.</strong></p>";
}
?>