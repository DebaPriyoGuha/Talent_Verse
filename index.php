<?php 

    include 'session-file.php';
    // include("links.php");

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



<?php
    // include 'database/header.php';
    include 'database/classes/User.php';
    include 'database/classes/Post.php';
    include 'database/classes/Message.php';
    //include 'session-file.php'

    if(isset($_POST['post'])){
        $uploadOk = 1;
        $imageName = $_FILES['fileToUpload']['name'];
        $errorMessage = "";
        
        if($imageName != ""){
            $targetDir = "assets/images/posts/";
            $imageName = $targetDir .uniqid() . basename($imageName);
            $imageFileType = pathinfo($imageName, PATHINFO_EXTENSION);
            
            if($uploadOk){
                if(move_uploaded_file($_FILES['fileToUpload']['tmp_name'], $imageName)){
                    //image Upload Okey
                    $errorMessage = "uploaded";
                }
                else{
                    $uploadOk = 0;
                    $errorMessage = "fail to upload";
                }
            }
        }
        
        if($uploadOk){

            // $update_cover_pic = mysqli_query($con, "insert into posts (image) values ($imageName) where added_by='$userLoggedIn'") or die(mysqli_error($con));


            $post = new Post($con, $userLoggedIn);
            $post->submitPost($_POST['post_text'], $imageName);
        }
        else{
            echo "<div style='text-align: center;' class='alert alert-danger'> $errorMessage </div>";
        }
    }

    $user_detail_query = mysqli_query($con,"select * from users where username='$userLoggedIn'");
    $user_array = mysqli_fetch_array($user_detail_query);
    $num_friends = (substr_count($user_array['friend_array'],","))-1;

?>



<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- link allfiles -->
    <link rel="stylesheet" type="text/css" href="assets/style.css">
    <script>
    < style src = "assets/js/jquery-3.5.1.min.js" > < / style>
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





        

    </div>
    




    <div class="index-wrapper">
        <div class="info-box">
            <div class="info-inner">
                <div class="info-in-head">
                    <a href="<?php echo $userLoggedIn; ?>"><img src="<?php echo $user['cover_pic']; ?>"></a>
                </div>
                <div class="info-in-body">
                    <div class="in-b-box">
                        <div class="in-b-img">
                            <a href="<?php echo $userLoggedIn; ?>"><img src="<?php echo $user['profile_pic']; ?>"></a>
                        </div>
                    </div>
                    <div class="info-body-name">
                        <div class="in-b-name">
                            <div><a
                                    href="<?php echo $userLoggedIn; ?>"><?php echo $user['first_name'] . " " . $user['last_name']; ?></a>
                            </div>
                            <span><small><a
                                        href="<?php echo $userLoggedIn; ?>"><?php echo "@" . $user['username'] ?></a></small></span>
                        </div>
                    </div>
                </div>
                <div class="info-in-footer">
                    <div class="number-wrapper">
                        <div class="num-box">
                            <div class="num-head">
                                POSTS
                            </div>
                            <div class="num-body">
                                <?php echo $user['num_posts']; ?>
                            </div>
                        </div>
                        <div class="num-box">
                            <div class="num-head">
                                LIKES
                            </div>
                            <div class="num-body">
                                <span class="count-likes">
                                    <?php echo $user['num_likes']; ?>
                                </span>
                            </div>
                        </div>
                        <div class="num-box">
                            <div class="num-head">
                                Friends
                            </div>
                            <div class="num-body">
                                <?php echo $num_friends ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="post-wrap">
            <div class="post-inner">
                <div class="post-h-left">
                    <div class="post-h-img">
                        <a href="<?php echo $userLoggedIn; ?>"><img src="<?php echo $user['profile_pic'] ?>"></a>
                    </div>
                </div>


                <div class="post-body">
                    <form action="index.php" method="post" enctype="multipart/form-data">
                        <textarea class="status" name="post_text" id="post_text" placeholder="Show something new!"
                            rows="4" cols="50"></textarea>
                        <div class="hash-box">
                            <ul>
                            </ul>
                        </div>
                        Select image to upload:
                        <input type="file" name="fileToUpload" id="fileToUpload">
                        <!-- <input type="submit" value="Upload Image" name="submit"> -->
                        <input id="sub-btn" type="submit" name="post" value="SHARE">
                    </form>

                </div>





            </div>
        </div>
    </div>


    <div class="show_post">
        <?php 
            $post = new Post($con, $userLoggedIn) ;
            $post->indexPosts();
        ?>
    </div>

</body>

</html>