<?php

if (isset($_POST['submit'])) {
	try {
		require "../../private/config.php";
		require "../../private/common.php";

		$connection = new PDO($dsn, $username, $password, $options);

		$sql = "SELECT *
    			FROM users
    			WHERE firstname = :firstname";

		$firstname = $_POST['firstname'];

		$statement = $connection->prepare($sql);
		$statement->bindParam(':firstname', $firstname, PDO::PARAM_STR);
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
					<th>First Name</th>
					<th>Last Name</th>
					<th>Email Address</th>
					<th>Date</th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ($result as $row) { ?>
					<tr>
						<td><?php echo escape($row["id"]); ?></td>
						<td><?php echo escape($row["firstname"]); ?></td>
						<td><?php echo escape($row["lastname"]); ?></td>
						<td><?php echo escape($row["email"]); ?></td>
						<td><?php echo escape($row["date"]); ?> </td>
					</tr>
				<?php } ?>
			</tbody>
		</table>
	<?php } else { ?>
		> No results found for <?php echo escape($_POST['firstname']); ?>.
<?php }
} ?>

<h2>Find user based on first name</h2>

<form method="post">
	<label for="fisrtname">First Name</label>
	<input type="text" id="firstname" name="firstname" >
	<input type="submit" name="submit" value="View Results">
</form>

<a href="index.php">Back to home</a>

<?php require "templates/footer.php"; ?>