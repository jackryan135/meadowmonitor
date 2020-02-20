<?php

try {
	require "../config.php";
	require "../common.php";
  
	$connection = new PDO($dsn, $username, $password, $options);
  
	$sql = "SELECT * FROM users";
  
	$statement = $connection->prepare($sql);
	$statement->execute();
  
	$result = $statement->fetchAll();
  
  } catch(PDOException $error) {
	echo $sql . "<br>" . $error->getMessage();
  }
?>

<?php require "templates/header.php"; ?>

<h2>Update users</h2>

<table class="table table-striped table-hover">
  <thead class="thead-dark">
    <tr>
      <th>#</th>
      <th>First Name</th>
      <th>Last Name</th>
      <th>Email Address</th>
	  <th>Date</th>
	  <th>Edit</th>
    </tr>
  </thead>
  <tbody>
  <?php foreach ($result as $row) : ?>
    <tr>
      <td><?php echo escape($row["id"]); ?></td>
      <td><?php echo escape($row["firstname"]); ?></td>
      <td><?php echo escape($row["lastname"]); ?></td>
      <td><?php echo escape($row["email"]); ?></td>
	  <td><?php echo escape($row["date"]); ?> </td>
	  <td><a type="button" class="btn btn-outline-primary" href="update-single.php?id=<?php echo escape($row["id"]); ?>">Edit</a></td>
  </tr>
  <?php endforeach; ?>
  </tbody>
</table>

<a href="index.php">Back to home</a>