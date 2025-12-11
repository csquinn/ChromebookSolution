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
	echo "<h2>Student Assigned Deprovisioned Chromebook (ADD GOOGLE SUPPORT)</h2>";
	drawchromebookTable($display);
	echo "<p>This student's Chromebook is marked as Deprovisioned in Snipe IT. <strong>ADD GADMIN CHECK HERE</strong></p>";
	echo "<p>If this Chromebook is deprovisioned because it is broken, it should be unassigned from them in Snipe IT and proper replacement protocol should be followed.</p>";
	echo "<p>If this Chromebook is not broken and it deprovisioned on accident, reenroll it and update it on Snipe IT.</p>";
	echo "<p>If this Chromebook is not broken and not deprovisioned via Google Admin, mark it as Deployed on Snipe IT.</p>";
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
?>