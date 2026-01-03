<?php
session_start();

// 🔒 Protect teacher dashboard from direct access
if (!isset($_SESSION['teacher']) || $_SESSION['role'] != 'teacher') {
    header("Location: admin_login.php");
    exit();
}

// 🧩 Database connection
include 'connect.php';

$teacher_id = $_SESSION['teacher_id'];
$teacher_name = $_SESSION['teacher_name'];

// Get teacher's assigned classes
$teacher_classes_query = mysqli_query($conn, "SELECT COUNT(*) as total FROM classes WHERE teacher LIKE '%$teacher_name%'");
$teacher_classes_data = mysqli_fetch_assoc($teacher_classes_query);
$teacher_classes_total = $teacher_classes_data['total'] ?? 0;

// Get total students (for reference)
$students_total = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM students"))['total'] ?? 0;

// 🚪 Logout
if (isset($_POST['logout'])) {
    session_destroy();
    header("Location: admin_login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Teacher Dashboard - Langata Road Primary & Junior School</title>
<link rel="stylesheet" href="teacher_dashboard.css">
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
  <form method="POST">
    <button class="logout-btn" name="logout">Logout</button>
  </form>
</div>

<!-- Main -->
<div class="main">
  <div class="header">
    <div>
      <h2>Welcome, <?php echo htmlspecialchars($teacher_name); ?> 👋</h2>
      <p>Langata Road Primary & Junior School - Teacher Portal</p>
    </div>
  </div>

  <div class="cards">
    <div class="card">
      <h3>My Profile</h3>
      <p>View and edit your profile</p>
      <a href="teacher_profile.php" class="view-btn">View Profile</a>
    </div>
    <div class="card">
      <h3>Manage Marks</h3>
      <p>Add or update student marks</p>
      <a href="teacher_results.php" class="view-btn">Manage Marks</a>
    </div>
    <div class="card">
      <h3>Class Assignments</h3>
      <p>Manage your assigned classes</p>
      <span><?php echo $teacher_classes_total; ?> Classes</span>
      <a href="teacher_classes.php" class="view-btn">View Classes</a>
    </div>
    <div class="card">
      <h3>Attendance</h3>
      <p>Record student attendance</p>
      <a href="teacher_attendance.php" class="view-btn">Take Attendance</a>
    </div>
    <div class="card">
      <h3>Students</h3>
      <p>View student information</p>
      <span><?php echo $students_total; ?> Students</span>
      <a href="teacher_students.php" class="view-btn">View Students</a>
    </div>
  </div>
</div>

</body>
</html>

