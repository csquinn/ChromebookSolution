<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>ChromebookSolution</title>
	<link rel = "stylesheet" href = "/sites/style.css">
</head>
<body>

<div id = "page">

<h1>Armstrong School District - Chromebook Solution</h1>

<?php
//displays warning message if class rosters for 4th and below weren't updated over the summer
$file = '/sources/classrooms.csv';

if (file_exists($file)) {
    $modifiedTime = filemtime($file);
    $year = date('Y', $modifiedTime);

    $juneFirst = strtotime(($year)."-06-01");

    if ($modifiedTime < $juneFirst) {
        echo "<h3 class='warning'>The Chromebook carts' locations and counts may not have been properly updated for this school year. Contact tech and request they modify classrooms.csv. Thanks!</h3>";
    }
}
?>

<h2><a href="/sites/startpipeline.html">Address a Specific Chromebook</a></h2>
<br>
<h2><a href="/sites/prereport.html">Perform Routine Chromebook Maintenance</a></h2>
<br>
<h2><a href="/sites/startknowledgebase.html">View the Chromebook Management Knowledge Base</a></h2>


</div>
</body>
</html>