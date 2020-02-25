<?php
if (isset($_GET['id'])) {
	try {
		require "../../private/config.php";
		require "../../private/common.php";

		$connection = new PDO($dsn, $username, $password, $options);
		$id = $_GET['id'];

		$sql = "SELECT * FROM devices WHERE ownerID = :id";
		$statement = $connection->prepare($sql);
		$statement->bindValue(':id', $id);
		$statement->execute();

		$device = $statement->fetchAll();
	} catch (PDOException $error) {
		echo $sql . "<br>" . $error->getMessage();
	}
} else {
	echo "Something went wrong!";
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
					<td><?php echo escape($row["idealPlantSpecies"]); ?></td>
					<td><?php echo escape($row["date"]); ?> </td>
					<td><a type="button" class="btn btn-success" href="devicedetails.php?id=<?php echo escape($row["id"]); ?>">Details</a></td>
				</tr>
			<?php endforeach; ?>
		</tbody>
	</table>

	<a href="viewuserlist.php">Back to user list</a>
</div>