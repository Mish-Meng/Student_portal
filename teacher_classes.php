<?php
session_start();

// Protect teacher pages
if (!isset($_SESSION['teacher']) || $_SESSION['role'] != 'teacher') {
    header("Location: admin_login.php");
    exit();
}

include 'connect.php';

$teacher_id = $_SESSION['teacher_id'];
$teacher_name = $_SESSION['teacher_name'];
$message = "";

// Helper function
function h($value) {
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

// Fetch classes assigned to this teacher
$search = trim($_GET['search'] ?? '');
if ($search) {
    $classes_query = mysqli_query($conn, "SELECT * FROM classes WHERE teacher LIKE '%$teacher_name%' AND (grade LIKE '%$search%' OR subjects LIKE '%$search%') ORDER BY grade ASC");
} else {
    $classes_query = mysqli_query($conn, "SELECT * FROM classes WHERE teacher LIKE '%$teacher_name%' ORDER BY grade ASC");
}

// Get students count for each class
$class_students = [];
$all_students = mysqli_query($conn, "SELECT class FROM students");
while ($student = mysqli_fetch_assoc($all_students)) {
    $class = $student['class'];
    if (!isset($class_students[$class])) {
        $class_students[$class] = 0;
    }
    $class_students[$class]++;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>My Classes - Teacher Portal</title>
<link rel="stylesheet" href="teacher_dashboard.css">
<style>
:root{
  --accent:#ff7200;
  --overlay:rgba(0,0,0,0.55);
  --surface:rgba(255,255,255,0.12);
  --border:rgba(255,255,255,0.18);
  --muted:rgba(229,231,235,0.7);
}

.classes-container {
  background: var(--surface);
  border: 1px solid var(--border);
  border-radius: 14px;
  padding: 28px;
  margin-top: 22px;
  backdrop-filter: blur(8px);
  box-shadow: 0 10px 24px rgba(0,0,0,0.35);
}

.class-card {
  background: rgba(0,0,0,0.25);
  border: 1px solid var(--border);
  border-radius: 12px;
  padding: 20px;
  margin-bottom: 16px;
  transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.class-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 20px rgba(0,0,0,0.4);
}

.class-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 12px;
}

.class-title {
  color: var(--accent);
  font-size: 20px;
  font-weight: 600;
  margin: 0;
}

.class-info {
  color: var(--muted);
  margin: 4px 0;
}

.search-bar {
  display: flex;
  gap: 10px;
  margin-bottom: 20px;
}

.search-bar input {
  flex: 1;
  padding: 12px 14px;
  border: 1px solid var(--border);
  border-radius: 10px;
  background: rgba(0,0,0,0.25);
  color: #fff;
  font-size: 15px;
}

.search-bar button {
  background: var(--accent);
  color: #000;
  border: none;
  padding: 12px 24px;
  border-radius: 10px;
  font-weight: 600;
  cursor: pointer;
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
}

.empty-state {
  text-align: center;
  color: var(--muted);
  padding: 40px 20px;
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
    <h2>My Class Assignments</h2>
    <p>Classes assigned to <?= h($teacher_name) ?></p>
  </div>

  <a href="teacher_dashboard.php" class="btn-secondary">⬅ Back to Dashboard</a>

  <div class="classes-container">
    <form method="GET" class="search-bar">
      <input type="text" name="search" placeholder="Search by grade or subject..." value="<?= h($search) ?>">
      <button type="submit">Search</button>
    </form>

    <?php if(mysqli_num_rows($classes_query) > 0): ?>
      <?php 
      mysqli_data_seek($classes_query, 0);
      while($class = mysqli_fetch_assoc($classes_query)): 
        $student_count = $class_students[$class['grade']] ?? 0;
      ?>
        <div class="class-card">
          <div class="class-header">
            <h3 class="class-title"><?= h($class['grade']) ?></h3>
            <span style="color: var(--muted);"><?= $student_count ?> Students</span>
          </div>
          <div class="class-info">
            <strong>Subjects:</strong> <?= h($class['subjects']) ?>
          </div>
          <div class="class-info">
            <strong>Time:</strong> <?= h($class['time']) ?>
          </div>
          <div class="class-info">
            <strong>Teacher:</strong> <?= h($class['teacher']) ?>
          </div>
        </div>
      <?php endwhile; ?>
    <?php else: ?>
      <div class="empty-state">
        <p>No classes assigned to you yet.</p>
        <p style="margin-top: 8px; font-size: 14px;">Contact the administrator to get class assignments.</p>
      </div>
    <?php endif; ?>
  </div>
</div>

</body>
</html>

