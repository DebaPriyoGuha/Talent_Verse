<?php 

	session_start();

  //include("connection.php");
  //include("functions.php");
  //include ("signup.php");


$verification_code=$_SESSION['code'];



  if($_SERVER['REQUEST_METHOD'] == "POST")
  {
    //something was posted
    $verification= $_POST['verification'];
    if($verification_code===$verification)
    {
    	echo "you are verified";
    }
    else
    {
    	echo "wrong code";
    }
  }
    
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0"> <!-- displays site properly based on user's device -->
  <link rel="stylesheet" href="style for knackbook.css">
  <link rel="icon" type="image/png" sizes="32x32" href="./images/favicon-32x32.png">
  
  <title>knackbook | verification</title>

  <style>
    .attribution { font-size: 11px; text-align: center; }
    .attribution a { color: hsl(228, 45%, 44%); }
  </style>
</head>
<body>
  <section id="intro">
    <div class="container">
      <div class="left-col">
        <h1>knackbook</h1>
        <p>Put your verification code to join the new universe</p>
      </div>
      <div class="right-col">
        <div class="top-box">
          <p>A verification code has just been sent to your email. Please check.<br>The code will expire after 90 seconds. Then you will be taken to signup page where you can put your same credentials again.</p>
        </div>
        <div class="form-container">
          <form method="post">
            
            <div class="field-group">
              <label for="email_address">Email Address</label>
              <input name='verification' id="first_name" type="text" placeholder="Verification Code">
              <img src="./images/icon-error.svg" class="error-icon" alt="">
              <p class="error-text">Invalid Email</p>              
            </div>
            <!--<div class="field-group">
              <label for="password">Password Address</label>
              <input name='password' id="password" value="" type="password" placeholder="Password">
              <img src="./images/icon-error.svg" class="error-icon" alt="">
              <p class="error-text">Password cannot be empty</p>              
            </div>
            -->
            <div class="login-button"><button type="submit" value="Login">verify</button></div>
            
            
          
            
            <p class="form-footer">By clicking the button, you are agreeing to our <span>Terms and Services</span></p>
          </form>
        </div>
      </div>
    </div>
  </section>

  

  
  
  <footer>
    <p class="attribution"> 
      Coded by <a href="https://www.youtube.com/watch?v=dQw4w9WgXcQ">Deba Priyo Guha</a>
    </p>
  </footer>
</body>
</html>