<?php
$csvFile = 'exclusions.csv';
$message = '';

// ========================
// HANDLE DELETE REQUEST
// ========================
if (isset($_POST['delete'])) {
	$deleteIndex = (int)$_POST['delete'];

	$rows = [];
	if (($handle = fopen($csvFile, 'r')) !== false) {
		while (($data = fgetcsv($handle, 0, ",", '"', "\\")) !== false) {
			$rows[] = $data;
		}
		fclose($handle);
	}

	// Remove the selected row (skip header row at index 0)
	if (isset($rows[$deleteIndex]) && $deleteIndex !== 0) {
		unset($rows[$deleteIndex]);

		// Rewrite CSV
		$handle = fopen($csvFile, 'w');
		foreach ($rows as $row) {
			fputcsv($handle, $row, ",", '"', "\\");
		}
		fclose($handle);

		$message = 'Row deleted successfully.';
	}

	header('Location: addExclusions.php'.((isset($_POST['school']))?("?school=".$_POST['school']):("")));
	exit;
}

// ========================
// HANDLE ADD REQUEST
// ========================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add'])) {
	$exclusion  = trim($_POST['exclusion'] ?? '');
	$reason = trim($_POST['reason'] ?? '');
	

	if ($exclusion && $reason) {
		$handle = fopen($csvFile, 'a');
		fputcsv($handle, [$exclusion, $reason, ((isset($_POST['permanent']))?("perm"):("nonperm")), date("m-d-Y"), ((isset($_POST['school']))?($_POST['school']):(""))]);
		fclose($handle);

		$message = 'Exclusion added successfully.';
    }

	header('Location: addExclusions.php'.((isset($_POST['school']))?("?school=".$_POST['school']):("")));
	exit;
}

// ========================
// READ CSV FOR DISPLAY
// ========================
$rows = [];
if (file_exists($csvFile) && ($handle = fopen($csvFile, 'r')) !== false) {
	while (($data = fgetcsv($handle, 0, ",", '"', "\\")) !== false) {
		$rows[] = $data;
	}
	fclose($handle);
}
?>

<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>ChromebookSolution</title>
	<link rel = "stylesheet" href = "../style.css">
</head>
<body>
<div id="page">
<h2>Add New Exclusion</h2>

<form method="post">
	<input type="hidden" name="add" value="1">
	<input type='hidden' name='school' value=<?php echo $_GET['school']; ?>>
	<label>
		Exclusion (99#, Asset Tag, or Serial):
		<input type="text" name="exclusion" required pattern="[^,\']*">
	</label>

	<label>
		Reason:
		<input type="text" name="reason" required pattern="[^,\']*">
	</label>

	<label>
		Permanent:
		<input type="checkbox" name="permanent">
	</label>

	<button type="submit">Add</button>
</form>

<hr>
<h2>Current Exclusions</h2>
<?php if (!empty($rows)): ?>
<table>
	<tr>
		<?php foreach ($rows[0] as $header): ?>
			<th><?= htmlspecialchars($header) ?></th>
		<?php endforeach; ?>
	</tr>

	<?php foreach ($rows as $index => $row): ?>
		<?php if ($index === 0) continue; ?>
		<tr>
			<?php foreach ($row as $cell): ?>
				<td><?php if($cell == "perm"){echo "Yes";}else if($cell =="nonperm"){echo "No";}else{echo htmlspecialchars($cell);} ?></td>
			<?php endforeach; ?>
			<td>
				<form method="post" class="inline">
				<input type='hidden' name='school' value=<?php echo $_GET['school']; ?>>
				<input type="hidden" name="delete" value="<?= $index ?>">
				<button type="submit" onclick="return confirm('Delete this exclusion?')">
					Delete
					</button>
				</form>
			</td>
		</tr>
	<?php endforeach; ?>
</table>
<?php else: ?>
	<p>No data found.</p>
<?php endif; ?>

<hr>

</div>

</body>
</html>