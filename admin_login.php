<?php
session_start();

// Database connection
include 'connect.php';

$error = "";

// When login form is submitted (Admin only)
if (isset($_POST['login'])) {
  $username = $_POST['username'];
  $password = $_POST['password'];

  // Check admin credentials
  $sql = "SELECT * FROM admins WHERE username=? AND password=?";
  $stmt = mysqli_prepare($conn, $sql);
  mysqli_stmt_bind_param($stmt, "ss", $username, $password);
  mysqli_stmt_execute($stmt);
  $result = mysqli_stmt_get_result($stmt);

  if (mysqli_num_rows($result) > 0) {
    $row = mysqli_fetch_assoc($result);
    $_SESSION['admin'] = $username;
    $_SESSION['role'] = 'admin';
    $_SESSION['user_id'] = $row['id'];
    header("Location: dashboard.php");
    exit();
  } else {
    $error = "Invalid admin username or password!";
  }
  mysqli_stmt_close($stmt);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin/Teacher Login</title>
  <link rel="stylesheet" href="admin.css">
</head>
<body>

<div class="admin-container">
  <div class="admin-header">
    <h2>Admin Login</h2>
    <p>Administrator access only</p>
  </div>
  <form action="" method="POST" novalidate>
    <input type="hidden" name="role" value="admin">
    <div class="form-group">
      <label class="label" for="username">Username</label>
      <input class="input" id="username" type="text" name="username" placeholder="Enter your username" required>
    </div>
    <div class="form-group">
      <label class="label" for="password">Password</label>
      <input class="input" id="password" type="password" name="password" placeholder="Enter your password" required>
    </div>
    <button class="submit-btn" type="submit" name="login">Sign In</button>
    <?php if(isset($error)) echo "<p class='error'>$error</p>"; ?>
  </form>
  <div class="footer-note">
    <a href="staff_portal.php" style="color: var(--muted); text-decoration: none;">← Back to Portal Selection</a>
  </div>
</div>

</body>
</html>
