<?php
session_start();
include 'connect.php';

$message = "";
$success = false;

// Get token from URL
$token = $_GET['token'] ?? '';

if (empty($token)) {
    $message = "❌ Invalid verification link!";
} else {
    // Find teacher with this token
    $token = mysqli_real_escape_string($conn, $token);
    $result = mysqli_query($conn, "SELECT id, fullname, email, email_verified FROM teachers WHERE verification_token = '$token'");
    
    if (mysqli_num_rows($result) > 0) {
        $teacher = mysqli_fetch_assoc($result);
        
        if ($teacher['email_verified'] == 1) {
            $message = "✅ Your email has already been verified. You can now log in.";
            $success = true;
        } else {
            // Verify the email
            $update_sql = "UPDATE teachers SET email_verified = 1, verification_token = NULL WHERE verification_token = '$token'";
            if (mysqli_query($conn, $update_sql)) {
                $message = "✅ Email verified successfully! You can now log in to your account.";
                $success = true;
            } else {
                $message = "❌ Error verifying email: " . mysqli_error($conn);
            }
        }
    } else {
        $message = "❌ Invalid or expired verification token!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Email Verification</title>
  <link rel="stylesheet" href="admin.css">
</head>
<body>

<div class="admin-container">
  <div class="admin-header">
    <h2>Email Verification</h2>
    <p><?= $success ? 'Verification Complete' : 'Verification Status' ?></p>
  </div>

  <div style="text-align: center; padding: 20px 0;">
    <div style="font-size: 48px; margin-bottom: 20px;">
      <?= $success ? '✅' : '❌' ?>
    </div>
    <p style="color: <?= $success ? '#86efac' : '#fca5a5' ?>; font-size: 16px; margin-bottom: 30px;">
      <?= htmlspecialchars($message) ?>
    </p>
    
    <?php if($success): ?>
      <a href="teacher_signup.php" class="submit-btn" style="display: inline-block; text-decoration: none; width: auto; padding: 12px 30px;">
        Go to Login
      </a>
    <?php else: ?>
      <a href="teacher_signup.php" class="submit-btn" style="display: inline-block; text-decoration: none; width: auto; padding: 12px 30px; background: var(--muted);">
        Back to Signup
      </a>
    <?php endif; ?>
  </div>

  <div class="footer-note">
    <a href="staff_portal.php" style="color: var(--muted); text-decoration: none;">← Back to Portal</a>
  </div>
</div>

</body>
</html>

