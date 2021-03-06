<?php
session_start();

if (isset($_SESSION['id'])) {
	try {
		require "../../private/config.php";
		require "../../private/common.php";

		$connection = new PDO($dsn, $username, $password, $options);
		$id = $_SESSION['id'];

		$sql = "SELECT devices.id, devices.label, plants.plantName, devices.date FROM devices INNER JOIN plants ON devices.idealPlantID = plants.ID WHERE ownerID = :id";
		$statement = $connection->prepare($sql);
		$statement->bindValue(':id', $id);
		$statement->execute();

		$device = $statement->fetchAll();
	} catch (PDOException $error) {
		echo $sql . "<br>" . $error->getMessage();
	}
} else {
	header("location: login.php");
	exit;
}
?>

<?php require "templates/header.php"; ?>
<div class="container px-lg-5"  style="margin-top: 25px;">
<div class="row" style="margin-bottom: 30px;">
	<h2>Your Devices</h2>
	<a class="btn btn-secondary" href="addDevice.php" style="margin-left: auto; margin-right: 15px;">Add Device</a>
</div>
	<table class="table table-striped table-hover">
		<thead class="thead-dark">
			<tr>
				<th>Device ID</th>
				<th>Name</th>
				<th>Plant Species</th>
				<th>Details</th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ($device as $row) : ?>
				<tr>
					<td><?php echo escape($row["id"]); ?></td>
					<td><?php echo escape($row["label"]); ?></td>
					<td><?php echo escape($row["plantName"]); ?></td>
					<td><a type="button" class="btn btn-success" href="devicedetails.php?id=<?php echo escape($row["id"]); ?>">Details</a></td>
				</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
</div>
<?php require "templates/footer.php"; ?>