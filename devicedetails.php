<?php

require "../config.php";
require "../common.php";

if (isset($_POST['submit'])) {
	try {
		$connection = new PDO($dsn, $username, $password, $options);
		$device = [
			"id"        => $_POST['id'],
			"ownerID" => $_POST['ownerID'],
			"plantSpecies"  => $_POST['plantSpecies'],
			"date"      => $_POST['date']
		];

		$sql = "UPDATE devices
			  SET id = :id,
				ownerID = :ownerID,
				plantSpecies = :plantSpecies,
				date = :date
			  WHERE id = :id";

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

		$sql = "SELECT * FROM devices WHERE id = :id";
		$statement = $connection->prepare($sql);
		$statement->bindValue(':id', $id);
		$statement->execute();

		$user = $statement->fetch(PDO::FETCH_ASSOC);
	} catch (PDOException $error) {
		echo $sql . "<br>" . $error->getMessage();
	}
} else {
	echo "Something went wrong!";
	exit;
}
?>

<?php require "templates/header.php"; ?>

<?php if (isset($_POST['submit']) && $statement) : ?>
	<?php echo escape($_POST['id']); ?> successfully updated.
<?php endif; ?>

<h2>Edit a device</h2>

<form method="post">
	<?php foreach ($user as $key => $value) : ?>
		<label for="<?php echo $key; ?>">
			<?php echo ucfirst($key); ?>
		</label>
		<input type="text" name="<?php echo $key; ?>" id="<?php echo $key; ?>" value="<?php echo escape($value); ?>" <?php echo ($key === 'id' ? 'readonly' : null); ?>>
	<?php endforeach; ?>
	<input type="submit" name="submit" value="Submit">
</form>

<a href="index.php">Back to home</a>

<?php require "templates/footer.php"; ?>