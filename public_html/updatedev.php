<?php

try {
  require "../../private/config.php";
  require "../../private/common.php";

  $connection = new PDO($dsn, $username, $password, $options);

  $sql = "SELECT * FROM devices";

  $statement = $connection->prepare($sql);
  $statement->execute();

  $result = $statement->fetchAll();
} catch (PDOException $error) {
  echo $sql . "<br>" . $error->getMessage();
}
?>

<?php require "templates/header.php"; ?>

<div class="container px-lg-5"  style="margin-top: 25px;">
  <h2>Update devices</h2>

  <table class="table table-striped table-hover">
    <thead class="thead-dark">
      <tr>
        <th>#</th>
        <th>Owner ID</th>
        <th>Plant Species</th>
        <th>Date</th>
        <th>Edit</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($result as $row) : ?>
        <tr>
          <td><?php echo escape($row["id"]); ?></td>
          <td><?php echo escape($row["ownerID"]); ?></td>
          <td><?php echo escape($row["plantSpecies"]); ?></td>
          <td><?php echo escape($row["date"]); ?> </td>
          <td><a type="button" class="btn btn-outline-primary" href="devicedetails.php?id=<?php echo escape($row["id"]); ?>">Edit</a></td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>

  <a href="index.php">Back to home</a>
</div>