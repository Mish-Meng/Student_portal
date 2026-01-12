<?php
session_start();

// Protect teacher pages
if (!isset($_SESSION['teacher']) || $_SESSION['role'] != 'teacher') {
    header("Location: admin_login.php");
    exit();
}

include 'connect.php';

// Create timetable table if it doesn't exist
$create_timetable_table = "CREATE TABLE IF NOT EXISTS timetable (
    id INT AUTO_INCREMENT PRIMARY KEY,
    class_grade VARCHAR(255) NOT NULL,
    day_of_week ENUM('Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday') NOT NULL,
    time_slot VARCHAR(50) NOT NULL,
    subject VARCHAR(255) NOT NULL,
    teacher VARCHAR(255) NOT NULL,
    room VARCHAR(100) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_class_grade (class_grade),
    INDEX idx_day (day_of_week),
    INDEX idx_teacher (teacher),
    UNIQUE KEY unique_class_day_time (class_grade, day_of_week, time_slot)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
mysqli_query($conn, $create_timetable_table);

$teacher_id = $_SESSION['teacher_id'];
$teacher_name = $_SESSION['teacher_name'];

// Helper function
function h($value) {
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

// Get teacher's assigned classes
$teacher_classes_query = mysqli_query($conn, "SELECT grade FROM classes WHERE teacher LIKE '%$teacher_name%'");
$teacher_class_grades = [];
while ($row = mysqli_fetch_assoc($teacher_classes_query)) {
    $teacher_class_grades[] = mysqli_real_escape_string($conn, $row['grade']);
}

// Fetch timetable entries where this teacher is teaching
$timetable = [];
if (!empty($teacher_class_grades)) {
    $class_list = "'" . implode("','", $teacher_class_grades) . "'";
    $timetable_query = mysqli_query($conn, 
        "SELECT * FROM timetable 
         WHERE (class_grade IN ($class_list) OR teacher LIKE '%$teacher_name%')
         ORDER BY FIELD(day_of_week, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'), time_slot ASC"
    );
    while ($row = mysqli_fetch_assoc($timetable_query)) {
        $timetable[] = $row;
    }
}

// Group timetable by day
$timetable_by_day = [
    'Monday' => [],
    'Tuesday' => [],
    'Wednesday' => [],
    'Thursday' => [],
    'Friday' => [],
    'Saturday' => []
];

foreach ($timetable as $entry) {
    $day = $entry['day_of_week'];
    if (isset($timetable_by_day[$day])) {
        $timetable_by_day[$day][] = $entry;
    }
}

// Get today's schedule
$today = date('l'); // e.g., "Monday"
$today_schedule = $timetable_by_day[$today] ?? [];
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>My Timetable - Teacher Portal</title>
<link rel="stylesheet" href="teacher_dashboard.css">
<style>
:root{
  --accent:#ff7200;
  --overlay:rgba(0,0,0,0.55);
  --surface:rgba(255,255,255,0.12);
  --border:rgba(255,255,255,0.18);
  --muted:rgba(229,231,235,0.7);
}

.timetable-container {
  background: var(--surface);
  border: 1px solid var(--border);
  border-radius: 14px;
  padding: 28px;
  margin-top: 22px;
  backdrop-filter: blur(8px);
  box-shadow: 0 10px 24px rgba(0,0,0,0.35);
}

.today-schedule {
  background: rgba(255,114,0,0.2);
  border: 2px solid var(--accent);
  border-radius: 12px;
  padding: 20px;
  margin-bottom: 24px;
}

.today-schedule h3 {
  color: var(--accent);
  margin-bottom: 16px;
  font-size: 20px;
}

.timetable-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
  gap: 20px;
  margin-top: 20px;
}

.day-card {
  background: rgba(0,0,0,0.25);
  border: 1px solid var(--border);
  border-radius: 12px;
  padding: 20px;
}

.day-header {
  color: var(--accent);
  font-size: 18px;
  font-weight: 700;
  margin-bottom: 16px;
  padding-bottom: 12px;
  border-bottom: 2px solid var(--accent);
}

.class-entry {
  background: rgba(255,114,0,0.15);
  border: 1px solid rgba(255,114,0,0.3);
  border-radius: 8px;
  padding: 12px;
  margin-bottom: 12px;
}

.class-entry:last-child {
  margin-bottom: 0;
}

.class-time {
  font-weight: 600;
  color: var(--accent);
  font-size: 13px;
  margin-bottom: 6px;
}

.class-subject {
  font-weight: 600;
  font-size: 15px;
  margin-bottom: 4px;
}

.class-class {
  font-size: 13px;
  color: var(--muted);
  margin-bottom: 4px;
}

.class-room {
  font-size: 12px;
  color: var(--muted);
  opacity: 0.8;
}

.empty-state {
  text-align: center;
  padding: 40px 20px;
  color: var(--muted);
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
  transition: all .2s ease;
}

.btn-secondary:hover {
  background: var(--accent);
  color: #000;
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
      <li><a href="teacher_timetable.php">📅 My Timetable</a></li>
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
    <div>
      <h2>My Timetable</h2>
      <p>Your class schedule for the week</p>
    </div>
  </div>

  <a href="teacher_dashboard.php" class="btn-secondary">⬅ Back to Dashboard</a>

  <div class="timetable-container">
    <!-- Today's Schedule -->
    <?php if(!empty($today_schedule)): ?>
      <div class="today-schedule">
        <h3>📅 Today's Classes (<?= $today ?>)</h3>
        <?php foreach($today_schedule as $entry): ?>
          <div class="class-entry">
            <div class="class-time">🕐 <?= h($entry['time_slot']) ?></div>
            <div class="class-subject">📚 <?= h($entry['subject']) ?></div>
            <div class="class-class">🏫 Class: <?= h($entry['class_grade']) ?></div>
            <?php if(!empty($entry['room'])): ?>
              <div class="class-room">🚪 Room: <?= h($entry['room']) ?></div>
            <?php endif; ?>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

    <!-- Weekly Timetable -->
    <h3 style="color: var(--accent); margin-bottom: 20px; font-size: 22px;">📋 Weekly Timetable</h3>
    
    <?php if(empty($timetable)): ?>
      <div class="empty-state">
        <p>No timetable entries found for your classes yet.</p>
        <p style="margin-top: 8px; font-size: 14px;">The administrator will add timetable entries for your classes.</p>
      </div>
    <?php else: ?>
      <div class="timetable-grid">
        <?php foreach($timetable_by_day as $day => $entries): ?>
          <?php if(!empty($entries)): ?>
            <div class="day-card">
              <div class="day-header"><?= h($day) ?></div>
              <?php foreach($entries as $entry): ?>
                <div class="class-entry">
                  <div class="class-time">🕐 <?= h($entry['time_slot']) ?></div>
                  <div class="class-subject">📚 <?= h($entry['subject']) ?></div>
                  <div class="class-class">🏫 <?= h($entry['class_grade']) ?></div>
                  <?php if(!empty($entry['room'])): ?>
                    <div class="class-room">🚪 Room: <?= h($entry['room']) ?></div>
                  <?php endif; ?>
                </div>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</div>

</body>
</html>

