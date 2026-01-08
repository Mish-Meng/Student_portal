<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login - School Portal</title>
  <link rel="stylesheet" href="index.css">
</head>
<body>
  <div class="main">
    <div class="navbar">
      <div class="icon">
        <h2 class="logo">School Portal</h2>
      </div>

      <div class="search">
        <input class="srch" type="search" placeholder="Type to search">
        <a href="#"><button class="btn">Search</button></a>
      </div>
    </div>

    <div class="content">
      <h1>Welcome Back<br><span>Login</span><br>to Your Account</h1>
      <p class="par">Access your school portal account to manage payments, updates, and school information.<br>
        Secure, reliable, and easy to use.</p>

      <button class="cn"><a href="signup.php">Sign Up</a></button>

      <div class="form">
        <h2>Login Here</h2>
        <form action="login_process.php" method="POST">
          <input type="text" name="username" placeholder="Enter Username" required>
          <input type="password" name="password" placeholder="Enter Password" required>
          <button type="submit" class="btnn">Login</button>
        </form>

        <p class="link">Don't have an account?<br>
          <a href="signup.php">Sign up here</a>
        </p>
        <p class="liw">Log in with</p>

        <div class="icon">
          <a href="#"><ion-icon name="logo-facebook"></ion-icon></a>
          <a href="#"><ion-icon name="logo-instagram"></ion-icon></a>
          <a href="#"><ion-icon name="logo-google"></ion-icon></a>
          <a href="#"><ion-icon name="logo-twitter"></ion-icon></a>
        </div>
      </div>
    </div>
  </div>

  <script src="https://unpkg.com/ionicons@5.4.0/dist/ionicons.js"></script>
</body>
</html>
