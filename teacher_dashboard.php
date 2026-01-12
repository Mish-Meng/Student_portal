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
    <ul>
      <li><a href="teacher_dashboard.php">🏠 Dashboard</a></li>
      <li><a href="teacher_profile.php">👤 My Profile</a></li>
      <li><a href="teacher_results.php">📊 Manage Marks</a></li>
      <li><a href="teacher_classes.php">🏫 Class Assignments</a></li>
      <li><a href="teacher_timetable.php">📅 My Timetable</a></li>
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

  <!-- Today's Schedule Card -->
  <?php
  // Get today's schedule for the teacher
  $today = date('l'); // e.g., "Monday"
  $today_classes = [];
  if (!empty($teacher_class_grades)) {
    $class_list = "'" . implode("','", $teacher_class_grades) . "'";
    $today_query = mysqli_query($conn, 
      "SELECT * FROM timetable 
       WHERE (class_grade IN ($class_list) OR teacher LIKE '%$teacher_name%')
       AND day_of_week = '$today'
       ORDER BY time_slot ASC"
    );
    while ($row = mysqli_fetch_assoc($today_query)) {
      $today_classes[] = $row;
    }
  }
  ?>
  
  <?php if(!empty($today_classes)): ?>
    <div style="background: var(--surface); border: 1px solid var(--border); border-radius: 14px; padding: 20px; margin-top: 22px; backdrop-filter: blur(8px); box-shadow: var(--shadow);">
      <h3 style="color: var(--accent); margin-top: 0; margin-bottom: 16px; font-size: 20px;">
        📅 Today's Schedule (<?= $today ?>)
      </h3>
      <div style="display: grid; gap: 12px;">
        <?php foreach($today_classes as $class): ?>
          <div style="background: rgba(255,114,0,0.15); border: 1px solid rgba(255,114,0,0.3); border-radius: 8px; padding: 12px;">
            <div style="display: flex; justify-content: space-between; align-items: start; flex-wrap: wrap; gap: 8px;">
              <div>
                <div style="font-weight: 600; color: var(--accent); font-size: 14px; margin-bottom: 4px;">
                  🕐 <?= htmlspecialchars($class['time_slot']) ?>
                </div>
                <div style="font-weight: 600; font-size: 15px; margin-bottom: 4px;">
                  📚 <?= htmlspecialchars($class['subject']) ?>
                </div>
                <div style="font-size: 13px; color: var(--muted);">
                  🏫 <?= htmlspecialchars($class['class_grade']) ?>
                  <?php if(!empty($class['room'])): ?>
                    | 🚪 <?= htmlspecialchars($class['room']) ?>
                  <?php endif; ?>
                </div>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
      <div style="margin-top: 16px; text-align: center;">
        <a href="teacher_timetable.php" style="color: var(--accent); text-decoration: none; font-weight: 600; font-size: 14px;">
          View Full Timetable →
        </a>
      </div>
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
      <h3>My Timetable</h3>
      <p>View your weekly schedule</p>
      <a href="teacher_timetable.php" class="view-btn">View Timetable</a>
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

