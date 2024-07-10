<?php require 'config.php'; ?>

<!DOCTYPE html>
<html>
<body>

<h2>SAMLogs - Test POST data</h2>

<form method="POST" action="sensordata.php"> <!-- Change action to sensordata.php -->
  <label for="apikey">Api Key:</label><br>
  <input type="text" id="api_key" name="api_key" value="<?php echo PROJECT_API_KEY;?>"><br>
  <label for="temperature">Temperature:</label><br>
  <input type="text" id="temperature" name="temperature" value="16.53"><br>
  <label for="humidity">Humidity:</label><br>
  <input type="text" id="humidity" name="humidity" value="55.67"><br>
  <label for="gas_level">Gas Level:</label><br> <!-- New field for gas level -->
  <input type="text" id="gas_level" name="gas_level" value="20"><br>
  <label for="air_quality">Air Quality:</label><br> <!-- New field for air quality -->
  <input type="text" id="air_quality" name="air_quality" value="2"><br><br>
  <input type="submit" value="Submit">
</form> 

</body>
</html>
