<?php

require "../../private/config.php";
require "../../private/common.php";


if (isset($_POST['submit'])) {
	try {
		$connection = new PDO($dsn, $username, $password, $options);
		$device = [
			"hiddenid"        => $_POST['hiddenid'],
			"ownerID" => $_POST['ownerID'],
			"idealPlantSpecies"  => $_POST['idealPlantSpecies'],
			"idealMoisture" => $_POST['idealMoisture'],
			"idealTemp" => $_POST['idealTemp'],
			"date"      => $_POST['date']
		];

		$sql = "UPDATE devices
			  SET id = :hiddenid,
				ownerID = :ownerID,
				idealPlantSpecies = :idealPlantSpecies,
				idealMoisture = :idealMoisture,
				idealTemp = :idealTemp,
				date = :date
			  WHERE id = :hiddenid";

		$statement = $connection->prepare($sql);
		$statement->execute($device);
	} catch (PDOException $error) {
		echo $sql . "<br>" . $error->getMessage();
	}
}

if (isset($_GET['id'])) {
	try {
		$connection = new PDO($dsn, $username, $password, $options);
		$id = $_GET['id'];

		$sql = "SELECT * FROM data WHERE deviceID = :id and date = (SELECT max(date) FROM data WHERE deviceID = :id)";
		$statement = $connection->prepare($sql);
		$statement->bindValue(':id', $id);
		$statement->execute();

		$user = $statement->fetch(PDO::FETCH_ASSOC);

		$devicesql = "SELECT plants.plantName, devices.id, devices.idealTemp, devices.idealPH, devices.idealMoisture, devices.idealLight FROM devices INNER JOIN plants ON devices.idealPlantID = plants.ID WHERE devices.id = :id";
		$devicestatement = $connection->prepare($devicesql);
		$devicestatement->bindValue(':id', $id);
		$devicestatement->execute();

		$device = $devicestatement->fetch(PDO::FETCH_ASSOC);

		$graphsql = "SELECT date, ph, temp, light, moisture FROM data WHERE deviceID = :id"; # and date >= DATE_SUB(NOW(),INTERVAL 1 HOUR)";
		$graphstate = $connection->prepare($graphsql);
		$graphstate->bindValue(':id', $id);
		$graphstate->execute();

		$graphdata = $graphstate->fetchAll(PDO::FETCH_ASSOC);
	} catch (PDOException $error) {
		echo $sql . "<br>" . $error->getMessage();
	}
} else {
	echo "Something went wrong!";
	exit;
}
?>

