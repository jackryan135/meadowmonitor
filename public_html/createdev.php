<?php
if (isset($_POST['submit'])) {
	require "../../private/config.php";
	require "../../private/common.php";

	try {
		$connection = new PDO($dsn, $username, $password, $options);
		$new_device = array(
			"ownerID" => $_POST['ownerID'],
			"plantSpecies" => $_POST['plantSpecies'],
		);

		$sql = sprintf(
			"INSERT INTO %s (%s) values (%s)",
			"devices",
			implode(", ", array_keys($new_device)),
			":" . implode(", :", array_keys($new_device))
		);

		$statement = $connection->prepare($sql);
		$statement->execute($new_device);
	} catch (PDOException $error) {
		echo $sql . "<br>" . $error->getMessage();
	}
}
?>

<?php include "templates/header.php"; ?>

<?php if (isset($_POST['submit']) && $statement) { ?>
	<?php echo escape($_POST['plantSpecies']); ?> successfully added.
<?php } ?>

<h2>Add a device</h2>

<form method="post">
	<label for="ownerID">Owner ID</label>
	<input type="text" name="ownerID" id="ownerID">
	<label for="plantSpecies">Plant Species</label>
	<input type="text" name="plantSpecies" id="plantSpecies">
	<input type="submit" name="submit" value="Submit">
</form>

<a href="index.php">Back to home</a>

<?php include "templates/footer.php"; ?>