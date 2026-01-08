<?php
session_start();

// 🔒 Protect admin dashboard from direct access - Admin only
if (!isset($_SESSION['admin']) || !isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: admin_login.php");
    exit();
}

// 🧩 Database connection
$conn = new mysqli("localhost", "root", "", "schoolportal");
if ($conn->connect_error) {
    die("Database Connection Failed: " . $conn->connect_error);
}

// 🧮 Safe totals (avoid crashing if a table doesn’t exist)
function getTotal($conn, $table) {
    $result = $conn->query("SHOW TABLES LIKE '$table'");
    if ($result && $result->num_rows > 0) {
        $res = $conn->query("SELECT COUNT(*) as total FROM $table");
        if ($res && $data = $res->fetch_assoc()) {
            return $data['total'];
        }
    }
    return 0;
}

$students_total = getTotal($conn, "students");
$teachers_total = getTotal($conn, "teachers");
$classes_total  = getTotal($conn, "classes");

// Get teachers with their assigned classes
$teachers_with_classes = [];
$teachers_query = $conn->query("SELECT id, fullname, email, subject FROM teachers ORDER BY fullname ASC");
while ($teacher = $teachers_query->fetch_assoc()) {
    $teacher_name = $teacher['fullname'];
    $classes_query = $conn->query("SELECT grade, subjects, time FROM classes WHERE teacher LIKE '%$teacher_name%' ORDER BY grade ASC");
    $assigned_classes = [];
    while ($class = $classes_query->fetch_assoc()) {
        $assigned_classes[] = $class;
    }
    $teachers_with_classes[] = [
        'id' => $teacher['id'],
        'fullname' => $teacher['fullname'],
        'email' => $teacher['email'],
        'subject' => $teacher['subject'],
        'classes' => $assigned_classes,
        'class_count' => count($assigned_classes)
    ];
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
<title>Admin Dashboard - Langata Road Primary & Junior School</title>
<link rel="stylesheet" href="dashboard.css">
</head>
<body>

<!-- Sidebar -->
<div class="sidebar">
  <div>
    <h2>Admin Panel</h2>
    <ul>
      <li><a href="dashboard.php">🏠 Dashboard</a></li>
      <li><a href="teachers.php">👨‍🏫 Teachers</a></li>
      <li><a href="students.php">👩‍🎓 Students</a></li>
      <li><a href="classes.php">🏫 Classes</a></li>
      <li><a href="view_payments.php">💳 Payments</a></li>
      <li><a href="admin_results.php">📊 Results</a></li>
      <li><a href="#">⚙️ Settings</a></li>
    </ul>
  </div>
  <form method="POST">
    <button class="logout-btn" name="logout">Logout</button>
  </form>
</div>

<!-- Main -->
<div class="main">
  <div class="header">
    <h2>Welcome, <?php echo htmlspecialchars($_SESSION['admin']); ?> 👋</h2>
    <p>Langata Road Primary & Junior School Dashboard</p>
  </div>

  <?php
  // Dynamic page inclusion
  if (isset($_GET['page'])) {
      $page = $_GET['page'];
      if (in_array($page, ['students', 'teachers', 'classes'])) {
          include $page . ".php";
      } else {
          echo "<p>⚠️ Page not found!</p>";
      }
  } else {
  ?>
  <div class="cards">
    <div class="card">
      <h3>Students</h3>
      <p>Total Students</p>
      <span><?php echo $students_total; ?></span>
    </div>
    <div class="card">
      <h3>Teachers</h3>
      <p>Total Teachers</p>
      <span><?php echo $teachers_total; ?></span>
    </div>
    <div class="card">
      <h3>Classes</h3>
      <p>Total Classes</p>
      <span><?php echo $classes_total; ?></span>
    </div>
    <div class="card">
      <h3>Payments</h3>
      <p>View student payments</p>
      <a href="view_payments.php" class="view-btn">View Payments</a>
    </div>
    <div class="card">
      <h3>Exam Results</h3>
      <p>Add or update student scores</p>
      <a href="admin_results.php" class="view-btn">Manage Results</a>
    </div>
    <div class="card">
      <h3>Admissions</h3>
      <p>View submitted applications</p>
      <a href="view_report.php" class="view-btn">View Reports</a>
    </div>
    <div class="card">
      <h3>School Events</h3>
      <p>Add or manage school events (Admin Only)</p>
      <a href="admin_events.php" class="view-btn">Manage Events</a>
    </div>
  </div>

  <!-- Teachers and Their Assigned Classes Section -->
  <div style="margin-top: 30px;">
    <h3 style="color: #ff7200; margin-bottom: 20px; font-size: 22px;">👨‍🏫 Teachers and Their Assigned Classes</h3>
    
    <?php if(empty($teachers_with_classes)): ?>
      <p style="color: rgba(255,255,255,0.7); text-align: center; padding: 20px;">No teachers found.</p>
    <?php else: ?>
      <div class="cards">
        <?php foreach($teachers_with_classes as $teacher): ?>
          <div class="card">
            <h3><?= htmlspecialchars($teacher['fullname']) ?></h3>
            <p style="font-size: 12px; margin-bottom: 8px;">📧 <?= htmlspecialchars($teacher['email']) ?></p>
            <p style="font-size: 12px; margin-bottom: 12px;">📚 <?= htmlspecialchars($teacher['subject']) ?></p>
            <span style="font-size: 24px; font-weight: 800; display: block; margin-top: 6px; color: #fff;">
              <?= $teacher['class_count'] ?>
            </span>
            <p style="font-size: 13px; margin-top: 4px;">Class<?= $teacher['class_count'] != 1 ? 'es' : '' ?> Assigned</p>
            <?php if(empty($teacher['classes'])): ?>
              <a href="classes.php" class="view-btn" style="margin-top: 10px; font-size: 12px; padding: 8px 12px;">Assign Classes</a>
            <?php else: ?>
              <div style="margin-top: 12px; padding-top: 12px; border-top: 1px solid rgba(255,255,255,0.2);">
                <?php foreach($teacher['classes'] as $class): ?>
                  <div style="font-size: 11px; color: rgba(255,255,255,0.8); margin-bottom: 6px;">
                    🏫 <?= htmlspecialchars($class['grade']) ?>
                  </div>
                <?php endforeach; ?>
              </div>
            <?php endif; ?>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
  <?php } ?>
</div>

</body>
</html>
