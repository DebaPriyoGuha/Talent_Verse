<?php 
session_start();

	include("connection.php");
	include("functions.php");

	$user_data = check_login($con);

?>

<!DOCTYPE html>
<html>
<head>
	<title>Welcome</title>
</head>
<body>

	<a href="logout.php">Logout</a>
	<h1>Show & learn something new!</h1>

	<br>
	Hello, <?php echo $user_data['first_name']; ?>
</body>
</html>