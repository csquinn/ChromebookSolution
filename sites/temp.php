<?php require '../vendor/autoload.php';

$client = new Google_Client();
$client->setAuthConfig('../user_variables/google-auth.json');
$client->setScopes([
    'https://www.googleapis.com/auth/admin.directory.device.chromeos.readonly'
]);
$client->setSubject('snipeit@asd.k12.pa.us');

$service = new Google_Service_Directory($client);

$serial = 'YX06JW4G';

		//create array specifying api call parameters
		$optParams = array(
			'projection' => 'BASIC',
			'query' => 'id:' . $serial,
			'maxResults' => 2
		);

		//make api call with the directory object
		$results = $service->chromeosdevices->listChromeosdevices('my_customer', $optParams); 


$devices = $results->getChromeosdevices();

if (empty($devices)) {
    echo "No device found for serial {$serialNumber}";
    exit;
}

$device = $devices[0];

// Status field
$status = $device->getStatus();

echo "Status: {$status}";

echo PHP_EOL;
$recentUsers = $device->getRecentUsers();

if (!empty($recentUsers)) {
    // Usually the first one is the last user
    $lastUser = $recentUsers[0];
    echo "    Last user email: " . $lastUser['email'] . PHP_EOL;
    echo "    Last login time: " . $lastUser['type'] . " / " . $lastUser['lastLoginTime'] . PHP_EOL;
} else {
    echo "No recent users recorded.";
}

$lastSync = $device->getLastSync() ?? 'never';
echo "               Last sync: {$lastSync}" . PHP_EOL;


echo PHP_EOL. "           ";

$mysqli = new mysqli("localhost","snipe_user","","snipeit");

// Check connection
if ($mysqli -> connect_errno) {
  echo "Failed to connect to MySQL: " . $mysqli -> connect_error;
  exit();
}

$result = $mysqli -> query("select assets.serial, assets.asset_tag, assets.rtd_location_id, assets.status_id, models.name as modelName, locations.name as locationName, status_labels.name as statusName from assets inner join models on assets.model_id = models.id inner join status_labels on assets.status_id = status_labels.id left join locations on assets.rtd_location_id = locations.id where assets.asset_tag = 'WHP A4-13' and assets.deleted_at is null;");
$row = $result -> fetch_assoc();
echo print_r($row);
?>