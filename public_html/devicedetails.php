<?php

require "../../private/config.php";
require "../../private/common.php";

session_start();

if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] == false){
	header("location: login.php");
	exit;
}
else{
	try {
		$connection = new PDO($dsn, $username, $password, $options);
		$id = $_SESSION['id'];
		$deviceID = $_GET['id'];

		$sql = "SELECT ownerID FROM devices WHERE id = :id";
		$statement = $connection->prepare($sql);
		$statement->bindValue(':id', $deviceID);
		$statement->execute();

		$result = $statement->fetch();
		if($id != $result["ownerID"]){
			header("location: userdevices.php");
			exit;
		}
	} catch (PDOException $error) {
		echo $sql . "<br>" . $error->getMessage();
	}
}

// if (isset($_POST['submit'])) {
// 	try {
// 		$connection = new PDO($dsn, $username, $password, $options);
// 		$device = [
// 			"hiddenid"        => $_POST['hiddenid'],
// 			"ownerID" => $_POST['ownerID'],
// 			"idealPlantSpecies"  => $_POST['idealPlantSpecies'],
// 			"idealMoisture" => $_POST['idealMoisture'],
// 			"idealTemp" => $_POST['idealTemp'],
// 			"date"      => $_POST['date']
// 		];

// 		$sql = "UPDATE devices
// 			  SET id = :hiddenid,
// 				ownerID = :ownerID,
// 				idealPlantSpecies = :idealPlantSpecies,
// 				idealMoisture = :idealMoisture,
// 				idealTemp = :idealTemp,
// 				date = :date
// 			  WHERE id = :hiddenid";

// 		$statement = $connection->prepare($sql);
// 		$statement->execute($device);
// 	} catch (PDOException $error) {
// 		echo $sql . "<br>" . $error->getMessage();
// 	}
// }

