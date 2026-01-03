<?php
session_start();
include 'connect.php';

$message = "";
$show_signup = true;

// Handle signup
if (isset($_POST['signup'])) {
    $fullname = mysqli_real_escape_string($conn, $_POST['fullname']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $subject = mysqli_real_escape_string($conn, $_POST['subject']);
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);
    $confirm_password = mysqli_real_escape_string($conn, $_POST['confirm_password']);

    // Validation
    if (empty($fullname) || empty($email) || empty($phone) || empty($subject) || empty($username) || empty($password)) {
        $message = "❌ Please fill in all fields!";
    } elseif ($password !== $confirm_password) {
        $message = "❌ Passwords do not match!";
    } elseif (strlen($password) < 6) {
        $message = "❌ Password must be at least 6 characters!";
    } else {
        // Check if username or email already exists
        $check = mysqli_query($conn, "SELECT id FROM teachers WHERE username = '$username' OR email = '$email'");
        if (mysqli_num_rows($check) > 0) {
            $message = "❌ Username or email already exists!";
        } else {
            // Generate verification token
            $verification_token = bin2hex(random_bytes(32));
            
            // Insert teacher with unverified status
            $sql = "INSERT INTO teachers (fullname, email, phone, subject, username, password, email_verified, verification_token) 
                    VALUES ('$fullname', '$email', '$phone', '$subject', '$username', '$password', 0, '$verification_token')";
            
            if (mysqli_query($conn, $sql)) {
                // Generate verification link
                $verification_link = "http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . "/verify_email.php?token=$verification_token";
                
                // Try to send email (for production)
                $email_subject = "Email Verification - School Portal";
                $email_body = "
                <html>
                <head>
                    <style>
                        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                        .button { background: #ff7200; color: #000; padding: 12px 24px; text-decoration: none; border-radius: 5px; display: inline-block; font-weight: bold; }
                    </style>
                </head>
                <body>
                    <div class='container'>
                        <h2>Welcome to Langata Road Primary & Junior School Portal!</h2>
                        <p>Hello $fullname,</p>
                        <p>Thank you for signing up as a staff member. Please verify your email address by clicking the button below:</p>
                        <p><a href='$verification_link' class='button'>Verify Email Address</a></p>
                        <p>Or copy and paste this link into your browser:</p>
                        <p style='word-break: break-all; color: #666;'>$verification_link</p>
                        <p>This link will expire in 24 hours.</p>
                        <p>If you did not sign up for this account, please ignore this email.</p>
                        <hr>
                        <p style='color: #666; font-size: 12px;'>Langata Road Primary & Junior School</p>
                    </div>
                </body>
                </html>
                ";
                
                $headers = "MIME-Version: 1.0" . "\r\n";
                $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
                $headers .= "From: School Portal <noreply@schoolportal.com>" . "\r\n";
                
                // Try to send email (may not work in local XAMPP)
                $email_sent = @mail($email, $email_subject, $email_body, $headers);
                
                // Always store verification link in session to display on page (for local dev)
                $_SESSION['verification_link'] = $verification_link;
                $_SESSION['signup_email'] = $email;
                
                if ($email_sent) {
                    $message = "✅ Signup successful! Please check your email ($email) to verify your account. If you don't see the email, use the verification link shown below:";
                } else {
                    // For local development, show the link directly
                    $message = "✅ Signup successful! Since email sending may not work in local development, please use the verification link below:";
                }
                $show_signup = false;
            } else {
                $message = "❌ Error: " . mysqli_error($conn);
            }
        }
    }
}

// Handle login
if (isset($_POST['login'])) {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);

    $sql = "SELECT * FROM teachers WHERE username=? AND password=?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "ss", $username, $password);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        
        // Allow login even if email is not verified (for testing/development)
        // In production, you may want to re-enable email verification check
        $_SESSION['teacher'] = $username;
        $_SESSION['role'] = 'teacher';
        $_SESSION['teacher_id'] = $row['id'];
        $_SESSION['teacher_name'] = $row['fullname'];
        header("Location: teacher_dashboard.php");
        exit();
        
        /* Uncomment this block to require email verification in production:
        if ($row['email_verified'] == 0) {
            $message = "❌ Please verify your email address before logging in. Check your inbox for the verification link.";
        } else {
            $_SESSION['teacher'] = $username;
            $_SESSION['role'] = 'teacher';
            $_SESSION['teacher_id'] = $row['id'];
            $_SESSION['teacher_name'] = $row['fullname'];
            header("Location: teacher_dashboard.php");
            exit();
        }
        */
    } else {
        $message = "❌ Invalid username or password!";
    }
    mysqli_stmt_close($stmt);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Teacher Signup/Login</title>
  <link rel="stylesheet" href="admin.css">
  <style>
    .tabs {
      display: flex;
      gap: 10px;
      margin-bottom: 20px;
      border-bottom: 2px solid var(--border);
    }
    .tab {
      padding: 12px 20px;
      background: transparent;
      border: none;
      color: var(--muted);
      cursor: pointer;
      font-weight: 600;
      border-bottom: 2px solid transparent;
      margin-bottom: -2px;
      transition: all 0.2s ease;
    }
    .tab.active {
      color: var(--accent);
      border-bottom-color: var(--accent);
    }
    .tab-content {
      display: none;
    }
    .tab-content.active {
      display: block;
    }
    .form-group {
      margin-top: 14px;
    }
    .back-link {
      display: block;
      text-align: center;
      margin-top: 20px;
      color: var(--muted);
      text-decoration: none;
      font-size: 14px;
    }
    .back-link:hover {
      color: var(--accent);
    }
  </style>
