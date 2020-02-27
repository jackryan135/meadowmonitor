<?php
require "../../private/config.php";

if (isset($_GET['id'])) {
	try {
		$id = $_GET['id'];
		$updatesql = "SELECT date, ph, temp, light, moisture FROM data WHERE deviceID = :id and date = (SELECT max(date) FROM data WHERE deviceID = :id)";
		$updateconnection = new PDO($dsn, $username, $password, $options);
		$updatestate = $updateconnection->prepare($updatesql);
		$updatestate->bindValue(':id', $id);
		$updatestate->execute();
		$update = $updatestate->fetch(PDO::FETCH_ASSOC);

		echo json_encode($update, JSON_NUMERIC_CHECK);
	} catch (PDOException $error) {
		echo $sql . "<br>" . $error->getMessage();
	}
} else {
	echo "Something went wrong!";
	exit;
}
