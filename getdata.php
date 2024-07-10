<?php
require 'config.php';

$sql = "SELECT * FROM tbl_temperature ORDER BY id DESC LIMIT 30";
$result = $db->query($sql);

if (!$result) {
    echo json_encode(['error' => $db->error]);
    exit();
}

$data = [];
$totalTemp = 0;
$totalHumidity = 0;
$totalGas = 0;
$count = 0;

while ($row = $result->fetch_assoc()) {
    $data[] = $row;
    $totalTemp += $row['temperature'];
    $totalHumidity += $row['humidity'];
    $totalGas += $row['gas_level'];
    $count++;
}

$averages = [
    'avg_temp' => $count ? $totalTemp / $count : 0,
    'avg_humidity' => $count ? $totalHumidity / $count : 0,
    'avg_gas' => $count ? $totalGas / $count : 0
];

$response = [
    'data' => $data,
    'averages' => $averages
];

echo json_encode($response);
?>
