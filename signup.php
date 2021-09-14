<?php 
session_start();

  include("connection.php");
  include("functions.php");


  if($_SERVER['REQUEST_METHOD'] == "POST")
  {
    //something was posted
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $email_address = $_POST['email_address'];
    $password = $_POST['password'];

    if(!empty($first_name)&& !empty($last_name) && !empty(email_address)  && !empty($password) )
    {

      //save to database
      $user_id = random_num(20);
      $query = "insert into knackpeople (first_name, last_name, email_address , password ,user_id) values ('$first_name', '$last_name', '$email_address', '$password', '$user_id')";

      mysqli_query($con, $query);

      header("Location: login.php");
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
  <link rel="icon" type="image/png" sizes="32x32" href="./images/favicon-32x32.png">
  
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