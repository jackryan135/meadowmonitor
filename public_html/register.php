<?php
// Include config file
require "../../private/config.php";

// Define variables and initialize with empty values
$email = $userpassword = $confirm_password = "";
$email_err = $password_err = $confirm_password_err = "";
$first = $last = "";

// Processing form data when form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
	$mysqli = new PDO($dsn, $username, $password, $options);

	$first = $_POST["firstname"];
	$last = $_POST["lastname"];
	// Validate username
	if (empty(trim($_POST["email"]))) {
		$email_err = "Please enter an email.";
	} else {
		// Prepare a select statement
		$sql = "SELECT id FROM users WHERE email = :email";

		if ($stmt = $mysqli->prepare($sql)) {
			// Set parameters
			$param_email = trim($_POST["email"]);
			$stmt->bindValue(':email', $param_email);

			// Attempt to execute the prepared statement
			if ($stmt->execute()) {
				// store result
				$data = $stmt->fetchAll();

				if (!empty($data)) {
					$email_err = "This email is already in use.";
				} else {
					$email = trim($_POST["email"]);
				}
			} else {
				echo "Oops! Something went wrong. Please try again later.";
			}

		}
	}

	// Validate password
	if (empty(trim($_POST["password"]))) {
		$password_err = "Please enter a password.";
	} elseif (strlen(trim($_POST["password"])) < 6) {
		$password_err = "Password must have atleast 6 characters.";
	} else {
		$userpassword = trim($_POST["password"]);
	}

	// Validate confirm password
	if (empty(trim($_POST["confirm_password"]))) {
		$confirm_password_err = "Please confirm password.";
	} else {
		$confirm_password = trim($_POST["confirm_password"]);
		if (empty($password_err) && ($userpassword != $confirm_password)) {
			$confirm_password_err = "Password did not match.";
		}
	}

	// Check input errors before inserting in database
	if (empty($email_err) && empty($password_err) && empty($confirm_password_err)) {

		// Prepare an insert statement
		$sql = "INSERT INTO users (email, password, firstname, lastname) VALUES (:email, :pass, :first, :last)";

		if ($stmt = $mysqli->prepare($sql)) {
			$param_email = $email;
			$param_password = password_hash($userpassword, PASSWORD_DEFAULT);
			// Bind variables to the prepared statement as parameters
			$stmt->bindValue(":email", $param_email);
			$stmt->bindValue(":pass", $param_password);
			$stmt->bindValue(":first",  $first);
			$stmt->bindValue(":last",  $last);

			// Attempt to execute the prepared statement
			if ($stmt->execute()) {
				// Redirect to login page
				header("location: login.php");
			} else {
				echo "Something went wrong. Please try again later.";
			}

		}
	}

}
?>

<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="UTF-8">
	<title>Sign Up</title>
	<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css" integrity="sha384-Vkoo8x4CGsO3+Hhxv8T/Q5PaXtkKtu6ug5TOeNV6gBiFeWPGFN9MuhOf23Q9Ifjh" crossorigin="anonymous">
	<link href="https://fonts.googleapis.com/css?family=Amatic+SC&display=swap" rel="stylesheet">
	<script src="https://code.jquery.com/jquery-3.2.1.min.js" crossorigin="anonymous"></script>

    <!-- Custom styles for this template -->
    <link href="css/style.css" rel="stylesheet">
</head>

<body>
<nav class="navbar navbar-expand-lg navbar-light fixed-bottom" style="background-color: #72f29d;">
        <div class="container">
            <a class="navbar-brand" href="index.html" style="font-family: 'Amatic SC', cursive; font-size:30px; font-weight:bold;"><img class="navbar-icon" src="flower.png" width="40" height="30" alt="flower icon">Meadow Monitor</a>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarResponsive" aria-controls="navbarResponsive" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>
            <div class="collapse navbar-collapse" id="navbarResponsive">
                <ul class="navbar-nav ml-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.html">Home
            			</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="login.php">Login</a>
                    </li>
                    <li class="nav-item active">
                        <a class="nav-link" href="register.php">Register
						<span class="sr-only">(current)</span>
						</a>
                </ul>
            </div>
        </div>
    </nav>
	<div class="wrapper register">
		<h2>Sign Up</h2>
		<p>Please fill this form to create an account.</p>
		<form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
			<div class="form-group">
				<label>First Name</label>
				<input type="text" name="firstname" class="form-control" value="<?php echo $first; ?>">
			</div>
			<div class="form-group">
				<label>Last Name</label>
				<input type="text" name="lastname" class="form-control" value="<?php echo $last; ?>">
			</div>
			<div class="form-group <?php echo (!empty($email_err)) ? 'has-error' : ''; ?>">
				<label>Email</label>
				<input type="text" name="email" class="form-control" value="<?php echo $email; ?>">
				<span class="help-block"><?php echo $email_err; ?></span>
			</div>
			<div class="form-group <?php echo (!empty($password_err)) ? 'has-error' : ''; ?>">
				<label>Password</label>
				<input type="password" name="password" class="form-control" value="<?php echo $userpassword; ?>">
				<span class="help-block"><?php echo $password_err; ?></span>
			</div>
			<div class="form-group <?php echo (!empty($confirm_password_err)) ? 'has-error' : ''; ?>">
				<label>Confirm Password</label>
				<input type="password" name="confirm_password" class="form-control" value="<?php echo $confirm_password; ?>">
				<span class="help-block"><?php echo $confirm_password_err; ?></span>
			</div>
			<div class="form-group">
				<input type="submit" class="btn btn-primary" value="Submit">
				<input type="reset" class="btn btn-default" value="Reset">
			</div>
			<p>Already have an account? <a href="login.php">Login here</a>.</p>
		</form>
	</div>
</body>

</html>