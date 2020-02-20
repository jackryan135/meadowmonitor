<?php

if (isset($_POST['submit'])) {
	try {
		require "../config.php";
		require "../common.php";

		$connection = new PDO($dsn, $username, $password, $options);

		$sql = "SELECT *
    			FROM devices
    			WHERE ID = :ID";

		$ID = $_POST['ID'];

		$statement = $connection->prepare($sql);
		$statement->bindParam(':ID', $ID, PDO::PARAM_STR);
		$statement->execute();

		$result = $statement->fetchAll();
	} catch (PDOException $error) {
		echo $sql . "<br>" . $error->getMessage();
	}
}
?>
<?php require "templates/header.php"; ?>

<?php
if (isset($_POST['submit'])) {
	if ($result && $statement->rowCount() > 0) { ?>
		<h2>Results</h2>

		<table class="table table-striped table-hover">
			<thead class="thead-dark">
				<tr>
					<th>#</th>
					<th>Owner ID</th>
					<th>Plant Type</th>
					<th>Date</th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ($result as $row) { ?>
					<tr>
						<td><?php echo escape($row["id"]); ?></td>
						<td><?php echo escape($row["ownerID"]); ?></td>
						<td><?php echo escape($row["plantSpecies"]); ?></td>
						<td><?php echo escape($row["date"]); ?> </td>
					</tr>
				<?php } ?>
			</tbody>
		</table>
	<?php } else { ?>
		> No results found for <?php echo escape($_POST['ID']); ?>.
<?php }
} ?>

<h2>Find user based on first name</h2>

<form method="post">
	<label for="ID">Device ID</label>
	<input type="text" id="ID" name="ID" >
	<input type="submit" name="submit" value="View Results">
</form>

<a href="index.php">Back to home</a>

<?php require "templates/footer.php"; ?>