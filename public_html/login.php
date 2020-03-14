<?php
// Initialize the session
session_start();

// Check if the user is already logged in, if yes then redirect him to welcome page
if(isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true){
    header("location: userdevices.php");
    exit;
}
 
// Include config file
require "../../private/config.php";
 
// Define variables and initialize with empty values
$email = $userpassword = "";
$username_err = $password_err = "";
 
// Processing form data when form is submitted
if($_SERVER["REQUEST_METHOD"] == "POST"){
	$mysqli = new PDO($dsn, $username, $password, $options);
 
    // Check if username is empty
    if(empty(trim($_POST["email"]))){
        $username_err = "Please enter email.";
    } else{
		$email = trim($_POST["email"]);
    }
    
    // Check if password is empty
    if(empty(trim($_POST["userpassword"]))){
        $password_err = "Please enter your password.";
    } else{
        $userpassword = trim($_POST["userpassword"]);
    }
    
    // Validate credentials
    if(empty($username_err) && empty($password_err)){
        // Prepare a select statement
        $sql = "SELECT id, email, password FROM users WHERE email = :email";
        
        if($stmt = $mysqli->prepare($sql)){
			$param_email = $email;
            // Bind variables to the prepared statement as parameters
			$stmt->bindValue(":email", $param_email);
			
            // Attempt to execute the prepared statement
            if($stmt->execute()){
                // Store result
				$data = $stmt->fetch();
                
                // Check if username exists, if yes then verify password
                if($data != NULL){                 
                    // Bind result variables
					$id = $data["id"];
					$email = $data["email"];


                    if(password_verify($userpassword, $data["password"])){
                            // Password is correct, so start a new session
                        session_start();
                            
                            // Store data in session variables
                        $_SESSION["loggedin"] = true;
                        $_SESSION["id"] = $id;
                        $_SESSION["email"] = $email;                            
                            
                            // Redirect user to welcome page
						header("location: userdevices.php");
                    } else{
                            // Display an error message if password is not valid
                        $password_err = "The password you entered was not valid.";
                    }
                } else{
                    // Display an error message if username doesn't exist
                    $username_err = "No account found with that username.";
                }
            } else{
                echo "Oops! Something went wrong. Please try again later.";
            }

        }
    }
    
}
?>
 
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">
    <title>Login</title>
	<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css" integrity="sha384-Vkoo8x4CGsO3+Hhxv8T/Q5PaXtkKtu6ug5TOeNV6gBiFeWPGFN9MuhOf23Q9Ifjh" crossorigin="anonymous">
    <link href="https://fonts.googleapis.com/css?family=Amatic+SC&display=swap" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.2.1.min.js" crossorigin="anonymous"></script>

    <!-- Custom styles for this template -->
    <link href="css/style.css" rel="stylesheet">
    <style type="text/css">
        body{ font: 14px sans-serif; }
    </style>
</head>
<body class="home">
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
                    <li class="nav-item active">
                        <a class="nav-link" href="login.php">Login
						<span class="sr-only">(current)</span>
						</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="register.php">Register</a>
                </ul>
            </div>
        </div>
    </nav>
    <div class="wrapper login">
        <h2>Login</h2>
        <p>Please fill in your credentials to login.</p>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <div class="form-group <?php echo (!empty($username_err)) ? 'has-error' : ''; ?>">
                <label>Email</label>
                <input type="text" name="email" class="form-control" value="<?php echo $email; ?>">
                <span class="help-block"><?php echo $username_err; ?></span>
            </div>    
            <div class="form-group <?php echo (!empty($password_err)) ? 'has-error' : ''; ?>">
                <label>Password</label>
                <input type="password" name="userpassword" class="form-control">
                <span class="help-block"><?php echo $password_err; ?></span>
            </div>
            <div class="form-group">
                <input type="submit" class="btn btn-primary" value="Login">
            </div>
            <p>Don't have an account? <a href="register.php">Sign up now</a>.</p>
        </form>
    </div>  
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js" integrity="sha384-Q6E9RHvbIyZFJoft+2mJbHaEWldlvI9IOYy5n3zV9zzTtmI3UksdQRVvoxMfooAo" crossorigin="anonymous"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js" integrity="sha384-wfSDF2E50Y2D1uUdj0O3uMBJnjuUD4Ih7YwaYd1iqfktj0Uod8GCExl3Og8ifwB6" crossorigin="anonymous"></script>  
</body>
</html>