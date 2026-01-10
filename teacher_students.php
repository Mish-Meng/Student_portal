<?php
session_start();

// Protect teacher pages
if (!isset($_SESSION['teacher']) || $_SESSION['role'] != 'teacher') {
    header("Location: admin_login.php");
    exit();
}

include 'connect.php';

// Helper function
function h($value) {
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

// Handle search
$search = trim($_GET['search'] ?? '');
if ($search) {
    $students_query = mysqli_query($conn, "SELECT * FROM students WHERE fullname LIKE '%$search%' OR adm_no LIKE '%$search%' OR class LIKE '%$search%' ORDER BY class, fullname ASC");
} else {
    $students_query = mysqli_query($conn, "SELECT * FROM students ORDER BY class, fullname ASC");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>View Students - Teacher Portal</title>
<link rel="stylesheet" href="teacher_dashboard.css">
<style>
:root{
  --accent:#ff7200;
  --overlay:rgba(0,0,0,0.55);
  --surface:rgba(255,255,255,0.12);
  --border:rgba(255,255,255,0.18);
  --muted:rgba(229,231,235,0.7);
}

.students-container {
  background: var(--surface);
  border: 1px solid var(--border);
  border-radius: 14px;
  padding: 28px;
  margin-top: 22px;
  backdrop-filter: blur(8px);
  box-shadow: 0 10px 24px rgba(0,0,0,0.35);
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

table {
  width: 100%;
  border-collapse: collapse;
  color: #fff;
}

th, td {
  padding: 12px;
  border-bottom: 1px solid rgba(255,255,255,0.15);
  text-align: left;
}

th {
  background: var(--accent);
  color: #000;
  font-weight: 600;
}

tr:nth-child(even) {
  background: rgba(255,255,255,0.05);
}

tr:hover {
  background: rgba(255,255,255,0.12);
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
    <h2>View Students</h2>
    <p>Browse all students (Read-only)</p>
  </div>

  <a href="teacher_dashboard.php" class="btn-secondary">⬅ Back to Dashboard</a>

  <div class="students-container">
    <form method="GET" class="search-bar">
      <input type="text" name="search" placeholder="Search by name, admission number, or class..." value="<?= h($search) ?>">
      <button type="submit">Search</button>
    </form>

    <div style="overflow-x: auto;">
      <table>
        <thead>
          <tr>
            <th>ID</th>
            <th>Full Name</th>
            <th>Admission No</th>
            <th>Class</th>
            <th>Gender</th>
            <th>Age</th>
          </tr>
        </thead>
        <tbody>
          <?php if(mysqli_num_rows($students_query) > 0): ?>
            <?php 
            mysqli_data_seek($students_query, 0);
            while($student = mysqli_fetch_assoc($students_query)): 
            ?>
              <tr>
                <td><?= h($student['id']) ?></td>
                <td><?= h($student['fullname']) ?></td>
                <td><?= h($student['adm_no']) ?></td>
                <td><?= h($student['class']) ?></td>
                <td><?= h($student['gender']) ?></td>
                <td><?= h($student['age']) ?></td>
              </tr>
            <?php endwhile; ?>
          <?php else: ?>
            <tr>
              <td colspan="6" class="empty-state">No students found.</td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

</body>
</html>