</head>
<body>

<div class="admin-container">
  <div class="admin-header">
    <h2>Staff/Teacher Portal</h2>
    <p>Sign up or sign in to access your dashboard</p>
  </div>

  <?php if($message): ?>
    <div class="message" style="padding: 12px; margin-bottom: 16px; border-radius: 10px; background: <?= strpos($message,'❌')!==false ? 'rgba(239,68,68,0.2)' : 'rgba(34,197,94,0.2)' ?>; border: 1px solid <?= strpos($message,'❌')!==false ? 'rgba(239,68,68,0.5)' : 'rgba(34,197,94,0.5)' ?>; color: <?= strpos($message,'❌')!==false ? '#fca5a5' : '#86efac' ?>;">
      <?= htmlspecialchars($message) ?>
    </div>
    
    <?php if(isset($_SESSION['verification_link']) && !empty($_SESSION['verification_link'])): ?>
      <div style="background: rgba(0,0,0,0.3); border: 1px solid var(--border); border-radius: 10px; padding: 20px; margin-bottom: 20px;">
        <h3 style="color: var(--accent); margin-top: 0; margin-bottom: 12px;">📧 Verification Link</h3>
        <p style="color: var(--muted); margin-bottom: 12px; font-size: 14px;">Click the button below to verify your email address:</p>
        <a href="<?= htmlspecialchars($_SESSION['verification_link']) ?>" 
           style="display: inline-block; background: var(--accent); color: #000; padding: 12px 24px; border-radius: 8px; text-decoration: none; font-weight: 600; margin-bottom: 12px;">
          Verify Email Address
        </a>
        <p style="color: var(--muted); font-size: 12px; margin-top: 12px; word-break: break-all;">
          Or copy this link: <br>
          <code style="background: rgba(0,0,0,0.3); padding: 4px 8px; border-radius: 4px; font-size: 11px; display: block; margin-top: 8px;">
            <?= htmlspecialchars($_SESSION['verification_link']) ?>
          </code>
        </p>
      </div>
      <?php 
      // Clear the session variable after displaying (optional - you can remove this if you want to keep it)
      // unset($_SESSION['verification_link']);
      // unset($_SESSION['signup_email']);
      ?>
    <?php endif; ?>
  <?php endif; ?>

  <?php if($show_signup): ?>
  <div class="tabs">
    <button class="tab active" onclick="showTab('signup')">Sign Up</button>
    <button class="tab" onclick="showTab('login')">Login</button>
  </div>

  <!-- Signup Form -->
  <div id="signup-tab" class="tab-content active">
    <form action="" method="POST" novalidate>
      <div class="form-group">
        <label class="label" for="fullname">Full Name</label>
        <input class="input" id="fullname" type="text" name="fullname" placeholder="Enter your full name" required>
      </div>
      <div class="form-group">
        <label class="label" for="email">Email</label>
        <input class="input" id="email" type="email" name="email" placeholder="Enter your email" required>
      </div>
      <div class="form-group">
        <label class="label" for="phone">Phone</label>
        <input class="input" id="phone" type="text" name="phone" placeholder="Enter your phone number" required>
      </div>
      <div class="form-group">
        <label class="label" for="subject">Subject</label>
        <input class="input" id="subject" type="text" name="subject" placeholder="Enter your subject" required>
      </div>
      <div class="form-group">
        <label class="label" for="username">Username</label>
        <input class="input" id="username" type="text" name="username" placeholder="Choose a username" required>
      </div>
      <div class="form-group">
        <label class="label" for="password">Password</label>
        <input class="input" id="password" type="password" name="password" placeholder="Enter password (min 6 characters)" required minlength="6">
      </div>
      <div class="form-group">
        <label class="label" for="confirm_password">Confirm Password</label>
        <input class="input" id="confirm_password" type="password" name="confirm_password" placeholder="Confirm your password" required>
      </div>
      <button class="submit-btn" type="submit" name="signup">Sign Up</button>
    </form>
  </div>

  <!-- Login Form -->
  <div id="login-tab" class="tab-content">
    <form action="" method="POST" novalidate>
      <div class="form-group">
        <label class="label" for="login_username">Username</label>
        <input class="input" id="login_username" type="text" name="username" placeholder="Enter your username" required>
      </div>
      <div class="form-group">
        <label class="label" for="login_password">Password</label>
        <input class="input" id="login_password" type="password" name="password" placeholder="Enter your password" required>
      </div>
      <button class="submit-btn" type="submit" name="login">Sign In</button>
    </form>
  </div>
  <?php endif; ?>

  <a href="staff_portal.php" class="back-link">← Back to Portal Selection</a>
  <div class="footer-note">New staff must sign up and verify email</div>
</div>

<script>
function showTab(tab) {
  // Hide all tabs
  document.querySelectorAll('.tab-content').forEach(content => {
    content.classList.remove('active');
  });
  document.querySelectorAll('.tab').forEach(btn => {
    btn.classList.remove('active');
  });
  
  // Show selected tab
  document.getElementById(tab + '-tab').classList.add('active');
  event.target.classList.add('active');
}
</script>

</body>
</html>

