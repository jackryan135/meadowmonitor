<?php
if (isset($_POST['submit'])) {
	require "../../private/config.php";
	require "../../private/common.php";

	try {
		$connection = new PDO($dsn, $username, $password, $options);
		$new_data = array(
			"deviceID" => $_POST['deviceID'],
			"plantSpecies" => $_POST['plantSpecies'],
			"ph" => $_POST['ph'],
			"temp" => $_POST['temp'],
			"light" => $_POST['light'],
			"moisture" => $_POST['moisture'],
		);

		$sql = sprintf(
			"INSERT INTO %s (%s) values (%s)",
			"data",
			implode(", ", array_keys($new_data)),
			":" . implode(", :", array_keys($new_data))
		);

		$statement = $connection->prepare($sql);
		$statement->execute($new_data);
	} catch (PDOException $error) {
		echo $sql . "<br>" . $error->getMessage();
	}
}
?>

<?php include "templates/header.php"; ?>

<?php if (isset($_POST['submit']) && $statement) { ?>
	<?php echo escape($_POST['plantSpecies']); ?> data successfully added.
<?php } ?>

<h2>Add a data entry</h2>

<form method="post">
	<label for="deviceID">Device ID</label>
	<input type="text" name="deviceID" id="deviceID">
	<label for="plantSpecies">Plant Species</label>
	<input type="text" name="plantSpecies" id="plantSpecies">
	<label for="ph">pH Level</label>
	<input type="text" name="ph" id="ph">
	<label for="temp">Tempurature</label>
	<input type="text" name="temp" id="temp">
	<label for="light">Light Level</label>
	<input type="text" name="light" id="light">
	<label for="moisture">Moisture Level</label>
	<input type="text" name="moisture" id="moisture">
	<input type="submit" name="submit" value="Submit">
</form>

<a href="index.php">Back to home</a>

<?php include "templates/footer.php"; ?>