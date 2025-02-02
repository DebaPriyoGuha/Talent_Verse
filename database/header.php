<?php 

    include 'session-file.php';

    if(isset($_SESSION['username'])){
        $userLoggedIn = $_SESSION['username'];
        $user_details_query = mysqli_query($con, "SELECT * FROM users WHERE username='$userLoggedIn'");
        $user = mysqli_fetch_array($user_details_query);
    }
    elseif ($userLoggedIn == 'admin') {
        header("Location: admin_home.php");
    }
    else{
        header("Location: login.php");
    }

    

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- link allfiles -->
    <link rel="stylesheet" type="text/css" href="assets/style.css">
    <script>
    < style src = "assets/js/jquery-3.5.1.min.js" ></ style>
    </script>
    <link rel="stylesheet" href="assets/fontawesome-free-5.15.1-web/css/all.css">
    <link rel="shortcut icon" href="images/favicon.svg" type="image/x-icon">
    <link rel="stylesheet" href="assets/bootsrap.min.css">

    <title>knackbook</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
</head>

<body>

    <div class="header_bar">
        <div class="logo">
            <a href="index.php" style="text-decoration: none; color: #44c2d8;"><img src="images/favicon.svg" alt="O"
                    style="height: 40px; width: 100px; margin: 18px -60px -10px 30px;">
                <span style="font-family: Roboto;/*! text-decoration: none; */font-size: 26px;">knackbook</span></a>
        </div>

        <div class="nav-center">
            <div class="dropdown">
                <span><img src="<?php echo $user['profile_pic']; ?>" style="margin-bottom: 3px;"></span>
                <div class="dropdown-content">
                    <div class="dropdown-a">
                        <h5><a href="<?php echo $userLoggedIn; ?>">
                                <?php echo "@".$user ['username']?></a></h5>

                        <a href="request.php"> <i class="fas fa-user-plus fa-lg" style="margin-right: 3px;"></i>
                            Requests</a>

                        <hr>

                        <a href="account_settings.php"> <i class="fas fa-cog fa-lg" style="margin-right: 3px;"></i>
                            Settings</a>

                        <hr>

                        <a href="logout.php"> <i class="fas fa-sign-out-alt fa-lg" style="margin-right: 3px;"></i>
                            Logout</a>
                    </div>
                </div>
                <?php echo "<br>"."Hello ".$user['first_name']; ?><?php echo "!";?>

            </div>


            <nav>


            <a href="index.php"> <i class="fas fa-home fa-lg"></i></a>

            <a href="messages.php"> <i class="fas fa-envelope fa-lg"></i></a>
        </nav>
        </div>

        

        

        <img src="images/icons/menu.png">


    </div>