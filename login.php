<?php 

//session_start();

  include("session-file.php");
  include("functions.php");


  if($_SERVER['REQUEST_METHOD'] == "POST")
  {
    //something was posted
    $email_address = $_POST['email_address'];
    $password = $_POST['password'];

    if(!empty($email_address) && !empty($password) )
    {

      //read from database
      $query = "select * from users where email_address = '$email_address' limit 1";
      $result = mysqli_query($con, $query);

      if($result)
      {
        if($result && mysqli_num_rows($result) > 0)
        {

          $user_data = mysqli_fetch_assoc($result);
          
          $verify_password=  password_verify($password, $user_data['password']);
          if($verify_password)
          {

            $_SESSION['username'] = $user_data['username'];
            header("Location: index.php");
            die;
          }
        }
      }
      
      echo "wrong email or password!";
    }else
    {
      echo "wrong email or password!";
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
  
  <title>knackbook | log in</title>

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
          <p>Welcome Back to the Universe</p>
        </div>
        <div class="form-container">
          <form method="post">
            
            <div class="field-group">
              <label for="email_address">Email Address</label>
              <input name='email_address' id="email_address" type="email" placeholder="Email Address">
              <img src="./images/icon-error.svg" class="error-icon" alt="">
              <p class="error-text">Invalid Email</p>              
            </div>
            <div class="field-group">
              <label for="password">Password Address</label>
              <input name='password' id="password" value="" type="password" placeholder="Password">
              <img src="./images/icon-error.svg" class="error-icon" alt="">
              <p class="error-text">Password cannot be empty</p>              
            </div>
            
            <div class="login-button"><button type="submit" value="Login">log in</button></div>
            
            <div class="forget-pass"><p> <a href="https://www.facebook.com">Forgotten Password?<a></p> </div>
            
            <div class="or"><p>OR</p></div>
            
            <div class="create-button"> <a href="signup.php">Create New Account</a></div>
            
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
  <script src="./main.js"></script>
</body>
</html>