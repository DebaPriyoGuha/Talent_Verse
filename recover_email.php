<?php

	// session_start();

?>

<!doctype html>
<html lang="en">

	<head>
  
		<title>Recover Email</title>
		
		<?php include 'links.php'; ?>
		
		<!-- <?php include 'css/style.php'; ?> -->

		<?php include 'functions.php'; ?>

		<link rel="stylesheet" type="text/css" href="bootstrap.css">
		
	</head>
	
	<body>
	
		<?php
		
			include 'session-file.php';
		
			if(isset($_POST['submit'])){
				
				$email = mysqli_real_escape_string($con, $_POST['email_address']);
				
				$emailquery = "select * from users where email_address='$email'";
				$query = mysqli_query($con, $emailquery);
				
				$emailcount = mysqli_num_rows($query);
				
				if($emailcount){
					
					$userdata = mysqli_fetch_array($query);
					
					$username = $userdata['username'];
					$token = $userdata['token'];
					
					$subject = "Password Reset";
					$body = "Hi, $username. Click here to reset your password
					http://localhost/knackbook%20working%20folder/reset_password.php?token=$token";
					$sender_email = "From: prabirkantiguha@gmail.com";
					
					if(send_mail($email, $subject, $body, $sender_email)){
						$_SESSION['msg'] = "check you mail to reset your password $email";
						header('location: index.php');
					}else{
						echo "Email sending failed...";
					}	
				}else{
					echo "No email found";
				}
			}
		
		?>
	
		<div class="card bg-light">
		
		<article class="card-body mx-auto" style="max-width: 400px;">
	
			<h4 class="card-title mt-3 text-center">Recover Your Account</h4>
		
			<p class="text-center">Please fill email id correctly</p>
			
		<form action="<?php echo htmlentities($_SERVER['PHP_SELF']);?>" method="POST">
			
			<div class="form-group input-group">
			
				<div class="input-group-prepend">
				
					<span class="input-group-text"><i class="fa fa-envelope"></i></span>
				
				</div>
				
				<input type="email_address" class="form-control" name="email_address" placeholder="Email Address" required autocomplete="off">
			
			</div>
			
			<div class="form-group">
			
				<button type="submit" class="btn btn-block btn-primary mb-3" name="submit">Send Email</button>
				
			</div>
				
		</form>
		
		<p class="text-center">Have an account?<a href="index.php" class="p-2">Login</a></p>
		
		</article>
		
		</div>
	
	</body>
	
</html>