if (isset($_GET['id'])) {
	try {
		$connection = new PDO($dsn, $username, $password, $options);
		$id = $_GET['id'];

		$sql = "SELECT * FROM data WHERE deviceID = :id and date = (SELECT max(date) FROM data WHERE deviceID = :id)";
		$statement = $connection->prepare($sql);
		$statement->bindValue(':id', $id);
		$statement->execute();

		$user = $statement->fetch(PDO::FETCH_ASSOC);

		$devicesql = "SELECT plants.plantName, devices.id, devices.label, devices.idealTemp, devices.idealPH, devices.idealMoisture, devices.idealLight FROM devices INNER JOIN plants ON devices.idealPlantID = plants.ID WHERE devices.id = :id";
		$devicestatement = $connection->prepare($devicesql);
		$devicestatement->bindValue(':id', $id);
		$devicestatement->execute();

		$device = $devicestatement->fetch(PDO::FETCH_ASSOC);

		$graphsql = "SELECT date, ph, temp, light, moisture FROM data WHERE deviceID = :id and date >= DATE_SUB(NOW(),INTERVAL 1 HOUR)";
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

	<h2>Plant Details - <?php echo $device['label']; ?></h2>

	<script>
		var id = <?php echo $id ?>;

		setInterval("my_function();", 3000);

		function my_function() {
			$("#tempDiv").load("/devicedetails.php?id=" + id + " #tempDiv");
			$("#lightDiv").load("/devicedetails.php?id=" + id + " #lightDiv");
			$("#moistureDiv").load("/devicedetails.php?id=" + id + " #moistureDiv");
		}
	</script>
	<div class="container px-xl-5" style="padding:20px; margin-top:10px;">
		<h5>Current Status:</h5>
		<div class="card-deck px-xl-5" style="margin-top:15px;">
			<div class="card bg-success text-white text-center p-1">
				<h5 style="padding-top: 16px;">Tempurature</h5>
				<div id="tempDiv">
					<p><strong><?php echo $user['temp']; ?> ºF</strong></p>
				</div>
			</div>
			<div class="card bg-warning text-white text-center p-1">
				<h5 style="padding-top: 16px;">Light Level</h5>
				<div id="lightDiv">
					<p><strong>
						<?php if($user['light'] >= 3800){
							echo "High";
						} else if ($user['light'] >= 2900 && $user['light'] < 3800){
							echo "Medium";	
						}else{
							echo "Low";	
						}?></strong></p>
				</div>
			</div>
			<div class="card bg-info text-white text-center p-1">
				<h5 style="padding-top: 16px;">Moisture Level</h5>
				<div id="moistureDiv">
					<p><strong><?php if($user['moisture'] >= 3500){
							echo "High";
						} else if ($user['moisture'] >= 2750 && $user['light'] < 3500){
							echo "Medium";	
						}else{
							echo "Low";	
						}?></strong></p>
				</div>
			</div>
		</div>
	</div>

	<div class="px-xl-5" style="margin: 20px;">
		<h5>Historical Data:</h5>
		<p class="graph-label"><strong>Tempurature</strong></p>
		<div><canvas class="chart-container" id="tempchart-container"></canvas></div>
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
			lightData = data.map(function(e) {
				return e.light;
			}),
			tempData = data.map(function(e) {
				return e.temp;
			}),
			moistureData = data.map(function(e) {
				return e.moisture;
			});

			labels = labels.filter(function (value, index, ar) {
    			return (index % 12 == 0);
			} );

			dates = dates.filter(function (value, index, ar) {
    			return (index % 12 == 0);
			} );

			lightData = lightData.filter(function (value, index, ar) {
    			return (index % 12 == 0);
			} );

			tempData = tempData.filter(function (value, index, ar) {
    			return (index % 12 == 0);
			} );

			moistureData = moistureData.filter(function (value, index, ar) {
    			return (index % 12 == 0);
			} );

		var ctxtemp = document.getElementById('tempchart-container').getContext("2d");
		var ctxlight = document.getElementById('lightchart-container').getContext("2d");
		var ctxmoist = document.getElementById('moistchart-container').getContext("2d");

		var lightchart;
		var moistchart;
		var tempchart;

		window.onload = function() {
			Chart.defaults.global.legend.display = false;

			tempchart = new Chart(ctxtemp, {
				type: 'line',
				data: {
					labels: labels,
					datasets: [{
						label: "Tempurature",
						data: tempData,
						borderWidth: 2,
						backgroundColor: "rgba(6, 160, 6, 0.1)",
						borderColor: "rgba(6, 160, 6, 1)",
						pointBackgroundColor: "rgba(225, 225, 225, 1)",
						pointBorderColor: "rgba(6, 160, 6, 1)",
						pointHoverBackgroundColor: "rgba(6, 160, 6, 1)",
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
					lightData.push(data.light);
					tempData.push(data.temp);
					moistureData.push(data.moisture);
				}
			}, "json");

			tempchart.update();
			lightchart.update();
			moistchart.update();
		}
	</script>

	<div class="container px-xl-5" style="padding:20px; margin-top:10px;">
		<h5>Edit Environment Settings:</h5>
		<form method="post" action="http://meadowmonitor.com:5001/api/webapp/<?php echo $deviceID; ?>/plant/change" class="px-xl-5" style="margin-top:30px;" id="plantChangeForm">
			<div class="row">
				<label for="plantSpecies" class="col">
					Plant Species Search
				</label>
				<label for="plantSpecies" class="col">
					Plant Species
				</label>
			</div>

			<script>
				function update_select(data) {
					$("#idealPlantSpecies").empty();

					var select = document.getElementById("idealPlantSpecies");
					
					for (var i = 0; i < data.length; i++) {
						var option = document.createElement('option');
						option.text = data[i].common_name;
						option.value = data[i].id;
						select.add(option, 0);
					}
					select.disabled = false;
				}

				function update_search(term) {
					var select = document.getElementById("idealPlantSpecies");
					select.disabled = true;
					$.getJSON(
						"http://meadowmonitor.com:5001/api/webapp/search",
						{ 'search_term': term },
						update_select
					);
				}
			</script>

			<div class="form-row">
				<div class="col">
					<input type="text" class="form-control" id="plantSpeciesSearch"
						value="<?php echo $device['plantName']; ?>"
						onChange="update_search(this.value);">
					</input>
				</div>
				<div class="col">
					<select class="form-control" name="species_id" id="idealPlantSpecies" disabled="false"></select>
				</div>
			</div>
			<input class="btn btn-primary" type="submit" value="Change Plant" style="margin-top: 10px; margin-right:auto;">
		</form>

		<form method="post" action="http://meadowmonitor.com:5001/api/webapp/<?php echo $deviceID; ?>/override" class="px-xl-5" style="margin-top:30px;">
			<div class="row">
				<label for="idealMoisture" class="col">
					Desired Moisture
				</label>
				<label for="idealTemp" class="col">
					Desired Tempurature (ºF)
				</label>
			</div>

			<div class="form-row">
				<div class="col">
					<!-- <input type="text" class="form-control" name="moisture" id="idealMoisture"
						value="<?php echo $device['idealMoisture']; ?>">
					</input> -->
					<select class="form-control" name="moisture" id="idealMoisture">
						<?php if($device['idealMoisture'] == "LOW"){ ?>
							<option value="LOW" selected>Low</option>
						<?php } else { ?>
							<option value="LOW">Low</option>
						<?php } ?>
						<?php if($device['idealMoisture'] == "MEDIUM"){ ?>
							<option value="MEDIUM" selected>Medium</option>
						<?php } else { ?>
							<option value="MEDIUM">Medium</option>
						<?php } ?>
						<?php if($device['idealMoisture'] == "HIGH"){ ?>
							<option value="HIGH" selected>High</option>
						<?php } else { ?>
							<option value="HIGH">High</option>
						<?php } ?>
					</select>
				</div>
				<div class="col">
					<input type="text" class="form-control" name="temperature" id="idealTemp"
						value="<?php echo $device['idealTemp']; ?>">
					</input>
				</div>
			</div>
			<input class="btn btn-primary" type="submit" name="submit" value="Override defaults"
				style="margin-top: 10px; margin-right:auto;">
		</form>
	</div>

	<a href="userdevices.php">Back to home</a>
</div>

<?php require "templates/footer.php"; ?>