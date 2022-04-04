<?php 
session_start();

  include("connection.php");
  include("functions.php");
  //include("php mailer.php");

  if($_SERVER['REQUEST_METHOD'] == "POST")
  {
    //something was posted
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $email_address = $_POST['email_address'];
    $password = $_POST['password'];
    $password_hash=password_hash($password, PASSWORD_DEFAULT);
    $password_asterisk=stringtoasterisk($password_hash);

    if(!empty($first_name)&& !empty($last_name) && !empty($email_address)  && !empty($password) )
    {

      //save to database
      $username = random_num(20);
      $query = "insert into users (first_name, last_name, email_address , password ,username) values ('$first_name', '$last_name', '$email_address', '$password_hash', '$username')";

      mysqli_query($con, $query);



      $verification_code= verification_code(6);
      $query="insert into users (verification_code) values ('$verification_code')";
      $_SESSION['code']=$verification_code;
      mysqli_query($con,$query);

      //$recipient = $email_address;
      $subject = "Verification Code";
      $message = "Your verication code is $verification_code";

  
      //$recipient="debapriyoguha@gmail.com";
      //$email_address=$recipient;
      //$subject= "hello";
      //$message= "ok gese";
      
      if (send_mail($email_address,$subject,$message))
      {
        //echo "verification code has been sent";
      }
      else
      {
        echo "not sent";
      }


      //header("Location: login.php");
      die;
    }

    else
    {
      echo "Please enter some valid information!";
    }
  }
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0"> 
  <link rel="stylesheet" href="style for knackbook.css">
  <link rel="icon" type="image/png" sizes="32x32" href="./images/favicon.svg">
  
  <title>knackbook | sign up</title>

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
        <p>Every person is unique with their talent.<br>Show your talent to the world.</p>
      </div>
      <div class="right-col">
        <div class="top-box">
          <p>Welcome to the Universe of Talented People</p>
        </div>
        <div class="form-container">
          
          <form method="post">
            
            <div class="field-group">
              <label for="first_name">First Name</label>
              <input name='first_name' id="first_name" type="text" placeholder="First Name">
              <img src="./images/icon-error.svg" class="error-icon" alt="">
              <p class="error-text">First Name cannot be empty</p>              
            </div>

            <div class="field-group">
              <label for="first_name">Last Name</label>
              <input name='last_name' id="last_name" type="text" placeholder="Last Name">
              <img src="./images/icon-error.svg" class="error-icon" alt="">
              <p class="error-text">Last Name cannot be empty</p>              
            </div>
            
            <div class="field-group">
              <label for="email_address">Email Address</label>
              <input name='email_address' id="email_address"  type="email_address" placeholder="Email Address">
              <img src="./images/icon-error.svg" class="error-icon" alt="">
              <p class="error-text">Invalid email</p>              
            </div>
            
            <div class="field-group">
              <label for="password">Password Address</label>
              <input name='password' id="password" type="password" placeholder="Password">
              <img src="./images/icon-error.svg" class="error-icon" alt="">
              <p class="error-text">Password cannot be empty</p>              
            </div>
            
            <div class="signup-button"><button type="submit">sign in </button></div>
            <div class="or"><p>OR</p></div>
            <div class="already-button"><a href="login.php">Already have an account</a></div>
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