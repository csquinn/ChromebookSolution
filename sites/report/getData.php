<?php
/*
These two lines read a txt file that stores the MySQL password for the snipe_user MySQL account
This is used to query the MySQL database directly, rather than through SnipeIT
All MySQL queries are done via the $mysqli object which requires these credentials
This is notably NOT an account that exists on the frontend of inventory
*/
$db_password = file_get_contents("../../user_variables/snipe_mysql_password.txt");
$db_password = str_replace(array("\r", "\n"), '', $db_password);

/*
These two lines read a txt file that stores the URL used to access SnipeIT
This is used to link to relevant records on SnipeIT that are referenced by ChromebookSolution
*/
$snipe_url = file_get_contents("../../user_variables/snipe_url.txt");
$snipe_url = str_replace(array("\r", "\n"), '', $snipe_url);

//Create mysqli connection
$mysqli = new mysqli("localhost","snipe_user",$db_password,"snipeit");

// Check connection
if ($mysqli -> connect_errno) {
  echo "Failed to connect to MySQL: " . $mysqli -> connect_error;
  exit();
}

/*
The following while loop iterates through a csv export from Skyward and reads it into various nested arrays.
If the format of Skyward csv exports ever change, this code will need to be modified to read the data correctly into the appropriate arrays
Each school has its own array, but students are also added to a master array.
The outermost array (the students array) is an indexed array, and each student is stored as an associative array within the indexed array

The current format of the skyward csv export is as follows
Location ID, First Name, Middle Initial, Last Name, Grade, Location ID (again), School 

The Location IDs found on the Skyward export are as follows
005 - Dayton, 013 - Elderton, 026 - Shannock, 016 - Lenape, 028 - West Hills Primary, 022 - West Hills Intermediate, 032 - Armstrong High School, 027 - West Shamokin, 040 - Cyber, 700 - Also Cyber

ChromebookSolutions has no use for Skyward's location codes for the schools. As such, the "location" key has the Snipe IT location code set as its value instead.
The Location IDs found in Snipe IT are as follows
Dayton - 5, Elderton - 7, Shannock - 9, Lenape - 8, West Hills Primary - 2, West Hills Intermediate - 4, Armstrong High School - 3, West Shamokin - 6, Cyber - 12
*/
$filename = '../../sources/student.csv';
$handle = fopen($filename, 'r');
$Daytonstudents= [];
$Eldertonstudents= [];
$Shannockstudents= [];
$Lenapestudents= [];
$Primarystudents= [];
$Intermediatestudents= [];
$Armstrongstudents= [];
$WSstudents= [];
$Cyberstudents= [];
$Otherstudents= [];
$Allstudents= [];

if (!$handle) {
	die("Cannot open file: $filename");
}

