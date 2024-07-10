<?php
define('DB_HOST', 'fdb1032.awardspace.net');
define('DB_USERNAME', '4495016_testdb1');
define('DB_PASSWORD', 'skih_3113');
define('DB_NAME', '4495016_testdb1');

define('POST_DATA_URL', 'http://samaq.atwebpages.com/sensordata.php');

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
