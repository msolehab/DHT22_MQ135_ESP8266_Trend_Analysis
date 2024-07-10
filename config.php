<?php
define('DB_HOST', 'Your Awardspace Host name');
define('DB_USERNAME', 'your username');
define('DB_PASSWORD', 'your password');
define('DB_NAME', 'your mysql database name');

define('POST_DATA_URL', 'your domain/sensordata.php');

//PROJECT_API_KEY is the exact duplicate of, PROJECT_API_KEY in NodeMCU sketch file
//Both values must be same
define('PROJECT_API_KEY', 'tempQuality');


//set time zone for your country
date_default_timezone_set("Asia/Kuala_Lumpur");

// Connect with the database 
$db = new mysqli(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_NAME); 
 
// Display error if failed to connect 
if ($db->connect_errno) { 
    echo "Connection to database is failed: ".$db->connect_error;
    exit();
}