while (($line = fgets($handle)) !== false) {
	$line = trim($line);      // Remove line breaks and spaces
	if(!($line == "null" or $line == " " or $line == "" or $line == null)){
		$temp = explode(',', $line);
		if(substr($temp[4], 0, 2) == "99"){
			$location = 0;
			switch ($temp[0]) { //get locations of students from roster and match the id of snipe database
    				case "005": //Dayton
    				    $location = 5;
						$Daytonstudents[] = array(
							"lastName" => $temp[3],
							"firstName" => $temp[1], 
							"id" => $temp[4], 
							"grade" => (($temp[5] == "KG")?(0):((int)$temp[5])), //For some reason, Skyward exports list KG as a grade instead of grade 0. This ternary operator sets grade = 0 instead of KG
							"location" => $location
						);//last name, first name, 99#, grade, location
					break 1;
   				 case "013": //Elderton
      				  $location = 7;
					$Eldertonstudents[] = array(
							"lastName" => $temp[3],
							"firstName" => $temp[1], 
							"id" => $temp[4], 
							"grade" => (($temp[5] == "KG")?(0):((int)$temp[5])), //For some reason, Skyward exports list KG as a grade instead of grade 0. This ternary operator sets grade = 0 instead of KG
							"location" => $location
						);//last name, first name, 99#, grade, location
					break 1;
   				 case "026": //Shannock
       				  $location = 9;
					$Shannockstudents[] = array(
							"lastName" => $temp[3],
							"firstName" => $temp[1], 
							"id" => $temp[4], 
							"grade" => (($temp[5] == "KG")?(0):((int)$temp[5])), //For some reason, Skyward exports list KG as a grade instead of grade 0. This ternary operator sets grade = 0 instead of KG
							"location" => $location
						);//last name, first name, 99#, grade, location
					break 1;
				case "016": //Lenape
					$location = 8;
					$Lenapestudents[] = array(
							"lastName" => $temp[3],
							"firstName" => $temp[1], 
							"id" => $temp[4], 
							"grade" => (($temp[5] == "KG")?(0):((int)$temp[5])), //For some reason, Skyward exports list KG as a grade instead of grade 0. This ternary operator sets grade = 0 instead of KG
							"location" => $location
						);//last name, first name, 99#, grade, location
					break 1;
				case "028": //Primary
					$location = 2;
					$Primarystudents[] = array(
							"lastName" => $temp[3],
							"firstName" => $temp[1], 
							"id" => $temp[4], 
							"grade" => (($temp[5] == "KG")?(0):((int)$temp[5])), //For some reason, Skyward exports list KG as a grade instead of grade 0. This ternary operator sets grade = 0 instead of KG
							"location" => $location
						);//last name, first name, 99#, grade, location
					break 1;
				case "022": //Intermediate
					$location = 4;
					$Intermediatestudents[] = array(
							"lastName" => $temp[3],
							"firstName" => $temp[1], 
							"id" => $temp[4], 
							"grade" => (($temp[5] == "KG")?(0):((int)$temp[5])), //For some reason, Skyward exports list KG as a grade instead of grade 0. This ternary operator sets grade = 0 instead of KG
							"location" => $location
						);//last name, first name, 99#, grade, location
					break 1;
				case "032": //Armstrong
					$location = 3;
					$Armstrongstudents[] = array(
							"lastName" => $temp[3],
							"firstName" => $temp[1], 
							"id" => $temp[4], 
							"grade" => (($temp[5] == "KG")?(0):((int)$temp[5])), //For some reason, Skyward exports list KG as a grade instead of grade 0. This ternary operator sets grade = 0 instead of KG
							"location" => $location
						);//last name, first name, 99#, grade, location
					break 1;
				case "027": //WS
					$location = array(
							"lastName" => $temp[3],
							"firstName" => $temp[1], 
							"id" => $temp[4], 
							"grade" => (($temp[5] == "KG")?(0):((int)$temp[5])), //For some reason, Skyward exports list KG as a grade instead of grade 0. This ternary operator sets grade = 0 instead of KG
							"location" => $location
						);//last name, first name, 99#, grade, location
					break 1;
				case "040": //Cyber
					$location = 12;
					$Cyberstudents[] = array(
							"lastName" => $temp[3],
							"firstName" => $temp[1], 
							"id" => $temp[4], 
							"grade" => (($temp[5] == "KG")?(0):((int)$temp[5])), //For some reason, Skyward exports list KG as a grade instead of grade 0. This ternary operator sets grade = 0 instead of KG
							"location" => $location
						);//last name, first name, 99#, grade, location
					break 1;
				case "700": //also cyber I guess
					$location = 12;
					$Cyberstudents[] = array(
							"lastName" => $temp[3],
							"firstName" => $temp[1], 
							"id" => $temp[4], 
							"grade" => (($temp[5] == "KG")?(0):((int)$temp[5])), //For some reason, Skyward exports list KG as a grade instead of grade 0. This ternary operator sets grade = 0 instead of KG
							"location" => $location
						);//last name, first name, 99#, grade, location
					break 1;
				default:	//sets location to admin, will be used to mark as error
					$location = 1;
					$Otherstudents[] = array(
							"lastName" => $temp[3],
							"firstName" => $temp[1], 
							"id" => $temp[4], 
							"grade" => (($temp[5] == "KG")?(0):((int)$temp[5])), //For some reason, Skyward exports list KG as a grade instead of grade 0. This ternary operator sets grade = 0 instead of KG
							"location" => $location
						);//last name, first name, 99#, grade, location
				break 1;
			}
			$Allstudents[] = array(
				"lastName" => $temp[3],
				"firstName" => $temp[1], 
				"id" => $temp[4], 
				"grade" => (($temp[5] == "KG")?(0):((int)$temp[5])), //For some reason, Skyward exports list KG as a grade instead of grade 0. This ternary operator sets grade = 0 instead of KG
				"location" => $location
			);//last name, first name, 99#, grade, location			
		}
	}
}

