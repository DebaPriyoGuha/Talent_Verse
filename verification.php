<?php 

session_start();

  include("connection.php");
  include("functions.php");


  if($_SERVER['REQUEST_METHOD'] == "POST")
  {
    //something was posted
    // $email_address = $_POST['email_address'];
    // $password = $_POST['password'];
    $verification_code=$_POST['verification_code'];

    if(!empty($verification_code) )
    {

      //read from database
      $query = "select user_verify from users where verification_code = '$verification_code' limit 1";
      $result = mysqli_query($con, $query);
      //echo "$result";

      if(!$result)
      {

        //   $user_data = mysqli_fetch_assoc($result);
          
        //   $verify_password=  password_verify($password, $user_data['password']);

            // $_SESSION['username'] = $user_data['username'];

            $query = "update users set user_verify=1 where verification_code='$verification_code'";
      

            header("Location: index.php");
            die;
          
        
      }
      
      echo "Invalid Code";
    }else
    {
      echo "Invalid Code";
    }
  }

?>


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0"> <!-- displays site properly based on user's device -->
  <link rel="stylesheet" href="style for knackbook.css">
  <link rel="icon" type="image/png" sizes="32x32" href="./images/favicon.svg">
  
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
        <p>Every person is unique with their talent.<br>Show your talent to the world.</p>
      </div>
      <div class="right-col">
        <div class="top-box">
          <p>Check your email and put the verification code here</p>
        </div>
        <div class="form-container">
          <form method="post">
            
            <div class="field-group">
              <label for="verification_code">Verification Code</label>
              <input name='verification_code' id="verification_code" type="text" placeholder="Verification Code">
              <img src="./images/icon-error.svg" class="error-icon" alt="">
              <p class="error-text">Wrong Verification Code</p>              
            <!-- </div>
             <div class="field-group">
              <label for="password">Password Address</label>
              <input name='password' id="password" value="" type="password" placeholder="Password">
              <img src="./images/icon-error.svg" class="error-icon" alt="">
              <p class="error-text">Password cannot be empty</p>              
            </div> -->
            
            <div class="login-button"><button type="submit" value="Submit">SUBMIT</button></div>
            
            <!-- <div class="forget-pass"><p> <a href="https://www.facebook.com">Forgotten Password?<a></p> </div>
            
            <div class="or"><p>OR</p></div>
            
            <div class="create-button"> <a href="signup.php">Create New Account</a></div>  -->
            
            <!-- <p class="form-footer">By clicking the button, you are agreeing to our <span>Terms and Services</span></p> -->
          </form>
        </div>
      </div>
    </div>
  </section>

  

  
  
  <!-- <footer>
    <p class="attribution"> 
      Coded by <a href="https://www.youtube.com/watch?v=dQw4w9WgXcQ">Deba Priyo Guha</a>
    </p>
  </footer>
  <script src="./main.js"></script> -->
</body>
</html>