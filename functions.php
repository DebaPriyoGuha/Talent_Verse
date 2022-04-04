<?php

function check_login($con)
{

	if(isset($_SESSION['username']))
	{

		$id = $_SESSION['username'];
		$query = "select * from users where username = '$id' limit 1";

		$result = mysqli_query($con,$query);
		if($result && mysqli_num_rows($result) > 0)
		{

			$user_data = mysqli_fetch_assoc($result);
			return $user_data;
		}
	}

	//redirect to login
	header("Location: login.php");
	die;

}

function random_num($length)
{

	$text = "";
	if($length < 5)
	{
		$length = 5;
	}

	$len = rand(4,$length);

	for ($i=0; $i < $len; $i++) { 
		# code...

		$text .= rand(0,9);
	}

	return $text;
}




function verification_code($length)
{
	$text="";
	for ($i=0; $i <$length ; $i++) { 
		// code...
		$text .= rand(0,9);
	}
	return $text;
}

function stringtoasterisk($string)
{
	return str_repeat("*", strlen($string));
}

?>


<?php
  use PHPMailer\PHPMailer\PHPMailer;
  use PHPMailer\PHPMailer\Exception;
  require 'PHPMailer/src/Exception.php';
  require 'PHPMailer/src/PHPMailer.php';
  require 'PHPMailer/src/SMTP.php';

function send_mail($recipient,$subject,$message)
{

  $mail = new PHPMailer();
  $mail->IsSMTP();

  $mail->SMTPDebug  = 0;  
  $mail->SMTPAuth   = TRUE;
  $mail->SMTPSecure = "tls";
  $mail->Port       = 587;
  $mail->Host       = "smtp.gmail.com";
  $mail->Username   = "prabirkantiguha@gmail.com";
  $mail->Password   = "eievokkipqxcewnc";

  $mail->IsHTML(true);
  $mail->AddAddress($recipient, "");
  $mail->SetFrom("knackbook@gmail.com", "knackbook");
  $mail->AddReplyTo("reply-to-email", "reply-to-name");
  //$mail->AddCC("cc-recipient-email", "cc-recipient-name");
  $mail->Subject = $subject;
  $content = $message;

  $mail->MsgHTML($content); 
  if(!$mail->Send()) {
    echo "Error while sending Email.";
    var_dump($mail);
    return false;
  } else {
    echo "Verification code has been sent to your email";
    return true;
  }
  

}

?>