/*
The following while loop iterates through a csv from Google Admin and reads it into an associative array
This data is used to reference Chromebooks' statuses (is the device being used, is it deprovisioned, are unintended parties using the device, etc)
The outermost array (the gAdminExport array) is an indexed array, and each Chromebook is stored as an associative array within the indexed array

The export utilizes the following format
deviceId,serialNumber,model,lastPolicySync,enrollmentTime,osVersion,orgUnitPath,provisionStatus,annotatedAssetId,annotatedUser,annotatedLocation,annotatedNotes,ethernetMacAddress,macAddress,lastReport,autoUpdateExpiration,meid,platformVersion,firmwareVersion,lastDeprovision,deprovisionReason,mostRecentActivity,mostRecentUser,wifiSignalStrength,volumeLevelPercent,cpuUtilizationPercent,memoryUsageByte,diskSpaceUsageByte,kioskApp,lanIpAddress,wanIpAddress,lastIpAddressUpdate,bootMode,tpmFirmwareVersion,manufacturingDate,eids,iccids
Only a few of these values are collected, as they are not all necessary for this application

For now, model information is not taken from the Google Admin CSV. This is because it would need to be translated into Snipe IT's ID system.
I cannot think of a simple, autonomous way to implement this that is worth the time it would take.
*/
$filename = '../../sources/admin.csv';
$handle2 = fopen($filename, 'r');
$gAdminExport = [];

if (!$handle2) {
	die("Cannot open file: $filename");
}

fgets($handle2); //skips header line in csv export

while (($line = fgets($handle2)) !== false) {
	$line = trim($line);      // Remove line breaks and spaces
	if(!($line == "null" or $line == " " or $line == "" or $line == null)){
		$temp = explode(',', $line);
		$gAdminExport[] = array(
			"serial" => $temp[1],
			"lastPolicySync" => $temp[3], //could be used to determine if a CB is lost to the district
			"provisionStatus" => $temp[7],
			"mostRecentUser" => substr($temp[22], 0, strpos($temp[22], "@")) //cuts off email information from @ sign and afterwards. Only care for student ID
		);
	}
}


/*
The following while loop iterates through the classrooms.csv file to determine what classrooms in the district have Chromebooks assigned as well as how many
This is applicable for 4th grade and below classrooms where Chromebooks are not individually assigned
The format of this csv is "classroom, numOfCBs"
Functions the same way as the students lists, where there are individual arrays and also a master array
Ideally, this list will be editable from the browser so that it can be changed by parties besides tech
*/
$filename = '../../sources/classrooms.csv';
$handle3 = fopen($filename, 'r');
$Daytonclassrooms= [];
$Eldertonclassrooms= [];
$Shannockclassrooms= [];
$Lenapeclassrooms= [];
$Primaryclassrooms= [];
$Intermediateclassrooms= [];
$Armstrongclassrooms= [];
$WSclassrooms= [];
$Otherclassrooms= [];
$Allclassrooms= [];

if (!$handle3) {
	die("Cannot open file: $filename");
}