<?php require "templates/header.php"; ?>
<div class="container px-lg-5" style="margin-top: 25px;">

	<?php if (isset($_POST['submit']) && $statement) : ?>
		Device successfully updated.
	<?php endif; ?>

	<h2>Plant Details - Device ID: <?php echo $user['deviceID']; ?></h2>

	<script>
		var id = <?php echo $id ?>;

		setInterval("my_function();", 3000);

		function my_function() {
			$("#tempDiv").load("/devicedetails.php?id=" + id + " #tempDiv");
			$("#phDiv").load("/devicedetails.php?id=" + id + " #phDiv");
			$("#lightDiv").load("/devicedetails.php?id=" + id + " #lightDiv");
			$("#moistureDiv").load("/devicedetails.php?id=" + id + " #moistureDiv");
		}
	</script>
	<div class="container px-xl-5" style="padding:20px; margin-top:10px;">
		<h5>Current Status:</h5>
		<div class="card-deck px-xl-5" style="margin-top:15px;">
			<div class="card bg-primary text-white text-center p-1">
				<h5 style="padding-top: 16px;">Tempurature</h5>
				<div id="tempDiv">
					<p><strong><?php echo $user['temp']; ?></strong></p>
				</div>
			</div>
			<div class="card bg-success text-white text-center p-1">
				<h5 style="padding-top: 16px;">pH</h5>
				<div id="phDiv">
					<p><strong><?php echo $user['ph']; ?></strong></p>
				</div>
			</div>
			<div class="card bg-warning text-white text-center p-1">
				<h5 style="padding-top: 16px;">Light Level</h5>
				<div id="lightDiv">
					<p><strong><?php echo $user['light']; ?></strong></p>
				</div>
			</div>
			<div class="card bg-info text-white text-center p-1">
				<h5 style="padding-top: 16px;">Moisture Level</h5>
				<div id="moistureDiv">
					<p><strong><?php echo $user['moisture']; ?></strong></p>
				</div>
			</div>
		</div>
	</div>

	<div class="px-xl-5" style="margin: 20px;">
		<h5>Historical Data:</h5>
		<p class="graph-label"><strong>Tempurature</strong></p>
		<div><canvas class="chart-container" id="tempchart-container"></canvas></div>
		<p class="graph-label"><strong>pH</strong></p>
		<div><canvas class="chart-container" id="phchart-container"></canvas></div>
		<p class="graph-label"><strong>Light Level</strong></p>
		<div><canvas class="chart-container" id="lightchart-container"></canvas></div>
		<p class="graph-label"><strong>Moisture Level</strong></p>
		<div><canvas class="chart-container" id="moistchart-container"></canvas></div>
	</div>

	<script>
		var data = <?php echo json_encode($graphdata, JSON_NUMERIC_CHECK); ?>;
		var labels = data.map(function(e) {
				return (e.date.substring(5, 7) + "/" + e.date.substring(8, 10) + " - " + e.date.substring(11, 16));
			}),
			dates = data.map(function(e) {
				return e.date;
			}),
			phData = data.map(function(e) {
				return e.ph;
			}),
			lightData = data.map(function(e) {
				return e.light;
			}),
			tempData = data.map(function(e) {
				return e.temp;
			}),
			moistureData = data.map(function(e) {
				return e.moisture;
			});

		var ctxph = document.getElementById('phchart-container').getContext("2d");
		var ctxtemp = document.getElementById('tempchart-container').getContext("2d");
		var ctxlight = document.getElementById('lightchart-container').getContext("2d");
		var ctxmoist = document.getElementById('moistchart-container').getContext("2d");

		var phchart;
		var lightchart;
		var moistchart;
		var tempchart;

		window.onload = function() {
			Chart.defaults.global.legend.display = false;
			phchart = new Chart(ctxph, {
				title: "pH",
				type: 'line',
				data: {
					labels: labels,
					datasets: [{
						label: "pH",
						data: phData,
						borderWidth: 2,
						backgroundColor: "rgba(6, 200, 6, 0.1)",
						borderColor: "rgba(6, 200, 6, 1)",
						pointBackgroundColor: "rgba(225, 225, 225, 1)",
						pointBorderColor: "rgba(6, 200, 6, 1)",
						pointHoverBackgroundColor: "rgba(6, 200, 6, 1)",
						pointHoverBorderColor: "#fff"
					}]
				},
			});

			tempchart = new Chart(ctxtemp, {
				type: 'line',
				data: {
					labels: labels,
					datasets: [{
						label: "Tempurature",
						data: tempData,
						borderWidth: 2,
						backgroundColor: "rgba(6, 6, 160, 0.1)",
						borderColor: "rgba(6, 6, 160, 1)",
						pointBackgroundColor: "rgba(225, 225, 225, 1)",
						pointBorderColor: "rgba(6, 6, 160, 1)",
						pointHoverBackgroundColor: "rgba(6, 6, 160, 1)",
						pointHoverBorderColor: "#fff"
					}]
				},
			});

			lightchart = new Chart(ctxlight, {
				type: 'line',
				data: {
					labels: labels,
					datasets: [{
						label: "Light",
						data: lightData,
						borderWidth: 2,
						backgroundColor: "rgba(235, 235, 6, 0.3)",
						borderColor: "rgba(235, 235, 6, 1)",
						pointBackgroundColor: "rgba(225, 225, 225, 1)",
						pointBorderColor: "rgba(235, 235, 6, 1)",
						pointHoverBackgroundColor: "rgba(235, 235, 6, 1)",
						pointHoverBorderColor: "#fff"
					}]
				},
			});

			moistchart = new Chart(ctxmoist, {
				type: 'line',
				data: {
					labels: labels,
					datasets: [{
						label: "Moisture",
						data: moistureData,
						borderWidth: 2,
						backgroundColor: "rgba(6, 50, 255, 0.1)",
						borderColor: "rgba(6, 50, 255, 1)",
						pointBackgroundColor: "rgba(225, 225, 225, 1)",
						pointBorderColor: "rgba(6, 50, 255, 1)",
						pointHoverBackgroundColor: "rgba(6, 50, 255, 1)",
						pointHoverBorderColor: "#fff"
					}]
				},
			});

			phchart.render();
			tempchart.render();
			lightchart.render();
			moistchart.render();
		}

		setInterval("chartUpdate();", 3000);

		function chartUpdate() {
			var url = "/getUpdate.php?id=" + <?php echo $id ?>;
			$.get(url, function(data) {
				console.log(data);
				if (data.date != dates[labels.length - 1]) {
					dates.push(data.date);
					labels.push(data.date.substring(5, 7) + "/" + data.date.substring(8, 10) + " - " + data.date.substring(11, 16));
					phData.push(data.ph);
					lightData.push(data.light);
					tempData.push(data.temp);
					moistureData.push(data.moisture);
				}
			}, "json");

			phchart.update();
			tempchart.update();
			lightchart.update();
			moistchart.update();
		}
	</script>

	<div class="container px-xl-5" style="padding:20px; margin-top:10px;">
		<h5>Edit Environment Settings:</h5>
		<form method="post" class="px-xl-5" style="margin-top:30px;">
			<div class="row">
			<div class="col">
					<input type="hidden" class="form-control" name="hiddenid" id="hiddenid" value="<?php echo $device['id'] ?>">
					</input>
				</div>
				<div class="col">
					<input type="hidden" class="form-control" name="ownerID" id="ownerID" value="<?php echo $device['ownerID'] ?>">
					</input>
				</div>
				<div class="col">
					<input type="hidden" class="form-control" name="date" id="date" value="<?php echo $device['date'] ?>">
					</input>
				</div>
			</div>
			<div class="row">
				<label for="plantSpecies" class="col">
					Plant Species
				</label>
				<label for="moisture" class="col">
					Desired Moisture
				</label>
				<label for="temp" class="col">
					Desired Tempurature
				</label>
			</div>
			<div class="form-row">
				<div class="col">
					<input type="text" class="form-control" name="idealPlantSpecies" id="idealPlantSpecies" value="<?php echo $device['plantName'] ?>">
					</input>
				</div>
				<div class="col">
					<input type="text" class="form-control" name="idealMoisture" id="idealMoisture" value="<?php echo $device['idealMoisture'] ?>">
					</input>
				</div>
				<div class="col">
					<input type="text" class="form-control" name="idealTemp" id="idealTemp" value="<?php echo $device['idealTemp'] ?>">
					</input>
				</div>
			</div>
			<input class="btn btn-primary" type="submit" name="submit" value="Submit" style="margin-top: 10px; margin-right:auto;">
		</form>
	</div>

	<a href="index.php">Back to home</a>
</div>

<?php require "templates/footer.php"; ?>