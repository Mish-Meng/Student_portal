<?php
include 'connect.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $fullname = trim($_POST['fullname']);
    $email = trim($_POST['email']);
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if ($password !== $confirm_password) {
        $error = "Passwords do not match!";
    } else {

        // 🔐 hash password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // 🔑 generate verification token
        $token = bin2hex(random_bytes(32));

        // 🧾 insert user (NOT verified)
        $sql = "INSERT INTO users 
                (fullname, email, username, password, is_verified, verification_token)
                VALUES (?, ?, ?, ?, 0, ?)";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param(
            $stmt,
            "sssss",
            $fullname,
            $email,
            $username,
            $hashed_password,
            $token
        );

        if (mysqli_stmt_execute($stmt)) {

            // 📧 send verification email
            $mail = new PHPMailer(true);
            try {
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth = true;
                $mail->Username = 'schoolportalke@gmail.com';   // CHANGE
                $mail->Password = 'YOUR_APP_PASSWORD';     // CHANGE
                $mail->SMTPSecure = 'tls';
                $mail->Port = 587;

                $mail->setFrom('schoolportalke@gmail.com', 'School Portal');
                $mail->addAddress($email, $fullname);

                $verifyLink = "http://yourdomain.com/verify.php?token=$token"; // CHANGE DOMAIN

                $mail->isHTML(true);
                $mail->Subject = 'Verify Your Account';
                $mail->Body = "
                    <h3>Hello $fullname</h3>
                    <p>Please verify your account by clicking the link below:</p>
                    <a href='$verifyLink'>Verify Account</a>
                ";

                $mail->send();

                // redirect to login
                header("Location: login.php?msg=verify");
                exit();

            } catch (Exception $e) {
                $error = "Email not sent. Error: {$mail->ErrorInfo}";
            }

        } else {
            $error = "Database error: " . mysqli_error($conn);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Sign Up</title>
  <style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body {
      font-family: Arial, sans-serif;
      color: white;
      background: url('https://images.pexels.com/photos/3401403/pexels-photo-3401403.jpeg') no-repeat center center fixed;
      background-size: cover;
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh;
    }
    .form {
      width: 350px;
      background: rgba(0,0,0,0.8);
      border-radius: 12px;
      padding: 25px;
      text-align: center;
      backdrop-filter: blur(10px);
    }
    .form h2 {
      color: #ff7200;
      background-color: #fff;
      border-radius: 10px;
      margin-bottom: 20px;
      padding: 10px;
    }
    .form input {
      width: 90%;
      height: 40px;
      background: transparent;
      border: none;
      border-bottom: 1px solid #ff7200;
      color: #fff;
      font-size: 15px;
      margin: 15px 0;
      padding: 5px;
    }
    .form input:focus { outline: none; }
    ::placeholder { color: #fff; }
    .btnn {
      width: 90%;
      height: 45px;
      background: #ff7200;
      border: none;
      margin-top: 20px;
      font-size: 18px;
      border-radius: 10px;
      cursor: pointer;
      color: #fff;
      transition: 0.4s ease;
    }
    .btnn:hover { background: #fff; color: #ff7200; }
    .link { font-size: 15px; padding-top: 20px; }
    .link a { text-decoration: none; color: #ff7200; }
    .error {
      color: #ff7200;
      background: rgba(255,255,255,0.1);
      padding: 8px;
      margin-top: 10px;
      border-radius: 5px;
    }
  </style>
</head>
<body>
  <div class="form">
    <h2>Sign Up</h2>
    <?php if (!empty($error)) echo "<p class='error'>$error</p>"; ?>
    <form action="" method="POST">
      <input type="text" name="fullname" placeholder="Full Name" required>
      <input type="email" name="email" placeholder="Email" required>
      <input type="text" name="username" placeholder="Username" required>
      <input type="password" name="password" placeholder="Password" required>
      <input type="password" name="confirm_password" placeholder="Confirm Password" required>
      <button type="submit" class="btnn">Create Account</button>
    </form>
    <p class="link">Already have an account? <a href="login.php">Login here</a></p>
  </div>
</body>
</html>
