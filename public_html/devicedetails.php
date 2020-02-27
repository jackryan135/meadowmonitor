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

		$sql = "SELECT * FROM devices JOIN data WHERE devices.id = :id and data.date = (SELECT max(data.date) FROM data WHERE deviceID = :id)";
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
<div class="container px-lg-5" style="margin-top: 25px;">

	<?php if (isset($_POST['submit']) && $statement) : ?>
		<?php echo escape($_POST['id']); ?> successfully updated.
	<?php endif; ?>

	<h2>Plant Details - Device ID: <?php echo $user['deviceID']; ?></h2>

	<div class="container px-xl-5" style="padding:20px; margin-top:10px;">
		<h5>Current Status:</h5>
		<div class="card-deck px-xl-5" style="margin-top:15px;">
			<div class="card bg-primary text-white text-center p-1">
				<h5 style="padding-top: 16px;">Tempurature</h5>
				<p><strong><?php echo $user['temp']; ?></strong></p>
			</div>
			<div class="card bg-success text-white text-center p-1">
				<h5 style="padding-top: 16px;">pH</h5>
				<p><strong><?php echo $user['ph']; ?></strong></p>
			</div>
			<div class="card bg-warning text-white text-center p-1">
				<h5 style="padding-top: 16px;">Light Level</h5>
				<p><strong><?php echo $user['light']; ?></strong></p>
			</div>
		</div>
	</div>

	<div class="container px-xl-5" style="padding:20px; margin-top:10px;">
		<h5>Edit Environment Settings:</h5>
		<form method="post" class="px-xl-5" style="margin-top:30px;">
			<div class="row">
				<label for="plantSpecies" class="col-md-6">
					Plant Species
				</label>
			</div>
			<div class="row">
				<select type="text" class="form-control col-md-6" name="plantSpecies" id="plantSpecies" value="<?php echo $user['plantSpecies'] ?>">
					<?php foreach ($result as $row) : ?>
						<option><?php echo escape($row["common_name"]); ?></option>
					<?php endforeach; ?>
				</select>
			</div>
			<input class="btn btn-primary" type="submit" name="submit" value="Submit">
		</form>
	</div>

	<a href="index.php">Back to home</a>
</div>

<?php require "templates/footer.php"; ?>