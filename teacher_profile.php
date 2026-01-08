<?php
session_start();

// Protect teacher pages
if (!isset($_SESSION['teacher']) || $_SESSION['role'] != 'teacher') {
    header("Location: admin_login.php");
    exit();
}

include 'connect.php';

$teacher_id = $_SESSION['teacher_id'];
$message = "";

// Fetch teacher data
$teacher_query = mysqli_query($conn, "SELECT * FROM teachers WHERE id = $teacher_id");
$teacher = mysqli_fetch_assoc($teacher_query);

// Handle profile update
if (isset($_POST['update_profile'])) {
    $fullname = mysqli_real_escape_string($conn, $_POST['fullname']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $subject = mysqli_real_escape_string($conn, $_POST['subject']);
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    
    // Only update password if provided
    if (!empty($_POST['password'])) {
        $password = mysqli_real_escape_string($conn, $_POST['password']);
        $sql = "UPDATE teachers SET fullname='$fullname', email='$email', phone='$phone', subject='$subject', username='$username', password='$password' WHERE id=$teacher_id";
    } else {
        $sql = "UPDATE teachers SET fullname='$fullname', email='$email', phone='$phone', subject='$subject', username='$username' WHERE id=$teacher_id";
    }
    
    if (mysqli_query($conn, $sql)) {
        $message = "✅ Profile updated successfully!";
        // Update session
        $_SESSION['teacher_name'] = $fullname;
        // Refresh teacher data
        $teacher_query = mysqli_query($conn, "SELECT * FROM teachers WHERE id = $teacher_id");
        $teacher = mysqli_fetch_assoc($teacher_query);
    } else {
        $message = "❌ Failed to update profile: " . mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>My Profile - Teacher Portal</title>
<link rel="stylesheet" href="teacher_dashboard.css">
<style>
.profile-container {
  background: var(--surface);
  border: 1px solid var(--border);
  border-radius: 14px;
  padding: 28px;
  margin-top: 22px;
  backdrop-filter: blur(8px);
  box-shadow: var(--shadow);
  max-width: 600px;
}

.form-group {
  margin-bottom: 18px;
}

.form-group label {
  display: block;
  color: var(--muted);
  margin-bottom: 6px;
  font-size: 14px;
  font-weight: 500;
}

.form-group input {
  width: 100%;
  padding: 12px 14px;
  border: 1px solid var(--border);
  border-radius: 10px;
  background: rgba(0,0,0,0.25);
  color: #fff;
  font-size: 15px;
  outline: none;
  transition: border-color 0.2s ease, box-shadow 0.2s ease;
}

.form-group input:focus {
  border-color: var(--accent);
  box-shadow: 0 0 0 4px rgba(255,114,0,0.28);
}

.btn-primary {
  background: var(--accent);
  color: #000;
  border: none;
  padding: 12px 24px;
  border-radius: 10px;
  font-weight: 600;
  cursor: pointer;
  transition: background 0.2s ease;
  width: 100%;
  margin-top: 8px;
}

.btn-primary:hover {
  background: #ffa45c;
}

.btn-secondary {
  background: transparent;
  color: var(--accent);
  border: 2px solid var(--accent);
  padding: 10px 20px;
  border-radius: 10px;
  font-weight: 600;
  cursor: pointer;
  text-decoration: none;
  display: inline-block;
  margin-bottom: 18px;
  transition: all 0.2s ease;
}

.btn-secondary:hover {
  background: var(--accent);
  color: #000;
}

.message {
  padding: 12px 16px;
  border-radius: 10px;
  margin-bottom: 20px;
  background: rgba(0,0,0,0.4);
  border: 1px solid var(--border);
}

.success {
  background: rgba(34,197,94,0.2);
  border-color: rgba(34,197,94,0.5);
  color: #86efac;
}

.error {
  background: rgba(239,68,68,0.2);
  border-color: rgba(239,68,68,0.5);
  color: #fca5a5;
}
</style>
</head>
<body>

<!-- Sidebar -->
<div class="sidebar">
  <div>
    <h2>Teacher Panel</h2>
    <ul>
      <li><a href="teacher_dashboard.php">🏠 Dashboard</a></li>
      <li><a href="teacher_profile.php">👤 My Profile</a></li>
      <li><a href="teacher_results.php">📊 Manage Marks</a></li>
      <li><a href="teacher_classes.php">🏫 Class Assignments</a></li>
      <li><a href="teacher_attendance.php">✅ Attendance</a></li>
      <li><a href="teacher_students.php">👩‍🎓 View Students</a></li>
    </ul>
  </div>
  <form method="POST" action="teacher_dashboard.php">
    <button class="logout-btn" name="logout">Logout</button>
  </form>
</div>

<!-- Main -->
<div class="main">
  <div class="header">
    <h2>My Profile</h2>
    <p>View and update your profile information</p>
  </div>

  <a href="teacher_dashboard.php" class="btn-secondary">⬅ Back to Dashboard</a>

  <?php if($message): ?>
    <div class="message <?= strpos($message,'❌')!==false?'error':'success' ?>">
      <?= htmlspecialchars($message) ?>
    </div>
  <?php endif; ?>

  <div class="profile-container">
    <form method="POST">
      <div class="form-group">
        <label for="fullname">Full Name</label>
        <input type="text" id="fullname" name="fullname" value="<?= htmlspecialchars($teacher['fullname'] ?? '') ?>" required>
      </div>

      <div class="form-group">
        <label for="email">Email</label>
        <input type="email" id="email" name="email" value="<?= htmlspecialchars($teacher['email'] ?? '') ?>" required>
      </div>

      <div class="form-group">
        <label for="phone">Phone</label>
        <input type="text" id="phone" name="phone" value="<?= htmlspecialchars($teacher['phone'] ?? '') ?>" required>
      </div>

      <div class="form-group">
        <label for="subject">Subject</label>
        <input type="text" id="subject" name="subject" value="<?= htmlspecialchars($teacher['subject'] ?? '') ?>" required>
      </div>

      <div class="form-group">
        <label for="username">Username</label>
        <input type="text" id="username" name="username" value="<?= htmlspecialchars($teacher['username'] ?? '') ?>" required>
      </div>

      <div class="form-group">
        <label for="password">Password (leave blank to keep current password)</label>
        <input type="password" id="password" name="password" placeholder="Enter new password">
      </div>

      <button type="submit" name="update_profile" class="btn-primary">Update Profile</button>
    </form>
  </div>
</div>

</body>
</html>

