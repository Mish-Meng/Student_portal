<?php
session_start();

// Ensure the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
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

// Get student information
$user_id = $_SESSION['user_id'];
$student_class = null;

// Try to get student's class from users table or students table
if (isset($_SESSION['adm_no']) && !empty($_SESSION['adm_no'])) {
    $adm_no = mysqli_real_escape_string($conn, $_SESSION['adm_no']);
    $student_query = mysqli_query($conn, "SELECT class FROM students WHERE adm_no = '$adm_no'");
    if ($student_row = mysqli_fetch_assoc($student_query)) {
        $student_class = $student_row['class'];
    }
}

// If not found, try direct match
if (!$student_class) {
    $student_query = mysqli_query($conn, "SELECT class FROM students WHERE id = $user_id");
    if ($student_row = mysqli_fetch_assoc($student_query)) {
        $student_class = $student_row['class'];
    }
}

// Method 3: Try matching by fullname
if (!$student_class && isset($_SESSION['fullname'])) {
    $fullname = mysqli_real_escape_string($conn, $_SESSION['fullname']);
    $student_query = mysqli_query($conn, "SELECT class FROM students WHERE fullname = '$fullname' LIMIT 1");
    if ($student_row = mysqli_fetch_assoc($student_query)) {
        $student_class = $student_row['class'];
    }
}

// Fetch timetable for the student's class
$timetable = [];
if ($student_class) {
    $class_escaped = mysqli_real_escape_string($conn, $student_class);
    $timetable_query = mysqli_query($conn, 
        "SELECT * FROM timetable 
         WHERE class_grade = '$class_escaped' 
         ORDER BY FIELD(day_of_week, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'), time_slot ASC"
    );
    while ($row = mysqli_fetch_assoc($timetable_query)) {
        $timetable[] = $row;
    }
}

// Helper function
function h($value) {
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
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
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>My Timetable - School Portal</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
<style>
:root{
  --accent:#ff7200;
  --overlay:rgba(0,0,0,0.55);
  --surface:rgba(255,255,255,0.12);
  --border:rgba(255,255,255,0.18);
  --muted:rgba(255,255,255,0.85);
}

*{margin:0;padding:0;box-sizing:border-box;}

body{
  font-family:'Poppins','Segoe UI',sans-serif;
  color:#fff;
  background:url('https://images.pexels.com/photos/3762806/pexels-photo-3762806.jpeg') no-repeat center center fixed;
  background-size:cover;
  min-height:100vh;
  position:relative;
}

body::before{
  content:"";
  position:fixed;
  inset:0;
  background:var(--overlay);
  z-index:0;
}

.page{position:relative;z-index:1;padding:110px 16px 40px;}

header{
  background:rgba(0,0,0,0.55);
  padding:16px 20px;
  display:flex;
  justify-content:space-between;
  align-items:center;
  border-bottom:1px solid rgba(255,255,255,0.12);
  position:fixed;
  top:0;
  left:0;
  right:0;
  z-index:10;
}

header h2{color:var(--accent);letter-spacing:0.5px;}
nav a{margin:0 12px;text-decoration:none;color:#fff;font-weight:600;transition:color .2s ease;}
nav a:hover{color:var(--accent);}

.container{
  max-width:1200px;
  margin:0 auto;
  background:var(--surface);
  border:1px solid var(--border);
  border-radius:18px;
  padding:28px 32px;
  backdrop-filter:blur(12px);
  box-shadow:0 14px 36px rgba(0,0,0,0.35);
}

h1{
  color:var(--accent);
  margin-bottom:8px;
  font-size:32px;
}

.subtitle{
  color:var(--muted);
  margin-bottom:24px;
  font-size:15px;
}

.timetable-grid{
  display:grid;
  grid-template-columns:repeat(auto-fit, minmax(300px, 1fr));
  gap:20px;
  margin-top:24px;
}

.day-card{
  background:rgba(0,0,0,0.25);
  border:1px solid var(--border);
  border-radius:12px;
  padding:20px;
  backdrop-filter:blur(8px);
}

.day-header{
  color:var(--accent);
  font-size:20px;
  font-weight:700;
  margin-bottom:16px;
  padding-bottom:12px;
  border-bottom:2px solid var(--accent);
}

.class-entry{
  background:rgba(255,114,0,0.15);
  border:1px solid rgba(255,114,0,0.3);
  border-radius:8px;
  padding:12px;
  margin-bottom:12px;
}

.class-entry:last-child{
  margin-bottom:0;
}

.class-time{
  font-weight:600;
  color:var(--accent);
  font-size:13px;
  margin-bottom:6px;
}

.class-subject{
  font-weight:600;
  font-size:15px;
  margin-bottom:4px;
}

.class-teacher{
  font-size:13px;
  color:var(--muted);
  margin-bottom:4px;
}

.class-room{
  font-size:12px;
  color:var(--muted);
  opacity:0.8;
}

.empty-state{
  text-align:center;
  padding:40px 20px;
  color:var(--muted);
}

.empty-state p{
  font-size:16px;
  margin-bottom:8px;
}

.back-btn{
  display:inline-block;
  background:transparent;
  color:var(--accent);
  border:2px solid var(--accent);
  padding:10px 20px;
  border-radius:8px;
  text-decoration:none;
  font-weight:600;
  margin-bottom:20px;
  transition:all .2s ease;
}

.back-btn:hover{
  background:var(--accent);
  color:#000;
}

@media(max-width:768px){
  .timetable-grid{grid-template-columns:1fr;}
  header{flex-direction:column;gap:12px;}
  nav{display:flex;flex-wrap:wrap;justify-content:center;}
}
</style>
</head>
<body>

<header>
  <h2>School Portal</h2>
  <nav>
    <a href="home.php">Home</a>
    <a href="student_timetable.php">📅 Timetable</a>
    <a href="exams_results.php">📊 Results</a>
    <a href="profile.php">Profile</a>
    <a href="logout.php" style="color:var(--accent);">Logout</a>
  </nav>
</header>

<div class="page">
  <div class="container">
    <a href="home.php" class="back-btn">← Back to Home</a>
    
    <h1>📅 My Timetable</h1>
    <p class="subtitle">
      <?php if($student_class): ?>
        Class: <strong><?= h($student_class) ?></strong>
      <?php else: ?>
        No class assigned. Please contact the administrator.
      <?php endif; ?>
    </p>

    <?php if(empty($timetable)): ?>
      <div class="empty-state">
        <p>No timetable available yet.</p>
        <p style="font-size:14px;margin-top:8px;">Your class timetable will appear here once it's been set up by the administrator.</p>
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
                  <div class="class-teacher">👨‍🏫 <?= h($entry['teacher']) ?></div>
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