while (($line = fgets($handle3)) !== false) {
	$line = trim($line);      // Remove line breaks and spaces
	if(!($line == "null" or $line == " " or $line == "" or $line == null)){
		$temp = explode(',', $line);
		switch(substr($temp[0], 0, 3)){ //looks at the first 3 characters of the name to determine location. Set to 3 instead of 2 because Primary is WHP instead of WP for some reason
			case "DE":
				$Daytonclassrooms[] = array(
					"room" => $temp[0],
					"numOfCBs" => $temp[1]
				);
				break 1;
			case "EE ":
				$Eldertonclassrooms[] = array(
					"room" => $temp[0],
					"numOfCBs" => $temp[1]
				);
				break 1;
			case "SV ":
				$Shannockclassrooms[] = array(
					"room" => $temp[0],
					"numOfCBs" => $temp[1]
				);
				break 1;
			case "LE ":
				$Lenapeclassrooms[] = array(
					"room" => $temp[0],
					"numOfCBs" => $temp[1]
				);
				break 1;
			case "WHP":
				$Primaryclassrooms[] = array(
					"room" => $temp[0],
					"numOfCBs" => $temp[1]
				);
				break 1;
			case "WI ":
				$Intermediateclassrooms[] = array(
					"room" => $temp[0],
					"numOfCBs" => $temp[1]
				);
				break 1;
			case "AS ": //should likely never occur
				$Armstrongclassrooms[] = array(
					"room" => $temp[0],
					"numOfCBs" => $temp[1]
				);
				break 1;
			case "WS ": //should likely never occur
				$WSclassrooms[] = array(
					"room" => $temp[0],
					"numOfCBs" => $temp[1]
				);
				break 1;
			default: //should likely never occur
				$Otherclassrooms[] = array(
					"room" => $temp[0],
					"numOfCBs" => $temp[1]
				);
				break 1;
		}
		$Allclassrooms[] = array(
			"room" => $temp[0],
			"numOfCBs" => $temp[1]
		);
	}
}

/*
The following code reads in exclusions from exclusions.txt
These records will be used when known assignments want to be excluded from the list of assignments.
This list of exclusions is generated from addExclusions.txt
Like the students and classrooms, exclusions are divided by schools
*/
$filename = 'exclusions.txt';
$handle4 = fopen($filename, 'r');
$Daytonexclusions= [];
$Eldertonexclusions= [];
$Shannockexclusions= [];
$Lenapeexclusions= [];
$Primaryexclusions= [];
$Intermediateexclusions= [];
$Armstrongexclusions= [];
$WSexclusions= [];
$Otherexclusions= [];
$Allexclusions= [];

if (!$handle4) {
	die("Cannot open file: $filename");
}

while (($line = fgets($handle4)) !== false) {
	$line = trim($line);      // Remove line breaks and spaces
	if(!($line == "null" or $line == " " or $line == "" or $line == null)){
		$temp = explode(',', $line);
		switch($temp[4]){ //looks at the first 3 characters of the name to determine location. Set to 3 instead of 2 because Primary is WHP instead of WP for some reason
			case "DE":
				$Daytonexclusions[] = array(
					"exclusion" => $temp[0],
					"reason" => $temp[1],
					"date" => $temp[3]
				);
				break 1;
			case "EE":
				$Eldertonexclusions[] = array(
					"exclusion" => $temp[0],
					"reason" => $temp[1],
					"date" => $temp[3]
				);
				break 1;
			case "SV":
				$Shannockexclusions[] = array(
					"exclusion" => $temp[0],
					"reason" => $temp[1],
					"date" => $temp[3]
				);
				break 1;
			case "LE":
				$Lenapeexclusions[] = array(
					"exclusion" => $temp[0],
					"reason" => $temp[1],
					"date" => $temp[3]
				);
				break 1;
			case "WP":
				$Primaryexclusions[] = array(
					"exclusion" => $temp[0],
					"reason" => $temp[1],
					"date" => $temp[3]
				);
				break 1;
			case "WI":
				$Intermediateexclusions[] = array(
					"exclusion" => $temp[0],
					"reason" => $temp[1],
					"date" => $temp[3]
				);
				break 1;
			case "AS":
				$Armstrongexclusions[] = array(
					"exclusion" => $temp[0],
					"reason" => $temp[1],
					"date" => $temp[3]
				);
				break 1;
			case "WS":
				$WSexclusions[] = array(
					"exclusion" => $temp[0],
					"reason" => $temp[1],
					"date" => $temp[3]
				);
				break 1;
			default: //should likely never occur
				$Otherexclusions[] = array(
					"exclusion" => $temp[0],
					"reason" => $temp[1],
					"date" => $temp[3]
				);
				break 1;
		}
		$Allexclusions[] = array(
			"exclusion" => $temp[0],
			"reason" => $temp[1],
			"date" => $temp[3]
		);
	}
}
?>