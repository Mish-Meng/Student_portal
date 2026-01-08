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

// Get teacher's assigned classes (full details)
$teacher_classes_query = mysqli_query($conn, "SELECT * FROM classes WHERE teacher LIKE '%$teacher_name%' ORDER BY grade ASC");
$teacher_classes_total = mysqli_num_rows($teacher_classes_query);

// Get teacher's assigned class grades
$teacher_class_grades = [];
mysqli_data_seek($teacher_classes_query, 0); // Reset pointer
while ($row = mysqli_fetch_assoc($teacher_classes_query)) {
    $teacher_class_grades[] = mysqli_real_escape_string($conn, $row['grade']);
}

// Get total students in teacher's classes
$students_total = 0;
if (!empty($teacher_class_grades)) {
    $class_list = "'" . implode("','", $teacher_class_grades) . "'";
    $students_query = mysqli_query($conn, "SELECT COUNT(*) as total FROM students WHERE class IN ($class_list)");
    $students_data = mysqli_fetch_assoc($students_query);
    $students_total = $students_data['total'] ?? 0;
}

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
    <?php if($teacher_classes_total > 0): ?>
      <div style="background: rgba(255,255,255,0.1); border-radius: 8px; padding: 12px; margin: 0 12px 16px 12px; border: 1px solid rgba(255,255,255,0.2);">
        <div style="font-size: 11px; color: rgba(255,255,255,0.7); margin-bottom: 6px; text-transform: uppercase; letter-spacing: 0.5px;">My Classes</div>
        <div style="font-size: 13px; font-weight: 600; color: #fff;">
          <?= $teacher_classes_total ?> Class<?= $teacher_classes_total > 1 ? 'es' : '' ?>
        </div>
        <a href="teacher_classes.php" style="color: rgba(255,255,255,0.8); font-size: 11px; text-decoration: none; margin-top: 4px; display: block;">View Details →</a>
      </div>
    <?php endif; ?>
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

  <!-- My Assigned Classes Section -->
  <?php if($teacher_classes_total > 0): ?>
  <div style="background: var(--surface); border: 1px solid var(--border); border-radius: 14px; padding: 20px; margin-top: 22px; backdrop-filter: blur(8px); box-shadow: var(--shadow);">
    <h3 style="color: var(--accent); margin-top: 0; margin-bottom: 16px; display: flex; align-items: center; gap: 8px;">
      🏫 My Assigned Classes
    </h3>
    <div style="display: flex; flex-wrap: wrap; gap: 10px;">
      <?php 
      mysqli_data_seek($teacher_classes_query, 0); // Reset pointer
      while($class = mysqli_fetch_assoc($teacher_classes_query)): 
      ?>
        <div style="background: rgba(255,114,0,0.2); border: 2px solid var(--accent); border-radius: 8px; padding: 10px 16px; display: inline-block;">
          <strong style="color: var(--accent);"><?= htmlspecialchars($class['grade']) ?></strong>
          <span style="color: var(--muted); font-size: 12px; display: block; margin-top: 4px;"><?= htmlspecialchars($class['subjects']) ?></span>
        </div>
      <?php endwhile; ?>
    </div>
    <p style="color: var(--muted); font-size: 13px; margin-top: 12px; margin-bottom: 0;">
      💡 You can only view and manage students from these classes. <a href="teacher_classes.php" style="color: var(--accent); text-decoration: none;">View Details →</a>
    </p>
  </div>
  <?php else: ?>
  <div style="background: rgba(239,68,68,0.2); border: 1px solid rgba(239,68,68,0.5); border-radius: 14px; padding: 20px; margin-top: 22px; backdrop-filter: blur(8px);">
    <p style="color: #fca5a5; margin: 0;">
      ⚠️ No classes assigned yet. Please contact the administrator to get class assignments.
    </p>
  </div>
  <?php endif; ?>

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

