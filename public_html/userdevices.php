<?php
session_start();

if (isset($_SESSION['id'])) {
	try {
		require "../../private/config.php";
		require "../../private/common.php";

		$connection = new PDO($dsn, $username, $password, $options);
		$id = $_SESSION['id'];

		$sql = "SELECT devices.id, plants.plantName, devices.date FROM devices INNER JOIN plants ON devices.idealPlantID = plants.ID WHERE ownerID = :id";
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
	<h2>Your Devices</h2>

	<table class="table table-striped table-hover">
		<thead class="thead-dark">
			<tr>
				<th>#</th>
				<th>Plant Species</th>
				<th>Date</th>
				<th>Details</th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ($device as $row) : ?>
				<tr>
					<td><?php echo escape($row["id"]); ?></td>
					<td><?php echo escape($row["plantName"]); ?></td>
					<td><?php echo escape($row["date"]); ?> </td>
					<td><a type="button" class="btn btn-success" href="devicedetails.php?id=<?php echo escape($row["id"]); ?>">Details</a></td>
				</tr>
			<?php endforeach; ?>
		</tbody>
	</table>

	<a href="viewuserlist.php">Back to user list</a>
</div>