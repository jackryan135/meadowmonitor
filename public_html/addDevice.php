<?php
session_start();

if (isset($_SESSION['id'])) {
	if(isset($_POST['label'])){
	try {
		require "../../private/config.php";
		require "../../private/common.php";

		$connection = new PDO($dsn, $username, $password, $options);
		$id = $_SESSION['id'];
		$plantID = -1;

		$data = [
			"label"   => $_POST['label'],
			"ownerID" => $id,
			"plantID" => $plantID,
		];

		$sql = "INSERT INTO devices (label, ownerID, idealPlantID) VALUES (:label, :ownerID, :plantID);";
		$statement = $connection->prepare($sql);
		$statement->execute($data);

		header("location: userdevices.php");
	} catch (PDOException $error) {
		echo $sql . "<br>" . $error->getMessage();
	}
}
} else {
	header("location: login.php");
	exit;
}
?>

<?php require "templates/header.php"; ?>
<div class="container px-lg-5"  style="margin-top: 25px;">
	<h2>Add Device</h2>
	<form method="post" class="px-xl-5" style="margin-top:30px;">
		<h5>Enter desired nickname for this device:</h5>
		<input type="text" class="form-control" name="label" id="label">
		</input>
		<input class="btn btn-primary" type="submit" name="submit" value="Submit" style="margin-top: 10px; margin-right:auto;">
		<p style="margin-top:50px;">Once you hit submit, look at the device ID on the next page and enter it on the setup captive portal.</p>
	</form>
</div>
<?php require "templates/footer.php"; ?>