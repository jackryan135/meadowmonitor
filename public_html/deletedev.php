<?php

require "../../private/config.php";
require "../../private/common.php";

if (isset($_GET["id"])) {
  try {
    $connection = new PDO($dsn, $username, $password, $options);

    $id = $_GET["id"];

    $sql = "DELETE FROM devices WHERE id = :id";

    $statement = $connection->prepare($sql);
    $statement->bindValue(':id', $id);
    $statement->execute();

    $success = "Device successfully deleted";
  } catch(PDOException $error) {
    echo $sql . "<br>" . $error->getMessage();
  }
}

try {
  $connection = new PDO($dsn, $username, $password, $options);

  $sql = "SELECT * FROM devices";

  $statement = $connection->prepare($sql);
  $statement->execute();

  $result = $statement->fetchAll();
} catch(PDOException $error) {
  echo $sql . "<br>" . $error->getMessage();
}
?>

<?php require "templates/header.php"; ?>

<h2>Delete devices</h2>

<table class="table table-striped table-hover">
  <thead class="thead-dark">
    <tr>
      <th>#</th>
      <th>Owner ID</th>
      <th>Plant Species</th>
	  <th>Date</th>
	  <th>Delete</th>
    </tr>
  </thead>
  <tbody>
  <?php foreach ($result as $row) : ?>
    <tr>
      <td><?php echo escape($row["id"]); ?></td>
      <td><?php echo escape($row["ownerID"]); ?></td>
      <td><?php echo escape($row["plantSpecies"]); ?></td>
	  <td><?php echo escape($row["date"]); ?> </td>
	  <td><a type="button" class="btn btn-outline-danger" href="deletedev.php?id=<?php echo escape($row["id"]); ?>">Delete</a></td>
  </tr>
  <?php endforeach; ?>
  </tbody>
</table>

<a href="index.php">Back to home</a>