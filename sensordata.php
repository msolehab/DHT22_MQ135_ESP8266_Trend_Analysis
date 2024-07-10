<?php
require 'config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $api_key = escape_data($_POST["api_key"]);

    if ($api_key == PROJECT_API_KEY) {
        $temperature = escape_data($_POST["temperature"]);
        $humidity = escape_data($_POST["humidity"]);
        $gas_level = escape_data($_POST["gas_level"]); // New field for gas level
        $air_quality = escape_data($_POST["air_quality"]); // New field for air quality

        $sql = "INSERT INTO tbl_temperature (temperature, humidity, gas_level, air_quality, created_date) 
                VALUES ('$temperature', '$humidity', '$gas_level', '$air_quality', '".date("Y-m-d H:i:s")."')";

        if ($db->query($sql) === FALSE) {
            echo "Error: " . $sql . "<br>" . $db->error;
        } else {
            echo "OK. INSERT ID: " . $db->insert_id;
        }
    } else {
        echo "Wrong API Key";
    }
} else {
    echo "No HTTP POST request found";
}

function escape_data($data) {
    global $db;
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $db->real_escape_string($data);
}
?>
