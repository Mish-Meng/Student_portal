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
      <li><a href="admin_timetable.php">📅 Timetables</a></li>
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
  <?php } ?>
</div>

</body>
</html>
