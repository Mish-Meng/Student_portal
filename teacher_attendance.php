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

// Helper function
function h($value) {
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

// Create attendance table if it doesn't exist
$create_table = "CREATE TABLE IF NOT EXISTS attendance (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    date DATE NOT NULL,
    status ENUM('Present', 'Absent', 'Late') DEFAULT 'Present',
    teacher_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_attendance (student_id, date),
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE
)";

mysqli_query($conn, $create_table);

// Handle attendance submission
if (isset($_POST['submit_attendance'])) {
    $date = $_POST['date'];
    $students = $_POST['students'] ?? [];
    $attendance_status = $_POST['attendance_status'] ?? [];
    
    if (empty($date)) {
        $message = "❌ Please select a date!";
    } else {
        $success_count = 0;
        $error_count = 0;
        
        foreach ($students as $student_id) {
            $status = $attendance_status[$student_id] ?? 'Present';
            
            // Check if attendance already exists for this date
            $check = mysqli_query($conn, "SELECT id FROM attendance WHERE student_id = $student_id AND date = '$date'");
            
            if (mysqli_num_rows($check) > 0) {
                // Update existing attendance
                $update_sql = "UPDATE attendance SET status = '$status', teacher_id = $teacher_id WHERE student_id = $student_id AND date = '$date'";
                if (mysqli_query($conn, $update_sql)) {
                    $success_count++;
                } else {
                    $error_count++;
                }
            } else {
                // Insert new attendance
                $insert_sql = "INSERT INTO attendance (student_id, date, status, teacher_id) VALUES ($student_id, '$date', '$status', $teacher_id)";
                if (mysqli_query($conn, $insert_sql)) {
                    $success_count++;
                } else {
                    $error_count++;
                }
            }
        }
        
        if ($success_count > 0) {
            $message = "✅ Attendance recorded for $success_count student(s)!";
        }
        if ($error_count > 0) {
            $message .= " ❌ Failed to record $error_count student(s).";
        }
    }
}

// Get selected date (default to today)
$selected_date = $_GET['date'] ?? date('Y-m-d');

// Fetch students
$students_query = mysqli_query($conn, "SELECT id, fullname, adm_no, class FROM students ORDER BY class, fullname ASC");

// Fetch attendance for selected date
$attendance_data = [];
if ($selected_date) {
    $attendance_query = mysqli_query($conn, "SELECT student_id, status FROM attendance WHERE date = '$selected_date'");
    while ($row = mysqli_fetch_assoc($attendance_query)) {
        $attendance_data[$row['student_id']] = $row['status'];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Attendance - Teacher Portal</title>
<link rel="stylesheet" href="teacher_dashboard.css">
<style>
:root{
  --accent:#ff7200;
  --overlay:rgba(0,0,0,0.55);
  --surface:rgba(255,255,255,0.12);
  --border:rgba(255,255,255,0.18);
  --muted:rgba(229,231,235,0.7);
}

.attendance-container {
  background: var(--surface);
  border: 1px solid var(--border);
  border-radius: 14px;
  padding: 28px;
  margin-top: 22px;
  backdrop-filter: blur(8px);
  box-shadow: 0 10px 24px rgba(0,0,0,0.35);
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

.form-group input, .form-group select {
  width: 100%;
  padding: 12px 14px;
  border: 1px solid var(--border);
  border-radius: 10px;
  background: rgba(0,0,0,0.25);
  color: #fff;
  font-size: 15px;
  outline: none;
}

.form-group input:focus, .form-group select:focus {
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
  width: 100%;
  margin-top: 8px;
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

.message {
  padding: 12px 16px;
  border-radius: 10px;
  margin-bottom: 20px;
}

.message.success {
  background: rgba(34,197,94,0.2);
  border: 1px solid rgba(34,197,94,0.5);
  color: #86efac;
}

.message.error {
  background: rgba(239,68,68,0.2);
  border: 1px solid rgba(239,68,68,0.5);
  color: #fca5a5;
}

.students-list {
  margin-top: 24px;
}

.student-item {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 14px;
  margin-bottom: 10px;
  background: rgba(0,0,0,0.25);
  border-radius: 10px;
  border: 1px solid var(--border);
}

.student-info {
  flex: 1;
}

.student-info strong {
  color: #fff;
  display: block;
  margin-bottom: 4px;
}

.student-info small {
  color: var(--muted);
}

.attendance-radio {
  display: flex;
  gap: 16px;
}

.attendance-radio label {
  display: flex;
  align-items: center;
  gap: 6px;
  cursor: pointer;
  color: var(--muted);
}

.attendance-radio input[type="radio"] {
  width: auto;
  margin: 0;
}

.date-filter {
  display: flex;
  gap: 10px;
  margin-bottom: 20px;
}

.date-filter input {
  flex: 1;
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
    <h2>Student Attendance</h2>
    <p>Record student attendance for today</p>
  </div>

  <a href="teacher_dashboard.php" class="btn-secondary">⬅ Back to Dashboard</a>

  <?php if($message): ?>
    <div class="message <?= strpos($message,'❌')!==false?'error':'success' ?>">
      <?= h($message) ?>
    </div>
  <?php endif; ?>

  <div class="attendance-container">
    <form method="GET" class="date-filter">
      <div class="form-group" style="margin: 0; flex: 1;">
        <label>Select Date</label>
        <input type="date" name="date" value="<?= h($selected_date) ?>" required>
      </div>
      <button type="submit" class="btn-primary" style="width: auto; margin-top: 24px; padding: 12px 20px;">View Date</button>
    </form>

    <form method="POST">
      <input type="hidden" name="date" value="<?= h($selected_date) ?>">
      
      <div class="form-group">
        <h3 style="color: #fff; margin-bottom: 16px;">Attendance for <?= date('F d, Y', strtotime($selected_date)) ?></h3>
      </div>

      <div class="students-list">
        <?php if(mysqli_num_rows($students_query) > 0): ?>
          <?php 
          mysqli_data_seek($students_query, 0);
          while($student = mysqli_fetch_assoc($students_query)): 
            $current_status = $attendance_data[$student['id']] ?? 'Present';
          ?>
            <div class="student-item">
              <div class="student-info">
                <strong><?= h($student['fullname']) ?></strong>
                <small>Adm No: <?= h($student['adm_no']) ?> | Class: <?= h($student['class']) ?></small>
              </div>
              <div class="attendance-radio">
                <input type="hidden" name="students[]" value="<?= $student['id'] ?>">
                <label>
                  <input type="radio" name="attendance_status[<?= $student['id'] ?>]" value="Present" <?= $current_status == 'Present' ? 'checked' : '' ?>>
                  Present
                </label>
                <label>
                  <input type="radio" name="attendance_status[<?= $student['id'] ?>]" value="Absent" <?= $current_status == 'Absent' ? 'checked' : '' ?>>
                  Absent
                </label>
                <label>
                  <input type="radio" name="attendance_status[<?= $student['id'] ?>]" value="Late" <?= $current_status == 'Late' ? 'checked' : '' ?>>
                  Late
                </label>
              </div>
            </div>
          <?php endwhile; ?>
        <?php else: ?>
          <p style="color: var(--muted); text-align: center;">No students found.</p>
        <?php endif; ?>
      </div>

      <button type="submit" name="submit_attendance" class="btn-primary">Save Attendance</button>
    </form>
  </div>
</div>

</body>
</html>

