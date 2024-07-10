<?php
require 'config.php';

// Fetch the data
$sql = "SELECT * FROM tbl_temperature ORDER BY id DESC LIMIT 30";
$result = $db->query($sql);

if (!$result) {
    die("Error: " . $sql . "<br>" . $db->error);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>SAM's Air Quality Monitor</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.1/dist/css/bootstrap.min.css">
    <script src="https://code.jquery.com/jquery-3.1.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.1/dist/js/bootstrap.bundle.min.js"></script>
    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
    <style>
        .chart {
            width: 100%;
            min-height: 450px;
        }
        .row {
            margin: 0 !important;
        }
        .average-section {
            text-align: center;
            margin: 20px 0;
        }
        .average-section img {
            width: 50px;
            height: 50px;
        }
    .mid {
  		text-align: center;
  		border: 7px solid green;
	}	
    </style>
</head>
<body>

<div class="container">
    <div class="mid">
    	<h2>AQ Level</h2>
            <ul>
  				<li>1: Good</li>
  				<li>2: Poor</li>
  				<li>3: Dangerous</li>
			</ul>  
    </div>
    <div class="row">
        <div class="col-md-12 text-center">
            <h1>Air Quality Monitor</h1>
        </div>
        <div class="clearfix"></div>
        <div class="col-md-6">
            <div id="chart_temperature" class="chart"></div>
        </div>
        <div class="col-md-6">
            <div id="chart_humidity" class="chart"></div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <table class="table">
                <thead>
                <tr>
                    <th scope="col">#</th>
                    <th scope="col">Temperature (°C)</th>
                    <th scope="col">Humidity (%)</th>
                    <th scope="col">C02 Concentration (PPM)</th>
                    <th scope="col">AQ Level</th>
                    <th scope="col">Date Time</th>
                </tr>
                </thead>
                <tbody>
                <?php $i = 1; while ($row = $result->fetch_assoc()) {?>
                    <tr>
                        <th scope="row"><?php echo $i++;?></th>
                        <td><?php echo $row['temperature'];?></td>
                        <td><?php echo $row['humidity'];?></td>
                        <td><?php echo $row['gas_level'];?></td>
                        <td><?php echo $row['air_quality'];?></td>
                        <td><?php echo date("Y-m-d h:i:sa ", strtotime($row['created_date']));?></td>
                    </tr>
                <?php } ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div id="line_chart_temperature" class="chart"></div>
            <div id="line_chart_humidity" class="chart"></div>
            <div id="line_chart_gas" class="chart"></div>
        </div>
    </div>
        
    <div class="mid">
    	<h2>Average</h2>
    </div>

    <div class="row average-section">
        <div class="col-md-4">
            <img src="assets/img/therm.png" alt="Temperature">
            <h3>Temperature : <span id="avg_temp">0</span> °C</h3>
        </div>
        <div class="col-md-4">
            <img src="assets/img/hum.png" alt="Humidity">
            <h3>Humidity : <span id="avg_humidity">0</span> %</h3>
        </div>
        <div class="col-md-4">
            <img src="assets/img/gas.png" alt="Gas Level">
            <h3>C02 Concentration : <span id="avg_gas">0</span> ppm</h3>
        </div>
    </div>
</div>

<script>
    google.charts.load('current', {'packages':['gauge', 'corechart']});
    google.charts.setOnLoadCallback(drawTemperatureChart);
    google.charts.setOnLoadCallback(drawHumidityChart);
    google.charts.setOnLoadCallback(drawLineCharts);

    function drawTemperatureChart() {
        var data = google.visualization.arrayToDataTable([
            ['Label', 'Value'],
            ['Temperature', 0],
        ]);

        var options = {
            width: 1600,
            height: 480,
            redFrom: 30,
            redTo: 100,
            yellowFrom: 26,
            yellowTo: 30,
            greenFrom: 0,
            greenTo: 26,
            minorTicks: 5
        };

        var chart = new google.visualization.Gauge(document.getElementById('chart_temperature'));
        chart.draw(data, options);

        function refreshData() {
            $.ajax({
                url: 'getdata.php',
                dataType: 'json',
                success: function (response) {
                    var temperature = parseFloat(response.data[0].temperature).toFixed(2);
                    var data = google.visualization.arrayToDataTable([
                        ['Label', 'Value'],
                        ['Temperature', eval(temperature)],
                    ]);
                    chart.draw(data, options);
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    console.log(errorThrown + ': ' + textStatus);
                }
            });
        }

        setInterval(refreshData, 1000);
    }

    function drawHumidityChart() {
        var data = google.visualization.arrayToDataTable([
            ['Label', 'Value'],
            ['Humidity', 0],
        ]);

        var options = {
            width: 1600,
            height: 480,
            redFrom: 72,
            redTo: 100,
            yellowFrom: 72,
            yellowTo: 87,
            greenFrom: 0,
            greenTo: 72,
            minorTicks: 5
        };

        var chart = new google.visualization.Gauge(document.getElementById('chart_humidity'));
        chart.draw(data, options);

        function refreshData() {
            $.ajax({
                url: 'getdata.php',
                dataType: 'json',
                success: function (response) {
                    var humidity = parseFloat(response.data[0].humidity).toFixed(2);
                    var data = google.visualization.arrayToDataTable([
                        ['Label', 'Value'],
                        ['Humidity', eval(humidity)],
                    ]);
                    chart.draw(data, options);
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    console.log(errorThrown + ': ' + textStatus);
                }
            });
        }

        setInterval(refreshData, 1000);
    }

    function drawLineCharts() {
        function refreshLineChartData() {
            $.ajax({
                url: 'getdata.php',
                dataType: 'json',
                success: function (response) {
                    var temperatureData = [['Date', 'Temperature']];
                    var humidityData = [['Date', 'Humidity']];
                    var gasData = [['Date', 'Gas Level']];

                    response.data.reverse().forEach(function (row) {
                        var date = new Date(row.created_date);
                        temperatureData.push([date, parseFloat(row.temperature)]);
                        humidityData.push([date, parseFloat(row.humidity)]);
                        gasData.push([date, parseFloat(row.gas_level)]);
                    });

                    drawLineChart('line_chart_temperature', temperatureData, 'Temperature Over Time', 'Temperature (°C)');
                    drawLineChart('line_chart_humidity', humidityData, 'Humidity Over Time', 'Humidity (%)');
                    drawLineChart('line_chart_gas', gasData, 'C02 Concentration Over Time', 'Gas Level (ppm)');

                    // Update averages
                    $('#avg_temp').text(parseFloat(response.averages.avg_temp).toFixed(2));
                    $('#avg_humidity').text(parseFloat(response.averages.avg_humidity).toFixed(2));
                    $('#avg_gas').text(parseFloat(response.averages.avg_gas).toFixed(2));
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    console.log(errorThrown + ': ' + textStatus);
                }
            });
        }

        function drawLineChart(elementId, data, title, vAxisTitle) {
            var dataTable = google.visualization.arrayToDataTable(data);

            var options = {
                title: title,
                curveType: 'function',
                legend: { position: 'bottom' },
                hAxis: {
                    title: 'Date',
                    format: 'MMM d, yyyy HH:mm:ss',
                    gridlines: { count: 15 } // Adjust to show more gridlines
                },
                vAxis: {
                    title: vAxisTitle
                }
            };

            var chart = new google.visualization.LineChart(document.getElementById(elementId));
            chart.draw(dataTable, options);
        }

        refreshLineChartData();
        setInterval(refreshLineChartData, 10000); // Refresh data every 10 seconds
    }

    $(window).resize(function(){
        drawTemperatureChart();
        drawHumidityChart();
        drawLineCharts();
    });
</script>
</body>
</html>